<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
// get database connection
include_once '../config/database.php';
 
// instantiate product object
include_once '../objects/sensors.php';
 
$database = new Database();
$db = $database->getConnection();
 
$sensor = new sensors($db);

// get posted data
$data = json_decode(file_get_contents("php://input"));
 
// make sure data is not empty
if(
    // !empty($data->sensorID) &&
    !empty($data->sensorID)
    // !empty($data->sensorStatus)
){
 
    // set product property values
    // $sensor->sensorID = $data->sensorID;
    $sensor->sensorID = $data->sensorID;
    // $sensor->sensorStatus = 1;
    // echo $sensor->sensorStatus;
    // create the product
    if($result = $sensor->getSensorData()){
 
        // set response code - 201 created
        http_response_code(201);
 
        // tell the user
        echo json_encode($result);
    }
 
    // if unable to create the product, tell the user
    else{
 
        // set response code - 503 service unavailable
        http_response_code(503);
 
        // tell the user
        echo json_encode(array("message" => "Unable to register sensor."));
    }
}
 
// tell the user data is incomplete
else{
    echo $data->sensorType;
    // set response code - 400 bad request
    http_response_code(400);
 
    // tell the user
    echo json_encode(array("message" => "Data is incomplete."));
}
?>