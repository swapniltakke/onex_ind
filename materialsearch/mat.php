<?php
SharedManager::checkAuthToModule(3);
$project = $_GET["project"] ?: 0;
SharedManager::saveLog("log_material_search", "Search for Project: $project");
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
    .full-loader{
        position: fixed !important;
        top: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(50, 50, 50, 0.8) !important;
        z-index: 10000;
    }

    .full-loader > .loader{
        display: flex !important;
        height: 58px;
        flex-direction: column-reverse;
        width: fit-content;
        font-size: 17px;
    }

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

    .ui.tiny.button,
    .ui.tiny.buttons .button,
    .ui.tiny.buttons .or {
        font-size: 14px !important;
    }

    .dt-center{ text-align: center !important; }

    /* Horizontal Scroll Wrapper */
    .table-scroll-wrapper {
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
    }

    .table-scroll-wrapper table {
        width: 100%;
        margin: 0;
        min-width: 2000px;
    }

    /* DataTables specific styling */
    #orderInfoDataTable {
        margin-bottom: 0 !important;
    }

    #orderInfoDataTable thead th {
        white-space: nowrap;
        padding: 10px 8px !important;
        font-size: 12px;
        text-align: center !important;
        vertical-align: middle !important;
    }

    #orderInfoDataTable tbody td {
        white-space: nowrap;
        padding: 8px 5px !important;
        font-size: 12px;
    }

    /* Scrollbar styling */
    .table-scroll-wrapper::-webkit-scrollbar {
        height: 8px;
    }

    .table-scroll-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .table-scroll-wrapper::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .table-scroll-wrapper::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Hide DataTables default wrapper elements that cause duplicate headers */
    .dataTables_wrapper {
        width: 100%;
    }

    .dataTables_scroll {
        clear: both;
    }

    .dataTables_scrollHead {
        overflow: visible !important;
        border: none !important;
    }

    .dataTables_scrollBody {
        overflow-x: auto !important;
        overflow-y: auto !important;
        border: 1px solid #ddd !important;
    }

</style>
<body>
<div class="ui inverted segment full-loader" style="display: flex">
    <div class="ui active inverted loader">Loading</div>
</div>
<div class="ui fixed menu" style="
                padding-top: 3px;

                color:teal;

            ">
    <div class="ui center aligned container">
        <div class="ui centered small image">
            <a href="/">
                <img class="logo" src="/images/onex.png">
            </a>
        </div>
    </div>
</div>
<div class="ui fluid container" style='
                    margin-top:6em;
             '>
    <div class="twelve wide column">
        <div class="ui segment middle aligned">
            <h3 class="ui center aligned header">
                <div class="ui center aligned fluid container">
                    <div class="ui search">
                        <div class="ui left icon input">
                            <input class="prompt" type="text" data-placeholder-translate="projectSearch" autofocus>
                            <i class="search icon"></i>
                        </div>
                    </div>
                </div>
            </h3>
        </div>
        <div class="ui date-none" id="projectsList"></div>
    </div>
</div>

