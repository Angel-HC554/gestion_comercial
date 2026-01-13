<div class="w-full">
    {{-- 1. HEADER SUPERIOR (Diseño Unificado) --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
        <div>
            <h2 class="text-2xl font-bold text-zinc-900">Resumen Semanal por Agencias</h2>
            <p class="text-sm text-zinc-500">
                Periodo: <span class="font-semibold text-emerald-600 capitalize">{{ $nombreMes }}</span>
            </p>
        </div>

        <a href="/supervision-semanal">
        <button class="w-full md:w-auto h-10 px-6 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-md transition-colors shadow-sm flex items-center justify-center gap-2 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver al listado
        </button>   
    </a>
    </div>
    
</div>

{{-- CONTROLES DE NAVEGACIÓN SEMANAL --}}
<div class="bg-white rounded-lg border-t-4 border-t-emerald-600 shadow-sm p-4 border border-zinc-200 mb-4 flex flex-col sm:flex-row justify-between items-center gap-4">
    
    {{-- Botón Anterior --}}
    <button 
        @if($semanaIndex > 0)
            hx-get="/supervision-semanal/resumen-agencias?semana_index={{ $semanaIndex - 1 }}&mes={{ $mes }}&año={{ $año }}"
            hx-target="#contenedor-principal"
            hx-swap="innerHTML"
            class="flex items-center gap-2 px-4 py-2 bg-zinc-100 border border-zinc-300 text-zinc-700 rounded-md hover:bg-zinc-200 font-medium transition-colors cursor-pointer"
        @else
            disabled
            class="flex items-center gap-2 px-4 py-2 bg-gray-100 border border-gray-200 text-gray-400 rounded-md cursor-not-allowed font-medium"
        @endif
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Semana Anterior
    </button>

    {{-- Título Central --}}
    <div class="text-center">
        <h3 class="text-lg font-bold text-emerald-900">Semana {{ $numeroSemana }}</h3>
        <span class="text-sm text-emerald-700 bg-emerald-200/50 px-3 py-0.5 rounded-full font-medium uppercase">
            {{ $rangoSemana }}
        </span>
    </div>

    {{-- Botón Siguiente --}}
    <button 
        @if($semanaIndex < $totalSemanas - 1)
            hx-get="/supervision-semanal/resumen-agencias?semana_index={{ $semanaIndex + 1 }}&mes={{ $mes }}&año={{ $año }}"
            hx-target="#contenedor-principal"
            hx-swap="innerHTML"
            class="flex items-center gap-2 px-4 py-2 bg-zinc-100 border border-zinc-300 text-zinc-700 rounded-md hover:bg-zinc-200 font-medium transition-colors cursor-pointer"
        @else
            disabled
            class="flex items-center gap-2 px-4 py-2 bg-gray-100 border border-gray-200 text-gray-400 rounded-md cursor-not-allowed font-medium"
        @endif
    >
        Semana Siguiente
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
    </button>
</div>
    </div>


    {{-- TABLA DE DATOS --}}
    <div class="tabla-scrollable bg-white rounded-lg shadow-md border border-zinc-200 overflow-hidden animate-in fade-in zoom-in duration-300">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-zinc-50 border-b border-zinc-200">
                    <th class="py-3 px-4 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider">Clave</th>
                    <th class="py-3 px-4 text-left text-xs font-bold text-zinc-500 uppercase tracking-wider border-r border-zinc-200">Nombre</th>
                    <th class="py-3 px-4 text-center text-xs font-bold text-zinc-500 uppercase tracking-wider">Vehículos</th>
                    <th class="py-3 px-4 text-center text-xs font-bold text-zinc-500 uppercase tracking-wider">En Taller</th>
                    <th class="py-3 px-4 text-center text-xs font-bold text-zinc-500 uppercase tracking-wider">Pendientes</th>
                    <th class="py-3 px-4 text-center text-xs font-bold text-emerald-600 uppercase tracking-wider">Cumplimiento</th>
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
                        
                        {{-- PENDIENTES --}}
                        <td class="py-3 px-4 text-center">
                            @if($fila['pendientes'] > 0)
                                <div class="flex flex-col items-center">
                                    <span class="text-red-600 font-bold text-sm">{{ $fila['pendientes'] }}</span>
                                    <span class="text-[10px] text-red-400">Faltantes</span>
                                </div>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-600">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Completo
                                </span>
                            @endif
                        </td>
                        
                        {{-- CUMPLIMIENTO --}}
                        <td class="py-3 px-4">
                            <div class="flex flex-col items-center justify-center w-32 mx-auto">
                                <div class="flex justify-between w-full text-xs mb-1">
                                    <span class="font-bold text-zinc-700">{{ $fila['cumplidos'] }} <span class="text-zinc-400 font-normal">hechos</span></span>
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
    {{-- 2. INFO BOX (Explicación del cálculo) --}}
    <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 flex items-start gap-3 mb-4 mx-auto justify-center">
        <svg class="w-5 h-5 text-blue-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="text-sm text-blue-800">
            <p class="font-bold">¿Cómo se calcula?</p>
            <p>El porcentaje se basa en los vehículos <strong>activos</strong>. Los vehículos en <strong>taller</strong> se restan del total y no afectan tu cumplimiento.</p>
        </div>
    </div>
</div>