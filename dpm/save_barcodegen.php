<?php
header('Content-type: application/json');

session_start();
$user=$_SESSION['username'];

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];
$cust_name = $_POST['cust_name'];
$serial_no= $_POST['serial_no'];
$panel_no = $_POST['panel_no'];
$station_id = $_POST['station_id'];
include 'DatabaseConfig.php';
$conn = new mysqli($HostName, $HostUser, $HostPass, $DatabaseName);
if (!$conn) {
	die ('Failed to connect to MySQL: ' . mysqli_connect_error());	
}
$sql="select product_name from tbl_product where product_id=".$product_id;
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$product_name= $row['product_name'];
$custname = explode(" ", $cust_name);
$barcode = strtoupper($custname[0])."".$product_name."".$serial_no."".$panel_no."".$station_id;
//echo $barcode; exit; die();
$sqlstation="select station_name from tbl_station where station_id=".$station_id;
$resultstation = mysqli_query($conn, $sqlstation);
$rowstation = mysqli_fetch_assoc($resultstation);
$station_name = $rowstation['station_name'];
$sql_bar="select barcode from tbl_transactions where user_id=".$user_id;
$result1 = mysqli_query($conn, $sql_bar);
$row1 = mysqli_fetch_assoc($result1);
if($row1['barcode'] == $barcode){
    $response_array['status'] = 'error'; 
}
else 
{
    $sql="insert into tbl_transactions (product_id,product_name,user_id,cust_name,serial_no,panel_no,station_id,station_name,barcode) values ('".trim($product_id)."','".trim($product_name)."','".trim($user_id)."','".trim($cust_name)."','".trim($serial_no)."','".trim($panel_no)."','".trim($station_id)."','".trim($station_name)."','".trim($barcode)."')" ;
    $query = mysqli_query($conn, $sql);
    $response_array['status'] = 'success'; 
 
 

}
echo json_encode($response_array);
?>