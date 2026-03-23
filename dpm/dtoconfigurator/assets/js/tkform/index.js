$(document).ready(async function() {
    $('.ui.dropdown').dropdown();
    $('.ui.checkbox').checkbox();
    await initializeTkFormListDataTable();
});

async function fetchAllTkForms() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/TkFormController.php', { params: { action: 'getAllTkForms' }});
        return response.data.data;
    } catch (error) {
        fireToastr('error', 'Error fetching TK forms:', error);
        return [];
    }
}

async function initializeTkFormListDataTable() {
    const data = await fetchAllTkForms();

    const table = $('#tkFormTable').DataTable({
        data: data,
        pageLength: 25,
        autoWidth: false,
        order: [[0, 'desc']],
        paging:true,
        columnDefs: [
            { width: '10%', targets: 0 },
            { width: '12%', targets: 1 },
            { width: '46%', targets: 2 },
            { width: '13%', targets: 3 }
        ],
        columns: [
            {
              data: function (row) {
                    return `<span class="display-none">${row.id}</span>
                            <span class="u-pointer" onclick="openTkFormDetailsPage('${row.id}', '${row.document_number}', '${row.dto_number}')">
                                ${row.document_number} <i class="arrow alternate circle right outline icon"></i>
                            </span>`;
                }
            },
            { data: 'dto_number' },
            { data: 'description' },
            { data: 'created_name' },
            { data: 'created', className: 'text-center' },
            { data: 'updated', className: 'text-center' }
        ],
        destroy: true
    });

    function applyCombinedSearch() {
        $('#searchTip').show();

        // Split inputs into quoted phrases and other terms
        const extractSearchTerms = (input) => {
            const quoted = [...input.matchAll(/"([^"]+)"/g)].map(m => m[1].toLowerCase()); // Get quoted terms
            const unquoted = input.replace(/"([^"]+)"/g, '').trim().toLowerCase().split(/\s+/); // Get unquoted terms
            return { quoted, unquoted: unquoted.filter(Boolean) }; // Remove empty terms
        };

        const { quoted: searchTerms1Quoted, unquoted: searchTerms1Unquoted } = extractSearchTerms($('#search1').val());
        const { quoted: searchTerms2Quoted, unquoted: searchTerms2Unquoted } = extractSearchTerms($('#search2').val());

        $.fn.dataTable.ext.search.push(function (settings, data) {
            const documentNumber = (data[0] || '').toLowerCase();
            const dtoNumber = (data[1] || '').toLowerCase();
            const description = (data[2] || '').toLowerCase();
            const createdBy = (data[3] || '').toLowerCase();

            const matchesSearch1Quoted = searchTerms1Quoted.every(term => documentNumber.includes(term) || dtoNumber.includes(term));
            const matchesSearch2Quoted = searchTerms2Quoted.every(term => description.includes(term) || createdBy.includes(term));

            const matchesSearch1Unquoted = searchTerms1Unquoted.every(term => documentNumber.includes(term) || dtoNumber.includes(term));
            const matchesSearch2Unquoted = searchTerms2Unquoted.every(term => description.includes(term) || createdBy.includes(term));

            return matchesSearch1Quoted && matchesSearch1Unquoted && matchesSearch2Quoted && matchesSearch2Unquoted;
        });

        table.draw();
        $.fn.dataTable.ext.search.pop();
    }

    // Attach event listeners to both search boxes
    $('#search1').on('keyup', applyCombinedSearch);
    $('#search2').on('keyup', applyCombinedSearch);

    $('#tkFormPage .loader').hide();
    $('#tkFormGrid').transition('zoom');
    table.draw();
}

function openTkFormDetailsPage(id, documentNumber, dtoNumber) {
    window.open(`/dpm/dtoconfigurator/core/tkform/detail/info.php?id=${id}&document-number=${documentNumber}&dto-number=${dtoNumber}`, '_blank');
}


