var selected_option = "main_lines";

function printOrderDetails(data) {
    $("#btnProjectNo").text(data.FactoryNumber);
    //$("#btnPanelNo").text(panelNo);
    $("#btnProjectName").text(data.ProjectName);
    $("#btnPanelQty").text(data.Qty);
    $("#btnOrderManager").text(data.OrderManager);
    $("#btnSwitchGearType").text(data.SwitchGearType);
}

async function getOrderDetail(orderNo = '') {
    return $.ajax({
        url: `../api/orderIssuesAPI.php`,
        data: {
            "type": "getOrderDetail",
            "orderNo": orderNo
        },
        dataType: 'json',
        type: 'GET',
        success: function (data) {
            if (!data) {
                showNotification("warning", "Project details could not be found");
                return
            }
            printOrderDetails(data);
        },
        error: function (err) {
            console.log(err);
            showNotification("error", "Project details could not be loaded");
        }
    });
}


async function getProjectNotes(orderNo) {
    const projectNotes = await $.ajax({
        url: `../api/notesAPI.php`,
        data: {
            "action": "projectNotes",
            "orderNo": orderNo
        },
        dataType: 'json',
        type: 'GET'
    }).catch(e => {
        console.log(e);
        showNotification("error", "Project details could not be loaded");
    });

    if (getUrlParameters()['ProjectNo'] && getUrlParameters()['PanelNo'])
        $('#dtOrderNotes').DataTable().search(getUrlParameters()['PanelNo']).draw();

    loadProjectNotes(projectNotes);
}

function loadProjectNotes(data) {
    if ($.fn.DataTable.isDataTable('#dtOrderNotes'))
        $('#dtOrderNotes').DataTable().clear().destroy();

    $('#dtOrderNotes').DataTable({
        pageLength: 5,
        responsive: true,
        autoWidth: false,
        dom: '<"html5buttons"B>lTfgitp',
        oLanguage: {
            "sLengthMenu": "_MENU_ Per page"
        },
        lengthMenu: [
            [5, 10, 20, -1],
            [5, 10, 20, "All"],
        ],
        data: data,
        columns: [
            {
                "data": "notestatus", render: function (data) {
                    if (data == 0) return 'Open';
                    else return 'Closed';
                }
            },
            {"data": "panelno"},
            {"data": "mainCategory"},
            {"data": "subCategory"},
            {"data": "note"},
            {"data": "created"},
            {"data": "createdby"},
            {"data": "updated"},
            {"data": "updatedby"},
            {
                "data": "id",
                render: function (data) {
                    return `
                        <button class="btn btn-sm btn-info" type="button" onClick="openUpdateModal(event, ${data})">
                            Update
                        </button>
                    `;
                }
            }
        ],
        buttons: [
            {extend: 'copy'},
            {extend: 'csv'},
            {extend: 'excel', title: 'ExampleFile'},
            {extend: 'pdf', title: 'ExampleFile'},
            {
                extend: 'print',
                customize: function (win) {
                    $(win.document.body).addClass('white-bg');
                    $(win.document.body).css('font-size', '10px');

                    $(win.document.body).find('table')
                        .addClass('compact')
                        .css('font-size', 'inherit');
                }
            }
        ]

    });
}

function loadProjectProductDetails(data) {
    let trItem = "";
    $("#tBodyProjectNotes").html('');
    $.each(data, function (index, itemData) {
        let itemContent = `
            <tr>
                <td><h4>${itemData.projectNo}</h4></td>';
                <td><h4>${itemData.scope}</h4></td>
                <td><h4>${itemData.poz}</h4></td>
                <td><h4>${itemData.productionLine}</h4></td>
                <td><h4>${itemData.productionWeek}</h4></td>
                <td><h4>${itemData.productionday}</h4></td>
                <td><h4>${itemData.quantityonday}</h4></td>
            </tr>
        `;
        trItem = trItem + itemContent;
    });
    $("#tBodyProjectNotes").append(trItem);
}


async function getProjectProductDetails(orderNo) {
    const projectProductDetailsData = await $.ajax({
        url: `../api/notesAPI.php`,
        data: {
            "action": "projectProductDetails",
            "projectNo": orderNo
        },
        dataType: 'json',
        type: 'GET',
    }).catch(e => {
        console.log(e)
        showNotification("error", "Project details could not be loaded");
    });

    loadProjectProductDetails(projectProductDetailsData);
}

//called by datatable button
/*function getOrderNote(id) {
    $.ajax({
        url: `../api/notesAPI.php`,
        data: {
            "action": "getOrderNote",
            "id": id
        },
        dataType: 'json',
        type: 'GET',
        success: function (data) {
            openEditNoteModal(data)
        }
    }).catch(e => {
        console.log(e)
        showNotification("error", "Project details could not be loaded");
    });
}*/


function clearInsertModal(panelNo = null) {
    $("#textAreaNewNote").val('');
    $("#slSubCategory").html('');
    $("#inpReworkTime_emergency_line").val("0").trigger('change');
    !panelNo && $("#slPanoNos").val('').trigger('change');
    $("#slCategory").prop("selectedIndex", -1);
    $("#slMissingCategory").prop("selectedIndex", -1);
    slMaterialNos.value = "";
    slMaterialNos.innerHTML = ""
}

