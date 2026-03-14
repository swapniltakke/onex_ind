<?php
include('shared/CommonManager.php');
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$actual_output = $_POST['actual_output'];
if (isset($_POST['remarks'])) {
	$remarks = implode(",",$_POST['remarks']);
} else {
	$remarks = '';	
}
if (isset($_POST['remarks_comp'])) {
	$remark_compcnt = implode(",",$_POST['remarks_comp']);
} else{
	$remark_compcnt = '';	
}
$product_id  = $_POST['product_id'];
$station_id = $_POST['station_id'];
$product_name = $_POST['product_name'];
$station_name = $_POST['station_name'];
$stage_id = $_POST['stage_id'];
$user_id = $_POST['user_id'];
$cust_name = $_POST['cust_name'];
$scanqrcode = $_POST['scanqrcode'];
$mlfb_num = substr($scanqrcode,8,8);

$sql = "insert into tbl_transactions(product_id,product_name,user_id,cust_name,station_id,station_name,stage_id,actual_output,remarks,remark_comp,barcode,status) 
		values (:product_id, :product_name, :user_id, :cust_name, :station_id, :station_name, :stage_id, :actual_output, :remarks, :remark_compcnt, :scanqrcode, :status)";
$query = DbManager::fetchPDOQuery('spectra_db', $sql, [
  ":product_id" => "$product_id",
  ":product_name" => "$product_name",
  ":user_id" => "$user_id",
  ":cust_name" => "$cust_name",
  ":station_id" => "$station_id",
  ":station_name" => "$station_name",
  ":stage_id" => "$stage_id",
  ":actual_output" => "$actual_output",
  ":remarks" => "$remarks",
  ":remark_compcnt" => "$remark_compcnt",
  ":scanqrcode" => "$scanqrcode",
  ":status" => "1"
]);

if (!$query) {
	$res['error'] = "ERROR";
} else{
	$sql2 = "update tbl_DailyUpload set Progress_Status =:ProgressStatus, CompDate =:CompDate where barcode=:scanqrcode and progress_status !=:progress_status";	
	$resuploadid = DbManager::fetchPDOQuery('spectra_db', $sql2, [":ProgressStatus" => "4", ":CompDate" => date('Y-m-d h:i:s'), ":scanqrcode" => "$scanqrcode", ":progress_status" => "4"]);
  	$res['saved'] = "SAVED";
}

$response['success'] = $res;
header("Content-type:application/json");
echo json_encode($response);
die();
?>