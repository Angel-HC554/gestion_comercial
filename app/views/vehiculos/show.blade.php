@extends('layouts.app-layout')

@section('title', 'Detalle de Vehículo')

@section('content')
<div x-data="{ 
    tab: 'estado', // Pestaña activa del primer grupo (Estado/Fotos)
    tabHistorial: 'historial' // Pestaña activa del segundo grupo (Historial/Supervisión)
}" class="min-h-screen pb-10">

    <div class="flex items-center justify-between mb-6 mx-6 md:mx-10 pt-6">
        <h1 class="text-3xl font-bold tracking-tight text-zinc-900 flex items-center gap-2">
            Información del vehículo
        </h1>
        
        <a href="/vehiculos" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
            Volver
        </a>
    </div>

    <div class="mx-6 md:mx-10 grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-1">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-md">
                <div class="flex items-start gap-4 bg-gradient-to-t from-emerald-600 to-emerald-900 text-white rounded-t-lg -m-5 mb-2 p-4 md:p-5">
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
                            :class="tab === 'estado' ? 'bg-emerald-600 text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-t-md text-sm font-semibold transition-colors">
                            Estado KM
                        </button>
                        <button @click="tab = 'fotos'"
                            :class="tab === 'fotos' ? 'bg-emerald-600 text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-t-md text-sm font-semibold transition-colors">
                            Fotos
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    
                    <div x-show="tab === 'estado'" x-cloak>
    @php
        // 1. Definir estatus y colores de fondo (Badge)
        $estatus = $vehiculo->estado_mantenimiento;
        
        $colores = [
            'verde' => 'bg-green-600',
            'amarillo' => 'bg-yellow-500',
            'rojo' => 'bg-red-500',
            'rojo_pasado' => 'bg-red-700 animate-pulse',
            'gris' => 'bg-gray-400',
        ];
        $textos = [
            'verde' => 'Vehiculo al día',
            'amarillo' => 'Mantenimiento próximo',
            'rojo' => 'Mantenimiento Urgente',
            'rojo_pasado' => 'MANTENIMIENTO VENCIDO',
            'gris' => 'Sin datos de KM',
        ];

        // 2. RECUPERADO: Colores de texto para "Próximo servicio" (Faltaba esto)
        $texto2 = [
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
    @endphp

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        
        <div class="flex-1">
            <h2 class="text-sm font-medium text-gray-700 mb-1">Kilometraje actual</h2>
            <p class="text-2xl font-bold text-gray-800">
                @if ($kmData['kilometraje'] !== 0)
                    {{ number_format($kmData['kilometraje']) }} <span class="text-sm font-normal text-gray-600">km</span>
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
                    {{ number_format($kmUltimo) }} <span class="text-sm font-normal text-gray-600">km</span>
                    
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
            <h2 class="text-sm font-medium text-gray-400 mb-1">Próximo servicio</h2>
            <p class="text-xl font-semibold {{ $texto2[$estatus] ?? 'bg-gray-400' }}">
                @if($kmData['kilometraje'] !== 0)
                    {{ number_format($proximo) }} km
                    <span class="block text-xs text-gray-500 mt-1">
                        Faltan: {{ number_format(max(0, $proximo - $kmData['kilometraje'])) }} km
                    </span>
                @else
                    <span class="text-gray-400">—</span>
                @endif
            </p>
        </div>

        <div class="flex-shrink-0">
            <div class="inline-flex items-center px-4 py-4 rounded-full text-sm font-semibold {{ $colores[$estatus] ?? 'bg-gray-400' }} text-white shadow-sm">
                {{ $textos[$estatus] ?? 'Indefinido' }}
            </div>
        </div>

    </div>
</div>

                    <div x-show="tab === 'fotos'" x-cloak>
    @if ($fotos)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            
            {{-- Foto Delantera --}}
            @if($fotos->foto_del)
                <a href="{{ $fotos->foto_del }}" 
                   data-fancybox="gallery" 
                   data-caption="Foto delantera del auto"
                   class="block overflow-hidden rounded-lg ring-1 ring-gray-200">
                    <img src="{{ $fotos->foto_del }}" 
                         class="h-28 w-full object-cover hover:cursor-pointer transition-transform duration-300 hover:scale-105" 
                         alt="Foto delantera">
                </a>
            @endif

            {{-- Foto Trasera --}}
            @if($fotos->foto_tra)
                <a href="{{ $fotos->foto_tra }}" 
                   data-fancybox="gallery" 
                   data-caption="Foto trasera del auto"
                   class="block overflow-hidden rounded-lg ring-1 ring-gray-200">
                    <img src="{{ $fotos->foto_tra }}" 
                         class="h-28 w-full object-cover hover:cursor-pointer transition-transform duration-300 hover:scale-105" 
                         alt="Foto trasera">
                </a>
            @endif

            {{-- Foto Lado Derecho --}}
            @if($fotos->foto_lado_der)
                <a href="{{ $fotos->foto_lado_der }}" 
                   data-fancybox="gallery" 
                   data-caption="Foto lado derecho del auto"
                   class="block overflow-hidden rounded-lg ring-1 ring-gray-200">
                    <img src="{{ $fotos->foto_lado_der }}" 
                         class="h-28 w-full object-cover hover:cursor-pointer transition-transform duration-300 hover:scale-105" 
                         alt="Foto lado derecho">
                </a>
            @endif

            {{-- Foto Lado Izquierdo --}}
            @if($fotos->foto_lado_izq)
                <a href="{{ $fotos->foto_lado_izq }}" 
                   data-fancybox="gallery" 
                   data-caption="Foto lado izquierdo del auto"
                   class="block overflow-hidden rounded-lg ring-1 ring-gray-200">
                    <img src="{{ $fotos->foto_lado_izq }}" 
                         class="h-28 w-full object-cover hover:cursor-pointer transition-transform duration-300 hover:scale-105" 
                         alt="Foto lado izquierdo">
                </a>
            @endif

        </div>

        {{-- Mensaje si el objeto $fotos existe pero todos los campos son nulos --}}
        @if(!$fotos->foto_del && !$fotos->foto_tra && !$fotos->foto_lado_der && !$fotos->foto_lado_izq)
            <div class="text-sm text-gray-500 py-4 text-center">No hay imágenes cargadas en la última supervisión.</div>
        @endif

    @else
        <div class="flex flex-col items-center justify-center py-10 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 mb-2 text-gray-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
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
                            :class="tabHistorial === 'historial' ? 'bg-emerald-600 text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-t-md text-sm font-semibold transition-colors whitespace-nowrap">
                            Historial de Órdenes
                        </button>
                        <button @click="tabHistorial = 'semanal'"
                            :class="tabHistorial === 'semanal' ? 'bg-emerald-600 text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-t-md text-sm font-semibold transition-colors whitespace-nowrap">
                            Supervisión Semanal
                        </button>
                        <button @click="tabHistorial = 'diaria'"
                            :class="tabHistorial === 'diaria' ? 'bg-emerald-600 text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                            class="px-4 py-2 rounded-t-md text-sm font-semibold transition-colors whitespace-nowrap">
                            Supervisión Diaria
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    <div x-show="tabHistorial === 'historial'" x-cloak>
                        <div class="flex justify-between mb-4">
                            <h3 class="text-lg font-medium leading-6 text-emerald-800">
                                Todas las órdenes del vehículo
                            </h3>
                            <a href="/ordenvehiculos/create" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Crear Orden
                            </a>
                        </div>

                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orden #</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kilometraje</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taller</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($ordenes as $orden)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $orden->id }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $orden->fechafirm ? \Carbon\Carbon::parse($orden->fechafirm)->format('d/m/Y') : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ number_format($orden->kilometraje) }} km
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $orden->taller ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="/ordenvehiculos/pdf/{{ $orden->id }}" class="text-emerald-600 hover:text-emerald-900 mr-3">PDF</a>
                                                <a href="/ordenvehiculos/{{ $orden->id }}/edit" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                No hay órdenes registradas.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div x-show="tabHistorial === 'semanal'" x-cloak>
                        @if ($supervision_existe)
                            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r shadow-sm">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-emerald-700">
                                            La supervisión semanal ya fue realizada para este vehículo en la semana actual.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- INCLUIMOS EL FORMULARIO --}}
                            @include('components.super_sem_form', [
                                'vehiculo_id' => $vehiculo->id, 
                                'no_economico' => $vehiculo->no_economico
                            ])
                        @endif
                    </div>

                    <div x-show="tabHistorial === 'diaria'" x-cloak>
                        {{-- INCLUIMOS EL FORMULARIO --}}
                        @include('components.super_diaria_form', [
                            'vehiculo_id' => $vehiculo->id, 
                            'no_economico' => $vehiculo->no_economico
                        ])
                    </div>

                </div>
            </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection