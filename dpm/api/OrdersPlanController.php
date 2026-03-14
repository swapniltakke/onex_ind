<?php
// /dpm/api/OrdersPlanController.php

/**
 * Orders Plan Controller
 * 
 * Handles all order summary and orders plan related API operations
 * Uses existing OneX India database structure and DbManager
 */

require_once __DIR__ . '/../core/index.php';

class OrdersPlanController extends BaseController {

    /**
     * Get Order Summary Data
     */
    public function getOrderSummaryData() {
        try {
            $projectNo = isset($_GET['projectNo']) ? trim($_GET['projectNo']) : 
                        (isset($_POST['projectNo']) ? trim($_POST['projectNo']) : '');
            $nachbauFile = isset($_GET['nachbauFile']) ? trim($_GET['nachbauFile']) : 
                          (isset($_POST['nachbauFile']) ? trim($_POST['nachbauFile']) : '');

            if (empty($projectNo)) {
                $this->sendJsonResponse(400, 'Project number is required', null);
                return;
            }

            // Get all order items
            $orderQuery = "SELECT * FROM order_items 
                          WHERE project_no = :projectNo";
            
            if (!empty($nachbauFile)) {
                $orderQuery .= " AND nachbau_file = :nachbauFile";
            }
            
            $orderQuery .= " ORDER BY id ASC";

            $params = [':projectNo' => $projectNo];
            if (!empty($nachbauFile)) {
                $params[':nachbauFile'] = $nachbauFile;
            }

            $orderResult = DbManager::fetchPDOQueryData('dto_configurator', $orderQuery, $params)['data'] ?? [];

            // Separate special DTOs and spare parts
            $specialDTOs = array_filter($orderResult, function($item) {
                return isset($item['type']) && $item['type'] === 'special';
            });

            $spareParts = array_filter($orderResult, function($item) {
                return isset($item['type']) && $item['type'] === 'spare';
            });

            $standardItems = array_filter($orderResult, function($item) {
                return isset($item['type']) && $item['type'] === 'standard';
            });

            // Calculate statistics
            $totalQuantity = 0;
            $totalValue = 0;

            foreach ($orderResult as $item) {
                $totalQuantity += isset($item['quantity']) ? intval($item['quantity']) : 0;
                $totalValue += (isset($item['quantity']) ? intval($item['quantity']) : 0) * (isset($item['unit_price']) ? floatval($item['unit_price']) : 0);
            }

            $statistics = [
                'totalItems' => count($orderResult),
                'totalQuantity' => $totalQuantity,
                'totalValue' => round($totalValue, 2),
                'standardItems' => count($standardItems),
                'specialDTOs' => count($specialDTOs),
                'spareParts' => count($spareParts)
            ];

            $response = [
                'projectNo' => $projectNo,
                'nachbauFile' => $nachbauFile,
                'orderItems' => $orderResult,
                'specialDTOs' => array_values($specialDTOs),
                'spareParts' => array_values($spareParts),
                'standardItems' => array_values($standardItems),
                'statistics' => $statistics
            ];

            $this->sendJsonResponse(200, 'Order summary data retrieved successfully', $response);

        } catch (Exception $e) {
            error_log("Error in getOrderSummaryData: " . $e->getMessage());
            $this->sendJsonResponse(500, $e->getMessage(), null);
        }
    }

