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
                
                // ✅ FIXED: Check if supervisor exists in employee_registration
                $checkSupervisorQuery = "SELECT gid, name, supervisor, sub_department 
                                        FROM employee_registration 
                                        WHERE gid = :supervisor_id 
                                        AND (status = 'A' OR status IS NULL)
                                        LIMIT 1";
                
                $supervisorCheck = $pdoManager->fetchQueryData($checkSupervisorQuery, [':supervisor_id' => $supervisorId]);
                
                if (isset($supervisorCheck['data']) && !empty($supervisorCheck['data'])) {
                    $supervisorRecord = $supervisorCheck['data'][0];
                    
                    // ✅ FIXED: Use CONCAT + LIKE pattern for supervisor filtering
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

<style>
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

.access-denied-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    margin-bottom: 20px;
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
    gap: 16px;
    flex: 1;
}

.access-denied-banner i {
    font-size: 24px;
    flex-shrink: 0;
}

.access-denied-banner-text {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.access-denied-banner-title {
    font-weight: 700;
    font-size: 15px;
    letter-spacing: 0.3px;
}

.access-denied-banner-message {
    font-weight: 400;
    font-size: 13px;
    line-height: 1.5;
    opacity: 0.9;
}

.access-denied-banner-close {
    background: none;
    border: none;
    color: inherit;
    cursor: pointer;
    font-size: 20px;
    padding: 0;
    margin-left: 16px;
    opacity: 0.7;
    transition: opacity 0.2s ease;
    flex-shrink: 0;
}

.access-denied-banner-close:hover {
    opacity: 1;
}

.admin-indicator, .supervisor-indicator {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.5px;
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

.required-field::after {
    content: " *";
    color: red;
    font-weight: bold;
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

.suggestions-container {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ccc;
    border-top: none;
    height: 200px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.suggestion-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    height: 40px;
    line-height: 24px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    transition: background-color 0.2s ease; 
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

.field {
    position: relative;
}

.gid-input-container {
    position: relative;
    display: inline-block;
    width: 100%;
}

#loading-indicator {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 20px;
    border-radius: 5px;
    z-index: 9999;
}

input[readonly] {
    background-color: #f8f9fa !important;
    color: #000000 !important;
    cursor: not-allowed !important;
    border: 1px solid #dee2e6 !important;
    opacity: 1 !important;
}

.ui.dropdown.disabled {
    background-color: #f8f9fa !important;
    opacity: 1 !important;
}

.ui.dropdown.disabled .text,
.ui.disabled.dropdown .menu > .item {
    color: #000000 !important;
    opacity: 1 !important;
}

.ui.disabled.dropdown > .text {
    color: #000000 !important;
    font-weight: normal !important;
}

select[disabled] {
    background-color: #f8f9fa !important;
    color: #000000 !important;
    cursor: not-allowed !important;
    opacity: 1 !important;
}

.ui.selection.dropdown.disabled {
    background-color: #f8f9fa !important;
    border-color: #dee2e6 !important;
    color: #000000 !important;
    opacity: 1 !important;
}

.ui.selection.dropdown.disabled > .default.text,
.ui.selection.dropdown.disabled > .text {
    color: #000000 !important;
    opacity: 1 !important;
}

.ui.selection.dropdown.disabled:hover {
    border-color: #dee2e6 !important;
    box-shadow: none !important;
}

.ui.form input[readonly],
.ui.form select[disabled],
.ui.form .disabled.field,
.ui.form .disabled.fields .field,
.ui.form .field :disabled {
    color: #000000 !important;
    opacity: 1 !important;
    background-color: #f8f9fa !important;
}

.ui.form .disabled.field label,
.ui.form .disabled.fields .field label,
.ui.form .field :disabled + label {
    color: #495057 !important;
}

.ui.form .field.disabled,
.ui.form .fields.disabled .field,
.ui.form .field.disabled label {
    opacity: 1 !important;
}

.ui.dropdown .menu > .item {
    color: #000000 !important;
}

.ui.dropdown.disabled.selected {
    background-color: #f8f9fa !important;
    border-color: #dee2e6 !important;
}

#page-wrapper {
    overflow-y: auto !important;
    min-height: 100vh !important;
    padding-bottom: 80px !important;
}

.ibox-content {
    padding-bottom: 30px !important;
}

.form-submit-container {
    margin-top: 20px;
    padding-top: 15px;
    padding-bottom: 15px;
    text-align: center;
    clear: both;
}

.btn-primary {
    padding: 8px 20px !important;
    font-size: 16px !important;
    font-weight: 500 !important;
}

.ui.form .fields {
    margin-bottom: 1em !important;
}

#loading-indicator {
    z-index: 10000 !important;
}

.user-role-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 10px;
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
</style>

<body>
<div id="wrapper">
    <?php $activePage = '/dpm/user_transfer.php'; ?>
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
                            User Transfer Form
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

        <div class="wrapper wrapper-content">
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
                    <div class="ibox mb-0">
                        <div id="headersegment">
                            <div class="ibox-content">
                               <form class="ui form disabled-fields-visible" method="post" id="registrationForm" novalidate <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                    <input type="hidden" name="action" value="submitTransferRegistration">
                                    <input type="hidden" name="user_role" id="user_role" value="<?php echo htmlspecialchars($userRole); ?>">
                                    <input type="hidden" name="supervisor_id" id="supervisor_id" value="<?php echo htmlspecialchars($supervisorId ?? ''); ?>">
                                    <h3 class="ui dividing header">Transfer User</h3>
                                    <div class="two fields">
                                        <div class="field">
                                            <label class="required-field"> GID </label>                                        
                                                <div class="gid-input-container">
                                                    <input id="gid" name="gid" class="form-control required" value="" placeholder="-- Type to search GID --" autocomplete="off" required <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                                    <div id="gid_suggestions" class="suggestions-container" style="display: none;"></div>
                                                </div>                                        
                                        </div>
                                        <div class="field">
                                            <label class="required-field" >Name</label>
                                            <input type="text" id="name" name="name" class="form-control" required <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                        </div>
                                    </div>
                                    
                                    <div class="two fields">
                                        <div class="field">
                                            <label class="required-field">Department</label>
                                            <select name="department" id="department" class="ui dropdown search" required <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                                <option value="">-- Select Department --</option>
                                            </select>
                                        </div>
                                        <div class="field">
                                            <label class="required-field">Sub-Department</label>
                                            <select name="sub_department" class="ui dropdown search" required <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                                <option value="">-- Select Sub-Department --</option>
                                                <option value="700">700</option>
                                                <option value="704">704</option>
                                                <option value="720">720</option>
                                                <option value="750">750</option>
                                                <option value="Mechanical Engineering">Mechanical Engineering</option>
                                                <option value="Product Care">Product Care</option>
                                                <option value="warehouse">Warehouse</option>
                                                <option value="packing">Packing</option>
                                                <option value="QC - AISP Domestic">QC - AISP Domestic</option>
                                                <option value="QC - AISP Export">QC - AISP Export</option>
                                                <option value="QC - AISP TF">QC - AISP TF</option>
                                                <option value="QC - AISP">QC - AISP</option>
                                                <option value="QC - SD">QC - SD</option>
                                                <option value="QC - INSP">QC - INSP</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="two fields">
                                        <div class="field">
                                            <label class="required-field">Role</label>
                                            <select name="role" id="role" class="ui dropdown search" required <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                                <option value="">-- Select Role --</option>
                                            </select>
                                        </div>
                                        <div class="field">
                                            <label class="required-field">Group Type</label>
                                            <select name="group_type" class="ui dropdown" required <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                                <option value="">-- Select group Type --</option>
                                                <option value="A">Group A</option>
                                                <option value="B">Group B</option>
                                                <option value="NA">NA</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="two fields">
                                        <div class="field">
                                            <label >In-Company Manager</label>
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
                                    </div>

                                    <div class="two fields">
                                        <div class="field">
                                            <label>Supervisor</label>
                                            <select name="supervisor" id="supervisor" class="ui dropdown search" style="color: #000000 !important;" <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                                <option value="">-- Select Supervisor --</option>
                                            </select>
                                        </div>
                                        <div class="field" id="sponsorField">
                                            <label>Sponsor</label>
                                            <select name="sponsor" id="sponsor" class="ui dropdown search" style="color: #000000 !important;" <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                                <option value="">-- Select Sponsor --</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="two fields">
                                        <div class="field">
                                            <label class="required-field">Joined 01.01.2005</label>
                                            <select name="joined" id="joined" class="ui dropdown search" <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                                <option value="">-- Select Option --</option>
                                                <option value="before">Before</option>
                                                <option value="after">After</option>
                                            </select>
                                        </div>
                                        <div class="field">
                                            <label class="required-field">Type of Employment</label>
                                            <select name="employment_type" id="employment_type" class="ui dropdown search" required <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                                <option value="">-- Select Employment Type --</option>
                                                <option value="Blue Collar">Blue Collar</option>
                                                <option value="Blue Collar Trainee">Blue Collar Trainee</option>
                                                <option value="Blue Collar Contract">Blue Collar Contract</option>
                                                <option value="White Collar">White Collar</option>
                                                <option value="White Collar Contract">White Collar Contract</option>
                                            </select>
                                        </div>
                                    </div>

                                    <h4 class="ui dividing header">Temporary Assignment Details</h4>
                                    <div class="two fields">
                                        <div class="field">
                                            <label class="required-field">Temporary Sub-Department</label>
                                            <select name="temp_sub_department" class="ui dropdown search" required <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                                <option value="">-- Select Sub-Department --</option>
                                                <option value="700">700</option>
                                                <option value="704">704</option>
                                                <option value="720">720</option>
                                                <option value="750">750</option>
                                                <option value="Mechanical Engineering">Mechanical Engineering</option>
                                                <option value="Product Care">Product Care</option>
                                                <option value="warehouse">Warehouse</option>
                                                <option value="packing">Packing</option>
                                                <option value="QC - AISP Domestic">QC - AISP Domestic</option>
                                                <option value="QC - AISP Export">QC - AISP Export</option>
                                                <option value="QC - AISP TF">QC - AISP TF</option>
                                                <option value="QC - AISP">QC - AISP</option>
                                                <option value="QC - SD">QC - SD</option>
                                                <option value="QC - INSP">QC - INSP</option>
                                            </select>
                                        </div>
                                        <div class="field">
                                            <label class="required-field">Temporary Group Type</label>
                                            <select name="temp_group_type" class="ui dropdown" required <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                                <option value="">-- Select group Type --</option>
                                                <option value="A">Group A</option>
                                                <option value="B">Group B</option>
                                                <option value="NA">NA</option>
                                            </select>
                                        </div>    
                                    </div>

                                    <div class="two fields">
                                        <div class="field">
                                            <label class="required-field" for="from">From:</label>
                                            <input type="date" id="from" name="from" class="form-control" required <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                        </div>
        
                                        <div class="field">
                                            <label class="required-field" for="to">To:</label>
                                            <input type="date" id="to" name="to" class="form-control" required <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                        </div>
                                    </div>
                                
                                    <div class="form-submit-container">
                                        <button class="btn btn-primary" type="submit" <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                            Submit Transfer
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div> 
                    </div>
                </div>
            </div>
            <?php $footer_display = 'PMS';
            include_once '../assemblynotes/shared/footer.php'; ?>
        </div>
    </div>
</div>

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
console.log('✅ Supervisor Departments:', SUPERVISOR_DEPARTMENTS);
console.log('✅ Can Access:', CAN_ACCESS);

var currentGIDSuggestions = [];
var displayedGIDCount = 0;
var itemsPerPage = 5;
var isLoadingMore = false;
var allDropdownOptions = null;

$(document).ready(function() {
    if (USER_ROLE === 'supervisor') {
        if (IS_SUPERVISOR) {
            console.log('✅ Supervisor Validated - Can access');
        }
    } else if (USER_ROLE === 'admin') {
        console.log('✅ Admin Access Granted');
    }

    $('.ui.dropdown').dropdown({
        fullTextSearch: true,
        filterRemoteData: true,
        ignoreDiacritics: true,
        selectOnKeydown: false,
        forceSelection: false,
        allowAdditions: false,
        message: {
            noResults: 'No results found.'
        },
        onShow: function() {
            $(this).find('.item').removeClass('selected');
        },
        onChange: function(value, text, $choice) {
            if (value) {
                $(this).dropdown('set selected', value);
            }
        }
    });

    $('.ui.dropdown').on('focus', function() {
        $(this).find('.menu .item').removeClass('selected active');
    });

    loadAllDropdownOptionsNew();
    
    $('#gid').on('input', function() {
        const searchTerm = $(this).val().trim();
        if (searchTerm.length >= 2) {
            currentPage = 0;
            searchEmployeesByGIDOrNameNew(searchTerm);
        } else {
            $('#gid_suggestions').hide();
            resetFormFields();
        }
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.gid-input-container').length) {
            $('#gid_suggestions').hide();
        }
    });

    $('#gid_suggestions').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
    });

    $('#gid_suggestions').on('scroll', function() {
        var container = $(this);
        var scrollTop = container.scrollTop();
        var scrollHeight = container[0].scrollHeight;
        var containerHeight = container.height();
        
        if (scrollTop + containerHeight >= scrollHeight - 10) {
            loadMoreGIDSuggestions();
        }
    });

    $('#registrationForm').on('submit', function(e) {
        e.preventDefault();

        const requiredFields = {
            gid: $('#gid').val().trim(),
            temp_sub_department: $('select[name="temp_sub_department"]').val(),
            temp_group_type: $('select[name="temp_group_type"]').val(),
            from: $('#from').val(),
            to: $('#to').val()
        };

        console.log('✅ Form submitted with values:', requiredFields);

        const emptyFields = [];
        if (!requiredFields.gid) emptyFields.push('GID');
        if (!requiredFields.temp_sub_department) emptyFields.push('Temporary Sub-department');
        if (!requiredFields.temp_group_type) emptyFields.push('Temporary Group Type');
        if (!requiredFields.from) emptyFields.push('From Date');
        if (!requiredFields.to) emptyFields.push('To Date');

        if (emptyFields.length > 0) {
            console.error('❌ Missing required fields:', emptyFields);
            alert('Please fill in all required fields:\n' + emptyFields.join('\n'));
            return;
        }

        if (requiredFields.from && requiredFields.to) {
            const fromDate = new Date(requiredFields.from);
            const toDate = new Date(requiredFields.to);
            if (fromDate > toDate) {
                console.error('❌ From Date cannot be after To Date');
                alert('From Date cannot be after To Date.');
                return;
            }
        }

        const formData = {
            action: 'submitTransferRegistration',
            gid: requiredFields.gid,
            name: $('#name').val().trim(),
            department: $('select[name="department"]').val(),
            sub_department: $('select[name="sub_department"]').val(),
            role: $('select[name="role"]').val(),
            group_type: $('select[name="group_type"]').val(),
            in_company_manager: $('select[name="in_company_manager"]').val(),
            line_manager: $('select[name="line_manager"]').val(),
            temp_sub_department: requiredFields.temp_sub_department,
            temp_group_type: requiredFields.temp_group_type,
            from: requiredFields.from,
            to: requiredFields.to,
            user_role: USER_ROLE,
            supervisor_id: SUPERVISOR_ID
        };

        $('#loading-indicator').show();

        $.ajax({
            url: '/dpm/api/PMSController.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                $('#loading-indicator').hide();
                const result = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (result.success) {
                    console.log('✅ User transfer details updated successfully!');
                    alert('✅ User transfer details updated successfully!');
                    resetFormFields();
                    $('#gid').val('').focus();
                } else {
                    console.error('❌ Error:', result.message);
                    alert('❌ Error: ' + (result.message || 'Unknown error occurred'));
                }
            },
            error: function(xhr, status, error) {
                $('#loading-indicator').hide();
                console.error('❌ Error submitting form:', error);
                alert('❌ Error submitting form: ' + error);
            }
        });
    });
});

