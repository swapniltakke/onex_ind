<?php
include('shared/CommonManager.php');
if($_POST['update_type'] == "add") {
	$sql = "insert into tbl_workbench (title,title_icon) values (:title,:icon)";
	$query = DbManager::fetchPDOQueryData('spectra_db', $sql, [":title" => trim($_POST['title']), ":icon" => trim($_POST['icon'])]);
	if (!$query) { 	
		$_SESSION['success'] = "<strong>Record</strong> is not added";
	} else {	
		$_SESSION['success'] = "<strong>Congratulations</strong> Workbench information has been inserted successfully.";	 
	}
} else {
  	$sql = "update tbl_workbench set title =:title,title_icon =:title_icon where id=:workbench_id";
	$query = DbManager::fetchPDOQuery('spectra_db', $sql, [":title" => trim($_POST['title']),":title_icon" => trim($_POST['icon']),":workbench_id" => trim($_POST['workbench_id'])]);
    if (!$query) {
		$_SESSION['success'] = "<strong>Record</strong> is not updated";
	} else {
		$_SESSION['success'] = "<strong>Congratulations</strong> Workbench information has been updated successfully.";
	}
}
header("Location:Workbentchlist.php");
exit(0);
?>