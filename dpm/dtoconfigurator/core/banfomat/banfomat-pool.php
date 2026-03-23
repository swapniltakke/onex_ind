<?php
include_once $_SERVER["DOCUMENT_ROOT"] . '/checklogin.php';
include_once '../../api/models/Journals.php';

SharedManager::checkAuthToModule(35);
Journals::saveJournal('Accessing Banfomat Pool Page', PAGE_BANFOMAT, BANFOMAT_POOL, ACTION_VIEWED, null, 'Banfomat Pool');
SharedManager::saveLog('log_dtoconfigurator', 'Accessing Banfomat Page');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banfomat Havuzu | DTO Configurator</title>
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
    <a href="/dpm/dtoconfigurator/core/tkform/index.php" class="item active">
        <i class="clipboard outline icon"></i> TK Forms
    </a>
    <a href="/dpm/dtoconfigurator/core/orders-plan/index.php" id="ordersPlanItem" class="item active" style="display:none;">
        <i class="calendar alternate icon"></i> Orders Plan
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
                    <a href="/dpm/dtoconfigurator/core/banfomat/banfomat-pool.php" target="_blank" class="item info active"><i class="chart area icon"></i>Banfomat Havuzu</a>
                    <a href="/dpm/dtoconfigurator/core/banfomat/banfomat-history.php" target="_blank" class="item info"><i class="clock icon"></i>Banfomat Geçmişi</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Main Content Area -->
