const getOrderIssues = function (param, projectNoParam = '', statusCodeParam = '0', omStatusIdParam = 0) {
    $.ajax({
        url: `../api/orderIssuesAPI.php`,
        data: {
            "type": "list",
            "param": param,
            "projectNo": projectNoParam,
            "statusCode": statusCodeParam,
            "omStatusId": omStatusIdParam,
        },
        dataType: 'json',
        type: 'GET',
        success: function (data) {
            if (param === "all") {
                $("#orderSelect").val('').trigger("change");
                $("#panelSelect").val('').trigger("change");
                $("#issueCodeSelectFilter").val('').trigger("change");
                $("#statusFilter").val('').trigger("change");
            }
            getIssueWidgets();
            loadOrderIssues(data);
            saveLog("log_order_issues", `Order Issues:OrderNo Issues Page Access`);
        },
        error: function (err) {
            console.log(err);
            showNotification("error", "Data could not be loaded");
        }
    }).done(function () {
        const scroll_id = getUrlParameters()['id'];
        const $container = $("html,body");
        const $scrollTo = $(`li[data-id=${scroll_id}]`);
        if ($scrollTo.length) {
            $container.animate({
                scrollTop: $scrollTo.offset().top - $container.offset().top + $container.scrollTop(),
                scrollLeft: 0
            }, 1000);
        }
    });
};

const getIssueWidgets = function () {
    $.ajax({
        url: `../api/orderIssuesAPI.php`,
        data: {
            "type": "getStatusWidgets",
            "projectNo": $("#orderSelect").val()
        },
        dataType: 'json',
        type: 'GET',
        success: function (response) {
            if (response.data.length > 0) {
                loadWidgets(response.data);
            }
        },
        error: function (err) {
            console.log(err);
            showNotification("error", "Data could not be loaded");
        }
    });
};
const getIssueCodes = async function () {
    const issueCodes = await $.ajax({
        url: `../api/orderIssuesAPI.php`,
        data: {
            "type": "listIssueCodes"
        },
        dataType: 'json',
        type: 'GET'
    }).catch(e => {
        console.log(e);
        showNotification("error", "Error codes could not be loaded");
    });

    let issueCodesHTMLArray = [];
    $("#issueCodeSelect").empty().trigger("change");
    for (const value of issueCodes) {
        var newOption = new Option(value.Description, value.Id, false, false);
        issueCodesHTMLArray.push(newOption)
    }
    $('#issueCodeSelect').append(issueCodesHTMLArray);
    $('#issueCodeSelect').trigger('change');
};
const loadWidgets = function (data) {
    for (var i = 0, len = data.length; i < len; i++) {
        var widget = data[i];
        if (widget.StatusId == 1) {
            $("#openOrderIssueWidget").text(`${widget.Quantity} `)
        }
        if (widget.StatusId == 2) {
            $("#closeOrderIssueWidget").text(`${widget.Quantity} `)
        }
        if (widget.StatusId > 2) {
            $("#fixingOrderIssueWidget").text(`${widget.Quantity} `)
        }
    }
};

const getPanelNumbers = async function (orderNo) {
    const panelNumbers = await $.ajax({
        url: `../api/orderIssuesAPI.php`,
        data: {
            "type": "getPanelNumbers",
            "orderNo": orderNo
        },
        dataType: 'json',
        type: 'GET'
    }).catch(e => {
        console.log(e);
        showNotification("error", "Data could not be loaded");
    });

    $("#panelSelect").empty().trigger("change");
    let panelsHTMLArray = [];
    for (const panel of panelNumbers) {
        var newOption = new Option(panel, panel, false, false);
        panelsHTMLArray.push(newOption)
    }
    $('#panelSelect').append(panelsHTMLArray);
    $('#panelSelect').trigger('change');
};
const getOrderIssueById = function (param, isBelong = false) {
    if (isBelong) {
        $("#chechbox_hasLineStopEffect").prop("disabled", false);
    }
    if (param < 1 || !param) {
        showNotification("warning", "Data could not be loaded");
    } else {
        $.ajax({
            url: `../api/orderIssuesAPI.php`,
            data: {
                "type": "getItem",
                "param": param
            },
            dataType: 'json',
            type: 'GET',
            success: function (data) {
                openEditModal(data);
                saveLog("log_order_issues", `Order Issues:GET OrderNo Issue Id: ${param}`);
            },
            error: function (err) {
                console.log(err);
                showNotification("info", "Data could not be loaded");
            }
        });
    }
};
const openEditModal = function (entity) {

    $("#ReferenceCode").val("").trigger("change");
    $("#mailSelect").val("").trigger("change");
    $("#externalMailSelect").val("").trigger("change");
    $("#hdnOrderNoForInsertModal").val("");
    $("#hdnId").val(entity.Id).trigger("change");
    $("#hdnPanelNumberForInsertModal").val(entity.PanelNumber).trigger("change");
    $("#hdnOrderNoForInsertModal").val(entity.OrderNo).trigger("change");
    $("#note").val(entity.Note).trigger("change");
    $("#issueCodeSelect").val(entity.CodeId).trigger("change");
    $("#referenceCode").val(entity.ReferenceCode).trigger("change");
    getOrderDetail(entity.OrderNo);
    if (entity.hasLineStopEffect == 1) {
        $("#chechbox_hasLineStopEffect").prop("checked", true);
    }
    $("#referenceCode").attr("disabled", false);
    $("#insertModal").modal('show');
};
const changeStatus = function (id = 0) {
    const e = document.getElementById(`dropdownStatus-${id}`);
    const statusId = e.options[e.selectedIndex].value;
    $.ajax({
        url: `../api/orderIssuesAPI.php`,
        data: {
            "id": id,
            "statusId": statusId,
            "type": "setItemStatus"
        },
        dataType: 'json',  // <-- what to expect back from the PHP script, if anything
        type: 'GET',
        success: function (response) {
            if (response.code == 200) {
                getOrderIssues('all');
                saveLog("log_order_issues", `Order Issues:Project Issue Status Changed Id=${id}`);
                showNotification("success", response.message);
            } else {
                showNotification("error", response.message);
            }
        },
        error: function (err) {
            console.log(err);
            showNotification("error", JSON.parse(err.responseText).message);
        }
    });
};
const getDropdownComponent = function (itemData) {
    if (parseInt(itemData.ParentId) != 0) {
        return '';
    }
    if (itemData.isBelong == false) {
        return '';
    }
    let aStatusText = (itemData.StatusId == 1) ? "selected" : "";
    let kStatusText = (itemData.StatusId == 2) ? "selected" : "";
    let tStatusText = (itemData.StatusId == 3) ? "selected" : "";
    let orderManagerOption = "";

    // sipariş yöneticisiyse OM Cevapladı seçeneğini görebilir ya da konuyu açık duruma getirebilir.
    let statusDropdown = `<select class="form-control rounded rounded-3 col-lg-2 col-md-2 col-sm-2 mx-1 text-sm-left" 
style="height: 1.5rem; padding: 0px" 
id="dropdownStatus-${itemData.Id}" 
onchange="changeStatus(${itemData.Id})" title="Durum">
<option ${aStatusText} value="1">Open</option>
<option ${tStatusText} value="3">Rework</option>
<option ${kStatusText} value="2">Closed</option>
</select>`;
    return statusDropdown;
};
const getStatusBorderColor = function (statusId = 0, parentId = 0) {
    let borderColor = '';
    if (parentId > 0) {
        return borderColor;
    }
    switch (statusId) {
        case "1":
            borderColor = 'border-danger'; // Open
            break;
        case "2":
            borderColor = 'border-primary'; // Closed
            break;
        case "3":
            borderColor = 'border-success'; // Rework
            break;
        default:
            borderColor = '';
    }
    return borderColor;
};
const getActiveQuestionFromUrl = function (parentIdParam = 0, id = 0) {
    let idByUrl = getUrlParameters()["id"];
    let customStyleBg = 'background: #F1D5D9;';
    if (idByUrl == id) {
        return customStyleBg;
    }
    return '';
};
const getToAndCCMailsToolTipContent = function (itemData) {
    let toolTipObject = {
        "toMailsToolTip": "",
        "ccMailsToolTip": "",
        "toMailsToolTipContent": "",
        "ccMailsToolTipContent": ""
    };

    if (itemData.toMails) {
        // Tek eleman içeriyorsa split'e izin vermek amacıyla yapıldı
        if (itemData.toMails.indexOf(";") < 0) {
            itemData.toMails += ";";
        }
        if (itemData.toMails.indexOf(";") > -1) {
            // data-toggle="tooltip" data-placement="bottom" title="Rererans Kodu"
            const splitToMails = itemData.toMails.split(";");
            const lengthOfToMailsArr = splitToMails.length;
            for (let i = 0; i < lengthOfToMailsArr; i++) {
                if (splitToMails[i]) { // boş satır eklemesin
                    toolTipObject.toMailsToolTip += `${i + 1}.${splitToMails[i]}<br/>`
                }
            }
            toolTipObject.toMailsToolTipContent = `<span class="label label-warning" data-html="true"
    data-toggle="tooltip" data-placement="top" title="${toolTipObject.toMailsToolTip}">TO</span>`;
        }
    }
    if (itemData.ccMails) {
        // Tek eleman içeriyorsa split'e izin vermek amacıyla yapıldı
        if (itemData.ccMails.indexOf(";") < 0) {
            itemData.ccMails += ";";
        }
        if (itemData.ccMails.indexOf(";") > -1) {
            // data-toggle="tooltip" data-placement="bottom" title="Rererans Kodu"
            const splitCcMails = itemData.ccMails.split(";");
            const lengthOfCcMailsArr = splitCcMails.length;
            for (let i = 0; i < lengthOfCcMailsArr; i++) {
                if (splitCcMails[i]) { // boş satır eklemesin
                    toolTipObject.ccMailsToolTip += `${i + 1}. ${splitCcMails[i]}<br/>`
                }
            }
            toolTipObject.ccMailsToolTipContent = ` <span class="label label-warning-light" data-html="true"
    data-toggle="tooltip" data-placement="top" title="${toolTipObject.ccMailsToolTip}">CC</span>`;
        }
    }
    return toolTipObject;
};
const getOrderIssueItem = function (index, itemData) {
    const mailToolTipObj = getToAndCCMailsToolTipContent(itemData);
    let direction = "left";
    let codeReferenceContent = `${itemData.ReferenceCode}`;
    let hdnInputs = `<input id="hdnToMails-${index}" value="${itemData.toMails}" type="hidden" />
    <input id="hdnCcMails-${index}" value="${itemData.ccMails}" type="hidden" />`;
    let codeReferenceItem = `<span class="btn btn-xs btn-rounded btn-outline-success" data-toggle="tooltip" data-placement="bottom" title="Rererans Kodu">${codeReferenceContent}</span>`;

    if (!itemData.ReferenceCode) {
        codeReferenceItem = '';
    }
    const isOmAnswerContent = ((itemData.isOrderManager == true && parseInt(itemData.ParentId) == 0) ?
        `<input type="checkbox" ${itemData.isRepliedByOM} onchange="changeOmStatus(${itemData.Id}, this)"
           data-toggle="tooltip" data-placement="bottom" title="OM Cevapladı Mı" id="isAnsweredByOm" >` :
        "");

    let oppositeDirection = "right";
    let gallery = "";
    let IdLabel = `<span class="label label-info rounded align-middle px-2 mx-1" data-toggle="tooltip" data-placement="bottom" title="Sorun No" style="font-size: 20px;">${itemData.QNumber}</span>`;
    let statusDropdown = getDropdownComponent(itemData);

    let codeIssueItem = ``;
    if (itemData.IssueCode) {
        codeIssueItem = `<span class="btn btn-xs btn-rounded btn-outline-warning" data-toggle="tooltip" data-placement="bottom" title="">
${itemData.IssueCode}</span>`;
    }
    let generalInfoContent = `${codeIssueItem} 
  <span class="float-${oppositeDirection} btn btn-sm btn-outline-info mx-1 text-center">${itemData.OrderNo}/${itemData.PanelNumber} 
  ${isOmAnswerContent}
  </span>`;

    const hasLineStopEffectCheckContent = ((itemData.isBelong == true && parseInt(itemData.ParentId) == 0) ?
        `<input type="checkbox"  ${itemData.hasLineStopEffectCheck} onchange="changeLineStopEffect(${itemData.Id}, this)"
           data-toggle="tooltip" data-placement="bottom" title="Hatta Duruş Etkisi Var mı ?" id="hasLineStopEffect" >` :
        "");
    const hasLineStopEffectContent = ((itemData.hasLineStopEffect == true && parseInt(itemData.ParentId) == 0) ?
        ` <span style="font-size: 20px;height: fit-content;" class="text-lg-center btn btn-sm btn-danger mx-1 text-center"> 
        Line Stopage ${hasLineStopEffectCheckContent}</span>` :
        "");

    $.each(itemData.files, function (index, fileItem) {
        let file_path = fileItem.path.replaceAll("\\", "\\\\");
        let galleryItem = `<div style="display: inline-flex;flex-direction: column;">
        <a class="close" aria-label="close" 
        href="/lvfolder/index.php?fileName=${fileItem.name}&projectNumber=${itemData.OrderNo}&id=${itemData.Id}" 
        title="${fileItem.name}" 
        data-gallery="#${itemData.Id}">
        <img width="75" height="75" alt="${fileItem.name}" src="/lvfolder/index.php?fileName=${fileItem.name}&projectNumber=${itemData.OrderNo}&id=${itemData.Id}">
        </a>`;
        if (itemData.isBelong == true) {
            galleryItem += `<button class="btn btn-sm btn-danger" onclick="deleteImage('${file_path}',$(this))">Sil</button>`;
        }
        gallery = gallery + galleryItem + '</div>';
    });
    let actionButtons = `
<button data-toggle="tooltip" data-placement="bottom" title="Comment" data-id="${itemData.Id}" onclick="openAnswerModal(
    ${itemData.Id},
${itemData.OrderNo},
'${itemData.PanelNumber}',
'${itemData.ReferenceCode}',
${itemData.CodeId},
'${itemData.Created_By}', ${itemData.QNumber})" class="float-${oppositeDirection} btn btn-xs btn-success"><i class="fa fa-reply"></i></button>`;
    // child konuşmalara yorum yapılmasının önüne geçilir
    if (parseInt(itemData.ParentId) != 0) {
        actionButtons = "";
    }
    if (itemData.isBelong == true && parseInt(itemData.ParentId) == 0) {

        actionButtons += `<span data-toggle="tooltip" data-placement="bottom" title="Delete" data-id="${itemData.Id}" onclick="openConfirmModal(${itemData.Id},${itemData.ParentId},${itemData.OrderNo})" class="float-${oppositeDirection} btn btn-xs btn-danger mx-1"><i class="fa fa-trash"></i></span>
                            <span data-toggle="tooltip" data-placement="bottom" title="Edit" data-id="${itemData.Id}" onclick="getOrderIssueById(${itemData.Id}, ${itemData.isBelong})" class="float-${oppositeDirection} btn btn-xs btn-warning"><i class="fa fa-edit"></i></span>`
    }

    let generalInfoItem = `<div class="text-center d-flex" style="height: 1.5rem">
  ${IdLabel}
  ${statusDropdown}
  ${generalInfoContent}
  ${codeReferenceItem}
  ${hasLineStopEffectContent}
</div>`;
    if (parseInt(itemData.ParentId) != 0 && itemData.isBelong == true) {
        actionButtons = `<span data-toggle="tooltip" data-placement="bottom" title="Delete comment" data-id="${itemData.Id}" onclick="openConfirmModal(${itemData.Id},${itemData.ParentId},${itemData.OrderNo})" class="float-${oppositeDirection} btn btn-xs btn-danger mx-1"><i class="fa fa-trash"></i></span>`;
    }
    let datePhraseContent = `${itemData.Created_At}`;
    if (itemData.StatusId == 2) {
        datePhraseContent = `${itemData.Created_At} -  ${itemData.closed_At}`;
    }
    let orderIssueItem = ` <div class="chat-element" id="chat-element-${itemData.Id}">
 <div class="d-none">omStatusId=${itemData.OmStatusId}</div>
                                    <a href="#" class="float-${direction}">
                                        <img alt="image" class="rounded-circle" src="/users/index.php?&gid=${itemData.Created_By_Gid}">
                                    </a>
                                    <div class="media-body text-${direction}">                           
    ${actionButtons}                                   
                                        <strong>${itemData.Created_By}</strong>
                                        <p class="m-b-xs">
                                            ${itemData.Note}
                                        </p>
                                        <small class="text-muted">${datePhraseContent}</small>
                                        ${mailToolTipObj.toMailsToolTipContent}
                                       ${mailToolTipObj.ccMailsToolTipContent}
                                        <div class="lightBoxGallery"> ${gallery}
                        </div>
                                    </div>
                                </div>`;

    let inner_text = `<li class="dd-item" data-id="${index}">
  <div class="dd-handle ${getStatusBorderColor(itemData.StatusId, itemData.ParentId)}" 
  style="${getActiveQuestionFromUrl(itemData.ParentId, itemData.Id)}">
${hdnInputs}
  ${generalInfoItem}
  ${orderIssueItem}
   <div id="blueimp-gallery" class="blueimp-gallery">
                                <div class="slides"></div>
                                <h3 class="title"></h3>
                                <a class="prev">‹</a>
                                <a class="next">›</a>
                                <a class="close">×</a>
                                <a class="play-pause"></a>
                                <ol class="indicator"></ol>
                            </div>

        </div></li>`;
    return inner_text;
};
const loadOrderIssues = function (data) {
    let itemOl = "";
    let tempStr = "";
    $("#olOrderIssues").empty();
    $.each(data, function (index, parent) {
        let parentId = 0;
        tempStr = "";
        $.each(parent, function (ind, child) {
            if (child.ParentId == 0) {
                const generatedItem = getOrderIssueItem(child.Id, child);
                $("#olOrderIssues").append(generatedItem);
            } else {
                parentId = child.ParentId;
                const itemLi = getOrderIssueItem(child.Id, child);
                tempStr = tempStr.concat(" ", itemLi);
            }
        });
        if (parentId > 0) {
            itemOl = `<ol class="dd-list" style="">${tempStr}</ol>`;
            $(`#chat-element-${parentId}`).append(itemOl);
        }

    });
};


