let SpareWdaDtosOfProject = [];
let SparePartDtosOfProject = [];
async function getSpareDtosOfNachbau() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/NachbauController.php', {
            params: {
                action: 'getSpareDtosOfNachbau',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                accessoryTypicalNo: NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryTypicalCode']
            }
        });
        SpareWdaDtosOfProject = response.data.spareWdaDtos;
        SparePartDtosOfProject = response.data.sparePartDtos;
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    }
}

async function checkSpareWdaDtoCountsInSameTypical(wdaParentKmat, spareTypical, spareDto, spareDtoDesc) {
    //Çok nadirde olsa (1 siparişte gördüm) bazen aynı tipiğe 2 spare dtosu girilme durumu olabiliyor.
    const matchingDtos = SpareWdaDtosOfProject.filter(item => item.typical_no === spareTypical);

    if (matchingDtos.length > 1) {
        await showDtoSelectionModal(wdaParentKmat, spareTypical, matchingDtos);
    } else if (matchingDtos.length === 1) {
        await openSpareWorkModal(wdaParentKmat, spareTypical, spareDto, spareDtoDesc);
    } else {
        console.error("No DTOs found for typical:", spareTypical);
    }
}

async function showDtoSelectionModal(wdaParentKmat, spareTypical, matchingDtos) {
    const radioButtonItems = matchingDtos.map((dto, index) => `
        <div class="field">
            <div class="ui radio checkbox">
                <input type="radio" name="dto-option" value="${dto.dto_number}" id="radio-dto-${index}">
                <label for="radio-dto-${index}" style="cursor:pointer;">
                    <span style="font-weight: bold; color: #21ba45;">${dto.dto_number}</span> - 
                    <span style="font-weight: bold; color: #f2711c;">${formatDescription(dto.description, 4)}</span>
                </label>
            </div>
        </div>
    `).join('');

    $('#dtoSelectionModal .radioButtonItems').html(radioButtonItems);
    $('#dtoSelectionModal .spareTypicalNo').html(spareTypical);

    $('#dtoSelectionModal .ui.radio.checkbox').checkbox();

    $('#dtoSelectionModal').modal({
        onApprove: async function () {
            const selectedDtoNumber = $('input[name="dto-option"]:checked').val();
            const selectedDto = matchingDtos.find(dto => dto.dto_number === selectedDtoNumber);

            if (selectedDto) {
                await openSpareWorkModal(
                    wdaParentKmat,
                    spareTypical,
                    selectedDto.dto_number,
                    escapeString(selectedDto.description)
                );
            } else {
                return false;
            }
        }
    }).modal('show');
}


let globalWdaParentKmat = '';
let globalSpareTypical = '';
let globalSpareDto = '';
let globalSpareDtoDesc = '';

