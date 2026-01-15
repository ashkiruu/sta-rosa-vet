<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Sta. Rosa Vet</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    <aside class="w-64 bg-slate-900 text-white flex flex-col">
        <div class="p-6 text-center font-bold text-xl border-b border-slate-700">
            <span class="text-blue-400">STA. ROSA</span> VET
        </div>
        <nav class="flex-1 mt-4">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white' : '' }}">
                <i class="fas fa-chart-line mr-3"></i> Dashboard
            </a>
            <a href="{{ route('admin.verifications') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.verifications') || request()->routeIs('admin.user.show') ? 'bg-blue-600 text-white' : '' }}">
                <i class="fas fa-user-check mr-3"></i> User Verification
            </a>
            <a href="{{ route('admin.appointment_index') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.appointment_index') ? 'bg-blue-600 text-white' : '' }}">
                <i class="fas fa-calendar-alt mr-3"></i> Appointments
            </a>
            <a href="{{ route('admin.attendance') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.attendance') ? 'bg-blue-600 text-white' : '' }}">
                <i class="fas fa-clipboard-check mr-3"></i> Attendance
            </a>
            <a href="{{ route('admin.certificates.index') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.certificates.*') ? 'bg-blue-600 text-white' : '' }}">
                <i class="fas fa-certificate mr-3"></i> Certificates
            </a>
            <a href="{{ route('admin.reports') }}" class="flex items-center px-6 py-3 text-gray-300 hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.reports') ? 'bg-blue-600 text-white' : '' }}">
                <i class="fas fa-file-medical mr-3"></i> Reports
            </a>
        </nav>
        <div class="p-4 border-t border-slate-700">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-slate-800 rounded">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </button>
            </form>
        </div>
    </aside>
    <div class="flex-1 flex flex-col">
        <header class="h-16 bg-white border-b flex items-center justify-between px-8">
            <h1 class="text-lg font-semibold text-gray-700">@yield('page_title')</h1>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500 italic">Administrator</span>
                <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                    {{ substr(Auth::user()->First_Name, 0, 1) }}
                </div>
            </div>
        </header>
        @if(session('success'))
            <div class="mx-8 mt-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 shadow-sm rounded">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mx-8 mt-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 shadow-sm rounded">
                <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            </div>
        @endif
        @if(session('info'))
            <div class="mx-8 mt-4 p-4 bg-blue-100 border-l-4 border-blue-500 text-blue-700 shadow-sm rounded">
                <i class="fas fa-info-circle mr-2"></i> {{ session('info') }}
            </div>
        @endif
        <main class="p-8 overflow-y-auto">
            @yield('content')
        </main>
    </div>
    
</body>
</html>