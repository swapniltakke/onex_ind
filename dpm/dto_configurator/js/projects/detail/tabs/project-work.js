// /dpm/dto_configurator/js/projects/detail/tabs/project-work.js

/**
 * Project Work Module
 * 
 * This module handles all project work-related operations including:
 * - Loading project work data
 * - Displaying work items in tables
 * - Managing work item filtering and searching
 * - Handling work item operations (edit, delete, etc.)
 * - Managing work center assignments
 * - Handling DTO work operations
 */

// ============================================================================
// GLOBAL VARIABLES
// ============================================================================

/**
 * Project work data
 * @type {Object}
 */
let projectWorkData = {};

/**
 * All work items
 * @type {Array}
 */
let allWorkItems = [];

/**
 * Filtered work items (based on current filters)
 * @type {Array}
 */
let filteredWorkItems = [];

/**
 * Work centers list
 * @type {Array}
 */
let workCentersList = [];

/**
 * Current filter criteria
 * @type {Object}
 */
let currentWorkFilters = {
    searchTerm: '',
    workCenter: 'all',
    status: 'all',
    type: 'all'
};

/**
 * Flag to track if project work module is initialized
 * @type {boolean}
 */
let isProjectWorkInitialized = false;

/**
 * Cache for work item details
 * @type {Object}
 */
let workItemCache = {};

/**
 * Current project number
 * @type {string|null}
 */
let projectWorkProjectNo = null;

/**
 * Current nachbau file
 * @type {string|null}
 */
let projectWorkNachbauFile = null;

// ============================================================================
// INITIALIZATION
// ============================================================================

/**
 * Initialize Project Work Module
 * Called when the project work tab is first loaded
 */
function initializeProjectWorkModule() {
    if (isProjectWorkInitialized) {
        return;
    }
    isProjectWorkInitialized = true;

    // Get project and nachbau info from global variables
    projectWorkProjectNo = new URLSearchParams(window.location.search).get('project');
    projectWorkNachbauFile = window.selectedNachbauFile || null;

    // Setup event listeners
    setupProjectWorkEventListeners();

    console.log('[Project Work Module] Initialized for project: ' + projectWorkProjectNo);
}

/**
 * Setup Project Work Event Listeners
 * Attach event handlers for project work interactions
 */
function setupProjectWorkEventListeners() {
    // Search functionality
    $(document).on('keyup', '#projectWorkSearchInput', function() {
        const searchTerm = $(this).val();
        handleProjectWorkSearch(searchTerm);
    });

    // Work center filter
    $(document).on('change', '#projectWorkCenterFilter', function() {
        const workCenter = $(this).val();
        handleWorkCenterFilter(workCenter);
    });

    // Status filter
    $(document).on('change', '#projectWorkStatusFilter', function() {
        const status = $(this).val();
        handleWorkStatusFilter(status);
    });

    // Type filter
    $(document).on('change', '#projectWorkTypeFilter', function() {
        const type = $(this).val();
        handleWorkTypeFilter(type);
    });

    // Work item row click
    $(document).on('click', '.work-item-row', function() {
        const workItemId = $(this).data('work-item-id');
        handleWorkItemRowClick(workItemId);
    });

    // Edit work item
    $(document).on('click', '.edit-work-item-btn', function(e) {
        e.stopPropagation();
        const workItemId = $(this).data('work-item-id');
        handleEditWorkItem(workItemId);
    });

    // Delete work item
    $(document).on('click', '.delete-work-item-btn', function(e) {
        e.stopPropagation();
        const workItemId = $(this).data('work-item-id');
        handleDeleteWorkItem(workItemId);
    });

    // Add new work item
    $(document).on('click', '#addNewWorkItemBtn', function() {
        handleAddNewWorkItem();
    });

    // Export work items
    $(document).on('click', '#exportWorkItemsBtn', function() {
        exportProjectWorkToCSV();
    });

    console.log('[Project Work Module] Event listeners attached');
}

// ============================================================================
// PROJECT WORK DATA LOADING
// ============================================================================

