<?php
include('shared/CommonManager.php');
if($_POST['update_type'] == "add") {
    $station_name=trim($_POST['station_name']);
    
	$devices = $_POST['stages'];
   $stage_id = implode(',',$devices);
   $product_id = implode(',', $_POST['product_id']);
   $machine_name =$_POST['machine_name'];
   $check_station="select count(*) As cnt from tbl_station where product_id=:product_id";
   $station_rs= DbManager::fetchPDOQueryData('spectra_db', $check_station,[":product_id" => $product_id])["data"];
//    echo "<pre>";
//    print_r($station_rs);
//    echo "</pre>";
  
  /* $emp_cnt = odbc_result($station_rs,'cnt');
   if($emp_cnt > 0){
	   echo "<script type='text/javascript'>";
	   echo "alert('This product is allready done for some station')";
	   echo "window.location.href='Stationlist.php'";
	   echo "</script>";

   }
   else{*/
		$sql="insert into tbl_station (station_name,stage_id,product_id,Machine_name) values (:station_name,:stage_id,:product_id,:machine_name)" ;
		$queryParams = [
            ":station_name" =>trim($station_name),
            ":stage_id" => trim($stage_id),
            ":product_id" => trim($product_id),
            ":machine_name" =>trim($machine_name)
        ];
	  // echo $sql; exit;
		$query = DbManager::fetchPDOQueryData('spectra_db', $sql,$queryParams);
	 if (!$query) 
	 { 	
		
		$_SESSION['success'] = "<strong>Record</strong> is not added";
		
	 }
	 else
	 {
     echo "Record is added successfully!";
	 //exit;	
     $_SESSION['success'] = "<strong>Congratulations</strong> Station information has been inserted successfully.";	 
	 header("Location:Stationlist.php");
	 exit(0); 
    }
	//}


}
else
{
  
    $station_name=trim($_POST['station_name']);
    
    $devices = $_POST['stages'];
	$machine_name=$_POST['machine_name'];
	$product_id= implode(',', $_POST['product_id']);
   $stage_id = implode(',',$devices);
   $check_station="select count(*) As cnt from tbl_station where product_id=:product_id";
   $station_rs= DbManager::fetchPDOQueryData('spectra_db', $check_station,[":product_id" => $product_id])["data"];
   $emp_cnt = $station_rs['cnt'];
  /* if($emp_cnt > 0){
	   //echo "hello";
	   echo "<script type='text/javascript'>";
	   echo "alert('This product is allready done for some station');";
	   echo "window.location.href='Stationlist.php'";
	   echo "</script>";

   }
   else {*/
   //echo "checkbox ###".$stage_id; exit;
    $sql="update tbl_station set station_name =:station_name,stage_id =:stage_id,product_id=:product_id,Machine_name=:machine_name WHERE station_id =:station_id ";
	$query = DbManager::fetchPDOQuery('spectra_db', $sql, [
		":station_name" => trim($station_name), 
		":stage_id" =>trim($stage_id),
		":product_id" => trim($product_id),
		":machine_name" => trim($machine_name),
		":station_id" => trim($_POST['station_id'])]);
		//echo $query;exit;
        //$query = odbc_exec($conn, $sql);
        if (!$query) 
        { 
		
		$_SESSION['success'] = "<strong>Record</strong> is not updated";
	 }
	 else
	 {
     //echo "Record updated successfully!";
		$_SESSION['success'] = "<strong>Congratulations</strong> Station information has been updated successfully.";
	    header("Location:Stationlist.php");
         exit(0); 
	}
	//}

}

?>