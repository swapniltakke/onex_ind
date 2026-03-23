let ExtensionDtosOfProject = [];
async function getExtensionDtosOfNachbau() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/ExtensionDtoController.php', {
            params: { action: 'getExtensionDtosOfNachbau', projectNo: getUrlParam('project-no'), nachbauNo: getUrlParam('nachbau-no'), accTypical: NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryTypicalCode'] }
        });
        ExtensionDtosOfProject = response.data;
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    }
}

async function renderExtensionDtoWorksDataTable(extensionDtoData) {
    const tableId = '#extensionDtoListDataTable';
    const $table = $(tableId);

    // Clear and destroy any existing DataTable instance
    if ($.fn.DataTable.isDataTable(tableId)) {
        $table.DataTable().clear().destroy();
        $table.empty(); // Clear table DOM structure
    }

    // Show or hide the container based on data availability
    if (extensionDtoData.length === 0) {
        $('#extensionDtoListContainer').hide();
        return;
    } else {
        $('#extensionDtoListContainer').show();
    }

    // Extract unique typical_no values
    const uniqueTypicals = [...new Set(extensionDtoData.map(item => item.typical_no))].sort((a, b) => a.localeCompare(b));

    let tableHeader = `<tr><th>DTO Number</th><th>Description</th>`;
    uniqueTypicals.forEach(typical => {
        tableHeader += `<th>${typical}</th>`;
    });
    tableHeader += `<th><button class="ui primary basic button small" onclick="openAddExtensionTypicalModal()">
                    <i class="plus icon"></i> Add Typical
                </button></th>`;
    tableHeader += `</tr>`;
    $table.html(`<thead>${tableHeader}</thead><tbody></tbody>`); // Ensure full reset of table content

    // Group data by DTO number
    const groupedData = extensionDtoData.reduce((acc, dto) => {
        const key = dto.dto_number;
        if (!acc[key]) {
            acc[key] = {
                dto_number: dto.dto_number,
                description: dto.description,
                typicals: {}
            };
        }
        acc[key].typicals[dto.typical_no] = {
            has_extension_works: dto.has_extension_works,
            is_extra_typical: dto.is_extra_typical
        };
        return acc;
    }, {});


    const tableData = Object.values(groupedData).map(dto => {
        const row = [dto.dto_number, formatDescription(dto.description, 5)];

        uniqueTypicals.forEach(typical => {
            const typicalData = dto.typicals[typical];
            const hasWorks = typicalData?.has_extension_works;
            const isExtraTypical = typicalData?.is_extra_typical;
            const bgColor = hasWorks ? 'green' : 'red';
            const dataTooltip = hasWorks ? 'Has Works' : 'Not Worked';

            const isAccessory = NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryTypicalCode'] === typical;

            const rowData = {
                dto_number: dto.dto_number,
                typical_no: typical,
                description: dto.description,
                is_accessory: isAccessory,
                is_extra_typical: isExtraTypical
            };

            const rowDataString = JSON.stringify(rowData).replace(/"/g, '&quot;');

            row.push(hasWorks !== undefined ? `<div class="ui circular icon ${bgColor} button tiny" 
                                     data-tooltip="${dataTooltip}" data-position="top center" data-inverted=""
                                     onclick="openExtensionDtoModal(this,${rowDataString})">
                                     <i class="eye large icon"></i>
                                   </div>` : '');

        });
        row.push(''); // Add empty cell for "Add Typical" button

        return row;
    });

    const dataTable = $table.DataTable({
        data: tableData,
        paging: true,
        ordering:false,
        fixedHeader: true,
        pageLength: 10,
        autoWidth: false
    });

    dataTable.draw();

    const $searchInput = $('.dt-search input[type="search"]');
    $searchInput.attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
}



async function openExtensionDtoModal(_this,dtoData) {
    $(_this).addClass('disabled');
    $('#extensionDtoModal .tabular.menu .item').tab();

    // Add a click event listener to all tab menu items
    $('#extensionDtoModal .menu .item').on('click', function () {
        // Remove the 'active' class from all tabs and hide their content
        $('#extensionDtoModal .menu .item').removeClass('active');
        $('#extensionDtoModal .tab.segment').css('display', 'none');

        // Add the 'active' class to the clicked tab and show its content
        $(this).addClass('active');
        const tabName = $(this).data('tab');
        $(`#${tabName}`).css('display', 'block');
    });

    // Initialize the first active tab
    $('#extensionDtoModal .menu .item.active').trigger('click');

    await getOverviewOfExtensionWorks(dtoData);
    $(_this).removeClass('disabled');
}

async function getOverviewOfExtensionWorks(dtoData) {
    const isAccessory = dtoData.is_accessory;
    const action = isAccessory ? 'getExtensionDtoAccessoryWorks' : 'getPanelsOfDtoWithExtensionWork';

    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/ExtensionDtoController.php', {
            params: {
                action: action,
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                dtoNumber: dtoData.dto_number,
                typicalNo: dtoData.typical_no,
                isExtraTypical: dtoData.is_extra_typical
            }
        });

        const panels = isAccessory ?  response.data : response.data.panels;

        initializeExtensionDtoModalDetails(panels, dtoData);

        $('#extensionDtoModalHeader').html(`
                <h3 id="dtoTitle" data-dto-number="${dtoData.dto_number}">
                    Extension DTO Work Menu | ${dtoData.dto_number} | ${dtoData.typical_no}
                </h3>
            `);

        $('#extensionDtoModal').modal({
            closable: false,
            onApprove : function() {
                return false;
            }
        }).modal('show');
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    }
}


