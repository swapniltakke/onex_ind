<!DOCTYPE html>
<html>
<?php
// ===== INCLUDE CORE FIRST =====
include_once 'core/index.php';

// ===== DETERMINE USER ROLE AND VALIDATE SUPERVISOR =====
$userRole = null;
$isPMSAdmin = 0;
$userInfo = SharedManager::getUser();
$supervisorDepartments = [];
$supervisorValidation = [
    'is_valid_supervisor' => false,
    'supervised_employees' => 0,
    'message' => ''
];
$accessDeniedMessage = null;
$accessDeniedType = null;
$hasFullAccess = false;

try {
    $pdoManager = new PDOManager('spectra_db');
    
    if (in_array(20, $userInfo["Modules"])) {
        // **ADMIN ROLE**
        $isPMSAdmin = 1;
        $userRole = 'admin';
        SharedManager::checkAuthToModule(20);
        $hasFullAccess = true;
        
        $supervisorValidation['message'] = 'Admin access granted - Full system access';
        $accessDeniedType = 'success';
        error_log("Admin user logged in: " . ($userInfo['GID'] ?? 'Unknown'));
        
    } elseif (in_array(21, $userInfo["Modules"])) {
        // **SUPERVISOR ROLE - VALIDATE AND FETCH DEPARTMENTS**
        $isPMSAdmin = 2;
        $userRole = 'supervisor';
        SharedManager::checkAuthToModule(21);
        
        $supervisorId = $userInfo['GID'] ?? null;
        
        if (!$supervisorId) {
            $supervisorValidation['is_valid_supervisor'] = false;
            $supervisorValidation['message'] = 'Error: Supervisor ID not found';
            $accessDeniedMessage = 'Error: Supervisor ID not found in user session';
            $accessDeniedType = 'error';
            error_log("Supervisor validation failed: No GID found for user");
        } else {
            // **CHECK IF USER EXISTS IN EMPLOYEE_REGISTRATION AS SUPERVISOR**
            $checkSupervisorQuery = "SELECT gid, name, supervisor, sub_department 
                                    FROM employee_registration 
                                    WHERE gid = :supervisor_id 
                                    LIMIT 1";
            
            $supervisorCheck = $pdoManager->fetchQueryData($checkSupervisorQuery, [':supervisor_id' => $supervisorId]);
            
            if (isset($supervisorCheck['data']) && !empty($supervisorCheck['data'])) {
                $supervisorRecord = $supervisorCheck['data'][0];
                
                // **FETCH ALL UNIQUE DEPARTMENTS UNDER THIS SUPERVISOR**
                $getDepartmentsQuery = "SELECT DISTINCT department, sub_department 
                                       FROM employee_registration 
                                       WHERE CONCAT(',', TRIM(REPLACE(supervisor, ' ', '')), ',') LIKE :supervisor_id
                                       AND department IS NOT NULL
                                       AND department != ''
                                       ORDER BY department ASC";
                
                $departmentsResult = $pdoManager->fetchQueryData($getDepartmentsQuery, [':supervisor_id' => '%,' . $supervisorId . ',%']);
                
                if (isset($departmentsResult['data']) && !empty($departmentsResult['data'])) {
                    // **VALID SUPERVISOR WITH DEPARTMENTS**
                    $supervisorDepartments = array_unique(array_column($departmentsResult['data'], 'department'));
                    
                    // Count total employees
                    $countQuery = "SELECT COUNT(*) as total_employees 
                                  FROM employee_registration 
                                  WHERE CONCAT(',', TRIM(REPLACE(supervisor, ' ', '')), ',') LIKE :supervisor_id";
                    
                    $countResult = $pdoManager->fetchQueryData($countQuery, [':supervisor_id' => '%,' . $supervisorId . ',%']);
                    $totalEmployees = isset($countResult['data'][0]['total_employees']) ? $countResult['data'][0]['total_employees'] : 0;
                    
                    $supervisorValidation['is_valid_supervisor'] = true;
                    $supervisorValidation['supervised_employees'] = $totalEmployees;
                    $supervisorValidation['message'] = "Supervisor access granted - Managing $totalEmployees employee(s) in " . count($supervisorDepartments) . " department(s)";
                    $hasFullAccess = true;
                    $accessDeniedType = 'success';
                    
                    error_log("Supervisor $supervisorId validated successfully - Departments: " . implode(', ', $supervisorDepartments));
                    
                } else {
                    // **SUPERVISOR RECORD EXISTS BUT NO EMPLOYEES ASSIGNED**
                    $supervisorValidation['is_valid_supervisor'] = false;
                    $supervisorValidation['supervised_employees'] = 0;
                    $supervisorValidation['message'] = "Supervisor record found but no employees assigned";
                    $accessDeniedMessage = "Supervisor record found but no employees assigned to manage";
                    $accessDeniedType = 'warning';
                    
                    error_log("Supervisor $supervisorId has no employees assigned");
                }
            } else {
                // **SUPERVISOR NOT FOUND IN EMPLOYEE_REGISTRATION**
                $supervisorValidation['is_valid_supervisor'] = false;
                $supervisorValidation['message'] = "Error: Supervisor record not found in employee database";
                $accessDeniedMessage = "Error: Supervisor record not found in employee database";
                $accessDeniedType = 'error';
                
                error_log("Supervisor validation failed: User $supervisorId not found in employee_registration table");
            }
        }
        
    } else {
        $isPMSAdmin = 0;
        $userRole = 'user';
        $accessDeniedMessage = "Access Denied: You do not have the required permissions to access this page.";
        $accessDeniedType = 'error';
        error_log("Regular user attempted to access Attendance Reports page: " . ($userInfo['GID'] ?? 'Unknown'));
    }
    
} catch (Exception $e) {
    error_log("Error in supervisor validation: " . $e->getMessage());
    $supervisorValidation['message'] = 'Error: Database connection failed - ' . $e->getMessage();
    $accessDeniedMessage = 'Error: ' . htmlspecialchars($e->getMessage());
    $accessDeniedType = 'error';
}

