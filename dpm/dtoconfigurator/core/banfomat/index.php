<?php
include_once $_SERVER["DOCUMENT_ROOT"] . '/checklogin.php';
include_once '../../api/models/Journals.php';

SharedManager::checkAuthToModule(35);
Journals::saveJournal('Accessing Banfomat Index Page', PAGE_BANFOMAT, BANFOMAT_INDEX, ACTION_VIEWED, null, 'Banfomat Index');
SharedManager::saveLog('log_dtoconfigurator', 'Accessing Banfomat Page');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banfomat | DTO Configurator</title>
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
                    <a href="/dpm/dtoconfigurator/core/banfomat/index.php" class="item info active"><i class="cubes icon"></i>Banfomat Çalışma</a>
                    <a href="/dpm/dtoconfigurator/core/banfomat/banfomat-pool.php" target="_blank" class="item info"><i class="chart area icon"></i>Banfomat Havuzu</a>
                    <a href="/dpm/dtoconfigurator/core/banfomat/banfomat-history.php" target="_blank" class="item info"><i class="clock icon"></i>Banfomat Geçmişi</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Area -->
<div id="banfomatPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>

    <div class="banfomatContainer ui container-fluid" style="padding-right:5%;">
        <div class="ui icon message warning">
            <i class="info circle icon"></i>
            <div class="content">
                <div class="header">
                    Bu Sayfa Hakkında
                </div>
                <p>Bu sayfa, siparişte geçen kaplama DTO'larını görüntüleyip, bakır kaplamalı siparişlerde kaplanacak parçaları detaylandırmak için Banfomat Excel oluşturmak amacıyla kullanılmaktadır.</p>
            </div>
        </div>

        <div class="ui grid">
            <div class="eleven wide column">
                <h2 class="ui header">
                    <i class="cubes icon"></i>
                    <div class="content">
                        Sipariş Ara
                        <div class="sub header">Siparişteki bakırları ve kaplama DTO'larını görüntülemek için aşağıdan sipariş arayın.</div>
                    </div>
                </h2>
            </div>

            <div class="five wide column" style="display: flex;align-items: center;justify-content: end;">
                <div id="proceedToProjectBanfomatInfoButton" class="ui teal large circular icon compact button" style="display:none;">
                    <i class="right arrow icon" style="margin-right:0.3rem!important;"></i>
                    Sipariş Kaplama Bilgileri (Excel Oluşturma Sayfası)
                </div>
            </div>
        </div>

        <div class="ui grid">
            <div class="sixteen wide column">
                <select id="banfomatProjectSearch" name="banfomatProjectSearch" class="ui fluid search selection dropdown">
                    <option value="">Sipariş Ara</option>
                </select>
                <div id="projectSearchErrMsg" class="ui pointing red basic label hidden">Lütfen sipariş seçiniz!</div>
            </div>
        </div>

        <div class="ui active centered inline loader" style="margin-top:10%;"></div>

        <!-- Nachbau Copper Coat Details Table -->
        <div id="nachbauCopperCoatDetailsContainer" style="display:none;margin-top:2.5rem;">
            <h4 class="ui block top attached header">
                <i class="table icon"></i>
                <div class="content">
                    Aktarımda Geçen Kaplama DTO'su
                </div>
            </h4>

            <div class="ui attached segment">
                <table id="nachbauCopperCoatDetailsTable" class="ui celled table">
                    <thead>
                        <tr>
                            <th>Sipariş No</th>
                            <th>Lotlar</th>
                            <th>Son Aktarım No</th>
                            <th>DTO Numarası</th>
                            <th>Açıklama</th>
                            <th>Kalay</th>
                            <th>Gümüş</th>
                            <th>Nikel</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;margin-top:1rem;margin-bottom:0.9rem;">

            <div id="coppersDataSourceMsg" class="ui compact message warning small" style="display:none; margin: 0;">
                <i class="info circle icon"></i>
                Veri kaynağı : <span class="coppersDataSourceSpan" style="font-weight:bold;"></span>
            </div>
            <div id="projectPublishStatus" class="ui compact message info small" style="display:none; margin: 0;">
                <i class="info circle icon"></i>
                Proje Yayın Durumu : <span class="projectPublishStatusSpan" style="font-weight:bold;"></span>
            </div>

        </div>


        <!-- Copper Material List Table -->
        <div id="copperMaterialListTableContainer" style="display:none;margin-top:0.5rem;">
            <h4 class="ui block top attached header">
                <i class="table icon"></i>
                <div class="content">
                    Siparişte Geçen Bütün Bakırlar (Lotlara Göre Tablo)
                </div>
            </h4>

            <div class="ui attached segment">
                <table id="copperMaterialListTable" class="ui striped celled compact table stackable padded">
                    <thead>
                        <tr>
                            <th>Lot</th>
                            <th>Malzeme</th>
                            <th>Açıklama</th>
                            <th>Industry Desc</th>
                            <th>Adet</th>
                            <th>Birim</th>
                            <th>Üst Liste</th>
                            <th>Supply Area</th>
                            <th>MRP</th>
                            <th>SLoc</th>
                            <th>KMAT</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="fullDescriptionModal" class="ui mini modal">
        <div class="header"></div>
        <div class="scrolling content"></div>
    </div>
</div>

<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/banfomat/index.js?<?=uniqid()?>"></script>
</body>
</html>
