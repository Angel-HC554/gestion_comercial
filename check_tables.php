<?php
require __DIR__ . '/vendor/autoload.php';

try {
    $db = new Leaf\Db;
    $db->connect([
        'dbtype' => 'mysql',
        'host' => '127.0.0.1',
        'dbname' => 'pruebas',
        'user' => 'root',
        'password' => ''
    ]);

    echo "Connected.\n";
    $tables = $db->query("SHOW TABLES")->fetchAll();
    echo "Tables found: " . count($tables) . "\n";
    foreach ($tables as $table) {
        print_r($table);
    }

    $columns = $db->query("DESCRIBE orden_vehiculos")->fetchAll();
    if ($columns) {
        echo "orden_vehiculos columns:\n";
        foreach ($columns as $col) {
            echo $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
