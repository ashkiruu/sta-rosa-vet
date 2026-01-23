<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\ServiceType;
use App\Services\QRCodeService;
use App\Services\CertificateService;
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
        
        // Get appointment limit info for the view
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
        // Validate first to get the Pet_ID
        $validated = $this->validateAppointmentRequest($request);
        
        // Check pending appointment limit for this specific pet
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
        // Validate first to get the Pet_ID
        $validated = $this->validateAppointmentRequest($request);
        
        // Check pending appointment limit for this specific pet
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
        // Validate first to get the Pet_ID
        $validated = $this->validateAppointmentRequest($request, true);
        
        // Check pending appointment limit for this specific pet
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
        return response()->json($this->loadSchedule());
    }

    /**
     * API endpoint to check appointment limit status
     */
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
        
        if ($appointment->Status === 'Completed') {
            return redirect()->route('appointments.verify', [
                'id' => $appointment->Appointment_ID,
                'token' => QRCodeService::generateVerificationToken($appointment)
            ]);
        }

        $qrCodeUrl = QRCodeService::getQRCodeUrl($appointment) 
            ?? $this->generateQRCode($appointment);

        return view('appointments.qrcode', compact('appointment', 'qrCodeUrl'));
    }

    public function downloadQRCode($id)
    {
        $appointment = $this->findUserAppointment($id, ['Approved', 'Completed']);
        
        $path = QRCodeService::getQRCodePath($appointment) 
            ?? QRCodeService::generateForAppointment($appointment);

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
        $message = $attendance['already_checked_in']
            ? "Already checked in at {$attendance['check_in_time']}"
            : 'Successfully checked in!';

        return $this->verifyView(true, $appointment->fresh(['pet', 'service', 'user']), $attendance, $message);
    }

    // =====================================================
    // NOTIFICATION METHODS
    // =====================================================

    public function markNotificationSeen(Request $request)
    {
        $this->addSeenNotification($request->input('key'));
        return response()->json(['success' => true]);
    }

    public function markAllNotificationsSeen()
    {
        $seenNotifications = $this->loadSeenNotifications();
        $userId = $this->userId();
        
        // Mark approved appointments
        Appointment::where('User_ID', $userId)->where('Status', 'Approved')
            ->each(function ($apt) use (&$seenNotifications, $userId) {
                $key = "{$apt->Appointment_ID}_{$apt->Status}_{$apt->updated_at->timestamp}";
                if (!in_array($key, $seenNotifications[$userId] ?? [])) {
                    $seenNotifications[$userId][] = $key;
                }
            });

        // Mark declined notifications
        foreach ($this->loadDeclinedNotifications() as $declined) {
            if ($declined['user_id'] == $userId) {
                $key = "declined_{$declined['pet_name']}_{$declined['date']}_{$declined['declined_at']}";
                if (!in_array($key, $seenNotifications[$userId] ?? [])) {
                    $seenNotifications[$userId][] = $key;
                }
            }
        }

        $this->saveSeenNotifications($seenNotifications);

        return redirect()->back()->with('success', 'All notifications marked as read.');
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

        // Verify pet ownership
        $pet = Pet::find($validated['Pet_ID']);
        if ($pet->Owner_ID != $this->userId()) {
            abort(403, 'This pet does not belong to you.');
        }

        return $validated;
    }

    private function checkAppointmentConflicts(array $data): ?array
    {
        if ($this->isDateClosed($data['Date'])) {
            return ['Date' => 'The clinic is closed on this date. Please select another date.'];
        }

        if ($this->isTimeSlotTaken($data['Date'], $data['Time'])) {
            return ['Time' => 'This time slot is already taken. Please select a different time.'];
        }

        return null;
    }

    /**
     * Check if user has reached their pending appointment limit
     * Each pet can only have one pending appointment at a time
     */
    private function checkPendingAppointmentLimit(?int $petId = null): ?array
    {
        $userId = $this->userId();
        
        // Count user's pets
        $petCount = Pet::where('Owner_ID', $userId)->count();
        
        if ($petCount === 0) {
            return ['limit' => 'You must register at least one pet before booking an appointment.'];
        }
        
        // If a specific pet is being booked, check if that pet already has a pending appointment
        if ($petId) {
            $petHasPending = Appointment::where('User_ID', $userId)
                ->where('Pet_ID', $petId)
                ->where('Status', 'Pending')
                ->exists();
            
            if ($petHasPending) {
                $pet = Pet::find($petId);
                $petName = $pet ? $pet->Pet_Name : 'This pet';
                return [
                    'limit' => "{$petName} already has a pending appointment. Each pet can only have one pending appointment at a time. Please wait for it to be processed or select a different pet."
                ];
            }
        }
        
        // Also check if ALL pets have pending appointments (no available pets to book for)
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
                'limit' => "All your pets already have pending appointments. Each pet can only have one pending appointment at a time. Please wait for your current appointment(s) to be processed before booking new ones."
            ];
        }
        
        return null;
    }

    /**
     * Get appointment limit information for display in views
     */
    private function getAppointmentLimitInfo(): array
    {
        $userId = $this->userId();
        
        $petCount = Pet::where('Owner_ID', $userId)->count();
        $pendingCount = Appointment::where('User_ID', $userId)
            ->where('Status', 'Pending')
            ->count();
        
        // Get pets that already have pending appointments
        $petsWithPending = Appointment::where('User_ID', $userId)
            ->where('Status', 'Pending')
            ->distinct()
            ->pluck('Pet_ID')
            ->toArray();
        
        // Get available pets (those without pending appointments)
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

    private function isDateClosed($date): bool
    {
        $schedule = $this->loadSchedule();
        $dateStr = Carbon::parse($date)->format('Y-m-d');
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        if (in_array($dateStr, $schedule['opened_dates'] ?? [])) {
            return false;
        }

        if (in_array($dateStr, $schedule['closed_dates'] ?? [])) {
            return true;
        }

        return in_array($dayOfWeek, $schedule['default_closed_days'] ?? self::DEFAULT_CLOSED_DAYS);
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

    private function generateQRCode(Appointment $appointment): ?string
    {
        $path = QRCodeService::generateForAppointment($appointment);
        return $path ? asset('storage/' . $path) : null;
    }

    private function verifyView(bool $valid, ?Appointment $appointment, ?array $attendance, string $message)
    {
        return view('appointments.verify', compact('valid', 'appointment', 'attendance', 'message'));
    }

    // =====================================================
    // FILE STORAGE HELPERS
    // =====================================================

    private function getStoragePath(string $filename): string
    {
        return storage_path("app/{$filename}");
    }

    private function loadJsonFile(string $filename, array $default = []): array
    {
        $path = $this->getStoragePath($filename);
        
        if (!file_exists($path)) {
            $this->saveJsonFile($filename, $default);
            return $default;
        }

        return json_decode(file_get_contents($path), true) ?? $default;
    }

    private function saveJsonFile(string $filename, array $data): void
    {
        $path = $this->getStoragePath($filename);
        $directory = dirname($path);
        
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function loadSchedule(): array
    {
        return $this->loadJsonFile('clinic_schedule.json', [
            'default_closed_days' => self::DEFAULT_CLOSED_DAYS,
            'opened_dates' => [],
            'closed_dates' => [],
        ]);
    }

    private function loadSeenNotifications(): array
    {
        return $this->loadJsonFile('seen_notifications.json', []);
    }

    private function saveSeenNotifications(array $data): void
    {
        $this->saveJsonFile('seen_notifications.json', $data);
    }

    private function loadDeclinedNotifications(): array
    {
        return $this->loadJsonFile('declined_notifications.json', []);
    }

    private function addSeenNotification(string $key): void
    {
        $notifications = $this->loadSeenNotifications();
        $userId = $this->userId();

        if (!isset($notifications[$userId])) {
            $notifications[$userId] = [];
        }

        if (!in_array($key, $notifications[$userId])) {
            $notifications[$userId][] = $key;
            $this->saveSeenNotifications($notifications);
        }
    }

    private function getNotifications(): array
    {
        $notifications = [];
        $userId = $this->userId();
        $seenNotifications = $this->loadSeenNotifications()[$userId] ?? [];

        // Approved appointment notifications
        foreach ($this->getUserAppointments() as $apt) {
            if ($apt->updated_at->diffInDays(now()) > self::NOTIFICATION_EXPIRY_DAYS) continue;
            if ($apt->Status !== 'Approved') continue;

            $key = "{$apt->Appointment_ID}_{$apt->Status}_{$apt->updated_at->timestamp}";
            if (in_array($key, $seenNotifications)) continue;

            $notifications[] = [
                'id' => $apt->Appointment_ID,
                'key' => $key,
                'type' => 'success',
                'title' => 'Appointment Approved! 🎉',
                'message' => "Your appointment for {$apt->pet->Pet_Name} on {$apt->Date->format('M d, Y')} at " .
                    Carbon::parse($apt->Time)->format('h:i A') . " has been approved! Your QR code is ready.",
                'time' => $apt->updated_at->diffForHumans(),
                'qr_link' => route('appointments.qrcode', $apt->Appointment_ID),
            ];
        }

        // Declined notifications
        $declinedNotifications = $this->loadDeclinedNotifications();
        $remainingDeclined = [];

        foreach ($declinedNotifications as $declined) {
            if ($declined['user_id'] != $userId) {
                $remainingDeclined[] = $declined;
                continue;
            }

            $declinedAt = Carbon::parse($declined['declined_at']);
            if ($declinedAt->diffInDays(now()) > self::NOTIFICATION_EXPIRY_DAYS) continue;

            $key = "declined_{$declined['pet_name']}_{$declined['date']}_{$declined['declined_at']}";
            if (in_array($key, $seenNotifications)) continue;

            $notifications[] = [
                'id' => 'declined_' . md5($key),
                'key' => $key,
                'type' => 'error',
                'title' => 'Appointment Declined',
                'message' => "Your {$declined['service']} appointment for {$declined['pet_name']} on {$declined['date']} at {$declined['time']} has been declined.",
                'time' => $declinedAt->diffForHumans(),
            ];

            $remainingDeclined[] = $declined;
        }

        $this->saveJsonFile('declined_notifications.json', $remainingDeclined);

        return $notifications;
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
}