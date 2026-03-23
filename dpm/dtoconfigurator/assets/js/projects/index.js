let projectsTable = null;

$(document).ready(function() {
    console.log('Document ready - initializing projects page');
    initializeSearchProjectSelect();
    initializeProjectsDataTable();
});

async function fetchAllProjects() {
    try {
        console.log('Fetching all projects...');
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/ProjectController.php', { 
            params: { action: 'getAllProjects' }
        });
        console.log('Projects fetched:', response.data);
        return response.data || [];
    } catch (error) {
        console.error('Error fetching projects:', error);
        fireToastr('error', 'Error fetching project list: ' + (error.message || 'Unknown error'));
        return [];
    }
}

async function initializeProjectsDataTable() {
    try {
        console.log('Initializing DataTable...');
        const data = await fetchAllProjects();
        console.log('Data received for table:', data);

        // Show table, hide loading message
        $('#loadingMessage').hide();
        $('#projectsTable').show();

        if (projectsTable) {
            projectsTable.destroy();
        }

        projectsTable = $('#projectsTable').DataTable({
            data: data,
            pageLength: 25,
            autoWidth: false,
            order: [[10, 'desc']],
            paging: true,
            destroy: false,
            language: {
                emptyTable: "No projects found. Use the search box above to find projects."
            },
            columnDefs: [
                { width: '13%', targets: 0 },
                { width: '9%', targets: 1 },
                { width: '10%', targets: 2 },
                { width: '21%', targets: 3 },
                { width: '6%', targets: 4 },
                { width: '10%', targets: 5 },
                { width: '9%', targets: 6 },
                { width: '9%', targets: 7 },
                { width: '7%', targets: 8 }
            ],
            columns: [
                {
                    render: (data, type, row) => {
                        let html = '';
                        
                        if (row.MEName && row.MEMail) {
                            html += `<div class="tooltip-container" title="${row.MEName} (Mechanical Engineer)">
                                        <a target="_blank" href="https://teams.microsoft.com/l/chat/0/0?users=${row.MEMail}">
                                            <img src="/users/?gid=${row.MEGID}" class="user-img" alt="ME" 
                                             onerror="this.onerror=null; this.src='/dpm/dtoconfigurator/assets/images/no-avatar.jpg';" style="width: 30px; height: 30px; border-radius: 50%; margin: 2px;">
                                        </a>
                                    </div>`;
                        }
                        
                        if (row.OMName && row.OMMail) {
                            html += `<div class="tooltip-container" title="${row.OMName} (Order Manager)">
                                        <a target="_blank" href="https://teams.microsoft.com/l/chat/0/0?users=${row.OMMail}">
                                            <img src="/users/?gid=${row.OMGID}" class="user-img" alt="OM" 
                                             onerror="this.onerror=null; this.src='/dpm/dtoconfigurator/assets/images/no-avatar.jpg';" style="width: 30px; height: 30px; border-radius: 50%; margin: 2px;">
                                        </a>
                                    </div>`;
                        }
                        
                        if (row.EEName && row.EEMail) {
                            html += `<div class="tooltip-container" title="${row.EEName} (Electrical Engineer)">
                                        <a target="_blank" href="https://teams.microsoft.com/l/chat/0/0?users=${row.EEMail}">
                                            <img src="/users/?gid=${row.EEGID}" class="user-img" alt="EE" 
                                             onerror="this.onerror=null; this.src='/dpm/dtoconfigurator/assets/images/no-avatar.jpg';" style="width: 30px; height: 30px; border-radius: 50%; margin: 2px;">
                                        </a>
                                    </div>`;
                        }
                        
                        return html;
                    },
                    className: 'center aligned'
                },
                {
                    data: 'ProjectNumber',
                    render: (data) => `<a href="/dpm/dtoconfigurator/core/projects/detail/info.php?project-no=${data}" target="_blank">
                                       ${data}
                                    </a>`,
                    className: 'center aligned'
                },
                {
                    data: 'NachbauNumber',
                    className: 'center aligned'
                },
                {
                    data: 'ProjectName',
                    className: 'center aligned'
                },
                {
                    data: 'PanelQuantity',
                    className: 'center aligned'
                },
                {
                    data: function (data) {
                        return data.package ? `${data.ProductType} <br> <span style="color: #999; font-size: 10px;">${data.package}</span>`
                                            : (data.ProductType || '-');
                    },
                    className: 'center aligned'
                },
                {
                    data: 'LastUpdatedBy',
                    className: 'center aligned',
                },
                {
                    data: 'WorkingUser',
                    render: function (data, type, row) {
                        if (row.LastUpdatedBy !== '' && row.LastUpdatedBy !== null && (row.WorkingUser === null || row.WorkingUser === '')) {
                            return `<span style="color: #dc3545; font-weight: bold;">Checked Out</span>`;
                        } else {
                            return data || '-';
                        }
                    },
                    className: 'center aligned',
                },
                {
                    data: 'Status',
                    render: function (data) {
                        if (data === 'Work in Progress')
                            return `<span style="background-color: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 4px;">${data}</span>`;
                        else if (data === 'Pending Approval')
                            return `<span style="background-color: #fef5e7; color: #f39c12; padding: 4px 8px; border-radius: 4px;">${data}</span>`;
                        else if (data === 'Published')
                            return `<span style="background-color: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px;">${data}</span>`;
                        else
                            return `<span style="background-color: #f8f9fa; color: #6c757d; padding: 4px 8px; border-radius: 4px;">${data || '-'}</span>`;
                    },
                    className: 'center aligned'
                },
                {
                    data: "LastNachbauDate",
                    render: {
                        "_": "ProjectLastNachbauTimeValue",
                        "display": "ProjectLastNachbauDate"
                    },
                    className: "center aligned",
                },
                {
                    data: "UpdatedDate",
                    render: {
                        "_": "ProjectLastUpdatedTimeValue",
                        "display": function (data, type, row) {
                            if (!row.UpdatedDate || !row.UpdatedDate.ProjectLastUpdatedDate) return "-";

                            let fullDate = row.UpdatedDate.ProjectLastUpdatedDate.split(" ");
                            let datePart = fullDate[0];
                            let timePart = fullDate[1] || '';

                            return `${datePart} <span style="color: gray; font-size: 0.85em;">${timePart}</span>`;
                        }
                    },
                    className: "center aligned",
                }
            ]
        });

        console.log('DataTable initialized successfully');

    } catch (error) {
        console.error('Error initializing DataTable:', error);
        $('#loadingMessage').html('<div class="error-message">Error loading projects. Please refresh the page.</div>');
    }
}

function initializeSearchProjectSelect() {
    const searchInput = $('#searchProjectSelect');
    const clearIcon = $('#clearSearchIcon');
    const dropdown = $('#projectDropdown');

    searchInput.on('input', async function() {
        const searchTerm = $(this).val().trim();

        if (searchTerm.length < 2) {
            dropdown.removeClass('show');
            clearIcon.hide();
            return;
        }

        clearIcon.show();

        try {
            console.log('Searching for:', searchTerm);
            const response = await axios.get('/dpm/dtoconfigurator/api/controllers/BaseController.php', {
                params: { action: 'searchProject', keyword: searchTerm }
            });

            const projects = response.data || [];
            console.log('Search results:', projects);

            if (projects.length === 0) {
                dropdown.html('<div class="owner-dropdown-item no-results">No projects found</div>').addClass('show');
                return;
            }

            let html = '';
            projects.forEach(project => {
                html += `<div class="owner-dropdown-item" data-project-no="${project.FactoryNumber}">
                            <strong>${project.FactoryNumber}</strong> - ${project.ProjectName}
                         </div>`;
            });

            dropdown.html(html).addClass('show');

            // Handle dropdown item click
            dropdown.find('.owner-dropdown-item').on('click', function() {
                const projectNo = $(this).data('project-no');
                if (projectNo) {
                    window.open(`/dpm/dtoconfigurator/core/projects/detail/info.php?project-no=${projectNo}`, '_blank');
                    searchInput.val('');
                    dropdown.removeClass('show');
                    clearIcon.hide();
                }
            });

        } catch (error) {
            console.error('Error searching projects:', error);
            fireToastr('error', 'Error searching projects');
            dropdown.html('<div class="owner-dropdown-item no-results">Error loading projects</div>').addClass('show');
        }
    });

    clearIcon.on('click', function(e) {
        e.stopPropagation();
        searchInput.val('');
        dropdown.removeClass('show');
        $(this).hide();
    });

    // Hide dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.owner-input-wrapper').length) {
            dropdown.removeClass('show');
        }
    });
}

// Toast notification function
function fireToastr(type, text) {
    const colors = {
        'error': { bg: '#dc3545', icon: 'error' },
        'success': { bg: '#28a745', icon: 'success' },
        'warning': { bg: '#ffc107', icon: 'warning' }
    };

    const config = colors[type] || colors['error'];

    $.toast({
        heading: type.charAt(0).toUpperCase() + type.slice(1),
        text: text,
        icon: config.icon,
        position: 'top-right',
        bgColor: config.bg,
        hideAfter: 5000,
        stack: false
    });
}