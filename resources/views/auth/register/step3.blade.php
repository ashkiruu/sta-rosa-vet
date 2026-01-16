<x-guest-layout>
    <div class="max-w-5xl mx-auto px-4 py-10">
        {{-- Single Flattened Landscape Card --}}
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden">
            
            <div class="p-8 md:p-12">
                {{-- Centered Header & Progress Bar Section --}}
                <div class="flex flex-col items-center justify-center mb-12">
                    <h2 class="text-4xl font-black text-gray-900 uppercase tracking-tight">Register</h2>
                    <p class="text-red-700 font-bold uppercase text-xs tracking-[0.2em] mt-2 mb-8">Step 3: Account Setup</p>

                    {{-- Centered Progress Bar --}}
                    <div class="flex items-center justify-center w-full max-w-md relative">
                        <div class="absolute top-1/2 left-0 w-full h-0.5 bg-gray-300 -translate-y-1/2 z-0"></div>
                        <div class="flex justify-between w-full relative z-10">
                            {{-- Step 1 & 2 Completed --}}
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-400 text-white font-black border-4 border-white">1</div>
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-400 text-white font-black border-4 border-white">2</div>
                            {{-- Step 3 Active --}}
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-700 text-white font-black shadow-lg border-4 border-white">3</div>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('register.step3.post') }}" class="space-y-6">
                    @csrf

                    {{-- Account Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        
                        {{-- Email Address --}}
                        <div class="md:col-span-2">
                            <x-input-label for="email" value="Email Address" class="text-red-700 font-black uppercase text-[10px]" />
                            <x-text-input id="email" name="email" type="email" :value="old('email')" 
                                class="block mt-1 w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50" required autofocus />
                            <x-input-error :messages="$errors->get('email')" class="mt-1" />
                        </div>

                        {{-- Password --}}
                        <div>
                            <x-input-label for="password" value="Password" class="text-red-700 font-black uppercase text-[10px]" />
                            <x-text-input id="password" name="password" type="password" 
                                class="block mt-1 w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-1" />
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <x-input-label for="password_confirmation" value="Confirm Password" class="text-red-700 font-black uppercase text-[10px]" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" 
                                class="block mt-1 w-full border-gray-200 focus:border-red-500 focus:ring-red-500 rounded-xl bg-gray-50/50" required />
                        </div>
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

                    {{-- Terms and Conditions --}}
                    <div class="flex flex-col items-end gap-4">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="terms" required class="w-5 h-5 rounded border-gray-300 text-red-700 focus:ring-red-500 transition-all cursor-pointer">
                            <span class="text-sm font-medium text-gray-600">
                                I agree to the <a href="#" class="text-blue-600 font-bold hover:underline">Terms and Conditions</a>.
                            </span>
                        </label>
                    </div>

                    {{-- Action Footer --}}
                    <div class="pt-8 flex flex-col md:flex-row items-center md:items-end justify-between border-t border-gray-100 gap-6">
                        
                        {{-- Left Side: Back Button --}}
                        <div class="w-full md:w-auto">
                            <a href="{{ route('register.step1') }}" class="inline-block w-full md:w-auto bg-gray-500 hover:bg-gray-600 text-white font-black py-4 px-10 rounded-2xl shadow-lg transition-all active:scale-95 uppercase tracking-widest text-sm text-center">
                                Back
                            </a>
                        </div>

                        {{-- Right Side: Login Link + Submit Button --}}
                        <div class="flex flex-col items-center md:items-end w-full md:w-auto">
                            <p class="text-[15px] font-black tracking-tighter mb-2">
                                Already have an account? <a href="{{ route('login') }}" class="text-red-700 hover:underline">Log in here</a>
                            </p>
                            <button type="submit" class="w-full md:w-64 bg-red-700 hover:bg-red-800 text-white font-black py-4 rounded-2xl shadow-lg transition-all active:scale-95 uppercase tracking-widest text-sm">
                                Submit
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>