<!DOCTYPE html>
<html>
<?php
include_once '../core/index.php';
$check = SharedManager::checkAuthToModule(23);
$menu_header_display = 'Document Master';
?>
<head>
    <title>OneX | Document Master</title>
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
    
    <!-- ✅ JQUERY UI CSS FOR DATEPICKER -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    
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
    
    <!-- ✅ JQUERY UI JS FOR DATEPICKER -->
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
</head>

<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    html, body {
        height: 100%;
        width: 100%;
        overflow: hidden;
    }

    #wrapper {
        display: flex;
        flex-direction: column;
        height: 100vh;
        width: 100vw;
    }

    #page-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background-color: #f3f3f4;
    }

    .row.border-bottom {
        flex-shrink: 0;
    }

    .gray-bg {
        display: flex;
        flex-direction: column;
        height: 100%;
        overflow: hidden;
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

    .ui.fixed.menu {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 100;
        width: 100%;
        margin: 0;
    }

    .ui.container {
        max-width: 100% !important;
        width: 100%;
        padding: 0 15px;
    }

    .form-container {
        background: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 100%;
        flex-shrink: 0;
    }

    .form-section-title {
        font-size: 16px;
        font-weight: bold;
        color: #00b5ad;
        margin-bottom: 12px;
        margin-top: 24px;
        border-bottom: 2px solid #00b5ad;
        padding-bottom: 4px;
    }

    .form-section-title:first-of-type {
        margin-top: 0;
    }

    .form-group {
        margin-bottom: 0px;
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

    .form-group input::placeholder,
    .dropdown-wrapper input::placeholder,
    #product::placeholder {
        color: #5a6268;
        opacity: 1;
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
        resize: none;
        min-height: 60px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 12px;
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

    .form-actions {
        margin-top: 10px;
        display: flex;
        gap: 8px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-submit,
    .btn-reset {
        padding: 8px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: background-color 0.3s ease;
        white-space: nowrap;
    }

    .btn-submit {
        background-color: #00b5ad;
        color: white;
    }

    .btn-submit:hover {
        background-color: #008b87;
    }

    .btn-reset {
        background-color: #6c757d;
        color: white;
    }

    .btn-reset:hover {
        background-color: #5a6268;
    }

    .required-field {
        color: red;
    }

    .error-message {
        color: #dc3545;
        font-size: 11px;
        margin-top: 2px;
    }

    .success-message {
        color: #28a745;
        font-size: 11px;
        margin-top: 2px;
    }

    .file-format-hint {
        color: #666;
        font-size: 10px;
        margin-top: 2px;
        font-style: italic;
    }

    .seq-no-display {
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
    }

    .document-number-display {
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

    .dropdown-wrapper {
        position: relative;
        width: 100%;
    }

    .dropdown-wrapper input {
        padding-right: 30px;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%2300b5ad" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>');
        background-repeat: no-repeat;
        background-position: right 8px center;
        background-size: 16px 16px;
    }

    .dropdown-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 4px 4px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        display: none;
    }

    .dropdown-list.show {
        display: block;
    }

    .dropdown-list .dropdown-item {
        padding: 8px 10px;
        cursor: pointer;
        font-size: 12px;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s ease;
    }

    .dropdown-list .dropdown-item:last-child {
        border-bottom: none;
    }

    .dropdown-list .dropdown-item:hover {
        background-color: #00b5ad;
        color: white;
    }

    .dropdown-list .dropdown-item.new-item {
        background-color: #f9f9f9;
        font-weight: 600;
        color: #00b5ad;
        border-top: 1px solid #ddd;
    }

    .dropdown-list .dropdown-item.new-item:hover {
        background-color: #00b5ad;
        color: white;
    }

    .dropdown-list .dropdown-item.no-results {
        padding: 10px;
        text-align: center;
        color: #999;
        cursor: default;
    }

    .dropdown-list .dropdown-item.no-results:hover {
        background-color: transparent;
        color: #999;
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
        padding: 8px;
        border: 2px dashed #00b5ad;
        border-radius: 4px;
        background-color: #f9f9f9;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 11px;
        color: #00b5ad;
        font-weight: 600;
        min-height: 32px;
        gap: 4px;
    }

    .file-upload-label:hover {
        background-color: #f0f0f0;
        border-color: #008b87;
    }

    .file-upload-label.drag-over {
        background-color: #e8f5f4;
        border-color: #008b87;
    }

    .file-upload-label-main {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    .file-upload-label-formats {
        font-size: 9px;
        color: #666;
        font-weight: normal;
        font-style: italic;
        text-align: center;
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
        display: flex;
        align-items: center;
        word-break: break-all;
        margin-top: 6px;
        justify-content: space-between;
    }

    .file-name-display.success {
        color: #28a745;
        border-color: #28a745;
    }

    .file-name-display.error {
        color: #dc3545;
        border-color: #dc3545;
    }

    .file-upload-icon {
        font-size: 14px;
        flex-shrink: 0;
    }

    .clear-file-btn {
        background-color: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        min-width: 24px;
        padding: 0;
        cursor: pointer;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.3s ease;
        margin-left: 10px;
        flex-shrink: 0;
    }

    .clear-file-btn:hover {
        background-color: #c82333;
    }

    .file-name-display.success .clear-file-btn {
        background-color: #28a745;
    }

    .file-name-display.success .clear-file-btn:hover {
        background-color: #218838;
    }

    .content-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        padding-top: 0;
    }

    .form-wrapper {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 50px 10px;
    }

    .form-wrapper::-webkit-scrollbar {
        width: 6px;
    }

    .form-wrapper::-webkit-scrollbar-track {
        background: transparent;
    }

    .form-wrapper::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .form-wrapper::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .owner-input-wrapper {
        position: relative;
        width: 100%;
    }

    .owner-input-wrapper input {
        padding-right: 30px;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="%2300b5ad" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>');
        background-repeat: no-repeat;
        background-position: right 8px center;
        background-size: 16px 16px;
    }

    .owner-dropdown-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 4px 4px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        display: none;
    }

    .owner-dropdown-list.show {
        display: block;
    }

    .owner-dropdown-list .owner-dropdown-item {
        padding: 8px 10px;
        cursor: pointer;
        font-size: 12px;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s ease;
    }

    .owner-dropdown-list .owner-dropdown-item:last-child {
        border-bottom: none;
    }

    .owner-dropdown-list .owner-dropdown-item:hover {
        background-color: #00b5ad;
        color: white;
    }

    .owner-dropdown-list .owner-dropdown-item.no-results {
        padding: 10px;
        text-align: center;
        color: #999;
        cursor: default;
    }

    .owner-dropdown-list .owner-dropdown-item.no-results:hover {
        background-color: transparent;
        color: #999;
    }

    .empty-form-group {
        visibility: hidden;
    }

    /* ✅ MULTIPLE SELECT PRODUCT DROPDOWN STYLES */
    .product-select-wrapper {
        position: relative;
        width: 100%;
    }

    .product-select-display {
        width: 100%;
        padding: 6px 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background-color: white;
        cursor: pointer;
        font-size: 12px;
        font-family: Arial, sans-serif;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 32px;
        box-sizing: border-box;
        flex-wrap: wrap;
        gap: 6px;
        transition: all 0.2s ease;
    }

    .product-select-display:hover {
        border-color: #00b5ad;
    }

    .product-select-display:focus {
        outline: none;
        border-color: #00b5ad;
        box-shadow: 0 0 5px rgba(0, 181, 173, 0.3);
    }

    .product-select-display.disabled {
        background-color: #f5f5f5;
        cursor: not-allowed;
        color: #00b5ad;
        font-weight: 600;
    }

    .product-selected-items {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        flex: 1;
        align-items: center;
    }

    .product-tag {
        background-color: #00b5ad;
        color: white;
        padding: 4px 10px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        box-shadow: 0 2px 4px rgba(0, 181, 173, 0.2);
    }

    .product-tag-remove {
        cursor: pointer;
        font-weight: bold;
        transition: opacity 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        background-color: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
    }

    .product-tag-remove:hover {
        background-color: rgba(255, 255, 255, 0.6);
    }

    .product-select-placeholder {
        color: #5a6268;
        font-style: italic;
        font-size: 12px;
    }

    .product-dropdown-arrow {
        font-size: 14px;
        color: #00b5ad;
        flex-shrink: 0;
        transition: transform 0.2s ease;
    }

    .product-dropdown-arrow.open {
        transform: rotate(180deg);
    }

    .product-dropdown-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 4px 4px;
        max-height: 250px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        display: none;
        margin-top: -1px;
    }

    .product-dropdown-list.show {
        display: block;
    }

    .product-dropdown-item {
        padding: 10px 12px;
        cursor: pointer;
        font-size: 12px;
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .product-dropdown-item:last-child {
        border-bottom: none;
    }

    .product-dropdown-item:hover {
        background-color: #f0f0f0;
    }

    .product-dropdown-item.selected {
        background-color: #e8f5f4;
        color: #00b5ad;
        font-weight: 600;
    }

    .product-dropdown-item.selected::before {
        color: #00b5ad;
        font-weight: bold;
        font-size: 14px;
    }

    .product-dropdown-item input[type="checkbox"] {
        cursor: pointer;
        width: 16px;
        height: 16px;
        accent-color: #00b5ad;
        margin: 0;
    }

    .product-dropdown-item.no-results {
        padding: 10px;
        text-align: center;
        color: #999;
        cursor: default;
    }

    .product-dropdown-item.no-results:hover {
        background-color: transparent;
        color: #999;
    }

    /* ✅ JQUERY UI DATEPICKER STYLES - MATCHING manual_details.php */
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

    @media (max-width: 1024px) {
        .form-row {
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .form-row.document-upload-row {
            grid-template-columns: 1fr;
        }

        .form-container {
            padding: 12px;
        }
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .form-row.document-upload-row {
            grid-template-columns: 1fr;
        }

        .form-container {
            padding: 10px;
        }

        .form-wrapper {
            padding: 8px;
        }

        .form-group label {
            font-size: 11px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            font-size: 11px;
            padding: 5px;
        }

        .btn-submit,
        .btn-reset {
            padding: 6px 15px;
            font-size: 12px;
        }

        .file-upload-label {
            font-size: 10px;
            padding: 6px;
            min-height: 28px;
        }

        .file-upload-icon {
            font-size: 12px;
        }

        .file-upload-label-formats {
            font-size: 8px;
        }
    }

    @media (max-width: 480px) {
        .form-row {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .form-row.document-upload-row {
            grid-template-columns: 1fr;
        }

        .form-container {
            padding: 8px;
        }

        .form-group {
            margin-bottom: 8px;
        }

        .form-group label {
            font-size: 10px;
            margin-bottom: 2px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            font-size: 10px;
            padding: 4px;
        }

        .form-group textarea {
            min-height: 50px;
        }

        .form-section-title {
            font-size: 14px;
            margin-bottom: 10px;
            margin-top: 16px;
            padding-bottom: 6px;
        }

        .form-actions {
            gap: 6px;
            margin-top: 8px;
        }

        .btn-submit,
        .btn-reset {
            padding: 6px 12px;
            font-size: 11px;
            flex: 1;
        }

        .error-message {
            font-size: 10px;
        }

        .file-upload-label {
            font-size: 9px;
            padding: 5px;
            min-height: 24px;
        }

        .file-upload-icon {
            font-size: 12px;
        }

        .file-upload-label-formats {
            font-size: 7px;
        }

        .product-tag {
            padding: 3px 8px;
            font-size: 10px;
        }
    }
</style>

<body>
<div id="wrapper">
    <?php $activePage = '/document_master.php'; ?>
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

        <div class="content-wrapper">
            <div class="form-wrapper">
                <div class="form-container">
                    <form id="documentMasterForm" enctype="multipart/form-data" novalidate>
                        
                        <!-- Type Selector (Always Visible) -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="documentType">Document Type <span class="required-field">*</span></label>
                                <select id="documentType" name="type" required style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                                    <option value="">-- Select Type --</option>
                                    <option value="document">Document</option>
                                    <option value="certificate">Certificate</option>
                                    <option value="policy">Policy</option>
                                    <option value="manual">Manual</option>
                                    <option value="fac">Function Allocation Chart</option>
                                </select>
                                <div class="error-message" id="documentTypeError"></div>
                            </div>

                            <div class="form-group empty-form-group">
                                <label>&nbsp;</label>
                                <div style="visibility: hidden;">Empty</div>
                            </div>

                            <div class="form-group empty-form-group">
                                <label>&nbsp;</label>
                                <div style="visibility: hidden;">Empty</div>
                            </div>
                        </div>

                        <!-- Dynamic Form Content -->
                        <div id="dynamicFormContent"></div>

                        <!-- Form Actions -->
                        <div class="form-actions" id="formActions" style="display: none;">
                            <button type="submit" class="btn-submit">Submit</button>
                            <button type="reset" class="btn-reset">Clear</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php $footer_display = 'Add New Document';
    include_once '../../assemblynotes/shared/footer.php'; ?>
</div>

<!-- TEMPLATES FOR DIFFERENT DOCUMENT TYPES -->

<!-- DOCUMENT TEMPLATE -->
<template id="documentTemplate">
    <div class="form-section-title">Add New Document</div>

    <!-- Row 1: Department, Doc Type, Sequential Number -->
    <div class="form-row">
        <div class="form-group">
            <label for="department">Department <span class="required-field">*</span></label>
            <div class="dropdown-wrapper">
                <input type="text" id="department" name="department" placeholder="Select or type department" autocomplete="off">
                <div class="dropdown-list" id="departmentDropdown"></div>
            </div>
            <div class="error-message" id="departmentError"></div>
        </div>

        <div class="form-group">
            <label for="docType">Doc Type <span class="required-field">*</span></label>
            <div class="dropdown-wrapper">
                <input type="text" id="docType" name="doc_type" placeholder="Select or type document type" autocomplete="off">
                <div class="dropdown-list" id="docTypeDropdown"></div>
            </div>
            <div class="error-message" id="docTypeError"></div>
        </div>

        <div class="form-group">
            <label for="seqNo">Sequential Number <span class="required-field">*</span></label>
            <div class="seq-no-display" id="seqNoDisplay">-</div>
            <input type="hidden" id="seqNo" name="seq_no">
            <div class="error-message" id="seqNoError"></div>
        </div>
    </div>

    <!-- Row 2: Document Number, Owner, Version -->
    <div class="form-row">
        <div class="form-group">
            <label for="documentNumber">Document Number</label>
            <div class="document-number-display" id="documentNumberDisplay">-</div>
            <input type="hidden" id="documentNumber" name="document_number">
            <div class="error-message" id="documentNumberError"></div>
        </div>

        <div class="form-group">
            <label for="owner">Owner <span class="required-field">*</span></label>
            <div class="owner-input-wrapper">
                <input type="text" id="owner" name="owner" placeholder="Type at least 2 letters" autocomplete="off">
                <input type="hidden" id="ownerHidden" name="owner_hidden">
                <div class="owner-dropdown-list" id="ownerDropdown"></div>
            </div>
            <div class="error-message" id="ownerError"></div>
        </div>

        <div class="form-group">
            <label for="version">Version <span class="required-field">*</span></label>
            <input type="text" id="version" name="version" placeholder="0.0" disabled>
            <input type="hidden" id="versionHidden" name="version_hidden">
            <div class="error-message" id="versionError"></div>
        </div>
    </div>

    <!-- Row 3: Issue Date, Next Review Date -->
    <div class="form-row">
        <div class="form-group">
            <label for="issueDate">Issue Date <span class="required-field">*</span></label>
            <input type="text" id="issueDate" name="issue_date" placeholder="DD/MM/YYYY" autocomplete="off">
            <div class="error-message" id="issueDateError"></div>
        </div>

        <div class="form-group">
            <label for="nextReviewDate">Next Review Date <span class="required-field">*</span></label>
            <input type="text" id="nextReviewDate" name="next_review_date" placeholder="DD/MM/YYYY" autocomplete="off">
            <div class="error-message" id="nextReviewDateError"></div>
        </div>

        <div class="form-group empty-form-group">
            <label>&nbsp;</label>
            <div style="visibility: hidden;">Empty</div>
        </div>
    </div>

    <!-- Conditional Section for WI Type -->
    <div id="wiConditionalSection" style="display: none;">
        <div class="form-row">
            <div class="form-group">
                <label for="wiType">WI Type <span class="required-field">*</span></label>
                <select id="wiType" name="wi_type" style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                    <option value="">-- Select WI Type --</option>
                    <option value="SB">SB (Switch Board)</option>
                    <option value="SD">SD (Switching Devices)</option>
                </select>
                <div class="error-message" id="wiTypeError"></div>
            </div>

            <div class="form-group">
                <label for="product">Product <span class="required-field">*</span></label>
                <div class="product-select-wrapper">
                    <div class="product-select-display" id="productDisplay" tabindex="0">
                        <div class="product-selected-items" id="productSelectedItems">
                            <span class="product-select-placeholder">Select products...</span>
                        </div>
                        <span class="product-dropdown-arrow">▼</span>
                    </div>
                    <input type="hidden" id="product" name="product">
                    <div class="product-dropdown-list" id="productDropdown"></div>
                </div>
                <div class="error-message" id="productError"></div>
            </div>

            <div class="form-group empty-form-group">
                <label>&nbsp;</label>
                <div style="visibility: hidden;">Empty</div>
            </div>
        </div>
    </div>

    <!-- Row 4: Upload Document File -->
    <div class="form-row document-upload-row">
        <div class="form-group">
            <label for="pdfFile">Upload Document File</label>
            <div class="file-upload-wrapper">
                <input type="file" id="pdfFile" name="pdf_file" class="file-upload-input" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx" data-file-type="pdf">
                <label for="pdfFile" class="file-upload-label">
                    <div class="file-upload-label-main">
                        <span class="file-upload-icon">📄</span>
                        <span id="fileUploadText">Click to upload or drag and drop document file</span>
                    </div>
                    <div class="file-upload-label-formats">
                        (Accepted: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX - Max 50MB)
                    </div>
                </label>
                <div class="file-name-display" id="fileNameDisplay" style="display: none;">
                    <span id="fileNameText"></span>
                    <button type="button" class="clear-file-btn" title="Remove file">✕</button>
                </div>
            </div>
            <div class="error-message" id="pdfFileError"></div>
        </div>
    </div>

    <!-- Row 5: Title/Description -->
    <div class="form-row full-width">
        <div class="form-group">
            <label for="description">Title/Description <span class="required-field">*</span></label>
            <textarea id="description" name="description" placeholder="Enter document title or description"></textarea>
            <div class="error-message" id="descriptionError"></div>
        </div>
    </div>

    <!-- Row 6: Remark -->
    <div class="form-row full-width">
        <div class="form-group">
            <label for="remark">Remark</label>
            <textarea id="remark" name="remark" placeholder="Enter any additional remarks (optional)"></textarea>
            <div class="error-message" id="remarkError"></div>
        </div>
    </div>
</template>

<!-- CERTIFICATE TEMPLATE -->
<template id="certificateTemplate">
    <div class="form-section-title">Add New Certificate</div>

    <div class="form-row">
        <div class="form-group">
            <label for="standard">Standard <span class="required-field">*</span></label>
            <select id="standard" name="standard" required style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                <option value="">-- Select Standard --</option>
                <option value="ISO 9001:2015">ISO 9001:2015</option>
                <option value="ISO 14001:2015">ISO 14001:2015</option>
                <option value="ISO 45001:2018">ISO 45001:2018</option>
                <option value="ISO 50001:2018">ISO 50001:2018</option>
            </select>
            <div class="error-message" id="standardError"></div>
        </div>

        <div class="form-group">
            <label for="certificateNo">Certificate No. <span class="required-field">*</span></label>
            <input type="text" id="certificateNo" name="certificate_no" placeholder="Enter certificate number" required>
            <div class="error-message" id="certificateNoError"></div>
        </div>

        <div class="form-group">
            <label for="certIssueDate">Issue Date <span class="required-field">*</span></label>
            <input type="text" id="certIssueDate" name="cert_issue_date" placeholder="DD/MM/YYYY" required autocomplete="off">
            <div class="error-message" id="certIssueDateError"></div>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="certExpiryDate">Expiry Date <span class="required-field">*</span></label>
            <input type="text" id="certExpiryDate" name="cert_expiry_date" placeholder="DD/MM/YYYY" required autocomplete="off">
            <div class="error-message" id="certExpiryDateError"></div>
        </div>

        <div class="form-group" style="grid-column: span 2;">
            <label for="certPdfFile">Upload PDF File</label>
            <div class="file-upload-wrapper">
                <input type="file" id="certPdfFile" name="cert_pdf_file" class="file-upload-input" accept=".pdf" data-file-type="cert">
                <label for="certPdfFile" class="file-upload-label">
                    <div class="file-upload-label-main">
                        <span class="file-upload-icon">📄</span>
                        <span id="certFileUploadText">Click to upload or drag and drop PDF file</span>
                    </div>
                    <div class="file-upload-label-formats">
                        (Accepted: PDF only - Max 50MB)
                    </div>
                </label>
                <div class="file-name-display" id="certFileNameDisplay" style="display: none;">
                    <span id="certFileNameText"></span>
                    <button type="button" class="clear-file-btn" title="Remove file">✕</button>
                </div>
            </div>
            <div class="error-message" id="certPdfFileError"></div>
        </div>
    </div>

    <div class="form-row full-width">
        <div class="form-group">
            <label for="certRemark">Remark</label>
            <textarea id="certRemark" name="cert_remark" placeholder="Enter any additional remarks (optional)"></textarea>
            <div class="error-message" id="certRemarkError"></div>
        </div>
    </div>
</template>

<!-- POLICY TEMPLATE -->
<template id="policyTemplate">
    <div class="form-section-title">Add New Policy</div>

    <div class="form-row">
        <div class="form-group">
            <label for="policyType">Type <span class="required-field">*</span></label>
            <select id="policyType" name="policy_type" required style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                <option value="">-- Select Type --</option>
                <option value="Quality">Quality</option>
                <option value="EHS">EHS</option>
                <option value="EnMS">EnMS</option>
            </select>
            <div class="error-message" id="policyTypeError"></div>
        </div>

        <div class="form-group">
            <label for="policyIssueDate">Issue Date <span class="required-field">*</span></label>
            <input type="text" id="policyIssueDate" name="policy_issue_date" placeholder="DD/MM/YYYY" required autocomplete="off">
            <div class="error-message" id="policyIssueDateError"></div>
        </div>

        <div class="form-group">
            <label for="policyPdfFile">Upload PDF File</label>
            <div class="file-upload-wrapper">
                <input type="file" id="policyPdfFile" name="policy_pdf_file" class="file-upload-input" accept=".pdf" data-file-type="policy">
                <label for="policyPdfFile" class="file-upload-label">
                    <div class="file-upload-label-main">
                        <span class="file-upload-icon">📄</span>
                        <span id="policyFileUploadText">Click to upload or drag and drop PDF file</span>
                    </div>
                    <div class="file-upload-label-formats">
                        (Accepted: PDF only - Max 50MB)
                    </div>
                </label>
                <div class="file-name-display" id="policyFileNameDisplay" style="display: none;">
                    <span id="policyFileNameText"></span>
                    <button type="button" class="clear-file-btn" title="Remove file">✕</button>
                </div>
            </div>
            <div class="error-message" id="policyPdfFileError"></div>
        </div>
    </div>

    <div class="form-row full-width">
        <div class="form-group">
            <label for="policyRemark">Remark</label>
            <textarea id="policyRemark" name="policy_remark" placeholder="Enter any additional remarks (optional)"></textarea>
            <div class="error-message" id="policyRemarkError"></div>
        </div>
    </div>
</template>

<!-- MANUAL TEMPLATE -->
<template id="manualTemplate">
    <div class="form-section-title">Add New Manual</div>

    <div class="form-row">
        <div class="form-group">
            <label for="manualType">Type <span class="required-field">*</span></label>
            <select id="manualType" name="manual_type" required style="width: 100%; padding: 6px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                <option value="">-- Select Type --</option>
                <option value="Quality">Quality</option>
                <option value="EHS">EHS</option>
                <option value="EnMS">EnMS</option>
            </select>
            <div class="error-message" id="manualTypeError"></div>
        </div>

        <div class="form-group">
            <label for="manualIssueDate">Issue Date <span class="required-field">*</span></label>
            <input type="text" id="manualIssueDate" name="manual_issue_date" placeholder="DD/MM/YYYY" required autocomplete="off">
            <div class="error-message" id="manualIssueDateError"></div>
        </div>

        <!-- ✅ ENABLED: User can type version manually -->
        <div class="form-group">
            <label for="manualVersion">Version <span class="required-field">*</span></label>
            <input type="text" id="manualVersion" name="manual_version" placeholder="e.g., 1.0 or 2.0" value="0.0" required>
            <input type="hidden" id="manualVersionHidden" name="manual_version_hidden" value="0.0">
            <div class="error-message" id="manualVersionError"></div>
        </div>
    </div>

    <div class="form-row full-width">
        <div class="form-group">
            <label for="manualPdfFile">Upload PDF File</label>
            <div class="file-upload-wrapper">
                <input type="file" id="manualPdfFile" name="manual_pdf_file" class="file-upload-input" accept=".pdf" data-file-type="manual">
                <label for="manualPdfFile" class="file-upload-label">
                    <div class="file-upload-label-main">
                        <span class="file-upload-icon">📄</span>
                        <span id="manualFileUploadText">Click to upload or drag and drop PDF file</span>
                    </div>
                    <div class="file-upload-label-formats">
                        (Accepted: PDF only - Max 50MB)
                    </div>
                </label>
                <div class="file-name-display" id="manualFileNameDisplay" style="display: none;">
                    <span id="manualFileNameText"></span>
                    <button type="button" class="clear-file-btn" title="Remove file">✕</button>
                </div>
            </div>
            <div class="error-message" id="manualPdfFileError"></div>
        </div>
    </div>

    <div class="form-row full-width">
        <div class="form-group">
            <label for="manualRemark">Remark</label>
            <textarea id="manualRemark" name="manual_remark" placeholder="Enter any additional remarks (optional)"></textarea>
            <div class="error-message" id="manualRemarkError"></div>
        </div>
    </div>
</template>

<!-- FAC TEMPLATE -->
<template id="facTemplate">
    <div class="form-section-title">Add New Function Allocation Chart</div>

    <div class="form-row">
        <div class="form-group">
            <label for="facDepartment">Department <span class="required-field">*</span></label>
            <div class="dropdown-wrapper">
                <input type="text" id="facDepartment" name="fac_department" placeholder="Select or type department" autocomplete="off">
                <div class="dropdown-list" id="facDepartmentDropdown"></div>
            </div>
            <div class="error-message" id="facDepartmentError"></div>
        </div>

        <div class="form-group">
            <label for="facIssueDate">Issue Date <span class="required-field">*</span></label>
            <input type="text" id="facIssueDate" name="fac_issue_date" placeholder="DD/MM/YYYY" required autocomplete="off">
            <div class="error-message" id="facIssueDateError"></div>
        </div>

        <div class="form-group">
            <label for="facNextReviewDate">Next Review Date <span class="required-field">*</span></label>
            <input type="text" id="facNextReviewDate" name="fac_next_review_date" placeholder="DD/MM/YYYY" required autocomplete="off">
            <div class="error-message" id="facNextReviewDateError"></div>
        </div>
    </div>

    <div class="form-row full-width">
        <div class="form-group">
            <label for="facPdfFile">Upload File (PDF or PPT)</label>
            <div class="file-upload-wrapper">
                <input type="file" id="facPdfFile" name="fac_pdf_file" class="file-upload-input" accept=".pdf,.ppt,.pptx" data-file-type="fac">
                <label for="facPdfFile" class="file-upload-label">
                    <div class="file-upload-label-main">
                        <span class="file-upload-icon">📄</span>
                        <span id="facFileUploadText">Click to upload or drag and drop file</span>
                    </div>
                    <div class="file-upload-label-formats">
                        (Accepted: PDF, PPT, PPTX - Max 50MB)
                    </div>
                </label>
                <div class="file-name-display" id="facFileNameDisplay" style="display: none;">
                    <span id="facFileNameText"></span>
                    <button type="button" class="clear-file-btn" title="Remove file">✕</button>
                </div>
            </div>
            <div class="error-message" id="facPdfFileError"></div>
        </div>
    </div>

    <div class="form-row full-width">
        <div class="form-group">
            <label for="facRemark">Remark</label>
            <textarea id="facRemark" name="fac_remark" placeholder="Enter any additional remarks (optional)"></textarea>
            <div class="error-message" id="facRemarkError"></div>
        </div>
    </div>
</template>

<!-- Scripts -->
<?php include_once '../../assemblynotes/shared/headerSemanticScripts.php' ?>
<script src="../../shared/shared.js"></script>

<script>
let departmentDropdownInstance = null;
let docTypeDropdownInstance = null;
let productDropdownInstance = null;
let ownerDropdownInstance = null;
let facDepartmentDropdownInstance = null;
let uploadedFiles = {};
let ownerSelected = false;
let currentFormType = null;
let selectedProducts = [];

// Updated product data for WI types
const productData = {
    'SB': [
        { value: 'NXAIR' },
        { value: 'NXAIR H' }
    ],
    'SD': [
        { value: 'SION M' },
        { value: 'SION M36' },
        { value: 'SION MVAR' }
    ]
};

// ============ UTILITY FUNCTIONS ============
function showFullLoader() {
    const loader = document.querySelector('.full-loader');
    if (loader) loader.style.display = 'flex';
}

function hideFullLoader() {
    const loader = document.querySelector('.full-loader');
    if (loader) loader.style.display = 'none';
}

function formatDateToDDMMYYYY(date) {
    if (!date) return '';
    const d = new Date(date);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
}

function getTodayInDDMMYYYY() {
    return formatDateToDDMMYYYY(new Date());
}

function extractCode(value) {
    if (!value) return '';
    const match = value.match(/^([^(]+)/);
    return match ? match[1].trim() : value.trim();
}

function generateDocumentNumber() {
    const department = $('#department').val().trim();
    const docType = $('#docType').val().trim();
    const seqNo = $('#seqNo').val();
    const version = '0.0';

    if (department && docType && seqNo) {
        const deptCode = extractCode(department);
        const docTypeCode = extractCode(docType);
        const seqNoFormatted = String(seqNo).padStart(3, '0');
        
        const documentNumber = `${deptCode}_${docTypeCode}_${seqNoFormatted} , V ${version}`;
        $('#documentNumber').val(documentNumber);
        $('#documentNumberDisplay').text(documentNumber);
    } else {
        $('#documentNumber').val('');
        $('#documentNumberDisplay').text('-');
    }
}

function checkIfWIType() {
    const department = $('#department').val().trim();
    const docType = $('#docType').val().trim();
    const wiDepartments = ['GCC', 'MF1', 'MF2', 'QC'];
    
    const deptCode = extractCode(department).toUpperCase();
    const docTypeCode = extractCode(docType).toUpperCase();
    
    const isWI = wiDepartments.some(dept => deptCode.includes(dept)) && docTypeCode.includes('WI');
    
    if (isWI) {
        $('#wiConditionalSection').show();
        $('#wiType').prop('required', true);
        $('#product').prop('required', true);
    } else {
        $('#wiConditionalSection').hide();
        $('#wiType').prop('required', false);
        $('#product').prop('required', false);
        $('#wiType').val('');
        $('#product').val('').prop('disabled', true);
        selectedProducts = [];
        updateProductDisplay();
        $('#wiTypeError').text('');
        $('#productError').text('');
    }
}

// ✅ INITIALIZE DATEPICKER FUNCTION
function initializeDatepicker(selector) {
    try {
        $(selector).datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            yearRange: '-100:+10',
            beforeShow: function(input, inst) {
                setTimeout(function() {
                    inst.dpDiv.css('z-index', 99999);
                }, 0);
            }
        });
        console.log('✓ Datepicker initialized for:', selector);
    } catch (e) {
        console.warn('Datepicker warning for', selector, ':', e.message);
    }
}

// ============ CUSTOM DROPDOWN CLASS ============
class CustomDropdown {
    constructor(inputSelector, dropdownSelector, apiAction, isStaticData = false, staticData = []) {
        this.input = document.querySelector(inputSelector);
        this.dropdownContainer = document.querySelector(dropdownSelector);
        this.apiAction = apiAction;
        this.allItems = staticData;
        this.isOpen = false;
        this.isLoaded = isStaticData;
        this.isStaticData = isStaticData;

        if (this.input && this.dropdownContainer) {
            this.init();
        }
    }

    init() {
        this.input.addEventListener('focus', () => {
            if (!this.isLoaded) {
                this.loadAllItems();
            } else {
                this.filterItems(this.input.value);
                this.open();
            }
        });

        this.input.addEventListener('input', (e) => {
            this.filterItems(e.target.value);
        });

        document.addEventListener('click', (e) => {
            if (e.target !== this.input && !this.dropdownContainer.contains(e.target)) {
                this.close();
            }
        });

        this.dropdownContainer.addEventListener('click', (e) => {
            if (e.target.classList.contains('dropdown-item')) {
                const value = e.target.getAttribute('data-value');
                this.input.value = value;
                this.close();
                this.input.dispatchEvent(new Event('change'));
            }
        });
    }

    loadAllItems() {
        if (this.isStaticData) {
            this.isLoaded = true;
            this.filterItems(this.input.value);
            this.open();
            return;
        }

        $.ajax({
            url: '../api/documentMasterAPI.php',
            type: 'POST',
            dataType: 'json',
            data: { action: this.apiAction, term: '' },
            success: (data) => {
                this.allItems = data || [];
                this.isLoaded = true;
                this.filterItems(this.input.value);
                this.open();
            },
            error: () => {
                this.allItems = [];
                this.isLoaded = true;
                this.renderDropdown([]);
                this.open();
            }
        });
    }

    filterItems(searchTerm) {
        const filtered = this.allItems.filter(item => 
            item.value.toLowerCase().includes(searchTerm.toLowerCase())
        );

        const isNewItem = searchTerm.trim() !== '' && 
                        !this.allItems.some(item => item.value.toLowerCase() === searchTerm.toLowerCase());

        this.renderDropdown(filtered, searchTerm, isNewItem);
    }

    renderDropdown(items, searchTerm = '', isNewItem = false) {
        this.dropdownContainer.innerHTML = '';

        if (items.length === 0 && !isNewItem) {
            if (searchTerm === '') {
                this.allItems.forEach(item => {
                    this.dropdownContainer.appendChild(this.createDropdownItem(item.value));
                });
            } else {
                const noResultsItem = document.createElement('div');
                noResultsItem.className = 'dropdown-item no-results';
                noResultsItem.textContent = 'No results found';
                this.dropdownContainer.appendChild(noResultsItem);
            }
        } else {
            items.forEach(item => {
                this.dropdownContainer.appendChild(this.createDropdownItem(item.value));
            });
        }

        if (isNewItem) {
            const newItemDiv = document.createElement('div');
            newItemDiv.className = 'dropdown-item new-item';
            newItemDiv.setAttribute('data-value', searchTerm.trim());
            newItemDiv.innerHTML = `<strong>+ Add new:</strong> "${searchTerm.trim()}"`;
            newItemDiv.addEventListener('click', () => {
                this.input.value = searchTerm.trim();
                this.close();
                this.input.dispatchEvent(new Event('change'));
            });
            this.dropdownContainer.appendChild(newItemDiv);
        }

        if (this.isOpen) this.open();
    }

    createDropdownItem(value) {
        const item = document.createElement('div');
        item.className = 'dropdown-item';
        item.setAttribute('data-value', value);
        item.textContent = value;
        return item;
    }

    open() {
        this.dropdownContainer.classList.add('show');
        this.isOpen = true;
    }

    close() {
        this.dropdownContainer.classList.remove('show');
        this.isOpen = false;
    }
}

// ============ MULTIPLE SELECT PRODUCT DROPDOWN CLASS ============
class MultiSelectProductDropdown {
    constructor(displaySelector, dropdownSelector) {
        this.display = document.querySelector(displaySelector);
        this.dropdownContainer = document.querySelector(dropdownSelector);
        this.allItems = [];
        this.isOpen = false;

        if (this.display && this.dropdownContainer) {
            this.init();
        }
    }

    init() {
        this.display.addEventListener('click', (e) => {
            e.stopPropagation();
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        });

        this.display.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (this.isOpen) {
                    this.close();
                } else {
                    this.open();
                }
            }
        });

        document.addEventListener('click', (e) => {
            if (!this.display.contains(e.target) && !this.dropdownContainer.contains(e.target)) {
                this.close();
            }
        });

        this.dropdownContainer.addEventListener('click', (e) => {
            e.stopPropagation();
            const checkbox = e.target.closest('input[type="checkbox"]');
            if (checkbox) {
                const value = checkbox.getAttribute('data-value');
                if (checkbox.checked) {
                    if (!selectedProducts.includes(value)) {
                        selectedProducts.push(value);
                    }
                } else {
                    selectedProducts = selectedProducts.filter(p => p !== value);
                }
                this.updateCheckboxStates();
                updateProductDisplay();
                $('#product').val(selectedProducts.join(',')).trigger('change');
            }
        });
    }

    setItems(items) {
        this.allItems = items || [];
        selectedProducts = [];
        this.renderDropdown();
        updateProductDisplay();
    }

    renderDropdown() {
        this.dropdownContainer.innerHTML = '';

        if (this.allItems.length === 0) {
            const noResultsItem = document.createElement('div');
            noResultsItem.className = 'product-dropdown-item no-results';
            noResultsItem.textContent = 'No products available';
            this.dropdownContainer.appendChild(noResultsItem);
        } else {
            this.allItems.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'product-dropdown-item';
                if (selectedProducts.includes(item.value)) {
                    itemDiv.classList.add('selected');
                }

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.setAttribute('data-value', item.value);
                checkbox.checked = selectedProducts.includes(item.value);

                const label = document.createElement('span');
                label.textContent = item.value;

                itemDiv.appendChild(checkbox);
                itemDiv.appendChild(label);
                this.dropdownContainer.appendChild(itemDiv);
            });
        }
    }

    updateCheckboxStates() {
        const checkboxes = this.dropdownContainer.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            const itemDiv = checkbox.closest('.product-dropdown-item');
            if (checkbox.checked) {
                itemDiv.classList.add('selected');
            } else {
                itemDiv.classList.remove('selected');
            }
        });
    }

    open() {
        this.dropdownContainer.classList.add('show');
        const arrow = this.display.querySelector('.product-dropdown-arrow');
        if (arrow) arrow.classList.add('open');
        this.isOpen = true;
    }

    close() {
        this.dropdownContainer.classList.remove('show');
        const arrow = this.display.querySelector('.product-dropdown-arrow');
        if (arrow) arrow.classList.remove('open');
        this.isOpen = false;
    }
}

