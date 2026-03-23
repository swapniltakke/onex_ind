let parsedProjectLots = [];

$(document).on('click', '#parseBtn', async function() {
    const inputText = $('#projectLotInput').val().trim();

    if (!inputText) {
        fireToastr('warning', 'Please paste project-lot numbers');
        return;
    }

    const lines = inputText.split('\n').map(line => line.trim()).filter(line => line.length > 0);

    if (lines.length > 10) {
        showErrorDialog(`
            <div class="ui center aligned container">
                <h3>Too Many Entries</h3>
                <p>You can search maximum <strong>10 projects</strong> at once.</p>
                <p>You entered <strong>${lines.length}</strong> entries.</p>
                <p>Please reduce the number of entries and try again.</p>
            </div>
        `);
        return;
    }

    const projectLots = [];
    const invalidLines = [];

    lines.forEach((line, index) => {
        const parts = line.split('-');
        if (parts.length === 2) {
            const projectNo = parts[0].trim();
            const lotNo = parts[1].trim();

            if (projectNo && lotNo && !isNaN(lotNo)) {
                projectLots.push({
                    project: projectNo,
                    lot: parseInt(lotNo),
                    originalLine: line,
                    lineNumber: index + 1
                });
            } else {
                invalidLines.push({ line, lineNumber: index + 1 });
            }
        } else {
            invalidLines.push({ line, lineNumber: index + 1 });
        }
    });

    if (invalidLines.length > 0) {
        let errorMsg = '<div class="ui center aligned container">';
        errorMsg += '<i class="exclamation triangle icon massive orange"></i>';
        errorMsg += '<h3>Invalid Format Found</h3>';
        errorMsg += '<p>The following lines have invalid format:</p>';
        errorMsg += '<div class="ui left aligned segment" style="margin: 1rem auto; max-width: 400px;">';

        invalidLines.forEach(item => {
            errorMsg += `<div><strong>Line ${item.lineNumber}:</strong> ${item.line}</div>`;
        });

        errorMsg += '</div>';
        errorMsg += '<p>Please use format: <strong>ProjectNumber-LotNumber</strong></p>';
        errorMsg += '</div>';

        showErrorDialog(errorMsg);
        return;
    }

    if (projectLots.length === 0) {
        fireToastr('warning', 'No valid project-lot combinations found');
        return;
    }

    const uniqueProjectLots = removeDuplicates(projectLots);

    if (uniqueProjectLots.length > 10) {
        showErrorDialog(`
            <div class="ui center aligned container">
                <h3>Too Many Unique Entries</h3>
                <p>After removing duplicates, you still have <strong>${uniqueProjectLots.length}</strong> unique combinations.</p>
                <p>Maximum allowed is <strong>10</strong>.</p>
                <p>Please reduce the number of entries and try again.</p>
            </div>
        `);
        return;
    }

    if (projectLots.length > uniqueProjectLots.length) {
        const duplicatesRemoved = projectLots.length - uniqueProjectLots.length;
        fireToastr('info', `Removed ${duplicatesRemoved} duplicate entries. Processing ${uniqueProjectLots.length} unique combinations.`);
    }

    await validateProjectsWithBackend(uniqueProjectLots);
});

function removeDuplicates(projectLots) {
    const seen = new Set();
    return projectLots.filter(item => {
        const key = `${item.project}-${item.lot}`;
        if (seen.has(key)) {
            return false;
        }
        seen.add(key);
        return true;
    });
}

