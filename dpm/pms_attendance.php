<?php
include_once 'core/index.php';

// ===== USER AUTHENTICATION & ROLE VALIDATION =====
$userInfo = SharedManager::getUser();
$current_user_id = $userInfo['GID'] ?? null;
$userModules = $userInfo['Modules'] ?? [];
$isAdmin = 0;
$isSupervisor = 0;
$isRegularUser = 0;
$userRole = 'user';
$supervisorId = null;
$supervisorDepartments = [];
$supervisorValidation = [
    'is_valid_supervisor' => false,
    'supervised_employees' => 0,
    'message' => ''
];
$accessDeniedMessage = null;
$accessDeniedType = null;

try {
    if (isset($userInfo["Modules"]) && is_array($userInfo["Modules"])) {
        
        if (in_array(20, $userInfo["Modules"])) {
            $isAdmin = 1;
            $userRole = 'admin';
            SharedManager::checkAuthToModule(20);
            $supervisorValidation['message'] = 'Admin access granted - Full system access';
            error_log("Admin user logged in: " . ($userInfo['GID'] ?? 'Unknown'));
            
        } elseif (in_array(21, $userInfo["Modules"])) {
            $isSupervisor = 1;
            $userRole = 'supervisor';
            SharedManager::checkAuthToModule(21);
            
            $supervisorId = $userInfo['GID'] ?? null;
            
            if (!$supervisorId) {
                $supervisorValidation['is_valid_supervisor'] = false;
                $supervisorValidation['message'] = 'Error: Supervisor ID not found in user session';
                $accessDeniedMessage = 'Error: Supervisor ID not found in user session';
                $accessDeniedType = 'error';
                error_log("Supervisor validation failed: No GID found for user");
            } else {
                $pdoManager = new PDOManager('spectra_db');
                
                $checkSupervisorQuery = "SELECT gid, name, supervisor, sub_department 
                                        FROM employee_registration 
                                        WHERE gid = :supervisor_id 
                                        LIMIT 1";
                
                $supervisorCheck = $pdoManager->fetchQueryData($checkSupervisorQuery, [':supervisor_id' => $supervisorId]);
                
                if (isset($supervisorCheck['data']) && !empty($supervisorCheck['data'])) {
                    $supervisorRecord = $supervisorCheck['data'][0];
                    
                    $getEmployeesQuery = "SELECT DISTINCT sub_department 
                                            FROM employee_registration 
                                            WHERE CONCAT(',', TRIM(REPLACE(supervisor, ' ', '')), ',') LIKE :supervisor_id
                                            AND sub_department IS NOT NULL
                                            AND sub_department != ''
                                            ORDER BY sub_department ASC";

                    $employeesResult = $pdoManager->fetchQueryData($getEmployeesQuery, [':supervisor_id' => '%,' . $supervisorId . ',%']);
                    
                    if (isset($employeesResult['data']) && !empty($employeesResult['data'])) {
                        $supervisorDepartments = array_column($employeesResult['data'], 'sub_department');
                        
                        $countQuery = "SELECT COUNT(*) as total_employees 
                                        FROM employee_registration 
                                        WHERE CONCAT(',', TRIM(REPLACE(supervisor, ' ', '')), ',') LIKE :supervisor_id";
                                                
                        $countResult = $pdoManager->fetchQueryData($countQuery, [':supervisor_id' => '%,' . $supervisorId . ',%']);
                        $totalEmployees = isset($countResult['data'][0]['total_employees']) ? (int)$countResult['data'][0]['total_employees'] : 0;
                        
                        $supervisorValidation['is_valid_supervisor'] = true;
                        $supervisorValidation['supervised_employees'] = $totalEmployees;
                        $supervisorValidation['message'] = "Supervisor access granted - Managing {$totalEmployees} employee(s) in " . count($supervisorDepartments) . " department(s)";
                        
                        error_log("Supervisor {$supervisorId} validated successfully - Departments: " . implode(', ', $supervisorDepartments));
                        
                    } else {
                        $supervisorValidation['is_valid_supervisor'] = false;
                        $supervisorValidation['supervised_employees'] = 0;
                        $supervisorValidation['message'] = "Supervisor record found but no employees assigned to manage";
                        $accessDeniedMessage = "Supervisor record found but no employees assigned to manage";
                        $accessDeniedType = 'warning';
                        
                        error_log("Supervisor {$supervisorId} has no employees assigned");
                    }
                } else {
                    $supervisorValidation['is_valid_supervisor'] = false;
                    $supervisorValidation['message'] = "Error: Supervisor record not found in employee database";
                    $accessDeniedMessage = "Error: Supervisor record not found in employee database";
                    $accessDeniedType = 'error';
                    
                    error_log("Supervisor validation failed: User {$supervisorId} not found in employee_registration table");
                }
            }
        } elseif (in_array(19, $userInfo["Modules"])) {
            $isRegularUser = 1;
            $userRole = 'regular_user';
            SharedManager::checkAuthToModule(19);
            $supervisorValidation['message'] = 'User access granted - View only';
            error_log("Regular user logged in: " . ($userInfo['GID'] ?? 'Unknown'));
            
        } else {
            $userRole = 'user';
            $supervisorValidation['message'] = 'Access Denied: You do not have permission to access this module';
            $accessDeniedMessage = 'Access Denied: You do not have permission to access this module';
            $accessDeniedType = 'error';
            error_log("User attempted access without proper module permissions");
        }
    } else {
        throw new Exception("No module permissions found for user");
    }
} catch (Exception $e) {
    error_log("Error in user validation: " . $e->getMessage());
    $supervisorValidation['message'] = 'Error: ' . $e->getMessage();
    $accessDeniedMessage = 'Error: ' . htmlspecialchars($e->getMessage());
    $accessDeniedType = 'error';
}

$canAccess = ($isAdmin === 1 || ($isSupervisor === 1 && $supervisorValidation['is_valid_supervisor']) || $isRegularUser === 1);

$base_path = '/';
$dpm_path = '/dpm/';
$css_path = '/css/';
$js_path = '/js/';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Record - PMS Module</title>
    
    <!-- ===== CSS LIBRARIES - LOAD FIRST ===== -->
    <link href="../css/semantic.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.semanticui.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <link href="../css/main.css?13" rel="stylesheet"/>
    
    <?php include_once 'shared/headerStyles.php' ?>
    
    <style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

:root {
    --siemens-teal: #009999;
    --siemens-teal-dark: #007A7A;
    --siemens-teal-light: #00B8B8;
    --siemens-petrol: #005F6A;
    --siemens-petrol-light: #0A7C8C;
    --siemens-gray-light: #F2F2F2;
    --siemens-gray-medium: #E5E5E5;
    --siemens-gray-dark: #666666;
    --siemens-text: #333333;
    --siemens-white: #FFFFFF;
    --siemens-border: #CCCCCC;
    --siemens-hover: #E6F7F7;
    --transfer-bg: #CCF2F2;
    --transfer-border: #009999;
    --transfer-text: #005F6A;
    --transfer-hover: #99E6E6;
    --transfer-highlight: #66D9D9;
    
    --base-font-size: 10px;
    --base-padding: 6px;
    --base-column-width: 65px;  
    --base-name-width: 130px;   
    --base-id-width: 80px;      
    --base-hours-width: 60px;   
    --base-input-height: 32px;
    --base-button-padding: 6px 12px;
    --base-search-width: 200px;
}

