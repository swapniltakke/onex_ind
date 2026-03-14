document.addEventListener("DOMContentLoaded", function () {
    saveLog("log_breaker", "Open List page access");
    localStorage.removeItem('DataTables_table_open_items_/dpm/breaker/allBreaker.php');

    new FilterFields('table_open_items');
    let planMonthDateFilter = new Filter('planMonthDate', 'table_open_items', 4);
    let salesOrderNoFilter = new Filter('salesOrderNo', 'table_open_items', 5);
    let itemNoFilter = new Filter('itemNo', 'table_open_items', 6);
    let clientFilter = new Filter('client', 'table_open_items', 7);
    let mlfbNoFilter = new Filter('mlfbNo', 'table_open_items', 8);
    let productNameFilter = new Filter('productName', 'table_open_items', 10);
    let productionOrderNoFilter = new Filter('productionOrderNo', 'table_open_items', 19);
    let mlfbFilterOptions = {};
    let mlfbFilterNextValue = 1;

    let filterArray = [planMonthDateFilter, salesOrderNoFilter, itemNoFilter, clientFilter, mlfbNoFilter, productNameFilter, productionOrderNoFilter];
    new FilterInitial("table_open_items", filterArray);
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
            "paging": true,
            "scrollX": false,
            "autoWidth": false,
            'responsive': false,
            ajax: {
                url: 'api/BreakerController.php?projectNo=all&action=allList',
                dataSrc: function(json) {
                    // Filter out the rows where isDisplay is not blank
                    return json.data.filter(function(item) {
                        return item.isDisplay == 1;
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
                            <button class="ui mini orange button mt-1" onClick='openUpdateModal(event, ${data.id})'>Update</button>
                            <button class="ui mini negative button mt-1" onclick="openDeleteRequest(${data.id})">Delete</button>
                        </div>`;
                
                        return actionButtonsHtmlContent;
                    }
                },
                { // 1
                    visible: false,
                    className: "ta-c",
                    data: "srNo"
                },
                { // 2
                    className: "ta-c",
                    data: "groupName"
                },
                { // 3
                    className: "ta-c",
                    data: "cddDate"
                },
                { // 4
                    className: "ta-c",
                    data: function (data, type, row) {
                        if (!planMonthDateFilter.data.includes(data.planMonthDate)) {
                            planMonthDateFilter.data.push(data.planMonthDate);
                            $("#planMonthDateFilter").append(new Option(data.planMonthDate, data.planMonthDate));
                        }
                        return `<span>${data.planMonthDate}</span>`
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
                    data: function (data, type, row) {
                        if (!itemNoFilter.data.includes(data.itemNo)) {
                            itemNoFilter.data.push(data.itemNo);
                            $("#itemNoFilter").append(new Option(data.itemNo, data.itemNo));
                        }
                        return `<span>${data.itemNo}</span>`
                    }
                },
                { // 7
                    className: "ta-c",
                    data: function (data, type, row) {
                        if (!clientFilter.data.includes(data.client)) {
                            clientFilter.data.push(data.client);
                            $("#clientFilter").append(new Option(data.client, data.client));
                        }
                        return `<span>${data.client}</span>`
                    }
                },
                { // 8
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
                { // 9
                    className: "ta-c",
                    data: "rating"
                },
                { // 10
                    className: "ta-c",
                    data: function (data, type, row) {
                        if (!productNameFilter.data.includes(data.productName)) {
                            productNameFilter.data.push(data.productName);
                            $("#productNameFilter").append(new Option(data.productName, data.productName));
                        }
                        return `<span>${data.productName}</span>`
                    }
                },
                { // 11
                    className: "ta-c",
                    data: "width"
                },
                { // 12
                    className: "ta-c",
                    data: "trolleyType"
                },
                { // 13
                    className: "ta-c",
                    data: "trolleyRefair"
                },
                { // 14
                    className: "ta-c",
                    data: "totalQuantity"
                },
                { // 15
                    className: "ta-c",
                    data: "productionOrderQuantity"
                },
                { // 16
                    className: "ta-c",
                    data: "addon"
                },
                { // 17
                    className: "ta-c",
                    data: "serialNo"
                },
                { // 18
                    className: "ta-c",
                    data: "ptdNo"
                },
                { // 19
                    className: "ta-c",
                    data: function (data, type, row) {
                        if (!productionOrderNoFilter.data.includes(data.productionOrderNo)) {
                            productionOrderNoFilter.data.push(data.productionOrderNo);
                            $("#productionOrderNoFilter").append(new Option(data.productionOrderNo, data.productionOrderNo));
                        }
                        return `<span>${data.productionOrderNo}</span>`
                    }
                },
                { // 20
                    className: "ta-c",
                    data: "viType"
                },
                { // 21
                    className: "ta-c",
                    data: "c1Date"
                },
                { // 22
                    className: "ta-c",
                    data: "ciaDate"
                },
                { // 23
                    className: "ta-c",
                    data: "remark"
                },
                { // 24
                    className: "ta-c",
                    data: "createDate"
                },
                { // 25
                    className: "ta-c",
                    data: "currentStatus"
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
        $('#table_open_items tbody').on('click', 'button.details-control', function() {
            var tr = $(this).closest('tr');
            var row = table.row(tr);
            var data = table.row(tr).data();
        
            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
                data.isExpanded = false;
                $(this).text('+');
            } else {
                // Open this row
                row.child(childRows(data, table.ajax.json().data)).show();
                tr.addClass('shown');
                data.isExpanded = true;
                $(this).text('-');
            }
        
            table.cell(tr, 0).data(data).draw();
        });
    });
});

function childRows(row, data) {
    let expandedData = data.filter(item => item.id === row.parentId || (item.parentId === row.parentId && item.id !== row.id));
    console.log(row);
    console.log(data);
    console.log(expandedData);
    let html = '<table class="table table-bordered" style="width: auto;">';
    html += '<tbody>';
    html += '<tr>';
    html += '<th>Sr No.</th>';
    html += '<th>Group</th>';
    html += '<th>CDD Date</th>';
    html += '<th>Plan Month</th>';
    html += '<th>Sales Order No.</th>';
    html += '<th>Item No.</th>';
    html += '<th>Client</th>';
    html += '<th>MLFB</th>';
    html += '<th>Rating</th>';
    html += '<th>Product Name</th>';
    html += '<th>Width</th>';
    html += '<th>Trolley Type Of Siemens</th>';
    html += '<th>Trolley Required For Refair</th>';
    html += '<th>Total Quantity</th>';
    html += '<th>Production Order Quantity</th>';
    html += '<th>Addon</th>';
    html += '<th>Serial No.</th>';
    html += '<th>PTD No.</th>';
    html += '<th>Production Order No</th>';
    html += '<th>VI Type</th>';
    html += '<th>C1 Date</th>';
    html += '<th>CIA Date</th>';
    html += '<th>Remark</th>';
    html += '<th>Create Date</th>';
    html += '<th>Current Status</th>';
    html += '</tr>';
    
    if (expandedData.length > 0) {
        for (let i = 0; i < expandedData.length; i++) {
            html += '<tr>';
            html += '<td style="min-width: 86px;">' + (i + 1) + '</td>'; // Use the index as the Sr No
            html += '<td style="min-width: 80px;">' + expandedData[i].groupName + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].cddDate + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].planMonthDate + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].salesOrderNo + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].itemNo + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].client + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].mlfbNo + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].rating + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].productName + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].width + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].trolleyType + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].trolleyRefair + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].totalQuantity + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].productionOrderQuantity + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].addon + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].serialNo + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].ptdNo + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].productionOrderNo + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].viType + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].c1Date + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].ciaDate + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].remark + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].createDate + '</td>';
            html += '<td style="min-width: 80px;">' + expandedData[i].currentStatus + '</td>';
            html += '</tr>';
        }
    } else {
        console.log('No expanded data available for the current row.');
        html += '<tr><td colspan="15">No expanded data available</td></tr>';
    }
    html += '</tbody>';
    html += '</table>';

    return html;
}

function filterAllListByDate() {
    $('#date_filter').css('background-color', '#00646E');
    $('#planned_month_filter').css('background-color', '#00AF8E');
    $("#span_production_order_quantity_count").text("");
    $("#mai_spinner_page").addClass('active');
    let dates = $('#reportrange span').html().split(' / ');
    let start_date = dates[0];
    let finish_date = dates[1];
    let url_string = "api/BreakerController.php?projectNo=all&action=allList&start_date=" + start_date + "&finish_date=" + finish_date;
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
    let url_string = "api/BreakerController.php?projectNo=all&action=plannedMonthAllList&start_date=" + start_date + "&finish_date=" + finish_date;
    $('#table_open_items').DataTable().ajax.url(url_string);
    $('#table_open_items').DataTable().ajax.reload();
}

const updateReworkTime = function (ecrTime, updateType, id) {
    saveLog("log_breaker", `confirmed changeStatus in allList; ID: ${id}, updateType: ${updateType}`);
    $.ajax({
        async: false,
        url: 'api/BreakerController.php?action=' + updateType + '&reworkItemId=' + id + '&ecrTime=' + ecrTime,
        dataType: "json",
        method: 'POST',
        success: function (data) {
            showNotification('success', 'Saved');
            $('#table_open_items').DataTable().ajax.reload(null, false);
            saveLog("log_breaker", `success on changeStatus in allList; ID: ${id}, updateType: ${updateType}, ecrTime: ${ecrTime}`);
        },
        error: function (errResponse) {
            console.log(errResponse);
        }
    })
};
function openDeleteRequest(id = 0) {
    saveLog("log_breaker", `deleteBreakerRequest in allList; ID: ${id}`);
    Swal.fire({
        title: 'Are you sure to delete this breaker?',
        showCancelButton: true,
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancel!',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Delete',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            deleteOpenBreaker(id);
        },
        allowOutsideClick: () => !Swal.isLoading()
    });
}

const deleteOpenBreaker = function (breakerId = 0) {
    if (breakerId < 1) {
        showNotification('error', 'Could not identify breaker information');
        return false;
    }
    $.ajax({
        url: 'api/BreakerController.php?action=delete&breakerId=' + breakerId,
        dataType: 'json',
        type: 'GET',
        success: function (data) {
            showNotification('success', 'Successfully deleted');
            $('#table_open_items').DataTable().ajax.reload(null, false);
            saveLog("log_breaker", `Breaker is deleted in allList; ID: ${breakerId}`);
        },
        error: function (errResponse) {
            showNotification('error', 'Error on breaker deletion');
            saveLog("log_breaker", `Breaker could not delete in allList; ID: ${breakerId}`);
        }
    });
};

function changeStatus(id, updateType) {
    saveLog("log_breaker", `changeStatus in allList; ID: ${id}, updateType: ${updateType}`);
    Swal.fire({
        title: 'Enter times',
        html: `
            <label for="swal-input1">Amount of time spent(ECR): </label>
            <input id="swal-add-time-input" class="swal2-input" placeholder="Time spent(hours)" style="width: 80%; margin: 0 0 1rem;">
            <label for="swal-input1">Amount of idle time</label>
            <input id="swal-idle-time-input" class="swal2-input" placeholder="Idle time(hours)" style="width: 80%; margin: 0 0 1rem;">
        `,
        showCancelButton: true,
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancel!',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Save',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const timeSpent = document.getElementById('swal-add-time-input').value;
            const idleTime = document.getElementById('swal-idle-time-input').value;

            console.log('timeSpent:', timeSpent);
            console.log('idleTime:', idleTime);

            //updateReworkTime(ecrTime, updateType, id);

            //Swal.fire('Saved!', `timeSpent`, 'success');

            return new Promise((resolve) => {
                setTimeout(() => {
                    // Resolve the Promise with the result of the operation
                    resolve({});
                }, 2000); // Simulated delay of 2 seconds
            });
        }
    });
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