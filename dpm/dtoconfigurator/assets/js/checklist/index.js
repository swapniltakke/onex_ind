$(document).ready(async function() {
    $('#checklistIndexPage .loader').hide();
    $('#checklistIndexPageContainer').transition('pulse');

    await initializeChecklistDataTable();
    initializeFilters();
});

let table;
let allChecklistItems = [];

async function initializeChecklistDataTable() {
    const checklistItems = await getChecklistItems();
    allChecklistItems = checklistItems;
    const tableId = '#checklistTable';

    if ($.fn.DataTable.isDataTable(tableId)) {
        $(tableId).DataTable().destroy();
    }

    if (checklistItems.length === 0) {
        $('#checklistTable').hide();
        return;
    }

    table = $(tableId).DataTable({
        data: checklistItems,
        autoWidth: false,
        paging: true,
        pageLength: 10,
        destroy: true,
        order: [],
        columnDefs: [
            { width: '35%', targets: [1] },
        ],
        columns: [
            {
                data: 'checklist_item_id',
                className: 'center aligned',
                render: (data) => `<b>${data}</b>`
            },
            {
                data: 'checklist_detail_html',
                className: 'center aligned',
                render: function(data, type, row) {
                    if (type === 'display') {
                        return $('<textarea>').html(data).text();
                    }
                    return data;
                }
            },
            {
                data: 'category_name',
                className: 'center aligned'
            },
            {
                data: null,
                className: 'center aligned',
                render: function(data, type, row) {
                    return getProductStatus(row, 1); // NXAIR 1C
                }
            },
            {
                data: null,
                className: 'center aligned',
                render: function(data, type, row) {
                    return getProductStatus(row, 2); // NXAIR 50kA
                }
            },
            {
                data: null,
                className: 'center aligned',
                render: function(data, type, row) {
                    return getProductStatus(row, 3); // NXAIR H
                }
            },
            {
                data: null,
                className: 'center aligned',
                render: function(data, type, row) {
                    return getProductStatus(row, 4); // NXAIR World
                }
            },
            {
                data: null,
                className: 'center aligned',
                render: function(data, type, row) {
                    return getProductStatus(row, 5); // NXAIR
                }
            },
            {
                data: 'image_file_name',
                className: 'center aligned',
                render: function(data) {
                    if (data)
                        return `<img src="/dpm/dtoconfigurator/partials/getNoteImages.php?type=5&file=${data}" class="enlargeable-image" style="width:50px;height:40px;margin:0 auto;cursor:pointer;">`;
                    return '';
                },
            },
            {
                data: null,
                render: (data, type, row) =>
                    `<div class="ui mini buttons">
                        <button class="ui icon button circular mini blue" data-tooltip="Edit" data-position="top center" 
                                onclick="window.open('/dpm/dtoconfigurator/core/checklist/edit-checklist-item.php?id=${row.checklist_item_id}', '_blank')" 
                                style="font-size:9px;">
                            <i class="edit large icon"></i>
                        </button>
                        <button class="ui icon button circular mini red" data-tooltip="Delete" data-position="top center" 
                                onclick="deleteChecklistItem(${row.checklist_item_id})" style="font-size:9px;">
                            <i class="trash alternate large icon"></i>
                        </button>
                    </div>`,
                className: 'center aligned'
            }
        ],
        initComplete: function() {
            const api = this.api();
            setTimeout(() => {
                api.draw(false);
            }, 50);
        }
    });

    // Style the search input
    $('.dt-search input[type="search"]').attr('placeholder', 'Search...').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
    $('.dt-column-order').css('display', 'none');
    $('.dt-input').css('border-radius', '4px');

    // Populate category filter dropdown
    populateCategoryFilter();
}

function initializeFilters() {
    // Initialize Semantic UI multiple selection dropdowns
    $('#categoryFilter').dropdown({
        onChange: function(value, text, $choice) {
            applyFilters();
        },
        message: {
            maxSelections: 'Max {maxCount} selections',
            noResults: 'No categories found'
        }
    });

    $('#productFilter').dropdown({
        onChange: function(value, text, $choice) {
            applyFilters();
        },
        message: {
            maxSelections: 'Max {maxCount} selections',
            noResults: 'No products found'
        }
    });

    // Clear filters button
    $('#clearFilters').click(function() {
        $('#categoryFilter').dropdown('clear');
        $('#productFilter').dropdown('clear');
        applyFilters();
    });
}

function populateCategoryFilter() {
    const categories = [...new Set(allChecklistItems.map(item => item.category_name))].sort();

    const categoryMenu = $('#categoryFilter .menu');

    categoryMenu.empty();

    categories.forEach(category => {
        if (category) {
            categoryMenu.append(`<div class="item" data-value="${category}">${category}</div>`);
        }
    });
}

function applyFilters() {
    const selectedCategories = $('#categoryFilter').dropdown('get value');
    const selectedProducts = $('#productFilter').dropdown('get value');

    let filteredData = allChecklistItems;

    // Apply category filter (OR logic - show items that match ANY selected category)
    if (selectedCategories && selectedCategories.length > 0) {
        const categoryArray = Array.isArray(selectedCategories) ? selectedCategories : selectedCategories.split(',');
        filteredData = filteredData.filter(item =>
            categoryArray.includes(item.category_name)
        );
    }

    // Apply product filter (OR logic - show items that are assigned to ANY selected product)
    if (selectedProducts && selectedProducts.length > 0) {
        const productArray = Array.isArray(selectedProducts) ? selectedProducts : selectedProducts.split(',');
        filteredData = filteredData.filter(item => {
            const products = item.products || [];
            return productArray.some(selectedProductId =>
                products.some(p => p.product_id == selectedProductId)
            );
        });
    }

    table.clear();
    table.rows.add(filteredData);
    table.draw();

    updateFilterCount(filteredData.length, allChecklistItems.length);
}

function updateFilterCount(filteredCount, totalCount) {
    const filterCountElement = $('#filterCount');
    const filterCountText = $('#filterCountText');

    if (filteredCount === totalCount) {
        filterCountElement.hide();
    } else {
        filterCountText.text(filteredCount);
        filterCountElement.show();
    }
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

async function getChecklistItems() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/ChecklistController.php', {
            params: { action: 'getChecklistItems'}
        });
        return response.data || [];
    } catch (error) {
        console.error('Error fetching checklist items:', error);
        return [];
    }
}

async function deleteChecklistItem(id) {
    showConfirmationDialog({
        title: 'Remove Checklist Item?',
        htmlContent: 'Are you sure to delete this item?',
        confirmButtonText: 'Yes, delete it!',
        confirmButtonColor: "#d33",
        onConfirm: async function () {
            try {
                await axios.post('/dpm/dtoconfigurator/api/controllers/ChecklistController.php?',
                    {
                        action: 'deleteChecklistItem',
                        id: id
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                showSuccessDialog('Checklist item has been removed from database.').then(() => {
                    $('#deleteModal').modal('hide');
                    initializeChecklistDataTable();
                });
            } catch (error) {
                showErrorDialog('Failed to delete entry. Try again.');
            }
        },
    });
};
