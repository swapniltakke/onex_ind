let currentProjectNo = '';
let currentNachbauNo = '';
let currentAssemblyStart = '';
let projectStatus = '';

$(document).ready(async function() {
    $('.ui.accordion').accordion({
        exclusive: false,
        collapsible: true
    });
    const releasedProjectId = getUrlParam('released-project-id');
    await renderProjectDetailsData(releasedProjectId);
    await renderOrderChangesOfProject(releasedProjectId);
    await loadKukoMatrixForOrderChanges();
    await handleProjectStatus();

    $('#adminOrderChangesPage .loader').hide();
    $('#adminOrderChangesPage #adminOrderChangesPageContainer').transition('zoom');
});

async function renderProjectDetailsData(releasedProjectId) {
    const projectDetails = await getReleasedProjectDetailsById(releasedProjectId);
    projectStatus = projectDetails?.overall_project_status;

    currentProjectNo = projectDetails?.project_number;
    currentNachbauNo = projectDetails?.nachbau_number;
    currentAssemblyStart = projectDetails?.assembly_start_date;

    $('.project-details-menu .project-number').text(projectDetails?.project_number || '-');
    $('.project-details-menu .project-name').text(projectDetails?.project_name || '-');
    $('.project-details-menu .nachbau-number').text(projectDetails?.nachbau_number || '-');
    $('.project-details-menu .nachbau-date').text(projectDetails?.nachbau_date || '-');
    $('.project-details-menu .product').text(projectDetails?.product_name || '-');
    $('.project-details-menu .assembly-start-date').text(projectDetails?.assembly_start_date || '-');
    $('.project-details-menu .panel-qty').text(projectDetails?.panel_quantity || '-');
    $('.project-details-menu .submitted-by').text(projectDetails?.submitted_by || '-');
    $('.project-details-menu .submitted-date').text(moment(projectDetails?.submitted_date).format('DD.MM.YYYY HH:mm') || '-');

    if (projectStatus === '3' || projectStatus === '8') {
        $('#project-current-status .step-class').removeClass('completed');
        $('#project-current-status .icon-class').removeClass('payment').addClass('clock');
        $('#project-current-status .ui.steps').css('background', 'lightgoldenrodyellow');
        $('#project-current-status .step-class').css('background', 'lightgoldenrodyellow');
        $('#status-action-buttons').css('display', '');

        if (projectStatus === '3')
            $('#project-current-status .step-class .title').text('Pending Approval');
        else if (projectStatus === '8') {
            $('#project-current-status .step-class .title').text('Pending Revision Approval');
            $('#project-revision-info').show();
        }
    }
    else if (projectStatus === '4') {
        $('#project-current-status .step-class').removeClass('completed');
        $('#project-current-status .icon-class').removeClass('payment').addClass('ban');
        $('#project-current-status .ui.steps').css('background', 'red');
        $('#project-current-status .step-class').css('background', 'red');
        $('#project-current-status .step-class').css('color', 'white');
        $('#project-current-status .step-class .title').text('Publish Request Rejected');
        $('#status-action-buttons').css('display', 'none');

        const rejectedBy = projectDetails?.reviewed_by;
        const rejectedDate = moment(projectDetails?.reviewed_date).format('DD.MM.YYYY HH:mm');
        const rejectionReason = projectDetails?.rejection_reason;
        updateStatusDisplay('rejected');
        showMessage('error', 'Project publish request has been rejected!', rejectionReason, rejectedDate, rejectedBy);
    }
    else if (projectStatus === '5' ||  projectStatus === '9') {
        $('#project-current-status .ui.steps').css('background', 'palegreen');
        $('#project-current-status .step-class').css('background', 'palegreen');
        $('#project-current-status .step-class .title').text('Publish Request Approved');
        $('#status-action-buttons').css('display', 'none');

        if (projectStatus === '5')
            $('#project-current-status .step-class .title').text('Publish Request Approved');
        else if (projectStatus === '9') {
            $('#project-current-status .step-class .title').text('Revision Publish Approved');
            $('#project-revision-info').show();
        }

        const approvedBy = projectDetails?.reviewed_by;
        const approvedDate = moment(projectDetails?.reviewed_date).format('DD.MM.YYYY HH:mm');
        const approvalNote = projectDetails?.approval_notes;
        updateStatusDisplay('approved');
        showMessage('success', 'Project publish request has been approved successfully!', approvalNote, approvedDate, approvedBy);
    }
}

