<?php
// /dpm/api/BaseController.php
// CORRECTED VERSION - Matching OneX Turkey Structure

abstract class BaseController {
    protected $user;
    protected $userGroupID;
    protected $dbName = 'dtoconfigurator'; // Correct database name

    public function __construct() {
        require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";
        
        $this->user = SharedManager::getUser();
        $this->userGroupID = $this->user["GroupID"] ?? null;
        
        if (!$this->isUserAuthorized()) {
            $this->sendJsonResponse(403, "Not Authorized", null);
            exit;
        }
    }

    /**
     * Check if user is authorized
     */
    protected function isUserAuthorized() {
        if (!isset($this->userGroupID)) {
            return false;
        }
        
        if (!is_int($this->userGroupID) && !(is_string($this->userGroupID) && ctype_digit($this->userGroupID))) {
            return false;
        }
        
        return true;
    }

    /**
     * Send JSON response
     */
    protected function sendJsonResponse($statusCode, $message, $data = null) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = [
            'success' => $statusCode >= 200 && $statusCode < 300,
            'message' => $message,
            'data' => $data
        ];
        
        echo json_encode($response);
    }

    /**
     * Get current user
     */
    protected function getUser() {
        return $this->user;
    }

    /**
     * Get user group ID
     */
    protected function getUserGroupId() {
        return $this->userGroupID;
    }

    /**
     * Check if user is admin
     */
    protected function isAdmin() {
        return in_array($this->userGroupID, [1, '1']);
    }

    /**
     * Check if user has access right
     */
    protected function hasAccessRight($moduleId, $actionId) {
        try {
            return SharedManager::hasAccessRight($moduleId, $actionId);
        } catch (Exception $e) {
            error_log("Error in hasAccessRight: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log message
     */
    protected function log($message) {
        error_log("[" . date('Y-m-d H:i:s') . "] " . $message);
    }

    /**
     * Validate required fields
     */
    protected function validateRequiredFields($data, $requiredFields) {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return [
                    'valid' => false,
                    'message' => "Field '{$field}' is required"
                ];
            }
        }
        
        return ['valid' => true];
    }

    /**
     * Sanitize input
     */
    protected function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get database name
     */
    protected function getDbName() {
        return $this->dbName;
    }
}
?>