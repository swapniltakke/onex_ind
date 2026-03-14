<?php
// Load the database configuration file
include 'DatabaseConfig.php';

if(isset($_POST['importSubmit'])){
    
    // Allowed mime types
    $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
    
    // Validate whether selected file is a CSV file
    if(!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $csvMimes)){
        
        // If the file is uploaded
        if(is_uploaded_file($_FILES['file']['tmp_name'])){
            
            // Open uploaded CSV file with read-only mode
            $csvFile = fopen($_FILES['file']['tmp_name'], 'r');
            
            // Skip the first line
            fgetcsv($csvFile);
            
            // Parse data from CSV file line by line
            while(($line = fgetcsv($csvFile)) !== FALSE){
                // Get row data
                $Date   = $line[0];
				$ProductionOrder = $line[1];
                $SAPNo = $line[2];
                $Client = $line[3];
				$Product   = $line[4];
                $Rating  = $line[5];
                $Qty  = $line[6];
                $WB = $line[7];
                $OIS = $line[8];
                $ODC_MSN = $line[9];
                $FAT = $line[10];
				$FirstShiftDrive   = $line[11];
                $FirstShiftPole  = $line[12];
                $SeconfSHiftDrive  = $line[13];
                $SeconfSHiftPole = $line[14];
                $Issued = $line[15];
                $Balance = $line[16];
                $StartDate = $line[17];
				$EndDate   = $line[18];
                $PickingStatus  = $line[19];
                $OCC  = $line[20];
                $DeliveryReamarks = $line[21];
                				
                // Check whether member already exists in the database with the same email
                //$prevQuery = "SELECT uploadid FROM tbl_DailyUpload WHERE checklist_item = '".$line[0]."'";
                //$sql = odbc_exec($conn, $prevQuery);
                //$num=odbc_num_rows($sql);
                //if($num > 0){
                    // Update member data in the database
                   // $db->query();
                //   $update_query="UPDATE supervisor_imort_checklist SET remarks = '".$remarks."', product = '".$product."', checkbox_req = '".$checkbox_req."', textbox_req = '".$textbox_req."', check_type = '".$check_type."', station_no = '".$station."' WHERE checklist_item = '".$checklist_item."'"; 
                  // odbc_exec($conn, $update_query);
                //}else{
                    // Insert member data in the database
                    $insert_query="INSERT INTO tbl_DailyUpload( uploaddate, ProductinOrder , SAPNo , Client , Product , rating , Qty , WB , ois , ODS_MSN , FAT , FirstShiftDrive , FirstShiftPole , SecondshiftDrive , SecondshiftPole , Issued , Bal , StartDate , EndDate , PickingStatus , OCC , DeliveryRemarks  ) VALUES ('".$Date ."', '".$ProductionOrder."', '".$SAPNo."', '".$Client."', '".$Product."', '".$Rating."','".$Qty."' , '".$WB."', '".$OIS."', '".$ODC_MSN."', '".$FAT."','".$FirstShiftDrive."', '".$FirstShiftPole."', '".$SecondshiftDrive."', '".$SecondshiftPole."', '".$Issued."','".$Bal."', '".$StartDate."', '".$EndDate."', '".$PickingStatus."','".$OCC."', '".$DeliveryReamarks."')";
                   // echo $insert_query; exit;
                   odbc_exec($conn, $insert_query);
                //}
            }
            
            // Close opened CSV file
            fclose($csvFile);
            
            $qstring = '?status=succ';
        }else{
            $qstring = '?status=err';
        }
    }else{
        $qstring = '?status=invalid_file';
    }
}

// Redirect to the listing page
header("Location: Uploaddailyworkshit.php".$qstring);
?>