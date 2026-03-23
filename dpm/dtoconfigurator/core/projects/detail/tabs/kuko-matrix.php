<link href="/dpm/dtoconfigurator/assets/css/projects/detail/tabs/kuko-matrix.css" rel="stylesheet" type="text/css"/>

<div class="ui active inline indeterminate text loader hidden" style="display:none;!important;"><b>Loading Kuko Matrix</b></div>

<div id="kukoMatrixContainer" class="ui container-fluid">
    <div id="kukoMatrixNotFoundMsg" class="ui red compact message" style="margin-bottom:1.5rem;">
        <i class="exclamation circle icon"></i> No Kuko Matrix Data Found.
    </div>

    <div id="specialDtoExistMsg" class="ui compact message positive" style="display:none;">
        <p><i class="exclamation circle icon"></i>There is <b><span class="specialDtoSpan"></span> DTO</b> exists in this project. <b>Don't forget</b> to work them on Project Work page.</p>
    </div><br>

    <div id="kukoMatrixDtoNotWorkMsg" class="ui compact message negative small" style="display:none;">
        <p><i class="info circle icon"></i>DTO numbers excluded from this project will be shown in rows with a red background.</p>
    </div>

    <div id="kukoMatrixDtoMechanicNotWorkMsg" class="ui compact message warning small" style="display:none;">
        <p><i class="info circle icon"></i>Yellow background rows mean that DTO won't be worked by Mechanical Team.</p>
    </div>

    <div id="spareInterchangeBothExistMsg" class="ui icon message warning" style="display:none;">
        <i class="exclamation circle icon"></i>
        <div class="content">
            <div class="header" style="margin-bottom:0.6rem;">
                Important Notice
            </div>
            Nachbau includes both <b>Spare Withdrawable Unit</b> and <b>Interchangeability</b> DTOs. Please start by working on the Interchangeability DTO, and <b>once it's finalized</b>, add the spare withdrawable units accordingly.
        </div>
    </div>

    <div id="kukoMatrixTableDiv" class="sixteen wide column">
        <table id="kukoMatrixTable" class="ui striped celled table stackable compact selectable padded hover">
            <thead></thead>
            <tbody></tbody>
        </table>
    </div>

    <div id="dtoFullDescriptionModal" class="ui mini modal">
        <div class="header"></div>
        <div class="scrolling content"></div>
    </div>

    <div id="addKukoNotesModal" class="ui mini modal">
        <div class="header">Add Kuko Note</div>
        <div class="scrolling content"></div>
        <div class="actions">
            <div class="ui red cancel button">Cancel</div>
            <div id="saveKukoNoteButton" class="ui green button" style="margin:0;">Save Note</div>
        </div>
    </div>

    <div id="kukoNoteDetailsModal" class="ui mini modal">
        <div class="header">Kuko Note Details</div>
        <div class="scrolling content"></div>
        <div class="actions" style="display:flex;justify-content: space-evenly;">
            <div id="deleteKukoNoteButton" class="ui red button">Delete Note</div>
            <div id="updateKukoNoteButton" class="ui green button" style="margin:0;">Update Note</div>
        </div>
    </div>
</div>

<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/kuko-matrix.js?<?=uniqid()?>"></script>
