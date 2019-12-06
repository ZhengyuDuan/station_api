<?php
/*
GPS sensor
Speed sensor
Temperature Sensor
soil Moisture sensor
Oxygen sensor
Carbon Dioxide Sensor
*/
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
        $query = "SELECT sensorID,sensorType,sensorStatus FROM
                    " . $this->table_name ;
     
        // prepare query statement
        $stmt = $this->conn->prepare($query);
     
        // execute query
        $stmt->execute();
     
        return $stmt;
    }

    /*
    *   get station status
    *   return status from table info 
    */
    function getStationStatus(){
        $query = "SELECT status from info;" ;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    function changeStationStatus($newStatus){
        $query = "UPDATE INFO SET STATUS = ".$newStatus.";" ;
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }

    function getSensorStatus($sensorID){
        // CHECK IF SENSOR EXISTS
        $query = "SELECT COUNT(*) FROM SENSORS WHERE SENSORID = ".$sensorID.";";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        if($count == 0)return false;
        // PROCESS
        $query = "SELECT SENSORSTATUS from SENSORS WHERE SENSORID = ".$sensorID.";" ;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    function changeSensorStatus($sensorID, $status){
        // CHECK IF SENSOR EXISTS
        $query = "SELECT COUNT(*) FROM SENSORS WHERE SENSORID = ".$sensorID.";";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        if($count == 0)return false;
        //PROCESS
        $query = "UPDATE SENSORS SET SENSORSTATUS = ".$status." WHERE SENSORID = ".$sensorID.";" ;
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }

    // get one sensor data history,
    // retrunes last data
    function getSensorData(){
        $amount = 1;
        $result = array();
        $result['sensorID']=$this->sensorID;

        $currentSensorID=htmlspecialchars(strip_tags($this->sensorID));
        $query = "SELECT * FROM sensor_data_".$currentSensorID." ORDER BY time DESC LIMIT ".$amount.";";
        $stmt = $this->conn->prepare($query);
        if($stmt->execute()){
            $row = $stmt->fetch();
            $result['time']=$row['time'];
            $result['data']=json_decode($row['data']);

            return $result;

        }
        return false;
    }

    // register new sensor in this edge station
    function register($sensorID, $sensorType){
        //determine if same sensor type exists;
        $query = "SELECT COUNT(SENSORTYPE) FROM SENSORS WHERE SENSORTYPE = ".$sensorType.";";
        $stmt =  $this->conn->prepare($query);
        $stmt -> execute();
        $row = $stmt->fetch();
        if($row[0]!=0)return false;

        // query to insert record
        $query = "INSERT INTO
                    " . $this->table_name . "
                SET
                    sensorID =:sensorID,
                    sensorType=:sensorType";
        $stmt = $this->conn->prepare($query);
        

        $stmt->bindParam(":sensorID", $sensorID);
        $stmt->bindParam(":sensorType", $sensorType);

        if($stmt->execute()){
            // echo "sensor inserted";

            //create new table for new sensor;
            $query = "CREATE TABLE sensor_data_".$sensorID."(id int not null AUTO_INCREMENT, time int not null, data varchar(256) not null, PRIMARY KEY (id, time));";
            $stmt = $this->conn->prepare($query);
            
            return $stmt->execute();
        }
     
        return false;
    }



    function initial($stationID,$stationType,$orderID,$GPSID){
        // *********************************************************************
        // while initializing, 
        // delete all tables and clear info & sensors
        // register a GPS sensor with ID
        // insert machine type,order id, machine ID.
        // *********************************************************************

        $query = "SET foreign_key_checks = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $query = "SHOW TABLES";
        $stmt = $this->conn->prepare($query);

        if($stmt->execute()){
            $rows=$stmt->fetchAll();
            foreach ($rows as $row) {
                // if(strcmp($row[0],"sensors")==0 ||
                //     strcmp($row[0],"info")==0)continue;
                $query = "DROP TABLE IF EXISTS ".$row[0];

                $stmt = $this->conn->prepare($query);
                $stmt->execute();

            }
        }else{
            echo "Failed to clear tables;";
        }
        $query = "SET foreign_key_ckecks = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();


        //create info table if not exists;
        $query = "CREATE TABLE IF NOT EXISTS info(stationID int not null, stationType int not null, orderID varchar(30), status int not null DEFAULT 1 ,PRIMARY KEY (stationID));";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        //create sensors table if not exists;
        $query = "CREATE TABLE IF NOT EXISTS sensors(sensorID int not null, sensorType int not null, sensorStatus int not null DEFAULT 1, PRIMARY KEY (sensorID));";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        //insert infomation into info table
        $query = "INSERT INTO INFO SET STATIONID = ".$stationID.", stationType = ".$stationType.", orderID= ".$orderID.";";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $this->register($GPSID, 0);
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
                $longitude = rand(331431,338934)/1000000;
                $latitude = rand(-884717,-877522)/1000000;
                $currentData = array(
                    "longitude"=>$longitude+37,
                    "latitude"=>$latitude-121
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

    function generateDataForTime($startTime, $endTime, $period){

        //CHECK STATION STATUS
        $query = "SELECT STATUS FROM INFO;";
        $stmt =  $this->conn->prepare($query);
        $stmt -> execute();
        $row = $stmt->fetch();
        if($row[0]==0){
            echo "Station down, unable to generate data";
            return ;
        }

        $sensors = array();

        for($type = 0; $type < 6; $type ++){
            // CHECK IF SENSOR EXISTS
            $query = "SELECT COUNT(SENSORTYPE) FROM SENSORS WHERE SENSORTYPE = ".$type.";";
            $stmt =  $this->conn->prepare($query);
            $stmt -> execute();
            $row = $stmt->fetch();
            if($row[0]==0){
                continue;
            }

            // CHECK SENSOR STATUS
            $query = "SELECT SENSORSTATUS FROM SENSORS WHERE SENSORTYPE = ".$type.";";
            $stmt =  $this->conn->prepare($query);
            $stmt -> execute();
            $row = $stmt->fetch();
            if($row[0]==0)continue;

            array_push($sensors,$type);
        }


        for($i =$startTime; $i<=$endTime; $i+=$period){
            $return = array();
            //**************************************************************************
            // initialize return result;
            //**************************************************************************
            $return["time"]=$i;
            $return["sensors"] = array();
            foreach ($sensors as $type) {
            // echo "Attempting to generate data for type ".$type."...\n";
            array_push($return["sensors"], json_decode($this->generateDataByType($type,$i),true));
            }
            // $this->curl();
            echo json_encode($return);
            echo "\n";
        }   
        
        return true;
    }

    function generateDataByType($sensorType, $time){
    	switch($sensorType){
    		case 0:
    		// echo "Generating data for GPS Sensor at time ".$time;
    		return $this->generateGPSData($time);
    		case 1:
    		// echo "Generating data for Speed sensor at time ".$time;
            return $this->generateSpeedData($time);
    		break;
    		case 2:
    		// echo "Generating data for Temperature Sensor at time ".$time;
    		return $this->generateTempData($time);
            break;
    		case 3:
    		// echo "Generating data for Soil Moisture Sensor at time ".$time;
    		return $this->generatesoilMData($time);
            break;
    		case 4:
    		// echo "Generating data for Oxygen Sensor at time ".$time;
    		return $this->generateO2Data($time);
            break;
    		case 5:
    		// echo "Generating data for Carbon Dioxide Sensor at time ".$time;
    		return $this->generateCO2Data($time);
            break;
    		default:
    		break;

    	}
    }

    //type 0;
    function generateGPSData($time){
        $return = array();
        //**************************************************************************
        // initialize return result;
        //**************************************************************************

        //**************************************************************************
        // sensor type :0   -   GPS sensor
        // sensor data range:   longitude: 37.331431,37.338934
        // sensor data range:   latitude: -121.884717,-121.877522
        // sensor data random range: +-1
        //**************************************************************************
    	$query = "SELECT sensorID FROM sensors WHERE sensorType = 0;";
    	$stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row=$stmt->fetch();
        $currentID = $row[0];
        $return["id"]=$currentID;
	    //get last row infomation
	    $query = "SELECT MAX(ID) FROM SENSOR_DATA_".$currentID;
	    $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row=$stmt->fetch();
        $lastID = $row[0];
        //generate data
        //if its first data, totally random,
        //if not, generate data based on last generated data;
        if($lastID==null){
        	// echo "null test\n";
            $longitude = rand(331431,338934)/1000000;
            $latitude = rand(-884717,-877522)/1000000;
            $currentData = array(
                "longitude"=>$longitude+37,
                "latitude"=>$latitude-121
            );
            $jsonData = json_encode($currentData);
            $query = "INSERT INTO sensor_data_".$currentID." SET time=".$time.", data = '".$jsonData."'; ";
            $stmt = $this->conn->prepare($query);
            if($stmt->execute()){
                $return["data"]=json_decode($jsonData);
            }
        }else{
        	// echo "\nLAST INSERTED ID: ".$lastID."\n";
        	$query = "SELECT DATA FROM SENSOR_DATA_".$currentID." WHERE ID = ".$lastID.";";
		    $stmt = $this->conn->prepare($query);
		    $stmt->execute();
		    $row=$stmt->fetch();
		    $lastData = $row[0];
		    //get old data
		    $oldJSONData = json_decode($lastData,true);
		    $oldLongitude = $oldJSONData["longitude"]*1000000;
		    $oldLatitude = $oldJSONData["latitude"]*1000000;
		    //random two new values

		    $newLongitude = rand(max(37331431,$oldLongitude-1),min(37338934,$oldLongitude+1))/1000000;
		    // random a number
		    // $newLatitude = rand(max(-121884717,$oldLatitude-1),min(-121877522,$oldLatitude+1))/1000000;
		    // make it go as a linear
		    $newLatitude = ($oldLatitude-1)/1000000;
		    // echo "\nLa:\t".$newLatitude;

            $currentData = array(
                "longitude"=>$newLongitude,
                "latitude"=>$newLatitude
            );

		    $jsonData = json_encode($currentData);
            $query = "INSERT INTO sensor_data_".$currentID." SET time=".$time.", data = '".$jsonData."'; ";
            $stmt = $this->conn->prepare($query);
            if($stmt->execute()){
                $return["data"]=json_decode($jsonData);
            }
        }

        return json_encode($return);
        
    }

    //type 1;
    function generateSpeedData($time){
    	//get sensorID of sensorType speed
        $return = array();
        //**************************************************************************
        // initialize return result;
        //**************************************************************************
        $MIN = 20.0;
        $MAX = 50.0;
        $RANGE = 3.0;
        $sensorType = 1;
        //**************************************************************************
        // sensor type :1   -   speed sensor
        // sensor data range:   20-50
        // sensor data random range: +-3
        //**************************************************************************
    	$query = "SELECT sensorID FROM sensors WHERE sensorType = ".$sensorType.";";
    	$stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row=$stmt->fetch();
        $currentID = $row[0];
        $return["id"]=$currentID;
	    //get last row infomation
	    $query = "SELECT MAX(ID) FROM SENSOR_DATA_".$currentID;
	    $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row=$stmt->fetch();
        $lastID = $row[0];
        //generate data
        //if its first data, totally random,
        //if not, generate data based on last generated data;
        if($lastID==null){
        	//*******************************************************************************
        	//generate new random value
        	//*******************************************************************************
        	$value = rand($MIN*10,$MAX*10)/10;
            // echo "first value: ".$value;
            $currentData = array(
                "value"=>$value
            );
            $jsonData = json_encode($currentData);
            $query = "INSERT INTO sensor_data_".$currentID." SET time=".$time.", data = '".$jsonData."'; ";
            $stmt = $this->conn->prepare($query);
            if($stmt->execute()){
                $return["data"]=json_decode($jsonData);
            }
        }else{
        	// echo "\nLAST INSERTED ID: ".$lastID."\n";
        	$query = "SELECT DATA FROM SENSOR_DATA_".$currentID." WHERE ID = ".$lastID.";";
		    $stmt = $this->conn->prepare($query);
		    $stmt->execute();
		    $row=$stmt->fetch();
		    $lastData = $row[0];
		    // echo $lastData;
		    //get old data
		    $oldJSONData = json_decode($lastData,true);
		    $oldValue = $oldJSONData["value"];


		    $newValue = rand(max($MIN*10,($oldValue-$RANGE)*10),min($MAX*10,($oldValue+$RANGE)*10))/10;

            $currentData = array(
                "value"=>$newValue
            );

		    $jsonData = json_encode($currentData);
            $query = "INSERT INTO sensor_data_".$currentID." SET time=".$time.", data = '".$jsonData."'; ";
            $stmt = $this->conn->prepare($query);
            if($stmt->execute()){
                $return["data"]=json_decode($jsonData);
            }
        }
        return json_encode($return);
    }

    //type 2;
    function generateTempData($time){
        //get sensorID of sensorType speed
        $return = array();
        //**************************************************************************
        // initialize return result;
        //**************************************************************************
        $MIN = 20.0;
        $MAX = 50.0;
        $RANGE = 3.0;
        $sensorType = 2;
        //**************************************************************************
        // sensor type :1   -   speed sensor
        // sensor data range:   20-50
        // sensor data random range: +-3
        //**************************************************************************
        $query = "SELECT sensorID FROM sensors WHERE sensorType = ".$sensorType.";";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row=$stmt->fetch();
        $currentID = $row[0];
        $return["id"]=$currentID;
        //get last row infomation
        $query = "SELECT MAX(ID) FROM SENSOR_DATA_".$currentID;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row=$stmt->fetch();
        $lastID = $row[0];
        //generate data
        //if its first data, totally random,
        //if not, generate data based on last generated data;
        if($lastID==null){
            //*******************************************************************************
            //generate new random value
            //*******************************************************************************
            $value = rand($MIN*10,$MAX*10)/10;
            // echo "first value: ".$value;
            $currentData = array(
                "value"=>$value
            );
            $jsonData = json_encode($currentData);
            $query = "INSERT INTO sensor_data_".$currentID." SET time=".$time.", data = '".$jsonData."'; ";
            $stmt = $this->conn->prepare($query);
            if($stmt->execute()){
                $return["data"]=json_decode($jsonData);
            }
        }else{
            // echo "\nLAST INSERTED ID: ".$lastID."\n";
            $query = "SELECT DATA FROM SENSOR_DATA_".$currentID." WHERE ID = ".$lastID.";";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row=$stmt->fetch();
            $lastData = $row[0];
            // echo $lastData;
            //get old data
            $oldJSONData = json_decode($lastData,true);
            $oldValue = $oldJSONData["value"];


            $newValue = rand(max($MIN*10,($oldValue-$RANGE)*10),min($MAX*10,($oldValue+$RANGE)*10))/10;

            $currentData = array(
                "value"=>$newValue
            );

            $jsonData = json_encode($currentData);
            $query = "INSERT INTO sensor_data_".$currentID." SET time=".$time.", data = '".$jsonData."'; ";
            $stmt = $this->conn->prepare($query);
            if($stmt->execute()){
                $return["data"]=json_decode($jsonData);
            }
        }
        return json_encode($return);
        
    }

    //type 3;
    function generatesoilMData($time){
        //get sensorID of sensorType speed
        $return = array();
        //**************************************************************************
        // initialize return result;
        //**************************************************************************
        $MIN = 20.0;
        $MAX = 50.0;
        $RANGE = 3.0;
        $sensorType = 3;
        //**************************************************************************
        // sensor type :1   -   speed sensor
        // sensor data range:   20-50
        // sensor data random range: +-3
        //**************************************************************************
        $query = "SELECT sensorID FROM sensors WHERE sensorType = ".$sensorType.";";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row=$stmt->fetch();
        $currentID = $row[0];
        $return["id"]=$currentID;
        //get last row infomation
        $query = "SELECT MAX(ID) FROM SENSOR_DATA_".$currentID;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row=$stmt->fetch();
        $lastID = $row[0];
        //generate data
        //if its first data, totally random,
        //if not, generate data based on last generated data;
        if($lastID==null){
            //*******************************************************************************
            //generate new random value
            //*******************************************************************************
            $value = rand($MIN*10,$MAX*10)/10;
            // echo "first value: ".$value;
            $currentData = array(
                "value"=>$value
            );
            $jsonData = json_encode($currentData);
            $query = "INSERT INTO sensor_data_".$currentID." SET time=".$time.", data = '".$jsonData."'; ";
            $stmt = $this->conn->prepare($query);
            if($stmt->execute()){
                $return["data"]=json_decode($jsonData);
            }
        }else{
            // echo "\nLAST INSERTED ID: ".$lastID."\n";
            $query = "SELECT DATA FROM SENSOR_DATA_".$currentID." WHERE ID = ".$lastID.";";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row=$stmt->fetch();
            $lastData = $row[0];
            // echo $lastData;
            //get old data
            $oldJSONData = json_decode($lastData,true);
            $oldValue = $oldJSONData["value"];


            $newValue = rand(max($MIN*10,($oldValue-$RANGE)*10),min($MAX*10,($oldValue+$RANGE)*10))/10;

            $currentData = array(
                "value"=>$newValue
            );

            $jsonData = json_encode($currentData);
            $query = "INSERT INTO sensor_data_".$currentID." SET time=".$time.", data = '".$jsonData."'; ";
            $stmt = $this->conn->prepare($query);
            if($stmt->execute()){
                $return["data"]=json_decode($jsonData);
            }
        }
        
        return json_encode($return);
    }

    //type 4;
    function generateO2Data($time){
        //get sensorID of sensorType speed
        $return = array();
        //**************************************************************************
        // initialize return result;
        //**************************************************************************
        $MIN = 20.0;
        $MAX = 50.0;
        $RANGE = 3.0;
        $sensorType = 4;
        //**************************************************************************
        // sensor type :1   -   speed sensor
        // sensor data range:   20-50
        // sensor data random range: +-3
        //**************************************************************************
        $query = "SELECT sensorID FROM sensors WHERE sensorType = ".$sensorType.";";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row=$stmt->fetch();
        $currentID = $row[0];
        $return["id"]=$currentID;
        //get last row infomation
        $query = "SELECT MAX(ID) FROM SENSOR_DATA_".$currentID;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row=$stmt->fetch();
        $lastID = $row[0];
        //generate data
        //if its first data, totally random,
        //if not, generate data based on last generated data;
        if($lastID==null){
            //*******************************************************************************
            //generate new random value
            //*******************************************************************************
            $value = rand($MIN*10,$MAX*10)/10;
            // echo "first value: ".$value;
            $currentData = array(
                "value"=>$value
            );
            $jsonData = json_encode($currentData);
            $query = "INSERT INTO sensor_data_".$currentID." SET time=".$time.", data = '".$jsonData."'; ";
            $stmt = $this->conn->prepare($query);
            if($stmt->execute()){
                $return["data"]=json_decode($jsonData);
            }
        }else{
            // echo "\nLAST INSERTED ID: ".$lastID."\n";
            $query = "SELECT DATA FROM SENSOR_DATA_".$currentID." WHERE ID = ".$lastID.";";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row=$stmt->fetch();
            $lastData = $row[0];
            // echo $lastData;
            //get old data
            $oldJSONData = json_decode($lastData,true);
            $oldValue = $oldJSONData["value"];


            $newValue = rand(max($MIN*10,($oldValue-$RANGE)*10),min($MAX*10,($oldValue+$RANGE)*10))/10;

            $currentData = array(
                "value"=>$newValue
            );

            $jsonData = json_encode($currentData);
            $query = "INSERT INTO sensor_data_".$currentID." SET time=".$time.", data = '".$jsonData."'; ";
            $stmt = $this->conn->prepare($query);
            if($stmt->execute()){
                $return["data"]=json_decode($jsonData);
            }
        }
        
        return json_encode($return);
    }

    //type 5;
    function generateCO2Data($time){
        //get sensorID of sensorType speed
        $return = array();
        //**************************************************************************
        // initialize return result;
        //**************************************************************************
        $MIN = 20.0;
        $MAX = 50.0;
        $RANGE = 3.0;
        $sensorType = 5;
        //**************************************************************************
        // sensor type :1   -   speed sensor
        // sensor data range:   20-50
        // sensor data random range: +-3
        //**************************************************************************
        $query = "SELECT sensorID FROM sensors WHERE sensorType = ".$sensorType.";";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row=$stmt->fetch();
        $currentID = $row[0];
        $return["id"]=$currentID;
        //get last row infomation
        $query = "SELECT MAX(ID) FROM SENSOR_DATA_".$currentID;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row=$stmt->fetch();
        $lastID = $row[0];
        //generate data
        //if its first data, totally random,
        //if not, generate data based on last generated data;
        if($lastID==null){
            //*******************************************************************************
            //generate new random value
            //*******************************************************************************
            $value = rand($MIN*10,$MAX*10)/10;
            // echo "first value: ".$value;
            $currentData = array(
                "value"=>$value
            );
            $jsonData = json_encode($currentData);
            $query = "INSERT INTO sensor_data_".$currentID." SET time=".$time.", data = '".$jsonData."'; ";
            $stmt = $this->conn->prepare($query);
            if($stmt->execute()){
                $return["data"]=json_decode($jsonData);
            }
        }else{
            // echo "\nLAST INSERTED ID: ".$lastID."\n";
            $query = "SELECT DATA FROM SENSOR_DATA_".$currentID." WHERE ID = ".$lastID.";";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row=$stmt->fetch();
            $lastData = $row[0];
            // echo $lastData;
            //get old data
            $oldJSONData = json_decode($lastData,true);
            $oldValue = $oldJSONData["value"];


            $newValue = rand(max($MIN*10,($oldValue-$RANGE)*10),min($MAX*10,($oldValue+$RANGE)*10))/10;

            $currentData = array(
                "value"=>$newValue
            );

            $jsonData = json_encode($currentData);
            $query = "INSERT INTO sensor_data_".$currentID." SET time=".$time.", data = '".$jsonData."'; ";
            $stmt = $this->conn->prepare($query);
            // echo "\n".$query;
            if($stmt->execute()){
                $return["data"]=json_decode($jsonData);
            }
               
        }
        return json_encode($return);
    }


    function curlcall($url, $data){
        //**************************************************************************
        // this function call and sending data to another DB
        //TODO: following codes are copy from web, needs modify.
        //**************************************************************************
        $data = array('stationID' => '1',
                    'stationType' => '2',
                    'orderID' => '23231231233',
                    'GPSID' => '2'
                );                                                                    
        $data_string = json_encode($data);                                                                                   
                                                                                                                             
        $ch = curl_init('http://localhost:8888/sensors/initial.php');                                                                      
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($data_string))                                                                       
        );                                                                                                                   
                                                                                                                             
        $result = curl_exec($ch);
        // echo $result;
    }

    function getDataByTime($id,$t1,$t2){
        //**************************************************************************
        // returns sensor data as json between two timestamp.
        //**************************************************************************

        $amount = 1;
        $result = array();
        $result['sensorID']=$this->sensorID;
        $result['datas'] = array();
        $currentSensorID=htmlspecialchars(strip_tags($this->sensorID));
        $query = "SELECT * FROM sensor_data_".$currentSensorID." WHERE TIME>".$t1." AND TIME<".$t2." ORDER BY time DESC ;";
        $stmt = $this->conn->prepare($query);
        
        if($stmt->execute()){
            $rows=$stmt->fetchAll();
            $arr_item=array();
            foreach ($rows as $row) {
                $arr_item["time"]=$row["time"];
                $arr_item["data"]=json_decode($row["data"]);

                array_push($result["datas"], $arr_item);
            }

            return $result;
        }
        return false;
    }
}
?>