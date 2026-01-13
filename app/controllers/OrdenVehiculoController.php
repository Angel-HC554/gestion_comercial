<?php

namespace App\Controllers;

// Importamos el modelo que acabamos de crear
use App\Models\OrdenVehiculo;
use App\Models\Vehiculo;
use App\Models\VehiculoSalidaTaller;
use App\Models\OrdenArchivo;
use App\Models\HistorialOrden;
use clsTinyButStrong;

class OrdenVehiculoController extends Controller
{
    // 1. Carga la vista inicial
    public function index()
    {
        // Renderizamos la vista vacía, AlpineJS cargará los datos al iniciar
        render('ordenvehiculos.index');
    }

    // 2. API para la tabla (Filtrado y Paginación)
    public function search()
    {
        $page = request()->get('page', 1);
        $perPage = request()->get('perPage', 10);
        $search = request()->get('search', '');
        $estado = request()->get('estado', '');
        $fecha_inicio = request()->get('fecha_inicio', '');
        $fecha_fin = request()->get('fecha_fin', '');

        // NUEVO PARÁMETRO: Para filtrar exactamente por un vehículo (vista show)
        $noEconomicoExacto = request()->get('no_economico_exacto', '');

        // Query Builder de Eloquent
        $query = OrdenVehiculo::query()
            ->with('archivo')
            ->select('id', 'area', 'zona', 'departamento', 'noeconomico', 'status', 'fechafirm', 'orden_500', 'requiere_servicio', 'observacion',
                    'vehicle1',
            'vehicle2',
            'vehicle3',
            'vehicle4',
            'vehicle5',
            'vehicle6',
            'vehicle7',
            'vehicle8',
            'vehicle9',
            'vehicle10',
            'vehicle11',
            'vehicle12',
            'vehicle13',
            'vehicle14',
            'vehicle15',
            'vehicle16',
            'vehicle17',
            'vehicle18',
            'vehicle19',
            'vehicle20',);
        if ($noEconomicoExacto) {
            // Filtro exacto para la vista "Show" del vehículo
            $query->where('noeconomico', $noEconomicoExacto);
        }
        // Filtros
        if ($search) {
            $query->where('noeconomico', 'like', "%{$search}%");
        }
        if ($estado) {
            $query->where('status', $estado);
        }
        if ($fecha_inicio) {
            $query->where('fechafirm', '>=', $fecha_inicio);
        }
        if ($fecha_fin) {
            $query->where('fechafirm', '<=', $fecha_fin);
        }

        // Paginación manual para API
        $total = $query->count();
        $ordenes = $query->orderBy('id', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return response()->json([
            'data' => $ordenes,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'current_page' => (int)$page
        ]);
    }
    public function create()
    {
        // 1. Obtener ID de la URL (si existe)
        $vehiculoId = request()->get('vehiculo_id');
        $returnUrl = request()->get('return_url');

        $preseleccionado = null;
        $ultimoKm = 0;
        // 2. Si venimos del Show, buscamos los datos
        if ($vehiculoId) {
            $vehiculo = Vehiculo::find($vehiculoId);
            if ($vehiculo) {
                // Reutilizamos tu lógica de obtener último KM (asegúrate que el modelo Vehiculo tenga este método o calculalo aquí)
                $kmData = $vehiculo->ultimoKilometraje(); // Asumiendo que este método existe en tu modelo Vehiculo
                $ultimoKm = $kmData['kilometraje'] ?? 0;

                $preseleccionado = [
                    'no_economico' => $vehiculo->no_economico,
                    'placas'       => $vehiculo->placas,
                    'marca'        => $vehiculo->marca,
                    'modelo'       => $vehiculo->modelo, // Ejemplo de mapeo
                ];
            }
        }
        // Obtener el siguiente ID disponible
        $nextId = OrdenVehiculo::max('id') + 1;
        $nextId = $nextId ?: 1; // Si no hay registros, comenzar desde 1

        $vehiculosRaw = Vehiculo::all();

        $vehiculos = $vehiculosRaw->map(function($v) {
        // Calculamos el KM para CADA vehículo de la lista
        $k = $v->ultimoKilometraje();
        
        return [
            'no_economico' => $v->no_economico,
            'placas'       => $v->placas,
            'marca'        => $v->marca,
            'modelo'       => $v->modelo,
            // ¡ESTO ES LO NUEVO! Enviamos el último KM al frontend
            'ultimo_km'    => $k['kilometraje'] ?? 0 
        ];
    });

        // 2. DATOS DE EJEMPLO: Vehículos (Simulando tu tabla 'vehiculos')
        //$vehiculos = Vehiculo::query()->select('no_economico', 'placas', 'marca', 'modelo')->get();

        // 3. DATOS DE EJEMPLO: Usuarios (Simulando tu tabla 'users')
        // Nota: 'usuario' aquí simula ser el RPE
        $users = [
            ['name' => 'JUAN PEREZ LOPEZ', 'usuario' => '98765'],
            ['name' => 'MARIA GONZALEZ', 'usuario' => '12345'],
            ['name' => 'PEDRO PARAM', 'usuario' => '54321'],
            ['name' => 'ADMINISTRADOR SISTEMA', 'usuario' => '11111']
        ];

        // 4. Enviamos todo a la vista
        // 'ordenEditar' va null porque estamos CREANDO
        render('ordenvehiculos.create', [
            'id' => $nextId,
            'vehiculos' => $vehiculos,
            'users' => $users,
            'ordenEditar' => null,
            // NUEVAS VARIABLES
            'preseleccionado' => $preseleccionado,
            'ultimoKm' => $ultimoKm
            ,'returnUrl' => $returnUrl ?: '/ordenvehiculos'
        ]);
    }

    public function store()
    {
        // 1. Obtener todos los datos directamente (sin validar reglas estrictas)
        // Esto incluye 'area', 'zona', etc. tal cual vienen del formulario.
        $data = request()->body();
        // --- PASO DE LIMPIEZA ---
        // Eliminamos la coma (,) antes de cualquier validación o guardado
        if (isset($data['kilometraje'])) {
            $data['kilometraje'] = str_replace(',', '', $data['kilometraje']);
        }
        // ------------------------

        // 2. Manejo de Checkboxes (Si no están marcados, no llegan en el request)
        $checkboxes = [
            'vehicle1',
            'vehicle2',
            'vehicle3',
            'vehicle4',
            'vehicle5',
            'vehicle6',
            'vehicle7',
            'vehicle8',
            'vehicle9',
            'vehicle10',
            'vehicle11',
            'vehicle12',
            'vehicle13',
            'vehicle14',
            'vehicle15',
            'vehicle16',
            'vehicle17',
            'vehicle18',
            'vehicle19',
            'vehicle20',
        ];

        foreach ($checkboxes as $checkbox) {
            // Si no existe en $data (porque no se marcó), lo ponemos vacío
            if (!isset($data[$checkbox])) {
                $data[$checkbox] = '';
            }
        }

        // 3. Normalizar datos especiales
        $data['orden_500'] = strtoupper(request()->get('orden_500', 'NO'));
        $data['requiere_servicio'] = request()->get('requiere_servicio') == '1' ? 1 : 0;
        $data['status'] = 'PENDIENTE';

        // 4. Convertir campos vacíos a null solo para campos nullable
        $nullableFields = ['taller', 'kilometraje', 'fecharecep'];
        foreach ($nullableFields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        // 5. Crear la Orden
        try {
            $orden = OrdenVehiculo::create($data);
            //Actualizar estado del vehiculo
            $vehiculo = Vehiculo::where('no_economico', $orden->noeconomico)->first();

            if ($vehiculo) {
                $vehiculo->ordenes_pendientes += 1;
                $vehiculo->en_taller = true;
                $vehiculo->finalizado = false;
                $vehiculo->estado = 'En Mantenimiento';
                $vehiculo->save();
            }

            HistorialOrden::create([
                'orden_vehiculo_id' => $orden->id,
                'tipo_evento' => 'orden_creada',
                'detalles' => 'Orden creada',
                'old_value' => null,
                'new_value' => 'PENDIENTE'
            ]);
            // CORRECCIÓN: Devolvemos JSON, no redirect
            return response()->json([
                'status' => 'success',
                'message' => 'Orden creada correctamente',
                'id' => $orden->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function generar($id)
    {
        // Buscamos la orden recién creada
        $orden = OrdenVehiculo::find($id);

        if (!$orden) {
            return "Error: Orden no encontrada.";
        }

        // Por ahora, solo mostramos un mensaje de éxito
        echo "<h1>¡Éxito!</h1>";
        echo "<p>La orden #{$orden->id} para el vehículo <strong>{$orden->noeconomico}</strong> se guardó correctamente en la Base de Datos.</p>";
        echo "<p>Aquí irá la vista para descargar el PDF.</p>";
        echo "<a href='/ordenvehiculos/create'>Crear otra</a>";
    }
    public function generarOrden($id)
    {
        $orden = OrdenVehiculo::find($id);

        if (!$orden) {
            return response()->json(['status' => 'error', 'message' => 'Orden no encontrada'], 404);
        }

        // 1. Iniciar TBS y OpenTBS
        // En Leaf usamos las clases importadas del namespace global o composer
        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, \clsOpenTBS::class);

        // 2. Rutas (Adaptadas a Leaf/PHP Puro)
        // __DIR__ apunta a app/controllers. Subimos niveles para llegar a public
        $basePath = dirname(__DIR__, 2); // Raíz del proyecto
        $templatePath = $basePath . '/public/plantillas/orden_vehiculo.docx';

        if (!file_exists($templatePath)) {
            return response()->json(['status' => 'error', 'message' => 'Plantilla no encontrada en: ' . $templatePath], 500);
        }

        $TBS->LoadTemplate($templatePath, OPENTBS_ALREADY_UTF8);

        // 3. Asignación de Datos (Idéntico a tu código anterior)
        $TBS->MergeField('pro.ordenq', $orden->id);
        $TBS->MergeField('pro.noorden', $orden->id);
        $TBS->MergeField('pro.area', strtoupper($orden->area));
        $TBS->MergeField('pro.zona', strtoupper($orden->zona));
        $TBS->MergeField('pro.departamento', strtoupper($orden->departamento));
        $TBS->MergeField('pro.noeconomico', $orden->noeconomico);
        $TBS->MergeField('pro.marca', strtoupper($orden->marca));
        $TBS->MergeField('pro.placas', $orden->placas);
        $TBS->MergeField('pro.taller', strtoupper($orden->taller ?? ''));
        $TBS->MergeField('pro.kilometraje', $orden->kilometraje);
        $TBS->MergeField('pro.fecharecep', $orden->fecharecep);

        // Lógica de Radio Buttons (Idéntica)
        $radioOptions = ['radiocom', 'llantaref', 'autoestereo', 'gatoh', 'llavecruz', 'extintor', 'botiquin', 'escalera', 'escalerad'];
        $contador = 1;
        foreach ($radioOptions as $option) {
            $val = $orden->$option ?? 'No'; // Protección contra nulos
            $valorSi = ($val === 'Si') ? ' X' : '';
            $valorNo = ($val === 'No') ? ' X' : '';
            $TBS->MergeField('pro.rs' . $contador, 'Si' . $valorSi);
            $TBS->MergeField('pro.rn' . $contador, 'No' . $valorNo);
            $contador++;
        }

        // Lógica de Gasolina (Rutas corregidas)
        $gasPath = $basePath . '/public/plantillas/gasolina/';
        $gasImg = '';

        // Aseguramos que sea string para el switch
        switch ((string)$orden->gasolina) {
            case '0':
                $gasImg = $gasPath . '0.png';
                break;
            case '25':
                $gasImg = $gasPath . '25.png';
                break;
            case '50':
                $gasImg = $gasPath . '50.png';
                break;
            case '75':
                $gasImg = $gasPath . '75.png';
                break;
            case '100':
                $gasImg = $gasPath . '100.png';
                break;
        }
        $TBS->VarRef['x'] = $gasImg;

        // Checkboxes
        for ($i = 1; $i <= 20; $i++) {
            $field = 'vehicle' . $i;
            $TBS->MergeField('pro.c' . $i, $orden->$field ?? '');
        }

        // Firmas y Observaciones
        $TBS->MergeField('pro.observaciones', strtoupper($orden->observacion));
        $TBS->MergeField('pro.fechafirm', $orden->fechafirm);
        $TBS->MergeField('pro.areausuaria', strtoupper($orden->areausuaria));
        $TBS->MergeField('pro.rpeusuaria', $orden->rpeusuaria);
        $TBS->MergeField('pro.jefedpto', strtoupper($orden->autoriza));
        $TBS->MergeField('pro.rpejefedpto', $orden->rpejefedpt);
        $TBS->MergeField('pro.responsablepv', strtoupper($orden->resppv));
        $TBS->MergeField('pro.responsablepvrpe', $orden->rperesppv);

        $TBS->PlugIn(OPENTBS_DELETE_COMMENTS);

        // 4. Guardar el archivo generado
        $fileName = 'orden_vehiculo_' . $orden->id . '.docx';
        $outputDir = $basePath . '/storage/orden_vehiculos/';

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $docxFilePath = $outputDir . $fileName;
        $TBS->Show(OPENTBS_FILE, $docxFilePath);

        // IMPORTANTE: En lugar de redirect, devolvemos JSON diciendo que ya se generó.
        // AlpineJS en el frontend recibirá esto y mostrará el modal de éxito.
        return response()->json([
            'status' => 'success',
            'message' => 'Documento generado correctamente',
            'orden_id' => $orden->id,
            'file_url' => '/ordenes/descargar-docx/' . $orden->id // Opcional si quieres bajar el docx
        ]);
    }


    public function generatePdf($id)
    {
        $basePath = dirname(__DIR__, 2); // Raíz
        $inputDir = $basePath . '/storage/orden_vehiculos/';
        $outputDir = $basePath . '/storage/pdf_exports/';

        $docxFile = 'orden_vehiculo_' . $id . '.docx';
        $docxPath = $inputDir . $docxFile;

        if (!file_exists($docxPath)) {
            // Intentamos generarlo si no existe
            $this->generarOrden($id);
            if (!file_exists($docxPath)) {
                return response()->json(['status' => 'error', 'message' => 'No se pudo generar el archivo base DOCX'], 404);
            }
        }

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        // 3. Ejecutar LibreOffice (Usando exec nativo de PHP, más rápido que Process de Laravel)
        // Asegúrate de tener LIBREOFFICE_PATH en tu .env
        $sofficePath = getenv('LIBREOFFICE_PATH');

        if (!$sofficePath) {
            // Fallback común en Windows si no está en .env
            $sofficePath = 'C:\Program Files\LibreOffice\program\soffice.exe';
        }

        // Comando Shell
        $command = "\"{$sofficePath}\" --headless --convert-to pdf \"{$docxPath}\" --outdir \"{$outputDir}\"";

        // Ejecutamos
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            $pdfFileName = str_replace('.docx', '.pdf', $docxFile);
            $pdfPath = $outputDir . $pdfFileName;

            if (file_exists($pdfPath)) {
                // Leaf Helper para descargar
                return response()->download($pdfPath, $pdfFileName);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Error al convertir PDF. Verifica la ruta de LibreOffice.',
            'debug' => $output
        ], 500);
    }

    public function edit($id)
    {
        // Get the order to edit
        $orden = OrdenVehiculo::find($id);

        if (!$orden) {
            return response()->json(['status' => 'error', 'message' => 'Orden no encontrada'], 404);
        }

        // Get vehicles data (same as in create method)
        $vehiculos = [
            [
                'no_economico' => '1001',
                'placas' => 'YZA-1234',
                'marca' => 'NISSAN',
                'modelo' => 'NP300'
            ],
            [
                'no_economico' => '2005',
                'placas' => 'YXE-9876',
                'marca' => 'FORD',
                'modelo' => 'RANGER'
            ],
            [
                'no_economico' => '3050',
                'placas' => 'ABC-0000',
                'marca' => 'CHEVROLET',
                'modelo' => 'S10'
            ]
        ];

        // Get users data (same as in create method)
        $users = [
            ['name' => 'JUAN PEREZ LOPEZ', 'usuario' => '98765'],
            ['name' => 'MARIA GONZALEZ', 'usuario' => '12345'],
            ['name' => 'PEDRO PARAM', 'usuario' => '54321'],
            ['name' => 'ADMINISTRADOR SISTEMA', 'usuario' => '11111']
        ];

        // Render the edit view with the order data
        return render('ordenvehiculos.edit', [
            'id' => $orden->id,
            'vehiculos' => $vehiculos,
            'users' => $users,
            'ordenEditar' => $orden
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        // Find the order to update
        $orden = OrdenVehiculo::find($id);

        if (!$orden) {
            return response()->json([
                'status' => 'error',
                'message' => 'Orden no encontrada'
            ], 404);
        }

        // 1. Get all form data
        $data = request()->body();
        // --- PASO DE LIMPIEZA ---
        // Eliminamos la coma (,) antes de cualquier validación o guardado
        if (isset($data['kilometraje'])) {
            $data['kilometraje'] = str_replace(',', '', $data['kilometraje']);
        }
        // ------------------------

        // 2. Handle checkboxes (same as in store method)
        $checkboxes = [
            'vehicle1',
            'vehicle2',
            'vehicle3',
            'vehicle4',
            'vehicle5',
            'vehicle6',
            'vehicle7',
            'vehicle8',
            'vehicle9',
            'vehicle10',
            'vehicle11',
            'vehicle12',
            'vehicle13',
            'vehicle14',
            'vehicle15',
            'vehicle16',
            'vehicle17',
            'vehicle18',
            'vehicle19',
            'vehicle20',
        ];

        foreach ($checkboxes as $checkbox) {
            if (!isset($data[$checkbox])) {
                $data[$checkbox] = '';
            }
        }

        // 3. Normalize special data
        $orden_500 = strtoupper(request()->get('orden_500', 'NO'));
        // Preserve existing value if it's not 'NO' or 'SI' and we're not explicitly setting it to 'SI'
        if (!in_array($orden->orden_500, ['NO', 'SI']) && $orden_500 !== 'NO') {
            $data['orden_500'] = $orden->orden_500;
        } else {
            $data['orden_500'] = $orden_500;
        }
        $data['requiere_servicio'] = request()->get('requiere_servicio') == '1' ? 1 : 0;

        // 4. Convert empty strings to null for nullable fields
        $nullableFields = ['taller', 'kilometraje', 'fecharecep'];
        foreach ($nullableFields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        // 5. Update the order
        try {
            $orden->update($data);
            HistorialOrden::create([
                'orden_vehiculo_id' => $orden->id,
                'tipo_evento' => 'orden_actualizada',
                'detalles' => 'Datos generales actualizados',
                'old_value' => null,
                'new_value' => null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Orden actualizada correctamente',
                'id' => $orden->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateModal($id)
    {
        // Find the order to update
        $orden = OrdenVehiculo::find($id);

        if (!$orden) {
            return response()->json([
                'status' => 'error',
                'message' => 'Orden no encontrada'
            ], 404);
        }

        // 1. Get all form data
        $data = request()->body();
        $kmSalidaInput = $data['kilometraje'] ?? 0;
        $newStatus = $data['status'] ?? null;
        $fechaTerminacion = $data['fechaTerminacion'] ?? $data['fecha_terminacion'] ?? date('Y-m-d');

        $kmSalida = str_replace(',', '', $kmSalidaInput);
        $kmEntrada = (int) $orden->kilometraje;

        $fechaEntrada = $orden->fechafirm;


        // --- PASO DE LIMPIEZA ---
        if ($kmSalida < $kmEntrada) {
            return response()->json([
                'status' => 'error',
                'message' => "El kilometraje de salida no puede ser menor al de entrada (" . number_format($kmEntrada) . ")."
            ], 400);
        }

        if ($fechaTerminacion < $fechaEntrada) {
            return response()->json([
                'status' => 'error',
                'message' => "La fecha de terminación no puede ser menor a la fecha de entrada ({$fechaEntrada})."
            ], 400);
        }

        // 5. Update the order
        try {
            // Capturamos estado anterior
            $oldStatus = $orden->status;
            $orden->update(['status' => $newStatus]);

            if ($newStatus === 'TERMINADO') {
                VehiculoSalidaTaller::create([
                'orden_vehiculo_id' => $orden->id,
                'kilometraje' => $kmSalida,
                'fecha_terminacion' => $fechaTerminacion,
                'servicio' => $orden->requiere_servicio,
            ]);

                // --- LIBERAR VEHÍCULO ---
                $vehiculo = Vehiculo::where('no_economico', $orden->noeconomico)->first();
                
                if ($vehiculo) {
                    if ($vehiculo->ordenes_pendientes > 0) {
                        $vehiculo->ordenes_pendientes -= 1;
                    }

                    if ($vehiculo->ordenes_pendientes === 0) {
                        $vehiculo->en_taller = false;
                        $vehiculo->finalizado = true;
                        $vehiculo->estado = 'En Circulacion';
                    } else{
                        $vehiculo->en_taller = true;
                        $vehiculo->finalizado = false;
                        $vehiculo->estado = 'En Mantenimiento';
                    }
                    $vehiculo->save();
                }
                // -------------------------------
            }

            HistorialOrden::create([
                'orden_vehiculo_id' => $orden->id,
                'tipo_evento' => 'estado_cambiado',
                'detalles' => $newStatus === 'TERMINADO'
                    ? 'Orden finalizada - Kilometraje: ' . $kmSalida
                    : 'Estado actualizado manualmente',
                'old_value' => $oldStatus,
                'new_value' => $newStatus,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Orden actualizada correctamente',
                'id' => $orden->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadScan($id)
    {
        $orden = OrdenVehiculo::find($id);

        if (!$orden) {
            return response()->json(['status' => 'error', 'message' => 'Orden no encontrada'], 404);
        }

        // 1. Validar que se envió un archivo
        // Leaf/PHP maneja los archivos en $_FILES o request()->files()
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            return response()->json(['status' => 'error', 'message' => 'No se ha seleccionado ningún archivo válido.'], 400);
        }

        $file = $_FILES['archivo'];
        $comentarios = request()->get('comentarios', '');

        try {
            // 2. Preparar el directorio de subida
            // Guardaremos en: storage/escaneos/ID_ORDEN/
            $basePath = dirname(__DIR__, 2); // Raíz del proyecto
            $uploadDir = $basePath . '/public/ordenes_escaneos/' . $orden->id . '/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // 3. Generar nombre único y mover el archivo
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = 'escaneo_' . time() . '.' . $extension;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Capturamos estado antes del cambio
                $oldStatus = $orden->status;

                // 4. Actualizar Estado de la Orden a VEHICULO TALLER solo si no está TERMINADO
                if ($oldStatus !== 'TERMINADO') {
                    $orden->status = 'VEHICULO TALLER';
                    $orden->save();
                }

                // 5. Verificar si ya existe un escaneo previo
                $rutaRelativa = '/ordenes_escaneos/' . $orden->id . '/' . $fileName;
                $archivoExistente = OrdenArchivo::where('orden_vehiculo_id', $orden->id) // Asumiendo que hay un campo tipo_archivo
                    ->first();

                if ($archivoExistente) {
                    // Obtener el nombre del archivo anterior para el historial
                    $archivoAnterior = basename($archivoExistente->ruta_archivo);

                    // Eliminar el archivo físico anterior si existe
                    $rutaAnterior = dirname(__DIR__, 2) . '/public/ordenes_escaneos/' . $orden->id . '/' . $archivoAnterior;
                    if (file_exists($rutaAnterior) && is_file($rutaAnterior)) {
                        @unlink($rutaAnterior);
                    }

                    // Actualizar el registro existente
                    $archivoExistente->update([
                        'ruta_archivo' => $rutaRelativa,
                        'comentarios'  => $comentarios,
                        'updated_at'   => date('Y-m-d H:i:s')
                    ]);

                    // Actualizar el historial del archivo
                    $historialExistente = HistorialOrden::where('orden_vehiculo_id', $orden->id)
                        ->where('tipo_evento', 'archivo_subido')
                        ->where('new_value', $archivoAnterior)
                        ->first();

                    if ($historialExistente) {
                        $historialExistente->update([
                            'old_value' => $archivoAnterior,
                            'new_value' => $fileName,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    } else {
                        // Si por alguna razón no existe el historial, lo creamos
                        HistorialOrden::create([
                            'orden_vehiculo_id' => $orden->id,
                            'tipo_evento' => 'archivo_subido',
                            'detalles' => 'ESCANEO ACTUALIZADO',
                            'old_value' => $archivoAnterior,
                            'new_value' => $fileName,
                        ]);
                    }

                    $mensaje = 'Escaneo actualizado correctamente.';
                } else {
                    // Crear nuevo registro si no existe uno previo
                    OrdenArchivo::create([
                        'orden_vehiculo_id' => $orden->id,
                        'ruta_archivo'      => $rutaRelativa,
                        'comentarios'       => $comentarios,
                        'tipo_archivo'      => 'escaneo' // Asegurarse de establecer el tipo de archivo
                    ]);

                    // Registrar en el historial
                    HistorialOrden::create([
                        'orden_vehiculo_id' => $orden->id,
                        'tipo_evento' => 'archivo_subido',
                        'detalles' => 'ESCANEO SUBIDO',
                        'old_value' => null,
                        'new_value' => $fileName,
                    ]);

                    $mensaje = 'Escaneo subido correctamente.';
                }

                // Registrar cambio de estado si es necesario
                if ($oldStatus !== 'VEHICULO TALLER' && $oldStatus !== 'TERMINADO') {
                    HistorialOrden::create([
                        'orden_vehiculo_id' => $orden->id,
                        'tipo_evento' => 'estado_cambiado',
                        'detalles' => 'Estado actualizado por subida de escaneo',
                        'old_value' => $oldStatus,
                        'new_value' => 'VEHICULO TALLER',
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => $mensaje
                ]);
            } else {
                throw new \Exception("Error al mover el archivo al servidor.");
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateCode500($id)
    {
        // 1. Buscar la orden
        $orden = OrdenVehiculo::find($id);

        if (!$orden) {
            return response()->json([
                'status' => 'error',
                'message' => 'Orden no encontrada'
            ], 404);
        }

        try {
            // 2. Obtener solo el dato que nos interesa
            // Si no se envía nada, asumimos string vacío o 'NO'
            $nuevoCodigo = request()->get('orden_500');

            // 3. Actualizar UNICAMENTE ese campo
            $orden->update([
                'orden_500' => $nuevoCodigo
            ]);
            // --- NUEVO: REGISTRO HISTORIAL ---
            HistorialOrden::create([
                'orden_vehiculo_id' => $orden->id,
                'tipo_evento' => 'orden_500',
                'detalles' => 'Numero: ' . $nuevoCodigo,
                'old_value' => null, // Podrías poner el valor anterior si quisieras
                'new_value' => $nuevoCodigo
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Código agregado correctamente',
                'id' => $orden->id,
                'orden_500' => $nuevoCodigo // Devolvemos el dato para actualizar la vista si fuera necesario
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $orden = OrdenVehiculo::find($id);

        if (!$orden) {
            return response()->json([
                'status' => 'error',
                'message' => 'Orden no encontrada'
            ], 404);
        }

        try {
            if ($orden->status !== 'TERMINADO') {
                $vehiculo = Vehiculo::where('no_economico', $orden->noeconomico)->first();
                if ($vehiculo && $vehiculo->ordenes_pendientes > 0) {
                    $vehiculo->ordenes_pendientes -= 1;

                    if ($vehiculo->ordenes_pendientes === 0) {
                        $vehiculo->en_taller = false;
                        $vehiculo->finalizado = true;
                        $vehiculo->estado = 'En Circulacion';
                    }
                    $vehiculo->save();
                }
            }
            // Opcional: Si tienes configurada la relación en el modelo, 
            // podrías necesitar borrar archivos relacionados primero o confiar en el "cascade" de la BD.
            // Por ahora, hacemos un delete simple:
            $orden->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Orden eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
    // Obtener historial de una orden específica
    public function history($id)
    {
        $historial = HistorialOrden::where('orden_vehiculo_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        $archivos = OrdenArchivo::where('orden_vehiculo_id', $id)->get();

        $historial->transform(function ($evento) use ($archivos) {
            if ($evento->tipo_evento === 'archivo_subido' && $evento->new_value) {

                // Buscamos el archivo que coincida con el nombre guardado en 'new_value'
                // new_value tiene el nombre del archivo (ej: escaneo_123.pdf)
                $archivoEncontrado = $archivos->first(function ($archivo) use ($evento) {
                    // Usamos str_contains o str_ends_with porque ruta_archivo tiene la ruta completa
                    return strpos($archivo->ruta_archivo, $evento->new_value) !== false;
                });

                if ($archivoEncontrado) {
                    // Creamos una propiedad "virtual" para el JSON
                    $evento->comentario_archivo = $archivoEncontrado->comentarios;
                }
            }
            return $evento;
        });

        return response()->json($historial);
    }
}