html, body {
    height: 100%;
    overflow: hidden;
}

#page-wrapper {
    height: 100vh;
    overflow-y: auto;
    overflow-x: hidden;
}

.wrapper-content {
    width: 100%;
    padding: 15px;
    overflow: visible !important;
}

.ibox {
    width: 100%;
    margin: 0;
    background: white;
    border: 1px solid #e3e3e3;
    border-radius: 2px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

.ibox-content {
    width: 100%;
    overflow: visible !important;
    padding: 0;
    display: flex;
    flex-direction: column;
}

/* ===== ACCESS DENIED BANNER ===== */
.access-denied-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    margin-bottom: 15px;
    border-radius: 4px;
    border-left: 5px solid;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    animation: slideDown 0.3s ease-out;
    font-size: 12px;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.access-denied-banner.error {
    background: #FFF5F5;
    border-left-color: #D32F2F;
    color: #B71C1C;
}

.access-denied-banner.warning {
    background: #FFF8E1;
    border-left-color: #F57F17;
    color: #E65100;
}

.access-denied-banner.success {
    background: #E8F5E9;
    border-left-color: #388E3C;
    color: #1B5E20;
}

.access-denied-banner-content {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.access-denied-banner-close {
    background: none;
    border: none;
    color: inherit;
    cursor: pointer;
    font-size: 20px;
    padding: 0;
    margin-left: 12px;
    opacity: 0.7;
    transition: opacity 0.2s ease;
}

.access-denied-banner-close:hover {
    opacity: 1;
}

/* ===== TAB CONTENT ===== */
.tab-content {
    display: none;
}

.tab-content.active {
    display: block !important;
}

/* ===== TABS ===== */
.tabs {
    display: flex;
    background: transparent;
    border: none;
    margin: 0;
    padding: 0;
    gap: 0;
    overflow: visible;
    border-bottom: 1px solid var(--siemens-teal);
}

.tab-button {
    flex: 1;
    background: #f5f5f5;
    border: none;
    outline: none;
    padding: 12px 16px;
    font-size: 12px;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    border-radius: 0;
}

.tab-button i {
    margin-right: 8px;
}

.tab-button.active {
    background: white;
    color: var(--siemens-teal);
    font-weight: 700;
    border: 1px solid var(--siemens-teal);
    border-bottom: none;
    z-index: 2;
    border-radius: 4px 4px 0 0;
    margin-bottom: -3px;
}

.tab-button:not(.active):hover {
    background: #efefef;
}

/* ===== TAB CONTENT WRAPPER ===== */
.tab-content-wrapper {
    background: white;
    border: 1px solid var(--siemens-teal);
    border-top: none;
    border-radius: 0 4px 4px 4px;
    padding: 0;
    margin: 0;
    overflow: visible !important;
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
}

/* ===== FILTERS CONTAINER ===== */
.filters-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    width: 100%;
    flex-wrap: nowrap;
    margin-bottom: 0;
    overflow: visible !important;
    position: relative;
    z-index: 50;
    padding: 15px;
    background: white;
    border: none;
    border-radius: 0;
    box-sizing: border-box;
}

.dropdown-group {
    flex: 1;
    min-width: 0;
    position: relative;
    overflow: visible !important;
    z-index: 50;
}

.dropdown-group select:not(.custom-multi-select) {
    width: 100%;
    height: 32px;
    padding: 5px 24px 5px 8px;
    border: 1px solid var(--siemens-teal);
    border-radius: 3px;
    background: white;
    font-size: 10px;
    font-weight: 400;
    color: #333;
    cursor: pointer;
    transition: all 0.2s ease;
}

.dropdown-group select:not(.custom-multi-select):hover {
    border-color: var(--siemens-teal-dark);
    background: var(--siemens-hover);
    box-shadow: 0 2px 8px rgba(0, 153, 153, 0.15);
}

.dropdown-group select:not(.custom-multi-select):focus {
    outline: none;
    border-color: var(--siemens-teal-dark);
    box-shadow: 0 0 0 3px rgba(0, 153, 153, 0.25);
}

.dropdown-group select:not(.custom-multi-select):disabled {
    background: #e5e5e5;
    color: #666;
    cursor: not-allowed;
    opacity: 0.6;
}

/* ===== CUSTOM DROPDOWN WRAPPER ===== */
.custom-dropdown-wrapper {
    position: relative;
    width: 100%;
    z-index: 50;
}

.custom-dropdown-toggle {
    width: 100%;
    min-height: 32px;
    padding: 6px 24px 6px 10px;
    border: 1px solid var(--siemens-teal);
    border-radius: 3px;
    background: white;
    font-size: 10px;
    font-weight: 400;
    color: #333;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
    text-align: left;
    box-shadow: none;
    overflow: hidden;
    position: relative;
}

.custom-dropdown-toggle:hover:not(:disabled) {
    border-color: var(--siemens-teal-dark);
    background: var(--siemens-hover);
    box-shadow: 0 2px 8px rgba(0, 153, 153, 0.15);
}

.custom-dropdown-toggle.active {
    border-color: var(--siemens-teal-dark);
    background: white;
    box-shadow: 0 0 0 3px rgba(0, 153, 153, 0.25);
}

.custom-dropdown-toggle:disabled {
    background: #e5e5e5;
    color: #666;
    cursor: not-allowed;
    opacity: 0.6;
}

.custom-dropdown-toggle-text {
    flex: 1;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
    overflow: hidden;
    max-height: 48px;
    overflow-y: auto;
}

.custom-dropdown-toggle-text.empty::before {
    content: '-- Select --';
    color: #999;
    font-weight: 400;
}

.selected-tag {
    background: var(--siemens-teal);
    color: white;
    padding: 4px 10px;
    border-radius: 3px;
    font-size: 9px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    flex-shrink: 0;
    box-shadow: 0 2px 4px rgba(0, 153, 153, 0.2);
}

.selected-tag .remove-tag {
    cursor: pointer;
    font-weight: bold;
    margin-left: 2px;
}

.custom-dropdown-arrow {
    font-size: 10px;
    color: var(--siemens-teal);
    transition: transform 0.2s ease;
    margin-left: 8px;
    flex-shrink: 0;
    font-weight: bold;
}

.custom-dropdown-toggle.active .custom-dropdown-arrow {
    transform: rotate(180deg);
}

/* ===== DROPDOWN MENU - OPENS DOWNWARD ===== */
.custom-dropdown-menu {
    display: none;
    position: absolute;
    background-color: white;
    border: 2px solid var(--siemens-teal);
    border-top: none;
    border-radius: 0 0 3px 3px;
    box-shadow: 0 8px 16px rgba(0, 153, 153, 0.25);
    z-index: 10001;
    width: 100%;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 0;
    max-height: 400px;
    overflow-y: auto;
    overflow-x: hidden;
    flex-direction: column;
}

.custom-dropdown-menu.show {
    display: flex;
}

.custom-dropdown-options {
    overflow: visible;
    flex: 1;
    padding: 6px 0;
    display: flex;
    flex-direction: column;
}

.custom-dropdown-option {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    width: 100%;
    text-align: left;
    font-size: 10px;
    color: #333;
    background: none;
    font-weight: 400;
    flex-shrink: 0;
}

.custom-dropdown-option:hover {
    background: var(--siemens-hover);
    padding-left: 16px;
}

.custom-dropdown-option input[type="checkbox"] {
    margin-right: 10px;
    cursor: pointer;
    accent-color: var(--siemens-teal);
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.custom-dropdown-option label {
    margin: 0;
    cursor: pointer;
    flex: 1;
    font-weight: 400;
}

.custom-dropdown-option.select-all {
    border-bottom: 2px solid var(--siemens-teal);
    background: linear-gradient(to right, rgba(0, 153, 153, 0.05), transparent);
    font-weight: 600;
    color: var(--siemens-teal);
}

.custom-dropdown-option.select-all label {
    color: var(--siemens-teal);
    font-weight: 600;
}

/* ===== DATE RANGE INPUT ===== */
.input-daterange {
    flex: 1;
    min-width: 0;
}

#reportrange,
#overtimeReportrange {
    width: 100%;
    height: 32px;
    padding: 6px 10px;
    border: 1px solid var(--siemens-teal);
    border-radius: 3px;
    background: white;
    font-size: 10px;
    font-weight: 400;
    color: #333;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

#reportrange:hover,
#overtimeReportrange:hover {
    border-color: var(--siemens-teal-dark);
    background: var(--siemens-hover);
    box-shadow: 0 2px 8px rgba(0, 153, 153, 0.15);
}

/* ===== DATE NAVIGATION ===== */
.date-navigation {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0;
    padding: 15px;
    gap: 12px;
    flex-wrap: nowrap;
    background: white;
    border: none;
    border-top: 1px solid #e0e0e0;
    border-radius: 0 0 4px 4px;
    width: 100%;
    box-sizing: border-box;
}

.date-nav-btn {
    padding: 6px 12px;
    background: white;
    border: 2px solid var(--siemens-teal);
    border-radius: 3px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 10px;
    font-weight: 600;
    color: var(--siemens-teal);
    min-width: 45px;
    text-align: center;
    flex-shrink: 0;
}

.date-nav-btn:hover:not(.disabled) {
    background: var(--siemens-teal);
    color: white;
    box-shadow: 0 2px 8px rgba(0, 153, 153, 0.25);
}

.date-nav-btn.disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.current-week-display {
    font-size: 12px;
    font-weight: 700;
    color: var(--siemens-teal);
    text-align: center;
    padding: 0 15px;
    white-space: nowrap;
    flex-shrink: 0;
}

/* ===== TABLE CONTAINER - FIXED ===== */
.table-container {
    background: white;
    border: 2px solid var(--siemens-teal);
    border-radius: 4px;
    margin: 15px;
    overflow: visible !important;
    box-shadow: 0 2px 8px rgba(0, 153, 153, 0.15);
    position: relative;
    box-sizing: border-box;
}

.dataTables_wrapper {
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    background: transparent !important;
    overflow: visible !important;
}

/* ===== ENHANCED DATATABLE CONTROLS ===== */
.dataTables_wrapper .top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 18px;
    border-bottom: 2px solid var(--siemens-teal);
    background: linear-gradient(to bottom, #ffffff, #f8f9fa);
    margin: 0;
    width: 100%;
    box-sizing: border-box;
    gap: 20px;
    flex-wrap: nowrap;
}

.dataTables_wrapper .dataTables_length {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
    padding: 0;
    flex-shrink: 0;
}

.dataTables_wrapper .dataTables_length label {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
    padding: 0;
    font-size: 13px;
    font-weight: 600;
    color: var(--siemens-text);
    white-space: nowrap;
}

.dataTables_wrapper .dataTables_length select {
    padding: 9px 36px 9px 14px;
    border: 2px solid var(--siemens-teal);
    border-radius: 4px;
    background: white url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14"><path fill="%23009999" d="M7 10L2 5h10z"/></svg>') no-repeat right 12px center;
    background-size: 14px;
    font-size: 13px;
    font-weight: 600;
    color: var(--siemens-text);
    cursor: pointer;
    min-width: 85px;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 153, 153, 0.1);
    height: 42px;
}

