<?php

namespace App\Controllers;

use App\Models\SupervisionDiaria;
use App\Models\Vehiculo;
use Carbon\Carbon;

class SupervisionDiariaController extends Controller
{
    private function getDatosFiltros($departamentoFilter)
    {
        $departamentos = Vehiculo::whereNotNull('departamento')
            ->where('departamento', '!=', '')
            ->distinct()
            ->orderBy('departamento')
            ->pluck('departamento');

        $queryUbicaciones = Vehiculo::whereNotNull('ubicacion')->where('ubicacion', '!=', '');
        if ($departamentoFilter) {
            $queryUbicaciones->where('departamento', $departamentoFilter);
        }
        $ubicaciones = $queryUbicaciones->distinct()->orderBy('ubicacion')->pluck('ubicacion');

        return [$departamentos, $ubicaciones];
    }

    public function index()
    {
        // 1. Configuración segura de fechas y filtros
        $mesInput = request()->get('mes');
        $añoInput = request()->get('año');
        $departamentoFilter = request()->get('departamento');
        $ubicacionFilter = request()->get('ubicacion'); // Añadido para que viaje entre vistas

        $mes = ($mesInput && is_numeric($mesInput)) ? (int)$mesInput : Carbon::now()->month;
        $año = ($añoInput && is_numeric($añoInput)) ? (int)$añoInput : Carbon::now()->year;

        Carbon::setLocale('es');
        $fechaInicioMes = Carbon::create($año, $mes, 1)->startOfMonth();
        $fechaFinMes = $fechaInicioMes->copy()->endOfMonth();
        $nombreMes = $fechaInicioMes->translatedFormat('F Y');

        // 2. Consulta de Vehículos
        $queryVehiculos = Vehiculo::with(['supervisioDiaria' => function ($q) use ($fechaInicioMes, $fechaFinMes){
                $q->whereBetween('fecha', [$fechaInicioMes->format('Y-m-d'), $fechaFinMes->format('Y-m-d')]);
            }])
            ->select('id', 'no_economico', 'departamento', 'ubicacion', 'en_taller');
        
        if ($departamentoFilter) $queryVehiculos->where('departamento', $departamentoFilter);
        if ($ubicacionFilter) $queryVehiculos->where('ubicacion', $ubicacionFilter);

        $todosLosVehiculos = $queryVehiculos->get();

        // 3. Procesamiento y Agrupación
        $tablaResumen = [];
        $columnaAgrupacion = $departamentoFilter ? 'ubicacion' : 'departamento';
        $tipoAgrupacion = $departamentoFilter ? 'Ubicación' : 'Proceso';

        $grupos = $todosLosVehiculos->groupBy($columnaAgrupacion);

        foreach ($grupos as $nombreDato => $vehiculosGrupo) {
            $nombre = $nombreDato ?: 'SIN ' . mb_strtoupper($tipoAgrupacion);

            $totalVehiculos = $vehiculosGrupo->count();
            $vehiculosEnTallerColeccion = $vehiculosGrupo->where('en_taller', 1);
            $totalEnTaller = $vehiculosEnTallerColeccion->count();
            $listaEnTaller = $vehiculosEnTallerColeccion->pluck('no_economico')->toArray();
            
            $totalSupervisionesHechas = 0;
            $totalDiasFaltantes = 0;
            $totalDiasEsperados = 0;

            foreach ($vehiculosGrupo as $vehiculo) {
                if ($vehiculo->en_taller) continue; 

                $iteradorDia = $fechaInicioMes->copy();
                while ($iteradorDia->lte($fechaFinMes)) {
                    if ($iteradorDia->isFuture()) {
                        $iteradorDia->addDay();
                        continue;
                    }

                    $totalDiasEsperados++; 

                    $cumplioDia = $vehiculo->supervisioDiaria->contains(function ($sup) use ($iteradorDia) {
                        return Carbon::parse($sup->fecha)->isSameDay($iteradorDia);
                    });

                    if ($cumplioDia) {
                        $totalSupervisionesHechas++;
                    } else {
                        $totalDiasFaltantes++;
                    }
                    $iteradorDia->addDay();
                }
            }

            $porcentaje = ($totalDiasEsperados > 0) ? round(($totalSupervisionesHechas / $totalDiasEsperados) * 100) : 0; 
            if ($totalDiasEsperados == 0 && $totalVehiculos > 0) $porcentaje = 0;

            $tablaResumen[] = [
                'nombre' => $nombre, 'total_vehiculos' => $totalVehiculos,
                'en_taller' => $totalEnTaller, 'pendientes' => $totalDiasFaltantes,
                'vehiculos_en_taller' => $listaEnTaller,  
                'cumplidos' => $totalSupervisionesHechas, 'porcentaje' => $porcentaje
            ];
        }

        // Ordenar alfabéticamente
        usort($tablaResumen, function($a, $b) { return strcmp($a['nombre'], $b['nombre']); });

        list($departamentos, $ubicaciones) = $this->getDatosFiltros($departamentoFilter);

        render('supervision_diaria.index', [
            'nombreMes' => ucfirst($nombreMes),
            'resumen' => $tablaResumen,
            'departamentos' => $departamentos,
            'ubicaciones' => $ubicaciones,
            'tipoAgrupacion' => $tipoAgrupacion,
            'filtrosActuales' => [
                'departamento' => $departamentoFilter, 'ubicacion' => $ubicacionFilter,
                'mes' => $mes, 'año' => $año
            ]
        ]);
    }

