<!DOCTYPE html>
<html>
<?php
include_once 'core/index.php';

// ===== USER AUTHENTICATION & ROLE VALIDATION =====
$userInfo = SharedManager::getUser();
$current_user_id = $userInfo['GID'] ?? null;
$userModules = $userInfo['Modules'] ?? [];
$isAdmin = 0;
$isSupervisor = 0;
$isPMSAdmin = 0;
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
            $isPMSAdmin = 1;
            $userRole = 'admin';
            SharedManager::checkAuthToModule(20);
            $supervisorValidation['message'] = 'Admin access granted - Full system access';
            error_log("Admin user logged in: " . ($userInfo['GID'] ?? 'Unknown'));
            
        } elseif (in_array(21, $userInfo["Modules"])) {
            $isSupervisor = 1;
            $isPMSAdmin = 2;
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

$canAccess = ($isAdmin === 1 || ($isSupervisor === 1 && $supervisorValidation['is_valid_supervisor']));
?>

<link href="../css/semantic.min.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="../css/dataTables.semanticui.min.css">
<link rel="stylesheet" type="text/css" href="../css/responsive.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<link href="../css/main.css?13" rel="stylesheet"/>
<?php $menu_header_display = 'PMS Module'; ?>
<?php include_once 'shared/headerStyles.php' ?>
<?php include_once '../assemblynotes/shared/headerScripts.php' ?>

<style>
/* ===== SIEMENS COLOR VARIABLES ===== */
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
}

/* ===== ACCESS DENIED MESSAGE BANNER ===== */
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

