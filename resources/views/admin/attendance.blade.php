@extends('layouts.admin')

@section('page_title', 'Attendance Logs')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Attendance Logs</h1>
                <p class="text-sm text-gray-600">View check-in records from QR code scans</p>
            </div>
            <div class="flex gap-3">
                <span class="bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200 text-sm font-medium text-gray-700">
                    Total Check-ins Today: {{ count($todayLogs) }}
                </span>
            </div>
        </div>

        {{-- Date Filter --}}
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <form method="GET" action="{{ route('admin.attendance') }}" class="flex items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Date</label>
                    <input type="date" name="date" value="{{ $selectedDate }}" 
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
                    Filter
                </button>
                <a href="{{ route('admin.attendance') }}" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition">
                    Reset
                </a>
            </form>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-2xl">
                        ✅
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Today's Check-ins</p>
                        <p class="text-2xl font-bold text-gray-800">{{ count($todayLogs) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-2xl">
                        📊
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total All Time</p>
                        <p class="text-2xl font-bold text-gray-800">{{ count($allLogs) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center text-2xl">
                        📅
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Showing Date</p>
                        <p class="text-lg font-bold text-gray-800">{{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Attendance Table --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Check-in Records</h2>
            </div>
            
            @if(count($filteredLogs) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pet</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Owner</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scheduled</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check-in Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($filteredLogs as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-mono text-sm font-semibold text-gray-900">
                                            VET-{{ str_pad($log['appointment_id'], 6, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900">🐾 {{ $log['pet_name'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900">{{ $log['owner_name'] }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                            {{ $log['service'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($log['scheduled_date'])->format('M d') }} at 
                                        {{ \Carbon\Carbon::parse($log['scheduled_time'])->format('h:i A') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-semibold text-green-600">
                                            {{ \Carbon\Carbon::parse($log['check_in_time'])->format('h:i A') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                            ✓ Checked In
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <div class="text-4xl mb-4">📋</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Check-ins Found</h3>
                    <p class="text-gray-500">No attendance records for {{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection