<?php
include 'DatabaseConfig.php';

$product_id = $_POST['product_id'];
$station_id = $_POST['station_id'];

$sql = "SELECT c2.checklist_item,c2.id,c1.check_req,c1.text_req 
FROM tbl_checklistdetails c1
inner join tbl_checklist c2 on c1.checklist_id=c2.id
inner join tbl_stage  c3 on c1.stage_id=c3.stage_id
inner join tbl_product p on c1.product_id =p.product_id
where  c3.stage_id='5' and p.product_id='$product_id'
and c1.station_id like '%$station_id%'
";
	
$query = odbc_exec($conn, $sql);





 
		$sr=1;$k=0; $j=0;
		while(odbc_fetch_row($query))
{
     $stage_data['sr_no']= $sr;
	 $stage_data['check_item']= odbc_result($query,'checklist_item');
     $query_remark1 ="select remarks,actual_output from tbl_transactions where stage_id='5' and station_id='$station_id' and product_id='$product_id'";
     //echo $query_remark1; //exit;
    $query_remark = odbc_exec($conn, $query_remark1);
        $remarks=odbc_result($query_remark,'remarks');
        $actual_output=odbc_result($query_remark,'actual_output');
        
        $actual_output1 = explode(",", $actual_output);
        //print_r($actual_output1); exit;
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
       $actual_otp ='<input type="radio" class="check_req" value="1" '.$check_o.' name="actual_opt'.$sr.'" id="check_req'.$sr.'"  > Ok
       <input type="radio" class="check_req" value="0" '.$check_z.' name="actual_opt'.$sr.'" id="check_req'.$sr.'" > Not Ok';
     $stage_data['actual_opt']= $actual_otp;
     $text_req=odbc_result($query,'text_req');
     $response ="";
     
     $remarks1 = explode(",", $remarks);
     for($i=0;$i < $text_req;$i++){
  
             $response .='<input type="text" id="'.$j.'" value="'.$remarks1[$j].'"  class="form-control" name="remark[]">&nbsp;';
 
            //}
         $j++; 
     }
     $response1 = $response;
     $stage_data['remark']= $response1;
	
	
	  
	//$stage_data['action']='<a class="btn btn-default btn-circle" title="Modify" href="add_ao.php?aao_code='.$row[0].'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a class="btn btn-danger btn-circle"  data-placement="top" data-original-title=""  onclick=\'return confirm("Are you sure you want to delete this Auditor?");\' title="" href="delete_ao.php?aao_code='.$row[0].'" ><i class="fa fa-trash"></i></a>'; 
 $stage_info[] = $stage_data;
 $k++; $sr++;
}

$results = array(
    "sEcho" => 1,
    "iTotalRecords" => count($stage_info),
    "iTotalDisplayRecords" => count($stage_info),
    "aaData" => $stage_info);

echo json_encode($results);

?>
