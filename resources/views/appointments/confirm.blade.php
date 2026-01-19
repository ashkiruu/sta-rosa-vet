<x-dashboardheader-layout>
    <div class="max-w-5xl mx-auto px-4 py-10">
        
        {{-- Top Navigation Breadcrumbs --}}
        <div class="flex items-center gap-2 mb-6 ml-4">
            <a href="{{ route('dashboard') }}" class="text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-red-700 transition">Dashboard</a>
            <span class="text-gray-300 text-[10px]">/</span>
            <a href="{{ route('appointments.create') }}" class="text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-red-700 transition">Appointment Booking</a>
            <span class="text-gray-300 text-[10px]">/</span>
            <span class="text-[10px] font-black uppercase tracking-widest text-red-700">Confirm Appointment</span>
        </div>

        {{-- Main White Box --}}
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden">
            <div class="p-8 md:p-12">
                
                {{-- Centered Header & Progress Bar Section --}}
                <div class="flex flex-col items-center justify-center mb-12">
                    <h2 class="text-4xl font-black text-gray-900 uppercase tracking-tight">Book Appointment</h2>
                    <p class="text-red-700 font-bold uppercase text-xs tracking-[0.2em] mt-2 mb-8">Step 2: Review & Confirm Details</p>

                    {{-- Centered Progress Bar --}}
                    <div class="flex items-center justify-center w-full max-w-md relative">
                        <div class="absolute top-1/2 left-0 w-full h-0.5 bg-gray-300 -translate-y-1/2 z-0"></div>
                        <div class="flex justify-between w-full relative z-10">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-200 text-gray-500 font-black border-4 border-white shadow-sm">1</div>
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-700 text-white font-black shadow-lg border-4 border-white">2</div>
                        </div>
                    </div>
                </div>

                {{-- Summary Content --}}
                <div class="flex flex-col lg:flex-row gap-12">
                    
                    {{-- Left Side: Notes with Red Circle Numbers --}}
                    <div class="lg:w-1/2">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="text-red-700 font-black uppercase text-xs tracking-widest">Important Notes</span>
                        </div>
                        
                        <div class="bg-gray-50/50 rounded-3xl p-8 border border-gray-100">
                            <ul class="space-y-6">
                                <li class="flex gap-4">
                                    <span class="shrink-0 w-6 h-6 bg-red-700 text-white rounded-full flex items-center justify-center text-[10px] font-black">1</span>
                                    <p class="text-[11px] font-bold text-gray-600 uppercase tracking-wider leading-relaxed">Appointment booked at City of Santa Rosa Veterinary Office is non-transferable and cannot be rescheduled.</p>
                                </li>
                                <li class="flex gap-4">
                                    <span class="shrink-0 w-6 h-6 bg-red-700 text-white rounded-full flex items-center justify-center text-[10px] font-black">2</span>
                                    <p class="text-[11px] font-bold text-gray-600 uppercase tracking-wider leading-relaxed">If an applicant wishes to make changes, the existing appointment must first be cancelled.</p>
                                </li>
                                <li class="flex gap-4">
                                    <span class="shrink-0 w-6 h-6 bg-red-700 text-white rounded-full flex items-center justify-center text-[10px] font-black">3</span>
                                    <p class="text-[11px] font-bold text-gray-600 uppercase tracking-wider leading-relaxed">Your chosen time slot is reserved for 10 minutes. Please arrive on time.</p>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    {{-- Right Side: Appointment Summary --}}
                    <div class="lg:w-1/2">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="text-red-700 font-black uppercase text-xs tracking-widest">Appointment Summary</span>
                        </div>

                        <div class="space-y-4">
                            <div class="p-6 bg-red-50/50 border-2 border-red-600 rounded-2xl flex items-center justify-between">
                                <div>
                                    <p class="text-[9px] font-black text-red-700 uppercase tracking-widest mb-1">Service</p>
                                    <h3 class="text-lg font-black text-gray-800 uppercase">{{ $service->Service_Name }}</h3>
                                </div>
                                <span class="text-2xl">📋</span>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-5 border-2 border-gray-100 rounded-2xl flex flex-col justify-center min-h-[85px]">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Date</p>
                                    <p class="text-[11px] font-black text-gray-800 uppercase">{{ \Carbon\Carbon::parse($appointmentData['Date'])->format('M d, Y') }}</p>
                                    <p class="text-[9px] font-bold text-gray-500 uppercase">{{ \Carbon\Carbon::parse($appointmentData['Date'])->format('l') }}</p>
                                </div>
                                <div class="p-5 border-2 border-gray-100 rounded-2xl flex flex-col justify-center min-h-[85px]">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Time Slot</p>
                                    <p class="text-[11px] font-black text-gray-800 uppercase">{{ \Carbon\Carbon::parse($appointmentData['Time'])->format('h:i A') }}</p>
                                </div>
                            </div>

                            <div class="p-6 border-2 border-gray-100 rounded-2xl flex items-center gap-4 group transition-all">
                                <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center text-xl transition-colors">
                                    🐾
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Patient</p>
                                    <h3 class="font-black text-gray-800 uppercase tracking-widest">{{ $pet->Pet_Name }}</h3>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons Row --}}
                        <form method="POST" action="{{ route('appointments.confirm') }}" class="mt-8">
                            @csrf
                            <input type="hidden" name="Service_ID" value="{{ $appointmentData['Service_ID'] }}">
                            <input type="hidden" name="Pet_ID" value="{{ $appointmentData['Pet_ID'] }}">
                            <input type="hidden" name="Date" value="{{ $appointmentData['Date'] }}">
                            <input type="hidden" name="Time" value="{{ $appointmentData['Time'] }}">
                            <input type="hidden" name="Location" value="{{ $appointmentData['Location'] }}">
                            <input type="hidden" name="Special_Notes" value="{{ $appointmentData['Special_Notes'] }}">
                            
                            <div class="flex flex-col sm:flex-row gap-4">
                                <a href="{{ route('appointments.create') }}" 
                                    class="flex-1 text-center py-5 border-2 border-gray-200 text-gray-400 hover:text-red-700 hover:border-red-700 rounded-2xl font-black uppercase text-[10px] tracking-widest transition-all active:scale-95">
                                    ← Go Back & Edit
                                </a>
                                
                                <button type="submit" 
                                    class="flex-[2] bg-red-700 hover:bg-red-800 text-white font-black py-5 rounded-2xl shadow-xl transition-all active:scale-95 uppercase tracking-[0.2em] text-[10px]">
                                    Finalize Appointment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboardheader-layout>