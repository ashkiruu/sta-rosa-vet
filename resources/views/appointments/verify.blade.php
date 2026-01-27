<x-public-layout>
    <div class="max-w-4xl mx-auto px-4 py-10">
        
        {{-- Breadcrumbs --}}
        <div class="flex items-center gap-2 mb-6 ml-4">
            <a href="{{ url('/') }}" class="text-[10px] font-black uppercase tracking-widest text-black hover:text-red-700 transition">Home</a>
            <span class="text-black text-[10px]">/</span>
            <span class="text-[10px] font-black uppercase tracking-widest text-red-700">Verification Result</span>
        </div>

        {{-- Main Container --}}
        <div class="bg-white rounded-[2.5rem] shadow-2xl border-2 border-gray-100 overflow-hidden">
            @if(!$valid)
                {{-- Invalid QR Code or Appointment Not Found --}}
                <div class="flex flex-col lg:flex-row min-h-[500px]">
                    <div class="lg:w-1/3 bg-red-50 p-10 flex flex-col justify-center items-center text-center border-r-2 border-red-100">
                        <div class="text-6xl mb-4">❌</div>
                        <h2 class="text-2xl font-black text-red-800 uppercase tracking-tighter leading-none">Invalid<br>QR Code</h2>
                    </div>
                    <div class="lg:w-2/3 p-12 flex flex-col justify-center">
                        <h3 class="text-xl font-black text-gray-900 uppercase mb-4">Verification Failed</h3>
                        <p class="text-gray-500 font-bold uppercase text-[10px] tracking-widest mb-6 leading-relaxed">
                            {{ $message }}
                        </p>
                        <p class="text-gray-500 font-bold uppercase text-[10px] tracking-widest mb-6 leading-relaxed">
                            Possible reasons:
                        </p>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-3 text-xs font-black text-gray-700 uppercase"><span class="w-2 h-2 bg-red-500 rounded-full"></span> Expired Appointment</li>
                            <li class="flex items-center gap-3 text-xs font-black text-gray-700 uppercase"><span class="w-2 h-2 bg-red-500 rounded-full"></span> Cancelled Session</li>
                            <li class="flex items-center gap-3 text-xs font-black text-gray-700 uppercase"><span class="w-2 h-2 bg-red-500 rounded-full"></span> Invalid System Signature</li>
                        </ul>
                        <p class="text-sm text-gray-500 mb-6">Please check the QR code and try again, or contact the clinic for assistance.</p>
                        <a href="{{ url('/') }}" class="bg-black text-white text-center py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-lg hover:bg-gray-800 transition">
                            Back to Homepage
                        </a>
                    </div>
                </div>
            @elseif(isset($attendance['not_released']) && $attendance['not_released'])
                {{-- QR Code Not Released Yet --}}
                <div class="flex flex-col lg:flex-row min-h-[500px]">
                    <div class="lg:w-1/3 bg-yellow-50 p-10 flex flex-col justify-center items-center text-center border-r-2 border-yellow-100">
                        <div class="text-6xl mb-4">⏳</div>
                        <h2 class="text-2xl font-black text-yellow-800 uppercase tracking-tighter leading-none">QR Code<br>Not Released</h2>
                        <p class="mt-4 text-[10px] font-black text-yellow-600 uppercase tracking-widest">Check in at reception first</p>
                    </div>
                    <div class="lg:w-2/3 p-8 md:p-12 bg-white">
                        {{-- Information --}}
                        <div class="bg-yellow-50 border-2 border-yellow-200 rounded-3xl p-6 mb-6">
                            <div class="flex items-start gap-3">
                                <span class="text-2xl">🏥</span>
                                <div>
                                    <p class="text-yellow-800 font-black uppercase text-sm mb-2 tracking-tight">Check In Required</p>
                                    <p class="text-yellow-700 text-xs font-bold uppercase tracking-wide">
                                        {{ $attendance['message'] }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Appointment Details --}}
                        @if($appointment)
                            <div class="mb-6">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Appointment Reference</p>
                                <h3 class="text-3xl font-black text-gray-900 uppercase tracking-tighter">VET-{{ str_pad($appointment->Appointment_ID, 6, '0', STR_PAD_LEFT) }}</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div class="p-5 bg-gray-50 border-2 border-gray-100 rounded-3xl">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Patient</p>
                                    <p class="text-sm font-black text-gray-800 uppercase tracking-tight">🐾 {{ $appointment->pet->Pet_Name ?? 'N/A' }}</p>
                                </div>
                                <div class="p-5 bg-gray-50 border-2 border-gray-100 rounded-3xl">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Service</p>
                                    <p class="text-sm font-black text-gray-800 uppercase tracking-tight">{{ $appointment->service->Service_Name ?? 'N/A' }}</p>
                                </div>
                                <div class="p-5 bg-gray-50 border-2 border-gray-100 rounded-3xl">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Owner</p>
                                    <p class="text-sm font-black text-gray-800 uppercase tracking-tight">{{ ($appointment->user->First_Name ?? '') . ' ' . ($appointment->user->Last_Name ?? '') }}</p>
                                </div>
                                <div class="p-5 bg-gray-50 border-2 border-gray-100 rounded-3xl">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Status</p>
                                    <p class="text-sm font-black text-green-800 uppercase tracking-tight">{{ $appointment->Status }}</p>
                                </div>
                            </div>
                        @endif

                        {{-- Instructions --}}
                        <div class="bg-blue-50 rounded-3xl p-6">
                            <h4 class="font-black text-blue-800 uppercase text-sm mb-3 tracking-tight">💡 What to do?</h4>
                            <ul class="text-xs font-bold text-blue-700 space-y-2 uppercase tracking-wide">
                                <li>• Go to the reception desk</li>
                                <li>• Tell them your name and appointment details</li>
                                <li>• The receptionist will release your QR code</li>
                                <li>• Then scan the QR code again to check in</li>
                            </ul>
                        </div>
                    </div>
                </div>
            @elseif(isset($attendance['already_checked_in']) && $attendance['already_checked_in'])
                {{-- Already Checked In --}}
                <div class="flex flex-col lg:flex-row min-h-[500px]">
                    <div class="lg:w-1/3 bg-yellow-50 p-10 flex flex-col justify-center items-center text-center border-r-2 border-yellow-100">
                        <div class="text-6xl mb-4">⚠️</div>
                        <h2 class="text-2xl font-black text-yellow-800 uppercase tracking-tighter leading-none">Already<br>Checked In</h2>
                        <p class="mt-4 text-[10px] font-black text-yellow-600 uppercase tracking-widest">{{ $message }}</p>
                    </div>
                    <div class="lg:w-2/3 p-8 md:p-12 bg-white">
                        <div class="bg-blue-50 border-2 border-blue-200 rounded-3xl p-6 mb-6">
                            <p class="text-blue-800 text-center font-black uppercase text-sm tracking-tight">
                                <strong>Checked in at:</strong> {{ $attendance['check_in_time'] ?? 'N/A' }}
                            </p>
                        </div>

                        @if($appointment)
                            <div class="mb-6">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Appointment Reference</p>
                                <h3 class="text-3xl font-black text-gray-900 uppercase tracking-tighter">VET-{{ str_pad($appointment->Appointment_ID, 6, '0', STR_PAD_LEFT) }}</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="p-5 bg-gray-50 border-2 border-gray-100 rounded-3xl">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Patient</p>
                                    <p class="text-sm font-black text-gray-800 uppercase tracking-tight">🐾 {{ $appointment->pet->Pet_Name ?? 'N/A' }}</p>
                                </div>
                                <div class="p-5 bg-gray-50 border-2 border-gray-100 rounded-3xl">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Service</p>
                                    <p class="text-sm font-black text-gray-800 uppercase tracking-tight">{{ $appointment->service->Service_Name ?? 'N/A' }}</p>
                                </div>
                                <div class="p-5 bg-gray-50 border-2 border-gray-100 rounded-3xl">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Owner</p>
                                    <p class="text-sm font-black text-gray-800 uppercase tracking-tight">{{ ($appointment->user->First_Name ?? '') . ' ' . ($appointment->user->Last_Name ?? '') }}</p>
                                </div>
                                <div class="p-5 bg-gray-50 border-2 border-gray-100 rounded-3xl">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Schedule</p>
                                    <p class="text-sm font-black text-gray-800 uppercase tracking-tight">
                                        {{ \Carbon\Carbon::parse($appointment->Date)->format('M d') }} @ {{ \Carbon\Carbon::parse($appointment->Time)->format('h:i A') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        <div class="mt-8">
                            <a href="{{ url('/') }}" class="flex items-center justify-center w-full py-4 border-2 border-gray-200 text-gray-400 hover:text-black hover:border-black rounded-2xl font-black uppercase text-[10px] tracking-widest transition-all">
                                ← Return to Portal
                            </a>
                        </div>
                    </div>
                </div>
            @elseif($attendance && !isset($attendance['error']))
                {{-- Successfully Checked In --}}
                <div class="flex flex-col lg:flex-row min-h-[500px]">
                    <div class="lg:w-1/3 bg-green-50 p-10 flex flex-col justify-center items-center text-center border-r-2 border-green-100">
                        <div class="text-6xl mb-4">✅</div>
                        <h2 class="text-2xl font-black text-green-800 uppercase tracking-tighter leading-none">Check-In<br>Successful</h2>
                        <p class="mt-4 text-[10px] font-black text-green-600 uppercase tracking-widest">Recorded at {{ $attendance['check_in_time'] }}</p>
                    </div>
                    <div class="lg:w-2/3 p-8 md:p-12 bg-white">
                        <div class="bg-green-50 border-2 border-green-200 rounded-3xl p-6 mb-6">
                            <p class="text-green-800 text-center font-black uppercase text-sm tracking-tight">
                                Checked in at: {{ $attendance['check_in_time'] ?? now()->format('Y-m-d H:i:s') }}
                            </p>
                        </div>

                        @if($appointment)
                            <div class="mb-6">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Appointment Reference</p>
                                <h3 class="text-3xl font-black text-gray-900 uppercase tracking-tighter">VET-{{ str_pad($appointment->Appointment_ID, 6, '0', STR_PAD_LEFT) }}</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
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

                            <div class="p-5 bg-gray-50 border-2 border-gray-100 rounded-3xl mb-6">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Status</p>
                                <p class="text-sm font-black text-blue-800 uppercase tracking-tight">Completed</p>
                            </div>
                        @endif

                        <div class="bg-gray-50 rounded-3xl p-6 mb-6">
                            <h4 class="font-black text-gray-700 uppercase text-sm mb-3 tracking-tight">📋 Next Steps</h4>
                            <ul class="text-xs font-bold text-gray-600 space-y-2 uppercase tracking-wide">
                                <li>• Please take a seat in the waiting area</li>
                                <li>• You will be called when it's your turn</li>
                                <li>• Keep your pet calm and comfortable</li>
                            </ul>
                        </div>

                        <div class="w-full bg-green-600 text-white text-center py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-lg">
                            ✓ Clear to Proceed
                        </div>
                    </div>
                </div>
            @else
                {{-- Other Status Messages (Pending, Declined, etc.) --}}
                <div class="flex flex-col lg:flex-row min-h-[500px]">
                    <div class="lg:w-1/3 bg-gray-50 p-10 flex flex-col justify-center items-center text-center border-r-2 border-gray-100">
                        <div class="text-6xl mb-4">⚠️</div>
                        <h2 class="text-2xl font-black text-gray-800 uppercase tracking-tighter leading-none">Cannot<br>Check In</h2>
                    </div>
                    <div class="lg:w-2/3 p-8 md:p-12 bg-white">
                        <div class="bg-gray-50 border-2 border-gray-200 rounded-3xl p-6 mb-6">
                            <p class="text-gray-700 text-center font-black uppercase text-sm tracking-tight">{{ $message }}</p>
                        </div>

                        @if($appointment)
                            <div class="mb-6">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Appointment Reference</p>
                                <h3 class="text-3xl font-black text-gray-900 uppercase tracking-tighter">VET-{{ str_pad($appointment->Appointment_ID, 6, '0', STR_PAD_LEFT) }}</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div class="p-5 bg-gray-50 border-2 border-gray-100 rounded-3xl">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Patient</p>
                                    <p class="text-sm font-black text-gray-800 uppercase tracking-tight">🐾 {{ $appointment->pet->Pet_Name ?? 'N/A' }}</p>
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
                                <div class="p-5 bg-gray-50 border-2 border-gray-100 rounded-3xl">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Status</p>
                                    <p class="text-sm font-black uppercase tracking-tight
                                        @if($appointment->Status === 'Pending') text-yellow-800
                                        @elseif($appointment->Status === 'Declined') text-red-800
                                        @else text-gray-800 @endif">
                                        {{ $appointment->Status }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        <div class="bg-yellow-50 rounded-3xl p-6">
                            <h4 class="font-black text-yellow-800 uppercase text-sm mb-3 tracking-tight">💡 Need Help?</h4>
                            <p class="text-xs font-bold text-yellow-700 uppercase tracking-wide">
                                Please speak with the receptionist if you believe this is an error or need assistance.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <p class="text-center mt-8 text-[9px] font-black text-gray-400 uppercase tracking-[0.3em]">
            Secured Verification System • {{ now()->format('Y') }}
        </p>
    </div>
</x-public-layout>