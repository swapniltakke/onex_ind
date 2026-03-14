<?php
// /dpm/dto_configurator/tabs/kuko-matrix.php
?>

<div id="kukoMatrixContainer" class="ui segment" style="display:none;">
    <!-- Kuko Matrix Header -->
    <div class="ui segment">
        <h3>Kuko Matrix - <span id="kukoMatrixNachbauFile"></span></h3>
        <div class="ui statistics">
            <div class="statistic">
                <div class="value">
                    <i class="database icon"></i><span id="kukoTotalMaterials">0</span>
                </div>
                <div class="label">Total Materials</div>
            </div>
            <div class="statistic">
                <div class="value">
                    <i class="th icon"></i><span id="kukoTotalPanels">0</span>
                </div>
                <div class="label">Panels</div>
            </div>
            <div class="statistic">
                <div class="value">
                    <i class="sitemap icon"></i><span id="kukoTotalFields">0</span>
                </div>
                <div class="label">Fields</div>
            </div>
        </div>
    </div>

    <!-- Panel Filter Tabs -->
    <div class="ui segment">
        <label style="font-weight: bold; margin-bottom: 10px; display: block;">Filter by Panel</label>
        <div id="kukoMatrixPanelTabs" class="panel-tabs"></div>
    </div>

    <!-- Kuko Matrix Table -->
    <div class="ui segment">
        <h4>Materials by Field</h4>
        <table id="kukoMatrixTable" class="ui celled table">
            <thead>
                <tr>
                    <th>Panel</th>
                    <th>Field Name</th>
                    <th>Position</th>
                    <th>Material Number</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Typical No</th>
                </tr>
            </thead>
            <tbody id="kukoMatrixTableBody">
            </tbody>
        </table>
    </div>
</div>

<div id="kukoMatrixErrorDiv" class="ui message error" style="display:none;">
    <p id="kukoMatrixErrorMsg"></p>
</div>

<style>
    .ui.statistics {
        display: flex;
        gap: 20px;
        margin: 20px 0;
    }

    .statistic {
        flex: 1;
        text-align: center;
        padding: 15px;
        background-color: #f5f5f5;
        border-radius: 4px;
    }

    .statistic .value {
        font-size: 24px;
        font-weight: bold;
        color: #00b5ad;
        margin-bottom: 5px;
    }

    .statistic .label {
        font-size: 12px;
        color: #666;
    }

    .panel-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 15px;
    }

    .panel-tab-button {
        padding: 8px 12px;
        background-color: #f0f0f0;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        transition: all 0.3s ease;
    }

    .panel-tab-button:hover {
        background-color: #e0e0e0;
    }

    .panel-tab-button.active {
        background-color: #00b5ad;
        color: white;
        border-color: #00b5ad;
    }
</style>