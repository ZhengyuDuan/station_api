<?php
        //**************************************************************************
        // this function call and sending data to another DB
        //TODO: following codes are copy from web, needs modify.
        //**************************************************************************
/*
$data = array(
    "status"=>1);
$data_string = json_encode($data);

$ch = curl_init('http://xckang.com/api/public/sensor/25');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data_string))
);

$result = curl_exec($ch);
echo $result;
?>


        $query = "SELECT STATIONID FROM INFO";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $id = $stmt->fetchColumn();
        echo $id;
        $url = "machine/".$id;
        $data = array("status"=>$newStatus);
        $this->postcurl($url,json_encode($data));
*/
echo "This api currently closed for public."
