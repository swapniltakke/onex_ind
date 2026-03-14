<?php
/*
 * Task Name: Snowflake Create Tokens
 * Interval: Every 30 minutes
 * Description: Creates Snowflake API JWT Tokens and saves to database
 *
 * */

require_once $_SERVER["DOCUMENT_ROOT"] . "/cronjobs/CronManagers.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/snowflake/SnowflakeQuery.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";


$getUsersQuery = "SELECT * FROM snowflake";
$getUsersQueryData = CronDbManager::fetchQueryData('php_auth', $getUsersQuery)["data"];

$updateQueryValues = "";
$updateQueryParameters = [];

$snowflakeObject = new Snowflake();
$index = 0;
foreach ($getUsersQueryData as $row){
    $userKeyword = $row["user_keyword"];
    $user = $row["user"];
    $orgCode = $row["org_code"];
    $costCenter = $row["cost_center"];

    $userTokenData = $snowflakeObject->createJWTToken($user);
    $userToken = $userTokenData["token"];
    $tokenValidFrom = date("Y-m-d H:i:s", $userTokenData["payload"]["iat"]);
    $tokenValidUntil = date("Y-m-d H:i:s", $userTokenData["payload"]["exp"]);

    $userKeywordKey = ":$index"."userKeyword";
    $userKey = ":$index"."userName";
    $tokenKey = ":$index"."jwtToken";
    $costCenterKey = ":$index"."costCenter";
    $orgCodeKey = ":$index"."orgCode";
    $validFromKey = ":$index"."validFrom";
    $validUntilKey = ":$index"."validUntil";
    $updateQueryValues .= "($userKeywordKey, $userKey, $tokenKey, $costCenterKey, $orgCodeKey, $validFromKey, $validUntilKey),";

    $updateQueryParameters[] = [
        $userKeywordKey => $userKeyword,
        $userKey => $user,
        $tokenKey => $userToken,
        $costCenterKey => $costCenter,
        $orgCodeKey => $orgCode,
        $validFromKey => $tokenValidFrom,
        $validUntilKey => $tokenValidUntil
    ];

    $index++;
}

$updateQueryValues = rtrim($updateQueryValues, ',');
$updateQuery = "
    INSERT INTO snowflake(`user_keyword`,`user`, `token`, cost_center, org_code, valid_from, valid_until) 
    VALUES $updateQueryValues
    ON DUPLICATE KEY UPDATE 
        `token`=VALUES(`token`),
        `cost_center`=VALUES(`cost_center`),
        `org_code`=VALUES(`org_code`),
        `valid_from`=VALUES(`valid_from`),
        `valid_until`=VALUES(`valid_until`)
";

CronDbManager::fetchQuery('php_auth', $updateQuery, $updateQueryParameters, [], false, false);
