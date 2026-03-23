<?php
include_once $_SERVER["DOCUMENT_ROOT"] . '/checklogin.php';
include_once '../../api/models/Journals.php';
header('Content-Type: text/html; charset=UTF-8');

SharedManager::checkAuthToModule(35);
Journals::saveJournal('Accessing Banfomat Page', PAGE_BANFOMAT, PROJECT_BANFOMAT_INFO, ACTION_VIEWED, null, 'Project Banfomat Info');
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
<div id="projectBanfomatInfoPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>

    <div class="projectBanfomatInfoContainer ui container-fluid" style="padding-right:5%;">
        <div id="projectBanfomatCoatTypeInfo" style="display:none;margin-bottom:0.8rem;">
            <div class="ui icon message info">
                <i class="info circle icon"></i>
                <div class="content">
                    <div class="header">
                        Önemli: Siparişte Tespit Edilen Kaplama Tipi
                    </div>
                    <p style="line-height:1.6rem;">
                        Bu siparişte <b><span class="coatTypeSpan"></span> KAPLAMA</b> DTO'su tespit edilmiştir. Aşağıdaki tabloda bulunan malzemelerin <b><span class="coatTypeSpan"></span> DTO metaline</b> göre alan bilgileri getirilmiştir.
                    </p>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <div style="display: flex; gap: 1rem;">
                <div id="silverCoatedTypeDiv" class="ui grey label item" style="background-color:darkcyan!important;display:none; font-size:1rem!important;">
                    <i class="paint brush icon"></i>
                    Siparişte <span id="silverCoatedTypeLabel"></span> Kaplanacak Toplam Alan <span id="totalSilverCoatedArea"></span> (m&sup2)
                </div>
                <div id="tinCoatedTypeDiv" class="ui blue label item" style="background-color:darkblue;display:none; font-size:1rem!important;">
                    <i class="paint brush icon"></i>
                    Siparişte <span id="tinCoatedTypeLabel"></span> Kaplanacak Toplam Alan <span id="totalTinCoatedArea"></span> (m&sup2)
                </div>
                <div id="nickelCoatedTypeDiv" class="ui teal label item" style="display:none; font-size:1rem!important;">
                    <i class="paint brush icon"></i>
                    Siparişte <span id="nickelCoatedTypeLabel"></span> Kaplanacak Toplam Alan <span id="totalNickelCoatedArea"></span> (m&sup2)
                </div>
            </div>

            <div>
                <button id="exportExcelBtn" class="ui green large icon compact button" style="display:none;">
                    <i class="download icon" style="margin-right:0.3rem!important;"></i>
                    Excel Oluştur
                </button>
            </div>
        </div>

        <!-- Project Banfomat Info Table -->
        <div id="projectBanfomatInfoTableContainer" style="display:none;margin-top:0.7rem;">
            <div class="ui form" style="margin-bottom: 1rem; width:25%;">
                <div class="field">
                    <label>Lot Filtresi</label>
                    <select id="lotFilterDropdown" class="ui fluid dropdown" multiple>
                    </select>
                </div>
            </div>

            <h4 class="ui block top attached header">
                <i class="table icon"></i>
                <div class="content">
                    Banfomat Excel Önizleme Tablosu
                </div>
            </h4>

            <div class="ui attached segment">
                <table id="projectBanfomatInfoListDataTable" class="ui striped hover compact celled table stackable padded">
                    <thead>
                        <tr>
                            <th>Sipariş</th>
                            <th>Lot</th>
                            <th>Malzeme</th>
                            <th>Adet</th>
                            <th>Birim Alan (m&sup2)</th>
                            <th>Toplam Alan (m&sup2)</th>
                            <th>Kaplama Türü</th>
                            <th>Kap. Bölge</th>
                            <th>Detay</th>
                            <th>Supply Area</th>
                            <th>MRP</th>
                            <th>Üretim Yeri</th>
                            <th>Resim</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="chooseLotModal" class="ui modal center aligned">
        <i class="close icon"></i>
        <div id="chooseLotModalHeader" class="header" style="text-align:center;">Siparişin Banfomat Excelini Oluştur</div>
        <div class="content">
            <div id="projectBanfomatGenerateExcelInfo" style="margin-bottom:0.8rem;">
                <div class="ui icon message warning">
                    <i class="warning icon"></i>
                    <div class="content">
                        <div class="header" style="margin-bottom:0.6rem;">
                            ÖNEMLİ NOT!
                        </div>
                        <p style="line-height:1.6rem;">
                            Siparişin banfomat excelini oluşturmak üzeresiniz. Lütfen siparişteki bakırlara ait kaplama bilgilerini <b>tekrar gözden geçirin ve doğruluğundan emin olun. </b>
                            Eğer veriler doğruysa bir lot numarasını seçip, onay kutusunu işaretleyerek exceli oluşturabilirsiniz. <br>

                            Kaplama bilgilerinde bir hata farkettiğiniz durumda lütfen DGT ekibiyle iletişime geçiniz.
                        </p>
                    </div>
                </div>
            </div>

            <div class="field" style="width:25%;margin:0 auto;">
                <label style="font-weight:700;padding:5px;">LOT</label>
                <div class="ui search selection dropdown fluid selectedLotSelect" id="selectedLotSelect" style="margin-top:0.4rem;">
                    <input type="hidden" name="selectedLotSelect" required>
                    <i class="dropdown icon"></i>
                    <div class="default text">Lot Seçiniz</div>
                    <div class="menu"></div>
                </div>
            </div>

        </div>

        <div class="actions footer" style="display: flex;justify-content: space-between;">
            <div class="ui cancel button">Cancel</div>
            <div id="confirmLotBtn" class="ui animated positive fade button">
                <div class="visible content">Excel İndir</div>
                <div class="hidden content">
                    <i class="download icon"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/banfomat/project-banfomat-info.js?<?=uniqid()?>"></script>
</body>
</html>
