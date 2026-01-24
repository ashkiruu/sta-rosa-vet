@extends('layouts.admin')

@section('page_title', 'Manage Admin Accounts')

@section('content')
<div class="min-h-screen py-4">
    {{-- Header Area --}}
    <div class="mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900 uppercase tracking-tight">Admin Accounts</h1>
            <p class="text-[10px] font-bold text-purple-600 uppercase tracking-[0.2em]">Manage staff and system access levels</p>
        </div>
        <a href="{{ route('admin.admins.create') }}" class="w-full md:w-auto bg-gray-900 hover:bg-purple-600 text-white px-6 py-3.5 rounded-[1.5rem] font-black text-xs uppercase tracking-widest transition-all shadow-lg flex items-center justify-center gap-2 group">
            <i class="fas fa-user-plus group-hover:scale-110 transition-transform"></i> 
            Add New Admin
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group hover:border-purple-200 transition-colors">
            <div class="p-4 bg-purple-50 text-purple-600 rounded-2xl mr-4 group-hover:bg-purple-600 group-hover:text-white transition-all">
                <i class="fas fa-crown text-xl"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Super Admins</p>
                <h4 class="text-2xl font-black text-gray-900">{{ $admins->where('is_super_admin', true)->count() }}</h4>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group hover:border-blue-200 transition-colors">
            <div class="p-4 bg-blue-50 text-blue-600 rounded-2xl mr-4 group-hover:bg-blue-600 group-hover:text-white transition-all">
                <i class="fas fa-user-tie text-xl"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Administrators</p>
                <h4 class="text-2xl font-black text-gray-900">{{ $admins->where('admin_role', 'admin')->where('is_super_admin', false)->count() }}</h4>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group hover:border-green-200 transition-colors">
            <div class="p-4 bg-green-50 text-green-600 rounded-2xl mr-4 group-hover:bg-green-600 group-hover:text-white transition-all">
                <i class="fas fa-user text-xl"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Staff Members</p>
                <h4 class="text-2xl font-black text-gray-900">{{ $admins->where('admin_role', 'staff')->where('is_super_admin', false)->count() }}</h4>
            </div>
        </div>
    </div>

    {{-- Admin List Table --}}
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
            <h3 class="text-xs font-black text-gray-700 uppercase tracking-[0.2em]">Directory Access Control</h3>
            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ $admins->count() }} Total Accounts</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-white">
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">User Profile</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Access Level</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Origin</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Enrolled At</th>
                        <th class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Management</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($admins as $admin)
                    <tr class="hover:bg-gray-50/80 transition-all {{ $admin->is_super_admin ? 'bg-purple-50/30' : '' }}">
                        <td class="px-8 py-6">
                            <div class="flex items-center">
                                <div class="h-12 w-12 rounded-2xl {{ $admin->is_super_admin ? 'bg-purple-600' : 'bg-gray-900' }} flex items-center justify-center text-white text-lg font-black shadow-inner mr-4">
                                    {{ substr($admin->user->First_Name ?? 'A', 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-black text-gray-900 uppercase tracking-tight text-sm">
                                        {{ $admin->user->First_Name ?? 'Unknown' }} {{ $admin->user->Last_Name ?? '' }}
                                    </p>
                                    <p class="text-[10px] font-bold text-gray-400 italic">{{ $admin->user->Email ?? 'No email' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            @if($admin->is_super_admin)
                                <span class="inline-flex items-center px-4 py-1.5 text-[9px] font-black bg-purple-100 text-purple-700 rounded-full uppercase tracking-widest border border-purple-200">
                                    <i class="fas fa-crown mr-2"></i> Super Admin
                                </span>
                            @elseif($admin->admin_role === 'admin')
                                <span class="inline-flex items-center px-4 py-1.5 text-[9px] font-black bg-blue-100 text-blue-700 rounded-full uppercase tracking-widest border border-blue-200">
                                    <i class="fas fa-user-tie mr-2"></i> Administrator
                                </span>
                            @else
                                <span class="inline-flex items-center px-4 py-1.5 text-[9px] font-black bg-green-100 text-green-700 rounded-full uppercase tracking-widest border border-green-200">
                                    <i class="fas fa-user mr-2"></i> Staff
                                </span>
                            @endif
                        </td>
                        <td class="px-8 py-6 text-[10px] font-black text-gray-500 uppercase">
                            @if($admin->creator)
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-link text-gray-300"></i>
                                    {{ $admin->creator->First_Name }} {{ $admin->creator->Last_Name }}
                                </span>
                            @else
                                <span class="text-gray-300 italic tracking-widest">CORE_SYSTEM</span>
                            @endif
                        </td>
                        <td class="px-8 py-6 text-[10px] font-black text-gray-500 uppercase tracking-widest">
                            {{ $admin->created_at ? $admin->created_at->format('M d, Y') : '--' }}
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex items-center justify-end gap-3">
                                @if(!$admin->is_super_admin)
                                    <form action="{{ route('admin.admins.update', $admin->User_ID) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PUT')
                                        <select name="admin_role" onchange="this.form.submit()" class="bg-gray-100 border-none text-[10px] font-black uppercase tracking-widest rounded-xl px-3 py-2 focus:ring-2 focus:ring-purple-500 transition-all cursor-pointer hover:bg-gray-200">
                                            <option value="staff" {{ $admin->admin_role === 'staff' ? 'selected' : '' }}>Set Staff</option>
                                            <option value="admin" {{ $admin->admin_role === 'admin' ? 'selected' : '' }}>Set Admin</option>
                                        </select>
                                    </form>
                                    
                                    @if($admin->User_ID !== auth()->id())
                                    <form action="{{ route('admin.admins.destroy', $admin->User_ID) }}" method="POST" class="inline-block" onsubmit="return confirm('WARNING: REVOKE ACCESS? This administrator will lose all system privileges immediately.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2.5 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-xl transition-all shadow-sm">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                    @endif
                                @else
                                    <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest border border-gray-100 px-3 py-1.5 rounded-lg bg-gray-50/50">System Protected</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center">
                            <div class="w-20 h-20 bg-gray-50 rounded-[2rem] flex items-center justify-center mb-6 mx-auto border border-gray-100">
                                <i class="fas fa-users-slash text-3xl text-gray-200"></i>
                            </div>
                            <h3 class="text-xs font-black text-gray-900 uppercase tracking-[0.2em]">No Directory Data</h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase mt-2 italic">Add a staff member to begin management</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Info Box --}}
    <div class="mt-8 bg-gray-600 rounded-[2.5rem] p-8 text-white shadow-xl relative overflow-hidden">
        {{-- Decorative Icon --}}
        <i class="fas fa-shield-alt absolute -right-4 -bottom-4 text-8xl text-white/5 rotate-12"></i>
        
        <h4 class="text-xs font-black uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
            <i class="fas fa-info-circle text-blue-400"></i> Role Governance Architecture
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative z-10">
            <div class="border-l border-white/10 pl-4">
                <p class="text-green-400 text-[10px] font-black uppercase tracking-widest mb-1">Staff</p>
                <p class="text-[10px] font-bold text-white leading-relaxed uppercase">Operation-focused: Verifications, Appointments, Certificates, and Reports. Full Audit Logs enabled.</p>
            </div>
            <div class="border-l border-white/10 pl-4">
                <p class="text-blue-400 text-[10px] font-black uppercase tracking-widest mb-1">Administrator</p>
                <p class="text-[10px] font-bold text-white leading-relaxed uppercase">Management-focused: Same as Staff with expanded system oversight and setting configurations.</p>
            </div>
            <div class="border-l border-white/10 pl-4">
                <p class="text-purple-400 text-[10px] font-black uppercase tracking-widest mb-1">Super Admin</p>
                <p class="text-[10px] font-bold text-white leading-relaxed uppercase">Root Access: Full control over directory and audit logs. Actions are bypassed from activity logs.</p>
            </div>
        </div>
    </div>
</div>
@endsection