<?php

namespace App\Controllers;

use App\Models\AreaUsuario;
use App\Models\OrdenVehiculo;
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
}
