<?php

namespace App\Models;

class OrdenArchivo extends Model
{
    protected $table = 'orden_archivos';

    protected $fillable = [
        'orden_vehiculo_id',
        'ruta_archivo',
        'comentarios',
    ];

    /**
     * Get the ordenVehiculo that owns the OrdenArchivo
     */
    public function ordenVehiculo()
    {
        return $this->belongsTo(OrdenVehiculo::class, 'orden_vehiculo_id');
    }
}
