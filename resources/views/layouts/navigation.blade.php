<nav x-data="{ open: false }" class="bg-gray-800 border-b border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('games.index') }}" class="text-white font-black text-2xl">
                        GAME<span class="text-green-500">BOX</span>
                    </a>
                </div>
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <a href="{{ route('games.index') }}"
                        class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-300 hover:text-white hover:border-gray-300 transition">
                        Juegos
                    </a>
                </div>
            </div>
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                @auth
                    <div class="ml-3 relative">
                        <span class="text-gray-300 text-sm font-bold">{{ Auth::user()->name }}</span>
                    </div>
                @else
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" class="text-sm font-bold text-gray-300 hover:text-white transition">
                            Entrar
                        </a>
                        <a href="{{ route('register') }}"
                            class="bg-green-600 hover:bg-green-500 text-white text-sm font-bold px-4 py-2 rounded transition shadow-lg shadow-green-900/20">
                            Registrarse
                        </a>
                    </div>
                @endauth        
            </div>
        </div>
    </div>
</nav>
