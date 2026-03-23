let searchTimeout;
let updateSearchTimeout;

$(document).ready(function() {
    $('.menu .item').tab();

    initializeWorkCenterSelectBox();
    initializeSapMaterialDropdown();
    initializeExistingMaterialsDropdown();

    $('#materialDefinePage .loader').hide();
    $('#materialDefineContainer').transition('zoom');

});

async function initializeWorkCenterSelectBox() {
    const workCenterSelectBox = $('#workCenterSelect');

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
                    const menuElement = document.querySelector('.workCenterSelect .menu.transition.visible');
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
            onChange: function(value) {
                const materialsHasSubKmats = ['4', '6', '11', '12', '19', '23', '45', '46'];

                if (materialsHasSubKmats.includes(value)) {
                    initializeSubWorkCenterSelectBox(value);
                    $('#subWorkCenterSelectErrMsg').removeClass('hidden');
                    $('#subWorkCenterSelect').dropdown('clear').removeClass('disabled');
                } else {
                    $('#subWorkCenterSelectErrMsg').addClass('hidden');
                    $('#subWorkCenterSelect').dropdown('clear').addClass('disabled');
                }

                $('#defineMaterialButton').prop('disabled', false);
            }
        });
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        fireToastr('error', errorMessage);
        console.error('Error:', error);
    }
}

async function initializeSubWorkCenterSelectBox(workCenterId) {
    const subWorkCenterSelectBox = $('#subWorkCenterSelect');

    const response = await axios.get(`/dpm/dtoconfigurator/api/controllers/WorkCenterController.php`, {
        params: { action: 'getSubWorkCentersOfWorkCenter', workCenterId: workCenterId },
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
}

function initializeSapMaterialDropdown() {
    $('#sapMaterialDropdown').dropdown({
        fullTextSearch: true,
        filterRemoteData: false,
        onChange: function(value, text, $selectedItem) {
            if (value) {
                // Hide manual entry when SAP material is selected
                hideManualEntryOption();

                // Get material data from the selected item
                const materialData = $selectedItem.data('material');

                if (materialData) {
                    // Show and populate description field
                    $('#materialDescription').val(materialData.description);
                    $('#descriptionField').show();
                    $('#descriptionHint').show();

                    setTimeout(() => {
                        $('#materialDescription').focus();
                    }, 100);
                }

                checkFormValidity();
            } else {
                // Disable submit button and hide description field
                // $('#defineMaterialButton').prop('disabled', true);
                $('#descriptionField').hide();
                $('#descriptionHint').hide();
                $('#materialDescription').val('');
            }
        }
    });

    // Search on input with 7 character minimum
    $('#sapMaterialDropdown input.search').on('input', function() {
        const originalSearchTerm = $(this).val().trim();
        let cleanSearchTerm = originalSearchTerm;

        if (originalSearchTerm.length <= 13) {
            cleanSearchTerm = originalSearchTerm.replace(/[^a-zA-Z0-9]/g, '');

            if (originalSearchTerm !== cleanSearchTerm) {
                $(this).val(cleanSearchTerm);
            }
        }

        $('.entered-material-text').text(cleanSearchTerm);

        clearTimeout(searchTimeout);

        // Clear and hide description field when user starts typing again
        $('#descriptionField').hide();
        $('#descriptionHint').hide();
        $('#materialDescription').val('');
        $('#materialId').val('');
        $('#defineMaterialButton').prop('disabled', true);
        hideManualEntryOption(); // Hide manual entry when searching

        if (cleanSearchTerm.length >= 7) {
            searchTimeout = setTimeout(() => searchMaterials(cleanSearchTerm), 300);
        } else {
            $('#materialMenu').empty();
            $('#sapMaterialDropdown').dropdown('refresh');
        }
    });
}

async function searchMaterials(searchTerm) {
    try {
        $('#loadingIndicator').show();
        $('#sapMaterialDropdown').addClass('loading');

        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/BaseController.php', {
            params: { action: 'getMaterialDetailWithLike', materialKeyword: searchTerm }
        });

        const materials = response.data;
        const menu = $('#materialMenu');
        menu.empty();

        if (!materials || materials.length === 0) {
            menu.append('<div class="message"><div>No materials found for "' + searchTerm + '"</div></div>');
            showManualEntryOption(searchTerm);
        } else {
            materials.forEach(material => {
                const displayText = material.material ?
                    `<b>${material.material}</b> - ${material.description}` :
                    material.description;

                const item = $(`<div class="item" data-value="${material.material}">${displayText}</div>`);
                item.data('material', material);
                menu.append(item);
            });
            // Hide manual entry option when materials are found
            hideManualEntryOption();
        }

        $('#sapMaterialDropdown').dropdown('refresh');

    } catch (error) {
        console.error('Search failed:', error);
        const menu = $('#materialMenu');
        menu.empty();
        menu.append('<div class="message"><div class="header">Search Error</div><p>Failed to search materials. Please try again.</p></div>');
        $('#sapMaterialDropdown').dropdown('refresh');
        // Show manual entry option on error as well
        showManualEntryOption(searchTerm);
    } finally {
        $('#loadingIndicator').hide();
        $('#sapMaterialDropdown').removeClass('loading');
    }
}

