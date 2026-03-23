$(document).ready(async function() {
    $('.ui.checkbox').checkbox();
    $('#addMaterialListContainer .tabular.menu .item').tab();

    $('#addMaterialListPage .loader').hide();
    $('#addMaterialListContainer').transition('zoom');

    const counts = await fetchCountOfNcAndTkNotesOfTkForm();
    document.getElementById('ncListCount').innerText = counts.nc_count;
    document.getElementById('tkNotesCount').innerText = counts.tk_notes_count;

    if (parseInt(counts.nc_count) > 0)
        document.getElementById('ncListCount').classList.add('brown');
    if (parseInt(counts.tk_notes_count) > 0)
        document.getElementById('tkNotesCount').classList.add('brown');

    await initializeReferenceProjectSelectBox();
    await initializeMaterialDropdowns();
    await initializeAffectedDtoNumbersSelectBox();
    await initializeDeviceSecondaryWorkCenterSelectBox();
});

// ADD MATERIAL FORM SECTION START
async function initializeReferenceProjectSelectBox() {
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
        minCharacters: 3,
        clearable: true,
        allowAdditions: false,
    });
}

async function initializeAffectedDtoNumbersSelectBox() {
    const affectedSelectBox = $('#affectedDtoNumbers');

    affectedSelectBox.dropdown({
        apiSettings: {
            url: `/dpm/dtoconfigurator/api/controllers/TkFormController.php?action=searchDtoNumbers&keyword={query}`,
            cache: false,
            onResponse: function(response) {
                const menuElement = document.querySelector('.affectedDtoNumbers .menu.transition.visible');
                const dtoNumbers = Array.isArray(response) ? response : Object.values(response);
                let results = [];

                if (dtoNumbers.length === 0) {
                    if (menuElement)
                        menuElement.innerHTML = '';
                    return { results };
                }

                results = dtoNumbers.map(dto => ({
                    name: `<span style="font-weight:bold;">${dto.dto_number}</span> - ${dto.description.substring(0, 100)}${dto.description.length > 100 ? '...' : ''}`,
                    value: `${dto.id}|${dto.dto_number}`, // Encode id and dto_number with a delimiter
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
        multiple: true,
        fullTextSearch: true,
        forceSelection: false,
        selectOnKeydown: false,
    });
}

let materialMap = {};
async function initializeMaterialDropdowns() {
    $('#materialAdded, #materialDeleted').dropdown({
        apiSettings: {
            url: `/dpm/dtoconfigurator/api/controllers/MaterialController.php?action=getMaterialsBySearch&keyword={query}`,
            cache: false,
            onResponse: function(response) {
                const dropdownId = $(this).attr('id');
                const menuElement = document.querySelector(`#${dropdownId} + .menu.transition.visible`);
                const materials = Array.isArray(response) ? response : Object.values(response);
                let results = [];

                if (materials.length === 0) {
                    if (menuElement)
                        menuElement.innerHTML = '';
                    return { results };
                }

                results = materials.map(material => {
                    materialMap[material.id] = {
                        isDevice: material.is_device,
                        isCableCode: material.is_cable_code,
                        stationCode: material.work_center,
                        stationName: material.work_content
                    };

                    return {
                        name: `<b>${material.material_number}</b> - ${material.description}`,
                        value: material.id
                    };
                });

                return { results };
            },
            beforeSend: function(settings) {
                const dropdown = $(this);
                dropdown.find('.menu').empty();
                return settings;
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
        fullTextSearch: false,
        forceSelection: true,
        selectOnKeydown: false,
        filterRemoteData: false,
        saveRemoteData: false,
        onChange: updateWorkCenterInputs
    });

    $('#materialAdded, #materialDeleted').on('input', '.search', function() {
        const $dropdown = $(this).closest('.dropdown');
        const currentValue = $dropdown.dropdown('get value');

        if (currentValue) {
            $dropdown.dropdown('set value', '');
        }
    });

    $('.materialSelectBox').on('input', '.search', function () {
        const currentVal = $(this).val();
        if (currentVal.length <= 13) {
            const cleaned = currentVal.replace(/[^a-zA-Z0-9]/g, '');
            $(this).val(cleaned);
        }
    });
}
// deviceSecondaryWcSelect
async function initializeDeviceSecondaryWorkCenterSelectBox() {
    const workCenterSelectBox = $('#deviceSecondaryWcSelect');

    try {
        // Fetch all work centers when page loads
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/WorkCenterController.php', {
            params: { action: 'getWorkCenters' }
        });

        const workCenters = response.data;

        workCenterSelectBox.empty().append('<option value="" disabled selected>Search Work Center</option>');
        workCenters.forEach(workCenter => {
            workCenterSelectBox.append(
                `<option value="${workCenter.id}">
                    <b>${workCenter.work_center}</b> - ${workCenter.work_content}
                </option>`
            );
        });

        // Each time search in dropdown
        workCenterSelectBox.dropdown({
            apiSettings: {
                url: `/dpm/dtoconfigurator/api/controllers/WorkCenterController.php?action=getWorkCentersBySearch&keyword={query}`,
                cache: false,
                onResponse: function(response) {
                    const menuElement = document.querySelector('.deviceSecondaryWcSelect .menu.transition.visible');
                    const workCenters = Array.isArray(response) ? response : Object.values(response);
                    let results = [];

                    if (workCenters.length === 0) {
                        if (menuElement)
                            menuElement.innerHTML = '';
                        return { results };
                    }

                    results = workCenters.map(workCenter => ({
                        name: `<b>${workCenter.work_center}</b> - ${workCenter.work_content}`,
                        value: workCenter.id
                    }));

                    return { results };
                }
            },
            fields: {
                remoteValues: 'results',
                name: 'name',
                value: 'value'
            },
            clearable: true,
            allowAdditions: false,
            minCharacters:1,
            fullTextSearch: true,
            forceSelection: false,
            selectOnKeydown: false,
            onChange: async function(value) {
                const materialsHasSubKmats = ['4', '6', '11', '12', '19', '23', '45', '46'];

                if (materialsHasSubKmats.includes(value)) {
                    const subWorkCenterSelectBox = $('#deviceSecondarySubWcSelect');

                    const response = await axios.get(`/dpm/dtoconfigurator/api/controllers/WorkCenterController.php`, {
                        params: { action: 'getSubWorkCentersOfWorkCenter', workCenterId: value },
                        headers: { "Content-Type": "multipart/form-data" }
                    });

                    const subWorkCenters = response.data;
                    subWorkCenterSelectBox.empty().append('<option value="" selected>Search Sub Work Center</option>');

                    subWorkCenters.forEach(subWorkCenter => {
                        subWorkCenterSelectBox.append(
                            `<option value="${subWorkCenter.sub_kmat_name}">
                                  <b>${subWorkCenter.sub_kmat}</b> - ${subWorkCenter.sub_kmat_name}
                              </option>`
                        );
                    });

                    subWorkCenterSelectBox.dropdown({
                        search: true, // Enables frontend search
                        clearable: true,
                        allowAdditions: false
                    });

                    subWorkCenterSelectBox.on('change', function() {
                        $('#subWorkCenterSelectErrMsg').addClass('hidden')
                    });


                    $('#deviceSecondarySubWcSelect').dropdown('clear').removeClass('disabled');
                } else {
                    $('#deviceSecondarySubWcSelect').dropdown('clear').addClass('disabled');
                }
            }
        });
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        fireToastr('error', errorMessage);
        console.error('Error:', error);
    }
}

function updateWorkCenterInputs() {
    const materialAddedValue = $('#materialAdded').dropdown('get value');
    const materialDeletedValue = $('#materialDeleted').dropdown('get value');

    // Check if there is a selected value for "materialAdded"
    if (materialAddedValue) {
        $('.materialErrMsg').hide(); $('.workCenterCheckErrMsg').hide(); $('.changeTypeErrMsg').hide();
        $('#addedStationField').show();
        fillWorkCenterInputs(materialAddedValue, '#addedStationCode', '#addedStationName');

        if(materialMap[materialAddedValue].isDevice === '1'){
            $('#deviceSecondaryWorkCenterSelectDiv').css('display', '');
        } else {
            $('#deviceSecondaryWorkCenterSelectDiv').css('display', 'none');
        }

        if(materialMap[materialAddedValue].isCableCode === '1'){
            $('#materialAddedStartsBy').parent().show().css('width', '20%');
        } else {
            $('#materialAddedStartsBy').parent().hide();
        }

    } else {
        $('#addedStationField').hide();
        $('#addedStationCode').val('');
        $('#addedStationName').val('');
    }

    // Check if there is a selected value for "materialDeleted"
    if (materialDeletedValue) {
        $('.materialErrMsg').hide(); $('.workCenterCheckErrMsg').hide(); $('.changeTypeErrMsg').hide();
        $('#deletedStationField').show();
        fillWorkCenterInputs(materialDeletedValue, '#deletedStationCode', '#deletedStationName');

        if(materialMap[materialDeletedValue].isCableCode === '1'){
            $('#materialDeletedStartsBy').parent().show().css('width', '20%');
        } else {
            $('#materialDeletedStartsBy').parent().hide();
            $('#materialDeleted').parent().css('width', '100%');
        }
    } else {
        $('#deletedStationField').hide();
        $('#deletedStationCode').val('');
        $('#deletedStationName').val('');
    }
}

function fillWorkCenterInputs(materialId, stationCodeSelector, stationNameSelector) {
    const materialData = materialMap[materialId] || {};
    const stationCode = materialData.stationCode;
    const stationName = materialData.stationName;

    if (!stationCode || !stationName) {
        $(stationCodeSelector).val('Tanımlı Değil').css({ 'color': 'red', 'font-weight': 'bold' });
        $(stationNameSelector).val('Tanımlı Değil').css({ 'color': 'red', 'font-weight': 'bold' });
    } else {
        $(stationCodeSelector).val(stationCode).css({ 'color': 'blue', 'font-weight': 'bold' });
        $(stationNameSelector).val(stationName).css({ 'color': 'blue', 'font-weight': 'bold' });
    }
}

$('#btnAddList').on('click', async function() {
    $('#btnAddList').addClass('loading disabled');
    const formElement = document.getElementById('addMaterialListForm');
    const formData = new FormData(formElement);

    // Material Error Controls
    const materialAdded = $('#materialAdded').val();
    const materialDeleted = $('#materialDeleted').val();

    let operation;
    if (materialAdded && !materialDeleted) {
        operation = 'add';
    } else if (materialAdded && materialDeleted) {
        operation = 'replace';
    } else if (!materialAdded && materialDeleted) {
        operation = 'delete';
    } else {
        $('.materialErrMsg').text('Please select a material!').transition('pulse');
        $('#btnAddList').removeClass('loading disabled');
        return;
    }

    const addedStationCode = $('#addedStationCode').val();
    const deletedStationCode = $('#deletedStationCode').val();

    // Work Center Error Controls
    if (addedStationCode && deletedStationCode && addedStationCode !== deletedStationCode) {
        $('.workCenterCheckErrMsg').text('Work Center values of materials must be the same!').transition('pulse');
        $('#btnAddList').removeClass('loading disabled');
        return;
    }
    else if (materialAdded && addedStationCode === 'Tanımlı Değil') {
        $('#addedStationCodeErrMsg').text('Please first update work center of material!').transition('pulse');
        $('#btnAddList').removeClass('loading disabled');
        return;
    }
    else if (materialDeleted && deletedStationCode === 'Tanımlı Değil') {
        $('#deletedStationCodeErrMsg').text('Please first update work center of material!').transition('pulse');
        $('#btnAddList').removeClass('loading disabled');
        return;
    }

    // List Type Selection Check
    const changeType = $('input[name="changeType"]:checked').val();
    if (changeType) {
        formData.append('changeType', changeType);
    } else {
        $('.changeTypeErrMsg').transition('pulse');
        $('#btnAddList').removeClass('loading disabled');
        return;
    }

    const sidePanelEffect = $('input[name="sidePanelEffect"]').is(':checked');
    const secondaryWcSelect = $('#deviceSecondaryWcSelect').val() ?? '';
    const secondarySubWcSelectName = $('#deviceSecondarySubWcSelect').val() ?? '';
    formData.append('sidePanelEffect', sidePanelEffect);
    formData.append('secondaryWcSelect', secondaryWcSelect);
    formData.append('secondarySubWcSelectName', secondarySubWcSelectName);

    let materialAddedStartsBy = '';
    if(materialMap[materialAdded]?.isCableCode === '1')
        materialAddedStartsBy =  $('#materialAddedStartsBy').val();

    let materialDeletedStartsBy = '';
    if(materialMap[materialDeleted]?.isCableCode === '1')
        materialDeletedStartsBy =  $('#materialDeletedStartsBy').val();

    formData.append('materialAddedStartsBy', materialAddedStartsBy);
    formData.append('materialDeletedStartsBy', materialDeletedStartsBy);

    formData.append('tkformId', getUrlParam('id'));
    formData.append('dtoNumber', getUrlParam('dto-number'));
    formData.append('operation', operation);
    formData.append('action', 'createTkFormMaterial');

    try {
        const response = await axios.post('/dpm/dtoconfigurator/api/controllers/TkFormMaterialController.php?', formData, { headers: { 'Content-Type': 'multipart/form-data' }} );

        if (response.status === 200) {
            $('#addMaterialSuccessMsg').transition('jiggle');
            setTimeout(function() {
                $('#addMaterialSuccessMsg').transition('fade out');
            }, 7000);

            await resetCreateMaterialListForm();
        } else {
            const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
            fireToastr('error', errorMessage);
            $('#btnAddList').removeClass('loading disabled');
        }
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        fireToastr('error', errorMessage);
        $('#btnAddList').removeClass('loading disabled');
    }
});

async function resetCreateMaterialListForm() {
    $('#btnAddList').removeClass('loading disabled');
    $('.changeTypeErrMsg').hide();

    $('input[name="changeType"]').prop('checked', false);
    $('input[name="sidePanelEffect"]').prop('checked', false);

    // ✅ Dropdown'ları düzgün temizle
    $('#materialAdded').dropdown('clear').dropdown('restore defaults');
    $('#materialDeleted').dropdown('clear').dropdown('restore defaults');

    $('#affectedDtoNumbers').dropdown('clear');
    $('#quantity').val('1');
    $('#unit').val('ST');
    $('#specialNote').val('');
    $('#deviceSecondaryWcSelect').dropdown('clear');
    $('#deviceSecondarySubWcSelect').dropdown('clear').addClass('disabled');
    $('#deviceSecondaryWorkCenterSelectDiv').css('display', 'none');

    $('#materialAddedStartsBy').parent().hide();
    $('#materialDeletedStartsBy').parent().hide();

}

$('.changeType').on('change', function() {
    if ($(this).val() === 'Panel') {
        $('#sidePanelEffect').prop('disabled', false);
    } else {
        $('#sidePanelEffect').prop('disabled', true).prop('checked', false); // Disable and uncheck checkbox
    }
});
// ADD MATERIAL FORM SECTION END