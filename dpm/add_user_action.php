<?php
include('shared/CommonManager.php');
if($_POST['update_type'] == "add") {
	$encrypted_qr_id = $encrypted_password = "";
	if ($_POST['qr_id'] != "") {
		$encrypted_qr_id = SharedManager::encrypt_password(trim($_POST['qr_id']));
	}
	$encrypted_password = SharedManager::encrypt_password(trim($_POST['password']));
	$sql="insert into tbl_user_login(user_string,user_name,password,role_id) values (:qr_id,:username,:password,:role_id)";
	$queryParams = [
		":qr_id" => $encrypted_qr_id,
		":username" => trim($_POST['username']),
		":password" => $encrypted_password,
		":role_id" => trim($_POST['role_id'])
		];
	$query = DbManager::fetchPDOQueryData('spectra_db', $sql,$queryParams);
	if (!$query) { 	
		$_SESSION['success'] = "<strong>Record</strong> is not added";
	} else {
		$_SESSION['success'] = "<strong>Congratulations</strong> User information has been inserted successfully.";	 
	}
}
else
{
	$encrypted_qr_id = $encrypted_password = "";
	if ($_POST['qr_id'] != "") {
		$encrypted_qr_id = SharedManager::encrypt_password(trim($_POST['qr_id']));
	}
	$encrypted_password = SharedManager::encrypt_password(trim($_POST['password']));
	$sql="update tbl_user_login set user_string =:qr_id,user_name=:user_name,password=:password,role_id=:role_id WHERE user_id =:user_id";
	$query = DbManager::fetchPDOQuery('spectra_db', $sql, [
		":qr_id" => $encrypted_qr_id, 
		":user_name" =>trim($_POST['username']),
		":password" => $encrypted_password,
		":role_id" => trim($_POST['role_id']),
		":user_id" => trim($_POST['user_id'])]);
	if (!$query) {
		$_SESSION['success'] = "<strong>Record</strong> is not updated";
	} else {
		$_SESSION['success'] = "<strong>Congratulations</strong> User information has been updated successfully.";
	}
}
header("Location:manage_users_details.php");
exit(0);
?>