<!DOCTYPE html>
<html lang="es" class="h-full bg-zinc-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Gestión Comercial' }} - {{ getenv('APP_NAME') ?? 'CFE' }}</title>

    <link rel="shortcut icon" href="/assets/img/logo_cfe.svg" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.1/dist/fancybox/fancybox.css" />

    @vite(['css/app.css', 'js/app.js'])
    {{-- <link rel="stylesheet" href="http://localhost/gestion_comercial-original/public/build/assets/app-CV5Aeeuw.css">
    <link rel="stylesheet" href="http://localhost/gestion_comercial-original/public/build/assets/app-CV5Aeeuw.css"> --}}

    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>

    @alpine
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/imask"></script>
    <script src="/assets/js/input-masks.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.8/dist/htmx.min.js"
        integrity="sha384-/TgkGk7p307TH7EXJDuUlgG3Ce1UVolAOFopFekQkkXihi5u/6OCvVKyz1W+idaz" crossorigin="anonymous">
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* CAMBIO: Aplicar Inter a todo el cuerpo */
        body {
            font-family: 'Inter', font-sans;
        }

        /* Opcional: Para que los números se vean mejor alineados en tablas */
        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }
    </style>
</head>

<body class="font-sans text-zinc-800 antialiased bg-zinc-100" x-data="{ mobileMenuOpen: false, notificationOpen: false }">

    <div class="min-h-screen flex flex-col">
    <header class="sticky top-0 z-40 w-full border-b border-zinc-200 bg-white/80 backdrop-blur-md">
        <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">

            <div class="flex items-center gap-4">
                <button type="button" @click="mobileMenuOpen = true"
                    class="lg:hidden p-2 text-zinc-500 hover:text-zinc-700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <a href="/dashboard" class="flex items-center gap-3 font-semibold text-emerald-700">
                    <img src="/assets/img/logo_cfe.svg" alt="CFE" class="h-10 w-auto">
                    <span class="hidden sm:block tracking-tight">Gestión Comercial</span>
                </a>

                <nav class="hidden lg:flex items-center gap-4 ml-6">
                    <a href="/dashboard"
                        class="hover:bg-zinc-50 px-2 py-2 flex items-center gap-2 text-sm font-medium text-zinc-600 hover:text-zinc-900">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        Inicio
                    </a>

                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open"
                            class="hover:bg-zinc-50 px-2 py-2 flex items-center gap-1 text-sm font-medium text-zinc-600 hover:text-zinc-900 focus:outline-none group cursor-pointer">
                            Opciones
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor"
                                class="w-4 h-4 text-zinc-400 group-hover:text-zinc-600 transition-transform duration-200"
                                :class="open ? 'rotate-180' : ''">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div x-show="open" x-transition.opacity.duration.200ms x-cloak
                            class="absolute left-0 mt-2 w-64 origin-top-left rounded-xl bg-white py-2 shadow-xl ring-1 ring-zinc-900/5 focus:outline-none z-50">

                            <div class="px-4 py-1 text-xs font-bold tracking-wider text-zinc-500 uppercase">
                                Seguimiento vehicular</div>

                            <a href="/ordenvehiculos"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-emerald-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                </svg>
                                Generación de órdenes
                            </a>

                            <a href="/vehiculos"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-emerald-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" 
                                    height="24" width="24" 
                                    viewBox="0 -960 960 960" 
                                    fill="currentColor" 
                                    class="w-6 h-6">
                                    <path d="M90-100q-12.38 0-21.19-8.81T60-130v-310l81.16-193q6.27-15.41 19.71-24.47 13.44-9.07 29.9-9.07h344.61q16.37 0 30 9.07 13.64 9.06 20 24.47l80.77 193v310q0 12.38-8.8 21.19-8.81 8.81-21.2 8.81h-24.61q-12.75 0-21.38-8.81-8.62-8.81-8.62-21.19v-48.08H144.61V-130q0 12.38-8.8 21.19Q127-100 114.61-100H90Zm55.08-399.61h435.38l-44.54-106.93H190.23l-45.15 106.93ZM120-238.08h486.15v-201.54H120v201.54Zm101.24-48.46q21.84 0 37.03-15 15.19-15 15.19-36.94 0-21.95-15.28-37.31-15.28-15.36-37.12-15.36-21.83 0-37.02 15.36-15.19 15.36-15.19 37.31 0 21.94 15.28 36.94 15.28 15 37.11 15Zm283.85 0q21.83 0 37.03-15 15.19-15 15.19-36.94 0-21.95-15.29-37.31-15.28-15.36-37.11-15.36t-37.02 15.36q-15.2 15.36-15.2 37.31 0 21.94 15.29 36.94 15.28 15 37.11 15ZM720-216.92v-327.85l-73.77-177.15H246.62l11.07-26.46q6.37-15.41 20-24.48 13.64-9.06 30-9.06H650q16.27 0 29.83 9.04 13.55 9.04 19.78 24.5L780-556.92v304.61q0 14.69-10.35 25.04-10.34 10.35-25.04 10.35H720Zm115.38-116.93v-327.84L762-837.31H363.92L375-863.77q6.36-15.41 20-24.47 13.64-9.07 30-9.07h340.77q16.27 0 29.82 9.04 13.56 9.04 19.79 24.5l80 190.31v304.23q0 14.69-10.34 25.04-10.35 10.34-25.04 10.34h-24.62Zm-472.3-5Z"/>
                                </svg>
                                Vehículos
                            </a>

                            @if(auth()->user()->is('admin'))
                            <a href="/dashboard-vehiculos"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-emerald-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125z" />
                                </svg>
                                Estadísticas de Vehículos
                            </a>

                            <div class="border-t border-zinc-200 my-2"></div>
                            <div class="px-4 py-1 text-xs font-bold tracking-wider text-zinc-500 uppercase">
                                Supervisiones</div>

                            <a href="/dashboard-semanal"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-emerald-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                                Semanal
                            </a>
                            <a href="/dashboard-diario"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-emerald-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                </svg>
                                Diario
                            </a>
                            @endif
                        </div>
                    </div>

                    @if(auth()->user()->is('admin'))
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open"
                            class="hover:bg-zinc-50 px-2 py-2 flex items-center gap-1 text-sm font-medium text-zinc-600 hover:text-zinc-900 focus:outline-none group cursor-pointer">
                            Ajustes
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor"
                                class="w-4 h-4 text-zinc-400 group-hover:text-zinc-600 transition-transform duration-200"
                                :class="open ? 'rotate-180' : ''">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                    </button>

                    <div x-show="open" x-transition.opacity.duration.200ms x-cloak
                            class="absolute left-0 mt-2 w-64 origin-top-left rounded-xl bg-white py-2 shadow-xl ring-1 ring-zinc-900/5 focus:outline-none z-50">

                            <a href="/users"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-emerald-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                                Usuarios
                            </a>

                            <a href="/settings/profile"
                            class="flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-emerald-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>

                            Configuración
                        </a>
                        </div>
                    </div>
                    @endif
                </nav>
            </div>

            <div class="flex items-center gap-4">
                <div class="relative" x-data="{ notificationOpen: false }" @click.away="notificationOpen = false">
                    <button @click="notificationOpen = !notificationOpen"
                        class="text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 p-2 rounded-lg transition-colors cursor-pointer">
                        @can('generar 500')
                            {{-- Badge dinámico con Alpine.js --}}
                            <span x-data="{ count: 0 }" @notification-update.window="count = $event.detail.count"
                                x-show="count > 0" x-text="count" x-transition.scale style="display: none;"
                                class="absolute -top-0.1 -right-2 h-3.5 w-4.5 rounded-md bg-red-500 ring-2 ring-white flex items-center justify-center text-xs text-white font-bold tabular-nums">
                            </span>
                        @endcan
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                    </button>
                    <div x-cloak x-show="notificationOpen" x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-xl ring-1 ring-zinc-900/5 focus:outline-none z-50">

                        <div class="p-4">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="font-medium text-gray-900">Notificaciones</h3>
                                <button @click="notificationOpen = false" class="text-gray-400 hover:text-gray-500 cursor-pointer">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="max-h-96 overflow-y-auto" x-data="{ count: 0 }"
                                @notification-update.window="count = $event.detail.count">

                                <template x-if="count > 0">
                                    <div class="p-3 border-b border-zinc-100 hover:bg-zinc-50 bg-red-50">
                                        <p class="text-sm font-bold text-red-600">
                                            Tienes <span x-text="count"></span> solicitud(es) 500 pendientes.
                                        </p>
                                        <a href="/ordenvehiculos"
                                            class="text-xs text-emerald-600 underline mt-1 block">Ir a atender</a>
                                    </div>
                                </template>

                                <div x-show="count === 0" class="p-3 border-b border-gray-100 hover:bg-gray-50">
                                    <p class="text-sm text-gray-600">No tienes notificaciones nuevas</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open"
                        class="flex items-center gap-2 rounded-lg p-1 hover:bg-zinc-100 transition-colors cursor-pointer">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white text-xs font-bold ring-1 ring-zinc-900/5">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <span class="hidden text-sm font-medium text-zinc-700 md:block">
                            {{ strtok(auth()->user()->name ?? 'Usuario', ' ') }}
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-zinc-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-transition.opacity.duration.200ms x-cloak
                        class="absolute right-0 mt-2 w-56 origin-top-right rounded-xl bg-white py-1 shadow-xl ring-1 ring-zinc-900/5 focus:outline-none z-50">

                        <div class="px-4 py-3 border-b border-zinc-100">
                            <p class="text-sm font-medium text-zinc-900">
                                {{ auth()->user()->name ?? 'Usuario Invitado' }}
                            </p>
                            <p class="text-xs text-zinc-500 truncate">{{ auth()->user()->user ?? 'sin usuario' }}</p>
                        </div>

                        <a href="/settings/profile"
                            class="flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-emerald-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>

                            Configuración
                        </a>

                        <form action="/auth/logout" method="POST" class="block border-t border-zinc-100 mt-1">
                            <button type="submit"
                                class="w-full flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-red-700 transition-colors cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                </svg>

                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="relative z-50 lg:hidden" x-show="mobileMenuOpen" role="dialog" aria-modal="true" x-cloak>
        <div x-show="mobileMenuOpen" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 bg-zinc-900/80 backdrop-blur-sm"
            aria-hidden="true">
        </div>

        <div class="fixed inset-0 flex">
            <div x-show="mobileMenuOpen" x-transition:enter="transition ease-in-out duration-300 transform"
                x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in-out duration-300 transform"
                x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                @click.away="mobileMenuOpen = false"
                class="relative mr-16 flex w-full max-w-xs flex-1 flex-col bg-white py-6 pb-4 shadow-xl">

                <div class="flex items-center justify-between px-6 mb-6">
                    <div class="flex items-center gap-2 font-bold text-zinc-900 text-xl">
                        <img src="/assets/img/logo_cfe.svg" alt="CFE" class="h-8 w-auto">
                        <span class="text-sm">Gestión</span>
                    </div>
                    <button type="button" @click="mobileMenuOpen = false"
                        class="-m-2.5 p-2.5 text-zinc-700 hover:text-red-600 transition-colors">
                        <span class="sr-only">Cerrar menú</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="flex flex-col gap-1 px-4 overflow-y-auto">
                    <a href="/dashboard"
                        class="flex items-center gap-3 rounded-lg bg-zinc-50 px-3 py-2 text-base font-medium text-zinc-900">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-zinc-500">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        Inicio
                    </a>

                    <div class="mt-4 mb-2 px-3 text-xs font-bold tracking-wider text-zinc-400 uppercase">Módulos</div>

                    <a href="/ordenes"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-base font-medium text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-zinc-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        Ordenes
                    </a>
                    <a href="/vehiculos"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-base font-medium text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-zinc-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V14.25m-3 0h3m-3 8.25a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3-1.5H4.75a1.125 1.125 0 01-1.125-1.125V4.125C3.625 3.504 4.129 3 4.75 3h12.75c.621 0 1.125.504 1.125 1.125V14.25" />
                        </svg>
                        Vehículos
                    </a>
                    <a href="/dashboard-vehiculos"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-base font-medium text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-zinc-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        Estadísticas de Vehículos
                    </a>

                    <div class="mt-4 mb-2 px-3 text-xs font-bold tracking-wider text-zinc-400 uppercase">Supervisiones
                    </div>
                    <a href="/supervision-semanal"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-base font-medium text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-zinc-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        Semanal
                    </a>
                    <a href="/supervision-diaria"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-base font-medium text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-zinc-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                        </svg>
                        Diaria
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <main class="flex-1 py-2 px-5">
        @yield('content')
    </main>

    <footer class="border-t border-zinc-200 bg-white/80 backdrop-blur-sm py-4">
            <div class="text-center text-xs text-zinc-500">
                2026 Desarrollado por Luis Angel Hoil Canche
            </div>
        </footer>
    </div>

    @toastContainer

    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@6.1/dist/fancybox/fancybox.umd.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Fancybox.bind('[data-fancybox]', {
                // Opciones de Fancybox
                Toolbar: {
                    display: {
                        left: ['infobar'],
                        middle: [],
                        right: ['close'],
                    },
                },
                Thumbs: {
                    type: 'classic',
                },
            });
        });
    </script>
    {{-- SISTEMA DE NOTIFICACIONES (Polling) --}}

    @can('generar 500')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('notificationSystem', () => ({
                    hasPermission: false,
                    lastCount: 0,
                    firstLoad: true,

                    init() {
                        // 1. Solicitar permisos para notificaciones del navegador
                        if ("Notification" in window && Notification.permission !== "granted") {
                            Notification.requestPermission().then(permission => {
                                this.hasPermission = permission === "granted";
                            });
                        } else if (Notification.permission === "granted") {
                            this.hasPermission = true;
                        }

                        // 2. Polling: Consultar cada 30 segundos
                        this.checkNotifications();
                        setInterval(() => {
                            this.checkNotifications();
                        }, 30000);
                    },

                    async checkNotifications() {
                        try {
                            // En Leaf la ruta es relativa a tu base, asegúrate que /api/... sea correcta
                            const response = await fetch('/api/check-orden-500');
                            const data = await response.json();

                            window.dispatchEvent(new CustomEvent('notification-update', {
                                detail: {
                                    count: data.count
                                }
                            }));

                            if (this.firstLoad){
                                this.lastCount = data.count;
                                this.firstLoad = false;
                                return;
                            }

                            if (data.alert && data.count > this.lastCount) {
                                this.notify(data.message);
                            }
                            this.lastCount = data.count;
                        } catch (error) {
                            console.error('Error de conexión con notificaciones:', error);
                        }
                    },

                    notify(message) {
                        // A. Toast de SweetAlert2 (Visual en la web)
                        const Toast = Swal.mixin({
                            toast: true,
                            position: "top-end",
                            showConfirmButton: true,
                            confirmButtonText: "Entendido",
                            confirmButtonColor: "#059669",
                            timer: 0,
                            timerProgressBar: false,
                            didOpen: (toast) => {
                            }
                        });

                        Toast.fire({
                            icon: "warning",
                            title: "Solicitud 500 Pendiente",
                            text: message,
                            // Botón HTML dentro del Toast para ir rápido
                            html: `
                            <div class="flex flex-col gap-2">
                                <span>${message}</span>
                                <a href="/ordenvehiculos" class="bg-emerald-600 text-white text-xs px-3 py-1 rounded text-center hover:bg-emerald-700">
                                    Ver Órdenes
                                </a>
                            </div>
                        `
                        });

                        // B. Notificación Push Nativa (Escritorio)
                        if (this.hasPermission) {
                            const notification = new Notification("Gestión Comercial CFE", {
                                body: message,
                                icon: "/assets/img/logo_cfe.svg",
                                requireInteraction: true,
                                tag: "orden-500-alert" // Tag único para no apilar muchas
                            });

                            notification.onclick = function() {
                                window.focus();
                                window.location.href = '/ordenvehiculos';
                                this.close();
                            };
                        }
                    }
                }));
            });
        </script>

        {{-- Inicializamos el componente Alpine aquí --}}
        <div x-data="notificationSystem"></div>
    @endcan
</body>

</html>
