<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>GameBox - Acceso</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,600,800&display=swap" rel="stylesheet" />
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: { colors: { backloggd: '#16181c', card: '#24282f' }, fontFamily: { sans: ['Inter', 'sans-serif'] } }
                }
            }
        </script>
    </head>
    <body class="font-sans text-gray-100 antialiased bg-backloggd min-h-screen flex flex-col justify-center items-center pt-6 sm:pt-0 px-4">
        <div class="mb-6">
            <a href="/" class="font-black text-5xl tracking-tighter text-white flex gap-1">
                GAME<span class="text-green-500">BOX</span>
            </a>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-8 py-8 bg-[#24282f] shadow-2xl overflow-hidden sm:rounded-lg border border-gray-800">
            {{ $slot }}
        </div>
    </body>
</html>