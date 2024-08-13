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
*   Cleavey MS-SQL Database driver version 3.0.1
* 
*  
*/
class MssqlPdo {
    private $pdo;
    private $host = 'localhost';
    private $db = 'testa_event';
    private $user = 'root';
    private $pass = '';
    private $charset = 'utf8';
    #
    public $ErrorMessage;
    public $ConnMsg;

    public function __construct($host, $database, $user, $pass) {
        #
        $this->user = $user;
        $this->pass = $pass;
        $this->db = $database;
        $this->host = $host;
        #
        $dsn = "sqlsrv:Server=$this->host;Database=$this->db";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function insert($table, $data) {
        try{
            $columns = implode(", ", array_keys($data));
            $placeholders = implode(", ", array_fill(0, count($data), '?'));
            $sql = "INSERT INTO $table ($columns) VALUES ($placeholders); SELECT SCOPE_IDENTITY() AS id";
            #
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_values($data));
            return $stmt->fetchColumn(); // fetchColumn returns the first column of the first row in the result set.
        }catch (PDOException $e){
            $this->ErrorMessage = "Query Error: ".$e;
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
            throw $e;
        }
        return $lastInsertIds;
    }

    public function update($table, $data, $where) {
        try{
            $columns = array_keys($data);
            $setClause = implode(", ", array_map(function($col) { return "$col = ?"; }, $columns));
            $whereClause = implode(" AND ", array_map(function($col) { return "$col = ?"; }, array_keys($where)));
            #
            $sql = "UPDATE $table SET $setClause WHERE $whereClause";
            $stmt = $this->pdo->prepare($sql);
            #
            return $stmt->execute(array_merge(array_values($data), array_values($where)));
        }catch (PDOException $e){
            $this->ErrorMessage = "Query Error: ".$e;
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
            $this->ErrorMessage = "Query Error: ".$e;
            $totalUpdated = 0;
        }
        return $totalUpdated;
    }

    public function delete($table, $where) {
        try{
            $whereClause = implode(" AND ", array_map(function($col) { return "$col = ?"; }, array_keys($where)));
            $sql = "DELETE FROM $table WHERE $whereClause";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(array_values($where));
        }catch (PDOException $e){
            $this->ErrorMessage = "Query Error: ".$e;
        }
    }

    public function read($table, $where = []) {
        $sql = "SELECT * FROM $table";
        try{
            if (!empty($where)) {
                $whereClause = implode(" AND ", array_map(function($col) { return "$col = ?"; }, array_keys($where)));
                $sql .= " WHERE $whereClause";
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_values($where));
            return $stmt->fetchAll();
        }catch (PDOException $e){
            $this->ErrorMessage = "Query Error: ".$e;
        }
    }

    public function table($query, $option = 1){
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();

            if ($option == 2) {
                return $stmt->fetchAll(PDO::FETCH_NUM);
            } else {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $this->ErrorMessage = "Query Error: " . $e->getMessage();
            return false;
        }
    }

    public function execute($query, $params = []) {
        try{
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }catch (PDOException $e){
            $this->ErrorMessage = "Query Error: ".$e;
        }
    }

    public function selectValue($query){
        try{
            $row = $this->pdo->query($query)->fetch(PDO::FETCH_NUM);
            return $row[0];
        }catch (PDOException $e){
            $this->ErrorMessage = "Query Error: ".$e;
        }
    }

    public function excelTable($query, $option = 1){
        $data = [];
        if (!$this->pdo) {
            return $data;
        }

        try {
            $stm = $this->pdo->query($query);
            $fetchMode = $option == 2 ? PDO::FETCH_NUM : PDO::FETCH_ASSOC;

            while ($r = $stm->fetch($fetchMode)) {
                $data[] = $r;
            }
        } catch (PDOException $e) {
            $this->ErrorMessage = "Query Error: " . $e->getMessage();
        }

        return $data;
    }
}

?>