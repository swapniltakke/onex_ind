async function getDtoData(id){
    return $.ajax({
        url: `api/DTOController.php`,
        type: 'GET',
        data: {
            "action": "getDtoData",
            "id": id
        }
    }).catch(e => {
        console.log(e)
        showNotification("error", "This DTO data could not be loaded");
    });
}

async function getUpdateData(id){
    return $.ajax({
        url: `api/DTOController.php`,
        type: 'GET',
        data: {
            "action": "getUpdateData",
            "id": id
        }
    }).catch(e => {
        console.log(e)
        showNotification("error", "This DTO data could not be loaded");
    });
}

async function getUpdateMatRegister(id){
    return $.ajax({
        url: `api/DTOController.php`,
        type: 'GET',
        data: {
            "action": "getUpdateMatRegister",
            "id": id
        }
    }).catch(e => {
        console.log(e)
        showNotification("error", "This DTO data could not be loaded");
    });
}

async function getImage(docNo) {
    return $.ajax({
        url: 'api/DTOController.php',
        type: 'GET',
        data: {
            "action": "getImage",  
            "docNo": docNo        
        }
    }).catch(e => {
        console.log(e);
        showNotification("error", "This image could not be loaded");
    });
}

async function getDocs(docNo) {
    return $.ajax({
        url: 'api/DTOController.php',  
        type: 'GET',
        data: {
            "action": "getDocs",  
            "docNo": docNo         
        }
    }).catch(e => {
        console.log(e);
        showNotification("error", "Files could not be loaded");
    });
}

async function openUpdateModal(event, id){
    event.target.innerText = "Loading...";
    event.target.classList.add("disabled");
//alert(id);
    const breakerData = await getDtoData(id);

    $("#docNo2").val(breakerData.docNo2).trigger('change');
    $("#matNo").val(breakerData.matNo).trigger('change');
    $("#dMat").val(breakerData.dMat).trigger('change');
    $("#quanTt").val(breakerData.quanTt).trigger('change');
    $("#descRp").val(breakerData.descRp).trigger('change');
    $("#kMat").val(breakerData.kMat).trigger('change');

    populateTable(breakerData);

    event.target.innerText = "Offer";
    event.target.classList.remove("disabled");

    $("#updateModal").modal('show');
    saveLog("log_assembly_dto", `Opened updatedtoodal in details page;`);
}

