<?php

class DatabaseExporter
{
    private $pdo;
    private $dbName;

    public function __construct($host, $dbName, $user, $pass)
    {
        $dsn = "mysql:host=$host;dbname=$dbName;charset=utf8mb4";
        $this->pdo = new PDO($dsn, $user, $pass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->dbName = $dbName;
    }

    public function exportDatabase($options = [])
    {
        $export = [];

        // Add DROP DATABASE IF EXISTS and CREATE DATABASE
        $export[] = "DROP DATABASE IF EXISTS `{$this->dbName}`;";
        $export[] = "CREATE DATABASE `{$this->dbName}`;";
        $export[] = "USE `{$this->dbName}`;";

        // Export tables
        if (isset($options['tables']) && $options['tables'] === 'all') {
            $tables = $this->getAllTables();
        } elseif (isset($options['tables']) && is_array($options['tables'])) {
            $tables = $options['tables'];
        } else {
            $tables = [];
        }

        foreach ($tables as $table) {
            $export[] = $this->exportTableStructure($table);
            if (isset($options['export_data']) && $options['export_data']) {
                $export[] = $this->exportTableData($table);
            }
        }

        // Export functions
        if (isset($options['export_functions']) && $options['export_functions']) {
            $export[] = $this->exportFunctions();
        }

        // Export events
        if (isset($options['export_events']) && $options['export_events']) {
            $export[] = $this->exportEvents();
        }

        // Export triggers
        if (isset($options['export_triggers']) && $options['export_triggers']) {
            $export[] = $this->exportTriggers();
        }

        return implode("\n\n", $export);
    }

    private function getAllTables()
    {
        $stmt = $this->pdo->query("SHOW TABLES");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function exportTableStructure($table)
    {
        $stmt = $this->pdo->query("SHOW CREATE TABLE `$table`");
        $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
        return "DROP TABLE IF EXISTS `$table`;\n" . $createTable['Create Table'] . ";";
    }

    private function exportTableData($table)
    {
        $stmt = $this->pdo->query("SELECT * FROM `$table`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return "";
        }

        $columns = array_keys($rows[0]);
        $insertStatements = [];

        foreach ($rows as $row) {
            $values = array_map(function ($value) {
                return $this->pdo->quote($value);
            }, array_values($row));
            $insertStatements[] = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");";
        }

        return implode("\n", $insertStatements);
    }

    private function exportFunctions()
    {
        $stmt = $this->pdo->query("SHOW FUNCTION STATUS WHERE Db = '{$this->dbName}'");
        $functions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $export = [];
        foreach ($functions as $function) {
            $name = $function['Name'];
            $stmt = $this->pdo->query("SHOW CREATE FUNCTION `$name`");
            $createFunction = $stmt->fetch(PDO::FETCH_ASSOC);
            $export[] = "DROP FUNCTION IF EXISTS `$name`;\n" . $createFunction['Create Function'] . ";";
        }

        return implode("\n\n", $export);
    }

    private function exportEvents()
    {
        $stmt = $this->pdo->query("SHOW EVENTS");
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $export = [];
        foreach ($events as $event) {
            $name = $event['Name'];
            $stmt = $this->pdo->query("SHOW CREATE EVENT `$name`");
            $createEvent = $stmt->fetch(PDO::FETCH_ASSOC);
            $export[] = "DROP EVENT IF EXISTS `$name`;\n" . $createEvent['Create Event'] . ";";
        }

        return implode("\n\n", $export);
    }

    private function exportTriggers()
    {
        $stmt = $this->pdo->query("SHOW TRIGGERS");
        $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $export = [];
        foreach ($triggers as $trigger) {
            $name = $trigger['Trigger'];
            $stmt = $this->pdo->query("SHOW CREATE TRIGGER `$name`");
            $createTrigger = $stmt->fetch(PDO::FETCH_ASSOC);
            $export[] = "DROP TRIGGER IF EXISTS `$name`;\n" . $createTrigger['SQL Original Statement'] . ";";
        }

        return implode("\n\n", $export);
    }
}

?>