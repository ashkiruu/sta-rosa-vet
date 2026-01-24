<x-dashboardheader-layout>
    <div class="max-w-6xl mx-auto px-4 py-10">
        
        {{-- Breadcrumbs --}}
        <div class="flex items-center gap-2 mb-6 ml-4">
            <a href="{{ route('dashboard') }}" class="text-[10px] font-black uppercase tracking-widest text-black hover:text-red-700 transition">Dashboard</a>
            <span class="text-black text-[10px]">/</span>
            <a href="{{ route('appointments.index') }}" class="text-[10px] font-black uppercase tracking-widest text-black hover:text-red-700 transition">My Appointments</a>
            <span class="text-black text-[10px]">/</span>
            <span class="text-[10px] font-black uppercase tracking-widest text-red-700">Digital Pass</span>
        </div>

        {{-- Main Pass Container --}}
        <div class="bg-white rounded-[2.5rem] shadow-2xl border-2 border-gray-100 overflow-hidden">
            <div class="flex flex-col lg:flex-row min-h-[650px]">
                
                {{-- AT MOST LEFT: Important Reminders (Lightened Red Sidebar) --}}
                <div class="lg:w-1/4 bg-red-50 p-10 flex flex-col justify-center border-r-2 border-red-100">
                    <h4 class="text-red-700 font-black uppercase text-3xl leading-none tracking-tighter mb-8 border-l-4 border-red-700 pl-4">
                        Important<br>Reminders
                    </h4>
                    <ul class="space-y-8">
                        <li class="flex flex-col gap-1">
                            <span class="text-[10px] font-black text-red-400 uppercase tracking-widest">Step 01</span>
                            <p class="text-xs font-black text-red-900 uppercase tracking-tight">Arrive 10 minutes before your scheduled time slot.</p>
                        </li>
                        <li class="flex flex-col gap-1">
                            <span class="text-[10px] font-black text-red-400 uppercase tracking-widest">Step 02</span>
                            <p class="text-xs font-black text-red-900 uppercase tracking-tight">Present this QR code to the clinic staff upon arrival.</p>
                        </li>
                        <li class="flex flex-col gap-1">
                            <span class="text-[10px] font-black text-red-400 uppercase tracking-widest">Step 03</span>
                            <p class="text-xs font-black text-red-900 uppercase tracking-tight">Ensure phone brightness is high for scanning.</p>
                        </li>
                    </ul>
                </div>

                {{-- MIDDLE: QR & Download --}}
                <div class="lg:w-5/12 p-8 md:p-12 text-center border-b-2 lg:border-b-0 lg:border-r-2 border-gray-100 flex flex-col items-center justify-center bg-white">
                    <div class="mb-8">
                        <h2 class="text-4xl font-black text-gray-900 uppercase tracking-tighter">Digital Pass</h2>
                        <p class="text-red-700 font-bold uppercase text-xs tracking-[0.2em] mt-1">Ref: VET-{{ str_pad($appointment->Appointment_ID, 6, '0', STR_PAD_LEFT) }}</p>
                    </div>

                    {{-- QR Code --}}
                    <div class="bg-white p-6 rounded-[2.5rem] border-[6px] border-black shadow-sm mb-8 inline-block">
                        @php
                            $token = substr(md5($appointment->Appointment_ID . '-' . $appointment->User_ID . '-' . $appointment->Date . config('app.key', 'veterinary-clinic-secret')), 0, 16);
                            $verificationUrl = url("/appointments/verify/{$appointment->Appointment_ID}/{$token}");
                            $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=350x350&data=' . urlencode($verificationUrl);
                        @endphp
                        <img src="{{ $qrApiUrl }}" alt="QR Code" class="w-56 h-56 md:w-64 md:h-64 mx-auto" id="qrImage">
                    </div>

                    {{-- Live Status --}}
                    <div id="statusIndicator" class="mb-10">
                        <div class="inline-flex items-center gap-3 px-8 py-3 bg-yellow-50 border-2 border-yellow-200 text-yellow-800 rounded-full font-black uppercase text-[10px] tracking-widest">
                            <span class="flex h-2.5 w-2.5 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-yellow-600"></span>
                            </span>
                            Waiting for check-in
                        </div>
                    </div>

                    <a href="{{ route('appointments.qrcode.download', $appointment->Appointment_ID) }}" 
                       class="w-full max-w-sm bg-black hover:bg-gray-800 text-white font-black py-5 rounded-2xl shadow-xl transition-all active:scale-95 uppercase tracking-widest text-xs flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download QR
                    </a>
                </div>

                {{-- RIGHT: Information Grid --}}
                <div class="lg:w-1/3 p-8 md:p-12 bg-gray-50/30 flex flex-col justify-center">
                    <div class="space-y-5">
                        
                        <div class="p-5 bg-white border-2 border-gray-100 rounded-[2rem] flex items-center gap-4 shadow-sm">
                            <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center text-xl">🐾</div>
                            <div>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Patient</p>
                                <h3 class="font-black text-gray-800 uppercase text-lg leading-tight">{{ $appointment->pet->Pet_Name }}</h3>
                            </div>
                        </div>

                        <div class="p-5 bg-white border-2 border-gray-100 rounded-[2rem] flex items-center gap-4 shadow-sm">
                            <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center text-xl">📋</div>
                            <div>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Service</p>
                                <h3 class="font-black text-gray-800 uppercase text-lg leading-tight">{{ $appointment->service->Service_Name }}</h3>
                            </div>
                        </div>

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

                        <div class="p-6 border-2 rounded-[2rem] flex items-center justify-between shadow-sm
                            {{ in_array($appointment->Status, ['Approved', 'Confirmed']) ? 'bg-green-50 border-green-200 text-green-700' : 'bg-gray-50 border-gray-200 text-gray-700' }}">
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest mb-0.5 opacity-70">Status</p>
                                <h3 class="font-black uppercase text-xl tracking-tighter" id="appointmentStatus">{{ $appointment->Status }}</h3>
                            </div>
                            <div class="text-2xl">✓</div>
                        </div>

                        <a href="{{ route('appointments.index') }}" 
                           class="flex items-center justify-center w-full py-4 border-2 border-gray-200 text-gray-400 hover:text-black hover:border-black rounded-2xl font-black uppercase text-[10px] tracking-widest transition-all mt-4">
                            — Back to appointments
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const appointmentId = {{ $appointment->Appointment_ID }};
        let isChecking = true;

        async function checkAttendanceStatus() {
            if (!isChecking) return;
            try {
                const response = await fetch(`/appointments/${appointmentId}/check-status`);
                const data = await response.json();
                if (data.status === 'Completed' || data.status === 'Confirmed') {
                    isChecking = false;
                    document.getElementById('statusIndicator').innerHTML = `
                        <div class="inline-flex items-center gap-2 px-6 py-2 bg-green-50 border-2 border-green-200 text-green-800 rounded-full font-black uppercase text-[10px] tracking-widest transition-all">
                            <span class="h-2 w-2 bg-green-600 rounded-full"></span>
                            Verified
                        </div>`;
                    document.getElementById('appointmentStatus').textContent = 'Completed';
                    setTimeout(() => { window.location.href = data.redirect_url; }, 2000);
                }
            } catch (error) { console.log('Checking...'); }
            if (isChecking) setTimeout(checkAttendanceStatus, 3000);
        }
        document.addEventListener('DOMContentLoaded', () => setTimeout(checkAttendanceStatus, 2000));
        window.addEventListener('beforeunload', () => isChecking = false);
    </script>
</x-dashboardheader-layout>