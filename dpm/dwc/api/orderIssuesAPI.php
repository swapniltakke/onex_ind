<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/api/MToolManager.php";

$type = $_GET["type"] ?? $_POST["type"];
$uploadDirectoryBase = $_SERVER["DOCUMENT_ROOT"]."/assemblynotes/orderIssues/uploads";
switch ($type) {
    case "getStatusWidgets":
        getStatusWidgets();
    break;
    case "getPanelNumbers":
        getPanelsByProjectNo();
    break;
    case "listTable":
        $projectNoParam = $_GET["projectNoParam"] ?? null;
        getOrderIssuesForTable($projectNoParam);
    break;
    case "list":
        getOrderIssues();
    break;
    case "listIssueCodes":
        getIssueCodes();
    break;
    case "getOrderDetail":
        getOrderDetails();
    break;
    case "listMails":
        getMailAddresses();
    break;
    case "getItem":
        $param = $_GET["param"] ?? 0;
        $responseItems = getOrderIssueById($param);
        echo json_encode($responseItems, JSON_THROW_ON_ERROR);
    break;
    case "setItemStatus":
        setItemStatus();
    break;
    case "setOmStatus":
        $id = $_GET["id"] ?? 0;
        $statusId = $_GET["omStatusId"] ?? 0;
        setOmStatus($id, $statusId);
    break;
    case "setLineStopEffect":
        $id = $_GET["id"] ?? 0;
        $hasLineStop = $_GET["hasLineStop"] ?? 0;
        setLineStopEffect($id, $hasLineStop);
    break;
    case "deactive":
        setDeactiveOrderIssue();
    break;
    case "save":
        $entity = json_decode($_POST["entity"] ?? []);
        ($entity->id > 0) ? updateOrderIssue() : insertOrderIssue();
    break;
    case "deleteImage":
        deleteImage();
    break;
    case "listOneXUserMails":
        $entityList = getUserMails();
        echo json_encode($entityList);
    break;
    default :
    break;
}
exit;

function getUserMails()
{
    $queryOnexUsers = "
        SELECT 
            email AS 'Name',
            email AS 'Mail' 
        FROM 
            users 
        WHERE 
            `status`=1 AND
            is_critical_manager_email=0
    ";

    $onexUsers = DbManager::fetchPDOQueryData('php_auth', $queryOnexUsers)["data"];
    $arr = [];
    foreach ($onexUsers as $row) {
        $name = $row['Name'];
        $mail = $row['Mail'];
        $item = str_contains($name, "@");
        $nameArr = explode('@siemens.com', $name);
        $arr['items'][] = array(
            "fullInfo" => "$name",
            "mail" => "$name",
            "name" => "$nameArr[0]"
        );
    }

    $object_return = (object)$arr;
    return $object_return;
}
function deleteImage()
{
    try {
        $file_path = $_POST["file_path"] ?? null;
        $file_path = explode("assemblynotes\\\\", $file_path)[1];
        $file_path = __DIR__ . "/../" . $file_path;
        $file_path = rtrim($file_path, '"');
        if (file_exists($file_path)) {
            unlink($file_path);
            echo json_encode(["message" => "Success", "code" => "200"], JSON_THROW_ON_ERROR);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error on status update", "code" => "500"], JSON_THROW_ON_ERROR);
        }
    } catch (Throwable $ex) {
        throw new Exception($ex->getMessage());
    }
    exit;
}

function setItemStatus()
{
    try {
        $closeStatement = "";
        $id = $_GET["id"] ?? 0;
        $statusId = $_GET["statusId"] ?? 0;
        if ($statusId == 2) {
            $closeStatement = " , closed_At = NOW()";
        }
        $updateQuery = "UPDATE torder_issues SET StatusId = :StatusId $closeStatement  WHERE Id = $id ";

        $response = DbManager::fetchPDOQuery('assembly_items', $updateQuery, [":StatusId" => $statusId]);
        if ($response === false) {
            http_response_code(500);
            echo json_encode(["message" => "Error on status update", "code" => "500"], JSON_THROW_ON_ERROR);
        }
        echo json_encode(["message" => "Success", "code" => "200"], JSON_THROW_ON_ERROR);
    } catch (Throwable $ex) {
        throw new Exception($ex->getMessage());
    }
    exit;
}

