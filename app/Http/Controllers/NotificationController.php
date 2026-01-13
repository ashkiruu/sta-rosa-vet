<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * View a specific appointment and mark its notification as seen
     */
    public function viewAppointment($id)
    {
        $appointment = Appointment::with(['pet', 'service'])
            ->where('Appointment_ID', $id)
            ->where('User_ID', Auth::user()->User_ID)
            ->firstOrFail();

        // Mark this notification as seen (file-based, persistent)
        $this->markAsSeen($id);

        return redirect()->route('appointments.show', $id);
    }

    /**
     * Mark all notifications as seen (file-based, persistent)
     */
    public function markAllSeen()
    {
        $userId = Auth::user()->User_ID;
        
        // Load seen notifications from file
        $seenFile = storage_path('app/seen_notifications.json');
        $allSeenNotifications = [];
        if (file_exists($seenFile)) {
            $allSeenNotifications = json_decode(file_get_contents($seenFile), true) ?? [];
        }
        
        // Initialize user's array if not exists
        if (!isset($allSeenNotifications[$userId])) {
            $allSeenNotifications[$userId] = [];
        }
        
        // Get all recent appointments and mark them as seen
        $recentAppointments = Appointment::where('User_ID', $userId)
            ->where('created_at', '>=', now()->subDays(7))
            ->get();
        
        foreach ($recentAppointments as $appointment) {
            $notificationKey = 'dashboard_' . $appointment->Appointment_ID;
            if (!in_array($notificationKey, $allSeenNotifications[$userId])) {
                $allSeenNotifications[$userId][] = $notificationKey;
            }
        }
        
        // Save to file
        file_put_contents($seenFile, json_encode($allSeenNotifications));

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Mark a single notification as seen
     */
    private function markAsSeen($appointmentId)
    {
        $userId = Auth::user()->User_ID;
        
        // Load seen notifications from file
        $seenFile = storage_path('app/seen_notifications.json');
        $allSeenNotifications = [];
        if (file_exists($seenFile)) {
            $allSeenNotifications = json_decode(file_get_contents($seenFile), true) ?? [];
        }
        
        // Initialize user's array if not exists
        if (!isset($allSeenNotifications[$userId])) {
            $allSeenNotifications[$userId] = [];
        }
        
        // Add the notification key if not already there
        $notificationKey = 'dashboard_' . $appointmentId;
        if (!in_array($notificationKey, $allSeenNotifications[$userId])) {
            $allSeenNotifications[$userId][] = $notificationKey;
            file_put_contents($seenFile, json_encode($allSeenNotifications));
        }
    }
}