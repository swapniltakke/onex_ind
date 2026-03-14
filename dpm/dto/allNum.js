document.addEventListener("DOMContentLoaded", function () {
    saveLog("log_dto", "Open List page access");
    localStorage.removeItem('DataTables_table_open_items_/dpm/dto/numreg_details.php');

    new FilterFields('table_open_items');
    let productNameFilter = new Filter('prdName', 'table_open_items', 2);
    let drwNameFilter = new Filter('drwName', 'table_open_items', 3);
    let matNameFilter = new Filter('matName', 'table_open_items', 4);
    let mainNumberFilter = new Filter('mainNumber', 'table_open_items', 5);
    let startNumberFilter = new Filter('startNumber', 'table_open_items', 6);
    let endNumberFilter = new Filter('endNumber', 'table_open_items', 7);
    let userFilter = new Filter('user', 'table_open_items', 8);
    
    
    let filterArray = [productNameFilter, drwNameFilter, matNameFilter, mainNumberFilter, startNumberFilter, endNumberFilter, userFilter];
    new FilterInitial("table_open_items", filterArray);
    filterArray.forEach(filter => {
        filter.init();
    });
    


    
    function sortDropdown(selectElementId) {
        var $select = $(selectElementId);
        // Remove duplicate options
        var uniqueOptions = {};
        $select.find('option').each(function() {
            var text = $(this).text().trim();
            uniqueOptions[text] = $(this);
        });
        // Sort unique options
        var sortedOptions = Object.keys(uniqueOptions)
            .sort((a, b) => a.localeCompare(b, undefined, {sensitivity: 'base'}))
            .map(key => uniqueOptions[key]);
        
        // Clear and repopulate the select element
        $select.empty().append(sortedOptions);
        // Trigger change to ensure UI updates
        $select.trigger('change');
    }
    
    // Enhanced document ready function
    $(document).ready(function() {
        // Delay to ensure all filters are loaded
        setTimeout(function() {
            // Sort dropdowns
            sortDropdown("#clientFilter");
            // Additional refresh methods
            $('#table_open_items').DataTable().draw();
        }, 500);
    });

    $(document).ready(function() {
        $('#date_filter').css('background-color', '#00646E');
        var table = $('#table_open_items').DataTable({
             "dom": 'Blfrtip',
    "pageLength": 20,
    "scrollX": true,
    "scrollCollapse": true,
    "autoWidth": false,
    "columnDefs": [
        {
            "targets": 0, // Action column
            "width": "100px",
            "orderable": false
        },
        {
            "targets": 1, // Product Name
            "width": "250px"
        },
        {
            "targets": 2, // Drawing Name
            "width": "250px"
        },
        {
            "targets": 3, // Material Name
            "width": "250px"
        },
        {
            "targets": 4, // Main Number
            "width": "250px"
        },
        {
            "targets": 5, // Start Number
            "width": "250px"
        },
        {
            "targets": 6, // End Number
            "width": "0px"
        }
    ],
            ajax: {
                url: 'api/DTOController.php?action=allNum',
                dataSrc: function(json) {
                    // Filter out the rows where isDisplay is not blank
                    return json.data.filter(function(item) {
                        return item;
                    });
                }
            },
            contentType: "application/json",
            "bStateSave": true,
            "stateSave": true,
            lengthMenu: [
                [20, 40, 60, -1],
                [20, 40, 60, "All"],
            ],
            
            "initComplete": function (settings, json) {
                document.querySelector('#detailsegment').setAttribute('style', 'display: block');
                $('#table_open_items th').css({'min-width': '80px'});
                $('.dt-button.buttons-copy.buttons-html5').addClass('ui white button');
                $('.dt-button.buttons-copy.buttons-html5').prepend('<i class="clipboard icon"></i>');
                $('.dt-button.buttons-excel.buttons-html5').addClass('ui white button');
                $('.dt-button.buttons-excel.buttons-html5').prepend('<i class="file excel outline icon"></i>');
                $("#table_open_items_formFields").css('margin-bottom', "2%");
                $("#span_production_order_quantity_count").text(json.ProductionOrderQuantityCount);
                $("#mai_spinner_page").removeClass('active');
            },
            columns: [
                { // 0
                    className: 'details-control',
                    orderable: false,
                    data: null,
                    render: function(data, type, row) {
                        let actionButtonsHtmlContent = `<div class="ui mini buttons" style="flex-direction: column;">`;
                
                        if (data.showCollapse == '1') {
                            actionButtonsHtmlContent += `
                                <button class="ui mini blue button mt-1 details-control">${row.isExpanded ? '-' : '+'}</button>
                            `;
                        } else {
                            actionButtonsHtmlContent += `
                                <button class="ui mini blue button mt-1 details-control" style="display: none;"></button>
                            `;
                        }
                
                        actionButtonsHtmlContent += `
                           <button class="ui mini orange button mt-1" onclick="openUpdateNum(event, ${data.id})">Update</button>
                          <!-- <button class="ui mini negative button mt-1" onclick="openDeleteRequest(${data.dto_id})">Delete</button> -->
                        </div>`;
                
                        return actionButtonsHtmlContent;
                    }
                },
                { // 1
                    visible: false,
                    className: "ta-c",
                    data: "id"
                },
                { // 2
                    className: "ta-c",
                    //data: "prdName"
                    data: function (data, type, row) {
                        if (!productNameFilter.data.includes(data.prdName)) {
                            productNameFilter.data.push(data.prdName);
                            $("#productNameFilter").append(new Option(data.prdName, data.prdName));
                        }
                        return `<span>${data.prdName}</span>`
                    }
                },
                { // 3
                    className: "ta-c",
                    //data: "drwName"
                    data: function (data, type, row) {
                        if (!drwNameFilter.data.includes(data.drwName)) {
                            drwNameFilter.data.push(data.drwName);
                            $("#drwNameFilter").append(new Option(data.drwName, data.drwName));
                        }
                        return `<span>${data.drwName}</span>`
                    }
                },
                { // 4
                    className: "ta-c",
                    //data: "matName"
                    data: function (data, type, row) {
                        if (!matNameFilter.data.includes(data.matName)) {
                            matNameFilter.data.push(data.matName);
                            $("#matNameFilter").append(new Option(data.matName, data.matName));
                        }
                        return `<span>${data.matName}</span>`
                    }
                },
                { // 5
                    className: "ta-c",
                    //data: "mainNumber"
                     data: function (data, type, row) {
                        if (!mainNumberFilter.data.includes(data.mainNumber)) {
                            mainNumberFilter.data.push(data.mainNumber);
                            $("#mainNumberFilter").append(new Option(data.mainNumber, data.mainNumber));
                        }
                        return `<span>${data.mainNumber}</span>`
                    }
                },
                { // 6
                    className: "ta-c",
                    //data: "startNumber"
                    data: function (data, type, row) {
                        if (!startNumberFilter.data.includes(data.startNumber)) {
                            startNumberFilter.data.push(data.startNumber);
                            $("#startNumberFilter").append(new Option(data.startNumber, data.startNumber));
                        }
                        return `<span>${data.startNumber}</span>`
                    }
                },
                { // 7
                    className: "ta-c",
                    //data: "endNumber"
                    data: function (data, type, row) {
                        if (!endNumberFilter.data.includes(data.endNumber)) {
                            endNumberFilter.data.push(data.endNumber);
                            $("#endNumberFilter").append(new Option(data.endNumber, data.endNumber));
                        }
                        return `<span>${data.endNumber}</span>`
                    }
                },
                { // 8
                    className: "ta-c",
                    //data: "user"
                    data: function (data, type, row) {
                        if (!userFilter.data.includes(data.user)) {
                            userFilter.data.push(data.user);
                            $("#userFilter").append(new Option(data.user, data.user));
                        }
                        return `<span>${data.user}</span>`
                    }
                    
                } 
                
            ],
            "order": [[1, 'asc']],
            buttons: [
                {
                    extend: 'excelHtml5',
                    autoFilter: true,
                    exportOptions: {
                        columns: ':not(:first-child)' // exclude the last column (action)
                    }
                },
                {
                    extend: 'copy',
                    text: 'Copy',
                    exportOptions: {
                        columns: ':not(:first-child)' // exclude the last column (action)
                    }
                }
            ],
            "drawCallback": function (settings) {
                if (settings.json) {
                    $("#span_production_order_quantity_count").text(settings.json.ProductionOrderQuantityCount);
                }
                $("#mai_spinner_page").removeClass('active');
            }
        });
        // Add event listener for expanding and collapsing rows
        // Add event listener for expanding and collapsing rows
        
    });
});



