<x-dashboardheader-layout>
    @section('title', 'Dashboard')

    <div class="bg-yellow-400 p-8">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold mb-3">Welcome, {{ Auth::user()->First_Name }}!</h2>
            <p class="text-base mb-2">You can book appointments, check your verification status, and access your pet's certificates — all in one dashboard.</p>
            <p class="font-bold mb-6 text-lg">Batang City Vet Ako!</p>
            
            <div class="inline-block bg-white rounded-lg px-6 py-3 shadow-md">
                <span class="font-semibold text-lg text-gray-800">Account Status: </span>
                
                @if(Auth::user()->Verification_Status_ID == 2)
                    <span class="bg-green-500 text-white px-4 py-2 rounded-full text-sm font-semibold ml-2">✓ Verified Member</span>
                @elseif(Auth::user()->Verification_Status_ID == 1)
                    <span class="bg-blue-500 text-white px-4 py-2 rounded-full text-sm font-semibold ml-2">⏳ Pending Review</span>
                @else
                    <span class="bg-yellow-500 text-white px-4 py-2 rounded-full text-sm font-semibold ml-2">⚠️ Limited Access</span>
                    <a href="{{ route('verify.reverify') }}" class="ml-3 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition shadow-sm">
                        Verify Now
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-dashboardheader-layout>