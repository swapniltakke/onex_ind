$(document).ready(async function() {
    const counts = await fetchCountOfNcAndTkNotesOfTkForm();
    document.getElementById('ncListCount').innerText = counts.nc_count;
    document.getElementById('tkNotesCount').innerText = counts.tk_notes_count;

    if (parseInt(counts.nc_count) > 0)
        document.getElementById('ncListCount').classList.add('brown');
    if (parseInt(counts.tk_notes_count) > 0)
        document.getElementById('tkNotesCount').classList.add('brown');
});

async function fetchTkNotesByTkFormId() {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const id = urlParams.get('id');

        const url = `/dpm/dtoconfigurator/api/controllers/TkFormNotesController.php?action=getAllTkFormNotesByTkFormId&id=${id}`;
        const response = await axios.get(url, { id: id }, { headers: { "Content-Type": "multipart/form-data" } });

        return response.data;
    } catch (error) {
        fireToastr('error', 'Error fetching TK Notes:', error);
        return [];
    }
}

async function prepareTkNotesContent() {
    const tkNotes = await fetchTkNotesByTkFormId();
    const tkNoteItemList = document.getElementById('tkNoteItemList');

    if (tkNotes.length !== 0) {
        tkNotes.forEach(note => {
            tkNoteItemList.innerHTML += `
                <div class="item">
                    <div class="ui medium image" data-tooltip="Click on image to see on full screen" data-position="top center">
                      <a id="openDeleteNoteModal" data-note-id="${note.id}" data-note-content="${note.note}" 
                         class="ui red left corner label" style="cursor:pointer;">
                        <i class="trash icon"></i>
                      </a>
                      <img src="/dpm/dtoconfigurator/partials/getNoteImages?type=2&file=${note.file_name}" class="enlargeable-image" 
                           data-tooltip="Click on image to see on full screen" data-position="top center" style="width:200px;margin:0 auto;cursor:pointer;">
                    </div>
                    <div class="content">
                        <a id="noteContent" class="header">${note.note}</a>
                        <div class="extra">
                            <div id="noteCreatedBy" class="cinema">${note.user_created}</span>
                        </div>
                        <div class="extra">
                            <div id="noteCreatedDate" class="ui label">${moment(note.created).format('DD.MM.YYYY HH:mm')}</div>
                        </div>
                    </div>
                </div>`;
        });
    } else {
        $('#tkNotesContainer .segment').hide();
        $('#tkNotesCheckMsg').show();
    }

    $('#tkNotesPage .loader').hide();
    $('#tkNotesContainer').transition('zoom');
}

prepareTkNotesContent();

$(document).on('click', '#addNewTkNoteBtn', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    const documentNumber = urlParams.get('document-number');
    const dtoNumber = urlParams.get('dto-number');

    window.location.href = `/dpm/dtoconfigurator/core/tkform/detail/tk-notes/add-tk-note.php?id=${id}&document-number=${documentNumber}&dto-number=${dtoNumber}`;
});

$(document).on('click', '#openDeleteNoteModal', function () {
    const noteId = $(this).data('note-id');
    const noteContent = $(this).data('note-content');

    $('#deleteTkNoteModal').data('note-id', noteId).modal({ blurring: true }).modal('show');
    $('#deleteTkNoteContent').text(noteContent);
});


$(document).on('click', '#deleteTkNoteModal .approve.button', async function () {
    const noteId = $('#deleteTkNoteModal').data('note-id');

    try {
        const response = await axios.post('/dpm/dtoconfigurator/api/controllers/TkFormNotesController.php?',
            { action: 'deleteTkNoteById', noteId: noteId },
            { headers: { 'Content-Type': 'multipart/form-data' }}
        );

        if (response.status === 200) {
            fireToastr('success', 'Note deleted successfully');
            $('#deleteTkNoteModal').modal('hide');

            // Remove the deleted note from the DOM
            $(`#openDeleteNoteModal[data-note-id="${noteId}"]`).closest('.item').remove();

            // Check if there are any items left in the list
            if ($('#tkNoteItemList .item').length === 0)
                location.reload();
        } else {
            fireToastr('error', 'An unexpected error has occurred. Please try again later.');
            console.error('Failed to delete note');
        }
    } catch (error) {
        fireToastr('error', 'An unexpected error has occurred. Please try again later.');
        console.error('Error deleting note:', error);
    }
});
