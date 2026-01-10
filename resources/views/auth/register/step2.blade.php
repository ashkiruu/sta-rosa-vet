<x-guest-layout>
    <div class="max-w-xl mx-auto mt-10 p-6 bg-white rounded shadow">
        <!-- Progress Bar -->
        <div class="flex mb-6">
            <div class="w-1/3 h-2 bg-red-600 rounded-l"></div>
            <div class="w-1/3 h-2 bg-red-600 mx-1"></div>
            <div class="w-1/3 h-2 bg-gray-300 rounded-r"></div>
        </div>

        <h2 class="text-xl font-semibold mb-4">Step 2: Upload Your ID</h2>

        <form method="POST" action="{{ route('register.step2.post') }}" enctype="multipart/form-data">
            @csrf

            <!-- ID Upload -->
            <div class="mb-4">
                <label for="id_file" class="block font-medium">Upload ID (jpg, jpeg, png)</label>
                <input type="file" name="id_file" id="id_file"
                    class="w-full border rounded px-3 py-2" accept=".jpg,.jpeg,.png" required>

                @error('id_file') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Preview -->
            <div class="mb-4">
                <label class="block font-medium">Preview:</label>
                <img id="preview" src="#" class="hidden w-64 h-auto border mt-2" />
            </div>

            <!-- Verification Feedback -->
            @if(session('ocr_status'))
                <div class="mb-4 p-3 border rounded @if(session('ocr_status') == 'Verified') bg-green-100 text-green-700 @else bg-yellow-100 text-yellow-700 @endif">
                    {{ session('ocr_message') }}
                </div>
            @endif

            <div class="flex justify-between">
                <a href="{{ route('register.step1') }}" class="px-6 py-2 border rounded hover:bg-gray-100">Back</a>
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700">
                    Next
                </button>
            </div>
        </form>
    </div>

    <!-- JS for image preview -->
    <script>
        const fileInput = document.getElementById('id_file');
        const preview = document.getElementById('preview');

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</x-guest-layout>
