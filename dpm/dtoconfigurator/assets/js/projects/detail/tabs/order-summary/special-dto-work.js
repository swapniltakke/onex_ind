let SpecialDtosOfProject = [];
async function getSpecialDtosOfNachbau() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/SpecialDtoController.php', {
            params: { action: 'getSpecialDtosOfNachbau', projectNo: getUrlParam('project-no'), nachbauNo: getUrlParam('nachbau-no') }
        });

        SpecialDtosOfProject = response.data;
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    }
}

async function openSpecialDtoWorkModal(dtoNumber, description, typicalNo, wdaParentKmat) {
    $('#specialDtoModalContent #available-materials').html('');
    $('#specialDtoModalContent #selected-materials').html('');

    const accessoryParentKmat = NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryParentKmat'];
    const accessoryTypicalNumber = NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryTypicalCode'];

    $('#specialDtoModalHeader').html(`<h3>Special DTO Work Menu</h3> <h4 style="margin-top: 0">${dtoNumber} | ${description} | ${typicalNo}</h4>`);
    $('#specialDtoModalContent .special-note').html(
        `Selected lists will be <b>added</b> to the accessory typical (<b>${accessoryTypicalNumber}</b>) of the order.
         Also, selected lists will be <b>removed</b> from <b>${typicalNo}</b> of PA Withdrawable KMAT.`
    );

    let availableMaterials = [];
    let selectedMaterials = [];

    // ŞU AN SAĞDA VE SOLDA DOĞRU VERILER CIKIYOR. SOLDAN DA SİLİYOR ZATEN EKLENMİŞ VERİYİ. YAPILMASI GEREKEN KONTROL: EĞER ZATEN EKLENDİYSE DUPLICATE OLMASIN.
    // VEYA TEKRARDAN CIKARILABILIYORMU. BUNUN DISINDA AKSESUARA DA ZATEN EKLIYOR, O YUZDEN GET ORDER SUMMARY DE GOSTERMESI KALDI.

    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/SpecialDtoController.php', {
            params: {
                action: 'getSubMaterialListsOfWdaAndSpecialDtoChanges',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                kmat: wdaParentKmat,
                typicalNo: typicalNo
            }
        });

        availableMaterials = response.data;

        try {
            const response = await axios.get('/dpm/dtoconfigurator/api/controllers/SpecialDtoController.php', {
                params: {
                    action: 'getSpecialDtoChangesOfTypical',
                    projectNo: getUrlParam('project-no'),
                    nachbauNo: getUrlParam('nachbau-no'),
                    dtoNumber: dtoNumber,
                    typicalNo: typicalNo,
                    wdaParentKmat: wdaParentKmat
                }
            });

            selectedMaterials = Array.isArray(response.data) ? response.data : [];
            availableMaterials = availableMaterials.filter(availMaterial => {
                return !selectedMaterials.some(selectedMaterial =>
                    selectedMaterial.material_number === availMaterial.material_number
                );
            });

            // Render available materials
            let availableMaterialListHtml = availableMaterials.map(material =>
                `<a class="item"
                     data-material="${material.material_number}" data-desc="${material.material_description}" data-dto="${dtoNumber}" 
                     data-dtodesc="${description}" data-typical="${typicalNo}" data-kmat="${wdaParentKmat}" data-id="${material.id}">
                    <span><b>${material.material_number}</b> - ${material.material_description}</span>
                    <span class="ui label grey basic" data-tooltip="Unit" data-position="top center">
                        <span class="material-unit">${material.unit}</span>
                    </span>
                    <span class="ui label grey basic" data-tooltip="Quantity" data-position="top center">
                        <span class="material-quantity">${parseFloat(material.quantity).toString().replace(/\\.000$/, '')}</span>
                    </span>
                </a>`
            ).join('');
            $('#specialDtoModalContent #available-materials').html(availableMaterialListHtml);

            // Render selected materials
            let selectedMaterialListHtml = selectedMaterials.map(material =>
                `<a class="item already-added-material"
                     data-material="${material.material_number}" data-desc="${material.material_description}" 
                     data-typical="${material.typical_no}" data-kmat="${material.wda_parent_kmat}"
                     style="background-color:palegreen;">
                    <span><b>${material.material_number}</b> - ${material.material_description}</span>
                    <span class="ui label grey basic" data-tooltip="Unit" data-position="top center">
                        <span class="material-unit">${material.release_unit}</span>
                    </span>
                    <span class="ui label grey basic" data-tooltip="Quantity" data-position="top center">
                        <span class="material-quantity">${parseFloat(material.release_quantity).toString().replace(/\\.000$/, '')}</span>
                    </span>
                    <span class="ui label basic" style="background: red;color: white;cursor: pointer;padding-right: 1px;padding-left: 4px;margin-left:5px;" 
                       onclick="removeSpecialDtoChange(this,'${material.id}')">
                        <i class="trash outline icon" style="margin-left:5px;"></i>
                   </span>
                </a>`
            ).join('');
            $('#specialDtoModalContent #selected-materials').html(selectedMaterialListHtml);

            updateMaterialCounts();

            $('#removeSpecialWorksButton').data('dto', dtoNumber).data('typical', typicalNo);
            $('#specialDtoModal').modal('show');

        } catch (error) {
            const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
            showErrorDialog(`Error: ${errorMessage}`);
        }
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`<b>${errorMessage}</b>`);
    }
}

function updateMaterialCounts() {
    $('#specialDtoModalContent #available-materials-count').text($('#specialDtoModalContent #available-materials a').length);
    $('#specialDtoModalContent #selected-materials-count').text($('#specialDtoModalContent #selected-materials a').length);
}

$('#specialDtoModalContent #available-materials').on('click', 'a', function () {
    $(this).toggleClass('active');
});

$('#specialDtoModalContent #selected-materials').on('click', 'a', function () {
    $(this).toggleClass('active');
});

$('#saveSpecialWorksButton').on('click', async function () {
    let selectedMaterials = [];

    $('#specialDtoModalContent  #selected-materials a').each(function () {
        let id = $(this).data('id');
        let material_number = $(this).data('material');
        let material_description = $(this).data('desc');
        let material_unit = $(this).find('.material-unit').text();
        let dto_number = $(this).data('dto');
        let dto_description = $(this).data('dtodesc');
        let typical_no = $(this).data('typical');
        let wda_kmat = $(this).data('kmat');
        let is_item_already_added = $(this).hasClass('already-added-material');

        if (!is_item_already_added) {
            selectedMaterials.push({
                id: id,
                material_number: material_number,
                material_description: material_description,
                material_unit: material_unit,
                dto_number: dto_number,
                dto_description: dto_description,
                typical_no: typical_no,
                wda_kmat: wda_kmat,
            });
        }
    });

    if (selectedMaterials.length === 0) {
        showErrorDialog('There is no selected materials!');
        return;
    }

    try {
        $(this).addClass('loading disabled');

        await axios.post('/dpm/dtoconfigurator/api/controllers/SpecialDtoController.php?',
            {
                action: 'saveSpecialDtoChangesToAccessory',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                materials: selectedMaterials,
                accessoryTypicalNo: NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryTypicalCode'],
                accessoryParentKmat: NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryParentKmat'],
                isRevisionNachbau: currentlyWorkingUser.isRevisionNachbau
            },
            { headers: { 'Content-Type': 'multipart/form-data' }}
        );

        showSuccessDialog('Spare DTO works are saved successfully.').then(async () => {
            showLoader('#orderSummaryV2');
            hideElement('#orderSummaryV2Container');

            await getOrderSummaryV2();
            $('#specialDtoModal').modal('hide');

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


$('#specialDtoModalContent #move-material').on('click', function () {
    let selectedItemsLeft = $('#specialDtoModalContent #available-materials a.active');
    let selectedItemsRight = $('#specialDtoModalContent #selected-materials a.active');

    if (selectedItemsLeft.length > 0) {
        selectedItemsLeft.each(function () {
            let selectedItem = $(this).clone();
            $('#specialDtoModalContent #selected-materials').append(selectedItem.removeClass('active').css('background-color', 'aquamarine'));
            $(this).remove();
        });

        updateMaterialCounts();
    }

    else if (selectedItemsRight.length > 0) {
        selectedItemsRight.each(function () {
            let selectedItem = $(this).clone();
            $('#specialDtoModalContent #available-materials').append(selectedItem.removeClass('active').css('background-color', 'aquamarine'));
            $(this).remove();
        });

        updateMaterialCounts();
    }
    else {
        fireToastr('warning', 'Lütfen malzeme listesi seçiniz.');  // Show warning if no items are selected
    }
});

async function removeSpecialDtoChange(_this, id) {
    const selectedItemToRemove = $(_this).closest('.item');

    showConfirmationDialog({
        title: 'Removing Change?',
        htmlContent: 'Are you sure to remove material list?',
        confirmButtonText: 'Yes, delete!',
        confirmButtonColor: "#d33",
        onConfirm: async function () {
            try {
                await axios.post('/dpm/dtoconfigurator/api/controllers/SpecialDtoController.php?',
                    {
                        action: 'removeSpecialDtoChange',
                        id: id
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                showSuccessDialog('Change removed successfully.').then(async () => {
                    selectedItemToRemove.remove();
                    await getOrderSummaryV2();
                });
            } catch (error) {
                const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                showErrorDialog(`<b>${errorMessage}</b>`);
            }
        },
    });
}


$('#removeSpecialWorksButton').on('click', async function () {
    const dtoNumber = $(this).data('dto');
    const typicalNo = $(this).data('typical');

    showConfirmationDialog({
        title: 'Remove all changes?',
        htmlContent: 'Do you want to remove all changes for this DTO?',
        confirmButtonText: 'Yes, delete it!',
        confirmButtonColor: "#d33",
        onConfirm: async function () {

            try {
                $(this).addClass('loading disabled');

                await axios.post('/dpm/dtoconfigurator/api/controllers/SpecialDtoController.php?',
                    {
                        action: 'removeAllSpecialDtoChanges',
                        projectNo: getUrlParam('project-no'),
                        nachbauNo: getUrlParam('nachbau-no'),
                        dtoNumber: dtoNumber,
                        typicalNo: typicalNo
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                showSuccessDialog('All changes are removed successfully.').then(async () => {
                    showLoader('#orderSummaryV2');
                    hideElement('#orderSummaryV2Container');

                    await getOrderSummaryV2();
                    $('#specialDtoModal').modal('hide');

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

