<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'GameBox') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#0d1117] text-gray-200">
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="bg-[#161b22] border-t border-gray-800 py-8 mt-12">
                <div class="max-w-7xl mx-auto px-4 text-center text-gray-500 text-sm">
                    <p>&copy; {{ date('Y') }} GameBox. Data provided by IGDB.</p>
                </div>
            </footer>
        </div>
    </body>
</html>