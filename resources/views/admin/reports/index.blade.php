@extends('layouts.admin')

@section('page_title', 'Reports')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Weekly Reports</h1>
                <p class="text-sm text-gray-600">Generate and manage weekly service reports</p>
            </div>
        </div>

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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Generate Report Panel --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-blue-600 text-white px-6 py-4">
                        <h2 class="text-lg font-bold">📊 Generate New Report</h2>
                        <p class="text-blue-200 text-sm">Create a weekly report</p>
                    </div>
                    
                    <div class="p-6">
                        <form action="{{ route('admin.reports.generate') }}" method="POST">
                            @csrf
                            
                           

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2"> Select Custom Date Range</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="text-xs text-gray-500">Start Date</label>
                                        <input type="date" name="custom_start" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">End Date</label>
                                        <input type="date" name="custom_end" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                                    </div>
                                </div>
                                
                            </div>
                            
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Generate Report
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Quick Stats --}}
                <div class="bg-white rounded-xl shadow-lg overflow-hidden mt-6">
                    <div class="bg-gray-800 text-white px-6 py-4">
                        <h2 class="text-lg font-bold">📈 This Week's Stats</h2>
                    </div>
                    <div class="p-6">
                        @php
                            $weekStart = \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY);
                            $weekEnd = \Carbon\Carbon::now()->endOfWeek(\Carbon\Carbon::SUNDAY);
                            
                            $thisWeekTotal = \App\Models\Appointment::whereBetween('Date', [$weekStart, $weekEnd])->count();
                            $thisWeekCompleted = \App\Models\Appointment::whereBetween('Date', [$weekStart, $weekEnd])
                                ->where('Status', 'Completed')->count();
                            $thisWeekPending = \App\Models\Appointment::whereBetween('Date', [$weekStart, $weekEnd])
                                ->where('Status', 'Pending')->count();
                        @endphp
                        
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Total Appointments</span>
                                <span class="text-2xl font-bold text-blue-600">{{ $thisWeekTotal }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Completed</span>
                                <span class="text-2xl font-bold text-green-600">{{ $thisWeekCompleted }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Pending</span>
                                <span class="text-2xl font-bold text-yellow-600">{{ $thisWeekPending }}</span>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-4 border-t">
                            <p class="text-xs text-gray-400">
                                Week {{ $weekStart->weekOfYear }}: {{ $weekStart->format('M d') }} - {{ $weekEnd->format('M d, Y') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Report Types Info --}}
                <div class="bg-white rounded-xl shadow-lg overflow-hidden mt-6">
                    <div class="bg-yellow-500 text-white px-6 py-4">
                        <h2 class="text-lg font-bold">ℹ️ Report Types</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="border-l-4 border-red-500 pl-4">
                            <h3 class="font-bold text-red-700">Anti-Rabies Vaccination</h3>
                            <p class="text-xs text-gray-500 mt-1">Client name, address, civil status, years of residency, pet details</p>
                        </div>
                        <div class="border-l-4 border-green-500 pl-4">
                            <h3 class="font-bold text-green-700">Routine Services</h3>
                            <p class="text-xs text-gray-500 mt-1">Client name, barangay, birthdate, contact, service rendered, pet details</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Reports List --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gray-800 text-white px-6 py-4">
                        <h2 class="text-lg font-bold">📋 Generated Reports</h2>
                        <p class="text-gray-300 text-sm">View and download past reports</p>
                    </div>
                    
                    @if(count($reports) > 0)
                        <div class="divide-y divide-gray-200">
                            @foreach($reports as $report)
                                <div class="p-6 hover:bg-gray-50">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-bold text-lg text-gray-800">
                                                {{ $report['report_number'] }}
                                            </h3>
                                            <p class="text-sm text-gray-500 mt-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    Week {{ $report['week_number'] }}, {{ $report['year'] }}
                                                </span>
                                                <span class="ml-2">
                                                    {{ \Carbon\Carbon::parse($report['start_date'])->format('M d') }} - 
                                                    {{ \Carbon\Carbon::parse($report['end_date'])->format('M d, Y') }}
                                                </span>
                                            </p>
                                            <p class="text-xs text-gray-400 mt-2">
                                                Generated {{ \Carbon\Carbon::parse($report['generated_at'])->diffForHumans() }} by {{ $report['generated_by'] }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    {{-- Download Buttons - Use specific report IDs --}}
                                    <div class="mt-4 flex flex-wrap gap-3">
                                        @if(!empty($report['anti_rabies_id']))
                                        <a href="{{ route('admin.reports.anti-rabies', $report['anti_rabies_id']) }}" 
                                           target="_blank"
                                           class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Anti-Rabies Report
                                        </a>
                                        @endif
                                        
                                        @if(!empty($report['routine_services_id']))
                                        <a href="{{ route('admin.reports.routine-services', $report['routine_services_id']) }}" 
                                           target="_blank"
                                           class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Routine Services Report
                                        </a>
                                        @endif
                                        
                                        <form action="{{ route('admin.reports.delete', $report['id']) }}" method="POST" class="inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this report?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-12 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-600 mb-2">No Reports Generated Yet</h3>
                            <p class="text-gray-400">Generate your first weekly report using the form on the left.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection