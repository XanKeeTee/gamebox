<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <label for="email" class="block font-bold text-sm text-gray-400">Email</label>
            <input id="email" class="block mt-2 w-full bg-gray-900 border border-gray-700 rounded text-white focus:border-green-500 focus:ring-green-500 p-2.5" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <label for="password" class="block font-bold text-sm text-gray-400">Contraseña</label>
            <input id="password" class="block mt-2 w-full bg-gray-900 border border-gray-700 rounded text-white focus:border-green-500 focus:ring-green-500 p-2.5" type="password" name="password" required />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a class="text-sm text-gray-500 hover:text-white transition" href="{{ route('password.request') }}">
                    ¿Olvidaste contraseña?
                </a>
            @endif

            <button class="ml-3 bg-green-600 hover:bg-green-500 text-white font-bold py-2 px-6 rounded transition">
                Entrar
            </button>
        </div>
    </form>
</x-guest-layout>