function showManualEntryOption(searchTerm) {
    $('#manualMaterialSection').show();
    $('#manualMaterialNumber').val(searchTerm);

    // Setup manual entry checkbox handler
    $('#allowManualEntry').off('change').on('change', function() {
        if ($(this).is(':checked')) {
            $('#manualMaterialField').show();
            $('#manualMaterialHint').show();
            $('#descriptionField').show();
            $('#sapMaterialDropdownField').hide();
            $('#materialDescription').val('').attr('placeholder', 'Enter material description');
            $('#manualMaterialSection .ui.message').hide();
            $('#manualMaterialSection .ui.image').hide();

            checkFormValidity();
        } else {
            $('#manualMaterialField').hide();
            $('#manualMaterialHint').hide();
            $('#descriptionField').hide();
            $('#sapMaterialDropdownField').show();
            $('#materialDescription').val('');
            $('#defineMaterialButton').prop('disabled', true);
            $('#manualMaterialSection .ui.message').show();
            $('#manualMaterialSection .ui.image').show();
        }
    });

    $('#manualMaterialNumber').off('input').on('input', function() {
        // const value = $(this).val().trim();
        // // Clean the input (remove non-alphanumeric characters)
        // const cleanValue = value.replace(/[^a-zA-Z0-9]/g, '');
        // if (value !== cleanValue) {
        //     $(this).val(cleanValue);
        // }
        checkFormValidity();
    });

    // Setup manual description handler
    $('#materialDescription').off('input').on('input', checkFormValidity);
}

function hideManualEntryOption() {
    $('#manualMaterialSection').hide();
    $('#manualMaterialField').hide();
    $('#manualMaterialHint').hide();
    $('#allowManualEntry').prop('checked', false);
    $('#manualMaterialNumber').val('');
}

function checkFormValidity() {
    const workcenterId = $('#workCenterSelect').val();
    const materialsHasSubKmats = ['4', '6', '11', '12', '19', '23', '45', '46'];
    const requiresSubWorkCenter = materialsHasSubKmats.includes(workcenterId);
    const subWorkCenterName = $('#subWorkCenterSelect').val();

    let isValid = false;

    if ($('#allowManualEntry').is(':checked')) {
        // Manual entry validation
        const manualMaterialNumber = $('#manualMaterialNumber').val().trim();
        const manualDescription = $('#materialDescription').val().trim();

        isValid = manualMaterialNumber.length >= 7 &&
            manualDescription.length > 0 &&
            workcenterId &&
            (!requiresSubWorkCenter || subWorkCenterName);
    }
    else {
        // Normal SAP material validation
        const materialId = $('#materialId').val();
        const materialDescription = $('#materialDescription').val().trim();

        isValid = materialId &&
            materialDescription &&
            workcenterId &&
            (!requiresSubWorkCenter || subWorkCenterName);

        $('#manualMaterialSection .ui.message').show();
        $('#manualMaterialSection .ui.image').show();
    }

    $('#defineMaterialButton').prop('disabled', !isValid);
}

