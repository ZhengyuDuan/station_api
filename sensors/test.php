<!DOCTYPE html>
<html>
<head>
	<title>test?</title>
</head>
<body>

<?php
if (extension_loaded('pdo')) 
{
	echo "success";
}else{
    dl('pdo.so');
}
?>
</body>
</html>
