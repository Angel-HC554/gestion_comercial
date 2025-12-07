<?php

app()->get('/', function () {
    response()->redirect('/auth/login');
});
app()->get('/ordenvehiculos/create', 'OrdenVehiculoController@create');
app()->post('/ordenvehiculos/store', 'OrdenVehiculoController@store');
// NUEVA RUTA: Pantalla de éxito/generación de PDF
app()->get('/ordenvehiculos/generar/{id}', 'OrdenVehiculoController@generar');
// Generar el DOCX (usado internamente o para debugging)
app()->post('/ordenes/generar/{id}', 'OrdenVehiculoController@generarOrden');

// Ruta para el botón "Descargar PDF" del modal
app()->get('/ordenvehiculos/pdf/{id}', 'OrdenVehiculoController@generatePdf');
// Vista principal
app()->get('/ordenvehiculos', 'OrdenVehiculoController@index');

// API de búsqueda (AJAX)
app()->get('/api/ordenes/search', 'OrdenVehiculoController@search');

// Rutas para editar ordenes de vehículo
app()->get('/api/ordenes/{id}/historial', 'OrdenVehiculoController@history');
app()->get('/ordenvehiculos/{id}/edit', 'OrdenVehiculoController@edit');
app()->put('/ordenvehiculos/{id}', 'OrdenVehiculoController@update');
app()->put('/ordenvehiculos/modal/{id}', 'OrdenVehiculoController@updateModal');
app()->post('/ordenvehiculos/upload/{id}', 'OrdenVehiculoController@uploadScan');
// En _app.php, agrégalo junto a tus otras rutas PUT
app()->put('/ordenvehiculos/code500/{id}', 'OrdenVehiculoController@updateCode500');
// NUEVA RUTA PARA ELIMINAR
app()->delete('/ordenvehiculos/{id}', 'OrdenVehiculoController@destroy');

// --- RUTAS DE VEHICULOS (NUEVAS) ---

// 1. API de Búsqueda (Debe ir ANTES de la ruta {id} para evitar conflictos)
// Esta ruta es llamada por el fetch() en el AlpineJS del index
app()->get('/vehiculos/search', 'VehiculoController@search');
app()->get('/vehiculos', 'VehiculoController@index');
app()->post('/vehiculos', 'VehiculoController@store');
app()->post('/vehiculos/import', 'VehiculoController@import');
app()->get('/vehiculos/export', 'VehiculoController@export');
app()->get('/vehiculos/{id}', 'VehiculoController@show');

// Rutas de Supervisión
// Ruta para generar el reporte PDF de supervisión semanal
app()->get('/supervisiones/pdf/{id}', 'SupervisionSemanalController@generarReportePdf');
app()->post('/supervision-semanal', 'SupervisionSemanalController@store');
app()->get('/supervision-semanal', 'SupervisionSemanalController@index');
app()->post('/supervision-diaria', 'SupervisionDiariaController@store');
app()->get('/supervision-diaria', 'SupervisionDiariaController@index');

app()->get('/dashboard-diario', 'DashboardController@index');
app()->get('/dashboard-semanal', 'DashboardSemanalController@index');

// RUTA TEMPORAL PARA ASIGNAR ADMIN
app()->get('/hacerme-admin', function() {
    // 1. Asegúrate de estar logueado antes de entrar a esta ruta
    $user = auth()->user();
    
    if (!$user) {
        response()->json(['error' => 'Primero inicia sesión en el sistema.'], 401);
    }
    
    // 2. Asignar el rol
    $user->assign('admin');
    
    response()->json([
        'success' => true,
        'message' => '¡Rol de ADMIN asignado correctamente a ' . $user->username . '!',
        'roles_actuales' => $user->roles()
    ]);
});

// Página principal de usuarios
app()->get('/users', 'UserController@index');
// Endpoint que devuelve SOLO las filas (HTML) para HTMX
app()->get('/users/search', 'UserController@search');
app()->post('/users', 'UserController@store');
app()->get('/users/{id}/edit', 'UserController@edit');
app()->put('/users/{id}', 'UserController@update');