function updateProductDisplay() {
    const selectedItemsContainer = document.getElementById('productSelectedItems');
    if (!selectedItemsContainer) return;

    selectedItemsContainer.innerHTML = '';

    if (selectedProducts.length === 0) {
        const placeholder = document.createElement('span');
        placeholder.className = 'product-select-placeholder';
        placeholder.textContent = 'Select products...';
        selectedItemsContainer.appendChild(placeholder);
    } else {
        selectedProducts.forEach(product => {
            const tag = document.createElement('div');
            tag.className = 'product-tag';
            
            const text = document.createElement('span');
            text.textContent = product;
            
            const removeBtn = document.createElement('span');
            removeBtn.className = 'product-tag-remove';
            removeBtn.textContent = '✕';
            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                selectedProducts = selectedProducts.filter(p => p !== product);
                updateProductDisplay();
                if (productDropdownInstance) {
                    productDropdownInstance.renderDropdown();
                }
                $('#product').val(selectedProducts.join(',')).trigger('change');
            });
            
            tag.appendChild(text);
            tag.appendChild(removeBtn);
            selectedItemsContainer.appendChild(tag);
        });
    }
}

// ============ OWNER DROPDOWN CLASS ============
class OwnerDropdown {
    constructor(inputSelector, hiddenInputSelector, dropdownSelector) {
        this.input = document.querySelector(inputSelector);
        this.hiddenInput = document.querySelector(hiddenInputSelector);
        this.dropdownContainer = document.querySelector(dropdownSelector);

        if (this.input && this.hiddenInput && this.dropdownContainer) {
            this.init();
        }
    }

