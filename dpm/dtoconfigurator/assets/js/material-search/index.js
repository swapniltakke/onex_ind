$(document).ready(function() {
    $('#materialSearchPage .loader').hide();
    $('#materialSearchContainer').transition('zoom');

    initializeSearchMaterialDropdown();
});

async function initializeSearchMaterialDropdown() {
    $('#materialSearch').dropdown({
        apiSettings: {
            url: `/dpm/dtoconfigurator/api/controllers/MaterialController.php?action=getMaterialsBySearch&keyword={query}`,
            cache: false,
            onResponse: function(response) {
                const materials = Array.isArray(response) ? response : Object.values(response);

                const results = materials.map(material => {
                    return {
                        name: `<b>${material.material_number}</b> - ${material.description}`,
                        value: material.id
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
        allowAdditions: false
    });
}

$('#btnMaterialSearch').on('click', async function() {
    $('#btnMaterialSearch').addClass('loading disabled');
    $('#table-loader').show();
    $('#materialSearchErrMsg').hide(); $('#materialSearchNotFoundMsg').hide();

    const materialId = $('#materialSearch').val();
    if(!materialId) {
        $('#materialSearchErrMsg').transition('pulse');
        return;
    }

    try {
        const url = `/dpm/dtoconfigurator/api/controllers/MaterialController.php?action=searchMaterialDtoData&materialId=${materialId}`;
        const response = await axios.get(url, { headers: { "Content-Type": "multipart/form-data" } });

        if (response.status === 200) {
            const materialResponseData = response.data;

            if (materialResponseData.length === 0) {
                $('#materialSearchListTableContainer').hide();
                $('#materialSearchNotFoundMsg').transition('pulse');
            } else {
                $('#materialSearchListTableContainer').show();
                await fillMaterialSearchTable(materialResponseData);
            }
        }
    } catch (error) {
        fireToastr('error', 'An unexpected error has occurred. Please try again later.');
        console.error('Error:', error);
    } finally {
        $('#btnMaterialSearch').removeClass('loading disabled');
        $('#table-loader').hide();
    }
});

async function fillMaterialSearchTable(materialResponseData) {

    $('#materialSearchListTable').DataTable({
        data: materialResponseData,
        pageLength: 25,
        autoWidth: false,
        order: [[0, 'desc']],
        fixedHeader:true,
        paging:true,
        columnDefs: [
            { width: '8%', targets: 0, className: 'center aligned' },
            { width: '10%', targets: 1, className: 'center aligned' },
            { width: '25%', targets: 2, className: 'center aligned line-height-15' },
            { width: '11%', targets: [3,5], className: 'center aligned' },
            { width: '15%', targets: [4,6], className: 'center aligned line-height-15' },
            { width: '2%', targets: 7, className: 'center aligned' },
        ],
        columns: [
            { data: function (row) {
                    return `<span class="display-none">${row.id}</span>
                            <span class="u-pointer" onclick="openTkFormDetailsPage('${row.id}', '${row.document_number}', '${row.dto_number}')"
                                  data-tooltip="Navigate to TK Form information page" data-position="top left">
                                ${row.document_number} <i class="arrow alternate circle right outline icon"></i>
                            </span>`;
             }
            },
            { data: function (row) {
                    return `<span class="display-none">${row.id}</span>
                            <span class="u-pointer" onclick="openTkFormDetailsPage('${row.id}', '${row.document_number}', '${row.dto_number}')"
                                   data-tooltip="Navigate to TK Form information page" data-position="top left">
                                ${row.dto_number} <i class="arrow alternate circle right outline icon"></i>
                            </span>`;
             }
            },
            { data: 'description' },
            { data: 'added_material' },
            { data: 'added_material_description' },
            { data: 'deleted_material' },
            { data: 'deleted_material_description' },
            {
                data: function(data) {
                    if (data.acc) {
                        return `<div class="ui icon violet button mini" 
                                     data-tooltip="${data.acc}" data-position="top right" data-inverted="">
                                   Note
                                </div>`;
                    }
                    return '';
                }
            }
        ],
        destroy: true
    });

    const $searchInput = $('.dt-search input[type="search"]');
    $searchInput.attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
}

function openTkFormDetailsPage(id, documentNumber, dtoNumber) {
    window.open(`/dpm/dtoconfigurator/core/tkform/detail/info.php?id=${id}&document-number=${documentNumber}&dto-number=${dtoNumber}`, '_blank');
}