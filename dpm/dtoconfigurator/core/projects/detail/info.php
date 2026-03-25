<?php
SharedManager::checkAuthToModule(24);
include_once $_SERVER["DOCUMENT_ROOT"] . '/dpm/core/index.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/checklogin.php';
$projectNo = getSanitizedOrDefaultData($_GET['project-no']);
SharedManager::saveLog("log_project_detail", "View for Project: $projectNo");
$currentUser = isset($_SESSION['username']) ? $_SESSION['username'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTO Configurator | <?php echo $projectNo; ?></title>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta charset="utf-8">
    <?php include_once '../../../../shared/headerStyles.php' ?>
    <?php include_once '../../../partials/libraries.php'; ?>
    <link href="../../../assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="../../../assets/css/projects/detail/info.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<!-- Main Content Area -->
<div id="wrapper">
    <!-- Header -->
    <?php $activePage = '/info.php'; ?>
    <?php include_once '../../../../shared/dto_sidebar.php'; ?>
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom" style="position: relative; margin: 0;">
            <div class="ui fixed menu" style="padding: 21px; color:teal; width: 100%;">
                <div class="ui container" style="position: relative; width: 100%;">
                    <div style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); display: flex; align-items: center;">
                        <a href="/" style="display: flex; align-items: center; text-decoration: none;">
                            <div style="margin-right: 10px;">
                                <img src="/images/onex_icon.png" width="25" height="36" class="logo-icon">
                            </div>
                            <div class="logo-text">
                                <h5 style="margin: 0; font-size: 18px; line-height: 1.2;">
                                    DTO Configurator <sup class="badge badge-danger" style="font-size: 0.4em; background-color: #dc3545; color: white; padding: 0.2em 0.3em; border-radius: 0.25rem; vertical-align: super;">OneX</sup>
                                </h5>
                                <p style="margin: 0; text-transform: uppercase; font-size: 10px; color: #6c757d; line-height: 1.2;">Web system for configuring DTOs</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="ui active centered inline loader" style="margin-top:10%;"></div>

        <div id="projectPageErrorDiv" class="ui red compact message" style="display:none;">
            <i class="info circle icon"></i><span id="projectPageErrorMsg"></span>
        </div>

        <div id="projectPageContainer" class="ui grid container-fluid" style="display:none;padding-right:2%;">

            <!-- Project Page Header -->
            <div id="projectInfoLabels" class="twelve wide column" style="display: flex; align-items: center; gap: 10px;">
                <div class="ui blue basic inverted label large">
                    <span id="downloadProjectFolder"></span>
                    <span id="projectNumberLabel"></span> -
                    <span id="projectNameLabel"></span>
                </div>

                <div class="ui green basic label" data-tooltip="Order Manager" data-position="top left">
                    <i class="mail icon"></i> <span id="orderManagerLabel"></span>
                </div>

                <div class="ui violet image basic label item" style="display: flex; align-items: center; gap: 5px;">
                    <i class="plug icon"></i>
                    <span id="productLabel" data-tooltip="Product Type" data-position="top left"></span>
                    <div id="panelCountLabel" class="floating ui violet label" data-tooltip="Panel Count" data-position="top left"></div>
                </div>

                <div id="packTypeDiv" class="ui violet basic label" data-tooltip="Pack Type" data-position="top left" style="display:none;">
                    <i class="archive icon"></i> <span id="packTypeLabel"></span>
                </div>

                <div id="assemblyStartDate" class="ui red basic label" data-tooltip="Assembly Start Date" data-position="top left">
                    <i class="calendar icon"></i> <span id="assemblyStartDateLabel"></span>
                </div>

                <div id="assemblyStartRemainingWeek" class="ui red basic label" data-tooltip="Assembly Start Remaining" data-position="top left">
                    <i class="calendar icon"></i> <span id="assemblyStartRemainingWeekLabel"></span>
                </div>

                <div class="ui brown basic label" data-tooltip="Rated Voltage" data-position="top left">
                    <i class="lightbulb icon"></i> <span id="ratedVoltageLabel"></span>
                </div>

                <div class="ui brown basic label" data-tooltip="Rated Short Circuit" data-position="top left">
                    <i class="lightning icon"></i> <span id="ratedShortCircuitLabel"></span>
                </div>

                <div class="ui brown basic label" data-tooltip="Rated Current" data-position="top left">
                    <i class="battery full icon"></i> <span id="ratedCurrentLabel"></span>
                </div>
            </div>

            <div class="four wide column right aligned">
                <select id="searchProjectSelect" name="searchProjectSelect" class="ui fluid selection search dropdown searchProjectSelect">
                    <option value="">Search Project</option>
                </select>
            </div>

            <div id="projectDataTabsInfoMessage" class="sixteen wide column" style="display:none;">
                <div class="ui icon message warning" style="width:40%;">
                    <i class="hand point down outline icon"></i>
                    <div class="content">
                        <div class="header" style="margin-bottom:0.4rem;">
                            Quick Tip
                        </div>
                        <span><b>Choose a Nachbau File below to see the details of project.</b></span>
                    </div>
                </div>
            </div>

            <!-- Project Status Messages (Pending, Published, Rejected, etc.) -->
            <div id="projectStatusMessageDiv" style="margin-bottom: 1.3rem; display:none;">
                <div class="ui icon message positive">
                    <i class="wrench icon"></i>
                    <div class="content">
                        <div class="header" style="margin-bottom:0.4rem;">
                            Project current status is: <span class="projectStatusName"></span>
                        </div>
                        <span id="pendingApprovalStatusMsg" style="display:none;">
                            This project was sent for approval on <b><span class="sendReviewDate"></span></b> by <b><span class="whoSentApproval"></span></b>.
                        </span>
                        <span id="rejectedStatusMsg" style="display:none;">
                            Approval request is rejected on <b><span class="reviewedDate"></span></b> by <b><span class="whoSentApproval"></span></b>. <br>
                            Check for more details.
                        </span>
                        <span id="publishedStatusMsg" style="display:none;">
                            The project was released on <b><span class="reviewedDate"></span></b> by <b><span class="whoSentApproval"></span></b>.
                        </span>
                        <span id="pendingRevisionApprovalStatusMsg" style="display:none;">
                            This project revisions were sent for approval on <b><span class="sendReviewDate"></span></b> by <b><span class="whoSentApproval"></span></b>.
                        </span>
                        <span id="revisionPublishedStatusMsg" style="display:none;">
                            The project revisions were released on <b><span class="reviewedDate"></span></b> by <b><span class="whoSentApproval"></span></b>.
                        </span>
                    </div>
                </div>
                <div id="withdrawPublishRequest" class="ui red circular icon compact button" style="display:none;">
                    <i class="reply icon"></i>
                    Withdraw Publish Request
                </div>
            </div>

            <!-- Working Status Messages (Someone is working OR Under Revision) -->
            <div id="projectWorkingStatusInfo" style="margin-bottom: 1.3rem; display:none;">
                <div class="ui icon message warning">
                    <i class="wrench icon"></i>
                    <div class="content">
                        <div class="header" style="margin-bottom:0.5rem;">
                            <span id="workingStatusHeader"></span>
                        </div>

                        <!-- Someone is currently working -->
                        <span id="currentlyWorkingUserInfoMessage" style="display:none;">
                            <b><span id="currentlyWorkingUser"></span></b> is currently working on this Nachbau file. To avoid conflicts, editing project works is temporarily disabled.
                        </span>

                        <!-- Under Revision -->
                        <span id="projectRevisionStatusTabMessage" style="display:none;">
                            <b><span id="revisionWorkingUser"></span></b> is working on the <b>revision</b> in this nachbau.
                            Click the button below to view the <b>not transferred works</b> from the <b>previous nachbau transfer.</b> <br>
                        </span>
                    </div>
                </div>
            </div>

            <div id="transferSummaryButtonDiv" style="margin-top: 0.1rem;margin-bottom:1rem;width:100%;display:none;">
                <button id="transferSummaryButton" class="ui inverted button brown small">
                    <i class="eye icon"></i>
                    Transfer Summary
                </button>
            </div>

            <!-- No DTO Number for this project -->
            <div id="nachbauErrorMsgDiv" style="display:none;">
                <div class="ui placeholder segment">
                    <div id="nachbauErrorMsg" class="ui icon header"></div>
                </div>
            </div>

            <!-- Nachbau Data is available for this project -->
            <div id="nachbauFileExistsOrNot">
                <div class="ui placeholder segment">
                    <div id="nachbauFileMsg" class="ui icon header"></div>
                </div>
            </div>

            <div class="ui grid" style="width:100%!important;">
                <!-- Row for Nachbau Data -->
                <div class="row">
                    <div class="sixteen wide column">
                        <!-- Nachbau Filter -->
                        <?php include_once '../../projects/detail/nachbau-data.php'; ?>
                    </div>
                </div>
                <div id="checkInButton" class="ui green circular icon compact button" data-position="top left" style="display:none;margin-left: 1.1rem;margin-top: 0.5rem;"
                    data-tooltip="Lock this project and start working on it.">
                    <i class="lock icon"></i>
                    Check In Project
                </div>
                <!-- Row for Project Data Tabs -->
                <div id="projectDataTabsRow" class="row">
                    <div class="sixteen wide column">
                        <div class="ui active inline indeterminate text loader" style="margin-top:10%;"><b>Loading Project Data</b></div>

                        <div id="projectDataTabs" style="display:none !important;">
                            <div id="projectDataTabularMenu" class="ui top attached tabular menu">
                                <a class="active item" data-tab="kukoMatrix"><i class="plus square icon"></i>KuKo Matrix</a>
                                <a class="item" data-tab="projectWork"><i class="pencil alternate icon"></i>Project Work</a>
                                <a class="item" data-tab="jtCollection"><i class="dropbox icon"></i>JT Collection</a>
                                <a class="item" data-tab="projectNcList"> <span id="projectNcListCount"></span> NC</a>
                                <a class="item" data-tab="nachbauOperations"><i class="exchange icon"></i>Nachbau Operations</a>
                                <a class="item" data-tab="orderSummaryV2"><i class="th list icon"></i>Order Summary</a>
                                <a class="item" data-tab="bomNotes"><i class="sticky note outline icon"></i>BOM Notes</a>
                                <a class="item" data-tab="checklist"><i class="check square outline icon"></i>Checklist</a>
                                <a class="item" data-tab="publishProject"><i class="rocket icon"></i>Publish Project</a>
                            </div>
                            <div id="kukoMatrix" class="ui bottom active attached tab segment" style="display:none;" data-tab="kukoMatrix">
                                <?php include_once '../../projects/detail/tabs/kuko-matrix.php'; ?>
                            </div>
                            <div id="projectWork" class="ui bottom attached tab segment" style="display:none;" data-tab="projectWork">
                                <?php include_once '../../projects/detail/tabs/project-work/project-work.php'; ?>
                            </div>
                            <div id="jtCollection" class="ui bottom attached tab segment" style="display:none;" data-tab="jtCollection">
                                <?php include_once '../../projects/detail/tabs/jt-collection.php'; ?>
                            </div>
                            <!-- <div id="projectNcList" class="ui bottom attached tab segment" style="display:none;" data-tab="projectNcList">
                                <?php // include_once '../../projects/detail/tabs/nc-list.php'; ?>
                            </div> -->
                            <div id="nachbauOperations" class="ui bottom attached tab segment" style="display:none;" data-tab="nachbauOperations">
                                <?php include_once '../../projects/detail/tabs/nachbau-operations.php'; ?>
                            </div>
                            <div id="orderSummaryV2" class="ui bottom attached tab segment" style="display:none;" data-tab="orderSummaryV2">
                                <?php include_once '../../projects/detail/tabs/order-summary/order-summary-v2.php'; ?>
                            </div>
                            <div id="orderSummary" class="ui bottom attached tab segment" style="display:none;" data-tab="orderSummary">
                                <?php include_once '../../projects/detail/tabs/order-summary/order-summary.php'; ?>
                            </div>
                            <div id="bomNotes" class="ui bottom attached tab segment" style="display:none;" data-tab="bomNotes">
                                <?php include_once '../../projects/detail/tabs/bom-notes/bom-notes.php'; ?>
                            </div>
                            <div id="checklist" class="ui bottom attached tab segment" data-tab="checklist">
                                <?php include_once '../../projects/detail/tabs/checklist.php'; ?>
                            </div>
                            <div id="publishProject" class="ui bottom attached tab segment" style="display:none;" data-tab="publishProject">
                                <?php include_once '../../projects/detail/tabs/publish-project.php'; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php $footer_display = 'Project Detail';
    include_once '../../../../../assemblynotes/shared/footer.php'; ?>
</div>

<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/projects/detail/info.js?<?=uniqid()?>"></script>
</body>
</html>