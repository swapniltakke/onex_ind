<?php
session_start();
include('shared/CommonManager.php');
$station_id = $_POST['station_id'];
$product_id = $_POST['product_id'];
$mlfb_num = $_POST['mlfb_num'];
$subassem_req = $_POST['subassem_req'];
$product_name = $_POST['product_name'];
$station_name = $_POST['station_name'];
$machine_name = $_POST['machine_name'];
$scanqrcode = $_POST['scanqrcode'];
$remark = $_POST['remark'];
// $action = $_POST['action'];
$action = $_SESSION['role_name'];

$ip_address = SharedManager::get_ip_address();
if ($machine_name != $ip_address) {
	if ($action == 'Stamping' || $action == 'Manufacturing') {
		$_SESSION['station_name'] = $station_name;
		$_SESSION['product_name'] = $product_name;
		$_SESSION['product_id'] = $product_id;
		$_SESSION['station_id'] = $station_id;
		$_SESSION['mlfb_num'] = $mlfb_num;
		$_SESSION['subassem_req'] = $subassem_req;
		$_SESSION['machine_name'] = $ip_address;
		$_SESSION['scanqrcode'] = $scanqrcode;
		$_SESSION['remark'] = $remark;
		if ($_POST['tr_id'] != '') {
			$res['cnt'] = 0;
			$res['type'] = "edit";
			$res['res'] = "STAMPCHECK";
		} else {
			$check_locstage2 = "select tr_id,stage_id,status from tbl_transactions where product_id =:product_id and barcode=:scanqrcode and station_id =:station_id";
			$result_loc2 = DbManager::fetchPDOQuery('spectra_db', $check_locstage2, [":product_id" => "$product_id", ":scanqrcode" => "$scanqrcode", ":station_id" => "$station_id"])["data"];
			if ($action == 'Manufacturing') {
				if (isset($result_loc2[0]['stage_id']) && $result_loc2[0]['stage_id'] == '8') {
					if (isset($result_loc2[0]['status']) && $result_loc2[0]['status'] == '1') {
						$res['cnt'] = 0;
						$res['type'] = "edit";
						$res['res'] = 'ALREADYSCANNED';
					} else {
						$res['cnt'] = 0;
						$res['type'] = "edit";
						$res['tr_id'] = $result_loc2[0]['tr_id'];
						$res['res'] = 'MANUFACTURERCHECK';
					}
				} else {
					$res['cnt'] = 0;
					$res['type'] = "add";
					$res['res'] = "NOTAUTHORIZED";
				}
			} else {
				if (isset($result_loc2[0]['stage_id']) && $result_loc2[0]['stage_id'] == '8') {
					if (isset($result_loc2[0]['status']) && $result_loc2[0]['status'] == '1') {
						$res['cnt'] = 0;
						$res['type'] = "edit";
						$res['res'] = 'ALREADYSCANNED';
					} else {
						$res['cnt'] = 0;
						$res['type'] = "edit";
						$res['tr_id'] = $result_loc2[0]['tr_id'];
						$res['res'] = 'STAMPINGCHECK';
					}
				} else {
					$res['cnt'] = 0;
					$res['type'] = "add";
					$res['res'] = "STAMPCHECK";
				}
			}
		}
		$response['success'] = $res;
	} else {
		if ($subassem_req == 'Yes') {
			$stage_id = '1,2,3';
			$sql = "select count(*) as cnt from tbl_transactions where product_id =:product_id and stage_id in (:stage_id)";
			$resultset = DbManager::fetchPDOQuery('spectra_db', $sql, [":product_id" => $product_id,":stage_id" => "$stage_id"])["data"];
			$cnt = $resultset[0]['cnt'];
			if ($cnt < 1) {
				$res['cnt'] = $cnt;
				$res['res'] = 'NOFOUND';
				$response['success'] = $res;
			} else{

			}
		}
	}
} else if($machine_name == $ip_address) {
	$_SESSION['station_name'] = $station_name;
	$_SESSION['product_name'] = $product_name;
	$_SESSION['product_id'] = $product_id;
	$_SESSION['station_id'] = $station_id;
	$_SESSION['mlfb_num'] = $mlfb_num;
	$_SESSION['subassem_req'] = $subassem_req;
	$_SESSION['machine_name'] = $machine_name;
	$_SESSION['scanqrcode'] = $scanqrcode;
	$_SESSION['remark'] = $remark;
    if ($subassem_req == 'Yes') {
		$scanqrcode1 = substr($scanqrcode,8,8);
		$daily_wq = "select count(*) as totald from tbl_DailyUpload where substring(Product,1,8) =:scanqrcode1 and progress_status !=:progress_status";
        $result = DbManager::fetchPDOQuery('spectra_db', $daily_wq, [":scanqrcode1" => "$scanqrcode1", ":progress_status" => "4"])["data"];
		$cnt_p = $result[0]['totald'];
        if ($cnt_p < 1) {
            $res['res'] = 'NOAVAILABLE';
        } else {
			/* if (preg_match("/shaft/i", $station_name)) {
				$stage_id = '8';
				$skip_station_id = "'%1048%'";
				$sql = "select count(*) as cnt from tbl_transactions where product_id =:product_id and barcode =:scanqrcode and status =1 and station_id like :skip_station_id";
			} else {
				$stage_id = '1,2,3';
				$skip_station_id = $station_id;
				$sql = "select count(*) as cnt from tbl_transactions where product_id =:product_id and barcode =:scanqrcode and status =1 and station_id =:skip_station_id";
			} */
			$stage_id = '1,2,3';
			$check_step = "select count('s.*') as count_rs from tbl_station s
			where concat(',', s.product_id, ',') like :product_id 
			and s.stage_id in (:stage_id) and s.station_id =:station_id and s.Machine_name =:machine_name";
			$rs_set = DbManager::fetchPDOQuery('spectra_db', $check_step, [":product_id" => "%,$product_id,%", ":stage_id" => "$stage_id", ":station_id" => "$station_id", ":machine_name" => "$machine_name"])["data"];
			$cnt_rs = $rs_set[0]['count_rs'];

        	if ($cnt_rs == 1) {
				$sql = "select count(*) as cnt from tbl_transactions where product_id =:product_id and barcode =:scanqrcode and status =1 and station_id =:station_id";
				$resultset = DbManager::fetchPDOQuery('spectra_db', $sql, [":product_id" => "$product_id", ":scanqrcode" => "$scanqrcode", ":station_id" => "$station_id"])["data"];
				$count_rs = $resultset[0]['cnt'];
				if ($count_rs < 1) {
					//To update status as Subassembly   
					$check_Uploadid = "select uploadid as count_rs from tbl_DailyUpload where SUBSTRING(Product,1,8) =:scanqrcode1 and progress_status = 0 LIMIT 1" ;	
					$rs_uploadid = DbManager::fetchPDOQuery('spectra_db', $check_Uploadid, [":scanqrcode1" => "$scanqrcode1"])["data"];
					$cnt_uploadid = $rs_uploadid[0]['count_rs'];

					$sql2="update tbl_DailyUpload set Progress_Status = 1, barcode =:scanqrcode where uploadid =:cnt_uploadid" ;	
					$resuploadid = DbManager::fetchPDOQuery('spectra_db', $sql2, [":scanqrcode" => $scanqrcode, ":cnt_uploadid" => $cnt_uploadid]);
					
					$res['cnt'] = $count_rs;
					$res['res'] = 'SUBPRECHECK';			
				} else if($count_rs > 0 && $count_rs < 2) {
					$res['cnt'] = $count_rs;
					$res['res'] = 'SUBASSEMBLY';
				} else if($count_rs > 1 && $count_rs < 3) {
					$res['cnt'] = $count_rs;
					$res['res'] = 'SUBLOCATION';
				} else if($count_rs == 3) {
					$res['cnt'] = $count_rs;
					$res['res'] = 'SUBASSDONE';					
				} else {
					$res['cnt'] = 0;
					$res['res'] = 'EXIST';					
				}
			} else {
				$check_subass = "select count('s.*') as count_ass from tbl_station s
					inner join tbl_transactions t on t.station_id = s.station_id
					where concat(',', s.product_id, ',') like :product_id and t.barcode=:scanqrcode
					and s.stage_id in (:stage_id)";
				$rs_sub = DbManager::fetchPDOQuery('spectra_db', $check_subass, [":product_id" => "%,$product_id,%", ":scanqrcode" => "$scanqrcode", ":stage_id" => "$stage_id"])["data"];
				$cnt = $rs_sub[0]['count_ass'];
				if ($cnt < 1) {
					$res['cnt'] = $cnt;
					$res['res'] = "REQASSEM";
				} else if ($cnt > 0 && $cnt < 2 || $cnt > 1 && $cnt < 3) {
					$res['cnt'] = $cnt;
					$res['res'] = "PARTIALDONE";
				} else {
					$sql = " select count(*) as cnt from tbl_transactions where product_id =:product_id and barcode=:scanqrcode";
					$resultset = DbManager::fetchPDOQuery('spectra_db', $sql, [":product_id" => "$product_id", ":scanqrcode" => "$scanqrcode"])["data"];
					$count_rs = $resultset[0]['cnt'];
					if ($count_rs < 1) {
						$check_Uploadid = "select uploadid as count_rs from tbl_DailyUpload where SUBSTRING(Product,1,8) =:scanqrcode1 and progress_status = 0";	
						$rs_uploadid = DbManager::fetchPDOQuery('spectra_db', $check_Uploadid, [":scanqrcode1" => "$scanqrcode1"])["data"];
						$cnt_uploadid = $rs_uploadid[0]['count_rs'];
						
						$sql2 = "update tbl_DailyUpload set Progress_Status = 1, barcode =:scanqrcode where uploadid =:cnt_uploadid" ;	
						$resuploadid = DbManager::fetchPDOQuery('spectra_db', $sql2, [":scanqrcode" => $scanqrcode, ":cnt_uploadid" => $cnt_uploadid]);
							
						$res['cnt'] = $count_rs;
						$res['res'] = 'SUBPRECHECK';
					} else if($count_rs > 0 && $count_rs < 2) {
						$res['cnt'] = $count_rs;
						$res['res'] = 'SUBASSEMBLY';
					} else if($count_rs > 1 && $count_rs < 3) {
						$res['cnt'] = $count_rs;
						$res['res'] = 'SUBLOCATION';
					} else if($count_rs > 2 ) {
						$trans_check_loc = "select count(*) as cnttotal from tbl_transactions where product_id =:product_id and stage_id=:stage_id and barcode =:scanqrcode";
						$rs_loc = DbManager::fetchPDOQuery('spectra_db', $trans_check_loc, [":product_id" => "$product_id", ":stage_id" => "3", ":scanqrcode" => "$scanqrcode"])["data"];
						$cnt_total_loc = $rs_loc[0]['cnttotal'];

						$trans_check_loc_new ="select station_name as station_names from tbl_transactions where product_id =:product_id and stage_id=:stage_id and barcode=:scanqrcode";
						$rs_loc_new = DbManager::fetchPDOQuery('spectra_db', $trans_check_loc_new, [":product_id" => "$product_id", ":stage_id" => "3", ":scanqrcode" => "$scanqrcode"])["data"];
						$cnt_total_loc_new = $rs_loc_new[0]['station_names'];

						$check_locstage = "select count(*) as total from tbl_station where concat(',', product_id, ',') like :product_id and concat(',', stage_id, ',') like :stage_id and station_name=:station_name";
						$result_loc = DbManager::fetchPDOQuery('spectra_db', $check_locstage, [":product_id" => "%,$product_id,%", ":stage_id" => "%,3,%", ":station_name" => "$cnt_total_loc_new"])["data"];
						$cnt_loc = $result_loc[0]['total'];
						//echo $cnt_loc ."  " .$cnt_total_loc."   ".$count_rs; exit;

						if ($cnt_loc == $cnt_total_loc) {
							$check_locstage2 = "select max(stage_id) as total from tbl_transactions where product_id =:product_id and barcode=:scanqrcode and station_id =:station_id";
							$result_loc2 = DbManager::fetchPDOQuery('spectra_db', $check_locstage2, [":product_id" => "$product_id", ":scanqrcode" => "$scanqrcode", ":station_id" => "$station_id"])["data"];
							$cnt_loc2 = $result_loc2[0]['total'];
							
							//$check_stationstage="select count(*) as total from tbl_station where concat(',', product_id, ',') like '%,$product_id,%'  and station_id = '".$station_id."' and stage_id like  '%4%' " ;
							$check_stationstage = "select stage_id as total from tbl_station where concat(',', product_id, ',') like :product_id and station_id =:station_id" ;
							$result_stationstage = DbManager::fetchPDOQuery('spectra_db', $check_stationstage, [":product_id" => "%,$product_id,%", ":station_id" => "$station_id"])["data"];
							$cnt_Stationstage = $result_stationstage[0]['total'];
							//echo $cnt_loc2." MAchine " .$cnt_Stationstage; exit;
							
							if ($cnt_loc2 == 4 && $cnt_Stationstage == '4,5') {
								$check_locstage1 = "select count(*) as total from tbl_station where concat(',', product_id, ',') like :product_id and concat(',', stage_id, ',') like :stage_id and station_id =:station_id";
								$result_loc1 = DbManager::fetchPDOQuery('spectra_db', $check_locstage1, [":product_id" => "%,$product_id,%", ":stage_id" => "%,4,%", ":station_id" => "$station_id"])["data"];
								$cnt_loc1 = $result_loc1[0]['total'];
								$trans_check_loc1 = "select count(*) as cnttotal from tbl_transactions where product_id =:product_id and stage_id=:stage_id and barcode=:scanqrcode and station_id =:station_id";
								$rs_loc1 = DbManager::fetchPDOQuery('spectra_db', $trans_check_loc1, [":product_id" => "$product_id", ":stage_id" => "4", ":scanqrcode" => "$scanqrcode", ":station_id" => "$station_id"])["data"];
								$cnt_total_loc1 = $rs_loc1[0]['cnttotal'];
								//echo $cnt_loc1 ."  " .$cnt_total_loc1."   ".$count_rs; exit;

								if ($cnt_loc1 == $cnt_total_loc1) {
									$res['cnt'] = 4;
									$res['res'] = 'ASSEMBLY_STAGE2';					
								}
							}
							if ($cnt_loc2 == null && $cnt_Stationstage == '4,5') {
								//echo $cnt_loc2." MAchine " .$cnt_Stationstage; exit;
								$res['cnt'] = $cnt_loc;
								$res['cnt_tot'] = $cnt_total_loc;
								$res['res'] = "ASSEMBLY";
							}
							//echo $cnt_loc2 ." space " .$cnt_Stationstage." space  ".$count_rs; exit;

							if ($cnt_loc2 == 5 || $cnt_Stationstage == '6,7') {
								//Testing
								//$check_locstage3="select count(stage_id) from tbl_checklistdetails where station_id like ('%".$station_id."%') and stage_id = 6 " ; 
								$check_locstage3 = "select count(*) as total from tbl_station where concat(',', product_id, ',') like :product_id and stage_id like :stage_id and station_id =:station_id"; 
								$result_loc3 = DbManager::fetchPDOQuery('spectra_db', $check_locstage3, [":product_id" => "%,$product_id,%", ":stage_id" => "%6%", ":station_id" => "$station_id"])["data"];
								$cnt_loc3 = $result_loc3[0]['total'];
								//echo $cnt_loc3 ." space " .$check_locstage3; exit;
								
								//To check Assembly has done already on this station---------------
								$check_Assstage = "select count(*) as total from tbl_station where concat(',', product_id, ',') like :product_id and concat(',', stage_id, ',') like :stage_id";
								$result_Ass = DbManager::fetchPDOQuery('spectra_db', $check_Assstage, [":product_id" => "%,$product_id,%", ":stage_id" => "%,4,%"])["data"];
								$cnt_Ass = $result_Ass[0]['total'];
								$trans_check_Ass = "select count(*) as cnttotal from tbl_transactions where product_id =:product_id and stage_id =:stage_id and barcode =:scanqrcode";
								$result_transAss = DbManager::fetchPDOQuery('spectra_db', $trans_check_Ass, [":product_id" => "$product_id", ":stage_id" => "4", ":scanqrcode" => "$scanqrcode"])["data"];
								$cnt_total_Ass = $result_transAss[0]['cnttotal'];								
								//echo $check_Assstage ." space " .$trans_check_Ass; exit;
										
								//if($cnt_loc3 == 0)
								//{
								//	$res['cnt']=0;
								//	$res['res']='No_TestingStage';					
								//}
								//else
								//{
									if ($cnt_loc3 == 0) {
										$res['cnt'] = 0;
										$res['res'] = 'No_TestingStage';					
									} else {
										$check_locstage1 = "select count(*) as total from tbl_station where concat(',', product_id, ',') like :product_id and concat(',', stage_id, ',') like :stage_id";
										$result_loc1 = DbManager::fetchPDOQuery('spectra_db', $check_locstage1, [":product_id" => "%,$product_id,%", ":stage_id" => "%5%"])["data"];
										$cnt_loc1 = $result_loc1[0]['total'];
										
										$trans_check_loc1 = "select count(*) as cnttotal from tbl_transactions where product_id =:product_id and stage_id=:stage_id and barcode=:scanqrcode";
										$rs_loc1 = DbManager::fetchPDOQuery('spectra_db', $trans_check_loc1, [":product_id" => "$product_id", ":stage_id" => "5", ":scanqrcode" => "$scanqrcode"])["data"];
										$cnt_total_loc1 = $rs_loc1[0]['cnttotal'];
										//echo $check_locstage1 ."  " .$trans_check_loc1."   ".$cnt_locstid; exit;
										//echo $cnt_loc1 ."  " .$cnt_total_loc1."   ".$cnt_locstid; exit;
										if ($cnt_loc1 == $cnt_total_loc1) {
											$res['cnt'] = 0;
											$res['res'] = 'PRECHECKTEST';					
										} else {
											$check_DupStation = "select count(*) as total,station_id as stid from tbl_station where concat(',', product_id, ',') like :product_id and concat(',', stage_id, ',') like :stage_id group by station_id";
											$result_loc1 = DbManager::fetchPDOQuery('spectra_db', $check_DupStation, [":product_id" => "%,$product_id,%", ":stage_id" => "%5%"])["data"];
											$B2B3station = 0;
											$A6A7station = 0;

											foreach ($result_loc1 as $row) { 
												switch ($row['stid']) {
													case "8":
														if ($B2B3station != 0) {
															$cnt_loc1 = $cnt_loc1 - 1;												
														}
														$B2B3station = 1;											
														break;
													case "9":
														if ($B2B3station != 0) {
															$cnt_loc1 = $cnt_loc1 - 1;												
														}
														$B2B3station = 1;											
														break;		
													case "6":
														if ($A6A7station != 0) {
															$cnt_loc1 = $cnt_loc1 - 1;												
														}
														$A6A7station = 1;											
														break;
													case "7":
														if ($A6A7station != 0) {
															$cnt_loc1 = $cnt_loc1 - 1;												
														}
														$A6A7station = 1;											
														break;	
												}
												//echo odbc_result($result_loc1,'stid');
											}
											//echo $cnt_loc1 ."  " .$cnt_total_loc1."   ".$cnt_locstid; exit;
											//if($cnt_loc1 == $cnt_total_loc1)
											//{
											//	$res['cnt']=0;
											//	$res['res']='PRECHECKTEST';					
											//}
											//-----------------------------------------------------------------------------------
											$sql1 = "select count(*) as cnt from tbl_transactions where product_id =:product_id and barcode=:scanqrcode and stage_id=:stage_id and station_id =:station_id";
											$resultset1 = DbManager::fetchPDOQuery('spectra_db', $sql1, [":product_id" => "$product_id", ":scanqrcode" => "$scanqrcode", ":stage_id" => "6", ":station_id" => "$station_id"])["data"];
											$count_rs1 = $resultset1[0]['cnt'];
											//echo $count_rs1;exit;
											if ($cnt_loc1 == $cnt_total_loc1) {
												$res['cnt'] = 0;
												$res['res'] = 'PRECHECKTEST';					
											} else if ($count_rs1 < 1) {
												$res['cnt'] = 0;
												$res['res'] = 'PRECHECKTEST';
											} else if ($count_rs1 > 0 && $count_rs1 < 2) {
												$sql1f = "select count(*) as cnt from tbl_transactions where product_id = '".$product_id."' and barcode=:scanqrcode and stage_id=:stage_id and station_id =:station_id";									
												$resultset1f = DbManager::fetchPDOQuery('spectra_db', $sql1f, [":product_id" => "$product_id", ":scanqrcode" => "$scanqrcode", ":stage_id" => "7", ":station_id" => "$station_id"])["data"];
												$count_rs1f = $resultset1[0]['cnt'];
												if ($count_rs1f < 1) {
													$res['cnt'] = 0;
													$res['res'] = 'TESTPARAMETER';
												}
											} else {
												$res['res'] = 'NA_Testing';
											}
										}
									}
								//}
							}

							if ($cnt_loc2 == null && $cnt_Stationstage == '8') {
								//echo $cnt_loc2." MAchine " .$cnt_Stationstage; exit;
								$res['cnt'] = $cnt_loc;
								$res['cnt_tot'] = $cnt_total_loc;
								$res['res'] = "STAMPCHECK";
							} else if ($cnt_loc2 == $cnt_Stationstage) {
								$res['cnt'] = 0;
								$res['res'] = 'EXIST';
							}
						} else if ($cnt_total_loc < $cnt_loc) {
							$res['cnt'] = $cnt_loc;
							$res['cnt_tot'] = $cnt_total_loc;
							$res['res'] = "NA_ASSEMPREC";
						}
					} else if($count_rs > 3 && $count_rs < 5) {
						$res['cnt'] = $count_rs;
						$res['res'] = 'ASSEMBLY_STAGE2';		
					}		
				}
            }
        }
        $response['success']=$res;
    } else {
        $scanqrcode1 = substr($scanqrcode,8,8);
        $daily_wq = "select count(*) as totald from tbl_DailyUpload where substring(Product,1,8)=:scanqrcode1 and progress_status !=:progress_status";
        $result = DbManager::fetchPDOQuery('spectra_db', $daily_wq, [":scanqrcode1" => "$scanqrcode1", ":progress_status" => "4"])["data"];
        $cnt_p = $result[0]['totald'];
		//echo $cnt_p; exit;
        if ($cnt_p < 1) {
            $res['res']='NOAVAILABLE';
        } else {
            $check_step = "select count('s.*') as count_rs from tbl_station s
            where concat(',', s.product_id, ',') like :product_id
            and  s.stage_id in (:stage_id) and s.station_id=:station_id and s.Machine_name =:machine_name";
            $rs_set = DbManager::fetchPDOQuery('spectra_db', $check_step, [":product_id" => "%,$product_id,%", ":stage_id" => "4,5", ":station_id" => "$station_id", ":machine_name" => "$machine_name"])["data"];
            $cnt_rs = $rs_set[0]['count_rs'];

			if ($cnt_rs == 1) {
				$sql = "select count(*) as cnt from tbl_transactions where product_id =:product_id and barcode=:scanqrcode and status =:status and station_id =:station_id";
                $resultset = DbManager::fetchPDOQuery('spectra_db', $sql, [":product_id" => "$product_id", ":scanqrcode" => "$scanqrcode", ":status" => "1", ":station_id" => "$station_id"])["data"];
				$count_rs = $resultset[0]['cnt'];
				//echo $count_rs;exit;
				if ($count_rs < 1) {
					$check_Uploadid = "select uploadid as count_rs from tbl_DailyUpload where SUBSTRING(Product,1,8) =:scanqrcode1 '".$scanqrcode1."' and progress_status !=:progress_status LIMIT 1";	
					$rs_uploadid = DbManager::fetchPDOQuery('spectra_db', $check_Uploadid, [":scanqrcode1" => "$scanqrcode1", ":progress_status" => "4"])["data"];
					$cnt_uploadid = $rs_uploadid[0]['count_rs'];
					
					$sql2 = "update tbl_DailyUpload set Progress_Status =:progress_status, barcode =:scanqrcode where uploadid = '".$cnt_uploadid."'" ;	
					$resuploadid = DbManager::fetchPDOQuery('spectra_db', $sql2, [":progress_status" => "2", ":scanqrcode" => "$scanqrcode", ":cnt_uploadid" => $cnt_uploadid]);
					$res['cnt'] = $count_rs;
                    //$res['cnt_tot'] = $cnt_total_loc;
                    $res['res'] = "ASSEMBLYWS";         	
				} else if ($count_rs > 0 && $count_rs < 2) {
					$res['cnt']=$count_rs;
					$res['res']='ASSEMBLY_STAGE2WS';
				} else {
					/* $check_locstage1 = "select count(*) as total from tbl_station where concat(',', product_id, ',') like :product_id and concat(',', stage_id, ',') like :stage_id";
					$result_loc1 = DbManager::fetchPDOQuery('spectra_db', $check_locstage1, [":product_id" => "%,$product_id,%", ":stage_id" => "%,5,%"])["data"];
					$cnt_loc1 = $result_loc1[0]['total'];
					$trans_check_loc1 = "select count(*) as cnttotal from tbl_transactions where product_id =:product_id and stage_id=:stage_id and barcode=:scanqrcode";
					$rs_loc1 = DbManager::fetchPDOQuery('spectra_db', $trans_check_loc1, [":product_id" => "$product_id", ":stage_id" => "5", ":scanqrcode" => "$scanqrcode"])["data"];
					$cnt_total_loc1 = $rs_loc1[0]['cnttotal'];
					//echo $cnt_loc1 ."  " .$cnt_total_loc1."   ".$count_rs; exit;
					if ($cnt_loc1 == $cnt_total_loc1) {
						//$res['cnt']=0;
						//$res['res']='PRECHECKTEST';		
					} */
				}               
			}
			$check_step1 = "select count('s.*') as count_rs from tbl_station s
				where concat(',', s.product_id, ',') like :product_id
				and s.stage_id in (:stage_id) and s.station_id=:station_id and s.Machine_name =:machine_name";
            $rs_set1 = DbManager::fetchPDOQuery('spectra_db', $check_step1, [":product_id" => "%,$product_id,%", ":stage_id" => "6,7", ":station_id" => "$station_id", ":machine_name" => "$machine_name"])["data"];
            $cnt_rs1 = $rs_set1[0]['count_rs'];
			//echo $cnt_rs1; exit;
			if ($cnt_rs1 == 1) {
				//-------------To check assebmly done for stations-------------------------------------------
				$check_locstage1 = "select count(*) as total from tbl_station where concat(',', product_id, ',') like :product_id and concat(',', stage_id, ',') like :stage_id";
				$result_loc1 = DbManager::fetchPDOQuery('spectra_db', $check_locstage1, [":product_id" => "%,$product_id,%", ":stage_id" => "%5%"])["data"];
				$cnt_loc1 = $result_loc1[0]['total'];
				//$cnt_locstid = odbc_result($result_loc1,'stid');
				$trans_check_loc1 = "select count(*) as cnttotal from tbl_transactions where product_id =:product_id and stage_id=:stage_id and barcode=:scanqrcode";
				$rs_loc1 = DbManager::fetchPDOQuery('spectra_db', $trans_check_loc1, [":product_id" => "$product_id", ":stage_id" => "5", ":scanqrcode" => "$scanqrcode"])["data"];
				$cnt_total_loc1 = $rs_loc1[0]['cnttotal'];
				if ($cnt_loc1 == $cnt_total_loc1) {
					$res['cnt'] = 0;
					$res['res'] = 'PRECHECKTESTWS';					
				}
				
				$check_DupStation = "select count(*) as total,station_id as stid from tbl_station where concat(',', product_id, ',') like :product_id and concat(',', stage_id, ',') like :stage_id group by station_id";
				$result_loc1 = DbManager::fetchPDOQuery('spectra_db', $check_DupStation, [":product_id" => "%,$product_id,%", ":stage_id" => "%5%"])["data"];
				$B2B3station = 0;
				$A6A7station = 0;
				foreach ($result_loc1 as $row) {
					switch ($row['stid']) {
						case "8":
							if ($B2B3station != 0) {
								$cnt_loc1 = $cnt_loc1 - 1;			
							}
							$B2B3station = 1;											
							break;
						case "9":
							if ($B2B3station != 0) {
								$cnt_loc1 = $cnt_loc1 - 1;
							}
							$B2B3station = 1;											
							break;		
						case "6":
							if ($A6A7station != 0) {
								$cnt_loc1 = $cnt_loc1 - 1;
							}
							$A6A7station = 1;											
							break;
						case "7":
							if ($A6A7station != 0) {
								$cnt_loc1 = $cnt_loc1 - 1;
							}
							$A6A7station = 1;											
							break;
					}
				}
				//-----------------------------------------------------------------------------------
				$sql1 = "select count(*) as cnt from tbl_transactions where product_id =:product_id and barcode=:scanqrcode and stage_id=:stage_id and station_id =:station_id";
                $resultset1 = DbManager::fetchPDOQuery('spectra_db', $sql1, [":product_id" => "$product_id", ":scanqrcode" => "$scanqrcode", ":stage_id" => "6", ":station_id" => "$station_id"])["data"];
				$count_rs1 = $resultset1[0]['cnt'];
				if ($cnt_loc1 == $cnt_total_loc1) {
					$res['cnt'] = 0;
					$res['res'] = 'PRECHECKTESTWS';					
				} else if ($count_rs1 < 1) {
					$res['cnt'] = 0;
					$res['res'] = 'PRECHECKTESTWS';
				} else if($count_rs1 > 0 && $count_rs1 < 2) {
					$sql1f = " select count(*) as cnt from tbl_transactions where product_id =:product_id and barcode=:scanqrcode and stage_id=:stage_id and station_id =:station_id";									
					$resultset1f = DbManager::fetchPDOQuery('spectra_db', $sql1f, [":product_id" => "$product_id", ":scanqrcode" => "$scanqrcode", ":stage_id" => "7", ":station_id" => "$station_id"])["data"];
					$count_rs1f = $resultset1[0]['cnt'];
					if ($count_rs1f < 1) {
						$res['cnt'] = $count_rs;
						$res['res'] = 'TESTINGSTAGE2WS';
					}
					//$res['cnt'] = $count_rs;
					//$res['res'] = 'TESTINGSTAGE2WS';
				}
			}
			/*else if ($cnt_rs1 == 0)
			{
				$res['cnt'] = 0;
				$res['res'] = 'No_TestingStage';					
			}*/
        }
        $response['success']=$res;
    }
}

/* $sql = "select count(*) as cnt from tbl_transactions where station_id =:station_id and product_id =:product_id";
$resultset = DbManager::fetchPDOQuery('spectra_db', $sql, [":station_id" => "$station_id", ":product_id" => "$product_id"])["data"];
$cnt = $resultset[0]['cnt'];
if ($cnt < 1) {
    $res['cnt'] = $cnt;
	$response['success'] = $res;
} else {
    $sql1 = "select stage_id from tbl_transactions where station_id =:station_id and product_id =:product_id group by stage_id order by stage_id DESC";
    $resultset1 = DbManager::fetchPDOQuery('spectra_db', $sql1, [":station_id" => "$station_id", ":product_id" => "$product_id"])["data"];
    $stage_id = $resultset1;
    $res['cnt'] = $cnt;
    $res['stage_id'] = $stage_id;
    $response['success'] = $res;
} */

header("Content-type:application/json");
echo json_encode($response);
die();
?>