function getIssueCodes()
{
    try {
        $query = "select t.* from tissue_codes as t";
        $response = DbManager::fetchPDOQueryData('assembly_items', $query)["data"];
        echo json_encode($response, JSON_THROW_ON_ERROR);
    } catch (Throwable $ex) {
        throw new Exception($ex->getMessage());
    }
    exit;
}

function getPanelsByProjectNo()
{
    $orderNo = $_GET["orderNo"] ?? "";
    if(!is_numeric($orderNo))
        returnHttpResponse(400, "Incorrect project number");

    $response = MToolManager::getProjectPosData($orderNo);
    $data = [];
    foreach (array_values($response) as $row){
        $posNo = $row["PosNo"];
        $data[] = $posNo;
    }
    echo json_encode($data, JSON_THROW_ON_ERROR);
    exit;
}

function getOrderDetails()
{
    try {
        $orderNo = $_GET["orderNo"] ?? "";
        $query = "
            SELECT 
                dbo.OneX_ProjectContacts.FactoryNumber,
                dbo.OneX_ProjectContacts.OMEmail,
                dbo.OneX_ProjectContacts.OrderManager,
                dbo.OneX_ProjectContacts.ProjectId,
                dbo.OneX_ProjectContacts.ProjectName,
                COALESCE(dbo.OneX_ProjectOverviewEE.SwitchGearType, 'Undefined') AS SwitchGearType,
                dbo.OneX_ProjectTrackingEE.Qty 
            FROM 
                dbo.OneX_ProjectContacts
            LEFT JOIN dbo.OneX_ProjectTrackingEE 
                ON dbo.OneX_ProjectContacts.FactoryNumber = dbo.OneX_ProjectTrackingEE.FactoryNumber
            LEFT JOIN dbo.OneX_ProjectOverviewEE 
                ON dbo.OneX_ProjectContacts.FactoryNumber=dbo.OneX_ProjectOverviewEE.FactoryNumber
            WHERE 
                dbo.OneX_ProjectContacts.FactoryNumber = :OrderNo
            ORDER BY 
                ProjectId DESC
        ";
        $response = DbManager::fetchPDOQueryData('MTool_INKWA', $query, [":OrderNo" => $orderNo])["data"][0];
        echo json_encode($response);
    } catch (Throwable $ex) {
        throw new Exception($ex->getMessage());
    }

    exit;
}

function setDeactiveOrderIssue()
{
    try {
        $id = json_decode($_GET["param"] ?? 0);
        if ($id < 1) {
            http_response_code(500);
            echo json_encode(["message" => "Error on deleting note", "code" => "500"], JSON_THROW_ON_ERROR);
            exit;
        }
        $updatedBy = SharedManager::getUser()["Name"] . ' ' . SharedManager::getUser()["Surname"];
        $updatedByGid = SharedManager::getUser()["GID"];
        $updateQuery = "
            UPDATE torder_issues 
            SET IsActive = 0,
            Updated_By = :updatedBy, 
            Updated_By_Gid = :updatedByGID
            WHERE Id = :ID
        ";

        $response = DbManager::fetchPDOQuery('assembly_items', $updateQuery, [":updatedBy" => $updatedBy, ":updatedByGID" => $updatedByGid, ":ID" => $id]);
        http_response_code(200);
        echo json_encode(["message" => "Success", "code" => "200"]);
        exit;
    } catch (Throwable $ex) {
        throw new Exception($ex->getMessage());
    }
}

function uploadFiles($orderNo, $id = 0)
{
    global $uploadDirectoryBase;
    $uploadDirectory = $uploadDirectoryBase . $orderNo . "-" . $id . "\\";

    if (!file_exists($uploadDirectory) && !empty($_FILES))
        mkdir($uploadDirectory, 0700, true);
    foreach ($_FILES as $file) {
        $fileSize = $file['size'];
        if ($fileSize > 4000000) {
            http_response_code(500);
            echo json_encode(["message" => $file['name'] . " size of the files is more than 40MB", "code" => 500], JSON_THROW_ON_ERROR);
            exit;
        }
    }
    foreach ($_FILES as $file) {
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $uploadPath = $uploadDirectory . basename($fileName);
        if (!file_exists($uploadPath)) {
            $didUpload = move_uploaded_file($fileTmpName, $uploadPath);
            if (!$didUpload) {
                echo json_encode(["message" => "Error occurred while uploading ". basename($fileName), "code" => 500], JSON_THROW_ON_ERROR);
                exit;
            }
        }
    }

    return true;
}

