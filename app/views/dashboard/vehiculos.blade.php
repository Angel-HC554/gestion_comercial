@extends('layouts.app-layout', [
    'title' => 'Vehículos y taller'
])

@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="min-h-screen bg-zinc-50 p-6 md:p-10 space-y-8">

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <p class="text-sm text-zinc-500 uppercase tracking-wider mb-1">Panel administrativo</p>
            <h1 class="text-3xl md:text-4xl font-bold text-zinc-900">Vehículos y Taller</h1>
            <p class="text-zinc-500 text-sm mt-1">Monitorea el estado de tu flota en mantenimiento, siniestros y órdenes 500.</p>
        </div>
        <a href="/vehiculos"
            class="inline-flex items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-xl shadow-lg shadow-emerald-400/30 text-sm font-semibold hover:bg-emerald-500 transition-colors">
            Ver vehiculos
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 space-y-3">
            <div class="text-xs uppercase tracking-wider text-zinc-500 font-semibold">Vehículos en taller</div>
            <div class="text-3xl font-bold text-zinc-900">{{ $vehiculosEnTallerCount }}</div>
            <p class="text-sm text-zinc-500 leading-relaxed">@if($vehiculosEnTallerCount) Desde <span class="font-semibold text-zinc-900">{{ $vehiculosEnTaller->first()['fecha_ingreso_programado'] ?? $vehiculosEnTaller->first()['fecha_ingreso'] ?? 'Sin fecha' }}</span>@else Ninguno en taller.@endif</p>
        </div>
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 space-y-3">
            <div class="text-xs uppercase tracking-wider text-zinc-500 font-semibold">Próximos a mantenimiento</div>
            <div class="text-3xl font-bold text-zinc-900">{{ $proximosMantenimientoCount }}</div>
            <p class="text-sm text-zinc-500">Atención a vehículos en estado <strong class="text-amber-600">amarillo</strong> o <strong class="text-red-600">rojo</strong>.</p>
        </div>
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 space-y-3">
            <div class="text-xs uppercase tracking-wider text-zinc-500 font-semibold">Vehículos con siniestros</div>
            <div class="text-3xl font-bold text-zinc-900">{{ $siniestrosCount }}</div>
            <p class="text-sm text-zinc-500">Basado en los últimos reportes diarios que marcaron golpes.</p>
        </div>
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm p-5 space-y-3">
            <div class="text-xs uppercase tracking-wider text-zinc-500 font-semibold">Órdenes 500 activas</div>
            <div class="text-3xl font-bold text-zinc-900">{{ $ordenes500Count }}</div>
            <p class="text-sm text-zinc-500">Ordenes con 500.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm font-semibold text-zinc-500 uppercase tracking-wider">Distribución por status</p>
                        <h3 class="text-xl font-bold text-zinc-900">Órdenes de mantenimiento</h3>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">Última semana</span>
                </div>
                <div id="chart-status" class="h-72"></div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm font-semibold text-zinc-500 uppercase tracking-wider">Reingresos más frecuentes</p>
                        <h3 class="text-xl font-bold text-zinc-900">Vehículos que vuelven al taller</h3>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold">Top 6</span>
                </div>
                <div id="chart-top" class="h-72"></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-zinc-100 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wider text-zinc-500 font-semibold">Vehículos en taller</p>
                    <h3 class="text-xl font-bold text-zinc-900">Detalle</h3>
                </div>
                <span class="text-sm font-semibold text-emerald-600">{{ $vehiculosEnTallerCount }} activos</span>
            </div>
            @if($vehiculosEnTallerCount)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-zinc-50 text-zinc-500 uppercase text-xs">
                            <tr>
                                <th class="px-4 py-3">No. económico</th>
                                <th class="px-4 py-3">Marca / Placas</th>
                                <th class="px-4 py-3">Taller</th>
                                <th class="px-4 py-3">Desde</th>
                                <th class="px-4 py-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach($vehiculosEnTaller as $vehiculo)
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-zinc-900">{{ $vehiculo['noeconomico'] }}</td>
                                    <td class="px-4 py-3 text-zinc-500">{{ $vehiculo['marca'] }} · {{ $vehiculo['placas'] }}</td>
                                    <td class="px-4 py-3 text-zinc-500">{{ $vehiculo['taller'] ?? 'N/D' }}</td>
                                    <td class="px-4 py-3 text-zinc-500">{{ $vehiculo['fecha_ingreso']->format('d M Y') ?? ($vehiculo['fecha_ingreso'] ? \Carbon\Carbon::parse($vehiculo['fecha_ingreso'])->format('d M Y') : 'Sin fecha') }}</td>
                                    <td class="px-4 py-3">
                                        @if($vehiculo['link'])
                                            <a href="{{ $vehiculo['link'] }}" class="text-emerald-600 text-xs font-semibold hover:underline">Ver vehículo</a>
                                        @else
                                            <span class="text-zinc-400 text-xs">Sin registro</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-6 text-sm text-zinc-500 border-t border-zinc-100">
                    Ningún vehículo aparece como "VEHICULO TALLER" actualmente.
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-zinc-100 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wider text-zinc-500 font-semibold">Top frecuentes</p>
                    <h3 class="text-xl font-bold text-zinc-900">Más órdenes</h3>
                </div>
                <span class="text-sm font-semibold text-amber-600">{{ $vehiculosFrecuentes->count() }} vehículos</span>
            </div>
            <div class="space-y-4 p-6">
                @forelse($vehiculosFrecuentes as $vehiculo)
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-zinc-900">{{ $vehiculo['noeconomico'] }}</p>
                            <p class="text-xs text-zinc-500">{{ $vehiculo['marca'] }} · {{ $vehiculo['placas'] }}</p>
                            <p class="text-xs text-zinc-400">Primera orden: {{ \Carbon\Carbon::parse($vehiculo['primera_fecha'])->format('d M Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-zinc-900">{{ $vehiculo['veces'] }}</p>
                            <p class="text-xs text-zinc-500">veces</p>
                            @if($vehiculo['link'])
                                <a href="{{ $vehiculo['link'] }}" class="text-emerald-600 text-xs font-semibold hover:underline">Ver</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500">No hay vehículos con múltiples órdenes aún.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-zinc-100 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wider text-zinc-500 font-semibold">Próximos mantenimientos</p>
                    <h3 class="text-xl font-bold text-zinc-900">A la vista</h3>
                </div>
                <span class="text-sm font-semibold text-emerald-600">{{ $proximosMantenimientoCount }} vehículos</span>
            </div>
            @if($proximosMantenimientoCount)
                <div class="divide-y divide-zinc-100">
                    @foreach($proximosMantenimiento as $vehiculo)
                        <div class="px-6 py-4 flex justify-between items-center">
                            <div>
                                <p class="text-sm font-semibold text-zinc-900">{{ $vehiculo['noeconomico'] }}</p>
                                <p class="text-xs text-zinc-500">{{ $vehiculo['marca'] }} · {{ $vehiculo['modelo'] }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-zinc-500 uppercase">{{ $vehiculo['estado'] }}</p>
                                <a href="{{ $vehiculo['link'] }}" class="text-emerald-600 text-xs font-semibold hover:underline">Revisar</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 text-sm text-zinc-500">
                    Ningún vehículo requiere atención inmediata.
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-zinc-100 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wider text-zinc-500 font-semibold">Siniestros</p>
                    <h3 class="text-xl font-bold text-zinc-900">Golpes reportados</h3>
                </div>
                <span class="text-sm font-semibold text-red-600">{{ $siniestrosCount }} reportes</span>
            </div>
            @if($siniestros->count())
                <div class="divide-y divide-zinc-100">
                    @foreach($siniestros as $siniestro)
                        @php
                            $vehiculo = $siniestro->vehiculo;
                        @endphp
                        <div class="px-6 py-4 flex justify-between items-center gap-4">
                            <div>
                                <p class="text-sm font-semibold text-zinc-900">{{ $siniestro->no_eco }}</p>
                                <p class="text-xs text-zinc-500">{{ $siniestro->golpes_coment ?? 'Siniestro capturado' }}</p>
                                <p class="text-xs text-zinc-400">{{ $siniestro->fecha->format('d M Y') }}</p>
                            </div>
                            <div class="text-right">
                                @if($vehiculo)
                                    <a href="/vehiculos/{{ $vehiculo->id }}" class="text-emerald-600 text-xs font-semibold hover:underline">Ver vehículo</a>
                                @else
                                    <span class="text-xs text-zinc-400">Sin vehículo registrado</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 text-sm text-zinc-500">
                    No hay siniestros reportados recientemente.
                </div>
            @endif
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const statusChart = {
            series: {!! $statusValues !!},
            chart: {
                type: 'donut',
                width: '100%',
                fontFamily: 'inherit'
            },
            labels: {!! $statusLabels !!},
            colors: ['#f97316', '#14b8a6', '#9333ea', '#0ea5e9', '#facc15'],
            legend: { position: 'bottom' },
            dataLabels: { enabled: false },
            responsive: [{
                breakpoint: 768,
                options: {
                    chart: { width: '100%' },
                    legend: { position: 'bottom' }
                }
            }]
        };
        new ApexCharts(document.querySelector("#chart-status"), statusChart).render();

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
            dataLabels: { enabled: false },
            xaxis: {
                categories: {!! $chartTopLabels !!},
                labels: { style: { fontSize: '12px' } }
            },
            colors: ['#9333ea'],
            grid: { borderColor: '#f3f4f6' }
        };
        new ApexCharts(document.querySelector("#chart-top"), topChart).render();
    });
</script>
@endsection