<div id="banfomatPoolPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>

    <div class="banfomatPoolContainer ui container-fluid" style="padding-right:5%; margin-top:1.5rem;">
        <div class="ui grid">
            <div class="twelve wide column">
                <h2 class="ui header">
                    <i class="chart area icon"></i>
                    <div class="content">
                        Banfomat Havuzu
                        <div class="sub header">Aşağıdaki tablo Banfomat Excel dosyası için gerekli kaplama bilgilerini içeren parçaların listesini içerir.</div>
                    </div>
                </h2>
            </div>

            <div class="four wide column">
                <div id="addMaterialToBanfomatPoolBtn" class="ui blue large circular icon compact button" style="float:right;margin-top: 3px;">
                    <i class="plus icon" style="margin-right:0.3rem!important;"></i>
                    Havuza Malzeme Ekle
                </div>
            </div>
        </div>

        <div class="ui form" style="margin-bottom: 1rem; margin-top:1.5rem;">
            <div class="three fields">
                <!-- Metal Filter -->
                <div class="field">
                    <label>DTO Metali</label>
                    <select id="metalFilterDropdown" class="ui fluid dropdown" multiple>
                        <option value="">Select Metals</option>
                    </select>
                </div>

                <!-- Kaplama Filter -->
                <div class="field">
                    <label>Kaplama</label>
                    <select id="coatedTypeFilterDropdown" class="ui fluid dropdown" multiple>
                        <option value="">Select Kaplama</option>
                    </select>
                </div>

                <!-- Kap. Bölge Filter -->
                <div class="field">
                    <label>Kaplanacak Bölge</label>
                    <select id="coatedPartFilterDropdown" class="ui fluid dropdown" multiple>
                        <option value="">Select Kap. Bölge</option>
                    </select>
                </div>

                <!-- Üretim Yeri Filter -->
                <div class="field">
                    <label>Üretim Yeri</label>
                    <select id="productionLocationFilterDropdown" class="ui fluid dropdown" multiple>
                        <option value="">Select Üretim Yeri</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Banfomat Pool List Table -->
        <div id="banfomatPoolListTableContainer" style="display:none;margin-top:2.5rem;">
            <h4 class="ui block top attached header">
                <i class="calculator icon"></i>
                <div class="content">
                    Banfomat Havuzu
                </div>
            </h4>

            <div class="ui attached segment">
                <table id="banfomatPoolDataTable" class="ui striped hover compact celled table stackable padded">
                    <thead>
                        <tr>
                            <th>Malzeme</th>
                            <th>Açıklama</th>
                            <th>DTO</th>
                            <th>Yüzey Alan</th>
                            <th>Kaplama</th>
                            <th>Kap. Bölge</th>
                            <th>Detay</th>
                            <th>Üretim Yeri</th>
                            <th>Resim</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="addMaterialToBanfomatPoolModal" class="ui modal center aligned">
    <i class="close icon"></i>
    <div id="addMaterialToBanfomatPoolModalHeader" class="header" style="text-align:center;">Parça Ekleme | Banfomat Havuzu</div>
    <div class="content">
        <form id="addMaterialToBanfomatPoolForm" class="ui form">
            <div id="formRequiredFieldsError" class="ui icon message small compact negative" style="display:none;">
                <i class="warning icon"></i>
                <div class="content">
                    <div class="header" style="margin-bottom:0.6rem;display:none;">Required Fields</div>
                    Please fill all required fields.
                </div>
            </div>

            <div class="field" style="margin-bottom:1.5rem;">
                <label>Malzeme Numarası<sup style="color:red;font-size:1rem;">*</sup></label>
                <div class="ui search selection dropdown fluid select-material-number" id="select-material-number">
                    <input type="hidden" name="select-material-number" required>
                    <i class="dropdown icon"></i>
                    <div class="default text">Malzeme Seçiniz</div>
                    <div class="menu"></div>
                </div>
            </div>

            <div class="fields" style="margin-bottom:1.5rem;">
                <div class="eight wide field">
                    <label>DTO Metali<sup style="color:red;font-size:1rem;">*</sup></label>
                    <div id="select-metal" class="ui fluid multiple search selection dropdown">
                        <input type="hidden" name="country">
                        <i class="dropdown icon"></i>
                        <div class="default text">DTO Metali Seçiniz<sup style="color:red;font-size:1rem;">*</sup></div>
                        <div class="menu">
                            <div class="item" data-value="Gümüş">Gümüş</div>
                            <div class="item" data-value="Kalay">Kalay</div>
                            <div class="item" data-value="Nikel">Nikel</div>
                        </div>
                    </div>
                </div>

                <div class="eight wide field">
                    <label>Yüzey Alan (m&sup2)</label>
                    <input type="number" id="input-surface-area" name="input-surface-area" step="0.01" placeholder="Yüzey Alan Giriniz (e.g., 0.180108)" required>
                </div>
            </div>

            <div class="fields" style="margin-bottom:1.5rem;">
                <div class="eight wide field">
                    <label>Kaplanacak Bölge</label>
                    <select id="select-coated-part" name="select-coated-part" class="ui fluid search dropdown" required>
                        <option value="">Kaplanacak Bölge Seçiniz</option>
                        <option value="Komple">Komple</option>
                        <option value="Kısmi">Kısmi</option>
                    </select>
                </div>
                <div class="eight wide field">
                    <label>Kaplama Türü</label>
                    <select id="select-coated-type" name="select-coated-type" class="ui fluid search dropdown" required>
                        <option value="">Kaplama Türü Seçiniz</option>
                        <option value="Sn8+12">Sn8+12</option>
                        <option value="Sn8+12 & Ag">Sn8+12 & Ag</option>
                        <option value="Ag">Ag</option>
                        <option value="Ag25+6">Ag25+6</option>
                        <option value="Ag3+2">Ag3+2</option>
                        <option value="Ni5+25">Ni5+25</option>
                    </select>
                </div>
            </div>

            <div class="field" style="margin-bottom:1.5rem;">
                <label>Detay</label>
                <textarea id="input-details" name="input-details" rows="2" required></textarea>
            </div>

            <div class="eight wide field" style="margin:0 auto;">
                <label>Teknik Resim</label>
                <div id="addTechnicalImageInput" class="ui field action input">
                    <input type="text" id="image-input-text" placeholder="Teknik Resim Seçiniz" style="cursor:pointer;" readonly>
                    <input type="file" id="image-input-file" name="image" accept=".jpg, .jpeg, .png, .gif">
                    <div id="choose-image-input-button" class="ui icon button">
                        <i class="attach icon"></i>
                    </div>
                    <div id="remove-image-button" class="ui red icon button" style="display: none;">
                        <i class="trash icon"></i>
                    </div>

                </div>
            </div>

            <div class="field" style="display: flex;justify-content: center;margin-top: 1.2rem;">
                <div class="ui checkbox">
                    <input type="checkbox" id="checkbox-wont-be-coated" name="checkbox-wont-be-coated">
                    <label>Banfomat Dışı</label>
                </div>
            </div>
        </form>
    </div>

    <div class="actions footer" style="display: flex;justify-content: space-between;">
        <div class="ui cancel button">İptal</div>
        <div id="saveMaterialToBanfomatPoolButton" class="ui animated positive fade button">
            <div class="visible content">Kaydet</div>
            <div class="hidden content">
                <i class="save icon"></i>
            </div>
        </div>
    </div>
</div>


