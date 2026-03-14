<?php
include('shared/CommonManager.php');
if ($_POST['update_type'] == "add") {
	$role_name = trim($_POST['role_name']);
	$check_role = "select count(*) as cnt from tbl_roles where role_name=:role_name";
	$rs_role = DbManager::fetchPDOQuery('spectra_db', $check_role, [":role_name" => $role_name])["data"];
	$cnt = $rs_role['cnt'];
    if($cnt > 0){
		echo "<script type='text/javascript'>";
		echo "alert('Already Added this Roles');";
		echo "window.location.href='add_roles.php'";
		echo "</script>";
	} else {
		$sql = "insert into tbl_roles(role_name) values (:role_name)" ;
	    $query = DbManager::fetchPDOQueryData('spectra_db', $sql, [":role_name" => trim($_POST['role_name'])]);
		if (!$query) { 	
			$_SESSION['success'] = "<strong>Record</strong> is not added";			
		} else {
			$_SESSION['success'] = "<strong>Congratulations</strong> Roles information has been inserted successfully.";	 
			header("Location:manage_roles_details.php");
			exit(0);
		}
	}
}
else
{
	$role_name = trim($_POST['role_name']);
	$check_role = "select count(*) as cnt from tbl_roles where role_name=:role_name";
	$rs_role = DbManager::fetchPDOQuery('spectra_db', $check_role, [":role_name" => $role_name])["data"];
	$cnt = $rs_role['cnt'];
    if($cnt > 0){
		echo "<script type='text/javascript'>";
		echo "alert('Allready Added this Roles');";
		echo "window.location.href='manage_roles_details.php'";
		echo "</script>";
	} else {
		$sql = "update tbl_roles set role_name =:role_name where role_id=:role_id";
		$query = DbManager::fetchPDOQuery('spectra_db', $sql, [":role_name" => trim($_POST['role_name']), ":role_id" => trim($_POST['role_id'])]);
        if (!$query) {		
			$_SESSION['success'] = "<strong>Record</strong> is not updated";
		} else {
			$_SESSION['success'] = "<strong>Congratulations</strong> Roles information has been updated successfully.";
			header("Location:manage_roles_details.php");
			exit(0); 
		}
	}
}
?>