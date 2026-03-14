// /dpm/dto_configurator/js/projects/detail/nachbau-data.js

/**
 * Nachbau Data Operations Module
 * 
 * This module handles all nachbau-related operations including:
 * - Storing selected nachbau file information
 * - Managing nachbau file list
 * - Providing utility functions for nachbau operations
 * - Handling nachbau-specific event listeners
 */

// ============================================================================
// GLOBAL VARIABLES
// ============================================================================

/**
 * Currently selected nachbau file name
 * @type {string|null}
 */
let selectedNachbauFile = null;

/**
 * List of all nachbau files for current project
 * @type {Array}
 */
let nachbauFilesList = [];

/**
 * Current nachbau data (materials, etc.)
 * @type {Object}
 */
let currentNachbauData = {};

/**
 * Flag to track if nachbau module is initialized
 * @type {boolean}
 */
let isNachbauModuleInitialized = false;

/**
 * Cache for nachbau file details
 * @type {Object}
 */
let nachbauFileCache = {};

/**
 * Current project number
 * @type {string|null}
 */
let currentProjectNo = null;

// ============================================================================
// INITIALIZATION
// ============================================================================

/**
 * Initialize Nachbau Module
 * Called when the page loads to set up event listeners and state
 */
function initializeNachbauModule() {
    if (isNachbauModuleInitialized) {
        return;
    }
    isNachbauModuleInitialized = true;

    // Get project number from URL
    currentProjectNo = new URLSearchParams(window.location.search).get('project');

    // Set up event listeners
    setupNachbauEventListeners();

    console.log('[Nachbau Module] Initialized for project: ' + currentProjectNo);
}

/**
 * Setup Nachbau Event Listeners
 * Attach event handlers for nachbau-related interactions
 */
function setupNachbauEventListeners() {
    // Event listener for nachbau file selection
    $(document).on('click', '.select-nachbau-btn', function(e) {
        e.preventDefault();
        const nachbauFile = $(this).data('nachbau-file');
        handleNachbauFileSelection(nachbauFile);
    });

    // Event listener for panel filter tabs
    $(document).on('click', '.panel-tab-button', function(e) {
        e.preventDefault();
        const selectedPanel = $(this).data('panel');
        handlePanelFilterChange(selectedPanel);
    });

    console.log('[Nachbau Module] Event listeners attached');
}

// ============================================================================
// NACHBAU FILE SELECTION
// ============================================================================

/**
 * Handle Nachbau File Selection
 * Called when user selects a nachbau file from the list
 * 
 * @param {string} nachbauFile - The selected nachbau file name
 */
function handleNachbauFileSelection(nachbauFile) {
    try {
        console.log('[Nachbau] File selected: ' + nachbauFile);

        // Store selected file
        selectedNachbauFile = nachbauFile;

        // Update UI to show selected file
        updateNachbauFileSelectionUI(nachbauFile);

        // Trigger kuko matrix data load (in info.js)
        if (typeof selectNachbauFile === 'function') {
            selectNachbauFile(currentProjectNo, nachbauFile);
        }

    } catch (error) {
        console.error('[Nachbau] Error selecting file:', error);
        showNachbauError('Error selecting nachbau file');
    }
}

/**
 * Update Nachbau File Selection UI
 * Highlight the selected file in the list
 * 
 * @param {string} nachbauFile - The selected nachbau file name
 */
function updateNachbauFileSelectionUI(nachbauFile) {
    try {
        // Remove active class from all items
        $('.nachbau-file-item').removeClass('active');

        // Add active class to selected item
        $('.nachbau-file-item[data-nachbau-file="' + nachbauFile + '"]').addClass('active');

        console.log('[Nachbau] UI updated for file: ' + nachbauFile);

    } catch (error) {
        console.error('[Nachbau] Error updating UI:', error);
    }
}

/**
 * Get Selected Nachbau File
 * Returns the currently selected nachbau file
 * 
 * @returns {string|null} - The selected nachbau file name or null
 */
function getSelectedNachbauFile() {
    return selectedNachbauFile;
}

/**
 * Set Selected Nachbau File
 * Programmatically set the selected nachbau file
 * 
 * @param {string} nachbauFile - The nachbau file name to set
 */
function setSelectedNachbauFile(nachbauFile) {
    selectedNachbauFile = nachbauFile;
    updateNachbauFileSelectionUI(nachbauFile);
}

// ============================================================================
// NACHBAU FILES LIST MANAGEMENT
// ============================================================================

/**
 * Store Nachbau Files List
 * Store the list of nachbau files for current project
 * 
 * @param {Array} files - Array of nachbau file objects
 */
