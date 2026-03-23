let selectedNachbauExtraDtos = []; // Seçili aktarımda bulunan ancak transferTo aktarımda bulunmayan DTO Listesi
let transferToNachbauExtraDtos = []; // TransferTo aktarımında bulunan ancak seçili aktarımda bulunmayan DTO Listesi

async function initializeNachbauOperationSelectBoxes() {
    const currentNachbau = getUrlParam('nachbau-no');
    const nachbauTxts = Object.keys(NachbauDataOfProject);

    // Get the select boxes
    const $currentNachbauSelect = $('#currentNachbau');
    const $allNachbausSelect = $('#allNachbaus');

    // Clear the select boxes
    $currentNachbauSelect.empty();
    $allNachbausSelect.empty();

    // Add a default option for Current Nachbau
    $currentNachbauSelect.append(`<option value="">Select Current Nachbau</option>`);
    $allNachbausSelect.append(`<option value="">Select All Nachbaus</option>`);

    // Populate options in both select boxes
    nachbauTxts.forEach((nachbau) => {
        const isSelected = nachbau === currentNachbau ? 'selected' : '';
        $currentNachbauSelect.append(`<option value="${nachbau}" ${isSelected}>${nachbau}</option>`);
        $allNachbausSelect.append(`<option value="${nachbau}">${nachbau}</option>`);
    });

    $currentNachbauSelect.dropdown({
        allowAdditions: false,
        selectOnKeydown: false
    });

    $allNachbausSelect.dropdown({
        allowAdditions: false,
        selectOnKeydown: false,
        clearable: true
    });

    hideLoader('#nachbauOperations');
    showElement('#nachbauOperationsContainer');
}

$(document).on('click', '#btnCompareNachbaus', function () {
    const projectNo = getUrlParam('project-no');
    const sbCurrentNachbau = $('#currentNachbau').val();
    const sbAllNachbaus = $('#allNachbaus').val();

    $('#allNachbausErrorMsg').hide();

    if (!sbAllNachbaus) {
        $('#allNachbausErrorMsg').text('Please select a Nachbau file to compare!').transition('pulse');
        return;
    }

    if(sbCurrentNachbau !== sbAllNachbaus){
        const compareUrl = `/masterplanning/comparetxt.php?project=${projectNo}&first=${sbCurrentNachbau}&second=${sbAllNachbaus}`;
        window.open(compareUrl, '_blank');
    }
    else {
        $('#allNachbausErrorMsg').text('Please select a different Nachbau file, not the current one!').transition('pulse');
    }
});

$(document).on('click', '#btnCheckNachbauDifferences', async function () {
    const projectNo = getUrlParam('project-no');
    const sbCurrentNachbau = $('#currentNachbau').val();
    const sbAllNachbaus = $('#allNachbaus').val();

    if (!sbAllNachbaus) {
        $('#allNachbausErrorMsg').text('Please select a Nachbau file to transfer!').transition('pulse');
        return;
    }

    if (sbCurrentNachbau !== sbAllNachbaus) {
        $('#btnCheckNachbauDifferences').addClass('loading disabled');
        showElement('#nachbauDifferencesGrid');

        const $dtosNotExistInTransferToNachbau = $('#dtosNotExistInTransferToNachbau .ui.list');
        const $newDtosInTransferToNachbau = $('#newDtosInTransferToNachbau .ui.list');
        $dtosNotExistInTransferToNachbau.empty();
        $newDtosInTransferToNachbau.empty();

        try {
            const dtoDifferences = await fetchNachbauTransferDtoDifferences(projectNo, sbCurrentNachbau, sbAllNachbaus);

            selectedNachbauExtraDtos = dtoDifferences.selectedNachbauExtraDtos;
            transferToNachbauExtraDtos = dtoDifferences.transferToNachbauExtraDtos;

            if (selectedNachbauExtraDtos.length === 0 && transferToNachbauExtraDtos.length === 0) {
                hideElement('#nachbauDifferencesGrid');
            } else {
                // Append items to the "Not Exists" section
                selectedNachbauExtraDtos.forEach(item => {
                    $dtosNotExistInTransferToNachbau.append(
                        `<div class="item"><i class="minus circle icon"></i><div class="content">${item}</div></div>`
                    );
                });

                // Append items to the "New DTO Numbers" section
                transferToNachbauExtraDtos.forEach(item => {
                    $newDtosInTransferToNachbau.append(
                        `<div class="item"><i class="plus circle icon"></i><div class="content">${item}</div></div>`
                    );
                });
            }

            // Update values in the modal
            $('.currentNachbauVal').text(sbCurrentNachbau);
            $('.currentNachbauTime').text(NachbauDataOfProject[sbCurrentNachbau]['Time']);
            $('.transferToNachbauVal').text(sbAllNachbaus);
            $('.transferToNachbauTime').text(NachbauDataOfProject[sbAllNachbaus]['Time']);

            // Show the modal
            $('#nachbauDifferencesModal')
                .data('triggerSource', 'transfer')
                .modal('show');
            $('#btnCheckNachbauDifferences').removeClass('loading disabled');
        } catch (error) {
            console.error('Error fetching DTO differences:', error.message);
            $('#btnCheckNachbauDifferences').removeClass('loading disabled');
        }
    }
    else {
        $('#allNachbausErrorMsg').text('Please select a different Nachbau file, not the current one!').transition('pulse');
    }
});

async function fetchNachbauTransferDtoDifferences(projectNo, sbCurrentNachbau, sbAllNachbaus) {
    try {
        const url = '/dpm/dtoconfigurator/api/controllers/NachbauTransferController.php';
        const response = await axios.get(url, {
            params: { action: 'checkNachbauTransferDtoDifferences', projectNo: projectNo, selectedNachbau: sbCurrentNachbau, transferToNachbau: sbAllNachbaus },
            headers: { "Content-Type": "multipart/form-data" },
        });
        return response.data;
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`<b>${errorMessage}</b>`);
    }
}

$(document).on('click', '#btnConfirmNachbauTransfer', async function () {
    $(this).addClass('loading disabled');
    const triggerSource = $('#nachbauDifferencesModal').data('triggerSource');

    let selectedNachbau;
    let transferToNachbau;
    let title;

    if (triggerSource === 'revision') {
        selectedNachbau = $('#nachbauDifferencesModal').data('publishedNachbauNo');
        transferToNachbau = $('#nachbauDifferencesModal').data('nachbauNo');
        selectedNachbauExtraDtos = $('#nachbauDifferencesModal').data('publishedNachbauExtraDtos');
        transferToNachbauExtraDtos = $('#nachbauDifferencesModal').data('targetNachbauExtraDtos');
        title = "Preparing for Revision...";
    }  else if (triggerSource === 'transfer') {
        selectedNachbau = $('#currentNachbau').val();
        transferToNachbau = $('#allNachbaus').val();
        title = "Nachbau Transfer in Progress...";
    }

    const swalHtmlContent = `<div style="text-align: left; font-size: 1.1rem; line-height: 1.5;">
                                        <div style="margin-bottom: 1rem; padding: 0.5rem; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;">
                                            <p style="margin: 0.5rem 0;"><strong>From:</strong> <span style="color: #555;">${selectedNachbau}</span></p>
                                            <p style="margin: 0.5rem 0;"><strong>Target Nachbau:</strong> <span style="color: #555;">${transferToNachbau}</span></p>
                                        </div>
                                        <p style="margin-top: 1rem; font-size: 1rem;">
                                            <b>Please wait... This may take up to a minute.</b>
                                        </p>
                                    </div>`;

    Swal.fire({
        title: title,
        html: swalHtmlContent,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        const response = await axios.post(
            '/dpm/dtoconfigurator/api/controllers/NachbauTransferController.php?',
            {
                action: 'transferNachbauToAnother',
                projectNo: getUrlParam('project-no'),
                selectedNachbau: selectedNachbau,
                transferToNachbau: transferToNachbau,
                selectedNachbauExtraDtos: selectedNachbauExtraDtos,
                transferToNachbauExtraDtos: transferToNachbauExtraDtos,
                accessoryTypicalNumber: NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryTypicalCode'],
                accessoryParentKmat: NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryParentKmat']
            },
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );

        Swal.close();

        if (response.status === 200) {
            const skipped = response.data?.skipped || {};
            const total = (skipped?.projectWorks?.length || 0) +
                (skipped?.spare?.length || 0) +
                (skipped?.interchange?.length || 0) +
                (skipped?.extension?.length || 0) +
                (skipped?.minusPrice?.length || 0) +
                (skipped?.special?.length || 0);

            if (total > 0) {
                // Skipped kayıtlar varsa modal göster
                showSkippedModal(skipped, selectedNachbau, transferToNachbau);
            } else {
                // Hepsi başarılı
                showSuccessDialog('Nachbau transferred successfully.').then(() => {
                    getOrderSummaryV2();
                    $('#nachbauDifferencesModal').modal('hide');
                });
            }

            await updateProjectRevisionStatus(getUrlParam('project-no'), getUrlParam('nachbau-no'));
        } else {
            showErrorDialog(`<b>${response.data?.message || 'Transfer failed'}</b>`);
        }
    } catch (error) {
        Swal.close();
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`<b>${errorMessage}</b>`);
    } finally {
        $(this).removeClass('loading disabled');
    }
});


function showSkippedModal(skipped, fromNachbau, toNachbau) {
    // DTO Type Configurations
    const dtoTypes = {
        projectWorks: {
            label: 'Project Works',
            icon: 'tasks',
            color: '#e91e63',
            columns: ['DTO Number',  'Added', 'Deleted', 'Description', 'Qty', 'Unit', 'Typical', 'Panel', 'KMAT', 'Skip Reason']
        },
        spare: {
            label: 'Spare DTOs',
            icon: 'tasks',
            color: '#9c27b0',
            columns: ['DTO Number', 'Added', 'Deleted', 'Description', 'Typical', 'Panel', 'KMAT', 'Skip Reason']
        },
        interchange: {
            label: 'Interchange DTOs',
            icon: 'exchange',
            color: '#2196F3',
            columns: ['DTO Number',  'Added', 'Deleted', 'Description', 'Typical', 'Panel', 'KMAT', 'Skip Reason']
        },
        extension: {
            label: 'Extension DTOs',
            icon: 'expand arrows alternate',
            color: '#9C27B0',
            columns: ['DTO Number',  'Added', 'Deleted', 'Description', 'Typical', 'Panel', 'KMAT', 'Skip Reason']
        },
        minusPrice: {
            label: 'Minus Price DTOs',
            icon: 'dollar sign',
            color: '#FF9800',
            columns: ['DTO Number', 'Added', 'Deleted', 'Description', 'Typical', 'Panel', 'KMAT', 'Skip Reason']
        },
        special: {
            label: 'Special DTOs',
            icon: 'star',
            color: '#4CAF50',
            columns: ['DTO Number',  'Added', 'Deleted', 'Description', 'Typical', 'Panel', 'KMAT', 'Skip Reason']
        }
    };

    // Set transfer info
    $('#skippedFromNachbau').text(fromNachbau);
    $('#skippedToNachbau').text(toNachbau);

    const hasSkippedWorks = Object.values(skipped).some(arr => arr && arr.length > 0);
    if (!hasSkippedWorks) {
        $('#skippedTabMenu').html('');
        $('#skippedTabContents').html('');

        $('#skippedTabContents').html(`
            <div class="ui success icon message" style="width: 100%;">
                <i class="check circle icon"></i>
                <div class="content">
                    <div class="header">All Works Transferred Successfully!</div>
                    <p>No works were skipped during the transfer from <b>${fromNachbau}</b> to <b>${toNachbau}</b>.</p>
                </div>
            </div>
        `);

        $('#transferSkippedModal').modal({
            closable: false,
            transition: 'fade up',
            duration: 400
        }).modal('show');

        return;
    }

    let cardsHtml = '';
    let firstTab = null;

    Object.keys(dtoTypes).forEach(type => {
        const count = skipped[type]?.length || 0;
        const config = dtoTypes[type];

        // Sadece count > 0 olanları göster
        if (count === 0) return;

        if (!firstTab) firstTab = type;

        cardsHtml += `
        <div class="card" style="border-top: 4px solid ${config.color};">
            <div class="content">
                <div class="header" style="color: ${config.color};">
                    <i class="${config.icon} icon"></i>
                    ${count}
                </div>
                <div class="meta">${config.label}</div>
            </div>
        </div>
    `;
    });

    $('#skippedSummaryCards').html(cardsHtml);

    // Generate tabs and contents
    let tabsHtml = '';
    let contentsHtml = '';

    Object.keys(dtoTypes).forEach(type => {
        const items = skipped[type] || [];
        if (items.length === 0) return;

        const config = dtoTypes[type];
        const isActive = type === firstTab ? 'active' : '';

        // Tab header
        tabsHtml += `
            <a class="item ${isActive}" data-tab="${type}" style="border-top: 3px solid ${config.color};">
                <i class="${config.icon} icon" style="color: ${config.color};"></i>
                ${config.label}
                <div class="ui ${config.color.replace('#', '')} label">${items.length}</div>
            </a>
        `;

        // Tab content
        contentsHtml += `
            <div class="ui tab ${isActive}" data-tab="${type}">
                <div class="ui icon input" style="width: 100%; margin-bottom: 1rem;">
                    <input type="text" placeholder="Search in ${config.label}..." onkeyup="filterSkippedTable('${type}', this.value)">
                    <i class="search icon"></i>
                </div>
                ${generateSkippedTable(type, items, config)}
            </div>
        `;
    });

    $('#skippedTabMenu').html(tabsHtml);
    $('#skippedTabContents').html(contentsHtml);

    // Initialize Semantic UI tabs
    $('.menu .item').tab();

    // Show modal
    $('#transferSkippedModal').modal({
        closable: false,
        transition: 'fade up',
        duration: 400,
        onVisible: function() {
            Object.keys(dtoTypes).forEach(type => {
                const tableId = `#skippedTable_${type}`;
                if ($(tableId).length > 0) {
                    $(tableId).DataTable({
                        fixedHeader: true,
                        paging: false,
                        ordering: false,
                        searching: false
                    });
                }
            });
        }
    }).modal('show');
}

function generateSkippedTable(type, items, config) {
    if (items.length === 0) {
        return '<div class="ui info message">No skipped items in this category.</div>';
    }

    let html = `
        <table class="ui celled striped table" id="skippedTable_${type}" style="font-size:0.9em;">
            <thead>
                <tr style="background: ${config.color}; color: white;">
    `;

    config.columns.forEach(col => {
        html += `<th>${col}</th>`;
    });

    html += `
                </tr>
            </thead>
            <tbody>
    `;

    items.forEach((item, idx) => {
        const operationClass = item.operation ? `row-${item.operation}` : '';
        let description = '';
        let addedStyle = '';
        let deletedStyle = '';

        if (item.operation === 'add') {
            addedStyle = 'font-weight: bold; color: green;';
        } else if (item.operation === 'replace') {
            addedStyle = 'font-weight: bold; color: green;';
            deletedStyle = 'font-weight: bold; color: red;';
        } else if (item.operation === 'delete') {
            addedStyle = 'font-weight: bold; color: red;';
            deletedStyle = 'font-weight: bold; color: red;';
        }

        html += `<tr class="${operationClass}">`;

        switch(type) {
            case 'projectWorks':
                const panel = (item.ortz_kz || '') + '/' + (item.panel_no || '');

                description = item.operation === 'add'
                    ? (item.material_added_description || '')
                    : (item.material_deleted_description || '');

                html += `
                    <td><strong>${item.dto_number || ''}</strong></td>
                    <td style="${addedStyle}">${item.material_added_number || ''}</td>
                    <td style="${deletedStyle}">${item.material_deleted_number || ''}</td>
                    <td>${description}</td>
                    <td>${item.quantity || ''}</td>
                    <td>${item.unit || ''}</td>
                    <td>${item.typical_no || ''}</td>
                    <td>${panel}</td>
                    <td style="text-align:center;"><a class="ui violet label">${item.work_center}</a><br>
                        <h5 style="margin-top:2%;">${item.work_content}</h5>
                    </td>
                    <td><span class="ui small red label basic"><i class="warning icon"></i>${item.skip_reason}</span></td>
                `;
                break;

            case 'spare':
                const kmatSpare = (item.work_center || '') + '<br>' + (item.work_content || '');
                const panelSpare = (item.ortz_kz || '') + '/' + (item.accessory_panel_no || item.panel_no || '');

                html += `
                    <td><strong>${item.dto_number || ''}</strong></td>
                    <td style="${addedStyle}">${item.material_added_number || ''}</td>
                    <td></td>
                    <td>${item.material_added_description || ''}</td>
                    <td>${item.spare_typical_number || item.typical_no || ''}</td>
                    <td>${panelSpare}</td>
                    <td style="text-align:center;">ACCESSORY</td>
                    <td><span class="ui small red label basic"><i class="warning icon"></i>${item.skip_reason}</span></td>
                `;
                break;

            case 'interchange':
                const panelInterchange = (item.ortz_kz || '') + '/' + (item.panel_no || '');
                description = item.operation === 'add'
                    ? (item.material_added_description || '')
                    : (item.material_deleted_description || '');

                html += `
                    <td><strong>${item.dto_number || ''}</strong></td>
                    <td style="${addedStyle}">${item.material_added_number || ''}</td>
                    <td style="${deletedStyle}">${item.material_deleted_number || ''}</td>
                    <td>${description}</td>
                    <td>${item.typical_no || ''}</td>
                    <td>${panelInterchange}</td>
                    <td style="text-align:center;">
                        <a class="ui violet label">${item.work_center}</a><br>
                        <h5 style="margin-top:2%;">${item.work_content}</h5>
                    </td>
                    <td><span class="ui small red label basic"><i class="warning icon"></i>${item.skip_reason}</span></td>
                `;
                break;

            case 'extension':
                const panelExtension = (item.ortz_kz || '') + '/' + (item.panel_no || '');
                description = item.operation === 'add'
                    ? (item.material_added_description || '')
                    : (item.material_deleted_description || '');

                html += `
                    <td><strong>${item.dto_number || ''}</strong></td>
                    <td style="${addedStyle}">${item.material_added_number || ''}</td>
                    <td style="${deletedStyle}">${item.material_deleted_number || ''}</td>
                    <td>${description}</td>
                    <td>${item.typical_no || ''}</td>
                    <td>${panelExtension}</td>
                    <td style="text-align:center;">
                        <a class="ui violet label">${item.work_center}</a><br>
                        <h5 style="margin-top:2%;">${item.work_content}</h5>
                    </td>
                    <td><span class="ui small red label basic"><i class="warning icon"></i>${item.skip_reason}</span></td>
                `;
                break;

            case 'minusPrice':
                const kmatMinusPrice = (item.work_center || '') + '<br>' + (item.work_content || '');
                const panelMinusPrice = (item.ortz_kz || '') + '/' + (item.panel_no || '');

                html += `
                    <td><strong>${item.dto_number || ''}</strong></td>
                    <td style="${addedStyle}">${item.material_added_number || ''}</td>
                    <td style="${deletedStyle}">${item.material_deleted_number || ''}</td>
                    <td>${material_deleted_description}</td>
                    <td>${item.dto_typical_number || item.typical_no || ''}</td>
                    <td>${panelMinusPrice}</td>
                    <td>${kmatMinusPrice}</td>
                    <td style="text-align:center;">
                        <a class="ui violet label">${item.work_center}</a><br>
                        <h5 style="margin-top:2%;">${item.work_content}</h5>
                    </td>
                    <td><span class="ui small red label basic"><i class="warning icon"></i>${item.skip_reason}</span></td>
                `;
                break;

            case 'special':
                const kmatSpecial = (item.work_center || '') + '<br>' + (item.work_content || '');
                const panelSpecial = (item.ortz_kz || '') + '/' + (item.panel_no || '');
                description = item.operation === 'add'
                    ? (item.material_added_description || '')
                    : (item.material_deleted_description || '');

                html += `
                    <td><strong>${item.dto_number || ''}</strong></td>
                    <td style="${addedStyle}">${item.material_added_number || ''}</td>
                    <td style="${deletedStyle}">${item.material_deleted_number || ''}</td>
                    <td>${description}</td>
                    <td>${item.typical_no || ''}</td>
                    <td>${panelSpecial}</td>
                    <td style="text-align:center;">${kmatSpecial}</td>
                    <td><span class="ui small red label basic"><i class="warning icon"></i>${item.skip_reason}</span></td>
                `;
                break;
        }

        html += `</tr>`;
    });

    html += `
            </tbody>
        </table>
    `;

    return html;
}

function filterSkippedTable(type, searchValue) {
    const table = document.getElementById(`skippedTable_${type}`);
    const rows = table.getElementsByTagName('tr');
    const search = searchValue.toLowerCase();

    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    }
}

function completeTransferAndClose() {
    $('#transferSkippedModal').modal('hide');
    showSuccessDialog('Transfer completed. Some items were skipped as shown in the summary.').then(() => {
        $('#nachbauDifferencesModal').modal('hide');
        location.reload();
    });
}

async function updateProjectRevisionStatus(projectNo, nachbauNo) {
    try {
        await axios.post('/dpm/dtoconfigurator/api/controllers/ProjectController.php?',
            {
                action: 'updateProjectRevisionStatus',
                projectNo: projectNo,
                nachbauNo: nachbauNo,
                revisionStatus: 7 // Start
            },
            { headers: { 'Content-Type': 'multipart/form-data' }}
        );
    } catch(error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`${errorMessage}`);
    }
}
