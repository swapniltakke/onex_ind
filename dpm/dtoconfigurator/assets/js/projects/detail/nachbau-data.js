let NachbauDataOfProject = [];
let currentlyWorkingUser = [];

$(document).ready(function () {
    showTypicalAndPanelsAndDtoNumbers();
});

async function showNachbauData(projectNo) {
    hideElement('#nachbauFileExistsOrNot');
    hideElement('#nachbauErrorMsgDiv');
    hideElement('#projectDataTabsInfoMessage');
    showElement('#nachbauDataContainer');
    showElement('#fetchNachbauLoader');

    try {
        const nachbauData = await fetchNachbauData(projectNo);
        if (nachbauData) {
            showNachbauList(nachbauData);
            $('#nachbauData').transition('pulse').css('display', 'flex').css('gap', '20px');
            $('#nachbauFilterAccordion').transition('pulse');
            $('#projectDataTabsInfoMessage').transition('pulse');
            hideElement('#fetchNachbauLoader');
            await getJTTypicalAndPanels();
        } else {
            $('#dtoNotFoundMsg span').text(message);
            $('#dtoNotFoundMsg').transition('pulse');
        }
    } catch (error) {
        $('#dtoNotFoundMsg span').text(error.message);
        $('#dtoNotFoundMsg').transition('pulse');
    } finally {
        hideLoader('#nachbauDataContainer');
        hideElement('#fetchNachbauLoader');
    }
}

function showNachbauList(nachbauData) {
    $('.ui.menu .item[data-tooltip]').popup();
    const $nachbauListMenu = $('#nachbauList .menu');
    $nachbauListMenu.empty();
    $.each(nachbauData, function (key, value) {
        const time = value?.Time || 'No Time';
        const dateOnly = time !== 'No Time' ? moment(time, 'DD.MM.YYYY HH:mm').format('DD.MM.YYYY') : time;
        const displayText = `${key} - ${dateOnly}`;

        let backgroundColor = '';
        let tooltip = null;

        if (value.PublishStatus === '5') {
            tooltip = 'Nachbau has been published';
            backgroundColor = 'lightgreen';
        }
        else if (value.PublishStatus === '4') {
            tooltip = 'Nachbau publish request rejected';
            backgroundColor = '#ff0000b0';
        }
        else if (value.PublishStatus === '3') {
            tooltip = 'Nachbau Sent for Approval';
            backgroundColor = 'lightskyblue';
        }
        else if (value.PublishStatus === '2') {
            tooltip = 'Work in Progress';
            backgroundColor = 'lightyellow';
        }
        else if (value.PublishStatus === '6') {
            tooltip = 'Withdrawn Request';
            backgroundColor = 'blanchedalmond';
        }
        else if (value.PublishStatus === '7') {
            tooltip = 'Nachbau Under Revision';
            backgroundColor = '#dfdf0e';
        }
        else if (value.PublishStatus === '8') {
            tooltip = 'Nachbau Sent for Revision Approval';
            backgroundColor = 'lightseagreen';
        }
        else if (value.PublishStatus === '9') {
            tooltip = 'Nachbau Revision has been released';
            backgroundColor = 'limegreen';
        }

        const tooltipAttr = tooltip ? `data-tooltip="${tooltip}" data-position="top center"` : '';

        $nachbauListMenu.append(`
            <a class="item nachbau-no-filter-item" data-value="${key}" ${tooltipAttr} style="background-color:${backgroundColor}">
                ${displayText}
            </a>
        `);
    });

    showListType($nachbauListMenu, nachbauData);
}

