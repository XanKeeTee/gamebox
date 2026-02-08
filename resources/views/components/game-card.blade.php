@props(['game'])
<div class="group relative bg-[#161b22] rounded-lg overflow-hidden border border-gray-800 hover:border-gray-600 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-black/50">
    <a href="{{ route('games.show', $game->slug) }}" class="block w-full aspect-[2/3] overflow-hidden relative bg-gray-900">
        @if($game->cover_url)
            <img src="{{ $game->cover_url }}" loading="lazy" class="w-full h-full object-cover transition duration-500 group-hover:scale-105 group-hover:opacity-80">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-gray-600 p-4 text-center">
                <span class="text-2xl mb-2">🎮</span>
                <span class="text-xs font-bold">Sin Portada</span>
            </div>
        @endif
        
        <div class="absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-200 backdrop-blur-[2px]">
            <span class="bg-green-600 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg transform scale-90 group-hover:scale-100 transition">Ver Ficha</span>
        </div>
        
        @if(isset($game->rating) && $game->rating > 0)
            <div class="absolute top-2 right-2 bg-black/80 backdrop-blur text-white text-[10px] font-bold px-1.5 py-0.5 rounded border border-gray-700">
                ★ {{ round($game->rating) }}
            </div>
        @endif
    </a>
    
    <div class="p-3">
        <h3 class="text-gray-100 text-sm font-bold truncate leading-tight group-hover:text-green-400 transition">{{ $game->name }}</h3>
        <div class="flex justify-between items-center mt-1">
            <span class="text-gray-500 text-[11px]">
                {{ isset($game->first_release_date) ? \Carbon\Carbon::parse($game->first_release_date)->year : '' }}
            </span>
        </div>
    </div>
</div>