    init() {
        this.input.addEventListener('input', (e) => {
            const searchTerm = e.target.value.trim();
            if (searchTerm.length >= 2) {
                this.searchOwners(searchTerm);
            } else {
                this.close();
                this.dropdownContainer.innerHTML = '';
            }
        });

        this.input.addEventListener('focus', (e) => {
            const searchTerm = e.target.value.trim();
            if (searchTerm.length >= 2) {
                this.searchOwners(searchTerm);
            }
        });

        document.addEventListener('click', (e) => {
            if (e.target !== this.input && !this.dropdownContainer.contains(e.target)) {
                this.close();
            }
        });

        this.dropdownContainer.addEventListener('click', (e) => {
            if (e.target.classList.contains('owner-dropdown-item')) {
                const fullName = e.target.getAttribute('data-value');
                this.input.value = fullName;
                this.hiddenInput.value = fullName;
                ownerSelected = true;
                this.close();
                this.input.dispatchEvent(new Event('change'));
            }
        });

        this.input.addEventListener('blur', () => {
            setTimeout(() => {
                if (this.input.value.trim() && !ownerSelected) {
                    toastr.warning('Please select an owner from the dropdown list');
                }
            }, 200);
        });
    }

    searchOwners(searchTerm) {
        $.ajax({
            url: '../api/documentMasterAPI.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'searchOwners', term: searchTerm },
            success: (data) => {
                this.renderDropdown(data || []);
                this.open();
            },
            error: () => {
                this.renderDropdown([]);
                this.open();
            }
        });
    }

