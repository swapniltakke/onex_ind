<?php


include 'DatabaseConfig.php';
$sql = "SELECT * FROM tbl_stage";
	
$res = odbc_exec($conn, $sql);
$cnt = odbc_num_rows($res);
$product_info =array();
if($cnt > 0){
		$sr=1;
		while(odbc_fetch_row($res))
{
     $stage_data['sr_no']= $sr;$sr++;
	 $stage_data['stage_id']= trim(odbc_result($res, 'stage_id'));
	 
	$stage_data['stage_name']= trim(odbc_result($res, 'stage_name'));
	$stage_data['description']=trim(odbc_result($res, 'description'));
	$stage_data['stage_type']=trim(odbc_result($res, 'stage_type'));
	
	  
	$stage_data['action']='<a class="btn btn-default btn-circle" title="Modify" href="add_stagelist.php?stage_id='.trim(odbc_result($res, 'stage_id')).'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a class="btn btn-danger btn-circle"  data-placement="top" data-original-title=""  onclick=\'return confirm("Are you sure you want to delete this Stage?");\' title="" href="delete_stage.php?stage_id='.trim(odbc_result($res, 'stage_id')).'" ><i class="fa fa-trash"></i></a>'; 
 $stage_info[] = $stage_data;
}
}
$results = array(
    "sEcho" => 1,
    "iTotalRecords" => count($stage_info),
    "iTotalDisplayRecords" => count($stage_info),
    "aaData" => $stage_info);

echo json_encode($results);

?>