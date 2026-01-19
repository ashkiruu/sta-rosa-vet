<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\ServiceType;
use App\Services\QRCodeService;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CalendarTime;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::where('User_ID', Auth::user()->User_ID)
            ->with(['pet', 'service'])
            ->orderBy('Date', 'desc')
            ->get();

        // Check for status changes and prepare notifications
        $notifications = $this->checkForStatusChanges($appointments);

        return view('appointments.index', compact('appointments', 'notifications'));
    }

    public function getTimeSlots()
    {
        $slots = \DB::table('calendar_time')
                    ->where('Is_Active', true)
                    ->orderBy('Slot_Val', 'asc')
                    ->get();

        return response()->json($slots);
    }

    /**
     * Check for appointment status changes and generate notifications
     */
    private function checkForStatusChanges($appointments)
    {
        $notifications = [];
        $userId = Auth::user()->User_ID;
        
        // Load seen notifications from file (persistent across sessions)
        $seenFile = storage_path('app/seen_notifications.json');
        $seenNotifications = [];
        if (file_exists($seenFile)) {
            $seenNotifications = json_decode(file_get_contents($seenFile), true) ?? [];
        }
        
        // Get seen notifications for this user
        $userSeenNotifications = $seenNotifications[$userId] ?? [];

        // Check for approved appointments
        foreach ($appointments as $appointment) {
            // Only show notifications for recently updated appointments (last 7 days)
            if ($appointment->updated_at->diffInDays(now()) > 7) {
                continue;
            }

            // Create a unique key for this status change
            $notificationKey = $appointment->Appointment_ID . '_' . $appointment->Status . '_' . $appointment->updated_at->timestamp;

            // Skip if already seen
            if (in_array($notificationKey, $userSeenNotifications)) {
                continue;
            }

            // Generate notification for approved appointments
            if ($appointment->Status === 'Approved') {
                $notifications[] = [
                    'id' => $appointment->Appointment_ID,
                    'key' => $notificationKey,
                    'type' => 'success',
                    'title' => 'Appointment Approved! 🎉',
                    'message' => "Your appointment for {$appointment->pet->Pet_Name} on " . 
                                $appointment->Date->format('M d, Y') . " at " . 
                                \Carbon\Carbon::parse($appointment->Time)->format('h:i A') . 
                                " has been approved! Your QR code is ready.",
                    'time' => $appointment->updated_at->diffForHumans(),
                    'qr_link' => route('appointments.qrcode', $appointment->Appointment_ID),
                ];
            }
        }

        // Check for declined notifications from file
        $declinedNotificationsFile = storage_path('app/declined_notifications.json');
        if (file_exists($declinedNotificationsFile)) {
            $declinedNotifications = json_decode(file_get_contents($declinedNotificationsFile), true) ?? [];
            $remainingNotifications = [];
            
            foreach ($declinedNotifications as $declined) {
                // Only show to the user who owned the appointment
                if ($declined['user_id'] != $userId) {
                    $remainingNotifications[] = $declined;
                    continue;
                }
                
                // Check if older than 7 days
                $declinedAt = \Carbon\Carbon::parse($declined['declined_at']);
                if ($declinedAt->diffInDays(now()) > 7) {
                    continue; // Don't add to remaining, effectively deleting old notifications
                }
                
                // Create unique key
                $notificationKey = 'declined_' . $declined['pet_name'] . '_' . $declined['date'] . '_' . $declined['declined_at'];
                
                // Skip if already seen
                if (in_array($notificationKey, $userSeenNotifications)) {
                    continue; // Don't show but keep in file for record
                }
                
                $notifications[] = [
                    'id' => 'declined_' . md5($notificationKey),
                    'key' => $notificationKey,
                    'type' => 'error',
                    'title' => 'Appointment Declined',
                    'message' => "Your {$declined['service']} appointment for {$declined['pet_name']} on {$declined['date']} at {$declined['time']} has been declined. Please book a new appointment.",
                    'time' => $declinedAt->diffForHumans(),
                ];
                
                $remainingNotifications[] = $declined;
            }
            
            // Update the file with remaining notifications
            file_put_contents($declinedNotificationsFile, json_encode($remainingNotifications));
        }

        return $notifications;
    }

    /**
     * Mark a notification as seen (persistent - survives logout)
     */
    public function markNotificationSeen(Request $request)
    {
        $key = $request->input('key');
        $userId = Auth::user()->User_ID;
        
        // Load seen notifications from file
        $seenFile = storage_path('app/seen_notifications.json');
        $seenNotifications = [];
        
        // Auto-create file if it doesn't exist
        if (!file_exists($seenFile)) {
            $directory = dirname($seenFile);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            file_put_contents($seenFile, json_encode([]));
        } else {
            $seenNotifications = json_decode(file_get_contents($seenFile), true) ?? [];
        }
        
        // Initialize user's array if not exists
        if (!isset($seenNotifications[$userId])) {
            $seenNotifications[$userId] = [];
        }
        
        // Add the key if not already there
        if (!in_array($key, $seenNotifications[$userId])) {
            $seenNotifications[$userId][] = $key;
            file_put_contents($seenFile, json_encode($seenNotifications, JSON_PRETTY_PRINT));
        }

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as seen (persistent - survives logout)
     */
    public function markAllNotificationsSeen()
    {
        $userId = Auth::user()->User_ID;
        
        // Load seen notifications from file
        $seenFile = storage_path('app/seen_notifications.json');
        $seenNotifications = [];
        
        // Auto-create file if it doesn't exist
        if (!file_exists($seenFile)) {
            $directory = dirname($seenFile);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            file_put_contents($seenFile, json_encode([]));
        } else {
            $seenNotifications = json_decode(file_get_contents($seenFile), true) ?? [];
        }
        
        // Initialize user's array if not exists
        if (!isset($seenNotifications[$userId])) {
            $seenNotifications[$userId] = [];
        }
        
        // Mark approved appointment notifications as seen
        $appointments = Appointment::where('User_ID', $userId)
            ->where('Status', 'Approved')
            ->get();
        
        foreach ($appointments as $appointment) {
            $key = $appointment->Appointment_ID . '_' . $appointment->Status . '_' . $appointment->updated_at->timestamp;
            if (!in_array($key, $seenNotifications[$userId])) {
                $seenNotifications[$userId][] = $key;
            }
        }
        
        // Also mark declined notifications as seen
        $declinedNotificationsFile = storage_path('app/declined_notifications.json');
        if (file_exists($declinedNotificationsFile)) {
            $declinedNotifications = json_decode(file_get_contents($declinedNotificationsFile), true) ?? [];
            
            foreach ($declinedNotifications as $declined) {
                if ($declined['user_id'] == $userId) {
                    $notificationKey = 'declined_' . $declined['pet_name'] . '_' . $declined['date'] . '_' . $declined['declined_at'];
                    if (!in_array($notificationKey, $seenNotifications[$userId])) {
                        $seenNotifications[$userId][] = $notificationKey;
                    }
                }
            }
        }
        
        // Save to file
        file_put_contents($seenFile, json_encode($seenNotifications, JSON_PRETTY_PRINT));

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Get clinic schedule configuration
     * Auto-creates the file if it doesn't exist
     */
    public function getClinicSchedule()
    {
        $schedulePath = storage_path('app/clinic_schedule.json');
        
        // Auto-create the file if it doesn't exist
        if (!file_exists($schedulePath)) {
            $defaultSchedule = [
                'default_closed_days' => [0, 6], // Sunday and Saturday
                'opened_dates' => [],
                'closed_dates' => [],
            ];
            
            // Ensure directory exists
            $directory = dirname($schedulePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            
            file_put_contents($schedulePath, json_encode($defaultSchedule, JSON_PRETTY_PRINT));
            return response()->json($defaultSchedule);
        }
        
        $schedule = json_decode(file_get_contents($schedulePath), true);
        return response()->json($schedule);
    }

    public function create()
    {
        $pets = Pet::where('Owner_ID', Auth::user()->User_ID)->get();
        $services = ServiceType::all();

        return view('appointments.create', compact('pets', 'services'));
    }

    /**
     * Get taken time slots for a specific date
     */
    public function getTakenTimes(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->date;

        // Get all appointments for this date that are not cancelled
        $takenTimes = Appointment::where('Date', $date)
            ->whereIn('Status', ['Pending', 'Confirmed', 'Approved'])
            ->pluck('Time')
            ->map(function ($time) {
                if (strlen($time) > 5) {
                    return substr($time, 0, 5);
                }
                return $time;
            })
            ->unique()
            ->values()
            ->toArray();

        return response()->json([
            'takenTimes' => $takenTimes
        ]);
    }

    /**
     * Check if a date is closed (weekend or admin-closed)
     */
    private function isDateClosed($date)
    {
        $schedulePath = storage_path('app/clinic_schedule.json');
        
        if (!file_exists($schedulePath)) {
            // Default: weekends are closed
            $dayOfWeek = \Carbon\Carbon::parse($date)->dayOfWeek;
            return in_array($dayOfWeek, [0, 6]); // Sunday = 0, Saturday = 6
        }
        
        $schedule = json_decode(file_get_contents($schedulePath), true);
        $dateStr = \Carbon\Carbon::parse($date)->format('Y-m-d');
        $dayOfWeek = \Carbon\Carbon::parse($date)->dayOfWeek;
        
        // Check if explicitly opened (overrides default closed days)
        if (in_array($dateStr, $schedule['opened_dates'] ?? [])) {
            return false;
        }
        
        // Check if explicitly closed
        if (in_array($dateStr, $schedule['closed_dates'] ?? [])) {
            return true;
        }
        
        // Check default closed days (weekends)
        return in_array($dayOfWeek, $schedule['default_closed_days'] ?? [0, 6]);
    }

    /**
     * Check if a time slot is already taken
     */
    private function isTimeSlotTaken($date, $time)
    {
        $normalizedTime = \Carbon\Carbon::parse($time)->format('H:i');
        
        return Appointment::where('Date', $date)
            ->whereIn('Status', ['Pending', 'Confirmed', 'Approved'])
            ->where(function ($query) use ($time, $normalizedTime) {
                $query->where('Time', $time)
                      ->orWhere('Time', $normalizedTime)
                      ->orWhere('Time', $normalizedTime . ':00');
            })
            ->exists();
    }

    /**
     * Show the appointment preview/confirmation page
     */
    public function preview(Request $request)
    {
        $request->validate([
            'Pet_ID' => 'required|exists:pets,Pet_ID',
            'Service_ID' => 'required|exists:service_types,Service_ID',
            'Date' => 'required|date|after_or_equal:today',
            'Time' => 'required',
        ]);

        // Verify pet belongs to user
        $pet = Pet::find($request->Pet_ID);
        if ($pet->Owner_ID != Auth::user()->User_ID) {
            return back()->withErrors(['Pet_ID' => 'This pet does not belong to you.']);
        }

        // Check if the date is closed
        if ($this->isDateClosed($request->Date)) {
            return back()->withErrors(['Date' => 'The clinic is closed on this date. Please select another date.'])
                         ->withInput();
        }

        // Check if time slot is already taken
        if ($this->isTimeSlotTaken($request->Date, $request->Time)) {
            return back()->withErrors(['Time' => 'This time slot is already taken. Please select a different time.'])
                         ->withInput();
        }

        $service = ServiceType::find($request->Service_ID);

        $appointmentData = [
            'Pet_ID' => $request->Pet_ID,
            'Service_ID' => $request->Service_ID,
            'Date' => $request->Date,
            'Time' => $request->Time,
            'Location' => $request->Location ?? 'Veterinary Office',
            'Special_Notes' => $request->Special_Notes ?? '',
        ];

        return view('appointments.confirm', compact('service', 'pet', 'appointmentData'));
    }

    /**
     * Confirm and store the appointment
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'Pet_ID' => 'required|exists:pets,Pet_ID',
            'Service_ID' => 'required|exists:service_types,Service_ID',
            'Date' => 'required|date|after_or_equal:today',
            'Time' => 'required',
        ]);

        // Verify pet belongs to user
        $pet = Pet::find($request->Pet_ID);
        if ($pet->Owner_ID != Auth::user()->User_ID) {
            return back()->withErrors(['Pet_ID' => 'This pet does not belong to you.']);
        }

        // Check if the date is closed
        if ($this->isDateClosed($request->Date)) {
            return redirect()->route('appointments.create')
                ->withErrors(['Date' => 'The clinic is closed on this date. Please select another date.'])
                ->withInput();
        }

        // Check if time slot is already taken (double-check before saving)
        if ($this->isTimeSlotTaken($request->Date, $request->Time)) {
            return redirect()->route('appointments.create')
                ->withErrors(['Time' => 'Sorry, this time slot was just taken by another user. Please select a different time.'])
                ->withInput();
        }

        $service = ServiceType::find($request->Service_ID);

        $appointment = new Appointment();
        $appointment->User_ID = Auth::user()->User_ID;
        $appointment->Pet_ID = $request->Pet_ID;
        $appointment->Service_ID = $request->Service_ID;
        $appointment->Date = $request->Date;
        $appointment->Time = $request->Time;
        $appointment->Location = $request->Location ?? 'Veterinary Office';
        $appointment->Status = 'Pending';
        $appointment->Special_Notes = $request->Special_Notes ?? '';
        $appointment->save();

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment booked successfully! You will be notified once it is reviewed.');
    }

    /**
     * Original store method
     */
    public function store(Request $request)
    {
        $request->validate([
            'Pet_ID' => 'required|exists:pets,Pet_ID',
            'Service_ID' => 'required|exists:service_types,Service_ID',
            'Date' => 'required|date|after_or_equal:today',
            'Time' => 'required',
            'Location' => 'nullable|string|max:255',
            'Special_Notes' => 'nullable|string|max:500',
        ]);

        $pet = Pet::find($request->Pet_ID);
        if ($pet->Owner_ID != Auth::user()->User_ID) {
            return back()->withErrors(['Pet_ID' => 'This pet does not belong to you.']);
        }

        // Check if the date is closed
        if ($this->isDateClosed($request->Date)) {
            return back()->withErrors(['Date' => 'The clinic is closed on this date. Please select another date.'])
                         ->withInput();
        }

        if ($this->isTimeSlotTaken($request->Date, $request->Time)) {
            return back()->withErrors(['Time' => 'This time slot is already taken. Please select a different time.'])
                         ->withInput();
        }

        $appointment = new Appointment();
        $appointment->User_ID = Auth::user()->User_ID;
        $appointment->Pet_ID = $request->Pet_ID;
        $appointment->Service_ID = $request->Service_ID;
        $appointment->Date = $request->Date;
        $appointment->Time = $request->Time;
        $appointment->Location = $request->Location ?? 'Veterinary Office';
        $appointment->Status = 'Pending';
        $appointment->Special_Notes = $request->Special_Notes ?? '';
        $appointment->save();

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment booked successfully!');
    }

    public function show($id)
    {
        $appointment = Appointment::with(['pet', 'service'])
            ->where('Appointment_ID', $id)
            ->where('User_ID', Auth::user()->User_ID)
            ->firstOrFail();

        return view('appointments.show', compact('appointment'));
    }

    public function cancel($id)
    {
        $appointment = Appointment::where('Appointment_ID', $id)
            ->where('User_ID', Auth::user()->User_ID)
            ->firstOrFail();

        if ($appointment->Status != 'Pending') {
            return back()->withErrors(['error' => 'Cannot cancel this appointment.']);
        }

        $appointment->delete();

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment cancelled.');
    }

    /**
     * Get fully booked dates for a given month
     */
    public function getFullyBookedDates(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year = $request->year;
        $month = $request->month;
        
        $totalSlots = 17;
        
        $startDate = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $appointmentCounts = Appointment::selectRaw('Date, COUNT(*) as count')
            ->whereBetween('Date', [$startDate, $endDate])
            ->whereIn('Status', ['Pending', 'Confirmed', 'Approved'])
            ->groupBy('Date')
            ->get();
        
        $fullyBookedDates = [];
        foreach ($appointmentCounts as $record) {
            if ($record->count >= $totalSlots) {
                $fullyBookedDates[] = \Carbon\Carbon::parse($record->Date)->format('Y-m-d');
            }
        }
        
        return response()->json([
            'fullyBookedDates' => $fullyBookedDates
        ]);
    }

    /**
     * Verify appointment from QR code scan
     * This is a public route (no auth required)
     * AUTOMATICALLY records attendance when scanned!
     */
    public function verifyAppointment($id, $token)
    {
        $appointment = Appointment::with(['pet', 'service', 'user'])->find($id);
        
        if (!$appointment) {
            return view('appointments.verify', [
                'valid' => false,
                'appointment' => null,
                'attendance' => null,
                'message' => 'Appointment not found.'
            ]);
        }
        
        // Verify the token
        $valid = QRCodeService::verifyToken($appointment, $token);
        
        if (!$valid) {
            return view('appointments.verify', [
                'valid' => false,
                'appointment' => null,
                'attendance' => null,
                'message' => 'Invalid QR code token.'
            ]);
        }
        
        // Check if appointment is approved (can only check-in approved appointments)
        if ($appointment->Status !== 'Approved' && $appointment->Status !== 'Completed') {
            return view('appointments.verify', [
                'valid' => true,
                'appointment' => $appointment,
                'attendance' => null,
                'message' => $appointment->Status === 'Pending' 
                    ? 'This appointment is still pending approval.' 
                    : 'This appointment cannot be checked in (Status: ' . $appointment->Status . ').'
            ]);
        }
        
        // Record attendance (this updates the appointment status to "Completed")
        $attendance = QRCodeService::recordAttendance($appointment);
        
        return view('appointments.verify', [
            'valid' => true,
            'appointment' => $appointment->fresh(['pet', 'service', 'user']), // Refresh to get updated status
            'attendance' => $attendance,
            'message' => $attendance['already_checked_in'] 
                ? 'Already checked in at ' . $attendance['check_in_time']
                : 'Successfully checked in!'
        ]);
    }

    /**
     * Show QR code for an appointment (for authenticated user)
     */
    public function showQRCode($id)
    {
        $appointment = Appointment::where('Appointment_ID', $id)
            ->where('User_ID', Auth::user()->User_ID)
            ->whereIn('Status', ['Approved', 'Completed']) // Allow both statuses
            ->with(['pet', 'service'])
            ->firstOrFail();
        
        $qrCodeUrl = QRCodeService::getQRCodeUrl($appointment);
        
        // If QR code doesn't exist, generate it
        if (!$qrCodeUrl) {
            $path = QRCodeService::generateForAppointment($appointment);
            if ($path) {
                $qrCodeUrl = asset('storage/' . $path);
            }
        }
        
        // Check if already checked in - if so, redirect to verification page
        if ($appointment->Status === 'Completed') {
            $token = QRCodeService::generateVerificationToken($appointment);
            return redirect()->route('appointments.verify', [
                'id' => $appointment->Appointment_ID,
                'token' => $token
            ]);
        }
        
        return view('appointments.qrcode', compact('appointment', 'qrCodeUrl'));
    }

    /**
     * Download QR code for an appointment
     */
    public function downloadQRCode($id)
    {
        $appointment = Appointment::where('Appointment_ID', $id)
            ->where('User_ID', Auth::user()->User_ID)
            ->whereIn('Status', ['Approved', 'Completed']) // Allow both statuses
            ->firstOrFail();
        
        $path = QRCodeService::getQRCodePath($appointment);
        
        if (!$path) {
            // Generate if doesn't exist
            $path = QRCodeService::generateForAppointment($appointment);
        }
        
        if ($path) {
            $fullPath = storage_path('app/public/' . $path);
            $filename = 'appointment_' . $appointment->Appointment_ID . '_qrcode.png';
            return response()->download($fullPath, $filename);
        }
        
        return back()->with('error', 'QR Code not available.');
    }

    /**
     * Check appointment status (for AJAX polling from QR code page)
     * Returns JSON with current status and redirect URL if checked in
     */
    public function checkStatus($id)
    {
        $appointment = Appointment::where('Appointment_ID', $id)
            ->where('User_ID', Auth::user()->User_ID)
            ->first();
        
        if (!$appointment) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Appointment not found'
            ], 404);
        }
        
        $response = [
            'status' => $appointment->Status,
            'appointment_id' => $appointment->Appointment_ID,
        ];
        
        // If checked in (Completed), include redirect URL
        if ($appointment->Status === 'Completed') {
            $token = QRCodeService::generateVerificationToken($appointment);
            $response['redirect_url'] = url('/appointments/verify/' . $appointment->Appointment_ID . '/' . $token);
            $response['message'] = 'Checked in successfully!';
        }
        
        return response()->json($response);
    }

    /**
     * =====================================================
     * USER CERTIFICATE METHODS
     * =====================================================
     */

    /**
     * Show user's certificates
     */
    public function certificatesIndex()
    {
        $userId = Auth::user()->User_ID;
        $certificates = CertificateService::getCertificatesByOwner($userId);
        
        return view('certificates.index', compact('certificates'));
    }

    /**
     * Download certificate (for user)
     */
    public function certificateDownload($id)
    {
        $certificate = CertificateService::getCertificate($id);
        
        if (!$certificate) {
            abort(404, 'Certificate not found.');
        }
        
        // Verify the certificate belongs to the user
        $userId = Auth::user()->User_ID;
        $appointment = Appointment::find($certificate['appointment_id']);
        
        if (!$appointment || $appointment->User_ID != $userId) {
            abort(403, 'Unauthorized.');
        }
        
        // Check if certificate is approved
        if ($certificate['status'] !== 'approved') {
            abort(403, 'Certificate is not yet approved.');
        }
        
        // Generate PDF if not exists
        if (empty($certificate['pdf_path'])) {
            $certificate['pdf_path'] = CertificateService::generatePdf($certificate);
        }
        
        $pdfPath = storage_path('app/public/' . $certificate['pdf_path']);
        
        if (!file_exists($pdfPath)) {
            $certificate['pdf_path'] = CertificateService::generatePdf($certificate);
            $pdfPath = storage_path('app/public/' . $certificate['pdf_path']);
        }
        
        return response()->file($pdfPath, [
            'Content-Type' => 'text/html',
        ]);
    }
}