<?php

namespace App\Controllers;

use App\Models\SupervisionDiaria;
use App\Models\SupervisionSemanal;
use App\Models\Vehiculo;
use App\Models\OrdenVehiculo;
use App\Models\Area;
use Carbon\Carbon;

class DashboardVehiculosController extends Controller
{
    public function index()
    {
        Carbon::setLocale('es');

        // 1. OBTENER FILTRO Y ÁREAS (PROCESOS)
        $procesoFiltro = request()->get('proceso', '');
        $areas = Area::orderBy('nombre')->get();

        // Query base para filtrar vehículos por Proceso
        $vehiculosQuery = Vehiculo::query();
        if ($procesoFiltro) {
            $vehiculosQuery->where('departamento', $procesoFiltro);
        }
        
        $noEconomicosValidos = $vehiculosQuery->pluck('no_economico')->toArray();
        $vehiculosIdsValidos = $vehiculosQuery->pluck('id')->toArray();

        // 2. VEHÍCULOS EN TALLER
        $ordenesTallerQuery = OrdenVehiculo::with('detallePropio', 'detalleArrendado')
            ->where('status', 'VEHICULO TALLER')
            ->orderByDesc('created_at');
            
        if ($procesoFiltro) {
            $ordenesTallerQuery->whereIn('noeconomico', $noEconomicosValidos);
        }
        
        $ordenesTaller = $ordenesTallerQuery->get();

        $vehiculosMap = Vehiculo::whereIn('no_economico', $ordenesTaller->pluck('noeconomico')->unique())
            ->select('id', 'no_economico', 'marca', 'modelo', 'placas', 'departamento', 'ubicacion', 'rpe_responsable')
            ->get()
            ->keyBy('no_economico');

        $vehiculosEnTaller = $ordenesTaller->map(function ($orden) use ($vehiculosMap) {
            $vehiculo = $vehiculosMap->get($orden->noeconomico);

            $nombreTaller = 'No especificado';
            $fechaIngreso = $orden->created_at;
            $observacion = '';

            if ($orden->detallePropio) {
                $nombreTaller = $orden->detallePropio->taller;
                $fechaIngreso = $orden->detallePropio->fecharecep ?? $orden->created_at;
                $observacion = $orden->detallePropio->observacion;
            } elseif ($orden->detalleArrendado) {
                $nombreTaller = !empty($orden->detalleArrendado->taller) ? $orden->detalleArrendado->taller : 'Arrendado / Externo';
                $fechaIngreso = $orden->detalleArrendado->fecha_gen ?? $orden->created_at;
                $observacion = $orden->detalleArrendado->tipo_servicio;
            }

            // Calculamos los días en taller
            $diasEnTaller = Carbon::parse($fechaIngreso)->startOfDay()->diffInDays(Carbon::now()->startOfDay());

            return [
                'orden_id' => $orden->id,
                'noeconomico' => $orden->noeconomico,
                'marca' => $orden->marca,
                'placas' => $orden->placas,
                'taller' => $nombreTaller,
                'fecha_ingreso' => $fechaIngreso,
                'dias_taller' => $diasEnTaller,
                'observacion' => $observacion,
                'link' => $vehiculo ? '/vehiculos/' . $vehiculo->id : null,
                'departamento' => $vehiculo?->departamento,
                'ubicacion' => $vehiculo?->ubicacion,
                'rpe' => $vehiculo?->rpe_responsable,
            ];
        });

        // 3. TOP REINGRESOS (Más frecuentes)
        $topReingresosQuery = OrdenVehiculo::selectRaw('noeconomico, marca, placas, COUNT(*) as total, MIN(created_at) as primera_fecha')
            ->groupBy('noeconomico', 'marca', 'placas')
            ->orderByDesc('total')
            ->limit(6);
            
        if ($procesoFiltro) {
            $topReingresosQuery->whereIn('noeconomico', $noEconomicosValidos);
        }
        $topReingresos = $topReingresosQuery->get();

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

        // 4. GRÁFICA DE STATUS
        $statusBreakdownQuery = OrdenVehiculo::selectRaw('status, COUNT(*) as total')->groupBy('status')->orderBy('status');
        if ($procesoFiltro) {
            $statusBreakdownQuery->whereIn('noeconomico', $noEconomicosValidos);
        }
        $statusBreakdown = $statusBreakdownQuery->pluck('total', 'status')->toArray();
        $statusLabels = array_keys($statusBreakdown);
        $statusValues = array_values($statusBreakdown);

        $topChartLabels = $vehiculosFrecuentes->pluck('noeconomico')->map(fn ($label) => "Eco {$label}")->toArray();
        $topChartValues = $vehiculosFrecuentes->pluck('veces')->toArray();

        // 5. PRÓXIMOS A MANTENIMIENTO
        $vehiculosMantenimientoQuery = clone $vehiculosQuery;
        $vehiculosMantenimiento = $vehiculosMantenimientoQuery->with(['latestSupervision', 'latestMantenimiento'])
            ->select('id', 'no_economico', 'marca', 'modelo', 'placas', 'departamento', 'rpe_responsable')
            ->get();

        $proximosMantenimiento = $vehiculosMantenimiento->map(function ($vehiculo) {
            $info = $vehiculo->info_mantenimiento;
            return [
                'id' => $vehiculo->id,
                'noeconomico' => $vehiculo->no_economico,
                'marca' => $vehiculo->marca,
                'modelo' => $vehiculo->modelo,
                'placas' => $vehiculo->placas,
                'departamento' => $vehiculo->departamento,
                'rpe' => $vehiculo->rpe_responsable,
                'estado' => $vehiculo->estado_mantenimiento, // verde, amarillo, rojo, rojo_pasado
                'km_faltantes' => $info['km_faltantes'] ?? 0,
                'dias_restantes' => $info['dias_restantes'] ?? null,
                'link' => '/vehiculos/' . $vehiculo->id
            ];
        })->filter(function ($vehiculo) {
            return in_array($vehiculo['estado'], ['amarillo', 'rojo', 'rojo_pasado'], true);
        })->values();

        // 6. SINIESTROS - LÓGICA DE ÚLTIMO REPORTE (AUTO-CANCELACIÓN)
        // A) Obtener el último reporte diario de CADA vehículo
        $diariasQuery = SupervisionDiaria::with('vehiculo')
            ->orderByDesc('fecha')->orderByDesc('created_at');
        if ($procesoFiltro) {
            $diariasQuery->whereIn('vehiculo_id', $vehiculosIdsValidos);
        }
        // unique('vehiculo_id') nos asegura quedarnos solo con la más reciente de cada vehículo
        $latestDiarias = $diariasQuery->get()->unique('vehiculo_id')->keyBy('vehiculo_id');

        // B) Obtener el último reporte semanal de CADA vehículo
        $semanalesQuery = SupervisionSemanal::with('vehiculo')
            ->orderByDesc('fecha_captura')->orderByDesc('created_at');
        if ($procesoFiltro) {
            $semanalesQuery->whereIn('vehiculo_id', $vehiculosIdsValidos);
        }
        $latestSemanales = $semanalesQuery->get()->unique('vehiculo_id')->keyBy('vehiculo_id');

        $todosSiniestrosActivos = collect();
        $vehiculosAEvaluar = $procesoFiltro ? $vehiculosIdsValidos : Vehiculo::pluck('id')->toArray();

        // C) Evaluar cuál es el reporte ABSOLUTO más reciente de cada vehículo
        foreach ($vehiculosAEvaluar as $vid) {
            $diaria = $latestDiarias->get($vid);
            $semanal = $latestSemanales->get($vid);

            $latestRecord = null;
            $tipoLatest = null;

            if ($diaria && $semanal) {
                $fechaD = Carbon::parse($diaria->fecha)->startOfDay();
                $fechaS = Carbon::parse($semanal->fecha_captura)->startOfDay();
                
                // Si son del mismo día, priorizamos la que TENGA reporte de golpe para no ocultarlo accidentalmente ese mismo día
                if ($fechaD->equalTo($fechaS)) {
                    if ($diaria->golpes) {
                        $latestRecord = $diaria;
                        $tipoLatest = 'diaria';
                    } else {
                        $latestRecord = $semanal;
                        $tipoLatest = 'semanal';
                    }
                } elseif ($fechaD->greaterThan($fechaS)) {
                    $latestRecord = $diaria;
                    $tipoLatest = 'diaria';
                } else {
                    $latestRecord = $semanal;
                    $tipoLatest = 'semanal';
                }
            } elseif ($diaria) {
                $latestRecord = $diaria;
                $tipoLatest = 'diaria';
            } elseif ($semanal) {
                $latestRecord = $semanal;
                $tipoLatest = 'semanal';
            }

            // D) Solo si su ÚLTIMO reporte indica golpe/atentado, lo mostramos
            if ($latestRecord) {
                if ($tipoLatest === 'diaria' && $latestRecord->golpes) {
                    $todosSiniestrosActivos->push((object)[
                        'tipo' => 'Supervisión Diaria',
                        'no_eco' => $latestRecord->no_eco,
                        'fecha' => $latestRecord->fecha,
                        'detalles' => $latestRecord->golpes_coment ?? 'Golpe reportado sin detalle',
                        'vehiculo' => $latestRecord->vehiculo,
                        'link_evidencia' => null
                    ]);
                } elseif ($tipoLatest === 'semanal' && !empty($latestRecord->foto_atent)) {
                    $todosSiniestrosActivos->push((object)[
                        'tipo' => 'Supervisión Semanal',
                        'no_eco' => $latestRecord->no_eco,
                        'fecha' => $latestRecord->fecha_captura,
                        'detalles' => 'Atentado / Daño registrado con evidencia',
                        'vehiculo' => $latestRecord->vehiculo,
                        'link_evidencia' => $latestRecord->foto_atent
                    ]);
                }
            }
        }

        // Ordenamos los siniestros activos para mostrarlos en la tabla
        $todosSiniestros = $todosSiniestrosActivos->sortByDesc('fecha')->values();
        $siniestrosVehiculosUnicos = $todosSiniestros->unique('no_eco')->count();

        // 7. ORDENES 500
        $ordenes500Query = OrdenVehiculo::with(['vehiculo' => function($q) {
                $q->select('no_economico', 'departamento', 'rpe_responsable');
            }])
            ->whereNotNull('orden_500')
            ->where('orden_500', '<>', 'NO')
            ->orderByDesc('created_at');
            
        if ($procesoFiltro) {
            $ordenes500Query->whereIn('noeconomico', $noEconomicosValidos);
        }
        $ordenes500 = $ordenes500Query->get();

        render('dashboard.vehiculos', [
            'areas' => $areas,
            'procesoFiltro' => $procesoFiltro,
            
            'vehiculosEnTaller' => $vehiculosEnTaller,
            'vehiculosEnTallerCount' => $vehiculosEnTaller->count(),
            
            'statusLabels' => json_encode($statusLabels),
            'statusValues' => json_encode($statusValues),
            'chartTopLabels' => json_encode($topChartLabels),
            'chartTopValues' => json_encode($topChartValues),
            'vehiculosFrecuentes' => $vehiculosFrecuentes,
            
            'proximosMantenimiento' => $proximosMantenimiento,
            'proximosMantenimientoCount' => $proximosMantenimiento->count(),
            
            'siniestros' => $todosSiniestros,
            'siniestrosCount' => $siniestrosVehiculosUnicos, // KPI: Vehículos únicos
            
            'ordenes500' => $ordenes500,
            'ordenes500Count' => $ordenes500->count()
        ]);
    }
}