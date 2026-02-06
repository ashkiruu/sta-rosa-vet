<x-guest-layout>
    <div class="max-w-5xl mx-auto px-4 py-10">
        {{-- Single Flattened Landscape Card --}}
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden">
            
            <div class="p-8 md:p-12">
                {{-- Centered Header & Progress Bar Section --}}
                <div class="flex flex-col items-center justify-center mb-12">
                    <h2 class="text-4xl font-black text-gray-900 uppercase tracking-tight">Register</h2>
                    <p class="text-red-700 font-bold uppercase text-xs tracking-[0.2em] mt-2 mb-8">Step 1: Personal Profile</p>

                    {{-- Centered Progress Bar (Notice -> 1 -> 2 -> 3) --}}
                    <div class="flex items-center justify-center w-full max-w-md relative mx-auto">
                        {{-- Background Line: Gray base --}}
                        <div class="absolute top-1/2 left-0 w-full h-0.5 bg-gray-300 -translate-y-1/2 z-0"></div>

                        <div class="flex justify-between w-full relative z-10">
                            {{-- Notice - Inactive/Passed (Gray) --}}
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-400 text-white font-black border-4 border-white transition-all duration-300">
                                !
                            </div>

                            {{-- Step 1 - THE ONLY HIGHLIGHTED STEP (Red) --}}
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-700 text-white font-black shadow-lg border-4 border-white transition-all duration-300 scale-110">
                                1
                            </div>

                            {{-- Step 2 - Inactive (Gray) --}}
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-400 text-white font-black border-4 border-white">
                                2
                            </div>

                            {{-- Step 3 - Inactive (Gray) --}}
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-400 text-white font-black border-4 border-white">
                                3
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('register.step1') }}" class="space-y-6" id="registrationForm" onsubmit="return validateAndSubmitForm()">
                    @csrf

                    {{-- Form Grid: 3 Columns for Landscape Desktop View --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-6">
                        
                        {{-- First Name --}}
                        <div>
                            <x-input-label for="First_Name" value="First Name" class="text-red-700 font-black uppercase text-[10px]" />
                            <x-text-input id="First_Name" name="First_Name" type="text" value="{{ old('First_Name', $data['First_Name'] ?? '') }}" 
                                class="block mt-1 w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50" 
                                required 
                                autofocus
                                minlength="2"
                                maxlength="50"
                                pattern="^[A-Za-z][A-Za-z\s\-'\.]*$"
                                title="First name must start with a letter and contain only letters, spaces, hyphens, apostrophes, and periods" />
                            <p class="text-[10px] mt-1" id="firstNameError"></p>
                            <x-input-error :messages="$errors->get('First_Name')" class="mt-1" />
                        </div>

                        {{-- Middle Name --}}
                        <div>
                            <x-input-label for="Middle_Name" value="Middle Name (Optional)" class="text-gray-400 font-black uppercase text-[10px]" />
                            <x-text-input id="Middle_Name" name="Middle_Name" type="text" value="{{ old('Middle_Name', $data['Middle_Name'] ?? '') }}" 
                                class="block mt-1 w-full border-gray-200 rounded-xl bg-gray-50/50"
                                maxlength="50"
                                pattern="^[A-Za-z][A-Za-z\s\-'\.]*$"
                                title="Middle name must start with a letter and contain only letters, spaces, hyphens, apostrophes, and periods" />
                            <p class="text-[10px] mt-1" id="middleNameError"></p>
                        </div>

                        {{-- Last Name --}}
                        <div>
                            <x-input-label for="Last_Name" value="Last Name" class="text-red-700 font-black uppercase text-[10px]" />
                            <x-text-input id="Last_Name" name="Last_Name" type="text" value="{{ old('Last_Name', $data['Last_Name'] ?? '') }}" 
                                class="block mt-1 w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50" 
                                required
                                minlength="2"
                                maxlength="50"
                                pattern="^[A-Za-z][A-Za-z\s\-'\.]*$"
                                title="Last name must start with a letter and contain only letters, spaces, hyphens, apostrophes, and periods" />
                            <p class="text-[10px] mt-1" id="lastNameError"></p>
                            <x-input-error :messages="$errors->get('Last_Name')" class="mt-1" />
                        </div>

                        {{-- Birthdate --}}
                        <div>
                            <x-input-label for="Birthdate" value="Date of Birth" class="text-red-700 font-black uppercase text-[10px]" />
                            <x-text-input id="Birthdate" name="Birthdate" type="date" value="{{ old('Birthdate', $data['Birthdate'] ?? '') }}" 
                                class="block mt-1 w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50" required 
                                max="{{ now()->subYears(18)->format('Y-m-d') }}" 
                                min="{{ now()->subYears(120)->format('Y-m-d') }}" />
                            <p class="text-[10px] text-gray-400 mt-1">You must be at least 15 years old</p>
                            <x-input-error :messages="$errors->get('Birthdate')" class="mt-1" />
                        </div>

                        {{-- Civil Status --}}
                        <div>
                            <x-input-label for="Civil_Status" value="Civil Status" class="text-red-700 font-black uppercase text-[10px]" />
                            <select id="Civil_Status" name="Civil_Status" 
                                class="mt-1 block w-full border-gray-200 rounded-xl focus:border-red-500 focus:ring-red-500 shadow-sm bg-gray-50/50 py-2.5" required>
                                <option value="">Select Civil Status</option>
                                @php 
                                    $civilStatuses = ['Single', 'Married', 'Widowed', 'Separated', 'Divorced'];
                                    $selectedCivil = old('Civil_Status', $data['Civil_Status'] ?? '');
                                @endphp
                                @foreach($civilStatuses as $status)
                                    <option value="{{ $status }}" {{ $selectedCivil === $status ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('Civil_Status')" class="mt-1" />
                        </div>

                        {{-- Years of Residency --}}
                        <div>
                            <x-input-label for="Years_Of_Residency" value="Years of Residency in Sta. Rosa" class="text-red-700 font-black uppercase text-[10px]" />
                            <x-text-input id="Years_Of_Residency" name="Years_Of_Residency" type="text" 
                                inputmode="numeric"
                                value="{{ old('Years_Of_Residency', $data['Years_Of_Residency'] ?? '') }}" 
                                class="block mt-1 w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" 
                                placeholder="e.g. 5"
                                required
                                pattern="^[0-9]{1,3}$"
                                title="Please enter a number between 0 and 100" />
                            <p class="text-[10px] mt-1" id="residencyError"></p>
                            <x-input-error :messages="$errors->get('Years_Of_Residency')" class="mt-1" />
                        </div>

                        {{-- Mobile Number --}}
                        <div>
                            <x-input-label for="Contact_Number" value="Mobile Number" class="text-red-700 font-black uppercase text-[10px]" />
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 font-bold text-sm">+63</span>
                                <x-text-input id="Contact_Number" name="Contact_Number" type="text" 
                                    inputmode="numeric"
                                    value="{{ old('Contact_Number', $data['Contact_Number'] ?? '') }}"
                                    class="block w-full pl-12 border-gray-200 rounded-xl bg-gray-50/50" 
                                    placeholder="9123456789" 
                                    required
                                    minlength="10"
                                    maxlength="10"
                                    pattern="^9[0-9]{9}$"
                                    title="Please enter a valid Philippine mobile number starting with 9 (10 digits)" />
                            </div>
                            <p class="text-[10px] mt-1" id="contactError"></p>
                            <x-input-error :messages="$errors->get('Contact_Number')" class="mt-1" />
                        </div>

                        {{-- Barangay --}}
                        <div>
                            <x-input-label for="Barangay_ID" value="Barangay" class="text-red-700 font-black uppercase text-[10px]" />
                            <select id="Barangay_ID" name="Barangay_ID" 
                                class="mt-1 block w-full border-gray-200 rounded-xl focus:border-red-500 focus:ring-red-500 shadow-sm bg-gray-50/50 py-2.5" required>
                                <option value="">Select Barangay</option>
                                @foreach($barangays as $barangay)
                                    @php $step1 = session('register.step1', []); @endphp

                                    <option value="{{ $barangay->Barangay_ID }}"
                                        {{ (string) old('Barangay_ID', $step1['Barangay_ID'] ?? '') === (string) $barangay->Barangay_ID ? 'selected' : '' }}>
                                        {{ $barangay->Barangay_Name }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Custom Error Message --}}
                            @error('Barangay_ID')
                                <p class="text-sm text-red-600 mt-1">The barangay field is required.</p>
                            @enderror
                        </div>

                        {{-- City --}}
                        <div>
                            <x-input-label for="City" value="City / Province" class="text-gray-400 font-black uppercase text-[10px]" />
                            <x-text-input id="City" name="City" type="text" value="Sta. Rosa, Laguna" 
                                class="mt-1 block w-full bg-gray-100 border-gray-200 rounded-xl text-gray-500 cursor-not-allowed" readonly />
                        </div>

                        {{-- Address (Full Width Span) --}}
                        <div class="md:col-span-3">
                            <x-input-label for="Address" value="House No. / Street / Subd." class="text-red-700 font-black uppercase text-[10px]" />
                            @php $step1 = $step1 ?? session('register.step1', []); @endphp
                            <textarea id="Address" name="Address" rows="2"
                                class="mt-1 block w-full border-gray-200 rounded-xl focus:border-red-500 focus:ring-red-500 shadow-sm bg-gray-50/50" 
                                required
                                minlength="5"
                                maxlength="500"
                                placeholder="e.g. 123 Main Street, Villa Subdivision">{{ old('Address', $step1['Address'] ?? '') }}</textarea>
                            <p class="text-[10px] text-gray-400 mt-1"><span id="addressCount">0</span>/500 characters</p>
                            <x-input-error :messages="$errors->get('Address')" class="mt-1" />
                        </div>
                    </div>

                    {{-- Action Footer --}}
                    <div class="pt-10 border-t border-gray-100">
                        {{-- Login Link (Centered above buttons) --}}

                        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                            {{-- Back Button (to Notice) --}}
                            <a href="{{ route('register.notice') }}" 
                                class="w-full md:w-auto bg-gray-500 hover:bg-gray-600 text-white font-black py-4 px-12 rounded-2xl shadow-lg transition-all active:scale-95 uppercase tracking-widest text-sm text-center">
                                Back
                            </a>

                            {{-- Next Button --}}
                            <button type="submit" id="submitBtn"
                                class="w-full md:w-64 bg-red-700 hover:bg-red-800 text-white font-black py-4 rounded-2xl shadow-lg transition-all active:scale-95 uppercase tracking-widest text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                Next Step
                            </button>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 font-medium text-right">
                        Already have an account? 
                        <a href="{{ route('login') }}" class="text-red-700 font-black hover:text-red-800 hover:underline transition ml-1">
                            Log in here
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script>
        // =====================
        // INPUT ELEMENTS
        // =====================
        const firstNameInput = document.getElementById('First_Name');
        const middleNameInput = document.getElementById('Middle_Name');
        const lastNameInput = document.getElementById('Last_Name');
        const contactInput = document.getElementById('Contact_Number');
        const residencyInput = document.getElementById('Years_Of_Residency');
        const addressInput = document.getElementById('Address');
        const birthdateInput = document.getElementById('Birthdate');

        // =====================
        // VALIDATION FUNCTIONS
        // =====================

        // Name validation: Letters, spaces, hyphens, apostrophes, periods only
        function validateName(input, errorElementId, fieldName, required = true) {
            let value = input.value;
            
            // Allow letters, spaces, hyphens, apostrophes, and periods
            value = value.replace(/[^A-Za-z\s\-'\.]/g, '');
            
            // Prevent starting with non-letter (if not empty)
            if (value.length > 0 && !/^[A-Za-z]/.test(value)) {
                value = value.replace(/^[^A-Za-z]+/, '');
            }
            
            // Prevent multiple consecutive spaces
            value = value.replace(/\s{2,}/g, ' ');
            
            input.value = value;
            
            const errorEl = document.getElementById(errorElementId);
            if (errorEl) {
                if (required && value.length > 0 && value.length < 2) {
                    errorEl.textContent = `${fieldName} must be at least 2 characters`;
                    errorEl.classList.add('text-red-500');
                    errorEl.classList.remove('text-gray-400');
                } else {
                    errorEl.textContent = '';
                    errorEl.classList.remove('text-red-500');
                }
            }
        }

        // Mobile number validation: Numbers only, must start with 9, exactly 10 digits
        function validateMobileNumber(input) {
            let value = input.value;
            
            // Remove any non-numeric characters
            value = value.replace(/[^0-9]/g, '');
            
            // Limit to 10 digits
            if (value.length > 10) {
                value = value.slice(0, 10);
            }
            
            input.value = value;
            
            const errorEl = document.getElementById('contactError');
            if (value.length > 0) {
                if (!value.startsWith('9')) {
                    errorEl.textContent = 'Mobile number must start with 9';
                    errorEl.classList.add('text-red-500');
                    errorEl.classList.remove('text-gray-400');
                } else if (value.length < 10) {
                    errorEl.textContent = `Enter ${10 - value.length} more digit(s)`;
                    errorEl.classList.add('text-red-500');
                    errorEl.classList.remove('text-gray-400');
                } else {
                    errorEl.textContent = '✓ Valid mobile number';
                    errorEl.classList.remove('text-red-500');
                    errorEl.classList.add('text-green-600');
                }
            } else {
                errorEl.textContent = '';
                errorEl.classList.remove('text-red-500', 'text-green-600');
            }
        }

        // Years of residency validation: Numbers only, 0-100
        function validateResidency(input) {
            let value = input.value;
            
            // Remove any non-numeric characters
            value = value.replace(/[^0-9]/g, '');
            
            // Limit to 3 digits
            if (value.length > 3) {
                value = value.slice(0, 3);
            }
            
            // Max 100 years
            const numValue = parseInt(value, 10);
            if (numValue > 100) {
                value = '100';
            }
            
            input.value = value;
            
            const errorEl = document.getElementById('residencyError');
            if (value.length > 0 && numValue > 100) {
                errorEl.textContent = 'Maximum 100 years';
                errorEl.classList.add('text-red-500');
            } else {
                errorEl.textContent = '';
                errorEl.classList.remove('text-red-500');
            }
        }

        // Address character counter
        function updateAddressCount() {
            const count = addressInput.value.length;
            document.getElementById('addressCount').textContent = count;
        }

        // Birthdate validation
        function validateBirthdate() {
            const value = birthdateInput.value;
            if (!value) return true;
            
            const birthDate = new Date(value);
            const today = new Date();
            const minDate = new Date();
            minDate.setFullYear(today.getFullYear() - 120);
            const maxDate = new Date();
            maxDate.setFullYear(today.getFullYear() - 15);
            
            if (birthDate > maxDate) {
                return false; // Under 5
            }
            if (birthDate < minDate) {
                return false; // Over 120
            }
            return true;
        }

        // =====================
        // EVENT LISTENERS
        // =====================

        // Name fields
        firstNameInput.addEventListener('input', function() {
            validateName(this, 'firstNameError', 'First name', true);
        });

        middleNameInput.addEventListener('input', function() {
            validateName(this, 'middleNameError', 'Middle name', false);
        });

        lastNameInput.addEventListener('input', function() {
            validateName(this, 'lastNameError', 'Last name', true);
        });

        // Mobile number - input event
        contactInput.addEventListener('input', function() {
            validateMobileNumber(this);
        });

        // Mobile number - keypress event (block non-numeric keys)
        contactInput.addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'ArrowLeft' && e.key !== 'ArrowRight' && e.key !== 'Tab') {
                e.preventDefault();
            }
        });

        // Mobile number - paste event
        contactInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const numericOnly = pastedText.replace(/[^0-9]/g, '');
            this.value = numericOnly.slice(0, 10);
            validateMobileNumber(this);
        });

        // Years of residency - input event
        residencyInput.addEventListener('input', function() {
            validateResidency(this);
        });

        // Years of residency - keypress event
        residencyInput.addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'ArrowLeft' && e.key !== 'ArrowRight' && e.key !== 'Tab') {
                e.preventDefault();
            }
        });

        // Years of residency - paste event
        residencyInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const numericOnly = pastedText.replace(/[^0-9]/g, '');
            this.value = numericOnly.slice(0, 3);
            validateResidency(this);
        });

        // Address character count
        addressInput.addEventListener('input', function() {
            updateAddressCount();
        });

        // Initialize address count on page load
        updateAddressCount();

        // =====================
        // FORM SUBMISSION
        // =====================

        function validateAndSubmitForm() {
            const btn = document.getElementById('submitBtn');
            const form = document.getElementById('registrationForm');
            
            // Run all validations
            validateName(firstNameInput, 'firstNameError', 'First name', true);
            validateName(lastNameInput, 'lastNameError', 'Last name', true);
            validateMobileNumber(contactInput);
            validateResidency(residencyInput);
            
            // Check if form is valid (HTML5 validation)
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }
            
            // Custom validations
            
            // Mobile number must be exactly 10 digits and start with 9
            const contactValue = contactInput.value;
            if (contactValue.length !== 10 || !contactValue.startsWith('9')) {
                alert('Please enter a valid Philippine mobile number (10 digits starting with 9).');
                contactInput.focus();
                return false;
            }
            
            // Years of residency must be 0-100
            const residencyValue = parseInt(residencyInput.value, 10);
            if (isNaN(residencyValue) || residencyValue < 0 || residencyValue > 100) {
                alert('Years of residency must be between 0 and 100.');
                residencyInput.focus();
                return false;
            }
            
            // Birthdate validation
            if (!validateBirthdate()) {
                alert('You must be at least 15 years old to register.');
                birthdateInput.focus();
                return false;
            }
            
            // Address minimum length
            if (addressInput.value.trim().length < 5) {
                alert('Please enter a valid address (at least 5 characters).');
                addressInput.focus();
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
</x-guest-layout>