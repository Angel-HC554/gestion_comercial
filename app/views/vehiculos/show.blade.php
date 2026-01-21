@extends('layouts.app-layout', [
    'title' => 'Información del vehículo',
])

@section('content')
    <div x-data="{
        tab: 'estado', // Pestaña activa del primer grupo (Estado/Fotos)
        tabHistorial: 'analisis' // Pestaña activa del segundo grupo (Historial/Supervisión)
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
                <div class="rounded-xl border border-zinc-300 bg-white p-5 shadow-md">
                    <div
                        class="flex items-start gap-4 bg-gradient-to-t from-emerald-600 to-emerald-900 text-white rounded-t-lg -m-5 mb-2 p-4 md:p-5">
                        <div class="h-20 w-36 shrink-0 overflow-hidden rounded-md bg-gray-100 ring-1 ring-white/20">
                            <img src="{{ $vehiculo->foto_url }}"
                                alt="foto del vehículo" class="h-full w-full object-cover">
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

                <div class="rounded-xl border border-zinc-300 bg-white shadow-md">
                    <div class="border-b border-zinc-300 px-6 pt-4">
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
                                // Obtener info centralizada del modelo vehiculo
                                $info = $vehiculo->info_mantenimiento;

                                // Extraer variables
                                $estatus = $info['estatus_general'];
                                $estadoKm = $info['estatus_km'];
                                $estadoFecha = $info['estatus_tiempo'];
                                $proximo = $info['km_proximo_servicio'];
                                $kmFaltantes = $info['km_faltantes'];
                                $proximaFecha = $info['fecha_proximo_servicio'];
                                $diasRestantes = $info['dias_restantes'];
                                $kmUltimo = $info['km_ultimo_mantenimiento'];
                                $intervalo_de_km = $info['intervalo_de_km'];
                                $intervalo_de_meses = $info['intervalo_de_meses'];

                                // Datos visuales extra
                                $kmData = $vehiculo->ultimoKilometraje();
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

                                // Recuperamos los intervalos de la configuración del modelo
                                $intKm = $info['intervalo_de_km'] > 0 ? $info['intervalo_de_km'] : 10000;
                                $intMeses = $info['intervalo_de_meses'] > 0 ? $info['intervalo_de_meses'] : 6;

                                // --- 1. CÁLCULO KM ---
                                // Cuánto se ha recorrido desde el último servicio
                                $kmRecorridosDesdeServicio = $info['km_actual'] - $info['km_ultimo_mantenimiento'];
                                // Porcentaje de uso (0 a 100%)
                                $porcentajeKm = ($kmRecorridosDesdeServicio / $intKm) * 100;
                                $porcentajeKm = max(0, min(100, $porcentajeKm));

                                // Colores Barra KM
                                $colorBarraKm = 'bg-emerald-500';
                                if ($porcentajeKm > 75) {
                                    $colorBarraKm = 'bg-yellow-500';
                                }
                                if ($porcentajeKm > 90 || $kmFaltantes < 0) {
                                    $colorBarraKm = 'bg-red-500';
                                }

                                // --- 2. CÁLCULO TIEMPO (MESES Y DÍAS) ---
                                $textoTiempo = 'Sin registro de fecha';
                                $porcentajeTiempo = 0;
                                $colorBarraTiempo = 'bg-gray-200';

                                if ($proximaFecha) {
                                    $diasTotalesIntervalo = $intMeses * 30; // Estimado
                                    $diasTranscurridos = $diasTotalesIntervalo - $diasRestantes;

                                    $porcentajeTiempo = ($diasTranscurridos / $diasTotalesIntervalo) * 100;
                                    $porcentajeTiempo = max(0, min(100, $porcentajeTiempo));

                                    // --- CORRECCIÓN AQUÍ: Usamos diff() nativo de Carbon ---
                                    // Esto calcula exacto usando calendario real
                                    $hoy = \Carbon\Carbon::now()->startOfDay();
                                    $target = $proximaFecha->copy()->startOfDay();

                                    // Obtenemos la diferencia desglosada (años, meses, días)
                                    $diff = $hoy->diff($target);

                                    // Convertimos años a meses para mostrar "12 meses" en lugar de "1 año"
                                    $mesesFaltan = $diff->y * 12 + $diff->m;
                                    $diasFaltan = $diff->d;

                                    // Convertir días restantes a "Meses y Días" para el usuario
                                    if ($diasRestantes < 0) {
                                        $textoTiempo = 'Vencido hace ' . abs(intval($diasRestantes)) . ' días';
                                        $colorBarraTiempo = 'bg-red-500';
                                    } else {
                                        if ($mesesFaltan > 0) {
                                            $textoTiempo =
                                                'Faltan ' .
                                                intval($mesesFaltan) .
                                                ' meses y ' .
                                                intval($diasFaltan) .
                                                ' días';
                                        } else {
                                            $textoTiempo = 'Faltan ' . intval($diasFaltan) . ' días';
                                        }

                                        // Colores Barra Tiempo
                                        $colorBarraTiempo = 'bg-emerald-500';
                                        if ($porcentajeTiempo > 75) {
                                            $colorBarraTiempo = 'bg-yellow-500';
                                        }
                                        if ($porcentajeTiempo > 90) {
                                            $colorBarraTiempo = 'bg-red-500';
                                        }
                                    }
                                }

                            @endphp

                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                                {{-- TARJETA 1: ODÓMETRO (Dato Duro) --}}
                                <div
                                    class="flex items-center p-4 bg-gray-50 rounded-lg border border-zinc-300 shadow-sm relative overflow-hidden">
                                    <div class="relative z-10 w-full">
                                        <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">
                                            Kilometraje Actual</h2>
                                        <div class="flex items-baseline gap-1">
                                            @if ($kmData['kilometraje'] !== 0)
                                                <span
                                                    class="text-3xl font-extrabold text-gray-800">{{ number_format($kmData['kilometraje']) }}</span>
                                                <span class="text-sm font-medium text-gray-600">km</span>
                                            @else
                                                <span class="text-xl text-gray-400">Sin registro</span>
                                            @endif
                                        </div>
                                        <div class="mt-2 text-xs text-gray-500">
                                            Última lectura:
                                            {{ $kmData['fecha'] ? \Carbon\Carbon::parse($kmData['fecha'])->format('d/m/Y') : '--' }}
                                        </div>
                                    </div>
                                    {{-- Icono fondo --}}
                                    <div class="absolute right-2 bottom-5 opacity-10 text-gray-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" width="100px" height="100px" viewBox="-1 0 19 19" class="cf-icon-svg"><path d="M16.417 9.583A7.917 7.917 0 1 1 8.5 1.666a7.917 7.917 0 0 1 7.917 7.917zm-3.948-1.455-.758-1.955a.816.816 0 0 0-.726-.498H6.054a.816.816 0 0 0-.727.498L4.57 8.128a1.43 1.43 0 0 0-1.052 1.375v2.046a.318.318 0 0 0 .317.317h.496v1.147a.238.238 0 0 0 .238.237h.892a.238.238 0 0 0 .237-.237v-1.147h5.644v1.147a.238.238 0 0 0 .237.237h.892a.238.238 0 0 0 .238-.237v-1.147h.496a.318.318 0 0 0 .317-.317V9.503a1.43 1.43 0 0 0-1.052-1.375zm-7.445.582a.792.792 0 1 0 .792.792.792.792 0 0 0-.792-.792zm5.96-2.402a.192.192 0 0 1 .137.094l.65 1.676H5.267l.65-1.676a.192.192 0 0 1 .136-.094h4.93zm1.04 2.402a.792.792 0 1 0 .792.792.792.792 0 0 0-.791-.792z"/></svg>
                                    </div>
                                </div>

                                {{-- TARJETA 2: REFERENCIA --}}
                                <div
                                    class="flex flex-col justify-center p-4 bg-white rounded-lg border border-zinc-300 shadow-sm">
                                    <h2
                                        class="text-xs font-semibold uppercase tracking-wider text-gray-600 mb-2 border-b border-gray-100 pb-1">
                                        Datos de Mantenimiento
                                    </h2>
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs text-gray-500">Último Servicio:</span>
                                            <span class="text-sm font-bold text-gray-700">
                                                {{ $kmUltimo ? number_format($kmUltimo) . ' km' : '0 km' }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between items-center mb-6">
                                            <span class="text-xs text-gray-500">Fecha Último:</span>
                                            <span class="text-sm font-bold text-gray-700">
                                                {{ $vehiculo->latestMantenimiento?->fecha_terminacion ? \Carbon\Carbon::parse($vehiculo->latestMantenimiento->fecha_terminacion)->format('d/m/Y') : 'N/A' }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between items-center bg-blue-50 px-2 py-1 rounded">
                                            <span class="text-xs text-blue-600 font-medium">Regla aplicada:</span>
                                            <span class="text-xs font-bold text-blue-800">
                                                Cada {{ number_format($intKm) }} km / {{ $intMeses }} meses
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- TARJETA 3: ESTADO DE SALUD (Las Barras) --}}
                                <div
                                    class="flex flex-col justify-center p-4 bg-white rounded-lg border border-zinc-300 {{ $estadoKm === 'rojo' || $estadoFecha === 'rojo' ? 'border-red-500' : ($estadoKm === 'amarillo' || $estadoFecha === 'amarillo' ? 'border-l-yellow-400' : 'border-l-emerald-500') }} border-l-8 shadow-sm">
                                    <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-600 mb-3 border-b border-gray-100 pb-1">Próximo
                                        Servicio</h2>

                                    {{-- 1. BARRA KILOMETRAJE --}}
                                    <div class="mb-4">
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="font-bold text-gray-700">Kilometraje</span>
                                            <span
                                                class="{{ $kmFaltantes < 0 ? 'text-red-600 font-bold' : 'text-gray-600' }}">
                                                {{ $kmFaltantes < 0 ? 'Excedido ' . number_format(abs($kmFaltantes)) . ' km' : 'Faltan ' . number_format($kmFaltantes) . ' km' }}
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="{{ $colorBarraKm }} h-2 rounded-full transition-all duration-1000"
                                                style="width: {{ $porcentajeKm }}%"></div>
                                        </div>
                                        <div class="text-xs text-gray-600 mt-1 text-right">
                                            Meta: {{ number_format($proximo) }} km
                                        </div>
                                    </div>

                                    {{-- 2. BARRA TIEMPO (MESES) --}}
                                    <div>
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="font-bold text-gray-700">Tiempo</span>
                                            <span
                                                class="{{ $diasRestantes < 0 ? 'text-red-600 font-bold' : 'text-gray-600' }}">
                                                {{ $textoTiempo }}
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="{{ $colorBarraTiempo }} h-2 rounded-full transition-all duration-1000"
                                                style="width: {{ $porcentajeTiempo }}%"></div>
                                        </div>
                                        @if ($proximaFecha)
                                            <div class="text-xs text-gray-600 mt-1 text-right">Meta:
                                                {{ $proximaFecha->format('d/m/Y') }}</div>
                                        @endif
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
            <div class="rounded-xl border border-zinc-300 bg-white shadow-md">
                <div class="border-b border-zinc-300 px-6 pt-4">
                    <nav class="flex space-x-4 overflow-x-auto">
                        <button @click="tabHistorial = 'analisis'"
                            :class="tabHistorial === 'analisis' ? 'bg-emerald-600 text-white' :
                                'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-t-md text-sm font-semibold transition-colors whitespace-nowrap cursor-pointer">
                            Historial de Órdenes
                        </button>
                        <button @click="tabHistorial = 'historial'"
                            :class="tabHistorial === 'historial' ? 'bg-emerald-600 text-white' :
                                'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-t-md text-sm font-semibold transition-colors whitespace-nowrap cursor-pointer">
                            Listado de órdenes
                        </button>
                        @if (!auth()->user()->is('oficinista'))
                        <button @click="tabHistorial = 'semanal'"
                        :class="tabHistorial === 'semanal' ? 'bg-emerald-600 text-white' :
                                'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-t-md text-sm font-semibold transition-colors whitespace-nowrap cursor-pointer">
                            Supervisión Semanal
                        </button>
                        @endif
                        <button @click="tabHistorial = 'diaria'"
                            :class="tabHistorial === 'diaria' ? 'bg-emerald-600 text-white' :
                                'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-t-md text-sm font-semibold transition-colors whitespace-nowrap cursor-pointer">
                            Supervisión Diaria
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    <div x-show="tabHistorial === 'analisis'" x-cloak>
                        @if ($ordenes->isEmpty())
                            <div class="p-4 bg-blue-50 text-blue-700 rounded-lg text-center border border-blue-200">
                                No hay registros de ordenes para este vehículo aún.
                            </div>
                        @else
                            @include('vehiculos.grafica', ['chartData' => $chartData])
                        @endif
                    </div>

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

                    @if(!auth()->user()->is('oficinista'))
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
                                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors cursor-pointer">
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
                                    <div class="shrink-0">
                                        <svg class="h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3 flex-1 flex justify-start items-center gap-4">
                                        <p class="text-md text-emerald-700">
                                            La supervisión semanal ya fue realizada para este vehículo.
                                        </p>
                                        <p class="mt-2 text-md md:mt-0 md:ml-6 hover:underline">
                                            <a href="{{ '/supervisiones/pdf/' . $id_supervision }}" target="_blank"
                                                class="whitespace-nowrap font-bold text-emerald-700 hover:text-emerald-600 flex items-center gap-1">
                                                Ver supervisión PDF <svg xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20" fill="currentColor" class="size-5">
                                                    <path fill-rule="evenodd"
                                                        d="M5.22 14.78a.75.75 0 0 0 1.06 0l7.22-7.22v5.69a.75.75 0 0 0 1.5 0v-7.5a.75.75 0 0 0-.75-.75h-7.5a.75.75 0 0 0 0 1.5h5.69l-7.22 7.22a.75.75 0 0 0 0 1.06Z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </a>
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
                    @endif

                    <div x-show="tabHistorial === 'diaria'" x-cloak x-data="{ subTab: 'form' }">
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
                                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors cursor-pointer">
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
                            <div class="flex justify-center mb-6">
                                <div class="border border-zinc-300 bg-gray-100 p-1 rounded-lg inline-flex shadow-inner">
                                    <button @click="subTab = 'form'"
                                        :class="subTab === 'form' ? 'bg-white text-emerald-700 shadow-sm' :
                                            'text-gray-500 hover:text-gray-700'"
                                        class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center gap-2 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                        Nueva Supervisión
                                    </button>

                                    <button @click="subTab = 'historial'"
                                        hx-get="/supervision-diaria/{{ $vehiculo->id }}/historial"
                                        hx-target="#historial-container" hx-indicator="#loading-history"
                                        hx-trigger="click once"
                                        :class="subTab === 'historial' ? 'bg-white text-emerald-700 shadow-sm' :
                                            'text-gray-500 hover:text-gray-700'"
                                        class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center gap-2 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Ver Historial
                                    </button>
                                </div>
                            </div>
                            <div x-show="subTab === 'form'" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0">
                                @include('components.super_diaria_form', [
                                    'vehiculo_id' => $vehiculo->id,
                                    'no_economico' => $vehiculo->no_economico,
                                ])
                            </div>
                            <div x-show="subTab === 'historial'" x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0">
                                <div id="loading-history" class="htmx-indicator flex justify-center py-10">
                                    <svg class="animate-spin h-8 w-8 text-emerald-600" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>
                                <div id="historial-container"></div>
                            </div>
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
                    class="px-4 py-2 text-zinc-600 hover:bg-zinc-100 rounded-md text-sm font-medium transition-colors cursor-pointer">
                    Cancelar
                </button>
                <button @click="save()"
                    class="px-4 py-2 bg-emerald-600 text-white hover:bg-emerald-700 rounded-md text-sm font-medium shadow-sm transition-colors flex items-center cursor-pointer">
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
                    this.form.fechaTerminacion = ''; // No establecer fecha por defecto
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
                                fecha_terminacion: this.form.fechaTerminacion,
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

        .htmx-indicator {
            display: none !important;
        }

        .htmx-indicator.htmx-request {
            display: flex !important;
        }
    </style>
@endsection
