@forelse($users as $user)
    <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            #{{ $user->id }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
            {{ $user->name }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ $user->email }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            @php
                // Decodificar si es string JSON o usar directo
                $roles = is_string($user->leaf_auth_user_roles)
                    ? json_decode($user->leaf_auth_user_roles)
                    : $user->leaf_auth_user_roles;
                // Si es un string simple separado por comas: explode(',', $user->roles)
            @endphp

            @if ($roles)
                @foreach ($roles as $role)
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                        {{ $role }}
                    </span>
                @endforeach
            @else
                <span class="text-gray-400 italic">Sin roles</span>
            @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <a href="/users/{{ $user->id }}/edit" class="text-emerald-600 hover:text-emerald-900">Editar</a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="px-6 py-12 text-center text-zinc-500">
            <svg class="h-10 w-10 text-zinc-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            No se encontraron usuarios.
        </td>
    </tr>
@endforelse

@if ($lastPage > 1)
    <tr>
        <td colspan="5" class="px-6 py-3 bg-gray-50">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500">
                    Página {{ $page }} de {{ $lastPage }}
                </span>

                <div class="inline-flex rounded-md shadow-sm">
                    <button
                        @if ($page > 1) hx-get="/users/search?page={{ $page - 1 }}&search={{ $search }}"
                        hx-target="#users-table-body"
                        class="px-2 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50"
                    @else
                        disabled
                        class="px-2 py-2 text-xs font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-l-md cursor-not-allowed" @endif>
                        Anterior
                    </button>

                    <button
                        @if ($page < $lastPage) hx-get="/users/search?page={{ $page + 1 }}&search={{ $search }}"
                        hx-target="#users-table-body"
                        class="px-2 py-2 text-xs font-medium text-zinc-600 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50"
                    @else
                        disabled
                        class="px-2 py-2 text-xs font-medium text-gray-400 bg-gray-100 border border-gray-300 rounded-r-md cursor-not-allowed" @endif>
                        Siguiente
                    </button>
                </div>
            </div>
        </td>
    </tr>
@endif
