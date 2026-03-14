<?php
include('shared/CommonManager.php');
$sql = "SELECT w.title,wt.w_type,wt.w_id FROM tbl_manageworkbench wt inner join tbl_workbench w on w.id=wt.workbench_id";
//echo $sql;exit;	
$res = DbManager::fetchPDOQueryData('spectra_db', $sql)["data"];
$cnt = count($res);
$product_info =array();
if($cnt > 0){
$sr=1;
foreach ($res as $result)
{
     $product_data['sr_no']= $sr;$sr++;
	 $product_data['workbench']= trim($result['title']);
	 
	$product_data['w_type']= trim($result['w_type']);
	//$product_data['icon']=trim(odbc_result($res, 'title_icon'));
	
	
	  
	$product_data['action']='<a class="btn btn-default btn-circle" title="Modify" href="add_workbenchtypes.php?w_id='.trim($result['w_id']).'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a class="btn btn-danger btn-circle"  data-placement="top" data-original-title=""  onclick=\'return confirm("Are you sure you want to delete this Workbench Types?");\' title="" href="delete_workbenchtypes.php?w_id='.trim($result['w_id']).'" ><i class="fa fa-trash"></i></a>'; 
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

