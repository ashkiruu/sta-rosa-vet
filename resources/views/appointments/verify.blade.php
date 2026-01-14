<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <nav class="bg-gradient-to-r from-red-800 to-red-700 text-white px-6 py-4">
        <div class="container mx-auto flex items-center gap-3">
            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                <span class="text-red-700 font-bold text-lg">🐾</span>
            </div>
            <div>
                <h1 class="font-bold text-lg">City Veterinary Office</h1>
                <p class="text-xs text-red-200">Appointment Verification & Attendance</p>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-8 px-4 max-w-lg">
        @if($valid && $appointment)
            <!-- Valid Appointment -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                
                {{-- Attendance Status Header --}}
                @if(isset($attendance) && $attendance)
                    @if($attendance['already_checked_in'])
                        {{-- Already Checked In --}}
                        <div class="bg-yellow-500 text-white px-6 py-4 text-center">
                            <div class="text-4xl mb-2">⚠️</div>
                            <h2 class="text-xl font-bold">Already Checked In</h2>
                            <p class="text-yellow-100 text-sm">{{ $message }}</p>
                        </div>
                    @else
                        {{-- Successfully Checked In --}}
                        <div class="bg-green-500 text-white px-6 py-4 text-center">
                            <div class="text-5xl mb-2">✓</div>
                            <h2 class="text-xl font-bold">Check-In Successful!</h2>
                            <p class="text-green-100 text-sm">Attendance recorded at {{ $attendance['check_in_time'] }}</p>
                        </div>
                    @endif
                @else
                    {{-- Valid but cannot check in (e.g., Pending status) --}}
                    <div class="bg-blue-500 text-white px-6 py-4 text-center">
                        <div class="text-4xl mb-2">ℹ️</div>
                        <h2 class="text-xl font-bold">Valid Appointment</h2>
                        <p class="text-blue-100 text-sm">{{ $message ?? 'Appointment found' }}</p>
                    </div>
                @endif

                <!-- Appointment Details -->
                <div class="p-6">
                    <div class="text-center mb-6">
                        <p class="text-gray-500 text-sm">Reference Number</p>
                        <p class="text-2xl font-bold text-gray-800">VET-{{ str_pad($appointment->Appointment_ID, 6, '0', STR_PAD_LEFT) }}</p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-3 border-b border-gray-100">
                            <span class="text-gray-500">Status</span>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold
                                @if($appointment->Status == 'Approved') bg-green-100 text-green-800
                                @elseif($appointment->Status == 'Pending') bg-yellow-100 text-yellow-800
                                @elseif($appointment->Status == 'Completed') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $appointment->Status }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center py-3 border-b border-gray-100">
                            <span class="text-gray-500">Pet Name</span>
                            <span class="font-semibold text-gray-800">🐾 {{ $appointment->pet->Pet_Name ?? 'N/A' }}</span>
                        </div>

                        <div class="flex justify-between items-center py-3 border-b border-gray-100">
                            <span class="text-gray-500">Owner</span>
                            <span class="font-semibold text-gray-800">{{ $appointment->user->First_Name ?? '' }} {{ $appointment->user->Last_Name ?? '' }}</span>
                        </div>

                        <div class="flex justify-between items-center py-3 border-b border-gray-100">
                            <span class="text-gray-500">Service</span>
                            <span class="font-semibold text-gray-800">{{ $appointment->service->Service_Name ?? 'N/A' }}</span>
                        </div>

                        <div class="flex justify-between items-center py-3 border-b border-gray-100">
                            <span class="text-gray-500">Scheduled Date</span>
                            <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($appointment->Date)->format('F d, Y') }}</span>
                        </div>

                        <div class="flex justify-between items-center py-3 border-b border-gray-100">
                            <span class="text-gray-500">Scheduled Time</span>
                            <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($appointment->Time)->format('h:i A') }}</span>
                        </div>

                        @if(isset($attendance) && $attendance)
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 bg-green-50 -mx-6 px-6">
                                <span class="text-green-700 font-medium">Check-In Time</span>
                                <span class="font-bold text-green-800">{{ \Carbon\Carbon::parse($attendance['check_in_time'])->format('h:i A') }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between items-center py-3">
                            <span class="text-gray-500">Location</span>
                            <span class="font-semibold text-gray-800">{{ $appointment->Location ?? 'Veterinary Office' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-6 py-4 text-center">
                    <p class="text-xs text-gray-500">
                        Scanned on {{ now()->format('F d, Y h:i A') }}
                    </p>
                </div>
            </div>

            {{-- Action for Staff --}}
            @if(isset($attendance) && $attendance && !$attendance['already_checked_in'])
                <div class="mt-4 bg-green-100 border border-green-300 rounded-lg p-4 text-center">
                    <p class="text-green-800 font-medium">✅ Patient may now proceed to the waiting area</p>
                </div>
            @endif

        @else
            <!-- Invalid Appointment -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-red-500 text-white px-6 py-8 text-center">
                    <div class="text-5xl mb-3">✗</div>
                    <h2 class="text-xl font-bold">Verification Failed</h2>
                    <p class="text-red-100 text-sm mt-2">{{ $message ?? 'This QR code is not valid' }}</p>
                </div>

                <div class="p-6 text-center">
                    <p class="text-gray-600 mb-4">
                        The appointment verification failed. This could mean:
                    </p>
                    <ul class="text-left text-gray-500 text-sm space-y-2 mb-6">
                        <li class="flex items-start gap-2">
                            <span class="text-red-500">•</span>
                            <span>The QR code has been tampered with</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-red-500">•</span>
                            <span>The appointment has been cancelled</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-red-500">•</span>
                            <span>The appointment does not exist</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-red-500">•</span>
                            <span>The QR code is from a different system</span>
                        </li>
                    </ul>
                    <a href="{{ url('/') }}" class="inline-block bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
                        Go to Homepage
                    </a>
                </div>
            </div>
        @endif

        <!-- Back Link -->
        <div class="text-center mt-6">
            <a href="{{ url('/') }}" class="text-gray-500 hover:text-gray-700 text-sm">
                ← Back to Home
            </a>
        </div>
    </div>
</body>
</html>