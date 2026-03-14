<?php
include 'DatabaseConfig.php';
if(isset($_GET['id'])) {
    //$delete_ids = base64_decode($_GET['location_id']);
    $sql = "delete FROM tbl_master_checklist where check_id=".$_GET['id'];
//echo $sql;exit;	
$query = odbc_exec($conn, $sql);

}


$_SESSION['success'] = "<strong>Congratulations</strong> Checklist information has been deleted successfully.";
header("location:load_assembly_checklist.php");
?>
