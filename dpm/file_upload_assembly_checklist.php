<?php
include('DatabaseConfig.php');

 //$file = $_FILES['file']['tmp_name'];
 $file =$_REQUEST['file_product'];
 echo "File Name:". $_REQUEST['file_product'];
 
 
 $handle = fopen($file, "r");
 $c = 0;

 
 while(($filesop = fgetcsv($handle, 1000, ",")) !== false)
           {
 $sr_no = $filesop[0];
 $current_station = $filesop[1];
 $activity=$filesop[2];
 $chk_box=$filesop[3];
 $text_req=$filesop[4];
 $product=$filesop[5];
 $new_station_no=$filesop[6];
 $sql = "insert into tbl_assembly_checklist(sr_no,current_station,activity,chk_box,text_req,product,new_station_no) values ('$sr_no','$current_station',$activity,'$chk_box''$text_req','$product','$new_station_no')";
 echo $sql; exit;
 $stmt = odbc_exec($conn,$sql);
 
 
 //mysqli_stmt_execute($stmt);

$c = $c + 1;
  }

   if($sql){
      echo "sucess";
    } 
else
{
   echo "Sorry! Unable to impo.";
 }

  
  


?>