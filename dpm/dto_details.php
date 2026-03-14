<!DOCTYPE html>
<html>
<?php
include_once 'core/index.php';
SharedManager::checkAuthToModule(17);
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'database';
$activePage = '/dpm/dto_details.php';
?>
<link href="../css/semantic.min.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="../css/dataTables.semanticui.min.css">
<script src="../js/jquery.min.js"></script>
<link href="../css/main.css?13" rel="stylesheet"/>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css" />
<?php include_once 'shared/dto_headerStyles.php' ?>
<?php include_once '../assemblynotes/shared/headerScripts.php' ?>

<style>
    select.form-control:not([size]):not([multiple]) {
        height: unset !important;
        font-size: 1rem;
    }

    #textAreaUpdateNote{
        overflow: hidden;
        height: 12rem;
        font-size: 1rem;
    }

    #date_filter {
        background-color: #00646E;
        border-color: #00646E;
        color: #FFFFFF;
    }

    #planned_month_filter {
        background-color: #00AF8E;
        border-color: #00AF8E;
        color: #FFFFFF;
    }
    
</style>
<?php include_once 'shared/dto_headerStyles.php' ?>
<?php include_once '../assemblynotes/shared/headerScripts.php' ?>
<body>

<div id="wrapper">
    <?php $activePage = '/dpm/dto_details.php'; ?>
    <?php require_once $_SERVER["DOCUMENT_ROOT"]."/dpm/shared/dto_sidebar.php"; ?>
    <?php include_once 'dto/updateDto.php'; ?>
    <?php include_once 'dto/updateLayout.php'; ?>
    <?php include_once 'dto/updateData.php'; ?>
    <?php include_once 'dto/updateOffer.php'; ?>
    <?php include_once 'dto/updateDocs.php'; ?>
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">
                    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i
                                class="fa fa-bars"></i> </a>
                </div>
                <ul class="nav navbar-top-links navbar-right">
                    <li>
                        <h2 style="text-align: left;">DTO Database</h2>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="row" id="mainRow">
            <div class="col-lg-12">
                <div class="ibox mb-0" id="ibox_open_list_page">
                    <div id="headersegment">
                        <!-- <div class="ibox-content text-center" style="padding-bottom:15px; display: flex;justify-content: space-between;">
                            <div class="input-daterange d-flex" id="datepicker"
                                 style="width: unset !important;">
                                <div id="reportrange" style="">
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span></span> <i class="fa fa-caret-down"></i>
                                </div> 
                                 <button class="btn ml-3" id='date_filter' onclick="filterAllListByDate()" type="button"><i
                                            class="fa fa-check"></i>&nbsp;Date Filter
                                </button> 
                                 <button class="btn ml-3" id='planned_month_filter' onclick="filterAllListByPlannedMonth()" type="button"><i
                                            class="fa fa-check"></i>&nbsp;Planned Month
                                </button> 
                            </div>
                            <div style="float: left;">
                                <div class="row horizontal-align">
                                     <label data-toggle="tooltip" data-placement="top" title="Production Order Quantity Count"
                                           class="btn btn-primary ml-3"><i class="fa fa-circle"></i><span
                                            class="ml-2" id="span_production_order_quantity_count"></span>
                                    </label> 
                                </div>
                            </div>
                        </div> -->
                        <div id="detailsegment" style="display: none">
                            <div class="ui inverted dimmer" id="mai_spinner_page">
                                <div class="ui loader"></div>
                            </div>
                            <div class="one column center aligned padded ui grid">
                                <div class="row" style="height:100%;margin-top:2%;">
                                    <div class="column" id="controlpanel">
                                        <table id='table_open_items'
                                               class="ui celled very small compact responsive table dataTable no-footer scrollable-table">
                                            <thead class="scrollable-thead">
                                            <tr>
                                                <th>Action</th>
                                                <th>Sr. No.</th>
                                                <!-- <th>Stage Name</th> -->
                                                <th>Product Name</th>
                                                <th>IAC Ratings</th>
                                                <th>Document Number</th>
                                                <th>Short Description</th>
                                                <th>Wound CTs</th>
                                                <th>Window CTs</th>
                                                <th>Cables/Bus Duct</th>
                                                <th>Cable Core</th>
                                                <th>Cable/Bus Entry</th>
                                                <th>Rated Voltage</th>
                                                <th>Rated Short Circuit</th>
                                                <th>Rated Current</th>
                                                <th>Width</th>
                                                <th>Rear Box Depth</th>
                                                <th>Feeder Material</th>
                                                <th>Released By</th>
                                                <th>Client Name</th>
                                                <th>Sale Order No</th>
                                                <th>Drawing No</th>
                                                <th>Earthing Switch</th>
                                                <th>PT-DOVT/FIX</th>
                                                <th>NXTOOL Selection</th>
                                                <!-- <th>Layout</th> -->
                                                <th>ADD ON</th>
                                                <th>Solenoid Interlocking</th>
                                                <th>Limit Switch</th>
                                                <th>Meshwire Assembly</th>
                                                <th>Lamp on Rear Cover</th>
                                                <th>Gland plate</th>
                                                <th>Rear Cover</th>
                                                <th>Stage Name</th>
                                                <th>Offer and Layout</th>
                                            </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php $footer_display = 'DTO Details';
        include_once '../assemblynotes/shared/footer.php'; ?>
    </div>
</div>
<!-- Mainly scripts -->
<?php include_once '../assemblynotes/shared/headerSemanticScripts.php' ?>
<script src="shared/shared.js"></script>
<script src="dto/updateDtoModal.js?<?= time() ?>"></script>
<script src="dto/allDto.js?<?php echo rand(); ?>"></script>
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
</body>
</html>
