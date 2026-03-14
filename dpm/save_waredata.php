<?php
include('shared/CommonManager.php');
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$product_id  = $_POST['product_id'];
$product_name = $_POST['product_name'];
$remark = $_POST['remark'];
$mis_assembly = $_POST['mis_assembly'];
$scanqrcode = $_POST['scanqrcode'];
$station_name = "Warehouse";

$sql = "insert into tbl_warehousebarcode(product_id,product_name,station_name,remark,barcode,mis_assembly,status) 
        values (:product_id, :product_name, :station_name, :remark, :scanqrcode, :mis_assembly, :status)";
$queryParams = [
  ":product_id" => $product_id,
  ":product_name" => $product_name,
  ":station_name" => $station_name,
  ":remark" => $remark,
  ":scanqrcode" => $scanqrcode,
  ":mis_assembly" => $mis_assembly,
  ":status" => "1"
];
$query = DbManager::fetchPDOQuery('spectra_db', $sql, $queryParams);
if (!$query) { 	 
  $res['error'] = "ERROR"; 
} else {
  $res['saved'] = "SAVED";
  $res['complete'] = "COMPLETE";
}
$response['success'] = $res;
header("Content-type:application/json");
echo json_encode($response);
die();
?>