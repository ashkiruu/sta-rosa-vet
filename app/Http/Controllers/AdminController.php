<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Models\Pet;
use App\Models\SystemLog;
use App\Models\Appointment;
use App\Models\Report;
use App\Models\ReportType;
use App\Models\AttendanceLog;
use App\Models\UserNotification;
use App\Services\ReportService;
use App\Services\QRCodeService;
use App\Services\AdminLogService;
use App\Services\CertificateService;
use App\Services\ClinicScheduleService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Log;


class AdminController extends Controller
{
    // =====================================================
    // CONSTANTS
    // =====================================================
    
    private const DEFAULT_CLOSED_DAYS = [0, 6];
    
    private function gcsSignedUrl(?string $objectName, int $minutes = 10): ?string
    {
        if (empty($objectName)) return null;

        $bucketName = env('GOOGLE_CLOUD_STORAGE_BUCKET');
        $projectId  = env('GOOGLE_CLOUD_PROJECT_ID', env('GCLOUD_PROJECT'));

        $storage = new StorageClient(['projectId' => $projectId]);
        $bucket  = $storage->bucket($bucketName);

        $obj = $bucket->object($objectName);

        if (!$obj->exists()) {
            Log::warning('GCS_ID_IMAGE_MISSING', ['object' => $objectName]);
            return null;
        }

        return $obj->signedUrl(new \DateTime("+{$minutes} minutes"));
    }

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
            'today_appointments' => Appointment::whereDate('Date', today())->count(),
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
        $user = User::findOrFail($id);
        $pets = Pet::where('Owner_ID', $id)->get();

        $latestProcessing = \App\Models\MlOcrProcessing::where('User_ID', $user->User_ID)
            ->orderByDesc('Created_Date')
            ->first();

        $idImageUrl = null;
        try {
            $idImageUrl = $this->gcsSignedUrl($latestProcessing?->Document_Image_Path, 10);
        } catch (\Throwable $e) {
            \Log::error('GCS_SIGNED_URL_ERROR', [
                'message' => $e->getMessage(),
                'path' => $latestProcessing?->Document_Image_Path,
                'user' => $user->User_ID,
            ]);
        }

        return view('admin.user_details', compact('user', 'pets', 'idImageUrl', 'latestProcessing'));
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
        // Auto-clean expired pending appointments (scheduled date has passed)
        $this->cleanExpiredPendingAppointments();

        $appointments = Appointment::with(['user', 'pet', 'service'])
            ->whereIn('Status', ['Pending', 'Approved', 'No Show'])
            ->orderBy('Date')->orderBy('Time')
            ->get()
            ->map(fn($apt) => tap($apt, function ($a) {
                $a->Date = $a->Date->format('Y-m-d');
                $a->Time = Carbon::parse($a->Time)->format('H:i');
                $a->qr_released = QRCodeService::isQRCodeReleased($a);
            }));

