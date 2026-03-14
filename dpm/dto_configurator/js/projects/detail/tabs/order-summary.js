// /dpm/dto_configurator/js/projects/detail/tabs/order-summary.js

/**
 * Order Summary Module
 * 
 * This module handles all order summary-related operations including:
 * - Loading order summary data
 * - Displaying order items in tables
 * - Managing order filtering and searching
 * - Calculating order statistics
 * - Handling order operations (view, export, etc.)
 * - Managing special DTOs and spare parts
 */

// ============================================================================
// GLOBAL VARIABLES
// ============================================================================

/**
 * Order summary data
 * @type {Object}
 */
let orderSummaryData = {};

/**
 * All order items
 * @type {Array}
 */
let allOrderItems = [];

/**
 * Filtered order items (based on current filters)
 * @type {Array}
 */
let filteredOrderItems = [];

/**
 * Special DTO items
 * @type {Array}
 */
let specialDTOItems = [];

/**
 * Spare part items
 * @type {Array}
 */
let sparePartItems = [];

/**
 * Current filter criteria
 * @type {Object}
 */
let currentOrderFilters = {
    searchTerm: '',
    itemType: 'all',
    status: 'all',
    priority: 'all'
};

/**
 * Flag to track if order summary module is initialized
 * @type {boolean}
 */
let isOrderSummaryInitialized = false;

/**
 * Cache for order item details
 * @type {Object}
 */
let orderItemCache = {};

/**
 * Current project number
 * @type {string|null}
 */
let orderSummaryProjectNo = null;

/**
 * Current nachbau file
 * @type {string|null}
 */
let orderSummaryNachbauFile = null;

/**
 * Order summary statistics
 * @type {Object}
 */
let orderSummaryStats = {
    totalItems: 0,
    totalQuantity: 0,
    totalValue: 0,
    standardItems: 0,
    specialDTOs: 0,
    spareParts: 0
};

// ============================================================================
// INITIALIZATION
// ============================================================================

/**
 * Initialize Order Summary Module
 * Called when the order summary tab is first loaded
 */
function initializeOrderSummaryModule() {
    if (isOrderSummaryInitialized) {
        return;
    }
    isOrderSummaryInitialized = true;

    // Get project and nachbau info from global variables
    orderSummaryProjectNo = new URLSearchParams(window.location.search).get('project');
    orderSummaryNachbauFile = window.selectedNachbauFile || null;

    // Setup event listeners
    setupOrderSummaryEventListeners();

    console.log('[Order Summary Module] Initialized for project: ' + orderSummaryProjectNo);
}

/**
 * Setup Order Summary Event Listeners
 * Attach event handlers for order summary interactions
 */
function setupOrderSummaryEventListeners() {
    // Search functionality
    $(document).on('keyup', '#orderSummarySearchInput', function() {
        const searchTerm = $(this).val();
        handleOrderSummarySearch(searchTerm);
    });

    // Item type filter
    $(document).on('change', '#orderItemTypeFilter', function() {
        const itemType = $(this).val();
        handleOrderItemTypeFilter(itemType);
    });

    // Status filter
    $(document).on('change', '#orderStatusFilter', function() {
        const status = $(this).val();
        handleOrderStatusFilter(status);
    });

    // Priority filter
    $(document).on('change', '#orderPriorityFilter', function() {
        const priority = $(this).val();
        handleOrderPriorityFilter(priority);
    });

    // Order item row click
    $(document).on('click', '.order-item-row', function() {
        const orderItemId = $(this).data('order-item-id');
        handleOrderItemRowClick(orderItemId);
    });

    // View order item details
    $(document).on('click', '.view-order-item-btn', function(e) {
        e.stopPropagation();
        const orderItemId = $(this).data('order-item-id');
        handleViewOrderItem(orderItemId);
    });

    // Export order summary
    $(document).on('click', '#exportOrderSummaryBtn', function() {
        exportOrderSummaryToCSV();
    });

    // Tab switching
    $(document).on('click', '.order-summary-tab-button', function() {
        const tabName = $(this).data('tab');
        switchOrderSummaryTab(tabName);
    });

    console.log('[Order Summary Module] Event listeners attached');
}

// ============================================================================
// ORDER SUMMARY DATA LOADING
// ============================================================================

