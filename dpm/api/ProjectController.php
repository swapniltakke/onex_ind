<?php
// /dpm/api/ProjectController.php

/**
 * Project Controller
 * 
 * Handles all project-related API operations
 * Uses existing OneX India database structure and DbManager
 */

require_once __DIR__ . '/../core/index.php';

class ProjectController extends BaseController {

    /**
     * Get All Projects
     * Fetches all projects from planning database
     */
    public function getAllProjects() {
        try {
            // Get all projects from database
            $projectsQuery = "SELECT 
                                p.project_no,
                                p.project_name,
                                p.status,
                                p.created_date,
                                p.contact,
                                p.panel,
                                p.product,
                                p.last_worked,
                                p.checked_in,
                                p.latest_nachbau,
                                p.last_updated
                            FROM projects p
                            ORDER BY p.created_date DESC";
            
            $projectsResult = DbManager::fetchPDOQueryData('dto_configurator', $projectsQuery, [])['data'] ?? [];

            // Check nachbau availability for each project
            $projects = [];
            foreach ($projectsResult as $project) {
                $nachbauQuery = "SELECT COUNT(*) as count FROM log_nachbau 
                                WHERE FactoryNumber = :projectNo";
                
                $nachbauResult = DbManager::fetchPDOQueryData('logs', $nachbauQuery, [
                    ':projectNo' => $project['project_no']
                ])['data'] ?? [];

                $project['nachbau_available'] = !empty($nachbauResult) && $nachbauResult[0]['count'] > 0;
                $projects[] = $project;
            }

            $response = [
                'projects' => $projects,
                'totalProjects' => count($projects)
            ];

            $this->sendJsonResponse(200, 'All projects retrieved successfully', $response);

        } catch (Exception $e) {
            error_log("Error in getAllProjects: " . $e->getMessage());
            $this->sendJsonResponse(500, $e->getMessage(), null);
        }
    }

    /**
     * Search Projects
     * Search projects by project number or name
     */
    public function searchProjects() {
        try {
            $searchTerm = isset($_GET['searchTerm']) ? trim($_GET['searchTerm']) : 
                         (isset($_POST['searchTerm']) ? trim($_POST['searchTerm']) : '');

            if (empty($searchTerm)) {
                $this->sendJsonResponse(400, 'Search term is required', null);
                return;
            }

            // Search in projects
            $projectsQuery = "SELECT 
                                p.project_no,
                                p.project_name,
                                p.status,
                                p.created_date,
                                p.contact,
                                p.panel,
                                p.product,
                                p.last_worked,
                                p.checked_in,
                                p.latest_nachbau,
                                p.last_updated
                            FROM projects p
                            WHERE p.project_no LIKE :searchTerm 
                               OR p.project_name LIKE :searchTerm
                               OR p.contact LIKE :searchTerm
                            ORDER BY p.created_date DESC
                            LIMIT 50";
            
            $searchPattern = '%' . $searchTerm . '%';
            $projectsResult = DbManager::fetchPDOQueryData('dto_configurator', $projectsQuery, [
                ':searchTerm' => $searchPattern
            ])['data'] ?? [];

            // Check nachbau availability for each project
            $projects = [];
            foreach ($projectsResult as $project) {
                $nachbauQuery = "SELECT COUNT(*) as count FROM log_nachbau 
                                WHERE FactoryNumber = :projectNo";
                
                $nachbauResult = DbManager::fetchPDOQueryData('logs', $nachbauQuery, [
                    ':projectNo' => $project['project_no']
                ])['data'] ?? [];

                $project['nachbau_available'] = !empty($nachbauResult) && $nachbauResult[0]['count'] > 0;
                $projects[] = $project;
            }

            $response = [
                'projects' => $projects,
                'totalProjects' => count($projects)
            ];

            $this->sendJsonResponse(200, 'Projects search completed successfully', $response);

        } catch (Exception $e) {
            error_log("Error in searchProjects: " . $e->getMessage());
            $this->sendJsonResponse(500, $e->getMessage(), null);
        }
    }

