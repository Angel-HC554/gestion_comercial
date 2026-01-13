@extends('layouts.app-layout', ['title' => 'Análisis de Mantenimiento'])

@section('content')


<div class="min-h-screen bg-gray-50 pb-10" 
     x-data="dashboardOrdenes({{ json_encode($chartData) }})">

    <div class="bg-white border-b border-gray-200 px-8 py-6 mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Análisis de Mantenimiento</h1>
                <p class="text-slate-500">Vehículo: <span class="font-mono font-bold text-slate-700">{{ $vehiculo->no_economico }}</span> | {{ $vehiculo->marca }} {{ $vehiculo->modelo }}</p>
            </div>
            <a href="/vehiculos/{{ $vehiculo->id }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                &larr; Volver al detalle
            </a>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                Evolución de Kilometraje e Incidentes
            </h3>
            
            <div class="relative h-80 w-full">
                <canvas id="kmChart"></canvas>
            </div>
            
            <div class="mt-4 flex gap-4 text-xs text-gray-500 justify-center">
                <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Mantenimiento Preventivo/Correctivo</div>
                <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-red-500"></span> Hojalatería / Golpe</div>
            </div>
        </div>

        <div x-show="selectedPoint" x-transition 
             class="bg-white rounded-xl shadow-lg border-l-4 border-emerald-500 p-6 relative overflow-hidden">
            
            <div class="absolute top-0 right-0 p-4">
                <button @click="selectedPoint = null" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-1 border-r border-gray-100 pr-4">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Fecha de Orden</p>
                    <p class="text-xl font-bold text-slate-800 mb-4" x-text="formatDate(selectedPoint?.fecha)"></p>
                    
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Kilometraje</p>
                    <p class="text-lg font-mono text-slate-700 mb-4" x-text="formatNumber(selectedPoint?.km) + ' km'"></p>

                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Taller</p>
                    <p class="text-sm text-slate-700 font-medium" x-text="selectedPoint?.taller || 'No especificado'"></p>
                </div>

                <div class="md:col-span-2">
                    <h4 class="text-sm font-bold text-slate-800 mb-3">Trabajos Realizados:</h4>
                    
                    <div class="flex flex-wrap gap-2 mb-4">
                        <template x-for="trabajo in selectedPoint?.reparaciones">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold border"
                                  :class="trabajo.includes('Hojalatería') ? 'bg-red-50 text-red-700 border-red-200' : 'bg-blue-50 text-blue-700 border-blue-200'"
                                  x-text="trabajo">
                            </span>
                        </template>
                        <template x-if="!selectedPoint?.reparaciones.length">
                            <span class="text-gray-400 text-sm italic">Sin trabajos específicos marcados.</span>
                        </template>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <p class="text-xs font-bold text-gray-500 mb-1">Observaciones:</p>
                        <p class="text-sm text-gray-700 italic" x-text="selectedPoint?.observacion || 'Sin observaciones'"></p>
                    </div>

                    <div class="mt-4 text-right">
                        <a :href="'/ordenvehiculos/' + selectedPoint?.id + '/edit'" target="_blank"
                           class="inline-flex items-center text-sm text-emerald-600 hover:text-emerald-700 font-semibold hover:underline">
                            Ver Documento Completo &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-bold text-slate-800">Historial de Órdenes</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taller</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kilometraje</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($ordenes as $orden)
                            @php
                                $esHojalateria = !empty($orden->vehicle10) && $orden->vehicle10 != '0';
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors cursor-pointer" 
                                @click="selectPointById({{ $orden->id }})">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ \Carbon\Carbon::parse($orden->fechafirm)->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ mb_strimwidth($orden->taller, 0, 20, '...') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-800">
                                    {{ number_format((int)str_replace(',', '', $orden->kilometraje)) }} km
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($esHojalateria)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Hojalatería
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Mantenimiento
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="#" class="text-emerald-600 hover:text-emerald-900">Ver</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
    function dashboardOrdenes(data) {
        return {
            chartData: data,
            selectedPoint: null,
            chartInstance: null,

            init() {
                this.renderChart();
            },

            formatDate(dateString) {
                if(!dateString) return '';
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                return new Date(dateString + 'T00:00:00').toLocaleDateString('es-ES', options);
            },

            formatNumber(num) {
                return new Intl.NumberFormat('es-MX').format(num || 0);
            },

            selectPointById(id) {
                const found = this.chartData.find(p => p.id === id);
                if(found) {
                    this.selectedPoint = found;
                    // Scroll suave hacia el detalle
                    window.scrollTo({ top: 300, behavior: 'smooth' });
                }
            },

            renderChart() {
                const ctx = document.getElementById('kmChart').getContext('2d');
                
                // Preparamos los datos para Chart.js
                const labels = this.chartData.map(d => d.fecha); // Fechas en eje X
                const kms = this.chartData.map(d => d.km);       // KMs en eje Y
                
                // Colores condicionales: Rojo si es golpe, Emerald si es normal
                const pointColors = this.chartData.map(d => d.es_golpe ? '#EF4444' : '#10B981');
                const pointRadiuses = this.chartData.map(d => d.es_golpe ? 8 : 5); // Puntos de golpe más grandes
                const pointStyles = this.chartData.map(d => d.es_golpe ? 'rectRot' : 'circle'); // Diamante para golpes

                this.chartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Kilometraje',
                            data: kms,
                            borderColor: '#34D399', // Color de la línea (Emerald-400)
                            backgroundColor: 'rgba(52, 211, 153, 0.1)',
                            borderWidth: 2,
                            tension: 0.3, // Curvatura suave
                            fill: true,
                            pointBackgroundColor: pointColors,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: pointRadiuses,
                            pointHoverRadius: 10,
                            pointStyle: pointStyles
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.y.toLocaleString() + ' km';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false }
                            },
                            y: {
                                beginAtZero: false, // El KM no empieza en 0 usualmente
                                grid: { borderDash: [5, 5] }
                            }
                        },
                        onClick: (evt, activeElements) => {
                            if (activeElements.length > 0) {
                                const index = activeElements[0].index;
                                const pointData = this.chartData[index];
                                
                                // Actualizamos variable Reactiva de Alpine
                                this.selectedPoint = pointData;
                            }
                        }
                    }
                });
            }
        }
    }
</script>
@endsection