function storeNachbauFilesList(files) {
    try {
        nachbauFilesList = files || [];

        // Build cache for quick lookup
        nachbauFileCache = {};
        files.forEach(file => {
            nachbauFileCache[file.FileName] = file;
        });

        console.log('[Nachbau] Files list stored: ' + files.length + ' files');

    } catch (error) {
        console.error('[Nachbau] Error storing files list:', error);
    }
}

/**
 * Get Nachbau Files List
 * Returns the list of nachbau files for current project
 * 
 * @returns {Array} - Array of nachbau file objects
 */
function getNachbauFilesList() {
    return nachbauFilesList;
}

/**
 * Get Nachbau File Details
 * Get details for a specific nachbau file
 * 
 * @param {string} fileName - The nachbau file name
 * @returns {Object|null} - The file object or null if not found
 */
function getNachbauFileDetails(fileName) {
    return nachbauFileCache[fileName] || null;
}

/**
 * Get Nachbau Files Count
 * Returns the total number of nachbau files
 * 
 * @returns {number} - Total number of files
 */
function getNachbauFilesCount() {
    return nachbauFilesList.length;
}

// ============================================================================
// NACHBAU DATA MANAGEMENT
// ============================================================================

/**
 * Store Current Nachbau Data
 * Store the materials data for currently selected nachbau file
 * 
 * @param {Object} data - The nachbau data object containing materials, summary, etc.
 */
function storeCurrentNachbauData(data) {
    try {
        currentNachbauData = data || {};

        console.log('[Nachbau] Current data stored for file: ' + data.nachbauFile);
        console.log('[Nachbau] Total materials: ' + data.materials.length);

    } catch (error) {
        console.error('[Nachbau] Error storing current data:', error);
    }
}

/**
 * Get Current Nachbau Data
 * Returns the materials data for currently selected nachbau file
 * 
 * @returns {Object} - The nachbau data object
 */
function getCurrentNachbauData() {
    return currentNachbauData;
}

/**
 * Get Nachbau Materials
 * Returns all materials for currently selected nachbau file
 * 
 * @returns {Array} - Array of material objects
 */
function getNachbauMaterials() {
    return currentNachbauData.materials || [];
}

/**
 * Get Nachbau Summary
 * Returns summary statistics for currently selected nachbau file
 * 
 * @returns {Object} - Summary object with totalMaterials, totalPanels, totalFields
 */
function getNachbauSummary() {
    return currentNachbauData.summary || {
        totalMaterials: 0,
        totalPanels: 0,
        totalFields: 0
    };
}

// ============================================================================
// PANEL FILTERING
// ============================================================================

/**
 * Handle Panel Filter Change
 * Called when user clicks on a panel filter button
 * 
 * @param {string} panelNo - The selected panel number ('all' for all panels)
 */
function handlePanelFilterChange(panelNo) {
    try {
        console.log('[Nachbau] Panel filter changed: ' + panelNo);

        // Update filter UI
        updatePanelFilterUI(panelNo);

        // Get filtered materials
        const filteredMaterials = getFilteredMaterials(panelNo);

        // Update kuko matrix table
        if (typeof displayKukoMatrixTable === 'function') {
            displayKukoMatrixTable(filteredMaterials);
        }

    } catch (error) {
        console.error('[Nachbau] Error changing panel filter:', error);
    }
}

/**
 * Update Panel Filter UI
 * Highlight the selected panel filter button
 * 
 * @param {string} panelNo - The selected panel number
 */
function updatePanelFilterUI(panelNo) {
    try {
        // Remove active class from all buttons
        $('.panel-tab-button').removeClass('active');

        // Add active class to selected button
        $('.panel-tab-button[data-panel="' + panelNo + '"]').addClass('active');

        console.log('[Nachbau] Panel filter UI updated: ' + panelNo);

    } catch (error) {
        console.error('[Nachbau] Error updating panel filter UI:', error);
    }
}

/**
 * Get Filtered Materials
 * Filter materials by panel number
 * 
 * @param {string} panelNo - The panel number to filter by ('all' for all panels)
 * @returns {Array} - Filtered array of material objects
 */
function getFilteredMaterials(panelNo) {
    try {
        const materials = getNachbauMaterials();

        if (panelNo === 'all') {
            return materials;
        }

        return materials.filter(material => material.panel_no === panelNo);

    } catch (error) {
        console.error('[Nachbau] Error filtering materials:', error);
        return [];
    }
}

// ============================================================================
// NACHBAU FIELD OPERATIONS
// ============================================================================

/**
 * Get Unique Panels
 * Get list of unique panel numbers from materials
 * 
 * @returns {Array} - Sorted array of unique panel numbers
 */