const initializeExtensionDtoModalDetails = (panels, dtoData) => {
    $('#subheader-row').empty();
    $('#extension-modal-subheader').empty();
    $('#extension-table tbody').empty(); // Clear rows for new content
    let rowCounter = 1;  // Initialize a counter for each row

    const affectedItems = [
        { label: 'Ground Bar', work_center_id: '12|24|31', work_center: 'M5255|M5140|M5432', kmat_name: 'PA Copper & Insulator | PA Cable Compartment (CC) | PA Copper' }, //8BT2_PA Copper
        { label: 'PRC', work_center_id: '5|32', work_center: 'M5175|M5435', kmat_name: 'PA Top Box & PRC | PA Endwall' }, //8BT2_PA Endwall
        { label: 'Busbar', work_center_id: '12|31', work_center: 'M5255|M5432', kmat_name: 'PA Copper & Insulator | PA Copper' }, //8BT2_PA Copper
        { label: 'GFK insulation tube', work_center_id: '10', work_center: 'M5203', kmat_name: 'PA Installation Material' },
        { label: 'Partition Plate', work_center_id: '46|29|31', work_center: 'M5181|M5426|M5432', kmat_name: 'PA Shutter | PA Bushing | PA Copper' }, //8BT2_PA Bushing-8BT2_PA Copper
        { label: 'Ledges', work_center_id: '8|4', work_center: 'M5180|M5170', kmat_name: 'PA Bus Bar Compartment (BBC) | PA End Wall / WDA' },
        { label: 'Set of screws (Busbar)', work_center_id: '14', work_center: 'M5700', kmat_name: 'FA Support Plate And CTs' },
        { label: 'Insulation Box', work_center_id: '14|29', work_center: 'M5700|M5426', kmat_name: 'FA Support Plate And CTs | PA Bushing' }, //8BT2_PA Bushing
        { label: 'Earthing Switch Operating Tool', work_center_id: '', work_center: '', kmat_name: '' }
    ];

    $('#extension-table thead th:first-child').css('width', '250px'); // Adjust this value as necessary

    affectedItems.forEach(item => {
        //Earthing Switch Operating Tool sadece aksesuara eklenebilir.
        if (!dtoData.is_accessory && item.label === 'Earthing Switch Operating Tool') {
            return;
        }

        $('#extension-table tbody').append(`
        <tr>
            <td>
                <a class="extension-kmat-list">${item.label}</a>
            </td>
        </tr>
    `);
    });


    const panelKeys = Object.keys(panels);

    // Check if the DTO is an accessory or not
    if (dtoData.is_accessory) {
        accessoryColumnCounter = panelKeys.length;

        if (panelKeys.length === 0) {
            accessoryColumnCounter = 1;
            $('#extension-modal-header').attr('colspan', accessoryColumnCounter).text('ACCESSORIES').css('font-size', '1.3rem').append(`
                <button type="button" class="circular ui positive icon button tiny" onclick="addNewAccessoryColumn()"><i class="plus circle icon large"></i></button>
            `);

            // Default layout with one Accessories column if no panels exist
            $('#extension-modal-subheader').append(`
            <th id="accessory-col-1" class="align-middle" style="padding-bottom: 1px;border: none;">
                <div class="ui input accPanelInputDiv" style="width: 75%; margin: 0 auto; display: flex; align-items: center;">
                    <input type="text" class="accPanelInput" style="text-align: center;" 
                       placeholder="Enter Panel Number or Note (e.g. Sahadaki +K0${accessoryColumnCounter})" value=""/>
                </div>
            </th>`);

            $('#subheader-row').append('<th>Added Material</th>');

            $('#extension-table tbody tr').each(function () {
                $(this).append(`
                    <td class="added-material-col-${rowCounter} clickable-cell panel-col">
                        <div class="dropdown-container">
                            <select class="ui search dropdown added-acc-material-select">
                                <option value="">Search Material</option>
                            </select>
                            <button type="button" class="circular ui positive icon button tiny add-dropdown-button">
                                <i class="plus icon"></i>
                            </button>
                        </div>
                    </td>
            `);
                rowCounter++;
            });

            $('#extension-table tbody tr').each(async function () {
                await initializeMaterialSelect('.added-acc-material-select');
            });

        } else {
            $('#extension-modal-header').attr('colspan', accessoryColumnCounter).text('ACCESSORIES').css('font-size', '1.3rem').append(`
                <button type="button" class="circular ui positive icon button tiny" onclick="addNewAccessoryColumn()"><i class="plus circle large icon"></i></button>
            `);

            // Add dynamic columns for each panel (response contains data)
            panelKeys.forEach((panelKey, index) => {
                $('#extension-modal-subheader').append(`
                    <th id="accessory-col-${index + 1}" colspan="1" class="align-middle" style="padding-bottom: 1px;border: none;">
                        <div class="input-group d-flex align-items-center">
                            <div class="mx-auto text-center" style="margin-bottom:0.6rem;"> 
                                <span class="extension-acc-panel-name" data-tooltip="Click to update panel name" 
                                      onclick="openExtensionAccPanelNameChangeModal('${panelKey}','${dtoData.dto_number}', '${dtoData.typical_no}')">
                                    ${panelKey}
                                </span>
                            </div>
                            <button class="circular ui icon red button mini"
                                onclick="removeAccessoryColumn('${panelKey}', ${index + 1}, '${dtoData.dto_number}', '${dtoData.typical_no}')">
                                Remove Panel
                            </button>
                        </div>
                    </th>
                `);
                $('#subheader-row').append('<th>Added Materials</th>');

                // Append data for each affected KMAT item under this new Accessories column
                $('#extension-table tbody tr').each(function (i) {
                    let kmatName = affectedItems[i].label;

                    if (panels[panelKey] && panels[panelKey][kmatName] && Array.isArray(panels[panelKey][kmatName])) {
                        let materialCells = panels[panelKey][kmatName].map(material => {

                            let noteButtonHtml = '';
                            if (!material.note) {
                                noteButtonHtml = `<button data-id="${material.extension_work_id}" data-dto="${dtoData.dto_number}" data-dtotypical="${dtoData.typical_no}"
                                                                    data-tooltip="Add Note to Selected Material" data-position="top right" 
                                                                    type="button" class="circular ui blue icon button tiny add-accessory-note-button">
                                                                    <i class="sticky note outline icon"></i>
                                                                </button>`
                            } else {
                                noteButtonHtml = `<button data-id="${material.extension_work_id}" data-note="${material.note}" data-dto="${dtoData.dto_number}" data-dtotypical="${dtoData.typical_no}"
                                                                    data-tooltip="${material.note}" data-position="top right" type="button" class="ui blue icon button mini edit-accessory-note-button">
                                                                    Note
                                                                </button>`
                            }

                            return `<div class="dropdown-container" style="width:80%;margin:0 auto;">
                                        <div class="ui search dropdown added-acc-material-select selection disabled" style="background:palegreen;opacity:0.9;padding-right:50px;">
                                            <select>
                                                <option value="">Search Material</option>
                                                <option value="${material.material_no}" class="addition">${material.material_no}</option>
                                            </select>
                                            <div class="text"><b>${material.material_no}</b> - ${material.material_description}</div>
                                            <div class="menu transition hidden" tabindex="-1">
                                                <div class="item active selected" data-value="${material.material_no}">
                                                    <b>${material.material_no}</b> - ${material.material_description}
                                                </div>
                                            </div>
                                        </div>
                                        <button data-id="${material.extension_work_id}" data-dto="${dtoData.dto_number}" data-dtotypical="${dtoData.typical_no}"
                                                    data-tooltip="Remove selected material" data-position="top center"
                                                    type="button" class="circular ui negative icon button tiny remove-added-accessory-button">
                                                    <i class="trash icon"></i>
                                        </button>
                                        ${noteButtonHtml}
                                        <button type="button" class="circular ui positive icon button tiny add-dropdown-button">
                                            <i class="plus icon"></i>
                                        </button>
                                    </div>`;
                        }).join('<br>');

                        // Append the materials to the cell
                        $(this).append(`<td class="panel-col panel-col-${index + 1} clickable-cell">
                                            ${materialCells}
                                        </td>`);
                    } else {
                        $(this).append(`
                                <td class="panel-col panel-col-${index + 1} clickable-cell">
                                    <div class="dropdown-container">
                                        <select class="ui search dropdown added-acc-material-select">
                                            <option value="">Search Material</option>
                                        </select>
                                        <button type="button" class="circular ui positive icon button tiny add-dropdown-button">
                                            <i class="plus icon"></i>
                                        </button>
                                    </div>
                                </td>
                        `);
                    }
                });

                // Initialize select boxes
                $('#extension-table tbody tr').each(async function () {
                    await initializeMaterialSelect('.added-acc-material-select');
                });
            });
        }

        $('#extensionDtoModal .footer').css('display', '')
            .html(`<div id="saveExtensionAccessories" class="ui animated positive fade button" 
                        onclick="saveExtensionAccessoryChange('${dtoData.dto_number}', '${dtoData.typical_no}')">
                        <div class="visible content">Save Changes</div>
                        <div class="hidden content">
                            <i class="save icon"></i>
                        </div>
                    </div>`);
    } else {
        $('#extension-modal-header').attr('colspan', panelKeys.length).text('Current Order');

        // Now create Current Order section dynamically based on the number of panels
        panels.forEach((panel, index) => {
            $('#subheader-row').append(`
                <th id="current-order-col-${index + 1}" colspan="1" class="align-middle">
                    <div class="input-group d-flex align-items-center w-100 mx-auto">
                        <h5 class="mx-auto text-center"><strong>${panel.ortz_kz}</strong></h5>
                    </div>
                </th>
            `);

            $('#extension-table tbody tr').each(function(i) {
                let hasWork = panel.has_work[affectedItems[i].label];
                let cellValue = hasWork ? 'Has Work' : '-';
                let backgroundColor = hasWork ? 'background-color: beige!important;' : '';

                $(this).append(`
                    <td class="panel-col panel-col-${index + 1} clickable-cell"
                        style="${backgroundColor}"
                        onclick="openExtensionKmatListModal('${dtoData.dto_number}', '${dtoData.typical_no}', '${escape(JSON.stringify(affectedItems[i]))}', '${panel.ortz_kz}')">
                        ${cellValue}
                    </td>
                `);
            });
        });
    }

    $('#extension-content').show();
};



