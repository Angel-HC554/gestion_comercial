<div x-data="expedienteApp({{ $vehiculo->id }})" x-cloak class="bg-white rounded-lg shadow-sm border border-zinc-200 overflow-hidden mb-8">
    
    <div class="px-6 py-4 border-b border-zinc-200 bg-zinc-50 flex justify-between items-center">
        <div>
            <h2 class="text-lg font-bold text-zinc-900">Expediente Digital</h2>
            <p class="text-sm text-zinc-500">Documentos del vehículo <span class="font-bold text-emerald-700">{{ $vehiculo->no_economico }}</span></p>
        </div>
        
        <button @click="openUpload = !openUpload" 
            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors shadow-sm cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 transition-transform duration-200" :class="openUpload ? 'rotate-180' : ''">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
            Subir Documento
        </button>
    </div>

    <div x-show="openUpload" x-collapse class="border-b border-zinc-200 bg-white px-6 py-5">
        <form @submit.prevent="subirDocumento" class="flex flex-col sm:flex-row gap-4 items-end">
            <div class="w-full sm:w-1/3">
                <label class="block text-sm font-medium text-zinc-700 mb-1">Nombre del Documento <span class="text-red-500">*</span></label>
                <input type="text" x-model="nombreDoc" placeholder="Ej. Tarjeta de Circulación 2026" required
                    class="w-full border-2 border-zinc-300 rounded-md focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 text-sm p-2 h-10">
            </div>

            <div class="w-full sm:w-1/2">
                <label class="block text-sm font-medium text-zinc-700 mb-1">Archivo (Solo PDF) <span class="text-red-500">*</span></label>
                <input type="file" x-ref="archivoPdf" accept="application/pdf" required
                    class="w-full text-sm text-zinc-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border-2 border-dashed border-zinc-300 rounded-lg p-2 h-10 bg-zinc-50 cursor-pointer focus:outline-none focus:border-emerald-500 transition-colors">
            </div>

            <div class="w-full sm:w-auto">
                <button type="submit" :disabled="uploading"
                    class="w-full sm:w-auto px-4 py-2 bg-emerald-600 text-white hover:bg-emerald-700 rounded-md text-sm font-medium shadow-sm transition-colors cursor-pointer disabled:opacity-50 h-10 flex items-center justify-center gap-2">
                    <span x-show="!uploading">Guardar</span>
                    <span x-show="uploading">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </span>
                </button>
            </div>
        </form>
    </div>

    <div class="p-6 bg-zinc-50/50">
        <h3 class="text-sm font-bold text-zinc-500 uppercase tracking-wider mb-4">Documentos Guardados</h3>
        
        <div x-show="loadingDocs" class="text-center py-4">
            <svg class="animate-spin h-6 w-6 text-emerald-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        </div>

        <div x-show="!loadingDocs && documentos.length === 0" class="text-center py-8 border-2 border-dashed border-zinc-300 rounded-lg bg-white">
            <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-zinc-900">No hay documentos</h3>
            <p class="mt-1 text-sm text-zinc-500">Aún no se ha subido ningún archivo a este expediente.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" x-show="!loadingDocs && documentos.length > 0">
            <template x-for="doc in documentos" :key="doc.id">
                <div class="flex items-center justify-between p-3 bg-white border border-zinc-200 rounded-lg shadow-sm hover:border-emerald-300 transition-colors group">
                    <div class="flex items-center space-x-3 overflow-hidden">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-red-500" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M8.267 14.68c-.184 0-.308.018-.372.036v1.178c.076.018.171.023.302.023.479 0 .774-.242.774-.651 0-.366-.254-.586-.704-.586zm3.487.012c-.2 0-.33.018-.407.036v2.61c.077.018.201.018.313.018.817.006 1.349-.444 1.349-1.396.006-.83-.479-1.268-1.255-1.268z"/>
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM9.498 16.19c-.309.29-.765.42-1.296.42a2.23 2.23 0 0 1-.308-.018v1.426H7v-3.936A7.558 7.558 0 0 1 8.219 14c.557 0 .953.106 1.22.319.254.202.426.533.426.923-.001.392-.131.723-.367.948zm3.807 1.355c-.42.349-1.059.515-1.84.515-.468 0-.799-.03-1.024-.06v-3.917A7.947 7.947 0 0 1 11.66 14c.757 0 1.249.136 1.633.426.415.308.675.799.675 1.504 0 .763-.279 1.29-.663 1.615zM17 14.77h-1.632v.84h1.094v.661h-1.094v1.71h-.846V14h2.478v.77z"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-zinc-900 truncate" x-text="doc.nombre"></p>
                            <p class="text-xs text-zinc-500" x-text="'Subido el ' + formatearFecha(doc.created_at)"></p>
                        </div>
                    </div>
                    <a :href="doc.url" target="_blank" title="Ver documento"
                        class="ml-2 flex-shrink-0 text-zinc-400 hover:text-emerald-600 cursor-pointer p-1">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    function expedienteApp(vehiculoId) {
        return {
            vehiculoId: vehiculoId,
            openUpload: false,
            uploading: false,
            loadingDocs: true,
            documentos: [],
            nombreDoc: '',

            init() {
                this.fetchDocumentos();
            },

            async fetchDocumentos() {
                this.loadingDocs = true;
                try {
                    // Asegúrate de crear esta ruta en tu backend
                    const response = await fetch(`/vehiculos/${this.vehiculoId}/documentos`);
                    if (response.ok) {
                        this.documentos = await response.json();
                    }
                } catch (error) {
                    console.error("Error al cargar documentos:", error);
                } finally {
                    this.loadingDocs = false;
                }
            },

            async subirDocumento() {
                const archivoInput = this.$refs.archivoPdf;
                
                if (!archivoInput.files.length) {
                    alert("Por favor selecciona un archivo PDF.");
                    return;
                }

                this.uploading = true;
                
                const formData = new FormData();
                formData.append('nombre', this.nombreDoc);
                formData.append('archivo', archivoInput.files[0]);
                
                // Obtenemos el token CSRF global de Laravel
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                            || document.querySelector('input[name="_token"]')?.value;
                if (token) {
                    formData.append('_token', token);
                }

                try {
                    const response = await fetch(`/vehiculos/${this.vehiculoId}/documentos`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // Limpiamos el formulario
                        this.nombreDoc = '';
                        archivoInput.value = '';
                        this.openUpload = false; // Cerramos el acordeón
                        
                        // Recargamos la lista
                        this.fetchDocumentos();
                        
                        // Opcional: Si usas SweetAlert2
                        if (window.Swal) {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Documento subido', showConfirmButton: false, timer: 3000 });
                        }
                    } else {
                        alert(data.message || "Error al subir el archivo.");
                    }
                } catch (error) {
                    console.error("Error al subir:", error);
                    alert("Ocurrió un error de conexión.");
                } finally {
                    this.uploading = false;
                }
            },

            formatearFecha(fechaStr) {
                if (!fechaStr) return '';
                const fechaLocal = new Date(fechaStr);
                return fechaLocal.toLocaleDateString('es-MX', {
                    day: '2-digit', month: 'short', year: 'numeric'
                }).toUpperCase().replace(/\./g, '');
            }
        }
    }
</script>