async function validateProjectsWithBackend(projectLots) {
    try {
        $('#parseBtn').addClass('loading');

        // Validate each project-lot combination individually
        const validationPromises = projectLots.map(async (item) => {
            try {
                const response = await axios.get('/dpm/dtoconfigurator/api/controllers/DTOAssemblyHoursController.php', {
                    params: {
                        action: 'validateProjectAndLot',
                        projectNo: item.project,
                        lotNo: item.lot
                    }
                });

                // Success case - your backend returns project data
                $('#calculateBtn').prop('disabled', false);
                return {
                    ...item,
                    isValid: true,
                    projectName: response.data[0].project_name || 'Unknown',
                    status: 'Valid',
                    data: response.data[0]
                };
            } catch (error) {
                let status = '';
                let projectName = 'Invalid Project/Lot';

                if (error.response && error.response.status === 400)
                    status = error.response?.data?.message ?? 'Invalid'
                else
                    status = 'Connection Error';

                return {
                    ...item,
                    isValid: false,
                    projectName: projectName,
                    status: status,
                    error: error.response?.data || error.message
                };
            }
        });

        parsedProjectLots = await Promise.all(validationPromises);
        displayParsedResults();

    } catch (error) {
        console.error('Error validating project-lot combinations:', error);
        fireToastr('error', 'Error validating project-lot combinations with backend');
    } finally {
        $('#parseBtn').removeClass('loading');
    }
}

function displayParsedResults() {
    const tableBody = $('#parsedResultsTable tbody');
    tableBody.empty();

    let validCount = 0;

    parsedProjectLots.forEach(item => {
        const statusClass = item.isValid ? 'positive' : 'negative';
        const statusIcon = item.isValid ? 'checkmark' : 'remove';

        if (item.isValid) validCount++;

        const row = `
            <tr class="${statusClass}">
                <td><strong>${item.project}</strong></td>
                <td>${item.lot}</td>
                <td>${item.projectName}</td>
                <td>
                    <i class="${statusIcon} icon"></i>
                    ${item.status}
                </td>
            </tr>
        `;
        tableBody.append(row);
    });

    $('#parsedResultsContainer').show();

    $('#calculateBtn').prop('disabled', validCount === 0);

    const invalidCount = parsedProjectLots.length - validCount;
    if (invalidCount > 0) {
        fireToastr('warning', `Found ${validCount} valid and ${invalidCount} invalid project-lot combinations`);
    } else {
        fireToastr('success', `Successfully parsed ${validCount} valid project-lot combinations`);
    }
}

$(document).on('click', '#calculateBtn', async function() {
    $('#calculateBtn').addClass('loading');
    $('#calculateBtn').prop('disabled', true);

    const projectLotData = [];

    // Get valid project-lot combinations from parsed data
    const validProjectLots = parsedProjectLots.filter(item => item.isValid);

    if (validProjectLots.length === 0) {
        fireToastr('warning', 'No valid project-lot combinations to calculate');
        return;
    }

    // Prepare data for backend
    validProjectLots.forEach(item => {
        projectLotData.push({
            project: item.project,
            lot: item.lot
        });
    });

    try {
        $('#calculationResultsContainer').show();
        $('#calculationResultsContainer .loader').show();

        // Send to backend for station-based calculation
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/DTOAssemblyHoursController.php', {
            params: {
                action: 'calculateStationBasedAssemblyHours',
                projectLotEntries: projectLotData
            }
        });

        const results = response.data;
        displayCalculationResults(results);

        $('#generateExcelBtn').prop('disabled', false);

    } catch (error) {
        console.error('Error calculating station-based hours:', error);
        const errorMessage = error.response?.data?.message || 'Error calculating station-based assembly hours';
        fireToastr('error', errorMessage);
    } finally {
        $('#calculationResultsContainer .loader').hide();
        $('#calculateBtn').removeClass('loading');
    }
});

function displayCalculationResults(results) {
    if (!results || !results.data || results.data.length === 0) {
        $('#calculationResultsContent').html('<div class="ui message">No calculation results received</div>');
        return;
    }

    const groupedData = {};

    results.data.forEach(dto => {
        const key = `${dto.project_number}-${dto.lot_number}`;
        if (!groupedData[key]) {
            groupedData[key] = {
                project_number: dto.project_number,
                lot_number: dto.lot_number,
                dtos: []
            };
        }
        groupedData[key].dtos.push(dto);
    });

    // Create accordion structure with separate tables
    createProjectLotAccordions(groupedData);

    // STORE DATA FOR EXCEL EXPORT
    window.excelExportData = groupedData;

    // Show Excel export button
    $('#generateExcelBtn').show();
}

