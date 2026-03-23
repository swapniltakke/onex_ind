async function fetchKukoMatrixData() {
    try {
        const projectNo = getUrlParam('project-no');
        const nachbauNo = getUrlParam('nachbau-no');
        const type = getUrlParam('type');
        const typeNumber = getUrlParam('type-number');
        const dtoNumber = getUrlParam('dto-number');
        const accessoryTypicalCode = NachbauDataOfProject[nachbauNo]['AccessoryTypicalCode'];

        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/KukoMatrixController.php', {
            params: {
                action: 'getKukoMatrix',
                projectNo: projectNo,
                nachbauNo: nachbauNo,
                type: type,
                typeNumber: typeNumber,
                dtoNumber: dtoNumber,
                accessoryTypicalCode: accessoryTypicalCode,
                page: 'project'
            },
            headers: { "Content-Type": "multipart/form-data" },
        });

        return response.data;
    } catch (error) {
        fireToastr('error', 'Error fetching Kuko Matrix Data', error);
        return [];
    }
}


async function getKukoMatrix() {
    const data = await fetchKukoMatrixData();
    const filterType = data['filter'];

    if ($.fn.DataTable.isDataTable('#kukoMatrixTable')) {
        $('#kukoMatrixTable').DataTable().clear().destroy();
        $('#kukoMatrixTable').empty(); // Clear any residual data in the table body
    }

    if (!data || !data.rows || Object.keys(data.rows).length === 0) {
        hideLoader('#kukoMatrix');
        showElement('#kukoMatrixContainer');
        $("#kukoMatrixNotFoundMsg").transition('pulse');
        return;
    }

    // Define columns dynamically
    const columns = [
        { title: "DTO Number", data: "DtoNumber", className: 'center aligned' },
        {
            title: "Description",
            data: "Description",
            className: 'center aligned',
            render: (data, type, row) => {
                return `<span class="description-click" data-dto="${row.DtoNumber}" data-desc="${escapeHtml(row.FullDescription)}" 
                              data-tooltip="Click on this to see full description" data-position="top right" style="cursor:pointer;">
                            ${data}
                        </span>`;
            }
        }
    ];

    const sanitizedColumns = data.columns;
    const panelCounts = data.panelCounts;  // Store panelCounts in a variable

    if (filterType === "no_filter" || filterType === 'dto_filter') {
        sanitizedColumns.forEach(column => {
            columns.push({
                title: column,
                data: row => row[column], // Use function to access key with a dot
                className: 'center aligned'
            });
        });
    } else {
        const typical = sanitizedColumns[0];
        columns.push({
            title: typical,
            data: row => row[typical], // Function for filtered columns
            className: 'center aligned'
        });
    }

    // Not eklenmiş dto numaraları select item olarak eklememek için ayıkla.
    const filteredDtoNumbers = Object.keys(data.rows).filter(dtoNumber => {
            const hasNote = data.notes.some(note => note.dto_number === dtoNumber);
            return !hasNote;
        }).join(',');

    // Add Kuko Note Column
    columns.push({
        title: `<div style="cursor:pointer;" class="kukoNoteBtn" data-tooltip="Click to Add Note" data-position="top center" onclick="showAddKukoNotesModal('${filteredDtoNumbers}')" >
                    <i class="plus circle icon large basic teal"></i><br>
                    Note
                </div>`,
        data: "KukoNote",
        className: 'center aligned',
    });


    //PREPARE ROWS
    const rows = Object.keys(data.rows).map(dtoNumber => {

        const note = data.notes.find(item => item.dto_number === dtoNumber);
        const kukoNoteHtml = note
            ? `<div class="ui icon violet button mini" style="padding:6px;" onclick="showKukoNoteDetailsModal('${dtoNumber}')" 
                data-tooltip="${note.kuko_note}&#xa;${note.created_by}" data-position="top right">
               Note
           </div>`
            : '';

        const row = {
            DtoRawNumber: dtoNumber,
            DtoNumber: formatDtoNumber(dtoNumber),
            Description: formatKukoDescription(escapeHtml(data.rows[dtoNumber].Description), 5) || "",
            FullDescription: data.rows[dtoNumber].Description,
            KukoNote: kukoNoteHtml,
            isDtoDeleted: note ? note.is_dto_deleted : "0",
            dtoExcludeOption: note ? note.dto_exclude_option : ""
        };

        if (filterType === "no_filter" || filterType === 'dto_filter') {
            sanitizedColumns.forEach(typical => {
                row[typical]  = data.rows[dtoNumber][`${typical}`] || ""; // Get DTO -> Typical Column has "X" or ""
            });
        } else {
            const typical = sanitizedColumns[0];
            row[typical] = data.rows[dtoNumber][`${typical}`] || ""; // Get DTO -> Typical Column has "X" or ""
        }

        return row;
    });
    const totalColumns = columns.length;  // Total number of columns

    const typicalColumns = Array.from({ length: totalColumns - 3 }, (_, i) => i + 2); // Targets from column 2 to last-1


    $('#kukoMatrixTable').DataTable({
        destroy: true,
        data: rows,
        columns: columns,
        ordering: false,
        autoWidth: false,
        paging: false,
        columnDefs: [
            { width: '10%', targets: [0] },
            {
                width: columns.length > 10 ? '25%' : '45%',
                targets: [1]
            },
            ...typicalColumns.map(index => ({ width: '3%', targets: index })),  // Set 3% for typicals
            { width: '5%', targets: [columns.length - 1] }

        ],
        createdRow: (row, rowData) => {
            setKukoCell(row, rowData, sanitizedColumns, data); // Set new kuko color on each click to cells
            getKukoCell(row, rowData, sanitizedColumns, data); // Get colors of kuko cells

            if (rowData.dtoExcludeOption === 'excludeAllTeams') {
                $('#kukoMatrixDtoNotWorkMsg').css('display', 'inline-flex');
                $(row).addClass('ui negative');
            }
            else if (rowData.dtoExcludeOption === 'excludeMechanicalTeam') {
                $('#kukoMatrixDtoMechanicNotWorkMsg').css('display', 'inline-flex');
                $(row).addClass('ui warning');
            }
        },
        initComplete: function () {
            sanitizedColumns.forEach((typical, index) => {
                const panelCount = panelCounts[`${typical}`] || 0;

                // Skip the first typical (index 0)
                if (index === 0) return;

                if (panelCount > 0) {
                    $(`#kukoMatrixTable thead th:eq(${index + 2})`).append(`
                    <div class="floating ui basic black label panelCountOfTypicalLabel" 
                         data-tooltip="Panel Count: ${panelCount}" 
                         data-position="top center">
                        ${panelCount}
                    </div>
                `);
                }
            });
        }
    });

    await handleSpecialDtoExistInfoMessage();

    if (SpareWdaDtosOfProject.length > 0 && InterchangeDtosOfProject.length > 0) {
        $("#spareInterchangeBothExistMsg").show();
    }

    hideLoader('#kukoMatrix');
    showElement('#kukoMatrixContainer');
}

