<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model as EloquentModel;
use Carbon\Carbon;

class Vehiculo extends Model
{
    // Si tu tabla en BD se llama diferente (ej: 'vehiculos'), descomenta esto:
    // protected $table = 'vehiculos';

    protected $fillable = [
        'id',
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
        'ordenes_pendientes',
        'en_taller',
        'finalizado',
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
        $inicio = Carbon::now()->startOfWeek(Carbon::MONDAY); // lunes
        $fin    = $inicio->copy()->addDays(5)->endOfDay(); // Sabado
        return $this->supervisioSemanal()
            ->whereBetween('fecha_captura', [$inicio, $fin])
            ->exists();
    }

    public function obtenerIdSupervisionSemanal()
    {
        $inicio = Carbon::now()->startOfWeek(Carbon::MONDAY); // lunes
        $fin    = $inicio->copy()->addDays(5)->endOfDay(); // Sabado
        
        $supervision = $this->supervisioSemanal()
            ->whereBetween('fecha_captura', [$inicio, $fin])
            ->first();
            
        return $supervision?->id;
    }

    // --- ACCESORS (Atributos Calculados) ---

    // Se accede como: $vehiculo->estado_mantenimiento
    public function getEstadoMantenimientoAttribute()
    {
        //kilometraje
        $intervaloKm = 10000;
        $kmventanaRoja = 1000;
        $kmventanaAmarilla = 2000;
        //Tiempo
        $mesesIntervalo = 3;
        $diasVentanaRoja = 7;
        $diasVentanaAmarilla = 21;
        $margenServicio = 500;

        // Eager loading: usa las relaciones cargadas si existen
        $supervision = $this->getRelationValue('latestSupervision') ?? $this->latestSupervision()->first();
        $mantenimiento = $this->getRelationValue('latestMantenimiento') ?? $this->latestMantenimiento()->first();

        // Validación inicial, si no hay supervisión, retorna gris
        if (!$supervision || $supervision->kilometraje === null) {
            return 'gris';
        }

        $kmActual = $supervision->kilometraje;
        $kmUltimoMantenimiento = $mantenimiento?->kilometraje ?? 0;

        // 1. Servicio reciente: Si hay mantenimiento y la diferencia es mínima, es verde automáticamente
        if ($mantenimiento && abs($kmActual - $kmUltimoMantenimiento) <= $margenServicio) {
            return 'verde';
        }

        //evaluacion de kilometraje, calculamos cuando toca el siguiente
        if ($mantenimiento) {
            $proximoMantenimiento = $kmUltimoMantenimiento + $intervaloKm;
        } else {
            // Si nunca ha tenido mtto, calculamos basado en el intervalo puro
            $proximoMantenimiento = ceil($kmActual / $intervaloKm) * $intervaloKm;
            if ($proximoMantenimiento == 0) $proximoMantenimiento = $intervaloKm;
        }

        $kmFaltantes = $proximoMantenimiento - $kmActual;

        //Determinar color por km
        $colorKm = 'verde';
        if ($kmFaltantes < 0) {
            $colorKm = 'rojo_pasado';
        } elseif ($kmFaltantes <= $kmventanaRoja){
            $colorKm = 'rojo';
        } elseif ($kmFaltantes <= $kmventanaAmarilla){
            $colorKm = 'amarillo';
        }

        $colorTiempo = 'verde';

        if ($mantenimiento && $mantenimiento->fecha_terminacion) {
            $fechaUltimo = Carbon::parse($mantenimiento->fecha_terminacion);
            $fechaLimite = $fechaUltimo->copy()->addMonths($mesesIntervalo);
            $hoy = Carbon::today();

            // Dias faltantes (negativo si ya paso)
            $diasFaltantes = $hoy->diffInDays($fechaLimite, false);

            if ($diasFaltantes < 0) {
                $colorTiempo = 'rojo_pasado';
            } elseif ($diasFaltantes <= $diasVentanaRoja){
                $colorTiempo = 'rojo';
            } elseif ($diasFaltantes <= $diasVentanaAmarilla){
                $colorTiempo = 'amarillo';
            }
        }

        //Evaluacion final
        //Orden de gravedad por prioridad
        if ($colorKm === 'rojo_pasado' || $colorTiempo === 'rojo_pasado') {
            return 'rojo_pasado';
        }

        if ($colorKm === 'rojo' || $colorTiempo === 'rojo') {
            return 'rojo';
        }

        if ($colorKm === 'amarillo' || $colorTiempo === 'amarillo') {
            return 'amarillo';
        }
        return 'verde';
    }

