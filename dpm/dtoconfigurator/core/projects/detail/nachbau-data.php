<link href="/dpm/dtoconfigurator/assets/css/projects/detail/nachbau-data.css" rel="stylesheet" type="text/css"/>

<div id="fetchNachbauLoader" class="ui icon warning message" style="display:none;width:25%;">
    <i class="notched circle loading icon"></i>
    <div class="content">
        <div class="header">
            Please Wait
        </div>
        <p>
            We are fetching nachbau data for you. <br>
            This operation can take a while...
        </p>
    </div>
</div>

<div id="nachbauFilterAccordion" class="ui styled fluid accordion" style="display:none;">
    <!-- Accordion Title -->
    <div class="title active">
        <i class="dropdown icon"></i>
        Nachbau Filter Sections
    </div>

    <!-- Accordion Content -->
    <div class="content active">
        <div id="nachbauDataContainer" style="display:none;">
            <div id="dtoNotFoundMsg" class="ui red compact message hidden">
                <i class="exclamation circle icon"></i><span></span>
            </div>

            <!--    NACHBAU FILTER SECTIONS BELOW  -->
            <div id="nachbauData" style="display:none;">
                <div id="nachbauList">
                    <h4>Nachbau Files</h4>
                    <div class="ui vertical menu scroller nachbau-no-filters"></div>
                </div>

                <div id="listType" style="display:none;">
                    <h4 style="margin-top:0;">List Types</h4>
                    <div class="ui vertical menu ">
                        <a class="item" data-value="Typical">Typical Based List (<span id="typicalCount"></span>)</a>
                        <a class="item" data-value="Panel">Panel Based List (<span id="panelCount"></span>)</a>
                        <a class="item" data-value="Accessories">Accessory List (<span id="accessoryCount"></span>)</a>
                    </div>
                </div>

                <div id="typicalAndPanels" style="display:none;">
                    <h4 style="margin-top:0;">Typical / Panel List</h4>
                    <div class="ui vertical menu scroller">
                        <!-- Dynamically add data -->
                    </div>
                </div>

                <div id="dtoNumbers" style="display:none;">
                    <h4 style="margin-top:0;">DTO Numbers</h4>
                    <div class="ui vertical menu scroller">
                        <!-- Dynamically add data -->
                    </div>
                </div>

                <div id="dtoDescription" style="display:none;">
                    <h4 style="margin-top:0;">DTO Description</h4>
                    <div class="ui vertical menu scroller"></div>
                </div>
                <div class="ui active inline indeterminate text loader" style="margin-top:2%;"><b>Loading Data</b></div>
            </div>
        </div>
    </div>
</div>

<script src="/dpm/dtoconfigurator/assets/js/projects/detail/nachbau-data.js?<?=uniqid()?>"></script>