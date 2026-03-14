// /dpm/dto_configurator/js/projects/detail/tabs/kuko-matrix.js

/**
 * PHASE 7: Display Kuko Matrix Data
 */
function displayKukoMatrix(data) {
    try {
        let html = `
            <div class="ui segment">
                <h3>Kuko Matrix - <strong>${data.nachbauFile}</strong></h3>
                
                <!-- Statistics -->
                <div class="ui statistics">
                    <div class="statistic">
                        <div class="value">
                            <i class="database icon"></i><span>${data.summary.totalMaterials}</span>
                        </div>
                        <div class="label">Total Materials</div>
                    </div>
                    <div class="statistic">
                        <div class="value">
                            <i class="th icon"></i><span>${data.summary.totalPanels}</span>
                        </div>
                        <div class="label">Panels</div>
                    </div>
                    <div class="statistic">
                        <div class="value">
                            <i class="sitemap icon"></i><span>${data.summary.totalFields}</span>
                        </div>
                        <div class="label">Fields</div>
                    </div>
                </div>
            </div>

            <!-- Panel Filter Tabs -->
            <div class="ui segment">
                <label style="font-weight: bold; margin-bottom: 10px; display: block;">Filter by Panel</label>
                <div id="kukoMatrixPanelTabs" class="panel-tabs"></div>
            </div>

            <!-- Kuko Matrix Table -->
            <div class="ui segment">
                <h4>Materials by Field</h4>
                <div style="overflow-x: auto;">
                    <table class="ui celled table">
                        <thead>
                            <tr>
                                <th>Panel</th>
                                <th>Field Name</th>
                                <th>Position</th>
                                <th>Material Number</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Typical No</th>
                            </tr>
                        </thead>
                        <tbody id="kukoMatrixTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        $('#kukoMatrixContainer').html(html);

        // Create panel filter tabs
        createKukoMatrixPanelTabs(data.materials);

        // Display all materials by default
        displayKukoMatrixTable(data.materials);

    } catch (error) {
        console.error('Error displaying kuko matrix:', error);
        $('#kukoMatrixContainer').html(`
            <div class="ui message error">
                <p>Error displaying kuko matrix data</p>
            </div>
        `);
    }
}

/**
 * Create Panel Filter Tabs
 */
function createKukoMatrixPanelTabs(materials) {
    // Get unique panel numbers
    const panels = [...new Set(materials.map(m => m.panel_no))].filter(p => p).sort();

    const container = $('#kukoMatrixPanelTabs');
    container.empty();

    // Add "All" button
    const allBtn = `<button class="panel-tab-button active" data-panel="all">All Panels (${materials.length})</button>`;
    container.append(allBtn);

    // Add individual panel buttons
    panels.forEach(panel => {
        const count = materials.filter(m => m.panel_no === panel).length;
        const btn = `<button class="panel-tab-button" data-panel="${panel}">Panel ${panel} (${count})</button>`;
        container.append(btn);
    });

    // Add click handlers
    container.off('click');
    container.on('click', '.panel-tab-button', function() {
        const selectedPanel = $(this).data('panel');
        
        // Update active state
        container.find('.panel-tab-button').removeClass('active');
        $(this).addClass('active');

        // Filter materials
        let filtered = window.kukoMatrixData.materials;
        if (selectedPanel !== 'all') {
            filtered = window.kukoMatrixData.materials.filter(m => m.panel_no === selectedPanel);
        }

        displayKukoMatrixTable(filtered);
    });
}

/**
 * Display Kuko Matrix Table
 */
function displayKukoMatrixTable(materials) {
    // Group by panel and field
    const grouped = {};

    materials.forEach(material => {
        const panelNo = material.panel_no || 'Unknown';
        const feldName = material.feld_name || 'Unknown';

        if (!grouped[panelNo]) {
            grouped[panelNo] = {};
        }

        if (!grouped[panelNo][feldName]) {
            grouped[panelNo][feldName] = [];
        }

        grouped[panelNo][feldName].push(material);
    });

    const tbody = $('#kukoMatrixTableBody');
    tbody.empty();

    // Sort panels
    const sortedPanels = Object.keys(grouped).sort();

    sortedPanels.forEach(panelNo => {
        const fields = grouped[panelNo];
        const sortedFields = Object.keys(fields).sort();

        sortedFields.forEach(feldName => {
            const fieldMaterials = fields[feldName];

            fieldMaterials.forEach((material, index) => {
                const row = `
                    <tr>
                        <td>${index === 0 ? panelNo : ''}</td>
                        <td>${index === 0 ? feldName : ''}</td>
                        <td>${material.position || '-'}</td>
                        <td>${material.parent_kmat || material.kmat || '-'}</td>
                        <td>${material.description || material.kmat_name || '-'}</td>
                        <td>${material.qty || '-'}</td>
                        <td>${material.unit || '-'}</td>
                        <td>${material.typical_no || '-'}</td>
                    </tr>
                `;
                tbody.append(row);
            });
        });
    });
}