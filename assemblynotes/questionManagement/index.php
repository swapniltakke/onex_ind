<!DOCTYPE html>
<html>
<?php include_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/core/index.php'; ?>
<?php include_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/shared/headerStyles.php' ?>
<style>

    .select2-selection__rendered {
        word-wrap: break-word !important;
        text-overflow: inherit !important;
        white-space: normal !important;
    }
</style>
<body>

<div id="wrapper">
    <?php $activePage = 'orderIssues/queryManagements/index.php'; ?>
    <?php include_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/shared/sidebar.php' ?>
    <div id="page-wrapper" class="gray-bg">
        <?php include_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/shared/sidebarHamburger.php' ?>
        <?php
        if ((int)SharedManager::getUser()["GroupID"] == 2) {
            echo '<div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5 data-translate="question-rule-write">Soru Kural Yazımı</h5>
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
                            <div class="form-group col-sm-6">
                                <label for="orderSelectCluster" data-translate="order-cluster">Sipariş Kümesi</label>
                                <select id="orderSelectCluster" class="form-control multiple" multiple></select>
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="orderSelectMain" data-translate="order-question-ask">Soru Sorulacak Sipariş Numarası</label>
                                <select id="orderSelectMain" class="form-control"></select>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-sm-11">
                            </div>
                            <div class="col-sm-1">
                                <button id="btnSaveOrderRule" onclick="saveOrderRule(this)"
                                        class="btn btn-sm btn-success" data-translate="save">Save
                                </button>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>';
        }
        ?>

        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5>Sipariş Kural Listesi</h5>
                    </div>
                    <div class="ibox-content" style="width: 100%;margin:auto;overflow-x: auto;">
                        <table id="dtOrderRules" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th data-translate="cluster">Küme</th>
                                    <th data-translate="projectNo">Sipariş No</th>
                                    <th data-translate="created-by">Oluşturan</th>
                                    <th data-translate="created-at">Kayıt Tarihi</th>
                                    <th data-translate="action">İşlem</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php include_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/shared/footer.php' ?>
    </div>
</div>


<!-- Mainly scripts -->
<?php include_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/shared/headerScripts.php' ?>
<script src="../shared/shared.js"></script>
<script src="./questionManagement.js"></script>
</body>
</html>
