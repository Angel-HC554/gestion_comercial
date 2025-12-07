<div class="mx-10 shadow-lg rounded-lg p-8 mt-5 bg-white border border-zinc-200" x-data="{
    // Datos BD
    vehiculosDB: {{ json_encode($vehiculos ?? []) }},
    usersDB: {{ json_encode($users ?? []) }},

    // Estado del Formulario
    loading: false,
    showModal: false,
    ordenId: null,

    // Variables de autocompletado
    numeco: '{{ old('noeconomico', $ordenEditar->noeconomico ?? '') }}',
    placa: '{{ old('placas', $ordenEditar->placas ?? '') }}',
    marca: '{{ old('marca', $ordenEditar->marca ?? '') }}',
    areausuaria: '{{ old('areausuaria', $ordenEditar->areausuaria ?? '') }}',
    rpeusuaria: '{{ old('rpeusuaria', $ordenEditar->rpeusuaria ?? '') }}',
    autoriza: '{{ old('autoriza', $ordenEditar->autoriza ?? '') }}',
    rpejefedpt: '{{ old('rpejefedpt', $ordenEditar->rpejefedpt ?? '') }}',
    resppv: '{{ old('resppv', $ordenEditar->resppv ?? '') }}',
    rperesppv: '{{ old('rperesppv', $ordenEditar->rperesppv ?? '') }}',

    gas: {{ $ordenEditar->gasolina ?? 50 }},

    // 1. Buscar Vehículo
    buscarVehiculo() {
        let encontrado = this.vehiculosDB.find(v => v.no_economico == this.numeco);
        if (encontrado) {
            this.placa = encontrado.placas;
            this.marca = encontrado.marca + ' ' + (encontrado.modelo || '');
        }
    },

    // 2. Buscar Usuario
    buscarUsuario(nombre, campoRpe) {
        let user = this.usersDB.find(u => u.name == nombre);
        if (user) {
            this[campoRpe] = user.usuario;
        }
    },

    // 3. ENVIAR FORMULARIO (AJAX)
    async submitForm() {
        this.loading = true;

        // Captura todos los campos del formulario automáticamente
        const form = document.getElementById('ordenForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        try {
            // Determinar si es edición o creación
            const isEdit = '{{ isset($ordenEditar) }}' === '1';
            const url = isEdit ?
                `/ordenvehiculos/${'{{ $ordenEditar->id ?? '' }}'}` :
                '/ordenvehiculos/store'; //le cambie aqui, le puse store
            const method = isEdit ? 'PUT' : 'POST';

            // A. Guardar/Actualizar Orden
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.status === 'success') {
                this.ordenId = result.id;

                // B. Generar Documento (Word/PDF) solo si es una orden nueva
                if (!isEdit) {
                    await fetch('/ordenes/generar/' + this.ordenId, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                }

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
    <form id="ordenForm" @submit.prevent="submitForm">
        @if (isset($ordenEditar))
            @method('PUT')
            @csrf
        @else
            @csrf
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mx-5 my-5">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Área</label>
                <input type="text" name="area" list="areas-list" required
                    class="appearance-none block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors"
                    value="{{ old('area', $ordenEditar->area ?? '') }}" placeholder="Escribe el área">
                <datalist id="areas-list">
                    <option value="DW01"></option>
                    <option value="DW01A"></option>
                    <option value="DW01B"></option>
                    <option value="DW01C"></option>
                    <option value="DW01D"></option>
                    <option value="DW01E"></option>
                    <option value="DW01G"></option>
                    <option value="DW01H"></option>
                    <option value="DW01J"></option>
                    <option value="DW01K"></option>
                    <option value="DW01M"></option>
                </datalist>
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Zona</label>
                <input type="text" name="zona" required
                    class="appearance-none block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors"
                    value="{{ old('zona', $ordenEditar->zona ?? 'MERIDA') }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Departamento</label>
                <input type="text" name="departamento" required
                    class="appearance-none block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors"
                    value="{{ old('departamento', $ordenEditar->departamento ?? 'COMERCIAL') }}">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mx-5 my-5">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">No. Económico</label>
                <input type="text" name="noeconomico" list="economicos-list" required x-model="numeco"
                    @input="buscarVehiculo()"
                    class="appearance-none block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors"
                    placeholder="Ej. 12345">
                <datalist id="economicos-list">
                    <template x-for="v in vehiculosDB" :key="v.no_economico">
                        <option :value="v.no_economico"></option>
                    </template>
                </datalist>
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Marca</label>
                <input type="text" name="marca" required x-model="marca"
                    class="appearance-none block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors">
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Placas</label>
                <input type="text" name="placas" required x-model="placa"
                    class="appearance-none block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mx-5 my-5">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Taller</label>
                <input type="text" name="taller"
                    class="appearance-none block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors"
                    value="{{ old('taller', $ordenEditar->taller ?? '') }}" placeholder="Nombre del taller">
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Kilometraje</label>
                <div class="relative rounded-md shadow-sm">
                    <input type="text" name="kilometraje"
                        class="appearance-none block w-full pl-3 pr-12 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors mask-km"
                        value="{{ old('kilometraje', $ordenEditar->kilometraje ?? '') }}">

                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">km</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Fecha Gen.</label>
                    <input type="date" name="fechafirm" x-bind:max="new Date().toISOString().split('T')[0]"
                        class="appearance-none block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors"
                        value="{{ old('fechafirm', $ordenEditar->fechafirm ?? date('Y-m-d')) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Fecha Recep.</label>
                    <input type="date" name="fecharecep"
                        class="appearance-none block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors"
                        value="{{ old('fecharecep', $ordenEditar->fecharecep ?? '') }}">
                </div>
            </div>
        </div>

        <hr class="my-8 border-zinc-200 mx-5">

        <div class="mx-5">
            <h3 class="text-lg font-semibold text-zinc-900 mb-4">MARCAR LA (S) CASILLA (S) QUE INDIQUE LA EXISTENCIA:
            </h3>

            @php
                $items = [
                    ['name' => 'radiocom', 'label' => 'Radiocomunicación'],
                    ['name' => 'llantaref', 'label' => 'Llanta de Refacción'],
                    ['name' => 'autoestereo', 'label' => 'Autoestereo'],
                    ['name' => 'gatoh', 'label' => 'Gato Hidráulico'],
                    ['name' => 'llavecruz', 'label' => 'Llave de Cruz'],
                    ['name' => 'extintor', 'label' => 'Extintor'],
                    ['name' => 'botiquin', 'label' => 'Botiquín'],
                    ['name' => 'escalera', 'label' => 'Escalera Sencilla'],
                    ['name' => 'escalerad', 'label' => 'Escalera Doble'],
                ];
            @endphp

            <div class="grid grid-cols-3 gap-y-6 gap-x-8">
                <div class="col-span-2 grid grid-cols-2 gap-4">
                    @foreach ($items as $item)
                        <div class="flex items-center justify-between bg-zinc-50 p-3 rounded-lg border border-zinc-100">
                            <span class="text-sm font-medium text-zinc-700">{{ $item['label'] }}</span>
                            <div class="flex items-center space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="{{ $item['name'] }}" value="Si"
                                        class="form-radio text-emerald-600 focus:ring-emerald-500"
                                        {{ isset($ordenEditar) && trim($ordenEditar->{$item['name']}) == 'Si' ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-zinc-700">Sí</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="{{ $item['name'] }}" value="No"
                                        class="form-radio text-emerald-600 focus:ring-emerald-500"
                                        {{ (isset($ordenEditar) && trim($ordenEditar->{$item['name']}) == 'No') || !isset($ordenEditar) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-zinc-700">No</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="flex items-start justify-end">
                    @include('components.gasolina-slider', ['gasolina' => $ordenEditar->gasolina ?? 0])
                </div>
            </div>
        </div>

        <hr class="my-8 border-zinc-200 mx-5">

        <div class="mx-5">
            <h3 class="text-lg font-semibold text-zinc-900 mb-4">REPARACIONES A EFECTUAR:</h3>
            <div class="grid grid-cols-3 gap-1">
                @php
                    $reparaciones = [
                        'vehicle1' => 'Afinación mayor',
                        'vehicle11' => 'Medio motor',
                        'vehicle2' => 'Ajuste motor',
                        'vehicle12' => 'Motor completo',
                        'vehicle3' => 'Alineación y balanceo',
                        'vehicle13' => 'Parabrisas y vidrios',
                        'vehicle4' => 'Amortiguadores',
                        'vehicle14' => 'Frenos',
                        'vehicle5' => 'Cambio aceite y filtro',
                        'vehicle15' => 'Sistema eléctrico',
                        'vehicle6' => 'Clutch',
                        'vehicle16' => 'Sistema de enfriamiento',
                        'vehicle7' => 'Diagnóstico',
                        'vehicle17' => 'Suspensión',
                        'vehicle8' => 'Dirección',
                        'vehicle18' => 'Transmisión y diferencial',
                        'vehicle9' => 'Lavado y engrasado',
                        'vehicle19' => 'Tapicería',
                        'vehicle10' => 'Hojalatería y pintura',
                        'vehicle20' => 'Otro',
                    ];
                @endphp

                @foreach ($reparaciones as $key => $label)
                    <div class="flex items-center">
                        <input type="checkbox" name="{{ $key }}" value="X" id="{{ $key }}"
                            class="w-4 h-4 text-emerald-600 border-zinc-300 rounded focus:ring-emerald-500"
                            {{ isset($ordenEditar) && $ordenEditar->$key == 'X' ? 'checked' : '' }}>
                        <label for="{{ $key }}"
                            class="ml-2 text-sm text-zinc-700">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mx-5 my-6">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Observaciones</label>
                <textarea name="observacion" rows="3" required
                    class="appearance-none block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors">{{ old('observacion', $ordenEditar->observacion ?? '') }}</textarea>
            </div>

            <div class="space-y-4 flex justify-evenly">
                <div x-data="{ checked: {{ isset($ordenEditar) && $ordenEditar->orden_500 != 'NO' ? 'true' : 'false' }} }">
                    <span class="block text-sm font-medium text-zinc-700 mb-1">Requiere 500:</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="orden_500" value="NO">
                        <input type="checkbox" name="orden_500" value="SI" class="sr-only" x-model="checked">
                        <div class="w-11 h-6 rounded-full transition-colors duration-200 ease-in-out focus:outline-none ring-offset-2 focus:ring-2 focus:ring-emerald-300"
                            :class="checked ? 'bg-emerald-600' : 'bg-zinc-200'">
                            <div class="bg-white border border-gray-300 rounded-full h-5 w-5 shadow transition-transform duration-200 ease-in-out mt-[2px] ml-[2px]"
                                :class="checked ? 'translate-x-5 border-transparent' : 'translate-x-0'"></div>
                        </div>
                        <span class="ml-3 text-sm font-medium text-zinc-700" x-text="checked ? 'Sí' : 'No'">
                        </span>
                    </label>
                </div>
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
        </div>

        <hr class="my-8 border-zinc-200 mx-5">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mx-5 mb-8">
            <datalist id="users-list">
                <template x-for="user in usersDB" :key="user.usuario">
                    <option :value="user.name"></option>
                </template>
            </datalist>

            <div class="bg-zinc-50 p-4 rounded-lg border border-zinc-200">
                <h4 class="text-xs font-bold text-zinc-600 uppercase mb-3">Solicita Area Usuaria</h4>
                <div class="space-y-3">
                    <input type="text" name="areausuaria" list="users-list" placeholder="Nombre..." required
                        x-model="areausuaria" @input="buscarUsuario(areausuaria, 'rpeusuaria')"
                        class="appearance-none block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors">

                    <div class="relative">
                        <span class="absolute left-3 top-2 text-xs text-zinc-400">RPE</span>
                        <input type="text" name="rpeusuaria" x-model="rpeusuaria"
                            class="appearance-none block w-full px-10 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors">
                    </div>
                </div>
            </div>

            <div class="bg-zinc-50 p-4 rounded-lg border border-zinc-200">
                <h4 class="text-xs font-bold text-zinc-600 uppercase mb-3">Autoriza Jefe Depto</h4>
                <div class="space-y-3">
                    <input type="text" name="autoriza" list="users-list" placeholder="Nombre..." required
                        x-model="autoriza" @input="buscarUsuario(autoriza, 'rpejefedpt')"
                        class="appearance-none block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors">

                    <div class="relative">
                        <span class="absolute left-3 top-2 text-xs text-zinc-400">RPE</span>
                        <input type="text" name="rpejefedpt" x-model="rpejefedpt"
                            class="appearance-none block w-full px-10 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors">
                    </div>
                </div>
            </div>

            <div class="bg-zinc-50 p-4 rounded-lg border border-zinc-200">
                <h4 class="text-xs font-bold text-zinc-600 uppercase mb-3">Responsable de PV</h4>
                <div class="space-y-3">
                    <input type="text" name="resppv" list="users-list" placeholder="Nombre..." required
                        x-model="resppv" @input="buscarUsuario(resppv, 'rperesppv')"
                        class="appearance-none block w-full px-2 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors">

                    <div class="relative">
                        <span class="absolute left-3 top-2 text-xs text-zinc-400">RPE</span>
                        <input type="text" name="rperesppv" x-model="rperesppv"
                            class="appearance-none block w-full px-10 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm text-gray-600 transition-colors">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-center pb-8">
            <button type="submit"
                class="w-72 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition-transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
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

                <div class="mt-6 flex justify-center gap-3">
                    <a :href="'/ordenvehiculos/pdf/' + ordenId"
                        class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none sm:text-sm">
                        Descargar PDF
                    </a>
                    <a href="/ordenvehiculos"
                        class="inline-flex justify-center rounded-md border border-zinc-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-zinc-700 hover:bg-zinc-50 focus:outline-none sm:text-sm">
                        Aceptar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
