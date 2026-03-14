const getCountWidget = function (param) {
    $.ajax({
        url: '../noteWidgets/noteWidgetsAPI.php',
        data: {
            "type": "listWidgets",
            "param": JSON.stringify(param)
        },
        dataType: "json",
        method: 'GET',
        success: function (response) {
            console.log(response.data);
            loadCountOfNoteWidget(response.data);
        },
        error: function (errResponse) {
            console.log(errResponse);
            showNotification('error', "Not göstergeleri yüklenemedi");
        }
    });
}
const getTimeReworkWidget = function (param) {
    $.ajax({
        url: '../noteWidgets/noteWidgetsAPI.php',
        data: {
            "type": "listReworkWidgets",
            "param": JSON.stringify(param)
        },
        dataType: "json",
        method: 'GET',
        success: function (response) {
            loadTimeOfReworkWidget(response.data);
        },
        error: function (errResponse) {
            console.log(errResponse);
            showNotification('error', "Not göstergeleri yüklenemedi");
        }
    });
}
const loadCountOfNoteWidget = function (widgetData) {
    let lang = localStorage.getItem('language');
    $("#openNoteWidget").text(widgetData["openNoteCount"]).append(` <label id="mai-qty-1">${changeLang[lang]["qty"]}</label>`);
    $("#closeNoteWidget").text(widgetData["closeNoteCount"]).append(` <span id="mai-qty-1">${changeLang[lang]["qty"]}</span>`);
    $("#totalNoteWidget").text(widgetData["totalNoteCount"]).append(` <span id="mai-qty-1">${changeLang[lang]["qty"]}</span>`);
}
const loadTimeOfReworkWidget = function (widgetData) {
    let lang = localStorage.getItem('language');
    $("#closeReworkTimeWidget").text(widgetData["closeReworkTime"]).append(` <label id="mai-mn-1">${changeLang[lang]["mn"]}</label>`);
    $("#totalReworkTimeWidget").text(widgetData["totalReworkTime"]).append(` <label id="mai-mn-1">${changeLang[lang]["mn"]}</label>`);
}
getCountWidget(null);
getTimeReworkWidget(null);