async function renderOrderChangesOfProject(releasedProjectId) {
    const orderChanges = await getReleasedBomChangeWithOrderChanges(releasedProjectId);

    const tableId = '#releasedOrderChangesTable';

    if ($.fn.DataTable.isDataTable(tableId)) {
        $(tableId).DataTable().clear().destroy();

        $(tableId + '_wrapper').remove();

        $(tableId + ' thead').empty();
        $(tableId + ' tbody').empty();

        $(tableId + ' thead').html(`
            <tr>
                <th>Pos</th>
                <th>Added Nr.</th>
                <th>Deleted Nr.</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Description</th>
                <th>Typical</th>
                <th>Panel</th>
                <th>Accs</th>
                <th>Note</th>
                <th>Revision</th>
            </tr>
        `);
    }

    if ($('#releasedOrderChangesTableContainer').hasClass('hidden'))
        $('#releasedOrderChangesTableContainer').removeClass('hidden');

    if (orderChanges.length === 0) {
        $('#releasedOrderChangesTableContainer').hide();
        return;
    }

    const affectedDtoDescriptions = await prepareAffectedDtoDescriptions(orderChanges);
    // Pre-process data to mark parent spare rows
    const processedSpareDtos = new Set();
    orderChanges.forEach(row => {
        if (row.released_dto_type_id === 2 && parseInt(row.spare_dto_type) !== 2) {
            let dtoTypicalKey = `${row.dto_number}-${row.spare_typical_no}`;
            if (!processedSpareDtos.has(dtoTypicalKey)) {
                processedSpareDtos.add(dtoTypicalKey);
                row.parentSpareRow = true;
            }
        }
    });

    $(tableId).DataTable({
        data: orderChanges,
        autoWidth: false,
        searching: true,
        paging: false,
        ordering: false,
        destroy: true,
        columnDefs: [
            {width: '3%', targets: [0]},
            {width: '9%', targets: [1, 2]},
            {width: '4%', targets: [3, 4]},
            {width: '4%', targets: [8]},
            {width: '23%', targets: [5]},
            {width: '8%', targets: [6,7]},
            {width: '24%', targets: [9]},
            {width: '6%', targets: [10]}
        ],
        columns: [
            {
                data: 'position',
                className: 'center aligned'
            },
            {
                data: 'material_added_number',
                render: (data, type, row) => {
                    if (row.type === 'nachbau_row')
                        return '';

                    if (row.operation === 'delete')
                        return 'SİL';

                    if (row.material_added_starts_by === 'A7ETKBL')
                        return `${row.material_added_starts_by}${row.material_added_number}`

                    return data;
                },
                className: 'center aligned'
            },
            {
                data: 'material_deleted_number',
                render: function (data, type, row) {
                    if (row.type === 'nachbau_row')
                        return row.kmat;

                    if (row.material_deleted_starts_by === 'A7ETKBL')
                        return `${row.material_deleted_starts_by}${row.material_deleted_number}`

                    if(row.is_cable)
                        return '';

                    return data;
                },
                className: 'center aligned'
            },
            {
                data: 'release_quantity',
                className: 'center aligned'
            },
            {
                data: 'release_unit',
                className: 'center aligned'
            },
            {
                data: null,
                render: (data, type, row) => {
                    if (row.type === 'nachbau_row' || row.is_cable)
                        return row.kmat_name;

                    if (row.type === 'nachbau_description')
                        return row.kmat_name.split("V:").filter(s => s.trim()).join("<br>");

                    if (row.operation === 'add')
                        return row.material_added_description;

                    return row.material_deleted_description;
                },
                className: 'center aligned'
            },
            {
                data: 'typical_no',
                className: 'center aligned'
            },
            {
                data: function (row) {
                    return row.ortz_kz ? row.ortz_kz + '/' + row.panel_no : row.panel_no;
                },
                className: 'center aligned'
            },
            {
                data: 'is_accessory',
                render: function (data) {
                    return data === '1' ? '<b>X</b>' : '';
                },
                className: 'center aligned'
            },
            {
                data: null,
                render: function (data, type, row) {
                    return renderNoteColumn(row, affectedDtoDescriptions);
                },
                className: 'center aligned'
            },
            {
                data: null,
                className: 'center aligned',
                width: '120px',
                render: function (data, type, row) {
                    if (row.is_revision_change === '1') {
                        return `<span style="color: red; white-space: normal; word-wrap: break-word; display: inline-block;"><b>${row.send_to_review_by}</b><br>${moment(row.created_at).format('DD.MM.YYYY')}</span>`;
                    }
                    return '';
                }
            }
        ],
        rowCallback: function (row, data) {
            if (data.kmat.startsWith('3003') || data.kmat.startsWith('3013')) {
                $(row).css('font-weight', 'bold');
                $(row).css('text-decoration', 'underline');
                $(row).css('color', 'mediumblue');
            }

            $(row).removeClass('row-add row-replace row-delete');

            if (data.operation === 'add') {
                $(row).addClass('row-add');
                $(row).find('td:eq(1)').css('font-weight', 'bold');
                $(row).find('td:eq(1)').css('color', 'green');
            } else if (data.operation === 'replace') {
                $(row).addClass('row-replace');
                $(row).find('td:eq(1)').css('font-weight', 'bold');
                $(row).find('td:eq(1)').css('color', 'green');
                $(row).find('td:eq(2)').css('font-weight', 'bold');
                $(row).find('td:eq(2)').css('color', 'red');
            } else if (data.operation === 'delete') {
                $(row).addClass('row-delete');
                $(row).find('td:eq(1)').css('font-weight', 'bold');
                $(row).find('td:eq(2)').css('font-weight', 'bold');
                $(row).find('td:eq(1)').css('color', 'red');
                $(row).find('td:eq(2)').css('color', 'red');
            }

            if (data.parentSpareRow) {
                $(row).addClass('parent-spare-row');
            }
        },
        initComplete: function () {
            const api = this.api();
            setupCompleteExcelFiltering(api, affectedDtoDescriptions);
        },
    });

    $('#releasedOrderChangesTable_wrapper .row .eight.wide.column').first().append(`
        <div class="ui small buttons">
            <button class="ui button clear-all-filters" onclick="clearAllColumnFilters()">
                <i class="eraser icon"></i>
                Clear All Filters
            </button>
        </div>
    `);

    $('.dt-search input[type="search"]').attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
}


function clearAllColumnFilters() {
    // Get the DataTable instance
    const table = $('#releasedOrderChangesTable').DataTable();
    const tableId = table.table().node().id;

    // Clear all column searches
    table.columns().search('').draw(false);

    // Remove all custom filters
    $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(function(fn) {
        return !fn.filterId || !fn.filterId.startsWith('column_');
    });

    // Special handling for Panel column (index 7) - reset to show all panels
    if (window.excelFilterRegistry && window.excelFilterRegistry[tableId]) {
        window.excelFilterRegistry[tableId].resetPanelFilterToAllPanels();
    }

    // Reset all OTHER filter dropdowns to "All Selected" state (skip panel column)
    $('.excel-filter-menu').each(function() {
        const $menu = $(this);
        const menuId = $menu.attr('id');

        // Skip panel column as it's handled separately above
        if (menuId === 'filter-menu-7') {
            return;
        }

        // Check all checkboxes
        $menu.find('.value-checkbox').prop('checked', true);
        $menu.find('.select-all-checkbox').prop('checked', true).prop('indeterminate', false);

        // Clear search input
        $menu.find('.filter-search-input').val('');

        // Show all filter items
        $menu.find('.filter-item:not(.select-all-item)').show();
    });

    // Update all button texts to show "All Selected"
    $('.excel-filter-btn').each(function() {
        const $btn = $(this);
        const columnIndex = $btn.data('column');
        const allCheckboxes = $(`#filter-menu-${columnIndex} .value-checkbox`);
        const blankCheckbox = allCheckboxes.filter('[value="__EMPTY__"]');
        const totalExcludingBlanks = blankCheckbox.length > 0 ? allCheckboxes.length - 1 : allCheckboxes.length;
        $btn.find('.filter-text').text(`All (${totalExcludingBlanks})`);
    });

    table.draw();

    $('.excel-filter-menu').removeClass('show');
}

async function prepareAffectedDtoDescriptions(orderChanges) {
    // Extract all unique affected DTO numbers
    const allAffectedDtos = new Set();

    orderChanges.forEach(row => {
        if (row.affected_dto_numbers && row.affected_dto_numbers.trim()) {
            const dtoNumbers = row.affected_dto_numbers.split('|');
            dtoNumbers.forEach(dto => {
                const trimmedDto = dto.trim();
                if (trimmedDto) {
                    allAffectedDtos.add(trimmedDto);
                }
            });
        }
    });

    // If no affected DTOs, return empty object
    if (allAffectedDtos.size === 0) {
        return {};
    }

    // Convert Set to Array and get descriptions
    const uniqueAffectedDtos = Array.from(allAffectedDtos);
    return await getDescriptionsOfAffectedDtoNumbers(uniqueAffectedDtos);
}

function renderNoteColumn(row, affectedDtoDescriptions) {
    const RELEASE_TYPES = {
        'Typical': 'TYPICAL BASED CHANGE',
        'Panel': 'PANEL BASED CHANGE'
    };

    const truncateText = (text, maxLength = 200) =>
        text?.length > maxLength ? `${text.substring(0, maxLength)}...` : (text || '');

    const createDtoHtml = (dtoNumber, description, releaseType) => `
        <div>
            <div class="dto-number">${dtoNumber}</div>
            <div class="dto-description">${truncateText(description)}</div>
            ${releaseType && parseInt(row.released_dto_type_id) !== 5 ? `<div class="release-type">${releaseType}</div>` : ''}
            ${parseInt(row.released_dto_type_id) === 3 && parseInt(row.is_accessory) === 1 ? `<div style="font-weight:bold;">${row.note}</div>${row.extension_extra_note ? `<br><div style="font-weight:bold;">${row.extension_extra_note}</div>` : ''}` : ''}            
            ${parseInt(row.released_dto_type_id) === 4 ? `<div style="font-weight:bold;">ÜRETİLMEYECEK</div>` : ''}
            ${parseInt(row.released_dto_type_id) === 5 ? `<div style="font-weight:bold;">NACHBAU ERROR</div>` : ''}
        </div>`;

    const releaseType = RELEASE_TYPES[row.release_type] || '';

    // Multiple DTOs case
    if (row.affected_dto_numbers?.trim()) {
        const validDtos = row.affected_dto_numbers
            .split('|')
            .map(dto => dto.trim())
            .filter(Boolean);

        const dtoHtmls = validDtos.map((dtoNumber, index) => {
            const divider = index > 0 ? '<hr style="margin: 8px 0; border: 0; border-top: 1px solid #ddd;">' : '';
            const description = affectedDtoDescriptions[dtoNumber] || '';
            return divider + createDtoHtml(row.dto_number, description, releaseType);
        });

        return `<div>${dtoHtmls.join('')}</div>`;
    }

    return createDtoHtml(row.dto_number, row.dto_description, releaseType);
}

