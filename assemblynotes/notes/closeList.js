document.addEventListener("DOMContentLoaded", function () {
    saveLog("log_assembly_notes", "Close list page access");
    localStorage.removeItem('DataTables_table_open_items_/assemblynotes/notes/closeList.php');

    new FilterFields('table_open_items');
    let projectNoFilter = new Filter('projectNo', 'table_open_items', 2);
    let categoryFilter = new Filter('category', 'table_open_items', 6);
    let mainCategoryFilter = new Filter('mainCategory', 'table_open_items', 7);

    let productTypeFilter = new Filter('productType', 'table_open_items', 8);
    let subCategoryFilter = new Filter('subCategory', 'table_open_items', 9);
    let missingCategoryFilter = new Filter('missingCategory', 'table_open_items', 10);

    let filterArray = [projectNoFilter, subCategoryFilter, categoryFilter, mainCategoryFilter, productTypeFilter, missingCategoryFilter];
    new FilterInitial("table_open_items", filterArray);
    filterArray.forEach(filter => {
        filter.init();
    });
    $('#table_open_items').DataTable({
        "dom": 'Blfrtip',
        "pageLength": 20,
        "paging": true,
        "order": [
            [7, "desc"]
        ],
        "scrollX": false,
        "autoWidth": false,
        'responsive': false,
        ajax: {
            url: '../api/notesAPI.php?projectNo=' + 'all' + '&action=' + 'closeList',
            dataSrc: 'data'
        },
        contentType: "application/json",
        "bStateSave": true,
        "stateSave": true,
        lengthMenu: [
            [20, 40, 60, -1],
            [20, 40, 60, "Hepsi"],
        ],
        "initComplete": function (settings, json) {
            document.querySelector('#detailsegment').setAttribute('style', 'display: block');
            $('#table_open_items th').css({'min-width': '80px'});
            $('.dt-button.buttons-copy.buttons-html5').addClass('ui white button');
            $('.dt-button.buttons-copy.buttons-html5').prepend('<i class="clipboard outline icon"></i>');
            $('.dt-button.buttons-excel.buttons-html5').addClass('ui white button');
            $('.dt-button.buttons-excel.buttons-html5').prepend('<i class="file excel outline icon"></i>');
            $("#table_open_items_formFields").css('margin-bottom', "2%");
            $("#mai_spinner_page").removeClass('active');
        },
        columns: [
            {
                data: function (data, type, row) {
                    return `<button class='ui yellow button' onclick='changeStatus(${data.id})';'>Reopen</button>`
                }
            },
            {
                data: function (data, type, row) {
                    if (data.notestatus == "0") {
                        return `<div class="blinking-red"></div></i><span> Not Finished </span>`
                    }
                    if (data.notestatus == "1") {
                        return `<div class="blinking-green"></div></i><span> Finished </span>`
                    }
                }
            },
            {
                data: function (data, type, row) {
                    if (!projectNoFilter.data.includes(data.projectNo)) {
                        projectNoFilter.data.push(data.projectNo);
                        $("#projectNoFilter").append(new Option(data.projectNo, data.projectNo));
                    }
                    return `<span>${data.projectNo}</span>`
                }
            },
            {
                className: "ta-c",
                data: "projectName"
            },
            {
                className: "ta-c",
                data: "panelno"
            },
            {
                className: "ta-c",
                data: "product"
            },
            {
                className: "ta-c",
                data: function (data, type, row) {
                    if (!categoryFilter.data.includes(data.category)) {
                        categoryFilter.data.push(data.category);
                        $("#categoryFilter").append(new Option(data.category, data.category));
                    }
                    return `<span>${data.category}</span>`
                }
            },
            {
                className: "ta-c",
                data: function (data, type, row) {
                    if (!subCategoryFilter.data.includes(data.subCategory)) {
                        subCategoryFilter.data.push(data.subCategory);
                        $("#subCategoryFilter").append(new Option(data.subCategory, data.subCategory));
                    }
                    return `<span>${data.subCategory}</span>`
                }
            },
            {
                className: "ta-c",
                data: function (data, type, row) {
                    if (!missingCategoryFilter.data.includes(data.missingCategory)) {
                        missingCategoryFilter.data.push(data.missingCategory);
                        $("#missingCategoryFilter").append(new Option(data.missingCategory, data.missingCategory));
                    }
                    return `<span>${data.missingCategory}</span>`
                }
            },
            {
                data: "note"
            },
            {
                className: "ta-c",
                data: "materialnolist"
            },
            {
                data: "createdby"
            },
            {
                className: "ta-c",
                data: "created"
            },
            {
                data: "updatedby"
            },
            {
                className: "ta-c",
                data: "updated"
            },
            {
                className: "ta-c",
                data: "ecrTime"
            },
            {
                className: "ta-c",
                data: "idleTime"
            }
        ],
        buttons: [{
            extend: 'excelHtml5',
            action: function (e, dt, button, config) {
                // Prevent default Excel export
                e.preventDefault();
                let dates = $('#reportrange span').html().split(' / ');
                let start_date = dates[0];
                let finish_date = dates[1];
                // Collect current filter and search parameters
                let filterParams = {
                    projectNo: $('#projectNoFilter').val(),
                    category: $('#categoryFilter').val(),
                    mainCategory: $('#mainCategoryFilter').val(),
                    productType: $('#productTypeFilter').val(),
                    subCategory: $('#subCategoryFilter').val(),
                    missingCategory: $('#missingCategoryFilter').val(),
                    startDate: start_date,
                    finishDate: finish_date,
                    searchValue: dt.search()
                };

                // Collect current page data
                let pageData = dt.rows({ search: 'applied' }).data().toArray();

                // Send data via AJAX to generate Excel
                $.ajax({
                    url: '../api/excelAPI.php?action=export_notes&openCloseState=1',
                    method: 'POST',
                    data: {
                        filterParams: JSON.stringify(filterParams)
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(response) {
                        // Create a temporary URL for the blob
                        let blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                        let link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = 'OneX_Closed_Assembly_Notes_' + new Date().toISOString().slice(0,19).replace(/:/g,'-') + '.xlsx';
                        link.click();
                    },
                    error: function(xhr, status, error) {
                        console.error('Excel export error:', error);
                        alert('Failed to generate Excel file');
                    }
                });
            }
        },
            {
                extend: 'copy',
                text: 'Copy',
            }
        ],
        "drawCallback": function (settings) {
            $("#mai_spinner_page").removeClass('active');
        }
    });
});

function filterCloseListByDate() {
    $("#mai_spinner_page").addClass('active');
    let dates = $('#reportrange span').html().split(' / ');
    let start_date = dates[0];
    let finish_date = dates[1];
    let url_string = "../api/notesAPI.php?projectNo=all&action=closeList&start_date=" + start_date + "&finish_date=" + finish_date;
    $('#table_open_items').DataTable().ajax.url(url_string);
    $('#table_open_items').DataTable().ajax.reload()
}


const updateReworkTime = function (ecrTime, id) {
    saveLog("log_assembly_notes", `Confirmed changeStatus in closeList; ID: ${id}, ecrTime: ${ecrTime}`);
    $.ajax({
        async: false,
        url: '../api/notesAPI.php?action=' + 'openItem' + '&reworkItemId=' + id + '&ecrTime=' + ecrTime,
        dataType: "json",
        method: 'GET',
        success: function (data) {
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: 'Kayıt Altına Alındı',
                showConfirmButton: false,
                timer: 1500
            });
            $('#table_open_items').DataTable().ajax.reload(null, false);
            saveLog("log_assembly_notes", `success on changeStatus in closeList; ID: ${id}, ecrTime: ${ecrTime}`);

        }
    })
};

function changeStatus(id) {
    //$('#table_open_items').DataTable().ajax.reload();
    saveLog("log_assembly_notes", `changeStatus button in closeList; ID: ${id}`);
    Swal.fire({
        title: 'This note will be reopened, would you like to add rework time? If not, enter 0!',
        input: 'number',
        inputAttributes: {
            autocapitalize: 'off'
        },
        showCancelButton: true,
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancel!',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Save',
        showLoaderOnConfirm: true,
        preConfirm: (ecrTime) => {
            updateReworkTime(ecrTime, id);
        },
        allowOutsideClick: () => !Swal.isLoading()
    })
}

$(document).ready(function () {
    $("#mai_spinner_page").addClass('active');
    var start = moment().startOf('month');
    var end = moment();

    function cb(start, end) {
        $('#reportrange span').html(start.format("DD-MM-YYYY") + ' / ' + end.format("DD-MM-YYYY"));
        start_date = moment(start, 'DD-MM-YYYY').format('YYYY-MM-DD');
        finish_date = moment(end, 'DD-MM-YYYY').format('YYYY-MM-DD');
    }

    var thisFiscalYear = new Date().getFullYear();
    var thisMonth = new Date().getMonth();
    if (thisMonth < 9) {
        thisFiscalYear -= 1;
    }
    var thisyear = new Date().getFullYear();
    var lastFiscalYear2;
    var lastFiscalYear1;
    if (thisMonth < 9) {
        lastFiscalYear1 = thisyear - 2;
        lastFiscalYear2 = thisyear - 1;
    }
    $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        showDropdowns: true,
        minYear: 2020,
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
});