<link href="/dpm/dtoconfigurator/assets/css/projects/detail/tabs/order-summary/order-summary-v2.css" rel="stylesheet" type="text/css"/>

<div class="ui active inline indeterminate text loader" style="display:none;!important;"><b>Loading Order Summary</b></div>

<div id="orderSummaryV2Container" class="pusher" style="display:none;!important;">

    <div id="orderSummaryV2ActionButtons">
        <div class="left-buttons">
            <div id="downloadBomExcel" class="ui green circular icon button">
                <i class="download icon" style="margin-right:0.3rem;"></i>
                Download BOM Change Excel
            </div>
        </div>

        <div class="right-buttons">
            <div id="generateAKDImportXml" class="ui purple circular icon button" style="display:none;">
                <i class="download icon" style="margin-right:0.3rem;"></i>
                AKD Import XML
            </div>
            <button id="scrollToTopButton" class="ui teal circular icon button"
                    data-tooltip="Scroll Top" data-position="bottom center">
                <i class="arrow up icon"></i>
            </button>
            <div id="nachbauErrorButton" class="ui violet circular icon button">
                <i class="bug icon" style="margin-right:0.3rem;"></i>
                Add Nachbau Error
            </div>
        </div>
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

    <div id="orderSummaryV2PageContainer" class="ui container-fluid" style="display:none;">

        <h2 id="orderSummaryV2Title" class="ui horizontal divider header" style="margin-top:0!important;">
            Order BOM Change
        </h2>

        <div id="spareDtoExistMsg" class="ui compact message warning small" style="display:none;margin-bottom:1.5rem;">
            <p><i class="info circle icon"></i>Nachbau contains Spare Withdrawable DTO. Check <b>PA Withdrawable Unit KMAT</b> of typicals: <span id="spareDtoExistMsgTypicals" style="font-weight:700;color: #2185d0;"></span></p>
        </div>

        <div id="orderSummaryV2TableContainer" class="sixteen wide column">
            <table id="orderSummaryV2Table" class="ui celled table striped stackable compact padded hover" style="width:100%">
                <thead>
                <tr>
                    <th>Pos</th>
                    <th>Added Nr.</th>
                    <th>Deleted Nr.</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Description</th>
                    <th>Typical</th>
                    <th>Panel</th>
                    <th>Accs</th>
                    <th>Note</th>
                    <th>Revision</th>
                </tr>
                </thead>
                <tbody>
                    <!-- Data will be populated here by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="/dpm/dtoconfigurator/assets/js/main.js?<?=uniqid()?>"></script>
<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/order-summary/order-summary-v2.js?<?=uniqid()?>"></script>
