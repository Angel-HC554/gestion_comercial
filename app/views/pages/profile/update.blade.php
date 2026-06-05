@extends('layouts.app-layout', [
    'title' => 'Actualizar Perfil',
])

@section('content')
    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="mb-6">
            <div class="flex justify-between">
                <h1 class="text-2xl font-bold text-zinc-900">Editar Perfil</h1>
                @if (auth()->user()->is('admin'))
                    <div>
                        <button
                            class="shadow-sm transition-colors inline-flex justify-center rounded-lg text-sm font-semibold py-3 px-4 hover:bg-emerald-700 bg-emerald-600 text-white cursor-pointer gap-2"
                            onclick="hacerRespaldo()" id="respaldo">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                                <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2">
                                    <ellipse cx="12" cy="5" rx="9" ry="3" />
                                    <path d="M3 12a9 3 0 0 0 5 2.69M21 9.3V5" />
                                    <path d="M3 5v14a9 3 0 0 0 6.47 2.88M12 12v4h4" />
                                    <path d="M13 20a5 5 0 0 0 9-3a4.5 4.5 0 0 0-4.5-4.5c-1.33 0-2.54.54-3.41 1.41L12 16" />
                                </g>
                            </svg>
                            Hacer respaldo de base de datos
                        </button>
                        @if (isset($ultimo_respaldo))
                            <p class="flex justify-center text-sm text-zinc-600 mt-2">Último respaldo: {{ $ultimo_respaldo }}</p>
                        @else
                            <p class="flex justify-center text-sm text-zinc-600 mt-2">No hay registros de respaldos.</p>
                        @endif
                    </div>
                @endif
            </div>
            <p class="text-sm text-zinc-600">Actualiza la información de tu cuenta.</p>
        </div>

        <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
            <form action="/settings/profile" method="post" class="space-y-6 max-w-xl">
                @csrf
                @method('patch')

                <div class="grid">
                    <label>Nombre</label>
                    <input
                        class="bg-[#F5F8F9] py-2 px-3 border-2 border-gray-300 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 rounded-lg"
                        type="text" name="name" placeholder="Nombre completo" value="{{ $name ?? '' }}"
                        pattern="^[a-zA-ZÀ-ÿ\s]+$" title="El nombre solo puede contener letras y espacios">
                    <small class="text-red-700 text-sm">{{ $errors['name'] ?? ($errors['auth'] ?? null) }}</small>
                </div>
                <div class="grid">
                    <label>Usuario (R.P.E)</label>
                    <input
                        class="bg-[#F5F8F9] py-2 px-3 border-2 border-gray-300 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 rounded-lg"
                        type="text" name="user" placeholder="Nombre de usuario" value="{{ $user ?? '' }}"
                        pattern="[A-Za-z0-9]+" title="Solo letras y números">
                    <small class="text-red-700 text-sm">{{ $errors['user'] ?? ($errors['auth'] ?? null) }}</small>
                </div>
                <div class="grid">
                    <label>Nueva contraseña</label>
                    <input
                        class="bg-[#F5F8F9] py-2 px-3 border-2 border-gray-300 focus:outline-none focus:ring-emerald-600 focus:border-emerald-600 rounded-lg"
                        type="text" name="password" placeholder="Dejar en blanco para no cambiar">
                    <small class="text-red-700 text-sm">{{ $errors['password'] ?? ($errors['auth'] ?? null) }}</small>
                </div>

                <button
                    class="shadow-sm transition-colors inline-flex justify-center rounded-lg text-sm font-semibold py-3 px-4 hover:bg-emerald-700 bg-emerald-600 text-white cursor-pointer">
                    Actualizar perfil
                </button>
            </form>
        </div>
    </div>
    <script>
        // 1. Mostrar alerta de éxito si existe el flash message
        @if (!empty($flash['success']))
            Swal.fire({
                title: '¡Logrado!',
                text: "{{ $flash['success'] }}",
                icon: 'success',
                showConfirmButton: false,
                timer: 1500
            });
        @endif
        // 2. Manejar el click para el respaldo
        function hacerRespaldo() {
            const enlaceRespaldo = document.getElementById('respaldo');

            if (enlaceRespaldo) {
                enlaceRespaldo.innerText = "Generando respaldos... por favor espere";
                enlaceRespaldo.classList.add('pointer-events-none', 'opacity-50');
            }
            async function respaldar() {
                try {
                    const response = await fetch('/api/ejecutar-respaldo', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({})
                    });
                    if (!response.ok) {
                        throw new Error(`Error en la petición: ${response.status}`);
                    }
                    const data = await response.json();
                    if (data.success) {
                        Swal.fire({
                            title: '¡Respaldo Exitoso!',
                            text: data.mensaje,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500
                        }).then((result) => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Hubo un problema',
                            text: data.mensaje,
                            icon: 'error',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                    console.log(data);
                } catch (error) {
                    console.error(error);
                    Swal.fire({
                        title: 'Error de conexión',
                        text: 'No se pudo comunicar con el servidor local.',
                        icon: 'error',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } finally {
                    if (enlaceRespaldo) {
                        enlaceRespaldo.innerHTML =
                            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 12a9 3 0 0 0 5 2.69M21 9.3V5"/><path d="M3 5v14a9 3 0 0 0 6.47 2.88M12 12v4h4"/><path d="M13 20a5 5 0 0 0 9-3a4.5 4.5 0 0 0-4.5-4.5c-1.33 0-2.54.54-3.41 1.41L12 16"/></g></svg> Hacer respaldo de base de datos';
                        enlaceRespaldo.classList.remove('pointer-events-none', 'opacity-50');
                    }
                }
            }
            respaldar();
        }
    </script>
@endsection