const getOrderDetail = function (orderNo = '') {
    $.ajax({
        url: `../api/orderIssuesAPI.php`,
        data: {
            "type": "getOrderDetail",
            "orderNo": orderNo
        },
        dataType: 'json',
        type: 'GET',
        success: function (data) {
            if (data) {
                console.log(data.OMEmail);
                $("#inpOrderManager").val(data.OMEmail).trigger('change');
            } else {
                $("#inpOrderManager").val("Sipariş Yöneticisi Bulunmamaktadır.").trigger('change');
            }
        },
        error: function (err) {
            console.log(err);
            showNotification("error", "Proje detayı yüklenemedi.");
        }
    });
};
const loadMails = function (mailList) {
    $("#mailSelect").empty().trigger("change");

    let mailUsersHTMLArray = [];

    for (const value of mailList) {
        var newOption = new Option(value.mail, value.mail, false, true);
        mailUsersHTMLArray.push(newOption)
    }

    $('#mailSelect').append(mailUsersHTMLArray);
    $('#mailSelect').trigger('change');
};
const loadExternalMails = function (mailList) {
    let mailUsersHTMLArray = [];
    $("#externalMailSelect").empty().trigger("change");
    for (const value of mailList) {
        var newOption = new Option(value.mail, value.mail, false, false);
        mailUsersHTMLArray.push(newOption)
    }
    $('#externalMailSelect').append(mailUsersHTMLArray);
    $('#externalMailSelect').trigger('change');
};

