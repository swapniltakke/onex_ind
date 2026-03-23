<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/shared/shared.php';

class BaseController
{
    public function getUserInfo(): void{
        SharedManager::checkAuthToModule(24);

        $user = SharedManager::getUser();
        $query = "SELECT Id FROM subauth WHERE ModuleId = 24";
        $appFunctions = array_column(DbManager::fetchPDOQueryData('php_auth', $query)['data'] ?? [], 'Id') ?? [];

        $userFunctionsStr = array_map('strval', $user['Functions']);
        $commonFunctionsArr = array_intersect($appFunctions, $userFunctionsStr);
        $isAdmin = in_array(SharedManager::getUser()["GroupID"], ['2', '7']);

        $data = [
            'Email' => SharedManager::getUser()["Email"],
            'Fullname' =>  SharedManager::getUser()["FullName"],
            'Gid' =>  SharedManager::getUser()["GID"],
            'Functions' => SharedManager::getUser()["Functions"],
            'isAdmin' => $isAdmin
        ];

        echo json_encode($data);
        exit();
    }

    public function searchProject(): void
    {
        $keyword = $_GET['keyword'];

        $query = "SELECT TOP 25 FactoryNumber, ProjectName
                  FROM dbo.OneX_ProjectDetails 
                  WHERE 
                  -- FactoryNumber LIKE '7024%' AND 
                  (FactoryNumber LIKE :keyword OR ProjectName LIKE :keyword)
                  -- AND (Product = '8BT2' OR Product LIKE 'NXAIR%')
                  -- AND SubProduct NOT LIKE '%Comp%'
                  ORDER BY FactoryNumber DESC";

        $data = DbManager::fetchPDOQuery('MTool_INKWA', $query, [':keyword' => "%$keyword%"])['result'];
        echo json_encode($data);
        exit();
    }

