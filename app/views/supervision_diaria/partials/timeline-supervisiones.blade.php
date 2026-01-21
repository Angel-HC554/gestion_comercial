<div class="space-y-6 animate-fade-in-down">
    
    {{-- Mantenemos tu encabezado si quieres que aparezca al cargar la pestaña, 
         o puedes quitarlo si sientes que repite información --}}
    <div class="mb-8 text-center">
        <p class="text-slate-500 mt-2 text-sm">
            Mostrando los últimos registros
        </p>
    </div>

    @if($supervisiones->isEmpty())
        <div class="p-4 bg-blue-50 text-blue-700 rounded-lg text-center border border-blue-200">
            No hay registros de supervisión para este vehículo aún.
        </div>
    @else
        <div class="relative border-l-4 border-zinc-300 ml-3 md:ml-6 space-y-8">
            
            @foreach($supervisiones as $sup)
                <div class="relative pl-8 md:pl-12 group">
                    
                    <div class="absolute -left-2.5 top-0 mt-1.5 w-5 h-5 rounded-full border-4 border-white 
                        {{ $sup->golpes ? 'bg-red-500 shadow-[0_0_0_4px_rgba(239,68,68,0.2)]' : 'bg-emerald-500' }}">
                    </div>

                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300 border 
                        {{ $sup->golpes ? 'border-red-300' : 'border-slate-300' }} overflow-hidden">
                        
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center bg-slate-50 px-4 py-3 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <span class="font-bold text-slate-700">{{ $sup->nombre_auxiliar }}</span>
                            </div>
                            <div class="text-xs font-mono text-slate-500 mt-1 md:mt-0 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                                {{-- Aseguramos que sea objeto Carbon o fecha válida --}}
                                {{ \Carbon\Carbon::parse($sup->fecha)->format('d/m/Y') }}
                            </div>
                        </div>

                        <div class="p-4 grid grid-cols-4 gap-4 text-sm">
                            <div>
                                <p class="text-slate-500 text-xs uppercase tracking-wider font-semibold">Kilometraje</p>
                                <p class="text-slate-700 font-mono text-base">{{ number_format($sup->kilometraje) }} km</p>
                            </div>
                            <div>
                                <p class="text-slate-500 text-xs uppercase tracking-wider font-semibold">Gasolina</p>
                                <div class="flex items-center gap-2">
                                    <img src="/assets/img/gas.svg" alt="Gasolina" class="w-6 h-6">
                                    @php
                                        $nivelTexto = match((int)$sup->gasolina) {
                                            0   => 'Vacío',
                                            25  => '1/4 de Tanque',
                                            50  => '1/2 Tanque',
                                            75  => '3/4 de Tanque',
                                            100 => 'Tanque Lleno',
                                            default => $sup->gasolina . '%'
                                        };
            
                                        // Opcional: Color del texto según nivel
                                        $colorGas = (int)$sup->gasolina <= 25 ? 'text-red-600 font-bold' : 'text-slate-700';
                                    @endphp
                                    <span class="{{ $colorGas }}">{{ $nivelTexto }}</span>
                                </div>
                            </div>
                            <div>
                                <p class="text-slate-500 text-xs uppercase tracking-wider font-semibold">Hora</p>
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <span class="text-slate-700">
                                        {{ $sup->hora_inicio ? \Carbon\Carbon::parse($sup->hora_inicio)->format('g:i a') : '--' }} - 
                                        {{ $sup->hora_fin ? \Carbon\Carbon::parse($sup->hora_fin)->format('g:i a') : '--' }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <p class="text-slate-500 text-xs uppercase tracking-wider font-semibold">Archivo</p>
                                <div class="flex items-center gap-2">
                                    <a href="{{ $sup->escaneo_url ? $sup->escaneo_url : '#' }}" target="_blank">
                                        <span class="text-emerald-700 underline font-bold hover:text-emerald-500 cursor-pointer flex gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                            </svg>
                                            {{ $sup->escaneo_url ? 'Ver archivo' : '--' }}
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        @if($sup->golpes)
                            <div class="bg-red-50 px-4 py-3 border-t border-red-100">
                                <div class="flex items-start gap-3 items-center">
                                    <img src="/assets/img/car_alert.svg" alt="Alerta" class="w-8 h-8">
                                    <div>
                                        <p class="text-red-800 font-bold text-sm">Reporte de Daño / Golpe</p>
                                        <p class="text-red-700 text-sm mt-1 italic">
                                            "{{ $sup->golpes_coment ?? 'Sin descripción detallada.' }}"
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            @endforeach
            
        </div>

        <div class="text-center mt-6">
             <span class="text-xs text-gray-600 bg-gray-50 px-3 py-1 rounded-full border border-gray-200">Fin de los registros recientes</span>
        </div>
    @endif
</div>