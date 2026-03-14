<?php
include('shared/CommonManager.php');
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$actual_output =$_POST['actual_output'];

//$actual_output = implode(",",$_POST['actual_output']);
//$remarks = implode(",",$_POST['remarks']);
if(isset($_POST['remarks'])){
$remarks = implode(",",$_POST['remarks']);
}
else{
	$remarks ='';
	
}

if(isset($_POST['remarks_comp'])){
$remark_compcnt =implode(",",$_POST['remarks_comp']);
}
else{
	$remark_compcnt = '';
	
}
$product_id  = $_POST['product_id'];
$station_id = $_POST['station_id'];
$product_name = $_POST['product_name'];
$station_name = $_POST['station_name'];
$stage_id = $_POST['stage_id'];
$user_id = $_POST['user_id'];
$cust_name = $_POST['cust_name'];
$station_name = $_POST['station_name'];
$machine_name = $_POST['machine_name'];
$scanqrcode = $_POST['scanqrcode'];

//$remark_compcnt =implode(",",$_POST['remarks_comp']);



 $sql="insert into tbl_transactions(product_id,product_name,user_id,cust_name,station_id,station_name,stage_id,actual_output,remarks,remark_comp,barcode,status) 
      values (:product_id,:product_name,:user_id,:cust_name,:station_id,:station_name,:stage_id,:actual_output,:remarks,:remark_compcnt,:scanqrcode,:status)" ;

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
        ":status" => '1'
     ];
     $query = DbManager::fetchPDOQuery('spectra_db', $sql, $queryParams);
if (!$query) 
{ 	

//$_SESSION['success'] = "<strong>Record</strong> is not added";
$res['error'] = "ERROR"; 
}
else{

  $check_nextstage = "select count(*) as total from tbl_station WHERE concat(',', product_id, ',') like :product_id and concat(',', stage_id, ',') like :stage_id and Machine_name =:machine_name";
  $response = DbManager::fetchPDOQuery('spectra_db', $check_nextstage, [":product_id" => "%,$product_id,%",":stage_id" => "%,$stage_id,%",":machine_name" => $machine_name])["data"];
  $cnt = $response[0]['total'];

  //echo $check_nextstage;//exit;

  $trans_check ="select count(*) as cnttotal from tbl_transactions where product_id =:product_id and stage_id=:stage_id and station_id =:station_id";
  $rs = DbManager::fetchPDOQuery('spectra_db', $trans_check, [":product_id" => $product_id,":stage_id" => $stage_id,":station_id" => $station_id])["data"];
  $cnt_total = $rs[0]['cnttotal'];
  //echo $check_nextstage. "  " .$trans_check; exit;
  if($cnt == $cnt_total ){ 

    $res['saved']="SAVED";
    $res['complete'] = "COMPLETE";


  }
  else if($cnt_total < $cnt){
    $res['saved']="SAVED";
         $res['notcomplete']="NOTCOMPLETE";


  }
  else
  {

    //$res['revertpage']="SCAN";
	$res['saved']="SAVED";
    $res['complete'] = "COMPLETE";

  }
}

$response['success']=$res;
header("Content-type:application/json");
echo json_encode($response);
die();






?>