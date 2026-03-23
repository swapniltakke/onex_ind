$(document).ready(function() {
    $('#materialCostPage .loader').hide();
    $('.materialCostContainer').transition('zoom');

    initializeMaterialCostDropdown();
});

async function initializeMaterialCostDropdown() {
    $('#materialCostSearch').dropdown({
        apiSettings: {
            url: `/dpm/dtoconfigurator/api/controllers/MaterialController.php?action=getMaterialsFrom064&keyword={query}`,
            cache: false,
            onResponse: function(response) {
                const materials = Array.isArray(response) ? response : Object.values(response);
                console.log(materials);

                const results = materials.map(material => {
                    return {
                        name: `<b>${material.Material}</b> - ${material.Description}`,
                        value: material.Material,
                        text: `${material.Material} - ${material.Description}`
                    };
                });

                return { results };
            }
        },
        fields: {
            remoteValues: 'results',
            name: 'name',
            value: 'value'
        },
        minCharacters: 1,
        clearable: true,
        allowAdditions: false,
        maxSelections: 10,
        onAdd: function(addedValue, addedText, $addedChoice) {
            const selectedValues = $('#materialCostSearch').dropdown('get value');

            // Multiple dropdown array döndürür, string değil
            const selectedCount = Array.isArray(selectedValues) ? selectedValues.length :
                (selectedValues ? selectedValues.split(',').filter(v => v).length : 0);

            console.log('Selected count:', selectedCount);

            if (selectedCount > 10) {
                $('#materialCostSearch').dropdown('remove selected', addedValue);
                $('#materialCostMaxLimitMsg').removeClass('hidden').transition('pulse');
                setTimeout(() => {
                    $('#materialCostMaxLimitMsg').addClass('hidden');
                }, 3000);
            } else {
                $('#materialCostMaxLimitMsg').addClass('hidden');
            }
        }
    });
}

$('#btnMaterialCostSearch').on('click', async function() {
    $('#btnMaterialCostSearch').addClass('loading disabled');
    $('#table-loader').show();
    $('#materialCostSearchErrMsg').addClass('hidden');
    $('#materialCostNotFoundMsg').addClass('hidden');
    $('#materialCostMaxLimitMsg').addClass('hidden');

    const selectedMaterials = $('#materialCostSearch').dropdown('get value');

    // Array veya string olabilir, ikisini de handle et
    const materialNumbers = Array.isArray(selectedMaterials) ? selectedMaterials :
        (selectedMaterials ? selectedMaterials.split(',').filter(v => v) : []);

    console.log('Selected materials:', selectedMaterials, 'Material numbers:', materialNumbers);

    if (materialNumbers.length === 0) {
        $('#materialCostSearchErrMsg').removeClass('hidden').transition('pulse');
        $('#btnMaterialCostSearch').removeClass('loading disabled');
        $('#table-loader').hide();
        return;
    }

    if (materialNumbers.length > 10) {
        $('#materialCostMaxLimitMsg').removeClass('hidden').transition('pulse');
        $('#btnMaterialCostSearch').removeClass('loading disabled');
        $('#table-loader').hide();
        return;
    }

    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/MaterialController.php', {
            params: { action: 'getMaterialsCosts', materials: materialNumbers}
        });

        if (response.status === 200) {
            const materialCostData = response.data;

            // Her seçilen malzeme için data oluştur - backend'den gelmeyenler için boş data ekle
            const completeData = materialNumbers.map(material => {
                const found = materialCostData.find(item => item.material === material);
                if (found) {
                    return { ...found, hasCost: true };
                } else {
                    return {
                        material: material,
                        total_cost_euro: null,
                        total_cost_tl: null,
                        euro_to_tl: null,
                        date: null,
                        hasCost: false
                    };
                }
            });

            $('#materialCostListTableContainer').show();
            await fillMaterialCostTable(completeData);
        }
    } catch (error) {
        fireToastr('error', 'An unexpected error has occurred. Please try again later.');
        console.error('Error:', error);
    } finally {
        $('#btnMaterialCostSearch').removeClass('loading disabled');
        $('#table-loader').hide();
    }
});
async function fillMaterialCostTable(materialCostData) {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#materialCostListTable')) {
        $('#materialCostListTable').DataTable().destroy();
    }

    // Calculate totals - sadece cost bilgisi olan malzemeler
    let totalEuro = 0;
    let totalTL = 0;

    materialCostData.forEach(item => {
        if (item.hasCost && item.total_cost_euro !== null && item.total_cost_tl !== null) {
            totalEuro += parseFloat(item.total_cost_euro);
            totalTL += parseFloat(item.total_cost_tl);
        }
    });

    $('#materialCostListTable').DataTable({
        data: materialCostData,
        pageLength: 25,
        autoWidth: false,
        order: [[0, 'asc']],
        fixedHeader: true,
        paging: true,
        columnDefs: [
            { width: '25%', targets: 0, className: 'center aligned' },
            { width: '20%', targets: 1, className: 'center aligned cost-column' },
            { width: '20%', targets: 2, className: 'center aligned cost-column' },
            { width: '20%', targets: 3, className: 'center aligned' },
            { width: '15%', targets: 4, className: 'center aligned' }
        ],
        columns: [
            {
                data: 'material',
                render: (data) => `<a target="_blank" href="/materialviewer/?material=${data}" 
                                       data-tooltip="Navigate to Material Viewer" data-position="top center" data-variation="inverted">
                                       ${data}
                                    </a>`
            },
            {
                data: 'total_cost_euro',
                render: function(data, type, row) {
                    if (data === null || data === undefined) {
                        return '<span style="color: #999;">-</span>';
                    }
                    return `€ ${parseFloat(data).toFixed(2)}`;
                }
            },
            {
                data: 'total_cost_tl',
                render: function(data, type, row) {
                    if (data === null || data === undefined) {
                        return '<span style="color: #999;">-</span>';
                    }
                    return `₺ ${parseFloat(data).toFixed(2)}`;
                }
            },
            {
                data: 'euro_to_tl',
                render: function(data) {
                    if (data === null || data === undefined) {
                        return '<span style="color: #999;">-</span>';
                    }
                    return `€1 = ₺${parseFloat(data).toFixed(4)}`;
                }
            },
            {
                data: 'date',
                render: function(data) {
                    if (data === null || data === undefined) {
                        return '<span style="color: #999;">-</span>';
                    }
                    return data;
                }
            }
        ],
        createdRow: function(row, data, dataIndex) {
            // Cost bilgisi yoksa satırı kırmızı yap
            if (!data.hasCost) {
                $(row).css('background-color', '#fee2e2');
            }
        },
        footerCallback: function(row, data, start, end, display) {
            // Footer'a total değerleri yaz
            $('#totalCostEuro').html(`<strong style="color: #059669; font-size: 15px;">€ ${totalEuro.toFixed(2)}</strong>`);
            $('#totalCostTL').html(`<strong style="color: #059669; font-size: 15px;">₺ ${totalTL.toFixed(2)}</strong>`);
        },
        destroy: true
    });

    const $searchInput = $('.dt-search input[type="search"]');
    $searchInput.attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
}