async function getDescriptionsOfAffectedDtoNumbers(dtoNumbers) {
    const response = await axios.get('/dpm/dtoconfigurator/api/controllers/AdminController.php', {
        params: {
            action: 'getDescriptionsOfAffectedDtoNumbers',
            dtoNumbers: dtoNumbers
        }
    });

    return response.data;
}

async function getReleasedBomChangeWithOrderChanges(releasedProjectId) {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/AdminController.php', { params: { action: 'getReleasedBomChangeWithOrderChanges', releasedProjectId: releasedProjectId } });
        return response.data;
    } catch (error) {
        fireToastr('error', `Error fetching order changes of project with id : ${releasedProjectId}`, error);
    }
}

async function getReleasedProjectDetailsById(releasedProjectId) {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/AdminController.php', { params: { action: 'getReleasedProjectDetailsById', releasedProjectId: releasedProjectId } });
        return response.data;
    } catch (error) {
        fireToastr('error', 'Error fetching released projects:', error);
    }
}


function updateStatusDisplay(status) {
    const statusStep = $('.step-class');
    const statusTitle = $('.step-class .title');

    if (status === 'approved') {
        statusStep.removeClass('completed').addClass('completed');
        $('#project-current-status .ui.steps').css('background', 'palegreen');
        $('#status-action-buttons').css('display', 'none');

        statusStep.css('background', 'palegreen');
        statusStep.find('.icon-class').removeClass('payment').removeClass('clock').addClass('check');
        statusTitle.text('Publish Request Approved');

    } else if (status === 'rejected') {
        statusStep.removeClass('completed').addClass('disabled');
        $('#project-current-status .ui.steps').css('background', 'red');
        $('#status-action-buttons').css('display', 'none');

        statusStep.css('background', 'red');
        statusStep.css('color', 'white');
        statusStep.find('.icon-class').removeClass('payment').removeClass('clock').addClass('ban');
        statusTitle.text('Publish Request Rejected');
        statusTitle.css('color', 'white');
    }
}

// Show message
function showMessage(type, header, note, date, by) {
    const messageDiv = $('#status-message');
    messageDiv.removeClass('success error warning info');
    messageDiv.addClass(type);
    messageDiv.find('.header').text(header);

    let content = `${note ? `<div style="text-align: center;">
                                   <br><strong>Note:</strong> ${note}
                                 </div>` : ''}
                                 <br><div style="display: flex; justify-content: space-between;">
                                     <div><strong>Manager:</strong> ${by}</div>
                                     <div><strong>Date:</strong> ${date}</div>
                                 </div>`;
    messageDiv.find('p').html(content);
    messageDiv.show();
}



