<x-guest-layout>
    <div class="max-w-5xl mx-auto px-4 py-10">
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden">
            <div class="p-8 md:p-12">

                {{-- Centered Header & Progress Bar Section --}}
                <div class="flex flex-col items-center justify-center mb-12">
                    <h2 class="text-4xl font-black text-gray-900 uppercase tracking-tight">Privacy Notice</h2>
                    <p class="text-red-700 font-bold uppercase text-xs tracking-[0.2em] mt-2 mb-8">
                        Data Privacy Act (RA 10173)
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
                        <h3 class="text-sm font-black uppercase tracking-widest text-gray-600">1) Data Collected</h3>
                        <ul class="mt-2 list-disc pl-6 text-gray-700 leading-relaxed space-y-2">
                            <li>Account details: name, email, contact number, address, barangay</li>
                            <li>Uploaded ID image (if provided) and extracted text for verification</li>
                            <li>Verification results and confidence scores</li>
                            <li>System logs for audit/security</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-gray-600">2) Purpose of Processing</h3>
                        <p class="mt-2 text-gray-700 leading-relaxed">
                            Data is processed to create your account, reduce fraudulent use, verify identity, and provide veterinary-related
                            services such as appointment scheduling and certificate issuance (when applicable).
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-gray-600">3) Access & Retention</h3>
                        <p class="mt-2 text-gray-700 leading-relaxed">
                            Access is limited to authorized staff. Data may be retained only as long as necessary for legitimate operational,
                            legal, and audit requirements. Reasonable security measures are applied to protect stored information.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-gray-600">4) Your Rights</h3>
                        <p class="mt-2 text-gray-700 leading-relaxed">
                            You may request information about your personal data, subject to verification and applicable policies.
                            For concerns, contact the clinic/admin for assistance.
                        </p>
                    </div>
                </div>

                {{-- Footer Buttons --}}
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
