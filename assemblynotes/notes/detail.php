<!DOCTYPE html>
<html>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/core/index.php'; ?>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/shared/headerStyles.php' ?>
<!--<link rel="stylesheet" type="text/css" href="/css/responsive.dataTables.min.css">-->
<link href="/shared/inspia_gh_assets/css/plugins/dataTables/datatables.min.css" rel="stylesheet">
<body>
<div id="wrapper">
    <?php $activePage = '/notes/detail.php'; ?>
    <?php require_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/shared/sidebar.php' ?>
    <?php require_once './updateModal.php' ?>
    <?php require_once './insertModal.php' ?>
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
                    <div class="ibox-content">
                        <h4 data-translate="projectSearch">Project Number Search</h4>
                        <div class="form-group row">
                            <div class="input-group mx-3">
                                <select id="orderSelect" class="form-control"></select>
                            </div>
                        </div>
                        <div class="form-group row" id="panelsSection">

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" id="projectDetails" style="display: none">
            <div class="col-lg-12">
                <?php include_once './projectInfo.php' ?>
            </div>
            <div class="col-lg-12">
                <?php include_once './projectPlanInfo.php' ?>
            </div>
            <div class="col-lg-12">
                <?php include_once './projectNoteList.php' ?>
            </div>
        </div>
        <?php include_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/shared/footer.php' ?>
</body>
<?php include_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/shared/headerScripts.php'; ?>
<!-- Mainly scripts -->
<script src="/shared/inspia_gh_assets/js/plugins/dataTables/datatables.min.js"></script>
<script src="/shared/inspia_gh_assets/js/plugins/dataTables/dataTables.bootstrap4.min.js"></script>
<script src="/assemblynotes/shared/shared.js"></script>
<script src="./updateOrderModal.js?<?= time() ?>"></script>
<script src="./detailsPage.js?<?= time() ?>"></script>
<script src="./searchProject.js?<?= time() ?>"></script>
</html>
