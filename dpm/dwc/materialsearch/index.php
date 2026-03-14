<?php
$project = $_GET["project"] ?: 0;
$panel = $_GET["panel"] ?: 0;

SharedManager::saveLog('log_material_search', "Material Search Main Page Access");
?>
<html>
<head>
    <title>Project Master</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=yes"/>

    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta charset="utf-8">

    <link href="/css/semantic.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="/css/dataTables.semanticui.min.css">
    <!--<link rel="stylesheet" type="text/css" href="/css/responsive.dataTables.min.css">-->

    <link href="/plugins/pacejs/pace.css" rel="stylesheet"/>
    <link href="/css/main.css?13" rel="stylesheet"/>

    <script src="/js/jquery.min.js"></script>
    <script src="/js/semantic.min.js"></script>
    <script src="/js/jquery.dataTables.js"></script>
    <script src="/js/dataTables.semanticui.min.js"></script>
    <script src="/js/dataTables.buttons.min.js"></script>
    <script src="/js/buttons.flash.min.js"></script>
    <script src="/js/jszip.min.js"></script>
    <script src="/js/pdfmake.min.js"></script>
    <script src="/js/vfs_fonts.js"></script>
    <script src="/js/buttons.html5.min.js"></script>
    <script src="/js/buttons.print.min.js"></script>
    <script src="/js/buttons.colVis.min.js"></script>
    <script src="/js/tablesort.js"></script>
    <script src="/js/Semantic-UI-Alert.js"></script>
    <link rel="stylesheet" href="/css/jquery.toast.min.css">
</head>
<style>
    .cont {
        padding-top: 40px !important;
        padding-bottom: 41px !important;
    }

    .active.item h3 {
        transform: scale(1.2) !important;
        color: white !important;
    }

    .active.item i {
        transform: scale(1.5) !important;
        color: white !important;
    }

    .active.item {
        background-color: #00b5ad !important;
    }

    .item {
        background-color: white !important;
    }

    .item i {
        color: black !important;
    }

    .item h3 {
        color: black !important;
        /* transform: scale(1)!important;
        -webkit-transition: width 2s, height 2s, -webkit-transform 2s!important; /* Safari */
        /* transition: width 2s, height 2s, transform 2s!important; */
    }

    .align-center {
        text-align: center !important;
    }

    .ui .label {
        margin-bottom: 10px !important;
    }

    .ui .segment {
        margin-top: 10px !important;
    }

    .date-active {
        opacity: 1.0;
    }

    .date-passive {
        opacity: 0.4;
    }

    .date-block {
        display: block;
    }

    .date-none {
        display: none;
    }

    .align-center {
        text-align: center !important;
    }

    .ui.search > .results {
        position: relative !important;
        margin: auto !important;
    }

    .results {
        width: 70% !important;
    }

    .ui.tiny.button,
    .ui.tiny.buttons .button,
    .ui.tiny.buttons .or {
        font-size: 14px !important;
    }

    .pull-left {
        float: left !important;
    }

    .pull-right {
        float: right !important;
    }
</style>
<body>
    <div class="ui fixed menu" style="
            padding-top: 3px;

            color:teal;

        ">
        <div class="ui center aligned container">
            <div class="ui centered small image">
                <a href="/index.php">
                    <img class="logo" src="/images/onex.png">
                </a>
            </div>
        </div>
    </div>
    <div class="ui fluid container" style="margin-top:1.5em;">
        <div class="ui doubling stackable one column grid">
            <div class="column" style="">
                <div class="ui placeholder segment">
                    <h3 style='margin-top:3.5em' class="ui center aligned header">
                        <div class="ui icon header" data-translate="projectSearch">
                            Search by Proje No/Name => UAE, 3008484618
                        </div>
                        <div class="ui center aligned fluid container">
                            <div class="ui search">
                                <div class="ui left icon input">
                                    <input class="prompt" type="text"
                                           data-placeholder-translate="projectSearch" autofocus>
                                    <i class="search icon"></i>
                                </div>
                            </div>
                            <a class='ui big blue horizontal label' href='/layout/' target="_blank"><i
                                        class='cogs icon'></i><span data-translate="weeklyAssemblyPlan">Weekly Production Plan</span></a>
                        </div>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            $('.ui.search')
                .search({
                    apiSettings: {
                        url: '/materialsearch/api/search.php?project={query}'
                    },
                    fields: {
                        results: 'items',
                        title: 'name',
                        url: 'html_url'
                    },
                    minCharacters: 3
                });

        });
    </script>
</body>
<html>