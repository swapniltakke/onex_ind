$(document).ready(async function() {
    $('.menu .item').tab();

    $('#dtoAssemblyHoursPage .loader').hide();
    $('#dtoAssemblyHoursPageContainer').transition('pulse');
    await initializeProjectSearchDropdown();
});

async function initializeProjectSearchDropdown() {
    const projectSelectBox = $('#searchProjectSelect');

    projectSelectBox.dropdown({
        apiSettings: {
            url: `/dpm/dtoconfigurator/api/controllers/BaseController.php?action=searchProject&keyword={query}`,
            cache: false,
            onResponse: function(response) {
                const menuElement = document.querySelector('.searchProjectSelect .menu.transition.visible');
                const projects = Array.isArray(response) ? response : Object.values(response);
                let results = [];

                if (projects.length === 0) {
                    if (menuElement)
                        menuElement.innerHTML = '';
                    return { results };
                }

                results = projects.map(project => ({
                    name: `<b>${project.FactoryNumber}</b> - ${project.ProjectName}`,
                    value: project.FactoryNumber
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
        allowAdditions: false,
        fullTextSearch: true,
        forceSelection: false,
        selectOnKeydown: false,
        onChange: function(value) {
            clearDTOMatrix();

            if (value) {
                initializeNachbauSelectBox(value);
            } else {
                clearNachbauDropdown();
            }
        }
    });
}

async function initializeNachbauSelectBox(projectNo) {
    const nachbauSelectBox = $('#searchNachbauSelect');

    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/NachbauController.php', {
            params: { action: 'getNachbauFilesOfProjectWithStatus', projectNo: projectNo }
        });

        const nachbauFiles = response.data;

        nachbauSelectBox.empty().append('<option value="" selected>Select Nachbau</option>');

        if (nachbauFiles && typeof nachbauFiles === 'object') {
            Object.entries(nachbauFiles).forEach(([fileName, fileData]) => {
                const publishedText = fileData.isPublished ? ' - Published' : '';

                nachbauSelectBox.append(
                    `<option value="${fileName}">
                        <b>${fileName}</b> - ${fileData.LastUpdated}${publishedText}
                    </option>`
                );
            });

            nachbauSelectBox.dropdown({
                search: true,
                clearable: true,
                allowAdditions: false
            });

            // Add the published-option class to published files after dropdown initialization
            Object.entries(nachbauFiles).forEach(([fileName, fileData]) => {
                if (fileData.isPublished) {
                    $(`.ui.dropdown .menu .item[data-value="${fileName}"]`).addClass('published-option');
                }
            });

            nachbauSelectBox.on('change', function() {
                const selectedNachbau = $(this).val();
                if (selectedNachbau) {
                    showDTOAssemblyHoursMatrix(projectNo, selectedNachbau);
                }
            });
            $('#searchNachbauField').show();

        } else {
            fireToastr('info', 'No nachbau files found for this project');
        }

    } catch (error) {
        console.error('Error loading nachbau files:', error);
        fireToastr('error', 'Error loading nachbau files');
    }
}

async function showDTOAssemblyHoursMatrix(projectNo, nachbauFileName) {
    try {
        $('#dtoMatrixContainer .loader').show();
        $('#dtoAssemblyHoursTable_wrapper').hide();

        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/DTOAssemblyHoursController.php', {
            params: {
                action: 'getDTOAssemblyHoursMatrixData',
                projectNo: projectNo,
                nachbauNo: nachbauFileName
            }
        });

        const matrixData = response.data;

        if (!matrixData || !matrixData.dtoData || !matrixData.panels) {
            fireToastr('warning', 'No matrix data found for selected project and nachbau');
            return;
        }

        if ($.fn.DataTable.isDataTable('#dtoAssemblyHoursTable')) {
            $('#dtoAssemblyHoursTable').DataTable().destroy();
        }

        let headerRow = '<tr><th class="center aligned">DTO Number</th><th class="center aligned">DTO Description</th>';
        matrixData.panels.forEach(panel => {
            headerRow += `<th class="center aligned">${panel.panelName}</th>`;
        });
        headerRow += '<th class="center aligned">Total</th></tr>';
        $('#dtoAssemblyHoursTable thead').html(headerRow);

        const columns = [
            {
                data: 'dtoNumber',
                className: 'center aligned',
                render: function(data) {
                    return `<a href="#" class="dto-link" data-dto-number="${data}">${data}</a>`
                }
            },
            {
                data: 'dtoDescription',
                className: 'center aligned',
                render: function(data, type, row) {
                    if (type === 'display') {
                        if (data && data.length > 30) {
                            const truncatedText = data.substring(0, 30) + '...';
                            return `<div class="tooltip-container" data-tooltip="${data.replace(/"/g, '&quot;')}" data-position="top left" style="overflow:hidden;text-overflow: ellipsis; white-space: nowrap;">${truncatedText}</div>`;
                        }
                        return data || '';
                    }
                    return data || '';
                }
            }
        ];

        // Add panel columns dynamically
        matrixData.panels.forEach(panel => {
            columns.push({
                data: panel.panelId,
                className: 'center aligned',
                render: function(data, type, row) {
                    if (type === 'display') {
                        const panelData = row[panel.panelId];
                        if (!panelData) return '-';

                        let html = '';
                        if (panelData.addedHours > 0) {
                            html += `<span style="color: green; font-weight: bold;">+${panelData.addedHours}${panelData.addedUnit}</span>`;
                        }
                        if (panelData.deletedHours > 0) {
                            if (html) html += '<br>';
                            html += `<span style="color: red; font-weight: bold;">-${panelData.deletedHours}${panelData.deletedUnit}</span>`;
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
        const tableData = matrixData.dtoData.map(dto => {
            const rowData = {
                dtoNumber: dto.dtoNumber,
                dtoDescription: dto.dtoDescription
            };

            matrixData.panels.forEach(panel => {
                const panelData = dto.panelHours.find(ph => ph.panelId === panel.panelId);
                rowData[panel.panelId] = panelData ? {
                    addedHours: panelData.addedHours || 0,
                    addedUnit: panelData.addedUnit || 'h',
                    deletedHours: panelData.deletedHours || 0,
                    deletedUnit: panelData.deletedUnit || 'h'
                } : null;
            });

            // Calculate row total
            rowData.rowTotal = calculateRowTotal(rowData, matrixData.panels);

            return rowData;
        });

        const columnTotals = calculateColumnTotals(tableData, matrixData.panels);

        let footerRow = '<tr><td style="font-weight: bold; background-color: aliceblue;">Grand Total</td><td style="background-color: #e9ecef;"></td>';
        matrixData.panels.forEach(panel => {
            const total = columnTotals[panel.panelId];
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

        const grandTotal = calculateGrandTotal(tableData);
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

        $('#dtoAssemblyHoursTable tfoot').html(footerRow);

        $('#dtoAssemblyHoursTable').DataTable({
            data: tableData,
            columns: columns,
            autoWidth: false,
            searching: true,
            paging: false,
            destroy: true,
            order: [[0, 'asc']],
            footerCallback: function(row, data, start, end, display) {
                // Footer is already built, just ensure styling
                $(this.api().table().footer()).find('td').css({
                    'border-top': '2px solid #dee2e6',
                    'font-weight': 'bold'
                });
            },
            initComplete: function() {
                $('#dtoMatrixContainer .loader').hide();
                $('#dtoAssemblyHoursTable_wrapper').show();

                $('#dtoAssemblyHoursTable thead th:nth-child(1)').css('width', '100px');
                $('#dtoAssemblyHoursTable thead th:nth-child(2)').css('width', '140px');
                $('#dtoAssemblyHoursTable tbody td:nth-child(2)').css('width', '140px');
                $('#dtoAssemblyHoursTable tfoot td:nth-child(1)').css('width', '100px');
                $('#dtoAssemblyHoursTable tfoot td:nth-child(2)').css('width', '140px');

                $('#dtoAssemblyHoursTable thead th:nth-child(n+3)').css('width', '50px');
                $('#dtoAssemblyHoursTable tbody td:nth-child(n+3)').css('width', '50px');
                $('#dtoAssemblyHoursTable tfoot td:nth-child(n+3)').css('width', '50px');

                $('#dtoAssemblyHoursTable').css('table-layout', 'fixed');

                $('#dtoMatrixContainer').show();

                fireToastr('success', 'DTO Assembly Hours Matrix loaded successfully');
            }
        });

        $('.dt-search input[type="search"]').attr('placeholder', 'Search DTOs').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
        $('.dt-column-order').css('display', 'none');
        $('.dt-input').css('border-radius', '50px');

    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "Error loading DTO Assembly Hours Matrix. Get Contact with DGT Team!";
        showErrorDialog(`<b>${errorMessage}</b>`);
        $('#dtoMatrixContainer .loader').hide();
    }
}

function clearDTOMatrix() {
    if ($.fn.DataTable.isDataTable('#dtoAssemblyHoursTable')) {
        $('#dtoAssemblyHoursTable').DataTable().destroy();
    }
    $('#dtoAssemblyHoursTable thead').empty();
    $('#dtoAssemblyHoursTable tbody').empty();
    $('#dtoAssemblyHoursTable tfoot').empty();
    $('#dtoMatrixContainer').hide();
}

function clearNachbauDropdown() {
    const nachbauSelectBox = $('#searchNachbauSelect');

    nachbauSelectBox.dropdown('clear');
    nachbauSelectBox.empty();
    nachbauSelectBox.append('<option value="">Select Nachbau</option>');
    nachbauSelectBox.off('change');

    clearDTOMatrix();
}

function convertToHours(time, unit) {
    switch (unit) {
        case 's': return time / 3600;
        case 'm': return time / 60;
        case 'h':
        default: return time;
    }
}

function getBestDisplayUnit(hours) {
    if (hours >= 1) return 'h';
    if (hours >= 1/60) return 'm';
    return 's';
}

function formatTimeWithUnit(hours, unit) {
    switch (unit) {
        case 's': return Math.round(hours * 3600 * 100) / 100;
        case 'm': return Math.round(hours * 60 * 100) / 100;
        case 'h':
        default: return Math.round(hours * 100) / 100;
    }
}

function calculateColumnTotals(tableData, panels) {
    const totals = {};

    panels.forEach(panel => {
        totals[panel.panelId] = {
            addedHours: 0,
            deletedHours: 0,
            addedUnit: 'h',
            deletedUnit: 'h'
        };
    });

    tableData.forEach(row => {
        panels.forEach(panel => {
            const panelData = row[panel.panelId];
            if (panelData) {
                if (panelData.addedHours > 0) {
                    // Convert to hours for totaling
                    const addedInHours = convertToHours(panelData.addedHours, panelData.addedUnit);
                    totals[panel.panelId].addedHours += addedInHours;
                }
                if (panelData.deletedHours > 0) {
                    const deletedInHours = convertToHours(panelData.deletedHours, panelData.deletedUnit);
                    totals[panel.panelId].deletedHours += deletedInHours;
                }
            }
        });
    });

    // Convert totals back to best unit
    panels.forEach(panel => {
        const panelTotal = totals[panel.panelId];
        panelTotal.addedUnit = getBestDisplayUnit(panelTotal.addedHours);
        panelTotal.deletedUnit = getBestDisplayUnit(panelTotal.deletedHours);
        panelTotal.addedHours = formatTimeWithUnit(panelTotal.addedHours, panelTotal.addedUnit);
        panelTotal.deletedHours = formatTimeWithUnit(panelTotal.deletedHours, panelTotal.deletedUnit);
    });

    return totals;
}

function calculateGrandTotal(tableData) {
    let grandTotalAddedHours = 0;
    let grandTotalDeletedHours = 0;

    tableData.forEach(row => {
        Object.keys(row).forEach(key => {
            if (key !== 'dtoNumber' && key !== 'dtoDescription' && key !== 'rowTotal') {
                const panelData = row[key];
                if (panelData) {
                    if (panelData.addedHours > 0) {
                        grandTotalAddedHours += convertToHours(panelData.addedHours, panelData.addedUnit);
                    }
                    if (panelData.deletedHours > 0) {
                        grandTotalDeletedHours += convertToHours(panelData.deletedHours, panelData.deletedUnit);
                    }
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

function calculateRowTotal(rowData, panels) {
    let totalHours = 0;

    panels.forEach(panel => {
        const panelData = rowData[panel.panelId];
        if (panelData) {
            if (panelData.addedHours > 0) {
                const addedInHours = convertToHours(panelData.addedHours, panelData.addedUnit);
                totalHours += addedInHours;
            }
            if (panelData.deletedHours > 0) {
                const deletedInHours = convertToHours(panelData.deletedHours, panelData.deletedUnit);
                totalHours -= deletedInHours; // Subtract deleted hours
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

$(document).on('click', '.dto-link', async function (e) {
    e.preventDefault();

    const dtoNumber = $(this).data('dto-number'); // Get dto_number from the link

    await axios.get(`/dpm/dtoconfigurator/api/controllers/TkFormController.php?action=getTkFormByDtoNumber&dtoNumber=${dtoNumber}`)
        .then(response => {
            const { id, document_number } = response.data;
            const url = `/dpm/dtoconfigurator/core/tkform/detail/material-list.php?id=${id}&document-number=${document_number}&dto-number=${dtoNumber}`;
            window.open(url, '_blank');
        })
        .catch(error => {
            console.error("Error fetching DTO parameters:", error);
            alert('An error occurred while fetching the data.');
        });
});