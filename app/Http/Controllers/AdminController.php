<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Models\SystemLog;
use App\Models\Appointment;
use App\Services\ReportService;
use App\Services\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'pending_users' => User::where('Verification_Status_ID', 1)->count(),
            'today_appointments' => Appointment::whereDate('Date', now())->count(),
            'total_pets' => \App\Models\Pet::count(), 
        ];
        
        return view('admin.dashboard', compact('stats'));
    }

    public function dashboard()
    {
        $currentAdmin = Admin::find(auth()->id());
        $isSuperAdmin = $currentAdmin ? $currentAdmin->isSuperAdmin() : false;
        
        $stats = [
            'pending_users' => User::where('Verification_Status_ID', 1)->count(),
            'today_appointments' => Appointment::whereDate('Date', now())->count(),
            'total_pets' => \App\Models\Pet::count(),
        ];

        // Super admin gets additional stats
        if ($isSuperAdmin) {
            $stats['total_admins'] = Admin::normalAdmins()->count();
            $stats['activity_summary'] = AdminLogService::getActivitySummary();
        }

        return view('admin.dashboard', compact('stats', 'isSuperAdmin'));
    }

    public function pendingVerifications(Request $request)
    {
        $query = User::whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('admins') 
                ->whereColumn('admins.User_ID', 'users.User_ID');
        });

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('First_Name', 'like', '%' . $request->search . '%')
                ->orWhere('Last_Name', 'like', '%' . $request->search . '%')
                ->orWhere('Email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('Verification_Status_ID', $request->status);
        }

        $users = $query->latest()->paginate(10);

        return view('admin.verifications', compact('users'));
    }

    public function approveUser($id)
    {
        $user = User::findOrFail($id);
        
        $user->update([
            'Verification_Status_ID' => 2 
        ]);

        // Log this action (only for normal admins)
        AdminLogService::logUserVerification($id, 'approved', $user->First_Name . ' ' . $user->Last_Name);

        return redirect()->route('admin.verifications')->with('success', 'Resident ' . $user->First_Name . ' has been successfully verified.');
    }

    public function rejectUser($id)
    {
        $user = User::findOrFail($id);
        
        $user->update([
            'Verification_Status_ID' => 3 
        ]);

        // Log this action (only for normal admins)
        AdminLogService::logUserVerification($id, 'rejected', $user->First_Name . ' ' . $user->Last_Name);

        return redirect()->route('admin.verifications')->with('success', 'Resident ' . $user->First_Name . ' has been rejected.');
    }

    public function verifications(Request $request)
    {
        $query = User::query();

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('First_Name', 'like', '%' . $request->search . '%')
                ->orWhere('Last_Name', 'like', '%' . $request->search . '%')
                ->orWhere('Email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('Verification_Status_ID', $request->status);
        }

        $users = $query->latest()->paginate(10);
        return view('admin.verifications', compact('users'));
    }
    
    public function showUser($id)
    {
        $user = User::with(['ocrData'])->findOrFail($id);
        $pets = \App\Models\Pet::where('Owner_ID', $id)->get(); 
        
        return view('admin.user_details', compact('user', 'pets'));
    }

    public function appointments()
    {
        $appointments = Appointment::with(['user', 'pet', 'service'])
            ->whereIn('Status', ['Pending', 'Approved'])
            ->orderBy('Date', 'asc')
            ->orderBy('Time', 'asc')
            ->get()
            ->map(function ($appointment) {
                $appointment->Date = \Carbon\Carbon::parse($appointment->Date)->format('Y-m-d');
                $appointment->Time = \Carbon\Carbon::parse($appointment->Time)->format('H:i');
                return $appointment;
            });

        $schedule = $this->getClinicSchedule();

        return view('admin.appointment_index', compact('appointments', 'schedule'));
    }

    private function getClinicSchedule()
    {
        $schedulePath = storage_path('app/clinic_schedule.json');
        
        if (!file_exists($schedulePath)) {
            $defaultSchedule = [
                'default_closed_days' => [0, 6],
                'opened_dates' => [],
                'closed_dates' => [],
            ];
            
            $directory = dirname($schedulePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            
            file_put_contents($schedulePath, json_encode($defaultSchedule, JSON_PRETTY_PRINT));
            return $defaultSchedule;
        }
        
        return json_decode(file_get_contents($schedulePath), true);
    }

    public function toggleDateStatus(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'action' => 'required|in:open,close'
        ]);

        $date = $request->date;
        $action = $request->action;
        $schedulePath = storage_path('app/clinic_schedule.json');
        $schedule = $this->getClinicSchedule();

        if ($action === 'open') {
            if (!in_array($date, $schedule['opened_dates'])) {
                $schedule['opened_dates'][] = $date;
            }
            $schedule['closed_dates'] = array_values(array_diff($schedule['closed_dates'], [$date]));
        } else {
            if (!in_array($date, $schedule['closed_dates'])) {
                $schedule['closed_dates'][] = $date;
            }
            $schedule['opened_dates'] = array_values(array_diff($schedule['opened_dates'], [$date]));
        }

        file_put_contents($schedulePath, json_encode($schedule, JSON_PRETTY_PRINT));

        // Log schedule change
        $dayName = \Carbon\Carbon::parse($date)->format('l, M d, Y');
        AdminLogService::logScheduleChange($dayName, strtoupper($action));

        $message = $action === 'open' 
            ? "Clinic is now OPEN on {$dayName}" 
            : "Clinic is now CLOSED on {$dayName}";

        return redirect()->back()->with('success', $message);
    }

    public function approveAppointment($id)
    {
        $appointment = Appointment::with(['pet', 'user', 'service'])->findOrFail($id);
        
        if ($appointment->Status !== 'Pending') {
            return redirect()->back()->with('error', 'This appointment cannot be approved.');
        }
        
        $appointment->Status = 'Approved';
        $appointment->save();

        $qrCodePath = \App\Services\QRCodeService::generateForAppointment($appointment);
        
        if ($qrCodePath) {
            \Log::info("QR Code generated for appointment {$id}: {$qrCodePath}");
        } else {
            \Log::warning("Failed to generate QR Code for appointment {$id}");
        }

        // Log appointment approval
        AdminLogService::logAppointmentAction(
            $id, 
            'approved', 
            $appointment->pet->Pet_Name,
            $appointment->user->First_Name . ' ' . $appointment->user->Last_Name
        );

        return redirect()->back()->with('success', 
            'Appointment for ' . $appointment->pet->Pet_Name . ' has been approved! QR code generated and the owner will be notified.');
    }

    public function rejectAppointment($id)
    {
        $appointment = Appointment::with(['pet', 'user'])->findOrFail($id);
        
        if ($appointment->Status !== 'Pending') {
            return redirect()->back()->with('error', 'This appointment cannot be rejected.');
        }
        
        $petName = $appointment->pet->Pet_Name;
        $ownerName = $appointment->user->First_Name . ' ' . $appointment->user->Last_Name;
        $ownerUserId = $appointment->User_ID;
        $date = $appointment->Date->format('M d, Y');
        $time = \Carbon\Carbon::parse($appointment->Time)->format('h:i A');
        $serviceName = $appointment->service->Service_Name ?? 'Appointment';
        
        $notificationPath = storage_path('app/declined_notifications.json');
        
        if (!file_exists($notificationPath)) {
            $directory = dirname($notificationPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            file_put_contents($notificationPath, json_encode([]));
        }
        
        $declinedNotifications = json_decode(file_get_contents($notificationPath), true) ?? [];
        $declinedNotifications[] = [
            'user_id' => $ownerUserId,
            'pet_name' => $petName,
            'date' => $date,
            'time' => $time,
            'service' => $serviceName,
            'declined_at' => now()->toDateTimeString(),
        ];
        file_put_contents($notificationPath, json_encode($declinedNotifications, JSON_PRETTY_PRINT));
        
        // Log before deleting
        AdminLogService::logAppointmentAction($id, 'rejected', $petName, $ownerName);
        
        $appointment->delete();

        return redirect()->back()->with('success', 
            "Appointment for {$petName} on {$date} at {$time} has been declined and removed. The time slot is now available.");
    }

    public function attendance(Request $request)
    {
        $selectedDate = $request->get('date', now()->format('Y-m-d'));
        
        $allLogs = \App\Services\QRCodeService::loadAttendanceLogs();
        
        $todayLogs = array_filter($allLogs, function($log) {
            return ($log['check_in_date'] ?? '') === now()->format('Y-m-d');
        });
        
        $filteredLogs = array_filter($allLogs, function($log) use ($selectedDate) {
            return ($log['check_in_date'] ?? '') === $selectedDate;
        });
        
        usort($filteredLogs, function($a, $b) {
            return strtotime($b['check_in_time']) - strtotime($a['check_in_time']);
        });
        
        return view('admin.attendance', compact('allLogs', 'todayLogs', 'filteredLogs', 'selectedDate'));
    }

    // =====================================================
    // CERTIFICATE GENERATION METHODS
    // =====================================================

    public function certificatesIndex()
    {
        $allCertificates = \App\Services\CertificateService::getAllCertificates();
        $draftCertificates = \App\Services\CertificateService::getAllCertificates('draft');
        $approvedCertificates = \App\Services\CertificateService::getAllCertificates('approved');
        
        $completedAppointments = Appointment::with(['pet', 'user', 'service'])
            ->where('Status', 'Completed')
            ->orderBy('Date', 'desc')
            ->get();
        
        return view('admin.certificates.index', compact(
            'allCertificates', 
            'draftCertificates', 
            'approvedCertificates', 
            'completedAppointments'
        ));
    }

    public function certificatesCreate($appointmentId)
    {
        $appointment = Appointment::with(['pet', 'user', 'service'])
            ->findOrFail($appointmentId);
        
        $existingCertificate = \App\Services\CertificateService::getCertificateByAppointment($appointmentId);
        if ($existingCertificate) {
            return redirect()->route('admin.certificates.edit', $existingCertificate['id'])
                ->with('info', 'A certificate already exists for this appointment.');
        }
        
        return view('admin.certificates.create', compact('appointment'));
    }

    public function certificatesStore(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required',
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
        ]);

        $data = $request->all();
        $data['created_by'] = auth()->user()->First_Name ?? 'Admin';
        
        $certificate = \App\Services\CertificateService::createCertificate($data);

        // Log certificate creation
        AdminLogService::logCertificateAction($certificate['id'], 'created', $data['pet_name']);
        
        if ($request->action === 'approve') {
            \App\Services\CertificateService::approveCertificate(
                $certificate['id'], 
                auth()->user()->First_Name ?? 'Admin'
            );
            AdminLogService::logCertificateAction($certificate['id'], 'approved', $data['pet_name']);
            
            return redirect()->route('admin.certificates.index')
                ->with('success', 'Certificate created and approved successfully!');
        }
        
        return redirect()->route('admin.certificates.index')
            ->with('success', 'Certificate saved as draft.');
    }

    public function certificatesEdit($id)
    {
        $certificate = \App\Services\CertificateService::getCertificate($id);
        
        if (!$certificate) {
            return redirect()->route('admin.certificates.index')
                ->with('error', 'Certificate not found.');
        }
        
        $appointment = Appointment::with(['pet', 'user', 'service'])
            ->find($certificate['appointment_id']);
        
        return view('admin.certificates.create', compact('certificate', 'appointment'));
    }

    public function certificatesUpdate(Request $request, $id)
    {
        $request->validate([
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
        ]);

        $certificate = \App\Services\CertificateService::updateCertificate($id, $request->all());
        
        if (!$certificate) {
            return redirect()->route('admin.certificates.index')
                ->with('error', 'Certificate not found.');
        }

        // Log certificate update
        AdminLogService::logCertificateAction($id, 'updated', $request->pet_name);
        
        if ($request->action === 'approve') {
            \App\Services\CertificateService::approveCertificate(
                $id, 
                auth()->user()->First_Name ?? 'Admin'
            );
            AdminLogService::logCertificateAction($id, 'approved', $request->pet_name);
            
            return redirect()->route('admin.certificates.index')
                ->with('success', 'Certificate updated and approved!');
        }
        
        return redirect()->route('admin.certificates.index')
            ->with('success', 'Certificate updated successfully.');
    }

    public function certificatesApprove($id)
    {
        $certificate = \App\Services\CertificateService::approveCertificate(
            $id, 
            auth()->user()->First_Name ?? 'Admin'
        );
        
        if (!$certificate) {
            return redirect()->route('admin.certificates.index')
                ->with('error', 'Certificate not found.');
        }

        // Log certificate approval
        AdminLogService::logCertificateAction($id, 'approved', $certificate['pet_name'] ?? 'Unknown');
        
        return redirect()->route('admin.certificates.index')
            ->with('success', 'Certificate approved and ready for download!');
    }

    public function certificatesView($id)
    {
        $certificate = \App\Services\CertificateService::getCertificate($id);
        
        if (!$certificate) {
            abort(404, 'Certificate not found.');
        }
        
        if (empty($certificate['pdf_path'])) {
            $certificate['pdf_path'] = \App\Services\CertificateService::generatePdf($certificate);
        }
        
        $pdfPath = storage_path('app/public/' . $certificate['pdf_path']);
        
        if (!file_exists($pdfPath)) {
            $certificate['pdf_path'] = \App\Services\CertificateService::generatePdf($certificate);
            $pdfPath = storage_path('app/public/' . $certificate['pdf_path']);
        }
        
        return response()->file($pdfPath, [
            'Content-Type' => 'text/html',
        ]);
    }

    public function certificatesDelete($id)
    {
        $certificate = \App\Services\CertificateService::getCertificate($id);
        
        if ($certificate) {
            AdminLogService::logCertificateAction($id, 'deleted', $certificate['pet_name'] ?? 'Unknown');
        }
        
        $deleted = \App\Services\CertificateService::deleteCertificate($id);
        
        if (!$deleted) {
            return redirect()->route('admin.certificates.index')
                ->with('error', 'Certificate not found.');
        }
        
        return redirect()->route('admin.certificates.index')
            ->with('success', 'Certificate deleted successfully.');
    }

    // =====================================================
    // REPORTS METHODS
    // =====================================================

    public function reports()
    {
        $reports = ReportService::getAllReports();
        
        return view('admin.reports.index', compact('reports'));
    }

    public function generateReport(Request $request)
    {
        if ($request->filled('custom_start') && $request->filled('custom_end')) {
            $startDate = Carbon::parse($request->custom_start)->startOfDay();
            $endDate = Carbon::parse($request->custom_end)->endOfDay();
            $weekNumber = $startDate->weekOfYear;
            $year = $startDate->year;
        } else {
            $weekOffset = (int) $request->input('week_offset', 0);
            $dateRange = ReportService::getWeeklyDateRange($weekOffset);
            $startDate = $dateRange['start'];
            $endDate = $dateRange['end'];
            $weekNumber = $dateRange['week_number'];
            $year = $dateRange['year'];
        }

        $antiRabiesData = ReportService::getAntiRabiesData($startDate, $endDate);
        $routineServicesData = ReportService::getRoutineServicesData($startDate, $endDate);
        $summary = ReportService::getWeeklySummary($startDate, $endDate);

        $reportData = [
            'type' => 'WEEKLY',
            'week_number' => $weekNumber,
            'year' => $year,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'generated_by' => auth()->user()->First_Name ?? 'Admin',
            'summary' => $summary,
            'anti_rabies_count' => count($antiRabiesData),
            'routine_services_count' => count($routineServicesData),
        ];

        $report = ReportService::createReport($reportData);

        $report['anti_rabies_data'] = $antiRabiesData;
        $report['routine_services_data'] = $routineServicesData;

        $antiRabiesPdf = ReportService::generateAntiRabiesPdf($report);
        $routineServicesPdf = ReportService::generateRoutineServicesPdf($report);

        ReportService::updateReport($report['id'], [
            'anti_rabies_pdf' => $antiRabiesPdf,
            'routine_services_pdf' => $routineServicesPdf,
        ]);

        // Log report generation
        AdminLogService::logReportGeneration('WEEKLY', $weekNumber, $year);

        return redirect()->route('admin.reports')
            ->with('success', "Weekly report for Week {$weekNumber}, {$year} has been generated successfully!");
    }

    public function viewAntiRabiesReport($id)
    {
        $report = ReportService::getReport($id);
        
        if (!$report) {
            abort(404, 'Report not found.');
        }
        
        if (empty($report['anti_rabies_pdf'])) {
            $startDate = Carbon::parse($report['start_date']);
            $endDate = Carbon::parse($report['end_date']);
            
            $report['anti_rabies_data'] = ReportService::getAntiRabiesData($startDate, $endDate);
            $report['anti_rabies_pdf'] = ReportService::generateAntiRabiesPdf($report);
            
            ReportService::updateReport($id, ['anti_rabies_pdf' => $report['anti_rabies_pdf']]);
        }
        
        $pdfPath = storage_path('app/public/' . $report['anti_rabies_pdf']);
        
        if (!file_exists($pdfPath)) {
            $startDate = Carbon::parse($report['start_date']);
            $endDate = Carbon::parse($report['end_date']);
            
            $report['anti_rabies_data'] = ReportService::getAntiRabiesData($startDate, $endDate);
            $report['anti_rabies_pdf'] = ReportService::generateAntiRabiesPdf($report);
            $pdfPath = storage_path('app/public/' . $report['anti_rabies_pdf']);
        }
        
        return response()->file($pdfPath, [
            'Content-Type' => 'text/html',
        ]);
    }

    public function viewRoutineServicesReport($id)
    {
        $report = ReportService::getReport($id);
        
        if (!$report) {
            abort(404, 'Report not found.');
        }
        
        if (empty($report['routine_services_pdf'])) {
            $startDate = Carbon::parse($report['start_date']);
            $endDate = Carbon::parse($report['end_date']);
            
            $report['routine_services_data'] = ReportService::getRoutineServicesData($startDate, $endDate);
            $report['routine_services_pdf'] = ReportService::generateRoutineServicesPdf($report);
            
            ReportService::updateReport($id, ['routine_services_pdf' => $report['routine_services_pdf']]);
        }
        
        $pdfPath = storage_path('app/public/' . $report['routine_services_pdf']);
        
        if (!file_exists($pdfPath)) {
            $startDate = Carbon::parse($report['start_date']);
            $endDate = Carbon::parse($report['end_date']);
            
            $report['routine_services_data'] = ReportService::getRoutineServicesData($startDate, $endDate);
            $report['routine_services_pdf'] = ReportService::generateRoutineServicesPdf($report);
            $pdfPath = storage_path('app/public/' . $report['routine_services_pdf']);
        }
        
        return response()->file($pdfPath, [
            'Content-Type' => 'text/html',
        ]);
    }

    public function deleteReport($id)
    {
        $report = ReportService::getReport($id);
        
        if ($report) {
            AdminLogService::log('REPORT_DELETED', "Report for Week {$report['week_number']}, {$report['year']} was deleted");
        }
        
        $deleted = ReportService::deleteReport($id);
        
        if (!$deleted) {
            return redirect()->route('admin.reports')
                ->with('error', 'Report not found.');
        }
        
        return redirect()->route('admin.reports')
            ->with('success', 'Report deleted successfully.');
    }

    // =====================================================
    // SUPER ADMIN: ADMIN MANAGEMENT METHODS
    // =====================================================

    /**
     * Display list of all admins (Super Admin only)
     */
    public function adminsIndex()
    {
        $admins = Admin::with('user', 'creator')
            ->orderBy('is_super_admin', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.admins.index', compact('admins'));
    }

    /**
     * Show form to create new admin (Super Admin only)
     */
    public function adminsCreate()
    {
        // Get verified users who are not already admins
        $eligibleUsers = User::where('Verification_Status_ID', 2)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('admins')
                    ->whereColumn('admins.User_ID', 'users.User_ID');
            })
            ->orderBy('First_Name')
            ->get();
        
        return view('admin.admins.create', compact('eligibleUsers'));
    }

    /**
     * Store new admin (Super Admin only)
     */
    public function adminsStore(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,User_ID',
            'admin_role' => 'required|in:staff,admin',
        ]);

        // Check if user is already an admin
        if (Admin::find($request->user_id)) {
            return redirect()->back()->with('error', 'This user is already an admin.');
        }

        $user = User::findOrFail($request->user_id);

        Admin::create([
            'User_ID' => $request->user_id,
            'is_super_admin' => false,
            'admin_role' => $request->admin_role,
            'created_by' => auth()->id(),
        ]);

        // Log admin creation
        AdminLogService::logAdminManagement(
            $request->user_id, 
            'created', 
            $user->First_Name . ' ' . $user->Last_Name
        );

        return redirect()->route('admin.admins.index')
            ->with('success', $user->First_Name . ' ' . $user->Last_Name . ' has been added as ' . ucfirst($request->admin_role) . '.');
    }

    /**
     * Update admin role (Super Admin only)
     */
    public function adminsUpdate(Request $request, $id)
    {
        $request->validate([
            'admin_role' => 'required|in:staff,admin',
        ]);

        $admin = Admin::with('user')->findOrFail($id);
        
        // Prevent modifying super admins
        if ($admin->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Cannot modify super admin accounts.');
        }

        $oldRole = $admin->admin_role;
        $admin->update(['admin_role' => $request->admin_role]);

        // Log role change
        AdminLogService::logAdminManagement(
            $id, 
            "role_changed_from_{$oldRole}_to_{$request->admin_role}", 
            $admin->user->First_Name . ' ' . $admin->user->Last_Name
        );

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin role updated successfully.');
    }

    /**
     * Remove admin privileges (Super Admin only)
     */
    public function adminsDestroy($id)
    {
        $admin = Admin::with('user')->findOrFail($id);
        
        // Prevent deleting super admins
        if ($admin->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Cannot remove super admin accounts.');
        }

        // Prevent deleting yourself
        if ($admin->User_ID == auth()->id()) {
            return redirect()->back()->with('error', 'You cannot remove your own admin privileges.');
        }

        $userName = $admin->user->First_Name . ' ' . $admin->user->Last_Name;
        
        // Log before deleting
        AdminLogService::logAdminManagement($id, 'removed', $userName);
        
        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', $userName . ' has been removed from admin role.');
    }

    // =====================================================
    // SUPER ADMIN: ACTIVITY LOGS METHODS
    // =====================================================

    /**
     * Display activity logs (Super Admin only)
     */
    public function activityLogs(Request $request)
    {
        $query = SystemLog::with('user');

        // Filter by normal admins only
        $normalAdminIds = Admin::normalAdmins()->pluck('User_ID');
        $query->whereIn('User_ID', $normalAdminIds);

        // Filter by admin
        if ($request->filled('admin_id')) {
            $query->where('User_ID', $request->admin_id);
        }

        // Filter by action type
        if ($request->filled('action')) {
            $query->where('Action', 'like', '%' . $request->action . '%');
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('Timestamp', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('Timestamp', '<=', $request->date_to);
        }

        $logs = $query->orderBy('Timestamp', 'desc')->paginate(25);

        // Get list of normal admins for filter dropdown
        $admins = Admin::normalAdmins()->with('user')->get();

        // Get unique action types for filter
        $actionTypes = SystemLog::whereIn('User_ID', $normalAdminIds)
            ->distinct()
            ->pluck('Action');

        return view('admin.logs.index', compact('logs', 'admins', 'actionTypes'));
    }
}