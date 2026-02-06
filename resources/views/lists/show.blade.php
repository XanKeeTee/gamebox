<x-app-layout>
    <div class="bg-[#20242c] border-b border-gray-800 pt-10 pb-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start gap-6">

                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span
                            class="bg-gray-700 text-gray-300 text-xs font-bold px-2 py-1 rounded uppercase tracking-wider">Lista</span>
                        @if ($list->is_public)
                            <span
                                class="text-green-500 text-xs font-bold border border-green-500/30 px-2 py-1 rounded">Pública</span>
                        @else
                            <span
                                class="text-yellow-500 text-xs font-bold border border-yellow-500/30 px-2 py-1 rounded">Privada</span>
                        @endif
                    </div>

                    <h1 class="text-4xl md:text-5xl font-black text-white mb-4 leading-tight">{{ $list->title }}</h1>

                    @if ($list->description)
                        <p class="text-gray-400 text-lg max-w-2xl mb-6 italic">"{{ $list->description }}"</p>
                    @endif

                    <div class="flex items-center gap-3 mt-4">
                        <a href="{{ route('users.show', $list->user->name) }}" class="block shrink-0">
                            <div
                                class="w-10 h-10 rounded-full overflow-hidden border-2 border-transparent hover:border-green-500 transition">
                                @if ($list->user->avatar)
                                    <img src="{{ asset('storage/' . $list->user->avatar) }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <div
                                        class="w-full h-full bg-gray-600 flex items-center justify-center font-bold text-white">
                                        {{ substr($list->user->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                        </a>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">Creada por</p>
                            <a href="{{ route('users.show', $list->user->name) }}"
                                class="text-white font-bold hover:text-green-400 transition">
                                {{ $list->user->name }}
                            </a>
                        </div>
                        <div class="text-gray-600 text-xs border-l border-gray-700 pl-3 ml-1">
                            {{ $games->total() }} Juegos
                        </div>
                    </div>
                </div>

                @if (Auth::id() === $list->user_id)
                    <div class="flex flex-col gap-2">
                        <form action="{{ route('lists.destroy', $list->id) }}" method="POST"
                            onsubmit="return confirm('¿Seguro que quieres borrar esta lista? No se puede deshacer.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="bg-red-900/50 hover:bg-red-600 text-red-200 hover:text-white px-4 py-2 rounded text-sm font-bold transition flex items-center gap-2 border border-red-800">
                                🗑️ Borrar Lista
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if ($games->count() > 0)
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4">
                @foreach ($games as $game)
                    <div
                        class="group relative aspect-[2/3] rounded-lg overflow-hidden bg-[#20242c] shadow-md hover:shadow-xl hover:ring-2 hover:ring-green-500 transition-all duration-300">

                        <a href="{{ route('games.show', $game->slug) }}" class="block w-full h-full">
                            @if ($game->cover_url)
                                <img src="{{ $game->cover_url }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div
                                    class="w-full h-full flex items-center justify-center text-gray-600 bg-gray-800 text-xs text-center p-2">
                                    {{ $game->name }}
                                </div>
                            @endif

                            <div
                                class="absolute inset-0 bg-black/80 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center p-2">
                                <p class="text-white text-center text-xs font-bold">{{ $game->name }}</p>
                            </div>
                        </a>

                        @if (Auth::id() === $list->user_id)
                            <form action="{{ route('lists.removeGame', [$list->id, $game->id]) }}" method="POST"
                                class="absolute top-1 right-1 z-20">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="bg-black/60 hover:bg-red-600 text-white w-6 h-6 rounded flex items-center justify-center transition backdrop-blur-sm"
                                    title="Quitar de la lista">
                                    ✕
                                </button>
                            </form>
                        @endif

                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $games->links() }}
            </div>
        @else
            <div class="text-center py-24 text-gray-500">
                <div class="text-5xl mb-4">📭</div>
                <h3 class="text-xl font-bold text-gray-400">Esta lista está vacía</h3>
                <p class="mt-2 text-sm">Añade juegos desde el catálogo para empezar.</p>
                <a href="{{ route('games.index') }}"
                    class="inline-block mt-6 bg-green-600 hover:bg-green-500 text-white font-bold px-6 py-2 rounded transition">
                    Explorar Juegos
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
