<?php
include('shared/CommonManager.php');
$scan_barcode1 = $_POST['scan_barcode1'];
$scan_barcode2 = $_POST['scan_barcode2'];
$scan_barcode3 = $_POST['scan_barcode3'];
$product_id = $_POST['product_id'];
$station_id = $_POST['station_id'];
$stage_id = $_POST['stage_id'];
$user_id = $_POST['user_id'];
$cust_name = $_POST['cust_name'];
$product_name = $_POST['product_name'];
$station_name = $_POST['station_name'];
$scanqrcode = $_POST['scanqrcode'];
$actual_output = "";
$remarks = "";
//print_r($_POST); //exit;
$sql = "insert into tbl_transactions(product_id,product_name,user_id,cust_name,station_id,station_name,stage_id,actual_output,remarks,barcode,status) 
        values (:product_id, :product_name, :user_id, :cust_name, :station_id, :station_name, :stage_id, :actual_output, :remarks, :scanqrcode, :status)";
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
  ":scanqrcode" => $scanqrcode,
  ":status" => "1"
];
$query = DbManager::fetchPDOQuery('spectra_db', $sql, $queryParams);
if (!$query) {
  $res['error'] = "ERROR";
} else {
  $res['trans'] = "SAVED";
}
if (!empty($scan_barcode1)) {
  $insert_barcode1 = "insert into tbl_location_save(stage_id,product_id,station_id,loc_barcode,user_id,cust_name)
                      values (:stage_id, :product_id, :station_id, :scan_barcode1, :user_id, :cust_name)";
  $queryParams1 = [
    ":stage_id" => $stage_id,
    ":product_id" => $product_id,
    ":station_id" => $station_id,
    ":scan_barcode1" => $scan_barcode1,
    ":user_id" => $user_id,
    ":cust_name" => $cust_name
  ];
  $query1 = DbManager::fetchPDOQuery('spectra_db', $insert_barcode1, $queryParams1);
  if (!$query1) { 	
    $res['error1'] = "ERROR1";
  } else {
    $res['barcode1'] = "SAVED";
  }	
}
if (!empty($scan_barcode2)) {
  $insert_barcode2 = "insert into tbl_location_save(stage_id,product_id,station_id,loc_barcode,user_id,cust_name) 
                      values (:stage_id, :product_id, :station_id, :scan_barcode2, :user_id, :cust_name)";
  $queryParams2 = [
    ":stage_id" => $stage_id,
    ":product_id" => $product_id,
    ":station_id" => $station_id,
    ":scan_barcode2" => $scan_barcode2,
    ":user_id" => $user_id,
    ":cust_name" => $cust_name
  ];
  $query2 = DbManager::fetchPDOQuery('spectra_db', $insert_barcode2, $queryParams2);
  if (!$query2) { 	
    $res['error2'] = "ERROR2";
  } else {
    $res['barcode2'] = "SAVED";
  }	
}
if (!empty($scan_barcode3)) {
	$insert_barcode3 = "insert into tbl_location_save(stage_id,product_id,station_id,loc_barcode,user_id,cust_name) 
                      values (:stage_id, :product_id, :station_id, :scan_barcode3, :user_id, :cust_name)";
  $queryParams3 = [
    ":stage_id" => $stage_id,
    ":product_id" => $product_id,
    ":station_id" => $station_id,
    ":scan_barcode3" => $scan_barcode3,
    ":user_id" => $user_id,
    ":cust_name" => $cust_name
  ];
  $query3 = DbManager::fetchPDOQuery('spectra_db', $insert_barcode3, $queryParams3);
  if (!$query3) { 	
    $res['error3']="ERROR3";
  } else {
    $res['barcode3']="SAVED";
  }
}

$check_nextstage = "select count(*) as total from tbl_station where concat(',', product_id, ',') like :product_id and concat(',', stage_id, ',') like :stage_id";
$result = DbManager::fetchPDOQueryData('spectra_db', $check_nextstage, [":product_id" => "%,$product_id,%", ":stage_id" => "%,3,%"])["data"];
$cnt = $result[0]['total'];

$trans_check ="select count(*) as cnttotal from tbl_transactions where product_id =:product_id and stage_id =:stage_id and barcode =:barcode";
$rs = DbManager::fetchPDOQueryData('spectra_db', $trans_check, [":product_id" => "$product_id", ":stage_id" => "3", ":barcode" => "$scanqrcode"])["data"];
$cnt_total = $rs[0]['cnttotal'];
if ($cnt == $cnt_total ) {
  $res['saved'] = "SAVED";
  $res['complete'] = "COMPLETE";
} else if($cnt_total < $cnt) {
  $res['saved'] = "SAVED";
  $res['notcomplete'] = "NOTCOMPLETE";
} else {
  $res['revertpage'] = "SCAN";
}
$response['success'] = $res;
header("Content-type:application/json");
echo json_encode($response);
die();
?>