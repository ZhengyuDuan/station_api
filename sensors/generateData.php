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
 
// read products will be here
// query products

    http_response_code(200);
 
    // tell the user no products found
    echo json_encode(
    array("message" => "This API currently not open for public.")
/*
if($result = $sensors->generateData()){
    // set response code - 200 OK
    http_response_code(200);
 
    // show sensors data in json format
    // echo json_encode(array("message" => "Data Generated."));
    echo json_encode($result);
}
 
// no products found will be here
else{
 
    // set response code - 404 Not found
    http_response_code(404);
 
    // tell the user no products found
    echo json_encode(
        array("message" => "Gathering Data Failed.")
    );
}
*/
