<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Models\Pet;
use App\Models\SystemLog;
use App\Models\Appointment;
use App\Services\ReportService;
use App\Services\QRCodeService;
use App\Services\AdminLogService;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    // =====================================================
    // CONSTANTS
    // =====================================================
    
    private const DEFAULT_CLOSED_DAYS = [0, 6];
    
    // =====================================================
    // DASHBOARD
    // =====================================================

    public function index()
    {
        return view('admin.dashboard', ['stats' => $this->getBaseStats()]);
    }

    public function dashboard()
    {
        $currentAdmin = Admin::find(auth()->id());
        $isSuperAdmin = $currentAdmin?->isSuperAdmin() ?? false;
        
        $stats = $this->getBaseStats();
        
        if ($isSuperAdmin) {
            $stats['total_admins'] = Admin::normalAdmins()->count();
            $stats['activity_summary'] = AdminLogService::getActivitySummary();
        }

        return view('admin.dashboard', compact('stats', 'isSuperAdmin'));
    }

    private function getBaseStats(): array
    {
        return [
            'pending_users' => User::where('Verification_Status_ID', 1)->count(),
            'today_appointments' => Appointment::whereDate('Date', now())->count(),
            'total_pets' => Pet::count(),
        ];
    }

    // =====================================================
    // USER VERIFICATION
    // =====================================================

    public function verifications(Request $request)
    {
        $users = $this->buildUserQuery($request)->latest()->paginate(10);
        return view('admin.verifications', compact('users'));
    }

    public function pendingVerifications(Request $request)
    {
        $query = User::whereNotExists(fn($q) => 
            $q->select(DB::raw(1))->from('admins')->whereColumn('admins.User_ID', 'users.User_ID')
        );

        $users = $this->applyUserFilters($query, $request)->latest()->paginate(10);
        return view('admin.verifications', compact('users'));
    }

    public function showUser($id)
    {
        $user = User::with('ocrData')->findOrFail($id);
        $pets = Pet::where('Owner_ID', $id)->get();
        return view('admin.user_details', compact('user', 'pets'));
    }

    public function approveUser($id)
    {
        return $this->updateUserVerification($id, 2, 'approved', 'successfully verified');
    }

    public function rejectUser($id)
    {
        return $this->updateUserVerification($id, 3, 'rejected', 'rejected');
    }

    private function updateUserVerification($id, $statusId, $action, $message)
    {
        $user = User::findOrFail($id);
        $user->update(['Verification_Status_ID' => $statusId]);
        
        AdminLogService::logUserVerification($id, $action, "{$user->First_Name} {$user->Last_Name}");

        return redirect()->route('admin.verifications')
            ->with('success', "Resident {$user->First_Name} has been {$message}.");
    }

    private function buildUserQuery(Request $request)
    {
        return $this->applyUserFilters(User::query(), $request);
    }

    private function applyUserFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q
                ->where('First_Name', 'like', "%{$search}%")
                ->orWhere('Last_Name', 'like', "%{$search}%")
                ->orWhere('Email', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('Verification_Status_ID', $request->status);
        }

        return $query;
    }

    // =====================================================
    // APPOINTMENTS
    // =====================================================

    public function appointments()
    {
        $appointments = Appointment::with(['user', 'pet', 'service'])
            ->whereIn('Status', ['Pending', 'Approved'])
            ->orderBy('Date')->orderBy('Time')
            ->get()
            ->map(fn($apt) => tap($apt, function ($a) {
                $a->Date = Carbon::parse($a->Date)->format('Y-m-d');
                $a->Time = Carbon::parse($a->Time)->format('H:i');
            }));

        return view('admin.appointment_index', [
            'appointments' => $appointments,
            'schedule' => $this->loadSchedule()
        ]);
    }

    public function approveAppointment($id)
    {
        $appointment = Appointment::with(['pet', 'user', 'service'])->findOrFail($id);

        if ($appointment->Status !== 'Pending') {
            return back()->with('error', 'This appointment cannot be approved.');
        }

        $appointment->update(['Status' => 'Approved']);
        
        $qrPath = QRCodeService::generateForAppointment($appointment);
        \Log::info($qrPath 
            ? "QR Code generated for appointment {$id}: {$qrPath}" 
            : "Failed to generate QR Code for appointment {$id}");

        AdminLogService::logAppointmentAction(
            $id, 'approved', 
            $appointment->pet->Pet_Name,
            "{$appointment->user->First_Name} {$appointment->user->Last_Name}"
        );

        return back()->with('success', 
            "Appointment for {$appointment->pet->Pet_Name} has been approved! QR code generated.");
    }

    public function rejectAppointment($id)
    {
        $appointment = Appointment::with(['pet', 'user', 'service'])->findOrFail($id);

        if ($appointment->Status !== 'Pending') {
            return back()->with('error', 'This appointment cannot be rejected.');
        }

        $this->saveDeclinedNotification($appointment);
        
        AdminLogService::logAppointmentAction(
            $id, 'rejected',
            $appointment->pet->Pet_Name,
            "{$appointment->user->First_Name} {$appointment->user->Last_Name}"
        );

        $date = $appointment->Date->format('M d, Y');
        $time = Carbon::parse($appointment->Time)->format('h:i A');
        $petName = $appointment->pet->Pet_Name;
        
        $appointment->delete();

        return back()->with('success', 
            "Appointment for {$petName} on {$date} at {$time} has been declined.");
    }

    public function toggleDateStatus(Request $request)
    {
        $request->validate(['date' => 'required|date', 'action' => 'required|in:open,close']);

        $schedule = $this->loadSchedule();
        $date = $request->date;
        $isOpening = $request->action === 'open';

        if ($isOpening) {
            $schedule['opened_dates'] = array_unique([...$schedule['opened_dates'], $date]);
            $schedule['closed_dates'] = array_values(array_diff($schedule['closed_dates'], [$date]));
        } else {
            $schedule['closed_dates'] = array_unique([...$schedule['closed_dates'], $date]);
            $schedule['opened_dates'] = array_values(array_diff($schedule['opened_dates'], [$date]));
        }

        $this->saveSchedule($schedule);

        $dayName = Carbon::parse($date)->format('l, M d, Y');
        AdminLogService::logScheduleChange($dayName, strtoupper($request->action));

        $status = $isOpening ? 'OPEN' : 'CLOSED';
        return back()->with('success', "Clinic is now {$status} on {$dayName}");
    }

    public function attendance(Request $request)
    {
        $selectedDate = $request->get('date', now()->format('Y-m-d'));
        $allLogs = QRCodeService::loadAttendanceLogs();

        $filterByDate = fn($logs, $date) => array_filter($logs, fn($log) => ($log['check_in_date'] ?? '') === $date);

        $filteredLogs = $filterByDate($allLogs, $selectedDate);
        usort($filteredLogs, fn($a, $b) => strtotime($b['check_in_time']) - strtotime($a['check_in_time']));

        return view('admin.attendance', [
            'allLogs' => $allLogs,
            'todayLogs' => $filterByDate($allLogs, now()->format('Y-m-d')),
            'filteredLogs' => $filteredLogs,
            'selectedDate' => $selectedDate,
        ]);
    }

    // =====================================================
    // CERTIFICATES
    // =====================================================

    public function certificatesIndex()
    {
        $completedAppointments = Appointment::with(['pet', 'user', 'service'])
            ->where('Status', 'Completed')
            ->orderBy('Date', 'desc')
            ->get();

        return view('admin.certificates.index', [
            'allCertificates' => CertificateService::getAllCertificates(),
            'draftCertificates' => CertificateService::getAllCertificates('draft'),
            'approvedCertificates' => CertificateService::getAllCertificates('approved'),
            'completedAppointments' => $completedAppointments,
        ]);
    }

    public function certificatesCreate($appointmentId)
    {
        $appointment = Appointment::with(['pet', 'user', 'service'])->findOrFail($appointmentId);

        if ($existing = CertificateService::getCertificateByAppointment($appointmentId)) {
            return redirect()->route('admin.certificates.edit', $existing['id'])
                ->with('info', 'A certificate already exists for this appointment.');
        }

        return view('admin.certificates.create', compact('appointment'));
    }

    public function certificatesStore(Request $request)
    {
        $data = $this->validateCertificateRequest($request, true);
        $data['created_by'] = auth()->user()->First_Name ?? 'Admin';

        $certificate = CertificateService::createCertificate($data);
        AdminLogService::logCertificateAction($certificate['id'], 'created', $data['pet_name']);

        if ($request->action === 'approve') {
            return $this->approveCertificateAndRedirect($certificate['id'], $data['pet_name'], 'created and approved');
        }

        return redirect()->route('admin.certificates.index')->with('success', 'Certificate saved as draft.');
    }

    public function certificatesEdit($id)
    {
        $certificate = CertificateService::getCertificate($id);
        
        if (!$certificate) {
            return redirect()->route('admin.certificates.index')->with('error', 'Certificate not found.');
        }

        $appointment = Appointment::with(['pet', 'user', 'service'])->find($certificate['appointment_id']);
        return view('admin.certificates.create', compact('certificate', 'appointment'));
    }

    public function certificatesUpdate(Request $request, $id)
    {
        $data = $this->validateCertificateRequest($request);
        $certificate = CertificateService::updateCertificate($id, $data);

        if (!$certificate) {
            return redirect()->route('admin.certificates.index')->with('error', 'Certificate not found.');
        }

        AdminLogService::logCertificateAction($id, 'updated', $request->pet_name);

        if ($request->action === 'approve') {
            return $this->approveCertificateAndRedirect($id, $request->pet_name, 'updated and approved');
        }

        return redirect()->route('admin.certificates.index')->with('success', 'Certificate updated successfully.');
    }

    public function certificatesApprove($id)
    {
        $certificate = CertificateService::approveCertificate($id, auth()->user()->First_Name ?? 'Admin');

        if (!$certificate) {
            return redirect()->route('admin.certificates.index')->with('error', 'Certificate not found.');
        }

        AdminLogService::logCertificateAction($id, 'approved', $certificate['pet_name'] ?? 'Unknown');
        return redirect()->route('admin.certificates.index')->with('success', 'Certificate approved!');
    }

    public function certificatesView($id)
    {
        return $this->serveCertificatePdf($id);
    }

    public function certificatesDelete($id)
    {
        $certificate = CertificateService::getCertificate($id);
        
        if ($certificate) {
            AdminLogService::logCertificateAction($id, 'deleted', $certificate['pet_name'] ?? 'Unknown');
        }

        if (!CertificateService::deleteCertificate($id)) {
            return redirect()->route('admin.certificates.index')->with('error', 'Certificate not found.');
        }

        return redirect()->route('admin.certificates.index')->with('success', 'Certificate deleted.');
    }

    private function validateCertificateRequest(Request $request, bool $includeAppointmentId = false): array
    {
        $rules = [
            'pet_name' => 'required|string|max:255',
            'animal_type' => 'required|string|max:100',
            'pet_gender' => 'required|string|max:50',
            'pet_age' => 'required|string|max:100',
            'pet_breed' => 'required|string|max:255',
            'pet_color' => 'required|string|max:100',
            'owner_name' => 'required|string|max:255',
            'owner_address' => 'required|string',
            'owner_phone' => 'required|string|max:50',
            'civil_status' => 'required|string|max:50',
            'years_of_residency' => 'required|string|max:100',
            'service_type' => 'required|string|max:255',
            'vaccination_date' => 'required|date',
            'vaccine_used' => 'required|string|max:255',
            'lot_number' => 'required|string|max:100',
            'veterinarian_name' => 'required|string|max:255',
            'license_number' => 'required|string|max:100',
            'ptr_number' => 'required|string|max:100',
        ];

        if ($includeAppointmentId) {
            $rules['appointment_id'] = 'required';
        }

        return $request->validate($rules);
    }

    private function approveCertificateAndRedirect($id, $petName, $action): \Illuminate\Http\RedirectResponse
    {
        CertificateService::approveCertificate($id, auth()->user()->First_Name ?? 'Admin');
        AdminLogService::logCertificateAction($id, 'approved', $petName);
        return redirect()->route('admin.certificates.index')->with('success', "Certificate {$action} successfully!");
    }

    private function serveCertificatePdf($id)
    {
        $certificate = CertificateService::getCertificate($id);
        abort_if(!$certificate, 404, 'Certificate not found.');

        $pdfPath = $this->ensurePdfExists($certificate, fn($c) => CertificateService::generatePdf($c));
        return response()->file($pdfPath, ['Content-Type' => 'text/html']);
    }

    // =====================================================
    // REPORTS
    // =====================================================

    public function reports()
    {
        return view('admin.reports.index', ['reports' => ReportService::getAllReports()]);
    }

    public function generateReport(Request $request)
    {
        [$startDate, $endDate, $weekNumber, $year] = $this->getReportDateRange($request);

        $antiRabiesData = ReportService::getAntiRabiesData($startDate, $endDate);
        $routineServicesData = ReportService::getRoutineServicesData($startDate, $endDate);

        $report = ReportService::createReport([
            'type' => 'WEEKLY',
            'week_number' => $weekNumber,
            'year' => $year,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'generated_by' => auth()->user()->First_Name ?? 'Admin',
            'summary' => ReportService::getWeeklySummary($startDate, $endDate),
            'anti_rabies_count' => count($antiRabiesData),
            'routine_services_count' => count($routineServicesData),
        ]);

        $report['anti_rabies_data'] = $antiRabiesData;
        $report['routine_services_data'] = $routineServicesData;

        ReportService::updateReport($report['id'], [
            'anti_rabies_pdf' => ReportService::generateAntiRabiesPdf($report),
            'routine_services_pdf' => ReportService::generateRoutineServicesPdf($report),
        ]);

        AdminLogService::logReportGeneration('WEEKLY', $weekNumber, $year);

        return redirect()->route('admin.reports')
            ->with('success', "Weekly report for Week {$weekNumber}, {$year} generated successfully!");
    }

    public function viewAntiRabiesReport($id)
    {
        return $this->serveReportPdf($id, 'anti_rabies_pdf', 'getAntiRabiesData', 'generateAntiRabiesPdf');
    }

    public function viewRoutineServicesReport($id)
    {
        return $this->serveReportPdf($id, 'routine_services_pdf', 'getRoutineServicesData', 'generateRoutineServicesPdf');
    }

    public function deleteReport($id)
    {
        $report = ReportService::getReport($id);
        
        if ($report) {
            AdminLogService::log('REPORT_DELETED', "Report for Week {$report['week_number']}, {$report['year']} deleted");
        }

        if (!ReportService::deleteReport($id)) {
            return redirect()->route('admin.reports')->with('error', 'Report not found.');
        }

        return redirect()->route('admin.reports')->with('success', 'Report deleted.');
    }

    private function getReportDateRange(Request $request): array
    {
        if ($request->filled('custom_start') && $request->filled('custom_end')) {
            $start = Carbon::parse($request->custom_start)->startOfDay();
            $end = Carbon::parse($request->custom_end)->endOfDay();
            return [$start, $end, $start->weekOfYear, $start->year];
        }

        $range = ReportService::getWeeklyDateRange((int) $request->input('week_offset', 0));
        return [$range['start'], $range['end'], $range['week_number'], $range['year']];
    }

    private function serveReportPdf($id, $pdfField, $dataMethod, $generateMethod)
    {
        $report = ReportService::getReport($id);
        abort_if(!$report, 404, 'Report not found.');

        if (empty($report[$pdfField])) {
            $startDate = Carbon::parse($report['start_date']);
            $endDate = Carbon::parse($report['end_date']);
            
            $report[str_replace('_pdf', '_data', $pdfField)] = ReportService::$dataMethod($startDate, $endDate);
            $report[$pdfField] = ReportService::$generateMethod($report);
            ReportService::updateReport($id, [$pdfField => $report[$pdfField]]);
        }

        $pdfPath = storage_path('app/public/' . $report[$pdfField]);

        if (!file_exists($pdfPath)) {
            $startDate = Carbon::parse($report['start_date']);
            $endDate = Carbon::parse($report['end_date']);
            $report[str_replace('_pdf', '_data', $pdfField)] = ReportService::$dataMethod($startDate, $endDate);
            $report[$pdfField] = ReportService::$generateMethod($report);
            $pdfPath = storage_path('app/public/' . $report[$pdfField]);
        }

        return response()->file($pdfPath, ['Content-Type' => 'text/html']);
    }

    // =====================================================
    // ADMIN MANAGEMENT (Super Admin)
    // =====================================================

    public function adminsIndex()
    {
        $admins = Admin::with('user', 'creator')
            ->orderBy('is_super_admin', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.admins.index', compact('admins'));
    }

    public function adminsCreate()
    {
        $eligibleUsers = User::where('Verification_Status_ID', 2)
            ->whereNotExists(fn($q) => 
                $q->select(DB::raw(1))->from('admins')->whereColumn('admins.User_ID', 'users.User_ID'))
            ->orderBy('First_Name')
            ->get();

        return view('admin.admins.create', compact('eligibleUsers'));
    }

    public function adminsStore(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,User_ID',
            'admin_role' => 'required|in:staff,admin',
        ]);

        if (Admin::find($request->user_id)) {
            return back()->with('error', 'This user is already an admin.');
        }

        $user = User::findOrFail($request->user_id);

        Admin::create([
            'User_ID' => $request->user_id,
            'is_super_admin' => false,
            'admin_role' => $request->admin_role,
            'created_by' => auth()->id(),
        ]);

        AdminLogService::logAdminManagement($request->user_id, 'created', "{$user->First_Name} {$user->Last_Name}");

        return redirect()->route('admin.admins.index')
            ->with('success', "{$user->First_Name} {$user->Last_Name} added as " . ucfirst($request->admin_role) . ".");
    }

    public function adminsUpdate(Request $request, $id)
    {
        $request->validate(['admin_role' => 'required|in:staff,admin']);

        $admin = Admin::with('user')->findOrFail($id);

        if ($admin->isSuperAdmin()) {
            return back()->with('error', 'Cannot modify super admin accounts.');
        }

        $oldRole = $admin->admin_role;
        $admin->update(['admin_role' => $request->admin_role]);

        AdminLogService::logAdminManagement($id, "role_changed_from_{$oldRole}_to_{$request->admin_role}", 
            "{$admin->user->First_Name} {$admin->user->Last_Name}");

        return redirect()->route('admin.admins.index')->with('success', 'Admin role updated.');
    }

    public function adminsDestroy($id)
    {
        $admin = Admin::with('user')->findOrFail($id);

        if ($admin->isSuperAdmin()) {
            return back()->with('error', 'Cannot remove super admin accounts.');
        }

        if ($admin->User_ID == auth()->id()) {
            return back()->with('error', 'You cannot remove your own admin privileges.');
        }

        $userName = "{$admin->user->First_Name} {$admin->user->Last_Name}";
        AdminLogService::logAdminManagement($id, 'removed', $userName);
        $admin->delete();

        return redirect()->route('admin.admins.index')->with('success', "{$userName} removed from admin role.");
    }

    // =====================================================
    // ACTIVITY LOGS (Super Admin)
    // =====================================================

    public function activityLogs(Request $request)
    {
        $normalAdminIds = Admin::normalAdmins()->pluck('User_ID');

        $query = SystemLog::with('user')->whereIn('User_ID', $normalAdminIds);

        if ($request->filled('admin_id')) {
            $query->where('User_ID', $request->admin_id);
        }
        if ($request->filled('action')) {
            $query->where('Action', 'like', "%{$request->action}%");
        }
        if ($request->filled('date_from')) {
            $query->whereDate('Timestamp', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('Timestamp', '<=', $request->date_to);
        }

        return view('admin.logs.index', [
            'logs' => $query->orderBy('Timestamp', 'desc')->paginate(25),
            'admins' => Admin::normalAdmins()->with('user')->get(),
            'actionTypes' => SystemLog::whereIn('User_ID', $normalAdminIds)->distinct()->pluck('Action'),
        ]);
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    private function loadSchedule(): array
    {
        $path = storage_path('app/clinic_schedule.json');

        if (!file_exists($path)) {
            $default = ['default_closed_days' => self::DEFAULT_CLOSED_DAYS, 'opened_dates' => [], 'closed_dates' => []];
            $this->saveSchedule($default);
            return $default;
        }

        return json_decode(file_get_contents($path), true);
    }

    private function saveSchedule(array $schedule): void
    {
        $path = storage_path('app/clinic_schedule.json');
        $dir = dirname($path);
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, json_encode($schedule, JSON_PRETTY_PRINT));
    }

    private function saveDeclinedNotification(Appointment $appointment): void
    {
        $path = storage_path('app/declined_notifications.json');
        
        $notifications = file_exists($path) 
            ? json_decode(file_get_contents($path), true) ?? []
            : [];

        $notifications[] = [
            'user_id' => $appointment->User_ID,
            'pet_name' => $appointment->pet->Pet_Name,
            'date' => $appointment->Date->format('M d, Y'),
            'time' => Carbon::parse($appointment->Time)->format('h:i A'),
            'service' => $appointment->service->Service_Name ?? 'Appointment',
            'declined_at' => now()->toDateTimeString(),
        ];

        file_put_contents($path, json_encode($notifications, JSON_PRETTY_PRINT));
    }

    private function ensurePdfExists(array $item, callable $generator): string
    {
        if (empty($item['pdf_path'])) {
            $item['pdf_path'] = $generator($item);
        }

        $path = storage_path('app/public/' . $item['pdf_path']);

        if (!file_exists($path)) {
            $item['pdf_path'] = $generator($item);
            $path = storage_path('app/public/' . $item['pdf_path']);
        }

        return $path;
    }
}