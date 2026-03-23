async function renderTkFormNotExistDataTable(tkformsNotExistData) {
    const tableId = '#tkformsNotExistDataTable';

    if ($.fn.DataTable.isDataTable(tableId))
        $(tableId).DataTable().destroy();

    if ($('#tkFormsNotExistContainer').hasClass('hidden'))
        $('#tkFormsNotExistContainer').removeClass('hidden');

    if (tkformsNotExistData.length === 0)
        $('#tkFormsNotExistContainer').hide()
    else {
        const table = $('#tkformsNotExistDataTable').DataTable({
            data: tkformsNotExistData,
            pageLength: 10,
            paging:true,
            destroy: true,
            autoWidth: false,
            fixedHeader:true,
            columns: [
                {
                    data: 'dto_number',
                    render: (data, type, row) => `<span style="font-weight:700;">${row.dto_number}</span>`,
                    className: 'center aligned'
                },
                {
                    data: 'description',
                    render: (data, type, row) => shortDescription(row.description, 150),
                    className: 'center aligned'
                },
                {
                    render: () => `<a class="ui inverted blue circular icon compact button" href="/dpm/dtoconfigurator/core/tkform/index.php" target="_blank">
                                       TK Form Page
                                       <i class="external alternate icon"></i>
                                   </a>`,
                    className: 'center aligned'
                }
            ],
            drawCallback: function () {
                requestAnimationFrame(() => {
                    table.fixedHeader.adjust();
                });
            }
        });

        $('#tkFormsNotExistContainer').transition('zoom', function() {
            requestAnimationFrame(() => {
                table.fixedHeader.adjust();
                table.draw();
            });
        });

        //Search customization
        const $searchInput = $('.dt-search input[type="search"]');
        $searchInput.attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
    }
}