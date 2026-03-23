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
    <title>DTO Configurator | NC List</title>
    <?php include_once '../../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/tkform/detail/nc-list.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<!-- Sidebar -->
<?php include_once '../../../partials/tk-sidebar.php'; ?>

<!-- Main Content Area -->
<div id="ncListPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../../partials/header.php'; ?>

    <div class="ui active centered inline loader" style="margin-top:10%;"></div>

    <div id="ncListContainer" class="ui container-fluid display-none" style="margin-right:5%;">
        <div class="ui breadcrumb">
            <a href="/dpm/dtoconfigurator/core/tkform/index.php" class="section"><i class="clipboard outline icon"></i>TK Forms</a>
            <i class="right chevron icon divider"></i>
            <div class="active section"><i class="exclamation triangle icon"></i>NC List</div>
        </div>
        <br>

        <h3 id="dtoTitle" class="ui block top attached header">
            <?php echo htmlspecialchars($documentNumber); ?> / <?php echo htmlspecialchars($dtoNumber); ?>
        </h3>

        <div class="ui attached segment">
            <div id="openNcCheckMsg" class="ui compact message"></div>

            <!-- NC List Table -->
            <div id="ncListTableContainer" class="sixteen wide column">
                <table id="ncListTable" class="ui celled table" style="width:100%">
                    <thead>
                    <tr>
                        <th>Project Number</th>
                        <th>Panel Number</th>
                        <th>NC Number</th>
                        <th>NC Status</th>
                        <th>Description</th>
                        <th>NC Created Date</th>
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
<script src="/dpm/dtoconfigurator/assets/js/tkform/detail/nc-list.js?<?=uniqid()?>"></script>
</body>
</html>
