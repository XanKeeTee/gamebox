<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-white mb-6 border-b border-gray-700 pb-4">🔔 Notificaciones</h1>

        <div class="space-y-2">
            @forelse($notifications as $notification)
                @php $data = $notification->data; @endphp
                
                <a href="{{ $data['url'] }}" class="block bg-[#20242c] hover:bg-[#2a2e36] p-4 rounded-lg border border-gray-800 transition flex items-center gap-4 {{ $notification->read_at ? 'opacity-75' : 'border-l-4 border-l-green-500' }}">
                    
                    <div class="shrink-0">
                        @if($data['type'] == 'like')
                            <div class="w-10 h-10 rounded-full bg-green-900/50 text-green-500 flex items-center justify-center">♥</div>
                        @elseif($data['type'] == 'comment')
                            <div class="w-10 h-10 rounded-full bg-blue-900/50 text-blue-500 flex items-center justify-center">💬</div>
                        @else
                            <div class="w-10 h-10 rounded-full bg-purple-900/50 text-purple-500 flex items-center justify-center">👥</div>
                        @endif
                    </div>

                    <div class="shrink-0 w-10 h-10 rounded-full overflow-hidden bg-gray-700">
                        @if(!empty($data['user_avatar']))
                            <img src="{{ asset('storage/' . $data['user_avatar']) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center font-bold text-white text-xs">{{ substr($data['user_name'], 0, 1) }}</div>
                        @endif
                    </div>

                    <div class="flex-1">
                        <p class="text-gray-200 text-sm">{{ $data['message'] }}</p>
                        <p class="text-gray-500 text-xs mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                </a>
            @empty
                <div class="text-center py-20 text-gray-500">
                    <p>No tienes notificaciones nuevas.</p>
                </div>
            @endforelse
        </div>
        
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    </div>
</x-app-layout>