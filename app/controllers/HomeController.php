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
        
        $queryVehiculos = Vehiculo::with([
            'latestSupervision' => function($q) { 
                $q->select('id', 'vehiculo_id', 'kilometraje', 'fecha', 'hora_fin'); 
            },
            'latestMantenimiento',
            'latestSupervisionSemanal'
        ])->where('estado', '!=', 'Fuera de circulacion');

            if ($areasUsuario->isNotEmpty()) {
                $queryVehiculos->where(function ($groupQuery) use ($areasUsuario) {
                    foreach ($areasUsuario as $asignacion) {
                        $groupQuery->orWhere(function ($subQuery) use ($asignacion) {
                            if ($asignacion->area) {
                                $subQuery->where('departamento', $asignacion->area->nombre);
                            }
                            if ($asignacion->subarea) {
                                $subQuery->where('ubicacion', $asignacion->subarea->nombre);
                            }
                        });
                    }
                });
            } else {
                if (!$usuario->is('admin')) {
                    $queryVehiculos->where('id', 0);
                }
            }

        $vehiculos = $queryVehiculos->select('id', 'no_economico', 'marca', 'modelo', 'estado', 'en_taller', 'departamento', 'ubicacion')->get();
        // Obtener un array con los números económicos válidos de su área para filtrar las órdenes
        $noEconomicosArea = $vehiculos->pluck('no_economico')->toArray();

        $conteosMantenimiento = [
            'verde'    => $vehiculos->filter(fn($v) => $v->estado_mantenimiento === 'verde')->count(),
            'amarillo'    => $vehiculos->filter(fn($v) => $v->estado_mantenimiento === 'amarillo')->count(),
            'rojo'        => $vehiculos->filter(fn($v) => $v->estado_mantenimiento === 'rojo')->count(),
            'rojo_pasado' => $vehiculos->filter(fn($v) => $v->estado_mantenimiento === 'rojo_pasado')->count(),
        ];

        // 2. Órdenes Globales (Solo Pendientes y En Taller)
        $queryOrdenesPendientes = OrdenVehiculo::where('status', 'PENDIENTE');
        $queryOrdenesEnTaller   = OrdenVehiculo::where('status', 'EN TALLER');
        if ($areasUsuario->isNotEmpty() || !$usuario->is('admin')) {
            $queryOrdenesPendientes->whereIn('noeconomico', $noEconomicosArea);
            $queryOrdenesEnTaller->whereIn('noeconomico', $noEconomicosArea);
        }
        
        $ordenesPendientes = $queryOrdenesPendientes->count();
        $ordenesEnTaller   = $queryOrdenesEnTaller->count();
        
        $totalVehiculos = $vehiculos->count();

        $supervisionesPendientes = [];
        $supervisionesCompletadasCount = 0;

        foreach ($vehiculos as $v) {
            if ($v->tieneSupervisionSemanal()) {
                $supervisionesCompletadasCount++;
            } else {
                // Coleccionamos los datos de las unidades faltantes
                $supervisionesPendientes[] = [
                    'id'           => $v->id,
                    'no_economico' => $v->no_economico,
                    'en_taller'    => $v->en_taller
                ];
            }
        }

        render('pages.dashboard', [
            'conteosMantenimiento' => $conteosMantenimiento,
            'ordenesPendientes'    => $ordenesPendientes,
            'ordenesEnTaller'      => $ordenesEnTaller,
            'totalVehiculos'       => $totalVehiculos,
            'areasUsuario'         => $areasUsuario,
            'supervisionesPendientes'       => $supervisionesPendientes,
            'supervisionesCompletadasCount' => $supervisionesCompletadasCount,
            'totalAsignados'                => $totalVehiculos
        ]);
    }
}