$(document).ready(async function() {
    const projectNo = getUrlParam('project-no');

    Swal.fire({
        title: "Lütfen bekleyin...",
        html: `Banfomat exceli önizlemesi hazırlanıyor. <br> Bakırların kaplama bilgileri hesaplanıyor...`,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    await renderProjectBanfomatInfoDataTable(projectNo);

    Swal.close();
});

async function renderProjectBanfomatInfoDataTable(projectNo) {
    const tableId = '#projectBanfomatInfoListDataTable';
    const data = await getProjectBanfomatDetails(projectNo);
    const banfomatMaterialsOfProject = data.banfomatMaterialsOfProject;

    hideElement('#projectBanfomatCoatTypeInfo');
    hideElement('#projectBanfomatInfoTableContainer');
    showLoader('#projectBanfomatInfoPage');

    if (data.totalSilverCoatedArea > 0) {
        $('#silverCoatedTypeLabel').text('Gümüş');
        $('#totalSilverCoatedArea').text(data.totalSilverCoatedArea);
        showElement('#silverCoatedTypeDiv');
    }
    if (data.totalTinCoatedArea > 0) {
        $('#tinCoatedTypeLabel').text('Kalay');
        $('#totalTinCoatedArea').text(data.totalTinCoatedArea);
        showElement('#tinCoatedTypeDiv');
    }
    if (data.totalNickelCoatedArea > 0) {
        $('#nickelCoatedTypeLabel').text('Nikel');
        $('#totalNickelCoatedArea').text(data.totalNickelCoatedArea);
        showElement('#nickelCoatedTypeDiv');
    }

    createLotFilterDropdown(banfomatMaterialsOfProject);

    let coatingText = data.coatingTypeDto;
    $('#projectBanfomatCoatTypeInfo .coatTypeSpan').text(coatingText);

    if ($.fn.DataTable.isDataTable(tableId))
        $(tableId).DataTable().destroy();

    if (banfomatMaterialsOfProject.length === 0)
        $('#projectBanfomatInfoTableContainer').hide()
    else {
        const projectBanfomatInfoTable = $(tableId).DataTable({
            data: banfomatMaterialsOfProject,
            pageLength: 50,
            autoWidth: false,
            order: [],
            fixedHeader:true,
            columnDefs: [
                { width: '9%', targets: [0] },
                { width: '3%', targets: [1] },
                { width: '10%', targets: [2] },
                { width: '3%', targets: [3] },
                { width: '10%', targets: [4,5] },
                { width: '8%', targets: [6] },
                { width: '7%', targets: [7] },
                { width: '18%', targets: [8] },
                { width: '8%', targets: [9] },
                { width: '4%', targets: [10] },
                { width: '8%', targets: [11] },
            ],
            columns: [
                { data: null, render: () => `<span>${projectNo}</span>`, className: 'center aligned' },
                { data: 'Lot', className: 'center aligned' },
                {   data: 'Material',
                    render: (data) => `<a target="_blank" href="/materialviewer/?material=${data}" 
                                           data-tooltip="Navigate to Material Viewer" data-position="top center" data-variation="inverted">
                                           ${data}
                                        </a>`,
                    className: 'center aligned dblclick-cell'
                },
                { data: 'Quantity', className: 'center aligned' },
                {   data: 'SurfaceArea',
                    render: function(data, type, row) {
                        if (row.isExistInPool === false)
                            return `<span style="font-weight:bold;color:red;">Tanımsız</span>`;

                        if(!data || data === 0 || data === '0.0000000' || data === '0')
                            return '';

                        return `<span style="font-weight:bold;">${parseFloat(data).toString()} m²</span>`;
                    },
                    className: 'center aligned',
                },
                {   data: 'TotalSurfaceArea',
                    render: function(data, type, row) {
                        if (row.isExistInPool === false)
                            return `<span style="font-weight:bold;color:red;">Tanımsız</span>`;

                        if(!data || data === 0 || data === '0.0000000' || data === '0')
                            return '';

                        return `<span style="font-weight:bold;">${parseFloat(data).toString()} m²</span>`;
                    },
                    className: 'center aligned',
                },
                {
                    data: 'CoatedType',
                    className: 'center aligned',
                    render: function(data,type,row) {
                        if (row.isExistInPool === false)
                            return `<span style="font-weight:bold;color:red;">Tanımsız</span>`;

                        return data;
                    }
                },
                { data: 'CoatedPart', className: 'center aligned' },
                { data: 'Details', className: 'center aligned' },
                { data: 'SupplyArea', className: 'center aligned' },
                { data: 'MRP', className: 'center aligned' },
                { data: 'ProductionLocation', className: 'center aligned' },
                {   data: 'ImageFileName',
                    render: function(data) {
                        if (data)
                            return `<img src="/dpm/dtoconfigurator/partials/getNoteImages.php?type=4&file=${data}" class="enlargeable-image" style="width:50px;height:40px;margin:0 auto;cursor:pointer;">`;
                        return '';
                    },
                    className: 'center aligned'
                },
            ],
            destroy: true
        });

        const $searchInput = $('.dt-search input[type="search"]');
        $searchInput.attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');

        hideLoader('#projectBanfomatInfoPage');
        showElement('#projectBanfomatCoatTypeInfo');

        $('#projectBanfomatInfoTableContainer').removeClass('transition hidden');

        $('#projectBanfomatInfoTableContainer').transition('zoom', function() {
            requestAnimationFrame(() => {
                projectBanfomatInfoTable.fixedHeader.adjust();
            });
        });

        projectBanfomatInfoTable.draw();

        showElement('#exportExcelBtn');
    }
}


async function getProjectBanfomatDetails(projectNo) {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/BanfomatController.php', {
            params: { action: 'getProjectBanfomatDetails', projectNo: projectNo }
        });

        return response.data;
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`Error: ${errorMessage}`);
    }
}