function createProjectLotAccordions(groupedData) {
    let accordionHtml = '<div class="ui fluid styled accordion" id="projectLotAccordion">';

    let accordionIndex = 0;
    Object.keys(groupedData).forEach(key => {
        const projectLotData = groupedData[key];
        const isFirstAccordion = accordionIndex === 0;

        accordionHtml += `
            <div class="title ${isFirstAccordion ? 'active' : ''}">
                <i class="dropdown icon"></i>
                <strong>Project: ${projectLotData.project_number} - Lot: ${projectLotData.lot_number}</strong>
                <span class="ui small label" style="margin-left: 10px;float:right;">${projectLotData.dtos.length} DTOs</span>
            </div>
            <div class="content ${isFirstAccordion ? 'active' : ''}">
                <div id="stationBasedContainer_${accordionIndex}" class="ui segment">
                    <div class="ui active dimmer" style="display:none;">
                        <div class="ui large text loader">Loading Station Based Assembly Hours Matrix...</div>
                    </div>
                    <div id="stationBasedTableContainer_${accordionIndex}" class="table-responsive station-based-table-container" style="overflow-x:auto;">
                        <table id="stationBasedTable_${accordionIndex}" class="ui striped hover compact celled table stackable padded station-based-table" style="width:100%">
                            <thead></thead>
                            <tfoot></tfoot>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
        accordionIndex++;
    });

    accordionHtml += '</div>';

    $('#calculationResultsContent').html(accordionHtml);

    $('#projectLotAccordion').accordion({
        exclusive: false,
        animateChildren: false
    });

    accordionIndex = 0;
    Object.keys(groupedData).forEach(key => {
        const projectLotData = groupedData[key];
        createStationBasedTable(projectLotData.dtos, accordionIndex, projectLotData.project_number, projectLotData.lot_number);
        accordionIndex++;
    });

    fireToastr('success', `Created ${Object.keys(groupedData).length} project-lot tables successfully`);
}

function createStationBasedTable(dtoData, tableIndex, projectNumber, lotNumber) {
    try {
        const workCenterSet = new Set();
        dtoData.forEach(dto => {
            Object.keys(dto).forEach(key => {
                if (key.startsWith('wc_')) {
                    const workCenter = key.replace('wc_', '');
                    workCenterSet.add(workCenter);
                }
            });
        });

        // Get work center details
        const workCenters = Array.from(workCenterSet).map(wcKey => {
            for (let dto of dtoData) {
                const wcData = dto[`wc_${wcKey}`];
                if (wcData && wcData.changes && wcData.changes.length > 0) {
                    const change = wcData.changes[0];
                    return {
                        workCenter: change.work_center,
                        workContent: change.work_content,
                        columnHeader: `${change.work_center} - ${change.work_content}`
                    };
                }
            }
            // Fallback if no changes found
            return {
                workCenter: wcKey,
                workContent: 'Unknown',
                columnHeader: `${wcKey} - Unknown`
            };
        });

        const tableId = `stationBasedTable_${tableIndex}`;

        if ($.fn.DataTable.isDataTable(`#${tableId}`)) {
            $(`#${tableId}`).DataTable().destroy();
        }

        // Build header row
        let headerRow = '<tr><th class="center aligned">DTO Number</th>';
        workCenters.forEach(workCenter => {
            headerRow += `<th class="center aligned">
                            <span style="font-size:14px!important;">${workCenter.workCenter}</span> 
                            <br> 
                            <span>${workCenter.workContent}</span>
                          </th>`;
        });
        headerRow += '<th class="center aligned">Total</th></tr>';
        $(`#${tableId} thead`).html(headerRow);

        // Define columns
        const columns = [
            {
                data: 'dto_number',
                className: 'center aligned',
                render: function(data) {
                    return `<a href="#" class="dto-link" data-dto-number="${data}" style="font-weight:bold;">${data}</a>`
                }
            }
        ];

        // Add work center columns dynamically
        workCenters.forEach(workCenter => {
            columns.push({
                data: `wc_${workCenter.workCenter}`,
                className: 'center aligned',
                render: function(data, type, row) {
                    if (type === 'display') {
                        if (!data || (data.addedHours === 0 && data.deletedHours === 0)) return '-';

                        let html = '';
                        if (data.addedHours > 0) {
                            html += `<span style="color: green; font-weight: bold;">+${data.addedHours}${data.addedUnit}</span>`;
                        }
                        if (data.deletedHours > 0) {
                            if (html) html += '<br>';
                            html += `<span style="color: red; font-weight: bold;">-${data.deletedHours}${data.deletedUnit}</span>`;
                        }
                        return html || '-';
                    }
                    return data || 0;
                }
            });
        });

        // Add Total column
        columns.push({
            data: 'rowTotal',
            className: 'center aligned',
            render: function(data, type, row) {
                if (type === 'display') {
                    if (!data || data.totalHours === 0) return '-';

                    const color = data.isNegative ? 'red' : 'green';
                    const sign = data.isNegative ? '-' : '+';
                    return `<span style="color: ${color}; font-weight: bold;">${sign}${data.totalHours}${data.unit}</span>`;
                }
                return data || 0;
            }
        });

        // Prepare data for DataTable
        const tableData = dtoData.map(dto => {
            const rowData = {
                dto_number: dto.dto_number,
                project_number: dto.project_number,
                lot_number: dto.lot_number
            };

            // Add work center data
            workCenters.forEach(workCenter => {
                const wcKey = `wc_${workCenter.workCenter}`;
                rowData[wcKey] = dto[wcKey] || {
                    addedHours: 0,
                    addedUnit: 'h',
                    deletedHours: 0,
                    deletedUnit: 'h'
                };
            });

            // Calculate row total
            rowData.rowTotal = calculateStationRowTotal(rowData, workCenters);

            return rowData;
        });

        // Calculate column totals for this table
        const columnTotals = calculateStationColumnTotals(tableData, workCenters);

        // Build footer row
        let footerRow = '<tr><td style="font-weight: bold; background-color: aliceblue;">Grand Total</td>';
        workCenters.forEach(workCenter => {
            const total = columnTotals[`wc_${workCenter.workCenter}`];
            let netHours = 0;

            if (total.addedHours > 0) {
                netHours += convertToHours(total.addedHours, total.addedUnit);
            }
            if (total.deletedHours > 0) {
                netHours -= convertToHours(total.deletedHours, total.deletedUnit);
            }

            if (netHours !== 0) {
                const bestUnit = getBestDisplayUnit(Math.abs(netHours));
                const formattedNet = formatTimeWithUnit(Math.abs(netHours), bestUnit);
                const color = netHours < 0 ? 'red' : 'green';
                const sign = netHours < 0 ? '-' : '+';
                footerRow += `<td style="text-align: center; font-weight: bold; background-color: aliceblue;">
                                <span style="color: ${color}; font-weight: bold;">${sign}${formattedNet}${bestUnit}</span>
                            </td>`;
            } else {
                footerRow += `<td style="text-align: center; font-weight: bold; background-color: aliceblue;">-</td>`;
            }
        });

        // Calculate grand total for this table
        const grandTotal = calculateStationGrandTotal(tableData, workCenters);
        let grandTotalHtml = '';

        if (grandTotal.addedHours > 0) {
            grandTotalHtml += `<span style="color: green; font-weight: bold;">+${grandTotal.addedHours}${grandTotal.addedUnit}</span><br>`;
        }
        if (grandTotal.deletedHours > 0) {
            grandTotalHtml += `<span style="color: red; font-weight: bold;">-${grandTotal.deletedHours}${grandTotal.deletedUnit}</span><br>`;
        }

        const netColor = grandTotal.isNetNegative ? 'red' : 'green';
        const netSign = grandTotal.isNetNegative ? '-' : '+';
        grandTotalHtml += `<span style="color: ${netColor}; font-weight: bold; text-decoration: underline;">${netSign}${grandTotal.netHours}${grandTotal.netUnit}</span>`;

        footerRow += `<td style="text-align: center; font-weight: bold; background-color: #d0e8ff;">
                ${grandTotalHtml}
             </td></tr>`;

        $(`#${tableId} tfoot`).html(footerRow);

        // Initialize DataTable
        $(`#${tableId}`).DataTable({
            data: tableData,
            columns: columns,
            autoWidth: false,
            searching: true,
            paging: false,
            destroy: true,
            ordering: false,
            footerCallback: function(row, data, start, end, display) {
                $(this.api().table().footer()).find('td').css({
                    'border-top': '2px solid #dee2e6',
                    'font-weight': 'bold'
                });
            },
            initComplete: function() {
                $(`#${tableId} thead th:nth-child(1)`).css('width', '120px');
                $(`#${tableId} thead th:nth-child(n+2)`).css('width', '100px');
                $(`#${tableId} tbody td:nth-child(1)`).css('width', '120px');
                $(`#${tableId} tbody td:nth-child(n+2)`).css('width', '100px');
                $(`#${tableId} tfoot td:nth-child(1)`).css('width', '120px');
                $(`#${tableId} tfoot td:nth-child(n+2)`).css('width', '100px');

                $(`#${tableId}`).css('table-layout', 'fixed');
            }
        });

        // Style the search input for this specific table
        const searchWrapper = $(`#${tableId}_wrapper .dt-search`);
        searchWrapper.find('input[type="search"]')
            .attr('placeholder', `Search DTOs in ${projectNumber}-${lotNumber}`)
            .wrap('<div class="ui icon input"></div>')
            .after('<i class="search icon"></i>');

        searchWrapper.find('.dt-column-order').css('display', 'none');
        searchWrapper.find('.dt-input').css('border-radius', '50px');

    } catch (error) {
        console.error(`Error creating table for project ${projectNumber}-${lotNumber}:`, error);
        fireToastr('error', `Error creating table for project ${projectNumber}-${lotNumber}`);
    }
}

// Helper functions for station-based calculations
function calculateStationRowTotal(rowData, workCenters) {
    let totalHours = 0;

    workCenters.forEach(workCenter => {
        const wcData = rowData[`wc_${workCenter.workCenter}`];
        if (wcData) {
            if (wcData.addedHours > 0) {
                const addedInHours = convertToHours(wcData.addedHours, wcData.addedUnit);
                totalHours += addedInHours;
            }
            if (wcData.deletedHours > 0) {
                const deletedInHours = convertToHours(wcData.deletedHours, wcData.deletedUnit);
                totalHours -= deletedInHours;
            }
        }
    });

    const bestUnit = getBestDisplayUnit(Math.abs(totalHours));
    const formattedTotal = formatTimeWithUnit(Math.abs(totalHours), bestUnit);

    return {
        totalHours: formattedTotal,
        unit: bestUnit,
        isNegative: totalHours < 0
    };
}

function calculateStationColumnTotals(tableData, workCenters) {
    const totals = {};

    workCenters.forEach(workCenter => {
        const wcKey = `wc_${workCenter.workCenter}`;
        totals[wcKey] = {
            addedHours: 0,
            deletedHours: 0,
            addedUnit: 'h',
            deletedUnit: 'h'
        };
    });

    tableData.forEach(row => {
        workCenters.forEach(workCenter => {
            const wcKey = `wc_${workCenter.workCenter}`;
            const wcData = row[wcKey];
            if (wcData) {
                if (wcData.addedHours > 0) {
                    const addedInHours = convertToHours(wcData.addedHours, wcData.addedUnit);
                    totals[wcKey].addedHours += addedInHours;
                }
                if (wcData.deletedHours > 0) {
                    const deletedInHours = convertToHours(wcData.deletedHours, wcData.deletedUnit);
                    totals[wcKey].deletedHours += deletedInHours;
                }
            }
        });
    });

    // Convert totals back to best unit
    workCenters.forEach(workCenter => {
        const wcKey = `wc_${workCenter.workCenter}`;
        const total = totals[wcKey];
        total.addedUnit = getBestDisplayUnit(total.addedHours);
        total.deletedUnit = getBestDisplayUnit(total.deletedHours);
        total.addedHours = formatTimeWithUnit(total.addedHours, total.addedUnit);
        total.deletedHours = formatTimeWithUnit(total.deletedHours, total.deletedUnit);
    });

    return totals;
}

