<?php
include('shared/CommonManager.php');
ini_set('display_errors', 0);

error_reporting(E_ERROR | E_WARNING | E_PARSE);

$actual_output =$_POST['actual_output'];
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
//print_r($_POST['remarks_comp']);
$product_id  = $_POST['product_id'];
$station_id = $_POST['station_id'];
$product_name = $_POST['product_name'];
$station_name = $_POST['station_name'];
$stage_id = $_POST['stage_id'];
$user_id = $_POST['user_id'];
$cust_name = $_POST['cust_name'];
$scanqrcode = $_POST['scanqrcode'];
//echo $remark_compcnt; exit;


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

//echo $sql; exit;
$query = DbManager::fetchPDOQuery('spectra_db', $sql, $queryParams);
if (!$query) 
{ 	

//$_SESSION['success'] = "<strong>Record</strong> is not added";
  
  $res['error'] = "ERROR"; 
}
else
{
  
  

    /* if (preg_match("/shaft/i", $station_name)) {
			$res['revertpage']="SCAN";
		} else { */
      $res['saved']="SAVED";
      $res['complete'] = "COMPLETE";  
		/* } */




  /*$check_nextstage="select count(*) as total from tbl_station where concat(',', product_id, ',') like '%,$product_id,%' and concat(',', stage_id, ',') like '%,1,%'";
  $result = odbc_exec($conn, $check_nextstage);
  $cnt = odbc_result($result,'total');

  $trans_check ="select count(*) as cnttotal from tbl_transactions where product_id ='$product_id' and stage_id='1'";
  $rs = odbc_exec($conn, $trans_check);
  $cnt_total = odbc_result($rs,'cnttotal');
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

    $res['revertpage']="SCAN";
  }*/
 
    
}
$response['success']=$res;
header("Content-type:application/json");
echo json_encode($response);
die();






?>