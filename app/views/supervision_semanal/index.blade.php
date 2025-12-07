@extends('layouts.app-layout')

@section('title', 'Supervisión Semanal')

@section('content')
<div class="min-h-screen bg-gray-50 pb-10">
    <div class="flex h-full flex-1 flex-col gap-4 mx-6 md:mx-10 pt-6">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h1 class="text-3xl font-bold tracking-tight text-zinc-900">Supervisiones Semanales</h1>
            <h3 class="font-semibold text-emerald-800 bg-emerald-100 px-3 py-1 rounded-md border border-emerald-200">
                Mostrando: {{ strtoupper($nombreMes) }}
            </h3>
        </div>

        <form method="GET" action="/supervision-semanal" class="w-full">
            <div class="flex flex-col md:flex-row gap-4 items-end md:items-center bg-white rounded-lg border-t-4 border-t-emerald-600 shadow-sm p-4 border border-zinc-200">
                
                <div class="flex flex-col w-full md:w-auto">
                    <label class="text-sm font-bold text-zinc-700 mb-1">Agencia:</label>
                    <select name="agencia" class="w-full md:w-64 h-10 border border-gray-300 bg-gray-50 rounded-md px-3 text-gray-700 focus:ring-emerald-600 focus:border-emerald-600 outline-none">
                        <option value="">Todas las Agencias</option>
                        @foreach($agencias as $agencia)
                            <option value="{{ $agencia }}" {{ ($filtrosActuales['agencia'] ?? '') == $agencia ? 'selected' : '' }}>
                                {{ $agencia }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col w-full md:w-auto">
                    <label class="text-sm font-bold text-zinc-700 mb-1">Cumplimiento:</label>
                    <select name="cumplimiento" class="w-full md:w-64 h-10 border border-gray-300 bg-gray-50 rounded-md px-3 text-gray-700 focus:ring-emerald-600 focus:border-emerald-600 outline-none">
                        <option value="todos">Mostrar Todos</option>
                        <option value="no_cumple" {{ ($filtrosActuales['cumplimiento'] ?? '') == 'no_cumple' ? 'selected' : '' }}>
                            Mostrar Solo Incumplidos
                        </option>
                    </select>
                </div>

                <button type="submit" class="w-full md:w-auto h-10 px-6 bg-zinc-800 hover:bg-zinc-700 text-white font-medium rounded-md transition-colors shadow-sm flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filtrar
                </button>
            </div>
        </form>

        <div id="loader-tabla" class="flex justify-center items-center py-20">
            <div class="flex flex-col items-center">
                <svg class="animate-spin h-10 w-10 text-emerald-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-zinc-500 font-medium">Cargando datos semanales...</span>
            </div>
        </div>

        <hr class="border-zinc-200">

        <div id="contenido-tabla" style="display:none;" class="tabla-scrollable bg-white rounded-lg shadow-md border border-zinc-200">
            <table class="tabla-matriz">
                <thead>
                    <tr>
                        <th class="bg-zinc-100 text-zinc-700 font-bold uppercase text-xs tracking-wider">Agencia</th>
                        <th class="bg-zinc-100 text-zinc-700 font-bold uppercase text-xs tracking-wider shadow-r">Vehículo</th>
                        @foreach($semanasDelMes as $i => $semana)
                            <th class="bg-zinc-50 text-zinc-600 font-medium text-xs border-b-2 border-zinc-200">
                                <span class="block font-bold text-emerald-700">Semana {{ $i + 1 }}</span>
                                <span class="text-zinc-400 text-[10px] uppercase">
                                    {{ \Carbon\Carbon::parse($semana['inicio'])->format('d M') }} - {{ \Carbon\Carbon::parse($semana['fin'])->format('d M') }}
                                </span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($vehiculos as $vehiculo)
                        <tr class="hover:bg-zinc-50 transition-colors">
                            <td class="font-medium text-zinc-600 text-sm">{{ $vehiculo->agencia }}</td>
                            <td class="font-bold text-zinc-800 text-sm border-r border-zinc-200 shadow-sm">{{ $vehiculo->no_economico }}</td>
                            
                            @foreach($vehiculo->status_semanas as $status)
                                <td class="p-2">
                                    @if($status['tipo'] == 'cumplido')
                                        <a href="{{ '/supervisiones/pdf/'. $status['id'] }}" target="_blank" class="flex justify-center group relative" title="Supervisión #{{ $status['id'] }}
Fecha: {{ $status['fecha'] }}">
                                            <svg class="w-7 h-7 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                                ID: {{ $status['id'] }}
                                            </span>
                                        </a>
                                    @elseif($status['tipo'] == 'no_cumplido')
                                        <div class="flex justify-center relative group" title="No Cumplido">
                                            <svg class="w-7 h-7 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    @elseif($status['tipo'] == 'futuro')
                                        <span class="text-zinc-300 font-light text-2xl">•</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($semanasDelMes) + 2 }}" class="py-10 text-center text-zinc-500">
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
    .tabla-scrollable { 
        overflow-x: auto;
        max-width: 100%;
        margin: 0 auto;
        -webkit-overflow-scrolling: touch;
        position: relative;
    }
    .tabla-matriz {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    .tabla-matriz th, 
    .tabla-matriz td {
        border-bottom: 1px solid #e5e7eb;
        border-right: 1px solid #f3f4f6;
        padding: 10px; 
        text-align: center;
        white-space: nowrap;
    }
    
    /* Header Superior Fijo */
    .tabla-matriz thead th {
        position: sticky;
        top: 0;
        z-index: 10;
    }

    /* Columna Vehículo Fija (Izquierda) */
    .tabla-matriz td:nth-child(2),
    .tabla-matriz th:nth-child(2) {
        position: sticky;
        left: 0;
        background-color: #ffffff;
        z-index: 20;
        border-right: 2px solid #e5e7eb;
    }
    
    /* Intersección Header/Columna Fija */
    .tabla-matriz th:nth-child(2) {
        z-index: 30;
        background-color: #f3f4f6;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const loader = document.getElementById('loader-tabla');
        const contenido = document.getElementById('contenido-tabla');

        if (loader && contenido) {
            setTimeout(() => {
                loader.style.display = 'none';
                contenido.style.display = 'block';
            }, 300);
        }
    });
</script>
@endsection