function showSelects(chosen) {
    /*$("#inpReworkTime_emergency_line").prop('disabled', true);
    $("#divEmergencyLineReworkTime").addClass('d-none');
    $("#inpReworkTime_emergency_line").val(0).trigger('change');
    $("#divSlPanoNos").attr("style", "display: block !important");
    $("#divSlMissingCategory").attr("style", "display: block !important");*/
}

async function openInsertModal(panelNo = null) {
    const projectNo = $('#btnProjectNo').html();

    btnNewNote.querySelector('span').innerText = "Loading...";
    btnNewNote.disabled = true;

    saveLog("log_assembly_notes", `openInsertModal in details page; projectNo: ${projectNo}`);
    clearInsertModal();

    await getCategories();
    await getMainCategories();
    await getSubCategories()
    await getMissingCategories();
    $('#slPanoNos').select2({
        dropdownParent: $('#insertModal .modal-body #divSlPanoNos'),
    });

    panelNo = panelNo ?? getUrlParameters()['PanelNo'];
    panelNo && $("#slPanoNos").val(panelNo).trigger('change');
    if (panelNo) {
        await getMaterialListByPanelItems([panelNo], projectNo);
    }

    btnNewNote.querySelector('span').innerText = "New Note";
    btnNewNote.disabled = false;

    $("#insertModal").modal('show');
    $(".mainCategory").removeClass("d-none");
    $(".subCategory").removeClass("d-none");
}


function insertOrderNote() {
    let subCategory = $('#slSubCategory').val();

    const projectNo = $('#btnProjectNo').html();
    const descripton = window.myEditor.getData();
    const category = $('#slCategory').children(':selected').text();
    const categoryItem = $('#slCategory').children(':selected').val();
    const panelNumbers = $('#slPanoNos').val() ?? [];
    const reworkTimeForEmergencyTime = $("#inpReworkTime_emergency_line").val() ?? 0;
    const missingCategory = $("#slMissingCategory").children(':selected').val() ?? 0;
    const selectedMaterialNoList = $("#slMaterialNos").val();
    if (!category) {
        showNotification("warning", "Category can not be empty");
        return false;
    }
    if (!descripton.trim()) {
        showNotification("warning", "Description can not be empty");
        return false;
    }

    if (!subCategory) {
        showNotification("warning", "Subcategory can not be empty");
        return false;
    }

    if (!missingCategory) {
        showNotification("warning", "Missing category can not be empty");
        return false;
    }

    if (panelNumbers.length === 0 && categoryItem !== "emergency_lines") {
        showNotification("warning", "Panel number can not be empty");
        return false;
    }
    if (categoryItem === "emergency_lines" && (reworkTimeForEmergencyTime <= 0 || !reworkTimeForEmergencyTime)) {
        showNotification("warning", "Time can not be empty when Emergency Line is selected");
        return false;
    }

    $.ajax({
        url: '/assemblynotes/api/notesAPI.php',
        data: {
            "action": "add",
            "projectNo": projectNo,
            "note": descripton,
            "subcategoryid": subCategory,
            "PanelNos": panelNumbers,
            "materialNos": selectedMaterialNoList,
            "category": category,
            "mainCategoryValue": categoryItem,
            "reworkTimeForEmergencyTime": reworkTimeForEmergencyTime,
            "missingCategoryId": missingCategory
        },
        dataType: "json",
        method: 'POST',
        success: function (data) {
            showNotification('success', "Successfully created");
            getProjectNotes(projectNo).then(() => $("#insertModal").modal('hide'));
        },
        error: function (errRespone) {
            showNotification('error', "An error occurred");
            console.log(errRespone);
        }
    });
}

function showMainCategories() {
    $(".mainCategory").removeClass("d-none");
    $(".subCategory").removeClass("d-none");
    getMainCategories();
    getSubCategories();
    getMissingCategories();
}

function showSupportCategories() {
    $(".supportCategory").removeClass("d-none");
    getSubSupportCategories();
}


function loadSupportCategories() {
    let optionContent = `
        <option value="TEST1">TEST1</option>
        <option value="TEST2">TEST2</option>
        <option value="TEST3">TEST3</option>
    `;
    $("#slSupportCategory").empty();
    $("#slSupportCategory").append(optionContent);
}

/*function loadSubCategories(data = []) {
    let optionContent = ``;
    $("#slSubCategory").empty();
    $.each(data, function (index, item) {
        optionContent += `<option value="${item.id}">${item.subCategory}</option>`
    });
    $("#slSubCategory").append(optionContent);
    $(".subCategory").removeClass("d-none");
}*/

async function getCategories() {
    const categories = await fetchCategories();

    let optionContent = ``;
    for(const row of categories)
        optionContent += `<option value="${row.id}">${row.category}</option>`
    $("#slCategory").empty().append(optionContent);
    $(".category").removeClass("d-none");
}

