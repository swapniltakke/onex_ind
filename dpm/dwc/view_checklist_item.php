<!DOCTYPE html>
<html>
<?php
SharedManager::checkAuthToModule(10);
include_once '../core/index.php';
include_once '/api/checklistAPI.php';
$menu_header_display = 'View Checklist';

$checklist = "SELECT checklist_id, checklist_name FROM tbl_chk_checklist GROUP BY checklist_name";
$checklist_data = DbManager::fetchPDOQueryData('spectra_db', $checklist)["data"];
$line = "SELECT * FROM tbl_line";
$line_data = DbManager::fetchPDOQueryData('spectra_db', $line)["data"];
$product = "SELECT * FROM tbl_chk_product";
$product_data = DbManager::fetchPDOQueryData('spectra_db', $product)["data"];
$station = "SELECT * FROM tbl_chk_station";
$station_data = DbManager::fetchPDOQueryData('spectra_db', $station)["data"];
?>
<head>
    <title>OneX | View Checklist</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=yes"/>

    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta charset="utf-8">

    <link href="../../css/semantic.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="../../css/dataTables.semanticui.min.css">
    <link rel="stylesheet" type="text/css" href="../../css/responsive.dataTables.min.css">

    <link href="../../css/main.css?13" rel="stylesheet"/>

    <?php include_once '../shared/headerStyles.php' ?>
    
    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/semantic.min.js"></script>
    <script src="../../js/jquery.dataTables.js"></script>
    <script src="../../js/dataTables.semanticui.min.js"></script>
    <script src="../../js/dataTables.buttons.min.js"></script>
    <script src="../../js/buttons.flash.min.js"></script>
    <script src="../../js/jszip.min.js"></script>
    <script src="../../js/pdfmake.min.js"></script>
    <script src="../../js/vfs_fonts.js"></script>
    <script src="../../js/buttons.html5.min.js"></script>
    <script src="../../js/buttons.print.min.js"></script>
    <script src="../../js/buttons.colVis.min.js"></script>
    <script src="../../js/tablesort.js"></script>
    <script src="../../js/Semantic-UI-Alert.js"></script>
    <script src="../../shared/inspia_gh_assets/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <link rel="stylesheet" href="../../css/jquery.toast.min.css">

    <script src="/shared/inspia_gh_assets/js/popper.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/bootstrap.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/bootstrap-select.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <script src="/shared/inspia_gh_assets/js/plugins/dataTables/datatables.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/dataTables/dataTables.bootstrap4.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/select2/js/select2.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/toastr/toastr.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/switchery/switchery.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/iCheck/icheck.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/moment.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/chosen/chosen.jquery.js"></script>
    <script src="/shared/inspia_gh_assets/js/inspinia.js"></script>
    <script src="/shared/inspia_gh_assets/js/daterangepicker.js"></script>
    
