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
$hasFullAccess = false;  // ✅ NEW FLAG

try {
    $pdoManager = new PDOManager('spectra_db');
    
    if (in_array(20, $userInfo["Modules"])) {
        // **ADMIN ROLE**
        $isPMSAdmin = 1;
        $userRole = 'admin';
        $hasFullAccess = true;  // ✅ ADMIN HAS FULL ACCESS
        SharedManager::checkAuthToModule(20);
        
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
                
                // **FETCH ALL EMPLOYEES UNDER THIS SUPERVISOR**
                $getEmployeesQuery = "SELECT DISTINCT sub_department 
                                     FROM employee_registration 
                                     WHERE CONCAT(',', TRIM(REPLACE(supervisor, ' ', '')), ',') LIKE :supervisor_id
                                     AND sub_department IS NOT NULL
                                     AND sub_department != ''
                                     ORDER BY sub_department ASC";
                
                $employeesResult = $pdoManager->fetchQueryData($getEmployeesQuery, [':supervisor_id' => '%,' . $supervisorId . ',%']);
                
                if (isset($employeesResult['data']) && !empty($employeesResult['data'])) {
                    // **VALID SUPERVISOR WITH EMPLOYEES**
                    $supervisorDepartments = array_column($employeesResult['data'], 'sub_department');
                    
                    // Count total employees
                    $countQuery = "SELECT COUNT(*) as total_employees 
                                  FROM employee_registration 
                                  WHERE CONCAT(',', TRIM(REPLACE(supervisor, ' ', '')), ',') LIKE :supervisor_id";
                    
                    $countResult = $pdoManager->fetchQueryData($countQuery, [':supervisor_id' => '%,' . $supervisorId . ',%']);
                    $totalEmployees = isset($countResult['data'][0]['total_employees']) ? $countResult['data'][0]['total_employees'] : 0;
                    
                    $supervisorValidation['is_valid_supervisor'] = true;
                    $supervisorValidation['supervised_employees'] = $totalEmployees;
                    $supervisorValidation['message'] = "Supervisor access granted - Managing $totalEmployees employee(s) in " . count($supervisorDepartments) . " department(s)";
                    $hasFullAccess = true;  // ✅ VALID SUPERVISOR HAS ACCESS
                    $accessDeniedType = 'success';
                    
                    error_log("Supervisor $supervisorId validated successfully - Departments: " . implode(', ', $supervisorDepartments));
                    
                } else {
                    // ✅ SHOW WARNING BUT STILL ALLOW PAGE LOAD
                    $supervisorValidation['is_valid_supervisor'] = false;
                    $supervisorValidation['supervised_employees'] = 0;
                    $supervisorValidation['message'] = "Supervisor record found but no employees assigned";
                    $accessDeniedMessage = "Supervisor record found but no employees assigned to manage";
                    $accessDeniedType = 'warning';
                    $hasFullAccess = false;  // ✅ LIMITED ACCESS
                    
                    error_log("Supervisor $supervisorId has no employees assigned");
                }
            } else {
                // ✅ SHOW ERROR BUT STILL ALLOW PAGE LOAD
                $supervisorValidation['is_valid_supervisor'] = false;
                $supervisorValidation['message'] = "Error: Supervisor record not found in employee database";
                $accessDeniedMessage = "Error: Supervisor record not found in employee database";
                $accessDeniedType = 'error';
                $hasFullAccess = false;  // ✅ NO ACCESS
                
                error_log("Supervisor validation failed: User $supervisorId not found in employee_registration table");
            }
        }
        
    } else {
        // **REGULAR USER - NO ACCESS**
        $isPMSAdmin = 0;
        $userRole = 'user';
        $accessDeniedMessage = "Access Denied: You do not have the required permissions to access this page.";
        $accessDeniedType = 'error';
        $hasFullAccess = false;  // ✅ NO ACCESS
        error_log("Regular user attempted to access Employee Leave Details page: " . ($userInfo['GID'] ?? 'Unknown'));
    }
    
} catch (Exception $e) {
    error_log("Error in supervisor validation: " . $e->getMessage());
    $supervisorValidation['message'] = 'Error: Database connection failed - ' . $e->getMessage();
    $accessDeniedMessage = 'Error: ' . htmlspecialchars($e->getMessage());
    $accessDeniedType = 'error';
    $hasFullAccess = false;  // ✅ NO ACCESS ON ERROR
}

