<?php SharedManager::checkAuthToModule(1); ?>
<html>
<head>
    <title data-translate="titleTag-masterPlanning">OneX - Master Planning</title>
    <meta name="viewport" content="width=device-width, initial-scale=0.7, maximum-scale=1, user-scalable=yes"/>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta charset="utf-8">

    <!-- Datatable -->
    <link rel="stylesheet" type="text/css" href="/css/dataTables.semanticui.min.css">
    <!-- Semantic -->

    <link href="/css/semantic.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="/css/select2.css">
    <link rel="stylesheet" type="text/css" href="/css/dataTables.semanticui.min.css">
    <link rel="stylesheet" type="text/css" href="/css/icon.min.css">
    <link rel="stylesheet" type="text/css" href="/css/responsive.semanticui.min.css">
    <link rel="stylesheet" type="text/css" href="/css/fixedHeader.semanticui.min.css">

    <link href="/plugins/ionicons/css/ionicons.min.css" rel="stylesheet"/>
    <link href="/plugins/datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet"/>
    <link href="/plugins/weather-icons/css/weather-icons-wind.min.css" rel="stylesheet"/>
    <link href="/plugins/weather-icons/css/weather-icons.min.css" rel="stylesheet"/>
    <link href="/plugins/chartist/chartist.min.css" rel="stylesheet"/>
    <link href="/css/chat-page.css" rel="stylesheet"/>
    <link rel="shortcut icon" href="/favicon.ico"/>
    <link href="/plugins/pacejs/pace.css" rel="stylesheet"/>
    <link href="/css/semantic.min.css" rel="stylesheet"/>
    <!--<link href="/css/main.css" rel="stylesheet"/>-->
    <link href="/css/calendar.min.css" rel="stylesheet" type="text/css"/>
    <link href="/ordersinplan/css/index.css" rel="stylesheet"/>

    <script src="/js/jquery.min.js"></script>
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
    <script src="/js/select2.full.js"></script>
    <script src="/js/Semantic-UI-Alert.js"></script>
    <script src="/js/dataTables.fixedHeader.min.js"></script>
    <!--<script src="/semantic/dist/semanticmodified.min.js"></script>-->
    <script src="/js/calendar.min.js"></script>
    <script src="/shared/shared.js"></script>
    <script src="/ordersinplan/js/index.js"></script>
</head>

<body>
    <div class="pusher">
        <div class="ui stackable grid" style="background-color:white;">
            <!-- MENU -->
            <div class="sixteen wide column" style="background-color:white;">
                <div class="ui top borderless fixed menu" id="navbar">
                    <a style='text-align:center' id="logo" href="/index.php" class="center aligned item">
                        <h1 style="font-size:21px;">
                            <img src="/images/onex.png" style="max-width:150px;" class="center aligned small image;"/></br>
                            <span data-translate="titleTag-masterPlanning">OneX - Master Planning</span>
                        </h1>
                    </a>
                    <div class='right floated item' style="display: flex;justify-content: space-between;">
                        <img style='margin-left: 20px;'
                             class='ui tiny circular image'
                             src='/users/index.php?gid=<?= SharedManager::getUser()["GID"] ?>'>
                    </div>
                </div>
            </div>

            <!-- GRID MESSAGE -->
            <div class="sixteen wide column" style="padding-top:131px!important;">
                <div class="ui centered grid message" style="flex-direction: column;">
                    <div class="header" style="margin-bottom: .5rem !important;">
                        <a class='ui big blue horizontal label' href='/layout' target="_blank">
                            <i class='setting icon'></i><span data-translate="weeklyAssemblyPlan">Weekly Assembly Plan</span></a>
                    </div>
                    <div style="display: flex; justify-content: center;">
                        <div style="margin-bottom: .5rem !important; font-weight: bold">
                            MV <span data-translate="updated-at">
                            Updated At</span>
                            : <span id="MvLastUpdate"></span>
                            <span><a id="MvLastUpdate2" class='ui big red horizontal label'></a></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <table id="ordersInPlanDatatable" class="ui celled collapsing striped table unstackable responsive" cellspacing="0">
            <thead>
                <tr>
                    <th class="one wide" data-translate="details">Details</th>
                    <th class="one wide" data-translate="projectNo">Project No</th>
                    <th class="one wide" data-translate="line">Line</th>
                    <th class="two wide" data-translate="projectName">Project Name</th>
                    <th class="one wide">CW</th>
                    <th class="two wide" data-translate="assemblyStart">Ass. Start</th>
                    <th class="two wide" data-translate="posItem">Panel</th>
                    <th class="two wide" data-translate="qty">Qty</th>
                    <th class="two wide" data-translate="orderManager">Order Manager</th>
                </tr>
            </thead>
        </table>
    <!-- end of pusher -->
    </div>
</body>

<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/language/translator.php"; ?>
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        const translator = new Translator('ordersinplan', 'body', ['en'], 'en')
    });
</script>
</html>