function getUniquePanels() {
    try {
        const materials = getNachbauMaterials();
        const panels = [...new Set(materials.map(m => m.panel_no))].filter(p => p).sort();
        return panels;

    } catch (error) {
        console.error('[Nachbau] Error getting unique panels:', error);
        return [];
    }
}

/**
 * Get Unique Fields
 * Get list of unique field names from materials
 * 
 * @returns {Array} - Sorted array of unique field names
 */
function getUniqueFields() {
    try {
        const materials = getNachbauMaterials();
        const fields = [...new Set(materials.map(m => m.feld_name))].filter(f => f).sort();
        return fields;

    } catch (error) {
        console.error('[Nachbau] Error getting unique fields:', error);
        return [];
    }
}

/**
 * Get Materials by Panel
 * Get all materials for a specific panel
 * 
 * @param {string} panelNo - The panel number
 * @returns {Array} - Array of material objects for the panel
 */
function getMaterialsByPanel(panelNo) {
    try {
        const materials = getNachbauMaterials();
        return materials.filter(m => m.panel_no === panelNo);

    } catch (error) {
        console.error('[Nachbau] Error getting materials by panel:', error);
        return [];
    }
}

/**
 * Get Materials by Field
 * Get all materials for a specific field
 * 
 * @param {string} feldName - The field name
 * @returns {Array} - Array of material objects for the field
 */
function getMaterialsByField(feldName) {
    try {
        const materials = getNachbauMaterials();
        return materials.filter(m => m.feld_name === feldName);

    } catch (error) {
        console.error('[Nachbau] Error getting materials by field:', error);
        return [];
    }
}

/**
 * Get Materials by Panel and Field
 * Get all materials for a specific panel and field combination
 * 
 * @param {string} panelNo - The panel number
 * @param {string} feldName - The field name
 * @returns {Array} - Array of material objects
 */
function getMaterialsByPanelAndField(panelNo, feldName) {
    try {
        const materials = getNachbauMaterials();
        return materials.filter(m => m.panel_no === panelNo && m.feld_name === feldName);

    } catch (error) {
        console.error('[Nachbau] Error getting materials by panel and field:', error);
        return [];
    }
}

// ============================================================================
// NACHBAU MATERIAL OPERATIONS
// ============================================================================

/**
 * Get Material by ID
 * Get a specific material by its ID
 * 
 * @param {number} materialId - The material ID
 * @returns {Object|null} - The material object or null if not found
 */
function getMaterialById(materialId) {
    try {
        const materials = getNachbauMaterials();
        return materials.find(m => m.id === materialId) || null;

    } catch (error) {
        console.error('[Nachbau] Error getting material by ID:', error);
        return null;
    }
}

/**
 * Get Material Count by Panel
 * Get the number of materials in a specific panel
 * 
 * @param {string} panelNo - The panel number
 * @returns {number} - Number of materials in the panel
 */
function getMaterialCountByPanel(panelNo) {
    try {
        return getMaterialsByPanel(panelNo).length;

    } catch (error) {
        console.error('[Nachbau] Error getting material count by panel:', error);
        return 0;
    }
}

/**
 * Get Material Count by Field
 * Get the number of materials in a specific field
 * 
 * @param {string} feldName - The field name
 * @returns {number} - Number of materials in the field
 */
function getMaterialCountByField(feldName) {
    try {
        return getMaterialsByField(feldName).length;

    } catch (error) {
        console.error('[Nachbau] Error getting material count by field:', error);
        return 0;
    }
}

/**
 * Search Materials
 * Search materials by various criteria
 * 
 * @param {string} searchTerm - The search term
 * @param {string} searchField - The field to search in (kmat, description, etc.)
 * @returns {Array} - Array of matching material objects
 */
function searchMaterials(searchTerm, searchField = 'kmat') {
    try {
        const materials = getNachbauMaterials();
        const term = searchTerm.toLowerCase();

        return materials.filter(material => {
            const fieldValue = material[searchField] || '';
            return fieldValue.toString().toLowerCase().includes(term);
        });

    } catch (error) {
        console.error('[Nachbau] Error searching materials:', error);
        return [];
    }
}

// ============================================================================
// NACHBAU DATA EXPORT
// ============================================================================

/**
 * Export Nachbau Materials to CSV
 * Export current nachbau materials to CSV format
 * 
 * @returns {string} - CSV formatted string
 */
