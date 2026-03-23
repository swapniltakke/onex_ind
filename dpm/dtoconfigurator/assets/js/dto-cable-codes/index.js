$(document).ready(async function() {
    $('.menu .item').tab();

    await initializeCableCodesSystem();
    initializeFilters();

    $('#dtoCableCodesPage .loader').hide();
    $('#dtoCableCodesPageContainer').transition('pulse');
});

let vthTable;
let cthTable;
let allVthItems = [];
let allCthItems = [];

async function initializeCableCodesSystem() {
    const cableItems = await getCableItems();

    allVthItems = cableItems.filter(item => item.cable_type_category === 'VTH');
    allCthItems = cableItems.filter(item => item.cable_type_category === 'CTH');

    $('#vthCount').text(allVthItems.length);
    $('#cthCount').text(allCthItems.length);

    await initializeVthDataTable();
    await initializeCthDataTable();
}

async function initializeVthDataTable() {
    const tableId = '#vthTable';

    if ($.fn.DataTable.isDataTable(tableId)) {
        $(tableId).DataTable().destroy();
    }

    if (allVthItems.length === 0) {
        $(tableId).hide();
        return;
    }

    vthTable = $(tableId).DataTable({
        data: allVthItems,
        autoWidth: false,
        paging: true,
        pageLength: 10,
        destroy: true,
        order: [],
        scrollCollapse: true,
        columns: [
            {
                data: 'definition',
                className: 'center aligned wrap-text',
                width: '250px'
            },
            {
                data: 'tr_notes',
                className: 'center aligned wrap-text',
                width: '300px'
            },
            {
                data: 'number_harness',
                className: 'center aligned important-text',
                width: '120px',
                render: (data) => `<a target="_blank" href="/materialviewer/?material=${data}" 
                          data-tooltip="Navigate to Material Viewer" data-position="top center" data-variation="inverted">
                          ${data}
                       </a>`
            },
            {
                data: 'cable_code',
                className: 'center aligned important-text',
                width: '200px',
                render: (data) => `<b style="color: #2185d0;">${data.replace(/[X?]/g, '<span style="color: red;">$&</span>')}</b>`
            },
            {
                data: 'number_drawing',
                className: 'center aligned important-text',
                width: '130px',
                render: (data) => `<a target="_blank" href="/materialviewer/?material=${data}" 
                          data-tooltip="Navigate to Material Viewer" data-position="top center" data-variation="inverted">
                          ${data}
                       </a>`
            },
            { data: 'cable_type', className: 'center aligned', width: '80px' },
            { data: 'cable_cross_section', className: 'center aligned important-text', width: '100px' },
            { data: 'cable_length_type', className: 'center aligned important-text', width: '100px' },
            // VTH Color columns
            { data: null, className: 'center aligned color-cell important-text', width: '45px', render: function(data, type, row) { return renderVthColor(row, 't5l1_a'); }},
            { data: null, className: 'center aligned color-cell important-text', width: '45px', render: function(data, type, row) { return renderVthColor(row, 't5l1_n'); }},
            { data: null, className: 'center aligned color-cell important-text', width: '45px', render: function(data, type, row) { return renderVthColor(row, 't5l2_a1'); }},
            { data: null, className: 'center aligned color-cell important-text', width: '45px', render: function(data, type, row) { return renderVthColor(row, 't5l2_n1'); }},
            { data: null, className: 'center aligned color-cell important-text', width: '45px', render: function(data, type, row) { return renderVthColor(row, 't5l2_a2'); }},
            { data: null, className: 'center aligned color-cell important-text', width: '45px', render: function(data, type, row) { return renderVthColor(row, 't5l2_n2'); }},
            { data: 'place_to_use', className: 'center aligned', width: '80px' },
            { data: 'panel_width', className: 'center aligned important-text', width: '90px' },
            { data: 'core', className: 'center aligned', width: '50px' },
            { data: 'order_no', className: 'center aligned', width: '80px' },
            // Product columns
            { data: null, className: 'center aligned', width: '50px', render: function(data, type, row) { return getProductStatus(row, 1); }},
            { data: null, className: 'center aligned', width: '50px', render: function(data, type, row) { return getProductStatus(row, 2); }},
            { data: null, className: 'center aligned', width: '50px', render: function(data, type, row) { return getProductStatus(row, 3); }},
            { data: null, className: 'center aligned', width: '50px', render: function(data, type, row) { return getProductStatus(row, 4); }},
            { data: null, className: 'center aligned', width: '50px', render: function(data, type, row) { return getProductStatus(row, 5); }},
            { data: null, className: 'center aligned', width: '50px', render: function(data, type, row) { return getProductStatus(row, 7); }},
            { data: null, className: 'center aligned', width: '50px', render: function(data, type, row) { return getProductStatus(row, 6); }},
            {
                data: 'additional_info',
                className: 'center aligned wrap-text',
                width: '250px'
            },
            {
                data: 'created_by',
                className: 'center aligned wrap-text',
                width: '150px'
            },
            {
                data: null,
                width: '80px',
                render: (data, type, row) => `<div class="ui mini buttons"><button class="ui icon button mini blue" onclick="editCableItem(${row.cable_code_id})"><i class="edit icon"></i></button><button class="ui icon button mini red" onclick="deleteCableItem(${row.cable_code_id})"><i class="trash icon"></i></button></div>`,
                className: 'center aligned'
            }
        ],
        initComplete: function() {
            const api = this.api();
            addVthColumnToggleControls();
            setTimeout(() => {
                api.draw(false);
            }, 50);
        }
    });

    // Style the search input for VTH table
    $('#vthTable_wrapper .dt-search input[type="search"]').attr('placeholder', 'Search VTH cables...').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
    $('.dt-input').css('border-radius', '50px');
}

