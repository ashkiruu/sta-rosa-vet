<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .notification-panel {
            position: fixed;
            top: 0;
            right: -100%;
            width: 100%;
            max-width: 400px;
            height: 100vh;
            background: white;
            box-shadow: -4px 0 15px rgba(0, 0, 0, 0.1);
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
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
            z-index: 999;
        }

        .notification-overlay.open {
            opacity: 1;
            visibility: visible;
        }

        .notification-item {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
        }

        .notification-item:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
        }

        .notification-item.unread {
            background: #fef3c7;
            border-color: #fbbf24;
        }

        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #fbbf24;
            color: #1f2937;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 9999px;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #991b1b;
        }

        .sidebar-panel {
            position: fixed;
            top: 0;
            left: -100%;
            width: 100%;
            max-width: 320px;
            height: 100vh;
            background: white;
            transition: left 0.3s ease-in-out;
            z-index: 1000;
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
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
            z-index: 999;
        }

        .sidebar-overlay.open {
            opacity: 1;
            visibility: visible;
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <div class="notification-overlay" id="notificationOverlay" onclick="toggleNotifications()"></div>

    <!-- Notification Panel -->
    <div class="notification-panel" id="notificationPanel">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-red-700 text-xl font-bold">Recent Activity</h2>
                <button onclick="toggleNotifications()" class="text-gray-500 hover:text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            @php
                $userId = Auth::user()->User_ID;
                
                // Use NotificationService instead of querying directly
                $notifications = \App\Services\NotificationService::getNotificationsForUser($userId);
                $unseenCount = \App\Services\NotificationService::getUnseenCount($userId);
            @endphp

            <div id="notificationContent">
                @if(count($notifications) > 0)
                    <!-- Mark All as Read -->
                    <div class="mb-4">
                        <button onclick="markAllNotificationsAsRead()" type="button" class="font-semibold text-red-600 text-sm hover:underline">
                            Mark all as read
                        </button>
                    </div>
                    
                    <hr class="border-black/30 mb-4">
                    
                    <!-- Notification Items -->
                    <div id="notificationList">
                        @foreach($notifications as $notification)
                            <a href="{{ $notification['qr_link'] ?? route('appointments.index') }}" 
                               class="notification-item p-4 rounded-lg mb-3 block {{ !$notification['seen'] ? 'unread' : '' }}"
                               data-notification-key="{{ $notification['key'] }}"
                               onclick="markNotificationSeen(event, '{{ $notification['key'] }}')">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <p class="text-black text-sm font-semibold">
                                            {{ $notification['title'] }}
                                        </p>
                                        <p class="text-black/80 text-xs mt-1">
                                            {{ $notification['message'] }}
                                        </p>
                                        <p class="text-black/40 text-xs mt-2">
                                            {{ $notification['time'] }}
                                        </p>
                                    </div>
                                    <div class="ml-2">
                                        @if(!$notification['seen'])
                                            <span class="w-2 h-2 bg-yellow-400 rounded-full inline-block"></span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    
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
                <button onclick="toggleNotifications()" class="relative text-white hover:text-yellow-400 transition focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="notification-badge" id="headerNotificationBadge" style="display: {{ $unseenCount > 0 ? 'flex' : 'none' }}">
                        {{ $unseenCount > 9 ? '9+' : $unseenCount }}
                    </span>
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

            {{-- Content Area --}}
            <div class="w-full flex-1 flex flex-col items-center">
                {{ $slot }}
                <div class="container mx-auto px-4 flex flex-col items-center justify-center">
                    <div class="w-full max-w-xs md:max-w-md h-px bg-gradient-to-r from-transparent via-gray-200 to-transparent mb-6"></div>
                    
                    <p class="text-xs md:text-sm text-gray-500 font-medium tracking-wide">
                        &copy; {{ date('Y') }} City Veterinary Office. All rights reserved.
                    </p>
                </div>
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

    function markNotificationSeen(event, key) {
        // Mark as seen via AJAX (don't prevent navigation)
        fetch('{{ route("appointments.notifications.markSeen") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ key: key })
        }).catch(err => console.error('Failed to mark as seen:', err));
    }

    function markAllNotificationsAsRead() {
        fetch('{{ route("notifications.markAllSeen") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Simply reload the page to show updated state
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Reload anyway as fallback
            window.location.reload();
        });
    }
    </script>
</body>
</html>