<?php

namespace App\Controllers;

// Importamos el modelo que acabamos de crear
use App\Models\OrdenVehiculo;
use App\Models\Vehiculo;
use App\Models\VehiculoSalidaTaller;
use App\Models\OrdenArchivo;
use App\Models\HistorialOrden;
use App\Models\OrdenVehiculoArren;
use App\Models\OrdenVehiculoPropio;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Models\User;
use clsTinyButStrong;
use Dompdf\Dompdf;
use Dompdf\Options;
use Carbon\Carbon;

class OrdenVehiculoController extends Controller
{
    // 1. Carga la vista inicial
    public function index()
    {
        $citas = OrdenVehiculo:: with('detalleArrendado')->where('status', 'CITA ASIGNADA')->get();
        // Recorremos las citas para agregarles el texto dinámico
        $citas->map(function ($cita) {
        if ($cita->detalleArrendado && $cita->detalleArrendado->fecha_cita) {
            
            // Convertimos las fechas a inicio de día para que el cálculo sea exacto (sin importar la hora)
            $fechaCita = Carbon::parse($cita->detalleArrendado->fecha_cita)->startOfDay();
            $hoy = Carbon::now()->startOfDay();

            // Calculamos la diferencia en días (el 'false' es para que nos dé números negativos si la cita ya pasó)
            $diferenciaDias = $hoy->diffInDays($fechaCita, false);

            if ($diferenciaDias == 0) {
                $cita->texto_fecha = 'Hoy,';
            } elseif ($diferenciaDias == 1) {
                $cita->texto_fecha = 'Mañana,';
            } elseif ($diferenciaDias > 1) {
                $cita->texto_fecha = 'En ' . $diferenciaDias . ' días,';
            } elseif ($diferenciaDias == -1) {
                $cita->texto_fecha = 'Ayer,';
            } elseif ($diferenciaDias < -1) {
                // Por si se les olvidó cambiar el estatus y la cita fue hace una semana
                $cita->texto_fecha = 'Hace ' . abs($diferenciaDias) . ' días,'; 
            }
        } else {
            $cita->texto_fecha = '';
        }
        
        return $cita;
    });

        // Renderizamos la vista vacía, AlpineJS cargará los datos al iniciar
        render('ordenvehiculos.index',[
            'citas' => $citas,
        ]);
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
            ->with(['archivo', 'detallePropio', 'detalleArrendado', 'vehiculo' => function ($q) {
                $q->select('no_economico', 'departamento', 'ubicacion');
            }])
            ->select(
                'id',
                'tipo_vehiculo',
                'noeconomico',
                'status',
                'orden_500',
            );
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

        $vehiculosRaw = Vehiculo::where('propiedad', 'Propio')->get();

        $vehiculos = $vehiculosRaw->map(function ($v) {
            // Calculamos el KM para CADA vehículo de la lista
            $k = $v->ultimoKilometraje();

            return [
                'no_economico' => $v->no_economico,
                'placas'       => $v->placas,
                'marca'        => $v->marca,
                'modelo'       => $v->modelo,
                'ultimo_km'    => $k['kilometraje'] ?? 0
            ];
        });

        $users = User::select('name', 'user')->get();

        // 4. Enviamos todo a la vista
        // 'ordenEditar' va null porque estamos CREANDO
        render('ordenvehiculos.create', [
            'id' => $nextId,
            'vehiculos' => $vehiculos,
            'users' => $users,
            'ordenEditar' => null,
            // NUEVAS VARIABLES
            'preseleccionado' => $preseleccionado,
            'ultimoKm' => $ultimoKm,
            'returnUrl' => $returnUrl ?: '/ordenvehiculos'
        ]);
    }

