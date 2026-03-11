<?php

namespace App\Models;

class AreaUsuario extends Model
{
    protected $table = 'areas_usuarios';
    protected $fillable = ['user_id', 'area_id', 'subarea_id'];
    
    // Relación para saber quién es el usuario
    public function user() {
        return $this->belongsTo(User::class,'user_id','id');
    }

    // Relación para saber el nombre del Área
    public function area() {
        return $this->belongsTo(Area::class,'area_id','id');
    }

    // Relación para saber el nombre de la Subárea
    public function subarea() {
        return $this->belongsTo(Subarea::class,'subarea_id','id');
    }
}
