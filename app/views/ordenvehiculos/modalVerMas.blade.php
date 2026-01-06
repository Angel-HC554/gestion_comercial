    <div x-show="modals.more" x-cloak x-trap.noscroll="modals.more"
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
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm cursor-pointer">
                            Información General
                        </button>
                        <button @click="activeTab = 'escaneos'"
                            :class="{ 'border-emerald-500 text-emerald-600': activeTab === 'escaneos', 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300': activeTab !== 'escaneos' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm cursor-pointer">
                            Subir escaneo
                        </button>
                        <button @click="activeTab = 'historial'"
                            :class="{ 'border-emerald-500 text-emerald-600': activeTab === 'historial', 'border-transparent text-zinc-500 hover:text-zinc-700 hover:border-zinc-300': activeTab !== 'historial' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm cursor-pointer">
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
                    <div x-show="activeTab === 'general'" class="h-full overflow-y-auto p-6">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                            <div class="space-y-5">
                                <div>
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Número de
                                        orden</p>
                                    <p x-text="'#' + (selectedOrden?.id || 'N/A')"
                                        class="text-2xl font-bold text-zinc-900"></p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Fecha
                                            de creación</p>
                                        <p x-text="selectedOrden?.fechafirm || 'N/A'"
                                            class="text-sm font-medium text-zinc-700"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Estado
                                        </p>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                            <span x-text="selectedOrden?.status || 'Desconocido'"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col justify-start md:items-end space-y-3">
                                <p
                                    class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1 w-full md:text-right">
                                    Archivos Adjuntos
                                </p>

                                <a :href="'/ordenvehiculos/pdf/' + selectedOrden?.id"
                                    class="w-full md:w-auto flex items-center justify-center md:justify-start px-4 py-2 text-sm font-medium text-sky-700 bg-sky-50 hover:bg-sky-100 border border-sky-300 rounded-lg transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                    Doc generado
                                </a>

                                <div class="w-full md:w-auto">
                                    <template x-if="selectedOrden?.archivo && selectedOrden.archivo.ruta_archivo">
                                        <a :href="selectedOrden.archivo.ruta_archivo" target="_blank"
                                            class="w-full md:w-auto flex items-center justify-center md:justify-start px-4 py-2 text-sm font-medium text-orange-700 bg-orange-50 hover:bg-orange-100 border border-orange-300 rounded-lg transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            Doc entregado taller
                                        </a>
                                    </template>

                                    <template x-if="!selectedOrden?.archivo">
                                        <span
                                            class="w-full md:w-auto flex items-center justify-center md:justify-start px-4 py-2 text-sm text-gray-400 bg-gray-50 border border-gray-100 rounded-lg cursor-not-allowed">
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

                        <hr class="border-gray-200 my-8">

                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Observaciones de la orden</p>
                            <div class="bg-amber-50 rounded-lg border border-amber-100 p-5">
                                <p x-text="selectedOrden?.observacion || 'No se han registrado observaciones para esta orden.'"
                                    class="text-sm text-zinc-700 leading-relaxed"></p>
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
                                    class="w-full border-2 border-zinc-300 rounded-md text-sm p-3 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 transition"
                                    rows="2" placeholder="Agrega tus comentarios aquí (opcional)..."></textarea>
                            </div>

                            <div class="flex justify-end">
                                <button @click="saveAction('upload')"
                                    class="bg-emerald-600 text-white px-4 py-2 rounded-md text-sm font-normal hover:bg-emerald-700 flex items-center shadow-sm transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 cursor-pointer">
                                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                        class="px-4 py-2 bg-emerald-600 text-white hover:bg-emerald-700 rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 cursor-pointer">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
