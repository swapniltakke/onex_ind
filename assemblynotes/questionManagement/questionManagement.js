let dtOrderRules;
$("#orderSelectCluster").select2({
    ajax: {
        url: `../api/orderSelectboxAPI.php`, dataType: 'json', type: 'GET', data: function (params) {
            return {
                projectNo: params.term, // search term
            };
        }, processResults: function (data, params) {
            data = $.map(data.items, function (obj) {
                return {
                    id: obj.project_no, text: obj.name
                };
            });
            return {
                results: data
            };
        }
    }, language: "tr", minimumInputLength: 3, cache: true
});
$("#orderSelectMain").select2({
    ajax: {
        url: `../api/orderSelectboxAPI.php`, dataType: 'json', type: 'GET', data: function (params) {
            return {
                projectNo: params.term, // search term
            };
        }, processResults: function (data, params) {
            data = $.map(data.items, function (obj) {
                return {
                    id: obj.project_no, text: obj.name
                };
            });
            return {
                results: data
            };
        }
    }, language: "tr", minimumInputLength: 3, cache: true
});
const getParameters = function () {
    let mainProject = $("#orderSelectMain").val();
    let clusterProjects = $("#orderSelectCluster").val() ?? [];
    const lengthOfCluster = clusterProjects.length;
    if (!mainProject) {
        showNotification("error", "Soru sorulması için sipariş belirlenmedi.");
        return false;
    }
    if (!clusterProjects || lengthOfCluster < 1) {
        showNotification("error", "Soru kümesi belirlenmedi.");
        return false;
    }
    for (let i = 0; i < lengthOfCluster; i++) {
        if (lengthOfCluster[i] == mainProject) {
            showNotification("error", `Soru sahibi proje ${mainProject} küme içerisinde yer alamaz.`);
            return false;
        }
    }
    return {
        "clusterProjects": clusterProjects, "mainProject": mainProject
    };
};
const saveOrderRule = function () {
    const params = getParameters();
    if (params !== false) {
        $.ajax({
            url: `./questionManagementAPI.php`, data: {
                "ownerProject": params.mainProject,
                "cluster": JSON.stringify(params.clusterProjects),
                "action": "saveOrderRule"
            }, dataType: 'json',  // <-- what to expect back from the PHP script, if anything
            type: 'GET', success: function (response) {
                console.log("success", response);
                showNotification("success", "Kural başarıyla kaydedildi.");
                saveLog("log_order_issues", `Project Rule Saved`);
                getOrderRules();
            }, error: function (err) {
                console.log("hata", err);
                showNotification("error", "Kural kaydedilemedi. Bir hata ile karşılaşıldı.");
            }
        });
    }
};

const loadOrderRulesTable = function (dataSrc) {
    dtOrderRules = $('#dtOrderRules').DataTable({
        "aLengthMenu": [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "Hepsi"]],
        "iDisplayLength": 5,
        "ordering": false,
        "order": [],
        "data": dataSrc.rules,
        "searching": true,
        "destroy": true,
        dom: '<"html5buttons"B>lTfgitp',
        autoWidth: false,
        buttons: ['excel', 'pdf', 'csv'],
        "columns": [{
            data: "Id"
        }, {"data": "Cluster"}, {"data": "OwnerCluster"}, {"data": "Created_By"}, {"data": "Created_At"}, {
            "data": "Id", render: function (data, type, full) {
                if (dataSrc.userGroupId == 5) {
                    return `
<label class="btn btn-sm btn-danger" onclick="deleteOrderRules(${data})"><i class="fa fa-trash"></i></label>`;
                } else {
                    return "";
                }
            }
        }],
        "columnDefs": [{"className": "text-center", "targets": "_all"}]
    });

    if (dataSrc.userGroupId == 5) {
        dtOrderRules.column(5).visible(true);
    } else {
        dtOrderRules.column(5).visible(false);
    }
};
const getOrderRules = function () {
    $.ajax({
        url: `./questionManagementAPI.php`, data: {
            "action": "getOrderRules",
        }, dataType: 'json', type: 'GET', success: function (data) {
            loadOrderRulesTable(data);
            saveLog("log_order_issues", `Order Issues: Order Rules Table Page Access`);
        }, error: function (err) {
            console.log(err);
            showNotification("info", "bx bx-x-circle", "Veri yüklenemedi");
        }
    });
};
const deleteOrderRules = function (id = 0) {
    if (id > 0) {
        $.ajax({
            url: `./questionManagementAPI.php`, data: {
                "action": "deleteOrderRule", "id": id
            }, dataType: 'json', type: 'GET', success: function (response) {
                console.log("success", response);
                showNotification("success", "Kural başarıyla silindi.");
                getOrderRules();
                saveLog("log_order_issues", `Order Issues: Order Rule Deleted Id=${id}`);
            }, error: function (err) {
                console.log("error", err);
                showNotification("error", "Veri yüklenemedi");
            }
        });
    } else {
        showNotification("warning", "Kural ID değeri belirlenemedi. İşlem iptal edildi.");
    }
};
getOrderRules();