// Handle form submission
$('#defineMaterialButton').on('click', async function() {
    let materialNo, materialDescription, isManualEntry = false;

    if ($('#allowManualEntry').is(':checked')) {
        // Manual entry
        materialNo = $('#manualMaterialNumber').val().trim();
        materialDescription = $('#materialDescription').val().trim();
        isManualEntry = true;
    } else {
        // SAP material
        const materialId = $('#materialId').val();
        const selectedItem = $('#sapMaterialDropdown').dropdown('get item', materialId);
        const materialData = selectedItem.data('material');
        materialNo = materialData ? materialData.material : '';
        materialDescription = $('#materialDescription').val().trim();
    }

    const workcenterId = $('#workCenterSelect').val();
    const subWorkCenterName = $('#subWorkCenterSelect').val();
    const isDevice = $('input[name="isDevice"]').is(':checked');

    $('#requiredFieldsError').hide();
    $('#successMessage').hide();

    const materialsHasSubKmats = ['4', '6', '11', '12', '19', '23', '45', '46'];
    const requiresSubWorkCenter = materialsHasSubKmats.includes(workcenterId);

    if (!materialNo || !materialDescription || !workcenterId || materialNo.length < 7) {
        $('#requiredFieldsError').show();
        return;
    }

    if (requiresSubWorkCenter && !subWorkCenterName) {
        $('#subWorkCenterSelectErrMsg').removeClass('hidden');
        $('#requiredFieldsError').show();
        return;
    }

    if (isManualEntry && materialNo.length < 11) {
        showErrorDialog('Only CTH and VTH codes are allowed before the SAP transfer.');
        return;
    }

    try {
        $(this).addClass('loading disabled');

        const formData = new FormData();
        formData.append('action', 'defineMaterial');
        formData.append('materialNo', materialNo);
        formData.append('description', materialDescription);
        formData.append('workCenterSelect', workcenterId);
        formData.append('subWorkCenterSelect', subWorkCenterName || '');
        formData.append('isDevice', isDevice);
        formData.append('isManualEntry', isManualEntry);
        formData.append('accessoryTypicalNumber', '');
        formData.append('accessoryParentKmat', '');
        formData.append('isUpdateMaterial', false); //New Material

        await axios.post('/dpm/dtoconfigurator/api/controllers/MaterialController.php', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        $('#successMessage').show();

        setTimeout(() => {
            resetForm();
        }, 2000);

    } catch (error) {
        console.error('Material definition failed:', error);
        $('#requiredFieldsError .content').html(`
            <div class="header" style="margin-bottom:0.6rem;">
                Error
            </div>
            Failed to define material: ${error.response?.data?.message || error.message}
        `);
        $('#requiredFieldsError').show();
    } finally {
        $(this).removeClass('loading disabled');
    }
});

function resetForm() {
    $('#sapMaterialDropdown').show();
    $('#sapMaterialDropdown').dropdown('clear');
    $('#workCenterSelect').dropdown('clear');
    $('#subWorkCenterSelect').dropdown('clear').addClass('disabled');
    $('#materialDescription').val('');
    $('#descriptionField').hide();
    $('#manualMaterialSection').hide();
    $('#manualMaterialField').hide();
    $('#allowManualEntry').prop('checked', false);
    $('#manualMaterialNumber').val('');
    $('input[name="isDevice"]').prop('checked', false);
    $('#descriptionHint').hide();
    $('#defineMaterialButton').prop('disabled', true);
    $('#requiredFieldsError').hide();
    $('#successMessage').hide();
}


// UPDATE MATERIAL TAB STARTS
function initializeExistingMaterialsDropdown() {
    $('#existingMaterialDropdown').dropdown({
        fullTextSearch: true,
        filterRemoteData: false,
        onChange: function(value, text, $selectedItem) {
            if (value) {
                // Get material data from the selected item
                const materialData = $selectedItem.data('material');

                if (materialData) {
                    populateUpdateForm(materialData);
                }
            } else {
                $('#updateMaterialDetails').hide();
            }
        }
    });

    // Search on input with 3 character minimum for existing materials
    $('#existingMaterialDropdown input.search').on('input', function() {
        const originalSearchTerm = $(this).val().trim();
        let cleanSearchTerm = originalSearchTerm;

        if (originalSearchTerm.length <= 13) {
            cleanSearchTerm = originalSearchTerm.replace(/[^a-zA-Z0-9]/g, '');

            if (originalSearchTerm !== cleanSearchTerm) {
                $(this).val(cleanSearchTerm);
            }
        }

        clearTimeout(updateSearchTimeout);

        $('#updateMaterialDetails').hide();
        $('#existingMaterialId').val('');

        if (cleanSearchTerm.length >= 3) {
            updateSearchTimeout = setTimeout(() => searchExistingMaterials(cleanSearchTerm), 300);
        } else {
            $('#existingMaterialMenu').empty();
            $('#existingMaterialDropdown').dropdown('refresh');
        }
    });
}

async function searchExistingMaterials(searchTerm) {
    try {
        $('#updateLoadingIndicator').show();
        $('#existingMaterialDropdown').addClass('loading');

        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/MaterialController.php', {
            params: { action: 'getMaterialsBySearch', keyword: searchTerm }
        });

        const materials = response.data;
        const menu = $('#existingMaterialMenu');
        menu.empty();

        if (!materials || materials.length === 0) {
            menu.append('<div class="message"><div>No existing materials found for "' + searchTerm + '"</div></div>');
        } else {
            materials.forEach(material => {
                const displayText = material.material_number ?
                    `<b>${material.material_number}</b> - ${material.description}` :
                    material.description;

                const item = $(`<div class="item" data-value="${material.id}">${displayText}</div>`);
                item.data('material', material);
                menu.append(item);
            });
        }

        $('#existingMaterialDropdown').dropdown('refresh');

    } catch (error) {
        console.error('Search existing materials failed:', error);
        const menu = $('#existingMaterialMenu');
        menu.empty();
        menu.append('<div class="message"><div class="header">Search Error</div><p>Failed to search existing materials. Please try again.</p></div>');
        $('#existingMaterialDropdown').dropdown('refresh');
    } finally {
        $('#updateLoadingIndicator').hide();
        $('#existingMaterialDropdown').removeClass('loading');
    }
}

async function populateUpdateForm(materialData) {
    try {
        $('#updateMaterialDetails').show();
        $('#updateMaterialDescription').val(materialData.description || '');
        $('#updateIsDevice').prop('checked', parseInt(materialData.is_device) === 1);

        // Check if work center is defined (not '0' or null/undefined)
        const hasWorkCenter = materialData.work_center_id && String(materialData.work_center_id) !== '0';

        if (hasWorkCenter) {
            // Initialize with the existing work center
            await initializeUpdateWorkCenterDropdown(materialData.work_center_id);
        } else {
            // Initialize empty work center dropdown (no work center defined)
            await initializeUpdateWorkCenterDropdown(null);
        }

        // Always check if sub work center is needed and handle accordingly
        const materialsHasSubKmats = ['4', '6', '11', '12', '19', '23', '45', '46'];
        const requiresSubWorkCenter = hasWorkCenter && materialsHasSubKmats.includes(String(materialData.work_center_id));

        if (requiresSubWorkCenter && materialData.sub_work_center_name) {
            // Initialize with the existing sub work center
            await initializeUpdateSubWorkCenterDropdown(materialData.work_center_id, materialData.sub_work_center_name);
            $('#updateSubWorkCenterSelectErrMsg').removeClass('hidden');
            $('#updateSubWorkCenterSelect').removeClass('disabled');
        } else if (requiresSubWorkCenter && !materialData.sub_work_center_name) {
            // Initialize empty sub work center dropdown (work center requires sub work center but material doesn't have one)
            await initializeUpdateSubWorkCenterDropdown(materialData.work_center_id);
            $('#updateSubWorkCenterSelectErrMsg').removeClass('hidden');
            $('#updateSubWorkCenterSelect').removeClass('disabled');
        } else {
            $('#updateSubWorkCenterSelectErrMsg').addClass('hidden');
            $('#updateSubWorkCenterSelect').dropdown('clear').addClass('disabled');
            $('#updateSubWorkCenterSelect').empty().append('<option value="">Search Sub Work Center</option>');
        }

    } catch (error) {
        console.error('Error populating update form:', error);
        $('#updateRequiredFieldsError').show();
    }
}