function getOrderIssueFiles($orderNo = '', $id = 0)
{
    $splitCharacter = $orderNo . "-" . $id . "\\";
    global $uploadDirectoryBase;
    $uploadDirectory = $uploadDirectoryBase . $splitCharacter;

    $files = readDirectory($uploadDirectory, true);

    $returnFiles = [];

    foreach ($files as $file) {

        $splitCharacters = explode($splitCharacter, $file);

        $filePath = "https://onex.siemens.com.tr" . "//lvfolder//01_SIPARISLER//04.LV_Open_issues//" . $orderNo . "-" . $id . "//" . explode('\\', $splitCharacters[1])[1];
        $details = array();
        $details['name'] = explode('\\', $splitCharacters[1])[1];
        $details['path'] = $filePath;
        $returnFiles[] = $details;
    }

    return $returnFiles;
}

function insertOrderIssue()
{
    try {
        $entity = json_decode($_POST["entity"] ?? []);
        $qNumber = 0;
        $parentCreatedByEmail = "";
        if (empty($entity->parentId)) {
            $entity->parentId = 0;
            $responseItem = getOrderIssueByProjectNo($entity->orderNo);
            $qNumber = ($responseItem["QNumber"] ?? 0) + 1;
        }
        if ($entity->parentId > 0) {
            $entityItem = getOrderIssueById($entity->parentId);
            $qNumber = $entityItem["QNumber"];
            if (!empty($entityItem["Created_By"] && str_contains($entityItem["Created_By"], '@siemens.com'))) {
                $parentCreatedByEmail = $entityItem["Created_By"];
            }
        }
        $createdBy = SharedManager::getUser()["Email"];
        $createdByGid = SharedManager::getUser()["GID"];
        $toMailsStr = implode(';', ($entity->toMails ?? [])) . ";" . $parentCreatedByEmail;
        $ccMailsStr = implode(';', ($entity->ccMails ?? []));
        $lineStopEffect_At = null;
        if ($entity->hasLineStopEffect) {
            $lineStopEffect_At = "NOW()";
        }


        $insertQuery = "insert into torder_issues (
            Qnumber,
            OrderNo,
            ParentId,
            CodeId,
            PanelNumber,
            Note,
            ReferenceCode,
            Created_By, 
            Created_By_Gid,
            toMails,
            ccMails,
            hasLineStopEffect, 
            hasLineStopEffect_At
        ) values (
            :qNumber,
            :orderNo,
            :parentId,
            :codeId,
            :panelNumber,
            :note,
            :referenceCode,
            :createdBy,
            :created_ByGid,
            :toMailsStr,
            :ccMailsStr,
            :hasLineStopEffect,
            :lineStopEffectAt
            )";

         DbManager::fetchPDOQuery('assembly_items', $insertQuery, [
            ":qNumber" => $qNumber,
            ":orderNo" => $entity->orderNo,
            ":parentId" => $entity->parentId,
            ":codeId" => $entity->codeId,
            ":panelNumber" => $entity->panelNumber,
            ":note" => $entity->note,
            ":referenceCode" => $entity->referenceCode,
            ":createdBy" => $createdBy,
            ":created_ByGid" => $createdByGid,
            ":toMailsStr" => $toMailsStr,
            ":ccMailsStr" => $ccMailsStr,
            ":hasLineStopEffect" => $entity->hasLineStopEffect,
            ":lineStopEffectAt" => $lineStopEffect_At
        ]);
        $responseItem = getOrderIssueByEntity($entity, $createdBy, $createdByGid);
        sendOrderIssueMail(true, $responseItem["Id"] ?? 0, $responseItem["ParentId"] ?? 0, $qNumber);
        uploadFiles($entity->orderNo, $responseItem["Id"]);
        http_response_code(200);
        echo json_encode(["message" => "Success", "code" => "200"]);
        exit;
    } catch (Throwable $ex) {
        http_response_code(500);
        echo json_encode(["message" => "Error on save", "code" => "500"]);
        exit;
    }
}

