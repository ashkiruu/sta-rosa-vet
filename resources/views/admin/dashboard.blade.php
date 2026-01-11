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
            <h3 class="text-2xl font-bold">{{ \App\Models\Pet::count() }}</h3>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="font-bold text-gray-700 mb-4">Quick Actions</h3>
    <div class="flex gap-4">
        <a href="{{ route('admin.verifications') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Review Pending Users</a>
        <button class="border border-gray-300 px-4 py-2 rounded hover:bg-gray-50">Generate Daily Report</button>
    </div>
</div>
@endsection