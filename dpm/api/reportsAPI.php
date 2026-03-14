<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/api/MToolManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";

$type = $_POST["action"] ?? $_GET["action"];
switch ($type) {
    case "allList":
        listReports(-1);
    break;
    default:
    break;
}
exit;

function listReports($openCloseState)
{
    global $replaceSubProductType;

    $start_date = $_GET['start_date'] ?? null;
    $finish_date = $_GET['finish_date'] ?? null;
    $firstOfMonth = date('Y-m-01 00:00:00');
    $firstOfMonth = date('Y-m-d H:i:s', strtotime($firstOfMonth . ' -1 second'));

    $date_query = $date_query1 = "";
    if ($start_date != null && $finish_date != null) {
        $startdateObj = DateTime::createFromFormat('d-m-Y', $start_date);
        $formattedStartDate = $startdateObj->format('Y-m-d 00:00:00');
        $enddateObj = DateTime::createFromFormat('d-m-Y', $finish_date);
        $formattedEndDate = $enddateObj->format('Y-m-d 23:59:59');

        $date_query = " AND up_date BETWEEN :formattedStartDate AND :formattedEndDate ORDER BY tr_id DESC";
        $pdo_params1 = [":formattedStartDate" => $formattedStartDate, ":formattedEndDate" => $formattedEndDate];
        $date_query1 = " AND up_date BETWEEN :formattedStartDate AND :formattedEndDate ORDER BY sort_date DESC";
        $pdo_params2 = [":formattedStartDate" => $formattedStartDate, ":formattedEndDate" => $formattedEndDate];
    } else {
        $date_query = " AND up_date >=:first_of_month ORDER BY tr_id DESC";
        $pdo_params1 = [":first_of_month" => $firstOfMonth];
        $date_query1 = " AND up_date >=:first_of_month ORDER BY sort_date DESC";
        $pdo_params2 = [":first_of_month" => $firstOfMonth];
    }

    $pdo_params = array_merge($pdo_params1, $pdo_params2, [":status" => "1"]);
    $query = "SELECT tr_id, barcode, stage_id, station_name, user_id, product_name, DATE(MIN(up_date)) AS start_datetime, DATE(MAX(up_date)) AS end_datetime, up_date AS sort_date
              FROM (
                    SELECT tr_id, barcode, stage_id, station_name, user_id, product_name, up_date,
                    ROW_NUMBER() OVER (PARTITION BY barcode, station_name, user_id, product_name ORDER BY stage_id DESC) AS rn
                    FROM tbl_transactions
                    WHERE status =:status $date_query
                ) AS subquery
              GROUP BY barcode, station_name, user_id, product_name
              UNION ALL
                SELECT tr_id, barcode, NULL AS stage_id, station_name, NULL AS user_id, product_name, DATE(up_date) AS start_datetime, DATE(up_date) AS end_datetime, up_date AS sort_date
                FROM tbl_warehousebarcode
                WHERE STATUS =:status $date_query1";
              
    $res = DbManager::fetchPDOQueryData('spectra_db', $query, $pdo_params)["data"];
    $cnt = count($res);
    $product_info = array();
    if ($cnt > 0) {
         $sr = 1;
         foreach ($res as $data) {    
              $product_data['sr_no'] = $sr;
              $sr++;
              $product_data['tr_id'] = trim($data['tr_id']);
              $product_data['workstation'] = trim($data['station_name']);
              $product_data['workstationUniqueNumber'] = preg_replace('/[^a-zA-Z0-9 _-]/', '', $product_data['workstation']);
              $product_data['productName'] = trim($data['product_name']);
              $product_data['user'] = trim($data['user_id']);
              $barcode = trim($data['barcode']);
              $product_data['barcode'] = $barcode;
              $product_data['serialno'] = substr($barcode, 0, 8);
              $product_data['mlfbNo'] = substr($barcode, 8, -29);
              $product_data['mlfbNoUniqueNumber'] = preg_replace('/[^a-zA-Z0-9 _-]/', '', $product_data['mlfbNo']);
              $product_data['salesOrderNo'] = substr($barcode, -29, 10);
              $product_data['itemNo'] = substr($barcode, -18, 6);
              $product_data['productionOrderNo'] = substr($barcode, -12);
              $product_data['stageId'] = trim($data['stage_id']);
              $product_data['startDateTime'] = $data['start_datetime'];
              $product_data['endDateTime'] = $data['end_datetime'];
              $product_info["data"][] = $product_data;
         }
    } else {
        $product_info["data"] = array();
    }
    echo json_encode($product_info, JSON_THROW_ON_ERROR);
    exit;
}