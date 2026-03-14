<?php
include('shared/CommonManager.php');
$station_id = $_POST['station_id'];
$product_id = $_POST['product_id'];
$work_id = $_POST['work_id'];
$end_time = "1900-01-01 00:00:00.000";

if ($station_id != '' && $product_id == '' && $work_id == '') {
    $sql = "SELECT st.stage_name,st.stage_type, t.trdet_id,t.workbench_id,t.start_time,t.end_time,t.tr_id,t.product_id,t.station_id,t.w_type,t.remark,p.product_name,s.station_name,t.Barcode,t.status 
    FROM tbl_transactiondetails t
    inner join tbl_product p on p.product_id=t.product_id
    inner join tbl_station s on s.station_id=t.station_id
    inner join tbl_stage st on st.stage_id = t.tr_id
    where t.station_id=:station_id";
    $res = DbManager::fetchPDOQueryData('spectra_db', $sql, [":station_id" => "$station_id"])["data"];
} else if ($station_id != '' && $product_id != '' && $work_id == '') {
    $sql = "SELECT st.stage_name,st.stage_type, t.trdet_id,t.workbench_id,t.start_time,t.end_time,t.tr_id,t.product_id,t.station_id,t.w_type,t.remark,p.product_name,s.station_name,t.Barcode,t.status 
    FROM tbl_transactiondetails t
    inner join tbl_product p on p.product_id=t.product_id
    inner join tbl_station s on s.station_id=t.station_id
    inner join tbl_stage st on st.stage_id = t.tr_id
    where t.product_id=:product_id and t.station_id=:station_id";
    $res = DbManager::fetchPDOQueryData('spectra_db', $sql, [":product_id" => "$product_id", ":station_id" => "$station_id"])["data"];
} else if ($station_id != '' && $product_id != '' && $work_id != '') {
    $sql = "SELECT st.stage_name,st.stage_type, t.trdet_id,t.workbench_id,t.start_time,t.end_time,t.tr_id,t.product_id,t.station_id,t.w_type,t.remark,p.product_name,s.station_name,t.Barcode,t.status
    FROM tbl_transactiondetails t
    inner join tbl_product p on p.product_id=t.product_id
    inner join tbl_station s on s.station_id=t.station_id
    inner join tbl_stage st on st.stage_id = t.tr_id
    where t.product_id=:product_id and t.station_id=:station_id and t.workbench_id=:work_id";
    $res = DbManager::fetchPDOQueryData('spectra_db', $sql, [":product_id" => "$product_id", ":station_id" => "$station_id", ":work_id" => "$work_id"])["data"];
} else { 
    $sql = "SELECT st.stage_name,st.stage_type, t.trdet_id,t.workbench_id,t.start_time,t.end_time,t.tr_id,t.product_id,t.station_id,t.w_type,t.remark,p.product_name,s.station_name,t.Barcode,t.status 
    FROM tbl_transactiondetails t
    inner join tbl_product p on p.product_id=t.product_id
    inner join tbl_station s on s.station_id=t.station_id
    inner join tbl_stage st on st.stage_id = t.tr_id";
    $res = DbManager::fetchPDOQueryData('spectra_db', $sql)["data"];
}	
$cnt = count($res);
$product_info = array();
if ($cnt > 0) {
    $sr = 1;
    foreach ($res as $data) {
        $product_data['sr_no'] = $sr;
        $sr++;
        $product_data['trdet_id'] = $data['trdet_id'];
        $product_data['station_name'] = trim($data['station_name']);
        
        $product_data['product_name'] = trim($data['product_name']);
        $product_data['start_time'] = trim($data['start_time']);
        $end_time = trim($data['end_time']);
        if ($end_time == '1900-01-01 00:00:00.000') {
            $product_data['end_time'] = '';
        } else {
            $product_data['end_time'] = $end_time;
        }
        $product_data['stage_name'] = trim($data['stage_name'])." ( ".trim($data['stage_type'])." )";
        $product_data['remark'] = trim($data['remark']);
        
        $product_data['action'] = '';
        $Barcode = trim($data['Barcode']);
        if ($Barcode == '') {
            $product_data['Barcode'] = '';
        } else {
            $product_data['Barcode'] = $Barcode;
        }
        $w_type = $data['w_type'];
        if($w_type != 0){
            $w_query = "select w_type from tbl_manageworkbench where workbench_id=:work_id and w_id=:w_type";
            $result = DbManager::fetchPDOQueryData('spectra_db', $w_query, [":work_id" => "$work_id", ":w_type" => "$w_type"])["data"];
            $product_data['w_type'] = $result[0]['w_type']; 
        } else {
            $product_data['w_type'] = '';  
        }
        $product_data['status'] = $data['status']; 
        $product_info[] = $product_data;
    }
}
$results = array(
    "sEcho" => 1,
    "iTotalRecords" => count($product_info),
    "iTotalDisplayRecords" => count($product_info),
    "aaData" => $product_info);

echo json_encode($results);
?>