/* ===== ROLE INDICATORS ===== */
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

    .ui.form.loading {
        position: relative;
        cursor: default;
        pointer-events: none;
        opacity: 0.7;
    }

    .ui.form.loading:before {
        position: absolute;
        content: '';
        top: 0;
        left: 0;
        background: rgba(255,255,255,0.8);
        width: 100%;
        height: 100%;
        z-index: 100;
    }
    
    /* Ensure all fields have equal spacing */
    .four.fields > .field {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
        flex: 1;
    }
    
    .four.fields > .field:first-child {
        padding-left: 0;
    }
    
    .four.fields > .field:last-child {
        padding-right: 0;
    }
    
    /* Make all input fields same height and styling */
    .four.fields .field input,
    .four.fields .field .ui.dropdown,
    .four.fields .field select {
        height: 38px !important;
        min-height: 38px !important;
        padding: 8px 12px !important;
        border: 1px solid rgba(59, 83, 107, 0.15) !important;
        border-radius: 4px !important;
        font-size: 14px !important;
        line-height: 1.5 !important;
        width: 100% !important;
    }
    
    /* Semantic UI dropdown specific styling */
    .four.fields .field .ui.dropdown {
        display: flex !important;
        align-items: center !important;
    }
    
    .four.fields .field .ui.dropdown > .text,
    .four.fields .field .ui.dropdown > .default.text {
        line-height: 1.5 !important;
        padding: 0 !important;
    }
    
    .four.fields .field .ui.dropdown > .dropdown.icon {
        padding: 8px 12px !important;
        line-height: 1.5 !important;
    }
    
    /* Custom styling for date range picker input */
    #dateRange {
        height: 38px !important;
        padding: 8px 12px !important;
        border: 1px solid rgba(34,36,38,.15) !important;
        border-radius: 4px !important;
        width: 100% !important;
        font-size: 14px !important;
        line-height: 1.5 !important;
        box-sizing: border-box !important;
    }
    
    #dateRange:focus {
        border-color: #00b5ad !important;
        outline: none !important;
    }
    
    /* Label styling for consistency */
    .four.fields .field > label {
        display: block !important;
        margin-bottom: 0.5rem !important;
        font-weight: 600 !important;
        font-size: 14px !important;
        color: rgba(0,0,0,.87) !important;
    }
    
    /* Hide the ranges sidebar */
    .daterangepicker .ranges {
        display: none !important;
    }
    
    /* Adjust calendar container when ranges are hidden */
    .daterangepicker .drp-calendar {
        max-width: 100% !important;
    }
    
    .daterangepicker {
        width: auto !important;
    }
    
    /* Report Section Styling - FIT INSIDE IBOX-CONTENT */
    #reportSection {
        margin-top: 30px;
        margin-left: 0;
        margin-right: 0;
        padding: 0;
    }
    
    /* Siemens Color Scheme - Teal/Petrol gradient */
    .report-header {
        background: linear-gradient(135deg, #009999 0%, #006E6E 100%);
        color: white;
        padding: 20px;
        border-radius: 8px 8px 0 0;
        margin-bottom: 0;
        margin-left: 0;
        margin-right: 0;
    }
    
    .table-container {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0 0 8px 8px;
        overflow-x: auto;
        overflow-y: visible;
        margin-left: 0;
        margin-right: 0;
    }
    
    /* CRITICAL FIX: Remove DataTables wrapper padding and spacing */
    .dataTables_wrapper {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .dataTables_wrapper .dataTables_scroll {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .dataTables_wrapper .dataTables_scrollHead {
        display: none !important; /* Hide the duplicate header */
    }
    
    .dataTables_wrapper .dataTables_scrollBody {
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    /* Hide all DataTables UI elements */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate,
    .dataTables_wrapper .dataTables_processing {
        display: none !important;
    }
    
    /* Make table responsive with FIXED LAYOUT */
    #shiftAllocationTable {
        margin: 0 !important;
        width: 100% !important;
        table-layout: fixed !important;
        border-collapse: collapse !important;
    }
    
    #shiftAllocationTable thead {
        background: #f8f9fa !important;
    }
    
    #shiftAllocationTable th:nth-child(1),
    #shiftAllocationTable td:nth-child(1) {
        width: 25% !important;
    }

    #shiftAllocationTable th:nth-child(2),
    #shiftAllocationTable td:nth-child(2) {
        width: 25% !important;
    }

    #shiftAllocationTable th:nth-child(3),
    #shiftAllocationTable td:nth-child(3) {
        width: 25% !important;
    }

    #shiftAllocationTable th:nth-child(4),
    #shiftAllocationTable td:nth-child(4) {
        width: 25% !important;
    }
    
    #shiftAllocationTable th,
    #shiftAllocationTable td {
        padding: 12px 10px !important;
        vertical-align: middle !important;
        text-align: left !important;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        border-left: none !important;
        border-right: none !important;
    }
    
    #shiftAllocationTable th {
        font-weight: 600 !important;
        font-size: 13px !important;
        border-bottom: 2px solid #dee2e6 !important;
        border-top: none !important;
        background-color: #f8f9fa !important;
    }
    
    #shiftAllocationTable td {
        font-size: 13px !important;
        border-bottom: 1px solid #dee2e6 !important;
        border-top: none !important;
    }
    
    #shiftAllocationTable tbody tr {
        border: none !important;
    }
    
    #shiftAllocationTable tbody tr:hover {
        background-color: #f1f3f5 !important;
    }

    .group-header {
        background: linear-gradient(135deg, #00b5ad 0%, #00b5ad 100%) !important;
        color: white !important;
        font-weight: bold !important;
        font-size: 14px !important;
        padding: 12px 10px !important;
    }

    .group-header td {
        color: white !important;
    }

    .group-header .btn-print {
        background-color: white !important;
        color: #00b5ad !important;
        padding: 6px 14px !important;
        font-size: 12px !important;
        border-radius: 4px !important;
        font-weight: 600 !important;
    }

    .group-header .btn-print:hover {
        background-color: #f0f0f0 !important;
        color: #00b5ad !important;
    }

    #shiftAllocationTable tbody tr.shift-row {
        background-color: #ffffff;
    }

    #shiftAllocationTable tbody tr.shift-row:hover {
        background-color: #f1f3f5 !important;
    }
    
    .badge-shift {
        padding: 5px 12px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 11px;
        white-space: nowrap;
        display: inline-block;
    }
    
    .badge-shift-1 {
        background-color: #E8F5E9;
        color: #2E7D32;
    }
    
    .badge-shift-2 {
        background-color: #FFF3E0;
        color: #E65100;
    }
    
    .badge-shift-3 {
        background-color: #E3F2FD;
        color: #00b5ad;
    }
    
    .badge-group {
        padding: 4px 10px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 11px;
        white-space: nowrap;
        display: inline-block;
    }
    
    .badge-group-a {
        background-color: #E1F5FE;
        color: #00b5ad;
    }
    
    .badge-group-b {
        background-color: #FCE4EC;
        color: #880E4F;
    }
    
    .badge-group-na {
        background-color: #F5F5F5;
        color: #616161;
    }

    .action-buttons {
        display: flex;
        gap: 5px;
        justify-content: center;
    }
    
    .btn-action {
        padding: 6px 12px;
        font-size: 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }
    
    .btn-print {
        background-color: #009999;
        color: white;
    }
    
    .btn-print:hover {
        background-color: #006E6E;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    
    .spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #009999;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    #printArea {
        position: fixed;
        left: -9999px;
        top: 0;
        width: 210mm;
        background: white;
        padding: 15mm;
        font-family: Arial, sans-serif;
    }

    @media print {
        .group-header {
            page-break-after: avoid !important;
            background: #009999 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        .shift-row {
            page-break-inside: avoid !important;
        }
        
        .sub-dept-group {
            page-break-inside: avoid !important;
        }
    }

    .print-shifts-container {
        margin-top: 30px;
    }

    .print-shift-block {
        margin-bottom: 30px;
        page-break-inside: avoid;
    }

    .print-shift-block:last-child {
        margin-bottom: 0;
    }

    .print-shift-header {
        background-color: #f0f0f0;
        padding: 10px 15px;
        margin-bottom: 15px;
        border-left: 4px solid #009999;
        font-weight: bold;
        font-size: 16px;
    }

    @media print {
        body * {
            visibility: hidden !important;
        }
        
        #printArea,
        #printArea * {
            visibility: visible !important;
        }
        
        #printArea {
            position: fixed !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            padding: 10mm !important;
            margin: 0 !important;
            background: white !important;
        }
        
        @page {
            margin: 10mm;
            size: A4 portrait;
        }
        
        html, body {
            width: 210mm;
            min-height: 297mm;
        }
        
        .print-header {
            page-break-after: avoid !important;
            page-break-inside: avoid !important;
        }
        
        .print-shift-section {
            page-break-inside: avoid !important;
        }
        
        .print-shift-section {
            page-break-after: auto !important;
        }
        
        table tr {
            page-break-inside: avoid !important;
        }
        
        table, th, td {
            border-color: #000 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        .print-shift-section div,
        thead tr {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            background-color: #f0f0f0 !important;
        }
        
        .print-footer {
            page-break-before: avoid !important;
            page-break-inside: avoid !important;
            margin-top: 20px !important;
        }
    }

    .print-header {
        text-align: center;
        margin-bottom: 15px;
        padding-bottom: 12px;
        border-bottom: 2px solid #009999;
        page-break-after: avoid;
        page-break-inside: avoid;
    }

    .print-header h2 {
        margin: 0 0 10px 0;
        color: #009999;
        font-size: 20px;
        font-weight: bold;
        letter-spacing: 0.3px;
    }

    .print-info {
        margin-top: 8px;
        font-size: 13px;
        color: #666;
        line-height: 1.5;
    }

    .print-info p {
        margin: 6px 0;
        line-height: 1.5;
        font-weight: 600;
    }

    .print-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 14px;
    }

    .print-table th,
    .print-table td {
        border: 1px solid #333;
        padding: 10px 12px;
        text-align: left;
        line-height: 1.5;
    }

    .print-table th {
        background-color: #f0f0f0;
        font-weight: bold;
        color: #333;
    }

    .print-table tr:nth-child(even) {
        background-color: #fafafa;
    }

    .print-footer {
        margin-top: 20px;
        text-align: center;
        color: #666;
        font-size: 12px;
    }

    .print-footer p {
        margin: 5px 0;
        line-height: 1.5;
    }

    /* Button styling for consistency - Siemens colors */
    .field button.btn {
        height: 38px !important;
        padding: 8px 16px !important;
        font-size: 14px !important;
        line-height: 1.5 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        border-radius: 4px !important;
        border: none !important;
        cursor: pointer !important;
        transition: all 0.3s ease !important;
    }
    
    .field button.btn i {
        font-size: 14px !important;
    }
    
    .field button.btn-primary {
        background-color: #009999 !important;
        color: white !important;
    }
    
    .field button.btn-primary:hover {
        background-color: #006E6E !important;
    }
    
    .field button.btn-info {
        background-color: #00B0B9 !important;
        color: white !important;
    }
    
    .field button.btn-info:hover {
        background-color: #008C94 !important;
    }
    
    @media screen and (max-width: 1200px) {
        #shiftAllocationTable th,
        #shiftAllocationTable td {
            font-size: 12px;
            padding: 8px 6px !important;
        }
        
        .badge-shift,
        .badge-group,
        .badge-active {
            font-size: 10px;
            padding: 3px 8px;
        }
    }