// ✅ REMOVED THE HARD die() - Now we just set flags and let the page load with the banner
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Leave Details</title>
    
    <script>
    // ===== USER ROLE CONFIGURATION =====
    var isPMSAdmin = <?php echo $isPMSAdmin; ?>;
    var userRole = '<?php echo $userRole; ?>';
    var supervisorId = '<?php echo isset($userInfo['GID']) ? $userInfo['GID'] : ''; ?>';
    var isValidSupervisor = <?php echo $supervisorValidation['is_valid_supervisor'] ? 'true' : 'false'; ?>;
    var supervisedEmployees = <?php echo $supervisorValidation['supervised_employees']; ?>;
    var hasFullAccess = <?php echo $hasFullAccess ? '1' : '0'; ?>;  // ✅ NEW
    
    // ✅ ADD EXPLICIT ADMIN AND SUPERVISOR FLAGS FOR AJAX
    var isAdmin = <?php echo ($isPMSAdmin === 1) ? '1' : '0'; ?>;
    var isSupervisor = <?php echo ($isPMSAdmin === 2) ? '1' : '0'; ?>;
    var isRegularUser = <?php echo ($isPMSAdmin === 0) ? '1' : '0'; ?>;
    
    // ✅ SUPERVISOR DEPARTMENTS (IF APPLICABLE)
    var supervisorDepartments = <?php echo json_encode($supervisorDepartments); ?>;
    
    console.log('✅ User Permissions Initialized:', {
        isPMSAdmin: isPMSAdmin,
        userRole: userRole,
        isAdmin: isAdmin,
        isSupervisor: isSupervisor,
        isRegularUser: isRegularUser,
        supervisorId: supervisorId,
        isValidSupervisor: isValidSupervisor,
        supervisedEmployees: supervisedEmployees,
        supervisorDepartments: supervisorDepartments,
        hasFullAccess: hasFullAccess  // ✅ LOG THIS
    });
    
    // ✅ VALIDATE ACCESS AND DISABLE TABLE IF NEEDED
    if (!hasFullAccess) {
        console.warn('⚠️ User does not have full access - Table will be disabled');
        // Optionally disable the table functionality
        document.addEventListener('DOMContentLoaded', function() {
            var table = document.getElementById('table_open_items');
            if (table) {
                table.style.opacity = '0.5';
                table.style.pointerEvents = 'none';
            }
        });
    }
</script>

    <link href="../css/semantic.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="../css/dataTables.semanticui.min.css">
    <script src="../js/jquery.min.js"></script>
    <link href="../css/main.css?13" rel="stylesheet"/>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css" />
    <?php include_once 'shared/dto_headerStyles.php' ?>
    <?php include_once '../assemblynotes/shared/headerScripts.php' ?>

    <style>
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

        /* ===== DATE FILTER CONTAINER ===== */
        .date-filter-container {
            display: flex;
            gap: 12px;
            align-items: center;
            margin: 0 auto;
            flex-wrap: wrap;
            justify-content: center;
            padding: 10px 0;
        }

        /* ===== DATE RANGE PICKER ===== */
        #reportrange.date-range-picker {
            padding: 11px 18px;
            background-color: #f8f9fa;
            color: #555;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            min-width: 220px;
        }

        #reportrange.date-range-picker:hover {
            background-color: #fff;
            border-color: #00646E;
            box-shadow: 0 2px 6px rgba(0, 100, 110, 0.1);
        }

        #reportrange.date-range-picker span {
            color: #333;
            font-weight: 500;
        }

        /* ===== FILTER BUTTONS ===== */
        .btn-filter {
            padding: 11px 22px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            outline: none;
        }

        .btn-filter:active {
            outline: none;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.15);
        }

        .btn-filter-primary {
            background-color: #00646E;
            color: white;
        }

        .btn-filter-primary:hover {
            background-color: #004d54;
            box-shadow: 0 3px 8px rgba(0, 100, 110, 0.25);
        }

        .btn-filter-primary:active {
            background-color: #003d43;
        }

        .btn-filter-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-filter-secondary:hover {
            background-color: #5a6268;
            box-shadow: 0 3px 8px rgba(108, 117, 125, 0.25);
        }

        .btn-filter-secondary:active {
            background-color: #4e555b;
        }

        .btn-filter i {
            font-size: 15px;
        }

        #reportrange i {
            font-size: 14px;
        }

        /* ===== ACTION COLUMN STYLING ===== */
        .action-column {
            width: 90px;
            min-width: 90px;
            text-align: center;
            padding: 6px 3px !important;
        }

        .action-buttons-container {
            display: flex;
            flex-direction: column;
            gap: 3px;
            justify-content: center;
            align-items: center;
        }

        .action-button {
            padding: 5px 10px !important;
            font-size: 11px !important;
            height: 28px !important;
            line-height: 18px !important;
            border-radius: 3px !important;
            border: none !important;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            display: block;
            font-weight: 600;
            width: 75px;
            margin: 0 auto;
        }

        .action-button.update {
            background-color: #FF9800 !important;
            color: white !important;
        }

        .action-button.update:hover {
            background-color: #F57C00 !important;
            box-shadow: 0 2px 4px rgba(255, 152, 0, 0.4);
        }

        .action-button.delete {
            background-color: #f44336 !important;
            color: white !important;
        }

        .action-button.delete:hover {
            background-color: #d32f2f !important;
            box-shadow: 0 2px 4px rgba(244, 67, 54, 0.4);
        }

        .action-button i {
            margin-right: 3px;
            font-size: 10px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 900px) {
            .date-filter-container {
                gap: 10px;
                padding: 8px 0;
            }
            
            #reportrange.date-range-picker {
                min-width: 180px;
                font-size: 13px;
                padding: 10px 15px;
            }
            
            .btn-filter {
                padding: 10px 18px;
                font-size: 13px;
            }
        }

        @media (max-width: 768px) {
            .date-filter-container {
                flex-direction: column;
                width: 100%;
                gap: 12px;
            }
            
            #reportrange.date-range-picker,
            .btn-filter {
                width: 100%;
                justify-content: center;
            }
            
            #reportrange.date-range-picker {
                min-width: unset;
            }
        }
    </style>
