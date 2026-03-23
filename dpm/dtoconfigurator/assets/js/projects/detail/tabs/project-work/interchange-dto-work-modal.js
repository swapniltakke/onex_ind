let InterchangeDtosOfProject = [];
let interchangeDtoNumber = '';
let interchangeDtoDescription = '';

async function getInterchangeDtosOfNachbau() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/InterchangeDtoController.php', {
            params: { action: 'getInterchangeDtosOfNachbau', projectNo: getUrlParam('project-no'), nachbauNo: getUrlParam('nachbau-no'), accTypical: NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryTypicalCode'] }
        });
        InterchangeDtosOfProject = response.data;
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    }
}

$('#chooseInterchangeDtoWorkModalButton').on('click', async function () {
    $(this).addClass('loading disabled');

    try {
        if (!Array.isArray(InterchangeDtosOfProject) || InterchangeDtosOfProject.length === 0) {
            showErrorDialog("No interchange DTOs found!");
            return;
        }

        // Group DTOs by dto_number
        const dtoGroups = {};
        const dtoDescriptions = {};

        InterchangeDtosOfProject.forEach(dto => {
            if (!dtoGroups[dto.dto_number]) {
                dtoGroups[dto.dto_number] = [];
                dtoDescriptions[dto.dto_number] = dto.description;
            }
            dtoGroups[dto.dto_number].push(dto.typical_no);
        });


        let modalContent = "";
        Object.entries(dtoGroups).forEach(([dtoNumber, typicals], index) => {
            modalContent += `
                <div class="ui segment" id="chooseInterchangeDtoWorkModal" style="cursor:pointer;">
                    <div class="ui form">
                        <div class="field">
                            <div class="dto-container">
                                <div class="ui radio checkbox">
                                    <input type="radio" name="interchangeDto" value="${dtoNumber}" ${index === 0 ? "checked" : ""}>
                                    <label></label>
                                </div>
                                <h3 class="dto-title">${dtoNumber}</h3>
                                <p class="dto-description">${formatDescription(dtoDescriptions[dtoNumber], 4) || 'No description'}</p>
                            </div>
                        </div>
                        <p class="typical-list">${typicals.join(" | ")}</p>
                    </div>
                </div>
        <div class="ui divider"></div>`;
        });

        // Append content to the modal
        $('#interchangeDtoRadioContainer').html(modalContent);

        // Reinitialize Semantic UI radio buttons
        $('.ui.radio.checkbox').checkbox();

        // Show the modal
        $('#chooseInterchangeDtoWorkModal').modal('show');

    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`<b>${errorMessage}</b>`);
    } finally {
        $(this).removeClass('loading disabled');
    }
});

$('#openInterchangeDtoWorkModalButton').on('click', async function (e) {
    e.stopPropagation();
    $(this).addClass('loading disabled');

    const checkedRadio = $('input[name="interchangeDto"]:checked');
    const dtoNumber = checkedRadio.val();
    const dtoDescription = checkedRadio.closest('.dto-container').find('.dto-description').text();

    interchangeDtoNumber = dtoNumber;
    interchangeDtoDescription = dtoDescription;

    await showInterchangeDtoWorkModalButton(dtoNumber, dtoDescription);
    $(this).removeClass('loading disabled');
});

async function showInterchangeDtoWorkModalButton(dtoNumber, dtoDescription) {
    const typicalsForDTO = InterchangeDtosOfProject
        .filter(item => item.dto_number === dtoNumber)
        .map(item => item.typical_no);

    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/InterchangeDtoController.php', {
            params: {
                action: 'getMaterialListsOfInterchangeDto',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                dtoNumber: dtoNumber,
                typicalsForDTO: typicalsForDTO
            }
        });

        const materialListsOfInterchangeDto = response.data;
        await renderInterchangeDtoTypicalDatatables(dtoNumber, dtoDescription, typicalsForDTO, materialListsOfInterchangeDto);

    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`<b>{errorMessage}</b>`);
    }
}

