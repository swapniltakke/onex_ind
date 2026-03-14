<!DOCTYPE html>
<html>
<?php
include_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/core/index.php';
?>
<?php include_once $_SERVER["DOCUMENT_ROOT"]. '/assemblynotes/shared/headerStyles.php' ?>
<link href="../assets/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
<link href="../trendReports/trendReports.css" rel="stylesheet">
<body>

<div id="wrapper">
    <?php $activePage = 'report|trends/panelTrend'; ?>
    <?php include_once $_SERVER["DOCUMENT_ROOT"].'/assemblynotes/shared/sidebar.php' ?>
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">
                    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i
                                class="fa fa-bars"></i> </a>
                </div>
            </nav>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h5>Rework/Pano Sayı Trendleri Raporu</h5>
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

                            <div class="col-sm-2" style="display: flex;">
                                <?= initializeDatePicker() ?>
                            </div>
                            <div class="col-sm-2">
                                <button class="btn btn-sm btn-primary" id="btnFilter" data-translate="filter">Filtrele</button>
                            </div>
                        </div>
                        <div class="row">
                            <figure class="highcharts-figure" style="width:100%; height:400px;">
                                <div id="containerMv" style="width:100%; height:400px;"></div>
                                <p class="highcharts-description">

                                </p>
                            </figure>
                        </div>
                        <div class="row">
                            <figure class="highcharts-figure" style="width:100%; height:400px;">
                                <div id="containerLv" style="width:100%; height:400px;"></div>
                                <p class="highcharts-description">

                                </p>
                            </figure>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include_once $_SERVER["DOCUMENT_ROOT"].'/assemblynotes/shared/footer.php' ?>
    </div>
</div>


<!-- Mainly scripts -->
<?php include_once $_SERVER["DOCUMENT_ROOT"]. '/assemblynotes/shared/headerScripts.php' ?>
<script src="../shared/shared.js"></script>
<script src="../assets/js/plugins/highcharts/highcharts.js"></script>
<script src="../assets/js/plugins/highcharts/series-label.js"></script>
<script src="../assets/js/plugins/highcharts/exporting.js"></script>
<script src="../assets/js/plugins/highcharts/export-data.js"></script>
<script src="../assets/js/plugins/highcharts/accessibility.js"></script>
<script src="../assets/js/plugins/datapicker/bootstrap-datepicker.js"></script>
<script src="./panelTrend.js"></script>
</body>
</html>


