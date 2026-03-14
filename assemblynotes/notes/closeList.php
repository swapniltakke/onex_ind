<?php
include_once '../core/index.php';
?>
<link href="/css/semantic.min.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="/css/dataTables.semanticui.min.css">
<link rel="stylesheet" type="text/css" href="/css/responsive.dataTables.min.css">


<link href="/css/main.css?13" rel="stylesheet"/>
<?php include_once '../shared/headerStyles.php' ?>
<body>

<div id="wrapper">
    <? $activePage = '/notes/closeList.php'; ?>
    <?php include_once '../shared/sidebar.php' ?>
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">
                    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i
                            class="fa fa-bars"></i> </a>
                </div>
                <ul class="nav navbar-top-links navbar-right">
                    <li>
                        <h2 style="text-align: left;">Closed Rework Items</h2>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox mb-0">
                    <div id="headersegment">
                        <div class="ibox-content text-center" style="padding-bottom:15px">
                            <div class="input-daterange d-flex" id="datepicker"
                                 style="width: unset !important;">
                                <div id="reportrange" style="">
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span></span> <i class="fa fa-caret-down"></i>
                                </div>
                                <button class="btn btn-primary ml-3" onclick="filterCloseListByDate()" type="button"><i
                                        class="fa fa-check"></i>&nbsp;Date Filter
                                </button>
                            </div>
                        </div>
                        <div id="detailsegment" style="display: none">
                            <div class="ui inverted dimmer" id="mai_spinner_page">
                                <div class="ui loader"></div>
                            </div>
                            <div class="one column center aligned padded ui grid">
                                <div class="row" style="height:100%;margin-top:2%;">
                                    <div class="column" id="controlpanel">
                                        <table id='table_open_items'
                                               class="ui celled very small compact responsive table dataTable no-footer scrollable-table">
                                            <thead class="scrollable-thead">
                                                <tr>
                                                    <th>Action</th>
                                                    <th>Status</th>
                                                    <th>Project No</th>
                                                    <th>Project Name</th>
                                                    <th>Panel No</th>
                                                    <th>Panel Type</th>
                                                    <th>Category</th>
                                                    <th>Subcategory</th>
                                                    <th>Missing Category</th>
                                                    <th>Rework Note</th>
                                                    <th>Materials</th>
                                                    <th>Created by</th>
                                                    <th>Created on</th>
                                                    <th>Updated by</th>
                                                    <th>Updated on</th>
                                                    <th>ECR</th>
                                                    <th>Idle</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include_once './../shared/footer.php' ?>
    </div>


    <!-- Mainly scripts -->
    <?php include_once './../shared/headerScripts.php' ?>
    <script src="../shared/shared.js"></script>
    <script src="./closeList.js?<?php echo rand(); ?>"></script>
    <?php include_once './../shared/headerSemanticScripts.php' ?>
</body>
</html>


