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
     * Original store method - kept for reference, but we now use preview + confirm flow
     */
    public function store(Request $request)
    {
        $request->validate([
            'Pet_ID' => 'required|exists:pets,Pet_ID',
            'Service_ID' => 'required|exists:service_types,Service_ID',
            'Date' => 'required|date|after_or_equal:today',
            'Time' => 'required',
            'Location' => 'required|string|max:255',
            'Special_Notes' => 'nullable|string|max:500',
        ]);

        $pet = Pet::find($request->Pet_ID);
        if ($pet->Owner_ID != Auth::user()->User_ID) {
            return back()->withErrors(['Pet_ID' => 'This pet does not belong to you.']);
        }

        $appointment = new Appointment();
        $appointment->User_ID = Auth::user()->User_ID;
        $appointment->Pet_ID = $request->Pet_ID;
        $appointment->Service_ID = $request->Service_ID;
        $appointment->Date = $request->Date;
        $appointment->Time = $request->Time;
        $appointment->Location = $request->Location;
        $appointment->Status = 'Pending';
        $appointment->Special_Notes = $request->Special_Notes;
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

        $appointment->Status = 'Cancelled';
        $appointment->save();

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment cancelled.');
    }
}