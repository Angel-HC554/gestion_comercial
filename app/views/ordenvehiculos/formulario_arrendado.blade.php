<style>
    /* Contenedor de la cuadrícula para las fotos */
    .photo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    /* Estilo para cada campo de subida de archivo */
    .upload-box {
        border: 2px dashed #ccc;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.3s, background-color 0.3s;
        height: 150px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .upload-box:hover {
        border-color: #007bff;
        background-color: #f8f9fa;
    }

    .upload-box label {
        color: #555;
        font-size: 14px;
        font-weight: bold;
    }

    .upload-box input[type="file"] {
        display: none;
    }

    .upload-box .thumbnail {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        /* display: none; */
        /* border-radius: 6px; */
    }
    .img-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0,0,0,0.5);
        color: white;
        font-size: 10px;
        padding: 4px;
        text-align: center;
    }
</style>
<div class="mx-10 shadow-lg rounded-lg p-8 bg-white border border-zinc-200" x-data="{
    // Datos BD
    vehiculosDB: {{ json_encode($vehiculos ?? []) }},
    maxDate: new Date().toLocaleDateString('en-CA'),
    // Validación de KM
    // Si viene del show (tiene valor inicial), úsalo. Si no, empieza en 0.
    minKilometraje: {{ $ultimoKm ?? 0 }},

    // Estado del Formulario
    loading: false,
    showModal: false,
    ordenId: null,
    returnUrl: '{{ $returnUrl ?? '/ordenvehiculos' }}',

    // Variables de autocompletado
    numeco: '{{ old('noeconomico', $ordenEditar->noeconomico ?? ($preseleccionado['no_economico'] ?? '')) }}',
    placa: '{{ old('placas', $ordenEditar->placas ?? ($preseleccionado['placas'] ?? '')) }}',
    kilometraje: '{{ old('kilometraje', $ordenEditar->kilometraje ?? '') }}',
    marca: '{{ old('marca', $ordenEditar->marca ?? (isset($preseleccionado) ? $preseleccionado['marca'] . ' ' . $preseleccionado['modelo'] : '')) }}',
    no_serie: '{{ old('no_serie', $ordenEditar->detalleArrendado->no_serie ?? (isset($preseleccionado) ? $preseleccionado['no_serie'] : '')) }}',

    // 1. Buscar Vehículo
    buscarVehiculo() {
        let encontrado = this.vehiculosDB.find(v => v.no_economico == this.numeco);
        if (encontrado) {
            this.placa = encontrado.placas;
            this.marca = encontrado.marca + ' ' + (encontrado.modelo || '');
            this.no_serie = encontrado.serie;
            this.minKilometraje = parseInt(encontrado.ultimo_km) || 0;
        }else{
            this.minKilometraje = 0;
            this.no_serie = '';
            this.marca = '';
            this.placa = '';
        }
    },

    // 3. ENVIAR FORMULARIO (AJAX)
    async submitForm() {
        this.loading = true;

        // Captura todos los campos del formulario automáticamente
        const form = document.getElementById('ordenForm');
        const formData = new FormData(form);
        // --- NUEVA VALIDACIÓN DE KILOMETRAJE ---
        // Obtenemos el valor y quitamos las comas
        let kmInput = formData.get('kilometraje').toString().replace(/,/g, '');
        let kmActual = parseInt(kmInput) || 0;

        // Solo validamos si es creación (no edición) y si tenemos un minKilometraje
        const isEdit = '{{ isset($ordenEditar) }}' === '1';

        if (!isEdit && kmActual <= this.minKilometraje && kmActual !== 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Kilometraje incorrecto',
                text: 'El kilometraje (' + new Intl.NumberFormat().format(kmActual) + ' km) no puede ser menor o igual al último registrado (' + new Intl.NumberFormat().format(this.minKilometraje) + ' km).',
            });
            this.loading = false;
            return; // Detenemos el envío
        }
        
        // ----------------------------------------
        try {
            // Determinar si es edición o creación
            
            const url = isEdit ?
                `/ordenvehiculos/arrendado/update/${'{{ $ordenEditar->id ?? '' }}'}` :
                '/ordenvehiculos/store_arrendado';
            const method = 'POST';

            // A. Guardar/Actualizar Orden
            const response = await fetch(url, {
                method: method,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData // Enviar FormData directamente
            });

            const result = await response.json();

            if (result.status === 'success') {
                this.ordenId = result.id;
                // C. Mostrar Modal
                this.showModal = true;
            } else {
                Swal.fire('Error', result.message || 'Error al procesar la solicitud', 'error');
            }
        } catch (error) {
            console.error(error);
            Swal.fire('Error', 'Error de conexión', 'error');
        } finally {
            this.loading = false;
        }
    }
}">
    <form id="ordenForm" @submit.prevent="submitForm" enctype="multipart/form-data">

        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mx-5 my-5">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Municipio y estado de origen</label>
                <input type="text" name="mun_estado_origen" list="areas-list" required
                    class="appearance-none block w-full px-2 py-2 border-2 border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors"
                    value="{{ old('mun_estado_origen', $ordenEditar->detalleArrendado->mun_estado_origen ?? 'MERIDA, YUCATAN') }}">
                <datalist id="areas-list">
                    <option value="MERIDA, YUCATAN"></option>
                </datalist>
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Municipio y estado para el servicio</label>
                <input type="text" name="mun_estado_servicio" required
                    class="appearance-none block w-full px-2 py-2 border-2 border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors"
                    value="{{ old('mun_estado_servicio', $ordenEditar->mun_estado_servicio ?? 'MERIDA, YUCATAN') }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">No. Económico</label>
                <input type="text" name="noeconomico" list="economicos-list" required x-model="numeco"
                    @input="buscarVehiculo()"
                    class="appearance-none block w-full px-2 py-2 border-2 border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors"
                    placeholder="Ej. 12345">
                <datalist id="economicos-list">
                    <template x-for="v in vehiculosDB" :key="v.no_economico">
                        <option :value="v.no_economico"></option>
                    </template>
                </datalist>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mx-5 my-5">

            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Marca y tipo</label>
                <input type="text" name="marca" required x-model="marca" readonly
                    class="appearance-none block w-full px-2 py-2 border-2 border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors">
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Placas</label>
                <input type="text" name="placas" required x-model="placa" readonly
                    class="appearance-none block w-full px-2 py-2 border-2 border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors">
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">No. serie</label>
                <input type="text" name="no_serie" required x-model="no_serie" readonly
                    class="appearance-none block w-full px-2 py-2 border-2 border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mx-5 my-5">

            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Kilometraje</label>
                <div class="relative rounded-md">
                    <input type="text" name="kilometraje" x-model="kilometraje" required
                        class="appearance-none block w-full pl-3 pr-12 py-2 border-2 border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors mask-km"
                        value="{{ old('kilometraje', $ordenEditar->kilometraje ?? '') }}">

                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">km</span>
                    </div>
                </div>
                {{-- 2. Ayuda visual Dinámica --}}
                {{-- Usamos h-5 para reservar el espacio y evitar saltos de layout --}}
                <div class="mt-1 h-5 flex items-center">
                    {{-- Solo mostramos si hay un minKilometraje > 0 y NO estamos editando una orden vieja --}}
                    <span x-show="minKilometraje > 0 && !{{ isset($ordenEditar) ? 'true' : 'false' }}"
                        x-transition.opacity.duration.300ms
                        class="text-xs font-medium text-emerald-600 flex items-center gap-1" style="display: none;">
                        {{-- style="display: none" evita parpadeo al cargar --}}

                        {{-- Icono pequeño de info --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>

                        {{-- Texto con formato --}}
                        <span>
                            Último registrado: <strong
                                x-text="new Intl.NumberFormat('es-MX').format(minKilometraje)"></strong> km
                        </span>
                    </span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Tipo de servicio a solicitar</label>
                <input type="text" name="tipo_servicio" required
                    class="appearance-none block w-full px-2 py-2 border-2 border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors"
                    value="{{ old('tipo_servicio', $ordenEditar->detalleArrendado->tipo_servicio ?? '') }}" placeholder="Servicio a solicitar" list="servicios">
                <datalist id="servicios">
                    <option value="SERVICIO DE MANTENIMIENTO PREVENTIVO"></option>
                </datalist>
            </div>
            <div class="space-y-4 flex justify-start">
                <div x-data="{ checked: {{ isset($ordenEditar) && $ordenEditar->requiere_servicio ? 'true' : 'false' }} }">
                    <span class="block text-sm font-medium text-zinc-700 mb-1">Servicio por kilometraje:</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="requiere_servicio" value="0">
                        <input type="checkbox" name="requiere_servicio" value="1" class="sr-only"
                            x-model="checked">

                        <div class="w-11 h-6 rounded-full transition-colors duration-200 ease-in-out focus:outline-none ring-offset-2 focus:ring-2 focus:ring-emerald-300"
                            :class="checked ? 'bg-emerald-600' : 'bg-zinc-200'">
                            <div class="bg-white border border-gray-300 rounded-full h-5 w-5 shadow transition-transform duration-200 ease-in-out mt-[2px] ml-[2px]"
                                :class="checked ? 'translate-x-5 border-transparent' : 'translate-x-0'"></div>
                        </div>

                        <span class="ml-3 text-sm font-medium text-zinc-700" x-text="checked ? 'Sí' : 'No'">
                        </span>
                    </label>
                </div>
            </div>

            

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <input type="hidden" name="fecha_gen"
                        value="{{ old('fecha_gen', $ordenEditar->fecha_gen ?? date('Y-m-d')) }}">
                </div>
            </div>
        </div>

        <hr class="my-8 border-zinc-200 mx-5">

        <div class="mx-5">
            <h3 class="text-lg font-semibold text-zinc-900 mb-4">EVIDENCIA FOTOGRAFICA:
            </h3>

            <div class="photo-grid mx-24">
                @php
                    $fotosBasicas = [
                        'foto_circulacion' => ['label' => 'TARJETA CIRCULACION', 'id' => 'tarjeta_circulacion', 'thumb' => 'thumbnail-circulacion'],
                        'foto_odometro'    => ['label' => 'ODOMETRO', 'id' => 'odometro', 'thumb' => 'thumbnail-odometro'],
                    ];
                @endphp
                @foreach($fotosBasicas as $campoBD => $meta)
                <div class="upload-box" onclick="document.getElementById('{{$meta['id']}}').click()">
                    <label>{{ $meta['label'] }}</label>
                    <input type="file" id="{{$meta['id']}}" name="{{$campoBD}}" accept="image/*" onchange="previewImage(event, '{{$meta['thumb']}}')">
                    
                    <img class="thumbnail" id="{{$meta['thumb']}}" 
                        @if(isset($ordenEditar) && $ordenEditar->detalleArrendado->$campoBD)
                            src="{{ $ordenEditar->detalleArrendado->$campoBD }}" style="display: block;"
                        @else
                            style="display: none;"
                        @endif
                    >
                </div>
                @endforeach 
            </div>
        </div>

        <hr class="my-8 border-zinc-200 mx-5">

        <div class="mx-5">
            <h3 class="text-lg font-semibold text-zinc-900 mb-4">SI SE REQUIEREN LLANTAS, ANEXAR FOTOS:</h3>
            <div class="photo-grid">
                @php
                    $fotosLlantas = [
                        'foto_llanta_del_pil' => ['label' => 'DELANTERA PILOTO', 'id' => 'llanta_del_pil', 'thumb' => 'thumbnail-del_pil'],
                        'foto_llanta_del_cop' => ['label' => 'DELANTERA COPILOTO', 'id' => 'llanta_del_cop', 'thumb' => 'thumbnail-del_cop'],
                        'foto_llanta_tra_pil' => ['label' => 'TRASERA PILOTO', 'id' => 'llanta_tra_pil', 'thumb' => 'thumbnail-tra_pil'],
                        'foto_llanta_tra_cop' => ['label' => 'TRASERA COPILOTO', 'id' => 'llanta_tra_cop', 'thumb' => 'thumbnail-tra_cop'],
                    ];
                @endphp
                @foreach($fotosLlantas as $campoBD => $meta)
                <div class="upload-box" onclick="document.getElementById('{{$meta['id']}}').click()">
                    <label>{{ $meta['label'] }}</label>
                    <input type="file" id="{{$meta['id']}}" name="{{$campoBD}}" accept="image/*" onchange="previewImage(event, '{{$meta['thumb']}}')">
                    
                    <img class="thumbnail" id="{{$meta['thumb']}}" 
                        @if(isset($ordenEditar) && $ordenEditar->detalleArrendado->$campoBD)
                            src="{{ $ordenEditar->detalleArrendado->$campoBD }}" style="display: block;"
                        @else
                            style="display: none;"
                        @endif
                    >
                </div>
                @endforeach 
            </div>
        </div>

        

        <hr class="my-8 border-zinc-200 mx-5">

        <div class="flex justify-center">
            <button type="submit"
                class="w-72 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition-transform cursor-pointer hover:scale-102 disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="loading">
                <span
                    x-show="!loading">{{ isset($ordenEditar) ? 'ACTUALIZAR DOCUMENTO' : 'GENERAR DOCUMENTO' }}</span>
                <span x-show="loading" class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Procesando...
                </span>
            </button>
        </div>
    </form>

    <div x-show="showModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4 transform transition-all">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-zinc-900">¡Documento Generado!</h3>
                <p class="mt-2 text-sm text-zinc-500">La orden se ha guardado correctamente.</p>
                <p class="mt-2 text-sm text-zinc-500">Para continuar con el proceso, suba la evidencia del correo enviado a PV en las opciones de la orden.</p>

                <div class="mt-6 flex justify-center gap-3">
                    <a :href="'/ordenvehiculos/pdf-arrendado/' + ordenId"
                        class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none sm:text-sm">
                        Descargar PDF
                    </a>
                    <a :href="returnUrl"
                        class="inline-flex justify-center rounded-md border border-zinc-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-zinc-700 hover:bg-zinc-50 focus:outline-none sm:text-sm">
                        Aceptar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function previewImage(event, thumbnailId) {
        const input = event.target;
        const thumbnail = document.getElementById(thumbnailId);

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                thumbnail.src = e.target.result;
                thumbnail.style.display = 'block';
                const label = input.parentElement.querySelector('label');
                if(label) label.style.opacity = '0';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