const getOneXMailList = function (param) {
    $.ajax({
        url: `/assemblynotes/api/orderIssuesAPI.php`,
        data: {
            "type": "listOneXUserMails",
            "param": param
        },
        dataType: 'json',
        type: 'GET',
        success: function (data) {
            loadExternalMails(data.items);
            loadMails(data.items);
        }
    }).catch(e => {
        console.log(e);
        showNotification("error", "User emails could not be loaded");
    });
};

$('#orderSelect').on('select2:select', function (e) {
    const orderNo = e.params.data.id;
    getPanelNumbers(orderNo);
});
$('.custom-file-input').on('change', function () {
    let fileName = $(this).val().split('\\').pop();
    $(this).next('.custom-file-label').addClass("selected").html(fileName);
});

jQuery(document).ready(function () {
    saveLog("log_order_issues", "Order Issues:Order Issues page access");
    getIssueWidgets();
    $(function () {
        $("[rel='tooltip']").tooltip();
    });
    const projectNoParam = getUrlParameters()['orderNo'];
    const idParam = getUrlParameters()['id'];
    if (projectNoParam) {
        getOrderIssues({'id': idParam}, projectNoParam);
    } else {
        getOrderIssues('all');
    }
    getIssueCodes();
    getOneXMailList();
});
const cleanInsertModal = function () {
    $("#titleProjectNo").text(" ");
    $("#mailSelect").val("").trigger("change");
    $("#externalMailSelect").val("").trigger("change");
    $("#inpOrderManager").val("").trigger('change');
    $("#hdnId").val("").trigger('change');
    $("#note").val("").trigger('change');
    $("#hdnParentId").val("").trigger('change');
    $("#hdnOrderNoForInsertModal").val("").trigger('change');
    $(".custom-file-input").val("").trigger('change');
    $("#hdnDeactiveId").val("").trigger('change');
    $("#hdnDeactiveOrderNo").val("").trigger('change');
    $("#referenceCode").val("").trigger('change');
    $('#chechbox_hasLineStopEffect').prop('checked', false);
};
const openInsertModal = function () {
    $("#referenceCode").attr("disabled", false);
    $("#chechbox_hasLineStopEffect").attr("disabled", false);
    $("#issueCodeSelect").attr("disabled", false);
    const orderNo = $("#orderSelect").val();
    const panelNumber = $("#panelSelect").val().join(",");
    cleanInsertModal();

    if (!orderNo) {
        showNotification("warning", "Project no can not be empty");
        return;
    }

    if (!panelNumber) {
        showNotification("warning", "Panel number can not be empty");
        return;
    }


    if (orderNo && panelNumber) {

        saveLog("log_order_issues", "Order Issues:Clicked start new conversation button on order issues page");
        $.ajax({
            url: `../questionManagement/questionManagementAPI.php`,
            data: {
                "searchedProject": orderNo,
                "action": "checkOrderRule"
            },
            dataType: 'json',  // <-- what to expect back from the PHP script, if anything
            type: 'GET',
            success: function (response) {
                if (response.state) {
                    $("#hdnOrderNoForInsertModal").val(orderNo).trigger('change');
                    $("#hdnPanelNumberForInsertModal").val(panelNumber).trigger('change');
                    getOrderDetail(orderNo);
                    $("#insertModal").modal('show');
                } else if (!response.state && response.data != 0) {
                    showNotification("warning", `Creating a question for this order is not allowed. Create a question on ${response.data} list.`);
                } else {
                    showNotification("warning", `Rule list has not been prepared. Contact with OM`);
                    $("#hdnOrderNoForInsertModal").val(orderNo).trigger('change');
                    $("#hdnPanelNumberForInsertModal").val(panelNumber).trigger('change');
                    getOrderDetail(orderNo);
                    $("#insertModal").modal('show');
                }
            },
            error: function (err) {
                console.log("error", err);
                showNotification("warning", `An error occurred`);
            }
        });
    }
};

