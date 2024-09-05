<?php
    include_once('mysql.exporter.php');
    // Usage example
    $exporter = new MySQLExporter();
    $database = 'testa_event';
    //$folder = 'db_backup/';
    $folder ='C:\\cleavey\\database backup\\';
    $file_name = $database." ".date('Ymd_His').'.sql';
    $file_path = $folder.$file_name;
    //
    $exporter->connect('localhost', $database, 'root', '');

    // Export specific parts as needed
    $exporter->exportTableStructure();
    $exporter->exportTableData();
    $exporter->exportViews();
    $exporter->exportTriggers();
    $exporter->exportFunctionsAndProcedures();
    $exporter->exportEvents();

    // Save output to a file
    $exporter->saveToFile($file_path);
    //
    echo "Database $database is exported successfully in $file_path";
?>