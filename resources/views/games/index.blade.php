<x-app-layout>
    <div class="min-h-screen bg-[#14181c] text-gray-100 font-sans">

        @if (isset($heroGame) && $heroGame)
            <div class="relative w-full h-[400px] overflow-hidden group">
                <div class="absolute inset-0 bg-cover bg-center blur-sm scale-110 opacity-40 group-hover:scale-100 transition duration-700"
                    style="background-image: url('{{ $heroGame->cover_url }}');"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-[#14181c] via-[#14181c]/60 to-transparent"></div>

                <div class="absolute bottom-0 left-0 w-full p-8 md:p-12 flex items-end gap-6 max-w-7xl mx-auto">
                    <div
                        class="hidden md:block w-40 rounded-lg shadow-2xl overflow-hidden border border-gray-600 rotate-3 group-hover:rotate-0 transition duration-500">
                        <img src="{{ $heroGame->cover_url }}" class="w-full">
                    </div>
                    <div class="mb-4">
                        <span
                            class="bg-green-600 text-white text-xs font-bold px-2 py-1 rounded uppercase tracking-wider mb-2 inline-block">Destacado</span>
                        <h1 class="text-4xl md:text-6xl font-black text-white leading-none drop-shadow-lg mb-2">
                            {{ $heroGame->name }}</h1>
                        <p class="text-gray-300 max-w-2xl line-clamp-2">{{ $heroGame->summary ?? '' }}</p>
                        <div class="mt-4 flex gap-3">
                            <a href="{{ route('games.show', $heroGame->slug) }}"
                                class="bg-white text-black font-bold px-6 py-2 rounded hover:bg-gray-200 transition">Ver
                                Ficha</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach ($games as $game)
                <div
                    class="group relative bg-[#20242c] rounded-lg overflow-hidden shadow-lg hover:ring-2 hover:ring-green-500 transition-all duration-300">
                    <a href="{{ route('games.show', $game->slug) }}"
                        class="block aspect-[2/3] overflow-hidden relative">
                        @if ($game->cover_url)
                            <img src="{{ $game->cover_url }}"
                                class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        @else
                            <div
                                class="w-full h-full bg-gray-800 flex items-center justify-center text-gray-500 text-xs text-center p-2">
                                Sin Portada</div>
                        @endif

                        <div
                            class="absolute inset-0 bg-black/80 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center p-2 text-center">
                            <span class="text-white font-bold text-sm">{{ $game->name }}</span>
                            <span class="text-green-400 text-xs mt-1">Ver detalles</span>
                        </div>
                    </a>

                    <div class="p-2 bg-[#20242c] border-t border-gray-800">
                        <h3 class="text-white text-xs font-bold truncate">{{ $game->name }}</h3>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-gray-500 text-[10px]">
                                {{ isset($game->first_release_date) ? \Carbon\Carbon::parse($game->first_release_date)->year : 'N/A' }}
                            </span>
                            @if (isset($game->rating))
                                <span class="text-yellow-500 text-[10px] font-bold">★
                                    {{ round($game->rating / 10, 1) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $games->links() }}
        </div>

    </div>
    </div>
</x-app-layout>
