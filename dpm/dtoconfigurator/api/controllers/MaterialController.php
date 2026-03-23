<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/shared.php';
include_once '../../api/controllers/BaseController.php';
include_once '../../api/models/Journals.php';
header('Content-Type: application/json; charset=utf-8');


class MaterialController extends BaseController {

    public function getMaterialsBySearch(): void {
        $keyword = $_GET['keyword'];

        $query = "SELECT m.id, m.material_number, m.sap_material_number, m.description, m.work_center_id, m.material_kmats, m.is_device, m.is_cable_code, wc.work_center, wc.work_content, wc.has_sub_kmat
              FROM materials m 
              LEFT JOIN work_centers wc ON m.work_center_id = wc.id
              WHERE ";

        if (is_numeric($keyword)) {
            // Sadece malzeme numarasında ara - BAŞTAN başlayan eşleşmeler
            $query .= "m.material_number LIKE :keyword LIMIT 25";
            $params = [':keyword' => "$keyword%"];
        } else {
            // Metinsel aramada: malzeme numarasında baştan, description'da arada ara
            $query .= "m.material_number LIKE :keywordStart OR m.description LIKE :keywordMiddle LIMIT 25";
            $params = [
                ':keywordStart' => "$keyword%",
                ':keywordMiddle' => "%$keyword%"
            ];
        }

        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, $params)['data'] ?? [];

        foreach($data as &$row) {
            $subKmatName = null;
            if ($row['has_sub_kmat'] === '1') {
                $materialKmats = explode('|', $row['material_kmats']);

                $query = "SELECT sub_kmat_name FROM material_kmat_subkmats 
                      WHERE material_number = :materialNo AND sub_kmat IN (:kmats) LIMIT 1";
                $subKmatName = DbManager::fetchPDOQueryData('dto_configurator', $query, [':materialNo'=>$row['material_number'], ':kmats' => $materialKmats])['data'][0]['sub_kmat_name'] ?? null;
            }
            $row['sub_work_center_name'] = $subKmatName ?? null;
        }

