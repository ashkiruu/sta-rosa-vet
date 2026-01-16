<x-guest-layout>
    <div class="max-w-5xl mx-auto px-4 py-10">
        {{-- Single Flattened Landscape Card --}}
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden">
            
            <div class="p-8 md:p-12">
                {{-- Centered Header & Progress Bar Section --}}
                <div class="flex flex-col items-center justify-center mb-12">
                    <h2 class="text-4xl font-black text-gray-900 uppercase tracking-tight">
                        {{ isset($isReverifying) ? 'Verify Your Account' : 'Register' }}
                    </h2>
                    <p class="text-red-700 font-bold uppercase text-xs tracking-[0.2em] mt-2 mb-8">
                        Step 2: ID Verification (Optional)
                    </p>

                    {{-- Centered Progress Bar --}}
                    @unless(isset($isReverifying))
                    <div class="flex items-center justify-center w-full max-w-md relative">
                        <div class="absolute top-1/2 left-0 w-full h-0.5 bg-gray-300 -translate-y-1/2 z-0"></div>
                        <div class="flex justify-between w-full relative z-10">
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-400 text-white font-black border-4 border-white">1</div>
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-700 text-white font-black shadow-lg border-4 border-white">2</div>
                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-400 text-white font-black border-4 border-white">3</div>
                        </div>
                    </div>
                    @endunless
                </div>

                <form method="POST" action="{{ isset($isReverifying) ? route('verify.process') : route('register.step2.post') }}" enctype="multipart/form-data" class="space-y-8">
                    @csrf

                    {{-- Landscape Content Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                        
                        {{-- Left Column: Tips & Info --}}
                        <div class="space-y-6">
                            <div class="space-y-4 bg-gray-50/50 p-6 rounded-3xl border border-gray-100">
                                <h3 class="text-gray-500 font-black uppercase text-xs tracking-widest">Tips for uploading valid ID</h3>
                                <ul class="text-sm text-gray-600 space-y-3 font-medium">
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 bg-red-700 rounded-full"></span> Use a clear, readable ID
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 bg-red-700 rounded-full"></span> Avoid glare or blur
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 bg-red-700 rounded-full"></span> Suggested Valid ID (National ID)
                                    </li>
                                </ul>
                            </div>

                            {{-- Optional Notice --}}
                            <div class="px-2">
                                <p class="text-[11px] text-amber-600 font-bold uppercase tracking-wider leading-relaxed">
                                    <i class="fas fa-info-circle mr-1"></i> Notice: You may skip this and verify later in settings, though unverified accounts have limited access to services.
                                </p>
                            </div>
                        </div>

                        {{-- Right Column: Upload Area --}}
                        <div class="flex flex-col items-center">
                            <div class="w-full">
                                <x-input-label for="id_file" value="Upload ID" class="text-gray-400 font-black uppercase text-[10px] mb-2" />
                                
                                {{-- Preview / Upload Box --}}
                                <div id="drop-zone" class="relative group border-2 border-dashed border-gray-200 rounded-[2rem] p-4 transition-all hover:border-red-300 min-h-[220px] flex flex-col items-center justify-center bg-gray-50/30">
                                    <img id="preview" src="#" class="hidden max-h-48 w-full object-contain rounded-xl z-10" />
                                    
                                    <div id="upload-placeholder" class="flex flex-col items-center gap-2 py-8">
                                        <svg class="w-10 h-10 text-gray-300 group-hover:text-red-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest text-center">Click to upload or drag & drop<br>(Optional)</p>
                                    </div>

                                    {{-- Removed 'required' attribute --}}
                                    <input type="file" name="id_file" id="id_file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".jpg,.jpeg,.png">
                                </div>
                                <x-input-error :messages="$errors->get('id_file')" class="mt-2 text-center" />
                            </div>

                            @if(session('ocr_status'))
                                <div class="mt-4 w-full p-3 rounded-xl text-center text-xs font-bold uppercase tracking-tight {{ session('ocr_status') == 'Verified' ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700' }}">
                                    {{ session('ocr_message') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Action Footer --}}
                    <div class="pt-10 flex flex-col md:flex-row items-center justify-between border-t border-gray-100 gap-6">
                        @if(isset($isReverifying))
                            <a href="{{ route('dashboard') }}" class="w-full md:w-auto px-10 py-4 text-gray-500 font-black uppercase text-xs tracking-widest hover:text-gray-700 text-center">Cancel</a>
                        @else
                            <a href="{{ route('register.step1') }}" class="w-full md:w-auto bg-gray-500 hover:bg-gray-600 text-white font-black py-4 px-10 rounded-2xl shadow-lg transition-all active:scale-95 uppercase tracking-widest text-sm text-center">
                                Back
                            </a>
                        @endif

                        <button type="submit" class="w-full md:w-64 bg-red-700 hover:bg-red-800 text-white font-black py-4 rounded-2xl shadow-lg transition-all active:scale-95 uppercase tracking-widest text-sm">
                            {{ isset($isReverifying) ? 'Upload and Verify' : 'Next Step' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('id_file');
        const preview = document.getElementById('preview');
        const placeholder = document.getElementById('upload-placeholder');

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    placeholder.classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</x-guest-layout>