<?php
include 'DatabaseConfig.php';
$sql = "SELECT * FROM tbl_transactions";

//$sql = "SELECT product_name,LEFT(barcode, 8) AS barcode_first8, RIGHT(barcode, 12) AS barcode_last12 from tbl_transactions ";
//echo $sql;exit;	
$res = odbc_exec($conn, $sql);

$cnt = odbc_num_rows($res);
$product_info =array();
if($cnt > 0){
//First lets get the username and password from the user
//$result = mysqli_query("SELECT * FROM login");

 
		$sr=1;
		  while(odbc_fetch_row($res))
{
     $product_data['sr_no']= $sr;$sr++;
     $product_data['procuct_id']= trim(odbc_result($res, 'product_id'));
	 $product_data['procuct_name']= trim(odbc_result($res, 'product_name'));
	 $product_data['barcode']=trim(odbc_result($res, 'barcode'));
     $product_data['remarks']=trim(odbc_result($res, 'remarks'));
    // $product_data['barcode1']=trim(odbc_result($res, 'barcode'));
     //$product_data = substr($barcode, 0, 8);
    
	$product_data['action']='<a class="btn btn-default btn-circle" title="Modify" href="add_warehouselist.php?product_id='.trim(odbc_result($res, 'product_id')).'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a class="btn btn-danger btn-circle"  data-placement="top" data-original-title=""  onclick=\'return confirm("Are you sure you want to delete this Product?");\' title="" href="delete_warehouse.php?product_id='.trim(odbc_result($res, 'product_id')).'" ><i class="fa fa-trash"></i></a>'; 
     $product_info[] = $product_data;
}
}

$results = array(
    "sEcho" => 1,
    "iTotalRecords" => count($product_info),
    "iTotalDisplayRecords" => count($product_info),
    "aaData" => $product_info);

echo json_encode($results);

?>


