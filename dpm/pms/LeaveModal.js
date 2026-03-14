async function getLeaveData(id){
    // alert(id);
    return $.ajax({
        url: `api/PMSController.php`,
        type: 'GET',
        dataType: 'json',
        data: {
            "action": "getLeaveData",
            "id": id
        }
    }).catch(e => {
        console.log(e)
        showNotification("error", "This Leave data could not be loaded");
    });
}




async function openLeaveModal(event, id){
    event.target.innerText = "Loading...";
    event.target.classList.add("disabled");
    const leaveData = await getLeaveData(id);
    
    //console.log(leaveData);    
    $("#hdnId").val(leaveData[0].id).trigger('change');
    $("#gid").val(leaveData[0].gid?.trim()).prop('disabled', true).trigger('change');
    $("#name").val(leaveData[0].name?.trim()).prop('disabled', true).trigger('change');
    $("#department").val(leaveData[0].department).prop('disabled', true).trigger('change');
    $("#sub_department").val(leaveData[0].sub_department).prop('disabled', true).trigger('change');
    $("#role").val(leaveData[0].role).prop('disabled', true).trigger('change');
    $("#group_type").val(leaveData[0].group_type).prop('disabled', true).trigger('change');
    $("#in_company_manager").val(leaveData[0].in_company_manager).prop('disabled', true).trigger('change');
    $("#line_manager").val(leaveData[0].line_manager).prop('disabled', true).trigger('change');
    $("#supervisor").val(leaveData[0].supervisor).prop('disabled', true).trigger('change');
    $("#sponsor").val(leaveData[0].sponsor).prop('disabled', true).trigger('change');
    $("#employment_type").val(leaveData[0].employment_type).prop('disabled', true).trigger('change');
    $("#joined").val(leaveData[0].joined).prop('disabled', true).trigger('change');
    $("#leave_type").val(leaveData[0].leave_type).trigger('change');
    $("#absence_detail").val(leaveData[0].absence_detail).trigger('change');
    $("#start_date").val(leaveData[0].start_date).trigger('change');
    $("#end_date").val(leaveData[0].end_date).trigger('change');
    $("#total_days").val(leaveData[0].total_days).trigger('change');
    
    
    // populateTable(leaveData);

    event.target.innerText = "Update";
    event.target.classList.remove("disabled");

    $("#LeaveModal").modal('show');
    saveLog("log_assembly_leave", `Opened update Leave Modal in details page;`);
}

function updateLeave() {
    const id = $("#hdnId").val();
    const gid = document.getElementById('gid').value;
    const name = document.getElementById('name').value;
    const department = document.getElementById('department').value;
    const sub_department = document.getElementById('sub_department').value;
    const role = document.getElementById('role').value;
    const group_type = document.getElementById('group_type').value;
    const in_company_manager = document.getElementById('in_company_manager').value;
    const line_manager = document.getElementById('line_manager').value;
    const supervisor = document.getElementById('supervisor').value;
    const sponsor = document.getElementById('sponsor').value;
    const employment_type = document.getElementById('employment_type').value;
    const joined = document.getElementById('joined').value;
    const leave_type = document.getElementById('leave_type').value;
    const absence_detail = document.getElementById('absence_detail').value;
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    const total_days = document.getElementById('total_days').value;
    $.ajax({
        url: 'api/PMSController.php',
        method: 'POST',
        dataType: 'json',
        data: {
            "action": "editLeave",
            "id": id,
            "gid": gid,
            "name": name,
            "department": department,
            "sub_department": sub_department,
            "role": role,
            "group_type": group_type,
            "in_company_manager": in_company_manager,
            "line_manager": line_manager,
            "supervisor": supervisor,
            "sponsor": sponsor,
            "employment_type": employment_type,
            "joined": joined,
            "leave_type": leave_type,
            "absence_detail": absence_detail,
            "start_date": start_date,
            "end_date": end_date,
            "total_days": total_days
        },
        success: function () {
            saveLog("log_assembly_leave", `success on updateLeave in details page; ID: ${id}, name: ${name}`);
            showNotification('success', "Successfully updated");
            $('#table_open_items').DataTable().ajax.reload(null, false);
            $("#LeaveModal").modal('hide');
        },
        error: function (errResponse) {
            saveLog("log_assembly_leave", `error on updateLeave in details page; ID: ${id}, name: ${name}`);
            showNotification('error', "An error occurred");
        }
    });
}
