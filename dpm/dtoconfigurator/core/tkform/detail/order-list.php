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
    <title>DTO Configurator | Order List</title>
    <?php include_once '../../../partials/libraries.php'; ?>
    <link href="/dpm/dtoconfigurator/assets/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="/dpm/dtoconfigurator/assets/css/tkform/detail/order-list.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<!-- Sidebar -->
<?php include_once '../../../partials/tk-sidebar.php'; ?>

<!-- Main Content Area -->
<div id="orderListPage" class="pusher" style="margin-right:260px;">
    <!-- Header -->
    <?php include_once '../../../partials/header.php'; ?>

    <div id="fetchOrderListLoader" class="ui icon warning message" style="width:25%;">
        <i class="notched circle loading icon"></i>
        <div class="content">
            <div class="header">
                Please Wait
            </div>
            <p>
                Retrieving the order list that includes this TK Form. <br>
                This may take a few seconds.
            </p>
        </div>
    </div>


    <div id="orderListContainer" class="ui container" style="margin-right:5%;display:none;">
        <div class="ui breadcrumb">
            <a href="/dpm/dtoconfigurator/core/tkform/index.php" class="section"><i class="clipboard outline icon"></i>TK Forms</a>
            <i class="right chevron icon divider"></i>
            <div class="active section"><i class="shopping cart icon"></i>Order List</div>
        </div>
        <br>

        <h3 id="dtoTitle" class="ui block top attached header">
            <?php echo htmlspecialchars($documentNumber); ?> / <?php echo htmlspecialchars($dtoNumber); ?>
        </h3>

        <div id="orderListCheckMsg" class="ui red compact message" style="display:none;">
            <i class="info circle icon"></i>No project associated with this DTO Number could be found.
        </div>

        <div class="ui attached segment">
            <h4 class="ui horizontal divider header">
                <i class="shopping basket icon tiny"></i>
                Orders
            </h4>
            <div class="ui warning message compact">
                <i class="lightbulb outline large icon"></i>
                <b>Tip:</b> Click the project number to view detailed information about the project.
            </div>
            <table id="dtoOrderList" class="ui definition table">
                <tbody>
                    <tr>
                        <td class="two wide column"></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include_once '../../../partials/footer.php'; ?>

<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/tkform/detail/order-list.js?<?=uniqid()?>"></script>
</body>
</html>
