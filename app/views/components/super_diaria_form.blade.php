<style>
    /* Estilos del formulario Diario */
    .form-diaria-container input[type="radio"]:checked + label {
        display: inline-flex; align-items: center; justify-content: center;
        width: 24px; height: 24px; border-radius: 4px; font-weight: bold;
    }
    .form-diaria-container input[type="radio"][value="1"]:checked + label { background-color: #10B981; color: white; }
    .form-diaria-container input[type="radio"][value="0"]:checked + label { background-color: #EF4444; color: white; }
    
    .form-diaria-container input, .form-diaria-container select, .form-diaria-container textarea {
        width: 100%; border-radius: 0.5rem; border: 1px solid #d1d5db; padding: 0.625rem;
    }
    .form-diaria-container input:focus { border-color: #059669; ring: 2px; outline: none; }
</style>

<div class="form-diaria-container bg-white p-6 rounded-lg"
     x-data="supervisionDiariaApp('{{ $vehiculo_id }}', '{{ $no_economico }}')">

    <div class="border-b border-gray-200 pb-4 mb-4">
        <h3 class="text-lg font-medium text-emerald-800">Registro de Verificación Diaria</h3>
    </div>

    <form @submit.prevent="submitDiaria">
        
        <div class="mb-6 border border-gray-200 rounded-lg overflow-hidden">
            <button type="button" @click="step = (step === 1 ? 0 : 1)" class="w-full flex justify-between p-4 bg-gray-50 font-semibold text-emerald-800">
                1. Información General
                <span x-text="step === 1 ? '▲' : '▼'"></span>
            </button>
            <div x-show="step === 1" class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Auxiliar Verificador</label>
                    <select x-model="form.nombre_auxiliar" required>
                        <option value="">Seleccione...</option>
                        <option value="JORGE MORENO">JORGE MORENO</option>
                        <option value="OPERADOR TURNO">OPERADOR TURNO</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Fecha</label>
                    <input type="date" x-model="form.fecha" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Hora Inicio</label>
                    <input type="time" x-model="form.hora_inicio" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Hora Fin</label>
                    <input type="time" x-model="form.hora_fin" required>
                </div>
            </div>
        </div>

        <div class="mb-6 border border-gray-200 rounded-lg overflow-hidden">
            <button type="button" @click="step = (step === 2 ? 0 : 2)" class="w-full flex justify-between p-4 bg-gray-50 font-semibold text-emerald-800">
                2. Lecturas Clave
                <span x-text="step === 2 ? '▲' : '▼'"></span>
            </button>
            <div x-show="step === 2" class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Kilometraje</label>
                    <input type="number" x-model="form.kilometraje" placeholder="Ej: 150000" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Nivel Gasolina</label>
                    <select x-model="form.gasolina" required>
                        <option value="0">Reserva</option>
                        <option value="25">1/4</option>
                        <option value="50">1/2</option>
                        <option value="75">3/4</option>
                        <option value="100">Lleno</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mb-6 border border-gray-200 rounded-lg overflow-hidden">
            <button type="button" @click="step = (step === 3 ? 0 : 3)" class="w-full flex justify-between p-4 bg-gray-50 font-semibold text-emerald-800">
                3. Checklist de Verificación
                <span x-text="step === 3 ? '▲' : '▼'"></span>
            </button>
            <div x-show="step === 3" class="p-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Componente</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Estado (Bien / Mal)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <template x-for="(label, key) in checklistItems" :key="key">
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-900" x-text="label"></td>
                                    <td class="px-3 py-2 text-center">
                                        <div class="flex items-center justify-center space-x-4">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="radio" :name="key" value="1" x-model="form[key]" class="hidden">
                                                <span :class="form[key] == '1' ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-400'" class="px-2 py-1 rounded">✓</span>
                                            </label>
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="radio" :name="key" value="0" x-model="form[key]" class="hidden">
                                                <span :class="form[key] == '0' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-400'" class="px-2 py-1 rounded">✗</span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mb-6 border border-gray-200 rounded-lg overflow-hidden">
            <button type="button" @click="step = (step === 4 ? 0 : 4)" class="w-full flex justify-between p-4 bg-gray-50 font-semibold text-emerald-800">
                4. Reporte y Evidencia
                <span x-text="step === 4 ? '▲' : '▼'"></span>
            </button>
            <div x-show="step === 4" class="p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">¿Hay golpes?</label>
                    <div class="mt-2 space-x-4">
                        <label><input type="radio" x-model="form.golpes" value="1"> Sí</label>
                        <label><input type="radio" x-model="form.golpes" value="0"> No</label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Comentarios</label>
                    <textarea x-model="form.golpes_coment" rows="3"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Adjuntar Escaneo (PDF/Img)</label>
                    <input type="file" @change="handleFileUpload">
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-5">
            <button type="submit" 
                class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-6 rounded-lg transition-colors flex items-center disabled:opacity-50"
                :disabled="loading">
                <span x-show="!loading">Guardar Supervisión</span>
                <span x-show="loading">Guardando...</span>
            </button>
        </div>
    </form>
</div>

<script>
    function supervisionDiariaApp(vehiculoId, noEco) {
        return {
            step: 1,
            loading: false,
            file: null,
            checklistItems: {
                'aceite': 'Nivel de Aceite', 'liq_fren': 'Líquido de Frenos', 'anti_con': 'Anticongelante',
                'agua': 'Agua', 'radiador': 'Radiador', 'llantas': 'Llantas', 'llanta_r': 'Llanta Refacción',
                'tapon_gas': 'Tapón Gasolina', 'limp_cab': 'Limpieza Cabina', 'limp_ext': 'Limpieza Exterior',
                'cinturon': 'Cinturón', 'limpia_par': 'Limpiaparabrisas', 'luces': 'Luces', 'intermit': 'Intermitentes'
            },
            form: {
                vehiculo_id: vehiculoId, no_eco: noEco,
                nombre_auxiliar: '', fecha: new Date().toISOString().split('T')[0],
                hora_inicio: '08:00', hora_fin: '08:30', kilometraje: '', gasolina: '50',
                golpes: '0', golpes_coment: '',
                // Default checks en 1 (Bien)
                aceite:'1', liq_fren:'1', anti_con:'1', agua:'1', radiador:'1', llantas:'1', llanta_r:'1',
                tapon_gas:'1', limp_cab:'1', limp_ext:'1', cinturon:'1', limpia_par:'1', manijas_puer:'1',
                espejo_int:'1', espejo_lat_i:'1', espejo_lat_d:'1', gato:'1', llave_cruz:'1', extintor:'1',
                direccionales:'1', luces:'1', intermit:'1'
            },
            
            handleFileUpload(e) {
                this.file = e.target.files[0];
            },

            async submitDiaria() {
                this.loading = true;
                const formData = new FormData();
                
                // Append form data
                for (const key in this.form) {
                    formData.append(key, this.form[key]);
                }
                if (this.file) {
                    formData.append('escaneo_url', this.file);
                }

                try {
                    const response = await fetch('/supervision-diaria', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.status === 'success') {
                        Swal.fire({ title: 'Éxito', text: result.message, icon: 'success', timer: 1500, showConfirmButton: false });
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        Swal.fire('Error', result.message || 'Error desconocido', 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', 'Error de conexión con el servidor', 'error');
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>