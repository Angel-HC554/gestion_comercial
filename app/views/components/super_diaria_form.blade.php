<div class="bg-white rounded-lg shadow-sm border border-zinc-300 overflow-hidden"
     x-data="supervisionDiariaApp('{{ $vehiculo_id }}', '{{ $no_economico }}')">
    
    <div class="px-6 py-3 border-b border-zinc-300 bg-gray-50 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-bold text-emerald-800">Supervisión Diaria</h3>
            <p class="text-sm text-zinc-500">Vehículo: <span class="font-bold text-zinc-700">{{ $no_economico }}</span></p>
        </div>
        <div class="text-sm font-medium text-zinc-600">
            Paso <span x-text="step"></span> de 4
        </div>
    </div>

    <form @submit.prevent="submitForm" class="p-6">
        
        <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
            <h4 class="text-md font-semibold text-zinc-700 mb-4 flex items-center">
                <span class="bg-emerald-100 text-emerald-700 w-6 h-6 rounded-full flex items-center justify-center text-xs mr-2">1</span>
                Datos Generales
            </h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Auxiliar Verificador</label>
                    <input type="text" x-model="form.nombre_auxiliar" class="w-full rounded-md border-2 border-zinc-300 focus:outline-none focus:border-emerald-600 focus:ring-emerald-500 sm:text-sm py-2 px-3" placeholder="Ingrese el nombre del auxiliar">
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Fecha</label>
                    <input type="date" x-model="form.fecha" class="w-full rounded-md border-zinc-300 focus:outline-none focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm py-2 px-3 border-2"
                    x-bind:max="maxDate" @blur="validateFecha()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Hora Inicio</label>
                    <input type="time" x-model="form.hora_inicio" class="w-full rounded-md border-zinc-300 focus:outline-none focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm py-2 px-3 border-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Hora Fin</label>
                    <input type="time" x-model="form.hora_fin" class="w-full rounded-md border-zinc-300 focus:outline-none focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm py-2 px-3 border-2">
                </div>
            </div>
        </div>

        <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" style="display: none;">
            <h4 class="text-md font-semibold text-zinc-700 mb-4 flex items-center">
                <span class="bg-emerald-100 text-emerald-700 w-6 h-6 rounded-full flex items-center justify-center text-xs mr-2">2</span>
                Lecturas Clave
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Kilometraje Actual</label>
                    <div class="relative rounded-md">
                        <input type="text" x-model="form.kilometraje" class="block w-full rounded-md border-zinc-300 pl-3 pr-12 focus:outline-none focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm py-2 border-2 mask-km" placeholder="0">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <span class="text-zinc-500 sm:text-sm">km</span>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="mx-10">
                        @include('components.gasolina-slider', ['gasolina' => 0])
                    </div>
                </div>
            </div>
        </div>

        <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" style="display: none;">
            <h4 class="text-md font-semibold text-zinc-700 mb-4 flex items-center">
                <span class="bg-emerald-100 text-emerald-700 w-6 h-6 rounded-full flex items-center justify-center text-xs mr-2">3</span>
                Checklist de Verificación
            </h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[500px] overflow-y-auto p-1">
                <template x-for="(label, key) in checklistItems" :key="key">
                    <div class="flex items-center justify-between p-3 bg-white border-2 border-zinc-300 rounded-lg shadow-sm hover:border-emerald-500 transition-colors">
                        <span class="text-sm font-medium text-zinc-700" x-text="label"></span>
                        
                        <div class="flex items-center bg-gray-100 rounded-lg p-1">
                            <button type="button" 
                                @click="form[key] = '1'"
                                :class="form[key] === '1' ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-400 hover:text-gray-600'"
                                class="px-3 py-1 rounded-md text-xs font-bold transition-all">
                                BIEN
                            </button>
                            <button type="button" 
                                @click="form[key] = '0'"
                                :class="form[key] === '0' ? 'bg-white text-red-500 shadow-sm' : 'text-gray-400 hover:text-gray-600'"
                                class="px-3 py-1 rounded-md text-xs font-bold transition-all">
                                MAL
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="step === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" style="display: none;">
            <h4 class="text-md font-semibold text-zinc-700 mb-4 flex items-center">
                <span class="bg-emerald-100 text-emerald-700 w-6 h-6 rounded-full flex items-center justify-center text-xs mr-2">4</span>
                Reporte y Evidencia
            </h4>

            <div class="space-y-6">
                <div class="bg-zinc-50 p-4 rounded-lg border border-zinc-200">
                    <label class="block text-sm font-bold text-zinc-700 mb-2">¿El vehículo presenta golpes nuevos?</label>
                    <div class="flex gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" x-model="form.golpes" value="1" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                            <span class="ml-2 text-sm text-zinc-700">Sí, hay daños</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" x-model="form.golpes" value="0" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                            <span class="ml-2 text-sm text-zinc-700">No, está bien</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Observaciones</label>
                    <textarea x-model="form.golpes_coment" rows="3" class="w-full rounded-md border-zinc-300 focus:outline-none focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm p-2 border-2" placeholder="Describe cualquier anomalía..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Subir formato entregado por el auxiliar</label>
                    <input type="file" @change="handleFileUpload" class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                </div>
            </div>
        </div>

        <div class="mt-8 pt-4 border-t border-zinc-200 flex justify-between">
            <button type="button" 
                x-show="step > 1" 
                @click="step--"
                class="px-4 py-2 border border-zinc-300 shadow-sm text-sm font-medium rounded-md text-zinc-700 bg-white hover:bg-zinc-50 focus:outline-none cursor-pointer">
                Anterior
            </button>
            <div x-show="step === 1" class="flex-grow"></div> <button type="button" 
                x-show="step < 4" 
                @click="step++"
                class="ml-auto px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none shadow-sm flex items-center cursor-pointer">
                Siguiente
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>

            <button type="submit" 
                x-show="step === 4" 
                class="ml-auto px-6 py-2 border border-transparent text-sm font-bold rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none shadow-lg flex items-center transition-all transform hover:scale-105 cursor-pointer"
                :disabled="loading">
                <span x-show="!loading">GUARDAR SUPERVISIÓN</span>
                <span x-show="loading" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Procesando...
                </span>
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
            // Lista completa de items para el loop de Alpine
            checklistItems: {
                'aceite': 'Nivel de Aceite',
                'liq_fren': 'Líquido de Frenos',
                'anti_con': 'Anticongelante',
                'agua': 'Nivel de Agua',
                'radiador': 'Radiador',
                'llantas': 'Estado de Llantas',
                'llanta_r': 'Llanta Refacción',
                'tapon_gas': 'Tapón Gasolina',
                'limp_cab': 'Limpieza Cabina',
                'limp_ext': 'Limpieza Exterior',
                'cinturon': 'Cinturones',
                'limpia_par': 'Limpiaparabrisas',
                'manijas_puer': 'Manijas Puertas',
                'espejo_int': 'Espejo Interior',
                'espejo_lat_i': 'Espejo Lat. Izq',
                'espejo_lat_d': 'Espejo Lat. Der',
                'gato': 'Gato Hidráulico',
                'llave_cruz': 'Llave de Cruz',
                'extintor': 'Extintor',
                'direccionales': 'Direccionales',
                'luces': 'Luces Principales',
                'intermit': 'Intermitentes'
            },
            // Estado del formulario
            form: {
                vehiculo_id: vehiculoId,
                no_eco: noEco,
                nombre_auxiliar: '',
                fecha: new Date().toLocaleDateString('en-CA'),
                hora_inicio: '08:00',
                hora_fin: '08:30',
                kilometraje: '',
                gasolina: '0',
                golpes: '0',
                golpes_coment: '',
                // Inicializamos todos los checks en '1' (Bien)
                aceite:'1', liq_fren:'1', anti_con:'1', agua:'1', radiador:'1', llantas:'1', llanta_r:'1',
                tapon_gas:'1', limp_cab:'1', limp_ext:'1', cinturon:'1', limpia_par:'1', manijas_puer:'1',
                espejo_int:'1', espejo_lat_i:'1', espejo_lat_d:'1', gato:'1', llave_cruz:'1', extintor:'1',
                direccionales:'1', luces:'1', intermit:'1'
            },
            maxDate: new Date().toLocaleDateString('en-CA'),
            validateFecha() {
                // Si no hay fecha, no hacemos nada
                if (!this.form.fecha) return;

                // Comparamos cadenas (YYYY-MM-DD)
                if (this.form.fecha > this.maxDate) {
                    // Opción A: Resetear a HOY
                    this.form.fecha = this.maxDate;

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

            handleFileUpload(event) {
                this.file = event.target.files[0];
            },

            async submitForm() {
                this.loading = true;
                
                // Creamos FormData para enviar archivo + datos
                const formData = new FormData();
                
                // Agregamos todos los campos del objeto form
                for (const key in this.form) {
                    formData.append(key, this.form[key]);
                }

                const inputGasolinaComponente = document.querySelector('input[name="gasolina"]');
                if (inputGasolinaComponente) {
                    formData.set('gasolina', inputGasolinaComponente.value);
                }
                
                // Agregamos el archivo si existe
                if (this.file) {
                    formData.append('escaneo_url', this.file);
                }

                try {
                    const response = await fetch('/supervision-diaria', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Error en la solicitud');
                    }

                    if (result.status === 'success') {
                        Swal.fire({
                            title: '¡Guardado!',
                            text: result.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', result.message || 'Ocurrió un error inesperado', 'error');
                    }
                } catch (error) {
                    console.error(error);
                    Swal.fire({
                        title: 'Error',
                        text: error.message || 'No se pudo conectar con el servidor',
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                    
                    // Si el error es de validación de kilometraje, regresar al paso 2
                    if (error.message && error.message.includes('kilometraje')) {
                        this.step = 2;
                    }
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>