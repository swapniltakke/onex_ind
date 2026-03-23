<link href="/dpm/dtoconfigurator/assets/css/projects/detail/tabs/order-summary/order-summary.css" rel="stylesheet" type="text/css"/>

<div class="ui active inline indeterminate text loader" style="display:none;!important;"><b>Loading Order Summary</b></div>

<div id="orderSummaryContainer" class="ui container-fluid">
    <div id="orderSummaryActionButtons">
        <div id="downloadBomExcel" class="ui green circular icon button">
            <i class="download icon" style="margin-right:0.3rem;"></i>
            Download BOM Change Excel
        </div>
        <div id="generateAKDImportXml" class="ui purple circular icon button" style="display:none;">
            <i class="download icon" style="margin-right:0.3rem;"></i>
            AKD Import XML
        </div>
<!--        <div id="nachbauErrorButton" class="ui violet circular icon button">-->
<!--            <i class="bug icon" style="margin-right:0.3rem;"></i>-->
<!--            Add Nachbau Error-->
<!--        </div>-->
    </div>

    <div id="possibleMissingMaterialLists" class="ui grid" style="display:none;">
        <div id class="sixteen wide column">
            <div class="ui icon message negative" style="width:100%;">
                <i class="exclamation triangle icon"></i>
                <div class="content">
                    <div class="header">
                        Attention
                    </div>
                    <p>Below materials are <b>already added</b> but <b>not appear</b> in below Order Summary table. Please contact with DGT Team!</p>
                </div>
            </div>

            <table id="missingMaterialListTable" class="ui celled table" style="text-align:center;">
                <thead>
                    <tr>
                        <th>DTO Number</th>
                        <th>Added Nr</th>
                        <th>Deleted Nr</th>
                        <th>Item</th>
                        <th>Work Center</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div id="dtFilteringSection" class="ui grid">
        <!-- Left-aligned filters -->
        <div class="fourteen wide left floated column">
            <div class="dtFiltering ui equal width grid"></div>
            <div class="dtFilteringRow ui equal width grid">
                <div id="orderSummaryTableFiltering" class="dtFiltering ui grid"></div>
            </div>
        </div>

        <!-- Right-aligned clear button -->
        <div class="two wide right floated column" style="display:flex;">
            <button id="clearFiltersButton" class="ui blue circular icon button"
                    data-tooltip="Clear All Filters" data-position="bottom center">
                <i class="trash icon"></i>
                <i class="filter icon"></i>
            </button>
            <button id="scrollToTopButton" class="ui teal circular icon button"
                    data-tooltip="Scroll Top" data-position="bottom center">
                <i class="arrow up icon"></i>
            </button>
        </div>
    </div>



    <div class="ui divider"></div>

    <table id="orderSummaryTable" class="dtFiltering ui celled table striped stackable compact padded hover" style="width:100%">
        <thead>
            <tr>
                <th>Position</th>
                <th>Added Nr.</th>
                <th>Deleted Nr.</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Description</th>
                <th>Typical</th>
                <th>Panel</th>
                <th>Accessory</th>
                <th>Note</th>
                <th>Revision</th>
            </tr>
        </thead>
        <tbody>
        <!-- Data will be populated here by JavaScript -->
        </tbody>
    </table>
</div>

<button id="goToTopButton" class="ui circular icon button" style="display:none;">
    <i class="arrow up icon"></i>
</button>


<?php include_once '../../projects/detail/tabs/order-summary/spare-work.php'; ?>
<?php include_once '../../projects/detail/tabs/order-summary/nachbau-error.php'; ?>
<?php include_once '../../projects/detail/tabs/order-summary/special-dto-work.php'; ?>

<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/order-summary/filtering.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/order-summary/order-summary.js?<?=uniqid()?>"></script>


