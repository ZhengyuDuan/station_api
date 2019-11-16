<?php
class sensors{
 
    // database connection and table name
    private $conn;
    private $table_name = "sensors";
 
    // object properties
    public $sensorID;
    public $sensorType;
    public $sensorStatus;
 
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    // get all infomation for sensor list.
    // TODO: add sensor newset data in returned values;
    function readSensors(){
        echo time();
        // select all query
        $query = "SELECT sensorID,sensorType,sensorStatus FROM
                    " . $this->table_name ;
     
        // prepare query statement
        $stmt = $this->conn->prepare($query);
     
        // execute query
        $stmt->execute();
     
        return $stmt;
    }

    // read all data from sensors in this edge station
    // this function will get current data
    function read(){

    }

    // register new sensor in this edge station
    function register(){

        // query to insert record
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    sensorType=:sensorType";
     // prepare query
        $stmt = $this->conn->prepare($query);
        // echo htmlspecialchars(strip_tags($this->sensorStatus));
        // echo $query;
        // sanitize
        $this->sensorID=1;
        $this->sensorType=htmlspecialchars(strip_tags($this->sensorType));
        $this->sensorStatus=1;

        
        // bind values
        // $stmt->bindParam(":sensorID", $this->sensorID);
        $stmt->bindParam(":sensorType", $this->sensorType);
        // $stmt->bindParam(":sensorStatus", $this->sensorStatus);
        // print"$stmt";
        // execute query
        if($stmt->execute()){

            //get last inserted sensorID;
            $query = "SELECT LAST_INSERT_ID()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();               
            $lastID = $stmt->fetchColumn();

            //create new table for new sensor;
            $query = "CREATE TABLE sensor_data_".$lastID."(time int not null, data JSON not null, PRIMARY KEY (time));";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return true;
        }
     
        return false;
    }

    // read one specific sensor from sensor list in this edge station
    // this function returns current/lastest data from database
    // or request(in demo case, generate one) current data and return, also stores into db
    function readOne(){
    }

    // get history data for a period of time of ONE sensor.
    function readHistory(){
    }

    // update edge station status to stop
    function stop(){
    }

    // update edge station status to running
    function start(){
    }

    // update sensor status
    // TODO: might change to two function: sensorStop/sensorStart
    function update(){
    }

    // delete sensor from sensor list
    function delete(){
    }

    // IMPORTANT
    // this function will clear all infomation in database
    // including all sensor data tables
    // except sensor list table (or GPS ?)
    function initialize(){
        $query = "SET foreign_key_checks = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $query = "SHOW TABLES";
        $stmt = $this->conn->prepare($query);

        if($stmt->execute()){
            // while ($row = $stmt->fetch()){
            //     // if(strcmp($row[0],"sensors")==0)continue;
            //     $query = "DROP TABLE IF EXISTS ".$row[0];

            //     $stmt = $this->conn->prepare($query);
            //     $stmt->execute();

            //     echo $row[0].",\n";
            //     $query = "SHOW TABLES";
            //     $stmt = $this->conn->prepare($query);
            //     $stmt->execute();

            // }
            $rows=$stmt->fetchAll();
            foreach ($rows as $row) {
                // echo $row[0]."\n";
                if(strcmp($row[0],"sensors")==0 ||
                    strcmp($row[0],"info")==0)continue;
                $query = "DROP TABLE IF EXISTS ".$row[0];

                $stmt = $this->conn->prepare($query);
                $stmt->execute();

                // echo $row[0].",\n";
                // $query = "SHOW TABLES";
                // $stmt = $this->conn->prepare($query);
                // $stmt->execute();
            }
        }else{
            echo "Failed to initialize";
        }
        $query = "SET foreign_key_ckecks = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $query = "TRUNCATE TABLE sensors";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        // // ----------------------------------------------------
        // // add back empty tables for further testing
        // $query = "CREATE TABLE info(stationID int(10) not null,orderID int(10), status varchar(32),PRIMARY KEY (`stationID`))";
        // $stmt = $this->conn->prepare($query);
        // $stmt->execute();

        // $query = "CREATE TABLE sensors(sensorID int not null, sensorType int not null, sensorStatus int not null, PRIMARY KEY (sensorID))";
        // $stmt = $this->conn->prepare($query);
        // $stmt->execute();
   
        // // insert test data
        // $query = "INSERT INTO sensors VALUES(111,1,1);";
        // $query .= "CREATE TABLE sensor_data_111(time_stamp timestamp not null, data1 float not null, data2 float not null, PRIMARY KEY (time_stamp));";
        // $query .= "INSERT INTO sensors VALUES(14,2,4);";
        // $query .= "CREATE TABLE sensor_data_14(time_stamp timestamp not null, data1 float not null, data2 float not null, PRIMARY KEY (time_stamp))";
        // $stmt = $this->conn->prepare($query);
        // $stmt->execute();
        // // ----------------------------------------------------

        return true;
    }

    // TEST ONLY
    // this function generats random data for each sensor on current time
    function generateData(){
        $currentTime = time();
        $currentID;
        $currentData;

        //TODO : generate data for all sensors

        $query = "SELECT * FROM sensors";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $rows=$stmt->fetchAll();
        // $rows=$stmt->fetch();
        foreach ($rows as $row) {
            // not generate new data if the sensor is not running.
            if($row['sensorStatus']==0)continue;
            // generate data depends on sensor type
            // gps sensor has 2 values;
            // others have one value;
            $currentID = $row['sensorID'];
            if($row['sensorType']==0){
                $longitude = rand(10000,2000000)/10000;
                $latitude = rand(10000,2000000)/10000;
                $currentData = array(
                    "longitude"=>$longitude,
                    "latitude"=>$latitude
                );
            }else{

                $data = rand(1,1000)/100;
                $currentData = array(
                    "value"=>$data
                );
            }
            $jsonData = json_encode($currentData);
            $query = "INSERT INTO sensor_data_".$currentID." SET time=".$currentTime.", data = '".$jsonData."'; ";
            $stmt = $this->conn->prepare($query);
            echo "sensor".$currentID." generated data: ".$jsonData."\n";
            if(!$stmt->execute())return false;
        }
        return true;
    }
}
?>