// VTH Column visibility state tracking
let vthColumnVisibility = {
    colors: true,
    products: true
};

function addVthColumnToggleControls() {
    const toggleContainer = `
        <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;display:flex;justify-content:center;">
            <div class="column-toggle-group" style="display: flex; gap: 10px; align-items: center;">
                <div class="ui toggle-card" style="background: white; border-radius: 6px; padding: 8px 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e0e1e2; transition: all 0.3s ease;">
                    <button class="ui mini button" id="vthColorToggle" onclick="toggleVthColumnGroup('colors')" style="border: none; background: none; padding: 4px 8px; border-radius: 4px; transition: all 0.3s ease; cursor: pointer;">
                        <i class="paint brush icon"></i>
                        <span class="toggle-text">Wire Colors</span>
                    </button>
                </div>
                <div class="ui toggle-card" style="background: white; border-radius: 6px; padding: 8px 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e0e1e2; transition: all 0.3s ease;">
                    <button class="ui mini button" id="vthProductToggle" onclick="toggleVthColumnGroup('products')" style="border: none; background: none; padding: 4px 8px; border-radius: 4px; transition: all 0.3s ease; cursor: pointer;">
                        <i class="cube icon"></i>
                        <span class="toggle-text">Products</span>
                    </button>
                </div>
            </div>
        </div>
    `;

    $('#vthTableContainer').prepend(toggleContainer);

    // Initialize button states and add hover effects (reuse existing CSS)
    updateVthToggleButtonStates();
}

function toggleVthColumnGroup(group) {
    let columnIndices = [];

    if (group === 'colors') {
        // VTH Color columns indices (T5L1-A through T5L2-N2)
        columnIndices = [8, 9, 10, 11, 12, 13];
        vthColumnVisibility.colors = !vthColumnVisibility.colors;
    } else if (group === 'products') {
        // Product columns indices (NX, SEC, C, 50kA, M, H, 8BT2)
        columnIndices = [18, 19, 20, 21, 22, 23, 24];
        vthColumnVisibility.products = !vthColumnVisibility.products;
    }

    const isVisible = group === 'colors' ? vthColumnVisibility.colors : vthColumnVisibility.products;

    columnIndices.forEach(index => {
        const column = vthTable.column(index);
        column.visible(isVisible);
    });

    // Update button states with smooth animation
    updateVthToggleButtonStates();

    // Reinitialize tooltips after column visibility change
    setTimeout(() => {
        $('[data-tooltip]').popup();
    }, 100);
}

