<?php
include('shared/CommonManager.php');
$sql = "SELECT * from tbl_error";
$res = DbManager::fetchPDOQueryData('spectra_db', $sql)["data"];
$cnt = count($res);
$product_info = array();
if ($cnt > 0) {
    $sr = 1;
	foreach ($res as $data) {    
        $product_data['error_id'] = $sr;
        $sr++;
        $product_data['error_name'] = trim($data['error_name']);
        $product_data['description'] = trim($data['description']);
        $product_data['error_type'] = trim($data['error_type']);
        $product_data['action'] = '<a class="btn btn-default btn-circle" title="Modify" href="add_error.php?error_id='.trim($data['error_id']).'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a class="btn btn-danger btn-circle"  data-placement="top" data-original-title=""  onclick=\'return confirm("Are you sure you want to delete this error?");\' title="" href="delete_error.php?error_id='.trim($data['error_id']).'" ><i class="fa fa-trash"></i></a>'; 
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