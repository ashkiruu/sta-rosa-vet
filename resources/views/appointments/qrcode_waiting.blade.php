<x-dashboardheader-layout>
    <div class="max-w-6xl mx-auto px-4 py-10">
        
        {{-- Breadcrumbs --}}
        <div class="flex items-center gap-2 mb-6 ml-4">
            <a href="{{ route('dashboard') }}" class="text-[10px] font-black uppercase tracking-widest text-black hover:text-red-700 transition">Dashboard</a>
            <span class="text-black text-[10px]">/</span>
            <a href="{{ route('appointments.index') }}" class="text-[10px] font-black uppercase tracking-widest text-black hover:text-red-700 transition">My Appointments</a>
            <span class="text-black text-[10px]">/</span>
            <span class="text-[10px] font-black uppercase tracking-widest text-red-700">Check-in Status</span>
        </div>

        {{-- Main Pass Container --}}
        <div class="bg-white rounded-[2.5rem] shadow-2xl border-2 border-gray-100 overflow-hidden">
            <div class="flex flex-col lg:flex-row min-h-[650px]">
                
                {{-- LEFT: Instructions (Red Sidebar) --}}
                <div class="lg:w-1/4 bg-red-50 p-10 flex flex-col justify-center border-r-2 border-red-100">
                    <h4 class="text-red-700 font-black uppercase text-3xl leading-none tracking-tighter mb-8 border-l-4 border-red-700 pl-4">
                        Next<br>Steps
                    </h4>
                    <ul class="space-y-8">
                        <li class="flex flex-col gap-1">
                            <span class="text-[10px] font-black text-red-400 uppercase tracking-widest">Step 01</span>
                            <p class="text-xs font-black text-red-900 uppercase tracking-tight">Proceed to the reception desk upon arrival.</p>
                        </li>
                        <li class="flex flex-col gap-1">
                            <span class="text-[10px] font-black text-red-400 uppercase tracking-widest">Step 02</span>
                            <p class="text-xs font-black text-red-900 uppercase tracking-tight">State your name or show the Reference ID.</p>
                        </li>
                        <li class="flex flex-col gap-1">
                            <span class="text-[10px] font-black text-red-400 uppercase tracking-widest">Step 03</span>
                            <p class="text-xs font-black text-red-900 uppercase tracking-tight">Wait for the staff to release your digital QR code.</p>
                        </li>
                    </ul>
                    
                    <div class="mt-8 pt-8 border-t border-red-200">
                        <p class="text-[9px] font-black text-red-400 uppercase tracking-widest mb-2">Why do this?</p>
                        <p class="text-[10px] font-bold text-red-800 leading-relaxed">
                            Checking in ensures we know you've arrived and keeps the queue organized.
                        </p>
                    </div>
                </div>

                {{-- MIDDLE: Main Status --}}
                <div class="lg:w-5/12 p-8 md:p-12 text-center border-b-2 lg:border-b-0 lg:border-r-2 border-gray-100 flex flex-col items-center justify-center bg-white relative">
                    
                    {{-- Status Badge --}}
                    <div class="absolute top-6 right-6">
                        <span class="bg-blue-100 text-blue-800 text-[9px] font-black px-3 py-1 rounded-full uppercase tracking-widest border border-blue-200">
                            Approved
                        </span>
                    </div>

                    <div class="mb-10">
                        <h2 class="text-3xl font-black text-gray-900 uppercase tracking-tighter leading-none mb-2">Appointment<br>Approved</h2>
                        <p class="text-gray-400 font-bold uppercase text-[10px] tracking-[0.2em]">Please check in at reception</p>
                    </div>

                    {{-- Central Visual --}}
                    <div class="w-48 h-48 bg-blue-50 rounded-[3rem] flex items-center justify-center mb-10 border-4 border-blue-100">
                        <span class="text-6xl animate-bounce">🏥</span>
                    </div>

                    <div class="mb-6">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Reference Number</p>
                        <p class="text-2xl font-black text-gray-800 font-mono tracking-tight">VET-{{ str_pad($appointment->Appointment_ID, 6, '0', STR_PAD_LEFT) }}</p>
                    </div>

                    {{-- Status Indicator (Target for JS) --}}
                    <div id="statusIndicator" class="w-full">
                        <div class="inline-flex items-center gap-3 px-6 py-3 bg-yellow-50 border-2 border-yellow-200 text-yellow-800 rounded-full font-black uppercase text-[10px] tracking-widest">
                            <span class="flex h-2.5 w-2.5 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-yellow-600"></span>
                            </span>
                            Waiting for QR release...
                        </div>
                    </div>
                    
                    <p class="text-[9px] font-bold text-gray-300 uppercase tracking-widest mt-4">Screen will update automatically</p>
                </div>

                {{-- RIGHT: Details Grid --}}
                <div class="lg:w-1/3 p-8 md:p-12 bg-gray-50/30 flex flex-col justify-center">
                    <div class="space-y-5">
                        
                        {{-- Patient Info --}}
                        <div class="p-5 bg-white border-2 border-gray-100 rounded-[2rem] flex items-center gap-4 shadow-sm">
                            <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center text-xl">🐾</div>
                            <div>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Patient</p>
                                <h3 class="font-black text-gray-800 uppercase text-lg leading-tight">{{ $appointment->pet->Pet_Name }}</h3>
                            </div>
                        </div>

                        {{-- Service Info --}}
                        <div class="p-5 bg-white border-2 border-gray-100 rounded-[2rem] flex items-center gap-4 shadow-sm">
                            <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center text-xl">📋</div>
                            <div>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Service</p>
                                <h3 class="font-black text-gray-800 uppercase text-lg leading-tight">{{ $appointment->service->Service_Name }}</h3>
                            </div>
                        </div>

                        {{-- Date & Time --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-5 bg-white border-2 border-gray-100 rounded-[1.5rem] shadow-sm text-center">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Date</p>
                                <p class="text-[11px] font-black text-gray-800 uppercase">{{ \Carbon\Carbon::parse($appointment->Date)->format('M d, Y') }}</p>
                            </div>
                            <div class="p-5 bg-white border-2 border-gray-100 rounded-[1.5rem] shadow-sm text-center">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Time</p>
                                <p class="text-[11px] font-black text-gray-800 uppercase">{{ \Carbon\Carbon::parse($appointment->Time)->format('h:i A') }}</p>
                            </div>
                        </div>

                        {{-- Current Status --}}
                        <div class="p-6 border-2 border-green-200 bg-green-50 rounded-[2rem] flex items-center justify-between shadow-sm">
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest mb-0.5 text-green-600 opacity-70">Current Status</p>
                                <h3 class="font-black uppercase text-xl tracking-tighter text-green-700" id="appointmentStatus">{{ $appointment->Status }}</h3>
                            </div>
                            <div class="text-2xl text-green-600">✓</div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Original Script Preserved --}}
    <script>
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

                    // Update UI to match the new design style
                    document.getElementById('statusIndicator').innerHTML = `
                        <div class="inline-flex items-center gap-3 px-6 py-3 bg-green-50 border-2 border-green-200 text-green-800 rounded-full font-black uppercase text-[10px] tracking-widest">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                            <span>QR Ready! Redirecting...</span>
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
</x-dashboardheader-layout>