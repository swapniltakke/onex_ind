$(document).ready(function() {
    prepareChooseImageSelect();
});

async function fetchBomNotesByTkFormId() {
    try {
        const projectNo = getUrlParam('project-no');
        const nachbauNo = getUrlParam('nachbau-no');

        const url = '/dpm/dtoconfigurator/api/controllers/BomNotesController.php';
        const response = await axios.get(url, {
            params: { action: 'getAllBomNotesByProjectAndNachbau', projectNo: projectNo, nachbauNo: nachbauNo },
            headers: { "Content-Type": "multipart/form-data" },
        });

        return response.data;
    } catch (error) {
        fireToastr('error', 'Error fetching TK Notes:', error);
        return [];
    }
}

async function prepareBomNotesContent() {
    const bomNotes = await fetchBomNotesByTkFormId();
    const bomNoteItemList = document.getElementById('bomNoteItemList');

    bomNoteItemList.innerHTML = '';
    hideElement('#bomNotesDataContent');
    hideElement('#bomNoteList');

    if (bomNotes.length > 0) {
        hideElement('#bomNotesCheckMsg');

        bomNotes.forEach(note => {
            bomNoteItemList.innerHTML += `
                <div class="item">
                    <div class="ui medium image" data-tooltip="Click on image to see on full screen" data-position="top center">
                      <a id="openDeleteBomNoteModal" data-note-id="${note.id}" data-note-content="${note.note}" 
                         class="ui red left corner label" style="cursor:pointer;">
                        <i class="trash icon"></i>
                      </a>
                       <img src="/dpm/dtoconfigurator/partials/getNoteImages?type=1&file=${note.file_name}" 
                             class="enlargeable-image" 
                             style="width:200px;margin:0 auto;cursor:pointer;"
                             onerror="this.onerror=null;this.src='/dpm/dtoconfigurator/assets/images/image-not-found.jpg';">
                    </div>
                    <div class="content">
                        <a id="bomNoteContent" class="header">${note.note}</a>
                        <div class="extra">
                            <div id="bomNoteCreatedBy" class="cinema">${note.user_created}</span>
                        </div>
                        <div class="extra">
                            <div id="bomNoteCreatedDate" class="ui label">${moment(note.created).format('DD.MM.YYYY HH:mm')}</div>
                        </div>
                    </div>
                </div>`;
        });

        $('#bomNoteList').transition('pulse');
    } else {
        $('#bomNoteList').attr('style', 'display: none !important');
        showElement('#bomNotesCheckMsg');
    }

    hideLoader('#bomNotes');
    showElement('#bomNotesDataContent')
    showElement('#bomNotesContainer')
}

$(document).on('click', '#openDeleteBomNoteModal', function () {
    const noteId = $(this).data('note-id');
    const noteContent = $(this).data('note-content');

    $('#deleteBomNoteModal').data('note-id', noteId).modal({ blurring: true }).modal('show');
    $('#deleteBomNoteModalContent').text(noteContent);
});

$(document).on('click', '#deleteBomNoteModal .approve.button', async function () {
    const noteId = $('#deleteBomNoteModal').data('note-id');

    try {
        const response = await axios.post('/dpm/dtoconfigurator/api/controllers/BomNotesController.php?',
            { action: 'deleteBomNoteById', noteId: noteId },
            { headers: { 'Content-Type': 'multipart/form-data' }}
        );

        if (response.status === 200) {
            fireToastr('success', 'Note deleted successfully');
            $('#deleteBomNoteModal').modal('hide');

            // Remove the deleted note from the DOM
            $(`#openDeleteBomNoteModal[data-note-id="${noteId}"]`).closest('.item').remove();

            // Check if there are any items left in the list
            if ($('#bomNoteItemList .item').length === 0) {
                await prepareBomNotesContent();
                showElement('#bomNotesContainer')
                $('#bomNoteList').transition('pulse');
            }
        } else {
            fireToastr('error', 'An unexpected error occurs while removing Bom Note.');
            console.error('Failed to delete note');
        }
    } catch (error) {
        fireToastr('error', 'An unexpected error occurs while removing Bom Note.');
        console.error('Error deleting note:', error);
    }
});

$('#saveBomNote').on('click', async function () {
    $('#saveBomNote').addClass('loading disabled');
        hideElement('#addBomNoteErrorMsg');
        const formElement = document.getElementById('addBomNoteForm');
        const formData = new FormData(formElement);

        const note = formData.get('note');
        if (!note || note.trim() === '') {
            $('#addBomNoteErrorMsg').removeClass('hidden').show();
            $('#saveBomNote').removeClass('loading disabled');
            return;
        }
        const imageFileInput = $('#bomNoteImageFileInput');
        const imageFileUploaded = imageFileInput.length > 0 && imageFileInput[0].files.length > 0;

        formData.append('action', 'addBomNoteToProject');
        formData.append('projectNo', getUrlParam('project-no'));
        formData.append('nachbauNo', getUrlParam('nachbau-no'));
        formData.append('imageFileUploaded', imageFileUploaded ? 'true' : 'false');

    try {
        const response = await axios.post('/dpm/dtoconfigurator/api/controllers/BomNotesController.php?', formData, { headers: { 'Content-Type': 'multipart/form-data' }} );

        if (response.status === 200) {
            fireToastr('success', 'Bom Note saved successfully.');
            await prepareBomNotesContent();
            $('#addBomNoteForm')[0].reset();
        }
    } catch (error) {
        if (error.response.status === 400)
            fireToastr('error', error.response.data.message);
        else
            fireToastr('error', 'An unexpected error has occurred. Please try again later.');
    } finally {
        $('#saveBomNote').removeClass('loading disabled');
    }
});

function prepareChooseImageSelect() {
    $('input:text').click(function () {
        $(this).siblings("input:file").click();
    });

    $('#searchPictureBtn').click(function () {
        $(this).siblings("input:file").click();
    });

    $('input:file').on('change', function (e) {
        let fileName = e.target.files[0]?.name || '';
        $(this).siblings('input:text').val(fileName);
    });
}