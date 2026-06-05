<?php

namespace App\Controllers;

use App\Models\SupervisionSemanal;
use App\Models\Vehiculo;
use Carbon\Carbon;
use setasign\Fpdi\Tcpdf\Fpdi;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class SupervisionSemanalController extends Controller
{
    /**
     * Muestra la matriz de supervisión semanal
     */

    private function getDatosFiltros($departamentoFilter)
    {
        $departamentos = Vehiculo::whereNotNull('departamento')->where('departamento', '!=', '')->distinct()->orderBy('departamento')->pluck('departamento');
        
        $queryUbicaciones = Vehiculo::whereNotNull('ubicacion')->where('ubicacion', '!=', '');
        if ($departamentoFilter) {
            $queryUbicaciones->where('departamento', $departamentoFilter);
        }
        $ubicaciones = $queryUbicaciones->distinct()->orderBy('ubicacion')->pluck('ubicacion');

        return [$departamentos, $ubicaciones];
    }

    public function detallado()
    {
        // 1. Configuración de Fechas (se mantiene igual)
        $mesInput = request()->get('mes', Carbon::now()->month);
        $añoInput = request()->get('año', Carbon::now()->year);

        $mes = ($mesInput && is_numeric($mesInput)) ? (int)$mesInput : Carbon::now()->month;
        $año = ($añoInput && is_numeric($añoInput)) ? (int)$añoInput : Carbon::now()->year;

        Carbon::setLocale('es');

        $fechaInicioMes = Carbon::create($año, $mes, 1)->startOfDay();
        $fechaFinMes = $fechaInicioMes->copy()->endOfMonth();

        // 2. Construcción de Semanas Laborales (se mantiene igual)
        $inicioSemana = $fechaInicioMes->copy()->startOfWeek(Carbon::MONDAY);
        $semanasDelMes = [];

        while ($inicioSemana <= $fechaFinMes) {
            $finSemana = $inicioSemana->copy()->addDays(5)->endOfDay();
            if ($finSemana->gt($fechaFinMes)) {
                $finSemana = $fechaFinMes->copy()->endOfDay();
            }

            $semanasDelMes[] = [
                'inicio' => $inicioSemana->copy(),
                'fin' => $finSemana->copy(),
                'inicio_real' => $inicioSemana->copy(),
                'fin_real' => $inicioSemana->copy()->addDays(5)->endOfDay()
            ];
            $inicioSemana->addWeek();
        }

        $fechaInicioConsulta = $semanasDelMes[0]['inicio_real'];
        $fechaFinConsulta = $semanasDelMes[count($semanasDelMes) - 1]['fin_real'];
        $nombreMes = $fechaInicioMes->translatedFormat('F Y');
        $hoy = Carbon::now();

        // 3. Query Vehículos (ACTUALIZADO: Usamos 'departamento' y 'ubicacion' en lugar de 'agencia')
        $queryVehiculos = Vehiculo::query()
            ->where('estado', '!=', 'Fuera de circulacion')
            ->select('id', 'no_economico', 'departamento', 'ubicacion', 'en_taller')
            ->orderBy('departamento')
            ->orderBy('no_economico');

        // Filtro por departamento
        $departamentoFilter = request()->get('departamento');
        if ($departamentoFilter) {
            $queryVehiculos->where('departamento', $departamentoFilter);
        }

        $ubicacionFilter = request()->get('ubicacion');
        if ($ubicacionFilter) {
            $queryVehiculos->where('ubicacion', $ubicacionFilter);
        }

        // 4. Eager Loading (se mantiene igual)
        $queryVehiculos->with(['supervisioSemanal' => function ($q) use ($fechaInicioConsulta, $fechaFinConsulta) {
            $q->whereBetween('fecha_captura', [$fechaInicioConsulta, $fechaFinConsulta])
                ->select('id', 'vehiculo_id', 'fecha_captura')
                ->orderBy('fecha_captura', 'desc');
        }]);

        $vehiculos = $queryVehiculos->get();

        // 5. Procesamiento de Datos (se mantiene igual)
        $vehiculosProcesados = $vehiculos->map(function ($vehiculo) use ($semanasDelMes, $hoy) {
            $statusPorSemana = [];
            $incumplimientos = 0;

            foreach ($semanasDelMes as $index => $semana) {
                $supervision = $vehiculo->supervisioSemanal->first(function ($sup) use ($semana) {
                    return $sup->fecha_captura->between($semana['inicio_real'], $semana['fin_real']);
                });

                if ($supervision) {
                    $status = ['tipo' => 'cumplido', 'id' => $supervision->id, 'fecha' => $supervision->fecha_captura->format('d/m/Y')];
                } elseif ($semana['inicio_real']->isFuture()) {
                    $status = ['tipo' => 'futuro'];
                } else {
                    if ($vehiculo->en_taller) {
                        $status = ['tipo' => 'taller'];
                    } else{
                        $status = ['tipo' => 'no_cumplido'];
                        $incumplimientos++;
                    }
                }
                $statusPorSemana[$index] = $status;
            }

            $vehiculo->setAttribute('status_semanas', $statusPorSemana);
            $vehiculo->setAttribute('total_incumplimientos', $incumplimientos);
            return $vehiculo;
        });

        // 6. Filtro de Cumplimiento
        $cumplimientoFilter = request()->get('cumplimiento');
        if ($cumplimientoFilter == 'no_cumple') {
            $vehiculosProcesados = $vehiculosProcesados->filter(function ($vehiculo) {
                return $vehiculo->total_incumplimientos > 0;
            });
        }

        // 7. Datos para filtros
        list($departamentos, $ubicaciones) = $this->getDatosFiltros($departamentoFilter);
        // 8. Renderizar
        render('supervision_semanal.detallado', [
            'vehiculos' => $vehiculosProcesados,
            'semanasDelMes' => $semanasDelMes,
            'nombreMes' => ucfirst($nombreMes),
            'departamentos' => $departamentos,
            'ubicaciones' => $ubicaciones,
            'filtrosActuales' => [
                'departamento' => $departamentoFilter,
                'ubicacion' => $ubicacionFilter,
                'cumplimiento' => $cumplimientoFilter,
                'mes' => $mes,
                'año' => $año
            ]
        ]);
    }

    public function index()
{
    // 1. Obtener parámetros de manera segura
    $mesInput = request()->get('mes');
    $añoInput = request()->get('año');
    $departamentoFilter = request()->get('departamento');
    $ubicacionFilter = request()->get('ubicacion');

    $mes = ($mesInput && is_numeric($mesInput)) ? (int)$mesInput : Carbon::now()->month;
    $año = ($añoInput && is_numeric($añoInput)) ? (int)$añoInput : Carbon::now()->year;

    Carbon::setLocale('es');
    $fechaInicioMes = Carbon::create($año, $mes, 1)->startOfDay();
    $fechaFinMes = $fechaInicioMes->copy()->endOfMonth();

    // 2. Construcción de Semanas Laborales
    $inicioSemana = $fechaInicioMes->copy()->startOfWeek(Carbon::MONDAY);
    $semanasDelMes = [];
    while ($inicioSemana <= $fechaFinMes) {
        $finSemana = $inicioSemana->copy()->addDays(5)->endOfDay();
        if ($finSemana->month != $mes) {
            $finSemana = $fechaFinMes->copy()->endOfDay();
        }
        
        if ($inicioSemana->month == $mes || $finSemana->month == $mes) {
            $semanasDelMes[] = [
                'inicio_real' => $inicioSemana->copy(), 
                'fin_real' => $finSemana->copy()
            ];
        }
        $inicioSemana->addWeek();
    }

    $totalSemanas = count($semanasDelMes);
    $semanaPorDefecto = 0;
    
    if ($totalSemanas > 0) {
        foreach ($semanasDelMes as $idx => $s) {
            if (Carbon::now()->between($s['inicio_real'], $s['fin_real'])) {
                $semanaPorDefecto = $idx;
                break;
            }
        }
    }

    // 3. Obtener el índice de la semana seleccionada de manera segura
    $semanaIndexInput = request()->get('semana_index');
    $semanaIndex = ($semanaIndexInput !== null && $semanaIndexInput !== '') ? (int)$semanaIndexInput : $semanaPorDefecto;
    
    if ($semanaIndex < 0) $semanaIndex = 0;
    if ($totalSemanas > 0 && $semanaIndex >= $totalSemanas) {
        $semanaIndex = max(0, $totalSemanas - 1);
    }

    $semanaSeleccionada = $semanasDelMes[$semanaIndex] ?? null;
    
    $tablaResumen = [];
    $tipoAgrupacion = $departamentoFilter ? 'Ubicación' : 'Proceso';

    // 4. Lógica de agrupamiento y consulta
    if ($semanaSeleccionada) {
        $inicioConsulta = $semanaSeleccionada['inicio_real'];
        $finConsulta = $semanaSeleccionada['fin_real'];

        $queryVehiculos = Vehiculo::with(['supervisioSemanal' => function ($q) use ($inicioConsulta, $finConsulta){
            $q->whereBetween('fecha_captura', [$inicioConsulta, $finConsulta]);
        }])
        ->where('estado', '!=', 'Fuera de circulacion')
        ->select('id', 'no_economico', 'departamento', 'ubicacion', 'en_taller');

        if ($departamentoFilter) {
            $queryVehiculos->where('departamento', $departamentoFilter);
        }
        if ($ubicacionFilter) {
            $queryVehiculos->where('ubicacion', $ubicacionFilter);
        }

        $todosLosVehiculos = $queryVehiculos->get();
        $columnaAgrupacion = $departamentoFilter ? 'ubicacion' : 'departamento';

        $grupos = $todosLosVehiculos->groupBy($columnaAgrupacion);
        
        foreach ($grupos as $nombreDato => $vehiculosGrupo) {
            $nombre = $nombreDato ?: 'SIN ' . strtoupper($tipoAgrupacion);
            $totalVehiculos = $vehiculosGrupo->count();
            $vehiculosEnTallerColeccion = $vehiculosGrupo->where('en_taller', 1);
            $totalEnTaller = $vehiculosEnTallerColeccion->count();
            $listaEnTaller = $vehiculosEnTallerColeccion->pluck('no_economico')->toArray();
            $supervisionesRealizadas = 0;
            $pendientes = 0;

            foreach ($vehiculosGrupo as $vehiculo) {
                if ($vehiculo->supervisioSemanal->isNotEmpty()) {
                    $supervisionesRealizadas++;
                } else {
                    if (!$vehiculo->en_taller) {
                        $pendientes++; 
                    }
                }
            }
            
            $vehiculosActivos = $totalVehiculos - $totalEnTaller;
            $porcentaje = ($vehiculosActivos > 0) ? round(($supervisionesRealizadas / $vehiculosActivos) * 100) : 0;

            $tablaResumen[] = [
                'nombre' => $nombre, 
                'total_vehiculos' => $totalVehiculos,
                'en_taller' => $totalEnTaller,
                'vehiculos_en_taller' => $listaEnTaller, 
                'pendientes' => $pendientes,
                'cumplidos' => $supervisionesRealizadas, 
                'porcentaje' => $porcentaje
            ];
        }
        
        // Ordenar alfabéticamente
        usort($tablaResumen, function($a, $b) { 
            return strcmp($a['nombre'], $b['nombre']); 
        });
    }

    list($departamentos, $ubicaciones) = $this->getDatosFiltros($departamentoFilter);

    // 5. Renderizar a la vista
    render('supervision_semanal.index', [
        'nombreMes' => ucfirst($fechaInicioMes->translatedFormat('F Y')),
        'resumen' => $tablaResumen,
        'semanaIndex' => $semanaIndex,
        'totalSemanas' => $totalSemanas,
        'rangoSemana' => $semanaSeleccionada ? ($inicioConsulta->translatedFormat('d M') . ' - ' . $finConsulta->translatedFormat('d M')) : 'Sin semanas',
        'numeroSemana' => $semanaIndex + 1,
        'tipoAgrupacion' => $tipoAgrupacion,
        'departamentos' => $departamentos,
        'ubicaciones' => $ubicaciones,
        'filtrosActuales' => [
            'departamento' => $departamentoFilter, 
            'ubicacion' => $ubicacionFilter,
            'mes' => $mes, 
            'año' => $año, 
            'semana_index' => $semanaIndex
        ]
    ]);
}

    public function store()
    {
        // 1. Obtener datos y archivos
        $data = request()->body();
        $files = request()->files();

        // 2. Validación Manual (Lo básico)
        if (empty($data['vehiculo_id']) || empty($data['no_eco'])) {
            return response()->json(['status' => 'error', 'message' => 'Faltan datos del vehículo.'], 400);
        }

        $hasUploadedFile = false;
        foreach ($files as $file) {
            if (is_array($file) && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
                $hasUploadedFile = true;
                break;
            }
        }

        if (!$hasUploadedFile) {
            return response()->json(['status' => 'error', "message" => 'No has subido ninguna foto'], 400);
        }

        $requiredPhotos = [
            'foto_del'      => 'delantera',
            'foto_tra'      => 'trasera',
            'foto_lado_izq' => 'del lado izquierdo',
            'foto_lado_der' => 'del lado derecho',
            'foto_llanta_ref' => 'de la llanta de refaccion'
        ];
        // Validar que las fotos obligatorias vengan
        foreach ($requiredPhotos as $inputName => $userFriendlyName) {
            if (!isset($files[$inputName]) || $files[$inputName]['error'] !== UPLOAD_ERR_OK) {
                return response()->json(['status' => 'error', 'message' => "La foto $userFriendlyName es obligatoria."], 400);
            }
        }

        try {
            // 3. Preparar array para guardar
            $saveData = [
                'vehiculo_id' => $data['vehiculo_id'],
                'no_eco' => $data['no_eco'],
                'fecha_captura' => $data['fecha_captura'] ?? null,
                'resumen_est' => $data['resumen_est'] ?? null,
                'user_id' => auth()->id(),
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
                'status' => 'success',
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
        $maxDim = 1200; 
        $quality = 75;
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
        try {
            // 3. INICIO DE LA MAGIA DE INTERVENTION IMAGE
            
            // Creamos el manager (usando el driver GD)
            $manager = new ImageManager(new Driver());

            // Leemos la imagen desde el archivo temporal
            $image = $manager->read($fileArray['tmp_name']);

            // a. Redimensionar (scaleDown evita estirar imágenes pequeñas)
            // Solo reduce si la imagen es mayor a $maxDim, manteniendo la proporción.
            $image->scaleDown(width: $maxDim, height: $maxDim);

            // b. Orientación Automática (corrige fotos de iPhone/Samsung rotadas)
            // Nota: En la v3 esto suele ser automático al leer, pero si usas v2 es ->orientate()
            
            // c. Guardar comprimida y convertida a JPG
            $image->toJpeg($quality)->save($targetFile);

            // Retornar ruta relativa
            return $relativePath . '/' . $filename;

        } catch (\Exception $e) {
            // Si el archivo no es una imagen válida o falla algo
            // error_log($e->getMessage()); // Descomentar para debug
            return null;
        }

        return null;
    }

    public function generarReportePdf($id)
    {
        // 1. Buscar datos en BD
        $supervision = SupervisionSemanal::find($id);

        if (!$supervision) {
            return response()->json(['message' => 'Supervisión no encontrada'], 404);
        }

        // 2. Configurar Rutas
        $basePath = dirname(__DIR__, 2); // Raíz del proyecto
        $templatePath = $basePath . '/public/plantillas/plantilla_supervisiones.pdf';
        $publicPath = $basePath . '/public'; // Ruta base donde se guardan las fotos

        if (!file_exists($templatePath)) {
            die("Error: No se encuentra la plantilla PDF en " . $templatePath);
        }

        // 3. Mapear las columnas de el BD al Array de 9 fotos
        // El orden es: 0-5 (Página 1), 6-8 (Página 2)
        $listaImagenes = [
            $supervision->foto_del,         // [0] Frente
            $supervision->foto_tra,         // [1] Trasera
            $supervision->foto_lado_izq,    // [2] Lado Izq
            $supervision->foto_lado_der,    // [3] Lado Der
            $supervision->foto_poliza,      // [4] Poliza (o interior)
            $supervision->foto_tar_circ,    // [5] Tarjeta (o tablero)

            // --- PÁGINA 2 ---
            $supervision->foto_atent,       // [6] Atentado (Importante para el texto verde)
            $supervision->foto_kit,         // [7] Kit / Extintor
            $supervision->foto_llanta_ref   // [8] Llanta refacción
        ];

        // 4. Validar rutas absolutas de las imágenes
        $fotosProcesadas = [];
        foreach ($listaImagenes as $imgName) {
            if ($imgName && file_exists($publicPath . $imgName)) {
                $fotosProcesadas[] = $publicPath . $imgName;
            } else {
                // Si no hay foto, usamos null o una imagen placeholder "Sin Foto"
                // Para este ejemplo usaremos null y validaremos antes de pintar
                $fotosProcesadas[] = null;
                
                // Debug: Mostrar la ruta que no se encontró
                error_log("No se encontró la imagen: " . ($imgName ? $publicPath . $imgName : 'Vacía'));
            }
        }

        // ---------------- INICIO LÓGICA FPDI ----------------
        $pdf = new Fpdi('L', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        // --- PÁGINA 1: Cuadrícula 3x2 ---
        $pdf->AddPage();
        $pdf->setSourceFile($templatePath);
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl, 0, 0, 297); // Importar fondo CFE

        // Datos del Vehículo
        $pdf->SetFont('Helvetica', 'B', 20);
        $pdf->SetXY(220, 15);
        $pdf->Cell(50, 5, 'Eco: ' . $supervision->no_eco, 0, 0, 'R');

        // Configuración de fotos Pag 1
        $top    = 45;
        $left   = 15;
        $ancho  = 85;
        $alto   = 65;
        $espacio = 6;
        
        // Descripciones para las fotos de la página 1
        $descripcionesPagina1 = [
            0 => 'Frente',
            1 => 'Trasera',
            2 => 'Lado Izquierdo',
            3 => 'Lado Derecho',
            4 => 'Poliza/Interior',
            5 => 'Tarjeta/Tablero'
        ];

        for ($i = 0; $i < 6; $i++) {
            // Solo pintamos si existe la imagen
            if ($fotosProcesadas[$i]) {
                $col = $i % 3;
                $row = intdiv($i, 3);
                $x = $left + ($col * ($ancho + $espacio));
                $y = $top  + ($row * ($alto  + $espacio));

                // Image(file, x, y, w, h, type, link, align, resize, dpi, palign, ismask, imgmask, border)
                $pdf->Image($fotosProcesadas[$i], $x, $y, $ancho, $alto, '', '', 'T', false, 300, '', false, false, 1, true);
                
                // Añadir texto descriptivo debajo de la imagen
                $pdf->SetFont('Helvetica', 'B', 8);
                $pdf->SetTextColor(0, 0, 0); // Texto negro
                $pdf->SetXY($x, $y + $alto + 1);
                $pdf->Cell($ancho, 5, $descripcionesPagina1[$i], 0, 0, 'C');
            }
        }

        // --- PÁGINA 2: Fotos Grandes + Textos ---
        $pdf->AddPage();
        $pdf->useTemplate($tpl, 0, 0, 297); // Importar fondo CFE nuevamente

        // Datos del Vehículo
        $pdf->SetFont('Helvetica', 'B', 20);
        $pdf->SetXY(220, 15);
        $pdf->Cell(50, 5, 'Eco: ' . $supervision->no_eco, 0, 0, 'R');

        // Mismas dimensiones que la página 1
        $anchoGrande = 85;
        $altoGrande  = 65;
        $yFoto = 50;
        $espacioHorizontal = (297 - (3 * $anchoGrande)) / 4; // Espacio entre fotos y márgenes
        
        // Array de descripciones para cada foto
        $descripciones = [
            6 => 'Atentado',
            7 => 'Kit/Otros',
            8 => 'Llanta/Otros'
        ];

        // Mostrar las 3 fotos horizontalmente
        for ($i = 6; $i <= 8; $i++) {
            $x = $espacioHorizontal + (($i - 6) * ($anchoGrande + $espacioHorizontal));
            
            // Mostrar imagen si existe
            if (!empty($fotosProcesadas[$i])) {
                $pdf->Image($fotosProcesadas[$i], $x, $yFoto, $anchoGrande, $altoGrande, '', '', '', false, 300, '', false, false, 1);
                
                // Añadir texto descriptivo debajo de la imagen
                $pdf->SetFont('Helvetica', 'B', 8);
                $pdf->SetTextColor(0, 0, 0); // Texto negro
                $pdf->SetXY($x, $yFoto + $altoGrande + 1);
                $pdf->Cell($anchoGrande, 5, $descripciones[$i], 0, 1, 'C');
            }
        }

        // --- TEXTOS ---
        $pdf->SetFont('Helvetica', 'B', 14);
        $pdf->SetTextColor(0, 100, 0); // Verde oscuro

        // Texto dinámico: Si hay foto de atentado, cambiamos el texto
        
        if ($supervision->foto_atent) {
            $pdf->SetXY(25, $yFoto + 70);
            $pdf->SetTextColor(200, 0, 0); // Rojo si hay atentado
            $pdf->MultiCell(90, 8, "SE DETECTA EVIDENCIA\nDE ATENTADO O GOLPE", 0, 'L');
        } else {
            $pdf->SetXY(25, $yFoto + 20);
            $pdf->SetTextColor(0, 100, 0); // Verde
            $pdf->MultiCell(90, 8, "FOTO SI CUENTA CON ALGÚN\nATENTADO (NO APLICA)", 0, 'L');
        }

        // Texto General
        $pdf->SetXY(95, $yFoto + 90);
        $pdf->SetTextColor(0, 100, 0);
        $pdf->Cell(0, 10, "Observaciones:" . ($supervision->resumen_est ?? 'Sin observaciones'), 0, 1, 'L');

        // Título Final
        $pdf->SetFont('Helvetica', 'B', 16);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetY(250); // Casi al pie de página
        $pdf->Cell(0, 10, 'VEHÍCULOS EN BUEN ESTADO', 0, 1, 'C');

        // 5. Salida
        // 'I' para mostrar en navegador, 'D' para forzar descarga
        $pdf->Output('Supervision_' . $supervision->no_eco . '.pdf', 'I');
        exit;
    }

    public function getUbicacionesPorDepartamento()
{
    $departamento = request()->get('departamento');
    
    $query = Vehiculo::whereNotNull('ubicacion')->where('ubicacion', '!=', '');
    
    if ($departamento) {
        $query->where('departamento', $departamento);
    }
    
    $ubicaciones = $query->distinct()->orderBy('ubicacion')->pluck('ubicacion');

    $html = '<option value="">Todas las ubicaciones</option>';
    foreach ($ubicaciones as $ubicacion) {
        $html .= '<option value="' . $ubicacion . '">' . $ubicacion . '</option>';
    }

    echo $html; 
}
}
