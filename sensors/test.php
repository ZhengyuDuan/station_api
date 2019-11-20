<?php
	echo "<table border = '1' class='user_table'><tr>
	<th>URL</th>
	<th>METHOD</th>
	<th>Body</th>
	<th>Description</th>
	</tr>";
	//1 readSensors
	echo "<tr>";
	echo "<td>readSensors</td>";
	echo "<td>GET</td>";
	echo "<td></td>";
	echo "<td>Get the list of the sensor information in edge station</td>";
	echo "</tr>";
	//2 register
	echo "<tr>";
	echo "<td>register</td>";
	echo "<td>PUT</td>";
	echo "<td>{“sensorType”:”1”}</td>";
	echo "<td>Add a sensor by sensor Type.</td>";
	echo "</tr>";
	//3 getStatus
	echo "<tr>";
	echo "<td>getStatus</td>";
	echo "<td>GET</td>";
	echo "<td></td>";
	echo "<td>Get status of the edge station</td>";
	echo "</tr>";
	//4 start
	echo "<tr>";
	echo "<td>start</td>";
	echo "<td>POST</td>";
	echo "<td></td>";
	echo "<td>Update a status of the edge station to running.</td>";
	echo "</tr>";
	//5 STOP
	echo "<tr>";
	echo "<td>stop</td>";
	echo "<td>POST</td>";
	echo "<td></td>";
	echo "<td>Update a status of the edge station to stop.</td>";
	echo "</tr>";
	//6 getSensorData
	echo "<tr>";
	echo "<td>getSensorData</td>";
	echo "<td>GET</td>";
	echo "<td>{“sensorID”:”1”}</td>";
	echo "<td>Get history data from specific sensor by sensor ID</td>";
	echo "</tr>";
	//7 sensorStart
	echo "<tr>";
	echo "<td>sensorStart</td>";
	echo "<td>POST</td>";
	echo "<td>{“sensorID”:”1”}</td>";
	echo "<td>Update specific sensor status to running</td>";
	echo "</tr>";
	//8 sensorStop
	echo "<tr>";
	echo "<td>sensorStop</td>";
	echo "<td>POST</td>";
	echo "<td>{“sensorID”:”1”}</td>";
	echo "<td>Update specific sensor status to stop</td>";
	echo "</tr>";
	//9 initialize
	echo "<tr>";
	echo "<td>initialize</td>";
	echo "<td>GET</td>";
	echo "<td></td>";
	echo "<td>Initialize the station to a new one that have no sensors</td>";
	echo "</tr>";
	// 10 generateData
	echo "<tr>";
	echo "<td>generateData</td>";
	echo "<td>GET</td>";
	echo "<td></td>";
	echo "<td>Get current data from all sensors and also save it to database</td>";
	echo "</tr>";
	// 11 setupStation
	echo "<tr>";
	echo "<td>setupStation</td>";
	echo "<td>POST</td>";
	echo "<td>{“stationID”:”1”,
“orderID”:”1”}
</td>";
	echo "<td>Setup station information</td>";
	echo "</tr>";
	// 12 changeOrderID
	echo "<tr>";
	echo "<td>changeOrderID</td>";
	echo "<td>POST</td>";
	echo "<td>{“orderID”:”1”}</td>";
	echo "<td>Change the order number of the station</td>";
	echo "</tr>";

?> 