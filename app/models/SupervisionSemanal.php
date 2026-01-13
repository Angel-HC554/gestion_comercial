<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class SupervisionSemanal extends Model
{
    protected $table = 'supervision_semanal';

    protected $fillable = [
        'vehiculo_id', 
        'user_id',
        'no_eco',
        'fecha_captura',
        'foto_del', 
        'foto_tra', 
        'foto_lado_der', 
        'foto_lado_izq', 
        'foto_poliza',
        'foto_tar_circ',
        'foto_kit',
        'foto_atent',
        'foto_llanta_ref',
        'resumen_est'
    ];

    protected $casts = [
        'fecha_captura' => 'date',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function user()
    {
        // Asegúrate de tener el modelo User creado, si no, comenta esto
        return $this->belongsTo(User::class);
    }
}