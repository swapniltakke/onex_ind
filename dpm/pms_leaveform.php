<!DOCTYPE html>
<html>
<?php
include_once 'core/index.php';

// ===== USER AUTHENTICATION & ROLE VALIDATION =====
$userInfo = SharedManager::getUser();
$isAdmin = 0;
$isSupervisor = 0;
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
            error_log("✅ Admin user logged in: " . ($userInfo['GID'] ?? 'Unknown'));
            
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
                error_log("❌ Supervisor validation failed: No GID found for user");
            } else {
                $pdoManager = new PDOManager('spectra_db');
                
                // ===== CHECK IF SUPERVISOR EXISTS IN employee_registration =====
                $checkSupervisorQuery = "SELECT gid, name, supervisor, sub_department 
                                        FROM employee_registration 
                                        WHERE gid = :supervisor_id 
                                        AND (status = 'A' OR status IS NULL)
                                        LIMIT 1";
                
                $supervisorCheck = $pdoManager->fetchQueryData($checkSupervisorQuery, [':supervisor_id' => $supervisorId]);
                
                if (isset($supervisorCheck['data']) && !empty($supervisorCheck['data'])) {
                    $supervisorRecord = $supervisorCheck['data'][0];
                    
                    // ===== GET EMPLOYEES UNDER THIS SUPERVISOR =====
                    $getEmployeesQuery = "SELECT DISTINCT sub_department 
                                         FROM employee_registration 
                                         WHERE CONCAT(',', TRIM(REPLACE(supervisor, ' ', '')), ',') LIKE :supervisor_id
                                         AND sub_department IS NOT NULL
                                         AND sub_department != ''
                                         AND (status = 'A' OR status IS NULL)
                                         ORDER BY sub_department ASC";
                    
                    $employeesResult = $pdoManager->fetchQueryData($getEmployeesQuery, [':supervisor_id' => '%,' . $supervisorId . ',%']);
                    
                    if (isset($employeesResult['data']) && !empty($employeesResult['data'])) {
                        $supervisorDepartments = array_column($employeesResult['data'], 'sub_department');
                        
                        $countQuery = "SELECT COUNT(*) as total_employees 
                                      FROM employee_registration 
                                      WHERE CONCAT(',', TRIM(REPLACE(supervisor, ' ', '')), ',') LIKE :supervisor_id
                                      AND (status = 'A' OR status IS NULL)";
                        
                        $countResult = $pdoManager->fetchQueryData($countQuery, [':supervisor_id' => '%,' . $supervisorId . ',%']);
                        $totalEmployees = isset($countResult['data'][0]['total_employees']) ? (int)$countResult['data'][0]['total_employees'] : 0;
                        
                        $supervisorValidation['is_valid_supervisor'] = true;
                        $supervisorValidation['supervised_employees'] = $totalEmployees;
                        $supervisorValidation['message'] = "✅ Supervisor access granted - Managing {$totalEmployees} employee(s) in " . count($supervisorDepartments) . " department(s)";
                        
                        error_log("✅ Supervisor {$supervisorId} validated successfully - Departments: " . implode(', ', $supervisorDepartments));
                        
                    } else {
                        $supervisorValidation['is_valid_supervisor'] = false;
                        $supervisorValidation['supervised_employees'] = 0;
                        $supervisorValidation['message'] = "⚠️ Supervisor record found but no employees assigned to manage";
                        $accessDeniedMessage = "Supervisor record found but no employees assigned to manage";
                        $accessDeniedType = 'warning';
                        
                        error_log("⚠️ Supervisor {$supervisorId} has no employees assigned");
                    }
                } else {
                    $supervisorValidation['is_valid_supervisor'] = false;
                    $supervisorValidation['message'] = "❌ Error: Supervisor record not found in employee database";
                    $accessDeniedMessage = "Error: Supervisor record not found in employee database";
                    $accessDeniedType = 'error';
                    
                    error_log("❌ Supervisor validation failed: User {$supervisorId} not found in employee_registration table");
                }
            }
        } else {
            $userRole = 'user';
            $supervisorValidation['message'] = '❌ Access Denied: You do not have permission to access this module';
            $accessDeniedMessage = 'Access Denied: You do not have permission to access this module';
            $accessDeniedType = 'error';
            error_log("❌ User attempted access without proper module permissions");
        }
    } else {
        throw new Exception("No module permissions found for user");
    }
} catch (Exception $e) {
    error_log("❌ Error in user validation: " . $e->getMessage());
    $supervisorValidation['message'] = '❌ Error: ' . $e->getMessage();
    $accessDeniedMessage = 'Error: ' . htmlspecialchars($e->getMessage());
    $accessDeniedType = 'error';
}

$canAccess = ($isAdmin === 1 || ($isSupervisor === 1 && $supervisorValidation['is_valid_supervisor']));
?>

<link href="../css/semantic.min.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="../css/dataTables.semanticui.min.css">
<link rel="stylesheet" type="text/css" href="../css/responsive.dataTables.min.css">
<link href="../css/main.css?13" rel="stylesheet"/>
<?php $menu_header_display = 'PMS Module'; ?>
<?php include_once 'shared/headerStyles.php' ?>
<?php include_once '../assemblynotes/shared/headerScripts.php' ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
/* ===== SIEMENS COLOR VARIABLES ===== */
:root {
    --siemens-teal: #009999;
    --siemens-teal-dark: #007A7A;
    --siemens-teal-light: #00B8B8;
    --siemens-petrol: #005F6A;
    --siemens-gray-light: #F2F2F2;
    --siemens-gray-medium: #E5E5E5;
    --siemens-gray-dark: #666666;
    --siemens-text: #333333;
    --siemens-white: #FFFFFF;
    --siemens-border: #CCCCCC;
}

/* ===== ACCESS DENIED MESSAGE BANNER ===== */
.access-denied-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    margin-bottom: 12px;
    border-radius: 4px;
    border-left: 5px solid;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    animation: slideDown 0.3s ease-out;
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

.access-denied-banner.error i {
    color: #D32F2F;
}

.access-denied-banner.warning {
    background: #FFF8E1;
    border-left-color: #F57F17;
    color: #E65100;
}

.access-denied-banner.warning i {
    color: #F57F17;
}

.access-denied-banner.info {
    background: #E3F2FD;
    border-left-color: #1976D2;
    color: #0D47A1;
}

.access-denied-banner.info i {
    color: #1976D2;
}

.access-denied-banner.success {
    background: #E8F5E9;
    border-left-color: #388E3C;
    color: #1B5E20;
}

.access-denied-banner.success i {
    color: #388E3C;
}

