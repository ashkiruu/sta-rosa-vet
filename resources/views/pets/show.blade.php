<x-dashboardheader-layout>
    <div class="min-h-screen bg-gray-50/50 pb-20">
        <div class="container mx-auto pt-8 px-4 max-w-5xl">
            
            {{-- Breadcrumb: Refined and Minimal --}}
            <nav class="flex items-center space-x-2 mb-8 text-[10px] uppercase font-black tracking-[0.2em]">
                <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-red-700 transition">Dashboard</a>
                <span class="text-gray-300">/</span>
                <a href="{{ route('pets.index') }}" class="text-gray-400 hover:text-red-700 transition">My Pets</a>
                <span class="text-gray-300">/</span>
                <span class="text-red-700">{{ $pet->Pet_Name }}</span>
            </nav>

            {{-- Main Profile Layout --}}
            <div class="bg-white rounded-[3rem] border-2 border-gray-100 shadow-2xl shadow-gray-200/50 overflow-hidden">
                
                {{-- Top Section: Large Identity Header --}}
                <div class="relative flex flex-col md:flex-row items-center p-8 md:p-12 gap-10 bg-gradient-to-br from-white to-gray-50 border-b border-gray-100">
                    
                    {{-- Large Avatar Box --}}
                    <div class="w-40 h-40 md:w-56 md:h-56 rounded-[2.5rem] bg-white border-2 border-gray-100 shadow-inner flex items-center justify-center text-7xl md:text-8xl shrink-0">
                        @if($pet->Species_ID == 1) 🐕 @elseif($pet->Species_ID == 2) 🐈 @else 🐾 @endif
                    </div>

                    {{-- Name & Quick Stats --}}
                    <div class="flex-1 text-center md:text-left">
                        <div class="flex flex-col md:flex-row md:items-center gap-4 mb-4">
                            <h1 class="text-3xl md:text-4xl font-black text-gray-900 uppercase tracking-tighter leading-none">
                                {{ $pet->Pet_Name }}
                            </h1>
                            <span class="inline-block px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest self-center md:self-auto
                                {{ $pet->Reproductive_Status == 'Intact' ? 'bg-red-50 text-red-700 border border-red-100' : 'bg-gray-900 text-white' }}">
                                {{ $pet->Reproductive_Status }}
                            </span>
                        </div>
                        <p class="text-[11px] font-black text-gray-400 uppercase tracking-[0.3em]">
                            {{ $pet->species->Species_Name ?? 'Unknown' }} • {{ $pet->Breed ?: 'Mixed Breed' }}
                        </p>
                        
                        {{-- Registration Date Badge --}}
                        <div class="mt-6 inline-flex items-center px-4 py-2 bg-gray-50 rounded-xl border border-gray-100 text-[10px] font-bold text-gray-500 uppercase tracking-tight">
                            <span class="mr-2">📅 Registered</span>
                            {{ $pet->Registration_Date ? \Carbon\Carbon::parse($pet->Registration_Date)->format('M d, Y') : 'N/A' }}
                        </div>
                    </div>
                </div>

                {{-- Information Grid: Using the Spacious Style --}}
                <div class="p-8 md:p-12 grid grid-cols-1 md:grid-cols-3 gap-8">
                    
                    {{-- Sex Detail --}}
                    <div class="flex items-center space-x-5 p-6 rounded-3xl border-2 border-gray-50 hover:border-red-100 transition-colors group">
                        <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-xl group-hover:bg-red-50 transition-colors">
                             {{ $pet->Sex == 'Male' ? '♂' : '♀' }}
                        </div>
                        <div>
                            <span class="block text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Sex</span>
                            <span class="font-black text-gray-900 uppercase text-lg">{{ $pet->Sex }}</span>
                        </div>
                    </div>

                    {{-- Age Detail --}}
                    <div class="flex items-center space-x-5 p-6 rounded-3xl border-2 border-gray-50 hover:border-red-100 transition-colors group">
                        <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-xl group-hover:bg-red-50 transition-colors">
                             🎂
                        </div>
                        <div>
                            <span class="block text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Age</span>
                            <span class="font-black text-gray-900 uppercase text-lg">
                                @if($pet->Age >= 12)
                                    {{ floor($pet->Age / 12) }}Y {{ $pet->Age % 12 }}M
                                @else
                                    {{ $pet->Age }} Mos
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Birth Date --}}
                    <div class="flex items-center space-x-5 p-6 rounded-3xl border-2 border-gray-50 hover:border-red-100 transition-colors group">
                        <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-xl group-hover:bg-red-50 transition-colors">
                             🌟
                        </div>
                        <div>
                            <span class="block text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Est. Birthday</span>
                            <span class="font-black text-gray-900 uppercase text-lg">
                                {{ $pet->Date_of_Birth ? \Carbon\Carbon::parse($pet->Date_of_Birth)->format('M d, Y') : 'Unknown' }}
                            </span>
                        </div>
                    </div>

                    {{-- Color Detail --}}
                    <div class="flex items-center space-x-5 p-6 rounded-3xl border-2 border-gray-50 hover:border-red-100 transition-colors group">
                        <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-xl group-hover:bg-red-50 transition-colors">
                             🎨
                        </div>
                        <div>
                            <span class="block text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Color / Markings</span>
                            <span class="font-black text-gray-900 uppercase text-lg">{{ $pet->Color ?: 'Not specified' }}</span>
                        </div>
                    </div>

                    {{-- Breed Detail --}}
                    <div class="flex items-center space-x-5 p-6 rounded-3xl border-2 border-gray-50 hover:border-red-100 transition-colors group">
                        <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-xl group-hover:bg-red-50 transition-colors">
                             🏷️
                        </div>
                        <div>
                            <span class="block text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Breed</span>
                            <span class="font-black text-gray-900 uppercase text-lg">{{ $pet->Breed ?: 'Mixed / Unknown' }}</span>
                        </div>
                    </div>

                    {{-- Species Detail --}}
                    <div class="flex items-center space-x-5 p-6 rounded-3xl border-2 border-gray-50 hover:border-red-100 transition-colors group">
                        <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-xl group-hover:bg-red-50 transition-colors">
                             @if($pet->Species_ID == 1) 🐕 @elseif($pet->Species_ID == 2) 🐈 @else 🐾 @endif
                        </div>
                        <div>
                            <span class="block text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Species</span>
                            <span class="font-black text-gray-900 uppercase text-lg">{{ $pet->species->Species_Name ?? 'Unknown' }}</span>
                        </div>
                    </div>

                </div>

                {{-- Medical History: Full Width Section --}}
                @if($pet->Medical_History)
                <div class="px-8 md:px-12 pb-8">
                    <div class="bg-red-50/30 border-2 border-red-100 rounded-[2rem] p-8">
                        <h3 class="text-[10px] font-black text-red-700 uppercase tracking-widest mb-4 flex items-center">
                            <span class="mr-2 text-base">📋</span> Medical History & Notes
                        </h3>
                        <p class="text-gray-700 leading-relaxed font-medium">
                            {{ $pet->Medical_History }}
                        </p>
                    </div>
                </div>
                @endif

                {{-- Actions: Spacious Bottom Bar --}}
                <div class="bg-gray-50/50 p-8 md:p-12 border-t border-gray-100 flex flex-col md:flex-row items-center gap-4">
                    
                    <a href="{{ route('pets.index') }}" 
                        class="w-full md:w-auto text-center border-2 border-gray-200 text-gray-500 px-10 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-white hover:text-gray-800 transition-all active:scale-95">
                        ← Back to List
                    </a>

                    {{-- Push Book Appointment to the right --}}
                    <div class="md:ml-auto w-full md:w-auto flex flex-col md:flex-row gap-4">
                        <form method="POST" action="{{ route('pets.destroy', $pet->Pet_ID) }}" 
                             onsubmit="return confirm('Permanently remove {{ $pet->Pet_Name }}?');">
                            @csrf @method('DELETE')
                            <button type="submit" 
                                class="w-full md:w-auto border-2 border-red-600 text-red-600 hover:bg-red-50/50 text-[10px] font-black uppercase tracking-widest px-8 py-4 rounded-2xl transition-all active:scale-95">
                                Remove Record
                            </button>
                        </form>

                        <a href="{{ route('appointments.create') }}" 
                            class="w-full md:w-auto text-center bg-red-700 text-white px-12 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-red-800 transition-all active:scale-95 shadow-xl shadow-red-700/20">
                            Book Appointment
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-dashboardheader-layout>