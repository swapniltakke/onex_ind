document.addEventListener("DOMContentLoaded", function () {
    saveLog("log_assembly_notes", "All List Page Access: Datatable is loaded");
    localStorage.removeItem('DataTables_table_all_items_/assemblynotes/notes/allNotes.php');

    new FilterFields('table_all_items');
    let projectNoFilter = new Filter('projectNo', 'table_all_items', 1);
    let categoryFilter = new Filter('category', 'table_all_items', 5);
    let mainCategoryFilter = new Filter('mainCategory', 'table_all_items', 6);
    let productTypeFilter = new Filter('productType', 'table_all_items', 7);
    let subCategoryFilter = new Filter('subCategory', 'table_all_items', 8);
    let missingCategoryFilter = new Filter('missingCategory', 'table_all_items', 9);

    let filterArray = [projectNoFilter, categoryFilter, subCategoryFilter, mainCategoryFilter, productTypeFilter, missingCategoryFilter];
    new FilterInitial("table_all_items", filterArray);
    filterArray.forEach(filter => {
        filter.init();
    });
    $('#table_all_items').DataTable({
        "dom": 'Blfrtip',
        "pageLength": -1,
        "order": [
            [7, "desc"]
        ],
        "scrollX": false,
        "autoWidth": false,
        'responsive': false,
        ajax: {
            url: '../api/notesAPI.php?projectNo=' + 'all' + '&action=' + 'allList',
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
            $('.dt-button.buttons-copy.buttons-html5').addClass('ui white button');
            $('.dt-button.buttons-copy.buttons-html5').prepend('<i class="clipboard icon"></i>');
            $('.dt-button.buttons-excel.buttons-html5').addClass('ui white button');
            $('.dt-button.buttons-excel.buttons-html5').prepend('<i class="file excel outline icon"></i>');
            $("#table_all_items_formFields").css('margin-bottom', "2%");
            $("#span_close_status_note_count").text(json.closeNoteStatusCount);
            $("#span_open_status_note_count").text(json.openNoteStatusCount);
            $("#mai_spinner_page").removeClass('active');
        },
        columns: [
            { // 0
                data: function (data, type, row) {
                    if (data.notestatus == "0") {
                        return `<div class="blinking-red"></div></i><span> Not Finished </span>`
                    }
                    if (data.notestatus == "1") {
                        return `<div class="blinking-green"></div></i><span> Finished </span>`
                    }
                    return "";
                }
            },
            { //1
                data: function (data, type, row) {
                    if (!projectNoFilter.data.includes(data.projectNo)) {
                        projectNoFilter.data.push(data.projectNo);
                        $("#projectNoFilter").append(new Option(data.projectNo, data.projectNo));
                    }
                    return `<span>${data.projectNo}</span>`
                }
            },
            { //2
                className: "ta-c",
                data: "projectName"
            },
            { //3
                data: "panelno"
            },
            { // 4
                className: "ta-c",
                data: "product"
            },
            { // 5
                className: "ta-c",
                data: function (data, type, row) {
                    if (!categoryFilter.data.includes(data.category)) {
                        categoryFilter.data.push(data.category);
                        $("#categoryFilter").append(new Option(data.category, data.category));
                    }
                    return `<span>${data.category}</span>`
                }
            },
            { //6
                data: function (data, type, row) {
                    if (!mainCategoryFilter.data.includes(data.mainCategory)) {
                        mainCategoryFilter.data.push(data.mainCategory);
                        $("#mainCategoryFilter").append(new Option(data.mainCategory, data.mainCategory));
                    }
                    return `<span>${data.mainCategory}</span>`
                }
            },
            { //7
                className: "ta-c",
                data: function (data, type, row) {
                    if (!productTypeFilter.data.includes(data.productType)) {
                        productTypeFilter.data.push(data.productType);
                        $("#productTypeFilter").append(new Option(data.productType, data.productType));
                    }
                    return `<span>${data.productType}</span>`
                }
            },
            { // 8
                className: "ta-c",
                data: function (data, type, row) {
                    if (!subCategoryFilter.data.includes(data.subCategory)) {
                        subCategoryFilter.data.push(data.subCategory);
                        $("#subCategoryFilter").append(new Option(data.subCategory, data.subCategory));
                    }
                    return `<span>${data.subCategory}</span>`
                }
            },
            { // 9
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
                className: "ta-c",
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
                        url: '../api/excelAPI.php?action=export_notes&openCloseState=-1',
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
                            link.download = 'OneX_All_Assembly_Notes_' + new Date().toISOString().slice(0,19).replace(/:/g,'-') + '.xlsx';
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
    let url_string = "../api/notesAPI.php?projectNo=all&action=allList&start_date=" + start_date + "&finish_date=" + finish_date;
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