// **Verify access**
$canAccess = ($userRole === 'admin' || ($userRole === 'supervisor' && $supervisorValidation['is_valid_supervisor']));
if (!$canAccess) {
    error_log("Access denied for user: " . ($userInfo['GID'] ?? 'Unknown') . " - Role: $userRole");
}
?>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Reports</title>
    
    <!-- CRITICAL: Define JavaScript variables BEFORE any other scripts -->
    <script>
        var isPMSAdmin = <?php echo $isPMSAdmin; ?>;
        var userRole = '<?php echo $userRole; ?>';
        var supervisorId = '<?php echo isset($userInfo['GID']) ? $userInfo['GID'] : ''; ?>';
        var isValidSupervisor = <?php echo $supervisorValidation['is_valid_supervisor'] ? 'true' : 'false'; ?>;
        var supervisorDepartments = <?php echo json_encode($supervisorDepartments); ?>;
        var supervisorEmployeeCount = <?php echo $supervisorValidation['supervised_employees']; ?>;
        var hasFullAccess = <?php echo $hasFullAccess ? '1' : '0'; ?>;
        
        // ✅ EXPLICIT ADMIN AND SUPERVISOR FLAGS
        var isAdmin = <?php echo ($isPMSAdmin === 1) ? '1' : '0'; ?>;
        var isSupervisor = <?php echo ($isPMSAdmin === 2) ? '1' : '0'; ?>;
        var isRegularUser = <?php echo ($isPMSAdmin === 0) ? '1' : '0'; ?>;
        
        console.log('✅ Attendance Report Permissions:', {
            isPMSAdmin: isPMSAdmin,
            userRole: userRole,
            supervisorId: supervisorId,
            isValidSupervisor: isValidSupervisor,
            supervisorDepartments: supervisorDepartments,
            supervisorEmployeeCount: supervisorEmployeeCount,
            hasFullAccess: hasFullAccess,
            isAdmin: isAdmin,
            isSupervisor: isSupervisor,
            isRegularUser: isRegularUser
        });
    </script>
    
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/semantic.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="../css/dataTables.semanticui.min.css">
    <script src="../js/jquery.min.js"></script>
    <link href="../css/main.css?13" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css" />
    
    <?php include_once 'shared/dto_headerStyles.php' ?>
    <?php include_once '../assemblynotes/shared/headerScripts.php' ?>
    
    <style>
        /* ===== ACCESS DENIED/ERROR MESSAGE BANNER ===== */
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

        /* ===== SUPERVISOR VALIDATION MESSAGE ===== */
        .supervisor-validation-message {
            padding: 12px 15px;
            margin: 15px 0;
            border-radius: 4px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .supervisor-validation-message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .supervisor-validation-message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .supervisor-validation-message i {
            font-size: 16px;
        }

        /* Custom styles for the attendance report */
        .report-header {
            background-color: #f3f3f4;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .filter-section {
            background-color: #ffffff;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #e7eaec;
            border-radius: 5px;
        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        
        .report-table th {
            background-color: #004d54;
            font-weight: 600;
            color: #ffffff;
        }
        
        .month-header {
            background-color: #1ab394 !important;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .quarter-header {
            background-color: #23c6c8 !important;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .sub-header {
            background-color: #f9f9f9;
            font-weight: 600;
        }
        
        .attendance-cell {
            background-color: #f0f8ff;
        }
        
        .overtime-cell {
            background-color: #fff0f0;
        }
        
        .quarter-att-cell {
            background-color: #e6f7ff;
            font-weight: 600;
        }
        
        .quarter-ot-cell {
            background-color: #ffe6e6;
            font-weight: 600;
        }
        
        .department-cell {
            font-weight: bold;
            background-color: #e7eaec;
        }
        
        .sub-department-cell {
            padding-left: 15px !important;
            text-align: left !important;
        }
        
        .export-buttons {
            margin-bottom: 15px;
        }
        
        .export-buttons .btn {
            margin-right: 5px;
        }
        
        /* Responsive table styles */
        @media (max-width: 767px) {
            .table-responsive {
                border: none;
                overflow-x: auto;
            }
        }
        
        .footer {
            padding: 10px;
            background-color: #f3f3f4;
            border-top: 1px solid #e7eaec;
            margin-top: 20px;
        }
        
        #wrapper {
            width: 100%;
        }
        
        #page-wrapper {
            padding: 0 15px;
            min-height: 568px;
            background-color: white;
        }
        
        /* Loading spinner */
        .loader {
            border: 5px solid #f3f3f4;
            border-top: 5px solid #1ab394;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
            margin: 20px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Additional styles */
        select.form-control:not([size]):not([multiple]) {
            height: unset !important;
            font-size: 1rem;
        }

        #textAreaUpdateNote{
            overflow: hidden;
            height: 12rem;
            font-size: 1rem;
        }

        #date_filter {
            background-color: #00646E;
            border-color: #00646E;
            color: #FFFFFF;
        }

        #planned_month_filter {
            background-color: #00AF8E;
            border-color: #00AF8E;
            color: #FFFFFF;
        }

        /* ===== HIDE EXPORT BUTTONS FOR NON-ADMIN ===== */
        .export-buttons.admin-only {
            display: none;
        }

        .export-buttons.admin-only.visible {
            display: block;
        }

        /* ===== SUPERVISOR INFO ===== */
        .supervisor-info-banner {
            background-color: #e3f2fd;
            border-left: 4px solid #1976d2;
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 3px;
            display: none;
        }

        .supervisor-info-banner.visible {
            display: block;
        }

        /* ===== TAB STYLES ===== */
        .nav-tabs {
            border-bottom: 2px solid #e7eaec;
            display: flex;
            gap: 10px;
        }

        .nav-tabs .nav-item {
            margin-bottom: -2px;
        }

        .nav-tabs .nav-link {
            color: #666;
            border: 1px solid transparent;
            border-radius: 4px 4px 0 0;
            padding: 10px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: #f9f9f9;
        }

        .nav-tabs .nav-link:hover {
            border-color: #ddd;
            background-color: #f3f3f4;
        }

        .nav-tabs .nav-link.active {
            color: #fff;
            background-color: #1ab394;
            border-color: #1ab394;
        }

        .tab-content {
            border: 1px solid #e7eaec;
            border-top: none;
            padding: 15px;
            border-radius: 0 0 4px 4px;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.show {
            display: block;
        }

        /* ===== DAY-WISE TABLE SPECIFIC STYLES ===== */
        .daywise-day-header {
            writing-mode: vertical-rl;
            text-orientation: mixed;
            padding: 8px 2px !important;
            font-size: 0.75rem;
            height: 80px;
        }

        @media print {
            .daywise-day-header {
                writing-mode: horizontal-tb;
                height: auto;
            }
        }

        /* ===== RESPONSIVE BANNER ===== */
        @media (max-width: 768px) {
            .access-denied-banner {
                flex-direction: column;
                gap: 12px;
            }

            .access-denied-banner-close {
                margin-left: 0;
                align-self: flex-end;
            }
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <?php $activePage = '/dpm/attendance_reports.php'; ?>
        <?php include_once 'shared/pms_sidebar.php'; ?>

        <div id="page-wrapper" class="gray-bg">
            <div class="row border-bottom">
                <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                    <div class="navbar-header">
                        <a class="navbar-minimalize minimalize-styl-2 btn btn-primary" href="#">
                            <i class="fa fa-bars"></i>
                        </a>
                    </div>
                    <ul class="nav navbar-top-links navbar-right">
                        <li><h2>Attendance Reports</h2></li>
                    </ul>
                </nav>
            </div>
            
            <div class="wrapper wrapper-content animated fadeInRight">
                <!-- ===== ACCESS DENIED/WARNING BANNER ===== -->
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
                                    <?php echo htmlspecialchars($accessDeniedMessage ?? $supervisorValidation['message']); ?>
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
                            <div class="ibox-title">
                                <h5>Attendance & Overtime Reports</h5>
                                <div class="ibox-tools">
                                    <a class="collapse-link">
                                        <i class="fa fa-chevron-up"></i>
                                    </a>
                                    <a class="fullscreen-link">
                                        <i class="fa fa-expand"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="ibox-content">
                                <!-- Department Report Section -->
                                <div id="department-report">
                                    <div class="report-header">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h3><i class="fa fa-calendar-check-o"></i> Monthly Attendance & Overtime Report</h3>
                                                <p>Department-wise and month-wise attendance and overtime data.</p>
                                            </div>
                                            <div class="col-md-6 text-right">
                                                <!-- ✅ EXPORT BUTTONS - ADMIN ONLY -->
                                                <div class="export-buttons admin-only" id="export-buttons-admin">
                                                    <button class="btn btn-primary" onclick="exportToPDF()">
                                                        <i class="fa fa-file-pdf-o"></i> Export to PDF
                                                    </button>
                                                    <button class="btn btn-success" onclick="exportToExcel()">
                                                        <i class="fa fa-file-excel-o"></i> Export to Excel
                                                    </button>
                                                    <button class="btn btn-info" onclick="printReport()">
                                                        <i class="fa fa-print"></i> Print
                                                    </button>
                                                </div>

                                                <!-- ✅ BASIC PRINT ONLY FOR SUPERVISOR -->
                                                <div id="print-buttons-supervisor" style="display: none;">
                                                    <button class="btn btn-info" onclick="printReport()">
                                                        <i class="fa fa-print"></i> Print
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Filter Section -->
                                    <div class="filter-section">
                                        <!-- ✅ SUPERVISOR INFO BANNER -->
                                        <div class="supervisor-info-banner" id="supervisor-info-banner">
                                            <i class="fa fa-info-circle"></i>
                                            <strong>Note:</strong> You can only view attendance data for your supervised departments and their sub-departments.
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Department</label>
                                                    <select class="form-control" id="department-filter">
                                                        <option value="all">All Departments</option>
                                                        <!-- Departments will be loaded dynamically -->
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Sub-Department</label>
                                                    <select name="sub_department" class="form-control" id="sub-department-filter">
                                                        <option value="">-- Select Sub-Department --</option>
                                                        <option value="all">All Sub-Departments</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Year</label>
                                                    <select class="form-control" id="year-filter">
                                                        <!-- Years will be populated dynamically from 2025 to current year -->
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Quarter</label>
                                                    <select class="form-control" id="quarter-filter">
                                                        <option value="all">All Quarters</option>
                                                        <option value="q1">Q1 (Jan-Mar)</option>
                                                        <option value="q2">Q2 (Apr-Jun)</option>
                                                        <option value="q3">Q3 (Jul-Sep)</option>
                                                        <option value="q4" selected>Q4 (Oct-Dec)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button class="btn btn-primary btn-block" id="generate-report-btn">
                                                        <i class="fa fa-filter"></i> Apply Filters
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="show-quarters" checked>
                                                    <label class="form-check-label" for="show-quarters">
                                                        Show Quarter Totals
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Department Report Content -->
                                    <div id="department-report-content" class="table-responsive">
                                        <!-- Report data will be loaded here -->
                                        <div class="text-center p-5">
                                            <p>Click "Apply Filters" to generate the report</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <?php $footer_display = 'Attendance Reports';
            include_once '../assemblynotes/shared/footer.php'; ?>
        </div>
    </div>

    <!-- Core Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Additional scripts -->
    <?php include_once '../assemblynotes/shared/headerSemanticScripts.php' ?>
    <script src="shared/shared.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
    
    <!-- Main Script -->
    <script>
    // ===== USER ROLE CONSTANTS =====
    const USER_IS_ADMIN = (typeof isAdmin !== 'undefined' && isAdmin === 1);
    const USER_IS_SUPERVISOR = (typeof isSupervisor !== 'undefined' && isSupervisor === 1);
    const USER_IS_REGULAR = (typeof isRegularUser !== 'undefined' && isRegularUser === 1);
    const USER_ROLE = (typeof userRole !== 'undefined') ? userRole : 'user';
    const SUPERVISOR_DEPARTMENTS = (typeof supervisorDepartments !== 'undefined') ? supervisorDepartments : [];
    const SUPERVISOR_ID = (typeof supervisorId !== 'undefined') ? supervisorId : '';
    const HAS_FULL_ACCESS = (typeof hasFullAccess !== 'undefined') ? hasFullAccess : 0;

    console.log('✅ Attendance Report Permissions:', {
        isAdmin: USER_IS_ADMIN,
        isSupervisor: USER_IS_SUPERVISOR,
        isRegularUser: USER_IS_REGULAR,
        userRole: USER_ROLE,
        supervisorDepartments: SUPERVISOR_DEPARTMENTS,
        supervisorId: SUPERVISOR_ID,
        hasFullAccess: HAS_FULL_ACCESS
    });

    // ===== BLOCK REGULAR USERS FROM INTERACTING =====
    if (USER_IS_REGULAR) {
        console.warn('⚠️ Regular user attempted to access attendance reports');
    }

    // Month names for display
    const monthNames = {
        1: "Jan", 2: "Feb", 3: "Mar", 4: "Apr", 5: "May", 6: "Jun",
        7: "Jul", 8: "Aug", 9: "Sep", 10: "Oct", 11: "Nov", 12: "Dec"
    };
    
    // Month codes in order
    const monthOrder = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
    
    // Quarter definitions
    const quarters = {
        q1: { name: "Q1", months: [1, 2, 3] },
        q2: { name: "Q2", months: [4, 5, 6] },
        q3: { name: "Q3", months: [7, 8, 9] },
        q4: { name: "Q4", months: [10, 11, 12] }
    };

    // ===== GENERATE DYNAMIC YEARS FROM 2025 TO CURRENT YEAR =====
    function generateYearOptions() {
        const currentYear = new Date().getFullYear();
        const startYear = 2025;
        
        const yearFilter = document.getElementById('year-filter');
        
        if (!yearFilter) {
            console.error("Year filter element not found");
            return;
        }
        
        yearFilter.innerHTML = '';
        
        // Add years from startYear to currentYear (inclusive)
        for (let year = startYear; year <= currentYear; year++) {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            
            // Set current year as selected
            if (year === currentYear) {
                option.selected = true;
            }
            
            yearFilter.appendChild(option);
        }
        
        console.log(`✅ Year options generated from ${startYear} to ${currentYear}`);
    }

    // ===== SETUP ROLE-BASED VISIBILITY =====
    function setupRoleBasedUI() {
        const exportButtonsAdmin = document.getElementById('export-buttons-admin');
        const printButtonsSupervisor = document.getElementById('print-buttons-supervisor');
        const supervisorInfoBanner = document.getElementById('supervisor-info-banner');

        if (USER_IS_ADMIN) {
            // ✅ ADMIN - Show export buttons
            if (exportButtonsAdmin) {
                exportButtonsAdmin.classList.add('visible');
            }
            console.log('🔓 Admin - Full access granted');
        } else if (USER_IS_SUPERVISOR) {
            // ✅ SUPERVISOR - Show print only and info banner
            if (printButtonsSupervisor) {
                printButtonsSupervisor.style.display = 'block';
            }
            if (supervisorInfoBanner) {
                supervisorInfoBanner.classList.add('visible');
            }
            console.log('🔒 Supervisor - Limited access, print only');
        } else if (USER_IS_REGULAR) {
            // ✅ REGULAR USER - Disable all functionality
            const departmentFilter = document.getElementById("department-filter");
            const generateReportBtn = document.getElementById("generate-report-btn");
            
            if (departmentFilter) departmentFilter.disabled = true;
            if (generateReportBtn) generateReportBtn.disabled = true;
            
            console.log('❌ Regular user - Access denied');
        }
    }

    function loadAttendanceData() {
    const departmentReportContent = document.getElementById("department-report-content");
    const departmentDropdown = document.getElementById("department-filter");
    const subDepartmentDropdown = document.getElementById("sub-department-filter");
    
    if (!departmentDropdown || !subDepartmentDropdown) {
        console.error("Required dropdown elements not found in the DOM");
        return;
    }
    
    if (departmentReportContent) {
        departmentReportContent.innerHTML = `
            <div class="text-center p-5">
                <div class="loader"></div>
                <p>Loading report data...</p>
            </div>
        `;
    }
    
    departmentDropdown.innerHTML = '<option value="all">Loading departments...</option>';
    departmentDropdown.disabled = true;
    
    console.log("Making AJAX request to fetch attendance data with departments...");
    
    $.ajax({
        url: '/dpm/api/PMSController.php',
        type: 'POST',
        data: {
            action: 'getAttendanceDataWithDepartments',
            is_admin: USER_IS_ADMIN ? 'true' : 'false',
            is_supervisor: USER_IS_SUPERVISOR ? 'true' : 'false',
            supervisor_id: USER_IS_SUPERVISOR ? SUPERVISOR_ID : '',
            supervisor_departments: USER_IS_SUPERVISOR ? JSON.stringify(SUPERVISOR_DEPARTMENTS) : '',
            include_daywise: 'true'
        },
        dataType: 'json',
        success: function(response) {
            console.log("Received attendance response with departments:", response);
            try {
                departmentDropdown.innerHTML = '<option value="all">All Departments</option>';
                
                if (response.success) {
                    window.attendanceData = response.data || [];
                    window.daywiseData = response.daywise_data || [];
                    
                    console.log('✅ Monthly data records:', window.attendanceData.length);
                    console.log('✅ Day-wise data records:', window.daywiseData.length);
                    
                    if (response.departments && response.departments.length > 0) {
                        response.departments.forEach(dept => {
                            const option = document.createElement("option");
                            option.value = dept;
                            option.textContent = dept;
                            departmentDropdown.appendChild(option);
                        });
                        console.log(`✅ Loaded ${response.departments.length} departments`);
                    }
                    
                    window.subDepartmentsByDept = response.subDepartmentsByDept || {};
                    
                    departmentDropdown.disabled = false;
                    departmentDropdown.addEventListener('change', updateSubDepartments);
                    
                    if (departmentReportContent) {
                        departmentReportContent.innerHTML = `
                            <div class="text-center p-5">
                                <p>Click "Apply Filters" to generate the report</p>
                                <div class="alert alert-info mt-3">
                                    <i class="fa fa-info-circle"></i> Data loaded successfully. 
                                    ${response.data ? response.data.length : 0} monthly records and 
                                    ${response.daywise_data ? response.daywise_data.length : 0} day-wise records found.
                                </div>
                            </div>
                        `;
                    }
                } else {
                    console.error('❌ Error loading data:', response.message || 'Unknown error');
                    if (departmentReportContent) {
                        departmentReportContent.innerHTML = `
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> ${response.message || 'No data found'}
                            </div>
                        `;
                    }
                    departmentDropdown.disabled = false;
                }
            } catch (error) {
                console.error('❌ Error processing response:', error);
                if (departmentReportContent) {
                    departmentReportContent.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fa fa-exclamation-circle"></i> Error processing response data: ${error.message}
                        </div>
                    `;
                }
                departmentDropdown.disabled = false;
            }
            
            subDepartmentDropdown.disabled = false;
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error:', status, error);
            console.error('Response Text:', xhr.responseText);
            
            if (departmentReportContent) {
                departmentReportContent.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-circle"></i> Error loading data. Status: ${status}, Error: ${error}
                    </div>
                `;
            }
            departmentDropdown.innerHTML = '<option value="all">All Departments</option>';
            departmentDropdown.disabled = false;
            subDepartmentDropdown.disabled = false;
        }
    });
}

    // ===== UPDATE SUB-DEPARTMENTS FOR SELECTED DEPARTMENT =====
    function updateSubDepartments() {
        const departmentDropdown = document.getElementById("department-filter");
        const subDepartmentDropdown = document.getElementById("sub-department-filter");
        
        if (!departmentDropdown || !subDepartmentDropdown) {
            console.error("Required dropdown elements not found in the DOM");
            return;
        }
        
        const selectedDepartment = departmentDropdown.value;
        
        console.log('Updating sub-departments for selected department:', selectedDepartment);
        
        // ✅ RESET SUB-DEPARTMENT DROPDOWN
        subDepartmentDropdown.innerHTML = '<option value="">-- Select Sub-Department --</option><option value="all">All Sub-Departments</option>';
        
        // ✅ ADD SUB-DEPARTMENTS BASED ON SELECTED DEPARTMENT
        if (selectedDepartment === 'all') {
            if (USER_IS_ADMIN && window.subDepartmentsByDept) {
                const allSubDepts = new Set();
                Object.values(window.subDepartmentsByDept).forEach(subDepts => {
                    subDepts.forEach(subDept => allSubDepts.add(subDept));
                });
                
                Array.from(allSubDepts).sort().forEach(subDept => {
                    const option = document.createElement("option");
                    option.value = subDept;
                    option.textContent = subDept;
                    subDepartmentDropdown.appendChild(option);
                });
                
                console.log('✅ Admin - Added all sub-departments');
            } else if (USER_IS_SUPERVISOR && window.subDepartmentsByDept) {
                const supervisorSubDepts = new Set();
                SUPERVISOR_DEPARTMENTS.forEach(dept => {
                    if (window.subDepartmentsByDept[dept]) {
                        window.subDepartmentsByDept[dept].forEach(subDept => {
                            supervisorSubDepts.add(subDept);
                        });
                    }
                });
                
                Array.from(supervisorSubDepts).sort().forEach(subDept => {
                    const option = document.createElement("option");
                    option.value = subDept;
                    option.textContent = subDept;
                    subDepartmentDropdown.appendChild(option);
                });
                
                console.log('✅ Supervisor - Added supervised sub-departments');
            }
        } else if (window.subDepartmentsByDept && window.subDepartmentsByDept[selectedDepartment]) {
            window.subDepartmentsByDept[selectedDepartment].forEach(subDept => {
                const option = document.createElement("option");
                option.value = subDept;
                option.textContent = subDept;
                subDepartmentDropdown.appendChild(option);
            });
            
            console.log('✅ Added sub-departments for department:', selectedDepartment);
        }
        
        subDepartmentDropdown.disabled = false;
    }

    // Filter attendance data by department, sub-department, year and quarter
    function filterAttendanceData(data, department, subDepartment, year, quarter) {
        return data.filter(record => {
            const [recordYear, recordMonth] = record.month_year.split('-');
            
            if (department !== 'all' && record.department !== department) {
                return false;
            }
            
            if (subDepartment !== 'all' && record.effective_sub_department !== subDepartment) {
                return false;
            }
            
            if (recordYear !== year.toString()) {
                return false;
            }
            
            if (quarter !== 'all') {
                const monthNum = parseInt(recordMonth);
                const quarterMonths = quarters[quarter].months;
                return quarterMonths.includes(monthNum);
            }
            
            return true;
        });
    }

    // ✅ NEW: Filter day-wise data by department, sub-department, year and month
    function filterDaywiseData(data, department, subDepartment, year, month) {
        return data.filter(record => {
            const [recordYear, recordMonth] = record.date.split('-').slice(0, 2);
            
            if (department !== 'all' && record.department !== department) {
                return false;
            }
            
            if (subDepartment !== 'all' && record.effective_sub_department !== subDepartment) {
                return false;
            }
            
            if (recordYear !== year.toString()) {
                return false;
            }
            
            if (month && recordMonth !== month.toString().padStart(2, '0')) {
                return false;
            }
            
            return true;
        });
    }

    // ===== GENERATE REPORT WITH TABS =====
    function generateReport() {
        const departmentFilter = document.getElementById("department-filter");
        const subDepartmentFilter = document.getElementById("sub-department-filter");
        const yearFilter = document.getElementById("year-filter");
        const quarterFilter = document.getElementById("quarter-filter");
        const showQuarters = document.getElementById("show-quarters");
        const departmentReportContent = document.getElementById("department-report-content");
        
        if (!departmentFilter || !subDepartmentFilter || !yearFilter || !quarterFilter || !showQuarters || !departmentReportContent) {
            console.error("Required filter elements not found in the DOM");
            return;
        }
        
        const department = departmentFilter.value;
        const subDepartment = subDepartmentFilter.value;
        const year = yearFilter.value;
        const quarter = quarterFilter.value;
        const showQuartersChecked = showQuarters.checked;
        
        departmentReportContent.innerHTML = `
            <div class="text-center p-5">
                <div class="loader"></div>
                <p>Processing report data...</p>
            </div>
        `;
        
        try {
            if (window.attendanceData && window.attendanceData.length > 0) {
                const filteredData = filterAttendanceData(window.attendanceData, department, subDepartment, year, quarter);
                
                if (filteredData.length === 0) {
                    departmentReportContent.innerHTML = `
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> No attendance data found for the selected filters.
                        </div>
                    `;
                    return;
                }
                
                const departmentData = convertToDepartmentFormat(filteredData);
                const tabsHTML = generateReportTabs(departmentData, year, quarter, showQuartersChecked, department, subDepartment);
                
                departmentReportContent.innerHTML = tabsHTML;
                attachTabListeners(department, subDepartment, year, quarter);
                
            } else {
                departmentReportContent.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-circle"></i> No attendance data available. Please refresh the page.
                    </div>
                `;
            }
        } catch (error) {
            console.error('❌ Error generating report:', error);
            departmentReportContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-circle"></i> Error generating report: ${error.message}
                </div>
            `;
        }
    }

    // Generate tabs for monthly and day-wise reports
    function generateReportTabs(departmentData, year, quarter, showQuarters, department, subDepartment) {
        let html = `
            <ul class="nav nav-tabs" role="tablist" style="margin-bottom: 20px;">
                <li class="nav-item">
                    <a class="nav-link active" id="monthly-tab" data-toggle="tab" href="#monthly-report" role="tab">
                        <i class="fa fa-calendar"></i> Monthly Report
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="daywise-tab" data-toggle="tab" href="#daywise-report" role="tab">
                        <i class="fa fa-calendar-o"></i> Day-Wise Report
                    </a>
                </li>
            </ul>
            
            <div class="tab-content">
                <div class="tab-pane fade show active" id="monthly-report" role="tabpanel">
        `;
        
        const monthlyHTML = generateMonthlyReport(departmentData, year, quarter, showQuarters);
        html += monthlyHTML;
        
        html += `
                </div>
                <div class="tab-pane fade" id="daywise-report" role="tabpanel">
                    <div id="daywise-report-content">
                        <div class="text-center p-5">
                            <div class="loader"></div>
                            <p>Loading day-wise data...</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        return html;
    }

    // Generate monthly report HTML
    function generateMonthlyReport(departmentData, year, quarter, showQuarters) {
        let monthsToDisplay = [];
        if (quarter === "all") {
            monthsToDisplay = monthOrder;
        } else {
            monthsToDisplay = quarters[quarter].months;
        }
        
        let html = '<table class="report-table">';
        
        html += '<thead><tr>';
        html += '<th rowspan="2" style="width: 15%;">Department</th>';
        html += '<th rowspan="2" style="width: 20%;">Sub-Department</th>';
        
        monthsToDisplay.forEach(month => {
            html += `<th colspan="2" class="month-header">${monthNames[month]}'${year.toString().substring(2)}</th>`;
        });
        
        if (showQuarters && quarter === "all") {
            Object.keys(quarters).forEach(quarterKey => {
                html += `<th colspan="2" class="quarter-header">${quarters[quarterKey].name}</th>`;
            });
        } else if (showQuarters) {
            html += `<th colspan="2" class="quarter-header">${quarters[quarter].name}</th>`;
        }
        
        html += '</tr>';
        
        html += '<tr>';
        monthsToDisplay.forEach(month => {
            html += '<th class="sub-header">Att</th><th class="sub-header">OT</th>';
        });
        
        if (showQuarters && quarter === "all") {
            Object.keys(quarters).forEach(quarterKey => {
                html += '<th class="sub-header">Att</th><th class="sub-header">OT</th>';
            });
        } else if (showQuarters) {
            html += '<th class="sub-header">Att</th><th class="sub-header">OT</th>';
        }
        
        html += '</tr></thead>';
        
        html += '<tbody>';
        
        let hasDepartments = false;
        
        Object.keys(departmentData).sort().forEach(deptKey => {
            hasDepartments = true;
            const dept = departmentData[deptKey];
            const subDepts = dept.subDepartments;
            let isFirstSubDept = true;
            let rowCount = Object.keys(subDepts).length;
            
            if (rowCount === 0) {
                return;
            }
            
            Object.keys(subDepts).sort().forEach((subDeptKey, index) => {
                const subDept = subDepts[subDeptKey];
                const quarterTotals = calculateQuarterTotals(subDept.months);
                
                html += '<tr>';
                
                if (isFirstSubDept) {
                    html += `<td rowspan="${rowCount}" class="department-cell">${dept.name}</td>`;
                    isFirstSubDept = false;
                }
                
                html += `<td class="sub-department-cell">${subDept.name}</td>`;
                
                monthsToDisplay.forEach(month => {
                    const data = subDept.months[month] || { attendance: 0, overtime: 0 };
                    html += `<td class="attendance-cell">${data.attendance || 0}</td>`;
                    html += `<td class="overtime-cell">${data.overtime || 0}</td>`;
                });
                
                if (showQuarters && quarter === "all") {
                    Object.keys(quarters).forEach(quarterKey => {
                        html += `<td class="quarter-att-cell">${quarterTotals[quarterKey].attendance}</td>`;
                        html += `<td class="quarter-ot-cell">${quarterTotals[quarterKey].overtime}</td>`;
                    });
                } else if (showQuarters) {
                    html += `<td class="quarter-att-cell">${quarterTotals[quarter].attendance}</td>`;
                    html += `<td class="quarter-ot-cell">${quarterTotals[quarter].overtime}</td>`;
                }
                
                html += '</tr>';
            });
            
            const totalColumns = 2 + (monthsToDisplay.length * 2) + (showQuarters ? (quarter === "all" ? 8 : 2) : 0);
            html += `<tr><td colspan="${totalColumns}" style="height: 5px; background-color: #f9f9f9;"></td></tr>`;
        });
        
        html += '</tbody></table>';
        
        if (!hasDepartments) {
            html = `
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> No attendance data found for the selected filters.
                </div>
            `;
        }
        
        return html;
    }

    // Attach tab listeners for day-wise report loading
    function attachTabListeners(department, subDepartment, year, quarter) {
        const daywiseTab = document.getElementById('daywise-tab');
        
        if (daywiseTab) {
            daywiseTab.addEventListener('click', function() {
                loadDaywiseReport(department, subDepartment, year, quarter);
            });
        }
    }

    // Load and generate day-wise report
    function loadDaywiseReport(department, subDepartment, year, quarter) {
        const daywiseReportContent = document.getElementById('daywise-report-content');
        
        if (!daywiseReportContent) {
            console.error("Day-wise report content element not found");
            return;
        }
        
        daywiseReportContent.innerHTML = `
            <div class="text-center p-5">
                <div class="loader"></div>
                <p>Processing day-wise data...</p>
            </div>
        `;
        
        try {
            if (window.daywiseData && window.daywiseData.length > 0) {
                let monthsToProcess = [];
                if (quarter === "all") {
                    monthsToProcess = monthOrder;
                } else {
                    monthsToProcess = quarters[quarter].months;
                }
                
                let daywiseHTML = '';
                
                monthsToProcess.forEach(month => {
                    const filteredDaywise = filterDaywiseData(window.daywiseData, department, subDepartment, year, month);
                    
                    if (filteredDaywise.length > 0) {
                        const monthDaywiseData = convertToDaywiseFormat(filteredDaywise);
                        daywiseHTML += generateDaywiseMonthReport(monthDaywiseData, month, year);
                    }
                });
                
                if (daywiseHTML === '') {
                    daywiseReportContent.innerHTML = `
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> No day-wise data found for the selected filters.
                        </div>
                    `;
                } else {
                    daywiseReportContent.innerHTML = daywiseHTML;
                }
            } else {
                daywiseReportContent.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-circle"></i> No day-wise data available.
                    </div>
                `;
            }
        } catch (error) {
            console.error('❌ Error loading day-wise report:', error);
            daywiseReportContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-circle"></i> Error loading day-wise report: ${error.message}
                </div>
            `;
        }
    }

    // Convert day-wise data to organized format
    function convertToDaywiseFormat(filteredData) {
        const daywiseData = {};
        
        filteredData.forEach(record => {
            const dept = record.department || 'Unassigned';
            const subDept = record.effective_sub_department || 'General';
            const date = record.date;
            const day = parseInt(date.split('-')[2]);
            
            if (!daywiseData[dept]) {
                daywiseData[dept] = {
                    name: dept,
                    subDepartments: {}
                };
            }
            
            if (!daywiseData[dept].subDepartments[subDept]) {
                daywiseData[dept].subDepartments[subDept] = {
                    name: subDept,
                    days: {}
                };
            }
            
            daywiseData[dept].subDepartments[subDept].days[day] = {
                attendance: parseFloat(record.actual_man_hours) || 0,
                overtime: parseFloat(record.overtime_hours) || 0,
                status: record.attendance_status || 'N/A'
            };
        });
        
        return daywiseData;
    }

    // Generate day-wise month report HTML
    function generateDaywiseMonthReport(daywiseData, month, year) {
        const daysInMonth = new Date(year, month, 0).getDate();
        
        let html = `
            <div style="margin-bottom: 30px; page-break-inside: avoid;">
                <h4 style="background-color: #f3f3f4; padding: 10px; margin: 15px 0 10px 0; border-radius: 3px;">
                    ${monthNames[month]} ${year} - Day-Wise Report
                </h4>
                
                <table class="report-table" style="font-size: 0.9rem;">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Department</th>
                            <th style="width: 20%;">Sub-Department</th>
        `;
        
        for (let day = 1; day <= daysInMonth; day++) {
            html += `<th style="width: 2%; padding: 4px 2px; font-size: 0.8rem;">${day}</th>`;
        }
        
        html += `
                            <th style="width: 8%;">Total Att</th>
                            <th style="width: 8%;">Total OT</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        Object.keys(daywiseData).sort().forEach(deptKey => {
            const dept = daywiseData[deptKey];
            const subDepts = dept.subDepartments;
            let isFirstSubDept = true;
            let rowCount = Object.keys(subDepts).length;
            
            Object.keys(subDepts).sort().forEach((subDeptKey, index) => {
                const subDept = subDepts[subDeptKey];
                let totalAttendance = 0;
                let totalOvertime = 0;
                
                html += '<tr>';
                
                if (isFirstSubDept) {
                    html += `<td rowspan="${rowCount}" class="department-cell">${dept.name}</td>`;
                    isFirstSubDept = false;
                }
                
                html += `<td class="sub-department-cell">${subDept.name}</td>`;
                
                for (let day = 1; day <= daysInMonth; day++) {
                    const dayData = subDept.days[day];
                    if (dayData) {
                        totalAttendance += dayData.attendance || 0;
                        totalOvertime += dayData.overtime || 0;
                        
                        let cellColor = '#f0f8ff';
                        if (dayData.status === 'leave') {
                            cellColor = '#ffe6e6';
                        } else if (dayData.status === 'absent') {
                            cellColor = '#fff0f0';
                        }
                        
                        html += `<td style="background-color: ${cellColor}; padding: 4px 2px; text-align: center; font-size: 0.8rem;" title="${dayData.status}">
                            ${dayData.attendance}
                        </td>`;
                    } else {
                        html += `<td style="background-color: #f9f9f9; padding: 4px 2px; text-align: center; font-size: 0.8rem;">-</td>`;
                    }
                }
                
                html += `<td class="quarter-att-cell">${totalAttendance}</td>`;
                html += `<td class="quarter-ot-cell">${totalOvertime}</td>`;
                html += '</tr>';
            });
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
        
        return html;
    }

    // Convert filtered data to department format
    function convertToDepartmentFormat(filteredData) {
        const departmentData = {};
        
        filteredData.forEach(record => {
            const dept = record.department || 'Unassigned';
            const subDept = record.effective_sub_department || 'General';
            const [, recordMonth] = record.month_year.split('-');
            const month = parseInt(recordMonth);
            
            if (!departmentData[dept]) {
                departmentData[dept] = {
                    name: dept,
                    subDepartments: {}
                };
            }
            
            if (!departmentData[dept].subDepartments[subDept]) {
                departmentData[dept].subDepartments[subDept] = {
                    name: subDept,
                    months: {}
                };
            }
            
            departmentData[dept].subDepartments[subDept].months[month] = {
                attendance: parseFloat(record.total_actual_man_hours) || 0,
                overtime: parseFloat(record.total_overtime_hours) || 0
            };
        });
        
        return departmentData;
    }
    
    // Calculate quarter totals for a sub-department
    function calculateQuarterTotals(monthData) {
        const quarterTotals = {};
        
        Object.keys(quarters).forEach(quarterKey => {
            const quarter = quarters[quarterKey];
            let totalAttendance = 0;
            let totalOvertime = 0;
            
            quarter.months.forEach(month => {
                if (monthData[month]) {
                    totalAttendance += monthData[month].attendance || 0;
                    totalOvertime += monthData[month].overtime || 0;
                }
            });
            
            quarterTotals[quarterKey] = {
                attendance: totalAttendance,
                overtime: totalOvertime
            };
        });
        
        return quarterTotals;
    }
    
    // Export to PDF function
    function exportToPDF() {
        if (!USER_IS_ADMIN) {
            if (typeof showNotification === 'function') {
                showNotification('error', 'Export to PDF is only available for administrators.');
            } else {
                alert("Export to PDF is only available for administrators.");
            }
            return;
        }

        const tableHTML = document.getElementById("department-report-content").innerHTML;
        if (!tableHTML.includes('report-table')) {
            if (typeof showNotification === 'function') {
                showNotification('warning', 'Please generate a report first before exporting.');
            } else {
                alert("Please generate a report first before exporting.");
            }
            return;
        }
        
        if (typeof showNotification === 'function') {
            showNotification('info', 'PDF export functionality would be implemented here using jsPDF library.');
        } else {
            alert("PDF export functionality would be implemented here using jsPDF library.");
        }
    }
    
    // Export to Excel function
    function exportToExcel() {
        if (!USER_IS_ADMIN) {
            if (typeof showNotification === 'function') {
                showNotification('error', 'Export to Excel is only available for administrators.');
            } else {
                alert("Export to Excel is only available for administrators.");
            }
            return;
        }

        const tableHTML = document.getElementById("department-report-content").innerHTML;
        if (!tableHTML.includes('report-table')) {
            if (typeof showNotification === 'function') {
                showNotification('warning', 'Please generate a report first before exporting.');
            } else {
                alert("Please generate a report first before exporting.");
            }
            return;
        }
        
        if (typeof showNotification === 'function') {
            showNotification('info', 'Excel export functionality would be implemented here.');
        } else {
            alert("Excel export functionality would be implemented here.");
        }
    }
    
    // Print function
    function printReport() {
        const tableHTML = document.getElementById("department-report-content").innerHTML;
        if (!tableHTML.includes('report-table')) {
            if (typeof showNotification === 'function') {
                showNotification('warning', 'Please generate a report first before printing.');
            } else {
                alert("Please generate a report first before printing.");
            }
            return;
        }
        
        const printContent = document.getElementById('department-report').innerHTML;
        const originalContent = document.body.innerHTML;
        
        document.body.innerHTML = `
            <div style="padding: 20px;">
                <h1 style="text-align: center;">Attendance Report</h1>
                ${printContent}
            </div>
        `;
        
        window.print();
        
        document.body.innerHTML = originalContent;
        
        initializePage();
    }
    
    // Initialize the page when DOM is fully loaded
    function initializePage() {
        try {
            console.log("✅ Initializing Attendance Reports page...");
            
            const yearFilter = document.getElementById("year-filter");
            const generateReportBtn = document.getElementById("generate-report-btn");
            const showQuarters = document.getElementById("show-quarters");
            const departmentReportContent = document.getElementById("department-report-content");
            
            if (!yearFilter || !generateReportBtn || !showQuarters || !departmentReportContent) {
                console.error("❌ Required elements not found in the DOM.");
                return;
            }
            
            // ✅ GENERATE DYNAMIC YEAR OPTIONS FROM 2025 TO CURRENT YEAR
            generateYearOptions();
            
            // ✅ Setup role-based UI
            setupRoleBasedUI();
            
            loadAttendanceData();
            
            generateReportBtn.addEventListener("click", generateReport);
            showQuarters.addEventListener("change", function() {
                if (departmentReportContent.innerHTML.includes('report-table')) {
                    generateReport();
                }
            });
            
            console.log("✅ Page initialization completed successfully.");
        } catch (error) {
            console.error('❌ Error initializing page:', error);
            if (typeof showNotification === 'function') {
                showNotification('error', 'There was an error initializing the page. Please check the console for details.');
            } else {
                alert('There was an error initializing the page. Please check the console for details.');
            }
        }
    }
    
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initializePage);
    } else {
        initializePage();
    }

    </script>
</body>
</html>