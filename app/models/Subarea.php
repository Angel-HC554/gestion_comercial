<?php

namespace App\Models;

class Subarea extends Model
{
    protected $table = 'subareas';
    protected $fillable = [
        'area_id',
        'nombre',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id', 'id');
    }
}