.access-denied-banner-content {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.access-denied-banner i {
    font-size: 20px;
    flex-shrink: 0;
}

.access-denied-banner-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.access-denied-banner-title {
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.3px;
}

.access-denied-banner-message {
    font-weight: 400;
    font-size: 11px;
    line-height: 1.4;
    opacity: 0.9;
}

.access-denied-banner-close {
    background: none;
    border: none;
    color: inherit;
    cursor: pointer;
    font-size: 18px;
    padding: 0;
    margin-left: 12px;
    opacity: 0.7;
    transition: opacity 0.2s ease;
    flex-shrink: 0;
}

.access-denied-banner-close:hover {
    opacity: 1;
}

/* ===== ROLE INDICATORS ===== */
.admin-indicator, .supervisor-indicator {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 3px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}

.admin-indicator {
    background: var(--siemens-petrol);
    color: var(--siemens-white);
}

.supervisor-indicator {
    background: var(--siemens-teal);
    color: var(--siemens-white);
}

#loading-indicator {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 15px;
    border-radius: 5px;
    z-index: 9999;
    font-size: 12px;
}

.gid-input-container {
    position: relative;
    display: inline-block;
    width: 100%;
}

.suggestions-container {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ccc;
    border-top: none;
    height: 100px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.suggestion-item {
    padding: 3px 8px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    height: 24px;
    line-height: 18px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    transition: background-color 0.2s ease;
    font-size: 11px;
}

.suggestion-item:hover {
    background-color: #1ab394;
    color: white;
}

.suggestion-item.active {
    background-color: #1ab394;
    color: white;
}

.suggestion-item:hover strong,
.suggestion-item.active strong {
    color: #ffffff;
    font-weight: bold;
    text-decoration: underline;
}

.suggestion-item:last-child {
    border-bottom: none;
}

.form-control,
.ui.dropdown,
.ui.form input,
.ui.form select {
    transition: all 0.2s ease !important;
}

.form-control:hover,
.ui.dropdown:hover,
.ui.form input:hover,
.ui.form select:hover,
.ui.form select:focus {
    border-color: #1ab394 !important;
    box-shadow: 0 0 0 0.2rem rgba(26, 179, 148, 0.25) !important;
    outline: none !important;
}

.btn-primary:hover {
    background-color: #18a689 !important;
    border-color: #18a689 !important;
}

.ui.dropdown .menu > .item:hover,
.ui.dropdown .menu > .item.selected,
.ui.dropdown .menu > .item:active {
    background-color: #1ab394 !important;
    color: white !important;
}

.ui.dropdown .menu > .item.active {
    background-color: #1ab394 !important;
    color: white !important;
    font-weight: bold !important;
}

.ui.dropdown:focus,
.ui.dropdown.active {
    border-color: #1ab394 !important;
}

.ui.dropdown:hover .dropdown.icon {
    color: #1ab394 !important;
}

.ui.dropdown.selected,
.ui.dropdown .menu .selected.item {
    background-color: #1ab394 !important;
    color: white !important;
}

.ui.dropdown .menu > .item {
    transition: background-color 0.2s ease, color 0.2s ease !important;
    padding: 3px 8px !important;
    font-size: 11px !important;
    line-height: 1.2 !important;
}

.ui.dropdown .menu > .item.disabled {
    opacity: 0.5 !important;
    background: #f9f9f9 !important;
    color: rgba(0, 0, 0, 0.4) !important;
}

.ui.dropdown.search > input.search {
    border-color: #1ab394 !important;
}

.ui.dropdown.error {
    border-color: #db2828 !important;
}

.ui.dropdown .menu {
    border-color: #1ab394 !important;
    box-shadow: 0 2px 4px rgba(26, 179, 148, 0.2) !important;
}

.ui.dropdown.disabled {
    opacity: 1 !important;
    background-color: #f0f0f0 !important;
    cursor: not-allowed !important;
}

.ui.dropdown.disabled .text {
    color: #000000 !important;
}

input:disabled:not(#start_date):not(#end_date):not(#total_days),
input[readonly]:not(#start_date):not(#end_date):not(#total_days) {
    background-color: #f0f0f0 !important;
    color: #000000 !important;
    cursor: not-allowed !important;
    opacity: 1 !important;
}

#start_date,
#end_date,
#total_days {
    background-color: #ffffff !important;
    color: #000000 !important;
}

.ui.dropdown.disabled input,
.ui.dropdown.disabled .menu {
    pointer-events: none !important;
}

.ui.dropdown .menu > .item.disabled {
    opacity: 1 !important;
    background-color: #f0f0f0 !important;
    color: #000000 !important;
}

.ui.selection.dropdown.disabled {
    background-color: #f0f0f0 !important;
    border-color: #ddd !important;
}

.ui.selection.dropdown.disabled > .text {
    color: #000000 !important;
}

.form-section-header {
    background: linear-gradient(135deg, #1ab394 0%, #18a689 100%);
    color: white;
    padding: 3px 10px;
    margin: 3px 0 2px 0;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.form-section-header:first-child {
    margin-top: 0;
}

.form-section-header i {
    margin-right: 4px;
}

.ibox-content {
    padding: 3px 8px;
}

.submit-button-container {
    text-align: center;
    margin-top: 4px;
    padding: 2px 0 3px 0;
}

.submit-button-container .btn-primary {
    min-width: 140px;
    padding: 6px 16px;
    font-size: 11px;
    font-weight: 600;
}

.ibox {
    margin-bottom: 0;
}

.wrapper-content {
    padding: 0px 4px 2px 4px !important;
}

.ui.form .field {
    margin-bottom: 2px !important;
}

.ui.form .fields {
    margin-bottom: 1px !important;
}

.ui.dividing.header {
    margin-top: 0 !important;
    margin-bottom: 3px !important;
    padding-bottom: 2px !important;
    font-size: 14px !important;
}

.navbar {
    margin-bottom: 0px !important;
}

.row {
    margin-bottom: 0 !important;
}

.row.border-bottom {
    margin-bottom: 0px !important;
}

.ui.form .field > label {
    margin-bottom: 1px !important;
    font-size: 10px !important;
}

.required-field::after {
    content: " *";
    color: red;
    font-weight: bold;
}

.three.fields,
.two.fields {
    margin-bottom: 1px !important;
}

.navbar-static-top {
    min-height: 35px !important;
    padding: 0 !important;
}

.navbar-header {
    min-height: 35px !important;
}

.navbar-top-links {
    margin: 0 !important;
    padding: 0 !important;
}

.navbar-top-links h2 {
    font-size: 14px !important;
    margin: 3px 0 !important;
    line-height: 28px !important;
}

.navbar-minimalize {
    padding: 6px 10px !important;
    margin: 2px !important;
}

.form-control,
.ui.dropdown,
.ui.form input:not([type="checkbox"]):not([type="radio"]),
.ui.form select {
    height: 24px !important;
    padding: 2px 6px !important;
    font-size: 10px !important;
    line-height: 1.3 !important;
}

textarea[name="absence_detail"] {
    min-height: 50px !important;
    padding: 2px 6px !important;
    font-size: 10px !important;
    resize: vertical;
}

.ui.dropdown .menu > .item {
    padding: 3px 6px !important;
    font-size: 10px !important;
    line-height: 1.2 !important;
}

#page-wrapper {
    padding: 0 !important;
}

.col-lg-12 {
    padding-left: 4px !important;
    padding-right: 4px !important;
}

body {
    font-size: 10px !important;
}

.ibox-content form {
    margin: 0 !important;
}

.ui.form .fields > .field {
    padding-left: 0.2em !important;
    padding-right: 0.2em !important;
}

.read-only-field {
    padding: 2px 6px !important;
    min-height: 24px !important;
    line-height: 18px !important;
    font-size: 10px !important;
    background-color: #f8f8f8 !important;
    border: 1px solid #ddd !important;
    border-radius: 3px !important;
    color: #333 !important;
    word-wrap: break-word;
    overflow-wrap: break-word;
    display: block !important;
}

.row {
    margin-left: 0 !important;
    margin-right: 0 !important;
}

.border-bottom {
    padding-bottom: 0 !important;
}

.ui.dropdown > .dropdown.icon {
    padding: 0.4em !important;
}

.flatpickr-input {
    height: 24px !important;
    padding: 2px 6px !important;
    font-size: 10px !important;
}

footer {
    margin-top: 3px !important;
    padding: 3px 0 !important;
    font-size: 10px !important;
}

.ibox-content {
    transform: scale(0.95);
    transform-origin: top center;
}

#page-wrapper {
    overflow-x: hidden !important;
}

.wrapper-content {
    overflow: visible !important;
}

.user-role-badge {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 2px;
    font-size: 9px;
    font-weight: 600;
    margin-left: 8px;
}

.role-admin {
    background-color: #e74c3c;
    color: white;
}

.role-supervisor {
    background-color: #1ab394;
    color: white;
}

.role-user {
    background-color: #95a5a6;
    color: white;
}

/* ===== FIX DOUBLE DROPDOWN - HIDE ORIGINAL SELECT AND WRAPPER ===== */
select.hide-select {
    display: none !important;
}

.field .ui.dropdown.hide-dropdown {
    display: none !important;
}

.field .ui.dropdown.hide-dropdown + .read-only-field {
    display: block !important;
}

input[type="hidden"] {
    display: none !important;
}

/* ===== DISABLED FIELD STYLING ===== */
.field.disabled-field input,
.field.disabled-field select,
.field.disabled-field textarea {
    background-color: #f0f0f0 !important;
    color: #000000 !important;
    cursor: not-allowed !important;
    opacity: 1 !important;
}

.field.disabled-field .ui.dropdown {
    background-color: #f0f0f0 !important;
    cursor: not-allowed !important;
    opacity: 1 !important;
    pointer-events: none !important;
}

.field.disabled-field .ui.dropdown .text {
    color: #000000 !important;
}
</style>

<head>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/metisMenu/2.7.9/metisMenu.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.3/skins/all.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.3/icheck.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>

<body>
<div id="wrapper">
    <?php $activePage = '/dpm/pms_leaveform.php'; ?>
    <?php include_once 'shared/pms_sidebar.php' ?>
    
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">
                    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary" href="#"><i class="fa fa-bars"></i></a>
                </div>
                <ul class="nav navbar-top-links navbar-right">
                    <li>
                        <h2 style="text-align: left;">
                            Leave Entry Form
                            <?php if ($isAdmin === 1): ?>
                                <span class="admin-indicator">
                                    <i class="fa fa-shield"></i> ADMIN
                                </span>
                            <?php elseif ($isSupervisor === 1): ?>
                                <span class="supervisor-indicator">
                                    <i class="fa fa-user-tie"></i> SUPERVISOR
                                </span>
                            <?php endif; ?>
                        </h2>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="wrapper-content">
            <!-- ===== ACCESS DENIED/ERROR MESSAGE BANNER ===== -->
            <?php if ($accessDeniedMessage): ?>
                <div class="access-denied-banner <?php echo htmlspecialchars($accessDeniedType); ?>" id="accessDeniedBanner">
                    <div class="access-denied-banner-content">
                        <?php if ($accessDeniedType === 'error'): ?>
                            <i class="fa fa-exclamation-circle"></i>
                        <?php elseif ($accessDeniedType === 'warning'): ?>
                            <i class="fa fa-exclamation-triangle"></i>
                        <?php elseif ($accessDeniedType === 'info'): ?>
                            <i class="fa fa-info-circle"></i>
                        <?php else: ?>
                            <i class="fa fa-check-circle"></i>
                        <?php endif; ?>
                        <div class="access-denied-banner-text">
                            <div class="access-denied-banner-title">
                                <?php 
                                    if ($accessDeniedType === 'error') {
                                        echo 'Access Denied';
                                    } elseif ($accessDeniedType === 'warning') {
                                        echo 'Warning';
                                    } elseif ($accessDeniedType === 'info') {
                                        echo 'Information';
                                    } else {
                                        echo 'Success';
                                    }
                                ?>
                            </div>
                            <div class="access-denied-banner-message">
                                <?php echo htmlspecialchars($accessDeniedMessage); ?>
                            </div>
                        </div>
                    </div>
                    <button class="access-denied-banner-close" onclick="document.getElementById('accessDeniedBanner').style.display='none';">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox">
                        <div class="ibox-content">
                            <form class="ui form" method="post" id="registrationForm" novalidate <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                <input type="hidden" name="action" value="leaveRegister">
                                <input type="hidden" name="user_role" id="user_role" value="<?php echo htmlspecialchars($userRole); ?>">
                                <input type="hidden" name="supervisor_id" id="supervisor_id" value="<?php echo htmlspecialchars($supervisorId ?? ''); ?>">
                                
                                <h3 class="ui dividing header" style="margin-top: 0;">Register New Leave Entry</h3>
                                
                                <div class="form-section-header">
                                    <i class="fa fa-user"></i> Employee Information
                                </div>
                                
                                <div class="three fields">
                                    <div class="field">
                                        <label class="required-field">GID</label>                                        
                                        <div class="gid-input-container" style="position: relative;">
                                            <input id="gid" name="gid" class="form-control required" value="" placeholder="-- Type to search GID --" autocomplete="off" required <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                            <div id="gid_suggestions" class="suggestions-container" style="display: none;"></div>
                                        </div>                                        
                                    </div>
                                    <div class="field">
                                        <label class="required-field">Name</label>
                                        <input type="text" id="name" name="name" class="form-control" required <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                    </div>
                                    <div class="field">
                                        <label class="required-field">Department</label>
                                        <select name="department" id="department" class="ui dropdown search" required <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                            <option value="">-- Select Department --</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="three fields">
                                    <div class="field">
                                        <label class="required-field">Sub-Department</label>
                                        <select name="sub_department" id="sub_department" class="ui dropdown search" <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                            <option value="">-- Select Sub-Department --</option>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label class="required-field">Role</label>
                                        <select name="role" id="role" class="ui dropdown search" required <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                            <option value="">-- Select Role --</option>
                                        </select>
                                    </div> 
                                    <div class="field">
                                        <label class="required-field">Group Type</label>
                                        <select name="group_type" id="group_type" class="ui dropdown" <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                            <option value="">-- Select group Type --</option>
                                            <option value="A">Group A</option>
                                            <option value="B">Group B</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="three fields">
                                    <div class="field">
                                        <label class="required-field">Type of Employment</label>
                                        <input type="text" id="employment_type" name="employment_type" class="form-control" required <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                    </div>
                                    <div class="field">
                                        <label class="required-field">Joined 01.01.2005</label>
                                        <select name="joined" id="joined" class="ui dropdown search" <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                            <option value="">-- Select Option --</option>
                                            <option value="before">Before</option>
                                            <option value="after">After</option>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label>Sponsor</label>
                                        <select name="sponsor" id="sponsor" class="ui dropdown search" <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                            <option value="">-- Select Sponsor --</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="three fields">
                                    <div class="field">
                                        <label>In-Company Manager</label>
                                        <select name="in_company_manager" id="in_company_manager" class="ui dropdown search" <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                            <option value="">-- Select In-Company Manager --</option>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label>Line Manager</label>
                                        <select name="line_manager" id="line_manager" class="ui dropdown search" <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                            <option value="">-- Select Line Manager --</option>
                                        </select>
                                    </div> 
                                    <div class="field">
                                        <label>Supervisor</label>
                                        <select name="supervisor" id="supervisor" class="ui dropdown search" <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                            <option value="">-- Select Supervisor --</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-section-header">
                                    <i class="fa fa-calendar"></i> Leave Details
                                </div>

                                <div class="two fields">
                                    <div class="field">
                                        <label class="required-field">Type of Leave</label>
                                        <select name="leave_type" id='leave_type' class="ui dropdown" required <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                            <option value="">-- Select Leave Type --</option>
                                            <option value="IN_Casual Leave">IN_Casual Leave</option>
                                            <option value="IN_Earned Leave">IN_Earned Leave</option>
                                            <option value="IN_Education Leave">IN_Education Leave</option>
                                            <option value="IN_LTA">IN_LTA</option>
                                            <option value="IN_Outdoor Duty">IN_Outdoor Duty</option>
                                            <option value="IN_Paternity Leave_Adoption">IN_Paternity Leave_Adoption</option>
                                            <option value="IN_Paternity Leave_Child Birth">IN_Paternity Leave_Child Birth</option>
                                            <option value="IN_Sabbatical leave">IN_Sabbatical leave</option>
                                            <option value="IN_Sick w/o Attachment">IN_Sick w/o Attachment</option>
                                            <option value="IN_Sick with Attachment">IN_Sick with Attachment</option>
                                            <option value="IN_Special Leave with Pay">IN_Special Leave with Pay</option>
                                            <option value="IN_Training/Seminar">IN_Training/Seminar</option>
                                            <option value="IN_Transfer Leave">IN_Transfer Leave</option>
                                            <option value="IN_Volunteering Leave">IN_Volunteering Leave</option>
                                            <option value="IN_Work From Home">IN_Work From Home</option>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label>Total Leave Days</label>
                                        <input type="number" name="total_days" id="total_days" readonly>
                                    </div>
                                </div>
                                
                                <div class="field">
                                    <label>Detail</label>
                                    <textarea name="absence_detail" rows="3" placeholder="Enter detailed reason for absence" <?php echo (!$canAccess ? 'disabled' : ''); ?>></textarea>
                                </div>

                                <div class="two fields">
                                    <div class="field">
                                        <label class="required-field" for="start_date">Start Date and Time</label>
                                        <input type="text" id="start_date" name="start_date" class="form-control" placeholder="YYYY-MM-DD HH:MM" required <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                    </div>
                                    <div class="field">
                                        <label class="required-field" for="end_date">End Date and Time</label>
                                        <input type="text" id="end_date" name="end_date" class="form-control" placeholder="YYYY-MM-DD HH:MM" required <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                    </div>
                                </div>

                                <div class="submit-button-container">
                                    <button class="btn btn-primary" type="submit" id="submit-button" <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                        <i class="fa fa-save"></i> Save New Leave Entry
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php $footer_display = 'PMS'; include_once '../assemblynotes/shared/footer.php'; ?>
    </div>
</div>

<!-- Loading indicator -->
<div id="loading-indicator">Loading...</div>

<?php include_once '../assemblynotes/shared/headerSemanticScripts.php' ?>
<script src="shared/shared.js"></script>

<script>
const USER_ROLE = '<?php echo htmlspecialchars($userRole); ?>';
const SUPERVISOR_ID = '<?php echo htmlspecialchars($supervisorId ?? ''); ?>';
const IS_ADMIN = <?php echo $isAdmin ? 'true' : 'false'; ?>;
const IS_SUPERVISOR = <?php echo $isSupervisor ? 'true' : 'false'; ?>;
const SUPERVISOR_DEPARTMENTS = <?php echo json_encode($supervisorDepartments); ?>;
const CAN_ACCESS = <?php echo $canAccess ? 'true' : 'false'; ?>;

console.log('✅ User Role:', USER_ROLE);
console.log('✅ Is Admin:', IS_ADMIN);
console.log('✅ Is Supervisor:', IS_SUPERVISOR);
console.log('✅ Can Access:', CAN_ACCESS);

var currentEmployeeSuggestions = [];
var displayedEmployeeCount = 0;
var itemsPerPage = 5;
var isLoadingMore = false;
var allDropdownOptions = null;
var startDateChanged = false;
var endDateChanged = false;

$(document).ready(function() {
    // Display validation status
    if (USER_ROLE === 'supervisor') {
        if (IS_SUPERVISOR) {
            console.log('✅ Supervisor Validated - Can access leave form');
        }
    } else if (USER_ROLE === 'admin') {
        console.log('✅ Admin Access Granted - Full access to leave form');
    }

    // ===== INITIALIZE DROPDOWNS =====
    $('.ui.dropdown').dropdown({
        fullTextSearch: true,
        filterRemoteData: true,
        ignoreDiacritics: true,
        selectOnKeydown: false,
        forceSelection: false,
        allowAdditions: false,
        message: {
            noResults: 'No results found.'
        }
    });

    // ===== LOAD DROPDOWN OPTIONS FROM employee_registration =====
    loadAllDropdownOptions();

    // ===== INITIALIZE START DATE PICKER - NO DEFAULT DATE =====
    const startPicker = flatpickr("#start_date", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        defaultHour: 0,
        defaultMinute: 0,
        minuteIncrement: 1,
        allowInput: true,
        onOpen: function() {
            console.log('📅 Start date picker opened');
        },
        onChange: function(selectedDates, dateStr) {
            console.log('✅ Start date changed:', dateStr);
            startDateChanged = true;
            
            if (selectedDates[0]) {
                // When date is selected, set time to 00:00 ONLY on first selection
                const selectedDate = new Date(selectedDates[0]);
                
                // Check if user is manually editing the time
                const currentValue = $('#start_date').val();
                const hasTime = currentValue.includes(':');
                
                if (!hasTime) {
                    // First time selection - set to 00:00
                    selectedDate.setHours(0, 0, 0, 0);
                    startPicker.setDate(selectedDate, false);
                    console.log('📅 Start date set to:', selectedDate, 'with time 00:00');
                } else {
                    // User is editing time - allow the change
                    console.log('⏰ Start time manually edited to:', dateStr);
                }
                
                // Set minimum end date to be the same as start date
                let minEndDate = new Date(selectedDate);
                minEndDate.setHours(0, 0, 0, 0);
                endPicker.set('minDate', minEndDate);
            }
            calculateTotalDays();
        },
        onClose: function(selectedDates, dateStr) {
            // Allow user to edit time after date is selected
            console.log('📅 Start date picker closed');
        }
    });

    // ===== INITIALIZE END DATE PICKER - NO DEFAULT DATE =====
    const endPicker = flatpickr("#end_date", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        defaultHour: 0,
        defaultMinute: 0,
        minuteIncrement: 1,
        allowInput: true,
        onOpen: function() {
            console.log('📅 End date picker opened');
        },
        onChange: function(selectedDates, dateStr) {
            console.log('✅ End date changed:', dateStr);
            endDateChanged = true;
            
            if (selectedDates[0]) {
                // When date is selected, set time to 00:00 ONLY on first selection
                const selectedDate = new Date(selectedDates[0]);
                
                // Check if user is manually editing the time
                const currentValue = $('#end_date').val();
                const hasTime = currentValue.includes(':');
                
                if (!hasTime) {
                    // First time selection - set to 00:00
                    selectedDate.setHours(0, 0, 0, 0);
                    endPicker.setDate(selectedDate, false);
                    console.log('📅 End date set to:', selectedDate, 'with time 00:00');
                } else {
                    // User is editing time - allow the change
                    console.log('⏰ End time manually edited to:', dateStr);
                }
            }
            calculateTotalDays();
        },
        onClose: function(selectedDates, dateStr) {
            // Allow user to edit time after date is selected
            console.log('📅 End date picker closed');
        }
    });

    // ===== ALLOW MANUAL TIME EDITING =====
    $('#start_date, #end_date').on('input', function() {
        const fieldId = $(this).attr('id');
        const value = $(this).val();
        console.log(`⏰ Manual input detected for ${fieldId}:`, value);
        
        // Allow user to manually type time
        // The flatpickr will handle the parsing
    });

    // ===== CALCULATE TOTAL DAYS FUNCTION =====
    function calculateTotalDays() {
        const startDateVal = $('#start_date').val();
        const endDateVal = $('#end_date').val();

        if (startDateVal && endDateVal) {
            const startDate = new Date(startDateVal);
            const endDate = new Date(endDateVal);

            if (endDate < startDate) {
                console.warn('⚠️ End date must be greater than or equal to start date');
                $('#total_days').val('');
                return;
            }

            // Extract just the date parts (ignore time)
            const startDateOnly = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate());
            const endDateOnly = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());

            // Calculate days difference
            const timeDiff = endDateOnly - startDateOnly;
            const daysDiff = Math.floor(timeDiff / (1000 * 60 * 60 * 24)) + 1; // +1 to include both start and end dates

            // If same day, count as 1 day regardless of time
            if (startDateOnly.getTime() === endDateOnly.getTime()) {
                $('#total_days').val(1);
                console.log('📊 Same day leave - Total days: 1');
            } else {
                $('#total_days').val(daysDiff > 0 ? daysDiff : 0);
                console.log('📊 Multi-day leave - Total days calculated:', daysDiff);
            }
        } else {
            $('#total_days').val('');
        }
    }

    $('#gid').on('input', function() {
        const searchTerm = $(this).val().trim();
        if (searchTerm.length >= 2) {
            getEmployeeSuggestions(searchTerm);
        } else {
            $('#gid_suggestions').hide();
        }
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.gid-input-container').length) {
            $('#gid_suggestions').hide();
        }
    });

    $('#gid_suggestions').on('scroll', function() {
        var container = $(this);
        var scrollTop = container.scrollTop();
        var scrollHeight = container[0].scrollHeight;
        var containerHeight = container.height();
        
        if (scrollTop + containerHeight >= scrollHeight - 10) {
            loadMoreEmployeeSuggestions();
        }
    });

    // ===== FORM SUBMISSION HANDLER =====
    $('#registrationForm').on('submit', function(e) {
        e.preventDefault();

        // ===== VALIDATE REQUIRED FIELDS =====
        const requiredFields = [
            { id: 'gid', name: 'GID' },
            { id: 'name', name: 'Name' },
            { id: 'department', name: 'Department' },
            { id: 'sub_department', name: 'Sub-Department' },
            { id: 'role', name: 'Role' },
            { id: 'group_type', name: 'Group Type' },
            { id: 'employment_type', name: 'Type of Employment' },
            { id: 'joined', name: 'Joined' },
            { id: 'leave_type', name: 'Type of Leave' },
            { id: 'start_date', name: 'Start Date and Time' },
            { id: 'end_date', name: 'End Date and Time' },
            { id: 'total_days', name: 'Total Days' }
        ];

        const emptyFields = [];
        
        requiredFields.forEach(field => {
            const value = $(`#${field.id}`).val();
            if (!value || value.trim() === '') {
                emptyFields.push(field.name);
                console.warn(`⚠️ Missing field: ${field.name}`);
            }
        });

        if (emptyFields.length > 0) {
            console.error('❌ Missing required fields:', emptyFields);
            alert('❌ Please fill in the following required fields:\n\n' + emptyFields.join('\n'));
            return;
        }

        // ===== VALIDATE DATE AND TIME =====
        const startDateVal = $('#start_date').val();
        const endDateVal = $('#end_date').val();

        const startDateTime = new Date(startDateVal);
        const endDateTime = new Date(endDateVal);

        if (endDateTime <= startDateTime) {
            console.error('❌ End date/time must be after start date/time');
            alert('❌ End date and time must be after start date and time');
            return;
        }

        // ===== PREPARE FORM DATA =====
        const formData = new FormData();
        
        formData.append('user_role', USER_ROLE);
        formData.append('supervisor_id', SUPERVISOR_ID);
        formData.append('action', 'leaveRegister');
        formData.append('gid', $('#gid').val().trim());
        formData.append('name', $('#name').val().trim());
        formData.append('department', $('#department').val());
        formData.append('sub_department', $('#sub_department').val());
        formData.append('role', $('#role').val());
        formData.append('group_type', $('#group_type').val());
        formData.append('in_company_manager', getHiddenFieldValue('in_company_manager'));
        formData.append('line_manager', getHiddenFieldValue('line_manager'));
        formData.append('supervisor', getHiddenFieldValue('supervisor'));
        formData.append('sponsor', getHiddenFieldValue('sponsor'));
        formData.append('employment_type', $('#employment_type').val().trim());
        formData.append('joined', $('#joined').val());
        formData.append('leave_type', $('#leave_type').val());
        formData.append('absence_detail', $('textarea[name="absence_detail"]').val().trim());
        formData.append('start_date', $('#start_date').val());
        formData.append('end_date', $('#end_date').val());
        formData.append('total_days', $('#total_days').val());

        console.log('📋 Form data prepared for submission');
        console.log('📅 Start Date/Time:', $('#start_date').val());
        console.log('📅 End Date/Time:', $('#end_date').val());
        console.log('📊 Total Days:', $('#total_days').val());

        const submitButton = document.getElementById('submit-button');
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
        $('#loading-indicator').show();

        // ===== SUBMIT FORM DATA =====
        $.ajax({
            url: '/dpm/api/PMSController.php',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                console.log('✅ Server response:', response);
                
                if (response.success) {
                    console.log('✅ Leave registration successful!');
                    alert('✅ Leave registration successful!');
                    setTimeout(function() {
                        location.href = "leave_reports.php";
                    }, 1500);
                } else {
                    console.error('❌ Error:', response.message);
                    alert('❌ Error: ' + (response.message || 'Failed to register leave'));
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fa fa-save"></i> Save New Leave Entry';
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ AJAX Error:', error);
                console.error('❌ Status:', status);
                console.error('❌ Response:', xhr.responseText);
                alert('❌ Error submitting form: ' + error);
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fa fa-save"></i> Save New Leave Entry';
            },
            complete: function() {
                $('#loading-indicator').hide();
            }
        });
    });
});

