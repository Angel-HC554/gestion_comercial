<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

use App\Models\VehiculoSalidaTaller;

class OrdenVehiculo extends Model
{
    protected $table = 'orden_vehiculos';

    protected $fillable = [
        'area',
        'zona',
        'departamento',
        'noeconomico',
        'marca',
        'placas',
        'taller',
        'kilometraje',
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
        // Opciones de checkbox
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
        // Observaciones y fecha firma
        'observacion',
        'fechafirm',
        //firmas y rpe
        'areausuaria',
        'rpeusuaria',
        'autoriza',
        'rpejefedpt',
        // Responsable de PV
        'resppv', // Nombre del responsable de PV
        'rperesppv', // R.P.E del responsable de PV
        'orden_500',
        'requiere_servicio',
        'status',
    ];

    /**
     * Get all of the archivos for the OrdenVehiculo
     *
     */
    public function archivos()
    {
        return $this->hasMany(OrdenArchivo::class, 'orden_vehiculo_id');
    }

    public function historial()
    {
        return $this->hasMany(HistorialOrden::class, 'orden_vehiculo_id');
    }

    public function salidasTaller()
    {
        return $this->hasMany(VehiculoSalidaTaller::class, 'orden_vehiculo_id');
    }
}