function exportNachbauMaterialsToCSV() {
    try {
        const materials = getNachbauMaterials();

        if (materials.length === 0) {
            console.warn('[Nachbau] No materials to export');
            return '';
        }

        // Define CSV headers
        const headers = [
            'Panel',
            'Field Name',
            'Position',
            'Material Number',
            'Description',
            'Quantity',
            'Unit',
            'Typical No'
        ];

        // Build CSV content
        let csv = headers.join(',') + '\n';

        materials.forEach(material => {
            const row = [
                material.panel_no || '',
                material.feld_name || '',
                material.position || '',
                material.parent_kmat || material.kmat || '',
                (material.description || material.kmat_name || '').replace(/,/g, ';'),
                material.qty || '',
                material.unit || '',
                material.typical_no || ''
            ];
            csv += row.join(',') + '\n';
        });

        console.log('[Nachbau] Materials exported to CSV');
        return csv;

    } catch (error) {
        console.error('[Nachbau] Error exporting materials to CSV:', error);
        return '';
    }
}

/**
 * Download Nachbau Materials as CSV
 * Download current nachbau materials as a CSV file
 */
function downloadNachbauMaterialsAsCSV() {
    try {
        const csv = exportNachbauMaterialsToCSV();

        if (!csv) {
            console.warn('[Nachbau] No data to download');
            return;
        }

        // Create blob
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });

        // Create download link
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);

        link.setAttribute('href', url);
        link.setAttribute('download', 'nachbau_' + selectedNachbauFile + '.csv');
        link.style.visibility = 'hidden';

        // Trigger download
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        console.log('[Nachbau] CSV file downloaded');

    } catch (error) {
        console.error('[Nachbau] Error downloading CSV:', error);
    }
}

// ============================================================================
// NACHBAU STATE MANAGEMENT
// ============================================================================

/**
 * Reset Nachbau Module State
 * Reset all nachbau module variables to initial state
 */
function resetNachbauModuleState() {
    try {
        selectedNachbauFile = null;
        nachbauFilesList = [];
        currentNachbauData = {};
        nachbauFileCache = {};

        console.log('[Nachbau] Module state reset');

    } catch (error) {
        console.error('[Nachbau] Error resetting module state:', error);
    }
}

/**
 * Get Nachbau Module State
 * Returns the current state of the nachbau module (for debugging)
 * 
 * @returns {Object} - Object containing current module state
 */
function getNachbauModuleState() {
    return {
        selectedNachbauFile: selectedNachbauFile,
        nachbauFilesCount: nachbauFilesList.length,
        currentProjectNo: currentProjectNo,
        materialsCount: getNachbauMaterials().length,
        summary: getNachbauSummary(),
        isInitialized: isNachbauModuleInitialized
    };
}

// ============================================================================
// ERROR HANDLING
// ============================================================================

/**
 * Show Nachbau Error
 * Display an error message to the user
 * 
 * @param {string} message - The error message to display
 */
function showNachbauError(message) {
    try {
        console.error('[Nachbau Error] ' + message);

        // Show error in UI if error section exists
        if ($('#errorSection').length) {
            $('#errorMessage').text(message);
            $('#errorSection').show();
        } else {
            // Fallback to alert
            alert('Error: ' + message);
        }

    } catch (error) {
        console.error('[Nachbau] Error showing error message:', error);
    }
}

/**
 * Clear Nachbau Error
 * Clear any displayed error messages
 */
function clearNachbauError() {
    try {
        if ($('#errorSection').length) {
            $('#errorSection').hide();
            $('#errorMessage').text('');
        }

    } catch (error) {
        console.error('[Nachbau] Error clearing error message:', error);
    }
}

// ============================================================================
// LOGGING AND DEBUGGING
// ============================================================================

/**
 * Log Nachbau Module Info
 * Log information about the nachbau module state
 */
function logNachbauModuleInfo() {
    const state = getNachbauModuleState();
    console.group('[Nachbau Module Info]');
    console.log('Selected File:', state.selectedNachbauFile);
    console.log('Total Files:', state.nachbauFilesCount);
    console.log('Current Project:', state.currentProjectNo);
    console.log('Total Materials:', state.materialsCount);
    console.log('Summary:', state.summary);
    console.log('Initialized:', state.isInitialized);
    console.groupEnd();
}

/**
 * Log Nachbau Materials
 * Log all materials for debugging
 */
function logNachbauMaterials() {
    const materials = getNachbauMaterials();
    console.group('[Nachbau Materials]');
    console.log('Total Materials:', materials.length);
    console.table(materials);
    console.groupEnd();
}

// ============================================================================
// INITIALIZATION ON PAGE LOAD
// ============================================================================

/**
 * Initialize nachbau module when DOM is ready
 */
$(document).ready(function() {
    initializeNachbauModule();
});