function setupCompleteExcelFiltering(api, affectedDtoDescriptions) {
    const settings = api.settings()[0];
    api.columns().every(function (columnIndex) {
        const column = this;
        const header = $(column.header());
        const columnDef = settings.aoColumns[columnIndex];

        // ====================================
        // STEP 1: Extract unique values from column data
        // ====================================
        const uniqueValues = [];
        const seenValues = new Set();

        // Special handling for Note column (index 9) to collect all unique DTO numbers
        if (columnIndex === 9) {
            const allRowsData = api.rows().data().toArray();
            const uniqueDtoNumbers = new Set();

            // Collect all DTO numbers from all rows
            allRowsData.forEach(rowData => {
                // Check for NACHBAU ERROR case first
                if (parseInt(rowData.released_dto_type_id) === 5) {
                    uniqueDtoNumbers.add('NACHBAU ERROR');
                    return; // Skip other processing for this row
                }

                // Add main DTO number
                if (rowData.dto_number) {
                    uniqueDtoNumbers.add(rowData.dto_number);
                }

                // Add affected DTO numbers
                if (rowData.affected_dto_numbers && rowData.affected_dto_numbers.trim()) {
                    const affectedDtos = rowData.affected_dto_numbers.split('|');
                    affectedDtos.forEach(dto => {
                        const trimmedDto = dto.trim();
                        if (trimmedDto) {
                            uniqueDtoNumbers.add(trimmedDto);
                        }
                    });
                }
            });

            // Check if there are any rows with completely blank Note columns (excluding NACHBAU ERROR)
            const hasBlankNoteRows = allRowsData.some(rowData =>
                parseInt(rowData.released_dto_type_id) !== 5 && // Not NACHBAU ERROR
                (!rowData.dto_number || rowData.dto_number.trim() === '') &&
                (!rowData.affected_dto_numbers || rowData.affected_dto_numbers.trim() === '')
            );

            // Create display text for each unique DTO number
            uniqueDtoNumbers.forEach(dtoNumber => {
                let description = '';

                // First try to get description from affectedDtoDescriptions
                if (affectedDtoDescriptions[dtoNumber]) {
                    description = affectedDtoDescriptions[dtoNumber];
                } else {
                    // Try to find it from main dto_description in any row
                    const rowWithDescription = allRowsData.find(row => row.dto_number === dtoNumber);
                    if (rowWithDescription && rowWithDescription.dto_description) {
                        description = rowWithDescription.dto_description;
                    }
                }

                const displayText = description
                    ? `${dtoNumber} - ${description}`
                    : dtoNumber;

                uniqueValues.push(displayText);
            });

            // Add blank option if there are blank Note rows
            if (hasBlankNoteRows) {
                uniqueValues.unshift(''); // Add empty string at the beginning
            }
        }  else {
            // For other columns, process normally
            column.data().each(function (value, rowIndex) {
                let displayValue = processColumnValue(value, columnDef, api, rowIndex, columnIndex);

                if (!seenValues.has(displayValue)) {
                    seenValues.add(displayValue);
                    uniqueValues.push(displayValue);
                }
            });
        }

        // Sort values: empty first, then alphabetically/numerically
        uniqueValues.sort(sortValues);

        // ====================================
        // STEP 2: Create filter UI elements
        // ====================================
        const {
            filterContainer,
            dropdownBtn,
            dropdownMenu,
            searchBox,
            scrollableContent,
            actionButtons,
            valueMapping
        } = createFilterElements(columnIndex, uniqueValues);

        // Append to header
        header.append(filterContainer);

        // ====================================
        // STEP 3: Setup functionality
        // ====================================
        const selectAllCheckbox = scrollableContent.find('.select-all-checkbox');
        const valueCheckboxes = scrollableContent.find('.value-checkbox');
        const searchInput = searchBox.find('.filter-search-input');
        const allFilterItems = scrollableContent.find('.filter-item:not(.select-all-item)');

        let initialSelectedValues = new Set(Array.from(valueMapping.keys()));

        // Search functionality
        setupSearchFunctionality(searchInput, allFilterItems);

        // Dropdown positioning and toggle
        setupDropdownToggle(dropdownBtn, dropdownMenu, searchInput, allFilterItems);

        // Select All functionality
        setupSelectAllFunctionality(selectAllCheckbox, valueCheckboxes);

        // Button event handlers
        setupButtonHandlers(
            actionButtons,
            dropdownMenu,
            valueCheckboxes,
            selectAllCheckbox,
            initialSelectedValues,
            valueMapping,
            uniqueValues,
            column,
            columnDef,
            columnIndex,
            api,
            updateButtonText
        );

        // Initialize display
        updateButtonText();

        // ====================================
        // HELPER FUNCTIONS
        // ====================================

        function updateSelectAllState() {
            const visibleCheckboxes = valueCheckboxes.filter(function() {
                return $(this).closest('.filter-item').is(':visible');
            });
            const totalVisible = visibleCheckboxes.length;
            const checkedVisible = visibleCheckboxes.filter(':checked').length;

            if (checkedVisible === 0) {
                selectAllCheckbox.prop('checked', false).prop('indeterminate', false);
            } else if (checkedVisible === totalVisible) {
                selectAllCheckbox.prop('checked', true).prop('indeterminate', false);
            } else {
                selectAllCheckbox.prop('checked', false).prop('indeterminate', true);
            }
        }

        function updateButtonText() {
            const checkedItems = valueCheckboxes.filter(':checked').length;
            const totalItems = valueCheckboxes.length;

            // Check if blank option exists and is checked
            const blankCheckbox = valueCheckboxes.filter('[value="__EMPTY__"]');
            const hasBlankOption = blankCheckbox.length > 0;
            const isBlankChecked = hasBlankOption && blankCheckbox.is(':checked');

            // Calculate counts excluding blank option
            const totalExcludingBlanks = hasBlankOption ? totalItems - 1 : totalItems;
            const checkedExcludingBlanks = isBlankChecked ? checkedItems - 1 : checkedItems;

            let text;
            if (checkedItems === 0) {
                text = 'None Selected';
            } else if (checkedItems === totalItems) {
                text = `All (${totalExcludingBlanks})`;
            } else {
                text = `${checkedExcludingBlanks} of ${totalExcludingBlanks} Selected`;
            }

            dropdownBtn.find('.filter-text').text(text);
        }
    });

    // Close dropdowns when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.excel-filter-container').length) {
            $('.excel-filter-menu').removeClass('show');
        }
    });

    // ====================================
    // UTILITY FUNCTIONS
    // ====================================

    function processColumnValue(value, columnDef, api, rowIndex, columnIndex) {
        let displayValue = value;

        // Handle different data types
        if (typeof value === 'object' && value !== null) {
            displayValue = JSON.stringify(value);
        } else if (value === null || value === undefined) {
            displayValue = '';
        } else {
            displayValue = String(value);
        }

        // Handle rendered columns
        if (columnDef.render && typeof columnDef.render === 'function') {
            const rowData = api.row(rowIndex).data();
            const renderedValue = columnDef.render(value, 'display', rowData);

            // Special handling for Note column (index 9) - extract only dto-number
            if (columnIndex === 9) {
                // For Note column with data: null, get values directly from rowData
                const dtoNumber = rowData.dto_number || '';
                const dtoDescription = rowData.dto_description || '';

                if (dtoNumber && dtoDescription) {
                    displayValue = `${dtoNumber} - ${dtoDescription}`;
                } else if (dtoNumber) {
                    displayValue = dtoNumber;
                } else if (dtoDescription) {
                    displayValue = dtoDescription;
                } else {
                    displayValue = String(renderedValue).replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
                }
            }
            // Special handling for Revision column (index 10)
            else if (columnIndex === 10) {
                if (rowData.is_revision_change === '1') {
                    const createdBy = rowData.send_to_review_by || '';
                    const createdAt = rowData.created_at ? moment(rowData.created_at).format('DD.MM.YYYY') : '';
                    displayValue = createdBy && createdAt ? `${createdBy} ${createdAt}` : '';
                } else {
                    displayValue = '';
                }
            }
            // For HTML rendered columns (like Description), clean HTML tags
            else if (String(renderedValue).includes('<')) {
                displayValue = String(renderedValue).replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
            } else {
                // For simple transformations (like Qty column removing .000), use rendered value directly
                displayValue = String(renderedValue);
            }
        }

        return String(displayValue).trim();
    }

    function sortValues(a, b) {
        // Empty values first
        if (a === '' && b !== '') return -1;
        if (a !== '' && b === '') return 1;

        // Numeric comparison for numbers
        if (!isNaN(a) && !isNaN(b)) {
            return parseFloat(a) - parseFloat(b);
        }

        // Alphabetical comparison
        return String(a).localeCompare(String(b));
    }

    function createFilterElements(columnIndex, uniqueValues) {
        // Main container
        const filterContainer = $('<div class="excel-filter-container"></div>');

        // Calculate count excluding blanks
        const countExcludingBlanks = uniqueValues.filter(value => value !== '').length;

        // Dropdown button
        const dropdownBtn = $(`
            <button class="excel-filter-btn" type="button" data-column="${columnIndex}">
                <span class="filter-text">All Selected (${countExcludingBlanks})</span>
                <i class="dropdown-arrow">▼</i>
            </button>
            `);

        // Dropdown menu
        const dropdownMenu = $(`<div class="excel-filter-menu" id="filter-menu-${columnIndex}"></div>`);

        // Search box
        const searchBox = $(`
        <div class="filter-search-container">
            <input type="text" class="filter-search-input" placeholder="Search items..." />
            <span class="filter-search-icon">🔍</span>
        </div>
    `);

        // Scrollable content
        const scrollableContent = $('<div class="excel-filter-content"></div>');

        // Select All item
        const selectAllItem = $(`
        <div class="filter-item select-all-item">
            <label>
                <input type="checkbox" class="select-all-checkbox" checked>
                <span class="checkmark"></span>
                <span class="item-text">Select All</span>
            </label>
        </div>
    `);

        // Add elements to scrollable content
        scrollableContent.append(selectAllItem);
        scrollableContent.append('<div class="filter-separator"></div>');

        // Create value mapping and filter items
        const valueMapping = new Map();
        uniqueValues.forEach(function (value, index) {
            const { filterItem, safeId } = createFilterItem(value, index);
            valueMapping.set(safeId, value);
            scrollableContent.append(filterItem);
        });

        // Action buttons
        const actionButtons = $(`
        <div class="filter-actions">
            <button type="button" class="filter-ok-btn">OK</button>
            <button type="button" class="filter-cancel-btn">Cancel</button>
            <button type="button" class="filter-clear-btn">Clear All</button>
        </div>
    `);

        // Assemble dropdown
        dropdownMenu.append(searchBox);
        dropdownMenu.append(scrollableContent);
        dropdownMenu.append(actionButtons);

        filterContainer.append(dropdownBtn);
        filterContainer.append(dropdownMenu);

        return {
            filterContainer,
            dropdownBtn,
            dropdownMenu,
            searchBox,
            scrollableContent,
            actionButtons,
            valueMapping
        };
    }

    function createFilterItem(value, index) {
        const safeId = value === '' ? '__EMPTY__' : 'opt_' + index;
        const displayText = value === '' ? '(Blank)' : value;
        const searchText = String(displayText || '').toLowerCase().trim();

        const filterItem = $(`
        <div class="filter-item" data-value="${safeId}">
            <label>
                <input type="checkbox" class="value-checkbox" value="${safeId}" checked>
                <span class="checkmark"></span>
                <span class="item-text">${$('<div>').text(displayText).html()}</span>
            </label>
        </div>
    `);

        filterItem.attr('data-text', searchText);
        return { filterItem, safeId };
    }

    function setupSearchFunctionality(searchInput, allFilterItems) {
        searchInput.on('input', function () {
            const searchTerm = $(this).val().toLowerCase().trim();

            if (searchTerm === '') {
                allFilterItems.show();
            } else {
                allFilterItems.each(function () {
                    const itemText = $(this).attr('data-text') || '';
                    if (itemText.includes(searchTerm)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });

        searchInput.on('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'a' || e.keyCode === 65)) {
                e.stopPropagation();
                this.select();
                return false;
            }
        });
    }

    function setupDropdownToggle(dropdownBtn, dropdownMenu, searchInput, allFilterItems) {
        dropdownBtn.on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            $('.excel-filter-menu').not(dropdownMenu).removeClass('show');

            if (dropdownMenu.hasClass('show')) {
                dropdownMenu.removeClass('show');
            } else {
                searchInput.val('');
                allFilterItems.show();

                const columnIndex = $(this).data('column');
                if (columnIndex === 0) {
                    dropdownMenu.css({
                        'position': 'fixed',
                        'top': '650px',
                        'left': '10%',
                        'transform': 'none',
                        'z-index': '99999'
                    });
                } else {
                    dropdownMenu.css({
                        'position': 'absolute',
                        'top': '65px',
                        'left': '50%',
                        'transform': 'translateX(-50%)',
                        'z-index': '9999'
                    });
                }

                dropdownMenu.addClass('show');
                setTimeout(() => searchInput.focus(), 100);
            }
        });
    }

    function setupSelectAllFunctionality(selectAllCheckbox, valueCheckboxes) {
        selectAllCheckbox.on('change', function () {
            const isChecked = $(this).prop('checked');
            const visibleCheckboxes = valueCheckboxes.filter(function() {
                return $(this).closest('.filter-item').is(':visible');
            });
            visibleCheckboxes.prop('checked', isChecked);
        });
    }

    function setupButtonHandlers(
        actionButtons,
        dropdownMenu,
        valueCheckboxes,
        selectAllCheckbox,
        initialSelectedValues,
        valueMapping,
        uniqueValues,
        column,
        columnDef,
        columnIndex,
        api,
        updateButtonText
    ) {
        // OK Button
        actionButtons.find('.filter-ok-btn').on('click', function () {
            applyColumnFilter(
                valueCheckboxes,
                valueMapping,
                uniqueValues,
                column,
                columnDef,
                columnIndex,
                api
            );
            updateButtonText();
            dropdownMenu.removeClass('show');

            // Update initial state for cancel functionality
            initialSelectedValues.clear();
            valueCheckboxes.filter(':checked').each(function() {
                initialSelectedValues.add($(this).val());
            });

            // If this is the Typical column (index 6), update Panel column filter
            if (columnIndex === 6) {
                updatePanelColumnFilter(valueCheckboxes, valueMapping, api);
            }
        });

        // Cancel Button
        actionButtons.find('.filter-cancel-btn').on('click', function () {
            valueCheckboxes.each(function () {
                const isInitiallySelected = initialSelectedValues.has($(this).val());
                $(this).prop('checked', isInitiallySelected);
            });
            updateButtonText();
            dropdownMenu.removeClass('show');
        });

        // Clear All Button
        actionButtons.find('.filter-clear-btn').on('click', function () {
            valueCheckboxes.prop('checked', false);
            selectAllCheckbox.prop('checked', false).prop('indeterminate', false);
            updateButtonText();
        });
    }

    function applyColumnFilter(
        valueCheckboxes,
        valueMapping,
        uniqueValues,
        column,
        columnDef,
        columnIndex,
        api
    ) {
        const selectedValues = valueCheckboxes.filter(':checked').map(function () {
            return valueMapping.get($(this).val());
        }).get();

        // Remove any existing custom filter for this column
        const filterId = `column_${columnIndex}_filter`;
        $.fn.dataTable.ext.search = $.fn.dataTable.ext.search.filter(function(fn) {
            return fn.filterId !== filterId;
        });

        if (selectedValues.length === 0) {
            // No selection - show nothing
            column.search('__NO_MATCH__', true, false).draw();
        } else if (selectedValues.length === uniqueValues.length) {
            // All values selected - show all
            column.search('', true, false).draw();
        } else {
            // Partial selection - apply appropriate filter
            if (shouldUseCustomFilter(columnIndex, columnDef)) {
                applyCustomFilter(selectedValues, columnIndex, api, filterId);
            } else {
                applyRegexFilter(selectedValues, column);
            }
        }
    }

    function shouldUseCustomFilter(columnIndex, columnDef) {
        // Use custom filter for:
        // - Description column (index 5)
        // - Qty column (index 3) that has .000 replacement
        // - Note column (index 9) that needs dto-number extraction
        // - Any column with render function that transforms the data
        return columnIndex === 5 ||
            columnIndex === 3 ||
            columnIndex === 9 ||
            columnIndex === 10 ||
            (columnDef.render && typeof columnDef.render === 'function');
    }

    function applyCustomFilter(selectedValues, columnIndex, api, filterId) {
        // Clear existing column search
        api.column(columnIndex).search('', true, false);

        // Create custom filter function
        const customFilter = function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'releasedOrderChangesTable') {
                return true;
            }

            const cellData = data[columnIndex] || '';
            let cleanedCellData;

            if (columnIndex === 3) {
                // For Qty column: apply the same .000 replacement as the render function
                cleanedCellData = String(cellData).replace('.000', '').trim();
            } else if (columnIndex === 9) {
                const rowData = api.row(dataIndex).data();

                // Check for NACHBAU ERROR case first
                if (parseInt(rowData?.released_dto_type_id) === 5) {
                    return selectedValues.includes('NACHBAU ERROR');
                }

                // Check if this row has blank Note column
                const isBlankNote = (!rowData.dto_number || rowData.dto_number.trim() === '') &&
                    (!rowData.affected_dto_numbers || rowData.affected_dto_numbers.trim() === '');

                // If blank is selected and this row is blank, show it
                if (selectedValues.includes('') && isBlankNote) {
                    return true;
                }

                // If blank is selected but this row is not blank, don't show it (unless other DTOs are selected)
                if (selectedValues.includes('') && selectedValues.length === 1 && !isBlankNote) {
                    return false;
                }

                // For non-blank rows, check DTO numbers
                if (!isBlankNote) {
                    const selectedDtoNumbers = selectedValues
                        .filter(value => value !== '' && value !== 'NACHBAU ERROR') // Exclude blank and NACHBAU ERROR from DTO matching
                        .map(value => value.split(' - ')[0]);

                    if (selectedDtoNumbers.length === 0) {
                        return false; // Only blank was selected, but this row is not blank
                    }

                    const rowDtoNumbers = [];

                    // Get main DTO number
                    if (rowData.dto_number) {
                        rowDtoNumbers.push(rowData.dto_number);
                    }

                    // Get affected DTO numbers
                    if (rowData.affected_dto_numbers && rowData.affected_dto_numbers.trim()) {
                        const affectedDtos = rowData.affected_dto_numbers
                            .split('|')
                            .map(dto => dto.trim())
                            .filter(dto => dto);
                        rowDtoNumbers.push(...affectedDtos);
                    }

                    // Check if any DTO number from this row is in the selected values
                    return rowDtoNumbers.some(dtoNumber => selectedDtoNumbers.includes(dtoNumber));
                }

                return false;
            }
            else if (columnIndex === 10) {
                // For Revision column
                const rowData = api.row(dataIndex).data();
                if (rowData.is_revision_change === '1') {
                    const createdBy = rowData.send_to_review_by || '';
                    const createdAt = rowData.created_at ? moment(rowData.created_at).format('DD.MM.YYYY') : '';
                    cleanedCellData = createdBy && createdAt ? `${createdBy} ${createdAt}` : '';
                } else {
                    cleanedCellData = '';
                }
            }
            else {
                // For other columns (like Description): remove HTML tags
                cleanedCellData = String(cellData)
                    .replace(/<[^>]*>/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim();
            }

            if (columnIndex !== 9) {
                return selectedValues.includes(cleanedCellData);
            }
        };

        customFilter.filterId = filterId;
        $.fn.dataTable.ext.search.push(customFilter);
        api.draw();
    }

    function applyRegexFilter(selectedValues, column) {
        const searchValues = selectedValues.map(v => {
            return v === '' ? '^$' : v.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        });

        const regexPattern = '^(' + searchValues.join('|') + ')$';
        column.search(regexPattern, true, false).draw();
    }

    function updatePanelColumnFilter(typicalCheckboxes, typicalValueMapping, api) {
        // Get selected typical values
        const selectedTypicals = typicalCheckboxes.filter(':checked').map(function () {
            return typicalValueMapping.get($(this).val());
        }).get();

        // If no typicals selected, show all panels from current data
        if (selectedTypicals.length === 0) {
            const allPanels = getAllPanelsFromCurrentData(api);
            rebuildPanelFilter(api, allPanels);
            return;
        }

        const panelBtn = $('.excel-filter-btn[data-column="7"]');
        panelBtn.prop('disabled', true).find('.filter-text').text('Loading...');

        getOrtzKzsAndPanelNoOfSelectedTypicals(selectedTypicals, function(panels) {
            panelBtn.prop('disabled', false);
            rebuildPanelFilter(api, panels || []);
        });
    }

    function getAllPanelsFromCurrentData(api) {
        const allRowsData = api.rows().data().toArray();
        const uniquePanels = [];
        const seenPanels = new Set();

        allRowsData.forEach(rowData => {
            const panelValue = rowData.ortz_kz ? rowData.ortz_kz + '/' + rowData.panel_no : rowData.panel_no;
            if (panelValue && !seenPanels.has(panelValue)) {
                seenPanels.add(panelValue);
                uniquePanels.push({
                    ortz_kz: rowData.ortz_kz,
                    panel_no: rowData.panel_no
                });
            }
        });

        return uniquePanels;
    }

    function rebuildPanelFilter(api, availablePanels) {
        const panelColumnIndex = 7; // Panel column index
        const panelColumn = api.column(panelColumnIndex);
        const panelHeader = $(panelColumn.header());
        const panelFilterContainer = panelHeader.find('.excel-filter-container');

        if (panelFilterContainer.length === 0) return;

        // Get the current panel filter elements
        const panelDropdownMenu = panelFilterContainer.find('.excel-filter-menu');
        const panelScrollableContent = panelDropdownMenu.find('.excel-filter-content');
        const panelActionButtons = panelDropdownMenu.find('.filter-actions');
        const panelDropdownBtn = panelFilterContainer.find('.excel-filter-btn');

        // Clear existing filter items (except Select All)
        panelScrollableContent.find('.filter-item:not(.select-all-item)').remove();
        panelScrollableContent.find('.filter-separator').remove();

        // Create new value mapping
        const newValueMapping = new Map();
        const newUniqueValues = [];

        if (availablePanels.length === 0) {
            newUniqueValues.push('');
        } else {
            // Add available panels
            availablePanels.forEach(panel => {
                const panelValue = panel.ortz_kz ? panel.ortz_kz + '/' + panel.panel_no : panel.panel_no;
                newUniqueValues.push(panelValue);
            });
        }

        // Sort values
        newUniqueValues.sort(sortValues);

        // Add separator
        panelScrollableContent.append('<div class="filter-separator"></div>');

        // Create new filter items
        newUniqueValues.forEach(function (value, index) {
            const { filterItem, safeId } = createFilterItem(value, index);
            newValueMapping.set(safeId, value);
            panelScrollableContent.append(filterItem);
        });

        // Update button text
        const countExcludingBlanks = newUniqueValues.filter(value => value !== '').length;
        panelDropdownBtn.find('.filter-text').text(`(${countExcludingBlanks}) Selected `);

        // Get new checkboxes
        const newValueCheckboxes = panelScrollableContent.find('.value-checkbox');
        const newSelectAllCheckbox = panelScrollableContent.find('.select-all-checkbox');

        // Reset select all state
        newSelectAllCheckbox.prop('checked', true).prop('indeterminate', false);

        // Update button handlers for the new elements
        const newInitialSelectedValues = new Set(Array.from(newValueMapping.keys()));

        // Re-setup Select All functionality for new elements
        setupSelectAllFunctionality(newSelectAllCheckbox, newValueCheckboxes);

        // Re-setup search functionality for new elements
        const newSearchInput = panelDropdownMenu.find('.filter-search-input');
        const newAllFilterItems = panelScrollableContent.find('.filter-item:not(.select-all-item)');
        setupSearchFunctionality(newSearchInput, newAllFilterItems);

        // Re-setup button handlers
        setupButtonHandlers(
            panelActionButtons,
            panelDropdownMenu,
            newValueCheckboxes,
            newSelectAllCheckbox,
            newInitialSelectedValues,
            newValueMapping,
            newUniqueValues,
            panelColumn,
            api.settings()[0].aoColumns[panelColumnIndex],
            panelColumnIndex,
            api,
            function() {
                const checkedItems = newValueCheckboxes.filter(':checked').length;
                const totalItems = newValueCheckboxes.length;
                const blankCheckbox = newValueCheckboxes.filter('[value="__EMPTY__"]');
                const hasBlankOption = blankCheckbox.length > 0;
                const isBlankChecked = hasBlankOption && blankCheckbox.is(':checked');
                const totalExcludingBlanks = hasBlankOption ? totalItems - 1 : totalItems;
                const checkedExcludingBlanks = isBlankChecked ? checkedItems - 1 : checkedItems;

                let text;
                if (checkedItems === 0) {
                    text = 'None Selected';
                } else if (checkedItems === totalItems) {
                    text = `All (${totalExcludingBlanks})`;
                } else {
                    text = `${checkedExcludingBlanks} of ${totalExcludingBlanks} Selected`;
                }
                panelDropdownBtn.find('.filter-text').text(text);
            }
        );

        // Apply the filter to show all available panels
        applyColumnFilter(
            newValueCheckboxes,
            newValueMapping,
            newUniqueValues,
            panelColumn,
            api.settings()[0].aoColumns[panelColumnIndex],
            panelColumnIndex,
            api
        );
    }

    const tableId = api.table().node().id;
    window.excelFilterRegistry = window.excelFilterRegistry || {};
    window.excelFilterRegistry[tableId] = {
        resetPanelFilterToAllPanels: function() {
            const allPanels = getAllPanelsFromCurrentData(api);
            rebuildPanelFilter(api, allPanels);
        }
    };
}

