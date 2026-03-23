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
    <title>DTO Configurator | TK Notes</title>
    <?php include_once '../../../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/tkform/detail/tk-notes/tk-notes.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<!-- Sidebar -->
<?php include_once '../../../../partials/tk-sidebar.php'; ?>

<!-- Main Content Area -->
<div id="tkNotesPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>

    <div id="tkNotesContainer" class="ui container" style="margin-right:5%;display:none;">
        <div class="ui breadcrumb">
            <a href="/dpm/dtoconfigurator/core/tkform/index.php" class="section"><i class="clipboard outline icon"></i>TK Forms</a>
            <i class="right chevron icon divider"></i>
            <div class="active section"><i class="sticky note icon"></i>TK Notes</div>
        </div>
        <br>
        <h3 id="dtoTitle" class="ui block top attached header">
            <?php echo htmlspecialchars($documentNumber); ?> / <?php echo htmlspecialchars($dtoNumber); ?>
            <div id="addNewTkNoteBtn" class="ui animated fade instagram button right floated" style="margin-top:-0.45rem;" tabindex="0">
                <div class="visible content">Add Note</div>
                <div class="hidden content">
                    <i class="plus square icon medium" style="margin-left:0.75rem;"></i>
                </div>
            </div>
        </h3>

        <div id="tkNotesCheckMsg" class="ui yellow compact message" style="display:none;">
            <i class="info circle icon"></i>There are no notes associated with this TK Form.
        </div>

        <div class="ui attached segment">
            <div id="tkNoteItemList" class="ui divided items"></div>
            <div id="deleteTkNoteModal" class="ui mini modal">
                <div class="header">Delete Note</div>
                <div class="content">
                    <p>Are you sure you want to delete this note?</p>
                    <p id="deleteTkFormModalContent" style="font-weight: 700;font-style: italic;font-size: 0.9rem;"></p>
                </div>
                <div class="actions">
                    <div class="ui cancel button">Cancel</div>
                    <div class="ui approve button">Delete</div>
                </div>
            </div>
        </div>
    </div>

    <div class="ui modal" id="imageEnlargeModal">
        <i class="close icon"></i>
        <div class="content" style="text-align: center;padding: 20px;">
            <img id="enlargedImage" src="" style="max-width: 100%; height: auto; margin: 0 auto; display: block;">
        </div>
    </div>
</div>


<?php include_once '../../../../partials/footer.php'; ?>

<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/tkform/detail/tk-notes/tk-notes.js?<?=uniqid()?>"></script>
</body>
</html>
