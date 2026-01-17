@extends('layouts.admin')
@section('page_title', 'Activity Logs')
@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Admin Activity Logs</h2>
    <p class="text-gray-500 mt-1">Monitor all actions performed by staff and administrators</p>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
    <form action="{{ route('admin.logs') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        {{-- Admin Filter --}}
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Filter by Admin</label>
            <select name="admin_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                <option value="">All Admins</option>
                @foreach($admins as $admin)
                    <option value="{{ $admin->User_ID }}" {{ request('admin_id') == $admin->User_ID ? 'selected' : '' }}>
                        {{ $admin->user->First_Name ?? 'Unknown' }} {{ $admin->user->Last_Name ?? '' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Action Filter --}}
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Filter by Action</label>
            <select name="action" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                <option value="">All Actions</option>
                @foreach($actionTypes as $action)
                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                        {{ str_replace('_', ' ', $action) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Date From --}}
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">From Date</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        {{-- Date To --}}
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">To Date</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
        </div>

        {{-- Buttons --}}
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition text-sm">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="{{ route('admin.logs') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

{{-- Activity Log Table --}}
<div class="bg-white rounded-xl shadow-sm border">
    <div class="p-4 border-b bg-gray-50 flex items-center justify-between">
        <h3 class="font-semibold text-gray-700">
            <i class="fas fa-history text-purple-500 mr-2"></i> Activity Log
        </h3>
        <span class="text-sm text-gray-500">{{ $logs->total() }} total records</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $log->Timestamp->format('M d, Y') }}</div>
                        <div class="text-xs text-gray-500">{{ $log->Timestamp->format('h:i:s A') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold mr-2">
                                {{ substr($log->user->First_Name ?? 'A', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">
                                    {{ $log->user->First_Name ?? 'Unknown' }} {{ $log->user->Last_Name ?? '' }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $actionClass = 'bg-gray-100 text-gray-700';
                            if (str_contains($log->Action, 'APPROVED') || str_contains($log->Action, 'CREATED')) {
                                $actionClass = 'bg-green-100 text-green-700';
                            } elseif (str_contains($log->Action, 'REJECTED') || str_contains($log->Action, 'DELETED') || str_contains($log->Action, 'REMOVED')) {
                                $actionClass = 'bg-red-100 text-red-700';
                            } elseif (str_contains($log->Action, 'UPDATED') || str_contains($log->Action, 'MODIFIED')) {
                                $actionClass = 'bg-blue-100 text-blue-700';
                            } elseif (str_contains($log->Action, 'GENERATED')) {
                                $actionClass = 'bg-purple-100 text-purple-700';
                            }
                        @endphp
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $actionClass }}">
                            {{ str_replace('_', ' ', $log->Action) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-600 max-w-md truncate" title="{{ $log->Description }}">
                            {{ $log->Description ?? '-' }}
                        </p>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-clipboard-list fa-3x text-gray-300 mb-3"></i>
                        <p class="font-medium">No activity logs found</p>
                        <p class="text-sm mt-1">Admin actions will appear here once they start performing tasks.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Pagination --}}
    @if($logs->hasPages())
    <div class="p-4 border-t bg-gray-50">
        {{ $logs->withQueryString()->links() }}
    </div>
    @endif
</div>

{{-- Legend --}}
<div class="mt-6 bg-gray-50 border rounded-lg p-4">
    <h4 class="text-sm font-semibold text-gray-700 mb-3"><i class="fas fa-info-circle mr-1"></i> Action Types Legend</h4>
    <div class="flex flex-wrap gap-3">
        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">APPROVED / CREATED</span>
        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">REJECTED / DELETED</span>
        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">UPDATED / MODIFIED</span>
        <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-700">GENERATED</span>
        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">OTHER</span>
    </div>
</div>
@endsection