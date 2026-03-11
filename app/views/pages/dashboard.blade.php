@extends('layouts.app-layout', [
    'title' => 'Inicio',
])

@section('content')
    @php
        $currentUser = auth()->user();
        $displayName = $currentUser->name ?? 'invitado';
        // ... Lógica de roles que ya tenías ...
    @endphp

    <div class="py-6 px-4 space-y-6">
        <div class="rounded-3xl bg-linear-to-br from-emerald-700 to-emerald-900 shadow-xl overflow-hidden flex justify-between">
            <div class="px-6 py-8">
                <p class="text-sm font-medium text-emerald-200 uppercase tracking-[0.3em]">Bienvenido</p>
                <h1 class="text-3xl font-semibold text-white mt-3">{{ $displayName }}</h1>
                <div class="mt-4 text-emerald-100/80 text-sm">
                    <span class="font-medium">{{ date('d/m/Y') }}</span>
                </div>
            </div>
            @if($areasUsuario->isNotEmpty())
                <div class="px-6 py-8">
                    <p class="text-sm font-medium text-emerald-200 uppercase tracking-[0.3em]">Mis áreas</p>
                    <div class="mt-2 space-y-1">
                        @foreach($areasUsuario as $areaUsuario)
                            <div>
                            <span class="px-3 py-1 bg-emerald-600/20 text-emerald-100 rounded-full text-xs font-medium">
                                {{ $areaUsuario->area->nombre }}
                            </span>
                            <span class="text-emerald-100 text-xs font-medium">
                                -
                            </span>
                            @if($areaUsuario->subarea)
                                <span class="px-3 py-1 bg-emerald-600/20 text-emerald-100 rounded-full text-xs font-medium">
                                    {{ $areaUsuario->subarea->nombre }}
                                </span>
                            @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="bg-white rounded-2xl border border-zinc-200 p-6 shadow-sm">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 bg-emerald-100 rounded-xl text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-zinc-800">Órdenes del mes</h3>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-500">Pendientes</span>
                        <span class="font-bold text-orange-600">{{ $ordenesPendientes }}</span>
                    </div>
                    <div class="flex justify-between text-sm border-t pt-2">
                        <span class="text-zinc-500">En Taller</span>
                        <span class="font-bold text-yellow-600">{{ $ordenesEnTaller }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-zinc-200 p-6 shadow-sm">
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 bg-red-100 rounded-xl text-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-zinc-800">Alertas de Mantenimiento</h3>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <a href="/vehiculos?mantenimiento=amarillo" class="text-zinc-500">Próximos</a>
                        <a href="/vehiculos?mantenimiento=amarillo"
                            class="font-bold text-yellow-600 hover:underline cursor-pointer">
                            {{ $conteosMantenimiento['amarillo'] }}
                        </a>
                    </div>
                    <div class="flex justify-between text-sm border-t pt-2">
                        <a href="/vehiculos?mantenimiento=urgente" class="text-zinc-500">Urgentes/Vencidos</a>
                        <a href="/vehiculos?mantenimiento=urgente"
                            class="font-bold text-red-600 hover:underline cursor-pointer">
                            {{ $conteosMantenimiento['rojo'] + $conteosMantenimiento['rojo_pasado'] }}
                        </a>
                    </div>
                    <div class="flex justify-between text-sm border-t pt-2 italic">
                        <span class="text-zinc-500">Total Vehículos</span>
                        <span>{{ $totalVehiculos }}</span>
                    </div>
                    <a href="/vehiculos"
                        class="block text-center text-xs text-emerald-700 font-semibold hover:underline pt-2">
                        Ver todos los vehículos →
                    </a>
                </div>
            </div>

        </div>
    </div>
@endsection
