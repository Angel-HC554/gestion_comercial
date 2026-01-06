@extends('layouts.app-layout', [
    'title' => 'Información del vehículo'
])

@section('content')
    <div x-data="{
        tab: 'estado', // Pestaña activa del primer grupo (Estado/Fotos)
        tabHistorial: 'historial' // Pestaña activa del segundo grupo (Historial/Supervisión)
    }" class="min-h-screen pb-10">

        <div class="flex items-center justify-between mb-6 mx-6 md:mx-10 pt-6">
            <h1 class="text-3xl font-bold tracking-tight text-zinc-900 flex items-center gap-2">
                Información del vehículo
            </h1>

            <a href="/vehiculos"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Volver
            </a>
        </div>

        <div class="mx-6 md:mx-10 grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-1">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-md">
                    <div
                        class="flex items-start gap-4 bg-gradient-to-t from-emerald-600 to-emerald-900 text-white rounded-t-lg -m-5 mb-2 p-4 md:p-5">
                        <div class="h-20 w-36 shrink-0 overflow-hidden rounded-md bg-gray-100 ring-1 ring-white/20">
                            <img src="https://media.ed.edmunds-media.com/chevrolet/silverado-1500/2026/oem/2026_chevrolet_silverado-1500_crew-cab-pickup_high-country_fq_oem_2_1600.jpg"
                                alt="vehiculo" class="h-full w-full object-cover">
                        </div>
                        <div>
                            <div class="text-xl font-semibold text-white">
                                {{ $vehiculo->marca ?? 'Marca' }} {{ $vehiculo->modelo ?? '' }}
                            </div>
                            <div class="text-sm text-emerald-100">{{ $vehiculo->tipo_vehiculo ?? 'Tipo' }}</div>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="flex text-sm flex-col">
                            <span class="font-semibold text-gray-700">Año:</span>
                            <span class="text-gray-600">{{ $vehiculo->año ?? '—' }}</span>
                        </div>
                        <div class="flex text-sm flex-col">
                            <span class="font-semibold text-gray-700">Estado:</span>
                            <span class="text-gray-600">{{ $vehiculo->estado ?? '—' }}</span>
                        </div>
                        <div class="flex text-sm flex-col">
                            <span class="font-semibold text-gray-700">Propietario:</span>
                            <span class="text-gray-600">{{ $vehiculo->propiedad ?? '—' }}</span>
                        </div>
                        <div class="flex text-sm flex-col">
                            <span class="font-semibold text-gray-700">Agencia:</span>
                            <span class="text-gray-600">{{ $vehiculo->agencia ?? '—' }}</span>
                        </div>
                        <div class="flex text-sm flex-col">
                            <span class="font-semibold text-gray-700">No. Económico:</span>
                            <span class="text-gray-600">{{ $vehiculo->no_economico ?? '—' }}</span>
                        </div>
                        <div class="flex text-sm flex-col">
                            <span class="font-semibold text-gray-700">Placas:</span>
                            <span class="text-gray-600">{{ $vehiculo->placas ?? '—' }}</span>
                        </div>
                        <div class="flex text-sm flex-col">
                            <span class="font-semibold text-gray-700">Alias:</span>
                            <span class="text-gray-600">{{ $vehiculo->alias ?? '—' }}</span>
                        </div>
                        <div class="flex text-sm flex-col">
                            <span class="font-semibold text-gray-700">Proceso:</span>
                            <span class="text-gray-600">{{ $vehiculo->proceso ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-8">

                <div class="rounded-xl border border-gray-200 bg-white shadow-md">
                    <div class="border-b border-gray-200 px-6 pt-4">
                        <nav class="flex space-x-4">
                            <button @click="tab = 'estado'"
                                :class="tab === 'estado' ? 'bg-emerald-600 text-white' :
                                    'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                                class="px-4 py-2 rounded-t-md text-sm font-semibold transition-colors cursor-pointer">
                                Estado KM
                            </button>
                            <button @click="tab = 'fotos'"
                                :class="tab === 'fotos' ? 'bg-emerald-600 text-white' :
                                    'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                                class="px-4 py-2 rounded-t-md text-sm font-semibold transition-colors cursor-pointer">
                                Fotos
                            </button>
                        </nav>
                    </div>

                    <div class="p-6">

                        <div x-show="tab === 'estado'" x-cloak>
                            @php
                                // 1. Definir estatus y colores de fondo (Badge)
                                $estatus = $vehiculo->estado_mantenimiento;

                                $coloresBadge = [
                                    'verde' => 'bg-green-600',
                                    'amarillo' => 'bg-yellow-500',
                                    'rojo' => 'bg-red-500',
                                    'rojo_pasado' => 'bg-red-700 animate-pulse',
                                    'gris' => 'bg-gray-400',
                                ];
                                $textosBadge = [
                                    'verde' => 'Vehiculo al día',
                                    'amarillo' => 'Mantenimiento próximo',
                                    'rojo' => 'Mantenimiento Urgente',
                                    'rojo_pasado' => 'MANTENIMIENTO VENCIDO',
                                    'gris' => 'Sin datos',
                                ];

                                // 2. RECUPERADO: Colores de texto para "Próximo servicio" (Faltaba esto)
                                $claseTexto = [
                                    'verde' => 'text-green-600',
                                    'amarillo' => 'text-yellow-500',
                                    'rojo' => 'text-red-500',
                                    'rojo_pasado' => 'text-red-700',
                                    'gris' => 'text-gray-400',
                                ];

                                // 3. Cálculos de KM
                                $kmData = $vehiculo->ultimoKilometraje();
                                $kmUltimo = $vehiculo->latestMantenimiento?->kilometraje;

                                // Cálculo de próximo servicio
                                $proximo = $kmUltimo 
                                ? ($kmUltimo + 10000) 
                                : (($kmData['kilometraje'] !== 0 ? ceil($kmData['kilometraje'] / 10000) * 10000 : 0) ?: 10000);

                                $kmFaltantes = $proximo - $kmData['kilometraje'];

                                // 4. Cálculos de FECHA (Nuevo)
                                $fechaUltimo = $vehiculo->latestMantenimiento?->fecha_terminacion;
                                $proximaFecha = null;
                                $diasRestantes = null;
                                $estadoFecha = 'verde'; // Estado individual de la fecha

                                if ($fechaUltimo) {
                                    $fechaCarbon = \Carbon\Carbon::parse($fechaUltimo);
                                    $proximaFecha = $fechaCarbon->copy()->addMonths(3); // 3 Meses
                                    $diasRestantes = \Carbon\Carbon::now()->diffInDays($proximaFecha, false); // false para negativos
                                    
                                    // Determinar color específico de la fecha para pintarlo en la UI
                                    if ($diasRestantes < 0) $estadoFecha = 'rojo_pasado';
                                    elseif ($diasRestantes <= 7) $estadoFecha = 'rojo';
                                    elseif ($diasRestantes <= 21) $estadoFecha = 'amarillo';
                                }

                                // Determinar color específico del KM
                                $estadoKm = 'verde';
                                if ($kmFaltantes < 0) $estadoKm = 'rojo_pasado';
                                elseif ($kmFaltantes <= 1000) $estadoKm = 'rojo';
                                elseif ($kmFaltantes <= 2000) $estadoKm = 'amarillo';
                            @endphp

                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                                <div class="flex-1">
                                    <h2 class="text-sm font-medium text-gray-700 mb-1">Kilometraje actual</h2>
                                    <p class="text-2xl font-bold text-gray-800">
                                        @if ($kmData['kilometraje'] !== 0)
                                            {{ number_format($kmData['kilometraje']) }} <span
                                                class="text-sm font-normal text-gray-600">km</span>
                                            <div class="text-sm text-gray-500 mt-1">
                                                {{-- Muestra Fecha --}}
                                                {{ $kmData['fecha'] ? \Carbon\Carbon::parse($kmData['fecha'])->format('d/m/Y') : '' }}

                                                {{-- RECUPERADO: Muestra Hora si existe --}}
                                                @if ($kmData['hora_fin'])
                                                    {{ \Carbon\Carbon::parse($kmData['hora_fin'])->format('H:i') }}
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-500">Sin registro</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="flex-1">
                                    <h2 class="text-sm font-medium text-gray-400 mb-1">Ultimo servicio</h2>
                                    <p class="text-2xl font-bold text-gray-500">
                                        @if ($kmUltimo)
                                            {{ number_format($kmUltimo) }} <span
                                                class="text-sm font-normal text-gray-600">km</span>

                                            {{-- RECUPERADO: Fecha de terminación del servicio --}}
                                            <div class="text-sm text-gray-500 mt-1">
                                                {{ $vehiculo->latestMantenimiento?->fecha_terminacion ? \Carbon\Carbon::parse($vehiculo->latestMantenimiento->fecha_terminacion)->format('d/m/Y') : 'Sin fecha' }}
                                            </div>
                                        @else
                                            <span class="text-gray-500">Sin registro</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="flex-1 text-right sm:text-left">
                                    <h2 class="text-sm font-medium text-gray-400 mb-2">Próximo servicio</h2>
    
                                <div class="space-y-3">
                                    {{-- BLOQUE 1: KILOMETRAJE --}}
                                <div class="flex items-start justify-end sm:justify-start gap-2">
            <div class="mt-1 text-gray-400">
               <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
               </svg>
            </div>
            
            <div>
                <p class="text-sm font-medium text-gray-700">
                    Por Kilometraje:
                </p>
                @if ($kmData['kilometraje'] !== 0)
                    <p class="text-base {{ $claseTexto[$estadoKm] ?? 'text-gray-800' }}">
                        {{ number_format($proximo) }} km
                    </p>
                    <span class="block text-xs {{ $estadoKm !== 'verde' ? $claseTexto[$estadoKm] : 'text-gray-500' }}">
                        @if($kmFaltantes < 0)
                            ¡Excedido por {{ number_format(abs($kmFaltantes)) }} km!
                        @else
                            Faltan: {{ number_format($kmFaltantes) }} km
                        @endif
                    </span>
                @else
                    <span class="text-gray-400">—</span>
                @endif
            </div>
        </div>

        {{-- BLOQUE 2: TIEMPO (Solo si hay historial) --}}
        @if ($proximaFecha)
            <div class="flex items-start justify-end sm:justify-start gap-2 border-t border-gray-100 pt-2">
                 <div class="mt-1 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />
                    </svg>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-700">
                        Por Fecha:
                    </p>
                    <p class="text-base {{ $claseTexto[$estadoFecha] ?? 'text-gray-800' }}">
                        {{ $proximaFecha->format('d/m/Y') }}
                    </p>
                    <span class="block text-xs {{ $estadoFecha !== 'verde' ? $claseTexto[$estadoFecha] : 'text-gray-500' }}">
                        @if($diasRestantes < 0)
                            ¡Vencido hace {{ abs(intval($diasRestantes)) }} días!
                        @else
                            Faltan: {{ intval($diasRestantes) }} días
                        @endif
                    </span>
                </div>
            </div>
        @endif
    </div>
</div>

                                <div class="flex-shrink-0">
                                    <div
                                        class="inline-flex items-center px-4 py-4 rounded-full text-sm font-semibold {{ $coloresBadge[$estatus] ?? 'bg-gray-400' }} text-white shadow-sm">
                                        {{ $textosBadge[$estatus] ?? 'Indefinido' }}
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div x-show="tab === 'fotos'" x-cloak>
                            @if ($fotos)
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">

                                    {{-- Foto Delantera --}}
                                    @if ($fotos->foto_del)
                                        <a href="{{ $fotos->foto_del }}" data-fancybox="gallery"
                                            data-caption="Foto delantera del auto"
                                            class="block overflow-hidden rounded-lg ring-1 ring-gray-200">
                                            <img src="{{ $fotos->foto_del }}"
                                                class="h-28 w-full object-cover hover:cursor-pointer transition-transform duration-300 hover:scale-105"
                                                alt="Foto delantera">
                                        </a>
                                    @endif

                                    {{-- Foto Trasera --}}
                                    @if ($fotos->foto_tra)
                                        <a href="{{ $fotos->foto_tra }}" data-fancybox="gallery"
                                            data-caption="Foto trasera del auto"
                                            class="block overflow-hidden rounded-lg ring-1 ring-gray-200">
                                            <img src="{{ $fotos->foto_tra }}"
                                                class="h-28 w-full object-cover hover:cursor-pointer transition-transform duration-300 hover:scale-105"
                                                alt="Foto trasera">
                                        </a>
                                    @endif

                                    {{-- Foto Lado Derecho --}}
                                    @if ($fotos->foto_lado_der)
                                        <a href="{{ $fotos->foto_lado_der }}" data-fancybox="gallery"
                                            data-caption="Foto lado derecho del auto"
                                            class="block overflow-hidden rounded-lg ring-1 ring-gray-200">
                                            <img src="{{ $fotos->foto_lado_der }}"
                                                class="h-28 w-full object-cover hover:cursor-pointer transition-transform duration-300 hover:scale-105"
                                                alt="Foto lado derecho">
                                        </a>
                                    @endif

                                    {{-- Foto Lado Izquierdo --}}
                                    @if ($fotos->foto_lado_izq)
                                        <a href="{{ $fotos->foto_lado_izq }}" data-fancybox="gallery"
                                            data-caption="Foto lado izquierdo del auto"
                                            class="block overflow-hidden rounded-lg ring-1 ring-gray-200">
                                            <img src="{{ $fotos->foto_lado_izq }}"
                                                class="h-28 w-full object-cover hover:cursor-pointer transition-transform duration-300 hover:scale-105"
                                                alt="Foto lado izquierdo">
                                        </a>
                                    @endif

                                </div>

                                {{-- Mensaje si el objeto $fotos existe pero todos los campos son nulos --}}
                                @if (!$fotos->foto_del && !$fotos->foto_tra && !$fotos->foto_lado_der && !$fotos->foto_lado_izq)
                                    <div class="text-sm text-gray-500 py-4 text-center">No hay imágenes cargadas en la
                                        última supervisión.</div>
                                @endif
                            @else
                                <div
                                    class="flex flex-col items-center justify-center py-10 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-8 mb-2 text-gray-400">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                    </svg>
                                    <p>No hay imágenes de supervisión reciente.</p>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>



            </div>
        </div>
        <div class="lg:col-span-2 space-y-8 mx-6 mt-5 md:mx-10">
            <div class="rounded-xl border border-gray-200 bg-white shadow-md">
                <div class="border-b border-gray-200 px-6 pt-4">
                    <nav class="flex space-x-4 overflow-x-auto">
                        <button @click="tabHistorial = 'historial'"
                            :class="tabHistorial === 'historial' ? 'bg-emerald-600 text-white' :
                                'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-t-md text-sm font-semibold transition-colors whitespace-nowrap cursor-pointer">
                            Historial de Órdenes
                        </button>
                        <button @click="tabHistorial = 'semanal'"
                            :class="tabHistorial === 'semanal' ? 'bg-emerald-600 text-white' :
                                'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-t-md text-sm font-semibold transition-colors whitespace-nowrap cursor-pointer">
                            Supervisión Semanal
                        </button>
                        <button @click="tabHistorial = 'diaria'"
                            :class="tabHistorial === 'diaria' ? 'bg-emerald-600 text-white' :
                                'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-t-md text-sm font-semibold transition-colors whitespace-nowrap cursor-pointer">
                            Supervisión Diaria
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    <div x-show="tabHistorial === 'historial'" x-cloak>
                        <div class="flex justify-between mb-4">
                            <h3 class="text-lg font-semibold leading-6 text-emerald-800">
                                Todas las órdenes del vehículo
                            </h3>
                            <a href="/ordenvehiculos/create?vehiculo_id={{ $vehiculo->id }}&return_url=/{{ urlencode(request()->getPath()) }}"
                                class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Crear Orden
                            </a>
                        </div>

                        @include('ordenvehiculos.tabla', ['noEconomico' => $vehiculo->no_economico])
                    </div>

                    <div x-show="tabHistorial === 'semanal'" x-cloak>
                        {{-- CASO 1: VEHÍCULO EN MANTENIMIENTO --}}
                        @if ($vehiculo->estado === 'En Mantenimiento')
                            <div class="bg-amber-50 border-l-4 border-amber-500 p-6 rounded-r shadow-sm">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-amber-800">Vehículo en Mantenimiento</h3>
                                        <p class="mt-2 text-sm text-amber-700">
                                            No es posible realizar supervisiones mientras el vehículo tenga una orden de
                                            servicio activa.
                                            <br>Estado actual de la orden:
                                            <strong>{{ $ordenActiva->status ?? 'Pendiente' }}</strong>
                                        </p>

                                        @if (isset($ordenActiva))
                                            <div class="mt-4">
                                                <button type="button"
                                                    @click="$dispatch('open-finish-modal-global', {{ json_encode($ordenActiva) }})"
                                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Finalizar Orden y Liberar Vehículo
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- CASO 2: SUPERVISIÓN YA HECHA --}}
                        @elseif ($supervision_existe)
                            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r shadow-sm">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-emerald-700">
                                            La supervisión semanal ya fue realizada para este vehículo en la semana
                                            actual.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- INCLUIMOS EL FORMULARIO --}}
                            @include('components.super_sem_form', [
                                'vehiculo_id' => $vehiculo->id,
                                'no_economico' => $vehiculo->no_economico,
                            ])
                        @endif
                    </div>

                    <div x-show="tabHistorial === 'diaria'" x-cloak>
                        {{-- CASO 1: VEHÍCULO EN MANTENIMIENTO (Bloqueo) --}}
                        @if ($vehiculo->estado === 'En Mantenimiento')
                            <div class="bg-amber-50 border-l-4 border-amber-500 p-6 rounded-r shadow-sm">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-amber-800">Vehículo en Mantenimiento</h3>
                                        <p class="mt-2 text-sm text-amber-700">
                                            No es posible realizar supervisiones mientras el vehículo tenga una orden de
                                            servicio activa.
                                            <br>Estado actual de la orden:
                                            <strong>{{ $ordenActiva->status ?? 'Pendiente' }}</strong>
                                        </p>

                                        @if (isset($ordenActiva))
                                            <div class="mt-4">
                                                <button type="button"
                                                    @click="$dispatch('open-finish-modal-global', {{ json_encode($ordenActiva) }})"
                                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors">
                                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Finalizar Orden y Liberar Vehículo
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            {{-- INCLUIMOS EL FORMULARIO --}}
                        @else
                            @include('components.super_diaria_form', [
                                'vehiculo_id' => $vehiculo->id,
                                'no_economico' => $vehiculo->no_economico,
                            ])
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- MODAL GLOBAL PARA FINALIZAR ORDEN --}}
    {{-- Este vive fuera de los tabs para no depender de su visibilidad --}}
    <div x-data="finishOrderModal()" @open-finish-modal-global.window="open($event.detail)" x-show="isOpen" x-cloak
        class="fixed inset-0 z-200 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-transition.opacity>

        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md transform transition-all" @click.away="close()">

            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-zinc-900">Actualizar estado</h3>
                <button @click="close()" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <div class="bg-amber-50 border-l-4 border-amber-400 p-3 mb-4 rounded-r">
                <p class="text-xs text-amber-800">
                    Al finalizar la orden <strong>#<span x-text="orden?.id"></span></strong>, el vehículo pasará
                    automáticamente a estado <strong>"En Circulación"</strong> y podrás realizar supervisiones.
                </p>
            </div>

            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Kilometraje de Salida <span
                            class="text-red-500">*</span></label>
                    <div class="relative rounded-md shadow-sm">
                        <input type="text" x-model="form.kilometraje" required
                            class="w-full border border-gray-300 rounded-md focus:ring-emerald-600 focus:border-emerald-600 py-2 pl-3 pr-12 shadow-sm mask-km"
                            placeholder="Ingrese el kilometraje de salida">

                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">km</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Fecha de Terminación <span
                            class="text-red-500">*</span></label>
                    <input type="date" x-model="form.fechaTerminacion" required
                        class="w-full border border-gray-300 rounded-md focus:ring-emerald-600 focus:border-emerald-600 p-2 shadow-sm"
                        x-bind:max="maxDate" @blur="validateFecha()">
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t pt-4 border-gray-300">
                <button @click="close()"
                    class="px-4 py-2 text-zinc-600 hover:bg-zinc-100 rounded-md text-sm font-medium transition-colors">
                    Cancelar
                </button>
                <button @click="save()"
                    class="px-4 py-2 bg-emerald-600 text-white hover:bg-emerald-700 rounded-md text-sm font-medium shadow-sm transition-colors flex items-center">
                    <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Guardar
                </button>
            </div>
        </div>
    </div>

    <script>
        function finishOrderModal() {
            return {
                isOpen: false,
                loading: false,
                orden: null,
                form: {
                    kilometraje: '',
                    fechaTerminacion: '', // Hoy por defecto
                    status: 'TERMINADO' // Forzamos terminado
                },
                maxDate: new Date().toLocaleDateString('en-CA'),
                open(ordenData) {
                    this.orden = ordenData;
                    this.form.kilometraje = ''; // Limpiar km
                    this.form.fechaTerminacion = this.maxDate;
                    this.isOpen = true;
                },
                close() {
                    this.isOpen = false;
                },
                validateFecha() {
                    // Si no hay fecha, no hacemos nada
                    if (!this.form.fechaTerminacion) return;

                    // Comparamos cadenas (YYYY-MM-DD)
                    if (this.form.fechaTerminacion > this.maxDate) {
                        // Opción A: Resetear a HOY
                        this.form.fechaTerminacion = this.maxDate;

                        // Opción B: Si prefieres borrarlo
                        // this.tempData.fechaTerminacion = '';

                        // Usamos tu SweetAlert existente para un aviso sutil (Toast)
                        const Swal = window.Swal; // Aseguramos acceso a Swal
                        if (Swal) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'warning',
                                title: 'No puedes seleccionar fechas futuras',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        } else {
                            alert('No puedes seleccionar fechas futuras');
                        }
                    }
                },
                async save() {
                    if (!this.form.kilometraje) {
                        Swal.fire({
                            toast: true,
                            position: "top-end",
                            icon: 'warning',
                            allowOutsideClick: false,
                            title: 'El kilometraje es obligatorio',
                            timer: 3000,
                            showConfirmButton: false
                        });
                        return;
                    }

                    this.loading = true;

                    // Usamos el mismo endpoint que usabas en la tabla
                    const url = `/ordenvehiculos/modal/${this.orden.id}`;

                    // Token CSRF (Leaf/Laravel)
                    const tokenInput = document.querySelector('input[name="_token"]');
                    const csrfToken = tokenInput ? tokenInput.value : '';

                    try {
                        const response = await fetch(url, {
                            method: 'PUT', // Tu controlador espera PUT
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                kilometraje: this.form.kilometraje,
                                fecha_terminacion: this.form.fechaTerminacion || this.maxDate,
                                status: this.form.status
                            })
                        });

                        const data = await response.json();

                        if (data.status === 'success') {
                            this.close();
                            await Swal.fire({
                                icon: 'success',
                                title: '¡Vehículo Liberado!',
                                text: 'La orden se finalizó correctamente.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            // Recargamos la página para que desaparezca la alerta amarilla
                            // y aparezcan los formularios de supervisión
                            window.location.reload();
                        } else {
                            throw new Error(data.message || 'Error desconocido');
                        }

                    } catch (error) {
                        Swal.fire({
                            toast: true,
                            position: "top-end",
                            icon: "warning",
                            allowOutsideClick: false,
                            title: error.message || 'Ocurrió un error',
                            showConfirmButton: false,
                            timer: 3000,
                            didOpen: () => {
                                const backdrop = document.querySelector('.swal2-backdrop-show');
                                if (backdrop) {
                                    backdrop.addEventListener('click', (e) => {
                                        e
                                            .stopPropagation(); // evita que el clic llegue al @click.away
                                        // Opcional: también puedes evitar que se cierre Swal si quieres
                                        // e.preventDefault();
                                    });
                                }
                            }
                        });
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endsection
