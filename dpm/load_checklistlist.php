<?php
include('shared/CommonManager.php');
$sql = "SELECT * FROM tbl_checklist";	
$res = DbManager::fetchPDOQueryData('spectra_db', $sql)["data"];
$sr = 1;
foreach ($res as $data) {
	$product_data['sr_no']= $sr;
	$sr++;
	$product_data['check_id'] = trim($data['id']);
	$product_data['checklist_item'] = trim($data['checklist_item']);
	$product_data['description'] = trim($data['description']);
	$product_data['action'] = '<a class="btn btn-default btn-circle" title="Modify" href="add_checklistitems.php?checklist_id='.trim($data['id']).'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a class="btn btn-danger btn-circle"  data-placement="top" data-original-title=""  onclick=\'return confirm("Are you sure you want to delete this Checklist?");\' title="" href="delete_checklist.php?checklist_id='.trim($data['id']).'" ><i class="fa fa-trash"></i></a>'; 
	$product_info[] = $product_data;
}
$results = array(
    "sEcho" => 1,
    "iTotalRecords" => count($product_info),
    "iTotalDisplayRecords" => count($product_info),
    "aaData" => $product_info);

echo json_encode($results);
?>