function showListType($nachbauListMenu, nachbauData) {
    $nachbauListMenu.find('.item').on('click', async function () {
        removeUrlParamArray(['nachbau-no', 'type', 'type-number', 'dto-number']);
        hideElement('#currentlyWorkingUserInfoMessage');
        hideElement('#listType');
        hideElement('#typicalAndPanels');
        hideElement('#dtoNumbers');
        hideElement('#dtoDescription');
        hideElement('#projectDataTabsInfoMessage');
        showLoader('#nachbauData');
        //ORDER SUMMARY
        showLoader('#orderSummaryV2');
        hideElement('#orderSummaryContainerV2');

        const $clickedItem = $(this);
        $nachbauListMenu.find('.item').removeClass('active').css('font-weight', '');
        $('#listType .ui.menu .item').removeClass('active').css('font-weight', '');
        $('#typicalAndPanels .ui.menu .item').removeClass('active');
        $('#dtoNumbers .ui.menu .item').removeClass('active');
        $clickedItem.addClass('active').css('font-weight', '700');

        const nachbauTxt = $clickedItem.data('value');
        updateUrlParameter('nachbau-no', nachbauTxt);

        const accessoryTypicalCode = nachbauData[nachbauTxt]?.AccessoryTypicalCode || null;
        const data = await fetchCountOfListTypes(nachbauTxt,accessoryTypicalCode);

        currentlyWorkingUser = await getProjectCurrentStatus(nachbauTxt);


        $('#typicalCount').text(data.countTypicalAndPanel);
        $('#panelCount').text(data.countTypicalAndPanel);
        $('#accessoryCount').text(data.countAccessories);

        hideLoader('#nachbauData');
        $('#listType').transition('pulse');

        showElement('#projectDataTabs')

        hideAndLoaderProjectTabs();

        //Special DTO Checks
        await getSpareDtosOfNachbau();
        await getExtensionDtosOfNachbau();
        await getMinusPriceDtosOfNachbau();
        await getInterchangeDtosOfNachbau();
        // await getSpecialDtosOfNachbau();

        //Tab Data
        await getProjectData(); // Project Work Data
        await getKukoMatrix(); // Kuko Matrix
        await getOrderSummaryV2(); // Order Summary v2
        await initializeNachbauOperationSelectBoxes(); // Nachbau Operations
        await initializeProjectChecklist(); // Checklist
        await prepareBomNotesContent(); // Bom Notes
    });
}

function showTypicalAndPanelsAndDtoNumbers() {
    $('#listType .ui.menu .item').on('click', async function () {
        removeUrlParamArray(['type-number', 'dto-number']);
        hideElement('#typicalAndPanels');
        hideElement('#dtoNumbers');
        hideElement('#dtoDescription');
        showLoader('#nachbauData');

        const $clickedItem = $(this);
        $('#listType .ui.menu .item').removeClass('active').css('font-weight', '');
        $clickedItem.addClass('active').css('font-weight', '700');

        const listType = $clickedItem.data('value');
        updateUrlParameter('type', listType);

        try {
            const nachbauTxt = getUrlParam('nachbau-no');
            const currentNachbauData = NachbauDataOfProject[nachbauTxt];
            const accessoryTypicalCode = currentNachbauData?.AccessoryTypicalCode || null;

            if (!currentNachbauData) {
                fireToastr('error', 'No data found for the selected Nachbau.');
                return;
            }

            let nachbauDataWithType = [];
            if (listType === 'Typical')
                nachbauDataWithType = Object.entries(currentNachbauData.Typical || {}); // Get typical numbers and details
            else if (listType === 'Panel')
                nachbauDataWithType = Object.entries(currentNachbauData.Panel || {}); // Get ortz_kz numbers and details
            else if (listType === 'Accessories')
                nachbauDataWithType = Object.entries(currentNachbauData.Accessories || {}); // Get accessory dtos


            await populateTypicalAndPanelsData(listType, nachbauDataWithType);
            await populateDtoNumbersData(nachbauTxt, listType, accessoryTypicalCode);
            hideLoader('#nachbauData');

            hideAndLoaderProjectTabs();
            showDtoNumbersByType();
            showDtoDescription();

            await showJTCollectionCardsByType();
            await getProjectData(); // Project Work Data
            await getKukoMatrix(); // Kuko Matrix
        } catch (error) {
            fireToastr('error', error.message || 'An error occurred while processing the list type data.');
            hideLoader('#nachbauData');
        }
    });
}

function showDtoNumbersByType() {
    $('#typicalAndPanels .ui.menu .item').on('click', async function () {
        removeUrlParam('dto-number');
        hideElement('#dtoNumbers');
        hideElement('#dtoDescription');
        showLoader('#nachbauData');

        const $dtoNumbersMenu = $('#dtoNumbers .ui.vertical.menu');
        $dtoNumbersMenu.empty();

        const $clickedItem = $(this);
        $('#typicalAndPanels .ui.menu .item').removeClass('active').css('font-weight', '');
        $clickedItem.addClass('active').css('font-weight', '700');

        const typeNumber = $clickedItem.data('value');
        updateUrlParameter('type-number', typeNumber);

        try {
            const nachbauTxt = getUrlParam('nachbau-no');
            const listType = getUrlParam('type');

            const dtoData = await fetchDtoNumbersByTypeNumber(nachbauTxt, listType, typeNumber);

            if (dtoData.length === 0) {
                $dtoNumbersMenu.append(`<div class="sub-item" style="margin-top: 10px; margin-left:10px;color:#888"><i class="info circle icon"></i>No DTO Numbers found</div>`);
                $dtoNumbersMenu.css('width', '15rem');
            } else {
                $.each(dtoData, (index, item) => {
                    $dtoNumbersMenu.append(`<a class="item" data-value="${item.dto_number}" data-desc="${escapeHtml(item.description)}" style="display: flex; align-items: flex-start;line-height: 1.3rem;">
                                <strong class="dtoNumber" style="white-space: nowrap;">${formatDtoNumber(item.dto_number)}</strong>
                                <span class="dtoDescription" style="font-size: small; font-weight: lighter; margin-left: 10px; display: inline-block; text-align: left;">
                                    ${formatDescription(item.description, 4)}
                                </span>
                            </a>`);
                });
                $dtoNumbersMenu.attr('style', 'width: 45rem !important;');
            }

            $('#dtoNumbers').transition('pulse');
            showDtoDescription();
            hideLoader('#nachbauData');

            hideAndLoaderProjectTabs();
            await showJTCollectionCardsByType();
            await getProjectData();
            await getKukoMatrix();
        } catch (error) {
            fireToastr('error', error.message || 'An error occurred while processing the list type data.');
            hideLoader('#nachbauData');
        }
    });
}

function showDtoDescription() {
    $('#dtoNumbers .ui.menu .item').on('click', async function () {
        hideElement('#dtoDescription');
        showLoader('#nachbauData');

        const $dtoNumbersMenu = $('#dtoNumbers .ui.vertical.menu');
        const $dtoDescriptionMenu = $('#dtoDescription .ui.vertical.menu');
        $dtoDescriptionMenu.empty();

        const $clickedItem = $(this);
        $('#dtoNumbers .ui.menu .item').removeClass('active').css('font-weight', '');
        $clickedItem.addClass('active').css('font-weight', '700');

        const dtoNumber = $clickedItem.data('value');
        const dtoDescription = $clickedItem.data('desc');
        updateUrlParameter('dto-number', dtoNumber);

        $dtoDescriptionMenu.append(`<div class="item" style="background-color:#f3f3f3;text-align:center;">
                                        <a id="btnGoToTkForm" class="ui button teal">Open TK Form</a>
                                        <hr style="margin-bottom:-0.8rem;">
                                        <span style="line-height:1.5rem;color:#444; font-weight:bold;" >${dtoDescription.split("V:").join('<br>')}</span>
                                    </div>`);

        $dtoNumbersMenu.attr('style', 'width: 15rem !important');
        $dtoNumbersMenu.find('span').hide();
        $dtoDescriptionMenu.css('width', '23rem');

        $('#dtoDescription').transition('pulse');
        hideLoader('#nachbauData');

        hideAndLoaderProjectTabs();
        await getProjectData(); // Project Work Data
        await getKukoMatrix(); // Kuko Matrix
    });
}

async function populateTypicalAndPanelsData(listType, nachbauDataWithType) {

    // Populate the Typical/Panel menu
    const $typicalAndPanelsMenu = $('#typicalAndPanels .ui.vertical.menu');
    $typicalAndPanelsMenu.empty();

    if (nachbauDataWithType.length > 0) {
        if (listType === 'Typical') {
            $.each(nachbauDataWithType, function (index, [typicalNumber, typicalDetails]) {
                const groupId = `group-${index}`; // Unique ID for each group
                const $group = $(`<div class="item group" id="${groupId}" data-value="${typicalNumber}" style="cursor: pointer;"></div>`);

                $group.append(`<div class="typical-item" style="font-weight: bold;">${typicalNumber}</div>`);


                const ortzKzKeys = Object.entries(typicalDetails || {}); // [{"+C11": {...}}, {"+C12": {...}}]
                if (ortzKzKeys.length > 0) {
                    const $subList = $('<div class="sub-list"></div>'); // Create a sub-list container

                    //1 satırda 2 ortz kz yapmak için, refactr edilebilir.
                    for (let i = 0; i < ortzKzKeys.length; i += 2) {
                        const ortzKz1 = ortzKzKeys[i];
                        const ortzKz2 = ortzKzKeys[i + 1];

                        // Create a row with two ortz_kz numbers
                        const row = `<div class="sub-item-row" style="display: flex; gap: 10px; margin-top: 10px;">
                                                        <div>${ortzKz1 ? `${ortzKz1[0]} (${ortzKz1[1]?.panel_no || 'N/A'})` : ''}</div>
                                                        <div>${ortzKz2 ? `${ortzKz2[0]} (${ortzKz2[1]?.panel_no || 'N/A'})` : ''}</div>
                                                    </div>`;

                        $subList.append(row); // Add the row to the sub-list
                    }
                    $group.append($subList); // Add the sub-list to the group
                } else {
                    $group.append(`<div class="sub-item" style="margin-top: 10px; color: #888;">No panels available</div>`);
                }

                $typicalAndPanelsMenu.append($group);
                $typicalAndPanelsMenu.css('width', '17rem');
            });
        }
        else if (listType === 'Panel') {
            $.each(nachbauDataWithType, function (index, [ortzKz, panelDetails]) {
                const typicalNo = panelDetails.typical_no || 'N/A';
                const panelNo = panelDetails.panel_no || 'N/A';

                $typicalAndPanelsMenu.append(`<a class="item" data-value="${ortzKz}" >
                                                <span style="color: #888;"> ${typicalNo}</span> <br>
                                                <p style="margin-top:0.3rem;"><strong>${ortzKz} (${panelNo})</strong></p>
                                            </a>`);
            });
            $typicalAndPanelsMenu.css('width', '15rem');
        }
    } else if (listType === 'Accessories'){
        hideElement('#typicalAndPanels');
    }
}

async function populateDtoNumbersData(nachbauTxt, listType, accessoryTypicalCode) {
    const $dtoNumbersMenu = $('#dtoNumbers .ui.vertical.menu');
    $dtoNumbersMenu.empty();

    let dtoData;
    if (listType === 'Accessories')
        dtoData = await fetchOnlyAccessoryDtos(nachbauTxt, accessoryTypicalCode);
    else
        dtoData = await fetchAllTypicalAndPanelDtos(nachbauTxt, accessoryTypicalCode);

    if (dtoData.length === 0) {
        $dtoNumbersMenu.append(`<div class="sub-item" style="margin-top: 10px; margin-left:10px;color:#888"><i class="info circle icon"></i>No DTO Numbers found</div>`);
        $dtoNumbersMenu.attr('style', 'width: 15rem !important');
    } else {
        $.each(dtoData, (index, item) => {
            $dtoNumbersMenu.append(`<a class="item" data-value="${item.dto_number}" data-desc="${escapeHtml(item.description)}" style="display: flex; align-items: flex-start;line-height: 1.3rem;">
                                <strong class="dtoNumber" style="white-space: nowrap;">${formatDtoNumber(item.dto_number)}</strong>
                                <span class="dtoDescription" style="font-size: small; font-weight: lighter; margin-left: 10px; display: inline-block; text-align: left;">
                                    ${formatDescription(item.description, 4)}
                                </span>
                            </a>`);
        });

        $dtoNumbersMenu.attr('style', 'width: 45rem !important;');
    }

    if (listType !== 'Accessories')
        $('#typicalAndPanels').transition('pulse');

    $('#dtoNumbers').transition('pulse');
}

async function fetchDtoNumbersByTypeNumber(nachbauTxt, listType, typeNumber) {
    const projectNo = getUrlParam('project-no');
    const url = '/dpm/dtoconfigurator/api/controllers/NachbauController.php';
    const response = await axios.get(url, {
        params: { action: 'getDtoNumbersByTypeNumber', projectNo: projectNo, nachbauNo: nachbauTxt, listType: listType, typeNumber: typeNumber },
        headers: { "Content-Type": "multipart/form-data" },
    });

    if (response.status === 200) {
        return response.data;
    } else {
        fireToastr('error', response.message);
        throw new Error(response.message);
    }
}

async function fetchAllTypicalAndPanelDtos(nachbauTxt, accessoryTypicalCode) {
    const projectNo = getUrlParam('project-no');
    const url = '/dpm/dtoconfigurator/api/controllers/NachbauController.php';
    const response = await axios.get(url, {
        params: { action: 'getAllTypicalAndPanelDtos', projectNo: projectNo, nachbauNo: nachbauTxt, accessoryTypicalCode: accessoryTypicalCode },
        headers: { "Content-Type": "multipart/form-data" },
    });

    if (response.status === 200) {
        return response.data;
    } else {
        fireToastr('error', response.message);
        throw new Error(response.message);
    }
}

async function fetchOnlyAccessoryDtos(nachbauTxt, accessoryTypicalCode) {
    const projectNo = getUrlParam('project-no');
    const url = '/dpm/dtoconfigurator/api/controllers/NachbauController.php';
    const response = await axios.get(url, {
        params: { action: 'getOnlyAccessoryDtos', projectNo: projectNo, nachbauNo: nachbauTxt, accessoryTypicalCode: accessoryTypicalCode },
        headers: { "Content-Type": "multipart/form-data" },
    });

    if (response.status === 200) {
        return response.data;
    } else {
        fireToastr('error', response.message);
        throw new Error(response.message);
    }
}

async function fetchCountOfListTypes(nachbauTxt, accessoryTypicalCode) {
    const response = await axios.get('/dpm/dtoconfigurator/api/controllers/NachbauController.php', {
        params: { action: 'getCountOfListTypes', projectNo: getUrlParam('project-no'), nachbauNo: nachbauTxt, accessoryTypicalCode: accessoryTypicalCode }
    });

    if (response.status === 200) {
        return response.data;
    } else {
        fireToastr('error', response.message);
        throw new Error(response.message);
    }
}

async function getProjectCurrentStatus(nachbauTxt) {
    const response = await axios.get('/dpm/dtoconfigurator/api/controllers/NachbauController.php', {
        params: { action: 'getProjectCurrentStatus', projectNo: getUrlParam('project-no'), nachbauNo: nachbauTxt }
    });

    if (response.status === 200) {
        return response.data;
    } else {
        fireToastr('error', response.message);
        throw new Error(response.message);
    }
}

async function fetchNachbauData(projectNo) {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/NachbauController.php', {
            params: { action: 'getNachbauDataOfProject', projectNo: projectNo }
        });
        NachbauDataOfProject = response.data;
        return response.data;
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        $('#nachbauErrorMsgDiv .ui.segment').css('background-color', '#f3f3f0');
        $('#nachbauErrorMsg').html(`<i class="exclamation circle icon red big"></i> 
                                           <h2 style="margin:0;">${errorMessage}</h2>`);
        showElement('#nachbauErrorMsgDiv');
    }
}

function hideAndLoaderProjectTabs(){
    //KUKO MATRIX HIDE
    showLoader('#kukoMatrix');
    hideElement('#kukoMatrixNotFoundMsg');
    hideElement('#kukoMatrixContainer');

    //PROJECT WORK HIDE
    showLoader('#projectWork');
    hideElement('#projectNotWorkedMessage');
    hideElement('#projectWorkDataGrid');
    hideElement('#projectWorkContainer');


    hideElement('#jtCollectionCards'); // JT TAB
    hideElement('#jtCollectionInfo'); // JT Info Msg
    hideElement('#jtCollectButton'); //JT Btn
    showElement('#jtCollectionMessage'); // JT Warning Msg
    $('#jtCollectionFilterHeader').html('');
}

$(document).on('click', '#btnGoToTkForm', async function () {
    const dtoNumber = formatDtoNumber(getUrlParam('dto-number'));

    await axios.get(`/dpm/dtoconfigurator/api/controllers/TkFormController.php?action=getTkFormByDtoNumber&dtoNumber=${dtoNumber}`)
        .then(response => {
            const { id, document_number } = response.data;
            const url = `/dpm/dtoconfigurator/core/tkform/detail/info.php?id=${id}&document-number=${document_number}&dto-number=${dtoNumber}`;
            window.open(url, '_blank');
        })
        .catch(error => {
            const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
            showErrorDialog('TK Form has not been created yet.');
        });
});
