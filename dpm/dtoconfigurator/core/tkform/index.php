<?php
include_once $_SERVER["DOCUMENT_ROOT"] . '/checklogin.php';
include_once '../../api/models/Journals.php';

SharedManager::checkAuthToModule(35);
Journals::saveJournal('Accessing TK Form Page', PAGE_TKFORM, TKFORM_MAIN, ACTION_PROCESSING, null, 'TK Form');
SharedManager::saveLog('log_dtoconfigurator', 'Accessing TK Form Page');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTO Configurator | TK Forms</title>
    <?php include_once '../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/tkform/index.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<!-- Sidebar -->
<div id="tkPageSidebar" class="ui visible left vertical sidebar menu" style="width:225px;background-color:#f3f3f0;">
    <a href="/dtoconfigurator">
        <div class="item">
            <img id="imgSiemensLogo" src="/dpm/dtoconfigurator/assets/images/siemens-dark.svg" alt="Siemens Logo">
        </div>
    </a>
    <a href="/dpm/dtoconfigurator/core/admin/index.php" id="adminMenuItem" class="item active" style="display:none;">
        <i class="shield icon"></i> Admin
    </a>
    <a href="/dpm/dtoconfigurator/core/orders-plan/index.php" id="ordersPlanItem" class="item active" style="display:none;">
        <i class="calendar alternate icon"></i> Orders Plan
    </a>
    <a href="/dpm/dtoconfigurator/core/tkform/index.php" class="item active">
        <i class="clipboard outline icon"></i> TK Forms
    </a>
    <a href="/dpm/dtoconfigurator/core/projects/index.php" class="item">
        <i class="list alternate icon"></i> Projects
    </a>
    <a href="/dpm/dtoconfigurator/core/material-search/index.php" class="item">
        <i class="search icon"></i> Material Search
    </a>
    <a href="/dpm/dtoconfigurator/core/material-cost/index.php" class="item">
        <i class="euro sign icon"></i> Material Cost
    </a>
    <a href="/dpm/dtoconfigurator/core/material-define/index.php" class="item" target="_blank">
        <i class="cube icon"></i> Material Define
    </a>
    <a href="/dpm/dtoconfigurator/core/checklist/index.php" class="item" target="_blank">
        <i class="check square outline icon"></i> Checklist
    </a>
    <a href="/dpm/dtoconfigurator/core/banfomat/index.php" class="item">
        <i class="file excel outline icon"></i> Banfomat
    </a>
    <a href="/dpm/dtoconfigurator/core/dto-assembly-hours/index.php" class="item">
        <i class="clock icon"></i> DTO Assembly Hours
    </a>
    <a href="/dpm/dtoconfigurator/core/dto-cable-codes/index.php" class="item">
        <i class="plug icon"></i> DTO Cable Codes
    </a>
</div>

