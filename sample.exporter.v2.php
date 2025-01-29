<?php
include_once('mysql.exporter.v2.php');
// Usage Example
$server = '127.0.0.1';
$database = 'psmitn_platform_data';
$user = 'root';
$pass = "";

$exporter = new DatabaseExporter($server, $database, $user, $pass);
//
$options = [
    'tables' => 'all', // or ['accounts', 'other_table']
    'export_data' => true,
    'export_functions' => true,
    'export_events' => true,
    'export_triggers' => true,
];

$exportedSchema = $exporter->exportDatabase($options);

$folder ='C:\\cleavey\\database backup\\';
$file_name = $database." ".date('Ymd_His').'.sql';
$file_path = $folder.$file_name;

file_put_contents($file_path, $exportedSchema);

echo "Database <b>$database</b> from <b>$server</b> exported successfully! <b>$file_name</b> to <b>$folder</b>";
?>