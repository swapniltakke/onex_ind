let projectInfoOverviewData = '';

$('#seeOverviewOfProjectButton').on('click', async function () {
    $(this).addClass('loading disabled');

    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/ProjectController.php', {
            params: {
                action: 'getOverviewOfProjectWork',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                nachbauDate: NachbauDataOfProject[getUrlParam('nachbau-no')]['Time'],
                currentlyWorkingUser: currentlyWorkingUser
            }
        });

        projectInfoOverviewData = response.data;
        await renderProjectInfoOverviewDataTable(projectInfoOverviewData);
        hideElement('#publishProjectInfo');
        showElement('#publishProjectSummary');
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`<b>${errorMessage}</b>`);
    } finally {
        $(this).removeClass('loading disabled');
    }
});

async function renderProjectInfoOverviewDataTable(projectInfoOverviewData) {
    const tableId = '#projectInfoOverviewDataTable';

    if ($.fn.DataTable.isDataTable(tableId))
        $(tableId).DataTable().destroy();

    if ($('#projectInfoOverview').hasClass('hidden'))
        $('#projectInfoOverview').removeClass('hidden');

    $('#totalChangeDiv #totalChangeCount').text(projectInfoOverviewData[0]['totalChanges']);

    if (projectInfoOverviewData.length === 0)
        $('#projectInfoOverview').hide();
    else {
        $(tableId).DataTable({
            data: projectInfoOverviewData,
            autoWidth:false,
            searching: false,
            paging:false,
            ordering:false,
            pageLength: 10,
            destroy: true,
            columns: [
                {
                    data: 'projectNumber',
                    className: 'center aligned'
                },
                {
                    data: 'projectName',
                    className: 'center aligned'
                },
                {
                    data: 'nachbauNumber',
                    className: 'center aligned'
                },
                {
                    data: 'nachbauDate',
                    className: 'center aligned'
                },
                {
                    data: 'product',
                    className: 'center aligned'
                },
                {
                    data: 'panelQty',
                    className: 'center aligned'
                },
                {
                    data: 'ratedVoltage',
                    render: (data, type, row) => `<span>${row.ratedVoltage} kV</span>`,
                    className: 'center aligned'
                },
                {
                    data: 'ratedShortCircuit',
                    render: (data, type, row) => `<span>${row.ratedShortCircuit} kA</span>`,
                    className: 'center aligned'
                },
                {
                    data: 'ratedCurrent',
                    render: (data, type, row) => `<span>${row.ratedCurrent} A</span>`,
                    className: 'center aligned'
                },
                {
                    data: 'electricalEngineer',
                    className: 'center aligned'
                },
                {
                    data: 'orderManager',
                    className: 'center aligned'
                },
                {
                    data: 'mechanicalEngineer',
                    className: 'center aligned'
                },
                {
                    data: 'workedUser',
                    className: 'center aligned'
                }
            ]
        });

        $('#projectInfoOverviewDataTable_info').hide();
    }
}

$('#publishProjectButton').on('click', async function () {

    showConfirmationDialog({
        title: 'Publish Project?',
        htmlContent: '<b>After confirmation, no further changes can be made to this order and it will be submitted for manager approval. Do you wish to proceed?</b>',
        confirmButtonText: 'Yes, publish!',
        confirmButtonColor: 'green',
        onConfirm: async function () {
            $(this).addClass('loading disabled');

            Swal.fire({
                title: 'Sending for Approval...',
                html: 'The project is being sent for publication approval.<br><br><b>This process may take a while. Please wait...</b>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const response = await axios.post('/dpm/dtoconfigurator/api/controllers/ProjectController.php?', {
                    action: 'insertReleaseProjectAndOrderChangeData',
                    projectNo: getUrlParam('project-no'),
                    nachbauNo: getUrlParam('nachbau-no'),
                    projectStatusId: currentlyWorkingUser.projectStatusId,
                    projectInfoData: projectInfoOverviewData,
                    assemblyStart: assemblyStartGlobal,
                    isRevisionNachbau: currentlyWorkingUser.isRevisionNachbau
                }, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });

                Swal.close();

                if (response.data.success) {
                    showSuccessDialog('Project has been sent for approval successfully.').then(() => {
                        location.reload();
                    });
                } else {
                    // Handle checklist incomplete error
                    if (response.data.checklist_status) {
                        const checklistStatus = response.data.checklist_status;
                        let missingItemsHtml = '<b>Missing checklist items:</b><ul>';

                        checklistStatus.incomplete_items.forEach(item => {
                            missingItemsHtml += `<li><b>${item.category_name}:</b> ${item.checklist_detail}</li>`;
                        });
                        missingItemsHtml += '</ul>';

                        showErrorDialog(`
                            <b>Cannot publish project!</b><br><br>
                            ${missingItemsHtml}
                            <br><b>Please complete all checklist items before publishing.</b>
                        `);
                    }
                    // Handle ZENF materials error
                    else if (response.data.not_defined_materials) {
                        const notDefinedMaterials = response.data.not_defined_materials;
                        let materialsHtml = '<b>Not defined materials (ZENF type):</b><ul>';

                        notDefinedMaterials.forEach(material => {
                            materialsHtml += `<li>${material}</li>`;
                        });
                        materialsHtml += '</ul>';

                        showErrorDialog(`
                            <b>Cannot publish project!</b><br><br>
                            ${response.data.message}<br><br>
                            ${materialsHtml}
                            <br><b>✅ Mail sent to SAP Material Define team!</b>
                        `);

                        sendNotDefinedMaterialsEMail(notDefinedMaterials);
                    }
                    else {
                        showErrorDialog(`<b>${response.data.message}</b>`);
                    }
                }
            } catch (error) {
                const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Please contact the DGT Team!";
                showErrorDialog(`<b>${errorMessage}</b>`);
            } finally {
                $(this).removeClass('loading disabled');
            }
        }
    });
});


function sendNotDefinedMaterialsEMail(notDefinedMaterials) {
    try {
        axios.post('/dpm/dtoconfigurator/api/controllers/MaterialController.php?',
            {
                action: 'sendNotDefinedMaterialsEMail',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                notDefinedMaterials: notDefinedMaterials
            },
            { headers: { 'Content-Type': 'multipart/form-data' }}
        );
    } catch(error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`${errorMessage}`);
    }
}
