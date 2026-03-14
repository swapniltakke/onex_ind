<?php
// /dpm/dto_configurator/projects/detail/info.php

SharedManager::checkAuthToModule(24);
include_once '../../../core/index.php';
$project = $_GET["project"] ?: 0;
SharedManager::saveLog("log_project_information", "View for Project: $project");
$menu_header_display = 'Project Information';
$project_information = "1";
$currentUser = isset($_SESSION['username']) ? $_SESSION['username'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>OneX | Project Details</title>
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

    .ui.grid > .column {
        padding: 3px 5px !important;
    }

    @media only screen and (max-width: 767px) {
        .ui.grid > .column {
            width: 100% !important;
        }
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

    .form-control[readonly] {
        background-color: #e9ecef;
    }

    .form-group {
        margin-bottom: 10px;
    }

    .form-group label {
        font-size: 12px;
        margin-bottom: 2px;
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

    /* Tab Navigation Styles */
    .nav-tabs {
        border-bottom: 2px solid #dee2e6;
        margin-bottom: 20px;
    }

    .nav-tabs .nav-link {
        color: #495057;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 10px 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .nav-tabs .nav-link:hover {
        color: #00b5ad;
        border-bottom-color: #00b5ad;
    }

    .nav-tabs .nav-link.active {
        color: #00b5ad;
        border-bottom-color: #00b5ad;
        background-color: transparent;
    }

    .tab-content {
        margin-top: 20px;
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.active {
        display: block;
    }

    /* Project Info Section */
    .project-info-section {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .project-info-section h4 {
        margin-top: 0;
        color: #00b5ad;
        border-bottom: 2px solid #00b5ad;
        padding-bottom: 10px;
    }

    .info-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 15px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-item label {
        font-weight: bold;
        font-size: 12px;
        color: #495057;
        margin-bottom: 5px;
    }

    .info-item value {
        font-size: 13px;
        color: #212529;
        padding: 8px;
        background-color: white;
        border-radius: 4px;
        border: 1px solid #dee2e6;
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

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
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

    /* Error Section */
    #errorSection {
        display: none;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }

    #errorSection.show {
        display: block;
    }

    #errorMessage {
        margin: 0;
    }
</style>
<body>
<div id="wrapper">
    <?php $activePage = '/projects/detail/info.php'; ?>
    <?php include_once '../../shared/dto_sidebar.php'; ?>
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom" style="position: relative; margin: 0;">
            <div class="ui fixed menu" style="padding: 21px; color:teal; width: 100%;">
                <div class="ui container" style="position: relative; width: 100%;">
                    <div style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); display: flex; align-items: center;">
                        <a href="/dpm/dto_configurator/index.php" style="display: flex; align-items: center; text-decoration: none;">
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

        <!-- Error Section -->
        <div id="errorSection" class="alert alert-danger">
            <p id="errorMessage"></p>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Project Header -->
                        <div class="project-info-section">
                            <h4><i class="fa fa-info-circle"></i> Project Information</h4>
                            <div class="info-row">
                                <div class="info-item">
                                    <label>Project Number</label>
                                    <value id="projectNo">-</value>
                                </div>
                                <div class="info-item">
                                    <label>Project Name</label>
                                    <value id="projectName">-</value>
                                </div>
                                <div class="info-item">
                                    <label>Contact</label>
                                    <value id="projectContact">-</value>
                                </div>
                                <div class="info-item">
                                    <label>Status</label>
                                    <value id="projectStatus">-</value>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs" role="tablist" style="margin-top: 35px;">
                            <li class="nav-item">
                                <a class="nav-link active" id="infoTab" data-toggle="tab" href="#infoContent" role="tab">
                                    <i class="fa fa-info-circle"></i> Project Info
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="kukoMatrixTab" data-toggle="tab" href="#kukoMatrixContent" role="tab">
                                    <i class="fa fa-th"></i> Kuko Matrix
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="projectWorkTab" data-toggle="tab" href="#projectWorkContent" role="tab">
                                    <i class="fa fa-wrench"></i> Project Work
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="orderSummaryTab" data-toggle="tab" href="#orderSummaryContent" role="tab">
                                    <i class="fa fa-bar-chart"></i> Order Summary
                                </a>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content">
                            <!-- Info Tab -->
                            <div class="tab-pane fade show active" id="infoContent" role="tabpanel">
                                <div id="projectInfoContainer">
                                    <p style="text-align: center; padding: 20px;">Loading project information...</p>
                                </div>
                            </div>

                            <!-- Kuko Matrix Tab -->
                            <div class="tab-pane fade" id="kukoMatrixContent" role="tabpanel">
                                <div id="kukoMatrixContainer">
                                    <p style="text-align: center; padding: 20px;">Click on Kuko Matrix tab to load data...</p>
                                </div>
                            </div>

                            <!-- Project Work Tab -->
                            <div class="tab-pane fade" id="projectWorkContent" role="tabpanel">
                                <div id="projectWorkContainer">
                                    <p style="text-align: center; padding: 20px;">Click on Project Work tab to load data...</p>
                                </div>
                            </div>

                            <!-- Order Summary Tab -->
                            <div class="tab-pane fade" id="orderSummaryContent" role="tabpanel">
                                <div id="orderSummaryContainer">
                                    <p style="text-align: center; padding: 20px;">Click on Order Summary tab to load data...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $footer_display = 'Project Details';
    include_once '../../../assemblynotes/shared/footer.php'; ?>
</div>

<script>
    // Global variables
    const projectNo = new URLSearchParams(window.location.search).get('project');
    let selectedNachbauFile = null;

    /**
     * Initialize page
     */
    $(document).ready(function() {
        if (!projectNo) {
            showError('Project number is required');
            return;
        }

        // Load initial project info
        loadProjectInfo();

        // Tab switching
        $('#infoTab').on('click', function() {
            loadTabData('info');
        });

        $('#kukoMatrixTab').on('click', function() {
            loadTabData('kukoMatrix');
        });

        $('#projectWorkTab').on('click', function() {
            loadTabData('projectWork');
        });

        $('#orderSummaryTab').on('click', function() {
            loadTabData('orderSummary');
        });
    });

    /**
     * Load Project Info
     */
    function loadProjectInfo() {
        showLoader();

        axios.get('/dpm/api/ProjectController.php', {
            params: {
                action: 'getProjectInfo',
                projectNo: projectNo
            }
        })
        .then(response => {
            hideLoader();
            if (response.data.success && response.data.data) {
                displayProjectInfo(response.data.data);
            } else {
                showError(response.data.message || 'Failed to load project information');
            }
        })
        .catch(error => {
            hideLoader();
            console.error('Error loading project info:', error);
            showError(error.message || 'An error occurred');
        });
    }

    /**
     * Display Project Info
     */
    function displayProjectInfo(data) {
        document.getElementById('projectNo').textContent = data.project_no || '-';
        document.getElementById('projectName').textContent = data.project_name || '-';
        document.getElementById('projectContact').textContent = data.contact || '-';
        document.getElementById('projectStatus').textContent = data.status || '-';

        // Store nachbau file for later use
        selectedNachbauFile = data.latest_nachbau || null;
    }

    /**
     * Load Tab Data
     */
    function loadTabData(tabName, project = projectNo) {
        console.log('Loading tab:', tabName);

        switch(tabName) {
            case 'kukoMatrix':
                loadKukoMatrixData();
                break;
            case 'projectWork':
                loadProjectWorkData();
                break;
            case 'orderSummary':
                loadOrderSummaryData();
                break;
            case 'info':
            default:
                loadProjectInfoData();
                break;
        }
    }

    /**
     * Load Project Info Data
     */
    function loadProjectInfoData() {
        const container = document.getElementById('projectInfoContainer');
        container.innerHTML = '<p style="text-align: center; padding: 20px;">Loading project information...</p>';

        showLoader();

        axios.get('/dpm/api/ProjectController.php', {
            params: {
                action: 'getProjectInfoDetails',
                projectNo: projectNo
            }
        })
        .then(response => {
            hideLoader();
            if (response.data.success && response.data.data) {
                let html = '<div class="project-info-section">';
                html += '<h4>Detailed Project Information</h4>';
                html += '<div class="info-row">';
                
                const data = response.data.data;
                for (const [key, value] of Object.entries(data)) {
                    html += `
                        <div class="info-item">
                            <label>${key.replace(/_/g, ' ').toUpperCase()}</label>
                            <value>${value || '-'}</value>
                        </div>
                    `;
                }
                
                html += '</div></div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p style="text-align: center; padding: 20px; color: #dc3545;">No project information found</p>';
            }
        })
        .catch(error => {
            hideLoader();
            console.error('Error loading project info:', error);
            container.innerHTML = '<p style="text-align: center; padding: 20px; color: #dc3545;">Error loading project information</p>';
        });
    }

    /**
     * Load Kuko Matrix Data
     */
    function loadKukoMatrixData() {
        const container = document.getElementById('kukoMatrixContainer');
        container.innerHTML = '<p style="text-align: center; padding: 20px;">Loading Kuko Matrix...</p>';

        showLoader();

        // Load the kuko-matrix.js file if not already loaded
        if (typeof loadKukoMatrixTable === 'undefined') {
            $.getScript('/dpm/dto_configurator/js/projects/detail/kuko-matrix.js', function() {
                loadKukoMatrixFromAPI();
            });
        } else {
            loadKukoMatrixFromAPI();
        }
    }

    /**
     * Load Kuko Matrix from API
     */
    function loadKukoMatrixFromAPI() {
        axios.get('/dpm/api/KukoMatrixController.php', {
            params: {
                action: 'getKukoMatrixData',
                projectNo: projectNo,
                nachbauFile: selectedNachbauFile
            }
        })
        .then(response => {
            hideLoader();
            if (response.data.success && response.data.data) {
                if (typeof displayKukoMatrixTable === 'function') {
                    displayGukoMatrixTable(response.data.data);
                }
            } else {
                document.getElementById('kukoMatrixContainer').innerHTML = '<p style="text-align: center; padding: 20px; color: #dc3545;">No Kuko Matrix data found</p>';
            }
        })
        .catch(error => {
            hideLoader();
            console.error('Error loading Kuko Matrix:', error);
            document.getElementById('kukoMatrixContainer').innerHTML = '<p style="text-align: center; padding: 20px; color: #dc3545;">Error loading Kuko Matrix</p>';
        });
    }

    /**
     * Load Project Work Data
     */
    function loadProjectWorkData() {
        const container = document.getElementById('projectWorkContainer');
        container.innerHTML = '<p style="text-align: center; padding: 20px;">Loading Project Work...</p>';

        showLoader();

        // Load the project-work.js file if not already loaded
        if (typeof loadProjectWorkData === 'undefined') {
            $.getScript('/dpm/dto_configurator/js/projects/detail/tabs/project-work.js', function() {
                loadProjectWorkFromAPI();
            });
        } else {
            loadProjectWorkFromAPI();
        }
    }

    /**
     * Load Project Work from API
     */
    function loadProjectWorkFromAPI() {
        axios.get('/dpm/api/ProjectController.php', {
            params: {
                action: 'getProjectWorkData',
                projectNo: projectNo,
                nachbauFile: selectedNachbauFile
            }
        })
        .then(response => {
            hideLoader();
            if (response.data.success && response.data.data) {
                if (typeof displayProjectWorkTab === 'function') {
                    displayProjectWorkTab(response.data.data);
                }
            } else {
                document.getElementById('projectWorkContainer').innerHTML = '<p style="text-align: center; padding: 20px; color: #dc3545;">No project work data found</p>';
            }
        })
        .catch(error => {
            hideLoader();
            console.error('Error loading project work:', error);
            document.getElementById('projectWorkContainer').innerHTML = '<p style="text-align: center; padding: 20px; color: #dc3545;">Error loading project work</p>';
        });
    }

    /**
     * Load Order Summary Data
     */
    function loadOrderSummaryData() {
        const container = document.getElementById('orderSummaryContainer');
        container.innerHTML = '<p style="text-align: center; padding: 20px;">Loading Order Summary...</p>';

        showLoader();

        // Load the order-summary.js file if not already loaded
        if (typeof loadOrderSummaryData === 'undefined') {
            $.getScript('/dpm/dto_configurator/js/projects/detail/tabs/order-summary.js', function() {
                loadOrderSummaryFromAPI();
            });
        } else {
            loadOrderSummaryFromAPI();
        }
    }

    /**
     * Load Order Summary from API
     */
    function loadOrderSummaryFromAPI() {
        axios.get('/dpm/api/OrdersPlanController.php', {
            params: {
                action: 'getOrderSummaryData',
                projectNo: projectNo,
                nachbauFile: selectedNachbauFile
            }
        })
        .then(response => {
            hideLoader();
            if (response.data.success && response.data.data) {
                if (typeof displayOrderSummaryTab === 'function') {
                    displayOrderSummaryTab(response.data.data);
                }
            } else {
                document.getElementById('orderSummaryContainer').innerHTML = '<p style="text-align: center; padding: 20px; color: #dc3545;">No order summary data found</p>';
            }
        })
        .catch(error => {
            hideLoader();
            console.error('Error loading order summary:', error);
            document.getElementById('orderSummaryContainer').innerHTML = '<p style="text-align: center; padding: 20px; color: #dc3545;">Error loading order summary</p>';
        });
    }

    /**
     * Show Loader
     */
    function showLoader() {
        document.getElementById('customLoaderOverlay').classList.add('show');
    }

    /**
     * Hide Loader
     */
    function hideLoader() {
        document.getElementById('customLoaderOverlay').classList.remove('show');
    }

    /**
     * Show Error
     */
    function showError(message) {
        const errorSection = document.getElementById('errorSection');
        const errorMessage = document.getElementById('errorMessage');
        errorMessage.textContent = message;
        errorSection.classList.add('show');
    }

    /**
     * Clear Error
     */
    function clearError() {
        const errorSection = document.getElementById('errorSection');
        errorSection.classList.remove('show');
    }
</script>

</body>
</html>