<div x-data="importModal()"
     @open-import-modal.window="isOpen = true"
     x-show="isOpen"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">

    <div class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden transform transition-all"
         @click.away="close()">
        
        <div class="p-6 border-b border-zinc-200 flex justify-between items-center">
            <h3 class="text-xl font-bold text-zinc-900">Importar Vehículos</h3>
            <button @click="close()" class="text-zinc-400 hover:text-zinc-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="p-6">
            <p class="text-sm text-zinc-600 mb-4">
                Sube un archivo Excel (.xlsx) con las columnas: <br>
                <span class="text-xs font-mono bg-gray-100 p-1 rounded">Agencia, No. Eco, Placas, Tipo, Marca, Modelo, Año, Estado, Propiedad, Proceso, Alias, rpe_cremod</span>
            </p>

            <form @submit.prevent="submitImport">
                <div class="flex items-center justify-center w-full">
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 border-gray-300 hover:border-emerald-500 transition-colors">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <template x-if="!fileName">
                                <div class="flex flex-col items-center">
                                    <svg class="w-8 h-8 mb-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/></svg>
                                    <p class="text-sm text-gray-500"><span class="font-semibold">Clic para subir</span></p>
                                    <p class="text-xs text-gray-500">XLSX, XLS (MAX. 5MB)</p>
                                </div>
                            </template>
                            <template x-if="fileName">
                                <div class="flex flex-col items-center">
                                    <svg class="w-8 h-8 mb-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-sm font-medium text-gray-900" x-text="fileName"></p>
                                </div>
                            </template>
                        </div>
                        <input type="file" class="hidden" accept=".xlsx, .xls" @change="handleFile">
                    </label>
                </div>

                <div x-show="uploading" class="mt-4">
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-emerald-600 h-2.5 rounded-full animate-pulse" style="width: 100%"></div>
                    </div>
                    <p class="text-xs text-center text-emerald-700 mt-1">Procesando archivo...</p>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="close()" class="px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 rounded-md transition-colors">Cancelar</button>
                    <button type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-md shadow-sm transition-colors flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!file || uploading">
                        <span x-show="!uploading">Importar</span>
                        <span x-show="uploading">Importando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function importModal() {
        return {
            isOpen: false,
            file: null,
            fileName: '',
            uploading: false,

            close() {
                this.isOpen = false;
                this.resetForm();
            },

            resetForm() {
                this.file = null;
                this.fileName = '';
                this.uploading = false;
            },

            handleFile(event) {
                const file = event.target.files[0];
                if (file) {
                    this.file = file;
                    this.fileName = file.name;
                }
            },

            async submitImport() {
                if (!this.file) return;

                this.uploading = true;
                const formData = new FormData();
                formData.append('archivoExcel', this.file);

                try {
                    const response = await fetch('/vehiculos/import', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.status === 'success') {
                        // Disparar evento para que el componente padre (index) recargue la tabla y muestre alerta
                        this.$dispatch('import-success', { message: result.message });
                        this.close();
                    } else {
                        Swal.fire('Error', result.message || 'Error al importar', 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', 'Error de conexión', 'error');
                    console.error(error);
                } finally {
                    this.uploading = false;
                }
            }
        }
    }
</script>