<?php
include_once $_SERVER["DOCUMENT_ROOT"] . '/checklogin.php';
include_once '../../api/models/Journals.php';

SharedManager::checkAuthToModule(35);
//Journals::saveJournal('Accessing Material Cost Page', PAGE_MATERIAL_COST, MATERIAL_COST, ACTION_PROCESSING, null, 'Material Cost');
SharedManager::saveLog('log_dtoconfigurator', 'Accessing Material Cost Page');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTO Configurator | Material Cost</title>
    <?php include_once '../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/material-cost/index.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<!-- Sidebar -->
<?php include_once '../../partials/sidebar.php'; ?>

<!-- Main Content Area -->
<div id="materialCostPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>

    <div class="materialCostContainer ui container-fluid" style="padding-right:5%;display:none;">
        <div class="ui icon message info">
            <i class="info circle icon"></i>
            <div class="content">
                <div class="header">
                    Find Out How to Use This Page
                </div>
                <p>This page enables searching for material costs. You can select up to 10 materials to view their current costs in both TL and Euro.</p>
            </div>
        </div>

        <h2 class="ui header">
            <i class="euro sign icon"></i>
            <div class="content">
                Material Cost
                <div class="sub header">Search for material costs (up to 10 materials)</div>
            </div>
        </h2>

        <div class="ui grid">
            <div class="thirteen wide column">
                <select id="materialCostSearch" name="materialCostSearch" class="ui fluid search selection dropdown" multiple="">
                    <option value="">Search Materials (Max 10)</option>
                </select>
                <div id="materialCostSearchErrMsg" class="ui pointing red basic label hidden">Please select at least one material!</div>
                <div id="materialCostMaxLimitMsg" class="ui pointing orange basic label hidden">You can select maximum 10 materials!</div>
            </div>
            <div class="three wide column">
                <button id="btnMaterialCostSearch" type="button" class="ui instagram button fluid"><i class="search icon"></i>Search</button>
            </div>
        </div>

        <div id="materialCostNotFoundMsg" class="ui red compact message hidden">
            <i class="exclamation circle icon"></i>No cost data found for the selected materials.
        </div>

        <div id="table-loader" class="ui active centered inline loader hidden" style="margin-top:10%;"></div>

        <!-- Material Cost List Table -->
        <!-- Material Cost List Table -->
        <div id="materialCostListTableContainer" style="display:none;margin-top:2.5rem;">

            <h4 class="ui block top attached header">
                <i class="table icon"></i>
                <div class="content">
                    Material Cost List
                </div>
            </h4>

            <div class="ui attached segment">
                <table id="materialCostListTable" class="ui celled table">
                    <thead>
                    <tr>
                        <th>Material</th>
                        <th>Cost (Euro)</th>
                        <th>Cost (TL)</th>
                        <th>Exchange Rate</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                    <tr>
                        <th style="text-align: center; font-weight: bold;">GRAND TOTAL</th>
                        <th id="totalCostEuro" style="text-align: center;"></th>
                        <th id="totalCostTL" style="text-align: center;"></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>


<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/material-cost/index.js?<?=uniqid()?>"></script>
</body>
</html>