.dataTables_wrapper .dataTables_length select:hover {
    border-color: var(--siemens-teal-dark);
    background-color: var(--siemens-hover);
    box-shadow: 0 2px 8px rgba(0, 153, 153, 0.2);
}

.dataTables_wrapper .dataTables_length select:focus {
    outline: none;
    border-color: var(--siemens-teal-dark);
    box-shadow: 0 0 0 3px rgba(0, 153, 153, 0.15);
}

.dataTables_wrapper .dataTables_filter {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
    padding: 0;
    flex-shrink: 0;
}

.dataTables_wrapper .dataTables_filter label {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
    padding: 0;
    font-size: 13px;
    font-weight: 600;
    color: var(--siemens-text);
    white-space: nowrap;
}

.dataTables_wrapper .dataTables_filter input {
    padding: 9px 14px 9px 40px;
    min-width: 300px;
    width: 300px;
    height: 42px;
    font-size: 13px;
    font-weight: 400;
    border: 2px solid var(--siemens-teal);
    border-radius: 4px;
    background: white url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="%23009999" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>') no-repeat left 14px center;
    background-size: 18px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 153, 153, 0.1);
    color: var(--siemens-text);
}

.dataTables_wrapper .dataTables_filter input::placeholder {
    color: #999;
    font-style: italic;
    font-weight: 400;
}

.dataTables_wrapper .dataTables_filter input:hover {
    border-color: var(--siemens-teal-dark);
    background-color: var(--siemens-hover);
    box-shadow: 0 2px 8px rgba(0, 153, 153, 0.2);
}

.dataTables_wrapper .dataTables_filter input:focus {
    outline: none;
    border-color: var(--siemens-teal-dark);
    background-color: white;
    box-shadow: 0 0 0 3px rgba(0, 153, 153, 0.15);
}

/* ===== TABLE SCROLL WRAPPER ===== */
.dataTables_wrapper .dataTables_scroll {
    width: 100% !important;
    overflow-x: auto !important;
    overflow-y: visible !important;
}

.dataTables_wrapper .dataTables_scrollBody {
    overflow-x: auto !important;
    overflow-y: visible !important;
    width: 100% !important;
}

/* ===== BOTTOM PAGINATION CONTROLS ===== */
.dataTables_wrapper .bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 18px;
    border-top: 2px solid var(--siemens-teal);
    background: linear-gradient(to top, #ffffff, #f8f9fa);
    margin: 0;
    width: 100%;
    box-sizing: border-box;
    gap: 20px;
    flex-wrap: wrap;
}

.dataTables_wrapper .dataTables_info {
    font-size: 13px;
    font-weight: 600;
    color: var(--siemens-text);
    margin: 0;
    padding: 0;
    flex-shrink: 0;
}