async function initializeUpdateWorkCenterDropdown(selectedWorkCenterId = null) {
    const workCenterSelectBox = $('#updateWorkCenterSelect');

    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/WorkCenterController.php', {
            params: { action: 'getWorkCenters' }
        });

        const workCenters = response.data;

        workCenterSelectBox.empty().append('<option value="">Search Work Center</option>');
        workCenters.forEach(workCenter => {
            // Only select if selectedWorkCenterId is not null and matches
            const isSelected = selectedWorkCenterId &&
            String(selectedWorkCenterId) !== '0' &&
            String(workCenter.id) === String(selectedWorkCenterId) ? 'selected' : '';
            workCenterSelectBox.append(
                `<option value="${workCenter.id}" ${isSelected}>
                    <b>${workCenter.work_center}</b> - ${workCenter.work_content}
                </option>`
            );
        });

        // Initialize dropdown with search functionality
        workCenterSelectBox.dropdown({
            apiSettings: {
                url: `/dpm/dtoconfigurator/api/controllers/WorkCenterController.php?action=getWorkCentersBySearch&keyword={query}`,
                cache: false,
                onResponse: function(response) {
                    const workCenters = Array.isArray(response) ? response : Object.values(response);
                    let results = [];

                    if (workCenters.length === 0) {
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
            minCharacters: 1,
            fullTextSearch: true,
            forceSelection: false,
            selectOnKeydown: false,
            onChange: function(value) {
                const materialsHasSubKmats = ['4', '6', '11', '12', '19', '23', '45', '46'];

                if (materialsHasSubKmats.includes(value)) {
                    initializeUpdateSubWorkCenterDropdown(value);
                    $('#updateSubWorkCenterSelectErrMsg').removeClass('hidden');
                    $('#updateSubWorkCenterSelect').dropdown('clear').removeClass('disabled');
                } else {
                    $('#updateSubWorkCenterSelectErrMsg').addClass('hidden');
                    $('#updateSubWorkCenterSelect').dropdown('clear').addClass('disabled');
                }
            }
        });

        if (selectedWorkCenterId && String(selectedWorkCenterId) !== '0') {
            workCenterSelectBox.dropdown('set selected', selectedWorkCenterId);
        } else {
            workCenterSelectBox.dropdown('clear');
        }

    } catch (error) {
        console.error('Error initializing update work center dropdown:', error);
    }
}

async function initializeUpdateSubWorkCenterDropdown(workCenterId, selectedSubWorkCenter = null) {
    const subWorkCenterSelectBox = $('#updateSubWorkCenterSelect');

    try {
        const response = await axios.get(`/dpm/dtoconfigurator/api/controllers/WorkCenterController.php`, {
            params: { action: 'getSubWorkCentersOfWorkCenter', workCenterId: workCenterId },
            headers: { "Content-Type": "multipart/form-data" }
        });

        const subWorkCenters = response.data;
        subWorkCenterSelectBox.empty().append('<option value="">Search Sub Work Center</option>');

        subWorkCenters.forEach(subWorkCenter => {
            const isSelected = selectedSubWorkCenter && subWorkCenter.sub_kmat_name === selectedSubWorkCenter ? 'selected' : '';
            subWorkCenterSelectBox.append(
                `<option value="${subWorkCenter.sub_kmat_name}" ${isSelected}>
                      <b>${subWorkCenter.sub_kmat}</b> - ${subWorkCenter.sub_kmat_name}
                  </option>`
            );
        });

        subWorkCenterSelectBox.dropdown({
            search: true,
            clearable: true,
            allowAdditions: false,
            onChange: function() {
                $('#updateSubWorkCenterSelectErrMsg').addClass('hidden');
            }
        });

        if (selectedSubWorkCenter) {
            subWorkCenterSelectBox.dropdown('set selected', selectedSubWorkCenter);
            $('#updateSubWorkCenterSelect').parent().removeClass('disabled');
        }

    } catch (error) {
        console.error('Error initializing update sub work center dropdown:', error);
    }
}


$('#updateMaterialButton').on('click', async function() {
    const existingMaterialId = $('#existingMaterialId').val();
    const selectedItem = $('#existingMaterialDropdown').dropdown('get item', existingMaterialId);
    const materialData = selectedItem.data('material');
    const materialNo = materialData ? materialData.material_number : '';
    const materialDescription = $('#updateMaterialDescription').val().trim();
    const workcenterId = $('#updateWorkCenterSelect').val();
    const subWorkCenterName = $('#updateSubWorkCenterSelect').val();
    const isDevice = $('#updateIsDevice').is(':checked');

    // Hide previous messages
    $('#updateRequiredFieldsError').hide();
    $('#updateSuccessMessage').hide();

    // Validation
    const materialsHasSubKmats = ['4', '6', '11', '12', '19', '23', '45', '46'];
    const requiresSubWorkCenter = materialsHasSubKmats.includes(workcenterId);

    if (!existingMaterialId || !materialNo || !materialDescription || !workcenterId || materialNo.length < 7) {
        $('#updateRequiredFieldsError').show();
        return;
    }

    if (requiresSubWorkCenter && !subWorkCenterName) {
        $('#updateSubWorkCenterSelectErrMsg').removeClass('hidden');
        $('#updateRequiredFieldsError').show();
        return;
    }

    try {
        $(this).addClass('loading disabled');

        const formData = new FormData();
        formData.append('action', 'defineMaterial'); // Same action as new material
        formData.append('materialNo', materialNo);
        formData.append('description', materialDescription);
        formData.append('workCenterSelect', workcenterId || '');
        formData.append('subWorkCenterSelect', subWorkCenterName || '');
        formData.append('isDevice', isDevice);
        formData.append('isManualEntry', false); // Update is never manual entry
        formData.append('accessoryTypicalNumber', '');
        formData.append('accessoryParentKmat', '');
        formData.append('isUpdateMaterial', true);

        await axios.post('/dpm/dtoconfigurator/api/controllers/MaterialController.php', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        $('#updateSuccessMessage').show();

        setTimeout(() => {
            resetUpdateForm();
        }, 2000);

    } catch (error) {
        console.error('Material update failed:', error);

        $('#updateRequiredFieldsError .content').html(`
            <div class="header" style="margin-bottom:0.6rem;">
                Error
            </div>
            Failed to update material: ${error.response?.data?.message || error.message}
        `);
        $('#updateRequiredFieldsError').show();

    } finally {
        $(this).removeClass('loading disabled');
    }
});

function resetUpdateForm() {
    $('#existingMaterialDropdown').dropdown('clear');
    $('#updateWorkCenterSelect').dropdown('clear');
    $('#updateSubWorkCenterSelect').dropdown('clear').addClass('disabled');
    $('#updateMaterialDescription').val('');
    $('#updateMaterialDetails').hide();
    $('#updateIsDevice').prop('checked', false);
    $('#updateRequiredFieldsError').hide();
    $('#updateSuccessMessage').hide();
    $('#updateSubWorkCenterSelectErrMsg').addClass('hidden');
    $('#updateSubWorkCenterSelect').empty().append('<option value="">Search Sub Work Center</option>');

    // Clear the existing material dropdown menu items
    $('#existingMaterialMenu').empty();
    $('#existingMaterialDropdown').dropdown('refresh');

    // Also clear the hidden input value
    $('#existingMaterialId').val('');
}