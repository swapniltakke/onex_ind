<?php
include 'DatabaseConfig.php';
//echo $_POST['update_type']; exit;
if($_POST['update_type'] == "add") {
echo "hello";
		//$sql="insert into tbl_product (product_name,description,create_dtts,ud_dtts,subassembly_req,mlfb_num) values ('".trim($_POST['product_name'])."','".trim($_POST['product_desc'])."',GETDATE(),GETDATE(),'".trim($_POST['subassembly_req'])."','".trim($_POST['mlfb_num'])."')" ;
////echo $sql; exit;
//	    $query = odbc_exec($conn, $sql);
//	 if (!$query) 
//	 { 	
//		
//		$_SESSION['success'] = "<strong>Record</strong> is not added";
//		
//	 }
//	 else
//	 {
//  
//$_SESSION['success'] = "<strong>Congratulations</strong> Product information has been inserted successfully.";	 
//	 }


}
else
{
  
		$sql="update tbl_master_checklist set check_type ='".trim($_POST['check_type'])."',check_item ='".trim($_POST['activity'])."',check_req='".trim($_POST['chk_box'])."',text_req='".trim($_POST['text_req'])."',product='".trim($_POST['product'])."',new_station='".trim($_POST['new_station_no'])."' where check_id='".trim($_POST['id'])."'";
		//echo $sql;exit;
        $query = odbc_exec($conn, $sql);
        if (!$query) 
        { 
		
		$_SESSION['success'] = "<strong>Record</strong> is not updated";
	 }
	 else
	 {
     //echo "Record updated successfully!";
		$_SESSION['success'] = "<strong>Congratulations</strong> Checklist information has been updated successfully.";
	 }


}
header("Location:load_assembly_checklist.php");
exit(0);
?>
