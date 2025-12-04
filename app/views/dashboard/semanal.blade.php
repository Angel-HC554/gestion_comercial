@extends('layouts.app-layout')

@section('title', 'Dashboard Semanal')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="min-h-screen bg-gray-50 p-6 md:p-10">
    
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-3xl font-bold text-zinc-900">Gestión Semanal</h1>
                <span class="bg-purple-100 text-purple-700 text-xs font-bold px-2 py-1 rounded border border-purple-200 uppercase tracking-wide">Reporte Táctico</span>
            </div>
            <p class="text-zinc-500 text-sm mt-1">Avance de la semana: <strong>{{ $semanaLabel }}</strong></p>
        </div>
        
        <a href="/supervision-semanal" class="mt-4 md:mt-0 flex items-center gap-2 bg-white border border-zinc-300 text-zinc-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-zinc-50 hover:text-zinc-900 transition-all shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
            Ver Sábana de Datos
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <div class="bg-white p-6 rounded-xl border border-purple-100 shadow-sm relative overflow-hidden">
            <div class="absolute right-0 top-0 h-full w-1 bg-purple-500"></div>
            <p class="text-xs font-bold text-purple-400 uppercase tracking-wider mb-1">Cumplimiento Global</p>
            <div class="flex items-baseline gap-2">
                <h3 class="text-4xl font-bold text-zinc-900">{{ $porcentaje }}%</h3>
                <span class="text-sm text-zinc-500">de la flota</span>
            </div>
            <div class="w-full bg-zinc-100 rounded-full h-2 mt-4">
                <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $porcentaje }}%"></div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-1">Supervisiones Realizadas</p>
                <h3 class="text-3xl font-bold text-zinc-900">{{ $realizadas }}</h3>
                <p class="text-xs text-zinc-500 mt-1">Vehículos revisados esta semana</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-purple-50 flex items-center justify-center text-purple-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-1">Pendientes</p>
                <h3 class="text-3xl font-bold text-zinc-900">{{ $faltantes }}</h3>
                <p class="text-xs text-zinc-500 mt-1">Vehículos sin revisar</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-orange-50 flex items-center justify-center text-orange-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
            <div class="mb-6">
                <h3 class="text-lg font-bold text-zinc-900">Tendencia Histórica</h3>
                <p class="text-sm text-zinc-500">Supervisiones realizadas en las últimas 8 semanas.</p>
            </div>
            <div id="chart-history"></div>
        </div>

        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
            <div class="mb-6">
                <h3 class="text-lg font-bold text-zinc-900">Top Agencias Cumplidas</h3>
                <p class="text-sm text-zinc-500">Agencias con mayor actividad esta semana.</p>
            </div>
            @if(count(json_decode($agenciaValues)) > 0)
                <div id="chart-agencies"></div>
            @else
                <div class="flex flex-col items-center justify-center h-64 text-zinc-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    <p>No hay datos registrados esta semana.</p>
                </div>
            @endif
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // --- Gráfica 1: Barras Históricas ---
        const optionsHist = {
            series: [{
                name: 'Revisiones',
                data: {!! $historiaValues !!}
            }],
            chart: {
                type: 'bar',
                height: 320,
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            colors: ['#9333ea'], // Purple 600
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '50%',
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: {!! $historiaLabels !!},
                labels: { style: { fontSize: '12px' } }
            },
            grid: { borderColor: '#f3f4f6' }
        };
        new ApexCharts(document.querySelector("#chart-history"), optionsHist).render();

        // --- Gráfica 2: Donut Agencias ---
        // Solo renderizar si hay datos
        if (document.querySelector("#chart-agencies")) {
            const optionsAgencies = {
                series: {!! $agenciaValues !!},
                labels: {!! $agenciaLabels !!},
                chart: {
                    type: 'donut',
                    height: 320,
                    fontFamily: 'inherit'
                },
                colors: ['#7e22ce', '#a855f7', '#c084fc', '#d8b4fe', '#f3e8ff'], // Paleta morada
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                    }
                                }
                            }
                        }
                    }
                },
                legend: { position: 'bottom' }
            };
            new ApexCharts(document.querySelector("#chart-agencies"), optionsAgencies).render();
        }
    });
</script>
@endsection