@php
    $noEconomico = $noEconomico ?? null;
@endphp

<div class="mx-auto py-6" x-data="ordenesTable('{{ $noEconomico }}')" @open-finish-modal.window="openModal('status', $event.detail)" x-cloak>
    @csrf

    <div class="bg-white p-4 rounded-lg shadow-sm border border-zinc-300 border-t-emerald-600/40 border-t-4 mb-6 flex flex-wrap gap-4 items-end">
        <div class="flex flex-col">
            <label class="text-sm font-medium text-zinc-700 mb-1">Desde</label>
            <input type="date" x-model="filters.fecha_inicio" @blur="fetchData()" @change="fetchData()"
                class="border-2 border-zinc-300 rounded-md focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 text-sm h-9 p-2">
        </div>
        <div class="flex flex-col">
            <label class="text-sm font-medium text-zinc-700 mb-1">Hasta</label>
            <input type="date" x-model="filters.fecha_fin" @blur="fetchData()" @change="fetchData()"
                class="border-2 border-zinc-300 rounded-md focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 text-sm h-9 p-2">
        </div>
        <div class="flex flex-col">
            <label class="text-sm font-medium text-zinc-700 mb-1">Estado</label>
            <select x-model="filters.estado" @blur="fetchData()" @change="fetchData()"
                class="border-2 border-zinc-300 rounded-md focus:ring-emerald-600 focus:border-emerald-600 text-sm w-40 h-9 pr-2 cursor-pointer">
                <option value="">Todos</option>
                <option value="PENDIENTE">PENDIENTE</option>
                <option value="VEHICULO TALLER">VEHICULO TALLER</option>
                <option value="TERMINADO">TERMINADO</option>
            </select>
        </div>
        <button @click="resetFilters()"
            class="bg-zinc-100 hover:bg-zinc-200 text-zinc-700 px-4 py-2 rounded-md text-sm font-medium transition-colors ml-auto h-9 cursor-pointer border border-zinc-300">
            Borrar filtros
        </button>
    </div>

    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center gap-2 text-sm text-zinc-600">
            <span>Ver</span>
            <select x-model="perPage" @change="fetchData()"
                class="rounded-md text-sm py-1 pl-2 pr-8 focus:ring-emerald-500 focus:border-emerald-600 bg-white border-2 border-zinc-300 cursor-pointer">
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="15">15</option>
            </select>
        </div>
        <template x-if="!noEconomicoExact">
            <div class="relative">
                <input type="text" x-model.debounce.500ms="search" @input="fetchData()"
                    placeholder="No. económico..."
                    class="w-full pl-10 pr-4 py-2 bg-white border-2 border-zinc-300 rounded-md text-sm focus:outline-none focus:ring-emerald-600 focus:border-emerald-600">
                <svg class="h-5 w-5 text-zinc-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </template>

    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden border border-zinc-400">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-400 text-sm">
                <thead class="bg-emerald-600/70 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">No.
                            Orden
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">No.
                            Eco
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">
                            Agencia
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Fecha
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">500
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">
                            Opciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-zinc-300">
                    <template x-for="orden in ordenes" :key="orden.id">
                        <tr class="hover:bg-zinc-50/80 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a class="text-zinc-600 font-medium"
                                    x-text="orden.id"></a>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-zinc-700 font-medium"
                                x-text="orden.noeconomico"></td>

                            <td class="px-6 py-4 whitespace-nowrap text-zinc-600" x-text="orden.area"></td>

                            <td class="px-6 py-4 whitespace-nowrap text-zinc-600" x-text="orden.fechafirm"></td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <span x-show="!orden.orden_500 || orden.orden_500 === 'NO'"
                                    class="text-zinc-400 text-xs">NO</span>
                                @if(auth()->user()->can('generar 500'))
                                <button x-show="orden.orden_500 && orden.orden_500 !== 'NO'"
                                    @click="openModal('code500', orden)"
                                    class="text-emerald-700 font-bold hover:underline cursor-pointer"
                                    x-text="orden.orden_500"></button>
                                @else
                                <span x-show="orden.orden_500 && orden.orden_500 !== 'NO'"
                                    class="text-zinc-700 font-bold"
                                    x-text="orden.orden_500"></span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <template x-if="orden.status === 'PENDIENTE'">
                                    <button
                                        class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wide transition-colors cursor-pointer border bg-red-100 text-red-800 border-red-200"
                                        x-text="orden.status">
                                    </button>
                                </template>
                                <template x-if="orden.status === 'VEHICULO TALLER'">
                                    <button @click="openModal('status', orden)"
                                        class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wide transition-colors cursor-pointer border bg-orange-100 text-orange-800 border-orange-200"
                                        x-text="orden.status">
                                    </button>
                                </template>
                                <template x-if="orden.status === 'TERMINADO'">
                                    <button
                                        class="px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wide transition-colors cursor-pointer border bg-emerald-100 text-emerald-800 border-emerald-200"
                                        x-text="orden.status">
                                    </button>
                                </template>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-2">
                                <button @click="openModal('more', orden)"
                                    class="shadow-xs cursor-pointer border border-gray-400 bg-gray-50 text-gray-700 p-1.5 rounded-md hover:bg-emerald-100 hover:text-emerald-700 transition duration-200">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>

                                @if(auth()->user()->is('admin'))
                                <a :href="'/ordenvehiculos/' + orden.id + '/edit?return_url=/{{ urlencode(request()->getPath()) }}'"
                                    class="shadow-xs border border-gray-400 text-gray-700 bg-gray-50 p-1.5 rounded-md hover:bg-indigo-100 hover:text-indigo-600 transition duration-200">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.768 3.732z" />
                                    </svg>
                                </a>
                                @endif

                                @if(auth()->user()->is('admin'))
                                <button @click="openModal('delete', orden)"
                                    class="shadow-xs cursor-pointer border border-gray-400 text-gray-700 bg-gray-50 p-1.5 rounded-md hover:bg-red-100 hover:text-red-600 transition duration-200">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                                @endif
                            </td>
                        </tr>
                    </template>

                    <tr x-show="loading">
                        <td colspan="8" class="px-6 py-12 text-center text-zinc-500">
                            <svg class="animate-spin h-8 w-8 text-emerald-600 mx-auto mb-3"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Cargando órdenes...
                        </td>
                    </tr>
                    <tr x-show="!loading && ordenes.length === 0">
                        <td colspan="8" class="px-6 py-12 text-center text-zinc-500">
                            <svg class="h-10 w-10 text-zinc-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            No se encontraron órdenes.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="bg-gray-50 px-4 py-3 border-t border-zinc-200 flex items-center justify-between sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <p class="text-xs text-zinc-700">
                    Página <span class="font-medium" x-text="currentPage"></span> de <span class="font-medium"
                        x-text="lastPage"></span>
                </p>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1"
                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-zinc-300 bg-white text-xs font-medium text-zinc-500 hover:bg-zinc-50 disabled:opacity-50">Anterior</button>
                    <button @click="changePage(currentPage + 1)" :disabled="currentPage === lastPage"
                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-zinc-300 bg-white text-xs font-medium text-zinc-500 hover:bg-zinc-50 disabled:opacity-50">Siguiente</button>
                </nav>
            </div>
        </div>
    </div>

    <div x-show="modals.code500" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-transition.opacity>
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg transform transition-all"
        @click.away="modals.code500 = false">
        
        <h3 class="text-xl font-bold text-zinc-900 mb-4 border-b pb-2">Asignar Código 500</h3>

        <div class="mb-4">
            <h4 class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Servicios Solicitados</h4>
            <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto p-1">
                <template x-for="(label, key) in reparacionesMap" :key="key">
                    <span x-show="selectedOrden && selectedOrden[key] === 'X'" 
                          x-text="label"
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                    </span>
                </template>
                
                <div x-show="selectedOrden && !Object.keys(reparacionesMap).some(k => selectedOrden[k] === 'X')" 
                     class="text-sm text-zinc-400 italic w-full">
                    No se seleccionaron servicios específicos.
                </div>
            </div>
        </div>

        <div class="mb-6">
            <h4 class="text-xs font-bold text-zinc-500 uppercase tracking-wider mb-2">Observaciones / Fallas</h4>
            <div class="bg-zinc-50 rounded-md p-3 border border-zinc-200 text-sm text-zinc-700 min-h-[60px] max-h-[100px] overflow-y-auto">
                <p x-text="selectedOrden?.observacion || 'Sin observaciones registradas.'"></p>
            </div>
        </div>

        <hr class="border-zinc-200 mb-6">

        <div class="mb-6">
            <label class="block text-sm font-medium text-zinc-700 mb-1">Ingresar Código</label>
            <div class="relative">
                <span class="absolute left-3 top-2 text-zinc-400">#</span>
                <input type="text" x-model="tempData.orden_500" placeholder="Ej. 500-1234"
                    class="pl-8 w-full border-gray-300 rounded-md focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 p-2 border-2 font-bold text-zinc-800">
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <button @click="modals.code500 = false"
                class="px-4 py-2 text-zinc-600 hover:bg-zinc-100 rounded-md text-sm font-medium cursor-pointer">Cancelar</button>
            <button @click="saveAction('code500')"
                class="px-4 py-2 bg-emerald-600 text-white hover:bg-emerald-700 rounded-md text-sm font-medium shadow-sm cursor-pointer">Guardar</button>
        </div>
    </div>
