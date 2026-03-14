<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/api/MToolManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";

$type = $_POST["action"] ?? $_GET["action"];
switch ($type) {
    case "main":
        loadMainCategories();
    break;
    case "categories":
        loadCategories();
    break;
    case "mainSupport":
        loadSupportMainCategories();
    break;
    case "mainEmergency":
        loadEmergencyMainCategories();
    break;
    case "sub":
        loadSubCategories();
    break;
    case "subSupport":
        loadSubSupportCategories();
    break;
    case "subEmergency":
        loadSubEmergencyCategories();
    break;
    case "getOrderNote":
        $id = $_GET['id'] ?? 0;
        if (!is_numeric($id)) {
            http_response_code(400);
            exit();
        }
        $noteEntity = getOrderNote($id);
        echo json_encode($noteEntity, JSON_THROW_ON_ERROR);
    break;
    case "projectProductDetails":
        getProjectProductDetails();
    break;
    case "projectNotes":
        getProjectNotes();
    break;
    case "openList":
        listNotes(0);
    break;
    case "allList":
        listNotes(-1);
    break;
    case "closeList":
        listNotes(1);
    break;
    case "closeItem":
        updateReworkTime(1); // 1 closed
    break;
    case "addTime":
        addTimes(); // 1 open
    break;
    case "openItem":
        updateReworkTime(0); // 0 open
    break;
    case "add":
        addNote();
        http_response_code(200);
    break;
    case "delete":
        $note_id = $_GET['noteId'] ?? 0;
        if ($note_id < 1 || empty($note_id)) {
            http_response_code(500);
            exit();
        }
        $note_id_response = deleteNote($note_id);
        echo json_encode($note_id_response, JSON_PRETTY_PRINT);
        http_response_code(200);
    break;
    case "addSupport":
        addSupportNode();
    break;
    case "edit":
        updateNote();
    break;
    case "getMissingCategories":
        getMissingCategories();
    break;
    case "getNoteData":
        getNoteData();
    break;
    default:
    break;
}
exit;


function loadCategories(){
    $query = "SELECT * FROM tcategories";
    $data = DbManager::fetchPDOQueryData('assembly_items', $query)["data"];
    echo json_encode($data); exit;
}

function loadMainCategories()
{
    $panelType = "MV";
    $query = "SELECT id, mainCategory FROM tmain_categories WHERE mainCategoryType=:p1";
    $response = DbManager::fetchPDOQuery('assembly_items', $query, [":p1" => $panelType]);
    echo json_encode($response["data"], JSON_PRETTY_PRINT);
    exit();
}

function loadSupportMainCategories()
{
    $query = "SELECT id, mainCategory FROM tmain_categories WHERE id IN ('1','2','3','4','7','8','9','10','22')";
    $response = DbManager::fetchPDOQueryData('assembly_items', $query)["data"];
    if ($response !== false)
        echo json_encode($response, JSON_THROW_ON_ERROR);
    exit;
}

function loadEmergencyMainCategories()
{
    $query = "SELECT id, mainCategory FROM tmain_categories WHERE  mainCategoryType='AM'";
    $response = DbManager::fetchPDOQueryData('assembly_items', $query)["data"];
    if ($response !== false)
        echo json_encode($response, JSON_THROW_ON_ERROR);
    exit;
}


function loadSubCategories()
{
    $query = "SELECT id, subCategory FROM tsub_categories";
    $response = DbManager::fetchPDOQueryData('assembly_items', $query)["data"];
    if ($response !== false)
        echo json_encode($response, JSON_THROW_ON_ERROR);
    exit;
}

