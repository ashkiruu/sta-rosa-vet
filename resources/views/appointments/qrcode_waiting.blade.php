<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiting for Check-in</title>
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
                    <p class="text-xs text-red-200">Appointment Check-in</p>
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
            <div class="bg-blue-500 text-white px-6 py-4 text-center">
                <h2 class="text-xl font-bold">Appointment Approved</h2>
                <p class="text-blue-100 text-sm">Please check in at the reception desk</p>
            </div>

            <!-- Main Content -->
            <div class="p-6 text-center">
                <div class="mb-4">
                    <p class="text-gray-500 text-sm">Reference Number</p>
                    <p class="text-2xl font-bold text-gray-800">VET-{{ str_pad($appointment->Appointment_ID, 6, '0', STR_PAD_LEFT) }}</p>
                </div>

                <!-- Waiting Animation -->
                <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-8 mb-6">
                    <div class="text-6xl mb-4">🏥</div>
                    <h3 class="text-lg font-bold text-blue-800 mb-2">Proceed to Reception</h3>
                    <p class="text-blue-700 text-sm mb-4">
                        Your appointment has been approved! Please go to the reception desk when you arrive at the clinic.
                    </p>
                    <p class="text-blue-600 text-xs">
                        The receptionist will release your QR code for check-in.
                    </p>
                </div>

                <!-- Status Indicator -->
                <div id="statusIndicator" class="mb-6">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                        <span class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></span>
                        <span>Waiting for QR code release...</span>
                    </div>
                </div>

                <p class="text-gray-500 text-sm">
                    This page will automatically update when your QR code is ready.
                </p>
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
                <h4 class="font-semibold text-yellow-800 mb-2">📋 What to do</h4>
                <ul class="text-sm text-yellow-700 space-y-1">
                    <li>• Go to the reception desk when you arrive</li>
                    <li>• Tell them your name and show this screen</li>
                    <li>• They will release your QR code for check-in</li>
                    <li>• Scan the QR code to complete check-in</li>
                </ul>
            </div>

            <!-- Why This Process -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                <h4 class="font-semibold text-gray-700 mb-2">ℹ️ Why check in at reception?</h4>
                <p class="text-sm text-gray-600">
                    This ensures we know you've arrived and can prepare for your pet's appointment. 
                    It also helps us maintain an organized queue for all our patients.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Auto-check if QR code has been released
        const appointmentId = {{ $appointment->Appointment_ID }};
        const checkInterval = 3000; // Check every 3 seconds
        let isChecking = true;

        async function checkQRStatus() {
            if (!isChecking) return;

            try {
                const response = await fetch(`/appointments/${appointmentId}/check-status`);
                const data = await response.json();

                if (data.qr_released) {
                    // QR code has been released!
                    isChecking = false;

                    // Update UI
                    document.getElementById('statusIndicator').innerHTML = `
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                            <span>✓ QR Code ready! Redirecting...</span>
                        </div>
                    `;

                    // Redirect to QR code page after short delay
                    setTimeout(() => {
                        window.location.href = `/appointments/${appointmentId}/qrcode`;
                    }, 1500);
                }

                if (data.status === 'Completed') {
                    // Already checked in
                    isChecking = false;
                    window.location.href = data.redirect_url;
                }
            } catch (error) {
                console.error('Error checking status:', error);
            }

            // Continue checking
            if (isChecking) {
                setTimeout(checkQRStatus, checkInterval);
            }
        }

        // Start checking when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Start polling after 2 seconds
            setTimeout(checkQRStatus, 2000);
        });

        // Stop checking when user leaves page
        window.addEventListener('beforeunload', function() {
            isChecking = false;
        });
    </script>
</body>
</html>