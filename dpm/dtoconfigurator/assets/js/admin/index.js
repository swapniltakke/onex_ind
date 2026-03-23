let activeStatusFilters = [];

$(document).ready(async function() {

    await initializeReleasedProjectsDataTable();

    $('#adminPage .loader').hide();
    $('#adminPage #adminPageContainer').transition('zoom');

    $(document).on('click', '.status-filter', function() {
        const status = $(this).data('status').toString();
        const $label = $(this);

        if (activeStatusFilters.includes(status)) {
            activeStatusFilters = activeStatusFilters.filter(s => s !== status);
            $label.addClass('basic');
        } else {
            activeStatusFilters.push(status);
            $label.removeClass('basic');
        }

        $('#releasedProjectsTable').DataTable().draw();

        updateClearButtonVisibility();
    });
    $(document).on('click', '#clearStatusFilters', function() {
        activeStatusFilters = [];

        $('.status-filter').addClass('basic');

        $('#releasedProjectsTable').DataTable().draw();

        updateClearButtonVisibility();
    });
    updateClearButtonVisibility();
});

async function initializeReleasedProjectsDataTable() {
    const releasedProjects = await getReleasedProjects();
    const tableId = '#releasedProjectsTable';

    if ($.fn.DataTable.isDataTable(tableId))
        $(tableId).DataTable().destroy();

    if ($('#releasedProjectsTableContainer').hasClass('hidden'))
        $('#releasedProjectsTableContainer').removeClass('hidden');

    if ($('#statusFilterContainer').hasClass('hidden'))
        $('#statusFilterContainer').removeClass('hidden');

    if (releasedProjects.length === 0) {
        $('#releasedProjectsTableContainer').hide();
        $('#statusFilterContainer').hide();
    }
    else {
        const dataTable = $(tableId).DataTable({
            data: releasedProjects,
            autoWidth: false,
            searching: true,
            paging: true,
            pageLength: 10,
            destroy: true,
            order: [],
            columns: [
                {
                    data: 'project_number',
                    render: (data) => `<a href="/dpm/dtoconfigurator/core/projects/detail/info.php?project-no=${data}" target="_blank">${data}</a>`,
                    className: 'center aligned'
                },
                {
                    data: 'project_name',
                    className: 'center aligned'
                },
                {
                    data: 'nachbau_number',
                    className: 'center aligned'
                },
                {
                    data: 'nachbau_date',
                    className: 'center aligned'
                },
                {
                    data: 'product_name',
                    className: 'center aligned'
                },
                {
                    data: 'assembly_start_date',
                    className: 'center aligned'
                },
                {
                    data: 'panel_quantity',
                    className: 'center aligned'
                },
                {
                    data: 'rated_voltage',
                    render: (data, type, row) => `<span>${row.rated_voltage}kV</span>`,
                    className: 'center aligned'
                },
                {
                    data: 'rated_short_circuit',
                    render: (data, type, row) => `<span>${row.rated_short_circuit}kA</span>`,
                    className: 'center aligned'
                },
                {
                    data: 'rated_current',
                    render: (data, type, row) => `<span>${row.rated_current}A</span>`,
                    className: 'center aligned'
                },
                {
                    className: 'center aligned',
                    render: (data, type, row) => `<div class="tooltip-container" 
                                                 ${row.contacts.MechanicalEngineer ? `data-tooltip="${row.contacts.MechanicalEngineer} (Mechanical Engineer)"` : ''} 
                                                 data-position="top left">
                                                <a target="_blank" href="https://teams.microsoft.com/l/chat/0/0?users=${row.contacts.MEEmail}">
                                                    <img src="/users/?gid=${row.contacts.MEGID}" class="user-img" alt="No avatar" 
                                                     onerror="this.onerror=null; this.src='/dpm/dtoconfigurator/assets/images/no-avatar.jpg';">
                                                </a>
                                            </div>
                                            <div class="tooltip-container" 
                                                 ${row.contacts.OrderManager ? `data-tooltip="${row.contacts.OrderManager} (Order Manager)"` : ''} 
                                                 data-position="top left">
                                                <a target="_blank" href="https://teams.microsoft.com/l/chat/0/0?users=${row.contacts.OMEmail}">
                                                    <img src="/users/?gid=${row.contacts.OMGID}" class="user-img" alt="No avatar" 
                                                     onerror="this.onerror=null; this.src='/dpm/dtoconfigurator/assets/images/no-avatar.jpg';">
                                                </a>
                                            </div>
                                            <div class="tooltip-container" 
                                                 ${row.contacts.ElectricalEngineer ? `data-tooltip="${row.contacts.ElectricalEngineer} (Electrical Engineer)"` : ''} 
                                                 data-position="top left">
                                                <a target="_blank" href="https://teams.microsoft.com/l/chat/0/0?users=${row.contacts.EEEmail}">
                                                    <img src="/users/?gid=${row.contacts.EEGID}" class="user-img" alt="No avatar" 
                                                     onerror="this.onerror=null; this.src='/dpm/dtoconfigurator/assets/images/no-avatar.jpg';">
                                                </a>
                                            </div>
                                        `,
                },
                {
                    data: 'submitted_by',
                    className: 'center aligned'
                },
                {
                    data: function(data) {
                        return moment(data.submitted_date).format('DD.MM.YYYY HH:mm');
                    },
                    className: 'center aligned',
                },
                {
                    data: 'submission_status_name',
                    className: 'center aligned',
                    render: (data, type, row) => {
                        let colorClass = '';

                        switch (row.submission_status) {
                            case '3':
                                colorClass = 'blue';
                                break;
                            case '4':
                                colorClass = 'red';
                                break;
                            case '5':
                                colorClass = 'green';
                                break;
                            case '8':
                                colorClass = 'blue';
                                break;
                            case '9':
                                colorClass = 'green';
                                break;
                            default:
                                colorClass = 'black';
                        }

                        let output = `<span class="ui ${colorClass} mini label">${data}</span>`;
                        if (row.submission_status === '5' || row.submission_status === '9') {
                            const formattedDate = moment(row.reviewed_date).format('DD.MM.YYYY HH:mm');
                            output += `<br><strong style="color: #005000;font-size:11px;">${formattedDate}</strong>`;
                        }
                        return output;
                    },
                },
                {
                    render: (data, type, row) => {
                        if (row.submission_status !== '6') {
                            return `<button class="ui icon button circular mini teal" data-tooltip="See Order Changes" data-position="top center"
                                             onclick="window.open('/dpm/dtoconfigurator/core/admin/order-changes.php?released-project-id=${row.released_project_id}', '_blank')">
                                        <i class="eye large icon"></i>
                                    </button>`;
                        }
                        return '';
                    },
                    className: 'center aligned'
                }
            ],
            initComplete: function() {
                // To solve pagination issue
                const api = this.api();
                setTimeout(() => {
                    api.draw(false);
                }, 50);
            }
        });

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'releasedProjectsTable') {
                return true;
            }

            if (activeStatusFilters.length === 0) {
                return true;
            }

            const rowData = dataTable.row(dataIndex).data();

            return activeStatusFilters.includes(rowData.submission_status);
        });

        $('.dt-search input[type="search"]').attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
        $('.dt-column-order').css('display', 'none');
        $('.dt-input').css('border-radius', '50px');
    }
}

async function getReleasedProjects() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/AdminController.php', { params: { action: 'getReleasedProjects'} });
        return response.data;
    } catch (error) {
        fireToastr('error', 'Error fetching released projects:', error);
    }
}

function updateClearButtonVisibility() {
    const $clearButton = $('#clearStatusFilters');
    if (activeStatusFilters.length > 0) {
        $clearButton.show();
    } else {
        $clearButton.hide();
    }
}