<div class='ui active tab segment cont'>
    <div class='' style='margin-top: -27px!important;padding: 5px!important;'>
        <div class='ui accordion' style='width: inherit;'>
            <div class='title'>

                <div class='ui sixteen column grid'>
                    <div class='row'>
                        <!-- PROJECT DETAILS -->
                        <div class='sixteen wide column'>
                            <div class='ui row'>
                                <!-- Project No  -->
                                <a class='ui blue image big label'>
                                    <i class='line chart icon'></i>
                                    <span id="infoProjectNo"></span>
                                    <div id="infoProjectName" class='detail' style='opacity: 1;background: rgba(0,0,0,0);'></div>
                                </a>
                                <!-- Type -->
                                <a class='ui image green big label'>
                                    <i class='sitemap  icon'></i>
                                    <span id="infoProjectType"></span>
                                    <div class='detail' style='opacity: 1;'
                                         data-translate='panelType'>Panel Type</div>
                                </a>
                                <!-- Qty -->
                                <a class='ui image orange big label'>
                                    <i class='html5 icon'></i>
                                    <span id="infoPanelQuantity"></span>
                                    <div class='detail' style='opacity: 1;'
                                         data-translate='qty'>Qty</div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class='content'>
        <!-- Tab Begin -->
        <!-- Predefined Search Bar -->
        <div class='ui stackable responsive  grid'>
            <div class='three wide column' id='panelDrop'>
                <div id='sourceTypeSelect' class='ui fluid selection dropdown' tabindex='0'>
                    <i class='dropdown icon'></i>
                    <input type='hidden' name='sourceTypeInput'>
                    <div class='default text' data-translate='source-select'></div>
                    <div class='menu'>
                        <div class='item' data-value=''>Source: Default</div>
                        <div class='item' data-value='0' data-translate='sourcetype-prod'>Source: Production</div>
                        <div class='item' data-value='1' data-translate='sourcetype-plan'>Source: Plan</div>
                        <div class='item' data-value='2' data-translate='sourcetype-all'>Source: Production+Plan</div>
                    </div>
                </div>
                <br>
                <!-- DROPDOWN FOR PANELS -->
                <div id='panelSearch' class='ui fluid selection dropdown' tabindex='0'>
                    <i class='dropdown icon'></i>
                    <input type='hidden' name='panelInput'>
                    <div class='default text' data-translate='panel-select'></div>
                    <div class='menu'>
                        <div class='item' data-value=''>Select Panel</div>
                        <div class='item' data-value='-1' data-translate='all-panels'>All Panels</div>
                    </div>
                </div>
                <!--<div id='kmatSearch' class='ui fluid selection dropdown' tabindex='0'>
                    <i class='dropdown icon'></i>
                    <input type='hidden' name='kmatInput'>
                    <div class='default text' data-translate='kmat-select'>Select KMAT</div>
                    <div class='menu'>
                        <div class='item' data-value='0' data-translate='all-kmats'>All KMats</div>
                    </div>
                </div>-->
            </div>
            <div class='thirteen wide column' id="customFilterParent" style="display: none">
                <!-- Search Bar -->
                <div class='ui grid center aligned'>
                    <div class='sixteen wide column'>
                        <div class='ui  big search'>
                            <div class='ui center icon fluid input'>
                                <input name='searchstring' class='searchstring' type='text' data-placeholder-translate='search-something' autocomplete='off' autofocus>
                                <i class='red search icon'></i>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Search Bar -->
                <div class='ui row'>
                    <a class="ui image red button" id="allsearch">
                        <span data-translate="all">All</span>
                    </a>
                    <a class='ui image blue button' id='busbarSearch'>
                        Busbar
                    </a>
                    <a class='ui image teal button' id='ctsearch'>
                        CT
                    </a>
                    <a class='ui image teal button' id='vtsearch'>
                        VT
                    </a>
                    <a class='ui image teal button' id='vcbsearch'>
                        VCB
                    </a>
                    <a class='ui image teal button' id='bushingsearch'>
                        Bushing
                    </a>
                    <a class='ui image teal button' id='wsearch'>
                        PAW
                    </a>
                    <a class='ui image teal button' id='shroudsearch'>
                        Shroud
                    </a>
                    <a class='ui image teal button' id='criticalsearch'>
                        <span data-translate='critical-parts'>Earthing Switch</span>
                    </a>
                    <a class='ui image teal button' id='transversesearch'>
                        Transverse Partition
                    </a>
                    <a class='ui image teal button' id='mimicsearch'>
                        Mimic
                    </a>
                    <a class='ui image teal button' id='gfksearch'>
                        GFK
                    </a>
                    <a class='ui image teal button' id='wiremeshsearch'>
                        Wiremesh
                    </a>
                    <a class='ui image teal button' id='cvisearch'>
                        CVI
                    </a>
                    <a class='ui image teal button' id='cbctsearch'>
                        CBCT
                    </a>
                </div>
                <div class='ui row'>
                    <a class='ui image teal button' id='t90'>
                        T 90
                    </a>
                    <a class='ui image teal button' id='heater'>
                        <span data-translate='heater'>Heater</span>
                    </a>
                    <a class='ui image teal button' id='termostat'>
                        Termostat
                    </a>
                    <a class='ui image teal button' id='bobbin'>
                        Bobbin
                    </a>
                    <a class='ui image teal button' id='parafudr'>
                        Surge Arrester
                    </a>
                    <a class='ui image teal button' id='kabloagaci'>
                        <span data-translate='cable-tree'>Cable Harness</span>
                    </a>
                    <a class='ui image teal button' id='fan'>
                        Fan
                    </a>
                    <a class='ui image teal button' id='arcsensor'>
                        Arc Sensor
                    </a>
                    <a class='ui image teal button' id='cvdsearch'>
                        CVD
                    </a>
                    <a class='ui image teal button' id='irWindowsearch'>
                        IR Window
                    </a>
                    <a class='ui image teal button' id='limitSwitchsearch'>
                        Limit Switch
                    </a>
                    <a class='ui image teal button' id='deepbottomsearch'>
                        Deep Bottom Pan
                    </a>
                    <a class='ui image teal button' id='ipcomponentsearch'>
                        IP Component List
                    </a>
                    <a class='ui image teal button' id='advancecomponent'>
                        Advanced PR Component
                    </a>
                </div>
            </div>
        </div>

        <div class='ui section' id="materialSearchDataParent" style="display: none">
            <div id='orderinfo'>
                <table id='orderInfoDataTable' class='ui green unstackable celled striped table display' cellspacing='0' width='100%' >
                    <thead>
                        <tr class='center aligned'>
                            <th>PO Number</th>
                            <th>Sales Order</th>
                            <th>Panel</th>
                            <th>Typical</th>
                            <th>Material Number</th>
                            <th>Short Text</th>
                            <th>Req.<br>Qty</th>
                            <th>Qty<br>Withdrawn</th>
                            <th>Head. Lvl<br>Ass. Number</th>
                            <th>BUn</th>
                            <th>MRP</th>
                            <th>Ind.<br>Backflush</th>
                            <th>Typ</th>
                            <th>Item<br>Cat.</th>
                            <th>Storage<br>Loc.</th>
                            <th>KMAT</th>
                            <th>KMAT Name</th>
                            <th>Material<br>Grouping</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="/shared/shared.js"></script>
