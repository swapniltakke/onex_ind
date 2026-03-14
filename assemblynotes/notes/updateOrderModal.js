async function getNoteData(id){
    return $.ajax({
        url: `../api/notesAPI.php`,
        type: 'GET',
        data: {
            "action": "getNoteData",
            "id": id
        }
    }).catch(e => {
        console.log(e)
        showNotification("error", "Project note data could not be loaded");
    });
}

async function fetchCategories(){
    return await $.ajax({
        url: '../api/notesAPI.php',
        data: {
            "action": "categories"
        },
        dataType: 'json',
        method: 'GET'
    }).catch(e => {
        console.log(e);
        showNotification('error', 'Category list could not be loaded');
    });
}

async function fetchSubCategories(){
    return await $.ajax({
        url: '../api/notesAPI.php',
        data: {
            "action": "sub"
        },
        dataType: 'json',
        method: 'GET'
    }).catch(e => {
        console.log(e);
        showNotification('error', 'Subcategory list could not be loaded');
    });
}


async function fetchMissingCategories(){
    return await $.ajax({
        url: '../api/notesAPI.php',
        data: {
            "action": "getMissingCategories"
        },
        dataType: 'json',
        method: 'GET'
    }).catch(e => {
        console.log(e);
        showNotification('error', 'Subcategory list could not be loaded');
    });
}

async function openUpdateModal(event, id){
    event.target.innerText = "Loading...";
    event.target.classList.add("disabled");

    const noteData = await getNoteData(id);

    const projectNo = $('#btnProjectNo').html();
    const panelNo = getUrlParameters()['PanelNo'];
    $("#hdnPanelNo").val(panelNo).trigger('change');
    $("#hdnId").val(noteData.id).trigger('change');

    // Set the value in the CKEditor instance
    window.editor.setData(noteData.note);

    $("#updateModalStatus").val(noteData.notestatus).trigger('change');

    updateModalProjectNoSpan.innerText = noteData.projectNo;
    updateModalPanelNoSpan.innerText = noteData.panelno;

    const missingCategories = await fetchMissingCategories();

    let optionContent = ``;
    for(const row of missingCategories)
        optionContent += `<option value="${row.id}">${row.missingCategory}</option>`
    $("#updateModalMissingCategory").empty().append(optionContent);
    updateModalMissingCategory.value = noteData.missingcategoryid;

    const categories = await fetchCategories();
    optionContent = ``;
    for(const row of categories)
        optionContent += `<option value="${row.id}">${row.category}</option>`
    $("#updateModalCategory").empty().append(optionContent);
    updateModalCategory.value = noteData.categoryid;

    const subCategories = await fetchSubCategories();
    optionContent = ``;
    for(const row of subCategories)
        optionContent += `<option value="${row.id}">${row.subCategory}</option>`
    $("#updateModalSubCategory").empty().append(optionContent);
    updateModalSubCategory.value = noteData.subcategoryid;

    event.target.innerText = "Update";
    event.target.classList.remove("disabled");

    $("#updateModal").modal('show');
    saveLog("log_assembly_notes", `Opened updateNoteModal in details page; projectNo: ${projectNo}, panelNo: ${panelNo}`);
}

function updateOrderNote(pageName) {
    const projectNo = $('#updateModalProjectNoSpan').html();
    const category = $('#updateModalCategory').val();
    const subCategory = $('#updateModalSubCategory').val();
    const missingCategory = $('#updateModalMissingCategory').val();
    const note = window.editor.getData(); // Get the note value from the CKEditor instance
    const noteStatus = $('#updateModalStatus').val();

    if (!note.trim()) { // Check if the note is empty after trimming whitespace
        showNotification("warning", "Note can not be empty");
        return;
    }

    const id = $("#hdnId").val();
    $.ajax({
        url: '../api/notesAPI.php',
        method: 'POST',
        data: {
            "action": "edit",
            "id": id,
            "category": category,
            "subCategory": subCategory,
            "missingCategory": missingCategory,
            "noteStatus": noteStatus,
            "note": note
        },
        success: function () {
            saveLog("log_assembly_notes", `success on updateOrderNote in details page; projectNo: ${projectNo}, ID: ${id}, note: ${note}, noteStatus: ${noteStatus}`);
            showNotification('success', "Successfully updated");
            if(pageName === "detail"){
                $(`#dtOrderNotes`).dataTable().fnDestroy();
                getProjectNotes(projectNo);
            }
            else{
                $('#table_open_items').DataTable().ajax.reload(null, false);
            }

            $("#updateModal").modal('hide');
        },
        error: function (errResponse) {
            saveLog("log_assembly_notes", `error on updateOrderNote in details page; projectNo: ${projectNo}, ID: ${id}, note: ${note}, noteStatus: ${noteStatus}`);
            showNotification('error', "An error occurred");
        }
    });
}