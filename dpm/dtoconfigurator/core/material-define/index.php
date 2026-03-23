<?php
include_once $_SERVER["DOCUMENT_ROOT"] . '/checklogin.php';
include_once '../../api/models/Journals.php';

SharedManager::checkAuthToModule(35);
Journals::saveJournal('Accessing Material Define Page', PAGE_MATERIAL_SEARCH, MATERIAL_SEARCH, ACTION_PROCESSING, null, 'Material Define');
SharedManager::saveLog('log_dtoconfigurator', 'Accessing Material Define Page');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTO Configurator | Material Define</title>
    <?php include_once '../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/material-define/index.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<!-- Sidebar -->
<?php include_once '../../partials/sidebar.php'; ?>

<!-- Main Content Area -->
<div id="materialDefinePage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>

    <div class="materialSearchContainer ui container" style="padding-right:5%;">
        <h3 class="ui header" style="margin-bottom:1.5rem; margin-top:1rem;">
            <i class="wpforms icon"></i>
            <div class="content">
                Material Definition Form
                <div class="sub header">Define materials here to add them into TK Forms and use on the Projects page</div>
            </div>
        </h3>

        <!-- Tab Menu -->
        <div class="ui secondary pointing menu" id="materialTabMenu">
            <a class="item active" data-tab="new-material">
                <i class="plus icon"></i>
                New Material
            </a>
            <a class="item" data-tab="update-material">
                <i class="edit icon"></i>
                Update Material
            </a>
        </div>

        <!-- New Material Tab Content -->
        <div class="ui tab segment active" data-tab="new-material">
            <div class="ui icon message info">
                <i class="info circle icon"></i>
                <div class="content">
                    <div class="header" style="margin-bottom:0.6rem;">
                        Add New Material
                    </div>
                    Search for a material in SAP and define it to DTO Configurator system.
                </div>
            </div>
            <div id="requiredFieldsError" class="ui icon message negative" style="display:none;">
                <i class="warning icon"></i>
                <div class="content">
                    <div class="header" style="margin-bottom:0.6rem;display:none;">
                        Required Fields
                    </div>
                    Please fill all required fields.
                </div>
            </div>
            <div id="successMessage" class="ui icon message positive" style="display:none;">
                <i class="thumbs up icon"></i>
                <div class="content">
                    <div class="header" style="margin-bottom:0.6rem;display:none;">
                        Successful!
                    </div>
                    Material is successfully saved.
                </div>
            </div>

            <div id="materialDefineForm2" class="ui form">
                <div class="required fields">
                    <div class="eight wide field">
                        <label>Work Center</label>
                        <select id="workCenterSelect" name="workCenterSelect" class="ui fluid selection search dropdown workCenterSelect">
                            <option value="">Search Work Center</option>
                        </select>
                    </div>
                    <div class="eight wide field">
                        <label>Sub Work Center</label>
                        <select id="subWorkCenterSelect" name="subWorkCenterSelect" class="ui fluid dropdown subWorkCenterSelect disabled">
                            <option value="">Search Sub Work Center</option>
                        </select>
                        <div id="subWorkCenterSelectErrMsg" class="ui pointing red basic label hidden">
                            Please choose a sub work center.
                        </div>
                    </div>
                </div>

                <div id="loadingIndicator" class="ui active centered inline loader" style="display: none;margin-bottom:10px;margin-top:15px;"></div>

                <div id="sapMaterialDropdownField" class="required field">
                    <label>Select Material</label>
                    <div class="ui fluid search selection dropdown" id="sapMaterialDropdown">
                        <input type="hidden" name="material_id" id="materialId">
                        <i class="dropdown icon"></i>
                        <div class="default text">Type at least 7 characters to material search...</div>
                        <div class="menu" id="materialMenu">
                            <!-- Materials will be populated here -->
                        </div>
                    </div>
                </div>

                <!-- Manual material entry section -->
                <div  id="manualMaterialSection" class="field" style="display: none;">
                    <div class="ui checkbox">
                        <input type="checkbox" id="allowManualEntry" name="allowManualEntry">
                        <label><strong>Are you trying to define VTH or CTH code?</strong></label>
                    </div>
                    <div class="ui info message">
                        <div class="header">Material <span class="entered-material-text"></span> not found in SAP</div>
                        <p>The material you searched for was not found. To define it, please first transfer material by clicking the <b>Transfer to SAP Locations</b> button in Teamcenter.</p>
                        <p>If you have already transferred the material to SAP Locations, please note that it may take <b>up to 10 minutes</b>  to appear in SAP.</p>
                    </div>
                    <div class="ui image" style="width:100%;">
                        <img src="/dpm/dtoconfigurator/assets/images/transferToSap.png" style="margin:0 auto;">
                    </div>

                </div>

                <!-- Manual material number input -->
                <div class="required field" id="manualMaterialField" style="display: none;">
                    <label>Material Number (CTH/VTH)</label>
                    <input type="text" id="manualMaterialNumber" name="manualMaterialNumber" placeholder="Enter material number (min 7 characters)" maxlength="50">
                    <div class="ui pointing above label" style="display: none;" id="manualMaterialHint">
                        <i class="info circle icon"></i>
                        Do not include <b>:: CTH:</b> or <b>:: VTH:</b> prefix.
                    </div>
                </div>

                <div class="required field" id="descriptionField" style="display: none;">
                    <label>Material Description</label>
                    <textarea id="materialDescription" name="material_description" placeholder="Material description will appear here..." rows="3"></textarea>
                    <div class="ui pointing top label" style="display: none;" id="descriptionHint">
                        <i class="info circle icon"></i>
                        You can edit this description as needed
                    </div>
                </div>

                <div class="field" style="display:flex;justify-content:center;margin-top:2rem;">
                    <div class="ui checkbox">
                        <input type="checkbox" id="isDevice" name="isDevice">
                        <label style="font-weight:bold;">Choose if it is a device</label>
                    </div>
                </div>

                <div style="display:flex;justify-content:end;">
                    <button class="ui green button" id="defineMaterialButton" disabled>
                        Submit Material
                    </button>
                </div>
            </div>
        </div>

        <!-- Update Material Tab Content -->
        <div class="ui tab segment" data-tab="update-material">

            <div class="ui icon message warning">
                <i class="exclamation circle icon"></i>
                <div class="content">
                    <div class="header" style="margin-bottom:0.6rem;">
                        Important Notice
                    </div>
                    Material number you're updating is <b>already defined</b> in the system, work center and description fields below will populate automatically. <br>Please be aware that changes to existing material <b>may affect works</b> on the Projects page.
                </div>
            </div>
            <div id="updateRequiredFieldsError" class="ui icon message negative" style="display:none;">
                <i class="warning icon"></i>
                <div class="content">
                    <div class="header" style="margin-bottom:0.6rem;display:none;">
                        Required Fields
                    </div>
                    Please fill all required fields.
                </div>
            </div>
            <div id="updateSuccessMessage" class="ui icon message positive" style="display:none;">
                <i class="thumbs up icon"></i>
                <div class="content">
                    <div class="header" style="margin-bottom:0.6rem;display:none;">
                        Successful!
                    </div>
                    Material has been successfully updated.
                </div>
            </div>

            <div id="updateMaterialForm" class="ui form">
                <div class="ui active centered inline loader" id="updateLoadingIndicator" style="display: none; margin-top: 10px; margin-bottom:10px;"></div>
                <div class="required field">
                    <label>Existing Material</label>
                    <div class="ui fluid search selection dropdown" id="existingMaterialDropdown">
                        <input type="hidden" name="existing_material_id" id="existingMaterialId">
                        <i class="dropdown icon"></i>
                        <div class="default text">Type at least 3 characters to search existing materials...</div>
                        <div class="menu" id="existingMaterialMenu">
                            <!-- Existing materials will be populated here -->
                        </div>
                    </div>

                </div>

                <!-- Material details will be shown here after selection -->
                <div id="updateMaterialDetails" style="display: none;">
                    <div class="ui divider"></div>
                    <h4 class="ui header">
                        <i class="edit icon"></i>
                        <div class="content">
                            Update Material Information
                            <div class="sub header">Modify the details below</div>
                        </div>
                    </h4>

                    <div class="required fields">
                        <div class="eight wide field">
                            <label>Work Center</label>
                            <select id="updateWorkCenterSelect" name="updateWorkCenterSelect" class="ui fluid selection search dropdown">
                                <option value="">Search Work Center</option>
                            </select>
                        </div>
                        <div class="eight wide field">
                            <label>Sub Work Center</label>
                            <select id="updateSubWorkCenterSelect" name="updateSubWorkCenterSelect" class="ui fluid dropdown disabled">
                                <option value="">Search Sub Work Center</option>
                            </select>
                            <div id="updateSubWorkCenterSelectErrMsg" class="ui pointing red basic label hidden">
                                Please choose a sub work center.
                            </div>
                        </div>
                    </div>

                    <div class="required field">
                        <label>Material Description</label>
                        <textarea id="updateMaterialDescription" name="updateMaterialDescription" placeholder="Enter material description..." rows="3"></textarea>
                    </div>

                    <div class="field" style="display:flex;justify-content:center;margin-top:1rem;">
                        <div class="ui checkbox">
                            <input type="checkbox" id="updateIsDevice" name="updateIsDevice">
                            <label>Choose if it is a device</label>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:end;gap:10px;">
                        <button class="ui orange button" id="updateMaterialButton">
                            Update Material
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/material-define/index.js?<?=uniqid()?>"></script>
</body>
</html>
