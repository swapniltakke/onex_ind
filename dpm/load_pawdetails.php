<?php
include('shared/CommonManager.php');
$sql = "SELECT * FROM tbl_pawbarcode order by tr_id DESC";
$res = DbManager::fetchPDOQueryData('spectra_db', $sql)["data"];
$cnt = count($res);
$product_info = array();
if ($cnt > 0) {
    $sr = 1;
    foreach ($res as $data) {    
        $product_data['sr_no'] = $sr;
        $sr++;
        $product_data['procuct_id'] = trim($data['product_id']);
        $product_data['procuct_name'] = trim($data['product_name']);
        $barcode = trim($data['barcode']);
        $product_data['barcode'] = $barcode;
        $product_data['part1'] = substr($barcode, 0, 8);
        $product_data['part2'] = substr($barcode, 8, -29);
        $product_data['part3'] = substr($barcode, -29, 10);
        $product_data['part4'] = substr($barcode, -18, 6);
        $product_data['part5'] = substr($barcode, -12);
        $product_data['remark'] = trim($data['remark']);
        $product_data['up_date'] = trim($data['up_date']);
        $product_data['action'] = '<a class="btn btn-default btn-circle" title="Modify" href="update_warehouselist.php?product_id='.trim($data['product_id']).'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a class="btn btn-danger btn-circle"  data-placement="top" data-original-title=""  onclick=\'return confirm("Are you sure you want to delete this Product?");\' title="" href="delete_warehouse.php?product_id='.trim($data['product_id']).'" ><i class="fa fa-trash"></i></a>'; 
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