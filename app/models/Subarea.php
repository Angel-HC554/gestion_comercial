<?php

namespace App\Models;

class Subarea extends Model
{
    protected $table = 'subareas';
    protected $fillable = [
        'area_id',
        'nombre',
    ];

    protected static function boot()
    {
        parent::boot();

        // 1. EVENTO AL ACTUALIZAR: Si cambia el nombre de la ubicación (Subárea)
        static::updating(function ($subarea) {
            // Capturamos el string original almacenado antes del cambio
            $nombreViejo = $subarea->getOriginal('nombre');
            // Forzamos mayúsculas limpias
            $nombreNuevo = strtoupper(trim($subarea->nombre));

            if ($nombreViejo !== $nombreNuevo) {
                // Sincronización automática por software en la tabla vehículos
                Vehiculo::where('ubicacion', $nombreViejo)
                    ->update(['ubicacion' => $nombreNuevo]);
            }
        });

        // 2. EVENTO AL ELIMINAR: Si se borra la ubicación
        static::deleting(function ($subarea) {
            // Evitamos que los vehículos asociados queden huérfanos o invisibles
            Vehiculo::where('ubicacion', $subarea->nombre)
                ->update(['ubicacion' => 'SIN UBICACIÓN ASIGNADA']);
        });
    }
    
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id', 'id');
    }
}
