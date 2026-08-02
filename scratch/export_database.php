<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
$dbName = env('DB_DATABASE', 'fiewin');
$tableKey = "Tables_in_" . $dbName;

$sql = "-- Fiewin Database Dump for InfinityFree / Shared Hosting\n";
$sql .= "-- Exported on " . date('Y-m-d H:i:s') . "\n\n";
$sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

foreach ($tables as $t) {
    $tableName = $t->$tableKey;
    
    // Structure
    $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
    $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
    $sql .= $createTable[0]->{'Create Table'} . ";\n\n";

    // Data
    $rows = DB::table($tableName)->get();
    if ($rows->count() > 0) {
        $sql .= "INSERT INTO `{$tableName}` VALUES\n";
        $valuesArr = [];
        foreach ($rows as $row) {
            $rowValues = array_map(function ($val) {
                if ($val === null) return 'NULL';
                return "'" . addslashes($val) . "'";
            }, (array)$row);
            $valuesArr[] = "(" . implode(", ", $rowValues) . ")";
        }
        $sql .= implode(",\n", $valuesArr) . ";\n\n";
    }
}

$sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

file_put_contents(__DIR__ . '/../fiewin_database.sql', $sql);
echo "Database dump generated successfully: fiewin_database.sql (" . number_format(filesize(__DIR__ . '/../fiewin_database.sql')) . " bytes)\n";
