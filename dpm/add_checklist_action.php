<?php
include('shared/CommonManager.php');
if($_POST['update_type'] == "add") {
	$sql = "insert into tbl_checklist(checklist_item,description) values (:checklist_item,:description)";
	$query = DbManager::fetchPDOQueryData('spectra_db', $sql, [":checklist_item" => trim($_POST['checklist_item']),":description" => trim($_POST['check_desc'])]);
	if (!$query) {
		$_SESSION['success'] = "<strong>Record</strong> is not added";
	} else {
		$_SESSION['success'] = "<strong>Congratulations</strong> Workbench information has been inserted successfully.";	 
	}
} else {
	$sql = "update tbl_checklist set checklist_item =:checklist_item,description =:description where id=:checklist_id";
	$query = DbManager::fetchPDOQuery('spectra_db', $sql, [":checklist_item" => trim($_POST['checklist_item']), ":description" => trim($_POST['check_desc']), ":checklist_id" => trim($_POST['checklist_id'])]);
    if (!$query) {
		$_SESSION['success'] = "<strong>Record</strong> is not updated";
	} else {
		$_SESSION['success'] = "<strong>Congratulations</strong> Workbench information has been updated successfully.";
	}
}
header("Location:Checklistdetails.php");
exit(0);
?>