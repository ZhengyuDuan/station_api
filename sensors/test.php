
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
 

// if (extension_loaded('pdo')) 
// {
// 	echo "success loaded pdo\n";
// }else{
//     dl('pdo.so');
// }


if($sensors->start()){
    // set response code - 200 OK
  //   http_response_code(200);
 	echo "1";
  //   echo json_encode(array("message" => "Station now Running."));
}else{
 
    // set response code - 404 Not found
  //   http_response_code(404);
 	echo "2";
 
  //   // tell the user no products found
  //   echo json_encode(
  //       array("message" => "Failed.")
  //   );
}
echo "3";
?>
