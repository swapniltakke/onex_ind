<?php
include('shared/CommonManager.php');
if (isset($_GET['w_id'])) {
    //$delete_ids = base64_decode($_GET['location_id']);
    //$sql = "delete FROM tbl_manageworkbench where w_id=".$_GET['w_id'];
    $sql = "delete FROM tbl_manageworkbench where w_id=:w_id";
//echo $sql;exit;	
$query = DbManager::fetchPDOQuery('spectra_db', $sql, [":w_id" => $_GET['w_id']]);
//mysqli_query($conn,"ALTER TABLE tbl_stage AUTO_INCREMENT = 1");
}


$_SESSION['success'] = "<strong>Congratulations</strong> Workbench Type information has been deleted successfully.";
header("location:Manageworkbentchlist.php");
?>