async function getMainCategories() {
    const mainCategories = await $.ajax({
        url: '../api/notesAPI.php',
        data: {
            "action": "main"
        },
        dataType: 'json',
        method: 'GET'
    }).catch(e => {
        console.log(e);
        showNotification('error', 'Main Category list could not be loaded');
    });

    let optionContent = ``;
    for(const row of mainCategories)
        optionContent += `<option value="${row.id}">${row.mainCategory}</option>`
    $(".mainCategory").removeClass("d-none");
}



async function getSubCategories() {
    const subCategories = await fetchSubCategories();

    let optionContent = ``;
    $("#slSubCategory").empty();
    for(const row of subCategories)
        optionContent += `<option value="${row.id}">${row.subCategory}</option>`
    $("#slSubCategory").append(optionContent);
    $(".subCategory").removeClass("d-none");
}

async function getMissingCategories(){
    const missingCategories = await fetchMissingCategories();

    $("#slMissingCategory").empty();
    let optionContent = ``;
    for(const row of missingCategories){
        optionContent += `<option value="${row.id}">${row.missingCategory}</option>`
    }
    $("#slMissingCategory").append(optionContent);
}

async function getSubSupportCategories(categoryName = "ŞALTER", mainCategoryId = 1) {
    const subSupportCategories = await $.ajax({
        url: '../api/notesAPI.php',
        data: {
            "action": "subSupport",
            "categoryName": categoryName,
            "mainCategoryId": mainCategoryId,
        },
        dataType: 'json',
        method: 'GET'
    }).catch(e => {
        console.log(e);
        showNotification('error', 'Subcategory list could not be loaded');
    });

    let optionContent = ``;
    $("#slSubCategory").empty();
    for(const row of subSupportCategories)
        optionContent += `<option value="${row.id}">${row.subCategory}</option>`;
    $("#slSubCategory").append(optionContent);
    $(".subCategory").removeClass("d-none")
}

async function getSubEmergencyCategories(mainCategoryId = 1) {
    const subEmergencyCategories = await $.ajax({
        url: '../api/notesAPI.php',
        data: {
            "action": "subEmergency",
            "mainCategoryId": mainCategoryId,
        },
        dataType: 'json',
        method: 'GET'
    }).catch(e => {
        console.log(e);
        showNotification('error', 'SubEmergency Category list could not be loaded');
    });

    $("#slSubCategory").empty();
    let optionContent = ``;
    for(const row of subEmergencyCategories)
        optionContent += `<option value="${row.id}">${row.subCategory}</option>`

    $("#slSubCategory").append(optionContent);
    $(".subCategory").removeClass("d-none");
}

$('select#slPanoNos').on('select2:select', function (e) {
    const selectedPanelNumbers = $(this).val();
    console.log(selectedPanelNumbers);
    const projectNo = $('#btnProjectNo').html();
    if (selectedPanelNumbers && selectedPanelNumbers.length > 0) {
        getMaterialListByPanelItems(selectedPanelNumbers, projectNo);
    }
});

const loadMaterialList = function (dataSource = []) {
    let optionContent = ``;
    $("#slMaterialNos").empty();
    $.each(dataSource, function (index, item) {
        optionContent += `<option style="color:white !important;" value="${item.MaterialNumber}">${item.MaterialNumber} - ${item.MaterialDescription} </option>`
    });
    $("#slMaterialNos").append(optionContent);
};
const getMaterialListByPanelItems = async function (panelItems = [], projectNo = "") {
    const materialListByPanelItems = await $.ajax({
        url: '/assemblynotes/api/sharedAPI.php',
        data: {
            "action": "getMaterialList",
            "projectNo": projectNo,
            "panelItemsParam": JSON.stringify(panelItems)
        },
        dataType: 'json',
        method: 'GET'
    }).catch(e => {
        console.log(e)
        showNotification('error', 'Material list could not be loaded');
    });

    loadMaterialList(materialListByPanelItems);
};

$(document).ready(() => {
    $('#slMaterialNos').select2({
        dropdownParent: $('#insertModal .modal-body #divSlMaterialNos'),
        width: '100%',
        tags: true,
        placeholder: 'Material Number',
    });

    const projectNo = getUrlParameters()['ProjectNo'];
    const panelNo = getUrlParameters()['PanelNo'];

    const logWhatAdditional = (projectNo && panelNo) ? `; projectNo: ${projectNo}, panelNo: ${panelNo}` : "";
    saveLog("log_assembly_notes", "Details page(selection screen) access" + logWhatAdditional);

    if (!projectNo || !panelNo) return;

    //when projectNo and panelNo is defined in GET query string
    getOrderDetail(projectNo)
        .then((prom) => {
            if (prom === null)
                throw "Project is not found";
            getProjectProductDetails(projectNo)
        })
        .then(() => getOrderPanels(projectNo))
        .then(() => getProjectNotes(projectNo))
        .then(() => showProjectDetailsDiv())
        .then(() => clearInsertModal(panelNo))
        .then(() => openInsertModal(panelNo))
});