const openConfirmModal = function (id, parentId, OrderNo = "") {
    if (parentId == 0) {
        $("#confirmModalBody").append(`<span class="text-danger">You are about to delete root of the conversation. If you delete this, all of its answers will also be gone.</span>`)
    }
    $("#hdnDeactiveId").val(id);
    $("#hdnDeactiveOrderNo").val(OrderNo);
    $("#confirmModal").modal('show');
};
const openAnswerModal = function (id = 0, orderNo = "", panelNumber = "", referenceCode = "", issueCodeId = "", createdByMail = "", qNumber = 0) {
    let toMailsArr = $("#hdnToMails-" + id).val().split(";");
    let ccMailsArr = $("#hdnCcMails-" + id).val().split(";");
    if (createdByMail.indexOf('siemens.com') > -1) {
        toMailsArr.push(createdByMail);
    }

    let titleModal = orderNo + "-" + qNumber + " Nolu";
    cleanInsertModal();
    $("#hdnOrderNoForInsertModal").val(orderNo).trigger('change');
    $("#hdnPanelNumberForInsertModal").val(panelNumber).trigger('change');
    $("#hdnParentId").val(id).trigger('change');
    $("#referenceCode").val(referenceCode).trigger('change');
    $("#issueCodeSelect").val(issueCodeId).trigger('change');
    $("#titleProjectNo").text(titleModal);
    getOrderDetail(orderNo);
    $("#mailSelect").val(toMailsArr).trigger('change');
    $("#externalMailSelect").val(ccMailsArr).trigger('change');

    $("#referenceCode").attr("disabled", true);
    $("#chechbox_hasLineStopEffect").attr("disabled", true);
    $("#issueCodeSelect").attr("disabled", true);

    $("#insertModal").modal('show');

};
const getAndCheckOrderIssueObject = function () {
    const objectOrderIssue = {
        "note": $("#note").val(),
        "orderNo": $("#hdnOrderNoForInsertModal").val(),
        "id": ($("#hdnId").val() ?? 0),
        "parentId": ($("#hdnParentId").val() ?? 0),
        "orderManagerMail": $("#inpOrderManager").val(),
        "toMails": $("#mailSelect").val() ?? [],
        "ccMails": $("#externalMailSelect").val() ?? [],
        "codeId": $("#issueCodeSelect").val() ?? 10,
        "referenceCode": $("#referenceCode").val() ?? "",
        "panelNumber": $("#hdnPanelNumberForInsertModal").val(),
        "hasLineStopEffect": $("#chechbox_hasLineStopEffect").prop('checked')
    };

    const indexOfMailAddr = objectOrderIssue.orderManagerMail.indexOf("@siemens.com");

    if (!objectOrderIssue.orderManagerMail || indexOfMailAddr < 0) {
        showNotification("warning", "OM could not be found");
        return false;
    } else if (!objectOrderIssue.panelNumber) {
        showNotification("warning", "Panel number can not be empty");
        return false;
    } else if (!objectOrderIssue.note) {
        showNotification("warning", "Note can not be empty");
        return false;
    } else if (!objectOrderIssue.codeId) {
        showNotification("warning", "Issue code can not be empty");
        return false;
    } else if (!objectOrderIssue.orderNo) {
        showNotification("warning", "Project no can not be empty");
        return false;
    } else if (objectOrderIssue.note.indexOf("/") > -1 || objectOrderIssue.note.indexOf("'") > -1 || objectOrderIssue.note.indexOf("<") > -1 || objectOrderIssue.note.indexOf(">") > -1) {
        showNotification("warning", "Note can not contain special characters (/ ' < >)");
        return false;
    } else if (objectOrderIssue.referenceCode.indexOf("/") > -1 || objectOrderIssue.referenceCode.indexOf("'") > -1 || objectOrderIssue.referenceCode.indexOf("<") > -1 || objectOrderIssue.referenceCode.indexOf(">") > -1) {
        showNotification("warning", "Reference can not contain special characters (/ ' < >)");
        return false;
    } else {
        var file_datas = $('#orderIssueAttachments').prop('files');
        var form_data = new FormData();
        //objectOrderIssue.note = getIssueNotByNc();
        $.each(file_datas, function (index, file) {
            form_data.append(`file-${index}`, file);
        });
        form_data.append('entity', JSON.stringify(objectOrderIssue));
        form_data.append('type', 'save');
        return form_data;
    }
};