</head>

<body>

<div id="wrapper">
    <?php $activePage = '/dpm/pms/user_reports.php'; ?>
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
                    <li><h2>Employee Leave Details</h2></li>
                </ul>
            </nav>
        </div>

        <!-- ===== ACCESS DENIED/ERROR MESSAGE BANNER ===== -->
        <div class="wrapper wrapper-content">
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
                                <?php echo htmlspecialchars($supervisorValidation['message']); ?>
                            </div>
                        </div>
                    </div>
                    <button class="access-denied-banner-close" onclick="document.getElementById('accessDeniedBanner').style.display='none';">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row" id="mainRow">
                <div class="col-lg-12">
                    <div class="ibox mb-0" id="ibox_open_list_page">
                        <div id="headersegment">
                            <div class="ibox-content text-center" style="padding-bottom:15px">
                                <div class="date-filter-container" id="datepicker">
                                    <div id="reportrange" class="date-range-picker">
                                        <i class="fa fa-calendar"></i>&nbsp;
                                        <span>Select Date Range</span> <i class="fa fa-caret-down"></i>
                                    </div>
                                    <button class="btn-filter btn-filter-primary" onclick="filterUsersByDate()" type="button"> 
                                        <i class="fa fa-filter"></i>&nbsp;Date Filter
                                    </button>
                                    <button id="clearDateFilter" class="btn-filter btn-filter-secondary">
                                        <i class="fa fa-times"></i>&nbsp;Clear Date Filter
                                    </button>
                                </div>
                            </div>

                            <div id="detailsegment" style="display: none">
                                <div class="ui inverted dimmer" id="mai_spinner_page">
                                    <div class="ui loader"></div>
                                </div>
                                <div class="one column center aligned padded ui grid">
                                    <div class="row" style="height:100%;margin-top:2%;">
                                        <div class="column" id="controlpanel">
                                            <!-- ✅ CRITICAL: Added tbody for DataTables -->
                                            <table id='table_open_items'
                                                   class="ui celled very small compact responsive table dataTable no-footer scrollable-table">
                                                <thead class="scrollable-thead">
                                                    <tr>
                                                        <th>Action</th>
                                                        <th>GID</th>
                                                        <th>Name</th>
                                                        <th>Department</th>
                                                        <th>Sub-Department</th>
                                                        <th>Role</th>
                                                        <th>Group Type</th>
                                                        <th>In-Company Manager</th>
                                                        <th>Line Manager</th>
                                                        <th>Supervisor</th>
                                                        <th>Sponsor</th>
                                                        <th>Type of Employment</th>
                                                        <th>Joined</th>
                                                        <th>Type of Leave</th>
                                                        <th>Detail</th>
                                                        <th>Start Date and Time</th>
                                                        <th>End Date and Time</th>
                                                        <th>Total Leave Days</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- DataTable will populate rows here -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php $footer_display = 'Employee Leave Details';
        include_once '../assemblynotes/shared/footer.php'; ?>
    </div>
</div>

<!-- Mainly scripts -->
<?php include_once '../assemblynotes/shared/headerSemanticScripts.php' ?>
<?php include_once 'pms/LeaveModal.php'; ?>
<script src="shared/shared.js"></script>
<script src="pms/LeaveModal.js?<?= time() ?>"></script>
<script src="/dpm/pms/leaveform.js?<?php echo rand(); ?>"></script>
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>

</body>
</html>