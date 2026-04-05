<?php

namespace App\Controllers;

use App\Models\AreaUsuario;
use App\Models\OrdenVehiculo;
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
        if (!$user->is('admin') && !$user->is('supervisor')) {
            return response()->json(['alert' => false, 'count' => 0]);
        }

        $areaUsuario = AreaUsuario::with('area', 'subarea')
            ->where('user_id', $user->id)
            ->first();
        if (!$areaUsuario || !$areaUsuario->area || !$areaUsuario->subarea) {
            return response()->json(['alert' => false, 'count' => 0]);
        }
        $departamentoUser = $areaUsuario->area->nombre;
        $ubicacionUser = $areaUsuario->subarea->nombre;

        $count = OrdenVehiculo::where('status', 'CITA ASIGNADA')
            ->whereHas('vehiculo', function ($query) use ($departamentoUser, $ubicacionUser) {
                $query->where('departamento', $departamentoUser)
                    ->where('ubicacion', $ubicacionUser);
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
}
