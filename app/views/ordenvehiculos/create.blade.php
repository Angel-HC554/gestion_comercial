@extends('layouts.app-layout', [
    'title' => 'Crear orden'
])

@section('content')
    <div class="flex justify-start gap-12 items-center mx-10 py-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Crear orden de servicio y reparación</h1>
        <div class="flex items-center gap-2">
            <span class="font-semibold text-zinc-600">Orden No: {{ $id ?? 'Nueva' }}</span>
        </div>
        <a href="{{ $returnUrl ?? '/ordenvehiculos' }}"
            class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
            <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
        </svg>
            Volver
        </a>
    </div>
    @include('ordenvehiculos.formulario')
@endsection
