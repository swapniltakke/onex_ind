async function renderNotDefinedWorkCenterDataTable(notDefinedWorkCenterData) {
    await fetchWorkCenters();
    const tableId = '#notDefinedWorkCenterDataTable';

    if ($.fn.DataTable.isDataTable(tableId))
        $(tableId).DataTable().destroy();

    if ($('#notDefinedWorkCenterContainer').hasClass('hidden'))
        $('#notDefinedWorkCenterContainer').removeClass('hidden');

    if (notDefinedWorkCenterData.length === 0)
        $('#notDefinedWorkCenterContainer').hide()
    else {
        const table = $(tableId).DataTable({
            data: notDefinedWorkCenterData,
            pageLength: 10,
            paging:true,
            destroy: true,
            autoWidth:false,
            columnDefs: [
                { width: '15%', targets: [0] },
                { width: '10%', targets: [1] },
                { width: '11%', targets: [2,3] },
                { width: '20%', targets: [4] },
                { width: '3%', targets: [5,6] },
                { width: '35%', targets: [7] },
                { width: '5%', targets: [8] },
            ],
            columns: [
                {
                    render: (data, type, row) => {
                        let defaultText = "";
                        let defaultValue = "";

                        if (row.type === 'Typical') {
                            defaultText = "Typical Based List";
                            defaultValue = "Typical";
                        } else if (row.type === 'Panel') {
                            defaultText = "Panel Based List";
                            defaultValue = "Panel";
                        } else if (row.type === 'Accessories') {
                            defaultText = "Accessory List";
                            defaultValue = "Accessories";
                        } else {
                            defaultText = "";
                            defaultValue = "";
                        }

                        return `<span id="notDefinedWcRow${row.id}" data-id="${row.id}" data-value="${defaultValue}">
                                    ${defaultText ? `<a class="ui basic black large label">${defaultText}</a>`
                                                  : `<a class="ui basic red large label" data-tooltip="This material list has empty type data. At first, you should update the work center." data-position="top left">No Type</a>` }
                                </span>`;

                    },
                    className: 'center aligned'
                },
                {
                    data: 'dto_number',
                    className: 'center aligned',
                    render: (data) => `<a href="#" data-tooltip="Open TK Form Material List" data-position="top center"   
                                          class="dto-link" data-dto-number="${data}"> ${data} </a>`
                },
                {
                    data: 'material_added_number',
                    render: (data, type, row) => {
                        const addedMaterial = `${row.material_added_starts_by}${row.material_added_number}`;
                        let linkStyle = '';
                        let dataTooltip = 'Navigate to Material Viewer';

                        if (row.material_added_sap_defined === '0') {
                            dataTooltip = 'Material not found in SAP system'
                            linkStyle = 'color:red;font-weight:bold;'; // SAP not defined
                            $('#materialNotDefinedInSapMsg').removeClass('hidden');
                        } else if (row.affected_dto_numbers !== '' && row.affected_dto_numbers !== null) {
                            dataTooltip = 'This material list has multiple DTO numbers'
                            linkStyle = 'color:green;font-weight:bold;'; // Affects other DTOs
                            $('#listAffectsOtherDtosMsg').removeClass('hidden');
                        }

                        return `<a target="_blank" href="/materialviewer/?material=${addedMaterial}" 
                                   data-tooltip="${dataTooltip}" data-position="top center" data-variation="inverted" 
                                   style="${linkStyle}">
                                   ${addedMaterial}
                                </a>`;
                    },
                    className: 'center aligned dblclick-cell'
                },
                {
                    data: 'material_deleted_number',
                    render: (data, type, row) => {
                        const deletedMaterial = `${row.material_deleted_starts_by}${row.material_deleted_number}`;
                        let linkStyle = '';
                        let dataTooltip = 'Navigate to Material Viewer';

                        if (row.material_deleted_sap_defined === '0') {
                            dataTooltip = 'Material not found in SAP system'
                            linkStyle = 'color:red;font-weight:bold;'; // Not defined in SAP
                            $('#materialNotDefinedInSapMsg').removeClass('hidden');
                        } else if (row.affected_dto_numbers !== '' && row.affected_dto_numbers !== null) {
                            dataTooltip = 'This material list affects multiple DTO numbers'
                            linkStyle = 'color:green;font-weight:bold;'; // Affects other DTOs
                            $('#listAffectsOtherDtosMsg').removeClass('hidden');
                        }

                        return `<a target="_blank" href="/materialviewer/?material=${deletedMaterial}" 
                                   data-tooltip="${dataTooltip}" data-position="top center" data-variation="inverted" 
                                   style="${linkStyle}">
                                   ${deletedMaterial}
                                </a>`;
                    },
                    className: 'center aligned dblclick-cell'
                },
                {
                    data: 'material_deleted_description',
                    render: (data, type, row) => row.operation === 'add' ? row.material_added_description : row.material_deleted_description,
                    className: 'center aligned'
                },
                {
                    data: 'quantity',
                    render: (data) => parseFloat(data).toString().replace(/\.000$/, ''),
                    className: 'center aligned'
                },
                {
                    data: 'unit',
                    className: 'center aligned'
                },
                {
                    data: 'work_center',
                    render: (data, type, row) => {
                        return createWorkCenterDropdown(row.id);
                    },
                    className: 'center aligned'
                },
                {
                    data: 'acc',
                    render: (data, type, row) => {
                        if (!row.affected_dto_numbers && !row.acc)
                            return '';
                        else if (!row.affected_dto_numbers && row.acc) {
                            return `<div class="ui icon teal button mini" 
                                         data-tooltip="${row.acc}" data-position="top right" data-inverted="">
                                       Note
                                    </div>`;
                        }
                        else if (row.affected_dto_numbers && !row.acc) {
                            const urlParams = new URLSearchParams(window.location.search);
                            const dtoNumber = urlParams.get('dto-number');

                            let dtoArray = row.affected_dto_numbers.split("|");
                            dtoArray = dtoArray.filter(item => item !== dtoNumber);
                            const otherDtoNumbers = dtoArray.join(", ");

                            return `<div class="ui icon blue button mini" 
                                         data-tooltip="${otherDtoNumbers}" data-position="top right" data-inverted="">
                                       DTO Group
                                    </div>`;
                        }
                        else {
                            const urlParams = new URLSearchParams(window.location.search);
                            const dtoNumber = urlParams.get('dto-number');

                            let dtoArray = row.affected_dto_numbers.split("|");
                            dtoArray = dtoArray.filter(item => item !== dtoNumber);
                            const otherDtoNumbers = dtoArray.join(", ");

                            return `<div class="ui icon blue button mini" 
                                         data-tooltip="${otherDtoNumbers}" data-position="top right" data-inverted="">
                                       DTO Group
                                    </div>
                                    <div class="ui icon teal button mini" 
                                         data-tooltip="${row.acc}" data-position="top right" data-inverted="">
                                       Note
                                    </div>`;
                        }
                    },
                    className: 'center aligned'
                },
            ]
        });

        $('#notDefinedWorkCenterContainer').transition('zoom', function() {
            requestAnimationFrame(() => {
                table.draw();

            });
        });

        //Search customization
        const $searchInput = $('.dt-search input[type="search"]');
        $searchInput.attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
    }
}


