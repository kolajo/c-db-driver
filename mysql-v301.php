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
*   Cleavey MySQL Database driver version 3.0.1
* 
*  
*/
class MysqlPdo {
    private $pdo;
    private $host = 'localhost';
    private $db = 'testa_event';
    private $user = 'root';
    private $pass = '';
    private $charset = 'utf8mb4';
    #
    public $ErrorMessage;
    public $ConnMsg;

    public function __construct($host,$database,$user,$pass) {
        #
        $this->user = $user;
        $this->pass = $pass;
        $this->db = $database;
        $this->host = $host;
        #
        $dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
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
    /*
     *  insert data into table by calling table name and data as array, key and value pair, 
     *  key is column name, value is value insert(table_name,data ["id"=>2,"name"=>"Matthew"])
     *  return autoincrement ID created
     */
    public function insert($table, $data) {
        try{
            $columns = implode(", ", array_keys($data));
            $placeholders = implode(", ", array_fill(0, count($data), '?'));
            $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            #
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array_values($data));
            return $this->pdo->lastInsertId();
        }catch (PDOException $e){
            $this->ErrorMessage = "Query Error: ".$e;
        }
    }
    /*
     *  insert bulk data into table in transaction mode by calling table name and data as array, key and value pair, 
     *  key is column name, value is value insert(table_name,data [["id"=>1,"name"=>"Ben"],["id"=>2,"name"=>"Matthew"])
     *  return array list of the autoincrement ID created
     */
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
    /*
     *  update table row by calling table name, data as array, key and value pair, and where condition array
     *  $data = ['first_name' => 'Israel', 'middle_name' => 'Joseph', 'last_name' => 'David'];
     *  $where = ['personid'=>19];
     *  return total row updated, usually 1
     */
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
    /*
     *  update bulk table row in transaction mode by calling table name, data as array, key and value pair, and where column
     *  $data = [['personid'=>1, 'first_name' => 'John'],['personid'=>2, 'first_name' => 'Matthew']];
     *  $where = 'personid';
     *  returns count of total successfully updated
     *  
     */
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
            $totalUpdated = 0;  //  empty total updated due to rollback
        }
        return $totalUpdated;
    }
    /*
     *  Delete row by calling table and where array 
     *  delete(table_name,['personid'=>1])
     *  returns count of total delete
     */
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
    /*
     *  Read table by calling table name read(table_name,where condition [id=>1])
     *  return 2 dimensional array
     */
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
    /*
     *  Read table by query table(query)
     *  Return option 2 to return numeric array, 1 or empty to return 2 dimensional array
     */
    public function table($query, $option = 1){
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(); // Execute the prepared statement

            if ($option == 2) {
                return $stmt->fetchAll(PDO::FETCH_NUM);
            } else {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $this->ErrorMessage = "Query Error: " . $e->getMessage();
            return false; // Return false on error
        }
    }
    /*
     *  Read table by query execute(query, data in the query)
     *  This enables you to send in raw query without adding data with the query to avoid injection alternative to table
     *  Return data is always in 2 dimensional array
     */
    public function execute($query, $params = []) {
        try{
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }catch (PDOException $e){
            $this->ErrorMessage = "Query Error: ".$e;
        }
    }
     /*
     *  Read specific value selectValue(Query)
     *  Return string result
     */
    public function selectValue($query){
        try{
            $row = $this->pdo->query($query)->fetch(PDO::FETCH_NUM);
            return $row[0];
        }catch (PDOException $e){
            $this->ErrorMessage = "Query Error: ".$e;
        }
    }
}
/*  
    Delivers database class
*/
function GetMysqlDatabase()
{
    $host = "localhost";
    $database = "testa_event";
    $user = "root";      //
    $pass = "";       //

    return new MysqlPdo($host,$database,$user,$pass);
}

class DbHelper{
    //  Echo JSON Tablular Data
    public static function EchoJsonTable($query)
    {
        $c = GetMysqlDatabase();
        $data = $c->table($query);
        echo json_encode($data);
    }
    //  Return JSON Tablular Data
    public static function GetJsonTable($query)
    {
        $c = GetMysqlDatabase();
        $data = $c->table($query);
        return json_encode($data);
    }
    //  Return 2 dimensional Array Data
    public static function Table($query)
    {
        $c = GetMysqlDatabase();
        return $c->table($query);
    }
    //  Return 2 dimensional Array Data
    public static function IntTable($query)
    {
        $c = GetMysqlDatabase();
        return $c->table($query, 2);
    }
    //  Return boolean if data found or not
    public static function Count($query)
    {
        if(count(DbHelper::Table($query)))
        {return true;}
        return false;
    }
    //  Echo single result 
    public static function EchoScalar($query)
    {
        $c = GetMysqlDatabase();
        echo $c->selectValue($query);
    }
    //  Return sinle result
    public static function GetScalar($query)
    {
        $c = GetMysqlDatabase();
        return $c->selectValue($query);
    }
    //  Insert array data returns auto gen ID
    public static function Insert($table, $data)
    {
        $c = GetMysqlDatabase();
        return $c->insert($table,$data);
    }
    //  Insert many data in transaction mode returns array list of auto gen ID
    public static function InsertBulk($table, $data)
    {
        $c = GetMysqlDatabase();
        return $c->bulkInsert($table,$data);
    }
    //  Update array data returns count of updated row
    public static function Update($table, $data, $where)
    {
        $c = GetMysqlDatabase();
        return $c->update($table,$data,$where);
    }
    //  Update many data in transaction mode returns count of updated rows
    public static function UpdateBulk($table, $data, $whereColumn)
    {
        $c = GetMysqlDatabase();
        return $c->bulkUpdate($table,$data,$whereColumn);
    }
    //  Delete C Entry class return total number of deleted rows 
    public static function Delete($table,$where)
    {
        $c = GetMysqlDatabase();
        return $c->delete($table,$where);
    }
    // Execute Raw Query with array of data to bind
    public static function Execute($query, $data = [])
    {
        $c = GetMysqlDatabase();
        return $c->execute($query,$data);
    }
}
   /*
    *
    *  Data management library
    *  
    */
class DataLib
{
    /*
    *  Slice the column
    */
    public static function Column($data, $number = 0)
    {
        $mode = array();
        foreach($data as $d)
        {
            $mode[] = $d[$number];
        }
        return $mode;
    }
    /*
    *  Slice column and onvert into Int array
    */
    public static function ColumnToInt($data, $number = 0)
    {
        $mode = array();
        foreach($data as $d)
        {
            $mode[] = intval($d[$number]);
        }
        return $mode;
    }
    /*
    *  Slice column and onvert into Float array
    */
    public static function ColumnToFloat($data, $number = 0)
    {
        $mode = array();
        foreach($data as $d)
        {
            $mode[] = floatval($d[$number]);
        }
        return $mode;
    }
    public static function Row($data, $number = 0)
    {
        return $data[$number];
    }
}
?>
