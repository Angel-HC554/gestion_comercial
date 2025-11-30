<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model as EloquentModel;
use Carbon\Carbon;

class Vehiculo extends Model
{
    // Si tu tabla en BD se llama diferente (ej: 'vehiculos'), descomenta esto:
    // protected $table = 'vehiculos';

    protected $fillable = [
        'id', // Ojo: usualmente el ID es auto-incremental y no se pone en fillable, pero lo dejo si lo necesitas manipular.
        'agencia',
        'no_economico',
        'placas',
        'tipo_vehiculo',
        'marca',
        'modelo',
        'año',
        'estado',
        'propiedad',
        'proceso',
        'alias',
        'rpe_creamod',
    ];

    // --- RELACIONES ---

    public function supervisioDiaria()
    {
        return $this->hasMany(SupervisionDiaria::class);
    }
    
    public function supervisioSemanal()
    {
        return $this->hasMany(SupervisionSemanal::class);
    }

    /**
     * Obtiene el ÚLTIMO registro de supervisión diaria (el KM más actual).
     */
    public function latestSupervision()
    {
        return $this->hasOne(SupervisionDiaria::class)
                    ->latest('fecha')
                    ->latest('hora_fin');
    }

    /**
     * Obtiene la ÚLTIMA orden de mantenimiento/servicio (el KM base).
     */
    public function latestMantenimiento()
    {
        return $this->hasOneThrough(
            VehiculoSalidaTaller::class,
            OrdenVehiculo::class,
            'noeconomico',       // Foreign key en OrdenVehiculo
            'orden_vehiculo_id', // Foreign key en VehiculoSalidaTaller
            'no_economico',      // Local key en Vehiculo
            'id'                 // Local key en OrdenVehiculo
        )
        ->select('vehiculo_salidas_taller.*')
        ->where('vehiculo_salidas_taller.servicio', true) // Asegúrate que tu gestor de BD soporte booleanos o usa 1
        ->latest('kilometraje');
    }

    // --- SCOPES (Filtros de consulta) ---

    public function scopeConSupervisionDiariaHoy($query)
    {
        return $query->whereHas('supervisioDiaria', function ($q) {
            $q->whereDate('fecha', Carbon::today());
        });
    }

    public function scopeConSupervisionSemanalEstaSemana($query)
    {
        return $query->whereHas('supervisioSemanal', function ($q) {
            $q->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        });
    }

    // --- MÉTODOS DE AYUDA ---

    public function ultimoKilometraje()
    {
        // Reutilizamos la relación ya definida para consistencia
        $ultimaSupervision = $this->latestSupervision;

        return $ultimaSupervision ? [
            'kilometraje' => $ultimaSupervision->kilometraje,
            'fecha' => $ultimaSupervision->fecha,
            'hora_fin' => $ultimaSupervision->hora_fin
        ] : [
            'kilometraje' => 0,
            'fecha' => null,
            'hora_fin' => null
        ];
    }

    public function tieneSupervisionHoy()
    {
        return $this->supervisioDiaria()
            ->whereDate('fecha', Carbon::today())
            ->exists();
    }

    public function tieneSupervisionSemanal()
    {
        return $this->supervisioSemanal()
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->exists();
    }

    // --- ACCESORS (Atributos Calculados) ---

    // Se accede como: $vehiculo->estado_mantenimiento
    public function getEstadoMantenimientoAttribute()
    {
        $intervalo = 10000;
        $ventanaRoja = 1000;
        $ventanaAmarilla = 2000;
        $margenServicio = 500;

        // Eager loading friendly: usa las relaciones cargadas si existen
        $supervision = $this->getRelationValue('latestSupervision') ?? $this->latestSupervision()->first();
        $mantenimiento = $this->getRelationValue('latestMantenimiento') ?? $this->latestMantenimiento()->first();

        // Validación inicial
        if (!$supervision || $supervision->kilometraje === null) {
            return 'gris';
        }

        $kmActual = $supervision->kilometraje;
        $kmUltimoMantenimiento = $mantenimiento?->kilometraje ?? 0;

        // 1. Servicio reciente
        if ($mantenimiento && abs($kmActual - $kmUltimoMantenimiento) <= $margenServicio) {
            return 'verde';
        }

        // 2. Vencido (solo si HAY mantenimiento previo registrado)
        if ($mantenimiento && ($kmActual - $kmUltimoMantenimiento) > $intervalo) {
            return 'rojo_pasado';
        }

        // 3. Cálculo de Próximo mantenimiento
        if ($mantenimiento) {
            $proximoMantenimiento = $kmUltimoMantenimiento + $intervalo;
        } else {
            // Si nunca ha tenido mtto, calculamos basado en el intervalo puro
            $proximoMantenimiento = ceil($kmActual / $intervalo) * $intervalo;
            if ($proximoMantenimiento == 0) $proximoMantenimiento = $intervalo;
        }

        $kmFaltantes = $proximoMantenimiento - $kmActual;

        if ($kmFaltantes <= $ventanaRoja) {
            return 'rojo';
        }
        if ($kmFaltantes <= $ventanaAmarilla) {
            return 'amarillo';
        }

        return 'verde';
    }
}