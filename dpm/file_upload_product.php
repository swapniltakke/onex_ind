<?php
include 'DatabaseConfig.php';
 $file = $_FILES['file']['tmp_name'];
 $handle = fopen($file, "r");
 $c = 0;
 while(($filesop = fgetcsv($handle, 1000, ",")) !== false)
           {
 $product_name = $filesop[0];
 $description = $filesop[1];
 $subassembly_req=$filesop[2];
 $mlfb_num=$filesop[3];
 $sql = "insert into tbl_product_new(product_name,description,create_dtts,subassembly_req,mlfb_num) values ('$product_name','$description',GETDATE(),'$subassembly_req','$mlfb_num')";
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