    /**
     * Create Order Item
     */
    public function createOrderItem() {
        try {
            $projectNo = isset($_POST['projectNo']) ? trim($_POST['projectNo']) : '';
            $nachbauFile = isset($_POST['nachbauFile']) ? trim($_POST['nachbauFile']) : '';
            $orderItem = isset($_POST['orderItem']) ? $_POST['orderItem'] : [];

            if (empty($projectNo) || empty($orderItem)) {
                $this->sendJsonResponse(400, 'Project number and order item data are required', null);
                return;
            }

            // Insert order item
            $insertQuery = "INSERT INTO order_items (
                project_no,
                nachbau_file,
                material_number,
                description,
                type,
                quantity,
                unit,
                unit_price,
                status,
                priority,
                notes,
                created_at,
                created_by
            ) VALUES (
                :projectNo,
                :nachbauFile,
                :materialNumber,
                :description,
                :type,
                :quantity,
                :unit,
                :unitPrice,
                :status,
                :priority,
                :notes,
                NOW(),
                :createdBy
            )";

            $params = [
                ':projectNo' => $projectNo,
                ':nachbauFile' => $nachbauFile,
                ':materialNumber' => $orderItem['material_number'] ?? '',
                ':description' => $orderItem['description'] ?? '',
                ':type' => $orderItem['type'] ?? 'standard',
                ':quantity' => $orderItem['quantity'] ?? 0,
                ':unit' => $orderItem['unit'] ?? '',
                ':unitPrice' => $orderItem['unit_price'] ?? 0,
                ':status' => $orderItem['status'] ?? 'pending',
                ':priority' => $orderItem['priority'] ?? 'medium',
                ':notes' => $orderItem['notes'] ?? '',
                ':createdBy' => isset($_SESSION['username']) ? $_SESSION['username'] : 'system'
            ];

            $result = DbManager::fetchPDOQuery('dto_configurator', $insertQuery, $params);

            if ($result['success']) {
                $this->sendJsonResponse(201, 'Order item created successfully', ['id' => $result['insertId']]);
            } else {
                $this->sendJsonResponse(500, 'Failed to create order item', null);
            }

        } catch (Exception $e) {
            error_log("Error in createOrderItem: " . $e->getMessage());
            $this->sendJsonResponse(500, $e->getMessage(), null);
        }
    }

    /**
     * Update Order Item
     */
    public function updateOrderItem() {
        try {
            $projectNo = isset($_POST['projectNo']) ? trim($_POST['projectNo']) : '';
            $orderItemId = isset($_POST['orderItemId']) ? trim($_POST['orderItemId']) : '';
            $orderItem = isset($_POST['orderItem']) ? $_POST['orderItem'] : [];

            if (empty($projectNo) || empty($orderItemId) || empty($orderItem)) {
                $this->sendJsonResponse(400, 'Project number, order item ID, and order item data are required', null);
                return;
            }

            // Update order item
            $updateQuery = "UPDATE order_items SET 
                material_number = :materialNumber,
                description = :description,
                type = :type,
                quantity = :quantity,
                unit = :unit,
                unit_price = :unitPrice,
                status = :status,
                priority = :priority,
                notes = :notes,
                updated_at = NOW(),
                updated_by = :updatedBy
            WHERE id = :id AND project_no = :projectNo";

            $params = [
                ':id' => $orderItemId,
                ':projectNo' => $projectNo,
                ':materialNumber' => $orderItem['material_number'] ?? '',
                ':description' => $orderItem['description'] ?? '',
                ':type' => $orderItem['type'] ?? 'standard',
                ':quantity' => $orderItem['quantity'] ?? 0,
                ':unit' => $orderItem['unit'] ?? '',
                ':unitPrice' => $orderItem['unit_price'] ?? 0,
                ':status' => $orderItem['status'] ?? 'pending',
                ':priority' => $orderItem['priority'] ?? 'medium',
                ':notes' => $orderItem['notes'] ?? '',
                ':updatedBy' => isset($_SESSION['username']) ? $_SESSION['username'] : 'system'
            ];

            $result = DbManager::fetchPDOQuery('dto_configurator', $updateQuery, $params);

            if ($result['success']) {
                $this->sendJsonResponse(200, 'Order item updated successfully', null);
            } else {
                $this->sendJsonResponse(500, 'Failed to update order item', null);
            }

        } catch (Exception $e) {
            error_log("Error in updateOrderItem: " . $e->getMessage());
            $this->sendJsonResponse(500, $e->getMessage(), null);
        }
    }

    /**
     * Delete Order Item
     */
    public function deleteOrderItem() {
        try {
            $projectNo = isset($_POST['projectNo']) ? trim($_POST['projectNo']) : '';
            $orderItemId = isset($_POST['orderItemId']) ? trim($_POST['orderItemId']) : '';

            if (empty($projectNo) || empty($orderItemId)) {
                $this->sendJsonResponse(400, 'Project number and order item ID are required', null);
                return;
            }

            // Delete order item
            $deleteQuery = "DELETE FROM order_items 
                           WHERE id = :id AND project_no = :projectNo";

            $params = [
                ':id' => $orderItemId,
                ':projectNo' => $projectNo
            ];

            $result = DbManager::fetchPDOQuery('dto_configurator', $deleteQuery, $params);

            if ($result['success']) {
                $this->sendJsonResponse(200, 'Order item deleted successfully', null);
            } else {
                $this->sendJsonResponse(500, 'Failed to delete order item', null);
            }

        } catch (Exception $e) {
            error_log("Error in deleteOrderItem: " . $e->getMessage());
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
    $controller = new OrdersPlanController();

    switch ($action) {
        case "getOrderSummaryData":
            $controller->getOrderSummaryData();
            break;
        case "createOrderItem":
            $controller->createOrderItem();
            break;
        case "updateOrderItem":
            $controller->updateOrderItem();
            break;
        case "deleteOrderItem":
            $controller->deleteOrderItem();
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
    error_log("Error in OrdersPlanController: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
    ]);
}
exit;
?>