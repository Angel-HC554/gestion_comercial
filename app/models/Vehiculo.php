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
        ->where('vehiculo_salidas_taller.servicio', true)
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
            $q->whereBetween('fecha_captura', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
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

    /**
     * Retorna la configuración de mantenimiento según la tabla del cliente.
     * Prioridad: Modelo específico > Marca > Default
     */
    private function getReglasMantenimiento()
    {
        // Normalizamos a mayúsculas para evitar errores de "Nissan" vs "NISSAN"
        $marca = strtoupper($this->marca);
        $modelo = strtoupper($this->modelo);

        // 1. Reglas por MODELO (Casos específicos de la imagen)
        // LOGAN: 10,000 KM / 12 MESES
        if (str_contains($modelo, 'LOGAN')) {
            return ['km' => 10000, 'meses' => 12];
        }

        // AVEO: 12,000 KM / 12 MESES
        if (str_contains($modelo, 'AVEO')) {
            return ['km' => 12000, 'meses' => 12];
        }

        // 2. Reglas por MARCA (Generales)
        
        // CHEVROLET (Silverado y otros no Aveo): La imagen dice 6,000 / 12,000 / 24,000
        // Esto significa que el intervalo base es cada 6,000 KM.
        if (str_contains($marca, 'CHEVROLET') || str_contains($marca, 'CHEVY')) {
            return ['km' => 6000, 'meses' => 12];
        }

        // NISSAN: 10,000 KM / 6 MESES
        if (str_contains($marca, 'NISSAN')) {
            return ['km' => 10000, 'meses' => 6];
        }

        // RAM: 10,000 KM / 12 MESES
        if (str_contains($marca, 'RAM') || str_contains($marca, 'DODGE')) {
            return ['km' => 10000, 'meses' => 12];
        }

        // MG: 10,000 KM / 6 MESES
        if (str_contains($marca, 'MG')) {
            return ['km' => 10000, 'meses' => 6];
        }

        // 3. Default (Para cualquier otro carro no listado, ej: Ford, Toyota)
        // Usamos el estándar más común que tenías antes
        return ['km' => 10000, 'meses' => 6]; 
    }

    // Se accede como: $vehiculo->info_mantenimiento
    public function getInfoMantenimientoAttribute()
    {
        // --- CONFIGURACIÓN ---
        $reglas = $this->getReglasMantenimiento();
        $intervaloKm = $reglas['km'];
        $kmVentanaRoja = 1000;
        $kmVentanaAmarilla = 2000;
        
        $mesesIntervalo = $reglas['meses'];
        $diasVentanaRoja = 7;
        $diasVentanaAmarilla = 21;
        $margenServicio = 50; // Margen de tolerancia post-servicio

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
            'km_proximo_servicio' => $intervaloKm,
            'km_faltantes' => 0,
            'fecha_ultimo_servicio' => null,
            'fecha_proximo_servicio' => null,
            'dias_restantes' => null,
            'tiene_info' => false,
            'intervalo_de_km' => $intervaloKm,
            'intervalo_de_meses' => $mesesIntervalo
        ];

        if (!$supervision || $supervision->kilometraje === null) {
            return $data; // Retorna vacío si no hay supervisión
        }

        $kmSupervision = $supervision->kilometraje ?? 0;
        $kmUltimoMantenimiento = $mantenimiento?->kilometraje ?? 0;
        $data['km_actual'] = max($kmSupervision, $kmUltimoMantenimiento);

        $data['km_ultimo_mantenimiento'] = $kmUltimoMantenimiento;
        // Validamos que exista al menos algún dato para mostrar info
        if ($data['km_actual'] === 0) {
             return $data; 
        }
        $data['tiene_info'] = true;
        
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

    public function getFotoUrlAttribute()
    {
        // 1. Definir el catálogo
        $modelos_fotos = [
            'aveo'      => 'aveo.png',
            'silverado' => 'silverado.jpg',
            'f-150'     => 'f-150.png',
            'frontier'  => 'frontier.jpg',
            'logan'     => 'logan.jpg',
            'mg'        => 'mg5.webp',
            'np300'     => 'np300.webp',
            'ram'       => 'ram.jpg',
            's10'       => 'S10.jpg'
        ];

        // 2. Normalizar a minúsculas (PHP Nativo)
        $modelo = strtolower($this->modelo ?? '');
        $marca  = strtolower($this->marca ?? '');
        
        $fotoKey = null;

        // 3. Lógica de coincidencia (Usando str_contains nativo de PHP 8)
        // Nota: Si usas PHP 7, cambia str_contains(A, B) por: strpos(A, B) !== false
        foreach ($modelos_fotos as $key => $filename) {
            // Si el modelo (ej: "ford f-150") contiene la clave (ej: "f-150")
            if ($key && str_contains($modelo, $key)) {
                $fotoKey = $key;
                break;
            }
        }

        if (!$fotoKey) {
        if (str_contains($marca, 'chevrolet')) {
            $fotoKey = 'silverado';
        } elseif (str_contains($marca, 'mg')) {
            $fotoKey = 'mg';
        } elseif (str_contains($marca, 'nissan')) {
            $fotoKey = 'np300';
        } elseif (str_contains($marca, 'renault')) {
            $fotoKey = 'logan';
        }   
        }   

        // 5. Retornar URL
        $archivo = $fotoKey ? ($modelos_fotos[$fotoKey] ?? null) : null;
        
        return $archivo 
            ? "/assets/img/vehiculos_default_fotos/{$archivo}" 
            : "/assets/img/vehiculos_default_fotos/default.jpg";
    }

    public function getEstadoMantenimientoAttribute()
    {
        $info = $this->info_mantenimiento;
        return $info['estatus_general'] ?? 'gris';
    }
}