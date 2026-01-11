<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Verification - Sta. Rosa Veterinary Office</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-8">
            <!-- Header -->
            <div class="flex items-start mb-8">
                <div class="bg-gray-600 text-white p-6 rounded-lg mr-6">
                    <h1 class="text-xl font-bold">City of</h1>
                    <h2 class="text-2xl font-bold">Santa Rosa</h2>
                    <p class="text-sm">VETERINARY<br>OFFICE</p>
                </div>
                
                <div class="flex-1">
                    <div class="bg-gray-600 text-white text-center py-4 rounded-lg mb-4">
                        <span class="text-lg font-semibold">REGISTRATION FORM</span>
                    </div>
                    
                    <h3 class="text-xl font-bold text-center mb-6">OWNER VERIFICATION</h3>
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- Last Name -->
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input 
                            type="text" 
                            id="last_name" 
                            name="last_name" 
                            value="{{ old('last_name') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500"
                            required
                        >
                    </div>

                    <!-- Extension -->
                    <div>
                        <label for="extension" class="block text-sm font-medium text-gray-700 mb-1">Ext. (Jr., Sr.)</label>
                        <input 
                            type="text" 
                            id="extension" 
                            name="extension" 
                            value="{{ old('extension') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500"
                        >
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500"
                            required
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <!-- First Name -->
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input 
                            type="text" 
                            id="first_name" 
                            name="first_name" 
                            value="{{ old('first_name') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500"
                            required
                        >
                    </div>

                    <!-- Mobile Number -->
                    <div>
                        <label for="mobile_number" class="block text-sm font-medium text-gray-700 mb-1">Mobile No.</label>
                        <input 
                            type="text" 
                            id="mobile_number" 
                            name="mobile_number" 
                            value="{{ old('mobile_number') }}"
                            placeholder="09XXXXXXXXX"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500"
                            required
                        >
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500"
                                required
                            >
                            <button 
                                type="button" 
                                onclick="togglePassword('password')"
                                class="absolute right-2 top-2 text-gray-500"
                            >
                                👁️
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <input 
                            type="text" 
                            id="address" 
                            name="address" 
                            value="{{ old('address') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500"
                            required
                        >
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500"
                                required
                            >
                            <button 
                                type="button" 
                                onclick="togglePassword('password_confirmation')"
                                class="absolute right-2 top-2 text-gray-500"
                            >
                                👁️
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Barangay Selection -->
                <div class="mb-4">
                    <label for="barangay_id" class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                    <select 
                        id="barangay_id" 
                        name="barangay_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500"
                        required
                    >
                        <option value="">Select Barangay</option>
                        @foreach(\App\Models\Barangay::orderBy('Barangay_Name')->get() as $barangay)
                            <option value="{{ $barangay->Barangay_ID }}" {{ old('barangay_id') == $barangay->Barangay_ID ? 'selected' : '' }}>
                                {{ $barangay->Barangay_Name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Valid ID Upload -->
                <div class="mb-6">
                    <label for="valid_id" class="block text-sm font-medium text-gray-700 mb-1">Valid ID</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                        <input 
                            type="file" 
                            id="valid_id" 
                            name="valid_id" 
                            accept="image/*"
                            class="hidden"
                            onchange="displayFileName(this)"
                            required
                        >
                        <label for="valid_id" class="cursor-pointer">
                            <div class="bg-gray-600 text-white px-6 py-2 rounded-md inline-block hover:bg-gray-700">
                                UPLOAD
                            </div>
                            <p class="mt-2 text-sm text-gray-500" id="file-name">No file chosen</p>
                        </label>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="terms" 
                            class="mr-2"
                            required
                        >
                        <span class="text-sm text-gray-700">I agree to the Terms and Conditions.</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="text-center mb-4">
                    <button 
                        type="submit" 
                        class="bg-gray-600 text-white px-12 py-3 rounded-md hover:bg-gray-700 text-lg font-semibold"
                    >
                        VERIFY
                    </button>
                </div>

                <!-- Link to Login -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Already Verified? 
                        <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Click Here to go to Homepage!</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            field.type = field.type === 'password' ? 'text' : 'password';
        }

        function displayFileName(input) {
            const fileName = input.files[0]?.name || 'No file chosen';
            document.getElementById('file-name').textContent = fileName;
        }
    </script>
</body>
</html>