function loadSubSupportCategories()
{
    $mainCategoryId = $_GET['mainCategoryId'] ?? 0;
    $categoryName = $_GET['categoryName'] ?? "";
    $subCategories = "";
    if ($categoryName == "ŞALTER") {
        $subCategories = "'Arge','Ürün Yönetimi','Sipariş Yönetimi','GPC D','Kesici dizayn hatası','Ölçü dizayn hatası','Ayırıcı dizayn hatası','Kesici dizayn hatası','Ölçü dizayn hatası','Ayırıcı dizayn hatası','Çgt dizayn hatası','Kablo ağacı hatası','Büküm Hatası','Punch Hatası','Deformasyon','Çapaklı Parça','Boya hataları','Kesici eksiği','VT eksiği','Kaset eksiği','Kontak malzemeleri','Cihaz eksiği','Kablo ağacı eksiği','Bobin eksiği','Lazer kesim parça eksiği','Kontaktör eksiği','MTS Parça Eksiği','Bağlantı elemanları','Bakır Eksiği','Sac Eksiği','Sarı Galvaniz Eksiği','Kaplamalı Bakır Eksiği','Polikarbon Eksiği','Pertinaks Eksiği','Sipariş Yönetimi (TBC)','Ürün Yönetimi (DTO)','Arge','GPC-D','Ürün Yönetimi','Sipariş Yönetimi','Arge','Ön İmalat','Tedarikçi','Elektrik mühendisliği','Cihaz','Direnç','Trafo'";
    } else if ($categoryName == "ÖN MONTAJ") {
        $subCategories = "'Arge','Ürün Yönetimi','Sipariş Yönetimi','GPC D','Bakır dizayn hatası','Kapı dizayn hatası','Topraklama dizayn hatası','Bakır dizayn hatası','Kapı dizayn hatası','Topraklama dizayn hatası','Büküm Hatası','Punch Hatası','Deformasyon','Çapaklı Parça','Boya hataları','Kaynak hataları','Bakır eksiği','Topraklama eksiği','Kapı parçaları','MTS Parça Eksiği','İzolatör eksiği','Support ES eksiği','İzole makaron eksiği','Bağlantı elemanları','Sensörler','Bakır Eksiği','Sac Eksiği','Sarı Galvaniz Eksiği','Kaplamalı Bakır Eksiği','Polikarbon Eksiği','Pertinaks Eksiği','Serigrafi eksiği','Sipariş Yönetimi (TBC)','Ürün Yönetimi (DTO)','Arge','GPC D','Ürün Yönetimi','Sipariş Yönetimi','Arge','Ön İmalat','Tedarikçi','Sensörler','Anahtar – Kilit Mekanizması Eksiği'";
    } else if ($categoryName == "TRAFO") {
        $subCategories = "'Arge','Ürün Yönetimi','Sipariş Yönetimi','GPC D','Sixpack ','BT/NX C trafo taşıyıcı','Sixpack ','BT/NX C trafo taşıyıcı','Kablo ağacı hatası','Büküm Hatası','Punch Hatası','Deformasyon','Çapaklı Parça','Boya hataları','Kaynak hataları','Bushing Eksiği','Termostat/ Higrostat Eksiği','Parafudr Eksiği','Etiket / Grave Eksikleri','CT Eksiği','Bobin Eksiği','T90 Kablosu Eksiği','VT Eksiği','Ark Sensörü Eskiği','Termal Sensör Eksiği','Kuvvet Kablosu Eksiği','Siviç Eksiği','İzolatör Eksiği','MTS Parça Eksiği','Sarı Galvaniz Eksiği','ct kablo ağacı eksiği','vt kablo ağacı eksiği','Primary Bolt Eksiği','Fan Eksiği','Yük Ayırıcı Eksiği','nem sensörleri eksiği','Lazer kesim parça eksiği','Bağlantı elemanları','Bakır Eksiği','Sac Eksiği','Sarı Galvaniz Eksiği','Kaplamalı Bakır Eksiği','Pertinaks Eksiği','Sipariş Yönetimi (TBC)','Ürün Yönetimi (DTO)','Arge','GPC D','Ürün Yönetimi','Sipariş Yönetimi','Arge','Ön İmalat','Tedarikçi','Elektrik mühendisliği','Ark Sensörü','Termal Sensör','Trafo','Parafudur'";
    } else if ($categoryName == "AKSESUAR") {
        $subCategories = "'Arge','Ürün Yönetimi','Sipariş Yönetimi','GPC D','Servis arabası','PRC ','PRC çıkış kanalı','Arka kapak','IP damlalık sacları','Deep bottom','Servis arabası','PRC ','PRC çıkış kanalı','Arka kapak','IP damlalık sacları','Deep bottom','Büküm Hatası','Punch Hatası','Deformasyon','Çapaklı Parça','Boya hataları','Kaynaklı parça hatası','Yedek malzemeler','Bağlantı elemanları','Sac Eksiği','Pertinaks Eksiği','GFK TUBE','Sipariş Yönetimi (TBC)','Ürün Yönetimi (DTO)','Arge','GPC D','Ürün Yönetimi','Sipariş Yönetimi','Arge','Ön İmalat','Tedarikçi','Yedek cihazlar'";
    }
    $subCategoriesStr = str_replace("'", "", $subCategories);
    $subCategoryList = explode(',', $subCategoriesStr);
    $query = "SELECT subCategory,id  FROM tsub_categories WHERE subCategory IN (:implodeParam) and mainCategoryId=:p1 group by subCategory";
    $response = DbManager::fetchPDOQueryData('assembly_items', $query, [":implodeParam" => $subCategoryList, ":p1" => $mainCategoryId])["data"];
    if ($response !== false)
        echo json_encode($response, JSON_THROW_ON_ERROR);
    exit;
}

