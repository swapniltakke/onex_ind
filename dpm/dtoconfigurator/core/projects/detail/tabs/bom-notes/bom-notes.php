<link href="/dpm/dtoconfigurator/assets/css/projects/detail/tabs/bom-notes/bom-notes.css" rel="stylesheet" type="text/css"/>

<div class="ui active inline indeterminate text loader" style="display:none;!important;"><b>Loading BOM Notes</b></div>

<div id="bomNotesContainer" class="ui container-fluid">
    <h3 class="ui header" style="margin-top:1rem; margin-bottom: 1rem;">
        <i class="cogs icon"></i>
        <div class="content">
            BOM Notes
            <div class="sub header">Notes in this section will be displayed in general notes cell in BOM Change Excel file.</div>
        </div>
    </h3>

    <div id="bomNotesDataContent" class="ui container-fluid">
        <div id="addBomNoteAccordion" class="sixteen wide column ui accordion" style="margin-bottom:1.5rem;">
            <div class="title">
                <div class="ui instagram dropdown button"><i class="plus circle icon"></i>Add Note</div>
            </div>
            <div class="content ui segment">
                <h4 class="ui horizontal divider header">
                    <i class="sticky note outline icon tiny"></i>
                    Add BOM Note
                </h4>
                <form id="addBomNoteForm" class="ui form">
                    <div class="field">
                        <textarea name="note" rows="2" placeholder="Enter a note"></textarea>
                        <div id="addBomNoteErrorMsg" class="ui pointing red basic label hidden">
                            Note field must be required.
                        </div>
                    </div>

                    <div id="bomNoteChooseImage" class="ui field action input">
                        <input type="text" placeholder="Choose Image" style="cursor:pointer;" readonly>
                        <input id="bomNoteImageFileInput" type="file" name="image" accept=".jpg, .jpeg, .png, .gif">
                        <div id="searchPictureBtn" class="ui icon button">
                            <i class="attach icon"></i>
                        </div>
                    </div>

                    <div class="field">
                        <div id="saveBomNote" class="ui animated green fade button">
                            <div class="visible content">Save Note</div>
                            <div class="hidden content">
                                <i class="save icon"></i>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>

        <h4 class="ui horizontal divider header">
            <i class="sticky note outline icon tiny"></i>
            BOM Notes
        </h4>
        <div id="bomNotesCheckMsg" class="ui yellow compact message" style="margin:0;display:none;">
            <i class="info circle icon"></i>There are no BOM notes exists.
        </div>

        <div id="bomNoteList" class="ui attached segment">
            <div id="bomNoteItemList" class="ui divided items"></div>
            <div id="deleteBomNoteModal" class="ui mini modal">
                <div class="header">Delete Note</div>
                <div class="content">
                    <p>Are you sure you want to delete this note?</p>
                    <p id="deleteBomNoteModalContent" style="font-weight: 700;font-style: italic;font-size: 0.9rem;"></p>
                </div>
                <div class="actions">
                    <div class="ui cancel button">Cancel</div>
                    <div class="ui approve button">Delete</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="ui modal" id="imageEnlargeModal">
    <i class="close icon"></i>
    <div class="content" style="text-align: center;padding: 20px;">
        <img id="enlargedImage" src="" style="max-width: 100%; height: auto; margin: 0 auto; display: block;">
    </div>
</div>


<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/bom-notes/bom-notes.js?<?=uniqid()?>"></script>
