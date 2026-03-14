<!DOCTYPE html>
<html>
<?php
SharedManager::checkAuthToModule(12);
include_once '../core/index.php';
$project = $_GET["project"] ?: 0;
SharedManager::saveLog("log_files_of_projects", "Search for Project: $project");
$menu_header_display = 'Files of Projects';
?>
<head>
    <title>OneX | Files of Projects</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=yes"/>

    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta charset="utf-8">

    <link href="../../css/semantic.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="../../css/dataTables.semanticui.min.css">
    <link rel="stylesheet" type="text/css" href="../../css/responsive.dataTables.min.css">

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
    <?php $activePage = '/files_of_projects.php'; ?>
    <?php include_once '../shared/sidebar.php'; ?>
    <div id="page-wrapper" class="gray-bg">
        <?php include_once 'header.php'; ?>
        <br>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Files of Projects: Check the DTO information by looking at the TBF file</h3>
                    </div>
                    <div class="card-body">
                        <div style="text-align: right; margin-bottom: 10px;">
                            <button class="btn btn-primary btn-sm" id="backButton" style="display: none;">
                                <i class="fa fa-arrow-left"></i> Back
                            </button>
                        </div>
                        <div id="fileList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $footer_display = 'Files of Projects';
    include_once '../../assemblynotes/shared/footer.php'; ?>
</div>

