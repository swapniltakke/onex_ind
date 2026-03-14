<?php
include('shared/CommonManager.php');
if($_POST['update_type'] == "add") {

		$sql="insert into tbl_manageworkbench(w_type,workbench_id) values (:w_type,:workbench_id)" ;
		$queryParams = [
            ":w_type" =>trim($_POST['w_type']),
            ":workbench_id" => trim($_POST['workbench_id'])
        ];
	    $query = DbManager::fetchPDOQueryData('spectra_db', $sql,$queryParams);
	 if (!$query) 
	 { 	
		
		$_SESSION['success'] = "<strong>Record</strong> is not added";
		
	 }
	 else
	 {
   //  echo "Record is added successfully!";
	 //exit;	
$_SESSION['success'] = "<strong>Congratulations</strong> Workbench Type information has been inserted successfully.";	 
	 }


}
else
{
  
		$sql="update tbl_manageworkbench set w_type =:w_type,workbench_id =:workbench_id where w_id=:w_id";
		//echo $query;exit;
    
		$query = DbManager::fetchPDOQuery('spectra_db', $sql, [
				":w_type" => trim($_POST['w_type']), 
				":workbench_id" =>trim($_POST['workbench_id']),
				":w_id" => trim($_POST['w_id'])]);
        if (!$query) 
        { 
		
		$_SESSION['success'] = "<strong>Record</strong> is not updated";
	 }
	 else
	 {
     //echo "Record updated successfully!";
		$_SESSION['success'] = "<strong>Congratulations</strong> Workbench Type information has been updated successfully.";
	 }


}
header("Location:Manageworkbentchlist.php");
exit(0);
?>
