<x-dashboardheader-layout>
    <div class="min-h-screen py-10 px-4 sm:px-6">
        {{-- Breadcrumbs --}}
        <div class="max-w-7xl mx-auto text-black text-xs py-4 px-2 uppercase font-black tracking-widest mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-red-700 transition-colors">Dashboard</a> 
            <span class="mx-2 text-gray-300">/</span>
            <span class="text-red-700">My Certificates</span>
        </div>

        <div class="max-w-7xl mx-auto">
            {{-- Main Container --}}
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden">
                
                {{-- Toolbar/Header Section --}}
                <div class="p-8 md:p-10 border-b border-gray-50 flex flex-col lg:flex-row justify-between items-center gap-6">
                    <div class="flex items-center">
                        <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter border-l-8 border-red-700 pl-4">
                            Official Certificates
                        </h3>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row items-center w-full lg:w-auto gap-4">
                        {{-- Search Bar --}}
                        <div class="relative w-full sm:w-80 group">
                            <input type="text" id="certSearch" placeholder="SEARCH PET OR CERT #..." 
                                class="w-full pl-5 pr-12 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:ring-0 focus:border-red-700 focus:bg-white text-xs font-bold uppercase tracking-widest transition-all">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Content Section --}}
                <div class="p-6 md:p-10 bg-white">
                    @if(session('success'))
                        <div class="mb-8 bg-green-50 border-l-4 border-green-500 text-green-700 p-5 rounded-r-2xl font-bold text-sm uppercase tracking-wide">
                            <span class="mr-2">✅</span> {{ session('success') }}
                        </div>
                    @endif

                    <div id="certGrid" class="space-y-4">
                        @forelse($certificates as $certificate)
                            {{-- Landscape Certificate Card --}}
                            <div class="cert-card group bg-white rounded-3xl border-2 border-gray-100 p-5 md:p-6 flex flex-col md:flex-row items-center justify-between hover:border-red-600 hover:shadow-2xl hover:shadow-red-700/5 transition-all duration-300">
                                
                                {{-- Left: Identity & Icon --}}
                                <div class="flex items-center w-full md:w-1/3 space-x-6">
                                    <div class="flex-shrink-0 w-20 h-20 rounded-2xl bg-gray-50 border-2 border-gray-100 flex items-center justify-center text-4xl group-hover:bg-red-50 group-hover:border-red-200 transition-colors">
                                        📜
                                    </div>
                                    <div class="truncate">
                                        <h4 class="cert-pet-name text-2xl font-black text-gray-900 uppercase tracking-tighter leading-none group-hover:text-red-700 transition-colors">
                                            {{ $certificate['pet_name'] }}
                                        </h4>
                                        <p class="cert-number text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mt-1">
                                            ID: {{ $certificate['certificate_number'] }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Center: Details --}}
                                <div class="hidden lg:flex flex-1 items-center justify-center gap-8 px-4">
                                    <div class="text-center">
                                        <span class="block text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Service</span>
                                        <span class="font-bold text-gray-700 uppercase text-xs">{{ $certificate['service_type'] }}</span>
                                    </div>
                                    <div class="h-8 w-px bg-gray-100"></div>
                                    <div class="text-center">
                                        <span class="block text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Date Issued</span>
                                        <span class="font-bold text-gray-700 uppercase text-xs">{{ date('M d, Y', strtotime($certificate['approved_at'])) }}</span>
                                    </div>
                                    <div class="h-8 w-px bg-gray-100"></div>
                                    <div class="text-center">
                                        <span class="block text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Status</span>
                                        <span class="px-3 py-1 rounded-full text-[9px] font-black bg-green-50 text-green-700 uppercase tracking-tighter">
                                            Verified
                                        </span>
                                    </div>
                                </div>

                                {{-- Right: Action --}}
                                <div class="flex items-center justify-end w-full md:w-auto gap-3 mt-6 md:mt-0">
                                    <a href="{{ route('certificates.download', $certificate['id']) }}" target="_blank"
                                        class="w-full md:w-auto text-center bg-red-700 text-white px-8 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-red-800 transition-all shadow-lg shadow-red-700/10 active:scale-95">
                                        Download PDF
                                    </a>
                                </div>
                            </div>
                        @empty
                            {{-- Consistent Empty State --}}
                            <div class="py-24 text-center bg-gray-50/50 rounded-[2rem] border-4 border-dashed border-gray-100">
                                <div class="text-7xl mb-6 opacity-20">📜</div>
                                <h3 class="text-xl font-black text-gray-400 uppercase tracking-widest">No Certificates Yet</h3>
                                <p class="text-gray-400 mb-8 font-bold text-xs uppercase tracking-widest">Complete an appointment to generate certificates</p>
                                <a href="{{ route('appointments.create') }}" class="bg-gray-900 text-white px-10 py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl">
                                    Book Appointment
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('certSearch').addEventListener('keyup', function() {
            let filter = this.value.toUpperCase();
            let cards = document.querySelectorAll('.cert-card');
            cards.forEach(card => {
                let name = card.querySelector('.cert-pet-name').textContent.toUpperCase();
                let num = card.querySelector('.cert-number').textContent.toUpperCase();
                card.style.display = (name.includes(filter) || num.includes(filter)) ? "" : "none";
            });
        });
    </script>
</x-dashboardheader-layout>