</style>

<body>
<div id="wrapper">
    <?php $activePage = '/dpm/Shift_Allocation.php'; ?>
    <?php include_once 'shared/pms_sidebar.php' ?>
    
    <div id="page-wrapper" class="gray-bg">
        <div class="row border-bottom">
            <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                <div class="navbar-header">
                    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary" href="#"><i class="fa fa-bars"></i></a>
                </div>
                <ul class="nav navbar-top-links navbar-right">
                    <li><h2>Shift Allocation Form</h2></li>
                    <li style="margin-right: 20px;">
                        <?php if ($isAdmin): ?>
                            <span class="admin-indicator">
                                <i class="fa fa-shield"></i> ADMIN
                            </span>
                        <?php elseif ($isSupervisor): ?>
                            <span class="supervisor-indicator">
                                <i class="fa fa-user-tie"></i> SUPERVISOR
                            </span>
                        <?php endif; ?>
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
                        <div class="ibox-content">
                            <div id="headersegment">
                                <form class="ui form" id="shiftAllocationForm" <?php echo (!$canAccess ? 'disabled' : ''); ?>>

                                    <h3 class="ui dividing header">Shift Allocation</h3>
                                    
                                    <!-- First Row: Date Range Picker and Dropdowns -->
                                    <div class="four fields">
                                        <div class="field">
                                            <label>Date Range</label>
                                            <input type="text" id="dateRange" name="dateRange" readonly <?php echo (!$canAccess ? 'disabled' : ''); ?> required />
                                        </div>
                                        
                                        <div class="field">
                                            <label>Sub-Department</label>
                                            
                                            <?php if ($isAdmin): ?>
                                                <!-- ADMIN: Show all departments -->
                                                <select name="sub_department" id="sub_department" class="ui dropdown" required>
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
                                                
                                            <?php elseif ($isSupervisor && $supervisorValidation['is_valid_supervisor']): ?>
                                                <!-- SUPERVISOR: Show only their departments -->
                                                <select name="sub_department" id="sub_department" class="ui dropdown" required>
                                                    <option value="">-- Select Sub-Department --</option>
                                                    <?php foreach ($supervisorDepartments as $dept): ?>
                                                        <option value="<?php echo htmlspecialchars($dept); ?>">
                                                            <?php echo htmlspecialchars($dept); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                
                                            <?php else: ?>
                                                <!-- INVALID SUPERVISOR OR NO ACCESS -->
                                                <select name="sub_department" id="sub_department" class="ui dropdown" disabled>
                                                    <option value="" selected>
                                                        No departments available
                                                    </option>
                                                </select>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="field">
                                            <label>Group Type</label>
                                            <select name="group_type" id="group_type" class="ui dropdown" <?php echo (!$canAccess ? 'disabled' : ''); ?> required>
                                                <option value="">-- Select Group Type --</option>
                                                <option value="A">Group A</option>
                                                <option value="B">Group B</option>
                                                <option value="NA">NA</option>
                                            </select>
                                        </div>
                                        
                                        <div class="field">
                                            <label>Shift Type</label>
                                            <select name="shift_type" id="shift_type" class="ui dropdown" <?php echo (!$canAccess ? 'disabled' : ''); ?> required>
                                                <option value="">-- Select Shift Type --</option>
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- Submit Button -->
                                    <div class="field" style="margin-top: 20px;">
                                        <button class="btn btn-primary" type="submit" <?php echo (!$canAccess ? 'disabled' : ''); ?>>
                                            <i class="fa fa-calendar-check-o"></i> Allocate Shift
                                        </button>
                                    </div>
                                    
                                </form>
                            </div> <!-- /#headersegment -->
                            
                            <!-- Report Section - NOW INSIDE IBOX-CONTENT -->
                            <div id="reportSection">
                                
                                <div class="report-header">
                                    <h3 style="margin: 0; display: flex; align-items: center; justify-content: space-between;">
                                        <span><i class="fa fa-bar-chart"></i> Shift Allocation Report</span>
                                        <span id="reportDateRange" style="font-size: 16px; font-weight: normal;"></span>
                                    </h3>
                                </div>
                                
                                <!-- Data Table -->
                                <div class="table-container" style="position: relative;">
                                    <div id="tableLoadingOverlay" class="loading-overlay" style="display: none;">
                                        <div class="spinner"></div>
                                    </div>
                                    <table id="shiftAllocationTable" class="ui celled table" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Group Type</th>
                                                <th>Shift Type</th>
                                                <th>Shift From</th>
                                                <th>Shift To</th>
                                            </tr>
                                        </thead>
                                        <tbody id="reportTableBody">
                                            <!-- Data will be populated here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                        </div> <!-- /.ibox-content -->
                    </div>
                </div>
            </div>
            <?php $footer_display = 'PMS';
            include_once '../assemblynotes/shared/footer.php'; ?>
        </div>
    </div>
