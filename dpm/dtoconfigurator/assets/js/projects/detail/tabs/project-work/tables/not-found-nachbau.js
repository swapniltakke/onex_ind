async function renderNotFoundNachbauDataTable(notFoundNachbauData) {
    const tableId = '#notFoundNachbauDataTable';

    if ($.fn.DataTable.isDataTable(tableId))
        $(tableId).DataTable().destroy();

    if ($('#notFoundNachbauContainer').hasClass('hidden'))
        $('#notFoundNachbauContainer').removeClass('hidden');

    if (notFoundNachbauData.length === 0)
        $('#notFoundNachbauContainer').hide()
    else {
        const table = $(tableId).DataTable({
            data: notFoundNachbauData,
            paging:true,
            destroy: true,
            pageLength: 10,
            autoWidth:false,
            columnDefs: [
                { width: '25%', targets: [0] },
                { width: '10%', targets: [1] },
                { width: '11%', targets: [2,3] },
                { width: '20%', targets: [4] },
                { width: '3%', targets: [5,6] },
                { width: '25%', targets: [7] },
                { width: '5%', targets: [8] },
            ],
            columns: [
                {
                    data: 'error_message_en',
                    render: (data, type, row) => `<span style="font-weight:700;">${data}</span>`,
                    className: 'center aligned'
                },
                {
                    data: 'dto_number',
                    className: 'center aligned',
                    render: (data) => `<a href="#" data-tooltip="Open TK Form Material List" data-position="top center"   
                                          class="dto-link" data-dto-number="${data}"> ${data} </a>`
                },
                {
                    data: 'material_added_number',
                    render: (data, type, row) => {
                        const addedMaterial = `${row.material_added_starts_by}${row.material_added_number}`;
                        let linkStyle = '';
                        let dataTooltip = 'Navigate to Material Viewer';

                        if (row.material_added_sap_defined === '0') {
                            dataTooltip = 'Material not found in SAP system'
                            linkStyle = 'color:red;font-weight:bold;'; // SAP not defined
                            $('#materialNotDefinedInSapMsg').removeClass('hidden');
                        } else if (row.affected_dto_numbers !== '' && row.affected_dto_numbers !== null) {
                            dataTooltip = 'This material list has multiple DTO numbers'
                            linkStyle = 'color:green;font-weight:bold;'; // Affects other DTOs
                            $('#listAffectsOtherDtosMsg').removeClass('hidden');
                        }

                        return `<a target="_blank" href="/materialviewer/?material=${addedMaterial}" 
                                   data-tooltip="${dataTooltip}" data-position="top center" data-variation="inverted" 
                                   style="${linkStyle}">
                                   ${addedMaterial}
                                </a>`;
                    },
                    className: 'center aligned dblclick-cell'
                },
                {
                    data: 'material_deleted_number',
                    render: (data, type, row) => {
                        const deletedMaterial = `${row.material_deleted_starts_by}${row.material_deleted_number}`;
                        let linkStyle = '';
                        let dataTooltip = 'Navigate to Material Viewer';

                        if (row.material_deleted_sap_defined === '0') {
                            dataTooltip = 'Material not found in SAP system'
                            linkStyle = 'color:red;font-weight:bold;'; // Not defined in SAP
                            $('#materialNotDefinedInSapMsg').removeClass('hidden');
                        } else if (row.affected_dto_numbers !== '' && row.affected_dto_numbers !== null) {
                            dataTooltip = 'This material list affects multiple DTO numbers'
                            linkStyle = 'color:green;font-weight:bold;'; // Affects other DTOs
                            $('#listAffectsOtherDtosMsg').removeClass('hidden');
                        }

                        return `<a target="_blank" href="/materialviewer/?material=${deletedMaterial}" 
                                   data-tooltip="${dataTooltip}" data-position="top center" data-variation="inverted" 
                                   style="${linkStyle}">
                                   ${deletedMaterial}
                                </a>`;
                    },
                    className: 'center aligned dblclick-cell'
                },
                {
                    data: 'material_deleted_description',
                    render: (data, type, row) => row.operation === 'add' ? row.material_added_description : row.material_deleted_description,
                    className: 'center aligned'
                },
                {
                    data: 'quantity',
                    render: (data) => parseFloat(data).toString().replace(/\.000$/, ''),
                    className: 'center aligned'
                },
                {
                    data: 'unit',
                    className: 'center aligned'
                },
                {
                    data: 'work_center',
                    render: (data, type, row) => row.work_center !== ''
                        ? `<a class="ui violet label">${row.work_center}</a><br>
                                                    <h5 style="margin-top:2%;">${row.work_content}</h5>`
                        : `<div class="ui red horizontal label">Undefined</div>`,
                    className: 'center aligned'
                },
                {
                    data: 'acc',
                    render: (data, type, row) => {
                        if (!row.affected_dto_numbers && !row.acc)
                            return '';
                        else if (!row.affected_dto_numbers && row.acc) {
                            return `<div class="ui icon teal button mini" 
                                         data-tooltip="${row.acc}" data-position="top right" data-inverted="">
                                       Note
                                    </div>`;
                        }
                        else if (row.affected_dto_numbers && !row.acc) {
                            const urlParams = new URLSearchParams(window.location.search);
                            const dtoNumber = urlParams.get('dto-number');

                            let dtoArray = row.affected_dto_numbers.split("|");
                            dtoArray = dtoArray.filter(item => item !== dtoNumber);
                            const otherDtoNumbers = dtoArray.join(", ");

                            return `<div class="ui icon blue button mini" 
                                         data-tooltip="${otherDtoNumbers}" data-position="top right" data-inverted="">
                                       DTO Group
                                    </div>`;
                        }
                        else {
                            const urlParams = new URLSearchParams(window.location.search);
                            const dtoNumber = urlParams.get('dto-number');

                            let dtoArray = row.affected_dto_numbers.split("|");
                            dtoArray = dtoArray.filter(item => item !== dtoNumber);
                            const otherDtoNumbers = dtoArray.join(", ");

                            return `<div class="ui icon blue button mini" 
                                         data-tooltip="${otherDtoNumbers}" data-position="top right" data-inverted="">
                                       DTO Group
                                    </div>
                                    <div class="ui icon teal button mini" 
                                         data-tooltip="${row.acc}" data-position="top right" data-inverted="">
                                       Note
                                    </div>`;
                        }
                    },
                    className: 'center aligned'
                },
            ]
        });

        $('#notFoundNachbauContainer').transition('zoom', function() {
            requestAnimationFrame(() => {
                table.draw();
            });
        });

        //Search customization
        const $searchInput = $('.dt-search input[type="search"]');
        $searchInput.attr('placeholder', 'Search').wrap('<div class="ui icon input"></div>').after('<i class="search icon"></i>');
    }
}


$('#notFoundNachbauDataTable').on('dblclick', '.dblclick-cell', function () {
    const starters = ['A7E00', 'A7ETKBL', 'A7ET', 'A7E'];
    let copiedText = $(this).text().trim();

    for (const starter of starters) {
        if (copiedText.startsWith(starter)) {
            copiedText = copiedText.slice(starter.length);
            break;
        }
    }

    navigator.clipboard.writeText(copiedText).then(() => {
        fireToastr('success', `${copiedText} copied to clipboard.`);
    }).catch(err => {
        console.error('Failed to copy text: ', err);
        fireToastr('error', 'Failed to copy text. Please try again.');
    });
});
