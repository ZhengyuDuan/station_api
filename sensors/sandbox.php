<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
 
// database connection will be here
// include database and object files
include_once '../config/database.php';
include_once '../objects/sensors.php';
 
// instantiate database and sensors object
$database = new Database();
$db = $database->getConnection();
 
// initialize object
$sensors = new sensors($db);
 
$sensors->generateDataForTime(100,200,2);
// echo "..";
// $sensors->generateSpeedData(12323);
// echo $sensors->changeSensorStatus(1,4);
?>
