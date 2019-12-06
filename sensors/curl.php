<?php
        //**************************************************************************
        // this function call and sending data to another DB
        //TODO: following codes are copy from web, needs modify.
        //**************************************************************************
        
$data = array('stationID' => '1',
            'stationType' => '2',
            'orderID' => '23231231233',
            'GPSID' => '2'
        );                                                                    
$data_string = json_encode($data);                                                                                   
                                                                                                                     
$ch = curl_init('http://localhost:8888/sensors/initial.php');                                                                      
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
