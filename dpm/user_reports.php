<!DOCTYPE html>
<html>
<?php
// **DETERMINE USER ROLE AND VALIDATE SUPERVISOR**
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

try {
    $pdoManager = new PDOManager('spectra_db');
    
    if (in_array(20, $userInfo["Modules"])) {
        // **ADMIN ROLE**
        $isPMSAdmin = 1;
        $userRole = 'admin';
        SharedManager::checkAuthToModule(20);
        
        $supervisorValidation['message'] = 'Admin access granted - Full system access';
        
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
                $getEmployeesQuery = "SELECT DISTINCT department
                                     FROM employee_registration 
                                     WHERE CONCAT(',', TRIM(REPLACE(supervisor, ' ', '')), ',') LIKE :supervisor_id
                                     AND department IS NOT NULL
                                     AND department != ''
                                     ORDER BY department ASC";
                
                $employeesResult = $pdoManager->fetchQueryData($getEmployeesQuery, [':supervisor_id' => '%,' . $supervisorId . ',%']);
                
                if (isset($employeesResult['data']) && !empty($employeesResult['data'])) {
                    // **VALID SUPERVISOR WITH EMPLOYEES**
                    $supervisorDepartments = array_column($employeesResult['data'], 'department');
                    
                    // Count total employees
                    $countQuery = "SELECT COUNT(*) as total_employees 
                                  FROM employee_registration 
                                  WHERE CONCAT(',', TRIM(REPLACE(supervisor, ' ', '')), ',') LIKE :supervisor_id";
                    
                    $countResult = $pdoManager->fetchQueryData($countQuery, [':supervisor_id' => '%,' . $supervisorId . ',%']);
                    $totalEmployees = isset($countResult['data'][0]['total_employees']) ? $countResult['data'][0]['total_employees'] : 0;
                    
                    $supervisorValidation['is_valid_supervisor'] = true;
                    $supervisorValidation['supervised_employees'] = $totalEmployees;
                    $supervisorValidation['message'] = "Supervisor access granted - Managing $totalEmployees employee(s) in " . count($supervisorDepartments) . " department(s)";
                    
                    error_log("Supervisor $supervisorId validated successfully - Departments: " . implode(', ', $supervisorDepartments));
                    
                } else {
                    $supervisorValidation['is_valid_supervisor'] = false;
                    $supervisorValidation['supervised_employees'] = 0;
                    $supervisorValidation['message'] = "Supervisor record found but no employees assigned";
                    $accessDeniedMessage = "Supervisor record found but no employees assigned to manage";
                    $accessDeniedType = 'warning';
                    
                    error_log("Supervisor $supervisorId has no employees assigned");
                }
            } else {
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
        SharedManager::checkAuthToModule(20);
    }
    
} catch (Exception $e) {
    error_log("Error in supervisor validation: " . $e->getMessage());
    $supervisorValidation['message'] = 'Error: Database connection failed - ' . $e->getMessage();
    $accessDeniedMessage = 'Error: ' . htmlspecialchars($e->getMessage());
    $accessDeniedType = 'error';
}

include_once 'core/index.php';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Registration</title>
    
    <!-- CRITICAL: Define JavaScript variables BEFORE any other scripts -->
