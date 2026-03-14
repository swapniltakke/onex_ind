<?php
session_start();
include_once("DatabaseConfig.php");
$station_id = $_POST['station_id'];
$product_id = $_POST['product_id'];
$mlfb_num   = $_POST['mlfb_num'];
$subassem_req = $_POST['subassem_req'];
$product_name = $_POST['product_name'];
$station_name = $_POST['station_name'];
$machine_name = $_POST['machine_name'];
$scanqrcode = $_POST['scanqrcode'];


$ip_address = gethostbyname('');
//$ip_address = $_SERVER['REMOTE_ADDR'];
echo $ip_address."Machine " .$machine_name;
if($machine_name != $ip_address){
    if($subassem_req == 'Yes'){
        $sql = " select count(*) as cnt from tbl_transactions where product_id = '".$product_id."' and stage_id in (1,2,3)";
        //echo $sql;//exit;
        $resultset = odbc_exec($conn, $sql);
        $cnt = odbc_result($resultset,'cnt');
        if($cnt < 1){
        $res['cnt']=$cnt;
        $res['res']='NOFOUND';
        $response['success']=$res;
        }
        else{

            
        }
    }
    



}
else if($machine_name == $ip_address){

    $_SESSION['station_name'] = $station_name;
$_SESSION['product_name'] = $product_name;
$_SESSION['product_id'] = $product_id;
$_SESSION['station_id'] = $station_id;
$_SESSION['mlfb_num'] = $mlfb_num;
$_SESSION['subassem_req'] = $subassem_req;
$_SESSION['machine_name'] = $machine_name;
$_SESSION['scanqrcode'] = $scanqrcode;
    if($subassem_req == 'Yes')
    {
        $scanqrcode1 = substr($scanqrcode,10,8);
        $daily_wq="select count(*) as totald from tbl_DailyUpload where substring(Product,0,9)='$scanqrcode1' and progress_status != 3 ";
        //echo $daily_wq; exit;
        $result = odbc_exec($conn, $daily_wq);
        $cnt_p = odbc_result($result,'totald');
        if($cnt_p < 1){
            $res['res']='NOAVAILABLE';
        }
       else
		{
			
			
        $check_step="select count('s.*') as count_rs from tbl_station s
        where concat(',', s.product_id, ',') like '%,$product_id,%' 
        and  s.stage_id in ('1,2,3') and s.station_id='$station_id' and s.Machine_name ='$machine_name'";
       // echo $check_step; exit;
        $rs_set = odbc_exec($conn, $check_step);
        $cnt_rs = odbc_result($rs_set,'count_rs');

        if($cnt_rs == 1)
		{
						
            $sql = " select count(*) as cnt from tbl_transactions where product_id = '".$product_id."' and barcode='".$scanqrcode."'  and status =1 and station_id = '".$station_id."'  ";
                //echo $sql;exit;
               $resultset = odbc_exec($conn, $sql);
               $count_rs = odbc_result($resultset,'cnt');
               if($count_rs < 1){
				//To update status as Subassembly   
				$sql2="update tbl_DailyUpload set Progress_Status = 1, barcode = '".$scanqrcode."' where SUBSTRING(Product,0,9) = '".$scanqrcode1."' and progress_status != 4" ;	
				//echo $sql2; exit;
				$resuploadid = odbc_exec($conn, $sql2);
			
                $res['cnt']=$count_rs;
                $res['res']='SUBPRECHECK';
        
               }
               else if($count_rs > 0 && $count_rs < 2)
               {
                $res['cnt']=$count_rs;
                $res['res']='SUBASSEMBLY';
               }
               else if($count_rs > 1 && $count_rs < 3){
                $res['cnt']=$count_rs;
                $res['res']='SUBLOCATION';
        
        
               }
			   else if($count_rs == 3){
				  $res['cnt']=$count_rs;
                $res['res']='SUBASSDONE'; 
				   
			   }

        }
        else 
		{

            $check_subass="select count('s.*') as count_ass from tbl_station s
            inner join tbl_transactions t on t.station_id=s.station_id
            where concat(',', s.product_id, ',') like '%,$product_id,%' and t.barcode='$scanqrcode'
            and  s.stage_id in ('1,2,3')";
            //echo $check_subass; exit;
            $rs_sub = odbc_exec($conn, $check_subass);
            $cnt = odbc_result($rs_sub,'count_ass');
            if($cnt < 1){
                     $res['cnt'] = $cnt;
                     $res['res'] = "REQASSEM";
            }
            else if($cnt > 0 && $cnt < 2 || $cnt > 1 && $cnt < 3){
                $res['cnt'] = $cnt;
                $res['res'] = "PARTIALDONE";
            }
            else
			{
    
                $sql = " select count(*) as cnt from tbl_transactions where product_id = '".$product_id."' and barcode='".$scanqrcode."'";
                //echo $sql;exit;
               $resultset = odbc_exec($conn, $sql);
               $count_rs = odbc_result($resultset,'cnt');
               if($count_rs < 1){
				   
				$sql2="update tbl_DailyUpload set Progress_Status = 1, barcode = '".$scanqrcode."'  where SUBSTRING(Product,0,9) = '".$scanqrcode1."' and progress_status != 4" ;	
				echo $sql2; exit;
				$resuploadid = odbc_exec($conn, $sql2);
			
                $res['cnt']=$count_rs;
                $res['res']='SUBPRECHECK';
        
               }
               else if($count_rs > 0 && $count_rs < 2)
               {
                $res['cnt']=$count_rs;
                $res['res']='SUBASSEMBLY';
               }
               else if($count_rs > 1 && $count_rs < 3){
                $res['cnt']=$count_rs;
                $res['res']='SUBLOCATION';
        
        
               } //&& $count_rs < 4
               else if($count_rs > 2 ){
                $check_locstage="select count(*) as total from tbl_station where concat(',', product_id, ',') like '%,$product_id,%' and concat(',', stage_id, ',') like '%,3,%'";
                $result_loc = odbc_exec($conn, $check_locstage);
                $cnt_loc = odbc_result($result_loc,'total');
                $trans_check_loc ="select count(*) as cnttotal from tbl_transactions where product_id ='$product_id' and stage_id='3' and  barcode='".$scanqrcode."' ";
                $rs_loc = odbc_exec($conn, $trans_check_loc);
                $cnt_total_loc = odbc_result($rs_loc,'cnttotal');
				//echo $cnt_loc ."  " .$cnt_total_loc."   ".$count_rs; exit;				
                if($cnt_loc == $cnt_total_loc){
					
					$check_locstage2="select max(stage_id) as total from tbl_transactions where product_id ='$product_id' and  barcode='".$scanqrcode."' and  station_id = '".$station_id."' ";
					$result_loc2 = odbc_exec($conn, $check_locstage2);
					$cnt_loc2 = odbc_result($result_loc2,'total');
					
					//$check_stationstage="select count(*) as total from tbl_station where concat(',', product_id, ',') like '%,$product_id,%'  and station_id = '".$station_id."' and stage_id like  '%4%' " ;
					$check_stationstage="select stage_id as total from tbl_station where concat(',', product_id, ',') like '%,$product_id,%'  and station_id = '".$station_id."' " ;
					$result_stationstage = odbc_exec($conn, $check_stationstage);
					$cnt_Stationstage = odbc_result($result_stationstage,'total');
						
					//echo $check_locstage2." MAchine " .$cnt_Stationstage; exit;
					
					if($cnt_loc2 == 4 && $cnt_Stationstage == '4,5')
					{
						$check_locstage1="select count(*) as total from tbl_station where concat(',', product_id, ',') like '%,$product_id,%' and concat(',', stage_id, ',') like '%,4,%' and station_id = '".$station_id."'";
						$result_loc1 = odbc_exec($conn, $check_locstage1);
						$cnt_loc1 = odbc_result($result_loc1,'total');
						$trans_check_loc1 ="select count(*) as cnttotal from tbl_transactions where product_id ='$product_id' and stage_id='4' and  barcode='".$scanqrcode."' and  station_id = '".$station_id."' ";
						$rs_loc1 = odbc_exec($conn, $trans_check_loc1);
						$cnt_total_loc1 = odbc_result($rs_loc1,'cnttotal');
						//echo $cnt_loc1 ."  " .$cnt_total_loc1."   ".$count_rs; exit;
						if($cnt_loc1 == $cnt_total_loc1)
						{
						$res['cnt']=4;
						$res['res']='ASSEMBLY_STAGE2';					
						}
					}
					if($cnt_loc2 == null && $cnt_Stationstage == '4,5')
					{
						//echo $cnt_loc2." MAchine " .$cnt_Stationstage; exit;
                    $res['cnt']=$cnt_loc;
                    $res['cnt_tot'] = $cnt_total_loc;
                    $res['res'] = "ASSEMBLY";
					}
					//echo $cnt_loc2 ." space " .$cnt_Stationstage." space  ".$count_rs; exit;
					if($cnt_loc2 == 5 || $cnt_Stationstage == '6,7') //Testing
					{
						//$check_locstage3="select count(stage_id) from tbl_checklistdetails where station_id like ('%".$station_id."%') and stage_id = 6 " ; 
						$check_locstage3="select count(*) as total from tbl_station where concat(',', product_id, ',') like '%,$product_id,%' and stage_id like '%6%' and station_id = '".$station_id."' "; 
						//echo $check_locstage3; exit;
						$result_loc3 = odbc_exec($conn, $check_locstage3);
						$cnt_loc3 = odbc_result($result_loc3,'total');
						//echo $cnt_loc3 ." space " .$check_locstage3; exit;
						
						//To check Assembly has done already on this station---------------
						$check_Assstage="select count(*) as total from tbl_station where concat(',', product_id, ',') like '%,$product_id,%' and concat(',', stage_id, ',') like '%,4,%'";
						$result_Ass = odbc_exec($conn, $check_Assstage);
						$cnt_Ass = odbc_result($result_Ass,'total');
						$trans_check_Ass ="select count(*) as cnttotal from tbl_transactions where product_id ='$product_id' and stage_id='4' and  barcode='".$scanqrcode."' ";
						$result_transAss = odbc_exec($conn, $trans_check_Ass);
						$cnt_total_Ass = odbc_result($result_transAss,'cnttotal');								
						//echo $check_Assstage ." space " .$trans_check_Ass; exit;
						
						/*if($cnt_loc1 == $cnt_total_loc1)
						{
							$res['cnt']=0;
							$res['res']='NA_Testing';					
						}
							
						//---------------------------------------------
						
						
						else */ if($cnt_loc3 == 0)
						{
							$res['cnt']=0;
							$res['res']='No_TestingStage';					
						}
						else
						{
							$check_locstage1="select count(*) as total from tbl_station where concat(',', product_id, ',') like '%,$product_id,%' and concat(',', stage_id, ',') like '%,5,%'";
							$result_loc1 = odbc_exec($conn, $check_locstage1);
							$cnt_loc1 = odbc_result($result_loc1,'total');
							$trans_check_loc1 ="select count(*) as cnttotal from tbl_transactions where product_id ='$product_id' and stage_id='5' and  barcode='".$scanqrcode."' ";
							$rs_loc1 = odbc_exec($conn, $trans_check_loc1);
							$cnt_total_loc1 = odbc_result($rs_loc1,'cnttotal');
							//echo $cnt_loc1 ."  " .$cnt_total_loc1."   ".$count_rs; exit;
							if($cnt_loc1 == $cnt_total_loc1)
							{
								$res['cnt']=0;
								$res['res']='PRECHECKTEST';					
							}
							else
							{
								$res['res'] = 'NA_Testing';
							}
						}
					}
                    
                }
                else if($cnt_total_loc < $cnt_loc){
        
                    $res['cnt']=$cnt_loc;
                    $res['cnt_tot'] = $cnt_total_loc;
                    $res['res'] = "NA_ASSEMPREC";
                   }
               }
               else if($count_rs > 3 && $count_rs < 5){
                $res['cnt']=$count_rs;
                $res['res']='ASSEMBLY_STAGE2';
    
    
               }
    
    
    
            }
           
            
    
    
    
        }
        }
        
   
       
      



       
        $response['success']=$res;
    }
    else{
        $scanqrcode1 = substr($scanqrcode,10,8);
        $daily_wq="select count(*) as totald from tbl_DailyUpload where substring(Product,0,9)='$scanqrcode1' and progress_status != 4 ";
        echo $daily_wq; exit;
        $result = odbc_exec($conn, $daily_wq);
        $cnt_p = odbc_result($result,'totald');
		//echo $cnt_p."  ".$daily_wq; exit;
        if($cnt_p < 1)
		{
            $res['res']='NOAVAILABLE';
        }
        else
		{
     
            $check_step="select count('s.*') as count_rs from tbl_station s
            where concat(',', s.product_id, ',') like '%,$product_id,%' 
            and  s.stage_id in ('4,5') and s.station_id='$station_id' and s.Machine_name ='$machine_name'";
            //echo $check_step; exit;
            $rs_set = odbc_exec($conn, $check_step);
            $cnt_rs = odbc_result($rs_set,'count_rs');

			if($cnt_rs == 1)
			{
				$sql = " select count(*) as cnt from tbl_transactions where product_id = '".$product_id."' and barcode='".$scanqrcode."'  and status =1 and station_id = '".$station_id."'  ";
                //echo $sql;exit;
				$resultset = odbc_exec($conn, $sql);
				$count_rs = odbc_result($resultset,'cnt');
				if($count_rs < 1)
				{
					$check_Uploadid="select uploadid as count_rs from tbl_DailyUpload where SUBSTRING(Product,0,9) = '".$scanqrcode1."' and progress_status != 4" ;	
					//echo $check_Uploadid; exit;
					$rs_uploadid = odbc_exec($conn, $check_Uploadid);
					$cnt_uploadid = odbc_result($rs_uploadid,'count_rs');
					
					$sql2="update tbl_DailyUpload set Progress_Status = 2, barcode = '".$scanqrcode."' where uploadid = '".$cnt_uploadid."'" ;	
					//echo $sql2; exit;
					$resuploadid = odbc_exec($conn, $sql2);
					$res['cnt']=$count_rs;
                    //$res['cnt_tot'] = $cnt_total_loc;
                    $res['res'] = "ASSEMBLYWS";         	
				}
				else if($count_rs > 0 && $count_rs < 2)
				{
					$res['cnt']=$count_rs;
					$res['res']='ASSEMBLY_STAGE2WS';
				}
				else 
				{
					/* $check_locstage1="select count(*) as total from tbl_station where concat(',', product_id, ',') like '%,$product_id,%' and concat(',', stage_id, ',') like '%,5,%'";
					$result_loc1 = odbc_exec($conn, $check_locstage1);
					$cnt_loc1 = odbc_result($result_loc1,'total');
					$trans_check_loc1 ="select count(*) as cnttotal from tbl_transactions where product_id ='$product_id' and stage_id='5' and  barcode='".$scanqrcode."' ";
					$rs_loc1 = odbc_exec($conn, $trans_check_loc1);
					$cnt_total_loc1 = odbc_result($rs_loc1,'cnttotal');
					//echo $cnt_loc1 ."  " .$cnt_total_loc1."   ".$count_rs; exit;
					if($cnt_loc1 == $cnt_total_loc1)
					{
						//$res['cnt']=0;
						//$res['res']='PRECHECKTEST';					
					} */
				}
               
			}	
			$check_step="select count('s.*') as count_rs from tbl_station s where concat(',', s.product_id, ',') like '".%,$product_id,%."' 
            and  s.stage_id in ('6,7') and s.station_id='".$station_id."' and s.Machine_name ='".$machine_name"'";
            echo $check_step; exit;
            $rs_set = odbc_exec($conn, $check_step);
            $cnt_rs = odbc_result($rs_set,'count_rs');

			if($cnt_rs == 1)
			{
				$sql = " select count(*) as cnt from tbl_transactions where product_id = '".$product_id."' and barcode='".$scanqrcode."'  and stage_id='5' and station_id = '".$station_id."'  ";
                echo $sql;exit;
				$resultset = odbc_exec($conn, $sql);
				$count_rs = odbc_result($resultset,'cnt');
				if($count_rs < 1)
				{
					$res['cnt']=0;
					res['res']='PRECHECKTESTWS';
				}				
			}
						
			else if ($cnt_rs == 0)
			{
				$res['cnt']=0;
				$res['res']='No_TestingStage';					
			}
        }

        $response['success']=$res;
    }


}

/*$sql = " select count(*) as cnt from tbl_transactions where station_id = '".$station_id."' and product_id = '".$product_id."'";
//echo $sql;exit;
$resultset = odbc_exec($conn, $sql);
$cnt = odbc_result($resultset,'cnt');


if($cnt < 1){
    $res['cnt']=$cnt;
$response['success']=$res;
}
else{
    $sql1 = " select stage_id from tbl_transactions where station_id = '".$station_id."' and product_id = '".$product_id."' group by stage_id order by stage_id DESC";
    $resultset1 = odbc_exec($conn, $sql1);
    $stage_id = odbc_result($resultset1,'stage_id');
    $res['cnt']=$cnt;
    $res['stage_id']=$stage_id;
    $response['success']=$res;

}*/


 header("Content-type:application/json");
echo json_encode($response);
die();


?>