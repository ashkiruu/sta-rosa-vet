{{-- resources/views/components/public-layout.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sta. Rosa Veterinary Clinic' }}</title>
    
    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    {{-- Tailwind CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    {{-- Simple Header --}}
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 w-10 object-contain">
                    <div>
                        <h1 class="text-sm font-black text-gray-900 uppercase tracking-tight">Sta. Rosa Veterinary Clinic</h1>
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">QR Verification System</p>
                    </div>
                </a>
                
                <a href="{{ url('/') }}" class="text-xs font-bold text-gray-500 hover:text-gray-900 uppercase tracking-widest transition">
                    ← Back to Home
                </a>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Simple Footer --}}
    <footer class="py-6 text-center">
        <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.3em]">
            © {{ now()->format('Y') }} Sta. Rosa Veterinary Clinic • Secure Verification
        </p>
    </footer>
</body>
</html>