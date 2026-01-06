@extends('layouts.app-layout', [
    'title' => 'Editar Usuario'
])

@section('content')
<div class="mx-auto px-4 py-6" x-data="{ loading: false }">
    <div class="flex justify-start gap-12 items-center mb-6">
        <h1 class="text-2xl font-bold text-zinc-900">Editar Usuario</h1>
        <a href="/users">
            <button class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors text-sm cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                    <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                </svg>
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
                               class="w-full border-2 border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Usuario (ID)</label>
                        <input type="text" name="user" value="{{ $user->user }}" required pattern="[A-Za-z0-9]+"
                               title="Solo letras y números"
                               class="w-full border-2 border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <small class="text-xs text-zinc-500 mt-1 block">Identificador alfanumérico único.</small>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Nueva Contraseña</label>
                        <input type="password" name="password" 
                               placeholder="Dejar en blanco para no cambiar"
                               class="w-full border-2 border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Rol</label>
                        @php
                            $userRoles = json_decode($user->leaf_auth_user_roles) ?? [];
                            $mainRoleOptions = ['admin', 'supervisor', 'oficinista'];
                            $mainRole = 'oficinista';
                            foreach ($mainRoleOptions as $roleOption) {
                                if (in_array($roleOption, $userRoles)) {
                                    $mainRole = $roleOption;
                                    break;
                                }
                            }
                        @endphp
                        <select name="main_role" required
                                class="w-full border-2 border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            <option value="admin" {{ $mainRole === 'admin' ? 'selected' : '' }}>Administrador</option>
                            <option value="supervisor" {{ $mainRole === 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                            <option value="oficinista" {{ $mainRole === 'oficinista' ? 'selected' : '' }}>Oficinista</option>
                        </select>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <h3 class="text-lg font-medium text-zinc-900 mb-4">Permiso Adicional</h3>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input id="generar500" name="rol_extra" type="checkbox" value="generar500" 
                                   {{ in_array('generar500', $userRoles) ? 'checked' : '' }}
                                   class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded cursor-pointer">
                            <label for="generar500" class="ml-2 block text-sm text-gray-900">
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
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 cursor-pointer"
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