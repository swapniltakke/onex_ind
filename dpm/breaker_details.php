<!DOCTYPE html>
<html>
<?php
include_once 'core/index.php';
SharedManager::checkAuthToModule(14);
?>
<link href="../css/semantic.min.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="../css/dataTables.semanticui.min.css">
<script src="../js/jquery.min.js"></script>
<link href="../css/main.css?13" rel="stylesheet"/>
<?php include_once 'shared/headerStyles.php' ?>
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
<body>

<div id="wrapper">
    <?php $activePage = '/dpm/breaker_details.php'; ?>
    <?php include_once "shared/sidebar.php"; ?>
    <?php include_once 'breaker/updateBreaker.php'; ?>
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">
                    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i
                                class="fa fa-bars"></i> </a>
                </div>
                <ul class="nav navbar-top-links navbar-right">
                    <li>
                        <h2 style="text-align: left;">Breaker Details</h2>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="row" id="mainRow">
            <div class="col-lg-12">
                <div class="ibox mb-0" id="ibox_open_list_page">
                    <div id="headersegment">
                        <div class="ibox-content text-center" style="padding-bottom:15px; display: flex;justify-content: space-between;">
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
                        </div>
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
                                                <th>Group</th>
                                                <th>CDD Date</th>
                                                <th>Plan Month</th>
                                                <th>Sales Order No.</th>
                                                <th>Item No.</th>
                                                <th>Client</th>
                                                <th>MLFB</th>
                                                <th>Rating</th>
                                                <th>Product Name</th>
                                                <th>Width</th>
                                                <th>Trolley Type Of Siemens</th>
                                                <th>Trolley Required For Refair</th>
                                                <th>Total Quantity</th>
                                                <th>Production Order Quantity</th>
                                                <th>Addon</th>
                                                <th>Serial No.</th>
                                                <th>PTD No.</th>
                                                <th>Production Order No.</th>
                                                <th>VI Type</th>
                                                <th>C1 Date</th>
                                                <th>CIA Date</th>
                                                <th>Remark</th>
                                                <th>Created Date</th>
                                                <th>Current Status</th>
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
        <?php $footer_display = 'Breaker Details';
        include_once '../assemblynotes/shared/footer.php'; ?>
    </div>
</div>
<!-- Mainly scripts -->
<?php include_once '../assemblynotes/shared/headerSemanticScripts.php' ?>
<script src="shared/shared.js"></script>
<script src="breaker/updateBreakerModal.js?<?= time() ?>"></script>
<script src="breaker/allBreaker.js?<?php echo rand(); ?>"></script>
</body>
</html>