function calculateStationGrandTotal(tableData, workCenters) {
    let grandTotalAddedHours = 0;
    let grandTotalDeletedHours = 0;

    tableData.forEach(row => {
        workCenters.forEach(workCenter => {
            const wcKey = `wc_${workCenter.workCenter}`;
            const wcData = row[wcKey];
            if (wcData) {
                if (wcData.addedHours > 0) {
                    grandTotalAddedHours += convertToHours(wcData.addedHours, wcData.addedUnit);
                }
                if (wcData.deletedHours > 0) {
                    grandTotalDeletedHours += convertToHours(wcData.deletedHours, wcData.deletedUnit);
                }
            }
        });
    });

    const netTotal = grandTotalAddedHours - grandTotalDeletedHours;

    const addedUnit = getBestDisplayUnit(grandTotalAddedHours);
    const deletedUnit = getBestDisplayUnit(grandTotalDeletedHours);
    const netUnit = getBestDisplayUnit(Math.abs(netTotal));

    return {
        addedHours: formatTimeWithUnit(grandTotalAddedHours, addedUnit),
        addedUnit: addedUnit,
        deletedHours: formatTimeWithUnit(grandTotalDeletedHours, deletedUnit),
        deletedUnit: deletedUnit,
        netHours: formatTimeWithUnit(Math.abs(netTotal), netUnit),
        netUnit: netUnit,
        isNetNegative: netTotal < 0
    };
}