let accessoryColumnCounter = 1; // Keeps track of how many accessory columns we have
const addNewAccessoryColumn = () => {
    accessoryColumnCounter++;

    // Update the colspan of the Accessories header
    $('#extension-modal-header').attr('colspan', accessoryColumnCounter);

    // Add a new input column for the panel name with a remove button
    $('#extension-modal-subheader').append(`
        <th id="accessory-col-${accessoryColumnCounter}" class="align-middle">
            <div class="ui input accPanelInputDiv" style="width: 75%; margin: 0 auto; display: flex; align-items: center;">
                <input type="text" class="accPanelInput" 
                       style="text-align: center;" 
                       placeholder="Enter Panel Number or Note (e.g. Sahadaki +K0${accessoryColumnCounter})" value=""/>
                <button class="ui circular red icon button" 
                        style="margin-left: 0.5rem;" 
                        onclick="removeAccessoryColumn('', ${accessoryColumnCounter}, '', '')">
                    <i class="minus icon"></i>
                </button>
            </div>
        </th>
    `);

    // Add new empty "Eklenen Malzeme" columns for each KMAT row
    $('#subheader-row').append('<th>Added Material</th>');

    $('#extension-table tbody tr').each(function (index) {
        $(this).append(`
              <td class="panel-col panel-col-${accessoryColumnCounter} clickable-cell">
                <div class="dropdown-container">
                    <select class="ui search dropdown added-acc-material-select">
                        <option value="">Search Material</option>
                    </select>
                    <button type="button" class="circular ui positive icon button tiny add-dropdown-button">
                        <i class="plus icon"></i>
                    </button>
                </div>
            </td>
        `);
    });

    // Initialize the new material select inputs
    $('#extension-table tbody tr').each(async function () {
        await initializeMaterialSelect('.added-acc-material-select');
    });
};

$(document).on('click', '.add-dropdown-button', async function () {
    const $container = $(this).closest('.dropdown-container');
    const newDropdown = `
                            <div class="dropdown-container">
                                <select class="ui search dropdown added-acc-material-select">
                                    <option value="">Search Material</option>
                                </select>
                                <button type="button" class="circular ui negative icon button tiny remove-dropdown-button">
                                    <i class="minus icon"></i>
                                </button>
                            </div>
                        `;
    $container.after(newDropdown);

    // Initialize the new dropdown
    await initializeMaterialSelect('.added-acc-material-select');
});

$(document).on('click', '.remove-dropdown-button', async function () {
    const $container = $(this).closest('.dropdown-container');
    const allDropdowns = $container.parent().find('.dropdown-container');

    // Ensure at least one dropdown remains
    if (allDropdowns.length > 1) {
        $container.remove();
    } else {
        alert('At least one material dropdown must remain.');
    }
});

async function removeAccessoryColumn(panelName, columnIndex, dtoNumber, typicalNo) {
    if (confirm(`Are you sure you want to remove the panel: ${panelName}?`)) {
        if (panelName === '') {
            // Remove the column from the header
            $(`#accessory-col-${columnIndex}`).remove();

            // Remove all rows corresponding to this column
            $(`.panel-col-${columnIndex}`).remove();

            // Remove one "Eklenen Malzeme" column to maintain balance
            $('#subheader-row th:last-child').remove();

            // Update the colspan of the Accessories header
            accessoryColumnCounter--;
            $('#extension-modal-header').attr('colspan', accessoryColumnCounter);

            // Adjust the width of remaining select boxes
            $('.added-acc-material-select').css('width', '100%');
        } else {

            try {
                const response = await axios.post('/dpm/dtoconfigurator/api/controllers/ExtensionDtoController.php?',
                    {
                        action: 'removeExtensionAccessoryChange',
                        projectNo: getUrlParam('project-no'),
                        nachbauNo: getUrlParam('nachbau-no'),
                        dtoNumber: dtoNumber,
                        typicalNo: typicalNo,
                        panelName: panelName
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                if (response.status === 200) {
                    fireToastr('success', 'Accessory panel removed successfully.');
                    // Remove the column from the table
                    $(`#accessory-col-${columnIndex}`).remove();

                    // Remove all rows corresponding to this column
                    $(`.panel-col-${columnIndex}`).remove();

                    // Remove one "Eklenen Malzeme" column to maintain balance
                    $('#subheader-row th:last-child').remove();

                    // Update the colspan of the Accessories header
                    accessoryColumnCounter--;
                    $('#extension-modal-header').attr('colspan', accessoryColumnCounter);

                    // Adjust the width of remaining select boxes
                    $('.added-acc-material-select').css('width', '100%');

                    await getExtensionDtosOfNachbau();
                    if (accessoryColumnCounter === 0) {
                        const dtoData = {
                            dto_number: dtoNumber,
                            typical_no: typicalNo,
                            is_accessory: true
                        };
                        await getOverviewOfExtensionWorks(dtoData);
                    }

                    await getOrderSummaryV2();

                    hideElement('#projectWorkContainer')
                    showLoader('#projectWork');
                    await getProjectData();

                } else {
                    showErrorDialog(response.message);
                }
            } catch(error) {
                const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                showErrorDialog(`${errorMessage}`);
            }
        }
    }
}

async function initializeMaterialSelect(selector) {
    let materialMap = {};

    $(selector).dropdown({
        apiSettings: {
            url: `/dpm/dtoconfigurator/api/controllers/MaterialController.php?action=getMaterialsBySearch&keyword={query}`,
            cache: false,
            onResponse: function(response) {
                const menuElement = document.querySelector('.added-acc-material-select .menu');
                const materials = Array.isArray(response) ? response : Object.values(response);
                let results = [];

                if (materials.length === 0) {
                    if (menuElement)
                        menuElement.innerHTML = '';
                    return { results };
                }

                results = materials.map(material => {
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
            value: 'value',
        },
        minCharacters: 2,
        clearable: true,
        selectOnKeydown:false,
        forceSelection: false,
        allowAdditions: false
    });
}

$('#materialDefineModalBtn').on('click', async function () {
    window.open('/dpm/dtoconfigurator/core/material-define/index.php', '_blank');
});

async function saveExtensionAccessoryChange(dtoNumber, typicalNo) {
    let extensionDtoData = [];
    let panelInputError = false;

    const affectedItems = [
        { label: 'Ground Bar', work_center_id: '12|24|31', work_center: 'M5255|M5140|M5432', kmat_name: 'PA Copper & Insulator | PA Cable Compartment (CC) | PA Copper' }, //8BT2_PA Copper
        { label: 'PRC', work_center_id: '5|32', work_center: 'M5175|M5435', kmat_name: 'PA Top Box & PRC | PA Endwall' }, //8BT2_PA Endwall
        { label: 'Busbar', work_center_id: '12|31', work_center: 'M5255|M5432', kmat_name: 'PA Copper & Insulator | PA Copper' }, //8BT2_PA Copper
        { label: 'GFK insulation tube', work_center_id: '10', work_center: 'M5203', kmat_name: 'PA Installation Material' },
        { label: 'Partition Plate', work_center_id: '46|29|31', work_center: 'M5181|M5426|M5432', kmat_name: 'PA Shutter | PA Bushing | PA Copper' },
        { label: 'Ledges', work_center_id: '8|4', work_center: 'M5180|M5170', kmat_name: 'PA Bus Bar Compartment (BBC) | PA End Wall / WDA' },
        { label: 'Set of screws (Busbar)', work_center_id: '14', work_center: 'M5700', kmat_name: 'FA Support Plate And CTs' },
        { label: 'Insulation Box', work_center_id: '14|29', work_center: 'M5700|M5426', kmat_name: 'FA Support Plate And CTs | PA Bushing' }, //8BT2_PA Bushing
        { label: 'Earthing Switch Operating Tool', work_center_id: '', work_center: '', kmat_name: '' }
    ];

    // Initialize an array for panels
    let panels = [];

    // Case 1: Panels already exist — collect panel names from the header row (th > h5)
    $('#extension-modal-subheader th').each(function() {
        const panelName = $(this).find('.extension-acc-panel-name').text().trim();
        if (panelName) {
            panels.push(panelName);  // Add to the beginning of the array
        }
    });

    // Case 2: No panels added yet — panel input field exists (#accPanelInput)
    $('.accPanelInputDiv .accPanelInput').each(function() {
        const accPanelInputValue = $(this).val().trim();

        if (!accPanelInputValue) {
            showErrorDialog('Panel input field cannot be empty.');
            panelInputError = true;
            return;
        }

        // Add the panel input value to the panels array
        panels.push(accPanelInputValue);
    });
    if (panelInputError) return;

    if (panels.length === 0) {
        showErrorDialog('Panel counts must be more than 0.');
        return;
    }


    // Loop through KMAT rows in the table
    $('#extension-table tbody tr').each(function() {
        let kmatName = $(this).find('.extension-kmat-list').text().trim();

        // Loop through each panel column in the row
        $(this).find('td.panel-col').each(function(index) {
            let selectedDropdownMaterialArray = $(this).find('.added-acc-material-select').dropdown('get value');
            let affectedKmat = affectedItems.find(item => item.label === kmatName);

            //Eğer SADECE bir tane eklendiyse
            if (!Array.isArray(selectedDropdownMaterialArray))
                selectedDropdownMaterialArray = selectedDropdownMaterialArray ? [selectedDropdownMaterialArray] : [];

            let panelInput = $(`#accessory-col-${index+1} .accPanelInput`).text(); // Get text inside h3
            let panel = panelInput || panels[index];  // Use input value or existing panel value

            selectedDropdownMaterialArray.forEach(material => {
                if (material) {
                    extensionDtoData.push({
                        kmatName,
                        affectedKmat,
                        materialNumber: material,
                        panel
                    });
                }
            });
        });
    });

    if (extensionDtoData.length === 0) {
        showErrorDialog('Please select at least one material.');
        return;
    }

    showConfirmationDialog({
        title: 'Are you sure?',
        htmlContent: 'Do you want to save your works as an accessory?',
        confirmButtonText: 'Yes!',
        confirmButtonColor: "green",
        onConfirm: async function () {
            try {
                const response = await axios.post('/dpm/dtoconfigurator/api/controllers/ExtensionDtoController.php?',
                    {
                        action: 'saveExtensionAccessoryChange',
                        projectNo: getUrlParam('project-no'),
                        nachbauNo: getUrlParam('nachbau-no'),
                        dtoNumber: dtoNumber,
                        typicalNo: typicalNo,
                        accessoryParentKmat: NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryParentKmat'],
                        extensionDtoData: extensionDtoData,
                        isRevisionNachbau: currentlyWorkingUser.isRevisionNachbau
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                if (response.status === 200) {
                    showSuccessDialog('All works are saved as an accessory successfully.').then(async () => {
                        const dtoData = {
                            dto_number: dtoNumber,
                            typical_no: typicalNo,
                            is_accessory: true
                        };

                        await getOverviewOfExtensionWorks(dtoData);
                        await getOrderSummaryV2();

                        await getExtensionDtosOfNachbau();

                        hideElement('#projectWorkContainer')
                        showLoader('#projectWork');
                        await getProjectData();

                    });
                } else {
                    showErrorDialog(response.message);
                }
            } catch(error) {
                const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                showErrorDialog(`${errorMessage}`);
            }
        },
    });
}



// EXTENSION KMAT LIST MODAL STARTS
let currentDtoNumber, currentKmatLabel, currentDtoTypicalNo, currentWorkCenterId, currentWorkCenter, currentWorkContent, currentPanelNo;

async function openExtensionKmatListModal(dtoNumber, typicalNo, itemStr, panelNo) {
    const extensionKmatItem = JSON.parse(unescape(itemStr));

    currentDtoNumber = dtoNumber;
    currentDtoTypicalNo = typicalNo;
    currentKmatLabel = extensionKmatItem.label;
    currentWorkCenterId = extensionKmatItem.work_center_id;
    currentWorkCenter = extensionKmatItem.work_center;
    currentWorkContent = extensionKmatItem.kmat_name;
    currentPanelNo = panelNo;

    await getExtensionDtoTableData();
}

async function getExtensionDtoTableData() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/ExtensionDtoController.php', {
            params: {
                action: 'getExtensionDtoTableData',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                dtoNumber: currentDtoNumber,
                typicalNo: currentDtoTypicalNo,
                kmatLabel: currentKmatLabel,
                workCenterId: currentWorkCenterId,
                workCenter: currentWorkCenter,
                workContent: currentWorkContent,
                panelNo: currentPanelNo
            }
        });

        if (response.status === 200) {
            const kmatList = response.data;
            await fillExtensionKmatListDataTable(kmatList);
            await initializeExtensionKmatListMaterialSelect();

            $('#extensionDtoKmatListModalHeader').text(currentWorkContent + ' | ' + currentDtoNumber + ' | ' + currentDtoTypicalNo + ' => Panel Number : ' + currentPanelNo);
            $('#extensionDtoKmatListModal').modal({ allowMultiple: true, closable: false, onApprove : function() { return false; } }).modal('show');
            $('#extensionDtoKmatListModal').draggable({ handle: '.header', containment: 'window' });

        } else {
            showErrorDialog(response.message);
        }
    } catch(error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`${errorMessage}`);
    }
}


async function fillExtensionKmatListDataTable(kmatList) {
    let tableSelector = $('#extensionDtoKmatListDataTable');
    if ($.fn.DataTable.isDataTable('#extensionDtoKmatListDataTable'))
        tableSelector.DataTable().clear().destroy();

    tableSelector.DataTable({
        data: kmatList,
        columnDefs: [
            { width: '3%', targets: [0], className: 'center aligned' },
            { width: '30%', targets: [1], className: 'center aligned' },
            { width: '7%', targets: [2], className: 'center aligned' },
            { width: '20%', targets: [3], className: 'center aligned' },
            { width: '4%', targets: [4, 5], className: 'center aligned' },
            { width: '7%', targets: [6], className: 'center aligned' },
            { width: '10%', targets: [7], className: 'center aligned' },
            { width: '18%', targets: [8], className: 'center aligned' },
            { width: '5%', targets: [9], className: 'center aligned' },
        ],
        columns: [
            { title: "Position", data: 'position', className: 'center aligned' },
            { title: "Added Nr.", className: 'center aligned',
                render: function (data, type, row) {
                    if (row.operation === 'add' || row.operation === 'replace')
                        return `<span class="extension-change-row">${row.material_added_number}</span>`;
                    else if (row.operation === 'delete')
                        return `<span class="extension-change-row">SİL</span>`;
                    else {
                        return `<div class="added-kmat-material-select-div">
                                    <select class="ui search dropdown added-kmat-material-select">
                                        <option value="">Search Material</option>
                                    </select>
                                </div>`;
                    }
                }
            },
            { title: "Deleted Nr.", data: 'material_deleted_number',className: 'center aligned' },
            { title: "Description", data: 'material_description',className: 'center aligned' },
            { title: "Qty", data: 'quantity', className: 'center aligned' },
            { title: "Unit", data: 'unit', className: 'center aligned' },
            { title: "Typical No", data: 'typical_no', className: 'center aligned' },
            { title: "Panel No", className: 'center aligned',
                data: null,
                render: function (data) {
                    return `${data.ortz_kz}/${data.panel_no}`;
                }
            },
            {
                title: "Notes",
                className: 'center aligned',
                data: function (data) {
                    return data.dto_number ? `<b>${data.dto_number}</b><br>${data.note || ''}` : (data.note || '');
                }
            },
            { data: function (data) {
                    if (data.operation === 'add' || data.operation === 'replace' || data.operation === 'delete') {
                        return `<button class="ui white button mini" data-tooltip="Remove selected row" data-position="top right" 
                                  onclick="removeExtensionChange(${data.extension_change_id})"> 
                                <i class="trash alternate large icon" style="width:5px;"></i> 
                            </button>`;
                    }
                    return '';
                },
                className: 'center aligned'
            }
        ],
        drawCallback: function () {
            $('#extensionDtoKmatListDataTable tbody tr').each(function () {
                const data = $('#extensionDtoKmatListDataTable').DataTable().row(this).data();

                // Apply conditional styling and hiding logic
                if (data) {
                    if (data.material_deleted_number.startsWith('003003') || data.material_deleted_number.startsWith('003013')) {
                        $(this).css({
                            'font-weight': 'bold',
                            'text-decoration': 'underline',
                            'color': 'mediumblue'
                        });

                        // Hide the parent .ui.dropdown of the select
                        $(this).find('.added-kmat-material-select-div').hide();
                    }

                    // Row background colors based on operation
                    if (data.operation === 'add') {
                        $(this).css({"background": "#67a863", "color": "white"});
                    }
                    if (data.operation === "delete") {
                        $(this).css({"background": "darkred", "color": "white"});
                    }
                    if (data.operation === "replace") {
                        $(this).css({"background": "darkcyan", "color": "white"});
                    }
                }
            });
        },
        paging: false, // Disable pagination
        searching: true, // Allow table search
        ordering: false,  // Disable column sorting
        deferRender: false,
        fixedHeader:true,
        autoWidth: false, // Disable auto-width
    });
}


async function initializeExtensionKmatListMaterialSelect() {
    let materialMap = {};

    $('.added-kmat-material-select').dropdown({
        apiSettings: {
            url: `/dpm/dtoconfigurator/api/controllers/MaterialController.php?action=getMaterialsBySearch&keyword={query}`,
            cache: false,
            onResponse: function(response) {
                const menuElement = document.querySelector('.added-kmat-material-select .menu');
                const materials = Array.isArray(response) ? response : Object.values(response);
                let results = [];

                if (materials.length === 0) {
                    if (menuElement)
                        menuElement.innerHTML = '';
                    return { results };
                }

                results = materials.map(material => {
                    materialMap[material.id] = {
                        stationCode: material.work_center,
                        stationName: material.work_content
                    };

                    return {
                        name: `<b>${material.material_number}</b> - ${material.description}`,
                        value: material.material_number
                    };
                });

                results.unshift({
                    name: `<span style="color:red;"><b>SİL</b></span>`,
                    value: 'CLEAR'
                });

                return { results };
            }
        },
        fields: {
            remoteValues: 'results',
            name: 'name',
            value: 'value',
        },
        minCharacters: 1,
        clearable: true,
        selectOnKeydown: false,
        forceSelection: false,
        allowAdditions: false
    });
}

async function getChangedRows() {
    let changedRows = [];

    $('#extensionDtoKmatListDataTable tbody tr').each(function() {
        const rowData = $('#extensionDtoKmatListDataTable').DataTable().row($(this)).data();

        if(rowData.extension_change_id)
            return;

        const addedMaterialNumber = $(this).find('.added-kmat-material-select').dropdown('get value');
        const deletedMaterialNumber = rowData.material_deleted_number.replace(/^00/, '');

        if (addedMaterialNumber) {
            let operation = '';
            if (addedMaterialNumber === 'CLEAR' && deletedMaterialNumber)
                operation = 'delete';
            else if (addedMaterialNumber && addedMaterialNumber !== 'CLEAR' && deletedMaterialNumber)
                operation = 'replace';

            const changedRowData = {
                dtoNumber: currentDtoNumber,
                workCenterId: currentWorkCenterId,
                workCenter: currentWorkCenter,
                workContent: currentWorkContent,
                dtoTypicalNo: currentDtoTypicalNo,
                position: rowData.position,
                materialAdded: addedMaterialNumber ?? '',
                materialDeleted: deletedMaterialNumber,
                materialDescription: rowData.material_description,
                operation:operation,
                extensionKmatName: currentKmatLabel,
                qty: rowData.quantity,
                unit: rowData.unit,
                typicalNo: rowData.typical_no,
                ortzKz: rowData.ortz_kz,
                panelNo: rowData.panel_no
            };
            changedRows.push(changedRowData); // Push the row data into the array
        }
    });

    return changedRows;
}

$('#saveExtensionChangesBtn').on('click', async function(e) {
    e.stopPropagation();
    const changedRows = await getChangedRows();
    if (changedRows.length === 0) {
        // fireToastr("warning", "You need to make some changes!");
        $('#extensionDtoKmatListModal').modal('hide');
        return;
    }

    $(this).addClass('loading disabled');
    await saveExtensionDtoChange(changedRows);
    $(this).removeClass('loading disabled');
});

async function saveExtensionDtoChange(changedRows) {
    showConfirmationDialog({
        title: 'Save Changes?',
        htmlContent: 'Do you want to save all changes?',
        confirmButtonText: 'Yes, save it!',
        confirmButtonColor: "green",
        onConfirm: async function () {
            try {
                const response = await axios.post('/dpm/dtoconfigurator/api/controllers/ExtensionDtoController.php?',
                    {
                        action: 'saveExtensionDtoChange',
                        projectNo: getUrlParam('project-no'),
                        nachbauNo: getUrlParam('nachbau-no'),
                        changedRows: changedRows,
                        isRevisionNachbau: currentlyWorkingUser.isRevisionNachbau
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                if (response.status === 200) {
                    showSuccessDialog('Changes are saved successfully.').then(async () => {
                        await getExtensionDtoTableData();

                        const dtoData = {
                            dto_number: currentDtoNumber,
                            typical_no: currentDtoTypicalNo,
                            is_accessory: false
                        };

                        await getOverviewOfExtensionWorks(dtoData);
                        await getOrderSummaryV2();

                        await getExtensionDtosOfNachbau();

                        hideElement('#projectWorkContainer')
                        showLoader('#projectWork');
                        await getProjectData();
                    });
                } else {
                    showErrorDialog(response.message);
                }
            } catch (error) {
                const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                showErrorDialog(`Error: ${errorMessage}`);
            }

        },
    });
}

async function removeExtensionChange(extensionChangeId) {

    showConfirmationDialog({
        title: 'Remove change?',
        htmlContent: 'Do you want to remove selected change?',
        confirmButtonText: 'Yes, delete it!',
        confirmButtonColor: "#d33",
        onConfirm: async function () {
            try {
                const response = await axios.post('/dpm/dtoconfigurator/api/controllers/ExtensionDtoController.php?',
                    {
                        action: 'removeExtensionDtoChange',
                        extensionChangeId: extensionChangeId
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );
                if(response.status === 200) {
                    showSuccessDialog('Change removed successfully.').then(async () => {
                        await getExtensionDtoTableData();

                        const dtoData = {
                            dto_number: currentDtoNumber,
                            typical_no: currentDtoTypicalNo,
                            is_accessory: false
                        };

                        await getOverviewOfExtensionWorks(dtoData);
                        await getOrderSummaryV2();

                        await getExtensionDtosOfNachbau();

                        hideElement('#projectWorkContainer')
                        showLoader('#projectWork');
                        await getProjectData();
                    });
                } else {
                    showErrorDialog(response.message);
                }
            } catch (error) {
                const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                showErrorDialog(`Error: ${errorMessage}`);
            }
        },
    });
}
// EXTENSION KMAT LIST MODAL ENDS


// EXTENSION ADD MATERIAL LIST TO KMAT MODAL STARTS
$('#extensionMaterialDefineModalButton').on('click', async function () {
    window.open('/dpm/dtoconfigurator/core/material-define/index.php', '_blank');
});

let materialObj = {}
$('#addMaterialListToExtensionDto').on('click', async function () {
    resetAddExtensionMaterialListModal();
    await initializeMaterialSelect('#extension-material-added-select');
    $('#addExtensionMaterialListModalHeader').html(`<h4>Add New Material List | ${currentWorkContent} | ${currentDtoNumber} + ${currentDtoTypicalNo} + Panel : ${currentPanelNo}</h4>`);

    $('#extension-material-added-select').dropdown('setting', 'onChange', async function(value, text, $selectedItem) {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/MaterialController.php', { params: { action: 'getMaterialWithWorkCenters', materialNo: value } });
        const material = response.data;

        $('#extension-material-description').val(material.description);
        if (!material.work_center)
        {
            $('#extension-material-work-center').val('Not Defined').css('color', 'red');
            $('#extension-material-work-content').val('Not Defined').css('color', 'red');
        } else {
            $('#extension-material-work-center').val(material.work_center).css('color', '');
            $('#extension-material-work-content').val(material.work_content).css('color', '');
        }

        materialObj = {
            material_number: value,
            description: material.description,
            work_center_id: material.work_center_id,
            work_center: material.work_center,
            work_content: material.work_content
        }
    });

    $('#extension-typical-number').val(currentDtoTypicalNo);
    $('#extension-panel-number').val(currentPanelNo);

    $('#addExtensionMaterialListModal').modal({ allowMultiple: true, closable: false }).modal('show');
    $('#addExtensionMaterialListModal').draggable({ handle: '.header', containment: 'window' });
});

$('#saveExtensionMaterialListBtn').on('click', async function (event) {
    let saveObjData = {};

    //Error controls
    if (Object.keys(materialObj).length === 0) {
        showErrorDialog('Please select a material.');
        event.stopPropagation(); return false; // Prevent modal from closing
    }

    if (!materialObj.work_center){
        showErrorDialog('Please update work center of material.');
        event.stopPropagation(); return false;
    }

    if (!currentWorkCenter.includes(materialObj.work_center))
    {
        const message = `<b>Work Centers does not match!</b> <br><br>
                                Work Center of material must be <span style="font-weight:700;">${currentWorkContent}</span>!<br><br>
                                Work Center: <br><span style="font-weight:700;">${materialObj.work_center} - ${materialObj.work_content}</span>`;
        showErrorDialog(message);
        event.stopPropagation();
        return false;
    }

    const quantity = $('#extension-material-quantity').val();
    if (!quantity || parseInt(quantity) <= 0) {
        showErrorDialog('Quantity is required and must be more than 0');
        event.stopPropagation(); return false;
    }

    const unit = $('#extension-material-unit').val();
    if (!unit) {
        showErrorDialog('Unit is required');
        event.stopPropagation(); return false;
    }

    saveObjData = {
        dtoNumber: currentDtoNumber,
        workCenterId: materialObj.work_center_id,
        workCenter: materialObj.work_center,
        workContent: materialObj.work_content,
        parentKmat: '',
        subKmat: '',
        dtoTypicalNo: currentDtoTypicalNo,
        position: '',
        materialAdded: materialObj.material_number,
        materialDeleted: '',
        materialDescription: materialObj.description,
        operation: 'add',
        extensionKmatName: currentKmatLabel,
        qty: quantity + '.000',
        unit: unit,
        note: $('#extension-material-note').val(),
        typicalNo: currentDtoTypicalNo,
        ortzKz: currentPanelNo,
        panelNo:  ''
    }

    const ids = currentWorkCenterId.split('|');
    if ((ids.includes('12') && materialObj.work_center_id !== '31') || ids.includes('46') || materialObj.work_center_id === '4') {
        try {
            const response = await axios.get(`/dpm/dtoconfigurator/api/controllers/WorkCenterController.php`, {
                params: {
                    action: 'getSubKmatNamesOfMaterial',
                    projectNo: getUrlParam('project-no'),
                    materialNo: materialObj.material_number
                }
            });

            if (response.status === 200) {
                const data = response.data;

                const hasNonEmptySubKmat = data.some(item => item.sub_kmat_name && item.sub_kmat_name.trim() !== '');

                if (!hasNonEmptySubKmat) {
                    let saveObjArray = [];

                    const parentKmats = data.map(item => item.parent_kmat).filter(pk => pk && pk.trim() !== '');
                    saveObjData.parentKmat = parentKmats.join('|');
                    saveObjData.subKmat = '';

                    saveObjArray.push(saveObjData);
                    await saveExtensionDtoChange(saveObjArray);
                } else {
                    const subKmatNames = data
                        .filter(item => item.sub_kmat_name && item.sub_kmat_name.trim() !== '')
                        .map(item => item.sub_kmat_name);

                    $('#chooseExtensionMaterialSubWorkCenterModal .currentWorkCenterSpan').text(materialObj.work_center + ' - ' + materialObj.work_content);
                    $('#chooseExtensionMaterialSubWorkCenterModal .subWorkCenterRadioItems').html(`
                        <div class="field" id="field-subkmat1">
                            <div class="ui radio checkbox">
                              <input type="radio" name="sub-kmat-option" value="${subKmatNames[0]}" id="radio-wc-subkmat1">
                              <label style="cursor:pointer;">
                                 ${subKmatNames[0]}
                              </label>
                            </div>
                        </div>
                        <div class="field" id="field-subkmat2">
                            <div class="ui radio checkbox">
                              <input type="radio" name="sub-kmat-option" value="${subKmatNames[1]}" id="radio-wc-subkmat2">
                              <label style="cursor:pointer;">
                                 ${subKmatNames[1]}
                              </label>
                            </div>
                        </div>
                    `);

                    $('#chooseExtensionMaterialSubWorkCenterModal').data('saveObjData', saveObjData);

                    $('#chooseExtensionMaterialSubWorkCenterModal').modal({
                        allowMultiple: true,
                        closable: false
                    }).modal('show');

                    $('#chooseExtensionMaterialSubWorkCenterModal').draggable({handle: '.header', containment: 'window'});
                }
            }
        } catch (error) {
            const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
            showErrorDialog(`Error: ${errorMessage}`);
        }
    }
    else {
        try {
            const response = await axios.get(`/dpm/dtoconfigurator/api/controllers/WorkCenterController.php`, {
                params: {
                    action: 'getParentKmatsOfMaterial',
                    projectNo: getUrlParam('project-no'),
                    materialNo: materialObj.material_number
                }
            });

            if (response.status === 200) {
                let saveObjArray = [];

                saveObjData.parentKmat = response.data.join('|');
                saveObjData.subKmat = '';

                saveObjArray.push(saveObjData)
                await saveExtensionDtoChange(saveObjArray);
            }
        } catch (error) {
            const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
            showErrorDialog(`Error: ${errorMessage}`);
        }
    }
});


$('#removeAllExtensionChangesBtn').on('click', async function (event) {
    event.stopPropagation();
    showConfirmationDialog({
        title: 'Remove All Existing Works?',
        htmlContent: 'Do you really want to delete all changes?',
        confirmButtonText: 'Yes, delete it!',
        confirmButtonColor: "#d33",
        onConfirm: async function () {
            try {
                const response = await axios.post('/dpm/dtoconfigurator/api/controllers/ExtensionDtoController.php?',
                    {
                        action: 'removeAllExtensionDtoChanges',
                        projectNo: getUrlParam('project-no'),
                        nachbauNo: getUrlParam('nachbau-no'),
                        extensionKmatName: currentKmatLabel
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                if (response.status === 200) {
                    showSuccessDialog('Existing changes are removed successfully.').then(async () => {
                        await getExtensionDtoTableData();

                        const dtoData = {
                            dto_number: currentDtoNumber,
                            typical_no: currentDtoTypicalNo,
                            is_accessory: false
                        };

                        await getOverviewOfExtensionWorks(dtoData);
                        await getOrderSummaryV2();
                    });
                } else {
                    showErrorDialog(response.message);
                }
            } catch (error) {
                const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                showErrorDialog(`Error: ${errorMessage}`);
            }
        },
    });
});


$('#chooseExtensionMaterialSubWorkCenterConfirmBtn').on('click', async function (event) {
    const saveObjData = $('#chooseExtensionMaterialSubWorkCenterModal').data('saveObjData');
    const selectedSubKmat = $('input[name="sub-kmat-option"]:checked').val();

    if (!selectedSubKmat) {
        showErrorDialog('Please select a sub-kmat.');
        event.stopPropagation();
        return false;
    }

    try {
        const response = await axios.get(`/dpm/dtoconfigurator/api/controllers/WorkCenterController.php`, {
            params: {
                action: 'getParentAndSubKmatsOfMaterialBySubKmatName',
                projectNo: getUrlParam('project-no'),
                materialNo: materialObj.material_number,
                subKmatName: selectedSubKmat
            }
        });

        if (response.status === 200) {
            let saveObjArray = [];
            saveObjData.parentKmat = response.data.parentKmats;
            saveObjData.subKmat = response.data.subKmats;

            saveObjArray.push(saveObjData)
            await saveExtensionDtoChange(saveObjArray);
        }
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    }
});


function resetAddExtensionMaterialListModal(){
    $('#extension-material-added-select').dropdown('clear');
    $('#extension-typical-number').val('');
    $('#extension-panel-number').val('');
    $('#extension-material-work-center').val('').attr('placeholder', 'Please First Select Material');
    $('#extension-material-work-content').val('').attr('placeholder', 'Please First Select Material');
    $('#extension-material-quantity').val(1);
    $('#extension-material-unit').val('ST');
}

// EXTENSION ADD MATERIAL LIST TO KMAT MODAL ENDS

$(document).on('click', '.remove-added-accessory-button', async function (event) {
    const extensionWorkId = $(this).data('id');
    const dtoNumber = $(this).data('dto');
    const dtoTypical = $(this).data('dtotypical');

    if (!extensionWorkId) {
        showErrorDialog('Missing material, kmat or panel information.');
        event.stopPropagation();
        return false;
    }

    showConfirmationDialog({
        title: 'Remove Selected Material?',
        htmlContent: 'Are you sure to delete selected material?',
        confirmButtonText: 'Yes, delete it!',
        confirmButtonColor: "#d33",
        onConfirm: async function () {
            try {
                const response = await axios.post('/dpm/dtoconfigurator/api/controllers/ExtensionDtoController.php?',
                    {
                        action: 'removeSelectedMaterialExtensionChange',
                        projectNo: getUrlParam('project-no'),
                        nachbauNo: getUrlParam('nachbau-no'),
                        dtoNumber: dtoNumber,
                        extensionWorkId: extensionWorkId
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                if (response.status === 200) {
                    showSuccessDialog('Material is removed from accessory.').then(async () => {

                        const dtoData = {
                            dto_number: dtoNumber,
                            typical_no: dtoTypical,
                            is_accessory: true
                        };

                        await getOverviewOfExtensionWorks(dtoData);

                        await getOrderSummaryV2();
                        await getExtensionDtosOfNachbau();

                        hideElement('#projectWorkContainer')
                        showLoader('#projectWork');
                        await getProjectData();
                    });
                } else {
                    showErrorDialog(response.message);
                }
            } catch (error) {
                const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                showErrorDialog(`Error: ${errorMessage}`);
            }
        }
    });
});

async function openExtensionAccPanelNameChangeModal(extensionAccPanelName, dtoNumber, dtoTypical) {
    $('#extensionAccPanelNameChangeModalContent').html('');
    $('#extensionAccPanelNameChangeModalFooter').html('');
    const modalContent = `<div class="ui form input-extension-acc-panel-name-div">
                                    <input type="text" id="input-extension-acc-panel-name">
                                 </div>`
    const modalFooter = `<div class="ui cancel button">Cancel</div>
                                <div id="updateExtensionAccPanelNameChange" class="ui green approve button">Update</div>`;

    $('#extensionAccPanelNameChangeModalContent').html(modalContent);
    $('#extensionAccPanelNameChangeModalFooter').html(modalFooter);

    $('#input-extension-acc-panel-name').val(extensionAccPanelName);

    $('#updateExtensionAccPanelNameChange').attr('data-panel-name', extensionAccPanelName);
    $('#updateExtensionAccPanelNameChange').attr('data-dtonumber', dtoNumber);
    $('#updateExtensionAccPanelNameChange').attr('data-dtotypical', dtoTypical);

    $('#extensionAccPanelNameChangeModal').modal({ allowMultiple: true, closable: false}).modal('show');
    $('#extensionAccPanelNameChangeModal').draggable({ handle: '.header', containment: 'window' });
}

$(document).on('click', '#updateExtensionAccPanelNameChange', async function (event) {
    event.stopPropagation();
    $(this).addClass('loading disabled');

    const dtoNumber = $(this).data('dtonumber');
    const dtoTypical = $(this).data('dtotypical');
    const existingPanelName = $(this).data('panel-name');
    const inputNewPanelName = $('#input-extension-acc-panel-name').val();

    try {
        await axios.post('/dpm/dtoconfigurator/api/controllers/ExtensionDtoController.php?',
            {
                action: 'updateExtensionPanelNameChange',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                dtoNumber: dtoNumber,
                existingPanelName: existingPanelName,
                inputNewPanel: inputNewPanelName
            },
            { headers: { 'Content-Type': 'multipart/form-data' }}
        );

        showSuccessDialog('Panel ' + existingPanelName + ' is changed to ' + inputNewPanelName + ' successfully.').then(async () => {
            const dtoData = {
                dto_number: dtoNumber,
                typical_no: dtoTypical,
                is_accessory: true
            };

            $('#extensionAccPanelNameChangeModal').modal('hide');
            await getOverviewOfExtensionWorks(dtoData);
            await getOrderSummaryV2();

            hideElement('#projectWorkContainer')
            showLoader('#projectWork');
            await getProjectData();
        });

    } catch(error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`${errorMessage}`);
    } finally {
        $(this).removeClass('loading disabled');
    }
});

$(document).on('click', '.add-accessory-note-button', async function (event) {
    const extensionWorkId = $(this).data('id');
    const dtoNumber = $(this).data('dto');
    const dtoTypical = $(this).data('dtotypical');

    if (!extensionWorkId) {
        showErrorDialog('Missing extension work id information.');
        event.stopPropagation();
        return false;
    }

    $('#extensionAccMaterialAddNoteModalContent').html('');
    $('#extensionAccMaterialAddNoteModalFooter').html('');
    const modalContent = `<div class="ui form">
                                    <div class="field">
                                        <label for="input-extension-accessory-material-note">Note</label>
                                        <textarea id="input-extension-accessory-material-note" rows="2" placeholder="Write a note..."></textarea>
                                    </div>
                                 </div>`
    const modalFooter = `<div class="ui cancel button">Cancel</div>
                                <div id="saveExtensionAccessoryNoteButton" class="ui green approve button" style="margin:0;">Add Note</div>`;

    $('#extensionAccMaterialAddNoteModalContent').html(modalContent);
    $('#extensionAccMaterialAddNoteModalFooter').html(modalFooter);

    $('#saveExtensionAccessoryNoteButton').attr('data-extensionid', extensionWorkId);
    $('#saveExtensionAccessoryNoteButton').attr('data-dtonumber', dtoNumber);
    $('#saveExtensionAccessoryNoteButton').attr('data-dtotypical', dtoTypical);

    $('#extensionAccMaterialAddNoteModal').modal({ allowMultiple: true, closable: false}).modal('show');
    $('#extensionAccMaterialAddNoteModal').draggable({ handle: '.header', containment: 'window' });
});

$(document).on('click', '#saveExtensionAccessoryNoteButton', async function (event) {
    event.stopPropagation();
    $(this).addClass('loading disabled');

    const dtoNumber = $(this).data('dtonumber');
    const dtoTypical = $(this).data('dtotypical');
    const extensionWorkId = $(this).data('extensionid');
    const extensionNote = $('#input-extension-accessory-material-note').val();

    if (!extensionNote) {
        showErrorDialog('Please enter a note.');
        return false;
    }

    try {
        await axios.post('/dpm/dtoconfigurator/api/controllers/ExtensionDtoController.php?',
            {
                action: 'handleNoteToExtensionAccessoryMaterial',
                extensionWorkId: extensionWorkId,
                extensionNote: extensionNote
            },
            { headers: { 'Content-Type': 'multipart/form-data' }}
        );

        showSuccessDialog('Note updated successfully.').then(async () => {
            const dtoData = {
                dto_number: dtoNumber,
                typical_no: dtoTypical,
                is_accessory: true
            };

            $('#extensionAccMaterialAddNoteModal').modal('hide');
            await getOverviewOfExtensionWorks(dtoData);
            await getOrderSummaryV2();

            hideElement('#projectWorkContainer')
            showLoader('#projectWork');
            await getProjectData();
        });

    } catch(error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`${errorMessage}`);
    } finally {
        $(this).removeClass('loading disabled');
    }
});

$(document).on('click', '.edit-accessory-note-button', async function (event) {
    const extensionWorkId = $(this).data('id');
    const extensionNote = $(this).data('note');
    const dtoNumber = $(this).data('dto');
    const dtoTypical = $(this).data('dtotypical');

    if (!extensionNote) {
        showErrorDialog('Note could not found');
        event.stopPropagation();
        return false;
    }

    $('#extensionAccMaterialEditNoteModalContent').html('');
    $('#extensionAccMaterialEditNoteModalFooter').html('');

    const modalContent = `<div class="ui form">
                                     <div class="field">
                                         <label for="input-extension-accessory-edit-material-note">Note</label>
                                         <textarea id="input-extension-accessory-edit-material-note" rows="2"></textarea>
                                     </div>
                                 </div>`;

    const modalFooter = `<div id="deleteExtensionAccessoryNoteButton" class="ui red button">Delete Note</div>
                                <div id="updateExtensionAccessoryNoteButton" class="ui green approve button" style="margin:0;">Update Note</div>`;

    $('#extensionAccMaterialEditNoteModalContent').html(modalContent);
    $('#extensionAccMaterialEditNoteModalFooter').html(modalFooter);

    $('#updateExtensionAccessoryNoteButton').attr('data-extensionid', extensionWorkId);
    $('#updateExtensionAccessoryNoteButton').attr('data-dtonumber', dtoNumber);
    $('#updateExtensionAccessoryNoteButton').attr('data-dtotypical', dtoTypical);

    $('#deleteExtensionAccessoryNoteButton').attr('data-extensionid', extensionWorkId);
    $('#deleteExtensionAccessoryNoteButton').attr('data-dtonumber', dtoNumber);
    $('#deleteExtensionAccessoryNoteButton').attr('data-dtotypical', dtoTypical);

    $('#input-extension-accessory-edit-material-note').val(extensionNote);

    $('#extensionAccMaterialEditNoteModal').modal({ allowMultiple: true, closable: false}).modal('show');
    $('#extensionAccMaterialEditNoteModal').draggable({ handle: '.header', containment: 'window' });
});

$(document).on('click', '#updateExtensionAccessoryNoteButton', async function (event) {
    event.stopPropagation();
    $('#updateExtensionAccessoryNoteButton').addClass('loading disabled');

    const dtoNumber = $(this).data('dtonumber');
    const dtoTypical = $(this).data('dtotypical');
    const extensionWorkId = $(this).data('extensionid');
    const extensionNote = $('#input-extension-accessory-edit-material-note').val();

    if (!extensionNote) {
        showErrorDialog('Please enter a note.');
        return false;
    }

    try {
        await axios.post('/dpm/dtoconfigurator/api/controllers/ExtensionDtoController.php?',
            {
                action: 'handleNoteToExtensionAccessoryMaterial',
                extensionWorkId: extensionWorkId,
                extensionNote: extensionNote
            },
            { headers: { 'Content-Type': 'multipart/form-data' }}
        );

        showSuccessDialog('Note updated successfully.').then(async () => {
            const dtoData = {
                dto_number: dtoNumber,
                typical_no: dtoTypical,
                is_accessory: true
            };

            $('#extensionAccMaterialEditNoteModal').modal('destroy').modal('hide');
            await getOverviewOfExtensionWorks(dtoData);
            await getOrderSummaryV2();

            hideElement('#projectWorkContainer')
            showLoader('#projectWork');
            await getProjectData();
        });

    } catch(error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`${errorMessage}`);
    } finally {
        $('#updateExtensionAccessoryNoteButton').removeClass('loading disabled');
    }
});

$(document).on('click', '#deleteExtensionAccessoryNoteButton', async function (event) {
    event.stopPropagation();
    $('#deleteExtensionAccessoryNoteButton').addClass('loading disabled');

    const dtoNumber = $(this).data('dtonumber');
    const dtoTypical = $(this).data('dtotypical');
    const extensionWorkId = $(this).data('extensionid');

    showConfirmationDialog({
        title: 'Are you sure?',
        htmlContent: 'Do you want to remove material note?',
        confirmButtonText: 'Yes!',
        confirmButtonColor: "green",
        onConfirm: async function () {
            try {
                await axios.post('/dpm/dtoconfigurator/api/controllers/ExtensionDtoController.php?',
                    {
                        action: 'deleteNoteFromExtensionAccessoryMaterial',
                        extensionWorkId: extensionWorkId
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                showSuccessDialog('Note removed successfully.').then(async () => {
                    const dtoData = {
                        dto_number: dtoNumber,
                        typical_no: dtoTypical,
                        is_accessory: true
                    };

                    $('#extensionAccMaterialEditNoteModal').modal('destroy').modal('hide');
                    await getOverviewOfExtensionWorks(dtoData);
                    await getOrderSummaryV2();

                    hideElement('#projectWorkContainer')
                    showLoader('#projectWork');
                    await getProjectData();
                });

            } catch(error) {
                const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                showErrorDialog(`${errorMessage}`);
            } finally {
                $('#deleteExtensionAccessoryNoteButton').removeClass('loading disabled');
            }
        },
    });
});

async function openAddExtensionTypicalModal() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/ExtensionDtoController.php', {
            params: {
                action: 'getExtraTypicalsOfExtensionProject',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                currentExtensionTypicals: ExtensionDtosOfProject.map(item => item.typical_no)
            }
        });
        const typicals = response.data;

        $('#typicalDropdown').dropdown('clear');
        $('#typicalWarningMessage').hide();
        $('#typicalErrorMessage').hide();

        $('#typicalDropdownMenu').empty();
        typicals.forEach(typical => {
            $('#typicalDropdownMenu').append(`
                <div class="item" data-value="${typical}">
                    ${typical}
                </div>
            `);
        });

        $('#typicalDropdown').dropdown('refresh');

        $('#addExtensionTypicalModal').modal('show');

    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`${errorMessage}`);
    }
}

$('#addExtensionTypicalButton').on('click', async function() {
    const selectedTypical = $('#typicalDropdown').dropdown('get value');

    if (!selectedTypical) {
        $('#typicalWarningMessage').show();
        return;
    }

    try {
        await axios.post('/dpm/dtoconfigurator/api/controllers/ExtensionDtoController.php?',
            {
                action: 'addExtraTypicalToExtensionProject',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                selectedTypical: selectedTypical,
                extensionDtoNumbers: [...new Set(ExtensionDtosOfProject.map(item => item.dto_number))]
            },
            { headers: { 'Content-Type': 'multipart/form-data' }}
        );

        $('#addExtensionTypicalModal').modal('hide');
        showSuccessDialog(`${selectedTypical} added successfully.`).then(() => {

        });
    } catch (error) {
        showErrorDialog('Failed to delete entry. Try again.');
    }

});
