<?php
    include_once("mysql-v301.php");
    //
    // $db = new MysqlPdo();
    //
    //  Testing insert data
    /*$data = ['first_name' => 'John', 'middle_name' => 'Maddison', 'last_name' => 'Solomon'];
    $id = $db->insert('person',$data);
    if($id){
        echo "Created successfully, ID: $id";
    }else{
        echo "Unable to save record at the moment.";
    }*/
    //
    //  Testing bulk insert
    /*$data = [
                ['first_name' => 'John', 'middle_name' => 'Maddison', 'last_name' => 'Solomon'],
                ['first_name' => 'Matthew', 'middle_name' => 'Benson', 'last_name' => 'Kingsley'],
                ['first_name' => 'Mark', 'middle_name' => 'Anthony', 'last_name' => 'Thomas'],
                ['first_name' => 'Israel', 'middle_name' => 'Joseph', 'last_name' => 'David']
    ];
    $ids = $db->bulkInsert('person',$data);
    if(count($data)){
        foreach($ids as $i){
            echo "Created successfully, ID: ".$i. "<br>";
        }
    }else{
        echo "Unable to save record at the moment";
    }
    //
    //  == Update ==
    //
    $data = ['first_name' => 'Israel', 'middle_name' => 'Joseph', 'last_name' => 'David'];
    $where = ['personid'=>19];
    $id = $db->update('person',$data, $where);
    echo $id;
    //
    //  == Bulk Update ==
    $data = [
        ['personid'=>1, 'first_name' => 'John', 'middle_name' => 'Maddison', 'last_name' => 'Solomon'],
        ['personid'=>2, 'first_name' => 'Matthew', 'middle_name' => 'Benson', 'last_name' => 'Kingsley'],
        ['personid'=>3, 'first_name' => 'Mark', 'middle_name' => 'Anthony', 'last_name' => 'Thomas'],
        ['personid'=>4, 'first_name' => 'Israel', 'middle_name' => 'Joseph', 'last_name' => 'David']
    ];
    $totalUpdated = $db->bulkUpdate('person',$data,'personid');
    echo "Total data updated: ".$totalUpdated;
    //
    //  == Delete ==
    $where = ['personid' => 8];
    $success = $db->delete('person',$where);
    if($success){
        echo "Record deleted successfully";
    }else{
        echo "Unable to delete record at the moment.";
    }
    //
    //  == Read ==
    $result = $db->read('person',['personid'=>1]);
    if(!empty($result)){
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }else{
        echo "No records found.";
    }
    //
    //  == Execute ==
    $query = "SELECT * FROM person WHERE personid > ?";
    $result = $db->execute($query,[20]);
    if(!empty($result)){
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }else{
        echo "No records found.";
    }
    //
    //  == Execute With error handling ==
    try{
        $query = "SELECT * FROM person WHERE first_name LIKE ? OR last_name LIKE ?";
        $result = $db->execute($query,['%Abu%','%so%']);
        if(!empty($result)){
            echo "<pre>";
            print_r($result);
            echo "</pre>";
        }else{
            echo "No records found.";
        }
    } catch (PDOException $e){
        //  handle SQL error
        echo 'SQL error: '.$e->getMessage();
    } catch (Exception $e) {
        //  Handle general errors
        echo "General error: ".$e->getMessage();
    }
        */

    #
    #   =============================
    #   ====== Helper Testing =======
    #   =============================
    #
    //
    // ===== GET JSON
    //$data = DbHelper::GetJsonTable("SELECT * FROM `person`");
    //echo $data;
    /*
    $query = "SELECT CONCAT_WS(' ',first_name,middle_name,last_name) FROM `person` WHERE personid = 30";
    $data = DbHelper::GetScalar($query);
    echo $data;
    
    //
    //  Insert data
    $data = ['first_name'=>'Kalasiwa','middle_name'=>'Maladogni','last_name'=>'Madaki'];
    $id = DbHelper::Insert('person',$data);
    if($id){
        echo "User created successfully with ID: $id";
    }else{
        echo "Unable to create data at the moment";
    }
    //
    //  Bulk insert
    $data = [['first_name'=>'Kalasiwa','middle_name'=>'Maladogni','last_name'=>'Madaki'],
    ['first_name'=>'Silas','middle_name'=>'Monday','last_name'=>'Madaki'],
    ['first_name'=>'Brosley','middle_name'=>'Monday','last_name'=>'Solomon'],
    ['first_name'=>'Hammed','middle_name'=>'Hassan','last_name'=>'Abdulwahab']];
    $ids = DbHelper::InsertBulk('person',$data);
    echo json_encode($ids);
    //
    //  Update function
    $data = ['first_name'=>'James','middle_name'=>'Thomas','last_name'=>'Madaki'];
    if(DbHelper::Update('person',$data,['personid'=>31])){
        echo "update was successful";
    }else{
        echo "Unable to update at the moment";
    }
    //
    //  Update Bulk
    $data = $data = [['personid'=>5,'first_name'=>'Kalasiwa','middle_name'=>'Maladogni','last_name'=>'Madaki'],
    ['personid'=>9,'first_name'=>'Silas','middle_name'=>'Monday','last_name'=>'Madaki'],
    ['personid'=>10,'first_name'=>'Brosley','middle_name'=>'Monday','last_name'=>'Solomon'],
    ['personid'=>11,'first_name'=>'Hammed','middle_name'=>'Hassan','last_name'=>'Abdulwahab']];
    $res = DbHelper::UpdateBulk('person',$data,'personid');
    if($res){
        echo "Bulk update was successful. $res";
    }else{
        echo "Unable to update records";
    }*/
?>