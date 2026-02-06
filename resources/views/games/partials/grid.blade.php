<div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
    @foreach($games as $game)
        <div class="group relative aspect-[2/3] rounded-lg overflow-hidden bg-[#20242c] shadow-md transition-all duration-300 hover:shadow-xl hover:z-10 hover:scale-105 hover:ring-2 hover:ring-green-500">
            <a href="{{ route('games.show', $game->slug) }}" class="block w-full h-full">
                
                @if($game->cover_url)
                    <img src="{{ $game->cover_url }}" 
                         loading="lazy"
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                         alt="{{ $game->name }}">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-gray-600 p-2 text-center bg-gray-800">
                        <span class="text-xs font-bold">{{ $game->name }}</span>
                    </div>
                @endif

                <div class="absolute inset-0 bg-black/80 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex flex-col items-center justify-center p-2 text-center">
                    <span class="text-white font-bold text-sm leading-tight mb-2">{{ $game->name }}</span>
                    
                    @if($game->first_release_date)
                        <span class="text-xs text-gray-400 bg-gray-800 px-2 py-0.5 rounded-full">
                            {{ \Carbon\Carbon::parse($game->first_release_date)->year }}
                        </span>
                    @endif
                </div>

                @auth
                    @php
                        // Nota: Para que esto funcione rápido en el grid, idealmente deberíamos cargar la relación en el controlador.
                        // Pero para mantenerlo simple, verificamos si 'users' está cargado (lo hicimos en el Controller)
                        $userPivot = $game->users->firstWhere('id', Auth::id());
                    @endphp
                    @if($userPivot)
                        <div class="absolute top-1 right-1 flex flex-col gap-1 z-20">
                            @if($userPivot->pivot->liked)
                                <span class="bg-orange-600/90 text-white text-[10px] p-1 rounded-md shadow-sm backdrop-blur-sm">♥</span>
                            @endif
                            @if($userPivot->pivot->wishlisted)
                                <span class="bg-blue-600/90 text-white text-[10px] p-1 rounded-md shadow-sm backdrop-blur-sm">+</span>
                            @endif
                        </div>
                    @endif
                @endauth
            </a>
        </div>
    @endforeach
</div>

<div class="mt-8">
    {{ $games->links() }}
</div>