<?php
// /dpm/api/KukoMatrixController.php

/**
 * Kuko Matrix Controller
 * 
 * Handles all Kuko Matrix related API operations
 * Uses existing OneX India database structure and DbManager
 */

require_once __DIR__ . '/../core/index.php';

class KukoMatrixController extends BaseController {

    /**
     * Get Kuko Matrix Data
     */
    public function getKukoMatrixData() {
        try {
            $projectNo = isset($_GET['projectNo']) ? trim($_GET['projectNo']) : 
                        (isset($_POST['projectNo']) ? trim($_POST['projectNo']) : '');
            $nachbauFile = isset($_GET['nachbauFile']) ? trim($_GET['nachbauFile']) : 
                          (isset($_POST['nachbauFile']) ? trim($_POST['nachbauFile']) : '');

            if (empty($projectNo)) {
                $this->sendJsonResponse(400, 'Project number is required', null);
                return;
            }

            // Get Kuko Matrix data
            $kukoQuery = "SELECT * FROM kuko_matrix 
                         WHERE project_no = :projectNo";
            
            if (!empty($nachbauFile)) {
                $kukoQuery .= " AND nachbau_file = :nachbauFile";
            }
            
            $kukoQuery .= " ORDER BY id ASC";

            $params = [':projectNo' => $projectNo];
            if (!empty($nachbauFile)) {
                $params[':nachbauFile'] = $nachbauFile;
            }

            $kukoResult = DbManager::fetchPDOQueryData('dto_configurator', $kukoQuery, $params)['data'] ?? [];

            // Get materials for reference
            $materialQuery = "SELECT * FROM materials ORDER BY name ASC";
            $materialResult = DbManager::fetchPDOQueryData('dto_configurator', $materialQuery, [])['data'] ?? [];

            $response = [
                'projectNo' => $projectNo,
                'nachbauFile' => $nachbauFile,
                'kukoMatrixData' => $kukoResult,
                'materials' => $materialResult,
                'totalEntries' => count($kukoResult)
            ];

            $this->sendJsonResponse(200, 'Kuko Matrix data retrieved successfully', $response);

        } catch (Exception $e) {
            error_log("Error in getKukoMatrixData: " . $e->getMessage());
            $this->sendJsonResponse(500, $e->getMessage(), null);
        }
    }