function filterAllListByDate() {
    $('#date_filter').css('background-color', '#00646E');
    $('#planned_month_filter').css('background-color', '#00AF8E');
    $("#span_production_order_quantity_count").text("");
    $("#mai_spinner_page").addClass('active');
    let dates = $('#reportrange span').html().split(' / ');
    let start_date = dates[0];
    let finish_date = dates[1];
    //let url_string = "api/DTOController.php?action=allList&start_date=" + start_date + "&finish_date=" + finish_date;
    let url_string = "api/DTOController.php?action=allList&start_date=" + start_date + "&finish_date=" + finish_date;
    $('#table_open_items').DataTable().ajax.url(url_string);
    $('#table_open_items').DataTable().ajax.reload();
}

function filterAllListByPlannedMonth() {
    $('#date_filter').css('background-color', '#00AF8E');
    $('#planned_month_filter').css('background-color', '#00646E');
    $("#span_production_order_quantity_count").text("");
    $("#mai_spinner_page").addClass('active');
    let dates = $('#reportrange span').html().split(' / ');
    let start_date = dates[0];
    let finish_date = dates[1];
    let url_string = "api/DTOController.php?projectNo=all&action=plannedMonthAllList&start_date=" + start_date + "&finish_date=" + finish_date;
    $('#table_open_items').DataTable().ajax.url(url_string);
    $('#table_open_items').DataTable().ajax.reload();
}

