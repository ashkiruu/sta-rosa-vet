@extends('layouts.admin')

@section('page_title', 'Resident Management')

@section('content')
<div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-6">
    <form action="{{ route('admin.verifications') }}" method="GET" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="text-xs font-bold text-gray-500 uppercase">Search Resident</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or Email..." class="w-full mt-1 border border-gray-300 rounded-md p-2 focus:ring-blue-500">
        </div>

        <div class="w-48">
            <label class="text-xs font-bold text-gray-500 uppercase">Status</label>
            <select name="status" class="w-full mt-1 border border-gray-300 rounded-md p-2">
                <option value="">All Status</option>
                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Pending</option>
                <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Verified</option>
            </select>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">Filter</button>
        <a href="{{ route('admin.verifications') }}" class="bg-gray-200 px-6 py-2 rounded-md hover:bg-gray-300 text-gray-700 text-center">Reset</a>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead class="bg-gray-50 border-b border-gray-100 text-gray-600 uppercase text-xs font-bold">
            <tr>
                <th class="px-6 py-4">Resident Name</th>
                <th class="px-6 py-4">Email Address</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4">Date Joined</th>
                <th class="px-6 py-4 text-center">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($users as $user)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <a href="{{ route('admin.user.show', $user->User_ID) }}" class="font-medium text-blue-600 hover:underline">
                        {{ $user->First_Name }} {{ $user->Last_Name }}
                    </a>
                </td>
                <td class="px-6 py-4">
                    @if($user->Verification_Status_ID == 2)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i> Verified
                        </span>
                    @elseif($user->Verification_Status_ID == 1)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <i class="fas fa-clock mr-1"></i> Pending
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-times-circle mr-1"></i> Rejected
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 text-gray-500 text-sm">{{ $user->created_at->format('M d, Y') }}</td>
                <td class="px-6 py-4 flex justify-center gap-2">
                    <a href="{{ route('admin.user.show', $user->User_ID) }}" class="text-blue-600 hover:text-blue-900 bg-blue-50 px-3 py-1 rounded-md text-sm">
                        View Details
                    </a>

                    @if($user->Verification_Status_ID == 1)
                        <form action="{{ route('admin.user.approve', $user->User_ID) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded-md text-sm hover:bg-green-700">
                                Approve
                            </button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-10 text-center text-gray-400 italic">No residents found matching those criteria.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection