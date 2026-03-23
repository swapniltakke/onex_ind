<div id="chooseInterchangeDtoWorkModal" class="ui modal">
    <div class="header" style="text-align:center;">Select an Interchangeability DTO</div>
    <div class="content">
        <div id="interchangeDtoRadioContainer" class="ui relaxed list"></div>
    </div>

    <div class="actions" style="display: flex;justify-content: space-between;">
        <div class="ui cancel button">Cancel</div>
        <div id="openInterchangeDtoWorkModalButton" class="ui green approve button">Open</div>
    </div>
</div>

<div id="interchangeDtoWorkModal" class="ui fullscreen modal">
    <i class="close icon"></i>
    <div id="interchangeDtoWorkModalHeader" class="header" style="text-align:center;"></div>
    <div class="scrolling content">
        <div id="interchangeDtoMaterialTablesContainer"></div>
    </div>

    <div class="actions footer" style="display: flex;justify-content: space-between;">
        <button class="ui cancel button">Close</button>
        <div id="saveInterchangeDtoChangesBtn" class="ui animated positive fade button">
            <div class="visible content">Save Changes</div>
            <div class="hidden content">
                <i class="save icon"></i>
            </div>
        </div>
    </div>
</div>


<div id="addInterchangeDtoMaterialModal" class="ui small modal">
    <i class="close icon" style="color:#000;"></i>
    <div class="header">Add New Material</div>
    <div class="content" style="background:#eeeeee85">
        <form id="addMaterialForm" class="ui form">
            <div class="field">
                <label>DTO Number</label>
                <input type="text" id="addInterchangeMaterialDtoNumber" readonly disabled>
            </div>
            <div class="field">
                <label>Typical Number</label>
                <input type="text" id="addInterchangeMaterialTypical" readonly disabled>
            </div>
            <div class="field">
                <label>Material Number</label>
                <div class="ui search selection dropdown fluid addInterchangeMaterialNumberSelect" id="addInterchangeMaterialNumberSelect">
                    <input type="hidden" name="material">
                    <i class="dropdown icon"></i>
                    <div class="default text">Select Material</div>
                    <div class="menu"></div>
                </div>
            </div>

            <div class="two fields">
                <div class="field">
                    <label>Quantity</label>
                    <input type="number" id="addInterchangeMaterialQty" min="1" step="1" value="1">
                </div>

                <div class="field">
                    <label>Unit</label>
                    <input type="text" id="addInterchangeMaterialUnit" value="ST">
                </div>
            </div>
        </form>
    </div>
    <div class="actions" style="display:flex;justify-content:space-between;">
        <button class="ui cancel button">Cancel</button>
        <button class="ui green button" id="addInterchangeDtoMaterialBtn">Add Material</button>
    </div>
</div>


<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/project-work/interchange-dto-work-modal.js?<?=uniqid()?>"></script>
