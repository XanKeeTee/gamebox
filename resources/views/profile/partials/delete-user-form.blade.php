<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-red-500">
            {{ __('Borrar Cuenta') }}
        </h2>
        <p class="mt-1 text-sm text-gray-400">
            {{ __('Una vez borrada tu cuenta, todos sus recursos y datos se eliminarán permanentemente.') }}
        </p>
    </header>

    <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" class="bg-red-600 hover:bg-red-500 text-white font-bold py-2 px-4 rounded transition">
        {{ __('Borrar Cuenta') }}
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 bg-[#24282f] border border-gray-700">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-white">
                {{ __('¿Estás seguro de que quieres borrar tu cuenta?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-400">
                {{ __('Una vez borrada, no hay vuelta atrás. Por favor, introduce tu contraseña para confirmar.') }}
            </p>

            <div class="mt-6">
                <label for="password" class="sr-only">Contraseña</label>
                <input id="password" name="password" type="password" class="mt-1 block w-3/4 bg-gray-900 border-gray-700 rounded-md text-white placeholder-gray-500 focus:border-red-500 focus:ring-red-500" placeholder="{{ __('Contraseña') }}" />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" x-on:click="$dispatch('close')" class="text-gray-300 hover:text-white mr-4">
                    {{ __('Cancelar') }}
                </button>

                <button type="submit" class="bg-red-600 hover:bg-red-500 text-white font-bold py-2 px-4 rounded transition">
                    {{ __('Borrar Cuenta') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
