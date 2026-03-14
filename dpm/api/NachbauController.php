<?php
// /dpm/api/NachbauController.php

require_once __DIR__ . '/../core/index.php';

class NachbauController extends BaseController {

    /**
     * Get Nachbau Files List
     */
    public function getNachbauFilesList() {
        try {
            $projectNo = isset($_GET['projectNo']) ? trim($_GET['projectNo']) : 
                        (isset($_POST['projectNo']) ? trim($_POST['projectNo']) : '');

            if (empty($projectNo)) {
                $this->sendJsonResponse(400, 'Project number is required', null);
                return;
            }

            // Get all nachbau files for project
            $filesQuery = "SELECT * FROM log_nachbau 
                          WHERE FactoryNumber = :projectNo 
                          ORDER BY Created DESC";
            
            $filesResult = DbManager::fetchPDOQueryData('logs', $filesQuery, [
                ':projectNo' => $projectNo
            ])['data'] ?? [];

            // Get material count for each file
            $files = [];
            foreach ($filesResult as $file) {
                $countQuery = "SELECT COUNT(*) as count FROM nachbau_datas 
                              WHERE project_no = :projectNo AND nachbau_no = :nachbauNo";
                
                $countResult = DbManager::fetchPDOQueryData('planning', $countQuery, [
                    ':projectNo' => $projectNo,
                    ':nachbauNo' => $file['FileName']
                ])['data'] ?? [];

                $file['MaterialCount'] = !empty($countResult) ? $countResult[0]['count'] : 0;
                $files[] = $file;
            }

            $response = [
                'files' => $files,
                'totalFiles' => count($files)
            ];

            $this->sendJsonResponse(200, 'Nachbau files list retrieved successfully', $response);

        } catch (Exception $e) {
            error_log("Error in getNachbauFilesList: " . $e->getMessage());
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
    $controller = new NachbauController();

    switch ($action) {
        case "getNachbauFilesList":
            $controller->getNachbauFilesList();
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
    error_log("Error in NachbauController: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
    ]);
}
exit;