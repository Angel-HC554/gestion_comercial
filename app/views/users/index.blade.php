@extends('layouts.app-layout')

@section('title', 'Listado de Órdenes')

@section('content')

    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="{ openModal: false }">

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-zinc-900">Gestión de Usuarios</h1>
            <button @click="openModal = true"
                class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors text-sm">
                Crear Usuario
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
                            <label class="block text-sm font-medium text-zinc-700">Usuario</label>
                            <input type="text" name="name" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700">Email</label>
                            <input type="email" name="email" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700">Contraseña</label>
                            <input type="password" name="password" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700">Rol</label>
                            <select name="leaf_auth_user_roles" class="w-full border-gray-300 rounded-md shadow-sm">
                                <option value="admin">Administrador</option>
                                <option value="operador">Operador</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="openModal = false"
                            class="px-4 py-2 text-zinc-600 hover:bg-zinc-100 rounded-md text-sm font-medium">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-emerald-600 text-white hover:bg-emerald-700 rounded-md text-sm font-medium">
                            Guardar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="px-4 pb-4 border-zinc-200 flex justify-between items-center">
            <div class="relative w-64">
                <input type="text" name="search" placeholder="Buscar usuario..."
                    class="w-full pl-10 pr-4 py-2 bg-white border border-zinc-300 rounded-md text-sm focus:ring-emerald-500 focus:border-emerald-500"
                    hx-get="/users/search" hx-trigger="keyup changed delay:500ms, search" hx-target="#users-table-body">

                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow border overflow-hidden border-zinc-200">

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones</th>
                        </tr>
                    </thead>

                    <tbody id="users-table-body" class="bg-white divide-y divide-zinc-200" hx-get="/users/search"
                        hx-trigger="load">
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
