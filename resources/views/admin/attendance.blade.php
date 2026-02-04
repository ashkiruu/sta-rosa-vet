@extends('layouts.admin')

@section('page_title', 'Attendance Logs')

@section('content')
<div class="min-h-screen py-4">
    {{-- Date Filter - Styled like the Verification Search --}}
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 mb-8">
        <form method="GET" action="{{ route('admin.attendance') }}" class="flex flex-col md:flex-row items-end gap-6">
            <div class="w-full md:flex-1">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1">Filter by Date</label>
                <div class="relative mt-2">
                    <i class="fas fa-calendar absolute left-4 top-1/2 -translate-y-1/2 text-red-500 text-xs"></i>
                    <input type="date" name="date" value="{{ $selectedDate }}" 
                           class="w-full bg-red-50/50 border-none rounded-2xl py-3 pl-10 pr-4 text-sm focus:ring-2 focus:ring-red-500 transition shadow-inner font-bold text-gray-700">
                </div>
            </div>
            <div class="flex gap-2 w-full md:w-auto">
                <button type="submit" class="flex-1 md:flex-none bg-gray-900 text-white px-8 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition shadow-md">
                    Apply Filter
                </button>
                <a href="{{ route('admin.attendance') }}" class="flex-1 md:flex-none bg-gray-100 text-gray-500 px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-200 transition text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Stats Cards --}}
    @php
        $checkedInToday = collect($todayLogs)->whereIn('status', ['checked_in', 'completed'])->count();
        $noShowToday = collect($todayLogs)->where('status', 'no_show')->count();
        $checkedInFiltered = collect($filteredLogs)->whereIn('status', ['checked_in', 'completed'])->count();
        $noShowFiltered = collect($filteredLogs)->where('status', 'no_show')->count();
    @endphp
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group hover:border-green-200 transition-all">
            <div class="w-14 h-14 bg-green-100 text-green-700 rounded-2xl flex items-center justify-center mr-5 shadow-sm group-hover:bg-green-600 group-hover:text-white transition-all">
                <i class="fas fa-check-double text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Checked In Today</p>
                <h3 class="text-2xl font-black text-gray-900 leading-none">{{ $checkedInToday }}</h3>
            </div>
        </div>
        
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group hover:border-orange-200 transition-all">
            <div class="w-14 h-14 bg-orange-100 text-orange-700 rounded-2xl flex items-center justify-center mr-5 shadow-sm group-hover:bg-orange-600 group-hover:text-white transition-all">
                <i class="fas fa-user-slash text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">No Shows Today</p>
                <h3 class="text-2xl font-black text-gray-900 leading-none">{{ $noShowToday }}</h3>
            </div>
        </div>
        
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group hover:border-blue-200 transition-all">
            <div class="w-14 h-14 bg-blue-100 text-blue-700 rounded-2xl flex items-center justify-center mr-5 shadow-sm group-hover:bg-blue-600 group-hover:text-white transition-all">
                <i class="fas fa-database text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Lifetime Logs</p>
                <h3 class="text-2xl font-black text-gray-900 leading-none">{{ count($allLogs) }}</h3>
            </div>
        </div>
        
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group hover:border-amber-200 transition-all">
            <div class="w-14 h-14 bg-amber-100 text-amber-700 rounded-2xl flex items-center justify-center mr-5 shadow-sm group-hover:bg-amber-600 group-hover:text-white transition-all">
                <i class="fas fa-calendar-day text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Active Date View</p>
                <h3 class="text-lg font-black text-gray-900 leading-none">{{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}</h3>
            </div>
        </div>
    </div>

    {{-- Attendance Table --}}
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between">
            <h2 class="text-sm font-black text-gray-900 uppercase tracking-widest">Attendance Records</h2>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Checked In: {{ $checkedInFiltered }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">No Show: {{ $noShowFiltered }}</span>
                </div>
            </div>
        </div>
        
        @if(count($filteredLogs) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">Reference</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">Pet & Owner</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">Service</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">Timing</th>
                            <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($filteredLogs as $log)
                            @php
                                $status = $log['status'] ?? 'checked_in';
                                $isNoShow = $status === 'no_show';
                                $isCompleted = $status === 'completed';
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition group {{ $isNoShow ? 'bg-orange-50/30' : '' }}">
                                <td class="px-8 py-5">
                                    <span class="font-mono text-xs font-black text-gray-900 bg-gray-100 px-3 py-1 rounded-lg">
                                        VET-{{ str_pad($log['appointment_id'], 6, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-gray-800 uppercase tracking-tight">🐾 {{ $log['pet_name'] }}</span>
                                        <span class="text-[11px] font-bold text-gray-400 uppercase italic">{{ $log['owner_name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="px-3 py-1 text-[9px] font-black uppercase tracking-widest bg-blue-100 text-blue-700 border border-blue-200 rounded-full">
                                        {{ $log['service'] }}
                                    </span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="text-center">
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Scheduled: {{ \Carbon\Carbon::parse($log['scheduled_time'])->format('h:i A') }}</p>
                                        @if($isNoShow)
                                            <p class="text-xs font-black text-orange-600 uppercase mt-1">Marked: {{ \Carbon\Carbon::parse($log['check_in_time'])->format('h:i A') }}</p>
                                        @else
                                            <p class="text-xs font-black text-green-600 uppercase mt-1">Checked-in: {{ \Carbon\Carbon::parse($log['check_in_time'])->format('h:i A') }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex justify-center">
                                        @if($isNoShow)
                                            <span class="inline-flex items-center px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest bg-orange-500 text-white shadow-sm shadow-orange-100">
                                                <i class="fas fa-user-slash mr-2"></i> No Show
                                            </span>
                                        @elseif($isCompleted)
                                            <span class="inline-flex items-center px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest bg-blue-500 text-white shadow-sm shadow-blue-100">
                                                <i class="fas fa-check-double mr-2"></i> Completed
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest bg-green-500 text-white shadow-sm shadow-green-100">
                                                <i class="fas fa-check-circle mr-2"></i> Checked In
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-8 py-24 text-center">
                <div class="flex flex-col items-center">
                    <div class="w-20 h-20 bg-gray-50 rounded-[2rem] flex items-center justify-center mb-4 border border-gray-100">
                        <i class="fas fa-clipboard-list text-3xl text-gray-200"></i>
                    </div>
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">No Records Found</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase mt-2">No attendance records for {{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection