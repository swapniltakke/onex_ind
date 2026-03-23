async function renderWorkListDataTable(workListData) {
    const tableId = '#workListDataTable';

    // Birbirine etki eden dto listeleri varsa duplicate olmaması için filtrelenmesi gerekiyor
    const seenRows = new Set();
    const filteredWorkListData = workListData.filter(row => {
        const rowKey = `${row.material_added_number}|${row.material_deleted_number}|${row.affected_dto_numbers}`;
        if (seenRows.has(rowKey) && row.affected_dto_numbers) {
            return false;
        }
        seenRows.add(rowKey);
        return true;
    });

    if ($.fn.DataTable.isDataTable(tableId))
        $(tableId).DataTable().destroy();

    if ($('#workListContainer').hasClass('transition hidden'))
        $('#workListContainer').removeClass('transition hidden');

    if (workListData.length === 0) {
        $('#workListContainer').addClass('hide-important')
    }
    else {
            const table = $(tableId).DataTable({
            data: filteredWorkListData,
            destroy: true,
            pageLength: 10,
            deferRender: false,  // Ensure full rendering upfront
            lengthMenu: [10, 25, 50, 100],  // Allow users to control page length
            paging:true,
            autoWidth:false,
            order: [[1, 'asc']],
            columnDefs: [
                { width: '25%', targets: [0] },
                { width: '10%', targets: [1] },
                { width: '11%', targets: [2] },
                { width: '11%', targets: [3] },
                { width: '20%', targets: [4] },
                { width: '3%', targets: [5,6], orderable: false },
                { width: '25%', targets: [7] },
                { width: '5%', targets: [8], orderable: false },
            ],
            columns: [
                {
                    render: function (data, type, row) {
                        if (row.error_message_id === '0') {
                            let cellContent = '';
                            let addButton = `<button id="btnAddMaterialToProject${row.id}" type="button" class="ui teal small very compact button addButtonWorkList" onclick="addMaterialToProject(this)">Add</button>`;
                            let removeAllButton = `<button id="removeAllButton${row.id}" type="button" class="ui red icon small very compact button removeButtonWorkList" onclick="removeAllSelectionsFromProject(this)">Clear</button>`;

                            let typeNumberDropdown = getTypeNumberDropdown(row);
                            let listTypeSelect = getListTypeSelectBox(row);

                            if (row.type) {
                                cellContent = `${typeNumberDropdown}
                                               <div style="display: flex; align-items: center; gap: 10px;">
                                                    ${addButton} 
                                                    ${listTypeSelect}
                                                    ${removeAllButton}
                                               </div>`;
                            } else {
                                cellContent = `${listTypeSelect}`;
                            }

                            return cellContent;
                        }
                        else {
                            if(row.error_message_id === '1') {
                                return `<span style="line-height:1.5rem;">
                                            ${row.error_message_en} 
                                            <br> Nachbau KMAT numbers: <br> 
                                            <b> <span style='color:red;'>${row.nachbau_kmats} / ${row.parent_kmat_in_nachbau}</span> </b>
                                        </span>`;
                            }
                            else {
                                return `<span class="errorMsgWorkList" style="color:red;"> ${row.error_message_en} </span>`;
                            }
                        }
                    },
                    className: 'center aligned'
                },
                {
                    data: 'dto_number',
                    className: 'center aligned',
                    render: function (data, type, row) {
                        let dtoLinks = '';

                        if (row.affected_dto_numbers) {
                            const dtoNumbersArray = row.affected_dto_numbers.split("|");
                            const links = dtoNumbersArray.map(dto =>
                                `<a href="#" data-tooltip="Open TK Form Material List" data-position="top center" 
                                    class="dto-link" data-dto-number="${dto}">${dto}</a>`
                                            );
                            dtoLinks = links.join('<br>');
                        } else {
                            dtoLinks = `<a href="#" data-tooltip="Open TK Form Material List" data-position="top center"
                            class="dto-link" data-dto-number="${data}">${data}</a>`;
                        }

                        if (row.has_tkform_note === '1' || row.has_tkform_note === 1 || row.has_tkform_note === true) {
                            const cornerMark = `<div class="tkform-corner" data-tkform-id="${row.tkform_id}" data-tooltip="View TK Form Note" data-position="top center"></div>`;
                            return `<div style="position: relative;">${dtoLinks}${cornerMark}</div>`;
                        } else {
                            return dtoLinks;
                        }
                    }
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
                    className: 'center aligned dblclick-cell addedMaterial'
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
                    className: 'center aligned dblclick-cell deletedMaterial'
                },
                {
                    data: 'material_deleted_description',
                    render: (data, type, row) => row.operation === 'add' ? row.material_added_description : row.material_deleted_description,
                    className: 'center aligned'
                },
                {
                    data: 'quantity',
                    render: (data, type, row) => {
                        const currenQty = parseFloat(data).toString().replace(/\.000$/, '');
                        if (row.operation === 'add')
                        {
                            return `<div class="ui input">
                                        <input type="number" class="quantity-input" min="0" step="1" value="${currenQty}" style="width: 30px; padding: 5px; text-align: center;">
                                    </div>`
                        }
                        else
                            return `<span class="quantity-input">${currenQty}</span>`;
                    },
                    className: 'center aligned'
                },
                {
                    data: 'unit',
                    className: 'center aligned'
                },
                {
                    data: 'work_center',
                    render: (data, type, row) => {
                        if (row.added_on_work_center_id && row.added_on_work_center_id !== row.tk_work_center_id) {
                            return `<div data-tooltip="Default kmat is ${row.work_content}" data-position="top center">
                                        <i class="info circle blue big icon" style="margin-bottom:0.4rem!important;"></i><br>
                                        <a class="ui blue label">${row.added_on_work_center}</a><br>
                                        <h5 style="margin-top:2%;">${row.added_on_work_content}</h5>
                                    </div>`;
                        } else if (row.secondary_work_center !== "" && row.added_on_work_center_id !== row.tk_work_center_id) {
                            return `<div data-tooltip="Default kmat is ${row.work_content}" data-position="top center">
                                        <i class="info circle blue big icon" style="margin-bottom:0.4rem!important;"></i><br>
                                        <a class="ui blue label">${row.secondary_work_center}</a><br>
                                        <h5 style="margin-top:2%;">${row.secondary_work_content}</h5>
                                    </div>`;
                        } else {
                            return `<a class="ui violet label">${row.work_center}</a><br>
                                    <h5 style="margin-top:2%;">${row.work_content}</h5>`;
                        }
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
            ],
            drawCallback: function () {
                $('.type-number-select').each(function () {
                    $(this).dropdown({
                        allowAdditions: false,
                        fullTextSearch: true,  // Search within option text
                        forceSelection: false,  // Allow users to type and search freely
                        selectOnKeydown: false,  // Avoid auto-selecting on keydown
                        clearable: true  // Allow clearing the selection
                    });
                });
            }
        });


        $('#workListContainer').transition('zoom', function() {
            requestAnimationFrame(() => {
                table.draw();
                // table.fixedHeader.adjust();
            });
        });

        //Search customization
        const $searchInput = $('.dt-search input[type="search"]');
        $searchInput.attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
    }
}

function getListTypeSelectBox(data) {
    const options = [
        { value: "Typical", label: "Typical Based List", selected: data.type === "Typical" },
        { value: "Panel", label: "Panel Based List", selected: data.type === "Panel" },
        { value: "Accessories", label: "Accessory List", selected: data.type === "Accessories" }
    ];

    const defaultOption = data.type ? "" : `<option value="" style="font-weight: bold;">Please Choose List Type</option>`;
    const style = data.effective === "1" ? "color:orange;" : (data.affected_dto_numbers ? "color:#00c2ff;" : "");
    const filteredOptions = data.effective === "1"
        ? options.filter(opt => opt.value === "Panel")
        : options;

    const optionElements = filteredOptions
        .map( opt => `<option value="${opt.value}" ${opt.selected ? "selected" : ""}>${opt.label}</option>` )
        .join("");

    return `<select class="ui selection dropdown list-type-select" data-current-list-type="${data.type}" style="${style}" onChange="updateMaterialListType(this)">
                ${defaultOption}
                ${optionElements}
            </select>`;

}

function getTypeNumberDropdown(data) {
    const nachbauNo = getUrlParam('nachbau-no');
    const typeNumber = getUrlParam('type-number');

    const accessoryTypicalCode = NachbauDataOfProject[nachbauNo]['AccessoryTypicalCode'];
    const accessoryReleaseItems = data.accessory_release_items ? data.accessory_release_items.split('|') : [];
    const releaseItems = data.release_items ? data.release_items.split('|') : [];

    const releaseData = [...releaseItems, ...accessoryReleaseItems]

    const nachbauNumbers = getNachbauTypeNumbers(data, accessoryTypicalCode);

    if (typeNumber)
        return handleTypeNumberFilter(nachbauNumbers, releaseItems, accessoryReleaseItems, releaseData, data.id);

    return generateDropdown(nachbauNumbers, releaseData, releaseItems, accessoryReleaseItems, accessoryTypicalCode, data.id);
}

function getNachbauTypeNumbers(data, accessoryTypicalCode) {
    const urlType = getUrlParam('type');

    if (data.type === 'Typical')
        return data.nachbau_typicals.split('|');
    if (data.type === 'Panel')
        return data.nachbau_panels.split('|');
    if (data.type === 'Accessories') {
        if (urlType === 'Typical')
            return data.nachbau_typicals.split('|');
        if (urlType === 'Panel')
            return data.nachbau_panels.split('|');

        return [accessoryTypicalCode];
    }
    return [];
}

function handleTypeNumberFilter(nachbauNumbers, releaseData, releaseItems, accessoryReleaseItems, id) {
    const typeNumber = getUrlParam('type-number');
    const isNumberReleased = releaseData.includes(typeNumber);

    if (!isNumberReleased) {
        return generateAddedNumbers(nachbauNumbers, releaseData, releaseItems, accessoryReleaseItems, id) + generateSingleSelectDropdown(typeNumber, id);
    }
    else {
        return generateAddedNumbers(nachbauNumbers, releaseData, releaseItems, accessoryReleaseItems, id);
    }
}

function generateDropdown(nachbauNumbers, releaseData, releaseItems, accessoryReleaseItems, accessoryTypicalCode, id) {

    if (areArraysEqual(nachbauNumbers, releaseData)) {
        return generateAddedNumbers(nachbauNumbers, releaseData, releaseItems, accessoryReleaseItems, id);
    }

    if (releaseData.length > nachbauNumbers.length && releaseData.includes(accessoryTypicalCode)) {
        return generateAddedNumbers(nachbauNumbers, releaseData, releaseItems, accessoryReleaseItems, id);
    }


    return generateAddedNumbers(nachbauNumbers, releaseData, releaseItems, accessoryReleaseItems, id) + generateMultipleSelectDropdown(nachbauNumbers, releaseData, id);
}

function generateAddedNumbers(nachbauNumbers, releaseData, releaseItems, accessoryReleaseItems, id) {
     return releaseData.map(item => {
        let backgroundColor = '';

        if (releaseItems.includes(item)) {
            backgroundColor = 'green';
        } else if (accessoryReleaseItems.includes(item)) {
            backgroundColor = 'blue';
        }

        return `
            <span class="ui label large added-items ${backgroundColor}" style="margin-bottom:10px!important;">
                <i class="delete icon removeNumberIcon" 
                   data-id="${id}" 
                   data-item="${item}" 
                   onclick="removeNumberFromProject(this)">
                </i>&nbsp;${item}
            </span>
        `;
    }).join("");
}

function generateSingleSelectDropdown(typeNumber, rowId) {
    return `<select data-row-id="${rowId}" class="ui selection dropdown multiple type-number-select" multiple="" >
                <option value="${typeNumber}" selected>${typeNumber}</option>
            </select>`;
}

function generateMultipleSelectDropdown(nachbauNumbers, releaseData, rowId) {
    if (releaseData.length > 0)
        nachbauNumbers = nachbauNumbers.filter(item => !releaseData.includes(item));

    return `<select data-row-id="${rowId}" class="ui selection dropdown multiple type-number-select" multiple="" >
                ${nachbauNumbers.map(item => `<option value="${item}" selected>${item}</option>`).join('')}
            </select>`;
}

function areArraysEqual(arr1, arr2) {
    if (arr1.length !== arr2.length) return false;
    const sortedArr1 = [...arr1].sort();
    const sortedArr2 = [...arr2].sort();
    return sortedArr1.every((value, index) => value === sortedArr2[index]);
}


async function addMaterialToProject(_this) {
    $(_this).addClass('loading disabled');
    const row = $(_this).closest('tr');
    const data = $('#workListDataTable').DataTable().row(row).data();
    const listType = row.find(".list-type-select option:selected").val();
    const releaseQuantity = row.find(".quantity-input").is("input") ? row.find(".quantity-input").val()
                                                                    : row.find(".quantity-input").text();
    const selectedItems = row.find(".type-number-select").dropdown('get value'); // Eklenecek itemların arrayini getirir.

    if (listType === "")
        showErrorDialog("Please choose a list type!");
    else if (listType === 'Accessories'){
        //Eğer Seçilen aksesuar listesiyse ve ayrıca TK ya Aksesuar olarak işlenmiş ise seçim yaptırmadan aksesuara gönder.

        if (data.type === 'Accessories')
            await sendMaterialToProject(data, selectedItems, listType, releaseQuantity, null,true);
        else
            await openAddAccessoryModal(_this, data, selectedItems, listType, releaseQuantity);
    }
    else {
        if (selectedItems.length > 0) {

            // Eğer eklenmeye çalışan bir cihaz ise ve secondary kmat ı seçilmiş bir cihaz ise, yani birden fazla kmata eklenebilir ise, if'e girer ve ekleneceği kmatı seçtirir.
            if (data.material_added_is_device === '1' && data.common_kmats.includes('|')) {
                const defaultWorkCenterId = data.tk_work_center_id;
                await openDeviceChoosableKmatsModal(data, selectedItems, listType, releaseQuantity, defaultWorkCenterId, false);
            } else {
                await sendMaterialToProject(data, selectedItems, listType, releaseQuantity, null,false);
            }
        }
        else {
            showErrorDialog("Please choose a typicals/panels to add to the project!");
        }
    }

    $(_this).removeClass('loading disabled');
}


async function openAddAccessoryModal(_this, data, selectedItems, listType, releaseQuantity) {
    const accessoryTypicalNumber = NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryTypicalCode'];

    $('#addAccessoryModal .radioButtonItems').html('');
    $('#addAccessoryModal .radioButtonItems').html(`
              <div class="field" id="choose-typical-item-field">
                <div class="ui radio checkbox">
                  <input type="radio" name="accessory-option" value="typical-number" id="radio-accessory-typical-number">
                  <label for="radio-accessory-typical-number" style="cursor:pointer;">
                     Add under <span style="font-weight: bold; color: #21ba45;">${accessoryTypicalNumber}</span> accessory typical.
                  </label>
                </div>
              </div>
              
              <div class="field" id="selected-items-field">
                <div class="ui radio checkbox">
                  <input type="radio" name="accessory-option" value="selected-items" id="radio-selected-items">
                  <label for="radio-selected-items" style="cursor:pointer;">
                    Add as accessory under <span style="font-weight: bold; color: #f2711c;">${selectedItems}</span> typicals.
                  </label>
                </div>
              </div>
        `);


    if (selectedItems === undefined || selectedItems.length === 0)
        $('#selected-items-field').css('display', 'none');

    $('#addAccessoryModal').modal('show');

    $('#addAccessoryModal .approve.button').data({
        data: data,
        selectedItems: selectedItems,
        listType: listType,
        releaseQuantity: releaseQuantity,
        accessoryTypicalNumber: accessoryTypicalNumber
    });

    $(_this).removeClass('loading disabled');
}

$(document).on('click', '#addAccessoryModal .approve.button', async function () {
    const data = $(this).data('data');
    let selectedItems = $(this).data('selectedItems');
    const listType = $(this).data('listType');
    const releaseQuantity = $(this).data('releaseQuantity');
    const accessoryTypicalNumber = $(this).data('accessoryTypicalNumber');

    const isSelectedItemsChecked = $('#radio-selected-items').is(':checked');
    const isAccessoryTypicalChecked = $('#radio-accessory-typical-number').is(':checked');

    if (isSelectedItemsChecked) {
        await sendMaterialToProject(data, selectedItems, listType, releaseQuantity, null,false);
    } else if (isAccessoryTypicalChecked) {
        selectedItems = [accessoryTypicalNumber];
        await sendMaterialToProject(data, selectedItems, listType, releaseQuantity,null, true);
    } else {
        showErrorDialog("Please make a selection!");
    }
});


async function sendMaterialToProject(data, selectedItems, listType, releaseQuantity, selectedAddedOnWorkCenterId, isAccessoryTypicalChecked) {
    const accessoryTypicalNumber = NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryTypicalCode'];
    const accessoryParentKmat = NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryParentKmat'];

    try {
        const response = await axios.post('/dpm/dtoconfigurator/api/controllers/ProjectController.php?',
            {
                action: 'addMaterialToProject',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                data: data,
                selectedItems: selectedItems,
                listType: listType,
                releaseQuantity: releaseQuantity,
                accessoryTypicalCode: accessoryTypicalNumber,
                accessoryParentKmat: accessoryParentKmat,
                isAccessoryTypicalChecked: isAccessoryTypicalChecked,
                selectedAddedOnWorkCenterId: selectedAddedOnWorkCenterId,
                currentlyWorkingUser: currentlyWorkingUser
            },
            { headers: { 'Content-Type': 'multipart/form-data' }}
        );

        if (response.status === 200) {

            const table = $('#workListDataTable').DataTable();
            const rowNode = table.row((idx, row) => row.id === data.id);

            if (rowNode.length) {
                const currentRowData = rowNode.data();

                currentRowData.release_items = response.data.release_items;
                currentRowData.accessory_release_items = response.data.accessory_release_items;
                currentRowData.added_on_work_center_id = selectedAddedOnWorkCenterId;
                currentRowData.added_on_work_center = response.data.added_on_work_center;
                currentRowData.added_on_work_content = response.data.added_on_work_content;
                currentRowData.material_added_sap_defined = response.data.material_added_sap_defined;
                currentRowData.material_deleted_sap_defined = response.data.material_deleted_sap_defined;

                rowNode.data(currentRowData).invalidate().draw(false);

                fireToastr('success', 'Items successfully added to the project!');
            }

            $('#addAccessoryModal').modal('hide');

            showLoader('#orderSummaryV2');
            hideElement('#orderSummaryV2Container');

            await getOrderSummaryV2();

            hideLoader('#orderSummaryV2');
            showElement('#orderSummaryV2Container');
        } else {
            showErrorDialog(response.message)
        }
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(errorMessage)
    }
}

async function removeAllSelectionsFromProject(_this) {
    $(_this).addClass('loading disabled');
    const row = $(_this).closest('tr');
    const data = $('#workListDataTable').DataTable().row(row).data();

    if ((data.release_items === null || data.release_items === '') && (data.accessory_release_items === null || data.accessory_release_items === '')) {
        showErrorDialog('No added typical or panel found!');
        $(_this).removeClass('loading disabled');
    }
    else {
        showConfirmationDialog({
            title: 'Are you sure?',
            htmlContent: 'Do you really want to delete all released typicals/panels from order summary?',
            confirmButtonText: 'Yes, delete it!',
            confirmButtonColor: "#d33",
            onConfirm: async function () {

                try {
                    const response = await axios.post('/dpm/dtoconfigurator/api/controllers/ProjectController.php', {
                            action: 'removeAllSelectionsFromProject',
                            data: data,
                            currentlyWorkingUser: currentlyWorkingUser
                        },
                        { headers: { 'Content-Type': 'multipart/form-data' }});

                    showSuccessDialog('All of released typicals/panels are removed successfully.').then(async () => {
                        $(_this).removeClass('loading disabled');

                        const table = $('#workListDataTable').DataTable();
                        const rowNode = table.row((idx, row) => row.id === response.data.id);

                        if (rowNode.length) {
                            const currentRowData = rowNode.data();

                            // update possible changed props
                            currentRowData.release_items = response.data.release_items;
                            currentRowData.accessory_release_items = response.data.accessory_release_items;
                            currentRowData.common_kmats = response.data.common_kmats;
                            currentRowData.nachbau_kmats = response.data.nachbau_kmats;
                            currentRowData.nachbau_panels = response.data.nachbau_panels;
                            currentRowData.nachbau_typicals = response.data.nachbau_typicals;
                            currentRowData.added_on_work_center_id = response.data.added_on_work_center_id;

                            rowNode.data(currentRowData).invalidate().draw(false);
                        }

                        await getOrderSummaryV2();
                    });

                } catch (error) {
                    const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                    showErrorDialog(`Error: ${errorMessage}`);
                }
            }
        });
    }
}

async function removeNumberFromProject(_this) {
    $(_this).addClass('loading disabled');

    let rowId = $(_this).data('id');
    let releaseItem = $(_this).data('item');

    try {
        const response = await axios.post('/dpm/dtoconfigurator/api/controllers/ProjectController.php', {
                action: 'removeTypeNumberFromWork',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                id: rowId,
                releaseItem: releaseItem,
                currentlyWorkingUser: currentlyWorkingUser
            },
            { headers: { 'Content-Type': 'multipart/form-data' }});

        if (response.status === 200) {
            const updatedReleaseItems = response.data.release_items;
            const updatedAccessoryReleaseItems = response.data.accessory_release_items;

            // Locate row in DataTable
            const table = $('#workListDataTable').DataTable();
            const rowNode = table.row((idx, row) => row.id === response.data.id);

            if (rowNode.length) {
                const currentRowData = rowNode.data();

                currentRowData.release_items = updatedReleaseItems;
                currentRowData.accessory_release_items = updatedAccessoryReleaseItems;

                // Update row without changing position
                rowNode.data(currentRowData).invalidate().draw(false);

                fireToastr('success', releaseItem + ' removed from the work list data!');
            }

            await getOrderSummaryV2();
        } else {
            showErrorDialog(response.message)
        }
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    } finally {
        $(_this).removeClass('loading disabled');
    }
}


async function updateMaterialListType(_this) {
    const row = $(_this).closest('tr');
    const data = $('#workListDataTable').DataTable().row(row).data();
    const selectedListType = $(_this).closest('tr').find(".list-type-select option:selected").val();
    const currentListType = $(_this).data('current-list-type');

    if (selectedListType === 'Accessories' || data.type === selectedListType) {
        return;
    }

    showConfirmationDialog({
        title: 'Change List Type?',
        htmlContent: `Material list type will be changed from <b>${currentListType}</b> to <b>${selectedListType}</b>.`,
        confirmButtonText: 'Yes, change it!',
        confirmButtonColor: "green",
        onConfirm: async function () {
            try {
                const response = await axios.post('/dpm/dtoconfigurator/api/controllers/TkFormMaterialController.php?', {
                    action: 'updateMaterialListType',
                    projectNo: getUrlParam('project-no'),
                    nachbauNo: getUrlParam('nachbau-no'),
                    tkformId: data.tkform_id,
                    materialAddedId: data.material_added_id,
                    materialDeletedId: data.material_deleted_id,
                    type: selectedListType
                }, { headers: { 'Content-Type': 'multipart/form-data' } });

                const responseObj = response.data;

                if (responseObj.responseStatus === 'warning') {
                    const data = responseObj.data;
                    let warningMsgBody = `⚠️ <b>Important Notice</b><br><br>`;
                    warningMsgBody += `Changing the material list type will delete the following BOM changes:<br><br>`;
                    warningMsgBody += `<ul style="list-style-type: none; padding: 0;">`;
                    data.forEach(row => {
                        warningMsgBody += `<li>🔹 <b>${row.project_number}</b> - <b>${row.nachbau_number}</b> - ${row.last_updated_by}</li>`;
                    });
                    warningMsgBody += `</ul>`;

                    // Show confirmation dialog for force update
                    Swal.fire({
                        title: "Are you sure?",
                        html: warningMsgBody,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Still Continue",
                        cancelButtonText: "Cancel",
                        allowOutsideClick: false
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            // User confirmed force update
                            try {
                                const forceResponse = await axios.post('/dpm/dtoconfigurator/api/controllers/TkFormMaterialController.php?', {
                                    action: 'updateMaterialListType',
                                    projectNo: getUrlParam('project-no'),
                                    nachbauNo: getUrlParam('nachbau-no'),
                                    tkformId: data.tkform_id,
                                    materialAddedId: data.material_added_id,
                                    materialDeletedId: data.material_deleted_id,
                                    type: selectedListType,
                                    forceUpdate: '1'
                                }, { headers: { 'Content-Type': 'multipart/form-data' } });

                                if (forceResponse.data.responseType === 'success') {
                                    updateTableRow(data, forceResponse.data.type);
                                } else {
                                    showErrorDialog("Force update failed. Please try again.");
                                }
                            } catch (error) {
                                showErrorDialog(`Error: ${error.message}`);
                            }
                        }
                    });

                } else if (responseObj.responseType === 'success') {
                    updateTableRow(data, responseObj.type);
                }
            } catch (error) {
                const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                showErrorDialog(`Error: ${errorMessage}`);
            }
        }
    });
}

function updateTableRow(data, updatedType) {
    const table = $('#workListDataTable').DataTable();
    const rowNode = table.row((idx, row) => row.id === data.id);

    if (rowNode.length) {
        const currentRowData = rowNode.data();
        currentRowData.type = updatedType;

        const urlType = getUrlParam('type');
        if (urlType && urlType !== updatedType) {
            rowNode.remove().draw(false);
            fireToastr('info', 'Row removed due to type mismatch.');
        } else {
            rowNode.data(currentRowData).invalidate().draw(false);
            fireToastr('success', 'Material list type successfully updated!');
        }
    }
}

$('#workListDataTable').on('dblclick', '.dblclick-cell', function () {
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


$('#workListDataTable').on('click', '.tkform-corner', async function (e) {
    e.preventDefault();
    const tkformId = $(this).data('tkform-id');

    await axios.get(`/dpm/dtoconfigurator/api/controllers/TkFormController.php?action=getTkForm&id=${tkformId}`)
        .then(response => {
            const { id, document_number, dto_number } = response.data;
            const url = `/dpm/dtoconfigurator/core/tkform/detail/tk-notes/tk-notes.php?id=${id}&document-number=${document_number}&dto-number=${dto_number}`;
            window.open(url, '_blank');
        })
        .catch(error => {
            console.error("Error fetching DTO parameters:", error);
            alert('An error occurred while fetching the data.');
        });
});


async function openDeviceChoosableKmatsModal(data, selectedItems, listType, releaseQuantity, defaultWorkCenterId, isAccessoryTypicalChecked) {

    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/WorkCenterController.php', {
            params: {
                action: 'getDeviceChoosableKmats',
                rowData: data
            }
        });

        const choosableKmats = Object.values(response.data);
        $('#chooseDeviceKmatModal .deviceKmatsRadioButtonItems').html('');

        let radioButtonsHtml = '';

        choosableKmats.forEach((item, index) => {
            const radioId = `radio-kmat-${index}`;
            radioButtonsHtml += `
                <div class="field">
                    <div class="ui radio checkbox">
                        <input type="radio" name="choosable-kmat-option" value="${item.kmat}" id="${radioId}" data-workcenterid="${item.work_center_id}">
                        <label for="${radioId}" style="cursor:pointer;">
                            ${item.kmat} - ${item.work_center} - <b>${item.work_content}</b>
                            ${item.work_center_id === defaultWorkCenterId ? '(Default)' : ''}
                        </label>
                    </div>
                </div>
            `;
        });

        $('#chooseDeviceKmatModal .deviceKmatsRadioButtonItems').html(radioButtonsHtml);
        $('#chooseDeviceKmatModal .ui.radio.checkbox').checkbox();
        $('#chooseDeviceKmatModal').modal('show');

        $('#chooseDeviceKmatModal .approve.button').data({
            data: data,
            selectedItems: selectedItems,
            listType: listType,
            releaseQuantity: releaseQuantity,
        });

    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    }
}

$('#chooseDeviceKmatModal .approve.button').off('click').on('click', async function () {
    $(this).addClass('loading disabled');
    
    let data = $('#chooseDeviceKmatModal .approve.button').data('data');
    const selectedItems = $('#chooseDeviceKmatModal .approve.button').data('selectedItems');
    const listType = $('#chooseDeviceKmatModal .approve.button').data('listType');
    const releaseQuantity = $('#chooseDeviceKmatModal .approve.button').data('releaseQuantity');

    const selectedKmat = $('#chooseDeviceKmatModal input[name="choosable-kmat-option"]:checked').val();
    const selectedAddedOnWorkCenterId = $('#chooseDeviceKmatModal input[name="choosable-kmat-option"]:checked').data('workcenterid');

    if (!selectedKmat) {
        showErrorDialog('Selected KMAT does not exists!');
        $(this).removeClass('loading disabled');
    }

    try {
        await axios.post('/dpm/dtoconfigurator/api/controllers/ProjectController.php?',
            {
                action: 'updateCommonKmatOfDevice',
                projectWorkId: data.id,
                selectedKmat: selectedKmat,
                selectedWorkCenterId: selectedAddedOnWorkCenterId
            },
            { headers: { 'Content-Type': 'multipart/form-data' }}
        );

        const table = $('#workListDataTable').DataTable();
        const rowNode = table.row((idx, row) => row.id === data.id);

        if (rowNode.length) {
            const currentRowData = rowNode.data();

            currentRowData.common_kmats = selectedKmat;
            currentRowData.added_on_work_center_id = selectedAddedOnWorkCenterId;

            await sendMaterialToProject(currentRowData, selectedItems, listType, releaseQuantity, selectedAddedOnWorkCenterId, false);
        }
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(errorMessage)
    } finally {
        $(this).removeClass('loading disabled');
    }
});
