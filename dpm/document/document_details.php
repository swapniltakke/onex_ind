<?php
SharedManager::checkAuthToModule(22);
include_once '../core/index.php';
$project = $_GET["project"] ?: 0;
SharedManager::saveLog("log_document_master_form", "View for Project: $project");
$menu_header_display = 'Document Details';
$currentUser = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Check if user has edit rights (Module 23)
$hasEditRights = in_array(23, SharedManager::getUser()["Modules"]);
$hasAdminRights = in_array(23, SharedManager::getUser()["Modules"]);
$hasViewRights = in_array(22, SharedManager::getUser()["Modules"]);
?>
<!DOCTYPE html>
<html>
<head>
    <title>OneX | Document Details</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
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

        .action-btn.edit-btn {
            color: #007bff !important;
        }

        .action-btn.edit-btn:hover {
            color: #0056b3 !important;
            transform: scale(1.15) !important;
        }

        .action-btn.download-btn {
            color: #17a2b8 !important;
        }

        .action-btn.download-btn:hover {
            color: #0c5460 !important;
            transform: scale(1.15) !important;
        }

        .action-btn i.fa {
            font-size: 14px !important;
            margin: 0 !important;
        }

        .modal-dialog {
            max-width: 90vw !important;
            width: 90vw !important;
            max-height: 90vh !important;
            margin: auto !important;
        }

        .modal-content {
            border-radius: 8px;
            z-index: 1050 !important;
            max-height: 90vh !important;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            flex-shrink: 0;
        }

        .modal-body {
            padding: 15px;
            max-height: calc(90vh - 120px);
            overflow-y: auto;
            overflow-x: hidden;
            flex: 1;
        }

        .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .modal-footer {
            border-top: 1px solid #dee2e6;
            padding: 1rem;
            flex-shrink: 0;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-responsive table {
            width: 100%;
            margin-bottom: 0;
            table-layout: auto;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .table-responsive th,
        .table-responsive td {
            padding: 0.75rem;
            vertical-align: middle;
            word-break: break-word;
            white-space: normal;
            max-width: 200px;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
            display: block;
            font-size: 12px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
            font-family: Arial, sans-serif;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #00b5ad;
            box-shadow: 0 0 5px rgba(0, 181, 173, 0.3);
        }

        .form-group input:disabled {
            background-color: #f5f5f5;
            cursor: not-allowed;
            color: #00b5ad;
            font-weight: 600;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 60px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 0px;
        }

        .form-row:last-of-type {
            margin-bottom: 0;
        }

        .form-row.document-upload-row {
            grid-template-columns: 1fr;
        }

        .form-row.full-width {
            grid-template-columns: 1fr;
        }

        .required-field {
            color: red;
        }

        .error-message {
            color: #dc3545;
            font-size: 11px;
            margin-top: 2px;
        }

        .display-value {
            background-color: #f5f5f5;
            padding: 6px;
            border-radius: 4px;
            font-weight: 600;
            color: #00b5ad;
            border: 1px solid #ddd;
            font-size: 12px;
            min-height: 28px;
            display: flex;
            align-items: center;
            word-break: break-all;
        }

        .display-value-black {
            background-color: #f5f5f5;
            padding: 6px;
            border-radius: 4px;
            font-weight: 600;
            color: black;
            border: 1px solid #ddd;
            font-size: 12px;
            min-height: 28px;
            display: flex;
            align-items: center;
            word-break: break-all;
        }

        .file-upload-wrapper {
            position: relative;
            width: 100%;
        }

        .file-upload-input {
            display: none;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 12px;
            border: 2px dashed #00b5ad;
            border-radius: 4px;
            background-color: #f9f9f9;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 12px;
            color: #00b5ad;
            font-weight: 600;
        }

        .file-upload-label:hover {
            background-color: #f0f0f0;
            border-color: #008b87;
        }

        .file-upload-label.drag-over {
            background-color: #e8f5f4;
            border-color: #008b87;
        }

        .file-name-display {
            background-color: #f5f5f5;
            padding: 6px;
            border-radius: 4px;
            font-weight: 600;
            color: #00b5ad;
            border: 1px solid #ddd;
            font-size: 12px;
            min-height: 28px;
            display: none;
            align-items: center;
            word-break: break-all;
            margin-top: 6px;
        }

        .file-name-display.success {
            color: #28a745;
            border-color: #28a745;
            display: flex;
        }

        .file-name-display.error {
            color: #dc3545;
            border-color: #dc3545;
            display: flex;
        }

        .file-upload-icon {
            margin-right: 8px;
            font-size: 16px;
        }

        .btn-submit,
        .btn-update,
        .btn-close {
            padding: 8px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: background-color 0.3s ease;
            white-space: nowrap;
        }

        .btn-submit,
        .btn-update {
            background-color: #00b5ad;
            color: white;
        }

        .btn-submit:hover,
        .btn-update:hover {
            background-color: #008b87;
        }

        .btn-close {
            background-color: #6c757d;
            color: white;
        }

        .btn-close:hover {
            background-color: #5a6268;
        }

        #resultsTable {
            width: 100% !important;
            margin: 0 !important;
            font-size: 10px !important;
            table-layout: fixed !important;
        }

        #resultsTable thead th {
            display: none !important;
        }

        #resultsTable thead {
            display: none !important;
        }

        #resultsTable {
            table-layout: fixed !important;
            width: 100% !important;
        }

        #resultsTable thead th:nth-child(1),
        #resultsTable tbody td:nth-child(1) { width: 105px !important; }

        #resultsTable thead th:nth-child(2),
        #resultsTable tbody td:nth-child(2) { width: 75px !important; }

        #resultsTable thead th:nth-child(3),
        #resultsTable tbody td:nth-child(3) {
            width: 45px !important;
        }

        #resultsTable tbody td:nth-child(3) {
            padding-left: 10px !important;
        }

        #resultsTable thead th:nth-child(4) {
            width: 56px !important;
            padding-left: 13px !important;
            font-size: 12px !important;
        }
        #resultsTable tbody td:nth-child(4) { width: 60px !important; }

        #resultsTable thead th:nth-child(5),
        #resultsTable tbody td:nth-child(5) { width: 85px !important; }

        #resultsTable thead th:nth-child(6),
        #resultsTable tbody td:nth-child(6) {
            width: 105px !important;
            padding-left: 8px !important;
        }

        #resultsTable thead th:nth-child(7),
        #resultsTable tbody td:nth-child(7) { width: 95px !important; }

        #resultsTable thead th:nth-child(8),
        #resultsTable tbody td:nth-child(8) { width: 85px !important; }

        #resultsTable thead th:nth-child(9),
        #resultsTable tbody td:nth-child(9) { width: 100px !important; }

        #resultsTable thead th:nth-child(10),
        #resultsTable tbody td:nth-child(10) { width: 60px !important; }

        #resultsTable tbody td {
            text-align: center !important;
            vertical-align: middle !important;
            font-size: 10px !important;
            padding: 5px 3px !important;
            height: 34px !important;
            line-height: 1.2 !important;
        }

        #resultsTable tbody tr:hover {
            background-color: #f0f8ff !important;
        }

        #resultsTable tbody tr:nth-child(even) {
            background-color: #f9f9f9 !important;
        }

        .dataTables_scrollHead thead th:nth-child(1) {
            width: 105px !important;
            white-space: nowrap !important;
        }

        .dataTables_scrollHead thead th {
            font-size: 10px !important;
            padding: 5px 3px !important;
            text-align: center !important;
            vertical-align: middle !important;
        }

        .card {
            width: 100%;
            margin-bottom: 20px;
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
            background-color: #1abc9c !important;
            color: white !important;
            border-color: #1abc9c !important;
            font-weight: 600 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background-color: #16a085 !important;
            border-color: #16a085 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            background-color: #f9f9f9 !important;
            border-color: #e0e0e0 !important;
            color: #999 !important;
            cursor: not-allowed !important;
            opacity: 0.5 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.previous,
        .dataTables_wrapper .dataTables_paginate .paginate_button.next {
            background-color: white !important;
            border: 1px solid #e0e0e0 !important;
            color: #666 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.previous:hover:not(.disabled),
        .dataTables_wrapper .dataTables_paginate .paginate_button.next:hover:not(.disabled) {
            background-color: #f5f5f5 !important;
            border-color: #bbb !important;
            color: #333 !important;
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

        .daterangepicker {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            border: 1px solid #ddd !important;
            border-radius: 6px !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
            z-index: 99999 !important;
            padding: 0 !important;
        }

        .daterangepicker .calendar-table {
            background-color: white !important;
            border: none !important;
            padding: 10px !important;
        }

        .daterangepicker .calendar-table thead tr {
            background-color: #f8f9fa !important;
        }

        .daterangepicker .calendar-table thead tr th {
            color: #333 !important;
            font-weight: 600 !important;
            font-size: 11px !important;
            padding: 8px 5px !important;
            border: none !important;
        }

        .daterangepicker .calendar-table tbody tr td {
            font-size: 12px !important;
            padding: 6px !important;
            text-align: center !important;
            border: none !important;
            color: #333 !important;
        }

        .daterangepicker .calendar-table tbody tr td.available:hover {
            background-color: #e8f5f4 !important;
            color: #00b5ad !important;
            border-radius: 4px !important;
            cursor: pointer !important;
        }

        .daterangepicker .calendar-table tbody tr td.active {
            background-color: #00b5ad !important;
            color: white !important;
            border-radius: 4px !important;
            font-weight: 600 !important;
        }

        .daterangepicker .calendar-table tbody tr td.in-range {
            background-color: #d4f1f0 !important;
            color: #00b5ad !important;
            border-radius: 0 !important;
        }

        .daterangepicker .calendar-table tbody tr td.start-date {
            background-color: #00b5ad !important;
            color: white !important;
            border-radius: 4px 0 0 4px !important;
        }

        .daterangepicker .calendar-table tbody tr td.end-date {
            background-color: #00b5ad !important;
            color: white !important;
            border-radius: 0 4px 4px 0 !important;
        }

        .daterangepicker .calendar-table tbody tr td.off {
            color: #ccc !important;
            background-color: #fafafa !important;
        }

        .daterangepicker .calendar-table tbody tr td.disabled {
            color: #ddd !important;
            cursor: not-allowed !important;
        }

        .daterangepicker select.monthselect,
        .daterangepicker select.yearselect {
            background-color: white !important;
            border: 1px solid #ddd !important;
            border-radius: 4px !important;
            padding: 4px 8px !important;
            font-size: 12px !important;
            color: #333 !important;
            cursor: pointer !important;
            margin: 0 5px !important;
        }

        .daterangepicker select.monthselect:hover,
        .daterangepicker select.yearselect:hover {
            border-color: #00b5ad !important;
            box-shadow: 0 2px 4px rgba(0, 181, 173, 0.2) !important;
        }

        .daterangepicker select.monthselect:focus,
        .daterangepicker select.yearselect:focus {
            outline: none !important;
            border-color: #00b5ad !important;
            box-shadow: 0 0 5px rgba(0, 181, 173, 0.3) !important;
        }

        .daterangepicker .drp-buttons {
            border-top: 1px solid #e0e0e0 !important;
            padding: 10px !important;
            background-color: #f9f9f9 !important;
            border-radius: 0 0 6px 6px !important;
        }

        .daterangepicker .drp-buttons .btn {
            padding: 6px 14px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            border-radius: 4px !important;
            transition: all 0.3s ease !important;
        }

        .daterangepicker .drp-buttons .applyBtn {
            background-color: #00b5ad !important;
            color: white !important;
            border: none !important;
        }

        .daterangepicker .drp-buttons .applyBtn:hover {
            background-color: #008b87 !important;
            box-shadow: 0 2px 6px rgba(0, 181, 173, 0.3) !important;
        }

        .daterangepicker .drp-buttons .cancelBtn {
            background-color: #6c757d !important;
            color: white !important;
            border: none !important;
        }

        .daterangepicker .drp-buttons .cancelBtn:hover {
            background-color: #5a6268 !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2) !important;
        }

        .daterangepicker .ranges {
            background-color: #f8f9fa !important;
            border-right: 1px solid #e0e0e0 !important;
            padding: 10px !important;
        }

        .daterangepicker .ranges ul {
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .daterangepicker .ranges li {
            padding: 8px 12px !important;
            margin-bottom: 4px !important;
            cursor: pointer !important;
            border-radius: 4px !important;
            font-size: 12px !important;
            color: #333 !important;
            transition: all 0.2s ease !important;
        }

        .daterangepicker .ranges li:hover {
            background-color: #e8f5f4 !important;
            color: #00b5ad !important;
        }

        .daterangepicker .ranges li.active {
            background-color: #00b5ad !important;
            color: white !important;
            font-weight: 600 !important;
        }

        .daterangepicker th.month {
            color: #00b5ad !important;
            font-weight: 700 !important;
            font-size: 13px !important;
        }

        .daterangepicker td.week {
            color: #999 !important;
            font-size: 10px !important;
        }

        .daterangepicker .drp-calendar {
            padding: 8px !important;
        }

        .daterangepicker .drp-calendar.left {
            border-right: 1px solid #e0e0e0 !important;
        }

        .daterangepicker th.prev,
        .daterangepicker th.next {
            cursor: pointer !important;
            color: #00b5ad !important;
            font-weight: bold !important;
            font-size: 16px !important;
        }

        .daterangepicker th.prev:hover,
        .daterangepicker th.next:hover {
            color: #008b87 !important;
            background-color: #f0f0f0 !important;
            border-radius: 4px !important;
        }

        .daterangepicker th.available:hover {
            background-color: #f0f0f0 !important;
            border-radius: 4px !important;
        }

        .daterangepicker td.today {
            background-color: #fff9e6 !important;
            border: 1px solid #ffc107 !important;
            border-radius: 4px !important;
        }

        .daterangepicker td.today:hover {
            background-color: #fff3cd !important;
        }

        .filter-section {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
            margin-bottom: 12px;
        }

        .filter-row.filter-row-2 {
            grid-template-columns: repeat(5, 1fr);
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .filter-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
            font-size: 12px;
            height: 18px;
            line-height: 18px;
        }

        .filter-group select,
        .filter-group input {
            padding: 8px 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
            font-family: Arial, sans-serif;
            height: 36px;
            line-height: 1.5;
            box-sizing: border-box;
            flex: 1;
        }

        .filter-group select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: none;
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 20px;
            padding-right: 8px;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #00b5ad;
            box-shadow: 0 0 5px rgba(0, 181, 173, 0.3);
        }

        .filter-buttons-group {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .filter-buttons-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
            font-size: 12px;
            height: 18px;
            line-height: 18px;
        }

        .filter-buttons-group .btn-filter,
        .filter-buttons-group .btn-clear {
            width: 100%;
            margin: 0 !important;
            padding: 0 12px !important;
            height: 36px !important;
            font-size: 12px !important;
            border: none !important;
            border-radius: 4px !important;
            cursor: pointer !important;
            font-weight: 600 !important;
            transition: background-color 0.3s ease !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-sizing: border-box !important;
            flex: 1;
        }

        .btn-filter,
        .btn-clear {
            padding: 0 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: background-color 0.3s ease;
            white-space: nowrap;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            box-sizing: border-box;
        }

        .btn-filter {
            background-color: #00b5ad;
            color: white;
        }

        .btn-filter:hover {
            background-color: #008b87;
        }

        .btn-clear {
            background-color: #e0e1e2;
            color: rgba(0, 0, 0, .6);
        }

        .btn-clear:hover {
            background-color: #939ca3ff;
        }

        .row {
            margin: 8px 0;
        }

        .col-md-12 {
            padding: 0 10px;
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

        /* =====================================================
           TITLE HOVER POPUP
           ===================================================== */
        .title-truncated {
            display: inline-block;
            max-width: 95px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 10px;
            color: inherit;
            cursor: default;
            vertical-align: middle;
        }

        #titleHoverPopup {
            display: none;
            position: fixed;
            z-index: 999999;
            background: #ffffff;
            border: 1px solid #c8c8c8;
            border-radius: 4px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.18);
            padding: 12px 15px;
            max-width: 220px;
            min-width: 120px;
            font-size: 12px;
            color: #222;
            line-height: 1.6;
            word-break: break-word;
            white-space: pre-wrap;
            pointer-events: none;
        }

        #titleHoverPopup.visible {
            display: block;
        }

        /* =====================================================
           VERSION STATUS BAR
           ===================================================== */
        .version-box {
            background-color: #f5f5f5;
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 14px;
            font-weight: 600;
            color: #555;
            min-height: 38px;
            display: flex;
            align-items: center;
            margin-bottom: 6px;
        }

        /* Default state — teal/grey (no file selected) */
        .version-status-bar {
            background-color: #e8f7f6;
            border: 1px solid #b2dfdb;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 12px;
            color: #555;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .version-status-bar .vs-label {
            font-weight: 700;
            color: #333;
        }

        .version-status-bar .vs-value {
            font-weight: 600;
            color: #00796b;
        }

        /* ✅ File selected state — full green */
        .version-status-bar.has-file {
            background-color: #e8f5e9;
            border-color: #a5d6a7;
        }

        .version-status-bar.has-file .vs-label {
            color: #2e7d32;
            font-weight: 700;
        }

        .version-status-bar.has-file .vs-value {
            color: #2e7d32;
            font-weight: 600;
        }

        /* =====================================================
           DOCUMENT VIEWER STYLES
           ===================================================== */
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

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-30px); }
            to   { opacity: 1; transform: translateY(0); }
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
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
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
            top: 0; left: 0;
            width: 100%; height: 100%;
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
            bottom: 8px; right: 12px;
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
            width: 40px; height: 40px;
            border: 3px solid #e0e0e0;
            border-top: 3px solid #00b5ad;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0%   { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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

        .document-viewer-container::-webkit-scrollbar { width: 8px; }
        .document-viewer-container::-webkit-scrollbar-track { background: #e0e0e0; border-radius: 4px; }
        .document-viewer-container::-webkit-scrollbar-thumb { background: #00b5ad; border-radius: 4px; }
        .document-viewer-container::-webkit-scrollbar-thumb:hover { background: #008b87; }

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

        .word-document-content {
            width: 100%;
            height: auto;
            display: block;
            overflow: visible;
            padding: 40px;
            background: white;
            font-family: 'Calibri', 'Arial', sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
            box-sizing: border-box;
        }

        .word-document-content * {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            max-height: none !important;
            height: auto !important;
            overflow: visible !important;
        }

        .word-document-content p { margin: 0 0 12px 0 !important; padding: 0 !important; line-height: 1.6 !important; }
        .word-document-content h1,
        .word-document-content h2,
        .word-document-content h3,
        .word-document-content h4,
        .word-document-content h5,
        .word-document-content h6 { margin: 20px 0 12px 0 !important; padding: 0 !important; font-weight: bold !important; line-height: 1.4 !important; }
        .word-document-content h1 { font-size: 26pt !important; }
        .word-document-content h2 { font-size: 22pt !important; }
        .word-document-content h3 { font-size: 18pt !important; }
        .word-document-content h4 { font-size: 14pt !important; }
        .word-document-content ul,
        .word-document-content ol { margin: 12px 0 12px 30px !important; padding: 0 !important; }
        .word-document-content li { margin: 5px 0 !important; padding: 0 !important; line-height: 1.6 !important; }
        .word-document-content table { width: 100% !important; border-collapse: collapse !important; margin: 15px 0 !important; border: 1px solid #999 !important; }
        .word-document-content tr { display: table-row !important; }
        .word-document-content td,
        .word-document-content th { border: 1px solid #999 !important; padding: 8px !important; text-align: left !important; vertical-align: top !important; display: table-cell !important; }
        .word-document-content th { background-color: #f0f0f0 !important; font-weight: bold !important; }
        .word-document-content img { max-width: 100% !important; height: auto !important; margin: 10px 0 !important; }
        .word-document-content br { display: block !important; }
        .word-document-content strong, .word-document-content b { font-weight: bold !important; }
        .word-document-content em, .word-document-content i { font-style: italic !important; }
        .word-document-content u { text-decoration: underline !important; }

        .pdf-watermark-container {
            position: absolute; top: 0; left: 0;
            width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            pointer-events: none; z-index: 2;
        }

        .pdf-watermark-text {
            font-size: 60px; font-weight: bold;
            color: rgba(200, 200, 200, 0.25);
            transform: rotate(-45deg);
            white-space: nowrap; text-align: center;
            font-family: Arial, sans-serif;
            letter-spacing: 2px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .form-row { grid-template-columns: 1fr; gap: 10px; }
            .filter-row { grid-template-columns: 1fr; }
            .btn-filter, .btn-clear { width: 100%; }
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter,
            #exportButtonsContainer { float: none !important; margin: 5px 0 !important; clear: both; }
            #exportButtonsContainer { justify-content: center; }
            .document-viewer-modal { width: 98%; height: 92vh; max-width: 100%; }
            .document-watermark-text { font-size: 40px; }
            .document-viewer-header { flex-direction: column; gap: 8px; align-items: flex-start; }
            .document-viewer-controls { width: 100%; justify-content: space-between; }
            .document-viewer-title { width: 100%; }
            .document-viewer-modal-overlay { padding: 10px; }
            .word-document-content { padding: 20px !important; }
            #resultsTable { font-size: 10px !important; }
            #resultsTable thead th, #resultsTable tbody td { padding: 4px 2px !important; }
        }
    </style>
</head>
<body>
<div id="wrapper">
    <?php $activePage = '/document_details.php'; ?>
    <?php include_once '../shared/sidebar.php'; ?>

    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom" style="position: relative; flex-shrink: 0;">
            <div class="ui fixed menu" style="padding: 10px 21px; color:teal; width: 100%;">
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

        <div class="ui inverted segment full-loader" style="display: none">
            <div class="ui active inverted loader">Loading</div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="filter-section">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="filterDepartment">Department</label>
                            <select id="filterDepartment" class="form-control">
                                <option value="">-- All --</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="filterDocType">Doc Type</label>
                            <select id="filterDocType" class="form-control">
                                <option value="">-- All --</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="filterWIType">WI Type</label>
                            <select id="filterWIType" class="form-control">
                                <option value="">-- All --</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="filterProduct">Product</label>
                            <select id="filterProduct" class="form-control">
                                <option value="">-- All --</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="filterDocumentNumber">Document Number</label>
                            <input type="text" id="filterDocumentNumber" class="form-control" placeholder="Search...">
                        </div>
                    </div>

                    <div class="filter-row filter-row-2">
                        <div class="filter-group">
                            <label for="filterOwner">Owner</label>
                            <input type="text" id="filterOwner" class="form-control" placeholder="Search...">
                        </div>
                        <div class="filter-group">
                            <label for="filterIssueDateRange">Issue Date</label>
                            <input type="text" id="filterIssueDateRange" class="form-control" placeholder="Select range">
                        </div>
                        <div class="filter-group">
                            <label for="filterReviewDateRange">Review Date</label>
                            <input type="text" id="filterReviewDateRange" class="form-control" placeholder="Select range">
                        </div>
                        <div class="filter-group filter-buttons-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn-filter" id="applyFiltersBtn" title="Apply Filters">Apply Filters</button>
                        </div>
                        <div class="filter-group filter-buttons-group">
                            <label>&nbsp;</label>
                            <button type="button" class="btn-clear" id="clearFiltersBtn" title="Clear Filters">Clear Filters</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12" style="margin-top: -40px;">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive" style="width: 100%; overflow: visible;">
                            <table id="resultsTable" class="table table-striped table-bordered" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Doc Type</th>
                                        <th>Seq No</th>
                                        <th>Ver</th>
                                        <th>Owner</th>
                                        <th>Issue Date</th>
                                        <th>Review Date</th>
                                        <th>Doc Number</th>
                                        <th>Title</th>
                                        <th>Action</th>
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

    <?php $footer_display = 'Document Details';
    include_once '../../assemblynotes/shared/footer.php'; ?>
</div>

<!-- SINGLE GLOBAL HOVER POPUP -->
<div id="titleHoverPopup"></div>

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
                <button class="document-control-btn" id="docFirstBtn" title="First Page" style="display:none;"><i class="fa fa-step-backward"></i> First</button>
                <button class="document-control-btn" id="docPrevBtn" title="Previous Page" style="display:none;"><i class="fa fa-chevron-left"></i> Prev</button>
                <span class="document-page-info" id="pageInfo" style="display:none;"><span id="docCurrentPage">1</span> / <span id="docTotalPages">1</span></span>
                <button class="document-control-btn" id="docNextBtn" title="Next Page" style="display:none;">Next <i class="fa fa-chevron-right"></i></button>
                <button class="document-control-btn" id="docLastBtn" title="Last Page" style="display:none;">Last <i class="fa fa-step-forward"></i></button>
                <button class="document-control-btn" id="docZoomInBtn" title="Zoom In" style="display:none;"><i class="fa fa-plus"></i></button>
                <button class="document-control-btn" id="docZoomOutBtn" title="Zoom Out" style="display:none;"><i class="fa fa-minus"></i></button>
                <button class="document-download-btn" id="docDownloadBtn" title="Download" style="display:flex;"><i class="fa fa-download"></i> Download</button>
                <button class="document-close-btn" id="docCloseBtn"><i class="fa fa-times"></i> Close</button>
            </div>
        </div>
        <div class="document-viewer-container" id="documentViewerContainer">
            <div class="document-loading" id="document-loading">
                <div class="document-loading-spinner"></div>
                <p>Loading document...</p>
            </div>
            <div id="documentContentWrapper" style="width:100%; height:100%; display:none; flex-direction:column; align-items:center; overflow-y: auto;"></div>
        </div>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 90vw; width: 90vw;">
        <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column;">
            <div class="modal-header" style="flex-shrink: 0;">
                <h5 class="modal-title" id="editModalLabel">Edit Document</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="flex: 1; overflow-y: auto; max-height: calc(90vh - 120px);">
                <form id="editDocumentForm" enctype="multipart/form-data" novalidate style="margin-top:-10px;">
                    <input type="hidden" id="documentId" name="document_id">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="editDepartment">Department</label>
                            <div class="display-value-black" id="editDepartment">-</div>
                            <input type="hidden" id="editDepartmentHidden" name="department">
                        </div>
                        <div class="form-group">
                            <label for="editDocType">Doc Type</label>
                            <div class="display-value-black" id="editDocType">-</div>
                            <input type="hidden" id="editDocTypeHidden" name="doc_type">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="editSeqNo">Sequential Number</label>
                            <div class="display-value" id="editSeqNo">-</div>
                            <input type="hidden" id="editSeqNoHidden" name="seq_no">
                        </div>

                        <!-- ✅ VERSION FIELD -->
                        <div class="form-group">
                            <label>Current Version</label>
                            <div class="version-box" id="editVersion">-</div>
                            <div class="version-status-bar" id="versionStatusBar">
                                <span class="vs-label" id="versionStatusLabel">Current:</span>
                                <span class="vs-value" id="versionStatusValue">v-</span>
                                <span id="versionStatusFile">(No file selected)</span>
                            </div>
                            <input type="hidden" id="editVersionHidden" name="version">
                        </div>
                        <!-- END VERSION FIELD -->

                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="editOwner">Owner</label>
                            <div class="display-value-black" id="editOwner">-</div>
                            <input type="hidden" id="editOwnerHidden" name="owner">
                        </div>
                        <div class="form-group">
                            <label for="editIssueDate">Issue Date <span class="required-field">*</span></label>
                            <input type="text" id="editIssueDate" name="issue_date" class="form-control" placeholder="DD/MM/YYYY" autocomplete="off">
                            <div class="error-message" id="editIssueDateError"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="editNextReviewDate">Next Review Date</label>
                            <input type="text" id="editNextReviewDate" name="next_review_date" class="form-control" placeholder="DD/MM/YYYY" autocomplete="off">
                            <div class="error-message" id="editNextReviewDateError"></div>
                        </div>
                        <div class="form-group">
                            <label for="editDocumentNumber">Document Number</label>
                            <div class="display-value" id="editDocumentNumber">-</div>
                            <input type="hidden" id="editDocumentNumberHidden" name="document_number">
                        </div>
                    </div>

                    <div class="form-row document-upload-row">
                        <div class="form-group">
                            <label for="editDocumentFile">Upload Document File (PDF, Excel, Word, PowerPoint)</label>
                            <div class="file-upload-wrapper">
                                <input type="file" id="editDocumentFile" name="document_file" class="file-upload-input" accept=".pdf,.xlsx,.xls,.docx,.doc,.ppt,.pptx">
                                <label for="editDocumentFile" class="file-upload-label">
                                    <span class="file-upload-icon">📎</span>
                                    <span id="editFileUploadText">Click to upload or drag and drop (PDF, Excel, Word, PowerPoint - Max 50MB)</span>
                                </label>
                                <div class="file-name-display" id="editFileNameDisplay"></div>
                            </div>
                            <div class="error-message" id="editDocumentFileError"></div>
                            <small style="color: #666; margin-top: 5px; display: block;">
                                Supported formats: PDF (.pdf), Excel (.xlsx, .xls), Word (.docx, .doc), PowerPoint (.ppt, .pptx) - Maximum file size: 50MB
                            </small>
                        </div>
                    </div>

                    <div class="form-row full-width">
                        <div class="form-group">
                            <label for="editDescription">Title/Description <span class="required-field">*</span></label>
                            <textarea id="editDescription" name="description" placeholder="Enter document title or description"></textarea>
                            <div class="error-message" id="editDescriptionError"></div>
                        </div>
                    </div>

                    <div class="form-row full-width">
                        <div class="form-group">
                            <label for="editRemark">Remark</label>
                            <textarea id="editRemark" name="remark" placeholder="Enter any additional remarks (optional)"></textarea>
                            <div class="error-message" id="editRemarkError"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="flex-shrink: 0;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateButton" style="background-color: #00b5ad; color: white; border: none;">Update</button>
            </div>
        </div>
    </div>
</div>

<script>
    const userPermissions = {
        hasEditRights:  <?php echo json_encode($hasEditRights); ?>,
        hasAdminRights: <?php echo json_encode($hasAdminRights); ?>,
        hasViewRights:  <?php echo json_encode($hasViewRights); ?>
    };

    if (typeof pdfjsLib !== 'undefined') {
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    }

    let selectedFile = null;
    const MAX_FILE_SIZE = 50 * 1024 * 1024;
    let currentFilters = {
        department: '', doc_type: '', wi_type: '', product: '',
        document_number: '', owner: '',
        issue_date_from: '', issue_date_to: '',
        review_date_from: '', review_date_to: ''
    };
    let dataTable = null;

    let originalDocumentData = {
        issue_date: '', next_review_date: '', description: '', remark: ''
    };

    let viewerState = {
        pdfDoc: null, currentPage: 1, totalPages: 0, scale: 1.2,
        isLoading: false, blobUrl: null, fileName: '', fileType: '',
        pageCache: {}, documentId: null, applyWatermark: false, hasAdminRights: false
    };

    /* =====================================================
       HOVER POPUP
       ===================================================== */
    (function () {
        var popup     = document.getElementById('titleHoverPopup');
        var hideTimer = null;

        function showPopup(el) {
            clearTimeout(hideTimer);
            var fullText = el.getAttribute('data-full-title');
            if (!fullText) return;
            popup.textContent = fullText;
            popup.classList.add('visible');
            positionPopup(el);
        }

        function positionPopup(el) {
            var rect    = el.getBoundingClientRect();
            var popupW  = popup.offsetWidth  || 220;
            var popupH  = popup.offsetHeight || 80;
            var gap     = 8;
            var viewW   = window.innerWidth;
            var viewH   = window.innerHeight;

            // ── Horizontal: centre the popup over the hovered element ──
            var left = rect.left + (rect.width / 2) - (popupW / 2);

            // Keep inside viewport
            if (left < 6)              left = 6;
            if (left + popupW > viewW - 6) left = viewW - popupW - 6;

            // ── Vertical: prefer above, fall back to below ──
            var top;
            if (rect.top - popupH - gap >= 4) {
                // Enough room above → show above
                top = rect.top - popupH - gap;
            } else {
                // Not enough room above → show below
                top = rect.bottom + gap;
            }

            // Keep inside viewport vertically
            if (top < 4)                   top = 4;
            if (top + popupH > viewH - 4)  top = viewH - popupH - 4;

            popup.style.left = left + 'px';
            popup.style.top  = top  + 'px';
        }

        function hidePopup() {
            hideTimer = setTimeout(function () { popup.classList.remove('visible'); }, 120);
        }

        document.addEventListener('mouseover', function (e) {
            var el = e.target.closest('[data-full-title]');
            if (el) showPopup(el);
        });
        document.addEventListener('mouseout', function (e) {
            var el = e.target.closest('[data-full-title]');
            if (el) hidePopup();
        });
        document.addEventListener('scroll', function () { popup.classList.remove('visible'); }, true);
        window.addEventListener('resize', function () { popup.classList.remove('visible'); });
    })();

    /* =====================================================
       HELPERS
       ===================================================== */
    function showFullLoader() {
        var loader = document.querySelector('.full-loader');
        if (loader) loader.style.display = 'flex';
    }

    function hideFullLoader() {
        var loader = document.querySelector('.full-loader');
        if (loader) loader.style.display = 'none';
    }

    function formatDateToDDMMYYYY(date) {
        if (!date) return '';
        var d     = new Date(date);
        var day   = String(d.getDate()).padStart(2, '0');
        var month = String(d.getMonth() + 1).padStart(2, '0');
        var year  = d.getFullYear();
        return day + '/' + month + '/' + year;
    }

    function incrementVersion(currentVersion) {
        if (!currentVersion) return '1.0';
        var parts = currentVersion.split('.');
        var major = parseInt(parts[0]) || 0;
        return (major + 1) + '.0';
    }

    function escapeSpecialChars(str) {
        if (!str) return '';
        return str.replace(/[%_\\]/g, '\\$&');
    }

    /* =====================================================
       ✅ VERSION STATUS BAR UPDATER
       ===================================================== */
    function updateVersionStatusBar(version, fileName) {
        var bar          = document.getElementById('versionStatusBar');
        var statusLabel  = document.getElementById('versionStatusLabel');
        var statusValue  = document.getElementById('versionStatusValue');
        var statusFile   = document.getElementById('versionStatusFile');

        if (fileName) {
            // ✅ File selected — green state: "New Upload: Will be saved as v1.0"
            var nextVer = incrementVersion(version);
            bar.classList.add('has-file');
            statusLabel.textContent = 'New Upload:';
            statusValue.textContent = 'Will be saved as v' + nextVer;
            statusFile.textContent  = '';
        } else {
            // Default — teal state: "Current: v0.0 (No file selected)"
            bar.classList.remove('has-file');
            statusLabel.textContent = 'Current:';
            statusValue.textContent = 'v' + (version || '-');
            statusFile.textContent  = '(No file selected)';
        }
    }

    /* =====================================================
       PDF WATERMARK + DOWNLOAD
       ===================================================== */
    async function applyWatermarkToPdf(pdfBlob, fileName) {
        try {
            showFullLoader();
            var pdfLib   = window.PDFLib;
            var pdfBytes = await pdfBlob.arrayBuffer();
            var pdfDoc   = await pdfLib.PDFDocument.load(pdfBytes);
            var pages    = pdfDoc.getPages();
            for (var i = 0; i < pages.length; i++) {
                var page = pages[i];
                var sz   = page.getSize();
                page.drawText('CONTROLLED COPY', {
                    x: sz.width / 4 + 10, y: sz.height * 0.25 + 10,
                    size: 60, color: pdfLib.rgb(0.6, 0.6, 0.6), opacity: 0.15, rotate: pdfLib.degrees(45)
                });
            }
            var watermarkedBytes = await pdfDoc.save();
            var watermarkedBlob  = new Blob([watermarkedBytes], { type: 'application/pdf' });
            hideFullLoader();
            downloadBlob(watermarkedBlob, fileName);
            toastr.success('PDF downloaded with watermark');
        } catch (error) {
            hideFullLoader();
            toastr.error('Error applying watermark: ' + error.message);
        }
    }

    function downloadDocument() {
        if (!viewerState.documentId || !viewerState.fileName) { toastr.error('Document information is not available'); return; }
        var fileExtension = viewerState.fileName.split('.').pop().toLowerCase();
        if (fileExtension === 'pdf') {
            showFullLoader();
            var formData = new FormData();
            formData.append('action', 'downloadPdfWithWatermark');
            formData.append('document_id', viewerState.documentId);
            fetch('../api/documentMasterAPI.php', { method: 'POST', body: formData })
            .then(function (response) {
                if (!response.ok) throw new Error('Download failed');
                var hasAdminRights = response.headers.get('X-Has-Admin-Rights') === 'true';
                var applyWatermark = response.headers.get('X-Apply-Watermark') === 'true';
                return response.blob().then(function (blob) { return { blob: blob, hasAdminRights: hasAdminRights, applyWatermark: applyWatermark }; });
            })
            .then(async function (data) {
                if (data.applyWatermark && !data.hasAdminRights) { await applyWatermarkToPdf(data.blob, viewerState.fileName); }
                else { hideFullLoader(); downloadBlob(data.blob, viewerState.fileName); toastr.success('PDF downloaded successfully'); }
            })
            .catch(function (error) { hideFullLoader(); toastr.error('Error downloading PDF: ' + error.message); });
        } else {
            downloadDocumentRegular(viewerState.documentId, viewerState.fileName);
        }
    }

    function downloadBlob(blob, fileName) {
        var url = window.URL.createObjectURL(blob);
        var a   = document.createElement('a');
        a.href = url; a.download = fileName;
        document.body.appendChild(a); a.click();
        window.URL.revokeObjectURL(url); document.body.removeChild(a);
    }

    function downloadDocumentRegular(documentId, fileName) {
        showFullLoader();
        var formData = new FormData();
        formData.append('action', 'downloadDocument');
        formData.append('document_id', documentId);
        fetch('../api/documentMasterAPI.php', { method: 'POST', body: formData })
        .then(function (response) { if (!response.ok) throw new Error('Download failed'); return response.blob(); })
        .then(function (blob) { hideFullLoader(); downloadBlob(blob, fileName); toastr.success('Document downloaded successfully'); })
        .catch(function (error) { hideFullLoader(); toastr.error('Error downloading document: ' + error.message); });
    }

    /* =====================================================
       DOCUMENT VIEWER
       ===================================================== */
    function openDocumentViewer(documentId, fileName) {
        if (!documentId || !fileName) { toastr.error('Document information is missing'); return; }
        var fileExtension = fileName.split('.').pop().toLowerCase();
        if (['ppt', 'pptx', 'doc'].includes(fileExtension)) { downloadDocumentRegular(documentId, fileName); return; }

        var documentLoading        = document.getElementById('document-loading');
        var documentContentWrapper = document.getElementById('documentContentWrapper');
        documentContentWrapper.innerHTML     = '';
        documentContentWrapper.style.display = 'none';
        documentLoading.style.display        = 'flex';

        viewerState.currentPage    = 1;
        viewerState.totalPages     = 0;
        viewerState.fileName       = fileName;
        viewerState.documentId     = documentId;
        viewerState.pageCache      = {};
        viewerState.fileType       = fileExtension;
        viewerState.applyWatermark = !userPermissions.hasAdminRights && fileExtension === 'pdf';

        ['docFirstBtn','docPrevBtn','pageInfo','docNextBtn','docLastBtn','docZoomInBtn','docZoomOutBtn'].forEach(function (id) {
            document.getElementById(id).style.display = 'none';
        });
        document.getElementById('docDownloadBtn').style.display = 'flex';
        document.getElementById('viewerIcon').className = 'fa fa-file';

        var watermarkIndicator = document.getElementById('watermarkIndicator');
        watermarkIndicator.style.display = viewerState.applyWatermark ? 'inline-block' : 'none';

        document.getElementById('documentViewerTitle').textContent = fileName;
        if (fileExtension === 'pdf')                         document.getElementById('viewerIcon').className = 'fa fa-file-pdf-o';
        else if (['xlsx','xls'].includes(fileExtension))     document.getElementById('viewerIcon').className = 'fa fa-file-excel-o';
        else if (fileExtension === 'docx')                   document.getElementById('viewerIcon').className = 'fa fa-file-word-o';

        showFullLoader();
        var formData = new FormData();
        formData.append('action', 'viewDocumentFile');
        formData.append('document_id', documentId);

        fetch('../api/documentMasterAPI.php', { method: 'POST', body: formData })
        .then(function (response) { if (!response.ok) throw new Error('Failed to load file'); return response.blob(); })
        .then(function (blob) {
            hideFullLoader();
            var blobUrl = URL.createObjectURL(blob);
            viewerState.blobUrl = blobUrl;
            documentLoading.style.display        = 'none';
            documentContentWrapper.style.display = 'flex';

            if (fileExtension === 'pdf')                     loadPdfDocument(blobUrl, fileName);
            else if (['xlsx','xls'].includes(fileExtension)) loadExcelDocument(blob, fileName);
            else if (fileExtension === 'docx')               loadWordDocument(blob, fileName);
            else                                             showViewerError('Unsupported file format: ' + fileExtension);

            document.getElementById('documentViewerOverlay').classList.add('show');
            document.getElementById('documentViewerModal').classList.add('show');
        })
        .catch(function (error) {
            hideFullLoader();
            toastr.error('Error loading file: ' + error.message);
            documentLoading.style.display        = 'none';
            documentContentWrapper.style.display = 'flex';
            showViewerError('Error loading file: ' + error.message);
            document.getElementById('documentViewerOverlay').classList.add('show');
            document.getElementById('documentViewerModal').classList.add('show');
        });
    }

    function loadPdfDocument(pdfUrl, fileName) {
        var pdfjsLib = window.pdfjsLib;
        ['docFirstBtn','docPrevBtn','docNextBtn','docLastBtn','docZoomInBtn','docZoomOutBtn'].forEach(function (id) {
            document.getElementById(id).style.display = 'flex';
        });
        document.getElementById('pageInfo').style.display = 'block';
        document.getElementById('viewerIcon').className   = 'fa fa-file-pdf-o';

        pdfjsLib.getDocument(pdfUrl).promise.then(function (pdf) {
            viewerState.pdfDoc      = pdf;
            viewerState.totalPages  = pdf.numPages;
            viewerState.currentPage = 1;
            document.getElementById('documentViewerTitle').textContent = fileName || 'PDF Document';
            document.getElementById('docCurrentPage').textContent      = '1';
            document.getElementById('docTotalPages').textContent       = viewerState.totalPages;
            renderAllPagesSequentially();
            updateDocumentNavigationButtons();
        }).catch(function (error) { showViewerError('Failed to load PDF: ' + error.message); });
    }

    function loadExcelDocument(blob, fileName) {
        document.getElementById('viewerIcon').className = 'fa fa-file-excel-o';
        document.getElementById('documentViewerTitle').textContent = fileName || 'Excel Document';
        var reader = new FileReader();
        reader.onload = function (e) {
            try {
                var data     = new Uint8Array(e.target.result);
                var workbook = XLSX.read(data, { type: 'array' });
                var contentWrapper = document.getElementById('documentContentWrapper');
                contentWrapper.innerHTML     = '';
                contentWrapper.style.display = 'flex';
                var wrapper = document.createElement('div');
                wrapper.style.cssText = 'width:100%;overflow-x:auto;padding:20px;display:flex;justify-content:center;';
                var firstSheetName = workbook.SheetNames[0];
                var worksheet      = workbook.Sheets[firstSheetName];
                var jsonData       = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
                var table = document.createElement('table');
                table.style.borderCollapse = 'collapse';
                table.style.border         = '1px solid #ddd';
                jsonData.forEach(function (row, rowIndex) {
                    var tr = document.createElement('tr');
                    row.forEach(function (cell) {
                        var tag     = rowIndex === 0 ? 'th' : 'td';
                        var element = document.createElement(tag);
                        element.textContent     = cell || '';
                        element.style.border    = '1px solid #ddd';
                        element.style.padding   = '8px';
                        element.style.textAlign = 'center';
                        tr.appendChild(element);
                    });
                    table.appendChild(tr);
                });
                wrapper.appendChild(table);
                contentWrapper.appendChild(wrapper);
            } catch (error) { showViewerError('Failed to load Excel file: ' + error.message); }
        };
        reader.readAsArrayBuffer(blob);
    }

    function loadWordDocument(blob, fileName) {
        document.getElementById('viewerIcon').className = 'fa fa-file-word-o';
        document.getElementById('documentViewerTitle').textContent = fileName || 'Word Document';
        var reader = new FileReader();
        reader.onload = function (e) {
            try {
                var arrayBuffer = e.target.result;
                mammoth.convertToHtml({ arrayBuffer: arrayBuffer }).then(function (result) {
                    var contentWrapper = document.getElementById('documentContentWrapper');
                    contentWrapper.innerHTML     = '';
                    contentWrapper.style.display = 'flex';
                    contentWrapper.style.cssText += ';justify-content:flex-start;align-items:flex-start;padding:0;overflow:auto;flex-direction:column;';
                    var wordContainer = document.createElement('div');
                    wordContainer.className = 'word-document-content';
                    wordContainer.innerHTML = result.value;
                    wordContainer.querySelectorAll('*').forEach(function (el) {
                        el.style.display    = el.style.display || 'block';
                        el.style.visibility = 'visible';
                        el.style.opacity    = '1';
                        el.style.maxHeight  = 'none';
                        el.style.height     = 'auto';
                        el.style.overflow   = 'visible';
                    });
                    wordContainer.querySelectorAll('table').forEach(function (table) {
                        table.style.display        = 'table';
                        table.style.width          = '100%';
                        table.style.borderCollapse = 'collapse';
                        table.querySelectorAll('tr').forEach(function (tr) {
                            tr.style.display = 'table-row';
                            tr.querySelectorAll('td,th').forEach(function (cell) {
                                cell.style.display       = 'table-cell';
                                cell.style.padding       = '8px';
                                cell.style.border        = '1px solid #999';
                                cell.style.verticalAlign = 'top';
                            });
                        });
                    });
                    wordContainer.querySelectorAll('ul,ol').forEach(function (list) {
                        list.style.display = 'block';
                        list.style.margin  = '12px 0 12px 30px';
                        list.querySelectorAll('li').forEach(function (li) { li.style.display = 'list-item'; });
                    });
                    contentWrapper.appendChild(wordContainer);
                }).catch(function (error) { showViewerError('Failed to convert Word document: ' + error.message); });
            } catch (error) { showViewerError('Failed to load Word document: ' + error.message); }
        };
        reader.readAsArrayBuffer(blob);
    }

    function renderAllPagesSequentially() {
        var contentWrapper = document.getElementById('documentContentWrapper');
        contentWrapper.innerHTML = '';
        var pagesWrapper = document.createElement('div');
        pagesWrapper.className = 'document-pages-wrapper';
        pagesWrapper.id        = 'documentPagesWrapper';
        contentWrapper.appendChild(pagesWrapper);
        renderPageSequentially(1, pagesWrapper);
    }

    function renderPageSequentially(pageNum, container) {
        if (!viewerState.pdfDoc || pageNum > viewerState.totalPages) return;
        viewerState.pdfDoc.getPage(pageNum).then(function (page) {
            var viewport = page.getViewport({ scale: viewerState.scale });
            var canvas   = document.createElement('canvas');
            var context  = canvas.getContext('2d');
            canvas.width     = viewport.width;
            canvas.height    = viewport.height;
            canvas.className = 'document-canvas';
            page.render({ canvasContext: context, viewport: viewport }).promise.then(function () {
                var pageContainer = document.createElement('div');
                pageContainer.className = 'document-page-container';
                pageContainer.style.cssText = 'position:relative;width:' + viewport.width + 'px;height:' + viewport.height + 'px;';
                pageContainer.setAttribute('data-page-number', pageNum);
                pageContainer.appendChild(canvas);
                if (viewerState.applyWatermark) {
                    var wm = document.createElement('div');
                    wm.className = 'document-watermark-container';
                    wm.innerHTML = '<div class="document-watermark-text">CONTROLLED COPY</div>';
                    pageContainer.appendChild(wm);
                }
                var pn = document.createElement('div');
                pn.className   = 'document-page-number';
                pn.textContent = 'Page ' + pageNum;
                pageContainer.appendChild(pn);
                container.appendChild(pageContainer);
                viewerState.pageCache[pageNum] = true;
                if (pageNum < viewerState.totalPages) setTimeout(function () { renderPageSequentially(pageNum + 1, container); }, 50);
            }).catch(function () { if (pageNum < viewerState.totalPages) setTimeout(function () { renderPageSequentially(pageNum + 1, container); }, 50); });
        }).catch(function () { if (pageNum < viewerState.totalPages) setTimeout(function () { renderPageSequentially(pageNum + 1, container); }, 50); });
    }

    function updateDocumentNavigationButtons() {
        document.getElementById('docFirstBtn').disabled = viewerState.currentPage <= 1;
        document.getElementById('docPrevBtn').disabled  = viewerState.currentPage <= 1;
        document.getElementById('docNextBtn').disabled  = viewerState.currentPage >= viewerState.totalPages;
        document.getElementById('docLastBtn').disabled  = viewerState.currentPage >= viewerState.totalPages;
    }

    function showViewerError(message) {
        var contentWrapper = document.getElementById('documentContentWrapper');
        contentWrapper.innerHTML     = '<div class="document-error"><i class="fa fa-exclamation-triangle"></i> ' + message + '</div>';
        contentWrapper.style.display = 'flex';
    }

    function closeViewer() {
        document.getElementById('documentViewerOverlay').classList.remove('show');
        document.getElementById('documentViewerModal').classList.remove('show');
        if (viewerState.pdfDoc)  { viewerState.pdfDoc.destroy(); viewerState.pdfDoc = null; }
        if (viewerState.blobUrl) { URL.revokeObjectURL(viewerState.blobUrl); viewerState.blobUrl = null; }
        document.getElementById('documentContentWrapper').innerHTML     = '';
        document.getElementById('documentContentWrapper').style.display = 'none';
        document.getElementById('document-loading').style.display       = 'flex';
        viewerState.currentPage    = 1;
        viewerState.totalPages     = 0;
        viewerState.scale          = 1.2;
        viewerState.pageCache      = {};
        viewerState.fileType       = '';
        viewerState.applyWatermark = false;
    }

    /* =====================================================
       FILE UPLOAD
       ===================================================== */
    function initializeFileUpload() {
        var fileInput       = document.getElementById('editDocumentFile');
        var fileUploadLabel = document.querySelector('.file-upload-label');
        var fileNameDisplay = document.getElementById('editFileNameDisplay');
        var fileUploadText  = document.getElementById('editFileUploadText');

        fileUploadLabel.addEventListener('click', function (e) { e.preventDefault(); fileInput.click(); });
        fileInput.addEventListener('change', function (e) { handleFileSelection(e.target.files[0]); });
        fileUploadLabel.addEventListener('dragover',  function (e) { e.preventDefault(); e.stopPropagation(); fileUploadLabel.classList.add('drag-over'); });
        fileUploadLabel.addEventListener('dragleave', function (e) { e.preventDefault(); e.stopPropagation(); fileUploadLabel.classList.remove('drag-over'); });
        fileUploadLabel.addEventListener('drop', function (e) {
            e.preventDefault(); e.stopPropagation();
            fileUploadLabel.classList.remove('drag-over');
            var files = e.dataTransfer.files;
            if (files.length > 0) { handleFileSelection(files[0]); fileInput.files = files; }
        });

        function handleFileSelection(file) {
            document.getElementById('editDocumentFileError').textContent = '';
            fileNameDisplay.className = 'file-name-display';

            if (!file) {
                selectedFile = null;
                fileUploadText.textContent = 'Click to upload or drag and drop (PDF, Excel, Word, PowerPoint - Max 50MB)';
                updateVersionStatusBar($('#editVersionHidden').val(), null);
                return;
            }

            var fileExtension     = file.name.split('.').pop().toLowerCase();
            var allowedExtensions = ['pdf','xlsx','xls','ppt','pptx','doc','docx'];

            if (!allowedExtensions.includes(fileExtension)) {
                document.getElementById('editDocumentFileError').textContent = 'Only PDF, Excel, Word, and PowerPoint files are allowed';
                fileNameDisplay.textContent = '❌ Invalid file type.';
                fileNameDisplay.classList.add('error');
                selectedFile = null; fileInput.value = '';
                updateVersionStatusBar($('#editVersionHidden').val(), null);
                return;
            }
            if (file.size > MAX_FILE_SIZE) {
                document.getElementById('editDocumentFileError').textContent = 'File size must be less than 50MB';
                fileNameDisplay.textContent = '❌ File size exceeds 50MB limit.';
                fileNameDisplay.classList.add('error');
                selectedFile = null; fileInput.value = '';
                updateVersionStatusBar($('#editVersionHidden').val(), null);
                return;
            }

            selectedFile = file;
            fileUploadText.textContent  = '✓ ' + file.name + ' selected';
            fileNameDisplay.textContent = '✓ ' + file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
            fileNameDisplay.classList.add('success');

            // ✅ Switch status bar to green "New Upload" state
            updateVersionStatusBar($('#editVersionHidden').val(), file.name);
        }
    }

    /* =====================================================
       FILTER DROPDOWNS
       ===================================================== */
    function loadFilterOptions() {
        var actions = ['getDepartments','getDocTypes','getWITypes','getProducts'];
        var selects = ['filterDepartment','filterDocType','filterWIType','filterProduct'];
        actions.forEach(function (action, i) {
            $.ajax({
                url: '../api/documentMasterAPI.php', type: 'POST', dataType: 'json',
                data: { action: action },
                success: function (response) {
                    var select = $('#' + selects[i]);
                    response.forEach(function (item) {
                        select.append('<option value="' + item.value + '">' + item.label + '</option>');
                    });
                }
            });
        });
    }

    function fetchAllFilteredData() {
        return $.ajax({
            url: '../api/documentMasterAPI.php', type: 'POST', dataType: 'json',
            data: {
                action: 'getAllDocuments',
                department:       currentFilters.department,
                doc_type:         currentFilters.doc_type,
                wi_type:          currentFilters.wi_type,
                product:          currentFilters.product,
                document_number:  currentFilters.document_number,
                owner:            currentFilters.owner,
                issue_date_from:  currentFilters.issue_date_from,
                issue_date_to:    currentFilters.issue_date_to,
                review_date_from: currentFilters.review_date_from,
                review_date_to:   currentFilters.review_date_to
            }
        });
    }

    /* =====================================================
       EXPORT
       ===================================================== */
    function exportToExcel() {
        showFullLoader();
        fetchAllFilteredData().done(function (response) {
            if (!response || !Array.isArray(response)) { hideFullLoader(); toastr.error('No data available to export'); return; }
            var workbook  = new ExcelJS.Workbook();
            var worksheet = workbook.addWorksheet('Documents');
            var headers   = ['Department','Doc Type','Sequential No','Version','Owner','Issue Date','Next Review Date','Document Number','Title/Description','Create Date'];
            worksheet.addRow(headers);
            response.forEach(function (row) {
                worksheet.addRow([
                    row.department || '', row.doc_type || '', row.seq_no || '', row.version || '', row.owner || '',
                    row.issue_date       ? moment(row.issue_date).format('DD/MM/YYYY')       : '',
                    row.next_review_date ? moment(row.next_review_date).format('DD/MM/YYYY') : '',
                    row.document_number || '', row.description || '',
                    row.create_date ? moment(row.create_date).format('YYYY-MM-DD HH:mm:ss') : ''
                ]);
            });
            worksheet.columns = [{width:15},{width:12},{width:12},{width:10},{width:15},{width:15},{width:15},{width:20},{width:25},{width:20}];
            var headerRow = worksheet.getRow(1);
            headerRow.font      = { bold: true, size: 12, color: { argb: 'FFFFFF' } };
            headerRow.fill      = { type: 'pattern', pattern: 'solid', fgColor: { argb: '4472C4' } };
            headerRow.alignment = { horizontal: 'center', vertical: 'middle' };
            headerRow.height    = 25;
            worksheet.eachRow(function (row, rowNumber) {
                row.eachCell(function (cell) {
                    cell.border    = { top:{style:'thin'}, left:{style:'thin'}, bottom:{style:'thin'}, right:{style:'thin'} };
                    cell.alignment = { horizontal: 'center', vertical: 'middle' };
                    if (rowNumber > 1) cell.font = { size: 11 };
                });
            });
            workbook.xlsx.writeBuffer().then(function (buffer) {
                var blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                var url  = window.URL.createObjectURL(blob);
                var a    = document.createElement('a');
                a.href = url; a.download = 'Document_Details_' + moment().format('YYYY-MM-DD_HH-mm-ss') + '.xlsx';
                a.click(); window.URL.revokeObjectURL(url);
                hideFullLoader(); toastr.success('Excel exported successfully');
            });
        }).fail(function () { hideFullLoader(); toastr.error('Error fetching data for export'); });
    }

    function exportToPDF() {
        showFullLoader();
        fetchAllFilteredData().done(function (response) {
            if (!response || !Array.isArray(response)) { hideFullLoader(); toastr.error('No data available to export'); return; }
            var jsPDF      = window.jspdf.jsPDF;
            var doc        = new jsPDF('l', 'mm', 'a4');
            var pageWidth  = 297, pageHeight = 210, margin = 3;
            var usableWidth = pageWidth - (2 * margin);
            var headers  = ['Department','Doc Type','Seq No','Ver','Owner','Issue Date','Next Review Date','Document Number','Title/Description','Create Date'];
            var dataRows = response.map(function (row) {
                return [
                    row.department || '', row.doc_type || '', row.seq_no || '', row.version || '', row.owner || '',
                    row.issue_date       ? moment(row.issue_date).format('DD/MM/YYYY')       : '',
                    row.next_review_date ? moment(row.next_review_date).format('DD/MM/YYYY') : '',
                    row.document_number || '', row.description || '',
                    row.create_date ? moment(row.create_date).format('YYYY-MM-DD HH:mm:ss') : ''
                ];
            });
            doc.setFontSize(14); doc.setFont(undefined, 'bold');
            doc.text('Document Details Report', margin, margin + 3);
            doc.setFontSize(9); doc.setFont(undefined, 'normal');
            doc.text('Generated on: ' + moment().format('DD/MM/YYYY HH:mm:ss'), margin, margin + 8);
            doc.autoTable({
                head: [headers], body: dataRows,
                startY: margin + 13,
                margin: { top: margin, right: margin, bottom: margin, left: margin },
                tableWidth: usableWidth,
                headStyles: { fillColor:[68,114,196], textColor:[255,255,255], fontStyle:'bold', fontSize:8, halign:'center', valign:'middle', cellPadding:1.5, lineColor:[0,0,0], lineWidth:0.3, minCellHeight:6 },
                bodyStyles: { fontSize:7, cellPadding:1, lineColor:[180,180,180], lineWidth:0.2, textColor:[0,0,0], valign:'middle', minCellHeight:5 },
                alternateRowStyles: { fillColor:[245,245,245] },
                didDrawPage: function (data) {
                    var totalPages = doc.internal.pages.length - 1;
                    doc.setFontSize(7); doc.setTextColor(128);
                    doc.text('Page ' + data.pageNumber + ' of ' + totalPages, pageWidth / 2, pageHeight - 2, { align: 'center' });
                },
                theme: 'grid', tableLineColor:[0,0,0], tableLineWidth:0.2
            });
            doc.save('Document_Details_' + moment().format('YYYY-MM-DD_HH-mm-ss') + '.pdf');
            hideFullLoader(); toastr.success('PDF exported successfully');
        }).fail(function () { hideFullLoader(); toastr.error('Error fetching data for export'); });
    }

    /* =====================================================
       DATE RANGE PICKERS
       ===================================================== */
    function initializeDateRangePicker() {
        var commonConfig = {
            locale: {
                format: 'YYYY-MM-DD', separator: ' to ', applyLabel: 'Apply', cancelLabel: 'Clear',
                fromLabel: 'From', toLabel: 'To', customRangeLabel: 'Custom', weekLabel: 'W',
                daysOfWeek: ['Su','Mo','Tu','We','Th','Fr','Sa'],
                monthNames: ['January','February','March','April','May','June','July','August','September','October','November','December'],
                firstDay: 1
            },
            autoUpdateInput: false, opens: 'left', drops: 'down', showDropdowns: true,
            minYear: 2000, maxYear: parseInt(moment().format('YYYY'), 10) + 10,
            showWeekNumbers: false, showISOWeekNumbers: false, timePicker: false,
            ranges: {
                'Today':       [moment(), moment()],
                'Yesterday':   [moment().subtract(1,'days'), moment().subtract(1,'days')],
                'Last 7 Days': [moment().subtract(6,'days'), moment()],
                'Last 30 Days':[moment().subtract(29,'days'), moment()],
                'This Month':  [moment().startOf('month'), moment().endOf('month')],
                'Last Month':  [moment().subtract(1,'month').startOf('month'), moment().subtract(1,'month').endOf('month')],
                'This Year':   [moment().startOf('year'), moment().endOf('year')]
            },
            alwaysShowCalendars: true, linkedCalendars: false
        };

        $('#filterIssueDateRange').daterangepicker(commonConfig);
        $('#filterIssueDateRange').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
        });
        $('#filterIssueDateRange').on('cancel.daterangepicker', function () { $(this).val(''); });

        $('#filterReviewDateRange').daterangepicker(commonConfig);
        $('#filterReviewDateRange').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' to ' + picker.endDate.format('YYYY-MM-DD'));
        });
        $('#filterReviewDateRange').on('cancel.daterangepicker', function () { $(this).val(''); });
    }

    function injectExportButtons() {
        if ($('#exportButtonsContainer').length === 0) {
            var exportHtml = '<div id="exportButtonsContainer">' +
                '<button type="button" class="export-btn export-btn-excel" id="exportExcelBtn" title="Export to Excel"><i class="fa fa-file-excel-o"></i> Excel</button>' +
                '<button type="button" class="export-btn export-btn-pdf"   id="exportPdfBtn"   title="Export to PDF"><i class="fa fa-file-pdf-o"></i> PDF</button>' +
                '</div>';
            $('#resultsTable_wrapper').find('.dataTables_length').after(exportHtml);
            $(document).on('click', '#exportExcelBtn', function () { exportToExcel(); });
            $(document).on('click', '#exportPdfBtn',   function () { exportToPDF(); });
        }
    }

    function initializeEditIssueDatePicker() {
        var dateInput = $('#editIssueDate');
        if (dateInput.hasClass('hasDatepicker')) dateInput.datepicker('destroy');
        try {
            dateInput.datepicker({
                dateFormat: 'dd/mm/yy', changeMonth: false, changeYear: false, yearRange: '-100:+10',
                onSelect: function () { document.getElementById('editIssueDateError').textContent = ''; },
                beforeShow: function (input, inst) { setTimeout(function () { inst.dpDiv.css('z-index', 99999); }, 0); }
            });
        } catch (e) { console.error('Issue Date Datepicker error:', e.message); }
    }

    function initializeEditNextReviewDatePicker() {
        var dateInput = $('#editNextReviewDate');
        if (dateInput.hasClass('hasDatepicker')) dateInput.datepicker('destroy');
        try {
            dateInput.datepicker({
                dateFormat: 'dd/mm/yy', changeMonth: false, changeYear: false, yearRange: '-100:+10',
                onSelect: function () { document.getElementById('editNextReviewDateError').textContent = ''; },
                beforeShow: function (input, inst) { setTimeout(function () { inst.dpDiv.css('z-index', 99999); }, 0); }
            });
        } catch (e) { console.error('Next Review Date Datepicker error:', e.message); }
    }

    /* =====================================================
       DOCUMENT READY
       ===================================================== */
    $(document).ready(function () {
        initializeFileUpload();
        loadFilterOptions();
        initializeDateRangePicker();

        dataTable = $('#resultsTable').DataTable({
            processing: true,
            language: { processing: '<div style="display:none;"></div>' },
            serverSide: true,
            pageLength: 10,
            lengthMenu: [[10,25,50,100],[10,25,50,100]],
            order: [[5,'desc']],
            scrollX: true,
            scrollY: '75vh',
            scrollCollapse: true,
            fixedHeader: false,
            dom: 'lBfrtip',
            buttons: [],
            ajax: {
                url: '../api/documentMasterAPI.php',
                type: 'POST',
                data: function (d) {
                    d.action           = 'getDocuments';
                    d.department       = currentFilters.department;
                    d.doc_type         = currentFilters.doc_type;
                    d.wi_type          = currentFilters.wi_type;
                    d.product          = currentFilters.product;
                    d.document_number  = currentFilters.document_number;
                    d.owner            = currentFilters.owner;
                    d.issue_date_from  = currentFilters.issue_date_from;
                    d.issue_date_to    = currentFilters.issue_date_to;
                    d.review_date_from = currentFilters.review_date_from;
                    d.review_date_to   = currentFilters.review_date_to;
                },
                dataSrc: function (json) {
                    if (json.data && Array.isArray(json.data)) {
                        json.data = json.data.filter(function (row) {
                            return Object.values(row).some(function (v) { return v !== null && v !== undefined && v !== ''; });
                        });
                    }
                    return json.data;
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    toastr.error('Error loading table data');
                }
            },
            columns: [
                { data: 'department',      searchable: true, className: 'text-center' },
                { data: 'doc_type',        searchable: true, className: 'text-center' },
                { data: 'seq_no',          searchable: true, className: 'text-center' },
                { data: 'version',         searchable: true, className: 'text-center' },
                { data: 'owner',           searchable: true, className: 'text-center' },
                {
                    data: 'issue_date', searchable: true, className: 'text-center',
                    render: function (data) { return data ? moment(data).format('DD/MM/YYYY') : '-'; }
                },
                {
                    data: 'next_review_date', searchable: true, className: 'text-center',
                    render: function (data) { return data ? moment(data).format('DD/MM/YYYY') : '-'; }
                },
                { data: 'document_number', searchable: true, className: 'text-center' },
                {
                    data: 'description', searchable: true, className: 'text-center',
                    render: function (data) {
                        if (!data) return '-';
                        var truncated = data.length > 25 ? data.substring(0, 25) + '...' : data;
                        var safe = data.replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                        return '<span class="title-truncated" data-full-title="' + safe + '">' + truncated + '</span>';
                    }
                },
                {
                    data: null, orderable: false, searchable: false, className: 'text-center',
                    render: function (data) {
                        var actionHtml = '<div class="action-buttons">';
                        if (userPermissions.hasEditRights) {
                            actionHtml += '<button class="action-btn edit-btn" onclick="editDocument(' + data.id + ')" title="Edit Document"><i class="fa fa-edit"></i></button>';
                        }
                        if (data.id && data.actual_file_name) {
                            var fileExt     = data.actual_file_name.split('.').pop().toLowerCase();
                            var iconClass   = 'fa-file';
                            var tooltipText = 'View/Download Document';
                            if (fileExt === 'pdf')                         { iconClass = 'fa-file-pdf-o';        tooltipText = 'View PDF (with watermark)'; }
                            else if (['xlsx','xls'].includes(fileExt))     { iconClass = 'fa-file-excel-o';      tooltipText = 'View Excel'; }
                            else if (['ppt','pptx'].includes(fileExt))     { iconClass = 'fa-file-powerpoint-o'; tooltipText = 'Download PowerPoint'; }
                            else if (['doc','docx'].includes(fileExt))     { iconClass = 'fa-file-word-o';       tooltipText = fileExt === 'docx' ? 'View Word' : 'Download Word'; }
                            actionHtml += '<button class="action-btn download-btn" onclick="openDocumentViewer(' + data.id + ', \'' + data.actual_file_name + '\')" title="' + tooltipText + '"><i class="fa ' + iconClass + '"></i></button>';
                        }
                        actionHtml += '</div>';
                        return actionHtml;
                    }
                }
            ],
            responsive: false,
            autoWidth: false,
            columnDefs: [{ targets: '_all', className: 'dt-head-center dt-body-center' }],
            initComplete: function () { injectExportButtons(); },
            drawCallback: function () { $(window).trigger('resize'); }
        });

        window.downloadDocument = function () {
            if (!viewerState.documentId || !viewerState.fileName) { toastr.error('Document information is not available'); return; }
            var fileExtension = viewerState.fileName.split('.').pop().toLowerCase();
            if (fileExtension === 'pdf') {
                showFullLoader();
                var formData = new FormData();
                formData.append('action', 'downloadPdfWithWatermark');
                formData.append('document_id', viewerState.documentId);
                fetch('../api/documentMasterAPI.php', { method: 'POST', body: formData })
                .then(function (response) {
                    if (!response.ok) throw new Error('Download failed');
                    var hasAdminRights = response.headers.get('X-Has-Admin-Rights') === 'true';
                    var applyWatermark = response.headers.get('X-Apply-Watermark') === 'true';
                    return response.blob().then(function (blob) { return { blob:blob, hasAdminRights:hasAdminRights, applyWatermark:applyWatermark }; });
                })
                .then(async function (data) {
                    if (data.applyWatermark && !data.hasAdminRights) { await applyWatermarkToPdf(data.blob, viewerState.fileName); }
                    else { hideFullLoader(); downloadBlob(data.blob, viewerState.fileName); toastr.success('PDF downloaded successfully'); }
                })
                .catch(function (error) { hideFullLoader(); toastr.error('Error downloading PDF: ' + error.message); });
            } else {
                downloadDocumentRegular(viewerState.documentId, viewerState.fileName);
            }
        };

        $(window).on('resize', function () {
            clearTimeout(window.resizeTimeout);
            window.resizeTimeout = setTimeout(function () { if (dataTable) dataTable.columns.adjust(); }, 100);
        });

        $('#applyFiltersBtn').click(function () {
            var issueDateRange = $('#filterIssueDateRange').val().trim();
            var issueDateFrom = '', issueDateTo = '';
            if (issueDateRange) {
                var dates = issueDateRange.split(' to ');
                if (dates.length === 2) {
                    issueDateFrom = dates[0].trim(); issueDateTo = dates[1].trim();
                    var dateRegex = /^\d{4}-\d{2}-\d{2}$/;
                    if (!dateRegex.test(issueDateFrom) || !dateRegex.test(issueDateTo)) { toastr.error('Issue Date: Please use YYYY-MM-DD format'); return; }
                } else if (issueDateRange !== '') { toastr.error('Issue Date: Please use format YYYY-MM-DD to YYYY-MM-DD'); return; }
            }
            var reviewDateRange = $('#filterReviewDateRange').val().trim();
            var reviewDateFrom = '', reviewDateTo = '';
            if (reviewDateRange) {
                var rdates = reviewDateRange.split(' to ');
                if (rdates.length === 2) {
                    reviewDateFrom = rdates[0].trim(); reviewDateTo = rdates[1].trim();
                    var rdateRegex = /^\d{4}-\d{2}-\d{2}$/;
                    if (!rdateRegex.test(reviewDateFrom) || !rdateRegex.test(reviewDateTo)) { toastr.error('Review Date: Please use YYYY-MM-DD format'); return; }
                } else if (reviewDateRange !== '') { toastr.error('Review Date: Please use format YYYY-MM-DD to YYYY-MM-DD'); return; }
            }
            currentFilters = {
                department:       escapeSpecialChars($('#filterDepartment').val().trim()),
                doc_type:         escapeSpecialChars($('#filterDocType').val().trim()),
                wi_type:          escapeSpecialChars($('#filterWIType').val().trim()),
                product:          escapeSpecialChars($('#filterProduct').val().trim()),
                document_number:  escapeSpecialChars($('#filterDocumentNumber').val().trim()),
                owner:            escapeSpecialChars($('#filterOwner').val().trim()),
                issue_date_from:  issueDateFrom, issue_date_to: issueDateTo,
                review_date_from: reviewDateFrom, review_date_to: reviewDateTo
            };
            dataTable.ajax.reload();
            toastr.success('Filters applied');
        });

        $('#clearFiltersBtn').click(function () {
            $('#filterDepartment,#filterDocType,#filterWIType,#filterProduct,#filterDocumentNumber,#filterOwner,#filterIssueDateRange,#filterReviewDateRange').val('');
            currentFilters = { department:'', doc_type:'', wi_type:'', product:'', document_number:'', owner:'', issue_date_from:'', issue_date_to:'', review_date_from:'', review_date_to:'' };
            dataTable.ajax.reload();
            toastr.success('Filters cleared');
        });

        document.getElementById('docFirstBtn').addEventListener('click', function () {
            if (viewerState.currentPage > 1) { viewerState.currentPage = 1; document.getElementById('documentViewerContainer').scrollTop = 0; updateDocumentNavigationButtons(); }
        });
        document.getElementById('docPrevBtn').addEventListener('click', function () {
            if (viewerState.currentPage > 1) { viewerState.currentPage--; document.getElementById('docCurrentPage').textContent = viewerState.currentPage; scrollToPage(viewerState.currentPage); updateDocumentNavigationButtons(); }
        });
        document.getElementById('docNextBtn').addEventListener('click', function () {
            if (viewerState.currentPage < viewerState.totalPages) { viewerState.currentPage++; document.getElementById('docCurrentPage').textContent = viewerState.currentPage; scrollToPage(viewerState.currentPage); updateDocumentNavigationButtons(); }
        });
        document.getElementById('docLastBtn').addEventListener('click', function () {
            if (viewerState.currentPage < viewerState.totalPages) {
                viewerState.currentPage = viewerState.totalPages;
                document.getElementById('docCurrentPage').textContent = viewerState.totalPages;
                document.getElementById('documentViewerContainer').scrollTop = document.getElementById('documentViewerContainer').scrollHeight;
                updateDocumentNavigationButtons();
            }
        });
        document.getElementById('docZoomInBtn').addEventListener('click',  function () { viewerState.scale += 0.2; renderAllPagesSequentially(); });
        document.getElementById('docZoomOutBtn').addEventListener('click', function () { if (viewerState.scale > 0.5) { viewerState.scale -= 0.2; renderAllPagesSequentially(); } });
        document.getElementById('docDownloadBtn').addEventListener('click', function () { downloadDocument(); });
        document.getElementById('docCloseBtn').addEventListener('click',    function () { closeViewer(); });
        document.getElementById('documentViewerOverlay').addEventListener('click', function (event) { if (event.target === this) closeViewer(); });

        function scrollToPage(pageNum) {
            var container = document.getElementById('documentContentWrapper');
            var pages     = container.querySelectorAll('[data-page-number]');
            for (var i = 0; i < pages.length; i++) {
                if (parseInt(pages[i].getAttribute('data-page-number')) === pageNum) {
                    pages[i].scrollIntoView({ behavior: 'smooth', block: 'start' });
                    break;
                }
            }
        }

        window.editDocument = function (id) {
            showFullLoader();
            $.ajax({
                url: '../api/documentMasterAPI.php', type: 'POST', dataType: 'json',
                data: { action: 'getDocument', id: id },
                success: function (response) {
                    hideFullLoader();
                    if (response.success) {
                        var doc = response.data;
                        $('#documentId').val(id);
                        $('#editDepartment').text(doc.department);        $('#editDepartmentHidden').val(doc.department);
                        $('#editDocType').text(doc.doc_type);             $('#editDocTypeHidden').val(doc.doc_type);
                        $('#editSeqNo').text(doc.seq_no);                 $('#editSeqNoHidden').val(doc.seq_no);
                        $('#editOwner').text(doc.owner);                  $('#editOwnerHidden').val(doc.owner);

                        var issueDateValue      = formatDateToDDMMYYYY(doc.issue_date);
                        var nextReviewDateValue = formatDateToDDMMYYYY(doc.next_review_date);
                        $('#editIssueDate').val(issueDateValue);
                        $('#editNextReviewDate').val(nextReviewDateValue);

                        originalDocumentData = {
                            issue_date: issueDateValue, next_review_date: nextReviewDateValue,
                            description: doc.description, remark: doc.remark
                        };

                        // ✅ Set version box text + reset status bar to default (no file)
                        $('#editVersion').text(doc.version || '-');
                        $('#editVersionHidden').val(doc.version);
                        updateVersionStatusBar(doc.version, null);

                        $('#editDocumentNumber').text(doc.document_number); $('#editDocumentNumberHidden').val(doc.document_number);
                        $('#editDescription').val(doc.description);
                        $('#editRemark').val(doc.remark);

                        selectedFile = null;
                        document.getElementById('editDocumentFile').value        = '';
                        document.getElementById('editFileNameDisplay').className = 'file-name-display';
                        document.getElementById('editFileUploadText').textContent = 'Click to upload or drag and drop (PDF, Excel, Word, PowerPoint - Max 50MB)';
                        ['editDocumentFileError','editDescriptionError','editIssueDateError','editNextReviewDateError'].forEach(function (eid) {
                            document.getElementById(eid).textContent = '';
                        });

                        $('#editModal').modal('show');
                        setTimeout(function () { initializeEditIssueDatePicker(); initializeEditNextReviewDatePicker(); }, 500);
                    } else {
                        toastr.error(response.message || 'Failed to load document');
                    }
                },
                error: function () { hideFullLoader(); toastr.error('Error loading document'); }
            });
        };

        $('#updateButton').click(function () {
            ['editDocumentFileError','editDescriptionError','editRemarkError','editIssueDateError','editNextReviewDateError'].forEach(function (eid) {
                document.getElementById(eid).textContent = '';
            });

            var isValid = true;
            if (!$('#editDescription').val().trim()) {
                isValid = false;
                document.getElementById('editDescriptionError').textContent = 'Title/Description is required';
            }

            var issueDateValue = $('#editIssueDate').val().trim();
            if (!issueDateValue) {
                isValid = false;
                document.getElementById('editIssueDateError').textContent = 'Issue Date is required';
            } else if (!/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[012])\/\d{4}$/.test(issueDateValue)) {
                isValid = false;
                document.getElementById('editIssueDateError').textContent = 'Invalid date format. Use DD/MM/YYYY';
            }

            var nextReviewDateValue = $('#editNextReviewDate').val().trim();
            if (nextReviewDateValue && !/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[012])\/\d{4}$/.test(nextReviewDateValue)) {
                isValid = false;
                document.getElementById('editNextReviewDateError').textContent = 'Invalid date format. Use DD/MM/YYYY';
            }

            if (!isValid) { toastr.error('Please fill all required fields correctly'); return; }

            showFullLoader();
            var newVersion = $('#editVersionHidden').val();
            if (selectedFile) newVersion = incrementVersion(newVersion);

            var ip = issueDateValue.split('/');
            var issueDateForDB = ip[2] + '-' + ip[1] + '-' + ip[0];
            var nextReviewDateForDB = '';
            if (nextReviewDateValue) {
                var np = nextReviewDateValue.split('/');
                nextReviewDateForDB = np[2] + '-' + np[1] + '-' + np[0];
            }

            var formData = new FormData();
            formData.append('action',           'updateDocument');
            formData.append('id',               $('#documentId').val());
            formData.append('department',       $('#editDepartmentHidden').val());
            formData.append('doc_type',         $('#editDocTypeHidden').val());
            formData.append('seq_no',           $('#editSeqNoHidden').val());
            formData.append('version',          newVersion);
            formData.append('owner',            $('#editOwnerHidden').val());
            formData.append('issue_date',       issueDateForDB);
            formData.append('next_review_date', nextReviewDateForDB);
            formData.append('document_number',  $('#editDocumentNumberHidden').val());
            formData.append('description',      $('#editDescription').val().trim());
            formData.append('remark',           $('#editRemark').val().trim());
            if (selectedFile) formData.append('document_file', selectedFile);

            $.ajax({
                url: '../api/documentMasterAPI.php', type: 'POST', dataType: 'json',
                data: formData, processData: false, contentType: false,
                success: function (response) {
                    hideFullLoader();
                    if (response.success) {
                        toastr.success(selectedFile ? 'Document updated successfully with version ' + newVersion : 'Document updated successfully');
                        $('#editModal').modal('hide');
                        dataTable.ajax.reload();
                    } else {
                        toastr.error(response.message || 'Failed to update document');
                    }
                },
                error: function () { hideFullLoader(); toastr.error('Error updating document'); }
            });
        });

        $('#editModal').on('hidden.bs.modal', function () {
            selectedFile = null;
            document.getElementById('editDocumentFile').value        = '';
            document.getElementById('editFileNameDisplay').className = 'file-name-display';
            document.getElementById('editFileUploadText').textContent = 'Click to upload or drag and drop (PDF, Excel, Word, PowerPoint - Max 50MB)';
            ['editDocumentFileError','editDescriptionError','editRemarkError','editIssueDateError','editNextReviewDateError'].forEach(function (eid) {
                document.getElementById(eid).textContent = '';
            });
            $('#editIssueDate').val('');
            $('#editNextReviewDate').val('');
            // ✅ Reset version status bar on modal close
            updateVersionStatusBar('-', null);
            originalDocumentData = { issue_date:'', next_review_date:'', description:'', remark:'' };
        });
    });
</script>
</body>
</html>