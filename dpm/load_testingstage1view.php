<?php
include('shared/CommonManager.php');
$product_id = $_POST['product_id'];
$station_id = $_POST['station_id'];

$sql = "SELECT c2.checklist_item,c2.id,c1.check_req,c1.text_req 
		FROM tbl_checklistdetails c1
		inner join tbl_checklist c2 on c1.checklist_id=c2.id
		inner join tbl_stage  c3 on c1.stage_id=c3.stage_id
		inner join tbl_product p on c1.product_id =p.product_id
		where  c3.stage_id=:stage_id and p.product_id=:product_id
		and concat(',', c1.station_id, ',') like :station_id";
$query = DbManager::fetchPDOQueryData('spectra_db', $sql, [":stage_id" => "6", ":product_id" => "$product_id", ":station_id" => "%,$station_id,%"])["data"];
$sr = 1; $j = 0; $k = 0;
foreach ($query as $data) {
	$stage_data['sr_no'] = $sr;
	$stage_data['check_item'] = $data['checklist_item'];
	$query_remark1 = "select remarks,actual_output from tbl_transactions where stage_id=:stage_id and station_id=:station_id and product_id=:product_id";
	$query_remark = DbManager::fetchPDOQueryData('spectra_db', $query_remark1, [":stage_id" => "6", ":product_id" => "$product_id", ":station_id" => "$station_id"])["data"];
	$remarks = $query_remark[0]['remarks'];
	$actual_output = $query_remark[0]['actual_output'];
	$actual_output1 = explode(",", $actual_output);
	if ($actual_output1[$k] == 0) {
		$check_z = "checked";
	} else {
		$check_z = "";
	}
	if ($actual_output1[$k] == 1) {
		$check_o = "checked";
	} else {
		$check_o = "";
	}
	$actual_otp ='<input type="radio" class="check_req" value="1" name="actual_opt_up'.$sr.'" id="check_req'.$sr.'" '.$check_o.' > Ok
	<input type="radio" class="check_req" value="0" name="actual_opt_up'.$sr.'" id="check_req'.$sr.'" '.$check_z.'> Not Ok';
	$stage_data['actual_opt'] = $actual_otp;
	$text_req = $data['text_req'];
	$response = "";
	$remarks1 = explode(",", $remarks);
	for ($i=0;$i<$text_req;$i++) {
		$response .='<input type="text" id="'.$j.'" value="'.$remarks1[$j].'"  class="form-control" name="remark[]">&nbsp;';
		$j++;
	}
	$response1 = $response;
	$stage_data['remark'] = $response1;
	$stage_info[] = $stage_data;
	$sr++; $k++;
}

$results = array(
    "sEcho" => 1,
    "iTotalRecords" => count($stage_info),
    "iTotalDisplayRecords" => count($stage_info),
    "aaData" => $stage_info);

echo json_encode($results);
?>