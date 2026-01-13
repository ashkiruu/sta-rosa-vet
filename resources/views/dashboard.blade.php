<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Notification Panel Styles */
        .notification-panel {
            position: fixed;
            top: 0;
            right: -400px;
            width: 380px;
            height: 100vh;
            background: #b91c1c;
            border-left: 3px dashed #fbbf24;
            transition: right 0.3s ease-in-out;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .notification-panel.open {
            right: 0;
        }
        
        .notification-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease-in-out;
            z-index: 999;
        }
        
        .notification-overlay.open {
            opacity: 1;
            visibility: visible;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #fbbf24;
            color: #000;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .notification-item {
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            transition: background 0.2s;
        }
        
        .notification-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .notification-item.unread {
            background: rgba(255, 255, 255, 0.08);
            border-left: 3px solid #fbbf24;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Notification Overlay -->
    <div class="notification-overlay" id="notificationOverlay" onclick="toggleNotifications()"></div>
    
    <!-- Notification Panel -->
    <div class="notification-panel" id="notificationPanel">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-white text-2xl font-bold">Notifications</h2>
                <button onclick="toggleNotifications()" class="text-white hover:text-yellow-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            @php
                $userId = Auth::user()->User_ID;
                
                // Get recent appointments (last 7 days) as notifications
                $recentAppointments = \App\Models\Appointment::where('User_ID', $userId)
                    ->where('created_at', '>=', now()->subDays(7))
                    ->with(['pet', 'service'])
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get();
                
                // Get seen notifications from FILE (persistent across sessions)
                $seenFile = storage_path('app/seen_notifications.json');
                $allSeenNotifications = [];
                if (file_exists($seenFile)) {
                    $allSeenNotifications = json_decode(file_get_contents($seenFile), true) ?? [];
                }
                $seenNotifications = $allSeenNotifications[$userId] ?? [];
            @endphp
            
            @if($recentAppointments->count() > 0)
                <!-- Mark All as Read -->
                <form action="{{ route('notifications.markAllSeen') }}" method="POST" class="mb-4">
                    @csrf
                    <button type="submit" class="text-yellow-400 text-sm hover:underline">Mark all as read</button>
                </form>
                
                <hr class="border-white/30 mb-4">
                
                <!-- Notification Items -->
                @foreach($recentAppointments as $appointment)
                    @php
                        // Check if this appointment's notification key is in seen list
                        $notificationKey = 'dashboard_' . $appointment->Appointment_ID;
                        $isUnread = !in_array($notificationKey, $seenNotifications);
                    @endphp
                    <a href="{{ route('notifications.viewAppointment', $appointment->Appointment_ID) }}" 
                       class="notification-item p-4 rounded-lg mb-3 block {{ $isUnread ? 'unread' : '' }}">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="text-white text-sm">
                                    @if($appointment->Status == 'Pending')
                                        You just reserved an appointment!
                                    @elseif($appointment->Status == 'Approved')
                                        Your appointment has been approved!
                                    @elseif($appointment->Status == 'Confirmed')
                                        Your appointment has been confirmed!
                                    @elseif($appointment->Status == 'Cancelled')
                                        Your appointment was cancelled.
                                    @else
                                        Appointment update
                                    @endif
                                    <span class="font-bold text-yellow-400 ml-1">
                                        Check for details
                                    </span>
                                </p>
                                <p class="text-white/80 text-xs mt-1">
                                    {{ $appointment->pet->Pet_Name ?? 'Pet' }} - {{ $appointment->service->Service_Name ?? 'Service' }}
                                </p>
                                <p class="text-white/60 text-xs mt-1">
                                    📅 {{ \Carbon\Carbon::parse($appointment->Date)->format('M d, Y') }} at {{ \Carbon\Carbon::parse($appointment->Time)->format('g:i A') }}
                                </p>
                                <p class="text-white/40 text-xs mt-2">
                                    Book Appointment · {{ $appointment->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <div class="ml-2">
                                @if($isUnread)
                                    <span class="w-2 h-2 bg-yellow-400 rounded-full inline-block"></span>
                                @elseif($appointment->Status == 'Approved')
                                    <span class="w-2 h-2 bg-green-400 rounded-full inline-block"></span>
                                @elseif($appointment->Status == 'Confirmed')
                                    <span class="w-2 h-2 bg-green-400 rounded-full inline-block"></span>
                                @elseif($appointment->Status == 'Cancelled')
                                    <span class="w-2 h-2 bg-gray-400 rounded-full inline-block"></span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
                
                <!-- View All Link -->
                <div class="text-center mt-4">
                    <a href="{{ route('appointments.index') }}" class="text-yellow-400 hover:underline text-sm">
                        View all appointments →
                    </a>
                </div>
            @else
                <div class="text-center py-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-white/40 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="text-white/60">No recent activity</p>
                    <a href="{{ route('appointments.create') }}" class="text-yellow-400 hover:underline text-sm mt-2 inline-block">
                        Book your first appointment →
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Header -->
    <nav class="bg-red-700 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                    <span class="text-red-700 font-bold text-xl">V</span>
                </div>
                <div>
                    <h1 class="font-bold">City</h1>
                    <h2 class="font-bold">Veterinary</h2>
                    <p class="text-xs">Office</p>
                </div>
            </div>
            <div class="flex items-center gap-6">
                <!-- Notification Bell -->
                @php
                    $userId = Auth::user()->User_ID;
                    
                    // Get seen notifications from FILE (persistent)
                    $seenFile = storage_path('app/seen_notifications.json');
                    $allSeenNotifications = [];
                    if (file_exists($seenFile)) {
                        $allSeenNotifications = json_decode(file_get_contents($seenFile), true) ?? [];
                    }
                    $userSeenNotifications = $allSeenNotifications[$userId] ?? [];
                    
                    // Get recent appointments and count unseen ones
                    $recentAppointmentsForBadge = \App\Models\Appointment::where('User_ID', $userId)
                        ->where('created_at', '>=', now()->subDays(7))
                        ->get();
                    
                    $unseenCount = 0;
                    foreach ($recentAppointmentsForBadge as $appt) {
                        $notificationKey = 'dashboard_' . $appt->Appointment_ID;
                        if (!in_array($notificationKey, $userSeenNotifications)) {
                            $unseenCount++;
                        }
                    }
                @endphp
                <button onclick="toggleNotifications()" class="relative text-white hover:text-yellow-400 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if($unseenCount > 0)
                        <span class="notification-badge">{{ $unseenCount > 9 ? '9+' : $unseenCount }}</span>
                    @endif
                </button>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-white hover:underline">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Welcome Banner -->
    <div class="bg-yellow-400 p-8">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold mb-3">Welcome, {{ Auth::user()->First_Name }}!</h2>
            <p class="text-base mb-2">You can book appointments, check your verification status, and access your pet's certificates — all in one dashboard.</p>
            <p class="font-bold mb-6 text-lg">Batang City Vet Ako!</p>
            
            <div class="inline-block bg-white rounded-lg px-6 py-3 shadow-md">
                <span class="font-semibold text-lg text-gray-800">Account Status: </span>
                
                @if(Auth::user()->Verification_Status_ID == 2)
                    <span class="bg-green-500 text-white px-4 py-2 rounded-full text-sm font-semibold ml-2">✓ Verified Member</span>
                @elseif(Auth::user()->Verification_Status_ID == 1)
                    <span class="bg-blue-500 text-white px-4 py-2 rounded-full text-sm font-semibold ml-2">⏳ Pending Review</span>
                @else
                    <span class="bg-yellow-500 text-white px-4 py-2 rounded-full text-sm font-semibold ml-2">⚠️ Limited Access</span>
                    
                    <a href="{{ route('verify.reverify') }}" class="ml-3 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition shadow-sm">
                        Verify Now
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Actions -->
    <div class="container mx-auto mt-12 px-4 pb-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <!-- Book Appointment -->
            <a href="{{ route('appointments.create') }}" class="bg-white border-2 border-red-600 rounded-lg p-10 text-center hover:bg-red-50 transition shadow-lg hover:shadow-xl">
                <h3 class="text-red-700 font-bold text-2xl">Book Appointment</h3>
            </a>

            <!-- Manage Pets -->
            <a href="{{ route('pets.index') }}" class="bg-white border-2 border-red-600 rounded-lg p-10 text-center hover:bg-red-50 transition shadow-lg hover:shadow-xl">
                <h3 class="text-red-700 font-bold text-2xl">Manage Pets</h3>
            </a>

            <!-- View Certificates -->
            <a href="#" class="bg-white border-2 border-red-600 rounded-lg p-10 text-center hover:bg-red-50 transition shadow-lg hover:shadow-xl">
                <h3 class="text-red-700 font-bold text-2xl">View Certificates</h3>
            </a>
        </div>
    </div>

    <script>
        function toggleNotifications() {
            const panel = document.getElementById('notificationPanel');
            const overlay = document.getElementById('notificationOverlay');
            panel.classList.toggle('open');
            overlay.classList.toggle('open');
        }
    </script>
</body>
</html>