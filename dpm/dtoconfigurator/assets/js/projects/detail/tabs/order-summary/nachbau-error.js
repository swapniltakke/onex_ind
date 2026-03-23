$('#nachbauErrorButton').on('click', async function() {
    $('#nachbauErrorModal')
        .modal({
            centered: false
        })
        .modal('show')
        .draggable({
            handle: '.header',
            containment: 'window'
        })
        .css({ top: '0', marginTop: '10px' });

    await initializeMaterialDropdowns();
});

let materialMap = {};
async function initializeMaterialDropdowns() {
    $('#addedMaterial, #deletedMaterial').dropdown({
        apiSettings: {
            url: `/dpm/dtoconfigurator/api/controllers/MaterialController.php?action=getMaterialsBySearch&keyword={query}`,
            cache: false,
            onResponse: function(response) {
                const materials = Array.isArray(response) ? response : Object.values(response);

                const results = materials.map(material => {
                    // Store material data in a global map
                    materialMap[material.id] = {
                        stationCode: material.work_center,
                        stationName: material.work_content
                    };

                    return {
                        name: `<b>${material.material_number}</b> - ${material.description}`,
                        value: material.material_number
                    };
                });

                return { results };
            }
        },
        fields: {
            remoteValues: 'results',
            name: 'name',
            value: 'value'
        },
        minCharacters: 1,
        clearable: true,
        allowAdditions: false,
        onChange: async function (value) {
            await fetchList();
        }
    });
}

async function fetchList() {
    $('#itemList').empty();
    $('#selectedItems').empty();

    const materialAddedNo = $('#addedMaterial').dropdown('get value');
    const materialDeletedNo = $('#deletedMaterial').dropdown('get value');
    const operation = await updateOperationType(materialAddedNo, materialDeletedNo);

    if (operation === 'add')
        $('#quantityAndUnitDiv').removeClass('hide-important');
    else
        $('#quantityAndUnitDiv').addClass('hide-important');


    if ((!materialAddedNo && !materialDeletedNo)) {
        $('#nachbauErrorBoxes #selectedItems').empty();
        $('#nachbauErrorBoxes #itemList').empty();
        $('#operationTitle').text('');
        $('#operationTitleDiv').hide();
        return;
    }

    try {
        const projectNo = getUrlParam('project-no');
        const nachbauNo = getUrlParam('nachbau-no');
        const url = '/dpm/dtoconfigurator/api/controllers/NachbauController.php';
        const response = await axios.get(url, {
            params: {
                action: 'getNachbauErrorItemAndSelectedList',
                projectNo: projectNo,
                nachbauNo: nachbauNo,
                operation: operation,
                materialAddedNo: materialAddedNo,
                materialDeletedNo: materialDeletedNo
            },
            headers: { "Content-Type": "multipart/form-data" },
        })

        await renderList(response.data.allTypicalsWithPanels, response.data.insertedItems);

    } catch (error) {
        fireToastr('error', 'Error fetching Nachbau Error Item and Selected List Data', error);
        return [];
    }
}

async function renderList(items, insertedItems = {}) {
    $('#itemList').empty();
    $('#selectedItems').empty();

    if (Object.keys(items).length === 0) {
        $('#itemList').append('<h6 class="text-center">No Typicals or Panels Found!</h6>');
        return;
    }

    Object.entries(items).forEach(([typical, data]) => {
        const insertedPanels = insertedItems[typical] || [];

        // Create a typical item with a dropdown icon and whitesmoke background
        const typicalItem = $(`
            <a class="item group typical-item" data-id="${typical}" style="background-color: whitesmoke; border: 1px solid #ddd;">
                <i class="dropdown icon"></i>
                <span>${typical} ${data.exists ? '(Aktarımda Bulunuyor)' : ''}</span>
            </a>
        `);

        // Create a hidden panel list with box styling
        const panelList = $('<div class="ui segment panel-list" style="display: none; margin-right: 20px; margin-left: 20px; padding:0; border: 1px solid #ddd; border-radius: 4px;"></div>');

        // Add panels to the panel list
        data.panels.forEach(panel => {
            if (!insertedPanels.includes(panel)) {
                const displayText = panel === "" ? "Accessory" : panel;
                panelList.append(`<a class="ui item panel-item" data-typical="${typical}" data-id="${panel}" style="display: block;">${displayText}</a>`);
            }
        });

        // Append the typical item and its panel list only if panels exist
        if (panelList.children().length > 0) {
            $('#itemList').append(typicalItem);
            $('#itemList').append(panelList);
        }

        // Add click event to the typical item to toggle the panel list
        typicalItem.on('click', function () {
            const icon = $(this).find('.dropdown.icon');
            panelList.slideToggle(200); // Smooth toggle
            icon.toggleClass('rotated'); // Rotate the dropdown icon
        });
    });

}



$('#transferButton').on('click', function () {
    // Get active panel items from the left box
    const activeLeftPanels = $('#itemList .panel-item.active');
    const activeRightPanels = $('#selectedItems .panel-item.active');

    // Move from left to right
    activeLeftPanels.each(function () {
        const panelItem = $(this);
        const typicalId = panelItem.data('typical');

        // Clone the panel item and append it to the corresponding typical in the right box
        const rightTypical = $(`#selectedItems .typical-item[data-id="${typicalId}"]`);
        if (rightTypical.length === 0) {
            // Create the typical item in the right box if it doesn't exist
            const newRightTypical = $(`<a class="item group typical-item" data-id="${typicalId}" style="background-color: whitesmoke; border: 1px solid #ddd;">
                <i class="dropdown icon"></i>
                <span>${typicalId}</span>
            </a>`);
            const newPanelList = $('<div class="ui segment panel-list" style="display: none; margin-right: 20px; margin-left: 20px; padding:0; border: 1px solid #ddd; border-radius: 4px;"></div>');
            newRightTypical.on('click', function () {
                const icon = $(this).find('.dropdown.icon');
                newPanelList.slideToggle(200);
                icon.toggleClass('rotated');
            });
            $('#selectedItems').append(newRightTypical).append(newPanelList);
        }

        // Append the panel item to the right box
        const panelList = $(`#selectedItems .typical-item[data-id="${typicalId}"]`).next('.panel-list');
        panelList.append(panelItem.clone().removeClass('active'));

        // Open the panel list and adjust the dropdown icon
        panelList.slideDown(200);
        $(`#selectedItems .typical-item[data-id="${typicalId}"] .dropdown.icon`).addClass('rotated');

        // Remove the panel item from the left box
        panelItem.remove();

        // If the typical has no panels left, hide it
        const leftPanelList = $(`#itemList .typical-item[data-id="${typicalId}"]`).next('.panel-list');
        if (leftPanelList.children('.panel-item').length === 0) {
            $(`#itemList .typical-item[data-id="${typicalId}"]`).remove();
            leftPanelList.remove();
        }
    });

    // Move from right to left
    activeRightPanels.each(function () {
        const panelItem = $(this);
        const typicalId = panelItem.data('typical');

        // Clone the panel item and append it to the corresponding typical in the left box
        const leftTypical = $(`#itemList .typical-item[data-id="${typicalId}"]`);
        if (leftTypical.length === 0) {
            // Create the typical item in the left box if it doesn't exist
            const newLeftTypical = $(`<a class="item group typical-item" data-id="${typicalId}" style="background-color: whitesmoke; border: 1px solid #ddd;">
                <i class="dropdown icon"></i>
                <span>${typicalId}</span>
            </a>`);
            const newPanelList = $('<div class="ui segment panel-list" style="display: none; margin-right: 20px; margin-left: 20px; padding:0; border: 1px solid #ddd; border-radius: 4px;"></div>');
            newLeftTypical.on('click', function () {
                const icon = $(this).find('.dropdown.icon');
                newPanelList.slideToggle(200);
                icon.toggleClass('rotated');
            });
            $('#itemList').append(newLeftTypical).append(newPanelList);
        }

        // Append the panel item to the left box
        const panelList = $(`#itemList .typical-item[data-id="${typicalId}"]`).next('.panel-list');
        panelList.append(panelItem.clone().removeClass('active'));

        // Open the panel list and adjust the dropdown icon
        panelList.slideDown(200);
        $(`#itemList .typical-item[data-id="${typicalId}"] .dropdown.icon`).addClass('rotated');

        // Remove the panel item from the right box
        panelItem.remove();

        // If the typical has no panels left, hide it
        const rightPanelList = $(`#selectedItems .typical-item[data-id="${typicalId}"]`).next('.panel-list');
        if (rightPanelList.children('.panel-item').length === 0) {
            $(`#selectedItems .typical-item[data-id="${typicalId}"]`).remove();
            rightPanelList.remove();
        }
    });
});

async function updateOperationType(materialAddedNo, materialDeletedNo){
    const operationType = $('#operationTitle');

    if (materialAddedNo && materialDeletedNo) {
        operationType.text('Replaced List');
        showElement('#operationTitleDiv')
        return 'replace';
    } else if (materialAddedNo) {
        operationType.text('Added List');
        showElement('#operationTitleDiv')
        return 'add';
    } else if (materialDeletedNo) {
        operationType.text('Deleted List');
        showElement('#operationTitleDiv')
        return 'delete'
    } else {
        operationType.text('');
        return '';
    }
}

