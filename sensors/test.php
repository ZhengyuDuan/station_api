test
<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
 
include_once '../config/database.php';
include_once '../objects/sensors.php';
 
echo "include";
$database = new Database();
$db = $database->getConnection();

$sensors = new sensors($db);
echo phpinfo();

if($result = $sensors->getStatus()){
    http_response_code(200);
    echo json_encode($result);
}
else{
 
    http_response_code(404);
 
    echo json_encode(array("message" => "Unable to get infomation."));
}
if ( extension_loaded('pdo') ) {
    echo "pdo support";
}

?>