<?php
include('shared/CommonManager.php');
$product_id = $_POST['product_id'];
$station_id = $_POST['station_id'];
//echo $product_id; exit;
//$sql = "SELECT c2.checklist_item,c2.id,c1.check_req,c1.text_req FROM tbl_checklistdetails as c1,tbl_checklist as c2,tbl_stage as c3 where c1.checklist_id=c2.id and c1.stage_id=c3.stage_id and c3.stage_id='7' ";
	
$sql = "SELECT c2.checklist_item,c2.id,c1.check_req,c1.text_req,c1.text_lable_names
        FROM tbl_checklistdetails c1
        inner join tbl_checklist c2 on c1.checklist_id=c2.id
        inner join tbl_stage c3 on c1.stage_id=c3.stage_id
        inner join tbl_product p on c1.product_id =p.product_id
        where  c3.stage_id=:stage_id and p.product_id=:product_id
        and concat(',', c1.station_id, ',') like :station_id";	
$query = DbManager::fetchPDOQueryData('spectra_db', $sql, [":stage_id" => "7", ":product_id" => "$product_id", ":station_id" => "%,$station_id,%"])["data"];
//echo $sql; exit;




 
$sr=1;
foreach ($query as $data) {

$stage_data['sr_no']= $sr;$sr++;
$stage_data['check_item']= $data['checklist_item'];

$stage_data['actual_opt']= $data['check_req'];
$text_req=$data['text_req'];
$text_lable_nmarr=explode(",",$data['text_lable_names']);

$text_fieldappend="<table>";
for($i=0;$i < $text_req;$i++){
$text_fieldappend .="<tr>";

$text_fieldappend .='<td>'.$text_lable_nmarr[$i].' :</td><td> <input type="text" style="form-control" name="remark[]" ></td>';
$text_fieldappend .='</tr>';
}
$text_fieldappend .='</table>';
$stage_data['remark']= $text_fieldappend;
$stage_info[] = $stage_data;
}

$results = array(
    "sEcho" => 1,
    "iTotalRecords" => count($stage_info),
    "iTotalDisplayRecords" => count($stage_info),
    "aaData" => $stage_info);

echo json_encode($results);

?>
