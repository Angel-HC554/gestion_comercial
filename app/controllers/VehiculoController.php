<?php

namespace App\Controllers;

use App\Models\Vehiculo;
use App\Models\OrdenVehiculo;
use App\Models\SupervisionSemanal;
use App\Models\SupervisionDiaria;
use App\Models\AreaUsuario;
use App\Models\VehiculoArchivo;
use App\Models\OrdenArchivo;
use Carbon\Carbon;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class VehiculoController extends Controller
{
    // 1. Vista Principal
    public function index()
    {
        $estados = Vehiculo::distinct()->pluck('estado')->toArray();

        render('vehiculos.index', [
            'estados' => $estados
        ]);
    }

    // 2. API de Búsqueda y Filtrado
    public function search()
    {
        $user = auth()->user();

        $query = Vehiculo::with([
            'latestSupervision' => function ($q) {
                $q->select('id', 'vehiculo_id', 'kilometraje', 'fecha', 'hora_fin');
            },
            'latestMantenimiento'
        ]);

        if (!$user->is('admin')) {
            $asignaciones = AreaUsuario::with('area', 'subarea')
                ->where('user_id', $user->id)
                ->get();

            if ($asignaciones->isNotEmpty()) {
                $query->where(function ($groupQuery) use ($asignaciones) {
                    foreach ($asignaciones as $asignacion) {
                        // Por cada fila en 'area_usuarios', agregamos un OR
                        $groupQuery->orWhere(function ($subQuery) use ($asignacion) {

                            // Condición A: El departamento coincide
                            if ($asignacion->area) {
                                $subQuery->where('departamento', $asignacion->area->nombre);
                            }

                            // Condición B: La ubicación coincide (solo si la asignación tiene subarea)
                            if ($asignacion->subarea) {
                                $subQuery->where('ubicacion', $asignacion->subarea->nombre);
                            }
                        });
                    }
                });
            } else {
                $query->where('id', 0);
            }
        }

        // Obtener parámetros
        $page = (int) request()->get('page', 1);
        $perPage = (int) request()->get('perPage', 16);
        if ($perPage <= 0) $perPage = 16;
        $search = request()->get('search', '');
        $estado = request()->get('estado', '');
        $mantenimiento = request()->get('mantenimiento', '');

        // Filtros
        if ($search) {
            $query->where('no_economico', 'like', "%{$search}%");
        }

        // Validamos que estado no sea 'Todos' ni nulo
        if ($estado && $estado !== 'Todos') {
            $query->where('estado', $estado);
        }

        $query->orderBy('no_economico', 'asc');

        // 5. OBTENER COLECCIÓN (AQUÍ CAMBIA TODO)
        $vehiculosCollection = $query->get();

        // 6. FILTRADO POR MANTENIMIENTO (Lógica PHP)
        if ($mantenimiento && $mantenimiento !== 'Todos') {
            // Usamos filter de Colecciones de Laravel
            $vehiculosCollection = $vehiculosCollection->filter(function ($v) use ($mantenimiento) {
                // Aquí se ejecuta el Accessor getEstadoMantenimientoAttribute()
                $semaforo = $v->estado_mantenimiento;

                // Lógica especial para "urgente" (incluye rojo y rojo pasado)
                if ($mantenimiento === 'urgente') {
                    return in_array($semaforo, ['rojo', 'rojo_pasado']);
                }

                // Comparación normal (verde, amarillo)
                return $semaforo === $mantenimiento;
            });
        }

        // Paginación Manual
        $total = $vehiculosCollection->count();
        $lastPage = ceil($total / $perPage);

        $pagedData = $vehiculosCollection->slice(($page - 1) * $perPage, $perPage)->values();

        // 8. FORMATEO FINAL (Fotos)
        $pagedData->each(function ($v) {
            $v->foto = $v->foto_url;
        });

        return response()->json([
            'data' => $pagedData,
            'total' => $total,
            // Aquí estaba el error, ahora $perPage seguro es mayor a 0
            'last_page' => $lastPage > 0 ? $lastPage : 1,
            'current_page' => $page
        ]);
    }

    // 3. API para Guardar Nuevo Vehículo
    public function store()
    {
        // Validación manual simple (Leaf tiene validadores más complejos si instalas leaf/form)
        $data = request()->body();

        $required = ['no_economico', 'departamento', 'ubicacion', 'placas', 'tipo_vehiculo', 'marca', 'modelo', 'año', 'estado', 'propiedad', 'rpe_responsable'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                return response()->json([
                    'status' => 'error',
                    'message' => "El campo {$field} es obligatorio."
                ], 400);
            }
        }

        // Validación de duplicados (No Económico)
        if (Vehiculo::where('no_economico', $data['no_economico'])->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => "El número económico {$data['no_economico']} ya existe."
            ], 400);
        }

        try {
            $vehiculo = Vehiculo::create($data);

            return response()->json([
                'status' => 'success',
                'message' => '¡Vehículo creado correctamente!',
                'data' => $vehiculo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }

    // 4. Vista de Detalle
    public function show($id)
    {
        $vehiculo = Vehiculo::find($id);

        if (!$vehiculo) {
            response()->redirect('/vehiculos');
            return;
        }

        // Cargar relaciones necesarias para los cálculos del modelo
        $vehiculo->load(['supervisioDiaria', 'supervisioSemanal']);
        $vehiculo->load(['latestSupervision', 'latestMantenimiento']);

        $numero_eco = $vehiculo->no_economico;
        //---------------------------------------------------->
        // 1. Obtener ordenes para la TABLA INFERIOR
        $ordenes = OrdenVehiculo::with('archivo', 'detallePropio', 'detalleArrendado')
            ->where('noeconomico', $numero_eco)
            ->orderBy('id', 'desc')
            ->get();

        // 2. LOGICA PARA LA GRÁFICA
        $ordenesParaGrafica = $ordenes->sortBy('created_at');

        // Catálogo de reparaciones
        $catalogoReparaciones = [
            'vehicle1' => 'Afinación mayor',
            'vehicle11' => 'Medio motor',
            'vehicle2' => 'Ajuste motor',
            'vehicle12' => 'Motor completo',
            'vehicle3' => 'Alineación y balanceo',
            'vehicle13' => 'Parabrisas y vidrios',
            'vehicle4' => 'Amortiguadores',
            'vehicle14' => 'Frenos',
            'vehicle5' => 'Cambio aceite y filtro',
            'vehicle15' => 'Sistema eléctrico',
            'vehicle6' => 'Clutch',
            'vehicle16' => 'Sistema de enfriamiento',
            'vehicle7' => 'Diagnóstico',
            'vehicle17' => 'Suspensión',
            'vehicle8' => 'Dirección',
            'vehicle18' => 'Transmisión y diferencial',
            'vehicle9' => 'Lavado y engrasado',
            'vehicle19' => 'Tapicería',
            'vehicle10' => 'Hojalatería y pintura',
            'vehicle20' => 'Otro',
        ];

        // Mapeo de datos para ChartJS
        $chartData = $ordenesParaGrafica->map(function ($orden) use ($catalogoReparaciones) {
            $reparaciones = [];
            $esGolpe = false;
            $fecha = $orden->created_at ? $orden->created_at->format('Y-m-d') : null;
            $taller = 'N/A';
            $observacion = '';

            if ($orden->detallePropio) {
                $det = $orden->detallePropio;

                // Extraer fecha, taller y observación de la tabla hija
                $fecha = $det->fechafirm ?? $fecha;
                $taller = $det->taller;
                $observacion = $det->observacion;

                // Extraer reparaciones (vehicle1...vehicle20 solo existen en propios)
                foreach ($catalogoReparaciones as $key => $label) {
                    if (!empty($det->$key) && $det->$key != '0') {
                        $reparaciones[] = $label;
                        if ($key === 'vehicle10') {
                            $esGolpe = true;
                        }
                    }
                }
            } elseif ($orden->detalleArrendado) {
                $det = $orden->detalleArrendado;

                // Arrendados tienen campos distintos
                $fecha = $det->fecha_gen ?? $fecha;
                $taller = $det->taller ?? 'N/A';
                $observacion = "Sin observaciones";

                // Si quieres mostrar el tipo de servicio como una "reparación" en la gráfica: OPCIONAAAAAAAAAL
                if ($det->tipo_servicio) {
                    $reparaciones[] = $det->tipo_servicio;
                }
            }

            return [
                'id' => $orden->id,
                'fecha' => $fecha,
                'km' => (int) str_replace(',', '', $orden->kilometraje),
                'taller' => $taller,
                'es_golpe' => $esGolpe,
                'reparaciones' => $reparaciones,
                'observacion' => $observacion,
                'archivo' => $orden->archivo ? ['ruta_archivo' => $orden->archivo->ruta_archivo] : null,
                'servicio' => $orden->requiere_servicio
            ];
        })->values();

        // Fotos de la última supervisión semanal
        $fotos = SupervisionSemanal::select('foto_del', 'foto_tra', 'foto_lado_der', 'foto_lado_izq')
            ->where('no_eco', $numero_eco)
            ->orderBy('fecha_captura', 'desc')
            ->first();

        // Checar si ya existe supervisión esta semana
        $supervision_existe = $vehiculo->tieneSupervisionSemanal();
        $id_supervision     = $supervision_existe ? $vehiculo->obtenerIdSupervisionSemanal() : null;

        // Buscar una orden que NO esté terminada para este vehículo
        $ordenActiva = OrdenVehiculo::where('noeconomico', $vehiculo->no_economico)
            ->whereIn('status', ['VEHICULO TALLER'])
            ->latest() // Por si hubiera error y hay 2, tomamos la última
            ->first();

        // Renderizar vista
        render('vehiculos.show', [
            'vehiculo' => $vehiculo,
            'ordenes' => $ordenes,
            'supervision_existe' => $supervision_existe,
            'id_supervision' => $id_supervision,
            'fotos' => $fotos,
            'ordenActiva' => $ordenActiva,
            'chartData' => $chartData
        ]);
    }

    public function import()
    {
        // 1. Validar que se haya subido un archivo
        $files = request()->files();

        if (!isset($files['archivoExcel']) || $files['archivoExcel']['error'] !== UPLOAD_ERR_OK) {
            return response()->json(['status' => 'error', 'message' => 'Error al subir el archivo.'], 400);
        }

        $file = $files['archivoExcel'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'xls'])) {
            return response()->json(['status' => 'error', 'message' => 'Formato inválido. Use .xlsx o .xls'], 400);
        }

        try {
            // 2. Cargar el Excel
            $spreadsheet = IOFactory::load($file['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();

            // Obtener todas las filas como un array de arrays
            // null, true, true, true asegura que las celdas vacías sean null y mantenga el formato
            $rows = $sheet->toArray(null, true, true, true);

            // 3. Mapear Encabezados (Fila 1)
            // Buscar en qué letra de columna está cada nombre
            $headers = array_shift($rows); // Saca la primera fila y la guarda en $headers

            // Filtrar encabezados nulos o vacíos antes de usar array_flip
            $headers = array_filter($headers, function ($value) {
                return $value !== null && $value !== '';
            });

            $colMap = array_flip($headers);

            // Validar que existan columnas críticas
            if (!isset($colMap['Número Económico'])) {
                return response()->json(['status' => 'error', 'message' => 'El archivo no tiene la columna "Número Económico".'], 400);
            }

            $normalizeSiNoToInt = function ($value) {
                if (is_bool($value)) return $value ? 1 : 0;
                if ($value === null) return 0;

                $raw = trim((string) $value);
                if ($raw === '') return 0;

                $raw = strtoupper($raw);
                $raw = strtr($raw, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U']);

                if (in_array($raw, ['SI', 'S', 'YES', 'Y', 'TRUE', '1'], true)) return 1;
                if (in_array($raw, ['NO', 'N', 'FALSE', '0'], true)) return 0;

                if (is_numeric($raw)) return ((int) $raw) > 0 ? 1 : 0;

                return 0;
            };

            $countCreated = 0;
            $countUpdated = 0;

            // 4. Recorrer los datos (Filas 2 en adelante)
            foreach ($rows as $row) {
                // Función auxiliar para obtener dato seguro usando el mapa
                $getVal = function ($colName) use ($row, $colMap) {
                    $colLetter = $colMap[$colName] ?? null;
                    return $colLetter ? ($row[$colLetter] ?? null) : null;
                };

                // Obtener identificadores
                $idExcel = $getVal('#'); // Puede venir null si es nuevo
                $noEconomico = $getVal('Número Económico');

                if (!$noEconomico) continue; // Saltar filas vacías

                // Preparar array de datos (Mapeo exacto de tu archivo anterior)
                $vehiculoData = [
                    'no_economico'  => $noEconomico,
                    'serie'         => $getVal('Serie'),
                    'departamento'  => $getVal('Departamento'),
                    'ubicacion'     => $getVal('Ubicación'),
                    'placas'        => $getVal('Placas'),
                    'tipo_vehiculo' => $getVal('Tipo Vehículo'),
                    'marca'         => $getVal('Marca'),
                    'modelo'        => $getVal('Modelo'),
                    'año'           => (int) $getVal('Año'),
                    'estado'        => $getVal('Estado') ?? 'En circulacion',
                    'propiedad'     => $getVal('Propiedad'),
                    'rpe_responsable'   => $getVal('RPE Responsable') ?? 'NA',
                ];

                if (isset($colMap['En Taller'])) {
                    $vehiculoData['en_taller'] = $normalizeSiNoToInt($getVal('En Taller'));
                }

                if (isset($colMap['Finalizado'])) {
                    $vehiculoData['finalizado'] = $normalizeSiNoToInt($getVal('Finalizado'));
                }

                // 5. LÓGICA DE UPSERT (Actualizar o Crear)
                $vehiculo = null;

                // A) Si el Excel trae ID, buscamos por ID
                if ($idExcel) {
                    $vehiculo = Vehiculo::find($idExcel);
                }

                // B) Si no se encontró por ID (o no traía), buscamos por No. Económico (Clave única de negocio)
                if (!$vehiculo) {
                    $vehiculo = Vehiculo::where('no_economico', $noEconomico)->first();
                }

                if ($vehiculo) {
                    // --- ACTUALIZAR ---
                    // Actualizamos solo si existen cambios (Eloquent lo maneja, pero hacemos update directo)
                    $vehiculo->update($vehiculoData);
                    $countUpdated++;
                } else {
                    // --- CREAR ---
                    // Si el Excel traía un ID pero no existía en BD, podemos forzar ese ID o dejar que sea autoincrement
                    if ($idExcel) {
                        $vehiculoData['id'] = $idExcel;
                    }
                    Vehiculo::create($vehiculoData);
                    $countCreated++;
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => "Importación exitosa: $countCreated nuevos, $countUpdated actualizados."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error procesando Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export()
    {
        try {
            // 1. Obtener datos
            $vehiculos = Vehiculo::all();

            // 2. Crear Spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // 3. Definir Encabezados
            $headers = [
                '#',
                'Número Económico',
                'Serie',
                'Departamento',
                'Ubicación',
                'Placas',
                'Tipo Vehículo',
                'Marca',
                'Modelo',
                'Año',
                'Ordenes Pendientes',
                'En Taller',
                'Finalizado',
                'Estado',
                'Propiedad',
                'RPE Responsable'
            ];

            // Escribir encabezados en fila 1 (A1, B1, C1...)
            // fromArray escribe una fila completa desde un array
            $sheet->fromArray($headers, NULL, 'A1');

            // 4. Escribir Datos
            $rowNum = 2; // Empezamos en la fila 2
            foreach ($vehiculos as $v) {
                $rowData = [
                    $v->id,
                    $v->no_economico,
                    $v->serie,
                    $v->departamento,
                    $v->ubicacion,
                    $v->placas,
                    $v->tipo_vehiculo,
                    $v->marca,
                    $v->modelo,
                    $v->año,
                    $v->ordenes_pendientes,
                    $v->en_taller ? 'SI' : 'NO',
                    $v->finalizado ? 'SI' : 'NO',
                    $v->estado,
                    $v->propiedad,
                    $v->rpe_responsable,
                ];
                $sheet->fromArray($rowData, NULL, 'A' . $rowNum);
                $rowNum++;
            }

            // 5. ESTILOS
            $lastCol = 'P'; // Columna 13
            $headerRange = "A1:{$lastCol}1";

            // A) Autoajustar ancho de columnas
            foreach (range('A', $lastCol) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // B) Estilos del Encabezado
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'A7F5D0'], // Verde claro
                ],
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
            ];
            $sheet->getStyle($headerRange)->applyFromArray($headerStyle);

            // C) Congelar primera fila
            $sheet->freezePane('A2');

            // 6. Generar Descarga
            $writer = new Xlsx($spreadsheet);

            $filename = 'vehiculos_' . date('Y-m-d_H-i') . '.xlsx';

            // Headers HTTP para forzar la descarga
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit; // Detener ejecución para no imprimir nada más

        } catch (\Exception $e) {
            // Si falla, mostramos JSON (aunque el navegador esperara archivo)
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function historial($id)
    {
        // 1. Buscamos el vehículo
        $vehiculo = Vehiculo::find($id);

        if (!$vehiculo) {
            response()->page(ViewsPath('errors/404.html', false), 404);
            return;
        }

        // 2. Obtenemos supervisiones
        $supervisiones = SupervisionDiaria::where('vehiculo_id', $id)
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio', 'desc')
            ->get();

        // 3. Renderizamos la vista
        render('vehiculos.historial', [
            'vehiculo' => $vehiculo,
            'supervisiones' => $supervisiones
        ]);
    }
    /**
     * Obtener la lista de archivos para el frontend
     */
    public function getDocumentos($id)
    {
        $documentos = VehiculoArchivo::where('vehiculo_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        $documentos->transform(function ($doc) {
            $doc->url = $doc->ruta_archivo;
            return $doc;
        });

        return response()->json($documentos);
    }

    /**
     * Recibir y guardar el nuevo documento PDF
     */
    public function storeDocumento($id)
    {
        // 1. Validar que se envió un archivo
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            return response()->json(['status' => 'error', 'message' => 'No se ha seleccionado ningún archivo válido.'], 400);
        }

        $file = $_FILES['archivo'];
        $nombreDoc = request()->get('nombre', 'Documento sin nombre');

        // 2. Validar que sea PDF por seguridad
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            return response()->json(['status' => 'error', 'message' => 'Solo se permiten archivos en formato PDF.'], 400);
        }

        try {
            // 3. Preparar el directorio de subida (ej. public/expedientes/5/)
            $basePath = dirname(__DIR__, 2); // Ajusta según la estructura de tu proyecto Leaf
            $uploadDir = $basePath . '/public/expedientes/' . $id . '/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // 4. Generar nombre único para no sobreescribir
            $fileName = 'doc_' . time() . '_' . uniqid() . '.pdf';
            $targetPath = $uploadDir . $fileName;

            // 5. Mover el archivo e insertar en la BD
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                
                $rutaRelativa = '/expedientes/' . $id . '/' . $fileName;

                VehiculoArchivo::create([
                    'vehiculo_id' => $id,
                    'nombre'      => strtoupper($nombreDoc),
                    'ruta_archivo'=> $rutaRelativa
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Documento guardado correctamente.'
                ]);
            } else {
                throw new \Exception("No se pudo mover el archivo al directorio de destino.");
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}