// Use event delegation on the parent container of the table
$(document).on('click', '.dto-link', async function (e) {
    e.preventDefault();

    const dtoNumber = $(this).data('dto-number'); // Get dto_number from the link

    await axios.get(`/dpm/dtoconfigurator/api/controllers/TkFormController.php?action=getTkFormByDtoNumber&dtoNumber=${dtoNumber}`)
        .then(response => {
            const { id, document_number } = response.data;
            const url = `/dpm/dtoconfigurator/core/tkform/detail/material-list.php?id=${id}&document-number=${document_number}&dto-number=${dtoNumber}`;
            window.open(url, '_blank');
        })
        .catch(error => {
            console.error("Error fetching DTO parameters:", error);
            alert('An error occurred while fetching the data.');
        });
});

let workCenterOptions = [];
async function fetchWorkCenters() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/WorkCenterController.php?action=getWorkCenters');
        workCenterOptions = response.data.map(wc => ({
            name: `${wc.work_center} - ${wc.work_content}`,
            value: wc.id,
            has_sub_kmat: wc.has_sub_kmat
        }));
    } catch (error) {
        console.error("Error fetching work centers:", error);
    }
}

function createWorkCenterDropdown(rowId) {
    const optionsHtml = workCenterOptions.map(option =>
        `<option value="${option.value}" data-sub="${option.has_sub_kmat}">${option.name}</option>`
    ).join('');

    // Return dropdown HTML
    const dropdownHtml = `
        <select name="workCenterSelect" 
                id="workCenterDropdown_${rowId}" 
                class="ui search selection dropdown work-center-dropdown"
                data-row-id="${rowId}">
            <option value="">Select Work Center</option>
            ${optionsHtml}
        </select>
        <div id="subWorkCenterContainer_${rowId}" style="margin-top: 10px;"></div>
    `;

    // Add a delayed execution to bind events
    setTimeout(() => {
        $(`#workCenterDropdown_${rowId}`).dropdown({
            onChange: async function (value) {
                const rowId = $(this).data('row-id');
                await handleWorkCenterChange(rowId, value);
            },
            allowAdditions: false,
            fullTextSearch: true,  // Search within option text
            forceSelection: false,  // Allow users to type and search freely
            selectOnKeydown: false,  // Avoid auto-selecting on keydown
            clearable: true  // Allow clearing the selection
        });
    }, 0);

    return dropdownHtml;
}

