<?php
// /dpm/dto_configurator/projects/index.php
SharedManager::checkAuthToModule(24);
include_once '../../core/index.php';
$project = $_GET["project"] ?: 0;
SharedManager::saveLog("log_project_information", "View for Project: $project");
$menu_header_display = 'Project Information';
$project_information = "1";
$currentUser = isset($_SESSION['username']) ? $_SESSION['username'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>OneX | Projects</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=yes"/>

    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta charset="utf-8">

    <link href="../../../css/semantic.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="../../../css/dataTables.semanticui.min.css">
    <link rel="stylesheet" type="text/css" href="../../../css/responsive.dataTables.min.css">

    <link href="../../../css/main.css?13" rel="stylesheet"/>

    <?php include_once '../../shared/headerStyles.php' ?>
    
    <script src="../../../js/jquery.min.js"></script>
    <script src="../../../js/semantic.min.js"></script>
    <script src="../../../js/jquery.dataTables.js"></script>
    <script src="../../../js/dataTables.semanticui.min.js"></script>
    <script src="../../../js/dataTables.buttons.min.js"></script>
    <script src="../../../js/buttons.flash.min.js"></script>
    <script src="../../../js/jszip.min.js"></script>
    <script src="../../../js/pdfmake.min.js"></script>
    <script src="../../../js/vfs_fonts.js"></script>
    <script src="../../../js/buttons.html5.min.js"></script>
    <script src="../../../js/buttons.print.min.js"></script>
    <script src="../../../js/buttons.colVis.min.js"></script>
    <script src="../../../js/tablesort.js"></script>
    <script src="../../../js/Semantic-UI-Alert.js"></script>
    <script src="../../../js/dataTables.fixedHeader.min.js"></script>
    <script src="../../../shared/inspia_gh_assets/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <link rel="stylesheet" href="../../../css/jquery.toast.min.css">

    <script src="/shared/inspia_gh_assets/js/popper.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/bootstrap.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/bootstrap-select.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <script src="/shared/inspia_gh_assets/js/plugins/dataTables/datatables.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/dataTables/dataTables.bootstrap4.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/select2/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" />
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
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<style>
    * {
        box-sizing: border-box;
    }

    html, body {
        margin: 0;
        padding: 0;
        height: 100%;
    }

    .status-box {
        border-radius: 4px;
        font-weight: bold;
        text-align: center;
        display: inline-block;
        padding: 4px 8px !important;
        font-size: 11px !important;
        min-width: 80px !important;
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

    .btn-sm.edit-btn {
        height: auto;
        display: inline-block;
        padding: 4px 8px !important;
        font-size: 11px !important;
        min-width: 60px !important;
    }

    .btn-sm.edit-btn i.fa {
        font-size: 11px !important;
        margin-right: 3px !important;
    }

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

    .punch-input:disabled {
        background-color: #f5f5f5;
        cursor: not-allowed;
        opacity: 0.7;
    }

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

    @media only screen and (max-width: 767px) {
        .ui.grid > .column {
            width: 100% !important;
        }
    }

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
        max-height: 300px;
        overflow-y: auto;
        z-index: 9999;
        display: none;
    }

    .suggestion-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        font-size: 12px;
    }

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

    .daterangepicker {
        z-index: 1100;
    }

    #resultsTable {
        width: 100% !important;
        margin: 0 !important;
    }

    #customFilterParent,
    .ui.form .ui.grid {
        width: 100% !important;
        margin: 0 !important;
    }

    #resultsTable thead th {
        text-align: center !important;
        vertical-align: middle !important;
        font-size: 11px !important;
        padding: 8px 4px !important;
        white-space: nowrap;
    }

    #resultsTable tbody td {
        text-align: center !important;
        vertical-align: middle !important;
        font-size: 11px !important;
        padding: 6px 4px !important;
    }

    #resultsTable .btn {
        margin: 0 auto;
        display: block;
    }

    #resultsTable .status-box {
        margin: 0 auto;
        display: block;
    }

    .card {
        width: 100%;
        margin: 0;
        padding: 0;
    }

    .card-body {
        padding: 10px !important;
        width: 100%;
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
        font-size: 11px !important;
    }

    #resultsTable th,
    #resultsTable td {
        white-space: normal;
        word-wrap: break-word;
    }

    .card > .card-body {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    @media screen and (max-width: 1400px) {
        .card {
            margin: 0;
        }
        
        .card-body {
            padding: 10px;
        }
    }

    .dataTables_length, 
    .dataTables_filter, 
    .dataTables_info, 
    .dataTables_paginate {
        font-size: 11px !important;
        padding: 5px 0 !important;
    }

    .dataTables_filter input {
        height: 28px !important;
        padding: 4px 8px !important;
        font-size: 11px !important;
    }

    .dataTables_length select {
        height: 28px !important;
        padding: 2px 4px !important;
        font-size: 11px !important;
    }

    .status-box,
    .btn-sm.edit-btn {
        white-space: nowrap;
    }

    .ui.form .field {
        position: relative;
    }

    .dataTables_scroll {
        width: 100% !important;
    }

    .dataTables_scrollBody {
        width: 100% !important;
    }

    .dataTables_scrollHead {
        overflow: visible !important;
    }
    
    #wrapper {
        display: flex;
        flex-direction: column;
        height: 100vh;
    }

    #page-wrapper {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 10px;
    }

    #resultsTable td:last-child {
        text-align: center;
    }

    .btn-sm.edit-btn:hover {
        background-color: #0056b3;
        border-color: #0056b3;
        transform: translateY(-1px);
        transition: all 0.2s ease;
    }

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

    .dt-buttons {
        text-align: center !important;
        margin-bottom: 10px;
    }

    .dataTables_filter {
        float: right !important;
    }

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

    .dt-buttons .btn-success {
        background-color: #28a745;
        border-color: #28a745;
        color: white;
    }

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

    .dataTables_wrapper {
        overflow: hidden;
        position: relative;
    }

    .dataTables_scroll {
        overflow: auto;
        max-height: 65vh;
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

    table.dataTable thead th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        z-index: 10;
    }

    .table-responsive {
        width: 100%;
        overflow: visible;
    }

    #projectsGrid {
        padding: 0;
    }

    .search-header {
        margin-bottom: 30px;
    }

    .search-header h3 {
        margin-bottom: 15px;
    }

    .search-dropdown {
        width: 100%;
    }

    #projectsTableContainer {
        margin-top: 20px;
    }

    #projectsTable {
        width: 100%;
    }

    #projectsTable thead th {
        background-color: #f8f9fa;
        font-weight: bold;
        text-align: center;
        padding: 8px 4px !important;
        font-size: 11px !important;
    }

    #projectsTable tbody td {
        padding: 6px 4px !important;
        text-align: center;
        font-size: 11px !important;
    }

    #projectsTable tbody tr:hover {
        background-color: #f5f5f5;
    }

    .owner-input-wrapper {
        position: relative;
        width: 100%;
    }

    .owner-input-wrapper input {
        width: 100%;
        padding: 10px 12px;
        border: 2px solid #ddd;
        border-radius: 6px;
        font-size: 13px;
        font-family: Arial, sans-serif;
        box-sizing: border-box;
        transition: all 0.3s ease;
        padding-right: 35px;
    }

    .owner-input-wrapper input:focus {
        outline: none;
        border-color: #00b5ad;
        box-shadow: 0 0 0 3px rgba(0, 181, 173, 0.1);
    }

    .owner-input-wrapper input::placeholder {
        color: #999;
    }

    .owner-input-wrapper .icon-container {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        height: 20px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 100;
        transition: all 0.3s ease;
    }

    .owner-input-wrapper .icon-container:hover {
        transform: translateY(-50%) scale(1.1);
    }

    .owner-input-wrapper .search-icon {
        width: 18px;
        height: 18px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2300b5ad' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        pointer-events: none;
    }

    .owner-input-wrapper .clear-icon {
        width: 20px;
        height: 20px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23dc3545' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cline x1='18' y1='6' x2='6' y2='18'/%3E%3Cline x1='6' y1='6' x2='18' y2='18'/%3E%3C/svg%3E");
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        pointer-events: auto;
    }

    .owner-dropdown-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 6px 6px;
        max-height: 250px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: none;
        margin-top: -2px;
    }

    .owner-dropdown-list.show {
        display: block;
        animation: slideDown 0.2s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .owner-dropdown-list .owner-dropdown-item {
        padding: 12px 14px;
        cursor: pointer;
        font-size: 13px;
        border-bottom: 1px solid #f5f5f5;
        transition: all 0.2s ease;
        color: #555;
    }

    .owner-dropdown-list .owner-dropdown-item:last-child {
        border-bottom: none;
    }

    .owner-dropdown-list .owner-dropdown-item:hover {
        background-color: #00b5ad;
        color: white;
        padding-left: 18px;
    }

    .owner-dropdown-list .owner-dropdown-item.no-results {
        padding: 12px 14px;
        text-align: center;
        color: #999;
        cursor: default;
        font-style: italic;
    }

    .owner-dropdown-list .owner-dropdown-item.no-results:hover {
        background-color: transparent;
        color: #999;
        padding-left: 14px;
    }

    .owner-dropdown-list::-webkit-scrollbar {
        width: 6px;
    }

    .owner-dropdown-list::-webkit-scrollbar-track {
        background: #f5f5f5;
        border-radius: 3px;
    }

    .owner-dropdown-list::-webkit-scrollbar-thumb {
        background: #00b5ad;
        border-radius: 3px;
    }

    .owner-dropdown-list::-webkit-scrollbar-thumb:hover {
        background: #008b87;
    }

    .loading-indicator {
        text-align: center;
        padding: 20px;
        color: #00b5ad;
        font-weight: bold;
    }

    .spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #00b5ad;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        animation: spin 1s linear infinite;
        display: inline-block;
        margin-right: 10px;
        vertical-align: middle;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Custom Loader Overlay */
    .custom-loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        z-index: 9998;
        justify-content: center;
        align-items: center;
    }

    .custom-loader-overlay.show {
        display: flex;
    }

    .custom-loader-container {
        background: white;
        padding: 40px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        text-align: center;
        min-width: 200px;
    }

    .custom-loader-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #00b5ad;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
        margin: 0 auto 15px;
    }

    .custom-loader-text {
        color: #333;
        font-size: 14px;
        font-weight: 500;
        margin: 0;
    }

    /* Hide DataTable Processing */
    .dataTables_processing {
        display: none !important;
    }

    /* Scrollbar styling for page wrapper */
    #page-wrapper::-webkit-scrollbar {
        width: 10px;
    }

    #page-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    #page-wrapper::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 5px;
    }

    #page-wrapper::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
