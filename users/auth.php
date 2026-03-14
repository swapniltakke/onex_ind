<?php
$userGroupID = SharedManager::getUser()["GroupID"];
if($userGroupID !== (int) SharedManager::getFromSharedEnv('ADMIN_GROUP_ID')){
    $email = SharedManager::getUser()["Email"];
    $userName = SharedManager::getUser()["Name"];
    $userSurname = SharedManager::getUser()["Surname"];
    $REDIRECT_PATH = "https://$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]";
    $this->redirect("/shared/screens/notallowed.php?email=$email&name=$userName&surname=$userSurname&link=$REDIRECT_PATH");
    exit;
}



