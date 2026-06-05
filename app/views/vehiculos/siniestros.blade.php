<div>
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold leading-6 text-emerald-800">
            Registro histórico de siniestros y daños
        </h3>
    </div>

    @php
        $historialSiniestros = $vehiculo->historial_siniestros;
    @endphp

    @if ($historialSiniestros->isEmpty())
        <div class="p-8 bg-zinc-50 border-2 border-dashed border-zinc-300 rounded-lg text-center">
            <svg class="mx-auto h-12 w-12 text-zinc-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-sm font-medium text-zinc-900">Historial limpio</h3>
            <p class="mt-1 text-sm text-zinc-500">Este vehículo no tiene reportes de siniestros o daños
                registrados.</p>
        </div>
    @else
        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Fecha / Origen
                        </th>
                        <th scope="col"
                            class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Detalles / Comentarios
                        </th>
                        <th scope="col"
                            class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Evidencia
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($historialSiniestros as $siniestro)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3">
                                <div class="text-sm font-bold text-gray-900">
                                    {{ \Carbon\Carbon::parse($siniestro->fecha)->format('d M. Y') }}
                                </div>
                                <div class="text-xs font-semibold text-blue-600 mt-0.5">
                                    {{ $siniestro->tipo }}
                                </div>
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-600 uppercase">
                                {{ $siniestro->detalles }}
                            </td>
                            <td
                                class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                @if ($siniestro->link_evidencia)
                                    <a href="{{ $siniestro->link_evidencia }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center px-3 py-1 bg-gray-100 border border-gray-300 rounded text-xs font-semibold text-gray-700 hover:bg-gray-200 transition-colors cursor-pointer">
                                        Ver Foto
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400">Sin foto</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