// ==================== FIXED FUNCTION NAMES & LOGIC ====================

// ✅ FIXED: Load dropdown options from employee_registration table
function loadAllDropdownOptionsNew() {
    console.log('📋 Loading dropdown options...');
    
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

// ✅ FIXED: Search employees using searchEmployeeRegistration action
function searchEmployeesByGIDOrNameNew(searchTerm) {
    console.log('🔍 Searching for GID/Name:', searchTerm);
    $('#gid_suggestions').html('<div class="suggestion-item">Loading...</div>').show();
    
    const data = {
        "action": "searchEmployeeRegistration",
        "searchTerm": searchTerm
    };
    
    // ✅ FIXED: Add supervisor filter if supervisor
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
            const result = typeof response === 'string' ? JSON.parse(response) : response;
            
            if (result.success) {
                currentGIDSuggestions = result.data || [];
                displayedGIDCount = 0;
                console.log('✅ Found ' + currentGIDSuggestions.length + ' employees');
                displayGIDSuggestions(searchTerm);
            } else {
                console.error('❌ Search failed:', result.message);
                $('#gid_suggestions').html('<div class="suggestion-item">Error: ' + result.message + '</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error:', error);
            $('#gid_suggestions').html('<div class="suggestion-item">Error: ' + error + '</div>');
        }
    });
}