async function handleProjectStatus() {
    // Initialize modals
    $('#approve-modal').modal({
        closable: false,
        onApprove: function() {
            $('#approve-modal .ui.ok.button').addClass('loading disabled');
            const note = $('#approve-note').val().trim();
            handleApproval(note);
            $('#approve-modal .ui.ok.button').removeClass('loading disabled');
            return true;
        }
    });

    $('#reject-modal').modal({
        closable: false,
        onApprove: function() {
            $('#reject-modal .ui.ok.button').addClass('loading disabled');
            const note = $('#reject-note').val().trim();
            if (note === '') {
                // Show error if rejection note is empty
                showMessage('error', 'Rejection reason is required!');
                return false;
            }
            handleRejection(note);
            $('#reject-modal .ui.ok.button').removeClass('loading disabled');
            return true;
        }
    });

    $('#approve-btn').click(function() {
        $('#approve-note').val('');
        $('#approve-modal').modal('show');
    });

    $('#reject-btn').click(function() {
        $('#reject-note').val('');
        $('#reject-modal').modal('show');
    });

    // Function to copy BOM Excel to folder
    function copyBomExcelToFolder() {
        return new Promise((resolve, reject) => {
            let isRevision = projectStatus === '8';
            const releasedOrderChangesTableData = $('#releasedOrderChangesTable').DataTable().data().toArray();

            const formData = new FormData();
            formData.append('projectNo', currentProjectNo);
            formData.append('nachbauNo', currentNachbauNo);
            formData.append('assemblyStart', currentAssemblyStart);
            formData.append('orderSummaryData', JSON.stringify(releasedOrderChangesTableData));
            formData.append('operation', 'copyToFolder');
            formData.append('isRevision', isRevision);

            axios.post('/dpm/dtoconfigurator/api/controllers/ExcelFunctions.php', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
                .then(response => {
                    if (response.data.success) {
                        resolve(response.data);
                    } else {
                        reject(new Error(response.data.message || 'Failed to copy BOM Excel file to folder.'));
                    }
                })
                .catch(error => {
                    reject(error);
                });
        });
    }

    // Function to copy AKD Import XML to folder
    function copyAkdImportXmlFileToFolder() {
        return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append('projectNo', currentProjectNo);
            formData.append('nachbauNo', currentNachbauNo);
            formData.append('operation', 'copyToFolder');
            formData.append('page', 'admin');
            formData.append('releasedProjectId', getUrlParam('released-project-id'));
            formData.append('action', 'generateAKDImportXmlFile');

            axios.post('/dpm/dtoconfigurator/api/controllers/NachbauController.php', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
                .then(response => {
                    if (response.data.success) {
                        resolve(response.data);
                    } else {
                        reject(new Error(response.data.message || 'Failed to copy AKD XML file to folder.'));
                    }
                })
                .catch(error => {
                    reject(error);
                });
        });
    }

    async function handleApproval(note) {
        if (projectStatus === '8') {
            updateProjectStatus('revision_approved', note);
        } else {
            updateProjectStatus('approved', note);
        }

        let bomSuccess = false;
        let akdSuccess = false;
        let bomResult = null;
        let akdResult = null;

        try {
            // Step 1: Copy BOM Excel to folder
            Swal.fire({
                title: 'Processing Files',
                html: '<div id="progress-status">📄 <strong>BOM Excel is copying...</strong><br><br>Please wait...</div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Process BOM Excel
            try {
                bomResult = await copyBomExcelToFolder();
                bomSuccess = true;
            } catch (error) {
                bomSuccess = false;
                bomResult = { success: false, message: error.message };
            }

            // Update progress - BOM done, start AKD
            Swal.update({
                html: `<div id="progress-status">
                ${bomSuccess ? '✅' : '❌'} <strong>BOM Excel: ${bomSuccess ? 'Done' : 'Failed'}</strong><br>
                📑 <strong>AKD Import XML is generating and copying...</strong><br><br>
                Please wait...
            </div>`
            });

            if (projectStatus !== '8') {
                // Step 2: Copy AKD Import XML to folder
                try {
                    akdResult = await copyAkdImportXmlFileToFolder();
                    akdSuccess = true;
                } catch (error) {
                    akdSuccess = false;
                    akdResult = { success: false, message: error.message };
                }
            }

            // Step 3: Show final results
            Swal.close();
            showFinalResults(bomSuccess, akdSuccess, bomResult, akdResult, note);
            sendPublishResultMail(getUrlParam('released-project-id'), note, 'approved');
        } catch (error) {
            Swal.close();
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'An unexpected error occurred: ' + error.message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    }

    // Function to handle final results display
    function showFinalResults(bomSuccess, akdSuccess, bomResult, akdResult, note) {
        let finalTitle, finalMessage, finalIcon;

        if (bomSuccess && akdSuccess) {
            finalTitle = 'Success!';
            finalMessage = '✅ Both BOM Excel and AKD Import XML files have been successfully copied to the project folder.';
            finalIcon = 'success';

            // Update status and show success message only if both succeeded
            updateStatusDisplay('approved');
            const curDate = new Date().toLocaleString('de-DE', {
                day: '2-digit',
                month: '2-digit',
                year: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            }).replace(',', '');
            const by = userInfo?.Fullname;
            showMessage('success', 'Project publish request has been approved successfully!', note, curDate, by);
            disableActionButtons();

        } else if (bomSuccess && !akdSuccess) {
            finalTitle = 'Partial Success';
            finalMessage = `✅ BOM Excel is copied successfully<br>❌ AKD Import XML failed: ${akdResult.message || 'Unknown error'}`;
            finalIcon = 'warning';
        } else if (!bomSuccess && akdSuccess) {
            finalTitle = 'Partial Success';
            finalMessage = `❌ BOM Excel failed: ${bomResult.message || 'Unknown error'}<br>✅ AKD Import XML is copied successfully`;
            finalIcon = 'warning';

        } else {
            finalTitle = 'Error!';
            finalMessage = `❌ Both operations failed:<br>BOM Excel: ${bomResult.message || 'Unknown error'}<br>AKD Import XML: ${akdResult.message || 'Unknown error'}`;
            finalIcon = 'error';
        }

        Swal.fire({
            title: finalTitle,
            html: finalMessage,
            icon: finalIcon,
            confirmButtonText: 'OK'
        });
    }

    // Handle rejection
    function handleRejection(note) {
        updateProjectStatus('rejected', note);
        updateStatusDisplay('rejected');

        const curDate = new Date().toLocaleString('de-DE', {day:'2-digit', month:'2-digit', year:'2-digit', hour:'2-digit', minute:'2-digit', hour12:false}).replace(',', '');
        const by = userInfo?.Fullname;
        showMessage('error', 'Project publish request has been rejected.', note, curDate, by);

        disableActionButtons();
        sendPublishResultMail(getUrlParam('released-project-id'), note, 'rejected');
    }

    function sendPublishResultMail(releasedProjectId, note, resultStatus) {
        let isRevision = projectStatus === '8';


        try {
            axios.post('/dpm/dtoconfigurator/api/controllers/AdminController.php?',
                {
                    action: 'sendPublishResultMail',
                    releasedProjectId: releasedProjectId,
                    note: note,
                    resultStatus: resultStatus,
                    isRevision: isRevision
                },
                { headers: { 'Content-Type': 'multipart/form-data' }}
            );
        } catch(error) {
            const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
            showErrorDialog(`${errorMessage}`);
        }
    }

    function updateProjectStatus(status, note) {
        try {
            axios.post('/dpm/dtoconfigurator/api/controllers/AdminController.php?',
                {
                    action: 'updateProjectStatus',
                    releasedProjectId: getUrlParam('released-project-id'),
                    status: status,
                    note: note
                },
                { headers: { 'Content-Type': 'multipart/form-data' }}
            );

            showSuccessDialog('Project status updated successfully.').then(async () => {});

        } catch(error) {
            const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
            showErrorDialog(`${errorMessage}`);
        } finally {
            $(this).removeClass('loading disabled');
        }
    }


    function disableActionButtons() {
        $('#approve-btn, #reject-btn').addClass('disabled');
    }
}

function getOrtzKzsAndPanelNoOfSelectedTypicals(selectedTypicals, callback) {
    axios.get('/dpm/dtoconfigurator/api/controllers/NachbauController.php', {
        params: {
            action: 'getOrtzKzsAndPanelNoOfSelectedTypicals',
            page: 'admin',
            selectedTypicals: selectedTypicals,
            releasedProjectId: getUrlParam('released-project-id')
        }
    })
        .then(response => {
            callback(response.data || []);
        })
        .catch(error => {
            const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
            showErrorDialog(`Error: ${errorMessage}`);
            callback([]);
        });
}

// ==================== KUKO MATRIX FUNCTIONS STARTS ====================

let KukoMatrixData = null;
async function fetchKukoMatrixData(releasedProjectId) {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/KukoMatrixController.php', {
            params: {
                action: 'getKukoMatrix',
                releasedProjectId: releasedProjectId,
                page: 'admin'
            },
            headers: { "Content-Type": "multipart/form-data" },
        });

        return response.data;
    } catch (error) {
        console.error('Error fetching Kuko Matrix Data:', error);
        fireToastr('error', 'Error fetching Kuko Matrix Data', error.message);
        return null;
    }
}

