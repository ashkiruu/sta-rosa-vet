<x-dashboardheader-layout>
    <div class="min-h-screen py-10 px-4 sm:px-6">
        {{-- Breadcrumbs inspired by Appointment Design --}}
        <div class="max-w-7xl mx-auto text-black text-xs py-4 px-2 uppercase font-black tracking-widest mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-red-700 transition-colors">Dashboard</a> 
            <span class="mx-2">/</span>
            <span class="font-black uppercase tracking-widest text-red-700">My Pets</span>
        </div>

        <div class="max-w-7xl mx-auto">
            {{-- Main Flattened Landscape Container --}}
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden">
                
                {{-- Toolbar Section --}}
                <div class="p-8 md:p-10 border-b border-gray-50 flex flex-col lg:flex-row justify-between items-center gap-6">
                    <div class="flex items-center">
                        <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter border-l-8 border-red-700 pl-4">
                            {{ Auth::user()->First_Name }}'s Pets
                        </h3>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row items-center w-full lg:w-auto gap-4">
                        {{-- Functional Search Bar --}}
                        <div class="relative w-full sm:w-80 group">
                            <input type="text" id="petSearch" placeholder="SEARCH PET NAME..." 
                                class="w-full pl-5 pr-12 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:ring-0 focus:border-red-700 focus:bg-white text-xs font-bold uppercase tracking-widest transition-all">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-red-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <a href="{{ route('pets.create') }}" 
                            class="w-full sm:w-auto bg-red-700 text-white px-8 py-4 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-red-800 transition-all shadow-lg shadow-red-700/20 active:scale-95 text-center">
                            + Register New Pet
                        </a>
                    </div>
                </div>

                {{-- Pet Cards List --}}
                <div class="p-6 md:p-10 bg-white">
                    @if(session('success'))
                        <div class="mb-8 bg-green-50 border-l-4 border-green-500 text-green-700 p-5 rounded-r-2xl font-bold text-sm uppercase tracking-wide shadow-sm">
                            <span class="mr-2">✅</span> {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-8 bg-red-50 border-l-4 border-red-600 text-red-700 p-5 rounded-r-2xl font-bold text-sm uppercase tracking-wide shadow-sm">
                            <span class="mr-2">⚠️</span> {{ session('error') }}
                        </div>
                    @endif

                    <div id="petGrid" class="space-y-4">
                        @forelse($pets as $pet)
                            {{-- Expansive Rectangle Pet Card --}}
                            <div class="pet-card group bg-white rounded-3xl border-2 border-gray-100 p-5 md:p-6 flex flex-col md:flex-row items-center justify-between hover:border-red-600 hover:shadow-2xl hover:shadow-red-700/5 transition-all duration-300">
                                
                                {{-- Left: Identity --}}
                                <div class="flex items-center w-full md:w-2/5 space-x-6">
                                    <div class="flex-shrink-0 w-20 h-20 rounded-2xl bg-gray-50 border-2 border-gray-100 flex items-center justify-center text-4xl group-hover:bg-red-50 group-hover:border-red-200 transition-colors">
                                        @if($pet->Species_ID == 1) 🐕 @elseif($pet->Species_ID == 2) 🐈 @else 🐾 @endif
                                    </div>
                                    <div class="truncate">
                                        <h4 class="pet-name text-2xl font-black text-gray-900 uppercase tracking-tighter leading-none group-hover:text-red-700 transition-colors">
                                            {{ $pet->Pet_Name }}
                                        </h4>
                                        <p class="pet-species text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mt-1">
                                            {{ $pet->species->Species_Name ?? 'General Species' }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Center: Status Badges (Hidden on mobile for cleaner look, or stacked) --}}
                                <div class="hidden lg:flex flex-1 items-center justify-center gap-8 px-4">
                                    <div class="text-center">
                                        <span class="block text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Sex</span>
                                        <span class="font-bold text-gray-700 uppercase text-xs">{{ $pet->Sex }}</span>
                                    </div>
                                    <div class="h-8 w-px bg-gray-100"></div>
                                    <div class="text-center">
                                        <span class="block text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Age</span>
                                        <span class="font-bold text-gray-700 uppercase text-xs">{{ $pet->Age }} Mos</span>
                                    </div>
                                    <div class="h-8 w-px bg-gray-100"></div>
                                    <div class="text-center">
                                        <span class="block text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Status</span>
                                        <span class="px-3 py-1 rounded-full text-[9px] font-black bg-red-50 text-red-700 uppercase tracking-tighter">
                                            {{ $pet->Reproductive_Status }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Right: Action Buttons --}}
                                <div class="flex items-center justify-end w-full md:w-auto gap-3 mt-6 md:mt-0 md:ml-auto md:pl-20">                                    <a href="{{ route('pets.show', $pet->Pet_ID) }}" 
                                        class="flex-1 md:flex-none text-center bg-gray-900 text-white px-6 py-3.5 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-gray-800 transition-all active:scale-95 shadow-lg shadow-gray-900/10">
                                        View Details
                                    </a>
                                    <form method="POST" action="{{ route('pets.destroy', $pet->Pet_ID) }}" class="flex-1 md:flex-none" onsubmit="return confirm('ARE YOU SURE YOU WANT TO REMOVE THIS PET RECORD?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full bg-white border-2 border-red-700 text-red-700 px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-red-700 hover:text-white transition-all active:scale-95">
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="py-24 text-center bg-gray-50/50 rounded-[2rem] border-4 border-dashed border-gray-100">
                                <div class="text-7xl mb-6 opacity-20">🐾</div>
                                <h3 class="text-xl font-black text-gray-400 uppercase tracking-widest">No Pets Registered Yet</h3>
                                <p class="text-gray-400 mb-8 font-bold text-xs uppercase tracking-widest">Start by adding your first pet to the system</p>
                                <a href="{{ route('pets.create') }}" class="bg-red-700 text-white px-10 py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl shadow-red-700/20">
                                    Get Started
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Client-side Search Functionality --}}
    <script>
        document.getElementById('petSearch').addEventListener('keyup', function() {
            let filter = this.value.toUpperCase();
            let cards = document.querySelectorAll('.pet-card');

            cards.forEach(card => {
                let name = card.querySelector('.pet-name').textContent.toUpperCase();
                let species = card.querySelector('.pet-species').textContent.toUpperCase();
                
                if (name.indexOf(filter) > -1 || species.indexOf(filter) > -1) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            });
        });
    </script>
</x-dashboardheader-layout>