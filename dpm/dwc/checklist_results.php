<!DOCTYPE html>
<html>
<?php
SharedManager::checkAuthToModule(12);
include_once '../core/index.php';
$project = $_GET["project"] ?: 0;
SharedManager::saveLog("log_checklist_form", "View for Project: $project");
$menu_header_display = 'Checklist Results';
$checklist_form = "1";
$currentUser = isset($_SESSION['username']) ? $_SESSION['username'] : '';
?>
<head>
    <title>OneX | Checklist Results</title>
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
    <script src="../../js/dataTables.fixedHeader.min.js"></script>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
</head>
<style>
    /* Adjust status box styling */
    .status-box {
        border-radius: 4px;
        font-weight: bold;
        text-align: center;
        display: inline-block;
        padding: 2px 6px !important;
        font-size: 11px !important;
        min-width: 50px !important;
    }

    .status-ok {
        background-color: #28a745;
        color: white;
        width: fit-content;
        margin: 0 auto;
    }

    .status-notok {
        background-color: #dc3545;
        color: white;
        width: fit-content;
        margin: 0 auto;
    }

    /* Increase edit button size */
    .btn-sm.edit-btn {
        height: auto;       /* Allow height to adjust */
        display: inline-block;
        padding: 2px 8px !important;
        font-size: 11px !important;
        min-width: 60px !important;
    }

    /* Make the edit icon slightly larger */
    .btn-sm.edit-btn i.fa {
        font-size: 11px !important;
        margin-right: 3px !important;
    }

    /* Modal styles */
    .edit-modal {
        max-width: 95%;
        width: 1400px;
    }

    .modal-xl {
        min-width: 1200px;
    }

    .modal-body {
        padding: 15px;
    }

    .punch-list-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .punch-list-table td {
        border: 1px solid black;
        padding: 4px;
        font-size: 12px;
        overflow: hidden;
    }

    .punch-list-table textarea.punch-input {
        width: 100%;
        border: none;
        resize: vertical;
        min-height: 30px;
        font-size: 12px;
        background: transparent;
    }

    .punch-list-table input.punch-input {
        width: 100%;
        border: none;
        font-size: 12px;
        background: transparent;
    }

    .punch-list-table .col-no { width: 3%; }
    .punch-list-table .col-desc { width: 25%; }
    .punch-list-table .col-by { width: 8%; }
    .punch-list-table .col-date { width: 8%; }
    .punch-list-table .col-remark { width: 12%; }
    .punch-list-table .col-work { width: 4%; }
    .punch-list-table .col-code { width: 4%; }

    /* Disabled state styles */
    .punch-input:disabled {
        background-color: #f5f5f5;
        cursor: not-allowed;
        opacity: 0.7;
    }

    /* Codes legend styling */
    .codes-legend {
        padding: 8px;
        font-size: 12px;
    }

    .codes-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
    }

    .header-info {
        background-color: #f8f9fa;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .header-info table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    .header-info td {
        border: 1px solid black;
        padding: 6px;
        font-size: 12px;
    }

    .table {
        margin-bottom: 0;
    }

    .table th {
        background-color: #f8f9fa;
        vertical-align: middle;
    }

    .table td {
        vertical-align: top;
    }

    .form-control[readonly] {
        background-color: #e9ecef;
    }

    .finding-info,
    .resolution-info,
    .rechecking-info {
        padding: 5px;
    }

    .form-group {
        margin-bottom: 10px;
    }

    .form-group label {
        font-size: 12px;
        margin-bottom: 2px;
    }

    .codes-legend {
        background-color: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
    }

    .codes-legend small {
        line-height: 1.8;
    }
    /* Add to your existing styles */
    .ui.input {
        width: 100%;
    }

    .ui.input input, .ui.dropdown {
        height: 32px !important;
        min-height: 32px !important;
        padding: 4px 8px !important;
        font-size: 12px !important;
    }

    .ui.form .field {
        margin-bottom: 0;
    }

    .ui.form .field label {
        margin-bottom: 3px !important;
        font-size: 0.9em;
        font-size: 12px !important;
    }

    .ui.form label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: rgba(0,0,0,.87);
    }

    .ui.buttons {
        display: flex;
        justify-content: flex-start;
        align-items: flex-end;
        height: 100%;
    }

    .ui.button {
        height: 32px !important;
        padding: 6px 12px !important;
        font-size: 12px !important;
    }

    .ui.button .icon {
        margin: 0 4px 0 0;
    }

    .ui.button.primary {
        background-color: #00b5ad;
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 4px;
        cursor: pointer;
        height: 37px;
    }

    .ui.button.primary:hover {
        background-color: #75d4d0ff;
    }

    /* Adjust spacing for the grid */
    .ui.form {
        position: relative;
        z-index: 1;
    }

    .ui.grid {
        margin: 0 !important;
        width: 100% !important;
    }

    .ui.form .field .suggestions {
        position: absolute;
        width: 100%;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        margin-top: 25px;
    }

    .ui.grid > .column {
        padding: 3px 5px !important;
    }

    /* Responsive adjustments */
    @media only screen and (max-width: 767px) {
        .ui.grid > .column {
            width: 100% !important;
        }
    }
    /* Add to your existing styles */
    .suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-radius: 0 0 4px 4px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        max-height: 300px; /* Increased max-height */
        overflow-y: auto;
        z-index: 9999; /* Higher z-index */
        display: none;
    }

    .suggestion-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        font-size: 12px;
    }

    /* Custom scrollbar for suggestions */
    .suggestions::-webkit-scrollbar {
        width: 8px;
    }

    .suggestions::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .suggestions::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .suggestions::-webkit-scrollbar-thumb:hover {
        background: #666;
    }

    .suggestion-item:hover {
        background-color: #f5f5f5;
    }

    .suggestion-item:last-child {
        border-bottom: none;
    }

    .field {
        position: relative;
        margin-bottom: 0px !important;
    }

    /* Date Range Picker Styles */
    .daterangepicker {
        z-index: 1100;
    }

    /* Table responsive behavior */
    #resultsTable {
        width: 100% !important;
        margin: 0 !important;
    }

    /* Update the column widths */
    #resultsTable th:nth-child(1) { width: 100px !important; } /* Sales Order */
    #resultsTable th:nth-child(2) { width: 100px !important; } /* Panel Type */
    #resultsTable th:nth-child(3) { width: 80px !important; }  /* Item Number */
    #resultsTable th:nth-child(4) { width: 100px !important; } /* Panel Name */
    #resultsTable th:nth-child(5) { width: 100px !important; } /* Typical Name */
    #resultsTable th:nth-child(6) { width: 80px !important; }  /* Sub Item */
    #resultsTable th:nth-child(7) { width: 100px !important; } /* Station */
    #resultsTable th:nth-child(8) { width: 200px !important; } /* Checklist Name */
    #resultsTable th:nth-child(9) { width: 100px !important; } /* Reference */
    #resultsTable th:nth-child(10) { width: 350px !important; } /* Description */
    #resultsTable th:nth-child(11) { width: 100px !important; } /* Finding By */
    #resultsTable th:nth-child(12) { width: 80px !important; } /* Date */
    #resultsTable th:nth-child(13) { width: 70px !important; } /* Action */

    /* Ensure the filters section matches table width */
    #customFilterParent,
    .ui.form .ui.grid {
        width: 100% !important;
        margin: 0 !important;
    }

    /* Center align all table headers and cells */
    #resultsTable thead th {
        text-align: center !important;
        vertical-align: middle !important;
        font-size: 12px !important;
    }

    #resultsTable tbody td {
        text-align: center !important;
        vertical-align: middle !important;
        font-size: 12px !important;
    }

    /* Ensure buttons remain centered */
    #resultsTable .btn {
        margin: 0 auto;
        display: block;
    }

    /* Keep the status-box centered */
    #resultsTable .status-box {
        margin: 0 auto;
        display: block;
    }

    /* Add to your existing styles */
    .card {
        width: 100%;
    }

    .card-body {
        padding: 15px;
        width: 100%;
        padding: 10px !important;
    }

    #resultsTable_wrapper {
        width: 100%;
        min-width: 100%;
        margin: 0;
        padding: 0;
    }

    #resultsTable {
        width: 100% !important;
        margin: 0 !important;
        font-size: 12px !important;
    }

    /* Control column widths */
    #resultsTable th,
    #resultsTable td {
        white-space: normal;
        word-wrap: break-word;
    }

    /* Responsive container for the entire content */
    .card > .card-body {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    /* Adjust filter columns responsiveness */
    @media screen and (max-width: 1400px) {
        .card {
            margin: 10px;
        }
        
        .card-body {
            padding: 10px;
        }
    }

    /* Table header and footer controls */
    .dataTables_length, 
    .dataTables_filter, 
    .dataTables_info, 
    .dataTables_paginate {
        font-size: 12px !important;
        padding: 5px 0 !important;
    }

    .dataTables_filter input {
        height: 28px !important;
        padding: 2px 8px !important;
        font-size: 12px !important;
    }

    /* Status box and button alignment */
    .status-box,
    .btn-sm.edit-btn {
        white-space: nowrap;
    }

    /* Ensure consistent spacing */
    .ui.form .field {
        position: relative;
    }

    /* DataTables specific fixes */
    .dataTables_scroll {
        width: 100% !important;
    }

    .dataTables_scrollBody {
        width: 100% !important;
    }

    /* Fix table header alignment */
    .dataTables_scrollHead {
        overflow: visible !important;
    }
    
    #wrapper {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    #page-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding-bottom: 60px; /* Adjust based on your footer height */
    }

    /* Ensure the button stays centered in the cell */
    #resultsTable td:last-child {
        text-align: center;
    }

    /* Add hover effect to edit button */
    .btn-sm.edit-btn:hover {
        background-color: #0056b3;
        border-color: #0056b3;
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }

    /* Modal styles */
    .modal-content {
        border-radius: 8px;
    }

    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    .modal-body {
        padding: 20px;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-control {
        border-radius: 4px;
        border: 1px solid #ced4da;
    }

    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }

    textarea.form-control {
        resize: vertical;
    }

    .modal-footer {
        border-top: 1px solid #dee2e6;
        padding: 1rem;
    }

    .btn-primary {
        background-color: #f2711c;
        border-color: #f2711c;
    }

    .btn-primary:hover {
        background-color: #c68457ff;
        border-color: #c68457ff;
    }

    .btn-primary1 {
        background-color: #1ab394;
        border-color: #1ab394;
        color: white;
    }

    .btn-primary1:hover {
        background-color: #117964ff;
        border-color: #117964ff;
        color: white;
    }

    .btn-primary2 {
        background-color: #1c84c6;
        border-color: #1c84c6;
        color: white;
    }

    .btn-primary2:hover {
        background-color: #125782ff;
        border-color: #125782ff;
        color: white;
    }
    
    .has-scroll::before,
    .has-scroll::after {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 5px;
        pointer-events: none;
    }

    .has-scroll::before {
        left: 0;
        background: linear-gradient(to right, rgba(0,0,0,0.1), transparent);
    }

    .has-scroll::after {
        right: 0;
        background: linear-gradient(to left, rgba(0,0,0,0.1), transparent);
    }

    /* DataTables Buttons styling */
    .dt-buttons {
        text-align: center !important;
        margin-bottom: 10px;
    }

    .dataTables_filter {
        float: right !important;
    }

    /* Style the buttons */
    .dt-buttons .btn {
        margin-right: 5px !important;
        margin: 0 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .dt-buttons .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    /* Style for Excel button */
    .dt-buttons .btn-success {
        background-color: #28a745;
        border-color: #28a745;
        color: white;
    }

    /* Style for PDF button */
    .dt-buttons .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
    }

    .dataTables_wrapper .dt-buttons {
        padding-top: 10px !important;
        text-align: center !important;
    }

    .export-loading {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 20px 40px;
        border-radius: 5px;
        z-index: 9999;
    }

    /* Fixed header and scrollable table styles */
    .dataTables_wrapper {
        overflow: hidden;
        position: relative;
    }

    .dataTables_scroll {
        overflow: auto;
        max-height: 70vh;
    }

    .dataTables_scrollHead {
        position: sticky !important;
        top: 0;
        z-index: 10;
        background-color: #f8f9fa;
    }

    .dataTables_scrollHead table {
        margin-bottom: 0 !important;
    }

    .dataTables_scrollBody {
        overflow-y: auto !important;
        overflow-x: auto !important;
    }

    /* Custom scrollbar styling */
    .dataTables_scrollBody::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }

    .dataTables_scrollBody::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Ensure header cells have proper background */
    table.dataTable thead th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        z-index: 10;
    }

    /* Ensure the table takes full width */
    .table-responsive {
        width: 100%;
        overflow: visible;
    }
