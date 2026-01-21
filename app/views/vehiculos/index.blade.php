@extends('layouts.app-layout', [
    'title' => 'Vehículos'
])

@section('content')
    <div x-data="vehiculosApp()" @import-success.window="handleImportSuccess($event.detail.message)" class="min-h-screen pb-10">

        <div class="flex items-center justify-start gap-12 mb-6 mx-10 pt-6">
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Vehículos</h1>

            <div x-show="flashMessage" x-transition.opacity.duration.500ms
                class="fixed top-5 left-1/2 transform -translate-x-1/2 z-[9999] px-4 py-2 rounded-lg shadow-lg text-sm font-medium text-white"
                :class="flashType === 'success' ? 'bg-emerald-600' : 'bg-red-600'" style="display: none;">
                <span x-text="flashMessage"></span>
            </div>

            <div class="flex space-x-2">
                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-zinc-700 bg-white border border-zinc-200 rounded-lg hover:bg-zinc-50 transition-colors shadow-sm cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor"
                                    class="w-4 h-4 text-zinc-400 group-hover:text-zinc-600 transition-transform duration-200"
                                    :class="open ? 'rotate-180' : ''">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                        Acciones
                    </button>
                    <div x-show="open" x-transition.opacity.duration.200ms x-cloak
                        class="absolute left-0 mt-2 w-64 origin-top-left rounded-xl bg-white shadow-xl ring-1 ring-zinc-900/5 focus:outline-none z-50 flex p-1 gap-1">
                        <a @click="$dispatch('open-import-modal')"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-zinc-700 rounded-lg hover:bg-zinc-100 hover:text-zinc-900 transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                            Importar
                        </a>
                        <div class="w-px bg-zinc-200 my-1 mx-1"></div>
                        <a @click="$dispatch('open-export-modal')"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-zinc-700 rounded-lg hover:bg-zinc-100 hover:text-zinc-900 transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Exportar
                        </a>
                    </div>
                </div>

                <button @click="openNewModal"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Nuevo Vehículo
                </button>
            </div>
        </div>

        <div
            class="flex flex-col sm:flex-row mx-4 sm:mx-10 gap-4 shadow-sm items-center bg-white rounded-md border-t-4 border-t-emerald-700/40 p-3 mb-10">

            <div class="relative w-full sm:w-96">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-zinc-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <input type="text" x-model.debounce.500ms="search" placeholder="no. económico"
                    class="block w-full rounded-md border-2 py-1.5 pl-10 text-zinc-900 border-zinc-300 placeholder:text-zinc-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm sm:leading-6 bg-white">
            </div>

            <select x-model="estado"
                class="w-full sm:w-64 h-9 border-2 border-zinc-300 rounded-md px-2 py-1 text-zinc-500 focus:ring-emerald-600 focus:border-emerald-600 text-sm cursor-pointer">
                <option value="">Todos</option>
                @foreach ($estados as $est)
                    <option value="{{ $est }}" class="text-zinc-700">{{ $est }}</option>
                @endforeach
            </select>

            <button @click="resetFilters"
                class="w-full sm:w-auto px-4 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 text-sm font-medium rounded-md transition-colors border border-zinc-300 cursor-pointer">
                Borrar filtros
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 auto-rows-fr gap-8 px-4 sm:px-10">

            <template x-for="v in vehiculos" :key="v.id">
                <a :href="'/vehiculos/' + v.id"
                    class="flex flex-col h-full bg-white rounded-xl shadow-md border border-zinc-300 shadow-zinc-300 hover:shadow-gray-600 cursor-pointer transition-all duration-150 hover:translate-y-2 group">

                    <div class="w-full h-48 overflow-hidden rounded-t-xl relative bg-gray-200">
                        <img :src="v.foto || 'https://placehold.co/460x260?text=Sin+foto'"
                            :alt="`Foto ${v.marca} ${v.modelo || ''}`"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    </div>

                    <div class="flex-1 p-4 bg-gradient-to-t from-emerald-600 to-emerald-900 rounded-b-xl text-white">
                        <div class="flex justify-between items-start">
                            <h2 class="font-bold text-lg text-white" x-text="v.marca + ' ' + (v.modelo || '')"></h2>
                            <p class="text-sm opacity-90">Año: <span x-text="v.año"></span></p>
                        </div>
                        <p class="text-md font-semibold text-white mt-1">No económico: <span x-text="v.no_economico"></span>
                        </p>
                        <p class="text-sm text-white opacity-90 mt-1" x-text="v.estado"></p>
                        <div class="text-sm flex justify-between text-white mt-3 pt-3 border-t border-emerald-500/30">
                            <p>Tipo: <span x-text="v.tipo_vehiculo"></span></p>
                            <p x-text="v.placas"></p>
                        </div>
                    </div>
                </a>
            </template>

            <div x-show="vehiculos.length === 0 && !loading" class="col-span-full px-10 text-center" style="display: none;">
                <div class="rounded-md border border-zinc-200 bg-white p-12 text-zinc-500 flex flex-col items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-10 mb-3 text-zinc-300">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <span class="text-lg">No hay vehículos para mostrar.</span>
                </div>
            </div>

            <template x-if="loading">
                <div class="col-span-full text-center py-10">
                    <svg class="animate-spin h-8 w-8 text-emerald-600 mx-auto" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span class="text-emerald-700 mt-2 block text-sm font-medium">Cargando vehículos...</span>
                </div>
            </template>

        </div>

        <div class="mx-10 mt-8 flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 rounded-lg shadow-sm"
            x-show="total > 0">
            <div class="flex flex-1 justify-between sm:hidden">
                <button @click="prevPage" :disabled="page === 1"
                    class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">Anterior</button>
                <button @click="nextPage" :disabled="page === lastPage"
                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">Siguiente</button>
            </div>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Mostrando página <span class="font-medium" x-text="page"></span> de <span class="font-medium"
                            x-text="lastPage"></span>
                        (<span class="font-medium" x-text="total"></span> resultados)
                    </p>
                </div>
                <div>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                        <button @click="prevPage" :disabled="page === 1"
                            class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="sr-only">Anterior</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                        <button @click="nextPage" :disabled="page === lastPage"
                            class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="sr-only">Siguiente</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </nav>
                </div>
            </div>
        </div>

        <div x-show="modals.new" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-[1200px] max-h-[90vh] flex flex-col overflow-hidden"
                @click.away="modals.new = false">
                <div class="p-6 border-b border-zinc-200">
                    <h3 class="text-xl font-bold text-zinc-900">Nuevo Vehículo</h3>
                    <p class="mt-2 text-sm text-zinc-500">Completa la información para registrar un nuevo vehículo.</p>
                </div>

                <div class="p-6 overflow-y-auto vehiculo-modal">
                    <form @submit.prevent="saveVehiculo">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Agencia</label>
                                <select x-model="form.agencia"
                                    class="mt-1 block w-full rounded-md border-zinc-300 border-2 focus:ring-emerald-600 focus:border-emerald-600">
                                    <option value="DW01">DW01</option>
                                    <option value="DW01A">DW01A</option>
                                    <option value="DW01B">DW01B</option>
                                    <option value="DW01C">DW01C</option>
                                    <option value="DW01D">DW01D</option>
                                    <option value="DW01E">DW01E</option>
                                    <option value="DW01G">DW01G</option>
                                    <option value="DW01H">DW01H</option>
                                    <option value="DW01J">DW01J</option>
                                    <option value="DW01K">DW01K</option>
                                    <option value="DW01M">DW01M</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">No. económico</label>
                                <input type="text" x-model="form.no_economico" placeholder="No. económico"
                                    class="mt-1 block w-full rounded-md border-zinc-300 border-2 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Placas</label>
                                <input type="text" x-model="form.placas" placeholder="Placas"
                                    class="mt-1 block w-full rounded-md border-zinc-300 border-2 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo de vehículo</label>
                                <input type="text" x-model="form.tipo_vehiculo" placeholder="Tipo de vehículo"
                                    class="mt-1 block w-full rounded-md border-zinc-300 border-2 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Marca</label>
                                <input type="text" x-model="form.marca" placeholder="Marca"
                                    class="mt-1 block w-full rounded-md border-zinc-300 border-2 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Modelo</label>
                                <input type="text" x-model="form.modelo" placeholder="Modelo"
                                    class="mt-1 block w-full rounded-md border-zinc-300 border-2 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Año</label>
                                <input type="number" x-model="form.año" placeholder="Ingresa el año"
                                    class="mt-1 block w-full rounded-md border-zinc-300 border-2 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600" placeholder="2025" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Proceso</label>
                                <input type="text" x-model="form.proceso" placeholder="Proceso"
                                    class="mt-1 block w-full rounded-md border-zinc-300 border-2 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Alias</label>
                                <input type="text" x-model="form.alias" placeholder="Alias"
                                    class="mt-1 block w-full rounded-md border-zinc-300 border-2 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">RPE Crea/Mod</label>
                                <input type="text" x-model="form.rpe_creamod" placeholder="RPE Crea/Mod"
                                    class="mt-1 block w-full rounded-md border-zinc-300 border-2 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Estado</label>
                                <select x-model="form.estado"
                                    class="mt-1 block w-full rounded-md border-zinc-300 border-2 focus:ring-emerald-600 focus:border-emerald-600">
                                    <option value="">Seleccione...</option>
                                    <option value="En circulacion">En circulación</option>
                                    <option value="En mantenimiento">En mantenimiento</option>
                                    <option value="Fuera de circulacion por falla pendiente">Fuera de circulación por falla
                                        pendiente</option>
                                    <option value="Fuera de circulacion">Fuera de circulación</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Propiedad</label>
                                <select x-model="form.propiedad"
                                    class="mt-1 block w-full rounded-md border-zinc-300 border-2 focus:ring-emerald-600 focus:border-emerald-600">
                                    <option value="">Seleccione...</option>
                                    <option value="Arrendado">Arrendado</option>
                                    <option value="Propio (CFE)">Propio (CFE)</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-zinc-100">
                            <button type="button" @click="modals.new = false"
                                class="px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 rounded-md transition-colors cursor-pointer">Cancelar</button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-md shadow-sm transition-colors flex items-center cursor-pointer"
                                :disabled="submitting">
                                <span x-show="!submitting">Guardar</span>
                                <span x-show="submitting">Guardando...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @include('vehiculos.modalExport')
        @include('vehiculos.modalImport')

    </div>

    <style>
        .vehiculo-modal input[type="text"],
        .vehiculo-modal input[type="number"],
        .vehiculo-modal select {
            height: 2rem;
            /* ~32px */
            padding: 0.25rem 0.75rem;
            /* py-1 px-3 */
            font-size: 0.875rem;
            /* text-sm */
            line-height: 1.25rem;
            /* leading-5 */
            caret-color: #111827;
            width: 100%;
            /* Asegura que llenen el contenedor */
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    <script>
        function vehiculosApp() {
            return {
                // Datos
                vehiculos: [],
                total: 0,
                page: 1,
                lastPage: 1,
                loading: false,

                // Filtros
                search: '',
                estado: '',

                // Modales y Formularios
                modals: {
                    new: false,
                    import: false,
                    export: false
                },
                submitting: false,
                flashMessage: '',
                flashType: 'success',

                form: {
                    agencia: 'DW01',
                    no_economico: '',
                    placas: '',
                    tipo_vehiculo: '',
                    marca: '',
                    modelo: '',
                    año: '',
                    proceso: '',
                    alias: '',
                    rpe_creamod: '',
                    estado: '',
                    propiedad: ''
                },

                init() {
                    this.fetchVehiculos();

                    // Watchers manuales (Alpine 'x-effect' o $watch)
                    this.$watch('search', () => {
                        this.page = 1;
                        this.fetchVehiculos();
                    });
                    this.$watch('estado', () => {
                        this.page = 1;
                        this.fetchVehiculos();
                    });
                },

                async fetchVehiculos() {
                    this.loading = true;
                    try {
                        // Construir query params
                        const params = new URLSearchParams({
                            page: this.page,
                            search: this.search,
                            estado: this.estado
                        });

                        const response = await fetch(`/vehiculos/search?${params}`);
                        const result = await response.json();

                        this.vehiculos = result.data;
                        this.total = result.total;
                        this.lastPage = result.last_page;
                    } catch (error) {
                        console.error('Error cargando vehículos:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                nextPage() {
                    if (this.page < this.lastPage) {
                        this.page++;
                        this.fetchVehiculos();
                    }
                },

                prevPage() {
                    if (this.page > 1) {
                        this.page--;
                        this.fetchVehiculos();
                    }
                },

                resetFilters() {
                    this.search = '';
                    this.estado = '';
                    this.page = 1;
                    // Fetch se disparará automáticamente por el $watch
                },

                // Acciones de Modal
                openNewModal() {
                    // Reset form
                    this.form = {
                        agencia: 'DW01',
                        no_economico: '',
                        placas: '',
                        tipo_vehiculo: '',
                        marca: '',
                        modelo: '',
                        año: '',
                        proceso: '',
                        alias: '',
                        rpe_creamod: '',
                        estado: '',
                        propiedad: ''
                    };
                    this.modals.new = true;
                },

                handleImportSuccess(msg) {
                    this.showFlash(msg, 'success');
                    this.fetchVehiculos(); // Recarga la tabla automáticamente
                },
                openExportModal() {
                    this.modals.export = true;
                },

                async saveVehiculo() {
                    this.submitting = true;
                    try {
                        const response = await fetch('/vehiculos', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(this.form)
                        });

                        const result = await response.json();

                        if (result.status === 'success') {
                            this.modals.new = false;
                            this.showFlash(result.message, 'success');
                            this.fetchVehiculos(); // Recargar tabla
                        } else {
                            this.showFlash(result.message || 'Error al guardar', 'error');
                        }
                    } catch (error) {
                        this.showFlash('Error de conexión', 'error');
                    } finally {
                        this.submitting = false;
                    }
                },

                showFlash(message, type) {
                    this.flashMessage = message;
                    this.flashType = type;
                    setTimeout(() => {
                        this.flashMessage = '';
                    }, 3000);
                }
            }
        }
    </script>
@endsection
