@extends('layouts.app-layout', ['title' => 'Gestión de Áreas'])

@section('content')
<div class="mx-auto px-4 sm:px-6 lg:px-8 py-6 h-auto">
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-zinc-900">Catálogo de Areas y Subareas</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 h-full items-start">
        
        <div class="bg-white shadow rounded-lg border border-zinc-200 flex flex-col h-full max-h-[800px]">
            <div class="p-4 border-b border-zinc-200 bg-zinc-50 rounded-t-lg">
                <h3 class="font-bold text-zinc-700 mb-2">Areas</h3>
                <form hx-post="/areas" hx-target="#areas-list" hx-swap="innerHTML" 
                      @htmx:after-request="this.reset()" class="flex gap-2">
                    <input type="text" name="nombre" placeholder="Nueva Area..." required
                        class="w-full text-sm pl-2 border-2 border-zinc-300 rounded-md  bg-white focus:border-emerald-600 focus:ring-emerald-600 focus:outline-none uppercase">
                    <button type="submit" class="bg-emerald-600 text-white p-2 rounded-md hover:bg-emerald-700 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </button>
                </form>
            </div>
            
            <div id="areas-list" class="overflow-y-auto flex-1 p-2 space-y-1">
                @include('areas.partials.areas_list', ['areas' => $areas])
            </div>
        </div>

        <div id="subareas-container" class="md:col-span-2 bg-white shadow rounded-lg border border-zinc-200 h-full max-h-[800px] flex flex-col relative">
            <div class="flex flex-col items-center justify-center h-full text-zinc-400">
                <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <div class="flex items-center justify-center">
                    <p class="text-center">Selecciona un area de la izquierda<br>para ver sus subareas.</p>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    function highlightArea(element) {
        document.querySelectorAll('.area-item').forEach(el => {
            el.classList.remove('bg-emerald-50', 'border-emerald-500', 'text-emerald-700');
            el.classList.add('border-transparent', 'hover:bg-zinc-50');
        });
        element.classList.remove('border-transparent', 'hover:bg-zinc-50');
        element.classList.add('bg-emerald-50', 'border-emerald-500', 'text-emerald-700');
    }
</script>
@endsection