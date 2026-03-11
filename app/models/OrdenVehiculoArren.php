<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class OrdenVehiculoArren extends Model
{
    protected $table = 'orden_vehiculos_arren';
    protected $primaryKey = 'id_orden';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id_orden',
        'mun_estado_origen',
        'mun_estado_servicio',
        'no_serie',
        'tipo_servicio',
        'fecha_gen',
        'foto_circulacion',
        'foto_odometro',
        'foto_llanta_del_pil',
        'foto_llanta_del_cop',
        'foto_llanta_tra_pil',
        'foto_llanta_tra_cop',
        'fecha_cita',
        'taller'
    ];
    
    public function ordenGeneral()
    {
        return $this->belongsTo(OrdenVehiculo::class, 'id_orden', 'id');
    }

}
