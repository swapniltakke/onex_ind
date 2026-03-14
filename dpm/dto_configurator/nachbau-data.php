<?php
// /dpm/dto_configurator/nachbau-data.php
SharedManager::checkAuthToModule(24);
include_once '../core/index.php';

$project = $_GET["project"] ?: 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>OneX | Nachbau Data - <?php echo $project; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=yes"/>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta charset="utf-8">

    <link href="../../css/semantic.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="../../css/dataTables.semanticui.min.css">
    <link rel="stylesheet" type="text/css" href="../../css/responsive.dataTables.min.css">
    <link href="../../css/main.css?13" rel="stylesheet"/>

    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/semantic.min.js"></script>
    <script src="../../js/jquery.dataTables.js"></script>
    <script src="../../js/dataTables.semanticui.min.js"></script>
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

    .ui.segment {
        margin-bottom: 20px;
    }

    .ui.table {
        margin-bottom: 0;
    }

    .ui.table th {
        background-color: #f8f9fa;
        vertical-align: middle;
    }

    .ui.table td {
        vertical-align: top;
    }

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

    .nachbau-file-item {
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .nachbau-file-item:hover {
        background-color: #f9f9f9;
        border-color: #00b5ad;
    }

    .nachbau-file-item.active {
        background-color: #e8f5f4;
        border-color: #00b5ad;
    }

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
</style>
<body>
<div id="wrapper">
    <?php $activePage = '/nachbau-data.php'; ?>
    <?php include_once '../shared/dto_sidebar.php'; ?>
    <div id="page-wrapper" class="gray-bg">
        <!-- Custom Loader Overlay -->
        <div class="custom-loader-overlay" id="customLoaderOverlay">
            <div class="custom-loader-container">
                <div class="custom-loader-spinner"></div>
                <p class="custom-loader-text">Loading</p>
            </div>
        </div>

        <div class="ui active centered inline loader" id="initialLoader" style="margin-top:10%;"></div>

        <div id="nachbauPageContainer" style="display:none; padding: 20px;">
            <!-- Header -->
            <div class="ui segment">
                <h2>Nachbau Data for Project <span id="projectNumberDisplay"></span></h2>
            </div>

            <!-- Nachbau Filter Section -->
            <div id="nachbauFilterSection"></div>

            <!-- Project Data Tabs -->
            <div id="projectDataTabs" style="display:none;">
                <div id="projectDataTabularMenu" class="ui top attached tabular menu">
                    <a class="active item" data-tab="kukoMatrix"><i class="th icon"></i>Kuko Matrix</a>
                    <a class="item" data-tab="projectWork"><i class="wrench icon"></i>Project Work</a>
                    <a class="item" data-tab="orderSummary"><i class="chart bar icon"></i>Order Summary</a>
                </div>

                <!-- Kuko Matrix Tab -->
                <div id="kukoMatrix" class="ui bottom attached tab segment active" data-tab="kukoMatrix">
                    <?php include_once '../tabs/kuko-matrix.php'; ?>
                </div>

                <!-- Project Work Tab -->
                <div id="projectWork" class="ui bottom attached tab segment" data-tab="projectWork">
                    <div id="projectWorkContainer"></div>
                </div>

                <!-- Order Summary Tab -->
                <div id="orderSummary" class="ui bottom attached tab segment" data-tab="orderSummary">
                    <div id="orderSummaryContainer"></div>
                </div>
            </div>
        </div>

        <div id="nachbauErrorDiv" style="display:none; padding: 20px;">
            <div class="ui message error">
                <i class="close icon"></i>
                <div class="header">Error Loading Nachbau Data</div>
                <p id="nachbauErrorMsg"></p>
            </div>
        </div>
    </div>
    <?php $footer_display = 'Nachbau Data';
    include_once '../../assemblynotes/shared/footer.php'; ?>
</div>

<script src="/dpm/dto_configurator/js/projects/detail/nachbau-data.js?<?=uniqid()?>"></script>
<script src="/dpm/dto_configurator/js/projects/detail/tabs/kuko-matrix.js?<?=uniqid()?>"></script>
</body>
</html>