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
$sql_transaction = "SELECT * FROM tbl_transactions WHERE stage_id=:stage_id AND status=:status AND tr_id=:tr_id GROUP BY barcode ORDER BY tr_id DESC";
$transaction_details = DbManager::fetchPDOQueryData('spectra_db', $sql_transaction, [":stage_id" => "8", ":status" => "0", ":tr_id" => $_REQUEST['id']])["data"][0];
$barcode = $transaction_details['barcode'];
$serial_no = substr($barcode, 0, 8);
$production_no = substr($barcode, -12);
$item_no = substr($barcode, -18, 6);
$sales_order_no = substr($barcode, -29, 10);
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
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
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    html, body {
        height: 100%;
        width: 100%;
        overflow-x: hidden;
        font-size: 13px;
    }

    body.hold-transition {
        background-attachment: fixed !important;
    }

    .wrapper {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .main-header {
        flex-shrink: 0;
        min-height: 50px;
    }

    .main-sidebar {
        flex-shrink: 0;
        position: fixed;
        left: 0;
        top: 50px;
        bottom: 0;
        width: 57px;
        overflow-y: auto;
    }

    .content-wrapper {
        flex: 1;
        margin-left: 57px;
        overflow-y: auto;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
    }

    .content-header {
        flex-shrink: 0;
        padding: 3px 5px;
        min-height: auto;
    }

    .content {
        flex: 1;
        padding: 3px;
        overflow-y: auto;
    }

    .main-footer {
        flex-shrink: 0;
        margin-left: 57px;
        padding: 2px 5px;
        font-size: 10px;
        line-height: 1.2;
    }

    .container-fluid {
        padding-left: 3px;
        padding-right: 3px;
    }

    .card {
        margin-bottom: 0;
        border-radius: 2px;
    }

    .card-body {
        padding: 6px;
    }

    .card-body.formarea.smpadding {
        padding: 5px;
    }

    .row {
        margin-right: -2px;
        margin-left: -2px;
        margin-bottom: 2px;
    }

    .row:last-child {
        margin-bottom: 0;
    }

    .col-md-2 {
        padding-right: 2px;
        padding-left: 2px;
        flex: 0 0 16.666667%;
        max-width: 16.666667%;
    }

    .col-md-8 {
        padding-right: 2px;
        padding-left: 2px;
        flex: 0 0 66.666667%;
        max-width: 66.666667%;
    }

    .col-md-12 {
        padding-right: 2px;
        padding-left: 2px;
    }

    label {
        font-size: 11px;
        font-weight: 600;
        margin-bottom: 1px;
        display: block;
        line-height: 1.1;
        color: #333;
    }

    .form-control, textarea.form-control {
        font-size: 11px;
        padding: 2px 4px;
        height: auto;
        min-height: 26px;
        line-height: 1.3;
        border: 1px solid #ced4da;
        background-color: #f5f5f5;
    }

    textarea.form-control {
        min-height: 38px;
        resize: vertical;
    }

    .blockhead h3 {
        font-size: 13px;
        margin: 1px 0;
        line-height: 1.1;
        padding: 2px 0;
    }

    .blockhead {
        padding: 2px 0;
        border-bottom: 1px solid #ddd;
    }

    .pagehead h5 {
        font-size: 12px;
        margin: 1px 0;
        line-height: 1.1;
        font-weight: 600;
    }

    p {
        margin-bottom: 1px;
        font-size: 10px;
        line-height: 1.1;
    }

    h4 {
        font-size: 12px;
        margin: 1px 0;
        line-height: 1.1;
    }

    .btn {
        font-size: 10px;
        padding: 3px 7px;
        line-height: 1.2;
        border-radius: 2px;
        display: inline-block;
        vertical-align: middle;
        height: 26px;
        border: 1px solid transparent;
        cursor: pointer;
        text-align: center;
    }

    .btn-success {
        padding: 3px 9px;
        height: 26px;
    }

    .btn-primary {
        margin: 0px 1px;
        display: inline-block;
        vertical-align: middle;
        height: 26px;
    }

    .badge {
        font-size: 9px;
        padding: 1px 3px;
    }

    .navbar {
        padding: 0.2rem 0.3rem;
        min-height: 50px;
    }

    .navbar-nav .nav-link {
        padding: 0.2rem 0.3rem;
        font-size: 10px;
    }

    .sidebar-dark-primary .nav-pills .nav-link {
        padding: 0.2rem 0.3rem;
        font-size: 10px;
    }

    .brand-link {
        padding: 0.2rem 0.2rem;
        min-height: auto;
    }

    .brand-image {
        height: 24px;
        width: 24px;
    }

    .brand-text {
        font-size: 9px;
    }

    #stampingDataContainer {
        margin-top: 1px;
    }

    #stampingDataContainer .row {
        margin-bottom: 2px;
    }

    #stampingDataContainer label {
        font-size: 11px;
        margin-bottom: 1px;
        font-weight: 600;
        color: #333;
    }

    #stampingDataContainer .form-control {
        font-size: 11px;
        padding: 2px 4px;
        min-height: 26px;
    }

    .stamping-section {
        margin-top: 3px;
        padding: 3px;
        border: 1px solid #ddd;
        border-radius: 2px;
        background-color: #fafafa;
    }

    .stamping-section-title {
        font-size: 12px;
        font-weight: 600;
        color: #333;
        margin-bottom: 2px;
        padding-bottom: 2px;
        border-bottom: 1px solid #ddd;
    }

    .modal {
        display: none;
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(10px);
        z-index: 1000;
    }

    .modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 15px;
        border-radius: 4px;
        width: 85%;
        max-width: 900px;
        max-height: 85vh;
        overflow-y: auto;
    }

    .close {
        font-size: 20px;
        color: #333;
        cursor: pointer;
        position: absolute;
        top: 5px;
        right: 10px;
    }

    .button-row {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 2px;
        flex-wrap: nowrap;
        padding: 1px 0;
        margin: 1px 0;
        width: 100%;
    }

    .button-row .btn {
        margin: 0 1px;
        flex-shrink: 0;
        padding: 3px 7px;
        line-height: 1.2;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .button-row button {
        margin: 0 1px;
        flex-shrink: 0;
        padding: 3px 7px;
        line-height: 1.2;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        font-size: 10px;
        border-radius: 2px;
    }

    .framecontent {
        padding: 0;
    }

    input[type="text"], input[type="hidden"], textarea {
        width: 100%;
    }

    .pagehead {
        padding: 2px 0;
    }

    .blank-col {
        padding-right: 2px;
        padding-left: 2px;
        flex: 0 0 16.666667%;
        max-width: 16.666667%;
    }

    @media (max-width: 1200px) {
        .col-md-2 {
            flex: 0 0 16.666667%;
            max-width: 16.666667%;
        }
    }

    @media (max-width: 992px) {
        .col-md-2 {
            flex: 0 0 25%;
            max-width: 25%;
        }
    }
    </style>
</head>

<body class="hold-transition sidebar-mini sidebar-collapse" style="background:url('img/bg.jpg') left no-repeat; background-size:cover;">
    <!-- Site wrapper -->
    <div class="wrapper">
        <!-- Navbar for pay bill login -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
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
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="Stampingdashboard.php" class="brand-link">
                <img src="img/fav_logo3.png" alt="SiemensLogo" class="brand-image img-thumbnail elevation-1">
                <span class="brand-text font-weight-light"><small style="font-size:0.6em;"></small></span>
            </a>
            <div class="sidebar">
                <ul id="accordion" class="accordion nav nav-pills nav-sidebar flex-column">
                    <li>
                        <a href="generatestamping.php">
                            <div class="link"><i class="fa fa-hourglass-start"></i><span>Start </span></div>
                        </a>
                    </li>
                    <li>
                        <a href="stampingdetails.php">
                            <div class="link"><i class="fas fa-layer-group"></i><span>List </span></div>
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
            <section class="content-header">
                <div class="container-fluid">
                    <div class="pagehead">
                        <h5><?php echo $welcome_msg; ?></h5>
                    </div>
                </div>
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
                                        <p style="color:red;">*Note : 00038317 3AH11052AF501KW2 3006869535/000110 800005226771</p><br>

                                        <div class="row">
                                            <div class="col-md-2"><label>Scan QR Code:</label></div>
                                            <div class="col-md-8">
                                                <input type="hidden" class="form-control" id="tr_id" name="tr_id" value="<?php echo $_REQUEST['id']; ?>">
                                                <input type="text" class="form-control" name="scanqrcode" id="scanqrcode" oninput="displayInput()" value="<?php echo $barcode; ?>" autofocus>
                                            </div>
                                            <div class="col-md-2"><input type="button" class="btn btn-success" value="GO" id="scan_id"></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-2"><label>Serial No:</label></div>
                                            <div class="col-md-2"><input type="text" id="outputBox1" placeholder="Serial No" value="<?php echo $serial_no; ?>" readonly></div>
                                            <div class="col-md-2"><label>Sales Order No:</label></div>
                                            <div class="col-md-2"><input type="text" id="outputBox4" placeholder="Sales Order No" value="<?php echo $sales_order_no; ?>" readonly></div>
                                            <div class="col-md-2"><label>Item No:</label></div>
                                            <div class="col-md-2"><input type="text" id="outputBox3" placeholder="Item No" value="<?php echo $item_no; ?>" readonly></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-2"><label>Production No:</label></div>
                                            <div class="col-md-2"><input type="text" id="outputBox2" placeholder="Production No" value="<?php echo $production_no; ?>" readonly></div>
                                            <div class="col-md-2"><label>Station Name:</label></div>
                                            <div class="col-md-2"><input type="hidden" class="form-control" id="station_id" name="station_id" readonly><input type="text" class="form-control" id="station_name" name="station_name" placeholder="Stamping" readonly></div>
                                            <div class="col-md-2"><label>Product Name:</label></div>
                                            <div class="col-md-2"><input type="text" class="form-control" id="product_name" name="product_name" readonly><input type="hidden" class="form-control" id="product_id" name="product_id" readonly></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-2"><label>MLFB No:</label></div>
                                            <div class="col-md-2"><textarea class="form-control" id="mlfb_num" name="mlfb_num" readonly></textarea></div>
                                            <div class="col-md-2"><label>Subassembly:</label></div>
                                            <div class="col-md-2"><input type="text" class="form-control" id="subassem_req" name="subassem_req" readonly></div>
                                            <div class="col-md-2"><label>Rating:</label></div>
                                            <div class="col-md-2"><input type="text" class="form-control" id="rating" name="rating" readonly></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-2"><label>Width:</label></div>
                                            <div class="col-md-2"><input type="text" class="form-control" id="width" name="width" readonly></div>
                                            <div class="col-md-2"><label>Trolley Type:</label></div>
                                            <div class="col-md-2"><input type="text" class="form-control" id="trolley_type" name="trolley_type" readonly></div>
                                            <div class="col-md-2"><label>Trolley Refair:</label></div>
                                            <div class="col-md-2"><input type="text" class="form-control" id="trolley_refair" name="trolley_refair" readonly></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-2"><label>Addon:</label></div>
                                            <div class="col-md-2"><input type="text" class="form-control" id="addon" name="addon" readonly></div>
                                            <div class="col-md-2"><label>VI Type:</label></div>
                                            <div class="col-md-2"><input type="text" class="form-control" id="vi_type" name="vi_type" readonly></div>
                                            <div class="col-md-2"><label>Additional Note:</label></div>
                                            <div class="col-md-2"><textarea class="form-control" id="additional_note" name="additional_note" readonly></textarea></div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-2"><label>Remarks:</label></div>
                                            <div class="col-md-2"><textarea class="form-control" id="remark" name="remark"><?php echo $transaction_details['remarks']; ?></textarea></div>
                                        </div>

                                        <!-- Stamping Data Section -->
                                        <div id="stampingDataContainer"></div>

                                        <div class="button-row">
                                            <button class="btn btn-primary" id="openModalBtnBOM" style="background-color: #fff;color: #099;border: 1px solid #099;">PAW BOM</button>
                                            <button class="btn btn-primary" id="openModalBtnMLFB" style="background-color: #fff;color: #099;border: 1px solid #099;">MLFB Desc</button>
                                            <button class="btn btn-primary" id="openModalBtnC1C2" style="background-color: #fff;color: #099;border: 1px solid #099;">C1 & C2</button>
                                            <button class="btn btn-primary" id="start_step" style="background-color: #007bff;color: white;border: 1px solid #007bff;">Start</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <div class="col-md-12 text-center">2025 DPM | Designed for SI EA O AIS THA</div>
        </footer>
    </div>

    <!-- Modals -->
    <div id="myModalBOM" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
        </div>
    </div>

    <div id="myModalMLFB" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>MLFB Description</h2>
        </div>
    </div>

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
    // Display sequence (using DB column names)
    var displaySequence = [
        'ACT_A',
        'mV DROP LIMIT',
        'R_mV',
        'Y_mV',
        'B_mV',
        'B BARCODE',
        'Y BARCODE',
        'R BARCODE',
        'R Stroke',
        'Y Stroke',
        'B Stroke',
        'Roller Gap',
        'Damping Gap',
        'Latch Gap'
    ];

    // Fields that need 3 decimal places formatting
    var decimalFields = ['R_mV', 'Y_mV', 'B_mV'];

    $(document).ready(function() {
        var scanqrcode = $('#scanqrcode').val();
        if (scanqrcode.trim() !== '') {
            handleScanQRCode();
        }

        $('#openModalBtnMLFB').click(function() {
            $.ajax({
                url: 'getmlfbdescription.php',
                type: 'POST',
                data: { scanqrcode: $('#scanqrcode').val() },
                success: function(response) {
                    $('#myModalMLFB .modal-content').html(response + '<span class="close">&times;</span>');
                    $('#myModalMLFB').show();
                    $('#myModalMLFB .close').click(function() {
                        $('#myModalMLFB').hide();
                    });
                },
                error: function() {
                    alert('Error fetching MLFB description.');
                }
            });
        });
        
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

        $('#myModalBOM').on('click', '.close', function() {
            $('#myModalBOM').hide();
        });

        var currentDirectoryPath = '';
        $('#openModalBtnC1C2').click(function() {
            var salesOrderNo = $('#outputBox4').val();
            if (salesOrderNo.trim().length !== 10 || isNaN(salesOrderNo)) {
                alert("Please Scan QR Code for sales order number to fetch C1/C2 Diagram.");
                return;
            }
            listFilesAndDirectories('\\\\inmumtha111dat\\M-Tool\\M-Tool\\Documents\\NXAIR\\Orders\\' + salesOrderNo + '\\05_SingleLineDiagrams');
            $('#myModalC1C2').show();
        });

        $('#myModalC1C2').on('click', '.close', function() {
            $('#myModalC1C2').hide();
        });

        function listFilesAndDirectories(directory_path) {
            currentDirectoryPath = directory_path;
            $.ajax({
                url: 'get_files.php',
                type: 'POST',
                data: { directory_path: directory_path },
                success: function(response) {
                    $('#fileList').html(response);
                    var salesOrderNo = $('#outputBox4').val();
                    if (directory_path !== '\\\\inmumtha111dat\\M-Tool\\M-Tool\\Documents\\NXAIR\\Orders\\' + salesOrderNo + '\\05_SingleLineDiagrams') {
                        $('#backButton').show();
                    } else {
                        $('#backButton').hide();
                    }
                    $('#fileList a').click(function(e) {
                        e.preventDefault();
                        var path = $(this).attr('href');
                        if (path.indexOf('?directory=') !== -1) {
                            var directoryPath = decodeURIComponent(path.split('?directory=')[1]);
                            listFilesAndDirectories(directoryPath);
                        } else if (path.indexOf('?file=') !== -1) {
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

        function openFile(file_path) {
            $.ajax({
                url: 'get_file.php',
                type: 'POST',
                data: { file_path: encodeURIComponent(file_path) },
                xhrFields: { responseType: 'blob' },
                success: function(response) {
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

        $('#backButton').click(function() {
            var parentDirectoryPath = currentDirectoryPath.substring(0, currentDirectoryPath.lastIndexOf('\\'));
            listFilesAndDirectories(parentDirectoryPath);
        });
    });

    $('#scan_id').click(function() {
        handleScanQRCode();
    });

    function handleScanQRCode() {
        var scanqrcode = $('#scanqrcode').val();
        if (scanqrcode.length >= 52) {
            var remaining = scanqrcode.substring(8, scanqrcode.length - 29);            
            if (remaining.length > 65) {
                alert("Error: Scanned barcode exceeds the limit. Please scan only once.");
                clearAllFields();
                document.getElementById("scanqrcode").focus();
                return false;
            }
            $.ajax({
                url: 'get-stationproductdet.php',
                type: 'post',
                dataType: 'json',
                data: {
                    scanqrcode: scanqrcode,
                    action: 'stamping'
                },
                success: function(data) {
                    if (data.notexist == "NOTEXIST") {
                        alert("Invalid Barcode");
                        clearAllFields();
                        return false;
                    } else {
                        var x = data.success;
                        $("#product_name").val(x.product_name);
                        $("#product_id").val(x.product_id);
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
                        
                        if (x.stamping_data) {
                            displayStampingData(x.stamping_data);
                        } else {
                            clearStampingData();
                        }
                    }
                }
            });
        }
    }

    function clearAllFields() {
        document.getElementById("scanqrcode").value = "";
        document.getElementById("outputBox1").value = "";
        document.getElementById("outputBox2").value = "";
        document.getElementById("outputBox3").value = "";
        document.getElementById("outputBox4").value = "";
        document.getElementById("product_name").value = "";
        document.getElementById("product_id").value = "";
        document.getElementById("mlfb_num").value = "";
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
        clearStampingData();
    }

    function formatDecimalValue(value, dbColumn) {
        // Check if this field needs decimal formatting
        if (decimalFields.indexOf(dbColumn) !== -1 && value !== '-' && !isNaN(value)) {
            return parseFloat(value).toFixed(3);
        }
        return value;
    }

    function displayStampingData(stampingData) {
        var container = $('#stampingDataContainer');
        var html = '<div class="stamping-section">';
        html += '<div class="stamping-section-title">mV Drop Parameters</div>';
        
        // Create ordered array based on displaySequence
        var orderedData = [];
        for (var i = 0; i < displaySequence.length; i++) {
            var dbColumn = displaySequence[i];
            if (stampingData.hasOwnProperty(dbColumn)) {
                var fieldData = stampingData[dbColumn];
                // Handle both object and string formats
                var displayName = (typeof fieldData === 'object') ? fieldData.displayName : dbColumn;
                var value = (typeof fieldData === 'object') ? fieldData.value : fieldData;
                
                // Format decimal values
                value = formatDecimalValue(value, dbColumn);
                
                orderedData.push({
                    displayName: displayName,
                    value: value,
                    dbColumn: dbColumn
                });
            }
        }

        // Display fields in 3-column layout with blank column after mV DROP LIMIT
        var colCount = 0;
        for (var i = 0; i < orderedData.length; i++) {
            if (colCount === 0) {
                html += '<div class="row">';
            }

            html += '<div class="col-md-2">';
            html += '<label>' + orderedData[i].displayName + ':</label>';
            html += '</div>';
            html += '<div class="col-md-2">';
            html += '<input type="text" class="form-control" value="' + orderedData[i].value + '" readonly>';
            html += '</div>';

            colCount++;

            // Add blank column after mV DROP LIMIT (which is at index 1)
            if (orderedData[i].dbColumn === 'mV DROP LIMIT') {
                html += '<div class="blank-col"></div>';
                colCount++;
            }

            // Close row after 3 columns
            if (colCount === 3) {
                html += '</div>';
                colCount = 0;
            }
        }

        // Close remaining row if needed
        if (colCount > 0) {
            html += '</div>';
        }

        html += '</div>';
        container.html(html);
    }

    function clearStampingData() {
        var container = $('#stampingDataContainer');
        if (container.length > 0) {
            container.empty();
        }
    }

    $('#start_step').on('click', function() {
        var tr_id = $('#tr_id').val();
        var station_id = $('#station_id').val();
        var station_name = $('#station_name').val();
        var product_id = $('#product_id').val();
        var product_name = $('#product_name').val();
        var scanqrcode = $('#scanqrcode').val();
        var mlfb_num = $('#mlfb_num').val();
        var machine_name = $('#machine_name').val();
        var subassem_req = $('#subassem_req').val();
        var remark = $('#remark').val();
        var action = "stamping";
        
        if (product_id != '') {
            $.ajax({
                type: "POST",
                url: 'checktestingstage.php',
                dataType: 'json',
                data: {
                    tr_id: tr_id,
                    station_id: station_id,
                    product_id: product_id,
                    subassem_req: subassem_req,
                    station_name: station_name,
                    product_name: product_name,
                    mlfb_num: mlfb_num,
                    machine_name: machine_name,
                    remark: remark,
                    scanqrcode: scanqrcode,
                    action: action
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
                    } else if (x.cnt == 1 && subassem_req == 'Yes' && x.res == 'SUBASSEMBLY') {
                        window.location.href = "checksubassembly2stage.php";
                    } else if (x.cnt == 2 && subassem_req == 'Yes' && x.res == 'SUBLOCATION') {
                        window.location.href = "checklocation.php";
                    } else if (x.cnt == x.cnt_tot && subassem_req == 'Yes' && x.res == 'ASSEMBLY') {
                        window.location.href = "checkassembly.php";
                    } else if (x.cnt == 0 && x.res == 'ASSEMBLYWS') {
                        window.location.href = "checkassembly.php";
                    } else if (x.cnt == 4 && subassem_req == 'Yes' && x.res == 'ASSEMBLY_STAGE2') {
                        window.location.href = "checkassemblystge2.php";
                    } else if (x.cnt == 2 && x.res == 'ASSEMBLY_STAGE2WS') {
                        window.location.href = "checkassemblystge2.php";
                    } else if (x.cnt == 0 && subassem_req == 'Yes' && x.res == 'PRECHECKTEST') {
                        window.location.href = "checkprechecktesting.php";
                    } else if (x.cnt == 0 && x.res == 'PRECHECKTESTWS') {
                        window.location.href = "checkprechecktesting.php";
                    } else if (x.type == 'add' && x.res == 'STAMPCHECK') {
                        window.location.href = "checkstamptesting.php";
                    } else if (x.type == 'edit' && x.res == 'STAMPCHECK') {
                        window.location.href = "checkstamptesting.php?id="+tr_id;
                    } else if (x.cnt == 0 && subassem_req == 'Yes' && x.res == 'TESTPARAMETER') {
                        window.location.href = "checktestingstage2.php";
                    } else if (x.res == 'TESTINGSTAGE2WS') {
                        window.location.href = "checktestingstage2.php";
                    } else if (x.res == 'EXIST') {
                        alert("This barcode is already scanned");
                    } else if (x.res == 'ALREADYSCANNED') {
                        alert("This barcode is already stamped");
                    } else if (x.type == 'edit' && x.res == 'STAMPINGCHECK') {
                        window.location.href = "checkstamptesting.php?id="+x.tr_id+"&status=final";
                    } else if (x.type == 'edit' && x.res == 'MANUFACTURERCHECK') {
                        window.location.href = "checkstamptesting.php?id="+x.tr_id;
                    } else if (x.res == 'NOTAUTHORIZED') {
                        alert("This barcode should be first scanned and attended by a stamper");
                    }
                }
            });
        } else {
            alert('Select Station first');
            return false;
        }
    });

    $(function() {
        $('#reservationdate,#reservationdate1').datetimepicker({
            format: "DD/MM/YYYY"
        });
        var Accordion = function(el, multiple) {
            this.el = el || {};
            this.multiple = multiple || false;
            var links = this.el.find('.link');
            var links1 = this.el.find('.link1');
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

    function displayInput() {
        const input = document.getElementById("scanqrcode").value;
        document.getElementById("outputBox1").value = input.substring(0, 8);
        const last12 = input.substring(input.length - 12);
        document.getElementById("outputBox2").value = last12;
        const beforeLast12 = input.substring(input.length - 18, input.length - 12);
        document.getElementById("outputBox3").value = beforeLast12;
        const beforeLast10 = input.substring(input.length - 29, input.length - 19);
        document.getElementById("outputBox4").value = beforeLast10;
    }
    </script>
</body>
</html>