function updateVthToggleButtonStates() {
    // Update color toggle button
    const colorButton = $('#vthColorToggle');
    const colorCard = colorButton.closest('.toggle-card');

    if (vthColumnVisibility.colors) {
        colorCard.removeClass('inactive').addClass('active');
    } else {
        colorCard.removeClass('active').addClass('inactive');
    }

    // Update product toggle button
    const productButton = $('#vthProductToggle');
    const productCard = productButton.closest('.toggle-card');

    if (vthColumnVisibility.products) {
        productCard.removeClass('inactive').addClass('active');
    } else {
        productCard.removeClass('active').addClass('inactive');
    }
}

async function initializeCthDataTable() {
    const tableId = '#cthTable';

    if ($.fn.DataTable.isDataTable(tableId)) {
        $(tableId).DataTable().destroy();
    }

    if (allCthItems.length === 0) {
        $(tableId).hide();
        return;
    }


    cthTable = $(tableId).DataTable({
        data: allCthItems,
        autoWidth: false,
        paging: true,
        pageLength: 10,
        destroy: true,
        order: [],
        scrollCollapse: true,
        columns: [
            {
                data: 'definition',
                className: 'center aligned wrap-text',
                width: '250px'
            },
            {
                data: 'tr_notes',
                className: 'center aligned wrap-text',
                width: '300px'
            },
            {
                data: 'number_harness',
                className: 'center aligned important-text',
                width: '120px',
                render: (data) => `<a target="_blank" href="/materialviewer/?material=${data}" 
                          data-tooltip="Navigate to Material Viewer" data-position="top center" data-variation="inverted">
                          ${data}
                       </a>`
            },
            {
                data: 'cable_code',
                className: 'center aligned important-text',
                width: '200px',
                render: (data) =>
                    `<b style="color: #2185d0;">${data.replace(/[X?]/g, '<span style="color: red;">$&</span>')}</b>`
            },
            {
                data: 'number_drawing',
                className: 'center aligned important-text',
                width: '130px',
                render: (data) => `<a target="_blank" href="/materialviewer/?material=${data}" 
                          data-tooltip="Navigate to Material Viewer" data-position="top center" data-variation="inverted">
                          ${data}
                       </a>`
            },
            { data: 'cable_type', className: 'center aligned', width: '80px' },
            { data: 'cable_cross_section', className: 'center aligned important-text', width: '100px' },
            { data: 'cable_length_type', className: 'center aligned important-text', width: '100px' },
            // CTH Color columns
            { data: null, className: 'center aligned color-cell important-text', width: '45px', render: function(data, type, row) { return renderCthColor(row, 't1l1_s1'); }},
            { data: null, className: 'center aligned color-cell important-text', width: '45px', render: function(data, type, row) { return renderCthColor(row, 't1l1_s2'); }},
            { data: null, className: 'center aligned color-cell important-text', width: '45px', render: function(data, type, row) { return renderCthColor(row, 't1l1_s3'); }},
            { data: null, className: 'center aligned color-cell important-text', width: '45px', render: function(data, type, row) { return renderCthColor(row, 't1l2_s1'); }},
            { data: null, className: 'center aligned color-cell important-text', width: '45px', render: function(data, type, row) { return renderCthColor(row, 't1l2_s2'); }},
            { data: null, className: 'center aligned color-cell important-text', width: '45px', render: function(data, type, row) { return renderCthColor(row, 't1l2_s3'); }},
            { data: null, className: 'center aligned color-cell important-text', width: '45px', render: function(data, type, row) { return renderCthColor(row, 't1l3_s1'); }},
            { data: null, className: 'center aligned color-cell important-text', width: '45px', render: function(data, type, row) { return renderCthColor(row, 't1l3_s2'); }},
            { data: null, className: 'center aligned color-cell important-text', width: '45px', render: function(data, type, row) { return renderCthColor(row, 't1l3_s3'); }},
            // CTH Specific columns
            { data: 'cable_length_total', className: 'center aligned important-text', width: '100px' },
            { data: 'cable_length_groups', className: 'center aligned important-text', width: '80px' },
            { data: 'total_cable_length', className: 'center aligned important-text', width: '80px' },
            { data: 'ct_in_rear_box', className: 'center aligned important-text', width: '80px' },
            { data: 'place_to_use', className: 'center aligned', width: '80px' },
            { data: 'panel_width', className: 'center aligned important-text', width: '90px' },
            { data: 'core', className: 'center aligned', width: '50px' },
            { data: 'order_no', className: 'center aligned', width: '80px' },
            // Product columns
            { data: null, className: 'center aligned', width: '50px', render: function(data, type, row) { return getProductStatus(row, 1); }},
            { data: null, className: 'center aligned', width: '50px', render: function(data, type, row) { return getProductStatus(row, 2); }},
            { data: null, className: 'center aligned', width: '50px', render: function(data, type, row) { return getProductStatus(row, 3); }},
            { data: null, className: 'center aligned', width: '50px', render: function(data, type, row) { return getProductStatus(row, 4); }},
            { data: null, className: 'center aligned', width: '50px', render: function(data, type, row) { return getProductStatus(row, 5); }},
            { data: null, className: 'center aligned', width: '50px', render: function(data, type, row) { return getProductStatus(row, 7); }},
            { data: null, className: 'center aligned', width: '50px', render: function(data, type, row) { return getProductStatus(row, 6); }},
            {
                data: 'additional_info',
                className: 'center aligned wrap-text',
                width: '250px'
            },
            {
                data: 'created_by',
                className: 'center aligned wrap-text',
                width: '150px'
            },
            {
                data: null,
                width: '80px',
                render: (data, type, row) => `<div class="ui mini buttons"><button class="ui icon button mini blue" onclick="editCableItem(${row.cable_code_id})"><i class="edit icon"></i></button><button class="ui icon button mini red" onclick="deleteCableItem(${row.cable_code_id})"><i class="trash icon"></i></button></div>`,
                className: 'center aligned'
            }
        ],
        initComplete: function() {
            const api = this.api();

            // Add modern column toggle controls
            addCthColumnToggleControls();

            setTimeout(() => {
                api.draw(false);
            }, 50);
        }
    });

    // Style the search input for CTH table
    $('#cthTable_wrapper .dt-search input[type="search"]').attr('placeholder', 'Search CTH cables...').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
    $('.dt-input').css('border-radius', '50px');
}

