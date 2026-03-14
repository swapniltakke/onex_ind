<!DOCTYPE html>
<html>
<?php
include_once $_SERVER["DOCUMENT_ROOT"]. "/assemblynotes/core/index.php";
?>
<?php include_once $_SERVER["DOCUMENT_ROOT"]. '/assemblynotes/shared/headerStyles.php' ?>
<link href="/assemblynotes/assets/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="./report.css" rel="stylesheet">
<body>

<div id="wrapper">
    <?php $activePage = 'report|trends/index.php'; ?>
    <?php include_once $_SERVER["DOCUMENT_ROOT"]. "/assemblynotes/shared/sidebar.php" ?>
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">
                    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i
                            class="fa fa-bars"></i> </a>
                </div>
                <ul class="nav navbar-top-links navbar-right">
                    <li style="float: right !important;">
                        <div style="display: flex;">
                            <?php include_once $_SERVER["DOCUMENT_ROOT"] . "/shared/language/language-button-content.php"; ?>
                        </div>
                    </li>
                </ul>
            </nav>
        </div>
        <?php include_once $_SERVER["DOCUMENT_ROOT"]. '/assemblynotes/noteWidgets/brief.php' ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h5 data-translate="rework-reasons-report">Rework Nedenleri Raporu</h5>
                        <div class="ibox-tools">
                            <a class="collapse-link">
                                <i class="fa fa-chevron-up"></i>
                            </a>
                            <a class="close-link">
                                <i class="fa fa-times"></i>
                            </a>
                        </div>
                    </div>
                    <div class="ibox-content">

                        <div class="row">
                            <div class="col-sm-1" style="display: flex;">
                                <div class="checkbox checkbox-success mr-2">
                                    <input type="checkbox" id="chkLV" value="lv" checked>
                                    <label for="chkLV"> LV </label>
                                </div>
                                <div class="checkbox checkbox-primary mr-2">
                                    <input type="checkbox" id="chkMV" value="mv" checked>
                                    <label for="chkMV"> MV </label>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <?= initializeDatePicker() ?>
                            </div>
                            <div class="col-sm-5" style="display: flex;">
                                <div class="form-group col-sm-4">
                                    <select id="categorySelect" data-placeholder="Kategori"
                                            class="form-control form-select">
                                        <option value="All" data-translate="all-categories">Tüm Kategoriler</option>
                                        <option value="Ana Montaj Hatları" data-translate="main-assembly-lines">Ana Montaj Hatları</option>
                                        <option value="Destek Hatları" data-translate="support-lines">Destek Hatları</option>
                                        <option value="Acil Müdahele" data-translate="urgent-action">Acil Müdahele</option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <select id="mainCategorySelect" data-placeholder="Ana Kategoriler"
                                            class="form-control form-select">
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <select style="width: 100%;" id="subCategorySelect"
                                            data-placeholder="Alt Kategoriler"
                                            data-placeholder-translate="sub-category"
                                            multiple="multiple"
                                            class="form-control form-select multiple">
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <select style="width: 100%;" id="openAndCloseSelect"
                                            class="form-control form-select">
                                        <option value="open" data-translate="open-notes">Açık Notlar</option>
                                        <option value="openAndClose" data-translate="open-close-notes">Açık ve Kapalı Notlar</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-1">
                                <button class="btn btn-sm btn-primary" data-translate="filter" id="btnFilter">Filtrele</button>
                            </div>
                        </div>
                        <div class="row">
                            <figure class="highcharts-figure" style="width:100%; height:400px;">
                                <div id="containerMain" style="width:100%; height:400px;"></div>
                                <p class="highcharts-description">

                                </p>
                            </figure>
                        </div>
                        <div class="row">
                            <figure class="highcharts-figure" style="width:100%; height:400px;">
                                <div id="container" style="width:100%; height:400px;"></div>
                                <p class="highcharts-description">

                                </p>
                            </figure>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title blue-bg">
                        <h5 data-translate="mai-header-2">Rework Nedenleri Ana Kategori Pivot Gösterimi</h5>
                        <div class="ibox-tools">
                            <a class="collapse-link">
                                <i class="fa fa-chevron-up text-white"></i>
                            </a>
                            <a class="close-link">
                                <i class="fa fa-times text-white"></i>
                            </a>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="row">
                            <table id="dt_main_category_pivot" class="table">
                                <thead>
                                <tr>
                                    <th data-translate="main-category">Ana Kategori</th>
                                    <th data-translate="qty">Adet</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title bg-warning">
                        <h5 data-translate="mai-header-1">Rework Nedenleri Alt Kategori Pivot Gösterimi</h5>
                        <div class="ibox-tools">
                            <a class="collapse-link">
                                <i class="fa fa-chevron-up text-white"></i>
                            </a>
                            <a class="close-link">
                                <i class="fa fa-times text-white"></i>
                            </a>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <div class="row">
                            <table id="dt_sub_category_pivot" class="table">
                                <thead>
                                <tr>
                                    <th data-translate="sub-category">Alt Kategori</th>
                                    <th data-translate="qty">Adet</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <?php include_once $_SERVER["DOCUMENT_ROOT"].'/shared/footer.php' ?>
    </div>
</div>


<!-- Mainly scripts -->
<?php include_once $_SERVER["DOCUMENT_ROOT"]. '/assemblynotes/shared/headerScripts.php' ?>
<script src="../shared/shared.js"></script>
<script src="../assets/js/plugins/highcharts/highcharts.js"></script>
<script src="../assets/js/plugins/highcharts/pareto.js"></script>
<script src="../assets/js/plugins/highcharts/exporting.js"></script>
<script src="../assets/js/plugins/highcharts/export-data.js"></script>
<script src="../assets/js/plugins/highcharts/accessibility.js"></script>
<script src="../assets/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="./report.js"></script>
<script src="../noteWidgets/brief.js"></script>
<script src="./main_sub_categories_select.js"></script>
</body>
</html>


