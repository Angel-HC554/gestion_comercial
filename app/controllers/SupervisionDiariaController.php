<?php

namespace App\Controllers;

use App\Models\SupervisionDiaria;
use App\Models\Vehiculo;
use Carbon\Carbon;

class SupervisionDiariaController extends Controller
{
    /**
     * Muestra la matriz de supervisión diaria (Calendario del mes)
     */
    public function index()
    {
        // 1. Configuración de Fechas
        // En Leaf usamos request()->get() para parámetros GET de la URL
        $mes = request()->get('mes', Carbon::now()->month);
        $año = request()->get('año', Carbon::now()->year);

        // Establecer locale al español para que los nombres de meses salgan en español
        Carbon::setLocale('es');

        $fechaInicio = Carbon::createFromDate($año, $mes, 1)->startOfMonth();
        $fechaFin = $fechaInicio->copy()->endOfMonth();
        $nombreMes = $fechaInicio->translatedFormat('F Y');

        // 2. Generar array de días (1, 2, 3... 30/31)
        $diasDelMes = [];
        $tempDate = $fechaInicio->copy();
        
        while ($tempDate->lte($fechaFin)) {
            $diasDelMes[] = $tempDate->format('d');
            $tempDate->addDay();
        }

        // 3. Query Base de Vehículos
        $queryVehiculos = Vehiculo::query()
            ->select('id', 'no_economico', 'agencia')
            ->orderBy('agencia')
            ->orderBy('no_economico');

        // Filtro por Agencia
        $agenciaFilter = request()->get('agencia');
        if ($agenciaFilter) {
            $queryVehiculos->where('agencia', $agenciaFilter);
        }

        // 4. Eager Loading (Carga Ansiosa) Eficiente
        // Cargamos SOLO las supervisiones que corresponden al mes seleccionado
        $queryVehiculos->with(['supervisioDiaria' => function ($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha', [
                $fechaInicio->format('Y-m-d'), 
                $fechaFin->format('Y-m-d')
            ]);
        }]);

        $vehiculos = $queryVehiculos->get();

        // 5. Procesamiento de la Matriz (Lógica de Negocio)
        // Hoy lo usamos para saber si un día vacío es "falta" o "futuro"
        $hoy = Carbon::now()->startOfDay();

        $vehiculosProcesados = $vehiculos->map(function ($vehiculo) use ($diasDelMes, $mes, $año, $hoy) {
            $statusDias = [];
            $incumplimientos = 0;

            foreach ($diasDelMes as $dia) {
                // Creamos objeto fecha para el día específico de la iteración
                $fechaIteracion = Carbon::createFromDate($año, $mes, $dia)->startOfDay();

                // Buscamos en la colección cargada (en memoria, no hace querys extra)
                // Asumimos que SupervisionDiaria tiene el cast 'fecha' => 'date' en el modelo
                $tieneSupervision = $vehiculo->supervisioDiaria->contains(function ($sup) use ($fechaIteracion) {
                    return $sup->fecha->isSameDay($fechaIteracion);
                });

                if ($tieneSupervision) {
                    $status = 'cumplido';
                } elseif ($fechaIteracion->isFuture()) {
                    $status = 'futuro';
                } else {
                    // Si es hoy o pasado y no tiene supervisión
                    $status = 'no_cumplido';
                    $incumplimientos++;
                }

                // Guardamos el status usando el número de día como clave
                $statusDias[$dia] = $status;
            }

            // Inyectamos propiedades temporales al objeto vehículo para la vista
            $vehiculo->setAttribute('status_dias', $statusDias);
            $vehiculo->setAttribute('total_incumplimientos', $incumplimientos);

            return $vehiculo;
        });

        // 6. Filtro de Cumplimiento (Post-Procesamiento)
        $cumplimientoFilter = request()->get('cumplimiento');
        if ($cumplimientoFilter == 'no_cumple') {
            $vehiculosProcesados = $vehiculosProcesados->filter(function ($vehiculo) {
                return $vehiculo->total_incumplimientos > 0;
            });
        }

        // 7. Datos para los selectores de filtros
        $agencias = Vehiculo::distinct()->orderBy('agencia')->pluck('agencia');

        // 8. Renderizar Vista
        // Asumo que crearás una carpeta 'supervision_diaria' dentro de views
        render('supervision_diaria.index', [
            'vehiculos' => $vehiculosProcesados,
            'diasDelMes' => $diasDelMes,
            'nombreMes' => ucfirst($nombreMes), // Primera letra mayúscula
            'agencias' => $agencias,
            // Pasamos los filtros actuales para mantenerlos seleccionados en el HTML
            'filtrosActuales' => [
                'agencia' => $agenciaFilter,
                'cumplimiento' => $cumplimientoFilter,
                'mes' => $mes,
                'año' => $año
            ]
        ]);
    }

    public function store()
    {
        $data = request()->body();
        $files = request()->files();
        // --- PASO DE LIMPIEZA ---
        // Eliminamos la coma (,) antes de cualquier validación o guardado
        if (isset($data['kilometraje'])) {
            $data['kilometraje'] = str_replace(',', '', $data['kilometraje']);
        }
        // ------------------------

        // 1. Validaciones Básicas
        $required = ['vehiculo_id', 'kilometraje', 'fecha', 'hora_inicio', 'hora_fin', 'nombre_auxiliar'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return response()->json(['status' => 'error', 'message' => "El campo $field es obligatorio."], 400);
            }
        }

        try {
            // 2. Validación de Kilometraje (Lógica de Negocio)
            // Buscamos el último registro de este vehículo
            $lastRecord = SupervisionDiaria::where('vehiculo_id', $data['vehiculo_id'])
                ->orderBy('fecha', 'desc')
                ->first();

            $nuevoKm = (int) $data['kilometraje'];
            
            if ($lastRecord && $nuevoKm < $lastRecord->kilometraje) {
                return response()->json([
                    'status' => 'error',
                    'message' => "El kilometraje no puede ser menor al último registrado (" . number_format($lastRecord->kilometraje) . ")."
                ], 400);
            }

            // 3. Manejo de Checkboxes (Radios)
            // En tu formulario HTML usas value="1" y "0". Leaf los recibe como strings "1" o "0".
            // Los pasamos directo, Eloquent hará el cast a boolean si está en el modelo,
            // pero nos aseguramos que existan en el array.
            $checkboxes = [
                'aceite', 'liq_fren', 'anti_con', 'agua', 'radiador', 'llantas',
                'llanta_r', 'tapon_gas', 'limp_cab', 'limp_ext', 'cinturon',
                'limpia_par', 'manijas_puer', 'espejo_int', 'espejo_lat_i',
                'espejo_lat_d', 'gato', 'llave_cruz', 'extintor', 'direccionales',
                'luces', 'intermit', 'golpes'
            ];

            foreach ($checkboxes as $chk) {
                if (!isset($data[$chk])) {
                    $data[$chk] = 0; // Valor por defecto si no se marcó nada
                }
            }

            // 4. Procesar Archivo (Escaneo PDF/Imagen)
            if (isset($files['escaneo_url']) && $files['escaneo_url']['error'] === UPLOAD_ERR_OK) {
                $basePath = dirname(__DIR__, 2) . '/public';
                $relativePath = '/escaneos_supervision_diaria';
                $fullPath = $basePath . $relativePath;
                
                if (!is_dir($fullPath)) mkdir($fullPath, 0755, true);

                $file = $files['escaneo_url'];
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'escaneo_' . $data['no_eco'] . '_' . date('Y-m-d_H-i') . '.' . $ext;
                
                if (move_uploaded_file($file['tmp_name'], $fullPath . '/' . $filename)) {
                    $data['escaneo_url'] = $relativePath . '/' . $filename;
                }
            }

            // 5. Timestamps
            $data['created_at'] = Carbon::now();
            $data['updated_at'] = Carbon::now();

            // 6. Guardar
            SupervisionDiaria::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Supervisión diaria registrada correctamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }
}