function loadSubEmergencyCategories()
{
    $mainCategoryId = $_GET['mainCategoryId'] ?? 0;
    $query = "SELECT id, subCategory FROM tsub_categories  WHERE mainCategoryId=:p1";
    $response = DbManager::fetchPDOQueryData('assembly_items', $query, [":p1" => $mainCategoryId])["data"];
    if ($response !== false)
        echo json_encode($response, JSON_THROW_ON_ERROR);
    exit;
}

function getOrderNote($id = 0)
{
    $query = "
        SELECT 
            tsub_categories.subCategory,
            tmain_categories.mainCategory,
            tnotes.*  
        FROM tnotes
        LEFT JOIN 
            tsub_categories ON tnotes.subcategoryid = tsub_categories.id
        LEFT JOIN 
            tmain_categories ON tsub_categories.mainCategoryId = tmain_categories.id
        WHERE tnotes.id=:p1 
        ORDER BY 
            tnotes.notestatus
    ";
    $response = DbManager::fetchPDOQuery('assembly_items', $query, [":p1" => $id]);
    return $response["data"][0];
}

function getNoteData()
{
    $id = $_GET["id"];
    if(!is_numeric($id))
        returnHttpResponse(400, "invalid id");

    $query = "
        SELECT 
            tnotes.id,
            tnotes.notestatus,
            tnotes.projectNo,
            tnotes.panelno,
            tcategories.id AS categoryid,
            tnotes.category,
            tnotes.subcategoryid,
            tsub_categories.subCategory,
            tnotes.missingcategoryid,
            tmissing_categories.missingCategory,
            tnotes.note
        FROM tnotes
        LEFT JOIN tcategories
        ON tcategories.category = tnotes.category 
        LEFT JOIN tsub_categories 
        ON tnotes.subcategoryid = tsub_categories.id
        LEFT JOIN tmissing_categories 
        ON tmissing_categories.id = tnotes.missingcategoryid
        WHERE tnotes.id = :p1
    ";

    $response = DbManager::fetchPDOQuery('assembly_items', $query, [
        ":p1" => $id
    ]);
    $results = array();
    $results = $response["data"][0];
    $results['note'] = html_entity_decode($response["data"][0]['note']);
    echo json_encode($results); exit;
}

function getProjectNotes()
{
    $projectNo = $_GET['orderNo'];

    $query = "
        SELECT 
            tsc.subCategory,
            tc.mainCategory,
            tn.id,
            tn.note,
            tn.projectNo,
            tn.createdby,
            tn.notestatus,
            DATE_FORMAT(tn.created, '%d-%m-%y %H:%i:%s') AS created,
            DATE_FORMAT(tn.updated, '%d-%m-%y %H:%i:%s') AS updated,
            tn.updatedby,
            tn.panelno,
            tn.subcategoryid,
            tn.ecrTime,
            tn.category,
            tn.missingcategoryid,
            tn.materialnolist
        FROM tnotes as tn
        LEFT JOIN 
            tsub_categories as tsc ON tn.subcategoryid = tsc.id
        LEFT JOIN 
            tmain_categories as tc ON tsc.mainCategoryId = tc.id
        WHERE 
            tn.projectNo = :param1
        ORDER BY 
            tn.notestatus
    ";
    $response = DbManager::fetchPDOQueryData('assembly_items', $query, [":param1" => $projectNo])["data"];
    $results = array();
    $results = $response;
    foreach ($results as $key => $values) {
        $results[$key]['note'] = html_entity_decode($values['note']);
    }
    echo json_encode($results, JSON_THROW_ON_ERROR);
    exit;
}


