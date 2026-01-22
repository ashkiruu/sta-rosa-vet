<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'City Vet')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Notification Panel Styles */
            .notification-panel {
                position: fixed;
                top: 0;
                right: -400px;
                width: 380px;
                height: 100vh;
                background: white;
                border-left: 4px dashed #b91c1c;
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
                border-bottom: 1px solid rgba(217, 55, 55, 0.82);
                transition: background 0.2s;
            }
            
            .notification-item:hover {
                background: rgba(220, 93, 93, 0.76);
            }
            
            .notification-item.unread {
                background: rgba(255, 255, 255, 0.08);
                border-left: 3px solid #fbbf24;
            }
            /* Sidebar Panel Styles */
            .sidebar-panel {
                position: fixed;
                top: 0;
                left: -400px; /* Hidden off-screen to the left */
                width: 100%;
                max-width: 320px;
                height: 100vh;
                background: white;
                border-right: 4px dashed #b91c1c; /* Red accent */
                transition: left 0.3s ease-in-out;
                z-index: 1001;
                overflow-y: auto;
            }

            .sidebar-panel.open {
                left: 0;
            }

            .sidebar-overlay {
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

            .sidebar-overlay.open {
                opacity: 1;
                visibility: visible;
            }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">
    <!-- Notification Panel -->
    <div class="notification-overlay" id="notificationOverlay" onclick="toggleNotifications()"></div>

    <div class="notification-panel" id="notificationPanel">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-red-700 text-2xl font-bold">Notifications</h2>
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
                    <button type="submit" class="font-semibold text-red-600 text-sm hover:underline">Mark all as read</button>
                </form>
                
                <hr class="border-black/30 mb-4">
                
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
                                <p class="text-black text-sm">
                                    @if($appointment->Status == 'Pending')
                                        You just reserved an appointment!
                                    @elseif($appointment->Status == 'Approved')
                                        Your appointment has been approved!
                                    @elseif($appointment->Status == 'Confirmed')
                                        Your appointment has been confirmed!
                                    @elseif($appointment->Status == 'Cancelled')
                                        Your appointment was cancelled.
                                    @elseif($appointment->Status == 'Completed')
                                        Your appointment is completed!
                                    @else
                                        Appointment update
                                    @endif
                                    <span class="font-bold text-black-400 ml-1">
                                        Check for details
                                    </span>
                                </p>
                                <p class="text-black/80 text-xs mt-1">
                                    {{ $appointment->pet->Pet_Name ?? 'Pet' }} - {{ $appointment->service->Service_Name ?? 'Service' }}
                                </p>
                                <p class="text-black/60 text-xs mt-1">
                                    📅 {{ \Carbon\Carbon::parse($appointment->Date)->format('M d, Y') }} at {{ \Carbon\Carbon::parse($appointment->Time)->format('g:i A') }}
                                </p>
                                <p class="text-black/40 text-xs mt-2">
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
                                @elseif($appointment->Status == 'Completed')
                                    <span class="w-2 h-2 bg-blue-400 rounded-full inline-block"></span>
                                @elseif($appointment->Status == 'Cancelled')
                                    <span class="w-2 h-2 bg-gray-400 rounded-full inline-block"></span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
                
                <!-- View All Link -->
                <div class="text-center mt-4">
                    <a href="{{ route('appointments.index') }}" class="font-semibold text-red-600 hover:underline text-sm">
                        View all appointments →
                    </a>
                </div>
            @else
                <div class="text-center py-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-black/40 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="text-black/60">No recent activity</p>
                    <a href="{{ route('appointments.create') }}" class="text-red-600 hover:underline text-sm mt-2 inline-block">
                        Book your first appointment →
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar Panel -->
    <div class="sidebar-panel shadow-2xl" id="sidebarPanel">
        <div class="p-6">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-red-700 text-xl font-bold">Quick Actions</h2>
                <button onclick="toggleSidebar()" class="text-gray-500 hover:text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="space-y-4">
                <a href="{{ route('appointments.create') }}" class="flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:bg-red-50 hover:border-red-200 transition group">
                    <span class="text-2xl">📅</span>
                    <div>
                        <p class="font-bold text-gray-800 group-hover:text-red-700">Book Appointment</p>
                        <p class="text-xs text-gray-500">Schedule a pet visit</p>
                    </div>
                </a>

                <a href="{{ route('pets.index') }}" class="flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:bg-red-50 hover:border-red-200 transition group">
                    <span class="text-2xl">🐾</span>
                    <div>
                        <p class="font-bold text-gray-800 group-hover:text-red-700">Manage Pets</p>
                        <p class="text-xs text-gray-500">Register your pets</p>
                    </div>
                </a>

                <a href="{{ route('certificates.index') }}" class="flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:bg-red-50 hover:border-red-200 transition group relative">
                    <span class="text-2xl">📜</span>
                    <div>
                        <p class="font-bold text-gray-800 group-hover:text-red-700">View Certificates</p>
                        <p class="text-xs text-gray-500">Vaccination records</p>
                    </div>
                    @if(($certCount ?? 0) > 0)
                        <span class="absolute top-2 right-2 bg-green-500 w-3 h-3 rounded-full border-2 border-black"></span>
                    @endif
                </a>

                <hr class="border-gray-100 my-4">

                <a href="{{ route('appointments.index') }}" class="flex items-center gap-4 p-3 text-red-600 hover:bg-red-50 rounded-lg font-semibold text-sm transition">
                    <span>View All Appointments →</span>
                </a>
            </nav>
        </div>
    </div>
        
    <nav class="bg-red-700 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="text-white hover:text-yellow-400 transition focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 hover:opacity-90 transition">
                    <img src="{{ asset('images/Logo.png') }}" 
                        alt="Logo" 
                        class="h-8 md:h-12 w-auto object-contain">
                </a>
            </div>

            <div class="flex items-center gap-4 md:gap-6">
                @php
                    $userId = Auth::user()->User_ID;
                    $seenFile = storage_path('app/seen_notifications.json');
                    $allSeenNotifications = file_exists($seenFile) ? (json_decode(file_get_contents($seenFile), true) ?? []) : [];
                    $userSeenNotifications = $allSeenNotifications[$userId] ?? [];
                    
                    $recentAppointmentsForBadge = \App\Models\Appointment::where('User_ID', $userId)
                        ->where('created_at', '>=', now()->subDays(7))
                        ->get();
                    
                    $unseenCount = 0;
                    foreach ($recentAppointmentsForBadge as $appt) {
                        if (!in_array('dashboard_' . $appt->Appointment_ID, $userSeenNotifications)) {
                            $unseenCount++;
                        }
                    }
                @endphp

                <button onclick="toggleNotifications()" class="relative text-white hover:text-yellow-400 transition focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if($unseenCount > 0)
                        <span class="notification-badge">{{ $unseenCount > 9 ? '9+' : $unseenCount }}</span>
                    @endif
                </button>
                
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-white hover:text-yellow-400 font-medium transition text-sm">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <main>
        <div class="min-h-[calc(100vh-80px)] w-full flex flex-col items-center bg-fixed bg-cover bg-center" 
            style="background-image: url('{{ asset('images/PawsBackground.png') }}');">

            {{-- Content Area: This is where your Appointment/Pet forms will appear --}}
            <div class="w-full flex-1 flex flex-col items-center">
                {{ $slot }}
            </div>
        </div>
    </main>

    <script>
        function toggleNotifications() {
            const panel = document.getElementById('notificationPanel');
            const overlay = document.getElementById('notificationOverlay');
            panel.classList.toggle('open');
            overlay.classList.toggle('open');
        }
        function toggleSidebar() {
            const panel = document.getElementById('sidebarPanel');
            const overlay = document.getElementById('sidebarOverlay');
            panel.classList.toggle('open');
            overlay.classList.toggle('open');
        }

    </script>
</body>
</html>