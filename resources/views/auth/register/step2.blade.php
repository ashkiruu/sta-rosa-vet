<x-guest-layout>
    <div class="max-w-5xl mx-auto px-4 py-10">
        <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden">
            <div class="p-8 md:p-12">

                {{-- Header --}}
                <div class="flex flex-col items-center justify-center mb-12">
                    <h2 class="text-4xl font-black text-gray-900 uppercase tracking-tight">
                        {{ isset($isReverifying) ? 'Verify Your Account' : 'Register' }}
                    </h2>
                    <p class="text-red-700 font-bold uppercase text-xs tracking-[0.2em] mt-2 mb-8">
                        Step 2: ID Verification (Optional)
                    </p>

                    {{-- Progress Bar --}}
                    <div class="flex items-center justify-center w-full max-w-md relative mx-auto">
                        <div class="absolute top-1/2 left-0 w-full h-0.5 bg-gray-300 -translate-y-1/2 z-0"></div>
                        <div class="flex justify-between w-full relative z-10">
                            <div class="w-10 h-10 rounded-full bg-gray-400 text-white flex items-center justify-center font-black border-4 border-white">!</div>
                            <div class="w-10 h-10 rounded-full bg-gray-400 text-white flex items-center justify-center font-black border-4 border-white">1</div>
                            <div class="w-10 h-10 rounded-full bg-red-700 text-white flex items-center justify-center font-black border-4 border-white scale-110 shadow-lg">2</div>
                            <div class="w-10 h-10 rounded-full bg-gray-400 text-white flex items-center justify-center font-black border-4 border-white">3</div>
                        </div>
                    </div>
                </div>

                {{-- ERROR ALERT - Single location for error display --}}
                @if ($errors->any())
                    <div class="mb-8 p-4 rounded-2xl bg-red-50 border-2 border-red-200 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-black uppercase tracking-wide text-red-800 mb-1">
                                    Verification Failed
                                </h3>
                                @foreach ($errors->all() as $error)
                                    <p class="text-sm text-red-700 font-medium">{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST"
                      action="{{ isset($isReverifying) ? route('verify.process') : route('register.step2.post') }}"
                      enctype="multipart/form-data"
                      class="space-y-10">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-start">

                        {{-- LEFT: Instructions --}}
                        <div class="space-y-6">

                            {{-- Formal Instructions --}}
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 px-6 py-5">
                                <p class="text-[11px] text-gray-700 leading-relaxed font-semibold">
                                    <span class="block font-black uppercase tracking-widest text-gray-500 text-[10px] mb-2">
                                        ID Upload Instructions
                                    </span>
                                    Please upload a clear image of a valid government-issued ID (e.g., Philippine National ID).
                                    Ensure that all information is readable and free from glare or blur.<br><br>
                                    After submission, the system will automatically process and verify your ID.
                                    <strong>This may take a few moments.</strong>
                                    While verification is in progress, please
                                    <strong>do not refresh the page or close your browser</strong>,
                                    as this may interrupt the process.
                                </p>
                            </div>

                            {{-- Example ID Image --}}
                            <div class="rounded-2xl border-2 border-blue-200 bg-blue-50/50 p-4">
                                <p class="text-[10px] font-black uppercase tracking-widest text-blue-700 mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Example: Philippine National ID
                                </p>
                                <div class="rounded-xl overflow-hidden border border-blue-200 shadow-sm bg-white">
                                    {{-- Actual image from public/images folder --}}
                                    <img src="{{ asset('images/sample-national-id.png') }}" 
                                         alt="Sample Philippine National ID" 
                                         class="w-full h-auto object-contain"
                                         onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'p-8 text-center text-gray-400\'><svg class=\'w-12 h-12 mx-auto mb-2\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\' /></svg><p class=\'text-xs\'>Sample ID image</p></div>';">
                                </div>
                                <p class="text-[9px] text-blue-600 font-bold uppercase tracking-wider mt-3 text-center">
                                    This is a sample image for reference only
                                </p>
                            </div>

                            {{-- Tips --}}
                            <div class="space-y-4 bg-gray-50/50 p-6 rounded-3xl border border-gray-100">
                                <h3 class="text-gray-500 font-black uppercase text-xs tracking-widest">
                                    Tips for uploading valid ID
                                </h3>
                                <ul class="text-sm text-gray-600 space-y-3 font-medium">
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 bg-red-700 rounded-full"></span>
                                        Use a clear, readable ID
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 bg-red-700 rounded-full"></span>
                                        Avoid glare or blur
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 bg-red-700 rounded-full"></span>
                                        Ensure your name and address are visible
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 bg-red-700 rounded-full"></span>
                                        Accepted: Philippine National ID, UMID, Driver's License
                                    </li>
                                </ul>
                            </div>

                            {{-- Optional Notice --}}
                            <p class="text-[11px] text-amber-600 font-bold uppercase tracking-wider leading-relaxed">
                                Notice: You may skip this step and verify later in your account settings.
                                Unverified accounts may have limited access to services.
                            </p>
                        </div>

                        {{-- RIGHT: Upload --}}
                        <div class="flex flex-col items-center space-y-4 w-full">

                            <x-input-label for="id_file"
                                value="Upload Your ID"
                                class="text-gray-400 font-black uppercase text-[10px]" />

                            <div id="drop-zone"
                                 class="relative border-2 border-dashed border-gray-200 rounded-[2rem] p-6
                                        min-h-[280px] flex flex-col items-center justify-center bg-gray-50/30
                                        hover:border-red-300 hover:bg-red-50/20 transition-all duration-300 w-full">

                                <img id="preview"
                                     src="#"
                                     class="hidden max-h-56 w-full object-contain rounded-xl z-10" />

                                <div id="upload-placeholder"
                                     class="flex flex-col items-center gap-3 py-8">
                                    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-sm text-gray-600 font-bold">
                                            Click to upload or drag & drop
                                        </p>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">
                                            JPG, PNG, or HEIC (Optional)
                                        </p>
                                    </div>
                                </div>

                                <input type="file"
                                       name="id_file"
                                       id="id_file"
                                       accept="image/*,.jpg,.jpeg,.png,.heic,.heif"
                                       class="absolute inset-0 opacity-0 cursor-pointer">
                            </div>

                            {{-- File name display --}}
                            <div id="file-name" class="hidden w-full text-center">
                                <p class="text-xs font-bold text-green-700 bg-green-50 px-4 py-2 rounded-xl inline-flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span id="file-name-text">File selected</span>
                                </p>
                            </div>

                            @if(session('ocr_status'))
                                <div class="w-full p-3 rounded-xl text-center text-xs font-bold uppercase tracking-tight
                                    {{ session('ocr_status') === 'Verified'
                                        ? 'bg-green-50 text-green-700 border border-green-200'
                                        : 'bg-yellow-50 text-yellow-700 border border-yellow-200' }}">
                                    {{ session('ocr_message') }}
                                </div>
                            @endif

                            <div id="processing-message"
                                 class="hidden w-full p-4 bg-blue-50 border border-blue-200 rounded-xl">
                                <div class="flex items-center justify-center gap-3">
                                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-xs font-bold uppercase tracking-widest text-blue-700">
                                        Verifying ID. Please wait...
                                    </span>
                                </div>
                                <p class="text-[10px] text-blue-600 text-center mt-2 font-medium">
                                    Do not close or refresh this page
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="pt-10 flex flex-col md:flex-row items-center justify-between border-t border-gray-100 gap-6">
                        @if(isset($isReverifying))
                            <a href="{{ route('dashboard') }}"
                               class="px-10 py-4 text-gray-500 font-black uppercase text-xs tracking-widest hover:text-gray-700 transition">
                                Cancel
                            </a>
                        @else
                            <a href="{{ route('register.step1') }}"
                               class="bg-gray-500 hover:bg-gray-600 text-white font-black py-4 px-10 rounded-2xl shadow-lg uppercase tracking-widest text-sm transition-all active:scale-95">
                                Back
                            </a>
                        @endif

                        <button type="submit"
                                id="submit-btn"
                                class="bg-red-700 hover:bg-red-800 text-white font-black py-4 px-12 rounded-2xl shadow-lg uppercase tracking-widest text-sm transition-all active:scale-95">
                            {{ isset($isReverifying) ? 'Upload and Verify' : 'Next Step' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Preview + Loading --}}
    <script>
        const fileInput = document.getElementById('id_file');
        const preview = document.getElementById('preview');
        const placeholder = document.getElementById('upload-placeholder');
        const processing = document.getElementById('processing-message');
        const fileName = document.getElementById('file-name');
        const fileNameText = document.getElementById('file-name-text');
        const form = document.querySelector('form');
        const submitBtn = document.getElementById('submit-btn');
        const dropZone = document.getElementById('drop-zone');

        // File input change handler
        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (!file) return;

            // Show file name
            fileNameText.textContent = file.name;
            fileName.classList.remove('hidden');

            // Show preview
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
                dropZone.classList.add('border-green-300', 'bg-green-50/30');
                dropZone.classList.remove('border-gray-200', 'bg-gray-50/30');
            };
            reader.readAsDataURL(file);
        });

        // Drag and drop styling
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-red-400', 'bg-red-50/50');
        });

        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-red-400', 'bg-red-50/50');
        });

        dropZone.addEventListener('drop', (e) => {
            dropZone.classList.remove('border-red-400', 'bg-red-50/50');
        });

        // Form submit handler
        form.addEventListener('submit', () => {
            processing.classList.remove('hidden');
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Processing...';
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        });
    </script>
</x-guest-layout>