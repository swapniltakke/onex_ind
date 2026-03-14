<?php
include('shared/CommonManager.php');
if ($_POST['update_type'] == "add") {
	$sql = "insert into tbl_product (product_name,description,create_dtts,ud_dtts,subassembly_req,mlfb_num) 
	values (:product_name,:description,:create_dtts,:ud_dtts,:subassembly_req,:mlfb_num)";
	$queryParams = [
		":product_name" => trim($_POST['product_name']),
		":description" => trim($_POST['product_desc']),
		":create_dtts" => date('Y-m-d H:i:s'),
		":ud_dtts" => date('Y-m-d H:i:s'),
		":subassembly_req" => trim($_POST['subassembly_req']),
		":mlfb_num" => trim($_POST['mlfb_num'])];
	$query = DbManager::fetchPDOQuery('spectra_db', $sql, $queryParams);

	if (!$query) {
		$_SESSION['success'] = "<strong>Record</strong> is not added";
	} else {	
		$_SESSION['success'] = "<strong>Congratulations</strong> Product information has been inserted successfully.";	 
	}
} else {
	$sql="update tbl_product set product_name = :product_name ,description = :description, ud_dtts =:getdate,subassembly_req=:subassembly_req,mlfb_num=:mlfb_num where product_id=:product_id";
	$query = DbManager::fetchPDOQuery('spectra_db', $sql, [
        ":product_name" => trim($_POST['product_name']), //notestatus = 0 is open
        ":description" => trim($_POST['product_desc']),
        ":getdate" => date('Y-m-d H:i:s'),
        ":subassembly_req" => trim($_POST['subassembly_req']),
        ":mlfb_num" => trim($_POST['mlfb_num']),
		":product_id" => trim($_POST['product_id'])]);

	if (!$query) {
		$_SESSION['success'] = "<strong>Record</strong> is not updated";
	} else {
		$_SESSION['success'] = "<strong>Congratulations</strong> Product information has been updated successfully.";
	}
}
header("Location:Productlist.php");
exit(0);
?>