.dataTables_wrapper .dataTables_paginate {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 8px;
    margin: 0;
    padding: 0;
    flex-wrap: wrap;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 10px 16px;
    border: 2px solid var(--siemens-teal);
    background: white;
    color: var(--siemens-text);
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    min-width: 44px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0, 153, 153, 0.1);
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.disabled):not(.current) {
    background: var(--siemens-teal);
    color: white;
    box-shadow: 0 2px 8px rgba(0, 153, 153, 0.25);
    transform: translateY(-1px);
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: var(--siemens-teal);
    color: white;
    font-weight: 700;
    box-shadow: 0 3px 10px rgba(0, 153, 153, 0.4);
    border-color: var(--siemens-teal-dark);
}

.dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: #f5f5f5;
    color: #999;
    border-color: #ddd;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.previous,
.dataTables_wrapper .dataTables_paginate .paginate_button.next {
    font-weight: 700;
}

/* ===== TABLE STYLING ===== */
.table-professional {
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    border-collapse: collapse !important;
    background: white !important;
    font-size: 11px;
    table-layout: fixed !important;
}

.table-professional thead {
    background: white !important;
    border-bottom: 2px solid #ccc !important;
}

.table-professional thead th {
    font-weight: 700;
    font-size: 10px;
    color: #333;
    padding: 10px 8px !important;
    text-align: center;
    vertical-align: middle;
    white-space: normal;
    border: none !important;
    border-right: 1px solid #ddd !important;
    background: white !important;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    line-height: 1.4;
}

.table-professional thead th:last-child {
    border-right: none !important;
}

.table-professional tbody tr {
    border-bottom: 1px solid #ddd !important;
    background: white !important;
    position: relative;
}

.table-professional tbody tr:hover {
    background: #f9f9f9 !important;
}

.table-professional tbody td {
    font-size: 11px;
    color: #333;
    padding: 8px !important;
    text-align: center;
    vertical-align: middle;
    border: none !important;
    border-right: 1px solid #ddd !important;
    background: white !important;
    position: relative;
    overflow: visible !important;
}

.table-professional tbody td:nth-child(3),
.table-professional thead th:nth-child(3) {
    width: 80px !important;
    min-width: 80px !important;
    max-width: 80px !important;
    overflow: visible !important;
    text-overflow: clip !important;
    word-wrap: break-word !important;
    white-space: normal !important;
    line-height: 1.3 !important;
    padding: 8px 4px !important;
}

.table-professional tbody td:nth-child(1),
.table-professional thead th:nth-child(1) {
    width: 100px !important;
    min-width: 100px !important;
}

.table-professional tbody td:nth-child(2),
.table-professional thead th:nth-child(2) {
    min-height: 40px;
    max-height: 40px;
    height: 40px;
    padding: 8px !important;
    display: table-cell;
    vertical-align: middle;
    width: 80px !important;
    min-width: 80px !important;
    font-weight: 600;
    color: #333;
    letter-spacing: 0.5px;
    line-height: 1.4;
}

/* ===== TOTAL OT HOURS COLUMN (3rd column) ===== */
.table-professional tbody td:nth-child(3),
.table-professional thead th:nth-child(3) {
    min-height: 40px;
    max-height: 40px;
    height: 40px;
    padding: 8px !important;
    display: table-cell;
    vertical-align: middle;
    width: 80px !important;
    min-width: 80px !important;
    font-weight: 600;
    color: var(--siemens-teal);
}

/* ===== COMPACT ATTENDANCE DROPDOWNS ===== */
.attendance-select {
    width: 100% !important;
    min-width: 60px !important;
    max-width: 100% !important;
    padding: 4px 6px !important;
    border: 2px solid var(--siemens-teal) !important;
    border-radius: 3px !important;
    background: white !important;
    font-size: 9px !important;
    font-weight: 500 !important;
    color: #333 !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    appearance: auto !important;
    -webkit-appearance: menulist !important;
    -moz-appearance: menulist !important;
}

.attendance-cell {
    padding: 4px !important;
}

.attendance-select:hover:not(:disabled) {
    border-color: var(--siemens-teal-dark) !important;
    background: var(--siemens-hover) !important;
    box-shadow: 0 2px 6px rgba(0, 153, 153, 0.2) !important;
}

.attendance-select:focus {
    outline: none !important;
    border-color: var(--siemens-teal-dark) !important;
    box-shadow: 0 0 0 3px rgba(0, 153, 153, 0.15) !important;
}

.attendance-select:disabled {
    background: #f5f5f5 !important;
    color: #999 !important;
    cursor: not-allowed !important;
    opacity: 0.7 !important;
}

/* ===== MODALS ===== */
#mai_spinner_page {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}

#mai_spinner_page.active {
    display: flex;
}

#mai_spinner_page::after {
    content: '';
    width: 60px;
    height: 60px;
    border: 5px solid rgba(0, 153, 153, 0.2);
    border-top: 5px solid var(--siemens-teal);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* ===== SCROLLBAR ===== */
::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: var(--siemens-teal);
    border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--siemens-teal-dark);
}

/* ===== COMPACT DATE COLUMNS ===== */
.col-date {
    width: 65px !important;
    min-width: 65px !important;
    max-width: 65px !important;
    padding: 4px !important;
}

/* ===== COMPACT DATE HEADER ===== */
.date-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    padding: 6px 4px;
    font-size: 9px;
    line-height: 1.2;
}

