<?php
include('shared/CommonManager.php');
$login_type = $_SESSION['role_name'];
if ($login_type == "Stamping") {
    $role_name = "Manufacturing";
} else if ($login_type == "Manufacturing") {
    $role_name = "Stamping";
}

$where_clause = "";
$status = "";
if ($_GET['status'] == "0") {
	$where_clause .= "AND status=:status ";
    $status = "0";
}

$product_info = array();
$create_date_from = isset($_GET['create_date_from']) ? $_GET['create_date_from'] : '';
$create_date_to = isset($_GET['create_date_to']) ? $_GET['create_date_to'] : '';

if ($create_date_from && $create_date_to) {
    $where_clause .= "AND up_date BETWEEN '$create_date_from' AND '$create_date_to'";
} elseif ($create_date_from) {
    $where_clause .= "AND up_date >= '$create_date_from'";
} elseif ($create_date_to) {
    $where_clause .= "AND up_date <= '$create_date_to'";
}

$main_sql = "SELECT * FROM tbl_transactions WHERE stage_id=:stage_id $where_clause GROUP BY barcode ORDER BY tr_id DESC";
if ($_GET['status'] == "0") {
	$main_res = DbManager::fetchPDOQueryData('spectra_db', $main_sql, [":stage_id" => "8", ":status" => "$status"])["data"];
} else {
	$main_res = DbManager::fetchPDOQueryData('spectra_db', $main_sql, [":stage_id" => "8"])["data"];
}
if (is_array($main_res) && !empty($main_res)) {
	$sql = "SELECT *
			FROM tbl_transactions
			WHERE stage_id =:stage_id
			$where_clause
			GROUP BY barcode
			ORDER BY tr_id DESC";
	if ($_GET['status'] == "0") {
		$res = DbManager::fetchPDOQueryData('spectra_db', $sql, [":stage_id" => "8", ":status" => "$status"])["data"];
	} else {
		$res = DbManager::fetchPDOQueryData('spectra_db', $sql, [":stage_id" => "8"])["data"];	
	}
	$cnt = count($res);
	if ($cnt > 0) {
		$sr = 1;
		foreach ($res as $data) {
			$product_data['sr_no'] = $sr;
			$sr++;
			$product_data['id'] = trim($data['tr_id']);
			$product_data['product_name'] = trim($data['product_name']);
			$product_data['product_id'] = trim($data['product_id']);
			$product_data['station_id'] = trim($data['station_id']);
			$product_data['barcode'] = trim($data['barcode']);
			$product_data['serial_no'] = substr($product_data['barcode'], 0, 8);
			$product_data['sales_order_no'] = substr($product_data['barcode'], -29, 10);
			$product_data['item_no'] = substr($product_data['barcode'], -18, 6);
			$product_data['production_order_no'] = substr($product_data['barcode'], -12);
			$product_data['user'] = trim($data['user_id']);
			$product_data['create_date'] = trim($data['up_date']);
			$product_data['status'] = ($data['status'] == 1) ? 'Completed' : 'Pending';
			$product_data['action']  = '<a class="btn btn-default btn-circle" title="Modify" href="generatestamping.php?id='.trim($data['tr_id']).'"><i class="fa fa-edit"></i></a>'; 
			$product_info[] = $product_data;
		}
	}
}
$results = array(
    "sEcho" => 1,
    "iTotalRecords" => count($product_info),
    "iTotalDisplayRecords" => count($product_info),
    "aaData" => $product_info);

echo json_encode($results);
?>