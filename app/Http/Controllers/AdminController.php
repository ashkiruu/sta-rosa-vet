<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
        $stats = [
            'pending_users' => User::where('Verification_Status_ID', 1)->count(),
            'today_appointments' => Appointment::whereDate('Date', now())->count(),
        ];
        return view('admin.dashboard', compact('stats'));
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
        $user = \App\Models\User::findOrFail($id);
        
        $user->update([
            'Verification_Status_ID' => 2 
        ]);

        return redirect()->route('admin.verifications')->with('success', 'Resident ' . $user->First_Name . ' has been successfully verified.');
    }

    public function rejectUser($id)
    {
        $user = \App\Models\User::findOrFail($id);
        
        $user->update([
            'Verification_Status_ID' => 3 
        ]);

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
            ->whereIn('Status', ['Pending', 'Approved']) // Only show active appointments
            ->orderBy('Date', 'asc')
            ->orderBy('Time', 'asc')
            ->get()
            ->map(function ($appointment) {
                // Format date as Y-m-d string for consistent JavaScript handling
                $appointment->Date = \Carbon\Carbon::parse($appointment->Date)->format('Y-m-d');
                // Format time as H:i string (remove seconds)
                $appointment->Time = \Carbon\Carbon::parse($appointment->Time)->format('H:i');
                return $appointment;
            });

        // Load clinic schedule
        $schedule = $this->getClinicSchedule();

        return view('admin.appointment_index', compact('appointments', 'schedule'));
    }

    /**
     * Get clinic schedule configuration
     * Auto-creates the file if it doesn't exist
     */
    private function getClinicSchedule()
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
            return $defaultSchedule;
        }
        
        return json_decode(file_get_contents($schedulePath), true);
    }

    /**
     * Toggle a specific date open/closed
     */
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
            // Add to opened_dates if not already there
            if (!in_array($date, $schedule['opened_dates'])) {
                $schedule['opened_dates'][] = $date;
            }
            // Remove from closed_dates if present
            $schedule['closed_dates'] = array_values(array_diff($schedule['closed_dates'], [$date]));
        } else {
            // Add to closed_dates if not already there
            if (!in_array($date, $schedule['closed_dates'])) {
                $schedule['closed_dates'][] = $date;
            }
            // Remove from opened_dates if present
            $schedule['opened_dates'] = array_values(array_diff($schedule['opened_dates'], [$date]));
        }

        file_put_contents($schedulePath, json_encode($schedule, JSON_PRETTY_PRINT));

        $dayName = \Carbon\Carbon::parse($date)->format('l, M d, Y');
        $message = $action === 'open' 
            ? "Clinic is now OPEN on {$dayName}" 
            : "Clinic is now CLOSED on {$dayName}";

        return redirect()->back()->with('success', $message);
    }

    /**
     * Approve a pending appointment.
     * This will update the status and trigger a notification for the user.
     */
    public function approveAppointment($id)
    {
        $appointment = \App\Models\Appointment::with(['pet', 'user'])->findOrFail($id);
        
        if ($appointment->Status !== 'Pending') {
            return redirect()->back()->with('error', 'This appointment cannot be approved.');
        }
        
        // Update the status - this triggers the updated_at timestamp
        // which is used by the notification system
        $appointment->Status = 'Approved';
        $appointment->save();

        return redirect()->back()->with('success', 
            'Appointment for ' . $appointment->pet->Pet_Name . ' has been approved! The owner will be notified.');
    }

    /**
     * Reject/Decline a pending appointment.
     * This will delete the appointment and free up the time slot.
     * Stores a notification in file so user can see it was declined.
     */
    public function rejectAppointment($id)
    {
        $appointment = \App\Models\Appointment::with(['pet', 'user'])->findOrFail($id);
        
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
        $notificationPath = storage_path('app/declined_notifications.json');
        
        // Auto-create file if it doesn't exist
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
        
        // Delete the appointment - this frees up the time slot
        $appointment->delete();

        return redirect()->back()->with('success', 
            "Appointment for {$petName} on {$date} at {$time} has been declined and removed. The time slot is now available.");
    }

}