        return view('admin.appointment_index', [
            'appointments' => $appointments,
            'schedule' => ClinicScheduleService::getSchedule()
        ]);
    }

    public function approveAppointment($id)
    {
        $appointment = Appointment::with(['pet', 'user', 'service'])->findOrFail($id);

        if ($appointment->Status !== 'Pending') {
            return back()->with('error', 'This appointment cannot be approved.');
        }

        $appointment->update(['Status' => 'Approved']);
        
        NotificationService::appointmentApproved($appointment);
        
        \Log::info("Appointment {$id} approved. QR code will be released when patient arrives.");

        AdminLogService::logAppointmentAction(
            $id, 'approved', 
            $appointment->pet->Pet_Name,
            "{$appointment->user->First_Name} {$appointment->user->Last_Name}"
        );

        return back()->with('success', 
            "Appointment for {$appointment->pet->Pet_Name} has been approved! Release QR code when patient arrives.");
    }

    public function releaseQRCode($id)
    {
        $appointment = Appointment::with(['pet', 'user', 'service'])->findOrFail($id);

        if ($appointment->Status !== 'Approved') {
            return back()->with('error', 'QR code can only be released for approved appointments.');
        }

        if (QRCodeService::isQRCodeReleased($appointment)) {
            return back()->with('info', 'QR code has already been released for this appointment.');
        }

        $adminName = auth()->user()->First_Name ?? 'Admin';
        $result = QRCodeService::releaseQRCode($appointment, $adminName);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        AdminLogService::log(
            'QR_CODE_RELEASED', 
            "QR code released for {$appointment->pet->Pet_Name}'s appointment (ID: {$id})"
        );

        return back()->with('success', 
            "QR code released for {$appointment->pet->Pet_Name}! The patient has been notified.");
    }

    public function markNoShow($id)
    {
        $appointment = Appointment::with(['pet', 'user', 'service'])->findOrFail($id);

        if ($appointment->Status !== 'Approved') {
            return back()->with('error', 'Only approved appointments can be marked as no-show.');
        }

        $existingAttendance = AttendanceLog::where('Appointment_ID', $id)
            ->whereIn('Status', ['checked_in', 'completed'])
            ->first();

        if ($existingAttendance) {
            return back()->with('error', 'This appointment has already been checked in and cannot be marked as no-show.');
        }

        $appointment->update(['Status' => 'No Show']);

        AttendanceLog::updateOrCreate(
            ['Appointment_ID' => $id],
            [
                'User_ID' => $appointment->User_ID,
                'Pet_Name' => $appointment->pet->Pet_Name,
                'Owner_Name' => "{$appointment->user->First_Name} {$appointment->user->Last_Name}",
                'Service' => $appointment->service->Service_Name ?? null,
                'Scheduled_Date' => $appointment->Date,
                'Scheduled_Time' => $appointment->Time,
                'Check_In_Time' => now(),
                'Check_In_Date' => now()->toDateString(),
                'Scanned_By' => auth()->user()->First_Name ?? 'Admin',
                'Status' => 'no_show',
            ]
        );

        AdminLogService::log(
            'APPOINTMENT_NO_SHOW',
            "Marked appointment for {$appointment->pet->Pet_Name} as no-show (ID: {$id})"
        );

        return back()->with('success', 
            "{$appointment->pet->Pet_Name}'s appointment has been marked as No Show.");
    }

    public function cancelAppointment(Request $request, $id)
    {
        $appointment = Appointment::with(['pet', 'user', 'service'])->findOrFail($id);

        if (!in_array($appointment->Status, ['Pending', 'Approved'])) {
            return back()->with('error', 'This appointment cannot be cancelled.');
        }

        $reason = $request->input('reason', 'Cancelled by administrator');
        $petName = $appointment->pet->Pet_Name;
        $ownerName = "{$appointment->user->First_Name} {$appointment->user->Last_Name}";
        $date = $appointment->Date instanceof \Carbon\Carbon 
            ? $appointment->Date->format('M d, Y') 
            : Carbon::parse($appointment->Date)->format('M d, Y');
        $time = Carbon::parse($appointment->Time)->format('h:i A');

        UserNotification::create([
            'User_ID' => $appointment->User_ID,
            'Type' => 'appointment_cancelled',
            'Title' => 'Appointment Cancelled',
            'Message' => "Your appointment for {$petName} on {$date} at {$time} has been cancelled. Reason: {$reason}",
            'Reference_Type' => 'appointment',
            'Reference_ID' => $id,
            'Data' => [
                'pet_name' => $petName,
                'service' => $appointment->service->Service_Name ?? 'N/A',
                'date' => $date,
                'time' => $time,
                'reason' => $reason,
                'cancelled_at' => now()->toDateTimeString(),
            ],
            'Expires_At' => now()->addDays(7),
        ]);

        AdminLogService::log(
            'APPOINTMENT_CANCELLED',
            "Cancelled appointment for {$petName} (Owner: {$ownerName}) scheduled on {$date} at {$time}. Reason: {$reason}"
        );

        $appointment->delete();

        return back()->with('success', 
            "Appointment for {$petName} on {$date} at {$time} has been cancelled.");
    }

    public function rejectAppointment($id)
    {
        $appointment = Appointment::with(['pet', 'user', 'service'])->findOrFail($id);

        if ($appointment->Status !== 'Pending') {
            return back()->with('error', 'This appointment cannot be rejected.');
        }

        NotificationService::appointmentDeclined($appointment);
        
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

        $result = ClinicScheduleService::toggleDateStatus(
            $request->date, 
            $request->action,
            auth()->id()
        );

        $dayName = Carbon::parse($request->date)->format('l, M d, Y');
        AdminLogService::logScheduleChange($dayName, strtoupper($request->action));

        return back()->with('success', $result['message']);
    }

    public function attendance(Request $request)
    {
        $selectedDate = $request->get('date', now()->format('Y-m-d'));
        
        $allLogs = AttendanceLog::getAllArray();
        $todayLogs = AttendanceLog::getByDateArray(now()->format('Y-m-d'));
        $filteredLogs = AttendanceLog::getByDateArray($selectedDate);

        usort($filteredLogs, fn($a, $b) => strtotime($b['check_in_time']) - strtotime($a['check_in_time']));

        return view('admin.attendance', [
            'allLogs' => $allLogs,
            'todayLogs' => $todayLogs,
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
            $signatureData = $request->input('signature_data');
            
            if (empty($signatureData)) {
                return back()->withInput()->with('error', 'Signature is required to approve the certificate.');
            }
            
            return $this->approveCertificateAndRedirect(
                $certificate['id'], 
                $data['pet_name'], 
                'created and approved',
                $signatureData
            );
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
            $signatureData = $request->input('signature_data');
            
            if (empty($signatureData)) {
                return back()->withInput()->with('error', 'Signature is required to approve the certificate.');
            }
            
            return $this->approveCertificateAndRedirect(
                $id, 
                $request->pet_name, 
                'updated and approved',
                $signatureData
            );
        }

        return redirect()->route('admin.certificates.index')->with('success', 'Certificate updated successfully.');
    }

    public function certificatesApprove($id)
    {
        return redirect()->route('admin.certificates.edit', $id)
            ->with('info', 'Please add your signature before approving the certificate.');
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
        $serviceType = $request->input('service_type', '');
        $serviceTypeLower = strtolower($serviceType);
        
        $isVaccination = strpos($serviceTypeLower, 'vaccination') !== false || strpos($serviceTypeLower, 'vaccine') !== false;
        $isDeworming = strpos($serviceTypeLower, 'deworming') !== false;
        $isCheckup = strpos($serviceTypeLower, 'checkup') !== false || strpos($serviceTypeLower, 'check-up') !== false;

        $rules = [
            'pet_name' => 'required|string|max:255',
            'animal_type' => 'required|string|max:100',
            'pet_gender' => 'required|string|max:50',
            'pet_age' => 'required|string|max:100',
            'pet_breed' => 'required|string|max:255',
            'pet_color' => 'required|string|max:100',
            'pet_dob' => 'nullable|date',
            'owner_name' => 'required|string|max:255',
            'owner_address' => 'required|string',
            'owner_phone' => 'required|string|max:50',
            'civil_status' => 'required|string|max:50',
            'years_of_residency' => 'required|string|max:100',
            'owner_birthdate' => 'nullable|date',
            'service_type' => 'required|string|max:255',
            'service_date' => 'required|date',
            'next_service_date' => 'nullable|date',
            'veterinarian_name' => 'required|string|max:255',
            'license_number' => 'required|string|max:100',
            'ptr_number' => 'required|string|max:100',
            'signature_data' => 'nullable|string',
        ];

        if ($isVaccination) {
            $rules['vaccine_type'] = 'required|in:anti-rabies,other';
            $vaccineType = $request->input('vaccine_type');
            if ($vaccineType === 'anti-rabies') {
                $rules['vaccine_name_rabies'] = 'required|string|max:255';
                $rules['lot_number'] = 'required|string|max:100';
            } elseif ($vaccineType === 'other') {
                $rules['vaccine_name_other'] = 'required|string|max:255';
                $rules['lot_number_other'] = 'required|string|max:100';
            }
        }

        if ($isDeworming) {
            $rules['medicine_used'] = 'nullable|string|max:255';
            $rules['dosage'] = 'nullable|string|max:100';
        }

        if ($isCheckup) {
            $rules['findings'] = 'nullable|string';
            $rules['recommendations'] = 'nullable|string';
        }

        if ($includeAppointmentId) {
            $rules['appointment_id'] = 'required';
        }

        $validated = $request->validate($rules);

        if ($isVaccination) {
            $vaccineType = $request->input('vaccine_type');
            $validated['vaccine_type'] = $vaccineType;
            
            if ($vaccineType === 'anti-rabies') {
                $validated['vaccine_name_rabies'] = $request->input('vaccine_name_rabies');
                $validated['vaccine_used'] = $request->input('vaccine_name_rabies');
                $validated['lot_number'] = $request->input('lot_number');
            } elseif ($vaccineType === 'other') {
                $validated['vaccine_used'] = $request->input('vaccine_name_other');
                $validated['lot_number'] = $request->input('lot_number_other');
            }
        }

        if (isset($validated['service_date'])) {
            $validated['vaccination_date'] = $validated['service_date'];
        }
        if (isset($validated['next_service_date'])) {
            $validated['next_vaccination_date'] = $validated['next_service_date'];
        }

        return $validated;
    }

    private function approveCertificateAndRedirect($id, $petName, $action, $signatureData = null): \Illuminate\Http\RedirectResponse
    {
        CertificateService::approveCertificate($id, auth()->user()->First_Name ?? 'Admin', $signatureData);
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
        $summary = ReportService::getWeeklySummary($startDate, $endDate);

        $reports = ReportService::createWeeklyReports([
            'week_number' => $weekNumber,
            'year' => $year,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'generated_by' => auth()->user()->First_Name ?? 'Admin',
            'summary' => $summary,
            'anti_rabies_count' => count($antiRabiesData),
            'routine_services_count' => count($routineServicesData),
        ]);

        $reportData = [
            'report_number' => $reports['anti_rabies']->Report_Number ?? "RPT-WEEKLY-{$year}-W{$weekNumber}",
            'week_number' => $weekNumber,
            'year' => $year,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'generated_by' => auth()->user()->First_Name ?? 'Admin',
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'anti_rabies_data' => $antiRabiesData,
            'routine_services_data' => $routineServicesData,
            'anti_rabies_id' => $reports['anti_rabies']->Report_ID ?? null,
            'routine_services_id' => $reports['routine_services']->Report_ID ?? null,
        ];

        ReportService::generateAntiRabiesPdf($reportData);
        ReportService::generateRoutineServicesPdf($reportData);

        AdminLogService::logReportGeneration('WEEKLY', $weekNumber, $year);

        return redirect()->route('admin.reports')
            ->with('success', "Weekly report for Week {$weekNumber}, {$year} generated successfully!");
    }

    public function viewAntiRabiesReport($id)
    {
        return $this->serveReportPdf($id, 'anti_rabies');
    }

    public function viewRoutineServicesReport($id)
    {
        return $this->serveReportPdf($id, 'routine_services');
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

    private function serveReportPdf($id, $reportType)
    {
        $report = Report::with('reportType')->find($id);
        
        if (!$report) {
            abort(404, 'Report not found.');
        }
        
        $isAntiRabies = $report->ReportType_ID === ReportType::ANTI_RABIES;
        $isRoutineServices = $report->ReportType_ID === ReportType::ROUTINE_SERVICES;
        
        $filePath = $report->File_Path;
        $fullPath = $filePath ? storage_path('app/public/' . $filePath) : null;
        
        if (!$fullPath || !file_exists($fullPath)) {
            $startDate = $report->Start_Date;
            $endDate = $report->End_Date;
            
            $reportData = [
                'id' => $report->Report_ID,
                'report_number' => $report->Report_Number,
                'week_number' => $report->Week_Number,
                'year' => $report->Year,
                'start_date' => $report->Start_Date->format('Y-m-d'),
                'end_date' => $report->End_Date->format('Y-m-d'),
                'generated_by' => $report->Generated_By,
                'generated_at' => $report->Generated_At->format('Y-m-d H:i:s'),
            ];
            
            if ($isAntiRabies) {
                $reportData['anti_rabies_data'] = ReportService::getAntiRabiesData($startDate, $endDate);
                $reportData['anti_rabies_id'] = $report->Report_ID;
                $filePath = ReportService::generateAntiRabiesPdf($reportData);
            } elseif ($isRoutineServices) {
                $reportData['routine_services_data'] = ReportService::getRoutineServicesData($startDate, $endDate);
                $reportData['routine_services_id'] = $report->Report_ID;
                $filePath = ReportService::generateRoutineServicesPdf($reportData);
            } else {
                abort(404, 'Unknown report type.');
            }
            
            $fullPath = storage_path('app/public/' . $filePath);
        }
        
        return response()->file($fullPath, ['Content-Type' => 'text/html']);
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

    /**
     * Clean up expired pending appointments (scheduled date has passed without approval).
     * Notifies affected users and logs the cleanup.
     */
    private function cleanExpiredPendingAppointments(): void
    {
        $expiredAppointments = Appointment::with(['pet', 'user', 'service'])
            ->where('Status', 'Pending')
            ->whereDate('Date', '<', today())
            ->get();

        if ($expiredAppointments->isEmpty()) {
            return;
        }

        foreach ($expiredAppointments as $appointment) {
            try {
                $petName = $appointment->pet->Pet_Name ?? 'your pet';
                $date = $appointment->Date instanceof Carbon
                    ? $appointment->Date->format('M d, Y')
                    : Carbon::parse($appointment->Date)->format('M d, Y');
                $time = Carbon::parse($appointment->Time)->format('h:i A');

                UserNotification::create([
                    'User_ID' => $appointment->User_ID,
                    'Type' => 'appointment_expired',
                    'Title' => 'Appointment Expired',
                    'Message' => "Your pending appointment for {$petName} on {$date} at {$time} was not approved in time and has been automatically removed. Please book a new appointment.",
                    'Reference_Type' => 'appointment',
                    'Reference_ID' => $appointment->Appointment_ID,
                    'Data' => [
                        'pet_name' => $petName,
                        'service' => $appointment->service->Service_Name ?? 'N/A',
                        'date' => $date,
                        'time' => $time,
                        'expired_at' => now()->toDateTimeString(),
                    ],
                    'Expires_At' => now()->addDays(7),
                ]);
            } catch (\Throwable $e) {
                Log::warning("Failed to notify user for expired appointment {$appointment->Appointment_ID}: {$e->getMessage()}");
            }

            $appointment->delete();
        }

        Log::info("Auto-cleaned {$expiredAppointments->count()} expired pending appointment(s).");
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