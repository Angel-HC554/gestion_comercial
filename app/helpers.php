<?php

if (!function_exists('old')) {
    /**
     * Recupera el valor de una entrada antigua o devuelve el valor por defecto.
     * Esto simula la función old() de Laravel para migraciones fáciles.
     */
    function old($key, $default = null)
    {
        // Si existen datos enviados por POST (ej. error de validación), úsalos.
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }

        // Si no, usa el valor por defecto (útil para edición)
        return $default;
    }
}