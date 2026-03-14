document.addEventListener("DOMContentLoaded", function () {
    saveLog("log_reports", "All List Page Access: Datatable is loaded");
    localStorage.removeItem('DataTables_table_all_items_/dpm/reports/allReports.php');

    new FilterFields('table_all_items');
    let workstationFilter = new Filter('workstation', 'table_all_items', 1);
    let productNameFilter = new Filter('productName', 'table_all_items', 2);
    let userFilter = new Filter('user', 'table_all_items', 8);
    let salesOrderNoFilter = new Filter('salesOrderNo', 'table_all_items', 5);
    let mlfbNoFilter = new Filter('mlfbNo', 'table_all_items', 4);
    let workstationFilterOptions = {};
    let workstationFilterNextValue = 1;
    let mlfbFilterOptions = {};
    let mlfbFilterNextValue = 1;

    let filterArray = [workstationFilter, productNameFilter, mlfbNoFilter, salesOrderNoFilter, userFilter];
    new FilterInitial("table_all_items", filterArray);
    filterArray.forEach(filter => {
        filter.init();
    });
    
    $('#mlfbNoFilter').parent().css('width', '180%');

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
            sortDropdown("#workstationFilter");
            sortDropdown("#productNameFilter");
            sortDropdown("#userFilter");
            // sortDropdown("#salesOrderNoFilter");
            // sortDropdown("#mlfbNoFilter");
            // Additional refresh methods
            $('#table_all_items').DataTable().draw();
        }, 500);
    });

    $('#table_all_items').DataTable({
        "dom": 'Blfrtip',
        "pageLength": 20,  // ← CHANGE THIS to 20
        "scrollX": false,
        "autoWidth": false,
        'responsive': false,
        ajax: {
            url: 'api/reportsAPI.php?workstation=' + 'all' + '&action=' + 'allList',
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
            $('#table_all_items th').css({'min-width': '80px'});
            
            // Style Excel button - Green with clear icon
            $('.dt-button.buttons-excel.buttons-html5')
                .addClass('ui white button')
                .css({
                    'background-color': '#28a745',
                    'color': 'white',
                    'border': '1px solid #28a745',
                    'font-weight': '600',
                    'padding': '8px 12px',
                    'border-radius': '4px',
                    'cursor': 'pointer',
                    'transition': 'all 0.3s ease'
                })
                .html('<i class="file excel outline icon" style="margin-right: 5px;"></i>Excel')
                .on('mouseenter', function() {
                    $(this).css({
                        'background-color': '#218838',
                        'border-color': '#218838'
                    });
                })
                .on('mouseleave', function() {
                    $(this).css({
                        'background-color': '#28a745',
                        'border-color': '#28a745'
                    });
                });
            
            // Style Copy button - Red with copy icon
            $('.dt-button.buttons-copy.buttons-html5')
                .addClass('ui white button')
                .css({
                    'background-color': '#dc3545',
                    'color': 'white',
                    'border': '1px solid #dc3545',
                    'font-weight': '600',
                    'padding': '8px 12px',
                    'border-radius': '4px',
                    'cursor': 'pointer',
                    'transition': 'all 0.3s ease'
                })
                .html('<i class="copy icon" style="margin-right: 5px;"></i>Copy')
                .on('mouseenter', function() {
                    $(this).css({
                        'background-color': '#c82333',
                        'border-color': '#c82333'
                    });
                })
                .on('mouseleave', function() {
                    $(this).css({
                        'background-color': '#dc3545',
                        'border-color': '#dc3545'
                    });
                });
            
            $("#table_all_items_formFields").css('margin-bottom', "2%");
            $("#span_close_status_note_count").text(json.closeNoteStatusCount);
            $("#span_open_status_note_count").text(json.openNoteStatusCount);
            $("#mai_spinner_page").removeClass('active');
        },
        columns: [
            { // 0
                className: "ta-c",
                data: "sr_no"
            },
            { // 1
                className: "ta-c",
                data: function (data, type, row) {
                    let workstationName = data.workstation;
                    let workstationUniqueNumber = data.workstationUniqueNumber;
            
                    if (!workstationFilterOptions.hasOwnProperty(workstationUniqueNumber)) {
                        workstationFilterOptions[workstationUniqueNumber] = workstationFilterNextValue++;
                        $("#workstationFilter").append(new Option(workstationName, workstationUniqueNumber));
                    }
                    return { workstationName, workstationUniqueNumber };
                },
                render: function(data, type, row) {
                    return `<span>${data.workstationName} <span style="display: none;">${data.workstationUniqueNumber}</sapn></span>`;
                }
            },
            { // 2
                className: "ta-c",
                data: function (data, type, row) {
                    if (!productNameFilter.data.includes(data.productName)) {
                        productNameFilter.data.push(data.productName);
                        $("#productNameFilter").append(new Option(data.productName, data.productName));
                    }
                    return `<span>${data.productName}</span>`
                }
            },
            { // 3
                className: "ta-c",
                data: "serialno"
            },
            { // 4
                className: "ta-c",
                data: function (data, type, row) {
                    let mlfbNoName = data.mlfbNo;
                    let mlfbNoUniqueNumber = data.mlfbNoUniqueNumber;
            
                    if (!mlfbFilterOptions.hasOwnProperty(mlfbNoUniqueNumber)) {
                        mlfbFilterOptions[mlfbNoUniqueNumber] = mlfbFilterNextValue++;
                        $("#mlfbNoFilter").append(new Option(mlfbNoName, mlfbNoUniqueNumber));
                    }
                    return { mlfbNoName, mlfbNoUniqueNumber };
                },
                render: function(data, type, row) {
                    return `<span>${data.mlfbNoName} <span style="display: none;">${data.mlfbNoUniqueNumber}</sapn></span>`;
                }
            },
            { // 5
                className: "ta-c",
                data: function (data, type, row) {
                    if (!salesOrderNoFilter.data.includes(data.salesOrderNo)) {
                        salesOrderNoFilter.data.push(data.salesOrderNo);
                        $("#salesOrderNoFilter").append(new Option(data.salesOrderNo, data.salesOrderNo));
                    }
                    return `<span>${data.salesOrderNo}</span>`
                }
            },
            { // 6
                className: "ta-c",
                data: "itemNo"
            },
            { // 7
                className: "ta-c",
                data: "productionOrderNo"
            },
            { // 8
                className: "ta-c",
                data: function (data, type, row) {
                    if (!userFilter.data.includes(data.user)) {
                        userFilter.data.push(data.user);
                        $("#userFilter").append(new Option(data.user, data.user));
                    }
                    return `<span>${data.user}</span>`
                }
            },
            { // 9
                className: "ta-c",
                data: "startDateTime"
            },
            { // 10
                className: "ta-c",
                data: "endDateTime"
            }
        ],
        buttons: [{
            extend: 'excelHtml5',
            autoFilter: true
        },
            {
                extend: 'copy',
                text: 'Copy',
            }
        ],
        "drawCallback": function (settings) {
            if (settings.json) {
                $("#span_close_status_note_count").text(settings.json.closeNoteStatusCount);
                $("#span_open_status_note_count").text(settings.json.openNoteStatusCount);
            }
            $("#mai_spinner_page").removeClass('active');
        }
    });
});

function filterOpenListByDate() {
    $("#span_close_status_note_count").text("");
    $("#span_open_status_note_count").text("");
    $("#mai_spinner_page").addClass('active');
    let dates = $('#reportrange span').html().split(' / ');
    let start_date = dates[0];
    let finish_date = dates[1];
    let url_string = "api/reportsAPI.php?workstation=all&action=allList&start_date=" + start_date + "&finish_date=" + finish_date;
    $('#table_all_items').DataTable().ajax.url(url_string);
    $('#table_all_items').DataTable().ajax.reload()
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
});