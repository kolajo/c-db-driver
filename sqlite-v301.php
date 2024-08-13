<?php
/*
*
*  Database driver and helper library, this library is owned by Cleavey Active Technology
*  All right reserved
* 
*  Usage of this code most be with appriopriate aproval from the owner
* 
*  This code is written by Kolajo Adeyinka
* 
*  2024 Copyright
* 
*  Production ready
*
*   Cleavey SQLite Database driver version 3.0.1
* 
*  
*/
class SqlitePdo {
    private $pdo;
    private $dbPath;
    #
    public $ErrorMessage;
    public $ConnMsg;

    public function __construct($dbPath) {
        $this->dbPath = $dbPath;
        $dsn = "sqlite:$this->dbPath";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, null, null, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function insert($table, $data) {
        try {
            $columns = implode(", ", array_keys($data));
            $placeholders = implode(", ", array_fill(0, count($data), '?'));
            $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_values($data));
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            $this->ErrorMessage = "Query Error: " . $e->getMessage();
        }
    }

    public function bulkInsert($table, $data) {
        $lastInsertIds = [];
        try {
            $this->pdo->beginTransaction();
            foreach ($data as $row) {
                $lastInsertId = $this->insert($table, $row);
                $lastInsertIds[] = $lastInsertId;
            }
            $this->pdo->commit();
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            $this->ErrorMessage = "Bulk Insert Error: " . $e->getMessage();
        }
        return $lastInsertIds;
    }

    public function update($table, $data, $where) {
        try {
            $columns = array_keys($data);
            $setClause = implode(", ", array_map(function($col) { return "$col = ?"; }, $columns));
            $whereClause = implode(" AND ", array_map(function($col) { return "$col = ?"; }, array_keys($where)));
            $sql = "UPDATE $table SET $setClause WHERE $whereClause";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(array_merge(array_values($data), array_values($where)));
        } catch (PDOException $e) {
            $this->ErrorMessage = "Update Error: " . $e->getMessage();
        }
    }

    public function bulkUpdate($table, $data, $whereColumn) {
        $totalUpdated = 0;
        try {
            $this->pdo->beginTransaction();
            foreach ($data as $row) {
                $where = [$whereColumn => $row[$whereColumn]];
                $totalUpdated += $this->update($table, $row, $where);
            }
            $this->pdo->commit();
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            $this->ErrorMessage = "Bulk Update Error: " . $e->getMessage();
            $totalUpdated = 0;
        }
        return $totalUpdated;
    }

    public function delete($table, $where) {
        try {
            $whereClause = implode(" AND ", array_map(function($col) { return "$col = ?"; }, array_keys($where)));
            $sql = "DELETE FROM $table WHERE $whereClause";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(array_values($where));
        } catch (PDOException $e) {
            $this->ErrorMessage = "Delete Error: " . $e->getMessage();
        }
    }

    public function read($table, $where = []) {
        $sql = "SELECT * FROM $table";
        try {
            if (!empty($where)) {
                $whereClause = implode(" AND ", array_map(function($col) { return "$col = ?"; }, array_keys($where)));
                $sql .= " WHERE $whereClause";
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_values($where));
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->ErrorMessage = "Read Error: " . $e->getMessage();
        }
    }

    public function table($query, $option = 1) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();

            if ($option == 2) {
                return $stmt->fetchAll(PDO::FETCH_NUM);
            } else {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $this->ErrorMessage = "Table Error: " . $e->getMessage();
        }
    }

    public function execute($query, $params = []) {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->ErrorMessage = "Execute Error: " . $e->getMessage();
        }
    }

    public function selectValue($query) {
        try {
            $row = $this->pdo->query($query)->fetch(PDO::FETCH_NUM);
            return $row[0];
        } catch (PDOException $e) {
            $this->ErrorMessage = "Select Value Error: " . $e->getMessage();
        }
    }

    public function excelTable($query, $option = 1) {
        $data = [];
        try {
            $stmt = $this->pdo->query($query);
            $fetchMode = $option == 2 ? PDO::FETCH_NUM : PDO::FETCH_ASSOC;

            while ($row = $stmt->fetch($fetchMode)) {
                $data[] = $row;
            }
        } catch (PDOException $e) {
            $this->ErrorMessage = "Excel Table Error: " . $e->getMessage();
        }
        return $data;
    }
}

?>