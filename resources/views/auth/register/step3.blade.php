<x-guest-layout>
    <div class="max-w-5xl mx-auto px-4 py-10">
        {{-- Single Flattened Landscape Card --}}
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden">
            
            <div class="p-8 md:p-12">
                {{-- Centered Header & Progress Bar Section --}}
                <div class="flex flex-col items-center justify-center mb-12">
                    <h2 class="text-4xl font-black text-gray-900 uppercase tracking-tight">Register</h2>
                    <p class="text-red-700 font-bold uppercase text-xs tracking-[0.2em] mt-2 mb-8">Step 3: Account Setup</p>

                    {{-- Centered Progress Bar (Notice -> 1 -> 2 -> 3) --}}
                    <div class="flex items-center justify-center w-full max-w-md relative mx-auto">
                        {{-- Background Line: Gray base --}}
                        <div class="absolute top-1/2 left-0 w-full h-0.5 bg-gray-300 -translate-y-1/2 z-0"></div>

                        <div class="flex justify-between w-full relative z-10">
                            {{-- Notice - Inactive (Gray) --}}
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-400 text-white font-black border-4 border-white transition-all duration-300">
                                !
                            </div>

                            {{-- Step 1 - Inactive (Gray) --}}
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-400 text-white font-black border-4 border-white transition-all duration-300">
                                1
                            </div>

                            {{-- Step 2 - Inactive (Gray) --}}
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-400 text-white font-black border-4 border-white transition-all duration-300">
                                2
                            </div>

                            {{-- Step 3 - THE ONLY HIGHLIGHTED STEP (Red) --}}
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-700 text-white font-black shadow-lg border-4 border-white transition-all duration-300 scale-110">
                                3
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Added onsubmit to trigger the disable function --}}
                <form id="registrationForm" method="POST" action="{{ route('register.step3.post') }}" class="space-y-6" onsubmit="disableSubmitButton()">
                    @csrf
                    @if ($errors->any())
                    <div class="w-full mb-6 rounded-2xl border border-red-200 bg-red-50 px-6 py-4 text-red-800">
                        <div class="font-black uppercase text-xs tracking-widest mb-2">
                            Please fix the following:
                        </div>
                        <ul class="list-disc pl-5 text-sm font-semibold space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                    {{-- Account Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        
                        {{-- Email Address --}}
                        <div class="md:col-span-2">
                            <x-input-label for="email" value="Email Address" class="text-red-700 font-black uppercase text-[10px]" />
                            <x-text-input id="email" name="email" type="email" :value="old('email')" 
                                class="block mt-1 w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50" required autofocus />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        {{-- Password --}}
                        <div>
                            <x-input-label for="password" value="Password" class="text-red-700 font-black uppercase text-[10px]" />
                            <div class="relative mt-1">
                                <x-text-input id="password" name="password" type="password" 
                                    class="block w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50 pr-10" required autocomplete="new-password" />
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />

                                <button type="button" onclick="togglePassword('password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-red-700 transition-colors">
                                    <svg class="h-5 w-5" fill="none" id="eye-icon-password" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <x-input-label for="password_confirmation" value="Confirm Password" class="text-red-700 font-black uppercase text-[10px]" />
                            <div class="relative mt-1">
                                <x-text-input id="password_confirmation" name="password_confirmation" type="password" 
                                    class="block w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50 pr-10" required />
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />

                                <button type="button" onclick="togglePassword('password_confirmation', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-red-700 transition-colors">
                                    <svg class="h-5 w-5" fill="none" id="eye-icon-password_confirmation" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- JavaScript Toggle Function --}}
                        <script>
                            function togglePassword(inputId, btn) {
                                const input = document.getElementById(inputId);
                                const icon = btn.querySelector('svg');
                                
                                if (input.type === "password") {
                                    input.type = "text";
                                    // Change icon to Eye-Off (strikethrough)
                                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />';
                                } else {
                                    input.type = "password";
                                    // Change back to normal Eye
                                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
                                }
                            }
                        </script>
                    </div>

                    {{-- ID Verification Status Indicators --}}
                    <div class="mt-8 p-6 bg-gray-50/50 rounded-3xl border border-gray-100 flex flex-wrap items-center gap-6">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path></svg>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 text-[10px] font-black uppercase tracking-widest text-gray-500">
                            {{-- Verified --}}
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded-full {{ session('ocr_status') == 'Verified' ? 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.5)]' : 'bg-gray-200' }}"></div>
                                <span>Verified</span>
                            </div>
                            {{-- For Manual Verification (Pending) --}}
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded-full {{ session('ocr_status') == 'Pending' ? 'bg-yellow-400 shadow-[0_0_8px_rgba(250,204,21,0.5)]' : 'bg-gray-200' }}"></div>
                                <span>For Manual Verification</span>
                            </div>
                            {{-- Unsubmitted (Skipped) --}}
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded-full {{ !session('ocr_status') ? 'bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.5)]' : 'bg-gray-200' }}"></div>
                                <span>Unsubmitted</span>
                            </div>
                        </div>
                    </div>

                    {{-- Action Footer --}}
                    <div class="pt-8 flex flex-col md:flex-row items-center md:items-end justify-between border-t border-gray-100 gap-6">
                        
                        {{-- Left Side: Back Button --}}
                        <div class="w-full md:w-auto">
                            <a href="{{ route('register.step2') }}" class="inline-block w-full md:w-auto bg-gray-500 hover:bg-gray-600 text-white font-black py-4 px-10 rounded-2xl shadow-lg transition-all active:scale-95 uppercase tracking-widest text-sm text-center">
                                Back
                            </a>
                        </div>

                        {{-- Right Side: Login Link + Submit Button --}}
                        <div class="flex flex-col items-center md:items-end w-full md:w-auto">
                            {{-- ID added for JS targeting --}}
                            <button type="submit" id="submitBtn" class="w-full md:w-64 bg-red-700 hover:bg-red-800 text-white font-black py-4 rounded-2xl shadow-lg transition-all active:scale-95 uppercase tracking-widest text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                Submit
                            </button>
                        </div>
                    </div>
                    <p class="text-[15px] font-black tracking-tighter mb-2 text-right">
                        Already have an account? <a href="{{ route('login') }}" class="text-red-700 hover:underline">Log in here</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    {{-- Script to handle double click prevention --}}
    <script>
        function disableSubmitButton() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = 'Processing...';
            btn.classList.add('bg-gray-400'); // Optional: change color to show it's inactive
            return true;
        }
    </script>
</x-guest-layout>