function listNotes($openCloseState)
{
    global $replaceSubProductType;
    $projectNo = $_GET['projectNo'];
    if($projectNo !== "all" && !is_numeric($projectNo))
        returnHttpResponse(400, "Incorrect project number");

    $pdo_params = [":noteStatus" => $openCloseState];
    $query = "SELECT tn.*, if(tn.createdby=:param_created_by,1,0) as hasAccessToDelete FROM vw_assembly_notes as tn WHERE tn.notestatus= :noteStatus";
    $pdo_params = array_merge($pdo_params, [":param_created_by" => SharedManager::getUser()["Email"]]);
    if ($openCloseState === -1) {
        $pdo_params = [];
        $query = "SELECT * FROM vw_assembly_notes as tn WHERE tn.notestatus in (0,1)";
    }

    $start_date = $_GET['start_date'] ?? null;
    $finish_date = $_GET['finish_date'] ?? null;
    $firstOfMonth = date('Y-m-01');

    $date_query = "";
    if ($start_date != null && $finish_date != null) {
        $start_date = date("Y-m-d", strtotime($start_date));
        $finish_date = date("Y-m-d", strtotime($finish_date));
        $date_query = " and DATE_FORMAT(tn.created_raw, '%Y-%m-%d') >= :start_date and DATE_FORMAT(tn.created_raw, '%Y-%m-%d') <= :finish_date";
        $pdo_params = array_merge($pdo_params, [":start_date" => $start_date, ":finish_date" => $finish_date]);
    } else {
        $date_query = " and DATE_FORMAT(tn.created_raw, '%Y-%m-%d') >= :first_of_month";
        $pdo_params = array_merge($pdo_params, [":first_of_month" => $firstOfMonth]);
    }

    $query .= $date_query;

    $projectNosForQuery = "";
    $data["data"] = [];
    $response["data"] = DbManager::fetchPDOQueryData('assembly_items', $query, $pdo_params)["data"];
    $openNoteStatusCount = 0;
    $closeNoteStatusCount = 0;
    foreach ($response["data"] as $key => $value) {
        $data["data"][$value["projectNo"]][$value["panelno"]][] = $value;
        $projectNosForQuery .= $value["projectNo"] . ",";
        if ($value["notestatus"] == 1) {
            $closeNoteStatusCount = $closeNoteStatusCount + 1;
        } else {
            $openNoteStatusCount = $openNoteStatusCount + 1;
        }
    }
    if (count($response["data"]) === 0) {
        echo json_encode($response, JSON_THROW_ON_ERROR);
        exit;
    }
    $projectNosForQuerySubstr = substr($projectNosForQuery, 0, -1);
    $projectNosForQuery = array_unique(explode(',', $projectNosForQuerySubstr));

    $responseForProjectDetails = MToolManager::searchMultipleProjects($projectNosForQuery);
    foreach ($responseForProjectDetails as $keys => $values) {
        foreach ($data["data"][$values["FactoryNumber"]] as $key => $value) {
            foreach ($data["data"][$values["FactoryNumber"]][$key] as $k => $v) {
                $data["data"][$values["FactoryNumber"]][$key][$k]["projectName"] = $values["ProjectName"];
                $data["data"][$values["FactoryNumber"]][$key][$k]["product"] = $values["Product"];
                $data["data"][$values["FactoryNumber"]][$key][$k]["productType"] = $replaceSubProductType[$values["Product"]] ?? "-";
            }
        }
    }

    $response["data"] = [];
    foreach ($data["data"] as $keys => $values) {
        foreach ($values as $key => $value) {
            foreach ($value as $k => $v) {
                $v['note'] = html_entity_decode($v['note']);
                $response["data"][] = $v;
            }
        }
    }
    $response["closeNoteStatusCount"] = $closeNoteStatusCount;
    $response["openNoteStatusCount"] = $openNoteStatusCount;
    echo json_encode($response, JSON_THROW_ON_ERROR);
    exit;
}

