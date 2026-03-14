<!DOCTYPE html>
<html>
<?php
include_once 'core/index.php';
SharedManager::checkAuthToModule(14);
?>
<link href="../css/semantic.min.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="../css/dataTables.semanticui.min.css">
<link rel="stylesheet" type="text/css" href="../css/responsive.dataTables.min.css">

<link href="../css/main.css?13" rel="stylesheet"/>
<?php include_once 'shared/headerStyles.php' ?>
<?php include_once '../assemblynotes/shared/headerScripts.php' ?>

<style>
    /* Remove horizontal scrolling completely */
    * {
        box-sizing: border-box;
    }

    html, body {
        width: 100%;
        overflow-x: hidden;
    }

    .dataTables_wrapper {
        width: 100%;
        overflow-x: visible !important;
    }

    .dataTables_scrollBody {
        overflow-x: visible !important;
    }

    /* Table responsive styling */
    #table_all_items {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: collapse;
        table-layout: fixed;
    }

    #table_all_items thead {
        background-color: #f5f5f5;
    }

    #table_all_items thead th {
        font-size: 13px !important;
        font-weight: 600;
        padding: 12px 8px !important;
        border: 1px solid #ddd;
        text-align: left;
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
        color: #333;
        line-height: 1.3;
    }

    #table_all_items tbody td {
        font-size: 12px !important;
        padding: 10px 8px !important;
        border: 1px solid #ddd;
        color: #555;
        line-height: 1.3;
    }

    /* MLFB No column - single line with ellipsis */
    #table_all_items thead th:nth-child(5),
    #table_all_items tbody td:nth-child(5) {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #table_all_items tbody tr:hover {
        background-color: #f9f9f9;
    }

    #table_all_items tbody tr:nth-child(even) {
        background-color: #fafafa;
    }

    /* Column width distribution - optimized for readability */
    #table_all_items thead th:nth-child(1),
    #table_all_items tbody td:nth-child(1) { width: 6%; }

    #table_all_items thead th:nth-child(2),
    #table_all_items tbody td:nth-child(2) { width: 10%; }

    #table_all_items thead th:nth-child(3),
    #table_all_items tbody td:nth-child(3) { width: 10%; }

    #table_all_items thead th:nth-child(4),
    #table_all_items tbody td:nth-child(4) { width: 9%; }

    #table_all_items thead th:nth-child(5),
    #table_all_items tbody td:nth-child(5) { width: 12%; }

    #table_all_items thead th:nth-child(6),
    #table_all_items tbody td:nth-child(6) { width: 10%; }

    #table_all_items thead th:nth-child(7),
    #table_all_items tbody td:nth-child(7) { width: 7%; }

    #table_all_items thead th:nth-child(8),
    #table_all_items tbody td:nth-child(8) { width: 11%; }

    #table_all_items thead th:nth-child(9),
    #table_all_items tbody td:nth-child(9) { width: 8%; }

    #table_all_items thead th:nth-child(10),
    #table_all_items tbody td:nth-child(10) { width: 9%; }

    #table_all_items thead th:nth-child(11),
    #table_all_items tbody td:nth-child(11) { width: 9%; }

    /* For 1920px screens */
    @media screen and (max-width: 1920px) {
        #table_all_items thead th {
            font-size: 12px !important;
            padding: 11px 7px !important;
        }

        #table_all_items tbody td {
            font-size: 11px !important;
            padding: 9px 7px !important;
        }
    }

    /* For 1600px screens */
    @media screen and (max-width: 1600px) {
        #table_all_items thead th {
            font-size: 12px !important;
            padding: 11px 7px !important;
        }

        #table_all_items tbody td {
            font-size: 11px !important;
            padding: 9px 7px !important;
        }
    }

    /* For 1366px screens */
    @media screen and (max-width: 1366px) {
        #table_all_items thead th {
            font-size: 11px !important;
            padding: 10px 6px !important;
        }

        #table_all_items tbody td {
            font-size: 10px !important;
            padding: 8px 6px !important;
        }
    }

    /* For 1024px screens */
    @media screen and (max-width: 1024px) {
        #table_all_items thead th {
            font-size: 10px !important;
            padding: 9px 5px !important;
        }

        #table_all_items tbody td {
            font-size: 9px !important;
            padding: 7px 5px !important;
        }
    }

    /* DataTable controls styling */
    .dataTables_length {
        margin-bottom: 20px;
    }

    .dataTables_filter {
        margin-bottom: 20px;
    }

    .dataTables_length select,
    .dataTables_filter input {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 13px;
    }

    /* Pagination styling */
    .dataTables_info {
        margin-top: 20px;
        margin-bottom: 15px;
        font-size: 12px;
        color: #666;
    }

    .dataTables_paginate {
        margin-top: -20px !important;
        margin-bottom: 15px;
        font-size: 12px;
        padding: 20px 0;
    }

    .dataTables_paginate a,
    .dataTables_paginate span {
        padding: 8px 12px;
        margin: 0 3px;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        display: inline-block;
        text-decoration: none;
        color: #333;
        transition: all 0.3s ease;
    }

    .dataTables_paginate a:hover {
        background-color: #25c297;
        color: white;
        border-color: #25c297;
    }

    .dataTables_paginate .paginate_button.current,
    .dataTables_paginate .paginate_button.current:hover {
        background-color: #25c297 !important;
        color: white !important;
        border-color: #25c297 !important;
        font-weight: 600;
    }

    .dataTables_paginate .paginate_button.disabled,
    .dataTables_paginate .paginate_button.disabled:hover {
        background-color: #f5f5f5;
        color: #999;
        cursor: not-allowed;
        border-color: #ddd;
    }

    .dataTables_paginate .ellipsis {
        padding: 8px 6px;
        border: none;
        cursor: default;
    }

    /* Zoom classes - maintain readability */
    body.zoom-125 #table_all_items thead th {
        font-size: 11px !important;
        padding: 10px 6px !important;
    }

    body.zoom-125 #table_all_items tbody td {
        font-size: 10px !important;
        padding: 8px 6px !important;
    }

    body.zoom-150 #table_all_items thead th {
        font-size: 10px !important;
        padding: 9px 5px !important;
    }

    body.zoom-150 #table_all_items tbody td {
        font-size: 9px !important;
        padding: 7px 5px !important;
    }

    body.zoom-175 #table_all_items thead th {
        font-size: 9px !important;
        padding: 8px 4px !important;
    }

    body.zoom-175 #table_all_items tbody td {
        font-size: 8px !important;
        padding: 6px 4px !important;
    }

    body.zoom-200 #table_all_items thead th {
        font-size: 8px !important;
        padding: 7px 3px !important;
    }

    body.zoom-200 #table_all_items tbody td {
        font-size: 7px !important;
        padding: 5px 3px !important;
    }

    /* Ensure no overflow */
    #controlpanel {
        width: 100%;
        overflow: visible !important;
        padding: 0;
        margin: 0;
    }

    .ibox-content {
        overflow: visible !important;
    }

    .ui.grid {
        margin: -1 !important;
        padding: 0 !important;
    }

    .ui.grid > .row {
        padding: 0 !important;
    }

    .ui.grid > .column {
        padding: 0 !important;
    }

    /* Tooltip for MLFB No on hover */
    #table_all_items tbody td:nth-child(5) {
        cursor: help;
    }

    #table_all_items tbody td:nth-child(5):hover {
        background-color: #e8f4f8;
    }

    /* Minimal footer spacing */
    #page-wrapper {
        padding-bottom: 0;
    }

    /* Wrapper for table container */
    .ibox {
        margin-bottom: 0;
    }
