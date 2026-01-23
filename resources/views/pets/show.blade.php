<x-dashboardheader-layout>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $pet->Pet_Name }} - Pet Details</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100">

        <div class="container mx-auto mt-8 px-4 max-w-2xl pb-12">
            
            {{-- Breadcrumb --}}
            <div class="flex items-center gap-2 mb-6 text-sm">
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-red-700 transition">Dashboard</a>
                <span class="text-gray-400">/</span>
                <a href="{{ route('pets.index') }}" class="text-gray-500 hover:text-red-700 transition">My Pets</a>
                <span class="text-gray-400">/</span>
                <span class="text-red-700 font-semibold">{{ $pet->Pet_Name }}</span>
            </div>

            {{-- Main Card --}}
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                
                {{-- Header with Pet Avatar --}}
                <div class="h-48 bg-gradient-to-br from-yellow-200 to-pink-200 flex items-center justify-center relative">
                    <div class="text-8xl">
                        @if($pet->Species_ID == 1)
                            🐕
                        @elseif($pet->Species_ID == 2)
                            🐈
                        @else
                            🐾
                        @endif
                    </div>
                    
                    {{-- Status Badge --}}
                    <div class="absolute top-4 right-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                            {{ $pet->Reproductive_Status == 'Intact' ? 'bg-blue-500 text-white' : '' }}
                            {{ $pet->Reproductive_Status == 'Neutered' ? 'bg-green-500 text-white' : '' }}
                            {{ $pet->Reproductive_Status == 'Spayed' ? 'bg-purple-500 text-white' : '' }}
                            {{ $pet->Reproductive_Status == 'Unknown' ? 'bg-gray-500 text-white' : '' }}
                        ">
                            {{ $pet->Reproductive_Status }}
                        </span>
                    </div>
                </div>

                {{-- Pet Info --}}
                <div class="p-6">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $pet->Pet_Name }}</h1>
                    <p class="text-gray-500 mb-6">Registered on {{ $pet->Registration_Date ? \Carbon\Carbon::parse($pet->Registration_Date)->format('F d, Y') : 'N/A' }}</p>

                    {{-- Info Grid --}}
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Species</p>
                            <p class="text-lg font-semibold text-gray-800">{{ $pet->species->Species_Name ?? 'Unknown' }}</p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Sex</p>
                            <p class="text-lg font-semibold text-gray-800">
                                {{ $pet->Sex == 'Male' ? '♂ Male' : '♀ Female' }}
                            </p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Age</p>
                            <p class="text-lg font-semibold text-gray-800">
                                @if($pet->Age >= 12)
                                    {{ floor($pet->Age / 12) }} year{{ floor($pet->Age / 12) > 1 ? 's' : '' }}
                                    @if($pet->Age % 12 > 0)
                                        {{ $pet->Age % 12 }} month{{ ($pet->Age % 12) > 1 ? 's' : '' }}
                                    @endif
                                @else
                                    {{ $pet->Age }} month{{ $pet->Age > 1 ? 's' : '' }}
                                @endif
                            </p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Breed</p>
                            <p class="text-lg font-semibold text-gray-800">{{ $pet->Breed ?: 'Not specified' }}</p>
                        </div>
                    </div>

                    {{-- Date of Birth --}}
                    @if($pet->Date_of_Birth)
                        <div class="bg-gray-50 rounded-xl p-4 mb-6">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Estimated Date of Birth</p>
                            <p class="text-lg font-semibold text-gray-800">{{ \Carbon\Carbon::parse($pet->Date_of_Birth)->format('F d, Y') }}</p>
                        </div>
                    @endif

                    {{-- Medical History --}}
                    @if($pet->Medical_History)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
                            <p class="text-xs text-yellow-600 uppercase tracking-wider font-semibold mb-2">📋 Medical History</p>
                            <p class="text-gray-700">{{ $pet->Medical_History }}</p>
                        </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="flex gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('pets.index') }}" 
                           class="flex-1 text-center bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:bg-gray-300 transition">
                            ← Back to My Pets
                        </a>
                        
                        <a href="{{ route('appointments.create') }}" 
                           class="flex-1 text-center bg-red-700 text-white px-6 py-3 rounded-xl font-semibold hover:bg-red-800 transition">
                            Book Appointment
                        </a>
                    </div>
                    
                    {{-- Delete Button --}}
                    <form method="POST" action="{{ route('pets.destroy', $pet->Pet_ID) }}" 
                          class="mt-4"
                          onsubmit="return confirm('Are you sure you want to remove {{ $pet->Pet_Name }}? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full text-center text-red-500 hover:text-red-700 text-sm font-medium py-2 transition">
                            Remove this pet
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </body>
    </html>
</x-dashboardheader-layout>