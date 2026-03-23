$('#openSparePartDtoWorkModal').on('click', async function () {
    await checkSparePartDtoCounts();
});

async function checkSparePartDtoCounts() {
    if (SparePartDtosOfProject.length > 1) {
        await showSparePartDtoSelectionModal();
    } else if (SparePartDtosOfProject.length === 1) {
        const { dto_number, description, typical_no } = SparePartDtosOfProject[0];
        await openSparePartDtoWorkModal(dto_number, description, typical_no);
    } else {
        showErrorDialog(`<b>No Spare Part Dtos have been found.</b>`);
    }
}

async function showSparePartDtoSelectionModal() {
    const radioButtonItems = SparePartDtosOfProject.map((dto, index) => `
        <div class="field">
            <div class="ui radio checkbox">
                <input type="radio" name="spare-part-dto-option" value="${dto.dto_number}" id="radio-dto-${index}">
                <label for="radio-dto-${index}">
                    <span style="font-weight: bold; color: #21ba45;">${dto.dto_number}</span> - 
                    <span style="font-weight: bold; color: #f2711c;">${formatDescription(dto.description,3)}</span>
                    <span style="font-weight: bold; color: #2185d0;">(${dto.typical_no})</span>
                </label>
            </div>
        </div>
    `).join('');

    $('#sparePartDtoSelectionModal .radioButtonItems').html(radioButtonItems);
    $('#sparePartDtoSelectionModal .ui.radio.checkbox').checkbox();

    $('#sparePartDtoSelectionModal').modal({
        onApprove: async function () {
            const selectedDtoNumber = $('input[name="spare-part-dto-option"]:checked').val();
            const selectedDto = SparePartDtosOfProject.find(dto => dto.dto_number === selectedDtoNumber);

            if (selectedDto) {
                await openSparePartDtoWorkModal(
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

async function openSparePartDtoWorkModal(dtoNumber, description, typicalNo) {
    $('#sparePartDtoWorkHeader').html(`
        <h3>Spare Part DTO Work Menu</h3>
        <h4>${dtoNumber} | ${formatDescription(description, 3)} | ${typicalNo}</h4>
    `);

    await renderSparePartDtoKmatsDataTable(dtoNumber, description, typicalNo);
    await renderExistingSelectedSparePartItems(dtoNumber, typicalNo);

    $('#sparePartDtoWorkMenuModal').modal({ closable: false }).modal('show');
}

async function renderSparePartDtoKmatsDataTable(dtoNumber, description, typicalNo) {
    const tableId = '#sparePartDtoKmatsDataTable';
    const data = await getSparePartDtoKmatsOfProject();

    if ($.fn.DataTable.isDataTable(tableId))
        $(tableId).DataTable().destroy();

    const table = $(tableId).DataTable({
        data: data,
        autoWidth: false,
        pageLength: 50,
        lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "All"]],
        order: [[0, 'asc']],
        createdRow: function (row, data) {
            if (data.kmat.startsWith('003003') || data.kmat.startsWith('003013')) {
                $(row).css('font-weight', 'bold');
                $(row).css('text-decoration', 'underline');
                $(row).css('color', 'mediumblue');
            }
        },
        columns: [
            { data: 'Id', visible: false },
            { data: 'position', className: 'center aligned' },
            { data: 'kmat', className: 'center aligned' },
            { data: 'kmat_name', className: 'center aligned' },
            { data: 'qty', className: 'center aligned' },
            { data: 'unit', className: 'center aligned' },
            { data: 'typical_no', className: 'center aligned' },
            { data: 'ortz_kz', className: 'center aligned' },
            {
                data: null,
                render: function (data, type, row) {
                    if (data.kmat.startsWith('003003') || data.kmat.startsWith('003013'))
                        return '';

                    return `<button data-row='${JSON.stringify(row).replace(/'/g, "&#39;")}'
                                    onclick="chooseSparePartQuantity(this, '${dtoNumber}', '${escapeHtml(description)}', '${typicalNo}')"
                                    class="circular compact ui positive icon button tiny" 
                                    data-tooltip="Add as an accessory.">
                                <i class="plus circle icon large"></i>
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

    $('#sparePartDtoModalContent').transition('zoom', function() {
        requestAnimationFrame(() => {
            table.fixedHeader.adjust();
            table.draw();
        });
    });

}

async function getSparePartDtoKmatsOfProject() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/ProjectController.php', {
            params: { action: 'getSparePartDtoKmatsOfProject', projectNo: getUrlParam('project-no'), nachbauNo: getUrlParam('nachbau-no') }
        });

        return response.data;
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`<b>${errorMessage}</b>`);
    }
}

async function chooseSparePartQuantity(_this, dtoNumber, description, typicalNo) {
    const rowData = JSON.parse(_this.getAttribute('data-row'));

    $('#addSparePartToSelectedItems')
        .data('rowData', rowData)
        .data('dtoNumber', dtoNumber)
        .data('description', description)
        .data('typicalNo', typicalNo);

    $('#sparePartQuantityModal').modal({
        allowMultiple: true,
        dimmerSettings: { closable: false }
    }).modal('show');
}


$(document).on('click', '#addSparePartToSelectedItems', async function () {
    const projectNo = getUrlParam('project-no');
    const nachbauNo = getUrlParam('nachbau-no');

    // Retrieve data from the button
    const rowData = $(this).data('rowData');
    const dtoNumber = $(this).data('dtoNumber');
    const description = $(this).data('description');
    const typicalNo = $(this).data('typicalNo');
    const selectedQuantity = parseFloat($('#spare-part-quantity-input').val());

    if (selectedQuantity === 0) {
        showErrorDialog('Quantity must not be equal to 0');
        return;
    }

    try {
        const response = await axios.post('/dpm/dtoconfigurator/api/controllers/ProjectController.php',
            {
                action: 'addSparePartToSelectedItems',
                projectNo: projectNo,
                nachbauNo: nachbauNo,
                accessoryTypicalNo: NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryTypicalCode'],
                accessoryPanelNo: NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryPanelNo'],
                accessoryParentKmat: NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryParentKmat'],
                rowData: rowData,
                dtoNumber: dtoNumber,
                description: description,
                typicalNo: typicalNo,
                quantity: selectedQuantity,
                isRevisionNachbau: currentlyWorkingUser.isRevisionNachbau
            },
            {
                headers: {'Content-Type': 'multipart/form-data'}
            });

        showSuccessDialog('Material added as an accessory.').then(async () => {
            const insertedSpareId = response.data;

            const selectedItemHTML = `
                <a class="item" data-id="${insertedSpareId}" style="background-color:palegreen;">
                    <span><b>${rowData.kmat.replace(/^00/, '')}</b> - ${rowData.kmat_name}</span>
                    <span class="ui label basic remove-spare-part-work-button" style="padding-right: 0; background:red; cursor:pointer;" data-id="${insertedSpareId}" data-tooltip="Remove" data-position="top center">
                        <i class="trash icon"></i>
                    </span>
                    <span class="ui label grey basic" data-tooltip="Unit" data-position="top center">
                        <span class="material-unit">${rowData.unit}</span>
                    </span>
                    <span class="ui label grey basic" data-tooltip="Quantity" data-position="top center">
                        <span class="material-quantity">${selectedQuantity}</span>
                    </span>
                </a>
            `;

            $('#spare-part-selected-materials').append(selectedItemHTML);

            await getOrderSummaryV2();
        });

    } catch (error) {
        showErrorDialog('Failed to add the item to the backend.');
    }
});

async function renderExistingSelectedSparePartItems(dtoNumber, typicalNo) {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/ProjectController.php', {
            params: {
                action: 'getSelectedSparePartItemsOfDto',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                dtoNumber: dtoNumber,
                typicalNo: typicalNo
            }
        });

        const existingItems = response.data;
        $('#spare-part-selected-materials').html(''); // Clear existing items

        const selectedItemHTML = existingItems.map(item => `
                <a class="item" data-id="${item.id}" style="background-color:palegreen;">
                    <span><b>${item.material_added_number}</b> - ${item.material_added_description}</span>
                    <button class="ui label basic remove-spare-part-work-button" style="padding-right: 0; background:red; cursor:pointer;" data-id="${item.id}" data-tooltip="Remove" data-position="top center">
                        <i class="trash icon"></i>
                    </button>
                    <span class="ui label grey basic" data-tooltip="Unit" data-position="top center">
                        <span class="material-unit">${item.release_unit}</span>
                    </span>
                    <span class="ui label grey basic" data-tooltip="Quantity" data-position="top center">
                        <span class="material-quantity">${parseFloat(item.release_quantity).toString().replace(/\.000$/, '')}</span>
                    </span>
                </a>
            `).join('');

        $('#spare-part-selected-materials').append(selectedItemHTML);
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`<b>${errorMessage}</b>`);
    }
}

$(document).on('click', '.remove-spare-part-work-button', function () {
    const sparePartWorkId = $(this).data('id');
    const selectedItemToRemove = $(this).closest('.item');

    showConfirmationDialog({
        title: 'Remove Material List?',
        htmlContent: 'Are you sure to delete this material list?',
        confirmButtonText: 'Yes, delete!',
        confirmButtonColor: "#d33",
        onConfirm: async function () {
            try {
                await axios.post('/dpm/dtoconfigurator/api/controllers/ProjectController.php?',
                    {
                        action: 'removeSparePartWork',
                        sparePartWorkId: sparePartWorkId
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                showSuccessDialog('Spare Part Work has been removed.').then(async () => {
                    selectedItemToRemove.remove();
                    await getOrderSummaryV2();
                });
            } catch (error) {
                showErrorDialog('Failed to delete entry. Try again.');
            }
        },
    });
});
