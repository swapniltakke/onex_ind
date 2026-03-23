<?php
include_once $_SERVER["DOCUMENT_ROOT"] . '/checklogin.php';
include_once '../../api/models/Journals.php';

SharedManager::checkAuthToModule(35);
Journals::saveJournal('Accessing Admin Page', PAGE_ADMIN, ADMIN_ORDER_CHANGES, ACTION_PROCESSING, null, 'Admin Order Changes');
SharedManager::saveLog('log_dtoconfigurator', 'Accessing Order Changes Page');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTO Configurator | Admin Order Change</title>
    <?php include_once '../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/admin/order-changes.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<!-- Sidebar -->
<?php include_once '../../partials/sidebar.php'; ?>

<!-- Main Content Area -->
<div id="adminOrderChangesPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>

    <div id="adminOrderChangesPageContainer" class="ui container-fluid" style="padding-right:5%;display:none;">
        <div class="ui styled accordion" style="width: 100%;">
            <div class="title active">
                <i class="dropdown icon"></i>
                <h4 class="ui top attached block" style="margin: 0; display: inline-block;">
                    Project Details
                </h4>
            </div>
            <div class="content active">
                <div class="ui bottom attached segment" style="padding: 20px;background:#f5f5dc2e;">
                    <div class="project-details-menu">
                        <div class="project-details-menu-item">
                            <strong>Project No</strong><br>
                            <div class="ui blue basic label">
                                <span class="project-number"></span>
                            </div>
                        </div>
                        <div class="project-details-menu-item">
                            <strong>Project Name</strong><br>
                            <div class="ui blue basic label">
                                <span class="project-name"></span>
                            </div>
                        </div>
                        <div class="project-details-menu-item">
                            <strong>Nachbau No</strong><br>
                            <div class="ui violet basic label">
                                <span class="nachbau-number"></span>
                            </div>
                        </div>
                        <div class="project-details-menu-item">
                            <strong>Nachbau Date</strong><br>
                            <div class="ui violet basic label">
                                <span class="nachbau-date"></span>
                            </div>
                        </div>
                        <div class="project-details-menu-item">
                            <strong>Product</strong><br>
                            <div class="ui green basic label">
                                <span class="product"></span>
                            </div>
                        </div>
                        <div class="project-details-menu-item">
                            <strong>Assembly Start</strong><br>
                            <div class="ui red basic label">
                                <span class="assembly-start-date"></span>
                            </div>
                        </div>
                        <div class="project-details-menu-item">
                            <strong>Panel Qty</strong><br>
                            <div class="ui brown basic label">
                                <span class="panel-qty"></span>
                            </div>
                        </div>
                        <div class="project-details-menu-item">
                            <strong>Submitted By</strong><br>
                            <div class="ui teal basic label">
                                <span class="submitted-by"></span>
                            </div>
                        </div>
                        <div class="project-details-menu-item">
                            <strong>Submitted Date</strong><br>
                            <div class="ui teal basic label">
                                <span class="submitted-date"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revision Info -->
        <div id="project-revision-info" style="text-align:center;display:none;">
            <div class="ui icon message warning" style="display: flex; max-width: 850px; text-align: left;margin: 1.2rem auto -1rem auto;">
                <i class="info circle icon"></i>
                <div class="content">
                    <div class="header">Important: Revision Request</div>
                    <p style="line-height:1.6rem;">
                        This publish request includes <b>revision data</b>. Please <b>review</b> the revision changes carefully before approving.
                    </p>
                </div>
            </div>
        </div>

        <!-- Project Current Status -->
        <div id="project-current-status" class="ui column grid" style="width:20%; margin: 0 auto;margin-top:2rem!important;" data-tooltip="Project Current Status" data-position="top center">
            <div class="ui steps" style="width:100%;">
                <div class="step-class completed step">
                    <i class="icon-class payment icon"></i>
                    <div class="content">
                        <div class="title"></div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Status Action Buttons -->
        <div id="status-action-buttons" class="ui column grid" style="margin: 0 auto;margin-top:2rem!important;display:none">
            <div class="ui center aligned container">
                <button id="approve-btn" class="ui green button">
                    <i class="check icon"></i>
                    Approve
                </button>
                <button id="reject-btn" class="ui red button">
                    <i class="ban icon"></i>
                    Reject
                </button>
            </div>
        </div>

        <!-- Approve Modal -->
        <div id="approve-modal" class="ui small modal">
            <div class="header">
                <i class="check circle icon green"></i>
                Approve Project
            </div>
            <div class="content">
                <div class="ui form">
                    <div class="field">
                        <label>Approval Note</label>
                        <textarea id="approve-note" placeholder="Enter your approval note here..." rows="4"></textarea>
                    </div>
                </div>
            </div>
            <div class="actions">
                <div class="ui cancel button">
                    <i class="remove icon"></i>
                    Cancel
                </div>
                <div class="ui green ok button">
                    <i class="checkmark icon"></i>
                    Confirm Approval
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div id="reject-modal" class="ui small modal">
            <div class="header">
                <i class="ban icon red"></i>
                Reject Project
            </div>
            <div class="content">
                <div class="ui form">
                    <div class="field">
                        <label>Rejection Reason <span style="color: red;">*</span></label>
                        <textarea id="reject-note" placeholder="Please provide a reason for rejection..." rows="4" required></textarea>
                    </div>
                </div>
            </div>
            <div class="actions">
                <div class="ui cancel button">
                    <i class="remove icon"></i>
                    Cancel
                </div>
                <div class="ui red ok button">
                    <i class="checkmark icon"></i>
                    Confirm Rejection
                </div>
            </div>
        </div>

        <!-- Status Display -->
        <div id="status-message" class="ui message">
            <div class="header"></div>
            <p></p>
        </div>

        <!-- Kuko Matrix Section -->
        <div id="kukoMatrixSection" style="margin-top: 2rem; ">
            <h2 class="ui horizontal divider header" style="margin: 2.5rem auto;">
                <i class="table icon"></i>
                Kuko Matrix
            </h2>

            <!-- Info Messages -->
            <div id="kukoMatrixMessages" style="margin-bottom: 1rem;">
                <div id="kukoMatrixDtoNotWorkMsg" class="ui negative message" style="display:none;">
                    <i class="exclamation triangle icon"></i>
                    Red rows: DTOs excluded from all teams
                </div>
                <div id="kukoMatrixDtoMechanicNotWorkMsg" class="ui warning message" style="display:none;">
                    <i class="warning icon"></i>
                    Yellow rows: DTOs excluded from mechanical team
                </div>
                <div id="spareInterchangeBothExistMsg" class="ui info message" style="display:none;">
                    <i class="info circle icon"></i>
                    This project contains both spare and interchange DTOs
                </div>
            </div>

            <!-- Loader -->
            <div id="kukoMatrixLoader" class="ui active centered inline loader"></div>

            <!-- Matrix Table Container -->
            <div id="kukoMatrixContainer" class="sixteen wide column" style="display: none; overflow-x: auto;">
                <div id="kukoMatrixNotFoundMsg" class="ui warning message" style="display:none;">
                    <i class="info circle icon"></i>
                    No Kuko Matrix data available for this project.
                </div>

                <div id="kukoMatrixTableDiv">
                    <table id="kukoMatrixTable" class="ui striped celled table stackable compact selectable padded hover">
                        <thead></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- DTO Full Description Modal -->
        <div id="dtoFullDescriptionModal" class="ui mini modal">
            <div class="header"></div>
            <div class="scrolling content"></div>
        </div>

        <h2 class="ui horizontal divider header" style="margin:2.5rem auto;">
            Order Changes
        </h2>

        <div id="releasedOrderChangesTableContainer" class="sixteen wide column">
            <table id="releasedOrderChangesTable" class="ui celled table striped stackable compact padded hover" style="width:100%">
                <thead>
                <tr>
                    <th>Pos</th>
                    <th>Added Nr.</th>
                    <th>Deleted Nr.</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Description</th>
                    <th>Typical</th>
                    <th>Panel</th>
                    <th>Accs</th>
                    <th>Note</th>
                    <th>Revision</th>
                </tr>
                </thead>
                <tbody>
                    <!-- Data will be populated here by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/admin/order-changes.js?<?=uniqid()?>"></script>
</body>
</html>
