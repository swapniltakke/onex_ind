<!DOCTYPE html>
<html>
<?php
SharedManager::checkAuthToModule(18);
include_once 'core/index.php';
$stations = [
    0 => 'Breaker Warehouse',
    1 => 'Breaker Dropping',
    2 => 'Breaker Stamping',
    3 => 'Panel LV Box',
    4 => 'Panel Structure',
    5 => 'Panel Warehouse',
    6 => 'Panel Assembly',
    7 => 'Final Testing'
];
$menu_header_display = 'Panel Status';
?>
<head>
    <title>OneX | Panel Status</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=yes"/>

    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta charset="utf-8">

    <link href="../css/semantic.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="../css/dataTables.semanticui.min.css">

    <link href="../css/main.css?13" rel="stylesheet"/>

    <?php include_once 'shared/headerStyles.php' ?>
    
    <script src="../js/jquery.min.js"></script>
    <script src="../js/semantic.min.js"></script>
    <script src="../js/jquery.dataTables.js"></script>
    <script src="../js/dataTables.semanticui.min.js"></script>
    <script src="../js/dataTables.buttons.min.js"></script>
    <script src="../js/buttons.flash.min.js"></script>
    <script src="../js/jszip.min.js"></script>
    <script src="../js/pdfmake.min.js"></script>
    <script src="../js/vfs_fonts.js"></script>
    <script src="../js/buttons.html5.min.js"></script>
    <script src="../js/buttons.print.min.js"></script>
    <script src="../js/buttons.colVis.min.js"></script>
    <script src="../js/tablesort.js"></script>
    <script src="../js/Semantic-UI-Alert.js"></script>
    <script src="../js/jquery.toast.min.js"></script>

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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<style>
    /* IMPROVED LAYOUT FOR FIXED SIDEBAR AND FOOTER */
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
        font-size: 13px;
    }

    #wrapper {
        display: flex;
        min-height: 100vh;
        position: relative;
    }

    /* Fixed sidebar styles */
    nav.navbar-default.navbar-static-side {
        position: fixed;
        top: 0;
        left: 0;
        width: 220px;
        height: 100%;
        z-index: 1000;
        overflow-y: auto;
    }

    /* Main content area with padding for sidebar */
    #page-wrapper {
        margin-left: 220px;
        width: calc(100% - 220px);
        min-height: 100vh;
        position: relative;
        padding-bottom: 60px;
    }

    /* Fixed footer styles */
    .footer {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 40px;
        background: #fff;
        border-top: 1px solid #e7eaec;
        z-index: 900;
        width: 100%;
    }

    /* Content area */
    .wrapper-content {
        padding: 15px;
        overflow-y: auto;
    }

    .gray-bg {
        background-color: #f3f3f4;
    }

    /* Card styles */
    .card {
        margin-bottom: 15px;
        border: 1px solid #e7eaec;
        border-radius: 3px;
        background-color: #fff;
    }

    .card-header {
        padding: 10px 15px;
        border-bottom: 1px solid #e7eaec;
        background-color: #f5f5f6;
    }

    .card-header .card-title {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
    }

    .card-body {
        padding: 10px;
    }

    /* Existing styles */
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

    /* .item {
        background-color: white !important;
    } */

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
        margin-bottom: 5px !important;
        font-size: 11px;
    }

    .ui .segment {
        margin-top: 5px !important;
    }

    .date-none {
        display: none;
    }

    .ui.search > .results {
        position: relative !important;
        margin: auto !important;
    }

    .ui.tiny.button,
    .ui.tiny.buttons .button,
    .ui.tiny.buttons .or {
        font-size: 12px !important;
    }

    /* Add to your existing styles */
    .ui.search .results {
        position: absolute !important;
        top: 100% !important;
        left: 0 !important;
        width: 100% !important;
        z-index: 1000 !important;
    }

    .ui.search .results .result {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px !important;
        border-bottom: 1px solid #f0f0f0;
        font-size: 12px;
    }

    .ui.search .results .result .content {
        display: flex;
        flex-direction: column;
        margin-left: 10px;
    }

    .ui.search .results .result .title {
        font-weight: bold;
        margin-bottom: 3px;
    }

    .ui.search .results .result .description {
        color: #888;
        font-size: 0.85em;
    }

    #customFilterParent {
        width: 100% !important;
        display: block !important;
    }

    /* Scanner icon styles */
    .scanner-icon {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        line-height: 2rem !important;
        height: 2rem !important;
        font-size: 14px;
    }

    /* Make all inputs consistent */
    .form-control,
    .ui.input input,
    select.form-control,
    .input-group {
        height: 2rem !important;
        line-height: 1.5;
        font-size: 12px;
    }

    /* Adjust input group append height */
    .input-group-append {
        height: 2rem !important;
    }

    /* Ensure consistent form-group heights */
    .form-group {
        margin-bottom: 8px;
    }

    .form-group label {
        font-size: 12px;
        margin-bottom: 3px;
        font-weight: 600;
    }

    /* Additional Styles */
    .badge {
        padding: 4px 8px;
        margin-right: 8px;
        font-size: 11px;
    }

    .actions .btn {
        margin-left: 5px;
    }

    .input-group-text {
        height: 100% !important;
        display: flex;
        align-items: center;
        font-size: 12px;
    }

    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }

    /* Status badge colors */
    .badge-secondary { background-color: #6c757d; color: #fff; }
    .badge-warning { background-color: #ffc107; color: #fff; }
    .badge-danger { background-color: #dc3545; }
    .badge-success { background-color: #28a745; }

    .ui.left.icon.input {
        width: 100%;
        height: 2rem !important;
    }

    .ui.input input {
        padding-right: 30px !important;
        height: 2rem !important;
        min-height: 2rem !important;
        font-size: 12px;
    }

    /* Adjust the search icon position */
    .ui.left.icon.input > i.icon {
        height: 1rem !important;
        line-height: 2rem !important;
        font-size: 13px;
    }

    .panel-status-container {
        border: 1px solid #ccc;
        border-radius: 4px;
        background: #fff;
        box-shadow: 0 2px 8px 0 #0001;
        padding: 0;
        overflow-x: auto;
        width: 100%;
    }

    .panel-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 12px;
    }

    .panel-table th {
        background: #f7fafd;
        font-size: 11px;
        color: #34495e;
        font-weight: 700;
        border-bottom: 2px solid #d3e0ee;
        padding: 6px 8px;
        text-align: center;
        letter-spacing: 0.03em;
    }

    .panel-table th.panel-info-header {
        text-align: left;
        padding: 6px 10px;
        width: 160px;
        border-radius: 4px 0 0 0;
        background: #e9eff5;
        border-right: 2px solid #d3e0ee;
    }

    /* NEW: Style for the group headers (Breaker, Panel) */
    .panel-table th.status-header-group {
        background: #e9eff5;
        border-bottom: 1px solid #d3e0ee;
        border-right: 2px solid #d3e0ee;
        padding: 4px 8px;
        font-size: 10px;
        color: #2c3e50;
        height: 24px;
        vertical-align: middle;
    }

    .panel-table th.status-header-group:last-child {
        border-right: none;
        border-radius: 0 4px 0 0;
    }

    /* Adjust individual station labels within the new header structure */
    .panel-table th.status-header-label {
        min-width: 70px;
        padding: 4px 0px 8px 0px;
        text-align: center;
        font-size: 10px;
        background: #f7fafd;
        border-bottom: 2px solid #d3e0ee;
        border-right: 1px solid #d3e0ee;
        height: 24px;
        vertical-align: middle;
    }

    .panel-table th.status-header-label:last-child {
        border-right: none;
        border-radius: 0 4px 0 0;
    }

    .panel-table tr {
        background: #fcfdff;
    }
    .panel-table tr:nth-child(even) {
        background: #f3f5f7;
    }

    .panel-table td {
        vertical-align: middle;
        padding: 0;
    }

    /* Compact Panel Info Cell */
    .panel-info-cell {
        padding: 8px 10px !important;
        border-right: 2px solid #d3e0ee;
        min-width: 160px;
        width: 220px;
        font-size: 11px;
        color: #2c3e50;
        line-height: 1.3;
    }

    /* Breaker Tab Info Display */
    .panel-info-cell.breaker-info {
        padding: 6px 10px !important;
    }

    .panel-info-row {
        margin-bottom: 2px;
        display: flex;
        align-items: baseline;
        gap: 4px;
    }

    .panel-info-row.order-item {
        margin-bottom: 4px;
        font-weight: 600;
    }

    .panel-info-row.order-item .panel-info-value {
        color: #25c297;
    }

    .panel-info-row.order-item .panel-info-value.item {
        color: #2c3e50;
        font-weight: 500;
    }

    .panel-info-row.project-name {
        margin-bottom: 4px;
        font-weight: 500;
        color: grey;
    }

    .panel-info-row.panel-details {
        margin-bottom: 0;
        font-size: 10px;
        color: #7f8c8d;
    }

    .panel-info-row.panel-details .panel-name {
        color: #2c3e50;
        font-weight: 500;
    }

    .panel-info-value {
        color: #25c297;
        font-weight: 600;
        word-break: break-word;
    }

    .panel-info-value.secondary {
        color: #2c3e50;
        font-weight: 500;
    }

    .panel-info-value.tertiary {
        color: #7f8c8d;
        font-size: 10px;
    }

    /* Status track container */
    .status-track {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 8px;
        width: 100%;
        min-height: 60px;
    }

    /* Status step container */
    .status-step {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        flex: 1;
        gap: 4px;
    }

    /* Station label for status step */
    .status-step-label {
        font-size: 9px;
        color: #2c3e50;
        font-weight: 600;
        text-align: center;
        max-width: 70px;
        line-height: 1.2;
        min-height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Circle icon base styles */
    .status-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid #b8bbc6;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        color: #b8bbc6;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        font-weight: 500;
        transition: .3s;
        z-index: 2;
        cursor: pointer;
        position: relative;
        flex-shrink: 0;
    }

    /* Highlighted (done/inprogress) styles */
    .status-done .status-circle { 
        border-color: #25c297; 
        color: #25c297; 
        background: #e6f9f2; 
        font-weight: 700; 
    }
    
    .status-inprogress .status-circle { 
        border-color: #f8c715; 
        color: #f8c715; 
        background: #fffbe8; 
        font-weight: 700; 
    }
    
    .status-upcoming .status-circle { 
        border-color: #b8bbc6; 
        color: #b8bbc6; 
        background: #fff; 
        font-weight: 500; 
    }

    /* Connecting line styles */
    .status-line {
        position: absolute;
        height: 3px;
        background-color: #b8bbc6;
        z-index: 1;
        top: 50%;
        transform: translateY(-50%);
    }

    .status-line.green {
        background-color: #25c297;
    }

    .status-line.yellow {
        background-color: #f8c715;
    }

    .status-line.gray {
        background-color: #b8bbc6;
    }

    /* Icon improvements for better look */
    .status-circle .fa,
    .status-circle .fas,
    .status-circle .far,
    .status-circle .fal {
        vertical-align: middle;
        line-height: 28px;
        font-size: 16px;
    }

    /* Add little animations */
    .status-step:hover .status-circle {
        box-shadow: 0 2px 8px rgba(37, 194, 151, 0.15);
    }

    @media (max-width: 900px) {
        .status-header-label, .status-step { min-width: 55px; }
        .status-circle { width: 26px; height: 26px; font-size: 12px; }
        .status-circle .fa { font-size: 13px; line-height: 22px; }
        .status-step-label { font-size: 8px; max-width: 55px; }
    }

    /* COMPLETELY REVISED TOOLTIP STYLES */
    /* Container for the tooltip */
    #tooltip-container {
        position: absolute;
        display: none;
        width: 280px;
        max-width: 90vw;
        background-color: #ffffff;
        color: #333;
        border: 2px solid #00c48a;
        border-radius: 6px;
        padding: 10px;
        z-index: 9999;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        pointer-events: none;
        font-size: 11px;
    }

    /* Top arrow */
    #tooltip-container.tooltip-top:before {
        content: '';
        position: absolute;
        top: -8px;
        left: 50%;
        margin-left: -8px;
        width: 0;
        height: 0;
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-bottom: 8px solid #00c48a;
    }

    #tooltip-container.tooltip-top:after {
        content: '';
        position: absolute;
        top: -5px;
        left: 50%;
        margin-left: -5px;
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-bottom: 5px solid #ffffff;
    }

    /* Bottom arrow */
    #tooltip-container.tooltip-bottom:before {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 50%;
        margin-left: -8px;
        width: 0;
        height: 0;
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-top: 8px solid #00c48a;
    }

    #tooltip-container.tooltip-bottom:after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 50%;
        margin-left: -5px;
        width: 0;
        height: 0;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 5px solid #ffffff;
    }

    /* Station header styling */
    .station-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
        border-bottom: 1px solid #eaeaea;
        padding-bottom: 6px;
    }

    .station-name {
        display: flex;
        align-items: center;
        font-size: 13px;
        font-weight: bold;
        color: #111;
    }

    .station-icon {
        color: #00c48a;
        font-size: 14px;
        margin-right: 8px;
        background: #f0fffa;
        padding: 5px;
        border-radius: 4px;
    }

    .status-complete {
        color: #00c48a;
        font-size: 16px;
    }

    /* Date information styling */
    .date-info {
        margin: 8px 0;
    }

    .date-row {
        display: flex;
        align-items: center;
        margin: 5px 0;
        font-size: 10px;
    }

    .date-icon {
        color: #00c48a;
        font-size: 12px;
        margin-right: 6px;
    }

    .date-label {
        color: #666;
        font-size: 10px;
        margin-bottom: 1px;
    }

    .date-value {
        color: #111;
        font-size: 11px;
        font-weight: bold;
    }

    .date-separator {
        color: #00c48a;
        font-size: 14px;
        margin: 0 auto;
        text-align: center;
        font-weight: bold;
        padding: 0 8px;
    }

    /* Operator information styling */
    .operator-info {
        display: flex;
        justify-content: space-between;
        margin-top: 8px;
        border-top: 1px solid #eaeaea;
        padding-top: 8px;
    }

    .operator {
        display: flex;
        align-items: center;
        font-size: 10px;
    }

    .operator-icon {
        color: #00c48a;
        font-size: 12px;
        margin-right: 5px;
    }

    .operator-label {
        color: #666;
        font-size: 9px;
        display: block;
    }

    .operator-name {
        color: #111;
        font-size: 11px;
        font-weight: bold;
    }

    /* Filter separator styles */
    .filter-separator {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 2rem;
        margin-top: 1.65rem;
        font-size: 16px;
        font-weight: bold;
        color: #6c757d;
        padding-left: 8px;
    }

    .filter-separator::before,
    .filter-separator::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #dee2e6;
        margin: 0 8px;
    }

    /* IMPROVED TAB NAVIGATION STYLES */
    .nav-tabs {
        border-bottom: none;
        margin-bottom: 0;
        position: relative;
        z-index: 5;
    }

    .nav-tabs .nav-item {
        margin-bottom: 0;
        margin-right: 3px;
    }

    .nav-tabs .nav-link {
        border: 1px solid #dee2e6;
        border-bottom: none;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
        color: #6c757d;
        font-weight: 600;
        padding: 8px 16px;
        transition: all 0.2s ease;
        position: relative;
        top: 1px;
        font-size: 12px;
    }

    .nav-tabs .nav-link:hover {
        background-color: #f8f9fa;
        color: #495057;
    }

    .nav-tabs .nav-link.active {
        color: #25c297;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
        font-weight: 700;
        box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.05);
    }

    .nav-tabs .nav-link i {
        margin-right: 6px;
        font-size: 13px;
    }

    /* Improved tab content container */
    .tab-content {
        padding: 0;
        border: 1px solid #dee2e6;
        border-radius: 0 4px 4px 4px;
        background-color: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .tab-content > .tab-pane {
        display: none;
        padding: 10px;
        position: relative;
    }

    .tab-content > .active {
        display: block;
    }
    
    /* Loader overlay for individual tabs */
    .tab-loader {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 100;
        border-radius: 0 4px 4px 4px;
    }
    
    .tab-loader .spinner-border {
        width: 2rem;
        height: 2rem;
    }
    
    .tab-pane {
        position: relative;
        min-height: 200px;
    }
    
    /* IMPROVED PAGINATION STYLES */
    .pagination-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 15px;
        gap: 5px;
        flex-wrap: wrap;
    }

    .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: 3px;
        align-items: center;
    }

    .pagination .page-item {
        margin: 0;
    }

    .pagination .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 6px;
        border: 1px solid #dee2e6;
        border-radius: 3px;
        color: #25c297;
        background-color: #fff;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .pagination .page-link:hover:not(.disabled) {
        background-color: #f0f8f6;
        border-color: #25c297;
        color: #1a8a6a;
    }

    .pagination .page-item.active .page-link {
        background-color: #25c297;
        border-color: #25c297;
        color: #fff;
        font-weight: 600;
    }

    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        border-color: #dee2e6;
        cursor: not-allowed;
        opacity: 0.5;
    }

    .pagination .page-item.prev-next .page-link {
        font-weight: 600;
        min-width: auto;
        padding: 0 10px;
    }

    .pagination-info {
        font-size: 12px;
        color: #6c757d;
        margin-left: 15px;
    }

    /* IMPROVED NO DATA FOUND STYLING */
    .no-data-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 30px 15px;
        text-align: center;
        background-color: #f9f9f9;
        border-radius: 4px;
        margin: 15px 0;
    }

    .no-data-icon {
        font-size: 36px;
        color: #d1d5db;
        margin-bottom: 12px;
    }

    .no-data-text {
        font-size: 14px;
        font-weight: 500;
        color: #6c757d;
        margin-bottom: 6px;
    }

    .no-data-subtext {
        font-size: 12px;
        color: #9ca3af;
        max-width: 400px;
        line-height: 1.5;
    }

    /* Ensure the panel container has proper borders */
    .panel-status-container {
        border: 1px solid #d3e0ee;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 15px;
        position: relative;
    }

    /* Table loader styles */
    .table-loader {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.8);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        z-index: 50;
    }

    .table-loader .spinner-border {
        width: 2rem;
        height: 2rem;
        margin-bottom: 8px;
    }

    .table-loader-text {
        font-size: 12px;
        font-weight: 500;
        color: #495057;
    }

    /* Media queries for responsive layout */
    @media (max-width: 768px) {
        #page-wrapper {
            margin-left: 0;
            width: 100%;
            padding-top: 60px;
        }
        
        nav.navbar-default.navbar-static-side {
            width: 70%;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }
        
        nav.navbar-default.navbar-static-side.show-mobile {
            transform: translateX(0);
        }
        
        .mobile-nav-toggle {
            display: block;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
        }

        .panel-info-cell {
            min-width: 150px;
            width: 150px;
        }

        .panel-table th.panel-info-header {
            width: 150px;
        }

        .pagination-container {
            flex-direction: column;
            gap: 10px;
        }

        .pagination-info {
            margin-left: 0;
            order: 2;
        }

        .pagination {
            order: 1;
        }
    }

    .row {
        margin-right: -7.5px;
        margin-left: -7.5px;
    }

    .row > [class*='col-'] {
        padding-right: 7.5px;
        padding-left: 7.5px;
    }
