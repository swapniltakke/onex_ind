<?php
include('shared/CommonManager.php');
$prod_order_id = $_POST['prod_order_id'];
$upload_date = $_POST['upload_date'];
$sql = "";
$sql = "SELECT * FROM tbl_DailyUpload WHERE ProductinOrder =:prod_order_id AND Barcode IS NOT NULL";
$resp = DbManager::fetchPDOQueryData('spectra_db', $sql, [":prod_order_id" => "$prod_order_id"])["data"];
$cntp = count($resp);
$product_info = array();
if($cntp > 0) {
	$productname = $resp[0]['product']; //left(product,8) as prodname
	$SAPno = $resp[0]['SAPNo'];
	$Clientno = $resp[0]['Client'];
	$Productfull = $resp[0]['Product'];
	$Rating = $resp[0]['rating'];
	$Barcode = $resp[0]['Barcode'];
}
$sql1 = "SELECT * FROM  tbl_transactions  where Barcode =:Barcode order by tr_id";
$res = DbManager::fetchPDOQueryData('spectra_db', $sql1, [":Barcode" => "$Barcode"])["data"];
$cnt = count($res);
if ($cnt > 0) {
	$sr = 1;
	$sr1 = 0;
	$sr2 = 0;
	foreach ($res as $data) {
		$product_id = trim($data['product_id']);	 
		$station_id = trim($data['station_id']);
		$stage_id = trim($data['stage_id']);
		$sr1 = 0;
		$sr2 = 0;
		
		$sql2 ="SELECT a.check_id, a.stage_id, a.checklist_id, 
		b.checklist_item, a.text_req, a.check_req, a.product_id, a.station_id, a.text_lable_names 
		FROM tbl_checklist b 
		INNER JOIN tbl_checklistdetails a ON b.id = a.checklist_id 
		where a.product_id =:product_id
		and a.stage_id =:stage_id
		and a.station_id =:station_id";
		$res2 = DbManager::fetchPDOQueryData('spectra_db', $sql2, [":product_id" => "$product_id", ":stage_id" => "$stage_id", ":station_id" => "$station_id"])["data"];

		$textlabel1 = $data['actual_output'];
		$textlabel = explode(",",$textlabel1);
		$textRem = $data['remarks'];
		$textRem1 = explode(",",$textRem);
		
		foreach ($res2 as $info) {
			$product_data['sr_no'] = $sr;
			$sr++;
			$product_data['station_name'] = $data['station_name'];
			$product_data['activity'] = $info['checklist_item'];
			$product_data['check'] = $textlabel[$sr1];
			if ($info['text_req'] == 1) {
				$product_data['remark'] = $textRem1[$sr2];
				$sr2++;
			} else {
				$product_data['remark'] = '';
			}
			$product_info[] = $product_data;
			$sr1++;
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