</head>
<style>
    /* Combine styles from both files, removing duplicates */
    .full-loader{
        position: fixed !important;
        top: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(50, 50, 50, 0.8) !important;
        z-index: 10000;
    }

    .full-loader > .loader{
        display: flex !important;
        height: 58px;
        flex-direction: column-reverse;
        width: fit-content;
        font-size: 17px;
    }

    .cont {
        padding-top: 40px !important;
        padding-bottom: 41px !important;
    }

    .active.item h3 {
        transform: scale(1.2) !important;
        color: white !important;
    }

    .active.item i {
        transform: scale(1.5) !important;
        color: white !important;
    }

    .active.item {
        background-color: #00b5ad !important;
    }

    .item {
        background-color: white !important;
    }

    .item i {
        color: black !important;
    }

    .item h3 {
        color: black !important;
    }

    .align-center {
        text-align: center !important;
    }

    .ui .label {
        margin-bottom: 10px !important;
    }

    .ui .segment {
        margin-top: 0px !important;
    }

    .date-none {
        display: none;
    }

    .align-center {
        text-align: center !important;
    }

    .ui.search > .results {
        position: relative !important;
        margin: auto !important;
    }

    .ui.tiny.button,
    .ui.tiny.buttons .button,
    .ui.tiny.buttons .or {
        font-size: 10px !important;
    }

    .ui.search .results {
        max-width: 100% !important;
        width: 100% !important;
        min-width: 100% !important;
        left: 0 !important;
        right: 0 !important;
    }

    .ui.search .results .result {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px !important;
        border-bottom: 1px solid #f0f0f0;
    }

    .ui.search .results .result .content {
        display: flex;
        flex-direction: column;
        margin-left: 10px;
    }

    .ui.search .results .result .title {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .ui.search .results .result .description {
        color: #888;
        font-size: 0.9em;
    }

    .dt-center{ text-align: center !important; }

    #materialSearchDataParent {
        width: 100%;
        overflow-x: auto;
    }

    #orderInfoDataTable {
        width: 100% !important;
        max-width: 100% !important;
    }

    .dataTables_wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .dataTables_wrapper .dataTables_scroll {
        overflow-x: auto;
        overflow-y: hidden;
    }

    .dataTables_wrapper .dataTables_scrollBody {
        overflow-x: auto;
        overflow-y: hidden;
    }

    #customFilterParent {
        width: 100% !important;
        display: block !important;
    }

    /* Scanner icon styles */
    .scanner-icon {
        cursor: pointer;
        margin-left: 10px;
        vertical-align: middle;
    }
    .scanner-icon img {
        width: 24px;
        height: 24px;
    }
    .required-field {
        color: red;
    }
    #wrapper {
        min-height: 100vh;
        position: relative;
        display: flex;
        flex-direction: column;
    }

    #page-wrapper {
        flex: 1;
        min-height: 100vh;
        position: relative;
        padding-bottom: 60px; /* Adjust this value based on your footer height */
    }
    /* Modal positioning and sizing */
    .ui.modal {
        width: 90% !important;
        max-height: 80vh !important; /* Increased to 80% of viewport height */
        height: auto !important;
        margin: 0 !important;
        position: fixed !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        overflow: auto !important;
    }

    /* Content styling */
    .ui.modal > .content {
        padding: 20px 30px !important;
    }

    /* Close icon styling */
    .ui.modal .close.icon {
        font-size: 1.5em !important;
        color: #666 !important;
        opacity: 0.8 !important;
        transition: opacity 0.2s !important;
    }

    .ui.modal .close.icon:hover {
        opacity: 1 !important;
    }

    /* Card title styling */
    .ui.modal .card-title {
        margin-bottom: 20px !important;
        padding-right: 20px !important; /* Make space for close icon */
    }

    /* Form container styling */
    .ui.modal .ui.fluid {
        height: auto !important;
        overflow: visible !important;
    }

    /* Ensure modal appears above other elements */
    .ui.modal {
        z-index: 1001 !important;
    }

    /* Dimmer styling */
    .ui.dimmer {
        z-index: 1000 !important;
    }
    /* Add this to your existing <style> section */
    .delete-btn {
        background-color: #db2828 !important;
        color: white !important;
        margin-left: 5px !important;
    }

    .action-buttons {
        white-space: nowrap;
    }
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin-bottom: 1rem;
    }

    .dataTables-example {
        width: 100%;
        margin-bottom: 0;
        table-layout: fixed;
    }

    .dataTables-example th, 
    .dataTables-example td {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .action-column {
        min-width: 120px;
        width: 120px;
    }

    .action-buttons {
        display: flex;
        gap: 5px;
        justify-content: flex-start;
        align-items: center;
    }

    .ui.tiny.button {
        padding: 0.5em 1em;
        font-size: 0.85em;
        white-space: nowrap;
    }

    /* Ensure buttons stay on the same line */
    .action-buttons button {
        flex-shrink: 0;
    }

    /* Adjust column widths */
    .dataTables-example td:nth-child(1) { width: 15%; }
    .dataTables-example td:nth-child(2) { width: 12%; }
    .dataTables-example td:nth-child(3) { width: 15%; }
    .dataTables-example td:nth-child(4) { width: 12%; }
    .dataTables-example td:nth-child(5) { width: 15%; }
    .dataTables-example td:nth-child(6) { width: 21%; }
    .dataTables-example td:nth-child(7) { width: 10%; }
</style>
<body>
<div id="wrapper">
    <?php $activePage = '/view_checklist_item.php'; ?>
    <?php include_once '../shared/sidebar.php'; ?>
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom" style="position: relative;">
            <div class="ui fixed menu" style="padding: 21px; color:teal; width: 100%;">
                <div class="ui container" style="position: relative; width: 100%;">
                    <div style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); display: flex; align-items: center;">
                        <a href="/" style="display: flex; align-items: center; text-decoration: none;">
                            <div style="margin-right: 10px;">
                                <img src="/images/onex_icon.png" width="25" height="36" class="logo-icon">
                            </div>
                            <div class="logo-text">
                                <h5 style="margin: 0; font-size: 18px; line-height: 1.2;">
                                    DWC <sup class="badge badge-danger" style="font-size: 0.4em; background-color: #dc3545; color: white; padding: 0.2em 0.3em; border-radius: 0.25rem; vertical-align: super;">OneX</sup>
                                </h5>
                                <p style="margin: 0; text-transform: uppercase; font-size: 10px; color: #6c757d; line-height: 1.2;">Digital Work Center</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="ui inverted segment full-loader" style="display: flex">
            <div class="ui active inverted loader">Loading</div>
        </div>

        <div class="ui fluid" style="margin-top: 60px;">
            <h1 class="card-title">View Checklist</h1>
            <div class="ui form" id="filterForm">
                <div class="column">
                    <div class="ui grid checklist-item-row">
                        <div class="four wide column">
                            <label>Checklist Name:</label>
                            <select id="checklistFilter">
                                <option value="">Select Checklist</option>
                                <?php foreach ($checklist_data as $checklists) {
                                    echo '<option value="'.$checklists['checklist_id'].'">'.$checklists['checklist_name'].'</option>';
                                } ?>
                            </select>
                        </div>
                        <div class="four wide column">
                            <label>Department Name:</label>
                            <select id="lineFilter">
                                <option value="">Select Department</option>
                                <?php foreach ($line_data as $lines) {
                                    echo '<option value="'.$lines['id'].'">'.$lines['line_name'].'</option>';
                                } ?>
                            </select>
                        </div>
                        <div class="four wide column">
                            <label>Product Name:</label>
                            <select id="productFilter">
                                <option value="">Select Product</option>
                                <?php foreach ($product_data as $products) {
                                    echo '<option value="'.$products['id'].'">'.$products['product_name'].'</option>';
                                } ?>
                            </select>
                        </div>
                        <div class="four wide column">
                            <label>Station Name:</label>
                            <select id="stationFilter">
                                <option value="">Select Station</option>
                                <?php foreach ($station_data as $stations) {
                                    echo '<option value="'.$stations['id'].'">'.$stations['station_name'].'</option>';
                                } ?>
                            </select>
                        </div>
                    </div>
                    <div class="column">
                        <div class="ui grid checklist-item-row" style="margin-top: 10px;">
                            <div class="four wide column">
                                <label>Checklist Reference:</label>
                                <input type="text" id="referenceFilter" placeholder="Search reference...">
                            </div>
                            <div class="eight wide column">
                                <label>Checklist Item:</label>
                                <input type="text" id="itemFilter" placeholder="Search item...">
                            </div>
                            <div class="four wide column" style="display: flex; align-items: flex-end; margin-top: 25px;">
                                <button class="ui teal button" id="applyFilter">Apply Filter</button>
                                <button class="ui button" id="resetFilter" style="margin-left: 10px;">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Replace the existing edit modal with this improved version -->
        <div class="ui modal" id="editModal">
            <i class="close icon" style="position: absolute; right: 10px; top: 10px; z-index: 1000; cursor: pointer;"></i>
            <div class="content" style="padding-top: 20px;">
                <div class="ui fluid">
                    <h1 class="card-title">Edit Checklist</h1>
                    <form class="ui form" id="editChecklistForm">
                        <div class="column">
                            <div class="ui grid checklist-item-row">
                                <div class="four wide column">
                                    <label><span class="required-field">*</span>Checklist Name:</label>
                                    <input type="hidden" id="checklistIdEdit" name="checklistIdEdit" value="">
                                    <select id="checklistNameEdit" name="checklistNameEdit">
                                        <option value="">Select Checklist</option>
                                        <?php foreach ($checklist_data as $checklists) {
                                            echo '<option value="'.$checklists['checklist_name'].'">'.$checklists['checklist_name'].'</option>';
                                        } ?>
                                    </select>
                                </div>
                                <div class="four wide column">
                                    <label><span class="required-field">*</span>Department Name:</label>
                                    <select id="lineNameEdit" name="lineNameEdit">
                                        <option value="">Select Department</option>
                                        <?php foreach ($line_data as $lines) {
                                            echo '<option value="'.$lines['id'].'">'.$lines['line_name'].'</option>';
                                        } ?>
                                    </select>
                                </div>
                                <div class="four wide column">
                                    <label><span class="required-field">*</span>Product Name:</label>
                                    <select id="productNameEdit" name="productNameEdit">
                                        <option value="">Select Product</option>
                                        <?php foreach ($product_data as $products) {
                                            echo '<option value="'.$products['id'].'">'.$products['product_name'].'</option>';
                                        } ?>
                                    </select>
                                </div>
                                <div class="four wide column">
                                    <label>Station Name:</label>
                                    <select id="stationNameEdit" name="stationNameEdit">
                                        <option value="">Select Station</option>
                                        <?php foreach ($station_data as $stations) {
                                            echo '<option value="'.$stations['id'].'">'.$stations['station_name'].'</option>';
                                        } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        </br>
                        <div class="column">
                            <div class="ui grid checklist-item-row">
                                <div class="twelve wide column">
                                    <label><span class="required-field">*</span>Document Description/No:</label>
                                    <textarea id="documentDescriptionEdit" name="documentDescriptionEdit" placeholder="Enter document description/no" rows="3"></textarea>
                                </div>
                                <div class="four wide column">
                                    <label><span class="required-field">*</span>Revision Date/No:</label>
                                    <input type="text" id="revisionDateEdit" name="revisionDateEdit" placeholder="Enter revision date/no">
                                </div>
                            </div>
                        </div>
                        </br>
                        <div class="column">
                            <label><span class="required-field">*</span>Checklist Items:</label>
                            <div class="ui segment" id="checklist-items-container">
                                <div class="ui grid checklist-item-row">
                                    <div class="two wide column">
                                        <input type="text" id="checklistReferenceEdit" name="checklistReferenceEdit" placeholder="Enter reference">
                                    </div>
                                    <div class="eight wide column">
                                        <input type="text" id="checklistItemEdit" name="checklistItemEdit" placeholder="Enter checklist item">
                                    </div>
                                    <div class="four wide column" style="display: flex; justify-content: center; align-items: center;">
                                        <div class="ui radio checkbox">
                                            <input type="radio" name="inputType" id="noneRadio" value="0" checked><label for="noneRadio">None</label>
                                        </div>&nbsp;&nbsp;
                                        <div class="ui radio checkbox">
                                            <input type="radio" name="inputType" id="freeTextRadio" value="1"><label for="freeTextRadio">Free Text</label>
                                        </div>&nbsp;&nbsp;
                                        <div class="ui radio checkbox">
                                            <input type="radio" name="inputType" id="serialNumberRadio" value="2"><label for="serialNumberRadio">Serial No.</label>
                                        </div>
                                    </div>
                                    <div class="two wide column">
                                        <button type="submit" class="ui green button">Update Checklist</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </br>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">View Checklist Names</h3>
                    </div>
                    <div class="card-body">
                        <div id="checklistsTable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $footer_display = 'View Checklist';
    include_once '../../assemblynotes/shared/footer.php'; ?>
