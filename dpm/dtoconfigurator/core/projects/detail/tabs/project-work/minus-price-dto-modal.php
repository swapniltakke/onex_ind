<div id="minusPriceDtoSelectionModal" class="ui small modal">
    <div class="content">
        <div class="ui warning small message">
            <i class="info circle icon"></i>
            <strong>Please select from below Minus Price DTO you want to work on...</strong>
        </div>
        <div class="ui form">
            <div class="grouped fields mpRadioButtonItems"></div>
        </div>
    </div>
    <div class="actions" style="display:flex;justify-content:space-between;">
        <div class="ui cancel button">Cancel</div>
        <div class="ui green approve button" style="margin:0;">Open</div>
    </div>
</div>


<div id="minusPriceDtoWorkMenuModal" class="ui modal center aligned">
    <i class="close icon"></i>
    <div id="minusPriceDtoWorkHeader" class="header" style="text-align:center;"></div>
    <div id="minusPriceDtoWorkMenuModalContent" class="scrolling content">
        <!-- Tabs Section -->
        <div class="ui top attached tabular menu" id="minusPriceModalTabs" style="display: flex; align-items: center;">

<!--            <a class="item active" id="nachbau-lists-tab" data-tab="nachbau-lists-content" role="tab" aria-controls="nachbau-lists-content" aria-selected="false" style="display:none;">-->
<!--                <i class="sticky note icon large"></i>-->
<!--                <span style="font-size:1.1rem;">Nachbau Materials</span>-->
<!--            </a>-->

            <a class="item active" id="wont-be-produced-tab" data-tab="wont-be-produced-content" role="tab" aria-controls="wont-be-produced-content" aria-selected="true">
                <i class="minus circle icon large"></i>
                <span style="font-size:1.1rem;">Remove From Typical</span>
            </a>
        </div>

<!--        <div class="ui bottom attached segment active tab" data-tab="nachbau-lists-content" id="nachbau-lists-content" role="tabpanel" aria-labelledby="nachbau-lists-tab">-->
<!--            <div id="minusPriceNachbauListTabModalContent" class="ui grid sixteen wide column">-->
<!--                <div id="minusPriceNachbauListDataTableContainer" class="nine wide column">-->
<!--                    <table id="minusPriceNachbauListDataTable" class="ui striped celled table" style="width:100%">-->
<!--                        <thead>-->
<!--                            <tr>-->
<!--                                <th></th>-->
<!--                                <th>Position</th>-->
<!--                                <th>KMAT</th>-->
<!--                                <th>Description</th>-->
<!--                                <th>Quantity</th>-->
<!--                                <th>Unit</th>-->
<!--                                <th>Typical</th>-->
<!--                                <th>Panel</th>-->
<!--                                <th></th>-->
<!--                            </tr>-->
<!--                        </thead>-->
<!--                        <tbody>-->
<!--                         Data will be populated here by JavaScript -->
<!--                        </tbody>-->
<!--                    </table>-->
<!--                </div>-->
<!--                <div id="minusPriceNachbauListSelectedItems" class="seven wide column">-->
<!--                    <h5 class="ui header" style="text-align:center;">-->
<!--                        Material lists that won't be produced-->
<!--                    </h5>-->
<!--                    <div class="ui segment">-->
<!--                        <div id="minus-price-selected-materials" class="ui vertical menu" style="width:100%;">-->
<!--                            Selected material items will be populated here -->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->

        <div class="ui bottom attached segment tab" data-tab="wont-be-produced-content" id="wont-be-produced-content" role="tabpanel" aria-labelledby="wont-be-produced-tab" style="display:block!important;">
            <div id="minusPriceDtoImportantMsg" class="ui icon message negative">
                <i class="warning icon"></i>
                <div class="content">
                    <div class="header">
                        Important Notice
                    </div>
                    <p>
                        DTO number <b><span class="mp-dto-number"> </span></b> exists in the typical <b><span class="mp-dto-typical"></span>.</b> <br>
                        If you select <b>Won't Be Produced</b> checkbox, all material lists under <b><span class="mp-dto-typical"></span></b> will be marked as <b>ÜRETİLMEYECEK</b>.
                    </p>
                </div>
            </div>

            <div id="minusPriceDtoSegment" class="ui red segment" style="text-align:center;">
                <div class="ui checkbox">
                    <input type="checkbox" name="wontBeProduced" id="wontBeProduced">
                    <label>Won't Be Produced</label>
                </div>
            </div>

            <div class="actions footer" style="display:flex; justify-content: space-between;">
                <div class="ui grey deny button">
                    Cancel
                </div>
                <div id="updateMinusPriceDtoChange" class="ui positive right labeled icon button">
                    Save
                    <i class="checkmark icon"></i>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/project-work/minus-price-dto-modal.js?<?=uniqid()?>"></script>
