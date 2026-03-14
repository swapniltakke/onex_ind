<?php
// /dpm/api/DTOConfiguratorController.php
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/api/MToolManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/dpm/api/BaseController.php";

header('Content-Type: application/json; charset=utf-8');

/**
 * DTOConfiguratorController Class
 * Handles all DTO Configurator related API calls
 */
class DTOConfiguratorController extends BaseController {

    private $projectsTable = 'projects';
    private $projectDetailsTable = 'project_details';
    private $assemblyPlanTable = 'assembly_plan_mv';

    /**
     * Get user information
     */
    public function getUserInfo() {
        try {
            $user = $this->getUser();
            
            $userInfo = [
                'username' => $user['username'] ?? '',
                'name' => $user['Name'] ?? '',
                'surname' => $user['Surname'] ?? '',
                'email' => $user['Email'] ?? '',
                'groupId' => $this->getUserGroupId(),
                'isAdmin' => $this->isAdmin(),
                'department' => $user['Department'] ?? ''
            ];
            
            $this->sendJsonResponse(200, 'User information retrieved successfully', $userInfo);
        } catch (Exception $e) {
            error_log("Error in getUserInfo: " . $e->getMessage());
            $this->sendJsonResponse(500, $e->getMessage(), null);
        }
    }

    /**
     * Get all projects with pagination
     */
    public function getProjects() {
        try {
            $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
            $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
            $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
            $search = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';
            $orderColumn = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
            $orderDir = isset($_POST['order'][0]['dir']) ? strtoupper($_POST['order'][0]['dir']) : 'ASC';

            $params = [];

            // Base query - Using projects table (DTO Configurator source of truth)
            $sql = "SELECT 
                        pj.project_number,
                        pj.nachbau_number,
                        pj.project_name,
                        po.product_type AS product,
                        ps.status,
                        pj.working_user,
                        pj.last_updated_by,
                        pj.last_updated_date,
                        pj.project_status
                    FROM projects pj
                    LEFT JOIN products po ON pj.product_type = po.id
                    LEFT JOIN status ps ON pj.project_status = ps.id
                    WHERE pj.deleted IS NULL";

            // Add search filter
            if (!empty($search)) {
                $sql .= " AND (pj.project_number LIKE :search 
                           OR pj.project_name LIKE :search 
                           OR pj.nachbau_number LIKE :search)";
                $params[':search'] = "%$search%";
            }

            // Get total records count
            $totalSql = "SELECT COUNT(DISTINCT pj.project_number) as count 
                        FROM projects pj
                        WHERE pj.deleted IS NULL";
            
            $totalResult = DbManager::fetchPDOQueryData('dto_configurator', $totalSql);
            $totalRecords = (isset($totalResult['data']) && !empty($totalResult['data'])) ? 
                           intval($totalResult['data'][0]['count']) : 0;

            // Get filtered records count
            $countSql = "SELECT COUNT(*) as count FROM (" . $sql . ") as counted";
            $countResult = DbManager::fetchPDOQueryData('dto_configurator', $countSql, $params);
            $filteredRecords = (isset($countResult['data']) && !empty($countResult['data'])) ? 
                              intval($countResult['data'][0]['count']) : 0;

            // Add ordering
            $columns = ['project_number', 'project_name', 'product', 'status', 'last_updated_date', 'working_user'];
            $orderColumnName = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'project_number';
            $sql .= " ORDER BY " . $orderColumnName . " " . $orderDir;

            // Add pagination
            $sql .= " LIMIT :start, :length";
            $params[':start'] = $start;
            $params[':length'] = $length;

            // Get data
            $result = DbManager::fetchPDOQueryData('dto_configurator', $sql, $params);
            $data = $result['data'] ?? [];

            // Format response
            $response = [
                'draw' => intval($draw),
                'recordsTotal' => intval($totalRecords),
                'recordsFiltered' => intval($filteredRecords),
                'data' => $data
            ];

            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            error_log("Error in getProjects: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'draw' => 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get project information by project number
     */
    public function getProjectInfo() {
        try {
            $projectNo = isset($_GET['projectNo']) ? trim($_GET['projectNo']) : 
                        (isset($_POST['projectNo']) ? trim($_POST['projectNo']) : '');

            error_log("DEBUG: getProjectInfo called with projectNo: " . $projectNo);

            if (empty($projectNo)) {
                $this->sendJsonResponse(400, 'Project number is required', null);
                return;
            }

            // Get project from MTool
            $projectInfo = MToolManager::getProjectDetailsByProjectNos([$projectNo])[0] ?? null;

            error_log("DEBUG: Project from MTool: " . json_encode($projectInfo));

            if (empty($projectInfo) || empty($projectInfo['FactoryNumber'])) {
                error_log("DEBUG: Project not found in MTool for: " . $projectNo);
                $this->sendJsonResponse(404, 'Project not found in MTool', null);
                return;
            }

            // Check if project is planned
            $isProjectPlanned = $this->checkIfProjectPlannedOrNot($projectNo);
            $projectInfo['isProjectPlanned'] = $isProjectPlanned;

            // Check if nachbau exists
            $isNachbauExists = $this->checkIfNachbauExistsInProject($projectNo);
            $projectInfo['isNachbauExists'] = $isNachbauExists;

            // Get project characteristics
            $projectCharacteristics = $this->getProjectCharacteristics($projectNo, $projectInfo['Product'] ?? '');
            $projectInfo['ratedVoltage'] = ltrim($projectCharacteristics['rated_voltage'], '0');
            $projectInfo['ratedShortCircuit'] = ltrim($projectCharacteristics['rated_short_circuit'], '0');
            $projectInfo['ratedCurrent'] = ltrim($projectCharacteristics['rated_current'], '0');

            // Get assembly start date
            $assemblyStartDate = $this->getProjectAssemblyStartDateOfProject($projectNo);
            $projectInfo['assemblyStartDate'] = $assemblyStartDate;

            error_log("DEBUG: Final projectInfo: " . json_encode($projectInfo));

            $this->sendJsonResponse(200, 'Project information retrieved successfully', $projectInfo);
        } catch (Exception $e) {
            error_log("Error in getProjectInfo: " . $e->getMessage());
            error_log("Error trace: " . $e->getTraceAsString());
            $this->sendJsonResponse(500, $e->getMessage(), null);
        }
    }

    /**
     * Get Nachbau data for a project
     */
    public function getNachbauData() {
        try {
            $projectNo = isset($_GET['projectNo']) ? trim($_GET['projectNo']) : 
                        (isset($_POST['projectNo']) ? trim($_POST['projectNo']) : '');

            if (empty($projectNo)) {
                $this->sendJsonResponse(400, 'Project number is required', null);
                return;
            }

            // Get latest nachbau
            $query = "SELECT nachbau_no FROM log_nachbau 
                    WHERE FactoryNumber = :pNo 
                    ORDER BY ID DESC LIMIT 1";
            $result = DbManager::fetchPDOQueryData('logs', $query, [':pNo' => $projectNo])['data'] ?? [];
            
            $nachbauNo = !empty($result) ? $result[0]['nachbau_no'] : null;

            if (empty($nachbauNo)) {
                $this->sendJsonResponse(404, 'No nachbau found for this project', null);
                return;
            }

            // Get nachbau materials
            $materialsQuery = "SELECT 
                                sap_pos_no as position,
                                parent_kmat as material_number,
                                description,
                                quantity,
                                unit,
                                panel_no,
                                typical_no
                            FROM nachbau_datas 
                            WHERE project_no = :pNo AND nachbau_no = :nNo
                            ORDER BY sap_pos_no ASC";
            
            $materialsResult = DbManager::fetchPDOQueryData('planning', $materialsQuery, [
                ':pNo' => $projectNo,
                ':nNo' => $nachbauNo
            ])['data'] ?? [];

            // Prepare response
            $response = [
                'nachbau_no' => $nachbauNo,
                'materials' => $materialsResult
            ];

            $this->sendJsonResponse(200, 'Nachbau data retrieved successfully', $response);

        } catch (Exception $e) {
            error_log("Error in getNachbauData: " . $e->getMessage());
            $this->sendJsonResponse(500, $e->getMessage(), null);
        }
    }

    /**
     * Get project assembly start date
     */
    private function getProjectAssemblyStartDateOfProject($projectNo) {
        try {
            $query = "SELECT DATE_FORMAT(MIN(productionday), '%d.%m.%Y') AS ProductionDay 
                      FROM (
                        SELECT projectNo, productionday
                        FROM assembly_plan_mv 
                        WHERE revisionNr IN (SELECT MAX(revisionNr) FROM assembly_plan_mv WHERE projectNo = :pNo)
                            AND ProjectNo = :pNo
                      ) AS t
                      GROUP BY projectNo";

            $result = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo])['data'] ?? [];
            
            return !empty($result) ? $result[0]['ProductionDay'] : '';
        } catch (Exception $e) {
            error_log("Error in getProjectAssemblyStartDateOfProject: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Check if nachbau exists in project
     */
    private function checkIfNachbauExistsInProject($projectNo): bool {
        try {
            $query = "SELECT FileName FROM log_nachbau WHERE FactoryNumber = :pNo ORDER BY ID DESC LIMIT 1";
            $data = DbManager::fetchPDOQueryData('logs', $query, [':pNo' => $projectNo])['data'] ?? null;

            return !empty($data);
        } catch (Exception $e) {
            error_log("Error in checkIfNachbauExistsInProject: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if project is planned
     */
    private function checkIfProjectPlannedOrNot($projectNo): bool {
        try {
            $query = "SELECT id FROM assembly_plan_mv WHERE projectNo = :pNo";
            $data = DbManager::fetchPDOQueryData('planning', $query, [':pNo' => $projectNo])['data'] ?? [];

            return !empty($data);
        } catch (Exception $e) {
            error_log("Error in checkIfProjectPlannedOrNot: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get project characteristics (rated voltage, current, short circuit)
     */
    private function getProjectCharacteristics($projectNo, $panelType): array {
        try {
            $query = "SELECT 
                        RatedVoltage AS rated_voltage,
                        SCCurTime AS rated_short_circuit,
                        BusbarCurrent AS rated_current
                    FROM OneX_ProjectTechDataEE 
                    WHERE FactoryNumber = :pNo";

            $result = DbManager::fetchPDOQueryData('MTool_INKWA', $query, [
                ':pNo' => $projectNo
            ])['data'] ?? [];

            return !empty($result) ? [
                'rated_voltage' => $result[0]['rated_voltage'] ?? '',
                'rated_short_circuit' => $result[0]['rated_short_circuit'] ?? '',
                'rated_current' => $result[0]['rated_current'] ?? ''
            ] : [
                'rated_voltage' => '',
                'rated_short_circuit' => '',
                'rated_current' => ''
            ];

        } catch (Exception $e) {
            error_log("Error in getProjectCharacteristics: " . $e->getMessage());
            return [
                'rated_voltage' => '',
                'rated_short_circuit' => '',
                'rated_current' => ''
            ];
        }
    }

    /**
     * Search projects with MTool
     */
    public function searchProjectsWithMTool() {
        try {
            $term = isset($_POST['term']) ? trim($_POST['term']) : 
                   (isset($_GET['term']) ? trim($_GET['term']) : '');

            error_log("DEBUG: searchProjectsWithMTool called with term: " . $term);

            if (empty($term) || strlen($term) < 1) {
                http_response_code(200);
                echo json_encode([]);
                return;
            }

            // Call MToolManager search function
            $result = MToolManager::searchProject($term);

            error_log("DEBUG: MToolManager::searchProject result: " . json_encode($result));

            $projects = [];
            if (isset($result) && !empty($result)) {
                foreach ($result as $row) {
                    $projects[] = [
                        'id' => $row['FactoryNumber'],
                        'project_no' => $row['FactoryNumber'],
                        'project_name' => $row['ProjectName']
                    ];
                }
            }

            http_response_code(200);
            echo json_encode($projects);
        } catch (Exception $e) {
            error_log("Error in searchProjectsWithMTool: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([]);
        }
    }

    /**
     * Get project by number
     */
    public function getProjectByNumber() {
        try {
            $projectNo = isset($_POST['projectNo']) ? trim($_POST['projectNo']) : '';

            if (empty($projectNo)) {
                $this->sendJsonResponse(400, 'Project number is required', null);
                return;
            }

            $projectInfo = MToolManager::getProjectDetailsByProjectNos([$projectNo]);

            if (!empty($projectInfo)) {
                $this->sendJsonResponse(200, 'Project found', $projectInfo);
            } else {
                $this->sendJsonResponse(404, 'Project not found', null);
            }
        } catch (Exception $e) {
            error_log("Error in getProjectByNumber: " . $e->getMessage());
            $this->sendJsonResponse(500, $e->getMessage(), null);
        }
    }

    /**
     * Get product ID of project
     */
    protected function getProductIdOfProject($projectNo) {
        try {
            $query = "SELECT product_type FROM projects WHERE project_number = :pNo LIMIT 1";
            $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':pNo' => $projectNo])['data'] ?? [];
            
            return !empty($result) ? $result[0]['product_type'] : null;
        } catch (Exception $e) {
            error_log("Error in getProductIdOfProject: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get DTO Configurator project ID
     */
    protected function getDtoConfiguratorProjectId($projectNo, $nachbauNo) {
        try {
            $query = "SELECT id FROM projects WHERE project_number = :pNo AND nachbau_number = :nNo LIMIT 1";
            $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [
                ':pNo' => $projectNo,
                ':nNo' => $nachbauNo
            ])['data'] ?? [];
            
            return !empty($result) ? $result[0]['id'] : null;
        } catch (Exception $e) {
            error_log("Error in getDtoConfiguratorProjectId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get SAP material prefix by material number
     */
    protected function getSapMaterialPrefixByMaterialNo($materialNo) {
        try {
            if (empty($materialNo)) {
                return '';
            }

            // Check if material starts with known prefixes
            if (strpos($materialNo, ':: VTH:') === 0) {
                return ':: VTH:';
            } elseif (strpos($materialNo, ':: CTH:') === 0) {
                return ':: CTH:';
            } elseif (strpos($materialNo, ':: KUKO') === 0) {
                return ':: KUKO';
            }

            // Query database for material prefix
            $query = "SELECT material_starts_by FROM materials WHERE material_number = :materialNo LIMIT 1";
            $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [':materialNo' => $materialNo])['data'] ?? [];
            
            return !empty($result) ? $result[0]['material_starts_by'] : '';
        } catch (Exception $e) {
            error_log("Error in getSapMaterialPrefixByMaterialNo: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Format DTO number
     */
    protected function formatDtoNumber($kmatName) {
        // Remove leading zeros and format DTO number
        $pattern = '/^[:\s]*([A-Z_]+_\d+)/';
        if (preg_match($pattern, $kmatName, $matches)) {
            return $matches[1];
        }
        return $kmatName;
    }

    /**
     * Format KUKO DTO number
     */
    protected function formatKukoDtoNumber($kmatName) {
        // Format KUKO DTO number
        $pattern = '/^::\s*KUKO\s*([A-Z0-9_]+)/';
        if (preg_match($pattern, $kmatName, $matches)) {
            return 'KUKO_' . $matches[1];
        }
        return $kmatName;
    }

    /**
     * Format description
     */
    protected function formatDescription($description, $maxLines = 3) {
        if (empty($description)) {
            return '';
        }
        
        $lines = explode("\n", $description);
        $limitedLines = array_slice($lines, 0, $maxLines);
        return implode("\n", $limitedLines);
    }

    /**
     * Get Nachbau description by DTO number
     */
    protected function getNachbauDescriptionByDtoNumber($dtoNumber, $projectNo, $nachbauNo, $isSpare = false) {
        try {
            $query = "SELECT description FROM nachbau_datas 
                      WHERE project_no = :pNo AND nachbau_no = :nNo AND kmat_name LIKE :dtoNo 
                      LIMIT 1";
            
            $result = DbManager::fetchPDOQueryData('planning', $query, [
                ':pNo' => $projectNo,
                ':nNo' => $nachbauNo,
                ':dtoNo' => '%' . $dtoNumber . '%'
            ])['data'] ?? [];
            
            return !empty($result) ? $result[0]['description'] : $dtoNumber;
        } catch (Exception $e) {
            error_log("Error in getNachbauDescriptionByDtoNumber: " . $e->getMessage());
            return $dtoNumber;
        }
    }

    /**
     * Get Nachbau DTO numbers starts by
     */
    protected function getNachbauDtoNumbersStartsBy() {
        try {
            $query = "SELECT rules FROM rules WHERE key = 'nachbau_dto_names'";
            $result = DbManager::fetchPDOQueryData('dto_configurator', $query)['data'] ?? [];
            
            if (!empty($result)) {
                return explode('|', $result[0]['rules']);
            }
            
            return [];
        } catch (Exception $e) {
            error_log("Error in getNachbauDtoNumbersStartsBy: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get typical and panels dictionary
     */
    protected function getTypicalAndPanelsDictionary($projectNo, $nachbauNo) {
        try {
            $query = "SELECT DISTINCT typical_no, ortz_kz, panel_no, sap_pos_no 
                      FROM nachbau_datas 
                      WHERE project_no = :pNo AND nachbau_no = :nNo
                      ORDER BY typical_no, ortz_kz";
            
            $result = DbManager::fetchPDOQueryData('planning', $query, [
                ':pNo' => $projectNo,
                ':nNo' => $nachbauNo
            ])['data'] ?? [];
            
            $dictionary = [];
            foreach ($result as $row) {
                if (!isset($dictionary[$row['typical_no']])) {
                    $dictionary[$row['typical_no']] = [];
                }
                
                $dictionary[$row['typical_no']][] = [
                    'ortz_kz' => $row['ortz_kz'],
                    'panel_no' => $row['panel_no'],
                    'sap_pos_no' => $row['sap_pos_no']
                ];
            }
            
            return $dictionary;
        } catch (Exception $e) {
            error_log("Error in getTypicalAndPanelsDictionary: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get material possible kmats
     */
    protected function getMaterialPossibleKmats($workCenterId, $subKmatName) {
        try {
            $query = "SELECT parent_kmat, sub_kmat FROM material_kmat_subkmats 
                      WHERE work_center_id = :wcId AND sub_kmat = :subKmat
                      GROUP BY parent_kmat, sub_kmat";
            
            $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [
                ':wcId' => $workCenterId,
                ':subKmat' => $subKmatName
            ])['data'] ?? [];
            
            $kmats = [];
            foreach ($result as $row) {
                $kmats[] = $row['parent_kmat'];
            }
            
            return implode('|', array_unique($kmats));
        } catch (Exception $e) {
            error_log("Error in getMaterialPossibleKmats: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Get material detail from SAP
     */
    protected function getMaterialDetail($materialNumber) {
        try {
            // This would call your SAP API or database
            // For now, returning a placeholder
            $query = "SELECT * FROM materials WHERE material_number = :materialNo LIMIT 1";
            $result = DbManager::fetchPDOQueryData('dto_configurator', $query, [
                ':materialNo' => $materialNumber
            ])['data'] ?? [];
            
            return !empty($result) ? $result[0] : [];
        } catch (Exception $e) {
            error_log("Error in getMaterialDetail: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get accessory KMAT object of project
     */
    protected function getAccessoryKmatObjectOfProject($projectNo, $nachbauNo = null) {
        try {
            $query = "SELECT parent_kmat, panel_no, ortz_kz FROM nachbau_datas 
                      WHERE project_no = :pNo AND parent_kmat LIKE '003003%'
                      LIMIT 1";
            
            $result = DbManager::fetchPDOQueryData('planning', $query, [
                ':pNo' => $projectNo
            ])['data'] ?? [];
            
            return !empty($result) ? [
                'kmat' => $result[0]['parent_kmat'],
                'panel_no' => $result[0]['panel_no'],
                'ortz_kz' => $result[0]['ortz_kz']
            ] : [];
        } catch (Exception $e) {
            error_log("Error in getAccessoryKmatObjectOfProject: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get project contacts from MTool
     */
    protected function getProjectContacts($projectNo) {
        try {
            return MToolManager::getProjectContacts([$projectNo])[0] ?? [];
        } catch (Exception $e) {
            error_log("Error in getProjectContacts: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Increase nachbau row position
     */
    protected function increaseNachbauRowPosition($position) {
        if (empty($position)) {
            return '1';
        }
        
        // Increment position by 0.1
        $newPosition = floatval($position) + 0.1;
        return number_format($newPosition, 1);
    }

    /**
     * Check material status in nachbau
     */
    protected function checkMaterialStatusInNachbau($projectNo, $nachbauNo, $row, $accessoryTypicalNumber = '', $accessoryParentKmat = '') {
        try {
            // This is a placeholder implementation
            // You would need to implement the full logic based on your requirements
            
            return [
                'error_message_id' => 0,
                'common_kmats' => '',
                'nachbau_kmats' => '',
                'nachbau_panels' => '',
                'nachbau_typicals' => ''
            ];
        } catch (Exception $e) {
            error_log("Error in checkMaterialStatusInNachbau: " . $e->getMessage());
            return [
                'error_message_id' => 0,
                'common_kmats' => '',
                'nachbau_kmats' => '',
                'nachbau_panels' => '',
                'nachbau_typicals' => ''
            ];
        }
    }

    /**
     * Update material status of project work
     */
    protected function updateMaterialStatusOfProjectWork($projectWorksResult, $workCenterId, $accessoryTypicalNumber = '', $accessoryParentKmat = '') {
        try {
            // Placeholder implementation
            // Implement based on your requirements
        } catch (Exception $e) {
            error_log("Error in updateMaterialStatusOfProjectWork: " . $e->getMessage());
        }
    }
}

// ============================================
// ACTION ROUTING
// ============================================

if (!isset($_POST["action"]) && !isset($_GET["action"])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action',
        'data' => null
    ]);
    exit;
}

$action = isset($_POST["action"]) ? $_POST["action"] : $_GET["action"];

try {
    $controller = new DTOConfiguratorController();

    switch ($action) {
        case "getUserInfo":
            $controller->getUserInfo();
            break;
        case "getProjects":
            $controller->getProjects();
            break;
        case "getProjectInfo":
            $controller->getProjectInfo();
            break;
        case "getNachbauData":
            $controller->getNachbauData();
            break;    
        case "searchProjectsWithMTool":
            $controller->searchProjectsWithMTool();
            break;
        case "getProjectByNumber":
            $controller->getProjectByNumber();
            break;
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action: ' . $action,
                'data' => null
            ]);
            break;
    }
} catch (Exception $e) {
    error_log("Error in DTOConfiguratorController: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
    ]);
}
exit;
?>