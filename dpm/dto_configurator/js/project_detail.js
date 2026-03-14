// /dpm/dto_configurator/js/project_detail.js
let assemblyStartGlobal = '';
let projectDataGlobal = {};

$(document).ready(function() {
    // Initialize tabs
    initializeTabs();
    
    // Load project info
    getProjectInfo();
    
    // Initialize search
    initializeSearchProjectSelect();
});

function initializeTabs() {
    $('#projectDataTabularMenu .item').on('click', function(e) {
        e.preventDefault();
        
        // Remove active from all items
        $('#projectDataTabularMenu .item').removeClass('active');
        $('#projectDataTabs .tab.segment').removeClass('active').hide();
        
        // Add active to clicked item
        $(this).addClass('active');
        const tabName = $(this).data('tab');
        $('#' + tabName).addClass('active').show();
    });
}

/**
 * Get project information from DTOConfiguratorController
 */
async function getProjectInfo() {
    try {
        const projectNo = new URLSearchParams(window.location.search).get('project');
        
        if (!projectNo) {
            $('#projectPageErrorMsg').text('No project number provided');
            $('#projectPageErrorDiv').show();
            return;
        }

        // Call DTOConfiguratorController API with getProjectInfo action
        const response = await axios.get('/dpm/api/DTOConfiguratorController.php', {
            params: {
                action: 'getProjectInfo',
                projectNo: projectNo
            }
        });

        if (response.data.success && response.data.data) {
            const data = response.data.data;
            projectDataGlobal = data;

            // Update header labels
            $('#projectNumberLabel').text(data.FactoryNumber);
            $('#projectNameLabel').text(data.ProjectName);
            $('#orderManagerLabel').text(data.OrderManager);
            $('#productLabel').text(data.Product);

            // Check for NXAIR pack type
            if (data.Product === 'NXAIR') {
                const ratedShortCircuit = parseFloat(data.ratedShortCircuit ? data.ratedShortCircuit.replace(',', '.') : 0);
                const ratedCurrent = parseInt(data.ratedCurrent, 10);

                if ((ratedShortCircuit < 31.5) || (ratedShortCircuit === 31.5 && ratedCurrent <= 2500)) {
                    $('#packTypeLabel').text('Pack 1');
                } else if ((ratedShortCircuit === 40) || (ratedShortCircuit === 31.5 && ratedCurrent > 2500)) {
                    $('#packTypeLabel').text('Pack 2');
                } else {
                    $('#packTypeLabel').text('Unknown');
                }

                showElement('#packTypeDiv');
            }

            $('#panelCountLabel').text(data.Qty);
            $('#ratedVoltageLabel').text(data.ratedVoltage + ' kV');
            $('#ratedShortCircuitLabel').text(data.ratedShortCircuit + ' kA');
            $('#ratedCurrentLabel').text(data.ratedCurrent + ' A');
            $('#assemblyStartDateLabel').text('Ass. Start: ' + (data.assemblyStartDate ?? 'Unknown'));
            assemblyStartGlobal = data.assemblyStartDate;

            // Update detail table
            $('#detailProjectNo').text(data.FactoryNumber);
            $('#detailProjectName').text(data.ProjectName);
            $('#detailOrderManager').text(data.OrderManager);
            $('#detailProduct').text(data.Product);
            $('#detailPanelCount').text(data.Qty);
            $('#detailRatedVoltage').text(data.ratedVoltage + ' kV');
            $('#detailRatedShortCircuit').text(data.ratedShortCircuit + ' kA');
            $('#detailRatedCurrent').text(data.ratedCurrent + ' A');

            // Calculate assembly start remaining weeks
            if (data.assemblyStartDate) {
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                const dateParts = data.assemblyStartDate.split('.');
                const day = parseInt(dateParts[0], 10);
                const month = parseInt(dateParts[1], 10) - 1;
                const year = parseInt(dateParts[2], 10);

                const assemblyDate = new Date(year, month, day);
                assemblyDate.setHours(0, 0, 0, 0);

                const timeDiff = assemblyDate - today;
                const daysDiff = timeDiff / (1000 * 60 * 60 * 24);
                let weeksDiff = daysDiff / 7;

                weeksDiff = Math.max(0, weeksDiff);

                $('#assemblyStartRemainingWeekLabel').text(weeksDiff.toFixed(1) + ' weeks');

                $('#assemblyStartRemainingWeek').removeClass('red green');

                if (weeksDiff < 6.5) {
                    $('#assemblyStartRemainingWeek').addClass('red');
                } else {
                    $('#assemblyStartRemainingWeek').addClass('green');
                }

                $('#assemblyStartRemainingWeek').show();
            } else {
                $('#assemblyStartRemainingWeek').hide();
            }

            // Check nachbau status
            if (!data.isNachbauExists) {
                $('#nachbauFileMsg').html(`<i class="exclamation circle icon red big"></i> 
                                           <h2 style="margin:0;">Nachbau TXT could not be found for this project.</h2>`);
            }
            else if(!data.isProjectPlanned) {
                $('#nachbauFileMsg').html(`<i class="exclamation circle icon red big"></i> 
                                           <h3 style="margin:0;line-height: 1.8rem;">Project has MTool record but has not planned yet. <br> Please get contact with planning department.</h3>`);
            } else {
                $('#nachbauFileMsg').html(`<i class="check circle icon green big"></i> 
                                           <h2 style="margin: 0.7rem;">Nachbau Data is available for this project.</h2>`);
            }

            // Hide loaders and show content
            $('#projectPageContainer').show();
            $('#projectDataTabs').show();
            $('#projectDataTabsRow .ui.loader').hide();
        } else {
            $('#projectPageErrorMsg').text(response.data.message || 'Project not found');
            $('#projectPageErrorDiv').show();
        }
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred";
        $('#projectPageErrorMsg').text(errorMessage);
        $('#projectPageErrorDiv').show();
        console.error('Error:', error);
    }
}

/**
 * Initialize search project dropdown
 */
function initializeSearchProjectSelect() {
    const projectSelectBox = $('#searchProjectSelect');

    projectSelectBox.dropdown({
        apiSettings: {
            url: `/dpm/api/DTOConfiguratorController.php?action=searchProjectsWithMTool&term={query}`,
            cache: false,
            onResponse: function(response) {
                const projects = Array.isArray(response) ? response : [];
                let results = [];

                if (projects.length === 0) {
                    return { results };
                }
                
                results = projects.map(project => ({
                    name: `<b>${project.project_no}</b> - ${project.project_name}`,
                    value: project.project_no
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
        onChange: function(value) {
            if (value) {
                window.location.href = `/dpm/dto_configurator/project_detail.php?project=${value}`;
            }
        }
    });
}