let MinusPriceDtosOfProject = [];
async function getMinusPriceDtosOfNachbau() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/NachbauController.php', {
            params: {action: 'getMinusPriceDtosOfNachbau', projectNo: getUrlParam('project-no'), nachbauNo: getUrlParam('nachbau-no') }
        });
        MinusPriceDtosOfProject = response.data;
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    }
}

$('#openMinusPriceDtoWorkModal').on('click', async function () {

    if (MinusPriceDtosOfProject.length > 1) {
        await showMinusPriceDtoSelectionModal();
    } else if (MinusPriceDtosOfProject.length === 1) {
        const { dto_number, description, typical_no } = MinusPriceDtosOfProject[0];
        await openMinusPriceDtoWorkModal(dto_number, description, typical_no);
    } else {
        showErrorDialog(`<b>No Minus Price Dtos have been found.</b>`);
    }
});


async function showMinusPriceDtoSelectionModal() {
    const mpRadioButtonItems = MinusPriceDtosOfProject.map((dto, index) => `
        <div class="field">
            <div class="ui radio checkbox">
                <input type="radio" name="minus-price-dto-option" data-dto="${dto.dto_number}" data-typical="${dto.typical_no}" id="radio-dto-${index}">
                <label for="radio-dto-${index}">
                    <span style="font-weight: bold; color: #21ba45;">${formatDtoNumber(dto.dto_number)}</span> - 
                    <span style="font-weight: bold; color: #f2711c;">${formatDescription(dto.description,4)}</span>
                    <span style="font-weight: bold; color: #2185d0;">(${dto.typical_no})</span>
                </label>
            </div>
        </div>
    `).join('');

    $('#minusPriceDtoSelectionModal .mpRadioButtonItems').html(mpRadioButtonItems);
    $('#minusPriceDtoSelectionModal .ui.radio.checkbox').checkbox();

    $('#minusPriceDtoSelectionModal').modal({
        onApprove: async function () {
            const selectedDtoNumber = $('input[name="minus-price-dto-option"]:checked').data('dto');
            const selectedTypical = $('input[name="minus-price-dto-option"]:checked').data('typical');

            const selectedDto = MinusPriceDtosOfProject.find(dto =>
                dto.dto_number === selectedDtoNumber && dto.typical_no === selectedTypical
            );

            if (selectedDto) {
                await openMinusPriceDtoWorkModal(
                    selectedDto.dto_number,
                    selectedDto.description,
                    selectedDto.typical_no
                );
            } else {
                return false;
            }
        }
    }).modal('show');
}

async function initializeMinusPriceWontBeProducedTabContent(dtoNumber, description, typicalNo) {
    $('#minusPriceDtoImportantMsg .mp-dto-number').text(dtoNumber);
    $('#minusPriceDtoImportantMsg .mp-dto-typical').text(typicalNo);

    const response = await axios.get('/dpm/dtoconfigurator/api/controllers/NachbauController.php', {
        params: {
            action: 'getMinusPriceDtoStatus',
            projectNo: getUrlParam('project-no'),
            nachbauNo: getUrlParam('nachbau-no'),
            dtoNumber: dtoNumber,
            typicalNo: typicalNo
        }
    });

    const existingMinusPriceCheckBox = response.data;
    $('#minusPriceDtoSegment .ui.checkbox input').prop('checked', existingMinusPriceCheckBox);
    $('#updateMinusPriceDtoChange').data('dto-number', dtoNumber);
    $('#updateMinusPriceDtoChange').data('dto-description', description);
    $('#updateMinusPriceDtoChange').data('dto-typical', typicalNo);
    $('#updateMinusPriceDtoChange').data('existing-minpri-cb', existingMinusPriceCheckBox);
}

async function openMinusPriceDtoWorkModal(dtoNumber, description, typicalNo) {
    $('#minusPriceDtoWorkMenuModalContent .tabular.menu .item').tab();
    showElement('#minusPriceDtoWorkMenuModalContent .ui.tab');

    $('#minusPriceDtoWorkHeader').html(`
        <h3>Minus Price DTO Work Menu</h3>
        <h4>${dtoNumber} | ${formatDescription(description, 5)} | ${typicalNo}</h4>
    `);
    $('#minusPriceDtoSegment .ui.checkbox').checkbox();

    // await initializeMinusPriceNachbauListsTabContent(dtoNumber, description, typicalNo);
    await initializeMinusPriceWontBeProducedTabContent(dtoNumber, description, typicalNo);
    // await renderExistingMinusPriceWontBeProducedItems(dtoNumber, typicalNo);

    $('#minusPriceDtoWorkMenuModal').modal('show');
}