</div>

<!-- Print Area - ALWAYS RENDERED -->
<div id="printArea">
    <!-- Content will be populated dynamically -->
</div>

<!-- Mainly scripts -->
<?php include_once '../assemblynotes/shared/headerSemanticScripts.php' ?>
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="shared/shared.js"></script>

<script>
$(document).ready(function() {
    $('.ui.dropdown').dropdown();
    $('.ui.checkbox').checkbox();
    
    // **USER VALIDATION**
    const userRole = '<?php echo $userRole; ?>';
    const supervisorDepts = <?php echo json_encode($supervisorDepartments); ?>;
    const supervisorValidation = <?php echo json_encode($supervisorValidation); ?>;
    const canAccess = <?php echo $canAccess ? 'true' : 'false'; ?>;
    
    // **Display validation status** (without toastr notifications)
    if (userRole === 'supervisor') {
        if (supervisorValidation.is_valid_supervisor) {
            console.log('✅ Supervisor Validated:', supervisorValidation.message);
            
            if (supervisorDepts.length === 1) {
                $('#sub_department').val(supervisorDepts[0]);
                $('#sub_department').dropdown('set selected', supervisorDepts[0]);
                console.log('Auto-selected department:', supervisorDepts[0]);
            }
            
        } 
    } else if (userRole === 'admin') {
        console.log('✅ Admin Access Granted:', supervisorValidation.message);
    }
    
    // Store the selected dates globally
    let selectedStartDate = null;
    let selectedEndDate = null;
    let dataTable = null;

    // Initialize Date Range Picker
    function initializeDatePicker(selector, callback) {
        const start = moment().startOf('week');
        const end = moment().endOf('week');

        $(selector).daterangepicker({
            startDate: start,
            endDate: end,
            showDropdowns: true,
            alwaysShowCalendars: true,
            autoApply: true,
            ranges: {},
            locale: {
                format: 'MM/DD/YYYY'
            }
        }, callback);

        callback(start, end);
    }

    // Initialize the date picker with callback
    initializeDatePicker('#dateRange', function(start, end) {
        selectedStartDate = start;
        selectedEndDate = end;
        $('#dateRange').val(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
        console.log('Date range selected:', start.format('MM/DD/YYYY'), 'to', end.format('MM/DD/YYYY'));
    });

    // Function to load report data
    function loadReportData(fromDate, toDate, subDept, groupType, shiftType, showMessage) {
        $('#tableLoadingOverlay').show();
        
        if (dataTable) {
            dataTable.destroy();
            dataTable = null;
        }
        
        let url = '/dpm/api/PMSController.php?action=getShiftAllocations&_=' + new Date().getTime();
        
        console.log('Loading ALL report data from:', url);
        
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            cache: false,
            success: function(response) {
                console.log('Report data received:', response);
                
                if (response && response.success) {
                    if (response.data && response.data.length > 0) {
                        populateReportTable(response.data);
                        
                        if (showMessage !== false) {
                            console.log('✅ Report loaded successfully with', response.data.length, 'record(s)');
                        }
                    } else {
                        $('#reportTableBody').html('<tr><td colspan="4" style="text-align: center; padding: 30px;">No shift allocations found</td></tr>');
                        
                        if (showMessage !== false) {
                            console.log('⚠️ No data found');
                        }
                    }
                } else {
                    console.error('❌ Failed to load report data:', response && response.message ? response.message : 'Unknown error');
                    $('#reportTableBody').html('<tr><td colspan="4" style="text-align: center; padding: 30px;">Error loading data</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', {xhr: xhr, status: status, error: error});
                $('#reportTableBody').html('<tr><td colspan="4" style="text-align: center; padding: 30px;">Failed to load data</td></tr>');
            },
            complete: function() {
                $('#tableLoadingOverlay').hide();
            }
        });
    }

    // **Function to populate report table with grouped data**
    function populateReportTable(data) {
        let tbody = $('#reportTableBody');
        
        if (data.length === 0) {
            tbody.html('<tr><td colspan="4" style="text-align: center; padding: 30px;">No allocations found</td></tr>');
            return;
        }
        
        if (dataTable) {
            dataTable.destroy();
            dataTable = null;
        }
        
        // **DEDUPLICATE**
        let allocationMap = {};
        data.forEach(function(row) {
            let key = `${row.sub_department}|${row.group_type}|${row.shift_type}|${row.shift_from}|${row.shift_to}`;
            allocationMap[key] = row;
        });
        
        let uniqueData = Object.values(allocationMap);
        
        // **GROUP DATA BY SUB-DEPARTMENT**
        let groupedData = {};
        uniqueData.forEach(function(row) {
            if (!groupedData[row.sub_department]) {
                groupedData[row.sub_department] = [];
            }
            groupedData[row.sub_department].push(row);
        });
        
        let sortedDepts = Object.keys(groupedData).sort();
        let globalIndex = 0;
        
        tbody.empty();
        
        // Populate table with grouped data
        sortedDepts.forEach(function(subDept) {
            let shifts = groupedData[subDept];
            
            shifts.sort(function(a, b) {
                let groupOrder = {'A': 1, 'B': 2, 'NA': 3};
                let groupCompare = (groupOrder[a.group_type] || 999) - (groupOrder[b.group_type] || 999);
                
                if (groupCompare !== 0) {
                    return groupCompare;
                }
                
                return parseInt(a.shift_type) - parseInt(b.shift_type);
            });
            
            let headerRow = `
                <tr class="group-header">
                    <td colspan="4" style="border: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span>
                                <i class="fa fa-building-o"></i> ${subDept} 
                                <span style="margin-left: 15px; font-size: 12px; font-weight: normal;">
                                    ${shifts.length} Shift${shifts.length > 1 ? 's' : ''}
                                </span>
                            </span>
                            <button class="btn-action btn-print" onclick="printSubDepartment('${subDept.replace(/'/g, "\\'")}')">
                                <i class="fa fa-print"></i> Print All Shifts
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(headerRow);
            
            shifts.forEach(function(row) {
                let shiftBadge = `<span class="badge-shift badge-shift-${row.shift_type}">Shift ${row.shift_type}</span>`;
                let groupBadge = `<span class="badge-group badge-group-${row.group_type.toLowerCase()}">${row.group_type}</span>`;
                
                let tr = `
                    <tr data-index="${globalIndex}" data-subdept="${subDept}" class="shift-row">
                        <td>${groupBadge}</td>
                        <td>${shiftBadge}</td>
                        <td>${row.shift_from_formatted}</td>
                        <td>${row.shift_to_formatted}</td>
                    </tr>
                `;
                tbody.append(tr);
                globalIndex++;
            });
        });
        
        window.allocationData = uniqueData;
        window.groupedAllocationData = groupedData;
        
        console.log('✅ Table populated with', uniqueData.length, 'unique records');
    }

    // **AUTO-LOAD REPORT ON PAGE LOAD**
    setTimeout(function() {
        console.log('Auto-loading all shift allocations...');
        loadReportData(null, null, '', '', '', false);
    }, 500);

    // Form submission
    $('#shiftAllocationForm').on('submit', function(e) {
        e.preventDefault();
        
        let isValid = true;
        
        if (!$('#dateRange').val() || !selectedStartDate || !selectedEndDate) {
            isValid = false;
            $('#dateRange').closest('.field').addClass('error');
            console.error('❌ Please select a date range');
        } else {
            $('#dateRange').closest('.field').removeClass('error');
        }
        
        $(this).find('select[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).closest('.field').addClass('error');
            } else {
                $(this).closest('.field').removeClass('error');
            }
        });

        if (!isValid) {
            console.error('❌ Please fill in all required fields');
            return;
        }

        let formData = new FormData(this);
        formData.append('action', 'updateShiftAllocation');
        formData.delete('dateRange');
        formData.append('from_date', selectedStartDate.format('MM/DD/YYYY'));
        formData.append('to_date', selectedEndDate.format('MM/DD/YYYY'));
        formData.append('from_date_db', selectedStartDate.format('YYYY-MM-DD'));
        formData.append('to_date_db', selectedEndDate.format('YYYY-MM-DD'));

        $(this).addClass('loading');

        $.ajax({
            url: '/dpm/api/PMSController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response && response.success) {
                    console.log('✅ Shift allocated successfully');
                    console.log('Response:', response);
                    
                    setTimeout(function() {
                        console.log('🔄 Auto-refreshing page...');
                        location.reload();
                    }, 2000);
                    
                } else {
                    console.error('❌ Operation failed:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ AJAX error:', status, error);
            },
            complete: function() {
                $('#shiftAllocationForm').removeClass('loading');
            }
        });
    });
    
});

// Print function
function printSubDepartment(subDept) {
    console.log('Print button clicked for sub-department:', subDept);
    
    if (!window.groupedAllocationData || !window.groupedAllocationData[subDept]) {
        console.error('❌ Data not found for this sub-department');
        return;
    }
    
    let shifts = window.groupedAllocationData[subDept];
    const originalTitle = document.title;
    document.title = `Shift Allocation - ${subDept}`;
    
    console.log('Fetching employee data for print...');
    
    $.ajax({
        url: '/dpm/api/PMSController.php',
        type: 'GET',
        data: {
            action: 'getShiftAllocations',
            sub_department: subDept,
            include_employees: 'true'
        },
        dataType: 'json',
        success: function(response) {
            console.log('API Response:', response);
            
            if (response.success && response.data && response.data.length > 0) {
                let sortedShifts = response.data.sort((a, b) => {
                    return parseInt(a.shift_type) - parseInt(b.shift_type);
                });
                
                let shiftsContent = '';
                sortedShifts.forEach(function(shift, index) {
                    shiftsContent += buildShiftTable(shift, shift.employees || [], index, sortedShifts.length);
                });
                
                let printContent = buildPrintContent(subDept, sortedShifts, shiftsContent);
                $('#printArea').html(printContent);
                
                setTimeout(function() {
                    window.print();
                    setTimeout(function() {
                        document.title = originalTitle;
                    }, 500);
                }, 250);
            } else {
                console.error('❌ No allocated shifts found for this sub-department');
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Error fetching employee data:', error);
        }
    });
}

function buildShiftTable(shift, employees, index, totalShifts) {
    let employeeRows = '';
    let srNo = 1;
    
    if (employees && employees.length > 0) {
        employees.forEach(function(emp) {
            employeeRows += `
                <tr>
                    <td style="border: 1px solid #333; padding: 6px; text-align: center; font-size: 12px;">${srNo}</td>
                    <td style="border: 1px solid #333; padding: 6px; text-align: center; font-size: 12px;">${emp.gid || ''}</td>
                    <td style="border: 1px solid #333; padding: 6px; text-align: left; font-size: 12px;">${emp.employee_name || emp.name || ''}</td>
                </tr>
            `;
            srNo++;
        });
    } else {
        employeeRows = `
            <tr>
                <td colspan="3" style="border: 1px solid #333; padding: 10px; text-align: center; font-size: 12px; color: #666; font-style: italic;">
                    No employees assigned to this shift
                </td>
            </tr>
        `;
    }
    
    return `
        <div class="print-shift-section" style="margin-bottom: ${index < totalShifts - 1 ? '20px' : '10px'}; page-break-inside: avoid;">
            <div style="background-color: #f0f0f0; padding: 8px 12px; margin-bottom: 8px; border-left: 4px solid #3574a8ff; font-weight: bold; font-size: 14px;">
                SHIFT ${shift.shift_type} ${employees && employees.length > 0 ? '(' + employees.length + ' employee' + (employees.length > 1 ? 's' : '') + ')' : '(0 employees)'}
            </div>
            <table style="width: 100%; border-collapse: collapse; border: 2px solid #333; margin: 0;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th style="border: 1px solid #333; padding: 8px; text-align: center; font-weight: bold; width: 10%; font-size: 12px;">Sr.No</th>
                        <th style="border: 1px solid #333; padding: 8px; text-align: center; font-weight: bold; width: 20%; font-size: 12px;">GID</th>
                        <th style="border: 1px solid #333; padding: 8px; text-align: left; font-weight: bold; width: 70%; font-size: 12px;">Name</th>
                    </tr>
                </thead>
                <tbody>
                    ${employeeRows}
                </tbody>
            </table>
        </div>
    `;
}

function buildPrintContent(subDept, shifts, shiftsContent) {
    let totalEmployees = shifts.reduce((sum, shift) => sum + (shift.employee_count || 0), 0);
    
    return `
        <div class="print-header">
            <h2>Shift Allocation Report</h2>
            <div class="print-info">
                <p><strong>From:</strong> ${shifts[0].shift_from_formatted} | <strong>To:</strong> ${shifts[0].shift_to_formatted}</p>
                <p><strong>Group:</strong> ${shifts[0].group_type} - ${subDept}</p>
                <p><strong>Total Shifts:</strong> ${shifts.length} | <strong>Total Employees:</strong> ${totalEmployees}</p>
            </div>
        </div>
        <div class="print-shifts-container">
            ${shiftsContent}
        </div>
    `;
}

</script>