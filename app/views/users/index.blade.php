@extends('layouts.app-layout', [
    'title' => 'Gestión de Usuarios'
])

@section('content')

    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="{ openModal: false }">

        <div class="flex justify-start gap-12 items-center mb-6">
            <h1 class="text-2xl font-bold text-zinc-900">Gestión de Usuarios</h1>
            <button @click="openModal = true"
                class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors text-sm cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Crear usuario
            </button>
        </div>

        <div x-show="openModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-transition.opacity>

            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md" @click.away="openModal = false">
                <h3 class="text-xl font-bold text-zinc-900 mb-4">Nuevo Usuario</h3>

                <form hx-post="/users" hx-swap="none"
                    @htmx:after-request="
                  const res = JSON.parse($event.detail.xhr.response);
                  if(event.detail.successful) {
                   $el.reset(); 
                   openModal = false; 
                   htmx.trigger('#users-table-body', 'search');
                   Swal.fire({
                    title: 'Éxito',
                    text: res.message,
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 1100
                   });
                   }else {
                    Swal.fire({
                        title: 'Error',
                        text: res.message,
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                   }">

                    @csrf <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700">Usuario (R.P.E.)</label>
                            <input type="text" name="user" required pattern="[A-Za-z0-9]+"
                                title="Solo letras y números" placeholder="Usuario"
                                class="w-full border-2 border-gray-300 px-2 py-2 rounded-md focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                            <small class="text-xs text-zinc-500 mt-1 block">Identificador único.</small>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700">Nombre</label>
                            <input type="text" name="name" required placeholder="Nombre"
                                class="w-full border-2 border-gray-300 px-2 py-2 rounded-md focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700">Contraseña</label>
                            <input type="password" name="password" required placeholder="Contraseña"
                                class="w-full border-2 border-gray-300 px-2 py-2 rounded-md focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 mb-1">Rol</label>
                            <select name="main_role" required
                                class="w-full border-2 border-gray-300 rounded-md px-2 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:border-transparent cursor-pointer">
                                <option value="admin">Administrador</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="oficinista">Oficinista</option>
                            </select>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <h3 class="text-sm font-medium text-zinc-900 mb-2">Permiso adicional</h3>
                            <div class="flex items-center gap-2">
                                <input id="generar500" type="checkbox" name="rol_extra" value="generar500"
                                    class="h-4 w-4 text-emerald-600 focus:ring-emerald-600 border-gray-300 rounded cursor-pointer">
                                <label for="generar500" class="text-sm text-zinc-700">Puede generar Orden 500</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="openModal = false"
                            class="px-4 py-2 text-zinc-600 hover:bg-zinc-100 rounded-md text-sm font-medium cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-emerald-600 text-white hover:bg-emerald-700 rounded-md text-sm font-medium cursor-pointer">
                            Guardar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="px-4 pb-4 border-zinc-200 flex justify-between items-center">
            <div class="relative w-64">
                <input type="text" name="search" placeholder="Buscar usuario..."
                    class="w-full pl-10 pr-4 py-2 bg-white border-2 border-zinc-300 rounded-md text-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500"
                    hx-get="/users/search" hx-trigger="keyup changed delay:500ms, search" hx-target="#users-table-body">

                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow border overflow-hidden border-zinc-400">

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-400">
                    <thead class="bg-emerald-600/85 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold  text-white uppercase tracking-wider">ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-bold  text-white uppercase tracking-wider">
                                Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-bold  text-white uppercase tracking-wider">
                                Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-bold  text-white uppercase tracking-wider">Roles
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">
                                Acciones</th>
                        </tr>
                    </thead>

                    <tbody id="users-table-body" class="bg-white divide-y divide-zinc-300" hx-get="/users/search"
                        hx-trigger="load, search">
                        <tr class="htmx-indicator-content">
                            <td colspan="5" class="px-6 py-12 text-center text-zinc-500">
                                <svg class="animate-spin h-8 w-8 text-emerald-600 mx-auto mb-3"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span class="block text-sm font-medium">Cargando usuarios...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