    public function getDtoNumberStartsBy(): array {
        $query = "SELECT * FROM rules WHERE rules.key = 'product_dto_names'";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query)['data'][0]['rules'];
        return explode('|', $result);
    }

    public function getNachbauDtoNumbersStartsBy(): array {
        $query = "SELECT * FROM rules WHERE rules.key = 'nachbau_dto_names'";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query)['data'][0]['rules'];
        return explode('|', $result);
    }

    public function getSapMaterialPrefixByMaterialId($materialId) {
        $query = "SELECT sap_material_number FROM materials WHERE id = :id";
        $sapMaterialNumber = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $materialId])['data'][0]['sap_material_number'];

        $prefixes = array('A7E00', 'A7ETKBL', 'A7ET0', 'A7ET', 'A7E');
        $materialPrefix = '';

        foreach ($prefixes as $prefix) {
            if (str_starts_with($sapMaterialNumber, $prefix)) {
                $materialPrefix = $prefix;
                break;
            }
        }

        return $materialPrefix;
    }

    public function getSapMaterialPrefixFrom064($materialNo) {
        $query = "SELECT Material FROM sap_spiridon_064 WHERE Material LIKE :materialNo";
        $sapMaterialNumber = DbManager::fetchPDOQueryData('rpa', $query, [':materialNo' => '%'.$materialNo])['data'][0]['Material'];

        $prefixes = array('A7E00', 'A7ETKBL', 'A7ET0', 'A7ET', 'A7E');
        $materialPrefix = '';

        foreach ($prefixes as $prefix) {
            if (str_starts_with($sapMaterialNumber, $prefix)) {
                $materialPrefix = $prefix;
                break;
            }
        }

        return $materialPrefix;
    }

    public function getFullSapMaterialFrom064($materialNo) {
        $query = "SELECT Material FROM sap_spiridon_064 WHERE Material LIKE :materialNo";
        return DbManager::fetchPDOQueryData('rpa', $query, [':materialNo' => '%'.$materialNo])['data'][0]['Material'];
    }

    public function getMaterialPossibleKmats($workCenterId, $subKmatName):string {

        if (!empty($subKmatName)) {
            $query = "SELECT DISTINCT product_id, parent_kmat, sub_kmat FROM material_kmat_subkmats
                      WHERE work_center_id = :wcId AND (sub_kmat_name IS NULL OR sub_kmat_name = :subKmat)";
            $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':wcId' => $workCenterId, ':subKmat' => $subKmatName])['data'];
        } else {
            $query = "SELECT DISTINCT product_id, parent_kmat, sub_kmat FROM material_kmat_subkmats WHERE work_center_id = :wcId";
            $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':wcId' => $workCenterId])['data'];
        }

        $materialKmats = [];
        foreach ($result as $row) {
            if (!empty($row['sub_kmat'])) {
                $materialKmats[] = $row['parent_kmat'];
                $materialKmats[] = $row['sub_kmat'];
            }
            else
                $materialKmats[] = $row['parent_kmat'];
        }

        return implode('|', array_unique($materialKmats));
    }

    public function formatKukoDtoNumber($dtoNumberKuko): string
    {

        // Remove everything after the first dot, including the dot
        $dtoNumberKuko = preg_replace('/\.\d+/', '', $dtoNumberKuko);



        // Remove ":: KUKO_CON_CST_" from the beginning of the string
        $dtoNumberKuko = preg_replace('/^:: KUKO_CON_CST_/', '', $dtoNumberKuko);

        return rtrim($dtoNumberKuko, '.');
    }

    public function formatDtoNumber($dtoNumber): string
    {
        // Remove everything after the first dot, including the dot
        $dtoNumber = preg_replace('/\.\d+/', '', $dtoNumber);

        // Remove ":: " from the beginning of the string
        $dtoNumber = preg_replace('/^:: /', '', $dtoNumber);

        return rtrim($dtoNumber, '.');
    }

    public function formatDescription($description, $splitLength) {
        if (!$description) {
            return '';
        }

        // Decode HTML entities before processing
        $description = html_entity_decode($description);

        // Split the description by "V:"
        $splitParts = explode('V:', $description);

        if (count($splitParts) < $splitLength) {
            // Return the cleaned-up description if there are fewer than splitLength parts
            return trim(str_replace(['V:', 'Description:'], '', $description));
        }

        // Extract the correct parts (same logic as JS)
        $extractedParts = array_slice($splitParts, 1, $splitLength - 1);

        // Join the extracted parts and clean up "Description:"
        return trim(str_replace('Description:', '', implode(' ', $extractedParts)));
    }

    public function checkMaterialStatusInNachbau($projectNo, $nachbauNo, $tkformMaterial, $accessoryTypicalNumber, $accessoryParentKmat): array
    {
        $nachbauTypicals = '';
        $nachbauPanels = '';
        $nachbauKmats = '';
        $commonKmats = '';

        $query = "SELECT Id FROM nachbau_datas WHERE project_no=:pNo AND nachbau_no=:nNo AND kmat_name LIKE :dtoNumber LIMIT 1";
        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNumber' => '%' . $tkformMaterial['dto_number'] . '%'])['data'] ?? [];

        $errorMessageId = 0;
        if (empty($result))
            $errorMessageId = 2; //DTO Numarası Aktarımda Bulunamadı.
        else if (empty($tkformMaterial['tk_kmats']))
            $errorMessageId = 4; //KMAT Numarası boş veya güncellenmesi gerekli.
        else {
            if ($tkformMaterial['operation'] === 'add') {
                // EKLENEN LİSTE İSE;

                if ($tkformMaterial['type'] === 'Accessories') {
                    $nachbauTypicals = $accessoryTypicalNumber;
                    $commonKmats = $accessoryParentKmat;
                } else {
                    $parentKmatsObj = $this->getParentKmatsOfNachbauByMaterialAndWc($tkformMaterial['material_added_number'], $tkformMaterial['tk_work_center_id']);

                    // Step 1: Explode the string into an array
                    $tkKmatsArray = explode('|', $tkformMaterial['tk_kmats']);

                    // Step 2: Prefix '00' to each element
                    $tkKmatsArrayWithZeros= array_map(function($kmat) {
                        return '00' . $kmat;
                    }, $tkKmatsArray);

                    // Step 3: Implode the array back into a string with commas, with each element wrapped in single quotes
                    $tkKmatsList = implode(',', array_map(function($kmat) {
                        return "'$kmat'";
                    }, $tkKmatsArrayWithZeros));

                    $query = "SELECT DISTINCT kmat FROM nachbau_datas 
                          WHERE project_no = :pNo AND nachbau_no = :nNo
                          AND kmat IN ($tkKmatsList)";
                    $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'] ?? [];

                    //Nachbaudan gelen 003003230 tarzındaki parent_kmat değerlerinin ilk iki 0'ını at.
                    $nachbauParentKmats = array_unique(array_map(function ($value) { return substr($value, 2); }, array_column($result, 'kmat')));
                    $nachbauKmats = implode('|', $nachbauParentKmats);

                    //Nachbau'dan gelen malzemenin istasyon kmat'ı ile, sisteme girilen istasyon kmat'ı aynı ise commonKmats arrayine atacak.
                    $commonKmats = array_unique(array_intersect($tkKmatsArray, $nachbauParentKmats));
                    if ($parentKmatsObj['has_sub_kmat'] === '1' && count($commonKmats) !== 1) {
                        $commonKmats = array_intersect($commonKmats, $parentKmatsObj['parent_kmats']);

                        if (count($commonKmats) > 1) {
                            // Siparişte bu kmat alt kırılımlı geldiyse count'ı 1 den büyüktür. $commonKmats = [30035500, 30035501]
                            $commonKmats = $this->getMaterialSubKmatAndEliminateParentKmat($commonKmats);
                        }
                    }
                    $commonKmats = implode('|', $commonKmats);

                    if (empty($commonKmats)) {
                        $errorMessageId = 3; // Eklenen malzemenin KMAT Numarası aktarımda yok
                    } else if ($tkformMaterial['material_added_work_center_id'] !== $tkformMaterial['tk_work_center_id']) {
                        $errorMessageId = 6; // Eklenen malzeme ile TK'nın KMAT'ı eşleşmiyor
                    }

                    $query = "SELECT typical_no, ortz_kz, parent_kmat FROM nachbau_datas 
                          WHERE project_no=:pNo AND nachbau_no=:nNo AND kmat_name LIKE :kmatNo AND ortz_kz <>''";
                    $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':kmatNo' => '%' . $tkformMaterial['dto_number'] . '%'])['data'] ?? [];

                    // Get Unique Nachbau Typicals of Nachbau Data and implode them
                    $nachbauTypicals = array_unique(array_column($result, 'typical_no'));
                    sort($nachbauTypicals);
                    $nachbauTypicals = implode('|', $nachbauTypicals);

                    // Get Unique Nachbau Panels of Nachbau Data by effectiveness and implode them
                    if ($tkformMaterial['effective'] === '1') {
                        $nachbauPanels = $this->getAllOrtzKzsOfProject($projectNo, $nachbauNo); // Eklenen listede projedeki bütün panolara etkiyebilir.
                        sort($nachbauPanels);
                        $nachbauPanels = implode('|', $nachbauPanels);
                    } else {
                        $nachbauPanels = array_unique(array_column($result, 'ortz_kz'));
                        $nachbauPanels = array_filter($nachbauPanels, function ($value) {
                            return $value !== '';
                        });
                        sort($nachbauPanels);
                        $nachbauPanels = implode('|', $nachbauPanels);
                    }
                }
            } else {
                //DEĞİŞEN VEYA SİLİNEN LİSTE İSE
                if ($tkformMaterial['material_deleted_starts_by'] === ':: VTH:' || $tkformMaterial['material_deleted_starts_by'] === ':: CTH:') {
                    // Replace 'x' with '_' to match any single character
                    $searchCableData = $tkformMaterial['material_deleted_starts_by'] . $tkformMaterial['material_deleted_number'];
                    $searchPattern = str_ireplace(['x', '?'], '_', $searchCableData);

                    $query = "SELECT typical_no, ortz_kz, parent_kmat FROM nachbau_datas WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat_name LIKE :cableCode";
                    $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':cableCode' => '%' . $searchPattern . '%'])['data'] ?? [];
                } else {
                    $query = "SELECT typical_no, ortz_kz, parent_kmat FROM nachbau_datas 
                                WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat LIKE :materialNo";
                    $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':materialNo' => '%' . $tkformMaterial['material_deleted_number'] . '%'])['data'] ?? [];
                }

                //Nachbaudan gelen 003003230 tarzındaki parent_kmat değerlerinin ilk iki 0'ını at.
                $nachbauParentKmats = array_unique(array_map(function ($value) { return substr($value, 2); }, array_column($result, 'parent_kmat')));
                $nachbauKmats = implode('|', array_unique($nachbauParentKmats));

                if ($tkformMaterial['type'] === 'Accessories' && in_array($accessoryParentKmat, $nachbauParentKmats)) {
                    $nachbauTypicals = $accessoryTypicalNumber;
                    $commonKmats = $accessoryParentKmat;
                } else {
                    //Nachbau'dan gelen malzemenin istasyon kmat'ı ile, site girilen istasyon kmat'ı aynıysa commonKmats arrayine atacak.
                    $commonKmats = array_unique(array_intersect(explode('|', $tkformMaterial['tk_kmats']), $nachbauParentKmats));
                    $commonKmats = implode('|', $commonKmats);

                    if (empty($result)) {
                        $errorMessageId = 5; // Silinecek malzeme no aktarımda yok
                    } else if (empty($commonKmats)) {
                        $errorMessageId = 1; // Nachbau KMAT'ı ile TK KMAT'ı eşleşmiyor
                    } else if ($tkformMaterial['operation'] === 'delete' && ($tkformMaterial['material_deleted_work_center_id'] !== $tkformMaterial['tk_work_center_id'])) {
                        $errorMessageId = 6; // Silinen malzemenin KMAT değeri ile TK KMAT'ı farklı
                    } else if ($tkformMaterial['operation'] === 'replace' && ($tkformMaterial['material_deleted_work_center_id'] !== $tkformMaterial['material_added_work_center_id'])) {
                        $errorMessageId = 7; // Değişen Listelerin KMAT değerleri farklı
                    }

                    if ($tkformMaterial['material_deleted_starts_by'] === ':: VTH:' || $tkformMaterial['material_deleted_starts_by'] === ':: CTH:') {
                        //cth veya vth ise DTOnun girildiği tipiklere bakmaya gerek yok.
                        $nachbauTypicals = implode('|', array_unique(array_column($result, 'typical_no')));
                        $nachbauPanels = implode('|', array_unique(array_column($result, 'ortz_kz')));
                    }
                    else if ($tkformMaterial['type'] === 'Accessories') {
                        $nachbauTypicals = $accessoryTypicalNumber;
                        $commonKmats = $accessoryParentKmat;
                    } else {
                        // Get Unique Nachbau Panels of Nachbau Data by effectiveness and implode them
                        if ($tkformMaterial['effective'] === '1') {
                            $nachbauPanels = $this->getAllOrtzKzsOfProject($projectNo, $nachbauNo);
                            sort($nachbauPanels);
                            $nachbauPanels = implode('|', $nachbauPanels);
                        } else {
                            $nachbauPanels = array_unique(array_column($result, 'ortz_kz'));
                            $nachbauPanels = array_filter($nachbauPanels, function ($value) {
                                return $value !== '';
                            });
                            sort($nachbauPanels);
                            $nachbauPanels = implode('|', $nachbauPanels);
                        }

                        $query1 = "SELECT DISTINCT typical_no FROM nachbau_datas 
                           WHERE project_no=:pNo AND nachbau_no=:nNo AND kmat LIKE :kmatNo AND ortz_kz <>''";
                        $result1 = DbManager::fetchPDOQueryData('planning', $query1, [
                            ':pNo' => $projectNo,
                            ':nNo' => $nachbauNo,
                            ':kmatNo' => '%' . $tkformMaterial['material_deleted_number'] . '%'
                        ])['data'] ?? [];
                        $materialDeletedTypicals = array_column($result1, 'typical_no');

                        $query2 = "SELECT DISTINCT typical_no FROM nachbau_datas 
                           WHERE project_no=:pNo AND nachbau_no=:nNo AND kmat_name LIKE :kmatName AND ortz_kz <>''";
                        $result2 = DbManager::fetchPDOQueryData('planning', $query2, [
                            ':pNo' => $projectNo,
                            ':nNo' => $nachbauNo,
                            ':kmatName' => '%' . $tkformMaterial['dto_number'] . '%'
                        ])['data'] ?? [];
                        $dtoTypicals = array_column($result2, 'typical_no');

                        $nachbauTypicals = array_intersect($materialDeletedTypicals, $dtoTypicals);
                        if (!empty($nachbauTypicals)) {
                            sort($nachbauTypicals);
                            $nachbauTypicals = implode('|', $nachbauTypicals);
                        }
                        else {
                            $nachbauTypicals = 'Typical of DTO entered is different from typical of deleted material list in nachbau.';
                            $nachbauPanels = $this->getOrtzKzsOfProjectByMaterial($projectNo, $nachbauNo, $tkformMaterial['material_deleted_number']);
                            sort($nachbauPanels);
                            $nachbauPanels = implode('|', $nachbauPanels);
                        }
                    }
                }
            }
        }

        return ['nachbau_typicals' => $nachbauTypicals, 'nachbau_panels' => $nachbauPanels, 'error_message_id' => $errorMessageId,
            'nachbau_kmats' => $nachbauKmats, 'common_kmats' => $commonKmats];
    }

    public function getParentKmatsOfNachbauByMaterialAndWc($materialNo, $workcenterId): array {
        $query = "SELECT DISTINCT parent_kmat, sub_kmat, has_sub_kmat FROM material_kmat_subkmats WHERE material_number = :mNo AND work_center_id = :wcId";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':mNo' => $materialNo, ':wcId' => $workcenterId])['data'] ?? [];

        $hasSubKmat = $result[0]['has_sub_kmat'];
        if ($hasSubKmat === '1') {
            $parentKmats = array_unique(array_column($result, 'sub_kmat'));
        }
        else {
            $parentKmats = array_unique(array_column($result, 'parent_kmat'));
        }

        return ['parent_kmats' => $parentKmats, 'has_sub_kmat' => $hasSubKmat];
    }

    public function getMaterialSubKmatAndEliminateParentKmat($commonKmats) {
        $bindings = [
            // One of them is sub kmat
            ':commonKmat1' => $commonKmats[0],
            ':commonKmat2' => $commonKmats[1]
        ];

        $query = "SELECT sub_kmat FROM sub_kmats WHERE sub_kmat IN (:commonKmat1, :commonKmat2) GROUP BY sub_kmat";
        $result = DbManager::fetchPDOQueryData('planning', $query, $bindings)['data'];

        if (!empty($result)) {
            $subKmat = $result[0]['sub_kmat'];
            return [$subKmat];
        }

        return $commonKmats;
    }


    public function getAllOrtzKzsOfProject($projectNo, $nachbauNo): array
    {
        $query = "SELECT ortz_kz FROM nachbau_datas 
                  WHERE project_no=:pNo AND nachbau_no=:nNo AND ortz_kz != '' 
                  GROUP BY ortz_kz
                  ORDER BY ortz_kz";

        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'];
        return array_column($result, 'ortz_kz');
    }

    public function getOrtzKzsOfProjectByMaterial($projectNo, $nachbauNo, $materialNumber): array
    {
        $query = "SELECT ortz_kz FROM nachbau_datas 
                  WHERE project_no=:pNo AND nachbau_no=:nNo AND kmat LIKE :kmat AND ortz_kz != ''
                  GROUP BY ortz_kz
                  ORDER BY ortz_kz";

        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':kmat' => "%" . $materialNumber . "%"])['data'];
        return array_column($result, 'ortz_kz');
    }

    public function getOrtzKzsOfProjectTypical($projectNo, $nachbauNo, $typicalNo): array
    {
        $query = "SELECT ortz_kz,panel_no FROM nachbau_datas WHERE project_no = :pNo AND nachbau_no = :nNo AND typical_no = :typicalNo AND ortz_kz != '' GROUP BY ortz_kz";
        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':typicalNo' => $typicalNo])['data'];
        return array_column($result, 'ortz_kz');
    }

    public function updateNachbauPanelsOfProjectWork($tkformMaterial, $projectWorkId, $effective): void
    {
        $query = "SELECT project_number, nachbau_number FROM project_work_view WHERE id=:id";
        $projectWork = DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $projectWorkId])['data'][0];

        if($tkformMaterial['operation'] === 'add') {
            // Get Unique Nachbau Panels of Nachbau Data by effectiveness and implode them
            if ($effective === 1)
            {
                // Eklenen listede effective 1 ise, projedeki bütün panolara etkiyebilir.
                $nachbauPanels = $this->getAllOrtzKzsOfProject($projectWork['project_number'], $projectWork['nachbau_number']);
            }
            else
            {
                $query = "SELECT typical_no, ortz_kz, parent_kmat FROM nachbau_datas
                          WHERE project_no=:pNo AND nachbau_no=:nNo AND kmat_name LIKE :kmatNo AND ortz_kz <>''";
                $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectWork['project_number'], ':nNo' => $projectWork['nachbau_number'],
                    ':kmatNo' => '%' . $tkformMaterial['dto_number'] . '%'])['data'] ?? [];

                $nachbauPanels = array_unique(array_column($result, 'ortz_kz'));
                $nachbauPanels = array_filter($nachbauPanels, function($value) { return $value !== ''; });
            }
        }
        else {
            if ($effective === 1)
            {
                // Silinen listede kmata ait yan panolara etkiyebilir.
                $nachbauPanels = $this->getAllOrtzKzsOfProject($projectWork['project_number'], $projectWork['nachbau_number']);
//                $nachbauPanels = $this->getOrtzKzsOfProjectByMaterial($projectWork['project_number'], $projectWork['nachbau_number'], $tkformMaterial['material_deleted_number']);
            }
            else
            {
                $query = "SELECT typical_no, ortz_kz, parent_kmat FROM nachbau_datas
                          WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat LIKE :materialNo";
                $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo'=>$projectWork['project_number'],':nNo'=>$projectWork['nachbau_number'],
                    ':materialNo'=>'%' . $tkformMaterial['material_deleted_number'] . '%'])['data'] ?? [];

                $nachbauPanels = array_unique(array_column($result, 'ortz_kz'));
                $nachbauPanels = array_filter($nachbauPanels, function($value) { return $value !== ''; });
            }
        }

        $nachbauPanels = implode('|', $nachbauPanels);

        $query = "UPDATE project_works SET nachbau_panels = :nachbau_panels WHERE id = :id";
        DbManager::fetchPDOQueryData('dto_configurator', $query,[':nachbau_panels' => $nachbauPanels, ':id' => $projectWorkId])['data'];
    }

    public function getAccessoryTypicalOfProject($projectNo) {
        //MTool'dan tipiği çek
        $query = "SELECT TypicalCode FROM dbo.OneX_SapPosData
                    WHERE LocationCode IS NULL
                    AND TypicalCode IS NOT NULL
                    AND	ProjectNo IN(:projectNo)";
        $result = DbManager::fetchPDOQueryData('MTool_INKWA', $query, [':projectNo' => $projectNo])['data'] ?? [];

        //MTool'da tipik boş ise planning.nachbau_datas'dan çek.
        if (empty($result))
        {
            $query = "SELECT typical_no as TypicalCode FROM nachbau_datas WHERE project_no = :pNo GROUP BY typical_no";
            $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo])['data'] ?? [];
        }

        return $result[0]['TypicalCode'];
    }

    public function getAllDtoDataByNachbau($projectNo, $nachbauNo): array {
        // ✅ CORRECT - fetch rules first from dto_configurator, then query planning
        $rulesQuery = "SELECT d.rules FROM rules d WHERE d.key='nachbau_dto_names'";
        $rulesResult = DbManager::fetchPDOQueryData('dto_configurator', $rulesQuery)['data'][0] ?? [];
        $nachbauDtoPattern = $rulesResult['rules'] ?? '';

        $query = "SELECT nachbau_no, typical_no, ortz_kz, panel_no, kmat_name as dto_number, description
                  FROM nachbau_datas 
                  WHERE project_no=:pNo 
                  AND nachbau_no=:nNo
                  AND kmat_name REGEXP :pattern
                  GROUP BY nachbau_no, typical_no, ortz_kz, panel_no, kmat_name
                  ORDER BY nachbau_no DESC";

        return DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':pattern' => $nachbauDtoPattern])['data'] ?? [];
    }

    public function getProductIdOfProject($projectNo): string {
        $query = "SELECT Product, SubProduct FROM dbo.OneX_ProjectDetails WHERE FactoryNumber = :projectNo";
        $projectDetail = DbManager::fetchPDOQuery('MTool_INKWA', $query, [':projectNo'=>$projectNo])['result'][0] ?? [];

        $mToolProductName = !empty($projectDetail['Product']) ? $projectDetail['Product']
                            : (!empty($projectDetail['SubProduct']) ? $projectDetail['SubProduct']
                            : '');

        if ($mToolProductName === 'NXAIR 1C') {
            $productId = 1;
        } elseif ($mToolProductName === 'NXAIR 50kA') {
            $productId = 2;
        } elseif ($mToolProductName === 'NXAIR H') {
            $productId = 3;
        } elseif ($mToolProductName === 'NXAIR World') {
            $productId = 4;
        } elseif ($mToolProductName === 'NXAIR') {
            $productId = 5;
        } else {
            $productId = null;
        }

        return $productId;
    }

    public function getAccessoryDtosOfProject($projectNo, $nachbauNo, $accessoryTypicalCode): array {
        $accessory_dtos = [];
        $dtos_with_descriptions = $this->getDtos($projectNo, $nachbauNo);
        $typicalNumber = $this->getDtosWithTypicals($projectNo, $nachbauNo, 'typical_no', $accessoryTypicalCode); // RETURNS ARRAY OF [TYPICAL, DTOS[]] -> Example: Array["=LZ00"] = ["NX18", "NX130", ...]
        $accessory_dtos_with_kukos = $typicalNumber[$accessoryTypicalCode];


        foreach ($accessory_dtos_with_kukos as $dto1) {
            $currentDtoNumber = $this->formatKukoDtoNumber($dto1);
            $count = 0;

            foreach ($dtos_with_descriptions as $dto2)
            {
                if (str_contains($dto2['DtoNumber'], $currentDtoNumber))
                    $count++;
            }

            // If the DTO is only exist once and its typical is .00, that means its accessory not typical.
            if ($count === 1)
                $accessory_dtos[] = $currentDtoNumber;
        }

        return $accessory_dtos;
    }

    public function getDtos($projectNo, $nachbauNo): array {
        // ✅ CORRECT - fetch rules first from dto_configurator, then query planning
        $rulesQuery = "SELECT d.rules FROM rules d WHERE d.key='nachbau_dto_names'";
        $rulesResult = DbManager::fetchPDOQueryData('dto_configurator', $rulesQuery)['data'][0] ?? [];
        $nachbauDtoPattern = $rulesResult['rules'] ?? '';

        $query = "SELECT DISTINCT kmat_name as DtoNumber, description 
                   FROM nachbau_datas 
                   WHERE project_no=:pNo 
                     AND nachbau_no=:nNo 
                     AND kmat_name LIKE '%::%'
                     AND kmat_name REGEXP :pattern";

        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo'=>$nachbauNo, ':pattern' => $nachbauDtoPattern])['data'] ?? [];
        $dtos_with_descriptions = array_combine(array_column($result, 'DtoNumber'), $result) ?? [];
        sort($dtos_with_descriptions);
        return $dtos_with_descriptions;
    }

    public function getDtosWithTypicals($projectNo, $nachbauNo, $type, $accessoryTypicalNumber): array {
        // ✅ CORRECT - fetch rules first from dto_configurator, then query planning
        $rulesQuery = "SELECT d.rules FROM rules d WHERE d.key='nachbau_dto_names'";
        $rulesResult = DbManager::fetchPDOQueryData('dto_configurator', $rulesQuery)['data'][0] ?? [];
        $nachbauDtoPattern = $rulesResult['rules'] ?? '';

        $query = "SELECT $type as type_num, GROUP_CONCAT(DISTINCT t1.kmat_name SEPARATOR '||') as kmat_concat 
                  FROM nachbau_datas AS t1 
                  WHERE t1.project_no=:pNo AND t1.nachbau_no=:nNo
                  AND t1.kmat_name REGEXP :pattern
                  GROUP BY $type";

        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':pattern' => $nachbauDtoPattern])['data'] ?? [];

        $data = [];

        foreach($result as $row){
            $data[$row['type_num']] = explode('||', $row['kmat_concat']);
        }

        // If the accessory typical number exists, remove duplicates from other typical numbers
        if (isset($data[$accessoryTypicalNumber])) {
            foreach ($data[$accessoryTypicalNumber] as $index => $dto) {
                // Check if the same DTO exists in other typicals
                foreach ($data as $typical => $dtos) {
                    if ($typical !== $accessoryTypicalNumber && in_array($dto, $dtos)) {
                        // Remove the DTO from the accessory typical number if it's found in another typical
                        unset($data[$accessoryTypicalNumber][$index]);
                        break; // No need to check further, move to next DTO
                    }
                }
            }

            $data[$accessoryTypicalNumber] = array_values($data[$accessoryTypicalNumber]);
        }

        return $data;
    }


    public function getTypicals($projectNo, $nachbauNo): array {
        $query = "SELECT typical_no, panel_no
                  FROM nachbau_datas
                  WHERE project_no=:pNo AND nachbau_no=:nNo GROUP BY CONCAT(typical_no, panel_no)";
        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'] ?? [];
        $typicals = [];
        foreach ($result as $item) {
            $typicals[$item['typical_no']][$item['panel_no']] = $item['panel_no'];
        }
        return $typicals;
    }

    public function getTypicalNumberByOrtzKz($projectNo, $nachbauNo, $ortzKz)
    {
        $query = "SELECT typical_no 
                  FROM nachbau_datas 
                  WHERE project_no=:pNo AND nachbau_no=:nNo AND ortz_kz=:ortzKz
                  GROUP BY typical_no";

        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':ortzKz' => $ortzKz])['data'] ?? [];

        return $result[0]['typical_no'];
    }

    public function getNachbauDescriptionByDtoNumber($dtoNumber, $projectNo, $nachbauNo, $isReplaced)
    {
        if ($isReplaced === false)
            $dtoNumber = $this->formatDtoNumber($dtoNumber);

        // ✅ CORRECT - fetch rules first from dto_configurator, then query planning
        $rulesQuery = "SELECT d.rules FROM rules d WHERE d.key='nachbau_dto_names'";
        $rulesResult = DbManager::fetchPDOQueryData('dto_configurator', $rulesQuery)['data'][0] ?? [];
        $nachbauDtoPattern = $rulesResult['rules'] ?? '';

        $query = "SELECT kmat_name, description 
                  FROM nachbau_datas
                  WHERE project_no = :pNo
                  AND nachbau_no = :nNo
                  AND kmat_name REGEXP :pattern
                  AND kmat_name LIKE :dtoNumber
                  GROUP BY kmat_name
                  ORDER BY LENGTH(kmat_name) DESC";

        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNumber' => "%" . $dtoNumber . "%", ':pattern' => $nachbauDtoPattern])['data'] ?? [];

        if(!empty($result)) {
            foreach ($result as $row) {
                if (!empty($row["description"]))
                    return $row["description"];
            }
        }

        //If nachbau does not have a description, then return tk description
        $query = "SELECT description FROM tkforms WHERE dto_number = :dtoNumber AND deleted IS NULL";
        $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':dtoNumber' => $dtoNumber])['data'] ?? [];
        return $result[0]["description"] !== null ? $result[0]["description"] : '';
    }

    public function updateMaterialStatusOfProjectWork($projectWorksResult, $workCenterId, $accessoryTypicalNumber, $accessoryParentKmat): void {
        $projectWorkIds = array_column($projectWorksResult, 'id');

        // 4. Project Worklerin KMAT değerlerini yeniden kontrol edip,  common_kmats, tk_kmats ve error_message'ı güncellemek gerekiyor.
        foreach ($projectWorksResult as $projectWork) {
            $errorMessageId = 0;
            $commonKmats = '';
            $nachbauKmats = '';
            $nachbauTypicals = '';

            if (!$accessoryParentKmat && !$accessoryTypicalNumber) {
                $accessoryKmatObj = $this->getAccessoryKmatObjectOfProject($projectWork['project_number']);
                $accessoryParentKmat = $accessoryKmatObj['kmat'];
                $accessoryTypicalNumber = $accessoryKmatObj['typical_no'];
            }

            if ($projectWork['operation'] === 'add')
            {
                // EKLENEN LİSTE İSE;
                $parentKmatsObj = $this->getParentKmatsOfNachbauByMaterialAndWc($projectWork['material_added_number'], $workCenterId);

                // Step 1: Explode the string into an array
                $tkKmatsArray = explode('|', $projectWork['tk_kmats']);
                // Step 2: Prefix '00' to each element
                $tkKmatsArrayWithZeros = array_map(function($kmat) { return '00' . $kmat; }, $tkKmatsArray);
                // Step 3: Implode the array back into a string with commas, with each element wrapped in single quotes
                $tkKmatsList = implode(',', array_map(function($kmat) { return "'$kmat'"; }, $tkKmatsArrayWithZeros));

                $query = "SELECT kmat FROM nachbau_datas
                          WHERE project_no = :pNo AND nachbau_no = :nNo
                          AND kmat IN ($tkKmatsList)
                          GROUP BY parent_kmat";
                $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectWork['project_number'], ':nNo' => $projectWork['nachbau_number']])['data'] ?? [];

                //Nachbaudan gelen 003003230 tarzındaki parent_kmat değerlerinin ilk iki 0'ını at.
                $nachbauParentKmats = array_unique(array_map(function ($value) { return substr($value, 2); }, array_column($result, 'kmat')));
                $nachbauKmats = implode('|', $nachbauParentKmats);

                //Nachbau'dan gelen malzemenin istasyon kmat'ı ile, sisteme girilen istasyon kmat'ı aynı ise commonKmats arrayine atacak.
                $commonKmats = array_unique(array_intersect($tkKmatsArray, $nachbauParentKmats));
                if ($parentKmatsObj['has_sub_kmat'] === '1' && count($commonKmats) !== 1){
                    $commonKmats = array_intersect($commonKmats, $parentKmatsObj['parent_kmats']);

                    if (count($commonKmats) > 1) {
                        // Siparişte bu kmat alt kırılımlı geldiyse count'ı 1 den büyüktür. $commonKmats = [30035500, 30035501]
                        $commonKmats = $this->getMaterialSubKmatAndEliminateParentKmat($commonKmats);
                    }
                }

                $commonKmats = implode('|', $commonKmats);

                if (empty($commonKmats)) {
                    $errorMessageId = 3; // Eklenen malzemenin KMAT Numarası aktarımda yok
                }

                $query = "SELECT typical_no, ortz_kz, parent_kmat FROM nachbau_datas
                          WHERE project_no=:pNo AND nachbau_no=:nNo AND kmat_name LIKE :kmatNo AND ortz_kz <> ''";
                $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo'=>$projectWork['project_number'],':nNo'=>$projectWork['nachbau_number'],':kmatNo'=>'%' . $projectWork['dto_number'] . '%'])['data'] ?? [];

                // Get Unique Nachbau Typicals of Nachbau Data and implode them
                $nachbauTypicals = array_unique(array_column($result, 'typical_no'));
                sort($nachbauTypicals);
                $nachbauTypicals = implode('|', $nachbauTypicals);

                // Get Unique Nachbau Panels of Nachbau Data by effectiveness and implode them
                if (intval($projectWork['effective']) === 1)
                {
                    // Eklenen listede projedeki bütün panolara etkiyebilir.
                    $nachbauPanels = $this->getAllOrtzKzsOfProject($projectWork['project_number'], $projectWork['nachbau_number']);
                    sort($nachbauPanels);
                    $nachbauPanels = implode('|', $nachbauPanels);
                }
                else
                {
                    $nachbauPanels = array_unique(array_column($result, 'ortz_kz'));
                    $nachbauPanels = array_filter($nachbauPanels, function($value) { return $value !== ''; });
                    sort($nachbauPanels);
                    $nachbauPanels = implode('|', $nachbauPanels);
                }
            }
            else {
                //DEĞİŞEN VEYA SİLİNEN LİSTE İSE
                if ($projectWork['material_deleted_starts_by'] === ':: VTH:' || $projectWork['material_deleted_starts_by'] === ':: CTH:') {
                    // Replace 'x' with '_' to match any single character
                    $searchCableData = $projectWork['material_deleted_starts_by'] . $projectWork['material_deleted_number'];
                    $searchPattern = str_ireplace(['x', '?'], '_', $searchCableData);

                    $query = "SELECT typical_no, ortz_kz, parent_kmat FROM nachbau_datas 
                                WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat_name LIKE :cableCode";
                    $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo'=>$projectWork['project_number'],':nNo'=>$projectWork['nachbau_number'],':cableCode'=>'%' . $searchPattern . '%'])['data'] ?? [];
                } else {
                    $query = "SELECT typical_no, ortz_kz, parent_kmat FROM nachbau_datas
                          WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat LIKE :materialNo";

                    $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo'=>$projectWork['project_number'],':nNo'=>$projectWork['nachbau_number'],':materialNo'=>'%' . $projectWork['material_deleted_number'] . '%'])['data'] ?? [];
                }

                //Nachbaudan gelen 003003230 tarzındaki parent_kmat değerlerinin ilk iki 0'ını at.
                $nachbauParentKmats = array_unique(array_map(function ($value) { return substr($value, 2); }, array_column($result, 'parent_kmat')));
                $nachbauKmats = implode('|', $nachbauParentKmats);

                if ($projectWork['type'] === 'Accessories' && in_array($accessoryParentKmat, $nachbauParentKmats)) {
                    $nachbauTypicals = $accessoryTypicalNumber;
                    $nachbauPanels = '';
                    $commonKmats = $accessoryParentKmat;
                } else {
                    //Nachbau'dan gelen malzemenin istasyon kmat'ı ile, site girilen istasyon kmat'ı aynıysa commonKmats arrayine atacak.
                    $commonKmats = array_unique(array_intersect(explode('|', $projectWork['tk_kmats']), $nachbauParentKmats));
                    $commonKmats = implode('|', $commonKmats);

                    if (empty($result)) {
                        $errorMessageId = 5; // Silinecek malzeme no aktarımda yok
                    } else if (empty($commonKmats)) {
                        $errorMessageId = 1; // Nachbau KMAT'ı ile TK KMAT'ı eşleşmiyor
                    } else if ($projectWork['operation'] === 'delete' && ($projectWork['material_deleted_work_center_id'] !== $projectWork['tk_work_center_id'])) {
                        $errorMessageId = 6; // Silinen malzemenin KMAT değeri ile TK KMAT'ı farklı
                    } else if ($projectWork['operation'] === 'replace' && ($projectWork['material_deleted_work_center_id'] !== $projectWork['material_added_work_center_id'])) {
                        $errorMessageId = 7; // Değişen Listelerin KMAT değerleri farklı
                    }

                    if ($projectWork['material_deleted_starts_by'] === ':: VTH:' || $projectWork['material_deleted_starts_by'] === ':: CTH:') {
                        //cth veya vth ise DTOnun girildiği tipiklere bakmaya gerek yok.
                        $nachbauTypicals = implode('|', array_unique(array_column($result, 'typical_no')));
                        $nachbauPanels = implode('|', array_unique(array_column($result, 'ortz_kz')));
                    } else {
                        if ($projectWork['effective'] === '1') {
                            // $nachbauPanels = $this->getOrtzKzsOfProjectByMaterial($projectNo, $nachbauNo, $tkformMaterial['material_deleted_number']); // Silinen listede kmata ait yan panolara etkiyebilir.
                            $nachbauPanels = $this->getAllOrtzKzsOfProject($projectWork['project_number'], $projectWork['nachbau_number']);
                            sort($nachbauPanels);
                            $nachbauPanels = implode('|', $nachbauPanels);
                        } else {
                            $nachbauPanels = array_unique(array_column($result, 'ortz_kz'));
                            $nachbauPanels = array_filter($nachbauPanels, function($value) { return $value !== ''; });
                            sort($nachbauPanels);
                            $nachbauPanels = implode('|', $nachbauPanels);
                        }

                        $query1 = "SELECT DISTINCT typical_no FROM nachbau_datas 
                           WHERE project_no=:pNo AND nachbau_no=:nNo AND kmat LIKE :kmatNo AND ortz_kz <>''";
                        $result1 = DbManager::fetchPDOQueryData('planning', $query1, [
                            ':pNo' => $projectWork['project_number'],
                            ':nNo' => $projectWork['nachbau_number'],
                            ':kmatNo' => '%' . $projectWork['material_deleted_number'] . '%'
                        ])['data'] ?? [];
                        $materialDeletedTypicals = array_column($result1, 'typical_no');

                        $query2 = "SELECT DISTINCT typical_no FROM nachbau_datas 
                           WHERE project_no=:pNo AND nachbau_no=:nNo AND kmat_name LIKE :kmatName AND ortz_kz <>''";
                        $result2 = DbManager::fetchPDOQueryData('planning', $query2, [
                            ':pNo' => $projectWork['project_number'],
                            ':nNo' => $projectWork['nachbau_number'],
                            ':kmatName' => '%' . $projectWork['dto_number'] . '%'
                        ])['data'] ?? [];
                        $dtoTypicals = array_column($result2, 'typical_no');

                        $nachbauTypicals = array_intersect($materialDeletedTypicals, $dtoTypicals);
                        if (!empty($nachbauTypicals)) {
                            sort($nachbauTypicals);
                            $nachbauTypicals = implode('|', $nachbauTypicals);
                        }
                        else {
                            $nachbauTypicals = 'Typical of DTO entered is different from typical of deleted material list in nachbau.';
                            // $errorMessageId = 10;
                            $nachbauPanels = $this->getOrtzKzsOfProjectByMaterial($projectWork['project_number'], $projectWork['nachbau_number'], $projectWork['material_deleted_number']);
                            sort($nachbauPanels);
                            $nachbauPanels = implode('|', $nachbauPanels);
                        }
                    }
                }
            }

            // Create a parameters array
            $params = [
                ':wc_id' => $workCenterId,
                ':uby' => SharedManager::$fullname,
                ':err_msg_id' => $errorMessageId,
                ':tk_kmats' => $projectWork['tk_kmats'],
                ':common_kmats' => $commonKmats,
                ':nachbau_kmats' => $nachbauKmats,
                ':nachbau_typicals' => $nachbauTypicals ?? '',
                ':nachbau_panels' => $nachbauPanels ?? '',
                ':id' => $projectWork['id']
            ];

            // 5. İstasyonu değiştireceğimiz için çalışma ekranında diğer sütunlarında güncellenmesi gerekiyor.
            $query = "UPDATE project_works SET work_center_id = :wc_id, last_updated_by=:uby, release_items='', accessory_release_items='', release_status='initial', error_message_id=:err_msg_id,
                      tk_kmats = :tk_kmats, common_kmats = :common_kmats, nachbau_kmats = :nachbau_kmats, nachbau_typicals = :nachbau_typicals, nachbau_panels = :nachbau_panels
                      WHERE id = :id";

            DbManager::fetchPDOQueryData('dto_configurator', $query, $params);
        }

        // 6. Eğer BOM'a kaydedilmişse, malzeme BOM'dan çıkarılmalı çünkü önceki istasyon değeri yanlış.
        $query = "UPDATE bom_change SET active = 0 WHERE project_work_id IN (:ids)";
        DbManager::fetchPDOQuery('dto_configurator', $query, [':ids' => $projectWorkIds]);
    }

    //MALZEMENİN AKTARIMDAKİ BİR ÜST KMATI
    public function getParentKmatsOfNachbauByMaterial($projectNo, $nachbauNo, $materialNo) {
        $query = "SELECT parent_kmat FROM nachbau_datas 
                  WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat LIKE :materialNo
                  GROUP BY parent_kmat";

        return DbManager::fetchPDOQuery('planning', $query, [':pNo' => $projectNo,':nNo' => $nachbauNo,':materialNo' =>'%'.$materialNo.'%'])['data'][0]['parent_kmat'];
    }

    public function getParentAndSubKmatByKmat($parentKmatInNachbau) {
        $query = "SELECT parent_kmat, sub_kmat FROM material_kmat_subkmats WHERE sub_kmat = :kmat";
        $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':kmat' => $parentKmatInNachbau])['data'][0] ?? null;

        if (!$result) {
            $query = "SELECT parent_kmat FROM material_kmat_subkmats WHERE parent_kmat = :kmat GROUP BY parent_kmat";
            $result = DbManager::fetchPDOQuery('dto_configurator', $query, [':kmat' => $parentKmatInNachbau])['data'][0];
        }

        return $result;
    }

    public function increaseNachbauRowPosition($position): string
    {
        // Check if the position is numeric or starts with dots
        if ($position === '0') {
            return '.1';
        } elseif (preg_match('/^\.*(\d+)$/', $position, $matches)) {
            $dotsCount = strlen($matches[0]) - strlen($matches[1]); // Count dots
            $number = (int)$matches[1]; // Get the current number
            $newNumber = $number + 1; // Increment the number
            return str_repeat('.', $dotsCount + 1) . $newNumber;
        } else {
            // Return the same position if it doesn't match the expected patterns
            return $position;
        }
    }

    public function removeAllProjectWorksInTransferToNachbau($projectNo, $nachbauNo) {

        $query = "SELECT id FROM project_works WHERE project_number = :projectNo AND nachbau_number = :transferToNachbau AND deleted IS NULL";
        $nachbauProjectWorks = DbManager::fetchPDOQueryData('dto_configurator', $query, [':projectNo' => $projectNo, ':transferToNachbau' => $nachbauNo])['data'];

        if (!empty($nachbauProjectWorks)) {
            $nachbauProjectWorkIds = array_column($nachbauProjectWorks, 'id');

            //Bom'a eklenenleri sil.
            $query = "UPDATE bom_change SET active = 0, deleted_user = :delUser, deleted = :deletedAt WHERE project_work_id IN (:ids)";
            DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':ids' => $nachbauProjectWorkIds]);

            //Çalışmadakileri sil.
            $query = "UPDATE project_works SET deleted = :deletedAt, deleted_user = :delUser WHERE id IN (:ids)";
            DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':ids' => $nachbauProjectWorkIds]);
        }

        //Varsa spare DTO çalışmalarını temizle.
        $query = "UPDATE project_works_spare SET deleted = :deletedAt, deleted_user = :delUser WHERE project_number = :pNo AND nachbau_number = :nNo";
        DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':pNo' => $projectNo, ':nNo' => $nachbauNo]);

        //Varsa extension DTO çalışmalarını temizle .
        $query = "UPDATE project_works_extensions SET deleted = :deletedAt, deleted_user = :delUser WHERE project_number = :pNo AND nachbau_number = :nNo";
        DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':pNo' => $projectNo, ':nNo' => $nachbauNo]);

        //Varsa aktarım hatası değişikliklerini temizle.
        $query = "UPDATE project_works_nachbau_errors SET deleted = :deletedAt, deleted_user = :delUser WHERE project_number = :pNo AND nachbau_number = :nNo";
        DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':pNo' => $projectNo, ':nNo' => $nachbauNo]);

        //Varsa interchange DTO çalışmalarını temizle.
        $query = "UPDATE project_works_interchange SET deleted = :deletedAt, deleted_user = :delUser WHERE project_number = :pNo AND nachbau_number = :nNo";
        DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':pNo' => $projectNo, ':nNo' => $nachbauNo]);

        //Varsa special DTO çalışmalarını temizle.
        $query = "UPDATE project_works_special_dtos SET deleted = :deletedAt, deleted_user = :delUser WHERE project_number = :pNo AND nachbau_number = :nNo";
        DbManager::fetchPDOQuery('dto_configurator', $query,[':delUser' => SharedManager::$fullname, ':deletedAt' => date('Y-m-d H:i:s'), ':pNo' => $projectNo, ':nNo' => $nachbauNo]);

        // Proje güncellenme tarihini updatele.
        $query = "UPDATE projects SET last_updated_by=:whom, last_updated_date=:uptDate, working_user = :wc, project_status = 2 WHERE project_number=:pNo AND nachbau_number=:nNo";
        DbManager::fetchPDOQuery('dto_configurator', $query, [':whom' => SharedManager::$fullname, ':uptDate' => (new DateTime())->format('Y-m-d H:i:s'), ':wc' => SharedManager::$fullname, ':pNo' => $projectNo, ':nNo' => $nachbauNo]);
    }

    public function getAccessoryKmatObjectOfProject($projectNo, $nachbauNo = null): array
    {
        if ($nachbauNo === null) {
            $query = "SELECT FileName FROM log_nachbau WHERE FactoryNumber = :pNo ORDER BY ID DESC LIMIT 1";
            $lastNachbauResult = DbManager::fetchPDOQueryData('logs', $query, [':pNo' => $projectNo])['data'][0] ?? [];
            $nachbauNo = $lastNachbauResult['FileName'] ?? null;
        }

        $query = "SELECT SUBSTRING(kmat, 3) AS kmat, typical_no, panel_no 
                 FROM nachbau_datas 
                 WHERE project_no = :pNo AND nachbau_no = :nNo 
                 GROUP BY typical_no LIMIT 1";

        return DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'][0] ?? [];
    }

    public function getNachbauDtoNumberForProjectWork($projectNo, $nachbauNo, $dtoNumberTrimmed)
    {
        $query = "SELECT kmat_name
                  FROM nachbau_datas 
                  WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat_name LIKE :kmat_name
                  GROUP BY kmat_name
                  ORDER BY LENGTH(kmat_name)";

        return DbManager::fetchPDOQueryData('planning', $query,
            [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':kmat_name' => '%' . $dtoNumberTrimmed . '%'])['data'][0]['kmat_name'] ?? [];
    }

    public function getSapMaterialNumberOfMlfb($mlfb) {
        //SPECIAL kodlarda MLFB bilgisi boş geliyor Description aramak lazım full LIKE ile.
        if (str_ends_with($mlfb, "SPE")) {
            $query = "SELECT Material FROM sap_spiridon_064 WHERE Description LIKE :mlfb";
            return DbManager::fetchPDOQueryData('rpa', $query, [':mlfb' => '%'.$mlfb. '%'])['data'][0]['Material'] ?? NULL;
        }else{
            $query = "SELECT Material FROM sap_spiridon_064 WHERE Mlfb LIKE :mlfb";
            return DbManager::fetchPDOQueryData('rpa', $query, [':mlfb' => '%'.$mlfb])['data'][0]['Material'] ?? NULL;
        }
    }

    public function getParentKmatOfMaterialWithTypicalAndOrtzKz($projectNo, $nachbauNo, $materialDeletedNo, $typicalNo, $ortzKz) {

        $query = "SELECT parent_kmat, qty, unit
                  FROM nachbau_datas
                  WHERE project_no = :pNo
                    AND nachbau_no = :nNo
                    AND kmat LIKE :kmat
                    AND typical_no = :typicalNo
                    AND ortz_kz = :ortzKz";

        return DbManager::fetchPDOQueryData('planning', $query,
            [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':kmat' => '%' . $materialDeletedNo . '%', ':typicalNo' => $typicalNo, ':ortzKz' => $ortzKz])['data'][0];
    }

    public function getTypicalAndPanelsDictionary($projectNo, $nachbauNo){

        $typicalPanelQuery = "SELECT typical_no, ortz_kz, panel_no 
                          FROM nachbau_datas 
                          WHERE project_no = :pNo 
                            AND nachbau_no = :nNo
                          GROUP BY typical_no, ortz_kz, panel_no";

        $typicalPanelRows = DbManager::fetchPDOQuery('planning', $typicalPanelQuery, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'];

        // Step 3: Create mapping array: typical_no => list of panel info
        $accessoryTypicalNo = $this->getAccessoryTypicalOfProject($projectNo);
        $typicalToPanelsMap = [];
        foreach ($typicalPanelRows as $row) {
            $typical = $row['typical_no'];
            if (!isset($typicalToPanelsMap[$typical])) {
                $typicalToPanelsMap[$typical] = [];
            }
            $typicalToPanelsMap[$typical][] = [
                'ortz_kz' => $row['ortz_kz'],
                'panel_no' => $row['panel_no'],
                'sap_pos_no' => $this->convertNachbauPanelNoToMToolPosNo($projectNo, $accessoryTypicalNo, $typical, $row['ortz_kz'], $row['panel_no'])
            ];
        }

        return $typicalToPanelsMap;
    }

    public function convertNachbauPanelNoToMToolPosNo($projectNo, $accessoryTypicalNo, $typical, $ortzKz, $panelNo) {
        if ($typical === $accessoryTypicalNo) {
            $query = "SELECT PosNo FROM dbo.OneX_SapPosData WHERE ProjectNo = :pNo AND TypicalCode = :typical";
            $posNo = DbManager::fetchPDOQueryData('MTool_INKWA', $query, [':pNo' => $projectNo, ':typical' => $typical])['data'][0]['PosNo'] ?? '';
        } else {
            $query = "SELECT PosNo FROM dbo.OneX_SapPosData WHERE ProjectNo = :pNo AND TypicalCode = :typical AND LocationCode = :ortzKz";
            $result = DbManager::fetchPDOQueryData('MTool_INKWA', $query, [':pNo' => $projectNo, ':typical' => $typical, ':ortzKz' => $ortzKz])['data'];

            if (count($result) > 1) {
                $query = "SELECT PosNo FROM dbo.OneX_SapPosData WHERE ProjectNo = :pNo AND TypicalCode = :typical AND LocationCode = :ortzKz AND PosNo LIKE :panelNo";
                $result = DbManager::fetchPDOQueryData('MTool_INKWA', $query, [':pNo' => $projectNo, ':typical' => $typical, ':ortzKz' => $ortzKz, ':panelNo' => '%'.ltrim($panelNo, '0').'%'])['data'];
            }

            $posNo = $result[0]['PosNo'] ?? '';
        }

        return str_pad($posNo, 6, '0', STR_PAD_LEFT); // Converting Mtool pos no to sap pos
    }

    public function getMaterialStartsByFromSpiridon064($materialNo): string
    {
        $query = "SELECT Material FROM sap_spiridon_064 WHERE Material LIKE :materialNo";
        $material = DbManager::fetchPDOQueryData('rpa', $query, [':materialNo' => '%'.$materialNo.'%'])['data'][0]['Material'] ?? '';
        return str_replace($materialNo, '', $material);
    }

    public function getSapMaterialPrefixByMaterialNo($materialNo) {
        $query = "SELECT sap_material_number FROM materials WHERE material_number = :materialNo";
        $sapMaterialNumber = DbManager::fetchPDOQueryData('dto_configurator', $query, [':materialNo' => $materialNo])['data'][0]['sap_material_number'];

        $prefixes = array('A7E00', 'A7ETKBL', 'A7ET0', 'A7ET', 'A7E');
        $materialPrefix = '';

        foreach ($prefixes as $prefix) {
            if (str_starts_with($sapMaterialNumber, $prefix)) {
                $materialPrefix = $prefix;
                break;
            }
        }

        return $materialPrefix;
    }

    public function getDtoConfiguratorProjectId($projectNo, $nachbauNo) {
        $query = "SELECT id FROM projects WHERE project_number = :pNo AND nachbau_number = :nNo";
        return DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'][0]['id'];
    }

    public function getMaterialDescriptionFromDtoConfigurator($materialNo): string {
        return DbManager::fetchPDOQueryData('dto_configurator',"SELECT description FROM materials WHERE material_number LIKE :materialNo", [':materialNo' => '%'.$materialNo.'%'])['data'][0]['description'] ?? '';
    }

    public function updateMaterialSapDefined($materialNo) {
        $query = "SELECT id FROM materials WHERE material_number = :materialNo";
        $materialData = DbManager::fetchPDOQueryData('dto_configurator', $query, [':materialNo' => $materialNo])['data'][0];

        if (empty($materialData))
            returnHttpResponse(500, "Material " . $materialNo . " is not defined in DTO Configurator.");

        $query = "UPDATE materials SET sap_defined = 1 WHERE id = :id";
        DbManager::fetchPDOQueryData('dto_configurator', $query, [':id' => $materialData['id']])['data'];
    }

    public function checkIfMaterialsSapDefinedApiRequest($operation, $data) {
        $materialAdded   = $data['material_added_starts_by'] . $data['material_added_number'];
        $materialDeleted = $data['material_deleted_starts_by'] . $data['material_deleted_number'];

        $checkMaterial = function ($materialNo, $materialFull, $label) {
            $materialDetail = $this->getMaterialDetail($materialFull);
            if (!empty($materialDetail['CREATED_ON'])) {
                $this->updateMaterialSapDefined($materialNo);
            }
//            else {
//                returnHttpResponse(400, $label . " material $materialFull has not been defined in SAP.");
//            }
        };

        if ($operation === 'add' && $data['material_added_sap_defined'] === '0') {
            $checkMaterial($data['material_added_number'], $materialAdded, 'Added');
        }

        if ($operation === 'replace') {
            if ($data['material_added_sap_defined'] === '0') {
                $checkMaterial($data['material_added_number'], $materialAdded, 'Added');
            }
            if ($data['material_deleted_sap_defined'] === '0') {
                $checkMaterial($data['material_deleted_number'], $materialDeleted, 'Deleted');
            }
        }

        if ($operation === 'delete' && $data['material_deleted_sap_defined'] === '0') {
            $checkMaterial($data['material_deleted_number'], $materialDeleted, 'Deleted');
        }
    }


    public function getMaterialDetail($material)
    {
        $postFields = json_encode(array(
            'api_Key' => 'AhKLHf345bGHK89qlshuebWHds',
            'fields' => [[
                'clazz' => 'java.lang.String',
                'name' => 'MATERIAL',
                'value' => $material,
                'transferType' => 0,
                'transferTypeText' => "Import",
                'subFields' => null
            ]
            ]
        ));
        $headers = [
            'Content-type: application/json',
            'Accept: text/plain',
            'User-Agent: PostmanRuntime/7.26.8',
            'Accept: */*',
            'Connection: keep-alive'
        ];

        $isLocal = in_array(getUserIP(), ["127.0.0.1", "::1"]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://apphub.siemens.com/data-bus/genericSAPBackend/call/Material_Details');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $isLocal ? 0 : 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        curl_setopt($ch, CURLOPT_POST, 1);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Handle cURL errors
        if ($curlError) {
            error_log("cURL Error for material $material: $curlError");
            return false;
        }

        // Handle HTTP errors (including 500)
        if ($httpCode !== 200) {
            error_log("HTTP Error $httpCode for material $material. Response: $result");
            return false;
        }

        if (!empty($result)) {
            return $this->extractMaterialDetail(json_decode($result, true), $material);
        }
    }

    public function extractMaterialDetail($materialDetails, $material) {
        $materialdata["MATERIAL_NO"] = $material;
        $fields_to_retrieve = array(
            "CREATED_ON",
            "MATL_TYPE"
        );

        $values = array();

        foreach ($materialDetails['responseObject']['fields'] as $field) {
            foreach ($fields_to_retrieve as $field_name) {
                if ($field['name'] === 'MATERIAL_GENERAL_DATA') {
                    foreach ($field['subFields'] as $subField) {
                        if ($subField['name'] === $field_name) {
                            $values[$field_name] = $subField['value'];
                        }
                    }
                }
            }
        }

        foreach ($values as $field_name => $value) {
            $materialdata[$field_name] = $value;
        }

        return $materialdata;
    }

    public function getMaterialDetailWithLike()
    {
        ini_set('memory_limit', '512M');
        $materialKeyword = $_GET['materialKeyword'];

        $postFields = json_encode(array(
            'api_Key' => 'AhKLHf345bGHK89qlshuebWHds',
            'fields' => array(
                array(
                    'clazz' => 'java.util.List',
                    'name' => 'MATNRSELECTION',
                    'transferType' => 2,
                    'transferTypeText' => 'Table',
                    'subFields' => array(
                        array(
                            'clazz' => 'java.util.Map',
                            'transferType' => 2,
                            'transferTypeText' => 'Row',
                            'subFields' => array(
                                array(
                                    'clazz' => 'java.lang.String',
                                    'name' => 'SIGN',
                                    'value' => 'I',
                                    'transferType' => 2,
                                    'transferTypeText' => 'Field'
                                ),
                                array(
                                    'clazz' => 'java.lang.String',
                                    'name' => 'OPTION',
                                    'value' => 'CP',
                                    'transferType' => 2,
                                    'transferTypeText' => 'Field'
                                ),
                                array(
                                    'clazz' => 'java.lang.String',
                                    'name' => 'MATNR_LOW',
                                    'value' => '*'.$materialKeyword.'*',
                                    'transferType' => 2,
                                    'transferTypeText' => 'Field'
                                ),
                                array(
                                    'clazz' => 'java.lang.String',
                                    'name' => 'MATNR_HIGH',
                                    'value' => '',
                                    'transferType' => 2,
                                    'transferTypeText' => 'Field'
                                )
                            )
                        )
                    )
                )
            )
        ));
        $headers = [
            'Content-type: application/json',
            'Accept: text/plain',
            'User-Agent: PostmanRuntime/7.26.8',
            'Accept: */*',
            'Connection: keep-alive'
        ];

        $isLocal = in_array(getUserIP(), ["127.0.0.1", "::1"]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://apphub.siemens.com/data-bus/genericSAPBackend/call/Material_GetList');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $isLocal ? 0 : 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        curl_setopt($ch, CURLOPT_POST, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        // Decode the API response
        $apiResponse = json_decode($result, true);

        // Extract materials using our function
        $materials = $this->extractMaterialsFromResponse($apiResponse);

        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($materials);
        exit();
    }

    function extractMaterialsFromResponse($apiResponse) {
        $materials = [];

        // Check if response has the expected structure
        if (!isset($apiResponse['responseObject']['fields'])) {
            return $materials;
        }

        $fields = $apiResponse['responseObject']['fields'];

        // Find MATNRLIST field
        $matnrList = null;
        foreach ($fields as $field) {
            if (isset($field['name']) && $field['name'] === 'MATNRLIST') {
                $matnrList = $field;
                break;
            }
        }

        // If MATNRLIST not found, return empty array
        if (!$matnrList || !isset($matnrList['subFields'])) {
            return $materials;
        }

        // Extract materials from each record
        foreach ($matnrList['subFields'] as $record) {
            if (!isset($record['subFields'])) {
                continue;
            }

            $materialNumber = '';
            $materialDesc = '';

            // Extract MATERIAL and MATL_DESC from subFields
            foreach ($record['subFields'] as $subField) {
                if (isset($subField['name']) && isset($subField['value'])) {
                    if ($subField['name'] === 'MATERIAL') {
                        $materialNumber = $subField['value'];
                    } elseif ($subField['name'] === 'MATL_DESC') {
                        $materialDesc = $subField['value'];
                    }
                }
            }

            // Add to materials array if both values exist
            if (!empty($materialNumber) && !empty($materialDesc)) {
                $materials[] = [
                    'material' => $materialNumber,
                    'description' => $materialDesc
                ];
            }
        }

        return $materials;
    }

    public function getAllTypicalsGroupedByDto($projectNo, $nachbauNo, $accessoryTypicalNumber = null): array {
        // ✅ CORRECT - fetch rules first from dto_configurator, then query planning
        $rulesQuery = "SELECT d.rules FROM rules d WHERE d.key='nachbau_dto_names'";
        $rulesResult = DbManager::fetchPDOQueryData('dto_configurator', $rulesQuery)['data'][0] ?? [];
        $nachbauDtoPattern = $rulesResult['rules'] ?? '';

        // Get all DTO data with typicals using rules table
        $query = "SELECT kmat_name, typical_no 
                  FROM nachbau_datas 
                  WHERE project_no = :pNo 
                    AND nachbau_no = :nNo 
                    AND kmat_name REGEXP :pattern
                  ORDER BY typical_no";

        $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':pattern' => $nachbauDtoPattern])['data'] ?? [];

        // Separate accessory DTOs from other DTOs
        $accessoryDtos = [];
        $otherDtos = [];

        foreach ($result as $row) {
            if (!empty($accessoryTypicalNumber) && $row['typical_no'] === $accessoryTypicalNumber) {
                $accessoryDtos[] = $row;
            } else {
                $otherDtos[] = $row;
            }
        }

        // Sanitize: Remove accessory DTOs if they exist in other typicals
        foreach ($accessoryDtos as $index => $accessoryRow) {
            $kmatName = $accessoryRow['kmat_name'];

            // Format DTO number
            if (str_contains($kmatName, 'KUKO_CON')) {
                $formattedDtoNumber = $this->formatKukoDtoNumber($kmatName);
            } else {
                $formattedDtoNumber = $this->formatDtoNumber($kmatName);
            }

            // Check if this DTO exists in other typicals
            foreach ($otherDtos as $otherRow) {
                $otherFormatted = str_contains($otherRow['kmat_name'], 'KUKO_CON')
                    ? $this->formatKukoDtoNumber($otherRow['kmat_name'])
                    : $this->formatDtoNumber($otherRow['kmat_name']);

                if ($formattedDtoNumber === $otherFormatted) {
                    // Remove from accessory DTOs if it exists in other typicals
                    unset($accessoryDtos[$index]);
                    break;
                }
            }

            // Also remove KUKO_CON from accessories
            if (str_contains($accessoryRow['kmat_name'], 'KUKO_CON')) {
                unset($accessoryDtos[$index]);
            }
        }

        // Merge sanitized data
        $accessoryDtos = array_values($accessoryDtos);
        $allDtos = array_merge($accessoryDtos, $otherDtos);

        // Group by formatted DTO number
        $grouped = [];
        foreach ($allDtos as $row) {
            $kmatName = $row['kmat_name'];

            // Format DTO number
            if (str_contains($kmatName, 'KUKO_CON')) {
                $dtoNumber = $this->formatKukoDtoNumber($kmatName);
            } else {
                $dtoNumber = $this->formatDtoNumber($kmatName);
            }

            if (!empty($dtoNumber) && !empty($row['typical_no'])) {
                if (!isset($grouped[$dtoNumber])) {
                    $grouped[$dtoNumber] = [];
                }
                $grouped[$dtoNumber][] = $row['typical_no'];
            }
        }

        // Remove duplicate typicals for each DTO
        foreach ($grouped as $dto => $typicals) {
            $grouped[$dto] = array_unique($typicals);
        }

        return $grouped;
    }


    public function getTypicalsOfDto($projectNo, $nachbauNo, $dtoNumber): array {
        $query = "SELECT DISTINCT typical_no FROM nachbau_datas WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat_name LIKE :dtoNumber";
        $parameters = [':pNo' => $projectNo, ':nNo' => $nachbauNo, ':dtoNumber' => '%'.$dtoNumber.'%'];
        $result = DbManager::fetchPDOQueryData('planning', $query, $parameters)['data'] ?? [];
        return array_column($result, 'typical_no');
    }

    public function checkIfProjectHasReleased($projectNo, $nachbauNo) {
        $query = "SELECT released_project_id FROM view_released_projects WHERE project_number = :pNo AND nachbau_number = :nNo AND submission_status = 5";
        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'];

        if (!empty($data))
            return true;

        return false;
    }

    public function insertProjectNachbauIntoDtoConfigurator($projectNo, $nachbauNo): void {
        $query = "SELECT id FROM projects WHERE project_number = :pNo AND nachbau_number = :nNo";
        $data = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo, ':nNo' => $nachbauNo])['data'][0] ?? [];
        // SharedManager::print($data);
        if (empty($data)) {
            $query = "SELECT Product FROM dbo.OneX_ProjectDetails WHERE FactoryNumber = :projectNo";
            $productName = DbManager::fetchPDOQuery('MTool_INKWA', $query, [':projectNo'=>$projectNo])['result'][0]['Product'] ?? [];
            // SharedManager::print($productName);
            if ($productName === 'NXAIR 1C') {
                $productId = 1;
            } elseif ($productName === 'NXAIR 50kA') {
                $productId = 2;
            } elseif ($productName === 'NXAIR H') {
                $productId = 3;
            } elseif ($productName === 'NXAIR World') {
                $productId = 4;
            } elseif ($productName === 'NXAIR') {
                $productId = 5;
            } else {
                $productId = null;
            }

            if ($productId !== null) {
                $query = "INSERT INTO projects (project_number, nachbau_number, product_type, start_date, last_updated_date, last_updated_by, project_status) 
                        VALUES (:projectNumber, :nachbauNumber, :productType, :startDate, :lastUpdatedDate, :lastUpdatedBy, :projectStatus)";

                $queryParamsA = [
                    ":projectNumber" => $projectNo,
                    ":nachbauNumber" => $nachbauNo,
                    ":productType" => $productId,
                    ":startDate" => date('Y-m-d H:i:s'),
                    ":lastUpdatedDate" => date('Y-m-d H:i:s'),
                    ":lastUpdatedBy" => SharedManager::$fullname,
                    ":projectStatus" => 1
                ];

                DbManager::fetchPDOQuery('dto_configurator', $query, $queryParamsA);

                SharedManager::saveLog('log_dtoconfigurator', "CREATED | Project and Nachbau Inserted into DTO Configurator | " . $projectNo . ' | ' . $nachbauNo . ' | ' . $productName);
                Journals::saveJournal("CREATED | Project and Nachbau Inserted into DTO Configurator | " . $projectNo . ' | ' . $nachbauNo . ' | ' . $productName, PAGE_PROJECTS, DESIGN_DETAIL_NACHBAU_OPERATIONS, ACTION_CREATED, implode(' | ', $_POST), "Insert Project");
            }
        }
    }

}

$controller = new BaseController($_POST);

$response = match ($_GET['action']) {
    'getUserInfo' => $controller->getUserInfo(),
    'getMaterialDetailWithLike' => $controller->getMaterialDetailWithLike(),
    'searchProject' => $controller->searchProject(),
    default => ['status' => 400, 'message' => 'Invalid action'],
};