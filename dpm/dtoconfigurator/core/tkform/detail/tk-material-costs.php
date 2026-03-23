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
    <title>DTO Configurator | TK Material Costs</title>
    <?php include_once '../../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/tkform/detail/tk-material-costs.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<!-- Sidebar -->
<?php include_once '../../../partials/tk-sidebar.php'; ?>

<!-- Main Content Area -->
<div id="tkMaterialCostsPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>

    <div id="materialListContainer" class="ui container-fluid display-none" style="margin-right:5%;">
        <div class="ui breadcrumb">
            <a href="/dpm/dtoconfigurator/core/tkform/index.php" class="section"><i class="clipboard outline icon"></i>TK Forms</a>
            <i class="right chevron icon divider"></i>
            <div class="active section"><i class="euro sign icon"></i>TK Material Costs</div>
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
                    <div class="sixteen wide column">
                        <div class="ui grid centered">
                            <div class="ten wide column">
                                <h3 class="ui header">
                                    <i class="euro sign icon"></i>
                                    <div class="content">
                                        TK Material Costs
                                        <div class="sub header">Use this section to calculate material list costs of TK Form.</div>
                                    </div>
                                </h3>
                            </div>

                            <div class="six wide column field">
                                <div style="display: flex; justify-content: flex-end; align-items: center;">
                                    <div class="ui green big label">
                                        <span style="font-weight: bold;">Total Cost:</span>
                                        <span id="totalCostValue" style="margin-left: 10px; font-size: 1.2em;">0.00 €</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ NEW ROW FOR CHECKBOXES -->
                        <div class="ui grid centered" style="margin-top: 10px;">
                            <div class="eight wide column" style="display: flex; justify-content: center; gap: 25px;">
                                <div class="ui checkbox">
                                    <input type="checkbox" id="selectAllMaterials">
                                    <label for="selectAllMaterials" style="cursor:pointer;">Select all materials</label>
                                </div>
                                <div class="ui checkbox">
                                    <input type="checkbox" id="selectAddedMaterials">
                                    <label for="selectAddedMaterials" style="cursor:pointer;">Select only added materials</label>
                                </div>
                                <div class="ui checkbox">
                                    <input type="checkbox" id="selectDeletedMaterials">
                                    <label for="selectDeletedMaterials" style="cursor:pointer;">Select only deleted materials</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <table id="materialListTable" class="ui striped celled table" style="width:100%">
                    <thead>
                    <tr>
                        <th>Order No</th>
                        <th>KMAT</th>
                        <th>Added Number</th>
                        <th>Deleted Number</th>
                        <th>Description</th>
                        <th>Created</th>
                        <th>Note</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- Data will be populated here by JavaScript -->
                    </tbody>
                </table>
            </div>


        </div>
    </div>
</div>
<?php include_once '../../../partials/footer.php'; ?>

<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/tkform/detail/tk-material-costs.js?<?=uniqid()?>"></script>
</body>
</html>
