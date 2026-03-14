<?php 
session_start();
include('shared/CommonManager.php');
// $station_id = $_POST['station_id'];
// $product_id = $_POST['product_id'];
// $work_id = $_POST['work_id'];
// $end_date = $_POST['end_date'];
$trdet_id = $_POST['trdet_id'];
$status = $_POST['status'];

// $sql = "update tbl_transactiondetails set end_time=:end_date where trdet_id=:trdet_id";
// $query = DbManager::fetchPDOQueryData('spectra_db', $sql, [":end_date" => "$end_date", ":trdet_id" => "$trdet_id"]);

$sql = "update tbl_transactiondetails set status=:status where trdet_id=:trdet_id";
$query = DbManager::fetchPDOQueryData('spectra_db', $sql, [":status" => "$status", ":trdet_id" => "$trdet_id"]);

if (!$query) {
  $res['error'] = "ERROR"; 
} else {
  $res['update'] = "UPDATE";
}
$response['success'] = $res;
header("Content-type:application/json");
echo json_encode($response);
die();
?>