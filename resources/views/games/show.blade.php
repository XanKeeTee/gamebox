<x-app-layout>
    @php
        $libraryEntry = null;
        $isLiked = false;
        $isWishlisted = false;

        // Si el usuario está logueado y el juego ya existe en BD, cargamos sus datos
        if(Auth::check() && $game->exists){
            $libraryEntry = Auth::user()->library()->where('game_id', $game->id)->first();
            if($libraryEntry){
                $isLiked = $libraryEntry->pivot->liked;
                $isWishlisted = $libraryEntry->pivot->wishlisted;
            }
        }
    @endphp

    @if(session('message')) <div class="fixed top-20 right-4 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-50">✅ {{ session('message') }}</div> @endif
    @if(session('error')) <div class="fixed top-20 right-4 bg-red-600 text-white px-4 py-2 rounded shadow-lg z-50">❌ {{ session('error') }}</div> @endif

    <div class="min-h-screen bg-[#14181c] text-gray-100 font-sans">
        
        <div class="relative h-[400px] w-full overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center blur-md opacity-30" style="background-image: url('{{ $game->cover_url }}');"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-[#14181c] to-transparent"></div>
            
            <div class="absolute bottom-0 w-full pb-8">
                <div class="max-w-6xl mx-auto px-4 flex flex-col md:flex-row gap-8 items-end">
                    <img src="{{ $game->cover_url }}" class="w-40 md:w-52 rounded-lg shadow-2xl border border-gray-700 z-10">
                    <div class="flex-1 mb-2">
                        <h1 class="text-4xl md:text-5xl font-black text-white leading-none mb-2">{{ $game->name }}</h1>
                        <div class="flex flex-wrap gap-2 text-sm text-gray-400">
                            @if(isset($game->first_release_date))
                                <span class="bg-gray-800 px-2 py-1 rounded border border-gray-700 font-bold">
                                    {{ \Carbon\Carbon::parse($game->first_release_date)->year }}
                                </span>
                            @endif
                            @foreach($game->genres ?? [] as $genre)
                                <span class="bg-gray-800 px-2 py-1 rounded border border-gray-700">{{ $genre }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <div class="space-y-6">
                <div class="bg-[#1b1e24] p-4 rounded-lg border border-gray-800 shadow-lg">
                    @auth
                        <div class="grid grid-cols-2 gap-2 mb-4">
                            <form action="{{ route('games.like', $game->slug) }}" method="POST"> @csrf
                                <button class="w-full py-2 rounded text-sm font-bold border border-transparent {{ $isLiked ? 'bg-orange-600 text-white' : 'bg-[#24282f] text-gray-300 hover:bg-gray-700' }}">♥ Me Gusta</button>
                            </form>
                            <form action="{{ route('games.wishlist', $game->slug) }}" method="POST"> @csrf
                                <button class="w-full py-2 rounded text-sm font-bold border border-transparent {{ $isWishlisted ? 'bg-blue-600 text-white' : 'bg-[#24282f] text-gray-300 hover:bg-gray-700' }}">+ Deseos</button>
                            </form>
                        </div>

                        <div class="border-t border-gray-700 pt-4">
                            <label class="text-xs font-bold text-gray-500 uppercase mb-2 block">Estado</label>
                            <form action="{{ route('games.status', $game->slug) }}" method="POST">
                                @csrf
                                <select name="status" onchange="this.form.submit()" class="w-full bg-[#14181c] border border-gray-600 text-gray-300 text-sm rounded p-2">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="playing" {{ ($libraryEntry && $libraryEntry->pivot->status === 'playing') ? 'selected' : '' }}>🎮 Jugando</option>
                                    <option value="completed" {{ ($libraryEntry && $libraryEntry->pivot->status === 'completed') ? 'selected' : '' }}>🏆 Completado</option>
                                    <option value="backlog" {{ ($libraryEntry && $libraryEntry->pivot->status === 'backlog') ? 'selected' : '' }}>📚 Backlog</option>
                                </select>
                            </form>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-700">
                            <h3 class="text-xs font-bold text-gray-500 uppercase mb-2">Añadir a lista</h3>
                            @if($userLists->count() > 0)
                            <form action="{{ route('lists.addGame', $game->slug) }}" method="POST" class="flex gap-2">
                                @csrf
                                <select name="list_id" class="flex-1 bg-[#14181c] border border-gray-600 text-gray-300 text-xs rounded p-2">
                                    @foreach($userLists as $list) <option value="{{ $list->id }}">{{ $list->title }}</option> @endforeach
                                </select>
                                <button class="bg-green-600 text-white px-3 rounded font-bold">+</button>
                            </form>
                            @else
                                <p class="text-xs text-gray-500">Crea una lista en tu perfil primero.</p>
                            @endif
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="block text-center bg-green-600 text-white py-2 rounded font-bold">Inicia sesión</a>
                    @endauth
                </div>

                <div class="text-gray-300 text-sm leading-relaxed">
                    <h3 class="text-xs font-bold text-gray-500 uppercase mb-2 border-b border-gray-800 pb-1">Sinopsis</h3>
                    <p>{{ $game->summary ?? 'Sin descripción.' }}</p>
                </div>
            </div>

            <div class="md:col-span-2">
                <h3 class="text-sm font-bold text-gray-400 uppercase mb-4 border-b border-gray-800 pb-2">Reseñas</h3>

                @auth
                <form action="{{ route('reviews.store', $game->slug) }}" method="POST" class="bg-[#1b1e24] p-4 rounded mb-6 flex gap-3">
                    @csrf
                    <textarea name="content" rows="2" class="w-full bg-transparent border-none text-white focus:ring-0 placeholder-gray-500 text-sm resize-none" placeholder="Escribe tu opinión..."></textarea>
                    <div>
                        <select name="rating" class="bg-[#14181c] text-white text-xs border border-gray-700 rounded py-1 mb-2 w-full">
                            <option value="5">★★★★★</option>
                            <option value="4">★★★★</option>
                            <option value="3">★★★</option>
                        </select>
                        <button class="bg-white text-black text-xs font-bold px-4 py-1.5 rounded w-full">Publicar</button>
                    </div>
                </form>
                @endauth

                @if($game->exists && $game->reviews->count() > 0)
                    <div class="space-y-4">
                        @foreach($game->reviews as $review)
                            <div class="bg-[#1b1e24] p-4 rounded border border-gray-800">
                                <div class="flex justify-between">
                                    <span class="font-bold text-white">{{ $review->user->name }}</span>
                                    <span class="text-green-500 text-xs">@for($i=0; $i<$review->rating; $i++) ★ @endfor</span>
                                </div>
                                <p class="text-gray-300 text-sm mt-2">{{ $review->content }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10 text-gray-500">
                        <p>Aún no hay reseñas. ¡Sé el primero!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>