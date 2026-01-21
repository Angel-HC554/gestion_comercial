<?php

namespace App\Controllers;
use App\Models\OrdenVehiculo;
class NotificationController extends Controller
{
    public function checkOrden500()
    {
        // 1. Verificación de Seguridad: Solo usuarios con el permiso/rol
        if (!auth()->user()->can('generar 500')) { // O verifica el rol directamente
            return response()->json(['alert' => false, 'count' => 0]);
        }

        // 2. Consulta: ¿Existe alguna orden con orden_500 = 'SI' y que NO haya sido vista?
        // NOTA: Deberías tener un campo 'visto' o 'atendido' para que no notifique eternamente.
        // Si no tienes ese campo, notificará mientras la orden exista con 'SI'.
        
        $count = OrdenVehiculo::where('orden_500', 'SI')->count();

        return response()->json([
            'alert' => $count > 0,
            'count' => $count,
            'message' => "Existen $count órdenes con solicitud 500 pendientes."
        ]);
    }
}
