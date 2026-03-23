<div id="extensionDtoListContainer" class="ui segment container-fluid" style="margin-top:1.5rem;">
    <h3 class="ui header">
        <i class="cogs icon"></i>
        <div class="content">
            Extension DTO List
            <div class="sub header">Table to work on Extension DTO's</div>
        </div>
    </h3>
    <table id="extensionDtoListDataTable" class="ui striped celled table stackable compact padded">
        <thead>

        </thead>
        <tbody>
            <!-- Data will be populated here by JavaScript -->
        </tbody>
    </table>
</div>

<div id="extensionDtoModal" class="ui modal fullscreen center aligned">
    <i class="close icon"></i>
    <div id="extensionDtoModalHeader" class="header" style="text-align:center;"></div>
    <div class="scrolling content">
        <div class="ui top attached tabular menu" id="extensionModalTabs" style="display: flex; align-items: center;">
            <!-- Tabs Section -->
            <a class="item active" id="extension-tab" data-tab="extension-content" role="tab" aria-controls="extension-content" aria-selected="true">
                <i class="pencil alternate icon large"></i>
                <span style="font-size:1.1rem;">Extension Work Tab</span>
            </a>

            <a class="item" id="notes-tab" data-tab="notes-content" role="tab" aria-controls="notes-content" aria-selected="false">
                <i class="sticky note icon large"></i>
                <span style="font-size:1.1rem;">Notes</span>
            </a>

            <!-- Material Define Button on the Right -->
            <div id="materialDefineModalBtn" class="ui teal circular icon compact button" style="margin-left: auto; height: 100%;">
                <i class="cubes icon" style="margin-right:0.3rem!important;"></i>
                Material Define
            </div>
        </div>

        <div class="ui bottom attached segment active tab" data-tab="extension-content" id="extension-content" role="tabpanel" aria-labelledby="extension-tab">
            <div id="div-extension-table">
                <table id="extension-table" class="ui celled table w-100">
                    <thead>
                    <tr>
                        <th rowspan="3" class="center aligned" style="font-size:1.3rem;">KMATS</th>
                        <th id="extension-modal-header" style="text-align:center;"></th>
                    </tr>
                    <tr id="extension-modal-subheader"></tr>
                    <tr id="subheader-row">
                        <!-- Accessories and Current Order subheaders will be inserted here -->
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div class="ui bottom attached segment tab" data-tab="notes-content" id="notes-content" role="tabpanel" aria-labelledby="notes-tab">
            <div class="ui form">
                <div class="field">
                    <textarea id="notes-textarea" rows="6" placeholder="Enter your notes here..."></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="actions footer" style="display:none;"></div>
</div>

<div id="extensionDtoKmatListModal" class="ui modal fullscreen center aligned">
    <i class="close icon"></i>
    <div id="extensionDtoKmatListModalHeader" class="header" style="text-align:center;"></div>
    <div class="scrolling content">
        <div style="display: flex; justify-content: space-between; align-items: center;margin-bottom:15px;">
            <div id="addMaterialListToExtensionDto" class="ui green circular icon compact button">
                <i class="plus square icon" style="margin-right:0.3rem!important;"></i>
                Add Extension Material List
            </div>

            <div style="display: flex; gap: 10px;">
                <div id="extensionMaterialDefineModalButton" class="ui teal circular icon compact button">
                    <i class="cubes icon" style="margin-right:0.3rem!important;"></i>
                    Material Define
                </div>
            </div>
        </div>


        <table id="extensionDtoKmatListDataTable" class="ui celled table">
            <thead>
            <tr>
                <th>Detail</th>
                <th>DTO Number</th>
                <th>Added Nr.</th>
                <th>Deleted Nr.</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>KMAT</th>
                <th>Note</th>
            </tr>
            </thead>
            <tbody>
            <!-- Data will be populated here by JavaScript -->
            </tbody>
        </table>
    </div>
    <div class="actions footer" style="display: flex;justify-content: space-between;">
        <div id="removeAllExtensionChangesBtn" class="ui animated negative fade button">
            <div class="visible content">Remove All Changes</div>
            <div class="hidden content">
                <i class="trash icon"></i>
            </div>
        </div>
        <div id="saveExtensionChangesBtn" class="ui animated positive fade button">
            <div class="visible content">Save Changes</div>
            <div class="hidden content">
                <i class="save icon"></i>
            </div>
        </div>
    </div>
</div>

