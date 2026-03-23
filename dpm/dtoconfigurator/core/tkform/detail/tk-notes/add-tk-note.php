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
    <title>DTO Configurator | Add TK Note</title>
    <?php include_once '../../../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/tkform/detail/tk-notes/add-tk-notes.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<!-- Sidebar -->
<?php include_once '../../../../partials/tk-sidebar.php'; ?>

<!-- Main Content Area -->
<div id="addTkNotePage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>

    <div id="addTkNoteTkNoteContainer" class="ui container" style="margin-right:5%;display:none;">
        <div class="ui breadcrumb">
            <a href="/dpm/dtoconfigurator/core/tkform/index.php" class="section"><i class="clipboard outline icon"></i>TK Forms</a>
            <i class="right chevron icon divider"></i>
            <a href="/dpm/dtoconfigurator/core/tkform/detail/tk-notes/tk-notes.php?<?php echo $urlParameters; ?>" class="section"><i class="sticky note icon"></i>TK Notes</a>
            <i class="right chevron icon divider"></i>
            <div class="active section"><i class="plus square icon"></i>Create TK Note</div>
        </div>

        <br>

        <h3 id="dtoTitle" class="ui block top attached header">
            <?php echo htmlspecialchars($documentNumber); ?> / <?php echo htmlspecialchars($dtoNumber); ?>
        </h3>

        <div class="ui attached segment">
            <h4 class="ui horizontal divider header">
                <i class="sticky note outline icon tiny"></i>
                Add TK Note
            </h4>
            <form id="addTkNoteForm" class="ui form error">
                <div class="field">
                    <textarea name="note" rows="2" placeholder="Enter a note"></textarea>
                    <div id="addTkNoteErrorMsg" class="ui pointing red basic label hidden">
                        Note field must be required.
                    </div>
                </div>

                <div class="ui field action input">
                    <input type="text" placeholder="Choose Image" style="cursor:pointer;" readonly>
                    <input type="file" name="image" accept=".jpg, .jpeg, .png, .gif">
                    <div id="searchPictureButton" class="ui icon button">
                        <i class="attach icon"></i>
                    </div>
                </div>

                <div class="field">
                    <div id="saveTkNote" class="ui animated instagram fade button" tabindex="0">
                        <div class="visible content">Save Note</div>
                        <div class="hidden content">
                            <i class="save icon"></i>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include_once '../../../../partials/footer.php'; ?>

<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/tkform/detail/tk-notes/add-tk-notes.js?<?=uniqid()?>"></script>
</body>
</html>
