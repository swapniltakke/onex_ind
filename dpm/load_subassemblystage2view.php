<?php
include('shared/CommonManager.php');
$product_id = $_POST['product_id'];
$station_id = $_POST['station_id'];
//$sql = "SELECT c2.checklist_item,c2.id,c1.check_req,c1.text_req FROM tbl_checklistdetails as c1,tbl_checklist as c2,tbl_stage as c3 where c1.checklist_id=c2.id and c1.stage_id=c3.stage_id and c3.stage_id='1' ";
$sql = "SELECT c2.checklist_item,c2.id,c1.check_req,c1.text_req 
FROM tbl_checklistdetails c1
inner join tbl_checklist c2 on c1.checklist_id=c2.id
inner join tbl_stage  c3 on c1.stage_id=c3.stage_id
inner join tbl_product p on c1.product_id =p.product_id
where  c3.stage_id='2' and p.product_id='$product_id'
and concat(',', c1.station_id, ',') like '%,$station_id,%'
";	
$query = odbc_exec($conn, $sql);





 
		$sr=1;
		while(odbc_fetch_row($query))
{
     $stage_data['sr_no']= $sr;$sr++;
	 $stage_data['check_item']= odbc_result($query,'checklist_item');
	
	$stage_data['actual_opt']= odbc_result($query,'check_req');;
	$text_req=odbc_result($query,'text_req');
	$text_fieldappend="";
	for($i=0;$i < $text_req;$i++){

		$text_fieldappend .='<input type="text" class="form-control" name="remark[]" >';
	}
	
	$stage_data['remark']= $text_fieldappend;
	
	
	  
	//$stage_data['action']='<a class="btn btn-default btn-circle" title="Modify" href="add_ao.php?aao_code='.$row[0].'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a class="btn btn-danger btn-circle"  data-placement="top" data-original-title=""  onclick=\'return confirm("Are you sure you want to delete this Auditor?");\' title="" href="delete_ao.php?aao_code='.$row[0].'" ><i class="fa fa-trash"></i></a>'; 
 $stage_info[] = $stage_data;
}

$results = array(
    "sEcho" => 1,
    "iTotalRecords" => count($stage_info),
    "iTotalDisplayRecords" => count($stage_info),
    "aaData" => $stage_info);

echo json_encode($results);

?>
