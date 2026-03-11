<div id="assignments-list">
    @if($assignments->isEmpty())
        <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
            <p class="text-gray-500 text-sm">Este usuario no tiene ubicaciones asignadas.</p>
        </div>
    @else
        <div class="overflow-hidden shadow border border-zinc-400 rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left text-xs font-bold uppercase text-gray-900 sm:pl-6">Area</th>
                        <th class="px-3 py-3.5 text-left text-xs font-bold uppercase text-gray-900">Subarea</th>
                        <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Eliminar</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach($assignments as $assign)
                    <tr>
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                            {{ $assign->area->nombre ?? 'N/A' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                            {{ $assign->subarea->nombre ?? 'N/A' }}
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            <button 
                                hx-delete="/users/{{ $userId }}/assignments/{{ $assign->id }}"
                                hx-target="#assignments-list"
                                hx-swap="outerHTML"
                                hx-trigger="confirmed"
                                onclick="
                                    Swal.fire({
                                            title: '¿Eliminar asignación?',
                                            text: 'Esta acción no se puede revertir.',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#ef4444',
                                            cancelButtonColor: '#6b7280',
                                            confirmButtonText: 'Sí, eliminar',
                                            cancelButtonText: 'Cancelar'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                htmx.trigger(this, 'confirmed');
                                            }
                                        })"
                                class="text-red-600 hover:text-red-900 font-bold transition-colors cursor-pointer"
                                title="Eliminar asignación">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>