$('#kukoMatrixTable').on('click', '.description-click', function () {
    const dtoNumber = $(this).data('dto');
    const fullDescription = $(this).data('desc');

    $('#dtoFullDescriptionModal .header').html(`<h4 style="text-align:center;">Full Description of ${dtoNumber}</h4>`);
    $('#dtoFullDescriptionModal .content').html(`<p style="text-align:center;font-weight:700;line-height:1.5rem;">${fullDescription.split('V:').join('<br>')}</p>`);
    $('#dtoFullDescriptionModal').modal('show').draggable({ handle: '.header', containment: 'window', blurring: false, inverted: true});
});

async function showAddKukoNotesModal(dtoNumbers) {
    const dtoNumbersArray = dtoNumbers.split(',').map(number => number.trim());

    if (dtoNumbersArray.length === 0) {
        showErrorDialog('No available DTO Numbers to add a note.');
        return;
    }

    $('#addKukoNotesModal .content').html('');
    const createKukoNoteBodyHtml = `<div class="ui form">
                                              <!-- Dropdown for DTO Numbers -->
                                              <div class="field">
                                                <label for="dtoSelect">DTO Number</label>
                                                <select id="dtoSelect" class="ui dropdown">
                                                  ${dtoNumbersArray.map(dto => `<option value="${dto}">${dto}</option>`).join('')}
                                                </select>
                                              </div>
                                        
                                              <!-- Textarea for Kuko Note -->
                                              <div class="field">
                                                <label for="dtoNote" class="mt-3 mb-2">Note</label>
                                                <textarea id="dtoNote" rows="4" placeholder="Enter Kuko Note..." required></textarea>
                                              </div>
                                        
                                              <!-- Radio buttons to ensure only one selection -->
                                              <div class="field" style="cursor: pointer;">
                                                <div class="ui radio checkbox">
                                                  <input type="radio" name="dtoOption" id="excludeMechanicalTeam" value="excludeMechanicalTeam">
                                                  <label for="excludeMechanicalTeam" style="cursor:pointer;">Mechanical team will not work on this DTO</label>
                                                </div>
                                              </div>
                                            
                                              <div class="field" style="cursor: pointer;">
                                                <div class="ui radio checkbox">
                                                  <input type="radio" name="dtoOption" id="excludeAllTeams" value="excludeAllTeams">
                                                  <label for="excludeAllTeams" style="cursor:pointer;">This DTO won’t be worked on</label>
                                                </div>
                                              </div>
                                            </div>`;

    $('#addKukoNotesModal .content').html(createKukoNoteBodyHtml);
    $('#addKukoNotesModal').modal('show').draggable({ handle: '.header', containment: 'window', blurring: false, inverted: true});
}

async function showKukoNoteDetailsModal(dtoNumber) {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/KukoMatrixController.php', {
            params: {
                action: 'getKukoNoteDetailsByDtoNumber',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                dtoNumber: dtoNumber
            },
            headers: { 'Content-Type': 'application/json' }
        });

        const { id, kuko_note, updated_by, dto_exclude_option, created } = response.data[0];

        const kukoNoteDetailsHtml = `
            <div class="ui form">
                
                <!-- DTO Number (read-only label) -->
                <div class="field">
                    <div id="dtoLabel" class="ui blue inverted horizontal label large">${dtoNumber}</div>
                </div>
        
                <!-- Textarea for Kuko Note -->
                <div class="field">
                    <label for="dtoNoteDetails" class="mt-3 mb-2">Note</label>
                    <textarea id="dtoNoteDetails" rows="4" placeholder="Enter your note..." required>${kuko_note || ''}</textarea>
                </div>
        
                <!-- Radio buttons for DTO Exclusion -->
                <div class="field" style="cursor: pointer;">
                    <div class="ui radio checkbox">
                        <input type="radio" name="dtoOptionDetails" id="excludeMechanicalTeamDetails" value="excludeMechanicalTeam">
                        <label for="excludeMechanicalTeamDetails" style="cursor:pointer;">Mechanical team will not work on this DTO</label>
                    </div>
                </div>
        
                <div class="field" style="cursor: pointer;">
                    <div class="ui radio checkbox">
                        <input type="radio" name="dtoOptionDetails" id="excludeAllTeamsDetails" value="excludeAllTeams">
                        <label for="excludeAllTeamsDetails" style="cursor:pointer;">This DTO won’t be worked on</label>
                    </div>
                </div>
        
                <!-- Created By (read-only label) -->
                <div class="field">
                    <div id="createdByLabel" class="ui label basic blue inverted ">${updated_by || 'N/A'}</div>
                </div>
                <div class="field">
                    <div id="createdLabel" class="ui label basic blue inverted ">${created || 'N/A'}</div>
                </div>
            </div>`;

        $('#kukoNoteDetailsModal .content').html(kukoNoteDetailsHtml);
        $('.ui.radio.checkbox').checkbox();
        if (dto_exclude_option === 'excludeMechanicalTeam') {
            $('#excludeMechanicalTeamDetails').prop('checked', true);
        } else if (dto_exclude_option === 'excludeAllTeams') {
            $('#excludeAllTeamsDetails').prop('checked', true);
        }


        // ✅ Store the last checked radio button
        let lastChecked = null;
        // ✅ Enable toggling to uncheck radio buttons on second click
        $('input[name="dtoOptionDetails"]').on('click', function () {
            if ($(this).val() === lastChecked) {
                // Uncheck the radio button using Semantic UI API
                $(this).closest('.ui.checkbox').checkbox('uncheck');
                lastChecked = null; // Reset selection
            } else {
                lastChecked = $(this).val(); // Store last checked value
            }
        });

        // store id for update and delete
        $('#deleteKukoNoteButton').data('note-id', id);
        $('#updateKukoNoteButton').data('note-id', id);

        $('#kukoNoteDetailsModal').modal('show');
    } catch (error) {
        console.error('Error fetching Kuko Note details:', error);
        fireToastr('error', 'Error fetching Kuko Note details. Please try again later.');
    }
}

