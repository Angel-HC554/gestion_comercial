@extends('layouts.app-layout', [
    'title' => 'Actualizar Perfil'
])

@section('content')
    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-zinc-900">Actualizar Perfil</h1>
            <p class="text-sm text-zinc-600">Actualiza la información de tu cuenta.</p>
        </div>

        <div>
            <form action="/settings/profile" method="post" class="space-y-6 max-w-xl">
                @csrf
                @method('patch')

                <div class="grid">
                    <label>Nombre</label>
                    <input class="bg-[#F5F8F9] py-2 px-3 border-2 border-gray-300 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 rounded-lg" type="text" name="name"
                        placeholder="Nombre completo" value="{{ $name ?? '' }}">
                    <small class="text-red-700 text-sm">{{ $errors['name'] ?? ($errors['auth'] ?? null) }}</small>
                </div>
                <div class="grid">
                    <label>Usuario</label>
                    <input class="bg-[#F5F8F9] py-2 px-3 border-2 border-gray-300 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 rounded-lg" type="text" name="user"
                        placeholder="Nombre de usuario" value="{{ $user ?? '' }}" pattern="[A-Za-z0-9]+">
                    <small class="text-red-700 text-sm">{{ $errors['user'] ?? ($errors['auth'] ?? null) }}</small>
                </div>

                <button
                    class="shadow-sm transition-colors inline-flex justify-center rounded-lg text-sm font-semibold py-3 px-4 hover:bg-emerald-700 bg-emerald-600 text-white cursor-pointer">
                    Actualizar perfil
                </button>
            </form>
        </div>
    </div>
@endsection
