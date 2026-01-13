@extends('layouts.app-layout', [
    'title' => 'Supervisiones Diarias'
])

@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<div class="min-h-screen bg-gray-50 p-6 md:p-10">
    
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-zinc-900">Gestion diaria</h1>
            <p class="text-zinc-500 text-sm mt-1">Resumen supervisiones al día de hoy.</p>
        </div>
        <div class="text-right mt-4 md:mt-0">
            <span class="bg-white border border-zinc-200 text-zinc-700 px-4 py-2 rounded-lg text-sm font-medium shadow-sm uppercase">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d \d\e F Y') }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm relative overflow-hidden">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-zinc-500 uppercase">Supervisión Diaria</p>
                    <h3 class="text-3xl font-bold text-zinc-900 mt-2">{{ $supervisionesHoy }} / {{ $totalVehiculos }}</h3>
                </div>
                <div class="p-2 bg-emerald-100 rounded-lg text-emerald-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-zinc-500">Progreso</span>
                    <span class="font-bold text-zinc-700">{{ $avanceDiario }}%</span>
                </div>
                <div class="w-full bg-zinc-100 rounded-full h-2">
                    <div class="bg-emerald-500 h-2 rounded-full transition-all duration-1000" style="width: {{ $avanceDiario }}%"></div>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-zinc-500 uppercase">Vehículos con Daños (Hoy)</p>
                    <h3 class="text-3xl font-bold {{ $vehiculosConGolpes > 0 ? 'text-red-600' : 'text-zinc-900' }} mt-2">
                        {{ $vehiculosConGolpes }}
                    </h3>
                </div>
                <div class="p-2 {{ $vehiculosConGolpes > 0 ? 'bg-red-100 text-red-600' : 'bg-zinc-100 text-zinc-400' }} rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
            </div>
            <p class="text-xs text-zinc-500 mt-4">
                @if($vehiculosConGolpes > 0)
                    Requieren atención inmediata.
                @else
                    Sin novedades de daños hoy.
                @endif
            </p>
        </div>

        <div class="bg-emerald-900 p-6 rounded-xl border border-emerald-800 shadow-sm text-white flex flex-col justify-between">
            <div>
                <h3 class="text-lg font-bold">Gestión Rápida</h3>
                <p class="text-emerald-200 text-sm mt-1">Accede a las matrices detalladas.</p>
            </div>
            <div class="flex gap-2 mt-4">
                <a href="/supervision-diaria" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-center py-2 rounded-lg text-sm font-medium transition-colors">
                    Ver Diarias
                </a>
                <a href="/supervision-semanal" class="flex-1 bg-white/10 hover:bg-white/20 text-center py-2 rounded-lg text-sm font-medium transition-colors">
                    Ver Semanales
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
            <h4 class="text-lg font-bold text-zinc-800 mb-4">Supervisiones Últimos 7 Días</h4>
            <div id="chart-week" style="min-height: 300px;"></div>
        </div>

        <div class="bg-white p-6 rounded-xl border border-zinc-200 shadow-sm">
            <h4 class="text-lg font-bold text-zinc-800 mb-4">Niveles de Gasolina (Hoy)</h4>
            <div id="chart-gas" style="min-height: 300px;"></div>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // --- Gráfica 1: Historial ---
        const optionsWeek = {
            series: [{
                name: 'Revisiones',
                data: {!! $graficaValores !!} // Array de PHP
            }],
            chart: {
                type: 'area',
                height: 300,
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            colors: ['#10B981'], // Color Emerald
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.9,
                    stops: [0, 90, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: {
                categories: {!! $graficaDias !!}, // Array de fechas PHP
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            grid: {
                borderColor: '#f1f1f1',
            }
        };
        const chartWeek = new ApexCharts(document.querySelector("#chart-week"), optionsWeek);
        chartWeek.render();


        // --- Gráfica 2: Gasolina ---
        const optionsGas = {
            series: [{
                name: 'Vehículos',
                data: {!! $gasolinaData !!}
            }],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: false,
                    distributed: true // Colores diferentes por barra
                }
            },
            colors: ['#EF4444', '#F59E0B', '#3B82F6', '#10B981', '#065F46'], // Rojo, Amarillo, Azul, Verde, Verde Oscuro
            dataLabels: { enabled: true },
            xaxis: {
                categories: ['Reserva', '1/4', '1/2', '3/4', 'Lleno'],
            },
            legend: { show: false }
        };
        const chartGas = new ApexCharts(document.querySelector("#chart-gas"), optionsGas);
        chartGas.render();
    });
</script>
@endsection