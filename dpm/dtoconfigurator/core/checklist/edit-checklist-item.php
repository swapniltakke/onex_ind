<?php
include_once $_SERVER["DOCUMENT_ROOT"] . '/checklogin.php';
include_once '../../api/models/Journals.php';

SharedManager::checkAuthToModule(35);
Journals::saveJournal('Accessing Edit Checklist Page', PAGE_CHECKLIST, CHECKLIST_EDIT_CHECKLIST_ITEM, ACTION_PROCESSING, null, 'Edit Checklist Item Page');
SharedManager::saveLog('log_dtoconfigurator', 'Accessing Edit Checklist Page');

$checklistItemId = getSanitizedOrDefaultData($_GET['id']) ?? null;

if (!$checklistItemId) {
    header('Location: /dpm/dtoconfigurator/core/checklist/index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DTO Configurator | Edit Checklist Item</title>
    <?php include_once '../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/checklist/style.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<!-- Sidebar -->
<?php include_once '../../partials/sidebar.php'; ?>

<!-- Main Content Area -->
<div id="checklistPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>

    <div class="checklistPageContainer ui container" style="padding-right:5%;display:none;">
        <!-- Edit Checklist Item Form -->
        <div id="edit-checklist-form" class="ui raised segment">
            <h3 class="ui header" style="margin-bottom:1.5rem; margin-top:0">
                <i class="edit icon"></i>
                <div class="content">
                    Edit Checklist Item
                    <div class="sub header">Modify checklist item details and settings</div>
                </div>
            </h3>

            <div class="ui divider"></div>

            <form class="ui form" id="checklistForm" enctype="multipart/form-data">
                <div class="ui error message" id="formMessage" style="display: none; border-radius: 8px;"></div>

                <input type="hidden" id="checklist-item-id" value="<?= $checklistItemId ?>">

                <div class="field required">
                    <label class="checklist-title-labels">
                        <i class="edit outline icon"></i>
                        Checklist Detail
                    </label>

                    <!-- Rich Text Editor Container -->
                    <div id="editor-container" style="border: 2px solid #e1e8ed; border-radius: 8px; background: white; height:100px;">
                        <!-- Quill editor will be initialized here -->
                    </div>

                    <!-- Hidden textarea to hold the HTML content -->
                    <textarea id="checklist-detail" name="detail" style="display: none;"></textarea>

                    <!-- Character count display -->
                    <div class="ui mini message" style="margin-top: 8px; padding: 8px;">
                        <i class="info circle icon"></i>
                        <span id="char-count">0</span> characters
                    </div>
                </div>

                <div class="two fields">
                    <div class="field required">
                        <label class="checklist-title-labels">
                            <i class="tag icon"></i>
                            Category
                        </label>
                        <select id="category-select" name="category_id" class="ui search dropdown checklist-dropdowns">
                            <option value="">Choose a category...</option>
                            <!-- Will be populated via JS -->
                        </select>
                    </div>

                    <div class="field">
                        <label class="checklist-title-labels">
                            <i class="image icon"></i>
                            Attachment (Optional)
                        </label>
                        <div class="ui action input" style="border-radius: 8px;">
                            <input type="file" name="image" accept="image/*" style="display: none;" id="imageInput">
                            <input type="text" placeholder="No file selected" readonly id="fileName" style="border-radius: 8px 0 0 8px;">
                            <div class="ui teal labeled icon button" id="uploadBtn" style="border-radius: 0 8px 8px 0;">
                                <i class="cloud upload icon"></i>
                                Browse
                            </div>
                        </div>
                    </div>
                </div>

                <div id="imagePreviewSegment" style="margin-bottom:15px;"></div>

                <div class="field required">
                    <label class="checklist-title-labels">
                        <i class="cubes icon"></i>
                        Product Types
                    </label>
                    <select name="product_types[]" class="ui fluid multiple search selection dropdown checklist-dropdowns" multiple>
                        <option value="">Choose product types...</option>
                        <!-- Will be populated via JS -->
                    </select>
                    <div class="ui pointing top label">
                        <i class="info circle icon"></i>
                        Select which product types this checklist applies to
                    </div>
                </div>

                <div class="ui buttons" style="display:flex;justify-content:end;">
                    <button class="ui button" type="button" onclick="window.history.back()">
                        <i class="arrow left icon"></i>
                        Cancel
                    </button>
                    <div class="or"></div>
                    <button id="updateChecklistItemButton" class="ui positive button" type="submit">
                        <i class="save icon"></i>
                        Update Checklist Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script>
    const CHECKLIST_ITEM_ID = <?= $checklistItemId ?>;
</script>
<script src="/dpm/dtoconfigurator/assets/js/checklist/edit-checklist-item.js?<?=uniqid()?>"></script>
</body>
</html>