const saveOrderIssue = function () {
    const form_data = getAndCheckOrderIssueObject();
    if (!form_data)
        return;

    $('#insert_modal_content').toggleClass('sk-loading');
    $.ajax({
        url: `../api/orderIssuesAPI.php`,
        data: form_data,
        dataType: 'text',  // <-- what to expect back from the PHP script, if anything
        cache: false,
        contentType: false,
        processData: false,
        type: 'POST',
        success: function (response) {
            const parsedResponse = JSON.parse(response);
            if (parsedResponse.code == 200) {
                filterIssues();
                saveLog("log_order_issues", `Order Issues:Project Issue saved`);
                showNotification("success", parsedResponse.message);
            } else {
                showNotification("error", parsedResponse.message);
            }
        }
    }).then(function (data) {
        cleanInsertModal();
        $("#insertModal").modal('hide');
        $('#insert_modal_content').toggleClass('sk-loading');
    }).catch(e => {
        console.log(e)
        showNotification("error", "Error on creating conversation");
    });
};

const deactiveOrderIssue = function () {
    const id = $("#hdnDeactiveId").val();
    const projectNoParam = $("#hdnDeactiveOrderNo").val();
    if (id && id > 0) {
        $.ajax({
            url: `../api/orderIssuesAPI.php`,
            data: {
                "param": id,
                "type": "deactive"
            },
            dataType: 'json',  // <-- what to expect back from the PHP script, if anything
            type: 'GET',
            success: function (response) {
                if (response.code == 200) {
                    getOrderIssues(null, projectNoParam);
                    saveLog("log_order_issues", `Order Issues:Project Issue removed Id=${id}`);
                    showNotification("success", response.message);
                } else {
                    showNotification("error", response.message);
                }
            },
            error: function (err) {
                console.log(err);
                showNotification("error", JSON.parse(err.responseText).message);
            }
        }).then(function () {
            cleanInsertModal();
            $("#confirmModal").modal('hide');
        });
    } else {
        showNotification("warning", "Data requested to be deleted could not be found");
    }
};
const filterIssues = function () {
    let statusId = 0;
    let omStatusId = 0;
    const e = document.getElementById("statusFilter");
    const selectedItem = e.options[e.selectedIndex];
    const projectNoParam = getUrlParameters()['orderNo'];
    const idParam = getUrlParameters()['id'];
    if (selectedItem) {
        statusId = e.options[e.selectedIndex].value;
        if (statusId > 3) {
            omStatusId = statusId;
            statusId = null;
        }
    }
    let projectNo = $("#orderSelect").val();
    if (!projectNo) {
        projectNo = projectNoParam;
    }
    getOrderIssues(idParam, projectNo, statusId, omStatusId);
    getIssueWidgets();
};

