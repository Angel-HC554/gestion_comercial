@extends('layouts.app-layout', [
    'title' => 'Vehículos y taller',
])

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <div class="min-h-screen p-6 md:p-10 space-y-8" x-data="{
        activeTab: new URLSearchParams(window.location.search).get('tab') || 'taller',
        init() {
            if (new URLSearchParams(window.location.search).has('tab')) {
                setTimeout(() => {
                    document.getElementById('seccion-tablas').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 300);
            }
        }
    }">

        {{-- CABECERA Y FILTRO --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <p class="text-sm text-zinc-500 uppercase tracking-wider mb-1">Panel administrativo</p>
                <h1 class="text-3xl md:text-4xl font-bold text-zinc-900">Vehículos y Taller</h1>
                <p class="text-zinc-500 text-sm mt-1">Monitorea el estado de tu flota, siniestros y órdenes activas.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-md border border-zinc-200 shadow-sm">
                    <label for="proceso_filter" class="text-sm font-semibold text-zinc-600">Proceso:</label>
                    <select id="proceso_filter" onchange="window.location.href='?proceso=' + this.value"
                        class="border-none bg-transparent text-sm font-medium text-zinc-900 focus:ring-0 cursor-pointer outline-none">
                        <option value="">Todos los procesos</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->nombre }}" {{ $procesoFiltro === $area->nombre ? 'selected' : '' }}>
                                {{ $area->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <a href="/vehiculos"
                    class="inline-flex items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-md shadow-lg shadow-emerald-400/30 text-sm font-semibold hover:bg-emerald-500 transition-colors">
                    Ver vehículos
                </a>
            </div>
        </div>

        {{-- TARJETAS KPI --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 space-y-3 cursor-pointer hover:border-emerald-400 transition-colors"
                @click="activeTab = 'taller'">
                <div class="text-xs uppercase tracking-wider text-zinc-500 font-semibold">Vehículos en taller</div>
                <div class="text-3xl font-bold text-zinc-900">{{ $vehiculosEnTallerCount }}</div>
                <p class="text-sm text-zinc-500 leading-relaxed">
                    @if ($vehiculosEnTallerCount)
                        Desde <span
                            class="font-semibold text-zinc-900">{{ \Carbon\Carbon::parse($vehiculosEnTaller->first()['fecha_ingreso'])->translatedFormat('d M') }}</span>
                    @else
                        Ninguno en taller.
                    @endif
                </p>
            </div>
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 space-y-3 cursor-pointer hover:border-amber-400 transition-colors"
                @click="activeTab = 'mantenimiento'">
                <div class="text-xs uppercase tracking-wider text-zinc-500 font-semibold">Próximos a mantenimiento</div>
                <div class="text-3xl font-bold text-zinc-900">{{ $proximosMantenimientoCount }}</div>
                <p class="text-sm text-zinc-500">Atención a <strong class="text-amber-600">amarillos</strong> o <strong
                        class="text-red-600">rojos</strong>.</p>
            </div>
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 space-y-3 cursor-pointer hover:border-red-400 transition-colors"
                @click="activeTab = 'siniestros'">
                <div class="text-xs uppercase tracking-wider text-zinc-500 font-semibold">Vehículos con siniestros</div>
                <div class="text-3xl font-bold text-zinc-900">{{ $siniestrosCount }}</div>
                <p class="text-sm text-zinc-500">Reportes diarios y atentados semanales.</p>
            </div>
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 space-y-3 cursor-pointer hover:border-purple-400 transition-colors"
                @click="activeTab = 'ordenes500'">
                <div class="text-xs uppercase tracking-wider text-zinc-500 font-semibold">Órdenes 500 activas</div>
                <div class="text-3xl font-bold text-zinc-900">{{ $ordenes500Count }}</div>
                <p class="text-sm text-zinc-500">Documentos 500 registrados.</p>
            </div>
        </div>

        {{-- GRÁFICAS --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-sm font-semibold text-zinc-500 uppercase tracking-wider">Distribución por status
                            </p>
                            <h3 class="text-xl font-bold text-zinc-900">Órdenes de mantenimiento</h3>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">Última
                            semana</span>
                    </div>
                    <div id="chart-status" class="h-72"></div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-sm font-semibold text-zinc-500 uppercase tracking-wider">Reingresos más
                                frecuentes</p>
                            <h3 class="text-xl font-bold text-zinc-900">Top vehículos en taller</h3>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold">Top 6</span>
                    </div>
                    <div id="chart-top" class="h-72"></div>
                </div>
            </div>
        </div>

        {{-- MASTER TABLE (CON PESTAÑAS) --}}
        <div id="seccion-tablas" class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden mt-8">
            {{-- Tabs Header --}}
            <div class="flex overflow-x-auto border-b border-zinc-200 bg-zinc-50">
                <button @click="activeTab = 'taller'"
                    :class="activeTab === 'taller' ? 'border-b-2 border-emerald-600 text-emerald-700 bg-white' :
                        'text-zinc-500 hover:text-zinc-700 hover:bg-zinc-100'"
                    class="px-6 py-4 text-sm font-bold uppercase tracking-wider transition-colors whitespace-nowrap outline-none">
                    En Taller ({{ $vehiculosEnTallerCount }})
                </button>
                <button @click="activeTab = 'mantenimiento'"
                    :class="activeTab === 'mantenimiento' ? 'border-b-2 border-amber-500 text-amber-700 bg-white' :
                        'text-zinc-500 hover:text-zinc-700 hover:bg-zinc-100'"
                    class="px-6 py-4 text-sm font-bold uppercase tracking-wider transition-colors whitespace-nowrap outline-none">
                    Mantenimientos Próximos ({{ $proximosMantenimientoCount }})
                </button>
                <button @click="activeTab = 'siniestros'"
                    :class="activeTab === 'siniestros' ? 'border-b-2 border-red-500 text-red-700 bg-white' :
                        'text-zinc-500 hover:text-zinc-700 hover:bg-zinc-100'"
                    class="px-6 py-4 text-sm font-bold uppercase tracking-wider transition-colors whitespace-nowrap outline-none">
                    Siniestros / Golpes ({{ $siniestros->count() }})
                </button>
                <button @click="activeTab = 'ordenes500'"
                    :class="activeTab === 'ordenes500' ? 'border-b-2 border-purple-500 text-purple-700 bg-white' :
                        'text-zinc-500 hover:text-zinc-700 hover:bg-zinc-100'"
                    class="px-6 py-4 text-sm font-bold uppercase tracking-wider transition-colors whitespace-nowrap outline-none">
                    Órdenes 500 ({{ $ordenes500Count }})
                </button>
            </div>

            {{-- Contenido de las Tabs --}}
            <div class="p-0">

                {{-- TAB: EN TALLER --}}
                <div x-show="activeTab === 'taller'" x-cloak class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-zinc-50 text-zinc-500 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-4 font-bold">Vehículo</th>
                                <th class="px-6 py-4 font-bold">Proceso / RPE</th>
                                <th class="px-6 py-4 font-bold">Taller</th>
                                <th class="px-6 py-4 font-bold">Días Ingresado</th>
                                <th class="px-6 py-4 font-bold">Motivo / Servicio</th>
                                <th class="px-6 py-4 text-right font-bold">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse($vehiculosEnTaller as $vehiculo)
                                <tr class="hover:bg-zinc-50">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-zinc-900">{{ $vehiculo['noeconomico'] }}</p>
                                        <p class="text-xs text-zinc-500">{{ $vehiculo['placas'] }} ·
                                            {{ $vehiculo['marca'] }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-zinc-700">{{ $vehiculo['departamento'] ?? 'N/A' }}</p>
                                        <p class="text-xs text-zinc-500">{{ $vehiculo['rpe'] ?? 'Sin RPE' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-zinc-700">{{ $vehiculo['taller'] }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $vehiculo['dias_taller'] > 15 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                                {{ $vehiculo['dias_taller'] }} días
                                            </span>
                                            <span
                                                class="text-xs text-zinc-400">({{ \Carbon\Carbon::parse($vehiculo['fecha_ingreso'])->format('d/m/Y') }})</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-zinc-600 max-w-xs truncate"
                                        title="{{ $vehiculo['observacion'] }}">
                                        {{ $vehiculo['observacion'] ?: 'Sin observaciones' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if ($vehiculo['link'])
                                            <a href="{{ $vehiculo['link'] }}"
                                                class="text-emerald-600 font-bold hover:underline">Revisar</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-zinc-500">Ningún vehículo en
                                        taller en este proceso.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- TAB: MANTENIMIENTOS PRÓXIMOS --}}
                <div x-show="activeTab === 'mantenimiento'" x-cloak class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-zinc-50 text-zinc-500 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-4 font-bold">Vehículo</th>
                                <th class="px-6 py-4 font-bold">Proceso / RPE</th>
                                <th class="px-6 py-4 font-bold">Estatus</th>
                                <th class="px-6 py-4 font-bold">Motivo (KM / Tiempo)</th>
                                <th class="px-6 py-4 text-right font-bold">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse($proximosMantenimiento as $vehiculo)
                                <tr class="hover:bg-zinc-50">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-zinc-900">{{ $vehiculo['noeconomico'] }}</p>
                                        <p class="text-xs text-zinc-500">{{ $vehiculo['placas'] }} ·
                                            {{ $vehiculo['marca'] }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-zinc-700">{{ $vehiculo['departamento'] ?? 'N/A' }}</p>
                                        <p class="text-xs text-zinc-500">{{ $vehiculo['rpe'] ?? 'Sin RPE' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if (in_array($vehiculo['estado'], ['rojo', 'rojo_pasado']))
                                            <span
                                                class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-red-100 text-red-700 uppercase">Urgente
                                                / Vencido</span>
                                        @else
                                            <span
                                                class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-amber-100 text-amber-700 uppercase">Próximo</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-zinc-600">
                                        @if ($vehiculo['km_faltantes'] <= 0)
                                            <span class="text-red-600 font-semibold">Excedido por
                                                {{ number_format(abs($vehiculo['km_faltantes'])) }} km</span>
                                        @elseif($vehiculo['km_faltantes'] <= 2000)
                                            Faltan {{ number_format($vehiculo['km_faltantes']) }} km
                                        @endif

                                        @if ($vehiculo['dias_restantes'] !== null)
                                            @if ($vehiculo['km_faltantes'] <= 2000)
                                                <br> <span class="text-xs text-zinc-400">o</span> <br>
                                            @endif
                                            @if ($vehiculo['dias_restantes'] < 0)
                                                <span class="text-red-600 font-semibold">Vencido hace
                                                    {{ abs($vehiculo['dias_restantes']) }} días</span>
                                            @elseif($vehiculo['dias_restantes'] <= 30)
                                                Faltan {{ $vehiculo['dias_restantes'] }} días
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ $vehiculo['link'] }}"
                                            class="text-amber-600 font-bold hover:underline">Agendar</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-zinc-500">Ningún vehículo
                                        requiere atención en este proceso.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- TAB: SINIESTROS --}}
                <div x-show="activeTab === 'siniestros'" x-cloak class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-zinc-50 text-zinc-500 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-4 font-bold">Fecha / Origen</th>
                                <th class="px-6 py-4 font-bold">Vehículo</th>
                                <th class="px-6 py-4 font-bold">Proceso</th>
                                <th class="px-6 py-4 font-bold">Detalles / Comentarios</th>
                                <th class="px-6 py-4 text-right font-bold">Evidencia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse($siniestros as $siniestro)
                                <tr class="hover:bg-zinc-50">
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-zinc-900">
                                            {{ \Carbon\Carbon::parse($siniestro->fecha)->translatedFormat('d M Y') }}</p>
                                        <p
                                            class="text-xs font-semibold {{ str_contains($siniestro->tipo, 'Semanal') ? 'text-purple-600' : 'text-blue-600' }}">
                                            {{ $siniestro->tipo }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-zinc-900">{{ $siniestro->no_eco }}</p>
                                        <p class="text-xs text-zinc-500">{{ $siniestro->vehiculo?->placas ?? '' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-zinc-700">
                                            {{ $siniestro->vehiculo?->departamento ?? 'N/A' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-zinc-700 max-w-sm">{{ $siniestro->detalles }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex flex-col items-end gap-1">
                                            @if ($siniestro->link_evidencia)
                                                <a href="{{ $siniestro->link_evidencia }}" target="_blank"
                                                    class="text-xs bg-zinc-100 border border-zinc-300 px-2 py-1 rounded text-zinc-700 font-bold hover:bg-zinc-200">Ver
                                                    Foto</a>
                                            @else
                                                <span class="text-xs text-zinc-400">Sin foto</span>
                                            @endif

                                            @if ($siniestro->vehiculo)
                                                <a href="/vehiculos/{{ $siniestro->vehiculo->id }}"
                                                    class="text-red-600 font-bold text-xs hover:underline mt-1">Ir a
                                                    vehículo</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-zinc-500">No hay siniestros
                                        reportados en este proceso.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- TAB: ORDENES 500 --}}
                <div x-show="activeTab === 'ordenes500'" x-cloak class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-zinc-50 text-zinc-500 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-4 font-bold">Código 500</th>
                                <th class="px-6 py-4 font-bold">Fecha Orden</th>
                                <th class="px-6 py-4 font-bold">Vehículo</th>
                                <th class="px-6 py-4 font-bold">Proceso</th>
                                <th class="px-6 py-4 font-bold">Estatus</th>
                                <th class="px-6 py-4 text-right font-bold">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse($ordenes500 as $orden)
                                <tr class="hover:bg-zinc-50">
                                    <td class="px-6 py-4">
                                        <span class="font-black text-purple-700 text-lg">{{ $orden->orden_500 }}</span>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-zinc-700">
                                        {{ $orden->created_at->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-zinc-900">{{ $orden->noeconomico }}</p>
                                        <p class="text-xs text-zinc-500">{{ $orden->marca }} · {{ $orden->placas }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-zinc-700">
                                            {{ $orden->vehiculo?->departamento ?? 'N/A' }}</p>
                                        <p class="text-xs text-zinc-500">{{ $orden->vehiculo?->rpe_responsable ?? '' }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($orden->status === 'TERMINADO')
                                            <span
                                                class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">{{ $orden->status }}</span>
                                        @else
                                            <span
                                                class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">{{ $orden->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if ($orden->vehiculo)
                                            <a href="/vehiculos/{{ $orden->vehiculo->id }}"
                                                class="text-purple-600 font-bold hover:underline">Ver</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-zinc-500">No hay órdenes 500
                                        registradas en este proceso.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Grafica Status
            const statusChart = {
                series: {!! $statusValues !!},
                chart: {
                    type: 'donut',
                    width: '100%',
                    fontFamily: 'inherit'
                },
                labels: {!! $statusLabels !!},
                colors: ['#f97316', '#14b8a6', '#9333ea', '#0ea5e9', '#facc15'],
                legend: {
                    position: 'bottom'
                },
                dataLabels: {
                    enabled: false
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: {
                            width: '100%'
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };
            new ApexCharts(document.querySelector("#chart-status"), statusChart).render();

            // Grafica Reingresos
            const topChart = {
                series: [{
                    name: 'Órdenes',
                    data: {!! $chartTopValues !!}
                }],
                chart: {
                    type: 'bar',
                    height: 250,
                    fontFamily: 'inherit'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 8,
                        columnWidth: '60%'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: {!! $chartTopLabels !!},
                    labels: {
                        style: {
                            fontSize: '12px'
                        }
                    }
                },
                colors: ['#9333ea'],
                grid: {
                    borderColor: '#f3f4f6'
                }
            };
            new ApexCharts(document.querySelector("#chart-top"), topChart).render();
        });
    </script>
@endsection
