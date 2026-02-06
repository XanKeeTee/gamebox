<x-app-layout>
    <div class="py-12 bg-backloggd min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <h2 class="font-bold text-2xl text-white mb-8 px-4 sm:px-0">
                Configuración del Perfil
            </h2>

            <div class="p-4 sm:p-8 bg-[#24282f] ...">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="p-4 sm:p-8 bg-[#24282f] ...">
                @include('profile.partials.update-password-form')
            </div>

            <div class="p-4 sm:p-8 bg-[#24282f] shadow-lg sm:rounded-lg border border-gray-800">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
