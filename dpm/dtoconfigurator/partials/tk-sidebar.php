<?php
$id = getSanitizedOrDefaultData($_GET['id']);
$documentNumber = getSanitizedOrDefaultData($_GET['document-number']);
$dtoNumber = getSanitizedOrDefaultData($_GET['dto-number']);

$urlParameters = 'id=' . urlencode($id) . '&document-number=' . urlencode($documentNumber) . '&dto-number=' . urlencode($dtoNumber);
?>

<div id="indexPageSidebar" class="ui visible left vertical sidebar menu" style="width: 225px;background-color:#f3f3f0;">
    <div class="item">
        <img id="imgSiemensLogo" src="/dpm/dtoconfigurator/assets/images/siemens-dark.svg" alt="Siemens Logo">
    </div>
    <a href="/dpm/dtoconfigurator/core/admin/index.php" id="adminMenuItem" class="item active" style="display:none;">
        <i class="shield icon"></i> Admin
    </a>
    <a href="/dpm/dtoconfigurator/core/orders-plan/index.php" id="ordersPlanItem" class="item active" style="display:none;">
        <i class="calendar alternate icon"></i> Orders Plan
    </a>
    <div class="item active">
        <div class="ui accordion" style="width:150%;">
            <div class="title active" style="display: flex; align-items: center; justify-content: space-between;padding:0;">
                <a href="/dpm/dtoconfigurator/core/tkform/index.php"><i class="clipboard outline icon" style="margin-left:7px;"></i> TK Forms</a>
                <i class="caret down icon" style="margin-right: -1px;"></i>
            </div>
            <div class="content active">
                <div id="tkformSubMenus" class="menu">
                    <a href="/dpm/dtoconfigurator/core/tkform/detail/info.php?<?php echo $urlParameters; ?>" class="item info"><i class="info circle icon"></i>TK Information</a>
                    <a href="/dpm/dtoconfigurator/core/tkform/detail/material-list.php?<?php echo $urlParameters; ?>" class="item material-list"><i class="list icon"></i>Material List</a>
                    <a href="/dpm/dtoconfigurator/core/tkform/detail/add-material-list.php?<?php echo $urlParameters; ?>" class="item add-material-list"><i class="plus square icon"></i>Add Material List</a>
                    <a href="/dpm/dtoconfigurator/core/tkform/detail/nc-list.php?<?php echo $urlParameters; ?>" class="item nc-list">
                        <i class="exclamation triangle icon"></i>NC List
                        <div class="ui mini label" id="ncListCount" style="margin: 0;padding-left: 6px;margin-left: 5px !important;padding-right: 6px;"></div>

                    </a>
                    <a href="/dpm/dtoconfigurator/core/tkform/detail/order-list.php?<?php echo $urlParameters; ?>" class="item order-list"><i class="shopping cart icon"></i>Order List</a>
                    <a href="/dpm/dtoconfigurator/core/tkform/detail/tk-notes/tk-notes.php?<?php echo $urlParameters; ?>" class="item tk-notes">
                        <i class="sticky note icon"></i>TK Notes
                        <div class="ui mini label" id="tkNotesCount" style="margin: 0;padding-left: 6px;margin-left: 5px !important;padding-right: 6px;"></div>
                    </a>
                    <a href="/dpm/dtoconfigurator/core/tkform/detail/tk-jt-collection.php?<?php echo $urlParameters; ?>" class="item tk-jt-collection"><i class="fax icon"></i>JT Collection</a>
                    <a href="/dpm/dtoconfigurator/core/tkform/detail/tk-material-costs.php?<?php echo $urlParameters; ?>" class="item tk-material-costs"><i class="euro sign icon"></i>TK Material Costs</a>
                </div>
            </div>
        </div>
    </div>
    <a href="/dpm/dtoconfigurator/core/projects/index.php" class="item">
        <i class="list alternate outline icon"></i> Projects
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
