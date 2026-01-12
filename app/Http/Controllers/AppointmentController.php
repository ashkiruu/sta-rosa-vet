<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::where('User_ID', Auth::user()->User_ID)
            ->with(['pet', 'service'])
            ->orderBy('Date', 'desc')
            ->get();

        return view('appointments.index', compact('appointments'));
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
        ->whereIn('Status', ['Pending', 'Confirmed'])
        ->pluck('Time')
        ->map(function ($time) {
            // Format time to HH:MM (without seconds) to match dropdown values
            // Handle both "08:00:00" and "08:00" formats
            if (strlen($time) > 5) {
                return substr($time, 0, 5); // Convert "08:00:00" to "08:00"
            }
            return $time;
        })
        ->unique()
        ->values()
        ->toArray();

    // Debug: Log what's being returned (remove this after testing)
    \Log::info('Taken times for ' . $date . ': ' . json_encode($takenTimes));

    return response()->json([
        'takenTimes' => $takenTimes
    ]);
}

    /**
     * Check if a time slot is already taken
     */
    private function isTimeSlotTaken($date, $time)
    {
        // Normalize the time format to H:i
        $normalizedTime = \Carbon\Carbon::parse($time)->format('H:i');
        
        return Appointment::where('Date', $date)
            ->whereIn('Status', ['Pending', 'Confirmed'])
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

        // Check if time slot is already taken
        if ($this->isTimeSlotTaken($request->Date, $request->Time)) {
            return back()->withErrors(['Time' => 'This time slot is already taken. Please select a different time.'])
                         ->withInput();
        }

        $service = ServiceType::find($request->Service_ID);

        // Create appointment data for display
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
            ->with('success', 'Appointment booked successfully!');
    }

    /**
     * Original store method - with time slot validation
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

        // Check if time slot is already taken
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

    // Only allow cancellation of Pending appointments
    if ($appointment->Status != 'Pending') {
        return back()->withErrors(['error' => 'Cannot cancel this appointment.']);
    }

    // Delete the appointment completely to free up the time slot
    $appointment->delete();

    return redirect()->route('appointments.index')
        ->with('success', 'Appointment cancelled.');
}

// Get fully booked dates for a given month
 
public function getFullyBookedDates(Request $request)
{
    $request->validate([
        'year' => 'required|integer',
        'month' => 'required|integer|min:1|max:12',
    ]);

    $year = $request->year;
    $month = $request->month;
    
    // Total available time slots per day
    $totalSlots = 17; // 08:00, 08:10, 08:20, 08:30, 08:45, 09:00, 09:10, 09:20, 09:30, 09:40, 09:50, 10:00, 10:10, 10:20, 10:30, 10:40, 10:45
    
    // Get the start and end dates of the month
    $startDate = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
    $endDate = date('Y-m-t', strtotime($startDate));
    
    // Get count of appointments per date for this month
    $appointmentCounts = Appointment::selectRaw('Date, COUNT(*) as count')
        ->whereBetween('Date', [$startDate, $endDate])
        ->whereIn('Status', ['Pending', 'Confirmed'])
        ->groupBy('Date')
        ->get();
    
    // Find dates where all slots are taken
    $fullyBookedDates = [];
    foreach ($appointmentCounts as $record) {
        if ($record->count >= $totalSlots) {
            // Format date to match JavaScript format (YYYY-MM-DD)
            $fullyBookedDates[] = \Carbon\Carbon::parse($record->Date)->format('Y-m-d');
        }
    }
    
    return response()->json([
        'fullyBookedDates' => $fullyBookedDates
    ]);
}

}