</div>

    <div x-show="modals.status" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-transition.opacity>
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md transform transition-all"
            @click.away="modals.status = false">

            <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-zinc-900">Actualizar estado</h3>
            <button @click="modals.status = false" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
            </div>

            <p class="text-sm text-zinc-500 mt-1 mb-4">Estado actual: <span class="font-bold"
                    x-text="selectedOrden?.status"></span></p>

            <div class="space-y-4 mb-6">

                <div class=p-3 rounded-md border border-emerald-100 space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Kilometraje Salida <span
                                class="text-red-500">*</span></label>
                        <div class="relative rounded-md shadow-sm">
                            <input type="text" x-model="tempData.kilometraje" required
                                class="w-full border border-gray-300 rounded-md focus:ring-emerald-600 focus:border-emerald-600 py-2 pl-3 pr-12 shadow-sm mask-km"
                                placeholder="Ingrese el kilometraje de salida">

                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">km</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Fecha de Terminación <span
                                class="text-red-500">*</span></label>
                        <input type="date" x-model="tempData.fechaTerminacion" required
                            class="w-full border border-gray-300 rounded-md focus:ring-emerald-600 focus:border-emerald-600 p-2 shadow-sm"
                            x-bind:max="maxDate" @blur="validateFecha()">
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-2 border-t pt-4 border-gray-300">
                <button @click="modals.status = false"
                    class="px-4 py-2 text-zinc-600 hover:bg-zinc-100 rounded-md text-sm font-medium">Cancelar</button>
                <button @click="saveAction('status')"
                    class="px-4 py-2 bg-emerald-600 text-white hover:bg-emerald-700 rounded-md text-sm font-medium">Guardar</button>
            </div>
        </div>
    </div>

    @include('ordenvehiculos.modalVerMas')

    <div x-show="modals.delete" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-transition.opacity>
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-sm transform transition-all"
            @click.away="modals.delete = false">
            <h3 class="text-lg font-bold text-zinc-900">¿Eliminar esta orden?</h3>
            <p class="text-sm text-zinc-500 mt-2">Esta acción no se puede revertir.</p>
            <div class="flex justify-end gap-2 mt-6">
                <button @click="modals.delete = false"
                    class="px-4 py-2 text-zinc-600 hover:bg-zinc-100 rounded-md text-sm font-medium cursor-pointer">Cancelar</button>
                <button @click="saveAction('delete')"
                    class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-md text-sm font-medium cursor-pointer">Eliminar</button>
            </div>
        </div>
    </div>

