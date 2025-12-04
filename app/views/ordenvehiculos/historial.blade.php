<div class="h-full flex flex-col min-h-0 overflow-y-auto relative px-10">
    <div x-show="historialLoading" x-transition.opacity
        class="absolute inset-0 bg-white/80 backdrop-blur-sm z-20 flex items-center justify-center pointer-events-none">

        <svg class="animate-spin h-8 w-8 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
    </div>

    <div x-show="!historialLoading && historial.length === 0" class="h-full flex flex-col">
        <div
            class="bg-zinc-50 rounded-lg border border-zinc-200 p-6 text-center flex-1 flex flex-col items-center justify-center m-4">
            <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-zinc-900">Historial de actividad</h3>
            <p class="mt-1 text-sm text-zinc-500">El historial de actividad aparecerá aquí</p>
        </div>
    </div>

    <div x-show="!historialLoading && historial.length > 0"
        class="relative mt-6 space-y-4 pl-6 pb-4 before:content-[''] before:absolute before:left-3 before:top-0 before:bottom-0 before:w-px before:bg-emerald-200">

        <template x-for="evento in historial" :key="evento.id">
            <div class="relative ml-1 rounded-xl border border-gray-200 bg-white p-4 shadow-sm mb-4">
                <span
                    class="absolute -left-3 top-5 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-4 ring-emerald-100"></span>

                <div class="flex items-start justify-between mb-2">
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-md font-semibold text-gray-800">
                                <span x-text="getTitulo(evento.tipo_evento)"></span>
                                
                            </span>

                            <span class="text-sm text-gray-600" x-text="formatDate(evento.created_at)"></span>

                            <span class="text-[11px] px-2 py-0.5 rounded-full font-medium"
                                :class="getClass(evento.tipo_evento)"
                                x-text="evento.tipo_evento.replace('_', ' ').toUpperCase()">
                            </span>
                        </div>
                    </div>
                    <span class="text-[11px] text-gray-400 whitespace-nowrap" x-text="'ID #' + evento.id"></span>
                </div>

                <template x-if="evento.tipo_evento === 'estado_cambiado'">
                    <div class="mt-3 rounded-md bg-gray-50 p-2 text-sm text-gray-600 flex content-center">
                        <div class="content-center w-full grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="inline-flex items-center">
                                <template x-if="evento.old_value">
                                    <span class="font-semibold" x-text="evento.old_value"></span>
                                </template>

                                <template x-if="evento.new_value">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                        </svg>
                                        <span class="font-semibold" x-text="evento.new_value"></span>
                                    </div>
                                </template>
                            </div>

                            <div class="space-y-1">
                                <div class="text-gray-600 font-medium">Detalles:</div>
                                <div x-text="evento.detalles" class="text-gray-700"></div>
                            </div>

                        </div>
                    </div>
                </template>

                <template
                    x-if="evento.tipo_evento !== 'estado_cambiado' && evento.tipo_evento !== 'archivo_subido' && evento.detalles">
                    <div class="mt-2 text-md text-gray-700" x-text="evento.detalles"></div>
                </template>

                <template x-if="evento.tipo_evento === 'archivo_subido'">
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div>
                            <template x-if="evento.new_value">
                                <a :href="'/ordenes_escaneos/' + selectedOrden?.id + '/' + evento.new_value"
                                    target="_blank"
                                    class="inline-flex items-center gap-1 text-emerald-600 hover:text-emerald-700 underline decoration-emerald-300">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                        </path>
                                    </svg>
                                    <span x-text="evento.new_value"></span>
                                </a>
                            </template>
                        </div>

                        <div x-show="evento.comentario_archivo">
                            <div class="text-gray-600 font-medium">Comentario:</div>
                            <div x-text="evento.comentario_archivo" class="text-gray-700"></div>
                        </div>


                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