<script>
    // ===== USER ROLE CONFIGURATION =====
    var isPMSAdmin = <?php echo $isPMSAdmin; ?>;
    var userRole = '<?php echo $userRole; ?>';
    var supervisorId = '<?php echo isset($userInfo['GID']) ? $userInfo['GID'] : ''; ?>';
    var isValidSupervisor = <?php echo $supervisorValidation['is_valid_supervisor'] ? 'true' : 'false'; ?>;
    var supervisedEmployees = <?php echo $supervisorValidation['supervised_employees']; ?>;
    
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
        supervisorDepartments: supervisorDepartments
    });
    
    // ✅ VALIDATE SUPERVISOR ACCESS
    if (isSupervisor && !isValidSupervisor) {
        console.warn('⚠️ Supervisor validation failed - Limited access');
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

    #planned_month_filter {
        background-color: #00AF8E;
        border-color: #00AF8E;
        color: #FFFFFF;
    }

    /* ===== ACCESS DENIED BANNER ===== */
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
        font-size: 18px;
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
        font-size: 12px;
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

    /* ===== DATE FILTER CONTAINER ===== */
    .date-filter-container {
        display: flex;
        gap: 8px;
        align-items: center;
        margin: 0 auto;
        flex-wrap: wrap;
        justify-content: center;
        padding: 8px 0;
    }

    /* ===== DATE RANGE PICKER ===== */
    #reportrange.date-range-picker {
        padding: 8px 14px;
        background-color: #f8f9fa;
        color: #555;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        min-width: 180px;
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
        padding: 8px 16px;
        font-size: 12px;
        font-weight: 600;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
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
        font-size: 13px;
    }

    #reportrange i {
        font-size: 12px;
    }

    /* ===== TABLE STYLING ===== */
    .scrollable-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .scrollable-thead {
        position: sticky;
        top: 0;
        background-color: #f5f5f5;
        z-index: 10;
    }

    .scrollable-thead th {
        padding: 8px 6px;
        text-align: left;
        font-weight: 600;
        border: 1px solid #ddd;
        font-size: 11px;
    }

    /* ===== ACTION COLUMN STYLING ===== */
    .action-column {
        width: 70px;
        min-width: 70px;
        text-align: center;
        padding: 4px 2px !important;
    }

    .action-buttons-container {
        display: flex;
        flex-direction: column;
        gap: 2px;
        justify-content: center;
        align-items: center;
    }

    .action-button {
        padding: 4px 8px !important;
        font-size: 10px !important;
        height: 24px !important;
        line-height: 16px !important;
        border-radius: 3px !important;
        border: none !important;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        display: block;
        font-weight: 600;
        width: 65px;
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
        margin-right: 2px;
        font-size: 9px;
    }

    .action-button-text {
        font-size: 9px;
    }

    /* ===== TABLE BODY STYLING ===== */
    .scrollable-table tbody tr {
        height: 32px;
    }

    .scrollable-table tbody td {
        padding: 4px 6px;
        border: 1px solid #ddd;
        font-size: 11px;
        vertical-align: middle;
    }

    /* ===== IBOX STYLING ===== */
    .ibox-content {
        padding: 10px !important;
    }

    .ibox {
        margin-bottom: 10px !important;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 900px) {
        .date-filter-container {
            gap: 6px;
            padding: 6px 0;
        }
        
        #reportrange.date-range-picker {
            min-width: 150px;
            font-size: 11px;
            padding: 7px 12px;
        }
        
        .btn-filter {
            padding: 7px 12px;
            font-size: 11px;
        }

        .scrollable-table {
            font-size: 10px;
        }

        .scrollable-thead th {
            padding: 6px 4px;
            font-size: 10px;
        }

        .scrollable-table tbody td {
            padding: 3px 4px;
            font-size: 10px;
        }

        .action-button {
            padding: 3px 6px !important;
            font-size: 9px !important;
            height: 22px !important;
            width: 60px;
        }
    }

    @media (max-width: 768px) {
        .date-filter-container {
            flex-direction: column;
            width: 100%;
            gap: 8px;
        }
        
        #reportrange.date-range-picker,
        .btn-filter {
            width: 100%;
            justify-content: center;
        }
        
        #reportrange.date-range-picker {
            min-width: unset;
        }

        .scrollable-table {
            font-size: 9px;
        }

        .scrollable-thead th {
            padding: 5px 3px;
            font-size: 9px;
        }

        .scrollable-table tbody td {
            padding: 2px 3px;
            font-size: 9px;
        }
    }
</style>
</head>

<body>