</div>

<script>
    function ordenesTable(filterNoEconomico = null) {
        return {
            ordenes: [],
            loading: true,
            // Paginación
            currentPage: 1,
            lastPage: 1,
            perPage: 10,
            search: '',
            noEconomicoExact: filterNoEconomico,
            filters: {
                fecha_inicio: '',
                fecha_fin: '',
                estado: ''
            },

            // Estado de Modales
            modals: {
                code500: false,
                status: false,
                more: false,
                delete: false
            },
            maxDate: new Date().toLocaleDateString('en-CA'),

            // Datos Temporales para Edición
            selectedOrden: null,
            historial: [],
            historialLoading: false,
            // Mapas de configuración (copiados de tu PHP array)
            badgeClasses: {
                'orden_creado': 'bg-green-100 text-green-700',
                'orden_creada': 'bg-green-100 text-green-700', // Por si acaso el typo en store
                'orden_actualizado': 'bg-blue-100 text-blue-700',
                'orden_actualizada': 'bg-blue-100 text-blue-700',
                'estado_cambiado': 'bg-amber-100 text-amber-700',
                'archivo_subido': 'bg-purple-100 text-purple-700',
                'orden_500': 'bg-gray-100 text-gray-700'
            },
            titleMap: {
                'archivo_subido': 'Se subió un archivo',
                'estado_cambiado': 'Se cambió el estado',
                'orden_creado': 'Se creó la orden',
                'orden_creada': 'Se creó la orden',
                'orden_actualizado': 'Se actualizó la orden',
                'orden_actualizada': 'Se actualizó la orden',
                'orden_500': 'Se agregó código 500',
            },

            // Mapa de etiquetas para las reparaciones
            reparacionesMap: {
                'vehicle1': 'Afinación mayor',
                'vehicle2': 'Ajuste motor',
                'vehicle3': 'Alineación y balanceo',
                'vehicle4': 'Amortiguadores',
                'vehicle5': 'Cambio aceite y filtro',
                'vehicle6': 'Clutch',
                'vehicle7': 'Diagnóstico',
                'vehicle8': 'Dirección',
                'vehicle9': 'Servicio Lavado y engrasado',
                'vehicle10': 'Hojalatería y pintura',
                'vehicle11': 'Medio motor',
                'vehicle12': 'Motor completo',
                'vehicle13': 'Parabrisas y vidrios',
                'vehicle14': 'Frenos',
                'vehicle15': 'Sistema eléctrico',
                'vehicle16': 'Sistema de enfriamiento',
                'vehicle17': 'Suspensión',
                'vehicle18': 'Transmisión y diferencial',
                'vehicle19': 'Tapicería',
                'vehicle20': 'Otro',
            },

            tempData: {
                orden_500: '',
                newStatus: 'TERMINADO',
                kilometraje: '',
                fechaTerminacion: ''
            },
            validateFecha() {
                // Si no hay fecha, no hacemos nada
                if (!this.tempData.fechaTerminacion) return;

                // Comparamos cadenas (YYYY-MM-DD)
                if (this.tempData.fechaTerminacion > this.maxDate) {
                    // Opción A: Resetear a HOY
                    this.tempData.fechaTerminacion = this.maxDate;

                    // Opción B: Si prefieres borrarlo
                    // this.tempData.fechaTerminacion = '';

                    // Usamos tu SweetAlert existente para un aviso sutil (Toast)
                    const Swal = window.Swal; // Aseguramos acceso a Swal
                    if (Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'warning',
                            title: 'No puedes seleccionar fechas futuras',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    } else {
                        alert('No puedes seleccionar fechas futuras');
                    }
                }
            },
            init() {
                // Carga inicial de datos
                this.fetchData();

                // Watchers para reaccionar a cambios en los filtros y búsqueda
                this.$watch('search', () => {
                    this.currentPage = 1;  // Reinicia a la primera página al buscar
                    this.fetchData();
                });

                this.$watch('filters', () => {
                    this.currentPage = 1;
                    this.fetchData();
                }, { deep: true }); // Importante: deep para detectar cambios dentro del objeto filters

                this.$watch('perPage', () => {
                    this.currentPage = 1;
                    this.fetchData();
                });
            },

            // Cargar Datos
            async fetchData() {
                this.loading = true;
                const params = new URLSearchParams({
                    page: this.currentPage,
                    perPage: this.perPage,
                    search: this.search,
                    estado: this.filters.estado,
                    fecha_inicio: this.filters.fecha_inicio,
                    fecha_fin: this.filters.fecha_fin,
                    no_economico_exacto: this.noEconomicoExact || ''
                });

                try {
                    const res = await fetch(`/api/ordenes/search?${params}`);
                    const data = await res.json();
                    this.ordenes = data.data;
                    this.lastPage = data.last_page;
                    this.currentPage = data.current_page;
                } catch (e) {
                    console.error(e);
                } finally {
                    this.loading = false;
                }
            },

            // Paginación
            changePage(page) {
                if (page >= 1 && page <= this.lastPage) {
                    this.currentPage = page;
                    this.fetchData();
                }
            },
            resetFilters() {
                this.search = '';
                this.filters = {
                    fecha_inicio: '',
                    fecha_fin: '',
                    estado: ''
                };
                this.fetchData();
            },

            // GESTIÓN DE MODALES
            openModal(type, orden) {
                // Close all modals first
                Object.keys(this.modals).forEach(modal => {
                    this.modals[modal] = false;
                });

                this.selectedOrden = orden;
                // Resetear datos temporales con los datos actuales de la orden
                this.tempData = {
                    orden_500: orden.orden_500 || '',
                    newStatus: 'TERMINADO',
                    kilometraje: '',
                    fechaTerminacion: ''
                };


                this.modals[type] = true;
                // NUEVO: Si abrimos el modal "more", cargamos el historial
                if (type === 'more') {
                    this.fetchHistorial(orden.id);
                    this.activeTab = 'general'; // Reset tab
                }
            },

            // NUEVA FUNCIÓN
            async fetchHistorial(id) {
                this.historialLoading = true;
                this.historial = []; // Limpiar anterior
                try {
                    const res = await fetch(`/api/ordenes/${id}/historial`); // Ajusta tu ruta según definas en Leaf
                    if (res.ok) {
                        this.historial = await res.json();
                    }
                } catch (e) {
                    console.error("Error cargando historial", e);
                } finally {
                    this.historialLoading = false;
                }
            },
            // UTILIDADES PARA LA VISTA
            formatDate(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                return date.toLocaleString('es-MX', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            },
            getTitulo(tipo) {
                return this.titleMap[tipo] || tipo.replace('_', ' ').toUpperCase();
            },
            getClass(tipo) {
                return this.badgeClasses[tipo] || 'bg-gray-100 text-gray-700';
            },

            // ACCIONES DE GUARDADO (Mockup por ahora)
            saveAction(type) {
                //const tokenInput = document.querySelector('input[name="_token"]');
                const tokenInput = this.$root.querySelector('input[name="_token"]');
                const csrfToken = tokenInput ? tokenInput.value : '';

                const swalInstance = Swal.fire({
                    title: 'Procesando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                let url = '';
                let method = 'PUT'; // Por defecto PUT
                let body = null;
                let headers = {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                };

                // --- LÓGICA SEGÚN EL TIPO ---
                // --- 1. CONFIGURACIÓN DE URL Y MÉTODO SEGÚN TIPO ---
                if (type === 'delete') {
                    url = `/ordenvehiculos/${this.selectedOrden.id}`;
                    method = 'DELETE';

                    // Para DELETE, generalmente enviamos solo el token o cuerpo vacío
                    // Leaf requiere el token en headers (ya está arriba)
                    body = JSON.stringify({
                        _token: csrfToken
                    });
                    headers['Content-Type'] = 'application/json';

                } else if (type === 'upload') {
                    // CONFIGURACIÓN PARA SUBIDA DE ARCHIVOS
                    url = `/ordenvehiculos/upload/${this.selectedOrden.id}`;
                    method = 'POST'; // Subida de archivos debe ser POST

                    // Usamos FormData para enviar archivos
                    const formData = new FormData();
                    // Obtenemos el archivo desde la referencia x-ref="fileInput"
                    const fileInput = this.$refs.fileInput;

                    if (fileInput && fileInput.files.length > 0) {
                        formData.append('archivo', fileInput.files[0]);
                    } else {
                        //Swal.fire('Error', 'Debes seleccionar un archivo', 'warning');
                        Swal.fire({
                            position: "top-center",
                            timerProgressBar: false,
                            icon: "warning",
                            title: "Debes seleccionar un archivo",
                            showConfirmButton: true,
                            confirmButtonColor: '#059669',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.hideLoading();
                                const backdrop = document.querySelector('.swal2-backdrop-show');
                                if (backdrop) {
                                    backdrop.addEventListener('click', (e) => {
                                        e
                                            .stopPropagation(); // evita que el clic llegue al @click.away
                                        // Opcional: también puedes evitar que se cierre Swal si quieres
                                        // e.preventDefault();
                                    });
                                }
                            }
                        });
                        return;
                    }

                    formData.append('comentarios', this.tempData.comentarios || '');
                    formData.append('_token', csrfToken); // Agregar token al form data también por seguridad

                    body = formData;

                } else {
                    // --- LÓGICA EXISTENTE PARA JSON (code500, status, etc) ---
                    const data = {
                        _token: csrfToken,

                    };

                    if (type === 'code500') {
                        url = `/ordenvehiculos/code500/${this.selectedOrden.id}`;
                        data.orden_500 = this.tempData.orden_500;
                    } else if (type === 'status') {
                        if (!this.tempData.kilometraje) {
                            Swal.fire({
                                toast: true,
                                position: "top-end",
                                icon: 'warning',
                                allowOutsideClick: false,
                                title: 'El kilometraje es obligatorio',
                                timer: 3000,
                                showConfirmButton: false,
                                timerProgressBar: false,
                                didOpen: (toast) => {
                                    Swal.hideLoading();
                                }
                            });
                            return; // Detiene la ejecución para que no se envíe nada
                        }   
                        url = `/ordenvehiculos/modal/${this.selectedOrden.id}`;
                        data.status = this.tempData.newStatus;
                        data.kilometraje = this.tempData.kilometraje;
                        data.fecha_terminacion = this.tempData.fechaTerminacion;
                    }

                    body = JSON.stringify(data);
                    headers['Content-Type'] = 'application/json';
                }

                // --- EJECUTAR FETCH ---
                fetch(url, {
                        method: method,
                        headers: headers,
                        body: body
                    })
                    .then(response => {
                        if (!response.ok) return response.json().then(err => {
                            throw err;
                        });
                        return response.json();
                    })
                    .then(data => {
                        // Cerrar modales
                        this.modals[type] = false;
                        if (type === 'upload') this.modals.more = false; // Cerrar también el modal "ver mas"

                        Swal.fire({
                            title: '¡Éxito!',
                            position: "top",
                            text: data.message || 'Acción realizada correctamente',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // Limpiar inputs si fue upload
                        if (type === 'upload' && this.$refs.fileInput) {
                            this.$refs.fileInput.value = '';
                            this.tempData.comentarios = '';
                        }

                        if (type === 'status') {
                            window.location.reload();
                            return;
                        }

                        this.fetchData();
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        Swal.fire({
                            toast: true,
                            position: "top-end",
                            icon: "warning",
                            allowOutsideClick: false,
                            title: error.message || 'Ocurrió un error',
                            showConfirmButton: false,
                            timer: 3000,
                            didOpen: () => {
                                const backdrop = document.querySelector('.swal2-backdrop-show');
                                if (backdrop) {
                                    backdrop.addEventListener('click', (e) => {
                                        e
                                            .stopPropagation(); // evita que el clic llegue al @click.away
                                        // Opcional: también puedes evitar que se cierre Swal si quieres
                                        // e.preventDefault();
                                    });
                                }
                            }
                        });
                    })
                    .finally(() => {
                        if (swalInstance.isActive) swalInstance.close();
                    });
            }
        }
    }
</script>
