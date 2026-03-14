function getUserIDForActivation(e){
    const id = e.target.getAttribute('data-id');
    const confirmation = window.confirm("Are you sure?");

    if(!confirmation)
        return null;
    return id;
}

async function deactivateUser(e){
    const id = getUserIDForActivation(e);
    if(!id) return;

    await $.ajax({
        url: '/users/api/ManagementController.php?',
        method: 'POST',
        data:{
            "action": "deactivate",
            "id": id
        }
    }).catch(e => {
        console.error(e);
        toastr.error('','Error occurred while deactivating user');
    });

    window.location.reload();
}

async function activateUser(e){
    const id = getUserIDForActivation(e);
    if(!id) return;

    await $.ajax({
        url: '/users/api/ManagementController.php?',
        method: 'POST',
        data:{
            "action": "activate",
            "id": id
        }
    }).catch(e => {
        console.error(e);
        toastr.error('','Error occurred while activating user');
    });

    window.location.reload();
}

function hideLoader(){
    document.getElementById('fullLoader').setAttribute('style', 'display: none !important');
    document.getElementById('tableContainer').setAttribute('style', 'display: block !important');
}

function showLoader(){
    document.getElementById('fullLoader').setAttribute('style', 'display: block !important');
    document.getElementById('tableContainer').setAttribute('style', 'display: none !important');
}

$(document).ready(function () {
    (async () => {
        const dtData = await $.ajax({
            url: '/users/api/ManagementController.php?action=getUsers',
            method: 'GET'
        }).catch(e => {
            console.error(e);
            toastr.error('','Error occurred while fetching users');
        });

        let dtColumns = [
            {
                className: "ta-c buttonsCol static-cell-bg",
                data: "id",
                render: function (id, type, row) {
                    const userStatus = row["status"];

                    const editUserButton = (userStatus) ? `<a target='_blank' href='/users/edituser.php?id=${id}'><button type="button" class="btn btn-xs btn-warning">Edit</button></a>` : ``;
                    const changeStatusButton = (userStatus)
                        ? `<button class="btn btn-xs btn-danger deactivateButton" data-id="${id}" onclick="deactivateUser(event)">Deactivate</button>`
                        : `<button class="btn btn-xs btn-primary deactivateButton" data-id="${id}" onclick="activateUser(event)">Activate</button>`;
                    return `
                                ${editUserButton}
                                ${changeStatusButton}
                            `;
                },
            },
            {
                className: "ta-c idCol static-cell-bg",
                data: "id"
            },
            {
                className: "ta-c emailCol static-cell-bg",
                data: "email"
            },
            {
                className: "ta-c",
                data: "groupName"
            },
            {
                className: "ta-c",
                data: "countryName"
            },
            {
                className: "ta-c",
                data: "lastLogin",
                render: {
                    "_": "lastLoginTime",
                    "display": "lastLoginDate"
                }
            }
        ]

        const tableModuleHeaders = dtData["tableModuleHeaders"];
        let tableModuleHeadersHTML = "";
        for(const moduleHeader of tableModuleHeaders){
            tableModuleHeadersHTML += `<th scope="col" class="rotate w-2 va-m p-0">${moduleHeader}</th>`;
            dtColumns.push({
                className: "ta-c scrollable-col",
                data: "moduleNames",
                render: function (data, type, row){
                    return `${data[moduleHeader]}`;
                }
            });
        }
        document.querySelector('#usersTable > thead > tr').innerHTML += tableModuleHeadersHTML;

        const usersData = dtData["userData"];

        $('#usersTable').DataTable({
            "initComplete": function (settings, json) {
                hideLoader();
            },
            bAutoWidth : false,
            data: usersData,
            columns: dtColumns,
            paging: false,
            sorting: false
        });
    })();

});