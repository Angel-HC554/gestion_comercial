<?php
app()->set404(function(){
    response()->page(ViewsPath('errors/404.html', false), 404);
});

app()->set500(function(){
    response()->page(ViewsPath('errors/500.html', false), 500);
});
// --- 1. RUTAS PÚBLICAS Y DE AUTENTICACIÓN ---
// La raíz redirige al login
app()->get('/', function () {
    response()->redirect('/auth/login');
});

// --- 2. RUTAS PROTEGIDAS (Middleware 'auth.required') ---
app()->group('/',['middleware' => 'auth.required', function () {

    // --- DASHBOARD ---
    app()->get('/dashboard', 'HomeController@index');
    app()->get('/dashboard/', 'HomeController@index');
    
    // --- API & UTILIDADES ---
    app()->get('/api/check-orden-500', 'NotificationController@checkOrden500');
    
    // --- ORDENES DE VEHICULOS ---
    app()->get('/ordenvehiculos/create', 'OrdenVehiculoController@create');
    app()->post('/ordenvehiculos/store', 'OrdenVehiculoController@store');
    app()->get('/ordenvehiculos/generar/{id}', 'OrdenVehiculoController@generar');
    app()->post('/ordenes/generar/{id}', 'OrdenVehiculoController@generarOrden');
    app()->get('/ordenvehiculos/pdf/{id}', 'OrdenVehiculoController@generatePdf');
    app()->get('/ordenvehiculos', 'OrdenVehiculoController@index');
    app()->get('/api/ordenes/search', 'OrdenVehiculoController@search');
    app()->get('/api/ordenes/{id}/historial', 'OrdenVehiculoController@history');
    app()->get('/ordenvehiculos/{id}/edit', 'OrdenVehiculoController@edit');
    app()->put('/ordenvehiculos/{id}', 'OrdenVehiculoController@update');
    app()->put('/ordenvehiculos/modal/{id}', 'OrdenVehiculoController@updateModal');
    app()->post('/ordenvehiculos/upload/{id}', 'OrdenVehiculoController@uploadScan');
    app()->put('/ordenvehiculos/code500/{id}', 'OrdenVehiculoController@updateCode500');
    app()->delete('/ordenvehiculos/{id}', 'OrdenVehiculoController@destroy');

    // --- VEHICULOS ---
    app()->get('/vehiculos/search', 'VehiculoController@search');
    app()->get('/vehiculos', 'VehiculoController@index');
    app()->post('/vehiculos', 'VehiculoController@store');
    app()->post('/vehiculos/import', 'VehiculoController@import');
    app()->get('/vehiculos/export', 'VehiculoController@export');
    app()->get('/vehiculos/{id}', 'VehiculoController@show');
    app()->get('/vehiculos/{id}/historial', 'VehiculoController@historial');

    // --- SUPERVISIONES ---
    app()->get('/supervisiones/pdf/{id}', 'SupervisionSemanalController@generarReportePdf');
    app()->post('/supervision-semanal', 'SupervisionSemanalController@store');
    app()->get('/supervision-semanal',['middleware' => 'is:admin', 'SupervisionSemanalController@index']);
    app()->get('/supervision-semanal/resumen-agencias','SupervisionSemanalController@resumenAgencias');
    
    app()->get('/supervision-diaria/resumen-agencias',['middleware' => 'is:admin', 'SupervisionDiariaController@resumenAgencias']);
    app()->post('/supervision-diaria', 'SupervisionDiariaController@store');
    app()->get('/supervision-diaria',['middleware' => 'is:admin', 'SupervisionDiariaController@index']);
    app()->get('/supervision-diaria/{id}/historial', 'SupervisionDiariaController@historial');

    // --- DASHBOARDS ESPECIFICOS ---
    app()->get('/dashboard-diario', ['middleware' => 'is:admin', 'DashboardController@index']);
    app()->get('/dashboard-semanal', ['middleware' => 'is:admin', 'DashboardSemanalController@index']);
    app()->get('/dashboard-vehiculos',['middleware' => 'is:admin', 'DashboardVehiculosController@index']);

    // --- USUARIOS ---
    app()->get('/users', ['middleware' => 'is:admin', 'UserController@index']);
    app()->get('/users/search', ['middleware' => 'is:admin', 'UserController@search']);
    app()->post('/users', ['middleware' => 'is:admin', 'UserController@store']);
    app()->get('/users/{id}/edit', ['middleware' => 'is:admin', 'UserController@edit']);
    app()->put('/users/{id}', ['middleware' => 'is:admin', 'UserController@update']);

    // --- UTILS ADMIN ---
    app()->get('/hacerme-admin', function() {
        // Como ya estamos dentro del grupo protegido, auth()->user() siempre devolverá un usuario[cite: 67].
        $user = auth()->user();
        $user->assign('admin');
        response()->json([
            'success' => true,
            'message' => '¡Rol de ADMIN asignado correctamente a ' . ($user->user ?? $user->username) . '!',
            'roles_actuales' => $user->roles()
        ]);
    });

}]); // Fin del grupo protegido