    renderDropdown(items) {
        this.dropdownContainer.innerHTML = '';

        if (items.length === 0) {
            const noResultsItem = document.createElement('div');
            noResultsItem.className = 'owner-dropdown-item no-results';
            noResultsItem.textContent = 'No owners found';
            this.dropdownContainer.appendChild(noResultsItem);
        } else {
            items.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'owner-dropdown-item';
                itemDiv.setAttribute('data-value', item.full_name);
                itemDiv.textContent = item.full_name;
                this.dropdownContainer.appendChild(itemDiv);
            });
        }
    }

    open() {
        this.dropdownContainer.classList.add('show');
    }

    close() {
        this.dropdownContainer.classList.remove('show');
    }
}

// ============ FILE UPLOAD HANDLER ============
function initializeFileUpload(fileInputId, fileUploadLabelSelector, fileNameDisplayId, fileUploadTextId, clearBtnSelector) {
    const fileInput = document.getElementById(fileInputId);
    const fileUploadLabel = document.querySelector(fileUploadLabelSelector);
    const fileNameDisplay = document.getElementById(fileNameDisplayId);
    const fileUploadText = document.getElementById(fileUploadTextId);
    const clearBtn = document.querySelector(clearBtnSelector);
    const fileType = fileInput.getAttribute('data-file-type') || fileInputId;

    if (!fileInput || !fileUploadLabel || !fileNameDisplay || !fileUploadText) return;

    fileUploadLabel.addEventListener('click', (e) => {
        e.preventDefault();
        fileInput.click();
    });

    fileInput.addEventListener('change', (e) => {
        handleFileSelection(e.target.files[0], fileType);
    });

    fileUploadLabel.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.stopPropagation();
        fileUploadLabel.classList.add('drag-over');
    });

    fileUploadLabel.addEventListener('dragleave', (e) => {
        e.preventDefault();
        e.stopPropagation();
        fileUploadLabel.classList.remove('drag-over');
    });

    fileUploadLabel.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
        fileUploadLabel.classList.remove('drag-over');
        if (e.dataTransfer.files.length > 0) {
            handleFileSelection(e.dataTransfer.files[0], fileType);
            fileInput.files = e.dataTransfer.files;
        }
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            fileInput.value = '';
            delete uploadedFiles[fileType];
            fileNameDisplay.className = 'file-name-display';
            fileNameDisplay.style.display = 'none';
            fileUploadText.textContent = 'Click to upload or drag and drop document file';
            document.getElementById(fileInputId + 'Error').textContent = '';
        });
    }

    function handleFileSelection(file, type) {
        const errorElement = document.getElementById(fileInputId + 'Error');
        errorElement.textContent = '';
        fileNameDisplay.className = 'file-name-display';
        fileNameDisplay.style.display = 'none';

        if (!file) {
            delete uploadedFiles[type];
            return;
        }

        const allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'];
        const fileExtension = file.name.split('.').pop().toLowerCase();

        if (!allowedExtensions.includes(fileExtension)) {
            errorElement.textContent = 'Invalid file type. Only PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX are allowed.';
            document.getElementById(fileNameDisplayId.replace('Display', 'Text')).textContent = '❌ Invalid file type';
            fileNameDisplay.classList.add('error');
            fileNameDisplay.style.display = 'flex';
            delete uploadedFiles[type];
            fileInput.value = '';
            return;
        }

        const maxSize = 50 * 1024 * 1024;
        if (file.size > maxSize) {
            errorElement.textContent = 'File size must be less than 50MB';
            document.getElementById(fileNameDisplayId.replace('Display', 'Text')).textContent = '❌ File size exceeds 50MB';
            fileNameDisplay.classList.add('error');
            fileNameDisplay.style.display = 'flex';
            delete uploadedFiles[type];
            fileInput.value = '';
            return;
        }

        uploadedFiles[type] = file;
        fileUploadText.textContent = `✓ ${file.name} selected`;
        document.getElementById(fileNameDisplayId.replace('Display', 'Text')).textContent = `✓ ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
        fileNameDisplay.classList.add('success');
        fileNameDisplay.style.display = 'flex';
    }
}

// ============ FORM INITIALIZATION BY TYPE ============
function initializeFormByType(type) {
    console.log('→ Initializing form for type:', type);
    
    switch(type) {
        case 'document':
            initializeDocumentForm();
            break;
        case 'certificate':
            initializeCertificateForm();
            break;
        case 'policy':
            initializePolicyForm();
            break;
        case 'manual':
            initializeManualForm();
            break;
        case 'fac':
            initializeFACForm();
            break;
    }
}

function initializeDocumentForm() {
    console.log('→ Setting up document form');
    
    setTimeout(() => {
        $('#version').val('0.0').prop('disabled', true);
        $('#versionHidden').val('0.0');
        $('#issueDate').val(getTodayInDDMMYYYY());
        
        // ✅ Initialize datepicker
        initializeDatepicker('#issueDate');
        initializeDatepicker('#nextReviewDate');

        initializeFileUpload('pdfFile', 'label[for="pdfFile"].file-upload-label', 'fileNameDisplay', 'fileUploadText', '#fileNameDisplay .clear-file-btn');

        departmentDropdownInstance = new CustomDropdown('#department', '#departmentDropdown', 'getDepartments');
        docTypeDropdownInstance = new CustomDropdown('#docType', '#docTypeDropdown', 'getDocTypes');
        productDropdownInstance = new MultiSelectProductDropdown('#productDisplay', '#productDropdown');
        ownerDropdownInstance = new OwnerDropdown('#owner', '#ownerHidden', '#ownerDropdown');

        showFullLoader();
        $.when(
            $.ajax({
                url: '../api/documentMasterAPI.php',
                type: 'POST',
                dataType: 'json',
                data: { action: 'getDepartments', term: '' },
                success: (data) => {
                    if (departmentDropdownInstance) {
                        departmentDropdownInstance.allItems = data || [];
                        departmentDropdownInstance.isLoaded = true;
                    }
                }
            }),
            $.ajax({
                url: '../api/documentMasterAPI.php',
                type: 'POST',
                dataType: 'json',
                data: { action: 'getDocTypes', term: '' },
                success: (data) => {
                    if (docTypeDropdownInstance) {
                        docTypeDropdownInstance.allItems = data || [];
                        docTypeDropdownInstance.isLoaded = true;
                    }
                }
            })
        ).done(() => hideFullLoader()).fail(() => hideFullLoader());

        $('#department, #docType').on('change', function() {
            const department = $('#department').val().trim();
            const docType = $('#docType').val().trim();

            checkIfWIType();

            if (department && docType) {
                $.ajax({
                    url: '../api/documentMasterAPI.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'getNextSequentialNumber',
                        department: department,
                        doc_type: docType
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#seqNo').val(response.next_seq_no);
                            $('#seqNoDisplay').text(String(response.next_seq_no).padStart(3, '0'));
                            generateDocumentNumber();
                        }
                    }
                });
            }
        });

        $(document).on('change', '#wiType', function() {
            const wiType = $(this).val();
            if (wiType && productData[wiType]) {
                productDropdownInstance.setItems(productData[wiType]);
                $('#productDisplay').prop('disabled', false);
            } else {
                productDropdownInstance.setItems([]);
                selectedProducts = [];
                updateProductDisplay();
                $('#product').val('').prop('disabled', true);
                $('#productDisplay').prop('disabled', true);
            }
        });

        console.log('✓ Document form initialized');
    }, 100);
}

function initializeCertificateForm() {
    console.log('→ Setting up certificate form');
    
    setTimeout(() => {
        $('#certIssueDate').val(getTodayInDDMMYYYY());
        
        // ✅ Initialize datepicker
        initializeDatepicker('#certIssueDate');
        initializeDatepicker('#certExpiryDate');

        initializeFileUpload('certPdfFile', 'label[for="certPdfFile"].file-upload-label', 'certFileNameDisplay', 'certFileUploadText', '#certFileNameDisplay .clear-file-btn');
        
        console.log('✓ Certificate form initialized');
    }, 100);
}

function initializePolicyForm() {
    console.log('→ Setting up policy form');
    
    setTimeout(() => {
        $('#policyIssueDate').val(getTodayInDDMMYYYY());
        
        // ✅ Initialize datepicker
        initializeDatepicker('#policyIssueDate');

        initializeFileUpload('policyPdfFile', 'label[for="policyPdfFile"].file-upload-label', 'policyFileNameDisplay', 'policyFileUploadText', '#policyFileNameDisplay .clear-file-btn');
        
        console.log('✓ Policy form initialized');
    }, 100);
}

function initializeManualForm() {
    console.log('→ Setting up manual form');
    
    setTimeout(() => {
        // ✅ ENABLED: Default value 0.0, user can edit
        $('#manualVersion').val('0.0').prop('disabled', false);
        $('#manualVersionHidden').val('0.0');
        $('#manualIssueDate').val(getTodayInDDMMYYYY());
        
        // ✅ Sync version input with hidden field on input
        $('#manualVersion').on('input', function() {
            const version = $(this).val().trim();
            $('#manualVersionHidden').val(version);
            
            // ✅ Clear error message when user starts typing
            $('#manualVersionError').text('');
        });
        
        // ✅ Validate version format on blur
        $('#manualVersion').on('blur', function() {
            validateManualVersion();
        });
        
        // ✅ Initialize datepicker
        initializeDatepicker('#manualIssueDate');

        initializeFileUpload('manualPdfFile', 'label[for="manualPdfFile"].file-upload-label', 'manualFileNameDisplay', 'manualFileUploadText', '#manualFileNameDisplay .clear-file-btn');
        
        console.log('✓ Manual form initialized');
    }, 100);
}

// ✅ ADD VERSION VALIDATION FUNCTION
function validateManualVersion() {
    const version = $('#manualVersion').val().trim();
    const versionError = $('#manualVersionError');
    
    versionError.text('');
    
    if (!version) {
        versionError.text('Version is required');
        return false;
    }
    
    // ✅ Validate version format (e.g., 0.0, 1.0, 2.5.3, etc.)
    const versionRegex = /^\d+(\.\d+)*$/;
    if (!versionRegex.test(version)) {
        versionError.text('Invalid format. Use: 0.0, 1.0 or 2.5.3');
        return false;
    }
    
    return true;
}

function initializeFACForm() {
    console.log('→ Setting up FAC form');
    
    setTimeout(() => {
        $('#facIssueDate').val(getTodayInDDMMYYYY());
        
        // ✅ Initialize datepicker
        initializeDatepicker('#facIssueDate');
        initializeDatepicker('#facNextReviewDate');

        initializeFileUpload('facPdfFile', 'label[for="facPdfFile"].file-upload-label', 'facFileNameDisplay', 'facFileUploadText', '#facFileNameDisplay .clear-file-btn');

        facDepartmentDropdownInstance = new CustomDropdown('#facDepartment', '#facDepartmentDropdown', 'getDepartments');

        showFullLoader();
        $.ajax({
            url: '../api/documentMasterAPI.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'getDepartments', term: '' },
            success: (data) => {
                if (facDepartmentDropdownInstance) {
                    const apiDepartments = data || [];
                    const combinedDepartments = [
                        ...apiDepartments,
                        { value: 'Operations' },
                        { value: 'QM&GCC' }
                    ];
                    facDepartmentDropdownInstance.allItems = combinedDepartments;
                    facDepartmentDropdownInstance.isLoaded = true;
                }
                hideFullLoader();
            },
            error: () => {
                if (facDepartmentDropdownInstance) {
                    facDepartmentDropdownInstance.allItems = [
                        { value: 'Operations' },
                        { value: 'QM&GCC' }
                    ];
                    facDepartmentDropdownInstance.isLoaded = true;
                }
                hideFullLoader();
            }
        });
        
        console.log('✓ FAC form initialized');
    }, 100);
}

// ============ MAIN INITIALIZATION ============
$(document).ready(function() {
    console.log('✓ Document ready - initializing form');

    $('#documentType').on('change', function() {
        const selectedType = $(this).val();
        console.log('✓ Type selected:', selectedType);
        currentFormType = selectedType;

        $('.error-message').text('');
        uploadedFiles = {};
        ownerSelected = false;
        selectedProducts = [];
        $('#formActions').hide();

        if (!selectedType) {
            $('#dynamicFormContent').html('');
            return;
        }

        const templateMap = {
            'document': 'documentTemplate',
            'certificate': 'certificateTemplate',
            'policy': 'policyTemplate',
            'manual': 'manualTemplate',
            'fac': 'facTemplate'
        };

        const templateId = templateMap[selectedType];
        const template = document.getElementById(templateId);

        if (!template) {
            console.error('✗ Template not found:', templateId);
            toastr.error('Form template not found');
            return;
        }

        const content = template.content.cloneNode(true);
        const contentDiv = document.getElementById('dynamicFormContent');
        contentDiv.innerHTML = '';
        contentDiv.appendChild(content);

        $('#formActions').show();

        setTimeout(() => {
            initializeFormByType(selectedType);
        }, 50);
    });

    // Form submission
    $('#documentMasterForm').on('submit', function(e) {
        e.preventDefault();

        if (!currentFormType) {
            toastr.error('Please select a document type');
            return;
        }

        console.log('→ Submitting form:', currentFormType);

        const formData = new FormData();
        formData.append('action', 'saveDocument');
        formData.append('type', currentFormType);

        let isValid = true;

        switch(currentFormType) {
            case 'document':
                isValid = validateDocumentForm(formData);
                break;
            case 'certificate':
                isValid = validateCertificateForm(formData);
                break;
            case 'policy':
                isValid = validatePolicyForm(formData);
                break;
            case 'manual':
                isValid = validateManualForm(formData);
                break;
            case 'fac':
                isValid = validateFACForm(formData);
                break;
        }

        if (!isValid) return;

        showFullLoader();

        $.ajax({
            url: '../api/documentMasterAPI.php',
            type: 'POST',
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                hideFullLoader();
                if (response.success) {
                    toastr.success(response.message);
                    
                    // ✅ DYNAMIC LANDING PAGE BASED ON DOCUMENT TYPE
                    const landingPages = {
                        'document': '/dpm/document/document_details.php',
                        'certificate': '/dpm/document/certificate_details.php',
                        'policy': '/dpm/document/policy_details.php',
                        'manual': '/dpm/document/manual_details.php',
                        'fac': '/dpm/document/fac_details.php'
                    };
                    
                    const landingPage = landingPages[currentFormType] || '/dpm/document/document_details.php';
                    
                    setTimeout(() => {
                        window.location.href = landingPage;
                    }, 2000);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                hideFullLoader();
                console.error('Error:', error);
                toastr.error('An error occurred while saving the document');
            }
        });
    });

    // Form reset
    $('#documentMasterForm').on('reset', function() {
        setTimeout(() => {
            $('#documentType').val('');
            $('#dynamicFormContent').html('');
            $('#formActions').hide();
            $('.error-message').text('');
            uploadedFiles = {};
            ownerSelected = false;
            selectedProducts = [];
            currentFormType = null;
        }, 0);
    });
});

// ============ VALIDATION FUNCTIONS ============
function validateDocumentForm(formData) {
    let isValid = true;
    const requiredFields = ['department', 'docType', 'owner', 'issueDate', 'nextReviewDate', 'description'];

    requiredFields.forEach(field => {
        const value = $('#' + field).val().trim();
        if (!value) {
            isValid = false;
            $('#' + field + 'Error').text(field.charAt(0).toUpperCase() + field.slice(1) + ' is required');
        }
    });

    if (!$('#seqNo').val()) {
        isValid = false;
        $('#seqNoError').text('Please select both Department and Doc Type first');
    }

    if (!ownerSelected) {
        isValid = false;
        $('#ownerError').text('Please select an owner from the dropdown');
    }

    if ($('#wiConditionalSection').is(':visible')) {
        const wiType = $('#wiType').val().trim();

        if (!wiType) {
            isValid = false;
            $('#wiTypeError').text('WI Type is required');
        }

        if (selectedProducts.length === 0) {
            isValid = false;
            $('#productError').text('Please select at least one product');
        }
    }

    if (isValid) {
        formData.append('department', $('#department').val().trim());
        formData.append('doc_type', $('#docType').val().trim());
        formData.append('seq_no', $('#seqNo').val());
        formData.append('version', '0.0');
        formData.append('description', $('#description').val().trim());
        formData.append('owner', $('#owner').val().trim());
        formData.append('issue_date', $('#issueDate').val().trim());
        formData.append('next_review_date', $('#nextReviewDate').val().trim());
        formData.append('document_number', $('#documentNumber').val().trim());
        formData.append('remark', $('#remark').val().trim());
        
        if ($('#wiConditionalSection').is(':visible')) {
            formData.append('wi_type', $('#wiType').val().trim());
            formData.append('product', selectedProducts.join(','));
        }
        
        if (uploadedFiles['pdf']) formData.append('pdf_file', uploadedFiles['pdf']);
    } else {
        toastr.error('Please fill all required fields');
    }

    return isValid;
}

function validateCertificateForm(formData) {
    let isValid = true;
    const requiredFields = ['standard', 'certificateNo', 'certIssueDate', 'certExpiryDate'];

    requiredFields.forEach(field => {
        const value = $('#' + field).val().trim();
        if (!value) {
            isValid = false;
            $('#' + field + 'Error').text('This field is required');
        }
    });

    if (isValid) {
        formData.append('standard', $('#standard').val().trim());
        formData.append('certificate_no', $('#certificateNo').val().trim());
        formData.append('cert_issue_date', $('#certIssueDate').val().trim());
        formData.append('cert_expiry_date', $('#certExpiryDate').val().trim());
        formData.append('cert_remark', $('#certRemark').val().trim());
        if (uploadedFiles['cert']) formData.append('cert_pdf_file', uploadedFiles['cert']);
    } else {
        toastr.error('Please fill all required fields');
    }

    return isValid;
}

function validatePolicyForm(formData) {
    let isValid = true;
    const requiredFields = ['policyType', 'policyIssueDate'];

    requiredFields.forEach(field => {
        const value = $('#' + field).val().trim();
        if (!value) {
            isValid = false;
            $('#' + field + 'Error').text('This field is required');
        }
    });

    if (isValid) {
        formData.append('policy_type', $('#policyType').val().trim());
        formData.append('policy_issue_date', $('#policyIssueDate').val().trim());
        formData.append('policy_remark', $('#policyRemark').val().trim());
        if (uploadedFiles['policy']) formData.append('policy_pdf_file', uploadedFiles['policy']);
    } else {
        toastr.error('Please fill all required fields');
    }

    return isValid;
}

function validateManualForm(formData) {
    let isValid = true;
    const requiredFields = ['manualType', 'manualIssueDate', 'manualVersion'];

    requiredFields.forEach(field => {
        const value = $('#' + field).val().trim();
        if (!value) {
            isValid = false;
            $('#' + field + 'Error').text('This field is required');
        }
    });

    // ✅ Validate version format
    if (isValid && $('#manualVersion').val().trim()) {
        if (!validateManualVersion()) {
            isValid = false;
        }
    }

    if (isValid) {
        // ✅ Get the version value (default or user-entered)
        const manualVersion = $('#manualVersion').val().trim();
        
        formData.append('manual_type', $('#manualType').val().trim());
        formData.append('manual_issue_date', $('#manualIssueDate').val().trim());
        formData.append('manual_version', manualVersion);  // ✅ Use actual value
        formData.append('version', manualVersion);  // ✅ Use actual value
        formData.append('manual_remark', $('#manualRemark').val().trim());
        
        if (uploadedFiles['manual']) {
            formData.append('manual_pdf_file', uploadedFiles['manual']);
        }
        
        console.log('Manual Form Data:');
        for (let [key, value] of formData.entries()) {
            if (value instanceof File) {
                console.log(`${key}: File - ${value.name}`);
            } else {
                console.log(`${key}: ${value}`);
            }
        }
    } else {
        toastr.error('Please fill all required fields');
    }

    return isValid;
}

function validateFACForm(formData) {
    let isValid = true;
    const requiredFields = ['facDepartment', 'facIssueDate', 'facNextReviewDate'];

    requiredFields.forEach(field => {
        const value = $('#' + field).val().trim();
        if (!value) {
            isValid = false;
            $('#' + field + 'Error').text('This field is required');
        }
    });

    if (isValid) {
        formData.append('fac_department', $('#facDepartment').val().trim());
        formData.append('fac_issue_date', $('#facIssueDate').val().trim());
        formData.append('fac_next_review_date', $('#facNextReviewDate').val().trim());
        formData.append('fac_remark', $('#facRemark').val().trim());
        if (uploadedFiles['fac']) formData.append('fac_pdf_file', uploadedFiles['fac']);
    } else {
        toastr.error('Please fill all required fields');
    }

    return isValid;
}
</script>

</body>
</html>