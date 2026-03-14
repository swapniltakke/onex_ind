<?php
// Ensure $stations is defined before including this file
$sql_checklist = "SELECT * FROM tbl_chk_checklist GROUP BY checklist_name ORDER BY checklist_name ASC";
$checklists = DbManager::fetchPDOQueryData('spectra_db', $sql_checklist)["data"];
$sql_line = "SELECT * FROM tbl_line ORDER BY line_name ASC";
$lines = DbManager::fetchPDOQueryData('spectra_db', $sql_line)["data"];
$sql_product = "SELECT * FROM tbl_chk_product ORDER BY product_name ASC";
$products = DbManager::fetchPDOQueryData('spectra_db', $sql_product)["data"];
$sql_station = "SELECT * FROM tbl_chk_station ORDER BY station_name ASC";
$stations = DbManager::fetchPDOQueryData('spectra_db', $sql_station)["data"];
session_start();
// Use these values in your page logic
// SharedManager::print($_SESSION);
// SharedManager::print($_GET);
$project = (isset($_GET['project']) || $_GET['project'] !== '') ? $_GET['project'] : $_SESSION['selected_project'];
$panel = (isset($_SESSION['selected_panel']) || $_SESSION['selected_panel'] !== '') ? $_SESSION['selected_panel'] : $_GET['panel'];
$checklist = (isset($_SESSION['selected_checklist']) || $_SESSION['selected_checklist'] !== '') ? $_SESSION['selected_checklist'] : $_GET['checklist'];
$line = (isset($_SESSION['selected_line']) || $_SESSION['selected_line'] !== '') ? $_SESSION['selected_line'] : $_GET['line'];
$product = (isset($_SESSION['selected_product']) || $_SESSION['selected_product'] !== '') ? $_SESSION['selected_product'] : $_GET['product'];
$station = (isset($_SESSION['selected_station']) || $_SESSION['selected_station'] !== '') ? $_SESSION['selected_station'] : $_GET['station'];

$isChecklistFormPage = (strpos($_SERVER['PHP_SELF'], 'checklist_form.php') !== false);
?>
<div class="row border-bottom" style="position: relative;">
    <div class="ui fixed menu" style="padding: 21px; color:teal; width: 100%;">
        <div class="ui container" style="position: relative; width: 100%;">
            <div style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); display: flex; align-items: center;">
                <a href="/" style="display: flex; align-items: center; text-decoration: none;">
                    <div style="margin-right: 10px;">
                        <img src="/images/onex_icon.png" width="25" height="36" class="logo-icon">
                    </div>
                    <div class="logo-text">
                        <h5 style="margin: 0; font-size: 18px; line-height: 1.2;">
                            DWC <sup class="badge badge-danger" style="font-size: 0.4em; background-color: #dc3545; color: white; padding: 0.2em 0.3em; border-radius: 0.25rem; vertical-align: super;">OneX</sup>
                        </h5>
                        <p style="margin: 0; text-transform: uppercase; font-size: 10px; color: #6c757d; line-height: 1.2;">Digital Work Center</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="ui inverted segment full-loader" style="display: flex">
    <div class="ui active inverted loader">Loading</div>
</div>

<div class="ui fluid" style='margin-top: 3em;'>
    <div class="ui grid" style="width: 100%; margin: 0;">
        <div class="sixteen wide column" style="padding: 0;">
            <div class="ui segment middle aligned" style="padding-right: 1px;">
                <div class="ui three column grid" style="width: 100.5%;">
                    <div class="column">
                        <div class="ui search">
                            <div class="ui left icon input fluid">
                                <input class="prompt" type="text" data-placeholder-translate="projectSearch" data-project-value="<?= htmlspecialchars($project) ?>" autofocus placeholder="Project Search" id="projectSearchInput">
                                <i class="search icon"></i>
                                <span class="scanner-icon" id="scannerIcon" style="float:right;font-weight:bold;font-size:22px">
                                    <i class="fa fa-qrcode" aria-hidden="true"></i>
                                </span>
                            </div>
                            <div class="ui date-none" id="projectsList"></div>
                        </div>
                    </div>
                    <div class="column">
                        <div id='panelSearch' class='ui fluid selection dropdown'>
                            <i class='dropdown icon'></i>
                            <input type='hidden' name='panelInput' value="<?= htmlspecialchars($panel) ?>">
                            <input type='hidden' name='scanPanelValue' id='scanPanelValue'>
                            <div class='default text' data-translate='panel-select'>Select Panel</div>
                            <div class='menu'>
                                <div class='item' data-value=''>Select Panel</div>
                                <?php if (!$isChecklistFormPage): ?>
                                <div class='item' data-value='-1' data-translate='all-panels'>All Panels</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                        if ($checklist_form == "1") {
                    ?>
                        <div class="column">
                            <div id='checklistSearch' class='ui fluid selection dropdown'>
                                <i class='dropdown icon'></i>
                                <input type='hidden' name='checklistInput' value="<?= htmlspecialchars($checklist) ?>">
                                <div class='default text' data-translate='checklist-select'>Select Checklist</div>
                                <div class='menu'>
                                    <div class='item <?= ($checklist === '') ? 'selected' : '' ?>' data-value=''>Select Checklist</div>
                                    <?php foreach ($checklists as $checklist_data): ?>
                                        <div class='item <?= ($checklist == $checklist_data['checklist_id']) ? 'selected' : '' ?>' 
                                            data-value='<?= $checklist_data['checklist_id'] ?>'>
                                            <?= $checklist_data['checklist_name'] ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="column">
                            <div id='lineSearch' class='ui fluid selection dropdown'>
                                <i class='dropdown icon'></i>
                                <input type='hidden' name='lineInput' value="<?= htmlspecialchars($line) ?>">
                                <div class='default text' data-translate='line-select'>Select Department</div>
                                <div class='menu'>
                                    <div class='item <?= ($line === '') ? 'selected' : '' ?>' data-value=''>Select Department</div>
                                    <?php foreach ($lines as $line_data): ?>
                                        <div class='item <?= ($line == $line_data['id']) ? 'selected' : '' ?>' 
                                            data-value='<?= $line_data['id'] ?>'>
                                            <?= $line_data['line_name'] ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="column">
                            <div id='productSearch' class='ui fluid selection dropdown'>
                                <i class='dropdown icon'></i>
                                <input type='hidden' name='productInput' value="<?= htmlspecialchars($product) ?>">
                                <div class='default text' data-translate='product-select'>Select Product</div>
                                <div class='menu'>
                                    <div class='item <?= ($product === '') ? 'selected' : '' ?>' data-value=''>Select Product</div>
                                    <?php foreach ($products as $product_data): ?>
                                        <div class='item <?= ($product == $product_data['id']) ? 'selected' : '' ?>' 
                                            data-value='<?= $product_data['id'] ?>'>
                                            <?= $product_data['product_name'] ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php
                        }
                    ?>
                    <div class="column">
                        <div id='stationSearch' class='ui fluid selection dropdown'>
                            <i class='dropdown icon'></i>
                            <input type='hidden' name='stationInput' value="<?= htmlspecialchars($station) ?>">
                            <div class='default text' data-translate='station-select'>Select Station</div>
                            <div class='menu'>
                                <div class='item <?= ($station === '') ? 'selected' : '' ?>' data-value=''>Select Station</div>
                                <?php foreach ($stations as $station_data): ?>
                                    <div class='item <?= ($station == $station_data['id']) ? 'selected' : '' ?>' 
                                        data-value='<?= $station_data['id'] ?>'>
                                        <?= $station_data['station_name'] ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // ===== GLOBAL LOADER STATE MANAGEMENT =====
    let activeLoaderRequests = 0;
    const loaderTimeout = {
        show: null,
        hide: null,
        reset: null
    };
    let isChecklistPage = false;
    let checklistFormDebounceTimeout = null;
    let lastScannedPanel = ''; // Track last scanned panel value
    let pendingPanelSelection = null; // Store pending panel selection
    let currentSubItem = ''; // Track current sub item selection

    // Unified loader functions
    function showGlobalLoader(message = 'Loading...') {
        clearTimeout(loaderTimeout.show);
        clearTimeout(loaderTimeout.hide);
        clearTimeout(loaderTimeout.reset);
        
        loaderTimeout.show = setTimeout(() => {
            activeLoaderRequests++;
            
            // Update message in both loader types
            if ($('#loaderMessage').length) {
                $('#loaderMessage').text(message);
            }
            if ($('.full-loader .loader').length) {
                $('.full-loader .loader').html(message);
            }
            
            // Show the appropriate loader
            if ($('#fullPageLoader').length) {
                $('#fullPageLoader').fadeIn(200);
            } else if ($('.full-loader').length) {
                $('.full-loader').css('display', 'flex');
            }
            
            // Safety timeout - force hide after 30 seconds if not hidden
            loaderTimeout.reset = setTimeout(() => {
                console.warn('Loader safety timeout triggered - forcing hide');
                forceHideAllLoaders();
            }, 30000);
        }, 100);
    }

    function hideGlobalLoader() {
        clearTimeout(loaderTimeout.hide);
        
        loaderTimeout.hide = setTimeout(() => {
            activeLoaderRequests--;
            
            // Only hide if all requests are complete
            if (activeLoaderRequests <= 0) {
                forceHideAllLoaders();
            }
        }, 300);
    }

    // Force hide all loaders regardless of state
    function forceHideAllLoaders() {
        activeLoaderRequests = 0;
        clearTimeout(loaderTimeout.reset);
        
        if ($('#fullPageLoader').length) {
            $('#fullPageLoader').fadeOut(200);
        }
        if ($('.full-loader').length) {
            $('.full-loader').css('display', 'none');
        }
    }

    function checkSessionStatus() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/dpm/dwc/api/commonAPI.php?type=check_session',
                method: 'GET',
                success: function(response) {
                    try {
                        const data = JSON.parse(response);
                        if (!data.isLoggedIn) {
                            showSessionExpiredAlert();
                            resolve(false);
                        } else {
                            resolve(true);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        showSessionExpiredAlert();
                        resolve(false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Session check failed:', error);
                    showSessionExpiredAlert();
                    resolve(false);
                }
            });
        });
    }

    function showSessionExpiredAlert() {
        Swal.fire({
            title: 'Session Expired',
            html: `
                <div style="margin: 20px;">
                    <p style="color: #666; font-size: 16px;">
                        Your session has expired or you are not properly logged in.
                    </p>
                    <div style="text-align: left; margin-top: 15px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
                        <p style="margin-bottom: 10px;">Please:</p>
                        <ol style="margin-left: 20px;">
                            <li style="margin-bottom: 5px;">Log out from the current session</li>
                            <li style="margin-bottom: 5px;">Log in again to continue</li>
                        </ol>
                    </div>
                </div>
            `,
            icon: 'warning',
            confirmButtonText: 'Logout',
            confirmButtonColor: '#1ab394',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '/dpm/dwc/logout.php';
            }
        });
        return false;
    }

    function getCurrentPageType() {
        const path = window.location.pathname;
        return path.includes('checklist_form.php') ? 'checklist' : 'other';
    }

    // ===== BARCODE PARSING FUNCTION =====
    function extractSalesOrderNumber(scannedValue) {
        if (!scannedValue || typeof scannedValue !== 'string') {
            return '';
        }

        scannedValue = scannedValue.trim();
        const hasDelimiter = scannedValue.includes('*') || scannedValue.includes('|') || scannedValue.includes(',');

        if (hasDelimiter) {
            let parts = [];
            
            if (scannedValue.includes('*')) {
                parts = scannedValue.split('*');
            } else if (scannedValue.includes('|')) {
                parts = scannedValue.split('|');
            } else if (scannedValue.includes(',')) {
                parts = scannedValue.split(',');
            }

            if (parts.length > 0) {
                const firstPart = parts[0].trim();
                
                if (/^\d+$/.test(firstPart) && firstPart.length >= 8) {
                    return firstPart;
                }
            }
        } else {
            if (/^\d+$/.test(scannedValue) && scannedValue.length >= 8) {
                return scannedValue;
            }
        }

        return '';
    }

    // ===== PANEL NUMBER PARSING FUNCTION =====
    function extractPanelInfo(scannedValue) {
        if (!scannedValue || typeof scannedValue !== 'string') {
            return '';
        }

        scannedValue = scannedValue.trim();
        const hasDelimiter = scannedValue.includes('*') || scannedValue.includes('|') || scannedValue.includes(',');

        if (hasDelimiter) {
            let parts = [];
            
            if (scannedValue.includes('*')) {
                parts = scannedValue.split('*');
            } else if (scannedValue.includes('|')) {
                parts = scannedValue.split('|');
            } else if (scannedValue.includes(',')) {
                parts = scannedValue.split(',');
            }

            if (parts.length >= 2) {
                let panelNumber = parts[1].trim();
                
                if (panelNumber.length > 0) {
                    panelNumber = panelNumber.padStart(6, '0');
                    
                    let code1 = '';
                    let code2 = '';
                    
                    if (parts.length > 5) {
                        code1 = parts[5].trim();
                    }
                    if (parts.length > 6) {
                        code2 = parts[6].trim();
                    }
                    
                    const panelValue = panelNumber + '|' + code2 + '|' + code1;
                    return panelValue;
                }
            }
        }

        return '';
    }

    // ===== ROBUST PANEL SELECTION FOR ALL DEVICES INCLUDING ZEBRA =====
    function selectPanelInDropdown(panelValue) {
        if (!panelValue) {
            console.warn('No panel value provided for selection');
            return false;
        }

        console.log('=== PANEL SELECTION ATTEMPT ===');
        console.log('Panel Value:', panelValue);

        try {
            // Check if dropdown exists
            if (!$('#panelSearch').length) {
                console.warn('Panel dropdown not found');
                return false;
            }

            // Store pending selection
            pendingPanelSelection = panelValue;

            // Method 1: Direct dropdown set selected
            $('#panelSearch').dropdown('set selected', panelValue);
            console.log('Method 1: Set selected called');

            // Method 2: Set the hidden input value
            $('input[name="panelInput"]').val(panelValue);
            console.log('Method 2: Hidden input set to:', panelValue);

            // Method 3: Trigger change event
            $('#panelSearch').trigger('change');
            console.log('Method 3: Change event triggered');

            // Method 4: Update the display text
            const displayText = $('#panelSearch').find('.text');
            if (displayText.length > 0) {
                // Find the matching item in the menu
                const menuItem = $('#panelSearch').find('.menu .item[data-value="' + panelValue + '"]');
                if (menuItem.length > 0) {
                    const itemText = menuItem.text();
                    displayText.text(itemText);
                    console.log('Method 4: Display text updated to:', itemText);
                } else {
                    console.warn('Menu item not found for value:', panelValue);
                }
            }

            // Method 5: Force refresh dropdown
            $('#panelSearch').dropdown('refresh');
            console.log('Method 5: Dropdown refreshed');

            console.log('=== PANEL SELECTION COMPLETED ===');
            return true;

        } catch (error) {
            console.error('Error in selectPanelInDropdown:', error);
            return false;
        }
    }

    // ===== PROCESS BARCODE SCAN =====
    function processBarcodeData(scannedValue) {
        console.log('Processing barcode:', scannedValue);

        const extractedSalesOrder = extractSalesOrderNumber(scannedValue);
        console.log('Extracted Sales Order:', extractedSalesOrder);

        if (!extractedSalesOrder) {
            console.warn('Could not extract sales order from barcode');
            return false;
        }

        // Set the sales order in the input
        $('#projectSearchInput').val(extractedSalesOrder);

        // Extract panel information
        const extractedPanel = extractPanelInfo(scannedValue);
        console.log('Extracted Panel:', extractedPanel);

        if (extractedPanel) {
            // Store in hidden field
            $('#scanPanelValue').val(extractedPanel);
            lastScannedPanel = extractedPanel;

            // Attempt to select panel immediately
            selectPanelInDropdown(extractedPanel);

            // Save to session
            saveSelectedValues('project', extractedPanel);
        } else {
            saveSelectedValues('project');
        }

        // Trigger search
        $('.ui.search').search('query', extractedSalesOrder);

        return true;
    }
    
    $(document).ready(async function() {
        // Determine if this is checklist page
        isChecklistPage = getCurrentPageType() === 'checklist';

        // Hide loaders when page is fully loaded
        $(window).on('load', function() {
            setTimeout(forceHideAllLoaders, 500);
        });
        
        // Force hide loaders if page has been loaded for a while
        setTimeout(forceHideAllLoaders, 3000);

        // Check session status first
        const isSessionValid = await checkSessionStatus();
        if (!isSessionValid) return false;

        let scanTimeout;
        let isScanning = false;
        let lastInputTime = 0;
        let initialLoadComplete = false;
        
        // Initialize dropdowns with error handling
        function initializeDropdowns() {
            try {
                // Initialize panel search
                if ($('#panelSearch').length) {
                    $('#panelSearch').dropdown({
                        onChange: function(value) {
                            console.log('Panel dropdown changed to:', value);
                            if (value && initialLoadComplete) {
                                const fileName = window.location.pathname.split('/').pop();
                                saveSelectedValues('panel');
                                
                                // Handle page-specific functionality
                                if (fileName === 'checklist_form.php') {
                                    handleChecklistFormPanel(value);
                                    triggerChecklistFormLoad();
                                } else if (fileName === 'missing_material_entry.php' || 
                                        fileName === 'show_missing_material.php') {
                                    handleMissingMaterialEntry(value);
                                }
                            }
                        }
                    });
                }

                // Initialize station search
                if ($('#stationSearch').length) {
                    $('#stationSearch').dropdown({
                        onChange: function(value) {
                            if (value && initialLoadComplete) {
                                saveSelectedValues('station');
                                
                                if (window.location.pathname.includes('checklist_form.php')) {
                                    triggerChecklistFormLoad();
                                }
                            }
                        }
                    });
                }

                // Initialize checklist-specific dropdowns
                if (isChecklistPage) {
                    if ($('#checklistSearch').length) {
                        $('#checklistSearch').dropdown({
                            onChange: function(value) {
                                if (value && initialLoadComplete) {
                                    saveSelectedValues('checklist');
                                    triggerChecklistFormLoad();
                                }
                            }
                        });
                    }

                    if ($('#lineSearch').length) {
                        $('#lineSearch').dropdown({
                            onChange: function(value) {
                                if (value && initialLoadComplete) {
                                    saveSelectedValues('line');
                                    triggerChecklistFormLoad();
                                }
                            }
                        });
                    }

                    if ($('#productSearch').length) {
                        $('#productSearch').dropdown({
                            onChange: function(value) {
                                if (value && initialLoadComplete) {
                                    saveSelectedValues('product');
                                    // Reset sub item when product changes
                                    currentSubItem = '';
                                    triggerChecklistFormLoad();
                                }
                            }
                        });
                    }
                }
            } catch (error) {
                console.error('Error initializing dropdowns:', error);
            }
        }

        // Trigger checklist form load with debouncing
        function triggerChecklistFormLoad() {
            clearTimeout(checklistFormDebounceTimeout);
            showGlobalLoader('Loading checklist...');
            
            checklistFormDebounceTimeout = setTimeout(() => {
                const checklistId = $('#checklistSearch').dropdown('get value');
                const lineId = $('#lineSearch').dropdown('get value');
                const productId = $('#productSearch').dropdown('get value');
                const stationId = $('#stationSearch').dropdown('get value');
                const salesOrderNo = $('#projectSearchInput').val();
                const panelNumber = ($('#panelSearch').dropdown('get value') || '').match(/^[^|]*/)[0];
                
                if (!salesOrderNo || salesOrderNo.trim().length !== 10 || isNaN(salesOrderNo) || !checklistId) {
                    hideGlobalLoader();
                    return;
                }
                
                if (typeof listChecklistForm === 'function') {
                    listChecklistForm(checklistId, lineId, productId, stationId, salesOrderNo, panelNumber, currentSubItem);
                } else {
                    hideGlobalLoader();
                }
            }, 800);
        }

        // Set initial project value
        const projectValue = $('#projectSearchInput').data('project-value');
        if (projectValue) {
            $('#projectSearchInput').val(projectValue);
        }

        // Initialize dropdowns
        initializeDropdowns();

        // Load saved values
        loadSelectedValues();

        // Initialize after delay
        setTimeout(() => {
            initialLoadComplete = true;
            
            // If there's a pending panel selection, apply it now
            if (pendingPanelSelection) {
                console.log('Applying pending panel selection:', pendingPanelSelection);
                selectPanelInDropdown(pendingPanelSelection);
            }
            
            if (isChecklistPage) {
                triggerChecklistFormLoad();
            }
        }, 1000);

        // Project input change handler
        $('#projectSearchInput').on('change', function() {
            if (initialLoadComplete) {
                const value = $(this).val().trim();
                if (value) {
                    saveSelectedValues('project');
                    
                    if (isChecklistPage) {
                        setTimeout(() => {
                            triggerChecklistFormLoad();
                        }, 500);
                    }
                }
            }
        });

        // ===== ENHANCED BARCODE SCANNER INPUT HANDLER =====
        $('#projectSearchInput').on('input', function(e) {
            const input = $(this);
            const currentTime = new Date().getTime();
            
            clearTimeout(scanTimeout);
            
            // Detect rapid input (scanner behavior)
            if (currentTime - lastInputTime < 100) {
                isScanning = true;
            }
            
            lastInputTime = currentTime;
            
            scanTimeout = setTimeout(() => {
                const scannedValue = input.val();
                
                if (isScanning && scannedValue) {
                    console.log('=== BARCODE SCAN DETECTED ===');
                    console.log('Raw scanned value:', scannedValue);
                    
                    processBarcodeData(scannedValue);
                    
                    isScanning = false;
                }
            }, 500);
        });

        // ===== KEYBOARD ENTER HANDLER FOR ZEBRA TABLETS =====
        $('#projectSearchInput').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                const scannedValue = $(this).val();
                
                if (scannedValue) {
                    console.log('=== ENTER KEY PRESSED - PROCESSING BARCODE ===');
                    console.log('Scanned value:', scannedValue);
                    
                    processBarcodeData(scannedValue);
                }
            }
        });

        // ===== BLUR HANDLER FOR ZEBRA TABLETS =====
        $('#projectSearchInput').on('blur', function() {
            const scannedValue = $(this).val();
            
            if (scannedValue && lastScannedPanel) {
                console.log('=== BLUR EVENT - RE-APPLYING PANEL SELECTION ===');
                setTimeout(() => {
                    selectPanelInDropdown(lastScannedPanel);
                }, 200);
            }
        });

        // Initialize search functionality
        $('.ui.search').search({
            apiSettings: {
                url: '/dpm/dwc/materialsearch/api/search.php',
                method: 'GET',
                beforeSend: function(settings) {
                    const fullPath = window.location.pathname;
                    const fileName = fullPath.split('/').pop().replace('.php', '');
                    settings.data = {
                        file: fileName,
                        project: $('#projectSearchInput').val(),
                        scannedPanelValue: $('#scanPanelValue').val()
                    };
                    return settings;
                }
            },
            fields: {
                results: 'items',
                title: 'name',
                url: 'html_url'
            },
            minCharacters: 4,
            searchDelay: 500,
            searchOnFocus: false,
            automaticFocusSearch: false,
            onSelect: function(result, response) {
                if (result && result.project_no) {
                    $('#projectSearchInput').val(result.project_no);
                    
                    // Re-apply panel selection after search
                    if (lastScannedPanel) {
                        console.log('=== SEARCH RESULT SELECTED - RE-APPLYING PANEL ===');
                        setTimeout(() => {
                            selectPanelInDropdown(lastScannedPanel);
                        }, 300);
                    }
                    
                    if ($('#scanPanelValue').val() != "") {
                        saveSelectedValues('project', $('#scanPanelValue').val());
                    } else {
                        saveSelectedValues('project');   
                    }
                    return false;
                }
            }
        });
    });

    function processScannedValue(value, panel_value) {
        const extractedSalesOrder = extractSalesOrderNumber(value);
        
        if (extractedSalesOrder) {
            $('#projectSearchInput').val(extractedSalesOrder);
            
            if (panel_value) {
                $('#scanPanelValue').val(panel_value);
                lastScannedPanel = panel_value;
                selectPanelInDropdown(panel_value);
            } else {
                const extractedPanel = extractPanelInfo(value);
                if (extractedPanel) {
                    $('#scanPanelValue').val(extractedPanel);
                    lastScannedPanel = extractedPanel;
                    selectPanelInDropdown(extractedPanel);
                }
            }
            
            $('.ui.search').search('query', extractedSalesOrder);
            saveSelectedValues();
        }
    }

    function saveSelectedValues(type = 'all', panel_value = "") {
        try {
            let data = {
                project: $('#projectSearchInput').val() || ''
            };

            if (type === 'project') {
                if (panel_value != "") {
                    data.panel = panel_value;
                } else {
                    data.clear = 1;
                }
            } else {
                const panelValue = $('#panelSearch').dropdown('get value');
                if (panelValue) {
                    data.panel = panelValue;
                }

                if (isChecklistPage) {
                    const checklistValue = $('#checklistSearch').dropdown('get value');
                    if (checklistValue) {
                        data.checklist = checklistValue;
                    }

                    const lineValue = $('#lineSearch').dropdown('get value');
                    if (lineValue) {
                        data.line = lineValue;
                    }

                    const productValue = $('#productSearch').dropdown('get value');
                    if (productValue) {
                        data.product = productValue;
                    }
                }

                const stationValue = $('#stationSearch').dropdown('get value');
                if (stationValue) {
                    data.station = stationValue;
                }
            }

            $.ajax({
                url: '/dpm/dwc/set_project_session.php',
                method: 'POST',
                data: data,
                success: function(response) {
                    console.log('Session updated successfully');
                },
                error: function(xhr, status, error) {
                    console.error('Failed to update session:', error);
                }
            });
        } catch (error) {
            console.error('Error in saveSelectedValues:', error);
        }
    }

    function loadSelectedValues() {
        $.ajax({
            url: './get_project_session.php',
            method: 'GET',
            success: function(response) {
                try {
                    const data = JSON.parse(response);
                    
                    if (data.project) {
                        $('#projectSearchInput').val(data.project);
                    }
                    if (data.panel) {
                        $('#panelSearch').dropdown('set selected', data.panel);
                        lastScannedPanel = data.panel;
                    }
                    if (data.checklist) {
                        $('#checklistSearch').dropdown('set selected', data.checklist);
                    }
                    if (data.line) {
                        $('#lineSearch').dropdown('set selected', data.line);
                    }
                    if (data.product) {
                        $('#productSearch').dropdown('set selected', data.product);
                    }
                    if (data.station) {
                        $('#stationSearch').dropdown('set selected', data.station);
                    }
                } catch (error) {
                    console.error('Error parsing session data:', error);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading session values:', error);
            }
        });
    }

    // Page-specific handlers
    function handleMissingMaterialEntry(value) {
        $('#slMaterialNos').select2({
            dropdownParent: $('#insertModal .modal-body #divSlMaterialNos'),
            width: '100%',
            tags: true,
            placeholder: 'Material Number',
        });

        const projectNo = $('#projectSearchInput').val();
        const panelNo = (value || '').match(/^[^|]*/)[0];
        
        if (!projectNo || !panelNo) return;

        getOrderDetail(projectNo)
            .then((prom) => {
                if (prom === null) throw "Project is not found";
                return getProjectProductDetails(projectNo);
            })
            .then(() => getOrderPanels(projectNo))
            .then(() => getProjectNotes(projectNo))
            .then(() => showProjectDetailsDiv())
            .then(() => clearInsertModal(panelNo))
            .then(() => {
                if (window.location.pathname.includes("missing_material_entry.php")) {
                    return openInsertModal(panelNo);
                }
                return Promise.resolve();
            });
    }

    function handleChecklistFormPanel(value) {
        const dropdownValue = value || '';
        const panelType = dropdownValue.split('|')[0] || '';
        const panelName = dropdownValue.split('|')[1] || '';
        const typicalName = dropdownValue.split('|')[2] || '';
        
        $('#panelTypeDisplay').text(panelType);
        if (panelName !== '' && typicalName !== '') {
            $('#locationTypicalValue').text(panelName + '/' + typicalName);
        }
    }
</script>