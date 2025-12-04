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

<body class="font-sans text-zinc-800 antialiased bg-zinc-100" x-data="{ mobileMenuOpen: false }">

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

                <nav class="hidden lg:flex items-center gap-6 ml-6">
                    <a href="/dashboard"
                        class="flex items-center gap-2 text-sm font-medium text-zinc-600 hover:text-zinc-900">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        Inicio
                    </a>

                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open"
                            class="flex items-center gap-1 text-sm font-medium text-zinc-600 hover:text-zinc-900 focus:outline-none group">
                            Opciones
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor"
                                class="w-4 h-4 text-zinc-400 group-hover:text-zinc-600 transition-transform duration-200"
                                :class="open ? 'rotate-180' : ''">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div x-show="open" x-transition.opacity.duration.200ms x-cloak
                            class="absolute left-0 mt-2 w-64 origin-top-left rounded-xl bg-white py-2 shadow-xl ring-1 ring-zinc-900/5 focus:outline-none z-50">

                            <a href="/ordenvehiculos"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-emerald-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-5 text-zinc-400">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                Generación de órdenes
                            </a>

                            <a href="/vehiculos"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-emerald-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-5 text-zinc-400">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                </svg>
                                Vehículos
                            </a>

                            <div class="border-t border-zinc-100 my-2"></div>
                            <div class="px-4 py-1 text-xs font-bold tracking-wider text-zinc-400 uppercase">
                                Supervisiones</div>

                            <a href="/dashboard-semanal"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-emerald-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-zinc-400">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                Semanal
                            </a>
                            <a href="/dashboard-diario"
                                class="flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-emerald-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-zinc-400">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                </svg>
                                Diario
                            </a>
                        </div>
                    </div>
                </nav>
            </div>

            <div class="flex items-center gap-4">

                <button class="text-zinc-400 hover:text-zinc-600 relative">
                    <span class="absolute -top-1 -right-1 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                </button>

                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                    <button @click="open = !open"
                        class="flex items-center gap-2 rounded-lg p-1 hover:bg-zinc-100 transition-colors">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white text-xs font-bold ring-1 ring-zinc-900/5">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <span class="hidden text-sm font-medium text-zinc-700 md:block">
                            {{ strtok(auth()->user()->name ?? 'Usuario', ' ') }}
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4 text-zinc-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div x-show="open" x-transition.opacity.duration.200ms x-cloak
                        class="absolute right-0 mt-2 w-56 origin-top-right rounded-xl bg-white py-1 shadow-xl ring-1 ring-zinc-900/5 focus:outline-none z-50">

                        <div class="px-4 py-3 border-b border-zinc-100">
                            <p class="text-sm font-medium text-zinc-900">
                                {{ auth()->user()->name ?? 'Usuario Invitado' }}
                            </p>
                            <p class="text-xs text-zinc-500 truncate">{{ auth()->user()->email ?? 'sin-email' }}</p>
                        </div>

                        <a href="/settings/profile"
                            class="flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-emerald-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-5 h-5 text-zinc-400">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Configuración
                        </a>

                        <form action="/auth/logout" method="POST" class="block border-t border-zinc-100 mt-1">
                            <button type="submit"
                                class="w-full flex items-center gap-3 px-4 py-2 text-sm text-zinc-700 hover:bg-zinc-50 hover:text-red-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-zinc-400">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
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
            x-transition:leave-end="opacity-0" class="fixed inset-0 bg-zinc-900/80 backdrop-blur-sm" aria-hidden="true">
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
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="flex flex-col gap-1 px-4 overflow-y-auto">
                    <a href="/dashboard"
                        class="flex items-center gap-3 rounded-lg bg-zinc-50 px-3 py-2 text-base font-medium text-zinc-900">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6 text-zinc-500">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        Inicio
                    </a>

                    <div class="mt-4 mb-2 px-3 text-xs font-bold tracking-wider text-zinc-400 uppercase">Módulos</div>

                    <a href="/ordenes"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-base font-medium text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6 text-zinc-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        Ordenes
                    </a>
                    <a href="/vehiculos"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-base font-medium text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6 text-zinc-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V14.25m-3 0h3m-3 8.25a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3-1.5H4.75a1.125 1.125 0 01-1.125-1.125V4.125C3.625 3.504 4.129 3 4.75 3h12.75c.621 0 1.125.504 1.125 1.125V14.25" />
                        </svg>
                        Vehículos
                    </a>

                    <div class="mt-4 mb-2 px-3 text-xs font-bold tracking-wider text-zinc-400 uppercase">Supervisiones
                    </div>
                    <a href="/supervision-semanal"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-base font-medium text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6 text-zinc-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        Semanal
                    </a>
                    <a href="/supervision-diaria"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-base font-medium text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6 text-zinc-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                        </svg>
                        Diaria
                    </a>
                </nav>
            </div>
        </div>
    </div>

    <main class="py-2 px-5">
        @yield('content')
    </main>

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
</body>

</html>