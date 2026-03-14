// /dpm/dto_configurator/js/projects/detail/info.js

let projectInfoGlobal = {};
let isInfoInitialized = false;

$(document).ready(function() {
    if (isInfoInitialized) {
        return;
    }
    isInfoInitialized = true;

    const projectNo = new URLSearchParams(window.location.search).get('project');
    if (projectNo) {
        // PHASE 2: Get Project Information
        getProjectInfo(projectNo);
    }
});

/**
 * PHASE 2: Get Project Information
 */
async function getProjectInfo(projectNo) {
    try {
        showLoader();

        const response = await axios.get('/dpm/api/ProjectController.php', {
            params: {
                action: 'getProjectInfo',
                projectNo: projectNo
            }
        });

        hideLoader();

        if (response.data.success && response.data.data) {
            projectInfoGlobal = response.data.data;
            // PHASE 3: Display Project Info
            displayProjectInfo(projectInfoGlobal, projectNo);
        } else {
            showError(response.data.message || 'Failed to load project information');
        }
    } catch (error) {
        hideLoader();
        console.error('Error loading project info:', error);
        showError(error.response?.data?.message || error.message || 'An error occurred');
    }
}

/**
 * PHASE 3: Display Project Information
 */
function displayProjectInfo(data, projectNo) {
    try {
        let html = `
            <div class="ui segment">
                <h2>Project: <strong>${projectNo}</strong></h2>
                <div class="ui relaxed divided list">
                    <div class="item">
                        <div class="content">
                            <div class="header">Project Name</div>
                            <p>${data.projectInfo?.projectName || '-'}</p>
                        </div>
                    </div>
                    <div class="item">
                        <div class="content">
                            <div class="header">Status</div>
                            <p>${data.projectInfo?.status || '-'}</p>
                        </div>
                    </div>
                    <div class="item">
                        <div class="content">
                            <div class="header">Created Date</div>
                            <p>${formatDate(data.projectInfo?.createdDate) || '-'}</p>
                        </div>
                    </div>
                </div>
        `;

        // Check if Nachbau exists
        if (data.nachbauExists) {
            html += `
                <button class="ui button primary" id="viewNachbauDataBtn" style="margin-top: 15px;">
                    <i class="database icon"></i> View Nachbau Data
                </button>
            `;
        } else {
            html += `
                <div class="ui message warning" style="margin-top: 15px;">
                    <p>No Nachbau data available for this project.</p>
                </div>
            `;
        }

        html += `</div>`;

        $('#projectInfoSection').html(html);
        $('#projectInfoSection').show();
        $('#initialLoader').hide();

        // Attach event handlers
        if (data.nachbauExists) {
            attachViewNachbauButton(projectNo);
        }

    } catch (error) {
        console.error('Error displaying project info:', error);
        showError('Error displaying project information');
    }
}

/**
 * Attach View Nachbau Button Handler
 */
function attachViewNachbauButton(projectNo) {
    $('#viewNachbauDataBtn').off('click');
    
    $('#viewNachbauDataBtn').on('click', function(e) {
        e.preventDefault();
        // PHASE 4: User clicks "View Nachbau Data"
        getNachbauFilesList(projectNo);
    });
}

/**
 * PHASE 4: Get Nachbau Files List
 */
async function getNachbauFilesList(projectNo) {
    try {
        showLoader();

        const response = await axios.get('/dpm/api/NachbauController.php', {
            params: {
                action: 'getNachbauFilesList',
                projectNo: projectNo
            }
        });

        hideLoader();

        if (response.data.success && response.data.data) {
            // PHASE 5: Display Nachbau Files List
            displayNachbauFilesList(response.data.data, projectNo);
        } else {
            showError(response.data.message || 'Failed to load nachbau files');
        }
    } catch (error) {
        hideLoader();
        console.error('Error loading nachbau files:', error);
        showError(error.response?.data?.message || error.message || 'An error occurred');
    }
}

/**
 * PHASE 5: Display Nachbau Files List (FILTER SECTION)
 */
