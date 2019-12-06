<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
 
include_once '../config/database.php';
include_once '../objects/sensors.php';
 
$database = new Database();
$db = $database->getConnection();
 
$sensor = new sensors($db);

$data = json_decode(file_get_contents("php://input"));

if(
    ($data->status=="0" || !empty($data->status)) 
){
    if($data->status <= -1 || $data->status>3){
        http_response_code(503);
        echo json_encode(array("message" => "Unable to change Station Status, input wrong."));
    }else if($sensor->changeStationStatus($data->status)){
 
        http_response_code(200);
        echo json_encode(array("message" => "Station status changed."));
    }

    else{
 
        http_response_code(503);
        echo json_encode(array("message" => "Unable to change status."));
    }
}
 
// tell the user data is incomplete
else{
    http_response_code(400);
 
    // tell the user
    echo json_encode(array("message" => "Unable to process request. Data is incomplete."));
}
?>