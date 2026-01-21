{{-- Cargamos ChartJS solo si no se ha cargado antes --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div class="bg-gray-50 rounded-xl p-4 sm:p-6" x-data="dashboardOrdenes({{ json_encode($chartData) }})">

    {{-- Encabezado de la sección --}}
    <div class="mb-6">
        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            <img src="/assets/img/car_gear.svg" alt="auto" class="w-7 h-7">
            Evolución de Kilometraje e Incidentes
        </h3>
    </div>

    {{-- Contenedor de la Gráfica --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <div class="relative h-80 w-full">
            <canvas id="kmChart"></canvas>
        </div>
        
        <div class="mt-4 flex gap-4 text-xs text-gray-500 justify-center">
            <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Mantenimiento Preventivo</div>
            <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-red-500"></span> Hojalatería / Golpe</div>
        </div>
    </div>

    {{-- Tarjeta de Detalles (Aparece al hacer click) --}}
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

                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-2">
                        <template x-if="selectedPoint?.servicio">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-yellow-200 text-black">
                                La orden se realizó por servicio km / tiempo
                            </span>
                        </template>
                    </div>
                    <div class="flex flex-col items-end gap-2 sm:flex-row sm:items-center sm:gap-4">
                        <template x-if="selectedPoint?.archivo?.ruta_archivo">
                            <a :href="selectedPoint.archivo.ruta_archivo" target="_blank"
                                class="inline-flex items-center text-sm text-emerald-600 hover:text-emerald-700 font-semibold hover:underline">
                                Ver Documento &rarr;
                            </a>
                        </template>
                        <template x-if="!selectedPoint?.archivo?.ruta_archivo">
                            <span class="text-sm text-gray-400 italic cursor-not-allowed">
                                Sin documento escaneado
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Verificamos si la función ya existe para no re-declararla si usas navegación tipo SPA/Turbo
    if (typeof dashboardOrdenes !== 'function') {
        function dashboardOrdenes(data) {
            return {
                chartData: data,
                selectedPoint: null,
                chartInstance: null,

                init() {
                    // Timeout pequeño para asegurar que el DOM (canvas) esté listo dentro del tab
                    setTimeout(() => {
                        this.renderChart();
                    }, 100);
                },

                formatDate(dateString) {
                    if(!dateString) return '';
                    // Truco para evitar problemas de zona horaria al parsear YYYY-MM-DD
                    const date = new Date(dateString + 'T00:00:00'); 
                    return date.toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' });
                },

                formatNumber(num) {
                    return new Intl.NumberFormat('es-MX').format(num || 0);
                },

                renderChart() {
                    const canvas = document.getElementById('kmChart');
                    if (!canvas) return; // Si cambiamos de tab muy rápido y no existe el canvas

                    const ctx = canvas.getContext('2d');
                    
                    const labels = this.chartData.map(d => d.fecha);
                    const kms = this.chartData.map(d => d.km);       
                    
                    const pointColors = this.chartData.map(d => d.es_golpe ? '#EF4444' : '#10B981');
                    const pointRadiuses = this.chartData.map(d => d.es_golpe ? 8 : 5);
                    const pointStyles = this.chartData.map(d => d.es_golpe ? 'rectRot' : 'circle');

                    // Destruir instancia anterior si existe para evitar superposiciones
                    if (this.chartInstance) {
                        this.chartInstance.destroy();
                    }

                    this.chartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Kilometraje',
                                data: kms,
                                borderColor: '#34D399', 
                                backgroundColor: 'rgba(52, 211, 153, 0.1)',
                                borderWidth: 2,
                                tension: 0.3,
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
                                    grid: { display: false },
                                    ticks: { display: false } // Ocultamos fechas para limpieza visual
                                },
                                y: {
                                    beginAtZero: false,
                                    grid: { borderDash: [5, 5] }
                                }
                            },
                            onClick: (evt, activeElements) => {
                                if (activeElements.length > 0) {
                                    const index = activeElements[0].index;
                                    // Actualizamos Alpine
                                    this.selectedPoint = this.chartData[index];
                                }
                            }
                        }
                    });
                }
            }
        }
    }
</script>