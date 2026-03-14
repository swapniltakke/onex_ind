<?php
      
              // $ip_address = gethostbyname('');  
			  // echo $ip_address;
			   //echo gethostbyaddr('132.186.183.193');
			   
			   //echo gethostname();
			   //echo gethostbyaddr($_SERVER['REMOTE_ADDR']);
				$path = "\\MD3ZE0XC\\SoPs";
				//$path = "C:\\xampp\\htdocs\\Spectra\\SoPs";
				exec("EXPLORER /E,$path");
		
				echo $path; exit;
 $res['suc']="SUCCESS";				
				$response['success']=$res;
header("Content-type:application/json");
echo json_encode($response);
//die();
      
?>


 