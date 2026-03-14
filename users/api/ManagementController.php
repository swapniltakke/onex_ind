<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";

header('Content-Type:application/json;charset=utf-8;');

$controller = new ManagementController();
switch ($_GET["action"]) {
    case "getUsers":
        $controller->getUsers();
    break;
    case "getAllModulesAndFunctions":
        $controller->getAllModulesAndFunctions();
    break;
    case "getUserPermissions":
        $controller->getUserPermissions();
    break;
    case "searchUserInAD":
        $controller->searchUserInAD();
    break;
    case "getGroups":
        $controller->getGroups();
    break;
    default:
        break;
}

switch ($_POST["action"]) {
    case "deactivate":
        $controller->deactivateUser();
    break;
    case "activate":
        $controller->activateUser();
    break;
    case "createUser":
        $controller->createUser();
    break;
    case "editUser":
    $controller->editUser();
    break;
    case "createGroup":
        $controller->createGroup();
    break;
    default:
        break;
}
exit;

class ManagementController{
    private $currentUserGroupID;

    public function __construct(){
        $userGroupID = SharedManager::getUser()["GroupID"];
        if($userGroupID !== 2)
            returnHttpResponse(403, "Not Authorized");
        $this->currentUserGroupID = $userGroupID;
    }

    private function getModules(){
        $getModulesQuery = "SELECT module_id, name FROM modules";
        $modulesData = DbManager::fetchPDOQueryData('php_auth', $getModulesQuery)["data"];
        return array_combine(array_column($modulesData, 'module_id'), $modulesData);
    }

    private function getFunctions(){
        $getFunctionsQuery = "SELECT ModuleId, Action FROM subauth";
        $functionsData = DbManager::fetchPDOQueryData('php_auth', $getFunctionsQuery)["data"];
        return array_combine(array_column($functionsData, 'module_id'), $functionsData);
    }

    public function getUsers(){
        $userInfoQuery = "SELECT * FROM user_info";
        $userInfoData = DbManager::fetchPDOQueryData('php_auth', $userInfoQuery)["data"];

        $tableModuleHeaders = [];

        $modulesData = $this->getModules();
        foreach ($modulesData as $moduleData){
            $tableModuleHeaders[] = $moduleData["name"];
        }

        //$functionsData = $this->getFunctions();
        $userData = [];
        foreach ($userInfoData as $userInfo){
            $id = $userInfo["id"];
            $email = $userInfo["email"];
            $group_name = $userInfo["group_name"];
            $country_name = $userInfo["country_name"];
            $status = $userInfo["status"];

            $modules = $userInfo["modules"];
            $moduleNames = array_map(function ($moduleID) use ($modulesData) {
                return $modulesData[$moduleID]["name"];
            }, explode('|', $modules));
            $userModuleAccess = [];
            foreach ($modulesData as $module)
                $userModuleAccess[$module["name"]] = (in_array($module["name"], $moduleNames)) ? "X" : "";

            $lastLogin = $userInfo["last_login"];

            $userData[$email] = [
                "id" => $id,
                "email" => $email,
                "groupName" => $group_name,
                "countryName" => $country_name,
                "status" => (int)$status,
                "lastLogin" => [
                    "lastLoginTime" => strtotime($lastLogin),
                    "lastLoginDate" => ($lastLogin) ? date("d-m-Y H:i:s", strtotime($lastLogin)) : ""
                ],
                "moduleNames" => $userModuleAccess
            ];
        }
        echo json_encode([
            "tableModuleHeaders" => $tableModuleHeaders,
            "userData" => array_values($userData)
        ]); exit;
    }

    public function deactivateUser(){
        $userID = $_POST["id"];
        if(!is_numeric($userID))
            returnHttpResponse(400, "ID parameter has to be numeric");

        $deactivateQuery = "UPDATE users SET status = '0' WHERE id=:id";
        DbManager::fetchPDOQuery('php_auth', $deactivateQuery, [
            ":id" => $userID
        ]);

        returnHttpResponse(200, "success");
    }

    public function activateUser(){
        $userID = $_POST["id"];
        if(!is_numeric($userID))
            returnHttpResponse(400, "ID parameter has to be numeric");

        $deactivateQuery = "UPDATE users SET status = '1' WHERE id=:id";
        DbManager::fetchPDOQuery('php_auth', $deactivateQuery, [
            ":id" => $userID
        ]);

        returnHttpResponse(200, "success");
    }

    public function getAllModulesAndFunctions(){
        $returnData = [
            "modules" => [],
            "functions" => []
        ];
        $getModulesQuery = "SELECT module_id, `name`, url FROM modules ORDER BY module_id ASC";
        $getModulesQueryData = DbManager::fetchPDOQueryData('php_auth', $getModulesQuery)["data"];
        foreach ($getModulesQueryData as $moduleData){
            $returnData["modules"][] = [
                "module_id" => $moduleData["module_id"],
                "module_name" => $moduleData["name"],
                "url" => $moduleData["url"],
            ];
        }

        $getFunctionsQuery = "SELECT * FROM subauth_info ORDER BY subauthId ASC";
        $getFunctionsQueryData = DbManager::fetchPDOQueryData('php_auth', $getFunctionsQuery)["data"];
        foreach ($getFunctionsQueryData as $functionData){
            $returnData["functions"][] = [
                "function_id" => $functionData["subauthId"],
                "function_name" => $functionData["Action"],
            ];
        }

        echo json_encode($returnData); exit;
    }

    private function getRegisteredEmails(){
        $getRegisteredEmailsQuery = "SELECT email FROM users";
        $registeredUsers = DbManager::fetchPDOQueryData('php_auth', $getRegisteredEmailsQuery)["data"];
        $registeredEmails = [];
        foreach ($registeredUsers as $registeredUser)
            $registeredEmails[] = $registeredUser["email"];

        return $registeredEmails;
    }

    private function getGroupIDs(){
        $getGroupIDsQuery = "SELECT group_id FROM `groups`";
        $getGroupIDsQueryData = DbManager::fetchPDOQueryData('php_auth', $getGroupIDsQuery)["data"];
        $groupIDs = [];
        foreach ($getGroupIDsQueryData as $groupIdData) {
            $groupIDs[] = $groupIdData["group_id"];
        }
        return $groupIDs;
    }

    public function searchUserInAD(){
        $email = $_GET["email"];
        $gid = $_GET["gid"];

        if($email && strlen($email) < 10)
            returnHttpResponse(400, "Email should be at least 10 characters");
        if($gid && strlen($gid) < 6)
            returnHttpResponse(400, "GID should be at least 6 characters");

        $searchResult = null;
        if($email)
            $searchResult = SharedManager::ldap('','',"$email*");
        else if($gid)
            $searchResult = SharedManager::ldap('','','', "$gid*");
        else
            returnHttpResponse(400, "Email or GID should be given as parameters");

        $registeredEmails = $this->getRegisteredEmails();
        $returnData = [];
        for($i=0; $i<count($searchResult) && $i<5; $i++){
            $userEmail = $searchResult[$i]["Email"];

            if(!$userEmail)
                continue;
            if(in_array($userEmail, $registeredEmails))
                continue;

            $returnData[] = [
                "email" => $userEmail,
                "gid" => $searchResult[$i]["GID"],
                "department" => $searchResult[$i]["Department"]
            ];
        }

        echo json_encode($returnData); exit;
    }

    private function isArrayOfNumericValues($array) {
        foreach ($array as $value) {
            if (!is_numeric($value)) {
                return false;
            }
        }
        return true;
    }

    public function createUser(){
        $email = $_POST["email"];
        $modules = $_POST["modules"];
        $this->validateModulesParamater($modules);
        $modules = implode('|', $modules);

        $functions = $_POST["functions"];
        if($functions && !$this->isArrayOfNumericValues($functions))
            returnHttpResponse(400, "Functions should be numeric");
        $functions = ($functions) ? implode('|', $functions) : "";

        $groupId = $_POST["groupId"];
        $groupIDsInDB = $this->getGroupIDs();
        if(!in_array($groupId, $groupIDsInDB))
            returnHttpResponse(422, "GroupID: '$groupId' is not defined");

        $adSearchResult = SharedManager::ldap('','',"$email*");
        $this->validateUserEmail($email, $adSearchResult);

        $managerGID = $adSearchResult[0]["ManagerGID"];
        $managerInfo = SharedManager::ldap('','','', '', "$managerGID*");
        if(count($managerInfo) === 0)
            returnHttpResponse(422, "Manager '$managerGID' could not be found in AD");
        $managerEmail = $managerInfo[0]["Email"];

        $userName = $adSearchResult[0]["Name"];
        $userSurname = $adSearchResult[0]["Surname"];
        $userGID = $adSearchResult[0]["GID"];
        $userDisplayName = $adSearchResult[0]["DisplayName"];
        $userOrgCode = $adSearchResult[0]["Department"];
        $office = $adSearchResult[0]["Office"];
        $title = $adSearchResult[0]["Title"];
        $costCenter = $adSearchResult[0]["CostCenter"];
        $country = $adSearchResult[0]["Country"];

        $insertUserQuery = "
            INSERT INTO users
            (email, name, surname, displayname, group_id, modules, status, functions, current_org_code, manager_email, office, title, gid, cost_location, country)
            VALUES(:email, :name, :surname, :displayname, :group_id, :modules, :status, :functions, :current_org_code, :manager_email, :office, :title, :gid, :cost_location, :country)
        ";

        DbManager::fetchPDOQuery('php_auth', $insertUserQuery, [
            ':email' => $email,
            ':name' => $userName,
            ':surname' => $userSurname,
            ':displayname' => $userDisplayName,
            ':group_id' => (int) $groupId,
            ':modules' => $modules,
            ':status' => '1',
            ':functions' => $functions,
            ':current_org_code' => $userOrgCode,
            ':manager_email' => $managerEmail,
            ':office' => $office,
            ':title' => $title,
            ':gid' => $userGID,
            ':cost_location' => $costCenter,
            ':country' => $country,
        ]);

        $sendEmail = (int) $_POST["sendEmail"];
        if($sendEmail)
            $this->sendNotificationEmail($email);

        returnHttpResponse(200, "User '$email' is created");
    }

