<?php
include_once $_SERVER["DOCUMENT_ROOT"] . '/checklogin.php';
include_once '../../api/models/Journals.php';

SharedManager::checkAuthToModule(35);
Journals::saveJournal('Accessing DTO Cable Codes Page', PAGE_DTO_CABLE_CODES, DTO_CABLE_CODES_INDEX, ACTION_VIEWED, null, 'DTO Cable Codes Page Index');
SharedManager::saveLog('log_dtoconfigurator', 'Accessing DTO Cable Codes Page');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTO Configurator | DTO Cable Codes</title>
    <?php include_once '../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/dto-cable-codes/style.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<!-- Sidebar -->
<?php include_once '../../partials/sidebar.php'; ?>

<!-- Main Content Area -->
<div id="dtoCableCodesPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>

    <div id="dtoCableCodesPageContainer" class="ui container-fluid" style="padding-right:5%;display:none;">

        <!-- Page Header -->
        <div class="ui grid" style="margin-top:1rem;margin-bottom:1rem;">
            <div class="twelve wide column">
                <h2 class="ui header">
                    <i class="plug icon"></i>
                    <div class="content">
                        DTO Cable Codes
                        <div class="sub header">View all VTH and CTH cable codes with their specifications</div>
                    </div>
                </h2>
            </div>

            <div class="four wide column">
                <div class="ui teal circular icon compact button" style="float:right;margin-top: 3px;" onclick="window.open('/dpm/dtoconfigurator/core/dto-cable-codes/add-cable-item.php', '_blank')">
                    <i class="plus icon" style="margin-right:0.3rem!important;"></i>
                    Add Cable Code
                </div>
            </div>
        </div>

        <div class="ui divider"></div>

        <!-- Tab Menu -->
        <div class="ui top attached tabular menu" style="display:flex;justify-content:center;">
            <div class="active item" data-tab="vth">
                <i class="blue lightning icon"></i>
                VTH Cables
                <div class="ui blue circular label" id="vthCount">0</div>
            </div>
            <div class="item" data-tab="cth">
                <i class="purple plug icon"></i>
                CTH Cables
                <div class="ui purple circular label" id="cthCount">0</div>
            </div>
        </div>

        <!-- VTH Tab Content -->
        <div class="ui bottom attached active tab segment" data-tab="vth">
            <!-- VTH Filters -->
            <div class="ui grid" style="margin-bottom: 1rem;">
                <div class="ten wide column">
                    <div class="ui fluid multiple selection dropdown" id="vthProductFilter">
                        <input type="hidden" name="vthProducts">
                        <i class="dropdown icon"></i>
                        <div class="default text">Filter VTH by Products</div>
                        <div class="menu">
                            <div class="item" data-value="1">NXAIR 1C</div>
                            <div class="item" data-value="2">NXAIR 50kA</div>
                            <div class="item" data-value="3">NXAIR H</div>
                            <div class="item" data-value="4">NXAIR World</div>
                            <div class="item" data-value="5">NXAIR</div>
                        </div>
                    </div>
                </div>
                <div class="six wide column" style="display: flex;justify-content: space-between;">
                    <div class="ui small button" id="vthFilterCount" style="margin-left: 10px; display: none;">
                        <i class="filter icon"></i>
                        <span id="vthFilterCountText">0</span> VTH items filtered
                    </div>
                    <button class="ui small blue button" id="clearVthFilters">
                        <i class="eraser icon"></i>
                        Clear VTH Filters
                    </button>
                </div>
            </div>


            <!-- VTH Table -->
            <div id="vthTableContainer" class="ui segment cableTableContainer" style="overflow:auto;">
                <table id="vthTable" class="ui celled table striped stackable compact padded hover cableTable" style="width:100%; min-width: 1800px;">
                    <thead>
                    <tr>
                        <th rowspan="2">Definition</th>
                        <th rowspan="2">TR Notes</th>
                        <th rowspan="2">Harness No</th>
                        <th rowspan="2">VTH Code</th>
                        <th rowspan="2">Drawing No</th>
                        <th rowspan="2">Cable Type</th>
                        <th rowspan="2">Cross Section (mm<sup>2</sup>)</th>
                        <th rowspan="2">Length Type</th>
                        <th colspan="6" class="ui center aligned" style="background-color: #f0f8ff; border-bottom: 2px solid #2185d0;">
                            <i class="paint brush icon" style="color: #2185d0;"></i>
                            Wire Colors
                        </th>
                        <th rowspan="2">Place</th>
                        <th rowspan="2">Panel Width (mm)</th>
                        <th rowspan="2">Core</th>
                        <th rowspan="2">Order</th>
                        <th colspan="7" class="ui center aligned" style="background-color: #fff8f0; border-bottom: 2px solid #f2711c;">
                            <i class="cube icon" style="color: #f2711c;"></i>
                            Products
                        </th>
                        <th rowspan="2">Additional Info</th>
                        <th rowspan="2">Created</th>
                        <th rowspan="2">Actions</th>
                    </tr>
                    <tr>
                        <th style="background-color: #f0f8ff;">T5L1-A</th>
                        <th style="background-color: #f0f8ff;">T5L1-N</th>
                        <th style="background-color: #f0f8ff;">T5L2-A1</th>
                        <th style="background-color: #f0f8ff;">T5L2-N1</th>
                        <th style="background-color: #f0f8ff;">T5L2-A2</th>
                        <th style="background-color: #f0f8ff;">T5L2-N2</th>
                        <th style="background-color: #fff8f0;">NX</th>
                        <th style="background-color: #fff8f0;">SEC</th>
                        <th style="background-color: #fff8f0;">C</th>
                        <th style="background-color: #fff8f0;">50kA</th>
                        <th style="background-color: #fff8f0;">M</th>
                        <th style="background-color: #fff8f0;">H</th>
                        <th style="background-color: #fff8f0;">8BT2</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- CTH Tab Content -->
        <div class="ui bottom attached tab segment" data-tab="cth">
            <!-- CTH Filters -->
            <div class="ui grid" style="margin-bottom: 1rem;">
                <div class="ten wide column">
                    <div class="ui fluid multiple selection dropdown" id="cthProductFilter">
                        <input type="hidden" name="cthProducts">
                        <i class="dropdown icon"></i>
                        <div class="default text">Filter CTH by Products</div>
                        <div class="menu">
                            <div class="item" data-value="1">NXAIR 1C</div>
                            <div class="item" data-value="2">NXAIR 50kA</div>
                            <div class="item" data-value="3">NXAIR H</div>
                            <div class="item" data-value="4">NXAIR World</div>
                            <div class="item" data-value="5">NXAIR</div>
                        </div>
                    </div>
                </div>
                <div class="six wide column" style="display: flex;justify-content: space-between;">
                    <div class="ui small button" id="cthFilterCount" style="margin-left: 10px; display: none;">
                        <i class="filter icon"></i>
                        <span id="cthFilterCountText">0</span> CTH items filtered
                    </div>
                    <button class="ui small blue button" id="clearCthFilters">
                        <i class="eraser icon"></i>
                        Clear CTH Filters
                    </button>
                </div>
            </div>

            <!-- CTH Table -->
            <div id="cthTableContainer" class="ui segment cableTableContainer" style="overflow:auto;">
                <table id="cthTable" class="ui celled table striped stackable compact padded hover cableTable" style="width:100%; min-width: 2400px;">
                    <thead>
                    <tr>
                        <th rowspan="2">Definition</th>
                        <th rowspan="2">TR Notes</th>
                        <th rowspan="2">Harness No</th>
                        <th rowspan="2">CTH Code</th>
                        <th rowspan="2">Drawing No</th>
                        <th rowspan="2">Cable Type</th>
                        <th rowspan="2">Cross Section (mm<sup>2</sup>)</th>
                        <th rowspan="2">Length Type</th>
                        <th colspan="9" class="ui center aligned" style="background-color: #f8f9ff; border-bottom: 2px solid #6435c9;">
                            <i class="paint brush icon" style="color: #6435c9;"></i>
                            Wire Colors
                        </th>
                        <th rowspan="2">Length L1+L2+L3 (mm)</th>
                        <th rowspan="2">Cable Qty (Group)</th>
                        <th rowspan="2">Total Length</th>
                        <th rowspan="2">CT in Rear Box</th>
                        <th rowspan="2">Place</th>
                        <th rowspan="2">Panel Width (mm)</th>
                        <th rowspan="2">Core</th>
                        <th rowspan="2">Order</th>
                        <th colspan="7" class="ui center aligned" style="background-color: #fff8f0; border-bottom: 2px solid #f2711c;">
                            <i class="cube icon" style="color: #f2711c;"></i>
                            Products
                        </th>
                        <th rowspan="2">Additional Info</th>
                        <th rowspan="2">Created</th>
                        <th rowspan="2">Actions</th>
                    </tr>
                    <tr>
                        <th style="background-color: #f8f9ff;">T1L1-1S1</th>
                        <th style="background-color: #f8f9ff;">T1L1-1S2</th>
                        <th style="background-color: #f8f9ff;">T1L1-1S3</th>
                        <th style="background-color: #f8f9ff;">T1L2-1S1</th>
                        <th style="background-color: #f8f9ff;">T1L2-1S2</th>
                        <th style="background-color: #f8f9ff;">T1L2-1S3</th>
                        <th style="background-color: #f8f9ff;">T1L3-1S1</th>
                        <th style="background-color: #f8f9ff;">T1L3-1S2</th>
                        <th style="background-color: #f8f9ff;">T1L3-1S3</th>
                        <th style="background-color: #fff8f0;">NX</th>
                        <th style="background-color: #fff8f0;">SEC</th>
                        <th style="background-color: #fff8f0;">C</th>
                        <th style="background-color: #fff8f0;">50kA</th>
                        <th style="background-color: #fff8f0;">M</th>
                        <th style="background-color: #fff8f0;">H</th>
                        <th style="background-color: #fff8f0;">8BT2</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/dto-cable-codes/index.js?<?=uniqid()?>"></script>
</body>
</html>