async function initKukoMatrix(releasedProjectId) {
    $('#kukoMatrixLoader').show();
    $('#kukoMatrixContainer').hide();
    $('#kukoMatrixSection').show();

    const data = await fetchKukoMatrixData(releasedProjectId);

    if (!data || !data.rows || Object.keys(data.rows).length === 0) {
        $('#kukoMatrixLoader').hide();
        $('#kukoMatrixNotFoundMsg').show();
        return;
    }

    KukoMatrixData = data;
    renderKukoMatrix(data);
}


function renderKukoMatrix(data) {

    if ($.fn.DataTable.isDataTable('#kukoMatrixTable')) {
        $('#kukoMatrixTable').DataTable().clear().destroy();
        $('#kukoMatrixTable').empty();
    }

    // Define base columns
    const columns = [
        {
            title: "DTO Number",
            data: "DtoNumber",
            className: 'center aligned',
            width: '10%'
        },
        {
            title: "Description",
            data: "Description",
            className: 'center aligned',
            width: '30%',
            render: (data, type, row) => {
                return `<span class="description-click" 
                              data-dto="${row.DtoNumber}" 
                              data-desc="${escapeHtml(row.FullDescription)}" 
                              data-tooltip="Click to see full description" 
                              data-position="top right" 
                              style="cursor:pointer;">
                            ${data}
                        </span>`;
            }
        }
    ];

    const sanitizedColumns = data.columns;
    const panelCounts = data.panelCounts;

    // Add typical columns
    sanitizedColumns.forEach(column => {
        columns.push({
            title: column,
            data: row => row[column],
            className: 'center aligned',
            width: '3%'
        });
    });

    columns.push({
        title: `<div style="text-align:center;">
                    Note
                </div>`,
        data: "KukoNote",
        className: 'center aligned',
        width: '5%',
        orderable: false
    });

    // Prepare rows
    const rows = Object.keys(data.rows).map(dtoNumber => {
        const note = data.notes.find(item => item.dto_number === dtoNumber);
        const kukoNoteHtml = note
            ? `<div class="ui icon violet mini button" 
                    style="padding:6px;" 
                    data-tooltip="${escapeHtml(note.kuko_note)}&#xa;${escapeHtml(note.created_by)}&#xa;${note.created}" 
                    data-position="left center">
                   <i class="sticky note icon"></i>
               </div>`
            : '';

        const row = {
            DtoRawNumber: dtoNumber,
            DtoNumber: formatDtoNumber(dtoNumber),
            Description: formatKukoDescription(escapeHtml(data.rows[dtoNumber].Description), 5) || "",
            FullDescription: data.rows[dtoNumber].Description || "",
            KukoNote: kukoNoteHtml,
            isDtoDeleted: note ? note.is_dto_deleted : "0",
            dtoExcludeOption: note ? note.dto_exclude_option : ""
        };

        // Add typical column values
        sanitizedColumns.forEach(typical => {
            row[typical] = data.rows[dtoNumber][typical] || "";
        });

        return row;
    });

    // Initialize DataTable
    $('#kukoMatrixTable').DataTable({
        destroy: true,
        data: rows,
        columns: columns,
        ordering: false,
        autoWidth: false,
        paging: false,
        searching: true,
        info: true,
        dom: '<"top"f>rt<"bottom"i>',
        createdRow: (row, rowData) => {
            // Apply cell colors
            applyKukoCellColors(row, rowData, sanitizedColumns, data);

            // Apply row styling based on exclusion status
            if (rowData.dtoExcludeOption === 'excludeAllTeams') {
                $('#kukoMatrixDtoNotWorkMsg').show();
                $(row).addClass('negative');
            } else if (rowData.dtoExcludeOption === 'excludeMechanicalTeam') {
                $('#kukoMatrixDtoMechanicNotWorkMsg').show();
                $(row).addClass('warning');
            }
        },
        initComplete: function () {
            // Add panel counts to column headers
            sanitizedColumns.forEach((typical, index) => {
                const panelCount = panelCounts[typical] || 0;

                // Skip the first typical (accessory column)
                if (index === 0) return;

                if (panelCount > 0) {
                    $(`#kukoMatrixTable thead th:eq(${index + 2})`).append(`
                        <div class="floating ui basic black mini label panelCountOfTypicalLabel" 
                             style="margin-top: 2px;"
                             data-tooltip="Panel Count: ${panelCount}" 
                             data-position="bottom center">
                            ${panelCount}
                        </div>
                    `);
                }
            });

            // Initialize tooltips
            $('.ui.table [data-tooltip]').popup();

            // Hide loader and show table
            $('#kukoMatrixLoader').hide();
            $('#kukoMatrixContainer').show();
        }
    });

    $('.dt-search input[type="search"]').attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
}