const ITEMS_PER_PAGE = 5;
let currentPage = 0;

function displayGIDSuggestions(searchTerm) {
    var suggestionsHtml = '';
    
    if (!currentGIDSuggestions || currentGIDSuggestions.length === 0) {
        suggestionsHtml = '<div class="suggestion-item">No employees found</div>';
        $('#gid_suggestions').html(suggestionsHtml).show();
        return;
    }
    
    var nextBatch = currentGIDSuggestions.slice(displayedGIDCount, displayedGIDCount + itemsPerPage);
    
    nextBatch.forEach(function(item) {
        // ✅ FIXED: Handle both response formats
        var gidKey = item.key || item.gid || item.GID || item;
        var gidValue = item.value || (item.gid + ' - ' + item.name) || item.gid || item.GID || item;
        
        if (gidKey && gidValue) {
            var highlightedValue = gidValue.toString().replace(
                new RegExp('(' + searchTerm + ')', 'gi'),
                '<strong>$1</strong>'
            );
            suggestionsHtml += '<div class="suggestion-item" onclick="selectGIDNew(\'' + 
                gidKey.replace(/'/g, "\\'") + '\', \'' + gidValue.replace(/'/g, "\\'") + '\')" data-gid="' + gidKey + '">' + 
                highlightedValue + '</div>';
        }
    });
    
    displayedGIDCount += nextBatch.length;
    
    if (displayedGIDCount < currentGIDSuggestions.length) {
        suggestionsHtml += '<div class="suggestion-item load-more-indicator" style="background-color: #e9ecef; color: #6c757d; font-style: italic; text-align: center; cursor: default;" onclick="event.stopPropagation();">Scroll down to load more... (' + (currentGIDSuggestions.length - displayedGIDCount) + ' more)</div>';
    }
    
    $('#gid_suggestions').html(suggestionsHtml).show();
}

function loadMoreGIDSuggestions() {
    if (isLoadingMore || displayedGIDCount >= currentGIDSuggestions.length) {
        return;
    }
    
    isLoadingMore = true;
    var searchTerm = $('#gid').val().trim();
    
    var currentHtml = $('#gid_suggestions').html();
    currentHtml = currentHtml.replace(/<div class="suggestion-item load-more-indicator"[^>]*>.*?<\/div>/, '');
    
    var nextBatch = currentGIDSuggestions.slice(displayedGIDCount, displayedGIDCount + itemsPerPage);
    var newItemsHtml = '';
    
    nextBatch.forEach(function(item) {
        var gidKey = item.key || item.gid || item.GID || item;
        var gidValue = item.value || (item.gid + ' - ' + item.name) || item.gid || item.GID || item;
        
        if (gidKey && gidValue) {
            var highlightedValue = gidValue.toString().replace(
                new RegExp('(' + searchTerm + ')', 'gi'),
                '<strong>$1</strong>'
            );
            newItemsHtml += '<div class="suggestion-item" onclick="selectGIDNew(\'' + 
                gidKey.replace(/'/g, "\\'") + '\', \'' + gidValue.replace(/'/g, "\\'") + '\')" data-gid="' + gidKey + '">' + 
                highlightedValue + '</div>';
        }
    });
    
    displayedGIDCount += nextBatch.length;
    
    if (displayedGIDCount < currentGIDSuggestions.length) {
        newItemsHtml += '<div class="suggestion-item load-more-indicator" style="background-color: #e9ecef; color: #6c757d; font-style: italic; text-align: center; cursor: default;" onclick="event.stopPropagation();">Scroll down to load more... (' + (currentGIDSuggestions.length - displayedGIDCount) + ' more)</div>';
    }
    
    $('#gid_suggestions').html(currentHtml + newItemsHtml);
    
    isLoadingMore = false;
}

// ✅ FIXED: Select GID and fetch employee data
function selectGIDNew(gidKey, gidValue) {
    console.log('✅ Selected GID:', gidKey);
    
    resetFormFields();
    $('#gid').val(gidKey);
    $('#gid_suggestions').hide();
    
    // ✅ FIXED: Use correct action name
    fetchEmployeeBasicDataNew(gidKey);
}

// ✅ FIXED: Fetch employee basic data using correct action
function fetchEmployeeBasicDataNew(gidKey) {
    $('#loading-indicator').show();
    console.log('📥 Fetching employee data for GID:', gidKey);
    
    const data = {
        "action": "fetchEmployeeBasicData",  // ✅ FIXED: Correct action name
        "gid": gidKey
    };
    
    // ✅ Add supervisor validation if needed
    if (IS_SUPERVISOR && !IS_ADMIN) {
        data.supervisor_id = SUPERVISOR_ID;
    }
    
    return $.ajax({
        url: '/dpm/api/PMSController.php',
        method: 'POST',
        dataType: 'json',
        data: data,
        success: function(response) {
            console.log('✅ Employee data response:', response);
            
            if (response.success && response.userData) {
                loadEmployeeDataFromResponseNew(gidKey, response);
            } else {
                const errorMsg = response.message || 'Failed to load employee data.';
                console.error('❌ Error:', errorMsg);
                alert('❌ ' + errorMsg);
                resetFormFields();
                $('#gid').val(gidKey);
                $('#loading-indicator').hide();
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error:', error);
            console.error('Response:', xhr.responseText);
            alert('❌ Error loading employee data. Please try again.');
            resetFormFields();
            $('#gid').val(gidKey);
            $('#loading-indicator').hide();
        }
    });
}

// ✅ FIXED: Load employee data from response
function loadEmployeeDataFromResponseNew(gidKey, response) {
    if (response.success && response.userData) {
        const userData = response.userData;
        
        console.log('📋 Loading employee data:', userData);
        
        // ✅ Set Name field as readonly
        $('#name').val(userData.name || '')
            .prop('readonly', true)
            .css({
                'color': '#000000',
                'opacity': '1',
                'background-color': '#f8f9fa'
            });
        
        const capitalizeFirstLetter = (string) => {
            if (!string) return '';
            return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
        };
        
        // ✅ FIXED: Set all dropdown values correctly
        setDisabledDropdownValue('department', userData.department || '');
        setDisabledDropdownValue('sub_department', userData.sub_department || '');
        setDisabledDropdownValue('role', userData.role || '');
        setDisabledDropdownValue('group_type', userData.group_type || '');
        setDisabledDropdownValue('in_company_manager', userData.in_company_manager || '');
        setDisabledDropdownValue('line_manager', userData.line_manager || '');
        setDisabledDropdownValue('supervisor', userData.supervisor || '');
        setDisabledDropdownValue('sponsor', userData.sponsor || '');
        setDisabledDropdownValue('employment_type', userData.employment_type || '');
        
        const joinedValue = capitalizeFirstLetter(userData.joined || '');
        setDisabledDropdownValue('joined', joinedValue);
        
        // ✅ Clear transfer dates for new entry
        $('#from').val('').prop('disabled', false);
        $('#to').val('').prop('disabled', false);
        
        $('#loading-indicator').hide();
        console.log('✅ Employee data loaded successfully');
    } else {
        const errorMsg = response.message || 'Failed to load employee data.';
        console.error('❌ Error:', errorMsg);
        alert('❌ ' + errorMsg);
        resetFormFields();
        $('#gid').val('');
        $('#loading-indicator').hide();
    }
}

function setDisabledDropdownValue(fieldName, value) {
    if (!value) return;
    
    const dropdown = $(`select[name="${fieldName}"]`);
    const dropdownContainer = dropdown.closest('.ui.dropdown');
    
    let optionExists = false;
    dropdown.find('option').each(function() {
        if ($(this).val() === value) {
            optionExists = true;
            return false;
        }
    });
    
    if (!optionExists && value) {
        dropdown.append(`<option value="${value}">${value}</option>`);
    }
    
    dropdown.val(value);
    
    dropdownContainer.find('.text')
        .text(value)
        .css({
            'color': '#000000',
            'opacity': '1',
            'font-weight': 'normal'
        });
    
    dropdownContainer.addClass('disabled-with-value');
    
    dropdown.prop('disabled', true);
    dropdownContainer.addClass('disabled');
}

function resetFormFields() {
    console.log('🔄 Resetting form fields...');
    
    $('form input[type="text"]').val('');
    $('form input[type="date"]').val('');

    $('.ui.dropdown').each(function() {
        $(this).dropdown('clear');
        $(this).find('.text').text('-- Select --');
    });

    $('#name').prop('readonly', true);
    $('select[name="department"]').prop('disabled', true);
    $('select[name="sub_department"]').prop('disabled', true);
    $('select[name="role"]').prop('disabled', true);
    $('select[name="group_type"]').prop('disabled', true);
    $('select[name="in_company_manager"]').prop('disabled', true);
    $('select[name="line_manager"]').prop('disabled', true);
    $('select[name="supervisor"]').prop('disabled', true);
    $('select[name="sponsor"]').prop('disabled', true);
    $('select[name="employment_type"]').prop('disabled', true);
    $('select[name="joined"]').prop('disabled', true);
    
    console.log('✅ Form reset complete');
}

// ✅ FIXED: Populate dropdowns from response data
function populateDropdowns(options) {
    console.log('📋 Populating dropdowns...');
    
    // ✅ Populate Department dropdown
    var deptDropdown = $('select[name="department"]');
    deptDropdown.empty().append('<option value="">-- Select Department --</option>');
    if (options.departmentList && Array.isArray(options.departmentList)) {
        options.departmentList.forEach(function(dept) {
            if (dept && dept.trim() !== '') {
                deptDropdown.append(`<option value="${dept.department || dept}">${dept.department || dept}</option>`);
            }
        });
    }

    // ✅ Populate Role dropdown
    var roleDropdown = $('select[name="role"]');
    roleDropdown.empty().append('<option value="">-- Select Role --</option>');
    if (options.roleList && Array.isArray(options.roleList)) {
        options.roleList.forEach(function(role) {
            if (role && role.trim() !== '') {
                roleDropdown.append(`<option value="${role.role || role}">${role.role || role}</option>`);
            }
        });
    }

    // ✅ Populate Manager dropdowns
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

    // ✅ Populate Sponsor dropdown
    var sponsorDropdown = $('select[name="sponsor"]');
    sponsorDropdown.empty().append('<option value="">-- Select Sponsor --</option>');
    if (options.sponsorList && Array.isArray(options.sponsorList)) {
        options.sponsorList.forEach(function(sponsor) {
            if (sponsor.value) {
                sponsorDropdown.append(`<option value="${sponsor.value}" data-gid="${sponsor.key || sponsor.value}">${sponsor.value}</option>`);
            }
        });
    }

    $('.ui.dropdown').dropdown('refresh');
    console.log('✅ Dropdowns populated successfully');
}
</script>
</body>
</html>