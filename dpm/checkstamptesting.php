<?php
session_start();
$user=$_SESSION['username'];
$user_id = $_SESSION['user_id'];
$pass = $_SESSION['pass'];
include('shared/CommonManager.php');
$product_name = $_SESSION['product_name'];
$station_name = $_SESSION['station_name'];
$product_id = $_SESSION['product_id'];
$station_id = $_SESSION['station_id'];
$machine_name = $_SESSION['machine_name'];
$scanqrcode = $_SESSION['scanqrcode'];
$remark = $_SESSION['remark'];
$id = $_REQUEST['id'];
$action = isset($_REQUEST['id']) && $_REQUEST['id'] != '' ? 'edit' : 'add';

$sql_station= "SELECT stage_id FROM tbl_station where station_id=:station_id";
$rs = DbManager::fetchPDOQueryData('spectra_db',$sql_station,[":station_id" => "$station_id"])["data"];
$stage_id = $rs[0]['stage_id'];

$sql = "SELECT * FROM tbl_stage where stage_type=:stage_type AND FIND_IN_SET(stage_id, :stage_id) > 0";	
$query = DbManager::fetchPDOQueryData('spectra_db',$sql,[":stage_type" => "Post Assembly",":stage_id" => "$stage_id"])["data"];

$sqldetails="select rating, Client from tbl_DailyUpload where Barcode =:scanqrcode LIMIT 1" ;
$querydetails = DbManager::fetchPDOQueryData('spectra_db',$sqldetails,[":scanqrcode" => "$scanqrcode"])["data"];

$sqlworkbench="select * from tbl_workbench";
$querywork = DbManager::fetchPDOQueryData('spectra_db', $sqlworkbench)["data"];

$sql1 = "SELECT * FROM tbl_transactions where user_id=:user_id";
$query1 = DbManager::fetchPDOQueryData('spectra_db',$sql1,[":user_id" => "$user_id"])["data"];

