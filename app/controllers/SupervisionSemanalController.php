<?php

namespace App\Controllers;

use App\Models\SupervisionSemanal;
use App\Models\Vehiculo;
use Carbon\Carbon;
use setasign\Fpdi\Tcpdf\Fpdi;

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
                ->select('id', 'vehiculo_id', 'created_at')
                ->orderBy('created_at', 'desc');
        }]);

        $vehiculos = $queryVehiculos->get();

        // 5. Procesamiento de Datos (Matriz)
        $vehiculosProcesados = $vehiculos->map(function ($vehiculo) use ($semanasDelMes, $hoy) {
            $statusPorSemana = [];
            $incumplimientos = 0;

            foreach ($semanasDelMes as $index => $semana) {
                // Buscamos la supervisión en el rango real de esa semana
                $supervision = $vehiculo->supervisioSemanal->first(function ($sup) use ($semana) {
                    return $sup->created_at->between($semana['inicio_real'], $semana['fin_real']);
                });

                if ($supervision) {
                    $status = [
                        'tipo' => 'cumplido',
                        'id' => $supervision->id,
                        'fecha' => $supervision->created_at->format('d/m/Y')
                    ];
                } elseif ($semana['fin_real']->isFuture()) {
                    $status = [
                        'tipo' => 'futuro'
                    ];
                } else {
                    $status = [
                        'tipo' => 'no_cumplido'
                    ];
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

        // 3. Mapear las columnas de tu BD al Array de 9 fotos
        // El orden es: 0-5 (Página 1), 6-8 (Página 2)
        // Usa los nombres de columnas de tu YAML anterior
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

        // ---------------- INICIO LÓGICA FPDI (Tu código adaptado) ----------------
        $pdf = new Fpdi('L', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        // --- PÁGINA 1: Cuadrícula 3x2 ---
        $pdf->AddPage();
        $pdf->setSourceFile($templatePath);
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl, 0, 0, 297); // Importar fondo CFE

        // Datos del Vehículo (Opcional: Poner No. Económico en el encabezado)
        $pdf->SetFont('Helvetica', 'B', 20);
        $pdf->SetXY(220, 15); // Ajusta según tu plantilla
        $pdf->Cell(50, 5, 'Eco: ' . $supervision->no_eco, 0, 0, 'R');

        // Configuración de fotos Pag 1
        $top    = 50;  // Bajé un poco para dar aire al logo
        $left   = 28;
        $ancho  = 75;
        $alto   = 60;
        $espacio = 8;

        for ($i = 0; $i < 6; $i++) {
            // Solo pintamos si existe la imagen
            if ($fotosProcesadas[$i]) {
                $col = $i % 3;
                $row = intdiv($i, 3);
                $x = $left + ($col * ($ancho + $espacio));
                $y = $top  + ($row * ($alto  + $espacio));

                // Image(file, x, y, w, h, type, link, align, resize, dpi, palign, ismask, imgmask, border)
                $pdf->Image($fotosProcesadas[$i], $x, $y, $ancho, $alto, '', '', 'T', false, 300, '', false, false, 1, true);
            }
        }

        // --- PÁGINA 2: Fotos Grandes + Textos ---
        $pdf->AddPage();
        $pdf->useTemplate($tpl, 0, 0, 297); // Importar fondo CFE nuevamente

        // Datos del Vehículo (Opcional: Poner No. Económico en el encabezado)
        $pdf->SetFont('Helvetica', 'B', 20);
        $pdf->SetXY(220, 15); // Ajusta según tu plantilla
        $pdf->Cell(50, 5, 'Eco: ' . $supervision->no_eco, 0, 0, 'R');

        $anchoGrande = 75;
        $altoGrande  = 60;
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
                $pdf->SetFont('Helvetica', 'B', 10);
                $pdf->SetTextColor(0, 0, 0); // Texto negro
                $pdf->SetXY($x, $yFoto + $altoGrande + 1);
                $pdf->Cell($anchoGrande, 5, $descripciones[$i], 0, 1, 'C');
            }
        }

        // --- TEXTOS ---
        $pdf->SetFont('Helvetica', 'B', 14); // Un poco más pequeño para asegurar que quepa
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
        $pdf->Cell(0, 10, "Observaciones:\n" . ($supervision->resumen_est ?? 'Sin observaciones'), 0, 1, 'L');

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
}
