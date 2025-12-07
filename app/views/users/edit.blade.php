@extends('layouts.app-layout')

@section('title', 'Editar Usuario')

@section('content')
<div class="mx-auto px-4 py-6" x-data="{ loading: false }">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-zinc-900">Editar Usuario</h1>
        <a href="/users">
            <button class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors text-sm">
                Volver
            </button>
        </a>
    </div>

    <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
        <form hx-put="{{ '/users/' . $user->id }}"
              hx-swap="none"
              @htmx:after-request="
              const res = JSON.parse($event.detail.xhr.response);
              if(event.detail.successful) {
                  Swal.fire({
                      title: '¡Éxito!',
                      text: res.message || 'Usuario actualizado correctamente',
                      icon: 'success',
                      showConfirmButton: false,
                      timer: 1500
                  });
              } else {
                  Swal.fire({
                      title: 'Error',
                      text: res.message || 'Error al actualizar el usuario',
                      icon: 'error',
                      confirmButtonText: 'Entendido'
                  });
              }">
            @csrf
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-zinc-900 mb-4">Datos generales</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Nombre</label>
                        <input type="text" name="name" value="{{ $user->name }}" required 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ $user->email }}" required
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Nueva Contraseña</label>
                        <input type="password" name="password" 
                               placeholder="Dejar en blanco para no cambiar"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Rol</label>
                        @php
                            $userRoles = json_decode($user->leaf_auth_user_roles) ?? ['invitado'];
                            $mainRole = in_array('admin', $userRoles) ? 'admin' : 
                                      (in_array('operador', $userRoles) ? 'operador' : 'invitado');
                        @endphp
                        <select name="main_role" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            <option value="admin" {{ $mainRole === 'admin' ? 'selected' : '' }}>Administrador</option>
                            <option value="operador" {{ $mainRole === 'operador' ? 'selected' : '' }}>Operador</option>
                            <option value="invitado" {{ $mainRole === 'invitado' ? 'selected' : '' }}>Invitado</option>
                        </select>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <h3 class="text-lg font-medium text-zinc-900 mb-4">Roles Adicionales</h3>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input id="orden500" name="rol_extra" type="checkbox" value="orden500" 
                                   {{ in_array('orden500', $userRoles) ? 'checked' : '' }}
                                   class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                            <label for="orden500" class="ml-2 block text-sm text-gray-900">
                                Puede generar Orden 500
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="/users" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                            :disabled="loading">
                        <span x-show="!loading">Guardar cambios</span>
                        <span x-show="loading">Guardando...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection