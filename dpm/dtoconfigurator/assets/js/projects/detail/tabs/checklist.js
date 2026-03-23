let projectChecklistTable;
let globalProjectId;
let globalProductId;

async function initializeProjectChecklist() {
    await loadProjectChecklistItems();

    hideLoader('#checklist');
    showElement('#checklistTabContainer');
}

async function loadProjectChecklistItems() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/ChecklistController.php', {
            params: {
                action: 'getProjectChecklistItems',
                projectNo: getUrlParam('project-no'),
                nachbauNo: getUrlParam('nachbau-no')
            }
        });

        const checklistItems = response.data || [];
        globalProjectId = checklistItems[0]['project_id'];
        globalProductId = checklistItems[0]['product_id'];
        initializeProjectChecklistTable(checklistItems);
        updateProgressStats(checklistItems);

    } catch (error) {
        console.error('Error loading project checklist:', error);
        fireToastr('error', 'Failed to load checklist items');
    }
}

function initializeProjectChecklistTable(checklistItems) {
    if ($.fn.DataTable.isDataTable('#projectChecklistTable')) {
        $('#projectChecklistTable').DataTable().destroy();
    }

    projectChecklistTable = $('#projectChecklistTable').DataTable({
        data: checklistItems,
        autoWidth: false,
        paging: false,
        searching: true,
        order: [[2, 'asc']],
        columnDefs: [
            { width: '55%', targets: 1 },
            { targets: [0, 3, 5], orderable: false},
        ],
        columns: [
            {
                data: null,
                className: 'center aligned',
                render: function(data, type, row) {
                    if (row.is_completed) {
                        return '<i class="check circle icon" style="color: #21ba45; font-size: 20px;"></i>';
                    } else {
                        return '<i class="circle outline icon" style="color: #999; font-size: 20px; cursor: pointer;" onclick="toggleChecklistItem(' + row.id + ')"></i>';
                    }
                }
            },
            {
                data: 'checklist_detail_html',
                className: 'center aligned',
                render: function(data, type, row) {
                    if (type === 'display') {
                        const decodedHtml = $('<textarea>').html(data).text();
                        return `<div style="word-wrap: break-word; white-space: normal; line-height: 1.4;">${decodedHtml}</div>`;
                    }
                    return data;
                },
                createdCell: function(td, cellData, rowData, row, col) {
                    if (rowData.is_completed) {
                        $(td).closest('tr').css('background-color', '#f0fdf4');
                    }
                }
            },
            {
                data: 'category_name',
                className: 'center aligned'
            },
            {
                data: 'image_file_name',
                className: 'center aligned',
                render: function(data) {
                    if (data)
                        return `<img src="/dpm/dtoconfigurator/partials/getNoteImages.php?type=5&file=${data}" class="enlargeable-image" style="width:125px;object-fit:cover;margin:0 auto;cursor:pointer;">`;
                    return '';
                },
            },
            {
                data: 'completed_date',
                className: 'center aligned',
                render: function(data, type, row) {
                    if (data && row.is_completed) {
                        return moment(data).format('DD.MM.YYYY HH:mm');
                    }
                    return '-';
                }
            },
            {
                data: null,
                className: 'center aligned',
                render: function(data, type, row) {
                    if (row.is_completed) {
                        return `<button class="ui icon button circular red" data-tooltip="Undo" data-position="top center" onclick="toggleChecklistItem(${row.id})" style="font-size:13px;">
                                    <i class="undo icon"></i>
                                </button>`;
                    } else {
                        return `<button class="ui icon button circular green" data-tooltip="Complete" data-position="top center" onclick="toggleChecklistItem(${row.id})" style="font-size:13px;">
                                    <i class="check icon"></i>
                                </button>`;
                    }
                }
            }
        ],
        rowCallback: function(row, data) {
            if (data.is_completed) {
                $(row).css('background-color', '#f0fdf4');
            }
        }
    });

    $('.dt-search input[type="search"]').attr('placeholder', 'Search...').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
}

async function toggleChecklistItem(checklistItemId) {
    try {
        const response = await axios.post('/dpm/dtoconfigurator/api/controllers/ChecklistController.php?',
            { action: 'toggleChecklistItem', projectId: globalProjectId, checklistItemId: checklistItemId },
            { headers: { 'Content-Type': 'multipart/form-data' }}
        );

        if (response.data.success) {
            loadProjectChecklistItems();
            fireToastr('success', response.data.message || 'Checklist item updated');
        } else {
            fireToastr('error', response.data.message || 'Failed to update item');
        }
    } catch (error) {
        console.error('Error toggling checklist item:', error);
        fireToastr('error', 'Error updating checklist item');
    }
}


function updateProgressStats(checklistItems) {
    const total = checklistItems.length;
    const completed = checklistItems.filter(item => item.is_completed).length;
    const percentage = total > 0 ? Math.round((completed / total) * 100) : 0;

    $('#totalCount').text(total);
    $('#completedCount').text(completed);

    $('#checklistProgress').progress({
        percent: percentage
    });
}

function showImageModal(imageSrc, detail) {
    $('#modalImage').attr('src', imageSrc);
    $('#imageModal').modal('show');
}
