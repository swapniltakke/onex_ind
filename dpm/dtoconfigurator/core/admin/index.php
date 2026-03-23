<?php
include_once $_SERVER["DOCUMENT_ROOT"] . '/checklogin.php';
include_once '../../api/models/Journals.php';

SharedManager::checkAuthToModule(35);
Journals::saveJournal('Accessing Admin Index Page', PAGE_ADMIN, ADMIN_INDEX, ACTION_PROCESSING, null, 'Admin Index');
SharedManager::saveLog('log_dtoconfigurator', 'Accessing Admin Index Page');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTO Configurator | Admin</title>
    <?php include_once '../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/admin/index.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<!-- Sidebar -->
<?php include_once '../../partials/sidebar.php'; ?>
<!-- Main Content Area -->
<div id="adminPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>

    <div id="adminPageContainer" class="ui container-fluid" style="padding-right:5%;display:none;">
            <h3 class="ui horizontal divider header" style="margin:2.5rem auto;width:75%;">
                Released Projects Summary
            </h3>

            <div id="statusFilterContainer" class="sixteen wide column ui segment" style="margin-bottom: 15px; ">
                <div class="ui horizontal list" style="width:100%;text-align:center;">
                    <div class="item">
                        <strong>Filter by Status:</strong>
                    </div>
                    <div class="item">
                <span class="ui basic label blue status-filter" data-status="3" style="cursor: pointer; margin-right: 10px;">
                    Pending Approval
                </span>
                    </div>
                    <div class="item">
                <span class="ui basic label red status-filter" data-status="4" style="cursor: pointer; margin-right: 10px;">
                    Rejected
                </span>
                    </div>
                    <div class="item">
                <span class="ui basic label green status-filter" data-status="5" style="cursor: pointer; margin-right: 10px;">
                    Published
                </span>
                    </div>
                    <div class="item">
                        <button class="ui mini button" id="clearStatusFilters" style="margin-left: 10px; display: none;">
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>
            <div id="releasedProjectsTableContainer" class="sixteen wide column">
                <table id="releasedProjectsTable" class="ui celled table striped stackable compact padded hover" style="width:100%;">
                    <thead>
                    <tr>
                        <th>Project No</th>
                        <th>Project Name</th>
                        <th>Nachbau No</th>
                        <th>Nachbau Date</th>
                        <th>Product</th>
                        <th>Ass. Start</th>
                        <th>Panels</th>
                        <th>Voltage</th>
                        <th>S.C.</th>
                        <th>Current</th>
                        <th>Contacts</th>
                        <th>Sent By</th>
                        <th>Sent Date</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be populated here by JavaScript -->
                    </tbody>
                </table>
            </div>

    </div>
</div>

<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/admin/index.js?<?=uniqid()?>"></script>
</body>
</html>