$(document).on('click', '#generateExcelBtn', function() {
    if (!window.excelExportData) {
        fireToastr('error', 'No data available for export');
        return;
    }

    $(this).addClass('loading');

    exportToExcelBackend(window.excelExportData);
});

async function exportToExcelBackend(groupedData) {
    try {
        const projectLotEntries = [];
        const exportData = {};

        Object.keys(groupedData).forEach(key => {
            const projectLotData = groupedData[key];
            projectLotEntries.push({
                project: projectLotData.project_number,
                lot: projectLotData.lot_number
            });
            exportData[key] = projectLotData;
        });

        // Create FormData and stringify the objects
        const formData = new FormData();
        formData.append('action', 'exportStationBasedAssemblyHoursToExcel');
        formData.append('projectLotEntries', JSON.stringify(projectLotEntries));
        formData.append('exportData', JSON.stringify(exportData));

        const response = await axios.post(
            '/dpm/dtoconfigurator/api/controllers/DTOAssemblyHoursController.php',
            formData,
            { responseType: 'blob' } // Remove the Content-Type header, let axios handle it
        );

        // Download the file
        const blob = new Blob([response.data], {
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        });

        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);

        // Generate filename with timestamp
        const now = new Date();
        const timestamp = now.toISOString().slice(0, 19).replace(/:/g, '-');
        link.download = `DTO_Assembly_Hours_Station_Based_${timestamp}.xlsx`;

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        fireToastr('success', 'Excel file generated and downloaded successfully');

    } catch (error) {
        console.error('Error generating Excel:', error);
        fireToastr('error', 'Error generating Excel file');
    } finally {
        $('#generateExcelBtn').removeClass('loading');
    }
}


$(document).on('click', '#clearBtn', function() {
    $('#projectLotInput').val('');
    $('#parsedResultsContainer').hide();
    $('#calculationResultsContainer').hide();
    $('#calculateBtn').prop('disabled', true);
    parsedProjectLots = [];
    fireToastr('info', 'All data cleared');
});

// Auto-resize textarea
$(document).on('input', '#projectLotInput', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
});