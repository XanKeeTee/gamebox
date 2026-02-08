<x-app-layout>
    {{-- SECCIÓN HERO: Solo se muestra si hay un juego destacado --}}
    @if(isset($heroGame) && $heroGame)
    <div class="relative w-full h-[500px] overflow-hidden group">
        <div class="absolute inset-0 bg-cover bg-center blur-sm scale-110 opacity-40"
             style="background-image: url('{{ $heroGame->cover_url }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#14181c] via-[#14181c]/50 to-transparent"></div>
        
        <div class="absolute bottom-0 left-0 w-full p-8 md:p-16 flex items-end gap-8 max-w-7xl mx-auto">
            <a href="{{ route('games.show', $heroGame->slug) }}" class="hidden md:block w-48 rounded-lg shadow-2xl overflow-hidden border-2 border-white/20 hover:scale-105 transition duration-500">
                <img src="{{ $heroGame->cover_url }}" class="w-full" alt="{{ $heroGame->name }}">
            </a>
            <div class="mb-4">
                <span class="bg-green-600 text-white text-xs font-bold px-2 py-1 rounded uppercase tracking-wider mb-2 inline-block">Destacado</span>
                <h1 class="text-5xl md:text-7xl font-black text-white leading-none drop-shadow-lg mb-4">{{ $heroGame->name }}</h1>
                <a href="{{ route('games.show', $heroGame->slug) }}" class="bg-white text-black font-bold px-8 py-3 rounded hover:bg-gray-200 transition">Ver Ficha</a>
            </div>
        </div>
    </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16">
        
        {{-- SECCIÓN POPULARES --}}
        @if(isset($popularGames) && $popularGames->isNotEmpty())
        <section>
            <div class="flex justify-between items-end mb-6 border-b border-gray-800 pb-2">
                <h2 class="text-xl font-bold text-white uppercase tracking-wider">🔥 Populares</h2>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
                @foreach($popularGames as $game)
                    <x-game-card :game="$game" />
                @endforeach
            </div>
        </section>
        @endif

        {{-- SECCIÓN NUEVOS LANZAMIENTOS --}}
        @if(isset($newReleases) && $newReleases->isNotEmpty())
        <section>
            <div class="flex justify-between items-end mb-6 border-b border-gray-800 pb-2">
                <h2 class="text-xl font-bold text-white uppercase tracking-wider">🚀 Nuevos Lanzamientos</h2>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
                @foreach($newReleases as $game)
                    <x-game-card :game="$game" />
                @endforeach
            </div>
        </section>
        @endif
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- SECCIÓN ACTIVIDAD RECIENTE (Base de Datos Local) --}}
            <div class="md:col-span-2">
                <h2 class="text-lg font-bold text-white uppercase tracking-wider mb-4 border-b border-gray-800 pb-2">💬 Actividad Reciente</h2>
                <div class="space-y-4">
                    @forelse($recentReviews as $review)
                        <div class="bg-[#20242c] p-4 rounded-lg flex gap-4 hover:bg-[#252a33] transition">
                            <div class="shrink-0 w-16">
                                <img src="{{ $review->game->cover_url ?? '' }}" class="w-full rounded shadow" alt="Portada">
                            </div>
                            <div>
                                <h4 class="text-white font-bold text-sm">
                                    <a href="{{ route('games.show', $review->game->slug) }}">{{ $review->game->name }}</a>
                                    <span class="text-gray-500 font-normal">por</span> 
                                    <a href="{{ route('users.show', $review->user->name) }}" class="text-green-400 hover:underline">{{ $review->user->name }}</a>
                                </h4>
                                <div class="text-yellow-500 text-xs my-1">@for($i=0; $i<$review->rating; $i++) ★ @endfor</div>
                                <p class="text-gray-400 text-sm line-clamp-2 italic">"{{ $review->content }}"</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-gray-500 text-sm italic">Aún no hay reseñas en la comunidad.</div>
                    @endforelse
                </div>
            </div>

            {{-- SECCIÓN PRÓXIMAMENTE --}}
            @if(isset($upcomingGames) && $upcomingGames->isNotEmpty())
            <div>
                <h2 class="text-lg font-bold text-white uppercase tracking-wider mb-4 border-b border-gray-800 pb-2">📅 Próximamente</h2>
                <div class="space-y-3">
                    @foreach($upcomingGames as $game)
                        <a href="{{ route('games.show', $game->slug) }}" class="flex items-center gap-3 group">
                            <div class="w-10 h-14 bg-gray-800 rounded overflow-hidden">
                                @if($game->cover_url) 
                                    <img src="{{ $game->cover_url }}" class="w-full h-full object-cover group-hover:scale-110 transition" alt="Miniatura"> 
                                @endif
                            </div>
                            <div>
                                <h4 class="text-gray-300 text-sm font-bold group-hover:text-white transition">{{ $game->name }}</h4>
                                <span class="text-gray-600 text-xs">{{ \Carbon\Carbon::parse($game->first_release_date)->format('d M, Y') }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>