/**
 * Load Project Work Data
 * Fetch project work data from API
 * 
 * @param {string} projectNo - Project number
 * @param {string} nachbauFile - Nachbau file name
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
            projectWorkData = response.data.data;
            storeProjectWorkData(response.data.data);
            displayProjectWorkTab(response.data.data);
        } else {
            showProjectWorkError(response.data.message || 'Failed to load project work data');
        }
    } catch (error) {
        hideLoader();
        console.error('Error loading project work data:', error);
        showProjectWorkError(error.response?.data?.message || error.message || 'An error occurred');
    }
}

/**
 * Store Project Work Data
 * Store work data in module variables
 * 
 * @param {Object} data - Project work data
 */
function storeProjectWorkData(data) {
    try {
        // Store work items
        allWorkItems = data.workItems || [];
        filteredWorkItems = [...allWorkItems];

        // Store work centers
        workCentersList = data.workCenters || [];

        // Build cache
        workItemCache = {};
        allWorkItems.forEach(item => {
            workItemCache[item.id] = item;
        });

        console.log('[Project Work] Data stored: ' + allWorkItems.length + ' work items');

    } catch (error) {
        console.error('[Project Work] Error storing data:', error);
    }
}

// ============================================================================
// PROJECT WORK DISPLAY
// ============================================================================

/**
 * Display Project Work Tab
 * Render the project work tab content
 * 
 * @param {Object} data - Project work data
 */
function displayProjectWorkTab(data) {
    try {
        let html = `
            <div class="ui segment">
                <h3><i class="wrench icon"></i> Project Work</h3>
                <p>Manage work items and assignments for this project</p>
            </div>

            <!-- Filters Section -->
            <div class="ui segment">
                <h4>Filters & Search</h4>
                <div class="ui form">
                    <div class="four fields">
                        <div class="field">
                            <label>Search</label>
                            <input type="text" id="projectWorkSearchInput" placeholder="Search work items...">
                        </div>
                        <div class="field">
                            <label>Work Center</label>
                            <select id="projectWorkCenterFilter">
                                <option value="all">All Work Centers</option>
                                ${getWorkCenterOptions()}
                            </select>
                        </div>
                        <div class="field">
                            <label>Status</label>
                            <select id="projectWorkStatusFilter">
                                <option value="all">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Type</label>
                            <select id="projectWorkTypeFilter">
                                <option value="all">All Types</option>
                                <option value="standard">Standard</option>
                                <option value="special">Special</option>
                                <option value="spare">Spare</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="ui segment">
                <button class="ui button primary" id="addNewWorkItemBtn">
                    <i class="plus icon"></i> Add Work Item
                </button>
                <button class="ui button" id="exportWorkItemsBtn">
                    <i class="download icon"></i> Export to CSV
                </button>
            </div>

            <!-- Statistics -->
            <div class="ui segment">
                <div class="ui statistics">
                    <div class="statistic">
                        <div class="value">
                            <i class="tasks icon"></i><span id="totalWorkItems">${allWorkItems.length}</span>
                        </div>
                        <div class="label">Total Work Items</div>
                    </div>
                    <div class="statistic">
                        <div class="value">
                            <i class="hourglass start icon"></i><span id="pendingWorkItems">${getPendingWorkItemsCount()}</span>
                        </div>
                        <div class="label">Pending</div>
                    </div>
                    <div class="statistic">
                        <div class="value">
                            <i class="spinner icon"></i><span id="inProgressWorkItems">${getInProgressWorkItemsCount()}</span>
                        </div>
                        <div class="label">In Progress</div>
                    </div>
                    <div class="statistic">
                        <div class="value">
                            <i class="check circle icon"></i><span id="completedWorkItems">${getCompletedWorkItemsCount()}</span>
                        </div>
                        <div class="label">Completed</div>
                    </div>
                </div>
            </div>

            <!-- Work Items Table -->
            <div class="ui segment">
                <h4>Work Items</h4>
                <div style="overflow-x: auto;">
                    <table class="ui celled table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Description</th>
                                <th>Work Center</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="projectWorkTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        $('#projectWorkContainer').html(html);

        // Display work items
        displayProjectWorkTable(filteredWorkItems);

        // Initialize module
        initializeProjectWorkModule();

    } catch (error) {
        console.error('[Project Work] Error displaying tab:', error);
        showProjectWorkError('Error displaying project work data');
    }
}

/**
 * Display Project Work Table
 * Render work items in table format
 * 
 * @param {Array} workItems - Array of work items to display
 */
function displayProjectWorkTable(workItems) {
    try {
        const tbody = $('#projectWorkTableBody');
        tbody.empty();

        if (workItems.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">
                        No work items found
                    </td>
                </tr>
            `);
            return;
        }

        workItems.forEach(item => {
            const statusBadge = getStatusBadge(item.status);
            const typeBadge = getTypeBadge(item.type);

            const row = `
                <tr class="work-item-row" data-work-item-id="${item.id}">
                    <td>${item.id || '-'}</td>
                    <td>${item.description || '-'}</td>
                    <td>${item.work_center || '-'}</td>
                    <td>${typeBadge}</td>
                    <td>${statusBadge}</td>
                    <td>${item.quantity || '-'}</td>
                    <td>${item.unit || '-'}</td>
                    <td>
                        <button class="ui mini button blue edit-work-item-btn" data-work-item-id="${item.id}">
                            <i class="edit icon"></i> Edit
                        </button>
                        <button class="ui mini button red delete-work-item-btn" data-work-item-id="${item.id}">
                            <i class="trash icon"></i> Delete
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        console.log('[Project Work] Table displayed with ' + workItems.length + ' items');

    } catch (error) {
        console.error('[Project Work] Error displaying table:', error);
    }
}

/**
 * Get Status Badge HTML
 * Return HTML badge for status
 * 
 * @param {string} status - Status value
 * @returns {string} - HTML badge
 */
function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="ui label orange"><i class="hourglass start icon"></i> Pending</span>',
        'in_progress': '<span class="ui label blue"><i class="spinner icon"></i> In Progress</span>',
        'completed': '<span class="ui label green"><i class="check circle icon"></i> Completed</span>'
    };
    return badges[status] || '<span class="ui label gray">' + status + '</span>';
}

/**
 * Get Type Badge HTML
 * Return HTML badge for type
 * 
 * @param {string} type - Type value
 * @returns {string} - HTML badge
 */
function getTypeBadge(type) {
    const badges = {
        'standard': '<span class="ui label blue">Standard</span>',
        'special': '<span class="ui label purple">Special</span>',
        'spare': '<span class="ui label orange">Spare</span>'
    };
    return badges[type] || '<span class="ui label gray">' + type + '</span>';
}

/**
 * Get Work Center Options HTML
 * Generate select options for work centers
 * 
 * @returns {string} - HTML options
 */
function getWorkCenterOptions() {
    let html = '';
    workCentersList.forEach(center => {
        html += `<option value="${center.id}">${center.name}</option>`;
    });
    return html;
}

// ============================================================================
// PROJECT WORK FILTERING
// ============================================================================

/**
 * Handle Project Work Search
 * Filter work items by search term
 * 
 * @param {string} searchTerm - Search term
 */
function handleProjectWorkSearch(searchTerm) {
    try {
        currentWorkFilters.searchTerm = searchTerm;
        applyProjectWorkFilters();

    } catch (error) {
        console.error('[Project Work] Error searching:', error);
    }
}

/**
 * Handle Work Center Filter
 * Filter work items by work center
 * 
 * @param {string} workCenter - Work center ID
 */
function handleWorkCenterFilter(workCenter) {
    try {
        currentWorkFilters.workCenter = workCenter;
        applyProjectWorkFilters();

    } catch (error) {
        console.error('[Project Work] Error filtering by work center:', error);
    }
}

/**
 * Handle Work Status Filter
 * Filter work items by status
 * 
 * @param {string} status - Status value
 */
function handleWorkStatusFilter(status) {
    try {
        currentWorkFilters.status = status;
        applyProjectWorkFilters();

    } catch (error) {
        console.error('[Project Work] Error filtering by status:', error);
    }
}

/**
 * Handle Work Type Filter
 * Filter work items by type
 * 
 * @param {string} type - Type value
 */
function handleWorkTypeFilter(type) {
    try {
        currentWorkFilters.type = type;
        applyProjectWorkFilters();

    } catch (error) {
        console.error('[Project Work] Error filtering by type:', error);
    }
}

/**
 * Apply Project Work Filters
 * Apply all active filters to work items
 */
function applyProjectWorkFilters() {
    try {
        filteredWorkItems = allWorkItems.filter(item => {
            // Search filter
            if (currentWorkFilters.searchTerm) {
                const term = currentWorkFilters.searchTerm.toLowerCase();
                const matchesSearch = (item.description && item.description.toLowerCase().includes(term)) ||
                                     (item.id && item.id.toString().includes(term));
                if (!matchesSearch) return false;
            }

            // Work center filter
            if (currentWorkFilters.workCenter !== 'all') {
                if (item.work_center !== currentWorkFilters.workCenter) return false;
            }

            // Status filter
            if (currentWorkFilters.status !== 'all') {
                if (item.status !== currentWorkFilters.status) return false;
            }

            // Type filter
            if (currentWorkFilters.type !== 'all') {
                if (item.type !== currentWorkFilters.type) return false;
            }

            return true;
        });

        // Update table
        displayProjectWorkTable(filteredWorkItems);

        console.log('[Project Work] Filters applied: ' + filteredWorkItems.length + ' items');

    } catch (error) {
        console.error('[Project Work] Error applying filters:', error);
    }
}

// ============================================================================
// PROJECT WORK ITEM OPERATIONS
// ============================================================================

/**
 * Handle Work Item Row Click
 * Called when user clicks on a work item row
 * 
 * @param {number} workItemId - Work item ID
 */
function handleWorkItemRowClick(workItemId) {
    try {
        console.log('[Project Work] Work item clicked: ' + workItemId);
        const workItem = workItemCache[workItemId];
        if (workItem) {
            showWorkItemDetails(workItem);
        }

    } catch (error) {
        console.error('[Project Work] Error handling row click:', error);
    }
}

/**
 * Show Work Item Details
 * Display detailed information about a work item
 * 
 * @param {Object} workItem - Work item object
 */
function showWorkItemDetails(workItem) {
    try {
        let html = `
            <div class="ui modal">
                <div class="header">
                    <i class="wrench icon"></i> Work Item Details
                </div>
                <div class="content">
                    <div class="ui form">
                        <div class="field">
                            <label>ID</label>
                            <input type="text" value="${workItem.id || ''}" readonly>
                        </div>
                        <div class="field">
                            <label>Description</label>
                            <input type="text" value="${workItem.description || ''}" readonly>
                        </div>
                        <div class="field">
                            <label>Work Center</label>
                            <input type="text" value="${workItem.work_center || ''}" readonly>
                        </div>
                        <div class="field">
                            <label>Type</label>
                            <input type="text" value="${workItem.type || ''}" readonly>
                        </div>
                        <div class="field">
                            <label>Status</label>
                            <input type="text" value="${workItem.status || ''}" readonly>
                        </div>
                        <div class="field">
                            <label>Quantity</label>
                            <input type="text" value="${workItem.quantity || ''}" readonly>
                        </div>
                        <div class="field">
                            <label>Unit</label>
                            <input type="text" value="${workItem.unit || ''}" readonly>
                        </div>
                    </div>
                </div>
                <div class="actions">
                    <button class="ui button">Close</button>
                </div>
            </div>
        `;

        // Show modal
        const modal = $(html);
        modal.modal('show');

    } catch (error) {
        console.error('[Project Work] Error showing work item details:', error);
    }
}

/**
 * Handle Edit Work Item
 * Called when user clicks edit button
 * 
 * @param {number} workItemId - Work item ID
 */
function handleEditWorkItem(workItemId) {
    try {
        console.log('[Project Work] Edit work item: ' + workItemId);
        const workItem = workItemCache[workItemId];
        if (workItem) {
            showEditWorkItemModal(workItem);
        }

    } catch (error) {
        console.error('[Project Work] Error editing work item:', error);
        showProjectWorkError('Error editing work item');
    }
}

/**
 * Show Edit Work Item Modal
 * Display modal for editing work item
 * 
 * @param {Object} workItem - Work item object
 */
function showEditWorkItemModal(workItem) {
    try {
        let html = `
            <div class="ui modal" id="editWorkItemModal">
                <div class="header">
                    <i class="edit icon"></i> Edit Work Item
                </div>
                <div class="content">
                    <div class="ui form">
                        <div class="field">
                            <label>ID</label>
                            <input type="text" id="editWorkItemId" value="${workItem.id || ''}" readonly>
                        </div>
                        <div class="field">
                            <label>Description</label>
                            <input type="text" id="editWorkItemDescription" value="${workItem.description || ''}">
                        </div>
                        <div class="field">
                            <label>Work Center</label>
                            <select id="editWorkItemCenter">
                                ${getWorkCenterOptions()}
                            </select>
                        </div>
                        <div class="field">
                            <label>Type</label>
                            <select id="editWorkItemType">
                                <option value="standard" ${workItem.type === 'standard' ? 'selected' : ''}>Standard</option>
                                <option value="special" ${workItem.type === 'special' ? 'selected' : ''}>Special</option>
                                <option value="spare" ${workItem.type === 'spare' ? 'selected' : ''}>Spare</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Status</label>
                            <select id="editWorkItemStatus">
                                <option value="pending" ${workItem.status === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="in_progress" ${workItem.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                                <option value="completed" ${workItem.status === 'completed' ? 'selected' : ''}>Completed</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Quantity</label>
                            <input type="number" id="editWorkItemQuantity" value="${workItem.quantity || ''}">
                        </div>
                        <div class="field">
                            <label>Unit</label>
                            <input type="text" id="editWorkItemUnit" value="${workItem.unit || ''}">
                        </div>
                    </div>
                </div>
                <div class="actions">
                    <button class="ui button" onclick="$('#editWorkItemModal').modal('hide');">Cancel</button>
                    <button class="ui button primary" id="saveEditWorkItemBtn">Save Changes</button>
                </div>
            </div>
        `;

        // Remove old modal if exists
        $('#editWorkItemModal').remove();

        // Add and show modal
        $('body').append(html);
        $('#editWorkItemModal').modal('show');

        // Attach save handler
        $('#saveEditWorkItemBtn').off('click').on('click', function() {
            saveEditedWorkItem(workItem.id);
        });

    } catch (error) {
        console.error('[Project Work] Error showing edit modal:', error);
    }
}

/**
 * Save Edited Work Item
 * Save changes to work item
 * 
 * @param {number} workItemId - Work item ID
 */
async function saveEditedWorkItem(workItemId) {
    try {
        showLoader();

        const updatedData = {
            id: workItemId,
            description: $('#editWorkItemDescription').val(),
            work_center: $('#editWorkItemCenter').val(),
            type: $('#editWorkItemType').val(),
            status: $('#editWorkItemStatus').val(),
            quantity: $('#editWorkItemQuantity').val(),
            unit: $('#editWorkItemUnit').val()
        };

        const response = await axios.post('/dpm/api/ProjectController.php', {
            action: 'updateWorkItem',
            projectNo: projectWorkProjectNo,
            nachbauFile: projectWorkNachbauFile,
            workItem: updatedData
        });

        hideLoader();

        if (response.data.success) {
            // Update cache
            workItemCache[workItemId] = updatedData;

            // Update in allWorkItems
            const index = allWorkItems.findIndex(item => item.id === workItemId);
            if (index !== -1) {
                allWorkItems[index] = updatedData;
            }

            // Refresh display
            applyProjectWorkFilters();

            // Close modal
            $('#editWorkItemModal').modal('hide');

            console.log('[Project Work] Work item updated: ' + workItemId);
        } else {
            showProjectWorkError(response.data.message || 'Failed to update work item');
        }

    } catch (error) {
        hideLoader();
        console.error('[Project Work] Error saving work item:', error);
        showProjectWorkError(error.message || 'An error occurred');
    }
}

/**
 * Handle Delete Work Item
 * Called when user clicks delete button
 * 
 * @param {number} workItemId - Work item ID
 */
function handleDeleteWorkItem(workItemId) {
    try {
        if (confirm('Are you sure you want to delete this work item?')) {
            deleteWorkItem(workItemId);
        }

    } catch (error) {
        console.error('[Project Work] Error deleting work item:', error);
    }
}

/**
 * Delete Work Item
 * Delete a work item from the project
 * 
 * @param {number} workItemId - Work item ID
 */
async function deleteWorkItem(workItemId) {
    try {
        showLoader();

        const response = await axios.post('/dpm/api/ProjectController.php', {
            action: 'deleteWorkItem',
            projectNo: projectWorkProjectNo,
            nachbauFile: projectWorkNachbauFile,
            workItemId: workItemId
        });

        hideLoader();

        if (response.data.success) {
            // Remove from cache
            delete workItemCache[workItemId];

            // Remove from allWorkItems
            allWorkItems = allWorkItems.filter(item => item.id !== workItemId);

            // Refresh display
            applyProjectWorkFilters();

            console.log('[Project Work] Work item deleted: ' + workItemId);
        } else {
            showProjectWorkError(response.data.message || 'Failed to delete work item');
        }

    } catch (error) {
        hideLoader();
        console.error('[Project Work] Error deleting work item:', error);
        showProjectWorkError(error.message || 'An error occurred');
    }
}

/**
 * Handle Add New Work Item
 * Called when user clicks add button
 */
function handleAddNewWorkItem() {
    try {
        showAddNewWorkItemModal();

    } catch (error) {
        console.error('[Project Work] Error adding work item:', error);
        showProjectWorkError('Error adding work item');
    }
}

/**
 * Show Add New Work Item Modal
 * Display modal for adding new work item
 */
function showAddNewWorkItemModal() {
    try {
        let html = `
            <div class="ui modal" id="addWorkItemModal">
                <div class="header">
                    <i class="plus icon"></i> Add New Work Item
                </div>
                <div class="content">
                    <div class="ui form">
                        <div class="field">
                            <label>Description</label>
                            <input type="text" id="newWorkItemDescription" placeholder="Enter description">
                        </div>
                        <div class="field">
                            <label>Work Center</label>
                            <select id="newWorkItemCenter">
                                ${getWorkCenterOptions()}
                            </select>
                        </div>
                        <div class="field">
                            <label>Type</label>
                            <select id="newWorkItemType">
                                <option value="standard">Standard</option>
                                <option value="special">Special</option>
                                <option value="spare">Spare</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Status</label>
                            <select id="newWorkItemStatus">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Quantity</label>
                            <input type="number" id="newWorkItemQuantity" placeholder="Enter quantity">
                        </div>
                        <div class="field">
                            <label>Unit</label>
                            <input type="text" id="newWorkItemUnit" placeholder="Enter unit">
                        </div>
                    </div>
                </div>
                <div class="actions">
                    <button class="ui button" onclick="$('#addWorkItemModal').modal('hide');">Cancel</button>
                    <button class="ui button primary" id="saveNewWorkItemBtn">Add Work Item</button>
                </div>
            </div>
        `;

        // Remove old modal if exists
        $('#addWorkItemModal').remove();

        // Add and show modal
        $('body').append(html);
        $('#addWorkItemModal').modal('show');

        // Attach save handler
        $('#saveNewWorkItemBtn').off('click').on('click', function() {
            saveNewWorkItem();
        });

    } catch (error) {
        console.error('[Project Work] Error showing add modal:', error);
    }
}

/**
 * Save New Work Item
 * Add a new work item to the project
 */
async function saveNewWorkItem() {
    try {
        // Validate inputs
        const description = $('#newWorkItemDescription').val();
        if (!description) {
            alert('Please enter a description');
            return;
        }

        showLoader();

        const newWorkItem = {
            description: description,
            work_center: $('#newWorkItemCenter').val(),
            type: $('#newWorkItemType').val(),
            status: $('#newWorkItemStatus').val(),
            quantity: $('#newWorkItemQuantity').val(),
            unit: $('#newWorkItemUnit').val()
        };

        const response = await axios.post('/dpm/api/ProjectController.php', {
            action: 'addWorkItem',
            projectNo: projectWorkProjectNo,
            nachbauFile: projectWorkNachbauFile,
            workItem: newWorkItem
        });

        hideLoader();

        if (response.data.success) {
            // Add to allWorkItems
            newWorkItem.id = response.data.data.id;
            allWorkItems.push(newWorkItem);
            workItemCache[newWorkItem.id] = newWorkItem;

            // Refresh display
            applyProjectWorkFilters();

            // Close modal
            $('#addWorkItemModal').modal('hide');

            console.log('[Project Work] Work item added');
        } else {
            showProjectWorkError(response.data.message || 'Failed to add work item');
        }

    } catch (error) {
        hideLoader();
        console.error('[Project Work] Error saving work item:', error);
        showProjectWorkError(error.message || 'An error occurred');
    }
}

// ============================================================================
// PROJECT WORK STATISTICS
// ============================================================================

/**
 * Get Pending Work Items Count
 * Count work items with pending status
 * 
 * @returns {number} - Count of pending items
 */
function getPendingWorkItemsCount() {
    return allWorkItems.filter(item => item.status === 'pending').length;
}

/**
 * Get In Progress Work Items Count
 * Count work items with in_progress status
 * 
 * @returns {number} - Count of in progress items
 */
function getInProgressWorkItemsCount() {
    return allWorkItems.filter(item => item.status === 'in_progress').length;
}

/**
 * Get Completed Work Items Count
 * Count work items with completed status
 * 
 * @returns {number} - Count of completed items
 */
function getCompletedWorkItemsCount() {
    return allWorkItems.filter(item => item.status === 'completed').length;
}

// ============================================================================
// PROJECT WORK EXPORT
// ============================================================================

/**
 * Export Project Work to CSV
 * Export work items to CSV format
 */
function exportProjectWorkToCSV() {
    try {
        if (filteredWorkItems.length === 0) {
            alert('No work items to export');
            return;
        }

        // Define CSV headers
        const headers = [
            'ID',
            'Description',
            'Work Center',
            'Type',
            'Status',
            'Quantity',
            'Unit'
        ];

        // Build CSV content
        let csv = headers.join(',') + '\n';

        filteredWorkItems.forEach(item => {
            const row = [
                item.id || '',
                (item.description || '').replace(/,/g, ';'),
                item.work_center || '',
                item.type || '',
                item.status || '',
                item.quantity || '',
                item.unit || ''
            ];
            csv += row.join(',') + '\n';
        });

        // Create blob and download
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);

        link.setAttribute('href', url);
        link.setAttribute('download', 'project_work_' + projectWorkProjectNo + '.csv');
        link.style.visibility = 'hidden';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        console.log('[Project Work] CSV exported');

    } catch (error) {
        console.error('[Project Work] Error exporting CSV:', error);
        showProjectWorkError('Error exporting data');
    }
}

// ============================================================================
// ERROR HANDLING
// ============================================================================

/**
 * Show Project Work Error
 * Display error message
 * 
 * @param {string} message - Error message
 */
function showProjectWorkError(message) {
    try {
        console.error('[Project Work Error] ' + message);

        if ($('#errorSection').length) {
            $('#errorMessage').text(message);
            $('#errorSection').show();
        } else {
            alert('Error: ' + message);
        }

    } catch (error) {
        console.error('[Project Work] Error showing error:', error);
    }
}

/**
 * Clear Project Work Error
 * Clear error messages
 */
function clearProjectWorkError() {
    try {
        if ($('#errorSection').length) {
            $('#errorSection').hide();
            $('#errorMessage').text('');
        }

    } catch (error) {
        console.error('[Project Work] Error clearing error:', error);
    }
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Show Loader
 * Display loading spinner
 */
function showLoader() {
    $('#customLoaderOverlay').addClass('show');
}

/**
 * Hide Loader
 * Hide loading spinner
 */
function hideLoader() {
    $('#customLoaderOverlay').removeClass('show');
}

/**
 * Get Project Work Module State
 * Returns current state (for debugging)
 * 
 * @returns {Object} - Module state
 */
function getProjectWorkModuleState() {
    return {
        projectNo: projectWorkProjectNo,
        nachbauFile: projectWorkNachbauFile,
        totalWorkItems: allWorkItems.length,
        filteredWorkItems: filteredWorkItems.length,
        workCenters: workCentersList.length,
        currentFilters: currentWorkFilters,
        isInitialized: isProjectWorkInitialized
    };
}

/**
 * Log Project Work Module Info
 * Log module state for debugging
 */
function logProjectWorkModuleInfo() {
    const state = getProjectWorkModuleState();
    console.group('[Project Work Module Info]');
    console.log('Project:', state.projectNo);
    console.log('Nachbau File:', state.nachbauFile);
    console.log('Total Work Items:', state.totalWorkItems);
    console.log('Filtered Items:', state.filteredWorkItems);
    console.log('Work Centers:', state.workCenters);
    console.log('Current Filters:', state.currentFilters);
    console.log('Initialized:', state.isInitialized);
    console.groupEnd();
}