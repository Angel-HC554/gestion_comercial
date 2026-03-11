<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use App\Models\OrdenVehiculoArren;
use App\Models\OrdenVehiculoPropio;
use App\Models\VehiculoSalidaTaller;
use App\Models\Vehiculo;

class OrdenVehiculo extends Model
{
    protected $table = 'orden_vehiculos';

    protected $fillable = [
        'tipo_vehiculo',
        'noeconomico',
        'marca',
        'placas',
        'kilometraje',
        'orden_500',
        'requiere_servicio',
        'status',
    ];

    //Relacion 1 a 1 con arrendados
    public function detalleArrendado()
    {
        return $this->hasOne(OrdenVehiculoArren::class, 'id_orden', 'id');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'noeconomico', 'no_economico');
    }

    //Relacion 1 a 1 con propios
    public function detallePropio()
    {
        return $this->hasOne(OrdenVehiculoPropio::class, 'id_orden', 'id');
    }

    /**
     * Relación para obtener UN SOLO archivo (el más reciente).
     * Esta es la que usa tu botón de descarga.
     */
    public function archivo()
    {
        return $this->hasOne(OrdenArchivo::class, 'orden_vehiculo_id')->latest();
    }
    /**
     * Get all of the archivos for the OrdenVehiculo
     *
     */
    public function archivos()
    {
        return $this->hasMany(OrdenArchivo::class, 'orden_vehiculo_id');
    }

    public function historial()
    {
        return $this->hasMany(HistorialOrden::class, 'orden_vehiculo_id');
    }

    public function salidasTaller()
    {
        return $this->hasMany(VehiculoSalidaTaller::class, 'orden_vehiculo_id');
    }
}
