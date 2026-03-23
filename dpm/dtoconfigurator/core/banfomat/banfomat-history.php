<?php
include_once $_SERVER["DOCUMENT_ROOT"] . '/checklogin.php';
include_once '../../api/models/Journals.php';

SharedManager::checkAuthToModule(35);
Journals::saveJournal('Accessing Banfomat History Page', PAGE_BANFOMAT, BANFOMAT_HISTORY, ACTION_VIEWED, null, 'Banfomat History');
SharedManager::saveLog('log_dtoconfigurator', 'Accessing Banfomat History Page');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banfomat Geçmişi | DTO Configurator</title>
    <?php include_once '../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/banfomat/index.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<!-- Sidebar -->
<div id="indexPageSidebar" class="ui visible left vertical sidebar menu" style="width:225px;background-color:#f3f3f0;">
    <div class="item">
        <img id="imgSiemensLogo" src="/dpm/dtoconfigurator/assets/images/siemens-dark.svg" alt="Siemens Logo">
    </div>
    <a href="/dpm/dtoconfigurator/core/admin/index.php" id="adminMenuItem" class="item active" style="display:none;">
        <i class="shield icon"></i> Admin
    </a>
    <a href="/dpm/dtoconfigurator/core/orders-plan/index.php" id="ordersPlanItem" class="item active" style="display:none;">
        <i class="calendar alternate icon"></i> Orders Plan
    </a>
    <a href="/dpm/dtoconfigurator/core/tkform/index.php" class="item active">
        <i class="clipboard outline icon"></i> TK Forms
    </a>
    <a href="/dpm/dtoconfigurator/core/projects/index.php" class="item">
        <i class="list alternate icon"></i> Projects
    </a>
    <a href="/dpm/dtoconfigurator/core/material-search/index.php" class="item">
        <i class="search icon"></i> Material Search
    </a>
    <a href="/dpm/dtoconfigurator/core/material-cost/index.php" class="item">
        <i class="euro sign icon"></i> Material Cost
    </a>
    <a href="/dpm/dtoconfigurator/core/material-define/index.php" class="item" target="_blank">
        <i class="cube icon"></i> Material Define
    </a>
    <a href="/dpm/dtoconfigurator/core/checklist/index.php" class="item" target="_blank">
        <i class="check square outline icon"></i> Checklist
    </a>
    <div class="item active">
        <div class="ui accordion" style="width:150%;">
            <div class="title active" style="display: flex; align-items: center; justify-content: space-between;padding:0;">
                <a href="/dpm/dtoconfigurator/core/banfomat/index.php"><i class="file excel outline icon" style="margin-left:7px;"></i> Banfomat</a>
                <i class="caret down icon" style="margin-right: -1px;"></i>
            </div>
            <div class="content active">
                <div id="banfomatSubMenus" class="menu">
                    <a href="/dpm/dtoconfigurator/core/banfomat/index.php" class="item info"><i class="cubes icon"></i>Banfomat Çalışma</a>
                    <a href="/dpm/dtoconfigurator/core/banfomat/banfomat-pool.php" target="_blank" class="item info"><i class="chart area icon"></i>Banfomat Havuzu</a>
                    <a href="/dpm/dtoconfigurator/core/banfomat/banfomat-history.php" target="_blank" class="item info active"><i class="clock icon"></i>Banfomat Geçmişi</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Main Content Area -->
<div id="banfomatHistoryPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>

    <div class="banfomatHistoryContainer ui container-fluid" style="padding-right:5%; margin-top:1.5rem;">
        <div class="ui grid">
            <div class="twelve wide column">
                <h2 class="ui header">
                    <i class="chart area icon"></i>
                    <div class="content">
                        Banfomat Geçmişi
                        <div class="sub header">Aşağıdaki tablo sipariş ve lot bazlı oluşturulmuş banfomat excel listesini içerir.</div>
                    </div>
                </h2>
            </div>
        </div>

        <div id="banfomatHistoryNotFoundMsg" class="ui red compact message" style="display:none;">
            <i class="exclamation circle icon"></i>Banfomat geçmişine ait tablo verisi bulunamadı.
        </div>

        <!-- Banfomat History Data Table -->
        <div id="banfomatHistoryTableContainer" style="display:none;margin-top:2.5rem;">
            <h4 class="ui block top attached header">
                <i class="calculator icon"></i>
                <div class="content">
                    Banfomat Geçmişi
                </div>
            </h4>

            <div class="ui attached segment">
                <table id="banfomatHistoryDataTable" class="ui striped hover compact celled table stackable padded">
                    <thead>
                        <tr>
                            <th>Sipariş</th>
                            <th>Lot</th>
                            <th>DTO Metali</th>
                            <th>Gümüş Kaplama Alan</th>
                            <th>Kalay Kaplama Alan</th>
                            <th>Nikel Kaplama Alan</th>
                            <th>Excel</th>
                            <th>Oluşturan</th>
                            <th>Tarih</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/banfomat/banfomat-history.js?<?=uniqid()?>"></script>
</body>
</html>
