<?php

namespace App\Controllers;

use App\Models\OrdenVehiculo;
use App\Models\Vehiculo;
use App\Models\AreaUsuario;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();
        $areasUsuario = AreaUsuario::with('area', 'subarea')
            ->where('user_id', $usuario->id)
            ->get();
        
        $vehiculos = Vehiculo::with([
            'latestSupervision' => function($q) { $q->select('id', 'vehiculo_id', 'kilometraje', 'fecha', 'hora_fin'); },
            'latestMantenimiento'
        ])
        ->select('id', 'marca', 'modelo')
        ->get();

        $conteosMantenimiento = [
            'verde'    => $vehiculos->filter(fn($v) => $v->estado_mantenimiento === 'verde')->count(),
            'amarillo'    => $vehiculos->filter(fn($v) => $v->estado_mantenimiento === 'amarillo')->count(),
            'rojo'        => $vehiculos->filter(fn($v) => $v->estado_mantenimiento === 'rojo')->count(),
            'rojo_pasado' => $vehiculos->filter(fn($v) => $v->estado_mantenimiento === 'rojo_pasado')->count(),
        ];

        // 2. Órdenes Globales (Solo Pendientes y En Taller)
        $ordenesPendientes = OrdenVehiculo::where('status', 'PENDIENTE')->count();
        $ordenesEnTaller   = OrdenVehiculo::where('status', 'EN TALLER')->count();
        
        $totalVehiculos = $vehiculos->count();

        render('pages.dashboard', [
            'conteosMantenimiento' => $conteosMantenimiento,
            'ordenesPendientes'    => $ordenesPendientes,
            'ordenesEnTaller'      => $ordenesEnTaller,
            'totalVehiculos'       => $totalVehiculos,
            'areasUsuario'         => $areasUsuario
        ]);
    }
}