function updateOrderIssue()
{
    try {
        $entity = json_decode($_POST["entity"] ?? [], false, 512, JSON_THROW_ON_ERROR);
        uploadFiles($entity->orderNo, $entity->id);
        $updatedBy = SharedManager::getUser()["Name"] . ' ' . SharedManager::getUser()["Surname"];
        $updatedByGid = SharedManager::getUser()["GID"];

        $toMailsStr = implode(';', ($entity->toMails ?? [])) . ";" . $entity->orderManagerMail;
        $ccMailsStr = implode(';', ($entity->ccMails ?? []));
        $entityItem = getOrderIssueById($entity->id);
        $statementForLineStopEffect = '';
        if ($entityItem["hasLineStopEffect"] != $entity->hasLineStopEffect) {
            // değişim var
            if ($entity->hasLineStopEffect) {
                $statementForLineStopEffect = ' hasLineStopEffect=1, hasLineStopEffect_At=NOW(), ';
            } else {
                $statementForLineStopEffect = ' hasLineStopEffect=0, hasLineStopEffect_At=null, ';
            }
        }
        $updateQuery = "update torder_issues set $statementForLineStopEffect 
                        ReferenceCode = :referenceCode,
                        CodeId=:codeId, 
                         Note =:note,
                         Updated_By= :updatedBy, Updated_By_Gid= :updatedByGid, 
                         ccMails=:ccMailsStr, toMails=:toMailsStr
                        where Id = :id";

        $response = DbManager::fetchPDOQuery('assembly_items', $updateQuery, [
            ":referenceCode" => $entity->referenceCode,
            ":codeId" => $entity->codeId,
            ":note" => $entity->note,
            ":updatedBy" => $updatedBy,
            ":updatedByGid" => $updatedByGid,
            ":ccMailsStr" => $ccMailsStr,
            ":toMailsStr" => $toMailsStr,
            ":id" => $entity->id
        ]);
        if ($response == false) {
            http_response_code(500);
            echo json_encode(["message" => "Error on save", "code" => "500"], JSON_THROW_ON_ERROR);
            exit;
        } else {
            sendOrderIssueMail(false);
            http_response_code(200);
            echo json_encode(["message" => "Success", "code" => "200"]);
            exit;
        }
    } catch (Throwable $ex) {
        throw new Exception($ex->getMessage());
    }
}

function getOrderIssueById($param = 0)
{
    try {
        $query = "select t.* from torder_issues as t where t.IsActive=1 and t.Id = :ID";
        $returnValues = DbManager::fetchPDOQueryData('assembly_items', $query, [":ID" => $param])["data"];
        $responseItems = null;

        foreach ($returnValues as $row) {
            $arrayItems = array_values(getOrderIssueFiles($row["OrderNo"], $row["Id"])) ?? [];
            $row["files"] = $arrayItems;
            $responseItems = $row;
        }
        return $responseItems;
    } catch (Throwable $ex) {
        throw new Exception($ex->getMessage());
    }

    exit;
}

function getOrderIssueByProjectNo($orderNo = '')
{
    try {
        $itemSelectQuery = "select * from torder_issues where OrderNo=:orderNo and ParentId=0 order by QNumber desc LIMIT 1";
        $response = DbManager::fetchPDOQueryData('assembly_items', $itemSelectQuery, [":orderNo" => $orderNo])["data"];
        if ($response === false) {
            http_response_code(500);
            echo json_encode(["message" => "Not okunamadı.", "code" => "500"], JSON_THROW_ON_ERROR);
            exit;
        }
        return $response[0];
    } catch (Throwable $ex) {
        http_response_code(500);
        echo json_encode(["message" => "getOrderIssueByProjectNo method hatası.", "code" => "500"], JSON_THROW_ON_ERROR);
        exit;
    }
}

