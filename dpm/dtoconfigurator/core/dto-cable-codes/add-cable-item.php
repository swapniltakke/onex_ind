<?php
include_once $_SERVER["DOCUMENT_ROOT"] . '/checklogin.php';
include_once '../../api/models/Journals.php';

SharedManager::checkAuthToModule(35);
//Journals::saveJournal('Accessing Checklist Page', PAGE_CHECKLIST, CHECKLIST_ADD_CHECKLIST_ITEM, ACTION_PROCESSING, null, 'Add Checklist Item Page');
//SharedManager::saveLog('log_dtoconfigurator', 'Accessing Checklist Page');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTO Configurator | DTO Cable Codes</title>
    <?php include_once '../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/dto-cable-codes/style.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<!-- Sidebar -->
<?php include_once '../../partials/sidebar.php'; ?>

<!-- Main Content Area -->
<div id="addCableItemPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>


    <div class="addCableItemPageContainer ui container" style="padding-right:5%;display:none;">
        <!-- Add New Cable Item Form -->
        <div id="addCableItemForm" class="ui raised">
            <h3 class="ui header" style="margin-bottom:1.5rem; margin-top:0">
                <i class="plug icon"></i>
                <div class="content">
                    Insert Cable Code
                    <div class="sub header">Create and manage CTH / VTH cable codes</div>
                </div>
            </h3>

            <div class="ui divider"></div>

            <form class="ui form" id="checklistForm" enctype="multipart/form-data">
                <!-- Cable Type Selection -->
                <div class="field">
                    <label>Cable Type Category</label>
                    <div class="ui selection dropdown" id="cableTypeCategory">
                        <input type="hidden" name="cableTypeCategory">
                        <i class="dropdown icon"></i>
                        <div class="default text">Select cable type</div>
                        <div class="menu">
                            <div class="item" data-value="VTH">VTH Cable</div>
                            <div class="item" data-value="CTH">CTH Cable</div>
                        </div>
                    </div>
                </div>

                <!-- Basic Information Section -->
                <div id="basicInfoSection" class="ui segment" style="display:none;">
                    <h4 class="ui dividing header" style="text-align:center;padding-bottom:10px;">
                        <i class="info circle icon"></i>
                        Basic Cable Information
                    </h4>

                    <div class="field">
                        <label>Definition of Cable Harness</label>
                        <input type="text" name="definition" placeholder="Enter cable harness definition">
                    </div>

                    <div class="field">
                        <label>TR NOT (Notes)</label>
                        <textarea name="notes" rows="2" placeholder="Enter a Turkish note"></textarea>
                    </div>

                    <div class="three fields">
                        <div class="field">
                            <label>Number of Harness</label>
                            <input type="text" name="numberHarness" placeholder="e.g., A7ETKBL400000297">
                        </div>
                        <div class="field">
                            <label id="codeLabel">VTH Code</label>
                            <input type="text" name="vthCode" placeholder="e.g., VTH:11XXAAA2A1A1C1">
                        </div>
                        <div class="field">
                            <label>Number of Drawing</label>
                            <input type="text" name="numberDrawing" placeholder="e.g., A7E0018701609">
                        </div>
                    </div>

                    <div class="three fields">
                        <div class="field">
                            <label>Cable Type</label>
                            <input type="text" name="cableType" placeholder="e.g., H07Z-K">
                        </div>
                        <div class="field">
                            <label>Cable Cross-section (mm²)</label>
                            <input type="text" name="cableCrossSection" placeholder="e.g., 2.5, 4.0, or 2.5 ve 4.0 mm2">
                        </div>
                        <div class="field">
                            <label>Cable Length Type</label>
                            <input type="text" name="cableLengthType" placeholder="e.g., NXAIR M 1000mm">
                        </div>
                    </div>
                </div>

                <!-- Cable Colors Section -->
                <div id="cableColorsSection" class="ui segment" style="display:none;">
                    <h4 class="ui dividing header" style="text-align:center;padding-bottom:10px;">
                        <i class="paint brush icon"></i>
                        Cable Colors
                    </h4>

                    <!-- VTH Colors (default hidden) -->
                    <div id="vthColors" style="display:none;">
                        <div class="three fields">
                            <div class="field">
                                <label>T5L1 - Color A</label>
                                <select name="t5l1ColorA" class="ui fluid search selection dropdown ral-color-dropdown">
                                    <option value="">Select RAL Color</option>
                                    <!-- Will be populated via JS -->
                                </select>
                            </div>
                            <div class="field">
                                <label>T5L2 - Color A (1)</label>
                                <select name="t5l2ColorA1" class="ui fluid search selection dropdown ral-color-dropdown">
                                    <option value="">Select RAL Color</option>
                                    <!-- Will be populated via JS -->
                                </select>
                            </div>
                            <div class="field">
                                <label>T5L2 - Color A (2)</label>
                                <select name="t5l2ColorA2" class="ui fluid search selection dropdown ral-color-dropdown">
                                    <option value="">Select RAL Color</option>
                                    <!-- Will be populated via JS -->
                                </select>
                            </div>
                        </div>
                        <div class="three fields">
                            <div class="field">
                                <label>T5L1 - Color N</label>
                                <select name="t5l1ColorN" class="ui fluid search selection dropdown ral-color-dropdown">
                                    <option value="">Select RAL Color</option>
                                    <!-- Will be populated via JS -->
                                </select>
                            </div>
                            <div class="field">
                                <label>T5L2 - Color N (1)</label>
                                <select name="t5l2ColorN1" class="ui fluid search selection dropdown ral-color-dropdown">
                                    <option value="">Select RAL Color</option>
                                    <!-- Will be populated via JS -->
                                </select>
                            </div>
                            <div class="field">
                                <label>T5L2 - Color N (2)</label>
                                <select name="t5l2ColorN2" class="ui fluid search selection dropdown ral-color-dropdown">
                                    <option value="">Select RAL Color</option>
                                    <!-- Will be populated via JS -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- CTH Colors (default hidden) -->
                    <div id="cthColors" style="display:none;">
                        <div class="three fields">
                            <div class="field">
                                <label>T1L1 - 1S1</label>
                                <select name="t1l1_1s1" class="ui fluid search selection dropdown ral-color-dropdown">
                                    <option value="">Select RAL Color</option>
                                    <!-- Will be populated via JS -->
                                </select>
                            </div>
                            <div class="field">
                                <label>T1L1 - 1S2</label>
                                <select name="t1l1_1s2" class="ui fluid search selection dropdown ral-color-dropdown">
                                    <option value="">Select RAL Color</option>
                                    <!-- Will be populated via JS -->
                                </select>
                            </div>
                            <div class="field">
                                <label>T1L1 - 1S3</label>
                                <select name="t1l1_1s3" class="ui fluid search selection dropdown ral-color-dropdown">
                                    <option value="">Select RAL Color</option>
                                    <!-- Will be populated via JS -->
                                </select>
                            </div>
                        </div>
                        <div class="three fields">
                            <div class="field">
                                <label>T1L2 - 1S1</label>
                                <select name="t1l2_1s1" class="ui fluid search selection dropdown ral-color-dropdown">
                                    <option value="">Select RAL Color</option>
                                    <!-- Will be populated via JS -->
                                </select>
                            </div>
                            <div class="field">
                                <label>T1L2 - 1S2</label>
                                <select name="t1l2_1s2" class="ui fluid search selection dropdown ral-color-dropdown">
                                    <option value="">Select RAL Color</option>
                                    <!-- Will be populated via JS -->
                                </select>
                            </div>
                            <div class="field">
                                <label>T1L2 - 1S3</label>
                                <select name="t1l2_1s3" class="ui fluid search selection dropdown ral-color-dropdown">
                                    <option value="">Select RAL Color</option>
                                    <!-- Will be populated via JS -->
                                </select>
                            </div>
                        </div>
                        <div class="three fields">
                            <div class="field">
                                <label>T1L3 - 1S1</label>
                                <select name="t1l3_1s1" class="ui fluid search selection dropdown ral-color-dropdown">
                                    <option value="">Select RAL Color</option>
                                    <!-- Will be populated via JS -->
                                </select>
                            </div>
                            <div class="field">
                                <label>T1L3 - 1S2</label>
                                <select name="t1l3_1s2" class="ui fluid search selection dropdown ral-color-dropdown">
                                    <option value="">Select RAL Color</option>
                                    <!-- Will be populated via JS -->
                                </select>
                            </div>
                            <div class="field">
                                <label>T1L3 - 1S3</label>
                                <select name="t1l3_1s3" class="ui fluid search selection dropdown ral-color-dropdown">
                                    <option value="">Select RAL Color</option>
                                    <!-- Will be populated via JS -->
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTH Specific Fields -->
                <div id="cthSpecificSection" class="ui segment" style="display:none;">
                    <h4 class="ui dividing header" style="text-align:center;padding-bottom:10px;">
                        <i class="settings icon"></i>
                        CTH Specific Information
                    </h4>

                    <div class="four fields">
                        <div class="field">
                            <label>Cable Length L1+L2+L3 (mm)</label>
                            <input type="text" name="cableLengthTotal" placeholder="e.g., 13800">
                        </div>
                        <div class="field">
                            <label>Cable Qty (Group)</label>
                            <input type="text" name="cableLengthGroups" placeholder="Enter cable length groups">
                        </div>
                        <div class="field">
                            <label>Total Cable Length (mm)</label>
                            <input type="text" name="totalCableLength" placeholder="Enter total cable length">
                        </div>
                        <div class="field">
                            <label>CT in Rear Box</label>
                            <div class="ui selection dropdown">
                                <input type="hidden" name="ctInRearBox">
                                <i class="dropdown icon"></i>
                                <div class="default text">Select option</div>
                                <div class="menu">
                                    <div class="item" data-value="Yes">Yes</div>
                                    <div class="item" data-value="No">No</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product and Usage Section -->
                <div id="productUsageSection" class="ui segment" style="display:none;">
                    <h4 class="ui dividing header" style="text-align:center;padding-bottom:10px;">
                        <i class="cubes icon"></i>
                        Product and Other Technical Information
                    </h4>

                    <div class="two fields">
                        <div class="field">
                            <label>Place to Use</label>
                            <input type="text" name="placeToUse" placeholder="e.g., CC">
                        </div>
                        <div class="field">
                            <label>
                                <i class="cubes icon"></i>
                                Product Types
                            </label>
                            <select name="product_types[]" class="ui fluid multiple search selection dropdown" multiple>
                                <option value="">Choose product types...</option>
                                <!-- Will be populated via JS -->
                            </select>
                        </div>
                    </div>

                    <div class="three fields">
                        <div class="field">
                            <label>Panel Width (mm)</label>
                            <input type="number" name="panelWidth" placeholder="Enter panel width">
                        </div>
                        <div class="field">
                            <label>Core</label>
                            <input type="number" name="core" placeholder="Enter core number">
                        </div>
                        <div class="field">
                            <label>Order No</label>
                            <input type="text" name="orderNo" placeholder="Enter order number">
                        </div>
                    </div>

                    <div class="field">
                        <label>Additional Information</label>
                        <textarea name="additionalInfo" rows="3" placeholder="Enter additional information"></textarea>
                    </div>
                </div>

                <!-- Form Actions Section -->
                <div id="formActionsSection" class="ui segment" style="display:none;">
                    <div class="ui buttons fluid">
                        <button class="ui button" type="button" id="cancelBtn">
                            <i class="cancel icon"></i>
                            Cancel
                        </button>
                        <div class="or"></div>
                        <button class="ui positive button" type="submit" id="submitBtn">
                            <i class="save icon"></i>
                            Save Cable
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

</div>


<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/dto-cable-codes/add-cable-item.js?<?=uniqid()?>"></script>
</body>
</html>
