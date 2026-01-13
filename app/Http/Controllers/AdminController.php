<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        // This combines both methods into one
        $stats = [
            'pending_users' => User::where('Verification_Status_ID', 1)->count(),
            'today_appointments' => Appointment::whereDate('Date', now())->count(),
            // Add one more stat for your thesis: Total Registered Pets
            'total_pets' => \App\Models\Pet::count(), 
        ];
        
        return view('admin.dashboard', compact('stats'));
    }


    public function dashboard()
    {
        // Summary stats for the Admin Dashboard
        $stats = [
            'pending_users' => User::where('Verification_Status_ID', 1)->count(), // Assuming 1 is Pending
            'today_appointments' => Appointment::whereDate('Date', now())->count(),
        ];
        return view('admin.dashboard', compact('stats'));
    }

    public function pendingVerifications(Request $request)
    {
        // 1. Start with all users who are NOT admins
        // We use a join or whereNotExists to make sure we don't list admin accounts here
        $query = User::whereNotExists(function ($query) {
            // Make sure this is 'admin' or 'admins' depending on your phpMyAdmin
            $query->select(DB::raw(1))
                ->from('admins') 
                ->whereColumn('admins.User_ID', 'users.User_ID');
        });

        // 2. Search by Name or Email
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('First_Name', 'like', '%' . $request->search . '%')
                ->orWhere('Last_Name', 'like', '%' . $request->search . '%')
                ->orWhere('Email', 'like', '%' . $request->search . '%');
            });
        }

        // 3. Filter by Status (If the user selects one)
        if ($request->filled('status')) {
            $query->where('Verification_Status_ID', $request->status);
        }

        // 4. Get the results
        $users = $query->latest()->paginate(10);

        return view('admin.verifications', compact('users'));
    }

    public function approveUser($id)
    {
        $user = \App\Models\User::findOrFail($id);
        
        // Update the status based on your Data Dictionary
        // Assuming 2 = 'Verified' in your verification_statuses table
        $user->update([
            'Verification_Status_ID' => 2 
        ]);

        return redirect()->route('admin.verifications')->with('success', 'Resident ' . $user->First_Name . ' has been successfully verified.');
    }

    public function verifications(Request $request)
    {
        $query = User::query();

        // 1. Search by Name or Email
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('First_Name', 'like', '%' . $request->search . '%')
                ->orWhere('Last_Name', 'like', '%' . $request->search . '%')
                ->orWhere('Email', 'like', '%' . $request->search . '%');
            });
        }

        // 2. Filter by Status (Verified/Pending)
        if ($request->has('status') && $request->status != '') {
            $query->where('Verification_Status_ID', $request->status);
        }

        $users = $query->latest()->paginate(10); // Added pagination for efficiency
        return view('admin.verifications', compact('users'));
    }
    
    public function showUser($id)
    {
        // We load the user along with their OCR data and pets
        $user = User::with(['ocrData'])->findOrFail($id);
        
        // Fetch pets for this user as well
        $pets = \App\Models\Pet::where('Owner_ID', $id)->get(); 
        
        return view('admin.user_details', compact('user', 'pets'));
    }

    public function appointments()
    {
        $appointments = Appointment::with(['user', 'pet', 'service'])
            ->orderBy('Date', 'asc')
            ->orderBy('Time', 'asc')
            ->get();

        // Changed from 'admin.appointments.index' to match your new file
        return view('admin.appointment_index', compact('appointments'));
    }

    /**
     * Approve a pending appointment.
     */
    public function approveAppointment($id)
    {
        // 1. Find the appointment
        $appointment = \App\Models\Appointment::with(['pet', 'user'])->findOrFail($id);

        if ($appointment->Status !== 'Pending') {
            return redirect()->back()->with('error', 'This appointment cannot be approved.');
        }
        
        
        // 2. Update the status
        $appointment->Status = 'Approved';
        $appointment->save(); // This updates the 'updated_at' timestamp automatically

        // 3. Return with a success message for the Admin UI
        return redirect()->back()->with('success', 'Appointment for ' . $appointment->User_ID . ' has been approved!');
    }

    /**
     * Reject/Decline a pending appointment.
     * This will delete the appointment and free up the time slot.
     * Stores a notification in session so user can see it was declined.
     */
    /**
 * Reject/Decline a pending appointment.
 * This will delete the appointment and free up the time slot.
 * Stores a notification in session so user can see it was declined.
 */
public function rejectAppointment($id)
{
    $appointment = \App\Models\Appointment::with(['pet', 'user', 'service'])->findOrFail($id);
    
    if ($appointment->Status !== 'Pending') {
        return redirect()->back()->with('error', 'This appointment cannot be rejected.');
    }
    
    // Store info for the success message and notification before deleting
    $petName = $appointment->pet->Pet_Name;
    $ownerUserId = $appointment->User_ID;
    $date = $appointment->Date->format('M d, Y');
    $time = \Carbon\Carbon::parse($appointment->Time)->format('h:i A');
    $serviceName = $appointment->service->Service_Name ?? 'Appointment';
    
    // Store declined notification for the user
    $filePath = storage_path('app/declined_notifications.json');
    $declinedNotifications = [];
    
    if (file_exists($filePath)) {
        $declinedNotifications = json_decode(file_get_contents($filePath), true) ?? [];
    }
    
    $declinedNotifications[] = [
        'user_id' => $ownerUserId,
        'pet_name' => $petName,
        'date' => $date,
        'time' => $time,
        'service' => $serviceName,
        'declined_at' => now()->toDateTimeString(),
    ];
    
    file_put_contents($filePath, json_encode($declinedNotifications));
    
    // DELETE the appointment - this frees up the time slot
    $appointment->delete();

    return redirect()->back()->with('success', 
        "Appointment for {$petName} on {$date} at {$time} has been declined and removed. The time slot is now available.");
}

}

