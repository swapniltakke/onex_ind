$(document).ready(async function() {
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
        const id = getUrlParam('id');

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
        $('#tkJtCollectionPage .loader').hide();
        $('#tkJtCollectionPage #materialListTable').hide();
        $('#tkJtCollectionPage #materialListContainer').show();
        $('#tkJtCollectionPage #materialListCheckMsg').transition('pulse')
        $('#tkJtCollectionPage #materialListTableContainer').hide();
    }
    else {
        const table = $('#tkJtCollectionPage #materialListTable').DataTable({
            data: data,
            pageLength: 100,
            lengthMenu: [100],
            autoWidth: false,
            order: [[0, 'desc']],
            fixedHeader:true,
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
                    render: (data, type, row) => row.work_center !== ''
                        ? `<a class="ui teal label">${row.work_center}</a><br>
                                                    <h5 style="margin-top:2%;">${row.work_content}</h5>`
                        : `<div class="ui red horizontal label">Undefined</div>`,
                    className: 'center aligned'
                },
                {
                    data: 'material_added_number',
                    render: (data, type, row, meta) => {
                        const addedMaterial = `${row.material_added_starts_by}${row.material_added_number}`;
                        const rowId = `row-${meta.row}-add`;
                        let linkStyle = '';
                        let dataTooltip = 'Navigate to Material Viewer';

                        if (row.material_added_sap_defined === '0') {
                            dataTooltip = 'Material not found in SAP system';
                            linkStyle = 'color:red;font-weight:bold;';
                            $('#tkJtCollectionPage #materialNotDefinedInSapMsg').removeClass('hidden');
                        } else if (row.affected_dto_numbers !== '' && row.affected_dto_numbers !== null) {
                            dataTooltip = 'This material list has multiple DTO numbers';
                            linkStyle = 'color:green;font-weight:bold;';
                            $('#tkJtCollectionPage #listAffectsOtherDtosMsg').removeClass('hidden');
                        }

                        return addedMaterial ? `
                            <div class="ui checkbox jt-checkbox">
                                <input type="checkbox" class="jt-checkbox" data-type="add" data-material="${addedMaterial}" data-row-id="${rowId}">
                                <label>
                                    <i class="plus circle icon" style="color:green;"></i>
                                    <a target="_blank" href="/materialviewer/?material=${addedMaterial}" 
                                       data-tooltip="${dataTooltip}" data-position="top center" data-variation="inverted" 
                                       style="${linkStyle}">
                                       ${addedMaterial}
                                    </a>
                                </label>
                            </div>
                            <i class="copy icon  black copy-material"  data-material="${addedMaterial}" title="Copy to clipboard" style="cursor: pointer; margin-left: 5px;"></i>`
                            : '';

                    },
                    className: 'center aligned addedMaterial'
                },
                {
                    data: 'material_deleted_number',
                    render: (data, type, row, meta) => {
                        const deletedMaterial = `${row.material_deleted_starts_by}${row.material_deleted_number}`;
                        const rowId = `row-${meta.row}-delete`;
                        let linkStyle = '';
                        let dataTooltip = 'Navigate to Material Viewer';

                        if (row.material_deleted_sap_defined === '0') {
                            dataTooltip = 'Material not found in SAP system';
                            linkStyle = 'color:red;font-weight:bold;';
                            $('#tkJtCollectionPage #materialNotDefinedInSapMsg').removeClass('hidden');
                        } else if (row.affected_dto_numbers !== '' && row.affected_dto_numbers !== null) {
                            dataTooltip = 'This material list affects multiple DTO numbers';
                            linkStyle = 'color:green;font-weight:bold;';
                            $('#tkJtCollectionPage #listAffectsOtherDtosMsg').removeClass('hidden');
                        }

                        return deletedMaterial ? `
                            <div class="ui checkbox jt-checkbox">
                                <input type="checkbox" class="jt-checkbox" data-type="delete" data-material="${deletedMaterial}" data-row-id="${rowId}">
                                <label>
                                    <i class="minus circle icon" style="color:red;"></i>
                                    <a target="_blank" href="/materialviewer/?material=${deletedMaterial}" 
                                       data-tooltip="${dataTooltip}" data-position="top center" data-variation="inverted" 
                                       style="${linkStyle}">
                                       ${deletedMaterial}
                                    </a>
                                </label>
                            </div>
                            <i class="copy icon  black copy-material"  data-material="${deletedMaterial}" title="Copy to clipboard" style="cursor: pointer; margin-left: 5px;"></i>` : '';
                    },
                    className: 'center aligned deletedMaterial'
                },
                {
                    data: 'material_deleted_description',
                    render: (data, type, row) => row.operation === 'add' ? row.material_added_description : row.material_deleted_description,
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
                }
            ],
            destroy: true
        });

        $('#tkJtCollectionPage .loader').hide();
        $('#tkJtCollectionPage #materialListContainer').transition('zoom', function() {
            requestAnimationFrame(() => {
                table.fixedHeader.adjust();
            });
        });
        table.draw();
    }

    //Search customization
    const $searchInput = $('.dt-search input[type="search"]');
    $searchInput.attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
}

$('#collectJTsBtn').on('click', async function () {
    $(this).addClass('loading disabled');
    const selectedMaterials = [];

    $('#materialListTable input[type="checkbox"]:checked').each(function () {
        let material = $(this).data('material');

        const starters = ['A7E00', 'A7ETKBL', 'A7ET', 'A7E'];
        for (const starter of starters) {
            if (material.startsWith(starter)) {
                material = material.slice(starter.length);
                break;
            }
        }

        selectedMaterials.push(material);
    });

    if (selectedMaterials.length === 0) {
        fireToastr('warning', 'Please select at least one material.');
        $(this).removeClass('loading disabled');
        return;
    }

    try {
        const query = new URLSearchParams();
        query.append('action', 'downloadJtFilesOfTkForm');
        query.append('dtoNumber', getUrlParam('dto-number'));
        selectedMaterials.forEach(m => query.append('materials[]', m));

        window.location.href = `/dpm/dtoconfigurator/api/controllers/TKFormController.php?${query.toString()}`;
        showSuccessDialog('Download has started successfully.');
    } catch (error) {
        console.error('Error collecting JTs:', error);
        fireToastr('error', 'An error occurred while collecting JTs.');
    } finally {
        $(this).removeClass('loading disabled');
    }
});


function setupCheckboxHighlighting() {
    $('#tkJtCollectionPage #materialListTable').on('change', 'input[type="checkbox"]', function () {
        const $td = $(this).closest('td');
        if (this.checked) {
            $td.css('background-color', 'wheat'); // highlight on select
        } else {
            $td.css('background-color', '');
        }
    });
}

$('#selectAllMaterials').on('change', function () {
    const isChecked = this.checked;
    const table = $('#tkJtCollectionPage #materialListTable').DataTable();

    // Store the state globally
    window.__jtSelectAllActive = isChecked;

    // Go through ALL rows across ALL pages
    table.rows().every(function () {
        const rowNode = this.node();
        // Skip if not visible in DOM
        if ($(rowNode).length > 0) {
            $(rowNode).find('input[type="checkbox"]').each(function () {
                $(this).prop('checked', isChecked).trigger('change');
            });
        }
    });
});

$('#tkJtCollectionPage #materialListTable').DataTable().on('draw', function () {
    $('#tkJtCollectionPage .jt-checkbox').checkbox();
    setupCheckboxHighlighting();

    const all = $('#selectAllMaterials').is(':checked');
    const add = $('#selectAddedMaterials').is(':checked');
    const del = $('#selectDeletedMaterials').is(':checked');

    if (all) handleCheckboxSelection('all');
    else if (add) handleCheckboxSelection('add');
    else if (del) handleCheckboxSelection('delete');

});

$(document).on('click', '.copy-material', function () {
    let material = $(this).data('material');
    const starters = ['A7E00', 'A7ETKBL', 'A7ET', 'A7E'];
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


function handleCheckboxSelection(type) {
    const allCheckbox = $('#selectAllMaterials');
    const addedCheckbox = $('#selectAddedMaterials');
    const deletedCheckbox = $('#selectDeletedMaterials');
    const table = $('#tkJtCollectionPage #materialListTable').DataTable();

    const isAll = type === 'all' && allCheckbox.is(':checked');
    const isAdd = type === 'add' && addedCheckbox.is(':checked');
    const isDelete = type === 'delete' && deletedCheckbox.is(':checked');

    // Deselect others and disable them
    allCheckbox.prop('disabled', !isAll && (isAdd || isDelete));
    addedCheckbox.prop('disabled', !isAdd && (isAll || isDelete));
    deletedCheckbox.prop('disabled', !isDelete && (isAll || isAdd));

    // Clear existing checks
    table.$('input.jt-checkbox').prop('checked', false).trigger('change');

    // Apply new selection
    table.rows().every(function () {
        const row = $(this.node());
        const checkboxes = row.find('input.jt-checkbox');

        checkboxes.each(function () {
            const checkbox = $(this);
            const typeAttr = checkbox.data('type');

            if (isAll ||
                (isAdd && typeAttr === 'add') ||
                (isDelete && typeAttr === 'delete')) {
                checkbox.prop('checked', true).trigger('change');
            }
        });
    });

    // Uncheck others if current is unchecked
    if (!isAll && !isAdd && !isDelete) {
        allCheckbox.prop('disabled', false);
        addedCheckbox.prop('disabled', false);
        deletedCheckbox.prop('disabled', false);
    }
}

// Event bindings
$('#selectAllMaterials').on('change', function () {
    handleCheckboxSelection('all');
});

$('#selectAddedMaterials').on('change', function () {
    handleCheckboxSelection('add');
});

$('#selectDeletedMaterials').on('change', function () {
    handleCheckboxSelection('delete');
});
