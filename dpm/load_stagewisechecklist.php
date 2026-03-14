<?php
include('shared/CommonManager.php');
$sql = "SELECT 
    s.stage_name,
    c.checklist_item,
    c1.check_id,
    c1.text_req,
    c1.check_req,
    c1.text_lable_names,
    p.product_name,
    c1.station_id,
    GROUP_CONCAT(st.station_name ORDER BY st.station_name ASC) AS station_names
	FROM 
		tbl_checklistdetails c1
	INNER JOIN 
		tbl_checklist c ON c.id = c1.checklist_id
	INNER JOIN 
		tbl_stage s ON s.stage_id = c1.stage_id
	INNER JOIN 
		tbl_product p ON p.product_id = c1.product_id
	LEFT JOIN 
		tbl_station st ON FIND_IN_SET(st.station_id, c1.station_id)
	GROUP BY 
		s.stage_name,
		c.checklist_item,
		c1.check_id,
		c1.text_req,
		c1.check_req,
		c1.text_lable_names,
		p.product_name,
		c1.station_id
	ORDER BY c1.check_id DESC";
$res = DbManager::fetchPDOQueryData('spectra_db', $sql)["data"];
$cnt = count($res);
$product_info = array();
if ($cnt > 0) {
	$sr = 1;
	foreach ($res as $data) {
		$product_data['sr_no'] = $sr;
		$sr++;
		$product_data['check_id'] = trim($data['check_id']);
		$product_data['stage_name'] = trim($data['stage_name']);
		$product_data['checklist_item'] = trim($data['checklist_item']);
		$product_data['text_req'] = trim($data['text_req']);
		$product_data['text_lable_name'] = trim($data['text_lable_names']);
		$check_req = trim($data['check_req']);
		if ($check_req == 'Y') {
			$check_req = 'Yes';
		} else {
			$check_req = 'No';
		}
		$product_data['check_req'] = $check_req;
		$product_data['product_name'] = trim($data['product_name']);
		$product_data['station_name'] = trim($data['station_names']);
		$product_data['action']  = '<a class="btn btn-default btn-circle" title="Modify" href="add_stagewisecheckitem.php?id='.trim($data['check_id']).'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a class="btn btn-danger btn-circle"  data-placement="top" data-original-title=""  onclick=\'return confirm("Are you sure you want to delete this Checklist?");\' title="" href="delete_stagechecklist.php?id='.trim($data['check_id']).'" ><i class="fa fa-trash"></i></a>'; 
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