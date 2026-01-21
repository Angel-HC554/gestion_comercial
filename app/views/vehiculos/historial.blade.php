    <div class="max-w-3xl mx-auto px-4 py-10">
        
        <div class="mb-10 text-center">
            <h1 class="text-3xl font-bold text-slate-900">Historial de Supervisiones</h1>
            <p class="text-slate-500 mt-2">
                Vehículo ID: <span class="font-mono font-semibold text-slate-700">{{ $vehiculo->id }}</span> 
                | No. Eco: <span class="font-mono font-semibold text-slate-700">{{ $vehiculo->no_eco ?? 'N/A' }}</span>
            </p>
        </div>

        @if($supervisiones->isEmpty())
            <div class="p-4 bg-blue-50 text-blue-700 rounded-lg text-center border border-blue-200">
                No hay registros de supervisión para este vehículo aún.
            </div>
        @else

        <div class="relative border-l-4 border-slate-300 ml-3 md:ml-6 space-y-8">
            
            @foreach($supervisiones as $sup)
                <div class="relative pl-8 md:pl-12 group">
                    
                    <div class="absolute -left-2.5 top-0 mt-1.5 w-5 h-5 rounded-full border-4 border-white 
                        {{ $sup->golpes ? 'bg-red-500 shadow-[0_0_0_4px_rgba(239,68,68,0.2)]' : 'bg-emerald-500' }}">
                    </div>

                    <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300 border 
                        {{ $sup->golpes ? 'border-red-200' : 'border-slate-200' }} overflow-hidden">
                        
                        <div class="flex flex-col md:flex-row md:justify-between md:items-center bg-slate-50 px-4 py-3 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <span class="font-bold text-slate-700">{{ $sup->nombre_auxiliar }}</span>
                            </div>
                            <div class="text-xs font-mono text-slate-500 mt-1 md:mt-0 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>

                                {{ $sup->fecha->format('d/m/Y') }}
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
                                    <span class="text-slate-700">{{ $sup->gasolina }}</span>
                                </div>
                            </div>
                            <div>
                                <p class="text-slate-500 text-xs uppercase tracking-wider font-semibold">Hora</p>
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>

                                    <span class="text-slate-700">{{ $sup->hora_inicio ? $sup->hora_inicio->format('g:i a') : '--' }} - {{ $sup->hora_fin ? $sup->hora_fin->format('g:i a') : '--' }}</span>
                                </div>
                            </div>
                            <div>
                                <p class="text-slate-500 text-xs uppercase tracking-wider font-semibold">Archivo</p>
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>

                                    <span class="text-slate-700">{{ $sup->escaneo_url ? 'Ver archivo' : '--' }}</span>
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
        @endif
    </div>