<script>
    const projectnumber = getUrlParameters()["project"];

    if(isNaN(+projectnumber))
        throw new Error("Project number should be numeric");

    let activeAjaxRequests = 0;        

    function showFullLoader(){
        document.querySelector('.full-loader').setAttribute('style', 'display: flex');
    }

    function hideFullLoader(){
        document.querySelector('.full-loader').setAttribute('style', 'display: none');
    }

    function showCustomFilterParent(){
        document.querySelector('#customFilterParent').setAttribute('style', 'display: block');
    }

    function showMaterialSearchDataParent(){
        document.querySelector('#materialSearchDataParent').setAttribute('style', 'display: block');
    }

    $(document).ready(function () {
        // Setup AJAX beforeSend and complete handlers
        $.ajaxSetup({
            beforeSend: function () {
                activeAjaxRequests++;
                showFullLoader();
            },
            complete: function () {
                activeAjaxRequests--;
                if (activeAjaxRequests === 0) {
                    hideFullLoader();
                }
            }
        });

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
                minCharacters: 4
            });
        if (projectnumber.length < 4)
            return;

        $("#projectsList").removeClass("date-none");
        (async () => {
            const projectSelectionData = await $.ajax( {
                url: '/materialsearch/api/getdatamaster.php',
                type: 'GET',
                data: {
                    filter: projectnumber
                }
            }).catch(e => {
                console.log(e);
            });

            for(const panel of projectSelectionData["panels"]){
                const itemSelectionDiv = document.createElement('div');
                itemSelectionDiv.classList.add('item');
                itemSelectionDiv.setAttribute('data-value', panel);
                itemSelectionDiv.innerText = panel;
                document.querySelector('#panelSearch > .menu').appendChild(itemSelectionDiv);
            }
            $('#panelSearch').dropdown('setting', 'onChange', function(){
                let panelNumber = ($('#panelSearch').dropdown('get value') || '').match(/^[^|]*/)[0];
                let sourceType = $('#sourceTypeSelect').dropdown('get value');
                if(!panelNumber) return;
                //let kmatName = $('#defaultkmat')[0].innerHTML;

                initOrderInfoDatatable(panelNumber, sourceType, currentDTFilter);
                initOrderInfoTableFilters();
            });

            $('#sourceTypeSelect').dropdown('setting', 'onChange', function(){
                let panelNumber = ($('#panelSearch').dropdown('get value') || '').match(/^[^|]*/)[0];
                let sourceType = $('#sourceTypeSelect').dropdown('get value');
                if(!sourceType) return;
                if(!panelNumber) return;
                initOrderInfoDatatable(panelNumber, sourceType, currentDTFilter);
                initOrderInfoTableFilters();
            });

            /*$('#kmatSearch').dropdown('setting', 'onChange', function(){
                let panelNumber = $('#panelSearch').dropdown('get value');
                let kmatName = $('#defaultkmat').dropdown('get value');
                initOrderInfoDatatable("", )
            });*/

            const projectData = await $.ajax({
                url: '/materialsearch/api/material_segments.php',
                type: 'GET',
                data: {
                    projectFilter: projectnumber
                }
            }).catch(e => {
                console.log(e);
            });
            document.getElementById('infoProjectNo').innerText = projectData.projectNo;
            document.getElementById('infoProjectName').innerText = projectData.projectName;
            document.getElementById('infoProjectType').innerText = projectData.panelType;
            document.getElementById('infoPanelQuantity').innerText = projectData.quantity;

            $("#main-loader").remove();
            $('.ui.accordion').accordion({});

            hideFullLoader();

            // Execute initOrderInfoDatatable only when the panel is found in the URL
            const panelNumber = getUrlParameters()["panel"];
            const sourceType = getUrlParameters()["source_type"];
            if(panelNumber === undefined) {
                // Do not execute initOrderInfoDatatable if panel is not found in the URL
                console.log("Panel number not found in the URL.");
            } else if(sourceType === undefined) {
                // Do not execute initOrderInfoDatatable if panel is not found in the URL
                console.log("Source type not found in the URL.");
            } else {
                const panelSearchDropdown = $('#panelSearch');
                panelSearchDropdown.dropdown('set selected', panelNumber);
                const sourceTypeSelectDropdown = $('#sourceTypeSelect');
                sourceTypeSelectDropdown.dropdown('set selected', sourceType);
                let currentDTFilterURL = {};
                // Get the materialNumbers from the console log
                const materialNumbers = "A7E0030048370|A7E0030048570|A7E0030035570|A7E0030035370|A7E0030035371";
                // Set the currentDTFilterURL object with the materialNumbers
                currentDTFilterURL = { materialNumbers: materialNumbers };
                $('input[name=searchstring]').val(materialNumbers);
                initOrderInfoDatatable(panelNumber, sourceType, currentDTFilterURL);
                initOrderInfoTableFilters();
            }
        })();
    });

    let currentDTFilter = {};
    function initOrderInfoDatatable(panelNumber, sourceType, searchParams = {}){
        currentDTFilter = searchParams;

        if($.fn.dataTable.isDataTable('#orderInfoDataTable'))
            $('#orderInfoDataTable').DataTable().clear().destroy();

        let searchQueryString = "";
        for(const [key, val] of Object.entries(searchParams)){
            searchQueryString += `&${key}=${val}`;
        }

        if(Object.entries(searchParams).length !== 0)
            searchQueryString = '&' + searchQueryString.slice(1);

        showCustomFilterParent();
        showMaterialSearchDataParent();

        $('#orderInfoDataTable').DataTable({
            dom: 'Bfrltip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    className: 'ui teal button'
                }
            ],
            ajax: `/materialsearch/api/materialsearch.php?project=${projectnumber}&panel=${panelNumber}&source_type=${sourceType}${searchQueryString}`,
            columnDefs: [
                { 'className': 'dt-center', 'targets': '_all' }
            ],
            columns: getColumnDefinitions(searchParams.type),
            responsive: false,
            paging: true,
            pageLength: 25,
            initComplete: function() {
                // Wrap the table in scroll container after initialization
                const tableWrapper = $('#orderInfoDataTable').closest('div');
                if (!tableWrapper.hasClass('table-scroll-wrapper')) {
                    $('#orderInfoDataTable').wrap('<div class="table-scroll-wrapper"></div>');
                }
            }
        });
    }

    function getColumnDefinitions(type) {
        if (type === 'advancecomponent') {
            return [
                { data: 'OrderNumber', visible: false },
                { 
                    data: 'SalesOrder',
                    title: 'Sales Order',
                    visible: true 
                },
                { data: 'Panel', visible: false },
                { data: 'Typical', visible: false },
                { 
                    data: 'MaterialNumber2',
                    title: 'Material Number',
                    visible: true 
                },
                { 
                    data: 'MaterialDescription',
                    title: 'Short Text',
                    visible: true 
                },
                { 
                    data: 'ReqQuantity',
                    title: 'Req. Qty',
                    visible: true 
                },
                { data: 'QuantityWithdrawn', visible: false },
                { data: 'MaterialNumber', visible: false },
                { data: 'BUn', visible: false },
                { data: 'MRP', visible: false },
                { data: 'IndicatorBackflush', visible: false },
                { data: 'Typ', visible: false },
                { data: 'ItemCategory', visible: false },
                { data: 'StorageLocation', visible: false },
                { data: 'KMATNo', visible: false },
                { data: 'KMATName', visible: false },
                { data: 'MaterialGrouping', visible: false }
            ];
        } else {
            return [
                { data: 'OrderNumber', visible: true },
                { data: 'SalesOrder', visible: true },
                { data: 'Panel', visible: true },
                { data: 'Typical', visible: true },
                { data: 'MaterialNumber2', visible: true },
                { data: 'MaterialDescription', visible: true },
                { data: 'ReqQuantity', visible: true },
                { data: 'QuantityWithdrawn', visible: true },
                { data: 'MaterialNumber', visible: true },
                { data: 'BUn', visible: true },
                { data: 'MRP', visible: true },
                { data: 'IndicatorBackflush', visible: true },
                { data: 'Typ', visible: true },
                { data: 'ItemCategory', visible: true },
                { data: 'StorageLocation', visible: true },
                { data: 'KMATNo', visible: true },
                { data: 'KMATName', visible: true },
                { data: 'MaterialGrouping', visible: true }
            ];
        }
    }

    const bushingSearchArray = [
        'A7E0011032553', 'A7E0011870033', 'A7E0011035423', 'A7E0011036733', 'A7E0011056883', 'A7E0011870073', 'A7E0011031013', 'A7E0011050893', 'A7E0011034453', 
        'A7E0011056893', 'A7E0011875103', 'A7E0011032123', 'A7E0011050903', 'A7E0011034783', 'A7E0011032563', 'A7E0011050853', 'A7E0017801890', 'A7E0012870053', 
        'A7E0012870073', 'A7E0012870063', 'A7E0012880023', 'A7E0012840513', 'A7E0012870093', 'A7E0012870113', 'A7E0012870103', 'A7E0012870133', 'A7E0012870153', 
        'A7E0012870143', 'A7E0012880043', 'A7E0012880383', 'A7E0012870023', 'A7E0012870043', 'A7E0012870033', 'A7E0012880003', 'A7E0011032513', 'A7E0017210883', 
        'A7E0017201923', 'A7E0017214963', 'A7E0012356170'
    ];

    const criticalSearchArray = [
        'A7E0011039333', 'A7E0011040553', 'A7E0011039243', 'A7E0011039263', 'A7E0011937713', 'A7E0011991003', 'A7E0011039323', 'A7E0011039343', 'A7E0011991143', 
        'A7E0011039253', 'A7E0011079143', 'A7E0011991163', 'A7E0011040533', 'A7E0011039353', 'A7E0011090603', 'A7E0017201403', 'A7E0017231623', 'A7E0017290003', 
        'A7E0011039273', 'A7E0017231643', 'A7E0011045223', 'A7E0011957473', 'A7E0011954493', 'A7E0011953893', 'A7E0011991023', 'A7E0011956233', 'A7E0011956203', 
        'A7E0012935453', 'A7E0012935313', 'A7E0017290093', 'A7E0017261583', 'A7E0011957503', 'A7E0011954883', 'A7E0011956893', 'A7E0011991173', 'A7E0011991033', 
        'A7E0017236653', 'A7E0017236683', 'A7E0011956273', 'A7E0011955333', 'A7E0011954453', 'A7E0011956783', 'A7E0011956763', 'A7E0011956793', 'A7E0011956773', 
        'A7E0011956753', 'A7E0011956243', 'A7E0012935163', 'A7E0012935533', 'A7E0011956803', 'A7E0011094823'
    ];

    const shroudSearchArray = [
        'A7E0011088430', 'A7E0011088470', 'A7E0011089270', 'A7E0011969820', 'A7E0011969830', 'A7E0011088830', 'A7E0011088230', 'A7E0011088250', 'A7E0011088490', 
        'A7E0011980070', 'A7E0011088410', 'A7E0011088390', 'A7E0011984440', 'A7E0011088350', 'A7E0011088330', 'A7E0011088450', 'A7E0011088370', 'A7E0011088290', 
        'A7E0011984260', 'A7E0011088270', 'A7E0011970710', 'A7E0011970720', 'A7E0011088310', 'A7E0011088510', 'A7E0011089870', 'A7E0011089910', 'A7E0011089190', 
        'A7E0011984600', 'A7E0011089210', 'A7E0011983740', 'A7E0011089090', 'A7E0011089390', 'A7E0011089430', 'A7E0011089250', 'A7E0011089330', 'A7E0011089370', 
        'A7E0011089230', 'A7E0011088790', 'A7E0011088810', 'A7E0011089670', 'A7E0011985910', 'A7E0011089720', 'A7E0011089730', 'A7E0011089170', 'A7E0011089830', 
        'A7E0011089450', 'A7E0011089410', 'A7E0011089790', 'A7E0011089290', 'A7E0011089110', 'A7E0011089850', 'A7E0076172050', 'A7E0076172190', 'A7E0076172230', 
        'A7E0011080080', 'A7E0011080090', 'A7E0011087380', 'A7E0017281160', 'A7E0011980390', 'A7E0011983670', 'A7E0011983690', 'A7E0017281170', 'A7E0011081560', 
        'A7E0011081580', 'A7E0011081600', 'A7E0011081620', 'A7E0011081640', 'A7E0017280240', 'A7E0017280250', 'A7E0011089710', 'A7E0011089690', 'A7E0011985550', 
        'A7E0011083360', 'A7E0011980590', 'A7E0011980600', 'A7E0011980610', 'A7E0011980620', 'A7E0011980630', 'A7E0011980640', 'A7E0011980650', 'A7E0011980660', 
        'A7E0011089490', 'A7E0011089470', 'A7E0011089950', 'A7E0011088850', 'A7E0011088870', 'A7E0011089310', 'A7E0011089350', 'A7E0011088930', 'A7E0011089030', 
        'A7E0011985570', 'A7E0011985590', 'A7E0011987010', 'A7E0011088890', 'A7E0011088950', 'A7E0011088970', 'A7E0011088910', 'A7E0017220690', 'A7E0017220700', 
        'A7E0011088320', 'A7E0011986260', 'A7E0017281200', 'A7E0017281180', 'A7E0011967103', 'A7E0011967193', 'A7E0011085750', 'A7E0011089770', 'A7E0011089750'
    ];

    const advancecomponentArray = [
        'A7E0011039333', 'A7E0011040553', 'A7E0011039243', 'A7E0011039263', 'A7E0011937713', 'A7E0011991003', 'A7E0011039323', 'A7E0011039343', 'A7E0011991143', 
        'A7E0011039253', 'A7E0011079143', 'A7E0011945823', 'A7E0011064320', 'A7E0011064310', 'A7E0017208283', 'A7E0017208373', 'A7E0017208303', 'A7E0011991163', 
        'A7E0011040533', 'A7E0011039353', 'A7E0088862813', 'A7E0011090603', 'A7E0017201403', 'A7E0017231623', 'A7E0017290003', 'A7E0011039273', 'A7E0017231643', 
        'A7E0011045223', 'A7E0002944683', 'A7E0089440400', 'A7E0011064300', 'A7E0011034683', 'A7E0012385133', 'A7E0011975920', 'A7E0012385153', 'A7E0011979640', 
        'A7E0012385143', 'A7E0011975940', 'A7E0011977930', 'A7E0011977910', 'A7E0011979630', 'A7E0011915663', 'A7E0011945203', 'A7E0011915683', 'A7E0011022510', 
        'A7E0011953820', 'A7E0011022520', 'A7E0011983940', 'A7E0011983950', 'A7E0011978780', 'A7E0011978740', 'A7E0011978770', 'A7E0011978730', 'A7E0011978750', 
        'A7E0011978760', 'A7E0088300250', 'A7E0088300260', 'A7E0011978720', 'A7E0011983860', 'A7E0011983870', 'A7E0011984040', 'A7E0011978890', 'A7E0011978900', 
        'A7E0011941250', 'A7E0011965503', 'A7E0011979120', 'A7E0011965903', 'A7E0011979130', 'A7E0089431220', 'A7E0089418000', 'A7E0011975790', 'A7E0011970093', 
        'A7E0011970103', 'A7E0011942100', 'A7E0011965803', 'A7E0011965813', 'A7E0011942010', 'A7E0011942020', 'A7E0011942090', 'A7E0011942030', 'A7E0084050210', 
        'A7E0011942080', 'A7E0011942110', 'A7E0011978940', 'A7E0011978950', 'A7E2219895561', 'A7E0011968043', 'A7E0011945813', 'A7E0011945803', 'A7E0011947100', 
        'A7E0011983910', 'A7E0011983920', 'A7E0011984340', 'A7E0011983900', 'A7E0011941690', 'A7E0011967343', 'A7E0011953890', 'A7E0011965713', 'A7E0011983930', 
        'A7E0011953810', 'A7E0011953800', 'A7E0011953790', 'A7E0011984750', 'A7E0011985250', 'A7E0011985260', 'A7E0011953710', 'A7E0011969663', 'A7E0011957473', 
        'A7E0011954493', 'A7E0011953893', 'A7E0011914873', 'A7E0011945253', 'A7E0011914893', 'A7E0011077680', 'A7E0011991023', 'A7E0011956233', 'A7E0011956203', 
        'A7E0012935453', 'A7E0012935313', 'A7E0017260350', 'A7E0017263040', 'A7E0017260370', 'A7E0017290093', 'A7E0017261583', 'A7E0011957503', 'A7E0011954883', 
        'A7E0011956893', 'A7E0011077770', 'A7E0011991173', 'A7E0011991033', 'A7E0017236653', 'A7E0017236683', 'A7E0011956273', 'A7E0011955333', 'A7E0011954453', 
        'A7E0011956783', 'A7E0011915693', 'A7E0011977940', 'A7E0011945223', 'A7E0011979790', 'A7E0011915713', 'A7E0011977960', 'A7E0012984550', 'A7E0011964183', 
        'A7E0017263383', 'A7E0017264610', 'A7E0017221950', 'A7E0017262503', 'A7E0017266043', 'A7E0017236633', 'A7E0011096223', 'A7E0011094823', 'A7E0017293113', 
        'A7E0017293153', 'A7E0017293093', 'A7E0017293133', 'A7E0017293103', 'A7E0017293143', 'A7E0017293163', 'A7E0017293123', 'A7E0017293173', 'A7E0017293183', 
        'A7E0011956753', 'A7E0011956773', 'A7E0011956793', 'A7E0011956243', 'A7E0011956803', 'A7E0012983420', 'A7E0011979990', 'A7E0011984780', 'A7E0011984790', 
        'A7E0011984800', 'A7E0011984070', 'A7E0011947080', 'A7E0011984810', 'A7E0012983410', 'A7E0011984820', 'A7E0011984770', 'A7E0017265483', 'A7E0011032553', 
        'A7E0011870033', 'A7E0011035423', 'A7E0011036733', 'A7E0011056883', 'A7E0011870073', 'A7E0011031013', 'A7E0011050893', 'A7E0011034453', 'A7E0011056893', 
        'A7E0011875103', 'A7E0011032123', 'A7E0011050903', 'A7E0011050853', 'A7E0017801890', 'A7E0012870053', 'A7E0012870073', 'A7E0012870063', 'A7E0012880023', 
        'A7E0012870093', 'A7E0012870113', 'A7E0012870103', 'A7E0012870133', 'A7E0012870153', 'A7E0012870143', 'A7E0012880043', 'A7E0012880383', 'A7E0012870023', 
        'A7E0012870043', 'A7E0012870033', 'A7E0012880003', 'A7E0011032513', 'A7E0017210883', 'A7E0017214963', 'A7E0012929223', 'A7E0012929253', 'A7E0012929233', 
        'A7E0012929243', 'A7E0011091953', 'A7E3700217615', 'A7E0076495693', 'A7E4406010086', 'A7E0002917883', 'A7E0011081780', 'A7E0011081790', 'A7E0011087310', 
        'A7E0011087320', 'A7E0011087370', 'A7E0011088660', 'A7E0011088670', 'A7E0011088680', 'A7E0011088690', 'A7E0011088700', 'A7E0011850310', 'A7E0011850320', 
        'A7E0011850330', 'A7E0011850340', 'A7E0011850350', 'A7E0011850380', 'A7E0011850390', 'A7E0011850400', 'A7E0011850410', 'A7E0011850430', 'A7E0011850440', 
        'A7E0011850450', 'A7E0011984330', 'A7E0011984420', 'A7E0011984520', 'A7E0011984650', 'A7E0011984660', 'A7E0011984670', 'A7E0011984680', 'A7E0011984690', 
        'A7E0011984700', 'A7E0011984710', 'A7E0011984720', 'A7E0011986290', 'A7E0011986300', 'A7E0011986310', 'A7E0012980060', 'A7E0012980070', 'A7E0012980080', 
        'A7E0012980090', 'A7E0012980100', 'A7E0012980110', 'A7E0011996453', 'A7E0011996563', 'A7E0011996553', 'A7E0011996573', 'A7E0011996603', 'A7E0011996593', 
        'A7E0011996613', 'A7E0011996703', 'A7E0011996723', 'A7E0011996643', 'A7E0011996673', 'A7E0011996713', 'A7E0011996733', 'A7E0011996653', 'A7E0011996663', 
        'A7E0011996683', 'A7E0011996693', 'A7E0011996803', 'A7E0011996823', 'A7E0011996743', 'A7E0011996773', 'A7E0011996813', 'A7E0011996833', 'A7E0011996753', 
        'A7E0011996763', 'A7E0011996783', 'A7E0011996793'
    ];

    function initOrderInfoTableFilters(){
        var enterEvent = jQuery.Event("keypress");
        enterEvent.which = 13; // Enter key code

        $('#allsearch').click(function() {
            enterEvent.params = {
                shortText: ''
            }
            $('input[name=searchstring]').val('').trigger(enterEvent);
        });

        $('#cvvtsearch').click(function() {
            const searchStr = '4MA|CT12|4MC96|KAT-|KA-|4MR|VB3|VBF|GDB';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#criticalsearch').click(function() {
            const criticalSearchStr = criticalSearchArray.join('|');
            enterEvent.params = {
                materialNumbers2: criticalSearchStr
            }
            $('input[name=searchstring]').val(criticalSearchStr).trigger(enterEvent);
        });

        $('#shroudsearch').click(function() {
            const shroudSearchStr = shroudSearchArray.join('|');
            enterEvent.params = {
                materialNumbers2: shroudSearchStr
            }
            $('input[name=searchstring]').val(shroudSearchStr).trigger(enterEvent);
        });

        $('#busbarSearch').click(function() {
            const searchStr = 'EN1|EN2';
            enterEvent.params = {
                mrp: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#ctsearch').click(function() {
            const searchStr = '4MA|CT12|4MC96|KAT-|KA-|AB24|4MB61';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#vtsearch').click(function() {
            const searchStr = '4MR|VB3|VBF|GDB';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#vcbsearch').click(function() {
            const searchStr = '3AE|3AH|3AK';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#bushingsearch').click(function() {
            const bushingSearchStr = bushingSearchArray.join('|');
            enterEvent.params = {
                materialNumbers2: bushingSearchStr
            }
            $('input[name=searchstring]').val(bushingSearchStr).trigger(enterEvent);
        });
        $('#t90').click(function() {
            const searchStr = 'ZCT';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#heater').click(function() {
            const searchStr = 'Heater';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#termostat').click(function() {
            const searchStr = 'RZH|THC|THERMOSTAT|HYGROSTAT';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#parafudr').click(function() {
            const searchStr = '3EK|SURGE ARRESTER|SURGE_ARRESTOR';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#kabloagaci').click(function() {
            const searchStr = 'HARNESS CT|Harness for Aux ES|Harness VT|Harness';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#fan').click(function() {
            const searchStr = 'NXAIR fan 270mm|FAN|11018113|A7E0002919053|A7E0011640503|A7E0011918803';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#arcsensor').click(function() {
            const searchStr = 'Arc|Optic|OPTICAL|Sensor';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#wsearch').click(function() {
            const searchStr = 'A7E0030048370|A7E0030048570|A7E0030035570|A7E0030035370|A7E0030035371';
            enterEvent.params = {
                materialNumbers: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#bobbin').click(function() {
            const searchStr = 'INTERLOCK SOLENOID|OPERATING SOLENOID';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#transversesearch').click(function() {
            const searchStr = 'TRANSVERSE';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#mimicsearch').click(function() {
            const searchStr = 'Mimic';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#gfksearch').click(function() {
            const searchStr = 'GFK';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#wiremeshsearch').click(function() {
            const searchStr = 'Wiremesh|Meshwire|Mesh_wire|Wire_mesh';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#cvisearch').click(function() {
            const searchStr = 'CVI';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#cbctsearch').click(function() {
            const searchStr = 'CBCT';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#cvdsearch').click(function() {
            const searchStr = 'CVD';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#irWindowsearch').click(function() {
            const searchStr = 'IR WINDOW';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#limitSwitchsearch').click(function() {
            const searchStr = 'Limit Switch';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#deepbottomsearch').click(function() {
            const searchStr = 'Deep';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#ipcomponentsearch').click(function() {
            const searchStr = 'IPX|IPX2 cover|IPX2 covers|IP cover';
            enterEvent.params = {
                shortText: searchStr
            }
            $('input[name=searchstring]').val(searchStr).trigger(enterEvent);
        });

        $('#advancecomponent').click(function() {
            const advancecomponentStr = advancecomponentArray.join('|');
            enterEvent.params = {
                materialNumbers2: advancecomponentStr,
                type: 'advancecomponent'
            }
            $('input[name=searchstring]').val(advancecomponentStr).trigger(enterEvent);
        });

        $('input[name=searchstring]').on('keypress', function(event) {
            if (event.which !== 13){
                return;
            }
            const panelNumber = ($('#panelSearch').dropdown('get value') || '').match(/^[^|]*/)[0];
            const sourceType = $('#sourceTypeSelect').dropdown('get value');
            initOrderInfoDatatable(panelNumber, sourceType, event.params);
        });
    }

</script>
</body>
</html>
