@extends('layouts.app-layout')

@section('title', 'Listado de Órdenes')

@section('content')

    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="ordenesTable()" x-init="fetchData()" x-cloak>
        @csrf
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl tracking-tight font-bold text-zinc-900">Órdenes de servicio y reparación</h1>
            <a href="/ordenvehiculos/create"
                class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors text-sm">
                CREAR ORDEN
            </a>
        </div>

        @include('ordenvehiculos.tabla')
    @endsection
