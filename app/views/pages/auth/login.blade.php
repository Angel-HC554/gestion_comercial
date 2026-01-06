@extends('layouts.auth')

@section('content')
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans">



        <div class="sm:mx-auto sm:w-full sm:max-w-[480px]">
            <div class="bg-white py-10 px-10 shadow-lg rounded-lg border border-gray-100">
                <div class="sm:mx-auto sm:w-full sm:max-w-md mb-6">
                    <div class="flex justify-center">
                        <img src="/assets/img/logo_cfe.png" alt="CFE" class="h-24 w-auto">
                    </div>
                    <div class="border-b border-gray-200 pb-6 mb-6"></div>

                </div>

                <div class="text-center mb-8">
                    <h2 class="text-2xl font-semibold text-gray-900">
                        Inicia sesión
                    </h2>
                    <p class="mt-2 text-sm text-gray-500">
                        Ingresa tu usuario y contraseña para acceder
                    </p>
                </div>

                <form class="space-y-6" action="/auth/login" method="POST">
                    @csrf

                    <div>
                        <label for="user" class="block text-sm font-medium text-gray-700 mb-1">
                            Usuario
                        </label>
                        <div class="mt-1">
                            <input id="user" name="user" type="text" required value="{{ $user ?? '' }}"
                                placeholder="Usuario"
                                class="appearance-none block w-full px-3 py-3 border-2 border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors">
                            <small class="text-red-600 text-xs">{{ $errors['user'] ?? ($errors['auth'] ?? null) }}</small>
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Contraseña
                        </label>
                        <div class="mt-1 relative">
                            <input id="password" name="password" type="password" required placeholder="Contraseña"
                                class="appearance-none block w-full px-3 py-3 pr-10 border-2 border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors">

                            <button type="button"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-500 focus:outline-none cursor-pointer"
                                onclick="togglePasswordVisibility()">
                                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg id="eyeSlashIcon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                            <small class="text-red-600 text-xs">{{ $errors['password'] ?? null }}</small>
                        </div>
                    </div>

                    <!--<div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" name="remember-me" type="checkbox"
                                class="h-4 w-4 text-emerald-600 focus:ring-emerald-600 border-gray-300 rounded">
                            <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                                Recuérdame
                            </label>
                        </div>
                    </div> -->

                    <div>
                        @component('components.button', ['type' => 'submit', 'class' => 'w-full'])
                            Iniciar sesión
                        @endcomponent
                    </div>
                </form>

            </div>
        </div>
    </div>
@push('scripts')
<script>
    // Hacer la función global
    window.togglePasswordVisibility = function() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeSlashIcon = document.getElementById('eyeSlashIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.add('hidden');
            eyeSlashIcon.classList.remove('hidden');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('hidden');
            eyeSlashIcon.classList.add('hidden');
        }
    }

    // Asegurar que el DOM esté completamente cargado
    document.addEventListener('DOMContentLoaded', function() {
        // Agregar el event listener al botón
        const toggleButton = document.querySelector('button[onclick="togglePasswordVisibility()"]');
        if (toggleButton) {
            toggleButton.onclick = function() {
                window.togglePasswordVisibility();
            };
        }
    });
</script>
@endpush

@endsection
