<?php
include 'DatabaseConfig.php';

if (isset($_GET['sr_id'])) {
    //$delete_ids = base64_decode($_GET['location_id']);
   // echo $_GET['sr_id']; exit;
    $del_check ="select sop_file from tbl_managesop where sr_id=".$_GET['sr_id'];
    $rs = odbc_exec($conn, $del_check);
    if($rs){
       $sop_file = "uploads/".odbc_result($rs,'sop_file');
    unlink($sop_file);
  
    $sql = "delete FROM tbl_managesop where sr_id=".$_GET['sr_id'];
//echo $sql;exit;	
$query = odbc_exec($conn, $sql);
//mysqli_query($conn,"ALTER TABLE tbl_roles AUTO_INCREMENT = 1");
if($query){
    echo "Deleted Successfully";

}
}
}
?>