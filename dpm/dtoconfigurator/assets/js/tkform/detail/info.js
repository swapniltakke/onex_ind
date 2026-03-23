$(document).ready(async function() {
    const counts = await fetchCountOfNcAndTkNotesOfTkForm();
    document.getElementById('ncListCount').innerText = counts.nc_count;
    document.getElementById('tkNotesCount').innerText = counts.tk_notes_count;

    if (parseInt(counts.nc_count) > 0)
        document.getElementById('ncListCount').classList.add('brown');
    if (parseInt(counts.tk_notes_count) > 0)
        document.getElementById('tkNotesCount').classList.add('brown');

    await fetchTkFormInfo();
});

async function fetchTkFormInfo() {
    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get('id');
    const documentNumber = urlParams.get('document-number');
    const dtoNumber = urlParams.get('dto-number');

    const url = `/dpm/dtoconfigurator/api/controllers/TkFormController.php?action=getTkForm&id=${id}&document-number=${documentNumber}&dto-number=${dtoNumber}`;

    try {
        const response = await axios.get(url, { id: id, documentNumber: documentNumber, dtoNumber: dtoNumber }, { headers: { "Content-Type": "multipart/form-data" } });

        if (response.status === 200) {
            const tkformData = response.data;

            document.getElementById('createdDate').value = moment(tkformData.created).format('DD.MM.YYYY HH:mm');
            document.getElementById('createdBy').value = tkformData.created_name;
            document.getElementById('lastUpdatedDate').value = moment(tkformData.updated).format('DD.MM.YYYY HH:mm');
            document.getElementById('updatedBy').value = tkformData.updated_name;
            document.getElementById('documentNumber').value = tkformData.document_number;
            document.getElementById('dtoNumber').value = tkformData.dto_number;
            document.getElementById('description').value = decodeHtmlEntities(tkformData.description);
            document.getElementById('descriptionTr').value = decodeHtmlEntities(tkformData.description_tr);

            $('#tkInfoPage .loader').hide();
            $('#tkInfoContainer').transition('zoom');
        } else {
            fireToastr('warning', 'TK Form not found.');
        }
    } catch (error) {
        fireToastr('error', 'An error occurred while fetching the TK Form.');
    }
}

$('#updateTkButton').on('click', async function() {
    $('#updateTkButton').addClass('loading disabled');
    hideMessages();

    const formElement = document.getElementById('tkformInfoForm');
    const formData = new FormData(formElement);
    formData.append('id', new URLSearchParams(window.location.search).get('id'));
    formData.append('action', 'updateTkForm');

    //Required fields check
    for (let [key, value] of formData.entries()) {
        if (value.trim() === "") {
            $('#updateTkFormError').transition('pulse');
            $('#updateTkButton').removeClass('loading disabled');
            return;
        }
    }

    try {
        const response = await axios.post('/dpm/dtoconfigurator/api/controllers/TkFormController.php', formData, { headers: { 'Content-Type': 'multipart/form-data' }});

        if (response.status === 200) {
            $('#tkInfoContainer').transition('hide');
            $('#tkInfoPage .loader').show();
            await fetchTkFormInfo();
            $('#updateTkFormSuccess').transition('pulse');
            setTimeout(function() { $('#updateTkFormSuccess').transition('fade out'); }, 5000);
        }
    } catch (error) {
        if (error.response) {
            const message = error.response.data.message;

            if (message === 'TK Wrong Entry') {
                $('#documentNumberError').transition('pulse');
            } else if (message === 'DTO Wrong Entry') {
                $('#dtoNumberError').transition('pulse');
            } else if (message === 'DTO Special Characters') {
                $('#dtoNumberWrongEntryError').transition('pulse');
            } else if (message === 'TK Form Exists') {
                $('#updateTkFormError').html(`<p><i class="exclamation circle icon"></i>TK Form is already exists!</p>`).transition('pulse');
            }
        }
    } finally {
        $('#updateTkButton').removeClass('loading disabled');
    }
});


$('#openDeleteTkModal').on('click', async function() {
    const dtoNumber = new URLSearchParams(window.location.search).get('dto-number');

    $('#deleteTkFormModalContent').text(`All works associated with ${dtoNumber} will be deleted!`);
    $('#deleteTkFormModal').modal({ blurring: true }).modal('show');
});

$('#deleteTkFormModal .approve.button').on('click', async function() {
    $('#deleteTkFormModal .approve.button').addClass('loading disabled');

    const requestData = {
        id: new URLSearchParams(window.location.search).get('id'),
        documentNumber: new URLSearchParams(window.location.search).get('document-number'),
        dtoNumber: new URLSearchParams(window.location.search).get('dto-number'),
        action: 'deleteTkForm'
    }

    try {
        const response = await axios.post('/dpm/dtoconfigurator/api/controllers/TkFormController.php', requestData, { headers: { 'Content-Type': 'multipart/form-data' }});
        if (response.status === 200) {
            $('#deleteTkFormModal').hide();
            $('#tkformInfoForm').hide();

            let count = 3;
            $('#deleteTkFormSuccess')
                .html(`<i class="check circle icon"></i> TK Form successfully deleted. You will be redirected to the main page in ${count} seconds.`)
                .transition('pulse');

            setTimeout(function() { $('#updateTkFormSuccess').transition('fade out'); }, 6000);
            const countdown = setInterval(() => {
                count--;
                $('#deleteTkFormSuccess').html(`<i class="check circle icon"></i> TK Form successfully deleted. You will be redirected to the main page in <b>${count}</b> seconds.`);

                if (count <= 0) {
                    clearInterval(countdown);
                    window.location.href = '/dpm/dtoconfigurator/core/tkform/index.php';
                }
            }, 1000);
        }
    } catch (error) {
        fireToastr('error', 'An error occurred while deleting the TK Form');
        console.error('Error:', error);
    } finally {
        $('#deleteTkFormModal .approve.button').removeClass('loading disabled');
    }
});


function hideMessages() {
    $('#documentNumberError').hide();
    $('#dtoNumberError').hide();
    $('#dtoNumberWrongEntryError').hide();
    $('#updateTkFormError').hide();
    $('#updateTkFormSuccess').hide();
}
