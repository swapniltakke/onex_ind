<?php

include 'DatabaseConfig.php';
$conn = new mysqli($HostName, $HostUser, $HostPass, $DatabaseName);
if (!$conn) {
	die ('Failed to connect to MySQL: ' . mysqli_connect_error());	
}
//include_once("db_connect.php");
if($_POST['barcode']) {
$sql = "select * from tbl_transactions where  barcode= '".$_POST['barcode']."'";
$resultset = mysqli_query($conn, $sql) or die("database error:". mysqli_error($conn));
//$data = array();
while( $rows = mysqli_fetch_assoc($resultset) ) {
$res = $rows;
}
$response['success']=$res;
 header("Content-type:application/json");
echo json_encode($response);
die();

} 
else {
echo 0;
}

?>