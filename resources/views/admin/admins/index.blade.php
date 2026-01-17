@extends('layouts.admin')
@section('page_title', 'Manage Admin Accounts')
@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Admin Accounts</h2>
        <p class="text-gray-500 mt-1">Manage staff and administrator access</p>
    </div>
    <a href="{{ route('admin.admins.create') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition flex items-center">
        <i class="fas fa-user-plus mr-2"></i> Add New Admin
    </a>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm border p-4">
        <div class="flex items-center">
            <div class="p-3 bg-purple-100 text-purple-600 rounded-lg mr-3">
                <i class="fas fa-crown"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">Super Admins</p>
                <h4 class="text-xl font-bold">{{ $admins->where('is_super_admin', true)->count() }}</h4>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm border p-4">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 text-blue-600 rounded-lg mr-3">
                <i class="fas fa-user-tie"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">Administrators</p>
                <h4 class="text-xl font-bold">{{ $admins->where('admin_role', 'admin')->where('is_super_admin', false)->count() }}</h4>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-sm border p-4">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 text-green-600 rounded-lg mr-3">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500">Staff Members</p>
                <h4 class="text-xl font-bold">{{ $admins->where('admin_role', 'staff')->where('is_super_admin', false)->count() }}</h4>
            </div>
        </div>
    </div>
</div>

{{-- Admin List --}}
<div class="bg-white rounded-xl shadow-sm border">
    <div class="p-4 border-b bg-gray-50">
        <h3 class="font-semibold text-gray-700">All Admin Accounts</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($admins as $admin)
                <tr class="hover:bg-gray-50 {{ $admin->is_super_admin ? 'bg-purple-50' : '' }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-full {{ $admin->is_super_admin ? 'bg-purple-500' : 'bg-blue-500' }} flex items-center justify-center text-white font-bold mr-3">
                                {{ substr($admin->user->First_Name ?? 'A', 0, 1) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">
                                    {{ $admin->user->First_Name ?? 'Unknown' }} {{ $admin->user->Last_Name ?? '' }}
                                </p>
                                <p class="text-sm text-gray-500">{{ $admin->user->Email ?? 'No email' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($admin->is_super_admin)
                            <span class="px-3 py-1 text-xs font-semibold bg-purple-100 text-purple-700 rounded-full">
                                <i class="fas fa-crown mr-1"></i> Super Admin
                            </span>
                        @elseif($admin->admin_role === 'admin')
                            <span class="px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-700 rounded-full">
                                <i class="fas fa-user-tie mr-1"></i> Administrator
                            </span>
                        @else
                            <span class="px-3 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">
                                <i class="fas fa-user mr-1"></i> Staff
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($admin->creator)
                            {{ $admin->creator->First_Name }} {{ $admin->creator->Last_Name }}
                        @else
                            <span class="text-gray-400 italic">System</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $admin->created_at ? $admin->created_at->format('M d, Y') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        @if(!$admin->is_super_admin)
                            {{-- Role Change Form --}}
                            <form action="{{ route('admin.admins.update', $admin->User_ID) }}" method="POST" class="inline-block mr-2">
                                @csrf
                                @method('PUT')
                                <select name="admin_role" onchange="this.form.submit()" class="text-sm border rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="staff" {{ $admin->admin_role === 'staff' ? 'selected' : '' }}>Staff</option>
                                    <option value="admin" {{ $admin->admin_role === 'admin' ? 'selected' : '' }}>Administrator</option>
                                </select>
                            </form>
                            
                            {{-- Delete Button --}}
                            @if($admin->User_ID !== auth()->id())
                            <form action="{{ route('admin.admins.destroy', $admin->User_ID) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to remove this admin? They will lose all administrative privileges.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        @else
                            <span class="text-xs text-gray-400 italic">Protected</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-users-slash fa-3x text-gray-300 mb-3"></i>
                        <p>No admin accounts found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Info Box --}}
<div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
    <h4 class="font-semibold text-blue-800 mb-2"><i class="fas fa-info-circle mr-2"></i> Role Permissions</h4>
    <ul class="text-sm text-blue-700 space-y-1">
        <li><strong>Staff:</strong> Can verify users, manage appointments, create certificates, and generate reports. All actions are logged.</li>
        <li><strong>Administrator:</strong> Same as Staff with potential for additional permissions in the future.</li>
        <li><strong>Super Admin:</strong> Full system access including admin management and activity log viewing. Actions are NOT logged.</li>
    </ul>
</div>
@endsection