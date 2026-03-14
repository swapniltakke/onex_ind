<?php
SharedManager::checkAuthToModule(12);
include_once '../core/index.php';
$project = $_GET["project"] ?: 0;
SharedManager::saveLog("log_checklist_form", "Search for Project: $project");
$menu_header_display = 'Checklist Form';
$checklist_form = "1";
$currentUser = isset($_SESSION['username']) ? $_SESSION['username'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>OneX | Checklist Form</title>
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
        margin-top: 10px !important;
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
    
    .scanner-icon {
        cursor: pointer;
        margin-left: 10px;
        vertical-align: middle;
    }
    .scanner-icon img {
        width: 24px;
        height: 24px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid black;
        padding: 5px;
    }
    .header {
        background-color: #f2f2f2;
        text-align: left;
        margin-bottom: 0 !important;
    }
    .yellow-bgc {
        background-color: #FFFF99;
    }
    .col1 {
        width: 15%;
    }
    .col2 {
        width: 40%;
    }
    .col3 {
        width: 15%;
    }
    .col4 {
        width: 20%;
    }
    .label-cell {
        text-align: left;
    }
    /* Add these styles to your existing <style> section */
    .passed-group input[type="radio"]:checked[value="ok"] + span::before {
        content: '';
        display: inline-block;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid #006400;
        background-color: #006400;
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
    }

    .passed-group input[type="radio"]:checked[value="notok"] + span::before {
        content: '';
        display: inline-block;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid #FF0000;
        background-color: #FF0000;
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
    }

    .passed-group input[type="radio"]:checked[value="na"] + span::before {
        content: '';
        display: inline-block;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid #808080;
        background-color: #808080;
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
    }

    .passed-group input[type="radio"] + span::before {
        content: '';
        display: inline-block;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid #ccc;
        background-color: white;
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
    }

    .passed-group input[type="radio"] {
        opacity: 0;
        position: absolute;
    }

    .passed-group label {
        position: relative;
        padding-left: 20px;
    }

    .passed-group {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
    }
    /* Custom styles for checkbox */
    .not-applicable-group input[type="checkbox"] {
        opacity: 0;
        position: absolute;
    }

    .not-applicable-group input[type="checkbox"] + span::before {
        content: '';
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #ccc;
        background-color: white;
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
    }

    .not-applicable-group input[type="checkbox"]:checked + span::before {
        background-color: #006400;
        border-color: #006400;
    }

    .not-applicable-group input[type="checkbox"]:checked + span::after {
        content: '✓';
        color: white;
        position: absolute;
        left: 3px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 12px;
    }

    .not-applicable-group label {
        position: relative;
        padding-left: 20px;
    }
    /* Add these styles to your existing <style> section */
    #wrapper {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    #page-wrapper {
        flex: 1;
        padding: 0; /* Remove default padding */
    }

    .gray-bg {
        padding-top: 0; /* Remove top padding */
    }

    .row {
        margin: 0; /* Remove default margins */
        /* padding-top: 15px; */ /* Add small top padding if needed */
    }

    /* Remove extra spacing from breadcrumb if present */
    .row.wrapper.border-bottom.white-bg.page-heading {
        margin: 0;
        padding: 10px 15px;
    }

    /* Adjust card spacing */
    .card {
        margin-top: 0;
        margin-bottom: 20px;
    }

    /* Remove extra space from br tag */
    #page-wrapper > br {
        display: none;
    }

    /* Adjust header spacing if needed */
    .navbar {
        margin-bottom: 0 !important;
    }

    /* Ensure proper content flow */
    .card-body {
        padding-top: 15px;
    }

    /* Fix table spacing */
    table {
        margin-bottom: 0;
    }

    /* Adjust the main content area */
    #page-wrapper .row:first-of-type {
        padding-top: 0;
    }
    /* Bootstrap spacing fixes */
    .container-fluid {
        padding-top: 0 !important;
    }

    .row > [class*='col-'] {
        padding-top: 0 !important;
    }
    /* Fix any fixed positioning issues */
    .fixed-top {
        position: relative !important;
    }

    #page-wrapper {
        margin-top: 0 !important;
    }
    .empty-checklist-message {
        text-align: center;
        padding: 20px;
        color: #666;
        font-style: italic;
        background-color: #f9f9f9;
    }
    /* Add to your existing <style> section */
    .measurement-input {
        display: flex;
        align-items: center;
        padding-left: 10px;
    }

    .measurement-input b {
        width: 30px; /* Fixed width for labels */
        margin-right: 5px;
    }

    .underline-input {
        width: 60px;
        border: none;
        border-bottom: 1px solid black;
        background: transparent;
        margin: 0 10px;
        padding: 2px 0;
        text-align: center;
        outline: none;
        text-align: left !important;
    }

    .underline-input:focus {
        border-bottom: 2px solid #00b5ad;
    }

    .unit {
        margin-left: 5px;
        font-weight: bold;
    }

    /* Optional: Add hover effect */
    .underline-input:hover {
        border-bottom-color: #666;
    }
    .remark-textarea {
        width: 100% !important;
        min-height: 60px !important;
        padding: 5px !important;
        border: 1px solid #ddd !important;
        border-radius: 4px !important;
        resize: vertical !important;
        font-family: inherit;
        font-size: inherit;
    }

    /* Ensure the focus state maintains the styling */
    .remark-textarea:focus {
        outline: none;
        border-color: #00b5ad !important;
        box-shadow: 0 0 0 2px rgba(0,181,173,0.2);
    }
    /* Add to your existing <style> section */

    .remark-textarea:disabled {
        opacity: 0.7 !important;
        background-color: #f5f5f5 !important;
        cursor: not-allowed !important;
        border: 1px solid #ddd !important;
        color: #000000 !important;
        -webkit-text-fill-color: #000000 !important;
        pointer-events: none !important;
    }

    .remark-textarea[disabled]:hover {
        cursor: not-allowed !important;
    }

    .production-order-input {
        border: none;
        border-bottom: 1px solid #000;
        background: transparent;
        padding: 2px 0;
        margin-left: 0 !important;
        outline: none;
        font-family: inherit;
        font-size: inherit;
        text-align: left !important;
    }

    .production-order-input:focus {
        border-bottom: 2px solid #00b5ad;
    }

    .production-order-input::placeholder {
        color: #999;
        font-style: italic;
    }

    .editable-field {
        border: none;
        border-bottom: 1px solid #000;
        background: transparent;
        padding: 2px 5px;
        outline: none;
        font-family: inherit;
        font-size: inherit;
        text-align: left !important;
    }

    .editable-field:focus {
        border-bottom: 2px solid #00b5ad;
    }

    .editable-field:disabled {
        opacity: 0.7;
        background-color: #f5f5f5;
        cursor: not-allowed;
        border-bottom: 1px solid #ddd !important;
    }

    .sub-item-dropdown {
        padding: 5px;
        border: 1px solid #000;
        border-radius: 3px;
        font-family: inherit;
        font-size: inherit;
        background-color: white;
        cursor: pointer;
        min-width: 120px;
    }

    .sub-item-dropdown:focus {
        outline: none;
        border-color: #00b5ad;
        box-shadow: 0 0 0 2px rgba(0,181,173,0.2);
    }

    .sub-item-dropdown:disabled {
        opacity: 0.7;
        background-color: #f5f5f5;
        cursor: not-allowed;
        border-color: #ddd;
    }

    #subItemOrSAPContainer {
        display: flex;
        align-items: center;
        gap: 10px;
        padding-left: 5px;
    }

    /* Add to your existing <style> section */
    #punch_list_table {
        table-layout: fixed;
        width: 100%;
        border-collapse: collapse;
        font-size: 12px; /* Base font size */
    }

    /* Cell styling */
    #punch_list_table td {
        vertical-align: top;
        padding: 8px;
        border: 1px solid black;
        word-wrap: break-word;
        overflow-wrap: break-word;
        hyphens: auto;
    }

    /* Input and textarea styling */
    #punch_list_table input,
    #punch_list_table textarea {
        width: 100%;
        padding: 6px;
        box-sizing: border-box;
        font-size: inherit;
        font-family: inherit;
        border: none;
        background: transparent;
        resize: vertical;
    }

    /* Date input specific styling */
    #punch_list_table .date-input {
        /* min-width: 120px; */ /* Minimum width for date fields */
        white-space: pre-wrap; /* Allow wrapping */
        word-break: break-all;
    }

    /* User name specific styling */
    #punch_list_table .by-input {
        white-space: pre-wrap;
        word-break: break-all;
        min-height: 40px;
    }

    /* Responsive design */
    @media screen and (max-width: 1200px) {
        #punch_list_table {
            font-size: 10px;
        }
        
        #punch_list_table td {
            padding: 6px;
        }
    }

    @media screen and (max-width: 992px) {
        #punch_list_table {
            font-size: 10px;
        }
        
        #punch_list_table td {
            padding: 4px;
        }
    }
    
    /* Table wrapper for horizontal scrolling on small screens */
    .reference-group-table {
        margin-bottom: 0px;
    }

    .punch-list-wrapper {
        margin-bottom: 40px;
    }

    .punch-input {
        border: none;
        background: transparent;
        width: 100%;
        padding: 4px;
        font-family: inherit;
        font-size: inherit;
    }

    .punch-input:focus {
        outline: none;
        background-color: rgba(0, 181, 173, 0.1);
    }

    .punch-list-table {
        font-size: 12px;
        width: 100%;
        border-collapse: collapse;
    }

    .punch-list-table td {
        vertical-align: top;
        padding: 4px;
        border: 1px solid black;
    }

    .punch-input {
        font-size: 12px;
        border: none;
        background: transparent;
        width: 100%;
        padding: 4px;
        font-family: inherit;
    }

    .punch-list-row textarea {
        font-size: 11px;
        border: none;
        background: transparent;
        width: 100%;
        resize: none;
        overflow-y: hidden;
        min-height: 40px;
    }

    /* Adjust column group widths */
    .punch-list-table colgroup {
        col:nth-child(1) { width: 3%; }
        col:nth-child(2) { width: 25%; }
        col:nth-child(3) { width: 6%; }
        col:nth-child(4) { width: 6%; }
        col:nth-child(5) { width: 8%; }
        col:nth-child(6) { width: 6%; }
        col:nth-child(7) { width: 6%; }
        col:nth-child(8) { width: 8%; }
        col:nth-child(9) { width: 6%; }
        col:nth-child(10) { width: 6%; }
        col:nth-child(11) { width: 8%; }
        col:nth-child(12) { width: 4%; }
        col:nth-child(13) { width: 4%; }
    }

    .punch-list-row textarea {
        border: none;
        background: transparent;
        width: 100%;
        resize: none;
        overflow-y: hidden;
    }

    .work-hrs-input, .code-input {
        text-align: center;
    }
    
    .punch-list-buttons {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .add-punch-list-row,
    .remove-punch-list-row {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .remove-punch-list-row {
        transition: opacity 0.3s ease;
    }

    .full-page-loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.7);
        z-index: 10001 !important;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }

    .full-page-loader .loader-content {
        background: white;
        padding: 40px 60px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        min-width: 300px;
    }

    .full-page-loader .spinner {
        border: 4px solid #f3f3f3;
        border-radius: 50%;
        border-top: 4px solid #1ab394;
        width: 50px;
        height: 50px;
        margin: 0 auto 20px;
        animation: spin 1s linear infinite;
    }

    #loaderMessage {
        font-size: 16px;
        color: #333;
        font-weight: 500;
        margin-top: 10px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Ensure toastr is visible above loader */
    .toast-container {
        z-index: 10002 !important;
    }

    .toast {
        z-index: 10002 !important;
    }

    .mandatory-field::after {
        content: ' *';
        color: red;
        font-weight: bold;
    }