    public function create_arrendado()
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
                    'modelo'       => $vehiculo->modelo,
                    'no_serie'     => $vehiculo->serie
                ];
            }
        }
        // Obtener el siguiente ID disponible
        $nextId = OrdenVehiculo::max('id') + 1;
        $nextId = $nextId ?: 1; // Si no hay registros, comenzar desde 1

        $vehiculosRaw = Vehiculo::where('propiedad', 'Arrendado')->get();

        $vehiculos = $vehiculosRaw->map(function ($v) {
            // Calculamos el KM para CADA vehículo de la lista
            $k = $v->ultimoKilometraje();

            return [
                'no_economico' => $v->no_economico,
                'placas'       => $v->placas,
                'marca'        => $v->marca,
                'modelo'       => $v->modelo,
                'serie'        => $v->serie,
                'ultimo_km'    => $k['kilometraje'] ?? 0
            ];
        });

        // 4. Enviamos todo a la vista
        // 'ordenEditar' va null porque estamos CREANDO
        render('ordenvehiculos.create_arrendado', [
            'id' => $nextId,
            'vehiculos' => $vehiculos,
            'ordenEditar' => null,
            // NUEVAS VARIABLES
            'preseleccionado' => $preseleccionado,
            'ultimoKm' => $ultimoKm,
            'returnUrl' => $returnUrl ?: '/ordenvehiculos'
        ]);
    }

    public function store()
    {
        // 1. Obtener todos los datos
        $data = request()->body();
        // --- PASO DE LIMPIEZA ---
        // Eliminamos la coma (,) antes de cualquier validación o guardado
        if (isset($data['kilometraje'])) {
            $data['kilometraje'] = str_replace(',', '', $data['kilometraje']);
        }
        // ------------------------

        // 2. Manejo de Checkboxes (Si no están marcados, no llegan en el request)
        $checkboxes = [];
        for ($i = 1; $i <= 20; $i++) {
            $checkboxes[] = 'vehicle' . $i;
        }

        foreach ($checkboxes as $checkbox) {
            if (!isset($data[$checkbox])) {
                $data[$checkbox] = '';
            }
        }

        // 3. Normalizar datos especiales
        $orden_500 = strtoupper(request()->get('orden_500', 'NO'));
        $requiere_servicio = request()->get('requiere_servicio') == '1' ? 1 : 0;
        $status = 'PENDIENTE';

        // 4. Convertir campos vacíos a null solo para campos nullable
        $nullableFields = ['taller', 'kilometraje', 'fecharecep'];
        foreach ($nullableFields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        $ordenCreada = null;

        // 5. Crear la Orden
        try {
            db()->transaction(function () use ($data, $orden_500, $requiere_servicio, $status, &$ordenCreada) {
                // A. DATOS PARA LA TABLA PADRE (OrdenVehiculo)
                $commonData = [
                    'tipo_vehiculo'     => 'propio', // Identificador clave
                    'noeconomico'       => $data['noeconomico'],
                    'marca'             => $data['marca'],
                    'placas'            => $data['placas'],
                    'kilometraje'       => $data['kilometraje'],
                    'orden_500'         => $orden_500,
                    'requiere_servicio' => $requiere_servicio,
                    'status'            => $status,
                ];
                $ordenCreada = OrdenVehiculo::create($commonData);

                // B. DATOS PARA LA TABLA HIJA (OrdenVehiculoPropio)
                // Mapeamos manualmente o usamos el array $data si los nombres coinciden con el $fillable
                $specificData = [
                    'id_orden'      => $ordenCreada->id, // Relación FK
                    'area'          => $data['area'] ?? null,
                    'zona'          => $data['zona'] ?? null,
                    'departamento'  => $data['departamento'] ?? null,
                    'taller'        => $data['taller'] ?? null,
                    'fecharecep'    => $data['fecharecep'] ?? null,
                    'gasolina'      => $data['gasolina'] ?? 0,

                    // Checklist de Accesorios (Radios)
                    'radiocom'      => $data['radiocom'] ?? 'No',
                    'llantaref'     => $data['llantaref'] ?? 'No',
                    'autoestereo'   => $data['autoestereo'] ?? 'No',
                    'gatoh'         => $data['gatoh'] ?? 'No',
                    'llavecruz'     => $data['llavecruz'] ?? 'No',
                    'extintor'      => $data['extintor'] ?? 'No',
                    'botiquin'      => $data['botiquin'] ?? 'No',
                    'escalera'      => $data['escalera'] ?? 'No',
                    'escalerad'     => $data['escalerad'] ?? 'No',

                    // Firmas y Responsables
                    'observacion'   => $data['observacion'] ?? '',
                    'fechafirm'     => $data['fechafirm'] ?? date('Y-m-d'),
                    'areausuaria'   => $data['areausuaria'] ?? '',
                    'rpeusuaria'    => $data['rpeusuaria'] ?? '',
                    'autoriza'      => $data['autoriza'] ?? '', // Jefe Depto
                    'rpejefedpt'    => $data['rpejefedpt'] ?? '',
                    'resppv'        => $data['resppv'] ?? '',
                    'rperesppv'     => $data['rperesppv'] ?? '',
                ];

                for ($i = 1; $i <= 20; $i++) {
                    $key = 'vehicle' . $i;
                    $specificData[$key] = $data[$key] ?? '';
                }

                // Creamos el detalle
                OrdenVehiculoPropio::create($specificData);

                return true;
            });

            // --- VERIFICACIÓN Y POST-PROCESO ---

            if (!$ordenCreada || !is_object($ordenCreada)) {
                throw new \Exception("La orden propia no se pudo instanciar correctamente.");
            }

            //Actualizar estado del vehiculo
            $vehiculo = Vehiculo::where('no_economico', $ordenCreada->noeconomico)->first();

            if ($vehiculo) {
                $vehiculo->ordenes_pendientes += 1;
                $vehiculo->en_taller = true;
                $vehiculo->finalizado = false;
                $vehiculo->estado = 'En Mantenimiento';
                $vehiculo->save();
            }

            HistorialOrden::create([
                'orden_vehiculo_id' => $ordenCreada->id,
                'tipo_evento' => 'orden_creada',
                'detalles' => 'Orden creada',
                'old_value' => null,
                'new_value' => 'PENDIENTE'
            ]);
            // CORRECCIÓN: Devolvemos JSON, no redirect
            return response()->json([
                'status' => 'success',
                'message' => 'Orden creada correctamente',
                'id' => $ordenCreada->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store_arrendado()
    {
        // 1. Obtener datos y archivos
        $data = request()->body();
        $files = $_FILES;

        // --- LIMPIEZA Y NORMALIZACIÓN ---
        if (isset($data['kilometraje'])) {
            $data['kilometraje'] = str_replace(',', '', $data['kilometraje']);
        }

        // Ajuste de variables simples
        $requiere_servicio = request()->get('requiere_servicio') == '1' ? 1 : 0;
        $status = 'PENDIENTE';

        // --- VALIDACIÓN DE ARCHIVOS (Igual que tu código original) ---
        $hasUploadedFile = false;
        foreach ($files as $fieldName => $file) {
            if (is_array($file) && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
                $hasUploadedFile = true;
                break;
            }
        }

        if (!$hasUploadedFile) {
            return response()->json(['status' => 'error', "message" => 'No has subido ninguna foto'], 400);
        }

        $requiredPhotos = [
            'foto_circulacion' => 'tarjeta de circulacion',
            'foto_odometro'    => 'odometro',
        ];

        foreach ($requiredPhotos as $inputName => $userFriendlyName) {
            if (!isset($files[$inputName]) || $files[$inputName]['error'] !== UPLOAD_ERR_OK) {
                return response()->json(['status' => 'error', 'message' => "La foto $userFriendlyName es obligatoria."], 400);
            }
        }

        $ordenCreada = null;
        // --- AQUÍ EMPIEZA LA MAGIA DE LOS ARRAYS ---
        try {
            $resultado = db()->transaction(function () use ($data, $files, $requiere_servicio, $status, &$ordenCreada) {

                // 1. Array para la TABLA PADRE (Datos Comunes)
                $commonData = [
                    'tipo_vehiculo'     => 'arrendado',
                    'noeconomico'       => $data['noeconomico'],
                    'marca'             => $data['marca'],
                    'placas'            => $data['placas'],
                    'kilometraje'       => $data['kilometraje'] ?? null,
                    'orden_500'         => 'NO',
                    'requiere_servicio' => $requiere_servicio,
                    'status'            => $status,
                ];

                // CREAMOS EL PADRE PRIMERO
                $ordenCreada = OrdenVehiculo::create($commonData);


                // 2. Array para la TABLA HIJA (Datos Específicos)
                // Nota: Aquí ya incluimos el ID generado arriba
                $specificData = [
                    'id_orden'            => $ordenCreada->id,
                    'mun_estado_origen'   => $data['mun_estado_origen'],
                    'mun_estado_servicio' => $data['mun_estado_servicio'],
                    'no_serie'            => $data['no_serie'],
                    'tipo_servicio'       => $data['tipo_servicio'],
                    'fecha_gen'           => $data['fecha_gen'],
                ];

                $imageMap = [
                    'foto_circulacion'    => 'circulacion',
                    'foto_odometro'       => 'odometro',
                    'foto_llanta_del_pil' => 'del_pil',
                    'foto_llanta_del_cop' => 'del_cop',
                    'foto_llanta_tra_pil' => 'tra_pil',
                    'foto_llanta_tra_cop' => 'tra_cop'
                ];

                foreach ($imageMap as $inputName => $suffix) {
                    if (isset($files[$inputName]) && $files[$inputName]['error'] === UPLOAD_ERR_OK) {
                        $path = $this->storeImage($files[$inputName], $data['noeconomico'], $suffix);
                        if ($path) {
                            // Agregamos la ruta al array específico
                            $specificData[$inputName] = $path;
                        }
                    }
                }

                // CREAMOS EL HIJO
                OrdenVehiculoArren::create($specificData);

                return true;
            });
            // Verificamos que el objeto se haya asignado correctamente mediante la referencia
            if (!$ordenCreada || !is_object($ordenCreada)) {
                throw new \Exception("La orden no se pudo instanciar correctamente.");
            }

            // Actualizar vehículo
            $vehiculo = Vehiculo::where('no_economico', $ordenCreada->noeconomico)->first();
            if ($vehiculo) {
                $vehiculo->ordenes_pendientes += 1;
                $vehiculo->en_taller = true;
                $vehiculo->finalizado = false;
                $vehiculo->estado = 'En Mantenimiento';
                $vehiculo->save();
            }

            // Historial
            HistorialOrden::create([
                'orden_vehiculo_id' => $ordenCreada->id,
                'tipo_evento'       => 'orden_creada',
                'detalles'          => 'Orden creada',
                'old_value'         => null,
                'new_value'         => 'PENDIENTE'
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Orden creada correctamente',
                'id'      => $ordenCreada->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
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
        // Definir ruta: public/orden_vehiculos_arrendados/NUM_ECO/FECHA/
        $dateFolder = date('Y-m-d');
        // __DIR__ es app/controllers, subimos a la raiz y entramos a public
        $basePath = dirname(__DIR__, 2) . '/public';
        $relativePath = "/orden_vehiculos_arrendados/{$no_eco}/{$dateFolder}";
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

    public function generarOrden($id)
    {
        // Llamamos a la lógica interna
        $resultado = $this->_crearArchivoDocx($id);

        if (!$resultado) {
            return response()->json(['status' => 'error', 'message' => 'Orden no encontrada o error al generar'], 404);
        }

        // Respondemos JSON
        return response()->json([
            'status' => 'success',
            'message' => 'Documento generado correctamente',
            'orden_id' => $id,
            'file_url' => '/ordenes/descargar-docx/' . $id
        ]);
    }

    /**
     * Función interna auxiliar: Solo genera el archivo físico, NO retorna respuesta HTTP.
     */
    private function _crearArchivoDocx($id)
    {
        $orden = OrdenVehiculo::with('detallePropio')->find($id);

        if (!$orden || $orden->tipo_vehiculo !== 'propio' || !$orden->detallePropio) {
            return false;
        }

        $detalle = $orden->detallePropio;

        // 1. Iniciar TBS y OpenTBS
        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, \clsOpenTBS::class);

        // 2. Rutas
        $basePath = dirname(__DIR__, 2);
        $templatePath = $basePath . '/public/plantillas/orden_vehiculo.docx';

        if (!file_exists($templatePath)) {
            // Puedes lanzar excepción o loguear error
            return false;
        }

        $TBS->LoadTemplate($templatePath, OPENTBS_ALREADY_UTF8);

        // 3. Asignación de Datos (Copiado de tu lógica original)
        $TBS->MergeField('pro.ordenq', $orden->id);
        $TBS->MergeField('pro.noorden', $orden->id);
        $TBS->MergeField('pro.area', strtoupper($detalle->area));
        $TBS->MergeField('pro.zona', strtoupper($detalle->zona));
        $TBS->MergeField('pro.departamento', strtoupper($detalle->departamento));
        $TBS->MergeField('pro.noeconomico', $orden->noeconomico);
        $TBS->MergeField('pro.marca', strtoupper($orden->marca));
        $TBS->MergeField('pro.placas', $orden->placas);
        $TBS->MergeField('pro.taller', strtoupper($detalle->taller ?? ''));
        $TBS->MergeField('pro.kilometraje', $orden->kilometraje);
        $TBS->MergeField('pro.fecharecep', $detalle->fecharecep);

        // Radio Buttons
        $radioOptions = ['radiocom', 'llantaref', 'autoestereo', 'gatoh', 'llavecruz', 'extintor', 'botiquin', 'escalera', 'escalerad'];
        $contador = 1;
        foreach ($radioOptions as $option) {
            $val = $detalle->$option ?? 'No';
            $valorSi = ($val === 'Si') ? ' X' : '';
            $valorNo = ($val === 'No') ? ' X' : '';
            $TBS->MergeField('pro.rs' . $contador, 'Si' . $valorSi);
            $TBS->MergeField('pro.rn' . $contador, 'No' . $valorNo);
            $contador++;
        }

        // Gasolina
        $gasPath = $basePath . '/public/plantillas/gasolina/';
        $gasImg = '';
        switch ((string)$detalle->gasolina) {
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
            $TBS->MergeField('pro.c' . $i, $detalle->$field ?? '');
        }

        // Firmas
        $TBS->MergeField('pro.observaciones', strtoupper($detalle->observacion));
        $TBS->MergeField('pro.fechafirm', $detalle->fechafirm);
        $TBS->MergeField('pro.areausuaria', strtoupper($detalle->areausuaria));
        $TBS->MergeField('pro.rpeusuaria', $detalle->rpeusuaria);
        $TBS->MergeField('pro.jefedpto', strtoupper($detalle->autoriza));
        $TBS->MergeField('pro.rpejefedpto', $detalle->rpejefedpt);
        $TBS->MergeField('pro.responsablepv', strtoupper($detalle->resppv));
        $TBS->MergeField('pro.responsablepvrpe', $detalle->rperesppv);

        $TBS->PlugIn(OPENTBS_DELETE_COMMENTS);

        // 4. Guardar archivo
        $fileName = 'orden_vehiculo_' . $orden->id . '.docx';
        $outputDir = $basePath . '/storage/orden_vehiculos/';

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $docxFilePath = $outputDir . $fileName;
        $TBS->Show(OPENTBS_FILE, $docxFilePath);

        return true; // Solo indica éxito, no imprime nada
    }

    public function generatePdf($id)
    {
        $basePath = dirname(__DIR__, 2);
        $inputDir = $basePath . '/storage/orden_vehiculos/';
        $outputDir = $basePath . '/storage/pdf_exports/';

        $docxFile = 'orden_vehiculo_' . $id . '.docx';
        $docxPath = $inputDir . $docxFile;

        // CORRECCIÓN AQUÍ:
        if (!file_exists($docxPath)) {
            // Usamos la función PRIVADA que no retorna JSON ni imprime nada
            $creado = $this->_crearArchivoDocx($id);

            if (!$creado || !file_exists($docxPath)) {
                return response()->json(['status' => 'error', 'message' => 'No se pudo generar el archivo base DOCX'], 404);
            }
        }

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        // ... El resto de tu lógica de LibreOffice se mantiene igual ...
        $sofficePath = getenv('LIBREOFFICE_PATH');
        if (!$sofficePath) {
            $sofficePath = 'C:\Program Files\LibreOffice\program\soffice.exe';
        }

        $command = "\"{$sofficePath}\" --headless --convert-to pdf \"{$docxPath}\" --outdir \"{$outputDir}\"";
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            $pdfFileName = str_replace('.docx', '.pdf', $docxFile);
            $pdfPath = $outputDir . $pdfFileName;

            if (file_exists($pdfPath)) {
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
        $orden = OrdenVehiculo::with('detallePropio')->find($id);

        if (!$orden || $orden->tipo_vehiculo !== 'propio') {
            return response()->json(['status' => 'error', 'message' => 'Orden no encontrada'], 404);
        }

        // Get vehicles data (same as in create method)
        $vehiculosRaw = Vehiculo::where('propiedad', 'Propio (CFE)')->get();
        $vehiculos = $vehiculosRaw->map(function ($v) {
            $k = $v->ultimoKilometraje();
            return [
                'no_economico' => $v->no_economico,
                'placas'       => $v->placas,
                'marca'        => $v->marca,
                'modelo'       => $v->modelo,
                'ultimo_km'    => $k['kilometraje'] ?? 0
            ];
        });

        // Get users data (same as in create method)
        $users = User::select('name', 'user')->get();

        $returnUrl = request()->get('return_url');
        // Render the edit view with the order data
        return render('ordenvehiculos.edit', [
            'id' => $orden->id,
            'vehiculos' => $vehiculos,
            'users' => $users,
            'ordenEditar' => $orden,
            'returnUrl' => $returnUrl ?: '/ordenvehiculos'
        ]);
    }

    public function edit_arrendado($id)
    {
        // 1. Buscamos la orden con sus detalles de arrendamiento
        $orden = OrdenVehiculo::with('detalleArrendado')->find($id);

        // 2. Validación de seguridad
        if (!$orden || $orden->tipo_vehiculo !== 'arrendado') {
            return response()->json(['status' => 'error', 'message' => 'Orden de arrendamiento no encontrada'], 404);
        }

        // 3. Obtener lista de vehículos arrendados (Igual que en create_arrendado)
        $vehiculosRaw = Vehiculo::where('propiedad', 'Arrendado')->get();

        $vehiculos = $vehiculosRaw->map(function ($v) {
            $k = $v->ultimoKilometraje();
            return [
                'no_economico' => $v->no_economico,
                'placas'       => $v->placas,
                'marca'        => $v->marca,
                'modelo'       => $v->modelo,
                'ultimo_km'    => $k['kilometraje'] ?? 0
            ];
        });

        $returnUrl = request()->get('return_url');

        // 4. Renderizar la vista de edición
        // Nota: Asumo que crearás una vista 'ordenvehiculos.edit_arrendado' 
        // o reutilizarás 'ordenvehiculos.create_arrendado' adaptándola.
        return render('ordenvehiculos.edit_arrendado', [
            'id' => $orden->id,
            'vehiculos' => $vehiculos,
            'ordenEditar' => $orden, // El objeto ya lleva adentro 'detalleArrendado'
            'returnUrl' => $returnUrl ?: '/ordenvehiculos'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        // Find the order to update
        $orden = OrdenVehiculo::with('detallePropio')->find($id);

        if (!$orden || $orden->tipo_vehiculo !== 'propio') {
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
        $checkboxes = [];
        for ($i = 1; $i <= 20; $i++) {
            $key = 'vehicle' . $i;
            // Si no viene en el request, asumimos que se desmarcó ('')
            if (!isset($data[$key])) {
                $data[$key] = '';
            }
        }

        // 3. Normalize special data
        $orden_500 = strtoupper($data['orden_500'] ?? 'NO');
        $requiere_servicio = ($data['requiere_servicio'] ?? 0) == '1' ? 1 : 0;

        // 4. Convert empty strings to null for nullable fields
        $nullableFields = ['taller', 'kilometraje', 'fecharecep'];
        foreach ($nullableFields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        // 5. Update the order
        try {
            db()->transaction(function () use ($orden, $data, $orden_500, $requiere_servicio) {
                // A. ACTUALIZAR TABLA PADRE (OrdenVehiculo)
                // Solo actualizamos los campos generales
                $orden->update([
                    'noeconomico'       => $data['noeconomico'],
                    'marca'             => $data['marca'],
                    'placas'            => $data['placas'],
                    'kilometraje'       => $data['kilometraje'],
                    'orden_500'         => $orden_500,
                    'requiere_servicio' => $requiere_servicio,
                    // 'status' no lo tocamos aquí, eso se hace en updateModal
                ]);

                // B. ACTUALIZAR TABLA HIJA (OrdenVehiculoPropio)
                // Preparamos el array de datos específicos
                $specificData = [
                    'area'          => $data['area'] ?? null,
                    'zona'          => $data['zona'] ?? null,
                    'departamento'  => $data['departamento'] ?? null,
                    'taller'        => $data['taller'] ?? null,
                    'fecharecep'    => $data['fecharecep'] ?? null,
                    'gasolina'      => $data['gasolina'] ?? 0, // Slider

                    // Inventario (Radios)
                    'radiocom'      => $data['radiocom'] ?? 'No',
                    'llantaref'     => $data['llantaref'] ?? 'No',
                    'autoestereo'   => $data['autoestereo'] ?? 'No',
                    'gatoh'         => $data['gatoh'] ?? 'No',
                    'llavecruz'     => $data['llavecruz'] ?? 'No',
                    'extintor'      => $data['extintor'] ?? 'No',
                    'botiquin'      => $data['botiquin'] ?? 'No',
                    'escalera'      => $data['escalera'] ?? 'No',
                    'escalerad'     => $data['escalerad'] ?? 'No',

                    // Textos y Firmas
                    'observacion'   => $data['observacion'] ?? '',
                    'fechafirm'     => $data['fechafirm'] ?? null,
                    'areausuaria'   => $data['areausuaria'] ?? '',
                    'rpeusuaria'    => $data['rpeusuaria'] ?? '',
                    'autoriza'      => $data['autoriza'] ?? '',
                    'rpejefedpt'    => $data['rpejefedpt'] ?? '',
                    'resppv'        => $data['resppv'] ?? '',
                    'rperesppv'     => $data['rperesppv'] ?? '',
                ];

                // Agregamos los checkboxes de reparaciones al array
                for ($i = 1; $i <= 20; $i++) {
                    $key = 'vehicle' . $i;
                    $specificData[$key] = $data[$key];
                }

                // Actualizamos la relación. 
                // updateOrCreate es útil por si, por error, no existía el registro hijo (migración de datos viejos)
                $orden->detallePropio()->updateOrCreate(
                    ['id_orden' => $orden->id],
                    $specificData
                );
            });
            $basePath = dirname(__DIR__, 2);
            $oldFile = $basePath . '/storage/orden_vehiculos/orden_vehiculo_' . $orden->id . '.docx';

            if (file_exists($oldFile)) {
                @unlink($oldFile);
            }

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

    public function update_arrendado($id)
    {
        // 1. Buscar la orden
        $orden = OrdenVehiculo::with('detalleArrendado')->find($id);

        if (!$orden || $orden->tipo_vehiculo !== 'arrendado') {
            return response()->json(['status' => 'error', 'message' => 'Orden de arrendamiento no encontrada'], 404);
        }

        $data = request()->body();
        $files = $_FILES; // Aquí SÍ llegarán los archivos porque usamos POST

        // Limpieza KM
        if (isset($data['kilometraje'])) {
            $data['kilometraje'] = str_replace(',', '', $data['kilometraje']);
        }

        $orden_500 = strtoupper(request()->get('orden_500', 'NO'));
        $requiere_servicio = request()->get('requiere_servicio') == '1' ? 1 : 0;

        try {
            db()->transaction(function () use ($orden, $data, $files, $orden_500, $requiere_servicio) {
                
                // A. Actualizar TABLA PADRE
                $orden->update([
                    'noeconomico'       => $data['noeconomico'],
                    'marca'             => $data['marca'],
                    'placas'            => $data['placas'],
                    'kilometraje'       => $data['kilometraje'],
                    'orden_500'         => $orden_500,
                    'requiere_servicio' => $requiere_servicio,
                ]);

                // B. Preparar datos para TABLA HIJA
                $specificData = [
                    'mun_estado_origen'   => $data['mun_estado_origen'],
                    'mun_estado_servicio' => $data['mun_estado_servicio'],
                    'no_serie'            => $data['no_serie'],
                    'tipo_servicio'       => $data['tipo_servicio'],
                    'fecha_gen'           => $data['fecha_gen'] ?? date('Y-m-d'),
                ];

                // C. Manejo de Imágenes (Solo actualizar si se subió una nueva)
                $imageMap = [
                    'foto_circulacion'    => 'circulacion',
                    'foto_odometro'       => 'odometro',
                    'foto_llanta_del_pil' => 'del_pil',
                    'foto_llanta_del_cop' => 'del_cop',
                    'foto_llanta_tra_pil' => 'tra_pil',
                    'foto_llanta_tra_cop' => 'tra_cop'
                ];

                $basePath = dirname(__DIR__, 2) . '/public';

                foreach ($imageMap as $inputName => $suffix) {
                    // Verificamos si hay un archivo nuevo subido sin errores
                    if (isset($files[$inputName]) && $files[$inputName]['error'] === UPLOAD_ERR_OK) {
                        
                        // 1. Guardar nueva imagen
                        $newPath = $this->storeImage($files[$inputName], $data['noeconomico'], $suffix);
                        
                        if ($newPath) {
                            $specificData[$inputName] = $newPath;

                            // 2. Borrar imagen antigua para ahorrar espacio (Opcional pero recomendado)
                            $oldPathRel = $orden->detalleArrendado->$inputName ?? null;
                            if ($oldPathRel) {
                                $oldPathAbs = $basePath . $oldPathRel;
                                if (file_exists($oldPathAbs)) {
                                    @unlink($oldPathAbs);
                                }
                            }
                        }
                    }
                }

                // Actualizar o Crear el detalle
                $orden->detalleArrendado()->updateOrCreate(
                    ['id_orden' => $orden->id],
                    $specificData
                );
            });

            // Registro en historial
            HistorialOrden::create([
                'orden_vehiculo_id' => $orden->id,
                'tipo_evento'       => 'orden_actualizada',
                'detalles'          => 'Datos generales actualizados',
                'old_value'         => null,
                'new_value'         => null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Orden actualizada correctamente',
                'id' => $orden->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateModal($id)
    {
        // Find the order to update
        $orden = OrdenVehiculo::with(['detallePropio', 'detalleArrendado'])->find($id);

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

        $fechaEntrada = date('Y-m-d');
        if ($orden->tipo_vehiculo === 'propio') {
            // Buscamos en el detalle propio
            $fechaEntrada = $orden->detallePropio->fechafirm ?? null;
        } elseif ($orden->tipo_vehiculo === 'arrendado') {
            // Buscamos en el detalle arrendado (allí se llama fecha_gen)
            $fechaEntrada = $orden->detalleArrendado->fecha_gen ?? null;
        }

        // Si por alguna razón sigue siendo null, usamos la fecha de creación del registro padre
        if (!$fechaEntrada) {
            $fechaEntrada = $orden->created_at ? $orden->created_at->format('Y-m-d') : date('Y-m-d');
        }


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
                    } else {
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

                // 4. Actualizar Estado según el tipo de vehículo
                if ($oldStatus === 'PENDIENTE') {
                    
                    // Definimos el nuevo estado dinámicamente
                    $nuevoEstado = ($orden->tipo_vehiculo === 'arrendado') 
                        ? 'ENVIADO A PV' 
                        : 'VEHICULO TALLER';

                    // Actualizamos la orden
                    $orden->status = $nuevoEstado;
                    $orden->save();

                    // Guardamos en historial USANDO LA VARIABLE $nuevoEstado
                    HistorialOrden::create([
                        'orden_vehiculo_id' => $orden->id,
                        'tipo_evento'       => 'estado_cambiado',
                        'detalles'          => 'Estado actualizado por subida de escaneo inicial',
                        'old_value'         => $oldStatus,
                        'new_value'         => $nuevoEstado, // <--- Aquí se arregla el error visual
                    ]);
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
                        'tipo_archivo'      => 'escaneo'
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

                return response()->json([
                    'status' => 'success',
                    'message' => $mensaje
                ]);
            } else {
                throw new \Exception("Error al mover el archivo al directorio de destino.");
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
                'orden_500' => $nuevoCodigo
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
        $orden = OrdenVehiculo::with(['detalleArrendado', 'detallePropio', 'archivos', 'salidasTaller', 'historial'])->find($id);

        if (!$orden) {
            return response()->json([
                'status' => 'error',
                'message' => 'Orden no encontrada'
            ], 404);
        }

        try {
            db()->transaction(function () use ($orden) {

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

                $basePath = dirname(__DIR__, 2);
                $scanDir = $basePath . '/public/ordenes_escaneos/' . $orden->id;
                if (is_dir($scanDir)) {
                    // Borramos todos los archivos dentro y luego la carpeta
                    array_map('unlink', glob("$scanDir/*.*"));
                    rmdir($scanDir);
                }
                // 2. Borrar Documento DOCX generado (solo aplica para propios)
                $docxFile = $basePath . '/storage/orden_vehiculos/orden_vehiculo_' . $orden->id . '.docx';
                if (file_exists($docxFile)) {
                    @unlink($docxFile);
                }

                if ($orden->tipo_vehiculo === 'arrendado' && $orden->detalleArrendado) {
                    $camposFoto = [
                        'foto_circulacion',
                        'foto_odometro',
                        'foto_llanta_del_pil',
                        'foto_llanta_del_cop',
                        'foto_llanta_tra_pil',
                        'foto_llanta_tra_cop'
                    ];

                    foreach ($camposFoto as $campo) {
                        $rutaRelativa = $orden->detalleArrendado->$campo;
                        if ($rutaRelativa) {
                            $fullPath = $basePath . '/public' . $rutaRelativa;
                            if (file_exists($fullPath)) {
                                @unlink($fullPath);
                            }
                        }
                    }
                }

                if ($orden->detallePropio) $orden->detallePropio->delete();
                if ($orden->detalleArrendado) $orden->detalleArrendado->delete();

                // Borrar historial y registros de archivos
                $orden->archivos()->delete();
                $orden->historial()->delete();
                $orden->salidasTaller()->delete();

                $orden->delete();
            });

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

public function generarPdfArrendado($id)
    {
        // 1. Buscar orden
        $orden = OrdenVehiculo::with('detalleArrendado')->find($id);

        if (!$orden || $orden->tipo_vehiculo !== 'arrendado') {
            return response()->json(['status' => 'error', 'message' => 'Orden no válida'], 404);
        }

        $detalle = $orden->detalleArrendado;
        $basePath = dirname(__DIR__, 2) . '/public';

        // 2. Preparar Imágenes en BASE64
        // Esto incrusta la imagen en el HTML, evitando errores de rutas o permisos de Dompdf
        $paths = [
            'circulacion' => $this->imageToBase64($basePath, $detalle->foto_circulacion),
            'odometro'    => $this->imageToBase64($basePath, $detalle->foto_odometro),
            'del_pil'     => $this->imageToBase64($basePath, $detalle->foto_llanta_del_pil),
            'del_cop'     => $this->imageToBase64($basePath, $detalle->foto_llanta_del_cop),
            'tra_pil'     => $this->imageToBase64($basePath, $detalle->foto_llanta_tra_pil),
            'tra_cop'     => $this->imageToBase64($basePath, $detalle->foto_llanta_tra_cop),
            'logo'        => $this->imageToBase64($basePath, '/assets/img/logo_arren.png')
        ];

        // 3. Renderizar vista
        $html = view('ordenvehiculos.pdf_arrendado', [
            'orden' => $orden,
            'paths' => $paths // Ahora enviamos cadenas largas de texto (Base64), no rutas
        ]);

        // 4. Configurar Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        
        // IMPORTANTE: Aumentar memoria si las fotos son pesadas
        ini_set('memory_limit', '256M'); 

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream('Orden_Arrendado_' . $orden->noeconomico . '.pdf', ['Attachment' => true]);
    }

    /**
     * Convierte una imagen local a cadena Base64 para incrustar en PDF
     */
    private function imageToBase64($basePath, $relativePath)
    {
        if (!$relativePath) return null;

        // Limpieza y normalización de ruta
        $cleanRel = ltrim($relativePath, '/');
        $cleanRel = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $cleanRel);
        $fullPath = $basePath . DIRECTORY_SEPARATOR . $cleanRel;

        if (!file_exists($fullPath)) {
            // Intentar encontrar el archivo si la extensión difiere (ej: en BD dice .jpg pero es .png)
            // Esto es útil si subiste archivos antes de forzar la extensión
            $extensions = ['.jpg', '.jpeg', '.png', '.gif'];
            $found = false;
            
            // Quitamos extensión actual si la tuviera para probar otras
            $baseName = preg_replace('/\.[^.]+$/', '', $fullPath);

            foreach ($extensions as $ext) {
                if (file_exists($baseName . $ext)) {
                    $fullPath = $baseName . $ext;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) return null;
        }

        try {
            // Leer contenido del archivo
            $imageData = file_get_contents($fullPath);
            
            // Obtener tipo de imagen para el header (jpg, png, etc)
            $type = pathinfo($fullPath, PATHINFO_EXTENSION);

            if ($type === 'jpg') $type = 'jpeg';
            
            // Retornar formato data URI: "data:image/jpg;base64,..."
            return 'data:image/' . $type . ';base64,' . base64_encode($imageData);
        } catch (\Exception $e) {
            return null;
        }
    }
    /**
     * Helper para verificar si la imagen existe físicamente y retornar ruta completa
     */

    /**
     * FLUJO ARRENDADOS: PV marca la solicitud como "Atendida"
     */
    public function marcarAtendidoArrendado($id)
    {
        $orden = OrdenVehiculo::find($id);

        if (!$orden || $orden->tipo_vehiculo !== 'arrendado') {
            return response()->json(['status' => 'error', 'message' => 'Orden no válida'], 404);
        }

        try {
            $oldStatus = $orden->status;
            $orden->update(['status' => 'ESPERANDO CITA']);

            HistorialOrden::create([
                'orden_vehiculo_id' => $orden->id,
                'tipo_evento' => 'estado_cambiado',
                'detalles' => 'PV está gestionando la cita',
                'old_value' => $oldStatus,
                'new_value' => 'ESPERANDO CITA',
            ]);

            return response()->json(['status' => 'success', 'message' => 'Orden marcada en espera de cita.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * FLUJO ARRENDADOS: PV asigna la fecha de la cita
     */
    public function asignarCitaArrendado($id)
    {
        $orden = OrdenVehiculo::with('detalleArrendado')->find($id);
        $data = request()->body();
        $fechaCita = $data['fecha_cita'] ?? null;
        $taller = $data['taller'] ?? null;

        if (!$orden || $orden->tipo_vehiculo !== 'arrendado' || !$fechaCita || !$taller) {
            return response()->json(['status' => 'error', 'message' => 'Datos incompletos o inválidos'], 400);
        }

        try {
            db()->transaction(function () use ($orden, $fechaCita, $taller) {
                // Actualizar detalle hijo
                if ($orden->detalleArrendado) {
                    $orden->detalleArrendado->update([
                        'fecha_cita' => $fechaCita,
                        'taller' => $taller
                    ]);
                }

                // Actualizar padre
                $oldStatus = $orden->status;
                $orden->update(['status' => 'CITA ASIGNADA']);

                HistorialOrden::create([
                    'orden_vehiculo_id' => $orden->id,
                    'tipo_evento' => 'estado_cambiado',
                    'detalles' => 'Cita asignada para el: ' . $fechaCita . ' en taller: ' . $taller,
                    'old_value' => $oldStatus,
                    'new_value' => 'CITA ASIGNADA',
                ]);
            });

            return response()->json(['status' => 'success', 'message' => 'Cita asignada correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * FLUJO ARRENDADOS: El usuario ingresa el vehículo al taller en el día de la cita
     */
    public function ingresarTallerArrendado($id)
    {
        $orden = OrdenVehiculo::find($id);

        if (!$orden || $orden->tipo_vehiculo !== 'arrendado') {
            return response()->json(['status' => 'error', 'message' => 'Orden no válida'], 404);
        }

        try {
            $oldStatus = $orden->status;
            $orden->update(['status' => 'VEHICULO TALLER']);

            HistorialOrden::create([
                'orden_vehiculo_id' => $orden->id,
                'tipo_evento' => 'estado_cambiado',
                'detalles' => 'Vehículo ingresado a taller arrendado',
                'old_value' => $oldStatus,
                'new_value' => 'VEHICULO TALLER',
            ]);

            return response()->json(['status' => 'success', 'message' => 'Vehículo marcado en taller.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
