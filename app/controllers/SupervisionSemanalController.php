<?php

namespace App\Controllers;

use App\Models\SupervisionSemanal;
use App\Models\Vehiculo;
use Carbon\Carbon;

class SupervisionSemanalController extends Controller
{
    /**
     * Muestra la matriz de supervisión semanal
     */
    public function index()
    {
        // 1. Configuración de Fechas
        $mes = request()->get('mes', Carbon::now()->month);
        $año = request()->get('año', Carbon::now()->year);

        Carbon::setLocale('es');

        $fechaInicioMes = Carbon::create($año, $mes, 1)->startOfDay();
        $fechaFinMes = $fechaInicioMes->copy()->endOfMonth();

        // 2. Construcción de Semanas Laborales (Lunes a Sábado)
        // Logica portada exactamente de tu Laravel Controller
        $inicioSemana = $fechaInicioMes->copy()->startOfWeek(Carbon::MONDAY);
        $semanasDelMes = [];

        while ($inicioSemana <= $fechaFinMes) {
            $finSemana = $inicioSemana->copy()->addDays(5)->endOfDay(); // Sábado

            // Recorte visual para que no muestre fechas del mes siguiente en la cabecera
            // aunque la consulta lógica sí debe buscar en la semana completa
            if ($finSemana->month != $mes) {
                $finSemana = $fechaFinMes->copy()->endOfDay();
            }

            $semanasDelMes[] = [
                'inicio' => $inicioSemana->copy(),
                'fin' => $finSemana->copy(),
                'inicio_real' => $inicioSemana->copy(), // Guardamos el inicio real para la query
                'fin_real' => $inicioSemana->copy()->addDays(5)->endOfDay() // Guardamos fin real (Sábado)
            ];

            // Avanzar al siguiente lunes
            $inicioSemana->addWeek();
        }

        // Definir rango total para la consulta Eager Loading
        // Usamos la primera y última semana calculada
        $fechaInicioConsulta = $semanasDelMes[0]['inicio_real'];
        $fechaFinConsulta = $semanasDelMes[count($semanasDelMes) - 1]['fin_real'];

        $nombreMes = $fechaInicioMes->translatedFormat('F Y');
        $hoy = Carbon::now();

        // 3. Query Vehículos
        $queryVehiculos = Vehiculo::query()
            ->select('id', 'no_economico', 'agencia')
            ->orderBy('agencia')
            ->orderBy('no_economico');

        $agenciaFilter = request()->get('agencia');
        if ($agenciaFilter) {
            $queryVehiculos->where('agencia', $agenciaFilter);
        }

        // 4. Eager Loading (Carga optimizada)
        $queryVehiculos->with(['supervisioSemanal' => function ($q) use ($fechaInicioConsulta, $fechaFinConsulta) {
            $q->whereBetween('created_at', [$fechaInicioConsulta, $fechaFinConsulta])
              ->select('id', 'vehiculo_id', 'created_at');
        }]);

        $vehiculos = $queryVehiculos->get();

        // 5. Procesamiento de Datos (Matriz)
        $vehiculosProcesados = $vehiculos->map(function ($vehiculo) use ($semanasDelMes, $hoy) {
            $statusPorSemana = [];
            $incumplimientos = 0;

            foreach ($semanasDelMes as $index => $semana) {
                // Verificamos si existe supervisión en el rango real de esa semana
                $tieneSupervision = $vehiculo->supervisioSemanal->contains(function ($sup) use ($semana) {
                    return $sup->created_at->between($semana['inicio_real'], $semana['fin_real']);
                });

                if ($tieneSupervision) {
                    $status = 'cumplido';
                } elseif ($semana['fin_real']->isFuture()) {
                    $status = 'futuro';
                } else {
                    $status = 'no_cumplido';
                    $incumplimientos++;
                }

                // Index + 1 para que coincida con "Semana 1", "Semana 2", etc.
                $statusPorSemana[$index] = $status;
            }

            // Inyectamos atributos temporales
            $vehiculo->setAttribute('status_semanas', $statusPorSemana);
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

        // 7. Datos para filtros
        $agencias = Vehiculo::distinct()->orderBy('agencia')->pluck('agencia');

        // 8. Renderizar
        render('supervision_semanal.index', [
            'vehiculos' => $vehiculosProcesados,
            'semanasDelMes' => $semanasDelMes,
            'nombreMes' => ucfirst($nombreMes),
            'agencias' => $agencias,
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
        // 1. Obtener datos y archivos
        // En Leaf usamos request()->post() para datos y request()->files() para archivos
        $data = request()->body();
        $files = request()->files();

        // 2. Validación Manual (Lo básico)
        if (empty($data['vehiculo_id']) || empty($data['no_eco'])) {
            return response()->json(['status' => 'error', 'message' => 'Faltan datos del vehículo.'], 400);
        }

        // Validar que las fotos obligatorias vengan
        $requiredPhotos = ['foto_del', 'foto_tra', 'foto_lado_izq', 'foto_lado_der'];
        foreach ($requiredPhotos as $photo) {
            if (!isset($files[$photo]) || $files[$photo]['error'] !== UPLOAD_ERR_OK) {
                return response()->json(['status' => 'error', 'message' => "La imagen $photo es obligatoria."], 400);
            }
        }

        try {
            // 3. Preparar array para guardar
            $saveData = [
                'vehiculo_id' => $data['vehiculo_id'],
                'no_eco' => $data['no_eco'],
                'resumen_est' => $data['resumen_est'] ?? null,
                // 'user_id' => auth()->id(), // Descomentar si ya tienes auth implementado
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];

            // 4. Procesar Imágenes
            // Mapeamos el nombre del input con el sufijo para la carpeta/nombre
            $imageMap = [
                'foto_del' => 'del',
                'foto_tra' => 'tra',
                'foto_lado_izq' => 'izq',
                'foto_lado_der' => 'der',
                'foto_poliza' => 'poliza',
                'foto_tar_circ' => 'tar_circ',
                'foto_kit' => 'kit',
                'foto_atent' => 'atent',
                'foto_llanta_ref' => 'llanta_ref'
            ];

            foreach ($imageMap as $inputName => $suffix) {
                if (isset($files[$inputName]) && $files[$inputName]['error'] === UPLOAD_ERR_OK) {
                    $path = $this->storeImage($files[$inputName], $data['no_eco'], $suffix);
                    if ($path) {
                        $saveData[$inputName] = $path;
                    }
                }
            }

            // 5. Guardar en BD
            SupervisionSemanal::create($saveData);

            return response()->json([
                'status' => 'success', // Tu JS espera esto para el SweetAlert
                'message' => 'Supervisión semanal guardada correctamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Función auxiliar para guardar imagen con PHP nativo
     */
    private function storeImage($fileArray, $no_eco, $descripcion)
    {
        // Definir ruta: public/fotos_supervision_semanal/NUM_ECO/FECHA/
        $dateFolder = date('Y-m-d');
        // __DIR__ es app/controllers, subimos a la raiz y entramos a public
        $basePath = dirname(__DIR__, 2) . '/public'; 
        $relativePath = "/fotos_supervision_semanal/{$no_eco}/{$dateFolder}";
        $fullPath = $basePath . $relativePath;

        // Crear directorio si no existe
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        // Obtener extensión
        $extension = pathinfo($fileArray['name'], PATHINFO_EXTENSION);
        $filename = "{$descripcion}.{$extension}";
        $targetFile = $fullPath . '/' . $filename;

        // Mover el archivo temporal al destino
        if (move_uploaded_file($fileArray['tmp_name'], $targetFile)) {
            // Retornar la ruta relativa para la BD (ej: /fotos_supervision_semanal/...)
            return $relativePath . '/' . $filename;
        }

        return null;
    }
}