$(document).ready(async function() {
    $('#banfomatPoolPage .loader').hide();
    $('#banfomatPoolContainer').transition('zoom');
    $('.ui.checkbox').checkbox();

    prepareChooseTechnicalImageSelect();
    await renderBanfomatMaterialsDataTable();
});

async function renderBanfomatMaterialsDataTable() {
    const tableId = '#banfomatPoolDataTable';
    const data = await fetchBanfomatMaterialDetails();

    hideElement('#banfomatPoolListTableContainer');

    prepareDropdownFilters(data);

    if ($.fn.DataTable.isDataTable(tableId))
        $(tableId).DataTable().destroy();

    if (data.length === 0)
        $('#banfomatPoolListTableContainer').hide()
    else {
        const banfomatPoolListTable = $(tableId).DataTable({
            data: data,
            pageLength: 25,
            autoWidth: false,
            order: [],
            fixedHeader:true,
            columnDefs: [
                { width: '10%', targets: [0] },
                { width: '25%', targets: [1] },
                { width: '5%', targets: [2] },
                { width: '8%', targets: [3] },
                { width: '8%', targets: [4] },
                { width: '8%', targets: [5] },
                { width: '25%', targets: [6] },
                { width: '8%', targets: [7] },
                { width: '8%', targets: [8] },
            ],
            columns: [
                {   data: 'material',
                    render: (data) => `<a target="_blank" href="/materialviewer/?material=${data}" 
                                                       data-tooltip="Navigate to Material Viewer" data-position="top center" data-variation="inverted">
                                                       ${data}
                                                    </a>`,
                    className: 'center aligned'
                },
                { data: 'description', className: 'center aligned' },
                { data: 'metal', className: 'center aligned' },
                {
                    data: 'surface_area',
                    render: function(data) {
                        if (!data || parseFloat(data) === 0)
                            return '';
                        return `<span style="font-weight:bold;">${parseFloat(data).toString()} m²</span>`;
                    },
                    className: 'center aligned',
                },
                { data: 'coated_type', className: 'center aligned' },
                { data: 'coated_part', className: 'center aligned' },
                { data: 'details', className: 'center aligned' },
                { data: 'production_location', className: 'center aligned' },
                {   data: 'image_file_name',
                    render: function(data) {
                        if (data)
                            return `<img src="/dpm/dtoconfigurator/partials/getNoteImages.php?type=4&file=${data}" class="enlargeable-image" style="width:50px;height:40px;margin:0 auto;cursor:pointer;">`;
                        return '';
                    },
                    className: 'center aligned'
                },
                {
                    render: (data, type, row) =>
                        `<button class="ui icon button circular mini teal" data-tooltip="Düzenle" data-position="top center" onclick="openEditBanfomatPoolRowModal('${row.id}')">
                            <i class="pencil alternate large icon"></i>
                        </button>`,
                    className: 'center aligned'
                }
            ],
            destroy: true
        });

        //Search customization
        const $searchInput = $('.dt-search input[type="search"]');
        $searchInput.attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');

        $('#banfomatPoolListTableContainer').transition('zoom', function() {
            requestAnimationFrame(() => {
                banfomatPoolListTable.fixedHeader.adjust();
            });
        });

        banfomatPoolListTable.draw();
    }
}

async function fetchBanfomatMaterialDetails() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/BanfomatController.php', {
            params: { action: 'getBanfomatPool' }
        });

        return response.data;
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    }
}

$('#addMaterialToBanfomatPoolBtn').on('click', async function () {
    $('#addMaterialToBanfomatPoolModal .ui.dropdown').dropdown({
        clearable: true,
        allowMultiple: true,
        allowAdditions: false,
        forceSelection: false,
        selectOnKeydown:false,
        showOnFocus: false
    });

    await initializeBanfomatMaterialSelect('add');

    $('#addMaterialToBanfomatPoolModal').modal({ closable: false }).modal('show');
    $('#addMaterialToBanfomatPoolModal').draggable({ handle: '.header', containment: 'window' });
});