async function initializeMinusPriceNachbauListsTabContent(dtoNumber, description, typicalNo) {
    const tableId = '#minusPriceNachbauListDataTable';
    const data = await getMinusPriceDtoNachbauListOfProject(dtoNumber, typicalNo);

    if ($.fn.DataTable.isDataTable(tableId))
        $(tableId).DataTable().destroy();

    const table = $(tableId).DataTable({
        data: data,
        autoWidth: false,
        pageLength: 50,
        lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "All"]],
        order: [[0, 'asc']],
        createdRow: function (row, data) {
            if (data.material_deleted_number.startsWith('003003') || data.material_deleted_number.startsWith('003013')) {
                $(row).css('font-weight', 'bold');
                $(row).css('text-decoration', 'underline');
                $(row).css('color', 'mediumblue');
            }
        },
        columns: [
            { data: 'id', visible: false },
            { data: 'position', className: 'center aligned' },
            { data: 'material_deleted_number', className: 'center aligned' },
            { data: 'material_deleted_description', className: 'center aligned' },
            { data: 'quantity', className: 'center aligned' },
            { data: 'unit', className: 'center aligned' },
            { data: 'typical_no', className: 'center aligned' },
            { data: 'ortz_kz', className: 'center aligned' },
            {
                data: null,
                render: function (data, type, row) {
                    if (data.material_deleted_number.startsWith('003003') || data.material_deleted_number.startsWith('003013'))
                        return '';

                    return `<button data-row='${JSON.stringify(row).replace(/'/g, "&#39;")}'
                                    onclick="moveMaterialToWontBeProduced(this, '${dtoNumber}', '${escapeHtml(description)}', '${typicalNo}')"
                                    class="circular compact ui negative icon button tiny"
                                    data-tooltip="Move to the won't be produced list.">
                                <i class="ban icon large"></i>
                            </button>`;
                },
                className: 'center aligned'
            }
        ],
        destroy: true
    });

    //Search customization
    const $searchInput = $('.dt-search input[type="search"]');
    $searchInput.attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');

    // showElement('#nachbau-lists-content');
}

async function getMinusPriceDtoNachbauListOfProject(dtoNumber, typicalNo) {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/NachbauController.php', {
            params: { action: 'getMinusPriceDtoNachbauListOfProject', projectNo: getUrlParam('project-no'), nachbauNo: getUrlParam('nachbau-no'),typicalNo: typicalNo }
        });

        return response.data;
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`<b>${errorMessage}</b>`);
    }
}

$('#updateMinusPriceDtoChange').on('click', async function (e) {
    e.stopPropagation();
    $(this).addClass('loading disabled');

    let dtoNumber = $(this).data('dto-number');
    let description = $(this).data('dto-description');
    let typicalNo = $(this).data('dto-typical');
    let existingMinusPriceCheckBox = $(this).data('existing-minpri-cb');
    let wontBeProduced = $('#wontBeProduced').prop('checked');

    if (existingMinusPriceCheckBox === wontBeProduced) {
        if (wontBeProduced === 0) {
            showErrorDialog(`<b>${dtoNumber}</b> DTO changes are already exist.`);
        } else {
            $('#minusPriceDtoWorkMenuModal').modal('hide');
        }
        $(this).removeClass('loading disabled');
        return;
    }

    try {
        await axios.post('/dpm/dtoconfigurator/api/controllers/NachbauController.php?',
            {
                action: 'updateMinusPriceDtoChange',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                dtoNumber: dtoNumber,
                description: description,
                typicalNo: typicalNo,
                wontBeProduced: wontBeProduced,
                isRevisionNachbau: currentlyWorkingUser.isRevisionNachbau
            },
            { headers: { 'Content-Type': 'multipart/form-data' }}
        );

        showSuccessDialog(`All changes are saved. Please check Order Summary table.`).then(async () => {
            showLoader('#orderSummaryV2');
            hideElement('#orderSummaryV2Container');

            $('#minusPriceDtoWorkMenuModal').modal('hide');

            await getOrderSummaryV2();

            hideLoader('#orderSummaryV2');
            showElement('#orderSummaryV2Container');
        });
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    } finally {
        $(this).removeClass('loading disabled');
    }
});


