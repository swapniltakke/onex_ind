<html>

<?php 
session_start();
include('shared/CommonManager.php');
$stage_id = $_POST['stage_id'];
$station_id = $_POST['station_id'];
$product_id = $_POST['product_id'];

$sql = "SELECT c2.checklist_item,c2.id,c1.check_req,c1.text_req,c1.text_lable_names
		FROM tbl_checklistdetails c1
		inner join tbl_checklist c2 on c1.checklist_id=c2.id
		inner join tbl_stage  c3 on c1.stage_id=c3.stage_id
		inner join tbl_product p on c1.product_id =p.product_id
		where  c3.stage_id=:stage_id and p.product_id=:product_id
		and concat(',', c1.station_id, ',') like :station_id";
$query = DbManager::fetchPDOQuery('spectra_db', $sql, [":stage_id" => "$stage_id", ":product_id" => "$product_id", ":station_id" => "%,$station_id,%"])["data"];

$response .='<link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
';
$query_remark1 ="select remarks,actual_output,tr_id,remark_comp from tbl_transactions where stage_id=:stage_id and station_id=:station_id and product_id=:product_id";
$$query_remark = DbManager::fetchPDOQuery('spectra_db', $query_remark1, [":stage_id" => "$stage_id", ":product_id" => "$product_id", ":station_id" => "%,$station_id,%"])["data"];

	   $remarks=($query_remark['remarks']);
	   $actual_output=($query_remark['actual_output']);
	   $actual_output1 = explode(",", $actual_output);
	   $remark_comp=($query_remark['remark_comp']);
	   $remark_comp1 = explode(",", $remark_comp);
	   $tr_id =($query_remark['tr_id']);
	   $response .="<input type='hidden' name='trans_id' id='trans_id' value='$tr_id'><input type='hidden' name='tran_stage_id' id='tran_stage_id' value='$stage_id'>";
$response .="<table id='example3'  class='table table-bordered table-striped' cellspacing='0' width='100%'>
		<thead><th width='1%'>Sr. No.</th>
        <th width='34%'>Checklist Item</th>
        <th width='15%'>Actual</th>
        <th width='40%'></th>
		<th width='10%'>Remarks</th>
		</thead><tbody>";
		
	$sr=1;	$j=0;$k=0;
		foreach ($$query as $response)
{
    $response .="<tr>";
    $response .="<td>".$sr."</td>";
    $response .="<td>".($query['checklist_item'])."</td>";
	
	   //print_r($actual_output1);

	   $response.="<td>";
	   if($actual_output1[$k] == 0){
		$check_z="checked";
	}
	else{
		$check_z="";
	}
	if($actual_output1[$k] == 1){
		//echo "Hello"; exit;
	 $check_o="checked";
 }
 else{
	 $check_o="";
 }
	
    if(($query['check_req'])== 'Y')
	 {
		 $response .='<input type="radio" value="1" '.$check_o.' class="check_req_up" name="actual_opt_up'.$sr.'"  '.$s1.' > Ok';
		 $response .='&nbsp;<input type="radio" '.$check_z.' class="check_req_up" value="0" name="actual_opt_up'.$sr.'" '.$s2.'>Not Ok';
	 }
	 else{
		$response .='<p style="display:block;"><input type="radio" '.$check_o.' value="1" name="actual_opt_up'.$sr.'" '.$s1.'> Ok <input type="radio" value="0" '.$check_z.' name="actual_opt_up'.$sr.'" '.$s2.'> Not Ok</p>';
 
	 }
	
	
    $response .="</td>";
		//echo $remarks; exit;
             $remarks1 = explode(",", $remarks);
			//print_r($remarks1); //exit;
		$stage_data['actual_opt']= $text_req;
	$text_req=($query['text_req']);
	$text_lblarr= explode(",",($query['text_lable_names']));
	$response .="<td><table>";

	for($i=0;$i < $text_req;$i++){
		$response .="<tr>";
           if($remarks1[$j] == "@"){
			$response .='<td>'.$text_lblarr[$i].': </td><td><input type="text" value=""  class="form-control" name="remark_up[]" >&nbsp;</td>';

		   }
		   else{
			$response .='<td>'.$text_lblarr[$i].': </td><td><input type="text" value="'.$remarks1[$j].'"  class="form-control" name="remark_up[]">&nbsp;</td>';

		   }
		   $response .="</tr>";
	    $j++;
	}
	

    $response .="</table></td>";

	$response .= "<td>";

$response .="<input type='text' class='form-control' name='remark_comp_up' value='$remark_comp1[$k]'>";
	$response .= "</td>";
    
    $response .= "</tr>";

$sr++; $k++;
}
		
	     $response.="</tbody></table>";
	    // $response .="alert('hello');";
//$response .="";

		$response .="<script>";
		 $response .="var table1= $('#example3').DataTable({
	  'paging'      : true,
	 'lengthChange': true,
      'searching'   : false,
      'ordering'    : false,
      'info'        : true,
	  'stateSave' : true,
      'autoWidth'   : false,
	  'serverSide' : false,
	  'destroy': true,
	 


	

	  
	  });";
		  $response .="</script>"; 
echo $response;
exit;
?>
</html>

		
		