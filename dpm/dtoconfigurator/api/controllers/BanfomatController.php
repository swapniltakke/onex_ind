<?php
include_once '../../api/controllers/BaseController.php';
include_once '../../api/models/Journals.php';
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER["DOCUMENT_ROOT"] .  '/shared/PHPExcel/Classes/PHPExcel.php';

class BanfomatController extends BaseController {

    public function getProjectCopperDetails() {
        $projectNo = $_GET['projectNo'];
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Get Project Copper and DTO Details Request | " . $projectNo);
        Journals::saveJournal("PROCESSING | Get Project Copper and DTO Details Request | " . $projectNo, PAGE_BANFOMAT, BANFOMAT_INDEX, ACTION_PROCESSING, implode(' | ', $_GET), "Banfomat Index");

        $query = "SELECT FileName FROM log_nachbau WHERE FactoryNumber = :pNo ORDER BY ID DESC LIMIT 1";
        $lastNachbauResult = DbManager::fetchPDOQueryData('logs', $query, [':pNo' => $projectNo])['data'][0] ?? [];
        $lastNachbauTxt = $lastNachbauResult['FileName'];

        if(empty($lastNachbauTxt))
            returnHttpResponse(400, 'Siparişe ait aktarım dosyası bulunamadı.');

        $isProjectReleased = $this->checkIfProjectHasReleased($projectNo, $lastNachbauTxt);
        $coatedDtosOfProject = $this->getCoatedDtosAndDetails($projectNo, $lastNachbauTxt);
        $copperMaterials = $this->getCopperMaterialsOfProjectByLots($projectNo);

        $data = [
            $projectNo => [
                $lastNachbauTxt => [
                    "isProjectReleased" => $isProjectReleased,
                    "coatedDtosOfProject" => $coatedDtosOfProject,
                    "copperMaterialsOfProject" => $copperMaterials['coppersData'],
                    "coppersDataSource" => $copperMaterials['coppersDataSource']
                ]
            ]
        ];

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | Project Copper and DTO Details Request Successful | " . $projectNo);
        Journals::saveJournal("RETURNED | Project Copper and DTO Details Request Successful | " . $projectNo, PAGE_BANFOMAT, BANFOMAT_INDEX, ACTION_VIEWED, implode(' | ', $_GET), "Banfomat Index");
        echo json_encode($data);
        exit();
    }


