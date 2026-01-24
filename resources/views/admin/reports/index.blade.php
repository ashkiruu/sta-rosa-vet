@extends('layouts.admin')

@section('page_title', 'Reports')

@section('content')
<div class="min-h-screen py-4">
    {{-- Session Alerts --}}
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-xl mb-6 shadow-sm flex items-center">
            <i class="fas fa-check-circle mr-3"></i>
            <span class="text-[10px] font-black uppercase tracking-widest">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-6 shadow-sm flex items-center">
            <i class="fas fa-exclamation-triangle mr-3"></i>
            <span class="text-[10px] font-black uppercase tracking-widest">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Controls & Stats --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Generate Report Panel --}}
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-blue-600 px-8 py-6">
                    <h2 class="text-xs font-black text-white uppercase tracking-[0.2em]">Create Report</h2>
                    <p class="text-[10px] text-blue-100 uppercase font-bold mt-1">Configure parameters</p>
                </div>
                
                <div class="p-8">
                    <form action="{{ route('admin.reports.generate') }}" method="POST">
                        @csrf
                        <div class="mb-6">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Custom Date Range</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[9px] font-bold text-gray-400 uppercase ml-1">Start Date</label>
                                    <input type="date" name="custom_start" 
                                           class="w-full mt-1 px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 text-sm font-bold text-gray-700">
                                </div>
                                <div>
                                    <label class="text-[9px] font-bold text-gray-400 uppercase ml-1">End Date</label>
                                    <input type="date" name="custom_end" 
                                           class="w-full mt-1 px-4 py-3 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-blue-500 text-sm font-bold text-gray-700">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full bg-gray-900 hover:bg-blue-600 text-white py-4 px-6 rounded-[1.5rem] font-black text-xs uppercase tracking-[0.15em] transition-all shadow-lg flex items-center justify-center gap-3 group">
                            <i class="fas fa-file-export group-hover:rotate-12 transition-transform"></i>
                            Generate Report
                        </button>
                    </form>
                </div>
            </div>

            {{-- Quick Stats --}}
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-900 px-8 py-5">
                    <h2 class="text-[10px] font-black text-white uppercase tracking-[0.2em]">Real-Time Stats</h2>
                </div>
                <div class="p-8">
                    @php
                        $weekStart = \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY);
                        $weekEnd = \Carbon\Carbon::now()->endOfWeek(\Carbon\Carbon::SUNDAY);
                        $thisWeekTotal = \App\Models\Appointment::whereBetween('Date', [$weekStart, $weekEnd])->count();
                        $thisWeekCompleted = \App\Models\Appointment::whereBetween('Date', [$weekStart, $weekEnd])->where('Status', 'Completed')->count();
                        $thisWeekPending = \App\Models\Appointment::whereBetween('Date', [$weekStart, $weekEnd])->where('Status', 'Pending')->count();
                    @endphp
                    
                    <div class="space-y-6">
                        <div class="flex justify-between items-end border-b border-gray-50 pb-4">
                            <div>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Appointments</p>
                                <p class="text-xs font-bold text-gray-600">Total this week</p>
                            </div>
                            <span class="text-3xl font-black text-blue-600 leading-none">{{ $thisWeekTotal }}</span>
                        </div>
                        <div class="flex justify-between items-end border-b border-gray-50 pb-4">
                            <div>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Success Rate</p>
                                <p class="text-xs font-bold text-gray-600">Completed</p>
                            </div>
                            <span class="text-3xl font-black text-green-600 leading-none">{{ $thisWeekCompleted }}</span>
                        </div>
                        <div class="flex justify-between items-end border-b border-gray-50 pb-4">
                            <div>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Queue</p>
                                <p class="text-xs font-bold text-gray-600">Pending</p>
                            </div>
                            <span class="text-3xl font-black text-amber-500 leading-none">{{ $thisWeekPending }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex items-center gap-2 bg-blue-50 p-3 rounded-xl border border-blue-100">
                        <i class="fas fa-calendar-alt text-blue-400 text-xs"></i>
                        <p class="text-[9px] font-black text-blue-700 uppercase">
                            Wk {{ $weekStart->weekOfYear }}: {{ $weekStart->format('M d') }} - {{ $weekEnd->format('M d, Y') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Info Panel --}}
            <div class="bg-gray-50 rounded-[2.5rem] p-8 border border-gray-100">
                <h2 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                    <i class="fas fa-info-circle"></i> Schema Definitions
                </h2>
                <div class="space-y-6">
                    <div class="group">
                        <h3 class="text-[11px] font-black text-red-600 uppercase tracking-tight">Anti-Rabies</h3>
                        <p class="text-[10px] font-bold text-gray-500 leading-relaxed mt-1">Full demographics, years of residency, and detailed pet biometric records.</p>
                    </div>
                    <div class="group">
                        <h3 class="text-[11px] font-black text-green-600 uppercase tracking-tight">Routine Services</h3>
                        <p class="text-[10px] font-bold text-gray-500 leading-relaxed mt-1">Barangay-level tracking, service rendering logs, and owner contact metrics.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Reports List --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
                <div class="bg-gray-900 px-8 py-6 flex justify-between items-center">
                    <div>
                        <h2 class="text-xs font-black text-white uppercase tracking-[0.2em]">Generated Archive</h2>
                        <p class="text-[10px] text-gray-400 uppercase font-bold mt-1">Vault of past service reports</p>
                    </div>
                    <i class="fas fa-database text-gray-700 text-xl"></i>
                </div>
                
                <div class="flex-grow overflow-y-auto custom-scrollbar">
                    @if(count($reports) > 0)
                        <div class="divide-y divide-gray-50">
                            @foreach($reports as $report)
                                <div class="p-8 hover:bg-gray-50/80 transition-all group">
                                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                                        <div class="flex-grow">
                                            <div class="flex items-center gap-3 mb-2">
                                                <h3 class="font-black text-base text-gray-900 uppercase tracking-tight">{{ $report['report_number'] }}</h3>
                                                <span class="px-3 py-0.5 rounded-full text-[9px] font-black bg-blue-50 text-blue-600 uppercase tracking-widest border border-blue-100">
                                                    Week {{ $report['week_number'] }}, {{ $report['year'] }}
                                                </span>
                                            </div>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                                <i class="fas fa-clock"></i>
                                                {{ \Carbon\Carbon::parse($report['start_date'])->format('M d') }} - 
                                                {{ \Carbon\Carbon::parse($report['end_date'])->format('M d, Y') }}
                                                <span class="text-gray-200">|</span>
                                                <span class="text-gray-300 italic">Created {{ \Carbon\Carbon::parse($report['generated_at'])->diffForHumans() }} by {{ $report['generated_by'] }}</span>
                                            </p>
                                        </div>
                                        
                                        <div class="flex flex-wrap gap-2">
                                            @if(!empty($report['anti_rabies_id']))
                                                <a href="{{ route('admin.reports.anti-rabies', $report['anti_rabies_id']) }}" 
                                                   target="_blank"
                                                   class="px-4 py-2.5 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all shadow-sm flex items-center gap-2">
                                                    <i class="fas fa-syringe"></i> Anti-Rabies
                                                </a>
                                            @endif
                                            
                                            @if(!empty($report['routine_services_id']))
                                                <a href="{{ route('admin.reports.routine-services', $report['routine_services_id']) }}" 
                                                   target="_blank"
                                                   class="px-4 py-2.5 bg-green-50 text-green-600 hover:bg-green-600 hover:text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all shadow-sm flex items-center gap-2">
                                                    <i class="fas fa-stethoscope"></i> Routine
                                                </a>
                                            @endif
                                            
                                            <form action="{{ route('admin.reports.delete', $report['id']) }}" method="POST" class="inline" 
                                                  onsubmit="return confirm('Confirm deletion of this record?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-2.5 bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-600 rounded-xl transition-all shadow-sm">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-24 text-center">
                            <div class="w-20 h-20 bg-gray-50 rounded-[2rem] flex items-center justify-center mb-6 mx-auto border border-gray-100">
                                <i class="fas fa-folder-open text-3xl text-gray-200"></i>
                            </div>
                            <h3 class="text-xs font-black text-gray-900 uppercase tracking-[0.2em]">Archive Empty</h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase mt-2 italic">Use the generator to create reports</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection