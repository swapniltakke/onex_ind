function showProjectDetailsDiv() {
    document.querySelector('#projectDetails').setAttribute('style', 'display: block');
}

function hideProjectDetailsDiv() {
    document.querySelector('#projectDetails').setAttribute('style', 'display: none');
}

async function getOrderPanels(projectNo) {
    hideProjectDetailsDiv();
    const orderPanels = await $.ajax({
        url: `api/optionAPI.php`,
        data: {
            "action": "getOrderPanels",
            "projectNo": projectNo
        },
        dataType: 'json',
        type: 'GET'
    }).catch(e => {
        console.log(e);
        showNotification("error", "Unable to load data");
    });

    document.querySelector('#slPanoNos').innerHTML = "";
    for(const row of orderPanels){
        const {PosNo, LocationCode, TypicalCode} = {...row};
        const html = `<option value='${PosNo}'>${PosNo + '|' + LocationCode + '|' + TypicalCode}</option>`;
        document.querySelector('#slPanoNos').innerHTML += html;
    }
}

$("#orderSelect").select2({
    ajax: {
        url: `/assemblynotes/api/orderSelectboxAPI.php`,
        dataType: 'json',
        type: 'GET',
        data: function (params) {
            return {
                projectNo: params.term, // search term
            };
        },
        processResults: function (data, params) {
            data = $.map(data.items, function (obj) {
                return {
                    id: obj.project_no,
                    text: obj.name
                };
            });
            return {
                results: data
            };
        }
    },
    language: "tr",
    minimumInputLength: 3,
    cache: true
});

$('#orderSelect').on('select2:select', function (e) {
    $("#panelSelect").empty();
    window.history.pushState('object', document.title, location.href.split("?")[0]);
    const projectNo = e.params.data.id;

    saveLog("log_assembly_notes", `Details of ProjectNo ${projectNo} in details page(selection screen)`);

    if(!projectNo) return;

    getOrderPanels(projectNo)
        .then(() => getOrderDetail(projectNo))
        .then(() => getProjectProductDetails(projectNo))
        .then(() => getProjectNotes(projectNo))
        .then(() => showProjectDetailsDiv())
        .then(() => clearInsertModal())
});