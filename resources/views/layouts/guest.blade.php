<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased bg-gray-100">
    {{-- Flattened structure: Logo and Content in a single flow --}}
    <div class="min-h-screen w-full flex flex-col items-center py-6 bg-fixed bg-cover bg-center" 
         style="background-image: url('{{ asset('images/PawsBackground.png') }}');">
        
        <img src="{{ asset('images/LogoBlack.png') }}" alt="Logo" class="w-100 h-100 mb-6 object-contain">

        {{ $slot }}
    </div>
</body>
</html>