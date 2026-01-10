<x-guest-layout>
    <div class="max-w-xl mx-auto mt-10 p-6 bg-white rounded shadow">
        <!-- Progress Bar -->
        <div class="flex mb-6">
            <div class="w-1/3 h-2 bg-red-600 rounded-l"></div>
            <div class="w-1/3 h-2 bg-gray-300 mx-1"></div>
            <div class="w-1/3 h-2 bg-gray-300 rounded-r"></div>
        </div>

        <h2 class="text-xl font-semibold mb-4">Step 1: Personal Information</h2>

        <form method="POST" action="{{ route('register.step1') }}">
            @csrf

            <!-- Last Name -->
            <div class="mb-4">
                <label for="Last_Name" class="block font-medium">Last Name</label>
                <input type="text" name="Last_Name" id="Last_Name" value="{{ old('Last_Name') }}"
                    class="w-full border rounded px-3 py-2" required>
                @error('Last_Name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- First Name -->
            <div class="mb-4">
                <label for="First_Name" class="block font-medium">First Name</label>
                <input type="text" name="First_Name" id="First_Name" value="{{ old('First_Name') }}"
                    class="w-full border rounded px-3 py-2" required>
                @error('First_Name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Middle Name -->
            <div class="mb-4">
                <label for="Middle_Name" class="block font-medium">Middle Name</label>
                <input type="text" name="Middle_Name" id="Middle_Name" value="{{ old('Middle_Name') }}"
                    class="w-full border rounded px-3 py-2" required>
                @error('Middle_Name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Contact Number -->
            <div class="mb-4">
                <label for="Contact_Number" class="block font-medium">Mobile Number</label>
                <input type="text" name="Contact_Number" id="Contact_Number" value="{{ old('Contact_Number') }}"
                    class="w-full border rounded px-3 py-2" required>
                @error('Contact_Number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Address -->
            <div class="mb-4">
                <label for="Address" class="block font-medium">Address</label>
                <textarea name="Address" id="Address" rows="2"
                    class="w-full border rounded px-3 py-2" required>{{ old('Address') }}</textarea>
                @error('Address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Barangay -->
            <div class="mb-4">
                <label for="Barangay_ID" class="block font-medium">Barangay</label>
                <select name="Barangay_ID" id="Barangay_ID" class="w-full border rounded px-3 py-2" required>
                    <option value="">Select Barangay</option>
                    @foreach($barangays as $barangay)
                        <option value="{{ $barangay->Barangay_ID }}" {{ old('Barangay_ID') == $barangay->Barangay_ID ? 'selected' : '' }}>
                            {{ $barangay->Barangay_Name }}
                        </option>
                    @endforeach
                </select>
                @error('Barangay_ID') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- City -->
            <div class="mb-6">
                <label for="City" class="block font-medium">City</label>
                <input type="text" name="City" id="City" value="Sta. Rosa, Laguna"
                    class="w-full border rounded px-3 py-2 bg-gray-100" readonly>
            </div>

            <!-- Next Button -->
            <div class="flex justify-end">
                <button type="submit"
                    class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700">Next</button>
            </div>
        </form>
    </div>
</x-guest-layout>
