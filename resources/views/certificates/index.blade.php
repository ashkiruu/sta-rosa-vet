<x-dashboardheader-layout>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>My Certificates</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100 min-h-screen">

        <!-- Page Header -->
        <div class="bg-yellow-400 p-6">
            <div class="container mx-auto">
                <h2 class="text-2xl font-bold">My Certificates</h2>
                <p class="text-sm">View and download your pet's vaccination and treatment certificates</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container mx-auto mt-8 px-4 pb-12">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                    {{ session('error') }}
                </div>
            @endif

            @if(count($certificates) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($certificates as $certificate)
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition">
                            <!-- Certificate Header -->
                            <div class="bg-gradient-to-r from-red-600 to-red-700 text-white p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-2xl">
                                        📜
                                    </div>
                                    <div>
                                        <p class="text-xs text-red-200">Certificate No.</p>
                                        <p class="font-bold text-lg">{{ $certificate['certificate_number'] }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Certificate Body -->
                            <div class="p-5">
                                <!-- Pet Info -->
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-xl">
                                        🐾
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800">{{ $certificate['pet_name'] }}</p>
                                        <p class="text-sm text-gray-500">{{ $certificate['pet_breed'] }} · {{ $certificate['animal_type'] }}</p>
                                    </div>
                                </div>
                                
                                <!-- Service Info -->
                                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-lg">💉</span>
                                        <span class="font-semibold text-gray-700">{{ $certificate['service_type'] }}</span>
                                    </div>
                                    <div class="text-sm text-gray-600 space-y-1">
                                        <p><span class="text-gray-400">Vaccine:</span> {{ $certificate['vaccine_used'] }}</p>
                                        <p><span class="text-gray-400">Date:</span> {{ date('M d, Y', strtotime($certificate['vaccination_date'])) }}</p>
                                        @if(!empty($certificate['next_vaccination_date']))
                                            <p><span class="text-gray-400">Next Due:</span> 
                                                <span class="text-orange-600 font-medium">{{ date('M d, Y', strtotime($certificate['next_vaccination_date'])) }}</span>
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Veterinarian -->
                                <div class="text-sm text-gray-500 mb-4">
                                    <p><span class="text-gray-400">Veterinarian:</span> {{ $certificate['veterinarian_name'] }}</p>
                                    <p><span class="text-gray-400">License:</span> {{ $certificate['license_number'] }}</p>
                                </div>
                                
                                <!-- Status Badge -->
                                <div class="flex items-center justify-between">
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                        ✅ Verified
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        Issued: {{ date('M d, Y', strtotime($certificate['approved_at'])) }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Certificate Footer -->
                            <div class="border-t bg-gray-50 p-4">
                                <a href="{{ route('certificates.download', $certificate['id']) }}" 
                                target="_blank"
                                class="w-full bg-red-600 hover:bg-red-700 text-white py-3 px-4 rounded-lg font-semibold text-center flex items-center justify-center gap-2 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    View / Download Certificate
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="bg-white rounded-xl shadow-lg p-12 text-center max-w-lg mx-auto">
                    <div class="text-6xl mb-4">📋</div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">No Certificates Yet</h3>
                    <p class="text-gray-500 mb-6">
                        Certificates are generated after your pet completes a vaccination or treatment appointment.
                    </p>
                    <a href="{{ route('appointments.create') }}" 
                    class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Book an Appointment
                    </a>
                </div>
                
                <!-- Info Cards -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                    <div class="bg-white rounded-lg p-6 text-center shadow">
                        <div class="text-3xl mb-3">1️⃣</div>
                        <h4 class="font-semibold text-gray-800 mb-2">Book Appointment</h4>
                        <p class="text-sm text-gray-500">Schedule a vaccination or treatment for your pet</p>
                    </div>
                    <div class="bg-white rounded-lg p-6 text-center shadow">
                        <div class="text-3xl mb-3">2️⃣</div>
                        <h4 class="font-semibold text-gray-800 mb-2">Visit the Clinic</h4>
                        <p class="text-sm text-gray-500">Bring your pet on the scheduled date</p>
                    </div>
                    <div class="bg-white rounded-lg p-6 text-center shadow">
                        <div class="text-3xl mb-3">3️⃣</div>
                        <h4 class="font-semibold text-gray-800 mb-2">Get Certificate</h4>
                        <p class="text-sm text-gray-500">Download your official certificate here</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-6 mt-auto">
            <div class="container mx-auto px-4 text-center">
                <p class="text-sm text-gray-400">© {{ date('Y') }} City Veterinary Office. All rights reserved.</p>
            </div>
        </footer>
    </body>
    </html>
</x-dashboardheader-layout>