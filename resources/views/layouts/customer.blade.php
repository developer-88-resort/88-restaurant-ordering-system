<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @if (file_exists(public_path('images/logo.png')))
            <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|playfair-display:500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-[#F7F0E3]" data-turbo="false">
        <x-toast />

        <header class="sticky top-0 z-40 bg-white border-b border-[#E5DDD0]">
            <div class="px-4 h-16 max-w-5xl mx-auto flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    @if (file_exists(public_path('images/logo.png')))
                        <img src="{{ asset('images/logo.png') }}" alt="88 Hot Spring Resort" class="h-9 w-9 rounded-full object-cover shrink-0">
                    @endif
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate" style="font-family: 'Playfair Display', serif;">
                            88 Hot Spring Resort
                        </p>
                        @if ($locationLabel)
                            <p class="text-xs text-[#8A3330] font-medium truncate">{{ $locationLabel }}</p>
                        @endif
                    </div>
                </div>
                <x-language-switcher align="right" />
            </div>
        </header>

        <main class="min-h-[calc(100vh-4rem)]">
            {{ $slot }}
        </main>
    </body>
</html>