$('#addKukoNotesModal').on('click', '#saveKukoNoteButton', async function () {
    const dtoNumber = $('#dtoSelect').val();
    const dtoNote = $('#dtoNote').val().trim();
    const radioButtonOption = $('input[name="dtoOption"]:checked').val() || null;

    if (!dtoNumber || !dtoNote) {
        fireToastr('warning', 'Please fill out all required fields.')
        return;
    }

    try {
        await axios.post('/dpm/dtoconfigurator/api/controllers/KukoMatrixController.php?',
            {
                action: 'createKukoMatrixNote',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no'),
                dtoNumber: dtoNumber,
                kukoNote: dtoNote,
                radioButtonOption: radioButtonOption
            },
            { headers: { 'Content-Type': 'multipart/form-data' }}
        );

        showSuccessDialog('Kuko Note created successfully.').then(() => {
            $('#addKukoNotesModal').modal('hide');
            getKukoMatrix();
            getProjectData();
        });
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    }
});

$('#kukoNoteDetailsModal').on('click', '#deleteKukoNoteButton', async function () {
    const noteId = $(this).data('note-id');

    if (!noteId) {
        fireToastr('error', 'Note ID is missing. Unable to delete.');
        return;
    }

    const confirmDelete = await Swal.fire({
        icon: 'warning',
        title: 'Are you sure?',
        text: 'Do you really want to delete this note?',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#43BB00',
        cancelButtonColor: '#6E7881'
    });

    if (!confirmDelete.isConfirmed)
        return;

    try {
        await axios.post('/dpm/dtoconfigurator/api/controllers/KukoMatrixController.php', {
                action: 'deleteKukoMatrixNote',
                noteId: noteId
            },
            { headers: { 'Content-Type': 'multipart/form-data' }});

        showSuccessDialog('Kuko Note deleted successfully.').then(() => {
            $('#kukoNoteDetailsModal').modal('hide');
            getKukoMatrix();
            getProjectData();
        });

    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    }
});

$('#kukoNoteDetailsModal').on('click', '#updateKukoNoteButton', async function () {
    const noteId = $(this).data('note-id'); // Get the note_id from the button
    const dtoNoteUpdated = $('#dtoNoteDetails').val().trim();
    const radioButtonOption = $('input[name="dtoOptionDetails"]:checked').val() || null;

    if (!dtoNoteUpdated) {
        fireToastr('warning', 'Please fill out all required fields.')
        return;
    }

    try {
        await axios.post('/dpm/dtoconfigurator/api/controllers/KukoMatrixController.php', {
                action: 'updateKukoMatrixNote',
                noteId: noteId,
                dtoNoteUpdated: dtoNoteUpdated,
                radioButtonOption: radioButtonOption
            },
            { headers: { 'Content-Type': 'multipart/form-data' }});

        showSuccessDialog('Kuko Note updated successfully.').then(() => {
            $('#kukoNoteDetailsModal').modal('hide');
            getKukoMatrix();
            getProjectData();
        });

    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    }
});

