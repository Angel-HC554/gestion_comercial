<?php

namespace App\Controllers;

use App\Models\SupervisionDiaria;
use App\Models\Vehiculo;
use App\Models\OrdenVehiculo;
use Carbon\Carbon;

class DashboardVehiculosController extends Controller
{
    public function index()
    {
        Carbon::setLocale('es');

        $ordenesTaller = OrdenVehiculo::with('detallePropio', 'detalleArrendado')
            ->where('status', 'VEHICULO TALLER')
            ->orderByDesc('created_at')
            ->get();

        $noEconomicosTaller = $ordenesTaller->pluck('noeconomico')->unique()->values();

        $vehiculosMap = Vehiculo::whereIn('no_economico', $noEconomicosTaller)
            ->select('id', 'no_economico', 'marca', 'modelo', 'placas', 'departamento', 'ubicacion')
            ->get()
            ->keyBy('no_economico');

        $vehiculosEnTaller = $ordenesTaller->map(function ($orden) use ($vehiculosMap) {
            $vehiculo = $vehiculosMap->get($orden->noeconomico);

            $nombreTaller = 'No especificado';
            $fechaIngreso = $orden->created_at;

            if ($orden->detallePropio) {
                $nombreTaller = $orden->detallePropio->taller;
                $fechaIngreso = $orden->detallePropio->fecharecep ?? $orden->created_at;
            } elseif ($orden->detalleArrendado) {
                $nombreTaller = 'Arrendado / Externo';
                $fechaIngreso = $orden->detalleArrendado->fecha_gen ?? $orden->created_at;
            }

            return [
                'orden_id' => $orden->id,
                'noeconomico' => $orden->noeconomico,
                'marca' => $orden->marca,
                'placas' => $orden->placas,
                'taller' => $nombreTaller,
                'fecha_ingreso' => $fechaIngreso,
                'link' => $vehiculo ? '/vehiculos/' . $vehiculo->id : null,
                'departamento' => $vehiculo?->departamento,
                'ubicacion' => $vehiculo?->ubicacion,
            ];
        });

        $topReingresos = OrdenVehiculo::selectRaw('noeconomico, marca, placas, COUNT(*) as total, MIN(created_at) as primera_fecha')
            ->groupBy('noeconomico', 'marca', 'placas')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $vehiculoPorNumero = Vehiculo::whereIn('no_economico', $topReingresos->pluck('noeconomico')->unique())
            ->select('id', 'no_economico')
            ->get()
            ->keyBy('no_economico');

        $vehiculosFrecuentes = $topReingresos->map(function ($registro) use ($vehiculoPorNumero) {
            $vehiculo = $vehiculoPorNumero->get($registro->noeconomico);
            return [
                'noeconomico' => $registro->noeconomico,
                'marca' => $registro->marca,
                'placas' => $registro->placas,
                'veces' => $registro->total,
                'primera_fecha' => $registro->primera_fecha,
                'link' => $vehiculo ? '/vehiculos/' . $vehiculo->id : null,
            ];
        });

        $statusBreakdown = OrdenVehiculo::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $statusLabels = array_keys($statusBreakdown);
        $statusValues = array_values($statusBreakdown);

        $topChartLabels = $vehiculosFrecuentes->pluck('noeconomico')->map(fn ($label) => "Eco {$label}")->toArray();
        $topChartValues = $vehiculosFrecuentes->pluck('veces')->toArray();

        $vehiculosMantenimiento = Vehiculo::with(['latestSupervision', 'latestMantenimiento'])
            ->select('id', 'no_economico', 'marca', 'modelo')
            ->get();

        $proximosMantenimiento = $vehiculosMantenimiento->filter(function ($vehiculo) {
            return in_array($vehiculo->estado_mantenimiento, ['amarillo', 'rojo', 'rojo_pasado'], true);
        })->values();

        $siniestros = SupervisionDiaria::with('vehiculo')
            ->where('golpes', true)
            ->orderByDesc('fecha')
            ->limit(8)
            ->get();

        $vehiculosSiniestros = $siniestros->pluck('vehiculo')->filter()->unique('no_economico')->values();

        $ordenes500 = OrdenVehiculo::whereNotNull('orden_500')
            ->where('orden_500', '<>', 'NO')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        render('dashboard.vehiculos', [
            'vehiculosEnTaller' => $vehiculosEnTaller,
            'vehiculosEnTallerCount' => $vehiculosEnTaller->count(),
            'statusLabels' => json_encode($statusLabels),
            'statusValues' => json_encode($statusValues),
            'chartTopLabels' => json_encode($topChartLabels),
            'chartTopValues' => json_encode($topChartValues),
            'vehiculosFrecuentes' => $vehiculosFrecuentes,
            'proximosMantenimiento' => $proximosMantenimiento->map(function ($vehiculo) {
                return [
                    'id' => $vehiculo->id,
                    'noeconomico' => $vehiculo->no_economico,
                    'marca' => $vehiculo->marca,
                    'modelo' => $vehiculo->modelo,
                    'estado' => $vehiculo->estado_mantenimiento,
                    'link' => '/vehiculos/' . $vehiculo->id
                ];
            }),
            'proximosMantenimientoCount' => $proximosMantenimiento->count(),
            'siniestros' => $siniestros,
            'siniestrosVehiculos' => $vehiculosSiniestros,
            'siniestrosCount' => $vehiculosSiniestros->count(),
            'ordenes500' => $ordenes500,
            'ordenes500Count' => $ordenes500->count()
        ]);
    }
}