<div id="wrapper">
    <?php $activePage = '/dpm/user_reports.php'; ?>
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
                    <li><h2 style="margin: 8px 0; font-size: 20px;">Employee Registration</h2></li>
                </ul>
            </nav>
        </div>
        <div class="wrapper wrapper-content" style="padding: 10px;">
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
        
            <div class="row" id="mainRow">
                <div class="col-lg-12">
                    <div class="ibox mb-0" id="ibox_open_list_page">
                        <div id="headersegment">
                            
                            <div class="ibox-content text-center">
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
                                    <div class="row" style="height:100%;margin-top:1%;">
                                        <div class="column" id="controlpanel" style="padding: 0;">
                                            <!-- ✅ FIXED: Added tbody element -->
                                            <table id='table_open_items'
                                                   class="ui celled very small compact responsive table dataTable no-footer scrollable-table">
                                                <thead class="scrollable-thead">
                                                    <tr>
                                                        <th>Action</th>
                                                        <th>GID</th>
                                                        <th>Name</th>
                                                        <th>Department</th>
                                                        <th>Sub Department</th>
                                                        <th>Role</th>
                                                        <th>Group Type</th>
                                                        <th>In Company Manager</th>
                                                        <th>Line Manager</th>
                                                        <th>Supervisor</th>
                                                        <th>Sponsor</th>
                                                        <th>Type of Employment</th>
                                                        <th>Joined 01.01.2005</th>
                                                        <th>Shift Type</th>
                                                        <th>Temporary Sub Department</th>
                                                        <th>Temporary Group Type</th>
                                                        <th>Transfer From Date</th>
                                                        <th>Transfer To Date</th>
                                                    </tr>
                                                </thead>
                                                <!-- ✅ CRITICAL: Added tbody for DataTables to populate rows -->
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

            <!-- Reason for Job Leaving Modal -->
            <div id="reasonModal" style="display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
                <div class="modal-content" style="background-color: #fefefe; margin: 7% auto; padding: 20px; border: 1px solid #888; width: 75%; max-width: 800px; border-radius: 5px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                    <form id="deleteUserForm">
                        <input type="hidden" name="user_id" id="delete_user_id">
                        
                        <div class="modal-header" style="background-color: #009999; color: white; padding: 12px; border-radius: 5px 5px 0 0; display: flex; justify-content: space-between; align-items: center; margin: -20px -20px 15px; font-size: 16px;">
                            <h4 class="modal-title" style="margin: 0; font-size: 16px;">Reason for Job Leaving</h4>
                            <button type="button" class="close" id="closeReasonModal" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        
                        <div class="modal-body" style="padding: 0 10px;">
                            <div style="display: flex; justify-content: space-between; gap: 30px;">
                                <div style="flex: 1;">
                                    <label for="reason" style="display: block; margin-bottom: 8px; font-weight: bold; font-size: 13px;">
                                        Select Reason: <span style="color: red;">*</span>
                                    </label>
                                    <select name="reason" id="reason" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px; background-color: #f9f9f9;">
                                        <option value="">-- Select --</option>
                                        <option value="Resignation">Resignation</option>
                                        <option value="Internal Transfer">Internal Transfer</option>
                                        <option value="Retirement">Retirement</option>
                                        <option value="Termination">Termination</option>
                                        <option value="Contract Ended">Contract Ended</option>
                                        <option value="Absenteeism">Absenteeism</option>
                                        <option value="Layoff / Downsizing">Layoff / Downsizing</option>
                                    </select>
                                </div>

                                <div style="flex: 1;">
                                    <label for="remarks" style="display: block; margin-bottom: 8px; font-weight: bold; font-size: 13px;">
                                        Remarks: <span style="color: red;">*</span>
                                    </label>
                                    <textarea name="remarks" id="remarks" rows="4" required placeholder="Enter additional details..." style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; resize: vertical; font-size: 12px; min-height: 100px;"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="modal-footer" style="padding: 15px 0 0; text-align: right; border-top: 1px solid #eee; margin-top: 15px;">
                            <button type="button" id="cancelReasonBtn" class="btn btn-white" style="padding: 8px 16px; background: #f1f1f1; border: 1px solid #ccc; border-radius: 4px; margin-right: 12px; font-size: 12px;">Cancel</button>
                            <button type="submit" class="btn btn-primary" style="padding: 8px 16px; background: #1ab394; color: white; border: none; border-radius: 4px; font-size: 12px;">Submit</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php $footer_display = 'Employee Registration';
            include_once '../assemblynotes/shared/footer.php'; ?>
        </div>
    </div>
</div>

<!-- Mainly scripts -->
<?php include_once '../assemblynotes/shared/headerSemanticScripts.php' ?>
<?php include_once 'pms/UserModal.php'; ?>

<script src="shared/shared.js"></script>
<script src="pms/UserModal.js?<?= time() ?>"></script>
<script src="/dpm/pms/alluser_reports.js?<?php echo rand(); ?>"></script>
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>

</body>
</html>