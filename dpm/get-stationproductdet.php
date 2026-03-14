<?php
include('shared/CommonManager.php');
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

if ($_POST['scanqrcode']) {
   $scanqrcode  = $_POST['scanqrcode'];
   $mlfb_num = substr($scanqrcode, 8, 8);
   $production_order_no = substr($scanqrcode, -12);
   
   $sql = "select p.product_name,p.product_id,p.mlfb_num,p.subassembly_req from tbl_product p where mlfb_num like :mlfb_num";
   $resultset = DbManager::fetchPDOQueryData('spectra_db', $sql, [":mlfb_num" => "%$mlfb_num%"])["data"];
   $cnt = count($resultset);
   
   if($cnt > 0) {
      foreach ($resultset as $rows) {
         $ip_address = SharedManager::get_ip_address();
         $product_id = $rows['product_id'];
         $product_query = "select station_id,station_name,Machine_name from tbl_station where product_id like :product_id and Machine_name=:ip_address";
         $res = $rows;
      }
      
      if ($_POST['action'] != '' && $_POST['action'] == "stamping") {
         $product_query = "select station_id,station_name from tbl_station where station_name =:station_name";
         $s1 = DbManager::fetchPDOQueryData('spectra_db', $product_query, [":station_name" => "Stamping"])["data"];
         
         // Fetch PLC User transaction data for stamping action
         $plc_query = "SELECT actual_output FROM tbl_transactions 
                       WHERE barcode = :barcode 
                       AND (station_name = 'mV DROP ST-1' OR station_name = 'mV DROP ST-2') 
                       AND user_id = 'PLC User' 
                       ORDER BY tr_id DESC LIMIT 1";
         $plc_result = DbManager::fetchPDOQueryData('spectra_db', $plc_query, [":barcode" => "$scanqrcode"])["data"];
         
         if ($plc_result && !empty($plc_result[0]['actual_output'])) {
            $actual_output = $plc_result[0]['actual_output'];
            $res['stamping_data'] = extractStampingData($actual_output);
         } else {
            // If no PLC data found, create empty stamping data with all values as '-'
            $res['stamping_data'] = createEmptyStampingData();
         }
      } else {
         $s1 = DbManager::fetchPDOQueryData('spectra_db', $product_query, [":product_id" => "%$product_id%", ":ip_address" => "$ip_address"])["data"];
      }
      
      $res['station_id'] = $s1[0]['station_id'];
      $res['station_name'] = $s1[0]['station_name'];
      $res['machine_name'] = $s1[0]['Machine_name'];
      
      $mlfb_details = "select * from tbl_breaker_details where production_order_no =:production_order_no order by id desc limit 1";
      $result_mlfb_details = DbManager::fetchPDOQueryData('spectra_db', $mlfb_details, [":production_order_no" => "$production_order_no"])["data"];
      
      $previous_station_query = "select user_id,station_name from tbl_transactions where barcode =:barcode GROUP BY station_id order by tr_id DESC";
      $previous_stations = DbManager::fetchPDOQueryData('spectra_db', $previous_station_query, [":barcode" => "$scanqrcode"])["data"];
      
      if ($previous_stations) {
         $res['previous_stations'] = $previous_stations;
      }
      
      if ($result_mlfb_details) {
         $res['rating'] = $result_mlfb_details[0]['rating'];
         $res['width'] = $result_mlfb_details[0]['width'];
         $res['trolley_type'] = $result_mlfb_details[0]['trolley_type'];
         $res['trolley_refair'] = $result_mlfb_details[0]['trolley_refair'];
         $res['addon'] = $result_mlfb_details[0]['addon'];
         $res['vi_type'] = $result_mlfb_details[0]['vi_type'];
         $res['additional_note'] = $result_mlfb_details[0]['remark'];
      }
      
      $response['success'] = $res;
   } else {
      $response['notexist'] = "NOTEXIST";
   }	
} else {
   echo 0;
}

/**
 * Create empty stamping data with all values as '-'
 * Used when no PLC data is available in the database
 */
