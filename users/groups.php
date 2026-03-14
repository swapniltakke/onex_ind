<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/users/auth.php';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Groups | OneX</title>

    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/users/components/headimports.php"; ?>
    <script src="/shared/inspia_gh_assets/js/plugins/chosen/chosen.jquery.js"></script>

    <script src="/shared/inspia_gh_assets/js/popper.min.js"></script>
    <script src="/shared/inspia_gh_assets/js/bootstrap.js"></script>
    <script src="/shared/inspia_gh_assets/js/bootstrap-select.min.js"></script>
    <style>
        .va-m{ vertical-align: middle !important; }
        .ta-c{ text-align: center !important; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php require_once $_SERVER["DOCUMENT_ROOT"]."/users/components/sidebar.php"; ?>
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header" data-animation="fadeInLeft">
                    <a style="cursor:pointer;" class="navbar-minimalize minimalize-styl-2 btn btn-primary text-white"><i class="fa fa-bars"></i></a>
                </div>
            </nav>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-center">
                    <h3 class="card-title">Add Group</h3>
                </div>
                <div class="row justify-content-center m-lg-4" style="margin-top: 2rem !important;">
                    <div class="col-12 col-lg-7">
                        <div class="form-group row">
                            <label for="groupNameInput" class="col-lg-2 col-form-label">Group Name</label>
                            <div class="col-lg-10">
                                <input id="groupNameInput" type="email" placeholder="Enter a group name"
                                       class="form-control required"
                                       value="" required>
                                <span class="form-text m-b-none"></span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="country" class="col-lg-2 col-form-label">Country</label>
                            <div class="col-lg-10">
                                <select name="country_id" class="form-control selectpicker required" id="group_id"
                                        data-live-search="true" required>
                                    <option value="0">Select Country</option>
                                    <?php
                                    $getCountries = "SELECT country_id, name FROM countries";
                                    $getCountriesData = DbManager::fetchPDOQueryData('php_auth', $getCountries)["data"];
                                    $DEFAULT_COUNTRY_ID = (int) SharedManager::getFromSharedEnv('DEFAULT_COUNTRY_ID');
                                    foreach ($getCountriesData as $groupData){
                                        $countryId = (int) $groupData["country_id"];
                                        $countryName = $groupData["name"];
                                        $isSelected = "";
                                        if($countryId === $DEFAULT_COUNTRY_ID)
                                            $isSelected = "selected";
                                        echo "<option value=\"$countryId\" $isSelected>$countryName</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col align-self-end">
                                <button id="submit-button" class="btn btn-lg btn-primary float-right">
                                    Create Group
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ibox m-0">
            <div class="ibox-title">
                <h5>Groups</h5>
                <div class="ibox-tools">
                    <a class="collapse-link">
                        <i class="fa fa-chevron-up"></i>
                    </a>
                    <a class="close-link">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
            </div>
            <div class="ibox-content table-container" >
                <div class="d-block w-full loader" id="fullLoader" style="display: block !important;">
                    <h4 class="text-center">Loading...</h4>
                    <div class="spiner-example p-0">
                        <div class="sk-spinner sk-spinner-cube-grid">
                            <div class="sk-cube"></div>
                            <div class="sk-cube"></div>
                            <div class="sk-cube"></div>
                            <div class="sk-cube"></div>
                            <div class="sk-cube"></div>
                            <div class="sk-cube"></div>
                            <div class="sk-cube"></div>
                            <div class="sk-cube"></div>
                            <div class="sk-cube"></div>
                        </div>
                    </div>
                </div>
                <div id="tableContainer" style="display: none !important;">
                    <table id="groupsTable" class="table nowrap p-0" style="width:50%">
                        <thead>
                            <tr>
                                <th scope="col" class="ta-c p-0">ID</th>
                                <th scope="col" class="ta-c p-0">Group Name</th>
                                <th scope="col" class="ta-c p-0">Country</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<script src="/shared/shared.js"></script>
<script>
    function hideLoader(){
        document.getElementById('fullLoader').setAttribute('style', 'display: none !important');
        document.getElementById('tableContainer').setAttribute('style', 'display: block !important');
    }

    function showLoader(){
        document.getElementById('fullLoader').setAttribute('style', 'display: block !important');
        document.getElementById('tableContainer').setAttribute('style', 'display: none !important');
    }

    document.getElementById('submit-button').addEventListener('click', () => {
        const groupName = document.getElementById('groupNameInput').value;
        if(groupName.length < 5){
            toastr.warning('','Group name should be at least 5 characters');
            return;
        }

        const countryId = document.getElementById('group_id').value;
        (async () => {
            document.getElementById('submit-button').disabled = true;
            document.getElementById('submit-button').innerText = "Processing"
            const groupCreation = await $.ajax({
                url: '/users/api/ManagementController.php',
                method: 'POST',
                data: {
                    "action": "createGroup",
                    "groupName": groupName,
                    "countryId": countryId
                }
            }).catch(e => {
                console.error(e);
                console.error(e);
                if(!e?.responseJSON?.message)
                    toastr.error('','Error occurred while creating group');
                else
                    toastr.warning('', e.responseJSON.message);
                document.getElementById('submit-button').innerText = "Create Group"
                document.getElementById('submit-button').disabled = false;
            });
            if(groupCreation.code === 200){
                toastr.success('','Group is created');
                document.getElementById('submit-button').innerText = "Group Added"
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
            else{
                toastr.error('','Something went wrong');
            }
        })();
    });

    $('#groupsTable').DataTable({
        "initComplete": function (settings, json) {
            hideLoader();
        },
        bAutoWidth : false,
        ajax: '/users/api/ManagementController.php?action=getGroups',
        columns: [
            {
                className: "ta-c",
                data: "group_id"
            },
            {
                className: "ta-c",
                data: "group_name"
            },
            {
                className: "ta-c",
                data: "country_name"
            }
        ],
        paging: false,
        sorting: false,
        "language": {
            "info": ""
        }
    });

    $(document).ready(function () {
        (async () => {
            /*const moduleAndFunctionData = await $.ajax({
                url: '/users/api/ManagementController.php',
                method: 'GET',
                data: {
                    "action": "getAllModulesAndFunctions"
                }
            }).catch(e => {
                console.error(e);
                toastr.error('','Error occurred while fetching module and function data');
            });*/

            $('.chosen-select').chosen({width: "100%"});
        })();
    });

</script>
</html>



