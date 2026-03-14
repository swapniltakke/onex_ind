<!DOCTYPE html>
<html>
<?php
include_once '../core/index.php';
$project = $_GET["project"] ?: 0;
SharedManager::saveLog("log_material_tracking", "View for Project: $project");
$menu_header_display = 'Material Tracking';
$material_tracking = "1";
$currentUser = isset($_SESSION['username']) ? $_SESSION['username'] : '';
// Get the project search input value from the session or GET parameter
$projectSearchInput = '';
$isSearchMode = isset($_GET['search']) && $_GET['search'] === '1';
// Only set projectSearchInput if not in search mode
if (!$isSearchMode) {
    SharedManager::checkAuthToModule(12);
    if (isset($_SESSION['projectSearchInput'])) {
        $projectSearchInput = $_SESSION['projectSearchInput'];
    } elseif (isset($_SESSION['selected_project'])) {
        $projectSearchInput = $_SESSION['selected_project'];
    } elseif (isset($_GET['projectSearchInput'])) {
        $projectSearchInput = $_GET['projectSearchInput'];
    }
} else {
    SharedManager::checkAuthToModule(13);
}
?>
<head>
    <title>OneX | Material Tracking</title>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
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

    .pad-top {
        padding-top: 12px;
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
        margin-top: 10px !important;
    }

    .date-none {
        display: none;
    }

    .ui.search > .results {
        margin: auto !important;
    }

    .ui.tiny.button,
    .ui.tiny.buttons .button,
    .ui.tiny.buttons .or {
        font-size: 14px !important;
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

    /*old*/
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
        padding: 9px 8px !important;
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
        border-top: none;
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
        table-layout: fixed;
    }

    /* Update the column widths */
    #resultsTable th,
    #resultsTable td {
        white-space: normal;
        word-wrap: break-word;
    }

    /* Specific column widths */
    #resultsTable th:nth-child(11), /* Short Text column */
    #resultsTable td:nth-child(11) {
        width: 250px !important;
        white-space: normal !important;
        word-wrap: break-word !important;
    }

    /* Default width for other columns */
    #resultsTable th,
    #resultsTable td {
        width: 120px !important;
    }

    /* Ensure the filters section matches table width */
    #customFilterParent,
    .ui.form .ui.grid {
        width: 100% !important;
        margin: 0 !important;
        display: block !important;
    }

    /* Ensure header text doesn't wrap */
    #resultsTable thead th {
        white-space: nowrap !important;
        background-color: #f8f9fa !important;
        font-weight: bold !important;
    }

    #resultsTable tbody tr:hover {
        background-color: #f5f5f5 !important;
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
        overflow-x: auto;
        margin-bottom: 10px;
    }
    
    /* Improve scrollbar appearance */
    .dataTables_scrollBody::-webkit-scrollbar {
        height: 8px;
    }

    .dataTables_scrollBody::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
        background: #555;
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
        background-color: #408d89ff;
        border-color: #408d89ff;
        transform: translateY(-1px);
        transition: all 0.2s ease;
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

    /* Add horizontal scroll indicator */
    .dataTables_wrapper {
        position: relative;
        overflow: hidden;
        overflow-x: auto;
        /* width: 100%; */
    }

    div.dataTables_wrapper div.dataTables_length .ui.selection.dropdown {
        width: 75px !important;
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

    .cell-content {
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cell-content-wrap {
        max-width: 250px;
        white-space: normal;
        word-wrap: break-word;
    }

    /* Add these to your existing styles */
    .ui.checkbox {
        margin-top: 8px;
    }
    
    .ui.checkbox label {
        font-size: 12px;
        cursor: pointer;
    }
    
    .ui.checkbox input[type="checkbox"] {
        cursor: pointer;
    }

    .scanner-icon {
        cursor: pointer;
        margin-left: 10px;
        vertical-align: middle;
    }
    .scanner-icon img {
        width: 24px;
        height: 24px;
    }

    /* Add these styles to your existing <style> block */
    .search-mode {
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }

    .search-mode #page-wrapper {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }

    /* Adjust the wrapper when in search mode */
    .search-mode .wrapper-content {
        padding-top: 60px !important;
    }

    /* Additional responsive adjustments for search mode */
    @media (max-width: 768px) {
        .search-mode #page-wrapper {
            min-height: auto !important;
        }
    }

    .ui.fixed.menu {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        display: flex !important;
        align-items: center !important;
        position: fixed !important;
    }

    /* .ui.fixed.menu {
        position: fixed !important;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
    } */

    .ui.fixed.menu .ui.center.aligned.container {
        position: relative !important;
        max-width: none !important;
    }

    .ui.fixed.menu h3 {
        font-size: 18px !important;
        font-weight: 600 !important;
        margin: 0 !important;
        padding: 0 !important;
        color: #00b5ad !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
    }

    .ui.fixed.menu .logo {
        max-height: 45px !important;
        width: auto !important;
        transition: transform 0.2s ease !important;
        vertical-align: middle !important;
    }

    .ui.center.aligned.container {
        max-width: none !important;
    }

    /* Ensure the logo container doesn't affect layout flow */
    .ui.fixed.menu .ui.centered.small.image {
        position: absolute !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        margin: 0 !important;
    }

    /* Adjust the container padding in search mode */
    .search-mode .card-body {
        padding-top: 20px !important;
    }

    /* Ensure the table header stays below the fixed header */
    .search-mode .fixedHeader-floating {
        top: 50px !important; /* Match the header height */
    }

    .fixedHeader-floating {
        z-index: 1050 !important;
        background: #fff !important;
        box-shadow: 0 2px 3px rgba(0,0,0,0.1) !important;
    }
    .dataTables_scrollHead {
        position: relative;
        z-index: 100 !important;
    }
</style>
<body>
<div id="wrapper" class="<?php echo $isSearchMode ? 'search-mode' : ''; ?>">
    <?php 
    if (!$isSearchMode) {
        $activePage = '/material_tracking.php';
        include_once '../shared/sidebar.php';
    }
    ?>
    <div id="page-wrapper" class="gray-bg" style="margin-top: 0em;">
        <?php
        if ($isSearchMode) {
            // Add the custom header for search mode
            echo '<div class="ui fixed menu" style="padding: 0; color: teal; z-index: 1000; background: white; height: 60px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div class="ui center aligned container" style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: none; padding: 0 20px;">
                        <div style="flex: 1; text-align: left;">
                            <h3 style="margin: 0; color: #00b5ad; font-size: 18px; font-weight: 600;">Material Tracking</h3>
                        </div>
                        <div style="position: absolute; left: 50%; transform: translateX(-50%); text-align: center;">
                            <a href="/">
                                <img class="logo" src="/images/onex.png" style="max-height: 45px;">
                            </a>
                        </div>
                        <div style="flex: 1;"></div>
                    </div>
                </div>';
        }
        if (!$isSearchMode) {
            include_once 'header.php';
        }
        ?>
        <div class="wrapper wrapper-content">
            <div class="row" style="margin-top: <?php echo $isSearchMode ? '1em' : '-1em'; ?>;">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- Filters section -->
                            <div class="ui form">
                                <div class="ui grid" style="margin: 0;">
                                    <div class="three wide column" style="padding: 5px;">
                                        <div class="field">
                                            <label>Tracking Number</label>
                                            <div class="ui input">
                                                <input type="text" class="filter-input autocomplete" id="trackingFilter" data-type="tracking" placeholder="Enter Sales Order No">
                                            </div>
                                            <div class="suggestions" id="trackingSuggestions"></div>
                                        </div>
                                    </div>
                                    <div class="three wide column" style="padding: 5px;">
                                        <div class="field">
                                            <label>Vendor Name</label>
                                            <div class="ui input">
                                                <input type="text" class="filter-input autocomplete" id="vendorFilter" data-type="vendor" placeholder="Enter Vendor Name">
                                            </div>
                                            <div class="suggestions" id="vendorSuggestions"></div>
                                        </div>
                                    </div>
                                    <!-- Add this after your existing filter columns -->
                                    <div class="three wide column" style="padding: 5px;">
                                        <div class="field">
                                            <label>&nbsp;</label>
                                            <div class="ui checkbox">
                                                <input type="checkbox" id="stillToBeDeliveredFilter">
                                                <label for="stillToBeDeliveredFilter">Still To Be Delivered</label>
                                            </div>
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
                            <div class="table-responsive" style="width: 100%; overflow-x: auto;">
                                <table id="resultsTable" class="table table-striped table-bordered" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Tracking Number</th>
                                            <th>Purchase Order No</th>
                                            <th>Purchase Item</th>
                                            <th>Material</th>
                                            <th>Short Text</th>
                                            <th>PO Quantity</th>
                                            <th>Quantity Delivered</th>
                                            <th>Still To Be Delivered</th>
                                            <th>Delivery Date</th>
                                            <th>Delivery Date Vendor Confirmation</th>
                                            <th>Sales Order No</th>
                                            <th>Production Order No</th>
                                            <th>Vendor Name</th>
                                            <th>Vendor Code</th>
                                            <th>PO Created By</th>
                                            <th>Our Reference</th>
                                            <th>PO Date</th>
                                            <th>Account Assignment Category</th>
                                            <th>Net Value PO Currency</th>
                                            <th>Currency</th>
                                            <th>Purch Grp</th>
                                            <th>Deletion IND</th>
                                            <th>Storage No</th>
                                            <th>Storage Location Desc</th>
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
    <?php $footer_display = 'Material Tracking';
        if (!$isSearchMode) {
            include_once '../../assemblynotes/shared/footer.php';
        } 
    ?>
</div>

<!-- Mainly scripts -->
<?php include_once '../../assemblynotes/shared/headerSemanticScripts.php' ?>
<script src="../../shared/shared.js"></script>
<script>
    // Entire JavaScript from the first file goes here
    const projectnumber = getUrlParameters()["project"];
    let activeAjaxRequests = 0;
    var currentUser = <?php echo json_encode($currentUser); ?>;   
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
    
    $(document).ready(function() {
        // Check for search mode
        const isSearchMode = new URLSearchParams(window.location.search).get('search') === '1';

        // Initialize Semantic UI dropdowns
        $('.ui.dropdown').dropdown();
        
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
                    url: '/dpm/dwc/materialsearch/api/materialtracking.php?type=get_suggestions',
                    type: 'POST',
                    data: {
                        search: value,
                        field: type
                    },
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.success && data.data) {
                                // Filter suggestions to match exact sequence of characters
                                const filteredData = data.data.filter(item => {
                                    const itemValue = item.value.toString().toLowerCase();
                                    const searchValue = value.toLowerCase();
                                    return itemValue.includes(searchValue);
                                });

                                // Only show suggestions if there are matches
                                if (filteredData.length > 0) {
                                    showSuggestions(filteredData, suggestionsDiv, input);
                                } else {
                                    suggestionsDiv.hide();
                                }
                            }
                        } catch (e) {
                            console.error('Error parsing suggestions:', e);
                            suggestionsDiv.hide();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching suggestions:', error);
                        suggestionsDiv.hide();
                    }
                });
            } else {
                suggestionsDiv.hide();
            }
        });

        function showSuggestions(data, suggestionsDiv, input) {
            suggestionsDiv.empty();
            
            if (data.length > 0) {
                const inputValue = input.val().toLowerCase();
                
                data.forEach(item => {
                    const itemValue = item.value.toString();
                    // Create a highlighted version of the suggestion
                    const highlightedText = highlightMatch(itemValue, inputValue);
                    
                    const div = $('<div>')
                        .addClass('suggestion-item')
                        .html(highlightedText) // Use html instead of text to show highlighting
                        .on('click', function() {
                            input.val(itemValue);
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

        function highlightMatch(text, search) {
            if (!search) return text;
            
            const searchLower = search.toLowerCase();
            const textLower = text.toLowerCase();
            const index = textLower.indexOf(searchLower);
            
            if (index >= 0) {
                return text.substring(0, index) +
                    '<span class="highlight">' +
                    text.substring(index, index + search.length) +
                    '</span>' +
                    text.substring(index + search.length);
            }
            return text;
        }

        $('<style>')
            .text(`
                .suggestion-item .highlight {
                    background-color: #f5f5f5;
                    font-weight: bold;
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
        
         // Add this near the beginning of your document.ready function
        const initialProjectSearch = <?php echo json_encode($projectSearchInput); ?>;
        
        // Only set initial filter values if not in search mode
        if (!isSearchMode) {
            if (initialProjectSearch) {
                $('#trackingFilter').val(initialProjectSearch);
                window.applyingFilters = true;
            }
        } else {
            // Clear all filters in search mode
            $('#trackingFilter').val('');
            $('#vendorFilter').val('');
            $('#stillToBeDeliveredFilter').prop('checked', false);
            window.applyingFilters = false;
        }
        
        var table = $('#resultsTable').DataTable({
            processing: true,
            language: {
                processing: '<div class="loading-spinner"><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i></div>'
            },
            serverSide: true,
            pageLength: isSearchMode ? 25 : 10, // Different page length for search mode
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]], // Show entries options
            order: [[8, 'desc']], // Default sort by date
            scrollX: true,
            scrollY: '60vh',
            scrollCollapse: true,
            autoWidth: false,
            responsive: false,
            fixedHeader: {
                header: true,
                headerOffset: isSearchMode ? 50 : $('.ui.fixed.menu').height()
            },
            ajax: {
                url: '/dpm/dwc/materialsearch/api/materialtracking.php?type=get_material_tracking',
                type: 'POST',
                data: function(d) {
                    d.applyingFilters = window.applyingFilters ? 'true' : 'false';
                    // Always send filter values when filters are being applied
                    if (window.applyingFilters) {
                        d.trackingFilter = $('#trackingFilter').val();
                        d.vendorFilter = $('#vendorFilter').val();
                        d.stillToBeDeliveredFilter = $('#stillToBeDeliveredFilter').is(':checked') ? 'true' : 'false';
                    }
                    d.isInitialLoad = (!isSearchMode && initialProjectSearch) ? 'true' : 'false';
                    d.isSearchMode = isSearchMode ? 'true' : 'false';
                }
            },
            columns: [
                { data: 'tracking_number', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'purchase_order_no', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'purchase_item', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'material', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'short_text', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'po_quantity', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'quantity_delivered', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'still_to_be_delivered', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'delivery_date', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'delivery_date_vendor_confirmation', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'sales_order_no', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'production_order_no', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'vendor_name', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'vendor_code', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'po_created_by', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'our_reference', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'po_date', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'account_assignment_category', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'net_value_po_currency', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'currency', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'purch_grp', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'deletion_ind', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'storage_no', searchable: true, render: renderWithDash, className: 'text-center' },
                { data: 'storage_location_desc', searchable: true, render: renderWithDash, className: 'text-center' }
            ],
            columnDefs: [
                {
                    targets: '_all',
                    className: 'dt-head-center dt-body-center',
                    width: '120px',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return data ? '<div class="cell-content">' + data + '</div>' : '-';
                        }
                        return data;
                    }
                },
                {
                    targets: [10], // Short Text column (0-based index)
                    width: '250px',
                    className: 'dt-head-center dt-body-left',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return data ? '<div class="cell-content-wrap">' + data + '</div>' : '-';
                        }
                        return data;
                    }
                }
            ],
            dom: '<"row"<"col-sm-12 col-md-3"l><"col-sm-12 col-md-6 text-center pad-top"B><"col-sm-12 col-md-3"f>>' +
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
                            url: '/dpm/dwc/materialsearch/api/materialtracking.php',
                            type: 'POST',
                            data: {
                                type: 'get_all_data',
                                trackingFilter: $('#trackingFilter').val(),
                                vendorFilter: $('#vendorFilter').val(),
                                applyingFilters: window.applyingFilters ? 'true' : 'false'
                            },
                            success: function(response) {
                                try {
                                    const data = JSON.parse(response);
                                    
                                    if (data.success && data.data) {
                                        // Create new workbook
                                        const workbook = new ExcelJS.Workbook();
                                        const worksheet = workbook.addWorksheet('Material Tracking');

                                        // Define headers
                                        const headers = [
                                            'Tracking Number',
                                            'Purchase Order No',
                                            'Purchase Item',
                                            'Material',
                                            'Short Text',
                                            'PO Quantity',
                                            'Quantity Delivered',
                                            'Still To Be Delivered',
                                            'Delivery Date',
                                            'Delivery Date Vendor Confirmation',
                                            'Sales Order No',
                                            'Production Order No',
                                            'Vendor Name',
                                            'Vendor Code',
                                            'PO Created By',
                                            'Our Reference',
                                            'PO Date',
                                            'Account Assignment Category',
                                            'Net Value PO Currency',
                                            'Currency',
                                            'Purch Grp',
                                            'Deletion IND',
                                            'Storage No',
                                            'Storage Location Desc'
                                        ];

                                        // Add headers
                                        worksheet.addRow(headers);

                                        // Add data rows
                                        data.data.forEach(row => {
                                            worksheet.addRow([
                                                row.tracking_number || '',
                                                row.purchase_order_no || '',
                                                row.purchase_item || '',
                                                row.material || '',
                                                row.short_text || '',
                                                row.po_quantity || '',
                                                row.quantity_delivered || '',
                                                row.still_to_be_delivered || '',
                                                row.delivery_date || '',
                                                row.delivery_date_vendor_confirmation || '',
                                                row.sales_order_no || '',
                                                row.production_order_no || '',
                                                row.vendor_name || '',
                                                row.vendor_code || '',
                                                row.po_created_by || '',
                                                row.our_reference || '',
                                                row.po_date || '',
                                                row.account_assignment_category || '',
                                                row.net_value_po_currency || '',
                                                row.currency || '',
                                                row.purch_grp || '',
                                                row.deletion_ind || '',
                                                row.storage_no || '',
                                                row.storage_location_desc || ''
                                            ]);
                                        });

                                        // Set column widths
                                        worksheet.columns.forEach(column => {
                                            column.width = 15;
                                        });

                                        // Style header row
                                        const headerRow = worksheet.getRow(1);
                                        headerRow.font = { bold: true, size: 12, color: { argb: 'FFFFFF' } };
                                        headerRow.fill = {
                                            type: 'pattern',
                                            pattern: 'solid',
                                            fgColor: { argb: '4472C4' }
                                        };
                                        headerRow.alignment = { horizontal: 'center', vertical: 'middle' };

                                        // Generate Excel file
                                        workbook.xlsx.writeBuffer().then(buffer => {
                                            const blob = new Blob([buffer], { 
                                                type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
                                            });
                                            const url = window.URL.createObjectURL(blob);
                                            const a = document.createElement('a');
                                            a.href = url;
                                            a.download = 'Material_Tracking_' + moment().format('YYYY-MM-DD_HH-mm-ss') + '.xlsx';
                                            a.click();
                                            window.URL.revokeObjectURL(url);
                                            $('.export-loading').remove();
                                        });
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
                // {
                //     extend: 'pdf',
                //     text: '<i class="fa fa-file-pdf-o"></i> PDF',
                //     className: 'btn btn-danger btn-sm',
                //     action: function(e, dt, button, config) {
                //         $('body').append('<div class="export-loading">Preparing PDF Export...</div>');

                //         $.ajax({
                //             url: '/dpm/dwc/materialsearch/api/materialtracking.php',
                //             type: 'POST',
                //             data: {
                //                 type: 'get_all_data',
                //                 trackingFilter: $('#trackingFilter').val(),
                //                 vendorFilter: $('#vendorFilter').val(),
                //                 applyingFilters: window.applyingFilters ? 'true' : 'false'
                //             },
                //             success: function(response) {
                //                 try {
                //                     const data = JSON.parse(response);
                                    
                //                     if (data.success && data.data) {
                //                         // Process data in chunks
                //                         if (checkDataSize(data.data.length)) {
                //                             $('.export-loading').remove();
                //                             return;
                //                         }
                //                         const chunkSize = 100; // Adjust this value based on your needs
                //                         const chunks = [];
                                        
                //                         for (let i = 0; i < data.data.length; i += chunkSize) {
                //                             chunks.push(data.data.slice(i, i + chunkSize));
                //                         }

                //                         const docDefinition = {
                //                             pageSize: 'A4',
                //                             pageOrientation: 'landscape',
                //                             pageMargins: [10, 40, 10, 40], // Reduced margins
                //                             header: {
                //                                 text: 'Material Tracking',
                //                                 alignment: 'center',
                //                                 fontSize: 14,
                //                                 bold: true,
                //                                 margin: [0, 10, 0, 0]
                //                             },
                //                             footer: function(currentPage, pageCount) {
                //                                 return {
                //                                     columns: [
                //                                         {
                //                                             text: moment().format('YYYY-MM-DD HH:mm:ss'),
                //                                             alignment: 'left',
                //                                             margin: [10, 0],
                //                                             fontSize: 8
                //                                         },
                //                                         {
                //                                             text: `Page ${currentPage} of ${pageCount}`,
                //                                             alignment: 'right',
                //                                             margin: [0, 0, 10, 0],
                //                                             fontSize: 8
                //                                         }
                //                                     ]
                //                                 };
                //                             },
                //                             content: [],
                //                             styles: {
                //                                 header: {
                //                                     fontSize: 7,
                //                                     bold: true,
                //                                     fillColor: '#4472C4',
                //                                     color: 'white',
                //                                     alignment: 'center'
                //                                 },
                //                                 cell: {
                //                                     fontSize: 6,
                //                                     alignment: 'center'
                //                                 }
                //                             },
                //                             defaultStyle: {
                //                                 fontSize: 6
                //                             }
                //                         };

                //                         // Add table headers
                //                         const headers = [
                //                             'Tracking\nNumber', 'Sales\nOrder No', 'Production\nOrder No',
                //                             'Purchase\nOrder No', 'Purchase\nItem', 'Vendor\nName',
                //                             'Vendor\nCode', 'PO Created\nBy', 'PO\nDate',
                //                             'Material', 'Short\nText', 'PO\nQuantity',
                //                             'Quantity\nDelivered', 'Still To Be\nDelivered', 'Delivery\nDate',
                //                             'Delivery Date\nVendor Conf.', 'Account\nAssignment', 'PO Item Net\nGross Value',
                //                             'PO Item\nNet Value', 'Currency', 'Purch\nGrp', 'Unloading\nPoint',
                //                             'Storage\nNo', 'Storage\nLocation'
                //                         ];

                //                         // Process each chunk
                //                         chunks.forEach((chunk, index) => {
                //                             if (index > 0) {
                //                                 docDefinition.content.push({ text: '', pageBreak: 'before' });
                //                             }

                //                             const tableBody = [
                //                                 headers.map(header => ({
                //                                     text: header,
                //                                     style: 'header'
                //                                 }))
                //                             ];

                //                             chunk.forEach(row => {
                //                                 tableBody.push([
                //                                     row.tracking_number || '-',
                //                                     row.sales_order_no || '-',
                //                                     row.production_order_no || '-',
                //                                     row.purchase_order_no || '-',
                //                                     row.purchase_item || '-',
                //                                     row.vendor_name || '-',
                //                                     row.vendor_code || '-',
                //                                     row.po_created_by || '-',
                //                                     row.po_date || '-',
                //                                     row.material || '-',
                //                                     row.short_text || '-',
                //                                     row.po_quantity || '-',
                //                                     row.quantity_delivered || '-',
                //                                     row.still_to_be_delivered || '-',
                //                                     row.delivery_date || '-',
                //                                     row.delivery_date_vendor_confirmation || '-',
                //                                     row.account_assignment_category || '-',
                //                                     row.net_value_po_currency || '-',
                //                                     row.currency || '-',
                //                                     row.purch_grp || '-',
                //                                     row.deletion_ind || '-',
                //                                     row.storage_no || '-',
                //                                     row.storage_location_desc || '-'
                //                                 ].map(cell => ({
                //                                     text: cell,
                //                                     style: 'cell'
                //                                 })));
                //                             });

                //                             docDefinition.content.push({
                //                                 table: {
                //                                     headerRows: 1,
                //                                     widths: Array(24).fill('*'),
                //                                     body: tableBody
                //                                 },
                //                                 layout: {
                //                                     hLineWidth: function(i, node) {
                //                                         return 0.5;
                //                                     },
                //                                     vLineWidth: function(i, node) {
                //                                         return 0.5;
                //                                     },
                //                                     hLineColor: function(i, node) {
                //                                         return '#aaa';
                //                                     },
                //                                     vLineColor: function(i, node) {
                //                                         return '#aaa';
                //                                     },
                //                                     paddingTop: function(i) { return 2; },
                //                                     paddingBottom: function(i) { return 2; }
                //                                 }
                //                             });
                //                         });

                //                         // Create PDF with compression
                //                         const pdfDocGenerator = pdfMake.createPdf(docDefinition);
                //                         pdfDocGenerator.getBlob((blob) => {
                //                             // Create download link
                //                             const url = window.URL.createObjectURL(blob);
                //                             const a = document.createElement('a');
                //                             a.href = url;
                //                             a.download = 'Material_Tracking_' + moment().format('YYYY-MM-DD_HH-mm-ss') + '.pdf';
                //                             document.body.appendChild(a);
                //                             a.click();
                //                             window.URL.revokeObjectURL(url);
                //                             $('.export-loading').remove();
                //                         });
                //                     }
                //                 } catch (error) {
                //                     console.error('Error creating PDF:', error);
                //                     toastr.error('Failed to create PDF');
                //                     $('.export-loading').remove();
                //                 }
                //             },
                //             error: function(xhr, status, error) {
                //                 console.error('Ajax error:', error);
                //                 toastr.error('Failed to fetch data for PDF');
                //                 $('.export-loading').remove();
                //             }
                //         });
                //     }
                // }
            ],      
            drawCallback: function(settings) {
                $(window).trigger('resize');
            },
            initComplete: function(settings, json) {
                $(window).trigger('resize');
                // Only trigger initial search if not in search mode
                if (!isSearchMode && initialProjectSearch) {
                    table.ajax.reload();
                }
            }
        });

        function checkDataSize(dataLength) {
            if (dataLength > 1000) {
                return !confirm(`You are attempting to export ${dataLength} records to PDF. This may take a long time or cause browser issues. Do you want to continue?`);
            }
            return false;
        }

        // Add this resize handler
        $(window).on('resize', function() {
            clearTimeout(window.resizeTimeout);
            window.resizeTimeout = setTimeout(function() {
                table.columns.adjust();
            }, 100);
        });

        // Apply Filters button click handler
        $('#applyFilters').click(function() {
            window.applyingFilters = true;
            table.ajax.reload();
        });

        // Reset Filters button click handler
        $('#resetFilters').click(function() {
            // Store the current page length
            const currentLength = table.page.len();
            
            // Clear only specific filters
            $('#trackingFilter').val('');
            $('#vendorFilter').val('');
            $('#stillToBeDeliveredFilter').prop('checked', false);
            
            // Reset filters flag
            window.applyingFilters = false;
            
            // Reload table while preserving the page length
            table.page.len(currentLength).ajax.reload();
        });

        // Setup AJAX beforeSend and complete handlers
        $.ajaxSetup({
            beforeSend: function (xhr, settings) {
                // Only show loader for project search and data fetching
                if (settings.url.includes('/dpm/dwc/materialsearch/api/search.php') || 
                    settings.url.includes('/dpm/dwc/materialsearch/api/getdatamaster.php') ||
                    settings.url.includes('/dpm/dwc/materialsearch/api/material_segments.php')) {
                    activeAjaxRequests++;
                    showFullLoader();
                }
            },
            complete: function (xhr, status) {
                // Only hide loader for project search and data fetching
                if (xhr.statusText === 'OK') {
                    activeAjaxRequests--;
                    if (activeAjaxRequests === 0) {
                        hideFullLoader();
                    }
                }
            }
        });

        $('.ui.search')
            .search({
                apiSettings: {
                    url: '/dpm/dwc/materialsearch/api/search.php',
                    method: 'GET',
                    beforeSend: function(settings) {
                        // Get values directly
                        const urlFile = "material_tracking";
                        const projectQuery = $('#projectSearchInput').val();
                        const scanPanelValue = $('#scanPanelValue').val();
                        
                        // Modify the URL parameters
                        settings.data = {
                            file: urlFile,
                            project: projectQuery,
                            scannedPanelValue: scanPanelValue
                        };
                        return settings;
                    }
                },
                fields: {
                    results: 'items',
                    title: 'name',
                    url: 'html_url'
                },
                minCharacters: 4,
                onSelect: function(result, response) {
                    if (result && result.project_no) { // Remove the !isSearchMode condition
                        $('#trackingFilter').val(result.project_no);
                        window.applyingFilters = true;
                        
                        if (typeof table !== 'undefined') {
                            table.ajax.reload();
                        }
                        
                        let data = {
                            project: result.project_no,
                            clear: 1
                        };
                        $.ajax({
                            url: '/dpm/dwc/set_project_session.php',
                            method: 'POST',
                            data: data,
                            success: function(response) {
                                console.log('Session updated successfully:', response);
                            },
                            error: function(xhr, status, error) {
                                console.error('Session update failed:', error);
                            }
                        });
                    }
                    return false;
                }
            });

        // Hide loader on initial page load
        hideFullLoader();

        // Skip further processing if no project number
        if (!projectnumber || projectnumber.trim() === '') {
            console.log("No project number provided");
            return;
        }

        // Rest of your existing code...
        if (projectnumber.length < 4)
            return;

        $("#projectsList").removeClass("date-none");

        // Check if panel number and source type are in URL
        const panelNumber = ($('#panelSearch').dropdown('get value') || '').match(/^[^|]*/)[0];

        if (panelNumber) {
            saveSelectedValuesToServerMaterialTracking("panel");
        }

        (async () => {
            // Get panel number from URL
            const panelNumber = getUrlParameters()["panel"];
            const panelValue = getUrlParameters()["panel_value"] || '';

            const projectSelectionData = await $.ajax({
                url: '/dpm/dwc/materialsearch/api/getdatamaster.php',
                type: 'GET',
                data: {
                    filter: projectnumber
                }
            }).catch(e => {
                console.log(e);
            });

            // Add panel numbers to dropdown
            for(const panel of projectSelectionData["panels"]){
                const itemSelectionDiv = document.createElement('div');
                itemSelectionDiv.classList.add('item');
                itemSelectionDiv.setAttribute('data-value', panel);
                itemSelectionDiv.innerText = panel;
                document.querySelector('#panelSearch > .menu').appendChild(itemSelectionDiv);
            }

            // Reinitialize dropdown to include new items
            $('#panelSearch').dropdown('destroy').dropdown();

            // Defer the selection to ensure dropdown is fully initialized
            setTimeout(() => {
                // Debugging logs
                // console.log('Panel Number from URL:', panelNumber);
                // console.log('Available Panels:', projectSelectionData["panels"]);

                // Try multiple methods to select the panel
                if (panelNumber) {
                    // Method 1: Direct set selected
                    $('#panelSearch').dropdown('set selected', panelNumber);

                    // Method 2: Manual selection if first method fails
                    const panelItems = $('#panelSearch .menu .item');
                    panelItems.each(function() {
                        if ($(this).data('value') === panelNumber) {
                            $('#panelSearch').dropdown('set value', panelNumber);
                            $(this).addClass('selected');
                        }
                    });

                    // Method 3: Force selection
                    $(`#panelSearch .menu .item[data-value="${panelNumber}"]`).trigger('click');
                }
            }, 100);

            // Panel dropdown change handler
            $('#panelSearch').dropdown('setting', 'onChange', function(){
                let selectedPanelNumber = ($('#panelSearch').dropdown('get value') || '').match(/^[^|]*/)[0];
                let sourceType = $('#sourceTypeSelect').dropdown('get value');
                if(!selectedPanelNumber) return;
                saveSelectedValuesToServerMaterialTracking("panel");
            });

            // Search mode specific adjustments
            if (isSearchMode) {
                $('body').addClass('search-mode');
                $('.sidebar-collapse').hide();
                $('.navbar-static-top').hide();
                $('#page-wrapper').css('margin', '0');
            }
            
            // Fetch project data
            const projectData = await $.ajax({
                url: '/dpm/dwc/materialsearch/api/material_segments.php',
                type: 'GET',
                data: {
                    projectFilter: projectnumber
                }
            }).catch(e => {
                console.log(e);
            });

            $("#main-loader").remove();
            $('.ui.accordion').accordion({});

            hideFullLoader();

            // Check if panel number and source type are in URL
            if(panelNumber === undefined) {
                console.log("Panel number not found in the URL.");
                return;
            }

            // Set panel value if present
            if (panelValue) {
                $('#scanPanelValue').val(panelValue);
            }
        })();
    });

    // Function to save selected values to server
    function saveSelectedValuesToServerMaterialTracking(type) {
        let data = {};
        // Check the valueToSave parameter and set the appropriate data
        if (type === "project") {
            data = {
                project: $('#projectSearchInput').val(),
                // Leave panel and station empty
            };
        } else {
            // Save all selected values
            data = {
                project: $('#projectSearchInput').val(),
                panel: $('#panelSearch').dropdown('get value'),
                station: $('#stationSearch').dropdown('get value')
            };
        }

        // console.log('Saving values:', data); // Debug log

        $.ajax({
            url: '/dpm/dwc/set_project_session.php',
            method: 'POST',
            data: data,
            success: function(response) {
                // console.log('Session updated successfully:', response);
            },
            error: function(xhr, status, error) {
                // console.error('Session update failed:', error);
            }
        });
        return false;
    }
</script>
</body>
</html>