$(document).ready(async function() {
    await initializeOrdersPlanDataTable();
});

async function getOrdersPlanData() {
    try {
        const response = await axios.get('/dpm/dtoconfigurator/api/controllers/OrdersPlanController.php', { params: { action: 'getOrdersPlanData'} });
        return response.data;
    } catch (error) {
        fireToastr('error', 'Error fetching released projects:', error);
    }
}


async function initializeOrdersPlanDataTable() {
    const ordersPlanData = await getOrdersPlanData();
    const tableId = '#ordersPlanTable';

    if ($.fn.DataTable.isDataTable(tableId))
        $(tableId).DataTable().destroy();

    if ($('#ordersPlanTableContainer').hasClass('hidden'))
        $('#ordersPlanTableContainer').removeClass('hidden');

    if (ordersPlanData.length === 0) {
        $('#ordersPlanTableContainer').hide();
    }
    else {
        const dataTable = $(tableId).DataTable({
            data: ordersPlanData,
            autoWidth: false,
            searching: true,
            paging: true,
            pageLength: 25,
            destroy: true,
            order: [],
            columns: [
                {
                    data: 'FactoryNumber',
                    width: '150px',
                    render: (data) => `<a href="/dpm/dtoconfigurator/core/projects/detail/info.php?project-no=${data}" target="_blank">${data}</a>`,
                    className: 'center aligned'
                },
                {
                    data: 'ProjectName',
                    width: '200px',
                    className: 'center aligned'
                },
                {
                    data: 'SubProduct',
                    width: '150px',
                    className: 'center aligned'
                },
                {
                    data: 'Qty',
                    width: '100px',
                    className: 'center aligned'
                },
                {
                    data: 'OM',
                    width: '80px',
                    className: 'center aligned'
                },
                {
                    data: 'ME',
                    width: '80px',
                    className: 'center aligned'
                },
            ],
            initComplete: function() {
                // To solve pagination issue
                const api = this.api();
                setTimeout(() => {
                    api.draw(false);
                }, 50);
            }
        });

        $('#ordersPlanPageContainer .loader').hide();
        $('#ordersPlanTableContainer').show();

        $('.dt-search input[type="search"]').attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
        $('.dt-column-order').css('display', 'none');
        $('.dt-input').css('border-radius', '50px');
    }
}