function format(rowData) {
    var div = $('<div/>').addClass('loading').text('Details section has not implemented yet');

    $.ajax({
        url: '/masterplanning/api/getdetails.php',

        type: 'GET',
        data: {
            project: rowData.projectno,
            quantity: rowData.qty,
            ordermanager: rowData.om
        },
        dataType: 'json',

        success: function (json) {
            div
                .html(json.html)
                .removeClass('loading');
        }
    });

    return div;
}

$(document).ready(function () {
    $.ajax({
        url: `/ordersinplan/api/getLastUpdate.php?type=mv`,
        type: "GET"
    }).then((data, status, responseObj) => {
        const lastUpdate = data["last_update"];
        const lastUpdate2 = data["last_update2"];

        MvLastUpdate.innerText = lastUpdate;
        MvLastUpdate2.innerHTML = lastUpdate2;
    }).catch((e) => {
        console.error(e)
    })

    //onex logo responsive function
    var alterClass = function () {
        var ww = document.body.clientWidth;

        if (ww < 537) {
            $('#logo').removeClass('extend');
            $('#logo').addClass('shrink');
        } else if (ww >= 401) {
            $('#logo').removeClass('shrink');
            $('#logo').addClass('extend');
        }
    };

    $(window).resize(function () {
        alterClass();
    });
    alterClass();
    // Header Responsive Function


    // Sidebar & Modal Responsive Function
    var table = $('#ordersInPlanDatatable').DataTable({
        fixedHeader: true,
        bAutoWidth : false,
        language: {
            emptyTable: "Processing..."
        },
        ajax: {
            url: 'api/getdata.php',
            dataSrc: 'data'
        },
        contentType: "application/json",
        dom: 'Bfrtip',
        buttons: [
            {extend: 'excel', className: 'ui green button'}
        ],
        order: [[5, "asc"]],
        columnDefs: [
            {"className": "align-center", "targets": [0, 1, 2, 3, 4, 5, 6, 7, 8]},
            {"width": "1%", "targets": [0,1]},
            {"width": "2%", "targets": [4, 6, 7]},
            {"width": "2%", "targets": [2]},
            {"width": "3%", "targets": [5,8]},
            {"width": "6%", "targets": [3]},
            {"width": "8%", "targets": []},
        ],
        columns: [
            {
                className: 'details-control',
                data: null,
                defaultContent: '',
                sorting: false
            },
            {
                "data": null,
                "render": function (data, type, full, meta) {
                    if (full["lot"])
                        return full["projectno"];
                    else
                        return full["projectno"];
                }
            },
            {data: "line"},
            {data: "projectname"},
            {
                data: {
                    _: "dates.cwdate",
                    sort: "dates.cwtime"
                }
            },
            {
                data: {
                    _: "dates.assdate",
                    sort: "dates.asstime"
                }
            },
            {data: "paneltype"},
            {data: "qty"},
            {data: "om"}
        ],
        paging: false
    });
    const buttonContent = `
                <div style='display: flex;'>
                    <div style='padding: 5px;'></div>
                </div>
            `;

    $("div.dt-buttons").append(buttonContent);

    $("#all").click(function () {
        $('#ordersInPlanDatatable_filter input').val("");
        table.search("").draw();
        saveLog('log_mp', "Master Planning Page All Projects Filtered");
    });

    $(".ui.green.button").click(function () {
        saveLog('log_mp', "Master Planning Page Excel Downloaded");
    });

    $('#ordersInPlanDatatable_filter input').focus();

    $('#ordersInPlanDatatable tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');

        var row = table.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            tr.removeClass('clicked');
        } else {
            row.child(format(row.data())).show();
            tr.addClass('shown');
            tr.addClass('clicked');
        }
    });

    var searchTerm = getUrlParameters()["search"];
    if(searchTerm){
        $('#ordersInPlanDatatable_filter input').val(searchTerm);
        table.search(searchTerm, true, false).draw();
    }
});