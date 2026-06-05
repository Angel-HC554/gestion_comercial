<?php

namespace App\Controllers;

use App\Models\AreaUsuario;
use App\Models\OrdenVehiculo;
use App\Models\Vehiculo;
use Leaf\Http\Response;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function checkOrden500()
    {
        if (!auth()->user()->can('generar 500')) {
            return response()->json(['alert' => false, 'count' => 0]);
        }

        $count = OrdenVehiculo::where('orden_500', 'SI')->count();

        return response()->json([
            'alert' => $count > 0,
            'count' => $count,
            'message' => "Existen $count órdenes con solicitud 500 pendientes."
        ]);
    }

    public function checkOrdenArrendado()
    {
        if (!auth()->user()->is('admin')) {
            return response()->json(['alert' => false, 'count' => 0]);
        }

        $count = OrdenVehiculo::where('status', 'ENVIADO A PV')->count();

        return response()->json([
            'alert' => $count > 0,
            'count' => $count,
            'message' => "Existen $count órdenes pendientes por atender."
        ]);
    }

    public function checkCitaAsignada()
    {
        $user = auth()->user();
        
        // Permitir tanto a admin como a supervisor
        if (!$user->is('admin') && !$user->is('supervisor')) {
            return response()->json(['alert' => false, 'count' => 0]);
        }

        // Obtenemos TODAS las áreas/subáreas asignadas al usuario (sea admin o supervisor)
        $asignaciones = AreaUsuario::with('area', 'subarea')->where('user_id', $user->id)->get();
        
        // Si no tiene áreas asignadas, no le mostramos nada para evitar spam
        if ($asignaciones->isEmpty()) {
            return response()->json(['alert' => false, 'count' => 0]);
        }

        $count = OrdenVehiculo::where('status', 'CITA ASIGNADA')
            ->whereHas('vehiculo', function ($queryVehiculo) use ($asignaciones) {
                $queryVehiculo->where(function ($q) use ($asignaciones) {
                    foreach ($asignaciones as $asignacion) {
                        $q->orWhere(function ($subQuery) use ($asignacion) {
                            if ($asignacion->area) {
                                $subQuery->where('departamento', $asignacion->area->nombre);
                            }
                            if ($asignacion->subarea) {
                                $subQuery->where('ubicacion', $asignacion->subarea->nombre);
                            }
                        });
                    }
                });
            })
            ->count();

        return response()->json([
            'alert' => $count > 0,
            'count' => $count,
            'message' => "Existen $count órdenes con cita asignada en tu área."
        ]);
    }

    public function notificacionesActivasold()
    {
        $citas = OrdenVehiculo::with('detalleArrendado')->where('status', 'CITA ASIGNADA')->latest()->take(3)->get();
        // Recorremos las citas para agregarles el texto dinámico
        $citas->map(function ($cita) {
            if ($cita->detalleArrendado && $cita->detalleArrendado->fecha_cita) {

                // Convertimos las fechas a inicio de día para que el cálculo sea exacto (sin importar la hora)
                $fechaCita = Carbon::parse($cita->detalleArrendado->fecha_cita)->startOfDay();
                $hoy = Carbon::now()->startOfDay();

                // Calculamos la diferencia en días
                $diferenciaDias = $hoy->diffInDays($fechaCita, false);

                if ($diferenciaDias == 0) {
                    $cita->texto_fecha = 'Hoy,';
                } elseif ($diferenciaDias == 1) {
                    $cita->texto_fecha = 'Mañana,';
                } elseif ($diferenciaDias > 1) {
                    $cita->texto_fecha = 'En ' . $diferenciaDias . ' días,';
                } elseif ($diferenciaDias == -1) {
                    $cita->texto_fecha = 'Ayer,';
                } elseif ($diferenciaDias < -1) {
                    // Por si se les olvidó cambiar el estatus y la cita fue hace una semana
                    $cita->texto_fecha = 'Hace ' . abs($diferenciaDias) . ' días,';
                }
            } else {
                $cita->texto_fecha = '';
            }

            return $cita;
        });
        return response()->json(['citas' => $citas]);
    }

    public function notificacionesActivas()
    {
        $user = auth()->user();
        $query = OrdenVehiculo::with('detalleArrendado')->where('status', 'CITA ASIGNADA');
        if (!$user->is('admin')) {
            $asignaciones = AreaUsuario::with('area', 'subarea')->where('user_id', $user->id)->get();
            if ($asignaciones->isNotEmpty()) {
                $query->whereHas('vehiculo', function ($queryVehiculo) use ($asignaciones) {
                    $queryVehiculo->where(function ($q) use ($asignaciones) {
                        foreach ($asignaciones as $asignacion) {
                            $q->orWhere(function ($subQuery) use ($asignacion) {
                                if ($asignacion->area) {
                                    $subQuery->where('departamento', $asignacion->area->nombre);
                                }
                                if ($asignacion->subarea) {
                                    $subQuery->where('ubicacion', $asignacion->subarea->nombre);
                                }
                            });
                        }
                    });
                });
            } else {
                $query->where('id', 0);
            }
        }
        $citas = $query->latest()->take(3)->get();
        // Recorremos las citas para agregarles el texto dinámico
        $citas = $citas->map(function ($cita) {
            if ($cita->detalleArrendado && $cita->detalleArrendado->fecha_cita) {

                // Convertimos las fechas a inicio de día para que el cálculo sea exacto (sin importar la hora)
                $fechaCita = Carbon::parse($cita->detalleArrendado->fecha_cita)->startOfDay();
                $hoy = Carbon::now()->startOfDay();

                // Calculamos la diferencia en días (el 'false' es para que nos dé números negativos si la cita ya pasó)
                $diferenciaDias = $hoy->diffInDays($fechaCita, false);

                if ($diferenciaDias == 0) {
                    $cita->texto_fecha = 'Hoy,';
                } elseif ($diferenciaDias == 1) {
                    $cita->texto_fecha = 'Mañana,';
                } elseif ($diferenciaDias > 1) {
                    $cita->texto_fecha = 'En ' . $diferenciaDias . ' días,';
                } elseif ($diferenciaDias == -1) {
                    $cita->texto_fecha = 'Ayer,';
                } elseif ($diferenciaDias < -1) {
                    // Por si se les olvidó cambiar el estatus y la cita fue hace una semana
                    $cita->texto_fecha = 'Hace ' . abs($diferenciaDias) . ' días,';
                }
            } else {
                $cita->texto_fecha = '';
            }

            return $cita;
        });
        return response()->json(['citas' => $citas]);
    }

