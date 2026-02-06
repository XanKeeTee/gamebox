<nav x-data="{ open: false }" class="bg-[#1b1e24] border-b border-gray-800 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('games.index') }}" class="text-2xl font-black text-white tracking-tighter flex items-center gap-2">
                        <div class="w-8 h-8 bg-green-600 rounded flex items-center justify-center text-white text-lg">G</div>
                        <span>GameBox</span>
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <a href="{{ route('games.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-300 hover:text-white hover:border-green-500 focus:outline-none transition duration-150 ease-in-out {{ request()->routeIs('games.index') ? 'border-green-500 text-white' : '' }}">
                        Explorar
                    </a>
                </div>
            </div>

            <div class="flex-1 flex items-center justify-center px-6 hidden sm:flex">
                <form action="{{ route('games.index') }}" method="GET" class="w-full max-w-lg relative">
                    <input type="text" name="q" placeholder="Buscar juegos..." value="{{ request('q') }}"
                           class="w-full bg-[#14181c] border border-gray-700 text-gray-300 text-sm rounded-full py-2 pl-4 pr-10 focus:ring-1 focus:ring-green-500 focus:border-green-500 outline-none transition">
                    <button type="submit" class="absolute right-3 top-2.5 text-gray-500 hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </form>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                @auth
                    <a href="{{ route('notifications.index') }}" class="relative p-2 text-gray-400 hover:text-white transition mr-2 group" title="Notificaciones">
                        <svg class="w-6 h-6 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        
                        @if(Auth::user()->unreadNotifications->count() > 0)
                            <span class="absolute top-1 right-1 flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[9px] font-bold text-white items-center justify-center">
                                    {{ Auth::user()->unreadNotifications->count() }}
                                </span>
                            </span>
                        @endif
                    </a>

                    <div class="ml-3 relative" x-data="{ openDropdown: false }">
                        <div>
                            <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" class="flex items-center gap-2 text-sm font-medium text-gray-300 hover:text-white focus:outline-none transition duration-150 ease-in-out">
                                <div class="text-right hidden md:block">
                                    <div class="font-bold">{{ Auth::user()->name }}</div>
                                </div>
                                <div class="w-9 h-9 rounded-full bg-gray-600 overflow-hidden border border-gray-500">
                                    @if(Auth::user()->avatar)
                                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center font-bold text-white">
                                            {{ substr(Auth::user()->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </button>
                        </div>

                        <div x-show="openDropdown" style="display: none;" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-[#20242c] border border-gray-700 ring-1 ring-black ring-opacity-5 z-50 origin-top-right" x-transition>
                            
                            <a href="{{ route('users.show', Auth::user()->name) }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white">
                                Mi Perfil
                            </a>
                            
                            <a href="{{ route('users.show', ['name' => Auth::user()->name, 'tab' => 'lists']) }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white">
                                Mis Listas
                            </a>

                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white">
                                Configuración
                            </a>

                            <div class="border-t border-gray-700 my-1"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-gray-700 hover:text-red-300">
                                    Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="space-x-4">
                        <a href="{{ route('login') }}" class="text-gray-300 hover:text-white font-medium text-sm transition">Iniciar Sesión</a>
                        <a href="{{ route('register') }}" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded font-bold text-sm transition">Registrarse</a>
                    </div>
                @endauth
            </div>

            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    @auth
                        @if(Auth::user()->unreadNotifications->count() > 0)
                            <span class="absolute top-3 right-3 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-[#1b1e24]"></span>
                        @endif
                    @endauth
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-[#20242c] border-b border-gray-800">
        
        <div class="pt-4 pb-2 px-4">
            <form action="{{ route('games.index') }}" method="GET">
                <input type="text" name="q" placeholder="Buscar..." value="{{ request('q') }}" class="w-full bg-[#14181c] border border-gray-600 text-white text-sm rounded p-2 focus:border-green-500 outline-none">
            </form>
        </div>

        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('games.index') }}" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-300 hover:text-white hover:bg-gray-700 hover:border-green-500 transition duration-150 ease-in-out">
                Explorar Juegos
            </a>
        </div>

        <div class="pt-4 pb-4 border-t border-gray-700">
            @auth
                <div class="flex items-center px-4 mb-3">
                    <div class="shrink-0">
                        <div class="w-10 h-10 rounded-full bg-gray-600 overflow-hidden flex items-center justify-center text-white font-bold">
                            @if(Auth::user()->avatar)
                                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="w-full h-full object-cover">
                            @else
                                {{ substr(Auth::user()->name, 0, 1) }}
                            @endif
                        </div>
                    </div>
                    <div class="ml-3">
                        <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                        <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                    </div>
                    
                    <a href="{{ route('notifications.index') }}" class="ml-auto relative p-2 text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        @if(Auth::user()->unreadNotifications->count() > 0)
                             <span class="absolute top-1 right-1 bg-red-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">
                                {{ Auth::user()->unreadNotifications->count() }}
                            </span>
                        @endif
                    </a>
                </div>

                <div class="space-y-1">
                    <a href="{{ route('users.show', Auth::user()->name) }}" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-400 hover:text-white hover:bg-gray-700 hover:border-gray-300 transition duration-150 ease-in-out">
                        Mi Perfil
                    </a>
                    <a href="{{ route('users.show', ['name' => Auth::user()->name, 'tab' => 'lists']) }}" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-400 hover:text-white hover:bg-gray-700 hover:border-gray-300 transition duration-150 ease-in-out">
                        Mis Listas
                    </a>
                    <a href="{{ route('profile.edit') }}" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-400 hover:text-white hover:bg-gray-700 hover:border-gray-300 transition duration-150 ease-in-out">
                        Configuración
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-red-400 hover:text-red-300 hover:bg-gray-700 transition duration-150 ease-in-out">
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            @else
                <div class="px-4 space-y-3">
                    <a href="{{ route('login') }}" class="block text-center w-full bg-gray-700 text-white py-2 rounded">Iniciar Sesión</a>
                    <a href="{{ route('register') }}" class="block text-center w-full bg-green-600 text-white py-2 rounded font-bold">Registrarse</a>
                </div>
            @endauth
        </div>
    </div>
</nav>