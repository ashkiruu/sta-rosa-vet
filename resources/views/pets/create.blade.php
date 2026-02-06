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

                {{-- Added onsubmit to trigger disable function --}}
                <form method="POST" action="{{ route('pets.store') }}" enctype="multipart/form-data" class="space-y-8" id="petForm" onsubmit="return validateAndSubmit()">
                    @csrf

                    {{-- Form Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-6">
                        
                        {{-- Pet Name --}}
                        <div class="md:col-span-2">
                            <label class="block text-red-700 font-black uppercase text-[10px] tracking-widest mb-2">Pet Name</label>
                            <input 
                                type="text" 
                                name="Pet_Name" 
                                id="petName"
                                class="block w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50 py-3 px-4 font-medium" 
                                placeholder="e.g. Batumbakal"
                                value="{{ old('Pet_Name') }}"
                                required
                                minlength="2"
                                maxlength="50"
                                pattern="^[A-Za-z][A-Za-z0-9\s\-'\.]*$"
                                title="Pet name must start with a letter and can contain letters, numbers, spaces, hyphens, apostrophes, and periods"
                            >
                            <p class="text-[10px] text-gray-400 mt-1" id="petNameError"></p>
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
                                type="text" 
                                name="Age" 
                                id="petAge"
                                inputmode="numeric"
                                class="block w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50 py-3 px-4 font-medium" 
                                placeholder="23"
                                value="{{ old('Age') }}"
                                required
                                pattern="^[0-9]{1,3}$"
                                title="Please enter a valid age in months (0-360)"
                            >
                            <p class="text-[10px] text-gray-400 mt-1" id="ageError"></p>
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

                        {{-- Color --}}
                        <div>
                            <label class="block text-red-700 font-black uppercase text-[10px] tracking-widest mb-2">Color / Markings</label>
                            <input 
                                type="text" 
                                name="Color" 
                                id="petColor"
                                class="block w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50 py-3 px-4 font-medium" 
                                placeholder="e.g. Brown, Black & White, Golden"
                                value="{{ old('Color') }}"
                                required
                                minlength="2"
                                maxlength="100"
                                pattern="^[A-Za-z][A-Za-z\s\,\&\-']*$"
                                title="Color must start with a letter and can contain letters, spaces, commas, ampersands, and hyphens"
                            >
                            <p class="text-[10px] text-gray-400 mt-1" id="colorError"></p>
                        </div>

                        {{-- Breed --}}
                        <div>
                            <label class="block text-gray-400 font-black uppercase text-[10px] tracking-widest mb-2">Breed (Optional)</label>
                            <input 
                                type="text" 
                                name="Breed" 
                                id="petBreed"
                                class="block w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50 py-3 px-4 font-medium" 
                                placeholder="e.g. Labrador, Mixed, Aspin"
                                value="{{ old('Breed') }}"
                                maxlength="100"
                                pattern="^[A-Za-z][A-Za-z\s\-'\/]*$"
                                title="Breed must start with a letter and can contain letters, spaces, hyphens, apostrophes, and slashes"
                            >
                            <p class="text-[10px] text-gray-400 mt-1" id="breedError"></p>
                        </div>

                        {{-- Conditional: Other Species --}}
                        <div class="md:col-span-3 transition-all duration-300" id="otherSpeciesDiv" style="display: none;">
                            <div class="bg-amber-50 border border-amber-100 p-4 rounded-2xl">
                                <label class="block text-amber-700 font-black uppercase text-[10px] tracking-widest mb-2">Specify Other Species</label>
                                <input 
                                    type="text" 
                                    name="other_species" 
                                    id="otherSpecies"
                                    class="block w-full border-amber-200 focus:border-amber-500 focus:ring-amber-500 rounded-xl bg-white py-3 px-4 font-medium" 
                                    placeholder="Enter species name"
                                    value="{{ old('other_species') }}"
                                    maxlength="50"
                                    pattern="^[A-Za-z][A-Za-z\s\-]*$"
                                    title="Species name must start with a letter and can contain only letters, spaces, and hyphens"
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

                        {{-- Medical History (Optional) --}}
                        <div class="md:col-span-3">
                            <label class="block text-gray-400 font-black uppercase text-[10px] tracking-widest mb-2">Medical History / Notes (Optional)</label>
                            <textarea 
                                name="medical_history" 
                                id="medicalHistory"
                                rows="3"
                                class="block w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50 py-3 px-4 font-medium" 
                                placeholder="Any known allergies, past illnesses, or special conditions..."
                                maxlength="1000"
                            >{{ old('medical_history') }}</textarea>
                            <p class="text-[10px] text-gray-400 mt-1"><span id="medicalHistoryCount">0</span>/1000 characters</p>
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

                            {{-- Submit Button (ID added for targeting) --}}
                            <button type="submit" id="petSubmitBtn"
                                class="w-full md:w-64 bg-red-700 hover:bg-red-800 text-white font-black py-4 rounded-2xl shadow-lg transition-all active:scale-95 uppercase tracking-widest text-sm disabled:opacity-50 disabled:cursor-not-allowed">
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
        const otherSpeciesInput = document.getElementById('otherSpecies');
        const sexSelect = document.getElementById('sexSelect');
        const reproductiveStatus = document.getElementById('reproductiveStatus');
        const neuteredOption = document.getElementById('neuteredOption');
        const spayedOption = document.getElementById('spayedOption');
        const reproductiveHint = document.getElementById('reproductiveHint');
        
        // Input elements
        const petNameInput = document.getElementById('petName');
        const petAgeInput = document.getElementById('petAge');
        const petColorInput = document.getElementById('petColor');
        const petBreedInput = document.getElementById('petBreed');
        const medicalHistoryInput = document.getElementById('medicalHistory');

        // =====================
        // INPUT VALIDATION FUNCTIONS
        // =====================

        // Pet Name: Letters, numbers, spaces, hyphens, apostrophes, periods only
        function validatePetName(input) {
            // Remove any characters that aren't allowed
            let value = input.value;
            // Allow letters, numbers, spaces, hyphens, apostrophes, and periods
            value = value.replace(/[^A-Za-z0-9\s\-'\.]/g, '');
            // Prevent starting with non-letter
            if (value.length > 0 && !/^[A-Za-z]/.test(value)) {
                value = value.replace(/^[^A-Za-z]+/, '');
            }
            input.value = value;
            
            const errorEl = document.getElementById('petNameError');
            if (value.length > 0 && value.length < 2) {
                errorEl.textContent = 'Pet name must be at least 2 characters';
                errorEl.classList.add('text-red-500');
            } else {
                errorEl.textContent = '';
                errorEl.classList.remove('text-red-500');
            }
        }

        // Age: Numbers only (0-360 months)
        function validateAge(input) {
            // Remove any non-numeric characters
            let value = input.value.replace(/[^0-9]/g, '');
            
            // Limit to 3 digits and max 360
            if (value.length > 3) {
                value = value.slice(0, 3);
            }
            
            const numValue = parseInt(value, 10);
            if (numValue > 360) {
                value = '360';
            }
            
            input.value = value;
            
            const errorEl = document.getElementById('ageError');
            if (value.length > 0 && (numValue < 0 || numValue > 360)) {
                errorEl.textContent = 'Age must be between 0 and 360 months (30 years)';
                errorEl.classList.add('text-red-500');
            } else {
                errorEl.textContent = '';
                errorEl.classList.remove('text-red-500');
            }
        }

        // Color: Letters, spaces, commas, ampersands, hyphens only
        function validateColor(input) {
            let value = input.value;
            // Allow letters, spaces, commas, ampersands, and hyphens
            value = value.replace(/[^A-Za-z\s\,\&\-']/g, '');
            // Prevent starting with non-letter
            if (value.length > 0 && !/^[A-Za-z]/.test(value)) {
                value = value.replace(/^[^A-Za-z]+/, '');
            }
            input.value = value;
            
            const errorEl = document.getElementById('colorError');
            if (value.length > 0 && value.length < 2) {
                errorEl.textContent = 'Color must be at least 2 characters';
                errorEl.classList.add('text-red-500');
            } else {
                errorEl.textContent = '';
                errorEl.classList.remove('text-red-500');
            }
        }

        // Breed: Letters, spaces, hyphens, apostrophes, slashes only
        function validateBreed(input) {
            let value = input.value;
            // Allow letters, spaces, hyphens, apostrophes, and slashes
            value = value.replace(/[^A-Za-z\s\-'\/]/g, '');
            // Prevent starting with non-letter
            if (value.length > 0 && !/^[A-Za-z]/.test(value)) {
                value = value.replace(/^[^A-Za-z]+/, '');
            }
            input.value = value;
        }

        // Other Species: Letters, spaces, hyphens only
        function validateOtherSpecies(input) {
            let value = input.value;
            value = value.replace(/[^A-Za-z\s\-]/g, '');
            if (value.length > 0 && !/^[A-Za-z]/.test(value)) {
                value = value.replace(/^[^A-Za-z]+/, '');
            }
            input.value = value;
        }

        // Medical History character counter
        function updateMedicalHistoryCount() {
            const count = medicalHistoryInput.value.length;
            document.getElementById('medicalHistoryCount').textContent = count;
        }

        // =====================
        // EVENT LISTENERS
        // =====================

        // Real-time validation on input
        petNameInput.addEventListener('input', function() {
            validatePetName(this);
        });

        petAgeInput.addEventListener('input', function() {
            validateAge(this);
        });

        // Prevent non-numeric keypress for age (allows backspace, delete, arrows)
        petAgeInput.addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'ArrowLeft' && e.key !== 'ArrowRight' && e.key !== 'Tab') {
                e.preventDefault();
            }
        });

        petColorInput.addEventListener('input', function() {
            validateColor(this);
        });

        petBreedInput.addEventListener('input', function() {
            validateBreed(this);
        });

        if (otherSpeciesInput) {
            otherSpeciesInput.addEventListener('input', function() {
                validateOtherSpecies(this);
            });
        }

        medicalHistoryInput.addEventListener('input', function() {
            updateMedicalHistoryCount();
        });

        // Prevent paste of invalid characters
        petAgeInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const numericOnly = pastedText.replace(/[^0-9]/g, '');
            this.value = numericOnly.slice(0, 3);
            validateAge(this);
        });

        // =====================
        // SPECIES & SEX LOGIC
        // =====================

        speciesSelect.addEventListener('change', function() {
            const selectedText = this.options[this.selectedIndex].text;
            const isOther = (selectedText === 'Other');
            otherSpeciesDiv.style.display = isOther ? 'block' : 'none';
            
            if (otherSpeciesInput) {
                if (isOther) {
                    otherSpeciesInput.setAttribute('required', 'required');
                } else {
                    otherSpeciesInput.removeAttribute('required');
                    otherSpeciesInput.value = '';
                }
            }
        });

        if (speciesSelect.options[speciesSelect.selectedIndex]?.text === 'Other') {
            otherSpeciesDiv.style.display = 'block';
            if (otherSpeciesInput) {
                otherSpeciesInput.setAttribute('required', 'required');
            }
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

        // Initialize character count
        updateMedicalHistoryCount();

        // =====================
        // FORM SUBMISSION
        // =====================

        function validateAndSubmit() {
            const btn = document.getElementById('petSubmitBtn');
            const form = document.getElementById('petForm');
            
            // Run all validations
            validatePetName(petNameInput);
            validateAge(petAgeInput);
            validateColor(petColorInput);
            
            // Check if form is valid
            if (!form.checkValidity()) {
                // Show validation messages
                form.reportValidity();
                return false;
            }
            
            // Additional custom validations
            const age = parseInt(petAgeInput.value, 10);
            if (isNaN(age) || age < 0 || age > 360) {
                alert('Please enter a valid age between 0 and 360 months.');
                petAgeInput.focus();
                return false;
            }
            
            // Check "Other" species
            const selectedSpeciesText = speciesSelect.options[speciesSelect.selectedIndex]?.text;
            if (selectedSpeciesText === 'Other' && otherSpeciesInput && !otherSpeciesInput.value.trim()) {
                alert('Please specify the species name.');
                otherSpeciesInput.focus();
                return false;
            }
            
            // Disable button to prevent double submission
            btn.disabled = true;
            btn.innerHTML = 'Processing...';
            btn.classList.add('bg-gray-400');
            btn.classList.remove('bg-red-700', 'hover:bg-red-800');
            
            return true;
        }
    </script>
</x-dashboardheader-layout>