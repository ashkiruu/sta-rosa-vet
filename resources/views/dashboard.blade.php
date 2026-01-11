<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <nav class="bg-red-700 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                    <span class="text-red-700 font-bold text-xl">V</span>
                </div>
                <div>
                    <h1 class="font-bold">City</h1>
                    <h2 class="font-bold">Veterinary</h2>
                    <p class="text-xs">Office</p>
                </div>
            </div>
            <div class="flex gap-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-white hover:underline">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Welcome Banner -->
    <div class="bg-yellow-400 p-8">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold mb-3">Welcome, {{ Auth::user()->First_Name }}!</h2>
            <p class="text-base mb-2">You can book appointments, check your verification status, and access your pet's certificates — all in one dashboard.</p>
            <p class="font-bold mb-6 text-lg">Batang City Vet Ako!</p>
            
            <div class="inline-block bg-white rounded-lg px-6 py-3 shadow-md">
                <span class="font-semibold text-lg">Verification Status: </span>
                <span class="bg-green-500 text-white px-4 py-2 rounded-full text-sm font-semibold ml-2">
                    @if(Auth::user()->Verification_Status_ID == 2)
                        ✓ Verified
                    @else
                        Pending
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Main Actions -->
    <div class="container mx-auto mt-12 px-4 pb-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <!-- Book Appointment -->
            <a href="{{ route('appointments.create') }}" class="bg-white border-2 border-red-600 rounded-lg p-10 text-center hover:bg-red-50 transition shadow-lg hover:shadow-xl">
                <h3 class="text-red-700 font-bold text-2xl">Book Appointment</h3>
            </a>

            <!-- Manage Pets -->
            <a href="{{ route('pets.index') }}" class="bg-white border-2 border-red-600 rounded-lg p-10 text-center hover:bg-red-50 transition shadow-lg hover:shadow-xl">
                <h3 class="text-red-700 font-bold text-2xl">Manage Pets</h3>
            </a>

            <!-- View Certificates -->
            <a href="#" class="bg-white border-2 border-red-600 rounded-lg p-10 text-center hover:bg-red-50 transition shadow-lg hover:shadow-xl">
                <h3 class="text-red-700 font-bold text-2xl">View Certificates</h3>
            </a>
        </div>
    </div>
</body>
</html>