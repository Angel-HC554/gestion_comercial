<div class="flex flex-col h-full animate-fade-in">
    <div class="p-4 border-b border-zinc-200 bg-zinc-50 rounded-t-lg flex justify-between items-center">
        <div>
            <span class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Subareas de:</span>
            <h2 class="text-xl font-bold text-emerald-700">{{ $area->nombre }}</h2>
        </div>
        <div class="text-xs text-zinc-500 bg-white px-2 py-1 rounded border">
            Total: {{ count($subareas) }}
        </div>
    </div>

    <div class="p-4 bg-white border-b border-zinc-100">
        <form hx-post="/areas/{{ $area->id }}/subareas" hx-target="#subareas-container" 
              @htmx:after-request="this.reset()" class="flex gap-2">
            <input type="text" name="nombre" placeholder="Agregar nueva ubicación ..." required
                class="w-full text-sm pl-2 border-2 border-zinc-300 rounded-md  bg-white focus:border-emerald-600 focus:ring-emerald-600 focus:outline-none uppercase">
            <button type="submit" class="bg-emerald-600 text-white text-sm font-medium px-4 py-2 rounded-md hover:bg-emerald-700 cursor-pointer">
                Agregar
            </button>
        </form>
    </div>

    <div class="overflow-y-auto flex-1 p-0">
        @if($subareas->isEmpty())
            <div class="p-8 text-center text-zinc-400 text-sm">
                No hay ubicaciones registradas en este departamento.
            </div>
        @else
            <table class="min-w-full divide-y divide-zinc-200">
                <tbody class="bg-white divide-y divide-zinc-200">
                    @foreach($subareas as $sub)
                    <tr class="hover:bg-zinc-50 group">
                        <td class="px-6 py-3 whitespace-nowrap text-sm font-medium text-zinc-700">
                            {{ $sub->nombre }}
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-right text-sm font-medium">
                            <button hx-delete="/subareas/{{ $sub->id }}"
                                    hx-confirm="¿Eliminar la ubicación {{ $sub->nombre }}?"
                                    hx-target="#subareas-container"
                                    class="text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>