function displayNachbauFilesList(data, projectNo) {
    try {
        let html = `
            <div class="ui segment">
                <h3><i class="database icon"></i> Nachbau Files</h3>
                <p>Select a nachbau file to view details and kuko matrix</p>
                <div class="ui relaxed divided list" id="nachbauFilesListContainer">
        `;

        if (data.files && data.files.length > 0) {
            data.files.forEach((file, index) => {
                html += `
                    <div class="item nachbau-file-item" data-nachbau-file="${file.FileName}" data-index="${index}">
                        <div class="right floated content">
                            <button class="ui mini button blue select-nachbau-btn" data-nachbau-file="${file.FileName}">
                                <i class="checkmark icon"></i> Select
                            </button>
                        </div>
                        <div class="content">
                            <div class="header">${file.FileName}</div>
                            <div class="description">
                                <p>
                                    <strong>Created:</strong> ${formatDate(file.Created)}<br>
                                    <strong>SAP Number:</strong> ${file.SAPNumber || '-'}<br>
                                    <strong>Materials:</strong> ${file.MaterialCount || 0}
                                </p>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            html += `
                <div class="item">
                    <div class="content">
                        <p>No nachbau files found for this project.</p>
                    </div>
                </div>
            `;
        }

        html += `
                </div>
            </div>
        `;

        $('#nachbauFilterSection').html(html);
        $('#nachbauFilterSection').show();

        // Attach event handlers
        attachNachbauFileListeners(projectNo);

    } catch (error) {
        console.error('Error displaying nachbau files:', error);
        showError('Error displaying nachbau files');
    }
}

/**
 * Attach Nachbau File Listeners
 */
function attachNachbauFileListeners(projectNo) {
    $('.select-nachbau-btn').off('click');
    
    $('.select-nachbau-btn').on('click', function(e) {
        e.preventDefault();
        
        const nachbauFile = $(this).data('nachbau-file');
        
        // Update active state
        $('.nachbau-file-item').removeClass('active');
        $(this).closest('.nachbau-file-item').addClass('active');

        // PHASE 6: User selects a nachbau file
        selectNachbauFile(projectNo, nachbauFile);
    });
}

/**
 * PHASE 6: Select Nachbau File and Load Kuko Matrix
 */
async function selectNachbauFile(projectNo, nachbauFile) {
    try {
        showLoader();

        const response = await axios.get('/dpm/api/KukoMatrixController.php', {
            params: {
                action: 'getKukoMatrixData',
                projectNo: projectNo,
                nachbauFile: nachbauFile
            }
        });

        hideLoader();

        if (response.data.success && response.data.data) {
            // Store in global variable
            window.selectedNachbauFile = nachbauFile;
            window.kukoMatrixData = response.data.data;

            // PHASE 7: Display Kuko Matrix (Default Tab)
            displayKukoMatrix(response.data.data);

            // Show tabs
            $('#projectDataTabularMenu').show();
            $('#projectDataTabs').show();

            // Attach tab handlers
            attachTabHandlers(projectNo);
        } else {
            showError(response.data.message || 'Failed to load kuko matrix data');
        }
    } catch (error) {
        hideLoader();
        console.error('Error loading kuko matrix:', error);
        showError(error.response?.data?.message || error.message || 'An error occurred');
    }
}

/**
 * Attach Tab Handlers
 */
function attachTabHandlers(projectNo) {
    $('#projectDataTabularMenu').off('click');
    
    $('#projectDataTabularMenu').on('click', '.item', function(e) {
        e.preventDefault();
        
        const tabName = $(this).data('tab');
        
        // Update active tab
        $('#projectDataTabularMenu .item').removeClass('active');
        $(this).addClass('active');
        
        // Hide all tabs
        $('#projectDataTabs .tab.segment').removeClass('active').hide();
        
        // Show selected tab
        $('#' + tabName).addClass('active').show();
        
        // PHASE 8: Load Tab Data
        loadTabData(tabName, projectNo);
    });
}

/**
 * PHASE 8: Load Tab Data
 */
async function loadTabData(tabName, projectNo) {
    const nachbauFile = window.selectedNachbauFile;

    switch(tabName) {
        case 'kukoMatrix':
            // Already loaded, just display
            displayKukoMatrix(window.kukoMatrixData);
            break;
        case 'projectWork':
            loadProjectWorkData(projectNo, nachbauFile);
            break;
        case 'orderSummary':
            loadOrderSummaryData(projectNo, nachbauFile);
            break;
        default:
            break;
    }
}

/**
 * Load Project Work Data
 */
async function loadProjectWorkData(projectNo, nachbauFile) {
    try {
        showLoader();

        const response = await axios.get('/dpm/api/ProjectController.php', {
            params: {
                action: 'getProjectWorkData',
                projectNo: projectNo,
                nachbauFile: nachbauFile
            }
        });

        hideLoader();

        if (response.data.success && response.data.data) {
            displayProjectWorkData(response.data.data);
        } else {
            $('#projectWorkContainer').html(`
                <div class="ui message error">
                    <p>${response.data.message || 'Failed to load project work data'}</p>
                </div>
            `);
        }
    } catch (error) {
        hideLoader();
        console.error('Error loading project work data:', error);
        $('#projectWorkContainer').html(`
            <div class="ui message error">
                <p>${error.message || 'An error occurred'}</p>
            </div>
        `);
    }
}

/**
 * Display Project Work Data
 */
function displayProjectWorkData(data) {
    let html = `
        <div class="ui segment">
            <h3>Project Work</h3>
            <p>Project work data will be displayed here</p>
        </div>
    `;
    $('#projectWorkContainer').html(html);
}

/**
 * Load Order Summary Data
 */
async function loadOrderSummaryData(projectNo, nachbauFile) {
    try {
        showLoader();

        const response = await axios.get('/dpm/api/OrdersPlanController.php', {
            params: {
                action: 'getOrderSummaryData',
                projectNo: projectNo,
                nachbauFile: nachbauFile
            }
        });

        hideLoader();

        if (response.data.success && response.data.data) {
            displayOrderSummaryData(response.data.data);
        } else {
            $('#orderSummaryContainer').html(`
                <div class="ui message error">
                    <p>${response.data.message || 'Failed to load order summary data'}</p>
                </div>
            `);
        }
    } catch (error) {
        hideLoader();
        console.error('Error loading order summary data:', error);
        $('#orderSummaryContainer').html(`
            <div class="ui message error">
                <p>${error.message || 'An error occurred'}</p>
            </div>
        `);
    }
}

/**
 * Display Order Summary Data
 */
function displayOrderSummaryData(data) {
    let html = `
        <div class="ui segment">
            <h3>Order Summary</h3>
            <p>Order summary data will be displayed here</p>
        </div>
    `;
    $('#orderSummaryContainer').html(html);
}

/**
 * Show Error
 */
function showError(message) {
    $('#errorMessage').text(message);
    $('#errorSection').show();
    $('#initialLoader').hide();
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
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
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