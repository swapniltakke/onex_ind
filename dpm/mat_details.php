<!DOCTYPE html>
<html>
<?php
include_once 'core/index.php';
SharedManager::checkAuthToModule(17);
// echo SharedManager::getUser()["Name"] . ' ' . SharedManager::getUser()["Surname"];
// exit;
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

    .material-type-filter {
        margin: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 4px;
    }

    #material_typeFilter {
        min-width: 200px;
    }
    #table_open_items thead th:nth-child(n+7) {
    position: sticky;
    top: 0;
    background: white;
    z-index: 10;
  }

  /* Optional: prevent wrapping */
  #table_open_items th,
  #table_open_items td {
    white-space: nowrap;
  }
</style>

<body>
<div id="wrapper">
    <?php $activePage = '/dpm/mat_details.php'; ?>
    <?php require_once $_SERVER["DOCUMENT_ROOT"]."/dpm/shared/dto_sidebar.php"; ?>
    <?php include_once 'dto/updatematnumber.php'; ?>
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">
                    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i
                                class="fa fa-bars"></i> </a>
                </div>
                <ul class="nav navbar-top-links navbar-right">
                    <li>
                        <h2 style="text-align: left;">Material Register</h2>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="row" id="mainRow">
            <div class="col-lg-12">
                <div class="ibox mb-0" id="ibox_open_list_page">
                    <div id="headersegment">
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
                                                <th>Product Name</th>
                                                <th>Drawing Name</th>
                                                <th>Drawing Number</th>
                                                <th>Material Type</th>
                                                <th>Material</th>
                                                <th>KA Rating</th>
                                                <th>Width</th>
                                                <th>Description</th>
                                                <th>Thickness</th>
                                                <th>Rear Box</th>
                                                <th>End Cover Location</th>
                                                <th>Ebb Cutout</th>
                                                <th>Ebb Size</th>
                                                <th>Cable Entry</th>
                                                <th>GP Thickness</th>
                                                <th>GP Material</th>
                                                <th>Interlock</th>
                                                <th>IR Window</th>
                                                <th>Nameplate</th>
                                                <th>Viewing Window</th>
                                                <th>LHS Panel RB</th>
                                                <th>RHS Panel RB</th>
                                                <th>Rear Box Type</th>
                                                <th>CT Type</th>
                                                <th>No.Cable</th>    
                                                <th>CBCT</th>
                                                <th>Panel Width</th>
                                                <th>Feeder Bar Size</th>
                                                <th>MBB Size</th>
                                                <th>Busbar Size</th>
                                                <th>AG Plating</th>
                                                <th>MBB Run</th>
                                                <th>Feeder Run</th>
                                                <th>Feeder Size</th>
                                                <th>Short Text</th>
                                                <th>Remarks</th>
                                                <th>User</th>
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
        <?php $footer_display = 'Material Number Details';
        include_once '../assemblynotes/shared/footer.php'; ?>
    </div>
</div>

<!-- Scripts -->
<?php include_once '../assemblynotes/shared/headerSemanticScripts.php' ?>
<script src="shared/shared.js"></script>
<script src="dto/updateDtoModal.js?<?= time() ?>"></script>
<script src="dto/allMat.js?<?php echo rand(); ?>"></script>
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
</body>
</html>