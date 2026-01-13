@extends('layouts.app-layout', ['title' => 'Resumen Diario por Agencias'])

@section('content')
<div class="min-h-screen bg-gray-50 pb-10">
    <div class="flex h-full flex-1 flex-col gap-4 mx-6 md:mx-10 pt-6 animate-in fade-in zoom-in duration-300">
        
        {{-- HEADER SUPERIOR --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
            <div>
                <h2 class="text-2xl font-bold text-zinc-900">Resumen Diario por Agencias</h2>
                <p class="text-sm text-zinc-500">
                    Acumulado del mes: <span class="font-semibold text-emerald-600 capitalize">{{ $nombreMes }}</span>
                </p>
            </div>

            {{-- BOTÓN VOLVER --}}
            <a href="/supervision-diaria?mes={{ $mes }}&año={{ $año }}" 
               class="w-full md:w-auto h-10 px-6 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-md transition-colors shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al listado
            </a>
        </div>

        {{-- TABLA DE DATOS --}}
        <div class="tabla-scrollable bg-white rounded-lg shadow-md border border-zinc-200 overflow-hidden mx-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-zinc-50 border-b border-zinc-200">
                        <th class="py-3 px-4 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider">Clave</th>
                        <th class="py-3 px-4 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider border-r border-zinc-200">Nombre</th>
                        <th class="py-3 px-4 text-center text-xs font-bold text-zinc-500 uppercase tracking-wider">Flotilla</th>
                        <th class="py-3 px-4 text-center text-xs font-bold text-zinc-500 uppercase tracking-wider">En Taller</th>
                        <th class="py-3 px-4 text-center text-xs font-bold text-zinc-500 uppercase tracking-wider">Faltas Acumuladas</th>
                        <th class="py-3 px-4 text-center text-xs font-bold text-emerald-600 uppercase tracking-wider">Cumplimiento Mensual</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 bg-white">
                    @foreach($resumen as $fila)
                        <tr class="hover:bg-zinc-50 transition-colors group">
                            {{-- CLAVE --}}
                            <td class="py-3 px-4 text-sm font-mono font-medium text-zinc-600 bg-zinc-50/50">
                                {{ $fila['clave'] }}
                            </td>
                            
                            {{-- NOMBRE --}}
                            <td class="py-3 px-4 text-sm font-bold text-zinc-800 border-r border-zinc-100">
                                {{ $fila['nombre'] }}
                            </td>
                            
                            {{-- VEHÍCULOS --}}
                            <td class="py-3 px-4 text-center text-sm text-zinc-600">
                                {{ $fila['total_vehiculos'] }}
                            </td>
                            
                            {{-- EN TALLER --}}
                            <td class="py-3 px-4 text-center">
                                @if($fila['en_taller'] > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-200">
                                        {{ $fila['en_taller'] }}
                                    </span>
                                @else
                                    <span class="text-zinc-300 text-sm">-</span>
                                @endif
                            </td>
                            
                            {{-- PENDIENTES (DÍAS FALTANTES) --}}
                            <td class="py-3 px-4 text-center">
                                @if($fila['pendientes'] > 0)
                                    <div class="flex flex-col items-center">
                                        <span class="text-red-600 font-bold text-sm">{{ $fila['pendientes'] }}</span>
                                        <span class="text-[10px] text-red-400">Días sin revisión</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-600">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Al día
                                    </span>
                                @endif
                            </td>
                            
                            {{-- CUMPLIMIENTO --}}
                            <td class="py-3 px-4">
                                <div class="flex flex-col items-center justify-center w-32 mx-auto">
                                    <div class="flex justify-between w-full text-xs mb-1">
                                        <span class="font-bold text-zinc-700">{{ $fila['cumplidos'] }} <span class="text-zinc-400 font-normal">regs.</span></span>
                                        <span class="font-bold {{ $fila['porcentaje'] == 100 ? 'text-emerald-600' : 'text-zinc-600' }}">{{ $fila['porcentaje'] }}%</span>
                                    </div>
                                    <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden border border-gray-100">
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
         {{-- INFO BOX --}}
        <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 flex justify-center items-start gap-3 mb-4 mx-auto">
            <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div class="text-sm text-blue-800">
                <p class="font-bold">¿Cómo se calcula?</p>
                <p>El porcentaje representa los días cumplidos vs. días transcurridos del mes. Los vehículos en taller se excluyen del cálculo.</p>
            </div>
        </div>
    </div>
</div>
@endsection