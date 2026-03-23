<?php
include_once $_SERVER["DOCUMENT_ROOT"] . '/checklogin.php';
include_once '../../api/models/Journals.php';

SharedManager::checkAuthToModule(35);
Journals::saveJournal('Accessing Checklist Page', PAGE_CHECKLIST, CHECKLIST_INDEX, ACTION_PROCESSING, null, 'Checklist Page');
SharedManager::saveLog('log_dtoconfigurator', 'Accessing Checklist Page');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTO Configurator | Checklist Page</title>
    <?php include_once '../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/checklist/style.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<!-- Sidebar -->
<?php include_once '../../partials/sidebar.php'; ?>

<!-- Main Content Area -->
<div id="checklistIndexPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>

    <div id="checklistIndexPageContainer" class="ui container-fluid" style="padding-right:5%;display:none;">

        <!-- Page Header -->
        <div class="ui grid" style="margin-top:1rem;margin-bottom:1rem;">
            <div class="twelve wide column">
                <h2 class="ui header" >
                    <i class="check square outline icon"></i>
                    <div class="content">
                        Checklist Items
                        <div class="sub header">View all checklist items and their product assignments</div>
                    </div>
                </h2>
            </div>

            <div class="four wide column">
                <div class="ui teal circular icon compact button" style="float:right;margin-top: 3px;" onclick="window.open('/dpm/dtoconfigurator/core/checklist/add-checklist-item.php', '_blank')">
                    <i class="plus icon" style="margin-right:0.3rem!important;"></i>
                    Add Checklist Item
                </div>
            </div>
        </div>

        <div class="ui divider"></div>


        <!-- Filters Section -->
            <div class="ui grid">
                <div class="eight wide column">
                    <div class="ui fluid multiple selection dropdown" id="categoryFilter">
                        <input type="hidden" name="categories">
                        <i class="dropdown icon"></i>
                        <div class="default text">Filter by Categories</div>
                        <div class="menu">
                            <!-- Categories will be populated dynamically -->
                        </div>
                    </div>
                </div>
                <div class="eight wide column">
                    <div class="ui fluid multiple selection dropdown" id="productFilter">
                        <input type="hidden" name="products">
                        <i class="dropdown icon"></i>
                        <div class="default text">Filter by Products</div>
                        <div class="menu">
                            <div class="item" data-value="1">NXAIR 1C</div>
                            <div class="item" data-value="2">NXAIR 50kA</div>
                            <div class="item" data-value="3">NXAIR H</div>
                            <div class="item" data-value="4">NXAIR World</div>
                            <div class="item" data-value="5">NXAIR</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ui grid" style="margin-top: 10px;">
                <div class="sixteen wide column" style="display:flex;justify-content:center;">
                    <div class="ui large label" id="filterCount" style="margin-left: 10px; display: none;">
                        <i class="filter icon"></i>
                        <span id="filterCountText">0</span> items filtered
                    </div>
                    <button class="ui small button" id="clearFilters">
                        <i class="eraser icon"></i>
                        Clear All Filters
                    </button>
                </div>
            </div>

        <!-- DataTable -->
        <div id="checklistTableContainer"  class="ui segment">
            <table id="checklistTable" class="ui celled table striped stackable compact padded hover" style="width:100%;">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Detail</th>
                    <th>Category</th>
                    <th style="text-align: center;">NXAIR 1C</th>
                    <th style="text-align: center;">NXAIR 50kA</th>
                    <th style="text-align: center;">NXAIR H</th>
                    <th style="text-align: center;">NXAIR World</th>
                    <th style="text-align: center;">NXAIR</th>
                    <th style="text-align: center;">Image</th>
                    <th style="text-align: center;"></th>
                </tr>
                </thead>
                <tbody>
                <!-- Data populated by DataTable -->
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="ui modal" id="imageEnlargeModal">
    <i class="close icon"></i>
    <div class="content" style="text-align: center;padding: 20px;">
        <img id="enlargedImage" src="" style="max-width: 100%; height: auto; margin: 0 auto; display: block;">
    </div>
</div>


<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/checklist/index.js?<?=uniqid()?>"></script>
</body>
</html>
