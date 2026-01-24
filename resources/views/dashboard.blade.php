<x-dashboardheader-layout>
    @section('title', 'Dashboard')

    <div class="min-h-screen py-8 bg-transparent">
        <div class="max-w-5xl mx-auto px-4">
            
            {{-- Verification Alert Banner --}}
            @if(Auth::user()->Verification_Status_ID != 2)
                <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg shadow">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">
                                @if(Auth::user()->Verification_Status_ID == 1)
                                    Account Verification Pending
                                @elseif(Auth::user()->Verification_Status_ID == 3)
                                    Account Verification Required
                                @else
                                    Account Not Verified
                                @endif
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>
                                    @if(Auth::user()->Verification_Status_ID == 1)
                                        Your account is currently under review. Dashboard features are temporarily disabled until verification is complete.
                                    @elseif(Auth::user()->Verification_Status_ID == 3)
                                        Your account is not yet approved. Please submit your identification for review to access dashboard features.
                                        <a href="{{ route('verify.reverify') }}" class="font-bold underline hover:text-yellow-900">Verify Now</a>
                                    @else
                                        Please complete account verification to access all dashboard features.
                                        <a href="{{ route('verify.reverify') }}" class="font-bold underline hover:text-yellow-900">Verify Now</a>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Session Messages --}}
            @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg shadow">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
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
                            @if(Auth::user()->Verification_Status_ID == 2)
                                You can book appointments, check your verification status, and access your pet's certificates, all in one dashboard.
                            @else
                                Complete your account verification to book appointments, manage pets, and access certificates.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- Action Buttons: Side by Side (Responsive Grid) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                {{-- Card 1: Book --}}
                @if(Auth::user()->Verification_Status_ID == 2)
                    <a href="{{ route('appointments.create') }}" class="group bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:border-red-300 transition-all duration-300">
                        <div class="text-3xl mb-4 group-hover:scale-110 transition-transform duration-300">📅</div>
                        <h3 class="font-black uppercase text-xs tracking-widest text-gray-800 group-hover:text-red-700 transition-colors">Book Appointment</h3>
                        <p class="text-[11px] text-gray-400 mt-1 uppercase font-bold">Schedule a visit</p>
                    </a>
                @else
                    <div class="relative group bg-gray-50 p-6 rounded-xl border border-gray-300 shadow-sm cursor-not-allowed opacity-60">
                        <div class="absolute top-2 right-2">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="text-3xl mb-4 opacity-50">📅</div>
                        <h3 class="font-black uppercase text-xs tracking-widest text-gray-500">Book Appointment</h3>
                        <p class="text-[11px] text-gray-400 mt-1 uppercase font-bold">Verification Required</p>
                    </div>
                @endif

                {{-- Card 2: Pets --}}
                @if(Auth::user()->Verification_Status_ID == 2)
                    <a href="{{ route('pets.index') }}" class="group bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:border-red-300 transition-all duration-300">
                        <div class="text-3xl mb-4 group-hover:scale-110 transition-transform duration-300">🐾</div>
                        <h3 class="font-black uppercase text-xs tracking-widest text-gray-800 group-hover:text-red-700 transition-colors">Manage Pets</h3>
                        <p class="text-[11px] text-gray-400 mt-1 uppercase font-bold">Register your pets</p>
                    </a>
                @else
                    <div class="relative group bg-gray-50 p-6 rounded-xl border border-gray-300 shadow-sm cursor-not-allowed opacity-60">
                        <div class="absolute top-2 right-2">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="text-3xl mb-4 opacity-50">🐾</div>
                        <h3 class="font-black uppercase text-xs tracking-widest text-gray-500">Manage Pets</h3>
                        <p class="text-[11px] text-gray-400 mt-1 uppercase font-bold">Verification Required</p>
                    </div>
                @endif

                {{-- Card 3: Certificates --}}
                @if(Auth::user()->Verification_Status_ID == 2)
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
                @else
                    <div class="relative group bg-gray-50 p-6 rounded-xl border border-gray-300 shadow-sm cursor-not-allowed opacity-60">
                        <div class="absolute top-2 right-2">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="text-3xl mb-4 opacity-50">📜</div>
                        <h3 class="font-black uppercase text-xs tracking-widest text-gray-500">Certificates</h3>
                        <p class="text-[11px] text-gray-400 mt-1 uppercase font-bold">Verification Required</p>
                    </div>
                @endif

                {{-- Card 4: View All --}}
                @if(Auth::user()->Verification_Status_ID == 2)
                    <a href="{{ route('appointments.index') }}" class="group bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:border-red-300 transition-all duration-300">
                        <div class="text-3xl mb-4 group-hover:scale-110 transition-transform duration-300">📂</div>
                        <h3 class="font-black uppercase text-xs tracking-widest text-gray-800 group-hover:text-red-700 transition-colors">History</h3>
                        <p class="text-[11px] text-gray-400 mt-1 uppercase font-bold">All Appointments</p>
                    </a>
                @else
                    <div class="relative group bg-gray-50 p-6 rounded-xl border border-gray-300 shadow-sm cursor-not-allowed opacity-60">
                        <div class="absolute top-2 right-2">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="text-3xl mb-4 opacity-50">📂</div>
                        <h3 class="font-black uppercase text-xs tracking-widest text-gray-500">History</h3>
                        <p class="text-[11px] text-gray-400 mt-1 uppercase font-bold">Verification Required</p>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-dashboardheader-layout>