</div>

<!-- Mainly scripts -->
<?php include_once '../../assemblynotes/shared/headerSemanticScripts.php' ?>
<script src="../../shared/shared.js"></script>
<script>
    $(document).ready(function() {
        // Initialize dropdowns
        $('.ui.dropdown').dropdown();
        
        // Filter functionality
        $('#applyFilter').click(function() {
            fetchAndDisplayChecklists(1, true); // true indicates this is a filter action
        });

        // Reset filter
        $('#resetFilter').click(function() {
            // Reset all select elements to their default value
            $('#filterForm select').each(function() {
                $(this).val('');
            });
            
            // Reset all input fields
            $('#filterForm input').val('');
            
            // Reset the currentFilters object
            currentFilters = {
                checklistName: '',
                lineName: '',
                productName: '',
                stationName: '',
                reference: '',
                item: ''
            };
            
            // Fetch the data with reset filters
            fetchAndDisplayChecklists(1, false);
        });

        // Initial load
        fetchAndDisplayChecklists(1, false);

        $('.ui.modal')
            .modal({
                closable: true,
                centered: true,
                observeChanges: true,
                onShow: function() {
                    $(this).css('margin-top', '-' + ($(this).height() / 2) + 'px !important');
                }
            });
        
        // Close modal on icon click
        $('.ui.modal .close.icon').on('click', function() {
            $('#editModal').modal('hide');
        });
        
        // Modify editChecklist function to open modal
        window.editChecklist = function(checklistId, checklistName, lineId, productId, stationId, checklistReference, checklistItem, addOn, documentDescription, revisionDate) {
            $('#checklistIdEdit').val(checklistId);
            $('#checklistNameEdit').val(checklistName);
            $('#lineNameEdit').val(lineId);
            $('#productNameEdit').val(productId);
            if (stationId === '0') {
                $('#stationNameEdit').val('');
            } else {
                $('#stationNameEdit').val(stationId);
            }
            $('#documentDescriptionEdit').val(documentDescription || '');
            $('#revisionDateEdit').val(revisionDate || '');
            $('#checklistReferenceEdit').val(checklistReference);
            $('#checklistItemEdit').val(checklistItem);
            
            // Set the selected radio button based on the addOn value
            if (addOn == 0) {
                $('#noneRadio').prop('checked', true);
            } else if (addOn == 1) {
                $('#freeTextRadio').prop('checked', true);
            } else if (addOn == 2) {
                $('#serialNumberRadio').prop('checked', true);
            } else {
                $('#noneRadio').prop('checked', true);
            }
            
            // Open modal
            $('#editModal').modal('show');
        }
    });
    
    let currentFilters = {
        checklistName: '',
        lineName: '',
        productName: '',
        stationName: '',
        reference: '',
        item: ''
    };
    let activeAjaxRequests = 0;        

    function showFullLoader(){
        const loader = document.querySelector('.full-loader');
        if (loader) {
            loader.style.display = 'flex';
        }
    }

    function hideFullLoader(){
        const loader = document.querySelector('.full-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    }

    // Function to update current filters
    function updateCurrentFilters() {
        currentFilters = {
            checklistName: $('#checklistFilter').val(),
            lineName: $('#lineFilter').val(),
            productName: $('#productFilter').val(),
            stationName: $('#stationFilter').val(),
            reference: $('#referenceFilter').val(),
            item: $('#itemFilter').val()
        };
    }

    function fetchAndDisplayChecklists(page = 1, isFilterAction = false) {
        showFullLoader();
        activeAjaxRequests++;

        // Update currentFilters if this is a filter action
        if (isFilterAction) {
            updateCurrentFilters();
        }

        // Combine page number with current filters
        const filterData = {
            page: page,
            ...currentFilters
        };

        $.ajax({
            url: '/dpm/dwc/api/checklistAPI.php?type=view_checklist',
            type: 'POST',
            data: filterData,
            success: function(response) {
                $('#checklistsTable').html(response);
                activeAjaxRequests--;
                if (activeAjaxRequests === 0) {
                    hideFullLoader();
                }
            },
            error: function() {
                activeAjaxRequests--;
                if (activeAjaxRequests === 0) {
                    hideFullLoader();
                }
            }
        });
    }

    // Fetch and display the checklists on page load
    fetchAndDisplayChecklists();

    // Function to check if a checklist name already exists
    function isChecklistNameExists(checklistName, lineName, productName, stationName, checklistReference, checklistItem, addOn, documentDescription, revisionDate, callback) {
        showFullLoader();
        activeAjaxRequests++;

        $.ajax({
            url: '/dpm/dwc/api/checklistAPI.php?type=check_checklist_name',
            type: 'POST',
            data: { 
                checklistName: checklistName, 
                lineName: lineName, 
                productName: productName, 
                stationName: stationName, 
                checklistReference: checklistReference, 
                checklistItem: checklistItem, 
                addOn: addOn,
                documentDescription: documentDescription,
                revisionDate: revisionDate
            },
            success: function(response) {
                callback(response);
                activeAjaxRequests--;
                if (activeAjaxRequests === 0) {
                    hideFullLoader();
                }
            },
            error: function() {
                activeAjaxRequests--;
                if (activeAjaxRequests === 0) {
                    hideFullLoader();
                }
            }
        });
    }
    
    // Function to populate the input field with the selected checklist name
    function editChecklist(checklistId, checklistName, lineId, productId, stationId, checklistReference, checklistItem, addOn, documentDescription, revisionDate) {
        $('#checklistIdEdit').val(checklistId);
        $('#checklistNameEdit').val(checklistName);
        $('#lineNameEdit').val(lineId);
        $('#productNameEdit').val(productId);
        if (stationId === '0') {
            $('#stationNameEdit').val('');
        } else {
            $('#stationNameEdit').val(stationId);
        }
        $('#documentDescriptionEdit').val(documentDescription || '');
        $('#revisionDateEdit').val(revisionDate || '');
        $('#checklistReferenceEdit').val(checklistReference);
        $('#checklistItemEdit').val(checklistItem);
        // Set the selected radio button based on the addOn value
        if (addOn == 0) {
            $('#noneRadio').prop('checked', true);
        } else if (addOn == 1) {
            $('#freeTextRadio').prop('checked', true);
        } else if (addOn == 2) {
            $('#serialNumberRadio').prop('checked', true);
        } else {
            $('#noneRadio').prop('checked', true);
        }
    }

    // Handle the edit checklist form submission
    $('#editChecklistForm').submit(function(e) {
        e.preventDefault();
        const checklistId = $('#checklistIdEdit').val();
        const checklistName = $('#checklistNameEdit').val().trim();
        const lineName = $('#lineNameEdit').val().trim();
        const productName = $('#productNameEdit').val().trim();
        const stationName = $('#stationNameEdit').val().trim();
        const documentDescription = $('#documentDescriptionEdit').val().trim();
        const revisionDate = $('#revisionDateEdit').val().trim();
        const checklistReference = $('#checklistReferenceEdit').val().trim();
        const checklistItem = $('#checklistItemEdit').val().trim();
        const addOn = $('input[name="inputType"]:checked').val();

        // Validation checks
        if (checklistName === '') {
            alert('Checklist name cannot be blank.');
            return;
        }
        if (lineName === '') {
            alert('Department name cannot be blank.');
            return;
        }
        if (productName === '') {
            alert('Product name cannot be blank.');
            return;
        }
        if (documentDescription === '') {
            alert('Document Description/No cannot be blank.');
            return;
        }
        if (revisionDate === '') {
            alert('Revision Date/No cannot be blank.');
            return;
        }
        if (checklistReference === '') {
            alert('Checklist reference cannot be blank.');
            return;
        }
        if (checklistItem === '') {
            alert('Checklist item cannot be blank.');
            return;
        }

        isChecklistNameExists(checklistName, lineName, productName, stationName, checklistReference, checklistItem, addOn, documentDescription, revisionDate, function(exists) {
            if (exists != 'false' && exists.id !== parseInt(checklistId)) {
                alert('Filled details already exists. Please enter a unique combination.');
            } else {
                // Close the modal first
                $('#editModal').modal('hide');
                
                // Show loading screen
                showFullLoader();
                activeAjaxRequests++;

                $.ajax({
                    url: '/dpm/dwc/api/checklistAPI.php?type=update_checklist',
                    type: 'POST',
                    data: { 
                        checklistId: checklistId, 
                        checklistName: checklistName, 
                        lineName: lineName, 
                        productName: productName, 
                        stationName: stationName,
                        documentDescription: documentDescription,
                        revisionDate: revisionDate,
                        checklistReference: checklistReference, 
                        checklistItem: checklistItem, 
                        addOn: addOn 
                    },
                    success: function(response) {
                        if (response === 'success') {
                            // Reset form fields
                            $('#checklistIdEdit').val('');
                            $('#checklistNameEdit').val('');
                            $('#lineNameEdit').val('');
                            $('#productNameEdit').val('');
                            $('#stationNameEdit').val('');
                            $('#documentDescriptionEdit').val('');
                            $('#revisionDateEdit').val('');
                            $('#checklistReferenceEdit').val('');
                            $('#checklistItemEdit').val('');
                            
                            // Refresh the checklists table
                            fetchAndDisplayChecklists(1, false);
                            
                            // Show success message after table refresh
                            setTimeout(function() {
                                alert('Checklist updated successfully!');
                            }, 500);
                        } else {
                            alert('Error while updating checklist');
                        }
                        
                        activeAjaxRequests--;
                        if (activeAjaxRequests === 0) {
                            hideFullLoader();
                        }
                    },
                    error: function() {
                        alert('Error while updating checklist');
                        activeAjaxRequests--;
                        if (activeAjaxRequests === 0) {
                            hideFullLoader();
                        }
                    }
                });
            }
        });
    });

    // Add this function to handle delete operation
    function deleteChecklist(checklistId) {
        if (confirm('Are you sure you want to delete this checklist?')) {
            showFullLoader();
            activeAjaxRequests++;

            $.ajax({
                url: '/dpm/dwc/api/checklistAPI.php?type=delete_checklist',
                type: 'POST',
                data: { checklistId: checklistId },
                success: function(response) {
                    if (response === 'success') {
                        // Refresh the checklists table
                        fetchAndDisplayChecklists(1, false);
                        
                        // Show success message
                        setTimeout(function() {
                            alert('Checklist deleted successfully!');
                        }, 500);
                    } else {
                        alert('Error while deleting checklist');
                    }
                    
                    activeAjaxRequests--;
                    if (activeAjaxRequests === 0) {
                        hideFullLoader();
                    }
                },
                error: function() {
                    alert('Error while deleting checklist');
                    activeAjaxRequests--;
                    if (activeAjaxRequests === 0) {
                        hideFullLoader();
                    }
                }
            });
        }
    }
</script>
</body>
</html>