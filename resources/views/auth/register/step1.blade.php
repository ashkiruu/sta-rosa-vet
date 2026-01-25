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

                <form method="POST" action="{{ route('register.step1') }}" class="space-y-6">
                    @csrf

                    {{-- Form Grid: 3 Columns for Landscape Desktop View --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-6">
                        
                        {{-- First Name --}}
                        <div>
                            <x-input-label for="First_Name" value="First Name" class="text-red-700 font-black uppercase text-[10px]" />
                            <x-text-input id="First_Name" name="First_Name" type="text" value="{{ old('First_Name', $data['First_Name'] ?? '') }}" 
                                class="block mt-1 w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50" required autofocus />
                            <x-input-error :messages="$errors->get('First_Name')" class="mt-1" />
                        </div>

                        {{-- Middle Name --}}
                        <div>
                            <x-input-label for="Middle_Name" value="Middle Name (Optional)" class="text-gray-400 font-black uppercase text-[10px]" />
                            <x-text-input id="Middle_Name" name="Middle_Name" type="text" value="{{ old('Middle_Name', $data['Middle_Name'] ?? '') }}" 
                                class="block mt-1 w-full border-gray-200 rounded-xl bg-gray-50/50" />
                        </div>

                        {{-- Last Name --}}
                        <div>
                            <x-input-label for="Last_Name" value="Last Name" class="text-red-700 font-black uppercase text-[10px]" />
                            <x-text-input id="Last_Name" name="Last_Name" type="text" value="{{ old('Last_Name', $data['Last_Name'] ?? '') }}" 
                                class="block mt-1 w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50" required />
                            <x-input-error :messages="$errors->get('Last_Name')" class="mt-1" />
                        </div>

                        {{-- Mobile Number --}}
                        <div>
                            <x-input-label for="Contact_Number" value="Mobile Number" class="text-red-700 font-black uppercase text-[10px]" />
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 font-bold text-sm">+63</span>
                                <x-text-input id="Contact_Number" name="Contact_Number" type="text" value="{{ old('Contact_Number', $data['Contact_Number'] ?? '') }}"
                                    class="block w-full pl-12 border-gray-200 rounded-xl bg-gray-50/50" placeholder="9123456789" required />
                            </div>
                            <x-input-error :messages="$errors->get('Contact_Number')" class="mt-1" />
                        </div>

                        {{-- Barangay --}}
                        <div>
                            <x-input-label for="Barangay_ID" value="Barangay" class="text-red-700 font-black uppercase text-[10px]" />
                            <select id="Barangay_ID" name="Barangay_ID" 
                                class="mt-1 block w-full border-gray-200 rounded-xl focus:border-red-500 focus:ring-red-500 shadow-sm bg-gray-50/50 py-2.5">
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
                                class="mt-1 block w-full border-gray-200 rounded-xl focus:border-red-500 focus:ring-red-500 shadow-sm bg-gray-50/50">{{ old('Address', $step1['Address'] ?? '') }}</textarea>
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
                            <button type="submit" 
                                class="w-full md:w-64 bg-red-700 hover:bg-red-800 text-white font-black py-4 rounded-2xl shadow-lg transition-all active:scale-95 uppercase tracking-widest text-sm">
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
</x-guest-layout>