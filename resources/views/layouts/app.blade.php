<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GameBox</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,600,800&display=swap" rel="stylesheet" />
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { backloggd: '#16181c', card: '#24282f', accent: '#445fce' },
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-track { background: #16181c; }
        ::-webkit-scrollbar-thumb { background: #333; border-radius: 5px; }
    </style>
</head>
<body class="bg-backloggd text-gray-200 antialiased min-h-screen flex flex-col">
    
    <nav class="bg-[#24282f] border-b border-gray-800 sticky top-0 z-50 h-16 shrink-0">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 h-full flex justify-between items-center">
            
            <a href="{{ route('games.index') }}" class="font-black text-2xl tracking-tighter text-white mr-4 shrink-0">
                GAME<span class="text-green-500">BOX</span>
            </a>

            <div class="flex-1 max-w-xl mx-4 lg:mx-8">
                <form action="{{ route('games.index') }}" method="GET" class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-500 group-focus-within:text-white transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" 
                           name="q"
                           id="search-input"
                           placeholder="Buscar juego..." 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-700 rounded-full leading-5 bg-gray-900 text-gray-300 placeholder-gray-500 focus:outline-none focus:bg-black focus:text-white focus:border-green-500 transition duration-150 ease-in-out sm:text-sm"
                           autocomplete="off"
                           value="{{ request('q') }}"> </form>
            </div>

            <div class="shrink-0">
                @auth
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.away="open = false" class="flex items-center gap-3 group focus:outline-none">
                            <div class="w-9 h-9 rounded-full overflow-hidden border border-gray-600 group-hover:border-green-500 transition ring-offset-2 ring-offset-[#24282f] group-focus:ring-2 group-focus:ring-green-500">
                                @if(Auth::user()->avatar)
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gray-700 flex items-center justify-center text-xs font-bold text-white">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 group-hover:text-white transition" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" 
                             x-transition
                             class="absolute right-0 mt-2 w-48 bg-[#1b1e24] rounded-md shadow-xl border border-gray-700 py-1 z-50 origin-top-right"
                             style="display: none;">
                            
                            <div class="block px-4 py-2 text-xs text-gray-500 border-b border-gray-700 mb-1">
                                {{ Auth::user()->name }}
                            </div>

                            <a href="{{ route('users.show', Auth::user()->name) }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition">
                                👤 Mi Perfil
                            </a>
                            
                            <div class="border-t border-gray-700 my-1"></div>

                            <a href="{{ route('library.index', ['filter' => 'liked']) }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition group">
                                <span class="text-orange-500 group-hover:text-orange-400 mr-2">♥</span> Mis Me Gusta
                            </a>
                            <a href="{{ route('library.index', ['filter' => 'wishlisted']) }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition group">
                                <span class="text-blue-500 group-hover:text-blue-400 mr-2">+</span> Lista de Deseos
                            </a>

                            <div class="border-t border-gray-700 my-1"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-red-900/20 hover:text-red-300 transition">
                                    Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" class="text-sm font-bold text-white hover:text-green-400 transition">Entrar</a>
                        <a href="{{ route('register') }}" class="bg-green-600 hover:bg-green-500 text-white text-sm font-bold px-4 py-2 rounded transition shadow-lg shadow-green-900/20">Registrarse</a>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <main class="flex-1">
        {{ $slot }}
    </main>

    <script>
        let timeout = null;
        const searchInput = document.getElementById('search-input');
        const gamesContainer = document.getElementById('games-container');
        const loadingSpinner = document.getElementById('loading-spinner');

        // Solo activamos la búsqueda "en vivo" si existe el contenedor de juegos (estamos en Home)
        if(searchInput && gamesContainer) {
            searchInput.addEventListener('input', function (e) {
                clearTimeout(timeout);
                if(loadingSpinner) loadingSpinner.classList.remove('hidden');

                timeout = setTimeout(() => {
                    const query = e.target.value;
                    fetch(`{{ route('games.index') }}?q=${query}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.text())
                    .then(html => {
                        gamesContainer.innerHTML = html;
                        if(loadingSpinner) loadingSpinner.classList.add('hidden');
                    })
                    .catch(error => console.error('Error:', error));
                }, 400);
            });
        }
    </script>
</body>
</html>