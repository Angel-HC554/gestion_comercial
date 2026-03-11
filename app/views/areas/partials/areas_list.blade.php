@foreach($areas as $area)
<div class="area-item group flex items-center justify-between p-3 rounded-md border-l-4 border-transparent cursor-pointer transition-all hover:bg-zinc-50"
     onclick="highlightArea(this)"
     hx-get="/areas/{{ $area->id }}/subareas"
     hx-target="#subareas-container"
     hx-swap="innerHTML">
    
    <span class="font-medium text-sm select-none">{{ $area->nombre }}</span>
    
    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
        <button onclick="editArea({{ $area->id }}, '{{ $area->nombre }}', event)" 
                class="text-zinc-500 hover:text-blue-600 p-1 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.768 3.732z"/></svg>
        </button>
        <button hx-delete="/areas/{{ $area->id }}" 
                hx-confirm="¿Seguro que deseas eliminar este departamento y TODAS sus ubicaciones?"
                hx-target="#areas-list"
                class="text-zinc-500 hover:text-red-600 p-1 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
    </div>
</div>
@endforeach

<script>
    // Función para editar nombre con SweetAlert
    function editArea(id, nombreActual, event) {
        event.stopPropagation(); // Evita que se seleccione la fila al dar click en editar
        Swal.fire({
            title: 'Editar Area',
            input: 'text',
            inputValue: nombreActual,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            confirmButtonColor: '#059669',
            preConfirm: (nuevoNombre) => {
                if (!nuevoNombre) Swal.showValidationMessage('El nombre es requerido');
                return nuevoNombre;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Hacemos la petición manual con fetch o htmx.ajax
                htmx.ajax('PUT', '/areas/' + id, {
                    target: '#areas-list',
                    values: { nombre: result.value }
                });
            }
        });
    }
</script>