<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Sta. Rosa Vet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; overflow: hidden; }

        /* Custom Scrollbar for a cleaner look */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #d1d5db;
        }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    {{-- Sidebar: Fixed Height, Internal Scroll --}}
    <aside class="w-72 bg-white border-r border-gray-100 flex flex-col shadow-sm shrink-0 h-full">
        {{-- Branding: Fixed Top --}}
        <div class="p-6 pb-4">
            <h2 class="text-red-700 text-2xl font-black uppercase tracking-tighter leading-none">
                Sta. Rosa <span class="text-gray-900 block text-lg">Vet Admin</span>
            </h2>
        </div>

        {{-- Scrollable Navigation Area --}}
        <nav class="flex-1 px-4 space-y-1.5 overflow-y-auto custom-scrollbar">
            @php
                $navItems = [
                    ['route' => 'admin.dashboard', 'icon' => 'fas fa-chart-line', 'label' => 'Dashboard', 'desc' => 'Overview'],
                    ['route' => 'admin.verifications', 'icon' => 'fas fa-user-check', 'label' => 'Verification', 'desc' => 'User Identity'],
                    ['route' => 'admin.appointment_index', 'icon' => 'fas fa-calendar-alt', 'label' => 'Appointments', 'desc' => 'Schedules'],
                    ['route' => 'admin.attendance', 'icon' => 'fas fa-clipboard-check', 'label' => 'Attendance', 'desc' => 'Daily Logs'],
                    ['route' => 'admin.certificates.index', 'icon' => 'fas fa-certificate', 'label' => 'Certificates', 'desc' => 'Records'],
                    ['route' => 'admin.reports', 'icon' => 'fas fa-file-medical', 'label' => 'Reports', 'desc' => 'Analytics'],
                ];
            @endphp

            @foreach($navItems as $item)
                <a href="{{ route($item['route']) }}" 
                   class="flex items-center gap-3 p-2.5 rounded-xl transition group {{ request()->routeIs($item['route']) ? 'bg-red-50 border-red-100 border shadow-sm' : 'hover:bg-gray-50 border border-transparent' }}">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center transition {{ request()->routeIs($item['route']) ? 'bg-red-700 text-white' : 'bg-gray-100 text-gray-500 group-hover:bg-red-100 group-hover:text-red-700' }}">
                        <i class="{{ $item['icon'] }} text-xs"></i>
                    </div>
                    <div>
                        <p class="font-bold text-[13px] {{ request()->routeIs($item['route']) ? 'text-red-700' : 'text-gray-700' }}">{{ $item['label'] }}</p>
                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">{{ $item['desc'] }}</p>
                    </div>
                </a>
            @endforeach

            {{-- Super Admin Section --}}
            @if(isset($isSuperAdmin) && $isSuperAdmin)
                <div class="pt-4 pb-2 space-y-1.5">
                    <p class="px-4 py-1.5 text-[9px] font-black text-gray-400 uppercase tracking-widest">System Management</p>
                    
                    <a href="{{ route('admin.admins.index') }}" class="flex items-center gap-3 p-2.5 rounded-xl transition group {{ request()->routeIs('admin.admins.*') ? 'bg-gray-900 border-gray-800 border text-white' : 'hover:bg-gray-50' }}">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.admins.*') ? 'bg-white text-gray-900' : 'bg-gray-100 text-gray-500' }}">
                            <i class="fas fa-users-cog text-xs"></i>
                        </div>
                        <p class="font-bold text-[13px]">Manage Admins</p>
                    </a>

                    <a href="{{ route('admin.logs') }}" class="flex items-center gap-3 p-2.5 rounded-xl transition group {{ request()->routeIs('admin.logs') ? 'bg-gray-900 border-gray-800 border text-white' : 'hover:bg-gray-50' }}">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.logs') ? 'bg-white text-gray-900' : 'bg-gray-100 text-gray-500' }}">
                            <i class="fas fa-history text-xs"></i>
                        </div>
                        <p class="font-bold text-[13px]">Activity Logs</p>
                    </a>
                </div>
            @endif
        </nav>

        {{-- Fixed Bottom Profile --}}
        <div class="p-4 border-t border-gray-50 mt-auto">
            <div class="bg-gray-50 rounded-2xl p-3 flex items-center gap-3">
                <div class="h-9 w-9 rounded-xl {{ (isset($isSuperAdmin) && $isSuperAdmin) ? 'bg-gray-900' : 'bg-red-700' }} flex items-center justify-center text-white font-black shadow-sm shrink-0">
                    {{ substr(Auth::user()->First_Name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-black text-gray-900 truncate uppercase leading-none">{{ Auth::user()->First_Name }}</p>
                    <p class="text-[9px] font-bold text-gray-400 uppercase italic">Admin Access</p>
                </div>
                
                <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" class="text-[10px] font-black text-red-600 hover:text-red-800 uppercase tracking-widest px-2 py-1 transition border border-transparent hover:border-red-200 rounded-lg">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Content Area --}}
    <div class="flex-1 flex flex-col relative min-w-0">
        {{-- Header logic remains the same --}}
        <header class="h-16 bg-white border-b border-gray-100 flex items-center justify-between px-8 shrink-0">
            <div>
                <h1 class="text-xl font-black text-gray-900 uppercase tracking-tight leading-none">@yield('page_title', 'Admin Panel')</h1>
                <p class="text-[9px] font-bold text-red-700 uppercase tracking-[0.2em] mt-1">Sta. Rosa Management</p>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex flex-col items-end mr-2">
                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-[0.15em] leading-none mb-1.5">Access Level</span>
                    @if(isset($isSuperAdmin) && $isSuperAdmin)
                        <span class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-50 border border-purple-100 text-[10px] font-black text-purple-700 uppercase tracking-tight shadow-sm">
                            <i class="fas fa-crown text-[8px]"></i> Super Admin
                        </span>
                    @else
                        <span class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-50 border border-red-100 text-[10px] font-black text-red-700 uppercase tracking-tight shadow-sm">
                            <i class="fas fa-shield-alt text-[8px]"></i> Clinic Staff
                        </span>
                    @endif
                </div>
                
                <div class="h-9 w-9 rounded-xl {{ (isset($isSuperAdmin) && $isSuperAdmin) ? 'bg-gray-900' : 'bg-red-700' }} flex items-center justify-center text-white font-black shadow-sm shrink-0">
                    {{ substr(Auth::user()->First_Name, 0, 1) }}
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-8 bg-gray-50/50">
            @yield('content')
        </main>
    </div>
</body>
</html>