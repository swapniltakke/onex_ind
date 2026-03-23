$(document).ready(async function () {
    const counts = await fetchCountOfNcAndTkNotesOfTkForm();
    document.getElementById('ncListCount').innerText = counts.nc_count;
    document.getElementById('tkNotesCount').innerText = counts.tk_notes_count;

    if (parseInt(counts.nc_count) > 0)
        document.getElementById('ncListCount').classList.add('brown');
    if (parseInt(counts.tk_notes_count) > 0)
        document.getElementById('tkNotesCount').classList.add('brown');

    await initializeMaterialListDataTable();
});

async function fetchMaterialListByTkFormId() {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const id = urlParams.get('id');

        const url = `/dpm/dtoconfigurator/api/controllers/TkFormMaterialController.php?action=getTKFormMaterialsByTkFormId&id=${id}`;
        const response = await axios.get(url, { id: id }, { headers: { "Content-Type": "multipart/form-data" } });

        return response.data;
    } catch (error) {
        fireToastr('error', 'Error fetching Material List:', error);
        return [];
    }
}

async function initializeMaterialListDataTable() {
    const data = await fetchMaterialListByTkFormId();

    if (data.length === 0) {
        $('#materialListPage .loader').hide();
        $('#materialListTable').hide();
        $('#materialListContainer').show();
        $('#materialListCheckMsg').transition('pulse')
        $('#materialListTableContainer').hide();
    }
    else {
        const table = $('#materialListTable').DataTable({
            data: data,
            pageLength: 15,
            lengthMenu: [15, 25, 50, 100],
            autoWidth: false,
            order: [[0, 'desc']],
            fixedHeader:true,
            columnDefs: [
                { width: '7%', targets: [0] },
                { width: '13%', targets: [1] },
                { width: '15%', targets: [2,3] },
                { width: '1%', targets: [4,5] },
                { width: '20%', targets: 6 },
                { width: '10%', targets: 8 },
                { width: '6%', targets: 9 },
                { targets: [0, 4, 5, 7, 8, 9, 10], orderable: false},
            ],
            columns: [
                {
                    data: 'project_number',
                    render: (data) => `<a href="/dpm/dtoconfigurator/core/projects/detail/info.php?project-no=${data}" target="_blank"
                                          data-tooltip="Navigate to Projects page" data-position="top center" data-variation="inverted">
                                          ${data}
                                       </a>`,
                    className: 'center aligned'
                },
                {
                    data: 'work_center',
                    render: (data, type, row) => {
                        if (row.secondary_work_center_id && row.secondary_work_center_id !== '0') {
                            return `<div data-tooltip="Default KMAT is ${row.work_content}" data-position="top center">
                                        <a class="ui teal label">${row.work_center}</a><br>
                                        <h5 style="margin-top:2%;">${row.work_content}</h5>
                                    </div><br>
                                    <div data-tooltip="Secondary KMAT" data-position="top center">
                                        <a class="ui blue label">${row.secondary_work_center}</a><br>
                                        <h5 style="margin-top:2%;">${row.secondary_work_content}</h5>
                                    </div>`;
                        } else if (row.work_center !== '') {
                            return `<a class="ui teal label">${row.work_center}</a><br>
                                    <h5 style="margin-top:2%;">${row.work_content}</h5>`;
                        } else {
                            return `<div class="ui red horizontal label">Undefined</div>`;
                        }
                    },
                    className: 'center aligned'
                },
                {
                    data: 'material_added_number',
                    render: (data, type, row) => {
                        const addedMaterial = `${row.material_added_starts_by}${row.material_added_number}`;
                        const addedMaterialLastRevision = `${row.material_added_last_revision_date}`;
                        const deletedMaterialLastRevision = `${row.material_deleted_last_revision_date}`;

                        let linkStyle = '';
                        let dataTooltip = 'Navigate to Material Viewer';
                        let cellStyle = '';

                        const isVthOrCth = row.material_added_starts_by?.startsWith(':: VTH:') ||
                            row.material_added_starts_by?.startsWith(':: CTH:') ||
                            row.material_deleted_starts_by?.startsWith(':: VTH:') ||
                            row.material_deleted_starts_by?.startsWith(':: CTH:');

                        let tooltipAttribute = '';
                        if (!isVthOrCth && addedMaterialLastRevision && deletedMaterialLastRevision) {
                            const addedDate = moment(addedMaterialLastRevision, 'DD.MM.YYYY');
                            const deletedDate = moment(deletedMaterialLastRevision, 'DD.MM.YYYY');

                            if (addedDate.isValid() && deletedDate.isValid() && deletedDate.isAfter(addedDate)) {
                                cellStyle = 'background-color: yellow; border: 2px solid blue;';
                                tooltipAttribute = 'data-tooltip="DTO material revision is older than standard!" data-position="top center" data-variation="inverted"';
                            }
                        }

                        if (row.material_added_sap_defined === '0') {
                            dataTooltip = 'Material not found in SAP system'
                            linkStyle = 'color:red;font-weight:bold;'; // SAP not defined
                            $('#materialNotDefinedInSapMsg').removeClass('hidden');
                        } else if (row.affected_dto_numbers !== '' && row.affected_dto_numbers !== null) {
                            dataTooltip = 'This material list has multiple DTO numbers'
                            linkStyle = 'color:green;font-weight:bold;'; // Affects other DTOs
                            $('#listAffectsOtherDtosMsg').removeClass('hidden');
                        }

                        return addedMaterial ? `<div style="padding: 5px; ${cellStyle}" ${tooltipAttribute}>
                                   <i class="plus circle icon" style="color:green;"></i>
                                   <a target="_blank" href="/materialviewer/?material=${addedMaterial}" 
                                      data-tooltip="${dataTooltip}" data-position="top center" data-variation="inverted" 
                                      style="${linkStyle}">
                                      ${addedMaterial}
                                   </a>
                                   <i class="copy icon black copy-material" data-material="${addedMaterial}" title="Copy to clipboard" style="cursor: pointer; margin-left: 5px;"></i><br>
                                   ${!isVthOrCth ? `<span style="margin-top: 2px;font-size: 12px;color: darkblue;font-style: italic;">Revision: ${addedMaterialLastRevision}</span>` : ''}
                                </div>` : '';
                    },
                    className: 'center aligned addedMaterial'
                },
                {
                    data: 'material_deleted_number',
                    render: (data, type, row) => {
                        const deletedMaterial = `${row.material_deleted_starts_by}${row.material_deleted_number}`;
                        const deletedMaterialLastRevision = `${row.material_deleted_last_revision_date}`;

                        let linkStyle = '';
                        let dataTooltip = 'Navigate to Material Viewer';

                        const isVthOrCth = row.material_deleted_starts_by?.startsWith(':: VTH:') ||
                            row.material_deleted_starts_by?.startsWith(':: CTH:');

                        if (row.material_deleted_sap_defined === '0') {
                            dataTooltip = 'Material not found in SAP system'
                            linkStyle = 'color:red;font-weight:bold;'; // Not defined in SAP
                            $('#materialNotDefinedInSapMsg').removeClass('hidden');
                        } else if (row.affected_dto_numbers !== '' && row.affected_dto_numbers !== null) {
                            dataTooltip = 'This material list affects multiple DTO numbers'
                            linkStyle = 'color:green;font-weight:bold;'; // Affects other DTOs
                            $('#listAffectsOtherDtosMsg').removeClass('hidden');
                        }

                        return deletedMaterial ? `<i class="minus circle icon" style="color:red;"></i>
                                   <a target="_blank" href="/materialviewer/?material=${deletedMaterial}" 
                                      data-tooltip="${dataTooltip}" data-position="top center" data-variation="inverted" 
                                      style="${linkStyle}">
                                      ${deletedMaterial}
                                   </a><i class="copy icon black copy-material" data-material="${deletedMaterial}" title="Copy to clipboard" style="cursor: pointer; margin-left: 5px;"></i><br>
                                   ${!isVthOrCth ? `<span style="margin-top: 2px;font-size: 12px;color: darkblue;font-style: italic;">Revision: ${deletedMaterialLastRevision}</span>` : ''}` : '';
                    },
                    className: 'center aligned deletedMaterial'
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
                    data: 'material_deleted_description',
                    render: (data, type, row) => row.operation === 'add' ? row.material_added_description : row.material_deleted_description,
                    className: 'center aligned'
                },
                {
                    data: 'type',
                    render: (data, type, row) => {
                        if (row.effective === '1') {
                            $('#listAffectsSidePanelsMsg').removeClass('hidden');
                            return `<span data-tooltip="This material list affects associated side panels" data-position="top center" data-variation="inverted" 
                                          style="color:orange;font-weight:bold;">
                                       ${row.type}
                                     </span>`
                        } else {
                            return `<span>${row.type}</span>`
                        }
                    },
                    className: 'center aligned'
                },
                {
                    data: 'created',
                    render: (data, type, row) =>
                        `<span>${row.created_by}</span><br>
                         <span>${moment(row.created).format('DD.MM.YYYY HH:mm')}</span>`,
                    className: 'center aligned'
                },
                {
                    data: 'acc',
                    render: (data, type, row) => {
                        if (!row.affected_dto_numbers && !row.acc)
                            return '';
                        else if (!row.affected_dto_numbers && row.acc) {
                            return `<div class="ui icon violet button mini" 
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

                            return `<div class="ui icon blue button mini" style="margin-bottom:5px;"
                                         data-tooltip="${otherDtoNumbers}" data-position="top right" data-inverted="">
                                       DTO Group
                                    </div>
                                    <div class="ui icon violet button mini" 
                                         data-tooltip="${row.acc}" data-position="top right" data-inverted="">
                                       Note
                                    </div>`;
                        }
                    },
                    className: 'center aligned'
                },
                {
                    render: (data, type, row) =>
                        `<button class="ui icon button tiny basic teal" onclick="openEditMaterialListModal('${row.id}')">
                            <i class="pencil alternate large icon"></i>
                        </button>`,
                    className: 'center aligned'
                }
            ],
            destroy: true
        });

        $('#materialListPage .loader').hide();
        $('#materialListContainer').transition('zoom', function() {
            requestAnimationFrame(() => {
                table.fixedHeader.adjust(); // İlk açılışta fix header olmama sorununun çözümü
            });
        });
        table.draw();
    }

    //Search customization
    const $searchInput = $('.dt-search input[type="search"]');
    $searchInput.attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
}

async function openEditMaterialListModal(tkMaterialId) {
    const url = `/dpm/dtoconfigurator/api/controllers/TkFormMaterialController.php?action=getTKFormMaterialsById&id=${tkMaterialId}`;
    try {
        const response = await axios.get(url, { id: tkMaterialId });

        if (response.status === 200) {
            const materialData = response.data;
            await initializeReferenceProjectSelectBox();
            await initializeAffectedDtoNumbersSelectBox(materialData);

            // Keep id of tk material
            $('#tkMaterialId').val(materialData.id);

            // Set Reference Project
            const referenceValue = materialData.project_number || "-";
            $('#referenceProject').dropdown('set selected', referenceValue).dropdown('set text', referenceValue);

            // Set Material Added Starts By
            const materialAddedStartsByValue = materialData.material_added_starts_by || "-";
            $('#materialAddedStartsBy').dropdown('set selected', materialAddedStartsByValue).dropdown('set text', materialAddedStartsByValue);

            // Set Material Added
            const materialAddedValue = materialData.material_added_number && materialData.material_added_description
                ? `${materialData.material_added_number} - ${materialData.material_added_description}`
                : "-";
            $('#materialAdded').dropdown('set selected', materialAddedValue).dropdown('set text', materialAddedValue);

            // Set Material Deleted Starts By
            const materialDeletedStartsByValue = materialData.material_deleted_starts_by || "-";
            $('#materialDeletedStartsBy').dropdown('set selected', materialDeletedStartsByValue).dropdown('set text', materialDeletedStartsByValue);

            // Set Material Deleted
            const materialDeletedValue = materialData.material_deleted_number && materialData.material_deleted_description
                ? `${materialData.material_deleted_number} - ${materialData.material_deleted_description}`
                : "-";
            $('#materialDeleted').dropdown('set selected', materialDeletedValue).dropdown('set text', materialDeletedValue);

            // Set Station Code and Station Name
            $('#stationCode').val(materialData.work_center || "-");
            $('#stationName').val(materialData.work_content || "-");

            // Quantity and Unit
            $('#quantity').val(materialData.quantity.replace(/\.000$/, ""));
            $('#unit').val(materialData.unit);

            // Set Special Note
            $('#specialNote').val(materialData.acc);

            // Match Change Type Radio Button
            $('.changeType').each(function () {
                if ($(this).val() === materialData.type) {
                    $(this).prop('checked', true);
                    if (materialData.type === "Panel") {
                        $('#sidePanelEffect').prop('disabled', false); // Enable if 'Panel'
                    } else {
                        $('#sidePanelEffect').prop('disabled', true).prop('checked', false); // Disable and uncheck otherwise
                    }
                }
            });

            // Set Side Panel Effect
            $('#sidePanelEffect').prop('checked', materialData.effective === "1");

            // Open Modal
            $('#editTkFormModal').modal({ blurring: false }).modal('show');
        } else {
            fireToastr('error', 'Error fetching TK Form Material:', response.statusText);
        }
    } catch (error) {
        fireToastr('error', 'Error fetching TK Form Material:', error.message);
    }
}

async function initializeReferenceProjectSelectBox(projectNo) {
    const projectSelectBox = $('#referenceProject');

    projectSelectBox.dropdown({
        apiSettings: {
            url: `/dpm/dtoconfigurator/api/controllers/BaseController.php?action=searchProject&keyword={query}`,
            cache: false,
            onResponse: function(response) {
                const menuElement = document.querySelector('.referenceProject .menu.transition.visible');
                const projects = Array.isArray(response) ? response : Object.values(response);
                let results = [];

                if (projects.length === 0) {
                    if (menuElement)
                        menuElement.innerHTML = '';
                    return { results };
                }

                results = projects.map(project => ({
                    name: `<b>${project.FactoryNumber}</b> - ${project.ProjectName}`,
                    value: project.FactoryNumber
                }));

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
        multiple: true,
        fullTextSearch: true,
        forceSelection: false,
        selectOnKeydown: false
    });

}

async function initializeAffectedDtoNumbersSelectBox(materialData) {
    const affectedSelectBox = $('#affectedDtoNumbers');

    // Initialize the Semantic UI dropdown
    affectedSelectBox.dropdown({
        apiSettings: {
            url: `/dpm/dtoconfigurator/api/controllers/TkFormController.php?action=searchDtoNumbers&keyword={query}`,
            cache: false,
            onResponse: function(response) {
                const dtoNumbers = Array.isArray(response) ? response : Object.values(response);

                const results = dtoNumbers.map(dto => ({
                    name: `<span style="font-weight:bold;">${dto.dto_number}</span> - ${dto.description.substring(0, 100)}${dto.description.length > 100 ? '...' : ''}`,
                    value: dto.dto_number,
                    text: dto.dto_number
                }));
                return { results };
            }
        },
        fields: {
            remoteValues: 'results',
            name: 'name',
            value: 'value',
            text: 'text'
        },
        clearable: true,
        allowAdditions: false,
        minCharacters: 1,
        multiple: true
    });

    affectedSelectBox.dropdown('clear');

    if (materialData.affected_dto_numbers) {
        let affectedDtoNumbers = materialData.affected_dto_numbers ? materialData.affected_dto_numbers.split('|') : [];
        affectedDtoNumbers = affectedDtoNumbers.filter(dto => dto !== materialData.dto_number);

        // Add <option> elements to the <select>
        affectedDtoNumbers.forEach(dto => {
            const option = $('<option>', {
                value: dto,
                class: 'addition',
                text: dto
            });
            affectedSelectBox.append(option);
        });

        // Get the dropdown container
        const dropdownContainer = affectedSelectBox.closest('.ui.dropdown');

        // Insert <a> tags next to the <i class="dropdown icon">
        const dropdownIcon = dropdownContainer.find('.dropdown.icon');
        affectedDtoNumbers.forEach(dto => {
            const label = $('<a>', {
                class: 'ui label transition visible',
                'data-value': dto,
                style: 'display: inline-block !important;',
                text: dto
            }).append($('<i>', { class: 'delete icon' })); // Add a delete icon inside the label

            // Insert the label after the dropdown icon
            dropdownIcon.after(label);
        });

        // Set the selected values in the dropdown
        affectedSelectBox.dropdown('set selected', affectedDtoNumbers);
        affectedSelectBox.dropdown('refresh');
    }
}

$('.changeType').on('change', function() {
    if ($(this).val() === 'Panel') {
        $('#sidePanelEffect').prop('disabled', false);
    } else {
        $('#sidePanelEffect').prop('disabled', true).prop('checked', false); // Disable and uncheck checkbox
    }
});


// UPDATE MATERIAL STARTS
$('#editTkFormModal .update.button').on('click', async function() {
    $('#editTkFormModal .update.button').addClass('loading disabled');

    const formElement = document.getElementById('editTkFormModalForm');
    const formData = new FormData(formElement);

    const tkMaterialId = $('#tkMaterialId').val().trim();
    let referenceProject =  $('#referenceProject').dropdown('get value');

    if (!referenceProject)
        referenceProject =  $('#referenceProject').dropdown('get text');

    let effective = $('#sidePanelEffect').is(':checked') ? '1' : '0';

    formData.append('action', 'editTkFormMaterial');
    formData.append('effective', effective);
    formData.append('referenceProject', referenceProject);
    formData.append('id', tkMaterialId);

    try {
        const url = '/dpm/dtoconfigurator/api/controllers/TkFormMaterialController.php?';
        const response = await axios.post(url, formData, { headers: { "Content-Type": "multipart/form-data" } });

        if (response.data.responseStatus === 'warning') {
            const data = response.data.data;

            let errorMsgBody = `⚠️ <b>Important Notice</b><br><br>`;
            errorMsgBody += `Updating this material list will delete BOM changes worked below<br><br>`;
            errorMsgBody += `<ul style="list-style-type: none; padding: 0;">`;
            data.forEach(row => {
                errorMsgBody += `<li>🔹 <b>${row.project_number}</b> - <b>${row.nachbau_number}</b> - ${row.last_updated_by}</li>`;
            });
            errorMsgBody += `</ul>`;

            Swal.fire({
                title: "Are you sure?",
                html: errorMsgBody,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Still Continue",
                cancelButtonText: "Cancel",
                allowOutsideClick: false
            }).then(async (result) => {
                if (result.isConfirmed) {
                    formData.append('forceUpdate', '1');

                    try {
                        const forceResponse = await axios.post(url, formData, { headers: { "Content-Type": "multipart/form-data" } });
                        if (forceResponse.data.responseStatus === 'success') {
                            fireToastr('success', 'Material list updated successfully.');
                            location.reload();
                            $('#editTkFormModal').modal('hide');
                        } else {
                            showErrorDialog("Force update failed. Please try again.");
                        }
                    } catch (error) {
                        showErrorDialog(`Error: ${error.message}`);
                    }
                }
            });

        } else if (response.data.responseStatus === 'success') {
            fireToastr('success', 'Material list updated successfully.');
            location.reload();
            $('#editTkFormModal').modal('hide');
        }
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    } finally {
        $('#editTkFormModal .update.button').removeClass('loading disabled');
    }
});

// UPDATE MATERIAL ENDS

// DELETE MATERIAL STARTS
$('#editTkFormModal .delete.button').on('click', async function() {
    const tkMaterialId = $('#tkMaterialId').val().trim();
    if (!tkMaterialId) {
        fireToastr('error', 'Error: TK Material ID is missing.');
        return;
    }

    const affectedDtoNumbersArr = [];
    $('#affectedDtoNumbers option').each(function() {
        affectedDtoNumbersArr.push($(this).val());
    });

    if (affectedDtoNumbersArr.length > 0) {
        $('#affectedDtoRadioButtonsContainer').empty();

        let radioButtonsHtml = `<div class="field">
                                            <div class="ui radio checkbox">
                                                <input type="radio" class="affectedDto" name="affectedDto" value="all">
                                                <label style="font-weight:bold;">REMOVE FROM ALL TK FORMS</label>
                                            </div>
                                        </div>`;

        affectedDtoNumbersArr.forEach((item) => {
            radioButtonsHtml += `<div class="field">
                <div class="ui radio checkbox">
                    <input type="radio" class="affectedDto" name="affectedDto" value="${item}">
                    <label style="font-weight:bold;">${item}</label>
                </div>
            </div>`;
        });

        // Append all radio buttons at once
        $('#affectedDtoRadioButtonsContainer').append(radioButtonsHtml);

        // Reinitialize Semantic UI checkboxes
        $('#affectedDtoRadioButtonsContainer .ui.radio.checkbox').checkbox();

        // Show the delete confirmation modal
        $('#deleteTkMaterialConfirmModal').modal({ blurring: false }).modal('show');
    }
    else {
        showConfirmationDialog({
            title: 'Are you sure?',
            htmlContent: 'Do you really want to delete this TK Material?',
            confirmButtonText: 'Yes, delete it!',
            confirmButtonColor: "#d33",
            onConfirm: async function () {
                await deleteTkFormMaterial(tkMaterialId, 'none');
            },
        });
    }
});

$('#deleteTkMaterialConfirmModal .deleteTkMaterial.button').on('click', async function() {
    const tkMaterialId = $('#tkMaterialId').val().trim();
    const dtoNumberSelectedOption= $('input[name="affectedDto"]:checked').val();

    if(!dtoNumberSelectedOption) {
        fireToastr('error', 'Please choose an option');
        return;
    }

    showConfirmationDialog({
        title: 'Are you sure?',
        htmlContent: 'Do you really want to delete this TK Material?',
        confirmButtonText: 'Yes, delete it!',
        confirmButtonColor: "#d33",
        onConfirm: async function () {
            await deleteTkFormMaterial(tkMaterialId, dtoNumberSelectedOption);
        },
    });
});

async function deleteTkFormMaterial(tkMaterialId, dtoNumberSelectedOption) {
    try {
        const requestData = {
            id: tkMaterialId,
            selectedDtoOption: dtoNumberSelectedOption,
            action: 'deleteTkFormMaterial'
        }

        const response = await axios.post('/dpm/dtoconfigurator/api/controllers/TkFormMaterialController.php?', requestData, { headers: { 'Content-Type': 'multipart/form-data' }});

        if (response.data.responseStatus === 'warning') {
            const data = response.data.data;

            let errorMsgBody = `⚠️ <b>Important Notice</b><br><br>`;
            errorMsgBody += `Updating this material list will delete BOM changes worked below<br><br>`;
            errorMsgBody += `<ul style="list-style-type: none; padding: 0;">`;
            data.forEach(row => {
                errorMsgBody += `<li>🔹 <b>${row.project_number}</b> - <b>${row.nachbau_number}</b> - ${row.last_updated_by}</li>`;
            });
            errorMsgBody += `</ul>`;

            Swal.fire({
                title: "Are you sure?",
                html: errorMsgBody,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Still Continue",
                cancelButtonText: "Cancel",
                allowOutsideClick: false
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const newRequestData = {
                        id: tkMaterialId,
                        selectedDtoOption: dtoNumberSelectedOption,
                        action: 'deleteTkFormMaterial',
                        forceUpdate: '1'
                    }

                    try {
                        const forceResponse = await axios.post('/dpm/dtoconfigurator/api/controllers/TkFormMaterialController.php?', newRequestData, { headers: { 'Content-Type': 'multipart/form-data' }});
                        if (forceResponse.data.responseStatus === 'success') {
                            fireToastr('success', 'Material list removed successfully.');
                            $('#editTkFormModal').modal('hide');
                            $('#deleteTkMaterialConfirmModal').modal('hide');
                            location.reload();
                        } else {
                            showErrorDialog("Force update failed. Please try again.");
                        }
                    } catch (error) {
                        showErrorDialog(`Error: ${error.message}`);
                    }
                }
            });

        } else if (response.data.responseStatus === 'success') {
            fireToastr('success', 'Material list updated successfully.');
            location.reload();
            $('#editTkFormModal').modal('hide');
        }
    } catch (error) {
        fireToastr('error', 'An unexpected error has occurred. Please try again later.');
        console.error('Error:', error);
    }
}
// DELETE MATERIAL ENDS

$(document).on('click', '.copy-material', function () {
    let material = $(this).data('material');

    const starters = ['A7E00', 'A7ETKBL', 'A7ET', 'A7E', ':: VTH:', ':: CTH:'];
    for (const starter of starters) {
        if (material.startsWith(starter)) {
            material = material.slice(starter.length);
            break;
        }
    }

    navigator.clipboard.writeText(material).then(() => {
        fireToastr('success', 'Material ' + material + ' copied!')
        setTimeout(() => {
            $(this).attr('title', 'Copy to clipboard');
        }, 1000);
    });
});