async function handleWorkCenterChange(rowId, selectedWorkCenter) {
    const selectedOption = workCenterOptions.find(wc => wc.value === selectedWorkCenter);

    if (selectedOption && selectedOption.has_sub_kmat === '1') {
        // Send request to fetch sub work centers
        try {
            const response = await axios.get(`/dpm/dtoconfigurator/api/controllers/WorkCenterController.php?action=getSubWorkCentersOfWorkCenter&workCenterId=${selectedWorkCenter}`);
            const subWorkCenters = response.data;

            if (subWorkCenters.length > 0) {
                const subOptionsHtml = subWorkCenters.map(sub =>
                    `<option value="${sub.sub_kmat_name}">${sub.sub_kmat} - ${sub.sub_kmat_name}</option>`
                ).join('');

                // Render sub work center dropdown
                $(`#subWorkCenterContainer_${rowId}`).html(`
                    <select name="subWorkCenterSelect" 
                            id="subWorkCenterDropdown_${rowId}" 
                            class="ui search selection dropdown sub-work-center-dropdown"
                            data-row-id="${rowId}">
                        <option value="">Select Sub Work Center</option>
                        ${subOptionsHtml}
                    </select>
                `);

                // Initialize Semantic UI dropdown
                $(`#subWorkCenterDropdown_${rowId}`).dropdown({
                    allowAdditions: false,
                    // fullTextSearch: true,  // Enables searching by visible text
                    // forceSelection: false,  // Allow users to type and search freely
                    selectOnKeydown: false,  // Prevent automatic selection
                    clearable: true  // Allow clearing of the dropdown selection
                });
            } else {
                // No sub work centers found
                $(`#subWorkCenterContainer_${rowId}`).html('<div class="ui red message">No Sub Work Centers Found</div>');
            }
        } catch (error) {
            console.error("Error fetching sub work centers:", error);
            $(`#subWorkCenterContainer_${rowId}`).html('<div class="ui red message">Error fetching sub work centers</div>');
        }
    } else {
        // Remove sub work center dropdown if not required
        $(`#subWorkCenterContainer_${rowId}`).html('');
    }
}

$('#updateAllWorkCenters').on('click', async function () {
    const projectWorkUpdates = [];

    // Iterate over all rows to collect selected work centers and sub kmats
    $('.work-center-dropdown').each(function () {
        const rowId = $(this).parent().find('select').data('row-id');
        const workCenterId = $(this).dropdown('get value');
        const subWorkCenterDropdown = $(`#subWorkCenterDropdown_${rowId}`);
        const subWorkCenter = subWorkCenterDropdown.length ? subWorkCenterDropdown.dropdown('get value') : '';

        if (workCenterId) {
            projectWorkUpdates.push({
                rowId: rowId,
                work_center_id: workCenterId,
                sub_kmat_name: subWorkCenter  // This will be empty if not required
            });
        }
    });

    // Validation: Check if required sub work centers are missing
    let valid = true;

    $('.sub-work-center-dropdown').each(function () {
        const subWorkCenterValue = $(this).dropdown('get value');
        if (!subWorkCenterValue) {
            valid = false;
            $(this).closest('.dropdown').addClass('error');
        } else {
            $(this).closest('.dropdown').removeClass('error');
        }
    });

    if (!valid) {
        fireToastr('warning', 'Please select sub work centers of required rows.')
        return;
    }

    try {
        hideElement('#projectWorkDataGrid')
        showLoader('#projectWork')

        const response = await axios.post('/dpm/dtoconfigurator/api/controllers/ProjectController.php', {
            action: 'updateNotDefinedWorkCenters',
            projectWorkUpdates: projectWorkUpdates,
            accessoryParentKmat: NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryParentKmat'],
            accessoryTypicalNumber: NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryTypicalCode']
        }, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });


        if (response.status === 200) {
            showSuccessDialog('Work Centers updated successfully.').then(() => {
                showLoader('#projectWork');
                hideElement('#projectNotWorkedMessage');
                hideElement('#projectWorkDataGrid');
                hideElement('#projectWorkContainer');

                getProjectData();
            });
        } else {
            showErrorDialog('Failed to update work centers.');
        }
    } catch (error) {
        console.error("Error updating work centers:", error);
        showErrorDialog('An error occurred while updating work centers');
    }
});


$('#notDefinedWorkCenterDataTable').on('dblclick', '.dblclick-cell', function () {
    const starters = ['A7E00', 'A7ETKBL', 'A7ET', 'A7E'];
    let copiedText = $(this).text().trim();

    for (const starter of starters) {
        if (copiedText.startsWith(starter)) {
            copiedText = copiedText.slice(starter.length);
            break;
        }
    }

    navigator.clipboard.writeText(copiedText).then(() => {
        fireToastr('success', `${copiedText} copied to clipboard.`);
    }).catch(err => {
        console.error('Failed to copy text: ', err);
        fireToastr('error', 'Failed to copy text. Please try again.');
    });
});