// Click event handler for the 'Save Changes' button
$('#saveNachbauErrorChanges').on('click', async function (e) {
    e.stopPropagation();
    const buttonSelector = $('#saveNachbauErrorChanges');
    buttonSelector.addClass('loading disabled');

    // Collect values from the form inputs
    const addedMaterial = $('#addedMaterial').dropdown('get value');
    const deletedMaterial = $('#deletedMaterial').dropdown('get value');
    let quantity = $('#quantity').val();
    const unit = $('#unit').val();
    const notes = $('#notes').val();

    // Initialize an object to store selected typicals and their panels
    let selectedItems = {};

    // Collect selected items from the right box
    $('#selectedItems .typical-item').each(function () {
        const typicalId = $(this).data('id');
        selectedItems[typicalId] = [];

        // Collect panels under each typical
        $(this).next('.panel-list').find('.panel-item').each(function () {
            const panelId = $(this).data('id');
            selectedItems[typicalId].push(panelId);
        });
    });

    // Perform validation before sending data
    if (!addedMaterial && !deletedMaterial) {
        showErrorDialog('Please select at least one material (added or deleted)')
        buttonSelector.removeClass('loading disabled');
        return;
    }

    if ($.isEmptyObject(selectedItems)) {
        showErrorDialog('Please select at least one typical or panel.');
        buttonSelector.removeClass('loading disabled');
        return;
    }

    if (addedMaterial && deletedMaterial && addedMaterial === deletedMaterial) {
        showErrorDialog('Replaced materials cannot be the same.');
        buttonSelector.removeClass('loading disabled');
        return;
    }

    if (addedMaterial && (!quantity || quantity <= 0)) {
        alert('Please enter a valid quantity for the added material.');
        buttonSelector.removeClass('loading disabled');
        return;
    }

    // Determine the operation type
    let changeOperation = '';
    if (addedMaterial && deletedMaterial) {
        changeOperation = 'replace';
    } else if (addedMaterial) {
        changeOperation = 'add';
    } else if (deletedMaterial) {
        changeOperation = 'delete';
    }

    showConfirmationDialog({
        title: 'Save Nachbau Error?',
        htmlContent: 'Do you want to save nachbau error change? <br> Old nachbau error data will be overwritten.',
        confirmButtonText: 'Yes, save it!',
        confirmButtonColor: "green",
        onConfirm: async function () {
            try {
                await axios.post('/dpm/dtoconfigurator/api/controllers/ProjectController.php?',
                    {
                        action: 'saveNachbauErrorChanges',
                        projectNo: getUrlParam('project-no'),
                        nachbauNo: getUrlParam('nachbau-no'),
                        materialAddedNo: addedMaterial,
                        materialDeletedNo: deletedMaterial,
                        quantity: quantity,
                        unit: unit,
                        note: notes,
                        selectedItems: selectedItems,
                        operation: changeOperation,
                        accessoryTypicalCode: NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryTypicalCode'],
                        isRevisionNachbau: currentlyWorkingUser.isRevisionNachbau
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                showSuccessDialog('Nachbau Error change saved successfully.').then(() => {
                    getOrderSummaryV2();
                    resetNachbauErrorModal();
                    // $('#nachbauErrorModal').modal('hide');
                });
            } catch (error) {
                const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                showErrorDialog(`Error: ${errorMessage}`);
            } finally {
                buttonSelector.removeClass('loading disabled');
            }
        },
    });
});

function resetNachbauErrorModal() {
    // Clear dropdowns
    $('#addedMaterial').dropdown('clear');
    $('#deletedMaterial').dropdown('clear');

    // Reset quantity and unit inputs
    $('#quantity').val('1'); // Default quantity
    $('#unit').val('ST');    // Default unit

    // Reset notes field
    $('#notes').val('NACHBAU ERROR'); // Default note

    // Clear selected items in the right box
    $('#selectedItems').empty();

    // Reset the left box
    $('#itemList').empty();
    $('#itemListInfoMsg').show(); // Show the placeholder message

    // Reset the right box placeholder message
    $('#selectedItemsInfoMsg').show();

    // Remove active classes from any items
    $('#itemList .panel-item, #selectedItems .panel-item').removeClass('active');


    // Optionally, reinitialize dropdowns or other UI components
    $('#addedMaterial, #deletedMaterial').dropdown(); // Reinitialize Semantic UI dropdowns
}


$('#itemList, #selectedItems').on('click', '.panel-item', async function () {
    $(this).toggleClass('active');
});