async function renderInterchangeDtoTypicalDatatables(dtoNumber, dtoDescription, typicalsForDTO, materialListsOfInterchangeDto) {

    // Ensure the modal content is empty before adding new tables
    $('#interchangeDtoWorkModalHeader').html(`<h3>${dtoNumber} | ${formatDescription(dtoDescription, 4)}</h3>`);
    $('#interchangeDtoMaterialTablesContainer').html('');

    let tableContent = '<div class="ui grid">';
    let allMaterialLists = {}; // ✅ Store material kmat data for comparison

    typicalsForDTO.forEach((typical, index) => {
        // Get all materials for the current typical
        const allMaterialsForTypical = materialListsOfInterchangeDto.filter(mat => mat.typical_no === typical);
        // Find the first ortz_kz used in that typical
        const firstOrtzKz = allMaterialsForTypical.length > 0 ? allMaterialsForTypical[0].ortz_kz : null;
        // Filter only materials that belong to the first ortz_kz
        const materials = allMaterialsForTypical.filter(mat => mat.ortz_kz === firstOrtzKz);

        const tableId = `datatable-${typical.replace(/[^a-zA-Z0-9]/g, '')}`;

        const ortzKzList = [...new Set(allMaterialsForTypical.map(mat => mat.ortz_kz))];
        const ortzKzText = ortzKzList.length > 0 ? ` (${ortzKzList.join(', ')})` : '';

        allMaterialLists[tableId] = materials.map(mat => mat.kmat);

        tableContent += `
            <div class="eight wide column">
                <div class="ui segment table-container">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h2 class="ui header" style="margin-bottom: 0.2rem;">${typical} <span style="font-size:18px;">${ortzKzText}</span></h2>
                        
                        <div style="display: flex; gap: 0.5rem;">
                            <div class="ui circular blue tiny icon button show-all-rows-btn" onclick="toggleShowAllRows('${tableId}', this)">
                                <i class="expand icon"></i> Show All Rows
                            </div>
                            <div class="ui teal tiny icon button toggle-datatable-btn" onclick="toggleDatatableBtn('${tableId}', this)">
                                <i class="chevron up icon"></i> 
                            </div>
                        </div>
                    </div>

                    <div class="table-wrapper" id="${tableId}-container"  style="margin-top:1rem;">                        
                         <button class="ui circular green tiny icon button" style="margin-bottom:1rem;"
                                    onclick="openAddInterchangeDtoMaterialModal('${typical}','${dtoNumber}')">
                                <i class="plus square icon" style="margin-right:5px!important;"></i>Add Material List
                         </button>
                        <table id="${tableId}" class="ui celled table compact nowrap" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Pos</th>
                                    <th style="text-align: center;">Added Nr.</th>
                                    <th style="text-align: center;">Deleted Nr.</th>
                                    <th>Description</th>
                                    <th>Qty</th>
                                    <th>Unit</th>
                                    <th>Panel</th>
                                    <th>Typical</th>
                                    <th>Parent Kmat</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${materials.map((mat, rowIndex) => {
                                        let rowStyle = '';
                                        let addedNumberContent = '';
                                        let deletedNumberContent = '';
                                        let positionContent = '';
                                        let dropdownMarkup = '';
                            
                                        if (mat.operation === 'add') {
                                            rowStyle = 'style="background-color: darkgreen; color: white;"';
                                            addedNumberContent = `<b>${mat.added_number}</b>` || '';
                                            deletedNumberContent = `<b>${mat.deleted_number}</b>` || '';
                                            positionContent = `<span class="ui label basic remove-interchange-dto-change" style="background: red;color: white;cursor: pointer;padding-right: 1px;padding-left: 4px;margin-left:5px;border-radius:50%;" 
                                                                   onclick="removeInterchangeDtoChange('${tableId}', '${rowIndex}', '${mat.interchangeDtoChangeId}')">
                                                                 <i class="trash outline icon" style="margin-left:5px;"></i>
                                                               </span>`;
                                        } else if (mat.operation === 'replace') {
                                            rowStyle = 'style="background-color: lightblue;"';
                                            addedNumberContent = `<b>${mat.added_number}</b>` || '';
                                            deletedNumberContent = `<b>${mat.deleted_number}</b>` || '';
                                            positionContent = `<span class="ui label basic remove-interchange-dto-change" style="background: red;color: white;cursor: pointer;padding-right: 1px;padding-left: 4px;margin-left:5px;border-radius:50%;" 
                                                                   onclick="removeInterchangeDtoChange('${tableId}', '${rowIndex}', '${mat.interchangeDtoChangeId}')">
                                                                 <i class="trash outline icon" style="margin-left:5px;"></i>
                                                               </span>`;
                                        } else if (mat.operation === 'delete') {
                                            rowStyle = 'style="background-color: tomato; color: white;"';
                                            addedNumberContent = '<b>SİL</b>';
                                            deletedNumberContent = `<b>${mat.deleted_number}</b>`;
                                            positionContent = `<span class="ui label basic remove-interchange-dto-change" style="background: red;color: white;cursor: pointer;padding-right: 1px;padding-left: 4px;margin-left:5px;border-radius:50%;" 
                                                                   onclick="removeInterchangeDtoChange('${tableId}', '${rowIndex}', '${mat.interchangeDtoChangeId}')">
                                                                 <i class="trash outline icon" style="margin-left:5px;"></i>
                                                               </span>`;
                                        }
                            
                                        if (!mat.kmat?.startsWith('003003') && !mat.kmat?.startsWith('003013')) {
                                            dropdownMarkup = `<div class="ui search selection dropdown added-kmat-material-select"
                                                                     data-row-index="${rowIndex}" data-table-id="${tableId}">
                                                                    <input type="hidden" name="added_kmat" value="${addedNumberContent}">
                                                                    <i class="dropdown icon"></i>
                                                                    <div class="default text" style="width:85%!important;line-height: 1.3rem;">Select Material</div>
                                                                    <div class="menu" style="width:300%!important;"></div>
                                                                </div>`;
                                        }
                            
                                        return `<tr ${rowStyle} data-change-id="${mat.interchangeDtoChangeId || ''}">
                                                    <td>${mat.Id}</td>
                                                    <td>${positionContent ? mat.position + ' ' + positionContent : mat.position}</td>
                                                    <td style="text-align: center;">${addedNumberContent || dropdownMarkup}</td>
                                                    <td style="text-align: center;">${deletedNumberContent || mat.kmat}</td>
                                                    <td>${mat.kmat_name || ''}</td>
                                                    <td>${mat.qty ? mat.qty.replace(/\.000$/, '') : ''}</td>
                                                    <td>${mat.unit || ''}</td>
                                                    <td>${mat.ortz_kz || ''}/${mat.panel_no || ''}</td>
                                                    <td>${mat.typical_no || ''}</td>
                                                    <td>${mat.parent_kmat || ''}</td>
                                                </tr>`;
                                        }).join('')}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>`;

        // Close the row after every 2 tables
        if ((index + 1) % 2 === 0) {
            tableContent += `</div><div class="ui grid">`;
        }
    });

    tableContent += '</div>'; // Close the last row

    // Insert tables into the modal content
    $('#interchangeDtoMaterialTablesContainer').html(tableContent);

    // ✅ Initialize DataTables, preventing reinitialization
    typicalsForDTO.forEach(typical => {
        const tableId = `datatable-${typical.replace(/[^a-zA-Z0-9]/g, '')}`;
        const tableElement = $(`#${tableId}`);

        if ($.fn.DataTable.isDataTable(tableElement)) {
            tableElement.DataTable().destroy();
        }

        tableElement.DataTable({
            order: [[0, 'asc']],
            responsive: true,
            paging: false,
            searching: false,
            ordering: false,
            scrollY: '320px',
            scrollCollapse: true,
            autoWidth:false,
            columnDefs: [
                { targets: [0,9], visible: false, searchable: false },
                { width: '2%', targets: [1,5,6] },
                { width: '15%', targets: [2] },
                { width: '10%', targets: [4] },
            ],
            createdRow: function (row, data) {
                const kmatValue = data[3]; // ✅ kmat is in the 3rd column (index starts at 0)

                if (kmatValue && typeof kmatValue === "string" && (kmatValue.startsWith('003003') || kmatValue.startsWith('003013'))) {
                    $(row).css('font-weight', 'bold');
                    $(row).css('text-decoration', 'underline');
                    $(row).css('color', 'mediumblue');
                }

            }
        });

        requestAnimationFrame(() => {
            $(`#${tableId}`).DataTable().draw();
        });

        $(`#${tableId}_wrapper .row:first`).css('display', 'none');
    });

    highlightCommonRows(allMaterialLists);
    await initializeInterchangeMaterialNumberSelect('.added-kmat-material-select');

    $('#interchangeDtoWorkModal').modal({ closable: false}).modal('show');

}


