{{-- resources/views/components/super_diaria_form.blade.php --}}

<style>
    /* Estilos para los botones de verificación */
    input[type="radio"]:checked + label {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 4px;
        font-weight: bold;
    }
    
    input[type="radio"][value="1"]:checked + label {
        background-color: #10B981; /* Verde */
        color: white;
    }
    
    input[type="radio"][value="0"]:checked + label {
        background-color: #EF4444; /* Rojo */
        color: white;
    }
    
    /* Estilos generales y para el contenedor principal */
    .form-container {
        padding: 25px;
        background-color: #ffffff;
    }

    .form-container h2 {
        text-align: center;
        color: #333;
        margin-bottom: 25px;
        font-weight: bold;
    }

    /* Estilos para encabezados de sección */
    .form-container h3, .form-container h4 {
        color: #016f2b !important;
    }

    /* Estilos para campos de formulario mejorados */
    .form-container input[type="text"],
    .form-container input[type="number"],
    .form-container input[type="date"],
    .form-container input[type="file"],
    .form-container select,
    .form-container textarea {
        border-radius: 8px !important;
        border: 1px solid #ddd !important;
        padding: 10px 12px !important;
        font-size: 14px !important;
        transition: border-color 0.3s, box-shadow 0.3s !important;
    }

    .form-container input:focus,
    .form-container select:focus,
    .form-container textarea:focus {
        border-color: #016f2b !important;
        box-shadow: 0 0 0 2px rgba(1, 111, 43, 0.1) !important;
        outline: none !important;
    }

    /* Estilos para botones mejorados */
    .form-container .btn-cancel {
        background-color: #6b7280 !important;
        border: 1px solid #6b7280 !important;
        color: white !important;
        padding: 10px 20px !important;
        border-radius: 8px !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        cursor: pointer !important;
        transition: background-color 0.3s !important;
    }

    .form-container .btn-cancel:hover {
        background-color: #4b5563 !important;
    }

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
        display: none;
        border-radius: 6px;
    }

    .summary-section textarea {
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 16px;
        min-height: 120px;
        resize: vertical;
        box-sizing: border-box;
    }
    
    .submit-btn {
        margin-left: auto;
        margin-right: auto;
        display: block;
        width: 50%;
        padding: 15px;
        background-color: rgb(5 150 105);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        margin-top: 25px;
        transition: background-color 0.3s;
    }

    .submit-btn:hover {
        background-color: rgb(4 120 87);
    }
</style>

