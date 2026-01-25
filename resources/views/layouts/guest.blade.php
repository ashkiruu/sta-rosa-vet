<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen w-full flex flex-col items-center bg-fixed bg-cover bg-center" 
         style="background-image: url('{{ asset('images/PawsBackground.png') }}');">
        
        <div class="mt-8 mb-2"> 
            <img src="{{ asset('images/Logo.png') }}" 
                 alt="Logo" 
                 class="w-auto h-20 md:h-28 object-contain mx-auto">
        </div>

        <div class="w-full flex-1 flex flex-col items-center">
            @include('partials.alerts')
            {{ $slot }}
        </div>

    </div>
</body>
</html>