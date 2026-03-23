<div id="nachbauErrorModal" class="ui modal fullscreen" style="text-align:center;">
    <div class="header">Add Nachbau Error</div>
    <div class="scrolling content">
        <div id="nachbauErrorModalContent" class="ui grid">
            <div class="sixteen wide column">
                <div class="ui grid centered">
                    <!-- Added Material -->
                    <div id="addedMaterialField" class="six wide column">
                        <label for="addedMaterial">Added Material</label>
                        <select id="addedMaterial" name="addedMaterial" class="ui fluid selection search dropdown">
                            <option value="">Select Added Material</option>
                        </select>
                    </div>
                    <!-- Deleted Material -->
                    <div id="deletedMaterialField" class="six wide column field">
                        <label for="deletedMaterial">Deleted Material</label>
                        <select id="deletedMaterial" name="deletedMaterial" class="ui fluid selection search dropdown">
                            <option value="">Select Deleted Material</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Two boxes with transfer button -->
            <div id="nachbauErrorBoxes" class="sixteen wide column"  style="overflow:auto">
                <div id="operationTitleDiv" class="ui compact message info" style="display:none;">
                    <h3 id="operationTitle" style="display: block;margin: 0 auto;"></h3>
                </div>
                <div class="ui grid centered middle aligned" style="margin-top:25px;">
                    <!-- Left Box -->
                    <div class="six wide column" style="padding:0;">
                        <h4 style="text-align:center;">Typical/Panel List</h4>
                        <div id="itemList" class="ui segment vertical menu">
                            <div id="itemListInfoMsg" class="ui compact message warning" style="width: 50%;text-align: center;margin: 0 auto;display: block;">
                                <h4>Please select a material</h4>
                            </div>
                            <!-- Items will be dynamically populated -->
                        </div>
                    </div>

                    <!-- Transfer Button -->
                    <div class="one wide column center aligned">
                        <button id="transferButton" class="ui circular icon button">
                            <i class="exchange icon"></i>
                        </button>
                    </div>

                    <!-- Right Box -->
                    <div class="six wide column" style="padding:0;">
                        <h4 style="text-align:center;">Additions</h4>
                        <div id="selectedItems" class="ui segment vertical menu">
                            <div id="selectedItemsInfoMsg" class="ui compact message warning" style="width: 50%;text-align: center;margin: 0 auto;display: block;">
                                <h4>Please select a material</h4>
                            </div>
                            <!-- Selected items will appear here -->
                        </div>
                    </div>
                </div>
            </div>


            <div id="quantityAndUnitDiv" class="sixteen wide column hide-important" style="display: flex;justify-content: center;gap: 20px;width: 35% !important;margin: 0 auto;">
                <!-- Quantity and Unit Column -->
                <div class="three wide column" style="text-align: center;">
                    <div class="ui form">
                        <div class="field">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" placeholder="1" value="1">
                        </div>
                        <div class="field" style="margin-top: 10px;">
                            <label for="unit">Unit:</label>
                            <input type="text" id="unit" name="unit" placeholder="ST" value="ST">
                        </div>
                    </div>
                </div>

                <!-- Notes Column -->
                <div class="five wide column" style="text-align: center; flex-grow: 1;"> <!-- Added flex-grow to expand dynamically -->
                    <div class="ui form">
                        <div class="field" style="margin-top: 10px;">
                            <label for="notes">Notes:</label>
                            <textarea id="notes" name="notes" rows="4" placeholder="Enter notes" style="width: 100%; max-width: 100%;">NACHBAU ERROR</textarea>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Buttons -->
    <div class="sixteen wide column" style="text-align: center; margin-top: 20px;padding-bottom:20px;">
        <div class="actions footer">
            <button class="ui cancel button">Cancel</button>
            <div id="saveNachbauErrorChanges" class="ui animated positive fade button">
                <div class="visible content">Save Changes</div>
                <div class="hidden content">
                    <i class="save icon"></i>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/order-summary/nachbau-error.js?<?=uniqid()?>"></script>
