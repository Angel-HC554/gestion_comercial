<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class OrdenVehiculoPropio extends Model
{
    protected $table = 'orden_vehiculos_propios';
    protected $primaryKey = 'id_orden';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
    'id_orden',
    'area',
    'zona',
    'departamento',
    'taller',
    'fecharecep',
    'radiocom',
    'llantaref',
    'autoestereo',
    'gatoh',
    'llavecruz',
    'extintor',
    'botiquin',
    'escalera',
    'escalerad',
    'gasolina',
    'vehicle1',
    'vehicle2',
    'vehicle3',
    'vehicle4',
    'vehicle5',
    'vehicle6',
    'vehicle7',
    'vehicle8',
    'vehicle9',
    'vehicle10',
    'vehicle11',
    'vehicle12',
    'vehicle13',
    'vehicle14',
    'vehicle15',
    'vehicle16',
    'vehicle17',
    'vehicle18',
    'vehicle19',
    'vehicle20',
    'observacion',
    'fechafirm',
    'areausuaria',
    'rpeusuaria',
    'autoriza',
    'rpejefedpt',
    'resppv',
    'rperesppv',
    ];

    public function ordenGeneral()
    {
        return $this->belongsTo(OrdenVehiculo::class, 'id_orden', 'id');
    }
}
