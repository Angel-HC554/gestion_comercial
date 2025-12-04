    <div x-show="modals.more" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-transition.opacity
        x-data="{ activeTab: 'general' }">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden"
            @click.away="modals.more = false">
            <div class="p-6 pb-0">
                <div class="flex items-center mb-4">
                    <h3 class="text-xl font-bold text-zinc-900 mr-2">Datos de la orden</h3>
                    <svg class="h-6 w-6 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>

                <!-- Tabs Navigation -->
                <div class="border-b border-zinc-200">
                    <nav class="-mb-px flex space-x-8">
                        <button @click="activeTab = 'general'"
                            :class="{ 'border-emerald-500 text-emerald-600': activeTab === 'general', 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300': activeTab !== 'general' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Información General
                        </button>
                        <button @click="activeTab = 'escaneos'"
                            :class="{ 'border-emerald-500 text-emerald-600': activeTab === 'escaneos', 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300': activeTab !== 'escaneos' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Subir escaneo
                        </button>
                        <button @click="activeTab = 'historial'"
                            :class="{ 'border-emerald-500 text-emerald-600': activeTab === 'historial', 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300': activeTab !== 'historial' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Historial
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="flex-1 flex flex-col overflow-hidden px-6 pb-6 mt-4">
                <!-- Tab Content Wrapper with fixed height -->
                <div class="flex-1 flex flex-col min-h-0 overflow-y-auto" style="min-height: 400px;">
                    <!-- Información General Tab -->
                    <div x-show="activeTab === 'general'" class="h-full overflow-y-auto p-4 space-y-6">
                        <!-- Sección de información principal -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                            <div class="grid grid-cols-3 gap-4 text-sm">
                                <div class="space-y-1">
                                    <p class="text-gray-700 text-xs uppercase tracking-wider">Número de orden</p>
                                    <p x-text="'#' + (selectedOrden?.id || 'N/A')" class="font-medium text-gray-900">
                                    </p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-gray-700 text-xs uppercase tracking-wider">Fecha de creación</p>
                                    <p x-text="selectedOrden?.fechafirm || 'N/A'" class="font-medium text-gray-900"></p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-gray-700 text-xs uppercase tracking-wider">Estado</p>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <span x-text="selectedOrden?.estado || 'Pendiente'"></span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Sección de observaciones -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                            <p class="text-gray-700 text-xs uppercase tracking-wider mb-4">Observaciones</p>
                            <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r text-sm text-gray-700">
                                <p x-text="selectedOrden?.observacion || 'No se han registrado observaciones para esta orden.'"
                                    class="leading-relaxed"></p>
                            </div>
                        </div>

                        <!-- Sección de archivos -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                            <p class="text-gray-700 text-xs uppercase tracking-wider mb-4">Archivos</p>
                            <div class="space-y-3 w-1/3">
                                <a :href="'/ordenvehiculos/pdf/' + selectedOrden?.id"
                                    class="flex items-center px-4 py-2 text-sm text-sky-700 bg-sky-50 hover:bg-sky-100 rounded">
                                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    Documento generado
                                </a>

                                <div class="mt-2">
                                    <template x-if="selectedOrden?.archivo && selectedOrden.archivo.ruta_archivo">
                                        <a :href="selectedOrden.archivo.ruta_archivo" target="_blank"
                                            class="flex items-center px-4 py-2 text-sm text-orange-600 bg-orange-50 hover:bg-orange-100 rounded border border-orange-200 transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            Entregado a Taller
                                        </a>
                                    </template>

                                    <template x-if="!selectedOrden?.archivo">
                                        <span
                                            class="flex items-center px-4 py-2 text-sm text-gray-400 bg-gray-50 rounded border border-gray-100 cursor-not-allowed">
                                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            Sin escaneo subido
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Escaneos Tab -->
                    <div x-show="activeTab === 'escaneos'" class="h-full overflow-y-auto">
                        <div class="h-full">
                            <div class="mb-5">
                                <label class="block text-sm font-bold text-zinc-700 mb-2">Archivo:</label>
                                <input type="file" x-ref="fileInput"
                                    class="block w-full text-sm text-zinc-500
                file:rounded-md file:text-sm file:font-semibold
                file:bg-emerald-50 file:text-emerald-700
                file:mr-4 file:p-2 file:border-2 file:rounded-b-sm 
                hover:file:bg-emerald-100 cursor-pointer">
                            </div>

                            <div class="mb-5">
                                <textarea x-model="tempData.comentarios"
                                    class="w-full border border-zinc-300 rounded-md text-sm p-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition shadow-sm"
                                    rows="2" placeholder="Agrega tus comentarios aquí (opcional)..."></textarea>
                            </div>

                            <div class="flex justify-end">
                                <button @click="saveAction('upload')"
                                    class="bg-emerald-600 text-white px-4 py-2 rounded-md text-sm font-normal hover:bg-emerald-700 flex items-center shadow-sm transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Subir Escaneo
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Historial Tab -->
                    <div x-show="activeTab === 'historial'" class="h-full min-h-0">
                        @include('ordenvehiculos.historial')
                    </div>
                </div>

                <div class="flex justify-end p-6 border-t border-zinc-200">
                    <button @click="modals.more = false"
                        class="px-4 py-2 bg-emerald-600 text-white hover:bg-emerald-700 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