</style>
<body>
<div id="wrapper">
    <?php $activePage = '/panel_status.php'; ?>
    <?php include_once 'shared/sidebar.php'; ?>
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
                                <h5 style="margin: 0; font-size: 18px; line-height: 0.2;">
                                    <sup class="badge badge-danger" style="font-size: 0.4em; background-color: #dc3545; color: white; padding: 0.2em 0.3em; border-radius: 0.25rem; vertical-align: super;">OneX</sup>
                                </h5>
                                <p style="margin: 0; text-transform: uppercase; font-size: 10px; color: #6c757d; line-height: 1.2;">Order Tracking System</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" style="margin-top: 48px;">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Filters Row -->
                        <div class="row mb-2">
                            <!-- Date Range Filter -->
                            <div style="width: 196px; padding-left: 7.5px;">
                                <div class="form-group">
                                    <label>Date Range</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="dateRangeFilter" readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Separator between filters -->
                            <div style="width: 1px;">
                                <div class="filter-separator">/</div>
                            </div>

                            <!-- Client Name Filter -->
                            <div class="col-md-4" style="padding-left: 13.5px; padding-right: 0.5px;">
                                <div class="form-group">
                                    <label>Client Name</label>
                                    <div class="ui search">
                                        <div class="ui left icon input fluid">
                                            <input class="prompt" type="text" id="clientNameFilter" placeholder="Enter client name">
                                            <i class="search icon"></i>
                                        </div>
                                        <div class="ui date-none" id="clientNamesFilterList"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Separator between filters -->
                            <div style="width: 1px;">
                                <div class="filter-separator">/</div>
                            </div>

                            <!-- Project Number Filter -->
                            <div class="col-md-3" style="padding-left: 13.5px; padding-right: 7.5px;">
                                <div class="form-group">
                                    <label>Project Number</label>
                                    <div class="ui search">
                                        <div class="ui left icon input fluid">
                                            <input class="prompt" type="text" id="projectFilter" placeholder="Enter project number">
                                            <i class="search icon"></i>
                                            <span class="scanner-icon" id="projectScannerIcon">
                                                <i class="fa fa-qrcode" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                        <div class="ui date-none" id="projectsFilterList"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Apply Filters Button -->
                            <div class="col-md-1" style="padding-left: 7.5px; padding-right: 3.75px;">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button class="btn btn-primary btn-block" id="applyFilters" style="font-size: 12px; padding: 6px 12px;">
                                        <i class="fa fa-filter"></i> Apply
                                    </button>
                                </div>
                            </div>

                            <!-- Clear Filters Button -->
                            <div class="col-md-1" style="padding-left: 3.75px; padding-right: 7.5px;">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button class="btn btn-secondary btn-block" id="clearFilters" style="font-size: 12px; padding: 6px 12px;">
                                        <i class="fa fa-times"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Status Legend moved above tabs -->
                        <div class="mb-2" style="margin-top: -10px;">
                            <span class="badge badge-secondary">Not Started</span>
                            <span class="badge badge-warning">In Progress</span>
                            <span class="badge badge-success">Done</span>
                        </div>

                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs" id="statusTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="breaker-tab" data-toggle="tab" href="#breaker" role="tab" aria-controls="breaker" aria-selected="true">
                                    <i class="fa fa-bolt"></i> Breaker
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="panel-tab" data-toggle="tab" href="#panel" role="tab" aria-controls="panel" aria-selected="false">
                                    <i class="fa fa-layer-group"></i> Panel
                                </a>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="statusTabContent">
                            <!-- Breaker Tab -->
                            <div class="tab-pane fade show active" id="breaker" role="tabpanel" aria-labelledby="breaker-tab">
                                <div class="tab-loader">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                                <!-- Breaker Results Table -->
                                <div class="panel-status-container">
                                    <!-- Table Loader -->
                                    <div class="table-loader" id="breakerTableLoader" style="display: none;">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <div class="table-loader-text">Loading data...</div>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="breakerResultsTable" class="panel-table">
                                            <thead>
                                                <tr>
                                                    <th class="panel-info-header" rowspan="2">Panel Info</th>
                                                    <th class="status-header-label">Warehouse</th>
                                                    <th class="status-header-label">Dropping</th>
                                                    <th class="status-header-label">Stamping</th>
                                                </tr>
                                            </thead>
                                            <tbody id="breakerTableBody">
                                                <!-- Breaker data will be loaded here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="pagination-container">
                                    <ul id="breakerPagination" class="pagination"></ul>
                                    <span id="breakerPaginationInfo" class="pagination-info"></span>
                                </div>
                            </div>

                            <!-- Panel Tab -->
                            <div class="tab-pane fade" id="panel" role="tabpanel" aria-labelledby="panel-tab">
                                <div class="tab-loader">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                                <!-- Panel Results Table -->
                                <div class="panel-status-container">
                                    <!-- Table Loader -->
                                    <div class="table-loader" id="panelTableLoader" style="display: none;">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <div class="table-loader-text">Loading data...</div>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="panelResultsTable" class="panel-table">
                                            <thead>
                                                <tr>
                                                    <th class="panel-info-header" rowspan="2">Panel Info</th>
                                                    <th class="status-header-label">LV Box</th>
                                                    <th class="status-header-label">Structure</th>
                                                    <th class="status-header-label">Warehouse</th>
                                                    <th class="status-header-label">Assembly</th>
                                                    <th class="status-header-label">Testing</th>
                                                </tr>
                                            </thead>
                                            <tbody id="panelTableBody">
                                                <!-- Panel data will be loaded here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="pagination-container">
                                    <ul id="panelPagination" class="pagination"></ul>
                                    <span id="panelPaginationInfo" class="pagination-info"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $footer_display = 'Panel Status';
    include_once '../assemblynotes/shared/footer.php'; ?>