    /**
     * Create Kuko Matrix Entry
     */
    public function createKukoMatrixEntry() {
        try {
            $projectNo = isset($_POST['projectNo']) ? trim($_POST['projectNo']) : '';
            $nachbauFile = isset($_POST['nachbauFile']) ? trim($_POST['nachbauFile']) : '';
            $entry = isset($_POST['entry']) ? $_POST['entry'] : [];

            if (empty($projectNo) || empty($entry)) {
                $this->sendJsonResponse(400, 'Project number and entry data are required', null);
                return;
            }

            // Insert Kuko Matrix entry
            $insertQuery = "INSERT INTO kuko_matrix (
                project_no,
                nachbau_file,
                material_number,
                description,
                quantity,
                unit,
                status,
                notes,
                created_at,
                created_by
            ) VALUES (
                :projectNo,
                :nachbauFile,
                :materialNumber,
                :description,
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
                ':materialNumber' => $entry['material_number'] ?? '',
                ':description' => $entry['description'] ?? '',
                ':quantity' => $entry['quantity'] ?? 0,
                ':unit' => $entry['unit'] ?? '',
                ':status' => $entry['status'] ?? 'pending',
                ':notes' => $entry['notes'] ?? '',
                ':createdBy' => isset($_SESSION['username']) ? $_SESSION['username'] : 'system'
            ];

            $result = DbManager::fetchPDOQuery('dto_configurator', $insertQuery, $params);

            if ($result['success']) {
                $this->sendJsonResponse(201, 'Kuko Matrix entry created successfully', ['id' => $result['insertId']]);
            } else {
                $this->sendJsonResponse(500, 'Failed to create Kuko Matrix entry', null);
            }

        } catch (Exception $e) {
            error_log("Error in createKukoMatrixEntry: " . $e->getMessage());
            $this->sendJsonResponse(500, $e->getMessage(), null);
        }
    }

    /**
     * Update Kuko Matrix Entry
     */
    public function updateKukoMatrixEntry() {
        try {
            $projectNo = isset($_POST['projectNo']) ? trim($_POST['projectNo']) : '';
            $entryId = isset($_POST['entryId']) ? trim($_POST['entryId']) : '';
            $entry = isset($_POST['entry']) ? $_POST['entry'] : [];

            if (empty($projectNo) || empty($entryId) || empty($entry)) {
                $this->sendJsonResponse(400, 'Project number, entry ID, and entry data are required', null);
                return;
            }

            // Update Kuko Matrix entry
            $updateQuery = "UPDATE kuko_matrix SET 
                material_number = :materialNumber,
                description = :description,
                quantity = :quantity,
                unit = :unit,
                status = :status,
                notes = :notes,
                updated_at = NOW(),
                updated_by = :updatedBy
            WHERE id = :id AND project_no = :projectNo";

            $params = [
                ':id' => $entryId,
                ':projectNo' => $projectNo,
                ':materialNumber' => $entry['material_number'] ?? '',
                ':description' => $entry['description'] ?? '',
                ':quantity' => $entry['quantity'] ?? 0,
                ':unit' => $entry['unit'] ?? '',
                ':status' => $entry['status'] ?? 'pending',
                ':notes' => $entry['notes'] ?? '',
                ':updatedBy' => isset($_SESSION['username']) ? $_SESSION['username'] : 'system'
            ];

            $result = DbManager::fetchPDOQuery('dto_configurator', $updateQuery, $params);

            if ($result['success']) {
                $this->sendJsonResponse(200, 'Kuko Matrix entry updated successfully', null);
            } else {
                $this->sendJsonResponse(500, 'Failed to update Kuko Matrix entry', null);
            }

        } catch (Exception $e) {
            error_log("Error in updateKukoMatrixEntry: " . $e->getMessage());
            $this->sendJsonResponse(500, $e->getMessage(), null);
        }
    }

    /**
     * Delete Kuko Matrix Entry
     */
    public function deleteKukoMatrixEntry() {
        try {
            $projectNo = isset($_POST['projectNo']) ? trim($_POST['projectNo']) : '';
            $entryId = isset($_POST['entryId']) ? trim($_POST['entryId']) : '';

            if (empty($projectNo) || empty($entryId)) {
                $this->sendJsonResponse(400, 'Project number and entry ID are required', null);
                return;
            }

            // Delete Kuko Matrix entry
            $deleteQuery = "DELETE FROM kuko_matrix 
                           WHERE id = :id AND project_no = :projectNo";

            $params = [
                ':id' => $entryId,
                ':projectNo' => $projectNo
            ];

            $result = DbManager::fetchPDOQuery('dto_configurator', $deleteQuery, $params);

            if ($result['success']) {
                $this->sendJsonResponse(200, 'Kuko Matrix entry deleted successfully', null);
            } else {
                $this->sendJsonResponse(500, 'Failed to delete Kuko Matrix entry', null);
            }

        } catch (Exception $e) {
            error_log("Error in deleteKukoMatrixEntry: " . $e->getMessage());
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
    $controller = new KukoMatrixController();

    switch ($action) {
        case "getKukoMatrixData":
            $controller->getKukoMatrixData();
            break;
        case "createKukoMatrixEntry":
            $controller->createKukoMatrixEntry();
            break;
        case "updateKukoMatrixEntry":
            $controller->updateKukoMatrixEntry();
            break;
        case "deleteKukoMatrixEntry":
            $controller->deleteKukoMatrixEntry();
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
    error_log("Error in KukoMatrixController: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
    ]);
}
exit;
?>