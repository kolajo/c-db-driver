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
*   Cleavey Mongo DB Database driver version 3.0.1
* 
*  
*/
class MongoDbManager {
    private $client;
    private $database;
    #
    public $ErrorMessage;
    public $ConnMsg;

    public function __construct($host, $database, $user = '', $pass = '') {
        #
        $this->database = $database;
        $dsn = "mongodb://$host";
        
        if ($user && $pass) {
            $dsn = "mongodb://$user:$pass@$host";
        }

        try {
            $this->client = new MongoDB\Client($dsn);
            $this->database = $this->client->$database;
        } catch (Exception $e) {
            $this->ErrorMessage = "Connection Error: " . $e->getMessage();
        }
    }

    public function insert($collection, $data) {
        try {
            $result = $this->database->$collection->insertOne($data);
            return $result->getInsertedId();
        } catch (Exception $e) {
            $this->ErrorMessage = "Insert Error: " . $e->getMessage();
        }
    }

    public function bulkInsert($collection, $data) {
        try {
            $result = $this->database->$collection->insertMany($data);
            return $result->getInsertedIds();
        } catch (Exception $e) {
            $this->ErrorMessage = "Bulk Insert Error: " . $e->getMessage();
        }
    }

    public function update($collection, $filter, $data) {
        try {
            $result = $this->database->$collection->updateOne($filter, ['$set' => $data]);
            return $result->getModifiedCount();
        } catch (Exception $e) {
            $this->ErrorMessage = "Update Error: " . $e->getMessage();
        }
    }

    public function bulkUpdate($collection, $data, $whereColumn) {
        $totalUpdated = 0;
        try {
            foreach ($data as $row) {
                $filter = [$whereColumn => $row[$whereColumn]];
                $totalUpdated += $this->update($collection, $filter, $row);
            }
        } catch (Exception $e) {
            $this->ErrorMessage = "Bulk Update Error: " . $e->getMessage();
            $totalUpdated = 0;
        }
        return $totalUpdated;
    }

    public function delete($collection, $filter) {
        try {
            $result = $this->database->$collection->deleteOne($filter);
            return $result->getDeletedCount();
        } catch (Exception $e) {
            $this->ErrorMessage = "Delete Error: " . $e->getMessage();
        }
    }

    public function read($collection, $filter = []) {
        try {
            $cursor = $this->database->$collection->find($filter);
            return iterator_to_array($cursor);
        } catch (Exception $e) {
            $this->ErrorMessage = "Read Error: " . $e->getMessage();
        }
    }

    public function table($collection, $filter = [], $options = []) {
        try {
            $cursor = $this->database->$collection->find($filter, $options);
            return iterator_to_array($cursor);
        } catch (Exception $e) {
            $this->ErrorMessage = "Table Error: " . $e->getMessage();
        }
    }

    public function execute($collection, $command, $params = []) {
        try {
            $command = new MongoDB\Driver\Command([$command => $params]);
            $cursor = $this->client->getManager()->executeCommand($this->database, $command);
            return $cursor->toArray();
        } catch (Exception $e) {
            $this->ErrorMessage = "Execute Error: " . $e->getMessage();
        }
    }

    public function selectValue($collection, $filter = [], $field = '') {
        try {
            $result = $this->database->$collection->findOne($filter, ['projection' => [$field => 1]]);
            return $result[$field] ?? null;
        } catch (Exception $e) {
            $this->ErrorMessage = "Select Value Error: " . $e->getMessage();
        }
    }

    public function excelTable($collection, $filter = [], $options = []) {
        $data = [];
        try {
            $cursor = $this->database->$collection->find($filter, $options);
            foreach ($cursor as $document) {
                $data[] = $document;
            }
        } catch (Exception $e) {
            $this->ErrorMessage = "Excel Table Error: " . $e->getMessage();
        }
        return $data;
    }
}

?>