</div>

<!-- Tooltip container that will be positioned dynamically -->
<div id="tooltip-container">
    <div class="station-header">
        <div class="station-name">
            <i class="fa fa-clipboard-check station-icon"></i>
            <span id="tooltip-station-name">LV Box</span>
        </div>
        <i class="fa fa-check-circle status-complete"></i>
    </div>
    
    <div class="date-info">
        <div class="date-row">
            <i class="fa fa-arrow-right date-icon"></i>
            <span>
                <span class="date-label">Entrance Date (17 days ago)</span><br>
                <span class="date-value">10:12:20 - 23.09.2025</span>
            </span>
        </div>
        
        <div class="date-separator">|</div>
        
        <div class="date-row">
            <i class="fa fa-sign-out-alt date-icon"></i>
            <span>
                <span class="date-label">Exit Date (9 days ago)</span><br>
                <span class="date-value">15:04:05 - 01.10.2025</span>
            </span>
        </div>
    </div>
    
    <div class="operator-info">
        <div class="operator">
            <i class="fa fa-arrow-right operator-icon"></i>
            <span>
                <span class="operator-label">Entrance Operator</span>
                <span class="operator-name">DOĞUKAN ÖZDEN</span>
            </span>
        </div>
        
        <div class="operator">
            <i class="fa fa-sign-out-alt operator-icon"></i>
            <span>
                <span class="operator-label">Exit Operator</span>
                <span class="operator-name">KORAY DEMİRCİ</span>
            </span>
        </div>
    </div>