/**
 * Load Order Summary Data
 * Fetch order summary data from API
 * 
 * @param {string} projectNo - Project number
 * @param {string} nachbauFile - Nachbau file name
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
            orderSummaryData = response.data.data;
            storeOrderSummaryData(response.data.data);
            displayOrderSummaryTab(response.data.data);
        } else {
            showOrderSummaryError(response.data.message || 'Failed to load order summary data');
        }
    } catch (error) {
        hideLoader();
        console.error('Error loading order summary data:', error);
        showOrderSummaryError(error.response?.data?.message || error.message || 'An error occurred');
    }
}

/**
 * Store Order Summary Data
 * Store order data in module variables
 * 
 * @param {Object} data - Order summary data
 */
function storeOrderSummaryData(data) {
    try {
        // Store order items
        allOrderItems = data.orderItems || [];
        filteredOrderItems = [...allOrderItems];

        // Store special items
        specialDTOItems = data.specialDTOs || [];
        sparePartItems = data.spareParts || [];

        // Store statistics
        orderSummaryStats = data.statistics || {
            totalItems: allOrderItems.length,
            totalQuantity: calculateTotalQuantity(allOrderItems),
            totalValue: calculateTotalValue(allOrderItems),
            standardItems: allOrderItems.filter(i => i.type === 'standard').length,
            specialDTOs: specialDTOItems.length,
            spareParts: sparePartItems.length
        };

        // Build cache
        orderItemCache = {};
        allOrderItems.forEach(item => {
            orderItemCache[item.id] = item;
        });

        console.log('[Order Summary] Data stored: ' + allOrderItems.length + ' order items');

    } catch (error) {
        console.error('[Order Summary] Error storing data:', error);
    }
}

// ============================================================================
// ORDER SUMMARY DISPLAY
// ============================================================================

/**
 * Display Order Summary Tab
 * Render the order summary tab content
 * 
 * @param {Object} data - Order summary data
 */
function displayOrderSummaryTab(data) {
    try {
        let html = `
            <div class="ui segment">
                <h3><i class="chart bar icon"></i> Order Summary</h3>
                <p>View and manage order items for this project</p>
            </div>

            <!-- Tab Menu -->
            <div class="ui top attached tabular menu">
                <a class="active item order-summary-tab-button" data-tab="standard">
                    <i class="list icon"></i> Standard Items
                </a>
                <a class="item order-summary-tab-button" data-tab="special">
                    <i class="star icon"></i> Special DTOs
                </a>
                <a class="item order-summary-tab-button" data-tab="spare">
                    <i class="wrench icon"></i> Spare Parts
                </a>
            </div>

            <!-- Filters Section -->
            <div class="ui segment">
                <h4>Filters & Search</h4>
                <div class="ui form">
                    <div class="four fields">
                        <div class="field">
                            <label>Search</label>
                            <input type="text" id="orderSummarySearchInput" placeholder="Search order items...">
                        </div>
                        <div class="field">
                            <label>Item Type</label>
                            <select id="orderItemTypeFilter">
                                <option value="all">All Types</option>
                                <option value="standard">Standard</option>
                                <option value="special">Special</option>
                                <option value="spare">Spare</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Status</label>
                            <select id="orderStatusFilter">
                                <option value="all">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="ordered">Ordered</option>
                                <option value="received">Received</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Priority</label>
                            <select id="orderPriorityFilter">
                                <option value="all">All Priorities</option>
                                <option value="high">High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="ui segment">
                <button class="ui button" id="exportOrderSummaryBtn">
                    <i class="download icon"></i> Export to CSV
                </button>
            </div>

            <!-- Statistics -->
            <div class="ui segment">
                <div class="ui statistics">
                    <div class="statistic">
                        <div class="value">
                            <i class="box icon"></i><span id="totalOrderItems">${orderSummaryStats.totalItems}</span>
                        </div>
                        <div class="label">Total Items</div>
                    </div>
                    <div class="statistic">
                        <div class="value">
                            <i class="cubes icon"></i><span id="totalOrderQuantity">${orderSummaryStats.totalQuantity}</span>
                        </div>
                        <div class="label">Total Quantity</div>
                    </div>
                    <div class="statistic">
                        <div class="value">
                            <i class="dollar icon"></i><span id="totalOrderValue">${formatCurrency(orderSummaryStats.totalValue)}</span>
                        </div>
                        <div class="label">Total Value</div>
                    </div>
                    <div class="statistic">
                        <div class="value">
                            <i class="star icon"></i><span id="specialDTOCount">${orderSummaryStats.specialDTOs}</span>
                        </div>
                        <div class="label">Special DTOs</div>
                    </div>
                    <div class="statistic">
                        <div class="value">
                            <i class="wrench icon"></i><span id="sparePartCount">${orderSummaryStats.spareParts}</span>
                        </div>
                        <div class="label">Spare Parts</div>
                    </div>
                </div>
            </div>

            <!-- Order Items Table -->
            <div class="ui segment">
                <h4>Order Items</h4>
                <div style="overflow-x: auto;">
                    <table class="ui celled table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Material Number</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Unit Price</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="orderSummaryTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        $('#orderSummaryContainer').html(html);

        // Display order items
        displayOrderSummaryTable(filteredOrderItems);

        // Initialize module
        initializeOrderSummaryModule();

    } catch (error) {
        console.error('[Order Summary] Error displaying tab:', error);
        showOrderSummaryError('Error displaying order summary data');
    }
}

/**
 * Display Order Summary Table
 * Render order items in table format
 * 
 * @param {Array} orderItems - Array of order items to display
 */
function displayOrderSummaryTable(orderItems) {
    try {
        const tbody = $('#orderSummaryTableBody');
        tbody.empty();

        if (orderItems.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="11" style="text-align: center; padding: 20px;">
                        No order items found
                    </td>
                </tr>
            `);
            return;
        }

        orderItems.forEach(item => {
            const statusBadge = getOrderStatusBadge(item.status);
            const priorityBadge = getOrderPriorityBadge(item.priority);
            const totalPrice = (item.quantity || 0) * (item.unit_price || 0);

            const row = `
                <tr class="order-item-row" data-order-item-id="${item.id}">
                    <td>${item.id || '-'}</td>
                    <td>${item.material_number || '-'}</td>
                    <td>${item.description || '-'}</td>
                    <td>${item.type || '-'}</td>
                    <td>${item.quantity || '-'}</td>
                    <td>${item.unit || '-'}</td>
                    <td>${formatCurrency(item.unit_price || 0)}</td>
                    <td>${formatCurrency(totalPrice)}</td>
                    <td>${statusBadge}</td>
                    <td>${priorityBadge}</td>
                    <td>
                        <button class="ui mini button blue view-order-item-btn" data-order-item-id="${item.id}">
                            <i class="eye icon"></i> View
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        console.log('[Order Summary] Table displayed with ' + orderItems.length + ' items');

    } catch (error) {
        console.error('[Order Summary] Error displaying table:', error);
    }
}

/**
 * Get Order Status Badge HTML
 * Return HTML badge for order status
 * 
 * @param {string} status - Status value
 * @returns {string} - HTML badge
 */
function getOrderStatusBadge(status) {
    const badges = {
        'pending': '<span class="ui label orange"><i class="hourglass start icon"></i> Pending</span>',
        'ordered': '<span class="ui label blue"><i class="shopping cart icon"></i> Ordered</span>',
        'received': '<span class="ui label green"><i class="check circle icon"></i> Received</span>'
    };
    return badges[status] || '<span class="ui label gray">' + status + '</span>';
}

/**
 * Get Order Priority Badge HTML
 * Return HTML badge for order priority
 * 
 * @param {string} priority - Priority value
 * @returns {string} - HTML badge
 */
function getOrderPriorityBadge(priority) {
    const badges = {
        'high': '<span class="ui label red"><i class="arrow up icon"></i> High</span>',
        'medium': '<span class="ui label yellow"><i class="minus icon"></i> Medium</span>',
        'low': '<span class="ui label green"><i class="arrow down icon"></i> Low</span>'
    };
    return badges[priority] || '<span class="ui label gray">' + priority + '</span>';
}

// ============================================================================
// ORDER SUMMARY FILTERING
// ============================================================================

/**
 * Handle Order Summary Search
 * Filter order items by search term
 * 
 * @param {string} searchTerm - Search term
 */
function handleOrderSummarySearch(searchTerm) {
    try {
        currentOrderFilters.searchTerm = searchTerm;
        applyOrderSummaryFilters();

    } catch (error) {
        console.error('[Order Summary] Error searching:', error);
    }
}

/**
 * Handle Order Item Type Filter
 * Filter order items by type
 * 
 * @param {string} itemType - Item type
 */
function handleOrderItemTypeFilter(itemType) {
    try {
        currentOrderFilters.itemType = itemType;
        applyOrderSummaryFilters();

    } catch (error) {
        console.error('[Order Summary] Error filtering by type:', error);
    }
}

/**
 * Handle Order Status Filter
 * Filter order items by status
 * 
 * @param {string} status - Status value
 */
function handleOrderStatusFilter(status) {
    try {
        currentOrderFilters.status = status;
        applyOrderSummaryFilters();

    } catch (error) {
        console.error('[Order Summary] Error filtering by status:', error);
    }
}

/**
 * Handle Order Priority Filter
 * Filter order items by priority
 * 
 * @param {string} priority - Priority value
 */
function handleOrderPriorityFilter(priority) {
    try {
        currentOrderFilters.priority = priority;
        applyOrderSummaryFilters();

    } catch (error) {
        console.error('[Order Summary] Error filtering by priority:', error);
    }
}

/**
 * Apply Order Summary Filters
 * Apply all active filters to order items
 */
function applyOrderSummaryFilters() {
    try {
        filteredOrderItems = allOrderItems.filter(item => {
            // Search filter
            if (currentOrderFilters.searchTerm) {
                const term = currentOrderFilters.searchTerm.toLowerCase();
                const matchesSearch = (item.description && item.description.toLowerCase().includes(term)) ||
                                     (item.material_number && item.material_number.toLowerCase().includes(term)) ||
                                     (item.id && item.id.toString().includes(term));
                if (!matchesSearch) return false;
            }

            // Item type filter
            if (currentOrderFilters.itemType !== 'all') {
                if (item.type !== currentOrderFilters.itemType) return false;
            }

            // Status filter
            if (currentOrderFilters.status !== 'all') {
                if (item.status !== currentOrderFilters.status) return false;
            }

            // Priority filter
            if (currentOrderFilters.priority !== 'all') {
                if (item.priority !== currentOrderFilters.priority) return false;
            }

            return true;
        });

        // Update table
        displayOrderSummaryTable(filteredOrderItems);

        console.log('[Order Summary] Filters applied: ' + filteredOrderItems.length + ' items');

    } catch (error) {
        console.error('[Order Summary] Error applying filters:', error);
    }
}

// ============================================================================
// ORDER ITEM OPERATIONS
// ============================================================================

/**
 * Handle Order Item Row Click
 * Called when user clicks on an order item row
 * 
 * @param {number} orderItemId - Order item ID
 */
function handleOrderItemRowClick(orderItemId) {
    try {
        console.log('[Order Summary] Order item clicked: ' + orderItemId);
        const orderItem = orderItemCache[orderItemId];
        if (orderItem) {
            showOrderItemDetails(orderItem);
        }

    } catch (error) {
        console.error('[Order Summary] Error handling row click:', error);
    }
}

/**
 * Handle View Order Item
 * Called when user clicks view button
 * 
 * @param {number} orderItemId - Order item ID
 */
function handleViewOrderItem(orderItemId) {
    try {
        console.log('[Order Summary] View order item: ' + orderItemId);
        const orderItem = orderItemCache[orderItemId];
        if (orderItem) {
            showOrderItemDetails(orderItem);
        }

    } catch (error) {
        console.error('[Order Summary] Error viewing order item:', error);
        showOrderSummaryError('Error viewing order item');
    }
}

/**
 * Show Order Item Details
 * Display detailed information about an order item
 * 
 * @param {Object} orderItem - Order item object
 */
function showOrderItemDetails(orderItem) {
    try {
        const totalPrice = (orderItem.quantity || 0) * (orderItem.unit_price || 0);

        let html = `
            <div class="ui modal" id="orderItemDetailsModal">
                <div class="header">
                    <i class="box icon"></i> Order Item Details
                </div>
                <div class="content">
                    <div class="ui form">
                        <div class="two fields">
                            <div class="field">
                                <label>ID</label>
                                <input type="text" value="${orderItem.id || ''}" readonly>
                            </div>
                            <div class="field">
                                <label>Material Number</label>
                                <input type="text" value="${orderItem.material_number || ''}" readonly>
                            </div>
                        </div>
                        <div class="field">
                            <label>Description</label>
                            <input type="text" value="${orderItem.description || ''}" readonly>
                        </div>
                        <div class="two fields">
                            <div class="field">
                                <label>Type</label>
                                <input type="text" value="${orderItem.type || ''}" readonly>
                            </div>
                            <div class="field">
                                <label>Status</label>
                                <input type="text" value="${orderItem.status || ''}" readonly>
                            </div>
                        </div>
                        <div class="two fields">
                            <div class="field">
                                <label>Priority</label>
                                <input type="text" value="${orderItem.priority || ''}" readonly>
                            </div>
                            <div class="field">
                                <label>Unit</label>
                                <input type="text" value="${orderItem.unit || ''}" readonly>
                            </div>
                        </div>
                        <div class="three fields">
                            <div class="field">
                                <label>Quantity</label>
                                <input type="text" value="${orderItem.quantity || ''}" readonly>
                            </div>
                            <div class="field">
                                <label>Unit Price</label>
                                <input type="text" value="${formatCurrency(orderItem.unit_price || 0)}" readonly>
                            </div>
                            <div class="field">
                                <label>Total Price</label>
                                <input type="text" value="${formatCurrency(totalPrice)}" readonly>
                            </div>
                        </div>
                        <div class="field">
                            <label>Notes</label>
                            <textarea readonly>${orderItem.notes || ''}</textarea>
                        </div>
                    </div>
                </div>
                <div class="actions">
                    <button class="ui button" onclick="$('#orderItemDetailsModal').modal('hide');">Close</button>
                </div>
            </div>
        `;

        // Remove old modal if exists
        $('#orderItemDetailsModal').remove();

        // Add and show modal
        $('body').append(html);
        $('#orderItemDetailsModal').modal('show');

    } catch (error) {
        console.error('[Order Summary] Error showing order item details:', error);
    }
}

// ============================================================================
// ORDER SUMMARY TAB SWITCHING
// ============================================================================

/**
 * Switch Order Summary Tab
 * Switch between standard, special, and spare tabs
 * 
 * @param {string} tabName - Tab name (standard, special, spare)
 */
function switchOrderSummaryTab(tabName) {
    try {
        // Update active button
        $('.order-summary-tab-button').removeClass('active');
        $('.order-summary-tab-button[data-tab="' + tabName + '"]').addClass('active');

        // Filter items based on tab
        let itemsToDisplay = [];

        switch(tabName) {
            case 'standard':
                itemsToDisplay = allOrderItems.filter(item => item.type === 'standard');
                break;
            case 'special':
                itemsToDisplay = specialDTOItems;
                break;
            case 'spare':
                itemsToDisplay = sparePartItems;
                break;
            default:
                itemsToDisplay = allOrderItems;
        }

        // Display filtered items
        displayOrderSummaryTable(itemsToDisplay);

        console.log('[Order Summary] Tab switched to: ' + tabName);

    } catch (error) {
        console.error('[Order Summary] Error switching tab:', error);
    }
}

// ============================================================================
// ORDER SUMMARY STATISTICS
// ============================================================================

/**
 * Calculate Total Quantity
 * Calculate total quantity of all order items
 * 
 * @param {Array} items - Array of order items
 * @returns {number} - Total quantity
 */
function calculateTotalQuantity(items) {
    return items.reduce((total, item) => total + (item.quantity || 0), 0);
}

/**
 * Calculate Total Value
 * Calculate total value of all order items
 * 
 * @param {Array} items - Array of order items
 * @returns {number} - Total value
 */
function calculateTotalValue(items) {
    return items.reduce((total, item) => {
        const itemTotal = (item.quantity || 0) * (item.unit_price || 0);
        return total + itemTotal;
    }, 0);
}

/**
 * Get Order Summary Statistics
 * Get comprehensive order statistics
 * 
 * @returns {Object} - Statistics object
 */
function getOrderSummaryStatistics() {
    return {
        totalItems: allOrderItems.length,
        totalQuantity: calculateTotalQuantity(allOrderItems),
        totalValue: calculateTotalValue(allOrderItems),
        standardItems: allOrderItems.filter(i => i.type === 'standard').length,
        specialDTOs: specialDTOItems.length,
        spareParts: sparePartItems.length,
        pendingItems: allOrderItems.filter(i => i.status === 'pending').length,
        orderedItems: allOrderItems.filter(i => i.status === 'ordered').length,
        receivedItems: allOrderItems.filter(i => i.status === 'received').length
    };
}

// ============================================================================
// ORDER SUMMARY EXPORT
// ============================================================================

/**
 * Export Order Summary to CSV
 * Export order items to CSV format
 */
function exportOrderSummaryToCSV() {
    try {
        if (filteredOrderItems.length === 0) {
            alert('No order items to export');
            return;
        }

        // Define CSV headers
        const headers = [
            'ID',
            'Material Number',
            'Description',
            'Type',
            'Quantity',
            'Unit',
            'Unit Price',
            'Total Price',
            'Status',
            'Priority'
        ];

        // Build CSV content
        let csv = headers.join(',') + '\n';

        filteredOrderItems.forEach(item => {
            const totalPrice = (item.quantity || 0) * (item.unit_price || 0);
            const row = [
                item.id || '',
                item.material_number || '',
                (item.description || '').replace(/,/g, ';'),
                item.type || '',
                item.quantity || '',
                item.unit || '',
                item.unit_price || '',
                totalPrice || '',
                item.status || '',
                item.priority || ''
            ];
            csv += row.join(',') + '\n';
        });

        // Create blob and download
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);

        link.setAttribute('href', url);
        link.setAttribute('download', 'order_summary_' + orderSummaryProjectNo + '.csv');
        link.style.visibility = 'hidden';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        console.log('[Order Summary] CSV exported');

    } catch (error) {
        console.error('[Order Summary] Error exporting CSV:', error);
        showOrderSummaryError('Error exporting data');
    }
}

// ============================================================================
// ERROR HANDLING
// ============================================================================

/**
 * Show Order Summary Error
 * Display error message
 * 
 * @param {string} message - Error message
 */
function showOrderSummaryError(message) {
    try {
        console.error('[Order Summary Error] ' + message);

        if ($('#errorSection').length) {
            $('#errorMessage').text(message);
            $('#errorSection').show();
        } else {
            alert('Error: ' + message);
        }

    } catch (error) {
        console.error('[Order Summary] Error showing error:', error);
    }
}

/**
 * Clear Order Summary Error
 * Clear error messages
 */
function clearOrderSummaryError() {
    try {
        if ($('#errorSection').length) {
            $('#errorSection').hide();
            $('#errorMessage').text('');
        }

    } catch (error) {
        console.error('[Order Summary] Error clearing error:', error);
    }
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Format Currency
 * Format number as currency
 * 
 * @param {number} value - Value to format
 * @returns {string} - Formatted currency string
 */
function formatCurrency(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(value);
}

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
 * Get Order Summary Module State
 * Returns current state (for debugging)
 * 
 * @returns {Object} - Module state
 */
function getOrderSummaryModuleState() {
    return {
        projectNo: orderSummaryProjectNo,
        nachbauFile: orderSummaryNachbauFile,
        totalOrderItems: allOrderItems.length,
        filteredOrderItems: filteredOrderItems.length,
        specialDTOs: specialDTOItems.length,
        spareParts: sparePartItems.length,
        currentFilters: currentOrderFilters,
        statistics: getOrderSummaryStatistics(),
        isInitialized: isOrderSummaryInitialized
    };
}

/**
 * Log Order Summary Module Info
 * Log module state for debugging
 */
function logOrderSummaryModuleInfo() {
    const state = getOrderSummaryModuleState();
    console.group('[Order Summary Module Info]');
    console.log('Project:', state.projectNo);
    console.log('Nachbau File:', state.nachbauFile);
    console.log('Total Order Items:', state.totalOrderItems);
    console.log('Filtered Items:', state.filteredOrderItems);
    console.log('Special DTOs:', state.specialDTOs);
    console.log('Spare Parts:', state.spareParts);
    console.log('Current Filters:', state.currentFilters);
    console.log('Statistics:', state.statistics);
    console.log('Initialized:', state.isInitialized);
    console.groupEnd();
}