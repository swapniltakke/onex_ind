<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/users/auth.php';
$userID = $_GET["id"];
if(!is_numeric($userID))
    returnHttpResponse(400, "ID should be numeric");
$userDataResult = DbManager::fetchPDOQueryData('php_auth', "SELECT * FROM users WHERE id=:id", [':id' => $userID])["data"];
if(count($userDataResult) === 0)
    returnHttpResponse(400, "User not found");
$userData = $userDataResult[0];
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit User | OneX</title>

    <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/users/components/headimports.php"; ?>
    <script src="/shared/inspia_gh_assets/js/plugins/summernote/summernote-bs4.js"></script>
    <style>
        .error {
            border: 2px solid #f00;
        }

        .va-m{ vertical-align: middle !important; }

        .selectUserButton{
            text-decoration: underline;
            color: blue;
        }
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
                    <h3 class="card-title">Edit User</h3>
                </div>
                <div class="row justify-content-center m-lg-4" style="margin-top: 2rem !important;">
                    <div class="col-12 col-lg-7">
                        <div class="form-group row">
                            <label for="emailinput" class="col-lg-2 col-form-label">Selected Email</label>
                            <div class="col-lg-10">
                                <input id="emailinput" type="email" placeholder=""
                                       class="form-control required"
                                       value="<?= $userData['email']; ?>" required disabled>
                                <span class="form-text m-b-none"></span>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="group" class="col-lg-2 col-form-label">Group Name</label>
                            <div class="col-lg-10">
                                <select name="group_id" class="form-control selectpicker required" id="group_id"
                                        data-live-search="true" required>
                                    <option value="0">Select Department(Country)</option>
                                    <?php
                                    $getGroupsQuery = "
                                        SELECT 
                                            group_id,
                                            group_name,
                                            `name` AS countryName
                                        FROM `groups`
                                        JOIN countries
                                        ON `groups`.country_id = countries.country_id
                                    ";
                                    $getGroupsQueryData = DbManager::fetchPDOQueryData('php_auth', $getGroupsQuery)["data"];
                                    foreach ($getGroupsQueryData as $groupData){
                                        $groupid = $groupData["group_id"];
                                        $group_name = $groupData["group_name"];
                                        $country_name = $groupData["countryName"];
                                        $isSelected = '';
                                        if($userData["group_id"] === $groupid)
                                            $isSelected = "selected";

                                        echo "<option value=\"$groupid\" $isSelected>$group_name ($country_name)</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-2 col-form-label">Access to Modules</label>
                            <div class="col-lg-10">
                                <select id="divmodule" data-placeholder="List of system/tools..." class="chosen-select required" multiple="multiple" tabindex="4">
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-2 col-form-label">Access to Functions</label>
                            <div class="col-lg-10">
                                <select data-placeholder="List of system/tools..."
                                        class="chosen-select"
                                        id="subAuthdiv" multiple="multiple"
                                        tabindex="4">
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-2 col-form-label">User Status</label>
                            <div class="col-lg-10">
                                <select class="form-control required" id="status">
                                    <option value="1" selected>Active</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-2 col-form-label">E-mail Language</label>
                            <div class="col-lg-10">
                                <select class="form-control" id="lang">
                                    <option value="en" select>English</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row ">
                            <label class="col-lg-2 col-form-label">E-mail Subject</label>
                            <div class="col-lg-10">
                                <input class="form-control required" name="email_subject" id="email_subject" value="">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-2 col-form-label">E-mail Body</label>
                            <div class="col-lg-12">
                                <textarea class="form-control required" id="email_body" name="email_body">

                                </textarea>
                            </div>
                        </div>
                        <div class="form-group ">
                            <input type="checkbox" id="sendEmail" checked>
                            <label>Send E-mail</label>
                        </div>
                        <div class="form-group row">
                            <div class="col align-self-end">
                                <button id="submit-button" class="btn btn-lg btn-primary float-right">
                                    Edit User
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
<script src="/shared/shared.js"></script>
<script>
    function fillMailBodyAndSubject(lang) {
        const selectedModulesIDs = $('#divmodule').chosen().val();
        const selectedModuleOptions =
            Array.from(document.querySelectorAll('#divmodule option'))
                .filter(option => selectedModulesIDs.includes(option.value));

        const selectedModuleNames = selectedModuleOptions.map(option => option.innerText);
        const selectedModuleURL = selectedModuleOptions.map(option => option.getAttribute('data-url'));
        let mailBodyModules = ``;
        for(const [index, moduleName] of Object.entries(selectedModuleNames))
            mailBodyModules += `${moduleName} (<a href='${selectedModuleURL[index]}' target='_blank'>${selectedModuleURL[index]}</a>)<br>`;

        const email = '<?= SharedManager::getUser()['Email']; ?>';
        const bodies = {
            "en": `Hello,<br>
                You have been granted access to OneX. (Authorized by: <b>${email}</b>)<br>
                Allowed modules: <br><br><b>${mailBodyModules}</b>
                <br>
                <br>
                You can access OneX from the <b><?= SharedManager::getFromSharedEnv("SITE_URL"); ?></b> address by using
                the <b>Microsoft Edge / Google Chrome</b> browser.<br><br>

                <?= SharedManager::getFromSharedEnv("SELF_ORG_CODE"); ?>`
        };
        const subjects = {
            "en": "[<?= SharedManager::getFromSharedEnv("SELF_ORG_CODE"); ?>] OneX Access Granted."
        };
        if (lang === 'en') {
            $('#email_body').summernote('code', bodies["en"]);
            $('#email_subject').val(subjects["en"]);
        }
        return true;
    }

    $('#divmodule').change(function () {
        fillMailBodyAndSubject($('#lang').val());
    });

    document.getElementById('submit-button').addEventListener('click', () => {
        const email = document.getElementById('emailinput').value;
        const selectedGroupName = document.querySelector('[data-id="group_id"]').getAttribute('title');
        const selectedGroupID = Array.from(document.querySelectorAll('#group_id option')).filter(option => { return selectedGroupName === option.innerText})[0].value
        const selectedModules = $('#divmodule').chosen().val();
        const selectedFunctions = $('#subAuthdiv').chosen().val();

        if(!+selectedGroupID){
            toastr.warning('',"User's group should be selected");
            return;
        }
        if(selectedModules.length === 0){
            toastr.warning('',"Please select a module");
            return;
        }

        const mailBody = $('#email_body').summernote('code');

    

        (async () => {
            document.getElementById('submit-button').disabled = true;
            document.getElementById('submit-button').innerText = "Processing"
            const userCreation = await $.ajax({
                url: '/users/api/ManagementController.php',
                method: 'POST',
                data: {
                    "action": "editUser",
                    "email": email,
                    "groupId": selectedGroupID,
                    "modules": selectedModules,
                    "functions": selectedFunctions,
                    "mailBody": mailBody,
                    "sendEmail": +document.getElementById('sendEmail').checked
                }
            }).catch(e => {
                console.error(e);
                toastr.error('','Error occurred while creating user');
                document.getElementById('submit-button').innerText = "Edit User"
                document.getElementById('submit-button').disabled = false;
            });
            if(userCreation.code === 200){
                toastr.success('','User information is updated');
                document.getElementById('submit-button').innerText = "User Edited"
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
            else{
                toastr.error('','Something went wrong');
            }
        })();
    });

    $(document).ready(function () {
        $('#email_body').summernote({
            tabsize: 2,
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview']]
            ]
        });

        (async () => {
            const moduleAndFunctionData = await $.ajax({
                url: '/users/api/ManagementController.php',
                method: 'GET',
                data: {
                    "action": "getAllModulesAndFunctions"
                }
            }).catch(e => {
                console.error(e);
                toastr.error('','Error occurred while fetching module and function data');
            });

            const userPermissions = await $.ajax({
                url: '/users/api/ManagementController.php',
                method: 'GET',
                data: {
                    "action": "getUserPermissions",
                    "id": getUrlParameters()['id']
                }
            }).catch(e => {
                console.error(e);
                toastr.error('','Error occurred while fetching user permission data');
            });

            const allModules = moduleAndFunctionData["modules"];
            const allFunctions = moduleAndFunctionData["functions"];
            const userModulePermissions = userPermissions["modules"].split('|');
            const userFunctionPermissions = userPermissions["functions"].split('|');

            let modulesSelectHTML = ``;
            for(const moduleData of allModules){
                const moduleId = moduleData["module_id"];
                const moduleName = moduleData["module_name"];
                const moduleURL = moduleData["url"];
                let isSelected = "";
                if(userModulePermissions.includes(moduleId))
                    isSelected = "selected";
                modulesSelectHTML += `<option value="${moduleId}" data-url='${moduleURL}' ${isSelected}>${moduleName}</option>`
            }
            document.querySelector('#divmodule').innerHTML = modulesSelectHTML;

            let functionsSelectHTML = ``;
            for(const functionData of allFunctions){
                const functionId = functionData["function_id"];
                const functionName = functionData["function_name"];
                let isSelected = "";
                if(userFunctionPermissions.includes(functionId))
                    isSelected = "selected";
                functionsSelectHTML += `<option value="${functionId}" ${isSelected}>${functionName}</option>`
            }
            document.querySelector('#subAuthdiv').innerHTML = functionsSelectHTML;

            $('.chosen-select').chosen({width: "100%"});

            fillMailBodyAndSubject($('#lang').val());
        })();
    });

</script>
</html>



