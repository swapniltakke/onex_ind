<!DOCTYPE html>
<html>
<?php
include_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/core/index.php';

?>
<?php include_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/shared/headerStyles.php' ?>

<body>

<div id="wrapper">
    <?php $activePage = '/orderIssues/index.php'; ?>
    <?php include_once '../shared/sidebar.php' ?>
    <?php include_once './insertModal.php' ?>
    <?php include_once './confirmModal.php' ?>
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
                <div class="ibox">
                    <div class="ibox-title">
                        <h5 data-translate="filter-methods"></h5>
                        <div class="ibox-tools">
                            <button data-toggle="tooltip" data-placement="bottom" title="Start a conversation"
                                    type="button" class="btn btn-xs btn-success" onclick="openInsertModal()">
                                <i class="fa fa-plus"></i>
                            </button>
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
                            <div class="form-group col-sm-8">
                                <label for="orderSelect" data-translate="projectSearch"></label>
                                <select id="orderSelect" class="form-control"></select>
                            </div>
                            <div class="form-group col-sm-2">
                                <label for="panelSelect" data-translate="posItem"></label>
                                <select id="panelSelect" class="form-control multiple" multiple></select>
                            </div>
                            <div class="form-group col-sm-1">
                                <label for="statusFilter" data-translate="status"></label>
                                <select class="form-control rounded" style="height: 1.8rem; padding: 0px"
                                        id="statusFilter">
                                    <option value="0" selected data-translate="all"></option>
                                    <option value="1" data-translate="open">Open</option>
                                    <option value="2" data-translate="close">Close</option>
                                    <option value="3" data-translate="rework">Rework</option>
                                    <option value="4" data-translate="answered-by-om">OM answered</option>
                                    <option value="5" data-translate="not-answered-by-om">OM did not answer</option>
                                </select>
                            </div>
                            <div class="col-sm-1">
                                <div id="btnFilters" class="btn-group-sm d-flex mt-4">
                                    <button data-toggle="tooltip" data-placement="bottom" title="Filter"
                                            class="btn btn-primary btn-sm" onclick="filterIssues()"><i
                                                class="fa fa-filter"></i>
                                    </button>
                                    <button data-toggle="tooltip" data-placement="bottom" title="Fetch all list"
                                            class="btn btn-white btn-sm" onclick="getOrderIssues('all')"><i
                                                class="fa fa-list"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group has-success col-sm-12">
                                <div class="input-group">
                                    <input type="text" id="inputSearch" class="form-control"
                                           data-toggle="tooltip" data-placement="bottom"
                                           title="Use status filter to see the result of 'OM answered'"
                                           data-placeholder-translate="search-something"
                                           placeholder="Search something...">
                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="fa fa-search"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include_once './list.php' ?>
        <?php include_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/shared/footer.php' ?>
    </div>
</div>


<!-- Mainly scripts -->
<?php include_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/shared/headerScripts.php' ?>
<script src="/assemblynotes/shared/shared.js"></script>
<script src="./orderSelectComponent.js"></script>
<script src="./orderIssues.js?"<?php time(); ?>></script>
<script src="/shared/inspia_gh_assets/js/plugins/blueimp/jquery.blueimp-gallery.min.js"></script>
<script src="/shared/inspia_gh_assets/js/plugins/nestable/jquery.nestable.js"></script>
</body>
</html>
