<?php
include_once $_SERVER["DOCUMENT_ROOT"] . '/checklogin.php';
include_once '../../api/models/Journals.php';

SharedManager::checkAuthToModule(35);
Journals::saveJournal('Accessing Orders Plan Page', PAGE_ORDERS_PLAN, ORDERS_PLAN_INDEX, ACTION_PROCESSING, null, 'Orders Plan');
SharedManager::saveLog('log_dtoconfigurator', 'Accessing Orders Plan Page');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTO Configurator | Orders Plan</title>
    <?php include_once '../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/orders-plan/style.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<!-- Sidebar -->
<?php include_once '../../partials/sidebar.php'; ?>

<!-- Main Content Area -->
<div id="ordersPlanPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../partials/header.php'; ?>

    <div id="ordersPlanPageContainer" class="ui container-fluid" style="padding-right:2%;">

        <h3 class="ui header">
            <i class="calendar alternate icon"></i>
            <div class="content">
                Orders Plan
                <div class="sub header">Manage Projects</div>
            </div>
        </h3>

        <div class="ui divider"></div>
        <div class="ui active centered inline loader" style="margin-top:10%;"></div>

        <div id="ordersPlanTableContainer" class="sixteen wide column" style="display:none;">
            <table id="ordersPlanTable" class="ui celled table striped stackable compact padded hover" style="width:100%;">
                <thead>
                <tr>
                    <th>Project No</th>
                    <th>Project Name</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>OM</th>
                    <th>ME</th>
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
<script src="/dpm/dtoconfigurator/assets/js/orders-plan/index.js?<?=uniqid()?>"></script>
</body>
</html>
