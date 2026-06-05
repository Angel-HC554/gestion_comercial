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
    {{--
    <link rel="stylesheet" href="http://localhost/gestion_comercial-original/public/build/assets/app-CV5Aeeuw.css">
    <link rel="stylesheet" href="http://localhost/gestion_comercial-original/public/build/assets/app-CV5Aeeuw.css"> --}}

    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

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

        body {
            font-family: 'Inter', font-sans;
        }

        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }
    </style>
</head>

<body class="font-sans text-zinc-800 antialiased bg-zinc-100" x-data="{ mobileMenuOpen: false, notificationOpen: false }">

    <div class="min-h-screen flex flex-col">
        <header class="sticky top-0 z-40 w-full border-b border-zinc-200 bg-white/70 backdrop-blur-md">
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
                        <span class="hidden sm:block tracking-tight">Vehículos</span>
                    </a>

                    <nav class="hidden lg:flex items-center gap-4 ml-6">
                        <a href="/dashboard"
                            class="hover:bg-zinc-50 px-2 py-2 flex items-center gap-2 text-sm font-medium text-zinc-600 hover:text-zinc-900">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-5">
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
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
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
                                    <svg xmlns="http://www.w3.org/2000/svg" height="24" width="24"
                                        viewBox="0 -960 960 960" fill="currentColor" class="w-6 h-6">
                                        <path
                                            d="M90-100q-12.38 0-21.19-8.81T60-130v-310l81.16-193q6.27-15.41 19.71-24.47 13.44-9.07 29.9-9.07h344.61q16.37 0 30 9.07 13.64 9.06 20 24.47l80.77 193v310q0 12.38-8.8 21.19-8.81 8.81-21.2 8.81h-24.61q-12.75 0-21.38-8.81-8.62-8.81-8.62-21.19v-48.08H144.61V-130q0 12.38-8.8 21.19Q127-100 114.61-100H90Zm55.08-399.61h435.38l-44.54-106.93H190.23l-45.15 106.93ZM120-238.08h486.15v-201.54H120v201.54Zm101.24-48.46q21.84 0 37.03-15 15.19-15 15.19-36.94 0-21.95-15.28-37.31-15.28-15.36-37.12-15.36-21.83 0-37.02 15.36-15.19 15.36-15.19 37.31 0 21.94 15.28 36.94 15.28 15 37.11 15Zm283.85 0q21.83 0 37.03-15 15.19-15 15.19-36.94 0-21.95-15.29-37.31-15.28-15.36-37.11-15.36t-37.02 15.36q-15.2 15.36-15.2 37.31 0 21.94 15.29 36.94 15.28 15 37.11 15ZM720-216.92v-327.85l-73.77-177.15H246.62l11.07-26.46q6.37-15.41 20-24.48 13.64-9.06 30-9.06H650q16.27 0 29.83 9.04 13.55 9.04 19.78 24.5L780-556.92v304.61q0 14.69-10.35 25.04-10.34 10.35-25.04 10.35H720Zm115.38-116.93v-327.84L762-837.31H363.92L375-863.77q6.36-15.41 20-24.47 13.64-9.07 30-9.07h340.77q16.27 0 29.82 9.04 13.56 9.04 19.79 24.5l80 190.31v304.23q0 14.69-10.34 25.04-10.35 10.34-25.04 10.34h-24.62Zm-472.3-5Z" />
                                    </svg>
                                    Vehículos
                                </a>

                                @if (auth()->user()->is('admin') || auth()->user()->is('supervisor'))
                                    <a href="/dashboard-vehiculos"
                                        class="flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-emerald-700 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125z" />
                                        </svg>
                                        Estadísticas de Vehículos
                                    </a>
                                @endif

                                @if (auth()->user()->is('admin'))

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

                        @if (auth()->user()->is('admin'))
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button @click="open = !open"
                                    class="hover:bg-zinc-50 px-2 py-2 flex items-center gap-1 text-sm font-medium text-zinc-600 hover:text-zinc-900 focus:outline-none group cursor-pointer">
                                    Ajustes
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor"
                                        class="w-4 h-4 text-zinc-400 group-hover:text-zinc-600 transition-transform duration-200"
                                        :class="open ? 'rotate-180' : ''">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
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

                                    <a href="/areas"
                                        class="flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-emerald-700 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                        </svg>

                                        Areas
                                    </a>

                                    <a href="/settings/profile"
                                        class="flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-emerald-700 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>

                                        Mi Perfil
                                    </a>
                                </div>
                            </div>
                        @endif
                    </nav>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden lg:flex gap-2 items-center justify-end" x-data="citasNavbar()" x-cloak>
                        <template x-if="lista_citas.length > 0">
                            <div class="flex gap-2">
                                <template x-for="cita in lista_citas" :key="cita.id">
                                    <div :title="'Cita Confirmada\nFecha: ' + formatearFecha(cita.detalle_arrendado?.fecha_cita) +
                                        '\nVehículo: ' + cita?.noeconomico"
                                        class="flex items-center gap-2 px-3 py-1.5 bg-[#EBF3FC] cursor-help select-none hover:bg-[#D1E4F9] transition-colors">

                                        <div class="text-[#0F6CBD]">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>

                                        <div class="flex flex-col justify-center leading-tight">
                                            <div class="flex items-center gap-1 text-[11px] text-[#0F6CBD]">
                                                <span class="font-bold capitalize" x-text="cita.texto_fecha"></span>
                                                <span class="font-medium"
                                                    x-text="new Date(cita.detalle_arrendado?.fecha_cita.replace(/-/g, '/')).toLocaleTimeString('es-MX', {hour: '2-digit', minute:'2-digit'})"></span>
                                            </div>
                                            <span class="text-[10px] font-semibold text-blue-800"
                                                x-text="'Vehículo: ' + cita?.noeconomico"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div class="relative" x-data="{
                        notificationOpen: false,
                        c500: 0,
                        cTrans: 0,
                        cCitas: 0,
                        cSiniestros: 0,
                        cMant: 0,
                        get hasNotifications() {
                            let total = 0;
                            {{-- El servidor solo compila las sumas si el usuario cumple con los permisos --}}
                            @can('generar 500') total += this.c500; @endcan
                            @is('admin')
                            total += this.cTrans;
                            @endis
                            @if (auth()->user()->is('admin') || auth()->user()->is('supervisor')) total += this.cCitas + this.cSiniestros + this.cMant; @endif
                            return total > 0;
                        }
                    }" {{-- Escuchadores de eventos protegidos desde el servidor --}} @can('generar 500')
                            @notification-update.window="c500 = $event.detail.count" @endcan @is('admin')
                        @notification-admin.window="cTrans = $event.detail.count" @endis
                        @if (auth()->user()->is('admin') || auth()->user()->is('supervisor'))
                        @notification-citas.window="cCitas = $event.detail.count"
                        @notification-siniestros.window="cSiniestros = $event.detail.count"
                        @notification-mantenimientos.window="cMant = $event.detail.count"
                        @endif
                        @click.away="notificationOpen = false">
                        <button @click="notificationOpen = !notificationOpen"
                            class="text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 p-2 rounded-lg transition-colors cursor-pointer">
                            {{-- Indicador visual protegido por la propiedad computada segura --}}
                            <span x-show="hasNotifications" x-transition.scale style="display: none;"
                                class="absolute top-1 right-1.5 h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white animate-pulse">
                            </span>
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
                                    <button @click="notificationOpen = false"
                                        class="text-gray-400 hover:text-gray-500 cursor-pointer">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="max-h-96 overflow-y-auto">
                                    @can('generar 500')
                                        <template x-if="c500 > 0">
                                            <div class="p-3 border-b border-zinc-100 hover:bg-zinc-50 bg-blue-50">
                                                <p class="text-sm font-bold text-red-600">
                                                    Tienes <span x-text="c500"></span> solicitud(es) 500 pendientes.
                                                </p>
                                                <a href="/ordenvehiculos?filtro=orden500"
                                                    class="text-xs text-emerald-700 underline mt-1 block">Ir a atender</a>
                                            </div>
                                        </template>
                                    @endcan

                                    @is('admin')
                                        <template x-if="cTrans > 0">
                                            <div class="p-3 border-b border-zinc-100 hover:bg-zinc-50 bg-blue-50">
                                                <p class="text-sm font-bold text-red-600">
                                                    Tienes <span x-text="cTrans"></span> órdenes por atender.
                                                </p>
                                                <a href="/ordenvehiculos?filtro=por_atender"
                                                    class="text-xs text-emerald-700 underline mt-1 block">Ir a atender</a>
                                            </div>
                                        </template>
                                    @endis

                                    @if (auth()->user()->is('admin') || auth()->user()->is('supervisor'))
                                        <template x-if="cCitas > 0">
                                            <div class="p-3 border-b border-zinc-100 hover:bg-zinc-50 bg-purple-50">
                                                <p class="text-sm font-bold text-purple-700">
                                                    Tienes <span x-text="cCitas"></span> vehículo(s) con cita asignada.
                                                </p>
                                                <a href="/ordenvehiculos?filtro=citas"
                                                    class="text-xs text-emerald-700 underline mt-1 block">Ver citas</a>
                                            </div>
                                        </template>

                                        <template x-if="cSiniestros > 0">
                                            <div class="p-3 border-b border-zinc-100 hover:bg-zinc-50 bg-amber-50">
                                                <p class="text-sm font-bold text-amber-700">
                                                    Hay <span x-text="cSiniestros"></span> vehículo(s) con siniestros
                                                    sin orden.
                                                </p>
                                                <a href="/dashboard-vehiculos?tab=siniestros"
                                                    class="text-xs text-emerald-700 underline mt-1 block">Ver
                                                    vehículos</a>
                                            </div>
                                        </template>

                                        <template x-if="cMant > 0">
                                            <div class="p-3 border-b border-zinc-100 hover:bg-zinc-50 bg-yellow-50/50">
                                                <p class="text-sm font-bold text-yellow-700">
                                                    Hay <span x-text="cMant"></span> vehículo(s) con alertas de
                                                    mantenimiento.
                                                </p>
                                                <a href="/vehiculos?mantenimiento=amarillo"
                                                    class="text-xs text-emerald-700 underline mt-1 block">Ver
                                                    vehículos</a>
                                            </div>
                                        </template>
                                    @endif
                                    {{-- Mensaje de bandeja vacía --}}
                                    <div x-show="!hasNotifications"
                                        class="p-3 border-b border-gray-100 hover:bg-gray-50">
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
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div x-show="open" x-transition.opacity.duration.200ms x-cloak
                            class="absolute right-0 mt-2 w-56 origin-top-right rounded-xl bg-white py-1 shadow-xl ring-1 ring-zinc-900/5 focus:outline-none z-50">

                            <div class="px-4 py-3 border-b border-zinc-100">
                                <p class="text-sm font-medium text-zinc-900">
                                    {{ auth()->user()->name ?? 'Usuario Invitado' }}
                                </p>
                                <p class="text-xs text-zinc-500 truncate">{{ auth()->user()->user ?? 'sin usuario' }}
                                </p>
                            </div>

                            <a href="/settings/profile"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-emerald-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>

                                Mi Perfil
                            </a>

                            <form action="/auth/logout" method="POST" class="block border-t border-zinc-100 mt-1">
                                <button type="submit"
                                    class="w-full flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-red-700 transition-colors cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
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
                x-transition:leave="transition-opacity ease-linear duration-300"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-zinc-900/80 backdrop-blur-sm" aria-hidden="true">
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
                            <span class="text-sm">Gestión Vehicular</span>
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

                    <nav class="flex flex-col gap-1 px-4 overflow-y-auto custom-scrollbar">
                        <a href="/dashboard"
                            class="flex items-center gap-3 rounded-lg px-3 py-2 text-base font-medium text-zinc-900 hover:bg-zinc-50">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-emerald-600">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                            Inicio
                        </a>

                        <div x-data="{ open: false }" class="mt-2">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between px-3 py-2 text-base font-bold tracking-wider text-zinc-500 uppercase">
                                <span>Opciones</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-4 h-4 transition-transform"
                                    :class="open ? 'rotate-180' : ''">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                            <div x-show="open" x-collapse x-cloak class="mt-1 space-y-1 pl-4">
                                <a href="/ordenvehiculos"
                                    class="flex items-center gap-3 px-3 py-2 text-sm text-zinc-600 hover:text-emerald-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                    </svg>
                                    Generación de órdenes
                                </a>
                                <a href="/vehiculos"
                                    class="flex items-center gap-3 px-3 py-2 text-sm text-zinc-600 hover:text-emerald-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="20" width="20"
                                        viewBox="0 -960 960 960" fill="currentColor">
                                        <path
                                            d="M90-100q-12.38 0-21.19-8.81T60-130v-310l81.16-193q6.27-15.41 19.71-24.47 13.44-9.07 29.9-9.07h344.61q16.37 0 30 9.07 13.64 9.06 20 24.47l80.77 193v310q0 12.38-8.8 21.19-8.81 8.81-21.2 8.81h-24.61q-12.75 0-21.38-8.81-8.62-8.81-8.62-21.19v-48.08H144.61V-130q0 12.38-8.8 21.19Q127-100 114.61-100H90Zm55.08-399.61h435.38l-44.54-106.93H190.23l-45.15 106.93ZM120-238.08h486.15v-201.54H120v201.54Zm101.24-48.46q21.84 0 37.03-15 15.19-15 15.19-36.94 0-21.95-15.28-37.31-15.28-15.36-37.12-15.36-21.83 0-37.02 15.36-15.19 15.36-15.19 37.31 0 21.94 15.28 36.94 15.28 15 37.11 15Zm283.85 0q21.83 0 37.03-15 15.19-15 15.19-36.94 0-21.95-15.29-37.31-15.28-15.36-37.11-15.36t-37.02 15.36q-15.2 15.36-15.2 37.31 0 21.94 15.29 36.94 15.28 15 37.11 15ZM720-216.92v-327.85l-73.77-177.15H246.62l11.07-26.46q6.37-15.41 20-24.48 13.64-9.06 30-9.06H650q16.27 0 29.83 9.04 13.55 9.04 19.78 24.5L780-556.92v304.61q0 14.69-10.35 25.04-10.34 10.35-25.04 10.35H720Zm115.38-116.93v-327.84L762-837.31H363.92L375-863.77q6.36-15.41 20-24.47 13.64-9.07 30-9.07h340.77q16.27 0 29.82 9.04 13.56 9.04 19.79 24.5l80 190.31v304.23q0 14.69-10.34 25.04-10.35 10.34-25.04 10.34h-24.62Zm-472.3-5Z" />
                                    </svg>
                                    Vehículos
                                </a>
                                @if (auth()->user()->is('admin') || auth()->user()->is('supervisor'))
                                    <a href="/dashboard-vehiculos"
                                        class="flex items-center gap-3 px-3 py-2 text-sm text-zinc-600 hover:text-emerald-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125z" />
                                        </svg>
                                        Estadísticas de Vehículos
                                    </a>
                                @endif
                            </div>
                        </div>

                        @is('admin')
                            <div x-data="{ open: false }" class="mt-2">
                                <button @click="open = !open"
                                    class="w-full flex items-center justify-between px-3 py-2 text-base font-bold tracking-wider text-zinc-500 uppercase text-left">
                                    <span>Supervisiones</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4 transition-transform"
                                        :class="open ? 'rotate-180' : ''">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                                <div x-show="open" x-collapse x-cloak class="mt-1 space-y-1 pl-4">
                                    <a href="/dashboard-semanal"
                                        class="flex items-center gap-3 px-3 py-2 text-sm text-zinc-600 hover:text-emerald-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                        </svg>
                                        Semanal
                                    </a>
                                    <a href="/dashboard-diario"
                                        class="flex items-center gap-3 px-3 py-2 text-sm text-zinc-600 hover:text-emerald-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                        </svg>
                                        Diario
                                    </a>
                                </div>
                            </div>
                        @endis

                        @is('admin')
                            <div x-data="{ open: false }" class="mt-2">
                                <button @click="open = !open"
                                    class="w-full flex items-center justify-between px-3 py-2 text-base font-bold tracking-wider text-zinc-500 uppercase text-left">
                                    <span>Ajustes</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4 transition-transform"
                                        :class="open ? 'rotate-180' : ''">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                                <div x-show="open" x-collapse x-cloak class="mt-1 space-y-1 pl-4">
                                    <a href="/users"
                                        class="flex items-center gap-3 px-3 py-2 text-sm text-zinc-600 hover:text-emerald-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                        </svg>
                                        Usuarios
                                    </a>
                                    <a href="/areas"
                                        class="flex items-center gap-3 px-3 py-2 text-sm text-zinc-600 hover:text-emerald-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                        </svg>
                                        Áreas
                                    </a>
                                </div>
                            </div>
                        @endis

                        <div class="mt-auto border-t border-zinc-100 pt-4">
                            <div class="px-3 py-2 mb-2 flex items-center gap-3">
                                <div
                                    class="h-10 w-10 flex items-center justify-center rounded-full bg-emerald-600 text-white font-bold">
                                    {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-zinc-900 leading-tight">
                                        {{ auth()->user()->name ?? 'Usuario' }}</p>
                                    <p class="text-xs text-zinc-500">{{ auth()->user()->user ?? 'sin usuario' }}</p>
                                </div>
                            </div>
                            <a href="/settings/profile"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 text-base font-medium text-zinc-600 hover:bg-zinc-50 hover:text-emerald-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                Mi Perfil
                            </a>
                            <form action="/auth/logout" method="POST">
                                <button type="submit"
                                    class="w-full flex items-center gap-3 rounded-lg px-3 py-2 text-base font-medium text-zinc-600 hover:bg-red-50 hover:text-red-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                    </svg>
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
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
    @if (auth()->user()->is('admin') || auth()->user()->is('supervisor'))
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('notificationSystemCitas', () => ({
                    hasPermission: false,
                    lastCount: 0,
                    firstLoad: true,

                    init() {
                        if ("Notification" in window && Notification.permission !== "granted") {
                            Notification.requestPermission().then(permission => {
                                this.hasPermission = permission === "granted";
                            });
                        } else if (Notification.permission === "granted") {
                            this.hasPermission = true;
                        }

                        this.checkNotifications();
                        setInterval(() => {
                            this.checkNotifications();
                        }, 30000); // Consulta cada 30 segundos
                    },

                    async checkNotifications() {
                        try {
                            const response = await fetch('/api/check-cita-asignada');
                            const data = await response.json();

                            window.dispatchEvent(new CustomEvent('notification-citas', {
                                detail: {
                                    count: data.count
                                }
                            }));

                            if (this.firstLoad) {
                                this.lastCount = data.count;
                                this.firstLoad = false;
                                return;
                            }

                            if (data.alert && data.count > this.lastCount) {
                                this.notify(data.message);
                            }
                            this.lastCount = data.count;
                        } catch (error) {
                            console.error('Error notificaciones Citas:', error);
                        }
                    },

                    notify(message) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                        });

                        Toast.fire({
                            icon: "info",
                            title: "Cita Asignada",
                            text: message,
                            html: `
                        <div class="flex flex-col gap-2">
                            <span>${message}</span>
                            <div class="flex gap-2">
                                <a href="/ordenvehiculos?filtro=citas" 
                                class="flex-1 bg-purple-600 text-white text-xs font-medium px-3 py-1.5 rounded text-center hover:bg-purple-700 transition-colors">
                                    Ver Citas
                                </a>
                                <button onclick="Swal.close()" 
                                        class="flex-1 bg-zinc-100 text-zinc-600 border border-zinc-200 text-xs font-medium px-3 py-1.5 rounded text-center hover:bg-zinc-200 transition-colors cursor-pointer">
                                    Cerrar
                                </button>
                            </div>
                        </div>
                        `
                        });

                        if (this.hasPermission) {
                            const notification = new Notification("Cita vehiculo CFE", {
                                body: message,
                                icon: "/assets/img/logo_cfe.svg",
                                requireInteraction: true,
                                tag: "orden-citas-alert"
                            });
                            notification.onclick = function() {
                                window.focus();
                                window.location.href = '/ordenvehiculos?filtro=citas';
                                this.close();
                            };
                        }
                    }
                }));
            });
        </script>

        {{-- Inicializamos el componente de Citas --}}
        <div x-data="notificationSystemCitas"></div>
    @endif
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
                            const response = await fetch('/api/check-orden-500');
                            const data = await response.json();

                            window.dispatchEvent(new CustomEvent('notification-update', {
                                detail: {
                                    count: data.count
                                }
                            }));

                            if (this.firstLoad) {
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
                            showConfirmButton: false,
                        });

                        Toast.fire({
                            icon: "info",
                            title: "Solicitud 500 Pendiente",
                            text: message,
                            // Botón HTML dentro del Toast para ir rápido
                            html: `
                            <div class="flex flex-col gap-2">
                                <span>${message}</span>
                                <div class="flex gap-2">
                                    <a href="/ordenvehiculos?filtro=orden500" 
                                       class="flex-1 bg-blue-600 text-white text-xs font-medium px-3 py-1.5 rounded text-center hover:bg-blue-700 transition-colors">
                                        Ver Órdenes
                                    </a>

                                    <button onclick="Swal.close()" 
                                            class="flex-1 bg-zinc-100 text-zinc-600 border border-zinc-200 text-xs font-medium px-3 py-1.5 rounded text-center hover:bg-zinc-200 transition-colors cursor-pointer">
                                        Cerrar
                                    </button>
                                </div>
                            </div>
                            `
                        });

                        // B. Notificación Push Nativa (Escritorio)
                        if (this.hasPermission) {
                            const notification = new Notification("Orden vehiculos CFE", {
                                body: message,
                                icon: "/assets/img/logo_cfe.svg",
                                requireInteraction: true,
                                tag: "orden-500-alert" // Tag único para no apilar muchas
                            });

                            notification.onclick = function() {
                                window.focus();
                                window.location.href = '/ordenvehiculos?filtro=orden500';
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

    @is('admin')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('notificationSystemAdmin', () => ({
                    hasPermission: false,
                    lastCount: 0,
                    firstLoad: true,

                    init() {
                        if ("Notification" in window && Notification.permission !== "granted") {
                            Notification.requestPermission().then(permission => {
                                this.hasPermission = permission === "granted";
                            });
                        } else if (Notification.permission === "granted") {
                            this.hasPermission = true;
                        }

                        this.checkNotifications();
                        setInterval(() => {
                            this.checkNotifications();
                        }, 30000); // Consulta cada 30 segundos
                    },

                    async checkNotifications() {
                        try {
                            const response = await fetch('/api/check-orden-arrendado');
                            const data = await response.json();

                            // Disparamos un evento DIFERENTE: 'notification-admin'
                            window.dispatchEvent(new CustomEvent('notification-admin', {
                                detail: {
                                    count: data.count
                                }
                            }));

                            if (this.firstLoad) {
                                this.lastCount = data.count;
                                this.firstLoad = false;
                                return;
                            }

                            if (data.alert && data.count > this.lastCount) {
                                this.notify(data.message);
                            }
                            this.lastCount = data.count;
                        } catch (error) {
                            console.error('Error notificaciones PV:', error);
                        }
                    },

                    notify(message) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                        });

                        Toast.fire({
                            icon: "info",
                            title: "Nueva Solicitud",
                            text: message,
                            html: `
                        <div class="flex flex-col gap-2">
                            <span>${message}</span>
                            <div class="flex gap-2">
                    <a href="/ordenvehiculos?filtro=por_atender" 
                       class="flex-1 bg-blue-600 text-white text-xs font-medium px-3 py-1.5 rounded text-center hover:bg-blue-700 transition-colors">
                        Atender
                    </a>
                    
                    <button onclick="Swal.close()" 
                            class="flex-1 bg-zinc-100 text-zinc-600 border border-zinc-200 text-xs font-medium px-3 py-1.5 rounded text-center hover:bg-zinc-200 transition-colors cursor-pointer">
                        Cerrar
                    </button>
                </div>
                        </div>
                    `
                        });

                        if (this.hasPermission) {
                            const notification = new Notification("PV CFE", {
                                body: message,
                                icon: "/assets/img/logo_cfe.svg",
                                requireInteraction: true,
                                tag: "orden-admin-alert"
                            });
                            notification.onclick = function() {
                                window.focus();
                                window.location.href = '/ordenvehiculos?filtro=por_atender';
                                this.close();
                            };
                        }
                    }
                }));
            });
        </script>
        {{-- Inicializamos el componente de PV --}}
        <div x-data="notificationSystemAdmin"></div>
    @endis
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('citasNavbar', () => ({
                lista_citas: [],

                init() {
                    // Se ejecuta 1 sola vez cuando carga el navbar
                    this.fetchCitasActivas();
                },

                async fetchCitasActivas() {
                    try {
                        const response = await fetch('/api/citas-activas');
                        const data = await response.json();

                        // Si el backend lo manda dentro de la propiedad 'citas'
                        if (data.citas) {
                            this.lista_citas = data.citas;

                            const citasDeHoy = this.lista_citas.filter(cita => cita.texto_fecha ===
                                'Hoy,');

                            if (citasDeHoy.length > 0 && !sessionStorage.getItem(
                                    'alertaCitasHoyMostrada')) {
                                this.mostrarAlertaCitasHoy(citasDeHoy.length);
                                sessionStorage.setItem('alertaCitasHoyMostrada', 'true');
                            }
                        }
                    } catch (error) {
                        console.error('Error cargando citas para el navbar:', error);
                    }
                },

                mostrarAlertaCitasHoy(cantidad) {
                    const plural = cantidad > 1 ? 's' : '';
                    const vehiculoPlural = cantidad > 1 ? 'vehículos' : 'vehículo';

                    Swal.fire({
                        title: '¡Tienes citas para hoy!',
                        text: `Recuerda que tienes ${cantidad} ${vehiculoPlural} con cita programada para ingresar al taller el día de hoy.`,
                        icon: 'warning',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#059669',
                        allowOutsideClick: false
                    });
                },

                formatearFecha(fechaStr) {
                    if (!fechaStr) return 'Sin fecha';
                    const fechaLocal = new Date(fechaStr.replace(/-/g, '/'));
                    return fechaLocal.toLocaleDateString('es-MX', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }).toUpperCase().replace(/\./g, '');
                }
            }));
        });
    </script>
    @if (auth()->user()->is('admin') || auth()->user()->is('supervisor'))
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('notificationSystemSiniestros', () => ({
                    hasPermission: false,
                    lastCount: 0,
                    firstLoad: true,

                    init() {
                        if ("Notification" in window && Notification.permission !== "granted") {
                            Notification.requestPermission().then(permission => {
                                this.hasPermission = permission === "granted";
                            });
                        } else if (Notification.permission === "granted") {
                            this.hasPermission = true;
                        }

                        this.checkNotifications();
                        setInterval(() => {
                            this.checkNotifications();
                        }, 30000); // cada 30 segundos
                    },

                    async checkNotifications() {
                        try {
                            const response = await fetch('/api/check-siniestros');
                            const data = await response.json();

                            window.dispatchEvent(new CustomEvent('notification-siniestros', {
                                detail: {
                                    count: data.count
                                }
                            }));

                            if (this.firstLoad) {
                                this.lastCount = data.count;
                                this.firstLoad = false;
                                return;
                            }

                            if (data.alert && data.count > this.lastCount) {
                                this.notify(data.message);
                            }
                            this.lastCount = data.count;
                        } catch (error) {
                            console.error('Error notificaciones Siniestros:', error);
                        }
                    },

                    notify(message) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                        });

                        Toast.fire({
                            icon: "warning",
                            title: "Siniestros Pendientes",
                            text: message,
                            html: `
                            <div class="flex flex-col gap-2">
                                <span>${message}</span>
                                <div class="flex gap-2">
                                    <a href="/dashboard-vehiculos?tab=siniestros" 
                                       class="flex-1 bg-amber-600 text-white text-xs font-medium px-3 py-1.5 rounded text-center hover:bg-amber-700 transition-colors">
                                        Ver Siniestros
                                    </a>
                                    <button onclick="Swal.close()" 
                                            class="flex-1 bg-zinc-100 text-zinc-600 border border-zinc-200 text-xs font-medium px-3 py-1.5 rounded text-center hover:bg-zinc-200 transition-colors cursor-pointer">
                                        Cerrar
                                    </button>
                                </div>
                            </div>
                        `
                        });

                        if (this.hasPermission) {
                            const notification = new Notification("Siniestros CFE", {
                                body: message,
                                icon: "/assets/img/logo_cfe.svg",
                                requireInteraction: true,
                                tag: "siniestros-alert"
                            });
                            notification.onclick = function() {
                                window.focus();
                                window.location.href = '/dashboard-vehiculos?tab=siniestros';
                                this.close();
                            };
                        }
                    }
                }));
            });
        </script>

        <!-- Inicializamos el componente Alpine -->
        <div x-data="notificationSystemSiniestros"></div>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('notificationSystemMantenimientos', () => ({
                    hasPermission: false,
                    lastCount: 0,
                    firstLoad: true,

                    init() {
                        if ("Notification" in window && Notification.permission !== "granted") {
                            Notification.requestPermission().then(permission => {
                                this.hasPermission = permission === "granted";
                            });
                        } else if (Notification.permission === "granted") {
                            this.hasPermission = true;
                        }

                        this.checkNotifications();
                        setInterval(() => {
                            this.checkNotifications();
                        }, 30000); // Consulta cada 30 segundos
                    },

                    async checkNotifications() {
                        try {
                            const response = await fetch('/api/check-mantenimientos');
                            const data = await response.json();

                            window.dispatchEvent(new CustomEvent('notification-mantenimientos', {
                                detail: {
                                    count: data.count
                                }
                            }));

                            if (this.firstLoad) {
                                this.lastCount = data.count;
                                this.firstLoad = false;
                                return;
                            }

                            if (data.alert && data.count > this.lastCount) {
                                this.notify(data.message);
                            }
                            this.lastCount = data.count;
                        } catch (error) {
                            console.error('Error notificaciones Mantenimientos:', error);
                        }
                    },

                    notify(message) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                        });

                        Toast.fire({
                            icon: "warning",
                            title: "Alerta de Mantenimiento",
                            text: message,
                            html: `
                        <div class="flex flex-col gap-2">
                            <span>${message}</span>
                            <div class="flex gap-2">
                                <a href="/vehiculos?mantenimiento=amarillo" 
                                   class="flex-1 bg-yellow-600 text-white text-xs font-medium px-3 py-1.5 rounded text-center hover:bg-yellow-700 transition-colors">
                                    Ver Vehículos
                                </a>
                                <button onclick="Swal.close()" 
                                        class="flex-1 bg-zinc-100 text-zinc-600 border border-zinc-200 text-xs font-medium px-3 py-1.5 rounded text-center hover:bg-zinc-200 transition-colors cursor-pointer">
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    `
                        });

                        if (this.hasPermission) {
                            const notification = new Notification("Mantenimientos CFE", {
                                body: message,
                                icon: "/assets/img/logo_cfe.svg",
                                requireInteraction: true,
                                tag: "mantenimientos-alert"
                            });
                            notification.onclick = function() {
                                window.focus();
                                window.location.href = '/vehiculos?mantenimiento=urgente';
                                this.close();
                            };
                        }
                    }
                }));
            });
        </script>

        <div x-data="notificationSystemMantenimientos"></div>
    @endif
</body>

</html>