<!-- Main Content Area -->
    <div id="tkFormPage" class="pusher" style="margin-right:260px;">
        <!-- Header -->
        <?php include_once '../../partials/header.php'; ?>

        <div class="ui active centered inline loader" style="margin-top:10%;"></div>

        <div id="tkFormGrid" class="ui grid" style="padding-right:2%; display:none;">
            <!-- Create TK Form  Column -->
            <div id="createTkFormAccordion" class="sixteen wide column ui accordion">
                <div class="title">
                    <div class="ui green dropdown button"><i class="plus circle icon"></i>Create TK Form</div>
                </div>
                <div class="content">
                    <form id="createTkFormUiForm" class="ui form">
                        <div id="createTkFormError" class="ui message compact red small hidden"></div>
                        <div id="createTkFormSuccess" class="ui message compact positive small hidden"><i class="check circle icon"></i>TK Form successfully created.</div>
                        <div class="five fields">
                            <div class="required field">
                                <label for="documentNumber">TK Number</label>
                                <input id="documentNumber" name="documentNumber" type="text">
                                <div id="documentNumberError" class="ui pointing red basic label hidden">
                                    TK Number must start with DTO_
                                </div>
                            </div>
                            <div class="required field">
                                <label for="dtoNumber">DTO Number</label>
                                <input id="dtoNumber" name="dtoNumber" type="text">
                                <div id="dtoNumberError" class="ui pointing red basic label hidden" style="line-height:1.3rem;">
                                    DTO Number must start with : <br> NX_ NXM_ NXC_ NX50_ 8BT2_ NXSEC_ SPARE_
                                </div>
                                <div id="dtoNumberWrongEntryError" class="ui pointing red basic label hidden" style="line-height:1.3rem;">
                                    DTO Number should not contain special characters
                                </div>
                            </div>
                            <div class="required field">
                                <label for="description">Description</label>
                                <input id="description" name="description" type="text">
                            </div>
                            <div class="required field">
                                <label for="descriptionTr">Description (Türkçe)</label>
                                <input id="descriptionTr" name="descriptionTr" type="text">
                            </div>
                            <div class="field">
                                <label for="">&nbsp;</label> <!-- to position button-->
                                <div id="btnCreateTkForm" class="ui animated positive fade button" tabindex="0">
                                    <div class="visible content">Create TK Form</div>
                                    <div class="hidden content"><i class="plus circle icon"></i></div>
                                </div>
                            </div>
                        </div>

                        <!-- New Fields for Rearbox Specifications -->
                        <div class="three fields">
                            <div class="required field" style="width:20%;text-align:center;">
                                <label>Is DTO Rearbox?</label>
                                <div class="inline fields" style="justify-content: center; margin-top: 0.75rem;">
                                    <div class="field">
                                        <div class="ui radio checkbox">
                                            <input type="radio" name="isRearbox" value="yes" id="isRearboxYes">
                                            <label for="isRearboxYes">Yes</label>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <div class="ui radio checkbox">
                                            <input type="radio" name="isRearbox" value="no" id="isRearboxNo">
                                            <label for="isRearboxNo">No</label>
                                        </div>
                                    </div>
                                </div>
                                <div id="isRearboxError" class="ui pointing red basic label hidden">
                                    Please select an option
                                </div>
                            </div>

                            <!-- Rearbox Details (Hidden by default) -->
                            <div id="rearboxConnectionType" class="required field" style="display: none;width:20%;">
                                <label for="connectionType">Connection Type</label>
                                <select id="connectionType" name="connectionType" class="ui dropdown">
                                    <option value="">Select Connection Type</option>
                                    <option value="Cable Top">Cable Top</option>
                                    <option value="Cable Bottom">Cable Bottom</option>
                                    <option value="Bar Top">Bar Top</option>
                                    <option value="Bar Bottom">Bar Bottom</option>
                                </select>
                                <div id="connectionTypeError" class="ui pointing red basic label hidden">
                                    Connection Type is required when Rearbox is Yes
                                </div>
                            </div>

                            <div id="rearboxCtVtQuantity" class="required field" style="display: none;width:20%;">
                                <label for="ctVtQuantity">Total CT/VT Quantity Set (1 Set = 3 CT/VT)</label>
                                <select id="ctVtQuantity" name="ctVtQuantity" class="ui dropdown">
                                    <option value="">Select Quantity</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                                <div id="ctVtQuantityError" class="ui pointing red basic label hidden">
                                    CT/VT Quantity is required when Rearbox is Yes
                                </div>
                            </div>
                        </div>
                        <div class="ui divider"></div>
                    </form>
                </div>
            </div>

            <div class="six wide column centered">
                <h3 class="ui header">
                    <i class="search icon"></i>
                    <div class="content">
                        Search TK Forms
                        <div class="sub header">Use the below search to filter TK forms and relevant information</div>
                    </div>
                </h3>
                <div class="ui form">
                    <div class="two fields">
                        <div class="field">
                            <input id="search1" type="text" placeholder="TK | DTO Number">
                        </div>
                        <div class="field">
                            <input id="search2" type="text" placeholder="Description | Created By">
                        </div>
                    </div>
                </div>
                <div id="searchTip" class="ui warning small message" style="display:none;margin:0;">
                    <i class="info circle icon"></i>
                    Use <b>spaces</b> to perform multiple searches in each select box. (e.g., '224 NXC', 'LV Box Minel')<br>
                    <i class="info circle icon"></i>
                    Use double quotes <b>("")</b> if you want sequential words search. (e.g., "100 mm cable")
                </div>
            </div>

            <!-- TK Form List Table -->
            <div id="tkFormTableContainer">
                <table id="tkFormTable" class="ui celled table" style="width:100%">
                    <thead>
                    <tr>
                        <th>TK Number</th>
                        <th>DTO Number</th>
                        <th>Description</th>
                        <th>Created By</th>
                        <th>Created Date</th>
                        <th>Last Updated Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
    <script src="/dpm/dtoconfigurator/assets/js/tkform/index.js?<?=uniqid()?>"></script>
</body>
</html>
