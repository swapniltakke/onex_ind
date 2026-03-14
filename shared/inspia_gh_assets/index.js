var start_date = moment().startOf("month").format("YYYY-MM-DD");
var finish_date = moment().format("YYYY-MM-DD");
let start_date_monthly = moment().startOf("month").format("MM-YYYY");
let finish_date_monthly = moment().format("MM-YYYY");
$(document).ready(function () {
    "use strict";
    window.lineInfo = $("#select_line").find(":selected").text();
});


function BarcodeDataComponent() {
    $('#BarcodeTable').DataTable({
        "bLengthChange": false,
        "pageLength": 4,
        "searching": false,
        "bDestroy": true,
        "ajax": {
            url: '../api/Controller.php',
            dataSrc: '',
            data: {
                action: 'getBarcode',
                start: start_date,
                end: finish_date,
                filter: window.lineInfo
            },
            dataType: 'json',
            type: 'POST',
            beforeSend: function () {

                $('#BarcodeTableIboxContent').addClass('sk-loading');
            }
        },
        "drawCallback": function (settings) {
            $('span.dateFilter').html("(" + $('#reportrange span').html() + ")");
            $('#BarcodeTableIboxContent').removeClass('sk-loading');
        },
        "columns": [
            {"data": "Date"},
            {"data": "Line"},
            {"data": "Planned"},
            {"data": "BarcodeA"},
            {"data": "BarcodeR"},
            {"data": "BarcodeT"},
            {"data": "Type"}
        ]
    });
}

function MonthlyStatusTableComponent() {
    $("#zaman_aylik_content").addClass("sk-loading");


    $.ajax({
        url: "./../api/Controller.php",
        type: "POST",
        dataType: "json",
        data: {
            action: "getLineTargetSum",
            start: start_date,
            end: finish_date,
            filter: window.lineInfo,
        },
    }).done(function (response) {
        $("#hat_giris_hedefi").html(response.Daily.target);
        $("#hat_giris_hedefi_aylik").html(response.Monthly.target);
        $("#hat_giris_gerceklesen").html(response.Daily.release);
        $("#hat_giris_gerceklesen_aylik").html(response.Monthly.release);

        var dailyReleasePercent = (100 * (parseInt(response.Daily.release) / parseInt(response.Daily.target))).toFixed(2);
        var monthlyReleasePercent = (100 * (parseInt(response.Monthly.release) / parseInt(response.Monthly.target))).toFixed(2);
        var dailyLossPercent = (100 * (parseInt(response.Daily.LossTotal) / parseInt(response.Daily.target))).toFixed(2);
        var monthlyLossPercent = (100 * (parseInt(response.Monthly.LossTotal) / parseInt(response.Monthly.target))).toFixed(2);
        $('#actual_rate_daily').html("%" + dailyReleasePercent);
        $('#actual_rate_monthly').html("%" + monthlyReleasePercent);
        $('#loss_rate_daily').html("%" + dailyLossPercent);
        $('#loss_rate_monthly').html("%" + monthlyLossPercent);
        $('#zaman').html("(" + $('#reportrange span').html() + ")");
        let month = $('#reportrange span').html().split(' / ')[0].split('-');
        $('#ay').html("(" + month[1] + "-" + month[2] + ")");
        $('#zaman_aylik_content').removeClass('sk-loading');

    });
}

function showNewProductionLossModal() {
    $('#newProductionLossModal').modal('show')
}

function hideNewProductionLossModal() {
    $('#newProductionLossModal').modal('hide')
}

function clearNewProductionLossModal() {
    $('#lineSelect').val('').trigger('change');
    $('#lossCause').val('').trigger('change');
    $('#department').val('').trigger('change');


    for (const selector of ["#dailyTarget", "#projectNumber", "#panelQty", "#stopTime", "#employeeCount", "#ncNo", "#newProductionLossNote"])
        $(selector).val('')
}

function new_production_loss() {
    const lineSelect = $("#lineSelect").val();
    const lossCause = $("#lossCause").val();
    const projectNumber = $("#projectNumber").val();
    const dailyTarget = +$("#dailyTarget").val();
    const department = $('#department').val();
    const panelQty = +$("#panelQty").val();
    const stopTime = +$("#stopTime").val();
    const employeeCount = +$("#employeeCount").val();
    const ncNo = $("#ncNo").val();
    const note = $('#newProductionLossNote').val();

    if (!dailyTarget) {
        fireToastr("warning", "Günlük hedef tam sayı olmalıdır")
        return;
    }
    if (!lineSelect) {
        fireToastr("warning", "Hat seçimi yapılmalıdır")
        return;
    }
    if (!lossCause) {
        fireToastr("warning", "Verim kaybı nedeni boş olamaz")
        return;
    }
    if (!projectNumber) {
        fireToastr("warning", "Proje numarası boş olamaz")
        return;
    }
    if (!department) {
        fireToastr("warning", "Bölüm seçeneği boş olamaz")
        return;
    }

    if (panelQty % 1 !== 0) {
        fireToastr("warning", "Etkilenen Pano Sayısı tam sayı olmalıdır")
        return;
    }
    if (stopTime % 1 !== 0) {
        fireToastr("warning", "Duruş Süresi tam sayı olmalıdır")
        return;
    }
    if (employeeCount % 1 !== 0) {
        fireToastr("warning", "Personel Sayısı tam sayı olmalıdır")
        return;
    }

    const userName = User.name;
    const userGID = User.gid;

    const postData = {
        dailyTarget,
        lineSelect,
        lossCause,
        projectNumber,
        panelQty,
        stopTime,
        employeeCount,
        note,
        department,
        ncNo,
        userName,
        userGID
    }

    $.ajax({
        url: "./../api/Controller.php",
        type: "POST",
        dataType:"json",
        data: {
            action: "newProductionLoss",
            ...postData
        }
    }).always(function (response, jqXhrRes, responseObj) {
        if (response.message === "success") {
            swal({
                title: "İşlem Başarılı!",
                text: "Yeni Verim Kaybı Girişi Yapıldı",
                type: "success",
            });
            hideNewProductionLossModal();
            clearNewProductionLossModal();
        } else {
            swal({
                title: "Hata",
                text: "Yeni Verim Kaybı Eklenemedi",
                type: "error",
            });
        }
    });
}

