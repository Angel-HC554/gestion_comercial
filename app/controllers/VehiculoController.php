<?php

namespace App\Controllers;

use App\Models\Vehiculo;
use App\Models\OrdenVehiculo;
use App\Models\SupervisionSemanal;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class VehiculoController extends Controller
{
    // 1. Vista Principal (equivalente a lo que cargaba el componente Livewire)
    public function index()
    {
        // Renderizamos la vista vacía. AlpineJS llamará a search() al cargar.
        // Pasamos los estados únicos para llenar el select de filtros en el frontend si es necesario,
        // o puedes hacer una llamada API separada para eso. Aquí lo inyecto directo para SSR.
        $estados = Vehiculo::distinct()->pluck('estado')->toArray();
        
        render('vehiculos.index', [
            'estados' => $estados
        ]);
    }

    // 2. API de Búsqueda y Filtrado (Reemplaza a VerAutos.php de Livewire)
    public function search()
    {
        // Obtener parámetros
        $page = (int) request()->get('page', 1);
        $perPage = (int) request()->get('perPage', 16);
        $search = request()->get('search', '');
        $estado = request()->get('estado', '');

        // --- CORRECCIÓN: Validación de seguridad ---
        // Si por alguna razón perPage es 0 o negativo, forzamos a 16
        if ($perPage <= 0) {
            $perPage = 16;
        }
        // -------------------------------------------

        // Query Builder
        $query = Vehiculo::query()
            ->select('id', 'marca', 'modelo', 'año', 'no_economico', 'estado', 'tipo_vehiculo', 'placas');

        // Filtros
        if ($search) {
            $query->where('no_economico', 'like', "%{$search}%");
        }
        
        // Validamos que estado no sea 'Todos' ni nulo
        if ($estado && $estado !== 'Todos') {
            $query->where('estado', $estado);
        }

        // Paginación Manual
        $total = $query->count();
        $vehiculos = $query->orderBy('no_economico', 'asc')
                           ->skip(($page - 1) * $perPage)
                           ->take($perPage)
                           ->get();

        return response()->json([
            'data' => $vehiculos,
            'total' => $total,
            // Aquí estaba el error, ahora $perPage seguro es mayor a 0
            'last_page' => ceil($total / $perPage), 
            'current_page' => $page
        ]);
    }

    // 3. API para Guardar Nuevo Vehículo (Reemplaza a VehiculoModales.php -> guardarVehiculos)
    public function store()
    {
        // Validación manual simple (Leaf tiene validadores más complejos si instalas leaf/form)
        $data = request()->body();
        
        // Lista de campos requeridos basados en tu validación de Livewire
        $required = ['agencia', 'no_economico', 'placas', 'tipo_vehiculo', 'marca', 'modelo', 'año', 'estado', 'propiedad', 'proceso', 'rpe_creamod'];
        
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
            // Manejo de alias (nullable)
            if (!isset($data['alias'])) {
                $data['alias'] = null;
            }

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

    // 4. Vista de Detalle (Show) - Migrado casi igual que en Laravel
    public function show($id)
    {
        $vehiculo = Vehiculo::find($id);

        if (!$vehiculo) {
            // Puedes redirigir o mostrar error 404
            response()->redirect('/vehiculos'); 
            return;
        }

        // Cargar relaciones necesarias para los cálculos del modelo
        // Nota: Asegúrate que latestSupervision y latestMantenimiento estén definidos en tu modelo Vehiculo
        $vehiculo->load(['supervisioDiaria', 'supervisioSemanal']); 
        // Eloquent a veces prefiere cargar las relaciones base si usas lógica custom en los accessors,
        // pero si usaste 'hasOne' en latestSupervision, puedes cargarla directo:
        $vehiculo->load(['latestSupervision', 'latestMantenimiento']);

        $numero_eco = $vehiculo->no_economico;

        // Obtener historial de órdenes
        // Nota: 'archivos' y 'historial' deben ser relaciones en OrdenVehiculo si las usas con 'with'
        // Si no existen aún, quita el ->with(...)
        $ordenes = OrdenVehiculo::where('noeconomico', $numero_eco)
                    ->orderBy('id', 'desc')
                    ->get();

        // Fotos de la última supervisión semanal
        $fotos = SupervisionSemanal::select('foto_del', 'foto_tra', 'foto_lado_der', 'foto_lado_izq')
            ->where('no_eco', $numero_eco)
            ->orderBy('created_at', 'desc')
            ->first();

        // Checar si ya existe supervisión esta semana
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        $supervision_existe = SupervisionSemanal::where('no_eco', $numero_eco)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->exists();

        // Renderizar vista
        render('vehiculos.show', [
            'vehiculo' => $vehiculo,
            'ordenes' => $ordenes,
            'supervision_existe' => $supervision_existe,
            'fotos' => $fotos
        ]);
    }
    
    // TODO: Implementar import() y export()
    // Nota: Para Excel en Leaf necesitarás instalar 'phpoffice/phpspreadsheet' 
    // ya que 'Maatwebsite\Excel' es exclusivo de Laravel.
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
            
            // $rows tiene formato: [1 => ['A'=>'#', 'B'=>'Agencia'...], 2 => ['A'=>'1', 'B'=>'DW01'...]]

            // 3. Mapear Encabezados (Fila 1)
            // Buscamos en qué letra de columna está cada nombre
            $headers = array_shift($rows); // Saca la primera fila y la guarda en $headers
            
            // Mapa inverso: 'Número Económico' => 'C', 'Agencia' => 'B'
            $colMap = array_flip($headers);

            // Validar que existan columnas críticas
            if (!isset($colMap['Número Económico'])) {
                return response()->json(['status' => 'error', 'message' => 'El archivo no tiene la columna "Número Económico".'], 400);
            }

            $countCreated = 0;
            $countUpdated = 0;

            // 4. Recorrer los datos (Filas 2 en adelante)
            foreach ($rows as $row) {
                // Función auxiliar para obtener dato seguro usando el mapa
                $getVal = function($colName) use ($row, $colMap) {
                    $colLetter = $colMap[$colName] ?? null;
                    return $colLetter ? ($row[$colLetter] ?? null) : null;
                };

                // Obtener identificadores
                $idExcel = $getVal('#'); // Puede venir null si es nuevo
                $noEconomico = $getVal('Número Económico');

                if (!$noEconomico) continue; // Saltar filas vacías

                // Preparar array de datos (Mapeo exacto de tu archivo anterior)
                $vehiculoData = [
                    'agencia'       => $getVal('Agencia'),
                    'no_economico'  => $noEconomico,
                    'placas'        => $getVal('Placas'),
                    'tipo_vehiculo' => $getVal('Tipo Vehículo'),
                    'marca'         => $getVal('Marca'),
                    'modelo'        => $getVal('Modelo'),
                    'año'           => (int) $getVal('Año'),
                    'estado'        => $getVal('Estado'),
                    'propiedad'     => $getVal('Propiedad'),
                    'proceso'       => $getVal('Proceso'),
                    'alias'         => $getVal('Alias'),
                    'rpe_creamod'   => $getVal('RPE Crea/Modifica'),
                ];

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
            $vehiculos = Vehiculo::all(); // O usa tu lógica de filtros si prefieres

            // 2. Crear Spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // 3. Definir Encabezados (Idénticos a tu Import/Export anterior)
            $headers = [
                '#', 'Agencia', 'Número Económico', 'Placas', 'Tipo Vehículo', 
                'Marca', 'Modelo', 'Año', 'Estado', 'Propiedad', 
                'Proceso', 'Alias', 'RPE Crea/Modifica'
            ];

            // Escribir encabezados en fila 1 (A1, B1, C1...)
            // fromArray escribe una fila completa desde un array
            $sheet->fromArray($headers, NULL, 'A1');

            // 4. Escribir Datos
            $rowNum = 2; // Empezamos en la fila 2
            foreach ($vehiculos as $v) {
                $rowData = [
                    $v->id,
                    $v->agencia,
                    $v->no_economico,
                    $v->placas,
                    $v->tipo_vehiculo,
                    $v->marca,
                    $v->modelo,
                    $v->año,
                    $v->estado,
                    $v->propiedad,
                    $v->proceso,
                    $v->alias,
                    $v->rpe_creamod
                ];
                $sheet->fromArray($rowData, NULL, 'A' . $rowNum);
                $rowNum++;
            }

            // 5. ESTILOS (Replicando tu VehiculosExport.php)
            $lastCol = 'M'; // Columna 13
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
                    'startColor' => ['rgb' => 'F2F2F2'], // Gris claro
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
            header('Content-Disposition: attachment; filename="'. urlencode($filename) .'"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit; // Detener ejecución para no imprimir nada más

        } catch (\Exception $e) {
            // Si falla, mostramos JSON (aunque el navegador esperara archivo)
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}