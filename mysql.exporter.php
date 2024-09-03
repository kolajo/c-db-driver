<?php
/*
// Usage example
$exporter = new MySQLExporter();
$exporter->connect('localhost', 'iptv', 'username', 'password');

// Export specific parts as needed
$exporter->exportTableStructure();
$exporter->exportTableData();
$exporter->exportViews();
$exporter->exportTriggers();
$exporter->exportFunctionsAndProcedures();
$exporter->exportEvents();

// Save output to a file
$exporter->saveToFile('backup.sql');
*/
class MySQLExporter
{
    private $pdo;
    private $output;
    private $dataBatchSize;

    // 1. Database Connection
    public function connect($host, $dbname, $user, $pass)
    {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $this->pdo = new PDO($dsn, $user, $pass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // 8. Keep the File Formatting (Header and Footer)
    private function generateHeader()
    {
        return "/*\n Cleavey-Kit Data Transfer \n
Source Server         : Local
Source Server Type    : MySQL
Source Server Version : " . $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "
Source Host           : " . $this->pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "
Source Schema         : " . $this->pdo->query('select database()')->fetchColumn() . "\n
Target Server Type    : MySQL
Target Server Version : " . $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "
File Encoding         : 65001\n
Date: " . date('d/m/Y H:i:s') . "
*/\n\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n";
    }

    private function generateFooter()
    {
        return "SET FOREIGN_KEY_CHECKS = 1;\n";
    }

    // 2. Export Table Structure
    public function exportTableStructure()
    {
        $tables = $this->getTables();
        foreach ($tables as $table) {
            $stmt = $this->pdo->query("SHOW CREATE TABLE `$table`");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->output .= "-- ----------------------------\n";
            $this->output .= "-- Table structure for $table\n";
            $this->output .= "-- ----------------------------\n";
            $this->output .= "DROP TABLE IF EXISTS `$table`;\n";
            $this->output .= $result['Create Table'] . ";\n\n";
        }
    }

    // 3. Export Data (with batching)
    public function exportTableData()
    {
        $tables = $this->getTables();
        foreach ($tables as $table) {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM `$table`");
            $totalRows = $stmt->fetchColumn();
            $batchSize = 1000;

            if ($totalRows > 0) {
                for ($offset = 0; $offset < $totalRows; $offset += $batchSize) {
                    $this->output .= $this->generateInsertStatements($table, $offset, $batchSize);
                }
            }
        }
    }

    private function generateInsertStatements($table, $offset, $batchSize)
    {
        $stmt = $this->pdo->query("SELECT * FROM `$table` LIMIT $offset, $batchSize");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) === 0) {
            return "";
        }

        $columns = array_keys($rows[0]);
        $columnsList = implode("`, `", $columns);

        $output = "INSERT INTO `$table` (`$columnsList`) VALUES\n";

        foreach ($rows as $row) {
            $values = array_map([$this->pdo, 'quote'], array_values($row));
            $output .= "(" . implode(", ", $values) . "),\n";
        }

        $output = rtrim($output, ",\n") . ";\n\n";

        return $output;
    }

    // 4. Export Views
    public function exportViews()
    {
        $stmt = $this->pdo->query("SHOW FULL TABLES WHERE TABLE_TYPE LIKE 'VIEW'");
        $views = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($views as $view) {
            $stmt = $this->pdo->query("SHOW CREATE VIEW `$view`");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->output .= "-- ----------------------------\n";
            $this->output .= "-- View structure for $view\n";
            $this->output .= "-- ----------------------------\n";
            $this->output .= "DROP VIEW IF EXISTS `$view`;\n";
            $this->output .= $result['Create View'] . ";\n\n";
        }
    }

    // 5. Export Triggers
    public function exportTriggers()
    {
        $stmt = $this->pdo->query("SHOW TRIGGERS");
        $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($triggers as $trigger) {
            $this->output .= "-- ----------------------------\n";
            $this->output .= "-- Trigger structure for {$trigger['Trigger']}\n";
            $this->output .= "-- ----------------------------\n";
            $stmt = $this->pdo->query("SHOW CREATE TRIGGER `{$trigger['Trigger']}`");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->output .= $result['SQL Original Statement'] . ";\n\n";
        }
    }

    // 6. Export Functions and Procedures
    public function exportFunctionsAndProcedures()
    {
        $stmt = $this->pdo->query("SHOW PROCEDURE STATUS WHERE Db = DATABASE()");
        $procedures = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($procedures as $procedure) {
            $stmt = $this->pdo->query("SHOW CREATE PROCEDURE `{$procedure['Name']}`");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->output .= "-- ----------------------------\n";
            $this->output .= "-- Procedure structure for {$procedure['Name']}\n";
            $this->output .= "-- ----------------------------\n";
            $this->output .= "DROP PROCEDURE IF EXISTS `{$procedure['Name']}`;\n";
            $this->output .= $result['Create Procedure'] . ";\n\n";
        }

        $stmt = $this->pdo->query("SHOW FUNCTION STATUS WHERE Db = DATABASE()");
        $functions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($functions as $function) {
            $stmt = $this->pdo->query("SHOW CREATE FUNCTION `{$function['Name']}`");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->output .= "-- ----------------------------\n";
            $this->output .= "-- Function structure for {$function['Name']}\n";
            $this->output .= "-- ----------------------------\n";
            $this->output .= "DROP FUNCTION IF EXISTS `{$function['Name']}`;\n";
            $this->output .= $result['Create Function'] . ";\n\n";
        }
    }

    // 7. Export Events
    public function exportEvents()
    {
        $stmt = $this->pdo->query("SHOW EVENTS WHERE Db = DATABASE()");
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($events as $event) {
            $stmt = $this->pdo->query("SHOW CREATE EVENT `{$event['Name']}`");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->output .= "-- ----------------------------\n";
            $this->output .= "-- Event structure for {$event['Name']}\n";
            $this->output .= "-- ----------------------------\n";
            $this->output .= "DROP EVENT IF EXISTS `{$event['Name']}`;\n";
            $this->output .= $result['Create Event'] . ";\n\n";
        }
    }

    // 9. Keep the File Output
    public function saveToFile($filename)
    {
        $this->output = $this->generateHeader() . $this->output . $this->generateFooter();
        file_put_contents($filename, $this->output);
    }

    // Utility to get all tables in the current database
    private function getTables()
    {
        $stmt = $this->pdo->query("SHOW TABLES");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

?>