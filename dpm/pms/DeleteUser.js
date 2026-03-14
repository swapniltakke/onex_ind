async function getUserEmpData(id){
    // alert(department);
    // alert(role);
    return $.ajax({
        url: `api/PMSController.php`,
        type: 'GET',
        dataType: 'json',
        data: {
            "action": "getUserEmpData",
            "id": id
        }
    }).catch(e => {
        console.log(e)
        showNotification("error", "This Leave data could not be loaded");
    });
}




async function openUserModal(event, id){
    event.target.innerText = "Loading...";
    event.target.classList.add("disabled");
    const userEmpData = await getUserEmpData(id);
    
    console.log(userEmpData);    
    $("#hdnId").val(userEmpData[0].id).trigger('change');
    $("#gid").val(userEmpData[0].gid?.trim()).prop('disabled', true).trigger('change');
    $("#name").val(userEmpData[0].name?.trim()).prop('disabled', true).trigger('change');
    $("#department").val(userEmpData[0].department).prop('disabled', true).trigger('change');
    $("#sub_department").val(userEmpData[0].sub_department).trigger('change');
    $("#role").val(userEmpData[0].role).prop('disabled', true).trigger('change');
    $("#group_type").val(userEmpData[0].group_type).trigger('change');
    $("#in_company_manager").val(userEmpData[0].in_company_manager).prop('disabled', true).trigger('change');
    $("#line_manager").val(userEmpData[0].line_manager).prop('disabled', true).trigger('change');
    $("#shift_type").val(userEmpData[0].shift_type).trigger('change');
    $("#temp_sub_department").val(userEmpData[0].temp_sub_department).trigger('change');
    $("#temp_group_type").val(userEmpData[0].temp_group_type).trigger('change');
    $("#transfer_from_date").val(userEmpData[0].transfer_from_date).trigger('change');
    $("#transfer_to_date").val(userEmpData[0].transfer_to_date).trigger('change');
    
    
    // populateTable(leaveData);

    event.target.innerText = "Update";
    event.target.classList.remove("disabled");

    $("#UserModal").modal('show');
    saveLog("log_assembly_leave", `Opened update User Modal in details page;`);
}

function updateUser() {
    const id = $("#hdnId").val();
    const gid = document.getElementById('gid').value;
    const name = document.getElementById('name').value;
    const department = document.getElementById('department').value;
    const sub_department = document.getElementById('sub_department').value;
    const role = document.getElementById('role').value;
    const group_type = document.getElementById('group_type').value;
    const in_company_manager = document.getElementById('in_company_manager').value;
    const line_manager = document.getElementById('line_manager').value;
    const shift_type = document.getElementById('shift_type').value;
    const temp_sub_department = document.getElementById('temp_sub_department').value;
    const temp_group_type = document.getElementById('temp_group_type').value;
    const transfer_from_date = document.getElementById('transfer_from_date').value;
    const transfer_to_date = document.getElementById('transfer_to_date').value;
    $.ajax({
        url: 'api/PMSController.php',
        method: 'POST',
        dataType: 'json',
        data: {
            "action": "editUser",
            "id": id,
            "gid": gid,
            "name": name,
            "department": department,
            "sub_department": sub_department,
            "role": role,
            "group_type": group_type,
            "in_company_manager": in_company_manager,
            "line_manager": line_manager,
            "shift_type": shift_type,
            "temp_sub_department": temp_sub_department,            
            "temp_group_type": temp_group_type,
            "transfer_from_date": transfer_from_date,
            "transfer_to_date": transfer_to_date
        },
        success: function () {
            saveLog("log_assembly_leave", `success on updateUser in details page; ID: ${id}, name: ${name}`);
            showNotification('success', "Successfully updated");
            $('#table_open_items').DataTable().ajax.reload(null, false);
            $("#UserModal").modal('hide');
        },
        error: function (errResponse) {
            saveLog("log_assembly_leave", `error on updateUser in details page; ID: ${id}, name: ${name}`);
            showNotification('error', "An error occurred");
        }
    });
}