// ===== GET HIDDEN FIELD VALUE =====
function getHiddenFieldValue(fieldName) {
    const hiddenInput = $(`input[name="${fieldName}"][type="hidden"]`);
    if (hiddenInput.length > 0) {
        return hiddenInput.val() || '-';
    }
    return $(`#${fieldName}`).val() || '-';
}

// ===== LOAD ALL DROPDOWN OPTIONS FROM employee_registration =====
function loadAllDropdownOptions() {
    console.log('📋 Loading dropdown options from employee_registration...');
    
    $.ajax({
        url: '/dpm/api/PMSController.php',
        method: 'POST',
        data: {
            "action": "retrieveEmployeeCompleteProfile",
            "gid": ""
        },
        success: function(response) {
            const data = typeof response === 'string' ? JSON.parse(response) : response;
            
            if (data.success && data.filterOptions) {
                allDropdownOptions = data.filterOptions;
                populateDropdowns(data.filterOptions);
                console.log('✅ Dropdown options loaded successfully');
            } else {
                console.error('❌ Failed to load dropdown options:', data.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Error loading dropdown options:', error);
        }
    });
}

// ===== SEARCH EMPLOYEES FROM employee_registration =====
function getEmployeeSuggestions(searchTerm) {
    if (!searchTerm || searchTerm.length < 2) {
        $('#gid_suggestions').hide().empty();
        return;
    }
    
    console.log('🔍 Searching for employees with term:', searchTerm);
    $('#gid_suggestions').html('<div class="suggestion-item">Loading...</div>').show();
    
    const data = {
        "action": "searchEmployeeRegistration",
        "searchTerm": searchTerm
    };
    
    if (IS_SUPERVISOR && !IS_ADMIN) {
        data.supervisor_id = SUPERVISOR_ID;
        data.filter_by_supervisor = true;
        console.log('👤 Supervisor filter applied for:', SUPERVISOR_ID);
    }
    
    $.ajax({
        url: '/dpm/api/PMSController.php',
        method: 'POST',
        data: data,
        success: function(response) {
            var result = typeof response === 'string' ? JSON.parse(response) : response;
            
            if (result.success) {
                currentEmployeeSuggestions = result.data || [];
                displayedEmployeeCount = 0;
                
                console.log('✅ Found ' + currentEmployeeSuggestions.length + ' employees');
                
                if (currentEmployeeSuggestions.length === 0) {
                    $('#gid_suggestions').html('<div class="suggestion-item">No employees found</div>').show();
                } else {
                    displayEmployeeSuggestions(searchTerm);
                }
            } else {
                console.error('❌ Search failed:', result.message);
                $('#gid_suggestions').html('<div class="suggestion-item">Error: ' + result.message + '</div>').show();
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error:', error);
            $('#gid_suggestions').html('<div class="suggestion-item">Error: ' + error + '</div>').show();
        }
    });
}

function displayEmployeeSuggestions(searchTerm) {
    const container = $('#gid_suggestions');
    const inputField = $('#gid');
    
    container.empty();
    
    if (!currentEmployeeSuggestions || currentEmployeeSuggestions.length === 0) {
        container.html('<div class="suggestion-item">No employees found</div>');
        container.show();
        return;
    }
    
    const nextBatch = currentEmployeeSuggestions.slice(displayedEmployeeCount, displayedEmployeeCount + itemsPerPage);
    
    const suggestionsHtml = nextBatch.map(employee => {
        const highlightedValue = employee.value.toString().replace(
            new RegExp('(' + searchTerm + ')', 'gi'),
            '<strong>$1</strong>'
        );
        return `<div class="suggestion-item" onclick="selectEmployee('${employee.key}')" data-gid="${employee.key}" style="cursor: pointer;">${highlightedValue}</div>`;
    }).join('');
    
    displayedEmployeeCount += nextBatch.length;
    
    const loadMoreHtml = displayedEmployeeCount < currentEmployeeSuggestions.length 
        ? `<div class="load-more-indicator" style="text-align: center; padding: 4px; background: #f0f0f0; font-size: 9px; color: #666;">Scroll to load more... (${currentEmployeeSuggestions.length - displayedEmployeeCount} more)</div>`
        : '';
    
    container.html(suggestionsHtml + loadMoreHtml);
    container.show();
    
    const inputPosition = inputField.offset();
    const inputHeight = inputField.outerHeight();
    const windowHeight = $(window).height();
    const inputBottom = inputPosition.top + inputHeight;
    const maxHeight = Math.min(150, windowHeight - inputBottom - 10);
    
    container.css({
        'max-height': `${maxHeight}px`,
        'overflow-y': 'auto'
    });
}

function loadMoreEmployeeSuggestions() {
    if (isLoadingMore || displayedEmployeeCount >= currentEmployeeSuggestions.length) {
        return;
    }
    
    isLoadingMore = true;
    var searchTerm = $('#gid').val().trim();
    
    var currentHtml = $('#gid_suggestions').html().replace(/<div class="load-more-indicator"[^>]*>.*?<\/div>/, '');
    
    var nextBatch = currentEmployeeSuggestions.slice(displayedEmployeeCount, displayedEmployeeCount + itemsPerPage);
    
    var newItemsHtml = '';
    
    nextBatch.forEach(function(employee) {
        var highlightedValue = employee.value.toString().replace(new RegExp('(' + searchTerm + ')', 'gi'), '<strong>$1</strong>');
        newItemsHtml += `<div class="suggestion-item" onclick="selectEmployee('${employee.key}')" data-gid="${employee.key}">${highlightedValue}</div>`;
    });
    
    displayedEmployeeCount += nextBatch.length;
    
    if (displayedEmployeeCount < currentEmployeeSuggestions.length) {
        newItemsHtml += `<div class="load-more-indicator" style="background-color: #e9ecef; color: #6c757d; font-style: italic; text-align: center; cursor: default; font-size: 9px; padding: 3px;" onclick="event.stopPropagation();">Scroll down to load more... (${currentEmployeeSuggestions.length - displayedEmployeeCount} more)</div>`;
    }
    
    $('#gid_suggestions').html(currentHtml + newItemsHtml);
    isLoadingMore = false;
}

function selectEmployee(gid) {
    console.log('✅ Selected GID:', gid);
    $('#gid_suggestions').hide().empty();
    
    resetFormFields();
    $('#gid').val(gid);
    
    if (IS_SUPERVISOR && !IS_ADMIN) {
        $.ajax({
            url: '/dpm/api/PMSController.php',
            method: 'POST',
            data: {
                "action": "getEmployeeRegistrationDetails",
                "gid": gid,
                "supervisor_id": SUPERVISOR_ID
            },
            success: function(response) {
                const result = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (!result.success) {
                    console.error('❌ Access denied:', result.message);
                    alert(result.message || 'You do not have permission to access this employee');
                    resetFormFields();
                    $('#gid').val('');
                    return;
                }
                loadEmployeeDataFromResponse(gid, result);
            },
            error: function(xhr, status, error) {
                console.error('❌ Authorization error:', error);
                alert('Error validating access to employee');
                resetFormFields();
                $('#gid').val('');
            }
        });
    } else {
        loadEmployeeDataDirect(gid);
    }
}

function loadEmployeeDataDirect(gid) {
    $('#loading-indicator').show();
    
    $.ajax({
        url: '/dpm/api/PMSController.php',
        method: 'POST',
        data: {
            "action": "getEmployeeRegistrationDetails",
            "gid": gid
        },
        success: function(response) {
            const data = typeof response === 'string' ? JSON.parse(response) : response;
            
            if (data.success && data.userData) {
                loadEmployeeDataFromResponse(gid, data);
            } else {
                handleEmployeeNotFound(gid);
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error:', error);
            alert('Error loading employee details: ' + error);
            $('#loading-indicator').hide();
        }
    });
}

function loadEmployeeDataFromResponse(gid, data) {
    const userData = data.userData;
    
    console.log('📋 Loading employee data:', userData);
    
    // ===== ENABLE GID FIELD, DISABLE ALL OTHERS =====
    $('#gid').prop('readonly', false).closest('.field').removeClass('disabled-field');
    
    // ===== DISABLE ALL OTHER EMPLOYEE INFO FIELDS =====
    $('#name').prop('readonly', true).closest('.field').addClass('disabled-field');
    $('#employment_type').prop('readonly', true).closest('.field').addClass('disabled-field');
    
    // Disable all dropdowns in employee info section
    $('#department, #sub_department, #role, #group_type, #joined, #sponsor, #in_company_manager, #line_manager, #supervisor')
        .prop('disabled', true)
        .closest('.field')
        .addClass('disabled-field');
    
    // Set values
    $('#name').val(userData.name);
    $('#employment_type').val(userData.employment_type || 'Not Specified');
    
    // ===== SET DROPDOWN VALUES =====
    setDropdownValue('#department', userData.department);
    setDropdownValue('#sub_department', userData.sub_department);
    setDropdownValue('#role', userData.role);
    setDropdownValue('#group_type', userData.group_type);
    setDropdownValue('#joined', userData.joined || '');
    
    // ===== SET MANAGER/SPONSOR FIELDS AS STATIC =====
    replaceWithStaticField('#in_company_manager', userData.in_company_manager);
    replaceWithStaticField('#line_manager', userData.line_manager);
    replaceWithStaticField('#supervisor', userData.supervisor);
    replaceWithStaticField('#sponsor', userData.sponsor);
    
    // ===== ENABLE LEAVE DETAILS FIELDS =====
    $('#leave_type, #absence_detail, #start_date, #end_date')
        .prop('disabled', false)
        .closest('.field')
        .removeClass('disabled-field');
    
    $('#loading-indicator').hide();
    console.log('✅ Employee data loaded successfully');
}

function handleEmployeeNotFound(gid) {
    resetFormFields();
    $('#gid').val(gid).prop('readonly', false);
    $('#name').prop('readonly', false);
    $('#employment_type').prop('readonly', false);
    
    replaceWithStaticField('#in_company_manager', '-');
    replaceWithStaticField('#line_manager', '-');
    replaceWithStaticField('#supervisor', '-');
    replaceWithStaticField('#sponsor', '-');
    
    console.log('ℹ️ New employee. Please fill in the details.');
    alert('New employee. Please fill in the details.');
    $('#loading-indicator').hide();
}

function setDropdownValue(selector, value) {
    if (value) {
        const $dropdown = $(selector);
        
        // Remove existing option if it exists
        $dropdown.find(`option[value="${value}"]`).remove();
        
        // Add new option and select it
        $dropdown.append(new Option(value, value, true, true));
        $dropdown.val(value);
        
        // Disable the dropdown
        $dropdown.prop('disabled', true);
        
        // Refresh semantic UI dropdown
        $dropdown.closest('.ui.dropdown').dropdown('refresh');
    }
}

function replaceWithStaticField(selector, value) {
    const $selector = $(selector);
    const $field = $selector.closest('.field');
    const fieldName = $selector.attr('name');
    
    // Get the wrapper div
    const $wrapper = $selector.closest('.ui.dropdown');
    
    // Remove existing static field and hidden input
    $field.find('.read-only-field').remove();
    $field.find(`input[name="${fieldName}"][type="hidden"]`).remove();
    
    // Hide both the select and the wrapper
    $selector.hide().addClass('hide-select');
    if ($wrapper.length > 0) {
        $wrapper.hide().addClass('hide-dropdown');
    }
    
    // Create and append static field
    const html = `
        <div class="read-only-field" data-field="${fieldName}">
            ${value || '-'}
        </div>
        <input type="hidden" name="${fieldName}" value="${value || ''}">
    `;
    
    $field.append(html);
}

function populateDropdowns(options) {
    console.log('📋 Populating dropdowns from options...');
    
    // ===== POPULATE DEPARTMENT =====
    var deptDropdown = $('select[name="department"]');
    deptDropdown.empty().append('<option value="">-- Select Department --</option>');
    if (options.departmentList && Array.isArray(options.departmentList)) {
        options.departmentList.forEach(function(dept) {
            const deptValue = dept.department || dept;
            if (deptValue && deptValue.trim() !== '') {
                deptDropdown.append(`<option value="${deptValue}">${deptValue}</option>`);
            }
        });
    }

    // ===== POPULATE SUB-DEPARTMENT =====
    var subDeptDropdown = $('select[name="sub_department"]');
    subDeptDropdown.empty().append('<option value="">-- Select Sub-Department --</option>');
    if (options.departmentList && Array.isArray(options.departmentList)) {
        options.departmentList.forEach(function(dept) {
            const subDeptValue = dept.sub_department || dept;
            if (subDeptValue && subDeptValue.trim() !== '') {
                subDeptDropdown.append(`<option value="${subDeptValue}">${subDeptValue}</option>`);
            }
        });
    }

    // ===== POPULATE ROLE =====
    var roleDropdown = $('select[name="role"]');
    roleDropdown.empty().append('<option value="">-- Select Role --</option>');
    if (options.roleList && Array.isArray(options.roleList)) {
        options.roleList.forEach(function(role) {
            const roleValue = role.role || role;
            if (roleValue && roleValue.trim() !== '') {
                roleDropdown.append(`<option value="${roleValue}">${roleValue}</option>`);
            }
        });
    }

    // ===== POPULATE MANAGER DROPDOWNS =====
    const managerDropdowns = [
        'select[name="line_manager"]', 
        'select[name="in_company_manager"]',
        'select[name="supervisor"]'
    ];
    managerDropdowns.forEach(function(dropdownSelector) {
        var managerDropdown = $(dropdownSelector);
        managerDropdown.empty().append('<option value="">-- Select Manager --</option>');
        if (options.managerList && Array.isArray(options.managerList)) {
            options.managerList.forEach(function(manager) {
                if (manager.value) {
                    managerDropdown.append(`<option value="${manager.value}" data-gid="${manager.key || manager.value}">${manager.value}</option>`);
                }
            });
        }
    });

    // ===== POPULATE SPONSOR =====
    var sponsorDropdown = $('select[name="sponsor"]');
    sponsorDropdown.empty().append('<option value="">-- Select Sponsor --</option>');
    if (options.sponsorList && Array.isArray(options.sponsorList)) {
        options.sponsorList.forEach(function(sponsor) {
            if (sponsor.value) {
                sponsorDropdown.append(`<option value="${sponsor.value}" data-gid="${sponsor.key || sponsor.value}">${sponsor.value}</option>`);
            }
        });
    }

    // ===== REFRESH SEMANTIC UI DROPDOWNS =====
    $('.ui.dropdown').dropdown('refresh');
    console.log('✅ Dropdowns populated successfully');
}

function resetFormFields() {
    console.log('🔄 Resetting form fields...');
    
    $('#gid_suggestions').hide().empty();
    
    // Reset inputs
    $('form input[type="text"]').not('#gid').val('').prop('readonly', false);
    $('select').val('').prop('disabled', false).removeClass('hide-select');
    $('textarea').val('').prop('disabled', false);

    // Remove static fields
    $('.read-only-field').remove();
    $('input[type="hidden"]').remove();
    
    // Show select elements and wrappers
    $('#in_company_manager, #line_manager, #supervisor, #sponsor').show().removeClass('hide-select');
    $('.ui.dropdown').show().removeClass('hide-dropdown');
    
    // Remove disabled-field class from all fields
    $('.field').removeClass('disabled-field');
    
    // Reset styles
    $('.field').removeClass('error disabled');
    $('input, select, textarea').css({
        'background-color': '',
        'color': '',
        'cursor': ''
    });
    
    // Refresh dropdowns
    $('.ui.dropdown').dropdown('refresh');
    
    $('#start_date, #end_date, #total_days').val('');
    startDateChanged = false;
    endDateChanged = false;
    console.log('✅ Form reset complete');
}

// ===== SIDEBAR FUNCTIONALITY =====
(function() {
    if (typeof $.fn.tooltip === 'undefined') {
        $.fn.tooltip = function() { return this; };
    }
    if (typeof $.fn.metisMenu === 'undefined') {
        $.fn.metisMenu = function() { return this; };
    }
    if (typeof $.fn.iCheck === 'undefined') {
        $.fn.iCheck = function() { return this; };
    }
    if (typeof $.fn.slimScroll === 'undefined') {
        $.fn.slimScroll = function() { return this; };
    }
})();

$(document).ready(function() {
    $('.navbar-minimalize').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $("body").toggleClass("mini-navbar");
        SmoothlyMenu();
    });

    function SmoothlyMenu() {
        if (!$('body').hasClass('mini-navbar') || $('body').hasClass('body-small')) {
            $('#side-menu').hide();
            setTimeout(function() {
                $('#side-menu').fadeIn(400);
            }, 200);
        } else if ($('body').hasClass('fixed-sidebar')) {
            $('#side-menu').hide();
            setTimeout(function() {
                $('#side-menu').fadeIn(400);
            }, 100);
        } else {
            $('#side-menu').removeAttr('style');
        }
    }

    $('#side-menu').on('click', 'li a', function(e) {
        const href = $(this).attr('href');
        if (href === '#' || href === 'javascript:void(0);') {
            e.preventDefault();
            e.stopPropagation();
            const submenu = $(this).next('ul');
            if (submenu.length) {
                submenu.slideToggle(300);
            }
        } else if (href && href !== '#') {
            e.preventDefault();
            window.location.href = href;
        }
    });

    if ($.fn.metisMenu) {
        $('#side-menu').metisMenu({
            preventDefault: true,
            toggle: true,
            doubleTapToGo: false
        });
    }
});
</script>
</body>
</html>