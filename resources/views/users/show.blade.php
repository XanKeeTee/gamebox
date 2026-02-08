<x-app-layout>
    <div x-data="{ 
        modalOpen: false, currentSlot: null, searchQuery: '', searchResults: [], isLoading: false,
        openModal(slot) { this.currentSlot = slot; this.modalOpen = true; this.searchQuery = ''; this.searchResults = []; setTimeout(() => document.getElementById('modal-search').focus(), 100); },
        search() { if(this.searchQuery.length < 2) return; this.isLoading = true; fetch(`{{ route('games.searchJson') }}?q=${this.searchQuery}`).then(r=>{if(!r.ok)throw new Error();return r.json()}).then(d=>{this.searchResults=d;this.isLoading=false;}).catch(e=>{console.error(e);this.isLoading=false;}); }
    }" class="min-h-screen bg-[#14181c] text-gray-100 font-sans">
        
        <div class="bg-[#20242c] pt-12 pb-6 border-b border-gray-800">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center md:items-end gap-6">
                <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-[#14181c] shadow-2xl shrink-0 relative group">
                    @if($user->avatar) <img src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full object-cover">
                    @else <div class="w-full h-full bg-gray-700 flex items-center justify-center text-4xl font-bold text-white">{{ substr($user->name, 0, 1) }}</div> @endif
                </div>

                <div class="flex-1 text-center md:text-left mb-2">
                    <h1 class="text-4xl font-black text-white tracking-tight">{{ $user->name }}</h1>
                    
                    <div class="flex items-center justify-center md:justify-start gap-4 mt-2 text-sm">
                        <a href="{{ route('users.show', ['name' => $user->name, 'tab' => 'following']) }}" class="flex gap-1 hover:text-green-400 transition group">
                            <span class="font-bold text-white group-hover:text-green-400">{{ $stats['following'] }}</span>
                            <span class="text-gray-500 group-hover:text-green-400/80">Siguiendo</span>
                        </a>
                        <a href="{{ route('users.show', ['name' => $user->name, 'tab' => 'followers']) }}" class="flex gap-1 hover:text-green-400 transition group">
                            <span class="font-bold text-white group-hover:text-green-400">{{ $stats['followers'] }}</span>
                            <span class="text-gray-500 group-hover:text-green-400/80">Seguidores</span>
                        </a>
                    </div>
                    
                    <p class="text-gray-600 text-xs mt-2">Miembro desde {{ $user->created_at->format('Y') }}</p>
                </div>

                <div class="mb-4 md:mb-2">
                    @auth
                        @if(Auth::id() === $user->id)
                            <a href="{{ route('profile.edit') }}" class="bg-[#343843] hover:bg-gray-600 text-white px-4 py-2 rounded font-bold text-sm transition shadow-lg">Editar Perfil</a>
                        @else
                            <form action="{{ route('users.follow', $user->id) }}" method="POST">
                                @csrf
                                @if(Auth::user()->isFollowing($user))
                                    <button class="bg-gray-600 hover:bg-red-600 text-white px-6 py-2 rounded font-bold text-sm transition group shadow-lg"><span class="group-hover:hidden">Siguiendo</span><span class="hidden group-hover:inline">Dejar de Seguir</span></button>
                                @else
                                    <button class="bg-green-600 hover:bg-green-500 text-white px-6 py-2 rounded font-bold text-sm transition shadow-lg">Seguir</button>
                                @endif
                            </form>
                        @endif
                    @endauth
                </div>
            </div>
        </div>

        <div class="bg-[#1b1e24] border-b border-gray-800 sticky top-16 z-30 shadow-md">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex space-x-8 overflow-x-auto">
                <a href="{{ route('users.show', ['name' => $user->name]) }}" class="py-4 font-bold transition {{ $tab == 'profile' ? 'text-white border-b-2 border-green-500' : 'text-gray-400 hover:text-white' }}">Perfil</a>
                <a href="{{ route('users.show', ['name' => $user->name, 'tab' => 'games']) }}" class="py-4 font-bold transition {{ $tab == 'games' ? 'text-white border-b-2 border-green-500' : 'text-gray-400 hover:text-white' }}">Juegos</a>
                <a href="{{ route('users.show', ['name' => $user->name, 'tab' => 'lists']) }}" class="py-4 font-bold transition {{ $tab == 'lists' ? 'text-white border-b-2 border-green-500' : 'text-gray-400 hover:text-white' }}">Listas</a>
                <a href="{{ route('users.show', ['name' => $user->name, 'tab' => 'reviews']) }}" class="py-4 font-bold transition {{ $tab == 'reviews' ? 'text-white border-b-2 border-green-500' : 'text-gray-400 hover:text-white' }}">Reviews</a>
                @if($tab == 'followers' || $tab == 'following')
                    <a href="#" class="py-4 font-bold text-white border-b-2 border-green-500 capitalize">{{ $tab == 'followers' ? 'Seguidores' : 'Siguiendo' }}</a>
                @endif
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            @if($tab == 'profile')
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    <div class="lg:col-span-1 space-y-8"><div><h3 class="text-gray-400 font-bold uppercase text-xs tracking-widest mb-3 border-b border-gray-800 pb-1">Bio</h3><p class="text-gray-300 text-sm whitespace-pre-line">{{ $user->bio ?? 'Sin biografía.' }}</p></div></div>
                    
                    <div class="lg:col-span-3 space-y-10">
                        <section>
                            <div class="flex items-center gap-2 mb-4"><span class="text-yellow-500 text-xl">♛</span><h2 class="text-2xl font-bold text-white">Juegos Favoritos</h2></div>
                            <div class="grid grid-cols-5 gap-4">
                                @for ($i = 1; $i <= 5; $i++) 
                                    @php $gameInSlot = $favorites->firstWhere('pivot.favorite_slot', $i); $isOwner = Auth::id() === $user->id; @endphp 
                                    <div class="aspect-[2/3] rounded-lg overflow-hidden relative group transition-all duration-300 {{ !$gameInSlot ? 'bg-[#1b1e24] border-2 border-dashed border-gray-700 hover:border-green-500' : 'shadow-lg hover:ring-2 hover:ring-green-500' }} {{ $isOwner ? 'cursor-pointer' : '' }}" 
                                         @if($isOwner) @click="openModal({{ $i }})" @endif> 
                                        @if($gameInSlot) 
                                            <a href="{{ route('games.show', $gameInSlot->slug) }}" class="block w-full h-full"><img src="{{ $gameInSlot->cover_url }}" class="w-full h-full object-cover"></a> 
                                            @if($isOwner) <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition"><span class="text-white font-bold text-xs bg-black/50 px-2 py-1 rounded border border-white/20">Cambiar</span></div> @endif 
                                        @else 
                                            <div class="w-full h-full flex flex-col items-center justify-center text-gray-600 group-hover:text-green-500 transition"><span class="text-4xl font-light mb-1">+</span><span class="text-[10px] font-bold uppercase tracking-wider">Añadir</span></div> 
                                        @endif 
                                    </div> 
                                @endfor
                            </div>
                        </section>
                        
                        <section class="border-t border-gray-800 pt-8 grid grid-cols-3 gap-4 text-center"><div><span class="block text-4xl font-black text-white">{{ $stats['total_reviews'] }}</span><span class="text-xs text-gray-500 uppercase font-bold">Reviews</span></div><div class="border-l border-gray-800"><span class="block text-4xl font-black text-white">{{ $stats['total_liked'] }}</span><span class="text-xs text-gray-500 uppercase font-bold">Likes</span></div><div class="border-l border-gray-800"><span class="block text-4xl font-black text-white">{{ $stats['total_wish'] }}</span><span class="text-xs text-gray-500 uppercase font-bold">Backlog</span></div></section>
                    </div>
                </div>

                @if(Auth::id() === $user->id)
                <div x-show="modalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-start justify-center pt-20 bg-black/80 backdrop-blur-sm p-4" x-transition.opacity>
                    <div @click.away="modalOpen = false" class="bg-[#24282f] w-full max-w-xl rounded-xl shadow-2xl border border-gray-700 overflow-hidden flex flex-col max-h-[80vh]">
                        <div class="p-4 border-b border-gray-700 flex justify-between items-center bg-[#1b1e24]">
                            <h3 class="text-white font-bold">Elige tu Top #<span x-text="currentSlot"></span></h3>
                            <button @click="modalOpen = false" class="text-gray-400 hover:text-white px-2">✕</button>
                        </div>
                        <div class="p-4 bg-[#24282f]">
                            <input type="text" id="modal-search" x-model="searchQuery" @input.debounce.300ms="search()" placeholder="Busca un juego..." class="w-full bg-[#14181c] text-white border border-gray-600 rounded-lg p-3 focus:ring-green-500 focus:border-green-500 outline-none placeholder-gray-500">
                        </div>
                        <div class="overflow-y-auto p-2 flex-1 space-y-1">
                            <template x-if="isLoading"><div class="text-center p-4 text-gray-500 animate-pulse">Buscando...</div></template>
                            
                            <template x-for="game in searchResults" :key="game.id">
                                <form action="{{ route('profile.setFavorite') }}" method="POST">
                                    @csrf 
                                    <input type="hidden" name="slot" :value="currentSlot">
                                    
                                    <input type="hidden" name="slug" :value="game.slug">
                                    
                                    <button type="submit" class="w-full flex items-center gap-3 p-2 hover:bg-[#343843] rounded-lg transition text-left group border border-transparent hover:border-gray-600">
                                        <div class="w-12 h-16 bg-gray-800 rounded overflow-hidden shrink-0">
                                            <img :src="game.cover_url" class="w-full h-full object-cover">
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-white font-bold text-sm group-hover:text-green-400" x-text="game.name"></div>
                                            <div class="text-gray-500 text-xs" x-text="new Date(game.first_release_date).getFullYear()"></div>
                                        </div>
                                        <div class="text-green-500 text-sm font-bold opacity-0 group-hover:opacity-100 transition mr-2">Seleccionar</div>
                                    </button>
                                </form>
                            </template>
                        </div>
                    </div>
                </div>
                @endif
            @endif

            @if($tab == 'games')
                @if(count($games_list) > 0)
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4">
                        @foreach($games_list as $game)
                            <div class="group relative aspect-[2/3]"><a href="{{ route('games.show', $game->slug) }}" class="block w-full h-full"><div class="w-full h-full rounded-md overflow-hidden bg-[#24282f] shadow-lg transition-transform duration-200 group-hover:scale-105 group-hover:ring-2 {{ $game->pivot->liked ? 'group-hover:ring-orange-500' : 'group-hover:ring-blue-500' }} relative"><img src="{{ $game->cover_url }}" class="w-full h-full object-cover"><div class="absolute inset-0 bg-black/80 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center p-2"><p class="text-white text-center text-xs font-bold">{{ $game->name }}</p></div><div class="absolute top-2 right-2 flex flex-col gap-1">@if($game->pivot->liked) <span class="bg-orange-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow">♥</span> @endif @if($game->pivot->wishlisted) <span class="bg-blue-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow">+</span> @endif</div></div></a></div>
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $games_list->appends(['tab' => 'games'])->links() }}</div>
                @else <div class="text-center py-20 text-gray-500"><div class="text-4xl mb-2">🎮</div><p>Está vacío.</p></div> @endif
            @endif

            @if($tab == 'lists')
                @if($lists->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">@foreach($lists as $list) <a href="{{ route('lists.show', $list->id) }}" class="group block"><div class="bg-[#20242c] rounded-xl overflow-hidden border border-gray-800 shadow-lg hover:border-gray-600 hover:ring-1 hover:ring-gray-600 transition-all"><div class="h-32 bg-[#14181c] grid grid-cols-4 gap-0.5 p-0.5">@foreach($list->games->take(4) as $gamePreview) <div class="h-full bg-gray-800 relative overflow-hidden"><img src="{{ $gamePreview->cover_url }}" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition"></div> @endforeach @for($i = $list->games->count(); $i < 4; $i++) <div class="h-full bg-[#1b1e24] flex items-center justify-center"><span class="text-gray-700 text-xs">•</span></div> @endfor</div><div class="p-4"><h3 class="font-bold text-white text-lg truncate group-hover:text-green-400 transition">{{ $list->title }}</h3><div class="flex justify-between items-center mt-2 text-xs text-gray-500"><span>{{ $list->games->count() }} juegos</span><span>Actualizada {{ $list->updated_at->diffForHumans() }}</span></div>@if($list->description) <p class="text-gray-400 text-xs mt-3 line-clamp-2">{{ $list->description }}</p> @endif</div></div></a> @endforeach</div>
                    <div class="mt-8">{{ $lists->appends(['tab' => 'lists'])->links() }}</div>
                @else <div class="text-center py-20 text-gray-500"><div class="text-4xl mb-2">📋</div><p>No ha creado ninguna lista todavía.</p></div> @endif
            @endif

            @if($tab == 'reviews')
                <div class="space-y-4 max-w-4xl mx-auto">@forelse($reviews_list as $review) <div class="bg-[#20242c] p-4 rounded border border-gray-800 flex gap-4 transition hover:border-gray-600"><a href="{{ route('games.show', $review->game->slug) }}" class="shrink-0 w-20 rounded overflow-hidden shadow-lg"><img src="{{ $review->game->cover_url }}" class="w-full h-auto"></a><div class="flex-1"><div class="flex items-baseline justify-between mb-1"><a href="{{ route('games.show', $review->game->slug) }}" class="font-bold text-white hover:text-green-400 text-lg">{{ $review->game->name }}</a><span class="text-gray-500 text-xs">{{ $review->created_at->format('d M Y') }}</span></div><div class="text-green-500 text-sm mb-2">@for($i=0; $i<$review->rating; $i++) ★ @endfor</div><p class="text-gray-300 text-sm leading-relaxed">"{{ $review->content }}"</p></div></div> @empty <div class="text-center py-12 text-gray-500 italic">No hay reviews.</div> @endforelse</div>
                <div class="mt-8">{{ $reviews_list->appends(['tab' => 'reviews'])->links() }}</div>
            @endif

            @if($tab == 'followers' || $tab == 'following')
                <h3 class="text-xl font-bold text-white mb-6 border-b border-gray-800 pb-2 capitalize">{{ $tab == 'followers' ? 'Seguidores' : 'Siguiendo' }}</h3>
                @if($users_list->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">@foreach($users_list as $followUser) <a href="{{ route('users.show', $followUser->name) }}" class="block group"><div class="bg-[#20242c] p-4 rounded-lg border border-gray-800 hover:border-green-500 transition flex items-center gap-4"><div class="w-14 h-14 rounded-full overflow-hidden border-2 border-gray-700 group-hover:border-green-500 shrink-0">@if($followUser->avatar) <img src="{{ asset('storage/' . $followUser->avatar) }}" class="w-full h-full object-cover"> @else <div class="w-full h-full bg-gray-700 flex items-center justify-center font-bold text-white">{{ substr($followUser->name, 0, 1) }}</div> @endif</div><div class="overflow-hidden"><h4 class="font-bold text-white truncate group-hover:text-green-400">{{ $followUser->name }}</h4><p class="text-xs text-gray-500">Miembro desde {{ $followUser->created_at->format('Y') }}</p></div></div></a> @endforeach</div>
                    <div class="mt-8">{{ $users_list->appends(['tab' => $tab])->links() }}</div>
                @else <div class="text-center py-20 text-gray-500"><div class="text-4xl mb-2">👥</div><p>Lista vacía.</p></div> @endif
            @endif

        </div>
    </div>
</x-app-layout>