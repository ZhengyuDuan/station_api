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
$data = json_decode(file_get_contents("php://input"));

// echo json_encode($data);
if(
    ($data->stationID=="0" || !empty($data->stationID)) &&
    ($data->stationType=="0" || !empty($data->stationType)) &&
    ($data->orderID=="0" || !empty($data->orderID)) &&
    ($data->GPSID=="0" || !empty($data->GPSID))
){
    // echo "get";

    if($sensors->initial($data->stationID,$data->stationType,$data->orderID,$data->GPSID)){
    // echo "f";
        // set response code - 201 created
        http_response_code(200);
 
        // tell the user
        echo json_encode(array("message" => "Station initialized."));
    }
 
    // if unable to create the product, tell the user
    else{
 
        // set response code - 503 service unavailable
        http_response_code(503);
 
        // tell the user
        echo json_encode(array("message" => "Unable to initial."));
    }
}
 
// tell the user data is incomplete
else{
    // set response code - 400 bad request
    http_response_code(400);
 
    // tell the user
    echo json_encode(array("message" => "Data is incomplete."));
}
?>

