<!DOCTYPE html>
<html>
<head>
	<title>test?</title>
</head>
<body>

<?php
if (extension_loaded('pdo')) 
{
	echo "success loaded pdo\n";
}else{
    dl('pdo.so');
}

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/database.php';
include_once '../objects/sensors.php';
 
$database = new Database();
$db = $database->getConnection();
 
$sensors = new sensors($db);
 

?>
</body>
</html>