function applyKukoCellColors(row, rowData, sanitizedColumns, data) {
    const colorMap = {
        "default": { background: "", text: "" },
        "green": { background: "#D4EDDA", text: "#155724" },
        "yellow": { background: "#FFF3CD", text: "#856404" },
        "red": { background: "#FDDCDC", text: "#8B0000" }
    };

    sanitizedColumns.forEach((typical, index) => {
        const xValue = rowData[typical] || "";
        const color = data.colors?.[typical]?.[rowData.DtoRawNumber] || "default";

        if (xValue === "X") {
            const cellColor = colorMap[color];
            $(row).find(`td:eq(${index + 2})`)
                .css("background-color", cellColor.background)
                .css("color", cellColor.text)
                .css("font-weight", "bold");
        }
    });
}

$(document).on('click', '.description-click', function () {
    const dtoNumber = $(this).data('dto');
    const fullDescription = $(this).data('desc');

    $('#dtoFullDescriptionModal .header').html(
        `<h4 style="text-align:center;">Full Description of ${dtoNumber}</h4>`
    );
    $('#dtoFullDescriptionModal .content').html(
        `<p style="text-align:center; font-weight:700; line-height:1.5rem;">
            ${fullDescription.split('V:').join('<br>')}
        </p>`
    );

    $('#dtoFullDescriptionModal')
        .modal({
            blurring: false,
            inverted: true
        })
        .modal('show');
});

async function loadKukoMatrixForOrderChanges() {
    const releasedProjectId = getUrlParam('released-project-id');

    if (releasedProjectId) {
        await initKukoMatrix(releasedProjectId);
    } else {
        console.warn('Missing parameters for Kuko Matrix');
        $('#kukoMatrixSection').hide();
    }
}

// ==================== KUKO MATRIX FUNCTIONS END ====================
