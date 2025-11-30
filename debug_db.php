<?php
require __DIR__ . '/vendor/autoload.php';

$leaf = new Leaf\App;
$db = new Leaf\Db;
$db->connect([
    'dbtype' => 'mysql',
    'host' => '127.0.0.1',
    'dbname' => 'pruebas',
    'user' => 'root',
    'password' => ''
]);

echo "Connected to DB.\n";

$tables = $db->query("SHOW TABLES")->fetchAll();
echo "Tables:\n";
foreach ($tables as $table) {
    print_r($table);
}

$columns = $db->query("DESCRIBE orden_vehiculos")->fetchAll();
if ($columns) {
    echo "\nColumns in orden_vehiculos:\n";
    foreach ($columns as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
} else {
    echo "\nTable orden_vehiculos does not exist.\n";
}
