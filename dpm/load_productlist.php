<?php
include('shared/CommonManager.php');
$sql = "SELECT * FROM tbl_product";	
$res = DbManager::fetchPDOQueryData('spectra_db', $sql)["data"];
$cnt = count($res);
$product_info =array();
if ($cnt > 0) {
	$sr = 1;
	foreach ($res as $data) {
		$product_data['sr_no'] = $sr;
		$sr++;
		$product_data['product_id'] = trim($data['product_id']);
		$product_data['procuct_name'] = trim($data['product_name']);
		$product_data['description'] = trim($data['description']);
		if (trim($data['subassembly_req']) == 'Y') {
			$subassembly_req = "Yes";
		} else {
			$subassembly_req = "No";
		}
		$product_data['subassembly_req'] = $subassembly_req;
		$product_data['mlfb_num'] = trim($data['mlfb_num']);
		$product_data['action'] = '<a class="btn btn-default btn-circle" title="Modify" href="add_productlist.php?product_id='.trim($data['product_id']).'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a class="btn btn-danger btn-circle"  data-placement="top" data-original-title=""  onclick=\'return confirm("Are you sure you want to delete this Product?");\' title="" href="delete_product.php?product_id='.trim($data['product_id']).'" ><i class="fa fa-trash"></i></a>'; 
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