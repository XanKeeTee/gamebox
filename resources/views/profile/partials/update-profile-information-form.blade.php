<section>
    <header>
        <h2 class="text-lg font-medium text-white">
            {{ __('Información del Perfil') }}
        </h2>
        <p class="mt-1 text-sm text-gray-400">
            {{ __('Actualiza tu foto, nombre y correo electrónico.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <label class="block font-medium text-sm text-gray-300 mb-2">Foto de Avatar</label>
            <div class="flex items-center gap-6">
                <div class="shrink-0">
                    <div
                        class="h-20 w-20 rounded-full overflow-hidden border-2 border-gray-600 bg-gray-700 flex items-center justify-center">
                        @if (Auth::user()->avatar)
                            <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="h-full w-full object-cover"
                                alt="Avatar">
                        @else
                            <span class="text-2xl font-bold text-gray-400">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        @endif
                    </div>
                </div>

                <label class="block">
                    <span class="sr-only">Elige una foto</span>
                    <input type="file" name="avatar"
                        class="block w-full text-sm text-gray-400
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-xs file:font-semibold
                        file:bg-green-600 file:text-white
                        hover:file:bg-green-500
                        cursor-pointer file:cursor-pointer
                    " />
                </label>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
        </div>

        <div>
            <label for="name" class="block font-medium text-sm text-gray-300">Nombre</label>
            <input id="name" name="name" type="text"
                class="mt-1 block w-full bg-gray-900 border-gray-700 rounded-md text-white focus:border-green-500 focus:ring-green-500"
                value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>
        <div class="mt-4">
            <label for="bio" class="block font-medium text-sm text-gray-300">Biografía / Sobre mí</label>
            <textarea id="bio" name="bio" rows="4"
                class="mt-1 block w-full bg-gray-900 border-gray-700 rounded-md text-white focus:border-green-500 focus:ring-green-500 resize-none"
                placeholder="Cuéntanos qué juegos te gustan...">{{ old('bio', $user->bio) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>
        <div>
            <label for="email" class="block font-medium text-sm text-gray-300">Correo Electrónico</label>
            <input id="email" name="email" type="email"
                class="mt-1 block w-full bg-gray-900 border-gray-700 rounded-md text-white focus:border-green-500 focus:ring-green-500"
                value="{{ old('email', $user->email) }}" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-gray-200">
                        {{ __('Tu correo no está verificado.') }}
                        <button form="send-verification" class="underline text-sm text-gray-400 hover:text-gray-100">
                            {{ __('Reenviar verificación.') }}
                        </button>
                    </p>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="submit"
                class="bg-green-600 hover:bg-green-500 text-white font-bold py-2 px-4 rounded transition">
                {{ __('Guardar Perfil') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-400">
                    {{ __('¡Guardado con éxito!') }}
                </p>
            @endif
        </div>
    </form>
</section>