<!-- Mainly scripts -->
<?php include_once '../../assemblynotes/shared/headerSemanticScripts.php' ?>
<script src="../../shared/shared.js"></script>
<script>
    // Entire JavaScript from the first file goes here
    const projectnumber = getUrlParameters()["project"];

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

    var currentDirectoryPath = '';

    // Function to list files and directories
    function listFilesAndDirectories(directory_path) {
        currentDirectoryPath = directory_path;
        $.ajax({
            url: '../get_files.php',
            type: 'POST',
            data: { directory_path: directory_path, files_of_projects: "1"},
            success: function(response) {
                $('#fileList').html(response);
                var salesOrderNo = $('#projectSearchInput').val();
                // Show or hide the back button based on the directory path
                if (directory_path !== '\\\\inmumtha111dat\\M-Tool\\M-Tool\\Documents\\NXAIR\\Orders\\' + salesOrderNo) {
                    $('#backButton').show();
                } else {
                    $('#backButton').hide();
                }

                // Add click event handler for file and directory links
                $('#fileList a').click(function(e) {
                    e.preventDefault();
                    var path = $(this).attr('href');
                    if (path.indexOf('?directory=') !== -1) {
                        // Directory link clicked
                        var directoryPath = decodeURIComponent(path.split('?directory=')[1]);
                        listFilesAndDirectories(directoryPath);
                    } else if (path.indexOf('?file=') !== -1) {
                        // File link clicked
                        var filePath = decodeURIComponent(path.split('?file=')[1].replace(/\+/g, ' '));
                        openFile(filePath);
                    }
                });
            },
            error: function() {
                $('#fileList').html('Error: Could not list files and directories.');
            }
        });
    }

    // Function to open the file
    function openFile(file_path) {
        $.ajax({
            url: '../get_file.php',
            type: 'POST',
            data: { file_path: encodeURIComponent(file_path) },
            xhrFields: {
                responseType: 'blob'
            },
            success: function(response) {
                // Create a download link
                var downloadLink = document.createElement('a');
                downloadLink.href = window.URL.createObjectURL(response);
                downloadLink.download = file_path.split('\\').pop();
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            },
            error: function() {
                alert('Error: Could not download the file.');
            }
        });
    }

    // Function to go to the parent directory
    $('#backButton').click(function() {
        var parentDirectoryPath = currentDirectoryPath.substring(0, currentDirectoryPath.lastIndexOf('\\'));
        listFilesAndDirectories(parentDirectoryPath);
    });

    $(document).ready(function () {
        // Setup AJAX beforeSend and complete handlers
        $.ajaxSetup({
            beforeSend: function (xhr, settings) {
                // Only show loader for project search and data fetching
                if (settings.url.includes('/dpm/dwc/materialsearch/api/search.php') || 
                    settings.url.includes('/dpm/dwc/materialsearch/api/getdatamaster.php') ||
                    settings.url.includes('/dpm/dwc/materialsearch/api/material_segments.php')) {
                    activeAjaxRequests++;
                    showFullLoader();
                }
            },
            complete: function (xhr, status) {
                // Only hide loader for project search and data fetching
                if (xhr.statusText === 'OK') {
                    activeAjaxRequests--;
                    if (activeAjaxRequests === 0) {
                        hideFullLoader();
                    }
                }
            }
        });

        $('.ui.search')
            .search({
                apiSettings: {
                    url: '/dpm/dwc/materialsearch/api/search.php',
                    method: 'GET',
                    beforeSend: function(settings) {
                        // Get values directly
                        const urlFile = "files_of_projects";
                        const projectQuery = $('#projectSearchInput').val();
                        const scanPanelValue = $('#scanPanelValue').val();
                        
                        // Modify the URL parameters
                        settings.data = {
                            file: urlFile,
                            project: projectQuery,
                            scannedPanelValue: scanPanelValue
                        };
                        return settings;
                    }
                },
                fields: {
                    results: 'items',
                    title: 'name',
                    url: 'html_url'
                },
                minCharacters: 4,
                onSelect: function(result, response) {
                    if (result && result.project_no) {
                        let data = {};
                        // Check the valueToSave parameter and set the appropriate data
                        data = {
                            project: result.project_no,
                            clear: 1
                            // Leave panel and station empty
                        };
                        $.ajax({
                            url: '/dpm/dwc/set_project_session.php',
                            method: 'POST',
                            data: data,
                            success: function(response) {
                                console.log('Session updated successfully:', response);
                            },
                            error: function(xhr, status, error) {
                                console.error('Session update failed:', error);
                            }
                        });
                        return false;
                    }
                }
            });

        // Hide loader on initial page load
        hideFullLoader();

        // Skip further processing if no project number
        if (!projectnumber || projectnumber.trim() === '') {
            console.log("No project number provided");
            return;
        }

        // Rest of your existing code...
        if (projectnumber.length < 4)
            return;

        $("#projectsList").removeClass("date-none");

         // Check if panel number and source type are in URL
        const panelNumber = ($('#panelSearch').dropdown('get value') || '').match(/^[^|]*/)[0];

        if (panelNumber) {
            saveSelectedValuesToServerFilesOfProject("panel");
        }

        var salesOrderNo = $('#projectSearchInput').val();
        if (salesOrderNo.trim().length === 10 && !isNaN(salesOrderNo)) {
            listFilesAndDirectories('\\\\inmumtha111dat\\M-Tool\\M-Tool\\Documents\\NXAIR\\Orders\\' + salesOrderNo);
        } else if (projectnumber.trim().length === 10 && !isNaN(projectnumber)) {
            listFilesAndDirectories('\\\\inmumtha111dat\\M-Tool\\M-Tool\\Documents\\NXAIR\\Orders\\' + projectnumber);
        }

        (async () => {
            // Get panel number from URL
            const panelNumber = getUrlParameters()["panel"];
            const panelValue = getUrlParameters()["panel_value"] || '';

            const projectSelectionData = await $.ajax({
                url: '/dpm/dwc/materialsearch/api/getdatamaster.php',
                type: 'GET',
                data: {
                    filter: projectnumber
                }
            }).catch(e => {
                console.log(e);
            });

            // Add panel numbers to dropdown
            for(const panel of projectSelectionData["panels"]){
                const itemSelectionDiv = document.createElement('div');
                itemSelectionDiv.classList.add('item');
                itemSelectionDiv.setAttribute('data-value', panel);
                itemSelectionDiv.innerText = panel;
                document.querySelector('#panelSearch > .menu').appendChild(itemSelectionDiv);
            }

            // Reinitialize dropdown to include new items
            $('#panelSearch').dropdown('destroy').dropdown();

            // Defer the selection to ensure dropdown is fully initialized
            setTimeout(() => {
                // Debugging logs
                // console.log('Panel Number from URL:', panelNumber);
                // console.log('Available Panels:', projectSelectionData["panels"]);

                // Try multiple methods to select the panel
                if (panelNumber) {
                    // Method 1: Direct set selected
                    $('#panelSearch').dropdown('set selected', panelNumber);

                    // Method 2: Manual selection if first method fails
                    const panelItems = $('#panelSearch .menu .item');
                    panelItems.each(function() {
                        if ($(this).data('value') === panelNumber) {
                            $('#panelSearch').dropdown('set value', panelNumber);
                            $(this).addClass('selected');
                        }
                    });

                    // Method 3: Force selection
                    $(`#panelSearch .menu .item[data-value="${panelNumber}"]`).trigger('click');
                }
            }, 100);

            // Panel dropdown change handler
            $('#panelSearch').dropdown('setting', 'onChange', function(){
                let selectedPanelNumber = ($('#panelSearch').dropdown('get value') || '').match(/^[^|]*/)[0];
                let sourceType = $('#sourceTypeSelect').dropdown('get value');
                if(!selectedPanelNumber) return;
                saveSelectedValuesToServerFilesOfProject("panel");
            });

            // Fetch project data
            const projectData = await $.ajax({
                url: '/dpm/dwc/materialsearch/api/material_segments.php',
                type: 'GET',
                data: {
                    projectFilter: projectnumber
                }
            }).catch(e => {
                console.log(e);
            });

            $("#main-loader").remove();
            $('.ui.accordion').accordion({});

            hideFullLoader();

            // Check if panel number and source type are in URL
            if(panelNumber === undefined) {
                console.log("Panel number not found in the URL.");
                return;
            }

            // Set panel value if present
            if (panelValue) {
                $('#scanPanelValue').val(panelValue);
            }
        })();
    });

    // Function to save selected values to server
    function saveSelectedValuesToServerFilesOfProject(type) {
        let data = {};
        // Check the valueToSave parameter and set the appropriate data
        if (type === "project") {
            data = {
                project: $('#projectSearchInput').val(),
                // Leave panel and station empty
            };
        } else {
            // Save all selected values
            data = {
                project: $('#projectSearchInput').val(),
                panel: $('#panelSearch').dropdown('get value'),
                station: $('#stationSearch').dropdown('get value')
            };
        }

        // console.log('Saving values:', data); // Debug log

        $.ajax({
            url: '/dpm/dwc/set_project_session.php',
            method: 'POST',
            data: data,
            success: function(response) {
                // console.log('Session updated successfully:', response);
            },
            error: function(xhr, status, error) {
                // console.error('Session update failed:', error);
            }
        });
        return false;
    }
</script>
</body>
</html>