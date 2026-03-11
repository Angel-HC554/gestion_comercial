<?php

namespace App\Models;

class Area extends Model
{
    protected $table = 'areas';
    protected $fillable = [
        'nombre',
    ];

    public function subareas()
    {
        return $this->hasMany(Subarea::class, 'area_id', 'id');
    }
}
