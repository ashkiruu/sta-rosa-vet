<x-dashboardheader-layout>
    @section('title', 'Dashboard')

    <div class="min-h-screen py-8 bg-transparent">
            <div class="max-w-5xl mx-auto px-4">
            
            {{-- Header Card --}}
            <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-8 border border-gray-100">
                <div class="flex flex-col md:flex-row">
                    {{-- Left Brand Side --}}
                    <div class="bg-red-700 text-white p-8 md:w-1/3 flex flex-col justify-center">
                        <h1 class="text-xl font-black tracking-tighter uppercase">City of</h1>
                        <h2 class="text-3xl font-black tracking-tight uppercase leading-none mb-2">Santa Rosa</h2>
                        <div class="h-1 w-12 bg-yellow-400 mb-4"></div>
                        <p class="text-xs font-bold tracking-widest text-gray-300 uppercase">Veterinary Office</p>
                    </div>

                    {{-- Right Welcome Side --}}
                    <div class="p-8 flex-1 bg-white">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h2 class="text-2xl font-black text-gray-800 uppercase">Welcome, {{ Auth::user()->First_Name }}!</h2>
                                <p class="text-gray-500 text-sm mt-1">Batang City Vet Ako!</p>
                            </div>
                            
                            {{-- Status Badge --}}
                            <div class="text-right">
                                <p class="text-[10px] font-black uppercase text-gray-400 mb-1 tracking-widest">Account Status</p>
                                @if(Auth::user()->Verification_Status_ID == 2)
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-black uppercase tracking-tighter flex items-center gap-1">
                                        <span class="w-2 h-2 bg-green-500 rounded-full"></span> Verified Member
                                    </span>
                                @elseif(Auth::user()->Verification_Status_ID == 1)
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-black uppercase tracking-tighter">⏳ Pending Review</span>
                                @else
                                    <a href="{{ route('verify.reverify') }}" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-full text-xs font-black uppercase transition">⚠️ Verify Now</a>
                                @endif
                            </div>
                        </div>
                        <p class="text-gray-600 leading-relaxed">
                            You can book appointments, check your verification status, and access your pet's certificates, all in one dashboard.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Action Buttons: Side by Side (Responsive Grid) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                {{-- Card 1: Book --}}
                <a href="{{ route('appointments.create') }}" class="group bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:border-red-300 transition-all duration-300">
                    <div class="text-3xl mb-4 group-hover:scale-110 transition-transform duration-300">📅</div>
                    <h3 class="font-black uppercase text-xs tracking-widest text-gray-800 group-hover:text-red-700 transition-colors">Book Appointment</h3>
                    <p class="text-[11px] text-gray-400 mt-1 uppercase font-bold">Schedule a visit</p>
                </a>

                {{-- Card 2: Pets --}}
                <a href="{{ route('pets.index') }}" class="group bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:border-red-300 transition-all duration-300">
                    <div class="text-3xl mb-4 group-hover:scale-110 transition-transform duration-300">🐾</div>
                    <h3 class="font-black uppercase text-xs tracking-widest text-gray-800 group-hover:text-red-700 transition-colors">Manage Pets</h3>
                    <p class="text-[11px] text-gray-400 mt-1 uppercase font-bold">Register your pets</p>
                </a>

                {{-- Card 3: Certificates --}}
                <a href="{{ route('certificates.index') }}" class="relative group bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:border-red-300 transition-all duration-300">
                    @if(($certCount ?? 0) > 0)
                        <span class="absolute top-4 right-4 flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-red-600"></span>
                        </span>
                    @endif
                    <div class="text-3xl mb-4 group-hover:scale-110 transition-transform duration-300">📜</div>
                    <h3 class="font-black uppercase text-xs tracking-widest text-gray-800 group-hover:text-red-700 transition-colors">Certificates</h3>
                    <p class="text-[11px] text-gray-400 mt-1 uppercase font-bold">Vaccination Records</p>
                </a>

                {{-- Card 4: View All --}}
                <a href="{{ route('appointments.index') }}" class="group bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:border-red-300 transition-all duration-300">
                    <div class="text-3xl mb-4 group-hover:scale-110 transition-transform duration-300">📂</div>
                    <h3 class="font-black uppercase text-xs tracking-widest text-gray-800 group-hover:text-red-700 transition-colors">History</h3>
                    <p class="text-[11px] text-gray-400 mt-1 uppercase font-bold">All Appointments</p>
                </a>

            </div>
        </div>
    </div>
</x-dashboardheader-layout>