<?php
session_start();
$user=$_SESSION['username'];
$pass=$_SESSION['pass'];
include('shared/CommonManager.php');
$sql = "SELECT * FROM tbl_stage";
$query = DbManager::fetchPDOQueryData('spectra_db', $sql)["data"];
$sql1 = "SELECT * FROM tbl_product";
$query1 = DbManager::fetchPDOQueryData('spectra_db', $sql1)["data"];
$sqlstation = "SELECT * FROM tbl_station";
$querystation = DbManager::fetchPDOQueryData('spectra_db', $sqlstation)["data"];


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

    .modal {
        display: none;
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7); /* Dark overlay */
        backdrop-filter: blur(10px); /* Blur the background */
    }

    .modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 30px;
        border-radius: 8px;
        width: 1000px;
    }

    .popup-content p {
        margin-bottom: 1px;
    }

    .popup-content {
        font-family: Arial, sans-serif;
    }

    .popup-content p {
        display: flex;
        align-items: baseline;
        margin: 0.1em 0;
    }

    .popup-content strong {
        min-width: 220px; /* Set the width according to the longest label */
        text-align: left;
        margin-right: 1px;
    }
    
    .close {
        font-size: 30px;
        color: #333;
        cursor: pointer;
        position: absolute;
        top: 10px;
        right: 15px;
    }
    </style>
</head>

<body class="hold-transition sidebar-mini sidebar-collapse"
    style="background:url('img/bg.jpg') left no-repeat;	background-size:cover;">
    <!-- Site wrapper -->
    <div class="wrapper">
        <!-- Navbar for pay bill login -->
        <nav class="main-header navbar navbar-expand navbar-white  navbar-light">

            <!-- Navbar for gpf login  use the bottom line of code for GPF login-->
            <!-- <nav class="main-header navbar navbar-expand navbar-white navbar-gpf navbar-light"> -->

            <!-- Navbar for gpf login  use the bottom line of code for income tax login-->
            <!-- <nav class="main-header navbar navbar-expand navbar-white navbar-income navbar-light"> -->

            <!-- Navbar for gpf login  use the bottom line of code for Employee login-->
            <!-- <nav class="main-header navbar navbar-expand navbar-white navbar-light"> -->

            <!-- Left navbar links -->
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
                <!-- Messages Dropdown Menu -->

                <!-- Notifications Dropdown Menu -->

                <!-- <li class="nav-item d-none d-sm-inline-block">
        <span class="nav-link"><i class="nav-icon fas fa-user"></i> <?php // echo $pass; ?></span>
      </li>  -->
                <!-- <li class="nav-item">
        <img src="img/emb.gif" alt="" height="55" />
      </li>-->
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="Admindash.php" class="brand-link">
                <img src="img/fav_logo3.png" alt="SiemensLogo" class="brand-image img-thumbnail elevation-1">
                <span class="brand-text font-weight-light"><br />
                  <small style="font-size:0.6em;"></small>
                </span>
            </a>
            <div class="sidebar">
                <ul id="accordion" class="accordion nav nav-pills nav-sidebar flex-column">
                    <!-- <li><a href="Admindash.php"><div class="link"><i class="fa fa-dashboard"></i><span>Dashboard</span></div></a></li> -->
                    <li>
                      <a href="barcodegenerate.php">
                        <div class="link"><i class="fa fa-hourglass-start"></i><span>Start </span></div>
                      </a>
                    </li>
                    <li>
                      <a href="logout.php">
                        <div class="link"> <i class="fa fa-sign-out nav-icon"></i><span>Logout</span></div>
                      </a>            
                    </li>
                </ul>
            </div>
        </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header mb-2">
            <div class="container-fluid">

                <div class="pagehead">
                    <div class="row">
                        <div class=" col-md-4 col-sm-4">
                            <h5>Welcome to Digital Production Management</h5>
                        </div>
                        <div class="col-sm-8">
                            <div class="tab float-sm-right">
                                <!-- <a href="Admindash.php"  class="tablinks" ><i class="fas fa-home"></i> </a>
  <a href="#"  class="tablinks paybill" ><i class="fa fa-money"></i> </a>
  	<a href="#"  class="tablinks gpf" ><i class="fa fa-database"></i> </a>
    <a href="#"  class="tablinks income" ><i class="fa fa-edit"></i></a>
	  <a href="#" class="tablinks" ><i class="fa fa-line-chart"></i> </a> -->
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
                                        <h3><i class="fa fa-address-card"></i> Start Page</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body formarea smpadding">
                                    <h4> <span class="badge badge-danger" id="span_barcode"
                                            style="display:none;"></span></h4>
                                    <p style="color:red">*Note : 00038317 3AH11052AF501KW2 3006869535/000110
                                        800005226771 </p>
                                    <!-- 132.186.78.116 -->
                                    <br></br>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Scan QR Code:</label>
                                        </div>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" name="scanqrcode" id="scanqrcode"
                                                oninput="displayInput()" autofocus>
                                        </div>
                                        <div class="col-md-2"> <input type="button" class="btn btn-success" value="GO"
                                                id="scan_id"> </div>
                                    </div>

                                    <!--  Start here Updated Split Code by Akshay With Split Script at bottom -->

                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Serial No:</label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" id="outputBox1" placeholder="Serial No" readonly>

                                        </div>
                                        <div class="col-md-2">
                                            <label>Sales Order No:</label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" id="outputBox4" placeholder="Sales Order No" readonly>
                                        </div>

                                        <div class="col-md-2">
                                            <label>Item No:</label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" id="outputBox3" placeholder="Item No" readonly>

                                        </div>
                                        <div class="col-md-2">
                                            <label>Production No:</label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" id="outputBox2" placeholder="Production No" readonly>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <!-- End Here New Split Code Display -->

                                    </div>


                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Station Name:</label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="hidden" class="form-control" id="station_id" name="station_id"
                                                readonly>
                                            <input type="text" class="form-control" id="station_name"
                                                name="station_name" readonly>

                                            <!-- <select class="form-control" id="station_id" name="station_id" aria-label="Default select example">
  <option value="">Select Station Name</option>
  <?php foreach ($querystation as $res) { 
     echo "<option value='".$res['station_id']."'>".$res,['station_name']."</option>";
 } ?>