<body>
<div id="wrapper">
    <?php $activePage = '/index.php'; ?>
    <?php include_once '../../shared/dto_sidebar.php'; ?>
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom" style="position: relative; margin: 0;">
            <div class="ui fixed menu" style="padding: 21px; color:teal; width: 100%;">
                <div class="ui container" style="position: relative; width: 100%;">
                    <div style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); display: flex; align-items: center;">
                        <a href="/" style="display: flex; align-items: center; text-decoration: none;">
                            <div style="margin-right: 10px;">
                                <img src="/images/onex_icon.png" width="25" height="36" class="logo-icon">
                            </div>
                            <div class="logo-text">
                                <h5 style="margin: 0; font-size: 18px; line-height: 1.2;">
                                    DTO Configurator <sup class="badge badge-danger" style="font-size: 0.4em; background-color: #dc3545; color: white; padding: 0.2em 0.3em; border-radius: 0.25rem; vertical-align: super;">OneX</sup>
                                </h5>
                                <p style="margin: 0; text-transform: uppercase; font-size: 10px; color: #6c757d; line-height: 1.2;">Web system for configuring DTOs</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Loader Overlay -->
        <div class="custom-loader-overlay" id="customLoaderOverlay">
            <div class="custom-loader-container">
                <div class="custom-loader-spinner"></div>
                <p class="custom-loader-text">Loading</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Search Project Section -->
                        <div id="projectsGrid">
                            <div style="margin-bottom: 20px; margin-top: 35px; display: flex; align-items: flex-end; gap: 10px;">
                                <div class="field search-field-container" style="flex: 1; max-width: 400px;">
                                    <label style="font-weight: bold; margin-bottom: 8px; display: block;">Search Project</label>
                                    <div class="owner-input-wrapper">
                                        <input type="text" id="searchProjectSelect" name="searchProjectSelect" placeholder="Enter Project Number or Project Name" autocomplete="off">
                                        <div class="icon-container">
                                            <div class="search-icon" id="searchIcon"></div>
                                            <div class="clear-icon" id="clearSearchIcon" style="display: none;"></div>
                                        </div>
                                        <div class="owner-dropdown-list" id="projectDropdown"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Projects Table -->
                            <div id="projectsTableContainer">
                                <table id="projectsTable" class="table table-striped table-bordered" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Contact</th>
                                            <th>Project No</th>
                                            <th>Nachbau No</th>
                                            <th>Project Name</th>
                                            <th>Panel</th>
                                            <th>Product</th>
                                            <th>Last Worked</th>
                                            <th>Checked In</th>
                                            <th>Status</th>
                                            <th>Latest Nachbau</th>
                                            <th>Last Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $footer_display = 'Projects';
    include_once '../../../assemblynotes/shared/footer.php'; ?>
</div>

<script src="/dpm/dto_configurator/js/projects/index.js?<?=uniqid()?>"></script>
</body>
</html>