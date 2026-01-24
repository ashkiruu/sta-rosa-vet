<x-dashboardheader-layout>
    <div class="max-w-5xl mx-auto px-4 py-10">
        
        {{-- Breadcrumbs aligned with design system --}}
        <div class="flex items-center gap-2 mb-6 ml-4">
            <a href="{{ route('dashboard') }}" class="text-[10px] font-black uppercase tracking-widest text-black hover:text-red-700 transition">Dashboard</a>
            <span class="text-black text-[10px]">/</span>
            <a href="{{ route('appointments.index') }}" class="text-[10px] font-black uppercase tracking-widest text-black hover:text-red-700 transition">My Appointments</a>
            <span class="text-black text-[10px]">/</span>
            <span class="text-[10px] font-black uppercase tracking-widest text-red-700">Details #{{ $appointment->Appointment_ID }}</span>
        </div>

        {{-- Main Container --}}
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden">
            <div class="p-8 md:p-12">
                
                {{-- Header Section with Status Badge --}}
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12 pb-8 border-b-2 border-gray-50">
                    <div>
                        <h2 class="text-4xl font-black text-gray-900 uppercase tracking-tight">Appointment Details</h2>
                        <p class="text-red-700 font-bold uppercase text-xs tracking-[0.2em] mt-2">Reference ID: #{{ $appointment->Appointment_ID }}</p>
                    </div>
                    
                    <div class="inline-flex items-center px-6 py-3 rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-sm
                        {{ $appointment->Status == 'Pending' ? 'bg-yellow-50 text-yellow-700 border-2 border-yellow-200' : '' }}
                        {{ in_array($appointment->Status, ['Approved', 'Confirmed']) ? 'bg-green-50 text-green-700 border-2 border-green-200' : '' }}
                        {{ $appointment->Status == 'Cancelled' ? 'bg-red-50 text-red-700 border-2 border-red-200' : '' }}
                        {{ $appointment->Status == 'Completed' ? 'bg-blue-50 text-blue-700 border-2 border-blue-200' : '' }}">
                        <span class="mr-2">●</span> {{ $appointment->Status }}
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-12">
                    
                    {{-- Left Side: Patient & Service Info --}}
                    <div class="lg:w-1/2 space-y-8">
                        {{-- Pet Info --}}
                        <section>
                            <div class="flex items-center gap-3 mb-6">
                                <span class="w-8 h-8 bg-red-700 text-white rounded-full flex items-center justify-center text-xs font-black">1</span>
                                <span class="text-red-700 font-black uppercase text-xs tracking-widest">Patient Information</span>
                            </div>
                            <div class="p-6 bg-gray-50/50 border-2 border-gray-100 rounded-[2rem] flex items-center gap-5">
                                <div class="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center text-3xl">🐾</div>
                                <div>
                                    <h3 class="text-xl font-black text-gray-800 uppercase">{{ $appointment->pet->Pet_Name }}</h3>
                                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">{{ $appointment->pet->species->Species_Name ?? 'Pet' }} • {{ $appointment->pet->Breed ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </section>

                        {{-- Service Info --}}
                        <section>
                            <div class="flex items-center gap-3 mb-6">
                                <span class="w-8 h-8 bg-red-700 text-white rounded-full flex items-center justify-center text-xs font-black">2</span>
                                <span class="text-red-700 font-black uppercase text-xs tracking-widest">Service Requested</span>
                            </div>
                            <div class="p-6 bg-red-50/30 border-2 border-red-100 rounded-[2rem]">
                                <p class="text-[9px] font-black text-red-700 uppercase tracking-widest mb-1">Service Type</p>
                                <h3 class="text-lg font-black text-gray-800 uppercase">{{ $appointment->service->Service_Name }}</h3>
                                @if($appointment->service->Description)
                                    <p class="mt-2 text-[11px] font-bold text-gray-600 uppercase leading-relaxed">{{ $appointment->service->Description }}</p>
                                @endif
                            </div>
                        </section>

                        {{-- Special Notes --}}
                        @if($appointment->Special_Notes)
                        <section>
                            <div class="flex items-center gap-3 mb-4">
                                <span class="text-gray-400 font-black uppercase text-xs tracking-widest">Additional Notes</span>
                            </div>
                            <div class="p-6 border-2 border-dashed border-gray-200 rounded-[2rem]">
                                <p class="text-[11px] font-bold text-gray-600 uppercase italic">"{{ $appointment->Special_Notes }}"</p>
                            </div>
                        </section>
                        @endif
                    </div>
                    
                    {{-- Right Side: Schedule & Actions --}}
                    <div class="lg:w-1/2">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="w-8 h-8 bg-red-700 text-white rounded-full flex items-center justify-center text-xs font-black">3</span>
                            <span class="text-red-700 font-black uppercase text-xs tracking-widest">Schedule Details</span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="p-6 border-2 border-gray-100 rounded-3xl flex flex-col justify-center">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Appointment Date</p>
                                <p class="text-sm font-black text-gray-800 uppercase">{{ $appointment->Date->format('M d, Y') }}</p>
                                <p class="text-[9px] font-bold text-gray-500 uppercase">{{ $appointment->Date->format('l') }}</p>
                            </div>
                            <div class="p-6 border-2 border-gray-100 rounded-3xl flex flex-col justify-center">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Time Slot</p>
                                <p class="text-sm font-black text-gray-800 uppercase">{{ date('g:i A', strtotime($appointment->Time)) }}</p>
                                <p class="text-[9px] font-bold text-gray-500 uppercase">Sharp</p>
                            </div>
                        </div>

                        <div class="p-6 border-2 border-gray-100 rounded-3xl mb-8">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Location</p>
                            <p class="text-[11px] font-black text-gray-800 uppercase tracking-wider">{{ $appointment->Location }}</p>
                        </div>

                            @if($appointment->Status == 'Pending')
                                <form method="POST" action="{{ route('appointments.cancel', $appointment->Appointment_ID) }}" 
                                      onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                    @csrf
                                    <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white font-black py-5 rounded-2xl shadow-lg transition-all active:scale-95 uppercase tracking-widest text-[10px]">
                                        Cancel Appointment
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('appointments.index') }}" 
                               class="flex items-center justify-center w-full py-5 border-2 border-gray-200 text-gray-500 hover:text-black hover:border-black rounded-2xl font-black uppercase text-[10px] tracking-widest transition-all">
                                ← Back to My List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer Timestamps --}}
        <div class="mt-8 flex flex-col md:flex-row justify-between items-center px-8 text-[9px] font-black uppercase tracking-[0.2em] text-gray-400">
            <p>Created: {{ $appointment->created_at->format('M d, Y g:i A') }}</p>
            @if($appointment->updated_at != $appointment->created_at)
                <p>Modified: {{ $appointment->updated_at->format('M d, Y g:i A') }}</p>
            @endif
        </div>
    </div>
</x-dashboardheader-layout>