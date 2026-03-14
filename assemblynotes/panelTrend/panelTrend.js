const startOfMonth = moment().startOf('month').format('DD-MM-YYYY');
const currentDay = moment().format('DD-MM-YYYY');

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
            formatter: (currData) => {
                const currDate = new Date(currData.value);
                return currDate.getDate() + '-' + (currDate.getMonth() + 1) + '-' + currDate.getFullYear();
            },
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
            "text": "Tarihler"
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

const getChartData = function (param) {
    $.ajax({
        url: 'panelTrendAPI.php',
        data: {
            "type": param.type,
            "param": JSON.stringify(param)
        },
        dataType: "json",
        method: 'GET',
        success: function (data) {
            if (param.type == "drawMv") {
                loadMvChart(data);
            }
            if (param.type == "drawLv") {
                loadLvChart(data);
            }
            saveLog("log_assembly_notes", 'Trend Reports MV Chart Loaded');
        },
        error: function (errResponse) {
            console.log(errResponse);
            showNotification('error', "Grafik yüklenemedi");
        }
    });
}
const loadMvChart = function (data) {
    Highcharts.chart('containerMv', {
        type: 'line',
        title: {
            text: 'İlave İşçilik Süresi (dk)\n(MV)'
        },
        credits: false,
        subtitle: {
            text: 'Kapalı durum-Güncelleme zamanları dikkate alınarak bu grafik çizdirilmiştir.'
        },

        yAxis: {
            title: {
                text: 'İlave İşçilik Süresi (dk)'
            },
            gridLineColor: 'transparent',
            allowDecimals: true,
            type: 'logarithmic',
            minorTickInterval: 1,
            lineWidth: 0,
            gridLineWidth: 0,
            minorGridLineWidth: 0
        },
        plotOptions: {
            series: {
                connectNulls: true
            }
        },
        xAxis: {
            categories: data.bydate,
            type: 'datetime',
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle'
        },

        series: [
            {
                name: 'MV Rework Süre Toplamı',
                data: data.reworkTotal
            }, {
                name: 'MV Pano üretim Adet',
                data: data.panelCount
            }, {
                name: 'Ortalama MV',
                data: data.avg
            }],

        responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    legend: {
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    }
                }
            }]
        }

    });
}
const loadLvChart = function (data) {
    Highcharts.chart('containerLv', {
        type: 'line',
        title: {
            text: 'İlave İşçilik Süresi (dk)\n(LV)'
        },
        credits: false,
        subtitle: {
            text: 'Kapalı durum-Güncelleme zamanları dikkate alınarak bu grafik çizdirilmiştir.'
        },

        yAxis: {
            title: {
                text: 'İlave İşçilik Süresi (dk)'
            },
            gridLineColor: 'transparent',
            allowDecimals: true,
            type: 'logarithmic',
            minorTickInterval: 1,
            lineWidth: 0,
            gridLineWidth: 0,
            minorGridLineWidth: 0
        },
        plotOptions: {
            series: {
                connectNulls: true
            }
        },
        xAxis: {
            categories: data.bydate,
            type: 'datetime',
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle'
        },

        series: [{
            name: 'LV Rework Süre Toplamı',
            data: data.reworkTotal
        }, {
            name: 'LV Pano üretim Adet',
            data: data.panelCount
        }, {
            name: 'Ortalama LV',
            data: data.avg
        }],

        responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    legend: {
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    }
                }
            }]
        }

    });
}
const getFilterParams = function () {
    let [startdate, finishdate] = getDates("MM/DD/YYYY");
    let params = {
        "startDate": startdate,
        "endDate": finishdate
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
        getChartData({"type": "drawMv", ...filters});
        getChartData({"type": "drawLv", ...filters});
    }
});

$(document).ready(function () {

    initializeDatePicker()
    $('#dateRangeFilter .input-daterange').datepicker({
        keyboardNavigation: false,
        forceParse: false,
        autoclose: true,
        minYear: 2022,
        todayHighlight: true,
        locale: {
            format: 'DD.MM.YYYY'
        },
    });


    saveLog("log_assembly_notes", "Trend Reports page access");
    getChartData({"type": "drawMv", "startDate": startOfMonth, "endDate": currentDay});
    getChartData({"type": "drawLv", "startDate": startOfMonth, "endDate": currentDay});
});