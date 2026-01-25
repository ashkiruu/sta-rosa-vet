<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\UserNotification;
use App\Services\NotificationService;
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

        // Mark this notification as seen (now database-backed)
        $this->markAsSeen($id);

        return redirect()->route('appointments.show', $id);
    }

    /**
     * Mark all notifications as seen (now database-backed)
     */
    public function markAllSeen(Request $request)
{
    $userId = Auth::user()->User_ID;
    
    // Use the NotificationService to mark all as seen
    NotificationService::markAllSeen($userId);
    
    // ALWAYS return JSON for AJAX requests
    if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.'
        ]);
    }
    
    // For regular form submissions, redirect
    return redirect()->back()->with('success', 'All notifications marked as read.');
}

    /**
     * Mark a single notification as seen
     */
    private function markAsSeen($appointmentId)
    {
        $userId = Auth::user()->User_ID;
        
        // Mark by reference (appointment)
        UserNotification::markSeenByReference($appointmentId, 'appointment', $userId);
    }

    /**
     * API endpoint to mark a notification as seen by key
     */
    public function markSeenByKey(Request $request)
    {
        $request->validate(['key' => 'required|string']);
        
        $userId = Auth::user()->User_ID;
        NotificationService::markSeenByKey($userId, $request->key);
        
        return response()->json(['success' => true]);
    }

    /**
     * API endpoint to get notification count
     */
    public function getUnseenCount()
    {
        $userId = Auth::user()->User_ID;
        $count = NotificationService::getUnseenCount($userId);
        
        return response()->json(['count' => $count]);
    }

    /**
     * API endpoint to get all notifications
     */
    public function getNotifications()
    {
        $userId = Auth::user()->User_ID;
        $notifications = NotificationService::getNotificationsForUser($userId);
        
        return response()->json(['notifications' => $notifications]);
    }
}
