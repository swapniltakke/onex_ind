<?php
include('shared/CommonManager.php');
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
$actual_output = $_POST['actual_output'];
if (isset($_POST['remarks'])) {
  $remarks = implode(",",$_POST['remarks']);
} else {
	$remarks ='';
}

if (isset($_POST['remarks_comp'])) {
  $remark_compcnt = implode(",",$_POST['remarks_comp']);
} else {
	$remark_compcnt = '';
}

$product_id = $_POST['product_id'];
$station_id = $_POST['station_id'];
$product_name = $_POST['product_name'];
$station_name = $_POST['station_name'];
$stage_id = $_POST['stage_id'];
$user_id = $_POST['user_id'];
$cust_name = $_POST['cust_name'];
//$remark_compcnt = implode(",",$_POST['remarks_comp']);
$scanqrcode = $_POST['scanqrcode'];

$sql = "insert into tbl_transactions(product_id,product_name,user_id,cust_name,station_id,station_name,stage_id,actual_output,remarks,remark_comp,barcode,status) 
        values (:product_id, :product_name, :user_id, :cust_name, :station_id, :station_name, :stage_id, :actual_output, :remarks, :remark_compcnt, :scanqrcode, :status)";
$queryParams = [
  ":product_id" => $product_id,
  ":product_name" => $product_name,
  ":user_id" => $user_id,
  ":cust_name" => $cust_name,
  ":station_id" => $station_id,
  ":station_name" => $station_name,
  ":stage_id" => $stage_id,
  ":actual_output" => $actual_output,
  ":remarks" => $remarks,
  ":remark_compcnt" => $remark_compcnt,
  ":scanqrcode" => $scanqrcode,
  ":status" => "1"
];
$query = DbManager::fetchPDOQuery('spectra_db', $sql, $queryParams);
if (!$query) {
  $res['error'] = "ERROR"; 
} else {
  $res['saved'] = "SAVED";
  $res['complete'] = "COMPLETE";
  
  $check_nextstage = "select count(*) as total from tbl_station where concat(',', product_id, ',') like :product_id and concat(',', stage_id, ',') like :stage_id";
  $result = DbManager::fetchPDOQuery('spectra_db', $check_nextstage, [":product_id" => "%,$product_id,%", ":stage_id" => "%,$stage_id,%"])["data"];
  $cnt = $result[0]['total'];

  $trans_check = "select count(*) as cnttotal from tbl_transactions where product_id =:product_id and stage_id=:stage_id";
  $rs = DbManager::fetchPDOQuery('spectra_db', $trans_check, [":product_id" => "$product_id", ":stage_id" => "$stage_id"])["data"];
  $cnt_total = $rs[0]['cnttotal'];
  if ($cnt == $cnt_total ) { 
    $res['saved'] = "SAVED";
    $res['complete'] = "COMPLETE";
  } else if ($cnt < $cnt_total) {
    $res['saved'] = "SAVED";
    $res['notcomplete'] = "NOTCOMPLETE";
  } else {
    $res['revertpage'] = "SCAN";
  }
}
$response['success'] = $res;
header("Content-type:application/json");
echo json_encode($response);
die();
?>