    public function getUserPermissions(){
        $userID = $_GET["id"];
        if(!is_numeric($userID))
            returnHttpResponse(400, "ID should be numeric");

        $getUserPermissionsQuery = "SELECT modules, functions FROM users WHERE id=:id";
        $getUserPermissionsQueryResult = DbManager::fetchPDOQueryData('php_auth', $getUserPermissionsQuery, [':id' => $userID])["data"];
        if(count($getUserPermissionsQueryResult) === 0)
            returnHttpResponse(400, "User is not found");

        echo json_encode($getUserPermissionsQueryResult[0]); exit;
    }

    private function validateUserEmail($email, $adSearchResult, $checkExistence = true){
        if(!filter_var($email, FILTER_VALIDATE_EMAIL))
            returnHttpResponse(400, "'$email' is not a valid email");
        if(!str_ends_with($email, '@siemens.com'))
            returnHttpResponse(400, "'$email' is not a valid Siemens email");

        if(count($adSearchResult) === 0)
            returnHttpResponse(400, "User '$email' is not found in AD");

        if($checkExistence){
            $registeredEmails = $this->getRegisteredEmails();
            if(in_array($email, $registeredEmails))
                returnHttpResponse(422, "User '$email' is already registered");
        }
    }

    private function validateModulesParamater($modules){
        if(!$modules || count($modules) === 0)
            returnHttpResponse(400, "Modules can not be empty");
        if(!$this->isArrayOfNumericValues($modules))
            returnHttpResponse(400, "Modules should be numeric");
    }

    private function sendNotificationEmail($email){
        $SELF_ORG_CODE = SharedManager::getFromSharedEnv('SELF_ORG_CODE');
        $mailSubject = "[$SELF_ORG_CODE] - Permission Granted";
        $mailBody = $_POST["mailBody"];

        MailManager::sendMail($mailSubject, $mailBody, 'new_user', [], [$email]);
    }

    public function editUser(){
        $email = $_POST["email"];
        $modules = $_POST["modules"];
        $this->validateModulesParamater($modules);
        $modules = implode('|', $modules);

        $functions = $_POST["functions"];
        if($functions && !$this->isArrayOfNumericValues($functions))
            returnHttpResponse(400, "Functions should be numeric");
        $functions = ($functions) ? implode('|', $functions) : "";

        $groupId = $_POST["groupId"];
        $groupIDsInDB = $this->getGroupIDs();
        if(!in_array($groupId, $groupIDsInDB))
            returnHttpResponse(422, "GroupID: '$groupId' is not defined");

        $adSearchResult = SharedManager::ldap('','',"$email*");
        $this->validateUserEmail($email, $adSearchResult, false);

        $editUserQuery = "
            UPDATE users
            SET
                group_id = :group_id,
                modules = :modules,
                functions = :functions,
                status = :status
            WHERE
                email = :email
            LIMIT 1
        ";

        DbManager::fetchPDOQuery('php_auth', $editUserQuery, [
            ':email' => $email,
            ':group_id' => (int) $groupId,
            ':modules' => $modules,
            ':status' => '1',
            ':functions' => $functions
        ]);

        $sendEmail = (int) $_POST["sendEmail"];
        if($sendEmail)
            $this->sendNotificationEmail($email);

        returnHttpResponse(200, "User '$email' is created");
    }

    public function getGroups(){
        $getGroupsQuery = "SELECT group_id, group_name, name AS country_name FROM group_info";
        $resultData = DbManager::fetchPDOQueryData('php_auth', $getGroupsQuery)["data"];
        echo json_encode(["data" => $resultData]); exit;
    }

    public function createGroup(){
        $groupName = $_POST["groupName"];
        $countryId = $_POST["countryId"];
        if(!is_numeric($countryId))
            returnHttpResponse(400 , "Country ID should be numeric");
        if(strlen($groupName) < 5)
            returnHttpResponse(400 , "Group name should be at least 5 characters");

        $checkGroupExistenceQuery = "
            SELECT `id` FROM `groups` 
            WHERE 
                group_name = :group_name AND
                country_id = :country_id 
        ";
        $checkGroupExistenceQueryResult = DbManager::fetchPDOQueryData('php_auth', $checkGroupExistenceQuery, [
            ':group_name' => $groupName,
            ':country_id' => $countryId
        ])["data"];
        if(count($checkGroupExistenceQueryResult) !== 0)
            returnHttpResponse(422, "Same group already exists");

        $newGroupID = (int) DbManager::fetchPDOQueryData('php_auth', "SELECT MAX(group_id)+1 AS newGroupID FROM `groups`")["data"][0]["newGroupID"];

        $createGroupQuery = "INSERT INTO `groups`(group_id, group_name, country_id) 
        VALUES(:newGroupID, :group_name, :country_id)";
        DbManager::fetchPDOQuery('php_auth', $createGroupQuery, [
            ':newGroupID' => $newGroupID,
            ':group_name' => $groupName,
            ':country_id' => $countryId
        ]);

        returnHttpResponse(200, "Group '$groupName' is created");
    }
}