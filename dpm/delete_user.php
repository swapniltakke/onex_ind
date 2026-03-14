<?php
include('shared/CommonManager.php');
if (isset($_GET['user_id'])) {
    //$delete_ids = base64_decode($_GET['location_id']);
    //$sql = "delete FROM tbl_user_login where user_id=".$_GET['user_id'];
    $sql = "delete FROM tbl_user_login where user_id=:user_id";
//echo $sql;exit;	
//$query = DbManager::fetchPDOQuery('spectra_db', $sql);
$query = DbManager::fetchPDOQuery('spectra_db', $sql, [":user_id" => $_GET['user_id']]);
//mysqli_query($conn,"ALTER TABLE tbl_stage AUTO_INCREMENT = 1");
}


$_SESSION['success'] = "<strong>Congratulations</strong> User information has been deleted successfully.";
header("location:manage_users_details.php");
?>
