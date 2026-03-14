<?php
// /dpm/api/DTOConfiguratorHelper.php

class DTOConfiguratorHelper {

    /**
     * Format project data for display
     */
    public static function formatProjectData($projectData) {
        return [
            'project_number' => $projectData['FactoryNumber'] ?? '',
            'project_name' => $projectData['ProjectName'] ?? '',
            'product' => $projectData['Product'] ?? '',
            'panel_quantity' => $projectData['Qty'] ?? 0,
            'order_manager' => $projectData['OrderManager'] ?? '',
            'electrical_engineer' => $projectData['ElectricalEngineer'] ?? '',
            'mechanical_engineer' => $projectData['MechanicalEngineer'] ?? ''
        ];
    }

    /**
     * Validate project number format
     */
    public static function validateProjectNumber($projectNo) {
        // Add your validation logic here
        return !empty($projectNo) && strlen($projectNo) >= 3;
    }

    /**
     * Get project status label
     */
    public static function getProjectStatusLabel($statusId) {
        $statusMap = [
            1 => 'New',
            2 => 'In Progress',
            3 => 'Pending Approval',
            4 => 'Rejected',
            5 => 'Approved',
            6 => 'Withdrawn',
            7 => 'Revision Start',
            8 => 'Revision In Progress',
            9 => 'Revision Pending'
        ];
        
        return $statusMap[$statusId] ?? 'Unknown';
    }

    /**
     * Calculate days until assembly start
     */
    public static function calculateDaysUntilAssemblyStart($assemblyStartDate) {
        if (empty($assemblyStartDate)) {
            return null;
        }

        try {
            $today = new DateTime();
            $today->setTime(0, 0, 0);

            $dateParts = explode('.', $assemblyStartDate);
            if (count($dateParts) !== 3) {
                return null;
            }

            $assemblyDate = new DateTime(
                $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0]
            );
            $assemblyDate->setTime(0, 0, 0);

            $interval = $assemblyDate->diff($today);
            $days = (int)$interval->format('%r%a');

            return $days;
        } catch (Exception $e) {
            error_log("Error in calculateDaysUntilAssemblyStart: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate weeks until assembly start
     */
    public static function calculateWeeksUntilAssemblyStart($assemblyStartDate) {
        $days = self::calculateDaysUntilAssemblyStart($assemblyStartDate);
        
        if ($days === null) {
            return null;
        }

        return round($days / 7, 1);
    }

    /**
     * Get assembly status color
     */
    public static function getAssemblyStatusColor($weeks) {
        if ($weeks === null) {
            return 'gray';
        }

        if ($weeks < 6.5) {
            return 'red';
        } elseif ($weeks < 13) {
            return 'orange';
        } else {
            return 'green';
        }
    }

    /**
     * Format date for display
     */
    public static function formatDateForDisplay($date, $format = 'd.m.Y') {
        if (empty($date)) {
            return '-';
        }

        try {
            $dateObj = new DateTime($date);
            return $dateObj->format($format);
        } catch (Exception $e) {
            return $date;
        }
    }

    /**
     * Escape HTML special characters
     */
    public static function escapeHtml($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Get project file path
     */
    public static function getProjectFilePath($projectNo) {
        // Adjust this based on your file structure
        return $_SERVER['DOCUMENT_ROOT'] . "/projects/" . $projectNo;
    }

    /**
     * Log activity
     */
    public static function logActivity($action, $projectNo, $details = '') {
        try {
            $user = SharedManager::getUser();
            $username = $user['username'] ?? 'Unknown';
            
            $logMessage = "[" . date('Y-m-d H:i:s') . "] " . 
                         "User: " . $username . " | " .
                         "Action: " . $action . " | " .
                         "Project: " . $projectNo;
            
            if (!empty($details)) {
                $logMessage .= " | Details: " . $details;
            }

            error_log($logMessage);
        } catch (Exception $e) {
            error_log("Error in logActivity: " . $e->getMessage());
        }
    }
}
?>