</style>

<body>
<div id="wrapper">
    <?php $activePage = '/dpm/report_viewer.php'; ?>
    <?php include_once 'shared/sidebar.php' ?>
    <div id="page-wrapper" class="white-bg">
        <div class="row border-bottom" style="position: relative;">
            <div class="ui fixed menu" style="padding: 21px; color:teal; width: 100%;">
                <div class="ui container" style="position: relative; width: 100%;">
                    <div style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); display: flex; align-items: center;">
                        <a href="/" style="display: flex; align-items: center; text-decoration: none;">
                            <div style="margin-right: 10px;">
                                <img src="/images/onex_icon.png" width="25" height="36" class="logo-icon">
                            </div>
                            <div class="logo-text">
                                <h5 style="margin: 0; font-size: 18px; line-height: 0.2;">
                                    <sup class="badge badge-danger" style="font-size: 0.4em; background-color: #dc3545; color: white; padding: 0.2em 0.3em; border-radius: 0.25rem; vertical-align: super;">OneX</sup>
                                </h5>
                                <p style="margin: 0; text-transform: uppercase; font-size: 10px; color: #6c757d; line-height: 1.2;">Order Tracking System</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox mb-0">
                    <div id="headersegment">
                        <div class="ibox-content text-center"
                             style="padding-bottom:15px; display: flex;justify-content: space-between; margin-top: 50px;">
                            <div class="input-daterange d-flex" id="datepicker"
                                 style="width: unset !important;">
                                <div id="reportrange" style="">
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span></span> <i class="fa fa-caret-down"></i>
                                </div>
                                <button class="btn btn-primary ml-3" onclick="filterOpenListByDate()" type="button"><i
                                        class="fa fa-check"></i>&nbsp;Date Filter
                                </button>
                            </div>
                        </div>
                        <div id="detailsegment" style="display: none;">
                            <div class="ui inverted dimmer" id="mai_spinner_page">
                                <div class="ui loader"></div>
                            </div>
                            <div class="one column center aligned padded ui grid">
                                <div class="row" style="height:100%;margin-top:2%;">
                                    <div class="column" id="controlpanel">
                                        <table id='table_all_items'
                                               class="ui celled very small compact responsive table dataTable no-footer">
                                            <thead class="scrollable-thead">
                                                <tr>
                                                    <th>Sr.No.</th>
                                                    <th>Work Station</th>
                                                    <th>Product Name</th>
                                                    <th>Serial No.</th>
                                                    <th>MLFB No.</th>
                                                    <th>Sales Order No.</th>
                                                    <th>Item No.</th>
                                                    <th>Production Order No.</th>
                                                    <th>User</th>
                                                    <th>Start Time</th>
                                                    <th>End Time</th>
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
        <?php $footer_display = 'Breaker Status';
        include_once '../assemblynotes/shared/footer.php'; ?>
    </div>
