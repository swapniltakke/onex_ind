<link href="/dpm/dtoconfigurator/assets/css/projects/detail/tabs/project-work/project-work.css" rel="stylesheet" type="text/css"/>

<div class="ui active inline indeterminate text loader" style="display:none;"><b>Loading Project Work</b></div>

<div id="projectWorkContainer" style="display:none;!important;">
    <div id="projectNotWorkedMessage">
        <div class="ui icon message warning">
            <i class="warning message icon" style="font-size: 5rem;margin-right: 1rem;"></i>
            <div class="content" style="padding:0.8rem;">
                <div class="header" style="margin-bottom:0.7rem;">
                    Start Working on Nachbau
                </div>
                <p><b>This nachbau has not been worked on before.</b></p>
                <p><b>If it is a new nachbau, you can transfer your previous work from another nachbau using the Nachbau Transfer option in the Nachbau Operations tab.</b></p>
                <button id="updateNachbauButton" class="ui labeled icon brown button" style="margin-top:0.4rem;">
                    <i class="hand point right outline large icon"></i>
                    Click to Start Working on Nachbau
                </button>
            </div>
        </div>
    </div>

    <div id="projectWorkDataGrid" class="ui container-fluid">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <!-- Left Side Buttons -->
            <div style="display: flex; gap: 10px;">
                <div id="removeAllProjectData" class="ui red circular icon compact button">
                    <i class="trash alternate icon"></i>
                    Remove Project Work Data
                </div>
                <div id="checkOutButton" class="ui purple circular icon compact button" data-position="top left"
                     data-tooltip="Unlock the project to allow others to check in and make changes.">
                    <i class="unlock icon"></i>
                    Check Out
                </div>

            </div>

            <!-- Right Side Buttons -->
            <div style="display: flex; gap: 10px;">
                <div id="materialDefineModalButton" class="ui teal circular icon compact button">
                    <i class="cubes icon" style="margin-right:0.3rem!important;"></i>
                    Material Define
                </div>
                <div id="refreshProjectData" data-tooltip="Refresh Project Work Data" data-position="top right" data-inverted=""
                     class="ui green circular icon compact button">
                    <i class="repeat icon"></i>
                </div>
            </div>
        </div>

        <div id="projectWorkTableButtons" style="text-align:center;">
            <div id="notDefinedWorkCenterButton" class="ui inverted blue circular icon very compact button">
                <i class="exclamation circle icon" style="margin-right:0.3rem;"></i>
                Update Work Centers
            </div>
            <div id="workListButton" class="ui inverted green circular icon very compact button">
                <i class="clipboard icon" style="margin-right:0.3rem;"></i>
                Work List
            </div>
            <div id="extensionDtoListButton" class="ui inverted violet circular icon very compact button">
                <i class="cogs icon" style="margin-right:0.3rem;"></i>
                Extension DTO List
            </div>
            <div id="notFoundNachbau" class="ui inverted brown circular icon very compact button">
                <i class="question circle icon" style="margin-right:0.3rem;"></i>
                Not Found Nachbau
            </div>
            <div id="tkformsNotExistButton" class="ui inverted orange circular icon very compact button">
                <i class="ban icon" style="margin-right:0.3rem;"></i>
                TK Forms Not Exists
            </div>
            <div id="openSparePartDtoWorkModal" class="ui inverted grey circular icon very compact button active" style="display:none!important;background-color: firebrick!important;color: white!important;"
                 data-tooltip="Click to open Spare Part DTO work menu" data-position="top center">
                <i class="puzzle piece icon" style="margin-right:0.3rem;"></i>
                Spare Part DTO
            </div>
            <div id="isSpareWdaDtoExistButton" class="ui inverted grey circular icon very compact button active" style="display:none!important;"
                 data-tooltip="You can work on Spare Withdrawable Unit DTO in Order Summary tab and on PA Withdrawable Unit KMAT of its related typicals." data-position="top center">
                <i class="cogs icon"></i>
                Spare Withdrawable Unit
            </div>
            <div id="openMinusPriceDtoWorkModal" class="ui grey inverted circular icon very compact button active" style="background-color:darkred;color:white;display:none!important;"
                 data-tooltip="Click to open Minus Price work menu" data-position="top center">
                <i class="minus icon"></i>
                Minus Price
            </div>
            <div id="chooseInterchangeDtoWorkModalButton" class="ui teal inverted circular icon very compact button active" style="background-color:indigo;color:white;display:none!important;"
                 data-tooltip="Click to open Interchangeability DTO Work Menu" data-position="top center">
                <i class="exchange icon"></i>
                Interchangeability DTO
            </div>
        </div>

        <div id="projectWorkTableSearch" style="text-align: center; margin: 1rem 0;">
            <div class="ui icon input" style="width:25%!important;">
                <label for="search-tables"></label>
                <input type="search" name="search-tables" id="search-tables" placeholder="Search All Tables">
                <i class="search icon"></i>
            </div>
        </div>

        <div id="projectWorkTableContent">
            <?php include_once 'tables/not-defined-work-center.php'; ?>
            <?php include_once 'tables/work-list.php'; ?>
            <?php include_once 'tables/extension-dto-list.php'; ?>
            <?php include_once 'tables/not-found-nachbau.php'; ?>
            <?php include_once 'tables/tkforms-not-exist.php'; ?>
        </div>
    </div>

    <button id="goToTopButton" class="ui circular icon button" style="display:none;">
        <i class="arrow up icon"></i>
    </button>

    <?php include_once 'spare-part-dto-work-modal.php'; ?>
    <?php include_once 'minus-price-dto-modal.php'; ?>
    <?php include_once 'interchange-dto-work-modal.php'; ?>
</div>


<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/project-work/project-work.js?<?=uniqid()?>"></script>
<!--tables scripts-->
<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/project-work/tables/not-defined-work-center.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/project-work/tables/work-list.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/project-work/tables/not-found-nachbau.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/project-work/tables/tkforms-not-exist.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/project-work/tables/extension-dto-list.js?<?=uniqid()?>"></script>
