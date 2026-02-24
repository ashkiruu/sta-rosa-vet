@extends('layouts.admin')

@section('page_title', 'Manage Accounts')

@section('content')
<div class="min-h-screen py-4">
    {{-- Header Area --}}
    <div class="mb-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900 uppercase tracking-tight">Account Directory</h1>
            <p class="text-[10px] font-bold text-purple-600 uppercase tracking-[0.2em]">Manage system access levels & roles</p>
        </div>
        <a href="{{ route('admin.admins.create') }}" class="w-full md:w-auto bg-gray-900 hover:bg-purple-600 text-white px-6 py-3.5 rounded-[1.5rem] font-black text-xs uppercase tracking-widest transition-all shadow-lg flex items-center justify-center gap-2 group">
            <i class="fas fa-user-plus group-hover:scale-110 transition-transform"></i> 
            Add Account
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group hover:border-purple-200 transition-colors">
            <div class="p-4 bg-purple-50 text-purple-600 rounded-2xl mr-4 group-hover:bg-purple-600 group-hover:text-white transition-all">
                <i class="fas fa-shield-alt text-xl"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Admins</p>
                <h4 class="text-2xl font-black text-gray-900">{{ $admins->where('admin_role', 'admin')->count() }}</h4>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group hover:border-emerald-200 transition-colors">
            <div class="p-4 bg-emerald-50 text-emerald-600 rounded-2xl mr-4 group-hover:bg-emerald-600 group-hover:text-white transition-all">
                <i class="fas fa-stethoscope text-xl"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Veterinary Doctors</p>
                <h4 class="text-2xl font-black text-gray-900">{{ $admins->where('admin_role', 'doctor')->count() }}</h4>
            </div>
        </div>

        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 flex items-center group hover:border-blue-200 transition-colors">
            <div class="p-4 bg-blue-50 text-blue-600 rounded-2xl mr-4 group-hover:bg-blue-600 group-hover:text-white transition-all">
                <i class="fas fa-id-badge text-xl"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Staff</p>
                <h4 class="text-2xl font-black text-gray-900">{{ $admins->where('admin_role', 'staff')->count() }}</h4>
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
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Role</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Assigned By</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Date Added</th>
                        <th class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($admins as $admin)
                    @php
                        $roleConfig = match($admin->admin_role) {
                            'admin' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'icon' => 'fas fa-shield-alt', 'label' => 'Admin', 'avatar' => 'bg-purple-600', 'rowBg' => 'bg-purple-50/30'],
                            'doctor' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => 'fas fa-stethoscope', 'label' => 'Vet Doctor', 'avatar' => 'bg-emerald-700', 'rowBg' => ''],
                            'staff' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'icon' => 'fas fa-id-badge', 'label' => 'Staff', 'avatar' => 'bg-gray-900', 'rowBg' => ''],
                            default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-200', 'icon' => 'fas fa-user', 'label' => ucfirst($admin->admin_role), 'avatar' => 'bg-gray-900', 'rowBg' => ''],
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/80 transition-all {{ $roleConfig['rowBg'] }}">
                        <td class="px-8 py-6">
                            <div class="flex items-center">
                                <div class="h-12 w-12 rounded-2xl {{ $roleConfig['avatar'] }} flex items-center justify-center text-white text-lg font-black shadow-inner mr-4">
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
                            <span class="inline-flex items-center px-4 py-1.5 text-[9px] font-black {{ $roleConfig['bg'] }} {{ $roleConfig['text'] }} rounded-full uppercase tracking-widest border {{ $roleConfig['border'] }}">
                                <i class="{{ $roleConfig['icon'] }} mr-2"></i> {{ $roleConfig['label'] }}
                            </span>
                        </td>
                        <td class="px-8 py-6 text-[10px] font-black text-gray-500 uppercase">
                            @if($admin->creator)
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-link text-gray-300"></i>
                                    {{ $admin->creator->First_Name }} {{ $admin->creator->Last_Name }}
                                </span>
                            @else
                                <span class="text-gray-300 italic tracking-widest">SYSTEM</span>
                            @endif
                        </td>
                        <td class="px-8 py-6 text-[10px] font-black text-gray-500 uppercase tracking-widest">
                            {{ $admin->created_at ? $admin->created_at->format('M d, Y') : '--' }}
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex items-center justify-end gap-3">
                                @if($admin->User_ID !== auth()->id())
                                    @if($admin->admin_role !== 'admin')
                                    {{-- Role Change Dropdown (only for staff/doctor) --}}
                                    <form action="{{ route('admin.admins.update', $admin->User_ID) }}" method="POST" class="inline-flex items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <select name="admin_role" onchange="this.form.submit()" class="text-[10px] font-bold bg-gray-50 border border-gray-200 rounded-lg px-2 py-1.5 cursor-pointer focus:ring-2 focus:ring-purple-500">
                                            <option value="staff" {{ $admin->admin_role === 'staff' ? 'selected' : '' }}>Staff</option>
                                            <option value="doctor" {{ $admin->admin_role === 'doctor' ? 'selected' : '' }}>Doctor</option>
                                        </select>
                                    </form>

                                    <form action="{{ route('admin.admins.destroy', $admin->User_ID) }}" method="POST" class="inline-block" onsubmit="return confirm('WARNING: REVOKE ACCESS? This user will lose all system privileges immediately.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2.5 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-xl transition-all shadow-sm" title="Remove Access">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                    @else
                                    <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest border border-gray-100 px-3 py-1.5 rounded-lg bg-gray-50/50">Protected</span>
                                    @endif
                                @else
                                    <span class="text-[9px] font-black text-gray-300 uppercase tracking-widest border border-gray-100 px-3 py-1.5 rounded-lg bg-gray-50/50">Current User</span>
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
                            <p class="text-[10px] font-bold text-gray-400 uppercase mt-2 italic">Add a user to begin management</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Info Box --}}
    <div class="mt-8 bg-gray-600 rounded-[2.5rem] p-8 text-white shadow-xl relative overflow-hidden">
        <i class="fas fa-shield-alt absolute -right-4 -bottom-4 text-8xl text-white/5 rotate-12"></i>
        
        <h4 class="text-xs font-black uppercase tracking-[0.2em] mb-6 flex items-center gap-2">
            <i class="fas fa-info-circle text-blue-400"></i> Role Governance Architecture
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative z-10">
            <div class="border-l border-white/10 pl-4">
                <p class="text-blue-400 text-[10px] font-black uppercase tracking-widest mb-1">Staff</p>
                <p class="text-[10px] font-bold text-white leading-relaxed uppercase">User verification, attendance logs, and report generation. All activity is logged.</p>
            </div>
            <div class="border-l border-white/10 pl-4">
                <p class="text-emerald-400 text-[10px] font-black uppercase tracking-widest mb-1">Veterinary Doctor</p>
                <p class="text-[10px] font-bold text-white leading-relaxed uppercase">Appointment management, certificate generation, attendance logs, and reports. All activity is logged.</p>
            </div>
            <div class="border-l border-white/10 pl-4">
                <p class="text-purple-400 text-[10px] font-black uppercase tracking-widest mb-1">Admin</p>
                <p class="text-[10px] font-bold text-white leading-relaxed uppercase">System management: role assignment, activity log monitoring. Full oversight of staff and doctor accounts.</p>
            </div>
        </div>
    </div>
</div>
@endsection