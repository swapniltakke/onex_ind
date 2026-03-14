<?php 
session_start();
include('shared/CommonManager.php');
$actual_output = $_POST['actual_output'];
$remarks = implode(",",$_POST['remarks']);;
$stage_id = $_POST['stage_id'];
$tr_id = $_POST['tr_id'];

$sql="update tbl_transactions set actual_output=:actual_output,remarks=:remarks where tr_id=:tr_id and stage_id=:stage_id" ;
$query = DbManager::fetchPDOQuery('spectra_db', $sql, [
  ":actual_output" => $actual_output, //notestatus = 0 is open
  ":remarks" => $remarks,
  ":tr_id" => $tr_id,
  ":stage_id" => $stage_id]);

if (!$query) 
{ 	

//$_SESSION['success'] = "<strong>Record</strong> is not added";
  
  $res['error'] = "ERROR"; 
}
else
{
    $res['update']="UPDATE";
}
$response['success']=$res;
header("Content-type:application/json");
echo json_encode($response);
die();
?>