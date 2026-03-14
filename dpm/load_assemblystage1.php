<?php
include('shared/CommonManager.php');
$product_id = $_POST['product_id'];
$station_id = $_POST['station_id'];

$sql = "SELECT c2.checklist_item,c2.id,c1.check_req,c1.text_req,c1.text_lable_names
		FROM tbl_checklistdetails c1
		inner join tbl_checklist c2 on c1.checklist_id=c2.id
		inner join tbl_stage c3 on c1.stage_id=c3.stage_id
		inner join tbl_product p on c1.product_id =p.product_id
		where c3.stage_id=:stage_id and p.product_id=:product_id
		and c1.station_id like :station_id";
$query = DbManager::fetchPDOQuery('spectra_db', $sql, [":product_id" => "$product_id", ":stage_id" => "4", ":station_id" => "%$station_id%"])["data"];
$cnt = count($query);
$sr = 1;
foreach ($query as $data) {
	$stage_data['sr_no'] = $sr;
	$sr++;
	$stage_data['check_item'] = $data['checklist_item'];
	$stage_data['actual_opt'] = $data['check_req'];
	$text_req = $data['text_req'];
	$text_lable_nmarr = explode(",",$data['text_lable_names']);
	$text_fieldappend = "<table>";
	for ($i=0;$i < $text_req;$i++) {
		$text_fieldappend .="<tr>";
		$text_fieldappend .='<td>'.$text_lable_nmarr[$i].' :</td><td> <input type="text" style="form-control" name="remark[]" ></td>';
		$text_fieldappend .='</tr>';
	}
	$text_fieldappend .='</table>';
	$stage_data['remark']= $text_fieldappend;
	//$stage_data['action']='<a class="btn btn-default btn-circle" title="Modify" href="add_ao.php?aao_code='.$row[0].'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a class="btn btn-danger btn-circle"  data-placement="top" data-original-title=""  onclick=\'return confirm("Are you sure you want to delete this Auditor?");\' title="" href="delete_ao.php?aao_code='.$row[0].'" ><i class="fa fa-trash"></i></a>'; 
	$stage_info[] = $stage_data;
}
if ($cnt == 0) {
	$stage_info = array();
}
$results = array(
    "sEcho" => 1,
    "iTotalRecords" => count($stage_info),
    "iTotalDisplayRecords" => count($stage_info),
    "aaData" => $stage_info);

echo json_encode($results);
//exit;
?>