function getPreviousTimes($reworkItemId){
    $query = "
        SELECT 
            IFNULL(tn.ecrTime,0) as ecrTime,
            IFNULL(tn.idleTime,0) as idleTime 
        FROM tnotes as tn 
        WHERE tn.id=:id LIMIT 1
    ";

    return DbManager::fetchPDOQueryData('assembly_items', $query, [
        ":id" => $reworkItemId
    ])["data"][0];
}

function addTimes(){
    $reworkTime = (int) $_GET['ecrTime'] ?? 0;
    $idleTime = (int) $_GET['idleTime'] ?? 0;
    $reworkItemId = $_GET['reworkItemId'];

    if(!is_numeric($reworkTime))
        returnHttpResponse(400, "reworkTime is not numeric");
    if(!is_numeric($idleTime))
        returnHttpResponse(400, "idleTime is not numeric");
    if(!is_numeric($reworkItemId))
        returnHttpResponse(400, "reworkItemId is not numeric");

    $previousTimesData = getPreviousTimes($reworkItemId);
    $reworkTimeInitial = $previousTimesData["ecrTime"];
    $idleTimeInitial = $previousTimesData["idleTime"];

    $newReworkTime = $reworkTimeInitial + $reworkTime;
    $newIdleTime = $idleTimeInitial + $idleTime;
    $query = "
        UPDATE tnotes as tn 
        SET 
            tn.notestatus = :p1 ,
            tn.ecrTime=:p2,
            tn.idleTime=:p3,
            updatedby= :p4 
        WHERE tn.id= :p5
    ";

    $result = DbManager::fetchPDOQuery('assembly_items', $query, [
        ":p1" => 0, //notestatus = 0 is open
        ":p2" => $newReworkTime,
        ":p3" => $newIdleTime,
        ":p4" => SharedManager::getUser()["Email"],
        ":p5" => $reworkItemId]);
    if ($result) {
        echo json_encode("success");
    } else {
        echo json_encode("false");
    }
}
function updateReworkTime($openCloseState)
{

}

function getProjectProductDetails()
{
    $projectNo = $_GET['projectNo'];
    if(!is_numeric($projectNo))
        returnHttpResponse(400, "Incorrect project number");

    $query = "
        SELECT assembly_plan_mv.projectNo,
            assembly_plan_mv.productionLine,
            assembly_plan_mv.productionWeek,
            assembly_plan_mv.quantityonday,
            assembly_plan_mv.productionday,
            assembly_plan_mv.poz,
            COALESCE(assembly_plan_mv.scope, 'Undefined') AS scope
        FROM assembly_plan_mv
        WHERE 
            assembly_plan_mv.projectNo = :p1 AND 
            assembly_plan_mv.revisionNr = (
              SELECT MAX(revisionNr) FROM assembly_plan_mv_index
            )
    ";
    $response = DbManager::fetchPDOQueryData('planning', $query, [":p1" => $projectNo])["data"];
    echo json_encode($response, JSON_THROW_ON_ERROR);
}

function setDeleteNoteResponseMessage($noteMessage = ""): void
{
    http_response_code(500);
    echo json_encode($noteMessage, JSON_PRETTY_PRINT);
    exit();
}

function deleteNote($note_id = 0)
{
    $noteEntity = getOrderNote($note_id);
    if (!isset($noteEntity)) {
        setDeleteNoteResponseMessage('Not verisi bulunamadı. İşlem iptal edildi.');
    }

    if (strcasecmp(SharedManager::getUser()["Email"], $noteEntity["createdby"]) != 0) {
        setDeleteNoteResponseMessage('Not authorized');
    }

    $query_delete = "delete from tnotes where id= :p1";
    $response_delete_query = DbManager::fetchPDOQuery('assembly_items', $query_delete, [":p1" => $note_id]);
    http_response_code(200);
    return $note_id;
}

