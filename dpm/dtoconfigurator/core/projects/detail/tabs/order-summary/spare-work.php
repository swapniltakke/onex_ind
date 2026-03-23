<div id="spareDtoModal" class="ui modal fullscreen">
    <i class="close icon"></i>
    <div id="spareDtoModalHeader" class="header" style="text-align:center;"></div>
    <div class="content">
        <div id="spareDtoModalContent" class="ui grid">
            <div class="sixteen wide column">
                <div class="ui three column grid">

                    <!-- Left side for available material list -->
                    <div class="seven wide column border-right">
                        <h5 class="ui header" style="text-align:center;">
                            PA Withdrawable Unit Alt Listeleri (<span id="available-materials-count">0</span>)
                        </h5>
                        <div class="ui segment">
                            <div id="available-materials" class="ui vertical menu" style="width:100%;">
                                <!-- Material list items will be populated here -->
                            </div>
                        </div>
                    </div>

                    <!-- Middle for move button -->
                    <div id="moveMaterialDiv" class="two wide column center aligned">
                        <button id="move-material" class="ui icon button">
                            <i class="chevron left icon"></i>
                            <i class="chevron right icon"></i>
                        </button>
                    </div>

                    <!-- Right side for selected material list -->
                    <div class="seven wide column">
                        <h5 class="ui header" style="text-align:center;">
                            Aksesuara Gönderilecek Listeler (<span id="selected-materials-count">0</span>)
                        </h5>
                        <div class="ui segment">
                            <div id="selected-materials" class="ui vertical menu" style="width:100%;">
                                <!-- Selected material items will be populated here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sixteen wide column">
                <div id="spareWdaNoteDiv" class="ui info small message compact">
                    <i class="info circle icon"></i><span class="spare-wda-note"></span>
                </div>

                <div class="ui form spare-wda-quantity">
                    <div class="inline fields center aligned" style="display: flex; justify-content: center; margin-top: 15px;">
                        <label for="spare-wda-quantity-input" data-tooltip="Each selected list will be added by multiplying it with the entered quantity coefficient.">
                            Select Quantity Coefficient:
                        </label>
                        <input type="number" id="spare-wda-quantity-input" class="ui input" min="1" value="1">
                    </div>
                </div>

                <div class="ui container-fluid center aligned" style="display: flex;justify-content: space-between;">
                    <div id="removeSpareWorksButton" class="ui animated negative fade button">
                        <div class="visible content">Remove Changes</div>
                        <div class="hidden content">
                            <i class="trash icon"></i>
                        </div>
                    </div>
                    <div id="saveSpareWorksButton" class="ui animated green fade button">
                        <div class="visible content">Save Changes</div>
                        <div class="hidden content">
                            <i class="save icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="dtoSelectionModal" class="ui small modal">
    <div class="header center aligned">Select DTO for <span class="spareTypicalNo"></span></div>
    <div class="content">
        <div class="ui warning small message">
            <i class="info circle icon"></i>
            <strong>Please select the Spare DTO you want to work on for the typical <span class="spareTypicalNo" style="color: #2185d0;"></span></strong>
        </div>
        <div class="ui form">
            <div class="grouped fields radioButtonItems"></div>
        </div>
    </div>
    <div class="actions">
        <div class="ui red cancel button">Cancel</div>
        <div class="ui green approve button" style="margin:0;">Open Spare Work Modal</div>
    </div>
</div>



<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/order-summary/spare-work.js?<?=uniqid()?>"></script>
