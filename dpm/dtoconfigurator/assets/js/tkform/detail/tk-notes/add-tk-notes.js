$(document).ready(async function() {
    $('#addTkNotePage .loader').hide();
    $('#addTkNoteTkNoteContainer').transition('zoom');

    $('input:text').click(function() {
        $(this).parent().find("input:file").click();
    });

    $('#searchPictureButton').click(function() {
        $(this).parent().find("input:file").click();
    });

    $('input:file', '#searchPictureButton').on('change', function(e) {
        let name = e.target.files[0]?.name || '';
        $('input:text', $(e.target).parent()).val(name);
    });

    const counts = await fetchCountOfNcAndTkNotesOfTkForm();
    document.getElementById('ncListCount').innerText = counts.nc_count;
    document.getElementById('tkNotesCount').innerText = counts.tk_notes_count;

    if (parseInt(counts.nc_count) > 0)
        document.getElementById('ncListCount').classList.add('brown');
    if (parseInt(counts.tk_notes_count) > 0)
        document.getElementById('tkNotesCount').classList.add('brown');

});


$('#saveTkNote').on('click', async function () {
    const formElement = document.getElementById('addTkNoteForm');
    const formData = new FormData(formElement);

    const note = formData.get('note');
    if (!note || note.trim() === '') {
        $('#addTkNoteErrorMsg').removeClass('hidden').show();
        return;
    }

    formData.append('action', 'addNoteToTkForm');
    formData.append('id', new URLSearchParams(window.location.search).get('id'));

    try {
        const response = await axios.post('/dpm/dtoconfigurator/api/controllers/TkFormNotesController.php?', formData, { headers: { 'Content-Type': 'multipart/form-data' }} );

        if (response.status === 200) {
            fireToastr('success', 'Note saved successfully.');

            const urlParams = new URLSearchParams(window.location.search);
            const id = urlParams.get('id');
            const documentNumber = urlParams.get('document-number');
            const dtoNumber = urlParams.get('dto-number');
            window.location.href = `/dpm/dtoconfigurator/core/tkform/detail/tk-notes/tk-notes.php?id=${id}&document-number=${documentNumber}&dto-number=${dtoNumber}`;
        }
    } catch (error) {
        if (error.response.status === 400)
                fireToastr('error', error.response.data.message);
        else
            fireToastr('error', 'An unexpected error has occurred. Please try again later.');
    }
});

