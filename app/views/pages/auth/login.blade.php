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
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Usuario
                        </label>
                        <div class="mt-1">
                            <input id="email" name="email" type="email" required value="{{ $email ?? '' }}"
                                placeholder="Usuario"
                                class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors">
                            <small class="text-red-600 text-xs">{{ $errors['email'] ?? ($errors['auth'] ?? null) }}</small>
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Contraseña
                        </label>
                        <div class="mt-1 relative">
                            <input id="password" name="password" type="password" required placeholder="Contraseña"
                                class="appearance-none block w-full px-3 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors">

                            <div
                                class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <small class="text-red-600 text-xs">{{ $errors['password'] ?? null }}</small>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember-me" name="remember-me" type="checkbox"
                                class="h-4 w-4 text-emerald-600 focus:ring-emerald-600 border-gray-300 rounded">
                            <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                                Recuérdame
                            </label>
                        </div>
                    </div>

                    <div>
                        @component('components.button', ['type' => 'submit', 'class' => 'w-full'])
                            Iniciar sesión
                        @endcomponent
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