</select>
<div id="error_product" style="display:none;color:red">* Please Select Product</div>
--->
                                        </div>
                                        <div class="col-md-2">
                                            <label>Product Name:</label>
                                        </div>
                                        <div class="col-md-4">

                                            <input type="text" class="form-control" id="product_name"
                                                name="product_name" readonly>
                                            <input type="hidden" class="form-control" id="product_id" name="product_id"
                                                readonly>

                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>MLFB No:</label>
                                        </div>
                                        <div class="col-md-4">
                                            <textarea class="form-control" id="mlfb_num" name="mlfb_num"
                                                readonly></textarea>
                                        </div>
                                        <div class="col-md-2">
                                            <label>Machine Name:</label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" id="machine_name"
                                                name="machine_name" readonly>
                                            <div id="error_panel" style="display:none;color:red">* Enter Panel No.</div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Subassembly Yes/No:</label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" id="subassem_req"
                                                name="subassem_req" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label>Rating:</label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" id="rating" name="rating" readonly>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Width:</label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" id="width" name="width" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label>Trolley Type Of Siemens:</label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" id="trolley_type" name="trolley_type" readonly>
                                        </div>
                                    </div>
                                    <div class="row">
                                       <div class="col-md-2">
                                            <label>Trolley Required For Refair:</label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" id="trolley_refair" name="trolley_refair" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label>Addon:</label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" id="addon" name="addon" readonly>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>VI Type:</label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" id="vi_type" name="vi_type" readonly>
                                        </div>
                                        <div class="col-md-2">
                                            <label>Additional Note:</label>
                                        </div>
                                        <div class="col-md-4">
                                            <textarea class="form-control" id="additional_note" name="additional_note" readonly></textarea>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Remarks:</label>
                                        </div>
                                        <div class="col-md-4">
                                            <textarea class="form-control" id="remark" name="remark"></textarea>
                                        </div>
                                    </div>
                                    <br>
                                    <div id="previousStationsContainer" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label>Previous Station Details:</label>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered" id="previousStationsTable">
                                                        <thead>
                                                            <tr>
                                                                <th>Station Name</th>
                                                                <th>User</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <!-- Previous station data will be populated here -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>    
                                    </br>
                                    <div style="text-align:center;">
                                        <button class="btn btn-primary" id="openModalBtnBOM" style="background-color: #fff;color: #099;">PAW BOM</button>
                                        <div id="myModalBOM" class="modal">
                                            <div class="modal-content">
                                                <span class="close">&times;</span>
                                            </div>
                                        </div>

                                        <button class="btn btn-primary" id="openModalBtnMLFB" style="background-color: #fff;color: #099;">MLFB Description</button>
                                        <div id="myModalMLFB" class="modal">
                                            <div class="modal-content">
                                                <span class="close">&times;</span>
                                                <h2>MLFB Description Modal</h2>
                                                <p>With a cool background blur effect.</p>
                                            </div>
                                        </div>

                                        
                                        <button class="btn btn-primary" id="openModalBtnC1C2" style="background-color: #fff;color: #099;">C1 & C2 Diagram</button>
                                        <div id="myModalC1C2" class="modal">
                                            <div class="modal-content">
                                                <span class="close">&times;</span>
                                                <h2>C1 & C2 Diagram</h2>
                                                <div style="text-align: right; margin-bottom: 10px;">
                                                    <button class="btn btn-primary btn-sm" id="backButton" style="display: none;">
                                                        <i class="fa fa-arrow-left"></i> Back
                                                    </button>
                                                </div>
                                                <div id="fileList"></div>
                                            </div>
                                        </div>

                                    </div>
                                    <div>
                                        <input type="button" value="Start" id="start_step" class="btn btn-primary">
                                    </div>
                                    </br>
                                    <!-- /.card-body -->
                                    <!--<div class="card-footer">
                Footer
              </div>-->
                                    <!-- /.card-footer-->
                                </div>




                                <!-- /.card -->

                                <!--  <div class="row">
				<div class="col-md-12">
					<div class="blockhead">
					<h3><i class="fa fa-address-card"></i>  Details</h3>
					</div>
				</div>
			   </div>
			  <div class="card">
			   <div class="card-body formarea smpadding">
               <div class="display_table">
               <table id="example1" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Sr. No.</th>
                    <th>Product Name</th>
                    <th>Barcode</th>
                    <th>View</th>
                    
                  </tr>
                  </thead>
                 
                  <tfoot>
                  <tr>
                  <th>Sr. No.</th>
                    <th>Product Name</th>
                    <th>Barcode</th>
                    <th>View</th>
                   
                  </tr>
                  </tfoot>
                </table>
               </div>

               </div>
               </div>
  
			

 Row div End -->
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

        $('#openModalBtnMLFB').click(function() {
            $.ajax({
                    url: 'getmlfbdescription.php',
                    type: 'POST',
                    data: {
                        scanqrcode: $('#scanqrcode').val()
                    },
                    success: function(response) {
                        // Display the MLFB description in the modal
                        $('#myModalMLFB .modal-content').html(response + '<span class="close">&times;</span>');
                        $('#myModalMLFB').show();

                        // Add click event handler for the close button
                        $('#myModalMLFB .close').click(function() {
                            $('#myModalMLFB').hide();
                        });
                    },
                    error: function() {
                        alert('Error fetching MLFB description.');
                    }
                });
            });
        // Close MLFB Description modal
        $('#myModalMLFB').on('click', '.close', function() {
            $('#myModalMLFB').hide();
        });


        $('#openModalBtnBOM').click(function() {
            var salesOrderNo = document.getElementById("outputBox4").value;
            var ItemNo = document.getElementById("outputBox3").value;

            if (salesOrderNo.trim().length !== 10 || isNaN(salesOrderNo)) {
                alert("Please Scan QR Code for sales order number to fetch BOM.");
                return;
            }

            var pawBOMUrl = '/materialsearch/mat.php?project=' + salesOrderNo + '&panel=' + ItemNo + '&source_type=';
            window.open(pawBOMUrl, '_blank', 'width=auto,height=800');
        });

        // Close PAW BOM modal
        $('#myModalBOM').on('click', '.close', function() {
            $('#myModalBOM').hide();
        });

        
        var currentDirectoryPath = '';
        // Open the C1/C2 Diagram modal
        $('#openModalBtnC1C2').click(function() {
            // Call the function to list files and directories
            var salesOrderNo = $('#outputBox4').val();
            if (salesOrderNo.trim().length !== 10 || isNaN(salesOrderNo)) {
                alert("Please Scan QR Code for sales order number to fetch C1/C2 Diagram.");
                return;
            }
            listFilesAndDirectories('\\\\inmumtha111dat\\M-Tool\\M-Tool\\Documents\\NXAIR\\Orders\\' + salesOrderNo + '\\05_SingleLineDiagrams');
            $('#myModalC1C2').show();
        });

        // Close the C1/C2 Diagram modal
        $('#myModalC1C2').on('click', '.close', function() {
            $('#myModalC1C2').hide();
        });

        // Function to list files and directories
        function listFilesAndDirectories(directory_path) {
            currentDirectoryPath = directory_path;
            $.ajax({
                url: 'get_files.php',
                type: 'POST',
                data: { directory_path: directory_path },
                success: function(response) {
                    $('#fileList').html(response);
                    var salesOrderNo = $('#outputBox4').val();
                    // Show or hide the back button based on the directory path
                    if (directory_path !== '\\\\inmumtha111dat\\M-Tool\\M-Tool\\Documents\\NXAIR\\Orders\\' + salesOrderNo + '\\05_SingleLineDiagrams') {
                        $('#backButton').show();
                    } else {
                        $('#backButton').hide();
                    }

                    // Add click event handler for file and directory links
                    $('#fileList a').click(function(e) {
                        e.preventDefault();
                        var path = $(this).attr('href');
                        if (path.indexOf('?directory=') !== -1) {
                            // Directory link clicked
                            var directoryPath = decodeURIComponent(path.split('?directory=')[1]);
                            listFilesAndDirectories(directoryPath);
                        } else if (path.indexOf('?file=') !== -1) {
                            // File link clicked
                            var filePath = decodeURIComponent(path.split('?file=')[1].replace(/\+/g, ' '));
                            openFile(filePath);
                        }
                    });
                },
                error: function() {
                    $('#fileList').html('Error: Could not list files and directories.');
                }
            });
        }

        // Function to open the file
        function openFile(file_path) {
            $.ajax({
                url: 'get_file.php',
                type: 'POST',
                data: { file_path: encodeURIComponent(file_path) },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    // Create a download link
                    var downloadLink = document.createElement('a');
                    downloadLink.href = window.URL.createObjectURL(response);
                    downloadLink.download = file_path.split('\\').pop();
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    document.body.removeChild(downloadLink);
                },
                error: function() {
                    alert('Error: Could not download the file.');
                }
            });
        }

        // Function to go to the parent directory
        $('#backButton').click(function() {
            var parentDirectoryPath = currentDirectoryPath.substring(0, currentDirectoryPath.lastIndexOf('\\'));
            listFilesAndDirectories(parentDirectoryPath);
        });

    });
    </script>
    <script>
    $(function() {

        $('#example1').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,

            'language': {
                "lengthMenu": "_MENU_ per page",
                "zeroRecords": "No records found",
                "info": "Showing <b>_START_ to _END_</b> (of _TOTAL_)",
                "infoFiltered": "",
                "infoEmpty": "No records found",
                "processing": '<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>'
            },
            'destroy': true,
            "sAjaxSource": "load_barcodedetails.php",
            "aoColumns": [{
                    mData: 'sr_no'
                },
                {
                    mData: 'product_name'
                },
                {
                    mData: 'barcode'
                },
                {
                    mData: 'view'
                }
            ]

        });
    });
    /* $(document).ready(function(){
// code to get all records from table via select box
$("#station_id").change(function() {
var station_id = $(this).find(":selected").val();
var dataString = 'station_id='+ station_id;
//alert(society_id);
$.ajax({
type: "POST",
url: 'get-stationproductdet.php',
dataType: 'json',
data: dataString,
cache: false,
success: function(data){
var x = data.success;
//alert(x);
//var data=data.success;
$("#product_name").val(x.product_name);
$("#product_id").val(x.product_id);
//alert(x.floors);
$("#mlfb_num").val(x.mlfb_num);
$("#machine_name").val(x.Machine_name);
if(x.subassembly_req == 'Y'){
   $('#subassem_req').val('Yes');
}
else
{
  $('#subassem_req').val('No');
}
} 
});
});
});


$(function () {
var ajaxRequestMade = false;
$('#scanqrcode').keyup(function() {

  var scanqrcode =$('#scanqrcode').val();
var dataString = 'scanqrcode='+ scanqrcode;
if(scanqrcode.length >= 53 && !ajaxRequestMade){
$.ajax({
type: "POST",
url: 'get-stationproductdet.php',
dataType: 'json',
data: dataString,
cache: false,
success: function(data){
var x = data.success;
//alert(x);
//var data=data.success;
$("#product_name").val(x.product_name);
$("#product_id").val(x.product_id);
//alert(x.floors);
$("#mlfb_num").val(x.mlfb_num);
$("#machine_name").val(x.Machine_name);
if(x.subassembly_req == 'Y'){
   $('#subassem_req').val('Yes');
}
else
{
  $('#subassem_req').val('No');
}
} 
});
}
});


});*/
    $('#scan_id').click(function() {
        //alert("Hello");
        var scanqrcode = $('#scanqrcode').val();
        if (scanqrcode.length >= 52) {
            var remaining = scanqrcode.substring(8, scanqrcode.length - 29);            
            if (remaining.length > 65) {
                alert("Error: Scanned barcode exceeds the limit. Please scan only once.");
                // Clear all input fields
                document.getElementById("scanqrcode").value = "";
                document.getElementById("outputBox1").value = "";
                document.getElementById("outputBox2").value = "";
                document.getElementById("outputBox3").value = "";
                document.getElementById("outputBox4").value = "";
                document.getElementById("product_name").value = "";
                document.getElementById("product_id").value = "";
                document.getElementById("mlfb_num").value = "";
                document.getElementById("machine_name").value = "";
                document.getElementById("station_name").value = "";
                document.getElementById("station_id").value = "";
                document.getElementById("rating").value = "";
                document.getElementById("width").value = "";
                document.getElementById("trolley_type").value = "";
                document.getElementById("trolley_refair").value = "";
                document.getElementById("addon").value = "";
                document.getElementById("vi_type").value = "";
                document.getElementById("additional_note").value = "";
                document.getElementById("subassem_req").value = "";
                document.getElementById("scanqrcode").focus();
                $('#previousStationsContainer').hide();
                return false;
            }

            $.ajax({
                url: 'get-stationproductdet.php',
                type: 'post',
                dataType: 'json',
                data: {
                    scanqrcode: scanqrcode
                },
                success: function(data) {
                    //alert(data.success);
                    if (data.notexist == "NOTEXIST") {
                        alert("Invalid Barcode");

                        $("#product_name").val('');
                        $("#product_id").val('');
                        //alert(x.floors);
                        $("#mlfb_num").val('');
                        $("#machine_name").val('');
                        $("#station_name").val('');
                        $("#station_id").val('');
                        $("#rating").val('');
                        $("#width").val('');
                        $("#trolley_type").val('');
                        $("#trolley_refair").val('');
                        $("#addon").val('');
                        $("#vi_type").val('');
                        $("#additional_note").val('');
                        $('#scanqrcode').val('');
                        $('#previousStationsContainer').hide();
                        return false;
                    } else {
                        var x = data.success;
                        // $('#scanqrcode').val();
                        $("#product_name").val(x.product_name);
                        $("#product_id").val(x.product_id);
                        //alert(x.floors);
                        $("#mlfb_num").val(x.mlfb_num);
                        $("#machine_name").val(x.machine_name);
                        $("#station_name").val(x.station_name);
                        $("#station_id").val(x.station_id);
                        if (x.subassembly_req == 'Y') {
                            $('#subassem_req').val('Yes');
                        } else {
                            $('#subassem_req').val('No');
                        }
                        $("#rating").val(x.rating);
                        $("#width").val(x.width);
                        $("#trolley_type").val(x.trolley_type);
                        $("#trolley_refair").val(x.trolley_refair);
                        $("#addon").val(x.addon);
                        $("#vi_type").val(x.vi_type);
                        $("#additional_note").val(x.additional_note);
                        //$('#scanqrcode').disabled(true);
                        //$('#scanqrcode').attr(‘disabled’,’disabled’);
                        // Update previous stations table
                        if (x.previous_stations && x.previous_stations.length > 0) {
                            var tableBody = '';
                            x.previous_stations.forEach(function(station) {
                                tableBody += `<tr>
                                    <td>${station.station_name}</td>
                                    <td>${station.user_id}</td>
                                </tr>`;
                            });
                            $('#previousStationsTable tbody').html(tableBody);
                            // Show the container with animation
                            $('#previousStationsContainer').slideDown('fast');
                        } else {
                            $('#previousStationsTable tbody').html('<tr><td colspan="4" class="text-center">No previous station data available</td></tr>');
                            $('#previousStationsContainer').slideDown('fast');
                        }
                    }


                }
            });
        }

    });
    $('#start_step').on('click', function() {
        var station_id = $('#station_id').val();
        var station_name = $('#station_name').val();
        var product_id = $('#product_id').val();
        var product_name = $('#product_name').val();
        var scanqrcode = $('#scanqrcode').val();

        var mlfb_num = $('#mlfb_num').val();
        var machine_name = $('#machine_name').val();
        var subassem_req = $('#subassem_req').val();
        if (station_id != '' && product_id != '') {
            //if (confirm('Are you sure you want to start for testing?')) {
            if (station_id != '' && product_id != '') {

                $.ajax({
                    type: "POST",
                    url: 'checktestingstage.php',
                    dataType: 'json',
                    data: {
                        station_id: station_id,
                        product_id: product_id,
                        subassem_req: subassem_req,
                        station_name: station_name,
                        product_name: product_name,
                        mlfb_num: mlfb_num,
                        machine_name: machine_name,
                        scanqrcode: scanqrcode
                    },
                    cache: false,


                    success: function(data) {
                        var x = data.success;
                        if (subassem_req == 'Yes' && x.res == 'NOAVAILABLE') {
                            alert('This Product is not Available for Daily Worksheet');
                            return false;

                        }
                        if (x.cnt == 0 && subassem_req == 'Yes' && x.res == "REQASSEM") {
                            alert('Subassembly is not Completed for other station please do first');
                            return false;
                        }
                        //alert (x.res);
                        if (x.cnt == 3 && x.res == "SUBASSDONE") {
                            alert('Subassembly is Completed for this Station');
                            return false;
                        }
                        if (x.cnt == 0 && x.res == "No_TestingStage") {
                            alert('This is not Testing Station');
                            return false;
                        } else if (x.cnt == 0 && subassem_req == 'Yes' && x.res == 'SUBPRECHECK') {
                            window.location.href = "checksubassembly.php";

                        } else if (x.res == 'NA_ASSEMPREC') {
                            alert('Subassembly is pending for other Station');
                            return false;

                        } else if (x.res == 'NA_Testing') {
                            alert('Assembly is pending for other Station');
                            return false;

                        }
                        /*else if(x.cnt_min_first < x.cnt_total_first && subassem_req == 'Yes' && x.res == 'SUBPRECHECK'){
                          window.location.href = "checksubassemblyview.php";
                        }

                        */
                        else if (x.cnt == 1 && subassem_req == 'Yes' && x.res == 'SUBASSEMBLY') {

                            window.location.href = "checksubassembly2stage.php";
                        }
                        /*else if(x.cnt_min_sec < x.cnt_total_sec && subassem_req == 'Yes' && x.res == 'SUBPRECHECK'){
                          window.location.href = "checksubassembly2stageview.php";
                        }*/
                        else if (x.cnt == 2 && subassem_req == 'Yes' && x.res == 'SUBLOCATION') {

                            window.location.href = "checklocation.php";
                        } else if (x.cnt == x.cnt_tot && subassem_req == 'Yes' && x.res ==
                            'ASSEMBLY') {

                            window.location.href = "checkassembly.php";
                        } else if (x.cnt == 0 && x.res == 'ASSEMBLYWS') {
                            window.location.href = "checkassembly.php";
                        }
                        /*else if(x.cnt_min_loc < x.cnt_total_loc && subassem_req == 'Yes' && x.res == 'PRECHECKASSEM'){

                        window.location.href = "checkassemblystgeview.php";
                        }*/
                        else if (x.cnt == 4 && subassem_req == 'Yes' && x.res ==
                            'ASSEMBLY_STAGE2') {

                            window.location.href = "checkassemblystge2.php";
                        } else if (x.cnt == 2 && x.res == 'ASSEMBLY_STAGE2WS') {
                            window.location.href = "checkassemblystge2.php";
                        }
                        /*else if(x.cnt_min_loc < x.cnt_total_loc && subassem_req == 'Yes' && x.res == 'ASSEMBLY'){

                        window.location.href = "checkassemblystge2view.php";
                        }*/
                        else if (x.cnt == 0 && subassem_req == 'Yes' && x.res == 'PRECHECKTEST') {
                            window.location.href = "checkprechecktesting.php";
                        } else if (x.cnt == 0 && x.res == 'PRECHECKTESTWS') {
                            window.location.href = "checkprechecktesting.php";
                        }
                        /*else if(x.cnt_min_test < x.cnt_total_test && subassem_req == 'Yes' && x.res == 'PRECHECKTEST'){

                        window.location.href = "checkprechecktestingview.php";
                        }*/
                        else if (x.cnt == 0 && subassem_req == 'Yes' && x.res == 'TESTPARAMETER') {

                            window.location.href = "checktestingstage2.php";
                        } else if (x.res == 'TESTINGSTAGE2WS') {

                            window.location.href = "checktestingstage2.php";
                        } else if (x.res == 'EXIST') {
                            alert("This barcode is already scaned");
                        }

                        /* if(x.cnt == 0 && subassem_req == 'Yes'){
    //window.location.href = "checksubassembly.php?station_name="+station_name+"&product_name="+product_name;
   
    window.location.href = "checksubassembly.php";
   //$().redirect('checksubassembly.php', {'station_id': 'station_id', 'product_id': 'product_id'});
 }
 else if(x.cnt > 0 && subassem_req == 'Yes' && x.stage_id=='3' ){
    //window.location.href = "checksubassembly.php?station_name="+station_name+"&product_name="+product_name;
   //alert("")
    window.location.href = "checkassembly.php";
   //$().redirect('checksubassembly.php', {'station_id': 'station_id', 'product_id': 'product_id'});
 }
 else if(x.cnt > 0 && subassem_req == 'Yes' && x.stage_id=='4' ){
    //window.location.href = "checksubassembly.php?station_name="+station_name+"&product_name="+product_name;
   //alert("")
    window.location.href = "checkassemblystage2.php";
   //$().redirect('checksubassembly.php', {'station_id': 'station_id', 'product_id': 'product_id'});
 }
 else if(x.cnt > 0 && subassem_req == 'Yes' && x.stage_id=='5' ){
    //window.location.href = "checksubassembly.php?station_name="+station_name+"&product_name="+product_name;
   //alert("")
    window.location.href = "checkprechecktesting.php";
   //$().redirect('checksubassembly.php', {'station_id': 'station_id', 'product_id': 'product_id'});
 }
 else if(x.cnt > 0 && subassem_req == 'Yes' && x.stage_id=='6' ){
    //window.location.href = "checksubassembly.php?station_name="+station_name+"&product_name="+product_name;
   //alert("")
    window.location.href = "checktestingstge2.php";
   //$().redirect('checksubassembly.php', {'station_id': 'station_id', 'product_id': 'product_id'});
 }
 else if(x.cnt == 0 && subassem_req == 'No'){
    //window.location.href = "checksubassembly.php?station_name="+station_name+"&product_name="+product_name;
   //alert("")
    window.location.href = "checkassembly.php";
   //$().redirect('checksubassembly.php', {'station_id': 'station_id', 'product_id': 'product_id'});
 }
 else if(x.stage_id=='1')
 {
  window.location.href = "checksubassembly2stage.php";
 }
 else if(x.stage_id=='2')
 {
  window.location.href = "checklocation.php";
 }

}
*/
                    }
                });


            }

        } else {
            alert('The station name or machine name appears to be incorrect. Please verify and try again.');
            return false;
        }


    });
    </script>
    <script>
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
    </script>
    <!-- new script for splt code by -->
    <script>
        function displayInput() {
            const input = document.getElementById("scanqrcode").value;
            // If length is okay, proceed with normal operation
            document.getElementById("outputBox1").value = input.substring(0, 8);
            const last12 = input.substring(input.length - 12);
            document.getElementById("outputBox2").value = last12;

            // 6 characters before the last 12
            const beforeLast12 = input.substring(input.length - 18, input.length - 12);
            document.getElementById("outputBox3").value = beforeLast12;
            
            // 10 characters before the last 6
            const beforeLast10 = input.substring(input.length - 29, input.length - 19);
            document.getElementById("outputBox4").value = beforeLast10;
        }
    </script>
</body>
</html>