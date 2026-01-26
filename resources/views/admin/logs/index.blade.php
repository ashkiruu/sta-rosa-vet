@extends('layouts.admin')

@section('page_title', 'Activity Logs')

@section('content')
<div class="min-h-screen py-4">
    {{-- Advanced Filters --}}
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8 mb-8">
        <form action="{{ route('admin.logs') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
            {{-- Admin Filter --}}
            <div class="space-y-2">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Operator</label>
                <div class="relative">
                    <select name="admin_id" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-xs font-bold text-gray-700 focus:ring-2 focus:ring-purple-500 transition-all appearance-none">
                        <option value="">All Personnel</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->User_ID }}" {{ request('admin_id') == $admin->User_ID ? 'selected' : '' }}>
                                {{ $admin->user->First_Name ?? 'Unknown' }} {{ $admin->user->Last_Name ?? '' }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-300 text-[10px] pointer-events-none"></i>
                </div>
            </div>

            {{-- Action Filter --}}
            <div class="space-y-2">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Event Type</label>
                <div class="relative">
                    <select name="action" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-xs font-bold text-gray-700 focus:ring-2 focus:ring-purple-500 transition-all appearance-none">
                        <option value="">All Events</option>
                        @foreach($actionTypes as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                {{ str_replace('_', ' ', $action) }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-300 text-[10px] pointer-events-none"></i>
                </div>
            </div>

            {{-- Date From --}}
            <div class="space-y-2">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Date Start</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-xs font-bold text-gray-700 focus:ring-2 focus:ring-purple-500 transition-all">
            </div>

            {{-- Date To --}}
            <div class="space-y-2">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Date End</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 text-xs font-bold text-gray-700 focus:ring-2 focus:ring-purple-500 transition-all">
            </div>

            {{-- Filter Actions --}}
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-gray-900 hover:bg-purple-600 text-white h-[44px] rounded-xl font-black text-[10px] uppercase tracking-widest transition-all shadow-md">
                    Apply Filter
                </button>
                <a href="{{ route('admin.logs') }}" class="w-[44px] h-[44px] bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-xl flex items-center justify-center transition-all">
                    <i class="fas fa-undo-alt text-xs"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- Activity Log Table --}}
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
            <h3 class="text-[10px] font-black text-gray-700 uppercase tracking-[0.2em] flex items-center">
                <span class="w-2 h-2 bg-purple-500 rounded-full mr-2 animate-pulse"></span>
                Event Registry
            </h3>
            <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest bg-white px-4 py-1.5 rounded-full border border-gray-100">
                {{ number_format($logs->total()) }} Records Found
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-white">
                        <th class="px-8 py-5 text-left text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Timestamp</th>
                        <th class="px-8 py-5 text-left text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Operator</th>
                        <th class="px-8 py-5 text-left text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Action Type</th>
                        <th class="px-8 py-5 text-left text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Data Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50/80 transition-all">
                        <td class="px-8 py-6 whitespace-nowrap">
                            <div class="text-[11px] font-black text-gray-900 uppercase tracking-tighter">{{ $log->Timestamp->format('M d, Y') }}</div>
                            <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ $log->Timestamp->format('h:i:s A') }}</div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-9 w-9 rounded-xl bg-gray-900 flex items-center justify-center text-white text-[10px] font-black shadow-sm mr-3">
                                    {{ substr($log->user->First_Name ?? 'A', 0, 1) }}
                                </div>
                                <span class="text-[11px] font-black text-gray-700 uppercase tracking-tight">
                                    {{ $log->user->First_Name ?? 'Unknown' }} {{ $log->user->Last_Name ?? '' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            @php
                                $actionClass = 'bg-gray-100 text-gray-500 border-gray-200';
                                $icon = 'fa-circle';
                                if (str_contains($log->Action, 'APPROVED') || str_contains($log->Action, 'CREATED')) {
                                    $actionClass = 'bg-green-50 text-green-700 border-green-100';
                                    $icon = 'fa-plus-circle';
                                } elseif (str_contains($log->Action, 'REJECTED') || str_contains($log->Action, 'DELETED') || str_contains($log->Action, 'REMOVED')) {
                                    $actionClass = 'bg-red-50 text-red-700 border-red-100';
                                    $icon = 'fa-exclamation-triangle';
                                } elseif (str_contains($log->Action, 'UPDATED') || str_contains($log->Action, 'MODIFIED')) {
                                    $actionClass = 'bg-blue-50 text-blue-700 border-blue-100';
                                    $icon = 'fa-edit';
                                } elseif (str_contains($log->Action, 'GENERATED')) {
                                    $actionClass = 'bg-purple-50 text-purple-700 border-purple-100';
                                    $icon = 'fa-cog';
                                }
                            @endphp
                            <span class="inline-flex items-center px-4 py-1.5 text-[9px] font-black rounded-full border {{ $actionClass }} uppercase tracking-widest">
                                <i class="fas {{ $icon }} mr-2"></i>
                                {{ str_replace('_', ' ', $log->Action) }}
                            </span>
                        </td>
                        <td class="px-8 py-6">
                            <div class="group relative">
                                <p class="text-[11px] font-bold text-gray-500 leading-relaxed max-w-sm truncate uppercase tracking-tight" title="{{ $log->Description }}">
                                    {{ $log->Description ?? 'N/A' }}
                                </p>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-8 py-24 text-center">
                            <div class="w-20 h-20 bg-gray-50 rounded-[2rem] flex items-center justify-center mb-6 mx-auto border border-gray-100">
                                <i class="fas fa-clipboard-list text-3xl text-gray-200"></i>
                            </div>
                            <h3 class="text-xs font-black text-gray-900 uppercase tracking-[0.2em]">No Events Found</h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase mt-2 italic">Refine your filters or wait for system activity</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div class="px-8 py-6 border-t border-gray-50 bg-gray-50/30">
            {{ $logs->withQueryString()->links() }}
        </div>
        @endif
    </div>

    {{-- Action Legend --}}
    <div class="mt-8 bg-gray-600 rounded-[2.5rem] p-8 text-white relative overflow-hidden">
        <div class="relative z-10">
            <h4 class="text-[10px] font-black uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
                <i class="fas fa-layer-group text-purple-400"></i> Event Classification Legend
            </h4>
            <div class="flex flex-wrap gap-4">
                <div class="bg-white/5 border border-white/10 px-4 py-3 rounded-2xl flex items-center gap-3">
                    <span class="w-3 h-3 bg-green-500 rounded-full shadow-[0_0_10px_rgba(34,197,94,0.5)]"></span>
                    <span class="text-[9px] font-black uppercase tracking-widest text-white">Approval / Creation</span>
                </div>
                <div class="bg-white/5 border border-white/10 px-4 py-3 rounded-2xl flex items-center gap-3">
                    <span class="w-3 h-3 bg-red-500 rounded-full shadow-[0_0_10px_rgba(239,68,68,0.5)]"></span>
                    <span class="text-[9px] font-black uppercase tracking-widest text-white">Deletion / Rejection</span>
                </div>
                <div class="bg-white/5 border border-white/10 px-4 py-3 rounded-2xl flex items-center gap-3">
                    <span class="w-3 h-3 bg-blue-500 rounded-full shadow-[0_0_10px_rgba(59,130,246,0.5)]"></span>
                    <span class="text-[9px] font-black uppercase tracking-widest text-white">Modification</span>
                </div>
                <div class="bg-white/5 border border-white/10 px-4 py-3 rounded-2xl flex items-center gap-3">
                    <span class="w-3 h-3 bg-purple-500 rounded-full shadow-[0_0_10px_rgba(168,85,247,0.5)]"></span>
                    <span class="text-[9px] font-black uppercase tracking-widest text-white">System Generation</span>
                </div>
            </div>
        </div>
        <i class="fas fa-fingerprint absolute -right-4 -bottom-4 text-9xl text-white/5 -rotate-12"></i>
    </div>
</div>
@endsection