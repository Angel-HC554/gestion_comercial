<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class SupervisionDiaria extends Model
{
    protected $table = 'supervision_diaria';

    protected $fillable = [
        'vehiculo_id',
        'no_eco',
        'nombre_auxiliar',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'gasolina',
        'kilometraje',
        'aceite',
        'liq_fren',
        'anti_con',
        'agua',
        'radiador',
        'llantas',
        'llanta_r',
        'tapon_gas',
        'limp_cab',
        'limp_ext',
        'cinturon',
        'limpia_par',
        'manijas_puer',
        'espejo_int',
        'espejo_lat_i',
        'espejo_lat_d',
        'gato',
        'llave_cruz',
        'extintor',
        'direccionales',
        'luces',
        'intermit',
        'golpes',
        'golpes_coment',
        'escaneo_url',
    ];

    // Estos casts son fundamentales para que los checkboxes funcionen bien
    protected $casts = [
        'fecha' => 'date',
        // 'datetime:H:i' formatea la salida JSON automáticamente
        'hora_inicio' => 'datetime:H:i', 
        'hora_fin' => 'datetime:H:i',
        'gasolina' => 'string',
        'aceite' => 'boolean',
        'liq_fren' => 'boolean',
        'anti_con' => 'boolean',
        'agua' => 'boolean',
        'radiador' => 'boolean',
        'llantas' => 'boolean',
        'llanta_r' => 'boolean',
        'tapon_gas' => 'boolean',
        'limp_cab' => 'boolean',
        'limp_ext' => 'boolean',
        'cinturon' => 'boolean',
        'limpia_par' => 'boolean',
        'manijas_puer' => 'boolean',
        'espejo_int' => 'boolean',
        'espejo_lat_i' => 'boolean',
        'espejo_lat_d' => 'boolean',
        'gato' => 'boolean',
        'llave_cruz' => 'boolean',
        'extintor' => 'boolean',
        'direccionales' => 'boolean',
        'luces' => 'boolean',
        'intermit' => 'boolean',
        'golpes' => 'boolean',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }
}