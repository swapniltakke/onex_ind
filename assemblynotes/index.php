<!DOCTYPE html>
<html>
<?php
include_once './core/index.php';
?>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>OneX | Scan Barcode</title>

    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/shared/inspia_gh_assets/font-awesome/css/font-awesome.css" rel="stylesheet">

    <link href="/shared/inspia_gh_assets/css/plugins/dataTables/datatables.min.css" rel="stylesheet">
    <link href="/shared/inspia_gh_assets/js/plugins/select2/css/select2.min.css" rel="stylesheet"/>
    <link href="/shared/inspia_gh_assets/css/animate.css" rel="stylesheet">
    <link href="/shared/inspia_gh_assets/css/style.css" rel="stylesheet">
    <link href="/shared/inspia_gh_assets/css/plugins/toastr/toastr.min.css" rel="stylesheet">
    <link href="/shared/inspia_gh_assets/css/plugins/blueimp/css/blueimp-gallery.min.css" rel="stylesheet">
    <link href="/shared/inspia_gh_assets/css/plugins/switchery/switchery.css" rel="stylesheet">
    <script src="/shared/inspia_gh_assets/css/plugins/iCheck/custom.css"></script>
    <link href="/shared/inspia_gh_assets/css/plugins/sweetalert/sweetalert.css" rel="stylesheet">
    <link href="./css/customs.css" rel="stylesheet">
</head>
<link rel="stylesheet" type="text/css" href="barcodereader_style.css">
<body>

<div id="wrapper">
    <?php $activePage = 'mainPage' ?>
    <?php include_once './shared/sidebar.php' ?>
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
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="center aligned container">
                        <div class="error-messages" style="display:none;"></div>
                    </div>
                    <div class="center aligned container" style="width:35%;z-index: 999;margin-bottom: 30px;">
                        <h1 style="color: red;float:left"><i onclick="reScanner()"
                                                             style="cursor:pointer;margin:10px"
                                                             class="sync alternate icon"></i></h1>
                    </div>
                    <div id="mainContent" class="ui center aligned container"
                         style="margin-top: 30px;width:35%;cursor:pointer">
                        <div style="margin-top:15px;background-color: #BED2C9;padding:15px; display: flex;flex-direction: column;max-width: 350px;"
                             id="imageDiv">
                            <h2 style="text-align: center; font-weight: bold" data-translate="panel-barcode-read"></h2>
                            <img class="huge rounded image" src="./assets/images/readbarcode.png">
                            <div class="diode">
                                <div class="laser"></div>
                            </div>
                        </div>
                    </div>
                    <div id="readData" class="ui center aligned container" style="width: 100%;margin-top:10px">
                    </div>
                </div>
            </div>
        </div>
        <?php include_once $_SERVER["DOCUMENT_ROOT"] . '/assemblynotes/shared/footer.php' ?>
    </div>
</div>


<!-- Mainly scripts -->
<?php include_once __DIR__ . '/shared/headerScripts.php' ?>
<script src="./shared/shared.js"></script>
<script src="barcodereader.js"></script>
<script type="text/javascript">
    saveLog("log_assembly_notes", "Assemblynotes main page access")

</script>
</body>
</html>

