<x-app-layout>
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="flex flex-col sm:flex-row justify-between items-end mb-8 border-b border-gray-800 pb-4 gap-4">
            <div>
                <h2 class="text-3xl font-black text-white tracking-tighter">
                    TU <span class="text-green-500">BIBLIOTECA</span>
                </h2>
                <p class="text-gray-400 text-sm mt-1">
                    Colección de <span class="font-bold text-white">{{ Auth::user()->name }}</span>
                </p>
            </div>

            <div class="flex bg-[#24282f] p-1 rounded-lg">
                <a href="{{ route('library.index', ['filter' => 'liked']) }}" 
                   class="px-6 py-2 rounded-md text-sm font-bold transition {{ $filter === 'liked' ? 'bg-green-600 text-white shadow' : 'text-gray-400 hover:text-white' }}">
                   ♥ Me Gusta
                </a>
                <a href="{{ route('library.index', ['filter' => 'wishlisted']) }}" 
                   class="px-6 py-2 rounded-md text-sm font-bold transition {{ $filter === 'wishlisted' ? 'bg-blue-600 text-white shadow' : 'text-gray-400 hover:text-white' }}">
                   + Lista de Deseos
                </a>
            </div>
        </div>

        @if($games->count() > 0)
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 xl:grid-cols-7 gap-4">
                @foreach($games as $game)
                    <div class="group relative aspect-[2/3]">
                        <a href="{{ route('games.show', $game->slug) }}" class="block w-full h-full">
                            <div class="w-full h-full rounded-md overflow-hidden bg-[#24282f] shadow-lg transition-transform duration-200 group-hover:scale-105 group-hover:ring-2 {{ $filter === 'liked' ? 'group-hover:ring-green-500' : 'group-hover:ring-blue-500' }} relative">
                                @if($game->cover_url)
                                    <img src="{{ $game->cover_url }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center p-2 text-center text-gray-500">
                                        <span class="text-xs">Sin imagen</span>
                                    </div>
                                @endif
                                
                                <div class="absolute inset-0 bg-black/80 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center p-2">
                                    <p class="text-white text-center text-xs font-bold">{{ $game->name }}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-8">
                {{ $games->appends(['filter' => $filter])->links() }}
            </div>
        @else
            <div class="text-center py-20 bg-[#1b1e24] rounded-xl border border-dashed border-gray-700">
                <div class="text-6xl mb-4 grayscale opacity-50">🎮</div>
                <h3 class="text-xl font-bold text-white mb-2">Está vacío...</h3>
                <a href="{{ route('games.index') }}" class="bg-green-600 hover:bg-green-500 text-white font-bold py-2 px-6 rounded transition">
                    Explorar Juegos
                </a>
            </div>
        @endif
    </div>
</x-app-layout>