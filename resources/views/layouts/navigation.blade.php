<nav x-data="{ open: false }" class="bg-[#161b22] border-b border-gray-800 sticky top-0 z-50 shadow-sm backdrop-blur-md bg-opacity-95">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                        <div class="w-8 h-8 bg-green-600 rounded flex items-center justify-center text-white font-black text-xl group-hover:bg-green-500 transition shadow-lg shadow-green-900/20">
                            G
                        </div>
                        <span class="text-xl font-bold text-white tracking-tight group-hover:text-green-400 transition">GameBox</span>
                    </a>
                </div>

                <div class="hidden space-x-1 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('games.index')" :active="request()->routeIs('games.index')" class="text-gray-300 hover:text-white px-3 py-2 rounded-md transition text-sm font-medium">
                        Explorar
                    </x-nav-link>
                    
                    <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')" class="text-gray-300 hover:text-white px-3 py-2 rounded-md transition text-sm font-medium">
                        Comunidad
                    </x-nav-link>

                    @auth
                        <x-nav-link :href="route('library.index')" :active="request()->routeIs('library.index')" class="text-gray-300 hover:text-white px-3 py-2 rounded-md transition text-sm font-medium">
                            Mi Biblioteca
                        </x-nav-link>
                    @endauth
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-1.5 border border-gray-700 text-sm leading-4 font-medium rounded-full text-gray-300 bg-[#0d1117] hover:text-white hover:border-gray-500 focus:outline-none transition ease-in-out duration-150">
                                <div class="flex items-center gap-2">
                                    @if(Auth::user()->avatar)
                                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="w-6 h-6 rounded-full object-cover">
                                    @else
                                        <div class="w-6 h-6 rounded-full bg-gray-700"></div>
                                    @endif
                                    <span class="truncate max-w-[100px]">{{ Auth::user()->name }}</span>
                                </div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('users.show', Auth::user()->name)">👤 Mi Perfil</x-dropdown-link>
                            <x-dropdown-link :href="route('profile.edit')">⚙️ Configuración</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-400 hover:text-red-300">
                                    🚪 Cerrar Sesión
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" class="text-sm font-bold text-gray-300 hover:text-white transition">Log in</a>
                        <a href="{{ route('register') }}" class="text-sm font-bold bg-white text-black px-4 py-2 rounded hover:bg-gray-200 transition">Sign up</a>
                    </div>
                @endauth
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-900 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-[#161b22] border-t border-gray-800">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('games.index')" :active="request()->routeIs('games.index')">Explorar</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')">Comunidad</x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="route('library.index')" :active="request()->routeIs('library.index')">Biblioteca</x-responsive-nav-link>
            @endauth
        </div>
    </div>
</nav>