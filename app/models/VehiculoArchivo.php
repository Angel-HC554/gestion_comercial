<?php

namespace App\Models;

class VehiculoArchivo extends Model
{
    protected $table = 'vehiculo_archivos';
    
    protected $fillable = [
        'vehiculo_id',
        'nombre',
        'ruta_archivo'
    ];
}
