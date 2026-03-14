<?php
include('shared/CommonManager.php');
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
$role_id = $_SESSION['role_id'];
$role_name = $_SESSION['role_name'];
$username = $_SESSION['username'];

$sql_transaction = "SELECT * FROM tbl_transactions WHERE stage_id=:stage_id AND status=:status AND tr_id=:tr_id GROUP BY barcode ORDER BY tr_id DESC";
$transaction_details = DbManager::fetchPDOQueryData('spectra_db', $sql_transaction, [":stage_id" => "8", ":status" => "0", ":tr_id" => $_REQUEST['id']])["data"][0];

$length = $_POST['length'];
$status = 1;
$stamping_remark = $_POST['stamping_remark'];
if (isset($_POST['remarks'])) {
    $remarks = implode(",", $_POST['remarks']);
    if (!empty($stamping_remark)) {
        $remarks = $stamping_remark . "," . $remarks;
    }
} else {
    $remarks = $stamping_remark;
}
if (isset($_POST['login_type']) && $_POST['login_type'] == "Stamping") {
  $actual_output = $_POST['actual_output'];
  if (is_array($transaction_details) && $transaction_details['actual_output'] != "") {
    $actual_output_manufacturing = $transaction_details['actual_output'];
    $actual_output_manufacturing_explode = explode("||", $actual_output_manufacturing);
    if (count($actual_output_manufacturing_explode) == 2) {
        $actual_output_manufacturing = $actual_output_manufacturing_explode[1];
        $new_actual_output = $actual_output."||".$actual_output_manufacturing;
    } else {
        $new_actual_output = $actual_output;
    }
  } else {
    $new_actual_output = $actual_output;
  }

  $actual_output_array = explode(",", $actual_output);
  if (count($actual_output_array) != $length) {
    $status = 0;
  } else {
    foreach ($actual_output_array as $value) {
        if ($value == '0' || $value == '""') {
            $status = 0;
            break;
        }
    }
  }

  $remark_compcnt = implode("','",$_POST['remarks_comp']);
  $remark_compcnt = "'$remark_compcnt'";

  if (is_array($transaction_details) && $transaction_details['remark_comp'] != "") {
    $remark_manufacturing_compcnt = $transaction_details['remark_comp'];
    $remark_manufacturing_compcnt_explode = explode("||", $remark_manufacturing_compcnt);
    if (count($remark_manufacturing_compcnt_explode) == 2) {
        $remark_manufacturing_compcnt = $remark_manufacturing_compcnt_explode[1];
        $new_remark_compcnt = $remark_compcnt."||".$remark_manufacturing_compcnt;
    } else {
      $new_remark_compcnt = $remark_compcnt;
    }
  } else {
    $new_remark_compcnt = $remark_compcnt;
  }

} else if (isset($_POST['login_type']) && $_POST['login_type'] == "Manufacturing") {
  $actual_output = $transaction_details['actual_output'];
  $actual_output_explode = explode("||", $actual_output);
  if (count($actual_output_explode) == 2) {
      $actual_output = $actual_output_explode[0];
  }

  $actual_output_manufacturing = $_POST['actual_output_manufacturing'];
  $new_actual_output = $actual_output."||".$actual_output_manufacturing;

  // $actual_output_array = explode(",", $actual_output);
  // if (count($actual_output_array) != $length) {
  //   $status = 0;
  // } else {
  //   foreach ($actual_output_array as $value) {
  //       if ($value == "0") {
  //           $status = 0;
  //           break;
  //       }
  //   }
  // }
  // $actual_output_manufacturing_array = explode(",", $actual_output_manufacturing);
  // if (count($actual_output_manufacturing_array) != $length) {
  //   $status = 0;
  // } else {
  //   foreach ($actual_output_manufacturing_array as $value) {
  //       if ($value == "0") {
  //           $status = 0;
  //           break;
  //       }
  //   }
  // }
  $status = 0;
  $remark_compcnt = $transaction_details['remark_comp'];
  $remark_compcnt_explode = explode("||", $remark_compcnt);
  if (count($remark_compcnt_explode) == 2) {
      $remark_compcnt = $remark_compcnt_explode[0];
  }

  $remark_manufacturing_compcnt = implode("','",$_POST['remarks_manufacturing_comp']);
  $remark_manufacturing_compcnt = "'$remark_manufacturing_compcnt'";
  
  $new_remark_compcnt = $remark_compcnt."||".$remark_manufacturing_compcnt;
} 

