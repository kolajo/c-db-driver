<?php
    include_once('mysql.exporter.php');
    // Usage example
    $exporter = new MySQLExporter();
    $exporter->connect('localhost', 'iptv', 'root', '');

    // Export specific parts as needed
    $exporter->exportTableStructure();
    $exporter->exportTableData();
    $exporter->exportViews();
    $exporter->exportTriggers();
    $exporter->exportFunctionsAndProcedures();
    $exporter->exportEvents();

    // Save output to a file
    $exporter->saveToFile('backup.sql');
?>