    // Se accede como: $vehiculo->info_mantenimiento
    public function getInfoMantenimientoAttribute()
    {
        // --- CONFIGURACIÓN ---
        $intervaloKm = 10000;
        $kmVentanaRoja = 1000;
        $kmVentanaAmarilla = 2000;
        
        $mesesIntervalo = 3;
        $diasVentanaRoja = 7;
        $diasVentanaAmarilla = 21;
        $margenServicio = 500; // Margen de tolerancia post-servicio

        // --- CARGA DE DATOS ---
        // Usamos relaciones cacheadas o cargamos
        $supervision = $this->relationLoaded('latestSupervision') ? $this->latestSupervision : $this->latestSupervision()->first();
        $mantenimiento = $this->relationLoaded('latestMantenimiento') ? $this->latestMantenimiento : $this->latestMantenimiento()->first();

        // Valores por defecto
        $data = [
            'estatus_general' => 'gris', // Semáforo grande
            'estatus_km' => 'verde',     // Color texto km
            'estatus_tiempo' => 'verde', // Color texto tiempo
            'km_actual' => 0,
            'km_ultimo_mantenimiento' => 0,
            'km_proximo_servicio' => 10000,
            'km_faltantes' => 0,
            'fecha_ultimo_servicio' => null,
            'fecha_proximo_servicio' => null,
            'dias_restantes' => null,
            'tiene_info' => false
        ];

        if (!$supervision || $supervision->kilometraje === null) {
            return $data; // Retorna vacío si no hay supervisión
        }

        $data['tiene_info'] = true;
        $data['km_actual'] = $supervision->kilometraje;
        $kmUltimoMantenimiento = $mantenimiento?->kilometraje ?? 0;
        $data['km_ultimo_mantenimiento'] = $kmUltimoMantenimiento;
        
        if ($mantenimiento && $mantenimiento->fecha_terminacion) {
             $data['fecha_ultimo_servicio'] = $mantenimiento->fecha_terminacion;
        }

        // --- 1. CÁLCULOS KILOMETRAJE ---
        
        // Regla: Si acaba de salir del taller (diferencia mínima), reseteamos visualmente
        if ($mantenimiento && abs($data['km_actual'] - $kmUltimoMantenimiento) <= $margenServicio) {
            $data['estatus_general'] = 'verde';
            $data['km_proximo_servicio'] = $kmUltimoMantenimiento + $intervaloKm;
            $data['km_faltantes'] = $intervaloKm; // Mostramos tanque lleno
            // Aún así calculamos fechas abajo por si acaso
        } else {
             // Cálculo normal
            if ($mantenimiento) {
                $proximo = $kmUltimoMantenimiento + $intervaloKm;
            } else {
                $proximo = ceil($data['km_actual'] / $intervaloKm) * $intervaloKm;
                if ($proximo == 0) $proximo = $intervaloKm;
            }
            
            $data['km_proximo_servicio'] = $proximo;
            $data['km_faltantes'] = $proximo - $data['km_actual'];

            // Color KM
            if ($data['km_faltantes'] < 0) $data['estatus_km'] = 'rojo_pasado';
            elseif ($data['km_faltantes'] <= $kmVentanaRoja) $data['estatus_km'] = 'rojo';
            elseif ($data['km_faltantes'] <= $kmVentanaAmarilla) $data['estatus_km'] = 'amarillo';
        }

        // --- 2. CÁLCULOS TIEMPO ---
        if ($mantenimiento && $mantenimiento->fecha_terminacion) {
            $fechaUltimo = Carbon::parse($mantenimiento->fecha_terminacion);
            $proximaFecha = $fechaUltimo->copy()->addMonths($mesesIntervalo);
            $hoy = Carbon::today();

            $data['fecha_proximo_servicio'] = $proximaFecha; // Objeto Carbon
            $data['dias_restantes'] = $hoy->diffInDays($proximaFecha, false);

            // Color Tiempo
            if ($data['dias_restantes'] < 0) $data['estatus_tiempo'] = 'rojo_pasado';
            elseif ($data['dias_restantes'] <= $diasVentanaRoja) $data['estatus_tiempo'] = 'rojo';
            elseif ($data['dias_restantes'] <= $diasVentanaAmarilla) $data['estatus_tiempo'] = 'amarillo';
        }

        // --- 3. EVALUACIÓN FINAL (El peor gana) ---
        // Si ya definimos verde arriba por margen de servicio, respetarlo, si no:
        if ($data['estatus_general'] !== 'verde' || abs($data['km_actual'] - $kmUltimoMantenimiento) > $margenServicio) {
             if ($data['estatus_km'] === 'rojo_pasado' || $data['estatus_tiempo'] === 'rojo_pasado') {
                $data['estatus_general'] = 'rojo_pasado';
            } elseif ($data['estatus_km'] === 'rojo' || $data['estatus_tiempo'] === 'rojo') {
                $data['estatus_general'] = 'rojo';
            } elseif ($data['estatus_km'] === 'amarillo' || $data['estatus_tiempo'] === 'amarillo') {
                $data['estatus_general'] = 'amarillo';
            } else {
                $data['estatus_general'] = 'verde';
            }
        }

        return $data;
    }
}