    public function getCoatedDtosAndDetails($projectNo, $lastNachbauTxt): array
    {
        // ✅ CORRECT - fetch rules first from dto_configurator, then query planning
        $rulesQuery = "SELECT d.rules FROM rules d WHERE d.key='nachbau_dto_names'";
        $rulesResult = DbManager::fetchPDOQueryData('dto_configurator', $rulesQuery)['data'][0] ?? [];
        $nachbauDtoPattern = $rulesResult['rules'] ?? '';

        $query = "SELECT DISTINCT kmat_name as DtoNumber, description as Description
               FROM nachbau_datas 
               WHERE project_no=:pNo 
                 AND nachbau_no=:nNo 
                 AND kmat_name LIKE '%::%'
                 AND kmat_name REGEXP :pattern";
        $allDtosInNachbau = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $lastNachbauTxt, ':pattern' => $nachbauDtoPattern])['data'] ?? [];

        if (empty($allDtosInNachbau))
            returnHttpResponse(400, 'No DTO found in this nachbau : ' . $lastNachbauTxt);

        $query = "SELECT Lot FROM sap_opt WHERE OrderNo = :pNo GROUP BY Lot ORDER BY Lot";
        $projectLotsArray = DbManager::fetchPDOQueryData('rpa', $query, [':pNo' => $projectNo])['data'] ?? [];
        $projectLots = array_column($projectLotsArray, 'Lot');

        $coatedDtos = [];
        foreach ($allDtosInNachbau as $dtoData) {
            $isTin = $isSilver = $isNickel = false;

            if (isset($dtoData['Description'])) {
                $descriptionLower = strtolower($this->formatDescription($dtoData['Description'],4));
                $isTin = str_contains($descriptionLower, 'tin plat');
                $isSilver = str_contains($descriptionLower, 'silver plat');
                $isNickel = str_contains($descriptionLower, 'nickel plat') || str_contains($descriptionLower, 'ni plat');
            }

            if (!$isTin && !$isSilver && !$isNickel)
                continue;

            if (str_starts_with($dtoData['DtoNumber'], ':: KUKO'))
                $dtoData['DtoNumber'] = $this->formatKukoDtoNumber($dtoData['DtoNumber']);
            elseif (str_starts_with($dtoData['DtoNumber'], ':: '))
                $dtoData['DtoNumber'] = $this->formatDtoNumber($dtoData['DtoNumber']);


            $coatedDtos[] = [
                "DtoNumber" => $dtoData['DtoNumber'],
                "Lots" => implode(' | ', $projectLots),
                "Description" => $dtoData['Description'] ?? '',
                "isTin" => $isTin,
                "isSilver" => $isSilver,
                "isNickel" => $isNickel
            ];
        }

        if (empty($coatedDtos))
            returnHttpResponse(400, 'No Coating DTO found in this nachbau ' . $lastNachbauTxt);

        return $coatedDtos;
    }

    public function getCopperMaterialsOfProjectByLots($projectNo) {
        // Siparişte geçen tüm bakırların Lot numaralarına göre bölünmesi.
        $query = "SELECT 
                   opt.Lot, 
                   sp.Material, 
                   sp.ShortText,
                   SUM(sp.Qty) AS TotalQuantity, 
                   sp.BUn AS Unit, 
                   sp.PeggedRequirement, 
                   sp.SupplyArea, 
                   sp.SLoc,
                   sp.MRP, 
                   sp.KMAT, 
                   sp.IndustryDesc
                FROM 
                   rpa.sap_spiridon_001 AS sp
                JOIN 
                   rpa.sap_opt AS opt
                ON 
                   sp.Item = opt.Item AND sp.OrderNo = opt.OrderNo
                WHERE 
                   sp.OrderNo = :pNo
                   AND (sp.IndustryDesc LIKE '%CU%' OR sp.MRP = 'S14')
                GROUP BY 
                   opt.Lot, sp.Material, sp.ShortText, sp.BUn, sp.PeggedRequirement, sp.SupplyArea, sp.SLoc, sp.MRP, sp.KMAT, sp.IndustryDesc
                ORDER BY 
                   opt.Lot, sp.Material;";

        $coppersData = DbManager::fetchPDOQueryData('rpa', $query, [':pNo' => $projectNo])['data'];
        $coppersDataSource = "Production";

        if (count($coppersData) === 0) {
            $query = "SELECT 
                   opt.Lot, 
                   sp.Material, 
                   sp.ShortText,
                   SUM(sp.Qty) AS TotalQuantity, 
                   sp.BUn AS Unit, 
                   sp.PeggedRequirement, 
                   sp.SupplyArea, 
                   sp.SLoc,
                   sp.MRP, 
                   sp.KMAT, 
                   sp.IndustryDesc
                FROM 
                   rpa.sap_spiridon_002 AS sp
                JOIN 
                   rpa.sap_opt AS opt
                ON 
                   sp.Item = opt.Item AND sp.OrderNo = opt.OrderNo
                WHERE 
                   sp.OrderNo = :pNo
                   AND (sp.IndustryDesc LIKE '%CU%' OR sp.MRP = 'S14')
                GROUP BY 
                   opt.Lot, sp.Material, sp.ShortText, sp.BUn, sp.PeggedRequirement, sp.SupplyArea, sp.SLoc, sp.MRP, sp.KMAT, sp.IndustryDesc
                ORDER BY 
                   opt.Lot, sp.Material;";

            $coppersData = DbManager::fetchPDOQueryData('rpa', $query, [':pNo' => $projectNo])['data'];
            $coppersDataSource = "Plan";
        }

        return ['coppersData' => $coppersData, 'coppersDataSource' => $coppersDataSource];
    }

    public function getReleasedProjectDtoChanges($projectNo) {
        $query = "SELECT released_project_id FROM view_released_projects WHERE project_number = :pNo AND submission_status = 5";
        $releasedProjectData =  DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo])['data'];

        if(count($releasedProjectData) === 0 ) {
            echo json_encode([
                'success' => false,
                'message' => 'This project has not been released yet.'
            ]);
            exit();
        }

        $query = "SELECT released_project_id FROM view_released_projects 
                    WHERE project_number = :pNo AND submission_status NOT IN ('4', '5') 
                    ORDER BY released_project_id DESC LIMIT 1";
        $latestReleasedOrdersProjectId = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo])['data'][0]['released_project_id'];

        $query = "SELECT DISTINCT operation, material_added_starts_by, material_added_number, material_deleted_starts_by, material_deleted_number, release_quantity, release_unit 
              FROM view_released_order_changes 
              WHERE released_project_id = :pId AND active = 1";
        $orderChanges = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pId' => $latestReleasedOrdersProjectId])['data'];

        $addedMaterials = [];
        $deletedMaterials = [];
        $addedMaterialsWithQuantity = [];

        foreach ($orderChanges as $change) {
            $operation = $change['operation'];

            if ($operation === 'delete') {
                $deletedMaterial = $change['material_deleted_starts_by'] . $change['material_deleted_number'];
                if (!empty($deletedMaterial) && !in_array($deletedMaterial, $deletedMaterials)) {
                    $deletedMaterials[] = $deletedMaterial;
                }
            }
            elseif ($operation === 'add') {
                $addedMaterial = $change['material_added_starts_by'] . $change['material_added_number'];
                if (!empty($addedMaterial) && !in_array($addedMaterial, $addedMaterials)) {
                    $addedMaterials[] = $addedMaterial;

                    $addedMaterialsWithQuantity[$addedMaterial] = [
                        'quantity' => $change['release_quantity'],
                        'unit' => $change['release_unit']
                    ];
                }
            }
            elseif ($operation === 'replace') {
                $addedMaterial = $change['material_added_starts_by'] . $change['material_added_number'];
                if (!empty($addedMaterial) && !in_array($addedMaterial, $addedMaterials)) {
                    $addedMaterials[] = $addedMaterial;

                    $addedMaterialsWithQuantity[$addedMaterial] = [
                        'quantity' => $change['release_quantity'],
                        'unit' => $change['release_unit']
                    ];
                }

                $deletedMaterial = $change['material_deleted_starts_by'] . $change['material_deleted_number'];
                if (!empty($deletedMaterial) && !in_array($deletedMaterial, $deletedMaterials)) {
                    $deletedMaterials[] = $deletedMaterial;
                }
            }
        }

        $addedLastNumbers = [];
        $deletedLastNumbers = [];
        $addedLastNumbersWithQuantity = [];

        // addedMaterials için tüm alt kırılımları bul ve 0'la bitenleri filtrele
        if (!empty($addedMaterials)) {
            $allAddedComponents = $this->getDeepComponents($addedMaterials);

            foreach ($allAddedComponents as $component) {
                // 0 ile bitenleri (bakır malzemeleri) $addedLastNumbers'a ekle
                if (str_ends_with($component, '0') && !in_array($component, $addedLastNumbers)) {
                    $addedLastNumbers[] = $component;

                    // Bu component hangi üst malzemeden geliyorsa onun quantity/unit bilgisini al
                    foreach ($addedMaterials as $parentMaterial) {
                        if (isset($addedMaterialsWithQuantity[$parentMaterial])) {
                            $addedLastNumbersWithQuantity[$component] = $addedMaterialsWithQuantity[$parentMaterial];
                            break;
                        }
                    }
                }
            }
        }

        // deletedMaterials için tüm alt kırılımları bul ve 0'la bitenleri filtrele
        if (!empty($deletedMaterials)) {
            $allDeletedComponents = $this->getDeepComponents($deletedMaterials);

            foreach ($allDeletedComponents as $component) {
                // 0 ile bitenleri (bakır malzemeleri) $deletedLastNumbers'a ekle
                if (str_ends_with($component, '0') && !in_array($component, $deletedLastNumbers)) {
                    $deletedLastNumbers[] = $component;
                }
            }
        }

        // Bakır olmayan malzemeleri arraylerden çıkar
        if (!empty($addedLastNumbers)) {
            $query = "SELECT DISTINCT Material 
                      FROM sap_spiridon_064 
                      WHERE Material IN (:materials) 
                      AND (IndustryDesc LIKE '%CU%' OR MRP = 'S14')";
            $copperAddedMaterials = DbManager::fetchPDOQueryData('rpa', $query, [':materials' => $addedLastNumbers])['data'];

            // Sadece bakır olan malzemeleri tut
            $addedLastNumbers = array_column($copperAddedMaterials, 'Material');
        }

        if (!empty($deletedLastNumbers)) {
            $query = "SELECT DISTINCT Material 
                      FROM sap_spiridon_064 
                      WHERE Material IN (:materials) 
                      AND (IndustryDesc LIKE '%CU%' OR MRP = 'S14')";
            $copperDeletedMaterials = DbManager::fetchPDOQueryData('rpa', $query, [':materials' => $deletedLastNumbers])['data'];

            // Sadece bakır olan malzemeleri tut
            $deletedLastNumbers = array_column($copperDeletedMaterials, 'Material');
        }

        return [
            'addedMaterials' => $addedLastNumbers,
            'deletedMaterials' => $deletedLastNumbers,
            'addedMaterialsWithQuantity' => $addedLastNumbersWithQuantity
        ];
    }

    // Recursive olarak tüm alt kırılımları bulan fonksiyon
    function getDeepComponents($materials, $allFoundComponents = []) {
        if (empty($materials)) {
            return $allFoundComponents;
        }

        $query = "SELECT DISTINCT component 
              FROM sap_cs03 
              WHERE material IN (:materials) AND (COMPONENT LIKE '%0' OR COMPONENT LIKE '%3')";
        $result = DbManager::fetchPDOQueryData('rpa', $query, [':materials' => $materials])['data'];

        $materialsEndingWith3 = [];

        foreach ($result as $row) {
            $component = $row['component'];

            if (!empty($component) && !in_array($component, $allFoundComponents)) {
                $allFoundComponents[] = $component;

                // 3 ile bitiyorsa, bunun da alt kırılımlarını aramak için kaydet
                if (str_ends_with($component, '3')) {
                    $materialsEndingWith3[] = $component;
                }
            }
        }

        // 3 ile biten malzemeler varsa, onların da alt kırılımlarını bul
        if (!empty($materialsEndingWith3)) {
            return $this->getDeepComponents($materialsEndingWith3, $allFoundComponents);
        }

        return $allFoundComponents;
    }


    public function getBanfomatPool(): void{
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Get Banfomat Pool List Request");
        Journals::saveJournal("PROCESSING | Get Banfomat Pool List Request", PAGE_BANFOMAT, BANFOMAT_POOL, ACTION_PROCESSING, implode(' | ', $_GET), "Banfomat Pool");

        $query = "SELECT * FROM banfomat_material_details ORDER BY id DESC";
        $banfomatMaterials = DbManager::fetchPDOQuery('dto_configurator', $query)['data'] ?? [];

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | Banfomat Pool List Request Returned Successfully");
        Journals::saveJournal("RETURNED | Banfomat Pool List Request Returned Successfully", PAGE_BANFOMAT, BANFOMAT_POOL, ACTION_VIEWED, implode(' | ', $_GET), "Banfomat Pool");

        echo json_encode($banfomatMaterials);exit();
    }

    public function getMaterialsFromSapBySearch(): void
    {
        $keyword = $_GET['keyword'];

        $query = "SELECT Material, Description 
                  FROM sap_spiridon_064 
                  WHERE Material LIKE :mNo
                  GROUP BY Material 
                  ORDER BY Material DESC
                  LIMIT 25";

        $data = DbManager::fetchPDOQuery('rpa', $query, [':mNo' => "%$keyword%"])['data'] ?? [];
        echo json_encode($data); exit();
    }

    public function saveMaterialToBanfomatPool(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Save Material to Banfomat Pool Request").implode(' | ', $_POST);
        Journals::saveJournal("PROCESSING | Save Material to Banfomat Pool Request", PAGE_BANFOMAT, BANFOMAT_POOL, ACTION_PROCESSING, implode(' | ', $_POST), "Banfomat Pool Create");

        $materialNo = $_POST['materialNo'];
        $surfaceArea = floatval($_POST['surfaceArea']);
        $coatedType = $_POST['coatedType'];
        $coatedPart = $_POST['coatedPart'];
        $details = $_POST['details'];
        $checkBoxWontBeCoated = $_POST['checkBoxWontBeCoated'] === 'true' ? 1 : 0;;
        $metalArray = explode(',', $_POST['metal']);

        $query = "SELECT id, metal FROM banfomat_material_details WHERE material = :mNo AND metal IN (:metalArray)";
        $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':mNo' => $materialNo, ':metalArray' => $metalArray])['data'] ?? [];

        if (!empty($result)) {
            $existingMetals = implode(',',array_column($result, 'metal'));
            returnHttpResponse(400, $materialNo . ' malzemesi ' . $existingMetals . ' için zaten kayıtlı!');
        }

        if ($_FILES['image']['name'])
            $imageFileName = $this->saveBanfomatImageToServer($materialNo, $coatedType, $_FILES['image']);

        $query = "SELECT Description, MType FROM sap_spiridon_064 WHERE Material = :mNo";
        $materialSapData = DbManager::fetchPDOQuery('rpa', $query, [':mNo' => $materialNo])['data'][0];

        if ($materialSapData['MType'] === 'ZTEK')
            $productionLocation = 'Depo';
        else
            $productionLocation = 'Ön imalat';

        foreach($metalArray as $metal) {
            $params = [];
            $query = "INSERT INTO banfomat_material_details(material, description, metal, coated_type, coated_part, details, production_location, surface_area, image_file_name, wont_be_coated, created_by, updated_by)";
            $params[] = [$materialNo, $materialSapData['Description'], $metal, $coatedType, $coatedPart, $details, $productionLocation, $surfaceArea, $imageFileName, $checkBoxWontBeCoated, SharedManager::$fullname, SharedManager::$fullname];
            DbManager::fetchInsert('dto_configurator', $query, $params);
        }

        SharedManager::saveLog('log_dtoconfigurator',"CREATED | Material saved to Banfomat Pool Request").implode(' | ', $_POST);
        Journals::saveJournal("CREATED | Material saved to Banfomat Pool Request", PAGE_BANFOMAT, BANFOMAT_POOL, ACTION_CREATED, implode(' | ', $_POST), "Banfomat Pool Create");
    }

    private string $basePath = "\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\Product_Management\\04_Design_Documents\\99_DTO_Configurator_Files";
    private string $uploadSubPath = "4_Banfomat_Technical_Images";
    private array $acceptedTypes = ["jpeg", "jpg", "png"];
    private int $fileSizeLimit = 3145728;
    public function saveBanfomatImageToServer($materialNo, $coatedType, $image)
    {
        if (isset($image) || !empty($image['name'])) {
            $fileInfo = pathinfo($image['name']);
            $extension = strtolower($fileInfo['extension']);

            // Set the upload path
            $uploadPath = "$this->basePath\\$this->uploadSubPath";
            if (!file_exists($uploadPath)) {
                SharedManager::saveLog('log_dtoconfigurator',"ERROR | Banfomat Image Addition Directory Not Found Error | ".implode(' | ', $_POST));
                Journals::saveJournal("ERROR | Banfomat Image Addition Directory Not Found Error | ".implode(' | ', $_POST), PAGE_BANFOMAT, BANFOMAT_POOL, ACTION_ERROR, implode(' | ', $_POST), "Banfomat Pool Image");
                returnHttpResponse(400, "Directory not found.");
            }

            // Validate the file extension
            if (!in_array($extension, $this->acceptedTypes)) {
                SharedManager::saveLog('log_dtoconfigurator',"ERROR | Banfomat Image Addition Extension Error | ".implode(' | ', $_POST));
                Journals::saveJournal("ERROR | Banfomat Image Addition Extension Error  | ".implode(' | ', $_POST), PAGE_BANFOMAT, BANFOMAT_POOL, ACTION_ERROR, implode(' | ', $_POST), "Banfomat Pool Image");
                returnHttpResponse(400, "Extension must be " . join(', ', $this->acceptedTypes));
            }

            // Validate the file size
            $fileSize = $image['size'];
            if ($fileSize > $this->fileSizeLimit) {
                SharedManager::saveLog('log_dtoconfigurator',"ERROR | Banfomat Image Addition File Size Max 3MB Error | ".implode(' | ', $_POST));
                Journals::saveJournal("ERROR | Banfomat Image Addition File Size Max 3MB Error | ".implode(' | ', $_POST), PAGE_BANFOMAT, BANFOMAT_POOL, ACTION_ERROR, implode(' | ', $_POST), "Banfomat Pool Image");
                returnHttpResponse(400, "File size must be maximum 3MB");
            }

            // Check for file upload errors
            if ($image['error']) {
                SharedManager::saveLog('log_dtoconfigurator',"ERROR | Banfomat Image Addition Unexpected Error | ".implode(' | ', $_POST));
                Journals::saveJournal("ERROR | Banfomat Image Addition Unexpected Error | ".implode(' | ', $_POST), PAGE_BANFOMAT, BANFOMAT_POOL, ACTION_ERROR, implode(' | ', $_POST), "Banfomat Pool Image");
                returnHttpResponse(500, "Unexpected error occurs : {$image['name']}");
            }

            if (str_starts_with(strtolower($coatedType), 'sn'))
                $coatedTypeName = 'tin';
            elseif (str_starts_with(strtolower($coatedType), 'ag'))
                $coatedTypeName = 'silver';
            elseif (str_starts_with(strtolower($coatedType), 'ni'))
                $coatedTypeName = 'nickel';
            else
                $coatedTypeName = '';

            $fileName = $materialNo . '_' . $coatedTypeName . '_' . date("Ymd_His") . '.' . $extension;
            $tempPath = $image['tmp_name'];
            $targetPath = "$uploadPath\\$fileName";

            // Move the uploaded file to the target path
            if (!move_uploaded_file($tempPath, $targetPath)) {
                SharedManager::saveLog('log_dtoconfigurator',"ERROR | Banfomat Image Addition Unexpected Error | ".implode(' | ', $_POST));
                Journals::saveJournal("ERROR | Banfomat Image Addition Unexpected Error | ".implode(' | ', $_POST), PAGE_BANFOMAT, BANFOMAT_POOL, ACTION_ERROR, implode(' | ', $_POST), "Banfomat Pool Image");
                returnHttpResponse(500, "Unexpected error occurs");
            }

            return $fileName;
        }
    }

    public function updateBanfomatPoolRow(): void {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Update Material to Banfomat Pool Request").implode(' | ', $_POST);
        Journals::saveJournal("PROCESSING | Update Material to Banfomat Pool Request", PAGE_BANFOMAT, BANFOMAT_POOL, ACTION_PROCESSING, implode(' | ', $_POST), "Banfomat Pool Update");

        $id = $_POST['id'];
        $materialNo = $_POST['materialNo'];
        $metal = $_POST['metal'];
        $surfaceArea = floatval($_POST['surfaceArea']);
        $coatedType = $_POST['coatedType'];
        $coatedPart = $_POST['coatedPart'];
        $details = $_POST['details'];
        $imageChanged = $_POST['imageChanged'];
        $existingImageName = $_POST['existingImageName'];
        $checkBoxWontBeCoated = $_POST['checkBoxWontBeCoated'] === 'true' ? 1 : 0;

        if ($imageChanged === "1" && isset($_FILES['edit-image']) && $_FILES['edit-image']['error'] == 0) {
            $imageFileName = $this->saveBanfomatImageToServer($materialNo, $coatedType, $_FILES['edit-image']);

            if ($imageFileName) {
                // Delete old image if exists
                $uploadPath = "$this->basePath\\$this->uploadSubPath";
                if (!empty($existingImageName) && file_exists("$uploadPath\\$existingImageName")) {
                    unlink("$uploadPath\\$existingImageName");
                }
            } else {
                $imageFileName = $existingImageName; // If upload fails, keep the old image
            }
        } else if ($imageChanged === "1" && $_FILES['edit-image']['name'] === "") {
            $uploadPath = "$this->basePath\\$this->uploadSubPath";
            if (!empty($existingImageName) && file_exists("$uploadPath\\$existingImageName")) {
                unlink("$uploadPath\\$existingImageName");
                $imageFileName = NULL;
            }
        } else {
            $imageFileName = $existingImageName; // No change, keep old image
        }

        $query = "SELECT Description, MType FROM sap_spiridon_064 WHERE Material = :mNo";
        $materialSapData = DbManager::fetchPDOQuery('rpa', $query, [':mNo' => $materialNo])['data'][0];

        if ($materialSapData['MType'] === 'ZTEK')
            $productionLocation = 'Depo';
        else
            $productionLocation = 'Ön imalat';

        $query = "UPDATE banfomat_material_details 
          SET material = :materialNo, 
              description = :description,
              metal = :metal,
              surface_area = :surfaceArea, 
              coated_type = :coatedType, 
              coated_part = :coatedPart, 
              details = :details, 
              production_location = :productionLocation,
              image_file_name = :imageFileName,
              wont_be_coated = :wontBeCoated
          WHERE id = :id";

        $params = [
            ':materialNo' => $materialNo,
            ':description' => $materialSapData['Description'],
            ':metal' => $metal,
            ':surfaceArea' => $surfaceArea,
            ':coatedType' => $coatedType,
            ':coatedPart' => $coatedPart,
            ':details' => $details,
            ':productionLocation' => $productionLocation,
            ':imageFileName' => $imageFileName,
            ':wontBeCoated' => $checkBoxWontBeCoated,
            ':id' => $id
        ];

        DbManager::fetchPDOQuery('dto_configurator', $query, $params);

        SharedManager::saveLog('log_dtoconfigurator',"UPDATED | Material Updated to Banfomat Pool Request").implode(' | ', $_POST);
        Journals::saveJournal("UPDATED | Material Updated to Banfomat Pool Request", PAGE_BANFOMAT, BANFOMAT_POOL, ACTION_MODIFIED, implode(' | ', $_POST), "Banfomat Pool Update");
    }


    public function getProjectBanfomatDetails(): void {
        ini_set('memory_limit', '512M');
        $projectNo = $_GET['projectNo'];
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Get Project Banfomat Details (Excel Preview Page) Request | " . $projectNo);
        Journals::saveJournal("PROCESSING | Get Project Banfomat Details (Excel Preview Page) Request | " . $projectNo, PAGE_BANFOMAT, PROJECT_BANFOMAT_INFO, ACTION_PROCESSING, implode(' | ', $_GET), "Banfomat Project Info");

        $query = "SELECT FileName FROM log_nachbau WHERE FactoryNumber = :pNo ORDER BY ID DESC LIMIT 1";
        $lastNachbauResult = DbManager::fetchPDOQueryData('logs', $query, [':pNo' => $projectNo])['data'][0] ?? [];

        if(empty($lastNachbauResult))
            returnHttpResponse(400, 'There is no nachbau file found for this project.');

        $lastNachbauTxt = $lastNachbauResult['FileName'];

        $coatedDtosOfProject = $this->getCoatedDtosAndDetails($projectNo, $lastNachbauTxt);

        $nickelCount = 0;
        $silverCount = 0;
        $tinCount = 0;

        foreach ($coatedDtosOfProject as $dto) {
            if ($dto['isNickel']) $nickelCount++;
            if ($dto['isSilver']) $silverCount++;
            if ($dto['isTin']) $tinCount++;
        }

        $searchConditions = '';
        $coatingTypeDto = [];
        if ($silverCount > 0) {
            $searchConditions = "metal = 'Gümüş'";
            $coatingTypeDto[] = 'GÜMÜŞ';
        }
        if ($tinCount > 0){
            $searchConditions = "metal = 'Kalay'";
            $coatingTypeDto[] = 'KALAY';
        }
        if ($nickelCount > 0) {
            $searchConditions = "metal = 'Nikel'";
            $coatingTypeDto[] = 'NİKEL';
        }

        if (!$searchConditions)
            returnHttpResponse(400, 'Coating type was not found.');

        $query = "SELECT 
                    opt.Lot,
                    sp.Material,
                    MIN(sp.ShortText) AS ShortText,
                    SUM(sp.Qty) AS Quantity,
                    MIN(sp.BUn) AS Unit,
                    MIN(sp.PeggedRequirement) AS PeggedRequirement,
                    MIN(sp.SupplyArea) AS SupplyArea,
                    MIN(sp.MRP) AS MRP,
                    MIN(sp.SLoc) AS SLoc,
                    MIN(sp.KMAT) AS KMAT,
                    MIN(sp.IndustryDesc) AS IndustryDesc
                FROM 
                    rpa.sap_spiridon_001 AS sp
                JOIN 
                    rpa.sap_opt AS opt
                    ON sp.Item = opt.Item 
                    AND sp.OrderNo = opt.OrderNo
                WHERE 
                    sp.OrderNo = :pNo
                    AND (sp.IndustryDesc LIKE '%CU%' OR sp.MRP = 'S14')
                GROUP BY 
                    opt.Lot, sp.Material
                ORDER BY 
                    opt.Lot, sp.Material;";
        $copperMaterialsOfProject = DbManager::fetchPDOQuery('rpa', $query, [':pNo' => $projectNo])['data'] ?? [];

        if (empty($copperMaterialsOfProject)) {

            // If spiridon_001 empty, check 002 to fetch materials
            $query = "SELECT 
                    opt.Lot,
                    sp.Material,
                    MIN(sp.ShortText) AS ShortText,
                    SUM(sp.Qty) AS Quantity,
                    MIN(sp.BUn) AS Unit,
                    MIN(sp.PeggedRequirement) AS PeggedRequirement,
                    MIN(sp.SupplyArea) AS SupplyArea,
                    MIN(sp.MRP) AS MRP,
                    MIN(sp.SLoc) AS SLoc,
                    MIN(sp.KMAT) AS KMAT,
                    MIN(sp.IndustryDesc) AS IndustryDesc
                FROM 
                    rpa.sap_spiridon_002 AS sp
                JOIN 
                    rpa.sap_opt AS opt
                    ON sp.Item = opt.Item 
                    AND sp.OrderNo = opt.OrderNo
                WHERE 
                    sp.OrderNo = :pNo
                    AND (sp.IndustryDesc LIKE '%CU%' OR sp.MRP = 'S14')
                GROUP BY 
                    opt.Lot, sp.Material
                ORDER BY 
                    opt.Lot, sp.Material;";
            $copperMaterialsOfProject = DbManager::fetchPDOQuery('rpa', $query, [':pNo' => $projectNo])['data'] ?? [];

            $releasedDtoChanges = $this->getReleasedProjectDtoChanges($projectNo);

            // deletedMaterials içindeki malzemeleri $copperMaterialsOfProject'den çıkart
            if (!empty($releasedDtoChanges['deletedMaterials'])) {
                $copperMaterialsOfProject = array_filter($copperMaterialsOfProject, function($copper) use ($releasedDtoChanges) {
                    return !in_array($copper['Material'], $releasedDtoChanges['deletedMaterials']);
                });

                // Array indekslerini yeniden düzenle (0, 1, 2... şeklinde)
                $copperMaterialsOfProject = array_values($copperMaterialsOfProject);
            }

            // addedMaterials içindeki malzemeleri $copperMaterialsOfProject'e ekle
            if (!empty($releasedDtoChanges['addedMaterials'])) {
                $query = "SELECT 
                            Material,
                            Description AS ShortText,
                            MRP,
                            IndustryDesc,
                            SLoc
                        FROM 
                            rpa.sap_spiridon_064
                        WHERE 
                            Material IN (:materials)";
                $addedCopperMaterials = DbManager::fetchPDOQuery('rpa', $query, [':materials' => $releasedDtoChanges['addedMaterials']])['data'] ?? [];

                // Eklenen malzemeleri copperMaterialsOfProject'e ekle
                foreach ($addedCopperMaterials as $addedMaterial) {
                    $materialNumber = $addedMaterial['Material'];
                    $quantityInfo = $releasedDtoChanges['addedMaterialsWithQuantity'][$materialNumber] ?? ['quantity' => 0, 'unit' => null];

                    $copperMaterialsOfProject[] = [
                        'Lot' => null,
                        'Material' => $materialNumber,
                        'ShortText' => $addedMaterial['ShortText'],
                        'Quantity' => $quantityInfo['quantity'],
                        'Unit' => $quantityInfo['unit'],
                        'PeggedRequirement' => null,
                        'SupplyArea' => null,
                        'MRP' => $addedMaterial['MRP'],
                        'SLoc' => null,
                        'KMAT' => null,
                        'IndustryDesc' => $addedMaterial['IndustryDesc']
                    ];
                }
            }
        }

        $totalSilverCoatedArea = 0.0;
        $totalTinCoatedArea = 0.0;
        $totalNickelCoatedArea = 0.0;
        $banfomatMaterialsOfProject = [];
        foreach($copperMaterialsOfProject as &$copperMaterial) {
            $query = "SELECT * FROM banfomat_material_details WHERE material = :mNo AND $searchConditions";
            $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':mNo' => $copperMaterial['Material']])['data'][0] ?? [];

            if ($result['wont_be_coated'] === '1')
                continue; // Eğer Malzeme Banfomat Dışı ise atla.

            if (empty($result))
                $copperMaterial['isExistInPool'] = false;
            else
                $copperMaterial['isExistInPool'] = true;

            $copperMaterial['Id'] = $result['id'];
            $copperMaterial['Description'] = $result['description'] ?? $copperMaterial['ShortText'] ?? '';
            $copperMaterial['CoatedType'] = $result['coated_type'] ?? '';
            $copperMaterial['CoatedPart'] = $result['coated_part'] ?? '';
            $copperMaterial['Details'] = $result['details'] ?? '';
            $copperMaterial['ProductionLocation'] = $result['production_location'] ?? '';
            $copperMaterial['SurfaceArea'] = $result['surface_area'];
            $copperMaterial['Metal'] = $result['metal'];
            $copperMaterial['TotalSurfaceArea'] = floatval($result['surface_area']) * floatval($copperMaterial['Quantity']);
            $copperMaterial['ImageFileName'] = $result['image_file_name'];

            if(str_starts_with($result['coated_type'], 'Ag'))
                $totalSilverCoatedArea += floatval($copperMaterial['TotalSurfaceArea']);
            if(str_starts_with($result['coated_type'], 'Sn'))
                $totalTinCoatedArea +=  floatval($copperMaterial['TotalSurfaceArea']);
            if(str_starts_with($result['coated_type'], 'Ni'))
                $totalNickelCoatedArea += floatval($copperMaterial['TotalSurfaceArea']);

            $copperMaterial['TotalSurfaceArea'] = round(floatval($result['surface_area']) * floatval($copperMaterial['Quantity']), 7);

            $banfomatMaterialsOfProject[] = $copperMaterial;
        }

        // Start gzip output buffering
        ob_start("ob_gzhandler");

        // Send JSON header
        header("Content-Encoding: gzip");
        header("Content-Type: application/json");

        $data = [
            'banfomatMaterialsOfProject' => $banfomatMaterialsOfProject,
            'totalSilverCoatedArea' => round($totalSilverCoatedArea, 7),
            'totalTinCoatedArea' => round($totalTinCoatedArea, 7),
            'totalNickelCoatedArea' => round($totalNickelCoatedArea, 7),
            'coatingTypeDto' => implode(' ve ', $coatingTypeDto)
        ];

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | Get Project Banfomat Details (Excel Preview Page) Request Successful | " . $projectNo);
        Journals::saveJournal("RETURNED | Get Project Banfomat Details (Excel Preview Page) Request Successful | " . $projectNo, PAGE_BANFOMAT, PROJECT_BANFOMAT_INFO, ACTION_VIEWED, implode(' | ', $_GET), "Banfomat Project Info");

        echo json_encode($data); exit();
    }

    public function exportBanfomatToExcel() {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Export Banfomat Details to Excel Request | " . implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Export Banfomat Details to Excel Request | " . implode(' | ', $_POST), PAGE_BANFOMAT, PROJECT_BANFOMAT_INFO, ACTION_PROCESSING, implode(' | ', $_POST), "Banfomat Project Info");

        $columns = json_decode($_POST['columns'], true);
        $rows = json_decode($_POST['data'], true);
        $projectNo = $_POST['projectNo'];
        $lotNumber = $_POST['selectedLot'];
        $excelFileName = $_POST['excelFileName'];
        $downloadRequestFromPage = $_POST['downloadRequestFromPage'];

        $totalSurfaceAreas = $this->getTotalSurfaceAreas($rows);

        if (empty($columns) || empty($rows)) {
            returnHttpResponse(400, 'Invalid Input');
            exit;
        }

        if ($downloadRequestFromPage !== 'historyPage') {
            $result = $this->checkIfBanfomatHistoryDataExists($projectNo, $lotNumber);

            if (!empty($result))
                returnHttpResponse(400,$projectNo . ' siparişinin Lot ' . $lotNumber . '\'ine ait banfomat exceli zaten oluşturulmuş. Lütfen görüntülemek için Banfomat Geçmişi sayfasına göz atın.');
        }

        $metal = $rows[0]['Metal'];

        // 2. Create a new PHPExcel object and select first sheet
        $objPHPExcel = new PHPExcel();
        $sheet       = $objPHPExcel->setActiveSheetIndex(0);

        // 3. Write header row
        $headerRow = 1;
        $colIndex  = 0;
        foreach ($columns as $headerText) {
            $sheet->setCellValueByColumnAndRow($colIndex, $headerRow, $headerText);
            $colIndex++;
        }

        // Set J column header for images
        $sheet->setCellValue('J1', 'Teknik Resim');

        // Write data rows
        $currentRow = 2;
        foreach ($rows as $r) {
            $colIndex = 0;

            $sheet->setCellValueByColumnAndRow($colIndex++, $currentRow, $r['OrderListNo']);
            $sheet->setCellValueByColumnAndRow($colIndex++, $currentRow, $r['Material']);
            $sheet->setCellValueByColumnAndRow($colIndex++, $currentRow, $r['Quantity'] ?? '');
            $sheet->setCellValueByColumnAndRow($colIndex++, $currentRow, ($r['SurfaceArea'] ?? 0) == 0 ? '' : $r['SurfaceArea']);
            $sheet->setCellValueByColumnAndRow($colIndex++, $currentRow, ($r['TotalSurfaceArea'] ?? 0) == 0 ? '' : $r['TotalSurfaceArea']);
            $sheet->setCellValueByColumnAndRow($colIndex++, $currentRow, $r['CoatedType']);
            $sheet->setCellValueByColumnAndRow($colIndex++, $currentRow, $r['CoatedPart']);
            $sheet->setCellValueByColumnAndRow($colIndex++, $currentRow, $r['Details']);
            $sheet->setCellValueByColumnAndRow($colIndex++, $currentRow, $r['ProductionLocation']);

            // Handle Image in Column J
            if (!empty($r['ImageFileName'])) {
                $imagePath = "\\\\ad001.siemens.net\\dfs001\\File\\TR\\SI_DS_TR_OP\\Product_Management\\04_Design_Documents\\99_DTO_Configurator_Files\\4_Banfomat_Technical_Images\\" . $r['ImageFileName'];

                if (file_exists($imagePath)) {
                    $objDrawing = new PHPExcel_Worksheet_Drawing();
                    $objDrawing->setName('Teknik Resim');
                    $objDrawing->setDescription('Teknik Resim');
                    $objDrawing->setPath($imagePath);
                    $objDrawing->setHeight(50); // Set image height
                    $objDrawing->setCoordinates('J' . $currentRow); // Place in column J

                    // Set image offsets
                    $objDrawing->setOffsetX(25);
                    $objDrawing->setOffsetY(10);

                    // Add the drawing to the worksheet
                    $objDrawing->setWorksheet($sheet);

                    // Adjust row height to fit the image
                    $sheet->getRowDimension($currentRow)->setRowHeight(50); // Match image height
                } else {
                    // If image is missing, leave the cell empty or display a placeholder
                    $sheet->setCellValue("J{$currentRow}", "");
                }
            }

            // Zebra styling: if currentRow is even, fill with light gray
            if (($currentRow % 2) === 0) {
                $sheet->getStyle("A{$currentRow}:I{$currentRow}")
                    ->applyFromArray([
                        'fill' => [
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'startcolor' => ['rgb' => 'EFEFEF']
                        ]
                    ]);
            }

            $currentRow++;
        }

        // Resize columns
        $sheet->getColumnDimension('A')->setWidth(17);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(8);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(24);
        $sheet->getColumnDimension('G')->setWidth(17);
        $sheet->getColumnDimension('H')->setWidth(80);
        $sheet->getColumnDimension('I')->setWidth(34);
        $sheet->getColumnDimension('K')->setWidth(32);
        $sheet->getColumnDimension('L')->setWidth(18);
        $sheet->getColumnDimension('J')->setWidth(15);

        // If you want “wrap text” in that column:
        $sheet->getStyle('H')->getAlignment()->setWrapText(true);

        // 2) Make the header row (A1:I1) bold and centered
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('A1:J1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // 3) Optionally apply thin borders around all cells (header + data).
        //    Suppose your data goes from row 1 to $currentRow - 1
        //    (because we incremented $currentRow in the loop):
        $lastDataRow = $currentRow - 1;  // if $currentRow ended up 1 past last
        $styleArray = [
            'borders' => [
                'allborders' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ]
            ]
        ];
        $sheet->getStyle("A1:J{$lastDataRow}")->applyFromArray($styleArray);

        // 4) (Optional) background fill for header row
        $sheet->getStyle('A1:J1')->applyFromArray([
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => ['rgb' => 'D8E4BC']
            ]
        ]);

        // Text Align Center for every cell
        $highestRow    = $sheet->getHighestRow();
        $range = "A1:L1{$highestRow}";
        $sheet->getStyle($range)->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        // 5) If the coated-area strings are NOT empty, write them to K/L rows 2..4
        // Define column labels
        $columnLabel = 'K';
        $columnValue = 'L';

        // Define row numbers for coatings
        $rowSilver  = 2;
        $rowTin     = 3;
        $rowNickel  = 4;

        // Define SAP Order Code Row (Always Displayed)
        $sapOrderRow = 6;
        $sheet->setCellValue("{$columnValue}{$sapOrderRow}", "SAP Sipariş Kodu");

        // Apply bold and underline style to "SAP Sipariş Kodu"
        $sapOrderStyle = [
            'font' => [
                'bold' => true,
                'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
            ],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            ]
        ];

        $sheet->getStyle("{$columnValue}{$sapOrderRow}")->applyFromArray($sapOrderStyle);

        // Define fixed rows for metal types
        $rowSilverCode = 7;
        $rowTinCode = 8;
        $rowNickelCode = 9;

        $styleArray = [
            'font' => [
                'bold' => true // Bold font
            ],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            ],
            'borders' => [
                'allborders' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'] // Black borders
                ]
            ]
        ];

        if ($totalSurfaceAreas['Silver'] !== 0.0) {
            // Write data for Silver plated area
            $sheet->setCellValue("{$columnLabel}{$rowSilver}", "Gümüş Kaplanacak Toplam Alan");
            $sheet->setCellValue("{$columnValue}{$rowSilver}", $totalSurfaceAreas['Silver'] . '  m²');

            // Apply styles (background light blue)
            $silverStyle = array_merge($styleArray, [
                'fill' => [
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => ['rgb' => 'D9EAF7'] // Light blue
                ]
            ]);
            $sheet->getStyle("{$columnLabel}{$rowSilver}:{$columnValue}{$rowSilver}")->applyFromArray($silverStyle);
            $sheet->setCellValue("{$columnLabel}{$rowSilverCode}", "Gümüş Kaplama");

            // Hangi tip gümüş kaplama çoğunlukta kullanıldıysa, onun sap kodunu almak için banfomat uygulanan parçaların coatingType nın max ını almanın en garanti olacağı üzerine konuşuldu.
            $silverCoatingType = $this->getMaxAgCoatedType($rows);

            if ($silverCoatingType === 'Ag3+2') {
                $sheet->setCellValue("{$columnValue}{$rowSilverCode}", "A7ET020021085");
            }
            else if ($silverCoatingType === 'Ag10+2') {
                $sheet->setCellValue("{$columnValue}{$rowSilverCode}", "A7ET020021086");
            }
            else if ($silverCoatingType === 'Ag20+4') {
                $sheet->setCellValue("{$columnValue}{$rowSilverCode}", "A7ET020021087");
            }
            else if ($silverCoatingType === 'Ag25+6' || $silverCoatingType === 'Ag25+6 & Ni5+25') {
                $sheet->setCellValue("{$columnValue}{$rowSilverCode}", "A7ET020031087");
            }

            // Write SAP Code for Silver
            $sheet->getStyle("{$columnLabel}{$rowSilverCode}:{$columnValue}{$rowSilverCode}")->applyFromArray($silverStyle);
        }

        if ($totalSurfaceAreas['Tin'] !== 0.0) {
            // Write data for Tin plated area
            $sheet->setCellValue("{$columnLabel}{$rowTin}", "Kalay Kaplanacak Toplam Alan");
            $sheet->setCellValue("{$columnValue}{$rowTin}", $totalSurfaceAreas['Tin'] . '  m²');

            // Apply styles (background light green)
            $tinStyle = array_merge($styleArray, [
                'fill' => [
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => ['rgb' => 'D9F7D9'] // Light green
                ]
            ]);
            $sheet->getStyle("{$columnLabel}{$rowTin}:{$columnValue}{$rowTin}")->applyFromArray($tinStyle);

            // Write SAP Code for Tin
            $sheet->setCellValue("{$columnLabel}{$rowTinCode}", "Kalay Kaplama");
            $sheet->setCellValue("{$columnValue}{$rowTinCode}", "A7ET020021008");
            $sheet->getStyle("{$columnLabel}{$rowTinCode}:{$columnValue}{$rowTinCode}")->applyFromArray($tinStyle);
        }

        if ($totalSurfaceAreas['Nickel'] !== 0.0) {
            // Write data for Nickel plated area
            $sheet->setCellValue("{$columnLabel}{$rowNickel}", "Nikel Kaplanacak Toplam Alan");
            $sheet->setCellValue("{$columnValue}{$rowNickel}", $totalSurfaceAreas['Nickel'] . '  m²');

            // Apply styles (background light yellow)
            $nickelStyle = array_merge($styleArray, [
                'fill' => [
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => ['rgb' => 'FFFACD'] // Light yellow
                ]
            ]);
            $sheet->getStyle("{$columnLabel}{$rowNickel}:{$columnValue}{$rowNickel}")->applyFromArray($nickelStyle);

            // Write SAP Code for Nickel
            $sheet->setCellValue("{$columnLabel}{$rowNickelCode}", "Nikel Kaplama");
            $sheet->setCellValue("{$columnValue}{$rowNickelCode}", "A7ET020021007");
            $sheet->getStyle("{$columnLabel}{$rowNickelCode}:{$columnValue}{$rowNickelCode}")->applyFromArray($nickelStyle);
        }

        $this->saveBanfomatHistoryData($projectNo, $lotNumber, $rows, $metal, $totalSilverCoatedArea, $totalTinCoatedArea, $totalNickelCoatedArea, $excelFileName);


        // 5. Send response headers and output the file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename=' . $excelFileName);
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function getBanfomatPoolDetailsById() {
        $banfomatRowId = $_GET['banfomatRowId'];

        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Get Banfomat Pool Details By Id Request With Banfomat Material ID : " . $banfomatRowId);
        Journals::saveJournal("PROCESSING | Get Banfomat Pool Details By Id Request With Banfomat Material ID : " . $banfomatRowId, PAGE_BANFOMAT, BANFOMAT_POOL, ACTION_PROCESSING, implode(' | ', $_GET), "Banfomat Pool");

        $query = "SELECT * FROM banfomat_material_details WHERE id = :id";
        $banfomatDetails = DbManager::fetchPDOQuery('dto_configurator', $query, [':id' => $banfomatRowId])['data'][0] ?? [];

        if (empty($banfomatDetails))
            returnHttpResponse(400, 'İlgili satıra ait banfomat detay bilgisi bulunamadı.');

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | Banfomat Pool Details By Id Request Successful With Banfomat Material ID : " . $banfomatRowId);
        Journals::saveJournal("RETURNED | Get Banfomat Pool Details By Id Request Successful With Banfomat Material ID : " . $banfomatRowId, PAGE_BANFOMAT, BANFOMAT_POOL, ACTION_VIEWED, implode(' | ', $_GET), "Banfomat Pool");

        echo json_encode($banfomatDetails); exit();
    }

    public function deleteBanfomatPoolRow(): void {
        $rowId = $_POST['id'];

        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Remove Banfomat Pool By Id Request With Banfomat Material ID : " . $rowId);
        Journals::saveJournal("PROCESSING | Remove Banfomat Pool By Id Request With Banfomat Material ID : " . $rowId, PAGE_BANFOMAT, BANFOMAT_POOL, ACTION_PROCESSING, implode(' | ', $_POST), "Banfomat Pool");

        $query = "SELECT image_file_name FROM banfomat_material_details WHERE id = :id";
        $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':id' => $rowId]);
        $imageFileName = $result['data'][0]['image_file_name'] ?? '';

        $query = "DELETE FROM banfomat_material_details WHERE id = :id";
        DbManager::fetchPDOQuery('dto_configurator', $query, [':id' => $rowId]);

        if (!empty($imageFileName)) {
            $uploadPath = "$this->basePath\\$this->uploadSubPath";
            $filePath = "$uploadPath\\$imageFileName";
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Remove Banfomat Pool By Id Request Successful With Banfomat Material ID : " . $rowId);
        Journals::saveJournal("PROCESSING | Remove Banfomat Pool By Id Request Successful With Banfomat Material ID : " . $rowId, PAGE_BANFOMAT, BANFOMAT_POOL, ACTION_DELETED, implode(' | ', $_POST), "Banfomat Pool");

    }

    public function saveBanfomatHistoryData($projectNo, $lotNumber, $banfomatMaterials, $metal, $totalSilverCoatedArea, $totalTinCoatedArea, $totalNickelCoatedArea, $excelFileName): void
    {
        $query = "INSERT INTO banfomat_history(order_no, lot, banfomat_material_id, material_order_quantity, metal, total_silver_coated_area, total_tin_coated_area, total_nickel_coated_area, excel_file_name, created_by)";
        $parameters = [];
        foreach($banfomatMaterials as $banfomatMaterial) {
            $parameters[] = [$projectNo, $lotNumber, $banfomatMaterial['Id'], $banfomatMaterial['Quantity'], $metal, $totalSilverCoatedArea, $totalTinCoatedArea, $totalNickelCoatedArea, $excelFileName, SharedManager::$fullname];
        }

        DbManager::fetchInsert('dto_configurator', $query, $parameters);

        SharedManager::saveLog('log_dtoconfigurator',"CREATED | Banfomat Excel Data Inserted Into Banfomat History Successfully | " . implode(' | ', $_POST));
        Journals::saveJournal("CREATED | Banfomat History Data Inserted Into Banfomat History Successfully | " . implode(' | ', $_POST), PAGE_BANFOMAT, PROJECT_BANFOMAT_INFO, ACTION_CREATED, implode(' | ', $_POST), "Banfomat Project Info");
    }

    public function checkIfBanfomatHistoryDataExists($projectNo, $lot): array {
        $query = "SELECT id FROM banfomat_history WHERE order_no = :orderNo AND lot = :lot AND deleted IS NULL LIMIT 1";
        return DbManager::fetchPDOQuery('dto_configurator', $query, [':orderNo' => $projectNo, ':lot' => $lot])['data'] ?? [];
    }

    public function getAllBanfomatHistoryData() {
        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Get All Banfomat History Data Request");
        Journals::saveJournal("PROCESSING | Get All Banfomat History Data Request", PAGE_BANFOMAT, BANFOMAT_HISTORY, ACTION_PROCESSING, implode(' | ', $_GET), "Banfomat History");

        $query = "SELECT 
                    id, order_no, lot, metal, total_silver_coated_area, total_tin_coated_area, total_nickel_coated_area, 
                    excel_file_name, created_by, DATE_FORMAT(created, '%d.%m.%Y') AS created 
                  FROM banfomat_history
                  WHERE deleted IS NULL
                  GROUP BY order_no, lot
                  ORDER BY id DESC";
        $data = DbManager::fetchPDOQuery('dto_configurator', $query)['data'] ?? [];

        SharedManager::saveLog('log_dtoconfigurator',"RETURNED | Get All Banfomat History Data Request");
        Journals::saveJournal("RETURNED | Get All Banfomat History Data Request", PAGE_BANFOMAT, BANFOMAT_HISTORY, ACTION_VIEWED, implode(' | ', $_GET), "Banfomat History");

        echo json_encode($data); exit;
    }

    public function deleteBanfomatHistoryRow(): void {

        SharedManager::saveLog('log_dtoconfigurator',"PROCESSING | Remove Banfomat History By Requested Parameters : " . implode(' | ', $_POST));
        Journals::saveJournal("PROCESSING | Remove Banfomat History By Requested Parameters : " . implode(' | ', $_POST), PAGE_BANFOMAT, BANFOMAT_HISTORY, ACTION_PROCESSING, implode(' | ', $_POST), "Banfomat History Delete");

        $orderNo = $_POST['orderNo'];
        $lot = $_POST['lot'];

        $query = "UPDATE banfomat_history SET deleted_by = :delUser, deleted = :deletedAt WHERE order_no = :orderNo AND lot = :lot";
        DbManager::fetchPDOQueryData('dto_configurator', $query, [':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':orderNo' => $orderNo, ':lot' => $lot]);

        SharedManager::saveLog('log_dtoconfigurator',"DELETED | Remove Banfomat History By Requested Parameters : " . implode(' | ', $_POST));
        Journals::saveJournal("DELETED | Remove Banfomat History By Requested Parameters : " . implode(' | ', $_POST), PAGE_BANFOMAT, BANFOMAT_HISTORY, ACTION_DELETED, implode(' | ', $_POST), "Banfomat History Delete");
    }

    public function getBanfomatExcelDataFromHistory() {
        $orderNo = $_GET['orderNo'];
        $lot = $_GET['lot'];

        $query = "SELECT bm.id, bm.material, bh.material_order_quantity as quantity, bm.surface_area, bm.metal, bm.coated_type, bm.coated_part, bm.details, bm.production_location, bm.image_file_name 
                  FROM banfomat_history bh
                  LEFT JOIN banfomat_material_details bm ON bh.banfomat_material_id = bm.id
                  WHERE bh.order_no = :orderNo AND bh.lot = :lot AND deleted IS NULL";

        $banfomatHistoryData = DbManager::fetchPDOQueryData('dto_configurator', $query, [':orderNo' => $orderNo, ':lot' => $lot])['data'] ?? [];

        $tableData = [];
        foreach ($banfomatHistoryData as $rowData) {
            $totalSurfaceArea = round(floatval($rowData['surface_area']) * $rowData['quantity'], 7);

            $tableData[] = [
                'Id'                 => $rowData['id'],
                'OrderListNo'        => $orderNo,
                'Material'           => $rowData['material'] ?? '',
                'Quantity'           => $rowData['quantity'] ?? '',
                'SurfaceArea'        => $rowData['surface_area'] ?? '',
                'TotalSurfaceArea'   => $totalSurfaceArea,
                'Metal'              => $rowData['metal'] ?? '',
                'CoatedType'         => $rowData['coated_type'] ?? '',
                'CoatedPart'         => $rowData['coated_part'] ?? '',
                'Details'            => $rowData['details'] ?? '',
                'ProductionLocation' => $rowData['production_location'] ?? '',
                'ImageFileName'      => $rowData['image_file_name']
            ];
        }

        echo json_encode($tableData); exit;
    }

    public function getMaxAgCoatedType($rows) {
        $coatingCounts = [];

        // Filter and count occurrences of CoatedType containing "Ag"
        foreach ($rows as $row) {
            if (str_starts_with($row['CoatedType'], 'Ag') && $row['CoatedType'] !== 'Ag') {
                $coatingType = $row['CoatedType'];
                if (!isset($coatingCounts[$coatingType])) {
                    $coatingCounts[$coatingType] = 0;
                }
                $coatingCounts[$coatingType]++;
            }
        }

        if (!empty($coatingCounts)) {
            return array_search(max($coatingCounts), $coatingCounts);
        }

        return null;
    }

    public function getTotalSurfaceAreas($rows) {
        $totals = [
            'Tin' => 0.0,
            'Silver' => 0.0,
            'Nickel' => 0.0
        ];

        foreach ($rows as $row) {
            $coatedType = $row['CoatedType'];
            $surfaceArea = floatval($row['TotalSurfaceArea']); // Convert string to float

            if (str_starts_with($coatedType, 'Sn')) {
                $totals['Tin'] += $surfaceArea;
            } elseif (str_starts_with($coatedType, 'Ag')) {
                $totals['Silver'] += $surfaceArea;
            } elseif (str_starts_with($coatedType, 'Ni')) {
                $totals['Nickel'] += $surfaceArea;
            }
        }

        return $totals;
    }

}

$controller = new BanfomatController($_POST);

$response = match ($_GET['action']) {
    'getProjectCopperDetails' => $controller->getProjectCopperDetails(),
    'getBanfomatPool' => $controller->getBanfomatPool(),
    'getMaterialsFromSapBySearch' => $controller->getMaterialsFromSapBySearch(),
    'getProjectBanfomatDetails' => $controller->getProjectBanfomatDetails(),
    'getBanfomatPoolDetailsById' => $controller->getBanfomatPoolDetailsById(),
    'getAllBanfomatHistoryData' => $controller->getAllBanfomatHistoryData(),
    'getBanfomatExcelDataFromHistory' => $controller->getBanfomatExcelDataFromHistory(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};

$response = match ($_POST['action']) {
    'saveMaterialToBanfomatPool' => $controller->saveMaterialToBanfomatPool(),
    'exportBanfomatToExcel' => $controller->exportBanfomatToExcel(),
    'deleteBanfomatPoolRow' => $controller->deleteBanfomatPoolRow(),
    'updateBanfomatPoolRow' => $controller->updateBanfomatPoolRow(),
    'deleteBanfomatHistoryRow' => $controller->deleteBanfomatHistoryRow(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};