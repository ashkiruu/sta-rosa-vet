<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pets</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <nav class="bg-red-700 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                    <span class="text-red-700 font-bold">🐾</span>
                </div>
                <div>
                    <h1 class="font-bold">City Veterinary Office</h1>
                </div>
            </div>
            <a href="{{ route('dashboard') }}" class="text-white hover:underline">← Back to Dashboard</a>
        </div>
    </nav>

    <div class="container mx-auto mt-8 px-4">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">My Pets</h2>
            <a href="{{ route('pets.create') }}" class="bg-red-700 text-white px-6 py-2 rounded-lg hover:bg-red-800">
                + Add New Pet
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($pets->isEmpty())
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <div class="text-6xl mb-4">🐕</div>
                <h3 class="text-xl font-semibold mb-2">No Pets Registered Yet</h3>
                <p class="text-gray-600 mb-6">Add your first pet to start booking appointments!</p>
                <a href="{{ route('pets.create') }}" class="bg-red-700 text-white px-8 py-3 rounded-lg hover:bg-red-800 inline-block">
                    Add Your First Pet
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($pets as $pet)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                        <div class="h-48 bg-gradient-to-br from-yellow-200 to-pink-200 flex items-center justify-center text-6xl">
                            {{ $pet->Species_ID == 1 ? '🐕' : ($pet->Species_ID == 2 ? '🐈' : '🐾') }}
                        </div>
                        <div class="p-4">
                            <h3 class="text-xl font-bold mb-2">{{ $pet->Pet_Name }}</h3>
                            <p class="text-gray-600 text-sm mb-1">
                                <span class="font-semibold">Species:</span> 
                                {{ $pet->Species_ID == 1 ? 'Dog' : ($pet->Species_ID == 2 ? 'Cat' : 'Other') }}
                            </p>
                            <p class="text-gray-600 text-sm mb-1">
                                <span class="font-semibold">Sex:</span> {{ $pet->Sex }}
                            </p>
                            <p class="text-gray-600 text-sm mb-4">
                                <span class="font-semibold">Age:</span> {{ $pet->Age }} months
                            </p>
                            <div class="flex justify-between gap-2">
                                <a href="{{ route('pets.show', $pet->Pet_ID) }}" class="flex-1 text-center bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                    View
                                </a>
                                <form method="POST" action="{{ route('pets.destroy', $pet->Pet_ID) }}" class="flex-1" onsubmit="return confirm('Are you sure you want to remove this pet?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>