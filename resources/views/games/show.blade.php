<x-app-layout>
    @if(session('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
             class="fixed top-20 right-4 bg-green-600 text-white px-6 py-3 rounded shadow-xl z-50 font-bold flex items-center gap-2 transition"
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <span>✅</span> {{ session('message') }}
        </div>
    @endif
    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
             class="fixed top-20 right-4 bg-red-600 text-white px-6 py-3 rounded shadow-xl z-50 font-bold flex items-center gap-2 transition">
            <span>❌</span> {{ session('error') }}
        </div>
    @endif

    <div class="min-h-screen bg-[#14181c] text-gray-100 font-sans">
        
        <div class="relative h-[400px] w-full overflow-hidden">
            @if($game->cover_url)
                <div class="absolute inset-0 bg-cover bg-center bg-no-repeat blur-sm opacity-30"
                     style="background-image: url('{{ $game->cover_url }}');">
                </div>
            @else
                <div class="absolute inset-0 bg-gray-800"></div>
            @endif
            
            <div class="absolute inset-0 bg-gradient-to-t from-[#14181c] via-[#14181c]/40 to-transparent"></div>

            <div class="absolute bottom-0 w-full">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row gap-8 items-end pb-8">
                    <div class="w-40 md:w-52 flex-shrink-0 rounded-lg shadow-2xl border border-gray-700/50 overflow-hidden relative z-10">
                        @if($game->cover_url)
                            <img src="{{ $game->cover_url }}" class="w-full h-auto object-cover">
                        @else
                            <div class="w-full h-64 bg-gray-800 flex items-center justify-center">Sin Portada</div>
                        @endif
                    </div>
                    <div class="flex-1 mb-2">
                        <h1 class="text-4xl md:text-5xl font-black text-white leading-none drop-shadow-md mb-3">{{ $game->name }}</h1>
                        <div class="flex items-center gap-4 text-sm text-gray-400">
                            <span class="bg-gray-800 px-2 py-1 rounded border border-gray-700 font-bold text-gray-300">
                                {{ $game->first_release_date ? \Carbon\Carbon::parse($game->first_release_date)->year : 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <div class="space-y-6">
                <div class="bg-[#1b1e24] p-4 rounded-lg border border-gray-800 shadow-lg">
                    
                    @auth
                        <a href="#review-form" 
                           onclick="setTimeout(() => document.getElementById('review-content').focus(), 100);"
                           class="block w-full bg-green-600 hover:bg-green-500 text-white font-bold py-3 rounded mb-3 transition text-center shadow-lg shadow-green-900/20">
                            Escribir Reseña
                        </a>

                        <div class="grid grid-cols-2 gap-2">
                            @php
                                $libraryEntry = Auth::user()->library()->where('game_id', $game->id)->first();
                                $isLiked = $libraryEntry ? $libraryEntry->pivot->liked : false;
                                $isWishlisted = $libraryEntry ? $libraryEntry->pivot->wishlisted : false;
                            @endphp

                            <form action="{{ route('games.like', $game->slug) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-2 rounded text-sm transition font-bold flex items-center justify-center gap-2 border border-transparent {{ $isLiked ? 'bg-orange-600 text-white' : 'bg-[#24282f] text-gray-300 border-gray-700 hover:bg-gray-700' }}">
                                    <span>♥</span> Me gusta
                                </button>
                            </form>

                            <form action="{{ route('games.wishlist', $game->slug) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-2 rounded text-sm transition font-bold flex items-center justify-center gap-2 border border-transparent {{ $isWishlisted ? 'bg-blue-600 text-white' : 'bg-[#24282f] text-gray-300 border-gray-700 hover:bg-gray-700' }}">
                                    <span>+</span> Deseos
                                </button>
                            </form>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-700">
                            <label class="text-xs font-bold text-gray-500 uppercase mb-2 block">Estado de Juego</label>
                            
                            <form action="{{ route('games.status', $game->slug) }}" method="POST">
                                @csrf
                                <select name="status" onchange="this.form.submit()" 
                                        class="w-full bg-[#14181c] border border-gray-600 text-gray-300 text-sm rounded focus:ring-green-500 focus:border-green-500 p-2.5 outline-none">
                                    <option value="" class="text-gray-500">-- Seleccionar --</option>
                                    <option value="playing" {{ ($libraryEntry && $libraryEntry->pivot->status === 'playing') ? 'selected' : '' }}>🎮 Jugando</option>
                                    <option value="completed" {{ ($libraryEntry && $libraryEntry->pivot->status === 'completed') ? 'selected' : '' }}>🏆 Completado</option>
                                    <option value="on_hold" {{ ($libraryEntry && $libraryEntry->pivot->status === 'on_hold') ? 'selected' : '' }}>⏸️ Pausado</option>
                                    <option value="dropped" {{ ($libraryEntry && $libraryEntry->pivot->status === 'dropped') ? 'selected' : '' }}>💀 Abandonado</option>
                                    <option value="backlog" {{ ($libraryEntry && $libraryEntry->pivot->status === 'backlog') ? 'selected' : '' }}>📚 Backlog</option>
                                </select>
                            </form>
                        </div>

                        <div class="mt-6 pt-6 border-t border-gray-700">
                            <h3 class="text-xs font-bold text-gray-500 uppercase mb-3">Tus Listas</h3>

                            @if(isset($userLists) && $userLists->count() > 0)
                                <form action="{{ route('lists.addGame', $game->slug) }}" method="POST" class="mb-4">
                                    @csrf
                                    <div class="flex gap-2">
                                        <select name="list_id" class="flex-1 bg-[#14181c] border border-gray-600 text-gray-300 text-xs rounded focus:ring-green-500 focus:border-green-500 p-2 outline-none">
                                            <option value="" disabled selected>Elegir lista...</option>
                                            @foreach($userLists as $list)
                                                <option value="{{ $list->id }}">{{ $list->title }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold px-3 py-2 rounded transition shadow-lg">+</button>
                                    </div>
                                </form>
                            @endif

                            <div x-data="{ open: false }">
                                <button @click="open = !open" type="button" class="text-xs text-green-500 hover:text-green-400 font-bold flex items-center gap-1 transition">
                                    <span x-text="open ? '−' : '+'" class="text-sm"></span> Crear nueva lista
                                </button>

                                <div x-show="open" style="display: none;" class="mt-3 space-y-2 bg-[#20242c] p-3 rounded border border-gray-700" x-transition>
                                    <form action="{{ route('lists.store') }}" method="POST">
                                        @csrf
                                        <input type="text" name="title" placeholder="Nombre (ej: Terror...)" required
                                               class="w-full bg-[#14181c] border border-gray-600 text-gray-300 text-xs rounded p-2 mb-2 focus:border-green-500 outline-none placeholder-gray-500">
                                        
                                        <textarea name="description" placeholder="Descripción (Opcional)" rows="2"
                                                  class="w-full bg-[#14181c] border border-gray-600 text-gray-300 text-xs rounded p-2 mb-2 focus:border-green-500 outline-none resize-none placeholder-gray-500"></textarea>
                                        
                                        <button type="submit" class="w-full bg-green-600 hover:bg-green-500 text-white text-xs font-bold py-2 rounded transition">Guardar Lista</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    @else
                        <a href="{{ route('login') }}" class="block w-full bg-green-600 hover:bg-green-500 text-white font-bold py-3 rounded mb-3 transition text-center shadow-lg shadow-green-900/20">
                            Registrar o Reseñar
                        </a>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('login') }}" class="bg-[#24282f] hover:bg-gray-700 text-gray-300 py-2 rounded text-sm flex justify-center gap-2 border border-gray-700">♥ Me gusta</a>
                            <a href="{{ route('login') }}" class="bg-[#24282f] hover:bg-gray-700 text-gray-300 py-2 rounded text-sm flex justify-center gap-2 border border-gray-700">+ Deseos</a>
                        </div>
                    @endauth
                </div>

                <div class="text-gray-300 text-sm leading-relaxed">
                    <h3 class="text-xs font-bold text-gray-500 uppercase mb-2 border-b border-gray-800 pb-1">Sinopsis</h3>
                    <p>{{ $game->summary ?? 'Sin descripción disponible.' }}</p>
                </div>
            </div>

            <div class="md:col-span-2">
                <h3 class="text-sm font-bold text-gray-400 uppercase mb-4 border-b border-gray-800 pb-2">Reseñas de la Comunidad</h3>

                @auth
                    <div id="review-form" class="bg-[#1b1e24] p-4 rounded-lg border border-gray-800 mb-6 flex gap-3">
                        <div class="w-10 h-10 rounded-full bg-green-600 shrink-0 flex items-center justify-center font-bold text-xs text-white overflow-hidden">
                            @if(Auth::user()->avatar)
                                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="w-full h-full object-cover">
                            @else
                                {{ substr(Auth::user()->name, 0, 1) }}
                            @endif
                        </div>
                        <form action="{{ route('reviews.store', $game->slug) }}" method="POST" class="flex-1">
                            @csrf
                            <textarea id="review-content" name="content" rows="2" class="w-full bg-transparent border-none text-white focus:ring-0 placeholder-gray-500 text-sm resize-none" placeholder="Escribe tu opinión sobre el juego..."></textarea>
                            <div class="flex justify-between items-center mt-2 border-t border-gray-800 pt-3">
                                <select name="rating" class="bg-[#14181c] text-white text-xs border border-gray-700 rounded py-1 outline-none">
                                    <option value="5">★★★★★ Excelente</option>
                                    <option value="4">★★★★ Muy bueno</option>
                                    <option value="3">★★★ Bueno</option>
                                    <option value="2">★★ Regular</option>
                                    <option value="1">★ Malo</option>
                                </select>
                                <button type="submit" class="text-xs bg-white hover:bg-gray-200 text-black font-bold px-4 py-1.5 rounded transition">Publicar</button>
                            </div>
                        </form>
                    </div>
                @endauth

                <div class="space-y-6">
                    @forelse($game->reviews as $review)
                        <div x-data="{ showComments: false }" class="bg-[#1b1e24]/50 rounded-lg border border-gray-800/50 overflow-hidden">
                            
                            <div class="p-4">
                                <div class="flex items-start gap-3">
                                    <div class="shrink-0">
                                        @if($review->user)
                                            <a href="{{ route('users.show', $review->user->name) }}">
                                                <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center font-bold text-white overflow-hidden">
                                                    @if($review->user->avatar)
                                                        <img src="{{ asset('storage/' . $review->user->avatar) }}" class="w-full h-full object-cover">
                                                    @else
                                                        {{ substr($review->user->name, 0, 1) }}
                                                    @endif
                                                </div>
                                            </a>
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-gray-600 flex items-center justify-center font-bold text-white">?</div>
                                        @endif
                                    </div>

                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                @if($review->user)
                                                    <a href="{{ route('users.show', $review->user->name) }}" class="font-bold text-gray-200 text-sm hover:text-white transition">{{ $review->user->name }}</a>
                                                @else
                                                    <span class="font-bold text-gray-500 text-sm italic">Usuario Eliminado</span>
                                                @endif
                                                
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-green-500 text-xs">@for($i=0; $i<$review->rating; $i++) ★ @endfor</span>
                                                    <span class="text-gray-600 text-xs">• {{ $review->created_at->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-gray-300 text-sm mt-2 leading-relaxed">{{ $review->content }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-[#14181c] px-4 py-2 border-t border-gray-800 flex items-center gap-4 text-xs">
                                @auth
                                    @php 
                                        $myVote = $review->userVote(Auth::id());
                                        $isLiked = $myVote && $myVote->is_like;
                                        $isDisliked = $myVote && !$myVote->is_like;
                                    @endphp

                                    <form action="{{ route('reviews.vote', $review->id) }}" method="POST">
                                        @csrf <input type="hidden" name="is_like" value="1">
                                        <button type="submit" class="flex items-center gap-1 hover:text-green-400 transition {{ $isLiked ? 'text-green-500 font-bold' : 'text-gray-500' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
                                            <span>{{ $review->likesCount() }}</span>
                                        </button>
                                    </form>

                                    <form action="{{ route('reviews.vote', $review->id) }}" method="POST">
                                        @csrf <input type="hidden" name="is_like" value="0">
                                        <button type="submit" class="flex items-center gap-1 hover:text-red-400 transition {{ $isDisliked ? 'text-red-500 font-bold' : 'text-gray-500' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.095c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"></path></svg>
                                            <span>{{ $review->dislikesCount() }}</span>
                                        </button>
                                    </form>
                                @else
                                    <span class="flex items-center gap-1 text-gray-500">👍 {{ $review->likesCount() }}</span>
                                    <span class="flex items-center gap-1 text-gray-500">👎 {{ $review->dislikesCount() }}</span>
                                @endauth

                                <button @click="showComments = !showComments" class="flex items-center gap-1 text-gray-500 hover:text-blue-400 transition ml-auto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                                    <span>{{ $review->comments->count() }} Comentarios</span>
                                </button>
                            </div>

                            <div x-show="showComments" style="display: none;" class="bg-[#0f1115] p-4 border-t border-gray-800" x-transition>
                                @if($review->comments->count() > 0)
                                    <div class="space-y-3 mb-4 pl-2 border-l-2 border-gray-800">
                                        @foreach($review->comments as $comment)
                                            <div class="text-xs">
                                                <div class="flex justify-between text-gray-500 mb-1">
                                                    <span class="font-bold text-gray-400">{{ $comment->user ? $comment->user->name : 'Usuario' }}</span>
                                                    <span>{{ $comment->created_at->diffForHumans() }}</span>
                                                </div>
                                                <p class="text-gray-300">{{ $comment->content }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @auth
                                    <form action="{{ route('reviews.comment', $review->id) }}" method="POST" class="flex gap-2">
                                        @csrf
                                        <input type="text" name="content" placeholder="Responde..." class="flex-1 bg-[#1b1e24] border border-gray-700 rounded px-3 py-1.5 text-xs text-white focus:border-green-500 outline-none" autocomplete="off">
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold px-3 py-1 rounded transition">Enviar</button>
                                    </form>
                                @else
                                    <p class="text-xs text-gray-500 italic">Inicia sesión para comentar.</p>
                                @endauth
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 text-gray-500">
                            <p class="italic mb-2">Aún no hay reseñas.</p>
                            <p class="text-xs">¡Sé el primero en compartir tu opinión!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>