$(document).ready(function () {
    $("#mai_spinner_page").addClass('active');
    const start = moment().startOf('month');
    const end = moment();

    function cb(start, end) {
        $('#reportrange span').html(start.format("DD-MM-YYYY") + ' / ' + end.format("DD-MM-YYYY"));
        start_date = moment(start, 'DD-MM-YYYY').format('YYYY-MM-DD');
        finish_date = moment(end, 'DD-MM-YYYY').format('YYYY-MM-DD');
    }

    function cb1(start, end) {
        $('#planned_month_reportrange span').html(start.format("DD-MM-YYYY") + ' / ' + end.format("DD-MM-YYYY"));
        start_date1 = moment(start, 'DD-MM-YYYY').format('YYYY-MM-DD');
        finish_date1 = moment(end, 'DD-MM-YYYY').format('YYYY-MM-DD');
    }

    let thisFiscalYear = new Date().getFullYear();
    const thisMonth = new Date().getMonth();
    if (thisMonth < 9) {
        thisFiscalYear -= 1;
    }
    const thisyear = new Date().getFullYear();
    let lastFiscalYear2;
    let lastFiscalYear1;
    if (thisMonth < 9) {
        lastFiscalYear1 = thisyear - 2;
        lastFiscalYear2 = thisyear - 1;
    }
    $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        showDropdowns: true,
        minYear: 2021,
        maxYear: parseInt(moment().format('YYYY'), 10) + 1,
        alwaysShowCalendars: true,
        autoApply: true,
        //minDate: new Date(2020, 10, 1), // TODO: VERİ TABANI BAĞLANIP EN KÜÇÜK ZAMAN ÇEKİLECEK
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            "Last Year": [moment().subtract(1, "y").startOf("year"), moment().subtract(1, "y").endOf("year")],
            "Last 1 Year": [moment().subtract(365, 'days'), moment()],
            "This Fiscal Year": [new Date(thisFiscalYear, 9, 1), moment()],
            "Last Fiscal Year": [new Date(lastFiscalYear1, 9, 1), new Date(lastFiscalYear2, 9, 1)]
        }
    }, cb);
    cb(start, end);
    $('#planned_month_reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        showDropdowns: true,
        minYear: 2021,
        maxYear: parseInt(moment().format('YYYY'), 10) + 1,
        alwaysShowCalendars: true,
        autoApply: true,
        //minDate: new Date(2020, 10, 1), // TODO: VERİ TABANI BAĞLANIP EN KÜÇÜK ZAMAN ÇEKİLECEK
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            "Last Year": [moment().subtract(1, "y").startOf("year"), moment().subtract(1, "y").endOf("year")],
            "Last 1 Year": [moment().subtract(365, 'days'), moment()],
            "This Fiscal Year": [new Date(thisFiscalYear, 9, 1), moment()],
            "Last Fiscal Year": [new Date(lastFiscalYear1, 9, 1), new Date(lastFiscalYear2, 9, 1)]
        }
    }, cb1);
    cb1(start, end);    

    
 
    
});