.date-header .day-name {
    font-size: 8px;
    font-weight: 700;
    color: var(--siemens-teal);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.date-header .button-group {
    display: flex;
    gap: 3px;
    margin-top: 3px;
    justify-content: center;
    flex-wrap: wrap;
}

.date-header button {
    padding: 3px 6px;
    font-size: 9px;
    border: 1px solid #ddd;
    background: #fff;
    color: #333;
    border-radius: 2px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 3px;
}

.date-header button:hover:not(:disabled) {
    background: var(--siemens-teal);
    color: white;
    border-color: var(--siemens-teal);
}

.date-header button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.date-header button i {
    font-size: 8px;
}

/* ===== COMPACT DAY OFF CELL ===== */
.holiday-cell {
    padding: 2px 4px !important;
    background: white !important;
    border-radius: 3px;
    border: 2px solid var(--siemens-teal) !important;
    min-height: 24px !important;
    max-height: 24px !important;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 9px !important;  
}

.holiday-text {
    color: var(--siemens-gray-dark);
    font-weight: 600;
    font-size: 10px !important;
    text-align: center;
    line-height: 1.2;
}

.holiday-header {
    background: linear-gradient(to bottom, #f8f8f8, #efefef);
    padding: 6px 4px;
}

.holiday-label {
    font-size: 8px !important;
    font-weight: 700;
    color: var(--siemens-gray-dark);
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-top: 2px;
    padding: 2px 4px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 2px;
}

/* ===== COMPACT BADGES AND INDICATORS ===== */
.admin-edit-indicator,
.can-update-badge {
    display: inline-block !important;
    margin-left: 4px !important;
    padding: 2px 4px !important;
    background: var(--siemens-teal) !important;
    color: var(--siemens-white) !important;
    font-size: 8px !important;
    font-weight: 600 !important;
    border-radius: 2px !important;
    white-space: nowrap !important;
    vertical-align: middle !important;
    line-height: 1.2 !important;
}

.leave-indicator {
    display: inline-block !important;
    margin-left: 4px !important;
    padding: 2px 4px !important;
    background: #FFA726 !important;
    color: white !important;
    font-size: 8px !important;
    font-weight: 600 !important;
    border-radius: 2px !important;
    white-space: nowrap !important;
    vertical-align: middle !important;
    line-height: 1.2 !important;
}

/* ===== STATUS BADGE COMPACT ===== */
.status-badge {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 2px;
    font-size: 8px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.status-badge.saved {
    background: #FFF3CD;
    color: #856404;
    border: 1px solid #FFEAA7;
}

.status-badge.submitted {
    background: #D4EDDA;
    color: #155724;
    border: 1px solid #C3E6CB;
}

.status-badge.incomplete {
    background: #FFECB3;
    color: #FF8F00;
    border: 1px solid #FFD54F;
}

/* ===== CUSTOM ALERT MODAL ===== */
#customAlertModal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 10000;
    justify-content: center;
    align-items: center;
}

#customAlertModal.show {
    display: flex;
}

.alert-modal-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    max-width: 480px;
    width: 85%;
    max-height: 75vh;
    overflow-y: auto;
    animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
    from {
        transform: translateY(30px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.alert-modal-header {
    padding: 18px 20px 12px 20px;
    border-bottom: 2px solid #e0e0e0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-modal-header.error {
    border-bottom-color: #d32f2f;
    background: #ffebee;
}

.alert-modal-header.warning {
    border-bottom-color: #f57f17;
    background: #fff8e1;
}

.alert-modal-header.success {
    border-bottom-color: #388e3c;
    background: #e8f5e9;
}

.alert-modal-header.info {
    border-bottom-color: #1976d2;
    background: #e3f2fd;
}

.alert-modal-header i {
    font-size: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.alert-modal-header.error i {
    color: #d32f2f;
}

.alert-modal-header.warning i {
    color: #f57f17;
}

.alert-modal-header.success i {
    color: #388e3c;
}

.alert-modal-header.info i {
    color: #1976d2;
}

.alert-modal-title {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #333;
}

.alert-modal-header.error .alert-modal-title {
    color: #d32f2f;
}

.alert-modal-header.warning .alert-modal-title {
    color: #f57f17;
}

.alert-modal-header.success .alert-modal-title {
    color: #388e3c;
}

.alert-modal-header.info .alert-modal-title {
    color: #1976d2;
}

.alert-modal-body {
    padding: 18px 20px;
    color: #555;
}

.alert-modal-message {
    margin: 0 0 12px 0;
    font-size: 13px;
    line-height: 1.5;
    color: #333;
    font-weight: 500;
}

#alertList {
    list-style: none;
    padding: 0;
    margin: 0 0 12px 0;
    background: #f5f5f5;
    border-radius: 4px;
    border-left: 4px solid var(--siemens-teal);
    overflow: hidden;
    max-height: 300px;
    overflow-y: auto;
}

#alertList li {
    padding: 8px 12px;
    border-bottom: 1px solid #e0e0e0;
    font-size: 12px;
    color: #555;
    display: flex;
    align-items: center;
    gap: 6px;
}

#alertList li:last-child {
    border-bottom: none;
}

#alertList li[data-page-header="true"] {
    background: var(--siemens-teal);
    color: white;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 10px;
    letter-spacing: 0.5px;
    border-bottom: 2px solid var(--siemens-teal-dark);
    padding: 6px 12px;
}

#alertList li[data-page-header="true"]::before {
    content: '📄';
    font-size: 12px;
    flex-shrink: 0;
}

#alertList li[data-employee="true"]::before {
    content: '👤';
    font-size: 11px;
    flex-shrink: 0;
}

#alertList li span {
    flex: 1;
    word-break: break-word;
}

.alert-modal-note {
    padding: 10px 12px;
    background: #f0f8f8;
    border-radius: 4px;
    border-left: 4px solid var(--siemens-teal);
    font-size: 11px;
    color: #0088a0;
    line-height: 1.4;
    margin-bottom: 0;
}

.alert-modal-footer {
    padding: 12px 20px;
    border-top: 1px solid #e0e0e0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    background: #f9f9f9;
}

#alertOkBtn {
    padding: 8px 28px;
    background: var(--siemens-teal);
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    min-width: 90px;
    text-align: center;
}

#alertOkBtn:hover {
    background: var(--siemens-teal-dark);
    box-shadow: 0 2px 8px rgba(0, 153, 153, 0.3);
    transform: translateY(-1px);
}

#alertOkBtn:active {
    transform: translateY(0);
}

#alertList::-webkit-scrollbar {
    width: 5px;
}

#alertList::-webkit-scrollbar-track {
    background: #f1f1f1;
}

#alertList::-webkit-scrollbar-thumb {
    background: var(--siemens-teal);
    border-radius: 3px;
}

#alertList::-webkit-scrollbar-thumb:hover {
    background: var(--siemens-teal-dark);
}

/* ===== TRANSFER INFO ICON STYLES ===== */
.transfer-info-icon-wrapper {
    position: relative;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: help;
    flex-shrink: 0;
    height: 16px !important;
    width: 16px !important;
    margin: 0 !important;
    padding: 0 !important;
    z-index: 100;
}

.transfer-info-icon-wrapper i {
    font-size: 12px !important;
    color: var(--siemens-teal);
    transition: all 0.3s ease;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 16px !important;
    height: 16px !important;
    border-radius: 50%;
    background: rgba(0, 169, 169, 0.08);
    margin: 0 !important;
    padding: 0 !important;
    line-height: 1 !important;
    cursor: pointer;
}

.transfer-info-icon-wrapper i:hover {
    font-size: 13px !important;
    color: #0088a0;
    background: rgba(0, 169, 169, 0.15);
    text-shadow: 0 0 8px rgba(0, 169, 169, 0.3);
    transform: scale(1.1);
}