function populateTable(breakerData) {
    const tableBody = document.getElementById('breakerDataTableBody');

    // Clear any existing rows
    tableBody.innerHTML = '';

    // Loop through each breakerData entry and create a row for each
    breakerData.forEach((data, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${data.docNo2 || ''}</td>
            <td>${data.matNo || ''}</td>
            <td>${data.dMat || ''}</td>
            <td>${data.quanTt || ''}</td>
            <td>${data.descRp || ''}</td>
            <td>${data.kMat || ''}</td>
        `;
        tableBody.appendChild(row);
    });
}

// async function ModalOfferUpdate(event, id){
//     event.target.innerText = "Loading...";
//     event.target.classList.add("disabled");
//     //alert(id);
//     const dtoData = await getDtoData(id);

//     $("#docNo2").val(dtoData[0].docNo2).trigger('change');
//     $("#matNo").val(dtoData.matNo).trigger('change');
//     $("#dMat").val(dtoData.dMat).trigger('change');
//     $("#quanTt").val(dtoData.quanTt).trigger('change');
//     $("#descRp").val(dtoData.descRp).trigger('change');
//     $("#kMat").val(dtoData.kMat).trigger('change');
//     alert('4');
//     populateTable1(dtoData);

//     event.target.innerText = "Update Offer";
//     event.target.classList.remove("disabled");

//     $("#updateModaloffer").modal('show');
//     saveLog("log_assembly_dto", `Opened updatedtoodal in details page;`);
// }
async function ModalOfferUpdate(event, id) {
    event.target.innerText = "Loading...";
    event.target.classList.add("disabled");

    const dtoData = await getDtoData(id) || [];

    if (dtoData[0]) {
        $("#docNo2").val(dtoData[0].docNo2).trigger('change');
        $("#matNo").val(dtoData[0].matNo).trigger('change');
        $("#dMat").val(dtoData[0].dMat).trigger('change');
        $("#quanTt").val(dtoData[0].quanTt).trigger('change');
        $("#descRp").val(dtoData[0].descRp).trigger('change');
        $("#kMat").val(dtoData[0].kMat).trigger('change');
    } else {
        $("#docNo2, #matNo, #dMat, #quanTt, #descRp, #kMat").val('').trigger('change');
    }

    populateTable1(dtoData);

    event.target.innerText = "Update Offer";
    event.target.classList.remove("disabled");

    $("#updateModaloffer").modal('show');
    saveLog("log_assembly_dto", `Opened updatedtoodal in details page;`);
}

function populateTable1(dtoData) {
    const tableBody = document.querySelector('#excelTableModal tbody');
    tableBody.innerHTML = '';

    if (!dtoData || dtoData.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${$('#docNo2').val().trim()}</td>
            <td colspan="5">No data available</td>
            <td><button class="btn btn-danger btn-sm delete-btn">Delete</button></td>
        `;
        tableBody.appendChild(row);
    } else {
        dtoData.forEach((data, index) => {
            
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${data.docNo2 || $('#docNo2').val().trim()}</td>
                <td>${data.matNo || ''}</td>
                <td>${data.dMat || ''}</td>
                <td>${data.quanTt || ''}</td>
                <td>${data.descRp || ''}</td>
                <td>${data.kMat || ''}</td>
                <td><button class="btn btn-danger btn-sm delete-btn">Delete</button></td>
            `;
            tableBody.appendChild(row);
        });
    }
}


async function openImageModal(event, docNo) {
    // Change button text and disable it during loading
    $('#docNo').val(docNo);
    event.target.innerText = "Loading...";
    event.target.classList.add("disabled");

    try {
        const imageData = await getImage(docNo);
        
        // Debug: Log the response
        console.log('imageData:', imageData);

        if (!imageData || !imageData.imageUrl) {
            console.error('No valid image URL found.');
            event.target.innerText = "Layout";
            event.target.classList.remove("disabled");
            return;
        }

        // Show the modal
        $('#ImageModal').modal('show');

        // Set the image source after the modal is shown
        const imageToShow = document.getElementById('modalImage');
        if (imageToShow) {
            // Set the image source and make it visible
            imageToShow.src = imageData.imageUrl;
            imageToShow.style.display = 'block';  // Ensure the image is visible
            console.log('Image src set to:', imageData.imageUrl);
            
            // Re-enable the button
            event.target.innerText = "Layout";
            event.target.classList.remove("disabled");
        } else {
            console.error("Image element with ID 'modalImage' not found.");
        }
    } catch (error) {
        console.error("Error fetching image:", error);
        event.target.innerText = "Layout";
        event.target.classList.remove("disabled");
    }
}

// async function ModalAddDoc(event, docNo) {
 
//     $('#docNo').val(docNo);
//     event.target.innerText = "Loading...";
//     event.target.classList.add("disabled");

//     try {
//         const folderData = await getDocs(docNo);  
//         console.log('folderData:', folderData);

//         if (!folderData || !folderData.files) {
//             console.error('No valid folder data found.');
//             event.target.innerText = "Documents";
//             event.target.classList.remove("disabled");
//             return;
//         }
//         $('#DocModal').modal('show');

        
//         const modalBody = document.querySelector('.modal-body');
//         modalBody.innerHTML = ''; 
//         function renderFolderStructure(files) {
//             const ul = document.createElement('ul');
//             files.forEach(file => {
//                 const li = document.createElement('li');
//                 li.textContent = file.name;

//                 if (file.type === 'folder') {
//                     // If the item is a folder, recursively render its contents
//                     const nestedUl = renderFolderStructure(file.files);  // Recursive call for nested folders
//                     li.appendChild(nestedUl);
//                 } else if (file.type === 'file') {
//                     // If it's a file, create a clickable link or preview option
//                     const link = document.createElement('a');
//                     link.href = file.url;
//                     link.textContent = file.name;
//                     link.target = "_blank";  // Open in a new tab

//                     li.appendChild(link);
//                 }

//                 ul.appendChild(li);
//             });
//             return ul;
//         }
//         const folderStructure = renderFolderStructure(folderData.files);
//         modalBody.appendChild(folderStructure);
//         console.log("Rendered HTML:", modalBody.innerHTML);
//         event.target.innerText = "Documents";
//         event.target.classList.remove("disabled");

//     } catch (error) {
//         console.error("Error fetching folder data:", error);
//         event.target.innerText = "Documents";
//         event.target.classList.remove("disabled");
//     }
// }
async function ModalAddDoc(event, docNo) {
    $('#docNo').val(docNo);
    event.target.innerText = "Loading...";
    event.target.classList.add("disabled");

    try {
        const folderData = await getDocs(docNo);  
        console.log('folderData:', folderData);

        if (!folderData || !folderData.files || folderData.files.length === 0) {
            console.error('No valid folder data found.');
            event.target.innerText = "Documents";
            event.target.classList.remove("disabled");
            return;
        }

        $('#DocModal').modal('show');

        const modalBody = document.querySelector('#DocModal .modal-body');
        modalBody.innerHTML = ''; 

        const fileList = document.createElement('div');
        fileList.style.width = '100%';

        folderData.files.forEach(file => {
            if (file.type === 'file') {
                const fileRow = document.createElement('div');
                fileRow.style.display = 'flex';
                fileRow.style.alignItems = 'center';
                fileRow.style.justifyContent = 'space-between';
                fileRow.style.padding = '10px';
                fileRow.style.borderBottom = '1px solid #ddd';

                const fileLink = document.createElement('a');
                fileLink.href = file.url;
                fileLink.textContent = file.name;
                fileLink.target = "_blank";
                fileLink.style.color = '#007bff';
                fileLink.style.textDecoration = 'none';
                fileLink.style.flex = '1';

                // Prevent modal from closing when clicking the file name
                fileLink.addEventListener('click', function (e) {
                    e.stopPropagation();
                });

                const downloadBtn = document.createElement('a');
                downloadBtn.href = file.url;
                downloadBtn.download = file.name;
                downloadBtn.innerHTML = '⬇️'; // Use icon here if preferred
                downloadBtn.title = "Download";
                downloadBtn.style.marginLeft = '10px';
                downloadBtn.style.fontSize = '18px';

                // Prevent modal from closing when clicking download
                downloadBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                });

                fileRow.appendChild(fileLink);
                fileRow.appendChild(downloadBtn);
                fileList.appendChild(fileRow);
            }
        });

        modalBody.appendChild(fileList);

        event.target.innerText = "Documents";
        event.target.classList.remove("disabled");

    } catch (error) {
        console.error("Error fetching folder data:", error);
        event.target.innerText = "Documents";
        event.target.classList.remove("disabled");
    }
}

async function getUpdateNumberData(id){
    return $.ajax({
        url: `api/DTOController.php`,
        type: 'GET',
        data: {
            "action": "getUpdateNumberData",
            "id": id
        }
    }).catch(e => {
        console.log(e)
        showNotification("error", "This breaker data could not be loaded");
    });
}


async function openUpdateModal1(event, id){
    event.target.innerText = "Loading...";
    event.target.classList.add("disabled");
//alert(id);
    const breakerData = await getUpdateData(id);
    
    //console.log(breakerData);    
    $("#id").val(breakerData[0].id).trigger('change');
    $("#stageName").val(breakerData[0].stageName?.trim()).trigger('change');
    $("#productName").val(breakerData[0].productName?.trim()).trigger('change');
    $("#iacRating").val(breakerData[0].iacRating).trigger('change');
    $("#docNo").val(breakerData[0].docNo).trigger('change');
    $("#shortDescription").val(breakerData[0].shortDescription).trigger('change');
    $("#woundCTs").val(breakerData[0].woundCTs).trigger('change');
    $("#windowCTs").val(breakerData[0].windowCTs).trigger('change');
    $("#cablesBus").val(breakerData[0].cablesBus).trigger('change');
    $("#cabCore").val(breakerData[0].cabCore).trigger('change');
    $("#cabEntry").val(breakerData[0].cabEntry).trigger('change');
    $("#ratedVol").val(breakerData[0].ratedVol).trigger('change');
    $("#ratedCir").val(breakerData[0].ratedCir).trigger('change');
    $("#ratedCurrent").val(breakerData[0].ratedCurrent).trigger('change');
    $("#width").val(breakerData[0].width).trigger('change');
    $("#rearBoxDepth").val(breakerData[0].rearBoxDepth).trigger('change');
    $("#feederMat").val(breakerData[0].feederMat).trigger('change');
    $("#realBy").val(breakerData[0].realBy).trigger('change');
    $("#info").val(breakerData[0].info).trigger('change');
    $("#orderNo").val(breakerData[0].orderNo).trigger('change');
    $("#DrawNo").val(breakerData[0].DrawNo).trigger('change');
    $("#eartSwitch").val(breakerData[0].eartSwitch).trigger('change');
    $("#doVt").val(breakerData[0].doVt).trigger('change');
    $("#toolSel").val(breakerData[0].toolSel).trigger('change');
    $("#addOn").val(breakerData[0].addOn).trigger('change');
    $("#solenoid").val(breakerData[0].solenoid).trigger('change');
    $("#limSwi").val(breakerData[0].limSwi).trigger('change');
    $("#meshAss").val(breakerData[0].meshAss).trigger('change');
    $("#lampRearCover").val(breakerData[0].lampRearCover).trigger('change');
    $("#glandPlate").val(breakerData[0].glandPlate).trigger('change');
    $("#rearCover").val(breakerData[0].rearCover).trigger('change');
    $("#DispUser").val(breakerData[0].DispUser).trigger('change');
    
    
    populateTable(breakerData);

    event.target.innerText = "Update";
    event.target.classList.remove("disabled");

    $("#updateModal1").modal('show');
    saveLog("log_assembly_breaker", `Opened update DTO Modal in details page;`);
}

function handleMaterialTypeVisibility(materialType) {
    // Hide all field groups first
    $('.field-group').hide();
    
    // Show fields common to all material types
    $('[data-field-type*="' + materialType + '"]').show();
    
    // Special case for busbar material type
    if (materialType === 'busbar') {
        // Show busbar specific fields
        $('#material').closest('.form-group').show();
        $('#material').attr('required', true);
    }
}

// Function to update material registration
function updateMaterialRegistration() {
    // Show loading indicator
    $('#updateMaterialBtn').text('Updating...').addClass('disabled');
    
    // Get all form data
    const formData = new FormData(document.getElementById('updateMaterialForm'));
    formData.append('action', 'updateMatRegister');
    
    // Send AJAX request to update material
    $.ajax({
        url: 'api/DTOController.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            try {
                const result = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (result.code === 200 || result.message === "Successfully Updated") {
                    showNotification('success', "Material registration updated successfully");
                    $('#table_open_items').DataTable().ajax.reload(null, false);
                    $("#updateMatNumModal").modal('hide');
                } else {
                    showNotification('error', result.message || "An error occurred during update");
                }
            } catch (e) {
                showNotification('success', "Material registration updated successfully");
                $('#table_open_items').DataTable().ajax.reload(null, false);
                $("#updateMatNumModal").modal('hide');
            }
        },
        error: function(xhr) {
            showNotification('error', "An error occurred while updating material registration");
            console.error("Update error:", xhr.responseText);
        },
        complete: function() {
            // Reset button state
            $('#updateMaterialBtn').text('Update').removeClass('disabled');
        }
    });
}

// Event handlers
$(document).ready(function() {
    // Material type change event
    $('#material_type').on('change', function() {
        const selectedType = $(this).val();
        handleMaterialTypeVisibility(selectedType);
    });
    
    // Update button click event
    $('#updateMaterialBtn').on('click', function() {
        updateMaterialRegistration();
    });
    
    // When modal is shown, set up fields based on material type
    $('#updateMatNumModal').on('shown.bs.modal', function() {
        const materialType = $('#material_type').val();
        if (materialType) {
            handleMaterialTypeVisibility(materialType);
        }
    });
});

// Enhance the existing openMaterailRegisterModal function
async function openMaterailRegisterModal(event, id) {
    event.target.innerText = "Loading...";
    event.target.classList.add("disabled");
    
    try {
        const MaterailData = await getUpdateMatRegister(id);
        
        if (!MaterailData || !MaterailData[0]) {
            showNotification('error', "Failed to load material data");
            return;
        }
        
        const data = MaterailData[0];
        
        // Reset form first
        $('#updateMaterialForm')[0].reset();
        
        // Set form values
        $("#id").val(data.id);
        $("#product_name").val(data.product_name?.trim());
        $("#drawing_name").val(data.drawing_name?.trim());
        $("#drawing_number").val(data.drawing_number);
        $("#material_type").val(data.material_type);
        $("#ka_rating").val(data.ka_rating);
        $("#width").val(data.width);
        $("#rear_box").val(data.rear_box);
        $("#material").val(data.material);
        $("#thickness").val(data.thickness);
        $("#description").val(data.description);
        $("#panel_width").val(data.panel_width);
        $("#gp_material").val(data.gp_material);
        $("#gp_thickness").val(data.gp_thickness);
        $("#sizeofbusbar").val(data.sizeofbusbar);
        $("#ag_plating").val(data.ag_plating);
        $("#feeder_bar_size").val(data.feeder_bar_size);
        $("#mbb_size").val(data.mbb_size);
        $("#end_cover_location").val(data.end_cover_location);
        $("#ebb_cutout").val(data.ebb_cutout);
        $("#ebb_size").val(data.ebb_size);
        $("#cable_entry").val(data.cable_entry);
        $("#interlock").val(data.interlock);
        $("#ir_window").val(data.ir_window);
        $("#nameplate").val(data.nameplate);
        $("#viewing_window").val(data.viewing_window);
        $("#rear_box_type").val(data.rear_box_type);
        $("#lhs_panel_rb").val(data.lhs_panel_rb);
        $("#rhs_panel_rb").val(data.rhs_panel_rb);
        $("#ct_type").val(data.ct_type);
        $("#cable_number").val(data.cable_number);
        $("#cbct").val(data.cbct);
        $("#mbb_run").val(data.mbb_run);
        $("#feeder_run").val(data.feeder_run);
        $("#feeder_size").val(data.feeder_size);
        $("#short_text").val(data.short_text);
        $("#remarks").val(data.remarks);
        
        // Apply material type visibility
        handleMaterialTypeVisibility(data.material_type);
        
        event.target.innerText = "Update";
        event.target.classList.remove("disabled");
        
        $("#updateMatNumModal").modal('show');
        saveLog("log_assembly_breaker", `Opened update Material Register Modal; ID: ${id}`);
    } catch (error) {
        console.error("Error loading material data:", error);
        showNotification('error', "Failed to load material data");
        event.target.innerText = "Update";
        event.target.classList.remove("disabled");
    }
}

// async function openMaterailRegisterModal(event,id){
//     event.target.innerText = "Loading...";
//     event.target.classList.add("disabled");
// //alert(id);
//     const MaterailData = await getUpdateMatRegister(id);
    
//     //console.log(breakerData);    
//     $("#id").val(MaterailData[0].id).trigger('change');
//     $("#product_name").val(MaterailData[0].product_name?.trim()).trigger('change');
//     $("#drawing_name").val(MaterailData[0].drawing_name?.trim()).trigger('change');
//     $("#drawing_number").val(MaterailData[0].drawing_number).trigger('change');
//     $("#material_type").val(MaterailData[0].material_type).trigger('change');
//     $("#ka_rating").val(MaterailData[0].ka_rating).trigger('change');
//     $("#width").val(MaterailData[0].width).trigger('change');
//     $("#rear_box").val(MaterailData[0].rear_box).trigger('change');
//     $("#material").val(MaterailData[0].material).trigger('change');
//     $("#thickness").val(MaterailData[0].thickness).trigger('change');
//     $("#description").val(MaterailData[0].description).trigger('change');
//     $("#panel_width").val(MaterailData[0].panel_width).trigger('change');
//     $("#gp_material").val(MaterailData[0].gp_material).trigger('change');
//     $("#gp_thickness").val(MaterailData[0].gp_thickness).trigger('change');
//     $("#sizeofbusbar").val(MaterailData[0].sizeofbusbar).trigger('change');
//     $("#ag_plating").val(MaterailData[0].ag_plating).trigger('change');
//     $("#feeder_bar_size").val(MaterailData[0].feeder_bar_size).trigger('change');
//     $("#mbb_size").val(MaterailData[0].mbb_size).trigger('change');
//     $("#end_cover_location").val(MaterailData[0].end_cover_location).trigger('change');
//     $("#ebb_cutout").val(MaterailData[0].ebb_cutout).trigger('change');
//     $("#ebb_size").val(MaterailData[0].ebb_size).trigger('change');
//     $("#cable_entry").val(MaterailData[0].cable_entry).trigger('change');
//     $("#interlock").val(MaterailData[0].interlock).trigger('change');
//     $("#ir_window").val(MaterailData[0].ir_window).trigger('change');
//     $("#nameplate").val(MaterailData[0].nameplate).trigger('change');
//     $("#viewing_window").val(MaterailData[0].viewing_window).trigger('change');
//     $("#rear_box_type").val(MaterailData[0].rear_box_type).trigger('change');
//     $("#lhs_panel_rb").val(MaterailData[0].lhs_panel_rb).trigger('change');
//     $("#rhs_panel_rb").val(MaterailData[0].rhs_panel_rb).trigger('change');
//     $("#ct_type").val(MaterailData[0].ct_type).trigger('change');
//     $("#cable_number").val(MaterailData[0].cable_number).trigger('change');
//     $("#cbct").val(MaterailData[0].cbct).trigger('change');
//     $("#mbb_run").val(MaterailData[0].mbb_run).trigger('change');
//     $("#feeder_run").val(MaterailData[0].feeder_run).trigger('change');
//     $("#feeder_size").val(MaterailData[0].feeder_size).trigger('change');
//     $("#short_text").val(MaterailData[0].short_text).trigger('change');
//     $("#remarks").val(MaterailData[0].remarks).trigger('change');
    
//     event.target.innerText = "Update";
//     event.target.classList.remove("disabled");

//     $("#updateMatNumModal").modal('show');
//     saveLog("log_assembly_breaker", `Opened update DTO Modal in details page;`);
// }

function updateBreaker1() {
    const id = $("#id").val();
    const stageName = document.getElementById('stageName').value;
    const productName = document.getElementById('productName').value;
    const iacRating = document.getElementById('iacRating').value;
    const docNo = document.getElementById('docNo').value;
    const shortDescription = document.getElementById('shortDescription').value;
    const woundCTs = document.getElementById('woundCTs').value;
    const windowCTs = document.getElementById('windowCTs').value;
    const cablesBus = document.getElementById('cablesBus').value;
    const cabCore = document.getElementById('cabCore').value;
    const cabEntry = document.getElementById('cabEntry').value;
    const ratedVol = document.getElementById('ratedVol').value;
    const ratedCir = document.getElementById('ratedCir').value;
    const ratedCurrent = document.getElementById('ratedCurrent').value;
    const width = document.getElementById('width').value;
    const rearBoxDepth = document.getElementById('rearBoxDepth').value;
    const feederMat = document.getElementById('feederMat').value;
    const realBy = document.getElementById('realBy').value;
    const info = document.getElementById('info').value;
    const orderNo = document.getElementById('orderNo').value;
    const DrawNo = document.getElementById('DrawNo').value;
    const eartSwitch = document.getElementById('eartSwitch').value;
    const doVt = document.getElementById('doVt').value;
    const toolSel = document.getElementById('toolSel').value;
    const addOn = document.getElementById('addOn').value;
    const solenoid = document.getElementById('solenoid').value;
    const limSwi = document.getElementById('limSwi').value;
    const meshAss = document.getElementById('meshAss').value;
    const lampRearCover = document.getElementById('lampRearCover').value;
    const glandPlate = document.getElementById('glandPlate').value;
    const rearCover = document.getElementById('rearCover').value;
    const DispUser = document.getElementById('DispUser').value;
    $.ajax({
        url: 'api/DTOController.php',
        method: 'POST',
        data: {
            "action": "edit",
            "id": id,
            "stageName": stageName,
            "productName": productName,
            "iacRating": iacRating,
            "docNo": docNo,
            "shortDescription": shortDescription,
            "woundCTs": woundCTs,
            "windowCTs": windowCTs,
            "cablesBus": cablesBus,
            "cabCore": cabCore,
            "cabEntry": cabEntry,
            "ratedVol": ratedVol,
            "ratedCir": ratedCir,
            "ratedCurrent": ratedCurrent,
            "width": width,
            "rearBoxDepth": rearBoxDepth,
            "feederMat": feederMat,
            "realBy": realBy,
            "info": info,
            "orderNo": orderNo,
            "DrawNo": DrawNo,
            "eartSwitch": eartSwitch,
            "doVt": doVt,
            "toolSel": toolSel,
            "addOn": addOn,
            "solenoid": solenoid,
            "limSwi": limSwi,
            "meshAss": meshAss,
            "lampRearCover": lampRearCover,
            "glandPlate": glandPlate,
            "rearCover": rearCover,
            "DispUser":DispUser
        },
        success: function () {
            saveLog("log_assembly_breaker", `success on updateBreaker in details page; ID: ${id}, productName: ${productName}`);
            showNotification('success', "Successfully updated");
            $('#table_open_items').DataTable().ajax.reload(null, false);
            $("#updateModal1").modal('hide');
        },
        error: function (errResponse) {
            saveLog("log_assembly_breaker", `error on updateBreaker in details page; ID: ${id}, productName: ${productName}`);
            showNotification('error', "An error occurred");
        }
    });
}

async function openUpdateNum(event, id){
    event.target.innerText = "Loading...";
    event.target.classList.add("disabled");
//alert(id);
    const NumberData = await getUpdateNumberData(id);
    
    //console.log(breakerData);    
    $("#id").val(NumberData[0].id).trigger('change');
    $("#prdName").val(NumberData[0].prdName?.trim()).trigger('change');
    $("#drwName").val(NumberData[0].drwName).trigger('change');
    $("#matName").val(NumberData[0].matName).trigger('change');
    $("#mainNumber").val(NumberData[0].mainNumber).trigger('change');
    $("#startNumber").val(NumberData[0].startNumber).trigger('change');
    $("#endNumber").val(NumberData[0].endNumber).trigger('change');
    
    event.target.innerText = "Update";
    event.target.classList.remove("disabled");

    $("#updateModal11").modal('show');
    saveLog("log_assembly_breaker", `Opened update DTO Modal in details page;`);
}

function updateNumber(pageName) {
    const id = $("#id").val();
    const prdName = document.getElementById('prdName').value;
    const drwName = document.getElementById('drwName').value;
    const matName = document.getElementById('matName').value;
    const mainNumber = document.getElementById('mainNumber').value;
    const startNumber = document.getElementById('startNumber').value;
    const endNumber = document.getElementById('endNumber').value;
    
    
    $.ajax({
        url: 'api/DTOController.php',
        method: 'POST',
        data: {
            "action": "editnumber",
            "id": id,
            "prdName": prdName,
            "drwName": drwName,
            "matName": matName,
            "mainNumber": mainNumber,
            "startNumber": startNumber,
            "endNumber": endNumber
        },
        success: function () {
            saveLog("log_assembly_breaker", `success on updateBreaker in details page; ID: ${id}, prdName: ${prdName}`);
            showNotification('success', "Successfully updated");
            $('#table_open_items').DataTable().ajax.reload(null, false);
            $("#updateModal1").modal('hide');
        },
        error: function (errResponse) {
            saveLog("log_assembly_breaker", `error on updateBreaker in details page; ID: ${id}, prdName: ${prdName}`);
            showNotification('error', "An error occurred");
        }
    });
}


async function updateOffer() {
    const tableRows = document.querySelectorAll('#excelTableModal tbody tr');  // Get all rows from the table body
    const offerData = [];

    // Loop through each row and extract the cell data
    tableRows.forEach((row) => {
        const cells = row.querySelectorAll('td');
        const rowData = {
            docNo2: cells[0].textContent.trim() || $('#docNo2').val().trim(),
            matNo: cells[1].textContent || '',
            dMat: cells[2].textContent || '',
            quanTt: cells[3].textContent || '',
            descRp: cells[4].textContent || '',
            kMat: cells[5].textContent || ''
        };
        offerData.push(rowData); // Add row data to offerData array
    });

    // Send the data to the backend via AJAX for updating the offer
    try {
        const response = await $.ajax({
            url: 'api/DTOController.php',  // Your endpoint for processing the data
            method: 'POST',
            data: {
                action: 'updateoffer',
                offerData: JSON.stringify(offerData)  // Send the table data as JSON
            }
        });

        // Handle the response from the server
        if (response.code === 200) {
            showNotification('success', 'Offer updated successfully');
            $("#updateModaloffer").modal('hide');  // Hide modal after successful update
            $('#table_open_items').DataTable().ajax.reload(null, false);  // Reload table data (if using DataTables)
        } else {
            showNotification('error', 'An error occurred during update');
        }
    } catch (error) {
        showNotification('error', 'An error occurred while sending the data');
    }
}

function updateImage(pageName) {
    var docNo = $('#docNo').val();
    //const id = $('#id').val();

    var fileInput = $('#exampleInputFile')[0]; 
    if (!fileInput) {
        showNotification('warning', "File input element not found.");
        return;
    }

    var file = fileInput.files[0];
    if (!file) {
        showNotification('warning', "Please select a file first.");
        return;
    }

    var formData = new FormData();
    formData.append('action', 'editimage'); 
    formData.append('docNo', docNo);
    formData.append('exampleInputFile', file); 

    $.ajax({
        url: 'api/DTOController.php',
        method: 'POST',
        data: formData,           
        processData: false,       
        contentType: false,       
        success: function () {
            saveLog("log_assembly_breaker", `Success on updateImage; ID: ${id}`);
            showNotification('success', "Successfully updated");
            $('#table_open_items').DataTable().ajax.reload(null, false);
            $("#ImageModal").modal('hide');
        },
        error: function (errResponse) {
            console.error("AJAX error:", errResponse);
            saveLog("log_assembly_breaker", `Error on updateImage; ID: ${id}`);
            showNotification('error', "An error occurred while uploading the image.");
        }
    });
}


