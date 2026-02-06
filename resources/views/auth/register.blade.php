<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <label for="name" class="block font-bold text-sm text-gray-400">Nombre de Usuario</label>
            <input id="name" class="block mt-2 w-full bg-gray-900 border border-gray-700 rounded text-white focus:border-green-500 focus:ring-green-500" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <label for="email" class="block font-bold text-sm text-gray-400">Correo Electrónico</label>
            <input id="email" class="block mt-2 w-full bg-gray-900 border border-gray-700 rounded text-white focus:border-green-500 focus:ring-green-500" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <label for="password" class="block font-bold text-sm text-gray-400">Contraseña</label>
            <input id="password" class="block mt-2 w-full bg-gray-900 border border-gray-700 rounded text-white focus:border-green-500 focus:ring-green-500" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <label for="password_confirmation" class="block font-bold text-sm text-gray-400">Confirmar Contraseña</label>
            <input id="password_confirmation" class="block mt-2 w-full bg-gray-900 border border-gray-700 rounded text-white focus:border-green-500 focus:ring-green-500" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-8">
            <a class="text-sm text-gray-500 hover:text-white transition" href="{{ route('login') }}">
                ¿Ya tienes cuenta?
            </a>

            <button class="ms-4 bg-green-600 hover:bg-green-500 text-white font-bold py-2 px-6 rounded transition shadow-lg shadow-green-900/20">
                Registrarse
            </button>
        </div>
    </form>
</x-guest-layout>