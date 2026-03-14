document.addEventListener("DOMContentLoaded", function () {
    saveLog("log_assembly_notes", "Open List page access");
    localStorage.removeItem('DataTables_table_open_items_/assemblynotes/notes/openList.php');

    /*new FilterFields('table_open_items');
    let projectNoFilter = new Filter('projectNo', 'table_open_items', 2);
    let categoryFilter = new Filter('category', 'table_open_items', 6);
    let mainCategoryFilter = new Filter('mainCategory', 'table_open_items', 7);
    let productTypeFilter = new Filter('productType', 'table_open_items', 8);
    let subCategoryFilter = new Filter('subCategory', 'table_open_items', 9);
    let missingCategoryFilter = new Filter('missingCategory', 'table_open_items', 10);

    let filterArray = [projectNoFilter, categoryFilter, subCategoryFilter, mainCategoryFilter, productTypeFilter, missingCategoryFilter];
    new FilterInitial("table_open_items", filterArray);
    filterArray.forEach(filter => {
        filter.init();
    });*/
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
            url: '../api/notesAPI.php?projectNo=' + 'all' + '&action=' + 'openList',
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
            $('.dt-button.buttons-copy.buttons-html5').prepend('<i class="clipboard icon"></i>');
            $('.dt-button.buttons-excel.buttons-html5').addClass('ui white button');
            $('.dt-button.buttons-excel.buttons-html5').prepend('<i class="file excel outline icon"></i>');
            $("#table_open_items_formFields").css('margin-bottom', "2%");
            $("#mai_spinner_page").removeClass('active');
        },
        columns: [
            {
                  data: function (data, type, row) {
                      let actionButtonsHtmlContent = `
                          <div class="ui mini buttons" style="flex-direction: column;">
                              <button class="ui mini yellow button" onclick='changeStatus(${data.id},"closeItem")'>Close Note</button>
                              <button class="ui mini teal button mt-1" onclick='changeStatus(${data.id},"addTime")'>Add Time</button>
                              <button class="ui mini orange button mt-1" onClick='openUpdateModal(event, ${data.id})'>Update</button>
                      `;
                      if (data.hasAccessToDelete == "1") {
                          actionButtonsHtmlContent += `<button class="ui mini negative button" onclick="openDeleteRequest(${data.id})">Delete</button></div>`;
                      } else {
                          actionButtonsHtmlContent += `</div>`;
                      }
                      return actionButtonsHtmlContent;
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
            { //1
                data: function (data, type, row) {
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
                    return `<span>${data.category}</span>`
                }
            },
            { // 8
                className: "ta-c",
                data: function (data, type, row) {
                    return `<span>${data.subCategory}</span>`
                }
            },
            {
                className: "ta-c",
                data: function (data, type, row) {
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
                    startDate: start_date,
                    finishDate: finish_date,
                    searchValue: dt.search()
                };

                // Collect current page data
                let pageData = dt.rows({ search: 'applied' }).data().toArray();

                // Send data via AJAX to generate Excel
                $.ajax({
                    url: '../api/excelAPI.php?action=export_notes&openCloseState=0',
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
                        link.download = 'OneX_Open_Assembly_Notes_' + new Date().toISOString().slice(0,19).replace(/:/g,'-') + '.xlsx';
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
        },
    });
});

function filterOpenListByDate() {
    $("#mai_spinner_page").addClass('active');
    let dates = $('#reportrange span').html().split(' / ');
    let start_date = dates[0];
    let finish_date = dates[1];
    let url_string = "../api/notesAPI.php?projectNo=all&action=openList&start_date=" + start_date + "&finish_date=" + finish_date;
    $('#table_open_items').DataTable().ajax.url(url_string);
    $('#table_open_items').DataTable().ajax.reload();
}

const updateReworkTime = function (ecrTime, updateType, id) {
    saveLog("log_assembly_notes", `confirmed changeStatus in openList; ID: ${id}, updateType: ${updateType}`);
    $.ajax({
        async: false,
        url: '../api/notesAPI.php?action=' + updateType + '&reworkItemId=' + id + '&ecrTime=' + ecrTime,
        dataType: "json",
        method: 'POST',
        success: function (data) {
            showNotification('success', 'Saved');
            $('#table_open_items').DataTable().ajax.reload(null, false);
            saveLog("log_assembly_notes", `success on changeStatus in openList; ID: ${id}, updateType: ${updateType}, ecrTime: ${ecrTime}`);
        },
        error: function (errResponse) {
            console.log(errResponse);
        }
    })
};
function openDeleteRequest(id = 0) {
    saveLog("log_assembly_notes", `deleteNoteRequest in openList; ID: ${id}`);
    Swal.fire({
        title: 'Are you sure to delete this note?',
        showCancelButton: true,
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancel!',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Delete',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            deleteOpenNote(id);
        },
        allowOutsideClick: () => !Swal.isLoading()
    });
}

const deleteOpenNote = function (noteId = 0) {
    if (noteId < 1) {
        showNotification('error', 'Could not identify note information');
        return false;
    }
    $.ajax({
        url: '../api/notesAPI.php?action=delete&noteId=' + noteId,
        dataType: 'json',
        type: 'GET',
        success: function (data) {
            showNotification('success', 'Successfully deleted');
            $('#table_open_items').DataTable().ajax.reload(null, false);
            saveLog("log_assembly_notes", `Note is deleted in openList; ID: ${noteId}`);
        },
        error: function (errResponse) {
            showNotification('error', 'Error on note deletion');
            saveLog("log_assembly_notes", `Note could not delete in openList; ID: ${noteId}`);
        }
    });
};
function changeStatus(id, updateType) {
    saveLog("log_assembly_notes", `changeStatus in openList; ID: ${id}, updateType: ${updateType}`);
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
});