    public function detallado()
    {
        $mesInput = request()->get('mes');
        $añoInput = request()->get('año');
        $departamentoFilter = request()->get('departamento');
        $ubicacionFilter = request()->get('ubicacion');
        $cumplimientoFilter = request()->get('cumplimiento');

        $mes = ($mesInput && is_numeric($mesInput)) ? (int)$mesInput : Carbon::now()->month;
        $año = ($añoInput && is_numeric($añoInput)) ? (int)$añoInput : Carbon::now()->year;

        Carbon::setLocale('es');
        $fechaInicio = Carbon::createFromDate($año, $mes, 1)->startOfMonth();
        $fechaFin = $fechaInicio->copy()->endOfMonth();
        $nombreMes = $fechaInicio->translatedFormat('F Y');

        $diasDelMes = [];
        $tempDate = $fechaInicio->copy();
        while ($tempDate->lte($fechaFin)) {
            $diasDelMes[] = $tempDate->format('d');
            $tempDate->addDay();
        }

        $queryVehiculos = Vehiculo::query()
            ->select('id', 'no_economico', 'departamento', 'ubicacion', 'en_taller')
            ->orderBy('departamento')
            ->orderBy('no_economico');

        if ($departamentoFilter) $queryVehiculos->where('departamento', $departamentoFilter);
        if ($ubicacionFilter) $queryVehiculos->where('ubicacion', $ubicacionFilter);

        $queryVehiculos->with(['supervisioDiaria' => function ($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha', [$fechaInicio->format('Y-m-d'), $fechaFin->format('Y-m-d')]);
        }]);

        $vehiculos = $queryVehiculos->get();
        $hoy = Carbon::now()->startOfDay();

        $vehiculosProcesados = $vehiculos->map(function ($vehiculo) use ($diasDelMes, $mes, $año, $hoy) {
            $statusDias = [];
            $incumplimientos = 0;

            foreach ($diasDelMes as $dia) {
                $fechaIteracion = Carbon::createFromDate($año, $mes, $dia)->startOfDay();

                $tieneSupervision = $vehiculo->supervisioDiaria->contains(function ($sup) use ($fechaIteracion) {
                    return Carbon::parse($sup->fecha)->isSameDay($fechaIteracion);
                });

                if ($tieneSupervision) {
                    $status = 'cumplido';
                } elseif ($fechaIteracion->isFuture()) {
                    $status = 'futuro';
                } else {
                    if ($vehiculo->en_taller) {
                         $status = 'taller'; 
                    } else {
                         $status = 'no_cumplido';
                         $incumplimientos++;
                    }
                }
                $statusDias[$dia] = $status;
            }

            $vehiculo->setAttribute('status_dias', $statusDias);
            $vehiculo->setAttribute('total_incumplimientos', $incumplimientos);
            return $vehiculo;
        });

        if ($cumplimientoFilter == 'no_cumple') {
            $vehiculosProcesados = $vehiculosProcesados->filter(function ($vehiculo) {
                return $vehiculo->total_incumplimientos > 0;
            });
        }

        list($departamentos, $ubicaciones) = $this->getDatosFiltros($departamentoFilter);

        render('supervision_diaria.detallado', [
            'vehiculos' => $vehiculosProcesados, 'diasDelMes' => $diasDelMes,
            'nombreMes' => ucfirst($nombreMes), 'departamentos' => $departamentos,
            'ubicaciones' => $ubicaciones,
            'filtrosActuales' => [
                'departamento' => $departamentoFilter, 'ubicacion' => $ubicacionFilter,
                'cumplimiento' => $cumplimientoFilter, 'mes' => $mes, 'año' => $año
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

    public function historial($id)
{
    // 1. Buscamos el vehículo
    $vehiculo = Vehiculo::find($id);

    if (!$vehiculo) {
        return 'Vehículo no encontrado';
    }

    // 2. Obtenemos las últimas 50 supervisiones para mantenerlo rápido
    // (Luego podemos agregar un botón "Cargar más" si lo necesitas)
    $supervisiones = \App\Models\SupervisionDiaria::where('vehiculo_id', $id)
        ->orderBy('fecha', 'desc')
        ->orderBy('hora_inicio', 'desc')
        ->take(50) 
        ->get();

    // 3. Retornamos SOLO la vista parcial (sin layouts, ni headers)
    // Asegúrate de crear la carpeta components si no existe
    render('supervision_diaria.partials.timeline-supervisiones', [
        'vehiculo' => $vehiculo,
        'supervisiones' => $supervisiones
    ]);
}
}