<div id="editBanfomatPoolModal" class="ui small modal center aligned">
    <i class="close icon"></i>
    <div id="editBanfomatPoolModalHeader" class="header" style="text-align:center;">Veri Güncelleme | Banfomat Havuzu </div>
    <div class="content">
        <form id="editBanfomatPoolForm" class="ui form">
            <div id="editFormRequiredFieldsError" class="ui icon message small compact negative" style="display:none;">
                <i class="warning icon"></i>
                <div class="content">
                    <div class="header" style="margin-bottom:0.6rem;display:none;">Required Fields</div>
                    Please fill all required fields.
                </div>
            </div>

            <input type="hidden" id="edit-banfomat-id" name="edit-banfomat-id">

            <div class="field">
                <label>Malzeme Numarası<sup style="color:red;font-size:1rem;">*</sup></label>
                <div class="ui search selection dropdown fluid select-material-number" id="edit-select-material-number">
                    <input type="hidden" name="edit-select-material-number" required>
                    <i class="dropdown icon"></i>
                    <div class="default text">Malzeme Seçiniz</div>
                    <div class="menu"></div>
                </div>
            </div>

            <div class="fields">
                <div class="eight wide field">
                    <label>DTO Metali<sup style="color:red;font-size:1rem;">*</sup></label>
                    <select id="edit-select-metal" name="edit-select-metal" class="ui fluid search dropdown" required>
                        <option value="">DTO Metali Seçiniz</option>
                        <option value="Gümüş">Gümüş</option>
                        <option value="Kalay">Kalay</option>
                        <option value="Nikel">Nikel</option>
                    </select>
                </div>

                <div class="eight wide field">
                    <label>Yüzey Alan (m&sup2)</label>
                    <input type="number" id="edit-input-surface-area" name="edit-input-surface-area" step="0.01">
                </div>
            </div>

            <div class="fields">
                <div class="eight wide field">
                    <label>Kaplanacak Bölge<sup style="color:red;font-size:1rem;">*</sup></label>
                    <select id="edit-select-coated-part" name="edit-select-coated-part" class="ui fluid search dropdown" required>
                        <option value="">Kaplanacak Bölge Seçiniz</option>
                        <option value="Komple">Komple</option>
                        <option value="Kısmi">Kısmi</option>
                    </select>
                </div>
                <div class="eight wide field">
                    <label>Kaplama Türü<sup style="color:red;font-size:1rem;">*</sup></label>
                    <select id="edit-select-coated-type" name="edit-select-coated-type" class="ui fluid search dropdown" required>
                        <option value="">Kaplama Türü Seçiniz</option>
                        <option value="Sn8+12">Sn8+12</option>
                        <option value="Sn8+12 & Ag">Sn8+12 & Ag</option>
                        <option value="Ag">Ag</option>
                        <option value="Ag25+6">Ag25+6</option>
                        <option value="Ag25+6 & Ni5+25">Ag25+6 & Ni5+25</option>
                        <option value="Ag3+2">Ag3+2</option>
                        <option value="Ni5+25">Ni5+25</option>
                    </select>
                </div>
            </div>

            <div class="field">
                <label>Detay</label>
                <textarea id="edit-input-details" name="edit-input-details" rows="2"></textarea>
            </div>

            <div class="eight wide field" style="margin:0 auto;">
                <label>Teknik Resim</label>
                <div id="editTechnicalImageInput" class="ui field action input">
                    <input type="text" id="edit-image-input" placeholder="Teknik Resim Seçiniz" style="cursor:pointer;" readonly>
                    <input type="file" id="edit-image-file" name="edit-image" accept=".jpg, .jpeg, .png, .gif">
                    <div id="edit-choose-image-input-button" class="ui icon button">
                        <i class="attach icon"></i>
                    </div>
                    <div id="remove-edit-image-button" class="ui red icon button" style="display: none;">
                        <i class="trash icon"></i>
                    </div>
                </div>
            </div>

            <div id="technicalImage" class="sixteen wide field" style="display:flex;margin-top:0.5rem;"></div>
            <input type="hidden" id="edit-existing-image-name" name="edit-existing-image-name">
            <input type="hidden" id="edit-image-changed" name="edit-image-changed" value="0">

            <div class="field" style="display: flex;justify-content: center;margin-top: 1.2rem;">
                <div class="ui checkbox">
                    <input type="checkbox" id="edit-checkbox-wont-be-coated" name="edit-checkbox-wont-be-coated">
                    <label>Banfomat Dışı</label>
                </div>
            </div>
        </form>
    </div>

    <div class="actions footer" style="display: flex;justify-content: space-between;">
        <button id="deleteBanfomatPoolRowButton" class="ui red button">Sil</button>
        <div>
            <div id="updateBanfomatPoolRowButton" class="ui animated positive fade button">
                <div class="visible content">Güncelle</div>
                <div class="hidden content">
                    <i class="save icon"></i>
                </div>
            </div>
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
<script src="/dpm/dtoconfigurator/assets/js/banfomat/banfomat-pool.js?<?=uniqid()?>"></script>
</body>
</html>
