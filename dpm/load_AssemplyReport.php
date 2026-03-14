<?php
include 'DatabaseConfig.php';
$station_id = $_POST['station_id'];
$product_id = $_POST['product_id'];
//$work_id = $_POST['work_id'];
//if(station_id == '' && product_id == '' && work_id == '')
echo $station_id; exit; 
if($station_id != '' && $product_id != '')
{
$sql1 = "SELECT * FROM  tbl_DailyUpload where ProductinOrder ='$station_id'  and uploaddate = '$product_id';
}

//where t.station_id='$station_id' and t.end_time='1900-01-01 00:00:00.000'";
echo $sql1;exit;	
$resp = odbc_exec($conn, $sql1);
$cntp = odbc_num_rows($resp);
if($cntp > 0)
{
	$productname = odbc_result(left($resp,8),'product'); //left(product,8) as prodname
	$SAPno = odbc_result($resp,'SAPNo');
	$Clientno = odbc_result($resp,'Client');
	$Productfull = odbc_result($resp,'Product');
	$Rating = odbc_result($resp,'rating');
	$Barcode = odbc_result($resp,'Barcode');
}

$sql1 = "SELECT * FROM  tbl_transactions  where Barcode ='$Barcode' order by trdet_id";	
$res = odbc_exec($conn, $sql1);
$cnt = odbc_num_rows($res);
 
if($cnt > 0){
$sr=1;
while(odbc_fetch_row($res))
{
     $product_data['sr_no']= $sr;$sr++;
     $product_data['tr_id'] = odbc_result($res,'tr_id');
	 $product_data['station_name']= trim(odbc_result($res, 'station_name'));	 
	$product_data['product_name']= trim(odbc_result($res, 'product_name'));
	$product_data['stage_id']=trim(odbc_result($res, 'stage_id'))." ( ".trim(odbc_result($res, 'stage_id'))." )";
	//To get checklist details
	$sql2 ="SELECT a.check_id, a.stage_id, a.checklist_id, b.checklist_item, a.text_req, a.check_req, a.product_id, a.station_id, a.text_lable_names 
	FROM  tbl_checklist b INNER JOIN tbl_checklistdetails a ON b.id = a.checklist_id where a.product_id ='' and a.stage_id ='' and a.station_id like '%'";
	//$product_data['remarks']=trim(odbc_result($res, 'remarks'));    	//need to seperated by ,
	//$product_data['actual_output']=trim(odbc_result($res, 'actual_output'));    //need to seperated by ,
	//$product_data['remark_comp']=trim(odbc_result($res, 'remark_comp'));  	//need to seperated by ,
	//echo trim(odbc_result($res, 'remarks'))	;
	//echo trim(odbc_result($res, 'actual_output')); 
	//echo trim(odbc_result($res, 'remark_comp')); exit;
	//$product_data['start_time']=trim(odbc_result($res, 'start_time'));
    //$end_time = trim(odbc_result($res, 'end_time'));
    //if($end_time == '1900-01-01 00:00:00.000'){
    //    $product_data['end_time']='';
    //}
    //else{
    //    $product_data['end_time']= $end_time;
    //}
	
	//$product_data['Barcode']=trim(odbc_result($res, 'Barcode'))." ( ".trim(odbc_result($res, 'Barcode'))." )";
	
	//$testres=trim(odbc_result($res, 'Barcode'));
	//echo $testres;//exit;
    
	//$product_data['action']= '';
	//$Barcode = trim(odbc_result($res, 'Barcode'));
    //if($Barcode == ''){
    //    $product_data['Barcode']='';
    //}
    //else{
    //    $product_data['Barcode']= $Barcode;

    //}
    //$w_type = odbc_result($res,'w_type');
    //if($w_type != 0){
    //    $w_query = "select w_type from tbl_manageworkbench where workbench_id='$work_id' and w_id='$w_type'";
    //    $result = odbc_exec($conn, $w_query);
    //    $product_data['w_type']= odbc_result($result,'w_type'); 
    //}
   //else{
    //$product_data['w_type']= '';  
   //}

	  
//	$product_data['action']='<a class="btn btn-default btn-circle" title="Modify" href="add_workbench.php?workbench_id='.trim(odbc_result($res, 'id')).'"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;<a class="btn btn-danger btn-circle"  data-placement="top" data-original-title=""  onclick=\'return confirm("Are you sure you want to delete this Workbench?");\' title="" href="delete_workbench.php?workbench_id='.trim(odbc_result($res, 'id')).'" ><i class="fa fa-trash"></i></a>'; 
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

 