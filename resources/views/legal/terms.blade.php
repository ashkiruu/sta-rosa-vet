<x-guest-layout>
    <div class="max-w-5xl mx-auto px-4 py-10">
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden">
            <div class="p-8 md:p-12">

                {{-- Centered Header & Progress Bar Section (match style) --}}
                <div class="flex flex-col items-center justify-center mb-12">
                    <h2 class="text-4xl font-black text-gray-900 uppercase tracking-tight">Terms and Conditions</h2>
                    <p class="text-red-700 font-bold uppercase text-xs tracking-[0.2em] mt-2 mb-8">
                        Sta. Rosa Vet System
                    </p>

                    {{-- Simple progress line (visual consistency) --}}
                    <div class="flex items-center justify-center w-full max-w-md relative">
                        <div class="absolute top-1/2 left-0 w-full h-0.5 bg-gray-300 -translate-y-1/2"></div>
                        <div class="flex justify-between w-full relative">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-400 text-white font-black border-4 border-white">1</div>
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-400 text-white font-black border-4 border-white">2</div>
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-400 text-white font-black border-4 border-white">3</div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50/50 rounded-3xl border border-gray-100 p-7 md:p-8 space-y-6">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-gray-600">1) Proper Use</h3>
                        <p class="mt-2 text-gray-700 leading-relaxed">
                            You agree to use this system only for lawful purposes related to veterinary services, appointments,
                            and identity verification. You must not submit false, misleading, or unauthorized information.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-gray-600">2) User Responsibilities</h3>
                        <ul class="mt-2 list-disc pl-6 text-gray-700 leading-relaxed space-y-2">
                            <li>Provide accurate and up-to-date personal details.</li>
                            <li>Do not impersonate others or upload an ID you do not own or have permission to use.</li>
                            <li>Keep your account credentials confidential.</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-gray-600">3) Verification</h3>
                        <p class="mt-2 text-gray-700 leading-relaxed">
                            The system may request a valid government-issued ID for identity verification. Verification may use
                            automated checks and/or manual review by authorized staff. Submission does not guarantee approval.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-gray-600">4) Compliance</h3>
                        <p class="mt-2 text-gray-700 leading-relaxed">
                            Misuse of personal data or misrepresentation may have legal consequences under applicable Philippine laws,
                            including Republic Act No. 10173 (Data Privacy Act of 2012).
                        </p>
                    </div>
                </div>

                {{-- Footer Buttons (matches Step pages) --}}
                <div class="pt-8 flex flex-col md:flex-row items-center md:items-end justify-between border-t border-gray-100 gap-6 mt-10">
                    <div class="w-full md:w-auto">
                        <a href="{{ url()->previous() }}"
                           class="inline-block w-full md:w-auto bg-gray-500 hover:bg-gray-600 text-white font-black py-4 px-10 rounded-2xl shadow-lg transition-all active:scale-95 uppercase tracking-widest text-sm text-center">
                            Back
                        </a>
                    </div>

                    <div class="w-full md:w-auto">
                        <a href="{{ route('register.notice') }}"
                           class="inline-block w-full md:w-64 bg-red-700 hover:bg-red-800 text-white font-black py-4 rounded-2xl shadow-lg transition-all active:scale-95 uppercase tracking-widest text-sm text-center">
                            Proceed to Register
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-guest-layout>