document.getElementById('exportExcelBtn').addEventListener('click', function() {
    const table = $('#projectBanfomatInfoListDataTable').DataTable();
    $('#selectedLotSelect').dropdown('clear');

    const uniqueLots = new Set();
    table.rows().every(function() {
        const rowData = this.data();
        if (rowData['Lot'] != null) {
            uniqueLots.add(String(rowData['Lot']));
        }
    });

    const lotSelect = $('#selectedLotSelect .menu')
    lotSelect.html('');

    uniqueLots.forEach(lotValue => {
        lotSelect.append(`<div class="item" data-value="${lotValue}">${lotValue}</div>`);
    });

    $('#chooseLotModal .ui.dropdown').dropdown({
        clearable: true,
        allowAdditions: false,
        fullTextSearch: true,
        forceSelection: false,
        selectOnKeydown:false,
        showOnFocus: false,
    });

    $('#chooseLotModal').modal('show');
});

document.getElementById('confirmLotBtn').addEventListener('click', async function(e) {
    e.stopPropagation();
    try {
        const table = $('#projectBanfomatInfoListDataTable').DataTable();

        // 1. Read the selected lot from the <select>
        const selectedLot = $('#selectedLotSelect').dropdown('get value');

        // If the user didn't pick anything, show error
        if (!selectedLot) {
            showErrorDialog('Lütfen bir Lot seçiniz!');
            return;
        }

        // 2. Format lot number to two digits (01, 02, etc.) if desired
        const lotNumber = selectedLot.padStart(2, '0');

        // 3. Gather projectNo from URL param (or hidden input)
        const projectNo = getUrlParam('project-no');

        // 4. Define columns
        const columns = [
            "Sipariş/Liste No",
            "Malzeme",
            "Adet",
            "Birim Alan (m2)",
            "Toplam Alan (m2)",
            "Kaplama Türü ve Kalınlığı",
            "Kaplanacak Bölge",
            "Detay",
            "Normal Şartlarda Parça Üretim Yeri"
        ];

        // 6. Filter & build the array of rows matching the selected Lot
        let hasUndefinedPool = false;
        const tableData = [];

        table.rows().every(function() {
            const rowData = this.data();

            if (!rowData['Lot'].split(',').map(s => s.trim()).includes(String(selectedLot))) {
                return;
            }

            // Check isExistInPool
            if (rowData['isExistInPool'] === false) {
                hasUndefinedPool = true;
            } else {
                tableData.push({
                    Id                 : rowData['Id'],
                    OrderListNo        : projectNo,
                    Material           : rowData['Material'],
                    Quantity           : rowData['Quantity'],
                    SurfaceArea        : rowData['SurfaceArea'] ?? '',
                    TotalSurfaceArea   : rowData['TotalSurfaceArea'] ?? '',
                    Metal              : rowData['Metal'],
                    CoatedType         : rowData['CoatedType'],
                    CoatedPart         : rowData['CoatedPart'],
                    Details            : rowData['Details'],
                    ProductionLocation : rowData['ProductionLocation'],
                    ImageFileName      : rowData['ImageFileName']
                });
            }
        });

        // If no rows found
        if (tableData.length === 0) {
            showErrorDialog("Bu lot numarası için veri bulunamadı.");
            return;
        }

        if (hasUndefinedPool) {
            showErrorDialog("Lütfen tanımsız malzemeleri banfomat havuzuna ekleyin.");
            return;
        }

        // 7. Generate filename: e.g. "7024054573_01_BAKIR.xlsx"
        const excelFileName = `Banfomat_${projectNo}_${lotNumber}_BAKIR.xlsx`;
        const downloadRequestFromPage = 'projectBanfomatInfoPage';
        const params = new URLSearchParams();
        params.append('action', 'exportBanfomatToExcel');
        params.append('projectNo', projectNo);
        params.append('selectedLot', selectedLot);
        params.append('columns', JSON.stringify(columns));
        params.append('excelFileName', excelFileName);
        params.append('downloadRequestFromPage', downloadRequestFromPage);
        params.append('data', JSON.stringify(tableData));

        // 8. Send the POST request as usual
        try {
            const response = await axios.post(
                '/dpm/dtoconfigurator/api/controllers/BanfomatController.php?',
                params,
                { responseType: 'blob' }
            );

            // 9. Download
            const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = excelFileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // 10. Hide the modal
            $('#chooseLotModal').modal('hide');

        } catch (error) {
            showErrorDialog(`<b>${projectNo}</b> siparişinin <b>Lot ${selectedLot}</b> ine ait banfomat exceli zaten oluşturulmuş. <br><br> Lütfen görüntülemek için <b>Banfomat Geçmişi</b> sayfasına göz atın.`);
        }
    } catch (error) {
        console.error("Export failed:", error);
    }
});