function setKukoCell(row, rowData, sanitizedColumns, data) {
    if (!currentlyWorkingUser.isAuthorizedToWorkOnProject || ['3', '5'].includes(currentlyWorkingUser.projectStatusId))
        return;

    $(row).find('td').each(function (index) {
        const totalColumns = $(row).find("td").length; // Get the total number of columns
        if (index > 1 && index < totalColumns - 1) { // Ignore DTO Number, Description, and Last Column
            const columnIndex = index - 2; // Adjust column index for sanitizedColumns
            const typical = sanitizedColumns[columnIndex];

            $(this).on("click", async function () {
                const xValue = rowData[typical] || "";
                if (xValue !== "X") return; // Only allow clicks on cells with "X"

                const currentColor = data.colors[`${typical}`]?.[rowData.DtoRawNumber] || "default";
                const colorOrder = ["default", "green", "yellow", "red"];
                const nextColor = colorOrder[(colorOrder.indexOf(currentColor) + 1) % colorOrder.length];

                // Update cell color dynamically in frontend
                const colorMap = {
                    "default": { background: "", text: "" },
                    "green": { background: "#D4EDDA", text: "#155724" },
                    "yellow": { background: "#FFF3CD", text: "#856404" },
                    "red": { background: "#FDDCDC", text: "#8B0000" }
                };
                const cellColor = colorMap[nextColor];
                $(this).css("background-color", cellColor.background).css("color", cellColor.text);

                try {
                    await axios.post('/dpm/dtoconfigurator/api/controllers/KukoMatrixController.php', {
                        action: 'updateKukoCellColor',
                        projectNo: getUrlParam('project-no'),
                        nachbauNo: getUrlParam('nachbau-no'),
                        rawDtoNumber: rowData.DtoRawNumber,
                        typicalNo: `${typical}`,
                        color: nextColor
                    }, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });

                    // Update data.colors object for consistency
                    if (!data.colors[`${typical}`]) {
                        data.colors[`${typical}`] = {};
                    }
                    data.colors[`${typical}`][rowData.DtoRawNumber] = nextColor;

                    fireToastr('success', 'Cell color updated successfully!');
                } catch (error) {
                    console.error('Error updating cell color:', error);
                    fireToastr('error', 'Failed to update cell color. Please try again.');
                }
            });
        }
    });
}

function getKukoCell(row, rowData, sanitizedColumns, data) {
    sanitizedColumns.forEach((typical, index) => {
        const xValue = rowData[typical] || "";
        const color = data.colors[`${typical}`]?.[rowData.DtoRawNumber] || "default";
        const colorMap = {
            "default": { background: "", text: "" },
            "green": { background: "#D4EDDA", text: "#155724" },
            "yellow": { background: "#FFF3CD", text: "#856404" },
            "red": { background: "#FDDCDC", text: "#8B0000" }
        };

        if (xValue === "X") {
            const cellColor = colorMap[color];
            $(row).find(`td:eq(${index + 2})`)
                .css("background-color", cellColor.background)
                .css("color", cellColor.text);
        }
    });
}

async function handleSpecialDtoExistInfoMessage(){
    let dtoTypes = new Set();

    if (SpareWdaDtosOfProject.length > 0 || SparePartDtosOfProject.length > 0) {
        dtoTypes.add("Spare");
    }
    if (MinusPriceDtosOfProject.length > 0) {
        dtoTypes.add("Minus Price");
    }
    if (ExtensionDtosOfProject.length > 0) {
        dtoTypes.add("Extension");
    }
    if (InterchangeDtosOfProject.length > 0) {
        dtoTypes.add("Interchange");
    }
    if (SpecialDtosOfProject.length > 0) {
        dtoTypes.add("Special");
    }

    let dtoText = Array.from(dtoTypes).join(", ");

    if (dtoText) {
        $("#specialDtoExistMsg .specialDtoSpan").text(dtoText);
        $("#specialDtoExistMsg").show();
    } else {
        $("#specialDtoExistMsg").hide();
    }
}
