<div id="sparePartDtoSelectionModal" class="ui small modal">
    <div class="content">
        <div class="ui warning small message">
            <i class="info circle icon"></i>
            <strong>Please select from below the Spare Part DTO you want to work on...</strong>
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

<div id="sparePartDtoWorkMenuModal" class="ui modal fullscreen">
    <i class="close icon"></i>
    <div id="sparePartDtoWorkHeader" class="header" style="text-align:center;"></div>
    <div class="scrolling content">
        <div id="sparePartDtoModalContent" class="ui grid sixteen wide column" style="display:none;">
            <div id="sparePartDtoKmatsDataTableContainer" class="nine wide column">
                <table id="sparePartDtoKmatsDataTable" class="ui striped celled table" style="width:100%">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Position</th>
                            <th>KMAT</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Typical</th>
                            <th>Panel</th>
                            <th></th>
                        </tr>
                    </thead>
                        <tbody>
                            <!-- Data will be populated here by JavaScript -->
                        </tbody>
                </table>
            </div>
            <div id="sparePartDtoSelectedItems" class="seven wide column">
                <h5 class="ui header" style="text-align:center;">
                    Aksesuara Gönderilen Listeler
                </h5>
                <div class="ui segment">
                    <div id="spare-part-selected-materials" class="ui vertical menu" style="width:100%;">
                        <!-- Selected material items will be populated here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="sparePartQuantityModal" class="ui mini modal">
    <i class="close icon" style="color:#000;"></i>
    <div class="content">
        <div class="ui form spare-part-quantity">
            <label for="spare-part-quantity-input" style="font-weight:bold;">
                Select Quantity
            </label>
            <input type="number" id="spare-part-quantity-input" style="margin-top:8px;" class="ui input" min="1" value="1">
        </div>
    </div>
    <div class="actions">
        <div class="ui red cancel button">Cancel</div>
        <div id="addSparePartToSelectedItems" class="ui green approve button" style="margin:0;">Add</div>
    </div>
</div>

<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/project-work/spare-part-dto-work-modal.js?<?=uniqid()?>"></script>
