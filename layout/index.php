<?php
SharedManager::checkAuthToModule(2);
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$DEFAULT_LINE = $_ENV["DEFAULT_LINE"];
$THIS_WEEK = date('D') == 'Sun' ? date('Y') . (int)date('W') + 1 : date('Y') . date('W');
$WEEK = (!$_GET["week"]) ? $THIS_WEEK : $_GET["week"];
$CALENDER_WEEK = isset($WEEK) ? substr($WEEK, 0, 4) . "-W" . substr($WEEK, 4, 6) : date('Y') . '-W' . date('W');
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Weekly Plan</title>

        <!-- Semantic UI -->
        <link href="/css/semantic.min.css" rel="stylesheet"/>

        <!-- dataTablesfixedColumns&Header style -->
        <link href="/shared/inspia_gh_assets/css/fixedColumns.bootstrap4.min.css" rel="stylesheet">
        <link href="/shared/inspia_gh_assets/css/fixedColumns.dataTables.min.css" rel="stylesheet">
        <link href="/shared/inspia_gh_assets/css/fixedHeader.dataTables.min.css" rel="stylesheet">
        <link href="/shared/inspia_gh_assets/css/jquery.dataTables.min.css" rel="stylesheet">

        <link href="/css/bootstrap.min.css" rel="stylesheet">
        <link href="/shared/inspia_gh_assets/font-awesome/css/font-awesome.css" rel="stylesheet">
        <link href="/shared/inspia_gh_assets/font-awesome/css/fontawesome-custom.min.css" rel="stylesheet">


        <!--  iOS Switcher -->
        <link href="/shared/inspia_gh_assets/css/plugins/switchery/switchery.css" rel="stylesheet">

        <!-- DataTable -->
        <link href="/shared/inspia_gh_assets/css/plugins/dataTables/datatables.min.css" rel="stylesheet">

        <!-- Toastr style -->
        <link href="/shared/inspia_gh_assets/css/plugins/toastr/toastr.min.css" rel="stylesheet">

        <!-- c3 Charts -->
        <link href="/shared/inspia_gh_assets/css/plugins/c3/c3.min.css" rel="stylesheet">

        <!-- Ladda style -->
        <link href="/shared/inspia_gh_assets/css/plugins/ladda/ladda-themeless.min.css" rel="stylesheet">

        <!-- dataTablesbootstrap4 style -->
        <link href="/shared/inspia_gh_assets/css/dataTables.bootstrap4.min.css" rel="stylesheet">

        <!-- Sweet Alert -->
        <link href="/shared/inspia_gh_assets/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">

        <!-- Rocket Loader -->
        <link href="/shared/inspia_gh_assets/css/rocketloader.css" rel="stylesheet">

        <!-- Date Picker -->
        <link href="/shared/inspia_gh_assets/css/plugins/datapicker/datepicker3.css" rel="stylesheet">
        <link href="/shared/inspia_gh_assets/css/plugins/select2/select2.min.css" rel="stylesheet">
        <link href="/shared/inspia_gh_assets/css/plugins/chosen/bootstrap-chosen.css" rel="stylesheet">
        <link href="/shared/inspia_gh_assets/css/plugins/touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet">

        <!-- iCheck -->
        <link href="/shared/inspia_gh_assets/css/plugins/iCheck/custom.css" rel="stylesheet">

        <!-- Color Picker -->
        <link href="/shared/inspia_gh_assets/css/plugins/colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet">

        <!-- Cropper -->
        <link href="/shared/inspia_gh_assets/css/plugins/cropper/cropper.min.css" rel="stylesheet">

        <!-- Chosen -->
        <link href="/shared/inspia_gh_assets/css/plugins/chosen/bootstrap-chosen.css" rel="stylesheet">


        <!-- Jasny -->
        <link href="/shared/inspia_gh_assets/css/plugins/jasny/jasny-bootstrap.min.css" rel="stylesheet">

        <!-- NouSlider -->
        <link href="/shared/inspia_gh_assets/css/plugins/nouslider/jquery.nouislider.css" rel="stylesheet">

        <!-- Range Slider -->
        <link href="/shared/inspia_gh_assets/css/plugins/ionRangeSlider/ion.rangeSlider.css" rel="stylesheet">
        <link href="/shared/inspia_gh_assets/css/plugins/ionRangeSlider/ion.rangeSlider.skinFlat.css" rel="stylesheet">

        <!-- Awesome Checkbox -->
        <link href="/shared/inspia_gh_assets/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css" rel="stylesheet">

        <!-- Clock Picker -->
        <link href="/shared/inspia_gh_assets/css/plugins/clockpicker/clockpicker.css" rel="stylesheet">

        <!-- DualList Box -->
        <link href="/shared/inspia_gh_assets/css/plugins/dualListbox/bootstrap-duallistbox.min.css" rel="stylesheet">

        <!-- jqGrid -->
        <link href="/shared/inspia_gh_assets/css/plugins/jQueryUI/jquery-ui-1.10.4.custom.min.css" rel="stylesheet">
        <link href="/shared/inspia_gh_assets/css/plugins/jqGrid/ui.jqgrid.css" rel="stylesheet">

        <!-- Date Range Picker -->
        <link rel="stylesheet" type="text/css" href="/shared/inspia_gh_assets/css/daterangepicker.css"/>

        <link href="/shared/inspia_gh_assets/css/animate.css" rel="stylesheet">
        <link href="/shared/inspia_gh_assets/css/style.css" rel="stylesheet">

        <style>
            .ibox-title {
                text-align: center !important;
                padding-right: 15px !important;
            }

            .ibox-content {
                text-align: center !important;
            }

            .tags {
                display: inline;
                position: relative;
            }

            .tags:hover:after {
                border-radius: 5px;
                bottom: -15px;
                color: #fff;
                content: attr(gloss);
                left: 20%;
                padding: 5px 15px;
                position: absolute;
                z-index: 98;
                min-width: 14rem;
                text-align: left;
                width: auto;
                font-size: 16px;
                left: -233px;
            }

            .tags-success:hover:after {
                background-color: #1ab394;
            }

            .tags-error:hover:after {
                background-color: #a94442;
                bottom: -43px;
                left: -244px;
            }

            .tags-waiting:hover:after {
                background-color: #1c84c6;
            }

            .tags-warning:hover:after {
                background-color: #ffc107;
            }

            div.dom_wrapper {
                position: sticky;
                /* Fix to the top */
                top: 0;
                padding: 5px;
                background: rgba(255, 255, 255, 1);
                /* hide the scrolling table */
            }

            .display-none- {
                display: none;
            }

            .display-none {
                display: none;
            }

            .visually-hidden {
                -webkit-animation: fadeInFromNone 1s ease-out;
                -moz-animation: fadeInFromNone 1s ease-out;
                -o-animation: fadeInFromNone 1s ease-out;
                animation: fadeInFromNone 1s ease-out;
            }
            .wideColumn{
                width: 100% !important;
                margin: 0px !important;
                padding: 0px 0px 1px 0px !important;
            }
            #main_table_wrapper .ui-jqgrid-labels {
                height: 220px

            }

            #main_table_wrapper .ui-jqgrid .ui-jqgrid-htable th div {
                overflow: visible;

            }

            #main_table_wrapper .ui-jqgrid-sortable {
                white-space: nowrap;
                text-align: left;
                -webkit-transform: rotate(-90deg);
                -moz-transform: rotate(-90deg);
                filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
            }


            @-webkit-keyframes fadeInFromNone {
                0% {
                    display: block;
                    opacity: 1;
                }

                100% {
                    display: none;
                    opacity: 0;
                }
            }

            @-moz-keyframes fadeInFromNone {
                0% {
                    display: block;
                    opacity: 1;
                }

                100% {
                    display: none;
                    opacity: 0;
                }
            }

            @-o-keyframes fadeInFromNone {
                0% {
                    display: block;
                    opacity: 1;
                }

                100% {
                    display: none;
                    opacity: 0;
                }
            }

            @keyframes fadeInFromNone {
                0% {
                    display: block;
                    opacity: 1;
                }

                100% {
                    display: none;
                    opacity: 0;
                }
            }

            .display-none-mto {
                display: none;
            }

            .display-none-LVBox {
                display: none;
            }

            .display-none-Com {
                display: none;
            }

            div.navbar-header:hover .navbar-default {
                transition: all 3s cubic-bezier(.3, 0, 0, 1.3);
                transform: translateY(-600%);
                background: red
            }

            .bigger {
                width: 28%;
            }

            .smaller {
                width: 12%;
            }

            .ui.segment,
            .ui.segments .segment {
                font-size: 13px;
            }

            .tabs-container .panel-body {
                padding: 15px;
            }

            .panels-col {
                padding-right: 3px;
                padding-left: 3px;
            }

            .column:hover {
                opacity: 1 !important;
                /* css standard */
                filter: alpha(opacity=100);
                /* internet explorer */
            }

            .icon {
                margin: 0.5em;
            }

            .hoverable:hover {
                cursor: pointer;
                transform: scale(1.1) perspective(0px);
                transition: transform .5s ease;
                -webkit-backface-visibility: hidden;
                z-index: 2;
                box-shadow: 1px 1px 2px black, 0 0 25px #25ccaa, 0 0 5px #119440;
                border-radius: 10px;
            }

            body {
                background: rgba(34, 36, 38, .1) !important;
            }
        </style>
        <link rel="stylesheet" href="/css/tailwind.min.css">
        <script src="/shared/shared.js"></script>
        <!--<link rel="stylesheet" type="text/css" href="/DGTLibrary/Notification/css/jquery.toast.min.css">-->
    </head>
    <body class="canvas-menu pace-done antialiased bg-gray-200 font-sans">
        <div class="pace  pace-inactive">
            <div class="pace-progress" data-progress-text="100%" data-progress="99"
                 style="transform: translate3d(100%, 0px, 0px);">
                <div class="pace-progress-inner"></div>
            </div>
            <div class="pace-activity"></div>
        </div>

        <div id="page-wrapper" class="gray-bg">
            <div class="row border-bottom">
                <nav id="header" class="navbar navbar-static-top" role="navigation" style="height: 4.5rem;">
                    <div class="ui image m-auto">
                        <a href="/index.php">
                            <img style="max-height:50px" class="logo" src="/images/onex.png">
                        </a>
                    </div>
                    <ul class="nav navbar-top-links navbar-right">
                        <li style="float: right !important;">
                            <div id="languageSelection" style="display: flex;">
                                <!-- LANGUAGE ICONS -->
                            </div>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="tabs-container">
                <ul id="lineTabs" class="nav nav-tabs" role="tablist" style="min-height: 41px;">

                </ul>
                <div class="tab-content">
                    <?php require_once('./components/line-tab-container.php') ?>

                </div>
            </div>
        </div>

        <?php require_once('./components/shared/footer.php') ?>
    </body>
</html>