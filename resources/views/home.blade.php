<x-app-layout>
    @if ($heroGame)
        <div class="relative w-full h-[500px] overflow-hidden group">
            <div class="absolute inset-0 bg-cover bg-center blur-sm scale-110 opacity-40"
                style="background-image: url('{{ $heroGame->cover_url }}');"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#14181c] via-[#14181c]/50 to-transparent"></div>

            <div class="absolute bottom-0 left-0 w-full p-8 md:p-16 flex items-end gap-8 max-w-7xl mx-auto">
                <a href="{{ route('games.show', $heroGame->slug) }}"
                    class="hidden md:block w-48 rounded-lg shadow-2xl overflow-hidden border-2 border-white/20 hover:scale-105 transition duration-500">
                    <img src="{{ $heroGame->cover_url }}" class="w-full">
                </a>
                <div class="mb-4">
                    <span
                        class="bg-green-600 text-white text-xs font-bold px-2 py-1 rounded uppercase tracking-wider mb-2 inline-block">Tendencia
                        #1</span>
                    <h1 class="text-5xl md:text-7xl font-black text-white leading-none drop-shadow-lg mb-4">
                        {{ $heroGame->name }}</h1>
                    <p class="text-gray-200 text-lg mb-6">¡Descubre el juego más popular de la semana en GameBox!</p>
                    <a href="{{ route('games.show', $heroGame->slug) }}"
                        class="bg-white text-black font-bold px-8 py-3 rounded hover:bg-gray-200 transition">
                        Ver Detalles
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16">

        <section>
            <div class="flex justify-between items-end mb-6 border-b border-gray-800 pb-2">
                <h2 class="text-xl font-bold text-white uppercase tracking-wider">🔥 Populares esta semana</h2>
                <a href="{{ route('games.index') }}"
                    class="text-xs text-gray-500 hover:text-white uppercase font-bold">Ver más</a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
                @foreach ($popularGames as $game)
                    <x-game-card :game="$game" />
                @endforeach
            </div>
        </section>

        <section>
            <div class="flex justify-between items-end mb-6 border-b border-gray-800 pb-2">
                <h2 class="text-xl font-bold text-white uppercase tracking-wider">🚀 Nuevos Lanzamientos</h2>
                <a href="{{ route('games.index', ['sort' => 'newest']) }}"
                    class="text-xs text-gray-500 hover:text-white uppercase font-bold">Ver más</a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
                @foreach ($newReleases as $game)
                    <x-game-card :game="$game" />
                @endforeach
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-2">
                <h2 class="text-lg font-bold text-white uppercase tracking-wider mb-4 border-b border-gray-800 pb-2">💬
                    Últimas Reseñas</h2>
                <div class="space-y-4">
                    @foreach ($recentReviews as $review)
                        <div class="bg-[#20242c] p-4 rounded-lg flex gap-4 hover:bg-[#252a33] transition">
                            <div class="shrink-0 w-16">
                                <img src="{{ $review->game->cover_url ?? 'https://via.placeholder.com/150' }}"
                                    class="w-full rounded shadow">
                            </div>
                            <div>
                                <h4 class="text-white font-bold text-sm">
                                    <a
                                        href="{{ route('games.show', $review->game->slug) }}">{{ $review->game->name }}</a>
                                    <span class="text-gray-500 font-normal">por</span>
                                    <a href="{{ route('users.show', $review->user->name) }}"
                                        class="text-green-400 hover:underline">{{ $review->user->name }}</a>
                                </h4>
                                <div class="text-yellow-500 text-xs my-1">
                                    @for ($i = 0; $i < $review->rating; $i++)
                                        ★
                                    @endfor
                                </div>
                                <p class="text-gray-400 text-sm line-clamp-2 italic">"{{ $review->content }}"</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <h2 class="text-lg font-bold text-white uppercase tracking-wider mb-4 border-b border-gray-800 pb-2">📅
                    Próximamente</h2>
                <div class="space-y-3">
                    @foreach ($upcomingGames as $game)
                        <a href="{{ route('games.show', $game->slug) }}" class="flex items-center gap-3 group">
                            <div class="w-10 h-14 bg-gray-800 rounded overflow-hidden">
                                @if ($game->cover_url)
                                    <img src="{{ $game->cover_url }}"
                                        class="w-full h-full object-cover group-hover:scale-110 transition">
                                @endif
                            </div>
                            <div>
                                <h4 class="text-gray-300 text-sm font-bold group-hover:text-white transition">
                                    {{ $game->name }}</h4>
                                <span
                                    class="text-gray-600 text-xs">{{ \Carbon\Carbon::parse($game->first_release_date)->format('d M, Y') }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

    </div>
</x-app-layout>

@verbatim
    <template id="game-card-template">
    </template>
@endverbatim