async function initializeBanfomatMaterialSelect(type) {
    let materialSelectBox;
    if (type === 'add')
        materialSelectBox = $('#select-material-number');
    else if (type === 'edit')
        materialSelectBox = $('#edit-select-material-number');

    materialSelectBox.dropdown({
        apiSettings: {
            url: `/dpm/dtoconfigurator/api/controllers/BanfomatController.php?action=getMaterialsFromSapBySearch&keyword={query}`,
            cache: false,
            onResponse: function(response) {
                const menuElement = document.querySelector('.select-material-number .menu.transition.visible');
                const materials = Array.isArray(response) ? response : Object.values(response);
                let results = [];

                if (materials.length === 0) {
                    if (menuElement)
                        menuElement.innerHTML = '';
                    return { results };
                }

                results = materials.map(material => ({
                    name: `<b>${material.Material}</b> - ${material.Description}`,
                    value: material.Material
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
        fullTextSearch: true,
        forceSelection: false,
        selectOnKeydown:false
    });
}

$('#saveMaterialToBanfomatPoolButton').on('click', async function (e) {
    e.stopPropagation();
    $(this).addClass('loading disabled');

    const formElement = document.getElementById('addMaterialToBanfomatPoolForm');
    const formData = new FormData(formElement);
    formData.append('action', 'saveMaterialToBanfomatPool');

    const materialNo =  $('#select-material-number').dropdown('get value');
    const metal = $('#select-metal').dropdown('get value');
    const surfaceArea = formData.get('input-surface-area');
    const coatedType = $('#select-coated-type').dropdown('get value');
    const coatedPart = $('#select-coated-part').dropdown('get value');
    const details = formData.get('input-details');
    const imageFileName = formData.get('image-input-file');
    const checkboxWontBeCoated = $('input[name="checkbox-wont-be-coated"]').is(':checked');

    if (surfaceArea && parseFloat(surfaceArea) > 1.0) {
        showErrorDialog('Surface area can not be more than 1.0 m2');
        $(this).removeClass('loading disabled');
        return;
    }

    if (!materialNo || !metal) {
        $('#formRequiredFieldsError').show();
        $(this).removeClass('loading disabled');
        return;
    }

    formData.append('materialNo', materialNo);
    formData.append('metal', metal);
    formData.append('surfaceArea', surfaceArea);
    formData.append('coatedType', coatedType);
    formData.append('coatedPart', coatedPart);
    formData.append('details', details);
    formData.append('imageFileName', imageFileName);
    formData.append('checkBoxWontBeCoated', checkboxWontBeCoated);

    try {
        await axios.post('/dpm/dtoconfigurator/api/controllers/BanfomatController.php?', formData, { headers: { 'Content-Type': 'multipart/form-data' }} );

        showSuccessDialog('Malzeme banfomat havuzuna başarıyla kaydedildi.').then(() => {
            $('#addMaterialToBanfomatPoolModal').modal('hide');
            resetAddMaterialToBanfomatPoolForm();
            $('#banfomatPoolListTableContainer').removeClass('transition hidden');
            renderBanfomatMaterialsDataTable();
        });

    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(errorMessage);
    }
    finally {
        $(this).removeClass('loading disabled');
    }
});

function resetAddMaterialToBanfomatPoolForm() {
    $('#select-material-number').dropdown('clear');
    $('#input-surface-area').val('');
    $('#select-metal').dropdown('clear');
    $('#select-coated-type').dropdown('clear');
    $('#select-coated-part').dropdown('clear');
    $('#input-details').val('');
    $('#addTechnicalImageInput input[type="file"]').val('');
    $('#addTechnicalImageInput input[type="text"]').val('');
    $('input[name="checkbox-wont-be-coated"]').prop('checked', false);
}

async function openEditBanfomatPoolRowModal(banfomatRowId) {
    $('#editFormRequiredFieldsError').hide();
    $('#editBanfomatPoolModal .ui.dropdown').dropdown({
        clearable: true,
        allowMultiple: true,
        allowAdditions: false,
        forceSelection: false,
        selectOnKeydown:false,
        showOnFocus: false
    });

    await initializeBanfomatMaterialSelect('edit');

    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/BanfomatController.php', {
            params: { action: 'getBanfomatPoolDetailsById', banfomatRowId: banfomatRowId }
        });

        const data = response.data;

        const materialVal = data.material ? `<b>${data.material}</b> - ${data.description}` : '';

        $('#edit-banfomat-id').val(banfomatRowId);
        $('#edit-select-material-number').dropdown('set selected', data.material).dropdown('set text', materialVal);
        $('#edit-select-material-number .menu').html(`<div class="item active selected" data-value="${data.material}">${materialVal}</div>`);

        $('#edit-select-metal').dropdown('set selected', data.metal);

        $('#edit-input-surface-area').val(data.surface_area);
        $('#edit-select-coated-type').dropdown('set selected', data.coated_type);

        $('#edit-select-coated-part').dropdown('set selected', data.coated_part);
        $('#edit-input-details').val(data.details);
        $('#edit-checkbox-wont-be-coated').prop('checked', data.wont_be_coated === "1");

        if (data.image_file_name) {
            $('#edit-existing-image-name').val(data.image_file_name);
            $('#edit-image-input').val(data.image_file_name); // Display image name in the browse box
            $('#technicalImage').html(`
                <img src="/dpm/dtoconfigurator/partials/getNoteImages.php?type=4&file=${data.image_file_name}" class="enlargeable-image"
                     style="width:200px;height:110px;margin:0 auto;cursor:pointer;">
            `);
            $('#remove-edit-image-button').show();
        } else {
            $('#edit-existing-image-name').val('');
            $('#edit-image-input').val('');
            $('#technicalImage').html('');
        }

        $('#edit-image-file').on('change', function () {
            if (this.files.length > 0) {
                $('#edit-image-input').val(this.files[0].name); // Show file name in input box
                $('#edit-image-changed').val('1'); // Mark as changed
                $('#technicalImage').html('');
            } else {
                $('#edit-image-changed').val('0'); // No change
            }
        });

        // Show modal
        $('#editBanfomatPoolModal').modal({ allowMultiple: true, closable: false }).modal('show');
        $('#editBanfomatPoolModal').draggable({ handle: '.header', containment: 'window' });

    } catch (error) {
        showErrorDialog("Error fetching Banfomat data. Please try again.");
    }
}

$('#updateBanfomatPoolRowButton').on('click', async function (e) {
    e.stopPropagation();
    $(this).addClass('loading disabled');

    const formElement = document.getElementById('editBanfomatPoolForm');
    const formData = new FormData(formElement);
    formData.append('action', 'updateBanfomatPoolRow');

    const id = $('#edit-banfomat-id').val();
    const materialNo =  $('#edit-select-material-number .menu .selected').data('value');
    const metal = $('#edit-select-metal').dropdown('get value');
    const surfaceArea = formData.get('edit-input-surface-area');
    const coatedType = $('#edit-select-coated-type').dropdown('get value');
    const coatedPart = $('#edit-select-coated-part').dropdown('get value');
    const details = formData.get('edit-input-details');
    const checkboxWontBeCoated = $('input[name="edit-checkbox-wont-be-coated"]').is(':checked');

    if (surfaceArea && parseFloat(surfaceArea) > 1.0) {
        showErrorDialog('Surface area can not be more than 1.0 m2');
        $(this).removeClass('loading disabled');
        return;
    }

    if (!materialNo || !metal) {
        $('#editFormRequiredFieldsError').show();
        $(this).removeClass('loading disabled');
        return;
    }

    // Append main data
    formData.append('id', id);
    formData.append('materialNo', materialNo);
    formData.append('metal', metal);
    formData.append('surfaceArea', surfaceArea);
    formData.append('coatedType', coatedType);
    formData.append('coatedPart', coatedPart);
    formData.append('details', details);
    formData.append('checkBoxWontBeCoated', checkboxWontBeCoated);

    // Handle image upload tracking
    const existingImageName = $('#edit-existing-image-name').val();
    let imageChanged = $('#edit-image-changed').val();
    const imageFileInput = $('#edit-image-file'); // Get the file input element

    // Ensure imageFileInput exists before accessing files
    const imageFile = imageFileInput.length > 0 && imageFileInput[0].files.length > 0
        ? imageFileInput[0].files[0]
        : null;

    // this means the image is deleted
    if (existingImageName && !imageFile) {
        imageChanged = 1;
    }

    formData.append('existingImageName', existingImageName);
    formData.append('imageChanged', imageChanged);

    // Only append the file if a new one is selected
    if (imageChanged === "1" && imageFile) {
        formData.append('edit-image', imageFile);
    }

    try {
        await axios.post('/dpm/dtoconfigurator/api/controllers/BanfomatController.php', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        showSuccessDialog('Malzeme banfomat bilgisi başarıyla güncellendi.').then(() => {
            $('#editBanfomatPoolModal').modal('hide');
            renderBanfomatMaterialsDataTable();
            $('#banfomatPoolListTableContainer').removeClass('transition hidden');
        });

    } catch (error) {
        showErrorDialog('Failed to update entry. Try again.');
    } finally {
        $(this).removeClass('loading disabled');
    }
});


$('#deleteBanfomatPoolRowButton').on('click', async function (e) {
    e.stopPropagation();
    const id = $('#edit-banfomat-id').val();

    showConfirmationDialog({
        title: 'Remove change?',
        htmlContent: 'Are you sure to delete this row?',
        confirmButtonText: 'Yes, delete it!',
        confirmButtonColor: "#d33",
        onConfirm: async function () {
            try {
                await axios.post('/dpm/dtoconfigurator/api/controllers/BanfomatController.php?',
                    {
                        action: 'deleteBanfomatPoolRow',
                        id: id
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                showSuccessDialog('Malzeme banfomat bilgisi başarıyla silindi.').then(() => {
                    $('#editBanfomatPoolModal').modal('hide');
                    renderBanfomatMaterialsDataTable();
                    $('#banfomatPoolListTableContainer').removeClass('transition hidden');
                });
            } catch (error) {
                showErrorDialog('Failed to delete entry. Try again.');
            }
        },
    });
});

function prepareChooseTechnicalImageSelect() {
    $('input:text').click(function () {
        $(this).siblings("input:file").click();
    });

    $('#choose-image-input-button').click(function () {
        $(this).siblings("input:file").click();
    });

    $('#edit-choose-image-input-button').click(function () {
        $(this).siblings("input:file").click();
    });

    $('input:file').on('change', function (e) {
        let fileName = e.target.files[0]?.name || '';
        $(this).siblings('input:text').val(fileName);
        $('#remove-image-button').show();
        $('#remove-edit-image-button').show();
    });

    $('#remove-image-button').click(function () {
        $('#addTechnicalImageInput input[type="file"]').val('');
        $('#addTechnicalImageInput input[type="text"]').val('');
        $(this).hide();
    });

    $('#remove-edit-image-button').click(function () {
        $('#editTechnicalImageInput input[type="file"]').val('');
        $('#editTechnicalImageInput input[type="text"]').val('');
        $('#technicalImage').html('');

        $(this).hide();
    });

}

//ENLARGE IMG
$(document).on('click', '.enlargeable-image', function () {
    const imageUrl = $(this).attr('src'); // Get the source of the clicked image
    $('#enlargedImage').attr('src', imageUrl); // Set the source of the modal image
    $('#imageEnlargeModal').modal({
        centered: false, // Disable automatic vertical centering
        allowMultiple: true,
        dimmerSettings: {closable: true} // Ensure the dimmer can be clicked to close the modal
    }).modal('show');
});

//checkbox on change
$(document).on("change", "#checkbox-wont-be-coated", function () {
    if ($(this).is(":checked")) {
        $("#input-details").val("Banfomat Dışı");
    } else {
        $("#input-details").val("");
    }
});

$(document).on("change", "#edit-checkbox-wont-be-coated", function () {
    if ($(this).is(":checked")) {
        $("#edit-input-details").val("Banfomat Dışı");
    } else {
        $("#edit-input-details").val("");
    }
});

function prepareDropdownFilters(data) {
    // Extract unique values from data in a single loop
    const uniqueValues = {
        metal: new Set(),
        coatedType: new Set(),
        coatedPart: new Set(),
        productionLocation: new Set()
    };

    // Single loop to populate Sets
    data.forEach(item => {
        if (item.metal) uniqueValues.metal.add(item.metal);
        if (item.coated_type) uniqueValues.coatedType.add(item.coated_type);
        if (item.coated_part) uniqueValues.coatedPart.add(item.coated_part);
        if (item.production_location) uniqueValues.productionLocation.add(item.production_location);
    });

    // Convert Sets to sorted arrays
    createDropdownElement([...uniqueValues.metal].sort(), '#metalFilterDropdown', 2);
    createDropdownElement([...uniqueValues.coatedType].sort(), '#coatedTypeFilterDropdown', 4);
    createDropdownElement([...uniqueValues.coatedPart].sort(), '#coatedPartFilterDropdown', 5);
    createDropdownElement([...uniqueValues.productionLocation].sort(), '#productionLocationFilterDropdown', 7);
}

// ✅ Generic function to create Semantic UI dropdown filters
function createDropdownElement(valuesArray, dropdownId, columnIndex) {
    const $dropdown = $(dropdownId);
    $dropdown.empty().append('<option value="">Filtre...</option>');

    valuesArray.forEach(value => {
        if (value) {
            $dropdown.append(`<option value="${escapeHtml(value)}">${escapeHtml(value)}</option>`);
        }
    });

    $dropdown.dropdown('destroy').dropdown({
        clearable: true,
        placeholder: 'Filter...',
        onChange: function(selectedValues) {
            selectedValues = selectedValues || [];
            filterDataTableByColumn(selectedValues, columnIndex);
        }
    });
}

// ✅ Generic function to filter DataTable by column
function filterDataTableByColumn(selectedValues, columnIndex) {
    if (!$.fn.DataTable.isDataTable('#banfomatPoolDataTable')) {
        console.error('DataTable is not initialized yet.');
        return;
    }

    const table = $('#banfomatPoolDataTable').DataTable();

    if (selectedValues.length === 0) {
        table.column(columnIndex).search('').draw(false);
    } else {
        const escapeRegex = (value) => value.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        const regexPattern = selectedValues.map(value => `^${escapeRegex(value)}$`).join('|');
        table.column(columnIndex).search(regexPattern, true, false).draw(false);
    }
}

