@extends('layouts.admin')
@section('page_title', 'System Overview')
@section('content')

{{-- Primary Stats Grid --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    {{-- Pending Residents - Red Focus --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group hover:border-red-200 transition-all duration-300">
        <div class="w-16 h-16 bg-red-100 text-red-700 rounded-2xl flex items-center justify-center mr-5 group-hover:bg-red-700 group-hover:text-white transition-all shadow-sm">
            <i class="fas fa-users-cog text-2xl"></i>
        </div>
        <div>
            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Pending Residents</p>
            <h3 class="text-3xl font-black text-gray-900 leading-none">{{ $stats['pending_users'] }}</h3>
        </div>
    </div>

    {{-- Today's Appointments - Blue Focus --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group hover:border-blue-200 transition-all duration-300">
        <div class="w-16 h-16 bg-blue-100 text-blue-700 rounded-2xl flex items-center justify-center mr-5 group-hover:bg-blue-700 group-hover:text-white transition-all shadow-sm">
            <i class="fas fa-calendar-day text-2xl"></i>
        </div>
        <div>
            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Today's Visits</p>
            <h3 class="text-3xl font-black text-gray-900 leading-none">{{ $stats['today_appointments'] }}</h3>
        </div>
    </div>

    {{-- Total Pets - Green Focus --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group hover:border-green-200 transition-all duration-300">
        <div class="w-16 h-16 bg-green-100 text-green-700 rounded-2xl flex items-center justify-center mr-5 group-hover:bg-green-700 group-hover:text-white transition-all shadow-sm">
            <i class="fas fa-paw text-2xl"></i>
        </div>
        <div>
            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Total Pets</p>
            <h3 class="text-3xl font-black text-gray-900 leading-none">{{ $stats['total_pets'] ?? \App\Models\Pet::count() }}</h3>
        </div>
    </div>
</div>

{{-- Super Admin Section --}}
@if(isset($isSuperAdmin) && $isSuperAdmin)
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    {{-- Purple is kept for Super Admin to indicate "Elevated Power" --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-purple-100 p-6 flex items-center border-l-8 border-l-purple-600">
        <div class="w-14 h-14 bg-purple-100 text-purple-700 rounded-xl flex items-center justify-center mr-5">
            <i class="fas fa-user-shield text-xl"></i>
        </div>
        <div>
            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Staff Accounts</p>
            <h3 class="text-2xl font-black text-gray-900 leading-none">{{ $stats['total_admins'] ?? 0 }}</h3>
        </div>
    </div>
    <div class="bg-white rounded-[2rem] shadow-sm border border-indigo-100 p-6 flex items-center border-l-8 border-l-indigo-600">
        <div class="w-14 h-14 bg-indigo-100 text-indigo-700 rounded-xl flex items-center justify-center mr-5">
            <i class="fas fa-history text-xl"></i>
        </div>
        <div>
            <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">System Actions Today</p>
            <h3 class="text-2xl font-black text-gray-900 leading-none">{{ $stats['activity_summary']['today_count'] ?? 0 }}</h3>
        </div>
    </div>
</div>

{{-- Recent Activity Table --}}
@if(isset($stats['activity_summary']['recent_logs']) && count($stats['activity_summary']['recent_logs']) > 0)
<div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8 mb-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight">Recent Admin Activity</h3>
            <p class="text-[10px] font-bold text-purple-600 uppercase tracking-widest">System Audit Log</p>
        </div>
        <a href="{{ route('admin.logs') }}" class="text-[10px] font-black text-gray-500 hover:text-purple-600 uppercase tracking-widest transition border border-gray-100 px-4 py-2 rounded-xl">
            View All Logs <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
    <div class="divide-y divide-gray-100">
        @foreach($stats['activity_summary']['recent_logs'] as $log)
        <div class="flex items-center justify-between py-4 group hover:bg-gray-50 transition px-4 -mx-4 rounded-2xl">
            <div class="flex items-center">
                {{-- Contrasting Initials Circle --}}
                <div class="h-10 w-10 rounded-xl bg-gray-900 flex items-center justify-center text-white text-xs font-black shadow-md mr-4 uppercase">
                    {{ substr($log->user->First_Name ?? 'A', 0, 1) }}
                </div>
                <div>
                    <p class="text-sm font-black text-gray-800 uppercase tracking-tight">
                        {{ $log->user->First_Name ?? 'Unknown' }} {{ $log->user->Last_Name ?? '' }}
                    </p>
                    <p class="text-[11px] font-bold text-gray-500 uppercase italic">{{ str_replace('_', ' ', $log->Action) }}</p>
                </div>
            </div>
            <span class="text-[10px] font-black text-gray-600 uppercase bg-gray-100 px-3 py-1.5 rounded-lg border border-gray-200">
                {{ $log->Timestamp->diffForHumans() }}
            </span>
        </div>
        @endforeach
    </div>
</div>
@endif
@endif

{{-- Quick Actions --}}
<div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8">
    <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight mb-6">Quick Actions</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Action 1: Blue for Verification --}}
        <a href="{{ route('admin.verifications') }}" class="flex flex-col items-center justify-center p-6 bg-blue-50 rounded-3xl border border-transparent hover:border-blue-200 hover:shadow-md transition group text-center">
            <div class="w-12 h-12 bg-white rounded-2xl shadow-sm flex items-center justify-center text-blue-600 mb-3 group-hover:bg-blue-600 group-hover:text-white transition">
                <i class="fas fa-user-check"></i>
            </div>
            <span class="text-[10px] font-black text-gray-900 uppercase tracking-widest">Review Users</span>
        </a>

        {{-- Action 2: Green for Confirmations --}}
        <a href="{{ route('admin.appointment_index') }}" class="flex flex-col items-center justify-center p-6 bg-green-50 rounded-3xl border border-transparent hover:border-green-200 hover:shadow-md transition group text-center">
            <div class="w-12 h-12 bg-white rounded-2xl shadow-sm flex items-center justify-center text-green-600 mb-3 group-hover:bg-green-600 group-hover:text-white transition">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <span class="text-[10px] font-black text-gray-900 uppercase tracking-widest">Manage Bookings</span>
        </a>

        {{-- Action 3: Gray/Black for neutral --}}
        <a href="{{ route('admin.reports') }}" class="flex flex-col items-center justify-center p-6 bg-gray-100 rounded-3xl border border-transparent hover:border-gray-300 hover:shadow-md transition group text-center">
            <div class="w-12 h-12 bg-white rounded-2xl shadow-sm flex items-center justify-center text-gray-700 mb-3 group-hover:bg-gray-800 group-hover:text-white transition">
                <i class="fas fa-file-medical"></i>
            </div>
            <span class="text-[10px] font-black text-gray-900 uppercase tracking-widest">Reports</span>
        </a>

        @if(isset($isSuperAdmin) && $isSuperAdmin)
        {{-- Action 4: High Contrast Black --}}
        <a href="{{ route('admin.admins.create') }}" class="flex flex-col items-center justify-center p-6 bg-gray-900 rounded-3xl border border-transparent hover:bg-black hover:shadow-lg transition group text-center">
            <div class="w-12 h-12 bg-gray-800 rounded-2xl shadow-sm flex items-center justify-center text-white mb-3 group-hover:scale-110 transition">
                <i class="fas fa-user-plus"></i>
            </div>
            <span class="text-[10px] font-black text-white uppercase tracking-widest">Add Admin</span>
        </a>
        @endif
    </div>
</div>
@endsection