        echo(json_encode($data));
        exit();
    }

    public function getMaterialsFrom064(): void {
        $keyword = $_GET['keyword'];

        $query = "SELECT Material, Description
                  FROM sap_spiridon_064 
                  WHERE Material LIKE :material";
        $data = DbManager::fetchPDOQueryData('rpa', $query, [':material' => '%'.$keyword.'%'])['data'] ?? [];

        echo(json_encode($data));
        exit();
    }

    public function getMaterialsCosts() {
        $materials = $_GET['materials'];

        $query = "SELECT material, total_cost_tl, total_cost_euro, euro_to_tl, DATE_FORMAT(date, '%d.%m.%Y') AS date  
                 FROM material_costs 
                 WHERE material IN (:materials)";
        $data = DbManager::fetchPDOQueryData('rpa', $query, [':materials' => $materials])['data'] ?? [];

        echo json_encode($data);
        exit();
    }

    public function getMaterialByNumber(): void {
        $materialNo = $_GET['materialNo'];

        $query = "SELECT id, material_number, description, is_device, is_cable_code, work_center_id
                  FROM materials WHERE material_number = :materialNo AND material_number IS NOT NULL";

        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, [':materialNo'=> $materialNo])['data'][0] ?? [];

        echo(json_encode($data));exit();
    }

    public function getMaterialWithWorkCenters(): void {
        $materialNo = $_GET['materialNo'];

        $query = "SELECT *
                  FROM materials m 
                  LEFT JOIN work_centers wc ON m.work_center_id = wc.id
                  WHERE m.material_number = :materialNo";

        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, [':materialNo'=> $materialNo])['data'][0] ?? [];
        echo(json_encode($data));
        exit();
    }

    public function searchMaterialDtoData(): void
    {
        SharedManager::saveLog('log_dtoconfigurator', "PROCESSING | Material Search Request | " . implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Material Search Request | " . implode(' | ', $_POST), PAGE_GENERAL, GENERAL_MATERIAL_DEFINE_MODAL, ACTION_PROCESSING, implode(' | ', $_POST), "Material Search");

        $materialId = $_GET['materialId'];
        $query = "SELECT tkform_id AS id, document_number, dto_number, tkform_description AS description, acc, operation,
                         material_added_number AS added_material, material_added_description AS added_material_description,
                         material_deleted_number AS deleted_material, material_deleted_description AS deleted_material_description
                  FROM tkform_materials_view
                  WHERE material_added_id = :materialId OR material_deleted_id = :materialId";

        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, [':materialId' => $materialId])['data'] ?? [];

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | Material Search Request Successful with Material ID : " . $materialId);
        Journals::saveJournal("RETURNED | Material Search Request Successful with Material ID : " . $materialId, PAGE_MATERIAL_SEARCH, MATERIAL_SEARCH, ACTION_PROCESSING, implode(' | ', $_GET), "Material Search");
        echo(json_encode($data));
    }

    public function defineMaterial(): void {
        ini_set('memory_limit', '512M');
        SharedManager::saveLog('log_dtoconfigurator', "PROCESSING | Material Define Request | " . implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Material Define Request | " . implode(' | ', $_POST), PAGE_GENERAL, GENERAL_MATERIAL_DEFINE_MODAL, ACTION_PROCESSING, implode(' | ', $_POST), "Material Define");

        if (!SharedManager::hasAccessRight(35, 49)) {
            SharedManager::saveLog('log_dtoconfigurator',"ERROR | Unauthorized User for Define a Material | ". implode(' | ', $_POST));
            Journals::saveJournal("ERROR | Unauthorized User for Define a Material | ".implode(' | ', $_POST),PAGE_TKFORM, TKFORM_CREATE_FORM, ACTION_ERROR, implode(' | ', $_POST), "Material Define");
            returnHttpResponse(400, "Unauthorized user to Define a Material.");
        }

        $materialNo = $this->trimMaterialPrefix(trim($_POST['materialNo']));
        $description = trim($_POST['description']);
        $workCenterId = $_POST['workCenterSelect'];
        $subWorkCenterName = $_POST['subWorkCenterSelect'];
        $isDevice = $_POST['isDevice'] === 'true' ? 1 : 0;
        $isCableCode = $_POST['isManualEntry'] === 'true';
        $isUpdateMaterial = $_POST['isUpdateMaterial'] === 'true';
        $accessoryTypicalNumber = $_POST['accessoryTypicalNumber'] ?? '';
        $accessoryParentKmat = $_POST['accessoryParentKmat'] ?? '';

        if (strlen($materialNo) < 7)
            returnHttpResponse(400, 'Material length must be more than 7 characters. Example: 02965903, 18711133, 500000090');

        // Update Material Operation
        if ($isUpdateMaterial) {
            $isSapDefined = $this->isMaterialSapDefined($materialNo);

            if ($isSapDefined)
                $sapMaterialNumber = $this->getSapMaterialNumber($materialNo);
            else
                $sapMaterialNumber = NULL;
        }
        // Add New Material Operation
        else {
            if ($isCableCode) {
                $sapMaterialNumber = NULL;
                $materialNo = $this->trimMaterialPrefix(trim($_POST['materialNo']));
                $isSapDefined = 0;
            } else {
                $sapMaterialNumber = $_POST['materialNo'];
                $materialNo = $this->trimMaterialPrefix(trim($_POST['materialNo']));
                $isSapDefined = 1;
            }

            // that means it is a CTH or VTH cable
            if (strlen($materialNo) > 11)
                $isSapDefined = 1;
        }

        // 1. Materials tablosundaki var olan malzemeyi güncelle veya ekle.
        $query = "INSERT INTO materials (material_number, sap_material_number, description, work_center_id, material_kmats, is_device, is_cable_code, sap_defined, created_by, updated_by)
                  VALUES (:material_number, :sap_material_number, :description, :work_center_id, :material_kmats, :is_device, :is_cable_code, :sap_defined, :created_by, :updated_by)
                  ON DUPLICATE KEY UPDATE
                      sap_material_number = VALUES(sap_material_number),
                      description = VALUES(description),
                      work_center_id = VALUES(work_center_id),
                      material_kmats = VALUES(material_kmats),
                      is_device = VALUES(is_device),
                      is_cable_code = VALUES(is_cable_code),
                      sap_defined = VALUES(sap_defined),
                      updated_by = VALUES(updated_by)";

        $parameters = [
            ':material_number' => $materialNo,
            ':sap_material_number' => $sapMaterialNumber,
            ':description' => $description,
            ':work_center_id' => $workCenterId,
            ':material_kmats' => $this->getMaterialPossibleKmats($workCenterId, $subWorkCenterName),
            ':is_device' => $isDevice,
            ':is_cable_code' => $isCableCode ? 1 : 0,
            ':sap_defined' => $isSapDefined,
            ':created_by' => SharedManager::$fullname,
            ':updated_by' => SharedManager::$fullname
        ];

        DbManager::fetchPDOQuery('dto_configurator', $query, $parameters);

        if ($isUpdateMaterial) {
            // 2. Malzemeyi güncelledikten sonra, bu malzemenin hangi TK'larda geçtiğini sorgula ve id'yi getir.
            $query = "SELECT id FROM tkform_materials_view WHERE material_deleted_number = :md_number";
            $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':md_number' => $materialNo])['data'] ?? [];
            $deletedIds = array_column($result, 'id');

            $query = "SELECT id FROM tkform_materials_view WHERE material_added_number = :ma_number AND material_deleted_number IS NULL";
            $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':ma_number' => $materialNo])['data'] ?? [];
            $addedIds = array_column($result, 'id');

            $tkformMaterialsIds = array_unique(array_merge($deletedIds, $addedIds));

            //Boş ise hiçbir TK'da geçmiyor yani update işlemi yok. İşlem, TK'da bulunmamıs bir malzeme güncelleme işlemi.
            if(!empty($tkformMaterialsIds)) {
                // 3. Geçtiği TK Form materyallerinin idlerini gönderip, bu malzemeye daha önceden çalışılan projelerin id'lerini arrayde toplarız,
                //    çünkü project_works ve bom changede de istasyonun değişmesi gerekiyor.
                $query = "SELECT id, project_number, nachbau_number, dto_number, material_added_number, type, material_deleted_number, product_id, operation, tk_kmats, nachbau_kmats, effective
                          FROM project_work_view
                          WHERE tkform_materials_id IN (:ids) AND release_status = 'initial'";
                $projectWorksResult = DbManager::fetchPDOQuery('dto_configurator', $query, [':ids' =>  $tkformMaterialsIds])['data'] ?? [];

                // 4. Malzemenin güncellendiği TK daha önce bir çalışmada bulunduysa if'e girsin.
                if (!empty($projectWorksResult))
                    $this->updateMaterialStatusOfProjectWork($projectWorksResult, $workCenterId, $accessoryTypicalNumber, $accessoryParentKmat);
            }
        }

        SharedManager::saveLog('log_dtoconfigurator', "CREATED | Material Defined Request Successful | " . implode(' | ', $_POST));
        Journals::saveJournal("CREATED | Material Defined Request Successful | " . implode(' | ', $_POST), PAGE_GENERAL, GENERAL_MATERIAL_DEFINE_MODAL, ACTION_CREATED, implode(' | ', $_POST), "Material Define");
    }

    public function isMaterialSapDefined($materialNoTrimmed): bool
    {
        if (strlen($materialNoTrimmed) > 11) {
            return true; // that means it is a CTH or VTH cable
        } else {
            $query = "SELECT id FROM sap_spiridon_064 WHERE Material LIKE :materialNoTrimmed";
            $result = DbManager::fetchPDOQuery('rpa', $query, [':materialNoTrimmed' => '%' . $materialNoTrimmed])['data'];

            if (empty($result)) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function trimMaterialPrefix($materialNo){
        $query = "SELECT rules FROM rules WHERE rules.key = 'material_starts_by'";
        $starters = DbManager::fetchPDOQuery('dto_configurator', $query)['data'][0]['rules'];
        $starters = explode('|', $starters);

        foreach ($starters as $starter) {
            if (str_starts_with($materialNo, $starter)) {
                $materialNo = substr($materialNo, strlen($starter));
                break;
            }
        }

        return $materialNo;
    }

    public function getSapMaterialNumber($materialNoTrimmed): bool
    {
        if (strlen($materialNoTrimmed) > 13) {
            //SPECIAL kodlarda MLFB bilgisi boş geliyor Description aramak lazım full LIKE ile.
            if (str_ends_with($materialNoTrimmed, "SPE")) {
                $query = "SELECT Material FROM sap_spiridon_064 WHERE Description LIKE :mlfb";
                return DbManager::fetchPDOQueryData('rpa', $query, [':mlfb' => '%'.$materialNoTrimmed. '%'])['data'][0]['Material'];
            }else{
                $query = "SELECT Material FROM sap_spiridon_064 WHERE Mlfb LIKE :mlfb";
                return DbManager::fetchPDOQueryData('rpa', $query, [':mlfb' => '%'.$materialNoTrimmed])['data'][0]['Material'];
            }
        } else {
            $query = "SELECT Material FROM sap_spiridon_064 WHERE Material LIKE :materialNoTrimmed";
            return DbManager::fetchPDOQueryData('rpa', $query, [':materialNoTrimmed' => '%' . $materialNoTrimmed])['data'][0]['Material'];
        }
    }

    public function sendNotDefinedMaterialsEMail() {
        try {
            $projectNo = $_POST['projectNo'];
            $nachbauNo = $_POST['nachbauNo'];
            $notDefinedMaterials = $_POST['notDefinedMaterials'] ?? [];

            if (empty($notDefinedMaterials)) {
                return;
            }

            $uploadedBy = SharedManager::$fullname;
            $uploadedByEmail = SharedManager::$email;

            $subject = "⚠️ ÖNEMLİ: " . $projectNo . " Sipariş DTO Fark Listesi Yayını Öncesi Tanımsız (ZENF) Listeler Tespiti";

            $materialsListHtml = "
            <table cellpadding='0' cellspacing='0' border='0' width='100%' style='margin-bottom: 20px;'>
                <tr>
                    <td style='background-color: #fef2f2; border: 1px solid #ef4444; border-radius: 8px;'>
                        <table cellpadding='20' cellspacing='0' border='0' width='100%'>
                            <tr>
                                <td>
                                    <h3 style='color: #dc2626; margin: 0 0 15px 0; font-size: 16px; font-family: Arial, sans-serif;'>
                                        ⚠️ Tanımsız Malzemeler (ZENF)
                                    </h3>
                                    <ul style='margin: 0; color: #dc2626; padding-left: 20px; line-height: 1.8; font-family: Arial, sans-serif;'>";

                                        foreach ($notDefinedMaterials as $material) {
                                            $materialsListHtml .= "<li style='margin-bottom: 8px; font-weight: bold; font-size: 14px;'>" . htmlspecialchars($material) . "</li>";
                                        }

            $materialsListHtml .= "
                                    </ul>
                                    <p style='margin: 15px 0 0 0; color: #dc2626; font-size: 13px; font-style: italic; font-family: Arial, sans-serif;'>
                                        ZENF olarak bulunan toplam: " . count($notDefinedMaterials) . " malzeme tespit edildi.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>";

            $bodyContent = "
            <div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6;'>
                <!-- Header Section -->
                <table cellpadding='0' cellspacing='0' border='0' width='100%' style='margin-bottom: 20px;'>
                    <tr>
                        <td style='background-color: #fef2f2; border-radius: 8px;'>
                            <table cellpadding='12' cellspacing='0' border='0' width='100%'>
                                <tr>
                                    <td>
                                        <h2 style='color: #dc2626; margin: 0 0 10px 0; font-size: 24px; font-family: Arial, sans-serif;'>
                                            ⚠️ Tanımlanmamış Malzeme Uyarısı
                                        </h2>
                                        <p style='font-size: 16px; color: #991b1b; margin: 0; font-family: Arial, sans-serif;'>
                                            <strong>{$projectNo}</strong> projesi, SAP'de bulunan <strong>ZENF tipindeki malzemeler</strong> nedeniyle yayınlanamıyor.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Project Details Table -->
                <div style='background-color: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; margin-bottom: 20px;'>
                    <table cellpadding='15' cellspacing='0' border='0' width='100%' style='background-color: #dc2626;'>
                        <tr>
                            <td>
                                <h3 style='margin: 0; font-size: 18px; color: white; font-family: Arial, sans-serif;'>📋 Uyarı Detayları</h3>
                            </td>
                        </tr>
                    </table>

                    <table style='border-collapse: collapse; border-spacing: 0; width: 100%; font-family: Arial, sans-serif;'>
                        <tbody>
                            <tr>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold; width: 35%;'>
                                    Proje Numarası
                                </td>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                    " . htmlspecialchars($projectNo) . "
                                </td>
                            </tr>
                            <tr>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold; width: 35%;'>
                                    Nachbau Numarası
                                </td>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                    " . htmlspecialchars($nachbauNo) . "
                                </td>
                            </tr>
                            <tr>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                    Raporlayan
                                </td>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                    " . htmlspecialchars($uploadedBy) . "
                                </td>
                            </tr>
                            <tr>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px; background-color: #f8f9fa; font-weight: bold;'>
                                    Tarih & Saati
                                </td>
                                <td style='border-bottom: 1px solid #e5e7eb; padding: 12px 16px;'>
                                    " . date('d.m.Y H:i:s') . "
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                " . $materialsListHtml . "

                <!-- Footer Section -->
                <table cellpadding='0' cellspacing='0' border='0' width='100%'>
                    <tr>
                        <td style='background-color: #f3f4f6; border-radius: 6px;'>
                            <table cellpadding='15' cellspacing='0' border='0' width='100%'>
                                <tr>
                                    <td style='text-align: center;'>
                                        <p style='margin: 0; font-size: 14px; color: #6b7280; font-family: Arial, sans-serif;'>
                                            Bu <b>DTO Configurator Sistemi</b>'nden gelen otomatik bir uyarıdır.<br>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        ";

            MailManager::sendMail($subject, $bodyContent, 95, "dto_not_defined_materials_alert", [], [], [$uploadedByEmail]);

        } catch (Exception $e) {
            error_log("Failed to send not defined materials alert email: " . $e->getMessage());
        }
    }

}

$controller = new MaterialController($_POST);

$response = match ($_GET['action']) {
    'getMaterialsBySearch' => $controller->getMaterialsBySearch(),
    'getMaterialByNumber' => $controller->getMaterialByNumber(),
    'getMaterialsFrom064' => $controller->getMaterialsFrom064(),
    'getMaterialWithWorkCenters' => $controller->getMaterialWithWorkCenters(),
    'getMaterialsCosts' => $controller->getMaterialsCosts(),
    'searchMaterialDtoData' => $controller->searchMaterialDtoData(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};


$response = match ($_POST['action']) {
    'defineMaterial' => $controller->defineMaterial(),
    'sendNotDefinedMaterialsEMail' => $controller->sendNotDefinedMaterialsEMail(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};