public function checkSiniestrosPendientes()
{
    $user = auth()->user();

        // Ahora permitimos que admin y supervisor entren
        if (!$user->is('admin') && !$user->is('supervisor')) {
            return response()->json(['alert' => false, 'count' => 0]);
        }

        // Obtenemos las asignaciones de área del usuario
        $asignaciones = AreaUsuario::with('area', 'subarea')->where('user_id', $user->id)->get();

        if ($asignaciones->isEmpty()) {
            return response()->json(['alert' => false, 'count' => 0]);
        }

        // Filtramos desde la consulta principal solo los vehículos que pertenecen a su área
        // Esto además optimiza la memoria, porque ya no traemos TODA la flotilla
        $vehiculos = Vehiculo::where('estado', '!=', 'Fuera de circulacion')
            ->where(function ($queryVehiculo) use ($asignaciones) {
                foreach ($asignaciones as $asignacion) {
                    $queryVehiculo->orWhere(function ($subQuery) use ($asignacion) {
                        if ($asignacion->area) {
                            $subQuery->where('departamento', $asignacion->area->nombre);
                        }
                        if ($asignacion->subarea) {
                            $subQuery->where('ubicacion', $asignacion->subarea->nombre);
                        }
                    });
                }
            })
            ->get();

        $vehiculosConGolpeNoAtendido = 0;

        foreach ($vehiculos as $vehiculo) {
            $diaria = \App\Models\SupervisionDiaria::where('vehiculo_id', $vehiculo->id)
                ->orderByDesc('fecha')->orderByDesc('created_at')->first();
                
            $semanal = \App\Models\SupervisionSemanal::where('vehiculo_id', $vehiculo->id)
                ->orderByDesc('fecha_captura')->orderByDesc('created_at')->first();

            $ultimoReporte = null;
            $fechaReporte = null;
            $tieneGolpe = false;

            if ($diaria && $semanal) {
                $fD = \Carbon\Carbon::parse($diaria->fecha);
                $fS = \Carbon\Carbon::parse($semanal->fecha_captura);
                if ($fD->gt($fS)) {
                    $ultimoReporte = $diaria;
                    $fechaReporte = $fD;
                    $tieneGolpe = $diaria->golpes;
                } else {
                    $ultimoReporte = $semanal;
                    $fechaReporte = $fS;
                    $tieneGolpe = !empty($semanal->foto_atent);
                }
            } elseif ($diaria) {
                $fechaReporte = \Carbon\Carbon::parse($diaria->fecha);
                $tieneGolpe = $diaria->golpes;
            } elseif ($semanal) {
                $fechaReporte = \Carbon\Carbon::parse($semanal->fecha_captura);
                $tieneGolpe = !empty($semanal->foto_atent);
            }

            if ($tieneGolpe && $fechaReporte) {
                $existeOrdenPosterior = \App\Models\OrdenVehiculo::where('noeconomico', $vehiculo->no_economico)
                    ->where('created_at', '>', $fechaReporte->endOfDay())
                    ->exists();

                if (!$existeOrdenPosterior) {
                    $vehiculosConGolpeNoAtendido++;
                }
            }
        }

        return response()->json([
            'alert' => $vehiculosConGolpeNoAtendido > 0,
            'count' => $vehiculosConGolpeNoAtendido,
            'message' => "Hay $vehiculosConGolpeNoAtendido vehículo(s) con siniestros sin orden en tu área."
        ]);
}

    public function checkAlertasMantenimiento()
    {
        $usuario = auth()->user();

        // 1. Obtener áreas asignadas al usuario
        $areasUsuario = AreaUsuario::with('area', 'subarea')
            ->where('user_id', $usuario->id)
            ->get();

        // 2. Si no es admin y no tiene áreas asignadas, no le mostramos alertas
        if (!$usuario->is('admin') && $areasUsuario->isEmpty()) {
            return response()->json(['alert' => false, 'count' => 0]);
        }

        // 3. Consultar vehículos (cargamos las mismas relaciones que el HomeController para que el cálculo funcione)
        $queryVehiculos = Vehiculo::with([
            'latestSupervision' => function($q) { 
                $q->select('id', 'vehiculo_id', 'kilometraje', 'fecha', 'hora_fin'); 
            },
            'latestMantenimiento',
            'latestSupervisionSemanal'
        ])->where('estado', '!=', 'Fuera de circulacion');

        // 4. Filtrar por área (igual que en el Dashboard)
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
        }

        $vehiculos = $queryVehiculos->get();

        // 5. Contar los vehículos que tengan estado amarillo, rojo o rojo_pasado
        $alertasCount = $vehiculos->filter(function ($v) {
            return in_array($v->estado_mantenimiento, ['amarillo', 'rojo', 'rojo_pasado']);
        })->count();

        return response()->json([
            'alert'   => $alertasCount > 0,
            'count'   => $alertasCount,
            'message' => "Tienes $alertasCount vehículo(s) con mantenimientos urgentes o próximos a vencer."
        ]);
    }
}
