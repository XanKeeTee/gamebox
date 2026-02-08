@props(['game'])
<div class="group relative bg-[#20242c] rounded-lg overflow-hidden shadow-lg hover:ring-2 hover:ring-green-500 transition-all duration-300">
    <a href="{{ route('games.show', $game->slug) }}" class="block aspect-[2/3] overflow-hidden relative">
        @if($game->cover_url)
            <img src="{{ $game->cover_url }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
        @else
            <div class="w-full h-full bg-gray-800 flex items-center justify-center text-gray-500 text-xs text-center p-2">Sin Portada</div>
        @endif
        
        <div class="absolute inset-0 bg-black/80 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center p-2 text-center">
            <span class="text-white font-bold text-sm">{{ $game->name }}</span>
            <span class="text-green-400 text-xs mt-1">Ver detalles</span>
        </div>
    </a>
</div>