function todayLossActual() {
    $.ajax({
        url: "./../api/Controller.php",
        type: "POST",
        dataType: "json",
        data: {
            action: 'getTodayLossActual',
            filter: window.lineInfo
        },
    }).done(function (response) {
        $('#todayLossActual tbody').html('');

        $.each(response, function (key, item) {
            $('#todayLossActual tbody').append(`<tr>
          <td>${item.Line}</td>
          <td>${item.Target}</td>
          <td>${item.Quantity}</td>
          <td>${item.ProjectNo}</td>
          <td>${item.Note}</td>
          <td>${item.Category}</td>
          <td>${item.Department}</td>
          </tr>`);
        });
        $('#todayLossActual').DataTable({
            "bLengthChange": false,
            "pageLength": 4,
            "searching": false,
            "bDestroy": true,
            "language": {
                "emptyTable": "Bugün kayıp girişi yapılmadı"
            }
        });
    });
}

function planProjectsComponent() {
    $('#dailyPlan').DataTable({
        "bLengthChange": false,
        "pageLength": 4,
        "searching": false,
        "bDestroy": true,
        "ajax": {
            url: '../api/Controller.php',
            dataSrc: '',
            data: {
                action: 'getTodayPlannedProjects',
                start: start_date,
                end: finish_date,
                filter: window.lineInfo
            },
            dataType: 'json',
            type: 'POST',
            beforeSend: function () {

                $('#dailyPlanIboxContent').addClass('sk-loading');
            }
        },
        "drawCallback": function (settings) {
            $('span.dateFilter').html("(" + $('#reportrange span').html() + ")");
            $('#dailyPlanIboxContent').removeClass('sk-loading');
        },

        "columns": [
            {"data": "Date"},
            {"data": "StationId"},
            {"data": "FactoryNumber"},
            {"data": "Qty"},
            {"data": "Product"}
        ]
    });
}

function getStats() {
    $.ajax({
        url: "./../api/Controller.php",
        type: "POST",
        dataType: "json",
        data: {
            action: "getChainLoss",
            StartDate: start_date,
            EndDate: finish_date,
            filter: window.lineInfo,
        },
        success: function (response) {
            $("#teyit_edilen_zinciri").html(response.Closed);
            $("#acikta_bekleyen_zinciri").html(response.NotClosed);
            $("#kapatilan_adedi").html(response.ClosedQuantity);
            $("#kapatma_onayi_bekleyen_adedi").html(response.NotClosedQuantity);
        },
        error: function (error) {
            console.log(
                "🚀 ~ file: head_right_table.php ~ line 80 ~ $ ~ error",
                error
            );
        },
    });
}


function fireToastr(type, text, disableHiding = false) {
    const hideAfterVal = (disableHiding) ? disableHiding : 10000;

    if (type === "error") {
        $.toast({
            heading: 'HATA',
            text: text,
            icon: 'error',
            position: 'top-right',
            bgColor: 'red',
            loaderBg: 'brown',
            hideAfter: hideAfterVal,
            stack: false
        })
    } else if (type === "success") {
        $.toast({
            heading: 'Güncelleme Başarılı',
            text: text,
            icon: 'success',
            position: 'top-right',
            bgColor: 'green',
            loaderBg: 'yellow',
            hideAfter: 5000,
            stack: false
        })
    } else if (type === "warning") {
        $.toast({
            heading: 'Uyarı',
            text: text,
            icon: 'warning',
            position: 'top-right',
            bgColor: '#b3883e',
            loaderBg: 'yellow',
            hideAfter: hideAfterVal,
            stack: false
        })
    }
}

$(document).ready(function () {
    ["#projectNumber", "#panelQty", "#stopTime", "#employeeCount", "#ncNo", "#newProductionLossNote"].forEach(function (selector) {
        document.querySelector(selector).addEventListener('keypress', function (e) {
            if (e.key === 'Enter')
                new_production_loss();
        })
    })

    document.querySelector("#openNewProductionLossModal").addEventListener('click', function () {
        showNewProductionLossModal();
        clearNewProductionLossModal();
    })
})
