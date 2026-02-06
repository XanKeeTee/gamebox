<section>
    <header>
        <h2 class="text-lg font-medium text-white">
            {{ __('Actualizar Contraseña') }}
        </h2>
        <p class="mt-1 text-sm text-gray-400">
            {{ __('Usa una contraseña segura para proteger tu cuenta.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="current_password" class="block font-medium text-sm text-gray-300">Contraseña Actual</label>
            <input id="current_password" name="current_password" type="password" class="mt-1 block w-full bg-gray-900 border-gray-700 rounded-md text-white focus:border-green-500 focus:ring-green-500" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="password" class="block font-medium text-sm text-gray-300">Nueva Contraseña</label>
            <input id="password" name="password" type="password" class="mt-1 block w-full bg-gray-900 border-gray-700 rounded-md text-white focus:border-green-500 focus:ring-green-500" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="password_confirmation" class="block font-medium text-sm text-gray-300">Confirmar Contraseña</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full bg-gray-900 border-gray-700 rounded-md text-white focus:border-green-500 focus:ring-green-500" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="bg-green-600 hover:bg-green-500 text-white font-bold py-2 px-4 rounded transition">
                {{ __('Cambiar Contraseña') }}
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-green-400">
                    {{ __('Guardado.') }}
                </p>
            @endif
        </div>
    </form>
</section>