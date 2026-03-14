// /dpm/dto_configurator/js/projects/index.js

let allProjects = [];
let isProjectsInitialized = false;

$(document).ready(function() {
    if (isProjectsInitialized) {
        return;
    }
    isProjectsInitialized = true;

    // Load all projects on page load
    loadAllProjects();

    // Attach search handler
    attachSearchHandler();
});

/**
 * Load All Projects
 */
async function loadAllProjects() {
    try {
        showLoader();

        const response = await axios.get('/dpm/api/ProjectController.php', {
            params: {
                action: 'getAllProjects'
            }
        });

        hideLoader();

        if (response.data.success && response.data.data) {
            allProjects = response.data.data.projects || [];
            displayProjectsTable(allProjects);
        } else {
            showError(response.data.message || 'Failed to load projects');
        }
    } catch (error) {
        hideLoader();
        console.error('Error loading projects:', error);
        showError(error.response?.data?.message || error.message || 'An error occurred');
    }
}

/**
 * Display Projects Table
 */
function displayProjectsTable(projects) {
    const tbody = $('#projectsTableBody');
    tbody.empty();

    if (projects.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="5" style="text-align: center; padding: 20px;">
                    No projects found
                </td>
            </tr>
        `);
        return;
    }

    projects.forEach(project => {
        const row = `
            <tr class="project-row" data-project="${project.project_no}">
                <td>${project.project_no || '-'}</td>
                <td>${project.project_name || '-'}</td>
                <td>${project.status || '-'}</td>
                <td>${formatDate(project.created_date) || '-'}</td>
                <td>
                    ${project.nachbau_available ? 
                        '<i class="green checkmark icon"></i> Yes' : 
                        '<i class="red times icon"></i> No'}
                </td>
            </tr>
        `;
        tbody.append(row);
    });

    // Attach row click handlers
    attachProjectRowHandlers();
}

/**
 * Attach Project Row Handlers
 */
function attachProjectRowHandlers() {
    $('.project-row').off('click');
    
    $('.project-row').on('click', function() {
        const projectNo = $(this).data('project');
        // Navigate to project detail page
        window.location.href = `/dpm/dto_configurator/projects/detail/info.php?project=${encodeURIComponent(projectNo)}`;
    });
}

/**
 * Attach Search Handler
 */
function attachSearchHandler() {
    $('#projectSearchInput').off('keyup');
    
    $('#projectSearchInput').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        const filtered = allProjects.filter(project => {
            return (project.project_no && project.project_no.toLowerCase().includes(searchTerm)) ||
                   (project.project_name && project.project_name.toLowerCase().includes(searchTerm));
        });

        displayProjectsTable(filtered);
    });
}

/**
 * Show Error
 */
function showError(message) {
    $('#errorMessage').text(message);
    $('#errorSection').show();
}

/**
 * Format Date
 */
function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

/**
 * Show Loader
 */
function showLoader() {
    $('#customLoaderOverlay').addClass('show');
}

/**
 * Hide Loader
 */
function hideLoader() {
    $('#customLoaderOverlay').removeClass('show');
}