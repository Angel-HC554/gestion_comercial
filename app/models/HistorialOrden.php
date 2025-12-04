<?php

namespace App\Models;

use Leaf\Model;

class HistorialOrden extends Model
{
    // Nombre de la tabla definido en el YAML
    protected $table = 'historial_ordenes';

    protected $fillable = [
        'orden_vehiculo_id', 
        'tipo_evento', 
        'detalles', 
        'old_value', 
        'new_value'
    ];

    public $timestamps = true;

    /**
     * Relación con OrdenVehiculo
     */
    public function ordenVehiculo()
    {
        return $this->belongsTo(OrdenVehiculo::class, 'orden_vehiculo_id');
    }
}