async function moveMaterialToWontBeProduced(_this, dtoNumber, description, typicalNo) {
    const projectNo = getUrlParam('project-no');
    const nachbauNo = getUrlParam('nachbau-no');
    const rowData = JSON.parse(_this.getAttribute('data-row'));

    try {
        const response = await axios.post('/dpm/dtoconfigurator/api/controllers/NachbauController.php',
            {
                action: 'moveMaterialToWontBeProduced',
                projectNo: projectNo,
                nachbauNo: nachbauNo,
                rowData: rowData,
                dtoNumber: dtoNumber,
                description: description,
                typicalNo: typicalNo,
                isRevisionNachbau: currentlyWorkingUser.isRevisionNachbau
            },
            {
                headers: {'Content-Type': 'multipart/form-data'}
            });

        showSuccessDialog('Material added to wont be produced list.').then(async () => {
            const insertedMinPriId = response.data;

            const selectedItemHTML = `
                <a class="item" data-id="${insertedMinPriId}" style="background-color:crimson;color:white;">
                    <span><b>${rowData.material_deleted_number.replace(/^00/, '')}</b> - ${rowData.material_deleted_description}</span>
                    <span class="ui label basic remove-minus-price-work-button" style="padding-right: 0; background:red; cursor:pointer;" data-id="${insertedMinPriId}" data-tooltip="Remove" data-position="top center">
                        <i class="trash icon"></i>
                    </span>
                    <span class="ui label grey basic" data-tooltip="Unit" data-position="top center">
                        <span class="material-unit">${rowData.unit}</span>
                    </span>
                    <span class="ui label grey basic" data-tooltip="Quantity" data-position="top center">
                        <span class="material-quantity">${parseFloat(rowData.quantity).toString().replace(/\.000$/, '')}</span>
                    </span>
                </a>
            `;

            $('#minus-price-selected-materials').append(selectedItemHTML);

            await getOrderSummaryV2();
        });

    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`<b>${errorMessage}</b>`);
    }
}

async function renderExistingMinusPriceWontBeProducedItems(dtoNumber, typicalNo) {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/NachbauController.php', {
            params: {
                action: 'getExistingMinusPriceWontBeProducedItems',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                dtoNumber: dtoNumber,
                typicalNo: typicalNo
            }
        });

        const existingItems = response.data;
        $('#minus-price-selected-materials').html('');

        const selectedItemHTML = existingItems.map(item => `
                <a class="item" data-id="${item.id}" style="background-color:crimson;color:white;">
                    <span><b>${item.material_deleted_number}</b> - ${item.material_deleted_description}</span>
                    <button class="ui label basic remove-minus-price-work-button" style="padding-right: 0; background:red; cursor:pointer;" data-id="${item.id}" data-tooltip="Remove" data-position="top center">
                        <i class="trash icon"></i>
                    </button>
                    <span class="ui label grey basic" data-tooltip="Unit" data-position="top center">
                        <span class="material-unit">${item.unit}</span>
                    </span>
                    <span class="ui label grey basic" data-tooltip="Quantity" data-position="top center">
                        <span class="material-quantity">${parseFloat(item.quantity).toString().replace(/\.000$/, '')}</span>
                    </span>
                </a>
            `).join('');

        $('#minus-price-selected-materials').append(selectedItemHTML);
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`<b>${errorMessage}</b>`);
    }
}



$(document).on('click', '.remove-minus-price-work-button', function () {
    const minusPriceWorkId = $(this).data('id');
    const selectedItemToRemove = $(this).closest('.item');

    showConfirmationDialog({
        title: 'Removing Material?',
        htmlContent: 'Are you sure to delete this material list?',
        confirmButtonText: 'Yes, delete!',
        confirmButtonColor: "#d33",
        onConfirm: async function () {
            try {
                await axios.post('/dpm/dtoconfigurator/api/controllers/NachbauController.php?',
                    {
                        action: 'removeMinusPriceWork',
                        minusPriceWorkId: minusPriceWorkId
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                showSuccessDialog('Successful.').then(async () => {
                    selectedItemToRemove.remove();
                    await getOrderSummaryV2();
                });
            } catch (error) {
                showErrorDialog('Failed to delete entry. Try again.');
            }
        },
    });
});
