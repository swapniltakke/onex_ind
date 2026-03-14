let dtPivotTableForMain;
let dtPivotTableForSub;
Highcharts.theme = {
    "colors": [
        "#A9CF54",
        "#C23C2A",
        "#FFFFFF",
        "#979797",
        "#FBB829"
    ],
    "chart": {
        "backgroundColor": "#242F39",
        "style": {
            "color": "white"
        }
    },
    "legend": {
        "enabled": true,
        "align": "right",
        "verticalAlign": "bottom",
        "itemStyle": {
            "color": "#C0C0C0"
        },
        "itemHoverStyle": {
            "color": "#C0C0C0"
        },
        "itemHiddenStyle": {
            "color": "#444444"
        }
    },
    "title": {
        "text": {},
        "style": {
            "color": "#FFFFFF"
        }
    },
    "tooltip": {
        "backgroundColor": "#1C242D",
        "borderColor": "#1C242D",
        "borderWidth": 1,
        "borderRadius": 0,
        "style": {
            "color": "#FFF"
        }
    },
    "subtitle": {
        "style": {
            "color": "#FFF"
        }
    },
    "xAxis": {
        "type": "datetime",
        "gridLineColor": "#2E3740",
        "gridLineWidth": 1,
        "labels": {
            "style": {
                "color": "#FFF"
            }
        },
        "lineColor": "#2E3740",
        "tickColor": "#2E3740",
        "title": {
            "style": {
                "color": "#FFFFFF"
            },
            "text": ""
        }
    },
    "yAxis": {
        "gridLineColor": "#2E3740",
        "gridLineWidth": 1,
        "labels": {
            "style": {
                "color": "#FFF"
            },
            "lineColor": "#2E3740",
            "tickColor": "#2E3740",
            "title": {
                "style": {
                    "color": "#FFF"
                },
                "text": {}
            }
        }
    }
};
// Apply the theme
Highcharts.setOptions(Highcharts.theme);
const loadParetoChart = function (subCategories, qtyDataStr) {
    Highcharts.chart('container', {
            chart: {
                renderTo: 'container',
                type: 'column'
            },
            title: {
                text: '<div id="subChartReasonDiv">Rework Nedenleri - Alt Kategoriler</div>'
            },
            tooltip: {
                shared: true
            },
            xAxis: {
                categories: subCategories,
                crosshair: true
            },
            credits: {
                enabled: false
            },
            yAxis: [{
                title: {
                    text: ''
                }
            }, {
                title: {
                    text: ''
                },
                minPadding: 0,
                maxPadding: 0,
                max: 100,
                min: 0,
                opposite: true,
                labels: {
                    format: "{value}%"
                }
            }],
            series: [{
                type: 'pareto',
                name: 'Pareto',
                yAxis: 1,
                zIndex: 10,
                baseSeries: 1,
                tooltip: {
                    valueDecimals: 2,
                    valueSuffix: '%'
                }
            }, {
                name: 'Panel Count',
                type: 'column',
                zIndex: 2,
                data: qtyDataStr
            }]
        }
        , function (chart) {
            const lang = localStorage.getItem('language');
            const new_title = `<div id="subChartReasonDiv">${changeLang[lang]["rework-reasons"]} - ${changeLang[lang]["sub-category"]}</div>`
            chart.setTitle({text: new_title});
        });

}
const loadParetoChartforMain = function (mainCategories, qtyDataStr) {
    Highcharts.chart('containerMain', {
            chart: {
                renderTo: 'container',
                type: 'column'
            },
            title: {
                text: '<div id="mainChartReasonDiv">Rework Nedenleri - Ana Kategoriler</div>'
            },
            tooltip: {
                shared: true
            },
            xAxis: {
                categories: mainCategories,
                crosshair: true
            },
            credits: {
                enabled: false
            },
            yAxis: [{
                title: {
                    text: ''
                }
            }, {
                title: {
                    text: ''
                },
                minPadding: 0,
                maxPadding: 0,
                max: 100,
                min: 0,
                opposite: true,
                labels: {
                    format: "{value}%"
                }
            }],
            series: [{
                type: 'pareto',
                name: 'Pareto',
                yAxis: 1,
                zIndex: 10,
                baseSeries: 1,
                tooltip: {
                    valueDecimals: 2,
                    valueSuffix: '%'
                }
            }, {
                name: 'Panel Count',
                type: 'column',
                zIndex: 2,
                data: qtyDataStr
            }]
        }
        , function (chart) {
            const lang = localStorage.getItem('language');
            const new_title = `<div id="mainChartReasonDiv"><label id="lblReworkReasons" data-translate="rework-reasons">${changeLang[lang]["rework-reasons"]}</label> - <label data-translate="main-category">${changeLang[lang]["main-category"]}</label></div>`
            chart.setTitle({text: new_title});
        });
}

