<!DOCTYPE html>
<html>
<?php
include_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/core/index.php';

?>
<?php include_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/shared/headerStyles.php' ?>

<body>

<div id="wrapper">
    <?php $activePage = '/orderIssues/listTable.php'; ?>
    <?php include_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/shared/sidebar.php' ?>
    <div id="page-wrapper" class="gray-bg">
        <?php include_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/shared/sidebarHamburger.php' ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5 data-translate="filter-methods">Filtreleme İşlemleri</h5>
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
                            <div class="form-group col-sm-10">
                                <label for="orderSelect" data-translate="projectNo">Sipariş</label>
                                <select id="orderSelect" class="form-control"></select>
                            </div>
                            <div class="col-sm-2">
                                <div id="btnFilters" class="btn-group-sm d-flex mt-4">
                                    <button data-toggle="tooltip" data-placement="bottom" title="Filtrele"
                                            class="btn btn-primary btn-sm" onclick="filterIssues()"><i
                                                class="fa fa-filter"></i>
                                    </button>
                                    <button data-toggle="tooltip" data-placement="bottom"
                                            title="Son bir senenin listesini getir"
                                            class="btn btn-white btn-sm" onclick="clearFilterIssues()"><i
                                                class="fa fa-list"></i>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <h5 data-translate="questions-of-project">Proje Sorunları Konuşma Listesi</h5>
                    </div>
                    <div class="ibox-content">
                        <table id="dtOrderIssues" class="table">
                            <thead>
                            <tr>
                                <th data-translate="question-answer">Question/Answer</th>
                                <th data-translate="projectNo">Project No</th>
                                <th data-translate="posItem">Panel</th>
                                <th data-translate="created-by">Created by</th>
                                <th data-translate="note">Note</th>
                                <th data-translate="created-at">Created On</th>
                                <th data-translate="status">Status</th>
                                <th data-translate="error-code">Error code</th>
                                <th data-translate="reference">Reference</th>
                                <th data-translate="answered-by-om-time">OM Answered on</th>
                                <th data-translate="answered-by-om-time-diff">OM answer time (Days)</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
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
<script src="orderSelectComponent.js"></script>
<script src="./listTable.js"></script>
</body>
</html>
