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
    <title>DTO Configurator | Add Material List</title>
    <?php include_once '../../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/tkform/detail/add-material-list.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<!-- Sidebar -->
<?php include_once '../../../partials/tk-sidebar.php'; ?>
<!-- Main Content Area -->
<div id="addMaterialListPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>

    <div id="addMaterialListContainer" class="ui container-fluid display-none" style="margin-right:5%;">
        <div class="ui breadcrumb">
            <a href="/dpm/dtoconfigurator/core/tkform/index.php" class="section"><i class="clipboard outline icon"></i>TK Forms</a>
            <i class="right chevron icon divider"></i>
            <div class="active section"><i class="plus square icon"></i>Add Material List</div>
        </div>
        <br>
        <h3 id="dtoTitle" class="ui block top attached header">
            <?php echo htmlspecialchars($documentNumber); ?> / <?php echo htmlspecialchars($dtoNumber); ?>
        </h3>

        <div class="ui top attached tabular menu">
            <div class="active item" data-tab="addMaterialList"><i class="plus square icon"></i>Add Material List</div>
        </div>
        <div class="ui bottom attached active tab segment" data-tab="addMaterialList">
            <h3 class="ui header" style="margin-top:0.5rem;">
                <i class="wpforms icon"></i>
                <div class="content">
                    Add Material List Form
                    <div class="sub header">The form below allows you to add material lists into TK Forms for use on the Projects page</div>
                </div>
            </h3>
            <div id="addMaterialSuccessMsg" class="ui positive message" style="display:none;">
                <i class="close icon"></i>
                <div class="header">
                    <i class="thumbs up icon"></i>Changes applied!
                </div>
                <p>Material list <b>successfully</b> added to the TK Form.</p>
            </div>
            <div id="addMaterialListTabContent" class="ui grid">
                <!-- Left Section -->
                <div class="four wide column">
                    <div class="ui segment">
                        <div class="ui form">
                            <div class="grouped fields">
                                <div class="ui pointing below red basic label hidden changeTypeErrMsg">Choose at least one list type!</div>
                                <label style="display:block;">List Type</label>
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
                            <div class="field">
                                <div class="ui checkbox">
                                    <input type="checkbox" id="sidePanelEffect" disabled name="sidePanelEffect">
                                    <label>Material List Affecting Side Panels</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="ui button" onclick="resetCreateMaterialListForm()">Reset Form</button>
                </div>
                <!-- Right Section -->
                <div class="twelve wide column ui segment" style="padding-top:1em;">
                    <form id="addMaterialListForm" class="ui form">
                        <div class="field">
                            <label>Reference/Sample Project</label>
                            <select id="referenceProject" name="referenceProject" class="ui fluid selection search dropdown referenceProject">
                                <option value="">Search Project</option>
                            </select>
                        </div>

                        <div class="two fields">
                            <div class="required field" style="display:none;">
                                <label>Starts By</label>
                                <select id="materialAddedStartsBy" name="materialAddedStartsBy" class="ui search dropdown">
                                    <option value=":: CTH:">:: CTH:</option>
                                    <option value=":: VTH:">:: VTH:</option>
                                </select>
                                <div class="ui pointing top orange basic label">Make sure the prefix is correct!</div>
                            </div>
                            <div class="required field" style="width:100%;">
                                <label>Added Material Number</label>
                                <select id="materialAdded" name="materialAdded" class="ui search dropdown materialSelectBox">
                                    <option value="">Search Material</option>
                                </select>
                                <div class="ui pointing red basic label hidden materialErrMsg"></div>
                            </div>
                        </div>

                        <div id="deviceSecondaryWorkCenterSelectDiv" class="fields" style="width: 75%;margin: 1.5rem auto;display:none;">
                            <div class="eight wide field">
                                <label>Secondary Work Center for Device</label>
                                <select id="deviceSecondaryWcSelect" name="deviceSecondaryWcSelect" class="ui fluid selection search dropdown deviceSecondaryWcSelect">
                                    <option value="">Search Secondary Work Center</option>
                                </select>
                            </div>
                            <div class="eight wide field">
                                <label>Secondary Sub Work Center</label>
                                <select id="deviceSecondarySubWcSelect" name="deviceSecondarySubWcSelect" class="ui fluid dropdown deviceSecondarySubWcSelect disabled">
                                    <option value="">Search Sub Work Center</option>
                                </select>
                            </div>
                        </div>


                        <div id="addedStationField" class="two fields" style="display:none;">
                            <div class="field">
                                <label>Station Code</label>
                                <input type="text" id="addedStationCode" name="addedStationCode" disabled>
                                <div id="addedStationCodeErrMsg" class="ui pointing red basic label hidden workCenterCheckErrMsg"></div>
                            </div>
                            <div class="field">
                                <label>Station Name</label>
                                <input type="text" id="addedStationName" name="addedStationName" disabled>
                            </div>
                        </div>

                        <div class="two fields">
                            <div class="required field" style="display:none;">
                                <label>Starts By</label>
                                <select id="materialDeletedStartsBy" name="materialDeletedStartsBy" class="ui search dropdown">
                                    <option value=":: CTH:">:: CTH:</option>
                                    <option value=":: VTH:">:: VTH:</option>
                                </select>
                                <div class="ui pointing top orange basic label">Make sure the prefix is correct!</div>
                            </div>
                            <div class="required field" style="width:100%;">
                                <label>Deleted Material Number</label>
                                <select id="materialDeleted" name="materialDeleted" class="ui search dropdown materialSelectBox">
                                    <option value="">Search Material</option>
                                </select>
                                <div class="ui pointing red basic label hidden materialErrMsg"></div>
                            </div>
                        </div>

                        <div id="deletedStationField" class="two fields" style="display:none;">
                            <div class="field">
                                <label>Station Code</label>
                                <input type="text" id="deletedStationCode" name="deletedStationCode" disabled>
                                <div id="deletedStationCodeErrMsg" class="ui pointing red basic label hidden workCenterCheckErrMsg">Work Center values of materials must be the same!</div>
                            </div>
                            <div class="field">
                                <label>Station Name</label>
                                <input type="text" id="deletedStationName" name="deletedStationName" disabled>
                            </div>
                        </div>

                        <div class="two fields">
                            <div class="required field">
                                <label>Quantity</label>
                                <input type="number" id="quantity" name="quantity" value="1">
                            </div>
                            <div class="required field">
                                <label>Unit</label>
                                <input type="text" id="unit" name="unit" value="ST">
                            </div>
                        </div>

                        <div class="field">
                            <label>Note</label>
                            <textarea id="specialNote" name="specialNote" rows="2"></textarea>
                        </div>

                        <div class="field">
                            <label>Affected DTO Numbers</label>
                            <select id="affectedDtoNumbers" name="affectedDtoNumbers[]" class="ui fluid search dropdown affectedDtoNumbers" multiple="">
                                <option value="">Search DTO Numbers</option>
                            </select>
                        </div>

                        <div id="btnAddList" class="ui animated positive fade button right floated" style="margin-top:0.7rem;" tabindex="0">
                            <div class="visible content">Add List</div>
                            <div class="hidden content"><i class="save icon"></i></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../../partials/footer.php'; ?>

<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/tkform/detail/add-material-list.js?<?=uniqid()?>"></script>
</body>
</html>