$product_id  = $_POST['product_id'];
$station_id = $_POST['station_id'];
$product_name = $_POST['product_name'];
$station_name = $_POST['station_name'];
$stage_id = $_POST['stage_id'];
$user_id = $_POST['user_id'];
$cust_name = $_POST['cust_name'];
$scanqrcode = $_POST['scanqrcode'];
$action = $_POST['action'];
$mlfb_num = substr($scanqrcode,8,8);

if ($action != '' && $action == "edit") {
  $id = $_POST['id'];
  $sql = "update tbl_transactions set actual_output =:actual_output, remarks =:remarks, remark_comp =:remark_compcnt, status =:status where tr_id=:tr_id";
  $query = DbManager::fetchPDOQuery('spectra_db', $sql, [":actual_output" => "$new_actual_output", ":remarks" => "$remarks", ":remark_compcnt" => "$new_remark_compcnt", ":status" => "$status", ":tr_id" => "$id"]);
  $tr_id = $_POST['id'];
} else {
  $sql = "insert into tbl_transactions(product_id,product_name,user_id,cust_name,station_id,station_name,stage_id,actual_output,remarks,remark_comp,barcode,status) 
      values (:product_id, :product_name, :user_id, :cust_name, :station_id, :station_name, :stage_id, :actual_output, :remarks, :remark_compcnt, :scanqrcode, :status)";
  $query = DbManager::fetchPDOQuery('spectra_db', $sql, [
    ":product_id" => "$product_id",
    ":product_name" => "$product_name",
    ":user_id" => "$user_id",
    ":cust_name" => "$cust_name",
    ":station_id" => "$station_id",
    ":station_name" => "$station_name",
    ":stage_id" => "$stage_id",
    ":actual_output" => "$new_actual_output",
    ":remarks" => "$remarks",
    ":remark_compcnt" => "$new_remark_compcnt",
    ":scanqrcode" => "$scanqrcode",
    ":status" => $status
  ]);

  $sql_insert_query = "SELECT tr_id FROM tbl_transactions WHERE stage_id=:stage_id AND station_id=:station_id AND product_id=:product_id AND barcode=:barcode GROUP BY barcode ORDER BY tr_id DESC";
  $insert_data = DbManager::fetchPDOQueryData('spectra_db', $sql_insert_query, [":stage_id" => "8", ":station_id" => "$station_id", ":product_id" => "$product_id", ":barcode" => "$scanqrcode"])["data"][0];
  $tr_id = $insert_data['tr_id'];
}

$sql_user = "insert into tbl_transactions_user_details(tr_id,role_id,role_name,username,product_id,product_name,user_id,cust_name,station_id,station_name,stage_id,actual_output,remarks,remark_comp,barcode,status) 
        values (:tr_id, :role_id, :role_name, :username, :product_id, :product_name, :user_id, :cust_name, :station_id, :station_name, :stage_id, :actual_output, :remarks, :remark_compcnt, :scanqrcode, :status)";
$query_user = DbManager::fetchPDOQuery('spectra_db', $sql_user, [
  ":tr_id" => "$tr_id",
  ":role_id" => "$role_id",
  ":role_name" => "$role_name",
  ":username" => "$username",
  ":product_id" => "$product_id",
  ":product_name" => "$product_name",
  ":user_id" => "$user_id",
  ":cust_name" => "$cust_name",
  ":station_id" => "$station_id",
  ":station_name" => "$station_name",
  ":stage_id" => "$stage_id",
  ":actual_output" => "$new_actual_output",
  ":remarks" => "$remarks",
  ":remark_compcnt" => "$new_remark_compcnt",
  ":scanqrcode" => "$scanqrcode",
  ":status" => $status
]);

if (!$query) {
	$res['error'] = "ERROR";
} else{
	$sql2 = "update tbl_DailyUpload set Progress_Status =:ProgressStatus, CompDate =:CompDate where barcode=:scanqrcode and progress_status !=:progress_status";	
	$resuploadid = DbManager::fetchPDOQuery('spectra_db', $sql2, [":ProgressStatus" => "5", ":CompDate" => date('Y-m-d h:i:s'), ":scanqrcode" => "$scanqrcode", ":progress_status" => "5"]);
  $res['saved'] = "SAVED";
  $res['complete'] = "COMPLETE";
}

$response['success'] = $res;
header("Content-type:application/json");
echo json_encode($response);
die();
?>