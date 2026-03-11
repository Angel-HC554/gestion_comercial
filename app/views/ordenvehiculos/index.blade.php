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
            <div class="flex gap-2 items-center justify-end relative">
                <template x-data="{
                    lista_citas: @js($citas),
                    activeCita: null, // Controla qué tarjeta está abierta
                    formatearFecha(fechaStr) {
                        if (!fechaStr) return 'Sin fecha';
                        const fechaLocal = new Date(fechaStr.replace(/-/g, '/'));
                        return fechaLocal.toLocaleDateString('es-MX', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        }).toUpperCase().replace(/\./g, '');
                    }
                }" x-if="lista_citas">
                    <template x-for="cita in lista_citas" :key="cita.id">
                        <div class="relative" @click.away="if(activeCita === cita.id) activeCita = null">

                            <button @click="activeCita = activeCita === cita.id ? null : cita.id"
                                class="relative flex items-center justify-center w-10 h-10 bg-blue-50 border border-blue-100 rounded-xl text-blue-600 hover:bg-blue-500 hover:text-white transition-colors shadow-sm focus:outline-none cursor-pointer group">

                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>

                                <span class="absolute -top-1 -right-1 flex h-3 w-3">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span
                                        class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500 border-2 border-white"></span>
                                </span>
                            </button>

                            <div x-show="activeCita === cita.id" x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                                class="absolute right-0 top-full mt-2 w-[220px] z-50 bg-white border border-zinc-200 rounded-2xl p-4 shadow-xl cursor-default"
                                @click.stop>
                                <div class="flex flex-col min-w-0">
                                    <div class="flex items-center justify-between mb-3 border-b border-zinc-100 pb-2">
                                        <span class="text-sm font-bold text-zinc-900 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Cita confirmada
                                        </span>
                                        <button @click="activeCita = null"
                                            class="text-zinc-400 hover:text-red-500 transition-colors cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="flex items-center text-[13px] text-zinc-600 mb-2">
                                        <span class="font-bold text-emerald-600 mr-1" x-text="cita.texto_fecha"></span>
                                        <span class="font-bold text-zinc-800"
                                            x-text="formatearFecha(cita.detalle_arrendado?.fecha_cita)"></span>
                                    </div>

                                    <div class="flex items-center text-[13px] text-zinc-500">
                                        <span>Vehículo:</span>
                                        <span
                                            class="bg-zinc-100 text-zinc-800 px-2 py-0.5 rounded-md font-bold text-[12px] ml-2"
                                            x-text="cita?.noeconomico"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </template>
            </div>
        </div>

        @include('ordenvehiculos.tabla')
    </div>
@endsection
