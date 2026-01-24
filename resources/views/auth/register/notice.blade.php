<x-guest-layout>
    <div class="max-w-5xl mx-auto px-4 py-10">
        {{-- Single Flattened Landscape Card --}}
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden">

            <div class="p-8 md:p-12">
                {{-- Centered Header & Progress Bar Section --}}
                <div class="flex flex-col items-center justify-center mb-12">
                    <h2 class="text-4xl font-black text-gray-900 uppercase tracking-tight">Register</h2>
                    <p class="text-red-700 font-bold uppercase text-xs tracking-[0.2em] mt-2 mb-8">
                        Attention to All Applicants
                    </p>

                    {{-- Centered Progress Bar (Notice -> 1 -> 2 -> 3) --}}
                    <div class="flex items-center justify-center w-full max-w-md relative">
                        <div class="absolute top-1/2 left-0 w-full h-0.5 bg-gray-300 -translate-y-1/2 z-0"></div>

                        <div class="flex justify-between w-full relative z-10">
                            {{-- Notice Active --}}
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-700 text-white font-black shadow-lg border-4 border-white">
                                !
                            </div>
                            {{-- Step 1 Inactive --}}
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-400 text-white font-black border-4 border-white">1</div>
                            {{-- Step 2 Inactive --}}
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-400 text-white font-black border-4 border-white">2</div>
                            {{-- Step 3 Inactive --}}
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-400 text-white font-black border-4 border-white">3</div>
                        </div>
                    </div>
                </div>

                {{-- Notice Content --}}
                <div class="bg-gray-50/50 rounded-3xl border border-gray-100 p-7 md:p-8">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-red-700 flex items-center justify-center shadow-lg shrink-0">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v3m0 4h.01M10.29 3.86l-7.1 12.27A2 2 0 005.01 19h13.98a2 2 0 001.72-2.87l-7.1-12.27a2 2 0 00-3.42 0z"/>
                            </svg>
                        </div>

                        <div class="flex-1">
                            <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">
                                Important Legal & Privacy Notice
                            </h3>

                            <p class="mt-3 text-gray-700 text-justify leading-relaxed">
                                By using this system, you confirm that the information you submit is true, accurate, and that you are authorized
                                to provide it. Submitting false information, using another person’s identity, or uploading a valid ID without
                                the owner’s knowledge and consent may constitute misrepresentation and may lead to legal consequences under
                                applicable Philippine laws, including Republic Act No. 10173 (Data Privacy Act of 2012).
                            </p>

                            <p class="mt-3 text-gray-700 text-justify leading-relaxed">
                                This system collects personal data solely for legitimate purposes related to account creation and identity
                                verification. Your data will be processed and accessed only by authorized personnel for official transactions.
                            </p>

                            <p class="mt-3 text-gray-700 text-justify leading-relaxed">
                                If you have doubts, concerns, or questions about authorization or privacy, please contact the clinic/admin before proceeding.
                            </p>

                            <div class="mt-5 text-sm text-gray-600 font-semibold">
                                Review:
                                <a href="{{ route('terms') }}" target="_blank" class="text-red-700 hover:underline font-black">
                                    Terms and Conditions
                                </a>
                                <span class="mx-2 text-gray-400">|</span>
                                <a href="{{ route('privacy') }}" target="_blank" class="text-red-700 hover:underline font-black">
                                    Privacy Notice
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Agree Form --}}
                <form method="POST" action="{{ route('register.notice.post') }}" class="mt-8 space-y-5">
                    @csrf

                    <div class="flex items-start gap-3">
                        <input type="checkbox" name="agree" value="1" required
                               class="w-5 h-5 rounded border-gray-300 text-red-700 focus:ring-red-500 mt-1 cursor-pointer">
                        <div>
                            <p class="text-sm font-semibold text-gray-700">
                                I have read and understood this notice, and I agree to proceed with registration.
                            </p>
                            @error('agree')
                                <p class="mt-2 text-sm text-red-700 font-bold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Action Footer (matches Step 1–3) --}}
                    <div class="pt-8 flex flex-col md:flex-row items-center md:items-end justify-between border-t border-gray-100 gap-6">
                        <div class="w-full md:w-auto">
                            <p class="text-[15px] font-black tracking-tighter mb-2 text-right">
                                Already have an account?
                                <a href="{{ route('login') }}" class="text-red-700 hover:underline">Log in here</a>
                            </p>
                        </div>

                        <div class="w-full md:w-auto">
                            <button type="submit"
                                    class="w-full md:w-64 bg-red-700 hover:bg-red-800 text-white font-black py-4 rounded-2xl shadow-lg transition-all active:scale-95 uppercase tracking-widest text-sm">
                                Proceed
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
