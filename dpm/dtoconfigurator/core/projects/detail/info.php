<?php
SharedManager::checkAuthToModule(24);
include_once $_SERVER["DOCUMENT_ROOT"] . '/dpm/core/index.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/checklogin.php';
$projectNo = getSanitizedOrDefaultData($_GET['project-no']);
SharedManager::saveLog("log_project_detail", "View for Project: $projectNo");
$currentUser = isset($_SESSION['username']) ? $_SESSION['username'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTO Configurator | <?php echo $projectNo; ?></title>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta charset="utf-8">

    <link href="../../../../../css/semantic.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="../../../../../css/dataTables.semanticui.min.css">
    <link rel="stylesheet" type="text/css" href="../../../../../css/responsive.dataTables.min.css">

    <link href="../../../../../css/main.css?13" rel="stylesheet"/>

    <?php include_once '../../../../shared/headerStyles.php' ?>
    <!-- jQuery (MUST BE FIRST) -->
    <script src="../../../../../js/jquery.min.js"></script>
    <!-- Axios Library (ADD THIS) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.0/axios.min.js"></script>
    <!-- jQuery Toast Library (ADD THIS) -->
    <script src="../../../../../js/jquery.toast.min.js"></script>
    
    <script src="../../../../../js/semantic.min.js"></script>
    <script src="../../../../../js/jquery.dataTables.js"></script>
    <script src="../../../../../js/dataTables.semanticui.min.js"></script>
    <script src="../../../../../js/dataTables.buttons.min.js"></script>
    <script src="../../../../../js/buttons.flash.min.js"></script>
    <script src="../../../../../js/jszip.min.js"></script>
    <script src="../../../../../js/pdfmake.min.js"></script>
    <script src="../../../../../js/vfs_fonts.js"></script>
    <script src="../../../../../js/buttons.html5.min.js"></script>
    <script src="../../../../../js/buttons.print.min.js"></script>
    <script src="../../../../../js/buttons.colVis.min.js"></script>
    <script src="../../../../../js/tablesort.js"></script>
    <script src="../../../../../js/Semantic-UI-Alert.js"></script>
    <script src="../../../../../js/dataTables.fixedHeader.min.js"></script>
    <script src="../../../../../shared/inspia_gh_assets/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <link rel="stylesheet" href="../../../../../css/jquery.toast.min.css">

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

    .gray-bg {
        background-color: #f3f3f4;
    }

    .card {
        width: 100%;
        margin: 0;
        padding: 0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        border-radius: 4px;
    }

    .card-body {
        padding: 15px !important;
        width: 100%;
    }

    /* Project Info Labels */
    #projectInfoLabels {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        margin-bottom: 20px;
    }

    .ui.label {
        font-size: 12px !important;
        padding: 8px 12px !important;
    }

    .ui.blue.basic.inverted.label {
        background-color: #00b5ad !important;
        color: white !important;
    }

    .ui.green.basic.label {
        background-color: #f0f0f0 !important;
        border: 1px solid #ddd !important;
    }

    .ui.violet.basic.label {
        background-color: #f0f0f0 !important;
        border: 1px solid #ddd !important;
    }

    .ui.red.basic.label {
        background-color: #f0f0f0 !important;
        border: 1px solid #ddd !important;
    }

    .ui.brown.basic.label {
        background-color: #f0f0f0 !important;
        border: 1px solid #ddd !important;
    }

    .ui.icon.message {
        padding: 15px !important;
        margin-bottom: 15px !important;
    }

    .ui.icon.message.positive {
        background-color: #d4edda !important;
        border: 1px solid #c3e6cb !important;
        color: #155724 !important;
    }

    .ui.icon.message.warning {
        background-color: #fff3cd !important;
        border: 1px solid #ffeaa7 !important;
        color: #856404 !important;
    }

    .ui.red.compact.message {
        background-color: #f8d7da !important;
        border: 1px solid #f5c6cb !important;
        color: #721c24 !important;
    }

    /* Tabs */
    .ui.top.attached.tabular.menu {
        border-bottom: 2px solid #dee2e6 !important;
        background-color: #f8f9fa !important;
    }

    .ui.top.attached.tabular.menu .item {
        color: #495057 !important;
        border-bottom: 3px solid transparent !important;
        padding: 12px 15px !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .ui.top.attached.tabular.menu .item:hover {
        color: #00b5ad !important;
    }

    .ui.top.attached.tabular.menu .item.active {
        color: #00b5ad !important;
        border-bottom-color: #00b5ad !important;
        background-color: white !important;
    }

    .ui.bottom.attached.tab.segment {
        border-top: none !important;
        padding: 20px !important;
        display: none;
    }

    .ui.bottom.attached.tab.segment.active {
        display: block !important;
    }

    /* Grid */
    .ui.grid.container-fluid {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .ui.grid > .column {
        padding: 10px !important;
    }

    /* Loader */
    .ui.active.centered.inline.loader {
        margin: 50px auto !important;
    }

    /* Placeholder */
    .ui.placeholder.segment {
        background-color: #f8f9fa !important;
        border: 1px solid #dee2e6 !important;
        padding: 40px 20px !important;
        text-align: center;
    }

    /* Buttons */
    .ui.button {
        font-size: 12px !important;
        padding: 8px 12px !important;
    }

    .ui.green.circular.icon.compact.button {
        background-color: #28a745 !important;
        color: white !important;
    }

    .ui.inverted.button.brown {
        background-color: #8B6F47 !important;
        color: white !important;
    }

    /* Scrollbar */
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

    .sixteen.wide.column {
        width: 100% !important;
    }

    .four.wide.column {
        width: 100% !important;
    }

    @media (min-width: 768px) {
        .four.wide.column {
            width: 33.333% !important;
        }
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

    .form-group {
        margin-bottom: 10px;
    }

    .form-group label {
        font-size: 12px;
        margin-bottom: 2px;
    }

    .ui.fluid.selection.search.dropdown {
        width: 100% !important;
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

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .custom-loader-text {
        color: #333;
        font-size: 14px;
        font-weight: 500;
        margin: 0;
    }

    /* Owner input wrapper for search */
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
</style>
<body>
<!-- Main Content Area -->
<div id="wrapper">
    <!-- Header -->
    <?php $activePage = '/info.php'; ?>
    <?php include_once '../../../../shared/dto_sidebar.php'; ?>
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
        
        <div class="ui active centered inline loader" style="margin-top:10%;"></div>

        <div id="projectPageErrorDiv" class="ui red compact message" style="display:none;">
            <i class="info circle icon"></i><span id="projectPageErrorMsg"></span>
        </div>

        <div id="projectPageContainer" class="ui grid container-fluid" style="display:none;padding-right:2%;">

            <!-- Project Page Header -->
            <div id="projectInfoLabels" class="twelve wide column" style="display: flex; align-items: center; gap: 10px;">
                <div class="ui blue basic inverted label large">
                    <span id="downloadProjectFolder"></span>
                    <span id="projectNumberLabel"></span> -
                    <span id="projectNameLabel"></span>
                </div>

                <div class="ui green basic label" data-tooltip="Order Manager" data-position="top left">
                    <i class="mail icon"></i> <span id="orderManagerLabel"></span>
                </div>

                <div class="ui violet image basic label item" style="display: flex; align-items: center; gap: 5px;">
                    <i class="plug icon"></i>
                    <span id="productLabel" data-tooltip="Product Type" data-position="top left"></span>
                    <div id="panelCountLabel" class="floating ui violet label" data-tooltip="Panel Count" data-position="top left"></div>
                </div>

                <div id="packTypeDiv" class="ui violet basic label" data-tooltip="Pack Type" data-position="top left" style="display:none;">
                    <i class="archive icon"></i> <span id="packTypeLabel"></span>
                </div>

                <div id="assemblyStartDate" class="ui red basic label" data-tooltip="Assembly Start Date" data-position="top left">
                    <i class="calendar icon"></i> <span id="assemblyStartDateLabel"></span>
                </div>

                <div id="assemblyStartRemainingWeek" class="ui red basic label" data-tooltip="Assembly Start Remaining" data-position="top left">
                    <i class="calendar icon"></i> <span id="assemblyStartRemainingWeekLabel"></span>
                </div>

                <div class="ui brown basic label" data-tooltip="Rated Voltage" data-position="top left">
                    <i class="lightbulb icon"></i> <span id="ratedVoltageLabel"></span>
                </div>

                <div class="ui brown basic label" data-tooltip="Rated Short Circuit" data-position="top left">
                    <i class="lightning icon"></i> <span id="ratedShortCircuitLabel"></span>
                </div>

                <div class="ui brown basic label" data-tooltip="Rated Current" data-position="top left">
                    <i class="battery full icon"></i> <span id="ratedCurrentLabel"></span>
                </div>
            </div>

            <div class="four wide column right aligned">
                <select id="searchProjectSelect" name="searchProjectSelect" class="ui fluid selection search dropdown searchProjectSelect">
                    <option value="">Search Project</option>
                </select>
            </div>

            <div id="projectDataTabsInfoMessage" class="sixteen wide column" style="display:none;">
                <div class="ui icon message warning" style="width:40%;">
                    <i class="hand point down outline icon"></i>
                    <div class="content">
                        <div class="header" style="margin-bottom:0.4rem;">
                            Quick Tip
                        </div>
                        <span><b>Choose a Nachbau File below to see the details of project.</b></span>
                    </div>
                </div>
            </div>

            <!-- Project Status Messages (Pending, Published, Rejected, etc.) -->
            <div id="projectStatusMessageDiv" style="margin-bottom: 1.3rem; display:none;">
                <div class="ui icon message positive">
                    <i class="wrench icon"></i>
                    <div class="content">
                        <div class="header" style="margin-bottom:0.4rem;">
                            Project current status is: <span class="projectStatusName"></span>
                        </div>
                        <span id="pendingApprovalStatusMsg" style="display:none;">
                            This project was sent for approval on <b><span class="sendReviewDate"></span></b> by <b><span class="whoSentApproval"></span></b>.
                        </span>
                        <span id="rejectedStatusMsg" style="display:none;">
                            Approval request is rejected on <b><span class="reviewedDate"></span></b> by <b><span class="whoSentApproval"></span></b>. <br>
                            Check for more details.
                        </span>
                        <span id="publishedStatusMsg" style="display:none;">
                            The project was released on <b><span class="reviewedDate"></span></b> by <b><span class="whoSentApproval"></span></b>.
                        </span>
                        <span id="pendingRevisionApprovalStatusMsg" style="display:none;">
                            This project revisions were sent for approval on <b><span class="sendReviewDate"></span></b> by <b><span class="whoSentApproval"></span></b>.
                        </span>
                        <span id="revisionPublishedStatusMsg" style="display:none;">
                            The project revisions were released on <b><span class="reviewedDate"></span></b> by <b><span class="whoSentApproval"></span></b>.
                        </span>
                    </div>
                </div>
                <div id="withdrawPublishRequest" class="ui red circular icon compact button" style="display:none;">
                    <i class="reply icon"></i>
                    Withdraw Publish Request
                </div>
            </div>

            <!-- Working Status Messages (Someone is working OR Under Revision) -->
            <div id="projectWorkingStatusInfo" style="margin-bottom: 1.3rem; display:none;">
                <div class="ui icon message warning">
                    <i class="wrench icon"></i>
                    <div class="content">
                        <div class="header" style="margin-bottom:0.5rem;">
                            <span id="workingStatusHeader"></span>
                        </div>

                        <!-- Someone is currently working -->
                        <span id="currentlyWorkingUserInfoMessage" style="display:none;">
                            <b><span id="currentlyWorkingUser"></span></b> is currently working on this Nachbau file. To avoid conflicts, editing project works is temporarily disabled.
                        </span>

                        <!-- Under Revision -->
                        <span id="projectRevisionStatusTabMessage" style="display:none;">
                            <b><span id="revisionWorkingUser"></span></b> is working on the <b>revision</b> in this nachbau.
                            Click the button below to view the <b>not transferred works</b> from the <b>previous nachbau transfer.</b> <br>
                        </span>
                    </div>
                </div>
            </div>

            <div id="transferSummaryButtonDiv" style="margin-top: 0.1rem;margin-bottom:1rem;width:100%;display:none;">
                <button id="transferSummaryButton" class="ui inverted button brown small">
                    <i class="eye icon"></i>
                    Transfer Summary
                </button>
            </div>

            <!-- No DTO Number for this project -->
            <div id="nachbauErrorMsgDiv" style="display:none;">
                <div class="ui placeholder segment">
                    <div id="nachbauErrorMsg" class="ui icon header"></div>
                </div>
            </div>

            <!-- Nachbau Data is available for this project -->
            <div id="nachbauFileExistsOrNot">
                <div class="ui placeholder segment">
                    <div id="nachbauFileMsg" class="ui icon header"></div>
                </div>
            </div>

            <div class="ui grid" style="width:100%!important;">
                <!-- Row for Nachbau Data -->
                <div class="row">
                    <div class="sixteen wide column">
                        <!-- Nachbau Filter -->
                        <?php include_once '../../projects/detail/nachbau-data.php'; ?>
                    </div>
                </div>
                <div id="checkInButton" class="ui green circular icon compact button" data-position="top left" style="display:none;margin-left: 1.1rem;margin-top: 0.5rem;"
                    data-tooltip="Lock this project and start working on it.">
                    <i class="lock icon"></i>
                    Check In Project
                </div>
                <!-- Row for Project Data Tabs -->
                <div id="projectDataTabsRow" class="row">
                    <div class="sixteen wide column">
                        <div class="ui active inline indeterminate text loader" style="margin-top:10%;"><b>Loading Project Data</b></div>

                        <div id="projectDataTabs" style="display:none !important;">
                            <div id="projectDataTabularMenu" class="ui top attached tabular menu">
                                <a class="active item" data-tab="kukoMatrix"><i class="plus square icon"></i>KuKo Matrix</a>
                                <a class="item" data-tab="projectWork"><i class="pencil alternate icon"></i>Project Work</a>
                                <a class="item" data-tab="jtCollection"><i class="dropbox icon"></i>JT Collection</a>
                                <a class="item" data-tab="projectNcList"> <span id="projectNcListCount"></span> NC</a>
                                <a class="item" data-tab="nachbauOperations"><i class="exchange icon"></i>Nachbau Operations</a>
                                <a class="item" data-tab="orderSummaryV2"><i class="th list icon"></i>Order Summary</a>
                                <a class="item" data-tab="bomNotes"><i class="sticky note outline icon"></i>BOM Notes</a>
                                <a class="item" data-tab="checklist"><i class="check square outline icon"></i>Checklist</a>
                                <a class="item" data-tab="publishProject"><i class="rocket icon"></i>Publish Project</a>
                            </div>
                            <div id="kukoMatrix" class="ui bottom active attached tab segment" style="display:none;" data-tab="kukoMatrix">
                                <?php include_once '../../projects/detail/tabs/kuko-matrix.php'; ?>
                            </div>
                            <div id="projectWork" class="ui bottom attached tab segment" style="display:none;" data-tab="projectWork">
                                <?php include_once '../../projects/detail/tabs/project-work/project-work.php'; ?>
                            </div>
                            <div id="jtCollection" class="ui bottom attached tab segment" style="display:none;" data-tab="jtCollection">
                                <?php include_once '../../projects/detail/tabs/jt-collection.php'; ?>
                            </div>
                            <!-- <div id="projectNcList" class="ui bottom attached tab segment" style="display:none;" data-tab="projectNcList">
                                <?php // include_once '../../projects/detail/tabs/nc-list.php'; ?>
                            </div> -->
                            <div id="nachbauOperations" class="ui bottom attached tab segment" style="display:none;" data-tab="nachbauOperations">
                                <?php include_once '../../projects/detail/tabs/nachbau-operations.php'; ?>
                            </div>
                            <div id="orderSummaryV2" class="ui bottom attached tab segment" style="display:none;" data-tab="orderSummaryV2">
                                <?php include_once '../../projects/detail/tabs/order-summary/order-summary-v2.php'; ?>
                            </div>
                            <div id="orderSummary" class="ui bottom attached tab segment" style="display:none;" data-tab="orderSummary">
                                <?php include_once '../../projects/detail/tabs/order-summary/order-summary.php'; ?>
                            </div>
                            <div id="bomNotes" class="ui bottom attached tab segment" style="display:none;" data-tab="bomNotes">
                                <?php include_once '../../projects/detail/tabs/bom-notes/bom-notes.php'; ?>
                            </div>
                            <div id="checklist" class="ui bottom attached tab segment" data-tab="checklist">
                                <?php include_once '../../projects/detail/tabs/checklist.php'; ?>
                            </div>
                            <div id="publishProject" class="ui bottom attached tab segment" style="display:none;" data-tab="publishProject">
                                <?php include_once '../../projects/detail/tabs/publish-project.php'; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php $footer_display = 'Project Detail';
    include_once '../../../../../assemblynotes/shared/footer.php'; ?>
</div>

<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/projects/detail/info.js?<?=uniqid()?>"></script>
</body>
</html>