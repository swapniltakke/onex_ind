<?php
include 'DatabaseConfig.php';
if(isset($_GET['uploadid'])) {
    //$delete_ids = base64_decode($_GET['location_id']);
    $sql = "delete FROM tbl_DailyUpload where uploadid=".$_GET['uploadid'];
//echo $sql;exit;	
$query = odbc_exec($conn, $sql);

}


$_SESSION['success'] = "<strong>Congratulations</strong> Daily information has been deleted successfully.";
header("location:Uploaddailyworkshit.php");
?>
