@extends('layouts.app-layout', [
    'title' => 'Listado de Órdenes'
])

@section('content')

    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="ordenesTable()" x-init="fetchData()" x-cloak>
        @csrf
        <div class="flex justify-start items-center gap-12 mb-6">
            <h1 class="text-2xl tracking-tight font-bold text-zinc-900">Órdenes de servicio y reparación</h1>
            <a href="/ordenvehiculos/create"
                class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Crear orden
            </a>
        </div>

        @include('ordenvehiculos.tabla')
    </div>
    @endsection
