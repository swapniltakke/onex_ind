<?php
include('shared/CommonManager.php');
$sql = "SELECT u.user_name,u.password,r.role_name,u.user_id FROM tbl_user_login u,tbl_roles r where u.role_id=r.role_id";
$res =  DbManager::fetchPDOQuery('spectra_db', $sql)["data"];
$cnt = count($res);
$product_info = array();
if($cnt > 0) {
	$sr=1;
	foreach ($res as $result)
	{
		$product_data['sr_no'] = $sr;
		$sr++;
		$decrypted_password = "";
		$decrypted_password = SharedManager::decrypt_password(trim($result['password']));
		$product_data['user_name'] = trim($result['user_name']);
		$product_data['upassword'] = $decrypted_password;
		$product_data['role'] = trim($result['role_name']);
		$product_data['action'] = '<a class="btn btn-default btn-circle" title="Modify" href="add_user.php?user_id='.trim($result['user_id']).'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a class="btn btn-danger btn-circle"  data-placement="top" data-original-title=""  onclick=\'return confirm("Are you sure you want to delete this User?");\' title="" href="delete_user.php?user_id='.trim($result['user_id']).'" ><i class="fa fa-trash"></i></a>'; 
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