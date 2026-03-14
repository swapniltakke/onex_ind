<!DOCTYPE html>
<html>
<?php
SharedManager::checkAuthToModule(12);
include_once '../core/index.php';
$project = $_GET["project"] ?: 0;
SharedManager::saveLog("log_show_missing_material", "Search for Project: $project");
$menu_header_display = 'Show Missing Material';
$current_file = "show_missing_material";
?>
<head>
    <title>OneX | Show Missing Material</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=yes"/>

    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta charset="utf-8">

    <link href="../../css/semantic.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="../../css/dataTables.semanticui.min.css">
    <link rel="stylesheet" type="text/css" href="../../css/responsive.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="../../shared/inspia_gh_assets/css/plugins/dataTables/datatables.min.css">

    <link href="../../css/main.css?13" rel="stylesheet"/>

    <?php include_once '../shared/headerStyles.php' ?>
    
    <script src="../../js/jquery.min.js"></script>
    <script src="../../js/semantic.min.js"></script>
    <script src="../../js/jquery.dataTables.js"></script>
    <script src="../../js/dataTables.semanticui.min.js"></script>
    <script src="../../js/dataTables.buttons.min.js"></script>
    <script src="../../js/buttons.flash.min.js"></script>
    <script src="../../js/jszip.min.js"></script>
    <script src="../../js/pdfmake.min.js"></script>
    <script src="../../js/vfs_fonts.js"></script>
    <script src="../../js/buttons.html5.min.js"></script>
    <script src="../../js/buttons.print.min.js"></script>
    <script src="../../js/buttons.colVis.min.js"></script>
    <script src="../../js/tablesort.js"></script>
    <script src="../../js/Semantic-UI-Alert.js"></script>
    <script src="../../shared/inspia_gh_assets/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <link rel="stylesheet" href="../../css/jquery.toast.min.css">

    <script src="/shared/inspia_gh_assets/js/popper.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/bootstrap.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/bootstrap-select.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <script src="/shared/inspia_gh_assets/js/plugins/dataTables/datatables.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/dataTables/dataTables.bootstrap4.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/select2/js/select2.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/toastr/toastr.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/switchery/switchery.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/iCheck/icheck.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/moment.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/plugins/chosen/chosen.jquery.js"></script>
    <script src="/shared/inspia_gh_assets/js/inspinia.js"></script>
    <script src="/shared/inspia_gh_assets/js/daterangepicker.js"></script>
    
</head>
<style>
    /* Combine styles from both files, removing duplicates */
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

    .ui.search .results {
        max-width: 100% !important;
        width: 100% !important;
        min-width: 100% !important;
        left: 0 !important;
        right: 0 !important;
    }

    .ui.search .results .result {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px !important;
        border-bottom: 1px solid #f0f0f0;
    }

    .ui.search .results .result .content {
        display: flex;
        flex-direction: column;
        margin-left: 10px;
    }

    .ui.search .results .result .title {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .ui.search .results .result .description {
        color: #888;
        font-size: 0.9em;
    }

    .dt-center{ text-align: center !important; }

    #materialSearchDataParent {
        width: 100%;
        overflow-x: auto;
    }

    #orderInfoDataTable {
        width: 100% !important;
        max-width: 100% !important;
    }

    .dataTables_wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .dataTables_wrapper .dataTables_scroll {
        overflow-x: auto;
        overflow-y: hidden;
    }

    .dataTables_wrapper .dataTables_scrollBody {
        overflow-x: auto;
        overflow-y: hidden;
    }

    #customFilterParent {
        width: 100% !important;
        display: block !important;
    }

    /* Scanner icon styles */
    .scanner-icon {
        cursor: pointer;
        margin-left: 10px;
        vertical-align: middle;
    }
    .scanner-icon img {
        width: 24px;
        height: 24px;
    }
</style>
<body>
<div id="wrapper">
    <?php $activePage = '/show_missing_material.php'; ?>
    <?php include_once '../shared/sidebar.php'; ?>
    <?php require_once './updateModal.php'; ?>
    <?php require_once './insertModal.php'; ?>
    <div id="page-wrapper" class="gray-bg">
        <?php include_once 'header.php'; ?>
        <br>
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
    </div>
    <?php $footer_display = 'Show Missing Material';
    include_once '../../assemblynotes/shared/footer.php'; ?>