async function openSpareWorkModal(wdaParentKmat, spareTypical, spareDto, spareDtoDesc) {
    $('#spareDtoModalContent #available-materials').html('');
    $('#spareDtoModalContent #selected-materials').html('');
    $('#spare-wda-quantity-input').val(1);

    globalWdaParentKmat = wdaParentKmat;
    globalSpareTypical = spareTypical;
    globalSpareDto = spareDto;
    globalSpareDtoDesc = spareDtoDesc;

    const accessoryTypicalNumber = NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryTypicalCode'];
    $('#spareDtoModalHeader').html(`<h3>Spare DTO Work Menu</h3> <h4 style="margin-top: 0">${spareDto} | ${formatDescription(spareDtoDesc, 3)} | ${spareTypical}</h4>`);
    $('#spareDtoModalContent .spare-wda-note').html(`Selected lists will be added to the accessory typical (<b>${accessoryTypicalNumber}</b>) of the order.`);

    let availableMaterials = [];
    let selectedMaterials = [];

    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/NachbauController.php', {
            params: {
                action: 'getSubMaterialListsOfWdaAndSpareChanges',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                kmat: wdaParentKmat,
                typicalNo: spareTypical
            }
        });

        if (response.status === 200) {
            availableMaterials = response.data;

            try {
                const response = await axios.get('/dpm/dtoconfigurator/api/controllers/ProjectController.php', {
                    params: {
                        action: 'getSpareProjectsOfTypical',
                        projectNo: getUrlParam('project-no'),
                        nachbauNo: getUrlParam('nachbau-no'),
                        dtoNumber: spareDto,
                        typicalNo: spareTypical,
                        accessoryTypicalNo: NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryTypicalCode']
                    }
                });

                // Filter out the selected materials from the available materials list
                selectedMaterials = Array.isArray(response.data) ? response.data : [];
                availableMaterials = availableMaterials.filter(availMaterial => {
                    return !selectedMaterials.some(selectedMaterial =>
                        selectedMaterial.material_added_number === availMaterial.material_added_number.replace(/^00/, '')
                    );
                });

                // Render available materials
                let availableMaterialListHtml = availableMaterials.map(material =>
                    `<a class="item"
                         data-id="${material.material_added_number}" data-desc="${material.material_added_description}" data-dto="${spareDto}"
                         data-typical="${spareTypical}" data-kmat="${wdaParentKmat}">
                        <span><b>${material.material_added_number}</b> - ${material.material_added_description}</span>
                        <span class="ui label grey basic" data-tooltip="Unit" data-position="top center">
                            <span class="material-unit">${material.unit}</span>
                        </span>
                        <span class="ui label grey basic" data-tooltip="Quantity" data-position="top center">
                            <span class="material-quantity">${parseFloat(material.quantity).toString().replace(/\\.000$/, '')}</span>
                        </span>
                    </a>`
                ).join('');
                $('#spareDtoModalContent #available-materials').html(availableMaterialListHtml);

                // Render selected materials
                let selectedMaterialListHtml = selectedMaterials.map(material =>
                    `<a class="item"
                         data-id="${material.material_added_number}" data-desc="${material.material_added_description}" data-dto="${spareDto}"
                         data-typical="${material.spare_typical_number}" data-kmat="${material.spare_parent_kmat}"
                         style="background-color:palegreen;">
                        <span><b>${material.material_added_number}</b> - ${material.material_added_description}</span>
                        <span class="ui label grey basic" data-tooltip="Unit" data-position="top center">
                            <span class="material-unit">${material.release_unit}</span>
                        </span>
                        <span class="ui label grey basic" data-tooltip="Quantity" data-position="top center">
                            <span class="material-quantity">${parseFloat(material.release_quantity).toString().replace(/\\.000$/, '')}</span>
                        </span>
                    </a>`
                ).join('');
                $('#spareDtoModalContent #selected-materials').html(selectedMaterialListHtml);

                updateMaterialCounts();

                $('#removeSpareWorksButton').data('dto', spareDto).data('typical', spareTypical);

                $('#spareDtoModal').modal('show');

            } catch (error) {
                const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                showErrorDialog(`Error: ${errorMessage}`);
            }
        } else {
            showErrorDialog(response.message);
        }
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    }
}


// Move the selected materials between lists on button click
$('#move-material').on('click', function () {
    let selectedItemsLeft = $('#spareDtoModalContent #available-materials a.active');  // Get all active items from available materials (left)
    let selectedItemsRight = $('#spareDtoModalContent #selected-materials a.active');  // Get all active items from selected materials (right)

    // Move items from available to selected
    if (selectedItemsLeft.length > 0) {
        selectedItemsLeft.each(function () {
            let selectedItem = $(this).clone(); // Clone the selected item
            $('#spareDtoModalContent #selected-materials').append(selectedItem.removeClass('active').css('background-color', 'aquamarine'));  // Move to right and change background color
            $(this).remove();  // Remove from left
        });

        // Update material counts after moving the items
        updateMaterialCounts();
    }
    // Move items from selected back to available
    else if (selectedItemsRight.length > 0) {
        selectedItemsRight.each(function () {
            let selectedItem = $(this).clone();  // Clone the selected item
            $('#spareDtoModalContent #available-materials').append(selectedItem.removeClass('active').css('background-color', 'aquamarine'));  // Move to left and change background color
            $(this).remove();  // Remove from right
        });

        // Update material counts after moving the items
        updateMaterialCounts();
    }
    // If no items are selected, show a warning
    else {
        fireToastr('warning', 'Lütfen malzeme listesi seçiniz.');  // Show warning if no items are selected
    }
});

function updateMaterialCounts() {
    $('#spareDtoModalContent #available-materials-count').text($('#spareDtoModalContent #available-materials a').length);
    $('#spareDtoModalContent #selected-materials-count').text($('#spareDtoModalContent #selected-materials a').length);
}

$('#spareDtoModalContent #available-materials').on('click', 'a', function () {
    $(this).toggleClass('active');  // Toggle active state for multi-selection
});

// Allow multi-selection for selected materials (right side)
$('#spareDtoModalContent #selected-materials').on('click', 'a', function () {
    $(this).toggleClass('active');  // Toggle active state for multi-selection
});


$('#saveSpareWorksButton').on('click', async function () {
    const quantityMultiplier = parseInt($('#spare-wda-quantity-input').val());
    let selectedMaterials = [];

    $('#spareDtoModalContent #selected-materials a').each(function () {
        // Collect the material data
        let material_added_number = $(this).data('id');
        let material_added_description = $(this).data('desc');
        let material_quantity = $(this).find('.material-quantity').text();
        let material_unit = $(this).find('.material-unit').text();
        let spare_dto = $(this).data('dto');
        let spare_typical_no = $(this).data('typical');
        let spare_parent_kmat = $(this).data('kmat');

        selectedMaterials.push({
            material_added_number: material_added_number,
            material_added_description: material_added_description,
            material_quantity: parseFloat(material_quantity) * quantityMultiplier,
            material_unit: material_unit,
            spare_dto: spare_dto,
            spare_typical_number: spare_typical_no,
            spare_parent_kmat: spare_parent_kmat
        });
    });

    if (selectedMaterials.length === 0) {
        showErrorDialog('There is no selected materials to send as an accessory!');
        return;
    }

    try {
        $(this).addClass('loading disabled');

        await axios.post('/dpm/dtoconfigurator/api/controllers/ProjectController.php?',
            {
                action: 'saveSpareListsAsAccessory',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                materials: selectedMaterials,
                accessoryTypicalNo: NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryTypicalCode'],
                accessoryParentKmat: NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryParentKmat'],
                accessoryPanelNo: NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryPanelNo'],
                isRevisionNachbau: currentlyWorkingUser.isRevisionNachbau
            },
            { headers: { 'Content-Type': 'multipart/form-data' }}
        );

        showSuccessDialog('Spare DTO works are saved successfully.').then(async () => {
            showLoader('#orderSummaryV2');
            hideElement('#orderSummaryV2Container');

            await openSpareWorkModal(globalWdaParentKmat, globalSpareTypical, globalSpareDto, globalSpareDtoDesc)

            await getOrderSummaryV2();

            hideLoader('#orderSummaryV2');
            showElement('#orderSummaryV2Container');
        });
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    } finally {
        $(this).removeClass('loading disabled');
    }
});


$('#removeSpareWorksButton').on('click', async function () {
    const spareDto = $(this).data('dto');
    const spareTypical = $(this).data('typical');

    showConfirmationDialog({
        title: 'Remove change?',
        htmlContent: 'Do you want to remove all spare changes for this DTO?',
        confirmButtonText: 'Yes, delete it!',
        confirmButtonColor: "#d33",
        onConfirm: async function () {

            try {
                $(this).addClass('loading disabled');

                await axios.post('/dpm/dtoconfigurator/api/controllers/ProjectController.php?',
                    {
                        action: 'removeSpareWorks',
                        projectNo: getUrlParam('project-no'),
                        nachbauNo: getUrlParam('nachbau-no'),
                        spareDto: spareDto,
                        spareTypical: spareTypical
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                showSuccessDialog('Spare works are removed successfully.').then(async () => {
                    showLoader('#orderSummaryV2');
                    hideElement('#orderSummaryV2Container');

                    // $('#spareDtoModal').modal('hide');
                    await openSpareWorkModal(globalWdaParentKmat, globalSpareTypical, globalSpareDto, globalSpareDtoDesc)

                    await getOrderSummaryV2();

                    hideLoader('#orderSummaryV2');
                    showElement('#orderSummaryV2Container');
                });



            } catch (error) {
                const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                showErrorDialog(`Error: ${errorMessage}`);
            } finally {
                $(this).removeClass('loading disabled');
            }
        },
    });
})

async function getSpareAccessoryDataOfProject(spareProjectId) {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/ProjectController.php', {
            params: { action: 'getSpareParametersOfProject', spareProjectId: spareProjectId}
        });

        if (response.status === 200) {
            const spareDtoData = response.data;
            await openSpareWorkModal(spareDtoData.spare_parent_kmat, spareDtoData.spare_typical_number, spareDtoData.dto_number, shortDescription(escapeString(spareDtoData.dto_description), 100))
        }
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    }
}