$sql_transaction = "SELECT * FROM tbl_transactions WHERE stage_id=:stage_id AND status=:status AND tr_id=:tr_id GROUP BY barcode ORDER BY tr_id DESC";
$transaction_details = DbManager::fetchPDOQueryData('spectra_db', $sql_transaction, [":stage_id" => "8", ":status" => "0", ":tr_id" => $_REQUEST['id']])["data"][0];
$login_type = $_SESSION['role_name'];
if ($login_type == "Stamping") {
    $welcome_msg =  "Greetings, Stamper! Welcome to Digital Production Management";
} else if ($login_type == "Manufacturing") {
    $welcome_msg =  "Greetings, Manufacturer! Welcome to Digital Production Management";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DPM | Dashboard</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400">
    <link rel="stylesheet" href="font-awesome-4.6.3/css/font-awesome.min.css">

    <!-- Css for datepicker -->
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="plugins/select2/css/select2.min.css">

    <!-- Css for datatable -->
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

    <style>
    .btn-circle.btn-xl {
        width: 60px;
        height: 60px;
        padding: 10px 16px;
        border-radius: 35px;
        font-size: 20px;
        line-height: 1.33;
        background-color: #bbb;
        border-color: black;
    }

    .btn-circle {
        width: 25px;
        height: 25px;
        padding: 6px 0px;
        border-radius: 15px;
        text-align: center;
        font-size: 12px;
        line-height: 1.42857;
    }

    .dot {
        height: 25px;
        width: 25px;
        background-color: #bbb;
        border-radius: 50%;
        display: inline-block;
    }

    .dot_inprogress {
        height: 25px;
        width: 25px;
        background-color: #2d9ab8;
        border-radius: 50%;
        display: inline-block;

    }

    .dot_complete {
        height: 25px;
        width: 25px;
        background-color: #28a745;
        border-radius: 50%;
        display: inline-block;

    }

    .dot_error {
        height: 25px;
        width: 25px;
        background-color: #FF0000;
        border-radius: 50%;
        display: inline-block;

    }
    </style>
</head>

<body class="hold-transition sidebar-mini sidebar-collapse"
    style="background:url('img/bg.jpg') left no-repeat;	background-size:cover;">
    <!-- Site wrapper -->
    <div class="wrapper">
        <!-- Navbar for pay bill login -->
        <nav class="main-header navbar navbar-expand navbar-white  navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>

                <li class="nav-item d-none d-sm-inline-block">
                    <span class="nav-link"><?php echo strtoupper($user); ?></span>
                </li>
            </ul>


            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <h1 class="navbar-nav"
                    style="color:#009999; font-size:40px; font-famiy:Siemens sans Black;  font-weight: bold;">SIEMENS
                </h1>
                <!-- Messages Dropdown Menu -->

                <!-- Notifications Dropdown Menu -->

                <li class="nav-item d-none d-sm-inline-block">
                    <span class="nav-link"></span>
                </li>
                <!-- <li class="nav-item">
        <img src="img/emb.gif" alt="" height="55" />
      </li>-->
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="Stampingdashboard.php" class="brand-link">
                <img src="img/fav_logo3.png" alt="DPM Logo" class="brand-image img-thumbnail elevation-1">
                <span class="brand-text font-weight-light"><br />
                    <small style="font-size:0.6em;"></small></span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">


                <ul id="accordion" class="accordion nav nav-pills nav-sidebar flex-column">
                    <!-- <li><a href="Admindash.php"><div class="link"><i class="fa fa-dashboard"></i><span>Dashboard</span></div></a></li> -->
                    <li><a href="generatestamping.php">
                            <div class="link"><i class="fa fa-hourglass-start"></i><span>Start </span></div>
                        </a></li>
                        <li>
                        <a href="stampingdetails.php">
                            <div class="link"><i class="fas fa-layer-group"></i><span>List </span></div>
                        </a>
                    </li>
                    <li>
                        <div class="link"><A href="logout.php"> <i
                                    class="fa fa-sign-out nav-icon"></i><span>Logout</span></a></div>

                    </li>
                </ul>

                <!-- Sidebar Menu -->

                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header mb-2">
                <div class="container-fluid">

                    <div class="pagehead">
                        <div class="row">
                            <div class=" col-md-6 col-sm-6">
                                <h5><?php echo $welcome_msg; ?></h5>
                            </div>
                            <div class="col-sm-8">
                                <div class="tab float-sm-right">
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- /.container-fluid -->
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- Default box -->
                            <div class="framecontent">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="blockhead">
                                            <h3><i class="fa fa-address-card"></i> Product Details</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-body formarea smpadding">
                                        <div class="row">
                                            <div class="col-md-12"
                                                style="display: flex; justify-content: space-between;">
                                                <label style="display: flex; justify-content: space-between;">
                                                    <div style="text-align: left;">
                                                        <b>Product Name: &nbsp;&nbsp;<span
                                                                id="product_nm"><?php echo $product_name; ?></span></b>
                                                    </div>
                                                    <div style="text-align: center;"></div>
                                                    <div style="text-align: right;">
                                                        <b>Station Name: &nbsp;&nbsp;<span
                                                                id="product_nm"><?php echo $station_name; ?></span></b>
                                                    </div>
                                                </label>
                                            </div>
                                            <!-- <div class="col-md-4">
					<label>Machine Name: &nbsp;&nbsp;<span id="mlfb_num"><?php echo $machine_name; ?> </span></label>
					<label>Client Name: &nbsp;&nbsp;<span id="mlfb_num"><?php echo $querydetails[0]["Client"];  ?> </span></label>
				</div>
        <div class="col-md-4">
					<label>Customer Name: &nbsp;&nbsp;<span id="customer_name"><?php echo $pass; ?></span></label>
					<label>Rating: &nbsp;&nbsp;<span id="customer_name"><?php echo $querydetails[0]["rating"]; ?></span></label>
				</div> -->

                                        </div>

                                    </div>
                                    <!-- /.card-body -->
                                    <!--<div class="card-footer">
                Footer
              </div>-->
                                    <!-- /.card-footer-->
                                </div>

                                <div class="modal fade" id="myModal" role="dialog">
                                    <div class="modal-dialog">

                                        <!-- Modal content-->
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 align="left" class="modal-title" id="work_title"></h4>
                                                <button type="button" class="close"
                                                    data-dismiss="modal">&times;</button>

                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" id="work_id" name="work_id">
                                                <div id="work_rewark" style="display:none">
                                                    <p align="right"> <button class="btn btn-primary btn-sm"
                                                            id="view_rework">View Rework&nbsp;</button></p>

                                                    <label>Types of Rework: </label>
                                                    <select name="w_type" class="select2 form-control" id="w_type">
                                                        <option value="">Select Rework_type</option>

                                                        <?php 
                            $sql_rework="select w_type,w_id from tbl_manageworkbench where workbench_id=:workbench_id";
                            $res=DbManager::fetchPDOQueryData('spectra_db',$sql_rework,[":workbench_id" => "1"])["data"];
                            foreach ($res1 as $res) { 
                               
                                    echo '<option value="'.$res["w_id"].'">'.$res["w_type"].'</option>';                               

                                 } ?>

                                                    </select>

                                                    <label>Remark: </label>
                                                    <textarea class="form-control" name="rework_remark"
                                                        id="rework_remark"></textarea>


                                                    <!-- <label>Start Date: </label>
                                 <input type="text" class="form-control" name="start_date_rework" value ="<?php echo date('Y-m-d h:i:s'); ?>" id="start_date_rework" disabled>
                                 <label>End Date: </label>
                                 <input type="text" class="form-control" name="end_date_rework" id="end_date_rework" disabled> -->


                                                </div>

                                                <div id="work_sop" style="display:none">
                                                    <p align="right"> <button class="btn btn-primary"
                                                            id="view_sop_files">View SOP Files&nbsp;<i
                                                                class='far fa-file-pdf'></i></button></p>
                                                    <br>
                                                    <label>PDF UPLOAD: </label>
                                                    <input type="file" class="form-control" name="sop_file"
                                                        id="sop_file">

                                                </div>
                                                <div id="div_breakdown" style="display:none">

                                                    <label>Types of Breakdown: </label>
                                                    <select name="break_type" class="select2 form-control"
                                                        id="break_type">
                                                        <option value="">Select Breakdown Type</option>

                                                        <?php 
                            $sql_rework="select w_type,w_id from tbl_manageworkbench where workbench_id=:workbench_id";
                            $res=DbManager::fetchPDOQueryData('spectra_db',$sql_rework,[":workbench_id" => "3"])["data"];
                            foreach ($res1 as $res) { 
                               
                              echo '<option value="'.$res["w_id"].'">'.$res["w_type"].'</option>';
 
                               

                                 } ?>

                                                    </select>
                                                    <label>Enter Remark Breakdown</label>
                                                    <textarea class="form-control" name="break_rem"
                                                        id="break_rem"></textarea>
                                                    <!--<input type="text" name="break_rem" class="form-control"> -->
                                                    <!-- <label>Start Date: </label>
                                 <input type="text" class="form-control" name="start_date_break" id="start_date_break" value ="<?php echo date('Y-m-d h:i:s'); ?>" disabled>
                                 <label>End Date: </label>
                                 <input type="text" class="form-control" name="end_date_break" id="end_date_break" readonly> -->

                                                </div>


                                                <div id="div_alert" style="display:none">

                                                    <label>Type Of Missing Material: </label>
                                                    <select name="missing_type" class="select2 form-control"
                                                        id="missing_type">
                                                        <option value="">Select Missing Material type</option>

                                                        <?php 
                            $sql_rework="select w_type,w_id from tbl_manageworkbench where workbench_id=:workbench_id";
                            $res=DbManager::fetchPDOQueryData('spectra_db',$sql_rework,[":workbench_id" => "4"])["data"];
                            foreach ($res1 as $res) { 
                               
                              echo '<option value="'.$res["w_id"].'">'.$res["w_type"].'</option>';
 
                               

                                 } ?>

                                                    </select>
                                                    <label>Enter Remark Missing Material</label>
                                                    <textarea class="form-control" name="missing_rem"
                                                        id="missing_rem"></textarea>
                                                    <!--<input type="text" name="missing_rem" class="form-control"> -->
                                                    <!-- <label>Start Date: </label>
                                 <input type="text" class="form-control" name="start_date_Missing" id="start_date_Missing" value ="<?php echo date('Y-m-d h:i:s'); ?>" disabled>
                                 <label>End Date: </label>
                                 <input type="text" class="form-control" name="end_date_Missing" id="end_date_Missing" readonly> -->

                                                </div>

                                                <div id="div_checklist" style="display:none">

                                                    <label>Types of Missing Infrastructure: </label>
                                                    <select name="infra_type" class="select2 form-control"
                                                        id="infra_type">
                                                        <option value="">Select Missing Infrastructure type</option>

                                                        <?php 
                            $sql_rework="select w_type,w_id from tbl_manageworkbench where workbench_id=:workbench_id";
                            $res=DbManager::fetchPDOQueryData('spectra_db',$sql_rework,[":workbench_id" => "1002"])["data"];
                            foreach ($res1 as $res) { 
                               
                              echo '<option value="'.$res["w_id"].'">'.$res["w_type"].'</option>';
 
                               

                                 } ?>

                                                    </select>
                                                    <label>Enter Remark Infrastructure</label>
                                                    <textarea class="form-control" name="Infra_remark"
                                                        id="Infra_remark"></textarea>
                                                    <!--<input type="text" name="Infra_remark" class="form-control"> -->
                                                    <!-- <label>Start Date: </label>
                                 <input type="text" class="form-control" name="start_date_Infra" id="start_date_Infra" value ="<?php echo date('Y-m-d h:i:s'); ?>" disabled>
                                 <label>End Date: </label>
                                 <input type="text" class="form-control" name="end_date_Infra" id="end_date_Infra" readonly> -->

                                                </div>


                                                <div id="div_kitting" style="display:none">
                                                    <label>Remark: </label>
                                                    <textarea class="form-control" name="kitting_remark"
                                                        id="kitting_remark"></textarea>



                                                </div>


                                                <div id="div_checklist" style="display:none">
                                                    <label>Remark: </label>
                                                    <textarea class="form-control" name="check_remark"
                                                        id="check_remark"></textarea>



                                                </div>
                                                <div id="div_det" style="display:none">
                                                    <label>Remark: </label>
                                                    <textarea class="form-control" name="det_remark"
                                                        id="det_remark"></textarea>



                                                </div>

                                                <div id="div_spec_point" style="display:none">
                                                    <label>Remark: </label>
                                                    <textarea class="form-control" name="spec_point_remark"
                                                        id="spec_point_remark"></textarea>



                                                </div>

                                                <!--<div id="div_defect" style="display:none">
         <label>Remark: </label>
							<textarea class="form-control" name="defect_remark" id="defect_remark"></textarea> 



                                </div> -->
                                                <!-- Safety -->
                                                <div id="div_defect" style="display:none">

                                                    <label>Safety: </label>
                                                    <select name="Safety_type" class="select2 form-control"
                                                        id="Safety_type">
                                                        <option value="">Select Safety Type</option>

                                                        <?php 
                            $sql_rework="select w_type,w_id from tbl_manageworkbench where workbench_id=:workbench_id";
                            $res=DbManager::fetchPDOQueryData('spectra_db',$sql_rework,[":workbench_id" => "2"])["data"];
                            foreach ($res1 as $res) { 
                               
                              echo '<option value="'.$res["w_id"].'">'.$res["w_type"].'</option>';
 
                               

                                 } ?>

                                                    </select>
                                                    <label>Enter Remark Safety</label>
                                                    <textarea class="form-control" name="safety_rem"
                                                        id="safety_rem"></textarea>
                                                    <label>Start Date: </label>
                                                    <input type="text" class="form-control" name="start_date_safety"
                                                        id="start_date_safety"
                                                        value="<?php echo date('Y-m-d h:i:s'); ?>" readonly>
                                                    <label>End Date: </label>
                                                    <input type="text" class="form-control" name="end_date_safety"
                                                        id="end_date_safety" readonly>

                                                </div>
                                                <!-- End -->

                                                <div id="div_skippart" style="display:none">
                                                    <label>Remark: </label>
                                                    <textarea class="form-control" name="skip_remark"
                                                        id="skip_remark"></textarea>



                                                </div>

                                                <div id="div_paus" style="display:none">
                                                    <label>Remark: </label>
                                                    <textarea class="form-control" name="pause_remark"
                                                        id="pause_remark"></textarea>



                                                </div>

                                            </div>
                                            <div class="modal-footer">
                                                <input type="submit" class="btn btn-warning" value="Save"
                                                    id="save_workbench"> <button type="button" class="btn btn-default"
                                                    data-dismiss="modal">Close</button>
                                            </div>
                                        </div>

                                    </div>
                                </div>


                                <!-- /.card -->
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="blockhead">
                                                    <h3><i class="fa fa-map-marker"></i>Test Checklist </h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-body formarea smpadding">

                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="display_table">
                                                            <input type="hidden" id="checkstage_id" value="1">

                                                            <table id="example1"
                                                                class="table table-bordered table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Sr. No.</th>
                                                                        <th>Checklist Item</th>
                                                                        <th>Stamper Initial</th>
                                                                        <th>Stamper Initial Remarks</th>
                                                                        <th>Stamper Final</th>
                                                                        <th>Stamper Final Remarks</th>
                                                                    </tr>
                                                                </thead>
                                                            </table>
                                                            <p align="right"><input type="button"
                                                                    class="btn btn-primary" id="check_submit"
                                                                    name="Submit" value="Save">
                                                                <!-- <input type="button" class="btn btn-success" id="check_skip" name="Submit" value="Skip">  -->
                                                            </p>

                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="blockhead">
                                                    <h3><i class="fa fa-map-marker"></i>Workbench Navigator </h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card"
                                            style="height: 536px; display: flex; flex-direction: column; justify-content: space-between; padding: 10px; overflow: hidden;">
                                            <div class="card-body formarea smpadding d-flex flex-column justify-content-between"
                                                style="flex-grow: 1; overflow-y: auto;">
                                                <div class="row d-flex justify-content-center align-items-center">
                                                    <?php $i=1; foreach ($querywork as $querywork1) { ?>
                                                    <div class="col-md-6 mb-3 text-center">
                                                        <a style="cursor: pointer;"
                                                            data-id="<?php echo $querywork1['id']; ?>"
                                                            work-id="<?php echo $querywork1['title']; ?>"
                                                            data-toggle="modal" class="workbench_clk"
                                                            data-target="#myModal"><?php echo html_entity_decode($querywork1['title_icon'])."<br>".html_entity_decode($querywork1['title']); ?></a>
                                                    </div>
                                                    <?php $i++;  } ?>
                                                </div>
                                                <br>
                                                <br>
                                                <br>
                                                <br>




                                                <div class="row">



                                                </div>
                                            </div>
                                        </div>
                                        <!-- Source Label Section -->
                                        <!-- <div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3>Safety Compliance [PPE] </h3>
					</div>
				</div>
			   </div>

         <div class="card">
			   <div class="card-body formarea smpadding">
       
 
 <div class="row">&nbsp;&nbsp;&nbsp; <span class="dot"></span>&nbsp; Initial
 &nbsp;&nbsp;<span class="dot_inprogress"></span> &nbsp; In Progress
 &nbsp;&nbsp;<span class="dot_complete"></span> &nbsp; Complete
 &nbsp;&nbsp;<span class="dot_error"></span> &nbsp; Error
  </div>



         </div>
         </div>

         <div class="card">
			   <div class="card-body formarea smpadding">
         <div class="row"><img src="./img/sanclock.gif">
         
    
    <p id="hours"></p>
         </div>

         </div></div> -->
                                        <!-- End Label Section -->
                                    </div>


                                    <div class="card col-md-12">
                                        <div class="card-body formarea smpadding">
                                            <div class="row"
                                                style="display: flex; justify-content: space-around; align-items: center;">
                                                <div>&nbsp;&nbsp;&nbsp;</div>
                                                <?php $i=1; $j=0;
$arr=array('<i class="fa fa-check-circle-o" style="font-size:24px;"></i>','<i class="fab fa-slack" style="font-size:24px;"></i>','<i class="fa fa-folder-open" style="font-size:24px;"></i>','<i class="fa fa-bullseye" style="font-size:24px"></i>','<i class="fa fa-check-square-o" style="font-size:24px;" ></i>','<i class="fa fa-check-square" style="font-size:24px"></i>','<i class="fa fa-calendar-check-o" style="font-size:24px"></i>','<i class="fas fa-clipboard-list" style="font-size:24px;"></i>');
foreach ($query as $query1)  { 
if($i == 1){
echo '<div style="display: flex; flex-direction: column; justify-content: center; align-items: center;"><h7>'.strtoupper($query1['stage_name']).'</h7><button type="button" id="div_check'.$i.'"  title="" class="btn btn-default btn-circle btn-xl">'.$arr[$j].'
                            </button>
</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
} else 
{
echo '<div style="display: flex; flex-direction: column; justify-content: center; align-items: center;"><h7>'.strtoupper($query1['stage_name']).'</h7><button type="button" id="div_check'.$i.'"  title="" class="btn btn-white btn-circle btn-xl">'.$arr[$j].'
                            </button>
</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
}
$j++; $i++; } ?>

                                            </div>

                                        </div>
                                    </div>
                                    <!-- Row div End -->
                                </div>



                            </div>

                        </div>
                    </div>
                </div>
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <footer class="main-footer">

            <div class="col-md-12 text-center">
                2025 DPM</div>

            <div class="col-md-12 text-center">
                Designed and Developed for SI EA O AIS THA
            </div>

        </footer>

    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->


    <script src="plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Date picker jquery -->
    <script src="plugins/select2/js/select2.full.min.js"></script>
    <script src="plugins/moment/moment.min.js"></script>
    <script src="plugins/inputmask/jquery.inputmask.min.js"></script>
    <script src="plugins/daterangepicker/daterangepicker.js"></script>
    <script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
    <!-- DataTables  & Plugins -->
    <script src="plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>



    <!-- AdminLTE App -->
    <script src="dist/js/adminlte.min.js"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="dist/js/demo.js"></script>
    <script src="js/fontawesome4.js"></script>
    <script>
    $(document).ready(function() {
        $(".icon-input-btn").each(function() {
            var btnFont = $(this).find(".btn").css("font-size");
            var btnColor = $(this).find(".btn").css("color");
            $(this).find(".fa").css({
                'font-size': btnFont,
                'color': btnColor
            });
        });
    });
    </script>
    <script>
    $(function() {
        var table = $('#example1').DataTable({
            "paging": false,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "scrollY": 300,
            'language': {
                "lengthMenu": "_MENU_ per page",
                "zeroRecords": "No records found",
                "info": "Showing <b>_START_ to _END_</b> (of _TOTAL_)",
                "infoFiltered": "",
                "infoEmpty": "No records found",
                "processing": '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>'
            },
            'destroy': true,
            "ajax": {
                'type': 'POST',
                'url': 'load_stampingstage1.php',
                'data': function(data) {
                    data.product_id = <?php echo $product_id;?>;
                    data.station_id = <?php echo $station_id;?>;
                    data.barcode = '<?php echo $scanqrcode;?>';
                    data.action = '<?php echo $action;?>';
                }
            },

            "aoColumns": [{
                    mData: 'sr_no',
                    sWidth: '2%'
                },
                {
                    mData: 'check_item',
                    sWidth: '30%'
                },
                {
                    mData: 'actual_opt',
                    render: function(mdata, type, row, meta) {
                        if (row['actual_opt'] == 'Y') {
                            var checkedAttrOk = (row['actual_opt_checked'] == 1) ? 'checked' : '';
                            var checkedAttrNotOk = (row['actual_opt_checked'] == 0) ? 'checked' : '';
                            var disabled = '<?php echo $login_type == "Manufacturing" ? "disabled" : ""; ?>';
                            var remarkDisabled = (row['actual_opt_checked'] == 0) ? 'disabled' : '';
                            return '<input type="radio" class="check_req" value="1" name="actual_opt' +
                                row['sr_no'] + '" id="check_req' + row['sr_no'] +
                                '" style="accent-color: #00b300; width: 30px; height: 30px; transform: scale(0.8);" ' + checkedAttrOk + ' onchange="toggleRemarkDisabled(this, ' + row['sr_no'] + ')"> Ok <input type="radio" class="check_req" value="0" name="actual_opt' +
                                row['sr_no'] + '" id="check_req' + row['sr_no'] +
                                '" style="accent-color: #FF0000; width: 30px; height: 30px; transform: scale(0.8);" ' + checkedAttrNotOk + ' onchange="toggleRemarkDisabled(this, ' + row['sr_no'] + ')"> Not Ok';
                        } else {
                            var disabled = '<?php echo $login_type == "Manufacturing" ? "disabled" : ""; ?>';
                            return '<div style="display:none;"><input type="radio" class="check_req" value="3" name="actual_opt' +
                                row['sr_no'] + '" id="check_req' + row['sr_no'] +
                                '" checked ></div>';
                        }
                    },
                    sWidth: '13%'
                },
                {
                    mData: 'remark',
                    sWidth: '21%',
                    render: function(mdata, type, row, meta) {
                        var disabled = '<?php echo $login_type == "Manufacturing" ? "disabled" : ""; ?>';
                        var remarkDisabled = (row['actual_opt_checked'] == 1) ? 'disabled' : '';
                        return '<textarea class="form-control" name="remark_comp[]" data-sr-no="' + row['sr_no'] + '" ' + disabled + ' ' + remarkDisabled + '>' + row['remark'] + '</textarea>';
                    }
                },
                {
                    mData: 'actual_opt_manufacturing',
                    render: function(mdata, type, row, meta) {
                        if (row['actual_opt_manufacturing'] == 'Y') {
                            //count++;
                            var checkedAttrOkManufacturing = (row['actual_opt_manufacturing_checked'] == 1) ? 'checked' : '';
                            var checkedAttrNotOkManufacturing = (row['actual_opt_manufacturing_checked'] == 0) ? 'checked' : '';
                            var disabled = '<?php echo $login_type == "Manufacturing" ? "disabled" : ""; ?>';
                            return '<input type="radio" class="check_req_manuf" value="1" name="actual_opt_manufacturing' +
                                row['sr_no'] + '" id="check_req_manuf' + row['sr_no'] +
                                '" style="accent-color: #00b300; width: 30px; height: 30px; transform: scale(0.8);" ' + checkedAttrOkManufacturing + '> Ok <input type="radio" class="check_req_manuf" value="0" name="actual_opt_manufacturing' +
                                row['sr_no'] + '" id="check_req_manuf' + row['sr_no'] +
                                '" style="accent-color: #FF0000; width: 30px; height: 30px; transform: scale(0.8);" ' + checkedAttrNotOkManufacturing + ' ' + disabled + '> Not Ok';

                        } else {
                            // count--;
                            var disabled = '<?php echo $login_type == "Manufacturing" ? "disabled" : ""; ?>';
                            return '<div style="display:none;"><input type="radio" class="check_req_manuf" value="3" name="actual_opt_manufacturing' +
                                row['sr_no'] + '" id="check_req_manuf' + row['sr_no'] +
                                '" ' + disabled + ' checked ></div>';
                        }

                    },
                    sWidth: '13%'
                },
                {
                    mData: 'remark_manufacturing',
                    sWidth: '21%',
                    render: function(mdata, type, row, meta) {
                        var disabled = '<?php echo $login_type == "Manufacturing" ? "disabled" : ""; ?>';
                        return '<textarea class="form-control" name="remark_comp_manufacturing[]" ' + disabled + '>' + row['remark_manufacturing'] + '</textarea>';
                    }
                }
            ]
        });
        $('#check_submit').on('click', function() {
            var login_type = '<?php echo $login_type; ?>';
            var length = table.rows().count();
            var status = 1;

            if (login_type === 'Stamping') {
              var remarkcnt = new Array();
              $("input[name='remark[]']").each(function() {
                  if ($(this).val() && $(this).val() != '') {
                      remarkcnt.push($(this).val());
                  }
              });
              var processedValues = [];
              $('#example1 tr').each(function() {
                  var checkedValue = '""';
                  $(this).find('.check_req').each(function() {
                      if ($(this).is(':checked')) {
                          checkedValue = this.value;
                      }
                  });
                  processedValues.push(checkedValue);
              });
              var resultString = processedValues.join(',');
              if (resultString.startsWith('""')) {
                  resultString = resultString.replace(/^""\s*,\s*/, '');
              }
              checkgroup = resultString;
              var remark_compcnt = new Array();
              $("textarea[name='remark_comp[]']").each(function() {
                  if ($(this).val() && $(this).val() != '') {
                      remark_compcnt.push($(this).val());
                  } else {
                      remark_compcnt.push('""');
                  }
              });
              var checkgroupArray = checkgroup.split(',');
              for (var i = 0; i < checkgroupArray.length; i++) {
                  if (checkgroupArray[i] === '0' || checkgroupArray[i] === '""') {
                      status = 0;
                      break;
                  }
              }
              $("input[name='remark[]']").each(function() {
                  if ($(this).val() && $(this).val() != '') {
                      remarkcnt.push($(this).val());
                  } else {
                      textfill = 'NotFill';
                      return false;
                  }
              });

              var remarkcntmanuf = new Array();
              $("input[name='remark_manufacturing[]']").each(function() {
                  if ($(this).val() && $(this).val() != '') {
                      remarkcntmanuf.push($(this).val());
                  }
              });
              var processedValuesManuf = [];
              $('#example1 tr').each(function() {
                  var checkedValueManuf = '""';
                  $(this).find('.check_req_manuf').each(function() {
                      if ($(this).is(':checked')) {
                        checkedValueManuf = this.value;
                      }
                  });
                  processedValuesManuf.push(checkedValueManuf);
              });
              var resultStringManuf = processedValuesManuf.join(',');
              if (resultStringManuf.startsWith('""')) {
                  resultStringManuf = resultStringManuf.replace(/^""\s*,\s*/, '');
              }
              checkgroupManuf = resultStringManuf;

              var remark_manufacturing_compcnt = new Array();
              $("textarea[name='remark_comp_manufacturing[]']").each(function() {
                  if ($(this).val() && $(this).val() != '') {
                    remark_manufacturing_compcnt.push($(this).val());
                  } else {
                    remark_manufacturing_compcnt.push('""');
                  }
              });
              // var checkgroupManufArray = checkgroupManuf.split(',');
              // for (var i = 0; i < checkgroupManufArray.length; i++) {
              //     if (checkgroupManufArray[i] === '0') {
              //         status = 0;
              //         break;
              //     }
              // }
              // status = 0;
              $("input[name='remark_manufacturing[]']").each(function() {
                  if ($(this).val() && $(this).val() != '') {
                      remarkcntmanuf.push($(this).val());
                  } else {
                      textfill = 'NotFill';
                      return false;
                  }
              });
              
            } else if (login_type === 'Manufacturing') {
              var remarkcntmanuf = new Array();
              $("input[name='remark_manufacturing[]']").each(function() {
                  if ($(this).val() && $(this).val() != '') {
                      remarkcntmanuf.push($(this).val());
                  }
              });
              var processedValuesManuf = [];
              $('#example1 tr').each(function() {
                  var checkedValueManuf = '""';
                  $(this).find('.check_req_manuf').each(function() {
                      if ($(this).is(':checked')) {
                        checkedValueManuf = this.value;
                      }
                  });
                  processedValuesManuf.push(checkedValueManuf);
              });
              var resultStringManuf = processedValuesManuf.join(',');
              if (resultStringManuf.startsWith('""')) {
                  resultStringManuf = resultStringManuf.replace(/^""\s*,\s*/, '');
              }
              checkgroupManuf = resultStringManuf;

              var remark_manufacturing_compcnt = new Array();
              $("textarea[name='remark_comp_manufacturing[]']").each(function() {
                  if ($(this).val() && $(this).val() != '') {
                    remark_manufacturing_compcnt.push($(this).val());
                  } else {
                    remark_manufacturing_compcnt.push('""');
                  }
              });
              // var checkgroupManufArray = checkgroupManuf.split(',');
              // for (var i = 0; i < checkgroupManufArray.length; i++) {
              //     if (checkgroupManufArray[i] === '0') {
              //         status = 0;
              //         break;
              //     }
              // }
              status = 0;
              $("input[name='remark_manufacturing[]']").each(function() {
                  if ($(this).val() && $(this).val() != '') {
                      remarkcntmanuf.push($(this).val());
                  } else {
                      textfill = 'NotFill';
                      return false;
                  }
              });
            }
            
            if (status === 1) {
                var displayMsg = "Please confirm: Are you sure you want to complete this stage of stamping?";
            } else {
                var displayMsg = "Please confirm: Do you want to save this stage of stamping?";
            }
            var product_id = '<?php echo $product_id;?>';
            var station_id = '<?php echo $station_id;?>';
            var product_name = '<?php echo $product_name;?>';
            var station_name = '<?php echo $station_name;?>';
            var user_id = '<?php echo $user; ?>';
            var cust_name = ' <?php echo $pass; ?>';
            var scanqrcode = '<?php echo $scanqrcode;?>';
            var stamping_remark = '<?php echo $remark;?>';
            var action = '<?php echo $action;?>';
            var id = '<?php echo $id;?>';
            var textfill = 'Fill';
            if (login_type === 'Stamping') {
              var actual_output = checkgroup;
              var remarks = remarkcnt;
              var remarks_comp = remark_compcnt;
              var actual_output_manufacturing = checkgroupManuf;
              var remark_manufacturing = remarkcntmanuf;
              var remarks_manufacturing_comp = remark_manufacturing_compcnt;
            } else if (login_type === 'Manufacturing') {
              var actual_output_manufacturing = checkgroupManuf;
              var remark_manufacturing = remarkcntmanuf;
              var remarks_manufacturing_comp = remark_manufacturing_compcnt;
            }

            if (textfill == "NotFill") {
                alert("Please fill in all text fields");
                return false;
            } else {
                if (confirm(displayMsg)) {
                    var data = {
                        product_id: product_id,
                        station_id: station_id,
                        product_name: product_name,
                        station_name: station_name,
                        stage_id: 8,
                        user_id: user_id,
                        cust_name: cust_name,
                        stamping_remark: stamping_remark,
                        length: length,
                        scanqrcode: scanqrcode,
                        id: id,
                        action: action,
                        login_type: login_type
                    };

                    if (login_type === 'Stamping') {
                        data.actual_output = actual_output;
                        data.remarks = remarks;
                        data.remarks_comp = remarks_comp;
                        data.actual_output_manufacturing = actual_output_manufacturing;
                        data.remark_manufacturing = remark_manufacturing;
                        data.remarks_manufacturing_comp = remark_manufacturing_compcnt;
                    } else if (login_type === 'Manufacturing') {
                        data.actual_output_manufacturing = actual_output_manufacturing;
                        data.remark_manufacturing = remark_manufacturing;
                        data.remarks_manufacturing_comp = remark_manufacturing_compcnt;
                    }

                    $.ajax({
                        url: 'save_firststagestamping.php',
                        type: "POST",
                        data: data,
                        success: function(data) {
                            var x = data.success
                            if (x.saved == "SAVED" && x.complete == "COMPLETE") {
                                window.location.href = 'generatestamping.php';
                            } else if (x.error == "ERROR") {
                                window.location.href = 'checkstamptesting.php';
                            }
                        }
                    });
                }
            }
        });
    });
    </script>
    <script>
        function toggleRemarkDisabled(radio, srNo) {
            var remarkTextarea = $('textarea[name="remark_comp[]"][data-sr-no="' + srNo + '"]');
            if (radio.value === '0') {
                remarkTextarea.prop('disabled', false);
            } else {
                remarkTextarea.prop('disabled', true);
            }
        }
    $(function() {
        //Date range picker
        $('#reservationdate,#reservationdate1').datetimepicker({
            format: "DD/MM/YYYY"
        });
        //Date range picker

        var Accordion = function(el, multiple) {
            this.el = el || {};
            this.multiple = multiple || false;

            // Variables privadas
            var links = this.el.find('.link');
            var links1 = this.el.find('.link1');
            // Evento
            links.on('click', {
                el: this.el,
                multiple: this.multiple
            }, this.dropdown)
            links1.on('click', {
                el: this.el,
                multiple: this.multiple
            }, this.dropdown1)
        }

        Accordion.prototype.dropdown = function(e) {
            var $el = e.data.el;
            $this = $(this),
                $next = $this.next();

            $next.slideToggle();
            $this.parent().toggleClass('open');

            if (!e.data.multiple) {
                $el.find('.submenu').not($next).slideUp().parent().removeClass('open');
            };
        }

        Accordion.prototype.dropdown1 = function(e) {
            var $el = e.data.el;
            $this = $(this),
                $next = $this.next();

            $next.slideToggle();
            $this.parent().toggleClass('open');

            if (!e.data.multiple) {
                $el.find('.submenu1').not($next).slideUp().parent().removeClass('open');
            };
        }

        var accordion = new Accordion($('#accordion'), false);
    });



    $('.workbench_clk').on('click', function() {
        var workbench_id = $(this).attr('data-id');
        var title_id = $(this).attr('work-id');
        $('#work_id').val(workbench_id);
        $('#work_title').html(title_id);
        if (title_id == "Rework") {
            $('#work_rewark').show();
            $('#work_sop').hide();
            $('#div_breakdown').hide();
            $('#div_kitting').hide();
            $('#div_alert').hide();
            $('#div_paus').hide();
            $('#div_main_slip').hide();
            $('#div_spec_point').hide();
            $('#div_det').hide();
            $('#div_defect').hide();
            $('#div_checklist').hide();
            $('#div_skippart').hide();

        } else if (title_id == "SOP") {
            $('#work_rewark').hide();
            //$('#work_sop').show();
            $.ajax({
                url: 'test.php',
                success: function(data) {
                    var x = data.success;
                    if (x.suc == "SUCCESS") {
                        $('#myModal').modal('hide');
                    }

                }
            });

            $('#div_breakdown').hide();
            $('#div_kitting').hide();
            $('#div_alert').hide();
            $('#div_paus').hide();
            $('#div_main_slip').hide();
            $('#div_spec_point').hide();
            $('#div_det').hide();
            $('#div_defect').hide();
            $('#div_checklist').hide();
            $('#div_skippart').hide();

        } else if (title_id == "Breakdown") {
            $('#work_rewark').hide();
            $('#work_sop').hide();
            $('#div_breakdown').show();
            $('#div_kitting').hide();
            $('#div_alert').hide();
            $('#div_paus').hide();
            $('#div_main_slip').hide();
            $('#div_spec_point').hide();
            $('#div_det').hide();
            $('#div_defect').hide();
            $('#div_checklist').hide();
            $('#div_skippart').hide();



        } else if (title_id == "Kitting List") {
            $('#work_rewark').hide();
            $('#work_sop').hide();
            $('#div_breakdown').hide();
            $('#div_kitting').show();
            $('#div_alert').hide();
            $('#div_paus').hide();
            $('#div_main_slip').hide();
            $('#div_spec_point').hide();
            $('#div_det').hide();
            $('#div_defect').hide();
            $('#div_checklist').hide();
            $('#div_skippart').hide();



        } else if (title_id == "Missing material") {
            $('#work_rewark').hide();
            $('#work_sop').hide();
            $('#div_breakdown').hide();
            $('#div_kitting').hide();
            $('#div_alert').show();
            $('#div_paus').hide();
            $('#div_main_slip').hide();
            $('#div_spec_point').hide();
            $('#div_det').hide();
            $('#div_defect').hide();
            $('#div_checklist').hide();
            $('#div_skippart').hide();


        } else if (title_id == "Missing Infrastructure") {
            $('#work_rewark').hide();
            $('#work_sop').hide();
            $('#div_breakdown').hide();
            $('#div_kitting').hide();
            $('#div_alert').hide();
            $('#div_paus').hide();
            $('#div_main_slip').hide();
            $('#div_spec_point').hide();
            $('#div_det').hide();
            $('#div_defect').hide();
            $('#div_checklist').show();
            $('#div_skippart').hide();


        } else if (title_id == "Safety") {
            $('#work_rewark').hide();
            $('#work_sop').hide();
            $('#div_breakdown').hide();
            $('#div_kitting').hide();
            $('#div_alert').hide();
            $('#div_paus').hide();
            $('#div_main_slip').hide();
            $('#div_spec_point').hide();
            $('#div_det').hide();
            $('#div_defect').show();
            $('#div_checklist').hide();
            $('#div_skippart').hide();

        } else if (title_id == "Details") {
            $('#work_rewark').hide();
            $('#work_sop').hide();
            $('#div_breakdown').hide();
            $('#div_kitting').hide();
            $('#div_alert').hide();
            $('#div_paus').hide();
            $('#div_main_slip').hide();
            $('#div_spec_point').hide();
            $('#div_det').show();
            $('#div_defect').hide();
            $('#div_checklist').hide();
            $('#div_skippart').hide();


        } else if (title_id == "Special Points") {
            $('#work_rewark').hide();
            $('#work_sop').hide();
            $('#div_breakdown').hide();
            $('#div_kitting').hide();
            $('#div_alert').hide();
            $('#div_paus').hide();
            $('#div_main_slip').hide();
            $('#div_spec_point').show();
            $('#div_det').hide();
            $('#div_defect').hide();
            $('#div_checklist').hide();
            $('#div_skippart').hide();



        } else if (title_id == "Maintainance Slip") {
            $('#work_rewark').hide();
            $('#work_sop').hide();
            $('#div_breakdown').hide();
            $('#div_kitting').hide();
            $('#div_alert').hide();
            $('#div_paus').hide();
            $('#div_main_slip').show();
            $('#div_spec_point').hide();
            $('#div_det').hide();
            $('#div_defect').hide();
            $('#div_checklist').hide();
            $('#div_skippart').hide();



        } else if (title_id == "Pause") {
            $('#work_rewark').hide();
            $('#work_sop').hide();
            $('#div_breakdown').hide();
            $('#div_kitting').hide();
            $('#div_alert').hide();
            $('#div_paus').show();
            $('#div_main_slip').hide();
            $('#div_spec_point').hide();
            $('#div_det').hide();
            $('#div_defect').hide();
            $('#div_checklist').hide();
            $('#div_skippart').hide();


        } else if (title_id == "Skip Part") {
            $('#work_rewark').hide();
            $('#work_sop').hide();
            $('#div_breakdown').hide();
            $('#div_kitting').hide();
            $('#div_alert').hide();
            $('#div_paus').hide();
            $('#div_main_slip').hide();
            $('#div_spec_point').hide();
            $('#div_det').hide();
            $('#div_defect').hide();
            $('#div_checklist').hide();
            $('#div_skippart').show();



        }


        // else

    });

    $('#save_workbench').on('click', function() {
        //alert('Hello');
        var product_id = '<?php echo $product_id;?>';
        var station_id = '<?php echo $station_id;?>';
        var work_id = $('#work_id').val();
        var scanqrcode = '<?php echo $scanqrcode;?>';
        //alert(''+work_id);
        if (work_id == '1') {
            var work_type = $('#w_type').val();
            var start_date = $('#start_date_rework').val();
            var end_date = $('#end_date_rework').val();
            var rework_remark = $('#rework_remark').val();
            if (work_type == "11") {
                alert("Please Select Work Type");
                return false;
            } else {
                //alert('Hello');
                $.ajax({
                    url: 'save_stagewise_workbenchdata.php',
                    type: "POST",
                    data: {
                        product_id: product_id,
                        station_id: station_id,
                        work_id: work_id,
                        stage_id: 8,
                        work_type: work_type,
                        start_date: start_date,
                        end_date: end_date,
                        scanqrcode: scanqrcode,
                        rework_remark: rework_remark


                    },
                    success: function(response) {
                        if (response.trim() == "SAVED") {
                            alert('Record is Saved');

                            $('#w_type').val('');
                            $('#rework_remark').val('');
                            $('#end_date_rework').val('');
                            $('#myModal').modal('hide');

                        } else if (response.trim() == "ERROR") {
                            alert('Record is not Saved');
                            return false;

                        }

                    }
                });



            }


        }

        //Safety_type
        else if (work_id == '2') {
            var break_type = $('#Safety_type').val();
            var start_date = $('#start_date_safety').val();
            var end_date = $('#end_date_safety').val();
            var break_rem = $('#safety_rem_rem').val();
            if (break_type == "11") {
                alert("Please Select Safety Type");
                return false;
            } else {
                $.ajax({
                    url: 'save_stagewise_workbenchdata.php',
                    type: "POST",
                    data: {
                        product_id: product_id,
                        station_id: station_id,
                        work_id: work_id,
                        stage_id: 8,
                        work_type: break_type,
                        start_date: start_date,
                        end_date: end_date,
                        rework_remark: break_rem,
                        scanqrcode: scanqrcode
                    },
                    success: function(response) {
                        if (response.trim() == "SAVED") {
                            alert('Record is Saved');

                            $('#Safety_type').val('');
                            $('#safety_rem_rem').val('');
                            $('#end_date_safety').val('');
                            $('#myModal').modal('hide');

                        } else if (response.trim() == "ERROR") {
                            alert('Record is not Saved');
                            return false;

                        }

                    }
                });



            }


        } else if (work_id == '4') {
            var work_type = $('#missing_type').val();
            var start_date = $('#start_date_Missing').val();
            var end_date = $('#end_date_Missing').val();
            var rework_remark = $('#missing_rem').val();
            if (work_type == "11") {
                alert("Please Select Missing Material Type");
                return false;
            } else {
                //alert('Hello');
                $.ajax({
                    url: 'save_stagewise_workbenchdata.php',
                    type: "POST",
                    data: {
                        product_id: product_id,
                        station_id: station_id,
                        work_id: work_id,
                        stage_id: 8,
                        work_type: work_type,
                        start_date: start_date,
                        end_date: end_date,
                        scanqrcode: scanqrcode,
                        rework_remark: rework_remark


                    },
                    success: function(response) {
                        if (response.trim() == "SAVED") {
                            alert('Record is Saved');

                            $('#w_type').val('');
                            $('#missing_rem').val('');
                            $('#end_date_Missing').val('');
                            $('#myModal').modal('hide');

                        } else if (response.trim() == "ERROR") {
                            alert('Record is not Saved');
                            return false;

                        }

                    }
                });
            }


        } else if (work_id == '1002') {
            var work_type = $('#infra_type').val();
            var start_date = $('#start_date_Infra').val();
            var end_date = $('#end_date_Missing').val();
            var rework_remark = $('#Infra_remark').val();
            if (work_type == "11") {
                alert("Please Select Infrastructure Type");
                return false;
            } else {
                //alert('Hello');
                $.ajax({
                    url: 'save_stagewise_workbenchdata.php',
                    type: "POST",
                    data: {
                        product_id: product_id,
                        station_id: station_id,
                        work_id: work_id,
                        stage_id: 8,
                        work_type: work_type,
                        start_date: start_date,
                        end_date: end_date,
                        scanqrcode: scanqrcode,
                        rework_remark: rework_remark


                    },
                    success: function(response) {
                        if (response.trim() == "SAVED") {
                            alert('Record is Saved');

                            $('#w_type').val('');
                            $('#Infra_remark').val('');
                            $('#end_date_Missing').val('');
                            $('#myModal').modal('hide');

                        } else if (response.trim() == "ERROR") {
                            alert('Record is not Saved');
                            return false;

                        }

                    }
                });


            }


        }


        /*else if(work_id == '2'){

            var formData = new FormData();
            var totalFiles = document.getElementById("sop_file").files.length;
            for (var i = 0; i < totalFiles; i++) {
                var file = document.getElementById("sop_file").files[i];

                formData.append("file[]", file);
            }
            formData.append("stage_id",1);
            formData.append("product_id",product_id);
            formData.append("work_id",work_id);
            formData.append("station_id",station_id);

            //alert(''+formData);
            $.ajax({
                type: "POST",
                url: 'sopfile_upload.php',
                data: formData,
               dataType: 'json',
                 contentType: false,
                processData: false,
                success: function (data) {
                  var x = data.success;
                  
              //alert(''+x);
                  if(x.res == "UPLOAD"){
                    alert('Data is inserted and file is uploaded!!');
                    $('#myModal').modal('hide');
                  }else{
                    alert('Data is not inserted and file is not uploaded!!');
                          return false;
                  }
                
                
                }
               
            });
          }*/
        else if (work_id == '3') {
            var break_type = $('#break_type').val();
            var start_date = $('#start_date_break').val();
            var end_date = $('#end_date_break').val();
            var break_rem = $('#break_rem').val();
            if (break_type == "11") {
                alert("Please Select Break Type");
                return false;
            } else {
                $.ajax({
                    url: 'save_stagewise_workbenchdata.php',
                    type: "POST",
                    data: {
                        product_id: product_id,
                        station_id: station_id,
                        work_id: work_id,
                        stage_id: 8,
                        work_type: break_type,
                        start_date: start_date,
                        end_date: end_date,
                        scanqrcode: scanqrcode,
                        rework_remark: break_rem
                    },
                    success: function(response) {
                        if (response.trim() == "SAVED") {
                            alert('Record is Saved');

                            $('#break_type').val('');
                            $('#break_rem').val('');
                            $('#end_date_break').val('');
                            $('#myModal').modal('hide');

                        } else if (response.trim() == "ERROR") {
                            alert('Record is not Saved');
                            return false;

                        }

                    }
                });



            }


        } else if (work_id == '4' || work_id == '6' || work_id == '7' || work_id == '8' || work_id == '9' ||
            work_id == '11' || work_id == '12') {
            if (work_id == '4') {
                var kitting_remark = $('#kitting_remark').val();
            } else if (work_id == '6') {
                var kitting_remark = $('#check_remark').val();
            } else if (work_id == '7') {
                var kitting_remark = $('#defect_remark').val();
            } else if (work_id == '8') {
                var kitting_remark = $('#det_remark').val();
            } else if (work_id == '9') {
                var kitting_remark = $('#spec_point_remark').val();
            } else if (work_id == '11') {
                var kitting_remark = $('#pause_remark').val();
            } else if (work_id == '12') {
                var kitting_remark = $('#skip_remark').val();
            }

            //if(kitting_remark == ""){
            //  alert("Please Enter Remark");
            //  return false;
            //}
            else {
                $.ajax({
                    url: 'save_stagewise_workbenchdata.php',
                    type: "POST",
                    data: {
                        product_id: product_id,
                        station_id: station_id,
                        work_id: work_id,
                        stage_id: 8,
                        rework_remark: kitting_remark
                    },
                    success: function(response) {
                        if (response.trim() == "SAVED") {
                            alert('Record is Saved');
                            if (work_id == "4") {
                                $('#kitting_remark').val('');
                            } else if (work_id == '6') {

                                $('#check_remark').val('');
                            } else if (work_id == '7') {

                                $('#defect_remark').val('');
                            } else if (work_id == '8') {

                                $('#det_remark').val('');
                            } else if (work_id == '9') {

                                $('#spec_point_remark').val('');
                            } else if (work_id == '11') {
                                $('#pause_remark').val('');
                            } else if (work_id == '12') {
                                $('#skip_remark').val('');
                            }

                            $('#myModal').modal('hide');

                        } else if (response.trim() == "ERROR") {
                            alert('Record is not Saved');
                            return false;

                        }

                    }
                });



            }

        }


    });

    $("#view_sop_files").click(function() {
        var work_id = '2';
        var stage_id = '8';
        window.open("view_sop_files_list.php?work_id=" + work_id + "&stage_id=" + stage_id, '_blank');

    });
    $("#view_rework").click(function() {
        var work_id = '1';
        var stage_id = '8';
        window.open("view_rework_list.php?work_id=" + work_id + "&stage_id=" + stage_id, '_blank');

    });
    </script>
</body>

</html>