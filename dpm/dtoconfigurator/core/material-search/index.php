<?php
include_once $_SERVER["DOCUMENT_ROOT"] . '/checklogin.php';
include_once '../../api/models/Journals.php';

SharedManager::checkAuthToModule(35);
Journals::saveJournal('Accessing Material Search Page', PAGE_MATERIAL_SEARCH, MATERIAL_SEARCH, ACTION_PROCESSING, null, 'Material Search');
SharedManager::saveLog('log_dtoconfigurator', 'Accessing Material Search Page');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTO Configurator | Material Search</title>
    <?php include_once '../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/material-search/index.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<!-- Sidebar -->
<?php include_once '../../partials/sidebar.php'; ?>

<!-- Main Content Area -->
<div id="materialSearchPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>

    <div class="materialSearchContainer ui container-fluid" style="padding-right:5%;">
        <div class="ui icon message warning">
            <i class="info circle icon"></i>
            <div class="content">
                <div class="header">
                    Find Out How to Use This Page
                </div>
                <p>This page enables searching for specific materials and displays the DTO numbers that include them, offering a streamlined way to track material usage.</p>
            </div>
        </div>

        <h2 class="ui header">
            <i class="cubes icon"></i>
            <div class="content">
                Materials
                <div class="sub header">Search for materials that exist in TK Forms</div>
            </div>
        </h2>

        <div class="ui grid">
            <div class="thirteen wide column">
                <select id="materialSearch" name="materialSearch" class="ui fluid search selection dropdown">
                    <option value="">Search Material</option>
                </select>
                <div id="materialSearchErrMsg" class="ui pointing red basic label hidden">Please select a material!</div>
            </div>
            <div class="three wide column">
                <button id="btnMaterialSearch" type="button" class="ui instagram button fluid"><i class="search icon"></i>Search</button>
            </div>
        </div>

        <div id="materialSearchNotFoundMsg" class="ui red compact message hidden">
            <i class="exclamation circle icon"></i>There is no TK found associated with searched material.
        </div>

        <div id="table-loader" class="ui active centered inline loader hidden" style="margin-top:10%;"></div>

        <!-- Material Search List Table -->
        <div id="materialSearchListTableContainer" style="display:none;margin-top:2.5rem;">

            <h4 class="ui block top attached header">
                <i class="table icon"></i>
                <div class="content">
                    Material and DTO Lists
                </div>
            </h4>

            <div class="ui attached segment">
                <table id="materialSearchListTable" class="ui celled table" >
                    <thead>
                        <tr>
                            <th>TK Number</th>
                            <th>DTO Number</th>
                            <th>Description</th>
                            <th>Added Material</th>
                            <th>Added Material Desc.</th>
                            <th>Deleted Material</th>
                            <th>Deleted Material Desc.</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/material-search/index.js?<?=uniqid()?>"></script>
</body>
</html>