/* ===== TRANSFER INFO TOOLTIP ===== */
.transfer-info-tooltip {
    display: none !important;
    position: fixed;
    background: white;
    color: #333;
    padding: 14px 16px;
    border-radius: 8px;
    font-size: 12px;
    white-space: normal;
    z-index: 10002;
    font-weight: 500;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    border: 1.5px solid var(--siemens-teal);
    background: linear-gradient(135deg, #f0f8f8 0%, #ffffff 100%);
    min-width: 280px;
    max-width: 320px;
    animation: tooltipFadeInUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    pointer-events: auto;
    visibility: hidden;
    opacity: 0;
    transition: opacity 0.2s ease, visibility 0.2s ease;
}

/* ===== SHOW TOOLTIP ONLY ON WRAPPER HOVER ===== */
.transfer-info-icon-wrapper:hover .transfer-info-tooltip {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.transfer-info-tooltip::before {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 10px solid transparent;
    border-right: 10px solid transparent;
    border-top: 10px solid var(--siemens-teal);
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
}

.transfer-info-tooltip.tooltip-below {
    bottom: auto;
}

.transfer-info-tooltip.tooltip-below::before {
    bottom: auto;
    top: -10px;
    border-top: none;
    border-bottom: 10px solid var(--siemens-teal);
}

.transfer-info-tooltip.tooltip-left {
    left: auto;
    right: auto;
    margin-right: 0;
    margin-bottom: 0;
}

.transfer-info-tooltip.tooltip-left::before {
    left: auto;
    right: -10px;
    transform: translateX(0);
    border-left: 10px solid var(--siemens-teal);
    border-right: none;
    border-top: 10px solid transparent;
    border-bottom: 10px solid transparent;
}

.transfer-info-tooltip.tooltip-right {
    left: auto;
    right: auto;
    margin-left: 0;
    margin-bottom: 0;
}

.transfer-info-tooltip.tooltip-right::before {
    left: -10px;
    right: auto;
    transform: translateX(0);
    border-right: 10px solid var(--siemens-teal);
    border-left: none;
    border-top: 10px solid transparent;
    border-bottom: 10px solid transparent;
}

.transfer-info-tooltip-content {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.transfer-info-tooltip-row {
    display: flex;
    gap: 10px;
    align-items: flex-start;
}

.transfer-info-tooltip-label {
    font-weight: 700;
    color: var(--siemens-teal);
    min-width: 70px;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    flex-shrink: 0;
}

.transfer-info-tooltip-value {
    color: #333;
    font-size: 12px;
    font-weight: 600;
    word-break: break-word;
    flex: 1;
}

.transfer-info-tooltip-value.status {
    color: #FF8F00;
    font-weight: 700;
    font-size: 12px;
    background: #FFF3E0;
    padding: 4px 8px;
    border-radius: 4px;
    display: inline-block;
}

@keyframes tooltipFadeInUp {
    from {
        opacity: 0;
        transform: translateY(8px);
        visibility: hidden;
    }
    to {
        opacity: 1;
        transform: translateY(0);
        visibility: visible;
    }
}

/* ===== N/A CELL ===== */
.not-in-dept-cell {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 26px !important;
    min-height: 26px !important;
    max-height: 26px !important;
    gap: 4px;
    padding: 4px 6px !important;
    background: #f9f9f9 !important;
    border-radius: 3px !important;
    position: relative;
    border: 1px solid #ddd !important;
    margin-top: 8px !important;
}

.not-in-dept-text {
    font-weight: 600;
    color: #999;
    font-size: 10px;
    letter-spacing: 0.5px;
    white-space: nowrap;
    margin: 0 !important;
    padding: 0 !important;
    line-height: 1.2 !important;
}

.employee-name-wrapper.has-active-transfer {
    background: transparent !important;
    padding: 0 !important;
    border: none !important;
    border-left: none !important;
}

.emp-transfer-info {
    display: none !important;
}

.transfer-duration {
    display: none !important;
}

.transfer-days-badge {
    display: none !important;
}

/* ===== RESPONSIVE DESIGN ===== */
@media screen and (max-width: 1400px) {
    .dataTables_wrapper .dataTables_filter input {
        min-width: 250px;
        width: 250px;
    }
    
    .transfer-info-tooltip {
        min-width: 200px;
        max-width: 260px;
    }
}

@media screen and (max-width: 1200px) {
    .filters-container {
        flex-wrap: wrap;
    }
    
    .dropdown-group {
        flex: 1 1 calc(50% - 5px);
    }
    
    .dataTables_wrapper .top {
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        min-width: 220px;
        width: 220px;
    }
    
    .transfer-info-tooltip {
        min-width: 270px;
        max-width: 310px;
    }
}

@media screen and (max-width: 768px) {
    .filters-container {
        flex-direction: column;
    }
    
    .dropdown-group,
    .input-daterange {
        flex: 1 1 100%;
    }
    
    .date-navigation {
        flex-wrap: wrap;
    }
    
    .dataTables_wrapper .top {
        flex-direction: column;
        align-items: stretch;
    }
    
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        width: 100%;
        justify-content: space-between;
    }
    
    .dataTables_wrapper .dataTables_filter input {
        flex: 1;
        min-width: 0;
        width: auto;
    }
    
    .dataTables_wrapper .bottom {
        flex-direction: column;
        align-items: stretch;
    }
    
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        width: 100%;
        justify-content: center;
    }
    
    .transfer-info-tooltip {
        font-size: 11px;
        padding: 12px 14px;
        min-width: 260px;
        max-width: 300px;
    }
    
    .transfer-info-tooltip-label {
        min-width: 65px;
        font-size: 10px;
    }
    
    .transfer-info-tooltip-value {
        font-size: 11px;
    }
}

@media screen and (max-width: 480px) {
    .alert-modal-content {
        width: 95%;
        max-width: 400px;
    }
    
    .alert-modal-header {
        padding: 14px 14px 8px 14px;
    }
    
    .alert-modal-header i {
        font-size: 20px;
    }
    
    .alert-modal-title {
        font-size: 15px;
    }
    
    .alert-modal-body {
        padding: 14px;
    }
    
    .alert-modal-message {
        font-size: 11px;
        margin-bottom: 10px;
    }
    
    #alertList {
        margin-bottom: 10px;
        max-height: 250px;
    }
    
    #alertList li {
        padding: 5px 8px;
        font-size: 10px;
        gap: 4px;
    }
    
    .alert-modal-note {
        padding: 6px 8px;
        font-size: 9px;
    }
    
    .alert-modal-footer {
        padding: 8px 14px;
        gap: 8px;
    }
    
    #alertOkBtn {
        padding: 6px 20px;
        font-size: 11px;
        min-width: 70px;
    }
    
    .transfer-info-tooltip {
        font-size: 10px;
        padding: 10px 12px;
        min-width: 240px;
        max-width: 280px;
    }
    
    .transfer-info-tooltip-label {
        min-width: 60px;
        font-size: 9px;
    }
    
    .transfer-info-tooltip-value {
        font-size: 10px;
    }
}

.employee-name-wrapper {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 8px 0;
}

.employee-name-wrapper.has-active-transfer {
    background: #e0f7f6;
    padding: 8px;
    border-radius: 4px;
    border-left: 4px solid #00a9a9;
    margin: -8px 0;
}

.emp-name-text {
    color: var(--siemens-text);
    font-weight: 700;
    font-size: 13px;
    line-height: 1.4;
}

.emp-transfer-info {
    font-size: 11px;
    color: #00a9a9;
    margin-top: 2px;
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}

.emp-transfer-info i {
    font-size: 12px;
    color: #00a9a9;
}

.transfer-text {
    font-weight: 600;
    font-size: 11px;
    color: #00a9a9;
}

.transfer-text strong {
    color: #0088a0;
    font-weight: 700;
    text-decoration: underline;
}

.transfer-duration {
    font-size: 10px;
    color: #0088a0;
    margin-top: 2px;
    font-style: italic;
    padding-left: 16px;
}

.transfer-days-badge {
    font-size: 10px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 3px;
    margin-top: 4px;
    display: inline-block;
    padding-left: 16px;
}

.transfer-days-badge.transfer-active {
    background: #d4f1f0;
    color: #00a9a9;
    border-left: 2px solid #00a9a9;
}

.transfer-days-badge.transfer-last-day {
    background: #fff3cd;
    color: #ff8f00;
    border-left: 2px solid #ff8f00;
}
    </style>
