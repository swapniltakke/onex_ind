<?php
include_once $_SERVER["DOCUMENT_ROOT"] . '/checklogin.php';
$id = getSanitizedOrDefaultData($_GET['id']);
$documentNumber = getSanitizedOrDefaultData($_GET['document-number']);
$dtoNumber = getSanitizedOrDefaultData($_GET['dto-number']);

$urlParameters = 'id=' . urlencode($id) . '&document-number=' . urlencode($documentNumber) . '&dto-number=' . urlencode($dtoNumber);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTO Configurator | Material List</title>
    <?php include_once '../../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/tkform/detail/material-list.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<!-- Sidebar -->
<?php include_once '../../../partials/tk-sidebar.php'; ?>

<!-- Main Content Area -->
<div id="materialListPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>

    <div id="materialListContainer" class="ui container-fluid display-none" style="margin-right:5%;">
        <div class="ui breadcrumb">
            <a href="/dpm/dtoconfigurator/core/tkform/index.php" class="section"><i class="clipboard outline icon"></i>TK Forms</a>
            <i class="right chevron icon divider"></i>
            <div class="active section"><i class="list icon"></i>Material List</div>
        </div>
        <br>

        <h3 id="dtoTitle" class="ui block top attached header">
            <?php echo htmlspecialchars($documentNumber); ?> / <?php echo htmlspecialchars($dtoNumber); ?>
        </h3>

        <div class="ui attached segment">
            <div id="materialListCheckMsg" class="ui red compact message hidden">
                <i class="exclamation circle icon"></i>There is no material list associated with this DTO Number could be found.
            </div>
            <div id="listAffectsOtherDtosMsg" class="ui compact message positive small hidden" style="margin-bottom:1.3rem!important;">
                <p><i class="info circle icon"></i>Lists affecting to other DTOs are displayed in green color.</p>
            </div>
            <div id="listAffectsSidePanelsMsg" class="ui compact message orange small hidden" style="margin-bottom:1.3rem!important;">
                <p><i class="info circle icon"></i>Lists affecting to side panels are displayed in orange color.</p>
            </div>
            <div id="materialNotDefinedInSapMsg" class="ui compact message red small hidden" style="margin-bottom:1.3rem!important;">
                <p><i class="info circle icon"></i>Materials not defined in the SAP system are displayed in red color.</p>
            </div>
            <!-- Material List Table -->
            <div id="materialListTableContainer" class="sixteen wide column">
                <div id="materialListTableHeader">
                    <h3 class="ui header">
                        <i class="clipboard list icon"></i>
                        <div class="content">
                            Material List
                            <div class="sub header">View the material list associated with the TK Form below</div>
                        </div>
                    </h3>
                </div>

                <table id="materialListTable" class="ui celled table striped stackable compact padded hover" style="width:100%">
                    <thead>
                    <tr>
                        <th>Order No</th>
                        <th>KMAT</th>
                        <th>Added Number</th>
                        <th>Deleted Number</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Created</th>
                        <th>Note</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- Data will be populated here by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="editTkFormModal" class="ui modal hidden">
        <i class="close icon"></i>
        <div class="header">Edit Material Form</div>
        <div class="content">
            <div class="ui icon message warning">
                <i class="exclamation circle icon"></i>
                <div class="content">
                    <div class="header" style="margin-bottom:0.6rem;">
                        Important Notice
                    </div>
                    <b>Please note that</b> changing added or deleted material numbers is not allowed, as it could interfere with works on Projects page. To make adjustments, remove the material list and add a new one with the accurate data.
                </div>
            </div>
            <div id="editTkFormModalContent" style="width:75%; margin:0 auto;">
                <form id="editTkFormModalForm" class="ui form">
                    <input type="text" id="tkMaterialId" style="display:none;">
                    <div class="field">
                        <label>Reference/Sample Project</label>
                        <select id="referenceProject" class="ui fluid selection search dropdown referenceProject">
                            <option value="">Search Project</option>
                        </select>
                    </div>

                    <div class="two fields" style="gap:10%;">
                        <div class="field" style="width:20%;">
                            <label>Starts By</label>
                            <select id="materialAddedStartsBy" disabled></select>
                        </div>
                        <div class="field" style="width:80%;">
                            <label>Added Material Number</label>
                            <select id="materialAdded" disabled></select>
                        </div>
                    </div>

                    <div class="two fields" style="gap:10%;">
                        <div class="field" style="width:20%;">
                            <label>Starts By</label>
                            <select id="materialDeletedStartsBy" disabled></select>
                        </div>
                        <div class="field" style="width:80%;">
                            <label>Deleted Material Number</label>
                            <select id="materialDeleted" disabled></select>
                        </div>
                    </div>

                    <div id="stationField" class="two fields">
                        <div class="field">
                            <label>Station Code</label>
                            <input type="text" id="stationCode" disabled>
                        </div>
                        <div class="field">
                            <label>Station Name</label>
                            <input type="text" id="stationName" disabled>
                        </div>
                    </div>

                    <div class="two fields">
                        <div class="required field">
                            <label>Quantity</label>
                            <input type="number" id="quantity" name="quantity">
                        </div>
                        <div class="required field">
                            <label>Unit</label>
                            <input type="text" id="unit" name="unit">
                        </div>
                    </div>

                    <div class="field">
                        <label>Note</label>
                        <textarea id="specialNote" name="specialNote" rows="2"></textarea>
                    </div>

                    <div class="field">
                        <label>Affected DTO Numbers</label>
                        <select id="affectedDtoNumbers" class="ui fluid search dropdown" multiple="" disabled></select>
                    </div>

                    <div class="three fields" style="text-align:center;margin-top:1.5rem;margin-bottom:1.5rem;">
                        <div class="field">
                            <div class="ui radio checkbox">
                                <input type="radio" class="changeType" name="changeType" value="Typical">
                                <label>Typical Based</label>
                            </div>
                        </div>
                        <div class="field">
                            <div class="ui radio checkbox">
                                <input type="radio" class="changeType" name="changeType" value="Panel">
                                <label>Panel Based</label>
                            </div>
                        </div>

                        <div class="field">
                            <div class="ui radio checkbox">
                                <input type="radio" class="changeType" name="changeType" value="Accessories">
                                <label>Accessory List</label>
                            </div>
                        </div>
                    </div>
                    <div class="ui checkbox">
                        <input type="checkbox" id="sidePanelEffect" style="display: flex;justify-content: center;" disabled>
                        <label>Material List Affected Side Panels</label>
                    </div>
                </form>
            </div>
        </div>
        <div class="actions" style="display:flex;justify-content:space-between;">
            <div class="ui red button delete">Delete</div>
            <div class="ui green button update" style="margin:0;">Update</div>
        </div>
    </div>


    <div id="deleteTkMaterialConfirmModal" class="ui modal hidden">
        <i class="close icon"></i>
        <div class="header">Delete Material List Confirmation</div>
        <div class="content">
            <h4>This material list also affects other DTO numbers. Please select which TK Form you want to delete it from.</h4>
            <form id="affectedDtoRadioButtonsForm" class="ui form">
                <div id="affectedDtoRadioButtonsContainer">
                    <!-- Radio buttons will be inserted here dynamically -->
                </div>
            </form>
        </div>
        <div class="actions">
            <div class="ui button cancel" style="background-color:#6e7881;color:white;">Cancel</div>
            <div class="ui button deleteTkMaterial" style="background-color:#d33;color:white;margin:0;">Delete</div>
        </div>
    </div>
</div>

<?php include_once '../../../partials/footer.php'; ?>

<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/tkform/detail/material-list.js?<?=uniqid()?>"></script>
</body>
</html>
