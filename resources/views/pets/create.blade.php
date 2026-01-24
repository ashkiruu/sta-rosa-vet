<x-dashboardheader-layout>
    <div class="max-w-5xl mx-auto px-4 py-10">
        {{-- Breadcrumbs inspired by Appointment Design --}}
        <div class="text-black text-[10px] py-4 px-2 uppercase font-black tracking-[0.2em] mb-4">
            <a href="{{ route('dashboard') }}" class="hover:text-red-700 transition-colors">Dashboard</a> 
            <span class="mx-2 text-gray-300">/</span>
            <a href="{{ route('pets.index') }}" class="hover:text-red-700 transition-colors">My Pets</a> 
            <span class="mx-2 text-gray-300">/</span>
            <span class="font-black uppercase tracking-widest text-red-700">Add New Pet</span>
        </div>
        
        {{-- Main Flattened Card --}}
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden">
            <div class="p-8 md:p-12">
                {{-- Header Section --}}
                <div class="flex flex-col items-center justify-center mb-10">
                    <h2 class="text-4xl font-black text-gray-900 uppercase tracking-tight">Register Pet</h2>
                    <p class="text-red-700 font-bold uppercase text-xs tracking-[0.2em] mt-2">New Pet Profile</p>
                    
                    {{-- Decorative Divider --}}
                    <div class="w-24 h-1 bg-red-700 mt-6 rounded-full"></div>
                </div>

                {{-- Error Handling --}}
                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-600 text-red-700 p-4 rounded-xl mb-8">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span class="font-black uppercase text-xs tracking-widest">Please correct the following:</span>
                        </div>
                        <ul class="list-disc list-inside text-sm font-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('pets.store') }}" enctype="multipart/form-data" class="space-y-8">
                    @csrf

                    {{-- Form Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-6">
                        
                        {{-- Pet Name --}}
                        <div class="md:col-span-2">
                            <label class="block text-red-700 font-black uppercase text-[10px] tracking-widest mb-2">Pet Name</label>
                            <input 
                                type="text" 
                                name="Pet_Name" 
                                class="block w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50 py-3 px-4 font-medium" 
                                placeholder="e.g. Batumbakal"
                                value="{{ old('Pet_Name') }}"
                                required
                            >
                        </div>

                        {{-- Sex --}}
                        <div>
                            <label class="block text-red-700 font-black uppercase text-[10px] tracking-widest mb-2">Sex</label>
                            <select name="Sex" id="sexSelect" class="block w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50 py-3 px-4 font-medium" required>
                                <option value="" disabled {{ old('Sex') ? '' : 'selected' }}>Select Sex</option>
                                <option value="Male" {{ old('Sex') == 'Male' ? 'selected' : '' }}>Male (M)</option>
                                <option value="Female" {{ old('Sex') == 'Female' ? 'selected' : '' }}>Female (F)</option>
                            </select>
                        </div>

                        {{-- Age --}}
                        <div>
                            <label class="block text-red-700 font-black uppercase text-[10px] tracking-widest mb-2">Age (Months)</label>
                            <input 
                                type="number" 
                                name="Age" 
                                class="block w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50 py-3 px-4 font-medium" 
                                placeholder="23"
                                value="{{ old('Age') }}"
                                min="0"
                                required
                            >
                        </div>

                        {{-- Species --}}
                        <div>
                            <label class="block text-red-700 font-black uppercase text-[10px] tracking-widest mb-2">Species</label>
                            <select name="Species_ID" id="speciesSelect" class="block w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50 py-3 px-4 font-medium" required>
                                <option value="" disabled {{ old('Species_ID') ? '' : 'selected' }}>Select Species</option>
                                @foreach($species as $sp)
                                    <option value="{{ $sp->Species_ID }}" {{ old('Species_ID') == $sp->Species_ID ? 'selected' : '' }}>
                                        {{ $sp->Species_Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Breed --}}
                        <div>
                            <label class="block text-gray-400 font-black uppercase text-[10px] tracking-widest mb-2">Breed (Optional)</label>
                            <input 
                                type="text" 
                                name="Breed" 
                                class="block w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50 py-3 px-4 font-medium" 
                                placeholder="e.g. Labrador, Mixed"
                                value="{{ old('Breed') }}"
                            >
                        </div>

                        {{-- Conditional: Other Species --}}
                        <div class="md:col-span-3 transition-all duration-300" id="otherSpeciesDiv" style="display: none;">
                            <div class="bg-amber-50 border border-amber-100 p-4 rounded-2xl">
                                <label class="block text-amber-700 font-black uppercase text-[10px] tracking-widest mb-2">Specify Other Species</label>
                                <input 
                                    type="text" 
                                    name="other_species" 
                                    class="block w-full border-amber-200 focus:border-amber-500 focus:ring-amber-500 rounded-xl bg-white py-3 px-4 font-medium" 
                                    placeholder="Enter species name"
                                    value="{{ old('other_species') }}"
                                >
                            </div>
                        </div>

                        {{-- Reproductive Status --}}
                        <div class="md:col-span-3">
                            <label class="block text-red-700 font-black uppercase text-[10px] tracking-widest mb-2">Reproductive Status</label>
                            <select name="Reproductive_Status" id="reproductiveStatus" class="block w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50 py-3 px-4 font-medium" required>
                                <option value="" disabled {{ old('Reproductive_Status') ? '' : 'selected' }}>Select Status</option>
                                <option value="Intact" {{ old('Reproductive_Status') == 'Intact' ? 'selected' : '' }}>Intact (Not neutered/spayed)</option>
                                <option value="Neutered" id="neuteredOption" {{ old('Reproductive_Status') == 'Neutered' ? 'selected' : '' }} style="display: none;">Neutered</option>
                                <option value="Spayed" id="spayedOption" {{ old('Reproductive_Status') == 'Spayed' ? 'selected' : '' }} style="display: none;">Spayed</option>
                                <option value="Unknown" {{ old('Reproductive_Status') == 'Unknown' ? 'selected' : '' }}>Unknown</option> 
                            </select>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight mt-3 flex items-center" id="reproductiveHint">
                                <i class="fas fa-info-circle mr-2 text-red-500"></i> Select the sex first to see status options
                            </p>
                        </div>
                    </div>

                    {{-- Action Footer --}}
                    <div class="pt-10 border-t border-gray-100">
                        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                            {{-- Back Button --}}
                            <a href="{{ route('pets.index') }}" 
                                class="w-full md:w-auto bg-gray-500 hover:bg-gray-600 text-white font-black py-4 px-12 rounded-2xl shadow-lg transition-all active:scale-95 uppercase tracking-widest text-sm text-center">
                                Back
                            </a>

                            {{-- Submit Button --}}
                            <button type="submit" 
                                class="w-full md:w-64 bg-red-700 hover:bg-red-800 text-white font-black py-4 rounded-2xl shadow-lg transition-all active:scale-95 uppercase tracking-widest text-sm">
                                Register Pet
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const speciesSelect = document.getElementById('speciesSelect');
        const otherSpeciesDiv = document.getElementById('otherSpeciesDiv');
        const sexSelect = document.getElementById('sexSelect');
        const reproductiveStatus = document.getElementById('reproductiveStatus');
        const neuteredOption = document.getElementById('neuteredOption');
        const spayedOption = document.getElementById('spayedOption');
        const reproductiveHint = document.getElementById('reproductiveHint');
        
        speciesSelect.addEventListener('change', function() {
            const selectedText = this.options[this.selectedIndex].text;
            otherSpeciesDiv.style.display = (selectedText === 'Other') ? 'block' : 'none';
        });

        if (speciesSelect.options[speciesSelect.selectedIndex]?.text === 'Other') {
            otherSpeciesDiv.style.display = 'block';
        }

        sexSelect.addEventListener('change', function() {
            const sex = this.value;
            reproductiveStatus.value = '';
            
            if (sex === 'Male') {
                neuteredOption.style.display = 'block';
                spayedOption.style.display = 'none';
                reproductiveHint.innerHTML = '<i class="fas fa-mars mr-2 text-blue-500"></i> Neutered = surgically sterilized male';
            } else if (sex === 'Female') {
                neuteredOption.style.display = 'none';
                spayedOption.style.display = 'block';
                reproductiveHint.innerHTML = '<i class="fas fa-venus mr-2 text-pink-500"></i> Spayed = surgically sterilized female';
            } else {
                neuteredOption.style.display = 'none';
                spayedOption.style.display = 'none';
                reproductiveHint.innerHTML = '<i class="fas fa-info-circle mr-2 text-red-500"></i> Select sex first to see options';
            }
        });

        // Load state
        const initialSex = sexSelect.value;
        if (initialSex === 'Male') {
            neuteredOption.style.display = 'block';
            reproductiveHint.innerHTML = '<i class="fas fa-mars mr-2 text-blue-500"></i> Neutered = surgically sterilized male';
        } else if (initialSex === 'Female') {
            spayedOption.style.display = 'block';
            reproductiveHint.innerHTML = '<i class="fas fa-venus mr-2 text-pink-500"></i> Spayed = surgically sterilized female';
        }
    </script>
</x-dashboardheader-layout>