const loadPivotTableForMain = function (dataSrc = []) {
    dtPivotTableForMain = $('#dt_main_category_pivot').DataTable({
        "aLengthMenu": [
            [5, 10, 25, 50, 100, -1],
            [5, 10, 25, 50, 100, "Hepsi"]
        ],
        "iDisplayLength": 10,
        "ordering": false,
        "order": [],
        "data": dataSrc,
        "searching": true,
        "destroy": true,
        dom: '<"html5buttons"B>lTfgitp',
        autoWidth: false,
        buttons: [
            'copy', 'excel', 'pdf', 'csv'
        ],
        "columns": [
            {
                data: "category"
            },
            {data: "qty"}
        ],
        "columnDefs": [
            {"className": "text-center", "targets": "_all"}
        ]
    });
};
const loadPivotTableForSub = function (dataSrc = []) {
    dtPivotTableForSub = $('#dt_sub_category_pivot').DataTable({
        "aLengthMenu": [
            [5, 10, 25, 50, 100, -1],
            [5, 10, 25, 50, 100, "Hepsi"]
        ],
        "iDisplayLength": 10,
        "ordering": false,
        "order": [],
        "data": dataSrc,
        "searching": true,
        "destroy": true,
        dom: '<"html5buttons"B>lTfgitp',
        autoWidth: false,
        buttons: [
            'copy', 'excel', 'pdf', 'csv'
        ],
        "columns": [
            {
                data: "category"
            },
            {data: "qty"}
        ],
        "columnDefs": [
            {"className": "text-center", "targets": "_all"}
        ]
    });
};
const getParetoData = function (param) {
    $.ajax({
        url: 'reportAPI.php',
        data: {
            "type": "list",
            "param": JSON.stringify(param)
        },
        dataType: "json",
        method: 'GET',
        success: function (data) {
            const subCategories = data.subCategories;
            const qtyStr = data.qtyInfo;
            loadParetoChart(subCategories, qtyStr);
            loadPivotTableForSub(data.qtyDataForSubCategoriesRaw);
            saveLog("log_assembly_notes", 'Rework Pareto Chart Loaded Sub Categories');
        },
        error: function (errResponse) {
            console.log(errResponse);
            showNotification('error', "Grafik yüklenemedi");
        }
    });
}
const getParetoDataForMain = function (param) {
    $.ajax({
        url: 'reportAPI.php',
        data: {
            "type": "listMain",
            "param": JSON.stringify(param)
        },
        dataType: "json",
        method: 'GET',
        success: function (data) {
            const mainCategories = data.mainCategories;
            const qtyStr = data.qtyInfo;
            loadParetoChartforMain(mainCategories, qtyStr);
            loadPivotTableForMain(data.qtyDataForMainCategoriesRaw);
            saveLog("log_assembly_notes", 'Rework Pareto Chart Loaded Main Categories');
        },
        error: function (errResponse) {
            console.log(errResponse);
            showNotification('error', "Grafik yüklenemedi");
        }
    });
}
$(document).ready(function () {
    saveLog("log_assembly_notes", "Report page access");

    getParetoData(null);
    getParetoDataForMain(null);
});
const getFilterParams = function () {
    let subCategories = $("#subCategorySelect").val().join(',');
    let [startdate, finishdate] = getDates("DD-MM-YYYY");
    let params = {
        "lv": false,
        "mv": false,
        "startDate": startdate,
        "endDate": finishdate,
        "subCategories": subCategories,
        "openAndClose": "open",
        "category": "All"
    }
    params.openAndClose = $('#openAndCloseSelect').val()
    params.category = $('#categorySelect').val()
    checkedLv = $('#chkLV').is(':checked');
    checkedMv = $('#chkMV').is(':checked');

    if (checkedLv) {
        params.lv = true;
    }
    if (checkedMv) {
        params.mv = true;
    } else if (checkedMv == false && checkedLv == false) {
        showNotification("warning", "MV/LV seçilmelidir. İşlemler i̇ptal edildi.")
        return false;
    }

    if (!isset(params.startDate)) {
        showNotification("warning", "Başlangıç tarih filtresi seçilmelidir. İşlemler i̇ptal edildi.")
        return false;
    } else if (!isset(params.endDate)) {
        showNotification("warning", "Bitiş tarih filtresi seçilmelidir. İşlemler i̇ptal edildi.")
        return false;
    }
    if (new Date(params.startDate) > new Date(params.endDate)) {
        showNotification("warning", "Başlangıç tarihi bitiş tarihinden önce olamaz. İşlemler i̇ptal edildi.")
        return false;
    }
    return params
}
$("#btnFilter").click(function () {
    const filters = getFilterParams();
    if (filters != false) {
        getParetoData(filters);
        getCountWidget(filters);
        getParetoDataForMain(filters);
        getTimeReworkWidget(filters);
    }
});
$('#dateRangeFilter .input-daterange').datepicker({
    keyboardNavigation: false,
    forceParse: false,
    autoclose: true,
    format: 'dd-mm-yyyy'
});


jQuery(document).ready(function () {
    getMainCategories();
    initializeDatePicker()
});
