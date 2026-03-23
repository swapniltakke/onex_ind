<div id="specialDtoModal" class="ui modal fullscreen">
    <i class="close icon"></i>
    <div id="specialDtoModalHeader" class="header" style="text-align:center;"></div>
    <div class="content">
        <div id="specialDtoModalContent" class="ui grid">
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
                <div id="specialNoteDiv" class="ui info small message compact" style="text-align: center;display: flex;justify-content: center;width: 75%;margin: auto;">
                    <i class="info circle icon"></i><span class="special-note"></span>
                </div>

                <div class="ui container-fluid center aligned" style="display: flex;justify-content: space-between;">
                    <div id="removeSpecialWorksButton" class="ui animated negative fade button">
                        <div class="visible content">Remove Changes</div>
                        <div class="hidden content">
                            <i class="trash icon"></i>
                        </div>
                    </div>
                    <div id="saveSpecialWorksButton" class="ui animated green fade button">
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


<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/order-summary/special-dto-work.js?<?=uniqid()?>"></script>
