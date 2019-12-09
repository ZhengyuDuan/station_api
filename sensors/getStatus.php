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

$sensors = new sensors($db);
$result = $sensors->getStationStatus();
if($result == -1){
    http_response_code(404);
 
    echo json_encode(
        array("message" => "Unable to get infomation.")
    );
}
 
else{
    http_response_code(200);
    echo json_encode($result);
 
}
?>