function createEmptyStampingData() {
   $data = array();
   
   // Map DB column names to display names
   $fieldMap = array(
      'ACT_A' => 'ACT_A',
      'mV DROP LIMIT' => 'mV DROP LIMIT',
      'R_mV' => 'R_phase mV',
      'Y_mV' => 'Y_phase mV',
      'B_mV' => 'B_phase mV',
      'B BARCODE' => 'R phase VI Sr. no.',
      'Y BARCODE' => 'Y phase VI Sr. no.',
      'R BARCODE' => 'B phase VI Sr. no.',
      'R Stroke' => 'R Stroke',
      'Y Stroke' => 'Y Stroke',
      'B Stroke' => 'B Stroke',
      'Roller Gap' => 'Cam roller gap (mm)',
      'Damping Gap' => 'Damping Gap(mm)',
      'Latch Gap' => 'Latch Gap(mm)'
   );
   
   // Define the fields in order (using DB column names as keys)
   $fieldsInOrder = array(
      'ACT_A',
      'mV DROP LIMIT',
      'R_mV',
      'Y_mV',
      'B_mV',
      'B BARCODE',
      'Y BARCODE',
      'R BARCODE',
      'R Stroke',
      'Y Stroke',
      'B Stroke',
      'Roller Gap',
      'Damping Gap',
      'Latch Gap'
   );
   
   // Create empty data with all values as '-'
   foreach ($fieldsInOrder as $dbColumn) {
      $displayName = $fieldMap[$dbColumn];
      
      $data[$dbColumn] = array(
         'displayName' => $displayName,
         'value' => '-'
      );
   }
   
   return $data;
}

/**
 * Extract stamping data from actual_output string
 * Maps DB column names to display names and returns in correct order
 */
function extractStampingData($actual_output) {
   $data = array();
   
   // Map DB column names to display names
   $fieldMap = array(
      'ACT_A' => 'ACT_A',
      'mV DROP LIMIT' => 'mV DROP LIMIT',
      'R_mV' => 'R_phase mV',
      'Y_mV' => 'Y_phase mV',
      'B_mV' => 'B_phase mV',
      'B BARCODE' => 'R phase VI Sr. no.',
      'Y BARCODE' => 'Y phase VI Sr. no.',
      'R BARCODE' => 'B phase VI Sr. no.',
      'R Stroke' => 'R Stroke',
      'Y Stroke' => 'Y Stroke',
      'B Stroke' => 'B Stroke',
      'Roller Gap' => 'Cam roller gap (mm)',
      'Damping Gap' => 'Damping Gap(mm)',
      'Latch Gap' => 'Latch Gap(mm)'
   );
   
   // Fields that need 3 decimal places rounding
   $decimalFields = array(
      'R_mV',
      'Y_mV',
      'B_mV'
   );
   
   // Define the fields to extract in order (using DB column names as keys)
   $fieldsInOrder = array(
      'ACT_A',
      'mV DROP LIMIT',
      'R_mV',
      'Y_mV',
      'B_mV',
      'B BARCODE',
      'Y BARCODE',
      'R BARCODE',
      'R Stroke',
      'Y Stroke',
      'B Stroke',
      'Roller Gap',
      'Damping Gap',
      'Latch Gap'
   );
   
   // Extract each field from the actual_output string using DB column names
   foreach ($fieldsInOrder as $dbColumn) {
      // Try to find the field using the display name pattern
      $displayName = $fieldMap[$dbColumn];
      $pattern = '/' . preg_quote($displayName, '/') . ':\s*([^,}]+)/';
      
      $extractedValue = '';
      
      if (preg_match($pattern, $actual_output, $matches)) {
         $extractedValue = trim($matches[1]);
      } else {
         // Try alternative pattern for the field
         $pattern2 = '/' . preg_quote($dbColumn, '/') . ':\s*([^,}]+)/';
         if (preg_match($pattern2, $actual_output, $matches)) {
            $extractedValue = trim($matches[1]);
         } else {
            $extractedValue = '-';
         }
      }
      
      // Apply rounding for decimal fields
      if (in_array($dbColumn, $decimalFields) && $extractedValue !== '-' && is_numeric($extractedValue)) {
         $extractedValue = round(floatval($extractedValue), 3);
      }
      
      $data[$dbColumn] = array(
         'displayName' => $displayName,
         'value' => $extractedValue
      );
   }
   
   return $data;
}

header("Content-type:application/json");
echo json_encode($response);
die();
?>