function getOrderIssueByEntity($entity, $createdBy, $createdByGid)
{
    try {
        $itemSelectQuery = "select * from torder_issues where OrderNo= :orderNo and Note= :note 
                              and ParentId=:parentId and Created_By= :createdB and Created_By_Gid= :created_ByGid ORDER BY Id DESC LIMIT 1";
        $response = DbManager::fetchPDOQueryData('assembly_items', $itemSelectQuery, [
            ":orderNo" => $entity->orderNo,
            ":note" => $entity->note,
            ":parentId" => $entity->parentId,
            ":createdB" => $createdBy,
            ":created_ByGid" => $createdByGid
        ])["data"];
        if ($response == false || empty($response[0])) {
            http_response_code(500);
            echo json_encode(["message" => "Dosya kaydedilemedi.", "code" => "500"], JSON_THROW_ON_ERROR);
            exit;
        }
        return $response[0];
    } catch (Throwable $ex) {
        http_response_code(500);
        echo json_encode(["message" => "Dosya kaydedilemedi.", "code" => "500"], JSON_THROW_ON_ERROR);
        exit;
    }
}

function decideFilterQueryByOmStatus($omStatusIdParam = 0)
{
    $query_item_where_1 = "";
    $query_item_where_2 = "";

    switch ($omStatusIdParam) {
        case 4:
            $query_item_where_1 = " and t.OmStatusId=1";
            $query_item_where_2 = " and t1.OmStatusId=1";
            break;
        case 5:
            $query_item_where_1 = " and t.OmStatusId=0";
            $query_item_where_2 = " and t1.OmStatusId=0";
            break;
        default:
            break;
    }

    return [
        "query_where_section_1" => $query_item_where_1,
        "query_where_section_2" => $query_item_where_2
    ];
}

function decideFilterQuery()
{
    $parameters_pdo = [];
    $projectNo = $_GET["projectNo"] ?? "all";
    $params = empty($_GET["param"]) == false ? $_GET["param"] : "all";
    $statusId = (!empty($_GET["statusCode"]) ? $_GET["statusCode"] : 0) ?? 0;
    $omStatusId = (!empty($_GET["omStatusId"]) ? $_GET["omStatusId"] : 0) ?? 0;
    $omStatusWhereClauseArr = decideFilterQueryByOmStatus($omStatusId);
    $sortClause = " order by t.ParentId,t.QNumber, t.Created_At asc";
    $query = "select t.* from vw_order_issues as t where t.IsActive =1" . $omStatusWhereClauseArr["query_where_section_1"];

    if ($statusId > 0) {
        $query = "
            select distinct * from (
        select * from vw_order_issues as t where t.IsActive =1 and t.StatusId= :statusID1 " . $omStatusWhereClauseArr["query_where_section_1"] . "
    union all 
    select * from vw_order_issues as t1 where t1.IsActive =1  " . $omStatusWhereClauseArr["query_where_section_2"] . " and t1.StatusId= :statusID2 and (t1.ParentId=0 or t1.ParentId in (select Id from torder_issues tn where tn.StatusId= :statusID3)) ) 
       as tl where tl.IsActive =1";
        $parameters_pdo = [
            ":statusID1" => $statusId,
            ":statusID2" => $statusId,
            ":statusID3" => $statusId
        ];
    }

    if ($params == "all" && !is_numeric($projectNo) && $statusId < 1) {
        $sortClause .= " limit 5";
        $returnQuery = $query . " " . $sortClause;
        return ["queryStr" => $returnQuery, "pdoParams" => $parameters_pdo];
    }
    if (is_numeric($projectNo)) {
        $query .= " AND OrderNo=:projectNo";
        $parameters_pdo = array_merge($parameters_pdo, [":projectNo" => $projectNo]);
    }


    return ["queryStr" => $query, "pdoParams" => $parameters_pdo];
}

function getOrderIssues()
{
    $queryItem = decideFilterQuery();

    $returnValues = DbManager::fetchPDOQueryData('assembly_items', $queryItem["queryStr"], $queryItem["pdoParams"])["data"];
    $groupItems = array();
    foreach ($returnValues as $row) {
        $arrayItems = array_values(getOrderIssueFiles($row["OrderNo"], $row["Id"])) ?? [];
        $row["files"] = $arrayItems;
        $row["isBelong"] = (SharedManager::getUser()["GID"] == $row["Created_By_Gid"] ? true : false);
        $row["isOrderManager"] = in_array((int) SharedManager::getUser()["GroupID"], [5, 29]) ? true : false;
        $row["isRepliedByOM"] = $row["OmStatusId"] == "1" ? "checked" : "";
        $row["hasLineStopEffectCheck"] = $row["hasLineStopEffect"] == "1" ? "checked" : "";
        $row["ParentId"] = empty($row["ParentId"]) ? 0 : $row["ParentId"];
        if ($row["isOrderManager"]) {
            $row["hasLineStopEffectCheck"] .= " disabled ";
        }
        $groupItems[$row['ParentId']][] = $row;
    }
    echo json_encode($groupItems, JSON_THROW_ON_ERROR);
}

