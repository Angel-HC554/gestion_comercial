<div x-data="exportModal()"
     @open-export-modal.window="isOpen = true"
     x-show="isOpen"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">

    <div class="bg-white rounded-lg shadow-xl w-full max-w-sm overflow-hidden transform transition-all"
         @click.away="close()">
        
        <div class="p-6 border-b border-zinc-200">
            <h3 class="text-xl font-bold text-zinc-900">Exportar Vehículos</h3>
        </div>

        <div class="p-6">
            <p class="text-sm text-zinc-600 mb-4">
                Se generará un archivo Excel (.xlsx) con el listado completo de vehículos actuales.
            </p>
            
            <div class="bg-emerald-50 border border-emerald-200 rounded p-3 mb-4">
                <p class="text-xs text-emerald-800">
                    <strong>Nota:</strong> Este archivo tiene el formato correcto para ser editado y vuelto a importar.
                </p>
            </div>

            <div class="flex justify-end gap-3 mt-4">
                <button type="button" @click="close()" class="px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 rounded-md transition-colors">
                    Cancelar
                </button>
                
                <button @click="download()" 
                    class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-md shadow-sm transition-colors flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Descargar Excel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function exportModal() {
        return {
            isOpen: false,

            close() {
                this.isOpen = false;
            },

            download() {
                // Redirige al navegador a la ruta de descarga
                // Esto disparará la descarga del archivo
                window.location.href = '/vehiculos/export';
                
                // Opcional: Cerrar el modal después de un momento
                setTimeout(() => this.close(), 1000);
            }
        }
    }
</script>