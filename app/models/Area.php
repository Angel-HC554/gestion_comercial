<?php

namespace App\Models;
use App\Models\Vehiculo;

class Area extends Model
{
    protected $table = 'areas';
    protected $fillable = [
        'nombre',
    ];

    protected static function boot()
    {
        parent::boot();

        // 1. EVENTO AL ACTUALIZAR: Si cambia el nombre del proceso (Área)
        static::updating(function ($area) {
            // Capturamos el string original almacenado antes del cambio
            $nombreViejo = $area->getOriginal('nombre');
            // Forzamos que el nuevo valor vaya limpio y en mayúsculas
            $nombreNuevo = strtoupper(trim($area->nombre));

            if ($nombreViejo !== $nombreNuevo) {
                // Sincronización automática por software en la tabla vehículos
                Vehiculo::where('departamento', $nombreViejo)
                    ->update(['departamento' => $nombreNuevo]);
            }
        });

        // 2. EVENTO AL ELIMINAR: Si se borra el proceso
        static::deleting(function ($area) {
            // Evitamos que los vehículos asociados queden huérfanos o invisibles
            Vehiculo::where('departamento', $area->nombre)
                ->update(['departamento' => 'SIN PROCESO ASIGNADO']);
        });
    }

    public function subareas()
    {
        return $this->hasMany(Subarea::class, 'area_id', 'id');
    }
}