    /**
     * Get Project Information
     */
    public function getProjectInfo() {
        try {
            $projectNo = isset($_GET['projectNo']) ? trim($_GET['projectNo']) : 
                        (isset($_POST['projectNo']) ? trim($_POST['projectNo']) : '');

            if (empty($projectNo)) {
                $this->sendJsonResponse(400, 'Project number is required', null);
                return;
            }

            // Get project info from database
            $projectQuery = "SELECT * FROM projects WHERE project_no = :projectNo LIMIT 1";
            
            $projectResult = DbManager::fetchPDOQueryData('dto_configurator', $projectQuery, [
                ':projectNo' => $projectNo
            ])['data'] ?? [];

            if (empty($projectResult)) {
                $this->sendJsonResponse(404, 'Project not found', null);
                return;
            }

            $project = $projectResult[0];

            // Check if Nachbau exists
            $nachbauQuery = "SELECT * FROM log_nachbau 
                            WHERE FactoryNumber = :projectNo
                            ORDER BY DateCreated DESC
                            LIMIT 1";
            
            $nachbauResult = DbManager::fetchPDOQueryData('logs', $nachbauQuery, [
                ':projectNo' => $projectNo
            ])['data'] ?? [];

            $nachbauExists = !empty($nachbauResult);
            $latestNachbau = $nachbauExists ? $nachbauResult[0] : null;

            // Get project work items
            $workQuery = "SELECT * FROM project_work 
                         WHERE project_no = :projectNo
                         ORDER BY created_at DESC";
            
            $workResult = DbManager::fetchPDOQueryData('dto_configurator', $workQuery, [
                ':projectNo' => $projectNo
            ])['data'] ?? [];

            // Prepare response
            $response = [
                'projectInfo' => [
                    'projectNo' => $project['project_no'] ?? '',
                    'projectName' => $project['project_name'] ?? '',
                    'contact' => $project['contact'] ?? '',
                    'panel' => $project['panel'] ?? '',
                    'product' => $project['product'] ?? '',
                    'status' => $project['status'] ?? 'Active',
                    'createdDate' => $project['created_date'] ?? '',
                    'lastWorked' => $project['last_worked'] ?? '',
                    'checkedIn' => $project['checked_in'] ?? '',
                    'latestNachbau' => $project['latest_nachbau'] ?? '',
                    'lastUpdated' => $project['last_updated'] ?? ''
                ],
                'nachbauExists' => $nachbauExists,
                'latestNachbau' => $latestNachbau,
                'workItemsCount' => count($workResult)
            ];

            $this->sendJsonResponse(200, 'Project information retrieved successfully', $response);

        } catch (Exception $e) {
            error_log("Error in getProjectInfo: " . $e->getMessage());
            $this->sendJsonResponse(500, $e->getMessage(), null);
        }
    }

    /**
     * Get Project Info Details
     * Fetch detailed project information
     */
    public function getProjectInfoDetails() {
        try {
            $projectNo = isset($_GET['projectNo']) ? trim($_GET['projectNo']) : 
                        (isset($_POST['projectNo']) ? trim($_POST['projectNo']) : '');

            if (empty($projectNo)) {
                $this->sendJsonResponse(400, 'Project number is required', null);
                return;
            }

            // Get project info from database
            $projectQuery = "SELECT * FROM projects WHERE project_no = :projectNo LIMIT 1";
            
            $projectResult = DbManager::fetchPDOQueryData('dto_configurator', $projectQuery, [
                ':projectNo' => $projectNo
            ])['data'] ?? [];

            if (empty($projectResult)) {
                $this->sendJsonResponse(404, 'Project not found', null);
                return;
            }

            $project = $projectResult[0];

            // Get all related data
            $nachbauQuery = "SELECT * FROM log_nachbau 
                            WHERE FactoryNumber = :projectNo
                            ORDER BY DateCreated DESC";
            
            $nachbauResult = DbManager::fetchPDOQueryData('logs', $nachbauQuery, [
                ':projectNo' => $projectNo
            ])['data'] ?? [];

            // Get work items
            $workQuery = "SELECT * FROM project_work 
                         WHERE project_no = :projectNo
                         ORDER BY created_at DESC";
            
            $workResult = DbManager::fetchPDOQueryData('dto_configurator', $workQuery, [
                ':projectNo' => $projectNo
            ])['data'] ?? [];

            // Get order items
            $orderQuery = "SELECT * FROM order_items 
                          WHERE project_no = :projectNo
                          ORDER BY created_at DESC";
            
            $orderResult = DbManager::fetchPDOQueryData('dto_configurator', $orderQuery, [
                ':projectNo' => $projectNo
            ])['data'] ?? [];

            $response = [
                'projectInfo' => $project,
                'nachbauFiles' => $nachbauResult,
                'workItems' => $workResult,
                'orderItems' => $orderResult
            ];

            $this->sendJsonResponse(200, 'Project details retrieved successfully', $response);

        } catch (Exception $e) {
            error_log("Error in getProjectInfoDetails: " . $e->getMessage());
            $this->sendJsonResponse(500, $e->getMessage(), null);
        }
    }