</head>
<body>
<?php $activePage = '/dpm/pms_attendance.php'; ?>
<div id="wrapper">
    <?php include_once 'shared/pms_sidebar.php' ?>
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">
                    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary" href="#">
                        <i class="fa fa-bars"></i>
                    </a>
                </div>
                <ul class="nav navbar-top-links navbar-right">
                    <li><h2>Attendance Record</h2></li>
                    <li style="margin-right: 20px;">
                        <?php if ($isAdmin): ?>
                            <span class="admin-indicator" style="display: inline-block; padding: 6px 12px; border-radius: 3px; font-size: 11px; font-weight: 700; background: var(--siemens-petrol); color: white;">
                                <i class="fa fa-shield"></i> ADMIN
                            </span>
                        <?php elseif ($isSupervisor): ?>
                            <span class="supervisor-indicator" style="display: inline-block; padding: 6px 12px; border-radius: 3px; font-size: 11px; font-weight: 700; background: var(--siemens-teal); color: white;">
                                <i class="fa fa-user-tie"></i> SUPERVISOR
                            </span>
                        <?php elseif ($isRegularUser): ?>
                            <span class="user-indicator" style="display: inline-block; padding: 6px 12px; border-radius: 3px; font-size: 11px; font-weight: 700; background: #666; color: white;">
                                <i class="fa fa-user"></i> USER
                            </span>
                        <?php endif; ?>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="wrapper wrapper-content">
            <?php if ($accessDeniedMessage): ?>
                <div class="access-denied-banner <?php echo htmlspecialchars($accessDeniedType); ?>" id="accessDeniedBanner">
                    <div class="access-denied-banner-content">
                        <i class="fa fa-<?php echo ($accessDeniedType === 'error') ? 'exclamation-circle' : (($accessDeniedType === 'warning') ? 'exclamation-triangle' : 'check-circle'); ?>"></i>
                        <div>
                            <strong><?php echo ucfirst($accessDeniedType); ?></strong><br>
                            <?php echo htmlspecialchars($accessDeniedMessage); ?>
                        </div>
                    </div>
                    <button class="access-denied-banner-close" onclick="document.getElementById('accessDeniedBanner').style.display='none';">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <div id="mai_spinner_page"></div>

            <div id="customAlertModal">
                <div class="alert-modal-content">
                    <div class="alert-modal-header" id="alertHeader">
                        <i id="alertIcon"></i>
                        <h2 class="alert-modal-title" id="alertTitle"></h2>
                    </div>
                    <div class="alert-modal-body">
                        <p class="alert-modal-message" id="alertMessage"></p>
                        <ul id="alertList" style="display: none;"></ul>
                        <div id="alertNote" class="alert-modal-note" style="display: none;"></div>
                    </div>
                    <div class="alert-modal-footer">
                        <button id="alertOkBtn">OK</button>
                    </div>
                </div>
            </div>

            <div class="ibox">
                <div class="ibox-content">
                    <div class="tabs">
                        <button class="tab-button active" onclick="showTab('attendance'); return false;">
                            <i class="fa fa-clock-o"></i> Attendance Record
                        </button>
                        <button class="tab-button" onclick="showTab('overtime'); return false;">
                            <i class="fa fa-plus-circle"></i> Extra Hours
                        </button>
                    </div>

                    <!-- ATTENDANCE TAB -->
                    <div id="attendanceTab" class="tab-content-wrapper tab-content active">
                        <div class="filters-container">
                            <div class="dropdown-group">
                                <div class="custom-dropdown-wrapper" id="deptDropdownAttendance">
                                    <button class="custom-dropdown-toggle" id="sub_departmentToggle" <?php echo ($isRegularUser ? 'disabled' : ''); ?>>
                                        <span class="custom-dropdown-toggle-text empty" id="sub_departmentLabel"></span>
                                        <i class="custom-dropdown-arrow">▼</i>
                                    </button>
                                    <div class="custom-dropdown-menu" id="sub_departmentMenu">
                                        <div class="custom-dropdown-options" id="sub_departmentOptions"></div>
                                    </div>
                                </div>
                                <input type="hidden" id="sub_departmentSelect" data-type="multi" />
                            </div>

                            <div class="dropdown-group">
                                <select id="group_typeSelect" <?php echo ($isRegularUser ? 'disabled' : ''); ?>>
                                    <option value="">-- Select Group --</option>
                                    <option value="Both">Both</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>

                            <div class="dropdown-group">
                                <div class="custom-dropdown-wrapper" id="empDropdownAttendance">
                                    <button class="custom-dropdown-toggle" id="employment_typeToggle" <?php echo ($isRegularUser ? 'disabled' : ''); ?>>
                                        <span class="custom-dropdown-toggle-text empty" id="employment_typeLabel"></span>
                                        <i class="custom-dropdown-arrow">▼</i>
                                    </button>
                                    <div class="custom-dropdown-menu" id="employment_typeMenu">
                                        <div class="custom-dropdown-options" id="employment_typeOptions"></div>
                                    </div>
                                </div>
                                <input type="hidden" id="employment_typeSelect" data-type="multi" />
                            </div>

                            <div class="dropdown-group">
                                <select id="joinedSelect" <?php echo ($isRegularUser ? 'disabled' : ''); ?>>
                                    <option value="">-- Select Option --</option>
                                    <option value="before">Before</option>
                                    <option value="after" selected>After</option>
                                </select>
                            </div>

                            <div class="input-daterange">
                                <div id="reportrange" class="form-control" <?php echo ($isRegularUser ? 'style="pointer-events: none; opacity: 0.6;"' : ''); ?>>
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span></span> <i class="fa fa-caret-down"></i>
                                </div>
                            </div>
                        </div>

                        <div class="date-navigation">
                            <button class="date-nav-btn prev-week" <?php echo ($isRegularUser ? 'disabled' : ''); ?>>
                                <i class="fa fa-arrow-left"></i>
                            </button>
                            <span class="current-week-display"></span>
                            <button class="date-nav-btn next-week" <?php echo ($isRegularUser ? 'disabled' : ''); ?>>
                                <i class="fa fa-arrow-right"></i>
                            </button>
                        </div>

                        <div id="detailsegment" style="display: none;">
                            <div class="table-container">
                                <table id="table_all_items" class="ui celled very small compact table table-professional"></table>
                            </div>
                        </div>
                    </div>

                    <!-- OVERTIME TAB -->
                    <div id="overtimeTab" class="tab-content-wrapper tab-content">
                        <div class="filters-container">
                            <div class="dropdown-group">
                                <div class="custom-dropdown-wrapper" id="deptDropdownOvertime">
                                    <button class="custom-dropdown-toggle" id="overtimeSub_departmentToggle" <?php echo ($isRegularUser ? 'disabled' : ''); ?>>
                                        <span class="custom-dropdown-toggle-text empty" id="overtimeSub_departmentLabel"></span>
                                        <i class="custom-dropdown-arrow">▼</i>
                                    </button>
                                    <div class="custom-dropdown-menu" id="overtimeSub_departmentMenu">
                                        <div class="custom-dropdown-options" id="overtimeSub_departmentOptions"></div>
                                    </div>
                                </div>
                                <input type="hidden" id="overtimeSub_departmentSelect" data-type="multi" />
                            </div>

                            <div class="dropdown-group">
                                <select id="overtimeGroup_typeSelect" <?php echo ($isRegularUser ? 'disabled' : ''); ?>>
                                    <option value="">-- Select Group --</option>
                                    <option value="Both">Both</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="NA">NA</option>
                                </select>
                            </div>

                            <div class="dropdown-group">
                                <div class="custom-dropdown-wrapper" id="empDropdownOvertime">
                                    <button class="custom-dropdown-toggle" id="overtimeemployment_typeToggle" <?php echo ($isRegularUser ? 'disabled' : ''); ?>>
                                        <span class="custom-dropdown-toggle-text empty" id="overtimeemployment_typeLabel"></span>
                                        <i class="custom-dropdown-arrow">▼</i>
                                    </button>
                                    <div class="custom-dropdown-menu" id="overtimeemployment_typeMenu">
                                        <div class="custom-dropdown-options" id="overtimeemployment_typeOptions"></div>
                                    </div>
                                </div>
                                <input type="hidden" id="overtimeemployment_typeSelect" data-type="multi" />
                            </div>

                            <div class="dropdown-group">
                                <select id="overtimejoinedSelect" <?php echo ($isRegularUser ? 'disabled' : ''); ?>>
                                    <option value="">-- Select Option --</option>
                                    <option value="before">Before</option>
                                    <option value="after" selected>After</option>
                                </select>
                            </div>

                            <div class="input-daterange">
                                <div id="overtimeReportrange" class="form-control" <?php echo ($isRegularUser ? 'style="pointer-events: none; opacity: 0.6;"' : ''); ?>>
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span></span> <i class="fa fa-caret-down"></i>
                                </div>
                            </div>
                        </div>

                        <div class="date-navigation">
                            <button class="date-nav-btn prev-week" <?php echo ($isRegularUser ? 'disabled' : ''); ?>>
                                <i class="fa fa-arrow-left"></i>
                            </button>
                            <span class="current-week-display"></span>
                            <button class="date-nav-btn next-week" <?php echo ($isRegularUser ? 'disabled' : ''); ?>>
                                <i class="fa fa-arrow-right"></i>
                            </button>
                        </div>

                        <div id="overtimeDetailsegment" style="display: none;">
                            <div class="table-container">
                                <table id="table_overtime_items" class="ui celled very small compact table table-professional"></table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var USER_IS_ADMIN = <?php echo $isAdmin; ?>;
    var USER_IS_SUPERVISOR = <?php echo $isSupervisor; ?>;
    var USER_IS_REGULAR = <?php echo $isRegularUser; ?>;
    var USER_ROLE = '<?php echo htmlspecialchars($userRole); ?>';
    var SUPERVISOR_ID = <?php echo $supervisorId ? "'" . htmlspecialchars($supervisorId) . "'" : "null"; ?>;
    var SUPERVISOR_DEPARTMENTS = <?php echo json_encode($supervisorDepartments); ?>;
    var SUPERVISOR_VALIDATION = <?php echo json_encode($supervisorValidation); ?>;
    var CAN_ACCESS = <?php echo $canAccess ? 'true' : 'false'; ?>;
    
    const DEPARTMENT_OPTIONS = <?php 
        if ($isAdmin) {
            echo json_encode(['700', '704', '720', '750', 'Mechanical Engineering', 'Product Care', 
                'warehouse', 'packing', 'QC - AISP Domestic', 'QC - AISP Export', 
                'QC - AISP TF', 'QC - AISP', 'QC - SD', 'QC - INSP']);
        } else {
            echo json_encode($supervisorDepartments);
        }
    ?>;

    const EMPLOYMENT_OPTIONS = [
        'Blue Collar', 'Blue Collar Learner', 'Blue Collar Trainee', 'Blue Collar Contract', 
        'White Collar', 'White Collar Contract'
    ];
