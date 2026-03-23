$(document).ready(async function() {
    $('#banfomatHistoryPage .loader').hide();
    $('#banfomatHistoryContainer').transition('zoom');

    await renderBanfomatHistoryDataTable();
});

async function renderBanfomatHistoryDataTable() {
    const tableId = '#banfomatHistoryDataTable';
    const data = await fetchAllBanfomatHistoryData();

    hideElement('#banfomatHistoryNotFoundMsg');
    hideElement('#banfomatHistoryTableContainer');

    // prepareDropdownFilters(data);

    if ($.fn.DataTable.isDataTable(tableId))
        $(tableId).DataTable().destroy();

    if (data.length === 0) {
        showElement('#banfomatHistoryNotFoundMsg');
    }
    else {
        const banfomatHistoryTable = $(tableId).DataTable({
            data: data,
            pageLength: 25,
            autoWidth: false,
            fixedHeader:true,
            columns: [
                { data: 'order_no', className: 'center aligned' },
                { data: 'lot', className: 'center aligned' },
                { data: 'metal', className: 'center aligned' },
                {
                    data: 'total_silver_coated_area',
                    render: function(data) {
                        if (!data || parseFloat(data) === 0)
                            return '';
                        return `<span style="font-weight:bold;">${parseFloat(data).toString()} m²</span>`;
                    },
                    className: 'center aligned',
                },
                {
                    data: 'total_tin_coated_area',
                    render: function(data) {
                        if (!data || parseFloat(data) === 0)
                            return '';
                        return `<span style="font-weight:bold;">${parseFloat(data).toString()} m²</span>`;
                    },
                    className: 'center aligned',
                },
                {
                    data: 'total_nickel_coated_area',
                    render: function(data) {
                        if (!data || parseFloat(data) === 0)
                            return '';
                        return `<span style="font-weight:bold;">${parseFloat(data).toString()} m²</span>`;
                    },
                    className: 'center aligned',
                },
                { data: 'excel_file_name', className: 'center aligned' },
                { data: 'created_by', className: 'center aligned' },
                { data: 'created', className: 'center aligned' },
                {
                    render: (data, type, row) =>
                        `<button class="ui icon button circular mini blue" data-tooltip="Excel İndir" data-position="top center" onclick="downloadBanfomatExcel('${row.id}')">
                            <i class="download large icon"></i>
                        </button>
                        <button class="ui icon button circular mini red" data-tooltip="Sil" data-position="top center" onclick="deleteBanfomatHistoryRow('${row.order_no}','${row.lot}')">
                            <i class="trash alternate large icon"></i>
                        </button>`,
                    className: 'center aligned'
                }
            ],
            destroy: true
        });

        //Search customization
        const $searchInput = $('.dt-search input[type="search"]');
        $searchInput.attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');

        $('#banfomatHistoryTableContainer').transition('zoom', function() {
            requestAnimationFrame(() => {
                banfomatHistoryTable.fixedHeader.adjust();
            });
        });

        banfomatHistoryTable.draw();
    }
}

async function fetchAllBanfomatHistoryData() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/BanfomatController.php', {
            params: { action: 'getAllBanfomatHistoryData' }
        });

        return response.data;
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`<b>${errorMessage}</b>`);
    }
}

function deleteBanfomatHistoryRow(orderNo, lot) {
    showConfirmationDialog({
        title: 'Silme İşlemi',
        htmlContent: 'Banfomat excelini silmek istediğinizden emin misiniz?',
        confirmButtonText: 'Evet, sil!',
        confirmButtonColor: "#d33",
        onConfirm: async function () {
            try {
                await axios.post('/dpm/dtoconfigurator/api/controllers/BanfomatController.php?',
                    {
                        action: 'deleteBanfomatHistoryRow',
                        orderNo: orderNo,
                        lot: lot
                    },
                    { headers: { 'Content-Type': 'multipart/form-data' }}
                );

                showSuccessDialog('Banfomat geçmişi bilgisi başarıyla silindi.').then(() => {
                    renderBanfomatHistoryDataTable();
                    $('#banfomatHistoryTableContainer').removeClass('transition hidden');
                });
            } catch (error) {
                showErrorDialog('Failed to delete entry. Try again.');
            }
        },
    });
}

async function downloadBanfomatExcel(rowId) {
    $(this).addClass('loading disabled');
    const table = $('#banfomatHistoryDataTable').DataTable();
    const rowData = table.rows().data().toArray().find(row => row.id === rowId);

    //prepare excel request data
    const projectNo = rowData.order_no;
    const selectedLot = rowData.lot;
    const totalSilverCoatedArea = rowData.total_silver_coated_area;
    const totalTinCoatedArea = rowData.total_tin_coated_area;
    const totalNickelCoatedArea = rowData.total_nickel_coated_area;
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
    const excelFileName = rowData.excel_file_name;
    const downloadRequestFromPage = 'historyPage';
    const tableData = await fetchBanfomatExcelDataFromHistory(projectNo, selectedLot);

    showConfirmationDialog({
        title: 'Excel indirme işlemi',
        htmlContent: `<b>${excelFileName}</b> isimli banfomat exceli indirilsin mi?`,
        confirmButtonText: 'İndir',
        confirmButtonColor: "green",
        onConfirm: async function () {
            try {
                const response = await axios.post(
                    '/dpm/dtoconfigurator/api/controllers/BanfomatController.php?',
                    {
                        action: 'exportBanfomatToExcel',
                        projectNo,
                        selectedLot,
                        totalSilverCoatedArea,
                        totalTinCoatedArea,
                        totalNickelCoatedArea,
                        columns,
                        excelFileName,
                        downloadRequestFromPage,
                        data: tableData
                    },
                    { responseType: 'blob', headers: { 'Content-Type': 'multipart/form-data' }}
                );

                // 9. Download
                const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = excelFileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (error) {
                showErrorDialog('Error');
            } finally {
                $(this).removeClass('loading disabled');
            }
        },
    });
}

async function fetchBanfomatExcelDataFromHistory(orderNo, lot) {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/BanfomatController.php', {
            params: { action: 'getBanfomatExcelDataFromHistory', orderNo: orderNo, lot: lot }
        });

        return response.data;
    } catch (error) {
        const errorMessage = error.response?.data?.message || error.message || "An unexpected error occurred. Get Contact with DGT Team!";
        showErrorDialog(`<b>${errorMessage}</b>`);
    }
}