</div>

<!-- Mainly scripts -->
<?php include_once '../assemblynotes/shared/headerSemanticScripts.php' ?>
<script src="shared/shared.js"></script>
<script src="reports/allReports.js?<?php echo rand(); ?>"></script>

<script>
    // Detect and apply zoom level
    function detectAndApplyZoom() {
        var zoomLevel = Math.round((window.outerWidth / window.innerWidth) * 100);
        var body = document.body;

        // Remove all zoom classes
        body.classList.remove('zoom-100', 'zoom-125', 'zoom-150', 'zoom-175', 'zoom-200');

        // Add appropriate zoom class
        if (zoomLevel >= 200) {
            body.classList.add('zoom-200');
        } else if (zoomLevel >= 175) {
            body.classList.add('zoom-175');
        } else if (zoomLevel >= 150) {
            body.classList.add('zoom-150');
        } else if (zoomLevel >= 125) {
            body.classList.add('zoom-125');
        } else {
            body.classList.add('zoom-100');
        }
    }

    // Apply zoom on load
    window.addEventListener('load', function() {
        detectAndApplyZoom();
    });

    // Apply zoom on resize (includes zoom changes)
    window.addEventListener('resize', function() {
        detectAndApplyZoom();
    });

    // Check zoom periodically
    setInterval(detectAndApplyZoom, 1000);

    // Adjust table on data load
    $(document).ready(function() {
        // Watch for DataTable initialization
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    detectAndApplyZoom();
                    
                    // Add title attribute to MLFB cells for tooltip
                    var mlfbCells = document.querySelectorAll('#table_all_items tbody td:nth-child(5)');
                    mlfbCells.forEach(function(cell) {
                        if (cell.textContent.trim()) {
                            cell.setAttribute('title', cell.textContent.trim());
                        }
                    });
                }
            });
        });

        var tableElement = document.getElementById('table_all_items');
        if (tableElement) {
            observer.observe(tableElement, {
                childList: true,
                subtree: true
            });
        }

        // Initial tooltip setup
        var mlfbCells = document.querySelectorAll('#table_all_items tbody td:nth-child(5)');
        mlfbCells.forEach(function(cell) {
            if (cell.textContent.trim()) {
                cell.setAttribute('title', cell.textContent.trim());
            }
        });
    });
</script>
</body>
</html>