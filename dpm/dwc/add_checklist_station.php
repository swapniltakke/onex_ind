<!DOCTYPE html>
<html>
<?php
SharedManager::checkAuthToModule(10);
include_once '../core/index.php';
include_once '/api/checklistAPI.php';
$menu_header_display = 'Add Station';
?>
<head>
    <title>OneX | Add Station</title>
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
    .required-field {
        color: red;
    }
</style>
<body>
<div id="wrapper">
    <?php $activePage = '/add_checklist_station.php'; ?>
    <?php include_once '../shared/sidebar.php'; ?>
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom" style="position: relative;">
            <div class="ui fixed menu" style="padding: 21px; color:teal; width: 100%;">
                <div class="ui container" style="position: relative; width: 100%;">
                    <div style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); display: flex; align-items: center;">
                        <a href="/" style="display: flex; align-items: center; text-decoration: none;">
                            <div style="margin-right: 10px;">
                                <img src="/images/onex_icon.png" width="25" height="36" class="logo-icon">
                            </div>
                            <div class="logo-text">
                                <h5 style="margin: 0; font-size: 18px; line-height: 1.2;">
                                    DWC <sup class="badge badge-danger" style="font-size: 0.4em; background-color: #dc3545; color: white; padding: 0.2em 0.3em; border-radius: 0.25rem; vertical-align: super;">OneX</sup>
                                </h5>
                                <p style="margin: 0; text-transform: uppercase; font-size: 10px; color: #6c757d; line-height: 1.2;">Digital Work Center</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="ui inverted segment full-loader" style="display: flex">
            <div class="ui active inverted loader">Loading</div>
        </div>
        <div class="ui fluid" style='margin-top: 3em;'>
            <div class="ui grid" style="width: 100%; margin: 0;">
                <div class="sixteen wide column" style="padding: 0;">
                    <div class="ui segment middle aligned" style="padding-right: 1px;">
                        <div class="ui three column grid" style="width: 100.5%;">
                            <div class="column">
                                <div id="insert_data_station" style="display:block;">
                                    <form id="addStationForm" method="post" style="display: flex; align-items: center;">
                                        <label for="stationName" style="margin-right: 10px;"><span class="required-field">*</span>Station Name:</label>
                                        <div class="ui input fluid" style="width: 290px; margin-right: 10px;">
                                            <input type="text" class="form-control" id="stationName" name="stationName">&nbsp;&nbsp;
                                            <button type="submit" class="btn btn-primary">Save</button>
                                        </div>
                                    </form>
                                </div>
                                <div id="edit_data_station" style="display:none;">
                                    <form id="editStationForm" method="post" style="display: flex; align-items: center;">
                                        <input type="hidden" id="stationIdEdit" name="stationIdEdit" value="">
                                        <label for="stationName" style="margin-right: 10px;"><span class="required-field">*</span>Station Name:</label>
                                        <div class="ui input fluid" style="width: 290px; margin-right: 10px;">
                                            <input type="text" class="form-control" id="stationNameEdit" name="stationNameEdit">&nbsp;&nbsp;
                                            <button type="submit" class="btn btn-primary">Update</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </br>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Added Station Names</h3>
                    </div>
                    <div class="card-body">
                        <div id="stationsTable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $footer_display = 'Add Station';
    include_once '../../assemblynotes/shared/footer.php'; ?>
</div>