function getMailAddresses()
{
    try {
        $param = $_GET["param"] ?? "all";
        if (str_contains($param, "all")) {
            $query = "select distinct u.Mail from MailGroupElements s
         inner join Users u on u.UserID=s.UserID 
         where s.GroupID in (11,23,65,22) and u.IsActive=1";
            $returnValues = DbManager::fetchPDOQueryData('SI_EA_QR_Tracking', $query)["data"];
        } else {
            $query = "select distinct u.Mail from MailGroupElements s
         inner join Users u on u.UserID=s.UserID 
         where u.IsActive=1 and s.GroupID in (:groupIDs) and u.IsActive=1";
            $returnValues = DbManager::fetchPDOQueryData('SI_EA_QR_Tracking', $query, [":groupIDs" => $param])["data"];
        }

        echo json_encode($returnValues, JSON_THROW_ON_ERROR);
    } catch (Throwable $ex) {
        throw new Exception($ex->getMessage());
    }
    exit;
}

function getMailBody($orderNo, $id, $parentId, $qNumber)
{
    $currentUser = SharedManager::getUser()["Name"] . ' ' . SharedManager::getUser()["Surname"];
    $addr = "https://". $_SERVER["HTTP_HOST"] . '/assemblynotes/orderIssues/index.php?orderNo=' . $orderNo . '&id=' . $id . '&parentId=' . $parentId;
    $projectWebLink = '<a href="' . $addr . '">Click here to access order notes.<a/>';
    $bodyDraft = "<hr/><b>$currentUser</b> has asked a question about project: <b>$orderNo</b>.  <b>Question number: $qNumber </b><br><br>Access from: " . $projectWebLink;
    $bodyContent = getOneXMailContent($bodyDraft, 'Order Note Notification');

    return $bodyContent;
}

function sendOrderIssueMail($isNewRecord = false, $id = 0, $parentId = 0, $qNumber = 0)
{
    try {
        $entity = json_decode($_POST["entity"] ?? []);
        $body = getMailBody($entity->orderNo, $id, $parentId, $qNumber);

        $subject = $entity->orderNo . " Projesi Hk.";
        if(!$isNewRecord && !empty($entity->toMails) && in_array(SharedManager::getUser()["Email"], $entity->toMails))
            unset($entity->toMails[SharedManager::getUser()["Email"]]);
        $entity->ccMails[] = $entity->orderManagerMail;
        //MailManager::sendMail($subject, $body, 6, 'order_issues_notification', [], $entity->toMails, $entity->ccMails);
        MailManager::sendMail($subject, $body, 2, 'general', [], ["emre.karakuz@siemens.com"]);
    } catch (Throwable $ex) {
        throw new Exception($ex->getMessage());
    }
}

function getStatusWidgets()
{
    $whereClause = "tx.ParentId=0";
    $projectNo = $_GET["projectNo"] ?? "all";
    $pdoParams = [];

    if (str_contains($projectNo, "7024")) {
        $whereClause .= " AND tx.OrderNo= :p1 ";
        $pdoParams = [":p1" => $projectNo];
    }
    $query = "select
      tx.StatusId,
       count(*) as Quantity from torder_issues tx where " . $whereClause . " group by tx.StatusId";
    $response = DbManager::fetchPDOQueryData('assembly_items', $query, $pdoParams)["data"];
    if ($response === false) {
        http_response_code(500);
        echo json_encode(["message" => "Error on load.", "code" => "500"], JSON_THROW_ON_ERROR);
        exit;
    } else {
        http_response_code(200);
        echo json_encode(["data" => $response, "code" => "200"]);
        exit;
    }
}

