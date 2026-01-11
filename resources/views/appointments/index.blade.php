<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments</title>
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
            <h2 class="text-2xl font-bold">My Appointments</h2>
            <a href="{{ route('appointments.create') }}" class="bg-red-700 text-white px-6 py-2 rounded-lg hover:bg-red-800">
                + Book New Appointment
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($appointments->isEmpty())
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <div class="text-6xl mb-4">📅</div>
                <h3 class="text-xl font-semibold mb-2">No Appointments Yet</h3>
                <p class="text-gray-600 mb-6">Book your first appointment for your pet!</p>
                <a href="{{ route('appointments.create') }}" class="bg-red-700 text-white px-8 py-3 rounded-lg hover:bg-red-800 inline-block">
                    Book Appointment
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4">
                @foreach($appointments as $appointment)
                    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <h3 class="text-xl font-bold">{{ $appointment->pet->Pet_Name }}</h3>
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold
                                        {{ $appointment->Status == 'Pending' ? 'bg-yellow-200 text-yellow-800' : '' }}
                                        {{ $appointment->Status == 'Confirmed' ? 'bg-green-200 text-green-800' : '' }}
                                        {{ $appointment->Status == 'Cancelled' ? 'bg-red-200 text-red-800' : '' }}
                                        {{ $appointment->Status == 'Completed' ? 'bg-blue-200 text-blue-800' : '' }}">
                                        {{ $appointment->Status }}
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                                    <div>
                                        <p class="font-semibold text-gray-700">Service:</p>
                                        <p>{{ $appointment->service->Service_Name }}</p>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-700">Date & Time:</p>
                                        <p>{{ $appointment->Date->format('M d, Y') }} at {{ date('g:i A', strtotime($appointment->Time)) }}</p>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-700">Location:</p>
                                        <p>{{ $appointment->Location }}</p>
                                    </div>
                                    @if($appointment->Special_Notes)
                                        <div class="col-span-2">
                                            <p class="font-semibold text-gray-700">Notes:</p>
                                            <p>{{ $appointment->Special_Notes }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex gap-2">
                                @if($appointment->Status == 'Pending')
                                    <form method="POST" action="{{ route('appointments.cancel', $appointment->Appointment_ID) }}" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                        @csrf
                                        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 text-sm">
                                            Cancel
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>