const changeLineStopEffect = function (id = 0, item) {
    const isChecked = $(item).prop('checked');
    const intValue = (isChecked == true) ? 1 : 0;

    if (id > 0) {
        $.ajax({
            url: `../api/orderIssuesAPI.php`,
            data: {
                "id": id,
                "hasLineStop": intValue,
                "type": "setLineStopEffect"
            },
            dataType: 'json',  // <-- what to expect back from the PHP script, if anything
            type: 'GET',
            success: function (response) {
                if (response.code == 200) {
                    filterIssues();
                    saveLog("log_order_issues", `Order Issues:Has Line Stop Effect Id = ${id} State = ${intValue}`);
                    showNotification("success", response.message);
                } else {
                    showNotification("error", response.message);
                }
            },
            error: function (err) {
                console.log(err);
                showNotification("error", JSON.parse(err.responseText).message);
            }
        });
    } else {
        showNotification("error", "Error on identifying conversation id.");
    }
};
const changeOmStatus = function (id = 0, item) {
    const isChecked = $(item).prop('checked');
    const intValue = (isChecked == true) ? 1 : 0;

    if (id > 0) {
        $.ajax({
            url: `../api/orderIssuesAPI.php`,
            data: {
                "id": id,
                "omStatusId": intValue,
                "type": "setOmStatus"
            },
            dataType: 'json',  // <-- what to expect back from the PHP script, if anything
            type: 'GET',
            success: function (response) {
                if (response.code == 200) {
                    filterIssues();
                    saveLog("log_order_issues", `Order Issues:Project OM Status Changed Id=${id}`);
                    showNotification("success", response.message);
                } else {
                    showNotification("error", response.message);
                }
            },
            error: function (err) {
                console.log(err);
                showNotification("error", JSON.parse(err.responseText).message);
            }
        });
    } else {
        showNotification("error", "Konuşma Id numarası belirlenemedi. İşlem iptal edildi.");
    }
};
$("#inputSearch").on("keyup", function () {
    var value = $(this).val().toLowerCase();
    $("#parentDivOrderIssues .dd-item").filter(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
});

const deleteImage = function (file_path, item) {
    swal.fire({
        title: "Bu dosyayı silmek istediğinize emin misiniz?",
        text: "Sildikten sonra dosyanın geri getirilmesi mümkün olmayacaktır!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Sil!",
        cancelButtonText: "Vazgeç",
    }).then((e) => {
        if (e.value) {
            $.ajax({
                url: `../api/orderIssuesAPI.php`,
                data: {
                    "type": "deleteImage",
                    "file_path": JSON.stringify(file_path)
                },
                type: 'POST',
                success: function (data) {
                    swal.fire("Silindi!", "Dosyanız silinmiştir.", "success");
                    $(item).parent().addClass("d-none")
                },
                error: function (err) {
                    console.log(err);
                    showNotification("error", "Dosya silinemedi.");
                }
            });
        } else {
            swal.fire("İptal Edildi", "Dosya silme işlemi iptal edildi", "error");
        }
    });
};


$("#panelSelect").select2({
    width: '100%'
});
$("#issueCodeSelectFilter").select2({
    width: '100%',
    placeholder: 'Error Codes',
    allowClear: true
});
$("#issueCodeSelect").select2({
    dropdownParent: $('#insertModal .modal-body'),
    width: '100%',
    placeholder: 'Error Codes',
});
$("#mailSelect").select2({
    dropdownParent: $('#insertModal .modal-body'),
    width: '100%',
    placeholder: 'Mail To',
});

$("#externalMailSelect").select2({
    dropdownParent: $('#insertModal .modal-body'),
    width: '100%',
    placeholder: 'Mail CC',
});

