<?php
include('shared/CommonManager.php');
if ($_POST['update_type'] == "add") {
	$station_id = implode(',',$_POST['station_id']);
	$sql = "insert into tbl_checklistdetails(stage_id,checklist_id,text_req,check_req,product_id,station_id,text_lable_names) values (:stage_id,:checklist_id,:text_req,:check_req,:product_id,:station_id,:text_lable_name)" ;
	$query = DbManager::fetchPDOQueryData('spectra_db', $sql, [":stage_id" => trim($_POST['stage_id']),":checklist_id" => trim($_POST['checklist_id']),":text_req" => trim($_POST['text_req']),":check_req" => trim($_POST['check_req']),":product_id" => trim($_POST['product_id'])
			,":station_id" => $station_id,":text_lable_name" => trim($_POST['text_lable_name'])]);
	if (!$query) {
		$_SESSION['success'] = "<strong>Record</strong> is not added";
	} else {	
		$_SESSION['success'] = "<strong>Congratulations</strong> Stagewise Checklist Item information has been inserted successfully.";	 
	}
} else {
	$station_id = implode(',',$_POST['station_id']);
	$sql = "update tbl_checklistdetails set stage_id =:stage_id, checklist_id =:checklist_id, text_req=:text_req, check_req=:check_req, product_id=:product_id, station_id=:station_id, text_lable_names=:text_lable_name where check_id=:check_id";
	$query = DbManager::fetchPDOQuery('spectra_db', $sql, [":stage_id" => trim($_POST['stage_id']), ":checklist_id" => trim($_POST['checklist_id']), ":text_req" => trim($_POST['text_req']), ":check_req" => trim($_POST['check_req']), ":product_id" => trim($_POST['product_id']), ":station_id" => $station_id, ":text_lable_name" => trim($_POST['text_lable_name']), ":check_id" => trim($_POST['check_id'])]);
	if (!$query) { 
		$_SESSION['success'] = "<strong>Record</strong> is not updated";
	} else {
		$_SESSION['success'] = "<strong>Congratulations</strong> Stagewise Checklist Item information has been updated successfully.";
	}
}
header("Location:stagewisechecklistdet.php");
exit(0);
?>