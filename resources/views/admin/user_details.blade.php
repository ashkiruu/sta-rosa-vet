@extends('admin.layouts.app')

@section('title', 'User Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Back Button --}}
    <div class="mb-6">
        <a href="{{ route('admin.verifications') }}" class="inline-flex items-center text-gray-600 hover:text-red-700 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Verifications
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- User Information Card --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-red-700 to-red-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white">User Information</h2>
                </div>
                
                <div class="p-6">
                    <div class="flex items-start gap-6 mb-6">
                        {{-- Profile Picture --}}
                        <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center text-3xl">
                            {{ strtoupper(substr($user->First_Name, 0, 1)) }}{{ strtoupper(substr($user->Last_Name, 0, 1)) }}
                        </div>
                        
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-800">{{ $user->First_Name }} {{ $user->Last_Name }}</h3>
                            <p class="text-gray-500">{{ $user->Email }}</p>
                            
                            {{-- Verification Status Badge --}}
                            <div class="mt-2">
                                @if($user->Verification_Status_ID == 1)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                                        ⏳ Pending Verification
                                    </span>
                                @elseif($user->Verification_Status_ID == 2)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                        ✓ Verified
                                    </span>
                                @elseif($user->Verification_Status_ID == 3)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                                        ✕ Rejected
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- User Details Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Phone Number</p>
                            <p class="text-gray-800 font-medium">{{ $user->Phone ?? 'Not provided' }}</p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Address</p>
                            <p class="text-gray-800 font-medium">{{ $user->Address ?? 'Not provided' }}</p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Date of Birth</p>
                            <p class="text-gray-800 font-medium">
                                {{ $user->Date_of_Birth ? \Carbon\Carbon::parse($user->Date_of_Birth)->format('F d, Y') : 'Not provided' }}
                            </p>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Registered On</p>
                            <p class="text-gray-800 font-medium">{{ $user->created_at->format('F d, Y') }}</p>
                        </div>
                    </div>

                    {{-- OCR Data (if available) --}}
                    @if($user->ocrData)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">ID Verification Data</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if($user->ocrData->full_name)
                                    <div class="bg-blue-50 rounded-lg p-4">
                                        <p class="text-xs text-blue-600 uppercase tracking-wider font-semibold mb-1">Full Name (from ID)</p>
                                        <p class="text-gray-800 font-medium">{{ $user->ocrData->full_name }}</p>
                                    </div>
                                @endif
                                
                                @if($user->ocrData->address)
                                    <div class="bg-blue-50 rounded-lg p-4">
                                        <p class="text-xs text-blue-600 uppercase tracking-wider font-semibold mb-1">Address (from ID)</p>
                                        <p class="text-gray-800 font-medium">{{ $user->ocrData->address }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Action Buttons --}}
                    @if($user->Verification_Status_ID == 1)
                        <div class="mt-6 pt-6 border-t border-gray-200 flex gap-4">
                            <form action="{{ route('admin.users.approve', $user->User_ID) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                                    ✓ Approve User
                                </button>
                            </form>
                            
                            <form action="{{ route('admin.users.reject', $user->User_ID) }}" method="POST" class="flex-1" 
                                  onsubmit="return confirm('Are you sure you want to reject this user?');">
                                @csrf
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg transition">
                                    ✕ Reject User
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Registered Pets Card --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-yellow-500 to-yellow-400 px-6 py-4">
                    <h2 class="text-xl font-bold text-white">Registered Pets ({{ $pets->count() }})</h2>
                </div>
                
                <div class="p-6">
                    @if($pets->isEmpty())
                        <div class="text-center py-8">
                            <div class="text-4xl mb-3">🐾</div>
                            <p class="text-gray-500">No pets registered yet</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($pets as $pet)
                                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 hover:border-yellow-300 transition">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-200 to-pink-200 rounded-full flex items-center justify-center text-2xl">
                                            @if($pet->Species_ID == 1)
                                                🐕
                                            @elseif($pet->Species_ID == 2)
                                                🐈
                                            @else
                                                🐾
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-800">{{ $pet->Pet_Name }}</h4>
                                            <p class="text-xs text-gray-500">{{ $pet->species->Species_Name ?? 'Unknown Species' }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        <div>
                                            <span class="text-gray-400 text-xs">Sex:</span>
                                            <span class="text-gray-700 ml-1">{{ $pet->Sex }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-400 text-xs">Age:</span>
                                            <span class="text-gray-700 ml-1">{{ $pet->Age }} mo</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-400 text-xs">Breed:</span>
                                            <span class="text-gray-700 ml-1">{{ $pet->Breed ?: 'Not specified' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-400 text-xs">Status:</span>
                                            <span class="text-gray-700 ml-1">{{ $pet->Reproductive_Status }}</span>
                                        </div>
                                    </div>
                                    
                                    @if($pet->Color)
                                        <div class="mt-2 text-sm">
                                            <span class="text-gray-400 text-xs">Color:</span>
                                            <span class="text-gray-700 ml-1">{{ $pet->Color }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($pet->Registration_Date)
                                        <div class="mt-2 pt-2 border-t border-gray-200">
                                            <p class="text-xs text-gray-400">
                                                Registered: {{ \Carbon\Carbon::parse($pet->Registration_Date)->format('M d, Y') }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection