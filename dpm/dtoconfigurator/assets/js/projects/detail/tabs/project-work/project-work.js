let workType = '';
$('#updateNachbauButton').on('click', async function() {
    addButtonLoader('#updateNachbauButton');
    await updateProjectWork();
    // await checkRevisionAndUpdateWork();
});

async function checkRevisionAndUpdateWork() {
    try {
        const projectNo = getUrlParam('project-no');
        const nachbauNo = getUrlParam('nachbau-no');

        // NachbauDataOfProject'teki TXT dosyalarını Time'a göre sırala
        const sortedNachbauFiles = Object.keys(NachbauDataOfProject).sort((a, b) => {
            const timeA = parseDateTime(NachbauDataOfProject[a]['Time']);
            const timeB = parseDateTime(NachbauDataOfProject[b]['Time']);
            return timeA - timeB;
        });

        const selectedIndex = sortedNachbauFiles.indexOf(nachbauNo);

        // Önceki dosyaları kontrol et
        let hasPublishedBefore = false;
        let publishedNachbauNo = '';
        if (selectedIndex > 0) {
            for (let i = 0; i < selectedIndex; i++) {
                const fileKey = sortedNachbauFiles[i];
                if (NachbauDataOfProject[fileKey]['PublishStatus'] === '5') {
                    hasPublishedBefore = true;
                    publishedNachbauNo = fileKey; // Yayınlanmış dosya adını kaydet
                    break;
                }
            }
        }

        // Diğer nachbauları kontrol et (Time'dan bağımsız)
        for (const fileKey in NachbauDataOfProject) {
            // Mevcut nachbau'yu atla
            if (fileKey === nachbauNo) continue;

            if (NachbauDataOfProject[fileKey]['PublishStatus'] === '5') {
                hasPublishedBefore = true;
                publishedNachbauNo = fileKey; // Yayınlanmış dosya adını kaydet
                break;
            }
        }

        if (hasPublishedBefore) {
            const result = await Swal.fire({
                title: 'Choose Work Type',
                html: `Another nachbau <b>${publishedNachbauNo}</b> has already been <b>published</b>. What action will you take for this nachbau?`,
                icon: 'question',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                denyButtonColor: '#ff4500',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Start Revision',
                denyButtonText: 'Standard Work',
                cancelButtonText: 'Cancel',
                allowOutsideClick: false
            });

            // İptal butonuna basıldıysa işlemi durdur
            if (result.isDismissed) {
                removeButtonLoader('#updateNachbauButton');
                return;
            }

            if(result.isConfirmed) {
                // Revision
                showElement('#nachbauDifferencesGrid');
                let publishedNachbauExtraDtos = [];
                let targetNachbauExtraDtos = [];
                const $dtosNotExistInTransferToNachbau = $('#dtosNotExistInTransferToNachbau .ui.list');
                const $newDtosInTransferToNachbau = $('#newDtosInTransferToNachbau .ui.list');
                $dtosNotExistInTransferToNachbau.empty();
                $newDtosInTransferToNachbau.empty();

                try {
                    const dtoDifferences = await fetchNachbauTransferDtoDifferences(projectNo, publishedNachbauNo, nachbauNo);

                    publishedNachbauExtraDtos = dtoDifferences.selectedNachbauExtraDtos;
                    targetNachbauExtraDtos = dtoDifferences.transferToNachbauExtraDtos;

                    if (publishedNachbauExtraDtos.length === 0 && targetNachbauExtraDtos.length === 0) {
                        hideElement('#nachbauDifferencesGrid');
                    } else {
                        // Append items to the "Not Exists" section
                        publishedNachbauExtraDtos.forEach(item => {
                            $dtosNotExistInTransferToNachbau.append(
                                `<div class="item"><i class="minus circle icon"></i><div class="content">${item}</div></div>`
                            );
                        });

                        // Append items to the "New DTO Numbers" section
                        targetNachbauExtraDtos.forEach(item => {
                            $newDtosInTransferToNachbau.append(
                                `<div class="item"><i class="plus circle icon"></i><div class="content">${item}</div></div>`
                            );
                        });
                    }

                    $('.currentNachbauVal').text(publishedNachbauNo);
                    $('.currentNachbauTime').text(NachbauDataOfProject[publishedNachbauNo]['Time']);
                    $('.transferToNachbauVal').text(nachbauNo);
                    $('.transferToNachbauTime').text(NachbauDataOfProject[nachbauNo]['Time']);

                    $('#nachbauDifferencesModal')
                        .data('triggerSource', 'revision')
                        .data('publishedNachbauNo', publishedNachbauNo)
                        .data('nachbauNo', nachbauNo)
                        .data('publishedNachbauExtraDtos', publishedNachbauExtraDtos)
                        .data('targetNachbauExtraDtos', targetNachbauExtraDtos)
                        .modal('show');

                    await updateProjectRevisionStatus(projectNo, nachbauNo);
                } catch (error) {
                    console.error('Error fetching DTO differences:', error.message);
                    const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                    showErrorDialog(`<b>${errorMessage}</b>`);
                }

            } else {
                // Standard Work
                await updateProjectWork();
            }
        } else {
            await updateProjectWork();
        }
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(errorMessage);
    } finally {
        removeButtonLoader('#updateNachbauButton');
    }
}

async function updateProjectWork() {
    try {
        const projectNo = getUrlParam('project-no');
        const nachbauNo = getUrlParam('nachbau-no');
        const accessoryTypicalNumber = NachbauDataOfProject[nachbauNo]['AccessoryTypicalCode'];
        const accessoryParentKmat = NachbauDataOfProject[nachbauNo]['AccessoryParentKmat'];

        await axios.post('/dpm/dtoconfigurator/api/controllers/ProjectController.php?',
            {
                action: 'updateProjectWork',
                projectNo: projectNo,
                nachbauNo: nachbauNo,
                accessoryTypicalNumber: accessoryTypicalNumber,
                accessoryParentKmat: accessoryParentKmat
            },
            { headers: { 'Content-Type': 'multipart/form-data' }}
        );

    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(errorMessage)
    } finally {
        await getProjectData();
    }
}

async function getProjectData() {
    enableElementsInProjectWork();

    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/ProjectController.php', {
            params: {
                action: 'getProjectData',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                type: getUrlParam('type'),
                typeNumber:getUrlParam('type-number'),
                dtoNumber: getUrlParam('dto-number'),
                accessoryTypicalCode: NachbauDataOfProject[getUrlParam('nachbau-no')]['AccessoryTypicalCode']
            }
        });

        if (response.status === 200) {
            await configurateTableButtons(response.data);
            if (SpareWdaDtosOfProject.length > 0)
                $('#isSpareWdaDtoExistButton').css('display', '');
            if (SparePartDtosOfProject.length > 0)
                $('#openSparePartDtoWorkModal').css('display', '');
            if (MinusPriceDtosOfProject.length > 0)
                $('#openMinusPriceDtoWorkModal').css('display', '');
            if (InterchangeDtosOfProject.length > 0)
                $('#chooseInterchangeDtoWorkModalButton').css('display', '');

            // Önce tüm mesajları gizle
            $('#projectWorkingStatusInfo').hide();
            $('#projectStatusMessageDiv').hide();
            $('#currentlyWorkingUserInfoMessage').hide();
            $('#projectRevisionStatusTabMessage').hide();
            $('#transferSummaryButtonDiv').hide();
            $('#withdrawPublishRequest').hide();
            $('.projectStatusMessageDiv span[id$="StatusMsg"]').hide();

            const statusId = currentlyWorkingUser.projectStatusId;

            // If the nachbau has been transferred from another nachbau
            if (currentlyWorkingUser.transferredFromNachbau)
                $('#transferSummaryButtonDiv').show();

            // Status ID 2: Someone is working
            if (statusId === '2') {
                $('#workingStatusHeader').text('Someone is working!');
                $('#currentlyWorkingUser').text(currentlyWorkingUser.name);
                $('#currentlyWorkingUserInfoMessage').show();
                $('#projectWorkingStatusInfo').show().find('.ui.message').transition('pulse');
            }
            // Status ID 7: Under Revision
            else if (statusId === '7') {
                $('#workingStatusHeader').text('Under Revision!');
                $('#revisionWorkingUser').text(currentlyWorkingUser.name);
                $('#projectRevisionStatusTabMessage').show();
                $('#projectWorkingStatusInfo').show().find('.ui.message').transition('pulse');
            }
            // Status ID 3, 5, 8, 9: Pending/Published states
            else if (['3', '5', '8', '9'].includes(statusId)) {
                disableElementsInProjectWork();
                hideElement('#checkInButton');
                hideElement('#checkOutButton');

                const $statusDiv = $('#projectStatusMessageDiv');
                const $message = $statusDiv.find('.ui.icon.message');

                $statusDiv.find('.whoSentApproval').text(currentlyWorkingUser.whoSendApproval);
                $statusDiv.find('.projectStatusName').text(currentlyWorkingUser.projectStatusName);

                // Status ID 3: Pending Approval
                if (statusId === '3') {
                    $statusDiv.find('.sendReviewDate').text(moment(currentlyWorkingUser.sendReviewDate).format('DD.MM.YYYY HH:mm'));
                    $message.removeClass('positive negative').addClass('info');
                    $('#pendingApprovalStatusMsg').show();
                }
                // Status ID 5: Published
                else if (statusId === '5') {
                    $statusDiv.find('.reviewedDate').text(moment(currentlyWorkingUser.reviewedDate).format('DD.MM.YYYY HH:mm'));
                    $message.removeClass('info negative').addClass('positive');
                    $('#publishedStatusMsg').show();
                }
                // Status ID 8: Pending Revision Approval
                else if (statusId === '8') {
                    $statusDiv.find('.sendReviewDate').text(moment(currentlyWorkingUser.sendReviewDate).format('DD.MM.YYYY HH:mm'));
                    $message.removeClass('positive negative').addClass('info');
                    $('#pendingRevisionApprovalStatusMsg').show();
                }
                // Status ID 9: Revision Published
                else if (statusId === '9') {
                    $statusDiv.find('.reviewedDate').text(moment(currentlyWorkingUser.reviewedDate).format('DD.MM.YYYY HH:mm'));
                    $message.removeClass('info negative').addClass('positive');
                    $('#revisionPublishedStatusMsg').show();
                }

                $('#withdrawPublishRequest').show();
                $statusDiv.show();
            }
            // Status ID 4: Rejected
            else if (statusId === '4') {
                const $statusDiv = $('#projectStatusMessageDiv');

                $statusDiv.find('.whoSentApproval').text(currentlyWorkingUser.whoSendApproval);
                $statusDiv.find('.projectStatusName').text(currentlyWorkingUser.projectStatusName);
                $statusDiv.find('.reviewedDate').text(moment(currentlyWorkingUser.reviewedDate).format('DD.MM.YYYY HH:mm'));
                $statusDiv.find('.ui.icon.message').removeClass('info positive').addClass('negative');
                $('#rejectedStatusMsg').show();
                $statusDiv.show();
            }


            if (!currentlyWorkingUser.isAuthorizedToWorkOnProject) {
                disableElementsInProjectWork();
                hideElement('#checkOutButton');

                if (currentlyWorkingUser.name)
                    hideElement('#checkInButton');
                else
                    showElement('#checkInButton');
            }

            await Promise.all([
                renderWorkListDataTable(response.data.workList),
                renderNotDefinedWorkCenterDataTable(response.data.notDefinedWorkCenter),
                renderNotFoundNachbauDataTable(response.data.notFoundNachbau),
                renderTkFormNotExistDataTable(response.data.tkformsNotExist),
                renderExtensionDtoWorksDataTable(ExtensionDtosOfProject)
            ]);

            hideLoader('#projectWork')
            showElement('#projectWorkContainer')
            hideElement('#projectNotWorkedMessage')
            showElement('#projectWorkDataGrid')

            //DT'ler oluşturulduktan sonra boyutuna göre gizlesin, şimdilik çözüm bu
            if(response.data.workList.length === 0){
                $('#workListContainer').removeClass('visible').removeClass('transition');
                $('#workListContainer').addClass('hide-important');
            }
            if(response.data.notFoundNachbau.length === 0){
                $('#notFoundNachbauContainer').removeClass('visible').removeClass('transition');
                $('#notFoundNachbauContainer').addClass('hide-important');
            }
            if(response.data.notDefinedWorkCenter.length === 0){
                $('#notDefinedWorkCenterContainer').removeClass('visible').removeClass('transition');
                $('#notDefinedWorkCenterContainer').addClass('hide-important');
            }
            if(response.data.tkformsNotExist.length === 0){
                $('#tkFormsNotExistContainer').removeClass('visible').removeClass('transition');
                $('#tkFormsNotExistContainer').addClass('hide-important');
            }

        }
        else if (response.status === 204) {
            // Eğer proje daha önce çalışılmadıysa
            hideLoader('#projectWork')
            showElement('#projectWorkContainer')
            hideElement('#projectWorkDataGrid')
            showElement('#projectNotWorkedMessage')
            hideElement('#projectStatusMessageDiv')
        }
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(errorMessage)
    } finally {
        removeButtonLoader('#updateNachbauButton');
    }
}

async function configurateTableButtons(tableData) {
    // Map table data keys to buttons and containers
    const tableMapping = [
        { buttonId: '#notDefinedWorkCenterButton', containerId: '#notDefinedWorkCenterContainer', data: tableData.notDefinedWorkCenter },
        { buttonId: '#workListButton', containerId: '#workListContainer', data: tableData.workList },
        { buttonId: '#notFoundNachbau', containerId: '#notFoundNachbauContainer', data: tableData.notFoundNachbau },
        { buttonId: '#tkformsNotExistButton', containerId: '#tkFormsNotExistContainer', data: tableData.tkformsNotExist },
        { buttonId: '#extensionDtoListButton', containerId: '#extensionDtoListContainer', data: ExtensionDtosOfProject }
    ];

    // Loop through mapping to show/hide buttons and containers
    tableMapping.forEach(({ buttonId, containerId, data }) => {
        if (data && data.length > 0) {
            // Show button and container if data exists
            $(buttonId).show().addClass('active');
            $(containerId).show();
        } else {
            // Hide button and container if no data
            $(buttonId).hide();
            $(containerId).hide();
        }
    });

    // Attach toggle logic for visible buttons
    tableMapping.forEach(({ buttonId, containerId }) => {
        $(buttonId).off('click').on('click', function () {
            const button = $(this);
            const container = $(containerId);

            // Toggle active class and visibility
            if (button.hasClass('active')) {
                button.removeClass('active');
                container.transition('fade out');
            } else {
                button.addClass('active');
                container.transition('fade in');
            }
        });
    });
}

$('#removeAllProjectData').on('click', async function () {
    const projectNo = getUrlParam('project-no');
    const nachbauNo = getUrlParam('nachbau-no');

    Swal.fire({
        title: 'Are you sure?',
        text: `Do you really want to delete ${nachbauNo} work data?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        confirmButtonColor: "#d33",
        cancelButtonText: 'Cancel',
        showLoaderOnConfirm: true, // Shows a loading spinner
        allowOutsideClick: true,  // Prevents closing the popup by clicking outside
        preConfirm: async () => {
            try {
                await axios.post('/dpm/dtoconfigurator/api/controllers/ProjectController.php?',
                    { action: 'removeAllProjectWorkData', projectNo: projectNo, nachbauNo: nachbauNo },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                showSuccessDialog('Project Work data deleted successfully.').then(() => {
                    getProjectData(); // Refresh project data
                    getOrderSummaryV2(); // Refresh Order Summary Table
                });

            } catch (error) {
                // If an error occurs, throw it to show an error alert
                const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                showErrorDialog(`Error: ${errorMessage}`);
            }
        }
    });
});

$('#refreshProjectData').on('click', async function () {
    $('#refreshProjectData').addClass('loading disabled');
    hideElement('#projectWorkContainer')
    showLoader('#projectWork');

    await getProjectData();

    hideLoader('#projectWork');
    $('#projectWorkContainer').transition('pulse');
    $('#refreshProjectData').removeClass('loading disabled');
});

$('#materialDefineModalButton').on('click', async function () {
    window.open('/dpm/dtoconfigurator/core/material-define/index.php', '_blank');
});

$('input[name=search-tables]').on('keyup', (e) => {
    let value = e.target.value
    $('#workListDataTable').DataTable().search(value).draw()
    $('#notFoundNachbauDataTable').DataTable().search(value).draw()
    $('#notDefinedWorkCenterDataTable').DataTable().search(value).draw()
    $('#tkformsNotExistDataTable').DataTable().search(value).draw()
})

// Disable buttons and dropdowns in the current table
function disableElementsInProjectWork() {
    $('#projectWork').addClass('disabled');
    $('#projectWork .button').addClass('disabled');
    $('#projectWork .dropdown').addClass('disabled');
    $('#workListDataTable tbody tr td:first-child').addClass('disabled');
    $('#projectWork #checkInButton').removeClass('disabled');
    $('#publishProjectContainer .button').addClass('disabled');

    $('#orderSummaryV2Container #nachbauErrorButton').addClass('disabled');
    $('#bomNotes').addClass('disabled');
    $('#bomNotes .button').addClass('disabled');

    $('#kukoMatrix').addClass('disabled');
    $('#kukoMatrix .kukoNoteBtn').addClass('disabled');
}

function enableElementsInProjectWork() {
    $('#projectWork').removeClass('disabled');
    $('#projectWork .button').removeClass('disabled');
    addButtonLoader('#updateNachbauButton');
    $('#projectWork .dropdown').removeClass('disabled');
    $('#workListDataTable tbody tr td:first-child').removeClass('disabled');
    $('#publishProjectContainer .button').removeClass('disabled');

    $('#orderSummaryV2Container #nachbauErrorButton').removeClass('disabled');
    $('#bomNotes').removeClass('disabled');
    $('#bomNotes .button').removeClass('disabled');

    $('#kukoMatrix').removeClass('disabled');
    $('#kukoMatrix .kukoNoteBtn').removeClass('disabled');
}

$('#workListDataTable').on('draw.dt', function() {
    if (!currentlyWorkingUser.isAuthorizedToWorkOnProject) {
        disableElementsInProjectWork();
    }
});

$('#checkOutButton').on('click', async function () {
    showConfirmationDialog({
        title: 'Check out project?',
        htmlContent: 'Are you sure checking out project to allow others to check in and make changes?',
        confirmButtonText: 'Yes',
        confirmButtonColor: "green",
        onConfirm: async function () {
            try {
                await axios.post('/dpm/dtoconfigurator/api/controllers/ProjectController.php?',
                    {
                        action: 'checkOutProject', projectNo: getUrlParam('project-no'), nachbauNo: getUrlParam('nachbau-no')
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                showSuccessDialog('User ' + currentlyWorkingUser.name + ' checked out project successfully.').then(() => {
                    currentlyWorkingUser.name = "";
                    currentlyWorkingUser.isAuthorizedToWorkOnProject = false;
                    showLoader('#projectWork');
                    hideElement('#projectNotWorkedMessage');
                    hideElement('#projectWorkDataGrid');
                    hideElement('#projectWorkContainer');
                    getProjectData();
                    hideElement('#checkOutButton');
                    showElement('#checkInButton');
                });
            } catch (error) {
                const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                showErrorDialog(`Error: ${errorMessage}`);
            }
        },
    });
});

$('#checkInButton').on('click', async function () {
    try {
        const response = await axios.post('/dpm/dtoconfigurator/api/controllers/ProjectController.php?',
            {
                action: 'checkInProject', projectNo: getUrlParam('project-no'), nachbauNo: getUrlParam('nachbau-no')
            },
            { headers: { 'Content-Type': 'multipart/form-data' }}
        );

        const checkInUser = response.data;

        showSuccessDialog('User ' + checkInUser + ' checked in project successfully.').then(() => {
            currentlyWorkingUser.name = checkInUser;
            currentlyWorkingUser.isAuthorizedToWorkOnProject = true;
            showLoader('#projectWork');
            hideElement('#projectNotWorkedMessage');
            hideElement('#projectWorkDataGrid');
            hideElement('#projectWorkContainer');
            getProjectData();
            hideElement('#checkInButton');
            showElement('#checkOutButton');
        });
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    }
});

$('#withdrawPublishRequest').on('click', async function () {
    showConfirmationDialog({
        title: 'Withdraw Publish Request?',
        htmlContent: `<b>BOM Excel file will be removed from Mekanik folder.</b> <br><br> Are you sure?`,
        confirmButtonText: 'Yes',
        confirmButtonColor: "green",
        onConfirm: async function () {
            try {
                const projectNo = getUrlParam('project-no');
                const nachbauNo = getUrlParam('nachbau-no');

                await axios.post('/dpm/dtoconfigurator/api/controllers/ProjectController.php?',
                    {
                        action: 'withdrawPublishRequest', projectNo: projectNo, nachbauNo: nachbauNo
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                showSuccessDialog(`Publish request for ${projectNo} - ${nachbauNo} has been withdrawn.`).then(() => {
                    location.reload();
                });
            } catch (error) {
                const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                showErrorDialog(`<b>${errorMessage}</b>`);
            }
        },
    });
});

$('#transferSummaryButton').on('click', async function () {
    $('#transferSummaryButton').addClass('loading disabled');

    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/NachbauTransferController.php', {
            params: {
                action: 'getTransferSummaryOfNachbauTransfer',
                projectNo: getUrlParam('project-no'),
                currentNachbau: getUrlParam('nachbau-no')
            }
        });

        if (response.status === 200 && response.data) {
            showSkippedModal(
                response.data.skipped,
                response.data.fromNachbau,
                response.data.toNachbau
            );

            $('#nachbauTransferCompleteBtn').hide();
        }
    } catch (error) {
        fireToastr('error', 'Error fetching transfer summary data', error);
        const errorMessage = error.response?.data?.message || error.message || "Error fetching transfer summary data. Get Contact with DGT Team!";
        showErrorDialog(`<b>${errorMessage}</b>`);
    } finally {
        $('#transferSummaryButton').removeClass('loading disabled');
    }
});
