<?php
include('shared/CommonManager.php');
$sql = "SELECT * from tbl_roles";
$res = DbManager::fetchPDOQueryData('spectra_db', $sql)["data"];
$cnt = count($res);
$product_info =array();
if($cnt > 0){
    $sr=1;
    foreach ($res as $result)
    {
        $product_data['sr_no']= $sr;
        $sr++;
        $product_data['roles']= trim($result['role_name']); 
        $product_data['action']='<a class="btn btn-default btn-circle" title="Modify" href="add_roles.php?role_id='.trim($result['role_id']).'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a class="btn btn-danger btn-circle"  data-placement="top" data-original-title=""  onclick=\'return confirm("Are you sure you want to delete this Roles?");\' title="" href="delete_roles.php?role_id='.trim($result['role_id']).'" ><i class="fa fa-trash"></i></a>'; 
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