// Column visibility state tracking
let cthColumnVisibility = {
    colors: true,
    products: true
};

function addCthColumnToggleControls() {
    const toggleContainer = `
        <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;justify-content:center;">
            <div class="column-toggle-group" style="display: flex; gap: 10px; align-items: center;">
                <div class="ui toggle-card" style="background: white; border-radius: 6px; padding: 8px 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e0e1e2; transition: all 0.3s ease;">
                    <button class="ui mini button" id="cthColorToggle" onclick="toggleCthColumnGroup('colors')" style="border: none; background: none; padding: 4px 8px; border-radius: 4px; transition: all 0.3s ease; cursor: pointer;">
                        <i class="paint brush icon"></i>
                        <span class="toggle-text">Wire Colors</span>
                    </button>
                </div>
                <div class="ui toggle-card" style="background: white; border-radius: 6px; padding: 8px 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e0e1e2; transition: all 0.3s ease;">
                    <button class="ui mini button" id="cthProductToggle" onclick="toggleCthColumnGroup('products')" style="border: none; background: none; padding: 4px 8px; border-radius: 4px; transition: all 0.3s ease; cursor: pointer;">
                        <i class="cube icon"></i>
                        <span class="toggle-text">Products</span>
                    </button>
                </div>
            </div>
        </div>
    `;

    $('#cthTableContainer').prepend(toggleContainer);

    // Initialize button states and add hover effects
    updateCthToggleButtonStates();
    addToggleButtonEffects();
}

function addToggleButtonEffects() {
    // Add hover effects with CSS
    const style = document.createElement('style');
    style.textContent = `
        .toggle-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15) !important;
        }
        
        .toggle-card button:hover {
            background: rgba(100, 53, 201, 0.05) !important;
        }
        
        .toggle-card.active {
            border-color: #21ba45 !important;
            background: linear-gradient(135deg, #f0fff4 0%, #ffffff 100%) !important;
        }
        
        .toggle-card.inactive {
            border-color: #db2828 !important;
            background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%) !important;
        }
        
        .toggle-card.active button {
            color: #21ba45 !important;
        }
        
        .toggle-card.inactive button {
            color: #db2828 !important;
        }
        
        .toggle-card button {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }
        
        .toggle-card .toggle-text::after {
            content: '';
            margin-left: 6px;
            font-weight: 400;
            font-size: 0.85em;
        }
        
        .toggle-card.active .toggle-text::after {
            content: '(Shown)';
            color: #21ba45;
        }
        
        .toggle-card.inactive .toggle-text::after {
            content: '(Hidden)';
            color: #db2828;
        }
    `;
    document.head.appendChild(style);
}

function toggleCthColumnGroup(group) {
    let columnIndices = [];

    if (group === 'colors') {
        // Color columns indices (T1L1-S1 through T1L3-S3)
        columnIndices = [8, 9, 10, 11, 12, 13, 14, 15, 16];
        cthColumnVisibility.colors = !cthColumnVisibility.colors;
    } else if (group === 'products') {
        // Product columns indices (NX, SEC, C, 50kA, M, H, 8BT2)
        columnIndices = [25, 26, 27, 28, 29, 30, 31];
        cthColumnVisibility.products = !cthColumnVisibility.products;
    }

    const isVisible = group === 'colors' ? cthColumnVisibility.colors : cthColumnVisibility.products;

    columnIndices.forEach(index => {
        const column = cthTable.column(index);
        column.visible(isVisible);
    });

    // Update button states with smooth animation
    updateCthToggleButtonStates();

    // Reinitialize tooltips after column visibility change
    setTimeout(() => {
        $('[data-tooltip]').popup();
    }, 100);
}

function updateCthToggleButtonStates() {
    // Update color toggle button
    const colorButton = $('#cthColorToggle');
    const colorCard = colorButton.closest('.toggle-card');

    if (cthColumnVisibility.colors) {
        colorCard.removeClass('inactive').addClass('active');
    } else {
        colorCard.removeClass('active').addClass('inactive');
    }

    // Update product toggle button
    const productButton = $('#cthProductToggle');
    const productCard = productButton.closest('.toggle-card');

    if (cthColumnVisibility.products) {
        productCard.removeClass('inactive').addClass('active');
    } else {
        productCard.removeClass('active').addClass('inactive');
    }
}


function initializeFilters() {
    // Initialize VTH product filter
    $('#vthProductFilter').dropdown({
        onChange: function(value, text, $choice) {
            applyVthFilters();
        }
    });

    // Initialize CTH product filter
    $('#cthProductFilter').dropdown({
        onChange: function(value, text, $choice) {
            applyCthFilters();
        }
    });

    // Clear VTH filters button
    $('#clearVthFilters').click(function() {
        $('#vthProductFilter').dropdown('clear');
        applyVthFilters();
    });

    // Clear CTH filters button
    $('#clearCthFilters').click(function() {
        $('#cthProductFilter').dropdown('clear');
        applyCthFilters();
    });
}

function applyVthFilters() {
    const selectedProducts = $('#vthProductFilter').dropdown('get value');
    let filteredData = allVthItems;

    // Apply product filter
    if (selectedProducts && selectedProducts.length > 0) {
        const productArray = Array.isArray(selectedProducts) ? selectedProducts : selectedProducts.split(',');
        filteredData = filteredData.filter(item => {
            const products = item.products || [];
            return productArray.some(selectedProductId =>
                products.some(p => p.product_id == selectedProductId)
            );
        });
    }

    vthTable.clear();
    vthTable.rows.add(filteredData);
    vthTable.draw();

    updateVthFilterCount(filteredData.length, allVthItems.length);
}

function applyCthFilters() {
    const selectedProducts = $('#cthProductFilter').dropdown('get value');
    let filteredData = allCthItems;

    // Apply product filter
    if (selectedProducts && selectedProducts.length > 0) {
        const productArray = Array.isArray(selectedProducts) ? selectedProducts : selectedProducts.split(',');
        filteredData = filteredData.filter(item => {
            const products = item.products || [];
            return productArray.some(selectedProductId =>
                products.some(p => p.product_id == selectedProductId)
            );
        });
    }

    cthTable.clear();
    cthTable.rows.add(filteredData);
    cthTable.draw();

    updateCthFilterCount(filteredData.length, allCthItems.length);
}

function updateVthFilterCount(filteredCount, totalCount) {
    const filterCountElement = $('#vthFilterCount');
    const filterCountText = $('#vthFilterCountText');

    if (filteredCount === totalCount) {
        filterCountElement.hide();
    } else {
        filterCountText.text(filteredCount);
        filterCountElement.show();
    }
}

function updateCthFilterCount(filteredCount, totalCount) {
    const filterCountElement = $('#cthFilterCount');
    const filterCountText = $('#cthFilterCountText');

    if (filteredCount === totalCount) {
        filterCountElement.hide();
    } else {
        filterCountText.text(filteredCount);
        filterCountElement.show();
    }
}

function renderVthColor(row, colorType) {
    const color = row.vth_colors?.[colorType];

    if (color && color.hex) {
        const tooltipText = `${color.ral || 'N/A'} - ${color.tr || 'N/A'}`;
        return `<div style="width:18px; height:18px; background-color:${color.hex}; border:1px solid #000; border-radius:2px; margin:0 auto; cursor:pointer;" 
                     onmouseenter="showTooltip(event, '${tooltipText}')" 
                     onmouseleave="hideTooltip()"></div>`;
    }

    return '-';
}

function renderCthColor(row, colorType) {
    const color = row.cth_colors?.[colorType];

    if (color && color.hex) {
        const tooltipText = `${color.ral || 'N/A'} - ${color.tr || 'N/A'}`;
        return `<div style="width:18px; height:18px; background-color:${color.hex}; border:1px solid #000; border-radius:2px; margin:0 auto; cursor:pointer;" 
                     onmouseenter="showTooltip(event, '${tooltipText}')" 
                     onmouseleave="hideTooltip()"></div>`;
    }
    return '-';
}

function getProductStatus(row, productId) {
    const products = row.products || [];
    const isAssigned = products.some(p => p.product_id == productId);

    if (isAssigned) {
        return '<i class="check circle large icon" style="color: #21ba45;"></i>';
    } else {
        return '<i class="minus circle large icon" style="color: #db2828;"></i>';
    }
}

async function getCableItems() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/CableController.php', {
            params: { action: 'getCableItems'}
        });
        return response.data || [];
    } catch (error) {
        console.error('Error fetching cable items:', error);
        return [];
    }
}

function editCableItem(id) {
    window.open(`/dpm/dtoconfigurator/core/dto-cable-codes/edit-cable-item.php?id=${id}`, '_blank');
}

async function deleteCableItem(id, tableType) {
    showConfirmationDialog({
        title: 'Are you sure?',
        htmlContent: 'Do you want to remove cable code data?',
        confirmButtonText: 'Yes!',
        confirmButtonColor: "green",
        onConfirm: async function () {
            try {
                const formData = new FormData();
                formData.append('action', 'deleteCableItem');
                formData.append('id', id);

                await axios.post('/dpm/dtoconfigurator/api/controllers/CableController.php', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });

                location.reload();
            } catch (error) {
                const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
                showErrorDialog(`<b>${errorMessage}</b>`);
            }
        },
    });
}

function showTooltip(event, text) {
    hideTooltip();

    const tooltip = document.createElement('div');
    tooltip.id = 'custom-tooltip';
    tooltip.innerHTML = text;
    tooltip.style.cssText = `
        position: fixed;
        background:  #fff;
        color: black;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 9999999;
        pointer-events: none;
        white-space: nowrap;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    `;

    document.body.appendChild(tooltip);

    const rect = event.target.getBoundingClientRect();
    tooltip.style.left = (rect.left + rect.width/2 - tooltip.offsetWidth/2) + 'px';
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 8) + 'px';
}

function hideTooltip() {
    const tooltip = document.getElementById('custom-tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}