    /**
     * Get Project Work Data
     */
    public function getProjectWorkData() {
        try {
            $projectNo = isset($_GET['projectNo']) ? trim($_GET['projectNo']) : 
                        (isset($_POST['projectNo']) ? trim($_POST['projectNo']) : '');
            $nachbauFile = isset($_GET['nachbauFile']) ? trim($_GET['nachbauFile']) : 
                          (isset($_POST['nachbauFile']) ? trim($_POST['nachbauFile']) : '');

            if (empty($projectNo)) {
                $this->sendJsonResponse(400, 'Project number is required', null);
                return;
            }

            // Get project work items
            $workQuery = "SELECT * FROM project_work 
                         WHERE project_no = :projectNo";
            
            if (!empty($nachbauFile)) {
                $workQuery .= " AND nachbau_file = :nachbauFile";
            }
            
            $workQuery .= " ORDER BY created_at DESC";

            $params = [':projectNo' => $projectNo];
            if (!empty($nachbauFile)) {
                $params[':nachbauFile'] = $nachbauFile;
            }

            $workResult = DbManager::fetchPDOQueryData('dto_configurator', $workQuery, $params)['data'] ?? [];

            // Get work centers
            $centerQuery = "SELECT * FROM work_centers ORDER BY name ASC";
            $centerResult = DbManager::fetchPDOQueryData('dto_configurator', $centerQuery, [])['data'] ?? [];

            // Get materials
            $materialQuery = "SELECT * FROM materials ORDER BY name ASC";
            $materialResult = DbManager::fetchPDOQueryData('dto_configurator', $materialQuery, [])['data'] ?? [];

            $response = [
                'projectNo' => $projectNo,
                'nachbauFile' => $nachbauFile,
                'workItems' => $workResult,
                'workCenters' => $centerResult,
                'materials' => $materialResult,
                'totalWorkItems' => count($workResult)
            ];

            $this->sendJsonResponse(200, 'Project work data retrieved successfully', $response);

        } catch (Exception $e) {
            error_log("Error in getProjectWorkData: " . $e->getMessage());
            $this->sendJsonResponse(500, $e->getMessage(), null);
        }
    }

