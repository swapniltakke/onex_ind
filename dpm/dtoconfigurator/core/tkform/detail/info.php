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
    <title>DTO Configurator | TK Info</title>
    <?php include_once '../../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/tkform/detail/info.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<!-- Sidebar -->
<?php include_once '../../../partials/tk-sidebar.php'; ?>

<!-- Main Content Area -->
<div id="tkInfoPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>

    <div id="tkInfoContainer" class="ui container" style="display:none;">
        <div class="ui breadcrumb">
            <a href="/dpm/dtoconfigurator/core/tkform/index.php" class="section"><i class="clipboard outline icon"></i>TK Forms</a>
            <i class="right chevron icon divider"></i>
            <div class="active section"><i class="info circle icon"></i>TK Information</div>
        </div><br>

        <div id="deleteTkFormSuccess" class="ui message compact positive small" style="display:none;"></div>

        <form id="tkformInfoForm" class="ui segment form success">
            <h3 class="ui header" style="margin-top:5px; margin-bottom:25px;">
                <i class="wpforms icon"></i>
                <div class="content">
                    TK Form Information
                    <div class="sub header">Find the details of the TK Form in the section below</div>
                </div>
            </h3>

            <div id="updateTkFormError" class="ui message compact red small hidden"><i class="exclamation circle icon"></i>Please fill all the required fields.</div>
            <div id="updateTkFormSuccess" class="ui message compact positive small hidden"><i class="check circle icon"></i>TK Form successfully updated.</div>
            <div class="required fields">
                <div class="eight wide field">
                    <label>TK Number</label>
                    <input type="text" id="documentNumber" name="documentNumber">
                    <div id="documentNumberError" class="ui pointing red basic label hidden">
                        TK Number must start with DTO_
                    </div>
                </div>
                <div class="eight wide field">
                    <label>DTO Number</label>
                    <input type="text" id="dtoNumber" name="dtoNumber">
                    <div id="dtoNumberError" class="ui pointing red basic label hidden" style="line-height:1.3rem;">
                        DTO Number must start with : <br> NX_ NXM_ NXC_ NX50_ 8BT2_ NXSEC_ SPARE_
                    </div>
                    <div id="dtoNumberWrongEntryError" class="ui pointing red basic label hidden" style="line-height:1.3rem;">
                        DTO Number should not contain special characters
                    </div>
                </div>
            </div>
            <div class="required field">
                <label>Description</label>
                <textarea id="description" name="description" rows="2"></textarea>
            </div>
            <div class="required field">
                <label>Description (TR)</label>
                <textarea id="descriptionTr" name="descriptionTr" rows="2"></textarea>
            </div>
            <div class="fields">
                <div class="eight wide field">
                    <label>Created Date</label>
                    <input type="text" id="createdDate" disabled>
                </div>
                <div class="eight wide field">
                    <label>Created By</label>
                    <input type="text" id="createdBy" disabled>
                </div>
            </div>
            <div class="fields">
                <div class="eight wide field">
                    <label>Last Updated Date</label>
                    <input type="text" id="lastUpdatedDate" disabled>
                </div>
                <div class="eight wide field">
                    <label>Last Updated By</label>
                    <input type="text" id="updatedBy" disabled>
                </div>
            </div>
            <div id="tkInfoActionButtons" class="fields">
                <div id="openDeleteTkModal" class="ui animated red fade button" tabindex="0">
                    <div class="visible content">Delete TK Form</div>
                    <div class="hidden content">
                        <i class="trash icon"></i>
                    </div>
                </div>
                <div id="updateTkButton" class="ui animated positive fade button" tabindex="0">
                    <div class="visible content">Save Changes</div>
                    <div class="hidden content">
                        <i class="save icon"></i>
                    </div>
                </div>
            </div>
        </form>

        <div id="deleteTkFormModal" class="ui mini modal">
            <div class="header">Delete TK Form</div>
            <div class="content">
                <p id="deleteTkFormModalContent" style="font-weight: 700;font-size: 0.9rem;"></p>
                <p>Are you sure still want to delete TK Form?</p>
            </div>
            <div class="actions">
                <div class="ui red cancel button">Cancel</div>
                <div class="ui green approve button" style="margin:0;">Delete</div>
            </div>
        </div>
    </div>
</div>
<?php include_once '../../../partials/footer.php'; ?>

<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/tkform/detail/info.js?<?=uniqid()?>"></script>
</body>
</html>




