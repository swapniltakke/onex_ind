<?php
include('shared/CommonManager.php');
if ($_POST['update_type'] == "add") {
	$sql = "insert into tbl_error(error_name,description,error_type) values (:error_name,:description,:error_type)" ;
	$query = DbManager::fetchPDOQueryData('spectra_db', $sql, [":error_name" => trim($_POST['error_name']),":description" => trim($_POST['description']),":error_type" => trim($_POST['error_type'])]);
	if (!$query) { 	
		echo '<script>alert("not addedd>'; 
		$_SESSION['success'] = "<strong>Error</strong> is not added";
	} else {
   		echo '<script>alert("Addedd")</script>'; 
		$_SESSION['success'] = "<strong>Congratulations</strong> Error has been inserted successfully.";	 
	}
} else {
  	$sql = "update tbl_error set error_name =:error_name,description=:description,error_type=:error_type where error_id =:error_id";
	$query = DbManager::fetchPDOQuery('spectra_db', $sql, [":error_name" => trim($_POST['error_name']), ":description" => trim($_POST['description']), ":error_type" => trim($_POST['error_type']), ":error_id" => trim($_POST['error_id'])]);
    if (!$query) { 
		$_SESSION['success'] = "<strong>Error</strong> is not updated";
	} else {
		$_SESSION['success'] = "<strong>Congratulations</strong> Error has been updated successfully.";
	}
}
header("Location:Errorlist.php");
exit(0);
?>