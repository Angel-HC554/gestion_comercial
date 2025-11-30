@extends('layouts.app-layout')

@section('title', 'Crear Orden')

@section('content')
<div class="flex justify-between items-center mx-10 mb-6">
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Crear orden de servicio y reparación</h1>
    <div class="flex items-center gap-2">
        <span class="font-semibold text-zinc-600">Orden No: {{ $id ?? 'Nueva' }}</span>
    </div>
    <a href="/ordenvehiculos" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
        </svg>
        Volver
    </a>
</div>
@include('ordenvehiculos.formulario')
@endsection