function setOmStatus($id = 0, $isAnswered = 0)
{
    // OM Cevaplama hızının hesaplanması için kullanılan alandır.
    try {
        $updateQueryForTrue = "UPDATE torder_issues SET OmStatusId = 1, OmStatus_At= now()  WHERE (Id = :ID)";
        $updateQueryForFalse = "UPDATE torder_issues SET OmStatusId = 0, OmStatus_At= NULL  WHERE (Id = :ID)";
        if ($isAnswered == 1) {
            $response = DbManager::fetchPDOQuery('assembly_items', $updateQueryForTrue, [":ID" => $id]);
        } else {
            $response = DbManager::fetchPDOQuery('assembly_items', $updateQueryForFalse, [":ID" => $id]);
        }
        if ($response === false) {
            http_response_code(500);
            echo json_encode(["message" => "Error on status update", "code" => "500"], JSON_THROW_ON_ERROR);
        }
        echo json_encode(["message" => "Success", "code" => "200"], JSON_THROW_ON_ERROR);
    } catch (Throwable $ex) {
        throw new Exception($ex->getMessage());
    }
    exit;
}

function setLineStopEffect($id = 0, $hasLineStopEffect = 0)
{
    // OM Cevaplama hızının hesaplanması için kullanılan alandır.
    try {
        $updateQueryForTrue = "UPDATE torder_issues SET hasLineStopEffect = 1, hasLineStopEffect_At= now()  WHERE (Id = :ID)";
        $updateQueryForFalse = "UPDATE torder_issues SET hasLineStopEffect = 0, hasLineStopEffect_At= NULL  WHERE (Id = :ID)";
        if ($hasLineStopEffect == 1) {
            $response = DbManager::fetchPDOQuery('assembly_items', $updateQueryForTrue, [":ID" => $id]);
        } else {
            $response = DbManager::fetchPDOQuery('assembly_items', $updateQueryForFalse, [":ID" => $id]);
        }
        if ($response === false) {
            http_response_code(500);
            echo json_encode(["message" => "Line effect could not be updated", "code" => "500"], JSON_THROW_ON_ERROR);
        }
        echo json_encode(["message" => "Success", "code" => "200"], JSON_THROW_ON_ERROR);
    } catch (Throwable $ex) {
        throw new Exception($ex->getMessage());
    }
    exit;
}

#region LIST TABLE
function getRecursiveChildren($id, $items): array
{
    $kids = [];
    foreach ($items as $key => $item) {
        if ($item['ParentId'] == $id) {
            $item["QuestionId"] = $item['ParentId'];
            $kids[] = $item;
            array_push($kids, ...getRecursiveChildren($item["Id"], $items));
        }
    }
    return $kids;
}

function getOrderIssuesForTable($projectNoParam = "")
{
    $pdo_params = [];
    $whereClause = " and child.Created_At >= DATE_FORMAT(DATE_ADD(NOW(),INTERVAL -1 YEAR),'%Y-%m-01') ";
    try {
        if (!empty($projectNoParam)) {
            $pdo_params = [":p1" => $projectNoParam];
            $whereClause = " and OrderNo=:p1";
        }
        $query = "SELECT child.QNumber, child.ParentId,child.Id,tc.Description as IssueCodeDesc,
       child.PanelNumber,child.ReferenceCode,
       child.OrderNo,
       DATE_FORMAT(child.Created_At,'%d/%m/%Y %H:%i:') as Created_At,
       child.Created_By,child.OmStatusId,DATE_FORMAT(child.OmStatus_At,'%d/%m/%Y %H:%i') as OmStatus_At,
       TIMESTAMPDIFF(DAY , Created_At, OmStatus_At) AS difCreatedBetweenOmAnswer,
       child.Note, null as ParentChild,
          CASE
    WHEN child.StatusId = 1 THEN 'Open'
    WHEN child.StatusId = 2 THEN 'Closed'
    WHEN child.StatusId = 3 THEN 'Rework'
    ELSE 'Belirsiz'
END AS 'StatusDesc'
FROM torder_issues child
left join tissue_codes tc on tc.Id=child.CodeId 
where child.IsActive=1" . $whereClause . "
order by child.ParentId asc,child.OrderNo asc,child.Created_At desc";

        $returnValues = DbManager::fetchPDOQueryData('assembly_items', $query, $pdo_params)["data"];
        $recursive_dataSource = getRecursiveChildren(0, $returnValues);
        echo json_encode($recursive_dataSource, JSON_THROW_ON_ERROR);
    } catch (Throwable $ex) {
        http_response_code(500);
        throw new Exception($ex->getMessage());
    }

    exit;
}

#endregion