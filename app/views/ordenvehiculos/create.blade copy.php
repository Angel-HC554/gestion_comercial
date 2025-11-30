@extends('layouts.app-layout')

@section('title', 'Crear Orden')

@section('content')
<div class="flex justify-between items-center mx-10 mb-6">
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Crear orden de servicio y reparación</h1>
    <div class="flex items-center gap-2">
        <span class="font-semibold text-zinc-600">Orden No: {{ $id }}</span>
    </div>
    <a href="/ordenvehiculos" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
        </svg>
        Volver
    </a>
</div>

<div class="mx-10 shadow-lg rounded-lg p-8 mt-5 bg-white border border-zinc-200"
     x-data="{
        // Datos traídos del controlador convertidos a JSON
        vehiculosDB: {{ json_encode($vehiculos) }},
        usersDB: {{ json_encode($users) }},
        
        // Variables del formulario
        numeco: '{{ old('noeconomico', $ordenEditar->noeconomico ?? '') }}',
        placa: '{{ old('placas', $ordenEditar->placas ?? '') }}',
        marca: '{{ old('marca', $ordenEditar->marca ?? '') }}',
        
        // Variables para firmas (Autocompletado)
        areausuaria: '{{ old('areausuaria', $ordenEditar->areausuaria ?? '') }}',
        rpeusuaria: '{{ old('rpeusuaria', $ordenEditar->rpeusuaria ?? '') }}',
        
        autoriza: '{{ old('autoriza', $ordenEditar->autoriza ?? '') }}',
        rpejefedpt: '{{ old('rpejefedpt', $ordenEditar->rpejefedpt ?? '') }}',
        
        resppv: '{{ old('resppv', $ordenEditar->resppv ?? '') }}',
        rperesppv: '{{ old('rperesppv', $ordenEditar->rperesppv ?? '') }}',

        // Función: Buscar datos del vehículo
        buscarVehiculo() {
            let encontrado = this.vehiculosDB.find(v => v.no_economico == this.numeco);
            if (encontrado) {
                this.placa = encontrado.placas;
                this.marca = encontrado.marca + ' ' + (encontrado.modelo || '');
            } else {
                // Opcional: limpiar si no encuentra
                // this.placa = ''; 
            }
        },

        // Función genérica para buscar RPE por Nombre
        buscarUsuario(nombre, campoRpe) {
            let user = this.usersDB.find(u => u.name == nombre);
            if (user) {
                this[campoRpe] = user.usuario; // Asumiendo que 'usuario' es el RPE en la BD
            }
        }
     }"
