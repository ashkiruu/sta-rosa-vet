<x-dashboardheader-layout>
    <div class="max-w-4xl mx-auto px-4 py-10">
        
        {{-- Breadcrumbs --}}
        <div class="flex items-center gap-2 mb-6 ml-4">
            <a href="{{ url('/') }}" class="text-[10px] font-black uppercase tracking-widest text-black hover:text-red-700 transition">Home</a>
            <span class="text-black text-[10px]">/</span>
            <span class="text-[10px] font-black uppercase tracking-widest text-red-700">Verification Result</span>
        </div>

        {{-- Main Container --}}
        <div class="bg-white rounded-[2.5rem] shadow-2xl border-2 border-gray-100 overflow-hidden">
            @if($valid && $appointment)
                <div class="flex flex-col lg:flex-row min-h-[500px]">
                    
                    {{-- LEFT SIDE: STATUS HEADER --}}
                    <div class="lg:w-1/3 flex flex-col justify-center items-center p-10 text-center
                        @if(isset($attendance) && $attendance)
                            @if($attendance['already_checked_in']) bg-yellow-50 border-r-2 border-yellow-100 @else bg-green-50 border-r-2 border-green-100 @endif
                        @else bg-blue-50 border-r-2 border-blue-100 @endif">
                        
                        @if(isset($attendance) && $attendance)
                            @if($attendance['already_checked_in'])
                                <div class="text-6xl mb-4">⚠️</div>
                                <h2 class="text-2xl font-black text-yellow-800 uppercase tracking-tighter leading-none">Already<br>Checked In</h2>
                                <p class="mt-4 text-[10px] font-black text-yellow-600 uppercase tracking-widest">{{ $message }}</p>
                            @else
                                <div class="text-6xl mb-4">✅</div>
                                <h2 class="text-2xl font-black text-green-800 uppercase tracking-tighter leading-none">Check-In<br>Successful</h2>
                                <p class="mt-4 text-[10px] font-black text-green-600 uppercase tracking-widest">Recorded at {{ $attendance['check_in_time'] }}</p>
                            @endif
                        @else
                            <div class="text-6xl mb-4">ℹ️</div>
                            <h2 class="text-2xl font-black text-blue-800 uppercase tracking-tighter leading-none">Valid<br>Record</h2>
                            <p class="mt-4 text-[10px] font-black text-blue-600 uppercase tracking-widest">Appointment found</p>
                        @endif
                    </div>

                    {{-- RIGHT SIDE: DETAILS GRID --}}
                    <div class="lg:w-2/3 p-8 md:p-12 bg-white">
                        <div class="mb-8">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Appointment Reference</p>
                            <h3 class="text-3xl font-black text-gray-900 uppercase tracking-tighter">VET-{{ str_pad($appointment->Appointment_ID, 6, '0', STR_PAD_LEFT) }}</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Info Cards --}}
                            <div class="p-5 bg-gray-50 border-2 border-gray-100 rounded-3xl">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Patient</p>
                                <p class="text-sm font-black text-gray-800 uppercase tracking-tight">🐾 {{ $appointment->pet->Pet_Name ?? 'N/A' }}</p>
                            </div>

                            <div class="p-5 bg-gray-50 border-2 border-gray-100 rounded-3xl">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Owner</p>
                                <p class="text-sm font-black text-gray-800 uppercase tracking-tight">{{ $appointment->user->First_Name ?? '' }} {{ $appointment->user->Last_Name ?? '' }}</p>
                            </div>

                            <div class="p-5 bg-gray-50 border-2 border-gray-100 rounded-3xl">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Service</p>
                                <p class="text-sm font-black text-gray-800 uppercase tracking-tight">{{ $appointment->service->Service_Name ?? 'N/A' }}</p>
                            </div>

                            <div class="p-5 bg-gray-50 border-2 border-gray-100 rounded-3xl">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Schedule</p>
                                <p class="text-sm font-black text-gray-800 uppercase tracking-tight">
                                    {{ \Carbon\Carbon::parse($appointment->Date)->format('M d') }} @ {{ \Carbon\Carbon::parse($appointment->Time)->format('h:i A') }}
                                </p>
                            </div>
                        </div>

                        {{-- Action Button for Staff --}}
                        <div class="mt-8">
                            @if(isset($attendance) && $attendance && !$attendance['already_checked_in'])
                                <div class="w-full bg-green-600 text-white text-center py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-lg">
                                    ✓ Clear to Proceed
                                </div>
                            @else
                                <a href="{{ url('/') }}" class="flex items-center justify-center w-full py-4 border-2 border-gray-200 text-gray-400 hover:text-black hover:border-black rounded-2xl font-black uppercase text-[10px] tracking-widest transition-all">
                                    ← Return to Portal
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                {{-- INVALID QR CODE VIEW --}}
                <div class="flex flex-col lg:flex-row min-h-[500px]">
                    <div class="lg:w-1/3 bg-red-50 p-10 flex flex-col justify-center items-center text-center border-r-2 border-red-100">
                        <div class="text-6xl mb-4">❌</div>
                        <h2 class="text-2xl font-black text-red-800 uppercase tracking-tighter leading-none">Invalid<br>QR Code</h2>
                    </div>
                    <div class="lg:w-2/3 p-12 flex flex-col justify-center">
                        <h3 class="text-xl font-black text-gray-900 uppercase mb-4">Verification Failed</h3>
                        <p class="text-gray-500 font-bold uppercase text-[10px] tracking-widest mb-6 leading-relaxed">
                            The scanned code could not be verified. Possible reasons:
                        </p>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-3 text-xs font-black text-gray-700 uppercase"><span class="w-2 h-2 bg-red-500 rounded-full"></span> Expired Appointment</li>
                            <li class="flex items-center gap-3 text-xs font-black text-gray-700 uppercase"><span class="w-2 h-2 bg-red-500 rounded-full"></span> Cancelled Session</li>
                            <li class="flex items-center gap-3 text-xs font-black text-gray-700 uppercase"><span class="w-2 h-2 bg-red-500 rounded-full"></span> Invalid System Signature</li>
                        </ul>
                        <a href="{{ url('/') }}" class="bg-black text-white text-center py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-lg hover:bg-gray-800 transition">
                            Back to Homepage
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <p class="text-center mt-8 text-[9px] font-black text-gray-400 uppercase tracking-[0.3em]">
            Secured Verification System • {{ now()->format('Y') }}
        </p>
    </div>
</x-dashboardheader-layout>