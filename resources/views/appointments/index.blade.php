<x-dashboardheader-layout>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Appointments</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100">
        <div class="container mx-auto mt-8 px-4">
            
            {{-- Notifications Section --}}
            @if(isset($notifications) && count($notifications) > 0)
                <div class="mb-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-700">
                            🔔 New Updates ({{ count($notifications) }})
                        </h3>
                        <form action="{{ route('appointments.notifications.markAllSeen') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-sm text-gray-500 hover:text-red-600">
                                Mark all as read
                            </button>
                        </form>
                    </div>
                    
                    @foreach($notifications as $notification)
                        <div class="rounded-lg p-4 flex items-start gap-4 shadow-sm border
                            {{ $notification['type'] === 'success' ? 'bg-green-50 border-green-200' : '' }}
                            {{ $notification['type'] === 'error' ? 'bg-red-50 border-red-200' : '' }}
                            {{ $notification['type'] === 'info' ? 'bg-blue-50 border-blue-200' : '' }}
                        " id="notification-{{ $notification['id'] }}">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-xl
                                {{ $notification['type'] === 'success' ? 'bg-green-100' : '' }}
                                {{ $notification['type'] === 'error' ? 'bg-red-100' : '' }}
                                {{ $notification['type'] === 'info' ? 'bg-blue-100' : '' }}
                            ">
                                @if($notification['type'] === 'success')
                                    ✅
                                @elseif($notification['type'] === 'error')
                                    ❌
                                @else
                                    ℹ️
                                @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold 
                                    {{ $notification['type'] === 'success' ? 'text-green-800' : '' }}
                                    {{ $notification['type'] === 'error' ? 'text-red-800' : '' }}
                                    {{ $notification['type'] === 'info' ? 'text-blue-800' : '' }}
                                ">{{ $notification['title'] }}</h4>
                                <p class="text-sm 
                                    {{ $notification['type'] === 'success' ? 'text-green-700' : '' }}
                                    {{ $notification['type'] === 'error' ? 'text-red-700' : '' }}
                                    {{ $notification['type'] === 'info' ? 'text-blue-700' : '' }}
                                ">{{ $notification['message'] }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $notification['time'] }}</p>
                                
                                {{-- QR Code Button for Approved Appointments --}}
                                @if(isset($notification['qr_link']))
                                    <a href="{{ $notification['qr_link'] }}" 
                                    class="inline-flex items-center gap-1 mt-2 text-sm bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                        View QR Code
                                    </a>
                                @endif
                            </div>
                            <button type="button" 
                                    onclick="dismissNotification('{{ $notification['key'] }}', '{{ $notification['id'] }}')"
                                    class="text-gray-400 hover:text-gray-600 p-1">
                                ✕
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">My Appointments</h2>
                <a href="{{ route('appointments.create') }}" class="bg-red-700 text-white px-6 py-2 rounded-lg hover:bg-red-800">
                    + Book New Appointment
                </a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if($appointments->isEmpty())
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <div class="text-6xl mb-4">📅</div>
                    <h3 class="text-xl font-semibold mb-2">No Appointments Yet</h3>
                    <p class="text-gray-600 mb-6">Book your first appointment for your pet!</p>
                    <a href="{{ route('appointments.create') }}" class="bg-red-700 text-white px-8 py-3 rounded-lg hover:bg-red-800 inline-block">
                        Book Appointment
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4">
                    @foreach($appointments as $appointment)
                        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-3">
                                        <h3 class="text-xl font-bold">{{ $appointment->pet->Pet_Name }}</h3>
                                        <span class="px-3 py-1 rounded-full text-sm font-semibold
                                            {{ $appointment->Status == 'Pending' ? 'bg-yellow-200 text-yellow-800' : '' }}
                                            {{ $appointment->Status == 'Approved' ? 'bg-green-200 text-green-800' : '' }}
                                            {{ $appointment->Status == 'Confirmed' ? 'bg-green-200 text-green-800' : '' }}
                                            {{ $appointment->Status == 'Cancelled' ? 'bg-red-200 text-red-800' : '' }}
                                            {{ $appointment->Status == 'Completed' ? 'bg-blue-200 text-blue-800' : '' }}">
                                            {{ $appointment->Status }}
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                                        <div>
                                            <p class="font-semibold text-gray-700">Service:</p>
                                            <p>{{ $appointment->service->Service_Name }}</p>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-700">Date & Time:</p>
                                            <p>{{ $appointment->Date->format('M d, Y') }} at {{ date('g:i A', strtotime($appointment->Time)) }}</p>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-700">Location:</p>
                                            <p>{{ $appointment->Location }}</p>
                                        </div>
                                        @if($appointment->Special_Notes)
                                            <div class="col-span-2">
                                                <p class="font-semibold text-gray-700">Notes:</p>
                                                <p>{{ $appointment->Special_Notes }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Status Messages --}}
                                    @if($appointment->Status == 'Approved')
                                        <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                                            <p class="text-sm text-green-700">
                                                ✅ Your appointment has been approved! Please arrive 10 minutes early.
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex flex-col gap-2">
                                    {{-- QR Code Button for Approved Appointments --}}
                                    @if($appointment->Status == 'Approved')
                                        <a href="{{ route('appointments.qrcode', $appointment->Appointment_ID) }}" 
                                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm text-center flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                            </svg>
                                            View QR
                                        </a>
                                    @endif
                                    
                                    @if($appointment->Status == 'Pending')
                                        <form method="POST" action="{{ route('appointments.cancel', $appointment->Appointment_ID) }}" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                            @csrf
                                            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 text-sm w-full">
                                                Cancel
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <script>
        function dismissNotification(key, id) {
            // Hide the notification visually
            document.getElementById('notification-' + id).style.display = 'none';
            
            // Mark as seen via AJAX
            fetch('{{ route("appointments.notifications.markSeen") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ key: key })
            });
        }
        </script>
    </body>
    </html>
</x-dashboardheader-layout>