<x-app-layout>
    @if(!request('q') && isset($heroGame) && $heroGame)
        <div class="relative w-full h-[500px] overflow-hidden group">
            <div class="absolute inset-0 bg-cover bg-center transition duration-700 transform group-hover:scale-105"
                 style="background-image: url('{{ $heroGame->cover_url }}');">
                <div class="absolute inset-0 bg-[#14181c]/80 backdrop-blur-sm"></div>
            </div>
            
            <div class="absolute inset-0 bg-gradient-to-t from-[#14181c] via-transparent to-transparent"></div>
            
            <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center">
                <div class="flex flex-col md:flex-row items-end gap-8 w-full pb-12">
                    <div class="hidden md:block w-52 rounded-lg shadow-2xl overflow-hidden border border-gray-600/50 transform rotate-3 hover:rotate-0 transition duration-500">
                        <img src="{{ $heroGame->cover_url }}" class="w-full h-auto">
                    </div>
                    
                    <div class="flex-1 space-y-4">
                        <span class="bg-green-600 text-white text-xs font-bold px-2 py-1 rounded uppercase tracking-wider">Destacado</span>
                        <h1 class="text-5xl md:text-7xl font-black text-white leading-none drop-shadow-xl">
                            {{ $heroGame->name }}
                        </h1>
                        <p class="text-gray-300 max-w-2xl text-lg line-clamp-2 drop-shadow-md">
                            {{ $heroGame->summary ?? 'Descubre este increíble título en nuestra colección.' }}
                        </p>
                        
                        <div class="pt-4 flex gap-4">
                            <a href="{{ route('games.show', $heroGame->slug) }}" class="bg-white text-black font-bold px-8 py-3 rounded hover:bg-gray-200 transition shadow-lg flex items-center gap-2">
                                <span>Ver Juego</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        @if(!request('q') && isset($latestReviews) && $latestReviews->count() > 0)
            <div class="mb-12 border-b border-gray-800 pb-12">
                <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                    <span class="text-green-500">●</span> Actividad Reciente
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($latestReviews as $review)
                        <div class="bg-[#20242c] p-4 rounded-xl border border-gray-800 hover:border-gray-600 transition group flex gap-4">
                            <a href="{{ route('games.show', $review->game->slug) }}" class="shrink-0 w-20 h-28 rounded overflow-hidden shadow-lg">
                                <img src="{{ $review->game->cover_url }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                            </a>
                            <div class="flex-1 flex flex-col">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-5 h-5 rounded-full overflow-hidden bg-gray-700">
                                        @if($review->user && $review->user->avatar)
                                            <img src="{{ asset('storage/' . $review->user->avatar) }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-[10px] text-white font-bold">{{ substr($review->user->name ?? 'U', 0, 1) }}</div>
                                        @endif
                                    </div>
                                    <span class="text-xs text-gray-400 font-bold">{{ $review->user->name ?? 'Usuario' }}</span>
                                    <span class="text-green-500 text-xs ml-auto font-mono">★ {{ $review->rating }}</span>
                                </div>
                                <a href="{{ route('games.show', $review->game->slug) }}" class="text-white font-bold leading-tight hover:text-green-400 transition line-clamp-1 mb-1">
                                    {{ $review->game->name }}
                                </a>
                                <p class="text-gray-500 text-xs line-clamp-3 leading-relaxed italic">
                                    "{{ $review->content }}"
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div id="games-container">
            @include('games.partials.grid')
        </div>
        
        <div id="loading-spinner" class="hidden py-12 text-center">
            <div class="inline-block w-8 h-8 border-4 border-green-500 border-t-transparent rounded-full animate-spin"></div>
        </div>
    </div>
</x-app-layout>