    /**
     * Create Work Item
     */
    public function createWorkItem() {
        try {
            $projectNo = isset($_POST['projectNo']) ? trim($_POST['projectNo']) : '';
            $nachbauFile = isset($_POST['nachbauFile']) ? trim($_POST['nachbauFile']) : '';
            $workItem = isset($_POST['workItem']) ? $_POST['workItem'] : [];

            if (empty($projectNo) || empty($workItem)) {
                $this->sendJsonResponse(400, 'Project number and work item data are required', null);
                return;
            }

            // Insert work item
            $insertQuery = "INSERT INTO project_work (
                project_no,
                nachbau_file,
                description,
                work_center,
                material,
                quantity,
                unit,
                status,
                notes,
                created_at,
                created_by
            ) VALUES (
                :projectNo,
                :nachbauFile,
                :description,
                :workCenter,
                :material,
                :quantity,
                :unit,
                :status,
                :notes,
                NOW(),
                :createdBy
            )";

            $params = [
                ':projectNo' => $projectNo,
                ':nachbauFile' => $nachbauFile,
                ':description' => $workItem['description'] ?? '',
                ':workCenter' => $workItem['work_center'] ?? '',
                ':material' => $workItem['material'] ?? '',
                ':quantity' => $workItem['quantity'] ?? 0,
                ':unit' => $workItem['unit'] ?? '',
                ':status' => $workItem['status'] ?? 'pending',
                ':notes' => $workItem['notes'] ?? '',
                ':createdBy' => isset($_SESSION['username']) ? $_SESSION['username'] : 'system'
            ];

            $result = DbManager::fetchPDOQuery('dto_configurator', $insertQuery, $params);

            if ($result['success']) {
                $this->sendJsonResponse(201, 'Work item created successfully', ['id' => $result['insertId']]);
            } else {
                $this->sendJsonResponse(500, 'Failed to create work item', null);
            }

        } catch (Exception $e) {
            error_log("Error in createWorkItem: " . $e->getMessage());
            $this->sendJsonResponse(500, $e->getMessage(), null);
        }
    }

    /**
     * Update Work Item
     */
    public function updateWorkItem() {
        try {
            $projectNo = isset($_POST['projectNo']) ? trim($_POST['projectNo']) : '';
            $workItemId = isset($_POST['workItemId']) ? trim($_POST['workItemId']) : '';
            $workItem = isset($_POST['workItem']) ? $_POST['workItem'] : [];

            if (empty($projectNo) || empty($workItemId) || empty($workItem)) {
                $this->sendJsonResponse(400, 'Project number, work item ID, and work item data are required', null);
                return;
            }

            // Update work item
            $updateQuery = "UPDATE project_work SET 
                description = :description,
                work_center = :workCenter,
                material = :material,
                quantity = :quantity,
                unit = :unit,
                status = :status,
                notes = :notes,
                updated_at = NOW(),
                updated_by = :updatedBy
            WHERE id = :id AND project_no = :projectNo";

            $params = [
                ':id' => $workItemId,
                ':projectNo' => $projectNo,
                ':description' => $workItem['description'] ?? '',
                ':workCenter' => $workItem['work_center'] ?? '',
                ':material' => $workItem['material'] ?? '',
                ':quantity' => $workItem['quantity'] ?? 0,
                ':unit' => $workItem['unit'] ?? '',
                ':status' => $workItem['status'] ?? 'pending',
                ':notes' => $workItem['notes'] ?? '',
                ':updatedBy' => isset($_SESSION['username']) ? $_SESSION['username'] : 'system'
            ];

            $result = DbManager::fetchPDOQuery('dto_configurator', $updateQuery, $params);

            if ($result['success']) {
                $this->sendJsonResponse(200, 'Work item updated successfully', null);
            } else {
                $this->sendJsonResponse(500, 'Failed to update work item', null);
            }

        } catch (Exception $e) {
            error_log("Error in updateWorkItem: " . $e->getMessage());
            $this->sendJsonResponse(500, $e->getMessage(), null);
        }
    }

    /**
     * Delete Work Item
     */
    public function deleteWorkItem() {
        try {
            $projectNo = isset($_POST['projectNo']) ? trim($_POST['projectNo']) : '';
            $workItemId = isset($_POST['workItemId']) ? trim($_POST['workItemId']) : '';

            if (empty($projectNo) || empty($workItemId)) {
                $this->sendJsonResponse(400, 'Project number and work item ID are required', null);
                return;
            }

            // Delete work item
            $deleteQuery = "DELETE FROM project_work 
                           WHERE id = :id AND project_no = :projectNo";

            $params = [
                ':id' => $workItemId,
                ':projectNo' => $projectNo
            ];

            $result = DbManager::fetchPDOQuery('dto_configurator', $deleteQuery, $params);

            if ($result['success']) {
                $this->sendJsonResponse(200, 'Work item deleted successfully', null);
            } else {
                $this->sendJsonResponse(500, 'Failed to delete work item', null);
            }

        } catch (Exception $e) {
            error_log("Error in deleteWorkItem: " . $e->getMessage());
            $this->sendJsonResponse(500, $e->getMessage(), null);
        }
    }
}

// Handle routing
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
    $controller = new ProjectController();

    switch ($action) {
        case "getAllProjects":
            $controller->getAllProjects();
            break;
        case "searchProjects":
            $controller->searchProjects();
            break;
        case "getProjectInfo":
            $controller->getProjectInfo();
            break;
        case "getProjectInfoDetails":
            $controller->getProjectInfoDetails();
            break;
        case "getProjectWorkData":
            $controller->getProjectWorkData();
            break;
        case "createWorkItem":
            $controller->createWorkItem();
            break;
        case "updateWorkItem":
            $controller->updateWorkItem();
            break;
        case "deleteWorkItem":
            $controller->deleteWorkItem();
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
    error_log("Error in ProjectController: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
    ]);
}
exit;
?>