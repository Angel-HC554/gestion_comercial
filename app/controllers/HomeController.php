<?php

namespace App\Controllers;

use App\Models\OrdenVehiculo;
use App\Models\Vehiculo;

class HomeController extends Controller
{
    public function index()
    {
        // 1. Lógica de conteos de mantenimiento (sin caché)
        $vehiculos = Vehiculo::all();
        $conteosMantenimiento = [
            'amarillo'    => $vehiculos->where('estado_mantenimiento', 'amarillo')->count(),
            'rojo'        => $vehiculos->where('estado_mantenimiento', 'rojo')->count(),
            'rojo_pasado' => $vehiculos->where('estado_mantenimiento', 'rojo_pasado')->count(),
        ];

        // 2. Consultas generales
        $totalOrdenes = OrdenVehiculo::count();
        $ordenesPendientes = OrdenVehiculo::where('status', 'PENDIENTE')->count();
        $ordenesCompletadas = OrdenVehiculo::where('status', 'TERMINADO')->count();
        
        $totalVehiculos = Vehiculo::count();
        // Nota: Asegúrate que estos scopes existan en tu modelo Vehiculo de Leaf
        $vehiculosConDiaria = Vehiculo::conSupervisionDiariaHoy()->count();
        $vehiculosConSemanal = Vehiculo::conSupervisionSemanalEstaSemana()->count();

        // 3. Renderizar vista con datos
        render('pages.dashboard', [
            'conteosMantenimiento' => $conteosMantenimiento,
            'totalOrdenes' => $totalOrdenes,
            'ordenesPendientes' => $ordenesPendientes,
            'ordenesCompletadas' => $ordenesCompletadas,
            'totalVehiculos' => $totalVehiculos,
            'vehiculosConDiaria' => $vehiculosConDiaria,
            'vehiculosConSemanal' => $vehiculosConSemanal
        ]);
    }
}