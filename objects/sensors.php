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
        // echo time();
        // select all query
        $query = "SELECT sensorID,sensorType,sensorStatus FROM
                    " . $this->table_name ;
     
        // prepare query statement
        $stmt = $this->conn->prepare($query);
     
        // execute query
        $stmt->execute();
     
        return $stmt;
    }

    function getStatus(){
        $query = "SELECT status from info;" ;
     
        // prepare query statement
        $stmt = $this->conn->prepare($query);
     
        // execute query
        $stmt->execute();
     
        return $stmt->fetchColumn();
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
            $query = "CREATE TABLE sensor_data_".$lastID."(time int not null, data varchar(256) not null, PRIMARY KEY (time));";
            $stmt = $this->conn->prepare($query);
            echo $query;
            return $stmt->execute();
        }
     
        return false;
    }

    // read one specific sensor from sensor list in this edge station
    // this function returns current/lastest data from database
    // or request(in demo case, generate one) current data and return, also stores into db
    function readOne(){
    }

    // get one sensor data history,
    // retrunes last 10 data
    function getSensorData(){
        $amount = 10;
        $result = array();
        $result['sensorData']=array();
        $sensors_item = array();

        $currentSensorID=htmlspecialchars(strip_tags($this->sensorID));
        $query = "SELECT * FROM sensor_data_".$currentSensorID." ORDER BY time DESC LIMIT 10;";
        $stmt = $this->conn->prepare($query);
        if($stmt->execute()){
            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                $sensors_item['number']=$amount;
                $sensors_item['time']=$row['time'];
                $sensors_item['data']=json_decode($row['data'],true);
                array_push($result["sensorData"], $sensors_item);
                $amount--;
            }
            return $result;
        }
        return false;
    }

    // get history data for a period of time of ONE sensor.
    // function readHistory(){
    // }

    // update edge station status to stop
    function stop(){

        $query = "UPDATE info SET status = 0;";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }

    // update edge station status to running
    function start(){

        $query = "UPDATE info SET status = 1;";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
        // return true;
    }

    // update sensor status
    function sensorStart($sensorID){
        $query = "UPDATE sensors SET sensorStatus = 1 WHERE sensorID = ".$sensorID.";";
        $stmt = $this->conn->prepare($query);
        // echo $query;
        return $stmt->execute();
    }

    // update sensor status
    function sensorStop($sensorID){
        $query = "UPDATE sensors SET sensorStatus = 0 WHERE sensorID = ".$sensorID.";";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
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
            $rows=$stmt->fetchAll();
            foreach ($rows as $row) {
                if(strcmp($row[0],"sensors")==0 ||
                    strcmp($row[0],"info")==0)continue;
                $query = "DROP TABLE IF EXISTS ".$row[0];

                $stmt = $this->conn->prepare($query);
                $stmt->execute();

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
        return true;
    }

    // TEST ONLY
    // this function generats random data for each sensor on current time
    function generateData(){


        $query = "SELECT status FROM info;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        if($stmt->fetchColumn()==0)return false;


        $currentTime = time();
        $currentID;
        $currentData;
        $returnData = array();
        $returnData["time"] = $currentTime;
        $returnData["sensorsData"] = array();
        $sensors_item = array();
        $sensors_item["sensorData"] = array();

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
            // echo "sensor".$currentID." generated data: ".$jsonData."\n";
            $sensors_item=array(
                "sensorID" => $currentID,
                "sensorType" => $row['sensorType']
                // "sensorData" => json_decode($jsonda,true);
            );
            // $sensor_item["sensorData"] = array();
            $sensors_item["sensorData"] =  json_decode($jsonData,true);
            // array_push($sensors_item["sensorData"], json_decode($jsonData,true));
            array_push($returnData["sensorsData"], $sensors_item);
            // echo json_encode($returnData);
            if(!$stmt->execute())return false;
        }
        return $returnData;
    }

    function setupStation($stationID, $order){
        $query = "SELECT count(*) FROM info;";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        // echo $stmt->fetchColumn();


        if($stmt->fetchColumn()!=0){
            return false;
        }
        // echo $stmt->fetchAll()[0];
        $query = "INSERT INTO info SET stationID=".$stationID.", orderID = ".$order.";";
        // echo $query;
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }

    function changeOrderID($newID){

        $query = "UPDATE info SET orderID = ".$newID.";";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }
}
?>