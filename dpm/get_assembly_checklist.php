<?php
include 'DatabaseConfig.php';
$sql = "SELECT * FROM tbl_master_checklist";
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
	 $product_data['check_type']=trim(odbc_result($res, 'check_type'));
	 
	$product_data['activity']= trim(odbc_result($res, 'check_item'));
	if(trim(odbc_result($res,'check_req')) == 'Y')
	{
		$chk_box_value= "Yes";

	}
	else 
	{
		$chk_box_value= "No";
	}
	$product_data['chk_box'] = $chk_box_value;
	
	$product_data['text_req']=trim(odbc_result($res, 'text_req'));
	$product_data['label_text']=trim(odbc_result($res, 'label_text'));
	$product_data['product']= trim(odbc_result($res, 'product'));
	$product_data['new_station_no']= trim(odbc_result($res, 'new_station'));
	
	
	$product_data['action']='<a class="btn btn-default btn-circle" title="Modify" href="update_assembly_checklist.php?id='.trim(odbc_result($res, 'check_id')).'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a class="btn btn-danger btn-circle"  data-placement="top" data-original-title=""  onclick=\'return confirm("Are you sure you want to delete this Checklist Item?");\' title="" href="delete_assembly_checklist_data.php?id='.trim(odbc_result($res, 'check_id')).'" ><i class="fa fa-trash"></i></a>'; 
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


