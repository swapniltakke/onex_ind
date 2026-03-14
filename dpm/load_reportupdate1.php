<?php
include 'DatabaseConfig.php';
$Order_id = $_POST['prod_order_id'];
//please update your Query for Nested //to get barcodes for that order
$sql = "select * from tbl_DailyUpload  where ProductinOrder = '$station_id' and Barcode is not null";
//echo $sql;exit;	
$res = odbc_exec($conn, $sql);
$cnt = odbc_num_rows($res);
$product_info =array();
if($cnt > 0)
{
	$Barcode = odbc_result($res,'Barcode');
	//To get stage and all details of transactions
	$sql1 = "select * from tbl_transactions where barcode = '$Barcode' order by stage_id";
	if($cnt1 > 0){
 
		$sr=1;
		while(odbc_fetch_row($res))
		{
			$station_name = trim(odbc_result($res, 'station_name'));	 
			$product_name= trim(odbc_result($res, 'product_name'));
			$stage_id= trim(odbc_result($res, 'stage_id'));
			$sql2 = "select * from tbl_transactions where barcode = '$Barcode' order by stage_id";
			//$product_data['remarks']=trim(odbc_result($res, 'remarks'));    
			//$product_data['actual_output']=trim(odbc_result($res, 'actual_output'));    
			//$product_data['remark_comp']=trim(odbc_result($res, 'remark_comp')); 
    
			$product_data['sr_no']= $sr;$sr++;
			$product_data['station_name']= "";
			// $password = base64_decode($row[1]);
			//$product_data['activity']= trim(odbc_result($res, 'password'));
			$product_data['activity']= "";
			$product_data['check']= "";
			$product_data['remark']= "";
	  
			$product_info[] = $product_data;
		}
}
}
$results = array(
    "sEcho" => 1,
    "iTotalRecords" => count($product_info),
    "iTotalDisplayRecords" => count($product_info),
    "aaData" => $product_info);

echo json_encode($results);

?>
