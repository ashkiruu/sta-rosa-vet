<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Details</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <nav class="bg-red-700 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                    <span class="text-red-700 font-bold">🐾</span>
                </div>
                <div>
                    <h1 class="font-bold">City Veterinary Office</h1>
                </div>
            </div>
            <a href="{{ route('appointments.index') }}" class="text-white hover:underline">← Back to Appointments</a>
        </div>
    </nav>

    <div class="container mx-auto mt-8 px-4 max-w-3xl">
        <!-- Appointment Details Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-red-700 text-white p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold">Appointment Details</h2>
                        <p class="text-red-200 mt-1">Appointment #{{ $appointment->Appointment_ID }}</p>
                    </div>
                    <span class="px-4 py-2 rounded-full text-sm font-semibold
                        {{ $appointment->Status == 'Pending' ? 'bg-yellow-400 text-yellow-900' : '' }}
                        {{ $appointment->Status == 'Approved' ? 'bg-green-400 text-green-900' : '' }}
                        {{ $appointment->Status == 'Confirmed' ? 'bg-green-400 text-green-900' : '' }}
                        {{ $appointment->Status == 'Cancelled' ? 'bg-red-400 text-red-900' : '' }}
                        {{ $appointment->Status == 'Completed' ? 'bg-blue-400 text-blue-900' : '' }}">
                        {{ $appointment->Status }}
                    </span>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <!-- Pet Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="text-2xl">🐕</span> Pet Information
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Pet Name</p>
                                <p class="font-semibold text-gray-800">{{ $appointment->pet->Pet_Name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Species</p>
                                <p class="font-semibold text-gray-800">{{ $appointment->pet->species->Species_Name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Breed</p>
                                <p class="font-semibold text-gray-800">{{ $appointment->pet->Breed ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Age</p>
                                <p class="font-semibold text-gray-800">{{ $appointment->pet->Age ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="text-2xl">💉</span> Service Information
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Service Type</p>
                                <p class="font-semibold text-gray-800">{{ $appointment->service->Service_Name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Description</p>
                                <p class="font-semibold text-gray-800">{{ $appointment->service->Description ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Schedule Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="text-2xl">📅</span> Schedule
                    </h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Date</p>
                                <p class="font-semibold text-gray-800">{{ $appointment->Date->format('F d, Y') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Time</p>
                                <p class="font-semibold text-gray-800">{{ date('g:i A', strtotime($appointment->Time)) }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-sm text-gray-500">Location</p>
                                <p class="font-semibold text-gray-800">{{ $appointment->Location }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Special Notes -->
                @if($appointment->Special_Notes)
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="text-2xl">📝</span> Special Notes
                    </h3>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-gray-700">{{ $appointment->Special_Notes }}</p>
                    </div>
                </div>
                @endif

                <!-- Status Messages -->
                @if($appointment->Status == 'Pending')
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">⏳</span>
                            <div>
                                <p class="font-semibold text-yellow-800">Awaiting Approval</p>
                                <p class="text-sm text-yellow-700">Your appointment is being reviewed by the veterinary staff. You will be notified once it's approved.</p>
                            </div>
                        </div>
                    </div>
                @elseif($appointment->Status == 'Approved')
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">✅</span>
                            <div>
                                <p class="font-semibold text-green-800">Appointment Approved!</p>
                                <p class="text-sm text-green-700">Please arrive 10 minutes before your scheduled time. Don't forget to bring your QR code!</p>
                            </div>
                        </div>
                    </div>
                @elseif($appointment->Status == 'Completed')
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🎉</span>
                            <div>
                                <p class="font-semibold text-blue-800">Appointment Completed</p>
                                <p class="text-sm text-blue-700">Thank you for visiting the City Veterinary Office. Your certificate will be available soon.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-3 mt-6 pt-6 border-t">
                    @if($appointment->Status == 'Approved')
                        <a href="{{ route('appointments.qrcode', $appointment->Appointment_ID) }}" 
                           class="flex items-center gap-2 bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            View QR Code
                        </a>
                    @endif

                    @if($appointment->Status == 'Pending')
                        <form method="POST" action="{{ route('appointments.cancel', $appointment->Appointment_ID) }}" 
                              onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 bg-red-500 text-white px-6 py-3 rounded-lg hover:bg-red-600 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Cancel Appointment
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('appointments.index') }}" 
                       class="flex items-center gap-2 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Appointments
                    </a>
                </div>
            </div>
        </div>

        <!-- Timestamps -->
        <div class="mt-4 text-center text-sm text-gray-500">
            <p>Created: {{ $appointment->created_at->format('M d, Y g:i A') }}</p>
            @if($appointment->updated_at != $appointment->created_at)
                <p>Last Updated: {{ $appointment->updated_at->format('M d, Y g:i A') }}</p>
            @endif
        </div>
    </div>

    <footer class="mt-12 pb-8 text-center text-gray-500 text-sm">
        <p>City Veterinary Office - Sta. Rosa City</p>
    </footer>
</body>
</html>