$('#btnCreateTkForm').on('click', async function() {
    $('#btnCreateTkForm').addClass('loading disabled');
    hideMessages();

    const formElement = document.getElementById('createTkFormUiForm');
    const formData = new FormData(formElement);
    formData.append('action', 'createTkForm');

    // Basic validation for required fields
    const documentNumber = formData.get('documentNumber')?.trim();
    const dtoNumber = formData.get('dtoNumber')?.trim();
    const description = formData.get('description')?.trim();
    const descriptionTr = formData.get('descriptionTr')?.trim();
    const isRearbox = formData.get('isRearbox');

    if (!documentNumber || !dtoNumber || !description || !descriptionTr || !isRearbox) {
        $('#createTkFormError').html(`<p><i class="exclamation circle icon"></i>Please fill all the required fields.</p>`).removeClass('hidden').transition('pulse');
        $('#btnCreateTkForm').removeClass('loading disabled');
        return;
    }

    // Validate rearbox specific fields
    if (isRearbox === 'yes') {
        const connectionType = formData.get('connectionType')?.trim();
        const ctVtQuantity = formData.get('ctVtQuantity')?.trim();

        if (!connectionType) {
            $('#connectionTypeError').removeClass('hidden').transition('pulse');
            $('#btnCreateTkForm').removeClass('loading disabled');
            return;
        }

        if (!ctVtQuantity) {
            $('#ctVtQuantityError').removeClass('hidden').transition('pulse');
            $('#btnCreateTkForm').removeClass('loading disabled');
            return;
        }
    }

    try {
        const response = await axios.post('/dpm/dtoconfigurator/api/controllers/TkFormController.php', formData, { headers: { 'Content-Type': 'multipart/form-data' }});

        if (response.status === 200) {
            addTkRowToDatatable(response.data);
            $('#createTkFormSuccess').removeClass('hidden').transition('pulse');

            // Reset form
            formElement.reset();
            $('#rearboxConnectionType, #rearboxCtVtQuantity').hide();
            $('#connectionType, #ctVtQuantity').dropdown('clear');

            setTimeout(function() { $('#createTkFormSuccess').transition('fade out'); }, 5000);
        }
    } catch (error) {
        if (error.response) {
            const message = error.response.data.message;

            if (message === 'TK Wrong Entry') {
                $('#documentNumberError').removeClass('hidden').transition('pulse');
            } else if (message === 'DTO Wrong Entry') {
                $('#dtoNumberError').removeClass('hidden').transition('pulse');
            } else if (message === 'DTO Special Characters') {
                $('#dtoNumberWrongEntryError').removeClass('hidden').transition('pulse');
            } else if (message === 'TK Form Exists') {
                $('#createTkFormError').html(`<p><i class="exclamation circle icon"></i>TK Form is already exists!</p>`).removeClass('hidden').transition('pulse');
            } else {
                $('#createTkFormError').html(`<p><i class="exclamation circle icon"></i>${message || 'An error occurred'}</p>`).removeClass('hidden').transition('pulse');
            }
        } else {
            $('#createTkFormError').html(`<p><i class="exclamation circle icon"></i>An error occurred while creating TK Form.</p>`).removeClass('hidden').transition('pulse');
        }
    } finally {
        $('#btnCreateTkForm').removeClass('loading disabled');
    }
});

function addTkRowToDatatable(data) {
    const newRowData = {
        id: data.id,
        document_number: data.document_number,
        dto_number: data.dto_number,
        description: data.description,
        created_name: data.created_name,
        created: moment(data.created).format('DD.MM.YYYY'),
        updated: moment(data.updated).format('DD.MM.YYYY')
    };

    const table = $('#tkFormTable').DataTable();
    table.row.add(newRowData).draw(false);

    $('html, body').animate({ scrollTop: $('#tkFormTable').offset().top }, 'slow');
}

function hideMessages() {
    $('#createTkFormError, #createTkFormSuccess, #documentNumberError, #dtoNumberError, #dtoNumberWrongEntryError, #isRearboxError, #connectionTypeError, #ctVtQuantityError').addClass('hidden');
}

// Handle Rearbox radio button change
$('input[name="isRearbox"]').on('change', function() {
    const isRearbox = $(this).val();

    if (isRearbox === 'yes') {
        $('#rearboxConnectionType, #rearboxCtVtQuantity').slideDown(300);
        $('#connectionType, #ctVtQuantity').closest('.field').addClass('required');
    } else {
        $('#rearboxConnectionType, #rearboxCtVtQuantity').slideUp(300);
        $('#connectionType, #ctVtQuantity').closest('.field').removeClass('required');
        // Clear the values when hidden
        $('#connectionType').dropdown('clear');
        $('#ctVtQuantity').dropdown('clear');
        $('#connectionTypeError, #ctVtQuantityError').addClass('hidden');
    }
});