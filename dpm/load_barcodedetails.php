<?php
session_start();
$user_id=$_SESSION['user_id'];
include('shared/CommonManager.php');



$sql = "SELECT tr.tr_id,tr.product_name,tr.barcode FROM tbl_transactions as tr,tbl_product as pd where pd.product_id=tr.product_id and user_id= $user_id";
//echo $sql;exit;	
$query = DbManager::fetchPDOQueryData('spectra_db', $sql)["data"];

//First lets get the username and password from the user
//$result = mysqli_query("SELECT * FROM login");

 
		$sr=1;
		foreach ($query as $row)
{
     $product_data['sr_no']= $sr;$sr++;
	 $product_data['product_name']= $row[1];
	$product_data['barcode']= $row[2];

	
	
	  
	$product_data['view']='<a class="btn btn-default btn-circle" title="View" href="?tr_id='.$row[0].'"><i class="fa fa-edit"></i></a>'; 
 $product_info[] = $product_data;
}

$results = array(
    "sEcho" => 1,
    "iTotalRecords" => count($product_info),
    "iTotalDisplayRecords" => count($product_info),
    "aaData" => $product_info);

echo json_encode($results);

?>
