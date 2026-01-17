@extends('layouts.admin')
@section('page_title', 'System Overview')
@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
        <div class="p-4 bg-orange-100 text-orange-600 rounded-lg mr-4">
            <i class="fas fa-users-cog fa-2x"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Pending Residents</p>
            <h3 class="text-2xl font-bold">{{ $stats['pending_users'] }}</h3>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
        <div class="p-4 bg-blue-100 text-blue-600 rounded-lg mr-4">
            <i class="fas fa-calendar-day fa-2x"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Today's Appointments</p>
            <h3 class="text-2xl font-bold">{{ $stats['today_appointments'] }}</h3>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center">
        <div class="p-4 bg-green-100 text-green-600 rounded-lg mr-4">
            <i class="fas fa-paw fa-2x"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Total Pets</p>
            <h3 class="text-2xl font-bold">{{ $stats['total_pets'] ?? \App\Models\Pet::count() }}</h3>
        </div>
    </div>
</div>

{{-- Super Admin Only: Additional Stats --}}
@if(isset($isSuperAdmin) && $isSuperAdmin)
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
    <div class="bg-white rounded-xl shadow-sm border border-purple-200 p-6 flex items-center">
        <div class="p-4 bg-purple-100 text-purple-600 rounded-lg mr-4">
            <i class="fas fa-user-shield fa-2x"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Staff/Admin Accounts</p>
            <h3 class="text-2xl font-bold">{{ $stats['total_admins'] ?? 0 }}</h3>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-purple-200 p-6 flex items-center">
        <div class="p-4 bg-indigo-100 text-indigo-600 rounded-lg mr-4">
            <i class="fas fa-history fa-2x"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Admin Actions Today</p>
            <h3 class="text-2xl font-bold">{{ $stats['activity_summary']['today_count'] ?? 0 }}</h3>
        </div>
    </div>
</div>

{{-- Recent Admin Activity (Super Admin Only) --}}
@if(isset($stats['activity_summary']['recent_logs']) && count($stats['activity_summary']['recent_logs']) > 0)
<div class="bg-white rounded-xl shadow-sm border border-purple-200 p-6 mb-10">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-gray-700">
            <i class="fas fa-history text-purple-500 mr-2"></i> Recent Admin Activity
        </h3>
        <a href="{{ route('admin.logs') }}" class="text-sm text-purple-600 hover:text-purple-800">
            View All <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
    <div class="space-y-3">
        @foreach($stats['activity_summary']['recent_logs'] as $log)
        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
            <div class="flex items-center">
                <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold mr-3">
                    {{ substr($log->user->First_Name ?? 'A', 0, 1) }}
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">
                        {{ $log->user->First_Name ?? 'Unknown' }} {{ $log->user->Last_Name ?? '' }}
                    </p>
                    <p class="text-xs text-gray-500">{{ str_replace('_', ' ', $log->Action) }}</p>
                </div>
            </div>
            <span class="text-xs text-gray-400">{{ $log->Timestamp->diffForHumans() }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="font-bold text-gray-700 mb-4">Quick Actions</h3>
    <div class="flex flex-wrap gap-4">
        <a href="{{ route('admin.verifications') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
            <i class="fas fa-user-check mr-2"></i> Review Pending Users
        </a>
        <a href="{{ route('admin.appointment_index') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
            <i class="fas fa-calendar-alt mr-2"></i> Manage Appointments
        </a>
        <a href="{{ route('admin.reports') }}" class="border border-gray-300 px-4 py-2 rounded hover:bg-gray-50 transition">
            <i class="fas fa-file-medical mr-2"></i> Generate Report
        </a>
        
        {{-- Super Admin Only Actions --}}
        @if(isset($isSuperAdmin) && $isSuperAdmin)
        <a href="{{ route('admin.admins.create') }}" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition">
            <i class="fas fa-user-plus mr-2"></i> Create Admin Account
        </a>
        <a href="{{ route('admin.logs') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
            <i class="fas fa-history mr-2"></i> View Activity Logs
        </a>
        @endif
    </div>
</div>
@endsection