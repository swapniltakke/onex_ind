let GlobalProjectNo;

$(document).ready(async function() {
    $('#banfomatPage .loader').hide();
    $('#banfomatContainer').transition('zoom');

    await initializeBanfomatProjectSelect();
});

async function initializeBanfomatProjectSelect() {
    const projectSelectBox = $('#banfomatProjectSearch');

    projectSelectBox.dropdown({
        apiSettings: {
            url: `/dpm/dtoconfigurator/api/controllers/BaseController.php?action=searchProject&keyword={query}`,
            cache: false,
            onResponse: function(response) {
                const menuElement = document.querySelector('.searchProjectSelect .menu.transition.visible');
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
        minCharacters: 2,
        clearable: true,
        allowAdditions: false,
        fullTextSearch: true,
        forceSelection: false,
        selectOnKeydown:false,
        onChange: function(projectNo) {
            if (projectNo) {
                renderProjectTables(projectNo);
            }
        }
    });
}

async function renderProjectTables(projectNo) {
    const response = await getProjectCopperDetails(projectNo);
    GlobalProjectNo = projectNo;

    hideElement('#nachbauCopperCoatDetailsContainer');
    hideElement('#copperMaterialListTableContainer');
    showLoader('#banfomatContainer');

    const nachbauNo = Object.keys(response[projectNo])[0];
    const coatedDtosOfProject = response[projectNo][nachbauNo].coatedDtosOfProject || [];
    const copperMaterialsOfProject = response[projectNo][nachbauNo].copperMaterialsOfProject || [];
    const coppersDataSource = response[projectNo][nachbauNo].coppersDataSource || '';
    const isProjectPublished = response[projectNo][nachbauNo].isProjectReleased || '';

    await renderNachbauCopperCoatDetailsTable(projectNo, nachbauNo, coatedDtosOfProject);
    await renderProjectCopperMaterialsDataTable(copperMaterialsOfProject);

    $('#coppersDataSourceMsg .coppersDataSourceSpan').text(coppersDataSource);
    $('#coppersDataSourceMsg').show();

    if (isProjectPublished) {
        $('#projectPublishStatus .projectPublishStatusSpan').text('Yayınlandı');
    } else {
        $('#projectPublishStatus .projectPublishStatusSpan').text('Henüz Yayınlanmamış');
    }
    $('#projectPublishStatus').show();

    showElement('#proceedToProjectBanfomatInfoButton');

    const $searchInput = $('.dt-search input[type="search"]');
    $searchInput.attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
}

async function renderNachbauCopperCoatDetailsTable(projectNo, nachbauNo, coatedDtosOfProject) {
    const tableId = '#nachbauCopperCoatDetailsTable';

    if ($.fn.DataTable.isDataTable(tableId)) {
        $(tableId).DataTable().destroy();
        $(tableId).find('tbody').empty();
    }

    if (coatedDtosOfProject.length === 0) {
        $('#nachbauCopperCoatDetailsContainer').hide();
    } else {
        $('#nachbauCopperCoatDetailsContainer').show();

        $(tableId).DataTable({
            data: coatedDtosOfProject,
            pageLength: 25,
            autoWidth: false,
            searching: false,
            paging:false,
            ordering:false,
            columns: [
                { data: null, render: () => `<span>${projectNo}</span>`, className: 'center aligned' },
                { data: 'Lots', className: 'center aligned' },
                { data: null, render: () => `<span>${nachbauNo}</span>`, className: 'center aligned' },
                { data: 'DtoNumber', className: 'center aligned' },
                {
                    data: 'Description',
                    className: 'center aligned',
                    render: (data, type, row) => {
                        return `<span class="full-description-click" data-dto="${row.DtoNumber}" data-desc="${escapeHtml(row.Description)}" 
                                     data-tooltip="Click on this to see full description" data-position="top right" style="cursor:pointer;">
                                    ${formatDescription(data, 3)}
                                </span>`;
                    }
                },
                { data: 'isTin', render: data => data ? '<i class="check circle icon green big"></i>' : '', className: 'center aligned' },
                { data: 'isSilver', render: data => data ? '<i class="check circle icon green big"></i>' : '', className: 'center aligned' },
                { data: 'isNickel', render: data => data ? '<i class="check circle icon green big"></i>' : '', className: 'center aligned' }
            ]
        });
    }
}

async function renderProjectCopperMaterialsDataTable(copperMaterialListData) {
    const tableId = '#copperMaterialListTable';

    if ($.fn.DataTable.isDataTable(tableId))
        $(tableId).DataTable().destroy();

    if (copperMaterialListData.length === 0)
        $('#copperMaterialListTableContainer').hide()
    else {
        const copperMaterialListTable = $(tableId).DataTable({
            data: copperMaterialListData,
            pageLength: 50,
            autoWidth: false,
            fixedHeader:true,
            dom: 'Bfrtip', // Add this line to enable buttons
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="file excel outline icon"></i> Export to Excel',
                    title: 'CopperMaterials',
                    exportOptions: {
                        columns: ':visible'
                    }
                }
            ],
            columns: [
                { data: 'Lot', className: 'center aligned' },
                {   data: 'Material',
                    render: (data, type, row) => `<a target="_blank" href="/materialviewer/?material=${row.Material}" 
                                                       data-tooltip="Navigate to Material Viewer" data-position="top center" data-variation="inverted">
                                                       ${data}
                                                    </a>`,
                    className: 'center aligned'
                },
                { data: 'ShortText', className: 'center aligned'},
                {
                    data: 'IndustryDesc',
                    render: (data) => {
                        if (data)
                            return `<span>${data}</span>`
                        else
                            return `<span>DEPO</span>`
                    },
                    className: 'center aligned'
                },
                { data: 'TotalQuantity', className: 'center aligned' },
                { data: 'Unit', className: 'center aligned' },
                { data: 'PeggedRequirement', className: 'center aligned' },
                { data: 'SupplyArea', className: 'center aligned' },
                { data: 'MRP', className: 'center aligned' },
                { data: 'SLoc', className: 'center aligned' },
                { data: 'KMAT', className: 'center aligned' },
            ],
            destroy: true
        });


        $('#copperMaterialListTableContainer').removeClass('transition hidden');
        $('#copperMaterialListTableContainer').transition('zoom', function() {
            requestAnimationFrame(() => {
                copperMaterialListTable.fixedHeader.adjust();
            });
        });

        copperMaterialListTable.draw();
    }
}

async function getProjectCopperDetails(projectNo) {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/BanfomatController.php', {
            params: { action: 'getProjectCopperDetails', projectNo: projectNo }
        });

        return response.data;
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`<b>${errorMessage}</b>`);
    }
}

$(document).on('click', '#proceedToProjectBanfomatInfoButton', function () {
    window.open(`/dpm/dtoconfigurator/core/banfomat/project-banfomat-info.php?project-no=${GlobalProjectNo}`, '_blank');
});


$('#nachbauCopperCoatDetailsTable').on('click', '.full-description-click', function () {
    const dtoNumber = $(this).data('dto');
    const fullDescription = $(this).data('desc');

    $('#fullDescriptionModal .header').html(`<h4 style="text-align:center;">Full Description of ${dtoNumber}</h4>`);
    $('#fullDescriptionModal .content').html(`<p style="text-align:center;font-weight:700;line-height:1.5rem;">${fullDescription.split('V:').join('<br>')}</p>`);
    $('#fullDescriptionModal').modal('show').draggable({ handle: '.header', containment: 'window', blurring: false, inverted: true});
});


