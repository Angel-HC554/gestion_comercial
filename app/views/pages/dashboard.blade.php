@extends('layouts.app-layout', [
    'title' => 'Dashboard',
    'breadcrumbs' => [
        [
            'title' => 'Dashboard',
            'href' => '/dashboard',
        ]
    ]
])

@section('content')
    <div class="py-4 px-4">
        <div class="overflow-hidden shadow-sm sm:rounded-lg bg-black">
            <div class="p-6 text-gray-100">You're logged in!</div>
        </div>
    </div>
    @if(auth()->user() && auth()->user()->can('eliminar ordenes'))
        <p>Eres admin</p>
    @endif
    @can('eliminar ordenes')
        <p>Eres admin yeah</p>
    @endcan
@endsection
