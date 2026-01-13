<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Appointment</title>
    @vite([
    'resources/css/app.css',
    'resources/css/confirm_appointment.css',
    'resources/js/app.js'
])

</head>
<body>
    <!-- Header -->
    <nav class="bg-gradient-to-r from-red-800 to-red-700 text-white px-6 py-3 relative z-10">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center overflow-hidden">
                    <span class="text-red-700 font-bold text-lg">🐾</span>
                </div>
                <div class="leading-tight">
                    <p class="text-xs text-red-200">City of</p>
                    <h1 class="font-bold text-lg">Veterinary</h1>
                    <p class="text-xs text-red-200">Office</p>
                </div>
            </div>
            <div class="flex gap-4 items-center">
                <button class="w-10 h-10 flex items-center justify-center text-white hover:bg-red-600 rounded-lg transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </button>
                <button class="w-10 h-10 flex items-center justify-center text-white hover:bg-red-600 rounded-lg transition border border-white/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-12 px-4 max-w-4xl pb-12 relative z-10">
        <div class="main-card">
            <div class="flex flex-col lg:flex-row gap-8">
                
                <!-- Left Side - Notes -->
                <div class="lg:w-1/2">
                    <h2 class="note-title">NOTE:</h2>
                    <ol class="note-list list-decimal list-inside space-y-3">
                        <li>Appointment booked at City of Santa Rosa Veterinary Office is non-transferable and cannot be rescheduled.</li>
                        <li>If an applicant wishes to make any changes after obtaining their appointment, the existing appointment must first be cancelled. A new appointment can then be booked.</li>
                        <li>Please be advised that your chosen time slot is reserved for 10 minutes. Please arrive on time.</li>
                    </ol>
                    
                    <div class="mt-8">
                        <a href="{{ route('appointments.create') }}" class="back-btn">
                            ← Go Back
                        </a>
                    </div>
                </div>
                
                <!-- Right Side - Appointment Summary -->
                <div class="lg:w-1/2">
                    <div class="info-card">
                        <!-- Service Type -->
                        <div class="service-badge mb-4">
                            <span class="service-dot"></span>
                            <span class="font-semibold text-gray-800">{{ $service->Service_Name }}</span>
                        </div>
                        
                        <!-- Date and Time -->
                        <div class="datetime-box mb-4">
                            <p class="font-semibold text-gray-800">
                                {{ \Carbon\Carbon::parse($appointmentData['Date'])->format('l, F j, Y') }}
                            </p>
                            <p class="text-gray-700 mt-1">
                                Time: {{ \Carbon\Carbon::parse($appointmentData['Time'])->format('h:i A') }}
                            </p>
                        </div>
                        
                        <!-- Pet -->
                        <div class="pet-row mb-6">
                            <span class="text-gray-600 font-medium">Pet:</span>
                            <div class="pet-badge">
                                <span>🐕</span>
                                <span>{{ $pet->Pet_Name }}</span>
                            </div>
                        </div>
                        
                        <!-- Confirm Button -->
                        <form method="POST" action="{{ route('appointments.confirm') }}">
                            @csrf
                            <input type="hidden" name="Service_ID" value="{{ $appointmentData['Service_ID'] }}">
                            <input type="hidden" name="Pet_ID" value="{{ $appointmentData['Pet_ID'] }}">
                            <input type="hidden" name="Date" value="{{ $appointmentData['Date'] }}">
                            <input type="hidden" name="Time" value="{{ $appointmentData['Time'] }}">
                            <input type="hidden" name="Location" value="{{ $appointmentData['Location'] }}">
                            <input type="hidden" name="Special_Notes" value="{{ $appointmentData['Special_Notes'] }}">
                            
                            <button type="submit" class="confirm-btn">
                                Confirm
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>