<div id="addExtensionMaterialListModal" class="ui small modal center aligned">
    <i class="close icon"></i>
    <div id="addExtensionMaterialListModalHeader" class="header"></div>
    <div class="content">
        <form class="ui form">
            <div class="field">
                <label>Material Number</label>
                <div class="ui search selection dropdown fluid extension-material-added-select" id="extension-material-added-select">
                    <input type="hidden" name="material">
                    <i class="dropdown icon"></i>
                    <div class="default text">Select Material</div>
                    <div class="menu"></div>
                </div>
            </div>

            <div class="field">
                <label for="extension-material-description">Material Description</label>
                <textarea id="extension-material-description" rows="2" placeholder="Please First Select Material" readonly style="background:whitesmoke;"></textarea>
            </div>

            <div class="field">
                <label for="extension-material-work-center">Station Code</label>
                <input type="text" id="extension-material-work-center" placeholder="Please First Select Material" readonly style="background:whitesmoke;">
            </div>

            <div class="field">
                <label for="extension-material-work-content">Station Name</label>
                <input type="text" id="extension-material-work-content" placeholder="Please First Select Material" readonly style="background:whitesmoke;">
            </div>

            <div class="fields">
                <div class="eight wide field">
                    <label for="extension-typical-number">Typical</label>
                    <input type="text" id="extension-typical-number" readonly style="background:whitesmoke;">
                </div>
                <div class="eight wide field">
                    <label for="extension-panel-number">Panel</label>
                    <input type="text" id="extension-panel-number" readonly style="background:whitesmoke;">
                </div>
            </div>

            <div class="fields">
                <div class="eight wide field">
                    <label for="extension-material-quantity">Quantity</label>
                    <input type="number" id="extension-material-quantity" min="1" value="1">
                </div>
                <div class="eight wide field">
                    <label for="extension-material-unit">Unit</label>
                    <input type="text" id="extension-material-unit" value="ST" readonly>
                </div>
            </div>

            <div class="field">
                <label for="extension-material-note">Note</label>
                <textarea id="extension-material-note" rows="2" placeholder="Write a note..."></textarea>
            </div>
        </form>
    </div>
    <div class="actions footer" style="display:flex;justify-content:space-between;">
        <div class="ui cancel button">Cancel</div>
        <div id="saveExtensionMaterialListBtn" class="ui animated positive fade button">
            <div class="visible content">Add Material List</div>
            <div class="hidden content">
                <i class="save icon"></i>
            </div>
        </div>
    </div>
</div>


<div id="chooseExtensionMaterialSubWorkCenterModal" class="ui modal center aligned">
    <i class="close icon"></i>
    <div id="chooseExtensionMaterialSubWorkCenterModalHeader" class="header">Choose Sub Work Center</div>
    <div class="content">
        <div class="ui warning small message">
            <i class="info circle icon"></i>
            <strong><span class="currentWorkCenterSpan"></span></strong> has sub kmat values. Please choose one of them to add under it's KMAT.
        </div>
        <div class="ui form">
            <div class="grouped fields subWorkCenterRadioItems"></div>
        </div>
    </div>
    <div class="actions footer">
        <div class="ui cancel button">Cancel</div>
        <div id="chooseExtensionMaterialSubWorkCenterConfirmBtn" class="ui animated positive fade button">
            <div class="visible content">Add Material List</div>
            <div class="hidden content">
                <i class="save icon"></i>
            </div>
        </div>
    </div>
</div>

<!-- addExtensionTypicalModal -->
<div id="addExtensionTypicalModal" class="ui mini modal">
    <i class="close icon"></i>
    <div class="header">
        <i class="plus icon"></i>
        Add Typical to Extension DTO
    </div>
    <div class="content">
        <div class="ui form">
            <div class="field">
                <label>Select Typical</label>
                <div class="ui selection dropdown" id="typicalDropdown">
                    <input type="hidden" name="typical" value="">
                    <i class="dropdown icon"></i>
                    <div class="default text">Select a typical...</div>
                    <div class="menu" id="typicalDropdownMenu">
                        <!-- Options will be populated by JavaScript -->
                    </div>
                </div>
            </div>
            <div class="ui warning message" id="typicalWarningMessage" style="display: none;">
                <i class="warning icon"></i>
                Please select a typical before adding.
            </div>
            <div class="ui error message" id="typicalErrorMessage" style="display: none;">
                <i class="exclamation triangle icon"></i>
                <span id="errorMessageText">An error occurred while adding the typical.</span>
            </div>
        </div>
    </div>
    <div class="actions">
        <div class="ui black deny button">
            <i class="remove icon"></i>
            Cancel
        </div>
        <div class="ui positive right labeled icon button" id="addExtensionTypicalButton">
            <i class="plus icon"></i>
            Add Typical
        </div>
    </div>
</div>

<!--PANEL NAME CHANGE-->
<div id="extensionAccPanelNameChangeModal" class="ui mini modal">
    <i class="close icon" style="color:#000;"></i>
    <div class="header">Update Panel Name</div>
    <div id="extensionAccPanelNameChangeModalContent" class="content"></div>
    <div id="extensionAccPanelNameChangeModalFooter" class="actions" style="display:flex;justify-content:space-between;"></div>
</div>

<!--ADD ACCESSORY NOTE -->
<div id="extensionAccMaterialAddNoteModal" class="ui mini modal">
    <i class="close icon" style="color:#000;"></i>
    <div class="header">Add Accessory Note</div>
    <div id="extensionAccMaterialAddNoteModalContent" class="content"></div>
    <div id="extensionAccMaterialAddNoteModalFooter" class="actions" style="display:flex;justify-content:space-between;"></div>
</div>

<!--EDIT ACCESSORY NOTE-->
<div id="extensionAccMaterialEditNoteModal" class="ui mini modal">
    <i class="close icon" style="color:#000;"></i>
    <div class="header">Edit Accessory Note</div>
    <div id="extensionAccMaterialEditNoteModalContent" class="content"></div>
    <div id="extensionAccMaterialEditNoteModalFooter" class="actions" style="display:flex;justify-content:space-between;"></div>
</div>