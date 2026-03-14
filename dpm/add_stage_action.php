<?php
include('shared/CommonManager.php');
if ($_POST['update_type'] == "add") {
	$sql = "insert into tbl_stage(stage_name,description,stage_type) values (:stage_name,:description,:stage_type)" ;
	$query = DbManager::fetchPDOQueryData('spectra_db', $sql, [":stage_name" => trim($_POST['stage_name']),":description" => trim($_POST['stage_desc']),":stage_type" => trim($_POST['stage_type'])]);
	if (!$query) {
		$_SESSION['success'] = "<strong>Record</strong> is not added";
	} else {
		$_SESSION['success'] = "<strong>Congratulations</strong> Stage information has been inserted successfully.";	 
	}
} else {
	$sql = "update tbl_stage set stage_name =:stage_name, description =:description, stage_type=:stage_type where stage_id=:stage_id";
	$query = DbManager::fetchPDOQuery('spectra_db', $sql, [":stage_name" => trim($_POST['stage_name']), ":description" => trim($_POST['stage_desc']), ":stage_type" => trim($_POST['stage_type']), ":stage_id" => trim($_POST['stage_id'])]);
    if (!$query) {
		$_SESSION['success'] = "<strong>Record</strong> is not updated";
	} else {
		$_SESSION['success'] = "<strong>Congratulations</strong> Product information has been updated successfully.";
	}
}
header("Location:Stagelist.php");
exit(0);
?>