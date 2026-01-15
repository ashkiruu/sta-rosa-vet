<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment QR Code</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <nav class="bg-gradient-to-r from-red-800 to-red-700 text-white px-6 py-3">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                    <span class="text-red-700 font-bold text-lg">🐾</span>
                </div>
                <div>
                    <h1 class="font-bold text-lg">City Veterinary Office</h1>
                    <p class="text-xs text-red-200">Appointment QR Code</p>
                </div>
            </div>
            <a href="{{ route('appointments.index') }}" class="text-white hover:underline text-sm">
                ← Back to Appointments
            </a>
        </div>
    </nav>

    <div class="container mx-auto mt-8 px-4 max-w-lg pb-12">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-green-500 text-white px-6 py-4 text-center">
                <h2 class="text-xl font-bold">Appointment Confirmed</h2>
                <p class="text-green-100 text-sm">Show this QR code at the clinic</p>
            </div>

            <!-- QR Code Section -->
            <div class="p-6 text-center">
                <div class="mb-4">
                    <p class="text-gray-500 text-sm">Reference Number</p>
                    <p class="text-2xl font-bold text-gray-800">VET-{{ str_pad($appointment->Appointment_ID, 6, '0', STR_PAD_LEFT) }}</p>
                </div>

                @if($qrCodeUrl)
                    <div class="bg-white p-4 rounded-lg inline-block border-2 border-gray-200 mb-4">
                        <img src="{{ $qrCodeUrl }}" alt="Appointment QR Code" class="w-64 h-64 mx-auto">
                    </div>
                @else
                    <div class="bg-gray-100 p-8 rounded-lg mb-4">
                        <p class="text-gray-500">QR Code is being generated...</p>
                        <p class="text-gray-400 text-sm mt-2">Please refresh the page in a moment.</p>
                    </div>
                @endif

                <p class="text-gray-600 text-sm mb-4">
                    Scan this QR code to verify your appointment
                </p>

                <!-- Status Indicator -->
                <div id="statusIndicator" class="mb-4">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                        <span class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></span>
                        <span>Waiting for check-in...</span>
                    </div>
                </div>

                @if($qrCodeUrl)
                    <a href="{{ route('appointments.qrcode.download', $appointment->Appointment_ID) }}" 
                       class="inline-flex items-center gap-2 bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download QR Code
                    </a>
                @endif
            </div>

            <!-- Appointment Details -->
            <div class="border-t border-gray-200 px-6 py-4">
                <h3 class="font-semibold text-gray-700 mb-3">Appointment Details</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Pet</span>
                        <span class="font-medium text-gray-800">🐾 {{ $appointment->pet->Pet_Name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Service</span>
                        <span class="font-medium text-gray-800">{{ $appointment->service->Service_Name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Date</span>
                        <span class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($appointment->Date)->format('F d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Time</span>
                        <span class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($appointment->Time)->format('h:i A') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status</span>
                        <span id="appointmentStatus" class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                            {{ $appointment->Status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="bg-yellow-50 px-6 py-4 border-t border-yellow-100">
                <h4 class="font-semibold text-yellow-800 mb-2">📋 Instructions</h4>
                <ul class="text-sm text-yellow-700 space-y-1">
                    <li>• Please arrive 10 minutes before your scheduled time</li>
                    <li>• Show this QR code to the receptionist upon arrival</li>
                    <li>• Bring your pet's vaccination records if applicable</li>
                    <li>• Keep your pet on a leash or in a carrier</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Auto-check if appointment has been checked in
        const appointmentId = {{ $appointment->Appointment_ID }};
        const checkInterval = 3000; // Check every 3 seconds
        let isChecking = true;

        async function checkAttendanceStatus() {
            if (!isChecking) return;

            try {
                const response = await fetch(`/appointments/${appointmentId}/check-status`);
                const data = await response.json();

                if (data.status === 'Completed') {
                    // Appointment has been checked in!
                    isChecking = false;

                    // Update UI
                    document.getElementById('statusIndicator').innerHTML = `
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                            <span>✓ Checked in! Redirecting...</span>
                        </div>
                    `;

                    document.getElementById('appointmentStatus').className = 'px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800';
                    document.getElementById('appointmentStatus').textContent = 'Completed';

                    // Redirect to verification page after short delay
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 1500);
                }
            } catch (error) {
                console.error('Error checking status:', error);
            }

            // Continue checking
            if (isChecking) {
                setTimeout(checkAttendanceStatus, checkInterval);
            }
        }

        // Start checking when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Start polling after 2 seconds
            setTimeout(checkAttendanceStatus, 2000);
        });

        // Stop checking when user leaves page
        window.addEventListener('beforeunload', function() {
            isChecking = false;
        });
    </script>
</body>
</html>