function addNote()
{
    $subcategoryid = $_POST['subcategoryid'];
    $projectNo = $_POST['projectNo'];
    $PanelNos = $_POST['PanelNos'];
    $MaterialNos = $_POST['materialNos'] ?? "";
    $category = $_POST['category'];
    $missingCategoryId = $_POST['missingCategoryId'];
    $mainCategoryValue = $_POST['mainCategoryValue'] ?? false;
    $reworkTimeForEmergencyTime = $_POST['reworkTimeForEmergencyTime'] ?? 0;
    $notestatus = (!empty($mainCategoryValue) && $mainCategoryValue === "emergency_lines") ? 1 : 0;
    $createdby = SharedManager::getUser()["Email"];
    $note = htmlspecialchars($_POST['note']);

    $queryAdd = "
        INSERT INTO tnotes(
            subcategoryid,
            note,
            projectNo,
            notestatus,
            createdby,
            panelno,
            category,  
            missingcategoryid,
            materialnolist
        ) 
        VALUES ";
    $queryAddParameters = [];
    foreach ($PanelNos as $index => $panelNo) {
        $queryAdd .= "( :p1$index,:p2$index,:p3$index,:p4$index,:p5$index,:p6$index,:p7$index,:p8$index,:p9$index),";

        $queryAddParameters[":p1$index"] = $subcategoryid;
        $queryAddParameters[":p2$index"] = $note;
        $queryAddParameters[":p3$index"] = $projectNo;
        $queryAddParameters[":p4$index"] = $notestatus;
        $queryAddParameters[":p5$index"] = $createdby;
        $queryAddParameters[":p6$index"] = $panelNo;
        $queryAddParameters[":p7$index"] = $category;
        $queryAddParameters[":p8$index"] = $missingCategoryId;
        $queryAddParameters[":p9$index"] = $MaterialNos;
    }
    $queryAdd = rtrim($queryAdd, ",");
    $result = DbManager::fetchPDOQuery('assembly_items', $queryAdd, $queryAddParameters);

    echo json_encode("işlem başarılı", JSON_PRETTY_PRINT);
}

function updateNote()
{
    $notestatus = $_POST['noteStatus'];
    $note = htmlspecialchars($_POST['note']);
    $id = $_POST['id'];
    $category = $_POST["category"];
    $subCategory = $_POST["subCategory"];
    $missingCategory = $_POST["missingCategory"];

    if(!is_numeric($id))
        returnHttpResponse(400, "id is not numeric");
    if(!is_numeric($category))
        returnHttpResponse(400, "category is not numeric");
    if(!is_numeric($subCategory))
        returnHttpResponse(400, "category is not numeric");
    if(!is_numeric($missingCategory))
        returnHttpResponse(400, "category is not numeric");
    if(!is_numeric($notestatus))
        returnHttpResponse(400, "notestatus is not numeric");

    $query = "
        UPDATE tnotes 
        SET 
            note= :p1,
            subcategoryid= :subcategoryid,
            missingcategoryid= :missingcategoryid,
            category= (SELECT tcategories.category FROM tcategories WHERE id=:categoryid),
            updatedby=:p2, 
            notestatus = :p3 
        WHERE id=:p4
    ";
    DbManager::fetchPDOQuery('assembly_items', $query, [
        ":p1" => $note,
        ":subcategoryid" => $subCategory,
        ":missingcategoryid" => $missingCategory,
        ":categoryid" => $category,
        ":p2" => SharedManager::getUser()["Email"],
        ":p3" => $notestatus,
        ":p4" => $id
    ]);
    echo json_encode(["message" => "Successfully", "code" => 200], JSON_PRETTY_PRINT);
    exit;
}

function getMissingCategories(){
    $getMissingCategoriesQuery = "SELECT * FROM tmissing_categories";
    $getMissingCategoriesQueryData = DbManager::fetchPDOQueryData('assembly_items', $getMissingCategoriesQuery)["data"];
    echo json_encode($getMissingCategoriesQueryData); exit;
}
