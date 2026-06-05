<?php

namespace App\Controllers;

/**
 * This is the base controller for your Leaf MVC Project.
 * You can initialize packages or define methods here to use
 * them across all your other controllers which extend this one.
 */
class Controller extends \Leaf\Controller
{
    public function ejecutarRespaldo()
    {
        $basePath = dirname(__DIR__, 2);
        $rutaBat = $basePath . '\Respaldos_Web.bat';
        
        // Verificamos que el archivo realmente exista
        if (!file_exists($rutaBat)) {
            response()->json(['success' => false, 'mensaje' => 'No se encontró el archivo ejecutable en ' . $rutaBat]);
            return;
        }
        // Ejecutamos el archivo en Windows
        exec("cmd /c \"$rutaBat\"", $salidaTexto, $codigoRespuesta);

        // Si el codigo de respuesta es 0, significa que Windows ejecutó todo sin errores fatales
        if ($codigoRespuesta === 0) {
            response()->json(['success' => true, 'mensaje' => 'Todas las bases de datos han sido respaldadas con éxito.']);
        } else {
            response()->json(['success' => false, 'mensaje' => 'Hubo un error en la terminal de Windows al respaldar.']);
        }
    }

    public function obtenerUltimoRespaldo()
    {
        $carpetaRespaldo = 'C:\Respaldos_bd\pruebas'; 
                                                                                                                                                                       
        if (!is_dir($carpetaRespaldo)) {
            return null; 
        }

        $archivosRespaldo = glob($carpetaRespaldo . DIRECTORY_SEPARATOR . '*.sql'); 

        if (empty($archivosRespaldo)) {
            return null; 
        }                                                                                                                                                              
                                                                                                                                                                       
        usort($archivosRespaldo, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $ultimoRespaldo = $archivosRespaldo[0];

        $fechaUltimoRespaldo = date('d/m/Y H:i:s', filemtime($ultimoRespaldo));
                                                                                                                                                                       
        return $fechaUltimoRespaldo;
    }   
}