</style>
<body>
<div id="wrapper">
    <?php $activePage = '/checklist_form.php'; ?>
    <?php include_once '../shared/sidebar.php'; ?>
    <div id="page-wrapper" class="gray-bg">
        <?php include_once 'header.php'; ?>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body" id="checklistFormContent" style="display: none;">
                        <div style="text-align: right; margin-bottom: 10px;">
                            <button class="btn btn-primary btn-sm" id="backButton" style="display: none;">
                                <i class="fa fa-arrow-left"></i> Back
                            </button>
                            <table id='highlighted_checklist_form'>
                                <colgroup>
                                    <col style="width: 15%;">
                                    <col style="width: 35%;">
                                    <col style="width: 30%;">
                                    <col style="width: 20%;">
                                </colgroup>
                                <tr>
                                    <td class="label-cell"><b>Checklist Name:</b></td>
                                    <td></td>
                                    <td style="text-align: left; border: 3px solid black; padding-left: 10px;">
                                        <b>Order-No.:</b> <span id="orderNoDisplay"></span>
                                    </td>
                                    <td style="text-align: left;">
                                        <div class="measurement-input">
                                            <b>UR:</b>
                                            <input type="text" id="urValue" class="underline-input">
                                            <span class="unit">kV</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-cell"><b>Project Name:</b></td>
                                    <td colspan="2"></td>
                                    <td style="text-align: left;">
                                        <div class="measurement-input">
                                            <b>IK:</b>
                                            <input type="text" id="ikValue" class="underline-input">
                                            <span class="unit">kA</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label-cell"><b>Order Processor:</b></td>
                                    <td colspan="2"></td>
                                    <td style="text-align: left;">
                                        <div class="measurement-input">
                                            <b>IR:</b>
                                            <input type="text" id="irValue" class="underline-input">
                                            <span class="unit">A</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="yellow-bgc">
                                    <td class="label-cell" style="border-top:3px solid black; border-left:3px solid black;">
                                        <b id="panelTypeLabel" class="mandatory-field">Panel Type</b>
                                    </td>
                                    <td colspan="2" style="padding: 0; border-top:3px solid black;">
                                        <div style="display: flex; width: 100%;">
                                            <div style="width: 70%; padding:3px; border-right: 1px solid black; text-align: left;" id="panelTypeCell"></div>
                                            <div style="width: 30%; padding:5px;" id="locationTypicalLabel"><b>Location / Typical Name:</b></div>
                                        </div>
                                    </td>
                                    <td style="border-top:3px solid black; border-right:3px solid black; text-align: left;" id="locationTypicalValue"></td>
                                </tr>
                                <tr class="yellow-bgc">
                                    <td class="label-cell" style="border-bottom:3px solid black; border-left:3px solid black;">
                                        <b>Production Order:</b>
                                    </td>
                                    <td colspan="2" style="padding: 0; border-bottom:3px solid black;">
                                        <div style="display: flex; width: 100%;">
                                            <div style="width: 70%; padding:3px; border-right: 1px solid black;">
                                                <div style="display: flex; align-items: center;">
                                                    <input type="text" 
                                                        id="productionOrderInput" 
                                                        class="underline-input production-order-input" 
                                                        style="width: 100px; margin: 0; text-align: left;"
                                                        placeholder="">
                                                </div>
                                            </div>
                                            <div style="width: 30%; padding:5px;" id="subItemLabel"><b class="mandatory-field">Sub Item:</b></div>
                                        </div>
                                    </td>
                                    <td style="border-bottom:3px solid black; border-right:3px solid black;">
                                        <div style="text-align: left; padding-left: 5px; display: flex; align-items: center; gap: 10px;" id="subItemOrSAPContainer">
                                            <span id="panelTypeDisplay" style="flex: 1;"></span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            </br>
                            <table id="main_checklist_form" style="width: 100%; border-collapse: collapse;">
                                <tr style="background-color: #f2f2f2; text-align: center;">
                                    <td style="border: 1px solid black; padding: 5px; width: 50%;">
                                        <b>Checklist Item</b>
                                    </td>
                                    <td style="border: 1px solid black; padding: 5px; width: 15%; height: 100px; position: relative;">
                                        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 50%; padding: 5px; border-bottom: 1px solid black; text-align: center;">
                                            Name/Stamp<br>(Worker)
                                        </div>
                                        <div style="position: absolute; top: 50%; left: 0; right: 0; bottom: 0; padding: 5px; text-align: center;">
                                            Passed
                                        </div>
                                    </td>
                                    <td style="border: 1px solid black; padding: 5px; width: 25%;">
                                        Remark
                                    </td>
                                    <td style="border: 1px solid black; padding: 5px; width: 10%;">
                                        Previous User
                                    </td>
                                </tr>
                            </table>
                            <!-- Add this after the main_checklist_form table -->
                            <div class="punch-list-wrapper">
                                <table id="punch_list_table" style="width: 100%; border-collapse: collapse;">
                                    <colgroup>
                                        <col style="width: 3%">  <!-- No. -->
                                        <col style="width: 25%"> <!-- Description -->
                                        <col style="width: 8%">  <!-- By -->
                                        <col style="width: 8%">  <!-- Date -->
                                        <col style="width: 12%"> <!-- Remark -->
                                        <col style="width: 8%">  <!-- By -->
                                        <col style="width: 8%">  <!-- Date -->
                                        <col style="width: 12%"> <!-- Remark -->
                                        <col style="width: 8%">  <!-- By -->
                                        <col style="width: 8%">  <!-- Date -->
                                        <col style="width: 12%"> <!-- Remark -->
                                        <col style="width: 4%">  <!-- Work Hrs. -->
                                        <col style="width: 4%">  <!-- Code -->
                                    </colgroup>
                                    <tr>
                                        <td colspan="13" style="background-color: #f2f2f2; border: 1px solid black; padding: 8px; text-align:center;">
                                            <b style="float: left;">Punch list</b>
                                            <span><b>Checklist Item</b></span>
                                        </td>
                                    </tr>
                                    <!-- Header rows will be inserted here by JavaScript -->
                                    <tbody id="punch_list_body">
                                        <!-- Dynamic rows will be inserted here -->
                                    </tbody>
                                    <tr>
                                        <td colspan="13" style="border: 1px solid black;">
                                            <div style="padding: 10px;">
                                                <div style="font-weight: bold; margin-bottom: 5px; text-align: initial;">Codes:</div>
                                                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
                                                    <div style="display: flex; flex-direction: column;">
                                                        <div>1= PM</div>
                                                        <div>5= VCB Assembling</div>
                                                        <div>9= Faulty Equipment/part</div>
                                                    </div>
                                                    <div style="display: flex; flex-direction: column;">
                                                        <div>2= Engineering</div>
                                                        <div>6= DTO</div>
                                                        <div>10= Other (production)</div>
                                                    </div>
                                                    <div style="display: flex; flex-direction: column;">
                                                        <div>3= Purchasing</div>
                                                        <div>7= LV Assembling</div>
                                                        <div>11= delivery (e.g. with</div>
                                                        <div style="padding-left: 25px;">missing parts</div>
                                                    </div>
                                                    <div style="display: flex; flex-direction: column;">
                                                        <div>4= Prefabrication</div>
                                                        <div>8= Final Assembling</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>    

                            <!-- Add Row Button -->
                            <div class="punch-list-wrapper-add-row" style="text-align: left; margin-top: 10px;">
                                <button id="addPunchListRow" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Add Row
                                </button>
                            </div>
                            <!-- Submit Button -->
                            <div style="margin-top: -20px; text-align: left;">
                                <button id="saveChecklistBtn" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Save Checklist
                                </button>
                                <button id="submitChecklistBtn" class="btn btn-success" style="margin-left: 10px;">
                                    <i class="fa fa-check"></i> Submit Checklist
                                </button>
                                <!-- NEW BUTTON HERE -->
                                <button id="downloadPdfBtn" class="btn btn-info" style="margin-left: 10px;">
                                    <i class="fa fa-file-pdf-o"></i> Download PDF
                                </button>
                            </div>
                        </div>
                        <div id="fileList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $footer_display = 'Checklist Form';
    include_once '../../assemblynotes/shared/footer.php'; ?>
</div>

<!-- Mainly scripts -->
<?php include_once '../../assemblynotes/shared/headerSemanticScripts.php' ?>
<script src="../../shared/shared.js"></script>
<script>
    // ===== CHECKLIST FORM SPECIFIC CODE =====
    const projectnumber = getUrlParameters()["project"];
    let activeAjaxRequests = 0;
    var currentUser = <?php echo json_encode($currentUser); ?>;
    var isComponentProduct = false;

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

    // Helper function to show full page loader
    function showFullPageLoader(message = 'Loading...') {
        const loader = document.getElementById('fullPageLoader');
        if (loader) {
            updateFullPageLoaderMessage(message);
            loader.style.display = 'flex';
        }
    }

    // Helper function to hide full page loader
    function hideFullPageLoader() {
        const loader = document.getElementById('fullPageLoader');
        if (loader) {
            loader.style.display = 'none';
        }
    }

    // Helper function to update loader message
    function updateFullPageLoaderMessage(message) {
        const loaderMessage = document.getElementById('loaderMessage');
        if (loaderMessage) {
            loaderMessage.textContent = message;
        }
    }

    // Add a function to reset the checklist form state
    function resetChecklistFormState() {
        // Show the buttons
        $('#saveChecklistBtn, #submitChecklistBtn').show();
        
        // Remove any previous submitted message
        $('.alert.alert-info').remove();
        
        // Enable all inputs
        $('.addon-input, .serial-input, .passed-radio, .remark-textarea, #urValue, #ikValue, #irValue, #productionOrderInput')
            .prop('disabled', false)
            .css({
                'opacity': '1',
                'cursor': 'auto'
            });
    }

    var currentDirectoryPath = '';
    // REPLACE THE listChecklistForm FUNCTION with this:
    function listChecklistForm(checklistId, lineId, productId, stationId, salesOrderNo, panelNumber, subItem = '', actionFromSave = '') {
        // Show the content area when function is called
        $('#checklistFormContent').show();
        
        // Use the global loader from header.php
        showGlobalLoader('Loading checklist...');        
        
        $.ajax({
            url: '/dpm/dwc/api/checklistAPI.php?type=load_checklist',
            method: 'POST',
            data: {
                sales_order_no: salesOrderNo,
                panel_no: panelNumber,
                checklist_id: checklistId,
                line_id: lineId,
                product_id: productId,
                station_id: stationId,
                sub_item: subItem,
                action_from_save: actionFromSave
            },
            success: function(response) {
                enableChecklistButtons();
                try {
                    const data = JSON.parse(response);
                    if (data.autoSelected) {
                        if (data.autoSelected.line_id && $('#lineSearch').dropdown('get value') === '') {
                            $('#lineSearch').dropdown('set selected', data.autoSelected.line_id);
                        }
                        if (data.autoSelected.product_id && $('#productSearch').dropdown('get value') === '') {
                            $('#productSearch').dropdown('set selected', data.autoSelected.product_id);
                        }
                        if (data.autoSelected.station_id && $('#stationSearch').dropdown('get value') === '') {
                            $('#stationSearch').dropdown('set selected', data.autoSelected.station_id);
                        }
                    }
                    resetChecklistFormState();

                    // Set the component flag
                    isComponentProduct = data.headerInfo.is_component === 1;
                    updateUIForProductType();

                    // Update header information and status
                    if (data.status === "1") {
                        $('#saveChecklistBtn, #submitChecklistBtn').hide();
                        const messageHtml = `
                            <div class="alert alert-info" style="margin-top: -10px; padding: 15px; background-color: #1ab394; border: 1px solid #b8e2ef; border-radius: 4px; color: #ffffff; font-size: 14px; text-align: center;">
                                <i class="fa fa-info-circle"></i> This checklist has already been submitted and cannot be modified.
                            </div>`;
                        $('#main_checklist_form').after(messageHtml);

                        // Adjust download button position
                        $('#downloadPdfBtn')
                            .css({
                                'margin-left': '0px',
                                'margin-top': '20px'
                            })
                            .show();

                        $('.punch-list-buttons').hide();

                        // Add CSS for disabled states
                        $('<style>')
                            .text(`
                                .passed-radio:disabled + span::before {
                                    opacity: 0.7 !important;
                                    cursor: not-allowed !important;
                                }

                                .passed-group input[type="radio"]:disabled + span {
                                    opacity: 0.7 !important;
                                    cursor: not-allowed !important;
                                }

                                .passed-group label {
                                    position: relative !important;
                                }

                                .passed-group input[type="radio"]:checked:disabled + span::before {
                                    opacity: 0.7 !important;
                                }

                                .addon-input:disabled,
                                .serial-input:disabled,
                                .remark-textarea:disabled,
                                #urValue:disabled,
                                #ikValue:disabled,
                                #irValue:disabled,
                                #productionOrderInput:disabled,
                                .editable-field:disabled,
                                .sub-item-dropdown:disabled {
                                    opacity: 0.7 !important;
                                    background-color: #f5f5f5 !important;
                                    cursor: not-allowed !important;
                                    border: 1px solid #ddd !important;
                                }

                                textarea:disabled {
                                    resize: none !important;
                                }

                                .underline-input:disabled {
                                    opacity: 0.7 !important;
                                    background-color: #f5f5f5 !important;
                                    cursor: not-allowed !important;
                                    border-bottom: 1px solid #ddd !important;
                                }
                            `)
                            .appendTo('head');

                        // Disable header inputs
                        $('#urValue, #ikValue, #irValue, #productionOrderInput, #panelTypeInput, #subItemDropdown').prop('disabled', true);
                    } else {
                        $('#downloadPdfBtn')
                            .css({
                                'margin-left': '10px',
                                'margin-top': '0px'
                            })
                            .hide();
                        
                        // Enable all inputs if status is 0
                        $('#urValue, #ikValue, #irValue, #productionOrderInput, #panelTypeInput, #subItemDropdown').prop('disabled', false);
                    }

                    // Update header information
                    if (data.headerInfo) {
                        updateHeaderInformation(data.headerInfo, salesOrderNo);
                    }

                    const table = $('#main_checklist_form');
                    table.empty();
                    $('#punch_list_table').remove();

                    // Check if we should load items
                    // Load items only if:
                    // 1. Non-component product, OR
                    // 2. Component product AND subItem is provided
                    const shouldLoadItems = !isComponentProduct || (isComponentProduct && subItem && subItem.trim() !== '');

                    if (shouldLoadItems && Array.isArray(data.items) && data.items.length > 0) {
                        // Group items by checklist_reference
                        const groupedItems = {};
                        data.items.forEach(item => {
                            const reference = item.checklist_reference?.trim() || 'default';
                            if (!groupedItems[reference]) {
                                groupedItems[reference] = [];
                            }
                            groupedItems[reference].push(item);
                        });

                        // Get all references to determine the last one
                        const references = Object.keys(groupedItems);
                        const lastReference = references[references.length - 1];

                        // Process each group
                        Object.entries(groupedItems).forEach(([reference, items]) => {
                            if (!reference) {
                                console.error('Invalid reference found in grouped items');
                                return;
                            }

                            const groupTable = $('<table>').addClass('reference-group-table').css('width', '100%');
                            
                            // Add reference header
                            const headerRow = createReferenceHeaderRow(reference);
                            groupTable.append(headerRow);

                            // Add items for this reference
                            items.forEach(item => {
                                const row = createItemRow(item, data.status);
                                groupTable.append(row);
                            });

                            // Add the group table to the main form
                            $('#main_checklist_form').append(groupTable);

                            // Add punch list table after this reference group
                            const safeId = createSafeId(reference);
                            const isLastPunchList = reference === lastReference;
                            const punchListTableHtml = `
                                <div class="punch-list-wrapper" data-reference="${reference}">
                                    <table class="punch-list-table" id="${safeId}" style="width: 100%; border-collapse: collapse;">
                                        <tr>
                                            <td colspan="13" style="background-color: #f2f2f2; border: 1px solid black; padding: 8px; text-align:center;">
                                                <b style="float: left;">Punch list</b>
                                                <span><b>${reference}</b></span>
                                            </td>
                                        </tr>
                                        <tbody class="punch-list-header"></tbody>
                                        <tbody class="punch-list-body"></tbody>
                                        ${isLastPunchList ? `
                                        <tr>
                                            <td colspan="13" style="border: 1px solid black;">
                                                <div style="padding: 10px;">
                                                    <div style="font-weight: bold; margin-bottom: 5px; text-align: initial;">Codes:</div>
                                                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
                                                        <div style="flex: 1; text-align: left">
                                                            1= PM<br>
                                                            5= VCB Assembling<br>
                                                            9= Faulty Equipment/part
                                                        </div>
                                                        <div style="flex: 1; text-align: left">
                                                            2= Engineering<br>
                                                            6= DTO<br>
                                                            10= Other (production)
                                                        </div>
                                                        <div style="flex: 1; text-align: left">
                                                            3= Purchasing<br>
                                                            7= LV Assembling<br>
                                                            11= delivery (e.g. with missing parts)
                                                        </div>
                                                        <div style="flex: 1; text-align: left">
                                                            4= Prefabrication<br>
                                                            8= Final Assembling
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        ` : ''}
                                    </table>
                                    <div style="text-align: left; margin-top: 10px; ${data.status === "1" ? 'display: none;' : ''}" class="punch-list-buttons">
                                        <button class="btn btn-primary add-punch-list-row" data-reference="${reference}">
                                            <i class="fa fa-plus"></i> Add Row
                                        </button>
                                        <button class="btn btn-danger remove-punch-list-row" data-reference="${reference}" style="margin-left: 10px; display: none;">
                                            <i class="fa fa-minus"></i> Remove Row
                                        </button>
                                    </div>
                                </div>
                            `;
                            
                            // Add punch list table after the reference group table
                            if (data.status === "1") {
                                // If status is 1, only show punch list if it has data
                                if (data.punchList && data.punchList[reference] && data.punchList[reference].length > 0) {
                                    groupTable.after(punchListTableHtml);
                                }
                            } else {
                                // If status is not 1, always show the punch list table
                                groupTable.after(punchListTableHtml);
                            }
                            
                            // Initialize the punch list table
                            updateTableHeaders(reference);
                            initializePunchList(reference);

                            // Handle punch list data if available
                            if (data.punchList && data.punchList[reference]) {
                                populatePunchListData(reference, data.punchList[reference], data.status);
                            }

                            // Add spacing between groups
                            groupTable.after('<br>');
                        });
                        
                        initializeChecklistHandlers();
                        initializeRemarkHandlers();
                    } else {
                        // For component products without subItem, show only header
                        // For non-component products with no items, show empty message
                        // if (!isComponentProduct) {
                            const emptyRow = `
                                <tr>
                                    <td colspan="4" class="empty-checklist-message">
                                        No checklist items available for the selected criteria
                                    </td>
                                </tr>`;
                            table.append(emptyRow);
                        // }
                        
                        // Hide punch list elements when no items are available
                        $('.punch-list-wrapper').hide();
                        $('.punch-list-wrapper-add-row').hide();
                        $('#saveChecklistBtn, #submitChecklistBtn, #downloadPdfBtn').hide();
                    }

                } catch (error) {
                    console.error('Error parsing response:', error);
                    toastr.error('Error loading checklist data');
                }
            },
            error: function(xhr, status, error) {
                // Your existing error code
                $('#checklistFormContent').hide(); // Hide on error
                disableChecklistButtons();
                console.error('Ajax request failed:', error);
                toastr.error('Failed to load checklist data');
            },
            complete: function() {
                // Hide the global loader from header.php
                hideGlobalLoader();
                isSaving = false;
                $('body').css('pointer-events', 'auto');
            }
        });
    }

    function updateUIForProductType() {
        if (isComponentProduct) {
            // Component Product - Show Sub Item dropdown, make fields editable
            $('#panelTypeLabel').addClass('mandatory-field');
            $('#locationTypicalLabel').html('<b>Location / Typical Name:</b>');
            $('#subItemLabel').html('<b>SAP-Pos.:</b>');
            
            // Make panel type editable
            $('#panelTypeCell').html(`
                <input type="text" id="panelTypeInput" class="editable-field" style="width: 100%;" placeholder="Enter Panel Type">
            `);
            
            // Update location/typical to show as EDITABLE UNDERLINE with "/" separator
            $('#locationTypicalValue').html(`
                <div style="display: flex; align-items: center; gap: 5px; padding-left: 5px;">
                    <input type="text" id="locationValue" class="underline-input" style="width: 150px;" placeholder="Location">
                    <span style="font-weight: bold;">/</span>
                    <input type="text" id="typicalValue" class="underline-input" style="width: 150px;" placeholder="Typical">
                </div>
            `);
            
            // Add Sub Item dropdown in the same box as panelTypeDisplay - MANDATORY
            const subItemHtml = `
                <div style="width: 60%; padding:5px;" id="subItemLabel"><b class="mandatory-field">Sub Item:</b>
                <select id="subItemDropdown" class="sub-item-dropdown" style="width: 120px; padding: 5px; border: 1px solid #000; border-radius: 3px;" required>
                    <option value="">-- Select --</option>
                    <option value="-">-</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                    <option value="E">E</option>
                    <option value="F">F</option>
                    <option value="G">G</option>
                    <option value="H">H</option>
                    <option value="I">I</option>
                    <option value="J">J</option>
                </select>
                </div>
            `;
            
            // Update the container to show both panelTypeDisplay and subItemDropdown
            $('#subItemOrSAPContainer').html(`
                <span id="panelTypeDisplay" style="flex: 1; text-align: left; padding-left: 5px;"></span>
                ${subItemHtml}
            `);
            
            // Make production order non-mandatory
            $('#productionOrderInput').prop('required', false);
        } else {
            // Non-Component Product - Hide Sub Item, make fields non-editable
            $('#panelTypeLabel').removeClass('mandatory-field').text('Panel Type:');
            $('#locationTypicalLabel').html('<b>Location / Typical Name:</b>');
            $('#subItemLabel').html('<b>SAP-Pos.:</b>');
            
            // Keep panel type as non-editable text
            // Location and typical are already non-editable
            // Production order remains editable
            
            // Update location/typical to show as simple text with "/" separator (non-editable)
            $('#locationTypicalValue').html(`
                <span id="locationTypicalDisplay" style="text-align: left; padding-left: 5px;"></span>
            `);
            
            // Show SAP-Pos display only (no dropdown)
            $('#subItemOrSAPContainer').html(`
                <span id="panelTypeDisplay" style="text-align: left; padding-left: 5px;"></span>
            `);
        }
    }

    // Helper function to create reference header row
    function createReferenceHeaderRow(reference) {
        return `
            <tr style="background-color: #f2f2f2; text-align: center;">
                <td style="border: 1px solid black; padding: 5px; width: 50%;">
                    <b>${reference}</b>
                </td>
                <td style="border: 1px solid black; padding: 5px; width: 15%; height: 100px; position: relative;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 50%; padding: 5px; border-bottom: 1px solid black; text-align: center;">
                        Name/Stamp<br>(Worker)
                    </div>
                    <div style="position: absolute; top: 50%; left: 0; right: 0; bottom: 0; padding: 5px; text-align: center;">
                        Passed
                    </div>
                </td>
                <td style="border: 1px solid black; padding: 5px; width: 25%;">
                    Remark
                </td>
                <td style="border: 1px solid black; padding: 5px; width: 10%;">
                    Previous User
                </td>
            </tr>
        `;
    }

    function createItemRow(item, status) {
        // Get the currently selected station_id from the dropdown
        const currentStationId = $('#stationSearch').dropdown('get value');

        let row = `
            <tr data-checklist-id="${item.checklist_id}" 
            data-line-id="${item.line_id}" 
            data-product-id="${item.product_id}" 
            data-station-id="${currentStationId}"
            data-item-id="${item.id}"
            data-checklist-data-id="${item.checklist_data_id || ''}"
            data-checklist-item-id="${item.id}">
            <td style="border: 1px solid black; padding: 5px; text-align: left;">
                <div style="display: flex; align-items: baseline; flex-wrap: wrap;">
                    ${item.checklist_item}`;

        // Add addon content based on type (keep existing addon content code)
        if (item.add_on === '1') {
            row += `
                <span style="display: inline-block; margin-left: 5px; position: relative;">
                    <input type="text" 
                        class="form-control addon-input" 
                        value="${item.add_on_value || ''}"
                        data-checklist-id="${item.checklist_id}"
                        data-line-id="${item.line_id}"
                        data-product-id="${item.product_id}"
                        data-station-id="${item.station_id}"
                        data-item-id="${item.id}"
                        data-item-name="${item.checklist_item}"
                        data-reference="${item.checklist_reference}"
                        placeholder=""
                        ${status === "1" ? 'disabled' : ''}
                        style="width: 250px; 
                            border: none; 
                            border-bottom: 1px solid #000; 
                            padding: 2px 5px;
                            margin-left: 5px;
                            background: ${status === "1" ? '#f5f5f5' : 'transparent'};
                            opacity: ${status === "1" ? '0.7' : '1'};
                            cursor: ${status === "1" ? 'not-allowed' : 'text'};
                            outline: none;">
                </span>`;
        } else if (item.add_on === '2') {
            // Keep existing serial number inputs code
            row += `
                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; width: 33%; font-weight: bold;">
                            Serial no. L1:
                        </td>
                        <td style="border: 1px solid black; padding: 5px; width: 33%; font-weight: bold;">
                            Serial no. L2:
                        </td>
                        <td style="border: 1px solid black; padding: 5px; width: 33%; font-weight: bold;">
                            Serial no. L3:
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black; padding: 5px; width: 33%;">
                            <input type="text" 
                                class="form-control serial-input" 
                                data-type="L1"
                                value="${item.serial_l1 || ''}"
                                data-item-id="${item.id}"
                                data-checklist-id="${item.checklist_id}"
                                data-line-id="${item.line_id}"
                                data-product-id="${item.product_id}"
                                data-station-id="${item.station_id}"
                                data-reference="${item.checklist_reference}"
                                ${status === "1" ? 'disabled' : ''}
                                style="width: 100%; padding: 5px;
                                    background: ${status === "1" ? '#f5f5f5' : '#fff'};
                                    opacity: ${status === "1" ? '0.7' : '1'};
                                    cursor: ${status === "1" ? 'not-allowed' : 'text'};">
                        </td>
                        <td style="border: 1px solid black; padding: 5px; width: 33%;">
                            <input type="text" 
                                class="form-control serial-input" 
                                data-type="L2"
                                value="${item.serial_l2 || ''}"
                                data-item-id="${item.id}"
                                data-checklist-id="${item.checklist_id}"
                                data-line-id="${item.line_id}"
                                data-product-id="${item.product_id}"
                                data-station-id="${item.station_id}"
                                data-reference="${item.checklist_reference}"
                                ${status === "1" ? 'disabled' : ''}
                                style="width: 100%; padding: 5px;
                                    background: ${status === "1" ? '#f5f5f5' : '#fff'};
                                    opacity: ${status === "1" ? '0.7' : '1'};
                                    cursor: ${status === "1" ? 'not-allowed' : 'text'};">
                        </td>
                        <td style="border: 1px solid black; padding: 5px; width: 33%;">
                            <input type="text" 
                                class="form-control serial-input" 
                                data-type="L3"
                                value="${item.serial_l3 || ''}"
                                data-item-id="${item.id}"
                                data-checklist-id="${item.checklist_id}"
                                data-line-id="${item.line_id}"
                                data-product-id="${item.product_id}"
                                data-station-id="${item.station_id}"
                                data-reference="${item.checklist_reference}"
                                ${status === "1" ? 'disabled' : ''}
                                style="width: 100%; padding: 5px;
                                    background: ${status === "1" ? '#f5f5f5' : '#fff'};
                                    opacity: ${status === "1" ? '0.7' : '1'};
                                    cursor: ${status === "1" ? 'not-allowed' : 'text'};">
                        </td>
                    </tr>
                </table>`;
        }

        // Close the checklist item div and add radio buttons and remark
        // Modified the remark textarea part to be disabled if it has content
        const hasRemark = item.remark && item.remark.trim() !== '';
        row += `
                    </div>
                </td>
                <td style="border: 1px solid black; padding: 5px;">
                    <div class="passed-group">
                        <label style="opacity: ${status === "1" ? '0.7' : '1'}; cursor: ${status === "1" ? 'not-allowed' : 'pointer'};">
                            <input type="radio" 
                                name="passed_${item.id}" 
                                class="passed-radio" 
                                value="ok"
                                data-item-id="${item.id}"
                                ${item.passed === 'ok' ? 'checked' : ''}
                                ${status === "1" ? 'disabled' : ''}>
                            <span>Ok</span>
                        </label>
                        <label style="opacity: ${status === "1" ? '0.7' : '1'}; cursor: ${status === "1" ? 'not-allowed' : 'pointer'};">
                            <input type="radio" 
                                name="passed_${item.id}" 
                                class="passed-radio" 
                                value="notok"
                                data-item-id="${item.id}"
                                ${item.passed === 'notok' ? 'checked' : ''}
                                ${status === "1" ? 'disabled' : ''}>
                            <span>Not Ok</span>
                        </label>
                        <label style="opacity: ${status === "1" ? '0.7' : '1'}; cursor: ${status === "1" ? 'not-allowed' : 'pointer'};">
                            <input type="radio" 
                                name="passed_${item.id}" 
                                class="passed-radio" 
                                value="na"
                                data-item-id="${item.id}"
                                ${item.passed === 'na' ? 'checked' : ''}
                                ${status === "1" ? 'disabled' : ''}>
                            <span>NA</span>
                        </label>
                    </div>
                </td>
                <td style="border: 1px solid black; padding: 5px;">
                    <textarea 
                        class="remark-textarea" 
                        data-item-id="${item.id}"
                        ${(status === "1" || (item.remark && item.remark.trim() !== '')) ? 'disabled' : ''}
                        style="width: 100%; 
                            min-height: 60px; 
                            padding: 5px; 
                            border: 1px solid #ddd !important; 
                            border-radius: 4px; 
                            resize: vertical;
                            font-family: inherit;
                            font-size: inherit;
                            background-color: ${(status === "1" || (item.remark && item.remark.trim() !== '')) ? '#f5f5f5' : '#fff'} !important;
                            opacity: ${(status === "1" || (item.remark && item.remark.trim() !== '')) ? '0.7' : '1'} !important;
                            cursor: ${(status === "1" || (item.remark && item.remark.trim() !== '')) ? 'not-allowed' : 'text'} !important;
                            pointer-events: ${(status === "1" || (item.remark && item.remark.trim() !== '')) ? 'none' : 'auto'};
                            -webkit-text-fill-color: #000000 !important;
                            color: #000000 !important;"
                        placeholder="Enter remark...">${item.remark || ''}</textarea>
                </td>
                <td style="border: 1px solid black; padding: 5px; text-align: center;">
                    ${item.previous_user || '&nbsp;'}
                </td>
            </tr>`;

        return row;
    }

    // Add this to the updateHeaderInformation function in checklist_form.php
    function updateHeaderInformation(headerInfo, salesOrderNo) {
        // Update Checklist Name
        $('td:contains("Checklist Name:")').next().text(headerInfo.checklistName || '')
            .css({
                'text-align': 'left',
                'padding-left': '10px'
            });

        // Update Project Name
        $('td:contains("Project Name:")').next().text(headerInfo.projectName || '')
            .css({
                'text-align': 'left',
                'padding-left': '10px'
            });

        // Update Order Processor
        $('td:contains("Order Processor:")').next().text(headerInfo.orderProcessor || '')
            .css({
                'text-align': 'left',
                'padding-left': '10px'
            });

        // Update the measurement values - now using MTool data if available
        $('#urValue').val(headerInfo.ur_value || '');
        $('#ikValue').val(headerInfo.ik_value || '');
        $('#irValue').val(headerInfo.ir_value || '');
        $('#orderNoDisplay').text(salesOrderNo || '');

        const dropdownValueFetch = $('#panelSearch').dropdown('get value') || '';
        const panelTypeFetch = dropdownValueFetch.split('|')[0] || '';

        if (isComponentProduct) {
            // Component Product
            $('#panelTypeInput').val(headerInfo.panelType || '');
            
            // Display location and typical as EDITABLE UNDERLINE inputs
            $('#locationValue').val(headerInfo.location_name || '');
            $('#typicalValue').val(headerInfo.typical_name || '');
            
            // Set Sub Item dropdown value
            $('#subItemDropdown').val(headerInfo.sub_item || '');
            
            // Display panel type as non-editable text in the same box
            $('#panelTypeDisplay').text(panelTypeFetch || '');
            $('#productionOrderInput').val(headerInfo.production_order_no || '');
        } else {
            // Non-Component Product
            $('#panelTypeCell').text(headerInfo.panelType || '')
                .css({
                    'text-align': 'left',
                    'padding-left': '10px'
                });
            
            // Display location and typical with "/" separator (non-editable)
            const locationTypicalDisplay = `${headerInfo.location_name || ''} / ${headerInfo.typical_name || ''}`;
            $('#locationTypicalDisplay').text(locationTypicalDisplay);
            
            $('#panelTypeDisplay').text(panelTypeFetch || '');
            $('#productionOrderInput').val(headerInfo.production_order_no || '');
        }
        
        console.log("Updated header information with MTool data:", {
            urValue: headerInfo.ur_value,
            ikValue: headerInfo.ik_value,
            irValue: headerInfo.ir_value,
            isComponent: headerInfo.is_component,
            locationName: headerInfo.location_name,
            typicalName: headerInfo.typical_name,
            subItem: headerInfo.sub_item
        });
    }

    // NEW: Function to validate sub_item for component products
    function validateSubItemForComponent() {
        if (isComponentProduct) {
            const subItemValue = $('#subItemDropdown').val();
            if (!subItemValue || subItemValue.trim() === '') {
                Swal.fire({
                    title: 'Missing Required Field',
                    html: `
                        <div style="margin: 20px;">
                            <p style="color: #666; font-size: 16px;">
                                Please select a <strong>Sub Item</strong> before proceeding.
                            </p>
                            <div style="text-align: left; margin-top: 15px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
                                <p style="margin: 0; color: #333;">
                                    <i class="fa fa-info-circle" style="color: #1ab394; margin-right: 10px;"></i>
                                    Sub Item is a mandatory field for Component products.
                                </p>
                            </div>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonText: 'Ok',
                    confirmButtonColor: '#1ab394',
                    width: '500px'
                });
                return false;
            }
        }
        return true;
    }

    function initializeChecklistHandlers() {
        // Handle Enter button clicks for free text
        $('.enter-addon-value').on('click', function() {
            const $input = $(this).closest('.input-group').find('.addon-input');
            saveAddonValue($input);
        });

        // Handle Enter key press
        $('.addon-input, .serial-input').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                $(this).closest('.input-group').find('button').click();
            }
        });

        // Submit Checklist Button Handler
        $('#submitChecklistBtn').on('click', function() {
            if ($(this).prop('disabled')) return;
            
            // NEW: Validate sub_item for component products
            if (!validateSubItemForComponent()) {
                return;
            }
            
            if (validateChecklist()) {
                // Show loader and disable page interaction
                showLoaderAndSave('submit');
            }
        });

        // Save Checklist Button Handler
        $('#saveChecklistBtn').on('click', function() {
            if ($(this).prop('disabled')) return;
            
            // NEW: Validate sub_item for component products
            if (!validateSubItemForComponent()) {
                return;
            }
            
            if (!validateBaseFields()) {
                return false;
            }
            if (validateSaveChecklist()) {
                // Show loader and disable page interaction
                showLoaderAndSave('save');
            }
        });
    }

    function saveAddonValue($input) {
        const data = {
            checklist_id: $input.data('checklist-id'),
            line_id: $input.data('line-id'),
            product_id: $input.data('product-id'),
            station_id: $input.data('station-id'),
            item_id: $input.data('item-id'),
            item_name: $input.data('item-name'),
            reference: $input.data('reference'),
            addon_type: '1',
            addon_value: $input.val()
        };
        
        saveChecklistData(data);
    }

    function saveSerialValue($input) {
        const serialType = $input.data('type');
        const data = {
            checklist_id: $input.data('checklist-id'),
            line_id: $input.data('line-id'),
            product_id: $input.data('product-id'),
            station_id: $input.data('station-id'),
            item_id: $input.data('item-id'),
            item_name: $input.data('item-name'),
            reference: $input.data('reference'),
            addon_type: '2',
            [`serial_${serialType.toLowerCase()}`]: $input.val()
        };
        
        saveChecklistData(data);
    }

    function validateBaseFields() {
        const requiredFields = {
            'Project Number': $('#projectSearchInput').val()?.trim(),
            'Panel': $('#panelSearch').dropdown('get value')?.trim(),
            'Checklist': $('#checklistSearch').dropdown('get value')?.trim(),
            'Line': $('#lineSearch').dropdown('get value')?.trim(),
            'Product': $('#productSearch').dropdown('get value')?.trim()
        };

        const missingFields = Object.entries(requiredFields)
            .filter(([_, value]) => !value)
            .map(([field, _]) => field);

        if (missingFields.length > 0) {
            Swal.fire({
                html: `
                    <div style="margin: 20px;">
                        <p style="color: #666; font-size: 16px;">
                            Please select all required base fields before proceeding:
                        </p>
                        <div style="text-align: left; margin-top: 15px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
                            <table style="width: 100%; border-collapse: collapse; margin: 0 auto;">
                                <thead>
                                    <tr>
                                        <th style="border: 1px solid #ddd; padding: 12px; background-color: #e7e7e7; text-align: center; font-size: 1.1em; font-weight: bold;">
                                            Missing Required Fields
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${missingFields.map(field => `
                                        <tr>
                                            <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
                                                ${field}
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'Ok',
                confirmButtonColor: '#1ab394',
                width: '500px'
            });
            return false;
        }
        return true;
    }

    // Add this new validation function for Component product header fields
    function validateComponentHeaderFields(isSubmit = false) {
        const urValue = $('#urValue').val().trim();
        const ikValue = $('#ikValue').val().trim();
        const irValue = $('#irValue').val().trim();
        const panelTypeValue = $('#panelTypeInput').val().trim();
        const subItemValue = $('#subItemDropdown').val().trim();

        if (isSubmit) {
            // For Submit: All fields must be filled
            const missingFields = [];
            
            if (!urValue) missingFields.push('UR Value');
            if (!ikValue) missingFields.push('IK Value');
            if (!irValue) missingFields.push('IR Value');
            if (!panelTypeValue) missingFields.push('Panel Type');
            if (!subItemValue) missingFields.push('Sub Item');

            if (missingFields.length > 0) {
                const tableHtml = `
                    <div style="margin-top: 1rem; margin-bottom: 1rem;">
                        <table style="width: 100%; border-collapse: collapse; margin: 0 auto;">
                            <thead>
                                <tr>
                                    <th style="border: 1px solid #ddd; padding: 12px; background-color: #e7e7e7; text-align: center; font-size: 1.1em; font-weight: bold;">
                                        Required Fields for Component Product
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                ${missingFields.map(field => `
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px;">${field}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    <div style="color: #ff0000; margin-top: 1rem;">
                        All fields are mandatory for Component products
                    </div>`;

                Swal.fire({
                    title: 'Missing Required Fields',
                    html: tableHtml,
                    icon: 'warning',
                    confirmButtonText: 'Ok',
                    width: '600px'
                });
                return false;
            }
        } else {
            // For Save: At least one field must be filled
            if (!urValue && !ikValue && !irValue && !panelTypeValue && !subItemValue) {
                Swal.fire({
                    title: 'Cannot Save Empty Header',
                    html: `
                        <div style="margin: 20px;">
                            <p style="color: #666; font-size: 16px;">
                                Please enter at least one of the following for Component product:
                            </p>
                            <div style="text-align: left; margin-top: 15px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
                                <ul style="list-style-type: none; padding-left: 0;">
                                    <li style="margin: 10px 0; display: flex; align-items: center;">
                                        <i class="fa fa-chevron-right" style="margin-right: 10px; color: #1ab394;"></i>
                                        UR Value
                                    </li>
                                    <li style="margin: 10px 0; display: flex; align-items: center;">
                                        <i class="fa fa-chevron-right" style="margin-right: 10px; color: #1ab394;"></i>
                                        IK Value
                                    </li>
                                    <li style="margin: 10px 0; display: flex; align-items: center;">
                                        <i class="fa fa-chevron-right" style="margin-right: 10px; color: #1ab394;"></i>
                                        IR Value
                                    </li>
                                    <li style="margin: 10px 0; display: flex; align-items: center;">
                                        <i class="fa fa-chevron-right" style="margin-right: 10px; color: #1ab394;"></i>
                                        Panel Type
                                    </li>
                                    <li style="margin: 10px 0; display: flex; align-items: center;">
                                        <i class="fa fa-chevron-right" style="margin-right: 10px; color: #1ab394;"></i>
                                        Sub Item
                                    </li>
                                </ul>
                            </div>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonText: 'Ok',
                    confirmButtonColor: '#1ab394',
                    width: '550px'
                });
                return false;
            }
        }
        
        return true;
    }

    // Add this new validation function for non-Component product header fields
    function validateNonComponentHeaderFields(isSubmit = false) {
        const urValue = $('#urValue').val().trim();
        const ikValue = $('#ikValue').val().trim();
        const irValue = $('#irValue').val().trim();

        if (isSubmit) {
            // For Submit: All fields must be filled
            const missingFields = [];
            
            if (!urValue) missingFields.push('UR Value');
            if (!ikValue) missingFields.push('IK Value');
            if (!irValue) missingFields.push('IR Value');

            if (missingFields.length > 0) {
                const tableHtml = `
                    <div style="margin-top: 1rem; margin-bottom: 1rem;">
                        <table style="width: 100%; border-collapse: collapse; margin: 0 auto;">
                            <thead>
                                <tr>
                                    <th style="border: 1px solid #ddd; padding: 12px; background-color: #e7e7e7; text-align: center; font-size: 1.1em; font-weight: bold;">
                                        Required Fields
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                ${missingFields.map(field => `
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px;">${field}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    <div style="color: #ff0000; margin-top: 1rem;">
                        All fields are mandatory
                    </div>`;

                Swal.fire({
                    title: 'Missing Required Fields',
                    html: tableHtml,
                    icon: 'warning',
                    confirmButtonText: 'Ok',
                    width: '600px'
                });
                return false;
            }
        } else {
            // For Save: At least one field must be filled
            if (!urValue && !ikValue && !irValue) {
                Swal.fire({
                    title: 'Cannot Save Empty Header',
                    html: `
                        <div style="margin: 20px;">
                            <p style="color: #666; font-size: 16px;">
                                Please enter at least one of the following:
                            </p>
                            <div style="text-align: left; margin-top: 15px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
                                <ul style="list-style-type: none; padding-left: 0;">
                                    <li style="margin: 10px 0; display: flex; align-items: center;">
                                        <i class="fa fa-chevron-right" style="margin-right: 10px; color: #1ab394;"></i>
                                        UR Value
                                    </li>
                                    <li style="margin: 10px 0; display: flex; align-items: center;">
                                        <i class="fa fa-chevron-right" style="margin-right: 10px; color: #1ab394;"></i>
                                        IK Value
                                    </li>
                                    <li style="margin: 10px 0; display: flex; align-items: center;">
                                        <i class="fa fa-chevron-right" style="margin-right: 10px; color: #1ab394;"></i>
                                        IR Value
                                    </li>
                                </ul>
                            </div>
                        </div>
                    `,
                    icon: 'warning',
                    confirmButtonText: 'Ok',
                    confirmButtonColor: '#1ab394',
                    width: '550px'
                });
                return false;
            }
        }
        
        return true;
    }

    // Add this new validation function for checklist items
    function validateChecklist() {
        // First validate the base checklist as before
        if (!validateBaseFields()) {
            return false;
        }

        // Validate the header fields
        const headerValidation = {
            'UR Value': $('#urValue').val().trim(),
            'IK Value': $('#ikValue').val().trim(),
            'IR Value': $('#irValue').val().trim()
        };

        // Add component-specific validations
        if (isComponentProduct) {
            if (!$('#panelTypeInput').val().trim()) {
                headerValidation['Panel Type'] = '';
            }
            if (!$('#subItemDropdown').val().trim()) {
                headerValidation['Sub Item (Mandatory)'] = '';
            }
        }

        const missingFields = Object.entries(headerValidation)
            .filter(([_, value]) => !value)
            .map(([field, _]) => field);

        if (missingFields.length > 0) {
            const tableHtml = `
                <div style="margin-top: 1rem; margin-bottom: 1rem;">
                    <table style="width: 100%; border-collapse: collapse; margin: 0 auto;">
                        <thead>
                            <tr>
                                <th style="border: 1px solid #ddd; padding: 12px; background-color: #e7e7e7; text-align: center; font-size: 1.1em; font-weight: bold;">
                                    Required Fields
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            ${missingFields.map(field => `
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 8px;">${field}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                <div style="color: #ff0000; margin-top: 1rem;">
                    Please fill in all required fields above
                </div>`;

            Swal.fire({
                title: 'Missing Required Fields',
                html: tableHtml,
                icon: 'warning',
                confirmButtonText: 'Ok',
                width: '800px'
            });
            return false;
        }

        let isValid = true;
        let unselectedItems = {};
        let validationErrors = {};
        let notOkWithoutRemarks = {}; // New object to track Not Ok items without remarks

        let currentHeader = '';

        // First get the main header from the first row
        const mainHeader = $('#main_checklist_form tr:first td:first b').text().trim();
        if (mainHeader) {
            currentHeader = mainHeader;
            unselectedItems[currentHeader] = [];
            validationErrors[currentHeader] = [];
            notOkWithoutRemarks[currentHeader] = []; // Initialize for first header
        }

        // Check all rows
        $('#main_checklist_form tr:gt(0)').each(function() {
            const $row = $(this);
            
            // Skip if this row is part of punch list table or has punch-list-row class
            if ($row.closest('.punch-list-wrapper').length > 0 || $row.hasClass('punch-list-row') || $row.find('.punch-input').length > 0) {
                return true;
            }
            
            // Skip if this is a serial number sub-row or empty row
            if ($row.find('td').length < 4 || $row.find('td:first').text().trim().startsWith('Serial no.') || $row.find('td:first').text().trim().startsWith('No.')) {
                return true;
            }

            // Check if this is a header row
            const $headerCell = $row.find('td:eq(0) b');
            if ($headerCell.length > 0) {
                currentHeader = $headerCell.text().trim();
                // Skip if this is a punch list header
                if (currentHeader.toLowerCase().includes('punch list')) {
                    return true;
                }
                if (!unselectedItems[currentHeader]) {
                    unselectedItems[currentHeader] = [];
                }
                if (!validationErrors[currentHeader]) {
                    validationErrors[currentHeader] = [];
                }
                if (!notOkWithoutRemarks[currentHeader]) {
                    notOkWithoutRemarks[currentHeader] = [];
                }
                return true;
            }

            const itemText = $row.find('td:eq(0)').clone()
                .children('table, button, .remove-row-btn, .row-number').remove().end()
                .text().trim();

            // Skip if row is empty or just contains serial number information
            if (!itemText || 
                /^\d+$/.test(itemText) || // Skip if only numbers
                itemText.toLowerCase().includes('punch list') ||
                itemText.toLowerCase().includes('description of non-conformance')) {
                return true;
            }

            // Check if any radio button is selected
            const selectedValue = $row.find('.passed-radio:checked').val();
            if (!selectedValue && currentHeader) {
                unselectedItems[currentHeader].push({
                    description: itemText
                });
            } else if (selectedValue === 'notok') {
                // Check if remark is provided for Not Ok items
                const remarkText = $row.find('.remark-textarea').val().trim();
                if (!remarkText) {
                    notOkWithoutRemarks[currentHeader].push({
                        description: itemText
                    });
                }
            } else if (selectedValue === 'ok' && currentHeader) {
                // Only validate addon values if "Ok" is selected
                
                // Check addon type 1 (free text)
                const $addonInput = $row.find('.addon-input');
                if ($addonInput.length > 0) {
                    if (!$addonInput.val().trim()) {
                        validationErrors[currentHeader].push({
                            description: itemText,
                            error: 'Free text field is required'
                        });
                    }
                }

                // Check addon type 2 (serial numbers)
                const $serialInputs = $row.find('.serial-input');
                if ($serialInputs.length > 0) {
                    const l1 = $row.find('.serial-input[data-type="L1"]').val().trim();
                    const l2 = $row.find('.serial-input[data-type="L2"]').val().trim();
                    const l3 = $row.find('.serial-input[data-type="L3"]').val().trim();
                    
                    if (!l1 || !l2 || !l3) {
                        validationErrors[currentHeader].push({
                            description: itemText,
                            error: 'All serial numbers are required'
                        });
                    }
                }
            }
        });

        // Show alert for unselected rows
        const hasUnselectedItems = Object.values(unselectedItems).some(items => items && items.length > 0);
        if (hasUnselectedItems) {
            const tableHtml = `
                <div style="margin-top: 1rem; margin-bottom: 1rem;">
                    <table style="width: 100%; border-collapse: collapse; margin: 0 auto;">
                        ${Object.entries(unselectedItems).map(([header, items]) => {
                            if (!items || items.length === 0) return '';
                            return `
                                <thead>
                                    <tr>
                                        <th style="border: 1px solid #ddd; padding: 12px; background-color: #f4f4f4; text-align: left; font-size: 1.1em; font-weight: bold; background-color: #e7e7e7;">
                                            ${header}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${items.map(item => `
                                        <tr>
                                            <td style="border: 1px solid #ddd; padding: 8px; text-align: left;">${item.description}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            `;
                        }).join('')}
                    </table>
                </div>
                <div style="color: #ff0000; margin-top: 1rem;">
                    Please select status (Ok/Not Ok/NA) for the above items
                </div>`;

            Swal.fire({
                title: 'Missing Selections',
                html: tableHtml,
                icon: 'warning',
                confirmButtonText: 'Ok',
                width: '800px'
            });
            return false;
        }

        // Show alert for items marked as Not Ok without remarks
        const hasNotOkWithoutRemarks = Object.values(notOkWithoutRemarks).some(items => items && items.length > 0);
        if (hasNotOkWithoutRemarks) {
            const tableHtml = `
                <div style="margin-top: 1rem; margin-bottom: 1rem;">
                    <table style="width: 100%; border-collapse: collapse; margin: 0 auto;">
                        ${Object.entries(notOkWithoutRemarks).map(([header, items]) => {
                            if (!items || items.length === 0) return '';
                            return `
                                <thead>
                                    <tr>
                                        <th style="border: 1px solid #ddd; padding: 12px; background-color: #f4f4f4; text-align: left; font-size: 1.1em; font-weight: bold; background-color: #e7e7e7;">
                                            ${header}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${items.map(item => `
                                        <tr>
                                            <td style="border: 1px solid #ddd; padding: 8px; text-align: left;">${item.description}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            `;
                        }).join('')}
                    </table>
                </div>
                <div style="color: #ff0000; margin-top: 1rem;">
                    Remarks are mandatory for items marked as "Not Ok"
                </div>`;

            Swal.fire({
                title: 'Missing Remarks for Not Ok Items',
                html: tableHtml,
                icon: 'warning',
                confirmButtonText: 'Ok',
                width: '800px'
            });
            return false;
        }

        // Show validation errors for incomplete Ok items
        const hasValidationErrors = Object.values(validationErrors).some(errors => errors && errors.length > 0);
        if (hasValidationErrors) {
            const tableHtml = `
                <div style="margin-top: 1rem; margin-bottom: 1rem;">
                    <table style="width: 100%; border-collapse: collapse; margin: 0 auto;">
                        ${Object.entries(validationErrors).map(([header, errors]) => {
                            if (!errors || errors.length === 0) return '';
                            return `
                                <thead>
                                    <tr>
                                        <th colspan="2" style="border: 1px solid #ddd; padding: 12px; background-color: #f4f4f4; text-align: left; font-size: 1.1em; font-weight: bold; background-color: #e7e7e7;">
                                            ${header}
                                        </th>
                                    </tr>
                                    <tr>
                                        <th style="border: 1px solid #ddd; padding: 8px; background-color: #f4f4f4; text-align: center;">Item</th>
                                        <th style="border: 1px solid #ddd; padding: 8px; background-color: #f4f4f4; text-align: center;">Required Field</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${errors.map(error => `
                                        <tr>
                                            <td style="border: 1px solid #ddd; padding: 8px;">${error.description}</td>
                                            <td style="border: 1px solid #ddd; padding: 8px;">${error.error}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            `;
                        }).join('')}
                    </table>
                </div>
                <div style="color: #ff0000; margin-top: 1rem;">
                    Please complete the required fields for the above items
                </div>`;

            Swal.fire({
                title: 'Required Fields Missing',
                html: tableHtml,
                icon: 'warning',
                confirmButtonText: 'Ok',
                width: '800px'
            });
            return false;
        }

        // Add punch list validation
        if (!validatePunchList()) {
            return false;
        }

        return true;
    }

    function validateSaveChecklist() {
        let hasAtLeastOneInput = false;

        // Check header fields for any input
        const headerFields = {
            urValue: $('#urValue').val() || '',
            ikValue: $('#ikValue').val() || '',
            irValue: $('#irValue').val() || ''
        };

        // For Component products, also check panel type and sub item
        if (isComponentProduct) {
            headerFields.panelTypeValue = $('#panelTypeInput').val() || '';
            headerFields.subItemValue = $('#subItemDropdown').val() || '';
        }

        // Check if any header field has a value
        if (Object.values(headerFields).some(value => value.trim() !== '')) {
            hasAtLeastOneInput = true;
        }

        // Only check further if we haven't found any input yet
        if (!hasAtLeastOneInput) {
            let currentHeader = '';
            
            // Check all rows in the checklist
            $('#main_checklist_form tr').each(function() {
                const $row = $(this);
                
                // Skip if this is a serial number sub-row or empty row
                if ($row.find('td').length < 4 || $row.find('td:first').text().trim().startsWith('Serial no.')) {
                    return true;
                }

                // Check if this is a header row
                const $headerCell = $row.find('td:eq(0) b');
                if ($headerCell.length > 0) {
                    currentHeader = $headerCell.text().trim();
                    return true;
                }

                // Check for any input in this row
                // 1. Check radio buttons
                if ($row.find('.passed-radio:checked').length > 0) {
                    hasAtLeastOneInput = true;
                    return false; // Exit the loop
                }

                // 2. Check addon inputs (free text)
                const addonValue = $row.find('.addon-input').val();
                if (addonValue && addonValue.trim() !== '') {
                    hasAtLeastOneInput = true;
                    return false;
                }

                // 3. Check serial numbers
                const $serialInputs = $row.find('.serial-input');
                if ($serialInputs.length > 0) {
                    $serialInputs.each(function() {
                        const serialValue = $(this).val();
                        if (serialValue && serialValue.trim() !== '') {
                            hasAtLeastOneInput = true;
                            return false; // Exit the .each loop
                        }
                    });
                    if (hasAtLeastOneInput) return false; // Exit the main loop if we found input
                }

                // 4. Check remarks
                const remarkValue = $row.find('.remark-textarea').val();
                if (remarkValue && remarkValue.trim() !== '') {
                    hasAtLeastOneInput = true;
                    return false;
                }
            });
        }

        if (!hasAtLeastOneInput) {
            const componentText = isComponentProduct ? ', Panel Type, Sub Item' : '';
            Swal.fire({
                title: 'Cannot Save Empty Checklist',
                html: `
                    <div style="margin: 20px;">
                        <p style="color: #666; font-size: 16px;">
                            Please enter at least one of the following before saving:
                        </p>
                        <div style="text-align: left; margin-top: 15px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
                            <ul style="list-style-type: none; padding-left: 0;">
                                <li style="margin: 10px 0; display: flex; align-items: center;">
                                    <i class="fa fa-chevron-right" style="margin-right: 10px; color: #1ab394;"></i>
                                    Header Information (UR, IK, IR${componentText})
                                </li>
                                <li style="margin: 10px 0; display: flex; align-items: center;">
                                    <i class="fa fa-chevron-right" style="margin-right: 10px; color: #1ab394;"></i>
                                    Item Status Selection (Ok/Not Ok/NA)
                                </li>
                                <li style="margin: 10px 0; display: flex; align-items: center;">
                                    <i class="fa fa-chevron-right" style="margin-right: 10px; color: #1ab394;"></i>
                                    Serial Numbers
                                </li>
                                <li style="margin: 10px 0; display: flex; align-items: center;">
                                    <i class="fa fa-chevron-right" style="margin-right: 10px; color: #1ab394;"></i>
                                    Additional Text Inputs
                                </li>
                                <li style="margin: 10px 0; display: flex; align-items: center;">
                                    <i class="fa fa-chevron-right" style="margin-right: 10px; color: #1ab394;"></i>
                                    Remarks
                                </li>
                            </ul>
                        </div>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'Ok',
                confirmButtonColor: '#1ab394',
                width: '550px'
            });
            return false;
        }

        return true;
    }

    // Add this function to validate punch list data
    function validatePunchList() {
        let isValid = true;
        let invalidRows = [];

        // Check each punch list table
        $('.punch-list-wrapper').each(function() {
            const reference = $(this).data('reference');
            const safeId = createSafeId(reference);
            
            // Check each row in this punch list
            $(`#${safeId} .punch-list-body .punch-list-row`).each(function(index) {
                const $row = $(this);
                const rowNum = index + 1;
                
                // Get the description value
                const description = $row.find('.description-input').val().trim();
                
                // If description is filled, check other required fields
                if (description) {
                    const resolutionRemark = $row.find('.resolution-remark').val().trim();
                    const recheckingRemark = $row.find('.rechecking-remark').val().trim();
                    const workHrs = $row.find('.work-hrs-input').val().trim();
                    const code = $row.find('.code-input').val().trim();

                    const missingFields = [];

                    if (!resolutionRemark) missingFields.push('Resolution Remark');
                    if (!recheckingRemark) missingFields.push('Rechecking Remark');
                    if (!workHrs) missingFields.push('Work Hrs.');
                    if (!code) missingFields.push('Code');

                    if (missingFields.length > 0) {
                        invalidRows.push({
                            reference: reference,
                            rowNumber: rowNum,
                            description: description,
                            missingFields: missingFields
                        });
                        isValid = false;
                    }
                }
            });
        });

        if (!isValid) {
        // Create a formatted error message
            const errorHtml = `
                <div style="margin-top: 1rem; margin-bottom: 1rem;">
                    <table style="width: 100%; border-collapse: collapse; margin: 0 auto;">
                        <thead>
                            <tr>
                                <th style="border: 1px solid #ddd; padding: 12px; background-color: #e7e7e7; text-align: center;">Reference</th>
                                <th style="border: 1px solid #ddd; padding: 12px; background-color: #e7e7e7; text-align: center;">Row</th>
                                <th style="border: 1px solid #ddd; padding: 12px; background-color: #e7e7e7; text-align: center;">Description</th>
                                <th style="border: 1px solid #ddd; padding: 12px; background-color: #e7e7e7; text-align: center;">Missing Fields</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${invalidRows.map(row => `
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 8px;">${row.reference}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">${row.rowNumber}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px;">${row.description}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px;">${row.missingFields.join(', ')}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                <div style="color: #ff0000; margin-top: 1rem;">
                    Please fill in all required fields for the rows with descriptions.
                </div>`;

            Swal.fire({
                title: 'Incomplete Punch List Entries',
                html: errorHtml,
                icon: 'warning',
                confirmButtonText: 'Ok',
                width: '800px'
            });
        }

        return isValid;
    }

    // REPLACE THE showLoaderAndSave FUNCTION with this:
    let isSaving = false;
    function showLoaderAndSave(action) {
        if (isSaving) return;
        isSaving = true;
        
        // Show loader
        $('#fullPageLoader').fadeIn(300);
        $('#loaderMessage').text(action === 'submit' ? 'Submitting checklist...' : 'Saving checklist...');
        
        // Disable all interactions
        $('body').css('pointer-events', 'none');
        
        // Call save function
        saveEntireChecklist(action);
    }

    // REPLACE THE ENTIRE saveEntireChecklist FUNCTION with this:
    async function saveEntireChecklist(action) {
        try {
            const currentStationId = $('#stationSearch').dropdown('get value');

            // Collect the compliance data
            const complianceData = {
                order_no: $('#projectSearchInput').val(),
                item_no: ($('#panelSearch').dropdown('get value') || '').match(/^[^|]*/)[0],
                checklist_id: $('#checklistSearch').dropdown('get value'),
                line_id: $('#lineSearch').dropdown('get value'),
                product_id: $('#productSearch').dropdown('get value'),
                station_id: currentStationId,
                sub_item: isComponentProduct ? $('#subItemDropdown').val() : '',
                is_component_product: isComponentProduct ? 1 : 0,
                reference: {}
            };

            // Collect punch list data for each reference
            $('.punch-list-wrapper').each(function() {
                const reference = $(this).data('reference');
                if (!reference) return;

                const safeId = createSafeId(reference);
                const rows = $(`#${safeId} .punch-list-body .punch-list-row`);
                
                if (!complianceData.reference[reference]) {
                    complianceData.reference[reference] = [];
                }
                
                rows.each(function() {
                    const $row = $(this);
                    const description = $row.find('.description-input').val().trim();
                    
                    if (description) {
                        const itemId = $row.attr('data-item-id');
                        const checklistDataId = $row.attr('data-checklist-data-id');
                        
                        console.log('Collecting punch list row:', {
                            itemId,
                            checklistDataId,
                            description,
                            reference
                        });
                        
                        const workHrsValue = $row.find('.work-hrs-input').val().trim();
                        const rowData = {
                            db_id: $row.attr('data-db-id'),
                            description: description,
                            checklist_data_id: checklistDataId || null,
                            checklist_item_id: itemId || null,
                            finding: {
                                by: $row.find('.finding-by').val().trim(),
                                date: $row.find('.finding-date').val().trim()
                            },
                            resolution: {
                                by: $row.find('.resolution-by').val().trim(),
                                date: $row.find('.resolution-date').val().trim(),
                                remark: $row.find('.resolution-remark').val().trim()
                            },
                            rechecking: {
                                by: $row.find('.rechecking-by').val().trim(),
                                date: $row.find('.rechecking-date').val().trim(),
                                remark: $row.find('.rechecking-remark').val().trim()
                            },
                            work_hrs: workHrsValue || null,
                            code: $row.find('.code-input').val().trim()
                        };
                        
                        complianceData.reference[reference].push(rowData);
                    }
                });
                
                if (complianceData.reference[reference].length === 0) {
                    delete complianceData.reference[reference];
                }
            });

            // Clean up empty references
            Object.keys(complianceData.reference).forEach(key => {
                if (!complianceData.reference[key] || complianceData.reference[key].length === 0) {
                    delete complianceData.reference[key];
                }
            });

            const formData = {
                action: action,
                items: [],
                headerInfo: {
                    ur_value: $('#urValue').val().trim(),
                    ik_value: $('#ikValue').val().trim(),
                    ir_value: $('#irValue').val().trim(),
                    checklist_name: $('td:contains("Checklist Name:")').next().text().trim(),
                    project_name: $('td:contains("Project Name:")').next().text().trim(),
                    order_processor: $('td:contains("Order Processor:")').next().text().trim(),
                    panel_type: isComponentProduct ? $('#panelTypeInput').val().trim() : $('#panelTypeCell').text().trim(),
                    production_order_no: $('#productionOrderInput').val().trim(),
                    orderno: $('#orderNoDisplay').text().trim(),
                    location_name: isComponentProduct ? 
                        ($('#locationValue').val().trim() || '') : 
                        ($('#locationTypicalDisplay').text().split(' / ')[0] || ''),
                    typical_name: isComponentProduct ? 
                        ($('#typicalValue').val().trim() || '') : 
                        ($('#locationTypicalDisplay').text().split(' / ')[1] || ''),
                    sub_item: isComponentProduct ? $('#subItemDropdown').val().trim() : '',
                    is_component_product: isComponentProduct ? 1 : 0
                },
                complianceData: complianceData
            };

            let currentHeader = '';
            $('#main_checklist_form tr').each(function() {
                const $row = $(this);
                
                if ($row.closest('.punch-list-wrapper').length > 0) {
                    return true;
                }

                if ($row.find('td').length < 4 || 
                    $row.find('td:first').text().trim().startsWith('Serial no.') ||
                    $row.hasClass('punch-list-row')) {
                    return true;
                }

                const $headerCell = $row.find('td:eq(0) b');
                if ($headerCell.length > 0) {
                    currentHeader = $headerCell.text().trim();
                    return true;
                }

                const $itemCell = $row.find('td:eq(0)');
                let mainText = $itemCell.clone()
                    .children('table, button, .remove-row-btn, .row-number').remove().end()
                    .text()
                    .trim();

                if (!mainText || 
                    /^\d+$/.test(mainText) ||
                    mainText.includes('Remove Row') ||
                    mainText.includes('Punch list') ||
                    mainText.toLowerCase().includes('description of non-conformance')) {
                    return true;
                }

                if (mainText) {
                    const itemId = $row.find('[data-item-id]').first().data('item-id');
                    
                    if (!itemId) {
                        return true;
                    }

                    const rowData = {
                        order_no: $('#projectSearchInput').val(),
                        panel_no: ($('#panelSearch').dropdown('get value') || '').match(/^[^|]*/)[0],
                        checklist_id: $row.data('checklist-id') || $('#checklistSearch').dropdown('get value'),
                        line_id: $row.data('line-id') || $('#lineSearch').dropdown('get value'),
                        product_id: $row.data('product-id') || $('#productSearch').dropdown('get value'),
                        station_id: $row.data('station-id') || $('#stationSearch').dropdown('get value'),
                        item_id: itemId,
                        checklist_data_id: $row.data('checklist-data-id'),
                        item_name: mainText,
                        reference: currentHeader,
                        passed: $row.find('.passed-radio:checked').val() || '',
                        remark: $row.find('.remark-textarea').val() || '',
                        addon_type: '0',
                        addon_value: '',
                        serial_l1: '',
                        serial_l2: '',
                        serial_l3: ''
                    };

                    if ($row.find('.addon-input').length) {
                        rowData.addon_type = '1';
                        rowData.addon_value = $row.find('.addon-input').val() || '';
                    } else if ($row.find('.serial-input').length) {
                        rowData.addon_type = '2';
                        $row.find('.serial-input').each(function() {
                            const type = $(this).data('type');
                            if (type === 'L1') rowData.serial_l1 = $(this).val() || '';
                            if (type === 'L2') rowData.serial_l2 = $(this).val() || '';
                            if (type === 'L3') rowData.serial_l3 = $(this).val() || '';
                        });
                    }

                    formData.items.push(rowData);
                }
            });

            console.log('Final form data:', formData);

            // Make AJAX request
            const response = await $.ajax({
                url: '/dpm/dwc/api/checklistAPI.php?type=save_entire_checklist',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                timeout: 300000
            });

            // After successful save, reload the checklist with the saved sub_item
            const checklistId = $('#checklistSearch').dropdown('get value');
            const lineId = $('#lineSearch').dropdown('get value');
            const productId = $('#productSearch').dropdown('get value');
            const stationId = $('#stationSearch').dropdown('get value');
            const salesOrderNo = $('#projectSearchInput').val();
            const panelNumber = ($('#panelSearch').dropdown('get value') || '').match(/^[^|]*/)[0];
            const savedSubItem = isComponentProduct ? $('#subItemDropdown').val() : '';

            // Reload the checklist form with the saved sub_item and action
            listChecklistForm(checklistId, lineId, productId, stationId, salesOrderNo, panelNumber, savedSubItem, action);

        } catch (error) {
            $('#fullPageLoader').fadeOut(300);
            $('body').css('pointer-events', 'auto');
            
            console.error('Error:', error);
            toastr.error('Failed to save checklist. Please try again.');
            
            isSaving = false;
        }
    }

    // Add this JavaScript function to handle the radio button changes
    function handleNotApplicableChange(checkbox) {
        const itemId = checkbox.dataset.itemId;
        const isNotApplicable = checkbox.checked;
        const passedRadios = document.querySelectorAll(`input[name="passed_${itemId}"]`);
        const passedLabels = document.querySelectorAll(`input[name="passed_${itemId}"]`).forEach(radio => {
            const label = radio.closest('label');
            if (isNotApplicable) {
                label.style.opacity = '0.5';
                radio.disabled = true;
                radio.checked = false;
            } else {
                label.style.opacity = '1';
                radio.disabled = false;
                if (radio.value === 'ok') {
                    radio.checked = true;
                }
            }
        });

        // Update the data object
        const data = {
            item_id: itemId,
            not_applicable: isNotApplicable,
            passed: isNotApplicable ? null : 'ok' // Default to ok when not applicable is unchecked
        };
    }

    // Add this function to handle passed radio changes
    function handlePassedChange(radio) {
        const itemId = radio.dataset.itemId;
        const value = radio.value;
        
        // Update the data object
        const data = {
            item_id: itemId,
            passed: value
        };
        
        // Optional: Update visual feedback
        const label = radio.closest('label');
        const allLabels = radio.closest('.passed-group').querySelectorAll('label');
        
        allLabels.forEach(l => {
            l.style.opacity = '1';
        });

        if (value === 'na') {
            label.style.opacity = '0.8';
        }
    }

    function saveChecklistData(data) {
        $.ajax({
            url: '/dpm/dwc/api/checklistAPI.php?type=save_checklist_item',
            method: 'POST',
            data: data,
            success: function(response) {
                toastr.success('Data saved successfully');
            },
            error: function(xhr, status, error) {
                toastr.error('Failed to save data');
                console.error('Save failed:', error);
            }
        });
    }

    $(document).ready(function () {
        $('<style>')
            .text(`
                .punch-input:disabled {
                    opacity: 0.7 !important;
                    background-color: #f5f5f5 !important;
                    cursor: not-allowed !important;
                    border: 1px solid #ddd !important;
                    color: #000000 !important;
                    -webkit-text-fill-color: #000000 !important;
                    white-space: pre-wrap !important;
                    word-wrap: break-word !important;
                    overflow: hidden !important;
                }
                
                .description-input:disabled {
                    opacity: 0.7 !important;
                    background-color: #f5f5f5 !important;
                    cursor: not-allowed !important;
                    border: 1px solid #ddd !important;
                    color: #000000 !important;
                    -webkit-text-fill-color: #000000 !important;
                    white-space: pre-wrap !important;
                    word-wrap: break-word !important;
                    overflow: hidden !important;
                    resize: none !important;
                }
                    
                .punch-input.by-input:disabled,
                .punch-input.date-input:disabled {
                    opacity: 0.7 !important;
                    background-color: #f5f5f5 !important;
                    cursor: not-allowed !important;
                    border: 1px solid #ddd !important;
                }
            `)
            .appendTo('head');

        // Remove any br tags that might be dynamically added
        $('#page-wrapper > br').remove();
        
        // Adjust content positioning
        function adjustLayout() {
            const headerHeight = $('header').outerHeight(true) || 0;
            $('#page-wrapper').css('padding-top', headerHeight + 'px');
        }
        
        adjustLayout();
        $(window).on('resize', adjustLayout);
        // Set initial states
        $('.not-applicable-checkbox:checked').each(function() {
            handleNotApplicableChange(this);
        });

        // Add input validation and formatting
        $('.underline-input').on('input', function(e) {
            // Allow only numbers and decimal point
            let value = this.value.replace(/[^\d.]/g, '');
            
            // Allow only one decimal point
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            
            // Limit decimal places to 2
            if (parts.length > 1) {
                value = parts[0] + '.' + parts[1].slice(0, 2);
            }
            
            this.value = value;
        });

        // Optional: Auto-select content on focus
        $('.underline-input').on('focus', function() {
            this.select();
        });
        
        // Set default "Ok" for new items
        $('.passed-radio[value="ok"]').each(function() {
            if (!$(this).closest('.passed-group').find('input:checked').length) {
                $(this).prop('checked', true);
            }
        });

        // Set default values for radio buttons if none selected
        $('.passed-group').each(function() {
            const group = $(this);
            if (!group.find('input[type="radio"]:checked').length) {
                group.find('input[value="ok"]').prop('checked', true);
            }
        });

        // Initialize existing NA selections
        $('.passed-radio[value="na"]:checked').each(function() {
            const label = $(this).closest('label');
            label.css('opacity', '0.8');
        });

        // Call updateTableHeaders after the table is created
        if ($('#main_checklist_form tr').length > 1) { // Check if there are actual checklist items
            updateTableHeaders();
            initializePunchList();
        } else {
            $('.punch-list-wrapper').hide();
            $('.punch-list-wrapper-add-row').hide();
        }

        // Initialize remark handlers
        initializeRemarkHandlers();

        // Add this handler for remarks
        $(document).on('input', '.remark-textarea', function() {
            const $textarea = $(this);
            const $row = $textarea.closest('tr');
            const $radio = $row.find('.passed-radio:checked');
            
            if ($radio.val() === 'notok') {
                const reference = $row.closest('table').find('tr:first td:first b').text().trim();
                const itemId = $row.find('.passed-radio').first().data('item-id');
                
                // Set up the remark handler if not already set
                if (!$textarea.data('handler-initialized')) {
                    setupRemarkHandler($textarea, reference, itemId);
                    $textarea.data('handler-initialized', true);
                }
            }
        });

        $(document).on('input', '.description-input', function() {
            if (this.value) {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            }
        });
        
        // Function to update displayed values
        function updateDisplayedValues() {
            const orderNo = $('#projectSearchInput').val() || '';
            const dropdownValue = $('#panelSearch').dropdown('get value') || '';
            const panelType = dropdownValue.split('|')[0] || '';
            const panelName = dropdownValue.split('|')[1] || '';
            const typicalName = dropdownValue.split('|')[2] || '';
            
            $('#orderNoDisplay').text(orderNo);
            
            if (!isComponentProduct) {
                // Display location and typical with "/" separator (non-editable)
                const locationTypicalDisplay = `${panelName} / ${typicalName}`;
                $('#locationTypicalDisplay').text(locationTypicalDisplay);
                
                $('#panelTypeDisplay').text(panelType);
            } else {
                // Component Product - populate the editable fields
                $('#locationValue').val(panelName || '');
                $('#typicalValue').val(typicalName || '');
                
                // Display panel type as non-editable text (left side of the box)
                $('#panelTypeDisplay').text(panelType);
            }
        }

        // Update values on initial load
        updateDisplayedValues();
        
        // Setup AJAX beforeSend and complete handlers
        $.ajaxSetup({
            beforeSend: function (xhr, settings) {
                // Only show loader for specific API calls
                if (settings.url.includes('/dpm/dwc/materialsearch/api/') || 
                    (settings.url.includes('/dpm/dwc/api/checklistAPI.php') && 
                    !settings.url.includes('type=save_entire_checklist'))) {
                    
                    showGlobalLoader('Loading checklist...');
                }
            },
            complete: function (xhr, status) {
                // Only hide loader for non-save endpoints
                if (!this.url.includes('type=save_entire_checklist')) {
                    setTimeout(hideGlobalLoader, 200);
                }
            },
            error: function(xhr, status, error) {
                // Only handle errors for non-save endpoints
                if (!this.url.includes('type=save_entire_checklist')) {
                    console.error('AJAX error:', error);
                    setTimeout(forceHideAllLoaders, 500);
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
                        const urlFile = "checklist_form";
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
                    if (result && result.project_no) {
                        let data = {};
                        // Check the valueToSave parameter and set the appropriate data
                        data = {
                            project: result.project_no,
                            clear: 1
                            // Leave panel and station empty
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
                        return false;
                    }
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
            saveSelectedValuesToServerChecklist("panel");
        }
        const checklistId = $('#checklistSearch').dropdown('get value');
        if (checklistId) {
            saveSelectedValuesToServerChecklist("checklist");
        }
        const lineId = $('#lineSearch').dropdown('get value');
        if (lineId) {
            saveSelectedValuesToServerChecklist("line");
        }
        const productId = $('#productSearch').dropdown('get value');
        if (productId) {
            saveSelectedValuesToServerChecklist("product");
        }
        const stationId = $('#stationSearch').dropdown('get value');
        if (stationId) {
            saveSelectedValuesToServerChecklist("station");
        }

        var salesOrderNo = $('#projectSearchInput').val();
        if (salesOrderNo.trim().length === 10 && !isNaN(salesOrderNo)) {
            if (checklistId != "" && lineId != "" && productId != "") {
                // listChecklistForm(checklistId, lineId, productId, stationId, panelNumber);
            }
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

            // Update when project search input changes
            $('#projectSearchInput').on('change', updateDisplayedValues);

            // Panel dropdown change handler
            $('#panelSearch').dropdown('setting', 'onChange', function(){
                updateDisplayedValues();
                const selectedSalesOrderNo = $('#projectSearchInput').val();
                const selectedPanelNumber = ($('#panelSearch').dropdown('get value') || '').match(/^[^|]*/)[0];
                const selectedchecklistId = $('#checklistSearch').dropdown('get value');
                const selectedlineId = $('#lineSearch').dropdown('get value');
                const selectedproductId = $('#productSearch').dropdown('get value');
                const selectedstationId = $('#stationSearch').dropdown('get value');                
                saveSelectedValuesToServerChecklist("panel");
                if(!selectedPanelNumber) {
                    Swal.fire({
                        html: 'Please select panel number',
                        icon: 'error',
                        confirmButtonText: 'Ok',
                        width: '300px'
                    });
                    listChecklistForm(selectedchecklistId, selectedlineId, selectedproductId, selectedstationId, selectedSalesOrderNo, "");
                } else {
                    listChecklistForm(selectedchecklistId, selectedlineId, selectedproductId, selectedstationId, selectedSalesOrderNo, selectedPanelNumber);
                }
            });

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

        // Hide loader on initial page load
        forceHideAllLoaders();

        // Add this to handle page unload/reload
        $(window).on('beforeunload', function() {
            showGlobalLoader('Loading...');
        });
        
        // Add this to ensure the loader is hidden if AJAX requests fail
        $(document).ajaxError(function() {
            setTimeout(forceHideAllLoaders, 500);
        });

        // REPLACE THE EXISTING subItemDropdown HANDLER with this:
        // ===== NEW: Handle subItemDropdown change for Component products =====
        $(document).on('change', '#subItemDropdown', function() {
            const selectedSubItem = $(this).val();
            const selectedSalesOrderNo = $('#projectSearchInput').val();
            const selectedPanelNumber = ($('#panelSearch').dropdown('get value') || '').match(/^[^|]*/)[0];
            const selectedchecklistId = $('#checklistSearch').dropdown('get value');
            const selectedlineId = $('#lineSearch').dropdown('get value');
            const selectedproductId = $('#productSearch').dropdown('get value');
            const selectedstationId = $('#stationSearch').dropdown('get value');
            
            // Update the global currentSubItem variable
            currentSubItem = selectedSubItem;
            
            if (selectedSubItem && selectedSalesOrderNo && selectedchecklistId && selectedlineId && selectedproductId) {
                // Call listChecklistForm with sub_item parameter
                listChecklistForm(selectedchecklistId, selectedlineId, selectedproductId, selectedstationId, selectedSalesOrderNo, selectedPanelNumber, selectedSubItem);
            }
        });
    });

    function updateTableHeaders(reference) {
        const safeId = createSafeId(reference);
        const headerRows = `
            <tr style="background-color: #f2f2f2; text-align: center; font-size: 12px;">
                <td style="border: 1px solid black; padding: 5px; width: 3%;">No.</td>
                <td style="border: 1px solid black; padding: 5px; width: 25%;">Description of Non-Conformance</td>
                <td colspan="2" style="border: 1px solid black; padding: 5px; width: 12%;">Finding</td>
                <td colspan="3" style="border: 1px solid black; padding: 5px; width: 24%;">Resolution</td>
                <td colspan="3" style="border: 1px solid black; padding: 5px; width: 24%;">Rechecking</td>
                <td style="border: 1px solid black; padding: 5px; width: 4%;">Work Hrs.</td>
                <td style="border: 1px solid black; padding: 5px; width: 4%;">Code</td>
            </tr>
            <tr style="text-align: center; background-color: #f2f2f2; font-size: 12px;">
                <td style="border: 1px solid black;"></td>
                <td style="border: 1px solid black;"></td>
                <td style="border: 1px solid black; padding: 5px; width: 6%;">By</td>
                <td style="border: 1px solid black; padding: 5px; width: 6%;">Date</td>
                <td style="border: 1px solid black; padding: 5px; width: 8%;">By</td>
                <td style="border: 1px solid black; padding: 5px; width: 8%;">Date</td>
                <td style="border: 1px solid black; padding: 5px; width: 8%;">Remark</td>
                <td style="border: 1px solid black; padding: 5px; width: 8%;">By</td>
                <td style="border: 1px solid black; padding: 5px; width: 8%;">Date</td>
                <td style="border: 1px solid black; padding: 5px; width: 8%;">Remark</td>
                <td style="border: 1px solid black;"></td>
                <td style="border: 1px solid black;"></td>
            </tr>`;

        $(`#${safeId} .punch-list-header`).html(headerRows);
    }

    function createPunchListRow(reference, rowNumber = null, dbId = null, isNewRow = true) {
        const currentDate = new Date();
        const number = rowNumber || getNextRowNumber(reference);
        const formattedDate = formatDate(currentDate);
        const wrappedUser = currentUser.replace(/(.{20})/g, '$1\n');

        // Set initial values
        const initialBy = wrappedUser;
        const initialDate = formattedDate;

        return `
            <tr class="punch-list-row" data-reference="${reference}" data-db-id="${dbId || ''}" data-item-id="" data-checklist-data-id="" style="font-size: 12px;">
                <td style="text-align: center; width: 3%;">
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 5px;">
                        <span class="row-number">${number}</span>
                        <input type="hidden" class="punch-input number-input" value="${number}">
                        <button type="button" class="btn btn-danger btn-sm remove-row-btn" style="display: none; font-size: 11px; padding: 2px 5px;">
                            <i class="fa fa-minus-circle"></i>
                        </button>
                    </div>
                </td>
                <td style="width: 25%;">
                    <textarea class="punch-input description-input" rows="2" style="width: 100%; resize: none; min-height: 40px; overflow: hidden; word-wrap: break-word; white-space: pre-wrap;"></textarea>
                </td>
                <!-- Finding section (without Remark) -->
                <td style="width: 6%;">
                    <textarea class="punch-input by-input finding-by" rows="2" style="width: 100%; resize: none;" disabled>${initialBy}</textarea>
                </td>
                <td style="width: 6%;">
                    <textarea class="punch-input date-input finding-date" rows="2" style="width: 100%; resize: none;" disabled>${initialDate}</textarea>
                </td>
                <!-- Resolution section -->
                <td style="width: 8%;">
                    <textarea class="punch-input by-input resolution-by" rows="2" style="width: 100%; resize: none;" disabled>${initialBy}</textarea>
                </td>
                <td style="width: 8%;">
                    <textarea class="punch-input date-input resolution-date" rows="2" style="width: 100%; resize: none;" disabled>${initialDate}</textarea>
                </td>
                <td style="width: 8%;">
                    <textarea class="punch-input remark-input resolution-remark" rows="2" style="width: 100%; resize: none;"></textarea>
                </td>
                <!-- Rechecking section -->
                <td style="width: 8%;">
                    <textarea class="punch-input by-input rechecking-by" rows="2" style="width: 100%; resize: none;" disabled>${initialBy}</textarea>
                </td>
                <td style="width: 8%;">
                    <textarea class="punch-input date-input rechecking-date" rows="2" style="width: 100%; resize: none;" disabled>${initialDate}</textarea>
                </td>
                <td style="width: 8%;">
                    <textarea class="punch-input remark-input rechecking-remark" rows="2" style="width: 100%; resize: none;"></textarea>
                </td>
                <td style="width: 4%;">
                    <input type="text" class="punch-input work-hrs-input" style="width: 100%;">
                </td>
                <td style="width: 4%;">
                    <input type="text" class="punch-input code-input" style="width: 100%;">
                </td>
            </tr>
        `;
    }

    function populatePunchListData(reference, data, status) {
        const safeId = createSafeId(reference);
        const tbody = $(`#${safeId} .punch-list-body`);
        
        // Clear any existing rows
        tbody.empty();

        if (data && data.length > 0) {
            let rowNumber = 1;
            data.forEach((item) => {
                const row = $(createPunchListRow(reference, rowNumber, item.id, false));
                
                if (item.checklist_item_id) {
                    row.attr('data-item-id', item.checklist_item_id);
                }
                if (item.checklist_data_id) {
                    row.attr('data-checklist-data-id', item.checklist_data_id);
                }

                const setupFieldPermissions = (section, sectionData) => {
                    const byField = row.find(`.${section}-by`);
                    const dateField = row.find(`.${section}-date`);
                    const remarkField = section !== 'finding' ? row.find(`.${section}-remark`) : null;
                    
                    // Set values
                    if (sectionData.by) {
                        byField.val(sectionData.by);
                    }
                    if (sectionData.date) {
                        dateField.val(sectionData.date);
                    }
                    if (remarkField && sectionData.remark) {
                        remarkField.val(sectionData.remark);
                    }

                    // Always disable by and date fields
                    byField.prop('disabled', true).css({
                        'opacity': '0.7',
                        'background-color': '#f5f5f5',
                        'cursor': 'not-allowed',
                        'border': '1px solid #ddd'
                    });
                    
                    dateField.prop('disabled', true).css({
                        'opacity': '0.7',
                        'background-color': '#f5f5f5',
                        'cursor': 'not-allowed',
                        'border': '1px solid #ddd'
                    });

                    // Only disable remark fields if status is "1"
                    if (status === "1" && remarkField) {
                        remarkField.prop('disabled', true).css({
                            'opacity': '0.7',
                            'background-color': '#f5f5f5',
                            'cursor': 'not-allowed',
                            'border': '1px solid #ddd'
                        });
                    }
                };

                // Populate description and handle its permissions
                const $description = row.find('.description-input');
                $description.val(item.description || '');
                
                // Always disable description field regardless of status
                $description.prop('disabled', true).css({
                    'opacity': '0.7',
                    'background-color': '#f5f5f5',
                    'cursor': 'not-allowed',
                    'border': '1px solid #ddd'
                });

                if (status === "1") {
                    row.find('.work-hrs-input, .code-input').prop('disabled', true).css({
                        'opacity': '0.7',
                        'background-color': '#f5f5f5',
                        'cursor': 'not-allowed',
                        'border': '1px solid #ddd'
                    });
                }

                // Setup each section
                setupFieldPermissions('finding', item.finding);
                setupFieldPermissions('resolution', item.resolution);
                setupFieldPermissions('rechecking', item.rechecking);

                // Set work hours and code
                row.find('.work-hrs-input').val(item.work_hrs || '');
                row.find('.code-input').val(item.code || '');

                tbody.append(row);
                rowNumber++;
            });
        }

        updateRowNumbers(reference);
        updateRemoveButtonVisibility(reference);
        initializeTextareaAutoResize();

        if (status === "1") {
            $(`#${safeId}`).closest('.punch-list-wrapper').find('.punch-list-buttons').hide();
        }

        // Initialize textarea auto-resize
        tbody.find('textarea').each(function() {
            const textarea = this;
            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight) + 'px';
            
            $(textarea).on('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        });

        // Add validation for work hours
        tbody.find('.work-hrs-input').on('input', function() {
            let value = this.value.replace(/[^\d.]/g, '');
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            if (parts.length > 1) {
                value = parts[0] + '.' + parts[1].slice(0, 2);
            }
            this.value = value;
        });

        // Add validation for code
        tbody.find('.code-input').on('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 2) {
                value = value.slice(0, 2);
            }
            this.value = value;
        });
    }

    function initializePunchList(reference) {
        if (!reference) {
            console.error('No reference provided for punch list initialization');
            return;
        }
        const safeId = createSafeId(reference);
        const tbody = $(`#${safeId} .punch-list-body`);
        
        // Check if tbody is empty
        if (tbody.children().length === 0) {
            // Don't add a default row - let data populate first
            // Users can add rows manually using the "Add Row" button
        }
        
        // Show both Add and Remove buttons
        $(`.add-punch-list-row[data-reference="${reference}"]`).show();
        $(`.remove-punch-list-row[data-reference="${reference}"]`).show();
        
        initializeTextareaAutoResize();
    }

    function createSafeId(reference) {
        // Returns a safe DOM id even for references with unicode/special chars
        if (!reference) return 'punch_list_default';
        let normalized = reference.normalize('NFKD')
            .replace(/[\u201C\u201D\u201E\u201F\u2033\u2036\u0022]/g,'"') // Replace unicode quotes
            .replace(/["'""''‹›«»]/g, '') // Remove all types of quotes
            .replace(/&[a-z]+;/gi, '') // Remove HTML entities
            .trim();
        return 'punch_list_' + normalized.replace(/[^a-zA-Z0-9_]+/g, '_').toLowerCase();
    }

    // Add a single handler
    $(document).on('click', '.add-punch-list-row', function(e) {
        // Check if parent wrapper has status="1"
        if ($(this).closest('.punch-list-wrapper').find('.punch-list-buttons').is(':hidden')) {
            e.preventDefault();
            return false;
        }
        const reference = $(this).data('reference');
        if (!reference) {
            console.error('No reference found for add row button');
            return;
        }
        const safeId = createSafeId(reference);
        const newRow = createPunchListRow(reference, null, null, true);
        $(`#${safeId} .punch-list-body`).append(newRow);
        
        // Initialize the new row with current user and date
        const $newRow = $(`#${safeId} .punch-list-body`).children().last();
        const wrappedUser = currentUser.replace(/(.{20})/g, '$1\n');
        const formattedDate = formatDate(new Date());
        
        $newRow.find('.finding-by').val(wrappedUser);
        $newRow.find('.finding-date').val(formattedDate);
        
        updateRowNumbers(reference);
        updateRemoveButtonVisibility(reference);
        initializeTextareaAutoResize();
    });

    // Handle Remove Row button click
    $(document).on('click', '.remove-punch-list-row', function(e) {
        // Check if parent wrapper has status="1"
        if ($(this).closest('.punch-list-wrapper').find('.punch-list-buttons').is(':hidden')) {
            e.preventDefault();
            return false;
        }
        const reference = $(this).data('reference');
        const safeId = createSafeId(reference);
        const tbody = $(`#${safeId} .punch-list-body`);
        const rows = tbody.find('.punch-list-row');
        const lastRow = rows.last();
        const description = lastRow.find('.description-input').val().trim();
        const dbId = lastRow.data('db-id');

        Swal.fire({
            title: 'Remove Row',
            text: 'Are you sure you want to remove the last row?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, remove it',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // If the row has a database ID and description, try to delete from database
                if (dbId && description) {
                    $.ajax({
                        url: '/dpm/dwc/api/checklistAPI.php?type=delete_punch_list_row',
                        method: 'POST',
                        data: {
                            id: dbId,
                            order_no: $('#projectSearchInput').val(),
                            item_no: ($('#panelSearch').dropdown('get value') || '').match(/^[^|]*/)[0],
                            reference: reference,
                            description: description
                        },
                        success: function(response) {
                            // Remove the row from the UI
                            lastRow.fadeOut(300, function() {
                                $(this).remove();
                                updateRowNumbers(reference);
                                updateRemoveButtonVisibility(reference);
                            });
                            toastr.success('Row removed successfully');
                        },
                        error: function(xhr, status, error) {
                            toastr.error('Failed to remove row');
                            console.error('Delete failed:', error);
                        }
                    });
                } else {
                    // If no database ID or no description, just remove from UI
                    lastRow.fadeOut(300, function() {
                        $(this).remove();
                        updateRowNumbers(reference);
                        updateRemoveButtonVisibility(reference);
                    });
                    
                    // Show different messages based on why it was only removed from UI
                    if (dbId && !description) {
                        toastr.info('Row removed from view (no description to delete from database)');
                    } else {
                        toastr.success('Row removed successfully');
                    }
                }
            }
        });
    });

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

    function updateRowNumbers(reference) {
        if (!reference) {
            console.error('No reference provided for updating row numbers');
            return;
        }
        const safeId = createSafeId(reference);
        $(`#${safeId} .punch-list-row`).each(function(index) {
            $(this).find('.row-number').text(index + 1);
            $(this).find('.number-input').val(index + 1);
        });
    }

    // Helper functions
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

    function getNextRowNumber(reference) {
        const safeId = createSafeId(reference);
        const existingRows = $(`#${safeId} .punch-list-row`).length;
        return existingRows + 1;
    }

    function updateRemoveButtonVisibility(reference) {
        if (!reference) {
            console.error('No reference provided for updating remove button visibility');
            return;
        }
        const safeId = createSafeId(reference);
        const rowCount = $(`#${safeId} .punch-list-row`).length;
        const removeButton = $(`.remove-punch-list-row[data-reference="${reference}"]`);
        
        // Always show the remove button if there's at least one row
        if (rowCount > 0) {
            removeButton.show();
        } else {
            removeButton.hide();
        }
    }

    function initializeRemarkHandlers() {
        // Remove any existing handlers
        $(document).off('change.notok input.notok');

        // Handle radio button changes
        $(document).on('change.notok', '.passed-radio', function() {
            const $radio = $(this);
            const value = $radio.val();
            const $row = $radio.closest('tr');
            const $remarkTextarea = $row.find('.remark-textarea');
            const reference = $row.closest('table').find('tr:first td:first b').text().trim();
            const itemId = $radio.data('item-id');
            
            if (value === 'notok') {
                // Enable the textarea for editing
                $remarkTextarea.prop('disabled', false).css({
                    'opacity': '1',
                    'background-color': '#fff',
                    'cursor': 'text',
                    'border': '1px solid #ddd',
                    'color': '#000000',
                    '-webkit-text-fill-color': '#000000'
                });
                
                // Set up the remark handler
                setupRemarkHandler($remarkTextarea, reference, itemId);
                
                // If there's existing text, update punch list
                const existingRemark = $remarkTextarea.val().trim();
                if (existingRemark) {
                    addRemarkToPunchList(reference, existingRemark, itemId);
                }
            } else {
                // If changed from notok, remove the row for this item from punch list
                const safeId = createSafeId(reference);
                const existingRow = $(`#${safeId} .punch-list-body .punch-list-row[data-item-id="${itemId}"]`);
                if (existingRow.length) {
                    removeRow(existingRow);
                }
                
                // Clear and enable the textarea
                $remarkTextarea.val('').prop('disabled', false).css({
                    'opacity': '1',
                    'background-color': '#fff',
                    'cursor': 'text',
                    'border': '1px solid #ddd',
                    'color': '#000000',
                    '-webkit-text-fill-color': '#000000'
                });
            }
        });

        // Handle existing 'notok' selections and their remarks
        $('.passed-radio:checked[value="notok"]').each(function() {
            const $radio = $(this);
            const $row = $radio.closest('tr');
            const $remarkTextarea = $row.find('.remark-textarea');
            const reference = $row.closest('table').find('tr:first td:first b').text().trim();
            const itemId = $radio.data('item-id');
            
            // Set up the remark handler
            setupRemarkHandler($remarkTextarea, reference, itemId);
        });

        // Handle remarks for items that already have remarks
        $('.remark-textarea').each(function() {
            const $textarea = $(this);
            const $row = $textarea.closest('tr');
            const $radio = $row.find('.passed-radio:checked');
            
            if ($radio.val() === 'notok') {
                const reference = $row.closest('table').find('tr:first td:first b').text().trim();
                const itemId = $row.find('.passed-radio').first().data('item-id');
                
                // Set up handler
                setupRemarkHandler($textarea, reference, itemId);
            }
        });
    }

    function setupRemarkHandler($textarea, reference, itemId) {
        let timeoutId;
        
        $textarea.off('input.notok').on('input.notok', function() {
            const $this = $(this);
            clearTimeout(timeoutId);
            
            timeoutId = setTimeout(() => {
                const newRemark = $this.val().trim();
                
                if (newRemark) {
                    // Keep the textarea enabled for editing
                    $textarea.prop('disabled', false).css({
                        'opacity': '1',
                        'background-color': '#fff',
                        'cursor': 'text',
                        'border': '1px solid #ddd',
                        'color': '#000000',
                        '-webkit-text-fill-color': '#000000',
                        'pointer-events': 'auto'
                    });
                    
                    // Add or update the punch list row for this item
                    addRemarkToPunchList(reference, newRemark, itemId);
                } else {
                    // Remove the row if remark is empty
                    const safeId = createSafeId(reference);
                    const existingRow = $(`#${safeId} .punch-list-body .punch-list-row[data-item-id="${itemId}"]`);
                    if (existingRow.length) {
                        removeRow(existingRow);
                    }
                }
            }, 500);
        });
    }

    function addRemarkToPunchList(reference, remark, itemId) {
        if (!reference || !remark || !itemId) {
            console.warn('Missing required parameters:', { reference, remark, itemId });
            return;
        }

        const safeId = createSafeId(reference);
        const tbody = $(`#${safeId} .punch-list-body`);
        
        // Find the source checklist row
        const $sourceRow = $(`tr[data-item-id="${itemId}"]`).not('.punch-list-row').first();
        
        if ($sourceRow.length === 0) {
            console.warn('Source row not found for itemId:', itemId);
            return;
        }
        
        const checklistDataId = $sourceRow.data('checklist-data-id');
        
        console.log('Adding remark - itemId:', itemId, 'checklistDataId:', checklistDataId, 'reference:', reference);
        
        // Look for existing row with this itemId in punch list
        let existingRow = tbody.find(`.punch-list-row[data-item-id="${itemId}"]`);
        
        if (existingRow.length > 0) {
            // Update existing row
            console.log('Updating existing punch list row');
            const $description = existingRow.find('.description-input');
            $description.val(remark);
            
            // Ensure data attributes are set
            if (!existingRow.attr('data-checklist-data-id')) {
                existingRow.attr('data-checklist-data-id', checklistDataId);
            }
            
            // Style the description field
            $description.prop('disabled', true).css({
                'opacity': '0.7',
                'background-color': '#f5f5f5',
                'cursor': 'not-allowed',
                'border': '1px solid #ddd',
                'color': '#000000',
                '-webkit-text-fill-color': '#000000',
                'white-space': 'pre-wrap',
                'word-wrap': 'break-word',
                'overflow': 'hidden',
                'resize': 'none'
            });
            
            // Adjust height
            const textarea = $description[0];
            if (textarea) {
                textarea.style.height = 'auto';
                textarea.style.height = (textarea.scrollHeight) + 'px';
            }
        } else {
            // Try to reuse first empty row
            const firstRow = tbody.find('.punch-list-row').first();
            let rowToUse = null;
            
            if (firstRow.length > 0) {
                const firstRowDescription = firstRow.find('.description-input').val().trim();
                const firstRowItemId = firstRow.attr('data-item-id');
                
                // Only reuse if it's truly empty and doesn't belong to another item
                if (!firstRowDescription && !firstRowItemId) {
                    rowToUse = firstRow;
                }
            }
            
            if (rowToUse) {
                console.log('Reusing first empty row');
                // Set data attributes
                rowToUse.attr('data-item-id', itemId);
                rowToUse.attr('data-checklist-data-id', checklistDataId);
                
                const $description = rowToUse.find('.description-input');
                $description.val(remark);
                
                $description.prop('disabled', true).css({
                    'opacity': '0.7',
                    'background-color': '#f5f5f5',
                    'cursor': 'not-allowed',
                    'border': '1px solid #ddd',
                    'color': '#000000',
                    '-webkit-text-fill-color': '#000000',
                    'white-space': 'pre-wrap',
                    'word-wrap': 'break-word',
                    'overflow': 'hidden',
                    'resize': 'none'
                });
                
                // Initialize user and date fields
                const wrappedUser = currentUser.replace(/(.{20})/g, '$1\n');
                const currentDate = formatDate(new Date());
                
                rowToUse.find('.finding-by').val(wrappedUser);
                rowToUse.find('.finding-date').val(currentDate);
                rowToUse.find('.resolution-by').val(wrappedUser);
                rowToUse.find('.resolution-date').val(currentDate);
                rowToUse.find('.rechecking-by').val(wrappedUser);
                rowToUse.find('.rechecking-date').val(currentDate);
            } else {
                console.log('Creating new punch list row');
                // Create new row
                const newRow = $(createPunchListRow(reference));
                
                // Set data attributes BEFORE appending
                newRow.attr('data-item-id', itemId);
                newRow.attr('data-checklist-data-id', checklistDataId);
                
                const $description = newRow.find('.description-input');
                $description.val(remark);
                
                $description.prop('disabled', true).css({
                    'opacity': '0.7',
                    'background-color': '#f5f5f5',
                    'cursor': 'not-allowed',
                    'border': '1px solid #ddd',
                    'color': '#000000',
                    '-webkit-text-fill-color': '#000000',
                    'white-space': 'pre-wrap',
                    'word-wrap': 'break-word',
                    'overflow': 'hidden',
                    'resize': 'none'
                });

                // Initialize user and date fields
                const wrappedUser = currentUser.replace(/(.{20})/g, '$1\n');
                const currentDate = formatDate(new Date());
                
                newRow.find('.finding-by').val(wrappedUser);
                newRow.find('.finding-date').val(currentDate);
                newRow.find('.resolution-by').val(wrappedUser);
                newRow.find('.resolution-date').val(currentDate);
                newRow.find('.rechecking-by').val(wrappedUser);
                newRow.find('.rechecking-date').val(currentDate);
                
                tbody.append(newRow);
            }
        }
        
        // Update row numbers and visibility
        updateRowNumbers(reference);
        updateRemoveButtonVisibility(reference);
        initializeTextareaAutoResize();

        // Add validation for work hours and code
        tbody.find('.work-hrs-input').off('input').on('input', function() {
            let value = this.value.replace(/[^\d.]/g, '');
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            if (parts.length > 1) {
                value = parts[0] + '.' + parts[1].slice(0, 2);
            }
            this.value = value;
        });

        tbody.find('.code-input').off('input').on('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 2) {
                value = value.slice(0, 2);
            }
            this.value = value;
        });

        // Ensure proper textarea heights
        tbody.find('textarea').each(function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }

    function removeRow(row) {
        if (!row || !row.length) return;
        
        const reference = row.closest('.punch-list-wrapper').data('reference');
        
        row.fadeOut(300, function() {
            $(this).remove();
            if (reference) {
                updateRowNumbers(reference);
                updateRemoveButtonVisibility(reference);
            }
        });
    }

    // Function to save selected values to server
    function saveSelectedValuesToServerChecklist(type) {
        let data = {};
        // Check the valueToSave parameter and set the appropriate data
        if (type === "project") {
            data = {
                project: $('#projectSearchInput').val()
            };
        } else {
            data = {
                project: $('#projectSearchInput').val(),
                panel: $('#panelSearch').dropdown('get value'),
                checklist: $('#checklistSearch').dropdown('get value'),
                line: $('#lineSearch').dropdown('get value'),
                product: $('#productSearch').dropdown('get value'),
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

    function disableChecklistButtons() {
        $('#saveChecklistBtn, #submitChecklistBtn').prop('disabled', true).css({
            'cursor': 'not-allowed',
            'opacity': '0.6'
        });
    }

    function enableChecklistButtons() {
        $('#saveChecklistBtn, #submitChecklistBtn').prop('disabled', false).css({
            'cursor': 'pointer',
            'opacity': '1'
        });
    }

    // Global variable to store footer information
    let cachedFooterInfo = {
        document_description: 'Annex 7 to Documented Procedure No. 2-00-00-2-74-529',
        revision: 'Revision: 00'
    };

    /**
     * Fetch footer information from database
     */
    async function fetchFooterInfo() {
        const checklistId = $('#checklistSearch').dropdown('get value');
        const lineId = $('#lineSearch').dropdown('get value');
        const productId = $('#productSearch').dropdown('get value');

        if (!checklistId || !lineId || !productId) {
            console.warn('Missing required parameters for footer info');
            return cachedFooterInfo;
        }

        try {
            const response = await $.ajax({
                url: '/dpm/dwc/api/checklistAPI.php?type=get_footer_info',
                method: 'POST',
                dataType: 'json',
                data: {
                    checklist_id: checklistId,
                    line_id: lineId,
                    product_id: productId
                },
                timeout: 5000 // 5 second timeout
            });

            if (response && (response.success || response.document_description)) {
                cachedFooterInfo = {
                    document_description: response.document_description || 'Annex 7 to Documented Procedure No. 2-00-00-2-74-529',
                    revision: response.revision || 'Revision: 00'
                };
                console.log('Footer info loaded from database:', cachedFooterInfo);
            }

            return cachedFooterInfo;
        } catch (error) {
            console.error('Error fetching footer info:', error);
            return cachedFooterInfo;
        }
    }
    
    // Function to generate PDF using html2canvas and jsPDF
    async function downloadPdf() {
        showFullLoader();

        const originalContent = document.querySelector('.card-body');
        if (!originalContent) {
            toastr.error('Could not find the content to generate PDF.');
            hideFullLoader();
            return;
        }

        // Fetch footer information from database
        const footerInfo = await fetchFooterInfo();

        // Create a clone of the content for PDF generation
        const pdfContent = originalContent.cloneNode(true);
        
        // Create a hidden container for the PDF content
        const hiddenContainer = document.createElement('div');
        hiddenContainer.style.cssText = `
            position: absolute;
            left: -9999px;
            top: -9999px;
            width: ${originalContent.offsetWidth}px;
        `;
        hiddenContainer.appendChild(pdfContent);
        document.body.appendChild(hiddenContainer);

        try {
            // Remove empty punch lists and codes section from all but the last non-empty punch list
            const punchLists = Array.from(pdfContent.querySelectorAll('.punch-list-wrapper'));
            
            // Filter out punch lists that are empty (no rows or only empty rows)
            const nonEmptyPunchLists = punchLists.filter(punchList => {
                const rows = punchList.querySelectorAll('.punch-list-body .punch-list-row');
                // Check if there are any rows with content
                return Array.from(rows).some(row => {
                    const description = row.querySelector('.description-input')?.value?.trim();
                    return description && description.length > 0;
                });
            });

            // Remove empty punch lists
            punchLists.forEach(punchList => {
                if (!nonEmptyPunchLists.includes(punchList)) {
                    punchList.remove();
                }
            });

            // Handle codes section for non-empty punch lists
            if (nonEmptyPunchLists.length > 0) {
                nonEmptyPunchLists.forEach((punchList, index) => {
                    // Keep the header for all non-empty punch lists
                    const punchListTable = punchList.querySelector('table');
                    if (punchListTable) {
                        // Get all rows
                        const rows = Array.from(punchListTable.rows);
                        
                        // If it's not the last punch list, remove only the codes section
                        if (index < nonEmptyPunchLists.length - 1) {
                            // Find the codes section (last row with colspan="13")
                            const codesRow = rows.find(row => 
                                row.cells[0]?.getAttribute('colspan') === '13' && 
                                row.textContent.includes('Codes:')
                            );
                            
                            if (codesRow) {
                                codesRow.remove();
                            }
                        }
                    }
                });
            }

            // Remove buttons and alert messages from the clone
            const elementsToRemove = pdfContent.querySelectorAll(`
                .add-punch-list-row,
                .remove-punch-list-row,
                #downloadPdfBtn,
                #saveChecklistBtn,
                #submitChecklistBtn,
                .alert.alert-info,
                .punch-list-buttons,
                #backButton
            `);
            elementsToRemove.forEach(element => {
                if (element && element.parentNode) {
                    element.parentNode.removeChild(element);
                }
            });

            // Also remove any button containers or wrappers
            const buttonContainers = pdfContent.querySelectorAll(`
                div[style*="margin-top: -20px; text-align: left;"],
                div[style*="text-align: left; margin-top: 10px;"]
            `);
            buttonContainers.forEach(container => {
                if (container && container.parentNode) {
                    container.parentNode.removeChild(container);
                }
            });

            // Remove all <br> tags
            const brTags = pdfContent.querySelectorAll('br');
            brTags.forEach(br => {
                if (br && br.parentNode) {
                    br.parentNode.removeChild(br);
                }
            });

            // Handle inline fields in the clone
            const inlineFields = pdfContent.querySelectorAll('.addon-input, .serial-input, .underline-input, .editable-field, .sub-item-dropdown');
            inlineFields.forEach(field => {
                const wrapper = document.createElement('span');
                const computedStyle = window.getComputedStyle(field);

                wrapper.style.cssText = `
                    display: inline !important;
                    white-space: nowrap !important;
                    margin-right: 5px !important;
                    vertical-align: baseline !important;
                    font-size: ${computedStyle.fontSize} !important;
                `;

                let value = field.value || field.textContent || '';
                
                // Special handling for sub-item dropdown in Component products
                if (field.classList.contains('sub-item-dropdown')) {
                    // Get the value from the original form (not the clone)
                    const originalDropdown = document.querySelector('#subItemDropdown');
                    if (originalDropdown) {
                        const selectedValue = originalDropdown.value;
                        const selectedOption = originalDropdown.options[originalDropdown.selectedIndex];
                        
                        // Use the selected option text if available, otherwise use the value
                        value = selectedOption ? selectedOption.text : selectedValue;
                        
                        // If still empty or showing placeholder, try to get from headerInfo
                        if (!value || value === '-- Select --' || value.trim() === '') {
                            // Get the value that was saved in DB (from the form's current state)
                            value = selectedValue || '';
                        }
                    }
                }
                
                // Only create wrapper if there's a value to display
                if (value && value !== '-- Select --' && value.trim() !== '') {
                    wrapper.innerHTML = `
                        <span style="
                            display: inline !important;
                            border-bottom: 1px solid #000 !important;
                            padding: 0 5px !important;
                            margin: 0 5px !important;
                            min-width: ${computedStyle.width} !important;
                            white-space: nowrap !important;
                            font-size: ${computedStyle.fontSize} !important;
                        ">${value}</span>
                    `;
                    
                    field.parentNode.insertBefore(wrapper, field);
                }
                
                field.style.display = 'none';
            });
            
            // Handle textareas in the clone
            const textareas = pdfContent.querySelectorAll('textarea, .remark-textarea');
            textareas.forEach(textarea => {
                const div = document.createElement('div');
                const computedStyle = window.getComputedStyle(textarea);

                div.textContent = textarea.value || textarea.textContent;
                div.style.cssText = `
                    width: 100% !important;
                    min-height: ${textarea.classList.contains('remark-textarea') ? '60px' : 'auto'} !important;
                    padding: 5px !important;
                    border: 1px solid #ddd !important;
                    border-radius: 4px !important;
                    background-color: ${computedStyle.backgroundColor} !important;
                    white-space: pre-wrap !important;
                    word-wrap: break-word !important;
                    text-align: left !important;
                    margin: 0 !important;
                    font-size: ${computedStyle.fontSize} !important;
                `;

                textarea.parentNode.insertBefore(div, textarea);
                textarea.style.display = 'none';
            });

            // Generate canvas from the modified content
            const canvas = await html2canvas(pdfContent, {
                scale: 2,
                useCORS: true,
                logging: false,
                windowWidth: originalContent.offsetWidth,
                windowHeight: originalContent.scrollHeight,
                backgroundColor: '#ffffff'
            });

            const { jsPDF } = window.jspdf;
            
            // Initialize PDF with custom margins
            const pdf = new jsPDF({
                orientation: 'p',
                unit: 'mm',
                format: 'a3',
                compress: true
            });

            // A3 PDF dimensions and margins (in mm)
            const pageWidth = 297;
            const pageHeight = 420;
            const margin = {
                top: 25,
                bottom: 40,
                left: 20,
                right: 20
            };

            // Use dynamic footer information from database
            const footerText = footerInfo.document_description || 'Annex 7 to Documented Procedure No. 2-00-00-2-74-529';
            const footerRevision = footerInfo.revision || 'Revision: 00';
            const footerClassification = `Intern / Restricted`;

            // Calculate content dimensions
            const contentWidth = pageWidth - (margin.left + margin.right);
            const contentHeight = pageHeight - (margin.top + margin.bottom);

            // Calculate scale to fit width
            const scale = contentWidth / canvas.width;
            const scaledHeight = canvas.height * scale;

            // Calculate total pages needed - with better precision
            const totalPages = Math.ceil(scaledHeight / contentHeight);

            // Process each page
            for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
                if (pageNum > 1) {
                    pdf.addPage('a3', 'p');
                }

                // Calculate source area for this page
                const sourceY = (pageNum - 1) * (contentHeight / scale);
                const sourceHeight = Math.min(
                    contentHeight / scale,
                    canvas.height - sourceY
                );

                // Skip if source height is too small (blank page)
                if (sourceHeight < 10) {
                    continue;
                }

                // Create temporary canvas for this segment
                const tempCanvas = document.createElement('canvas');
                const tempCtx = tempCanvas.getContext('2d');
                tempCanvas.width = canvas.width;
                tempCanvas.height = sourceHeight;

                // Draw only the portion needed for this page
                tempCtx.drawImage(
                    canvas,
                    0, sourceY,
                    canvas.width, sourceHeight,
                    0, 0,
                    canvas.width, sourceHeight
                );

                // Add this segment to PDF
                const pageImgData = tempCanvas.toDataURL('image/png');
                const imgHeight = (sourceHeight * contentWidth) / canvas.width;

                pdf.addImage(
                    pageImgData,
                    'PNG',
                    margin.left,
                    margin.top,
                    contentWidth,
                    imgHeight,
                    null,
                    'FAST'
                );

                // Add footer to every page with dynamic information
                addFooterToPage(
                    pdf,
                    pageNum,
                    totalPages,
                    pageWidth,
                    pageHeight,
                    margin,
                    footerText,
                    footerRevision,
                    footerClassification
                );
            }

            // Remove extra blank pages that might have been added
            const totalPagesInPdf = pdf.internal.pages.length - 1;
            if (totalPagesInPdf > totalPages) {
                for (let i = totalPages + 1; i <= totalPagesInPdf; i++) {
                    pdf.deletePage(i);
                }
            }

            // Save the PDF - only once
            pdf.save('Checklist_Form.pdf');
            toastr.success('PDF generated successfully!');

        } catch (error) {
            console.error('Error generating PDF:', error);
            toastr.error('Failed to generate PDF. Please try again.');
        } finally {
            // Remove the hidden container
            if (hiddenContainer && hiddenContainer.parentNode) {
                hiddenContainer.parentNode.removeChild(hiddenContainer);
            }
            hideFullLoader();
        }
    }

    /**
     * Add footer to each PDF page with dynamic information
     * @param {jsPDF} pdf - The PDF document object
     * @param {number} pageNum - Current page number
     * @param {number} totalPages - Total number of pages
     * @param {number} pageWidth - Page width in mm
     * @param {number} pageHeight - Page height in mm
     * @param {object} margin - Margin object with top, bottom, left, right
     * @param {string} footerText - Main footer text (left side) - from database
     * @param {string} footerRevision - Revision text (left side) - from database
     * @param {string} footerClassification - Classification text (right side)
     */
    function addFooterToPage(pdf, pageNum, totalPages, pageWidth, pageHeight, margin, footerText, footerRevision, footerClassification) {
        // Footer positioning
        const footerY = pageHeight - margin.bottom + 8;
        const footerLineY = pageHeight - margin.bottom + 3;
        
        // Set font for footer
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(9);
        pdf.setTextColor(0, 0, 0);

        // Draw horizontal line at top of footer
        pdf.setDrawColor(0, 0, 0);
        pdf.setLineWidth(0.5);
        pdf.line(margin.left, footerLineY, pageWidth - margin.right, footerLineY);

        // Left side footer text - Dynamic from database
        pdf.text(
            footerText,
            margin.left,
            footerY,
            { align: 'left' }
        );

        // Revision text (below main text on left) - Dynamic from database
        pdf.setFontSize(8);
        pdf.text(
            footerRevision,
            margin.left,
            footerY + 5,
            { align: 'left' }
        );

        // Right side - Page numbers
        pdf.setFontSize(9);
        pdf.setFont('helvetica', 'normal');
        const pageNumberText = `Page ${pageNum} of ${totalPages}`;
        pdf.text(
            pageNumberText,
            pageWidth - margin.right,
            footerY,
            { align: 'right' }
        );

        // Right side - Classification (IN BOLD)
        pdf.setFontSize(9);
        pdf.setFont('helvetica', 'bold');
        pdf.text(
            footerClassification,
            pageWidth - margin.right,
            footerY + 5,
            { align: 'right' }
        );
    }

    // ===== REPLACE THE EVENT LISTENER AT THE END =====

    // Remove any duplicate event listeners and add single listener
    $(document).off('click', '#downloadPdfBtn');
    $(document).on('click', '#downloadPdfBtn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        downloadPdf();
    });
</script>
<div id="fullPageLoader" class="full-page-loader" style="display: none; z-index: 10001;">
    <div class="loader-content">
        <div class="spinner"></div>
        <div id="loaderMessage">Loading...</div>
    </div>
</div>
</body>
</html>