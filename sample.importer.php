<?php
include_once('mysql.importer.php');
// Usage example
$importer = new MySQLImporter();
$importer->connect('localhost', 'iptv_copy', 'root', '');
$importer->loadSQLFile('backup.sql');

// Import specific parts as needed
$importer->importTables();
$importer->importData();
$importer->importViews();
$importer->importTriggers();
$importer->importFunctionsAndProcedures();
$importer->importEvents();

$importer->closeSQLFile();
?>