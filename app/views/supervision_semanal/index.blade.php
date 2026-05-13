@extends('layouts.app-layout', ['title' => 'Supervisión Semanal'])

@section('content')
    <div class="min-h-screen pb-10">
        <div class="flex h-full flex-1 flex-col gap-4 mx-6 md:mx-10 pt-6">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h1 class="text-3xl font-bold tracking-tight text-zinc-900">
                        Supervisión Semanal
                    </h1>
                    <p class="text-lg text-zinc-500 font-medium">
                        @if (!empty($filtrosActuales['departamento']))
                            Resumen de ubicaciones: <span class="text-zinc-700">{{ $filtrosActuales['departamento'] }}</span>
                        @else
                            Resumen general por procesos
                        @endif
                    </p>
                </div>
                <h3 class="font-semibold text-emerald-800 bg-emerald-100 px-3 py-1 rounded-md border border-emerald-200">
                    Mes: {{ strtoupper($nombreMes) }}
                </h3>
            </div>

            {{-- FORMULARIO DE FILTROS --}}
            <form method="GET" id="form-filtros" class="w-full">
                @php
                    // 1. Obtenemos los valores actuales o los del mes en curso
                    $mesNum = $filtrosActuales['mes'] ?? date('n');
                    $añoNum = $filtrosActuales['año'] ?? date('Y');

                    // 2. El input type="month" es estricto: requiere un cero a la izquierda para meses < 10 (ej. 2026-04)
                    $mesFormateado = str_pad($mesNum, 2, '0', STR_PAD_LEFT);
                    $periodoActual = $añoNum . '-' . $mesFormateado;
                @endphp
                <input type="hidden" name="semana_index" value="{{ $filtrosActuales['semana_index'] ?? 0 }}">
                <input type="hidden" name="mes" id="hidden-mes" value="{{ $mesNum }}">
                <input type="hidden" name="año" id="hidden-año" value="{{ $añoNum }}">

                <div
                    class="flex flex-col md:flex-row gap-4 items-end md:items-center bg-white rounded-lg border-t-4 border-t-emerald-600 shadow-sm p-4 border border-zinc-200">

                    <div class="flex flex-col w-full md:w-auto border-r border-zinc-200 pr-4">
                        <label class="text-sm font-bold text-zinc-700 mb-1">Periodo a consultar:</label>
                        <input type="month" value="{{ $periodoActual }}"
                            class="w-full md:w-48 h-10 border border-gray-300 bg-gray-50 rounded-md px-3 text-gray-700 outline-none cursor-pointer focus:ring-emerald-600 focus:border-emerald-600"
                            onchange="
                           if(this.value) {
                               document.getElementById('hidden-año').value = this.value.split('-')[0];
                               // Le quitamos ceros a la izquierda para que el backend reciba '4' en vez de '04'
                               document.getElementById('hidden-mes').value = parseInt(this.value.split('-')[1], 10);
                           }
                       ">
                    </div>
                    {{-- Select Departamento --}}
                    <div class="flex flex-col w-full md:w-auto">
                        <label class="text-sm font-bold text-zinc-700 mb-1">Proceso:</label>
                        <select name="departamento" x-data="{}"
                            @change="htmx.ajax('GET', '/api/ubicaciones-options?departamento=' + $el.value, {target: '.ubicacion-select', swap: 'innerHTML'})"
                            class="w-full md:w-64 h-10 border border-gray-300 bg-gray-50 rounded-md px-3">
                            <option value="">Todos los procesos</option>
                            @foreach ($departamentos as $depto)
                                <option value="{{ $depto }}"
                                    {{ ($filtrosActuales['departamento'] ?? '') == $depto ? 'selected' : '' }}>
                                    {{ $depto }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Select Ubicación --}}
                    <div class="flex flex-col w-full md:w-auto">
                        <label class="text-sm font-bold text-zinc-700 mb-1">Ubicación:</label>
                        <select name="ubicacion"
                            class="ubicacion-select w-full md:w-64 h-10 border border-gray-300 bg-gray-50 rounded-md px-3">
                            <option value="">Todas las ubicaciones</option>
                            @foreach ($ubicaciones as $ubicacion)
                                <option value="{{ $ubicacion }}"
                                    {{ ($filtrosActuales['ubicacion'] ?? '') == $ubicacion ? 'selected' : '' }}>
                                    {{ $ubicacion }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- BOTONES DE ACCIÓN --}}
                    <div class="flex gap-2 w-full md:w-auto mt-6">
                        <button type="submit" formaction="/supervision-semanal"
                            class="flex-1 md:flex-none h-10 px-6 bg-zinc-800 hover:bg-zinc-700 text-white font-medium rounded-md flex items-center justify-center gap-2 transition-colors">
                            Filtrar Resumen
                        </button>

                        {{-- ESTE BOTÓN TE LLEVA AL DETALLE CON LOS MISMOS FILTROS --}}
                        <button type="submit" formaction="/supervision-semanal/detallado"
                            class="flex-1 md:flex-none h-10 px-6 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-md flex items-center justify-center gap-2 transition-colors">
                            Ver Matriz Detallada
                        </button>
                    </div>
                </div>
            </form>

            <hr class="border-zinc-200">

            {{-- CONTROLES DE SEMANA (Ahora usando parámetros GET para recargar con filtros) --}}
            <div class="bg-white rounded-lg shadow-sm p-4 border border-zinc-200 flex justify-between items-center">
                <button
                    onclick="document.querySelector('[name=semana_index]').value={{ $semanaIndex - 1 }}; document.getElementById('form-filtros').submit();"
                    @if ($semanaIndex <= 0) disabled @endif
                    class="px-4 py-2 bg-zinc-100 border text-zinc-700 rounded-md hover:bg-zinc-200 disabled:opacity-50">Semana
                    Anterior</button>
                <div class="text-center">
                    <h3 class="text-lg font-bold text-emerald-900">Semana {{ $numeroSemana }}</h3>
                    <span class="text-sm text-emerald-700 bg-emerald-200/50 px-3 rounded-full">{{ $rangoSemana }}</span>
                </div>
                <button
                    onclick="document.querySelector('[name=semana_index]').value={{ $semanaIndex + 1 }}; document.getElementById('form-filtros').submit();"
                    @if ($semanaIndex >= $totalSemanas - 1) disabled @endif
                    class="px-4 py-2 bg-zinc-100 border text-zinc-700 rounded-md hover:bg-zinc-200 disabled:opacity-50">Semana
                    Siguiente</button>
            </div>

            <div
                class="tabla-scrollable bg-white rounded-lg shadow-md border border-zinc-200 overflow-hidden animate-in fade-in zoom-in duration-300">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-zinc-50 border-b border-zinc-200">
                            <th
                                class="py-3 px-4 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider border-r border-zinc-200">
                                {{ $tipoAgrupacion }}</th>
                            <th class="py-3 px-4 text-center text-xs font-bold text-zinc-500 uppercase tracking-wider">
                                Vehículos</th>
                            <th class="py-3 px-4 text-center text-xs font-bold text-zinc-500 uppercase tracking-wider">En
                                Taller</th>
                            <th class="py-3 px-4 text-center text-xs font-bold text-zinc-500 uppercase tracking-wider">
                                Pendientes</th>
                            <th class="py-3 px-4 text-center text-xs font-bold text-emerald-600 uppercase tracking-wider">
                                Cumplimiento</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 bg-white">
                        @foreach ($resumen as $fila)
                            <tr class="hover:bg-zinc-50 transition-colors group">
                                {{-- DEPTO --}}
                                <td class="py-3 px-4 text-sm font-mono font-medium text-zinc-600 bg-zinc-50/50">
                                    {{ $fila['nombre'] }}
                                </td>

                                {{-- VEHÍCULOS --}}
                                <td class="py-3 px-4 text-center text-sm text-zinc-600">
                                    {{ $fila['total_vehiculos'] }}
                                </td>

                                {{-- EN TALLER --}}
                                <td class="py-3 px-4 text-center relative">
                                    @if ($fila['en_taller'] > 0)
                                        {{-- Contenedor Alpine.js: cada fila tiene su propio estado (abierto/cerrado) --}}
                                        <div x-data="{ tooltipOpen: false }" class="relative inline-flex">

                                            {{-- Botón interactivo (El globito amarillo) --}}
                                            <button @click="tooltipOpen = !tooltipOpen" type="button" 
                                                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200 shadow-sm hover:bg-amber-200 transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-1">
                                                {{ $fila['en_taller'] }}
                                                {{-- Flechita para indicar que se puede hacer clic --}}
                                                <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </button>

                                            {{-- EL TOOLTIP FLOTANTE (Se activa al hacer clic, se cierra al dar clic fuera) --}}
                                            <div x-show="tooltipOpen" 
                                                 @click.away="tooltipOpen = false"
                                                 x-transition:enter="transition ease-out duration-200"
                                                 x-transition:enter-start="opacity-0 translate-y-1"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 x-transition:leave="transition ease-in duration-150"
                                                 x-transition:leave-start="opacity-100 translate-y-0"
                                                 x-transition:leave-end="opacity-0 translate-y-1"
                                                 x-cloak
                                                 class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-max max-w-[220px] z-50">

                                                <div class="bg-zinc-800 text-white text-xs rounded-md py-2 px-3 shadow-xl border border-zinc-700 text-left">
                                                    <div class="font-bold text-amber-400 mb-1 border-b border-zinc-600 pb-1 flex justify-between items-center gap-3">
                                                        <span>Vehiculos:</span>
                                                        {{-- Opcional: una 'X' pequeñita por si prefieren cerrar desde adentro --}}
                                                        <button @click="tooltipOpen = false" type="button" class="text-zinc-400 hover:text-white cursor-pointer">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                    </div>
                                                    <span class="text-zinc-200 leading-relaxed block break-words">
                                                        {{ implode(', ', $fila['vehiculos_en_taller']) }}
                                                    </span>
                                                </div>
                                                {{-- El triangulito de la base --}}
                                                <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-zinc-800"></div>
                                            </div>
                                        
                                        </div>
                                    @else
                                        <span class="text-zinc-300 text-sm">-</span>
                                    @endif
                                </td>

                                {{-- PENDIENTES --}}
                                <td class="py-3 px-4 text-center">
                                    @if ($fila['pendientes'] > 0)
                                        <div class="flex flex-col items-center">
                                            <span class="text-red-600 font-bold text-sm">{{ $fila['pendientes'] }}</span>
                                            <span class="text-[10px] text-red-400">Faltantes</span>
                                        </div>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-600">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Completo
                                        </span>
                                    @endif
                                </td>

                                {{-- CUMPLIMIENTO --}}
                                <td class="py-3 px-4">
                                    <div class="flex flex-col items-center justify-center w-32 mx-auto">
                                        <div class="flex justify-between w-full text-xs mb-1">
                                            <span class="font-bold text-zinc-700">{{ $fila['cumplidos'] }} <span
                                                    class="text-zinc-400 font-normal">hechos</span></span>
                                            <span
                                                class="font-bold {{ $fila['porcentaje'] == 100 ? 'text-emerald-600' : 'text-zinc-600' }}">{{ $fila['porcentaje'] }}%</span>
                                        </div>
                                        <div
                                            class="w-full h-2 bg-gray-100 rounded-full overflow-hidden border border-gray-100">
                                            <div class="h-full transition-all duration-500 {{ $fila['porcentaje'] >= 90 ? 'bg-emerald-500' : ($fila['porcentaje'] >= 50 ? 'bg-amber-400' : 'bg-red-500') }}"
                                                style="width: {{ $fila['porcentaje'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection
