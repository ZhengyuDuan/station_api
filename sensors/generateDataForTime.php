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
// echo json_encode($data);
if(
    ($data->startTime=="0" || !empty($data->startTime)) &&
    ($data->endTime=="0" || !empty($data->endTime)) &&
    ( !empty($data->interval)) 
){
    if($data->endTime < $data->startTime){
 
        http_response_code(503);
     
        echo json_encode(
            array("message" => "Unable to process request, input invalid.")
        );
    }else if($result = $sensor->generateDataForTime($data->startTime,$data->endTime, $data->interval)){

        http_response_code(200);
        echo json_encode(
            array("message" => "Data generated.")
        );
   
    }else{
 
        http_response_code(404);
     
        echo json_encode(
            array("message" => "Unable to generate data.")
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

