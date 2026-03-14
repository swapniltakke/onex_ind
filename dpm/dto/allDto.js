document.addEventListener("DOMContentLoaded", function () {
    saveLog("log_dto", "Open List page access");
    localStorage.removeItem('DataTables_table_open_items_/dpm/dto/allDto.php');

    new FilterFields('table_open_items');
    let productNameFilter = new Filter('productName', 'table_open_items', 2);
    let iacRatingFilter = new Filter('iacRating', 'table_open_items', 3);
    let woundCTsFilter = new Filter('woundCTs', 'table_open_items', 6);
    let windowCTsFilter = new Filter('windowCTs', 'table_open_items', 7);
    let cablesBusFilter = new Filter('cablesBus', 'table_open_items', 8);
    let cabCoreFilter = new Filter('cabCore', 'table_open_items', 9);
    let cabEntryFilter = new Filter('cabEntry', 'table_open_items', 10);
    let ratedVolFilter = new Filter('ratedVol', 'table_open_items', 11);
    let ratedCirFilter = new Filter('ratedCir', 'table_open_items', 12);
    let ratedCurrentFilter = new Filter('ratedCurrent', 'table_open_items', 13);
    let widthFilter = new Filter('width', 'table_open_items', 14);
    let rearBoxDepthFilter = new Filter('rearBoxDepth', 'table_open_items', 15);
    let feederMatFilter = new Filter('feederMat', 'table_open_items', 16);
    let infoFilter = new Filter('info', 'table_open_items', 18);
    let orderNoFilter = new Filter('orderNo', 'table_open_items', 19);
    
    
    
    //cablesBusFilter.data = {};
    //let mlfbFilterOptions = {};
    //let mlfbFilterNextValue = 1;

    let filterArray = [productNameFilter, iacRatingFilter, woundCTsFilter, windowCTsFilter, cablesBusFilter, cabCoreFilter, cabEntryFilter, ratedVolFilter, ratedCirFilter, ratedCurrentFilter, widthFilter, rearBoxDepthFilter, feederMatFilter, infoFilter, orderNoFilter];
    new FilterInitial("table_open_items", filterArray);
    filterArray.forEach(filter => {
        filter.init();
    });
    
    //$('#mlfbNoFilter').parent().css('width', '180%');

    
    


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
            'fixedHeader': true,
            'fixedColumns': { leftColumns: 6 },
            ajax: {
                url: 'api/DTOController.php?action=allList',
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
                            <button class="ui mini orange button mt-1" onClick='openUpdateModal1(event, ${data.dto_id})'>Update</button>
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
                    //data: "productName"
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
                    //data: "iacRating"
                    data: function (data, type, row) {
                        if (!iacRatingFilter.data.includes(data.iacRating)) {
                            iacRatingFilter.data.push(data.iacRating);
                            $("#iacRatingFilter").append(new Option(data.iacRating, data.iacRating));
                        }
                        return `<span>${data.iacRating}</span>`
                    }
                },
                { // 4
                    className: "ta-c",
                    data: "docNo"
                    // data: function (data, type, row) {
                    //     if (!planMonthDateFilter.data.includes(data.planMonthDate)) {
                    //         planMonthDateFilter.data.push(data.planMonthDate);
                    //         $("#planMonthDateFilter").append(new Option(data.planMonthDate, data.planMonthDate));
                    //     }
                    //     return `<span>${data.planMonthDate}</span>`
                    // }
                },
                { // 5
                    className: "ta-c",
                    data: "shortDescription"
                    // data: function (data, type, row) {
                    //     if (!salesOrderNoFilter.data.includes(data.salesOrderNo)) {
                    //         salesOrderNoFilter.data.push(data.salesOrderNo);
                    //         $("#salesOrderNoFilter").append(new Option(data.salesOrderNo, data.salesOrderNo));
                    //     }
                    //     return `<span>${data.salesOrderNo}</span>`
                    // }
                },
                { // 6
                    className: "ta-c",
                    //data: "woundCTs"
                    data: function (data, type, row) {
                        if (!woundCTsFilter.data.includes(data.woundCTs)) {
                            woundCTsFilter.data.push(data.woundCTs);
                            $("#woundCTsFilter").append(new Option(data.woundCTs, data.woundCTs));
                        }
                        return `<span>${data.woundCTs}</span>`
                    }
                },
                { // 7
                    className: "ta-c",
                    //data: "windowCTs"
                    data: function (data, type, row) {
                        if (!windowCTsFilter.data.includes(data.windowCTs)) {
                            windowCTsFilter.data.push(data.windowCTs);
                            $("#windowCTsFilter").append(new Option(data.windowCTs, data.windowCTs));
                        }
                        return `<span>${data.windowCTs}</span>`
                    }
                },
                { // 8
                    className: "ta-c",
                    //data: "cablesBus"
                    data: function (data, type, row) {
                        if (!cablesBusFilter.data.includes(data.cablesBus)) {
                            cablesBusFilter.data.push(data.cablesBus);
                            $("#cablesBusFilter").append(new Option(data.cablesBus, data.cablesBus));
                        }
                        return `<span>${data.cablesBus}</span>`
                    }
                },
                { // 9
                    className: "ta-c",
                    //data: "cabCore"
                    data: function (data, type, row) {
                        if (!cabCoreFilter.data.includes(data.cabCore)) {
                            cabCoreFilter.data.push(data.cabCore);
                            $("#cabCoreFilter").append(new Option(data.cabCore, data.cabCore));
                        }
                        return `<span>${data.cabCore}</span>`
                    }
                },
                { // 10
                    className: "ta-c",
                    //data: "cabEntry"
                    data: function (data, type, row) {
                        if (!cabEntryFilter.data.includes(data.cabEntry)) {
                            cabEntryFilter.data.push(data.cabEntry);
                            $("#cabEntryFilter").append(new Option(data.cabEntry, data.cabEntry));
                        }
                        return `<span>${data.cabEntry}</span>`
                    }
                },
                { // 11
                    className: "ta-c",
                    //data: "ratedVol"
                    data: function (data, type, row) {
                        if (!ratedVolFilter.data.includes(data.ratedVol)) {
                            ratedVolFilter.data.push(data.ratedVol);
                            $("#ratedVolFilter").append(new Option(data.ratedVol, data.ratedVol));
                        }
                        return `<span>${data.ratedVol}</span>`
                    }
                },
                { // 12
                    className: "ta-c",
                    //data: "ratedCir"
                    data: function (data, type, row) {
                        if (!ratedCirFilter.data.includes(data.ratedCir)) {
                            ratedCirFilter.data.push(data.ratedCir);
                            $("#ratedCirFilter").append(new Option(data.ratedCir, data.ratedCir));
                        }
                        return `<span>${data.ratedCir}</span>`
                    }
                },
                { // 13
                    className: "ta-c",
                    //data: "ratedCurrent"
                    data: function (data, type, row) {
                        if (!ratedCurrentFilter.data.includes(data.ratedCurrent)) {
                            ratedCurrentFilter.data.push(data.ratedCurrent);
                            $("#ratedCurrentFilter").append(new Option(data.ratedCurrent, data.ratedCurrent));
                        }
                        return `<span>${data.ratedCurrent}</span>`
                    }
                    
                },
                { // 14
                    className: "ta-c",
                    //data: "width"
                    data: function (data, type, row) {
                        if (!widthFilter.data.includes(data.width)) {
                            widthFilter.data.push(data.width);
                            $("#widthFilter").append(new Option(data.width, data.width));
                        }
                        return `<span>${data.width}</span>`
                    }
                },
                { // 15
                    className: "ta-c",
                    //data: "rearBoxDepth"
                    data: function (data, type, row) {
                        if (!rearBoxDepthFilter.data.includes(data.rearBoxDepth)) {
                            rearBoxDepthFilter.data.push(data.rearBoxDepth);
                            $("#rearBoxDepthFilter").append(new Option(data.rearBoxDepth, data.rearBoxDepth));
                        }
                        return `<span>${data.rearBoxDepth}</span>`
                    }
                },
                { // 16
                    className: "ta-c",
                    //data: "feederMat"
                    data: function (data, type, row) {
                        if (!feederMatFilter.data.includes(data.feederMat)) {
                            feederMatFilter.data.push(data.feederMat);
                            $("#feederMatFilter").append(new Option(data.feederMat, data.feederMat));
                        }
                        return `<span>${data.feederMat}</span>`
                    }
                },
                { // 17
                    className: "ta-c",
                    data: "realBy"
                },
                { // 18
                    className: "ta-c",
                    //data: "info"
                    data: function (data, type, row) {
                        if (!infoFilter.data.includes(data.info)) {
                            infoFilter.data.push(data.info);
                            $("#infoFilter").append(new Option(data.info, data.info));
                        }
                        return `<span>${data.info}</span>`
                    }
                },
                { // 19
                    className: "ta-c",
                    //data: "orderNo"
                    data: function (data, type, row) {
                        if (!orderNoFilter.data.includes(data.orderNo)) {
                            orderNoFilter.data.push(data.orderNo);
                            $("#orderNoFilter").append(new Option(data.orderNo, data.orderNo));
                        }
                        return `<span>${data.orderNo}</span>`
                    }
                },
                { // 20
                    className: "ta-c",
                    data: "DrawNo"
                },
                { // 21
                    className: "ta-c",
                    data: "eartSwitch"
                    // data: function (data, type, row) {
                    //     if (!productionOrderNoFilter.data.includes(data.productionOrderNo)) {
                    //         productionOrderNoFilter.data.push(data.productionOrderNo);
                    //         $("#productionOrderNoFilter").append(new Option(data.productionOrderNo, data.productionOrderNo));
                    //     }
                    //     return `<span>${data.productionOrderNo}</span>`
                    // }
                },
                { // 22
                    className: "ta-c",
                    data: "doVt"
                },
                { // 23
                    className: "ta-c",
                    data: "toolSel"
                },
                // { // 24
                //     className: "ta-c",
                //     //data: "exampleInputFile"
                // },
                { // 25
                    className: "ta-c",
                    data: "addOn"
                },
                { // 26
                    className: "ta-c",
                    data: "solenoid"
                },
                { // 27
                    className: "ta-c",
                    data: "limSwi"
                },
                { // 28
                    className: "ta-c",
                    data: "meshAss"
                },
                { // 29
                    className: "ta-c",
                    data: "lampRearCover"
                },
                { // 30
                    className: "ta-c",
                    data: "glandPlate"
                },
                { // 31
                    className: "ta-c",
                    data: "rearCover"
                },
                { // 32
                    className: "ta-c",
                    //data: "stageName"
                    data: function (data, type, row) {
                        if (!productNameFilter.data.includes(data.stageName)) {
                            productNameFilter.data.push(data.stageName);
                            $("#stageNameNameFilter").append(new Option(data.stageName, data.stageName));
                        }
                        return `<span>${data.stageName}</span>`
                    }
                },
                { // 33
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
                            <button class="ui mini orange button mt-1" onClick="openUpdateModal(event, '${data.docNo}')">Offer</button>
                            <button class="ui mini orange button mt-1" onClick="openImageModal(event, '${data.docNo}')">Layout</button>
                            <button class="ui mini orange button mt-1" onClick="ModalOfferUpdate(event, '${data.docNo}')">Update Offer</button>
                            <button class="ui mini orange button mt-1" onClick="ModalAddDoc(event, '${data.docNo}')">Documents</button>
                            
                            
                         </div>`;
                
                        return actionButtonsHtmlContent;
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

const updateReworkTime = function (ecrTime, updateType, id) {
    saveLog("log_dto", `confirmed changeStatus in allList; ID: ${id}, updateType: ${updateType}`);
    $.ajax({
        async: false,
        url: 'api/DTOController.php?action=' + updateType + '&reworkItemId=' + id + '&ecrTime=' + ecrTime,
        dataType: "json",
        method: 'POST',
        success: function (data) {
            showNotification('success', 'Saved');
            $('#table_open_items').DataTable().ajax.reload(null, false);
            saveLog("log_dto", `success on changeStatus in allList; ID: ${id}, updateType: ${updateType}, ecrTime: ${ecrTime}`);
        },
        error: function (errResponse) {
            console.log(errResponse);
        }
    })
};
// function openDeleteRequest(dto_id = 0) {
//     saveLog("log_dto", `deleteBreakerRequest in allList; ID: ${dto_id}`);
//     Swal.fire({
//         title: 'Are you sure to delete this Record?',
//         showCancelButton: true,
//         cancelButtonColor: '#d33',
//         cancelButtonText: 'Cancel!',
//         confirmButtonColor: '#3085d6',
//         confirmButtonText: 'Delete',
//         showLoaderOnConfirm: true,
//         preConfirm: () => {
//             deleteOpenBreaker(dto_id);
//         },
//         allowOutsideClick: () => !Swal.isLoading()
//     });
// }
function openDeleteRequest(dto_id = 0) {
    saveLog("log_dto", `deleteBreakerRequest in allList; ID: ${dto_id}`);
    Swal.fire({
        title: 'Are you sure to delete this record?',
        showCancelButton: true,
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancel!',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Delete',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            // Return the promise so SweetAlert waits for it
            return deleteOpenBreaker(dto_id);
        },
        allowOutsideClick: () => !Swal.isLoading()
    });
}
// const deleteOpenBreaker = function (dto_id = 0) {
//     if (dto_id < 1) {
//         showNotification('error', 'Could not identify DTO information');
//         return false;
//     }
//     $.ajax({
//         url: 'api/DTOController.php?action=delete&dto_id=' + dto_id,
//         dataType: 'json',
//         type: 'GET',
//         success: function (data) {
//             showNotification('success', 'Successfully deleted');
//             $('#table_open_items').DataTable().ajax.reload(null, false);
//             saveLog("log_dto", `Breaker is deleted in allList; ID: ${dto_id}`);
//         },
//         error: function (errResponse) {
//             showNotification('error', 'Error on breaker deletion');
//             saveLog("log_dto", `Breaker could not delete in allList; ID: ${dto_id}`);
//         }
//     });
// };
const deleteOpenBreaker = function (dto_id = 0) {
    if (dto_id < 1) {
        showNotification('error', 'Could not identify DTO information');
        return Promise.reject('Invalid DTO ID');
    }

    return new Promise((resolve, reject) => {
        $.ajax({
            url: 'api/DTOController.php?action=delete&dto_id=' + dto_id,
            dataType: 'json',
            type: 'GET',
            success: function (data) {
                showNotification('success', 'Successfully deleted');
                $('#table_open_items').DataTable().ajax.reload(null, false);
                saveLog("log_dto", `Breaker is deleted in allList; ID: ${dto_id}`);
                resolve(data); // <-- resolve on success
            },
            error: function (errResponse) {
                showNotification('error', 'Error on breaker deletion');
                saveLog("log_dto", `Breaker could not delete in allList; ID: ${dto_id}`);
                reject(errResponse); // <-- reject on error
            }
        });
    });
};

function changeStatus(id, updateType) {
    saveLog("log_dto", `changeStatus in allList; ID: ${id}, updateType: ${updateType}`);
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

    // $(document).ready(function() {
    //     const table = $('#table_open_items').DataTable();
    //     let editingCell = null;
    
    //     // Double click to enter edit mode
    //     $('#table_open_items tbody').on('dblclick', 'td', function () {
    //         const cell = $(this);
    //         const cellIndex = table.cell(cell).index();
    
    //         // Avoid editing action column or already editing cell
    //         if (cellIndex.column === 0 || cell.find('input').length > 0) return;
    
    //         // If a cell is already being edited, finalize it first
    //         if (editingCell) {
    //             finalizeEdit(editingCell);
    //         }
    
    //         const currentValue = cell.text().trim();
    //         const input = $('<input type="text" class="form-control" />').val(currentValue);
    
    //         cell.empty().append(input);
    //         input.focus();
    
    //         editingCell = cell;
    
    //         // On blur, save value
    //         input.on('blur', function () {
    //             finalizeEdit(cell);
    //         });
    
    //         // Optional: Save on Enter
    //         input.on('keypress', function (e) {
    //             if (e.which === 13) {
    //                 $(this).blur();
    //             }
    //         });
    //     });
    
    //     // Function to finalize editing
    //     function finalizeEdit(cell) {
    //         const input = cell.find('input');
    //         if (input.length === 0) return;
    
    //         const newValue = input.val();
    //         const cellIndex = table.cell(cell).index();
    //         const rowData = table.row(cellIndex.row).data(); // entire row data
    
    //         // Replace cell content
    //         cell.text(newValue);
    //         editingCell = null;
    
    //         // Update DataTable
    //         table.cell(cell).data(newValue).draw();
    
    //         // Send AJAX request to save updated cell value
    //         $.ajax({
    //             url: 'updateData.php',
    //             method: 'POST',
    //             data: {
    //                 rowId: rowData[1], // Example: assuming Sr. No. is at index 1
    //                 columnName: getColumnName(cellIndex.column),
    //                 value: newValue
    //             },
    //             success: function(response) {
    //                 console.log('Update successful:', response);
    //                 // You can show a toast or highlight cell if needed
    //             },
    //             error: function(xhr, status, error) {
    //                 console.error('Update failed:', error);
    //                 // Optionally revert the value or show an error
    //             }
    //         });
    //     }
    
    //     // Map column index to column name (adjust based on your headers)
    //     function getColumnName(index) {
    //         const columns = [
    //             'action', 'sr_no', 'product_name', 'iac_ratings', 'document_number', 'short_description',
    //             'wound_cts', 'window_cts', 'cables_bus_duct', 'cable_core', 'cable_bus_entry',
    //             'rated_voltage', 'rated_short_circuit', 'rated_current', 'width', 'rear_box_depth',
    //             'feeder_material', 'released_by', 'client_name', 'sale_order_no', 'drawing_no',
    //             'earthing_switch', 'pt_dovt_fix', 'nxtool_selection', 'add_on', 'solenoid_interlocking',
    //             'limit_switch', 'meshwire_assembly', 'lamp_on_rear_cover', 'gland_plate',
    //             'rear_cover', 'offer_and_layout'
    //         ];
    //         return columns[index] || '';
    //     }
    // });
    
});