</div>
</body>
<!-- Mainly scripts -->
<?php include_once '../../assemblynotes/shared/headerSemanticScripts.php'; ?>
<script src="../../shared/shared.js"></script>
<script src="../shared/shared.js"></script>
<script src="./updateOrderModal.js?<?= time() ?>"></script>
<script src="./detailsPage.js?<?= time() ?>"></script>
<script src="./searchProject.js?<?= time() ?>"></script>
<script>
    // Entire JavaScript from the first file goes here
    const projectnumber = getUrlParameters()["project"];    
    // Only validate if projectnumber is not blank or undefined
    if (projectnumber && projectnumber.trim() !== '') {
        $('#projectDetailsSection').show();
    }

    let activeAjaxRequests = 0;        

    function showFullLoader(){
        const loader = document.querySelector('.full-loader');
        if (loader) {
            loader.style.display = 'flex';
        }
    }

    function hideFullLoader(){
        const loader = document.querySelector('.full-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    }

    function showCustomFilterParent(){
        document.querySelector('#customFilterParent').setAttribute('style', 'display: block');
    }

    function showMaterialSearchDataParent(){
        document.querySelector('#materialSearchDataParent').setAttribute('style', 'display: block');
    }

    // Replace your existing AJAX code for panel dropdown with this:
    $(document).ready(function() {
        // Only proceed if we have a project number
        if (!projectnumber || projectnumber.trim() === '') {
            console.log("No project number provided");
            return;
        }

        console.log("Initializing with project number:", projectnumber);
        
        // Function to load panels
        function loadPanels() {
            showFullLoader();
            console.log("Loading panels for project:", projectnumber);
            
            return $.ajax({
                url: '/dpm/dwc/materialsearch/api/getdatamaster.php',
                type: 'GET',
                data: {
                    filter: projectnumber
                },
                success: function(projectSelectionData) {
                    console.log("Panels data received:", projectSelectionData);
                    
                    // Clear existing items
                    $('#panelSearch .menu').empty();
                    
                    // Add default item
                    $('#panelSearch .menu').append('<div class="item" data-value="">Select Panel</div>');
                    
                    // Add panel numbers to dropdown
                    if (projectSelectionData && projectSelectionData.panels) {
                        console.log(`Adding ${projectSelectionData.panels.length} panels to dropdown`);
                        
                        for(const panel of projectSelectionData.panels) {
                            const itemSelectionDiv = document.createElement('div');
                            itemSelectionDiv.classList.add('item');
                            itemSelectionDiv.setAttribute('data-value', panel);
                            itemSelectionDiv.innerText = panel;
                            $('#panelSearch .menu').append(itemSelectionDiv);
                        }
                    } else {
                        console.warn("No panels found in response");
                    }
                    
                    // Force destroy and reinitialize dropdown
                    try {
                        $('#panelSearch').dropdown('destroy');
                    } catch (e) {
                        console.warn("Error destroying dropdown:", e);
                    }
                    
                    // Initialize with proper onChange handler
                    $('#panelSearch').dropdown({
                        onChange: function(value) {
                            console.log("Panel dropdown changed to:", value);
                            if (value) {
                                saveSelectedValues('panel');
                                
                                // Get project details
                                const projectNo = $('#projectSearchInput').val();
                                console.log("Getting order details for project:", projectNo);
                                
                                // Chain promises for data loading
                                getOrderDetail(projectNo)
                                    .then((prom) => {
                                        if (prom === null) throw "Project is not found";
                                        return getProjectProductDetails(projectNo);
                                    })
                                    .then(() => getOrderPanels(projectNo))
                                    .then(() => getProjectNotes(projectNo))
                                    .then(() => {
                                        console.log("All data loaded successfully");
                                        showProjectDetailsDiv();
                                    })
                                    .catch(error => {
                                        console.error("Error in data loading chain:", error);
                                        hideFullLoader();
                                    });
                            }
                        }
                    });
                    
                    // Set selected panel if available in URL
                    const panelNumber = getUrlParameters()["panel"];
                    if (panelNumber) {
                        console.log("Setting panel from URL parameter:", panelNumber);
                        setTimeout(() => {
                            $('#panelSearch').dropdown('set selected', panelNumber);
                        }, 300);
                    }
                    
                    hideFullLoader();
                },
                error: function(xhr, status, error) {
                    console.error("Error loading panels:", error);
                    console.error("Response:", xhr.responseText);
                    hideFullLoader();
                }
            });
        }
        
        // Load panels when page is ready
        setTimeout(loadPanels, 500);
    });
    
</script>
</html>