async function toggleShowAllRows(tableId, _this) {
    const container = $(`#${tableId}_wrapper .dt-scroll-body`);

    if (container.css("max-height") === "320px") {
        // Expand table (disable scrolling)
        container.css({
            "max-height": "none",
            "overflow-y": "visible"
        });
        $(_this).html('<i class="compress icon"></i> Collapse');
    } else {
        // Restore scrolling
        container.css({
            "max-height": "320px",
            "overflow-y": "auto"
        });
        $(_this).html('<i class="expand icon"></i> Show All Rows');
    }
}

async function toggleDatatableBtn(tableId, _this) {
    const tableContainer = $(`#${tableId}-container`);

    if (tableContainer.is(':visible')) {
        tableContainer.slideUp(200);
        $(_this).html(`<i class="chevron down icon"></i>`)
    } else {
        tableContainer.slideDown(200);
        $(_this).html(`<i class="chevron up icon"></i>`)
    }
}

function highlightCommonRows(allMaterialLists) {
    let commonKmats = new Map();

    // ✅ Count occurrences of each kmat
    Object.values(allMaterialLists).forEach(kmatList => {
        let uniqueKmats = new Set(kmatList);

        uniqueKmats.forEach(kmat => {
            commonKmats.set(kmat, (commonKmats.get(kmat) || 0) + 1);
        });
    });

    // ✅ Get the number of tables
    const totalTables = Object.keys(allMaterialLists).length;

    // ✅ Apply green background to rows that appear in all tables
    Object.entries(allMaterialLists).forEach(([tableId, kmatList]) => {
        const table = $(`#${tableId}`).DataTable();
        table.rows().every(function () {
            const rowData = this.data();
            const kmatValue = rowData[3];

            if (commonKmats.get(kmatValue) === totalTables) {
                $(this.node()).css('background-color', 'lightgreen');
            }
        });
    });
}

async function initializeInterchangeMaterialNumberSelect(selector) {
    let materialMap = {};

    $(selector).dropdown({
        apiSettings: {
            url: `/dpm/dtoconfigurator/api/controllers/MaterialController.php?action=getMaterialsBySearch&keyword={query}`,
            cache: false,
            onResponse: function(response) {
                const menuElement = document.querySelector(`${selector} .menu`);
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

$('#saveInterchangeDtoChangesBtn').on('click', async function (e) {
    e.stopPropagation();
    $(this).addClass('loading disabled');

    let changes = [];

    // Loop through each dropdown in the "Added Nr." column
    $('.added-kmat-material-select').each(function () {
        const selectedMaterial = $(this).dropdown('get value'); // ✅ Get selected material number

        if (!selectedMaterial) return; // ✅ Skip if no selection (empty value)

        const rowIndex = $(this).data('row-index'); // ✅ Get row index
        const tableId = $(this).data('table-id'); // ✅ Get table name (DataTable ID)
        const table = $(`#${tableId}`).DataTable();

        const rowData = table.row(rowIndex).data(); // ✅ Get row data for that row
        if (!rowData) return; // ✅ Skip if row data is missing

        changes.push({
            table: tableId,
            rowIndex: rowIndex,
            deletedNumber: rowData[3],
            addedNumber: selectedMaterial === "CLEAR" ? "CLEAR" : selectedMaterial,
            operation: selectedMaterial === "CLEAR" ? 'delete' : 'replace',
            rowData: rowData
        });
    });

    if (changes.length === 0) {
        showErrorDialog("No changes detected. Please select at least one material to save!");
        $(this).removeClass('loading disabled');
        return;
    }

    try {
        const response = await axios.post('/dpm/dtoconfigurator/api/controllers/InterchangeDtoController.php?',
            {
                action: 'saveInterchangeDtoChanges',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                dtoNumber: interchangeDtoNumber,
                dtoDescription: interchangeDtoDescription,
                changes: changes,
                isRevisionNachbau: currentlyWorkingUser.isRevisionNachbau
            },
            { headers: { 'Content-Type': 'multipart/form-data' }}
        );

        const savedChanges = response.data.savedChanges;

        showSuccessDialog('Changes are saved successfully').then(async () => {
            for (const change of changes) {
                const index = changes.indexOf(change);
                const table = $(`#${change.table}`).DataTable();
                const rowNode = table.row(change.rowIndex).node();

                if (rowNode) {
                    // ✅ Store interchangeDtoChangeId in row
                    $(rowNode).attr('data-change-id', savedChanges[index].interchangeDtoChangeId);

                    // ✅ Change background color
                    if (change.operation === 'replace') {
                        $(rowNode).css('background-color', 'lightblue');
                    }
                    if (change.operation === 'delete') {
                        $(rowNode).css('background-color', 'tomato');
                    }

                    // ✅ Disable dropdown
                    $(rowNode).find('.added-kmat-material-select').dropdown('destroy').addClass('disabled');

                    // ✅ Add trash icon button next to the select box
                    const trashButton = `<span class="ui label basic remove-interchange-dto-change" style="background: red;color: white;cursor: pointer;display: flex;justify-content: center;" 
                                                       onclick="removeInterchangeDtoChange('${change.table}', '${change.rowIndex}', '${savedChanges[index].interchangeDtoChangeId}')">
                                                    Remove <i class="trash outline icon" style="margin-left:5px;"></i>
                                                </span>`;

                    // Append trash icon next to the select box
                    $(rowNode).find('.added-kmat-material-select').parent().append(trashButton);
                }

                await getOrderSummaryV2();
            }
        });
        } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`<b>${errorMessage}</b>`);
    } finally {
        $(this).removeClass('loading disabled');
    }
});

async function removeInterchangeDtoChange(tableId, rowIndex, changeId) {
    const table = $(`#${tableId}`).DataTable();
    const rowNode = table.row(rowIndex).node();

    if (!rowNode) return;

    showConfirmationDialog({
        title: 'Removing Change?',
        htmlContent: 'Are you sure to remove material list?',
        confirmButtonText: 'Yes, delete!',
        confirmButtonColor: "#d33",
        onConfirm: async function () {
            try {
                await axios.post('/dpm/dtoconfigurator/api/controllers/InterchangeDtoController.php?',
                    {
                        action: 'removeInterchangeDtoChange',
                        interchangeDtoChangeId: changeId
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                showSuccessDialog('Change removed successfully.').then(async () => {
                    $(rowNode).css('background-color', '');

                    const dropdown = $(rowNode).find('.added-kmat-material-select');
                    dropdown.dropdown('clear');
                    dropdown.removeClass('disabled');
                    $(rowNode).find('.remove-interchange-dto-change').remove();

                    await showInterchangeDtoWorkModalButton(interchangeDtoNumber, interchangeDtoDescription);

                    await getOrderSummaryV2();
                });
            } catch (error) {
                const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                showErrorDialog(`<b>${errorMessage}</b>`);
            }
        },
    });
}

async function openAddInterchangeDtoMaterialModal(typical, dtoNumber) {
    resetAddInterchangeDtoMaterialModal();

    $('#addInterchangeDtoMaterialModal #addInterchangeMaterialDtoNumber').val(dtoNumber);
    $('#addInterchangeDtoMaterialModal #addInterchangeMaterialTypical').val(typical);

    await initializeInterchangeMaterialNumberSelect('#addInterchangeMaterialNumberSelect');

    $('#addInterchangeDtoMaterialModal').modal({ allowMultiple: true, closable: false}).modal('show');
}


$('#addInterchangeDtoMaterialBtn').on('click', async function (e) {
    e.stopPropagation();
    $(this).addClass('loading disabled');

    const dtoNumber = $('#addInterchangeMaterialDtoNumber').val();
    const typicalNo = $('#addInterchangeMaterialTypical').val();
    const materialNo =  $('#addInterchangeMaterialNumberSelect').dropdown('get value');
    const quantity = $('#addInterchangeMaterialQty').val();
    const unit = $('#addInterchangeMaterialUnit').val();

    if (!materialNo || !quantity || parseInt(quantity) <=0 ||!unit) {
        fireToastr('warning', 'Please fill out all required fields correctly.')
        $('#btnMaterialDefine').removeClass('loading disabled');
        return;
    }

    showConfirmationDialog({
        title: 'Save Material?',
        htmlContent: 'Are you sure to add material list to ' + typicalNo + ' ?',
        confirmButtonText: 'Yes!',
        confirmButtonColor: "green",
        onConfirm: async function () {
            try {
                await axios.post('/dpm/dtoconfigurator/api/controllers/InterchangeDtoController.php?',
                    {
                        action: 'addInterchangeDtoMaterialToTypical',
                        projectNo: getUrlParam('project-no'),
                        nachbauNo: getUrlParam('nachbau-no'),
                        dtoNumber: dtoNumber,
                        dtoDescription: interchangeDtoDescription,
                        typicalNo: typicalNo,
                        materialNo: materialNo,
                        quantity: quantity,
                        unit: unit,
                        isRevisionNachbau: currentlyWorkingUser.isRevisionNachbau
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                showSuccessDialog('Material added to ' + typicalNo + ' successfully.').then(async () => {

                    await showInterchangeDtoWorkModalButton(interchangeDtoNumber, interchangeDtoDescription);

                    $('#addInterchangeDtoMaterialBtn').removeClass('loading disabled');
                    $('#addInterchangeDtoMaterialModal').modal('hide');

                    await getOrderSummaryV2();
                });
            } catch (error) {
                const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                showErrorDialog(`<b>${errorMessage}</b>`);
            } finally {
                $('#addInterchangeDtoMaterialBtn').removeClass('loading disabled');
            }
        },
    });
});

function resetAddInterchangeDtoMaterialModal(){
    $('#addInterchangeMaterialNumberSelect').dropdown('clear');
    $('#addInterchangeMaterialQty').val(1);
    $('#addInterchangeMaterialUnit').val('ST');
}

$(document).on('click', '.ui.segment', function (e) {
    if (!$(e.target).is('input[type="radio"]') && !$(e.target).is('label')) {
        const radio = $(this).find('input[type="radio"]');
        radio.prop('checked', true);
    }
});
