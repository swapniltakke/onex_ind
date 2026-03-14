let dtOrderIssues;
const getStatusRowLabel = function (parentId = 0, statusDesc = "") {
    if (parentId > 0)
        return "";
    else {
        if (statusDesc == "Closed")
            return `<label class="label label-success">${statusDesc}</label>`;
        else if (statusDesc == "Open")
            return `<label class="label label-danger">${statusDesc}</label>`;
        else if (statusDesc == "Rework")
            return `<label class="label primary">${statusDesc}</label>`;
        else return "";
    }
};
const loadOrderIssueTable = function (data) {
    dtOrderIssues = $('#dtOrderIssues').DataTable({
        "aLengthMenu": [
            [5, 10, 25, 50, 100, -1],
            [5, 10, 25, 50, 100, "Hepsi"]
        ],
        "iDisplayLength": 5,
        "ordering": false,
        "order": [],
        "data": data,
        "searching": true,
        "destroy": true,
        dom: '<"html5buttons"B>lTfgitp',
        autoWidth: false,
        buttons: [
            'copy', 'excel', 'pdf', 'csv'
        ],
        "columns": [
            {
                data: "ParentId",
                render: function (data, type, full) {
                    if (data == 0)
                        return `<label class="label label-primary">${full.QNumber}</label>`;
                    else
                        return '<label class="label label-secondary">Cevap</label>';
                }
            },
            {"data": "OrderNo"},
            {"data": "PanelNumber"},
            {"data": "Created_By"},
            {"data": "Note"},
            {"data": "Created_At"},
            {
                "data": "StatusDesc",
                "render": function (data, type, full) {
                    return getStatusRowLabel(full.ParentId, data);
                }
            },
            {"data": "IssueCodeDesc"},
            {"data": "ReferenceCode"},
            {
                "data": "OmStatus_At",
                "render": function (data, type, full) {
                    if (full.OmStatusId == 1) {
                        return data;
                    } else {
                        return "";
                    }
                }
            },
            {"data": "difCreatedBetweenOmAnswer"},
        ],
        "columnDefs": [
            {"className": "text-center", "targets": "_all"}
        ]
    });
    $("#dtCard").removeClass("d-none");
};
const getOrderIssues = function (projectNoParam = "") {
    $.ajax({
        url: `../api/orderIssuesAPI.php`,
        data: {
            "type": "listTable",
            "projectNoParam": projectNoParam
        },
        dataType: 'json',
        type: 'GET',
        success: function (data) {
            loadOrderIssueTable(data);
            saveLog("log_order_issues", `Order Issues Table Page Access`);
        },
        error: function (err) {
            showNotification("info", "bx bx-x-circle", "Data could not be loaded");
        }
    });
};
getOrderIssues();


const filterIssues = function (param = "all") {
    let projectNo = $("#orderSelect").val();
    getOrderIssues(projectNo);
};
const clearFilterIssues = function () {
    $("#orderSelect").val(null).trigger("change");
    getOrderIssues(null);
};