>
    <form action="{{ isset($ordenEditar) ? '/ordenvehiculos/update/'.$ordenEditar->id : '/ordenvehiculos/store' }}" method="POST">
        @csrf <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mx-5 my-5">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Área</label>
                <input type="text" name="area" list="areas-list" required 
                    class="w-full rounded-lg border-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm py-2.5"
                    value="{{ old('area', $ordenEditar->area ?? '') }}" placeholder="Escribe el área">
                <datalist id="areas-list">
                    <option value="DW01"></option>
                    <option value="DW01A"></option>
                    <option value="DW01B"></option>
                    </datalist>
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Zona</label>
                <input type="text" name="zona" required 
                    class="w-full rounded-lg border-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm py-2.5"
                    value="{{ old('zona', $ordenEditar->zona ?? 'MERIDA') }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Departamento</label>
                <input type="text" name="departamento" required 
                    class="w-full rounded-lg border-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm py-2.5"
                    value="{{ old('departamento', $ordenEditar->departamento ?? 'COMERCIAL') }}">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mx-5 my-5">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">No. Económico</label>
                <input type="text" name="noeconomico" list="economicos-list" required 
                    x-model="numeco" @input="buscarVehiculo()"
                    class="w-full rounded-lg border-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm py-2.5"
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
                    class="w-full rounded-lg border-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm py-2.5 bg-zinc-50">
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Placas</label>
                <input type="text" name="placas" required x-model="placa"
                    class="w-full rounded-lg border-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm py-2.5 bg-zinc-50">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mx-5 my-5">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Taller</label>
                <input type="text" name="taller" 
                    class="w-full rounded-lg border-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm py-2.5"
                    value="{{ old('taller', $ordenEditar->taller ?? '') }}" placeholder="Nombre del taller">
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Kilometraje</label>
                <input type="number" name="kilometraje" 
                    class="w-full rounded-lg border-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm py-2.5"
                    value="{{ old('kilometraje', $ordenEditar->kilometraje ?? '') }}">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Fecha Gen.</label>
                    <input type="date" name="fechafirm" 
                        class="w-full rounded-lg border-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm py-2.5"
                        value="{{ old('fechafirm', $ordenEditar->fechafirm ?? date('Y-m-d')) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1">Fecha Recep.</label>
                    <input type="date" name="fecharecep" 
                        class="w-full rounded-lg border-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm py-2.5"
                        value="{{ old('fecharecep', $ordenEditar->fecharecep ?? '') }}">
                </div>
            </div>
        </div>

        <hr class="my-8 border-zinc-200 mx-5">

        <div class="mx-5">
            <h3 class="text-lg font-semibold text-zinc-900 mb-4">MARCAR LA (S) CASILLA (S) QUE INDIQUE LA EXISTENCIA:</h3>
            
            @php
                // Helper pequeño para renderizar radios repetitivos
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-y-6 gap-x-8">
                @foreach($items as $item)
                    <div class="flex items-center justify-between bg-zinc-50 p-3 rounded-lg border border-zinc-100">
                        <span class="text-sm font-medium text-zinc-700">{{ $item['label'] }}</span>
                        <div class="flex items-center space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="{{ $item['name'] }}" value="Si" 
                                    class="form-radio text-emerald-600 focus:ring-emerald-500"
                                    {{ (isset($ordenEditar) && trim($ordenEditar->{$item['name']}) == 'Si') ? 'checked' : '' }}>
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
        </div>

        <div class="mx-5 mt-8" x-data="{ gas: {{ $ordenEditar->gasolina ?? 50 }} }">
            <label class="block text-sm font-medium text-zinc-700 mb-2">Nivel de Gasolina: <span x-text="gas + '%'" class="font-bold text-emerald-600"></span></label>
            <input type="range" name="gasolina" min="0" max="100" step="25" x-model="gas"
                   class="w-full h-2 bg-zinc-200 rounded-lg appearance-none cursor-pointer accent-emerald-600">
            <div class="flex justify-between text-xs text-zinc-500 mt-1 px-1">
                <span>0%</span><span>25%</span><span>50%</span><span>75%</span><span>100%</span>
            </div>
        </div>

        <hr class="my-8 border-zinc-200 mx-5">

        <div class="mx-5">
            <h3 class="text-lg font-semibold text-zinc-900 mb-4">REPARACIONES A EFECTUAR:</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @php
                    $reparaciones = [
                        'vehicle1' => 'Afinación mayor', 'vehicle11' => 'Medio motor',
                        'vehicle2' => 'Ajuste motor', 'vehicle12' => 'Motor completo',
                        'vehicle3' => 'Alineación y balanceo', 'vehicle13' => 'Parabrisas y vidrios',
                        'vehicle4' => 'Amortiguadores', 'vehicle14' => 'Frenos',
                        'vehicle5' => 'Cambio aceite y filtro', 'vehicle15' => 'Sistema eléctrico',
                        'vehicle6' => 'Clutch', 'vehicle16' => 'Sistema de enfriamiento',
                        'vehicle7' => 'Diagnóstico', 'vehicle17' => 'Suspensión',
                        'vehicle8' => 'Dirección', 'vehicle18' => 'Transmisión y diferencial',
                        'vehicle9' => 'Lavado y engrasado', 'vehicle19' => 'Tapicería',
                        'vehicle10' => 'Hojalatería y pintura', 'vehicle20' => 'Otro',
                    ];
                @endphp

                @foreach($reparaciones as $key => $label)
                    <div class="flex items-center">
                        <input type="checkbox" name="{{ $key }}" value="X" id="{{ $key }}"
                            class="w-4 h-4 text-emerald-600 border-zinc-300 rounded focus:ring-emerald-500"
                            {{ (isset($ordenEditar) && $ordenEditar->$key == 'X') ? 'checked' : '' }}>
                        <label for="{{ $key }}" class="ml-2 text-sm text-zinc-700">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mx-5 my-6">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Observaciones</label>
                <textarea name="observacion" rows="3" required
                    class="w-full rounded-lg border-zinc-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">{{ old('observacion', $ordenEditar->observacion ?? '') }}</textarea>
            </div>

            <div class="space-y-4">
                <div>
                    <span class="block text-sm font-medium text-zinc-700 mb-1">Requiere 500:</span>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="orden_500" value="NO" class="text-emerald-600 focus:ring-emerald-500" checked>
                            <span class="ml-2 text-sm">No</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="orden_500" value="SI" class="text-emerald-600 focus:ring-emerald-500"
                            {{ (isset($ordenEditar) && $ordenEditar->orden_500 == 'SI') ? 'checked' : '' }}>
                            <span class="ml-2 text-sm">Sí</span>
                        </label>
                    </div>
                </div>
                <div>
                    <span class="block text-sm font-medium text-zinc-700 mb-1">Servicio por kilometraje:</span>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="requiere_servicio" value="0" class="text-emerald-600 focus:ring-emerald-500" checked>
                            <span class="ml-2 text-sm">No</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="requiere_servicio" value="1" class="text-emerald-600 focus:ring-emerald-500"
                            {{ (isset($ordenEditar) && $ordenEditar->requiere_servicio) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm">Sí</span>
                        </label>
                    </div>
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
                <h4 class="text-xs font-bold text-zinc-400 uppercase mb-3">Solicita Area Usuaria</h4>
                <div class="space-y-3">
                    <input type="text" name="areausuaria" list="users-list" placeholder="Nombre..." required
                        x-model="areausuaria" @input="buscarUsuario(areausuaria, 'rpeusuaria')"
                        class="w-full rounded border-zinc-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-xs text-zinc-400">RPE</span>
                        <input type="text" name="rpeusuaria" x-model="rpeusuaria" readonly
                             class="w-full pl-10 rounded border-zinc-300 text-sm bg-zinc-100 text-zinc-500">
                    </div>
                </div>
            </div>

            <div class="bg-zinc-50 p-4 rounded-lg border border-zinc-200">
                <h4 class="text-xs font-bold text-zinc-400 uppercase mb-3">Autoriza Jefe Depto</h4>
                <div class="space-y-3">
                    <input type="text" name="autoriza" list="users-list" placeholder="Nombre..." required
                        x-model="autoriza" @input="buscarUsuario(autoriza, 'rpejefedpt')"
                        class="w-full rounded border-zinc-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-xs text-zinc-400">RPE</span>
                        <input type="text" name="rpejefedpt" x-model="rpejefedpt" readonly
                             class="w-full pl-10 rounded border-zinc-300 text-sm bg-zinc-100 text-zinc-500">
                    </div>
                </div>
            </div>

            <div class="bg-zinc-50 p-4 rounded-lg border border-zinc-200">
                <h4 class="text-xs font-bold text-zinc-400 uppercase mb-3">Responsable de PV</h4>
                <div class="space-y-3">
                    <input type="text" name="resppv" list="users-list" placeholder="Nombre..." required
                        x-model="resppv" @input="buscarUsuario(resppv, 'rperesppv')"
                        class="w-full rounded border-zinc-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-xs text-zinc-400">RPE</span>
                        <input type="text" name="rperesppv" x-model="rperesppv" readonly
                             class="w-full pl-10 rounded border-zinc-300 text-sm bg-zinc-100 text-zinc-500">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-center pb-8">
            <button type="submit" class="w-72 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition-transform hover:scale-105">
                {{ isset($ordenEditar) ? 'ACTUALIZAR DOCUMENTO' : 'GENERAR DOCUMENTO' }}
            </button>
        </div>
    </form>
</div>

@if(session()->has('orden_id'))
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-zinc-900">¡Documento Generado!</h3>
            <p class="mt-2 text-sm text-zinc-500">La orden se ha guardado correctamente.</p>
            
            <div class="mt-6 flex justify-center gap-3">
                <a href="/ordenvehiculos/pdf/{{ session('orden_id') }}" target="_blank" 
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
@endif

@endsection