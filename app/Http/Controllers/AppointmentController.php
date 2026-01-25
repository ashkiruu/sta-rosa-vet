<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\ServiceType;
use App\Models\ClinicSchedule;
use App\Models\UserNotification;
use App\Services\QRCodeService;
use App\Services\CertificateService;
use App\Services\ClinicScheduleService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class AppointmentController extends Controller
{
    // =====================================================
    // CONSTANTS
    // =====================================================
    
    private const NOTIFICATION_EXPIRY_DAYS = 7;
    private const DEFAULT_CLOSED_DAYS = [0, 6]; // Sunday, Saturday
    private const DEFAULT_LOCATION = 'Veterinary Office';
    
    // =====================================================
    // MAIN VIEWS
    // =====================================================

    public function index()
    {
        $appointments = $this->getUserAppointments();
        $notifications = $this->getNotifications();

        return view('appointments.index', compact('appointments', 'notifications'));
    }

    public function create()
    {
        $pets = Pet::where('Owner_ID', $this->userId())->get();
        $services = ServiceType::all();
        $appointmentLimitInfo = $this->getAppointmentLimitInfo();

        return view('appointments.create', compact('pets', 'services', 'appointmentLimitInfo'));
    }

    public function show($id)
    {
        $appointment = $this->findUserAppointment($id);
        return view('appointments.show', compact('appointment'));
    }

    // =====================================================
    // APPOINTMENT CRUD
    // =====================================================

    public function preview(Request $request)
    {
        $validated = $this->validateAppointmentRequest($request);
        
        if ($limitError = $this->checkPendingAppointmentLimit($validated['Pet_ID'])) {
            return back()->withErrors($limitError)->withInput();
        }
        
        if ($error = $this->checkAppointmentConflicts($validated)) {
            return back()->withErrors($error)->withInput();
        }

        $pet = Pet::find($validated['Pet_ID']);
        $service = ServiceType::find($validated['Service_ID']);
        $appointmentData = $this->buildAppointmentData($validated);

        return view('appointments.confirm', compact('service', 'pet', 'appointmentData'));
    }

    public function confirm(Request $request)
    {
        $validated = $this->validateAppointmentRequest($request);
        
        if ($limitError = $this->checkPendingAppointmentLimit($validated['Pet_ID'])) {
            return redirect()->route('appointments.create')->withErrors($limitError)->withInput();
        }
        
        if ($error = $this->checkAppointmentConflicts($validated)) {
            return redirect()->route('appointments.create')->withErrors($error)->withInput();
        }

        $this->createAppointment($validated);

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment booked successfully! You will be notified once it is reviewed.');
    }

    public function store(Request $request)
    {
        $validated = $this->validateAppointmentRequest($request, true);
        
        if ($limitError = $this->checkPendingAppointmentLimit($validated['Pet_ID'])) {
            return back()->withErrors($limitError)->withInput();
        }
        
        if ($error = $this->checkAppointmentConflicts($validated)) {
            return back()->withErrors($error)->withInput();
        }

        $this->createAppointment($validated);

        return redirect()->route('appointments.index')->with('success', 'Appointment booked successfully!');
    }

    public function cancel($id)
    {
        $appointment = $this->findUserAppointment($id);

        if ($appointment->Status !== 'Pending') {
            return back()->withErrors(['error' => 'Cannot cancel this appointment.']);
        }

        $appointment->delete();

        return redirect()->route('appointments.index')->with('success', 'Appointment cancelled.');
    }

    // =====================================================
    // API ENDPOINTS
    // =====================================================

    public function getTimeSlots()
    {
        return response()->json(
            \DB::table('calendar_time')->where('Is_Active', true)->orderBy('Slot_Val')->get()
        );
    }

    public function getTakenTimes(Request $request)
    {
        $request->validate(['date' => 'required|date']);

        $takenTimes = Appointment::where('Date', $request->date)
            ->whereIn('Status', ['Pending', 'Confirmed', 'Approved'])
            ->pluck('Time')
            ->map(fn($time) => substr($time, 0, 5))
            ->unique()
            ->values();

        return response()->json(['takenTimes' => $takenTimes]);
    }

    public function getFullyBookedDates(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $startDate = sprintf('%d-%02d-01', $request->year, $request->month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $fullyBookedDates = Appointment::selectRaw('Date, COUNT(*) as count')
            ->whereBetween('Date', [$startDate, $endDate])
            ->whereIn('Status', ['Pending', 'Confirmed', 'Approved'])
            ->groupBy('Date')
            ->having('count', '>=', 17)
            ->pluck('Date')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d'));

        return response()->json(['fullyBookedDates' => $fullyBookedDates]);
    }

    public function getClinicSchedule()
    {
        return response()->json(ClinicScheduleService::getSchedule());
    }

    public function checkAppointmentLimit()
    {
        $info = $this->getAppointmentLimitInfo();
        return response()->json($info);
    }

    public function checkStatus($id)
    {
        $appointment = Appointment::where('Appointment_ID', $id)
            ->where('User_ID', $this->userId())
            ->first();

        if (!$appointment) {
            return response()->json(['status' => 'not_found', 'message' => 'Appointment not found'], 404);
        }

        $response = [
            'status' => $appointment->Status,
            'appointment_id' => $appointment->Appointment_ID,
            'qr_released' => QRCodeService::isQRCodeReleased($appointment),
        ];

        if ($appointment->Status === 'Completed') {
            $token = QRCodeService::generateVerificationToken($appointment);
            $response['redirect_url'] = url("/appointments/verify/{$appointment->Appointment_ID}/{$token}");
            $response['message'] = 'Checked in successfully!';
        }

        return response()->json($response);
    }

    // =====================================================
    // QR CODE METHODS
    // =====================================================

    public function showQRCode($id)
    {
        $appointment = $this->findUserAppointment($id, ['Approved', 'Completed']);
        $qrReleased = QRCodeService::isQRCodeReleased($appointment);
        
        if ($appointment->Status === 'Completed') {
            return redirect()->route('appointments.verify', [
                'id' => $appointment->Appointment_ID,
                'token' => QRCodeService::generateVerificationToken($appointment)
            ]);
        }

        if (!$qrReleased) {
            return view('appointments.qrcode_waiting', compact('appointment'));
        }

        $qrCodeUrl = QRCodeService::getQRCodeUrl($appointment);
        QRCodeService::markQRNotificationSeen($appointment->Appointment_ID);

        return view('appointments.qrcode', compact('appointment', 'qrCodeUrl'));
    }

    public function downloadQRCode($id)
    {
        $appointment = $this->findUserAppointment($id, ['Approved', 'Completed']);
        
        if (!QRCodeService::isQRCodeReleased($appointment)) {
            return back()->with('error', 'QR Code has not been released yet. Please wait for the receptionist.');
        }
        
        $path = QRCodeService::getQRCodePath($appointment);

        if ($path) {
            return response()->download(
                storage_path('app/public/' . $path),
                "appointment_{$appointment->Appointment_ID}_qrcode.png"
            );
        }

        return back()->with('error', 'QR Code not available.');
    }

    public function verifyAppointment($id, $token)
    {
        $appointment = Appointment::with(['pet', 'service', 'user'])->find($id);

        if (!$appointment) {
            return $this->verifyView(false, null, null, 'Appointment not found.');
        }

        if (!QRCodeService::verifyToken($appointment, $token)) {
            return $this->verifyView(false, null, null, 'Invalid QR code token.');
        }

        if (!in_array($appointment->Status, ['Approved', 'Completed'])) {
            $message = $appointment->Status === 'Pending'
                ? 'This appointment is still pending approval.'
                : "This appointment cannot be checked in (Status: {$appointment->Status}).";
            return $this->verifyView(true, $appointment, null, $message);
        }

        $attendance = QRCodeService::recordAttendance($appointment);
        
        if (isset($attendance['error']) && isset($attendance['not_released'])) {
            return $this->verifyView(true, $appointment, $attendance, $attendance['message']);
        }

        $message = $attendance['already_checked_in']
            ? "Already checked in at {$attendance['check_in_time']}"
            : 'Successfully checked in!';

        return $this->verifyView(true, $appointment->fresh(['pet', 'service', 'user']), $attendance, $message);
    }

    // =====================================================
    // NOTIFICATION METHODS (Database-backed)
    // =====================================================

    public function markNotificationSeen(Request $request)
    {
        $key = $request->input('key');
        NotificationService::markSeenByKey($this->userId(), $key);
        return response()->json(['success' => true]);
    }

    public function markAllNotificationsSeen(Request $request)
{
    NotificationService::markAllSeen($this->userId());
    
    // If AJAX request, return JSON
    if ($request->wantsJson() || $request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.'
        ]);
    }
    
    // Otherwise redirect back
    return redirect()->back()
        ->with('success', 'All notifications marked as read.');
}

    // =====================================================
    // CERTIFICATE METHODS
    // =====================================================

    public function certificatesIndex()
    {
        $certificates = CertificateService::getCertificatesByOwner($this->userId());
        return view('certificates.index', compact('certificates'));
    }

    public function certificateDownload($id)
    {
        $certificate = CertificateService::getCertificate($id);
        
        abort_if(!$certificate, 404, 'Certificate not found.');
        
        $appointment = Appointment::find($certificate['appointment_id']);
        abort_if(!$appointment || $appointment->User_ID != $this->userId(), 403, 'Unauthorized.');
        abort_if($certificate['status'] !== 'approved', 403, 'Certificate is not yet approved.');

        $pdfPath = $this->ensureCertificatePdf($certificate);

        return response()->file($pdfPath, ['Content-Type' => 'text/html']);
    }

    // =====================================================
    // PRIVATE HELPER METHODS
    // =====================================================

    private function userId(): int
    {
        return Auth::user()->User_ID;
    }

    private function getUserAppointments()
    {
        return Appointment::where('User_ID', $this->userId())
            ->with(['pet', 'service'])
            ->orderBy('Date', 'desc')
            ->get();
    }

    private function findUserAppointment($id, array $statuses = null)
    {
        $query = Appointment::with(['pet', 'service'])
            ->where('Appointment_ID', $id)
            ->where('User_ID', $this->userId());

        if ($statuses) {
            $query->whereIn('Status', $statuses);
        }

        return $query->firstOrFail();
    }

    private function validateAppointmentRequest(Request $request, bool $includeOptional = false): array
    {
        $rules = [
            'Pet_ID' => 'required|exists:pets,Pet_ID',
            'Service_ID' => 'required|exists:service_types,Service_ID',
            'Date' => 'required|date|after_or_equal:today',
            'Time' => 'required',
        ];

        if ($includeOptional) {
            $rules['Location'] = 'nullable|string|max:255';
            $rules['Special_Notes'] = 'nullable|string|max:500';
        }

        $validated = $request->validate($rules);

        $pet = Pet::find($validated['Pet_ID']);
        if ($pet->Owner_ID != $this->userId()) {
            abort(403, 'This pet does not belong to you.');
        }

        return $validated;
    }

    private function checkAppointmentConflicts(array $data): ?array
    {
        if (ClinicScheduleService::isDateClosed($data['Date'])) {
            return ['Date' => 'The clinic is closed on this date. Please select another date.'];
        }

        if ($this->isTimeSlotTaken($data['Date'], $data['Time'])) {
            return ['Time' => 'This time slot is already taken. Please select a different time.'];
        }

        return null;
    }

    private function checkPendingAppointmentLimit(?int $petId = null): ?array
    {
        $userId = $this->userId();
        $petCount = Pet::where('Owner_ID', $userId)->count();
        
        if ($petCount === 0) {
            return ['limit' => 'You must register at least one pet before booking an appointment.'];
        }
        
        if ($petId) {
            $petHasPending = Appointment::where('User_ID', $userId)
                ->where('Pet_ID', $petId)
                ->where('Status', 'Pending')
                ->exists();
            
            if ($petHasPending) {
                $pet = Pet::find($petId);
                $petName = $pet ? $pet->Pet_Name : 'This pet';
                return [
                    'limit' => "{$petName} already has a pending appointment. Each pet can only have one pending appointment at a time."
                ];
            }
        }
        
        $petsWithPending = Appointment::where('User_ID', $userId)
            ->where('Status', 'Pending')
            ->distinct()
            ->pluck('Pet_ID')
            ->toArray();
        
        $availablePets = Pet::where('Owner_ID', $userId)
            ->whereNotIn('Pet_ID', $petsWithPending)
            ->count();
        
        if ($availablePets === 0) {
            return [
                'limit' => "All your pets already have pending appointments. Please wait for your current appointment(s) to be processed."
            ];
        }
        
        return null;
    }

    private function getAppointmentLimitInfo(): array
    {
        $userId = $this->userId();
        
        $petCount = Pet::where('Owner_ID', $userId)->count();
        $pendingCount = Appointment::where('User_ID', $userId)->where('Status', 'Pending')->count();
        
        $petsWithPending = Appointment::where('User_ID', $userId)
            ->where('Status', 'Pending')
            ->distinct()
            ->pluck('Pet_ID')
            ->toArray();
        
        $availablePets = Pet::where('Owner_ID', $userId)
            ->whereNotIn('Pet_ID', $petsWithPending)
            ->get();
        
        $availableCount = $availablePets->count();
        $canBook = $availableCount > 0;
        
        return [
            'pet_count' => $petCount,
            'pending_count' => $pendingCount,
            'available_pets' => $availablePets,
            'available_count' => $availableCount,
            'pets_with_pending' => $petsWithPending,
            'can_book' => $canBook,
            'message' => $canBook 
                ? "You have {$availableCount} pet(s) available for booking."
                : ($petCount === 0 
                    ? "Please register a pet first to book appointments."
                    : "All your pets have pending appointments. Wait for them to be processed.")
        ];
    }

    private function createAppointment(array $data): Appointment
    {
        return Appointment::create([
            'User_ID' => $this->userId(),
            'Pet_ID' => $data['Pet_ID'],
            'Service_ID' => $data['Service_ID'],
            'Date' => $data['Date'],
            'Time' => $data['Time'],
            'Location' => $data['Location'] ?? self::DEFAULT_LOCATION,
            'Status' => 'Pending',
            'Special_Notes' => $data['Special_Notes'] ?? '',
        ]);
    }

    private function buildAppointmentData(array $validated): array
    {
        return [
            'Pet_ID' => $validated['Pet_ID'],
            'Service_ID' => $validated['Service_ID'],
            'Date' => $validated['Date'],
            'Time' => $validated['Time'],
            'Location' => $validated['Location'] ?? self::DEFAULT_LOCATION,
            'Special_Notes' => $validated['Special_Notes'] ?? '',
        ];
    }

    private function isTimeSlotTaken($date, $time): bool
    {
        $normalizedTime = Carbon::parse($time)->format('H:i');

        return Appointment::where('Date', $date)
            ->whereIn('Status', ['Pending', 'Confirmed', 'Approved'])
            ->where(fn($q) => $q->where('Time', $time)
                ->orWhere('Time', $normalizedTime)
                ->orWhere('Time', "{$normalizedTime}:00"))
            ->exists();
    }

    private function verifyView(bool $valid, ?Appointment $appointment, ?array $attendance, string $message)
    {
        return view('appointments.verify', compact('valid', 'appointment', 'attendance', 'message'));
    }

    private function ensureCertificatePdf(array $certificate): string
    {
        if (empty($certificate['pdf_path'])) {
            $certificate['pdf_path'] = CertificateService::generatePdf($certificate);
        }

        $pdfPath = storage_path('app/public/' . $certificate['pdf_path']);

        if (!file_exists($pdfPath)) {
            $certificate['pdf_path'] = CertificateService::generatePdf($certificate);
            $pdfPath = storage_path('app/public/' . $certificate['pdf_path']);
        }

        return $pdfPath;
    }

    /**
     * Get notifications for user (now database-backed)
     */
    private function getNotifications(): array
{
    // Return only unseen notifications
    return NotificationService::getUnseenNotifications($this->userId());
}
}
    