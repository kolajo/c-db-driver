<?php

/*
// Usage example
$importer = new MySQLImporter();
$importer->connect('localhost', 'iptv', 'username', 'password');
$importer->loadSQLFile('backup.sql');

// Import specific parts as needed
$importer->importTables();
$importer->importData();
$importer->importViews();
$importer->importTriggers();
$importer->importFunctionsAndProcedures();
$importer->importEvents();

$importer->closeSQLFile();
*/

class MySQLImporter
{
    private $pdo;
    private $fileHandle;

    // 1. Database Connection
    public function connect($host, $dbname, $user, $pass)
    {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $this->pdo = new PDO($dsn, $user, $pass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // 2. Load SQL File
    public function loadSQLFile($filename)
    {
        if (file_exists($filename)) {
            $this->fileHandle = fopen($filename, 'r');
            if (!$this->fileHandle) {
                throw new Exception("Cannot open SQL file: $filename");
            }
        } else {
            throw new Exception("SQL file not found: $filename");
        }
    }

    // 3. Import Tables
    public function importTables()
    {
        $this->processSQLFile('CREATE TABLE', 'DROP TABLE IF EXISTS');
    }

    // 4. Import Views
    public function importViews()
    {
        $this->processSQLFile('CREATE VIEW', 'DROP VIEW IF EXISTS');
    }

    // 5. Import Data
    public function importData()
    {
        $buffer = '';
        $delimiter = ';';

        while (!feof($this->fileHandle)) {
            $line = fgets($this->fileHandle);

            // Check if the line starts with 'INSERT INTO'
            if (stripos($line, 'INSERT INTO') !== false) {
                $buffer .= $line;
            } elseif (!empty($buffer)) {
                $buffer .= $line;
                // Check if the buffer ends with a semicolon indicating the end of an SQL statement
                if (substr(trim($line), -1) === $delimiter) {
                    $this->executeSQL($buffer);
                    $buffer = '';
                }
            }
        }

        // Execute any remaining SQL in the buffer
        if (!empty($buffer)) {
            $this->executeSQL($buffer);
        }
    }

    // 6. Import Triggers
    public function importTriggers()
    {
        $this->processSQLFile('CREATE TRIGGER', 'DROP TRIGGER IF EXISTS');
    }

    // 7. Import Functions and Procedures
    public function importFunctionsAndProcedures()
    {
        $this->processSQLFile('CREATE FUNCTION', 'DROP FUNCTION IF EXISTS');
        $this->processSQLFile('CREATE PROCEDURE', 'DROP PROCEDURE IF EXISTS');
    }

    // 8. Import Events
    public function importEvents()
    {
        $this->processSQLFile('CREATE EVENT', 'DROP EVENT IF EXISTS');
    }

    // Utility: Process SQL File by Sections
    private function processSQLFile($startKeyword, $dropKeyword)
    {
        $buffer = '';
        $delimiter = ';';

        while (!feof($this->fileHandle)) {
            $line = fgets($this->fileHandle);

            if (stripos($line, $startKeyword) !== false) {
                if ($dropKeyword) {
                    $buffer = $this->extractDropStatement($buffer, $dropKeyword);
                }
                $buffer .= $line;
            } elseif (stripos($line, $delimiter) !== false && !empty($buffer)) {
                $buffer .= $line;
                $this->executeSQL($buffer);
                $buffer = '';
            } elseif (!empty($buffer)) {
                $buffer .= $line;
            }
        }

        // Execute any remaining SQL in the buffer
        if (!empty($buffer)) {
            $this->executeSQL($buffer);
        }
    }

    // Utility: Extract Drop Statement if needed
    private function extractDropStatement($buffer, $dropKeyword)
    {
        if (stripos($buffer, $dropKeyword) !== false) {
            $endPos = strpos($buffer, ';') + 1;
            $this->executeSQL(substr($buffer, 0, $endPos));
            $buffer = substr($buffer, $endPos);
        }
        return $buffer;
    }

    // Utility: Execute SQL
    private function executeSQL($sql)
    {
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            echo "Error executing SQL: " . $e->getMessage() . "\n";
        }
    }

    // 9. Close File Handle
    public function closeSQLFile()
    {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }
    }
}

?>