$("#mainCategorySelect").select2({
    width: '100%'
});
$("#subCategorySelect").select2({
    width: '100%',
    dropdownAutoWidth: true
});
const loadMainCategories = function (data) {
    $("#mainCategorySelect").empty().trigger("change");
    $.each(data, function (index, value) {
        var newOption = new Option(value.text, value.id, false, false);
        $('#mainCategorySelect').append(newOption);
    });
};
const loadSubCategories = function (data) {
    $("#subCategorySelect").empty().trigger("change");
    $.each(data, function (index, value) {
        var newOption = new Option(value.text, value.id, false, true);
        $('#subCategorySelect').append(newOption);
    });
};
const getMainCategories = function () {
    $.ajax({
        url: '/assemblynotes/report/reportAPI.php',
        data: {
            "type": "listMainCategories",
        },
        dataType: "json",
        method: 'GET',
        success: function (response) {
            loadMainCategories(response.data)
        },
        error: function (errResponse) {
            console.log(errResponse);
            showNotification('error', "Ana kategoriler yüklenemedi");
        }
    });
};
const getSubCategories = function (mainCategoryId = 0) {
    $.ajax({
        url: '/assemblynotes/report/reportAPI.php',
        data: {
            "type": "listSubCategories",
            "mainCategoryId": mainCategoryId
        },
        dataType: "json",
        method: 'GET',
        success: function (response) {
            loadSubCategories(response.data)
        },
        error: function (errResponse) {
            console.log(errResponse);
            showNotification('error', "Ana kategoriler yüklenemedi");
        }
    });
};
$('#mainCategorySelect').on('select2:select', function (e) {
    $("#subCategorySelect").empty().trigger("change");
    const mainCategoryId = e.params.data.id;
    getSubCategories(mainCategoryId);
});