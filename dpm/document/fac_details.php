<?php
SharedManager::checkAuthToModule(22);
include_once '../core/index.php';
$project = $_GET["project"] ?: 0;
SharedManager::saveLog("log_fac_details", "View for Project: $project");
$menu_header_display = 'FAC Certificate Management';
$currentUser = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Check if user has edit rights (Module 23)
$hasEditRights = in_array(23, SharedManager::getUser()["Modules"]);
$hasAdminRights = in_array(23, SharedManager::getUser()["Modules"]);
$hasViewRights = in_array(22, SharedManager::getUser()["Modules"]);
?>
<!DOCTYPE html>
<html>
<head>
    <title>OneX | FAC (Function Allocation Chart)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=yes"/>

    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta charset="utf-8">

    <link href="../../css/semantic.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="../../css/dataTables.semanticui.min.css">
    <link rel="stylesheet" type="text/css" href="../../css/responsive.dataTables.min.css">
    <link href="../../css/main.css?13" rel="stylesheet"/>

    <?php include_once '../shared/headerStyles.php' ?>
    
    <!-- ✅ JQUERY UI CSS FOR DATEPICKER -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    
    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/semantic.min.js"></script>
    <script src="../../js/jquery.dataTables.js"></script>
    <script src="../../js/dataTables.semanticui.min.js"></script>
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
    
    <!-- ✅ JQUERY UI JS FOR DATEPICKER -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pptxjs/0.1.2/pptxjs.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <!-- ✅ CUSTOM STYLES -->
    <style>
        /* ✅ FIX: Ensure datepicker dropdown is visible above modal */
        .ui-datepicker {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            font-size: 12px;
            z-index: 99999 !important;
            position: absolute !important;
        }

        .ui-datepicker-header {
            background-color: #555a5a;
            color: white;
            border-radius: 4px 4px 0 0;
            padding: 8px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .ui-datepicker-title {
            text-align: center;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex: 1;
        }

        /* ✅ MONTH/YEAR DROPDOWN STYLES */
        .ui-datepicker-month,
        .ui-datepicker-year {
            background-color: white !important;
            color: #333 !important;
            border: 1px solid #ddd !important;
            border-radius: 4px !important;
            padding: 6px 8px !important;
            font-size: 13px !important;
            cursor: pointer !important;
            z-index: 99999 !important;
            position: relative !important;
            font-weight: 500 !important;
            min-width: 70px !important;
            text-align: center !important;
            user-select: none !important;
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            appearance: menulist !important;
            -webkit-appearance: menulist !important;
            -moz-appearance: menulist !important;
            box-sizing: border-box !important;
        }

        .ui-datepicker-month:hover,
        .ui-datepicker-year:hover {
            background-color: #f0f0f0 !important;
            border-color: #00b5ad !important;
            box-shadow: 0 2px 4px rgba(0, 181, 173, 0.2) !important;
        }

        .ui-datepicker-month:focus,
        .ui-datepicker-year:focus {
            outline: none !important;
            border-color: #00b5ad !important;
            box-shadow: 0 0 5px rgba(0, 181, 173, 0.3) !important;
        }

        .ui-datepicker-prev, 
        .ui-datepicker-next {
            background-color: transparent;
            border: none;
            cursor: pointer;
            color: white;
            font-weight: bold;
            padding: 4px 8px;
            z-index: 99999 !important;
        }

        .ui-datepicker-prev:hover, 
        .ui-datepicker-next:hover {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .ui-datepicker-calendar {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .ui-datepicker-calendar th {
            background-color: #f5f5f5;
            color: #333;
            padding: 6px;
            text-align: center;
            font-weight: 600;
            border-bottom: 1px solid #ddd;
        }

        .ui-datepicker-calendar td {
            padding: 4px;
            text-align: center;
            border: none;
        }

        .ui-datepicker-calendar a {
            display: block;
            padding: 6px;
            text-decoration: none;
            color: #333;
            border-radius: 3px;
            transition: all 0.2s ease;
            cursor: pointer !important;
        }

        .ui-datepicker-calendar a:hover {
            background-color: #00b5ad;
            color: white;
            font-weight: bold;
        }

        .ui-datepicker-calendar .ui-state-default {
            background-color: white;
            color: #333;
        }

        .ui-datepicker-calendar .ui-state-active {
            background-color: #00b5ad;
            color: white;
            font-weight: bold;
        }

        .ui-datepicker-calendar .ui-state-highlight {
            background-color: #e8f5f4;
            color: #00b5ad;
        }

        .ui-datepicker-calendar .ui-state-disabled {
            color: #ccc;
            cursor: not-allowed;
        }

        .ui-datepicker-buttonpane {
            background-color: #f9f9f9;
            border-top: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            border-radius: 0 0 4px 4px;
        }

        .ui-datepicker-buttonpane button {
            background-color: #00b5ad;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 3px;
            cursor: pointer;
            font-weight: 600;
            margin: 0 4px;
            transition: background-color 0.2s ease;
        }

        .ui-datepicker-buttonpane button:hover {
            background-color: #008b87;
        }

        /* ✅ FIX: Modal overflow hidden issue */
        .modal-body {
            overflow: visible !important;
            position: relative !important;
        }

        .modal {
            overflow: visible !important;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            width: 100%;
        }

        body {
            display: flex;
            flex-direction: column;
        }

        #wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
        }

        #page-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            padding-bottom: 0;
        }

        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 100;
            flex-shrink: 0;
            background-color: white;
            border-top: 1px solid #ddd;
        }

        #page-wrapper {
            padding-bottom: 20px;
        }

        .full-loader {
            position: fixed !important;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(50, 50, 50, 0.8) !important;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .full-loader > .loader {
            display: flex !important;
            height: 58px;
            flex-direction: column-reverse;
            width: fit-content;
            font-size: 17px;
        }

        /* ✅ ACTION BUTTONS */
        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: center;
        }

        .action-btn {
            background: transparent !important;
            border: none !important;
            padding: 4px 6px !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 14px !important;
            min-width: auto !important;
            height: auto !important;
        }

        .action-btn.view-btn {
            color: #007bff !important;
        }

        .action-btn.view-btn:hover {
            color: #0056b3 !important;
            transform: scale(1.15) !important;
        }

        .action-btn.edit-btn {
            color: #007bff !important;
        }

        .action-btn.edit-btn:hover {
            color: #0056b3 !important;
            transform: scale(1.15) !important;
        }

        .action-btn i.fa {
            font-size: 14px !important;
            margin: 0 !important;
        }

        /* ✅ FILTER SECTION WITH PROPER SPACING - MATCHING document_details.php */
        .filter-section {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-bottom: 12px;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
            font-size: 12px;
        }

        .filter-select,
        .filter-input {
            padding: 8px 8px !important;
            border: 1px solid #ddd !important;
            border-radius: 4px !important;
            font-size: 12px !important;
            font-family: Arial, sans-serif !important;
            height: 38px !important;
            line-height: 1.5 !important;
            box-sizing: border-box !important;
            background-color: white !important;
            transition: all 0.3s ease !important;
            width: 100% !important;
        }

        .filter-select {
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2300b5ad' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 8px center !important;
            background-size: 20px !important;
            padding-right: 30px !important;
            cursor: pointer !important;
        }

        .filter-input {
            cursor: text !important;
        }

        .filter-select:hover,
        .filter-input:hover {
            border-color: #00b5ad !important;
            box-shadow: 0 2px 6px rgba(0, 181, 173, 0.1) !important;
        }

        .filter-select:focus,
        .filter-input:focus {
            outline: none !important;
            border-color: #00b5ad !important;
            box-shadow: 0 0 8px rgba(0, 181, 173, 0.3) !important;
        }

        /* ✅ FILTER BUTTONS - SAME HEIGHT AS INPUTS */
        .filter-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
            width: 100%;
            height: 100%;
        }

        .btn-filter,
        .btn-clear {
            padding: 0 16px !important;
            border: none !important;
            border-radius: 4px !important;
            cursor: pointer !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
            white-space: nowrap !important;
            height: 38px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex: 1;
            line-height: 1 !important;
        }

        .btn-filter {
            background-color: #00b5ad !important;
            color: white !important;
        }

        .btn-filter:hover {
            background-color: #008b87 !important;
        }

        .btn-clear {
            background-color: #e0e1e2 !important;
            color: rgba(0, 0, 0, .6) !important;
        }

        .btn-clear:hover {
            background-color: #939ca3ff !important;
        }

        /* ✅ TABLE STYLES */
        #resultsTable {
            width: 100% !important;
            margin: 0 !important;
            font-size: 12px !important;
        }

        #resultsTable thead th {
            text-align: center !important;
            vertical-align: middle !important;
            font-size: 12px !important;
            background-color: #f8f9fa;
            font-weight: 600;
        }

        #resultsTable tbody td {
            text-align: center !important;
            vertical-align: middle !important;
            font-size: 12px !important;
        }

        .card {
            width: 100%;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
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

        .dataTables_wrapper {
            padding: 0 !important;
            width: 100%;
            position: relative;
        }

        .dataTables_wrapper .dataTables_length {
            float: left;
            font-size: 12px !important;
            margin: 0 !important;
            padding: 10px 0 !important;
        }

        .dataTables_wrapper .dataTables_length select {
            height: 28px !important;
            padding: 2px 8px !important;
            font-size: 12px !important;
            border: 1px solid #ddd !important;
            border-radius: 4px !important;
        }

        .dataTables_wrapper .dataTables_filter {
            float: right;
            font-size: 12px !important;
            margin: 0 !important;
            padding: 10px 0 !important;
        }

        .dataTables_wrapper .dataTables_filter input {
            height: 28px !important;
            padding: 2px 8px !important;
            font-size: 12px !important;
            margin-left: 8px !important;
            border: 1px solid #ddd !important;
            border-radius: 4px !important;
        }

        .dataTables_wrapper .dataTables_info {
            float: left;
            font-size: 12px !important;
            padding: 10px 0 !important;
            margin: 0 !important;
            clear: both;
        }

        .dataTables_wrapper .dataTables_paginate {
            float: right;
            font-size: 12px !important;
            padding: 10px 0 !important;
            margin: 0 !important;
        }

        .dataTables_processing {
            position: absolute;
            top: 50%;
            left: 50%;
            width: auto;
            height: auto;
            margin-left: -50%;
            margin-top: -30px;
            padding: 20px 40px;
            text-align: center;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(5px) !important;
            -webkit-backdrop-filter: blur(5px) !important;
            z-index: 1000;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 181, 173, 0.3);
            border: 2px solid #00b5ad;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .dataTables_processing::after {
            content: 'Loading...';
            display: block;
            font-weight: 600;
            color: #00b5ad;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        .dataTables_processing > div {
            display: none;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 6px 10px !important;
            margin: 0 2px !important;
            border: 1px solid #e0e0e0 !important;
            border-radius: 3px !important;
            cursor: pointer !important;
            background-color: white !important;
            color: #666 !important;
            font-size: 12px !important;
            font-weight: 500 !important;
            transition: all 0.3s ease !important;
            display: inline-block !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled) {
            background-color: #f5f5f5 !important;
            border-color: #bbb !important;
            color: #333 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: #00b5ad !important;
            color: white !important;
            border-color: #00b5ad !important;
            font-weight: 600 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background-color: #008b87 !important;
            border-color: #008b87 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            background-color: #f9f9f9 !important;
            border-color: #e0e0e0 !important;
            color: #999 !important;
            cursor: not-allowed !important;
            opacity: 0.5 !important;
        }

        #exportButtonsContainer {
            display: inline-flex !important;
            gap: 5px !important;
            align-items: center !important;
            margin: 0 10px !important;
            float: left !important;
            padding: 10px 0 !important;
        }

        .export-btn {
            padding: 5px 12px !important;
            font-size: 11px !important;
            border: none !important;
            border-radius: 3px !important;
            cursor: pointer !important;
            font-weight: 600 !important;
            transition: background-color 0.3s ease !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
            white-space: nowrap !important;
            color: white !important;
            height: 28px !important;
        }

        .export-btn-excel {
            background-color: #28a745 !important;
        }

        .export-btn-excel:hover {
            background-color: #218838 !important;
        }

        .export-btn-pdf {
            background-color: #dc3545 !important;
        }

        .export-btn-pdf:hover {
            background-color: #c82333 !important;
        }

        .export-btn i {
            font-size: 12px !important;
        }

        .row {
            margin: 15px 0;
        }

        .col-md-12 {
            padding: 0 15px;
        }

        .dataTables_scroll {
            overflow: auto;
            max-height: 75vh;
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

        .dataTables_wrapper::after {
            content: "";
            display: table;
            clear: both;
        }

        /* ✅ EDIT MODAL STYLES - OPTIMIZED FOR NO SCROLL */
        .edit-modal-overlay {
            display: none;
            position: fixed;
            z-index: 9998;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            padding: 20px;
            overflow-y: auto;
        }

        .edit-modal-overlay.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .edit-modal {
            display: none;
            position: relative;
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 900px;
            flex-direction: column;
            z-index: 9999;
            animation: slideDown 0.3s ease-out;
            margin: auto;
            max-height: 90vh;
            display: none;
        }

        .edit-modal.show {
            display: flex;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .edit-modal-header {
            background: white;
            color: #333;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
            flex-shrink: 0;
            border-radius: 8px 8px 0 0;
        }

        .edit-modal-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            color: #333;
        }

        .edit-modal-close-btn {
            background-color: transparent;
            color: #999;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 24px;
            font-weight: 300;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
        }

        .edit-modal-close-btn:hover {
            color: #333;
            background-color: #f5f5f5;
        }

        .edit-modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 25px;
            background: white;
        }

        .edit-modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .edit-modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .edit-modal-body::-webkit-scrollbar-thumb {
            background: #00b5ad;
            border-radius: 4px;
        }

        .edit-modal-body::-webkit-scrollbar-thumb:hover {
            background: #008b87;
        }

        /* ✅ FORM ROW LAYOUT - 3 COLUMNS */
        .edit-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .edit-form-row.full {
            grid-template-columns: 1fr;
        }

        .edit-form-row.two-col {
            grid-template-columns: 1fr 1fr;
        }

        .edit-form-group {
            display: flex;
            flex-direction: column;
        }

        .edit-form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 13px;
            display: block;
        }

        .edit-form-group label .required {
            color: #dc3545;
            margin-left: 2px;
        }

        .edit-form-input,
        .edit-form-select,
        .edit-form-textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: white;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .edit-form-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2300b5ad' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 18px;
            padding-right: 36px;
            cursor: pointer;
        }

        .edit-form-textarea {
            resize: vertical;
            min-height: 60px;
        }

        .edit-form-input:hover,
        .edit-form-select:hover,
        .edit-form-textarea:hover {
            border-color: #00b5ad;
            box-shadow: 0 2px 6px rgba(0, 181, 173, 0.1);
        }

        .edit-form-input:focus,
        .edit-form-select:focus,
        .edit-form-textarea:focus {
            outline: none;
            border-color: #00b5ad;
            box-shadow: 0 0 8px rgba(0, 181, 173, 0.3);
        }

        .edit-form-input:disabled,
        .edit-form-select:disabled {
            background-color: #f5f5f5;
            color: #999;
            cursor: not-allowed;
        }

        /* ✅ UPLOAD AREA */
        .edit-upload-area {
            border: 2px dashed #00b5ad;
            border-radius: 4px;
            padding: 15px;
            text-align: center;
            background-color: #f9f9f9;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 10px;
        }

        .edit-upload-area:hover {
            background-color: #f0f9f9;
            border-color: #008b87;
        }

        .edit-upload-area.dragover {
            background-color: #e8f5f4;
            border-color: #008b87;
        }

        .edit-upload-area i {
            font-size: 24px;
            color: #00b5ad;
            margin-bottom: 8px;
            display: block;
        }

        .edit-upload-area p {
            margin: 0;
            color: #333;
            font-weight: 600;
            font-size: 13px;
        }

        .edit-upload-area small {
            display: block;
            color: #999;
            margin-top: 5px;
            font-size: 11px;
        }

        .edit-upload-input {
            display: none;
        }

        .edit-file-info {
            font-size: 12px;
            color: #666;
            margin-top: 8px;
            padding: 8px;
            background-color: #f5f5f5;
            border-radius: 4px;
            display: none;
        }

        .edit-file-info.show {
            display: block;
        }

        .error-message {
            color: #dc3545;
            font-size: 11px;
            margin-top: 4px;
        }

        /* ✅ MODAL FOOTER */
        .edit-modal-footer {
            background: white;
            padding: 15px 25px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            border-top: 1px solid #dee2e6;
            flex-shrink: 0;
            border-radius: 0 0 8px 8px;
        }

        .btn-save,
        .btn-cancel {
            padding: 10px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 110px;
            justify-content: center;
        }

        .btn-save {
            background-color: #00b5ad;
            color: white;
            box-shadow: 0 2px 4px rgba(0, 181, 173, 0.2);
        }

        .btn-save:hover {
            background-color: #008b87;
            box-shadow: 0 4px 8px rgba(0, 181, 173, 0.3);
            transform: translateY(-1px);
        }

        .btn-save:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 181, 173, 0.2);
        }

        .btn-save:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            box-shadow: none;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-cancel:hover {
            background-color: #5a6268;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            transform: translateY(-1px);
        }

        .btn-cancel:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* ✅ DOCUMENT VIEWER MODAL STYLES */
        .document-viewer-modal-overlay {
            display: none;
            position: fixed;
            z-index: 9998;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
            padding: 20px;
        }

        .document-viewer-modal-overlay.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .document-viewer-modal {
            display: none;
            position: relative;
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 1200px;
            height: 90vh;
            flex-direction: column;
            z-index: 9999;
            animation: slideDown 0.3s ease-out;
            margin: auto;
        }

        .document-viewer-modal.show {
            display: flex;
        }

        .document-viewer-header {
            background: linear-gradient(135deg, #00b5ad 0%, #008b87 100%);
            color: white;
            padding: 12px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px 8px 0 0;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            gap: 15px;
        }

        .document-viewer-title {
            font-size: 15px;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }

        .document-viewer-controls {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-end;
            flex-shrink: 0;
        }

        .document-control-btn {
            padding: 5px 10px;
            border: none;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 3px;
            white-space: nowrap;
        }

        .document-control-btn:hover:not(:disabled) {
            background-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }

        .document-control-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .document-page-info {
            font-size: 11px;
            color: white;
            margin: 0 6px;
            white-space: nowrap;
            font-weight: 600;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 3px 8px;
            border-radius: 4px;
        }

        .document-close-btn {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .document-close-btn:hover {
            background-color: rgba(255, 100, 100, 0.3);
            transform: translateY(-1px);
        }

        .document-download-btn {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .document-download-btn:hover {
            background-color: rgba(76, 175, 80, 0.3);
            transform: translateY(-1px);
        }

        .document-viewer-container {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 20px;
            background: #f5f5f5;
            width: 100%;
        }

        .document-pages-wrapper {
            display: flex;
            flex-direction: column;
            gap: 15px;
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
        }

        .document-page-container {
            position: relative;
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            border-radius: 4px;
            overflow: hidden;
            animation: slideUp 0.3s ease-out;
            margin-left: auto;
            margin-right: auto;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .document-canvas {
            display: block;
            width: 100%;
            height: auto;
            position: relative;
            z-index: 1;
        }

        .document-watermark-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            z-index: 2;
        }

        .document-watermark-text {
            font-size: 60px;
            font-weight: bold;
            color: rgba(200, 200, 200, 0.25);
            transform: rotate(-45deg);
            white-space: nowrap;
            text-align: center;
            font-family: Arial, sans-serif;
            letter-spacing: 2px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .document-page-number {
            position: absolute;
            bottom: 8px;
            right: 12px;
            font-size: 10px;
            color: #999;
            background: rgba(0, 0, 0, 0.05);
            padding: 3px 6px;
            border-radius: 3px;
            z-index: 3;
        }

        .document-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 15px;
            color: #666;
            padding: 40px;
            width: 100%;
        }

        .document-loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e0e0e0;
            border-top: 3px solid #00b5ad;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        .document-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 4px;
            text-align: center;
            margin: 20px;
            font-weight: 600;
            border: 1px solid #f5c6cb;
        }

        .document-viewer-container::-webkit-scrollbar {
            width: 8px;
        }

        .document-viewer-container::-webkit-scrollbar-track {
            background: #e0e0e0;
            border-radius: 4px;
        }

        .document-viewer-container::-webkit-scrollbar-thumb {
            background: #00b5ad;
            border-radius: 4px;
        }

        .document-viewer-container::-webkit-scrollbar-thumb:hover {
            background: #008b87;
        }

        .watermark-badge {
            display: inline-block;
            background-color: #ffc107;
            color: #333;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            margin-left: 10px;
            border: 1px solid #ff9800;
        }

        /* ✅ PPT SLIDE VIEWER */
        .ppt-slide-container {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #f5f5f5;
        }

        .ppt-slide {
            background: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            border-radius: 4px;
            margin: 20px auto;
            max-width: 900px;
            width: 100%;
        }

        .ppt-slide img {
            width: 100%;
            height: auto;
            display: block;
        }

        @media (max-width: 1024px) {
            .filter-row {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }

            .document-viewer-modal {
                max-width: 95%;
                height: 88vh;
            }

            .document-pages-wrapper {
                max-width: 100%;
            }

            .edit-modal {
                max-width: 95%;
            }

            .edit-form-row {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .filter-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter,
            #exportButtonsContainer {
                float: none !important;
                margin: 5px 0 !important;
                clear: both;
            }

            #exportButtonsContainer {
                justify-content: center;
            }

            .document-viewer-modal {
                width: 98%;
                height: 92vh;
                max-width: 100%;
            }

            .document-watermark-text {
                font-size: 40px;
            }

            .document-viewer-header {
                flex-direction: column;
                gap: 8px;
                align-items: flex-start;
            }

            .document-viewer-controls {
                width: 100%;
                justify-content: space-between;
            }

            .document-viewer-title {
                width: 100%;
            }

            .document-viewer-modal-overlay {
                padding: 10px;
            }

            .edit-modal {
                width: 95%;
                max-width: 100%;
            }

            .edit-modal-overlay {
                padding: 10px;
            }

            .edit-form-row {
                grid-template-columns: 1fr;
            }

            .edit-modal-footer {
                flex-direction: column-reverse;
            }

            .btn-save,
            .btn-cancel {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php $activePage = '/fac_details.php'; ?>
    <?php include_once '../shared/sidebar.php'; ?>
    
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom" style="position: relative; flex-shrink: 0;">
            <div class="ui fixed menu" style="padding: 21px; color:teal; width: 100%;">
                <div class="ui container" style="position: relative; width: 100%;">
                    <div style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); display: flex; align-items: center;">
                        <a href="/" style="display: flex; align-items: center; text-decoration: none;">
                            <div style="margin-right: 10px;">
                                <img src="/images/onex_icon.png" width="25" height="36" class="logo-icon">
                            </div>
                            <div class="logo-text">
                                <h5 style="margin: 0; font-size: 18px; line-height: 1.2; background-color: #dc3545; color: white;">
                                    OneX 
                                </h5>
                                <p style="margin: 0; text-transform: uppercase; font-size: 10px; color: #6c757d; line-height: 1.2;">DMS</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="ui inverted segment full-loader" style="display: none">
            <div class="ui active inverted loader">Loading</div>
        </div>

        <!-- ✅ FILTER SECTION - MATCHING document_details.php -->
        <div class="row">
            <div class="col-md-12">
                <div class="filter-section">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="filterDepartment">Department</label>
                            <select id="filterDepartment" class="form-control filter-select">
                                <option value="">-- Select Department --</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filterIssueDate">Issue Date Range</label>
                            <input type="text" id="filterIssueDate" class="form-control filter-input" placeholder="Select date range">
                        </div>

                        <div class="filter-group">
                            <label for="filterExpiryDate">Next Review Date Range</label>
                            <input type="text" id="filterExpiryDate" class="form-control filter-input" placeholder="Select date range">
                        </div>

                        <div class="filter-group">
                            <div class="filter-buttons">
                                <button type="button" class="btn-filter" id="applyFiltersBtn">Apply Filters</button>
                                <button type="button" class="btn-clear" id="clearFiltersBtn">Clear Filters</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ FAC CERTIFICATES TABLE -->
        <div class="row">
            <div class="col-md-12" style="margin-top: -40px;">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive" style="width: 100%; overflow: visible;">
                            <table id="resultsTable" class="table table-striped table-bordered" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Issue Date</th>
                                        <th>Next Review Date</th>
                                        <th>Remark</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php $footer_display = 'FAC (Function Allocation Chart)';
    include_once '../../assemblynotes/shared/footer.php'; ?>
</div>

<!-- ✅ EDIT MODAL - WITH JQUERY UI DATEPICKER -->
<div id="editModalOverlay" class="edit-modal-overlay">
    <div id="editModal" class="edit-modal">
        <div class="edit-modal-header">
            <h2 class="edit-modal-title">Edit FAC Certificate</h2>
            <button class="edit-modal-close-btn" id="editModalCloseBtn" title="Close">×</button>
        </div>
        <div class="edit-modal-body">
            <form id="editCertificateForm">
                <input type="hidden" id="editCertificateId" value="">

                <!-- Row 1: Department, Issue Date, Expiry Date -->
                <div class="edit-form-row">
                    <div class="edit-form-group">
                        <label for="editDepartment">Department <span class="required">*</span></label>
                        <select id="editDepartment" class="edit-form-select" required>
                            <option value="">-- Select Department --</option>
                        </select>
                    </div>

                    <div class="edit-form-group">
                        <label for="editIssueDate">Issue Date <span class="required">*</span></label>
                        <input type="text" id="editIssueDate" class="edit-form-input date-input" placeholder="DD/MM/YYYY" required autocomplete="off">
                        <div class="error-message" id="editIssueDateError"></div>
                    </div>

                    <div class="edit-form-group">
                        <label for="editExpiryDate">Next Review Date <span class="required">*</span></label>
                        <input type="text" id="editExpiryDate" class="edit-form-input date-input" placeholder="DD/MM/YYYY" required autocomplete="off">
                        <div class="error-message" id="editExpiryDateError"></div>
                    </div>
                </div>

                <!-- Row 2: Upload Area (Full Width) -->
                <div class="edit-form-row full">
                    <div class="edit-form-group">
                        <label>Upload Document (PDF or PPT) <span class="required">*</span></label>
                        <div class="edit-upload-area" id="uploadArea">
                            <i class="fa fa-cloud-upload"></i>
                            <p>Click to upload or drag and drop</p>
                            <small>Supported formats: PDF (.pdf), PowerPoint (.ppt, .pptx) - Max 50MB</small>
                        </div>
                        <input type="file" id="editFileUpload" class="edit-upload-input" accept=".pdf,.ppt,.pptx" />
                        <div class="edit-file-info" id="fileInfo"></div>
                        <div class="error-message" id="editDocumentFileError"></div>
                    </div>
                </div>

                <!-- Row 3: Remark (Full Width) -->
                <div class="edit-form-row full">
                    <div class="edit-form-group">
                        <label for="editRemark">Remark</label>
                        <textarea id="editRemark" class="edit-form-textarea" placeholder="Enter remark"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="edit-modal-footer">
            <button type="button" class="btn-cancel" id="editModalCancelBtn">
                Close
            </button>
            <button type="button" class="btn-save" id="editModalSaveBtn">
                Update
            </button>
        </div>
    </div>
</div>

<!-- ✅ DOCUMENT VIEWER MODAL WITH WATERMARK -->
<div id="documentViewerOverlay" class="document-viewer-modal-overlay">
    <div id="documentViewerModal" class="document-viewer-modal">
        <div class="document-viewer-header">
            <h3 class="document-viewer-title">
                <i class="fa fa-file-pdf-o" id="viewerIcon"></i>
                <span id="documentViewerTitle">Document Viewer</span>
                <span id="watermarkIndicator" class="watermark-badge" style="display: none;">
                    <i class="fa fa-shield"></i> CONTROLLED COPY
                </span>
            </h3>
            <div class="document-viewer-controls">
                <button class="document-control-btn" id="docFirstBtn" title="First Page" style="display:none;">
                    <i class="fa fa-step-backward"></i> First
                </button>
                <button class="document-control-btn" id="docPrevBtn" title="Previous Page" style="display:none;">
                    <i class="fa fa-chevron-left"></i> Prev
                </button>
                <span class="document-page-info" id="pageInfo" style="display:none;">
                    <span id="docCurrentPage">1</span> / <span id="docTotalPages">1</span>
                </span>
                <button class="document-control-btn" id="docNextBtn" title="Next Page" style="display:none;">
                    Next <i class="fa fa-chevron-right"></i>
                </button>
                <button class="document-control-btn" id="docLastBtn" title="Last Page" style="display:none;">
                    Last <i class="fa fa-step-forward"></i>
                </button>
                <button class="document-control-btn" id="docZoomInBtn" title="Zoom In" style="display:none;">
                    <i class="fa fa-plus"></i>
                </button>
                <button class="document-control-btn" id="docZoomOutBtn" title="Zoom Out" style="display:none;">
                    <i class="fa fa-minus"></i>
                </button>
                <button class="document-download-btn" id="docDownloadBtn" title="Download" style="display:flex;">
                    <i class="fa fa-download"></i> Download
                </button>
                <button class="document-close-btn" id="docCloseBtn">
                    <i class="fa fa-times"></i> Close
                </button>
            </div>
        </div>
        <div class="document-viewer-container" id="documentViewerContainer">
            <div class="document-loading" id="document-loading">
                <div class="document-loading-spinner"></div>
                <p>Loading document...</p>
            </div>
            <div id="documentContentWrapper" style="width:100%; height:100%; display:none; flex-direction:column; align-items:center; overflow-y: auto;">
            </div>
        </div>
    </div>
</div>

<script>
    // Set user permissions from PHP
    const userPermissions = {
        hasEditRights: <?php echo json_encode($hasEditRights); ?>,
        hasAdminRights: <?php echo json_encode($hasAdminRights); ?>,
        hasViewRights: <?php echo json_encode($hasViewRights); ?>
    };

    // Initialize PDF.js worker
    if (typeof pdfjsLib !== 'undefined') {
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    }

    let currentFilters = {
        department: '',
        issue_date_from: '',
        issue_date_to: '',
        expiry_date_from: '',
        expiry_date_to: ''
    };
    let dataTable = null;
    let selectedFile = null;

    // ✅ DOCUMENT VIEWER STATE
    let viewerState = {
        pdfDoc: null,
        currentPage: 1,
        totalPages: 0,
        scale: 1.2,
        isLoading: false,
        blobUrl: null,
        fileName: '',
        fileType: '',
        pageCache: {},
        documentId: null,
        applyWatermark: false,
        hasAdminRights: false
    };

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

    function escapeSpecialChars(str) {
        if (!str) return '';
        return str.replace(/[%_\\]/g, '\\$&');
    }

    // ✅ HELPER FUNCTIONS
    function formatDateToDDMMYYYY(date) {
        if (!date) return '';
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        return `${day}/${month}/${year}`;
    }

    // ✅ FILE UPLOAD HANDLING
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('editFileUpload');

    uploadArea.addEventListener('click', () => fileInput.click());

    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });

    function handleFileSelect(file) {
        const allowedTypes = ['application/pdf', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
        const maxSize = 50 * 1024 * 1024; // 50MB

        document.getElementById('editDocumentFileError').textContent = '';

        if (!allowedTypes.includes(file.type)) {
            document.getElementById('editDocumentFileError').textContent = 'Invalid file type. Please upload PDF or PPT/PPTX files only.';
            document.getElementById('fileInfo').classList.remove('show');
            selectedFile = null;
            return;
        }

        if (file.size > maxSize) {
            document.getElementById('editDocumentFileError').textContent = 'File size exceeds 50MB limit.';
            document.getElementById('fileInfo').classList.remove('show');
            selectedFile = null;
            return;
        }

        selectedFile = file;
        const fileInfo = document.getElementById('fileInfo');
        fileInfo.classList.add('show');
        fileInfo.innerHTML = `
            <i class="fa fa-check-circle" style="color: #28a745;"></i>
            <strong>${file.name}</strong> (${(file.size / 1024 / 1024).toFixed(2)} MB)
        `;
    }

    // ✅ OPEN EDIT MODAL
    function openEditModal(certificateId) {
        showFullLoader();
        selectedFile = null;
        document.getElementById('editFileUpload').value = '';
        document.getElementById('fileInfo').classList.remove('show');
        document.getElementById('editDocumentFileError').textContent = '';
        document.getElementById('editIssueDateError').textContent = '';
        document.getElementById('editExpiryDateError').textContent = '';

        $.ajax({
            url: '../api/documentMasterAPI.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'getFac',
                id: certificateId
            },
            success: function(response) {
                hideFullLoader();

                if (response.success) {
                    const certificate = response.data;

                    // Populate form
                    $('#editCertificateId').val(certificate.id);
                    $('#editDepartment').val(certificate.department);
                    
                    // ✅ Format dates to DD/MM/YYYY
                    const issueDateFormatted = formatDateToDDMMYYYY(certificate.issue_date);
                    const expiryDateFormatted = formatDateToDDMMYYYY(certificate.expiry_date);
                    
                    $('#editIssueDate').val(issueDateFormatted);
                    $('#editExpiryDate').val(expiryDateFormatted);
                    $('#editRemark').val(certificate.remark);

                    // Show modal
                    document.getElementById('editModalOverlay').classList.add('show');
                    document.getElementById('editModal').classList.add('show');
                    
                    // ✅ INITIALIZE DATE PICKERS AFTER MODAL SHOWS
                    setTimeout(() => {
                        initializeEditIssueDatePicker();
                        initializeEditExpiryDatePicker();
                    }, 500);
                } else {
                    toastr.error(response.message || 'Failed to load certificate');
                }
            },
            error: function(xhr, status, error) {
                hideFullLoader();
                console.error('Error:', error);
                toastr.error('Error loading certificate');
            }
        });
    }

    // ✅ INITIALIZE ISSUE DATE PICKER
    function initializeEditIssueDatePicker() {
        const dateInput = $('#editIssueDate');
        
        if (dateInput.hasClass('hasDatepicker')) {
            dateInput.datepicker('destroy');
        }
        
        try {
            dateInput.datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                changeYear: true,
                yearRange: '-100:+10',
                onSelect: function(dateText) {
                    console.log('✓ Issue Date selected:', dateText);
                    document.getElementById('editIssueDateError').textContent = '';
                },
                beforeShow: function(input, inst) {
                    setTimeout(function() {
                        inst.dpDiv.css('z-index', 99999);
                    }, 0);
                }
            });
            
            console.log('✓ Issue Date Datepicker initialized successfully');
        } catch (e) {
            console.error('✗ Issue Date Datepicker initialization error:', e.message);
        }
    }

    // ✅ INITIALIZE EXPIRY DATE PICKER
    function initializeEditExpiryDatePicker() {
        const dateInput = $('#editExpiryDate');
        
        if (dateInput.hasClass('hasDatepicker')) {
            dateInput.datepicker('destroy');
        }
        
        try {
            dateInput.datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                changeYear: true,
                yearRange: '-100:+10',
                onSelect: function(dateText) {
                    console.log('✓ Expiry Date selected:', dateText);
                    document.getElementById('editExpiryDateError').textContent = '';
                },
                beforeShow: function(input, inst) {
                    setTimeout(function() {
                        inst.dpDiv.css('z-index', 99999);
                    }, 0);
                }
            });
            
            console.log('✓ Expiry Date Datepicker initialized successfully');
        } catch (e) {
            console.error('✗ Expiry Date Datepicker initialization error:', e.message);
        }
    }

    // ✅ CLOSE EDIT MODAL
    function closeEditModal() {
        document.getElementById('editModalOverlay').classList.remove('show');
        document.getElementById('editModal').classList.remove('show');
        $('#editCertificateForm')[0].reset();
        selectedFile = null;
        document.getElementById('fileInfo').classList.remove('show');
        document.getElementById('editDocumentFileError').textContent = '';
        document.getElementById('editIssueDateError').textContent = '';
        document.getElementById('editExpiryDateError').textContent = '';
        
        if ($('#editIssueDate').hasClass('hasDatepicker')) {
            $('#editIssueDate').datepicker('destroy');
        }
        if ($('#editExpiryDate').hasClass('hasDatepicker')) {
            $('#editExpiryDate').datepicker('destroy');
        }
    }

    // ✅ SAVE CERTIFICATE CHANGES
    function saveCertificateChanges() {
        document.getElementById('editDocumentFileError').textContent = '';
        document.getElementById('editIssueDateError').textContent = '';
        document.getElementById('editExpiryDateError').textContent = '';

        const certificateId = $('#editCertificateId').val();
        const department = $('#editDepartment').val();
        const issueDateValue = $('#editIssueDate').val().trim();
        const expiryDateValue = $('#editExpiryDate').val().trim();
        const remark = $('#editRemark').val();

        let isValid = true;

        if (!department) {
            isValid = false;
            toastr.error('Please select a department');
        }

        // ✅ Validate Issue Date
        if (!issueDateValue) {
            isValid = false;
            document.getElementById('editIssueDateError').textContent = 'Issue Date is required';
        } else {
            const dateRegex = /^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[012])\/\d{4}$/;
            if (!dateRegex.test(issueDateValue)) {
                isValid = false;
                document.getElementById('editIssueDateError').textContent = 'Invalid date format. Use DD/MM/YYYY';
            }
        }

        // ✅ Validate Expiry Date
        if (!expiryDateValue) {
            isValid = false;
            document.getElementById('editExpiryDateError').textContent = 'Next Review Date is required';
        } else {
            const dateRegex = /^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[012])\/\d{4}$/;
            if (!dateRegex.test(expiryDateValue)) {
                isValid = false;
                document.getElementById('editExpiryDateError').textContent = 'Invalid date format. Use DD/MM/YYYY';
            }
        }

        if (!selectedFile) {
            isValid = false;
            document.getElementById('editDocumentFileError').textContent = 'Document file is required';
        }

        if (!isValid) {
            toastr.error('Please fill all required fields');
            return;
        }

        showFullLoader();

        // ✅ Convert DD/MM/YYYY to YYYY-MM-DD for database
        const issueDateParts = issueDateValue.split('/');
        const issueDateForDB = `${issueDateParts[2]}-${issueDateParts[1]}-${issueDateParts[0]}`;

        const expiryDateParts = expiryDateValue.split('/');
        const expiryDateForDB = `${expiryDateParts[2]}-${expiryDateParts[1]}-${expiryDateParts[0]}`;

        const formData = new FormData();
        formData.append('action', 'updateFac');
        formData.append('id', certificateId);
        formData.append('department', department);
        formData.append('issue_date', issueDateForDB);
        formData.append('expiry_date', expiryDateForDB);
        formData.append('remark', remark);

        if (selectedFile) {
            formData.append('pdf_file', selectedFile);
        }

        console.log('✓ Update FormData prepared:');
        console.log('  - Certificate ID:', certificateId);
        console.log('  - Issue Date:', issueDateForDB);
        console.log('  - Expiry Date:', expiryDateForDB);
        console.log('  - File:', selectedFile ? selectedFile.name : 'None');

        $.ajax({
            url: '../api/documentMasterAPI.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                hideFullLoader();

                console.log('✓ Update Response:', response);

                if (response.success) {
                    toastr.success('FAC Certificate updated successfully');
                    closeEditModal();
                    dataTable.ajax.reload();
                } else {
                    toastr.error(response.message || 'Failed to update certificate');
                }
            },
            error: function(xhr, status, error) {
                hideFullLoader();
                console.error('✗ Error:', error);
                console.error('✗ Response:', xhr.responseText);
                toastr.error('Error updating certificate');
            }
        });
    }

    // ✅ OPEN DOCUMENT VIEWER
    function openDocumentViewer(documentId, fileName) {
        if (!documentId || !fileName) {
            toastr.error('Document information is missing');
            return;
        }

        const fileExtension = fileName.split('.').pop().toLowerCase();

        const documentLoading = document.getElementById('document-loading');
        const documentContentWrapper = document.getElementById('documentContentWrapper');

        documentContentWrapper.innerHTML = '';
        documentContentWrapper.style.display = 'none';
        documentLoading.style.display = 'flex';

        viewerState.currentPage = 1;
        viewerState.totalPages = 0;
        viewerState.fileName = fileName;
        viewerState.documentId = documentId;
        viewerState.pageCache = {};
        viewerState.fileType = fileExtension;
        viewerState.applyWatermark = false;

        document.getElementById('docFirstBtn').style.display = 'none';
        document.getElementById('docPrevBtn').style.display = 'none';
        document.getElementById('pageInfo').style.display = 'none';
        document.getElementById('docNextBtn').style.display = 'none';
        document.getElementById('docLastBtn').style.display = 'none';
        document.getElementById('docZoomInBtn').style.display = 'none';
        document.getElementById('docZoomOutBtn').style.display = 'none';
        document.getElementById('docDownloadBtn').style.display = 'flex';
        document.getElementById('viewerIcon').className = 'fa fa-file';

        const watermarkIndicator = document.getElementById('watermarkIndicator');
        watermarkIndicator.style.display = 'none';

        document.getElementById('documentViewerTitle').textContent = fileName;

        if (fileExtension === 'pdf') {
            document.getElementById('viewerIcon').className = 'fa fa-file-pdf-o';
        } else if (fileExtension === 'ppt' || fileExtension === 'pptx') {
            document.getElementById('viewerIcon').className = 'fa fa-file-powerpoint-o';
        }

        showFullLoader();

        const formData = new FormData();
        formData.append('action', 'viewDocumentFile');
        formData.append('document_id', documentId);

        fetch('../api/documentMasterAPI.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to load file');
            }
            return response.blob();
        })
        .then(blob => {
            hideFullLoader();

            const blobUrl = URL.createObjectURL(blob);
            viewerState.blobUrl = blobUrl;

            documentLoading.style.display = 'none';
            documentContentWrapper.style.display = 'flex';

            if (fileExtension === 'pdf') {
                loadPdfDocument(blobUrl, fileName);
            } else if (fileExtension === 'ppt' || fileExtension === 'pptx') {
                loadPptDocument(blobUrl, fileName);
            } else {
                showViewerError('Unsupported file format: ' + fileExtension);
            }

            document.getElementById('documentViewerOverlay').classList.add('show');
            document.getElementById('documentViewerModal').classList.add('show');
        })
        .catch(error => {
            hideFullLoader();
            console.error('Error:', error);
            toastr.error('Error loading file: ' + error.message);

            documentLoading.style.display = 'none';
            documentContentWrapper.style.display = 'flex';
            showViewerError('Error loading file: ' + error.message);

            document.getElementById('documentViewerOverlay').classList.add('show');
            document.getElementById('documentViewerModal').classList.add('show');
        });
    }

    // ✅ LOAD PDF DOCUMENT
    function loadPdfDocument(pdfUrl, fileName) {
        const pdfjsLib = window.pdfjsLib;

        document.getElementById('docFirstBtn').style.display = 'flex';
        document.getElementById('docPrevBtn').style.display = 'flex';
        document.getElementById('pageInfo').style.display = 'block';
        document.getElementById('docNextBtn').style.display = 'flex';
        document.getElementById('docLastBtn').style.display = 'flex';
        document.getElementById('docZoomInBtn').style.display = 'flex';
        document.getElementById('docZoomOutBtn').style.display = 'flex';
        document.getElementById('viewerIcon').className = 'fa fa-file-pdf-o';

        pdfjsLib.getDocument(pdfUrl).promise.then(pdf => {
            viewerState.pdfDoc = pdf;
            viewerState.totalPages = pdf.numPages;
            viewerState.currentPage = 1;

            document.getElementById('documentViewerTitle').textContent = fileName || 'PDF Document';
            document.getElementById('docCurrentPage').textContent = '1';
            document.getElementById('docTotalPages').textContent = viewerState.totalPages;

            renderAllPagesSequentially();
            updateDocumentNavigationButtons();
        }).catch(error => {
            console.error('Error loading PDF:', error);
            showViewerError('Failed to load PDF: ' + error.message);
        });
    }

    // ✅ LOAD PPT DOCUMENT
    function loadPptDocument(pptUrl, fileName) {
        document.getElementById('docFirstBtn').style.display = 'flex';
        document.getElementById('docPrevBtn').style.display = 'flex';
        document.getElementById('pageInfo').style.display = 'block';
        document.getElementById('docNextBtn').style.display = 'flex';
        document.getElementById('docLastBtn').style.display = 'flex';
        document.getElementById('viewerIcon').className = 'fa fa-file-powerpoint-o';

        const contentWrapper = document.getElementById('documentContentWrapper');
        contentWrapper.innerHTML = '';

        const pagesWrapper = document.createElement('div');
        pagesWrapper.className = 'document-pages-wrapper';
        contentWrapper.appendChild(pagesWrapper);

        // Simplified: Display message that PPT is loaded
        const message = document.createElement('div');
        message.style.cssText = 'padding: 40px; text-align: center; color: #666;';
        message.innerHTML = `
            <i class="fa fa-file-powerpoint-o" style="font-size: 48px; color: #00b5ad; margin-bottom: 20px; display: block;"></i>
            <h3>PowerPoint Presentation Loaded</h3>
            <p style="font-size: 12px; color: #999; margin-top: 20px;">
                Note: For full slide preview, please download the file or use PowerPoint viewer
            </p>
        `;
        pagesWrapper.appendChild(message);
    }

    // ✅ RENDER ALL PAGES SEQUENTIALLY
    function renderAllPagesSequentially() {
        const contentWrapper = document.getElementById('documentContentWrapper');
        contentWrapper.innerHTML = '';

        const pagesWrapper = document.createElement('div');
        pagesWrapper.className = 'document-pages-wrapper';
        pagesWrapper.id = 'documentPagesWrapper';

        contentWrapper.appendChild(pagesWrapper);
        renderPageSequentially(1, pagesWrapper);
    }

    // ✅ RENDER PAGE SEQUENTIALLY
    function renderPageSequentially(pageNum, container) {
        if (!viewerState.pdfDoc || pageNum > viewerState.totalPages) {
            return;
        }

        viewerState.pdfDoc.getPage(pageNum).then(page => {
            const viewport = page.getViewport({ scale: viewerState.scale });

            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            canvas.className = 'document-canvas';

            const renderContext = {
                canvasContext: context,
                viewport: viewport
            };

            page.render(renderContext).promise.then(() => {
                const pageContainer = document.createElement('div');
                pageContainer.className = 'document-page-container';
                pageContainer.style.position = 'relative';
                pageContainer.style.width = viewport.width + 'px';
                pageContainer.style.height = viewport.height + 'px';
                pageContainer.setAttribute('data-page-number', pageNum);

                pageContainer.appendChild(canvas);

                const pageNumDiv = document.createElement('div');
                pageNumDiv.className = 'document-page-number';
                pageNumDiv.textContent = 'Page ' + pageNum;
                pageContainer.appendChild(pageNumDiv);

                container.appendChild(pageContainer);
                viewerState.pageCache[pageNum] = true;

                if (pageNum < viewerState.totalPages) {
                    setTimeout(() => {
                        renderPageSequentially(pageNum + 1, container);
                    }, 50);
                }
            }).catch(error => {
                console.error('Error rendering page ' + pageNum + ':', error);
                if (pageNum < viewerState.totalPages) {
                    setTimeout(() => {
                        renderPageSequentially(pageNum + 1, container);
                    }, 50);
                }
            });
        }).catch(error => {
            console.error('Error getting page ' + pageNum + ':', error);
            if (pageNum < viewerState.totalPages) {
                setTimeout(() => {
                    renderPageSequentially(pageNum + 1, container);
                }, 50);
            }
        });
    }

    // ✅ UPDATE NAVIGATION BUTTONS
    function updateDocumentNavigationButtons() {
        const firstBtn = document.getElementById('docFirstBtn');
        const prevBtn = document.getElementById('docPrevBtn');
        const nextBtn = document.getElementById('docNextBtn');
        const lastBtn = document.getElementById('docLastBtn');

        firstBtn.disabled = viewerState.currentPage <= 1;
        prevBtn.disabled = viewerState.currentPage <= 1;
        nextBtn.disabled = viewerState.currentPage >= viewerState.totalPages;
        lastBtn.disabled = viewerState.currentPage >= viewerState.totalPages;
    }

    // ✅ SHOW VIEWER ERROR
    function showViewerError(message) {
        const contentWrapper = document.getElementById('documentContentWrapper');
        contentWrapper.innerHTML = '<div class="document-error"><i class="fa fa-exclamation-triangle"></i> ' + message + '</div>';
        contentWrapper.style.display = 'flex';
    }

    // ✅ CLOSE VIEWER
    function closeViewer() {
        document.getElementById('documentViewerOverlay').classList.remove('show');
        document.getElementById('documentViewerModal').classList.remove('show');

        if (viewerState.pdfDoc) {
            viewerState.pdfDoc.destroy();
            viewerState.pdfDoc = null;
        }

        if (viewerState.blobUrl) {
            URL.revokeObjectURL(viewerState.blobUrl);
            viewerState.blobUrl = null;
        }

        document.getElementById('documentContentWrapper').innerHTML = '';
        document.getElementById('documentContentWrapper').style.display = 'none';
        document.getElementById('document-loading').style.display = 'flex';

        viewerState.currentPage = 1;
        viewerState.totalPages = 0;
        viewerState.scale = 1.2;
        viewerState.pageCache = {};
        viewerState.fileType = '';
        viewerState.applyWatermark = false;
    }

    // ✅ DOWNLOAD DOCUMENT
    function downloadDocument() {
        if (!viewerState.documentId || !viewerState.fileName) {
            toastr.error('Document information is not available');
            return;
        }

        showFullLoader();

        const formData = new FormData();
        formData.append('action', 'downloadDocument');
        formData.append('document_id', viewerState.documentId);

        fetch('../api/documentMasterAPI.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Download failed');
            }
            return response.blob();
        })
        .then(blob => {
            hideFullLoader();
            downloadBlob(blob, viewerState.fileName);
            toastr.success('Document downloaded successfully');
        })
        .catch(error => {
            hideFullLoader();
            console.error('Download error:', error);
            toastr.error('Error downloading document: ' + error.message);
        });
    }

    // ✅ DOWNLOAD BLOB UTILITY
    function downloadBlob(blob, fileName) {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    }

    function loadFilterOptions() {
        // ✅ LOAD DEPARTMENTS FROM getDepartments
        $.ajax({
            url: '../api/documentMasterAPI.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'getDepartments' },
            success: function(response) {
                const select = $('#filterDepartment');
                const editSelect = $('#editDepartment');
                
                if (Array.isArray(response)) {
                    response.forEach(function(item) {
                        select.append(`<option value="${item.value}">${item.label}</option>`);
                        editSelect.append(`<option value="${item.value}">${item.label}</option>`);
                    });
                }
                
                // ✅ ADD HARDCODED DEPARTMENTS
                const hardcodedDepts = ['Operations', 'QM&GCC'];
                hardcodedDepts.forEach(function(dept) {
                    if (select.find(`option[value="${dept}"]`).length === 0) {
                        select.append(`<option value="${dept}">${dept}</option>`);
                        editSelect.append(`<option value="${dept}">${dept}</option>`);
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading departments:', error);
                
                // ✅ FALLBACK: ADD HARDCODED DEPARTMENTS IF API FAILS
                const hardcodedDepts = ['Operations', 'QM&GCC'];
                const select = $('#filterDepartment');
                const editSelect = $('#editDepartment');
                
                hardcodedDepts.forEach(function(dept) {
                    select.append(`<option value="${dept}">${dept}</option>`);
                    editSelect.append(`<option value="${dept}">${dept}</option>`);
                });
                
                toastr.error('Error loading departments');
            }
        });
    }

    function fetchAllFilteredData() {
        return $.ajax({
            url: '../api/documentMasterAPI.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'getAllFacs',
                department: currentFilters.department,
                issue_date_from: currentFilters.issue_date_from,
                issue_date_to: currentFilters.issue_date_to,
                expiry_date_from: currentFilters.expiry_date_from,
                expiry_date_to: currentFilters.expiry_date_to
            }
        });
    }

    function exportToExcel() {
        showFullLoader();

        fetchAllFilteredData().done(function(response) {
            if (!response || !Array.isArray(response)) {
                hideFullLoader();
                toastr.error('No data available to export');
                return;
            }

            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet('FAC Certificates');

            const headers = ['Department', 'Issue Date', 'Next Review Date', 'Remark'];
            worksheet.addRow(headers);

            response.forEach(function(row) {
                worksheet.addRow([
                    row.department || '',
                    row.issue_date ? moment(row.issue_date).format('MM/DD/YYYY') : '',
                    row.expiry_date ? moment(row.expiry_date).format('MM/DD/YYYY') : '',
                    row.remark || ''
                ]);
            });

            worksheet.columns = [
                { width: 25 },
                { width: 15 },
                { width: 15 },
                { width: 30 }
            ];

            const headerRow = worksheet.getRow(1);
            headerRow.font = { bold: true, size: 12, color: { argb: 'FFFFFF' } };
            headerRow.fill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: { argb: '4472C4' }
            };
            headerRow.alignment = { horizontal: 'center', vertical: 'middle' };
            headerRow.height = 25;

            worksheet.eachRow((row, rowNumber) => {
                row.eachCell((cell) => {
                    cell.border = {
                        top: { style: 'thin' },
                        left: { style: 'thin' },
                        bottom: { style: 'thin' },
                        right: { style: 'thin' }
                    };
                    cell.alignment = { horizontal: 'center', vertical: 'middle' };
                    if (rowNumber > 1) {
                        cell.font = { size: 11 };
                    }
                });
            });

            workbook.xlsx.writeBuffer().then(buffer => {
                const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'FAC_Certificate_' + moment().format('YYYY-MM-DD_HH-mm-ss') + '.xlsx';
                a.click();
                window.URL.revokeObjectURL(url);
                hideFullLoader();
                toastr.success('Excel exported successfully');
            }).catch(error => {
                hideFullLoader();
                console.error('Export error:', error);
                toastr.error('Error exporting to Excel');
            });
        }).fail(function() {
            hideFullLoader();
            toastr.error('Error fetching data for export');
        });
    }

    function exportToPDF() {
        showFullLoader();

        fetchAllFilteredData().done(function(response) {
            if (!response || !Array.isArray(response)) {
                hideFullLoader();
                toastr.error('No data available to export');
                return;
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4');

            const pageWidth = 297;
            const pageHeight = 210;
            const margin = 3;
            const usableWidth = pageWidth - (2 * margin);

            const headers = ['Department', 'Issue Date', 'Next Review Date', 'Remark'];
            const dataRows = [];

            response.forEach(function(row) {
                dataRows.push([
                    row.department || '',
                    row.issue_date ? moment(row.issue_date).format('MM/DD/YYYY') : '',
                    row.expiry_date ? moment(row.expiry_date).format('MM/DD/YYYY') : '',
                    row.remark || ''
                ]);
            });

            doc.setFontSize(14);
            doc.setFont(undefined, 'bold');
            doc.text('FAC (Function Allocation Chart) Certificate Report', margin, margin + 3);

            doc.setFontSize(9);
            doc.setFont(undefined, 'normal');
            doc.text('Generated on: ' + moment().format('MM/DD/YYYY HH:mm:ss'), margin, margin + 8);

            doc.autoTable({
                head: [headers],
                body: dataRows,
                startY: margin + 13,
                margin: { top: margin, right: margin, bottom: margin, left: margin },
                tableWidth: usableWidth,
                headStyles: {
                    fillColor: [68, 114, 196],
                    textColor: [255, 255, 255],
                    fontStyle: 'bold',
                    fontSize: 8,
                    halign: 'center',
                    valign: 'middle',
                    cellPadding: 1.5,
                    lineColor: [0, 0, 0],
                    lineWidth: 0.3,
                    minCellHeight: 6
                },
                bodyStyles: {
                    fontSize: 7,
                    cellPadding: 1,
                    lineColor: [180, 180, 180],
                    lineWidth: 0.2,
                    textColor: [0, 0, 0],
                    valign: 'middle',
                    minCellHeight: 5
                },
                alternateRowStyles: {
                    fillColor: [245, 245, 245]
                },
                didDrawPage: function(data) {
                    const totalPages = doc.internal.pages.length - 1;
                    doc.setFontSize(7);
                    doc.setTextColor(128);
                    doc.text(
                        'Page ' + data.pageNumber + ' of ' + totalPages,
                        pageWidth / 2,
                        pageHeight - 2,
                        { align: 'center' }
                    );
                },
                theme: 'grid',
                tableLineColor: [0, 0, 0],
                tableLineWidth: 0.2
            });

            doc.save('FAC_Certificate_' + moment().format('YYYY-MM-DD_HH-mm-ss') + '.pdf');
            hideFullLoader();
            toastr.success('PDF exported successfully');
        }).fail(function() {
            hideFullLoader();
            toastr.error('Error fetching data for export');
        });
    }

    function initializeDateRangePickers() {
        // ✅ ISSUE DATE RANGE PICKER
        $('#filterIssueDate').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD'
            },
            autoUpdateInput: false,
            opens: 'left'
        });

        $('#filterIssueDate').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
        });

        $('#filterIssueDate').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        // ✅ EXPIRY DATE RANGE PICKER
        $('#filterExpiryDate').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD'
            },
            autoUpdateInput: false,
            opens: 'left'
        });

        $('#filterExpiryDate').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
        });

        $('#filterExpiryDate').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    }

    function injectExportButtons() {
        const wrapper = $('#resultsTable_wrapper');

        if ($('#exportButtonsContainer').length === 0) {
            const exportHtml = `
                <div id="exportButtonsContainer">
                    <button type="button" class="export-btn export-btn-excel" id="exportExcelBtn" title="Export to Excel">
                        <i class="fa fa-file-excel-o"></i> Excel
                    </button>
                    <button type="button" class="export-btn export-btn-pdf" id="exportPdfBtn" title="Export to PDF">
                        <i class="fa fa-file-pdf-o"></i> PDF
                    </button>
                </div>
            `;

            wrapper.find('.dataTables_length').after(exportHtml);

            $(document).on('click', '#exportExcelBtn', function() {
                exportToExcel();
            });

            $(document).on('click', '#exportPdfBtn', function() {
                exportToPDF();
            });
        }
    }

    $(document).ready(function() {
        loadFilterOptions();
        initializeDateRangePickers();

        // ✅ EDIT MODAL HANDLERS
        document.getElementById('editModalCloseBtn').addEventListener('click', closeEditModal);
        document.getElementById('editModalCancelBtn').addEventListener('click', closeEditModal);
        document.getElementById('editModalSaveBtn').addEventListener('click', saveCertificateChanges);

        document.getElementById('editModalOverlay').addEventListener('click', function(event) {
            if (event.target === this) {
                closeEditModal();
            }
        });

        // ✅ DOCUMENT VIEWER BUTTON HANDLERS
        document.getElementById('docFirstBtn').addEventListener('click', function() {
            if (viewerState.currentPage > 1) {
                viewerState.currentPage = 1;
                document.getElementById('documentViewerContainer').scrollTop = 0;
                updateDocumentNavigationButtons();
            }
        });

        document.getElementById('docPrevBtn').addEventListener('click', function() {
            if (viewerState.currentPage > 1) {
                viewerState.currentPage--;
                document.getElementById('docCurrentPage').textContent = viewerState.currentPage;
                scrollToPage(viewerState.currentPage);
                updateDocumentNavigationButtons();
            }
        });

        document.getElementById('docNextBtn').addEventListener('click', function() {
            if (viewerState.currentPage < viewerState.totalPages) {
                viewerState.currentPage++;
                document.getElementById('docCurrentPage').textContent = viewerState.currentPage;
                scrollToPage(viewerState.currentPage);
                updateDocumentNavigationButtons();
            }
        });

        document.getElementById('docLastBtn').addEventListener('click', function() {
            if (viewerState.currentPage < viewerState.totalPages) {
                viewerState.currentPage = viewerState.totalPages;
                document.getElementById('docCurrentPage').textContent = viewerState.totalPages;
                document.getElementById('documentViewerContainer').scrollTop = document.getElementById('documentViewerContainer').scrollHeight;
                updateDocumentNavigationButtons();
            }
        });

        document.getElementById('docZoomInBtn').addEventListener('click', function() {
            viewerState.scale += 0.2;
            renderAllPagesSequentially();
        });

        document.getElementById('docZoomOutBtn').addEventListener('click', function() {
            if (viewerState.scale > 0.5) {
                viewerState.scale -= 0.2;
                renderAllPagesSequentially();
            }
        });

        document.getElementById('docDownloadBtn').addEventListener('click', function() {
            downloadDocument();
        });

        document.getElementById('docCloseBtn').addEventListener('click', function() {
            closeViewer();
        });

        document.getElementById('documentViewerOverlay').addEventListener('click', function(event) {
            if (event.target === this) {
                closeViewer();
            }
        });

        function scrollToPage(pageNum) {
            const container = document.getElementById('documentContentWrapper');
            const pages = container.querySelectorAll('[data-page-number]');
            for (let i = 0; i < pages.length; i++) {
                if (parseInt(pages[i].getAttribute('data-page-number')) === pageNum) {
                    pages[i].scrollIntoView({ behavior: 'smooth', block: 'start' });
                    break;
                }
            }
        }

        // ✅ INITIALIZE DATATABLE
        dataTable = $('#resultsTable').DataTable({
            processing: true,
            language: {
                processing: '<div style="display: none;"></div>'
            },
            serverSide: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[1, 'desc']],
            scrollX: true,
            scrollY: '75vh',
            scrollCollapse: true,
            fixedHeader: false,
            dom: 'lBfrtip',
            buttons: [],
            ajax: {
                url: '../api/documentMasterAPI.php',
                type: 'POST',
                data: function(d) {
                    d.action = 'getFacs';
                    d.department = currentFilters.department;
                    d.issue_date_from = currentFilters.issue_date_from;
                    d.issue_date_to = currentFilters.issue_date_to;
                    d.expiry_date_from = currentFilters.expiry_date_from;
                    d.expiry_date_to = currentFilters.expiry_date_to;
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    toastr.error('Error loading table data');
                }
            },
            columns: [
                {
                    data: 'department',
                    searchable: true,
                    className: 'text-center',
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: 'issue_date',
                    searchable: true,
                    className: 'text-center',
                    render: function(data) {
                        return data ? moment(data).format('MM/DD/YYYY') : '-';
                    }
                },
                {
                    data: 'expiry_date',
                    searchable: true,
                    className: 'text-center',
                    render: function(data) {
                        return data ? moment(data).format('MM/DD/YYYY') : '-';
                    }
                },
                {
                    data: 'remark',
                    searchable: true,
                    className: 'text-center',
                    render: function(data) {
                        return data || '-';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data) {
                        let actionHtml = '<div class="action-buttons">';

                        // ✅ EDIT BUTTON FIRST
                        if (userPermissions.hasEditRights) {
                            actionHtml += `<button class="action-btn edit-btn" onclick="openEditModal(${data.id})" title="Edit Certificate">
                                                <i class="fa fa-pencil-square-o"></i>
                                            </button>`;
                        }

                        // ✅ VIEW BUTTON SECOND
                        if (userPermissions.hasViewRights) {
                            actionHtml += `<button class="action-btn view-btn" onclick="viewFacCertificate(${data.id})" title="View Certificate">
                                                <i class="fa fa-file-pdf-o"></i>
                                            </button>`;
                        }

                        actionHtml += '</div>';
                        return actionHtml;
                    }
                }
            ],
            responsive: false,
            autoWidth: false,
            columnDefs: [
                {
                    targets: '_all',
                    className: 'dt-head-center dt-body-center'
                }
            ],
            initComplete: function() {
                console.log("DataTable initialized successfully");
                injectExportButtons();
            },
            drawCallback: function(settings) {
                $(window).trigger('resize');
            }
        });

        $(window).on('resize', function() {
            clearTimeout(window.resizeTimeout);
            window.resizeTimeout = setTimeout(function() {
                if (dataTable) {
                    dataTable.columns.adjust();
                }
            }, 100);
        });

        $('#applyFiltersBtn').click(function() {
            // ✅ ISSUE DATE RANGE
            const issueDateRange = $('#filterIssueDate').val().trim();
            let issueFrom = '';
            let issueTo = '';

            if (issueDateRange) {
                const issueDates = issueDateRange.split(' to ');
                if (issueDates.length === 2) {
                    issueFrom = issueDates[0].trim();
                    issueTo = issueDates[1].trim();

                    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
                    if (!dateRegex.test(issueFrom) || !dateRegex.test(issueTo)) {
                        toastr.error('Please use YYYY-MM-DD format for issue dates');
                        return;
                    }
                } else if (issueDateRange !== '') {
                    toastr.error('Please use format: YYYY-MM-DD to YYYY-MM-DD for issue dates');
                    return;
                }
            }

            // ✅ EXPIRY DATE RANGE
            const expiryDateRange = $('#filterExpiryDate').val().trim();
            let expiryFrom = '';
            let expiryTo = '';

            if (expiryDateRange) {
                const expiryDates = expiryDateRange.split(' to ');
                if (expiryDates.length === 2) {
                    expiryFrom = expiryDates[0].trim();
                    expiryTo = expiryDates[1].trim();

                    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
                    if (!dateRegex.test(expiryFrom) || !dateRegex.test(expiryTo)) {
                        toastr.error('Please use YYYY-MM-DD format for next review dates');
                        return;
                    }
                } else if (expiryDateRange !== '') {
                    toastr.error('Please use format: YYYY-MM-DD to YYYY-MM-DD for next review dates');
                    return;
                }
            }

            currentFilters = {
                department: escapeSpecialChars($('#filterDepartment').val().trim()),
                issue_date_from: issueFrom,
                issue_date_to: issueTo,
                expiry_date_from: expiryFrom,
                expiry_date_to: expiryTo
            };

            dataTable.ajax.reload();
            toastr.success('Filters applied');
        });

        $('#clearFiltersBtn').click(function() {
            $('#filterDepartment').val('');
            $('#filterIssueDate').val('');
            $('#filterExpiryDate').val('');

            currentFilters = {
                department: '',
                issue_date_from: '',
                issue_date_to: '',
                expiry_date_from: '',
                expiry_date_to: ''
            };

            dataTable.ajax.reload();
            toastr.success('Filters cleared');
        });

        // ✅ VIEW FAC CERTIFICATE FUNCTION
        window.viewFacCertificate = function(id) {
            showFullLoader();

            $.ajax({
                url: '../api/documentMasterAPI.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'getFac',
                    id: id
                },
                success: function(response) {
                    hideFullLoader();

                    if (response.success) {
                        const certificate = response.data;

                        if (certificate.file_path && certificate.actual_file_name) {
                            openDocumentViewer(id, certificate.actual_file_name);
                        } else {
                            toastr.error('No file available for this certificate');
                        }
                    } else {
                        toastr.error(response.message || 'Failed to load certificate');
                    }
                },
                error: function(xhr, status, error) {
                    hideFullLoader();
                    console.error('Error:', error);
                    toastr.error('Error loading certificate');
                }
            });
        };
    });
</script>
</body>
</html>