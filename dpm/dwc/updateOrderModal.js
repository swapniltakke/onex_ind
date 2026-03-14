async function getNoteData(id) {
    // Check if ID is valid
    if (!id) {
        console.warn("No note ID provided to getNoteData");
        return Promise.reject(new Error("No note ID provided"));
    }
    
    console.log("Getting note data for ID:", id);
    
    return $.ajax({
        url: `api/notesAPI.php`,
        type: 'GET',
        data: {
            "action": "getNoteData",
            "id": id
        },
        success: function(data) {
            console.log("Note data received:", data);
            if (!data) {
                console.warn("Empty note data received for ID:", id);
            }
            return data;
        },
        error: function(xhr, status, error) {
            console.error("Error getting note data:");
            console.error("Status:", status);
            console.error("Error:", error);
            console.error("Response:", xhr.responseText);
            showNotification("error", "Project note data could not be loaded");
            throw new Error("Failed to load note data");
        }
    }).catch(e => {
        console.error("Exception in getNoteData:", e);
        showNotification("error", "Project note data could not be loaded");
        throw e; // Re-throw to propagate the error
    });
}

async function fetchCategories(){
    return await $.ajax({
        url: 'api/notesAPI.php',
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
        url: 'api/notesAPI.php',
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
        url: 'api/notesAPI.php',
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

async function openUpdateModal(event, id) {
    console.log("Opening update modal for note ID:", id);
    
    // Check if ID is valid
    if (!id) {
        console.warn("No note ID provided to openUpdateModal");
        return; // Exit early if no ID
    }
    
    // Store the button element if event exists
    const button = event && event.target ? event.target : null;
    
    // Update button state if it exists
    if (button) {
        button.innerText = "Loading...";
        button.classList.add("disabled");
    }
    
    try {
        // Get note data
        const noteData = await getNoteData(id);
        
        if (!noteData) {
            throw new Error("No note data returned");
        }
        
        const projectNo = $('#btnProjectNo').html() || noteData.projectNo;
        const panelNo = getUrlParameters()['PanelNo'] || noteData.panelno;
        
        $("#hdnPanelNo").val(panelNo).trigger('change');
        $("#hdnId").val(noteData.id).trigger('change');
        
        // Set the value in the CKEditor instance if it exists
        if (window.editor) {
            window.editor.setData(noteData.note || '');
        } else {
            console.warn("CKEditor instance not found");
            $("#noteTextarea").val(noteData.note || '');
        }
        
        $("#updateModalStatus").val(noteData.notestatus).trigger('change');
        
        // Set project and panel info
        if ($("#updateModalProjectNoSpan").length) {
            $("#updateModalProjectNoSpan").text(noteData.projectNo || projectNo);
        }
        
        if ($("#updateModalPanelNoSpan").length) {
            $("#updateModalPanelNoSpan").text(noteData.panelno || panelNo);
        }
        
        // Load categories with error handling
        try {
            const missingCategories = await fetchMissingCategories();
            let optionContent = `<option value="">Select Category</option>`;
            
            for(const row of missingCategories) {
                optionContent += `<option value="${row.id}">${row.missingCategory}</option>`;
            }
            
            $("#updateModalMissingCategory").empty().append(optionContent);
            $("#updateModalMissingCategory").val(noteData.missingcategoryid);
        } catch (error) {
            console.error("Error loading missing categories:", error);
        }
        
        try {
            const categories = await fetchCategories();
            let optionContent = `<option value="">Select Category</option>`;
            
            for(const row of categories) {
                optionContent += `<option value="${row.id}">${row.category}</option>`;
            }
            
            $("#updateModalCategory").empty().append(optionContent);
            $("#updateModalCategory").val(noteData.categoryid);
        } catch (error) {
            console.error("Error loading categories:", error);
        }
        
        try {
            const subCategories = await fetchSubCategories();
            let optionContent = `<option value="">Select Subcategory</option>`;
            
            for(const row of subCategories) {
                optionContent += `<option value="${row.id}">${row.subCategory}</option>`;
            }
            
            $("#updateModalSubCategory").empty().append(optionContent);
            $("#updateModalSubCategory").val(noteData.subcategoryid);
        } catch (error) {
            console.error("Error loading subcategories:", error);
        }
        
        // Show the modal
        $("#updateModal").modal('show');
        
        // Log the action
        if (typeof saveLog === 'function') {
            saveLog("log_assembly_notes", `Opened updateNoteModal; projectNo: ${projectNo}, panelNo: ${panelNo}, noteId: ${id}`);
        }
        
    } catch (error) {
        console.error("Error in openUpdateModal:", error);
        // Only show notification if this was triggered by a user action (button click)
        if (button) {
            showNotification("error", "Could not load note data: " + error.message);
        }
    } finally {
        // Always restore button state
        if (button) {
            button.innerText = "Update";
            button.classList.remove("disabled");
        }
    }
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
        url: 'api/notesAPI.php',
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