<!-- Mainly scripts -->
<?php include_once '../../assemblynotes/shared/headerSemanticScripts.php' ?>
<script src="../../shared/shared.js"></script>
<script>
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

    // Function to fetch and display the stations
    function fetchAndDisplayStations(page = 1) {
        showFullLoader();
        activeAjaxRequests++;

        $.ajax({
            url: '/dpm/dwc/api/checklistAPI.php?type=view_station',
            type: 'POST',
            data: { page: page },
            success: function(response) {
                $('#stationsTable').html(response);
                activeAjaxRequests--;
                if (activeAjaxRequests === 0) {
                    hideFullLoader();
                }
            },
            error: function() {
                activeAjaxRequests--;
                if (activeAjaxRequests === 0) {
                    hideFullLoader();
                }
            }
        });
    }

    // Fetch and display the stations on page load
    fetchAndDisplayStations();

    // Function to check if a station name already exists
    function isStationNameExists(stationName, callback) {
        showFullLoader();
        activeAjaxRequests++;

        $.ajax({
            url: '/dpm/dwc/api/checklistAPI.php?type=check_station_name',
            type: 'POST',
            data: { stationName: stationName },
            success: function(response) {
                callback(response);
                activeAjaxRequests--;
                if (activeAjaxRequests === 0) {
                    hideFullLoader();
                }
            },
            error: function() {
                activeAjaxRequests--;
                if (activeAjaxRequests === 0) {
                    hideFullLoader();
                }
            }
        });
    }
    
    // Handle the form submission
    $('#addStationForm').submit(function(e) {
        e.preventDefault();
        const stationName = $('#stationName').val().trim();

        if (stationName === '') {
            alert('Station name cannot be blank.');
            return;
        }

        isStationNameExists(stationName, function(exists) {
            if (exists != 'false') {
                alert('Station name already exists. Please enter a unique name.');
            } else {
                showFullLoader();
                activeAjaxRequests++;

                $.ajax({
                    url: '/dpm/dwc/api/checklistAPI.php?type=insert_station',
                    type: 'POST',
                    data: { stationName: stationName },
                    success: function(response) {
                        if (response === 'Station saved successfully') {
                            alert('Station saved successfully!');
                        } else {
                            alert('Error while saving station');
                        }
                        fetchAndDisplayStations(); // Refresh the stations table
                        $('#stationName').val(''); // Clear the input field
                        activeAjaxRequests--;
                        $('#insert_data_station').show();
                        if (activeAjaxRequests === 0) {
                            hideFullLoader();
                        }
                    },
                    error: function() {
                        alert('Error while saving station');
                        activeAjaxRequests--;
                        if (activeAjaxRequests === 0) {
                            hideFullLoader();
                        }
                    }
                });
            }
        });
    });

    // Function to populate the input field with the selected station name
    function editStation(stationId, stationName) {
        $('#stationIdEdit').val(stationId);
        $('#stationNameEdit').val(stationName);
        $('#insert_data_station').hide();
        $('#edit_data_station').show();
    }

    // Handle the edit station form submission
    $('#editStationForm').submit(function(e) {
        e.preventDefault();
        const stationId = $('#stationIdEdit').val();
        const stationName = $('#stationNameEdit').val().trim();

        if (stationName === '') {
            alert('Station name cannot be blank.');
            return;
        }

        isStationNameExists(stationName, function(exists) {
            if (exists != 'false' && exists.id !== parseInt(stationId)) {
                alert('Station name already exists. Please enter a unique name.');
            } else {
                showFullLoader();
                activeAjaxRequests++;

                $.ajax({
                    url: '/dpm/dwc/api/checklistAPI.php?type=update_station',
                    type: 'POST',
                    data: { stationId: stationId, stationName: stationName },
                    success: function(response) {
                        if (response === 'success') {
                            alert('Station updated successfully!');
                            fetchAndDisplayStations(); // Refresh the stations table
                            $('#stationIdEdit').val('');
                            $('#stationNameEdit').val('');
                            $('#insert_data_station').show();
                            $('#edit_data_station').hide();
                        } else {
                            alert('Error while updating station');
                        }
                        activeAjaxRequests--;
                        if (activeAjaxRequests === 0) {
                            hideFullLoader();
                        }
                    },
                    error: function() {
                        alert('Error while updating station');
                        activeAjaxRequests--;
                        if (activeAjaxRequests === 0) {
                            hideFullLoader();
                        }
                    }
                });
            }
        });
    });

    // Initially, show the add station form and hide the edit station form
    $('#insert_data_station').show();
    $('#edit_data_station').hide();
</script>
</body>
</html>