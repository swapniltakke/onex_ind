<?php
$projectNo = getSanitizedOrDefaultData($_GET['project-no']);
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
    <a href="/dpm/dtoconfigurator/core/tkform/index.php" class="item">
        <i class="clipboard outline icon"></i> TK Forms
    </a>
    <div class="item active">
        <div class="ui accordion" style="width:150%;">
            <div class="title active" style="display: flex; align-items: center; justify-content: space-between;padding:0;">
                <a href="/dpm/dtoconfigurator/core/projects/index.php"><i class="list alternate icon" style="margin-left:7px;"></i> Projects</a>
                <i class="caret down icon" style="margin-right: -1px;"></i>
            </div>
            <div class="content active">
                <div id="projectsSubMenus" class="menu">
                    <a href="/dpm/dtoconfigurator/core/projects/detail/info.php?project-no=<?php echo $projectNo; ?>" class="item info">
                        <i class="info circle icon"></i>Project Information
                    </a>
                </div>
            </div>
        </div>
    </div>
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