$('#projectBanfomatInfoListDataTable').on('dblclick', '.dblclick-cell', function () {
    let copiedText = $(this).text().trim();
    navigator.clipboard.writeText(copiedText).then(() => {
        fireToastr('success', `${copiedText} copied to clipboard.`);
    }).catch(err => {
        console.error('Failed to copy text: ', err);
        fireToastr('error', 'Failed to copy text. Please try again.');
    });
});


function createLotFilterDropdown(banfomatMaterialsOfProject) {
    // Extract unique Lot values
    const uniqueLots = [...new Set(banfomatMaterialsOfProject.map(item => item.Lot))].sort();

    // Populate Lot Filter Dropdown
    const $lotDropdown = $('#lotFilterDropdown');
    $lotDropdown.empty();
    uniqueLots.forEach(lot => {
        $lotDropdown.append(`<option value="${lot}">Lot ${lot}</option>`);
    });

    $lotDropdown.dropdown({
        clearable: true,
        placeholder: 'Lot Seçiniz...',
        onChange: function(selectedLots) {
            // Filter DataTable By Lot
            const table = $('#projectBanfomatInfoListDataTable').DataTable();
            if (selectedLots.length === 0) {
                table.column(1).search('').draw(); // Clear filter
            } else {
                const regexPattern = selectedLots.map(lot => `^${lot}$`).join('|'); // Exact match regex
                table.column(1).search(regexPattern, true, false).draw();
            }
        }
    });
}
