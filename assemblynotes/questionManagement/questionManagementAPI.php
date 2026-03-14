<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/assemblynotes/core/index.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";
$type = $_GET["action"] ?? $_POST["action"];
selectMethod($type);
function selectMethod($type = "")
{
    switch ($type) {
        case "checkOrderRule":
            $searchedProject = $_GET["searchedProject"] ?? '';
            $response = checkOrderCluster($searchedProject);
            echo json_encode($response, JSON_PRETTY_PRINT);
            break;
        case "deleteOrderRule":
            $id = $_GET["id"] ?? 0;
            deleteOrderRule($id);
            echo json_encode(1, JSON_PRETTY_PRINT);
            break;
        case "saveOrderRule":
            saveOrderRule();
            echo json_encode(1, JSON_PRETTY_PRINT);
            break;
        case "getOrderRules":
            $rules = getOrderRules();
            $userGroupId = (int)SharedManager::getUser()["GroupID"] ?? 0;
            echo json_encode(["rules" => $rules, "userGroupId" => $userGroupId], JSON_PRETTY_PRINT);
            break;
    }
    exit();
}

function deleteOrderRule($id = 0): void
{
    $query = "delete from tquestion_rules where Id= ?";
    $response = DbManager::fetchPDOQuery('assembly_items', $query, [$id]);
    if ($response === false) {
        http_response_code(500);
    }
    http_response_code(200);
}

function getOrderRules()
{
    $query = "select Id,Cluster,OwnerCluster,Created_By,date_format(Created_At,'%d/%m/%Y %H:%i') as Created_At from tquestion_rules 
                                                                                                 where Created_At>= DATE_SUB(now(), INTERVAL 6 MONTH)
                                                                                                 order by Created_At desc";
    $response = DbManager::fetchPDOQueryData('assembly_items', $query)["data"];
    if ($response === false) {
        http_response_code(500);
    }
    return $response;
}

function checkOrderCluster($searchedProjectNo = "")
{
    $queryCluster = "select Id,Cluster,OwnerCluster from tquestion_rules where Cluster like :p1";
    $queryOwner = "select Id,Cluster,OwnerCluster from tquestion_rules where OwnerCluster = :p1";

    $responseOwner = DbManager::fetchPDOQueryData('assembly_items', $queryOwner, [":p1" => $searchedProjectNo])["data"];
    if ($responseOwner === false) {
        http_response_code(500);
        exit();
    }

    if (!empty($responseOwner)) {
        return (object)["data" => $responseOwner, "state" => true];
    }

    $responseCluster = DbManager::fetchPDOQueryData('assembly_items', $queryCluster, [":p1" => "%$searchedProjectNo%"])["data"];
    if ($responseCluster === false) {
        http_response_code(500);
        exit();
    }
    if (!empty($responseCluster)) {
        $tempStr = "";
        foreach ($responseCluster as $index => $item) {
            if (!str_contains($tempStr, $item["OwnerCluster"]))
                $tempStr .= $item["OwnerCluster"] . " - ";
        }
        return (object)["data" => $tempStr, "state" => false];
    } else {
        // kural tanımlanmamış
        return (object)["data" => false, "state" => false];
    }
    http_response_code(200);
}

function saveOrderRule()
{
    $clusterArray = json_decode($_GET["cluster"] ?? []);
    $clusterStr = implode(",", $clusterArray);
    $ownerProject = $_GET["ownerProject"] ?? "";
    if (empty($ownerProject) || empty($clusterArray)) {
        http_response_code(500);
    }
    $insertQuery = "insert into tquestion_rules (Cluster,OwnerCluster,Created_By) 
values(:p1,:p2,:p3)";
    $response = DbManager::fetchPDOQueryData('assembly_items', $insertQuery, [":p1" => $clusterStr, ":p2" => $ownerProject, ":p3" => SharedManager::getUser()["Email"]]);
    if ($response === false) {
        http_response_code(500);
    }
    http_response_code(200);
    return $response;
}