<div class="form-container">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium leading-6" style="color: #016f2b;">
            Registro de Verificación Diaria de Vehículo
        </h3>
    </div>
    @if ($errors->any() && $errors->hasBag('vehicleForm2'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul class="mt-0 mb-0 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="p-6">
        {{-- Es crucial agregar enctype para permitir la subida de archivos --}}
        @php
            $lastKil = $attributes->get('last_kilometraje') ?? null;
            if (!$lastKil && $attributes->get('vehiculo_id')) {
                try {
                    $lastKil = \App\Models\SupervisionDiaria::where('vehiculo_id', $attributes->get('vehiculo_id'))
                        ->orderBy('fecha', 'desc')
                        ->value('kilometraje');
                } catch (\Exception $e) {
                    $lastKil = null;
                }
            }
        @endphp

          <form id="vehicleForm2" action="{{ route('supervision_diaria.store') }}" method="POST" enctype="multipart/form-data"
              x-data="{ pasoAbierto: 1, kilometraje: null, lastKilometraje: {!! json_encode($lastKil) !!}, kilError: '', kilTimeout: null,
                     validateKilometraje() { if (this.lastKilometraje !== null && this.kilometraje !== null && Number(this.kilometraje) < Number(this.lastKilometraje)) { this.kilError = 'El kilometraje no puede ser menor al último registrado: ' + this.formatNumber(this.lastKilometraje); } else { this.kilError = ''; } },
                     debounceValidate() { clearTimeout(this.kilTimeout); this.kilTimeout = setTimeout(() => { this.validateKilometraje(); }, 700); },
                     formatNumber(n) { if (n === null || n === undefined || n === '') return ''; return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','); } }"
              x-ref="form"
              @submit.prevent="if (kilError) { alert(kilError); $refs.kmInput.focus(); } else { $refs.form.submit(); }">
            @csrf

            {{-- Campos ocultos --}}
            <input type="hidden" name="vehiculo_id" value="{{ $attributes->get('vehiculo_id') ?? '' }}">
            <input type="hidden" name="no_eco" value="{{ $attributes->get('no_economico') ?? '' }}">

            <div class="space-y-6">
                {{-- ======================================================== --}}
                {{-- BLOQUE 1: INFO GENERAL --}}
                {{-- ======================================================== --}}

                <div class="rounded-lg border border-gray-300 overflow-hidden">                    
                    <button type="button"
                            class="w-full flex items-center justify-between p-4 text-left text-base font-semibold text-gray-800 bg-emerald-600/5 hover:bg-emerald-600/10 focus:outline-none focus-visible:ring focus-visible:ring-indigo-500 focus-visible:ring-opacity-75"
                            @click="pasoAbierto = (pasoAbierto === 1) ? 0 : 1"
                            aria-expanded="pasoAbierto === 1">
                        <span class="text-emerald-800">1. Información General</span>
                        <svg class="h-5 w-5 transform transition-transform duration-200"
                            x-bind:class="{ 'rotate-180': pasoAbierto === 1 }"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <div x-show="pasoAbierto === 1" x-transition class="p-4 border-t border-gray-200 bg-white">
                    <div class="mt-4 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                        <div>
                            <label for="nombre_auxiliar" class="block text-sm font-medium text-gray-700 mb-1">Auxiliar Verificador</label>
                            <select id="nombre_auxiliar" name="nombre_auxiliar" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5" required>
                                <option selected disabled value="">Seleccione un auxiliar...</option>
                                <option value="JORGE MORENO">JORGE MORENO (RPE: 938286)</option> {{-- Ejemplo --}}
                            </select>
                        </div>

                        <div class="sm:col-span-1">
                            <label for="fecha" class="block text-sm font-medium text-gray-700">Fecha de Verificación</label>
                            <input type="date" name="fecha" id="fecha" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5" required>
                        </div>

                        <div class="sm:col-span-1">
                            <label for="hora_inicio" class="block text-sm font-medium text-gray-700">Hora de Inicio</label>
                            <input type="time" name="hora_inicio" id="hora_inicio" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5" required>
                        </div>

                        <div class="sm:col-span-1">
                            <label for="hora_fin" class="block text-sm font-medium text-gray-700">Hora de Fin</label>
                            <input type="time" name="hora_fin" id="hora_fin" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5" required>
                        </div>
                    </div>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- BLOQUE 2: LECTURAS CLAVE --}}
                {{-- ======================================================== --}}
                <div class="rounded-lg border border-gray-300 overflow-hidden">
                    <button type="button"
                            class="w-full flex items-center justify-between p-4 text-left text-base font-semibold text-emerald-800 bg-emerald-600/5 hover:bg-emerald-600/10 focus:outline-none focus-visible:ring focus-visible:ring-indigo-500 focus-visible:ring-opacity-75"
                            @click="pasoAbierto = (pasoAbierto === 2) ? 0 : 2"
                            aria-expanded="pasoAbierto === 2">
                        <span class="text-accent">2. Lecturas Clave</span>
                        <svg class="h-5 w-5 transform transition-transform duration-200"
                            x-bind:class="{ 'rotate-180': pasoAbierto === 2 }"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="pasoAbierto === 2" x-transition class="p-4 border-t border-gray-200 bg-white">
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="kilometraje" class="block text-sm font-medium text-gray-700">Kilometraje / Odómetro</label>
                            <input x-ref="kmInput" x-model.number="kilometraje" @input="debounceValidate()" @blur="validateKilometraje()" @keydown.enter.prevent type="number" name="kilometraje" id="kilometraje" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5" placeholder="Ingresa el kilometraje" required>
                            <p class="text-sm text-gray-500 mt-1" x-text="lastKilometraje ? 'Último kilometraje registrado: ' + lastKilometraje : 'No hay kilometraje previo registrado'"></p>
                            <p class="text-sm text-red-600 mt-1" x-show="kilError" x-text="kilError"></p>
                        </div>
                        <div class="w-full md:w-96">
                            <x-gasolina-slider />
                        </div>
                    </div>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- BLOQUE 3: CHECKLIST DE VERIFICACIÓN --}}
                {{-- ======================================================== --}}
                <div class="rounded-lg border border-gray-300 overflow-hidden">
                    <button type="button"
                            class="w-full flex items-center justify-between p-4 text-left text-base font-semibold text-emerald-800 bg-emerald-600/5 hover:bg-emerald-600/10 focus:outline-none focus-visible:ring focus-visible:ring-indigo-500 focus-visible:ring-opacity-75"
                            @click="pasoAbierto = (pasoAbierto === 3) ? 0 : 3"
                            aria-expanded="pasoAbierto === 3">
                        <span class="text-accent">3. Checklist de Verificación</span>
                        <svg class="h-5 w-5 transform transition-transform duration-200"
                            x-bind:class="{ 'rotate-180': pasoAbierto === 3 }"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="pasoAbierto === 3" x-transition class="p-4 border-t border-gray-200 bg-white">
                    <p class="mt-1 text-sm text-gray-600">Marque el estado de cada componente según el formulario.</p>
                    <div class="mt-4 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="mx-auto max-w-4xl py-2 align-middle">
                            <div class="overflow-hidden shadow ring-1 ring-gray-500 md:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="py-3.5 px-3 text-center text-sm font-semibold text-gray-900">Componente</th>
                                            <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900" style="width: 400px;">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        {{-- Aceite --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Aceite</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="aceite" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="aceite" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Líquido de Frenos --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Líquido de Frenos</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="liq_fren" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="liq_fren" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Anticongelante --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Anticongelante</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="anti_con" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="anti_con" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Agua --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Agua</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="agua" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="agua" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Radiador --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Radiador</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="radiador" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="radiador" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Llantas --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Llantas</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="llantas" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="llantas" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Llanta de Refacción --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Llanta de Refacción</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="llanta_r" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="llanta_r" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Tapón de Gasolina --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Tapón de Gasolina</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="tapon_gas" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="tapon_gas" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Limpieza de Cabina --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Limpieza de Cabina</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="limp_cab" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="limp_cab" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Limpieza Exterior --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Limpieza Exterior</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="limp_ext" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="limp_ext" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Cinturón de Seguridad --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Cinturón de Seguridad</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="cinturon" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="cinturon" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Limpiaparabrisas --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Limpiaparabrisas</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="limpia_par" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="limpia_par" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Manijas de Puertas --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Manijas de Puertas</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="manijas_puer" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="manijas_puer" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Espejo Interior --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Espejo Interior</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="espejo_int" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="espejo_int" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Espejo Lateral Izq --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Espejo Lateral Izq</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="espejo_lat_i" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="espejo_lat_i" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Espejo Lateral Der --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Espejo Lateral Der</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="espejo_lat_d" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="espejo_lat_d" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Gato --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Gato</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="gato" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="gato" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Llave de Cruz --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Llave de Cruz</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="llave_cruz" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="llave_cruz" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Extintor --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Extintor</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="extintor" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="extintor" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Direccionales --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Direccionales</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="direccionales" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="direccionales" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Luces --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Luces</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="luces" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="luces" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Intermitentes --}}
                                        <tr class="transition-colors duration-200">
                                            <td class="whitespace-nowrap py-4 px-3 text-sm font-medium text-gray-900 text-center">Intermitentes</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                <div class="flex items-center space-x-6 justify-center">
                                                    <div class="flex items-center">
                                                        <input name="intermit" value="1" type="radio" class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500" checked>
                                                        <label class="ml-2 block text-sm text-gray-900">✓</label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input name="intermit" value="0" type="radio" class="h-4 w-4 border-gray-300 text-red-600 focus:ring-red-500">
                                                        <label class="ml-2 block text-sm text-gray-900">✗</label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- BLOQUE 4: REPORTE DE DAÑOS Y EVIDENCIA --}}
                {{-- ======================================================== --}}
                <div class="rounded-lg border border-gray-300 overflow-hidden">
                    <button type="button"
                            class="w-full flex items-center justify-between p-4 text-left text-base font-semibold text-emerald-800 bg-emerald-600/5 hover:bg-emerald-600/10 focus:outline-none focus-visible:ring focus-visible:ring-indigo-500 focus-visible:ring-opacity-75"
                            @click="pasoAbierto = (pasoAbierto === 4) ? 0 : 4"
                            aria-expanded="pasoAbierto === 4">
                        <span class="text-accent">4. Reporte de Daños</span>
                        <svg class="h-5 w-5 transform transition-transform duration-200"
                            x-bind:class="{ 'rotate-180': pasoAbierto === 4 }"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="pasoAbierto === 4" x-transition class="p-4 border-t border-gray-200 bg-white">
                    <div class="mt-4 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">¿Hay golpes o daños?</label>
                            <div class="mt-2 space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="golpes" value="1" class="form-radio">
                                    <span class="ml-2">Sí</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="golpes" value="0" class="form-radio" checked>
                                    <span class="ml-2">No</span>
                                </label>
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="golpes_coment" class="block text-sm font-medium text-gray-700">Descripción de Golpes / Daños</label>
                            <textarea id="golpes_coment" name="golpes_coment" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5" placeholder="Transcriba los daños marcados en el diagrama. Ej: 'Rayón en puerta derecha'. Si no hay daños, déjelo en blanco."></textarea>
                        </div>

                        

                        <div class="sm:col-span-2">
                             <label for="escaneo_url" class="block text-sm font-medium text-gray-700">Adjuntar Escaneo del Formulario</label>
                             <input type="file" name="escaneo_url" id="escaneo_url" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                             <p class="mt-1 text-xs text-gray-500">PDF, JPG, PNG.</p>
                        </div>
                     </div>
                </div>
                </div>
            </div>

            {{-- Acciones del Formulario --}}
            <div class="mt-8 pt-5 border-t border-gray-200">
                <div class="flex justify-end space-x-3">
                    <button type="submit" class="submit-btn">
                        Guardar Verificación
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // No necesitamos el JavaScript complejo anterior ya que cambiamos la estructura
            console.log('Formulario de supervisión diaria cargado correctamente');
        });
    </script>
</div>