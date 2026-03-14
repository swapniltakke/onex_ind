<?php
include('shared/CommonManager.php');
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
// SharedManager::print($_POST);

$product_id  = $_POST['product_id'];
$station_id = $_POST['station_id'];
$scanqrcode = $_POST['scanqrcode'];
$stage_id = $_POST['stage_id'];
$work_id = $_POST['work_id'];
if($work_id == 1 || $work_id == 3 || $work_id == 4 || $work_id == 1002 || $work_id == 2)
   {
    $work_type = $_POST['work_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $rework_remark = $_POST['rework_remark'];

}
else if($work_id == 5 || $work_id == 7 || $work_id == 8 || $work_id == 9 || $work_id == 11 || $work_id == 12){
    $work_type = '';
    $start_date = NULL;
    $end_date = NULL;
    $rework_remark = $_POST['rework_remark'];

}



    $sql="insert into tbl_transactiondetails(workbench_id,start_time,end_time,tr_id,product_id,station_id,w_type,Remark,Barcode,status) values(:work_id,:start_date,:end_date,:stage_id,:product_id,:station_id,:work_type,:rework_remark,:scanqrcode,:status)" ;
    $queryParams = [
        ":work_id" => $work_id,
        ":start_date" => $start_date,
        ":end_date" => $end_date,
        ":stage_id" => $stage_id,
        ":product_id" => $product_id,
        ":station_id" => $station_id,
        ":work_type" => $work_type,
        ":rework_remark" => $rework_remark,
        ":scanqrcode" => $scanqrcode,
        ":status" => '1'
    ];
    //SharedManager::print($queryParams);
    //echo $sql;exit;
    $query = DbManager::fetchPDOQuery('spectra_db', $sql, $queryParams);
    if(!$query) 
    { 
        echo "ERROR";
    }
    else{
        echo "SAVED";
    }

	



?>