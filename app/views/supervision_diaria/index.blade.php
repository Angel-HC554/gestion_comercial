@extends('layouts.app-layout', [
    'title' => 'Supervisión Diaria'
])

@section('content')
<div class="min-h-screen pb-10">
    <div class="flex h-full flex-1 flex-col gap-4 mx-6 md:mx-10 pt-6">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h1 class="text-3xl font-bold tracking-tight text-zinc-900">Supervisiones Diarias</h1>
            <h3 class="font-semibold text-emerald-800 bg-emerald-100 px-3 py-1 rounded-md border border-emerald-200">
                Mostrando: {{ strtoupper($nombreMes) }}
            </h3>
        </div>

        <form method="GET" action="/supervision-diaria" class="w-full">
            <div class="flex flex-col md:flex-row gap-4 items-end md:items-center bg-white rounded-lg border-t-4 border-t-emerald-600 shadow-sm p-4 border border-zinc-200">
                
                <div class="flex flex-col w-full md:w-auto justify-between">
                    
                        <label class="text-sm font-bold text-zinc-700 mb-1">Proceso:</label>
                        <select name="departamento"
                        x-data="{}"
                        @change="htmx.ajax('GET', '/api/ubicaciones-options?departamento=' + $el.value, {target: $el.closest('form').querySelector('.ubicacion-select'), swap: 'innerHTML'})" 
                        class="w-full md:w-64 h-10 border border-gray-300 bg-gray-50 rounded-md px-3 text-gray-700 focus:ring-emerald-600 focus:border-emerald-600 outline-none cursor-pointer">
                        <option value="">Todos los procesos</option>
                        @foreach($departamentos as $depto)
                            <option value="{{ $depto }}" {{ ($filtrosActuales['departamento'] ?? '') == $depto ? 'selected' : '' }}>
                                {{ $depto }}
                            </option>
                        @endforeach
                        </select>
                    
                </div>

                <div class="flex flex-col w-full md:w-auto">
                    <label class="text-sm font-bold text-zinc-700 mb-1">Ubicación:</label>
                    <select name="ubicacion" class="ubicacion-select w-full md:w-64 h-10 border border-gray-300 bg-gray-50 rounded-md px-3 text-gray-700 focus:ring-emerald-600 focus:border-emerald-600 outline-none cursor-pointer">
                        <option value="">Todas las ubicaciones</option>
                        @foreach($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion }}" {{ ($filtrosActuales['ubicacion'] ?? '') == $ubicacion ? 'selected' : '' }}>
                                {{ $ubicacion }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col w-full md:w-auto">
                    <label class="text-sm font-bold text-zinc-700 mb-1">Cumplimiento:</label>
                    <select name="cumplimiento" class="w-full md:w-64 h-10 border border-gray-300 bg-gray-50 rounded-md px-3 text-gray-700 focus:ring-emerald-600 focus:border-emerald-600 outline-none transition-shadow cursor-pointer">
                        <option value="todos">Mostrar Todos</option>
                        <option value="no_cumple" {{ ($filtrosActuales['cumplimiento'] ?? '') == 'no_cumple' ? 'selected' : '' }}>
                            Mostrar Solo Incumplidos
                        </option>
                    </select>
                </div>

                <button type="submit" class="w-full md:w-auto h-10 px-6 mt-6 bg-zinc-800 hover:bg-zinc-700 text-white font-medium rounded-md transition-colors shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filtrar
                </button>

                <button 
                    type="button"
                    hx-get="/supervision-diaria/resumen-agencias?mes={{ $filtrosActuales['mes'] }}&año={{ $filtrosActuales['año'] }}"
                    hx-include="closest form"
                    hx-target="body" 
                    class="w-full md:w-auto h-10 px-6 mt-6 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-md transition-colors shadow-sm flex items-center justify-center gap-2 cursor-pointer"             >
                    Ver resumen por proceso
                </button>
            </div>
        </form>

        <div id="loader-tabla" class="flex justify-center items-center py-20">
            <div class="flex flex-col items-center">
                <svg class="animate-spin h-10 w-10 text-emerald-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-zinc-500 font-medium">Cargando matriz de datos...</span>
            </div>
        </div>

        <hr class="border-zinc-200">

        <div id="contenido-tabla" style="display:none;" class="tabla-scrollable bg-white rounded-lg shadow-md border border-zinc-200">
            <table class="tabla-matriz">
                <thead>
                    <tr>
                        <th class="bg-zinc-100 text-zinc-700 font-bold uppercase text-xs tracking-wider">Departamento / Ubicación</th>
                        <th class="bg-zinc-100 text-zinc-700 font-bold uppercase text-xs tracking-wider shadow-r">Vehículo</th>
                        @foreach($diasDelMes as $dia)
                            <th class="bg-zinc-50 text-zinc-500 font-medium text-xs">{{ $dia }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($vehiculos as $vehiculo)
                        <tr class="hover:bg-zinc-50 transition-colors">
                            <td class="font-medium text-zinc-600 text-sm">{{ $vehiculo->departamento }}
                                @if($vehiculo->ubicacion)
                                    <br><span class="text-xs text-zinc-400">{{ $vehiculo->ubicacion }}</span>
                                @endif
                            </td>
                            <td class="font-bold text-zinc-800 text-sm border-r border-zinc-200 shadow-sm">{{ $vehiculo->no_economico }}</td>
                            
                            @foreach($vehiculo->status_dias as $status)
                                <td class="p-1">
                                    @if($status == 'cumplido')
                                        <a href="{{ optional($vehiculo->latestSupervision)->escaneo_url }}" target="_blank" class="flex justify-center" title="Cumplido">
                                            <svg class="w-6 h-6 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        </a>
                                    @elseif($status == 'taller')
                                        <div class="flex justify-center" title="En Taller">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-amber-400">
                                                <path fill-rule="evenodd" d="M11.078 2.25c-.917 0-1.699.663-1.85 1.567L9.05 4.889c-.02.12-.115.26-.297.348a7.493 7.493 0 00-.986.57c-.166.115-.334.126-.45.083L6.3 5.508a1.875 1.875 0 00-2.282.819l-.922 1.597a1.875 1.875 0 00.432 2.385l.84.692c.095.078.17.229.154.43a7.598 7.598 0 000 1.139c.015.2-.059.352-.153.43l-.841.692a1.875 1.875 0 00-.432 2.385l.922 1.597a1.875 1.875 0 002.282.818l1.019-.382c.115-.043.283-.031.45.082.312.214.641.405.985.57.182.088.277.228.297.35l.178 1.071c.151.904.933 1.567 1.85 1.567h1.844c.916 0 1.699-.663 1.85-1.567l.178-1.072c.02-.12.114-.26.297-.349.344-.165.673-.356.985-.57.167-.114.335-.125.45-.082l1.02.382a1.875 1.875 0 002.28-.819l.923-1.597a1.875 1.875 0 00-.432-2.385l-.84-.692c-.095-.078-.17-.229-.154-.43a7.614 7.614 0 000-1.139c-.016-.2.059-.352.153-.43l.84-.692c.708-.582.891-1.59.433-2.385l-.922-1.597a1.875 1.875 0 00-2.282-.818l-1.02.382c-.114.043-.282.031-.449-.083a7.49 7.49 0 00-.985-.57c-.183-.087-.277-.227-.297-.348l-.179-1.072a1.875 1.875 0 00-1.85-1.567h-1.843zM12 15.75a3.75 3.75 0 100-7.5 3.75 3.75 0 000 7.5z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    @elseif($status == 'no_cumplido')
                                        <div class="flex justify-center" title="No Cumplido">
                                            <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    @elseif($status == 'futuro')
                                        <span class="text-zinc-300 font-light text-xl">•</span> 
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($diasDelMes) + 2 }}" class="py-10 text-center text-zinc-500">
                                No hay vehículos que coincidan con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    /* Estilos copiados y mejorados de tu versión original */
    .tabla-scrollable { 
        overflow-x: auto;
        max-width: 100%;
        margin: 0 auto;
        -webkit-overflow-scrolling: touch;
        position: relative;
    }
    .tabla-matriz {
        border-collapse: separate; /* Necesario para sticky headers limpios */
        border-spacing: 0;
        width: 100%;
    }
    .tabla-matriz th, 
    .tabla-matriz td {
        border-bottom: 1px solid #e5e7eb;
        border-right: 1px solid #f3f4f6;
        padding: 8px; 
        text-align: center;
        white-space: nowrap;
    }
    
    /* Header Superior Fijo */
    .tabla-matriz thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        border-bottom: 2px solid #e5e7eb;
    }

    /* Columna Vehículo Fija (Izquierda) */
    .tabla-matriz td:nth-child(2),
    .tabla-matriz th:nth-child(2) {
        position: sticky;
        left: 0;
        background-color: #ffffff; /* Fondo blanco para tapar scroll */
        z-index: 20;
        border-right: 2px solid #e5e7eb;
    }
    
    /* Intersección Header/Columna Fija */
    .tabla-matriz th:nth-child(2) {
        z-index: 30;
        background-color: #f3f4f6; /* Coincide con el header bg */
    }
</style>

<script>
    // Script simple para manejar el loader
    document.addEventListener("DOMContentLoaded", function() {
        const loader = document.getElementById('loader-tabla');
        const contenido = document.getElementById('contenido-tabla');

        if (loader && contenido) {
            // Pequeño timeout para evitar parpadeos si carga muy rápido
            setTimeout(() => {
                loader.style.display = 'none';
                contenido.style.display = 'block';
            }, 300);
        }
    });
</script>
@endsection