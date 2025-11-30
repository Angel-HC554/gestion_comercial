<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehiculoSalidaTaller extends Model
{
    protected $table = 'vehiculo_salidas_taller';

    protected $fillable = [
        'orden_vehiculo_id',
        'kilometraje',
        'fecha_terminacion',
        'servicio',
    ];

    protected $casts = [
        'fecha_terminacion' => 'date',
        'servicio' => 'boolean',
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenVehiculo::class, 'orden_vehiculo_id');
    }
}
