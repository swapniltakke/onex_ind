<div id="workListContainer" class="ui segment container-fluid" style="margin-top:1.5rem;">
    <h3 class="ui header">
        <i class="wpforms icon"></i>
        <div class="content">
            Work List
             <div class="sub header">Material lists required to prepare the BOM Change Excel are in the table below</div>
        </div>
    </h3>
    <table id="workListDataTable" class="ui striped celled table stackable compact padded">
        <thead>
        <tr>
            <th>Type Numbers</th>
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

    <div id="addAccessoryModal" class="ui small modal">
        <div class="header">Add Accessory List</div>
        <div class="content">
            <div class="ui warning small message">
                <i class="info circle icon"></i>
                <strong>This list will be added as the <span style="color: #2185d0;">Accessory List</span> based on the selection below.</strong>
            </div>
            <div class="ui form">
                <div class="grouped fields radioButtonItems"></div>
            </div>
        </div>
        <div class="actions">
            <div class="ui red cancel button">Cancel</div>
            <div class="ui green approve button" style="margin:0;">Add Accessory</div>
        </div>
    </div>

    <div id="chooseDeviceKmatModal" class="ui small modal">
        <div class="header">Select Device KMAT</div>
        <div class="content">
            <div class="ui warning small message">
                <i class="info circle icon"></i>
                <strong>This device will be added to KMAT which will be selected below.</strong>
            </div>
            <div class="ui form">
                <div class="grouped fields deviceKmatsRadioButtonItems"></div>
            </div>
        </div>
        <div class="actions footer" style="display:flex; justify-content:space-between;">
            <div class="ui cancel button">Cancel</div>
            <div class="ui green approve button" style="margin:0;">Add</div>
        </div>
    </div>

</div>