</script>

<!-- ===== TRANSFER TOOLTIP POSITIONING SCRIPT ===== -->
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // Position tooltip on wrapper hover
        document.addEventListener('mouseenter', function(e) {
            if (e.target.closest('.transfer-info-icon-wrapper')) {
                const wrapper = e.target.closest('.transfer-info-icon-wrapper');
                const tooltip = wrapper.querySelector('.transfer-info-tooltip');
                if (tooltip) {
                    adjustTooltipPosition(tooltip, wrapper);
                }
            }
        }, true);
    });

    /**
     * Adjust tooltip position based on available space
     */
    function adjustTooltipPosition(tooltip, wrapper) {
        // Remove all positioning classes
        tooltip.classList.remove('tooltip-below', 'tooltip-left', 'tooltip-right');
        
        // Get positions and dimensions
        const wrapperRect = wrapper.getBoundingClientRect();
        const viewportHeight = window.innerHeight;
        const viewportWidth = window.innerWidth;
        
        // Estimated dimensions
        const tooltipHeight = 200;
        const tooltipWidth = 300;
        
        // Calculate center position of wrapper
        const wrapperCenterX = wrapperRect.left + wrapperRect.width / 2;
        
        // Default: Show above
        let tooltipTop = wrapperRect.top - tooltipHeight - 15;
        let tooltipLeft = wrapperCenterX - tooltipWidth / 2;
        
        // Check if tooltip goes above viewport
        if (tooltipTop < 10) {
            // Show below instead
            tooltipTop = wrapperRect.bottom + 15;
            tooltip.classList.add('tooltip-below');
        }
        
        // Check if tooltip goes beyond right edge
        if (tooltipLeft + tooltipWidth > viewportWidth - 10) {
            // Align to right edge
            tooltipLeft = viewportWidth - tooltipWidth - 10;
            tooltip.classList.add('tooltip-right');
        }
        
        // Check if tooltip goes beyond left edge
        if (tooltipLeft < 10) {
            // Align to left edge
            tooltipLeft = 10;
            tooltip.classList.add('tooltip-left');
        }
        
        // Apply positioning
        tooltip.style.position = 'fixed';
        tooltip.style.top = tooltipTop + 'px';
        tooltip.style.left = tooltipLeft + 'px';
        tooltip.style.right = 'auto';
        tooltip.style.bottom = 'auto';
        tooltip.style.transform = 'none';
        tooltip.style.margin = '0';
    }

    /**
     * Re-adjust tooltips on window resize
     */
    window.addEventListener('resize', function() {
        const wrappers = document.querySelectorAll('.transfer-info-icon-wrapper:hover');
        wrappers.forEach(wrapper => {
            const tooltip = wrapper.querySelector('.transfer-info-tooltip');
            if (tooltip) {
                adjustTooltipPosition(tooltip, wrapper);
            }
        });
    });

    /**
     * Re-adjust tooltips on scroll
     */
    window.addEventListener('scroll', function() {
        const wrappers = document.querySelectorAll('.transfer-info-icon-wrapper:hover');
        wrappers.forEach(wrapper => {
            const tooltip = wrapper.querySelector('.transfer-info-tooltip');
            if (tooltip) {
                adjustTooltipPosition(tooltip, wrapper);
            }
        });
    }, true);
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.semanticui.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="./shared/shared.js"></script>
<script src="./attendance.js"></script>

</body>
</html>