</div>

<!-- Mobile Navigation Toggle Button (only visible on small screens) -->
<button class="btn btn-primary d-md-none mobile-nav-toggle">
    <i class="fa fa-bars"></i>
</button>

<!-- Mainly scripts -->
<?php include_once '../assemblynotes/shared/headerSemanticScripts.php' ?>
<script src="../shared/shared.js"></script>
<script>
    // Cache for panel data
    let panelDataCache = {
        breaker: {
            data: [],
            pagination: null,
            page: 1
        },
        panel: {
            data: [],
            pagination: null,
            page: 1
        }
    };
    
    let activeAjaxRequests = 0;
    let currentTabType = 'breaker'; // Default tab
    let resizeTimer; // For debouncing resize events
    let dataLoaded = false; // Track if data has been loaded initially

    function showFullLoader() {
        const loader = document.querySelector('.full-loader');
        if (loader) {
            loader.style.display = 'flex';
        }
    }

    function hideFullLoader() {
        const loader = document.querySelector('.full-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    }
    
    function showTabLoader(tabType) {
        $(`#${tabType} .tab-loader`).show();
    }
    
    function hideTabLoader(tabType) {
        $(`#${tabType} .tab-loader`).hide();
    }

    // Show table loader for specific tab
    function showTableLoader(tabType) {
        if (tabType === 'breaker') {
            $('#breakerTableLoader').show();
        } else {
            $('#panelTableLoader').show();
        }
    }
    
    // Hide table loader for specific tab
    function hideTableLoader(tabType) {
        if (tabType === 'breaker') {
            $('#breakerTableLoader').hide();
        } else {
            $('#panelTableLoader').hide();
        }
    }

    // Function to format date for display
    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleString('en-GB', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        }).replace(',', ' -');
    }

    // Calculate days ago for tooltip
    function getDaysAgo(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const today = new Date();
        const diffTime = Math.abs(today - date);
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays === 0) return 'today';
        if (diffDays === 1) return 'yesterday';
        return `${diffDays} days ago`;
    }

    // Calculate duration between two dates
    function getDuration(startDate, endDate) {
        if (!startDate || !endDate) return '';
        
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays > 0) {
            return `${diffDays} day${diffDays > 1 ? 's' : ''}`;
        } else {
            const diffHours = Math.floor((diffTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            return `${diffHours} hour${diffHours > 1 ? 's' : ''}`;
        }
    }

    // Function to fetch panel status data
    function fetchPanelStatus(page = null, forceRefresh = false) {
        // If page is not specified, use the current tab's page
        if (page === null) {
            page = panelDataCache[currentTabType].page;
        } else {
            // Update the current tab's page
            panelDataCache[currentTabType].page = page;
        }
        
        // Show both tab and table loaders
        showTabLoader(currentTabType);
        showTableLoader(currentTabType);
        
        // Check if we already have cached data for this page and tab
        if (!forceRefresh && 
            panelDataCache[currentTabType].data.length > 0 && 
            panelDataCache[currentTabType].page === page) {
            
            // Use cached data
            if (currentTabType === 'breaker') {
                renderBreakerStatus(panelDataCache.breaker.data);
                renderPagination(panelDataCache.breaker.pagination, 'breakerPagination', 'breakerPaginationInfo');
            } else {
                renderPanelStatus(panelDataCache.panel.data);
                renderPagination(panelDataCache.panel.pagination, 'panelPagination', 'panelPaginationInfo');
            }
            
            // Hide loaders
            hideTabLoader(currentTabType);
            hideTableLoader(currentTabType);
            
            // Adjust layout after rendering
            setTimeout(adjustLayout, 100);
            
            return Promise.resolve();
        }
        
        // Collect filter values
        const filters = {
            dateRange: $('#dateRangeFilter').val(),
            clientName: $('#clientNameFilter').val(),
            project: $('#projectFilter').val(),
            tabType: currentTabType,
            page: page,
            limit: 10
        };
        
        console.log('Fetching data with filters:', filters);
        
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/dpm/api/panelStatusController.php',
                type: 'GET',
                data: {
                    action: 'getPanelStatus',
                    ...filters
                },
                dataType: 'json',
                success: function(response) {
                    console.log('API Response:', response);
                    
                    if (response.status === 'success') {
                        // Update cache for the current tab
                        panelDataCache[currentTabType].data = response.data;
                        panelDataCache[currentTabType].pagination = response.pagination;
                        panelDataCache[currentTabType].page = page;
                        
                        dataLoaded = true;
                        
                        // Render the current tab
                        if (currentTabType === 'breaker') {
                            renderBreakerStatus(response.data);
                            renderPagination(response.pagination, 'breakerPagination', 'breakerPaginationInfo');
                        } else {
                            renderPanelStatus(response.data);
                            renderPagination(response.pagination, 'panelPagination', 'panelPaginationInfo');
                        }
                        
                        resolve(response);
                    } else {
                        console.error('Error fetching panel status:', response.message);
                        reject(response.message);
                    }
                    
                    // Hide both tab and table loaders
                    hideTabLoader(currentTabType);
                    hideTableLoader(currentTabType);
                    
                    // Adjust layout after data is loaded
                    setTimeout(adjustLayout, 100);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    hideTabLoader(currentTabType);
                    hideTableLoader(currentTabType);
                    reject(error);
                }
            });
        });
    }

    // Function to render breaker status data
    function renderBreakerStatus(panels) {
        const tableBody = $('#breakerTableBody');
        tableBody.empty();
        
        if (panels.length === 0) {
            // Enhanced "No data found" display
            tableBody.append(`
                <tr>
                    <td colspan="4" class="text-center p-3">
                        <div class="no-data-container">
                            <i class="fa fa-search no-data-icon"></i>
                            <div class="no-data-text">No data found</div>
                            <div class="no-data-subtext">Try adjusting your filters or select a different date range</div>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }
        
        panels.forEach(panel => {
            const row = $('<tr></tr>');
            
            // Panel info cell - Breaker format
            const panelInfoHtml = `
                <td class="panel-info-cell breaker-info">
                    <div class="panel-info-row order-item">
                        <span class="panel-info-value">${panel.project_no}</span>
                        <span class="panel-info-value item">(${panel.item_no})</span>
                    </div>
                    <div class="panel-info-row project-name">
                        ${panel.project_name || 'N/A'}
                    </div>
                </td>
            `;
            row.append(panelInfoHtml);
            
            // Create a cell that spans all breaker stations
            const statusTrackCell = $('<td colspan="3" style="padding:0;"></td>');
            const statusTrack = $('<div class="status-track"></div>');
            
            // Filter only breaker stations (first 3)
            const breakerStations = panel.stations.slice(0, 3);
            
            // First, create all the status steps (circles)
            breakerStations.forEach((station, index) => {
                const status = station.status === 'done' ? 'done' : station.status === 'in_progress' ? 'inprogress' : 'upcoming';
                
                // Determine icon based on station
                let icon = 'fa-clipboard';
                if (station.name.includes('Dropping')) {
                    icon = 'fa-truck-droplet';
                } else if (station.name.includes('Stamping')) {
                    icon = 'fa-stamp';
                } else if (station.name.includes('Warehouse')) {
                    icon = 'fa-warehouse';
                }
                
                // Extract short station name
                let shortName = 'Warehouse';
                if (station.name.includes('Dropping')) {
                    shortName = 'Dropping';
                } else if (station.name.includes('Stamping')) {
                    shortName = 'Stamping';
                }
                
                // Create status step with label
                const statusStep = $(`
                    <div class="status-step status-${status}">
                        <div class="status-circle" data-station="${station.name}" data-status="${status}" data-index="${index}">
                            <i class="fa ${icon}"></i>
                        </div>
                    </div>
                `);
                
                // Add tooltip data if available
                if (station.data) {
                    const startTime = station.data.start_time;
                    const endDate = station.data.end_date;
                    const userId = station.data.user_id || '';
                    const remarks = station.data.remarks || '';
                    const isBreakerWarehouse = station.name === 'Breaker Warehouse';
                    
                    statusStep.find('.status-circle').attr({
                        'data-start-time': startTime,
                        'data-end-date': endDate,
                        'data-user-id': userId,
                        'data-remarks': remarks,
                        'data-days-ago-start': getDaysAgo(startTime),
                        'data-days-ago-end': getDaysAgo(endDate),
                        'data-duration': getDuration(startTime, endDate),
                        'data-is-breaker-warehouse': isBreakerWarehouse ? 'true' : 'false'
                    });
                }
                
                statusTrack.append(statusStep);
            });
            
            statusTrackCell.append(statusTrack);
            row.append(statusTrackCell);
            tableBody.append(row);
        });
        
        // Re-initialize tooltip handlers
        initTooltipHandlers();
        
        // Add connecting lines after DOM is updated
        setTimeout(() => {
            $('#breakerResultsTable .status-track').each(function(index) {
                if (index < panels.length) {
                    const statusTrack = $(this);
                    const breakerStations = panels[index].stations.slice(0, 3);
                    addConnectingLines(statusTrack, breakerStations);
                }
            });
        }, 0);
    }

    // Function to render panel status data
    function renderPanelStatus(panels) {
        const tableBody = $('#panelTableBody');
        tableBody.empty();
        
        if (panels.length === 0) {
            // Enhanced "No data found" display
            tableBody.append(`
                <tr>
                    <td colspan="6" class="text-center p-3">
                        <div class="no-data-container">
                            <i class="fa fa-search no-data-icon"></i>
                            <div class="no-data-text">No data found</div>
                            <div class="no-data-subtext">Try adjusting your filters or select a different date range</div>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }
        
        panels.forEach(panel => {
            const row = $('<tr></tr>');
            
            // Panel info cell - Panel format
            const panelInfoHtml = `
                <td class="panel-info-cell">
                    <div class="panel-info-row order-item">
                        <span class="panel-info-value">${panel.project_no}</span>
                        <span class="panel-info-value item">(${panel.item_no})</span>
                        <span class="panel-name">${panel.panel_name || 'N/A'}</span>
                        <span class="panel-name"> / ${panel.typical_name || 'N/A'}</span>
                    </div>
                    <div class="panel-info-row project-name">
                        ${panel.project_name || 'N/A'}
                    </div>
                </td>
            `;
            row.append(panelInfoHtml);
            
            // Create a cell that spans all panel stations
            const statusTrackCell = $('<td colspan="5" style="padding:0;"></td>');
            const statusTrack = $('<div class="status-track"></div>');
            
            // Filter only panel stations (last 5)
            const panelStations = panel.stations.slice(3);
            
            // First, create all the status steps (circles)
            panelStations.forEach((station, index) => {
                const status = station.status === 'done' ? 'done' : station.status === 'in_progress' ? 'inprogress' : 'upcoming';
                
                // Determine icon based on station
                let icon = 'fa-clipboard';
                if (station.name.includes('LV Box')) {
                    icon = 'fa-box';
                } else if (station.name.includes('Structure')) {
                    icon = 'fa-building';
                } else if (station.name.includes('Assembly')) {
                    icon = 'fa-arrows-to-dot';
                } else if (station.name.includes('Testing')) {
                    icon = 'fa-microscope';
                } else if (station.name.includes('Warehouse')) {
                    icon = 'fa-warehouse';
                }
                
                // Extract short station name
                let shortName = 'LV Box';
                if (station.name.includes('Structure')) {
                    shortName = 'Structure';
                } else if (station.name.includes('Assembly')) {
                    shortName = 'Assembly';
                } else if (station.name.includes('Testing')) {
                    shortName = 'Testing';
                } else if (station.name.includes('Warehouse')) {
                    shortName = 'Warehouse';
                }
                
                // Create status step with label
                const statusStep = $(`
                    <div class="status-step status-${status}">
                        <div class="status-circle" data-station="${station.name}" data-status="${status}" data-index="${index}">
                            <i class="fa ${icon}"></i>
                        </div>
                    </div>
                `);
                
                // Add tooltip data if available
                if (station.data) {
                    const startTime = station.data.start_time;
                    const endDate = station.data.end_date;
                    const userId = station.data.user_id || '';
                    const remarks = station.data.remarks || '';
                    const isPanelLvBox = station.name === 'Panel LV Box';
                    
                    statusStep.find('.status-circle').attr({
                        'data-start-time': startTime,
                        'data-end-date': endDate,
                        'data-user-id': userId,
                        'data-remarks': remarks,
                        'data-days-ago-start': getDaysAgo(startTime),
                        'data-days-ago-end': getDaysAgo(endDate),
                        'data-duration': getDuration(startTime, endDate),
                        'data-is-panel-lv-box': isPanelLvBox ? 'true' : 'false'
                    });
                }
                
                statusTrack.append(statusStep);
            });
            
            statusTrackCell.append(statusTrack);
            row.append(statusTrackCell);
            tableBody.append(row);
        });
        
        // Re-initialize tooltip handlers
        initTooltipHandlers();
        
        // Add connecting lines after DOM is updated
        setTimeout(() => {
            $('#panelResultsTable .status-track').each(function(index) {
                if (index < panels.length) {
                    const statusTrack = $(this);
                    const panelStations = panels[index].stations.slice(3);
                    addConnectingLines(statusTrack, panelStations);
                }
            });
        }, 0);
    }

    // Function to add connecting lines between status circles
    function addConnectingLines(statusTrack, stations) {
        const circles = statusTrack.find('.status-circle');
        
        if (circles.length < 2) return;
        
        // Remove any existing lines
        statusTrack.find('.status-line').remove();
        
        // Add lines between each pair of circles
        for (let i = 0; i < circles.length - 1; i++) {
            const currentCircle = $(circles[i]);
            const nextCircle = $(circles[i + 1]);
            const currentStatus = stations[i].status;
            
            // Determine line color based on current circle's status
            let lineClass = 'gray';
            if (currentStatus === 'done') {
                lineClass = 'green';
            } else if (currentStatus === 'in_progress') {
                lineClass = 'yellow';
            }
            
            // Get positions and dimensions relative to the track container
            const trackRect = statusTrack[0].getBoundingClientRect();
            const currentRect = currentCircle[0].getBoundingClientRect();
            const nextRect = nextCircle[0].getBoundingClientRect();
            
            // Calculate line position and width
            const borderWidth = 2;
            const startX = currentRect.right - trackRect.left - borderWidth;
            const endX = nextRect.left - trackRect.left + borderWidth;
            const width = Math.max(0, endX - startX);
            
            // Create and append the line
            const line = $(`<div class="status-line ${lineClass}"></div>`);
            line.css({
                'left': startX + 'px',
                'width': width + 'px'
            });
            
            statusTrack.append(line);
        }
    }

    // Function to render pagination with improved design
    function renderPagination(pagination, containerId, infoId) {
        const paginationContainer = $(`#${containerId}`);
        const infoContainer = $(`#${infoId}`);
        paginationContainer.empty();
        infoContainer.empty();
        
        if (!pagination) {
            return;
        }
        
        // Calculate pagination info
        const startItem = (pagination.page - 1) * pagination.limit + 1;
        const endItem = Math.min(pagination.page * pagination.limit, pagination.total);
        const infoText = `Showing ${startItem} to ${endItem} of ${pagination.total} results`;
        infoContainer.text(infoText);
        
        if (pagination.pages <= 1) {
            return;
        }
        
        // Determine which pages to show
        const maxPagesToShow = 7;
        let startPage = 1;
        let endPage = pagination.pages;
        
        if (pagination.pages > maxPagesToShow) {
            const halfPages = Math.floor(maxPagesToShow / 2);
            startPage = Math.max(1, pagination.page - halfPages);
            endPage = Math.min(pagination.pages, startPage + maxPagesToShow - 1);
            
            // Adjust if we're near the end
            if (endPage - startPage < maxPagesToShow - 1) {
                startPage = Math.max(1, endPage - maxPagesToShow + 1);
            }
        }
        
        // Previous button
        const prevClass = pagination.page === 1 ? 'disabled' : '';
        paginationContainer.append(`
            <li class="page-item prev-next ${prevClass}">
                <a class="page-link" href="#" data-page="${pagination.page - 1}">
                    <i class="fa fa-chevron-left"></i> Previous
                </a>
            </li>
        `);
        
        // First page button if needed
        if (startPage > 1) {
            paginationContainer.append(`
                <li class="page-item">
                    <a class="page-link" href="#" data-page="1">1</a>
                </li>
            `);
            
            if (startPage > 2) {
                paginationContainer.append(`
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                `);
            }
        }
        
        // Page numbers
        for (let i = startPage; i <= endPage; i++) {
            const activeClass = pagination.page === i ? 'active' : '';
            paginationContainer.append(`
                <li class="page-item ${activeClass}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
        }
        
        // Last page button if needed
        if (endPage < pagination.pages) {
            if (endPage < pagination.pages - 1) {
                paginationContainer.append(`
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                `);
            }
            
            paginationContainer.append(`
                <li class="page-item">
                    <a class="page-link" href="#" data-page="${pagination.pages}">${pagination.pages}</a>
                </li>
            `);
        }
        
        // Next button
        const nextClass = pagination.page === pagination.pages ? 'disabled' : '';
        paginationContainer.append(`
            <li class="page-item prev-next ${nextClass}">
                <a class="page-link" href="#" data-page="${pagination.page + 1}">
                    Next <i class="fa fa-chevron-right"></i>
                </a>
            </li>
        `);
        
        // Add click handlers for pagination
        $(`#${containerId} .page-link`).on('click', function(e) {
            e.preventDefault();
            const page = parseInt($(this).data('page'));
            
            if (page > 0 && page <= pagination.pages) {
                // Show table loader for the current tab
                showTableLoader(currentTabType);
                
                // Fetch data for the new page
                fetchPanelStatus(page, true);
                
                // Scroll to top of table
                $('html, body').animate({
                    scrollTop: $('#statusTabContent').offset().top - 100
                }, 300);
            }
        });
    }

    // Function to show tooltip
    function showTooltip(circle, stationName, status, startTime, endDate, userId, daysAgoStart, daysAgoEnd, duration) {
        const tooltipContainer = $('#tooltip-container');
        
        // Set station name and icon
        $('#tooltip-station-name').text(stationName);
        
        // Determine the appropriate icon for the station
        let stationIcon = 'fa-clipboard';
        if (stationName.includes('Dropping')) {
            stationIcon = 'fa-truck-droplet';
        } else if (stationName.includes('Structure')) {
            stationIcon = 'fa-building';
        } else if (stationName.includes('Stamping')) {
            stationIcon = 'fa-stamp';
        } else if (stationName.includes('LV Box')) {
            stationIcon = 'fa-box';
        } else if (stationName.includes('Assembly')) {
            stationIcon = 'fa-arrows-to-dot';
        } else if (stationName.includes('Warehouse')) {
            stationIcon = 'fa-warehouse';
        } else if (stationName.includes('Testing')) {
            stationIcon = 'fa-microscope';
        }
        
        // Update the station icon
        $('.station-icon').removeClass().addClass('fa ' + stationIcon + ' station-icon');
        
        // Update status icon
        if (status === 'done') {
            $('.status-complete').show().removeClass('fa-spinner fa-spin').addClass('fa-check-circle');
        } else if (status === 'inprogress') {
            $('.status-complete').show().removeClass('fa-check-circle').addClass('fa-spinner fa-spin');
        } else {
            $('.status-complete').hide();
        }
        
        // Check if this is Panel LV Box or Breaker Warehouse
        const isPanelLvBox = stationName === 'Panel LV Box';
        const isBreakerWarehouse = stationName === 'Breaker Warehouse';
        
        if (isPanelLvBox) {
            $('.date-row:first').hide();
            $('.date-separator').hide();
            
            if (endDate) {
                $('.date-row:last .date-label').text(`Exit Date (${daysAgoEnd})`);
                $('.date-row:last .date-value').text(formatDate(endDate));
                $('.date-row:last').show();
            } else {
                $('.date-row:last').hide();
            }
            
            $('.operator:first').hide();
            
            if (endDate && userId) {
                $('.operator:last .operator-name').text(userId);
                $('.operator:last .operator-label').text('Exit Operator');
                $('.operator:last').show();
            } else {
                $('.operator:last').hide();
            }
        } else if (isBreakerWarehouse) {
            $('.date-row:first').hide();
            $('.date-separator').hide();
            
            if (endDate) {
                $('.date-row:last .date-label').text(`Processing Date (${daysAgoEnd})`);
                $('.date-row:last .date-value').text(formatDate(endDate));
                $('.date-row:last').show();
            } else {
                $('.date-row:last').hide();
            }
            
            $('.operator:first').hide();
            
            if (endDate && userId) {
                $('.operator:last .operator-name').text(userId);
                $('.operator:last .operator-label').text('Warehouse Operator');
                $('.operator:last').show();
            } else {
                $('.operator:last').hide();
            }
        } else {
            if (startTime) {
                $('.date-row:first .date-label').text(`Entrance Date (${daysAgoStart})`);
                $('.date-row:first .date-value').text(formatDate(startTime));
                $('.date-row:first').show();
            } else {
                $('.date-row:first').hide();
            }
            
            if (endDate) {
                $('.date-row:last .date-label').text(`Exit Date (${daysAgoEnd})`);
                $('.date-row:last .date-value').text(formatDate(endDate));
                $('.date-row:last').show();
            } else {
                $('.date-row:last').hide();
            }
            
            if (duration && startTime && endDate) {
                $('.date-separator').show();
                $('.date-separator').text(duration);
            } else {
                $('.date-separator').hide();
            }
            
            if (userId) {
                $('.operator:first .operator-name').text(userId);
                $('.operator:first').show();
            } else {
                $('.operator:first').hide();
            }
            
            if (endDate && userId) {
                $('.operator:last .operator-name').text(userId);
                $('.operator:last .operator-label').text('Exit Operator');
                $('.operator:last').show();
            } else {
                $('.operator:last').hide();
            }
        }
        
        // Position the tooltip
        positionTooltip(circle);
    }

    // Function to position tooltip
    function positionTooltip(circle) {
        const tooltipContainer = $('#tooltip-container');
        
        // Get circle position and dimensions
        const circleOffset = circle.offset();
        const circleWidth = circle.outerWidth();
        const circleHeight = circle.outerHeight();
        
        // Calculate center of circle
        const circleX = circleOffset.left + (circleWidth / 2);
        const circleY = circleOffset.top + (circleHeight / 2);
        
        // Get viewport dimensions and scroll position
        const windowWidth = $(window).width();
        const windowHeight = $(window).height();
        const scrollTop = $(window).scrollTop();
        const scrollLeft = $(window).scrollLeft();
        
        // Make tooltip temporarily visible to calculate its dimensions
        tooltipContainer.css({
            'visibility': 'hidden',
            'display': 'block',
            'position': 'absolute',
            'top': 0,
            'left': 0
        });
        
        const tooltipWidth = tooltipContainer.outerWidth();
        const tooltipHeight = tooltipContainer.outerHeight();
        
        // Reset tooltip classes
        tooltipContainer.removeClass('tooltip-top tooltip-bottom');
        
        // Calculate space available above and below the circle
        const spaceAbove = circleY - scrollTop;
        const spaceBelow = windowHeight - (circleY - scrollTop);
        
        // Calculate horizontal position (centered on circle)
        let leftPos = circleX - (tooltipWidth / 2);
        
        // Ensure tooltip stays within viewport horizontally
        if (leftPos < scrollLeft + 10) {
            leftPos = scrollLeft + 10;
        } else if (leftPos + tooltipWidth > scrollLeft + windowWidth - 10) {
            leftPos = scrollLeft + windowWidth - tooltipWidth - 10;
        }
        
        // Determine vertical position and arrow style
        let topPos;
        
        if (spaceBelow >= tooltipHeight + 20) {
            topPos = circleOffset.top + circleHeight + 10;
            tooltipContainer.addClass('tooltip-top');
        } 
        else if (spaceAbove >= tooltipHeight + 20) {
            topPos = circleOffset.top - tooltipHeight - 10;
            tooltipContainer.addClass('tooltip-bottom');
        }
        else if (spaceBelow >= spaceAbove) {
            topPos = circleOffset.top + circleHeight + 10;
            tooltipContainer.addClass('tooltip-top');
        } else {
            topPos = circleOffset.top - tooltipHeight - 10;
            tooltipContainer.addClass('tooltip-bottom');
        }
        
        // Apply final position and make visible
        tooltipContainer.css({
            'top': topPos,
            'left': leftPos,
            'visibility': 'visible',
            'opacity': 1
        });
    }

    // Function to initialize tooltip handlers
    function initTooltipHandlers() {
        // Remove any existing hover events to prevent duplication
        $('.status-circle').off('mouseenter mouseleave');
        
        // Add hover event to status circles
        $('.status-circle').hover(
            function() {
                const circle = $(this);
                
                // Get data attributes
                const stationName = circle.data('station');
                const status = circle.data('status');
                const startTime = circle.data('start-time');
                const endDate = circle.data('end-date');
                const userId = circle.data('user-id');
                const daysAgoStart = circle.data('days-ago-start');
                const daysAgoEnd = circle.data('days-ago-end');
                const duration = circle.data('duration');
                
                // Show tooltip with the data
                showTooltip(circle, stationName, status, startTime, endDate, userId, daysAgoStart, daysAgoEnd, duration);
            },
            function() {
                // Hide tooltip when not hovering
                $('#tooltip-container').hide();
            }
        );
    }

    // Function to adjust layout after content loads
    function adjustLayout() {
        // Get the content height
        const contentHeight = $('#page-wrapper').height();
        const windowHeight = $(window).height();
        
        // Ensure minimum height for the wrapper
        $('#wrapper').css('min-height', Math.max(contentHeight, windowHeight) + 'px');
    }

    // Efficient function to update connecting lines on zoom/resize
    function updateConnectingLines() {
        if (currentTabType === 'breaker') {
            $('#breakerResultsTable .status-track').each(function(index) {
                if (index < panelDataCache.breaker.data.length) {
                    const statusTrack = $(this);
                    const breakerStations = panelDataCache.breaker.data[index].stations.slice(0, 3);
                    addConnectingLines(statusTrack, breakerStations);
                }
            });
        } else {
            $('#panelResultsTable .status-track').each(function(index) {
                if (index < panelDataCache.panel.data.length) {
                    const statusTrack = $(this);
                    const panelStations = panelDataCache.panel.data[index].stations.slice(3);
                    addConnectingLines(statusTrack, panelStations);
                }
            });
        }
    }

    $(document).ready(function() {
        // Hide tab loaders initially
        $('.tab-loader').hide();
        $('.table-loader').hide();
        
        // Initialize date range picker with current date
        const today = moment();
        $('#dateRangeFilter').daterangepicker({
            startDate: today,
            endDate: today,
            locale: {
                format: 'DD.MM.YYYY'
            }
        });

        // Load initial data for the default tab
        fetchPanelStatus(1, true).then(() => {
            adjustLayout();
        });

        // Handle tab changes
        $('#statusTabs a').on('click', function(e) {
            e.preventDefault();
            $(this).tab('show');
            
            // Update current tab type based on selected tab
            const newTabType = $(this).attr('id').replace('-tab', '');
            
            // Only fetch data if we're changing tabs
            if (currentTabType !== newTabType) {
                currentTabType = newTabType;
                
                // If we have data for this tab, just show it
                if (panelDataCache[currentTabType].data.length > 0) {
                    // Show the loaders briefly for visual feedback
                    showTabLoader(currentTabType);
                    showTableLoader(currentTabType);
                    
                    // Use cached data
                    if (currentTabType === 'breaker') {
                        renderBreakerStatus(panelDataCache.breaker.data);
                        renderPagination(panelDataCache.breaker.pagination, 'breakerPagination', 'breakerPaginationInfo');
                    } else {
                        renderPanelStatus(panelDataCache.panel.data);
                        renderPagination(panelDataCache.panel.pagination, 'panelPagination', 'panelPaginationInfo');
                    }
                    
                    // Hide the loaders after a short delay for visual feedback
                    setTimeout(() => {
                        hideTabLoader(currentTabType);
                        hideTableLoader(currentTabType);
                        updateConnectingLines();
                    }, 300);
                } else {
                    // If no data yet for this tab, fetch it
                    fetchPanelStatus(1, true);
                }
            }
        });

        // Initialize client name search
        let clientNameTimeout;
        let isClientNameScanning = false;
        let lastClientNameInputTime = 0;

        $('#clientNameFilter').on('input', function(e) {
            const input = $(this);
            const currentTime = new Date().getTime();
            
            clearTimeout(clientNameTimeout);
            
            if (currentTime - lastClientNameInputTime < 100) {
                isClientNameScanning = true;
            }
            
            lastClientNameInputTime = currentTime;
            
            const searchValue = input.val();
            
            // Only search if at least 3 characters
            if (searchValue.length >= 3) {
                clientNameTimeout = setTimeout(() => {
                    $('.ui.search').eq(0).search('query', searchValue);
                }, 300);
            }
        });

        // Modify the client name search initialization
        $('#clientNameFilter').closest('.ui.search').search({
            apiSettings: {
                url: '/dpm/api/panelStatusController.php',
                method: 'GET',
                beforeSend: function(settings) {
                    settings.data = {
                        action: 'getClientNames',
                        search: $('#clientNameFilter').val()
                    };
                    return settings;
                }
            },
            fields: {
                results: 'items',
                title: 'name'
            },
            minCharacters: 3,
            onSelect: function(result, response) {
                if (result && result.project_name) {
                    $('#clientNameFilter').val(result.project_name);
                    // Hide the results dropdown
                    $(this).search('hide results');
                    return false; // Prevent default action
                }
            }
        });

        // Initialize project search with scanner functionality
        let scanTimeout;
        let isScanning = false;
        let lastInputTime = 0;

        // QR Code Scanner functionality for project filter
        $('#projectFilter').on('input', function(e) {
            const input = $(this);
            const currentTime = new Date().getTime();
            
            clearTimeout(scanTimeout);
            
            if (currentTime - lastInputTime < 100) {
                isScanning = true;
            }
            
            lastInputTime = currentTime;
            
            scanTimeout = setTimeout(() => {
                const scannedValue = input.val();
                
                if (isScanning) {
                    const splitValue = scannedValue.split('*');
                    if (splitValue.length > 1) {
                        const projectNo = splitValue[0].trim();
                        input.val(projectNo);
                        $('.ui.search').eq(1).search('query', projectNo);
                    }
                    isScanning = false;
                }
            }, 500);
        });

        // Modify the search initialization
        $('#projectFilter').closest('.ui.search').search({
            apiSettings: {
                url: '/dpm/dwc/materialsearch/api/search.php',
                method: 'GET',
                beforeSend: function(settings) {
                    settings.data = {
                        file: 'panel_status',
                        project: $('#projectFilter').val()
                    };
                    return settings;
                }
            },
            fields: {
                results: 'items',
                title: 'name'
            },
            minCharacters: 4,
            onSelect: function(result, response) {
                if (result && result.project_no) {
                    $('#projectFilter').val(result.project_no);
                    // Hide the results dropdown
                    $(this).search('hide results');
                    return false; // Prevent default action
                }
            }
        });

        // Handle apply filters click
        $('#applyFilters').click(function() {
            // Show table loader for the current tab
            showTableLoader(currentTabType);
            
            // Reset cache when applying new filters
            panelDataCache = {
                breaker: { data: [], pagination: null, page: 1 },
                panel: { data: [], pagination: null, page: 1 }
            };
            
            // Force refresh data
            fetchPanelStatus(1, true);
        });

        // Handle clear filters click
        $('#clearFilters').click(function() {
            // Clear only clientNameFilter and projectFilter
            $('#clientNameFilter').val('');
            $('#projectFilter').val('');
            
            // Hide any open search results
            $('#clientNameFilter').closest('.ui.search').search('hide results');
            $('#projectFilter').closest('.ui.search').search('hide results');
        });

        // Initialize tooltip handlers for the initial data
        initTooltipHandlers();
        
        // Use debounced resize handler to prevent performance issues
        $(window).resize(function() {
            // Clear the previous timer
            clearTimeout(resizeTimer);
            
            // Set a new timer
            resizeTimer = setTimeout(function() {
                // Adjust layout
                adjustLayout();
                
                // Update connecting lines
                updateConnectingLines();
                
                // If tooltip is visible, reposition it
                if ($('#tooltip-container').is(':visible')) {
                    const activeCircle = $('.status-circle:hover');
                    if (activeCircle.length) {
                        positionTooltip(activeCircle);
                    } else {
                        $('#tooltip-container').hide();
                    }
                }
            }, 100);
        });
        
        // Handle scroll events for tooltip repositioning
        $(window).scroll(function() {
            if ($('#tooltip-container').is(':visible')) {
                const activeCircle = $('.status-circle:hover');
                if (activeCircle.length) {
                    positionTooltip(activeCircle);
                } else {
                    $('#tooltip-container').hide();
                }
            }
        });
        
        // Handle zoom with ctrl+wheel
        $(window).on('wheel', function(e) {
            if (e.ctrlKey) {
                // Clear previous timer
                clearTimeout(resizeTimer);
                
                // Set a new timer
                resizeTimer = setTimeout(function() {
                    updateConnectingLines();
                }, 200);
            }
        });
        
        // Mobile navigation toggle
        $('.mobile-nav-toggle').on('click', function() {
            $('nav.navbar-default.navbar-static-side').toggleClass('show-mobile');
        });
        
        // Close mobile nav when clicking outside
        $(document).on('click', function(e) {
            if ($(window).width() <= 768) {
                const $target = $(e.target);
                if (!$target.closest('nav.navbar-default.navbar-static-side').length && 
                    !$target.closest('.mobile-nav-toggle').length) {
                    $('nav.navbar-default.navbar-static-side').removeClass('show-mobile');
                }
            }
        });
    });
</script>
</body>
</html>