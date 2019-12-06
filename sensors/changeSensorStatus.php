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
    ($data->sensorID=="0" || !empty($data->sensorID)) &&
    ($data->sensorStatus=="0" || !empty($data->sensorStatus)) 
){
    if($data->sensorStatus<0 || $data->sensorStatus >5){
 
        http_response_code(503);
     
        echo json_encode(
            array("message" => "Unable to change status, status input invalid.")
        );
    }else if($result = $sensor->changeSensorStatus($data->sensorID,$data->sensorStatus)){

        http_response_code(200);
        echo json_encode(
            array("message" => "Sensor status changed")
        );
   
    }else{
 
        http_response_code(404);
     
        echo json_encode(
            array("message" => "Unable to change status.")
        );
    }
}
 
// tell the user data is incomplete
else{
    http_response_code(400);
 
    // tell the user
    echo json_encode(array("message" => "Unable to process request. Data is incomplete."));
}
?>

