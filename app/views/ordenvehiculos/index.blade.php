@extends('layouts.app-layout', [
    'title' => 'Listado de Órdenes',
])

@section('content')
    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="ordenesTable()" x-init="fetchData()" x-cloak>
        @csrf
        <div class="flex justify-between gap-12">
            <div class="flex">
                <h1 class="text-2xl tracking-tight font-bold text-zinc-900">Órdenes de servicio y reparación</h1>
                <div class="relative ml-4" x-data="{ open: false }" @click.away="open = false">

                    <button @click="open = !open"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors text-sm flex items-center gap-2 focus:outline-none cursor-pointer">
                        Crear orden
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1 transition-transform duration-200"
                            :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute left-full ml-2 top-0 w-48 rounded-md shadow-xl bg-white ring-1 ring-zinc-200 ring-opacity-5 z-50 focus:outline-none origin-top-left"
                        style="display: none;">

                        <div class="py-1">
                            <a href="/ordenvehiculos/create"
                                class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-zinc-50 hover:text-emerald-700 transition-colors">
                                <div>
                                    <span class="block font-medium">Vehículo Propio</span>
                                </div>
                            </a>

                            <div class="border-t border-gray-300"></div>

                            <a href="/ordenvehiculos/create_arrendado"
                                class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-zinc-50 hover:text-emerald-700 transition-colors">
                                <div>
                                    <span class="block font-medium">Vehículo Arrendado</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('ordenvehiculos.tabla')
    </div>
@endsection