</style>
<body>
<div id="wrapper">
    <?php $activePage = '/checklist_results.php'; ?>
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
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Filters section -->
                        <div class="ui form">
                            <div class="ui grid" style="margin: 0;">
                                <div class="three wide column" style="padding: 5px;">
                                    <div class="field">
                                        <label>Sales Order No</label>
                                        <div class="ui input">
                                            <input type="text" class="filter-input autocomplete" id="orderFilter" data-type="order" placeholder="Enter Sales Order No">
                                        </div>
                                        <div class="suggestions" id="orderSuggestions"></div>
                                    </div>
                                </div>
                                <div class="three wide column" style="padding: 5px;">
                                    <div class="field">
                                        <label>Item Number</label>
                                        <div class="ui input">
                                            <input type="text" class="filter-input autocomplete" id="itemFilter" data-type="item" placeholder="Enter Item Number">
                                        </div>
                                        <div class="suggestions" id="itemSuggestions"></div>
                                    </div>
                                </div>
                                <div class="three wide column" style="padding: 5px;">
                                    <div class="field">
                                        <label>Checklist Name</label>
                                        <div class="ui input">
                                            <input type="text" class="filter-input autocomplete" id="checklistFilter" data-type="checklist" placeholder="Enter Checklist Name">
                                        </div>
                                        <div class="suggestions" id="checklistSuggestions"></div>
                                    </div>
                                </div>
                                <div class="three wide column" style="padding: 5px;">
                                    <div class="field">
                                        <label>Finding By</label>
                                        <div class="ui input">
                                            <input type="text" class="filter-input autocomplete" id="findingByFilter" data-type="finding_by" placeholder="Enter Finding By">
                                        </div>
                                        <div class="suggestions" id="finding_bySuggestions"></div>
                                    </div>
                                </div>
                                <div class="three wide column" style="padding: 5px;">
                                    <div class="field">
                                        <label>Date Range</label>
                                        <div class="ui input">
                                            <input type="text" class="filter-input" id="dateRangeFilter" placeholder="Select Date Range">
                                        </div>
                                    </div>
                                </div>
                                <div class="three wide column" style="padding: 5px;">
                                    <div class="field">
                                        <label>Station</label>
                                        <select class="ui dropdown filter-select" id="stationFilter">
                                            <option value="">Select Station</option>
                                            <?php
                                            $sql_station = "SELECT * FROM tbl_chk_station ORDER BY station_name ASC";
                                            $stations = DbManager::fetchPDOQueryData('spectra_db', $sql_station)["data"];
                                            foreach ($stations as $station) {
                                                echo "<option value='".$station['id']."'>".$station['station_name']."</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="three wide column" style="padding: 5px;">
                                    <div class="field">
                                        <label>&nbsp;</label>
                                        <div class="ui buttons">
                                            <button class="ui primary button" id="applyFilters">
                                                <i class="filter icon"></i> Apply Filters
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="three wide column" style="padding: 5px;">
                                    <div class="field">
                                        <label>&nbsp;</label>
                                        <div class="ui buttons">
                                            <button class="ui button" id="resetFilters">
                                                <i class="undo icon"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive" style="width: 100%; overflow: visible;">
                            <table id="resultsTable" class="table table-striped table-bordered" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Sales Order No</th>
                                        <th>Panel Type</th>
                                        <th>Item Number</th>
                                        <th>Panel Name</th>
                                        <th>Typical Name</th>
                                        <th>Sub Item</th>
                                        <th>Station</th>
                                        <th>Checklist Name</th>
                                        <th>Reference</th>
                                        <th>Description</th>
                                        <th>Finding By</th>
                                        <th>Finding Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $footer_display = 'Checklist Results';
    include_once '../../assemblynotes/shared/footer.php'; ?>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 95vw;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Punch List</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="punch-list-wrapper">
                    <!-- Punch list content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary1" id="saveChanges">Save Changes</button>
                <button type="button" class="btn btn-primary2" id="submitChanges">Submit Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    // First, add these additional styles to your existing CSS
    const modalStyles = `
    <style>
        .edit-modal {
            max-width: 95% !important;
            margin: 1.75rem auto;
        }

        .edit-modal .modal-content {
            border-radius: 8px;
        }

        .edit-modal .modal-body {
            padding: 15px;
            max-height: calc(100vh - 210px);
            overflow-y: auto;
        }

        .edit-modal .punch-list-table {
            font-size: 11px !important;
            width: 100%;
            margin-bottom: 0;
        }

        .edit-modal .punch-list-table td {
            padding: 4px !important;
            vertical-align: middle;
        }

        .edit-modal .header-info table {
            font-size: 12px;
            margin-bottom: 15px;
        }

        .edit-modal .punch-input {
            font-size: 11px !important;
            padding: 2px !important;
            min-height: 24px;
        }

        .edit-modal .codes-legend {
            font-size: 11px;
        }

        /* Custom scrollbar for better appearance */
        .edit-modal .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .edit-modal .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .edit-modal .modal-body::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .edit-modal .modal-body::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Responsive table wrapper */
        .edit-modal .table-responsive {
            overflow-x: auto;
            margin: 0 -15px;
            padding: 0 15px;
        }

        .edit-modal .table-wrapper {
            position: relative;
            overflow: hidden;
        }
    </style>
    `;

    // Add the styles to the document head
    $('head').append(modalStyles);

    // Function to populate the edit modal with data
    function populateEditModal(data) {
        // Get current user from PHP session (assuming it's stored in a global variable)
        const currentUser = '<?php echo $currentUser; ?>'; // This should be set in your PHP
        const currentDate = new Date();

        // Clear previous content
        $('.punch-list-wrapper').empty();
        
        // Format panel type display
        let panelTypeDisplay = data.panel_type || '-';
        if (data.panel_type === 'Component' && data.sub_item) {
            panelTypeDisplay = `Component (${data.sub_item})`;
        }
        
        // Create the form structure
        const formHtml = `
            <form id="editPunchListForm">
                <input type="hidden" name="id" value="${data.id}">
                
                <!-- Header Information -->
                <div class="header-info">
                    <table>
                        <tr>
                            <td style="width: 10%"><b>Sales Order No:</b></td>
                            <td style="width: 10%">${data.order_no || '-'}</td>
                            <td style="width: 10%"><b>Item Number:</b></td>
                            <td style="width: 10%">${data.item_no || '-'}</td>
                            <td style="width: 10%"><b>Location/Typical Name:</b></td>
                            <td style="width: 10%">${data.location_name || '-'}/${data.typical_name || '-'}</td>
                            <td style="width: 10%"><b>Checklist Name:</b></td>
                            <td style="width: 10%">${data.checklist_name || '-'}</td>
                        </tr>
                        <tr>
                            <td><b>Department Name:</b></td>
                            <td>${data.line_name || '-'}</td>
                            <td><b>Product Name:</b></td>
                            <td>${data.product_name || '-'}</td>
                            <td><b>Station Name:</b></td>
                            <td>${data.station_name || '-'}</td>
                            <td><b>Panel Type:</b></td>
                            <td>${panelTypeDisplay}</td>
                        </tr>
                    </table>
                </div>

                <!-- Punch List Table -->
                <table class="punch-list-table">
                    <colgroup>
                        <col class="col-no">
                        <col class="col-desc">
                        <col class="col-by">
                        <col class="col-date">
                        <col class="col-by">
                        <col class="col-date">
                        <col class="col-remark">
                        <col class="col-by">
                        <col class="col-date">
                        <col class="col-remark">
                        <col class="col-work">
                        <col class="col-code">
                    </colgroup>
                    <tr>
                        <td colspan="12" style="background-color: #f2f2f2; text-align: center;">
                            <b>${data.reference || '-'}</b>
                        </td>
                    </tr>
                    <tr style="background-color: #f2f2f2; text-align: center;">
                        <td>No.</td>
                        <td>Description of Non-Conformance</td>
                        <td colspan="2">Finding</td>
                        <td colspan="3">Resolution</td>
                        <td colspan="3">Rechecking</td>
                        <td>Work Hrs.</td>
                        <td>Code</td>
                    </tr>
                    <tr style="background-color: #f2f2f2; text-align: center;">
                        <td></td>
                        <td></td>
                        <td>By</td>
                        <td>Date</td>
                        <td>By</td>
                        <td>Date</td>
                        <td>Remark</td>
                        <td>By</td>
                        <td>Date</td>
                        <td>Remark</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">1</td>
                        <td>
                            <textarea class="punch-input description-input" name="description" readonly style="background-color: #f2f2f2 !important;">${data.description || ''}</textarea>
                        </td>
                        <td>
                            <textarea class="punch-input by-input" name="finding_by" readonly style="background-color: #f2f2f2;">${data.finding_by || ''}</textarea>
                        </td>
                        <td>
                            <textarea class="punch-input date-input" name="finding_date" readonly style="background-color: #f2f2f2;">${data.finding_date || ''}</textarea>
                        </td>
                        <td>
                            <textarea class="punch-input by-input" name="resolution_by" readonly style="background-color: #f2f2f2;">${data.resolution_by || currentUser}</textarea>
                        </td>
                        <td>
                            <textarea class="punch-input date-input" name="resolution_date" readonly style="background-color: #f2f2f2;">${data.resolution_date ? data.resolution_date : formatDate(currentDate)}</textarea>
                        </td>
                        <td>
                            <textarea class="punch-input remark-input" name="resolution_remark">${data.resolution_remark || ''}</textarea>
                        </td>
                        <td>
                            <textarea class="punch-input by-input" name="rechecking_by" readonly style="background-color: #f2f2f2;">${data.rechecking_by || currentUser}</textarea>
                        </td>
                        <td>
                            <textarea class="punch-input date-input" name="rechecking_date" readonly style="background-color: #f2f2f2;">${data.rechecking_date ? data.rechecking_date : formatDate(currentDate)}</textarea>
                        </td>
                        <td>
                            <textarea class="punch-input remark-input" name="rechecking_remark">${data.rechecking_remark || ''}</textarea>
                        </td>
                        <td>
                            <input type="text" class="punch-input work-hrs-input" name="work_hrs" ${data.work_hrs ? 'readonly style="background-color: #f2f2f2;"' : ''} value="${data.work_hrs || ''}">
                        </td>
                        <td>
                            <input type="text" class="punch-input code-input" name="code" ${data.code ? 'readonly style="background-color: #f2f2f2;"' : ''} value="${data.code || ''}">
                        </td>
                    </tr>
                    <!-- Image Upload Section -->
                    <tr>
                        <td colspan="4" style="text-align: center;">
                            <div class="image-upload-section">
                                <h6>Findings Images</h6>
                                <div class="image-preview" id="findingsImagePreview">
                                    <!-- Preview images will be loaded here -->
                                </div>
                                <div class="upload-controls">
                                    <input type="file" id="findingsImageUpload" class="image-upload" 
                                        data-section="findings" accept="image/*" multiple 
                                        ${data.finding_images && data.finding_images.length >= 3 ? 'disabled' : ''}>
                                    <label for="findingsImageUpload" class="btn btn-sm btn-primary">
                                        Upload Images
                                    </label>
                                    <button type="button" class="btn btn-sm btn-info view-images" 
                                            data-section="findings" ${!data.finding_images ? 'style="display:none;"' : ''}>
                                        View Images
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td colspan="3" style="text-align: center;">
                            <div class="image-upload-section">
                                <h6>Resolution Images</h6>
                                <div class="image-preview" id="resolutionImagePreview"></div>
                                <div class="upload-controls">
                                    <input type="file" id="resolutionImageUpload" class="image-upload" 
                                        data-section="resolution" accept="image/*" multiple 
                                        ${data.resolution_images && data.resolution_images.length >= 3 ? 'disabled' : ''}>
                                    <label for="resolutionImageUpload" class="btn btn-sm btn-primary">
                                        Upload Images
                                    </label>
                                    <button type="button" class="btn btn-sm btn-info view-images" 
                                            data-section="resolution">View Images</button>
                                </div>
                            </div>
                        </td>
                        <td colspan="5" style="text-align: center;">
                            <div class="image-upload-section">
                                <h6>Rechecking Images</h6>
                                <div class="image-preview" id="recheckingImagePreview"></div>
                                <div class="upload-controls">
                                    <input type="file" id="recheckingImageUpload" class="image-upload" 
                                        data-section="rechecking" accept="image/*" multiple 
                                        ${data.rechecking_images && data.rechecking_images.length >= 3 ? 'disabled' : ''}>
                                    <label for="recheckingImageUpload" class="btn btn-sm btn-primary">
                                        Upload Images
                                    </label>
                                    <button type="button" class="btn btn-sm btn-info view-images" 
                                            data-section="rechecking">View Images</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="12">
                            <div class="codes-legend">
                                <div style="font-weight: bold; margin-bottom: 4px;">Codes:</div>
                                <div class="codes-grid">
                                    <div>1= PM<br>5= VCB Assembling<br>9= Faulty Equipment/part</div>
                                    <div>2= Engineering<br>6= DTO<br>10= Other (production)</div>
                                    <div>3= Purchasing<br>7= LV Assembling<br>11= delivery (e.g. with missing parts)</div>
                                    <div>4= Prefabrication<br>8= Final Assembling</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        `;
        
        // Add the form to the modal
        $('.punch-list-wrapper').html(formHtml);

        // Add additional styles
        const styles = `
            <style>
                .punch-input[readonly] {
                    cursor: not-allowed;
                    opacity: 0.8;
                }
                
                .punch-input:not([readonly]) {
                    background-color: #fff;
                    border: 1px solid #ced4da;
                }

                .punch-list-table textarea.punch-input {
                    width: 100%;
                    padding: 4px;
                    min-height: 30px;
                    font-size: 12px;
                    resize: vertical;
                }

                .punch-list-table input.punch-input {
                    width: 100%;
                    padding: 4px;
                    height: 30px;
                    font-size: 12px;
                }

                .image-upload-section {
                    padding: 10px;
                    border: 1px dashed #ccc;
                    background-color: #f8f9fa;
                    border-radius: 4px;
                }

                .image-preview {
                    min-height: 60px;
                    background-color: #fff;
                    border-radius: 4px;
                    padding: 5px;
                    margin: 5px 0;
                }

                .image-preview img {
                    width: 50px;
                    height: 50px;
                    object-fit: cover;
                    cursor: pointer;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                }

                .upload-controls {
                    margin-top: 10px;
                    display: flex;
                    gap: 10px;
                    justify-content: center;
                }

                .image-upload {
                    display: none;
                }

                .upload-controls label {
                    margin: 0;
                    cursor: pointer;
                }

                .codes-legend {
                    font-size: 10px;
                    padding: 8px;
                    background-color: #f8f9fa;
                    border-radius: 4px;
                }

                .codes-grid {
                    display: grid;
                    grid-template-columns: repeat(4, 1fr);
                    gap: 8px;
                    margin-top: 4px;
                }

                /* Image Viewer Modal */
                .image-viewer-modal {
                    display: none;
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.9);
                    z-index: 1000;
                }

                .image-viewer-content {
                    position: relative;
                    width: 80%;
                    height: 80%;
                    margin: 5% auto;
                }

                .viewer-image {
                    max-width: 100%;
                    max-height: 100%;
                    object-fit: contain;
                }

                .image-nav {
                    position: absolute;
                    top: 50%;
                    transform: translateY(-50%);
                    padding: 10px;
                    background: rgba(255,255,255,0.5);
                    cursor: pointer;
                }

                .image-nav.prev { left: 10px; }
                .image-nav.next { right: 10px; }

                .zoom-lens {
                    position: absolute;
                    border: 1px solid #d4d4d4;
                    width: 100px;
                    height: 100px;
                }

                .zoom-result {
                    position: absolute;
                    right: 0;
                    top: 0;
                    width: 300px;
                    height: 300px;
                    border: 1px solid #d4d4d4;
                    background: white;
                }

                .image-preview-item {
                    position: relative;
                    display: inline-block;
                    margin: 5px;
                }

                .image-preview-item img {
                    width: 50px;
                    height: 50px;
                    object-fit: cover;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    cursor: pointer;
                }

                .image-preview-item .delete-image {
                    position: absolute;
                    top: -5px;
                    right: -5px;
                    padding: 2px 4px;
                    font-size: 10px;
                    border-radius: 50%;
                }

                .punch-input.description-input,
                .punch-input.remark-input {
                    background-color: #fff !important;
                    border: 1px solid #ced4da !important;
                    min-height: 34px;
                    padding: 4px 8px !important;
                    resize: vertical;
                }

                .punch-input.description-input:focus,
                .punch-input.remark-input:focus {
                    border-color: #80bdff !important;
                    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
                    outline: none;
                }

                /* Add visual indicator for editable fields */
                .punch-input.description-input,
                .punch-input.remark-input {
                    position: relative;
                }

                .punch-input.description-input::before,
                .punch-input.remark-input::before {
                    content: '';
                    position: absolute;
                    top: -1px;
                    left: -1px;
                    right: -1px;
                    height: 3px;
                    background-color: #007bff;
                    opacity: 0;
                    transition: opacity 0.2s;
                }

                .punch-input.description-input:focus::before,
                .punch-input.remark-input:focus::before {
                    opacity: 1;
                }
            </style>
        `;
        
        // Add the styles if they haven't been added yet
        if (!$('#punch-list-styles').length) {
            $('head').append(`<div id="punch-list-styles">${styles}</div>`);
        }

        // Initialize textarea auto-resize
        initializeTextareaAutoResize();

        // Add validation for work hours (numbers only)
        $('.work-hrs-input:not([readonly])').on('input', function() {
            this.value = this.value.replace(/[^0-9.]/g, '');
        });

        // Add validation for code (numbers only, 1-11)
        $('.code-input:not([readonly])').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value !== '' && (this.value < 1 || this.value > 11)) {
                this.value = '';
            }
        });

        // Initialize image handling
        initializeImageHandling();

        // Load existing images if any
        if (data.id) {
            loadImages('findings');
            loadImages('resolution');
            loadImages('rechecking');
        }
    }

    function initializeImageHandling() {
        // Image upload handling
        $('.image-upload').on('change', function(e) {
            const files = e.target.files;
            const section = $(this).data('section');
            
            if (files.length > 3) {
                toastr.error('Maximum 3 images allowed');
                return;
            }

            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('images[]', files[i]);
            }
            formData.append('section', section);
            formData.append('id', $('#editPunchListForm input[name="id"]').val());

            $.ajax({
                url: '/dpm/dwc/api/checklistAPI.php?type=upload_images',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            loadImages(section);
                            toastr.success('Images uploaded successfully');
                        } else {
                            toastr.error(result.message || 'Upload failed');
                        }
                    } catch (error) {
                        console.error('Error parsing upload response:', error);
                        toastr.error('Upload failed');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Upload error:', error);
                    toastr.error('Upload failed');
                }
            });

            // Clear the file input
            $(this).val('');
        });

        // Delete image handler
        $(document).on('click', '.delete-image', function() {
            const imageId = $(this).data('id');
            const section = $(this).data('section');
            
            if (confirm('Are you sure you want to delete this image?')) {
                $.ajax({
                    url: '/dpm/dwc/api/checklistAPI.php?type=delete_image',
                    type: 'POST',
                    data: { id: imageId },
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.success) {
                                loadImages(section);
                                toastr.success('Image deleted successfully');
                            } else {
                                toastr.error(result.message || 'Delete failed');
                            }
                        } catch (error) {
                            console.error('Error parsing delete response:', error);
                            toastr.error('Delete failed');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Delete error:', error);
                        toastr.error('Delete failed');
                    }
                });
            }
        });
    }

    let sectionImages = {
        findings: [],
        resolution: [],
        rechecking: []
    };

    function loadImages(section) {
        const id = $('#editPunchListForm input[name="id"]').val();
        $.ajax({
            url: '/dpm/dwc/api/checklistAPI.php?type=get_images',
            type: 'POST',
            data: { id: id, section: section },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        // Store the images in the global object
                        sectionImages[section] = result.images || [];
                        updateImagePreviews(section, result.images);
                    }
                } catch (error) {
                    console.error('Error parsing image data:', error);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading images:', error);
            }
        });
    }

    function updateImagePreviews(section, images) {
        const preview = $(`#${section}ImagePreview`);
        preview.empty();
        
        if (images && images.length > 0) {
            images.forEach((image, index) => {
                const thumbnailPath = '/dpm/dwc/uploads/thumbnails/' + image.filename;
                const fullPath = '/dpm/dwc/uploads/' + image.filename;
                
                preview.append(`
                    <div class="image-preview-item">
                        <img src="${thumbnailPath}" 
                            data-full="${fullPath}" 
                            data-index="${index}"
                            alt="Preview"
                            onclick="showImageViewer('${section}', ${index})">
                        <button type="button" class="btn btn-sm btn-danger delete-image" 
                                data-id="${image.id}" data-section="${section}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                `);
            });
            
            // Show view button only if there are images
            $(`.view-images[data-section="${section}"]`).show();
        } else {
            // Hide view button if no images
            $(`.view-images[data-section="${section}"]`).hide();
        }
    }

    // Modify the showImageViewer function
    function showImageViewer(section, startIndex = 0) {
        // Remove any existing viewer
        $('.image-viewer-modal').remove();

        // Get images for the specific section
        const sectionImagesList = sectionImages[section] || [];
        
        if (sectionImagesList.length === 0) {
            console.error('No images found for section:', section);
            return;
        }

        // Create viewer HTML
        const viewer = $(`
            <div class="image-viewer-modal">
                <div class="image-viewer-content">
                    <div class="image-container">
                        <img class="viewer-image" src="">
                        <div class="zoom-controls">
                            <button class="zoom-in">+</button>
                            <button class="zoom-out">-</button>
                            <button class="zoom-reset">Reset</button>
                        </div>
                    </div>
                    ${sectionImagesList.length > 1 ? `
                        <div class="image-nav prev">&lt;</div>
                        <div class="image-nav next">&gt;</div>
                    ` : ''}
                    <button class="close-viewer">&times;</button>
                    <div class="image-counter"></div>
                </div>
            </div>
        `).appendTo('body');

        // Add or update styles
        if (!$('#image-viewer-styles').length) {
            $('<style id="image-viewer-styles">')
                .text(`
                    .image-viewer-modal {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0,0,0,0.9);
                        z-index: 9999;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    }
                    .image-viewer-content {
                        position: relative;
                        width: 90%;
                        height: 90%;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    }
                    .image-container {
                        position: relative;
                        width: 100%;
                        height: 100%;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        overflow: hidden;
                    }
                    .viewer-image {
                        max-width: 100%;
                        max-height: 100%;
                        object-fit: contain;
                        transform-origin: center;
                        transition: transform 0.3s ease;
                    }
                    .image-nav {
                        position: absolute;
                        top: 50%;
                        transform: translateY(-50%);
                        padding: 20px;
                        background: rgba(255,255,255,0.2);
                        color: white;
                        cursor: pointer;
                        font-size: 24px;
                        border-radius: 5px;
                        transition: background 0.3s;
                        z-index: 1000;
                    }
                    .image-nav:hover {
                        background: rgba(255,255,255,0.4);
                    }
                    .image-nav.prev { left: 20px; }
                    .image-nav.next { right: 20px; }
                    .close-viewer {
                        position: absolute;
                        top: 20px;
                        right: 20px;
                        background: none;
                        border: none;
                        color: white;
                        font-size: 30px;
                        cursor: pointer;
                        z-index: 1000;
                    }
                    .zoom-controls {
                        position: absolute;
                        bottom: 20px;
                        left: 50%;
                        transform: translateX(-50%);
                        background: rgba(255,255,255,0.2);
                        padding: 10px;
                        border-radius: 5px;
                        display: flex;
                        gap: 10px;
                        z-index: 1000;
                    }
                    .zoom-controls button {
                        background: white;
                        border: none;
                        padding: 5px 10px;
                        border-radius: 3px;
                        cursor: pointer;
                    }
                    .zoom-controls button:hover {
                        background: #eee;
                    }
                    .image-counter {
                        position: absolute;
                        bottom: 20px;
                        right: 20px;
                        color: white;
                        background: rgba(0,0,0,0.5);
                        padding: 5px 10px;
                        border-radius: 3px;
                        z-index: 1000;
                    }
                `)
                .appendTo('head');
        }

        let currentIndex = startIndex;
        let currentScale = 1;
        const viewerImage = viewer.find('.viewer-image');

        // Function to update image counter
        function updateImageCounter() {
            viewer.find('.image-counter').text(`${currentIndex + 1} / ${sectionImagesList.length}`);
        }

        // Function to show current image
        function showImage(index) {
            currentIndex = index;
            currentScale = 1;
            const imagePath = '/dpm/dwc/uploads/' + sectionImagesList[index].filename;
            viewerImage
                .css('transform', `scale(${currentScale})`)
                .attr('src', imagePath);
            updateImageCounter();
        }

        // Show initial image
        showImage(currentIndex);

        // Navigation handlers
        viewer.find('.prev').click(() => {
            currentIndex = (currentIndex - 1 + sectionImagesList.length) % sectionImagesList.length;
            showImage(currentIndex);
        });

        viewer.find('.next').click(() => {
            currentIndex = (currentIndex + 1) % sectionImagesList.length;
            showImage(currentIndex);
        });

        // Zoom handlers
        viewer.find('.zoom-in').click(() => {
            currentScale = Math.min(currentScale * 1.2, 5);
            viewerImage.css('transform', `scale(${currentScale})`);
        });

        viewer.find('.zoom-out').click(() => {
            currentScale = Math.max(currentScale / 1.2, 0.5);
            viewerImage.css('transform', `scale(${currentScale})`);
        });

        viewer.find('.zoom-reset').click(() => {
            currentScale = 1;
            viewerImage.css('transform', `scale(${currentScale})`);
        });

        // Mouse wheel zoom
        viewer.on('wheel', (e) => {
            e.preventDefault();
            if (e.originalEvent.deltaY < 0) {
                currentScale = Math.min(currentScale * 1.1, 5);
            } else {
                currentScale = Math.max(currentScale / 1.1, 0.5);
            }
            viewerImage.css('transform', `scale(${currentScale})`);
        });

        // Close viewer
        viewer.find('.close-viewer').click(() => {
            viewer.remove();
            $(document).off('keydown.imageViewer');
        });
        
        viewer.click(function(e) {
            if ($(e.target).hasClass('image-viewer-modal')) {
                viewer.remove();
                $(document).off('keydown.imageViewer');
            }
        });

        // Keyboard navigation
        $(document).on('keydown.imageViewer', function(e) {
            switch(e.key) {
                case 'ArrowLeft':
                    viewer.find('.prev').click();
                    break;
                case 'ArrowRight':
                    viewer.find('.next').click();
                    break;
                case 'Escape':
                    viewer.remove();
                    $(document).off('keydown.imageViewer');
                    break;
            }
        });
    }

    // Modify the "View Images" button click handler
    $(document).on('click', '.view-images', function() {
        const section = $(this).data('section');
        showImageViewer(section, 0); // Start with the first image
    });

    // Helper function to format date
    function formatDate(date) {
        // Return empty string if date is invalid or null
        if (!date || date === '0000-00-00 00:00:00' || date === '-0001-11-30 00:00:00') {
            return '';
        }

        try {
            const d = new Date(date);
            // Check if date is valid
            if (isNaN(d.getTime())) {
                return '';
            }
            
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            const t = d.toTimeString().split(' ')[0];
            return `${year}-${month}-${day} ${t}`;
        } catch (e) {
            console.error('Error formatting date:', e);
            return '';
        }
    }

    function initializeTextareaAutoResize() {
        $('.punch-list-row textarea').each(function() {
            this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;');
        }).on('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Preserve the original styling for remark-textarea
        $('.remark-textarea').each(function() {
            $(this).attr('style', 'width: 100%; min-height: 60px; padding: 5px; border: 1px solid #ddd; border-radius: 4px; resize: vertical;');
        });
    }
    
    // Function to collect form data
    function collectFormData() {
        const formData = {
            id: $('#editPunchListForm input[name="id"]').val(),
            description: $('#editPunchListForm textarea[name="description"]').val(),
            finding_by: $('#editPunchListForm textarea[name="finding_by"]').val(),
            finding_date: $('#editPunchListForm textarea[name="finding_date"]').val(),
            resolution_by: $('#editPunchListForm textarea[name="resolution_by"]').val(),
            resolution_date: $('#editPunchListForm textarea[name="resolution_date"]').val(),
            resolution_remark: $('#editPunchListForm textarea[name="resolution_remark"]').val(),
            rechecking_by: $('#editPunchListForm textarea[name="rechecking_by"]').val(),
            rechecking_date: $('#editPunchListForm textarea[name="rechecking_date"]').val(),
            rechecking_remark: $('#editPunchListForm textarea[name="rechecking_remark"]').val(),
            work_hrs: $('#editPunchListForm input[name="work_hrs"]').val(),
            code: $('#editPunchListForm input[name="code"]').val()
        };
        return formData;
    }

    $(document).ready(function() {
        // Initialize Semantic UI dropdowns
        $('.ui.dropdown').dropdown();
        
        const start = moment().startOf('month');
        const end = moment();

        function cb(start, end) {
            $('#dateRangeFilter span').html(start.format("DD-MM-YYYY") + ' / ' + end.format("DD-MM-YYYY"));
            start_date = moment(start, 'DD-MM-YYYY').format('YYYY-MM-DD');
            finish_date = moment(end, 'DD-MM-YYYY').format('YYYY-MM-DD');
        }

        let thisFiscalYear = new Date().getFullYear();
        const thisMonth = new Date().getMonth();
        if (thisMonth < 9) {
            thisFiscalYear -= 1;
        }
        const thisyear = new Date().getFullYear();
        let lastFiscalYear2;
        let lastFiscalYear1;
        if (thisMonth < 9) {
            lastFiscalYear1 = thisyear - 2;
            lastFiscalYear2 = thisyear - 1;
        }
        // Initialize date range picker
        $('#dateRangeFilter').daterangepicker({
            startDate: start,
            endDate: end,
            showDropdowns: true,
            minYear: 2021,
            maxYear: parseInt(moment().format('YYYY'), 10) + 1,
            alwaysShowCalendars: true,
            autoApply: true,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                "Last Year": [moment().subtract(1, "y").startOf("year"), moment().subtract(1, "y").endOf("year")],
                "Last 1 Year": [moment().subtract(365, 'days'), moment()],
                "This Fiscal Year": [new Date(thisFiscalYear, 9, 1), moment()]
            }
        }, cb);
        cb(start, end);

        $('#dateRangeFilter').val('');

        $('#dateRangeFilter').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });

        $('#dateRangeFilter').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        $('.autocomplete').on('focus', function() {
            $('.suggestions').hide(); // Hide all other suggestions
            const value = $(this).val().trim();
            if (value.length >= 2) {
                $(this).closest('.field').find('.suggestions').show();
            }
        });

        // Auto-suggestion functionality
        $('.autocomplete').on('input', function() {
            const input = $(this);
            const value = input.val().trim();
            const type = input.data('type');
            const suggestionsDiv = $(`#${type}Suggestions`);

            if (value.length >= 2) {
                $.ajax({
                    url: '/dpm/dwc/api/checklistAPI.php?type=get_suggestions',
                    type: 'POST',
                    data: {
                        search: value,
                        field: type
                    },
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.success && data.data) {
                                showSuggestions(data.data, suggestionsDiv, input);
                            }
                        } catch (e) {
                            console.error('Error parsing suggestions:', e);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching suggestions:', error);
                    }
                });
            } else {
                suggestionsDiv.hide();
            }
        });

        function showSuggestions(data, suggestionsDiv, input) {
            suggestionsDiv.empty();
            
            if (data.length > 0) {
                data.forEach(item => {
                    const div = $('<div>')
                        .addClass('suggestion-item')
                        .text(item.value)
                        .on('click', function() {
                            input.val(item.value);
                            suggestionsDiv.hide();
                        });
                    suggestionsDiv.append(div);
                });
                
                // Position the suggestions
                const inputHeight = input.closest('.ui.input').outerHeight();
                suggestionsDiv.css({
                    'width': input.closest('.ui.input').outerWidth(),
                    'top': inputHeight + 'px'
                }).show();
            } else {
                suggestionsDiv.hide();
            }
        }

        $('<style>')
            .text(`
                .field {
                    position: relative;
                }
                .suggestions {
                    position: absolute;
                    top: 100%;
                    left: 0;
                    right: 0;
                    z-index: 1000;
                    background: white;
                    border-radius: 4px;
                    max-height: 200px;
                    overflow-y: auto;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .suggestion-item {
                    padding: 8px 12px;
                    cursor: pointer;
                    transition: background-color 0.2s;
                }
                .suggestion-item:hover {
                    background-color: #f5f5f5;
                }
            `)
            .appendTo('head');

        // Hide suggestions when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.field').length) {
                $('.suggestions').hide();
            }
        });

        function renderWithDash(data) {
            if (data === null || data === undefined || String(data).trim() === '') {
                return '-';
            }
            return data;
        }

        $('head').append(`
            <style>
                .dataTables_processing {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    width: 100%;
                    height: 60px;
                    margin-left: -50%;
                    margin-top: -30px;
                    padding-top: 20px;
                    text-align: center;
                    font-size: 1.2em;
                    background: rgba(255, 255, 255, 0.8) !important; /* Semi-transparent background */
                    backdrop-filter: blur(5px) !important; /* Add blur effect */
                    -webkit-backdrop-filter: blur(5px) !important; /* For Safari */
                    z-index: 1000;
                    border-radius: 5px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                
                .dataTables_processing:after {
                    content: 'Loading...'; /* Change text to "Loading..." */
                    display: block;
                    font-weight: bold;
                    color: #1ab394; /* Match your theme color */
                }
                
                /* Hide the original "Processing" text */
                .dataTables_processing > div {
                    display: none;
                }
            </style>
        `);

        var table = $('#resultsTable').DataTable({
            processing: true,
            language: {
                processing: '<div class="loading-spinner"><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i></div>'
            },
            serverSide: true,
            pageLength: 10, // Number of rows per page
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]], // Show entries options
            order: [[11, 'desc']], // Default sort by date (column 11 is now Finding Date)
            scrollX: true,
            scrollY: '60vh',
            scrollCollapse: true,
            fixedHeader: true,
            ajax: {
                url: '/dpm/dwc/api/checklistAPI.php?type=get_checklist_results',
                type: 'POST',
                data: function(d) {
                    d.applyingFilters = window.applyingFilters ? 'true' : 'false';
                    if (window.applyingFilters) {
                        d.orderFilter = $('#orderFilter').val();
                        d.itemFilter = $('#itemFilter').val();
                        d.checklistFilter = $('#checklistFilter').val();
                        d.findingByFilter = $('#findingByFilter').val();
                        d.dateRangeFilter = $('#dateRangeFilter').val();
                        d.stationFilter = $('#stationFilter').val();
                    }
                }
            },
            columns: [
                { data: 'order_no', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'panel_type', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'item_no', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'location_name', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'typical_name', searchable: true, render: renderWithDash, className: 'text-center' },
                {
                    data: null,
                    searchable: false,
                    render: function(data, type, row) {
                        // Display sub_item only if panel_type is "Component"
                        if (data.panel_type === 'Component' && data.sub_item) {
                            return data.sub_item;
                        }
                        return '-';
                    },
                    className: 'text-center'
                },
                { data: 'station_name', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'checklist_name', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'reference', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'description', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'finding_by', searchable: true, render: renderWithDash, className: 'text-center' },
                { 
                    data: 'finding_date',
                    searchable: true,
                    render: function(data) {
                        return data ? moment(data).format('YYYY-MM-DD HH:mm:ss') : '-';
                    },
                    className: 'text-center'
                },
                {
                    data: null,
                    searchable: false,
                    render: function(data, type, row) {
                        return `<button class="btn btn-primary btn-sm edit-btn" 
                                data-id="${data.id}">
                                <i class="fa fa-edit"></i> Edit
                            </button>`;
                    },
                    className: 'text-center'
                }
            ],
            responsive: false,
            autoWidth: false,
            columnDefs: [
                { 
                    targets: '_all',
                    className: 'dt-head-center dt-body-center'
                },
                {
                    targets: [9], // Description column
                    width: '200px',
                    className: 'dt-head-center dt-body-left'
                }
            ],
            dom: '<"row"<"col-sm-12 col-md-3"l><"col-sm-12 col-md-6 text-center"B><"col-sm-12 col-md-3"f>>' +
                '<"row"<"col-sm-12"tr>>' +
                '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    action: function(e, dt, button, config) {
                        $('body').append('<div class="export-loading">Preparing Excel Export...</div>');

                        $.ajax({
                            url: '/dpm/dwc/api/checklistAPI.php',
                            type: 'POST',
                            data: {
                                type: 'get_all_data',
                                orderFilter: $('#orderFilter').val(),
                                itemFilter: $('#itemFilter').val(),
                                checklistFilter: $('#checklistFilter').val(),
                                findingByFilter: $('#findingByFilter').val(),
                                dateRangeFilter: $('#dateRangeFilter').val(),
                                stationFilter: $('#stationFilter').val(),
                                applyingFilters: window.applyingFilters ? 'true' : 'false'
                            },
                            success: function(response) {
                                try {
                                    const data = JSON.parse(response);
                                    
                                    if (data.success && data.data) {
                                        // Create new workbook
                                        const workbook = new ExcelJS.Workbook();
                                        const worksheet = workbook.addWorksheet('Checklist Results');

                                        // Define headers
                                        const headers = [
                                            'Sales Order No',
                                            'Panel Type',
                                            'Item Number',
                                            'Panel Name',
                                            'Typical Name',
                                            'Sub Item',
                                            'Station',
                                            'Checklist Name',
                                            'Reference',
                                            'Description',
                                            'Finding By',
                                            'Finding Date'
                                        ];

                                        // Add headers
                                        worksheet.addRow(headers);

                                        // Add data rows
                                        data.data.forEach(row => {
                                            const subItem = row.panel_type === 'Component' && row.sub_item ? row.sub_item : '-';
                                            worksheet.addRow([
                                                row.order_no || '',
                                                row.panel_type || '',
                                                row.item_no || '',
                                                row.location_name || '',
                                                row.typical_name || '',
                                                subItem,
                                                row.station_name || '',
                                                row.checklist_name || '',
                                                row.reference || '',
                                                row.description || '',
                                                row.finding_by || '',
                                                row.finding_date ? moment(row.finding_date).format('YYYY-MM-DD HH:mm:ss') : ''
                                            ]);
                                        });

                                        // Set column widths
                                        worksheet.columns = [
                                            { width: 15 }, // Sales Order
                                            { width: 15 }, // Panel Type
                                            { width: 12 }, // Item Number
                                            { width: 15 }, // Panel Name
                                            { width: 15 }, // Typical Name
                                            { width: 12 }, // Sub Item
                                            { width: 15 }, // Station
                                            { width: 20 }, // Checklist Name
                                            { width: 15 }, // Reference
                                            { width: 40 }, // Description
                                            { width: 15 }, // Finding By
                                            { width: 20 }  // Finding Date
                                        ];

                                        // Style header row
                                        const headerRow = worksheet.getRow(1);
                                        headerRow.font = { bold: true, size: 12, color: { argb: 'FFFFFF' } };
                                        headerRow.fill = {
                                            type: 'pattern',
                                            pattern: 'solid',
                                            fgColor: { argb: '4472C4' }
                                        };
                                        headerRow.alignment = { horizontal: 'center', vertical: 'middle' };
                                        headerRow.height = 25;

                                        // Apply borders and center alignment to all cells
                                        worksheet.eachRow((row, rowNumber) => {
                                            row.eachCell((cell) => {
                                                cell.border = {
                                                    top: { style: 'thin' },
                                                    left: { style: 'thin' },
                                                    bottom: { style: 'thin' },
                                                    right: { style: 'thin' }
                                                };
                                                cell.alignment = { horizontal: 'center', vertical: 'middle' };
                                                
                                                // Set font size for data rows
                                                if (rowNumber > 1) {
                                                    cell.font = { size: 11 };
                                                }
                                            });
                                        });

                                        // Generate Excel file
                                        workbook.xlsx.writeBuffer().then(buffer => {
                                            const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                                            const url = window.URL.createObjectURL(blob);
                                            const a = document.createElement('a');
                                            a.href = url;
                                            a.download = 'Checklist_Results_' + moment().format('YYYY-MM-DD_HH-mm-ss') + '.xlsx';
                                            a.click();
                                            window.URL.revokeObjectURL(url);
                                            $('.export-loading').remove();
                                        });
                                    } else {
                                        toastr.error('No data available for export');
                                        $('.export-loading').remove();
                                    }
                                } catch (error) {
                                    console.error('Error creating Excel:', error);
                                    toastr.error('Failed to create Excel file');
                                    $('.export-loading').remove();
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Ajax error:', error);
                                toastr.error('Failed to fetch data for Excel');
                                $('.export-loading').remove();
                            }
                        });
                    }
                },
                {
                    extend: 'pdf',
                    text: '<i class="fa fa-file-pdf-o"></i> PDF',
                    className: 'btn btn-danger btn-sm',
                    action: function(e, dt, button, config) {
                        $('body').append('<div class="export-loading">Preparing PDF Export...</div>');

                        $.ajax({
                            url: '/dpm/dwc/api/checklistAPI.php',
                            type: 'POST',
                            data: {
                                type: 'get_all_data',
                                orderFilter: $('#orderFilter').val(),
                                itemFilter: $('#itemFilter').val(),
                                checklistFilter: $('#checklistFilter').val(),
                                findingByFilter: $('#findingByFilter').val(),
                                dateRangeFilter: $('#dateRangeFilter').val(),
                                stationFilter: $('#stationFilter').val(),
                                applyingFilters: window.applyingFilters ? 'true' : 'false'
                            },
                            success: function(response) {
                                try {
                                    const data = JSON.parse(response);
                                    
                                    if (data.success && data.data) {
                                        const docDefinition = {
                                            pageSize: 'A4',
                                            pageOrientation: 'landscape',
                                            pageMargins: [40, 60, 40, 60], // Increased margins
                                            header: function(currentPage, pageCount) {
                                                return {
                                                    columns: [
                                                        {
                                                            text: 'Checklist Results',
                                                            alignment: 'center',
                                                            fontSize: 16,
                                                            bold: true,
                                                            margin: [0, 20, 0, 0]
                                                        }
                                                    ]
                                                };
                                            },
                                            footer: function(currentPage, pageCount) {
                                                return {
                                                    columns: [
                                                        {
                                                            text: moment().format('YYYY-MM-DD HH:mm:ss'),
                                                            alignment: 'left',
                                                            margin: [40, 0],
                                                            fontSize: 8
                                                        },
                                                        {
                                                            text: `Page ${currentPage} of ${pageCount}`,
                                                            alignment: 'right',
                                                            margin: [0, 0, 40, 0],
                                                            fontSize: 8
                                                        }
                                                    ]
                                                };
                                            },
                                            content: [
                                                {
                                                    table: {
                                                        headerRows: 1,
                                                        widths: ['auto', 'auto', 'auto', 'auto', 'auto', 'auto', 'auto', 'auto', 'auto', 'auto', 'auto', 'auto'],
                                                        body: [
                                                            // Headers
                                                            [
                                                                'Sales Order No',
                                                                'Panel Type',
                                                                'Item Number',
                                                                'Panel Name',
                                                                'Typical Name',
                                                                'Sub Item',
                                                                'Station',
                                                                'Checklist Name',
                                                                'Reference',
                                                                'Description',
                                                                'Finding By',
                                                                'Finding Date'
                                                            ].map(text => ({
                                                                text: text,
                                                                bold: true,
                                                                fontSize: 10,
                                                                fillColor: '#4472C4',
                                                                color: 'white',
                                                                alignment: 'center'
                                                            })),
                                                            // Data rows
                                                            ...data.data.map(row => {
                                                                const subItem = row.panel_type === 'Component' && row.sub_item ? row.sub_item : '-';
                                                                return [
                                                                    row.order_no || '',
                                                                    row.panel_type || '',
                                                                    row.item_no || '',
                                                                    row.location_name || '',
                                                                    row.typical_name || '',
                                                                    subItem,
                                                                    row.station_name || '',
                                                                    row.checklist_name || '',
                                                                    row.reference || '',
                                                                    row.description || '',
                                                                    row.finding_by || '',
                                                                    row.finding_date ? moment(row.finding_date).format('YYYY-MM-DD HH:mm:ss') : ''
                                                                ].map(text => ({
                                                                    text: text,
                                                                    fontSize: 8,
                                                                    alignment: 'center'
                                                                }));
                                                            })
                                                        ]
                                                    },
                                                    layout: {
                                                        hLineWidth: function(i, node) {
                                                            return (i === 0 || i === 1 || i === node.table.body.length) ? 2 : 1;
                                                        },
                                                        vLineWidth: function(i, node) {
                                                            return (i === 0 || i === node.table.widths.length) ? 2 : 1;
                                                        },
                                                        hLineColor: function(i, node) {
                                                            return (i === 0 || i === 1 || i === node.table.body.length) ? '#aaa' : '#ddd';
                                                        },
                                                        vLineColor: function(i, node) {
                                                            return (i === 0 || i === node.table.widths.length) ? '#aaa' : '#ddd';
                                                        },
                                                        paddingTop: function(i) { return 4; },
                                                        paddingBottom: function(i) { return 4; }
                                                    }
                                                }
                                            ],
                                            defaultStyle: {
                                                fontSize: 9
                                            }
                                        };

                                        pdfMake.createPdf(docDefinition).download('Checklist_Results_' + 
                                            moment().format('YYYY-MM-DD_HH-mm-ss') + '.pdf');
                                    } else {
                                        toastr.error('No data available for export');
                                    }
                                } catch (error) {
                                    console.error('Error creating PDF:', error);
                                    toastr.error('Failed to create PDF');
                                }
                                
                                $('.export-loading').remove();
                            },
                            error: function(xhr, status, error) {
                                console.error('Ajax error:', error);
                                toastr.error('Failed to fetch data for PDF');
                                $('.export-loading').remove();
                            }
                        });
                    }
                }
            ],    
            drawCallback: function(settings) {
                $(window).trigger('resize');
            },
            initComplete: function(settings, json) {
                $(window).trigger('resize');
            }
        });

        // Add this resize handler
        $(window).on('resize', function() {
            clearTimeout(window.resizeTimeout);
            window.resizeTimeout = setTimeout(function() {
                table.columns.adjust();
            }, 100);
        });

        // Adjust when tab is changed
        $('a[data-toggle="tab"]').on('shown.bs.tab', function() {
            if (table) {
                table.columns.adjust();
            }
        });

        // Apply Filters button click handler
        $('#applyFilters').click(function() {
            window.applyingFilters = true;
            table.ajax.reload();
        });

        // Reset Filters button click handler
        $('#resetFilters').click(function() {
            $('.filter-input').val('');
            $('.ui.dropdown').dropdown('clear');
            $('#checklistFilter').val('');
            window.applyingFilters = false;
            table.ajax.reload();
        });

        // Handle edit button click
        $('#resultsTable').on('click', '.edit-btn', function() {
            const id = $(this).data('id');
            loadPunchListData(id);
        });

        // Load punch list data for editing
        function loadPunchListData(id) {
            $.ajax({
                url: '/dpm/dwc/api/checklistAPI.php?type=get_punch_list_detail',
                type: 'POST',
                data: { id: id },
                success: function(response) {
                    const data = JSON.parse(response);
                    populateEditModal(data);
                    $('#editModal').modal('show');
                },
                error: function(xhr, status, error) {
                    toastr.error('Failed to load punch list data');
                }
            });
        }

        // Add visual feedback for editable fields
        $(document).on('focus', '.punch-input.description-input, .punch-input.remark-input', function() {
            $(this).addClass('is-editing');
        });

        $(document).on('blur', '.punch-input.description-input, .punch-input.remark-input', function() {
            $(this).removeClass('is-editing');
        });
        
        function validateSaveForm() {
            let isValid = true;
            const description = $('#editPunchListForm textarea[name="description"]').val().trim();
            
            if (!description) {
                toastr.error('Description is required');
                isValid = false;
            }

            return isValid;
        }

        function validateSubmitForm() {
            let isValid = true;
            const description = $('#editPunchListForm textarea[name="description"]').val().trim();
            const resolution_remark = $('#editPunchListForm textarea[name="resolution_remark"]').val().trim();
            const rechecking_remark = $('#editPunchListForm textarea[name="rechecking_remark"]').val().trim();
            const work_hrs = $('#editPunchListForm input[name="work_hrs"]').val().trim();
            const code = $('#editPunchListForm input[name="code"]').val().trim();
            
            if (!description) {
                toastr.error('Description is required');
                isValid = false;
            }

            if (!resolution_remark) {
                toastr.error('Resolution remark is required');
                isValid = false;
            }
            
            if (!rechecking_remark) {
                toastr.error('Rechecking remark is required');
                isValid = false;
            }

            if (!work_hrs) {
                toastr.error('Work hrs is required');
                isValid = false;
            }

            if (!code) {
                toastr.error('Code is required');
                isValid = false;
            }

            return isValid;
        }

        // Save changes
        $('#saveChanges').click(function() {
            if (!validateSaveForm()) {
                return;
            }
            const formData = collectFormData();
            formData.action = 'save';
            $.ajax({
                url: '/dpm/dwc/api/checklistAPI.php?type=update_punch_list',
                type: 'POST',
                data: formData,
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            $('#editModal').modal('hide');
                            table.ajax.reload();
                            toastr.success(result.message);
                        } else {
                            toastr.error(result.message || 'Failed to save changes');
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        toastr.error('Failed to process server response');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ajax error:', error);
                    toastr.error('Failed to save changes: ' + error);
                }
            });
        });

        // Submit changes
        $('#submitChanges').click(function() {
            if (!validateSubmitForm()) {
                return;
            }
            const formData = collectFormData();
            formData.action = 'submit';
            $.ajax({
                url: '/dpm/dwc/api/checklistAPI.php?type=update_punch_list',
                type: 'POST',
                data: formData,
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            $('#editModal').modal('hide');
                            table.ajax.reload();
                            toastr.success(result.message);
                        } else {
                            toastr.error(result.message || 'Failed to save changes');
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        toastr.error('Failed to process server response');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Ajax error:', error);
                    toastr.error('Failed to save changes: ' + error);
                }
            });
        });

        // Initialize the modal
        $('#editModal').modal({
            backdrop: 'static',
            keyboard: false,
            show: false
        });

        // Add modal event handlers
        $('#editModal').on('shown.bs.modal', function () {
            $(this).find('textarea:first').focus();
        });

        $('#editModal').on('hidden.bs.modal', function () {
            $('.punch-list-wrapper').empty();
        });
    });
</script>
</body>
</html>