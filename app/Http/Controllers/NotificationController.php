<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mark a notification as seen and redirect to appointment details
     */
    public function viewAppointment($id)
    {
        // Get current seen notifications from session
        $seenNotifications = session('seen_notifications', []);
        
        // Add this appointment ID to seen list if not already there
        if (!in_array($id, $seenNotifications)) {
            $seenNotifications[] = $id;
            session(['seen_notifications' => $seenNotifications]);
        }
        
        // Redirect to appointment details
        return redirect()->route('appointments.show', $id);
    }

    /**
     * Mark all notifications as seen
     */
    public function markAllSeen()
    {
        // Get all recent appointment IDs for this user
        $appointmentIds = \App\Models\Appointment::where('User_ID', Auth::user()->User_ID)
            ->where('created_at', '>=', now()->subDays(7))
            ->pluck('Appointment_ID')
            ->toArray();
        
        // Store all as seen in session
        session(['seen_notifications' => $appointmentIds]);
        
        return back()->with('success', 'All notifications marked as read.');
    }
}