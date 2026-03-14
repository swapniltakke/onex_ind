<?php
include('shared/CommonManager.php');
$product_id = $_POST['product_id'];
$station_id = $_POST['station_id'];
$action = $_POST['action'];
$barcode = $_POST['barcode'];

$sql = "SELECT c2.checklist_item,c2.id,c1.check_req,c1.text_req,c1.text_lable_names
        FROM tbl_checklistdetails c1
        INNER JOIN tbl_checklist c2 ON c1.checklist_id=c2.id
        INNER JOIN tbl_stage c3 ON c1.stage_id=c3.stage_id
        INNER JOIN tbl_product p ON c1.product_id =p.product_id
        WHERE  c3.stage_id=:stage_id AND p.product_id=:product_id
        AND concat(',', c1.station_id, ',') LIKE :station_id ORDER BY c2.id ASC";	
$query = DbManager::fetchPDOQueryData('spectra_db', $sql, [":stage_id" => "8", ":product_id" => "$product_id", ":station_id" => "%,$station_id,%"])["data"];

if ($action == "edit") {
    $sql1 = "SELECT tr_id,actual_output,remark_comp FROM tbl_transactions WHERE barcode=:barcode AND station_id=:station_id ORDER BY tr_id DESC LIMIT 1";	
    $query1 = DbManager::fetchPDOQueryData('spectra_db', $sql1, [":barcode" => "$barcode", ":station_id" => "$station_id"])["data"][0];
    
    $actual_output_merge = explode("||",$query1['actual_output']);
    $actual_output = explode(",",$actual_output_merge[0]);
    $actual_output_manufacturing = explode(",",$actual_output_merge[1]);
    
    $remark_merge = explode("||",$query1['remark_comp']);
    $remark = trim($remark_merge[0],"'");
    $remark = explode("','",$remark);
    $remark_manufacturing = trim($remark_merge[1],"'");
    $remark_manufacturing = explode("','",$remark_manufacturing);
}

$sr = 1;
foreach ($query as $key => $data) {
    $stage_data['sr_no']= $sr;
    $sr++;
    $stage_data['check_item'] = $data['checklist_item'];
    $stage_data['actual_opt'] = $data['check_req'];
    $stage_data['actual_opt_manufacturing'] = $data['check_req'];
    $display_remark = "";
    $display_remark_manufacturing = "";
    if ($action == "edit") {
        $stage_data['actual_opt_checked'] = "2";
        if ($actual_output[$key] == "1") {
            $stage_data['actual_opt_checked'] = "1";
        } 
        if ($actual_output[$key] == "0") {
            $stage_data['actual_opt_checked'] = "0";
        }
        if ($remark[$key] != "") {
            $display_remark = ($remark[$key] == '""') ? '' : $remark[$key];
        }
        $stage_data['actual_opt_manufacturing_checked'] = "2";
        if ($actual_output_manufacturing[$key] == "1") {
            $stage_data['actual_opt_manufacturing_checked'] = "1";
        } 
        if ($actual_output_manufacturing[$key] == "0") {
            $stage_data['actual_opt_manufacturing_checked'] = "0";
        }
        if ($remark_manufacturing[$key] != "") {
            $display_remark_manufacturing = ($remark_manufacturing[$key] == '""') ? '' : $remark_manufacturing[$key];
        }
    }
    $stage_data['remark'] = $display_remark;
    $stage_data['remark_manufacturing'] = $display_remark_manufacturing;
    $stage_info[] = $stage_data;
}

$results = array(
"sEcho" => 1,
"iTotalRecords" => count($stage_info),
"iTotalDisplayRecords" => count($stage_info),
"aaData" => $stage_info);

echo json_encode($results);
?>