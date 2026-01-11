@extends('layouts.admin')

@section('page_title', 'Resident Profile: ' . $user->First_Name)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold mb-4 border-b pb-2 text-blue-800">Personal Details</h3>
            <div class="space-y-3">
                <p><span class="text-gray-400 text-xs uppercase font-bold">Full Name</span><br><span class="text-gray-800">{{ $user->First_Name }} {{ $user->Middle_Name }} {{ $user->Last_Name }}</span></p>
                <p><span class="text-gray-400 text-xs uppercase font-bold">Email</span><br><span class="text-gray-800">{{ $user->Email }}</span></p>
                <p><span class="text-gray-400 text-xs uppercase font-bold">Verification Status</span><br>
                    @if($user->Verification_Status_ID == 2)
                        <span class="text-green-600 font-bold">● Verified</span>
                    @else
                        <span class="text-yellow-600 font-bold">● Pending Review</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold mb-4 border-b pb-2 text-blue-800">Owned Pets ({{ $pets->count() }})</h3>
            <ul class="divide-y">
                @forelse($pets as $pet)
                    <li class="py-2 text-gray-700">
                        <i class="fas fa-paw text-gray-400 mr-2"></i> {{ $pet->Pet_Name }} <span class="text-xs text-gray-400">({{ $pet->Breed }})</span>
                    </li>
                @empty
                    <li class="py-2 text-gray-400 italic">No pets registered yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold mb-4 border-b pb-2 text-blue-800">Uploaded Identification Document</h3>
            
            @if($user->ocrData && $user->ocrData->Document_Image_Path) 
                <div class="mb-4 text-sm text-gray-500 italic">OCR Confidence Score: {{ number_format($user->ocrData->Confidence_Score * 100, 2) }}%</div>
                <img src="{{ asset('storage/' . $user->ocrData->Document_Image_Path) }}" 
                     class="rounded-lg border w-full max-h-[500px] object-contain bg-gray-50 shadow-inner" 
                     alt="Resident ID">
            @else
                <div class="bg-gray-100 h-64 flex flex-col items-center justify-center text-gray-400 rounded-lg">
                    <i class="fas fa-id-card-alt fa-3x mb-2"></i>
                    <p>No ID image found in records.</p>
                </div>
            @endif

            <div class="mt-8 pt-6 border-t flex gap-4">
                @if($user->Verification_Status_ID == 1)
                    <form action="{{ route('admin.user.approve', $user->User_ID) }}" method="POST">
                        @csrf
                        <button class="bg-green-600 text-white px-8 py-3 rounded-lg font-bold shadow-lg hover:bg-green-700 transition">
                            Approve Resident
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.verifications') }}" class="px-8 py-3 bg-gray-100 text-gray-600 rounded-lg font-bold hover:bg-gray-200">
                    Back to Directory
                </a>
            </div>
        </div>
    </div>
</div>
@endsection