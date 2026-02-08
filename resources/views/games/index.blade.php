<x-app-layout>
    <div class="min-h-screen bg-[#14181c] text-gray-100 font-sans">
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                <h1 class="text-3xl font-black text-white tracking-tighter">Explorar Juegos</h1>
                
                <form action="{{ route('games.index') }}" method="GET" class="w-full md:w-96 relative">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar título..." 
                           class="w-full bg-[#20242c] border border-gray-700 text-white rounded-full py-2 pl-4 pr-10 focus:ring-green-500 focus:border-green-500 outline-none">
                    <button class="absolute right-3 top-2.5 text-gray-500 hover:text-white">🔍</button>
                    
                    @if(request('genre')) <input type="hidden" name="genre" value="{{ request('genre') }}"> @endif
                    @if(request('platform')) <input type="hidden" name="platform" value="{{ request('platform') }}"> @endif
                </form>
            </div>

            <div class="bg-[#20242c] p-4 rounded-lg border border-gray-800 mb-6 flex flex-wrap gap-4 items-center">
                <span class="text-xs font-bold text-gray-500 uppercase">Filtrar por:</span>
                
                <form id="filterForm" action="{{ route('games.index') }}" method="GET" class="flex flex-wrap gap-2">
                    @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif

                    <select name="genre" onchange="this.form.submit()" class="bg-[#14181c] border border-gray-600 text-sm text-gray-300 rounded px-3 py-1.5 focus:border-green-500 outline-none">
                        <option value="">Todos los Géneros</option>
                        @foreach($genres as $g)
                            <option value="{{ $g['slug'] }}" {{ request('genre') == $g['slug'] ? 'selected' : '' }}>{{ $g['name'] }}</option>
                        @endforeach
                    </select>

                    <select name="platform" onchange="this.form.submit()" class="bg-[#14181c] border border-gray-600 text-sm text-gray-300 rounded px-3 py-1.5 focus:border-green-500 outline-none">
                        <option value="">Todas las Plataformas</option>
                        @foreach($platforms as $p)
                            <option value="{{ $p['slug'] }}" {{ request('platform') == $p['slug'] ? 'selected' : '' }}>{{ $p['name'] }}</option>
                        @endforeach
                    </select>
                </form>

                <div class="border-l border-gray-700 h-6 mx-2 hidden sm:block"></div>

                <div class="flex gap-1">
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'popular']) }}" 
                       class="px-3 py-1 rounded text-xs font-bold {{ !request('sort') || request('sort') == 'popular' ? 'bg-green-600 text-white' : 'bg-[#14181c] text-gray-400 hover:text-white' }}">
                        Populares
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" 
                       class="px-3 py-1 rounded text-xs font-bold {{ request('sort') == 'newest' ? 'bg-green-600 text-white' : 'bg-[#14181c] text-gray-400 hover:text-white' }}">
                        Nuevos
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($games as $game)
                    <x-game-card :game="$game" />
                @endforeach
            </div>

            <div class="mt-8">
                {{ $games->links() }}
            </div>
        </div>
    </div>
</x-app-layout>