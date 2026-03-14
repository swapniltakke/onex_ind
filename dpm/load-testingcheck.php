<?php
include 'DatabaseConfig.php';
$conn = new mysqli($HostName, $HostUser, $HostPass, $DatabaseName);
if (!$conn) {
	die ('Failed to connect to MySQL: ' . mysqli_connect_error());	
}
$stage_id = $_POST['stage_id'];

$sql = "SELECT c2.checklist_item FROM tbl_checklistdetails as c1,tbl_checklist as c2,tbl_stage as c3 where c1.checklist_id=c2.id and c1.stage_id=c3.stage_id and c3.stage_id='$stage_id' ";
	
$query = mysqli_query($conn, $sql);

if (!$query) {
	die ('SQL Error: ' . mysqli_error($conn));
}



 
		$sr=1;
		while($row=mysqli_fetch_array($query))
{
     $stage_data['sr_no']= $sr;$sr++;
	 $stage_data['check_item']= $row[0];
	 
	$stage_data['actual_opt']= '<input type="checkbox" name="actual_opt[]">';
	$stage_data['remark']= '<input type="text" class="form-control" name="remark[]">';
	
	
	  
	//$stage_data['action']='<a class="btn btn-default btn-circle" title="Modify" href="add_ao.php?aao_code='.$row[0].'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a class="btn btn-danger btn-circle"  data-placement="top" data-original-title=""  onclick=\'return confirm("Are you sure you want to delete this Auditor?");\' title="" href="delete_ao.php?aao_code='.$row[0].'" ><i class="fa fa-trash"></i></a>'; 
 $stage_info[] = $stage_data;
}

$results = array(
    "sEcho" => 1,
    "iTotalRecords" => count($stage_info),
    "iTotalDisplayRecords" => count($stage_info),
    "aaData" => $stage_info);

echo json_encode($results);

?>
